// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'calendar.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$CalendarEvent {
  String get id;
  String get type;
  String get title;
  String? get description;
  DateTime get startsAt;
  DateTime get endsAt;
  String? get timezone;
  int? get capacity;
  int get bookedCount;
  int get waitlistCount;
  int get creditsCost;
  IdName? get coach;
  IdName? get location;
  String? get meetingUrl;
  String? get rrule;
  String? get status;

  /// Create a copy of CalendarEvent
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $CalendarEventCopyWith<CalendarEvent> get copyWith =>
      _$CalendarEventCopyWithImpl<CalendarEvent>(
        this as CalendarEvent,
        _$identity,
      );

  /// Serializes this CalendarEvent to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as CalendarEvent;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is CalendarEvent &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            (identical(other.title, _this.title) ||
                other.title == _this.title) &&
            (identical(other.description, _this.description) ||
                other.description == _this.description) &&
            (identical(other.startsAt, _this.startsAt) ||
                other.startsAt == _this.startsAt) &&
            (identical(other.endsAt, _this.endsAt) ||
                other.endsAt == _this.endsAt) &&
            (identical(other.timezone, _this.timezone) ||
                other.timezone == _this.timezone) &&
            (identical(other.capacity, _this.capacity) ||
                other.capacity == _this.capacity) &&
            (identical(other.bookedCount, _this.bookedCount) ||
                other.bookedCount == _this.bookedCount) &&
            (identical(other.waitlistCount, _this.waitlistCount) ||
                other.waitlistCount == _this.waitlistCount) &&
            (identical(other.creditsCost, _this.creditsCost) ||
                other.creditsCost == _this.creditsCost) &&
            (identical(other.coach, _this.coach) ||
                other.coach == _this.coach) &&
            (identical(other.location, _this.location) ||
                other.location == _this.location) &&
            (identical(other.meetingUrl, _this.meetingUrl) ||
                other.meetingUrl == _this.meetingUrl) &&
            (identical(other.rrule, _this.rrule) ||
                other.rrule == _this.rrule) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as CalendarEvent;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.type,
      _this.title,
      _this.description,
      _this.startsAt,
      _this.endsAt,
      _this.timezone,
      _this.capacity,
      _this.bookedCount,
      _this.waitlistCount,
      _this.creditsCost,
      _this.coach,
      _this.location,
      _this.meetingUrl,
      _this.rrule,
      _this.status,
    );
  }

  @override
  String toString() {
    final _this = this as CalendarEvent;
    return 'CalendarEvent(id: ${_this.id}, type: ${_this.type}, title: ${_this.title}, description: ${_this.description}, startsAt: ${_this.startsAt}, endsAt: ${_this.endsAt}, timezone: ${_this.timezone}, capacity: ${_this.capacity}, bookedCount: ${_this.bookedCount}, waitlistCount: ${_this.waitlistCount}, creditsCost: ${_this.creditsCost}, coach: ${_this.coach}, location: ${_this.location}, meetingUrl: ${_this.meetingUrl}, rrule: ${_this.rrule}, status: ${_this.status})';
  }
}

/// @nodoc
abstract mixin class $CalendarEventCopyWith<$Res> {
  factory $CalendarEventCopyWith(
    CalendarEvent value,
    $Res Function(CalendarEvent) _then,
  ) = _$CalendarEventCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String type,
    String title,
    String? description,
    DateTime startsAt,
    DateTime endsAt,
    String? timezone,
    int? capacity,
    int bookedCount,
    int waitlistCount,
    int creditsCost,
    IdName? coach,
    IdName? location,
    String? meetingUrl,
    String? rrule,
    String? status,
  });

  $IdNameCopyWith<$Res>? get coach;
  $IdNameCopyWith<$Res>? get location;
}

