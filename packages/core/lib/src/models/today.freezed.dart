// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'today.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$TodayWorkout {
  String get workoutId;
  String? get programDayId;
  String? get programAssignmentId;
  String get name;
  int? get estimatedMinutes;
  int get exerciseCount;
  int? get weekNumber;
  int? get dayNumber;
  String get status;
  String? get sessionId;

  /// Create a copy of TodayWorkout
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $TodayWorkoutCopyWith<TodayWorkout> get copyWith =>
      _$TodayWorkoutCopyWithImpl<TodayWorkout>(
        this as TodayWorkout,
        _$identity,
      );

  /// Serializes this TodayWorkout to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as TodayWorkout;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is TodayWorkout &&
            (identical(other.workoutId, _this.workoutId) ||
                other.workoutId == _this.workoutId) &&
            (identical(other.programDayId, _this.programDayId) ||
                other.programDayId == _this.programDayId) &&
            (identical(other.programAssignmentId, _this.programAssignmentId) ||
                other.programAssignmentId == _this.programAssignmentId) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.estimatedMinutes, _this.estimatedMinutes) ||
                other.estimatedMinutes == _this.estimatedMinutes) &&
            (identical(other.exerciseCount, _this.exerciseCount) ||
                other.exerciseCount == _this.exerciseCount) &&
            (identical(other.weekNumber, _this.weekNumber) ||
                other.weekNumber == _this.weekNumber) &&
            (identical(other.dayNumber, _this.dayNumber) ||
                other.dayNumber == _this.dayNumber) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status) &&
            (identical(other.sessionId, _this.sessionId) ||
                other.sessionId == _this.sessionId));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as TodayWorkout;
    return Object.hash(
      runtimeType,
      _this.workoutId,
      _this.programDayId,
      _this.programAssignmentId,
      _this.name,
      _this.estimatedMinutes,
      _this.exerciseCount,
      _this.weekNumber,
      _this.dayNumber,
      _this.status,
      _this.sessionId,
    );
  }

  @override
  String toString() {
    final _this = this as TodayWorkout;
    return 'TodayWorkout(workoutId: ${_this.workoutId}, programDayId: ${_this.programDayId}, programAssignmentId: ${_this.programAssignmentId}, name: ${_this.name}, estimatedMinutes: ${_this.estimatedMinutes}, exerciseCount: ${_this.exerciseCount}, weekNumber: ${_this.weekNumber}, dayNumber: ${_this.dayNumber}, status: ${_this.status}, sessionId: ${_this.sessionId})';
  }
}

/// @nodoc
abstract mixin class $TodayWorkoutCopyWith<$Res> {
  factory $TodayWorkoutCopyWith(
    TodayWorkout value,
    $Res Function(TodayWorkout) _then,
  ) = _$TodayWorkoutCopyWithImpl;
  @useResult
  $Res call({
    String workoutId,
    String? programDayId,
    String? programAssignmentId,
    String name,
    int? estimatedMinutes,
    int exerciseCount,
    int? weekNumber,
    int? dayNumber,
    String status,
    String? sessionId,
  });
}

/// @nodoc
class _$TodayWorkoutCopyWithImpl<$Res> implements $TodayWorkoutCopyWith<$Res> {
  _$TodayWorkoutCopyWithImpl(this._self, this._then);

  final TodayWorkout _self;
  final $Res Function(TodayWorkout) _then;

