import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';

import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'barcode_scanner_screen.dart';
import 'nutrition_providers.dart';
import 'nutrition_screen.dart';

const _slots = ['breakfast', 'lunch', 'dinner', 'snack'];

/// Default meal slot for the time of day.
String suggestedSlot(DateTime now) {
  final h = now.hour;
  if (h < 11) return 'breakfast';
  if (h < 16) return 'lunch';
  if (h < 21) return 'dinner';
  return 'snack';
}

/// Quick-log sheet: search, barcode, photo (AI), recent, favourites, quick add.
Future<void> showQuickLogSheet(BuildContext context, WidgetRef ref, {required DateTime date, String? slot}) {
  return showAuBottomSheet<void>(
    context: context,
    title: context.l10n.logFood,
    expand: true,
    builder: (_) => _QuickLogView(date: date, initialSlot: slot ?? suggestedSlot(DateTime.now())),
  );
}

class _QuickLogView extends ConsumerStatefulWidget {
  const _QuickLogView({required this.date, required this.initialSlot});

  final DateTime date;
  final String initialSlot;

  @override
  ConsumerState<_QuickLogView> createState() => _QuickLogViewState();
}

class _QuickLogViewState extends ConsumerState<_QuickLogView> with SingleTickerProviderStateMixin {
  late final TabController _tabs = TabController(length: 4, vsync: this);
  late String _slot = widget.initialSlot;
  final _query = TextEditingController();
  Timer? _debounce;
  AsyncValue<List<Food>>? _results;
  bool _busy = false;

  @override
  void dispose() {
    _tabs.dispose();
    _query.dispose();
    _debounce?.cancel();
    super.dispose();
  }

  void _onQuery(String q) {
    _debounce?.cancel();
    if (q.trim().length < 2) {
      setState(() => _results = null);
      return;
    }
    _debounce = Timer(const Duration(milliseconds: 300), () async {
      setState(() => _results = const AsyncLoading());
      try {
        final page = await ref.read(nutritionRepositoryProvider).searchFoods(q.trim());
        if (mounted) setState(() => _results = AsyncData(page.items));
      } on ApiFailure catch (f, st) {
        if (mounted) setState(() => _results = AsyncError(f, st));
      }
    });
  }

  Future<void> _pickFood(Food food, {String source = 'search'}) async {
    final logged = await showPortionSheet(context, ref, food: food, date: widget.date, slot: _slot, source: source);
    if (logged == true && mounted) {
      AuToast.show(context, context.l10n.loggedFood(food.name), tone: AuToastTone.success);
    }
  }

