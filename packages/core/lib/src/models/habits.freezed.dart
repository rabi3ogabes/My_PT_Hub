// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'habits.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$Habit {
  String get id;
  String get name;
  String? get icon;
  String get type;
  String? get unit;

  /// Create a copy of Habit
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $HabitCopyWith<Habit> get copyWith =>
      _$HabitCopyWithImpl<Habit>(this as Habit, _$identity);

  /// Serializes this Habit to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Habit;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Habit &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.icon, _this.icon) || other.icon == _this.icon) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            (identical(other.unit, _this.unit) || other.unit == _this.unit));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Habit;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.name,
      _this.icon,
      _this.type,
      _this.unit,
    );
  }

  @override
  String toString() {
    final _this = this as Habit;
    return 'Habit(id: ${_this.id}, name: ${_this.name}, icon: ${_this.icon}, type: ${_this.type}, unit: ${_this.unit})';
  }
}

/// @nodoc
abstract mixin class $HabitCopyWith<$Res> {
  factory $HabitCopyWith(Habit value, $Res Function(Habit) _then) =
      _$HabitCopyWithImpl;
  @useResult
  $Res call({String id, String name, String? icon, String type, String? unit});
}

/// @nodoc
class _$HabitCopyWithImpl<$Res> implements $HabitCopyWith<$Res> {
  _$HabitCopyWithImpl(this._self, this._then);

  final Habit _self;
  final $Res Function(Habit) _then;

