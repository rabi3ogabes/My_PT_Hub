import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../network/api_client.dart';
import '../offline/connectivity.dart';
import '../offline/database.dart';
import '../offline/session_store.dart';
import '../offline/sync_engine.dart';
import '../repositories/auth_repository.dart';
import '../repositories/client_repositories.dart';
import '../repositories/messaging_repository.dart';
import '../services/analytics.dart';
import '../services/firebase_services.dart';
import '../services/local_notifications.dart';
import '../services/platform_services.dart';
import '../storage/token_store.dart';
import '../util/clock.dart';
import 'auth_controller.dart';
import 'preferences_controllers.dart';

/// Build-time API configuration. Must be overridden by each app.
final apiConfigProvider = Provider<ApiConfig>((ref) => throw StateError('apiConfigProvider must be overridden'));

/// Platform services (Firebase or no-op). Override with the initialized set.
final servicesProvider = Provider<AuServices>((ref) => const AuServices());

final analyticsProvider = Provider<AnalyticsService>((ref) => ref.watch(servicesProvider).analytics);
final crashReporterProvider = Provider<CrashReporter>((ref) => ref.watch(servicesProvider).crash);
final pushServiceProvider = Provider<PushService>((ref) => ref.watch(servicesProvider).push);
final remoteConfigProvider = Provider<RemoteConfigService>((ref) => ref.watch(servicesProvider).remoteConfig);
final localNotifierProvider = Provider<LocalNotifier>((ref) => const NoopLocalNotifier());

final clockProvider = Provider<Clock>((ref) => const SystemClock());

final tokenStoreProvider = Provider<TokenStore>((ref) => SecureTokenStore());

/// The local database. Apps override with `AuDatabase.open()`; tests with an
/// in-memory executor.
final databaseProvider = Provider<AuDatabase>((ref) {
  final db = AuDatabase.open();
  ref.onDispose(db.close);
  return db;
});

final connectivityProvider = Provider<ConnectivitySource>((ref) => PlusConnectivitySource());

/// Mutable request context read lazily by interceptors (tenant switches and
/// locale changes apply to the next request without rebuilding Dio).
class RequestContext {
  String localeCode = 'en';
  String? tenantId;
}

final requestContextProvider = Provider<RequestContext>((ref) {
  final context = RequestContext();
  context.localeCode = ref.read(effectiveLocaleProvider).languageCode;
  ref.listen(effectiveLocaleProvider, (_, next) => context.localeCode = next.languageCode);
  return context;
});

final apiClientProvider = Provider<ApiClient>((ref) {
  final context = ref.watch(requestContextProvider);
  return ApiClient(
    config: ref.watch(apiConfigProvider),
    tokenStore: ref.watch(tokenStoreProvider),
    clock: ref.watch(clockProvider),
    locale: () => context.localeCode,
    tenantId: () => context.tenantId,
    onSessionExpired: () => ref.read(authControllerProvider.notifier).onSessionExpired(),
  );
});

final syncEngineProvider = Provider<SyncEngine>((ref) {
  final engine = SyncEngine(
    db: ref.watch(databaseProvider),
    api: HttpSyncApi(ref.watch(apiClientProvider)),
    connectivity: ref.watch(connectivityProvider),
    clock: ref.watch(clockProvider),
  );
  ref.onDispose(engine.dispose);
  return engine;
});

final syncStatusProvider = StreamProvider<SyncStatus>((ref) async* {
  final engine = ref.watch(syncEngineProvider);
  yield engine.currentStatus;
  yield* engine.status;
});

/// Live reachability.
final onlineProvider = StreamProvider<bool>((ref) async* {
  final source = ref.watch(connectivityProvider);
  yield await source.isOnline();
  yield* source.onlineChanges;
});

final sessionStoreProvider = Provider<SessionStore>(
  (ref) => SessionStore(ref.watch(databaseProvider), ref.watch(syncEngineProvider), clock: ref.watch(clockProvider)),
);

// ---------------------------------------------------------------- Repositories

final authRepositoryProvider =
    Provider<AuthRepository>((ref) => AuthRepository(ref.watch(apiClientProvider), ref.watch(tokenStoreProvider)));

final todayRepositoryProvider =
    Provider<TodayRepository>((ref) => TodayRepository(ref.watch(apiClientProvider), ref.watch(databaseProvider)));

final workoutRepositoryProvider =
    Provider<WorkoutRepository>((ref) => WorkoutRepository(ref.watch(apiClientProvider), ref.watch(databaseProvider)));

final nutritionRepositoryProvider = Provider<NutritionRepository>(
  (ref) => NutritionRepository(ref.watch(apiClientProvider), ref.watch(databaseProvider), ref.watch(syncEngineProvider)),
);

final habitRepositoryProvider = Provider<HabitRepository>(
  (ref) => HabitRepository(ref.watch(apiClientProvider), ref.watch(databaseProvider), ref.watch(syncEngineProvider)),
);

final progressRepositoryProvider = Provider<ProgressRepository>(
  (ref) => ProgressRepository(ref.watch(apiClientProvider), ref.watch(databaseProvider), ref.watch(syncEngineProvider)),
);

final messagingRepositoryProvider = Provider<MessagingRepository>(
  (ref) => MessagingRepository(ref.watch(apiClientProvider), ref.watch(databaseProvider), ref.watch(syncEngineProvider)),
);

final calendarRepositoryProvider =
    Provider<CalendarRepository>((ref) => CalendarRepository(ref.watch(apiClientProvider), ref.watch(databaseProvider)));

final shopRepositoryProvider =
    Provider<ShopRepository>((ref) => ShopRepository(ref.watch(apiClientProvider), ref.watch(databaseProvider)));

final notificationRepositoryProvider = Provider<NotificationRepository>(
  (ref) => NotificationRepository(ref.watch(apiClientProvider), ref.watch(databaseProvider)),
);

final communityRepositoryProvider =
    Provider<CommunityRepository>((ref) => CommunityRepository(ref.watch(apiClientProvider), ref.watch(databaseProvider)));

final integrationRepositoryProvider = Provider<IntegrationRepository>(
  (ref) => IntegrationRepository(ref.watch(apiClientProvider), ref.watch(databaseProvider)),
);