  Future<void> _scan() async {
    final code = await Navigator.of(context).push<String>(MaterialPageRoute(builder: (_) => const BarcodeScannerScreen()));
    if (code == null || !mounted) return;
    setState(() => _busy = true);
    try {
      final food = await ref.read(nutritionRepositoryProvider).barcode(code);
      if (!mounted) return;
      if (food == null) {
        AuToast.show(context, context.l10n.barcodeNotFound, tone: AuToastTone.warning);
        _tabs.animateTo(3);
      } else {
        await _pickFood(food, source: 'barcode');
      }
    } on ApiFailure catch (f) {
      if (mounted) showError(context, f);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _photo() async {
    final file = await ImagePicker().pickImage(source: ImageSource.camera, maxWidth: 1600, imageQuality: 85);
    if (file == null || !mounted) return;
    setState(() => _busy = true);
    try {
      final result = await ref.read(nutritionRepositoryProvider).recognizePhoto(file.path);
      if (!mounted) return;
      await showPhotoCandidatesSheet(context, ref, result: result, date: widget.date, slot: _slot);
    } on ApiFailure catch (f) {
      if (mounted) showError(context, f);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final au = context.au;
    return Column(
      children: [
        Padding(
          padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, 0, AuSpacing.s5, AuSpacing.s3),
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                for (final s in _slots)
                  Padding(
                    padding: const EdgeInsetsDirectional.only(end: AuSpacing.s2),
                    child: AuChip(label: slotLabel(context, s), selected: _slot == s, onSelected: (_) => setState(() => _slot = s)),
                  ),
              ],
            ),
          ),
        ),
        Padding(
          padding: const EdgeInsetsDirectional.symmetric(horizontal: AuSpacing.s5),
          child: Row(
            children: [
              Expanded(
                child: AuTextField(
                  controller: _query,
                  hint: l10n.searchFoods,
                  prefixIcon: Icons.search_rounded,
                  onChanged: (v) {
                    if (_tabs.index != 0) _tabs.animateTo(0);
                    _onQuery(v);
                  },
                ),
              ),
              const SizedBox(width: AuSpacing.s2),
              AuIconButton(icon: Icons.qr_code_scanner_rounded, tooltip: l10n.scanBarcode, filled: true, onPressed: _busy ? null : _scan),
              const SizedBox(width: AuSpacing.s2),
              AuIconButton(icon: Icons.photo_camera_outlined, tooltip: l10n.snapMeal, filled: true, onPressed: _busy ? null : _photo),
            ],
          ),
        ),
        if (_busy) const LinearProgressIndicator(minHeight: 2),
        TabBar(
          controller: _tabs,
          isScrollable: true,
          tabAlignment: TabAlignment.start,
          tabs: [Tab(text: l10n.search), Tab(text: l10n.recent), Tab(text: l10n.favourites), Tab(text: l10n.quickAdd)],
        ),
        Expanded(
          child: TabBarView(
            controller: _tabs,
            children: [
              _results == null
                  ? AuEmptyState(icon: Icons.search_rounded, title: l10n.searchFoods, message: l10n.searchFoodsHint, compact: true)
                  : _results!.when(
                      loading: () => const AuSkeletonList(header: false, count: 5),
                      error: (e, _) => AuErrorState(error: e, compact: true, onRetry: () => _onQuery(_query.text)),
                      data: (foods) => foods.isEmpty
                          ? AuEmptyState(
                              icon: Icons.search_off_rounded,
                              title: l10n.noFoodsFound,
                              message: l10n.noFoodsFoundHint,
                              actionLabel: l10n.quickAdd,
                              onAction: () => _tabs.animateTo(3),
                              compact: true,
                            )
                          : _FoodList(foods: foods, onTap: _pickFood),
                    ),
              _RecentTab(onTap: (f) => _pickFood(f, source: 'search')),
              _RecentTab(onTap: (f) => _pickFood(f, source: 'search'), favouritesOnly: true),
              _QuickAddForm(date: widget.date, slot: _slot),
            ],
          ),
        ),
        SizedBox(height: MediaQuery.paddingOf(context).bottom, child: ColoredBox(color: au.surfaceRaised)),
      ],
    );
  }
}

class _FoodList extends ConsumerWidget {
  const _FoodList({required this.foods, required this.onTap});

  final List<Food> foods;
  final void Function(Food) onTap;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final au = context.au;
    return ListView.builder(
      itemCount: foods.length,
      itemBuilder: (context, i) {
        final f = foods[i];
        return AuListRow(
          title: f.name,
          subtitle: [
            if (f.brand != null) f.brand!,
            '${context.formatNumber(f.per100g.kcal, maxDecimals: 0)} ${context.core.unitKcal} / 100 ${context.core.unitG}',
          ].join(' · '),
          leading: f.isVerified ? Icon(Icons.verified_rounded, color: au.accent, size: 20) : Icon(Icons.restaurant_rounded, color: au.textMuted, size: 20),
          trailing: IconButton(
            tooltip: context.l10n.favourites,
            icon: Icon(f.isFavorite ? Icons.favorite_rounded : Icons.favorite_border_rounded, color: f.isFavorite ? au.protein : au.textMuted),
            onPressed: () async {
              try {
                await ref.read(nutritionRepositoryProvider).toggleFavorite(f.id);
                ref.invalidate(recentFoodsProvider);
              } on ApiFailure catch (e) {
                if (context.mounted) showError(context, e);
              }
            },
          ),
          onTap: () => onTap(f),
        );
      },
    );
  }
}

