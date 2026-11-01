import 'package:freezed_annotation/freezed_annotation.dart';

import 'common.dart';

part 'calendar.freezed.dart';
part 'calendar.g.dart';

/// **Event** — session, class, event, live, block.
@freezed
abstract class CalendarEvent with _$CalendarEvent {
  const CalendarEvent._();

  const factory CalendarEvent({
    required String id,
    @Default('session') String type,
    required String title,
    String? description,
    required DateTime startsAt,
    required DateTime endsAt,
    String? timezone,
    int? capacity,
    @Default(0) int bookedCount,
    @Default(0) int waitlistCount,
    @Default(0) int creditsCost,
    IdName? coach,
    IdName? location,
    String? meetingUrl,
    String? rrule,
    String? status,
  }) = _CalendarEvent;

  factory CalendarEvent.fromJson(Map<String, dynamic> json) => _$CalendarEventFromJson(json);

  bool get isFull => capacity != null && bookedCount >= capacity!;
  int? get spotsLeft => capacity == null ? null : (capacity! - bookedCount).clamp(0, capacity!);
}

/// **Booking**.
@freezed
abstract class Booking with _$Booking {
  const Booking._();

  const factory Booking({
    required String id,
    required CalendarEvent event,
    @Default('booked') String status,
    int? waitlistPosition,
    String? checkinCode,
    DateTime? canCancelFreeUntil,
    DateTime? createdAt,
  }) = _Booking;

  factory Booking.fromJson(Map<String, dynamic> json) => _$BookingFromJson(json);

  bool get isActive => status == 'booked' || status == 'waitlisted';

  /// Whether cancelling now is free under the coach's policy.
  bool isFreeCancellation(DateTime now) => canCancelFreeUntil == null || now.isBefore(canCancelFreeUntil!);
}

/// `GET /me/slots` item.
@freezed
abstract class Slot with _$Slot {
  const factory Slot({required DateTime startsAt, required DateTime endsAt}) = _Slot;

  factory Slot.fromJson(Map<String, dynamic> json) => _$SlotFromJson(json);
}

@freezed
abstract class CreditEntry with _$CreditEntry {
  const factory CreditEntry({required String id, required int amount, String? reason, String? note, DateTime? createdAt}) =
      _CreditEntry;

  factory CreditEntry.fromJson(Map<String, dynamic> json) => _$CreditEntryFromJson(json);
}

/// `GET /me/credits`.
@freezed
abstract class Credits with _$Credits {
  const factory Credits({@Default(0) int balance, @Default(<CreditEntry>[]) List<CreditEntry> entries}) = _Credits;

  factory Credits.fromJson(Map<String, dynamic> json) => _$CreditsFromJson(json);
}