  /// Create a copy of TodayWorkout
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? workoutId = null,
    Object? programDayId = freezed,
    Object? programAssignmentId = freezed,
    Object? name = null,
    Object? estimatedMinutes = freezed,
    Object? exerciseCount = null,
    Object? weekNumber = freezed,
    Object? dayNumber = freezed,
    Object? status = null,
    Object? sessionId = freezed,
  }) {
    return _then(
      TodayWorkout(
        workoutId: null == workoutId
            ? _self.workoutId
            : workoutId // ignore: cast_nullable_to_non_nullable
                  as String,
        programDayId: freezed == programDayId
            ? _self.programDayId
            : programDayId // ignore: cast_nullable_to_non_nullable
                  as String?,
        programAssignmentId: freezed == programAssignmentId
            ? _self.programAssignmentId
            : programAssignmentId // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        estimatedMinutes: freezed == estimatedMinutes
            ? _self.estimatedMinutes
            : estimatedMinutes // ignore: cast_nullable_to_non_nullable
                  as int?,
        exerciseCount: null == exerciseCount
            ? _self.exerciseCount
            : exerciseCount // ignore: cast_nullable_to_non_nullable
                  as int,
        weekNumber: freezed == weekNumber
            ? _self.weekNumber
            : weekNumber // ignore: cast_nullable_to_non_nullable
                  as int?,
        dayNumber: freezed == dayNumber
            ? _self.dayNumber
            : dayNumber // ignore: cast_nullable_to_non_nullable
                  as int?,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        sessionId: freezed == sessionId
            ? _self.sessionId
            : sessionId // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [TodayWorkout].
extension TodayWorkoutPatterns on TodayWorkout {
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
    TResult Function(_TodayWorkout value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _TodayWorkout() when $default != null:
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
    TResult Function(_TodayWorkout value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TodayWorkout():
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
    TResult? Function(_TodayWorkout value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TodayWorkout() when $default != null:
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
      String workoutId,
      String? programDayId,
      String? programAssignmentId,
      String name,
      int? estimatedMinutes,
      int exerciseCount,
      int? weekNumber,
      int? dayNumber,
      String status,
      String? sessionId,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _TodayWorkout() when $default != null:
        return $default(
          _that.workoutId,
          _that.programDayId,
          _that.programAssignmentId,
          _that.name,
          _that.estimatedMinutes,
          _that.exerciseCount,
          _that.weekNumber,
          _that.dayNumber,
          _that.status,
          _that.sessionId,
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
      String workoutId,
      String? programDayId,
      String? programAssignmentId,
      String name,
      int? estimatedMinutes,
      int exerciseCount,
      int? weekNumber,
      int? dayNumber,
      String status,
      String? sessionId,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TodayWorkout():
        return $default(
          _that.workoutId,
          _that.programDayId,
          _that.programAssignmentId,
          _that.name,
          _that.estimatedMinutes,
          _that.exerciseCount,
          _that.weekNumber,
          _that.dayNumber,
          _that.status,
          _that.sessionId,
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
      String workoutId,
      String? programDayId,
      String? programAssignmentId,
      String name,
      int? estimatedMinutes,
      int exerciseCount,
      int? weekNumber,
      int? dayNumber,
      String status,
      String? sessionId,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TodayWorkout() when $default != null:
        return $default(
          _that.workoutId,
          _that.programDayId,
          _that.programAssignmentId,
          _that.name,
          _that.estimatedMinutes,
          _that.exerciseCount,
          _that.weekNumber,
          _that.dayNumber,
          _that.status,
          _that.sessionId,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _TodayWorkout implements TodayWorkout {
  const _TodayWorkout({
    required this.workoutId,
    this.programDayId,
    this.programAssignmentId,
    required this.name,
    this.estimatedMinutes,
    this.exerciseCount = 0,
    this.weekNumber,
    this.dayNumber,
    this.status = 'pending',
    this.sessionId,
  });
  factory _TodayWorkout.fromJson(Map<String, dynamic> json) =>
      _$TodayWorkoutFromJson(json);

  @override
  final String workoutId;
  @override
  final String? programDayId;
  @override
  final String? programAssignmentId;
  @override
  final String name;
  @override
  final int? estimatedMinutes;
  @override
  @JsonKey()
  final int exerciseCount;
  @override
  final int? weekNumber;
  @override
  final int? dayNumber;
  @override
  @JsonKey()
  final String status;
  @override
  final String? sessionId;

  /// Create a copy of TodayWorkout
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$TodayWorkoutCopyWith<_TodayWorkout> get copyWith =>
      __$TodayWorkoutCopyWithImpl<_TodayWorkout>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$TodayWorkoutToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _TodayWorkout &&
            (identical(other.workoutId, workoutId) ||
                other.workoutId == workoutId) &&
            (identical(other.programDayId, programDayId) ||
                other.programDayId == programDayId) &&
            (identical(other.programAssignmentId, programAssignmentId) ||
                other.programAssignmentId == programAssignmentId) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.estimatedMinutes, estimatedMinutes) ||
                other.estimatedMinutes == estimatedMinutes) &&
            (identical(other.exerciseCount, exerciseCount) ||
                other.exerciseCount == exerciseCount) &&
            (identical(other.weekNumber, weekNumber) ||
                other.weekNumber == weekNumber) &&
            (identical(other.dayNumber, dayNumber) ||
                other.dayNumber == dayNumber) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.sessionId, sessionId) ||
                other.sessionId == sessionId));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      workoutId,
      programDayId,
      programAssignmentId,
      name,
      estimatedMinutes,
      exerciseCount,
      weekNumber,
      dayNumber,
      status,
      sessionId,
    );
  }

  @override
  String toString() {
    return 'TodayWorkout(workoutId: $workoutId, programDayId: $programDayId, programAssignmentId: $programAssignmentId, name: $name, estimatedMinutes: $estimatedMinutes, exerciseCount: $exerciseCount, weekNumber: $weekNumber, dayNumber: $dayNumber, status: $status, sessionId: $sessionId)';
  }
}

/// @nodoc
abstract mixin class _$TodayWorkoutCopyWith<$Res>
    implements $TodayWorkoutCopyWith<$Res> {
  factory _$TodayWorkoutCopyWith(
    _TodayWorkout value,
    $Res Function(_TodayWorkout) _then,
  ) = __$TodayWorkoutCopyWithImpl;
  @override
  @useResult
  $Res call({
    String workoutId,
    String? programDayId,
    String? programAssignmentId,
    String name,
    int? estimatedMinutes,
    int exerciseCount,
    int? weekNumber,
    int? dayNumber,
    String status,
    String? sessionId,
  });
}

/// @nodoc
class __$TodayWorkoutCopyWithImpl<$Res>
    implements _$TodayWorkoutCopyWith<$Res> {
  __$TodayWorkoutCopyWithImpl(this._self, this._then);

  final _TodayWorkout _self;
  final $Res Function(_TodayWorkout) _then;

  /// Create a copy of TodayWorkout
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? workoutId = null,
    Object? programDayId = freezed,
    Object? programAssignmentId = freezed,
    Object? name = null,
    Object? estimatedMinutes = freezed,
    Object? exerciseCount = null,
    Object? weekNumber = freezed,
    Object? dayNumber = freezed,
    Object? status = null,
    Object? sessionId = freezed,
  }) {
    return _then(
      _TodayWorkout(
        workoutId: null == workoutId
            ? _self.workoutId
            : workoutId // ignore: cast_nullable_to_non_nullable
                  as String,
        programDayId: freezed == programDayId
            ? _self.programDayId
            : programDayId // ignore: cast_nullable_to_non_nullable
                  as String?,
        programAssignmentId: freezed == programAssignmentId
            ? _self.programAssignmentId
            : programAssignmentId // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        estimatedMinutes: freezed == estimatedMinutes
            ? _self.estimatedMinutes
            : estimatedMinutes // ignore: cast_nullable_to_non_nullable
                  as int?,
        exerciseCount: null == exerciseCount
            ? _self.exerciseCount
            : exerciseCount // ignore: cast_nullable_to_non_nullable
                  as int,
        weekNumber: freezed == weekNumber
            ? _self.weekNumber
            : weekNumber // ignore: cast_nullable_to_non_nullable
                  as int?,
        dayNumber: freezed == dayNumber
            ? _self.dayNumber
            : dayNumber // ignore: cast_nullable_to_non_nullable
                  as int?,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        sessionId: freezed == sessionId
            ? _self.sessionId
            : sessionId // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$Streak {
  int get current;
  int get best;

  /// Create a copy of Streak
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $StreakCopyWith<Streak> get copyWith =>
      _$StreakCopyWithImpl<Streak>(this as Streak, _$identity);

  /// Serializes this Streak to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Streak;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Streak &&
            (identical(other.current, _this.current) ||
                other.current == _this.current) &&
            (identical(other.best, _this.best) || other.best == _this.best));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Streak;
    return Object.hash(runtimeType, _this.current, _this.best);
  }

  @override
  String toString() {
    final _this = this as Streak;
    return 'Streak(current: ${_this.current}, best: ${_this.best})';
  }
}

/// @nodoc
abstract mixin class $StreakCopyWith<$Res> {
  factory $StreakCopyWith(Streak value, $Res Function(Streak) _then) =
      _$StreakCopyWithImpl;
  @useResult
  $Res call({int current, int best});
}

/// @nodoc
class _$StreakCopyWithImpl<$Res> implements $StreakCopyWith<$Res> {
  _$StreakCopyWithImpl(this._self, this._then);

  final Streak _self;
  final $Res Function(Streak) _then;

  /// Create a copy of Streak
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? current = null, Object? best = null}) {
    return _then(
      Streak(
        current: null == current
            ? _self.current
            : current // ignore: cast_nullable_to_non_nullable
                  as int,
        best: null == best
            ? _self.best
            : best // ignore: cast_nullable_to_non_nullable
                  as int,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Streak].
extension StreakPatterns on Streak {
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
    TResult Function(_Streak value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Streak() when $default != null:
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
    TResult Function(_Streak value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Streak():
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
    TResult? Function(_Streak value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Streak() when $default != null:
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
    TResult Function(int current, int best)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Streak() when $default != null:
        return $default(_that.current, _that.best);
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
    TResult Function(int current, int best) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Streak():
        return $default(_that.current, _that.best);
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
    TResult? Function(int current, int best)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Streak() when $default != null:
        return $default(_that.current, _that.best);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Streak implements Streak {
  const _Streak({this.current = 0, this.best = 0});
  factory _Streak.fromJson(Map<String, dynamic> json) => _$StreakFromJson(json);

  @override
  @JsonKey()
  final int current;
  @override
  @JsonKey()
  final int best;

  /// Create a copy of Streak
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$StreakCopyWith<_Streak> get copyWith =>
      __$StreakCopyWithImpl<_Streak>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$StreakToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Streak &&
            (identical(other.current, current) || other.current == current) &&
            (identical(other.best, best) || other.best == best));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, current, best);
  }

  @override
  String toString() {
    return 'Streak(current: $current, best: $best)';
  }
}

/// @nodoc
abstract mixin class _$StreakCopyWith<$Res> implements $StreakCopyWith<$Res> {
  factory _$StreakCopyWith(_Streak value, $Res Function(_Streak) _then) =
      __$StreakCopyWithImpl;
  @override
  @useResult
  $Res call({int current, int best});
}

/// @nodoc
class __$StreakCopyWithImpl<$Res> implements _$StreakCopyWith<$Res> {
  __$StreakCopyWithImpl(this._self, this._then);

  final _Streak _self;
  final $Res Function(_Streak) _then;

  /// Create a copy of Streak
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? current = null, Object? best = null}) {
    return _then(
      _Streak(
        current: null == current
            ? _self.current
            : current // ignore: cast_nullable_to_non_nullable
                  as int,
        best: null == best
            ? _self.best
            : best // ignore: cast_nullable_to_non_nullable
                  as int,
      ),
    );
  }
}

/// @nodoc
mixin _$TodayNutrition {
  Macros? get targets;
  Macros get consumed;
  double get waterMl;
  double get waterTargetMl;

  /// Create a copy of TodayNutrition
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $TodayNutritionCopyWith<TodayNutrition> get copyWith =>
      _$TodayNutritionCopyWithImpl<TodayNutrition>(
        this as TodayNutrition,
        _$identity,
      );

  /// Serializes this TodayNutrition to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as TodayNutrition;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is TodayNutrition &&
            (identical(other.targets, _this.targets) ||
                other.targets == _this.targets) &&
            (identical(other.consumed, _this.consumed) ||
                other.consumed == _this.consumed) &&
            (identical(other.waterMl, _this.waterMl) ||
                other.waterMl == _this.waterMl) &&
            (identical(other.waterTargetMl, _this.waterTargetMl) ||
                other.waterTargetMl == _this.waterTargetMl));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as TodayNutrition;
    return Object.hash(
      runtimeType,
      _this.targets,
      _this.consumed,
      _this.waterMl,
      _this.waterTargetMl,
    );
  }

  @override
  String toString() {
    final _this = this as TodayNutrition;
    return 'TodayNutrition(targets: ${_this.targets}, consumed: ${_this.consumed}, waterMl: ${_this.waterMl}, waterTargetMl: ${_this.waterTargetMl})';
  }
}

