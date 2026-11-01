import 'package:dio/dio.dart';

import '../models/models.dart';
import '../network/api_client.dart';
import '../network/api_failure.dart';
import '../offline/sync_engine.dart';
import '../util/json_converters.dart';
import '../util/ulid.dart';
import 'base_repository.dart';

/// `GET /me/today`.
class TodayRepository extends BaseRepository {
  TodayRepository(super.api, super.db);

  Future<Fetched<Today>> today(DateTime date) {
    final d = toApiDate(date);
    return cachedGet('today:$d', '/me/today', (data) => Today.fromJson(BaseRepository.asMap(data)), query: {'date': d});
  }
}

/// Programs, workouts, exercise library, history and records.
class WorkoutRepository extends BaseRepository {
  WorkoutRepository(super.api, super.db);

  Future<Fetched<MyProgram?>> myProgram() => cachedGet(
        'program',
        '/me/program',
        (data) => data == null ? null : MyProgram.fromJson(BaseRepository.asMap(data)),
      );

  /// Workout with `last_performance` per exercise; cached for offline logging.
  Future<Fetched<Workout>> workout(String id) =>
      cachedGet('workout:$id', '/me/workouts/$id', (data) => Workout.fromJson(BaseRepository.asMap(data)));

  Future<CursorPage<Exercise>> exercises({String? query, String? muscle, String? equipment, bool favorites = false, String? cursor}) =>
      api.getPage(
        '/exercises',
        Exercise.fromJson,
        cursor: cursor,
        query: {'q': query, 'muscle': muscle, 'equipment': equipment, 'favorites': favorites ? 1 : null},
      );

  Future<Exercise> exercise(String id) async => Exercise.fromJson((await api.get('/exercises/$id')).dataMap);

  Future<LastPerformance?> lastPerformance(String exerciseId) =>
      api.getObject('/me/exercises/$exerciseId/last', LastPerformance.fromJson);

  Future<Fetched<ExerciseHistory>> history(String exerciseId) => cachedGet(
        'history:$exerciseId',
        '/me/exercises/$exerciseId/history',
        (data) => ExerciseHistory.fromJson(BaseRepository.asMap(data)),
      );

  Future<Fetched<List<PersonalRecord>>> records() => cachedGet(
        'records',
        '/me/records',
        (data) => BaseRepository.asList(data).map(PersonalRecord.fromJson).toList(),
      );

  Future<CursorPage<WorkoutSession>> sessions({String? cursor}) =>
      api.getPage('/me/sessions', WorkoutSession.fromJson, cursor: cursor);

  Future<WorkoutSession> session(String id) async => WorkoutSession.fromJson((await api.get('/me/sessions/$id')).dataMap);

  /// `POST /me/sessions/{id}/complete` — idempotent; returns detected PRs.
  Future<WorkoutSession> complete(
    String sessionId, {
    DateTime? endedAt,
    int? perceivedEffort,
    String? mood,
    String? notes,
  }) async {
    final envelope = await api.post(
      '/me/sessions/$sessionId/complete',
      data: compactJson({
        'ended_at': endedAt == null ? null : toApiDateTime(endedAt),
        'perceived_effort': perceivedEffort,
        'mood': mood,
        'notes': notes,
      }),
      options: ApiClient.idempotent,
    );
    return WorkoutSession.fromJson(envelope.dataMap);
  }
}

/// Nutrition: day view, plan, food search, offline-first logging.
class NutritionRepository extends BaseRepository {
  NutritionRepository(super.api, super.db, this.sync);

  final SyncEngine sync;

  Future<Fetched<NutritionDay>> day(DateTime date) {
    final d = toApiDate(date);
    return cachedGet('nutrition:$d', '/me/nutrition', (data) => NutritionDay.fromJson(BaseRepository.asMap(data)),
        query: {'date': d});
  }

  Future<Fetched<MealPlan?>> plan() => cachedGet(
        'meal_plan',
        '/me/nutrition/plan',
        (data) => data == null ? null : MealPlan.fromJson(BaseRepository.asMap(data)),
      );

