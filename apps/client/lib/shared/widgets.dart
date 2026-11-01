import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'l10n_ext.dart';

/// Large-title page with pull-to-refresh and the offline banner.
///
/// Children are laid out in a scrollable list with luxury gutters; [onRefresh]
/// enables pull-to-refresh (always scrollable so it works on short pages).
class AuPage extends ConsumerWidget {
  const AuPage({
    super.key,
    required this.title,
    required this.children,
    this.onRefresh,
    this.actions = const [],
    this.overline,
    this.floatingActionButton,
    this.showBack = false,
    this.bottom,
    this.padding = const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, 0, AuSpacing.s5, AuSpacing.s16),
  });

  final String title;
  final String? overline;
  final List<Widget> children;
  final Future<void> Function()? onRefresh;
  final List<Widget> actions;
  final Widget? floatingActionButton;
  final bool showBack;
  final Widget? bottom;
  final EdgeInsetsGeometry padding;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final canPop = showBack || Navigator.of(context).canPop();
    Widget list = CustomScrollView(
      physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
      slivers: [
        SliverAppBar(
          pinned: true,
          automaticallyImplyLeading: canPop,
          backgroundColor: au.background,
          surfaceTintColor: Colors.transparent,
          expandedHeight: 112,
          actions: [...actions, const SizedBox(width: AuSpacing.s2)],
          flexibleSpace: FlexibleSpaceBar(
            titlePadding: EdgeInsetsDirectional.only(start: canPop ? 56 : AuSpacing.s5, bottom: AuSpacing.s3, end: AuSpacing.s5),
            expandedTitleScale: 1.45,
            title: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (overline != null)
                  Text(overline!.toUpperCase(), style: au.text.overline.copyWith(color: au.accent, fontSize: 8)),
                Text(title, style: au.text.title.copyWith(fontWeight: FontWeight.w800), maxLines: 1, overflow: TextOverflow.ellipsis),
              ],
            ),
          ),
        ),
        const SliverToBoxAdapter(child: OfflineBanner()),
        SliverPadding(
          padding: padding,
          sliver: SliverList.list(children: children),
        ),
      ],
    );
    if (onRefresh != null) {
      list = RefreshIndicator(
        color: au.accent,
        backgroundColor: au.surfaceRaised,
        onRefresh: onRefresh!,
        child: list,
      );
    }
    return Scaffold(body: list, floatingActionButton: floatingActionButton, bottomNavigationBar: bottom);
  }
}