/// @nodoc
class _$CalendarEventCopyWithImpl<$Res>
    implements $CalendarEventCopyWith<$Res> {
  _$CalendarEventCopyWithImpl(this._self, this._then);

  final CalendarEvent _self;
  final $Res Function(CalendarEvent) _then;

  /// Create a copy of CalendarEvent
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? title = null,
    Object? description = freezed,
    Object? startsAt = null,
    Object? endsAt = null,
    Object? timezone = freezed,
    Object? capacity = freezed,
    Object? bookedCount = null,
    Object? waitlistCount = null,
    Object? creditsCost = null,
    Object? coach = freezed,
    Object? location = freezed,
    Object? meetingUrl = freezed,
    Object? rrule = freezed,
    Object? status = freezed,
  }) {
    return _then(
      CalendarEvent(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        title: null == title
            ? _self.title
            : title // ignore: cast_nullable_to_non_nullable
                  as String,
        description: freezed == description
            ? _self.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String?,
        startsAt: null == startsAt
            ? _self.startsAt
            : startsAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        endsAt: null == endsAt
            ? _self.endsAt
            : endsAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        timezone: freezed == timezone
            ? _self.timezone
            : timezone // ignore: cast_nullable_to_non_nullable
                  as String?,
        capacity: freezed == capacity
            ? _self.capacity
            : capacity // ignore: cast_nullable_to_non_nullable
                  as int?,
        bookedCount: null == bookedCount
            ? _self.bookedCount
            : bookedCount // ignore: cast_nullable_to_non_nullable
                  as int,
        waitlistCount: null == waitlistCount
            ? _self.waitlistCount
            : waitlistCount // ignore: cast_nullable_to_non_nullable
                  as int,
        creditsCost: null == creditsCost
            ? _self.creditsCost
            : creditsCost // ignore: cast_nullable_to_non_nullable
                  as int,
        coach: freezed == coach
            ? _self.coach
            : coach // ignore: cast_nullable_to_non_nullable
                  as IdName?,
        location: freezed == location
            ? _self.location
            : location // ignore: cast_nullable_to_non_nullable
                  as IdName?,
        meetingUrl: freezed == meetingUrl
            ? _self.meetingUrl
            : meetingUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        rrule: freezed == rrule
            ? _self.rrule
            : rrule // ignore: cast_nullable_to_non_nullable
                  as String?,
        status: freezed == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }

  /// Create a copy of CalendarEvent
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $IdNameCopyWith<$Res>? get coach {
    if (_self.coach == null) {
      return null;
    }

    return $IdNameCopyWith<$Res>(_self.coach!, (value) {
      return _then(_self.copyWith(coach: value));
    });
  }

  /// Create a copy of CalendarEvent
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $IdNameCopyWith<$Res>? get location {
    if (_self.location == null) {
      return null;
    }

    return $IdNameCopyWith<$Res>(_self.location!, (value) {
      return _then(_self.copyWith(location: value));
    });
  }
}

/// Adds pattern-matching-related methods to [CalendarEvent].
extension CalendarEventPatterns on CalendarEvent {
  /// A variant of `map` that fallback to returning `orElse`.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case _:
  ///     return orElse();
  /// }
  /// ```

  @optionalTypeArgs
  TResult maybeMap<TResult extends Object?>(
    TResult Function(_CalendarEvent value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CalendarEvent() when $default != null:
        return $default(_that);
      case _:
        return orElse();
    }
  }

  /// A `switch`-like method, using callbacks.
  ///
  /// Callbacks receives the raw object, upcasted.
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case final Subclass2 value:
  ///     return ...;
  /// }
  /// ```

  @optionalTypeArgs
  TResult map<TResult extends Object?>(
    TResult Function(_CalendarEvent value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CalendarEvent():
        return $default(_that);
      case _:
        throw StateError('Unexpected subclass');
    }
  }

  /// A variant of `map` that fallback to returning `null`.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case _:
  ///     return null;
  /// }
  /// ```

  @optionalTypeArgs
  TResult? mapOrNull<TResult extends Object?>(
    TResult? Function(_CalendarEvent value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CalendarEvent() when $default != null:
        return $default(_that);
      case _:
        return null;
    }
  }

  /// A variant of `when` that fallback to an `orElse` callback.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case _:
  ///     return orElse();
  /// }
  /// ```

  @optionalTypeArgs
  TResult maybeWhen<TResult extends Object?>(
    TResult Function(
      String id,
      String type,
      String title,
      String? description,
      DateTime startsAt,
      DateTime endsAt,
      String? timezone,
      int? capacity,
      int bookedCount,
      int waitlistCount,
      int creditsCost,
      IdName? coach,
      IdName? location,
      String? meetingUrl,
      String? rrule,
      String? status,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CalendarEvent() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.title,
          _that.description,
          _that.startsAt,
          _that.endsAt,
          _that.timezone,
          _that.capacity,
          _that.bookedCount,
          _that.waitlistCount,
          _that.creditsCost,
          _that.coach,
          _that.location,
          _that.meetingUrl,
          _that.rrule,
          _that.status,
        );
      case _:
        return orElse();
    }
  }

  /// A `switch`-like method, using callbacks.
  ///
  /// As opposed to `map`, this offers destructuring.
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case Subclass2(:final field2):
  ///     return ...;
  /// }
  /// ```

  @optionalTypeArgs
  TResult when<TResult extends Object?>(
    TResult Function(
      String id,
      String type,
      String title,
      String? description,
      DateTime startsAt,
      DateTime endsAt,
      String? timezone,
      int? capacity,
      int bookedCount,
      int waitlistCount,
      int creditsCost,
      IdName? coach,
      IdName? location,
      String? meetingUrl,
      String? rrule,
      String? status,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CalendarEvent():
        return $default(
          _that.id,
          _that.type,
          _that.title,
          _that.description,
          _that.startsAt,
          _that.endsAt,
          _that.timezone,
          _that.capacity,
          _that.bookedCount,
          _that.waitlistCount,
          _that.creditsCost,
          _that.coach,
          _that.location,
          _that.meetingUrl,
          _that.rrule,
          _that.status,
        );
      case _:
        throw StateError('Unexpected subclass');
    }
  }

  /// A variant of `when` that fallback to returning `null`
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case _:
  ///     return null;
  /// }
  /// ```

  @optionalTypeArgs
  TResult? whenOrNull<TResult extends Object?>(
    TResult? Function(
      String id,
      String type,
      String title,
      String? description,
      DateTime startsAt,
      DateTime endsAt,
      String? timezone,
      int? capacity,
      int bookedCount,
      int waitlistCount,
      int creditsCost,
      IdName? coach,
      IdName? location,
      String? meetingUrl,
      String? rrule,
      String? status,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CalendarEvent() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.title,
          _that.description,
          _that.startsAt,
          _that.endsAt,
          _that.timezone,
          _that.capacity,
          _that.bookedCount,
          _that.waitlistCount,
          _that.creditsCost,
          _that.coach,
          _that.location,
          _that.meetingUrl,
          _that.rrule,
          _that.status,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _CalendarEvent extends CalendarEvent {
  const _CalendarEvent({
    required this.id,
    this.type = 'session',
    required this.title,
    this.description,
    required this.startsAt,
    required this.endsAt,
    this.timezone,
    this.capacity,
    this.bookedCount = 0,
    this.waitlistCount = 0,
    this.creditsCost = 0,
    this.coach,
    this.location,
    this.meetingUrl,
    this.rrule,
    this.status,
  }) : super._();
  factory _CalendarEvent.fromJson(Map<String, dynamic> json) =>
      _$CalendarEventFromJson(json);

  @override
  final String id;
  @override
  @JsonKey()
  final String type;
  @override
  final String title;
  @override
  final String? description;
  @override
  final DateTime startsAt;
  @override
  final DateTime endsAt;
  @override
  final String? timezone;
  @override
  final int? capacity;
  @override
  @JsonKey()
  final int bookedCount;
  @override
  @JsonKey()
  final int waitlistCount;
  @override
  @JsonKey()
  final int creditsCost;
  @override
  final IdName? coach;
  @override
  final IdName? location;
  @override
  final String? meetingUrl;
  @override
  final String? rrule;
  @override
  final String? status;

  /// Create a copy of CalendarEvent
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$CalendarEventCopyWith<_CalendarEvent> get copyWith =>
      __$CalendarEventCopyWithImpl<_CalendarEvent>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$CalendarEventToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _CalendarEvent &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.title, title) || other.title == title) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.startsAt, startsAt) ||
                other.startsAt == startsAt) &&
            (identical(other.endsAt, endsAt) || other.endsAt == endsAt) &&
            (identical(other.timezone, timezone) ||
                other.timezone == timezone) &&
            (identical(other.capacity, capacity) ||
                other.capacity == capacity) &&
            (identical(other.bookedCount, bookedCount) ||
                other.bookedCount == bookedCount) &&
            (identical(other.waitlistCount, waitlistCount) ||
                other.waitlistCount == waitlistCount) &&
            (identical(other.creditsCost, creditsCost) ||
                other.creditsCost == creditsCost) &&
            (identical(other.coach, coach) || other.coach == coach) &&
            (identical(other.location, location) ||
                other.location == location) &&
            (identical(other.meetingUrl, meetingUrl) ||
                other.meetingUrl == meetingUrl) &&
            (identical(other.rrule, rrule) || other.rrule == rrule) &&
            (identical(other.status, status) || other.status == status));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      type,
      title,
      description,
      startsAt,
      endsAt,
      timezone,
      capacity,
      bookedCount,
      waitlistCount,
      creditsCost,
      coach,
      location,
      meetingUrl,
      rrule,
      status,
    );
  }

  @override
  String toString() {
    return 'CalendarEvent(id: $id, type: $type, title: $title, description: $description, startsAt: $startsAt, endsAt: $endsAt, timezone: $timezone, capacity: $capacity, bookedCount: $bookedCount, waitlistCount: $waitlistCount, creditsCost: $creditsCost, coach: $coach, location: $location, meetingUrl: $meetingUrl, rrule: $rrule, status: $status)';
  }
}