  Future<CursorPage<Food>> searchFoods(String query, {String? cursor, String? region}) =>
      api.getPage('/foods', Food.fromJson, cursor: cursor, query: {'q': query, 'region': region});

  /// Returns null when the barcode is unknown (404 `not_found`).
  Future<Food?> barcode(String code) async {
    try {
      return Food.fromJson((await api.get('/foods/barcode/${Uri.encodeComponent(code)}')).dataMap);
    } on ApiFailure catch (e) {
      if (e.code == ApiFailure.notFound) return null;
      rethrow;
    }
  }

  Future<Fetched<List<Food>>> recent() => cachedGet(
        'foods:recent',
        '/me/foods/recent',
        (data) => BaseRepository.asList(data).map(Food.fromJson).toList(),
      );

  Future<void> toggleFavorite(String foodId) => api.post('/me/foods/$foodId/favorite');

  /// Logs food locally and queues it for sync. Returns the optimistic log.
  Future<FoodLog> logFood({
    required DateTime date,
    required String slot,
    required String name,
    required double quantity,
    required String unit,
    required Macros macros,
    required String source,
    String? foodId,
    String? recipeId,
  }) async {
    final log = FoodLog(
      id: Ulid.generate(),
      logDate: toApiDate(date),
      slot: slot,
      foodId: foodId,
      recipeId: recipeId,
      name: name,
      quantity: quantity,
      unit: unit,
      kcal: macros.kcal,
      proteinG: macros.proteinG,
      carbsG: macros.carbsG,
      fatG: macros.fatG,
      source: source,
      loggedAt: DateTime.now().toUtc(),
    );
    await sync.enqueue(entity: SyncEntity.foodLog, op: SyncOp.upsert, payload: _foodLogPayload(log));
    return log;
  }

  Future<void> deleteFoodLog(FoodLog log) =>
      sync.enqueue(entity: SyncEntity.foodLog, op: SyncOp.delete, payload: {'id': log.id, 'log_date': log.logDate});

  Future<void> logFromPlan(DateTime date, String slot) =>
      api.post('/me/food-logs/from-plan', data: {'log_date': toApiDate(date), 'slot': slot});

  Future<void> copyDay({required DateTime from, required DateTime to, String? slot}) => api.post(
        '/me/food-logs/copy-day',
        data: compactJson({'from_date': toApiDate(from), 'to_date': toApiDate(to), 'slot': slot}),
      );

  Future<void> logWater(DateTime date, double ml) => sync.enqueue(
        entity: SyncEntity.waterLog,
        op: SyncOp.upsert,
        payload: {'id': Ulid.generate(), 'log_date': toApiDate(date), 'ml': ml},
      );

  Future<PhotoRecognition> recognizePhoto(String filePath) async {
    final form = FormData.fromMap({'photo': await MultipartFile.fromFile(filePath)});
    return PhotoRecognition.fromJson((await api.upload('/me/food-logs/photo', form)).dataMap);
  }

  static Map<String, dynamic> _foodLogPayload(FoodLog log) {
    final json = log.toJson();
    if (log.foodId != null || log.recipeId != null) {
      // Server recomputes macros from the food/recipe.
      json
        ..remove('kcal')
        ..remove('protein_g')
        ..remove('carbs_g')
        ..remove('fat_g');
    }
    json['logged_at'] = toApiDateTime(log.loggedAt ?? DateTime.now());
    return compactJson(json);
  }
}

/// Habits checklist.
class HabitRepository extends BaseRepository {
  HabitRepository(super.api, super.db, this.sync);

  final SyncEngine sync;

  Future<Fetched<List<HabitItem>>> habits(DateTime date) {
    final d = toApiDate(date);
    return cachedGet('habits:$d', '/me/habits', (data) => BaseRepository.asList(data).map(HabitItem.fromJson).toList(),
        query: {'date': d});
  }