/// Thin banner shown while offline (and the number of changes waiting).
class OfflineBanner extends ConsumerWidget {
  const OfflineBanner({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final online = ref.watch(onlineProvider).value ?? true;
    final pending = ref.watch(syncStatusProvider).value?.pending ?? 0;
    final au = context.au;
    return AnimatedSize(
      duration: AuMotion.base,
      curve: AuMotion.easeOut,
      child: online
          ? const SizedBox(width: double.infinity)
          : Container(
              width: double.infinity,
              margin: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, 0, AuSpacing.s5, AuSpacing.s3),
              padding: const EdgeInsetsDirectional.symmetric(horizontal: AuSpacing.s4, vertical: AuSpacing.s3),
              decoration: BoxDecoration(
                color: au.warningSoft,
                borderRadius: BorderRadius.circular(AuRadius.md),
                border: Border.all(color: au.warning.withValues(alpha: 0.4)),
              ),
              child: Row(
                children: [
                  Icon(Icons.cloud_off_rounded, size: 18, color: au.warning),
                  const SizedBox(width: AuSpacing.s3),
                  Expanded(
                    child: Text(
                      pending > 0 ? '${context.core.offlineBanner} ${context.core.pendingSync(pending)}' : context.core.offlineBanner,
                      style: au.text.caption.copyWith(color: au.textPrimary),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}

/// Small "saved data" pill shown when content came from the offline cache.
class CachedNotice extends StatelessWidget {
  const CachedNotice({super.key, required this.visible});

  final bool visible;

  @override
  Widget build(BuildContext context) {
    if (!visible) return const SizedBox.shrink();
    final au = context.au;
    return Padding(
      padding: const EdgeInsetsDirectional.only(bottom: AuSpacing.s3),
      child: Row(
        children: [
          Icon(Icons.history_rounded, size: 14, color: au.textMuted),
          const SizedBox(width: AuSpacing.s1),
          Text(context.l10n.savedOfflineCopy, style: au.text.caption.copyWith(color: au.textMuted)),
        ],
      ),
    );
  }
}

/// Inline async section: skeleton → error (retry) → data, inside a page.
class AsyncSection<T> extends StatelessWidget {
  const AsyncSection({super.key, required this.value, required this.builder, this.onRetry, this.skeletonHeight = 180});

  final AsyncValue<T> value;
  final Widget Function(T) builder;
  final VoidCallback? onRetry;
  final double skeletonHeight;

  @override
  Widget build(BuildContext context) {
    if (value.hasValue) return builder(value.requireValue);
    if (value.hasError) {
      return AuCard(child: AuErrorState(error: value.error!, onRetry: onRetry, compact: true));
    }
    return AuShimmer(child: AuSkeleton(height: skeletonHeight, radius: AuRadius.lg));
  }
}

/// Full-page skeleton for list screens.
class PageSkeleton extends StatelessWidget {
  const PageSkeleton({super.key, this.count = 4});

  final int count;

  @override
  Widget build(BuildContext context) {
    return AuShimmer(
      child: Column(
        children: [
          for (var i = 0; i < count; i++) ...[
            const AuSkeleton(height: 84, radius: AuRadius.lg),
            const SizedBox(height: AuSpacing.s3),
          ],
        ],
      ),
    );
  }
}

/// Horizontal week strip for picking a day (Today / Nutrition / Habits).
class DayStrip extends StatelessWidget {
  const DayStrip({super.key, required this.selected, required this.onSelected});

  final DateTime selected;
  final ValueChanged<DateTime> onSelected;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final days = [for (var i = -6; i <= 0; i++) today.add(Duration(days: i))];
    return SizedBox(
      height: 68,
      child: Row(
        children: [
          for (final day in days)
            Expanded(
              child: Builder(builder: (context) {
                final isSelected = DateUtils.isSameDay(day, selected);
                return Semantics(
                  selected: isSelected,
                  button: true,
                  label: context.formatDate(day, pattern: 'yMMMMEEEEd'),
                  excludeSemantics: true,
                  child: GestureDetector(
                    onTap: () {
                      AuHaptics.selection();
                      onSelected(day);
                    },
                    child: AnimatedContainer(
                      duration: AuMotion.base,
                      curve: AuMotion.easeOut,
                      margin: const EdgeInsetsDirectional.symmetric(horizontal: 2),
                      decoration: BoxDecoration(
                        gradient: isSelected ? au.goldGradient : null,
                        color: isSelected ? null : au.surface,
                        borderRadius: BorderRadius.circular(AuRadius.md),
                        border: isSelected ? null : Border.all(color: au.border),
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            context.formatDate(day, pattern: 'EEEEE'),
                            style: au.text.caption.copyWith(color: isSelected ? au.onAccent : au.textMuted),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            context.formatDate(day, pattern: 'd'),
                            style: au.text.title.copyWith(color: isSelected ? au.onAccent : au.textPrimary),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }),
            ),
        ],
      ),
    );
  }
}

/// Confirmation dialog with localized actions.
Future<bool> confirmAction(
  BuildContext context, {
  required String title,
  required String message,
  required String confirmLabel,
  bool destructive = false,
}) async {
  final result = await showDialog<bool>(
    context: context,
    builder: (context) => AlertDialog(
      title: Text(title),
      content: Text(message),
      actions: [
        TextButton(onPressed: () => Navigator.of(context).pop(false), child: Text(context.core.cancel)),
        TextButton(
          onPressed: () => Navigator.of(context).pop(true),
          style: destructive ? TextButton.styleFrom(foregroundColor: context.au.danger) : null,
          child: Text(confirmLabel),
        ),
      ],
    ),
  );
  return result ?? false;
}

/// Shows an error toast for [error].
void showError(BuildContext context, Object error) {
  AuToast.show(context, auFailureMessage(context, error), tone: AuToastTone.danger);
}
