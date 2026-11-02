import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../shared/l10n_ext.dart';
import '../messages/messages_providers.dart';

/// Bottom navigation: Today · Train · Nutrition · Progress · More.
class HomeShell extends ConsumerWidget {
  const HomeShell({super.key, required this.shell});

  final StatefulNavigationShell shell;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final au = context.au;
    final unread = ref.watch(unreadMessagesProvider);

    NavigationDestination dest(IconData icon, IconData selected, String label, {int badge = 0}) => NavigationDestination(
          icon: Badge(isLabelVisible: badge > 0, label: Text('$badge'), backgroundColor: au.danger, child: Icon(icon)),
          selectedIcon: Badge(isLabelVisible: badge > 0, label: Text('$badge'), backgroundColor: au.danger, child: Icon(selected)),
          label: label,
          tooltip: label,
        );

    return Scaffold(
      body: shell,
      bottomNavigationBar: DecoratedBox(
        decoration: BoxDecoration(border: Border(top: BorderSide(color: au.border))),
        child: NavigationBar(
          selectedIndex: shell.currentIndex,
          onDestinationSelected: (i) {
            AuHaptics.selection();
            shell.goBranch(i, initialLocation: i == shell.currentIndex);
          },
          destinations: [
            dest(Icons.wb_sunny_outlined, Icons.wb_sunny_rounded, l10n.navToday),
            dest(Icons.fitness_center_outlined, Icons.fitness_center_rounded, l10n.navTrain),
            dest(Icons.restaurant_outlined, Icons.restaurant_rounded, l10n.navNutrition),
            dest(Icons.insights_outlined, Icons.insights_rounded, l10n.navProgress),
            dest(Icons.grid_view_outlined, Icons.grid_view_rounded, l10n.navMore, badge: unread),
          ],
        ),
      ),
    );
  }
}