  /// One-tap completion: queued offline, idempotent on the server.
  Future<void> log(String assignmentId, DateTime date, {double? value, bool? completed}) => sync.enqueue(
        entity: SyncEntity.habitLog,
        op: SyncOp.upsert,
        payload: compactJson({
          'id': Ulid.generate(),
          'assignment_id': assignmentId,
          'date': toApiDate(date),
          'value': value,
          'completed': completed,
        }),
      );
}

/// Check-ins, body metrics and progress photos.
class ProgressRepository extends BaseRepository {
  ProgressRepository(super.api, super.db, this.sync);

  final SyncEngine sync;

  Future<Fetched<List<CheckinSummary>>> checkins() => cachedGet(
        'checkins',
        '/me/checkins',
        (data) => BaseRepository.asList(data).map(CheckinSummary.fromJson).toList(),
      );

  Future<CheckinDetail> checkin(String id) async => CheckinDetail.fromJson((await api.get('/me/checkins/$id')).dataMap);

  Future<void> submitCheckin(
    String id, {
    required Map<String, dynamic> answers,
    double? weightKg,
    double? bodyFatPct,
    Map<String, double>? measurements,
    List<String>? photoIds,
  }) =>
      api.post(
        '/me/checkins/$id/submit',
        data: compactJson({
          'answers': answers,
          'weight_kg': weightKg,
          'body_fat_pct': bodyFatPct,
          'measurements': (measurements?.isEmpty ?? true) ? null : measurements,
          'photo_ids': (photoIds?.isEmpty ?? true) ? null : photoIds,
        }),
      );

  Future<ProgressPhoto> uploadPhoto({
    required String filePath,
    required String pose,
    required DateTime takenOn,
    String? checkinResponseId,
    void Function(double)? onProgress,
  }) async {
    final form = FormData.fromMap({
      'photo': await MultipartFile.fromFile(filePath),
      'pose': pose,
      'taken_on': toApiDate(takenOn),
      'checkin_response_id': ?checkinResponseId,
    });
    return ProgressPhoto.fromJson((await api.upload('/me/progress-photos', form, onProgress: onProgress)).dataMap);
  }

  /// Body metrics are queued offline.
  Future<void> logBodyMetric({
    required DateTime measuredOn,
    double? weightKg,
    double? bodyFatPct,
    Map<String, double>? measurements,
  }) =>
      sync.enqueue(
        entity: SyncEntity.bodyMetric,
        op: SyncOp.upsert,
        payload: compactJson({
          'id': Ulid.generate(),
          'measured_on': toApiDate(measuredOn),
          'weight_kg': weightKg,
          'body_fat_pct': bodyFatPct,
          'measurements': (measurements?.isEmpty ?? true) ? null : measurements,
        }),
      );

  Future<Fetched<ProgressSummary>> progress() =>
      cachedGet('progress', '/me/progress', (data) => ProgressSummary.fromJson(BaseRepository.asMap(data)));
}

/// Bookings, classes, slots and credits.
class CalendarRepository extends BaseRepository {
  CalendarRepository(super.api, super.db);

  Future<Fetched<List<Booking>>> bookings() => cachedGet(
        'bookings',
        '/me/bookings',
        (data) => BaseRepository.asList(data).map(Booking.fromJson).toList(),
      );

  Future<List<CalendarEvent>> classes({required DateTime from, required DateTime to}) => api.getList(
        '/me/classes',
        CalendarEvent.fromJson,
        query: {'from': toApiDate(from), 'to': toApiDate(to)},
      );

  Future<List<Slot>> slots({String? coachId, required DateTime date}) =>
      api.getList('/me/slots', Slot.fromJson, query: {'coach_id': coachId, 'date': toApiDate(date)});

  Future<Booking> bookClass(String eventId) async =>
      Booking.fromJson((await api.post('/me/bookings', data: {'event_id': eventId})).dataMap);

  Future<Booking> bookSlot({required String coachId, required DateTime startsAt}) async => Booking.fromJson(
        (await api.post('/me/bookings', data: {'coach_id': coachId, 'starts_at': toApiDateTime(startsAt)})).dataMap,
      );

