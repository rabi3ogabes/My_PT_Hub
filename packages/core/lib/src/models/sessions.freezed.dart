// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'sessions.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$SessionSet {
  String get id;
  String get exerciseId;
  String? get workoutExerciseId;
  int get setNumber;
  @JsonKey(unknownEnumValue: SetType.working)
  SetType get setType;
  int? get reps;
  double? get weightKg;
  int? get durationSeconds;
  double? get distanceM;
  double? get rpe;
  bool get isCompleted;
  DateTime? get completedAt;

  /// Create a copy of SessionSet
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $SessionSetCopyWith<SessionSet> get copyWith =>
      _$SessionSetCopyWithImpl<SessionSet>(this as SessionSet, _$identity);

  /// Serializes this SessionSet to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as SessionSet;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is SessionSet &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.exerciseId, _this.exerciseId) ||
                other.exerciseId == _this.exerciseId) &&
            (identical(other.workoutExerciseId, _this.workoutExerciseId) ||
                other.workoutExerciseId == _this.workoutExerciseId) &&
            (identical(other.setNumber, _this.setNumber) ||
                other.setNumber == _this.setNumber) &&
            (identical(other.setType, _this.setType) ||
                other.setType == _this.setType) &&
            (identical(other.reps, _this.reps) || other.reps == _this.reps) &&
            (identical(other.weightKg, _this.weightKg) ||
                other.weightKg == _this.weightKg) &&
            (identical(other.durationSeconds, _this.durationSeconds) ||
                other.durationSeconds == _this.durationSeconds) &&
            (identical(other.distanceM, _this.distanceM) ||
                other.distanceM == _this.distanceM) &&
            (identical(other.rpe, _this.rpe) || other.rpe == _this.rpe) &&
            (identical(other.isCompleted, _this.isCompleted) ||
                other.isCompleted == _this.isCompleted) &&
            (identical(other.completedAt, _this.completedAt) ||
                other.completedAt == _this.completedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as SessionSet;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.exerciseId,
      _this.workoutExerciseId,
      _this.setNumber,
      _this.setType,
      _this.reps,
      _this.weightKg,
      _this.durationSeconds,
      _this.distanceM,
      _this.rpe,
      _this.isCompleted,
      _this.completedAt,
    );
  }

  @override
  String toString() {
    final _this = this as SessionSet;
    return 'SessionSet(id: ${_this.id}, exerciseId: ${_this.exerciseId}, workoutExerciseId: ${_this.workoutExerciseId}, setNumber: ${_this.setNumber}, setType: ${_this.setType}, reps: ${_this.reps}, weightKg: ${_this.weightKg}, durationSeconds: ${_this.durationSeconds}, distanceM: ${_this.distanceM}, rpe: ${_this.rpe}, isCompleted: ${_this.isCompleted}, completedAt: ${_this.completedAt})';
  }
}

/// @nodoc
abstract mixin class $SessionSetCopyWith<$Res> {
  factory $SessionSetCopyWith(
    SessionSet value,
    $Res Function(SessionSet) _then,
  ) = _$SessionSetCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String exerciseId,
    String? workoutExerciseId,
    int setNumber,
    @JsonKey(unknownEnumValue: SetType.working) SetType setType,
    int? reps,
    double? weightKg,
    int? durationSeconds,
    double? distanceM,
    double? rpe,
    bool isCompleted,
    DateTime? completedAt,
  });
}

/// @nodoc
class _$SessionSetCopyWithImpl<$Res> implements $SessionSetCopyWith<$Res> {
  _$SessionSetCopyWithImpl(this._self, this._then);

  final SessionSet _self;
  final $Res Function(SessionSet) _then;