/// @nodoc
abstract mixin class _$CalendarEventCopyWith<$Res>
    implements $CalendarEventCopyWith<$Res> {
  factory _$CalendarEventCopyWith(
    _CalendarEvent value,
    $Res Function(_CalendarEvent) _then,
  ) = __$CalendarEventCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String type,
    String title,
    String? description,
    DateTime startsAt,
    DateTime endsAt,
    String? timezone,
    int? capacity,
    int bookedCount,
    int waitlistCount,
    int creditsCost,
    IdName? coach,
    IdName? location,
    String? meetingUrl,
    String? rrule,
    String? status,
  });

  @override
  $IdNameCopyWith<$Res>? get coach;
  @override
  $IdNameCopyWith<$Res>? get location;
}

/// @nodoc
class __$CalendarEventCopyWithImpl<$Res>
    implements _$CalendarEventCopyWith<$Res> {
  __$CalendarEventCopyWithImpl(this._self, this._then);

  final _CalendarEvent _self;
  final $Res Function(_CalendarEvent) _then;

  /// Create a copy of CalendarEvent
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? title = null,
    Object? description = freezed,
    Object? startsAt = null,
    Object? endsAt = null,
    Object? timezone = freezed,
    Object? capacity = freezed,
    Object? bookedCount = null,
    Object? waitlistCount = null,
    Object? creditsCost = null,
    Object? coach = freezed,
    Object? location = freezed,
    Object? meetingUrl = freezed,
    Object? rrule = freezed,
    Object? status = freezed,
  }) {
    return _then(
      _CalendarEvent(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        title: null == title
            ? _self.title
            : title // ignore: cast_nullable_to_non_nullable
                  as String,
        description: freezed == description
            ? _self.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String?,
        startsAt: null == startsAt
            ? _self.startsAt
            : startsAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        endsAt: null == endsAt
            ? _self.endsAt
            : endsAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        timezone: freezed == timezone
            ? _self.timezone
            : timezone // ignore: cast_nullable_to_non_nullable
                  as String?,
        capacity: freezed == capacity
            ? _self.capacity
            : capacity // ignore: cast_nullable_to_non_nullable
                  as int?,
        bookedCount: null == bookedCount
            ? _self.bookedCount
            : bookedCount // ignore: cast_nullable_to_non_nullable
                  as int,
        waitlistCount: null == waitlistCount
            ? _self.waitlistCount
            : waitlistCount // ignore: cast_nullable_to_non_nullable
                  as int,
        creditsCost: null == creditsCost
            ? _self.creditsCost
            : creditsCost // ignore: cast_nullable_to_non_nullable
                  as int,
        coach: freezed == coach
            ? _self.coach
            : coach // ignore: cast_nullable_to_non_nullable
                  as IdName?,
        location: freezed == location
            ? _self.location
            : location // ignore: cast_nullable_to_non_nullable
                  as IdName?,
        meetingUrl: freezed == meetingUrl
            ? _self.meetingUrl
            : meetingUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        rrule: freezed == rrule
            ? _self.rrule
            : rrule // ignore: cast_nullable_to_non_nullable
                  as String?,
        status: freezed == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }

  /// Create a copy of CalendarEvent
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $IdNameCopyWith<$Res>? get coach {
    if (_self.coach == null) {
      return null;
    }

    return $IdNameCopyWith<$Res>(_self.coach!, (value) {
      return _then(_self.copyWith(coach: value));
    });
  }

  /// Create a copy of CalendarEvent
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $IdNameCopyWith<$Res>? get location {
    if (_self.location == null) {
      return null;
    }

    return $IdNameCopyWith<$Res>(_self.location!, (value) {
      return _then(_self.copyWith(location: value));
    });
  }
}

/// @nodoc
mixin _$Booking {
  String get id;
  CalendarEvent get event;
  String get status;
  int? get waitlistPosition;
  String? get checkinCode;
  DateTime? get canCancelFreeUntil;
  DateTime? get createdAt;

  /// Create a copy of Booking
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $BookingCopyWith<Booking> get copyWith =>
      _$BookingCopyWithImpl<Booking>(this as Booking, _$identity);

  /// Serializes this Booking to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Booking;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Booking &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.event, _this.event) ||
                other.event == _this.event) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status) &&
            (identical(other.waitlistPosition, _this.waitlistPosition) ||
                other.waitlistPosition == _this.waitlistPosition) &&
            (identical(other.checkinCode, _this.checkinCode) ||
                other.checkinCode == _this.checkinCode) &&
            (identical(other.canCancelFreeUntil, _this.canCancelFreeUntil) ||
                other.canCancelFreeUntil == _this.canCancelFreeUntil) &&
            (identical(other.createdAt, _this.createdAt) ||
                other.createdAt == _this.createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Booking;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.event,
      _this.status,
      _this.waitlistPosition,
      _this.checkinCode,
      _this.canCancelFreeUntil,
      _this.createdAt,
    );
  }

  @override
  String toString() {
    final _this = this as Booking;
    return 'Booking(id: ${_this.id}, event: ${_this.event}, status: ${_this.status}, waitlistPosition: ${_this.waitlistPosition}, checkinCode: ${_this.checkinCode}, canCancelFreeUntil: ${_this.canCancelFreeUntil}, createdAt: ${_this.createdAt})';
  }
}

