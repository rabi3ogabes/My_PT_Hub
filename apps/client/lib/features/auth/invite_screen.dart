import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'auth_layout.dart';

final invitationProvider = FutureProvider.autoDispose.family<InvitationPreview, String>(
  (ref, code) => ref.watch(authRepositoryProvider).invitation(code),
);

/// Invitation landing (deep link `/invite/{code}`) with the coach's branding.
class InviteScreen extends ConsumerStatefulWidget {
  const InviteScreen({super.key, required this.code});

  final String code;

  @override
  ConsumerState<InviteScreen> createState() => _InviteScreenState();
}

class _InviteScreenState extends ConsumerState<InviteScreen> {
  bool _accepting = false;

  Future<void> _accept() async {
    setState(() => _accepting = true);
    try {
      await ref.read(authControllerProvider.notifier).acceptInvitation(widget.code);
      if (!mounted) return;
      AuHaptics.success();
      AuToast.show(context, context.l10n.inviteAccepted, tone: AuToastTone.success);
      context.go(Routes.today);
    } on ApiFailure catch (f) {
      if (mounted) showError(context, f);
    } finally {
      if (mounted) setState(() => _accepting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final preview = ref.watch(invitationProvider(widget.code));
    final signedIn = ref.watch(authControllerProvider.select((s) => s.isSignedIn));
    return AuthLayout(
      title: l10n.inviteTitle,
      subtitle: l10n.inviteSubtitle,
      children: [
        preview.when(
          loading: () => const AuShimmer(child: AuSkeleton(height: 180, radius: AuRadius.lg)),
          error: (e, _) => AuCard(
            child: AuErrorState(
              error: e,
              compact: true,
              onRetry: e is ApiFailure && e.code == ApiFailure.invitationInvalid
                  ? null
                  : () => ref.invalidate(invitationProvider(widget.code)),
            ),
          ),
          data: (p) => Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              InvitePreviewCard(preview: p),
              const SizedBox(height: AuSpacing.s6),
              if (signedIn)
                AuButton(label: l10n.acceptInvitation, loading: _accepting, onPressed: _accept)
              else ...[
                AuButton(
                  label: l10n.createAccount,
                  onPressed: () => context.go(Uri(path: Routes.signUp, queryParameters: {'code': widget.code}).toString()),
                ),
                const SizedBox(height: AuSpacing.s3),
                AuButton.secondary(
                  label: l10n.haveAccountSignIn,
                  onPressed: () => context.go(
                    Uri(path: Routes.signIn, queryParameters: {'from': '${Routes.invite}/${widget.code}'}).toString(),
                  ),
                ),
              ],
            ],
          ),
        ),
      ],
    );
  }
}

/// The coach's branded card: logo, brand colour, coach name.
class InvitePreviewCard extends StatelessWidget {
  const InvitePreviewCard({super.key, required this.preview, this.compact = false});

  final InvitationPreview preview;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final brand = AuTheme.parseHex(preview.tenant.brandColor) ?? au.accent;
    final logo = preview.tenant.logoUrl;
    return Container(
      padding: EdgeInsetsDirectional.all(compact ? AuSpacing.s4 : AuSpacing.s6),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(AuRadius.lg),
        gradient: LinearGradient(
          begin: AlignmentDirectional.topStart,
          end: AlignmentDirectional.bottomEnd,
          colors: [brand.withValues(alpha: 0.22), au.surface],
        ),
        border: Border.all(color: brand.withValues(alpha: 0.6)),
      ),
      child: Row(
        children: [
          Container(
            width: compact ? 48 : 64,
            height: compact ? 48 : 64,
            decoration: BoxDecoration(color: brand, borderRadius: BorderRadius.circular(AuRadius.md)),
            clipBehavior: Clip.antiAlias,
            child: logo == null
                ? Center(
                    child: Text(
                      preview.tenant.name.characters.first.toUpperCase(),
                      style: au.text.h3.copyWith(color: ThemeData.estimateBrightnessForColor(brand) == Brightness.dark ? Colors.white : Colors.black),
                    ),
                  )
                : Image.network(logo, fit: BoxFit.cover, errorBuilder: (_, _, _) => const SizedBox.shrink()),
          ),
          const SizedBox(width: AuSpacing.s4),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(preview.tenant.name, style: compact ? au.text.title : au.text.h3),
                if (preview.coach != null) ...[
                  const SizedBox(height: 2),
                  Text(context.l10n.coachedBy(preview.coach!.name), style: au.text.bodySmall.copyWith(color: au.textSecondary)),
                ],
              ],
            ),
          ),
          Icon(Icons.verified_rounded, color: brand),
        ],
      ),
    );
  }
}
