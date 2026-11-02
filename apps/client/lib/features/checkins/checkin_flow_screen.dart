import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import '../progress/progress_screen.dart';
import 'pose_camera_screen.dart';

final checkinDetailProvider = FutureProvider.autoDispose.family<CheckinDetail, String>(
  (ref, id) => ref.watch(progressRepositoryProvider).checkin(id),
);

/// Step-by-step check-in: questions → metrics → photos → review.
class CheckinFlowScreen extends ConsumerWidget {
  const CheckinFlowScreen({super.key, required this.checkinId});

  final String checkinId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detail = ref.watch(checkinDetailProvider(checkinId));
    return detail.when(
      loading: () => Scaffold(appBar: AppBar(), body: const AuSkeletonList()),
      error: (e, _) => Scaffold(appBar: AppBar(), body: AuErrorState(error: e, onRetry: () => ref.invalidate(checkinDetailProvider(checkinId)))),
      data: (d) => _Flow(detail: d),
    );
  }
}

enum _StepKind { intro, question, metrics, photos, review }

class _Step {
  const _Step(this.kind, [this.question]);

  final _StepKind kind;
  final CheckinQuestion? question;
}

class _Flow extends ConsumerStatefulWidget {
  const _Flow({required this.detail});

  final CheckinDetail detail;

  @override
  ConsumerState<_Flow> createState() => _FlowState();
}

class _FlowState extends ConsumerState<_Flow> {
  final _page = PageController();
  int _index = 0;
  final Map<String, dynamic> _answers = {};
  late UnitSystem _units = ref.read(unitsProvider);
  double? _weight;
  double? _bodyFat;
  final Map<String, double> _measurements = {};
  final Map<String, String> _photoPaths = {};
  final Map<String, String> _photoIds = {};
  bool _submitting = false;
  double _uploadProgress = 0;

  late final List<_Step> _steps = [
    const _Step(_StepKind.intro),
    for (final q in widget.detail.form.questions.where((q) => q.type != 'photo')) _Step(_StepKind.question, q),
    if (widget.detail.form.includeMetrics) const _Step(_StepKind.metrics),
    if (widget.detail.form.includePhotos || widget.detail.form.questions.any((q) => q.type == 'photo')) const _Step(_StepKind.photos),
    const _Step(_StepKind.review),
  ];

  @override
  void dispose() {
    _page.dispose();
    super.dispose();
  }

  bool _canContinue() {
    final step = _steps[_index];
    if (step.kind != _StepKind.question) return true;
    final q = step.question!;
    if (!q.isRequired) return true;
    final a = _answers[q.id];
    return a != null && !(a is String && a.trim().isEmpty) && !(a is List && a.isEmpty);
  }

  void _go(int delta) {
    final next = (_index + delta).clamp(0, _steps.length - 1);
    if (next == _index) return;
    FocusScope.of(context).unfocus();
    setState(() => _index = next);
    _page.animateToPage(next, duration: AuMotion.slow, curve: AuMotion.easeOut);
  }