/// @nodoc
abstract mixin class $BookingCopyWith<$Res> {
  factory $BookingCopyWith(Booking value, $Res Function(Booking) _then) =
      _$BookingCopyWithImpl;
  @useResult
  $Res call({
    String id,
    CalendarEvent event,
    String status,
    int? waitlistPosition,
    String? checkinCode,
    DateTime? canCancelFreeUntil,
    DateTime? createdAt,
  });

  $CalendarEventCopyWith<$Res> get event;
}

/// @nodoc
class _$BookingCopyWithImpl<$Res> implements $BookingCopyWith<$Res> {
  _$BookingCopyWithImpl(this._self, this._then);

  final Booking _self;
  final $Res Function(Booking) _then;

  /// Create a copy of Booking
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? event = null,
    Object? status = null,
    Object? waitlistPosition = freezed,
    Object? checkinCode = freezed,
    Object? canCancelFreeUntil = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      Booking(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        event: null == event
            ? _self.event
            : event // ignore: cast_nullable_to_non_nullable
                  as CalendarEvent,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        waitlistPosition: freezed == waitlistPosition
            ? _self.waitlistPosition
            : waitlistPosition // ignore: cast_nullable_to_non_nullable
                  as int?,
        checkinCode: freezed == checkinCode
            ? _self.checkinCode
            : checkinCode // ignore: cast_nullable_to_non_nullable
                  as String?,
        canCancelFreeUntil: freezed == canCancelFreeUntil
            ? _self.canCancelFreeUntil
            : canCancelFreeUntil // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of Booking
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $CalendarEventCopyWith<$Res> get event {
    return $CalendarEventCopyWith<$Res>(_self.event, (value) {
      return _then(_self.copyWith(event: value));
    });
  }
}

/// Adds pattern-matching-related methods to [Booking].
extension BookingPatterns on Booking {
  /// A variant of `map` that fallback to returning `orElse`.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case _:
  ///     return orElse();
  /// }
  /// ```

  @optionalTypeArgs
  TResult maybeMap<TResult extends Object?>(
    TResult Function(_Booking value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Booking() when $default != null:
        return $default(_that);
      case _:
        return orElse();
    }
  }

  /// A `switch`-like method, using callbacks.
  ///
  /// Callbacks receives the raw object, upcasted.
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case final Subclass2 value:
  ///     return ...;
  /// }
  /// ```

  @optionalTypeArgs
  TResult map<TResult extends Object?>(
    TResult Function(_Booking value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Booking():
        return $default(_that);
      case _:
        throw StateError('Unexpected subclass');
    }
  }

  /// A variant of `map` that fallback to returning `null`.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case _:
  ///     return null;
  /// }
  /// ```

  @optionalTypeArgs
  TResult? mapOrNull<TResult extends Object?>(
    TResult? Function(_Booking value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Booking() when $default != null:
        return $default(_that);
      case _:
        return null;
    }
  }

  /// A variant of `when` that fallback to an `orElse` callback.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case _:
  ///     return orElse();
  /// }
  /// ```

  @optionalTypeArgs
  TResult maybeWhen<TResult extends Object?>(
    TResult Function(
      String id,
      CalendarEvent event,
      String status,
      int? waitlistPosition,
      String? checkinCode,
      DateTime? canCancelFreeUntil,
      DateTime? createdAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Booking() when $default != null:
        return $default(
          _that.id,
          _that.event,
          _that.status,
          _that.waitlistPosition,
          _that.checkinCode,
          _that.canCancelFreeUntil,
          _that.createdAt,
        );
      case _:
        return orElse();
    }
  }

  /// A `switch`-like method, using callbacks.
  ///
  /// As opposed to `map`, this offers destructuring.
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case Subclass2(:final field2):
  ///     return ...;
  /// }
  /// ```