  /// Create a copy of SessionSet
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? exerciseId = null,
    Object? workoutExerciseId = freezed,
    Object? setNumber = null,
    Object? setType = null,
    Object? reps = freezed,
    Object? weightKg = freezed,
    Object? durationSeconds = freezed,
    Object? distanceM = freezed,
    Object? rpe = freezed,
    Object? isCompleted = null,
    Object? completedAt = freezed,
  }) {
    return _then(
      SessionSet(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        exerciseId: null == exerciseId
            ? _self.exerciseId
            : exerciseId // ignore: cast_nullable_to_non_nullable
                  as String,
        workoutExerciseId: freezed == workoutExerciseId
            ? _self.workoutExerciseId
            : workoutExerciseId // ignore: cast_nullable_to_non_nullable
                  as String?,
        setNumber: null == setNumber
            ? _self.setNumber
            : setNumber // ignore: cast_nullable_to_non_nullable
                  as int,
        setType: null == setType
            ? _self.setType
            : setType // ignore: cast_nullable_to_non_nullable
                  as SetType,
        reps: freezed == reps
            ? _self.reps
            : reps // ignore: cast_nullable_to_non_nullable
                  as int?,
        weightKg: freezed == weightKg
            ? _self.weightKg
            : weightKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        durationSeconds: freezed == durationSeconds
            ? _self.durationSeconds
            : durationSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        distanceM: freezed == distanceM
            ? _self.distanceM
            : distanceM // ignore: cast_nullable_to_non_nullable
                  as double?,
        rpe: freezed == rpe
            ? _self.rpe
            : rpe // ignore: cast_nullable_to_non_nullable
                  as double?,
        isCompleted: null == isCompleted
            ? _self.isCompleted
            : isCompleted // ignore: cast_nullable_to_non_nullable
                  as bool,
        completedAt: freezed == completedAt
            ? _self.completedAt
            : completedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [SessionSet].
extension SessionSetPatterns on SessionSet {
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
    TResult Function(_SessionSet value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SessionSet() when $default != null:
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
    TResult Function(_SessionSet value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SessionSet():
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
    TResult? Function(_SessionSet value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SessionSet() when $default != null:
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
      String exerciseId,
      String? workoutExerciseId,
      int setNumber,
      @JsonKey(unknownEnumValue: SetType.working) SetType setType,
      int? reps,
      double? weightKg,
      int? durationSeconds,
      double? distanceM,
      double? rpe,
      bool isCompleted,
      DateTime? completedAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SessionSet() when $default != null:
        return $default(
          _that.id,
          _that.exerciseId,
          _that.workoutExerciseId,
          _that.setNumber,
          _that.setType,
          _that.reps,
          _that.weightKg,
          _that.durationSeconds,
          _that.distanceM,
          _that.rpe,
          _that.isCompleted,
          _that.completedAt,
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
      String exerciseId,
      String? workoutExerciseId,
      int setNumber,
      @JsonKey(unknownEnumValue: SetType.working) SetType setType,
      int? reps,
      double? weightKg,
      int? durationSeconds,
      double? distanceM,
      double? rpe,
      bool isCompleted,
      DateTime? completedAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SessionSet():
        return $default(
          _that.id,
          _that.exerciseId,
          _that.workoutExerciseId,
          _that.setNumber,
          _that.setType,
          _that.reps,
          _that.weightKg,
          _that.durationSeconds,
          _that.distanceM,
          _that.rpe,
          _that.isCompleted,
          _that.completedAt,
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
      String exerciseId,
      String? workoutExerciseId,
      int setNumber,
      @JsonKey(unknownEnumValue: SetType.working) SetType setType,
      int? reps,
      double? weightKg,
      int? durationSeconds,
      double? distanceM,
      double? rpe,
      bool isCompleted,
      DateTime? completedAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SessionSet() when $default != null:
        return $default(
          _that.id,
          _that.exerciseId,
          _that.workoutExerciseId,
          _that.setNumber,
          _that.setType,
          _that.reps,
          _that.weightKg,
          _that.durationSeconds,
          _that.distanceM,
          _that.rpe,
          _that.isCompleted,
          _that.completedAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
@JsonDoubleN()
class _SessionSet implements SessionSet {
  const _SessionSet({
    required this.id,
    required this.exerciseId,
    this.workoutExerciseId,
    required this.setNumber,
    @JsonKey(unknownEnumValue: SetType.working) this.setType = SetType.working,
    this.reps,
    this.weightKg,
    this.durationSeconds,
    this.distanceM,
    this.rpe,
    this.isCompleted = false,
    this.completedAt,
  });
  factory _SessionSet.fromJson(Map<String, dynamic> json) =>
      _$SessionSetFromJson(json);

  @override
  final String id;
  @override
  final String exerciseId;
  @override
  final String? workoutExerciseId;
  @override
  final int setNumber;
  @override
  @JsonKey(unknownEnumValue: SetType.working)
  final SetType setType;
  @override
  final int? reps;
  @override
  final double? weightKg;
  @override
  final int? durationSeconds;
  @override
  final double? distanceM;
  @override
  final double? rpe;
  @override
  @JsonKey()
  final bool isCompleted;
  @override
  final DateTime? completedAt;

  /// Create a copy of SessionSet
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$SessionSetCopyWith<_SessionSet> get copyWith =>
      __$SessionSetCopyWithImpl<_SessionSet>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$SessionSetToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _SessionSet &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.exerciseId, exerciseId) ||
                other.exerciseId == exerciseId) &&
            (identical(other.workoutExerciseId, workoutExerciseId) ||
                other.workoutExerciseId == workoutExerciseId) &&
            (identical(other.setNumber, setNumber) ||
                other.setNumber == setNumber) &&
            (identical(other.setType, setType) || other.setType == setType) &&
            (identical(other.reps, reps) || other.reps == reps) &&
            (identical(other.weightKg, weightKg) ||
                other.weightKg == weightKg) &&
            (identical(other.durationSeconds, durationSeconds) ||
                other.durationSeconds == durationSeconds) &&
            (identical(other.distanceM, distanceM) ||
                other.distanceM == distanceM) &&
            (identical(other.rpe, rpe) || other.rpe == rpe) &&
            (identical(other.isCompleted, isCompleted) ||
                other.isCompleted == isCompleted) &&
            (identical(other.completedAt, completedAt) ||
                other.completedAt == completedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      exerciseId,
      workoutExerciseId,
      setNumber,
      setType,
      reps,
      weightKg,
      durationSeconds,
      distanceM,
      rpe,
      isCompleted,
      completedAt,
    );
  }

  @override
  String toString() {
    return 'SessionSet(id: $id, exerciseId: $exerciseId, workoutExerciseId: $workoutExerciseId, setNumber: $setNumber, setType: $setType, reps: $reps, weightKg: $weightKg, durationSeconds: $durationSeconds, distanceM: $distanceM, rpe: $rpe, isCompleted: $isCompleted, completedAt: $completedAt)';
  }
}

/// @nodoc
abstract mixin class _$SessionSetCopyWith<$Res>
    implements $SessionSetCopyWith<$Res> {
  factory _$SessionSetCopyWith(
    _SessionSet value,
    $Res Function(_SessionSet) _then,
  ) = __$SessionSetCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String exerciseId,
    String? workoutExerciseId,
    int setNumber,
    @JsonKey(unknownEnumValue: SetType.working) SetType setType,
    int? reps,
    double? weightKg,
    int? durationSeconds,
    double? distanceM,
    double? rpe,
    bool isCompleted,
    DateTime? completedAt,
  });
}

/// @nodoc
class __$SessionSetCopyWithImpl<$Res> implements _$SessionSetCopyWith<$Res> {
  __$SessionSetCopyWithImpl(this._self, this._then);

  final _SessionSet _self;
  final $Res Function(_SessionSet) _then;

  /// Create a copy of SessionSet
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? exerciseId = null,
    Object? workoutExerciseId = freezed,
    Object? setNumber = null,
    Object? setType = null,
    Object? reps = freezed,
    Object? weightKg = freezed,
    Object? durationSeconds = freezed,
    Object? distanceM = freezed,
    Object? rpe = freezed,
    Object? isCompleted = null,
    Object? completedAt = freezed,
  }) {
    return _then(
      _SessionSet(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        exerciseId: null == exerciseId
            ? _self.exerciseId
            : exerciseId // ignore: cast_nullable_to_non_nullable
                  as String,
        workoutExerciseId: freezed == workoutExerciseId
            ? _self.workoutExerciseId
            : workoutExerciseId // ignore: cast_nullable_to_non_nullable
                  as String?,
        setNumber: null == setNumber
            ? _self.setNumber
            : setNumber // ignore: cast_nullable_to_non_nullable
                  as int,
        setType: null == setType
            ? _self.setType
            : setType // ignore: cast_nullable_to_non_nullable
                  as SetType,
        reps: freezed == reps
            ? _self.reps
            : reps // ignore: cast_nullable_to_non_nullable
                  as int?,
        weightKg: freezed == weightKg
            ? _self.weightKg
            : weightKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        durationSeconds: freezed == durationSeconds
            ? _self.durationSeconds
            : durationSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        distanceM: freezed == distanceM
            ? _self.distanceM
            : distanceM // ignore: cast_nullable_to_non_nullable
                  as double?,
        rpe: freezed == rpe
            ? _self.rpe
            : rpe // ignore: cast_nullable_to_non_nullable
                  as double?,
        isCompleted: null == isCompleted
            ? _self.isCompleted
            : isCompleted // ignore: cast_nullable_to_non_nullable
                  as bool,
        completedAt: freezed == completedAt
            ? _self.completedAt
            : completedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
mixin _$PersonalRecord {
  String get id;
  String get exerciseId;
  String get exerciseName;
  String get type;
  double get value;
  double? get previousValue;
  DateTime get achievedAt;

  /// Create a copy of PersonalRecord
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $PersonalRecordCopyWith<PersonalRecord> get copyWith =>
      _$PersonalRecordCopyWithImpl<PersonalRecord>(
        this as PersonalRecord,
        _$identity,
      );

  /// Serializes this PersonalRecord to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as PersonalRecord;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is PersonalRecord &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.exerciseId, _this.exerciseId) ||
                other.exerciseId == _this.exerciseId) &&
            (identical(other.exerciseName, _this.exerciseName) ||
                other.exerciseName == _this.exerciseName) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            (identical(other.value, _this.value) ||
                other.value == _this.value) &&
            (identical(other.previousValue, _this.previousValue) ||
                other.previousValue == _this.previousValue) &&
            (identical(other.achievedAt, _this.achievedAt) ||
                other.achievedAt == _this.achievedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as PersonalRecord;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.exerciseId,
      _this.exerciseName,
      _this.type,
      _this.value,
      _this.previousValue,
      _this.achievedAt,
    );
  }

  @override
  String toString() {
    final _this = this as PersonalRecord;
    return 'PersonalRecord(id: ${_this.id}, exerciseId: ${_this.exerciseId}, exerciseName: ${_this.exerciseName}, type: ${_this.type}, value: ${_this.value}, previousValue: ${_this.previousValue}, achievedAt: ${_this.achievedAt})';
  }
}

/// @nodoc
abstract mixin class $PersonalRecordCopyWith<$Res> {
  factory $PersonalRecordCopyWith(
    PersonalRecord value,
    $Res Function(PersonalRecord) _then,
  ) = _$PersonalRecordCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String exerciseId,
    String exerciseName,
    String type,
    double value,
    double? previousValue,
    DateTime achievedAt,
  });
}

/// @nodoc
class _$PersonalRecordCopyWithImpl<$Res>
    implements $PersonalRecordCopyWith<$Res> {
  _$PersonalRecordCopyWithImpl(this._self, this._then);

  final PersonalRecord _self;
  final $Res Function(PersonalRecord) _then;

  /// Create a copy of PersonalRecord
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? exerciseId = null,
    Object? exerciseName = null,
    Object? type = null,
    Object? value = null,
    Object? previousValue = freezed,
    Object? achievedAt = null,
  }) {
    return _then(
      PersonalRecord(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        exerciseId: null == exerciseId
            ? _self.exerciseId
            : exerciseId // ignore: cast_nullable_to_non_nullable
                  as String,
        exerciseName: null == exerciseName
            ? _self.exerciseName
            : exerciseName // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        value: null == value
            ? _self.value
            : value // ignore: cast_nullable_to_non_nullable
                  as double,
        previousValue: freezed == previousValue
            ? _self.previousValue
            : previousValue // ignore: cast_nullable_to_non_nullable
                  as double?,
        achievedAt: null == achievedAt
            ? _self.achievedAt
            : achievedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [PersonalRecord].
extension PersonalRecordPatterns on PersonalRecord {
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
    TResult Function(_PersonalRecord value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PersonalRecord() when $default != null:
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
    TResult Function(_PersonalRecord value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PersonalRecord():
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
    TResult? Function(_PersonalRecord value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PersonalRecord() when $default != null:
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
      String exerciseId,
      String exerciseName,
      String type,
      double value,
      double? previousValue,
      DateTime achievedAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PersonalRecord() when $default != null:
        return $default(
          _that.id,
          _that.exerciseId,
          _that.exerciseName,
          _that.type,
          _that.value,
          _that.previousValue,
          _that.achievedAt,
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
      String exerciseId,
      String exerciseName,
      String type,
      double value,
      double? previousValue,
      DateTime achievedAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PersonalRecord():
        return $default(
          _that.id,
          _that.exerciseId,
          _that.exerciseName,
          _that.type,
          _that.value,
          _that.previousValue,
          _that.achievedAt,
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
      String exerciseId,
      String exerciseName,
      String type,
      double value,
      double? previousValue,
      DateTime achievedAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PersonalRecord() when $default != null:
        return $default(
          _that.id,
          _that.exerciseId,
          _that.exerciseName,
          _that.type,
          _that.value,
          _that.previousValue,
          _that.achievedAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
@JsonDoubleN()
class _PersonalRecord implements PersonalRecord {
  const _PersonalRecord({
    required this.id,
    required this.exerciseId,
    required this.exerciseName,
    required this.type,
    required this.value,
    this.previousValue,
    required this.achievedAt,
  });
  factory _PersonalRecord.fromJson(Map<String, dynamic> json) =>
      _$PersonalRecordFromJson(json);

  @override
  final String id;
  @override
  final String exerciseId;
  @override
  final String exerciseName;
  @override
  final String type;
  @override
  final double value;
  @override
  final double? previousValue;
  @override
  final DateTime achievedAt;

  /// Create a copy of PersonalRecord
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$PersonalRecordCopyWith<_PersonalRecord> get copyWith =>
      __$PersonalRecordCopyWithImpl<_PersonalRecord>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$PersonalRecordToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _PersonalRecord &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.exerciseId, exerciseId) ||
                other.exerciseId == exerciseId) &&
            (identical(other.exerciseName, exerciseName) ||
                other.exerciseName == exerciseName) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.value, value) || other.value == value) &&
            (identical(other.previousValue, previousValue) ||
                other.previousValue == previousValue) &&
            (identical(other.achievedAt, achievedAt) ||
                other.achievedAt == achievedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      exerciseId,
      exerciseName,
      type,
      value,
      previousValue,
      achievedAt,
    );
  }

  @override
  String toString() {
    return 'PersonalRecord(id: $id, exerciseId: $exerciseId, exerciseName: $exerciseName, type: $type, value: $value, previousValue: $previousValue, achievedAt: $achievedAt)';
  }
}

/// @nodoc
abstract mixin class _$PersonalRecordCopyWith<$Res>
    implements $PersonalRecordCopyWith<$Res> {
  factory _$PersonalRecordCopyWith(
    _PersonalRecord value,
    $Res Function(_PersonalRecord) _then,
  ) = __$PersonalRecordCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String exerciseId,
    String exerciseName,
    String type,
    double value,
    double? previousValue,
    DateTime achievedAt,
  });
}

/// @nodoc
class __$PersonalRecordCopyWithImpl<$Res>
    implements _$PersonalRecordCopyWith<$Res> {
  __$PersonalRecordCopyWithImpl(this._self, this._then);

  final _PersonalRecord _self;
  final $Res Function(_PersonalRecord) _then;

  /// Create a copy of PersonalRecord
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? exerciseId = null,
    Object? exerciseName = null,
    Object? type = null,
    Object? value = null,
    Object? previousValue = freezed,
    Object? achievedAt = null,
  }) {
    return _then(
      _PersonalRecord(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        exerciseId: null == exerciseId
            ? _self.exerciseId
            : exerciseId // ignore: cast_nullable_to_non_nullable
                  as String,
        exerciseName: null == exerciseName
            ? _self.exerciseName
            : exerciseName // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        value: null == value
            ? _self.value
            : value // ignore: cast_nullable_to_non_nullable
                  as double,
        previousValue: freezed == previousValue
            ? _self.previousValue
            : previousValue // ignore: cast_nullable_to_non_nullable
                  as double?,
        achievedAt: null == achievedAt
            ? _self.achievedAt
            : achievedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// @nodoc
mixin _$WorkoutSession {
  String get id;
  String? get workoutId;
  String? get programDayId;
  String? get name;
  @JsonKey(unknownEnumValue: SessionStatus.inProgress)
  SessionStatus get status;
  DateTime? get startedAt;
  DateTime? get endedAt;
  int? get durationSeconds;
  double? get totalVolumeKg;
  int? get perceivedEffort;
  String? get mood;
  String? get notes;
  List<SessionSet> get sets;
  List<PersonalRecord> get personalRecords;

  /// Create a copy of WorkoutSession
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $WorkoutSessionCopyWith<WorkoutSession> get copyWith =>
      _$WorkoutSessionCopyWithImpl<WorkoutSession>(
        this as WorkoutSession,
        _$identity,
      );

  /// Serializes this WorkoutSession to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as WorkoutSession;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is WorkoutSession &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.workoutId, _this.workoutId) ||
                other.workoutId == _this.workoutId) &&
            (identical(other.programDayId, _this.programDayId) ||
                other.programDayId == _this.programDayId) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status) &&
            (identical(other.startedAt, _this.startedAt) ||
                other.startedAt == _this.startedAt) &&
            (identical(other.endedAt, _this.endedAt) ||
                other.endedAt == _this.endedAt) &&
            (identical(other.durationSeconds, _this.durationSeconds) ||
                other.durationSeconds == _this.durationSeconds) &&
            (identical(other.totalVolumeKg, _this.totalVolumeKg) ||
                other.totalVolumeKg == _this.totalVolumeKg) &&
            (identical(other.perceivedEffort, _this.perceivedEffort) ||
                other.perceivedEffort == _this.perceivedEffort) &&
            (identical(other.mood, _this.mood) || other.mood == _this.mood) &&
            (identical(other.notes, _this.notes) ||
                other.notes == _this.notes) &&
            const DeepCollectionEquality().equals(other.sets, _this.sets) &&
            const DeepCollectionEquality().equals(
              other.personalRecords,
              _this.personalRecords,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as WorkoutSession;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.workoutId,
      _this.programDayId,
      _this.name,
      _this.status,
      _this.startedAt,
      _this.endedAt,
      _this.durationSeconds,
      _this.totalVolumeKg,
      _this.perceivedEffort,
      _this.mood,
      _this.notes,
      const DeepCollectionEquality().hash(_this.sets),
      const DeepCollectionEquality().hash(_this.personalRecords),
    );
  }

  @override
  String toString() {
    final _this = this as WorkoutSession;
    return 'WorkoutSession(id: ${_this.id}, workoutId: ${_this.workoutId}, programDayId: ${_this.programDayId}, name: ${_this.name}, status: ${_this.status}, startedAt: ${_this.startedAt}, endedAt: ${_this.endedAt}, durationSeconds: ${_this.durationSeconds}, totalVolumeKg: ${_this.totalVolumeKg}, perceivedEffort: ${_this.perceivedEffort}, mood: ${_this.mood}, notes: ${_this.notes}, sets: ${_this.sets}, personalRecords: ${_this.personalRecords})';
  }
}

/// @nodoc
abstract mixin class $WorkoutSessionCopyWith<$Res> {
  factory $WorkoutSessionCopyWith(
    WorkoutSession value,
    $Res Function(WorkoutSession) _then,
  ) = _$WorkoutSessionCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String? workoutId,
    String? programDayId,
    String? name,
    @JsonKey(unknownEnumValue: SessionStatus.inProgress) SessionStatus status,
    DateTime? startedAt,
    DateTime? endedAt,
    int? durationSeconds,
    double? totalVolumeKg,
    int? perceivedEffort,
    String? mood,
    String? notes,
    List<SessionSet> sets,
    List<PersonalRecord> personalRecords,
  });
}

/// @nodoc
class _$WorkoutSessionCopyWithImpl<$Res>
    implements $WorkoutSessionCopyWith<$Res> {
  _$WorkoutSessionCopyWithImpl(this._self, this._then);

  final WorkoutSession _self;
  final $Res Function(WorkoutSession) _then;

  /// Create a copy of WorkoutSession
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? workoutId = freezed,
    Object? programDayId = freezed,
    Object? name = freezed,
    Object? status = null,
    Object? startedAt = freezed,
    Object? endedAt = freezed,
    Object? durationSeconds = freezed,
    Object? totalVolumeKg = freezed,
    Object? perceivedEffort = freezed,
    Object? mood = freezed,
    Object? notes = freezed,
    Object? sets = null,
    Object? personalRecords = null,
  }) {
    return _then(
      WorkoutSession(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        workoutId: freezed == workoutId
            ? _self.workoutId
            : workoutId // ignore: cast_nullable_to_non_nullable
                  as String?,
        programDayId: freezed == programDayId
            ? _self.programDayId
            : programDayId // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: freezed == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String?,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as SessionStatus,
        startedAt: freezed == startedAt
            ? _self.startedAt
            : startedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        endedAt: freezed == endedAt
            ? _self.endedAt
            : endedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        durationSeconds: freezed == durationSeconds
            ? _self.durationSeconds
            : durationSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        totalVolumeKg: freezed == totalVolumeKg
            ? _self.totalVolumeKg
            : totalVolumeKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        perceivedEffort: freezed == perceivedEffort
            ? _self.perceivedEffort
            : perceivedEffort // ignore: cast_nullable_to_non_nullable
                  as int?,
        mood: freezed == mood
            ? _self.mood
            : mood // ignore: cast_nullable_to_non_nullable
                  as String?,
        notes: freezed == notes
            ? _self.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        sets: null == sets
            ? _self.sets
            : sets // ignore: cast_nullable_to_non_nullable
                  as List<SessionSet>,
        personalRecords: null == personalRecords
            ? _self.personalRecords
            : personalRecords // ignore: cast_nullable_to_non_nullable
                  as List<PersonalRecord>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [WorkoutSession].
extension WorkoutSessionPatterns on WorkoutSession {
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
    TResult Function(_WorkoutSession value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _WorkoutSession() when $default != null:
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
    TResult Function(_WorkoutSession value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutSession():
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
    TResult? Function(_WorkoutSession value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutSession() when $default != null:
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
      String? workoutId,
      String? programDayId,
      String? name,
      @JsonKey(unknownEnumValue: SessionStatus.inProgress) SessionStatus status,
      DateTime? startedAt,
      DateTime? endedAt,
      int? durationSeconds,
      double? totalVolumeKg,
      int? perceivedEffort,
      String? mood,
      String? notes,
      List<SessionSet> sets,
      List<PersonalRecord> personalRecords,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _WorkoutSession() when $default != null:
        return $default(
          _that.id,
          _that.workoutId,
          _that.programDayId,
          _that.name,
          _that.status,
          _that.startedAt,
          _that.endedAt,
          _that.durationSeconds,
          _that.totalVolumeKg,
          _that.perceivedEffort,
          _that.mood,
          _that.notes,
          _that.sets,
          _that.personalRecords,
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
      String? workoutId,
      String? programDayId,
      String? name,
      @JsonKey(unknownEnumValue: SessionStatus.inProgress) SessionStatus status,
      DateTime? startedAt,
      DateTime? endedAt,
      int? durationSeconds,
      double? totalVolumeKg,
      int? perceivedEffort,
      String? mood,
      String? notes,
      List<SessionSet> sets,
      List<PersonalRecord> personalRecords,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutSession():
        return $default(
          _that.id,
          _that.workoutId,
          _that.programDayId,
          _that.name,
          _that.status,
          _that.startedAt,
          _that.endedAt,
          _that.durationSeconds,
          _that.totalVolumeKg,
          _that.perceivedEffort,
          _that.mood,
          _that.notes,
          _that.sets,
          _that.personalRecords,
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
      String? workoutId,
      String? programDayId,
      String? name,
      @JsonKey(unknownEnumValue: SessionStatus.inProgress) SessionStatus status,
      DateTime? startedAt,
      DateTime? endedAt,
      int? durationSeconds,
      double? totalVolumeKg,
      int? perceivedEffort,
      String? mood,
      String? notes,
      List<SessionSet> sets,
      List<PersonalRecord> personalRecords,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutSession() when $default != null:
        return $default(
          _that.id,
          _that.workoutId,
          _that.programDayId,
          _that.name,
          _that.status,
          _that.startedAt,
          _that.endedAt,
          _that.durationSeconds,
          _that.totalVolumeKg,
          _that.perceivedEffort,
          _that.mood,
          _that.notes,
          _that.sets,
          _that.personalRecords,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDoubleN()
class _WorkoutSession implements WorkoutSession {
  const _WorkoutSession({
    required this.id,
    this.workoutId,
    this.programDayId,
    this.name,
    @JsonKey(unknownEnumValue: SessionStatus.inProgress)
    this.status = SessionStatus.inProgress,
    this.startedAt,
    this.endedAt,
    this.durationSeconds,
    this.totalVolumeKg,
    this.perceivedEffort,
    this.mood,
    this.notes,
    List<SessionSet> sets = const <SessionSet>[],
    List<PersonalRecord> personalRecords = const <PersonalRecord>[],
  }) : _sets = sets,
       _personalRecords = personalRecords;
  factory _WorkoutSession.fromJson(Map<String, dynamic> json) =>
      _$WorkoutSessionFromJson(json);

  @override
  final String id;
  @override
  final String? workoutId;
  @override
  final String? programDayId;
  @override
  final String? name;
  @override
  @JsonKey(unknownEnumValue: SessionStatus.inProgress)
  final SessionStatus status;
  @override
  final DateTime? startedAt;
  @override
  final DateTime? endedAt;
  @override
  final int? durationSeconds;
  @override
  final double? totalVolumeKg;
  @override
  final int? perceivedEffort;
  @override
  final String? mood;
  @override
  final String? notes;
  final List<SessionSet> _sets;
  @override
  @JsonKey()
  List<SessionSet> get sets {
    if (_sets is EqualUnmodifiableListView) return _sets;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_sets);
  }

  final List<PersonalRecord> _personalRecords;
  @override
  @JsonKey()
  List<PersonalRecord> get personalRecords {
    if (_personalRecords is EqualUnmodifiableListView) return _personalRecords;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_personalRecords);
  }

  /// Create a copy of WorkoutSession
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$WorkoutSessionCopyWith<_WorkoutSession> get copyWith =>
      __$WorkoutSessionCopyWithImpl<_WorkoutSession>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$WorkoutSessionToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _WorkoutSession &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.workoutId, workoutId) ||
                other.workoutId == workoutId) &&
            (identical(other.programDayId, programDayId) ||
                other.programDayId == programDayId) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.startedAt, startedAt) ||
                other.startedAt == startedAt) &&
            (identical(other.endedAt, endedAt) || other.endedAt == endedAt) &&
            (identical(other.durationSeconds, durationSeconds) ||
                other.durationSeconds == durationSeconds) &&
            (identical(other.totalVolumeKg, totalVolumeKg) ||
                other.totalVolumeKg == totalVolumeKg) &&
            (identical(other.perceivedEffort, perceivedEffort) ||
                other.perceivedEffort == perceivedEffort) &&
            (identical(other.mood, mood) || other.mood == mood) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            const DeepCollectionEquality().equals(other.sets, _sets) &&
            const DeepCollectionEquality().equals(
              other.personalRecords,
              _personalRecords,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      workoutId,
      programDayId,
      name,
      status,
      startedAt,
      endedAt,
      durationSeconds,
      totalVolumeKg,
      perceivedEffort,
      mood,
      notes,
      const DeepCollectionEquality().hash(_sets),
      const DeepCollectionEquality().hash(_personalRecords),
    );
  }

  @override
  String toString() {
    return 'WorkoutSession(id: $id, workoutId: $workoutId, programDayId: $programDayId, name: $name, status: $status, startedAt: $startedAt, endedAt: $endedAt, durationSeconds: $durationSeconds, totalVolumeKg: $totalVolumeKg, perceivedEffort: $perceivedEffort, mood: $mood, notes: $notes, sets: $sets, personalRecords: $personalRecords)';
  }
}

/// @nodoc
abstract mixin class _$WorkoutSessionCopyWith<$Res>
    implements $WorkoutSessionCopyWith<$Res> {
  factory _$WorkoutSessionCopyWith(
    _WorkoutSession value,
    $Res Function(_WorkoutSession) _then,
  ) = __$WorkoutSessionCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String? workoutId,
    String? programDayId,
    String? name,
    @JsonKey(unknownEnumValue: SessionStatus.inProgress) SessionStatus status,
    DateTime? startedAt,
    DateTime? endedAt,
    int? durationSeconds,
    double? totalVolumeKg,
    int? perceivedEffort,
    String? mood,
    String? notes,
    List<SessionSet> sets,
    List<PersonalRecord> personalRecords,
  });
}

/// @nodoc
class __$WorkoutSessionCopyWithImpl<$Res>
    implements _$WorkoutSessionCopyWith<$Res> {
  __$WorkoutSessionCopyWithImpl(this._self, this._then);

  final _WorkoutSession _self;
  final $Res Function(_WorkoutSession) _then;

  /// Create a copy of WorkoutSession
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? workoutId = freezed,
    Object? programDayId = freezed,
    Object? name = freezed,
    Object? status = null,
    Object? startedAt = freezed,
    Object? endedAt = freezed,
    Object? durationSeconds = freezed,
    Object? totalVolumeKg = freezed,
    Object? perceivedEffort = freezed,
    Object? mood = freezed,
    Object? notes = freezed,
    Object? sets = null,
    Object? personalRecords = null,
  }) {
    return _then(
      _WorkoutSession(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        workoutId: freezed == workoutId
            ? _self.workoutId
            : workoutId // ignore: cast_nullable_to_non_nullable
                  as String?,
        programDayId: freezed == programDayId
            ? _self.programDayId
            : programDayId // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: freezed == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String?,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as SessionStatus,
        startedAt: freezed == startedAt
            ? _self.startedAt
            : startedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        endedAt: freezed == endedAt
            ? _self.endedAt
            : endedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        durationSeconds: freezed == durationSeconds
            ? _self.durationSeconds
            : durationSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        totalVolumeKg: freezed == totalVolumeKg
            ? _self.totalVolumeKg
            : totalVolumeKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        perceivedEffort: freezed == perceivedEffort
            ? _self.perceivedEffort
            : perceivedEffort // ignore: cast_nullable_to_non_nullable
                  as int?,
        mood: freezed == mood
            ? _self.mood
            : mood // ignore: cast_nullable_to_non_nullable
                  as String?,
        notes: freezed == notes
            ? _self.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        sets: null == sets
            ? _self._sets
            : sets // ignore: cast_nullable_to_non_nullable
                  as List<SessionSet>,
        personalRecords: null == personalRecords
            ? _self._personalRecords
            : personalRecords // ignore: cast_nullable_to_non_nullable
                  as List<PersonalRecord>,
      ),
    );
  }
}