  Future<void> _submit() async {
    setState(() => _submitting = true);
    final repo = ref.read(progressRepositoryProvider);
    try {
      final entries = _photoPaths.entries.where((e) => !_photoIds.containsKey(e.key)).toList();
      for (var i = 0; i < entries.length; i++) {
        final e = entries[i];
        final photo = await repo.uploadPhoto(
          filePath: e.value,
          pose: e.key,
          takenOn: DateTime.now(),
          checkinResponseId: widget.detail.id,
          onProgress: (p) => setState(() => _uploadProgress = (i + p) / entries.length),
        );
        _photoIds[e.key] = photo.id;
      }
      for (final q in widget.detail.form.questions.where((q) => q.type == 'photo')) {
        final id = _photoIds['q:${q.id}'] ?? _photoIds.values.firstOrNull;
        if (id != null) _answers[q.id] = id;
      }
      await repo.submitCheckin(
        widget.detail.id,
        answers: _answers,
        weightKg: _weight == null ? null : Units.weightFromDisplay(_weight!, _units),
        bodyFatPct: _bodyFat,
        measurements: _measurements.map((k, v) => MapEntry(k, Units.lengthFromDisplay(v, _units))),
        photoIds: _photoIds.values.toList(),
      );
      unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.checkinSubmitted()));
      ref.invalidate(checkinsProvider);
      if (!mounted) return;
      AuHaptics.success();
      AuToast.show(context, context.l10n.checkinSubmitted, tone: AuToastTone.success);
      context.go(Routes.today);
    } on ApiFailure catch (f) {
      if (mounted) showError(context, f);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final last = _index == _steps.length - 1;
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.detail.form.name),
        leading: IconButton(tooltip: context.core.close, icon: const Icon(Icons.close_rounded), onPressed: () => context.pop()),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(3),
          child: LinearProgressIndicator(value: (_index + 1) / _steps.length, minHeight: 3, backgroundColor: au.border),
        ),
      ),
      body: PageView(
        controller: _page,
        physics: const NeverScrollableScrollPhysics(),
        children: [for (final s in _steps) _buildStep(context, s)],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, AuSpacing.s2, AuSpacing.s5, AuSpacing.s3),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (_submitting && _uploadProgress > 0 && _uploadProgress < 1)
                Padding(
                  padding: const EdgeInsetsDirectional.only(bottom: AuSpacing.s2),
                  child: LinearProgressIndicator(value: _uploadProgress),
                ),
              Row(
                children: [
                  if (_index > 0) ...[
                    Expanded(child: AuButton.secondary(label: context.core.back, onPressed: () => _go(-1))),
                    const SizedBox(width: AuSpacing.s3),
                  ],
                  Expanded(
                    flex: 2,
                    child: AuButton(
                      label: last ? l10n.submitCheckin : context.core.next,
                      loading: _submitting,
                      onPressed: _canContinue() ? (last ? _submit : () => _go(1)) : null,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStep(BuildContext context, _Step step) {
    final au = context.au;
    final l10n = context.l10n;
    Widget page(String title, List<Widget> children, {String? subtitle}) => ListView(
          padding: const EdgeInsetsDirectional.all(AuSpacing.s6),
          children: [
            Text(title, style: au.text.h2),
            if (subtitle != null) ...[
              const SizedBox(height: AuSpacing.s2),
              Text(subtitle, style: au.text.body.copyWith(color: au.textSecondary)),
            ],
            const SizedBox(height: AuSpacing.s6),
            ...children,
          ],
        );

    switch (step.kind) {
      case _StepKind.intro:
        return page(
          widget.detail.form.name,
          [
            AuCard(
              variant: AuCardVariant.hero,
              child: Row(
                children: [
                  Icon(Icons.timer_outlined, color: au.accent),
                  const SizedBox(width: AuSpacing.s3),
                  Expanded(child: Text(l10n.checkinDuration(_steps.length - 2), style: au.text.bodySmall)),
                ],
              ),
            ),
            if (widget.detail.coachFeedback != null) ...[
              const SizedBox(height: AuSpacing.s4),
              AuCard(variant: AuCardVariant.sunken, child: Text(widget.detail.coachFeedback!, style: au.text.bodySmall)),
            ],
          ],
          subtitle: widget.detail.form.intro ?? l10n.checkinIntro,
        );
      case _StepKind.question:
        final q = step.question!;
        return page(q.label, [_QuestionInput(question: q, value: _answers[q.id], onChanged: (v) => setState(() => _answers[q.id] = v))],
            subtitle: q.isRequired ? null : l10n.optional);
      case _StepKind.metrics:
        return page(
          l10n.bodyMetrics,
          [
            AuSegmented<UnitSystem>(
              segments: {UnitSystem.metric: context.core.unitsMetric, UnitSystem.imperial: context.core.unitsImperial},
              selected: _units,
              onChanged: (u) => setState(() {
                if (_weight != null) _weight = double.parse(Units.weightToDisplay(Units.weightFromDisplay(_weight!, _units), u).toStringAsFixed(1));
                _measurements.updateAll((k, v) => double.parse(Units.lengthToDisplay(Units.lengthFromDisplay(v, _units), u).toStringAsFixed(1)));
                _units = u;
              }),
            ),
            const SizedBox(height: AuSpacing.s5),
            AuTextField(
              key: ValueKey('weight-${_units.name}'),
              label: '${context.core.weight} (${weightUnitLabel(context, _units)})',
              initialValue: _weight?.toString(),
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              textDirection: TextDirection.ltr,
              onChanged: (v) => _weight = double.tryParse(v.replaceAll(',', '.')),
            ),
            const SizedBox(height: AuSpacing.s3),
            MeasurementInputs(
              units: _units,
              values: _measurements,
              bodyFat: _bodyFat,
              onBodyFat: (v) => _bodyFat = v,
              onChanged: (k, v) => v == null ? _measurements.remove(k) : _measurements[k] = v,
            ),
          ],
          subtitle: l10n.bodyMetricsHint,
        );
      case _StepKind.photos:
        return page(
          l10n.progressPhotos,
          [
            Row(
              children: [
                for (final pose in const ['front', 'side', 'back']) ...[
                  Expanded(
                    child: _PhotoSlot(
                      pose: pose,
                      path: _photoPaths[pose],
                      onCaptured: (path) => setState(() {
                        _photoPaths[pose] = path;
                        _photoIds.remove(pose);
                      }),
                    ),
                  ),
                  if (pose != 'back') const SizedBox(width: AuSpacing.s3),
                ],
              ],
            ),
            const SizedBox(height: AuSpacing.s4),
            Row(
              children: [
                Icon(Icons.lock_outline_rounded, size: 16, color: au.textMuted),
                const SizedBox(width: AuSpacing.s2),
                Expanded(child: Text(l10n.photosPrivate, style: au.text.caption.copyWith(color: au.textMuted))),
              ],
            ),
          ],
          subtitle: l10n.photosHint,
        );
      case _StepKind.review:
        return page(l10n.reviewCheckin, [
          for (final q in widget.detail.form.questions.where((q) => q.type != 'photo'))
            AuListRow(
              dense: true,
              title: q.label,
              subtitle: _answerText(_answers[q.id]),
              onTap: () {
                final i = _steps.indexWhere((s) => s.question?.id == q.id);
                if (i >= 0) _go(i - _index);
              },
            ),
          if (_weight != null) AuListRow(dense: true, title: context.core.weight, subtitle: '${context.formatNumber(_weight!)} ${weightUnitLabel(context, _units)}'),
          if (_photoPaths.isNotEmpty) AuListRow(dense: true, title: l10n.progressPhotos, subtitle: '${_photoPaths.length}'),
        ], subtitle: l10n.reviewCheckinHint);
    }
  }

  String _answerText(Object? value) {
    if (value == null) return '—';
    if (value is List) return value.join(', ');
    return value.toString();
  }
}

class _QuestionInput extends StatelessWidget {
  const _QuestionInput({required this.question, required this.value, required this.onChanged});

  final CheckinQuestion question;
  final Object? value;
  final ValueChanged<Object?> onChanged;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    switch (question.type) {
      case 'long_text':
        return AuTextField(initialValue: value as String?, maxLines: 6, minLines: 4, hint: context.l10n.typeHere, onChanged: onChanged);
      case 'number':
      case 'measurement':
        return AuTextField(
          initialValue: value?.toString(),
          keyboardType: const TextInputType.numberWithOptions(decimal: true),
          textDirection: TextDirection.ltr,
          hint: question.unit,
          onChanged: (v) => onChanged(double.tryParse(v.replaceAll(',', '.'))),
        );
      case 'scale':
        final min = (question.min ?? 1).toInt();
        final max = (question.max ?? 10).toInt();
        final current = (value as num?)?.toInt();
        return Wrap(
          spacing: AuSpacing.s2,
          runSpacing: AuSpacing.s2,
          children: [
            for (var i = min; i <= max; i++) AuChip(label: '$i', selected: current == i, onSelected: (_) => onChanged(i)),
          ],
        );
      case 'single_choice':
        return Column(
          children: [
            for (final o in question.options ?? const <String>[])
              Padding(
                padding: const EdgeInsetsDirectional.only(bottom: AuSpacing.s2),
                child: AuCard(
                  onTap: () => onChanged(o),
                  variant: value == o ? AuCardVariant.hero : AuCardVariant.surface,
                  child: Row(
                    children: [
                      Icon(value == o ? Icons.radio_button_checked_rounded : Icons.radio_button_off_rounded, color: value == o ? au.accent : au.borderStrong),
                      const SizedBox(width: AuSpacing.s3),
                      Expanded(child: Text(o, style: au.text.body)),
                    ],
                  ),
                ),
              ),
          ],
        );
      case 'multi_choice':
        final selected = (value as List?)?.cast<String>() ?? const <String>[];
        return Column(
          children: [
            for (final o in question.options ?? const <String>[])
              CheckboxListTile(
                value: selected.contains(o),
                title: Text(o, style: au.text.body),
                controlAffinity: ListTileControlAffinity.leading,
                onChanged: (v) => onChanged(v == true ? [...selected, o] : selected.where((s) => s != o).toList()),
              ),
          ],
        );
      default:
        return AuTextField(initialValue: value as String?, hint: context.l10n.typeHere, onChanged: onChanged);
    }
  }
}

class _PhotoSlot extends StatelessWidget {
  const _PhotoSlot({required this.pose, required this.path, required this.onCaptured});

  final String pose;
  final String? path;
  final ValueChanged<String> onCaptured;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final label = switch (pose) {
      'side' => l10n.poseSide,
      'back' => l10n.poseBack,
      _ => l10n.poseFront,
    };
    return Semantics(
      button: true,
      label: label,
      child: GestureDetector(
        onTap: () async {
          final result = await Navigator.of(context).push<String>(MaterialPageRoute(builder: (_) => PoseCameraScreen(pose: pose)));
          if (result != null) onCaptured(result);
        },
        onLongPress: () async {
          final file = await ImagePicker().pickImage(source: ImageSource.gallery, maxWidth: 2000, imageQuality: 90);
          if (file != null) onCaptured(file.path);
        },
        child: AspectRatio(
          aspectRatio: 3 / 4,
          child: Container(
            decoration: BoxDecoration(
              color: au.surfaceSunken,
              borderRadius: BorderRadius.circular(AuRadius.md),
              border: Border.all(color: path == null ? au.border : au.accent),
            ),
            clipBehavior: Clip.antiAlias,
            child: path == null
                ? Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.add_a_photo_outlined, color: au.accent),
                      const SizedBox(height: AuSpacing.s2),
                      Text(label, style: au.text.caption),
                    ],
                  )
                : Stack(
                    fit: StackFit.expand,
                    children: [
                      Image(image: ResizeImage(FileImageProvider.of(path!), width: 400), fit: BoxFit.cover),
                      PositionedDirectional(top: 6, end: 6, child: Icon(Icons.check_circle_rounded, color: au.accent)),
                    ],
                  ),
          ),
        ),
      ),
    );
  }
}

/// Tiny indirection so the file image provider is created in one place.
abstract final class FileImageProvider {
  static ImageProvider of(String path) => FileImage(XFileCompat.file(path));
}