  Future<void> cancel(String bookingId) => api.post('/me/bookings/$bookingId/cancel');

  Future<Booking> claim(String bookingId) async =>
      Booking.fromJson((await api.post('/me/bookings/$bookingId/claim')).dataMap);

  Future<Credits> credits() async => Credits.fromJson((await api.get('/me/credits')).dataMap);
}

/// Client shop and purchases.
class ShopRepository extends BaseRepository {
  ShopRepository(super.api, super.db);

  Future<Fetched<List<Product>>> products() =>
      cachedGet('shop', '/me/shop', (data) => BaseRepository.asList(data).map(Product.fromJson).toList());

  Future<CheckoutResult> checkout(String productId, {required String priceId, String? coupon}) async {
    final envelope = await api.post(
      '/me/shop/$productId/checkout',
      data: compactJson({'price_id': priceId, 'coupon': coupon}),
      options: Options(headers: {'Idempotency-Key': Ulid.generate()}),
    );
    return CheckoutResult.fromJson(envelope.dataMap);
  }

  Future<OrderStatus> orderStatus(String orderId) async =>
      OrderStatus.fromJson((await api.get('/public/orders/$orderId/status', options: ApiClient.public)).dataMap);

  Future<Purchases> purchases() async => Purchases.fromJson((await api.get('/me/purchases')).dataMap);

  Future<void> cancelSubscription(String id) => api.post('/me/subscriptions/$id/cancel');
}

/// Notification centre.
class NotificationRepository extends BaseRepository {
  NotificationRepository(super.api, super.db);

  Future<CursorPage<AppNotification>> page({String? cursor}) =>
      api.getPage('/notifications', AppNotification.fromJson, cursor: cursor);

  Future<void> markRead([List<String>? ids]) =>
      api.post('/notifications/read', data: ids == null ? <String, dynamic>{} : {'ids': ids});
}

/// Community spaces, posts and challenges.
class CommunityRepository extends BaseRepository {
  CommunityRepository(super.api, super.db);

  Future<List<CommunitySpace>> spaces() => api.getList('/community/spaces', CommunitySpace.fromJson);

  Future<CursorPage<Post>> posts(String spaceId, {String? cursor}) =>
      api.getPage('/community/spaces/$spaceId/posts', Post.fromJson, cursor: cursor);

  Future<Post> createPost(String spaceId, String body) async =>
      Post.fromJson((await api.post('/community/spaces/$spaceId/posts', data: {'body': body, 'type': 'text'})).dataMap);

  Future<void> react(String postId, {String emoji = '🔥'}) =>
      api.post('/community/posts/$postId/react', data: {'emoji': emoji});

  Future<CursorPage<PostComment>> comments(String postId, {String? cursor}) =>
      api.getPage('/community/posts/$postId/comments', PostComment.fromJson, cursor: cursor);

  Future<PostComment> comment(String postId, String body) async =>
      PostComment.fromJson((await api.post('/community/posts/$postId/comments', data: {'body': body})).dataMap);

  Future<void> report({required String postId, required String reason}) =>
      api.post('/community/reports', data: {'post_id': postId, 'reason': reason});

  Future<List<Challenge>> challenges() => api.getList('/challenges', Challenge.fromJson);

  Future<Challenge> challenge(String id) async => Challenge.fromJson((await api.get('/challenges/$id')).dataMap);

  Future<void> join(String id) => api.post('/challenges/$id/join');
}

/// Connected health sources (Apple Health / Health Connect / wearables).
class IntegrationRepository extends BaseRepository {
  IntegrationRepository(super.api, super.db);

  Future<List<Map<String, dynamic>>> mine() async => (await api.get('/me/integrations')).dataList;

  Future<void> syncNow(String provider) => api.post('/me/integrations/$provider/sync');

  Future<void> disconnect(String provider) => api.delete('/me/integrations/$provider');

  Future<void> ingestSamples(List<Map<String, dynamic>> samples) =>
      api.post('/me/health/samples', data: {'samples': samples}, options: ApiClient.idempotent);
}
