import 'package:aurum_core/aurum_core.dart';
import 'package:aurum_core/testing.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter_test/flutter_test.dart';

/// A gallery of the core components, rendered in LTR + RTL, light + dark.
class _Gallery extends StatelessWidget {
  const _Gallery();

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = AuL10n.of(context);
    return Scaffold(
      body: SingleChildScrollView(
        padding: const EdgeInsetsDirectional.all(AuSpacing.s5),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            AuSectionHeader(overline: l10n.brandName, title: l10n.today, actionLabel: l10n.seeAll, onAction: () {}),
            AuButton(label: l10n.continueLabel, icon: Icons.play_arrow_rounded, onPressed: () {}),
            const SizedBox(height: AuSpacing.s3),
            Row(
              children: [
                Expanded(child: AuButton.secondary(label: l10n.cancel, onPressed: () {})),
                const SizedBox(width: AuSpacing.s3),
                Expanded(child: AuButton.destructive(label: l10n.delete, onPressed: () {})),
              ],
            ),
            const SizedBox(height: AuSpacing.s3),
            Row(
              children: [
                AuButton.ghost(label: l10n.skip, onPressed: () {}),
                const Spacer(),
                const SizedBox(width: 140, child: AuButton(label: 'Loading', loading: true, onPressed: null)),
              ],
            ),
            const SizedBox(height: AuSpacing.s4),
            AuTextField(label: l10n.email, hint: 'name@example.com', prefixIcon: Icons.mail_outline_rounded),
            const SizedBox(height: AuSpacing.s3),
            AuTextField(label: l10n.password, obscure: true, initialValue: 'secret', errorText: l10n.passwordTooShort),
            const SizedBox(height: AuSpacing.s4),
            Row(
              children: [
                Expanded(child: AuStatTile(label: l10n.weight, value: '82.4', unit: l10n.unitKg, icon: Icons.monitor_weight_outlined, trend: '-0.6')),
                const SizedBox(width: AuSpacing.s3),
                Expanded(child: AuStatTile(label: l10n.calories, value: '1,840', unit: l10n.unitKcal, icon: Icons.bolt_rounded)),
              ],
            ),
            const SizedBox(height: AuSpacing.s4),
            AuCard(
              variant: AuCardVariant.hero,
              child: Row(
                children: [
                  AuMacroRings(
                    size: 120,
                    consumed: const Macros(kcal: 1840, proteinG: 150, carbsG: 160, fatG: 70),
                    targets: const Macros(kcal: 2400, proteinG: 180, carbsG: 250, fatG: 70),
                    center: Text('1840', style: au.text.title),
                  ),
                  const SizedBox(width: AuSpacing.s4),
                  Expanded(
                    child: Column(
                      children: [
                        AuMacroBar(label: l10n.protein, value: 150, target: 180, color: au.protein, valueLabel: '150 / 180 g'),
                        const SizedBox(height: AuSpacing.s3),
                        AuMacroBar(label: l10n.fat, value: 70, target: 70, color: au.fat, valueLabel: '70 / 70 g'),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AuSpacing.s4),
            AuCard(
              padding: EdgeInsets.zero,
              child: Column(
                children: [
                  AuListRow(
                    leading: const AuAvatar(name: 'Sara Haddad', ring: true),
                    title: 'Sara Haddad',
                    subtitle: l10n.notificationsBadge(3),
                    trailing: AuBadge.count(3),
                    onTap: () {},
                  ),
                  const Divider(),
                  AuListRow(
                    leading: const AuIconTile(icon: Icons.local_fire_department_rounded),
                    title: l10n.streakDays(12),
                    trailing: const AuStreakFlame(count: 12),
                    onTap: () {},
                  ),
                ],
              ),
            ),
            const SizedBox(height: AuSpacing.s4),
            Wrap(
              spacing: AuSpacing.s2,
              children: [
                AuChip(label: l10n.protein, selected: true, onSelected: (_) {}),
                AuChip(label: l10n.carbs, onSelected: (_) {}, icon: Icons.grain_rounded),
                const AuBadge(label: 'PR', tone: AuBadgeTone.accent),
                const AuBadge(label: 'NEW', tone: AuBadgeTone.success, solid: false),
              ],
            ),
            const SizedBox(height: AuSpacing.s4),
            AuSegmented<int>(segments: {0: l10n.themeLight, 1: l10n.themeDark, 2: l10n.themeSystem}, selected: 1, onChanged: (_) {}),
            const SizedBox(height: AuSpacing.s4),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                AuStepper(value: 82.5, step: 2.5, unit: l10n.unitKg, onChanged: (_) {}),
                AuTimerDisplay(time: const Duration(seconds: 75), total: const Duration(seconds: 90), size: 120, label: 'Rest'),
              ],
            ),
            const SizedBox(height: AuSpacing.s4),
            AuCard(
              padding: const EdgeInsetsDirectional.all(AuSpacing.s2),
              child: Column(
                children: [
                  AuSetRow(setNumber: 1, setType: SetType.warmup, previous: '60 × 10', weight: 60, reps: 10, completed: true, onToggleComplete: () {}),
                  AuSetRow(setNumber: 2, previous: '80 × 8', weight: 82.5, reps: 8, active: true, rpe: 8, onRpeTap: () {}, onToggleComplete: () {}),
                  AuSetRow(setNumber: 3, previous: '80 × 7', weight: 82.5, repsHint: '6–8', onToggleComplete: () {}),
                ],
              ),
            ),
            const SizedBox(height: AuSpacing.s4),
            const SizedBox(
              height: 60,
              child: AuShimmer(
                child: Row(
                  children: [
                    AuSkeleton.circle(size: 44),
                    SizedBox(width: AuSpacing.s3),
                    Expanded(child: AuSkeleton(height: 16)),
                  ],
                ),
              ),
            ),
            AuEmptyState(
              compact: true,
              icon: Icons.restaurant_rounded,
              title: l10n.today,
              message: l10n.offlineBanner,
              actionLabel: l10n.add,
              onAction: () {},
            ),
          ],
        ),
      ),
    );
  }
}