class _RecentTab extends ConsumerWidget {
  const _RecentTab({required this.onTap, this.favouritesOnly = false});

  final void Function(Food) onTap;
  final bool favouritesOnly;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final recent = ref.watch(recentFoodsProvider);
    return recent.when(
      loading: () => const AuSkeletonList(header: false, count: 5),
      error: (e, _) => AuErrorState(error: e, compact: true, onRetry: () => ref.invalidate(recentFoodsProvider)),
      data: (fetched) {
        final foods = favouritesOnly ? fetched.value.where((f) => f.isFavorite).toList() : fetched.value;
        if (foods.isEmpty) {
          return AuEmptyState(
            icon: favouritesOnly ? Icons.favorite_border_rounded : Icons.history_rounded,
            title: favouritesOnly ? l10n.noFavouritesTitle : l10n.noRecentTitle,
            message: favouritesOnly ? l10n.noFavouritesHint : l10n.noRecentHint,
            compact: true,
          );
        }
        return _FoodList(foods: foods, onTap: onTap);
      },
    );
  }
}

class _QuickAddForm extends ConsumerStatefulWidget {
  const _QuickAddForm({required this.date, required this.slot});

  final DateTime date;
  final String slot;

  @override
  ConsumerState<_QuickAddForm> createState() => _QuickAddFormState();
}

class _QuickAddFormState extends ConsumerState<_QuickAddForm> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _kcal = TextEditingController();
  final _protein = TextEditingController();
  final _carbs = TextEditingController();
  final _fat = TextEditingController();

  @override
  void dispose() {
    for (final c in [_name, _kcal, _protein, _carbs, _fat]) {
      c.dispose();
    }
    super.dispose();
  }

  double _num(TextEditingController c) => double.tryParse(c.text.replaceAll(',', '.')) ?? 0;

  Future<void> _save() async {
    if (!(_form.currentState?.validate() ?? false)) return;
    final p = _num(_protein), c = _num(_carbs), f = _num(_fat);
    final kcal = _kcal.text.trim().isEmpty ? p * 4 + c * 4 + f * 9 : _num(_kcal);
    await ref.read(nutritionDayProvider(widget.date).notifier).log(
          slot: widget.slot,
          name: _name.text.trim().isEmpty ? context.l10n.quickAdd : _name.text.trim(),
          quantity: 1,
          unit: 'serving',
          macros: Macros(kcal: kcal, proteinG: p, carbsG: c, fatG: f),
          source: 'quick',
        );
    if (mounted) Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final core = context.core;
    Widget numberField(TextEditingController c, String label, String unit) => AuTextField(
          controller: c,
          label: '$label ($unit)',
          keyboardType: const TextInputType.numberWithOptions(decimal: true),
          inputFormatters: [FilteringTextInputFormatter.allow(RegExp(r'[0-9.,]'))],
          textDirection: TextDirection.ltr,
        );
    return Form(
      key: _form,
      child: ListView(
        padding: const EdgeInsetsDirectional.all(AuSpacing.s5),
        children: [
          AuTextField(controller: _name, label: context.l10n.foodName, hint: context.l10n.foodNameHint),
          const SizedBox(height: AuSpacing.s3),
          numberField(_kcal, core.calories, core.unitKcal),
          const SizedBox(height: AuSpacing.s3),
          Row(
            children: [
              Expanded(child: numberField(_protein, core.protein, core.unitG)),
              const SizedBox(width: AuSpacing.s2),
              Expanded(child: numberField(_carbs, core.carbs, core.unitG)),
              const SizedBox(width: AuSpacing.s2),
              Expanded(child: numberField(_fat, core.fat, core.unitG)),
            ],
          ),
          const SizedBox(height: AuSpacing.s2),
          Text(context.l10n.quickAddHint, style: context.au.text.caption.copyWith(color: context.au.textMuted)),
          const SizedBox(height: AuSpacing.s5),
          AuButton(label: context.l10n.logFood, onPressed: _save),
        ],
      ),
    );
  }
}

