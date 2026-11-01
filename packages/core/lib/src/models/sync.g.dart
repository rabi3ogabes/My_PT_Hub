// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'sync.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_SyncMutation _$SyncMutationFromJson(Map<String, dynamic> json) =>
    _SyncMutation(
      id: json['id'] as String,
      entity: json['entity'] as String,
      op: json['op'] as String,
      payload: json['payload'] as Map<String, dynamic>,
      clientTime: DateTime.parse(json['client_time'] as String),
    );

Map<String, dynamic> _$SyncMutationToJson(_SyncMutation instance) =>
    <String, dynamic>{
      'id': instance.id,
      'entity': instance.entity,
      'op': instance.op,
      'payload': instance.payload,
      'client_time': instance.clientTime.toIso8601String(),
    };

_SyncPushResultItem _$SyncPushResultItemFromJson(Map<String, dynamic> json) =>
    _SyncPushResultItem(
      id: json['id'] as String,
      status: $enumDecode(
        _$SyncResultStatusEnumMap,
        json['status'],
        unknownValue: SyncResultStatus.rejected,
      ),
      error: json['error'] as String?,
    );

Map<String, dynamic> _$SyncPushResultItemToJson(_SyncPushResultItem instance) =>
    <String, dynamic>{
      'id': instance.id,
      'status': _$SyncResultStatusEnumMap[instance.status]!,
      'error': instance.error,
    };

const _$SyncResultStatusEnumMap = {
  SyncResultStatus.applied: 'applied',
  SyncResultStatus.duplicate: 'duplicate',
  SyncResultStatus.rejected: 'rejected',
};

_SyncPushResponse _$SyncPushResponseFromJson(Map<String, dynamic> json) =>
    _SyncPushResponse(
      results:
          (json['results'] as List<dynamic>?)
              ?.map(
                (e) => SyncPushResultItem.fromJson(e as Map<String, dynamic>),
              )
              .toList() ??
          const <SyncPushResultItem>[],
      serverTime: json['server_time'] == null
          ? null
          : DateTime.parse(json['server_time'] as String),
    );

Map<String, dynamic> _$SyncPushResponseToJson(_SyncPushResponse instance) =>
    <String, dynamic>{
      'results': instance.results.map((e) => e.toJson()).toList(),
      'server_time': instance.serverTime?.toIso8601String(),
    };

_SyncPullResponse _$SyncPullResponseFromJson(Map<String, dynamic> json) =>
    _SyncPullResponse(
      serverTime: DateTime.parse(json['server_time'] as String),
      changes:
          (json['changes'] as Map<String, dynamic>?)?.map(
            (k, e) => MapEntry(k, e as List<dynamic>),
          ) ??
          const <String, List<dynamic>>{},
      deleted:
          (json['deleted'] as Map<String, dynamic>?)?.map(
            (k, e) => MapEntry(k, e as List<dynamic>),
          ) ??
          const <String, List<dynamic>>{},
    );

Map<String, dynamic> _$SyncPullResponseToJson(_SyncPullResponse instance) =>
    <String, dynamic>{
      'server_time': instance.serverTime.toIso8601String(),
      'changes': instance.changes,
      'deleted': instance.deleted,
    };