  /// Create a copy of Habit
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? icon = freezed,
    Object? type = null,
    Object? unit = freezed,
  }) {
    return _then(
      Habit(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        icon: freezed == icon
            ? _self.icon
            : icon // ignore: cast_nullable_to_non_nullable
                  as String?,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        unit: freezed == unit
            ? _self.unit
            : unit // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Habit].
extension HabitPatterns on Habit {
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
    TResult Function(_Habit value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Habit() when $default != null:
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
    TResult Function(_Habit value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Habit():
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
    TResult? Function(_Habit value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Habit() when $default != null:
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
      String name,
      String? icon,
      String type,
      String? unit,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Habit() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.icon,
          _that.type,
          _that.unit,
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
      String name,
      String? icon,
      String type,
      String? unit,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Habit():
        return $default(
          _that.id,
          _that.name,
          _that.icon,
          _that.type,
          _that.unit,
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
      String name,
      String? icon,
      String type,
      String? unit,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Habit() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.icon,
          _that.type,
          _that.unit,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Habit implements Habit {
  const _Habit({
    required this.id,
    required this.name,
    this.icon,
    this.type = 'boolean',
    this.unit,
  });
  factory _Habit.fromJson(Map<String, dynamic> json) => _$HabitFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String? icon;
  @override
  @JsonKey()
  final String type;
  @override
  final String? unit;

  /// Create a copy of Habit
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$HabitCopyWith<_Habit> get copyWith =>
      __$HabitCopyWithImpl<_Habit>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$HabitToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Habit &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.icon, icon) || other.icon == icon) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.unit, unit) || other.unit == unit));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, name, icon, type, unit);
  }

  @override
  String toString() {
    return 'Habit(id: $id, name: $name, icon: $icon, type: $type, unit: $unit)';
  }
}

/// @nodoc
abstract mixin class _$HabitCopyWith<$Res> implements $HabitCopyWith<$Res> {
  factory _$HabitCopyWith(_Habit value, $Res Function(_Habit) _then) =
      __$HabitCopyWithImpl;
  @override
  @useResult
  $Res call({String id, String name, String? icon, String type, String? unit});
}

/// @nodoc
class __$HabitCopyWithImpl<$Res> implements _$HabitCopyWith<$Res> {
  __$HabitCopyWithImpl(this._self, this._then);

  final _Habit _self;
  final $Res Function(_Habit) _then;

  /// Create a copy of Habit
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? icon = freezed,
    Object? type = null,
    Object? unit = freezed,
  }) {
    return _then(
      _Habit(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        icon: freezed == icon
            ? _self.icon
            : icon // ignore: cast_nullable_to_non_nullable
                  as String?,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        unit: freezed == unit
            ? _self.unit
            : unit // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$HabitItem {
  String get assignmentId;
  Habit get habit;
  double? get target;
  bool get scheduledToday;
  double? get value;
  bool get completed;
  Streak get streak;
  @JsonKey(name: 'completion_pct_30d')
  double get completionPct30d;
  List<String> get reminderTimes;

  /// Create a copy of HabitItem
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $HabitItemCopyWith<HabitItem> get copyWith =>
      _$HabitItemCopyWithImpl<HabitItem>(this as HabitItem, _$identity);

  /// Serializes this HabitItem to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as HabitItem;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is HabitItem &&
            (identical(other.assignmentId, _this.assignmentId) ||
                other.assignmentId == _this.assignmentId) &&
            (identical(other.habit, _this.habit) ||
                other.habit == _this.habit) &&
            (identical(other.target, _this.target) ||
                other.target == _this.target) &&
            (identical(other.scheduledToday, _this.scheduledToday) ||
                other.scheduledToday == _this.scheduledToday) &&
            (identical(other.value, _this.value) ||
                other.value == _this.value) &&
            (identical(other.completed, _this.completed) ||
                other.completed == _this.completed) &&
            (identical(other.streak, _this.streak) ||
                other.streak == _this.streak) &&
            (identical(other.completionPct30d, _this.completionPct30d) ||
                other.completionPct30d == _this.completionPct30d) &&
            const DeepCollectionEquality().equals(
              other.reminderTimes,
              _this.reminderTimes,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as HabitItem;
    return Object.hash(
      runtimeType,
      _this.assignmentId,
      _this.habit,
      _this.target,
      _this.scheduledToday,
      _this.value,
      _this.completed,
      _this.streak,
      _this.completionPct30d,
      const DeepCollectionEquality().hash(_this.reminderTimes),
    );
  }

  @override
  String toString() {
    final _this = this as HabitItem;
    return 'HabitItem(assignmentId: ${_this.assignmentId}, habit: ${_this.habit}, target: ${_this.target}, scheduledToday: ${_this.scheduledToday}, value: ${_this.value}, completed: ${_this.completed}, streak: ${_this.streak}, completionPct30d: ${_this.completionPct30d}, reminderTimes: ${_this.reminderTimes})';
  }
}

/// @nodoc
abstract mixin class $HabitItemCopyWith<$Res> {
  factory $HabitItemCopyWith(HabitItem value, $Res Function(HabitItem) _then) =
      _$HabitItemCopyWithImpl;
  @useResult
  $Res call({
    String assignmentId,
    Habit habit,
    double? target,
    bool scheduledToday,
    double? value,
    bool completed,
    Streak streak,
    @JsonKey(name: 'completion_pct_30d') double completionPct30d,
    List<String> reminderTimes,
  });

  $HabitCopyWith<$Res> get habit;
  $StreakCopyWith<$Res> get streak;
}

/// @nodoc
class _$HabitItemCopyWithImpl<$Res> implements $HabitItemCopyWith<$Res> {
  _$HabitItemCopyWithImpl(this._self, this._then);

  final HabitItem _self;
  final $Res Function(HabitItem) _then;

  /// Create a copy of HabitItem
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? assignmentId = null,
    Object? habit = null,
    Object? target = freezed,
    Object? scheduledToday = null,
    Object? value = freezed,
    Object? completed = null,
    Object? streak = null,
    Object? completionPct30d = null,
    Object? reminderTimes = null,
  }) {
    return _then(
      HabitItem(
        assignmentId: null == assignmentId
            ? _self.assignmentId
            : assignmentId // ignore: cast_nullable_to_non_nullable
                  as String,
        habit: null == habit
            ? _self.habit
            : habit // ignore: cast_nullable_to_non_nullable
                  as Habit,
        target: freezed == target
            ? _self.target
            : target // ignore: cast_nullable_to_non_nullable
                  as double?,
        scheduledToday: null == scheduledToday
            ? _self.scheduledToday
            : scheduledToday // ignore: cast_nullable_to_non_nullable
                  as bool,
        value: freezed == value
            ? _self.value
            : value // ignore: cast_nullable_to_non_nullable
                  as double?,
        completed: null == completed
            ? _self.completed
            : completed // ignore: cast_nullable_to_non_nullable
                  as bool,
        streak: null == streak
            ? _self.streak
            : streak // ignore: cast_nullable_to_non_nullable
                  as Streak,
        completionPct30d: null == completionPct30d
            ? _self.completionPct30d
            : completionPct30d // ignore: cast_nullable_to_non_nullable
                  as double,
        reminderTimes: null == reminderTimes
            ? _self.reminderTimes
            : reminderTimes // ignore: cast_nullable_to_non_nullable
                  as List<String>,
      ),
    );
  }

  /// Create a copy of HabitItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $HabitCopyWith<$Res> get habit {
    return $HabitCopyWith<$Res>(_self.habit, (value) {
      return _then(_self.copyWith(habit: value));
    });
  }

  /// Create a copy of HabitItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $StreakCopyWith<$Res> get streak {
    return $StreakCopyWith<$Res>(_self.streak, (value) {
      return _then(_self.copyWith(streak: value));
    });
  }
}

