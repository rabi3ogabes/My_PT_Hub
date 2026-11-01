import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'logger_controller.dart';
import 'logger_sheets.dart';
import 'logger_state.dart';
import 'logger_widgets.dart';

/// The smart logger. Opens the active session (restoring it after an app
/// kill when needed).
class LoggerScreen extends ConsumerStatefulWidget {
  const LoggerScreen({super.key});

  @override
  ConsumerState<LoggerScreen> createState() => _LoggerScreenState();
}

class _LoggerScreenState extends ConsumerState<LoggerScreen> {
  bool _restoring = true;

  @override
  void initState() {
    super.initState();
    _restore();
  }

  Future<void> _restore() async {
    await ref.read(loggerControllerProvider.notifier).restore();
    if (mounted) setState(() => _restoring = false);
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final controller = ref.read(loggerControllerProvider.notifier);
    controller.restAlarmTitle = context.l10n.restOverTitle;
    controller.restAlarmBody = context.l10n.restOverBody;
  }

  Future<void> _confirmDiscard() async {
    final ok = await confirmAction(
      context,
      title: context.l10n.discardWorkoutTitle,
      message: context.l10n.discardWorkoutMessage,
      confirmLabel: context.l10n.discard,
      destructive: true,
    );
    if (!ok) return;
    await ref.read(loggerControllerProvider.notifier).discard();
    if (mounted) context.go(Routes.today);
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(loggerControllerProvider);
    final l10n = context.l10n;
    if (state == null) {
      return Scaffold(
        appBar: AppBar(),
        body: _restoring
            ? const AuSkeletonList(header: false)
            : AuEmptyState(
                icon: Icons.fitness_center_rounded,
                title: l10n.noActiveWorkout,
                message: l10n.noActiveWorkoutHint,
                actionLabel: l10n.goToTrain,
                onAction: () => context.go(Routes.train),
              ),
      );
    }
    return _LoggerView(state: state, onDiscard: _confirmDiscard);
  }
}

class _LoggerView extends ConsumerWidget {
  const _LoggerView({required this.state, required this.onDiscard});

  final LoggerState state;
  final VoidCallback onDiscard;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    final l10n = context.l10n;
    final controller = ref.read(loggerControllerProvider.notifier);
    return PopScope(
      // Leaving the logger never pauses or loses the session.
      child: Scaffold(
        appBar: AppBar(
          leading: IconButton(
            tooltip: l10n.minimize,
            icon: const Icon(Icons.keyboard_arrow_down_rounded),
            onPressed: () => context.canPop() ? context.pop() : context.go(Routes.today),
          ),
          titleSpacing: 0,
          title: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(state.name ?? l10n.workout, style: au.text.title, maxLines: 1, overflow: TextOverflow.ellipsis),
              ElapsedText(startedAt: state.startedAt),
            ],
          ),
          actions: [
            PopupMenuButton<String>(
              tooltip: l10n.tools,
              icon: const Icon(Icons.more_horiz_rounded),
              onSelected: (v) {
                switch (v) {
                  case 'plates':
                    showPlateCalculator(context, ref);
                  case 'orm':
                    showOneRepMaxCalculator(context, ref);
                  case 'note':
                    showNoteSheet(context, title: l10n.sessionNote, initial: state.sessionNote, onSave: controller.setSessionNote);
                  case 'discard':
                    onDiscard();
                }
              },
              itemBuilder: (context) => [
                PopupMenuItem(value: 'plates', child: ListTile(leading: const Icon(Icons.album_outlined), title: Text(l10n.plateCalculator))),
                PopupMenuItem(value: 'orm', child: ListTile(leading: const Icon(Icons.calculate_outlined), title: Text(l10n.oneRepMaxCalculator))),
                PopupMenuItem(value: 'note', child: ListTile(leading: const Icon(Icons.edit_note_rounded), title: Text(l10n.sessionNote))),
                PopupMenuItem(
                  value: 'discard',
                  child: ListTile(
                    leading: Icon(Icons.delete_outline_rounded, color: au.danger),
                    title: Text(l10n.discardWorkout, style: TextStyle(color: au.danger)),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsetsDirectional.only(end: AuSpacing.s3),
              child: AuButton(
                label: l10n.finish,
                size: AuButtonSize.compact,
                expand: false,
                onPressed: () => context.push(Routes.finish),
              ),
            ),
          ],
          bottom: PreferredSize(
            preferredSize: const Size.fromHeight(3),
            child: TweenAnimationBuilder<double>(
              tween: Tween(begin: 0, end: state.progress),
              duration: AuMotion.slow,
              builder: (_, v, _) => LinearProgressIndicator(value: v, minHeight: 3, backgroundColor: au.border),
            ),
          ),
        ),
        body: Column(
          children: [
            const OfflineBanner(),
            Expanded(
              child: ListView(
                padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s4, AuSpacing.s4, AuSpacing.s4, 160),
                keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
                children: [
                  for (final block in state.blocks) ...[
                    BlockSection(block: block, state: state),
                    const SizedBox(height: AuSpacing.s5),
                  ],
                  AuButton.secondary(
                    label: l10n.addExercise,
                    icon: Icons.add_rounded,
                    onPressed: () => showAddExerciseSheet(context, ref),
                  ),
                  const SizedBox(height: AuSpacing.s3),
                  AuButton(
                    label: state.isComplete ? l10n.finishWorkout : l10n.finishEarly,
                    icon: Icons.flag_rounded,
                    onPressed: () => context.push(Routes.finish),
                  ),
                ],
              ),
            ),
          ],
        ),
        bottomSheet: state.rest == null ? null : RestTimerBar(rest: state.rest!, nextLabel: _nextLabel(context, state)),
      ),
    );
  }

  String? _nextLabel(BuildContext context, LoggerState state) {
    final id = state.activeSetId;
    if (id == null) return null;
    final loc = state.locate(id);
    if (loc == null) return null;
    final exercise = state.blocks[loc.block].exercises[loc.exercise];
    return context.l10n.upNext(exercise.exercise.name, loc.set + 1);
  }
}

/// Live "12:34" elapsed label (wall clock based).
class ElapsedText extends StatefulWidget {
  const ElapsedText({super.key, required this.startedAt});

  final DateTime startedAt;

  @override
  State<ElapsedText> createState() => _ElapsedTextState();
}

class _ElapsedTextState extends State<ElapsedText> {
  late final Timer _timer = Timer.periodic(const Duration(seconds: 1), (_) {
    if (mounted) setState(() {});
  });

  @override
  void dispose() {
    _timer.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Directionality(
      textDirection: TextDirection.ltr,
      child: Text(
        formatClock(DateTime.now().difference(widget.startedAt)),
        style: au.text.caption.copyWith(color: au.accent, fontFeatures: const [FontFeature.tabularFigures()]),
      ),
    );
  }
}