  @optionalTypeArgs
  TResult when<TResult extends Object?>(
    TResult Function(
      String id,
      CalendarEvent event,
      String status,
      int? waitlistPosition,
      String? checkinCode,
      DateTime? canCancelFreeUntil,
      DateTime? createdAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Booking():
        return $default(
          _that.id,
          _that.event,
          _that.status,
          _that.waitlistPosition,
          _that.checkinCode,
          _that.canCancelFreeUntil,
          _that.createdAt,
        );
      case _:
        throw StateError('Unexpected subclass');
    }
  }

  /// A variant of `when` that fallback to returning `null`
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case _:
  ///     return null;
  /// }
  /// ```

  @optionalTypeArgs
  TResult? whenOrNull<TResult extends Object?>(
    TResult? Function(
      String id,
      CalendarEvent event,
      String status,
      int? waitlistPosition,
      String? checkinCode,
      DateTime? canCancelFreeUntil,
      DateTime? createdAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Booking() when $default != null:
        return $default(
          _that.id,
          _that.event,
          _that.status,
          _that.waitlistPosition,
          _that.checkinCode,
          _that.canCancelFreeUntil,
          _that.createdAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Booking extends Booking {
  const _Booking({
    required this.id,
    required this.event,
    this.status = 'booked',
    this.waitlistPosition,
    this.checkinCode,
    this.canCancelFreeUntil,
    this.createdAt,
  }) : super._();
  factory _Booking.fromJson(Map<String, dynamic> json) =>
      _$BookingFromJson(json);

  @override
  final String id;
  @override
  final CalendarEvent event;
  @override
  @JsonKey()
  final String status;
  @override
  final int? waitlistPosition;
  @override
  final String? checkinCode;
  @override
  final DateTime? canCancelFreeUntil;
  @override
  final DateTime? createdAt;

  /// Create a copy of Booking
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$BookingCopyWith<_Booking> get copyWith =>
      __$BookingCopyWithImpl<_Booking>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$BookingToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Booking &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.event, event) || other.event == event) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.waitlistPosition, waitlistPosition) ||
                other.waitlistPosition == waitlistPosition) &&
            (identical(other.checkinCode, checkinCode) ||
                other.checkinCode == checkinCode) &&
            (identical(other.canCancelFreeUntil, canCancelFreeUntil) ||
                other.canCancelFreeUntil == canCancelFreeUntil) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      event,
      status,
      waitlistPosition,
      checkinCode,
      canCancelFreeUntil,
      createdAt,
    );
  }

  @override
  String toString() {
    return 'Booking(id: $id, event: $event, status: $status, waitlistPosition: $waitlistPosition, checkinCode: $checkinCode, canCancelFreeUntil: $canCancelFreeUntil, createdAt: $createdAt)';
  }
}

/// @nodoc
abstract mixin class _$BookingCopyWith<$Res> implements $BookingCopyWith<$Res> {
  factory _$BookingCopyWith(_Booking value, $Res Function(_Booking) _then) =
      __$BookingCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    CalendarEvent event,
    String status,
    int? waitlistPosition,
    String? checkinCode,
    DateTime? canCancelFreeUntil,
    DateTime? createdAt,
  });

  @override
  $CalendarEventCopyWith<$Res> get event;
}

/// @nodoc
class __$BookingCopyWithImpl<$Res> implements _$BookingCopyWith<$Res> {
  __$BookingCopyWithImpl(this._self, this._then);

  final _Booking _self;
  final $Res Function(_Booking) _then;

  /// Create a copy of Booking
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? event = null,
    Object? status = null,
    Object? waitlistPosition = freezed,
    Object? checkinCode = freezed,
    Object? canCancelFreeUntil = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _Booking(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        event: null == event
            ? _self.event
            : event // ignore: cast_nullable_to_non_nullable
                  as CalendarEvent,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        waitlistPosition: freezed == waitlistPosition
            ? _self.waitlistPosition
            : waitlistPosition // ignore: cast_nullable_to_non_nullable
                  as int?,
        checkinCode: freezed == checkinCode
            ? _self.checkinCode
            : checkinCode // ignore: cast_nullable_to_non_nullable
                  as String?,
        canCancelFreeUntil: freezed == canCancelFreeUntil
            ? _self.canCancelFreeUntil
            : canCancelFreeUntil // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of Booking
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $CalendarEventCopyWith<$Res> get event {
    return $CalendarEventCopyWith<$Res>(_self.event, (value) {
      return _then(_self.copyWith(event: value));
    });
  }
}

/// @nodoc
mixin _$Slot {
  DateTime get startsAt;
  DateTime get endsAt;

  /// Create a copy of Slot
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $SlotCopyWith<Slot> get copyWith =>
      _$SlotCopyWithImpl<Slot>(this as Slot, _$identity);

  /// Serializes this Slot to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Slot;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Slot &&
            (identical(other.startsAt, _this.startsAt) ||
                other.startsAt == _this.startsAt) &&
            (identical(other.endsAt, _this.endsAt) ||
                other.endsAt == _this.endsAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Slot;
    return Object.hash(runtimeType, _this.startsAt, _this.endsAt);
  }

  @override
  String toString() {
    final _this = this as Slot;
    return 'Slot(startsAt: ${_this.startsAt}, endsAt: ${_this.endsAt})';
  }
}

/// @nodoc
abstract mixin class $SlotCopyWith<$Res> {
  factory $SlotCopyWith(Slot value, $Res Function(Slot) _then) =
      _$SlotCopyWithImpl;
  @useResult
  $Res call({DateTime startsAt, DateTime endsAt});
}

/// @nodoc
class _$SlotCopyWithImpl<$Res> implements $SlotCopyWith<$Res> {
  _$SlotCopyWithImpl(this._self, this._then);

  final Slot _self;
  final $Res Function(Slot) _then;

