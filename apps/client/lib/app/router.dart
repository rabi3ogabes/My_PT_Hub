import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/auth/invite_screen.dart';
import '../features/auth/magic_link_screens.dart';
import '../features/auth/sign_in_screen.dart';
import '../features/auth/sign_up_screen.dart';
import '../features/auth/two_factor_screen.dart';
import '../features/calendar/calendar_screen.dart';
import '../features/checkins/checkin_flow_screen.dart';
import '../features/community/community_screen.dart';
import '../features/habits/habits_screen.dart';
import '../features/logger/finish_screen.dart';
import '../features/logger/logger_screen.dart';
import '../features/messages/conversations_screen.dart';
import '../features/messages/thread_screen.dart';
import '../features/more/more_screen.dart';
import '../features/notifications/notifications_screen.dart';
import '../features/nutrition/nutrition_screen.dart';
import '../features/onboarding/language_picker_screen.dart';
import '../features/onboarding/onboarding_screen.dart';
import '../features/onboarding/splash_screen.dart';
import '../features/progress/photo_compare_screen.dart';
import '../features/progress/progress_screen.dart';
import '../features/settings/devices_screen.dart';
import '../features/settings/notification_settings_screen.dart';
import '../features/settings/privacy_screen.dart';
import '../features/settings/profile_screen.dart';
import '../features/settings/settings_screen.dart';
import '../features/shell/home_shell.dart';
import '../features/shop/shop_screen.dart';
import '../features/today/today_screen.dart';
import '../features/train/exercise_progress_screen.dart';
import '../features/train/history_screen.dart';
import '../features/train/records_screen.dart';
import '../features/train/train_screen.dart';
import '../features/train/workout_preview_screen.dart';

abstract final class Routes {
  static const splash = '/splash';
  static const welcome = '/welcome';
  static const language = '/language';
  static const signIn = '/auth/sign-in';
  static const signUp = '/auth/sign-up';
  static const magicLink = '/auth/magic';
  static const twoFactor = '/auth/two-factor';
  static const invite = '/invite';
  static const today = '/today';
  static const train = '/train';
  static const nutrition = '/nutrition';
  static const progress = '/progress';
  static const more = '/more';
  static const logger = '/logger';
  static const finish = '/logger/finish';
  static const messages = '/messages';
  static const calendar = '/calendar';
  static const community = '/community';
  static const shop = '/shop';
  static const habits = '/habits';
  static const checkins = '/checkins';
  static const notifications = '/notifications';
  static const settings = '/settings';

  static String workout(String id, {String? dayId, String? assignmentId}) => Uri(
        path: '/train/workout/$id',
        queryParameters: {'day': ?dayId, 'assignment': ?assignmentId},
      ).toString();
  static String exercise(String id, String name) => Uri(path: '/train/exercise/$id', queryParameters: {'name': name}).toString();
  static String thread(String id) => '/messages/$id';
  static String checkin(String id) => '/checkins/$id';
  static String challenge(String id) => '/community/challenge/$id';
}

final _rootKey = GlobalKey<NavigatorState>(debugLabel: 'root');

