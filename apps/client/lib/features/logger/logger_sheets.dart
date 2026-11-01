import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'calculators.dart';
import 'logger_controller.dart';
import 'logger_state.dart';

/// Free-text note editor.
Future<void> showNoteSheet(
  BuildContext context, {
  required String title,
  required String? initial,
  required Future<void> Function(String?) onSave,
}) {
  final controller = TextEditingController(text: initial);
  return showAuBottomSheet<void>(
    context: context,
    title: title,
    builder: (context) => Padding(
      padding: const EdgeInsetsDirectional.all(AuSpacing.s5),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          AuTextField(controller: controller, autofocus: true, maxLines: 5, minLines: 3, hint: context.l10n.noteHint),
          const SizedBox(height: AuSpacing.s4),
          AuButton(
            label: context.core.save,
            onPressed: () async {
              await onSave(controller.text);
              if (context.mounted) Navigator.of(context).pop();
            },
          ),
        ],
      ),
    ),
  ).whenComplete(controller.dispose);
}

/// RPE picker (6–10 in half steps) with plain-language descriptions.
Future<void> showRpePicker(BuildContext context, WidgetRef ref, LoggerSet set) {
  final l10n = context.l10n;
  final values = [6.0, 6.5, 7.0, 7.5, 8.0, 8.5, 9.0, 9.5, 10.0];
  String describe(double v) {
    if (v >= 10) return l10n.rpe10;
    if (v >= 9) return l10n.rpe9;
    if (v >= 8) return l10n.rpe8;
    if (v >= 7) return l10n.rpe7;
    return l10n.rpe6;
  }

  return showAuBottomSheet<void>(
    context: context,
    title: l10n.rateEffort,
    builder: (context) {
      final au = context.au;
      return ListView(
        shrinkWrap: true,
        padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, AuSpacing.s2, AuSpacing.s5, AuSpacing.s6),
        children: [
          Wrap(
            spacing: AuSpacing.s2,
            runSpacing: AuSpacing.s2,
            children: [
              for (final v in values)
                AuChip(
                  label: v == v.roundToDouble() ? v.toStringAsFixed(0) : v.toStringAsFixed(1),
                  selected: set.rpe == v,
                  onSelected: (_) {
                    ref.read(loggerControllerProvider.notifier).setRpe(set.id, v);
                    Navigator.of(context).pop();
                  },
                ),
            ],
          ),
          const SizedBox(height: AuSpacing.s4),
          for (final v in const [10.0, 9.0, 8.0, 7.0, 6.0])
            Padding(
              padding: const EdgeInsetsDirectional.only(bottom: AuSpacing.s2),
              child: Row(
                children: [
                  SizedBox(width: 32, child: Text(v.toStringAsFixed(0), style: au.text.label.copyWith(color: au.accent))),
                  Expanded(child: Text(describe(v), style: au.text.bodySmall.copyWith(color: au.textSecondary))),
                ],
              ),
            ),
          if (set.rpe != null)
            AuButton.ghost(
              label: l10n.clearRpe,
              expand: true,
              onPressed: () {
                ref.read(loggerControllerProvider.notifier).setRpe(set.id, null);
                Navigator.of(context).pop();
              },
            ),
        ],
      );
    },
  );
}

/// Long-press menu on a set: type and removal.
Future<void> showSetOptions(BuildContext context, WidgetRef ref, LoggerSet set) {
  final controller = ref.read(loggerControllerProvider.notifier);
  final core = context.core;
  final types = {
    SetType.warmup: core.setTypeWarmup,
    SetType.working: core.setTypeWorking,
    SetType.drop: core.setTypeDrop,
    SetType.failure: core.setTypeFailure,
    SetType.amrap: core.setTypeAmrap,
  };
  return showAuBottomSheet<void>(
    context: context,
    title: core.setNumber(set.setNumber),
    builder: (context) => ListView(
      shrinkWrap: true,
      padding: const EdgeInsetsDirectional.only(bottom: AuSpacing.s6),
      children: [
        for (final entry in types.entries)
          AuListRow(
            title: entry.value,
            trailing: set.setType == entry.key ? Icon(Icons.check_rounded, color: context.au.accent) : null,
            onTap: () {
              controller.setType(set.id, entry.key);
              Navigator.of(context).pop();
            },
          ),
        const Divider(),
        AuListRow(
          title: context.l10n.removeSet,
          destructive: true,
          leading: Icon(Icons.delete_outline_rounded, color: context.au.danger),
          onTap: () {
            controller.removeSet(set.id);
            Navigator.of(context).pop();
          },
        ),
      ],
    ),
  );
}