/// @nodoc
mixin _$TopSet {
  int? get reps;
  double? get weightKg;

  /// Create a copy of TopSet
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $TopSetCopyWith<TopSet> get copyWith =>
      _$TopSetCopyWithImpl<TopSet>(this as TopSet, _$identity);

  /// Serializes this TopSet to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as TopSet;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is TopSet &&
            (identical(other.reps, _this.reps) || other.reps == _this.reps) &&
            (identical(other.weightKg, _this.weightKg) ||
                other.weightKg == _this.weightKg));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as TopSet;
    return Object.hash(runtimeType, _this.reps, _this.weightKg);
  }

  @override
  String toString() {
    final _this = this as TopSet;
    return 'TopSet(reps: ${_this.reps}, weightKg: ${_this.weightKg})';
  }
}

/// @nodoc
abstract mixin class $TopSetCopyWith<$Res> {
  factory $TopSetCopyWith(TopSet value, $Res Function(TopSet) _then) =
      _$TopSetCopyWithImpl;
  @useResult
  $Res call({int? reps, double? weightKg});
}

/// @nodoc
class _$TopSetCopyWithImpl<$Res> implements $TopSetCopyWith<$Res> {
  _$TopSetCopyWithImpl(this._self, this._then);

  final TopSet _self;
  final $Res Function(TopSet) _then;