final routerProvider = Provider<GoRouter>((ref) {
  final refresh = RouterRefresh();
  ref.listen(authControllerProvider.select((s) => s.status), (_, _) => refresh.refresh());
  ref.onDispose(refresh.dispose);

  Page<void> modal(GoRouterState state, Widget child) =>
      MaterialPage<void>(key: state.pageKey, fullscreenDialog: true, child: child);

  final router = GoRouter(
    navigatorKey: _rootKey,
    initialLocation: Routes.splash,
    refreshListenable: refresh,
    redirect: (context, state) => authRedirect(
      ref.read(authControllerProvider).status,
      state,
      splashPath: Routes.splash,
      signInPath: ref.read(appPrefsProvider).onboardingSeen ? Routes.signIn : Routes.welcome,
      homePath: Routes.today,
      publicPaths: const [Routes.welcome, Routes.language, '/auth', Routes.invite],
      signedInAllowedPublicPaths: const [Routes.invite, Routes.language],
    ),
    routes: [
      GoRoute(path: Routes.splash, builder: (_, _) => const SplashScreen()),
      GoRoute(path: Routes.welcome, builder: (_, _) => const OnboardingScreen()),
      GoRoute(path: Routes.language, builder: (_, _) => const LanguagePickerScreen()),
      GoRoute(path: Routes.signIn, builder: (_, s) => SignInScreen(from: s.uri.queryParameters['from'])),
      GoRoute(path: Routes.signUp, builder: (_, s) => SignUpScreen(inviteCode: s.uri.queryParameters['code'])),
      GoRoute(path: Routes.magicLink, builder: (_, _) => const MagicLinkRequestScreen()),
      GoRoute(path: '${Routes.magicLink}/:token', builder: (_, s) => MagicLinkVerifyScreen(token: s.pathParameters['token']!)),
      GoRoute(path: Routes.twoFactor, builder: (_, s) => TwoFactorScreen(challenge: s.uri.queryParameters['challenge'] ?? '')),
      GoRoute(path: '${Routes.invite}/:code', builder: (_, s) => InviteScreen(code: s.pathParameters['code']!)),
      StatefulShellRoute.indexedStack(
        builder: (_, _, shell) => HomeShell(shell: shell),
        branches: [
          StatefulShellBranch(routes: [GoRoute(path: Routes.today, builder: (_, _) => const TodayScreen())]),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: Routes.train,
                builder: (_, _) => const TrainScreen(),
                routes: [
                  GoRoute(
                    path: 'workout/:id',
                    builder: (_, s) => WorkoutPreviewScreen(
                      workoutId: s.pathParameters['id']!,
                      programDayId: s.uri.queryParameters['day'],
                      assignmentId: s.uri.queryParameters['assignment'],
                    ),
                  ),
                  GoRoute(path: 'history', builder: (_, _) => const HistoryScreen()),
                  GoRoute(path: 'records', builder: (_, _) => const RecordsScreen()),
                  GoRoute(
                    path: 'exercise/:id',
                    builder: (_, s) => ExerciseProgressScreen(
                      exerciseId: s.pathParameters['id']!,
                      name: s.uri.queryParameters['name'] ?? '',
                    ),
                  ),
                ],
              ),
            ],
          ),
          StatefulShellBranch(routes: [GoRoute(path: Routes.nutrition, builder: (_, _) => const NutritionScreen())]),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: Routes.progress,
                builder: (_, _) => const ProgressScreen(),
                routes: [GoRoute(path: 'compare', builder: (_, _) => const PhotoCompareScreen())],
              ),
            ],
          ),
          StatefulShellBranch(routes: [GoRoute(path: Routes.more, builder: (_, _) => const MoreScreen())]),
        ],
      ),
      GoRoute(
        parentNavigatorKey: _rootKey,
        path: Routes.logger,
        pageBuilder: (_, s) => modal(s, const LoggerScreen()),
        routes: [GoRoute(parentNavigatorKey: _rootKey, path: 'finish', builder: (_, _) => const FinishScreen())],
      ),
      GoRoute(
        parentNavigatorKey: _rootKey,
        path: Routes.messages,
        builder: (_, _) => const ConversationsScreen(),
        routes: [GoRoute(path: ':id', builder: (_, s) => ThreadScreen(conversationId: s.pathParameters['id']!))],
      ),
      GoRoute(parentNavigatorKey: _rootKey, path: Routes.calendar, builder: (_, _) => const CalendarScreen()),
      GoRoute(
        parentNavigatorKey: _rootKey,
        path: Routes.community,
        builder: (_, _) => const CommunityScreen(),
        routes: [GoRoute(path: 'challenge/:id', builder: (_, s) => ChallengeScreen(challengeId: s.pathParameters['id']!))],
      ),
      GoRoute(
        parentNavigatorKey: _rootKey,
        path: Routes.shop,
        builder: (_, _) => const ShopScreen(),
        routes: [GoRoute(path: 'purchases', builder: (_, _) => const PurchasesScreen())],
      ),
      GoRoute(parentNavigatorKey: _rootKey, path: Routes.habits, builder: (_, _) => const HabitsScreen()),
      GoRoute(
        parentNavigatorKey: _rootKey,
        path: '${Routes.checkins}/:id',
        pageBuilder: (_, s) => modal(s, CheckinFlowScreen(checkinId: s.pathParameters['id']!)),
      ),
      GoRoute(parentNavigatorKey: _rootKey, path: Routes.notifications, builder: (_, _) => const NotificationsScreen()),
      GoRoute(
        parentNavigatorKey: _rootKey,
        path: Routes.settings,
        builder: (_, _) => const SettingsScreen(),
        routes: [
          GoRoute(path: 'profile', builder: (_, _) => const ProfileScreen()),
          GoRoute(path: 'notifications', builder: (_, _) => const NotificationSettingsScreen()),
          GoRoute(path: 'devices', builder: (_, _) => const DevicesScreen()),
          GoRoute(path: 'privacy', builder: (_, _) => const PrivacyScreen()),
          GoRoute(path: 'language', builder: (_, _) => const LanguagePickerScreen(fromSettings: true)),
        ],
      ),
    ],
  );
  ref.onDispose(router.dispose);
  return router;
});