/// Plate calculator in the user's unit.
Future<void> showPlateCalculator(BuildContext context, WidgetRef ref, {double? initialTarget}) {
  final units = ref.read(unitsProvider);
  return showAuBottomSheet<void>(
    context: context,
    title: context.l10n.plateCalculator,
    builder: (_) => _PlateCalculatorView(units: units, initialTarget: initialTarget),
  );
}

class _PlateCalculatorView extends StatefulWidget {
  const _PlateCalculatorView({required this.units, this.initialTarget});

  final UnitSystem units;
  final double? initialTarget;

  @override
  State<_PlateCalculatorView> createState() => _PlateCalculatorViewState();
}

class _PlateCalculatorViewState extends State<_PlateCalculatorView> {
  late double _target = widget.initialTarget ?? (widget.units == UnitSystem.imperial ? 135 : 60);
  late double _bar = PlateCalculator.defaultBar(widget.units);

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final unit = weightUnitLabel(context, widget.units);
    final step = widget.units == UnitSystem.imperial ? 5.0 : 2.5;
    final result = PlateCalculator.calculate(target: _target, bar: _bar, plates: PlateCalculator.defaultPlates(widget.units));
    return Padding(
      padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, AuSpacing.s2, AuSpacing.s5, AuSpacing.s6),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          AuStepper(
            value: _target,
            step: step,
            min: _bar,
            max: 600,
            unit: unit,
            label: context.l10n.targetWeight,
            onChanged: (v) => setState(() => _target = v),
          ),
          const SizedBox(height: AuSpacing.s4),
          AuSegmented<double>(
            segments: widget.units == UnitSystem.imperial
                ? {45.0: '${context.l10n.bar} 45', 35.0: '${context.l10n.bar} 35', 15.0: 'EZ 15'}
                : {20.0: '${context.l10n.bar} 20', 15.0: '${context.l10n.bar} 15', 10.0: 'EZ 10'},
            selected: _bar,
            onChanged: (v) => setState(() {
              _bar = v;
              if (_target < v) _target = v;
            }),
          ),
          const SizedBox(height: AuSpacing.s6),
          Text(context.l10n.perSide.toUpperCase(), style: au.text.overline.copyWith(color: au.textMuted)),
          const SizedBox(height: AuSpacing.s3),
          SizedBox(
            height: 120,
            child: Directionality(
              textDirection: TextDirection.ltr,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(width: 60, height: 10, color: au.borderStrong),
                  for (final p in result.perSide)
                    Container(
                      width: 16,
                      height: 40 + (p / PlateCalculator.defaultPlates(widget.units).first) * 70,
                      margin: const EdgeInsets.symmetric(horizontal: 1.5),
                      decoration: BoxDecoration(gradient: au.goldGradient, borderRadius: BorderRadius.circular(4)),
                      alignment: Alignment.center,
                      child: RotatedBox(
                        quarterTurns: 3,
                        child: Text(context.formatNumber(p, maxDecimals: 2), style: au.text.caption.copyWith(color: au.onAccent, fontSize: 9)),
                      ),
                    ),
                  Container(width: 30, height: 10, color: au.borderStrong),
                ],
              ),
            ),
          ),
          const SizedBox(height: AuSpacing.s3),
          Text(
            result.perSide.isEmpty
                ? context.l10n.emptyBar
                : result.perSide.map((p) => context.formatNumber(p, maxDecimals: 2)).join(' + '),
            style: au.text.title,
          ),
          if (!result.exact)
            Text(
              context.l10n.closestLoad('${context.formatNumber(result.achieved, maxDecimals: 2)} $unit'),
              style: au.text.caption.copyWith(color: au.warning),
            ),
        ],
      ),
    );
  }
}