  /// Create a copy of TopSet
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? reps = freezed, Object? weightKg = freezed}) {
    return _then(
      TopSet(
        reps: freezed == reps
            ? _self.reps
            : reps // ignore: cast_nullable_to_non_nullable
                  as int?,
        weightKg: freezed == weightKg
            ? _self.weightKg
            : weightKg // ignore: cast_nullable_to_non_nullable
                  as double?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [TopSet].
extension TopSetPatterns on TopSet {
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
    TResult Function(_TopSet value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _TopSet() when $default != null:
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
    TResult Function(_TopSet value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TopSet():
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
    TResult? Function(_TopSet value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TopSet() when $default != null:
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
    TResult Function(int? reps, double? weightKg)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _TopSet() when $default != null:
        return $default(_that.reps, _that.weightKg);
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
    TResult Function(int? reps, double? weightKg) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TopSet():
        return $default(_that.reps, _that.weightKg);
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
    TResult? Function(int? reps, double? weightKg)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _TopSet() when $default != null:
        return $default(_that.reps, _that.weightKg);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDoubleN()
class _TopSet implements TopSet {
  const _TopSet({this.reps, this.weightKg});
  factory _TopSet.fromJson(Map<String, dynamic> json) => _$TopSetFromJson(json);

  @override
  final int? reps;
  @override
  final double? weightKg;

  /// Create a copy of TopSet
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$TopSetCopyWith<_TopSet> get copyWith =>
      __$TopSetCopyWithImpl<_TopSet>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$TopSetToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _TopSet &&
            (identical(other.reps, reps) || other.reps == reps) &&
            (identical(other.weightKg, weightKg) ||
                other.weightKg == weightKg));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, reps, weightKg);
  }

  @override
  String toString() {
    return 'TopSet(reps: $reps, weightKg: $weightKg)';
  }
}

/// @nodoc
abstract mixin class _$TopSetCopyWith<$Res> implements $TopSetCopyWith<$Res> {
  factory _$TopSetCopyWith(_TopSet value, $Res Function(_TopSet) _then) =
      __$TopSetCopyWithImpl;
  @override
  @useResult
  $Res call({int? reps, double? weightKg});
}

/// @nodoc
class __$TopSetCopyWithImpl<$Res> implements _$TopSetCopyWith<$Res> {
  __$TopSetCopyWithImpl(this._self, this._then);

  final _TopSet _self;
  final $Res Function(_TopSet) _then;

  /// Create a copy of TopSet
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? reps = freezed, Object? weightKg = freezed}) {
    return _then(
      _TopSet(
        reps: freezed == reps
            ? _self.reps
            : reps // ignore: cast_nullable_to_non_nullable
                  as int?,
        weightKg: freezed == weightKg
            ? _self.weightKg
            : weightKg // ignore: cast_nullable_to_non_nullable
                  as double?,
      ),
    );
  }
}

/// @nodoc
mixin _$ExerciseHistoryEntry {
  String get sessionId;
  String get date;
  TopSet? get topSet;
  double? get volumeKg;
  double? get e1rmKg;

  /// Create a copy of ExerciseHistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ExerciseHistoryEntryCopyWith<ExerciseHistoryEntry> get copyWith =>
      _$ExerciseHistoryEntryCopyWithImpl<ExerciseHistoryEntry>(
        this as ExerciseHistoryEntry,
        _$identity,
      );

  /// Serializes this ExerciseHistoryEntry to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as ExerciseHistoryEntry;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is ExerciseHistoryEntry &&
            (identical(other.sessionId, _this.sessionId) ||
                other.sessionId == _this.sessionId) &&
            (identical(other.date, _this.date) || other.date == _this.date) &&
            (identical(other.topSet, _this.topSet) ||
                other.topSet == _this.topSet) &&
            (identical(other.volumeKg, _this.volumeKg) ||
                other.volumeKg == _this.volumeKg) &&
            (identical(other.e1rmKg, _this.e1rmKg) ||
                other.e1rmKg == _this.e1rmKg));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as ExerciseHistoryEntry;
    return Object.hash(
      runtimeType,
      _this.sessionId,
      _this.date,
      _this.topSet,
      _this.volumeKg,
      _this.e1rmKg,
    );
  }

  @override
  String toString() {
    final _this = this as ExerciseHistoryEntry;
    return 'ExerciseHistoryEntry(sessionId: ${_this.sessionId}, date: ${_this.date}, topSet: ${_this.topSet}, volumeKg: ${_this.volumeKg}, e1rmKg: ${_this.e1rmKg})';
  }
}

