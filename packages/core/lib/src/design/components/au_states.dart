import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../l10n/gen/au_localizations.dart';
import '../../network/api_failure.dart';
import '../app_tokens.dart';
import '../au_theme.dart';
import 'au_button.dart';

/// Shimmering placeholder sweep. Honors "reduce motion" and text direction.
class AuShimmer extends StatefulWidget {
  const AuShimmer({super.key, required this.child});

  final Widget child;

  @override
  State<AuShimmer> createState() => _AuShimmerState();
}

class _AuShimmerState extends State<AuShimmer> with SingleTickerProviderStateMixin {
  late final AnimationController _controller =
      AnimationController(vsync: this, duration: const Duration(milliseconds: 1400));

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final reduce = MediaQuery.maybeDisableAnimationsOf(context) ?? false;
    if (reduce) {
      _controller.stop();
    } else if (!_controller.isAnimating) {
      _controller.repeat();
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final rtl = Directionality.of(context) == TextDirection.rtl;
    final base = au.isDark ? au.surfaceRaised : au.surfaceSunken;
    final highlight = au.isDark ? const Color(0xFF2B2B36) : const Color(0xFFFFFFFF);
    return AnimatedBuilder(
      animation: _controller,
      child: widget.child,
      builder: (context, child) {
        final t = _controller.value;
        final dx = rtl ? 1 - t : t;
        return ShaderMask(
          blendMode: BlendMode.srcATop,
          shaderCallback: (rect) => LinearGradient(
            colors: [base, highlight, base],
            stops: const [0.25, 0.5, 0.75],
            begin: Alignment(-1 - 2 + dx * 4, 0),
            end: Alignment(1 - 2 + dx * 4, 0),
          ).createShader(rect),
          child: child,
        );
      },
    );
  }
}

/// Skeleton primitives (wrap a composition in [AuShimmer]).
class AuSkeleton extends StatelessWidget {
  const AuSkeleton({super.key, this.width, this.height = 14, this.radius = AuRadius.xs, this.circle = false});

  const AuSkeleton.circle({super.key, required double size})
      : width = size,
        height = size,
        radius = 0,
        circle = true;

  final double? width;
  final double height;
  final double radius;
  final bool circle;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        color: au.isDark ? au.surfaceRaised : au.surfaceSunken,
        shape: circle ? BoxShape.circle : BoxShape.rectangle,
        borderRadius: circle ? null : BorderRadius.circular(radius),
      ),
    );
  }
}

/// A ready-made list skeleton (cards with avatar + two lines).
class AuSkeletonList extends StatelessWidget {
  const AuSkeletonList({super.key, this.count = 5, this.header = true});

  final int count;
  final bool header;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Semantics(
      label: AuL10n.of(context).loading,
      child: AuShimmer(
        child: ListView(
          physics: const NeverScrollableScrollPhysics(),
          padding: const EdgeInsetsDirectional.all(AuSpacing.s5),
          children: [
            if (header) ...[
              const AuSkeleton(width: 120, height: 12),
              const SizedBox(height: AuSpacing.s3),
              const AuSkeleton(width: 220, height: 28, radius: AuRadius.sm),
              const SizedBox(height: AuSpacing.s6),
              const AuSkeleton(height: 160, radius: AuRadius.lg),
              const SizedBox(height: AuSpacing.s6),
            ],
            for (var i = 0; i < count; i++) ...[
              Container(
                padding: const EdgeInsetsDirectional.all(AuSpacing.s4),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(AuRadius.lg),
                  border: Border.all(color: au.border),
                ),
                child: const Row(
                  children: [
                    AuSkeleton.circle(size: 40),
                    SizedBox(width: AuSpacing.s3),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          AuSkeleton(width: 160),
                          SizedBox(height: AuSpacing.s2),
                          AuSkeleton(width: 100, height: 10),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AuSpacing.s3),
            ],
          ],
        ),
      ),
    );
  }
}

/// Empty state that teaches the next action.
class AuEmptyState extends StatelessWidget {
  const AuEmptyState({
    super.key,
    required this.icon,
    required this.title,
    required this.message,
    this.actionLabel,
    this.onAction,
    this.compact = false,
  });

  final IconData icon;
  final String title;
  final String message;
  final String? actionLabel;
  final VoidCallback? onAction;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Center(
      child: Padding(
        padding: EdgeInsetsDirectional.symmetric(horizontal: AuSpacing.s8, vertical: compact ? AuSpacing.s6 : AuSpacing.s12),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: compact ? 64 : 88,
              height: compact ? 64 : 88,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: au.accent.withValues(alpha: 0.08),
                border: Border.all(color: au.accent.withValues(alpha: 0.35)),
              ),
              child: Icon(icon, size: compact ? 28 : 38, color: au.accent),
            ),
            const SizedBox(height: AuSpacing.s5),
            Text(title, style: au.text.h3, textAlign: TextAlign.center),
            const SizedBox(height: AuSpacing.s2),
            Text(message, style: au.text.body.copyWith(color: au.textSecondary), textAlign: TextAlign.center),
            if (actionLabel != null && onAction != null) ...[
              const SizedBox(height: AuSpacing.s6),
              AuButton(label: actionLabel!, onPressed: onAction, expand: false),
            ],
          ],
        ),
      ),
    );
  }
}

/// Localized, user-facing message for any error.
String auFailureMessage(BuildContext context, Object error) {
  final l10n = AuL10n.of(context);
  if (error is! ApiFailure) return l10n.errorGeneric;
  if (error.code == ApiFailure.validationFailed && error.message.isNotEmpty) return error.message;
  return switch (error.code) {
    ApiFailure.network => l10n.errorNetwork,
    ApiFailure.timeout => l10n.errorTimeout,
    ApiFailure.serverError => l10n.errorServer,
    ApiFailure.forbidden => l10n.errorForbidden,
    ApiFailure.notFound => l10n.errorNotFound,
    ApiFailure.validationFailed => l10n.errorValidation,
    ApiFailure.planLimitReached => l10n.errorPlanLimit,
    ApiFailure.accountLocked => l10n.errorAccountLocked,
    ApiFailure.tooManyRequests => l10n.errorTooManyRequests,
    ApiFailure.invitationInvalid => l10n.errorInvitationInvalid,
    ApiFailure.unauthenticated || ApiFailure.invalidRefreshToken => l10n.errorSessionExpired,
    _ => error.message.isNotEmpty ? error.message : l10n.errorGeneric,
  };
}

/// Error state with retry.
class AuErrorState extends StatelessWidget {
  const AuErrorState({super.key, required this.error, this.onRetry, this.compact = false});

  final Object error;
  final VoidCallback? onRetry;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    final l10n = AuL10n.of(context);
    final offline = error is ApiFailure && (error as ApiFailure).isConnectivity;
    return AuEmptyState(
      icon: offline ? Icons.wifi_off_rounded : Icons.error_outline_rounded,
      title: l10n.errorTitle,
      message: auFailureMessage(context, error),
      actionLabel: onRetry == null ? null : l10n.retry,
      onAction: onRetry,
      compact: compact,
    );
  }
}

/// Renders an [AsyncValue] with skeleton, error (+retry) and data states.
/// Keeps showing previous data while refreshing.
class AuAsync<T> extends StatelessWidget {
  const AuAsync({super.key, required this.value, required this.data, this.loading, this.onRetry});

  final AsyncValue<T> value;
  final Widget Function(T data) data;
  final Widget? loading;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) {
    if (value.hasValue) return data(value.requireValue);
    if (value.hasError) return AuErrorState(error: value.error!, onRetry: onRetry);
    return loading ?? const AuSkeletonList();
  }
}