/// Estimated 1RM with a percentage table.
Future<void> showOneRepMaxCalculator(BuildContext context, WidgetRef ref) {
  final units = ref.read(unitsProvider);
  return showAuBottomSheet<void>(
    context: context,
    title: context.l10n.oneRepMaxCalculator,
    builder: (_) => _OneRepMaxView(units: units),
  );
}

class _OneRepMaxView extends StatefulWidget {
  const _OneRepMaxView({required this.units});

  final UnitSystem units;

  @override
  State<_OneRepMaxView> createState() => _OneRepMaxViewState();
}

class _OneRepMaxViewState extends State<_OneRepMaxView> {
  late double _weight = widget.units == UnitSystem.imperial ? 185 : 80;
  double _reps = 5;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final unit = weightUnitLabel(context, widget.units);
    final oneRm = OneRepMax.estimate(_weight, _reps.round());
    return ListView(
      shrinkWrap: true,
      padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, AuSpacing.s2, AuSpacing.s5, AuSpacing.s6),
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            AuStepper(
              value: _weight,
              step: widget.units == UnitSystem.imperial ? 5 : 2.5,
              unit: unit,
              label: context.core.weight,
              onChanged: (v) => setState(() => _weight = v),
            ),
            AuStepper(value: _reps, min: 1, max: 20, unit: context.core.reps, label: context.core.reps, onChanged: (v) => setState(() => _reps = v)),
          ],
        ),
        const SizedBox(height: AuSpacing.s5),
        AuCard(
          variant: AuCardVariant.hero,
          child: Column(
            children: [
              Text(context.l10n.estimatedOneRepMax.toUpperCase(), style: au.text.overline.copyWith(color: au.accent)),
              Text('${context.formatNumber(oneRm)} $unit', style: au.text.numeric.copyWith(fontSize: 40)),
            ],
          ),
        ),
        const SizedBox(height: AuSpacing.s4),
        for (final (p, w) in OneRepMax.percentages(oneRm))
          Padding(
            padding: const EdgeInsetsDirectional.symmetric(vertical: AuSpacing.s1),
            child: Row(
              children: [
                SizedBox(width: 56, child: Text('$p%', style: au.text.label.copyWith(color: au.textSecondary))),
                Expanded(
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(AuRadius.pill),
                    child: LinearProgressIndicator(value: p / 100, minHeight: 6, backgroundColor: au.border),
                  ),
                ),
                const SizedBox(width: AuSpacing.s3),
                SizedBox(
                  width: 90,
                  child: Text('${context.formatNumber(Units.roundToIncrement(w, widget.units))} $unit',
                      style: au.text.label, textAlign: TextAlign.end),
                ),
              ],
            ),
          ),
      ],
    );
  }
}

/// Exercise library search to add an exercise mid-session.
Future<void> showAddExerciseSheet(BuildContext context, WidgetRef ref) => showAuBottomSheet<void>(
      context: context,
      title: context.l10n.addExercise,
      expand: true,
      builder: (_) => _ExercisePicker(
        onPicked: (exercise) => ref.read(loggerControllerProvider.notifier).addExercise(exercise),
      ),
    );

/// Swap to one of the coach-approved alternatives (or search the library).
Future<void> showSwapSheet(BuildContext context, WidgetRef ref, LoggerExercise exercise) => showAuBottomSheet<void>(
      context: context,
      title: context.l10n.swapExercise,
      expand: true,
      builder: (_) => _SwapView(exercise: exercise),
    );

final _exerciseDetailProvider = FutureProvider.autoDispose.family<Exercise, String>(
  (ref, id) => ref.watch(workoutRepositoryProvider).exercise(id),
);