/// @nodoc
abstract mixin class $ExerciseHistoryEntryCopyWith<$Res> {
  factory $ExerciseHistoryEntryCopyWith(
    ExerciseHistoryEntry value,
    $Res Function(ExerciseHistoryEntry) _then,
  ) = _$ExerciseHistoryEntryCopyWithImpl;
  @useResult
  $Res call({
    String sessionId,
    String date,
    TopSet? topSet,
    double? volumeKg,
    double? e1rmKg,
  });

  $TopSetCopyWith<$Res>? get topSet;
}

/// @nodoc
class _$ExerciseHistoryEntryCopyWithImpl<$Res>
    implements $ExerciseHistoryEntryCopyWith<$Res> {
  _$ExerciseHistoryEntryCopyWithImpl(this._self, this._then);

  final ExerciseHistoryEntry _self;
  final $Res Function(ExerciseHistoryEntry) _then;

  /// Create a copy of ExerciseHistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? sessionId = null,
    Object? date = null,
    Object? topSet = freezed,
    Object? volumeKg = freezed,
    Object? e1rmKg = freezed,
  }) {
    return _then(
      ExerciseHistoryEntry(
        sessionId: null == sessionId
            ? _self.sessionId
            : sessionId // ignore: cast_nullable_to_non_nullable
                  as String,
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        topSet: freezed == topSet
            ? _self.topSet
            : topSet // ignore: cast_nullable_to_non_nullable
                  as TopSet?,
        volumeKg: freezed == volumeKg
            ? _self.volumeKg
            : volumeKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        e1rmKg: freezed == e1rmKg
            ? _self.e1rmKg
            : e1rmKg // ignore: cast_nullable_to_non_nullable
                  as double?,
      ),
    );
  }

  /// Create a copy of ExerciseHistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $TopSetCopyWith<$Res>? get topSet {
    if (_self.topSet == null) {
      return null;
    }

    return $TopSetCopyWith<$Res>(_self.topSet!, (value) {
      return _then(_self.copyWith(topSet: value));
    });
  }
}