  /// Create a copy of Slot
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? startsAt = null, Object? endsAt = null}) {
    return _then(
      Slot(
        startsAt: null == startsAt
            ? _self.startsAt
            : startsAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        endsAt: null == endsAt
            ? _self.endsAt
            : endsAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Slot].
extension SlotPatterns on Slot {
  /// A variant of `map` that fallback to returning `orElse`.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case _:
  ///     return orElse();
  /// }
  /// ```

  @optionalTypeArgs
  TResult maybeMap<TResult extends Object?>(
    TResult Function(_Slot value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Slot() when $default != null:
        return $default(_that);
      case _:
        return orElse();
    }
  }

  /// A `switch`-like method, using callbacks.
  ///
  /// Callbacks receives the raw object, upcasted.
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case final Subclass2 value:
  ///     return ...;
  /// }
  /// ```

  @optionalTypeArgs
  TResult map<TResult extends Object?>(TResult Function(_Slot value) $default) {
    final _that = this;
    switch (_that) {
      case _Slot():
        return $default(_that);
      case _:
        throw StateError('Unexpected subclass');
    }
  }

  /// A variant of `map` that fallback to returning `null`.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case _:
  ///     return null;
  /// }
  /// ```

  @optionalTypeArgs
  TResult? mapOrNull<TResult extends Object?>(
    TResult? Function(_Slot value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Slot() when $default != null:
        return $default(_that);
      case _:
        return null;
    }
  }

  /// A variant of `when` that fallback to an `orElse` callback.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case _:
  ///     return orElse();
  /// }
  /// ```

  @optionalTypeArgs
  TResult maybeWhen<TResult extends Object?>(
    TResult Function(DateTime startsAt, DateTime endsAt)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Slot() when $default != null:
        return $default(_that.startsAt, _that.endsAt);
      case _:
        return orElse();
    }
  }

  /// A `switch`-like method, using callbacks.
  ///
  /// As opposed to `map`, this offers destructuring.
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case Subclass2(:final field2):
  ///     return ...;
  /// }
  /// ```

  @optionalTypeArgs
  TResult when<TResult extends Object?>(
    TResult Function(DateTime startsAt, DateTime endsAt) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Slot():
        return $default(_that.startsAt, _that.endsAt);
      case _:
        throw StateError('Unexpected subclass');
    }
  }

  /// A variant of `when` that fallback to returning `null`
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case _:
  ///     return null;
  /// }
  /// ```

  @optionalTypeArgs
  TResult? whenOrNull<TResult extends Object?>(
    TResult? Function(DateTime startsAt, DateTime endsAt)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Slot() when $default != null:
        return $default(_that.startsAt, _that.endsAt);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Slot implements Slot {
  const _Slot({required this.startsAt, required this.endsAt});
  factory _Slot.fromJson(Map<String, dynamic> json) => _$SlotFromJson(json);

  @override
  final DateTime startsAt;
  @override
  final DateTime endsAt;

  /// Create a copy of Slot
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$SlotCopyWith<_Slot> get copyWith =>
      __$SlotCopyWithImpl<_Slot>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$SlotToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Slot &&
            (identical(other.startsAt, startsAt) ||
                other.startsAt == startsAt) &&
            (identical(other.endsAt, endsAt) || other.endsAt == endsAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, startsAt, endsAt);
  }

  @override
  String toString() {
    return 'Slot(startsAt: $startsAt, endsAt: $endsAt)';
  }
}

/// @nodoc
abstract mixin class _$SlotCopyWith<$Res> implements $SlotCopyWith<$Res> {
  factory _$SlotCopyWith(_Slot value, $Res Function(_Slot) _then) =
      __$SlotCopyWithImpl;
  @override
  @useResult
  $Res call({DateTime startsAt, DateTime endsAt});
}

/// @nodoc
class __$SlotCopyWithImpl<$Res> implements _$SlotCopyWith<$Res> {
  __$SlotCopyWithImpl(this._self, this._then);

  final _Slot _self;
  final $Res Function(_Slot) _then;

  /// Create a copy of Slot
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? startsAt = null, Object? endsAt = null}) {
    return _then(
      _Slot(
        startsAt: null == startsAt
            ? _self.startsAt
            : startsAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        endsAt: null == endsAt
            ? _self.endsAt
            : endsAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// @nodoc
mixin _$CreditEntry {
  String get id;
  int get amount;
  String? get reason;
  String? get note;
  DateTime? get createdAt;

  /// Create a copy of CreditEntry
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $CreditEntryCopyWith<CreditEntry> get copyWith =>
      _$CreditEntryCopyWithImpl<CreditEntry>(this as CreditEntry, _$identity);

  /// Serializes this CreditEntry to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as CreditEntry;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is CreditEntry &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.amount, _this.amount) ||
                other.amount == _this.amount) &&
            (identical(other.reason, _this.reason) ||
                other.reason == _this.reason) &&
            (identical(other.note, _this.note) || other.note == _this.note) &&
            (identical(other.createdAt, _this.createdAt) ||
                other.createdAt == _this.createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as CreditEntry;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.amount,
      _this.reason,
      _this.note,
      _this.createdAt,
    );
  }

  @override
  String toString() {
    final _this = this as CreditEntry;
    return 'CreditEntry(id: ${_this.id}, amount: ${_this.amount}, reason: ${_this.reason}, note: ${_this.note}, createdAt: ${_this.createdAt})';
  }
}

/// @nodoc
abstract mixin class $CreditEntryCopyWith<$Res> {
  factory $CreditEntryCopyWith(
    CreditEntry value,
    $Res Function(CreditEntry) _then,
  ) = _$CreditEntryCopyWithImpl;
  @useResult
  $Res call({
    String id,
    int amount,
    String? reason,
    String? note,
    DateTime? createdAt,
  });
}

/// @nodoc
class _$CreditEntryCopyWithImpl<$Res> implements $CreditEntryCopyWith<$Res> {
  _$CreditEntryCopyWithImpl(this._self, this._then);

  final CreditEntry _self;
  final $Res Function(CreditEntry) _then;

  /// Create a copy of CreditEntry
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? amount = null,
    Object? reason = freezed,
    Object? note = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      CreditEntry(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        amount: null == amount
            ? _self.amount
            : amount // ignore: cast_nullable_to_non_nullable
                  as int,
        reason: freezed == reason
            ? _self.reason
            : reason // ignore: cast_nullable_to_non_nullable
                  as String?,
        note: freezed == note
            ? _self.note
            : note // ignore: cast_nullable_to_non_nullable
                  as String?,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [CreditEntry].
extension CreditEntryPatterns on CreditEntry {
  /// A variant of `map` that fallback to returning `orElse`.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case _:
  ///     return orElse();
  /// }
  /// ```

  @optionalTypeArgs
  TResult maybeMap<TResult extends Object?>(
    TResult Function(_CreditEntry value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CreditEntry() when $default != null:
        return $default(_that);
      case _:
        return orElse();
    }
  }

  /// A `switch`-like method, using callbacks.
  ///
  /// Callbacks receives the raw object, upcasted.
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case final Subclass2 value:
  ///     return ...;
  /// }
  /// ```

  @optionalTypeArgs
  TResult map<TResult extends Object?>(
    TResult Function(_CreditEntry value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CreditEntry():
        return $default(_that);
      case _:
        throw StateError('Unexpected subclass');
    }
  }

  /// A variant of `map` that fallback to returning `null`.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case _:
  ///     return null;
  /// }
  /// ```

  @optionalTypeArgs
  TResult? mapOrNull<TResult extends Object?>(
    TResult? Function(_CreditEntry value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CreditEntry() when $default != null:
        return $default(_that);
      case _:
        return null;
    }
  }

  /// A variant of `when` that fallback to an `orElse` callback.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case _:
  ///     return orElse();
  /// }
  /// ```

  @optionalTypeArgs
  TResult maybeWhen<TResult extends Object?>(
    TResult Function(
      String id,
      int amount,
      String? reason,
      String? note,
      DateTime? createdAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CreditEntry() when $default != null:
        return $default(
          _that.id,
          _that.amount,
          _that.reason,
          _that.note,
          _that.createdAt,
        );
      case _:
        return orElse();
    }
  }

  /// A `switch`-like method, using callbacks.
  ///
  /// As opposed to `map`, this offers destructuring.
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case Subclass2(:final field2):
  ///     return ...;
  /// }
  /// ```

  @optionalTypeArgs
  TResult when<TResult extends Object?>(
    TResult Function(
      String id,
      int amount,
      String? reason,
      String? note,
      DateTime? createdAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CreditEntry():
        return $default(
          _that.id,
          _that.amount,
          _that.reason,
          _that.note,
          _that.createdAt,
        );
      case _:
        throw StateError('Unexpected subclass');
    }
  }

  /// A variant of `when` that fallback to returning `null`
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case _:
  ///     return null;
  /// }
  /// ```

  @optionalTypeArgs
  TResult? whenOrNull<TResult extends Object?>(
    TResult? Function(
      String id,
      int amount,
      String? reason,
      String? note,
      DateTime? createdAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CreditEntry() when $default != null:
        return $default(
          _that.id,
          _that.amount,
          _that.reason,
          _that.note,
          _that.createdAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _CreditEntry implements CreditEntry {
  const _CreditEntry({
    required this.id,
    required this.amount,
    this.reason,
    this.note,
    this.createdAt,
  });
  factory _CreditEntry.fromJson(Map<String, dynamic> json) =>
      _$CreditEntryFromJson(json);

  @override
  final String id;
  @override
  final int amount;
  @override
  final String? reason;
  @override
  final String? note;
  @override
  final DateTime? createdAt;

  /// Create a copy of CreditEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$CreditEntryCopyWith<_CreditEntry> get copyWith =>
      __$CreditEntryCopyWithImpl<_CreditEntry>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$CreditEntryToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _CreditEntry &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.amount, amount) || other.amount == amount) &&
            (identical(other.reason, reason) || other.reason == reason) &&
            (identical(other.note, note) || other.note == note) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, amount, reason, note, createdAt);
  }

  @override
  String toString() {
    return 'CreditEntry(id: $id, amount: $amount, reason: $reason, note: $note, createdAt: $createdAt)';
  }
}

/// @nodoc
abstract mixin class _$CreditEntryCopyWith<$Res>
    implements $CreditEntryCopyWith<$Res> {
  factory _$CreditEntryCopyWith(
    _CreditEntry value,
    $Res Function(_CreditEntry) _then,
  ) = __$CreditEntryCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    int amount,
    String? reason,
    String? note,
    DateTime? createdAt,
  });
}

/// @nodoc
class __$CreditEntryCopyWithImpl<$Res> implements _$CreditEntryCopyWith<$Res> {
  __$CreditEntryCopyWithImpl(this._self, this._then);

  final _CreditEntry _self;
  final $Res Function(_CreditEntry) _then;

  /// Create a copy of CreditEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? amount = null,
    Object? reason = freezed,
    Object? note = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _CreditEntry(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        amount: null == amount
            ? _self.amount
            : amount // ignore: cast_nullable_to_non_nullable
                  as int,
        reason: freezed == reason
            ? _self.reason
            : reason // ignore: cast_nullable_to_non_nullable
                  as String?,
        note: freezed == note
            ? _self.note
            : note // ignore: cast_nullable_to_non_nullable
                  as String?,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
mixin _$Credits {
  int get balance;
  List<CreditEntry> get entries;

  /// Create a copy of Credits
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $CreditsCopyWith<Credits> get copyWith =>
      _$CreditsCopyWithImpl<Credits>(this as Credits, _$identity);

  /// Serializes this Credits to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Credits;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Credits &&
            (identical(other.balance, _this.balance) ||
                other.balance == _this.balance) &&
            const DeepCollectionEquality().equals(
              other.entries,
              _this.entries,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Credits;
    return Object.hash(
      runtimeType,
      _this.balance,
      const DeepCollectionEquality().hash(_this.entries),
    );
  }

  @override
  String toString() {
    final _this = this as Credits;
    return 'Credits(balance: ${_this.balance}, entries: ${_this.entries})';
  }
}

/// @nodoc
abstract mixin class $CreditsCopyWith<$Res> {
  factory $CreditsCopyWith(Credits value, $Res Function(Credits) _then) =
      _$CreditsCopyWithImpl;
  @useResult
  $Res call({int balance, List<CreditEntry> entries});
}

/// @nodoc
class _$CreditsCopyWithImpl<$Res> implements $CreditsCopyWith<$Res> {
  _$CreditsCopyWithImpl(this._self, this._then);

  final Credits _self;
  final $Res Function(Credits) _then;

  /// Create a copy of Credits
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? balance = null, Object? entries = null}) {
    return _then(
      Credits(
        balance: null == balance
            ? _self.balance
            : balance // ignore: cast_nullable_to_non_nullable
                  as int,
        entries: null == entries
            ? _self.entries
            : entries // ignore: cast_nullable_to_non_nullable
                  as List<CreditEntry>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Credits].
extension CreditsPatterns on Credits {
  /// A variant of `map` that fallback to returning `orElse`.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case _:
  ///     return orElse();
  /// }
  /// ```

  @optionalTypeArgs
  TResult maybeMap<TResult extends Object?>(
    TResult Function(_Credits value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Credits() when $default != null:
        return $default(_that);
      case _:
        return orElse();
    }
  }

  /// A `switch`-like method, using callbacks.
  ///
  /// Callbacks receives the raw object, upcasted.
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case final Subclass2 value:
  ///     return ...;
  /// }
  /// ```

  @optionalTypeArgs
  TResult map<TResult extends Object?>(
    TResult Function(_Credits value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Credits():
        return $default(_that);
      case _:
        throw StateError('Unexpected subclass');
    }
  }

  /// A variant of `map` that fallback to returning `null`.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case final Subclass value:
  ///     return ...;
  ///   case _:
  ///     return null;
  /// }
  /// ```

  @optionalTypeArgs
  TResult? mapOrNull<TResult extends Object?>(
    TResult? Function(_Credits value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Credits() when $default != null:
        return $default(_that);
      case _:
        return null;
    }
  }

  /// A variant of `when` that fallback to an `orElse` callback.
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case _:
  ///     return orElse();
  /// }
  /// ```

  @optionalTypeArgs
  TResult maybeWhen<TResult extends Object?>(
    TResult Function(int balance, List<CreditEntry> entries)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Credits() when $default != null:
        return $default(_that.balance, _that.entries);
      case _:
        return orElse();
    }
  }

  /// A `switch`-like method, using callbacks.
  ///
  /// As opposed to `map`, this offers destructuring.
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case Subclass2(:final field2):
  ///     return ...;
  /// }
  /// ```

  @optionalTypeArgs
  TResult when<TResult extends Object?>(
    TResult Function(int balance, List<CreditEntry> entries) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Credits():
        return $default(_that.balance, _that.entries);
      case _:
        throw StateError('Unexpected subclass');
    }
  }

  /// A variant of `when` that fallback to returning `null`
  ///
  /// It is equivalent to doing:
  /// ```dart
  /// switch (sealedClass) {
  ///   case Subclass(:final field):
  ///     return ...;
  ///   case _:
  ///     return null;
  /// }
  /// ```

  @optionalTypeArgs
  TResult? whenOrNull<TResult extends Object?>(
    TResult? Function(int balance, List<CreditEntry> entries)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Credits() when $default != null:
        return $default(_that.balance, _that.entries);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Credits implements Credits {
  const _Credits({
    this.balance = 0,
    List<CreditEntry> entries = const <CreditEntry>[],
  }) : _entries = entries;
  factory _Credits.fromJson(Map<String, dynamic> json) =>
      _$CreditsFromJson(json);

  @override
  @JsonKey()
  final int balance;
  final List<CreditEntry> _entries;
  @override
  @JsonKey()
  List<CreditEntry> get entries {
    if (_entries is EqualUnmodifiableListView) return _entries;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_entries);
  }

  /// Create a copy of Credits
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$CreditsCopyWith<_Credits> get copyWith =>
      __$CreditsCopyWithImpl<_Credits>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$CreditsToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Credits &&
            (identical(other.balance, balance) || other.balance == balance) &&
            const DeepCollectionEquality().equals(other.entries, _entries));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      balance,
      const DeepCollectionEquality().hash(_entries),
    );
  }

  @override
  String toString() {
    return 'Credits(balance: $balance, entries: $entries)';
  }
}

/// @nodoc
abstract mixin class _$CreditsCopyWith<$Res> implements $CreditsCopyWith<$Res> {
  factory _$CreditsCopyWith(_Credits value, $Res Function(_Credits) _then) =
      __$CreditsCopyWithImpl;
  @override
  @useResult
  $Res call({int balance, List<CreditEntry> entries});
}

/// @nodoc
class __$CreditsCopyWithImpl<$Res> implements _$CreditsCopyWith<$Res> {
  __$CreditsCopyWithImpl(this._self, this._then);

  final _Credits _self;
  final $Res Function(_Credits) _then;

  /// Create a copy of Credits
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? balance = null, Object? entries = null}) {
    return _then(
      _Credits(
        balance: null == balance
            ? _self.balance
            : balance // ignore: cast_nullable_to_non_nullable
                  as int,
        entries: null == entries
            ? _self._entries
            : entries // ignore: cast_nullable_to_non_nullable
                  as List<CreditEntry>,
      ),
    );
  }
}