/// Portion picker for a food (serving unit + quantity, live macros).
Future<bool?> showPortionSheet(
  BuildContext context,
  WidgetRef ref, {
  required Food food,
  required DateTime date,
  required String slot,
  required String source,
}) {
  return showAuBottomSheet<bool>(
    context: context,
    title: food.name,
    builder: (_) => _PortionView(food: food, date: date, slot: slot, source: source),
  );
}

class _PortionView extends ConsumerStatefulWidget {
  const _PortionView({required this.food, required this.date, required this.slot, required this.source});

  final Food food;
  final DateTime date;
  final String slot;
  final String source;

  @override
  ConsumerState<_PortionView> createState() => _PortionViewState();
}

class _PortionViewState extends ConsumerState<_PortionView> {
  late FoodServing? _serving = widget.food.servings.firstOrNull;
  late double _qty = _serving == null ? 100 : 1;

  double get _grams => _serving == null ? _qty : _qty * _serving!.grams;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final core = context.core;
    final macros = widget.food.macrosFor(_grams);
    return Padding(
      padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, AuSpacing.s2, AuSpacing.s5, AuSpacing.s6),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (widget.food.servings.isNotEmpty)
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  AuChip(
                    label: '100 ${core.unitG}',
                    selected: _serving == null,
                    onSelected: (_) => setState(() {
                      _serving = null;
                      _qty = 100;
                    }),
                  ),
                  for (final s in widget.food.servings)
                    Padding(
                      padding: const EdgeInsetsDirectional.only(start: AuSpacing.s2),
                      child: AuChip(
                        label: s.label ?? s.unit,
                        selected: _serving == s,
                        onSelected: (_) => setState(() {
                          _serving = s;
                          _qty = 1;
                        }),
                      ),
                    ),
                ],
              ),
            ),
          const SizedBox(height: AuSpacing.s4),
          Center(
            child: AuStepper(
              value: _qty,
              step: _serving == null ? 10 : 0.5,
              min: _serving == null ? 5 : 0.5,
              max: _serving == null ? 2000 : 20,
              unit: _serving == null ? core.unitG : (_serving!.label ?? _serving!.unit),
              onChanged: (v) => setState(() => _qty = v),
            ),
          ),
          const SizedBox(height: AuSpacing.s5),
          AuCard(
            variant: AuCardVariant.sunken,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _MacroValue(label: core.calories, value: context.formatNumber(macros.kcal, maxDecimals: 0), color: au.accent),
                _MacroValue(label: core.protein, value: '${context.formatNumber(macros.proteinG)}${core.unitG}', color: au.protein),
                _MacroValue(label: core.carbs, value: '${context.formatNumber(macros.carbsG)}${core.unitG}', color: au.carbs),
                _MacroValue(label: core.fat, value: '${context.formatNumber(macros.fatG)}${core.unitG}', color: au.fat),
              ],
            ),
          ),
          const SizedBox(height: AuSpacing.s5),
          AuButton(
            label: context.l10n.addToMealName(slotLabel(context, widget.slot)),
            onPressed: () async {
              await ref.read(nutritionDayProvider(widget.date).notifier).log(
                    slot: widget.slot,
                    name: widget.food.name,
                    quantity: _serving == null ? _grams : _qty,
                    unit: _serving == null ? 'g' : _serving!.unit,
                    macros: macros,
                    source: widget.source,
                    foodId: widget.food.id,
                  );
              if (context.mounted) Navigator.of(context).pop(true);
            },
          ),
        ],
      ),
    );
  }
}

class _MacroValue extends StatelessWidget {
  const _MacroValue({required this.label, required this.value, required this.color});

  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Column(
      children: [
        Text(value, style: au.text.title.copyWith(color: color)),
        Text(label, style: au.text.caption.copyWith(color: au.textMuted)),
      ],
    );
  }
}