/// Adds pattern-matching-related methods to [ExerciseHistoryEntry].
extension ExerciseHistoryEntryPatterns on ExerciseHistoryEntry {
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
    TResult Function(_ExerciseHistoryEntry value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistoryEntry() when $default != null:
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
    TResult Function(_ExerciseHistoryEntry value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistoryEntry():
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
    TResult? Function(_ExerciseHistoryEntry value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistoryEntry() when $default != null:
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
      String sessionId,
      String date,
      TopSet? topSet,
      double? volumeKg,
      double? e1rmKg,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistoryEntry() when $default != null:
        return $default(
          _that.sessionId,
          _that.date,
          _that.topSet,
          _that.volumeKg,
          _that.e1rmKg,
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
      String sessionId,
      String date,
      TopSet? topSet,
      double? volumeKg,
      double? e1rmKg,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistoryEntry():
        return $default(
          _that.sessionId,
          _that.date,
          _that.topSet,
          _that.volumeKg,
          _that.e1rmKg,
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
      String sessionId,
      String date,
      TopSet? topSet,
      double? volumeKg,
      double? e1rmKg,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistoryEntry() when $default != null:
        return $default(
          _that.sessionId,
          _that.date,
          _that.topSet,
          _that.volumeKg,
          _that.e1rmKg,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDoubleN()
class _ExerciseHistoryEntry implements ExerciseHistoryEntry {
  const _ExerciseHistoryEntry({
    required this.sessionId,
    required this.date,
    this.topSet,
    this.volumeKg,
    this.e1rmKg,
  });
  factory _ExerciseHistoryEntry.fromJson(Map<String, dynamic> json) =>
      _$ExerciseHistoryEntryFromJson(json);

  @override
  final String sessionId;
  @override
  final String date;
  @override
  final TopSet? topSet;
  @override
  final double? volumeKg;
  @override
  final double? e1rmKg;

  /// Create a copy of ExerciseHistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ExerciseHistoryEntryCopyWith<_ExerciseHistoryEntry> get copyWith =>
      __$ExerciseHistoryEntryCopyWithImpl<_ExerciseHistoryEntry>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$ExerciseHistoryEntryToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _ExerciseHistoryEntry &&
            (identical(other.sessionId, sessionId) ||
                other.sessionId == sessionId) &&
            (identical(other.date, date) || other.date == date) &&
            (identical(other.topSet, topSet) || other.topSet == topSet) &&
            (identical(other.volumeKg, volumeKg) ||
                other.volumeKg == volumeKg) &&
            (identical(other.e1rmKg, e1rmKg) || other.e1rmKg == e1rmKg));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, sessionId, date, topSet, volumeKg, e1rmKg);
  }

  @override
  String toString() {
    return 'ExerciseHistoryEntry(sessionId: $sessionId, date: $date, topSet: $topSet, volumeKg: $volumeKg, e1rmKg: $e1rmKg)';
  }
}

/// @nodoc
abstract mixin class _$ExerciseHistoryEntryCopyWith<$Res>
    implements $ExerciseHistoryEntryCopyWith<$Res> {
  factory _$ExerciseHistoryEntryCopyWith(
    _ExerciseHistoryEntry value,
    $Res Function(_ExerciseHistoryEntry) _then,
  ) = __$ExerciseHistoryEntryCopyWithImpl;
  @override
  @useResult
  $Res call({
    String sessionId,
    String date,
    TopSet? topSet,
    double? volumeKg,
    double? e1rmKg,
  });

  @override
  $TopSetCopyWith<$Res>? get topSet;
}

/// @nodoc
class __$ExerciseHistoryEntryCopyWithImpl<$Res>
    implements _$ExerciseHistoryEntryCopyWith<$Res> {
  __$ExerciseHistoryEntryCopyWithImpl(this._self, this._then);

  final _ExerciseHistoryEntry _self;
  final $Res Function(_ExerciseHistoryEntry) _then;

  /// Create a copy of ExerciseHistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? sessionId = null,
    Object? date = null,
    Object? topSet = freezed,
    Object? volumeKg = freezed,
    Object? e1rmKg = freezed,
  }) {
    return _then(
      _ExerciseHistoryEntry(
        sessionId: null == sessionId
            ? _self.sessionId
            : sessionId // ignore: cast_nullable_to_non_nullable
                  as String,
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        topSet: freezed == topSet
            ? _self.topSet
            : topSet // ignore: cast_nullable_to_non_nullable
                  as TopSet?,
        volumeKg: freezed == volumeKg
            ? _self.volumeKg
            : volumeKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        e1rmKg: freezed == e1rmKg
            ? _self.e1rmKg
            : e1rmKg // ignore: cast_nullable_to_non_nullable
                  as double?,
      ),
    );
  }

  /// Create a copy of ExerciseHistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $TopSetCopyWith<$Res>? get topSet {
    if (_self.topSet == null) {
      return null;
    }

    return $TopSetCopyWith<$Res>(_self.topSet!, (value) {
      return _then(_self.copyWith(topSet: value));
    });
  }
}

/// @nodoc
mixin _$ExerciseHistory {
  double? get bestE1rmKg;
  double? get bestWeightKg;
  List<ExerciseHistoryEntry> get sessions;

  /// Create a copy of ExerciseHistory
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ExerciseHistoryCopyWith<ExerciseHistory> get copyWith =>
      _$ExerciseHistoryCopyWithImpl<ExerciseHistory>(
        this as ExerciseHistory,
        _$identity,
      );

  /// Serializes this ExerciseHistory to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as ExerciseHistory;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is ExerciseHistory &&
            (identical(other.bestE1rmKg, _this.bestE1rmKg) ||
                other.bestE1rmKg == _this.bestE1rmKg) &&
            (identical(other.bestWeightKg, _this.bestWeightKg) ||
                other.bestWeightKg == _this.bestWeightKg) &&
            const DeepCollectionEquality().equals(
              other.sessions,
              _this.sessions,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as ExerciseHistory;
    return Object.hash(
      runtimeType,
      _this.bestE1rmKg,
      _this.bestWeightKg,
      const DeepCollectionEquality().hash(_this.sessions),
    );
  }

  @override
  String toString() {
    final _this = this as ExerciseHistory;
    return 'ExerciseHistory(bestE1rmKg: ${_this.bestE1rmKg}, bestWeightKg: ${_this.bestWeightKg}, sessions: ${_this.sessions})';
  }
}

/// @nodoc
abstract mixin class $ExerciseHistoryCopyWith<$Res> {
  factory $ExerciseHistoryCopyWith(
    ExerciseHistory value,
    $Res Function(ExerciseHistory) _then,
  ) = _$ExerciseHistoryCopyWithImpl;
  @useResult
  $Res call({
    double? bestE1rmKg,
    double? bestWeightKg,
    List<ExerciseHistoryEntry> sessions,
  });
}

/// @nodoc
class _$ExerciseHistoryCopyWithImpl<$Res>
    implements $ExerciseHistoryCopyWith<$Res> {
  _$ExerciseHistoryCopyWithImpl(this._self, this._then);

  final ExerciseHistory _self;
  final $Res Function(ExerciseHistory) _then;

  /// Create a copy of ExerciseHistory
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? bestE1rmKg = freezed,
    Object? bestWeightKg = freezed,
    Object? sessions = null,
  }) {
    return _then(
      ExerciseHistory(
        bestE1rmKg: freezed == bestE1rmKg
            ? _self.bestE1rmKg
            : bestE1rmKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        bestWeightKg: freezed == bestWeightKg
            ? _self.bestWeightKg
            : bestWeightKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        sessions: null == sessions
            ? _self.sessions
            : sessions // ignore: cast_nullable_to_non_nullable
                  as List<ExerciseHistoryEntry>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [ExerciseHistory].
extension ExerciseHistoryPatterns on ExerciseHistory {
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
    TResult Function(_ExerciseHistory value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistory() when $default != null:
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
    TResult Function(_ExerciseHistory value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistory():
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
    TResult? Function(_ExerciseHistory value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistory() when $default != null:
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
      double? bestE1rmKg,
      double? bestWeightKg,
      List<ExerciseHistoryEntry> sessions,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistory() when $default != null:
        return $default(_that.bestE1rmKg, _that.bestWeightKg, _that.sessions);
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
      double? bestE1rmKg,
      double? bestWeightKg,
      List<ExerciseHistoryEntry> sessions,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistory():
        return $default(_that.bestE1rmKg, _that.bestWeightKg, _that.sessions);
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
      double? bestE1rmKg,
      double? bestWeightKg,
      List<ExerciseHistoryEntry> sessions,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseHistory() when $default != null:
        return $default(_that.bestE1rmKg, _that.bestWeightKg, _that.sessions);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDoubleN()
class _ExerciseHistory implements ExerciseHistory {
  const _ExerciseHistory({
    this.bestE1rmKg,
    this.bestWeightKg,
    List<ExerciseHistoryEntry> sessions = const <ExerciseHistoryEntry>[],
  }) : _sessions = sessions;
  factory _ExerciseHistory.fromJson(Map<String, dynamic> json) =>
      _$ExerciseHistoryFromJson(json);

  @override
  final double? bestE1rmKg;
  @override
  final double? bestWeightKg;
  final List<ExerciseHistoryEntry> _sessions;
  @override
  @JsonKey()
  List<ExerciseHistoryEntry> get sessions {
    if (_sessions is EqualUnmodifiableListView) return _sessions;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_sessions);
  }

  /// Create a copy of ExerciseHistory
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ExerciseHistoryCopyWith<_ExerciseHistory> get copyWith =>
      __$ExerciseHistoryCopyWithImpl<_ExerciseHistory>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ExerciseHistoryToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _ExerciseHistory &&
            (identical(other.bestE1rmKg, bestE1rmKg) ||
                other.bestE1rmKg == bestE1rmKg) &&
            (identical(other.bestWeightKg, bestWeightKg) ||
                other.bestWeightKg == bestWeightKg) &&
            const DeepCollectionEquality().equals(other.sessions, _sessions));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      bestE1rmKg,
      bestWeightKg,
      const DeepCollectionEquality().hash(_sessions),
    );
  }

  @override
  String toString() {
    return 'ExerciseHistory(bestE1rmKg: $bestE1rmKg, bestWeightKg: $bestWeightKg, sessions: $sessions)';
  }
}

/// @nodoc
abstract mixin class _$ExerciseHistoryCopyWith<$Res>
    implements $ExerciseHistoryCopyWith<$Res> {
  factory _$ExerciseHistoryCopyWith(
    _ExerciseHistory value,
    $Res Function(_ExerciseHistory) _then,
  ) = __$ExerciseHistoryCopyWithImpl;
  @override
  @useResult
  $Res call({
    double? bestE1rmKg,
    double? bestWeightKg,
    List<ExerciseHistoryEntry> sessions,
  });
}

/// @nodoc
class __$ExerciseHistoryCopyWithImpl<$Res>
    implements _$ExerciseHistoryCopyWith<$Res> {
  __$ExerciseHistoryCopyWithImpl(this._self, this._then);

  final _ExerciseHistory _self;
  final $Res Function(_ExerciseHistory) _then;

  /// Create a copy of ExerciseHistory
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? bestE1rmKg = freezed,
    Object? bestWeightKg = freezed,
    Object? sessions = null,
  }) {
    return _then(
      _ExerciseHistory(
        bestE1rmKg: freezed == bestE1rmKg
            ? _self.bestE1rmKg
            : bestE1rmKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        bestWeightKg: freezed == bestWeightKg
            ? _self.bestWeightKg
            : bestWeightKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        sessions: null == sessions
            ? _self._sessions
            : sessions // ignore: cast_nullable_to_non_nullable
                  as List<ExerciseHistoryEntry>,
      ),
    );
  }
}