/// Adds pattern-matching-related methods to [HabitItem].
extension HabitItemPatterns on HabitItem {
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
    TResult Function(_HabitItem value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _HabitItem() when $default != null:
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
    TResult Function(_HabitItem value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _HabitItem():
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
    TResult? Function(_HabitItem value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _HabitItem() when $default != null:
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
      String assignmentId,
      Habit habit,
      double? target,
      bool scheduledToday,
      double? value,
      bool completed,
      Streak streak,
      @JsonKey(name: 'completion_pct_30d') double completionPct30d,
      List<String> reminderTimes,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _HabitItem() when $default != null:
        return $default(
          _that.assignmentId,
          _that.habit,
          _that.target,
          _that.scheduledToday,
          _that.value,
          _that.completed,
          _that.streak,
          _that.completionPct30d,
          _that.reminderTimes,
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
      String assignmentId,
      Habit habit,
      double? target,
      bool scheduledToday,
      double? value,
      bool completed,
      Streak streak,
      @JsonKey(name: 'completion_pct_30d') double completionPct30d,
      List<String> reminderTimes,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _HabitItem():
        return $default(
          _that.assignmentId,
          _that.habit,
          _that.target,
          _that.scheduledToday,
          _that.value,
          _that.completed,
          _that.streak,
          _that.completionPct30d,
          _that.reminderTimes,
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
      String assignmentId,
      Habit habit,
      double? target,
      bool scheduledToday,
      double? value,
      bool completed,
      Streak streak,
      @JsonKey(name: 'completion_pct_30d') double completionPct30d,
      List<String> reminderTimes,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _HabitItem() when $default != null:
        return $default(
          _that.assignmentId,
          _that.habit,
          _that.target,
          _that.scheduledToday,
          _that.value,
          _that.completed,
          _that.streak,
          _that.completionPct30d,
          _that.reminderTimes,
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
class _HabitItem extends HabitItem {
  const _HabitItem({
    required this.assignmentId,
    required this.habit,
    this.target,
    this.scheduledToday = true,
    this.value,
    this.completed = false,
    this.streak = const Streak(),
    @JsonKey(name: 'completion_pct_30d') this.completionPct30d = 0,
    List<String> reminderTimes = const <String>[],
  }) : _reminderTimes = reminderTimes,
       super._();
  factory _HabitItem.fromJson(Map<String, dynamic> json) =>
      _$HabitItemFromJson(json);

  @override
  final String assignmentId;
  @override
  final Habit habit;
  @override
  final double? target;
  @override
  @JsonKey()
  final bool scheduledToday;
  @override
  final double? value;
  @override
  @JsonKey()
  final bool completed;
  @override
  @JsonKey()
  final Streak streak;
  @override
  @JsonKey(name: 'completion_pct_30d')
  final double completionPct30d;
  final List<String> _reminderTimes;
  @override
  @JsonKey()
  List<String> get reminderTimes {
    if (_reminderTimes is EqualUnmodifiableListView) return _reminderTimes;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_reminderTimes);
  }

  /// Create a copy of HabitItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$HabitItemCopyWith<_HabitItem> get copyWith =>
      __$HabitItemCopyWithImpl<_HabitItem>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$HabitItemToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _HabitItem &&
            (identical(other.assignmentId, assignmentId) ||
                other.assignmentId == assignmentId) &&
            (identical(other.habit, habit) || other.habit == habit) &&
            (identical(other.target, target) || other.target == target) &&
            (identical(other.scheduledToday, scheduledToday) ||
                other.scheduledToday == scheduledToday) &&
            (identical(other.value, value) || other.value == value) &&
            (identical(other.completed, completed) ||
                other.completed == completed) &&
            (identical(other.streak, streak) || other.streak == streak) &&
            (identical(other.completionPct30d, completionPct30d) ||
                other.completionPct30d == completionPct30d) &&
            const DeepCollectionEquality().equals(
              other.reminderTimes,
              _reminderTimes,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      assignmentId,
      habit,
      target,
      scheduledToday,
      value,
      completed,
      streak,
      completionPct30d,
      const DeepCollectionEquality().hash(_reminderTimes),
    );
  }

  @override
  String toString() {
    return 'HabitItem(assignmentId: $assignmentId, habit: $habit, target: $target, scheduledToday: $scheduledToday, value: $value, completed: $completed, streak: $streak, completionPct30d: $completionPct30d, reminderTimes: $reminderTimes)';
  }
}

/// @nodoc
abstract mixin class _$HabitItemCopyWith<$Res>
    implements $HabitItemCopyWith<$Res> {
  factory _$HabitItemCopyWith(
    _HabitItem value,
    $Res Function(_HabitItem) _then,
  ) = __$HabitItemCopyWithImpl;
  @override
  @useResult
  $Res call({
    String assignmentId,
    Habit habit,
    double? target,
    bool scheduledToday,
    double? value,
    bool completed,
    Streak streak,
    @JsonKey(name: 'completion_pct_30d') double completionPct30d,
    List<String> reminderTimes,
  });

  @override
  $HabitCopyWith<$Res> get habit;
  @override
  $StreakCopyWith<$Res> get streak;
}

/// @nodoc
class __$HabitItemCopyWithImpl<$Res> implements _$HabitItemCopyWith<$Res> {
  __$HabitItemCopyWithImpl(this._self, this._then);

  final _HabitItem _self;
  final $Res Function(_HabitItem) _then;

  /// Create a copy of HabitItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? assignmentId = null,
    Object? habit = null,
    Object? target = freezed,
    Object? scheduledToday = null,
    Object? value = freezed,
    Object? completed = null,
    Object? streak = null,
    Object? completionPct30d = null,
    Object? reminderTimes = null,
  }) {
    return _then(
      _HabitItem(
        assignmentId: null == assignmentId
            ? _self.assignmentId
            : assignmentId // ignore: cast_nullable_to_non_nullable
                  as String,
        habit: null == habit
            ? _self.habit
            : habit // ignore: cast_nullable_to_non_nullable
                  as Habit,
        target: freezed == target
            ? _self.target
            : target // ignore: cast_nullable_to_non_nullable
                  as double?,
        scheduledToday: null == scheduledToday
            ? _self.scheduledToday
            : scheduledToday // ignore: cast_nullable_to_non_nullable
                  as bool,
        value: freezed == value
            ? _self.value
            : value // ignore: cast_nullable_to_non_nullable
                  as double?,
        completed: null == completed
            ? _self.completed
            : completed // ignore: cast_nullable_to_non_nullable
                  as bool,
        streak: null == streak
            ? _self.streak
            : streak // ignore: cast_nullable_to_non_nullable
                  as Streak,
        completionPct30d: null == completionPct30d
            ? _self.completionPct30d
            : completionPct30d // ignore: cast_nullable_to_non_nullable
                  as double,
        reminderTimes: null == reminderTimes
            ? _self._reminderTimes
            : reminderTimes // ignore: cast_nullable_to_non_nullable
                  as List<String>,
      ),
    );
  }

  /// Create a copy of HabitItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $HabitCopyWith<$Res> get habit {
    return $HabitCopyWith<$Res>(_self.habit, (value) {
      return _then(_self.copyWith(habit: value));
    });
  }

  /// Create a copy of HabitItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $StreakCopyWith<$Res> get streak {
    return $StreakCopyWith<$Res>(_self.streak, (value) {
      return _then(_self.copyWith(streak: value));
    });
  }
}
