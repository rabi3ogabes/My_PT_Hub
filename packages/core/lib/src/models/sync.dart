import 'package:freezed_annotation/freezed_annotation.dart';

part 'sync.freezed.dart';
part 'sync.g.dart';

/// Entities accepted by `POST /sync/push`.
abstract final class SyncEntity {
  static const session = 'session';
  static const sessionSet = 'session_set';
  static const foodLog = 'food_log';
  static const waterLog = 'water_log';
  static const habitLog = 'habit_log';
  static const bodyMetric = 'body_metric';
  static const message = 'message';

  static const all = [session, sessionSet, foodLog, waterLog, habitLog, bodyMetric, message];
}

abstract final class SyncOp {
  static const upsert = 'upsert';
  static const delete = 'delete';
}

/// One mutation as sent to the server.
@freezed
abstract class SyncMutation with _$SyncMutation {
  const factory SyncMutation({
    required String id,
    required String entity,
    required String op,
    required Map<String, dynamic> payload,
    required DateTime clientTime,
  }) = _SyncMutation;

  factory SyncMutation.fromJson(Map<String, dynamic> json) => _$SyncMutationFromJson(json);
}

@JsonEnum(fieldRename: FieldRename.snake)
enum SyncResultStatus { applied, duplicate, rejected }

@freezed
abstract class SyncPushResultItem with _$SyncPushResultItem {
  const factory SyncPushResultItem({
    required String id,
    @JsonKey(unknownEnumValue: SyncResultStatus.rejected) required SyncResultStatus status,
    String? error,
  }) = _SyncPushResultItem;

  factory SyncPushResultItem.fromJson(Map<String, dynamic> json) => _$SyncPushResultItemFromJson(json);
}

/// `POST /sync/push` response.
@freezed
abstract class SyncPushResponse with _$SyncPushResponse {
  const factory SyncPushResponse({
    @Default(<SyncPushResultItem>[]) List<SyncPushResultItem> results,
    DateTime? serverTime,
  }) = _SyncPushResponse;

  factory SyncPushResponse.fromJson(Map<String, dynamic> json) => _$SyncPushResponseFromJson(json);
}

/// `GET /sync/pull?since=` response. Changes stay as raw maps keyed by entity
/// collection (`sessions`, `session_sets`, `food_logs`, …) so the engine can
/// route each collection to its cache table.
@freezed
abstract class SyncPullResponse with _$SyncPullResponse {
  const factory SyncPullResponse({
    required DateTime serverTime,
    @Default(<String, List<dynamic>>{}) Map<String, List<dynamic>> changes,
    @Default(<String, List<dynamic>>{}) Map<String, List<dynamic>> deleted,
  }) = _SyncPullResponse;

  factory SyncPullResponse.fromJson(Map<String, dynamic> json) => _$SyncPullResponseFromJson(json);
}