/// @nodoc
abstract mixin class $TodayNutritionCopyWith<$Res> {
  factory $TodayNutritionCopyWith(
    TodayNutrition value,
    $Res Function(TodayNutrition) _then,
  ) = _$TodayNutritionCopyWithImpl;
  @useResult
  $Res call({
    Macros? targets,
    Macros consumed,
    double waterMl,
    double waterTargetMl,
  });

  $MacrosCopyWith<$Res>? get targets;
  $MacrosCopyWith<$Res> get consumed;
}

/// @nodoc
class _$TodayNutritionCopyWithImpl<$Res>
    implements $TodayNutritionCopyWith<$Res> {
  _$TodayNutritionCopyWithImpl(this._self, this._then);

  final TodayNutrition _self;
  final $Res Function(TodayNutrition) _then;

  /// Create a copy of TodayNutrition
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? targets = freezed,
    Object? consumed = null,
    Object? waterMl = null,
    Object? waterTargetMl = null,
  }) {
    return _then(
      TodayNutrition(
        targets: freezed == targets
            ? _self.targets
            : targets // ignore: cast_nullable_to_non_nullable
                  as Macros?,
        consumed: null == consumed
            ? _self.consumed
            : consumed // ignore: cast_nullable_to_non_nullable
                  as Macros,
        waterMl: null == waterMl
            ? _self.waterMl
            : waterMl // ignore: cast_nullable_to_non_nullable
                  as double,
        waterTargetMl: null == waterTargetMl
            ? _self.waterTargetMl
            : waterTargetMl // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }

  /// Create a copy of TodayNutrition
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MacrosCopyWith<$Res>? get targets {
    if (_self.targets == null) {
      return null;
    }

    return $MacrosCopyWith<$Res>(_self.targets!, (value) {
      return _then(_self.copyWith(targets: value));
    });
  }

  /// Create a copy of TodayNutrition
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MacrosCopyWith<$Res> get consumed {
    return $MacrosCopyWith<$Res>(_self.consumed, (value) {
      return _then(_self.copyWith(consumed: value));
    });
  }
}

/// Adds pattern-matching-related methods to [TodayNutrition].
extension TodayNutritionPatterns on TodayNutrition {
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
    TResult Function(_TodayNutrition value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _TodayNutrition() when $default != null:
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
    TResult Function(_TodayNutrition value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TodayNutrition():
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
    TResult? Function(_TodayNutrition value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TodayNutrition() when $default != null:
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
      Macros? targets,
      Macros consumed,
      double waterMl,
      double waterTargetMl,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _TodayNutrition() when $default != null:
        return $default(
          _that.targets,
          _that.consumed,
          _that.waterMl,
          _that.waterTargetMl,
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
      Macros? targets,
      Macros consumed,
      double waterMl,
      double waterTargetMl,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TodayNutrition():
        return $default(
          _that.targets,
          _that.consumed,
          _that.waterMl,
          _that.waterTargetMl,
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
      Macros? targets,
      Macros consumed,
      double waterMl,
      double waterTargetMl,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TodayNutrition() when $default != null:
        return $default(
          _that.targets,
          _that.consumed,
          _that.waterMl,
          _that.waterTargetMl,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _TodayNutrition implements TodayNutrition {
  const _TodayNutrition({
    this.targets,
    this.consumed = const Macros(),
    this.waterMl = 0,
    this.waterTargetMl = 0,
  });
  factory _TodayNutrition.fromJson(Map<String, dynamic> json) =>
      _$TodayNutritionFromJson(json);

  @override
  final Macros? targets;
  @override
  @JsonKey()
  final Macros consumed;
  @override
  @JsonKey()
  final double waterMl;
  @override
  @JsonKey()
  final double waterTargetMl;

  /// Create a copy of TodayNutrition
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$TodayNutritionCopyWith<_TodayNutrition> get copyWith =>
      __$TodayNutritionCopyWithImpl<_TodayNutrition>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$TodayNutritionToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _TodayNutrition &&
            (identical(other.targets, targets) || other.targets == targets) &&
            (identical(other.consumed, consumed) ||
                other.consumed == consumed) &&
            (identical(other.waterMl, waterMl) || other.waterMl == waterMl) &&
            (identical(other.waterTargetMl, waterTargetMl) ||
                other.waterTargetMl == waterTargetMl));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, targets, consumed, waterMl, waterTargetMl);
  }

  @override
  String toString() {
    return 'TodayNutrition(targets: $targets, consumed: $consumed, waterMl: $waterMl, waterTargetMl: $waterTargetMl)';
  }
}

/// @nodoc
abstract mixin class _$TodayNutritionCopyWith<$Res>
    implements $TodayNutritionCopyWith<$Res> {
  factory _$TodayNutritionCopyWith(
    _TodayNutrition value,
    $Res Function(_TodayNutrition) _then,
  ) = __$TodayNutritionCopyWithImpl;
  @override
  @useResult
  $Res call({
    Macros? targets,
    Macros consumed,
    double waterMl,
    double waterTargetMl,
  });

  @override
  $MacrosCopyWith<$Res>? get targets;
  @override
  $MacrosCopyWith<$Res> get consumed;
}

/// @nodoc
class __$TodayNutritionCopyWithImpl<$Res>
    implements _$TodayNutritionCopyWith<$Res> {
  __$TodayNutritionCopyWithImpl(this._self, this._then);

  final _TodayNutrition _self;
  final $Res Function(_TodayNutrition) _then;

  /// Create a copy of TodayNutrition
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? targets = freezed,
    Object? consumed = null,
    Object? waterMl = null,
    Object? waterTargetMl = null,
  }) {
    return _then(
      _TodayNutrition(
        targets: freezed == targets
            ? _self.targets
            : targets // ignore: cast_nullable_to_non_nullable
                  as Macros?,
        consumed: null == consumed
            ? _self.consumed
            : consumed // ignore: cast_nullable_to_non_nullable
                  as Macros,
        waterMl: null == waterMl
            ? _self.waterMl
            : waterMl // ignore: cast_nullable_to_non_nullable
                  as double,
        waterTargetMl: null == waterTargetMl
            ? _self.waterTargetMl
            : waterTargetMl // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }

  /// Create a copy of TodayNutrition
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MacrosCopyWith<$Res>? get targets {
    if (_self.targets == null) {
      return null;
    }

    return $MacrosCopyWith<$Res>(_self.targets!, (value) {
      return _then(_self.copyWith(targets: value));
    });
  }

  /// Create a copy of TodayNutrition
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MacrosCopyWith<$Res> get consumed {
    return $MacrosCopyWith<$Res>(_self.consumed, (value) {
      return _then(_self.copyWith(consumed: value));
    });
  }
}

/// @nodoc
mixin _$HabitsSummary {
  int get completed;
  int get total;

  /// Create a copy of HabitsSummary
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $HabitsSummaryCopyWith<HabitsSummary> get copyWith =>
      _$HabitsSummaryCopyWithImpl<HabitsSummary>(
        this as HabitsSummary,
        _$identity,
      );

  /// Serializes this HabitsSummary to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as HabitsSummary;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is HabitsSummary &&
            (identical(other.completed, _this.completed) ||
                other.completed == _this.completed) &&
            (identical(other.total, _this.total) ||
                other.total == _this.total));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as HabitsSummary;
    return Object.hash(runtimeType, _this.completed, _this.total);
  }

  @override
  String toString() {
    final _this = this as HabitsSummary;
    return 'HabitsSummary(completed: ${_this.completed}, total: ${_this.total})';
  }
}

/// @nodoc
abstract mixin class $HabitsSummaryCopyWith<$Res> {
  factory $HabitsSummaryCopyWith(
    HabitsSummary value,
    $Res Function(HabitsSummary) _then,
  ) = _$HabitsSummaryCopyWithImpl;
  @useResult
  $Res call({int completed, int total});
}

/// @nodoc
class _$HabitsSummaryCopyWithImpl<$Res>
    implements $HabitsSummaryCopyWith<$Res> {
  _$HabitsSummaryCopyWithImpl(this._self, this._then);

  final HabitsSummary _self;
  final $Res Function(HabitsSummary) _then;

  /// Create a copy of HabitsSummary
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? completed = null, Object? total = null}) {
    return _then(
      HabitsSummary(
        completed: null == completed
            ? _self.completed
            : completed // ignore: cast_nullable_to_non_nullable
                  as int,
        total: null == total
            ? _self.total
            : total // ignore: cast_nullable_to_non_nullable
                  as int,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [HabitsSummary].
extension HabitsSummaryPatterns on HabitsSummary {
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
    TResult Function(_HabitsSummary value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _HabitsSummary() when $default != null:
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
    TResult Function(_HabitsSummary value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _HabitsSummary():
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
    TResult? Function(_HabitsSummary value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _HabitsSummary() when $default != null:
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
    TResult Function(int completed, int total)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _HabitsSummary() when $default != null:
        return $default(_that.completed, _that.total);
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
    TResult Function(int completed, int total) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _HabitsSummary():
        return $default(_that.completed, _that.total);
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
    TResult? Function(int completed, int total)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _HabitsSummary() when $default != null:
        return $default(_that.completed, _that.total);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _HabitsSummary implements HabitsSummary {
  const _HabitsSummary({this.completed = 0, this.total = 0});
  factory _HabitsSummary.fromJson(Map<String, dynamic> json) =>
      _$HabitsSummaryFromJson(json);

  @override
  @JsonKey()
  final int completed;
  @override
  @JsonKey()
  final int total;

  /// Create a copy of HabitsSummary
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$HabitsSummaryCopyWith<_HabitsSummary> get copyWith =>
      __$HabitsSummaryCopyWithImpl<_HabitsSummary>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$HabitsSummaryToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _HabitsSummary &&
            (identical(other.completed, completed) ||
                other.completed == completed) &&
            (identical(other.total, total) || other.total == total));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, completed, total);
  }

  @override
  String toString() {
    return 'HabitsSummary(completed: $completed, total: $total)';
  }
}

/// @nodoc
abstract mixin class _$HabitsSummaryCopyWith<$Res>
    implements $HabitsSummaryCopyWith<$Res> {
  factory _$HabitsSummaryCopyWith(
    _HabitsSummary value,
    $Res Function(_HabitsSummary) _then,
  ) = __$HabitsSummaryCopyWithImpl;
  @override
  @useResult
  $Res call({int completed, int total});
}

/// @nodoc
class __$HabitsSummaryCopyWithImpl<$Res>
    implements _$HabitsSummaryCopyWith<$Res> {
  __$HabitsSummaryCopyWithImpl(this._self, this._then);

  final _HabitsSummary _self;
  final $Res Function(_HabitsSummary) _then;

  /// Create a copy of HabitsSummary
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? completed = null, Object? total = null}) {
    return _then(
      _HabitsSummary(
        completed: null == completed
            ? _self.completed
            : completed // ignore: cast_nullable_to_non_nullable
                  as int,
        total: null == total
            ? _self.total
            : total // ignore: cast_nullable_to_non_nullable
                  as int,
      ),
    );
  }
}

/// @nodoc
mixin _$CheckinDue {
  String get id;
  String get dueOn;
  String? get formName;

  /// Create a copy of CheckinDue
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $CheckinDueCopyWith<CheckinDue> get copyWith =>
      _$CheckinDueCopyWithImpl<CheckinDue>(this as CheckinDue, _$identity);

  /// Serializes this CheckinDue to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as CheckinDue;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is CheckinDue &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.dueOn, _this.dueOn) ||
                other.dueOn == _this.dueOn) &&
            (identical(other.formName, _this.formName) ||
                other.formName == _this.formName));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as CheckinDue;
    return Object.hash(runtimeType, _this.id, _this.dueOn, _this.formName);
  }

  @override
  String toString() {
    final _this = this as CheckinDue;
    return 'CheckinDue(id: ${_this.id}, dueOn: ${_this.dueOn}, formName: ${_this.formName})';
  }
}

/// @nodoc
abstract mixin class $CheckinDueCopyWith<$Res> {
  factory $CheckinDueCopyWith(
    CheckinDue value,
    $Res Function(CheckinDue) _then,
  ) = _$CheckinDueCopyWithImpl;
  @useResult
  $Res call({String id, String dueOn, String? formName});
}

/// @nodoc
class _$CheckinDueCopyWithImpl<$Res> implements $CheckinDueCopyWith<$Res> {
  _$CheckinDueCopyWithImpl(this._self, this._then);

  final CheckinDue _self;
  final $Res Function(CheckinDue) _then;

  /// Create a copy of CheckinDue
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? dueOn = null,
    Object? formName = freezed,
  }) {
    return _then(
      CheckinDue(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        dueOn: null == dueOn
            ? _self.dueOn
            : dueOn // ignore: cast_nullable_to_non_nullable
                  as String,
        formName: freezed == formName
            ? _self.formName
            : formName // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [CheckinDue].
extension CheckinDuePatterns on CheckinDue {
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
    TResult Function(_CheckinDue value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinDue() when $default != null:
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
    TResult Function(_CheckinDue value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinDue():
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
    TResult? Function(_CheckinDue value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinDue() when $default != null:
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
    TResult Function(String id, String dueOn, String? formName)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinDue() when $default != null:
        return $default(_that.id, _that.dueOn, _that.formName);
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
    TResult Function(String id, String dueOn, String? formName) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinDue():
        return $default(_that.id, _that.dueOn, _that.formName);
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
    TResult? Function(String id, String dueOn, String? formName)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinDue() when $default != null:
        return $default(_that.id, _that.dueOn, _that.formName);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _CheckinDue implements CheckinDue {
  const _CheckinDue({required this.id, required this.dueOn, this.formName});
  factory _CheckinDue.fromJson(Map<String, dynamic> json) =>
      _$CheckinDueFromJson(json);

  @override
  final String id;
  @override
  final String dueOn;
  @override
  final String? formName;

  /// Create a copy of CheckinDue
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$CheckinDueCopyWith<_CheckinDue> get copyWith =>
      __$CheckinDueCopyWithImpl<_CheckinDue>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$CheckinDueToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _CheckinDue &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.dueOn, dueOn) || other.dueOn == dueOn) &&
            (identical(other.formName, formName) ||
                other.formName == formName));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, dueOn, formName);
  }

  @override
  String toString() {
    return 'CheckinDue(id: $id, dueOn: $dueOn, formName: $formName)';
  }
}

/// @nodoc
abstract mixin class _$CheckinDueCopyWith<$Res>
    implements $CheckinDueCopyWith<$Res> {
  factory _$CheckinDueCopyWith(
    _CheckinDue value,
    $Res Function(_CheckinDue) _then,
  ) = __$CheckinDueCopyWithImpl;
  @override
  @useResult
  $Res call({String id, String dueOn, String? formName});
}

/// @nodoc
class __$CheckinDueCopyWithImpl<$Res> implements _$CheckinDueCopyWith<$Res> {
  __$CheckinDueCopyWithImpl(this._self, this._then);

  final _CheckinDue _self;
  final $Res Function(_CheckinDue) _then;

  /// Create a copy of CheckinDue
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? dueOn = null,
    Object? formName = freezed,
  }) {
    return _then(
      _CheckinDue(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        dueOn: null == dueOn
            ? _self.dueOn
            : dueOn // ignore: cast_nullable_to_non_nullable
                  as String,
        formName: freezed == formName
            ? _self.formName
            : formName // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$Today {
  String get date;
  String? get greetingName;
  PersonRef? get coach;
  TodayWorkout? get workout;
  Streak get streak;
  Booking? get nextSession;
  TodayNutrition get nutrition;
  HabitsSummary get habits;
  CheckinDue? get checkinDue;
  int get unreadMessages;

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $TodayCopyWith<Today> get copyWith =>
      _$TodayCopyWithImpl<Today>(this as Today, _$identity);

  /// Serializes this Today to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Today;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Today &&
            (identical(other.date, _this.date) || other.date == _this.date) &&
            (identical(other.greetingName, _this.greetingName) ||
                other.greetingName == _this.greetingName) &&
            (identical(other.coach, _this.coach) ||
                other.coach == _this.coach) &&
            (identical(other.workout, _this.workout) ||
                other.workout == _this.workout) &&
            (identical(other.streak, _this.streak) ||
                other.streak == _this.streak) &&
            (identical(other.nextSession, _this.nextSession) ||
                other.nextSession == _this.nextSession) &&
            (identical(other.nutrition, _this.nutrition) ||
                other.nutrition == _this.nutrition) &&
            (identical(other.habits, _this.habits) ||
                other.habits == _this.habits) &&
            (identical(other.checkinDue, _this.checkinDue) ||
                other.checkinDue == _this.checkinDue) &&
            (identical(other.unreadMessages, _this.unreadMessages) ||
                other.unreadMessages == _this.unreadMessages));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Today;
    return Object.hash(
      runtimeType,
      _this.date,
      _this.greetingName,
      _this.coach,
      _this.workout,
      _this.streak,
      _this.nextSession,
      _this.nutrition,
      _this.habits,
      _this.checkinDue,
      _this.unreadMessages,
    );
  }

  @override
  String toString() {
    final _this = this as Today;
    return 'Today(date: ${_this.date}, greetingName: ${_this.greetingName}, coach: ${_this.coach}, workout: ${_this.workout}, streak: ${_this.streak}, nextSession: ${_this.nextSession}, nutrition: ${_this.nutrition}, habits: ${_this.habits}, checkinDue: ${_this.checkinDue}, unreadMessages: ${_this.unreadMessages})';
  }
}

/// @nodoc
abstract mixin class $TodayCopyWith<$Res> {
  factory $TodayCopyWith(Today value, $Res Function(Today) _then) =
      _$TodayCopyWithImpl;
  @useResult
  $Res call({
    String date,
    String? greetingName,
    PersonRef? coach,
    TodayWorkout? workout,
    Streak streak,
    Booking? nextSession,
    TodayNutrition nutrition,
    HabitsSummary habits,
    CheckinDue? checkinDue,
    int unreadMessages,
  });

  $PersonRefCopyWith<$Res>? get coach;
  $TodayWorkoutCopyWith<$Res>? get workout;
  $StreakCopyWith<$Res> get streak;
  $BookingCopyWith<$Res>? get nextSession;
  $TodayNutritionCopyWith<$Res> get nutrition;
  $HabitsSummaryCopyWith<$Res> get habits;
  $CheckinDueCopyWith<$Res>? get checkinDue;
}

/// @nodoc
class _$TodayCopyWithImpl<$Res> implements $TodayCopyWith<$Res> {
  _$TodayCopyWithImpl(this._self, this._then);

  final Today _self;
  final $Res Function(Today) _then;

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? date = null,
    Object? greetingName = freezed,
    Object? coach = freezed,
    Object? workout = freezed,
    Object? streak = null,
    Object? nextSession = freezed,
    Object? nutrition = null,
    Object? habits = null,
    Object? checkinDue = freezed,
    Object? unreadMessages = null,
  }) {
    return _then(
      Today(
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        greetingName: freezed == greetingName
            ? _self.greetingName
            : greetingName // ignore: cast_nullable_to_non_nullable
                  as String?,
        coach: freezed == coach
            ? _self.coach
            : coach // ignore: cast_nullable_to_non_nullable
                  as PersonRef?,
        workout: freezed == workout
            ? _self.workout
            : workout // ignore: cast_nullable_to_non_nullable
                  as TodayWorkout?,
        streak: null == streak
            ? _self.streak
            : streak // ignore: cast_nullable_to_non_nullable
                  as Streak,
        nextSession: freezed == nextSession
            ? _self.nextSession
            : nextSession // ignore: cast_nullable_to_non_nullable
                  as Booking?,
        nutrition: null == nutrition
            ? _self.nutrition
            : nutrition // ignore: cast_nullable_to_non_nullable
                  as TodayNutrition,
        habits: null == habits
            ? _self.habits
            : habits // ignore: cast_nullable_to_non_nullable
                  as HabitsSummary,
        checkinDue: freezed == checkinDue
            ? _self.checkinDue
            : checkinDue // ignore: cast_nullable_to_non_nullable
                  as CheckinDue?,
        unreadMessages: null == unreadMessages
            ? _self.unreadMessages
            : unreadMessages // ignore: cast_nullable_to_non_nullable
                  as int,
      ),
    );
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $PersonRefCopyWith<$Res>? get coach {
    if (_self.coach == null) {
      return null;
    }

    return $PersonRefCopyWith<$Res>(_self.coach!, (value) {
      return _then(_self.copyWith(coach: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $TodayWorkoutCopyWith<$Res>? get workout {
    if (_self.workout == null) {
      return null;
    }

    return $TodayWorkoutCopyWith<$Res>(_self.workout!, (value) {
      return _then(_self.copyWith(workout: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $StreakCopyWith<$Res> get streak {
    return $StreakCopyWith<$Res>(_self.streak, (value) {
      return _then(_self.copyWith(streak: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $BookingCopyWith<$Res>? get nextSession {
    if (_self.nextSession == null) {
      return null;
    }

    return $BookingCopyWith<$Res>(_self.nextSession!, (value) {
      return _then(_self.copyWith(nextSession: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $TodayNutritionCopyWith<$Res> get nutrition {
    return $TodayNutritionCopyWith<$Res>(_self.nutrition, (value) {
      return _then(_self.copyWith(nutrition: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $HabitsSummaryCopyWith<$Res> get habits {
    return $HabitsSummaryCopyWith<$Res>(_self.habits, (value) {
      return _then(_self.copyWith(habits: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $CheckinDueCopyWith<$Res>? get checkinDue {
    if (_self.checkinDue == null) {
      return null;
    }

    return $CheckinDueCopyWith<$Res>(_self.checkinDue!, (value) {
      return _then(_self.copyWith(checkinDue: value));
    });
  }
}

/// Adds pattern-matching-related methods to [Today].
extension TodayPatterns on Today {
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
    TResult Function(_Today value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Today() when $default != null:
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
    TResult Function(_Today value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Today():
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
    TResult? Function(_Today value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Today() when $default != null:
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
      String date,
      String? greetingName,
      PersonRef? coach,
      TodayWorkout? workout,
      Streak streak,
      Booking? nextSession,
      TodayNutrition nutrition,
      HabitsSummary habits,
      CheckinDue? checkinDue,
      int unreadMessages,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Today() when $default != null:
        return $default(
          _that.date,
          _that.greetingName,
          _that.coach,
          _that.workout,
          _that.streak,
          _that.nextSession,
          _that.nutrition,
          _that.habits,
          _that.checkinDue,
          _that.unreadMessages,
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
      String date,
      String? greetingName,
      PersonRef? coach,
      TodayWorkout? workout,
      Streak streak,
      Booking? nextSession,
      TodayNutrition nutrition,
      HabitsSummary habits,
      CheckinDue? checkinDue,
      int unreadMessages,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Today():
        return $default(
          _that.date,
          _that.greetingName,
          _that.coach,
          _that.workout,
          _that.streak,
          _that.nextSession,
          _that.nutrition,
          _that.habits,
          _that.checkinDue,
          _that.unreadMessages,
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
      String date,
      String? greetingName,
      PersonRef? coach,
      TodayWorkout? workout,
      Streak streak,
      Booking? nextSession,
      TodayNutrition nutrition,
      HabitsSummary habits,
      CheckinDue? checkinDue,
      int unreadMessages,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Today() when $default != null:
        return $default(
          _that.date,
          _that.greetingName,
          _that.coach,
          _that.workout,
          _that.streak,
          _that.nextSession,
          _that.nutrition,
          _that.habits,
          _that.checkinDue,
          _that.unreadMessages,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Today implements Today {
  const _Today({
    required this.date,
    this.greetingName,
    this.coach,
    this.workout,
    this.streak = const Streak(),
    this.nextSession,
    this.nutrition = const TodayNutrition(),
    this.habits = const HabitsSummary(),
    this.checkinDue,
    this.unreadMessages = 0,
  });
  factory _Today.fromJson(Map<String, dynamic> json) => _$TodayFromJson(json);

  @override
  final String date;
  @override
  final String? greetingName;
  @override
  final PersonRef? coach;
  @override
  final TodayWorkout? workout;
  @override
  @JsonKey()
  final Streak streak;
  @override
  final Booking? nextSession;
  @override
  @JsonKey()
  final TodayNutrition nutrition;
  @override
  @JsonKey()
  final HabitsSummary habits;
  @override
  final CheckinDue? checkinDue;
  @override
  @JsonKey()
  final int unreadMessages;

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$TodayCopyWith<_Today> get copyWith =>
      __$TodayCopyWithImpl<_Today>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$TodayToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Today &&
            (identical(other.date, date) || other.date == date) &&
            (identical(other.greetingName, greetingName) ||
                other.greetingName == greetingName) &&
            (identical(other.coach, coach) || other.coach == coach) &&
            (identical(other.workout, workout) || other.workout == workout) &&
            (identical(other.streak, streak) || other.streak == streak) &&
            (identical(other.nextSession, nextSession) ||
                other.nextSession == nextSession) &&
            (identical(other.nutrition, nutrition) ||
                other.nutrition == nutrition) &&
            (identical(other.habits, habits) || other.habits == habits) &&
            (identical(other.checkinDue, checkinDue) ||
                other.checkinDue == checkinDue) &&
            (identical(other.unreadMessages, unreadMessages) ||
                other.unreadMessages == unreadMessages));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      date,
      greetingName,
      coach,
      workout,
      streak,
      nextSession,
      nutrition,
      habits,
      checkinDue,
      unreadMessages,
    );
  }

  @override
  String toString() {
    return 'Today(date: $date, greetingName: $greetingName, coach: $coach, workout: $workout, streak: $streak, nextSession: $nextSession, nutrition: $nutrition, habits: $habits, checkinDue: $checkinDue, unreadMessages: $unreadMessages)';
  }
}

/// @nodoc
abstract mixin class _$TodayCopyWith<$Res> implements $TodayCopyWith<$Res> {
  factory _$TodayCopyWith(_Today value, $Res Function(_Today) _then) =
      __$TodayCopyWithImpl;
  @override
  @useResult
  $Res call({
    String date,
    String? greetingName,
    PersonRef? coach,
    TodayWorkout? workout,
    Streak streak,
    Booking? nextSession,
    TodayNutrition nutrition,
    HabitsSummary habits,
    CheckinDue? checkinDue,
    int unreadMessages,
  });

  @override
  $PersonRefCopyWith<$Res>? get coach;
  @override
  $TodayWorkoutCopyWith<$Res>? get workout;
  @override
  $StreakCopyWith<$Res> get streak;
  @override
  $BookingCopyWith<$Res>? get nextSession;
  @override
  $TodayNutritionCopyWith<$Res> get nutrition;
  @override
  $HabitsSummaryCopyWith<$Res> get habits;
  @override
  $CheckinDueCopyWith<$Res>? get checkinDue;
}

/// @nodoc
class __$TodayCopyWithImpl<$Res> implements _$TodayCopyWith<$Res> {
  __$TodayCopyWithImpl(this._self, this._then);

  final _Today _self;
  final $Res Function(_Today) _then;

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? date = null,
    Object? greetingName = freezed,
    Object? coach = freezed,
    Object? workout = freezed,
    Object? streak = null,
    Object? nextSession = freezed,
    Object? nutrition = null,
    Object? habits = null,
    Object? checkinDue = freezed,
    Object? unreadMessages = null,
  }) {
    return _then(
      _Today(
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        greetingName: freezed == greetingName
            ? _self.greetingName
            : greetingName // ignore: cast_nullable_to_non_nullable
                  as String?,
        coach: freezed == coach
            ? _self.coach
            : coach // ignore: cast_nullable_to_non_nullable
                  as PersonRef?,
        workout: freezed == workout
            ? _self.workout
            : workout // ignore: cast_nullable_to_non_nullable
                  as TodayWorkout?,
        streak: null == streak
            ? _self.streak
            : streak // ignore: cast_nullable_to_non_nullable
                  as Streak,
        nextSession: freezed == nextSession
            ? _self.nextSession
            : nextSession // ignore: cast_nullable_to_non_nullable
                  as Booking?,
        nutrition: null == nutrition
            ? _self.nutrition
            : nutrition // ignore: cast_nullable_to_non_nullable
                  as TodayNutrition,
        habits: null == habits
            ? _self.habits
            : habits // ignore: cast_nullable_to_non_nullable
                  as HabitsSummary,
        checkinDue: freezed == checkinDue
            ? _self.checkinDue
            : checkinDue // ignore: cast_nullable_to_non_nullable
                  as CheckinDue?,
        unreadMessages: null == unreadMessages
            ? _self.unreadMessages
            : unreadMessages // ignore: cast_nullable_to_non_nullable
                  as int,
      ),
    );
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $PersonRefCopyWith<$Res>? get coach {
    if (_self.coach == null) {
      return null;
    }

    return $PersonRefCopyWith<$Res>(_self.coach!, (value) {
      return _then(_self.copyWith(coach: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $TodayWorkoutCopyWith<$Res>? get workout {
    if (_self.workout == null) {
      return null;
    }

    return $TodayWorkoutCopyWith<$Res>(_self.workout!, (value) {
      return _then(_self.copyWith(workout: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $StreakCopyWith<$Res> get streak {
    return $StreakCopyWith<$Res>(_self.streak, (value) {
      return _then(_self.copyWith(streak: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $BookingCopyWith<$Res>? get nextSession {
    if (_self.nextSession == null) {
      return null;
    }

    return $BookingCopyWith<$Res>(_self.nextSession!, (value) {
      return _then(_self.copyWith(nextSession: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $TodayNutritionCopyWith<$Res> get nutrition {
    return $TodayNutritionCopyWith<$Res>(_self.nutrition, (value) {
      return _then(_self.copyWith(nutrition: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $HabitsSummaryCopyWith<$Res> get habits {
    return $HabitsSummaryCopyWith<$Res>(_self.habits, (value) {
      return _then(_self.copyWith(habits: value));
    });
  }

  /// Create a copy of Today
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $CheckinDueCopyWith<$Res>? get checkinDue {
    if (_self.checkinDue == null) {
      return null;
    }

    return $CheckinDueCopyWith<$Res>(_self.checkinDue!, (value) {
      return _then(_self.copyWith(checkinDue: value));
    });
  }
}