void main() {
  setUpAll(loadAuFontsForTests);

  final variants = <String, (Locale, Brightness)>{
    'ltr_light': (const Locale('en'), Brightness.light),
    'ltr_dark': (const Locale('en'), Brightness.dark),
    'rtl_light': (const Locale('ar'), Brightness.light),
    'rtl_dark': (const Locale('ar'), Brightness.dark),
  };

  for (final entry in variants.entries) {
    testWidgets('core components — ${entry.key}', (tester) async {
      tester.view.physicalSize = const Size(430 * 3, 1900 * 3);
      tester.view.devicePixelRatio = 3;
      addTearDown(tester.view.reset);
      // Render real (blurred) shadows; restored before the invariant check.
      debugDisableShadows = false;

      await tester.pumpWidget(
        TickerMode(
          enabled: false,
          child: auTestApp(child: const _Gallery(), locale: entry.value.$1, brightness: entry.value.$2),
        ),
      );
      await tester.pump(const Duration(seconds: 1));
      try {
        await expectLater(find.byType(_Gallery), matchesGoldenFile('goldens/components_${entry.key}.png'));
      } finally {
        debugDisableShadows = true;
      }
    });
  }

  testWidgets('RTL mirrors the set row (done button on the left)', (tester) async {
    await tester.pumpWidget(auTestApp(
      locale: const Locale('ar'),
      child: Scaffold(body: AuSetRow(setNumber: 1, previous: '80 × 8', weight: 80, reps: 8, onToggleComplete: () {})),
    ));
    final done = tester.getCenter(find.byIcon(Icons.check_rounded));
    final badge = tester.getCenter(find.text('1'));
    expect(done.dx, lessThan(badge.dx));
  });
}