class _SwapView extends ConsumerWidget {
  const _SwapView({required this.exercise});

  final LoggerExercise exercise;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detail = ref.watch(_exerciseDetailProvider(exercise.exercise.id));
    final au = context.au;
    Future<void> pick(String id) async {
      try {
        final replacement = await ref.read(_exerciseDetailProvider(id).future);
        await ref.read(loggerControllerProvider.notifier).swapExercise(exercise.id, replacement);
        if (context.mounted) Navigator.of(context).pop();
      } on ApiFailure catch (f) {
        if (context.mounted) showError(context, f);
      }
    }

    return detail.when(
      loading: () => const AuSkeletonList(header: false, count: 4),
      error: (e, _) => AuErrorState(error: e, onRetry: () => ref.invalidate(_exerciseDetailProvider(exercise.exercise.id))),
      data: (d) {
        final alternatives = d.alternatives;
        return Column(
          children: [
            if (alternatives.isEmpty)
              Expanded(child: _ExercisePicker(onPicked: (e) => ref.read(loggerControllerProvider.notifier).swapExercise(exercise.id, e)))
            else ...[
              Padding(
                padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, 0, AuSpacing.s5, AuSpacing.s3),
                child: Text(context.l10n.coachApprovedAlternatives, style: au.text.bodySmall.copyWith(color: au.textSecondary)),
              ),
              Expanded(
                child: ListView(
                  children: [
                    for (final alt in alternatives)
                      AuListRow(
                        leading: const AuIconTile(icon: Icons.swap_horiz_rounded),
                        title: alt.name,
                        onTap: () => pick(alt.id),
                      ),
                  ],
                ),
              ),
            ],
          ],
        );
      },
    );
  }
}

class _ExercisePicker extends ConsumerStatefulWidget {
  const _ExercisePicker({required this.onPicked});

  final Future<void> Function(Exercise) onPicked;

  @override
  ConsumerState<_ExercisePicker> createState() => _ExercisePickerState();
}

class _ExercisePickerState extends ConsumerState<_ExercisePicker> {
  final _query = TextEditingController();
  Timer? _debounce;
  AsyncValue<List<Exercise>> _results = const AsyncLoading();

  @override
  void initState() {
    super.initState();
    _search('');
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _query.dispose();
    super.dispose();
  }

  Future<void> _search(String q) async {
    setState(() => _results = const AsyncLoading());
    try {
      final page = await ref.read(workoutRepositoryProvider).exercises(query: q);
      if (mounted) setState(() => _results = AsyncData(page.items));
    } on ApiFailure catch (f, st) {
      if (mounted) setState(() => _results = AsyncError(f, st));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, 0, AuSpacing.s5, AuSpacing.s3),
          child: AuTextField(
            controller: _query,
            hint: context.l10n.searchExercises,
            prefixIcon: Icons.search_rounded,
            onChanged: (v) {
              _debounce?.cancel();
              _debounce = Timer(const Duration(milliseconds: 300), () => _search(v));
            },
          ),
        ),
        Expanded(
          child: _results.when(
            loading: () => const AuSkeletonList(header: false, count: 5),
            error: (e, _) => AuErrorState(error: e, onRetry: () => _search(_query.text)),
            data: (items) => items.isEmpty
                ? AuEmptyState(
                    icon: Icons.search_off_rounded,
                    title: context.l10n.noExercisesFound,
                    message: context.l10n.noExercisesFoundHint,
                    compact: true,
                  )
                : ListView.builder(
                    itemCount: items.length,
                    itemBuilder: (context, i) {
                      final e = items[i];
                      return AuListRow(
                        leading: const AuIconTile(icon: Icons.fitness_center_rounded),
                        title: e.name,
                        subtitle: [e.primaryMuscle, ...e.equipment].whereType<String>().join(' · '),
                        onTap: () async {
                          await widget.onPicked(e);
                          if (context.mounted) Navigator.of(context).pop();
                        },
                      );
                    },
                  ),
          ),
        ),
      ],
    );
  }
}