/// AI photo recognition → confirm candidates (nothing is logged until the
/// user confirms).
Future<void> showPhotoCandidatesSheet(
  BuildContext context,
  WidgetRef ref, {
  required PhotoRecognition result,
  required DateTime date,
  required String slot,
}) {
  return showAuBottomSheet<void>(
    context: context,
    title: context.l10n.confirmMeal,
    builder: (_) => _CandidatesView(result: result, date: date, slot: slot),
  );
}

class _CandidatesView extends ConsumerStatefulWidget {
  const _CandidatesView({required this.result, required this.date, required this.slot});

  final PhotoRecognition result;
  final DateTime date;
  final String slot;

  @override
  ConsumerState<_CandidatesView> createState() => _CandidatesViewState();
}

class _CandidatesViewState extends ConsumerState<_CandidatesView> {
  late final List<bool> _selected = [for (final c in widget.result.candidates) c.confidence >= 0.5];
  late final List<double> _grams = [for (final c in widget.result.candidates) c.grams];
  bool _saving = false;

  Macros _macrosFor(int i) {
    final c = widget.result.candidates[i];
    final factor = c.grams <= 0 ? 1.0 : _grams[i] / c.grams;
    return Macros(kcal: c.kcal, proteinG: c.proteinG, carbsG: c.carbsG, fatG: c.fatG).scale(factor);
  }

  Future<void> _confirm() async {
    setState(() => _saving = true);
    final controller = ref.read(nutritionDayProvider(widget.date).notifier);
    for (var i = 0; i < widget.result.candidates.length; i++) {
      if (!_selected[i]) continue;
      final c = widget.result.candidates[i];
      await controller.log(
        slot: widget.slot,
        name: c.food?.name ?? c.name,
        quantity: _grams[i],
        unit: 'g',
        macros: _macrosFor(i),
        source: 'photo',
        foodId: c.food?.id,
      );
    }
    if (mounted) Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final candidates = widget.result.candidates;
    if (candidates.isEmpty) {
      return AuEmptyState(
        icon: Icons.no_photography_outlined,
        title: context.l10n.noFoodRecognized,
        message: context.l10n.noFoodRecognizedHint,
        compact: true,
      );
    }
    return ListView(
      shrinkWrap: true,
      padding: const EdgeInsetsDirectional.fromSTEB(AuSpacing.s5, 0, AuSpacing.s5, AuSpacing.s6),
      children: [
        Text(context.l10n.confirmMealHint, style: au.text.bodySmall.copyWith(color: au.textSecondary)),
        const SizedBox(height: AuSpacing.s3),
        for (var i = 0; i < candidates.length; i++)
          Padding(
            padding: const EdgeInsetsDirectional.only(bottom: AuSpacing.s2),
            child: AuCard(
              padding: const EdgeInsetsDirectional.all(AuSpacing.s3),
              child: Row(
                children: [
                  Checkbox(value: _selected[i], onChanged: (v) => setState(() => _selected[i] = v ?? false)),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(candidates[i].food?.name ?? candidates[i].name, style: au.text.title),
                        Text(
                          '${context.formatNumber(_macrosFor(i).kcal, maxDecimals: 0)} ${context.core.unitKcal} · ${context.l10n.confidence((candidates[i].confidence * 100).round())}',
                          style: au.text.caption.copyWith(color: au.textMuted),
                        ),
                      ],
                    ),
                  ),
                  AuStepper(
                    value: _grams[i],
                    step: 10,
                    min: 10,
                    max: 1500,
                    unit: context.core.unitG,
                    onChanged: (v) => setState(() => _grams[i] = v),
                  ),
                ],
              ),
            ),
          ),
        const SizedBox(height: AuSpacing.s3),
        AuButton(label: context.l10n.logSelected, loading: _saving, onPressed: _selected.contains(true) ? _confirm : null),
      ],
    );
  }
}
