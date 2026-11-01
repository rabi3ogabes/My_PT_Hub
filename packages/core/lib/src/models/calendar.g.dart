// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'calendar.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_CalendarEvent _$CalendarEventFromJson(Map<String, dynamic> json) =>
    _CalendarEvent(
      id: json['id'] as String,
      type: json['type'] as String? ?? 'session',
      title: json['title'] as String,
      description: json['description'] as String?,
      startsAt: DateTime.parse(json['starts_at'] as String),
      endsAt: DateTime.parse(json['ends_at'] as String),
      timezone: json['timezone'] as String?,
      capacity: (json['capacity'] as num?)?.toInt(),
      bookedCount: (json['booked_count'] as num?)?.toInt() ?? 0,
      waitlistCount: (json['waitlist_count'] as num?)?.toInt() ?? 0,
      creditsCost: (json['credits_cost'] as num?)?.toInt() ?? 0,
      coach: json['coach'] == null
          ? null
          : IdName.fromJson(json['coach'] as Map<String, dynamic>),
      location: json['location'] == null
          ? null
          : IdName.fromJson(json['location'] as Map<String, dynamic>),
      meetingUrl: json['meeting_url'] as String?,
      rrule: json['rrule'] as String?,
      status: json['status'] as String?,
    );

Map<String, dynamic> _$CalendarEventToJson(_CalendarEvent instance) =>
    <String, dynamic>{
      'id': instance.id,
      'type': instance.type,
      'title': instance.title,
      'description': instance.description,
      'starts_at': instance.startsAt.toIso8601String(),
      'ends_at': instance.endsAt.toIso8601String(),
      'timezone': instance.timezone,
      'capacity': instance.capacity,
      'booked_count': instance.bookedCount,
      'waitlist_count': instance.waitlistCount,
      'credits_cost': instance.creditsCost,
      'coach': instance.coach?.toJson(),
      'location': instance.location?.toJson(),
      'meeting_url': instance.meetingUrl,
      'rrule': instance.rrule,
      'status': instance.status,
    };

_Booking _$BookingFromJson(Map<String, dynamic> json) => _Booking(
  id: json['id'] as String,
  event: CalendarEvent.fromJson(json['event'] as Map<String, dynamic>),
  status: json['status'] as String? ?? 'booked',
  waitlistPosition: (json['waitlist_position'] as num?)?.toInt(),
  checkinCode: json['checkin_code'] as String?,
  canCancelFreeUntil: json['can_cancel_free_until'] == null
      ? null
      : DateTime.parse(json['can_cancel_free_until'] as String),
  createdAt: json['created_at'] == null
      ? null
      : DateTime.parse(json['created_at'] as String),
);

Map<String, dynamic> _$BookingToJson(_Booking instance) => <String, dynamic>{
  'id': instance.id,
  'event': instance.event.toJson(),
  'status': instance.status,
  'waitlist_position': instance.waitlistPosition,
  'checkin_code': instance.checkinCode,
  'can_cancel_free_until': instance.canCancelFreeUntil?.toIso8601String(),
  'created_at': instance.createdAt?.toIso8601String(),
};

_Slot _$SlotFromJson(Map<String, dynamic> json) => _Slot(
  startsAt: DateTime.parse(json['starts_at'] as String),
  endsAt: DateTime.parse(json['ends_at'] as String),
);

Map<String, dynamic> _$SlotToJson(_Slot instance) => <String, dynamic>{
  'starts_at': instance.startsAt.toIso8601String(),
  'ends_at': instance.endsAt.toIso8601String(),
};

_CreditEntry _$CreditEntryFromJson(Map<String, dynamic> json) => _CreditEntry(
  id: json['id'] as String,
  amount: (json['amount'] as num).toInt(),
  reason: json['reason'] as String?,
  note: json['note'] as String?,
  createdAt: json['created_at'] == null
      ? null
      : DateTime.parse(json['created_at'] as String),
);

Map<String, dynamic> _$CreditEntryToJson(_CreditEntry instance) =>
    <String, dynamic>{
      'id': instance.id,
      'amount': instance.amount,
      'reason': instance.reason,
      'note': instance.note,
      'created_at': instance.createdAt?.toIso8601String(),
    };

_Credits _$CreditsFromJson(Map<String, dynamic> json) => _Credits(
  balance: (json['balance'] as num?)?.toInt() ?? 0,
  entries:
      (json['entries'] as List<dynamic>?)
          ?.map((e) => CreditEntry.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <CreditEntry>[],
);

Map<String, dynamic> _$CreditsToJson(_Credits instance) => <String, dynamic>{
  'balance': instance.balance,
  'entries': instance.entries.map((e) => e.toJson()).toList(),
};
