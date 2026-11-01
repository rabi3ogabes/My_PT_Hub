// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'nutrition.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$Macros {
  double get kcal;
  double get proteinG;
  double get carbsG;
  double get fatG;

  /// Create a copy of Macros
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $MacrosCopyWith<Macros> get copyWith =>
      _$MacrosCopyWithImpl<Macros>(this as Macros, _$identity);

  /// Serializes this Macros to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Macros;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Macros &&
            (identical(other.kcal, _this.kcal) || other.kcal == _this.kcal) &&
            (identical(other.proteinG, _this.proteinG) ||
                other.proteinG == _this.proteinG) &&
            (identical(other.carbsG, _this.carbsG) ||
                other.carbsG == _this.carbsG) &&
            (identical(other.fatG, _this.fatG) || other.fatG == _this.fatG));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Macros;
    return Object.hash(
      runtimeType,
      _this.kcal,
      _this.proteinG,
      _this.carbsG,
      _this.fatG,
    );
  }

  @override
  String toString() {
    final _this = this as Macros;
    return 'Macros(kcal: ${_this.kcal}, proteinG: ${_this.proteinG}, carbsG: ${_this.carbsG}, fatG: ${_this.fatG})';
  }
}

/// @nodoc
abstract mixin class $MacrosCopyWith<$Res> {
  factory $MacrosCopyWith(Macros value, $Res Function(Macros) _then) =
      _$MacrosCopyWithImpl;
  @useResult
  $Res call({double kcal, double proteinG, double carbsG, double fatG});
}

/// @nodoc
class _$MacrosCopyWithImpl<$Res> implements $MacrosCopyWith<$Res> {
  _$MacrosCopyWithImpl(this._self, this._then);

  final Macros _self;
  final $Res Function(Macros) _then;

  /// Create a copy of Macros
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? kcal = null,
    Object? proteinG = null,
    Object? carbsG = null,
    Object? fatG = null,
  }) {
    return _then(
      Macros(
        kcal: null == kcal
            ? _self.kcal
            : kcal // ignore: cast_nullable_to_non_nullable
                  as double,
        proteinG: null == proteinG
            ? _self.proteinG
            : proteinG // ignore: cast_nullable_to_non_nullable
                  as double,
        carbsG: null == carbsG
            ? _self.carbsG
            : carbsG // ignore: cast_nullable_to_non_nullable
                  as double,
        fatG: null == fatG
            ? _self.fatG
            : fatG // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Macros].
extension MacrosPatterns on Macros {
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
    TResult Function(_Macros value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Macros() when $default != null:
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
    TResult Function(_Macros value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Macros():
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
    TResult? Function(_Macros value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Macros() when $default != null:
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
    TResult Function(double kcal, double proteinG, double carbsG, double fatG)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Macros() when $default != null:
        return $default(_that.kcal, _that.proteinG, _that.carbsG, _that.fatG);
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
    TResult Function(double kcal, double proteinG, double carbsG, double fatG)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Macros():
        return $default(_that.kcal, _that.proteinG, _that.carbsG, _that.fatG);
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
    TResult? Function(double kcal, double proteinG, double carbsG, double fatG)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Macros() when $default != null:
        return $default(_that.kcal, _that.proteinG, _that.carbsG, _that.fatG);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _Macros extends Macros {
  const _Macros({
    this.kcal = 0,
    this.proteinG = 0,
    this.carbsG = 0,
    this.fatG = 0,
  }) : super._();
  factory _Macros.fromJson(Map<String, dynamic> json) => _$MacrosFromJson(json);

  @override
  @JsonKey()
  final double kcal;
  @override
  @JsonKey()
  final double proteinG;
  @override
  @JsonKey()
  final double carbsG;
  @override
  @JsonKey()
  final double fatG;

  /// Create a copy of Macros
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$MacrosCopyWith<_Macros> get copyWith =>
      __$MacrosCopyWithImpl<_Macros>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$MacrosToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Macros &&
            (identical(other.kcal, kcal) || other.kcal == kcal) &&
            (identical(other.proteinG, proteinG) ||
                other.proteinG == proteinG) &&
            (identical(other.carbsG, carbsG) || other.carbsG == carbsG) &&
            (identical(other.fatG, fatG) || other.fatG == fatG));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, kcal, proteinG, carbsG, fatG);
  }

  @override
  String toString() {
    return 'Macros(kcal: $kcal, proteinG: $proteinG, carbsG: $carbsG, fatG: $fatG)';
  }
}

/// @nodoc
abstract mixin class _$MacrosCopyWith<$Res> implements $MacrosCopyWith<$Res> {
  factory _$MacrosCopyWith(_Macros value, $Res Function(_Macros) _then) =
      __$MacrosCopyWithImpl;
  @override
  @useResult
  $Res call({double kcal, double proteinG, double carbsG, double fatG});
}

/// @nodoc
class __$MacrosCopyWithImpl<$Res> implements _$MacrosCopyWith<$Res> {
  __$MacrosCopyWithImpl(this._self, this._then);

  final _Macros _self;
  final $Res Function(_Macros) _then;

  /// Create a copy of Macros
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? kcal = null,
    Object? proteinG = null,
    Object? carbsG = null,
    Object? fatG = null,
  }) {
    return _then(
      _Macros(
        kcal: null == kcal
            ? _self.kcal
            : kcal // ignore: cast_nullable_to_non_nullable
                  as double,
        proteinG: null == proteinG
            ? _self.proteinG
            : proteinG // ignore: cast_nullable_to_non_nullable
                  as double,
        carbsG: null == carbsG
            ? _self.carbsG
            : carbsG // ignore: cast_nullable_to_non_nullable
                  as double,
        fatG: null == fatG
            ? _self.fatG
            : fatG // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }
}

/// @nodoc
mixin _$Nutrients {
  double get kcal;
  double get proteinG;
  double get carbsG;
  double get fatG;
  double? get fiberG;
  double? get sugarG;
  double? get sodiumMg;

  /// Create a copy of Nutrients
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $NutrientsCopyWith<Nutrients> get copyWith =>
      _$NutrientsCopyWithImpl<Nutrients>(this as Nutrients, _$identity);

  /// Serializes this Nutrients to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Nutrients;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Nutrients &&
            (identical(other.kcal, _this.kcal) || other.kcal == _this.kcal) &&
            (identical(other.proteinG, _this.proteinG) ||
                other.proteinG == _this.proteinG) &&
            (identical(other.carbsG, _this.carbsG) ||
                other.carbsG == _this.carbsG) &&
            (identical(other.fatG, _this.fatG) || other.fatG == _this.fatG) &&
            (identical(other.fiberG, _this.fiberG) ||
                other.fiberG == _this.fiberG) &&
            (identical(other.sugarG, _this.sugarG) ||
                other.sugarG == _this.sugarG) &&
            (identical(other.sodiumMg, _this.sodiumMg) ||
                other.sodiumMg == _this.sodiumMg));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Nutrients;
    return Object.hash(
      runtimeType,
      _this.kcal,
      _this.proteinG,
      _this.carbsG,
      _this.fatG,
      _this.fiberG,
      _this.sugarG,
      _this.sodiumMg,
    );
  }

  @override
  String toString() {
    final _this = this as Nutrients;
    return 'Nutrients(kcal: ${_this.kcal}, proteinG: ${_this.proteinG}, carbsG: ${_this.carbsG}, fatG: ${_this.fatG}, fiberG: ${_this.fiberG}, sugarG: ${_this.sugarG}, sodiumMg: ${_this.sodiumMg})';
  }
}

/// @nodoc
abstract mixin class $NutrientsCopyWith<$Res> {
  factory $NutrientsCopyWith(Nutrients value, $Res Function(Nutrients) _then) =
      _$NutrientsCopyWithImpl;
  @useResult
  $Res call({
    double kcal,
    double proteinG,
    double carbsG,
    double fatG,
    double? fiberG,
    double? sugarG,
    double? sodiumMg,
  });
}

/// @nodoc
class _$NutrientsCopyWithImpl<$Res> implements $NutrientsCopyWith<$Res> {
  _$NutrientsCopyWithImpl(this._self, this._then);

  final Nutrients _self;
  final $Res Function(Nutrients) _then;

  /// Create a copy of Nutrients
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? kcal = null,
    Object? proteinG = null,
    Object? carbsG = null,
    Object? fatG = null,
    Object? fiberG = freezed,
    Object? sugarG = freezed,
    Object? sodiumMg = freezed,
  }) {
    return _then(
      Nutrients(
        kcal: null == kcal
            ? _self.kcal
            : kcal // ignore: cast_nullable_to_non_nullable
                  as double,
        proteinG: null == proteinG
            ? _self.proteinG
            : proteinG // ignore: cast_nullable_to_non_nullable
                  as double,
        carbsG: null == carbsG
            ? _self.carbsG
            : carbsG // ignore: cast_nullable_to_non_nullable
                  as double,
        fatG: null == fatG
            ? _self.fatG
            : fatG // ignore: cast_nullable_to_non_nullable
                  as double,
        fiberG: freezed == fiberG
            ? _self.fiberG
            : fiberG // ignore: cast_nullable_to_non_nullable
                  as double?,
        sugarG: freezed == sugarG
            ? _self.sugarG
            : sugarG // ignore: cast_nullable_to_non_nullable
                  as double?,
        sodiumMg: freezed == sodiumMg
            ? _self.sodiumMg
            : sodiumMg // ignore: cast_nullable_to_non_nullable
                  as double?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Nutrients].
extension NutrientsPatterns on Nutrients {
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
    TResult Function(_Nutrients value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Nutrients() when $default != null:
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
    TResult Function(_Nutrients value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Nutrients():
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
    TResult? Function(_Nutrients value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Nutrients() when $default != null:
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
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
      double? fiberG,
      double? sugarG,
      double? sodiumMg,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Nutrients() when $default != null:
        return $default(
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
          _that.fiberG,
          _that.sugarG,
          _that.sodiumMg,
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
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
      double? fiberG,
      double? sugarG,
      double? sodiumMg,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Nutrients():
        return $default(
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
          _that.fiberG,
          _that.sugarG,
          _that.sodiumMg,
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
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
      double? fiberG,
      double? sugarG,
      double? sodiumMg,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Nutrients() when $default != null:
        return $default(
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
          _that.fiberG,
          _that.sugarG,
          _that.sodiumMg,
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
class _Nutrients extends Nutrients {
  const _Nutrients({
    this.kcal = 0,
    this.proteinG = 0,
    this.carbsG = 0,
    this.fatG = 0,
    this.fiberG,
    this.sugarG,
    this.sodiumMg,
  }) : super._();
  factory _Nutrients.fromJson(Map<String, dynamic> json) =>
      _$NutrientsFromJson(json);

  @override
  @JsonKey()
  final double kcal;
  @override
  @JsonKey()
  final double proteinG;
  @override
  @JsonKey()
  final double carbsG;
  @override
  @JsonKey()
  final double fatG;
  @override
  final double? fiberG;
  @override
  final double? sugarG;
  @override
  final double? sodiumMg;

  /// Create a copy of Nutrients
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$NutrientsCopyWith<_Nutrients> get copyWith =>
      __$NutrientsCopyWithImpl<_Nutrients>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$NutrientsToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Nutrients &&
            (identical(other.kcal, kcal) || other.kcal == kcal) &&
            (identical(other.proteinG, proteinG) ||
                other.proteinG == proteinG) &&
            (identical(other.carbsG, carbsG) || other.carbsG == carbsG) &&
            (identical(other.fatG, fatG) || other.fatG == fatG) &&
            (identical(other.fiberG, fiberG) || other.fiberG == fiberG) &&
            (identical(other.sugarG, sugarG) || other.sugarG == sugarG) &&
            (identical(other.sodiumMg, sodiumMg) ||
                other.sodiumMg == sodiumMg));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      kcal,
      proteinG,
      carbsG,
      fatG,
      fiberG,
      sugarG,
      sodiumMg,
    );
  }

  @override
  String toString() {
    return 'Nutrients(kcal: $kcal, proteinG: $proteinG, carbsG: $carbsG, fatG: $fatG, fiberG: $fiberG, sugarG: $sugarG, sodiumMg: $sodiumMg)';
  }
}

/// @nodoc
abstract mixin class _$NutrientsCopyWith<$Res>
    implements $NutrientsCopyWith<$Res> {
  factory _$NutrientsCopyWith(
    _Nutrients value,
    $Res Function(_Nutrients) _then,
  ) = __$NutrientsCopyWithImpl;
  @override
  @useResult
  $Res call({
    double kcal,
    double proteinG,
    double carbsG,
    double fatG,
    double? fiberG,
    double? sugarG,
    double? sodiumMg,
  });
}

/// @nodoc
class __$NutrientsCopyWithImpl<$Res> implements _$NutrientsCopyWith<$Res> {
  __$NutrientsCopyWithImpl(this._self, this._then);

  final _Nutrients _self;
  final $Res Function(_Nutrients) _then;

  /// Create a copy of Nutrients
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? kcal = null,
    Object? proteinG = null,
    Object? carbsG = null,
    Object? fatG = null,
    Object? fiberG = freezed,
    Object? sugarG = freezed,
    Object? sodiumMg = freezed,
  }) {
    return _then(
      _Nutrients(
        kcal: null == kcal
            ? _self.kcal
            : kcal // ignore: cast_nullable_to_non_nullable
                  as double,
        proteinG: null == proteinG
            ? _self.proteinG
            : proteinG // ignore: cast_nullable_to_non_nullable
                  as double,
        carbsG: null == carbsG
            ? _self.carbsG
            : carbsG // ignore: cast_nullable_to_non_nullable
                  as double,
        fatG: null == fatG
            ? _self.fatG
            : fatG // ignore: cast_nullable_to_non_nullable
                  as double,
        fiberG: freezed == fiberG
            ? _self.fiberG
            : fiberG // ignore: cast_nullable_to_non_nullable
                  as double?,
        sugarG: freezed == sugarG
            ? _self.sugarG
            : sugarG // ignore: cast_nullable_to_non_nullable
                  as double?,
        sodiumMg: freezed == sodiumMg
            ? _self.sodiumMg
            : sodiumMg // ignore: cast_nullable_to_non_nullable
                  as double?,
      ),
    );
  }
}

/// @nodoc
mixin _$FoodServing {
  String get unit;
  double get grams;
  String? get label;

  /// Create a copy of FoodServing
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $FoodServingCopyWith<FoodServing> get copyWith =>
      _$FoodServingCopyWithImpl<FoodServing>(this as FoodServing, _$identity);

  /// Serializes this FoodServing to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as FoodServing;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is FoodServing &&
            (identical(other.unit, _this.unit) || other.unit == _this.unit) &&
            (identical(other.grams, _this.grams) ||
                other.grams == _this.grams) &&
            (identical(other.label, _this.label) ||
                other.label == _this.label));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as FoodServing;
    return Object.hash(runtimeType, _this.unit, _this.grams, _this.label);
  }

  @override
  String toString() {
    final _this = this as FoodServing;
    return 'FoodServing(unit: ${_this.unit}, grams: ${_this.grams}, label: ${_this.label})';
  }
}

/// @nodoc
abstract mixin class $FoodServingCopyWith<$Res> {
  factory $FoodServingCopyWith(
    FoodServing value,
    $Res Function(FoodServing) _then,
  ) = _$FoodServingCopyWithImpl;
  @useResult
  $Res call({String unit, double grams, String? label});
}

/// @nodoc
class _$FoodServingCopyWithImpl<$Res> implements $FoodServingCopyWith<$Res> {
  _$FoodServingCopyWithImpl(this._self, this._then);

  final FoodServing _self;
  final $Res Function(FoodServing) _then;

  /// Create a copy of FoodServing
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? unit = null,
    Object? grams = null,
    Object? label = freezed,
  }) {
    return _then(
      FoodServing(
        unit: null == unit
            ? _self.unit
            : unit // ignore: cast_nullable_to_non_nullable
                  as String,
        grams: null == grams
            ? _self.grams
            : grams // ignore: cast_nullable_to_non_nullable
                  as double,
        label: freezed == label
            ? _self.label
            : label // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [FoodServing].
extension FoodServingPatterns on FoodServing {
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
    TResult Function(_FoodServing value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _FoodServing() when $default != null:
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
    TResult Function(_FoodServing value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _FoodServing():
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
    TResult? Function(_FoodServing value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _FoodServing() when $default != null:
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
    TResult Function(String unit, double grams, String? label)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _FoodServing() when $default != null:
        return $default(_that.unit, _that.grams, _that.label);
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
    TResult Function(String unit, double grams, String? label) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _FoodServing():
        return $default(_that.unit, _that.grams, _that.label);
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
    TResult? Function(String unit, double grams, String? label)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _FoodServing() when $default != null:
        return $default(_that.unit, _that.grams, _that.label);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _FoodServing implements FoodServing {
  const _FoodServing({required this.unit, required this.grams, this.label});
  factory _FoodServing.fromJson(Map<String, dynamic> json) =>
      _$FoodServingFromJson(json);

  @override
  final String unit;
  @override
  final double grams;
  @override
  final String? label;

  /// Create a copy of FoodServing
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$FoodServingCopyWith<_FoodServing> get copyWith =>
      __$FoodServingCopyWithImpl<_FoodServing>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$FoodServingToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _FoodServing &&
            (identical(other.unit, unit) || other.unit == unit) &&
            (identical(other.grams, grams) || other.grams == grams) &&
            (identical(other.label, label) || other.label == label));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, unit, grams, label);
  }

  @override
  String toString() {
    return 'FoodServing(unit: $unit, grams: $grams, label: $label)';
  }
}

/// @nodoc
abstract mixin class _$FoodServingCopyWith<$Res>
    implements $FoodServingCopyWith<$Res> {
  factory _$FoodServingCopyWith(
    _FoodServing value,
    $Res Function(_FoodServing) _then,
  ) = __$FoodServingCopyWithImpl;
  @override
  @useResult
  $Res call({String unit, double grams, String? label});
}

/// @nodoc
class __$FoodServingCopyWithImpl<$Res> implements _$FoodServingCopyWith<$Res> {
  __$FoodServingCopyWithImpl(this._self, this._then);

  final _FoodServing _self;
  final $Res Function(_FoodServing) _then;

  /// Create a copy of FoodServing
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? unit = null,
    Object? grams = null,
    Object? label = freezed,
  }) {
    return _then(
      _FoodServing(
        unit: null == unit
            ? _self.unit
            : unit // ignore: cast_nullable_to_non_nullable
                  as String,
        grams: null == grams
            ? _self.grams
            : grams // ignore: cast_nullable_to_non_nullable
                  as double,
        label: freezed == label
            ? _self.label
            : label // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$Food {
  String get id;
  String get name;
  String? get brand;
  String? get barcode;
  String? get source;
  String? get region;
  String? get category;
  @JsonKey(name: 'per_100g')
  Nutrients get per100g;
  List<FoodServing> get servings;
  List<String> get dietTags;
  List<String> get allergens;
  bool get isVerified;
  bool get isCustom;
  bool get isFavorite;

  /// Create a copy of Food
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $FoodCopyWith<Food> get copyWith =>
      _$FoodCopyWithImpl<Food>(this as Food, _$identity);

  /// Serializes this Food to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Food;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Food &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.brand, _this.brand) ||
                other.brand == _this.brand) &&
            (identical(other.barcode, _this.barcode) ||
                other.barcode == _this.barcode) &&
            (identical(other.source, _this.source) ||
                other.source == _this.source) &&
            (identical(other.region, _this.region) ||
                other.region == _this.region) &&
            (identical(other.category, _this.category) ||
                other.category == _this.category) &&
            (identical(other.per100g, _this.per100g) ||
                other.per100g == _this.per100g) &&
            const DeepCollectionEquality().equals(
              other.servings,
              _this.servings,
            ) &&
            const DeepCollectionEquality().equals(
              other.dietTags,
              _this.dietTags,
            ) &&
            const DeepCollectionEquality().equals(
              other.allergens,
              _this.allergens,
            ) &&
            (identical(other.isVerified, _this.isVerified) ||
                other.isVerified == _this.isVerified) &&
            (identical(other.isCustom, _this.isCustom) ||
                other.isCustom == _this.isCustom) &&
            (identical(other.isFavorite, _this.isFavorite) ||
                other.isFavorite == _this.isFavorite));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Food;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.name,
      _this.brand,
      _this.barcode,
      _this.source,
      _this.region,
      _this.category,
      _this.per100g,
      const DeepCollectionEquality().hash(_this.servings),
      const DeepCollectionEquality().hash(_this.dietTags),
      const DeepCollectionEquality().hash(_this.allergens),
      _this.isVerified,
      _this.isCustom,
      _this.isFavorite,
    );
  }

  @override
  String toString() {
    final _this = this as Food;
    return 'Food(id: ${_this.id}, name: ${_this.name}, brand: ${_this.brand}, barcode: ${_this.barcode}, source: ${_this.source}, region: ${_this.region}, category: ${_this.category}, per100g: ${_this.per100g}, servings: ${_this.servings}, dietTags: ${_this.dietTags}, allergens: ${_this.allergens}, isVerified: ${_this.isVerified}, isCustom: ${_this.isCustom}, isFavorite: ${_this.isFavorite})';
  }
}

/// @nodoc
abstract mixin class $FoodCopyWith<$Res> {
  factory $FoodCopyWith(Food value, $Res Function(Food) _then) =
      _$FoodCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String name,
    String? brand,
    String? barcode,
    String? source,
    String? region,
    String? category,
    @JsonKey(name: 'per_100g') Nutrients per100g,
    List<FoodServing> servings,
    List<String> dietTags,
    List<String> allergens,
    bool isVerified,
    bool isCustom,
    bool isFavorite,
  });

  $NutrientsCopyWith<$Res> get per100g;
}

/// @nodoc
class _$FoodCopyWithImpl<$Res> implements $FoodCopyWith<$Res> {
  _$FoodCopyWithImpl(this._self, this._then);

  final Food _self;
  final $Res Function(Food) _then;

  /// Create a copy of Food
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? brand = freezed,
    Object? barcode = freezed,
    Object? source = freezed,
    Object? region = freezed,
    Object? category = freezed,
    Object? per100g = null,
    Object? servings = null,
    Object? dietTags = null,
    Object? allergens = null,
    Object? isVerified = null,
    Object? isCustom = null,
    Object? isFavorite = null,
  }) {
    return _then(
      Food(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        brand: freezed == brand
            ? _self.brand
            : brand // ignore: cast_nullable_to_non_nullable
                  as String?,
        barcode: freezed == barcode
            ? _self.barcode
            : barcode // ignore: cast_nullable_to_non_nullable
                  as String?,
        source: freezed == source
            ? _self.source
            : source // ignore: cast_nullable_to_non_nullable
                  as String?,
        region: freezed == region
            ? _self.region
            : region // ignore: cast_nullable_to_non_nullable
                  as String?,
        category: freezed == category
            ? _self.category
            : category // ignore: cast_nullable_to_non_nullable
                  as String?,
        per100g: null == per100g
            ? _self.per100g
            : per100g // ignore: cast_nullable_to_non_nullable
                  as Nutrients,
        servings: null == servings
            ? _self.servings
            : servings // ignore: cast_nullable_to_non_nullable
                  as List<FoodServing>,
        dietTags: null == dietTags
            ? _self.dietTags
            : dietTags // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        allergens: null == allergens
            ? _self.allergens
            : allergens // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        isVerified: null == isVerified
            ? _self.isVerified
            : isVerified // ignore: cast_nullable_to_non_nullable
                  as bool,
        isCustom: null == isCustom
            ? _self.isCustom
            : isCustom // ignore: cast_nullable_to_non_nullable
                  as bool,
        isFavorite: null == isFavorite
            ? _self.isFavorite
            : isFavorite // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }

  /// Create a copy of Food
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $NutrientsCopyWith<$Res> get per100g {
    return $NutrientsCopyWith<$Res>(_self.per100g, (value) {
      return _then(_self.copyWith(per100g: value));
    });
  }
}

/// Adds pattern-matching-related methods to [Food].
extension FoodPatterns on Food {
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
    TResult Function(_Food value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Food() when $default != null:
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
  TResult map<TResult extends Object?>(TResult Function(_Food value) $default) {
    final _that = this;
    switch (_that) {
      case _Food():
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
    TResult? Function(_Food value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Food() when $default != null:
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
      String? brand,
      String? barcode,
      String? source,
      String? region,
      String? category,
      @JsonKey(name: 'per_100g') Nutrients per100g,
      List<FoodServing> servings,
      List<String> dietTags,
      List<String> allergens,
      bool isVerified,
      bool isCustom,
      bool isFavorite,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Food() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.brand,
          _that.barcode,
          _that.source,
          _that.region,
          _that.category,
          _that.per100g,
          _that.servings,
          _that.dietTags,
          _that.allergens,
          _that.isVerified,
          _that.isCustom,
          _that.isFavorite,
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
      String? brand,
      String? barcode,
      String? source,
      String? region,
      String? category,
      @JsonKey(name: 'per_100g') Nutrients per100g,
      List<FoodServing> servings,
      List<String> dietTags,
      List<String> allergens,
      bool isVerified,
      bool isCustom,
      bool isFavorite,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Food():
        return $default(
          _that.id,
          _that.name,
          _that.brand,
          _that.barcode,
          _that.source,
          _that.region,
          _that.category,
          _that.per100g,
          _that.servings,
          _that.dietTags,
          _that.allergens,
          _that.isVerified,
          _that.isCustom,
          _that.isFavorite,
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
      String? brand,
      String? barcode,
      String? source,
      String? region,
      String? category,
      @JsonKey(name: 'per_100g') Nutrients per100g,
      List<FoodServing> servings,
      List<String> dietTags,
      List<String> allergens,
      bool isVerified,
      bool isCustom,
      bool isFavorite,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Food() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.brand,
          _that.barcode,
          _that.source,
          _that.region,
          _that.category,
          _that.per100g,
          _that.servings,
          _that.dietTags,
          _that.allergens,
          _that.isVerified,
          _that.isCustom,
          _that.isFavorite,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Food extends Food {
  const _Food({
    required this.id,
    required this.name,
    this.brand,
    this.barcode,
    this.source,
    this.region,
    this.category,
    @JsonKey(name: 'per_100g') this.per100g = const Nutrients(),
    List<FoodServing> servings = const <FoodServing>[],
    List<String> dietTags = const <String>[],
    List<String> allergens = const <String>[],
    this.isVerified = false,
    this.isCustom = false,
    this.isFavorite = false,
  }) : _servings = servings,
       _dietTags = dietTags,
       _allergens = allergens,
       super._();
  factory _Food.fromJson(Map<String, dynamic> json) => _$FoodFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String? brand;
  @override
  final String? barcode;
  @override
  final String? source;
  @override
  final String? region;
  @override
  final String? category;
  @override
  @JsonKey(name: 'per_100g')
  final Nutrients per100g;
  final List<FoodServing> _servings;
  @override
  @JsonKey()
  List<FoodServing> get servings {
    if (_servings is EqualUnmodifiableListView) return _servings;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_servings);
  }

  final List<String> _dietTags;
  @override
  @JsonKey()
  List<String> get dietTags {
    if (_dietTags is EqualUnmodifiableListView) return _dietTags;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_dietTags);
  }

  final List<String> _allergens;
  @override
  @JsonKey()
  List<String> get allergens {
    if (_allergens is EqualUnmodifiableListView) return _allergens;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_allergens);
  }

  @override
  @JsonKey()
  final bool isVerified;
  @override
  @JsonKey()
  final bool isCustom;
  @override
  @JsonKey()
  final bool isFavorite;

  /// Create a copy of Food
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$FoodCopyWith<_Food> get copyWith =>
      __$FoodCopyWithImpl<_Food>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$FoodToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Food &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.brand, brand) || other.brand == brand) &&
            (identical(other.barcode, barcode) || other.barcode == barcode) &&
            (identical(other.source, source) || other.source == source) &&
            (identical(other.region, region) || other.region == region) &&
            (identical(other.category, category) ||
                other.category == category) &&
            (identical(other.per100g, per100g) || other.per100g == per100g) &&
            const DeepCollectionEquality().equals(other.servings, _servings) &&
            const DeepCollectionEquality().equals(other.dietTags, _dietTags) &&
            const DeepCollectionEquality().equals(
              other.allergens,
              _allergens,
            ) &&
            (identical(other.isVerified, isVerified) ||
                other.isVerified == isVerified) &&
            (identical(other.isCustom, isCustom) ||
                other.isCustom == isCustom) &&
            (identical(other.isFavorite, isFavorite) ||
                other.isFavorite == isFavorite));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      name,
      brand,
      barcode,
      source,
      region,
      category,
      per100g,
      const DeepCollectionEquality().hash(_servings),
      const DeepCollectionEquality().hash(_dietTags),
      const DeepCollectionEquality().hash(_allergens),
      isVerified,
      isCustom,
      isFavorite,
    );
  }

  @override
  String toString() {
    return 'Food(id: $id, name: $name, brand: $brand, barcode: $barcode, source: $source, region: $region, category: $category, per100g: $per100g, servings: $servings, dietTags: $dietTags, allergens: $allergens, isVerified: $isVerified, isCustom: $isCustom, isFavorite: $isFavorite)';
  }
}

/// @nodoc
abstract mixin class _$FoodCopyWith<$Res> implements $FoodCopyWith<$Res> {
  factory _$FoodCopyWith(_Food value, $Res Function(_Food) _then) =
      __$FoodCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String? brand,
    String? barcode,
    String? source,
    String? region,
    String? category,
    @JsonKey(name: 'per_100g') Nutrients per100g,
    List<FoodServing> servings,
    List<String> dietTags,
    List<String> allergens,
    bool isVerified,
    bool isCustom,
    bool isFavorite,
  });

  @override
  $NutrientsCopyWith<$Res> get per100g;
}

/// @nodoc
class __$FoodCopyWithImpl<$Res> implements _$FoodCopyWith<$Res> {
  __$FoodCopyWithImpl(this._self, this._then);

  final _Food _self;
  final $Res Function(_Food) _then;

  /// Create a copy of Food
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? brand = freezed,
    Object? barcode = freezed,
    Object? source = freezed,
    Object? region = freezed,
    Object? category = freezed,
    Object? per100g = null,
    Object? servings = null,
    Object? dietTags = null,
    Object? allergens = null,
    Object? isVerified = null,
    Object? isCustom = null,
    Object? isFavorite = null,
  }) {
    return _then(
      _Food(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        brand: freezed == brand
            ? _self.brand
            : brand // ignore: cast_nullable_to_non_nullable
                  as String?,
        barcode: freezed == barcode
            ? _self.barcode
            : barcode // ignore: cast_nullable_to_non_nullable
                  as String?,
        source: freezed == source
            ? _self.source
            : source // ignore: cast_nullable_to_non_nullable
                  as String?,
        region: freezed == region
            ? _self.region
            : region // ignore: cast_nullable_to_non_nullable
                  as String?,
        category: freezed == category
            ? _self.category
            : category // ignore: cast_nullable_to_non_nullable
                  as String?,
        per100g: null == per100g
            ? _self.per100g
            : per100g // ignore: cast_nullable_to_non_nullable
                  as Nutrients,
        servings: null == servings
            ? _self._servings
            : servings // ignore: cast_nullable_to_non_nullable
                  as List<FoodServing>,
        dietTags: null == dietTags
            ? _self._dietTags
            : dietTags // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        allergens: null == allergens
            ? _self._allergens
            : allergens // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        isVerified: null == isVerified
            ? _self.isVerified
            : isVerified // ignore: cast_nullable_to_non_nullable
                  as bool,
        isCustom: null == isCustom
            ? _self.isCustom
            : isCustom // ignore: cast_nullable_to_non_nullable
                  as bool,
        isFavorite: null == isFavorite
            ? _self.isFavorite
            : isFavorite // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }

  /// Create a copy of Food
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $NutrientsCopyWith<$Res> get per100g {
    return $NutrientsCopyWith<$Res>(_self.per100g, (value) {
      return _then(_self.copyWith(per100g: value));
    });
  }
}

/// @nodoc
mixin _$FoodLog {
  String get id;
  String get logDate;
  String get slot;
  String? get foodId;
  String? get recipeId;
  String get name;
  double get quantity;
  String get unit;
  double get kcal;
  double get proteinG;
  double get carbsG;
  double get fatG;
  String get source;
  DateTime? get loggedAt;

  /// Create a copy of FoodLog
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $FoodLogCopyWith<FoodLog> get copyWith =>
      _$FoodLogCopyWithImpl<FoodLog>(this as FoodLog, _$identity);

  /// Serializes this FoodLog to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as FoodLog;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is FoodLog &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.logDate, _this.logDate) ||
                other.logDate == _this.logDate) &&
            (identical(other.slot, _this.slot) || other.slot == _this.slot) &&
            (identical(other.foodId, _this.foodId) ||
                other.foodId == _this.foodId) &&
            (identical(other.recipeId, _this.recipeId) ||
                other.recipeId == _this.recipeId) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.quantity, _this.quantity) ||
                other.quantity == _this.quantity) &&
            (identical(other.unit, _this.unit) || other.unit == _this.unit) &&
            (identical(other.kcal, _this.kcal) || other.kcal == _this.kcal) &&
            (identical(other.proteinG, _this.proteinG) ||
                other.proteinG == _this.proteinG) &&
            (identical(other.carbsG, _this.carbsG) ||
                other.carbsG == _this.carbsG) &&
            (identical(other.fatG, _this.fatG) || other.fatG == _this.fatG) &&
            (identical(other.source, _this.source) ||
                other.source == _this.source) &&
            (identical(other.loggedAt, _this.loggedAt) ||
                other.loggedAt == _this.loggedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as FoodLog;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.logDate,
      _this.slot,
      _this.foodId,
      _this.recipeId,
      _this.name,
      _this.quantity,
      _this.unit,
      _this.kcal,
      _this.proteinG,
      _this.carbsG,
      _this.fatG,
      _this.source,
      _this.loggedAt,
    );
  }

  @override
  String toString() {
    final _this = this as FoodLog;
    return 'FoodLog(id: ${_this.id}, logDate: ${_this.logDate}, slot: ${_this.slot}, foodId: ${_this.foodId}, recipeId: ${_this.recipeId}, name: ${_this.name}, quantity: ${_this.quantity}, unit: ${_this.unit}, kcal: ${_this.kcal}, proteinG: ${_this.proteinG}, carbsG: ${_this.carbsG}, fatG: ${_this.fatG}, source: ${_this.source}, loggedAt: ${_this.loggedAt})';
  }
}

/// @nodoc
abstract mixin class $FoodLogCopyWith<$Res> {
  factory $FoodLogCopyWith(FoodLog value, $Res Function(FoodLog) _then) =
      _$FoodLogCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String logDate,
    String slot,
    String? foodId,
    String? recipeId,
    String name,
    double quantity,
    String unit,
    double kcal,
    double proteinG,
    double carbsG,
    double fatG,
    String source,
    DateTime? loggedAt,
  });
}

/// @nodoc
class _$FoodLogCopyWithImpl<$Res> implements $FoodLogCopyWith<$Res> {
  _$FoodLogCopyWithImpl(this._self, this._then);

  final FoodLog _self;
  final $Res Function(FoodLog) _then;

  /// Create a copy of FoodLog
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? logDate = null,
    Object? slot = null,
    Object? foodId = freezed,
    Object? recipeId = freezed,
    Object? name = null,
    Object? quantity = null,
    Object? unit = null,
    Object? kcal = null,
    Object? proteinG = null,
    Object? carbsG = null,
    Object? fatG = null,
    Object? source = null,
    Object? loggedAt = freezed,
  }) {
    return _then(
      FoodLog(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        logDate: null == logDate
            ? _self.logDate
            : logDate // ignore: cast_nullable_to_non_nullable
                  as String,
        slot: null == slot
            ? _self.slot
            : slot // ignore: cast_nullable_to_non_nullable
                  as String,
        foodId: freezed == foodId
            ? _self.foodId
            : foodId // ignore: cast_nullable_to_non_nullable
                  as String?,
        recipeId: freezed == recipeId
            ? _self.recipeId
            : recipeId // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        quantity: null == quantity
            ? _self.quantity
            : quantity // ignore: cast_nullable_to_non_nullable
                  as double,
        unit: null == unit
            ? _self.unit
            : unit // ignore: cast_nullable_to_non_nullable
                  as String,
        kcal: null == kcal
            ? _self.kcal
            : kcal // ignore: cast_nullable_to_non_nullable
                  as double,
        proteinG: null == proteinG
            ? _self.proteinG
            : proteinG // ignore: cast_nullable_to_non_nullable
                  as double,
        carbsG: null == carbsG
            ? _self.carbsG
            : carbsG // ignore: cast_nullable_to_non_nullable
                  as double,
        fatG: null == fatG
            ? _self.fatG
            : fatG // ignore: cast_nullable_to_non_nullable
                  as double,
        source: null == source
            ? _self.source
            : source // ignore: cast_nullable_to_non_nullable
                  as String,
        loggedAt: freezed == loggedAt
            ? _self.loggedAt
            : loggedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [FoodLog].
extension FoodLogPatterns on FoodLog {
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
    TResult Function(_FoodLog value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _FoodLog() when $default != null:
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
    TResult Function(_FoodLog value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _FoodLog():
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
    TResult? Function(_FoodLog value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _FoodLog() when $default != null:
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
      String logDate,
      String slot,
      String? foodId,
      String? recipeId,
      String name,
      double quantity,
      String unit,
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
      String source,
      DateTime? loggedAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _FoodLog() when $default != null:
        return $default(
          _that.id,
          _that.logDate,
          _that.slot,
          _that.foodId,
          _that.recipeId,
          _that.name,
          _that.quantity,
          _that.unit,
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
          _that.source,
          _that.loggedAt,
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
      String logDate,
      String slot,
      String? foodId,
      String? recipeId,
      String name,
      double quantity,
      String unit,
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
      String source,
      DateTime? loggedAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _FoodLog():
        return $default(
          _that.id,
          _that.logDate,
          _that.slot,
          _that.foodId,
          _that.recipeId,
          _that.name,
          _that.quantity,
          _that.unit,
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
          _that.source,
          _that.loggedAt,
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
      String logDate,
      String slot,
      String? foodId,
      String? recipeId,
      String name,
      double quantity,
      String unit,
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
      String source,
      DateTime? loggedAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _FoodLog() when $default != null:
        return $default(
          _that.id,
          _that.logDate,
          _that.slot,
          _that.foodId,
          _that.recipeId,
          _that.name,
          _that.quantity,
          _that.unit,
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
          _that.source,
          _that.loggedAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _FoodLog extends FoodLog {
  const _FoodLog({
    required this.id,
    required this.logDate,
    required this.slot,
    this.foodId,
    this.recipeId,
    required this.name,
    this.quantity = 0,
    this.unit = 'g',
    this.kcal = 0,
    this.proteinG = 0,
    this.carbsG = 0,
    this.fatG = 0,
    this.source = 'search',
    this.loggedAt,
  }) : super._();
  factory _FoodLog.fromJson(Map<String, dynamic> json) =>
      _$FoodLogFromJson(json);

  @override
  final String id;
  @override
  final String logDate;
  @override
  final String slot;
  @override
  final String? foodId;
  @override
  final String? recipeId;
  @override
  final String name;
  @override
  @JsonKey()
  final double quantity;
  @override
  @JsonKey()
  final String unit;
  @override
  @JsonKey()
  final double kcal;
  @override
  @JsonKey()
  final double proteinG;
  @override
  @JsonKey()
  final double carbsG;
  @override
  @JsonKey()
  final double fatG;
  @override
  @JsonKey()
  final String source;
  @override
  final DateTime? loggedAt;

  /// Create a copy of FoodLog
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$FoodLogCopyWith<_FoodLog> get copyWith =>
      __$FoodLogCopyWithImpl<_FoodLog>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$FoodLogToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _FoodLog &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.logDate, logDate) || other.logDate == logDate) &&
            (identical(other.slot, slot) || other.slot == slot) &&
            (identical(other.foodId, foodId) || other.foodId == foodId) &&
            (identical(other.recipeId, recipeId) ||
                other.recipeId == recipeId) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.quantity, quantity) ||
                other.quantity == quantity) &&
            (identical(other.unit, unit) || other.unit == unit) &&
            (identical(other.kcal, kcal) || other.kcal == kcal) &&
            (identical(other.proteinG, proteinG) ||
                other.proteinG == proteinG) &&
            (identical(other.carbsG, carbsG) || other.carbsG == carbsG) &&
            (identical(other.fatG, fatG) || other.fatG == fatG) &&
            (identical(other.source, source) || other.source == source) &&
            (identical(other.loggedAt, loggedAt) ||
                other.loggedAt == loggedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      logDate,
      slot,
      foodId,
      recipeId,
      name,
      quantity,
      unit,
      kcal,
      proteinG,
      carbsG,
      fatG,
      source,
      loggedAt,
    );
  }

  @override
  String toString() {
    return 'FoodLog(id: $id, logDate: $logDate, slot: $slot, foodId: $foodId, recipeId: $recipeId, name: $name, quantity: $quantity, unit: $unit, kcal: $kcal, proteinG: $proteinG, carbsG: $carbsG, fatG: $fatG, source: $source, loggedAt: $loggedAt)';
  }
}

/// @nodoc
abstract mixin class _$FoodLogCopyWith<$Res> implements $FoodLogCopyWith<$Res> {
  factory _$FoodLogCopyWith(_FoodLog value, $Res Function(_FoodLog) _then) =
      __$FoodLogCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String logDate,
    String slot,
    String? foodId,
    String? recipeId,
    String name,
    double quantity,
    String unit,
    double kcal,
    double proteinG,
    double carbsG,
    double fatG,
    String source,
    DateTime? loggedAt,
  });
}

/// @nodoc
class __$FoodLogCopyWithImpl<$Res> implements _$FoodLogCopyWith<$Res> {
  __$FoodLogCopyWithImpl(this._self, this._then);

  final _FoodLog _self;
  final $Res Function(_FoodLog) _then;

  /// Create a copy of FoodLog
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? logDate = null,
    Object? slot = null,
    Object? foodId = freezed,
    Object? recipeId = freezed,
    Object? name = null,
    Object? quantity = null,
    Object? unit = null,
    Object? kcal = null,
    Object? proteinG = null,
    Object? carbsG = null,
    Object? fatG = null,
    Object? source = null,
    Object? loggedAt = freezed,
  }) {
    return _then(
      _FoodLog(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        logDate: null == logDate
            ? _self.logDate
            : logDate // ignore: cast_nullable_to_non_nullable
                  as String,
        slot: null == slot
            ? _self.slot
            : slot // ignore: cast_nullable_to_non_nullable
                  as String,
        foodId: freezed == foodId
            ? _self.foodId
            : foodId // ignore: cast_nullable_to_non_nullable
                  as String?,
        recipeId: freezed == recipeId
            ? _self.recipeId
            : recipeId // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        quantity: null == quantity
            ? _self.quantity
            : quantity // ignore: cast_nullable_to_non_nullable
                  as double,
        unit: null == unit
            ? _self.unit
            : unit // ignore: cast_nullable_to_non_nullable
                  as String,
        kcal: null == kcal
            ? _self.kcal
            : kcal // ignore: cast_nullable_to_non_nullable
                  as double,
        proteinG: null == proteinG
            ? _self.proteinG
            : proteinG // ignore: cast_nullable_to_non_nullable
                  as double,
        carbsG: null == carbsG
            ? _self.carbsG
            : carbsG // ignore: cast_nullable_to_non_nullable
                  as double,
        fatG: null == fatG
            ? _self.fatG
            : fatG // ignore: cast_nullable_to_non_nullable
                  as double,
        source: null == source
            ? _self.source
            : source // ignore: cast_nullable_to_non_nullable
                  as String,
        loggedAt: freezed == loggedAt
            ? _self.loggedAt
            : loggedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
mixin _$PlannedItem {
  String? get mealItemId;
  String? get foodId;
  String? get recipeId;
  String get name;
  double get quantity;
  String get unit;
  double get kcal;
  double get proteinG;
  double get carbsG;
  double get fatG;
  bool get logged;

  /// Create a copy of PlannedItem
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $PlannedItemCopyWith<PlannedItem> get copyWith =>
      _$PlannedItemCopyWithImpl<PlannedItem>(this as PlannedItem, _$identity);

  /// Serializes this PlannedItem to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as PlannedItem;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is PlannedItem &&
            (identical(other.mealItemId, _this.mealItemId) ||
                other.mealItemId == _this.mealItemId) &&
            (identical(other.foodId, _this.foodId) ||
                other.foodId == _this.foodId) &&
            (identical(other.recipeId, _this.recipeId) ||
                other.recipeId == _this.recipeId) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.quantity, _this.quantity) ||
                other.quantity == _this.quantity) &&
            (identical(other.unit, _this.unit) || other.unit == _this.unit) &&
            (identical(other.kcal, _this.kcal) || other.kcal == _this.kcal) &&
            (identical(other.proteinG, _this.proteinG) ||
                other.proteinG == _this.proteinG) &&
            (identical(other.carbsG, _this.carbsG) ||
                other.carbsG == _this.carbsG) &&
            (identical(other.fatG, _this.fatG) || other.fatG == _this.fatG) &&
            (identical(other.logged, _this.logged) ||
                other.logged == _this.logged));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as PlannedItem;
    return Object.hash(
      runtimeType,
      _this.mealItemId,
      _this.foodId,
      _this.recipeId,
      _this.name,
      _this.quantity,
      _this.unit,
      _this.kcal,
      _this.proteinG,
      _this.carbsG,
      _this.fatG,
      _this.logged,
    );
  }

  @override
  String toString() {
    final _this = this as PlannedItem;
    return 'PlannedItem(mealItemId: ${_this.mealItemId}, foodId: ${_this.foodId}, recipeId: ${_this.recipeId}, name: ${_this.name}, quantity: ${_this.quantity}, unit: ${_this.unit}, kcal: ${_this.kcal}, proteinG: ${_this.proteinG}, carbsG: ${_this.carbsG}, fatG: ${_this.fatG}, logged: ${_this.logged})';
  }
}

/// @nodoc
abstract mixin class $PlannedItemCopyWith<$Res> {
  factory $PlannedItemCopyWith(
    PlannedItem value,
    $Res Function(PlannedItem) _then,
  ) = _$PlannedItemCopyWithImpl;
  @useResult
  $Res call({
    String? mealItemId,
    String? foodId,
    String? recipeId,
    String name,
    double quantity,
    String unit,
    double kcal,
    double proteinG,
    double carbsG,
    double fatG,
    bool logged,
  });
}

/// @nodoc
class _$PlannedItemCopyWithImpl<$Res> implements $PlannedItemCopyWith<$Res> {
  _$PlannedItemCopyWithImpl(this._self, this._then);

  final PlannedItem _self;
  final $Res Function(PlannedItem) _then;

  /// Create a copy of PlannedItem
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? mealItemId = freezed,
    Object? foodId = freezed,
    Object? recipeId = freezed,
    Object? name = null,
    Object? quantity = null,
    Object? unit = null,
    Object? kcal = null,
    Object? proteinG = null,
    Object? carbsG = null,
    Object? fatG = null,
    Object? logged = null,
  }) {
    return _then(
      PlannedItem(
        mealItemId: freezed == mealItemId
            ? _self.mealItemId
            : mealItemId // ignore: cast_nullable_to_non_nullable
                  as String?,
        foodId: freezed == foodId
            ? _self.foodId
            : foodId // ignore: cast_nullable_to_non_nullable
                  as String?,
        recipeId: freezed == recipeId
            ? _self.recipeId
            : recipeId // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        quantity: null == quantity
            ? _self.quantity
            : quantity // ignore: cast_nullable_to_non_nullable
                  as double,
        unit: null == unit
            ? _self.unit
            : unit // ignore: cast_nullable_to_non_nullable
                  as String,
        kcal: null == kcal
            ? _self.kcal
            : kcal // ignore: cast_nullable_to_non_nullable
                  as double,
        proteinG: null == proteinG
            ? _self.proteinG
            : proteinG // ignore: cast_nullable_to_non_nullable
                  as double,
        carbsG: null == carbsG
            ? _self.carbsG
            : carbsG // ignore: cast_nullable_to_non_nullable
                  as double,
        fatG: null == fatG
            ? _self.fatG
            : fatG // ignore: cast_nullable_to_non_nullable
                  as double,
        logged: null == logged
            ? _self.logged
            : logged // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [PlannedItem].
extension PlannedItemPatterns on PlannedItem {
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
    TResult Function(_PlannedItem value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PlannedItem() when $default != null:
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
    TResult Function(_PlannedItem value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlannedItem():
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
    TResult? Function(_PlannedItem value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlannedItem() when $default != null:
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
      String? mealItemId,
      String? foodId,
      String? recipeId,
      String name,
      double quantity,
      String unit,
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
      bool logged,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PlannedItem() when $default != null:
        return $default(
          _that.mealItemId,
          _that.foodId,
          _that.recipeId,
          _that.name,
          _that.quantity,
          _that.unit,
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
          _that.logged,
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
      String? mealItemId,
      String? foodId,
      String? recipeId,
      String name,
      double quantity,
      String unit,
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
      bool logged,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlannedItem():
        return $default(
          _that.mealItemId,
          _that.foodId,
          _that.recipeId,
          _that.name,
          _that.quantity,
          _that.unit,
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
          _that.logged,
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
      String? mealItemId,
      String? foodId,
      String? recipeId,
      String name,
      double quantity,
      String unit,
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
      bool logged,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlannedItem() when $default != null:
        return $default(
          _that.mealItemId,
          _that.foodId,
          _that.recipeId,
          _that.name,
          _that.quantity,
          _that.unit,
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
          _that.logged,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _PlannedItem extends PlannedItem {
  const _PlannedItem({
    this.mealItemId,
    this.foodId,
    this.recipeId,
    required this.name,
    this.quantity = 0,
    this.unit = 'g',
    this.kcal = 0,
    this.proteinG = 0,
    this.carbsG = 0,
    this.fatG = 0,
    this.logged = false,
  }) : super._();
  factory _PlannedItem.fromJson(Map<String, dynamic> json) =>
      _$PlannedItemFromJson(json);

  @override
  final String? mealItemId;
  @override
  final String? foodId;
  @override
  final String? recipeId;
  @override
  final String name;
  @override
  @JsonKey()
  final double quantity;
  @override
  @JsonKey()
  final String unit;
  @override
  @JsonKey()
  final double kcal;
  @override
  @JsonKey()
  final double proteinG;
  @override
  @JsonKey()
  final double carbsG;
  @override
  @JsonKey()
  final double fatG;
  @override
  @JsonKey()
  final bool logged;

  /// Create a copy of PlannedItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$PlannedItemCopyWith<_PlannedItem> get copyWith =>
      __$PlannedItemCopyWithImpl<_PlannedItem>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$PlannedItemToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _PlannedItem &&
            (identical(other.mealItemId, mealItemId) ||
                other.mealItemId == mealItemId) &&
            (identical(other.foodId, foodId) || other.foodId == foodId) &&
            (identical(other.recipeId, recipeId) ||
                other.recipeId == recipeId) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.quantity, quantity) ||
                other.quantity == quantity) &&
            (identical(other.unit, unit) || other.unit == unit) &&
            (identical(other.kcal, kcal) || other.kcal == kcal) &&
            (identical(other.proteinG, proteinG) ||
                other.proteinG == proteinG) &&
            (identical(other.carbsG, carbsG) || other.carbsG == carbsG) &&
            (identical(other.fatG, fatG) || other.fatG == fatG) &&
            (identical(other.logged, logged) || other.logged == logged));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      mealItemId,
      foodId,
      recipeId,
      name,
      quantity,
      unit,
      kcal,
      proteinG,
      carbsG,
      fatG,
      logged,
    );
  }

  @override
  String toString() {
    return 'PlannedItem(mealItemId: $mealItemId, foodId: $foodId, recipeId: $recipeId, name: $name, quantity: $quantity, unit: $unit, kcal: $kcal, proteinG: $proteinG, carbsG: $carbsG, fatG: $fatG, logged: $logged)';
  }
}

/// @nodoc
abstract mixin class _$PlannedItemCopyWith<$Res>
    implements $PlannedItemCopyWith<$Res> {
  factory _$PlannedItemCopyWith(
    _PlannedItem value,
    $Res Function(_PlannedItem) _then,
  ) = __$PlannedItemCopyWithImpl;
  @override
  @useResult
  $Res call({
    String? mealItemId,
    String? foodId,
    String? recipeId,
    String name,
    double quantity,
    String unit,
    double kcal,
    double proteinG,
    double carbsG,
    double fatG,
    bool logged,
  });
}

/// @nodoc
class __$PlannedItemCopyWithImpl<$Res> implements _$PlannedItemCopyWith<$Res> {
  __$PlannedItemCopyWithImpl(this._self, this._then);

  final _PlannedItem _self;
  final $Res Function(_PlannedItem) _then;

  /// Create a copy of PlannedItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? mealItemId = freezed,
    Object? foodId = freezed,
    Object? recipeId = freezed,
    Object? name = null,
    Object? quantity = null,
    Object? unit = null,
    Object? kcal = null,
    Object? proteinG = null,
    Object? carbsG = null,
    Object? fatG = null,
    Object? logged = null,
  }) {
    return _then(
      _PlannedItem(
        mealItemId: freezed == mealItemId
            ? _self.mealItemId
            : mealItemId // ignore: cast_nullable_to_non_nullable
                  as String?,
        foodId: freezed == foodId
            ? _self.foodId
            : foodId // ignore: cast_nullable_to_non_nullable
                  as String?,
        recipeId: freezed == recipeId
            ? _self.recipeId
            : recipeId // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        quantity: null == quantity
            ? _self.quantity
            : quantity // ignore: cast_nullable_to_non_nullable
                  as double,
        unit: null == unit
            ? _self.unit
            : unit // ignore: cast_nullable_to_non_nullable
                  as String,
        kcal: null == kcal
            ? _self.kcal
            : kcal // ignore: cast_nullable_to_non_nullable
                  as double,
        proteinG: null == proteinG
            ? _self.proteinG
            : proteinG // ignore: cast_nullable_to_non_nullable
                  as double,
        carbsG: null == carbsG
            ? _self.carbsG
            : carbsG // ignore: cast_nullable_to_non_nullable
                  as double,
        fatG: null == fatG
            ? _self.fatG
            : fatG // ignore: cast_nullable_to_non_nullable
                  as double,
        logged: null == logged
            ? _self.logged
            : logged // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// @nodoc
mixin _$MealSlot {
  String get slot;
  String get label;
  Macros get consumed;
  List<FoodLog> get logs;
  List<PlannedItem> get planned;

  /// Create a copy of MealSlot
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $MealSlotCopyWith<MealSlot> get copyWith =>
      _$MealSlotCopyWithImpl<MealSlot>(this as MealSlot, _$identity);

  /// Serializes this MealSlot to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as MealSlot;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is MealSlot &&
            (identical(other.slot, _this.slot) || other.slot == _this.slot) &&
            (identical(other.label, _this.label) ||
                other.label == _this.label) &&
            (identical(other.consumed, _this.consumed) ||
                other.consumed == _this.consumed) &&
            const DeepCollectionEquality().equals(other.logs, _this.logs) &&
            const DeepCollectionEquality().equals(
              other.planned,
              _this.planned,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as MealSlot;
    return Object.hash(
      runtimeType,
      _this.slot,
      _this.label,
      _this.consumed,
      const DeepCollectionEquality().hash(_this.logs),
      const DeepCollectionEquality().hash(_this.planned),
    );
  }

  @override
  String toString() {
    final _this = this as MealSlot;
    return 'MealSlot(slot: ${_this.slot}, label: ${_this.label}, consumed: ${_this.consumed}, logs: ${_this.logs}, planned: ${_this.planned})';
  }
}

/// @nodoc
abstract mixin class $MealSlotCopyWith<$Res> {
  factory $MealSlotCopyWith(MealSlot value, $Res Function(MealSlot) _then) =
      _$MealSlotCopyWithImpl;
  @useResult
  $Res call({
    String slot,
    String label,
    Macros consumed,
    List<FoodLog> logs,
    List<PlannedItem> planned,
  });

  $MacrosCopyWith<$Res> get consumed;
}

/// @nodoc
class _$MealSlotCopyWithImpl<$Res> implements $MealSlotCopyWith<$Res> {
  _$MealSlotCopyWithImpl(this._self, this._then);

  final MealSlot _self;
  final $Res Function(MealSlot) _then;

  /// Create a copy of MealSlot
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? slot = null,
    Object? label = null,
    Object? consumed = null,
    Object? logs = null,
    Object? planned = null,
  }) {
    return _then(
      MealSlot(
        slot: null == slot
            ? _self.slot
            : slot // ignore: cast_nullable_to_non_nullable
                  as String,
        label: null == label
            ? _self.label
            : label // ignore: cast_nullable_to_non_nullable
                  as String,
        consumed: null == consumed
            ? _self.consumed
            : consumed // ignore: cast_nullable_to_non_nullable
                  as Macros,
        logs: null == logs
            ? _self.logs
            : logs // ignore: cast_nullable_to_non_nullable
                  as List<FoodLog>,
        planned: null == planned
            ? _self.planned
            : planned // ignore: cast_nullable_to_non_nullable
                  as List<PlannedItem>,
      ),
    );
  }

  /// Create a copy of MealSlot
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MacrosCopyWith<$Res> get consumed {
    return $MacrosCopyWith<$Res>(_self.consumed, (value) {
      return _then(_self.copyWith(consumed: value));
    });
  }
}

/// Adds pattern-matching-related methods to [MealSlot].
extension MealSlotPatterns on MealSlot {
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
    TResult Function(_MealSlot value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MealSlot() when $default != null:
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
    TResult Function(_MealSlot value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MealSlot():
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
    TResult? Function(_MealSlot value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MealSlot() when $default != null:
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
      String slot,
      String label,
      Macros consumed,
      List<FoodLog> logs,
      List<PlannedItem> planned,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MealSlot() when $default != null:
        return $default(
          _that.slot,
          _that.label,
          _that.consumed,
          _that.logs,
          _that.planned,
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
      String slot,
      String label,
      Macros consumed,
      List<FoodLog> logs,
      List<PlannedItem> planned,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MealSlot():
        return $default(
          _that.slot,
          _that.label,
          _that.consumed,
          _that.logs,
          _that.planned,
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
      String slot,
      String label,
      Macros consumed,
      List<FoodLog> logs,
      List<PlannedItem> planned,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MealSlot() when $default != null:
        return $default(
          _that.slot,
          _that.label,
          _that.consumed,
          _that.logs,
          _that.planned,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _MealSlot extends MealSlot {
  const _MealSlot({
    required this.slot,
    required this.label,
    this.consumed = const Macros(),
    List<FoodLog> logs = const <FoodLog>[],
    List<PlannedItem> planned = const <PlannedItem>[],
  }) : _logs = logs,
       _planned = planned,
       super._();
  factory _MealSlot.fromJson(Map<String, dynamic> json) =>
      _$MealSlotFromJson(json);

  @override
  final String slot;
  @override
  final String label;
  @override
  @JsonKey()
  final Macros consumed;
  final List<FoodLog> _logs;
  @override
  @JsonKey()
  List<FoodLog> get logs {
    if (_logs is EqualUnmodifiableListView) return _logs;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_logs);
  }

  final List<PlannedItem> _planned;
  @override
  @JsonKey()
  List<PlannedItem> get planned {
    if (_planned is EqualUnmodifiableListView) return _planned;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_planned);
  }

  /// Create a copy of MealSlot
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$MealSlotCopyWith<_MealSlot> get copyWith =>
      __$MealSlotCopyWithImpl<_MealSlot>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$MealSlotToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _MealSlot &&
            (identical(other.slot, slot) || other.slot == slot) &&
            (identical(other.label, label) || other.label == label) &&
            (identical(other.consumed, consumed) ||
                other.consumed == consumed) &&
            const DeepCollectionEquality().equals(other.logs, _logs) &&
            const DeepCollectionEquality().equals(other.planned, _planned));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      slot,
      label,
      consumed,
      const DeepCollectionEquality().hash(_logs),
      const DeepCollectionEquality().hash(_planned),
    );
  }

  @override
  String toString() {
    return 'MealSlot(slot: $slot, label: $label, consumed: $consumed, logs: $logs, planned: $planned)';
  }
}

/// @nodoc
abstract mixin class _$MealSlotCopyWith<$Res>
    implements $MealSlotCopyWith<$Res> {
  factory _$MealSlotCopyWith(_MealSlot value, $Res Function(_MealSlot) _then) =
      __$MealSlotCopyWithImpl;
  @override
  @useResult
  $Res call({
    String slot,
    String label,
    Macros consumed,
    List<FoodLog> logs,
    List<PlannedItem> planned,
  });

  @override
  $MacrosCopyWith<$Res> get consumed;
}

/// @nodoc
class __$MealSlotCopyWithImpl<$Res> implements _$MealSlotCopyWith<$Res> {
  __$MealSlotCopyWithImpl(this._self, this._then);

  final _MealSlot _self;
  final $Res Function(_MealSlot) _then;

  /// Create a copy of MealSlot
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? slot = null,
    Object? label = null,
    Object? consumed = null,
    Object? logs = null,
    Object? planned = null,
  }) {
    return _then(
      _MealSlot(
        slot: null == slot
            ? _self.slot
            : slot // ignore: cast_nullable_to_non_nullable
                  as String,
        label: null == label
            ? _self.label
            : label // ignore: cast_nullable_to_non_nullable
                  as String,
        consumed: null == consumed
            ? _self.consumed
            : consumed // ignore: cast_nullable_to_non_nullable
                  as Macros,
        logs: null == logs
            ? _self._logs
            : logs // ignore: cast_nullable_to_non_nullable
                  as List<FoodLog>,
        planned: null == planned
            ? _self._planned
            : planned // ignore: cast_nullable_to_non_nullable
                  as List<PlannedItem>,
      ),
    );
  }

  /// Create a copy of MealSlot
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
mixin _$Fasting {
  String get mode;
  String? get windowStart;
  String? get windowEnd;

  /// Create a copy of Fasting
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $FastingCopyWith<Fasting> get copyWith =>
      _$FastingCopyWithImpl<Fasting>(this as Fasting, _$identity);

  /// Serializes this Fasting to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Fasting;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Fasting &&
            (identical(other.mode, _this.mode) || other.mode == _this.mode) &&
            (identical(other.windowStart, _this.windowStart) ||
                other.windowStart == _this.windowStart) &&
            (identical(other.windowEnd, _this.windowEnd) ||
                other.windowEnd == _this.windowEnd));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Fasting;
    return Object.hash(
      runtimeType,
      _this.mode,
      _this.windowStart,
      _this.windowEnd,
    );
  }

  @override
  String toString() {
    final _this = this as Fasting;
    return 'Fasting(mode: ${_this.mode}, windowStart: ${_this.windowStart}, windowEnd: ${_this.windowEnd})';
  }
}

/// @nodoc
abstract mixin class $FastingCopyWith<$Res> {
  factory $FastingCopyWith(Fasting value, $Res Function(Fasting) _then) =
      _$FastingCopyWithImpl;
  @useResult
  $Res call({String mode, String? windowStart, String? windowEnd});
}

/// @nodoc
class _$FastingCopyWithImpl<$Res> implements $FastingCopyWith<$Res> {
  _$FastingCopyWithImpl(this._self, this._then);

  final Fasting _self;
  final $Res Function(Fasting) _then;

  /// Create a copy of Fasting
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? mode = null,
    Object? windowStart = freezed,
    Object? windowEnd = freezed,
  }) {
    return _then(
      Fasting(
        mode: null == mode
            ? _self.mode
            : mode // ignore: cast_nullable_to_non_nullable
                  as String,
        windowStart: freezed == windowStart
            ? _self.windowStart
            : windowStart // ignore: cast_nullable_to_non_nullable
                  as String?,
        windowEnd: freezed == windowEnd
            ? _self.windowEnd
            : windowEnd // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Fasting].
extension FastingPatterns on Fasting {
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
    TResult Function(_Fasting value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Fasting() when $default != null:
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
    TResult Function(_Fasting value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Fasting():
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
    TResult? Function(_Fasting value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Fasting() when $default != null:
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
    TResult Function(String mode, String? windowStart, String? windowEnd)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Fasting() when $default != null:
        return $default(_that.mode, _that.windowStart, _that.windowEnd);
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
    TResult Function(String mode, String? windowStart, String? windowEnd)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Fasting():
        return $default(_that.mode, _that.windowStart, _that.windowEnd);
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
    TResult? Function(String mode, String? windowStart, String? windowEnd)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Fasting() when $default != null:
        return $default(_that.mode, _that.windowStart, _that.windowEnd);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Fasting implements Fasting {
  const _Fasting({this.mode = 'none', this.windowStart, this.windowEnd});
  factory _Fasting.fromJson(Map<String, dynamic> json) =>
      _$FastingFromJson(json);

  @override
  @JsonKey()
  final String mode;
  @override
  final String? windowStart;
  @override
  final String? windowEnd;

  /// Create a copy of Fasting
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$FastingCopyWith<_Fasting> get copyWith =>
      __$FastingCopyWithImpl<_Fasting>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$FastingToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Fasting &&
            (identical(other.mode, mode) || other.mode == mode) &&
            (identical(other.windowStart, windowStart) ||
                other.windowStart == windowStart) &&
            (identical(other.windowEnd, windowEnd) ||
                other.windowEnd == windowEnd));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, mode, windowStart, windowEnd);
  }

  @override
  String toString() {
    return 'Fasting(mode: $mode, windowStart: $windowStart, windowEnd: $windowEnd)';
  }
}

/// @nodoc
abstract mixin class _$FastingCopyWith<$Res> implements $FastingCopyWith<$Res> {
  factory _$FastingCopyWith(_Fasting value, $Res Function(_Fasting) _then) =
      __$FastingCopyWithImpl;
  @override
  @useResult
  $Res call({String mode, String? windowStart, String? windowEnd});
}

/// @nodoc
class __$FastingCopyWithImpl<$Res> implements _$FastingCopyWith<$Res> {
  __$FastingCopyWithImpl(this._self, this._then);

  final _Fasting _self;
  final $Res Function(_Fasting) _then;

  /// Create a copy of Fasting
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? mode = null,
    Object? windowStart = freezed,
    Object? windowEnd = freezed,
  }) {
    return _then(
      _Fasting(
        mode: null == mode
            ? _self.mode
            : mode // ignore: cast_nullable_to_non_nullable
                  as String,
        windowStart: freezed == windowStart
            ? _self.windowStart
            : windowStart // ignore: cast_nullable_to_non_nullable
                  as String?,
        windowEnd: freezed == windowEnd
            ? _self.windowEnd
            : windowEnd // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$NutritionDay {
  String get date;
  Macros? get targets;
  double get waterMl;
  double get waterTargetMl;
  Macros get consumed;
  List<MealSlot> get meals;
  Fasting? get fasting;

  /// Create a copy of NutritionDay
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $NutritionDayCopyWith<NutritionDay> get copyWith =>
      _$NutritionDayCopyWithImpl<NutritionDay>(
        this as NutritionDay,
        _$identity,
      );

  /// Serializes this NutritionDay to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as NutritionDay;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is NutritionDay &&
            (identical(other.date, _this.date) || other.date == _this.date) &&
            (identical(other.targets, _this.targets) ||
                other.targets == _this.targets) &&
            (identical(other.waterMl, _this.waterMl) ||
                other.waterMl == _this.waterMl) &&
            (identical(other.waterTargetMl, _this.waterTargetMl) ||
                other.waterTargetMl == _this.waterTargetMl) &&
            (identical(other.consumed, _this.consumed) ||
                other.consumed == _this.consumed) &&
            const DeepCollectionEquality().equals(other.meals, _this.meals) &&
            (identical(other.fasting, _this.fasting) ||
                other.fasting == _this.fasting));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as NutritionDay;
    return Object.hash(
      runtimeType,
      _this.date,
      _this.targets,
      _this.waterMl,
      _this.waterTargetMl,
      _this.consumed,
      const DeepCollectionEquality().hash(_this.meals),
      _this.fasting,
    );
  }

  @override
  String toString() {
    final _this = this as NutritionDay;
    return 'NutritionDay(date: ${_this.date}, targets: ${_this.targets}, waterMl: ${_this.waterMl}, waterTargetMl: ${_this.waterTargetMl}, consumed: ${_this.consumed}, meals: ${_this.meals}, fasting: ${_this.fasting})';
  }
}

/// @nodoc
abstract mixin class $NutritionDayCopyWith<$Res> {
  factory $NutritionDayCopyWith(
    NutritionDay value,
    $Res Function(NutritionDay) _then,
  ) = _$NutritionDayCopyWithImpl;
  @useResult
  $Res call({
    String date,
    Macros? targets,
    double waterMl,
    double waterTargetMl,
    Macros consumed,
    List<MealSlot> meals,
    Fasting? fasting,
  });

  $MacrosCopyWith<$Res>? get targets;
  $MacrosCopyWith<$Res> get consumed;
  $FastingCopyWith<$Res>? get fasting;
}

/// @nodoc
class _$NutritionDayCopyWithImpl<$Res> implements $NutritionDayCopyWith<$Res> {
  _$NutritionDayCopyWithImpl(this._self, this._then);

  final NutritionDay _self;
  final $Res Function(NutritionDay) _then;

  /// Create a copy of NutritionDay
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? date = null,
    Object? targets = freezed,
    Object? waterMl = null,
    Object? waterTargetMl = null,
    Object? consumed = null,
    Object? meals = null,
    Object? fasting = freezed,
  }) {
    return _then(
      NutritionDay(
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        targets: freezed == targets
            ? _self.targets
            : targets // ignore: cast_nullable_to_non_nullable
                  as Macros?,
        waterMl: null == waterMl
            ? _self.waterMl
            : waterMl // ignore: cast_nullable_to_non_nullable
                  as double,
        waterTargetMl: null == waterTargetMl
            ? _self.waterTargetMl
            : waterTargetMl // ignore: cast_nullable_to_non_nullable
                  as double,
        consumed: null == consumed
            ? _self.consumed
            : consumed // ignore: cast_nullable_to_non_nullable
                  as Macros,
        meals: null == meals
            ? _self.meals
            : meals // ignore: cast_nullable_to_non_nullable
                  as List<MealSlot>,
        fasting: freezed == fasting
            ? _self.fasting
            : fasting // ignore: cast_nullable_to_non_nullable
                  as Fasting?,
      ),
    );
  }

  /// Create a copy of NutritionDay
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

  /// Create a copy of NutritionDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MacrosCopyWith<$Res> get consumed {
    return $MacrosCopyWith<$Res>(_self.consumed, (value) {
      return _then(_self.copyWith(consumed: value));
    });
  }

  /// Create a copy of NutritionDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $FastingCopyWith<$Res>? get fasting {
    if (_self.fasting == null) {
      return null;
    }

    return $FastingCopyWith<$Res>(_self.fasting!, (value) {
      return _then(_self.copyWith(fasting: value));
    });
  }
}

/// Adds pattern-matching-related methods to [NutritionDay].
extension NutritionDayPatterns on NutritionDay {
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
    TResult Function(_NutritionDay value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _NutritionDay() when $default != null:
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
    TResult Function(_NutritionDay value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _NutritionDay():
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
    TResult? Function(_NutritionDay value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _NutritionDay() when $default != null:
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
      Macros? targets,
      double waterMl,
      double waterTargetMl,
      Macros consumed,
      List<MealSlot> meals,
      Fasting? fasting,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _NutritionDay() when $default != null:
        return $default(
          _that.date,
          _that.targets,
          _that.waterMl,
          _that.waterTargetMl,
          _that.consumed,
          _that.meals,
          _that.fasting,
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
      Macros? targets,
      double waterMl,
      double waterTargetMl,
      Macros consumed,
      List<MealSlot> meals,
      Fasting? fasting,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _NutritionDay():
        return $default(
          _that.date,
          _that.targets,
          _that.waterMl,
          _that.waterTargetMl,
          _that.consumed,
          _that.meals,
          _that.fasting,
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
      Macros? targets,
      double waterMl,
      double waterTargetMl,
      Macros consumed,
      List<MealSlot> meals,
      Fasting? fasting,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _NutritionDay() when $default != null:
        return $default(
          _that.date,
          _that.targets,
          _that.waterMl,
          _that.waterTargetMl,
          _that.consumed,
          _that.meals,
          _that.fasting,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _NutritionDay implements NutritionDay {
  const _NutritionDay({
    required this.date,
    this.targets,
    this.waterMl = 0,
    this.waterTargetMl = 0,
    this.consumed = const Macros(),
    List<MealSlot> meals = const <MealSlot>[],
    this.fasting,
  }) : _meals = meals;
  factory _NutritionDay.fromJson(Map<String, dynamic> json) =>
      _$NutritionDayFromJson(json);

  @override
  final String date;
  @override
  final Macros? targets;
  @override
  @JsonKey()
  final double waterMl;
  @override
  @JsonKey()
  final double waterTargetMl;
  @override
  @JsonKey()
  final Macros consumed;
  final List<MealSlot> _meals;
  @override
  @JsonKey()
  List<MealSlot> get meals {
    if (_meals is EqualUnmodifiableListView) return _meals;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_meals);
  }

  @override
  final Fasting? fasting;

  /// Create a copy of NutritionDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$NutritionDayCopyWith<_NutritionDay> get copyWith =>
      __$NutritionDayCopyWithImpl<_NutritionDay>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$NutritionDayToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _NutritionDay &&
            (identical(other.date, date) || other.date == date) &&
            (identical(other.targets, targets) || other.targets == targets) &&
            (identical(other.waterMl, waterMl) || other.waterMl == waterMl) &&
            (identical(other.waterTargetMl, waterTargetMl) ||
                other.waterTargetMl == waterTargetMl) &&
            (identical(other.consumed, consumed) ||
                other.consumed == consumed) &&
            const DeepCollectionEquality().equals(other.meals, _meals) &&
            (identical(other.fasting, fasting) || other.fasting == fasting));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      date,
      targets,
      waterMl,
      waterTargetMl,
      consumed,
      const DeepCollectionEquality().hash(_meals),
      fasting,
    );
  }

  @override
  String toString() {
    return 'NutritionDay(date: $date, targets: $targets, waterMl: $waterMl, waterTargetMl: $waterTargetMl, consumed: $consumed, meals: $meals, fasting: $fasting)';
  }
}

/// @nodoc
abstract mixin class _$NutritionDayCopyWith<$Res>
    implements $NutritionDayCopyWith<$Res> {
  factory _$NutritionDayCopyWith(
    _NutritionDay value,
    $Res Function(_NutritionDay) _then,
  ) = __$NutritionDayCopyWithImpl;
  @override
  @useResult
  $Res call({
    String date,
    Macros? targets,
    double waterMl,
    double waterTargetMl,
    Macros consumed,
    List<MealSlot> meals,
    Fasting? fasting,
  });

  @override
  $MacrosCopyWith<$Res>? get targets;
  @override
  $MacrosCopyWith<$Res> get consumed;
  @override
  $FastingCopyWith<$Res>? get fasting;
}

/// @nodoc
class __$NutritionDayCopyWithImpl<$Res>
    implements _$NutritionDayCopyWith<$Res> {
  __$NutritionDayCopyWithImpl(this._self, this._then);

  final _NutritionDay _self;
  final $Res Function(_NutritionDay) _then;

  /// Create a copy of NutritionDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? date = null,
    Object? targets = freezed,
    Object? waterMl = null,
    Object? waterTargetMl = null,
    Object? consumed = null,
    Object? meals = null,
    Object? fasting = freezed,
  }) {
    return _then(
      _NutritionDay(
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        targets: freezed == targets
            ? _self.targets
            : targets // ignore: cast_nullable_to_non_nullable
                  as Macros?,
        waterMl: null == waterMl
            ? _self.waterMl
            : waterMl // ignore: cast_nullable_to_non_nullable
                  as double,
        waterTargetMl: null == waterTargetMl
            ? _self.waterTargetMl
            : waterTargetMl // ignore: cast_nullable_to_non_nullable
                  as double,
        consumed: null == consumed
            ? _self.consumed
            : consumed // ignore: cast_nullable_to_non_nullable
                  as Macros,
        meals: null == meals
            ? _self._meals
            : meals // ignore: cast_nullable_to_non_nullable
                  as List<MealSlot>,
        fasting: freezed == fasting
            ? _self.fasting
            : fasting // ignore: cast_nullable_to_non_nullable
                  as Fasting?,
      ),
    );
  }

  /// Create a copy of NutritionDay
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

  /// Create a copy of NutritionDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MacrosCopyWith<$Res> get consumed {
    return $MacrosCopyWith<$Res>(_self.consumed, (value) {
      return _then(_self.copyWith(consumed: value));
    });
  }

  /// Create a copy of NutritionDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $FastingCopyWith<$Res>? get fasting {
    if (_self.fasting == null) {
      return null;
    }

    return $FastingCopyWith<$Res>(_self.fasting!, (value) {
      return _then(_self.copyWith(fasting: value));
    });
  }
}

/// @nodoc
mixin _$PlanMeal {
  String get slot;
  String? get name;
  String? get time;
  List<PlannedItem> get items;

  /// Create a copy of PlanMeal
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $PlanMealCopyWith<PlanMeal> get copyWith =>
      _$PlanMealCopyWithImpl<PlanMeal>(this as PlanMeal, _$identity);

  /// Serializes this PlanMeal to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as PlanMeal;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is PlanMeal &&
            (identical(other.slot, _this.slot) || other.slot == _this.slot) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.time, _this.time) || other.time == _this.time) &&
            const DeepCollectionEquality().equals(other.items, _this.items));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as PlanMeal;
    return Object.hash(
      runtimeType,
      _this.slot,
      _this.name,
      _this.time,
      const DeepCollectionEquality().hash(_this.items),
    );
  }

  @override
  String toString() {
    final _this = this as PlanMeal;
    return 'PlanMeal(slot: ${_this.slot}, name: ${_this.name}, time: ${_this.time}, items: ${_this.items})';
  }
}

/// @nodoc
abstract mixin class $PlanMealCopyWith<$Res> {
  factory $PlanMealCopyWith(PlanMeal value, $Res Function(PlanMeal) _then) =
      _$PlanMealCopyWithImpl;
  @useResult
  $Res call({String slot, String? name, String? time, List<PlannedItem> items});
}

/// @nodoc
class _$PlanMealCopyWithImpl<$Res> implements $PlanMealCopyWith<$Res> {
  _$PlanMealCopyWithImpl(this._self, this._then);

  final PlanMeal _self;
  final $Res Function(PlanMeal) _then;

  /// Create a copy of PlanMeal
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? slot = null,
    Object? name = freezed,
    Object? time = freezed,
    Object? items = null,
  }) {
    return _then(
      PlanMeal(
        slot: null == slot
            ? _self.slot
            : slot // ignore: cast_nullable_to_non_nullable
                  as String,
        name: freezed == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String?,
        time: freezed == time
            ? _self.time
            : time // ignore: cast_nullable_to_non_nullable
                  as String?,
        items: null == items
            ? _self.items
            : items // ignore: cast_nullable_to_non_nullable
                  as List<PlannedItem>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [PlanMeal].
extension PlanMealPatterns on PlanMeal {
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
    TResult Function(_PlanMeal value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PlanMeal() when $default != null:
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
    TResult Function(_PlanMeal value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlanMeal():
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
    TResult? Function(_PlanMeal value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlanMeal() when $default != null:
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
      String slot,
      String? name,
      String? time,
      List<PlannedItem> items,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PlanMeal() when $default != null:
        return $default(_that.slot, _that.name, _that.time, _that.items);
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
      String slot,
      String? name,
      String? time,
      List<PlannedItem> items,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlanMeal():
        return $default(_that.slot, _that.name, _that.time, _that.items);
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
      String slot,
      String? name,
      String? time,
      List<PlannedItem> items,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlanMeal() when $default != null:
        return $default(_that.slot, _that.name, _that.time, _that.items);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _PlanMeal implements PlanMeal {
  const _PlanMeal({
    required this.slot,
    this.name,
    this.time,
    List<PlannedItem> items = const <PlannedItem>[],
  }) : _items = items;
  factory _PlanMeal.fromJson(Map<String, dynamic> json) =>
      _$PlanMealFromJson(json);

  @override
  final String slot;
  @override
  final String? name;
  @override
  final String? time;
  final List<PlannedItem> _items;
  @override
  @JsonKey()
  List<PlannedItem> get items {
    if (_items is EqualUnmodifiableListView) return _items;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_items);
  }

  /// Create a copy of PlanMeal
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$PlanMealCopyWith<_PlanMeal> get copyWith =>
      __$PlanMealCopyWithImpl<_PlanMeal>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$PlanMealToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _PlanMeal &&
            (identical(other.slot, slot) || other.slot == slot) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.time, time) || other.time == time) &&
            const DeepCollectionEquality().equals(other.items, _items));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      slot,
      name,
      time,
      const DeepCollectionEquality().hash(_items),
    );
  }

  @override
  String toString() {
    return 'PlanMeal(slot: $slot, name: $name, time: $time, items: $items)';
  }
}

/// @nodoc
abstract mixin class _$PlanMealCopyWith<$Res>
    implements $PlanMealCopyWith<$Res> {
  factory _$PlanMealCopyWith(_PlanMeal value, $Res Function(_PlanMeal) _then) =
      __$PlanMealCopyWithImpl;
  @override
  @useResult
  $Res call({String slot, String? name, String? time, List<PlannedItem> items});
}

/// @nodoc
class __$PlanMealCopyWithImpl<$Res> implements _$PlanMealCopyWith<$Res> {
  __$PlanMealCopyWithImpl(this._self, this._then);

  final _PlanMeal _self;
  final $Res Function(_PlanMeal) _then;

  /// Create a copy of PlanMeal
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? slot = null,
    Object? name = freezed,
    Object? time = freezed,
    Object? items = null,
  }) {
    return _then(
      _PlanMeal(
        slot: null == slot
            ? _self.slot
            : slot // ignore: cast_nullable_to_non_nullable
                  as String,
        name: freezed == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String?,
        time: freezed == time
            ? _self.time
            : time // ignore: cast_nullable_to_non_nullable
                  as String?,
        items: null == items
            ? _self._items
            : items // ignore: cast_nullable_to_non_nullable
                  as List<PlannedItem>,
      ),
    );
  }
}

/// @nodoc
mixin _$PlanDay {
  int get dayNumber;
  String? get label;
  List<PlanMeal> get meals;

  /// Create a copy of PlanDay
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $PlanDayCopyWith<PlanDay> get copyWith =>
      _$PlanDayCopyWithImpl<PlanDay>(this as PlanDay, _$identity);

  /// Serializes this PlanDay to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as PlanDay;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is PlanDay &&
            (identical(other.dayNumber, _this.dayNumber) ||
                other.dayNumber == _this.dayNumber) &&
            (identical(other.label, _this.label) ||
                other.label == _this.label) &&
            const DeepCollectionEquality().equals(other.meals, _this.meals));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as PlanDay;
    return Object.hash(
      runtimeType,
      _this.dayNumber,
      _this.label,
      const DeepCollectionEquality().hash(_this.meals),
    );
  }

  @override
  String toString() {
    final _this = this as PlanDay;
    return 'PlanDay(dayNumber: ${_this.dayNumber}, label: ${_this.label}, meals: ${_this.meals})';
  }
}

/// @nodoc
abstract mixin class $PlanDayCopyWith<$Res> {
  factory $PlanDayCopyWith(PlanDay value, $Res Function(PlanDay) _then) =
      _$PlanDayCopyWithImpl;
  @useResult
  $Res call({int dayNumber, String? label, List<PlanMeal> meals});
}

/// @nodoc
class _$PlanDayCopyWithImpl<$Res> implements $PlanDayCopyWith<$Res> {
  _$PlanDayCopyWithImpl(this._self, this._then);

  final PlanDay _self;
  final $Res Function(PlanDay) _then;

  /// Create a copy of PlanDay
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? dayNumber = null,
    Object? label = freezed,
    Object? meals = null,
  }) {
    return _then(
      PlanDay(
        dayNumber: null == dayNumber
            ? _self.dayNumber
            : dayNumber // ignore: cast_nullable_to_non_nullable
                  as int,
        label: freezed == label
            ? _self.label
            : label // ignore: cast_nullable_to_non_nullable
                  as String?,
        meals: null == meals
            ? _self.meals
            : meals // ignore: cast_nullable_to_non_nullable
                  as List<PlanMeal>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [PlanDay].
extension PlanDayPatterns on PlanDay {
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
    TResult Function(_PlanDay value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PlanDay() when $default != null:
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
    TResult Function(_PlanDay value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlanDay():
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
    TResult? Function(_PlanDay value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlanDay() when $default != null:
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
    TResult Function(int dayNumber, String? label, List<PlanMeal> meals)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PlanDay() when $default != null:
        return $default(_that.dayNumber, _that.label, _that.meals);
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
    TResult Function(int dayNumber, String? label, List<PlanMeal> meals)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlanDay():
        return $default(_that.dayNumber, _that.label, _that.meals);
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
    TResult? Function(int dayNumber, String? label, List<PlanMeal> meals)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PlanDay() when $default != null:
        return $default(_that.dayNumber, _that.label, _that.meals);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _PlanDay implements PlanDay {
  const _PlanDay({
    required this.dayNumber,
    this.label,
    List<PlanMeal> meals = const <PlanMeal>[],
  }) : _meals = meals;
  factory _PlanDay.fromJson(Map<String, dynamic> json) =>
      _$PlanDayFromJson(json);

  @override
  final int dayNumber;
  @override
  final String? label;
  final List<PlanMeal> _meals;
  @override
  @JsonKey()
  List<PlanMeal> get meals {
    if (_meals is EqualUnmodifiableListView) return _meals;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_meals);
  }

  /// Create a copy of PlanDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$PlanDayCopyWith<_PlanDay> get copyWith =>
      __$PlanDayCopyWithImpl<_PlanDay>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$PlanDayToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _PlanDay &&
            (identical(other.dayNumber, dayNumber) ||
                other.dayNumber == dayNumber) &&
            (identical(other.label, label) || other.label == label) &&
            const DeepCollectionEquality().equals(other.meals, _meals));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      dayNumber,
      label,
      const DeepCollectionEquality().hash(_meals),
    );
  }

  @override
  String toString() {
    return 'PlanDay(dayNumber: $dayNumber, label: $label, meals: $meals)';
  }
}

/// @nodoc
abstract mixin class _$PlanDayCopyWith<$Res> implements $PlanDayCopyWith<$Res> {
  factory _$PlanDayCopyWith(_PlanDay value, $Res Function(_PlanDay) _then) =
      __$PlanDayCopyWithImpl;
  @override
  @useResult
  $Res call({int dayNumber, String? label, List<PlanMeal> meals});
}

/// @nodoc
class __$PlanDayCopyWithImpl<$Res> implements _$PlanDayCopyWith<$Res> {
  __$PlanDayCopyWithImpl(this._self, this._then);

  final _PlanDay _self;
  final $Res Function(_PlanDay) _then;

  /// Create a copy of PlanDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? dayNumber = null,
    Object? label = freezed,
    Object? meals = null,
  }) {
    return _then(
      _PlanDay(
        dayNumber: null == dayNumber
            ? _self.dayNumber
            : dayNumber // ignore: cast_nullable_to_non_nullable
                  as int,
        label: freezed == label
            ? _self.label
            : label // ignore: cast_nullable_to_non_nullable
                  as String?,
        meals: null == meals
            ? _self._meals
            : meals // ignore: cast_nullable_to_non_nullable
                  as List<PlanMeal>,
      ),
    );
  }
}

/// @nodoc
mixin _$MealPlan {
  String get id;
  String get name;
  String? get type;
  List<PlanDay> get days;

  /// Create a copy of MealPlan
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $MealPlanCopyWith<MealPlan> get copyWith =>
      _$MealPlanCopyWithImpl<MealPlan>(this as MealPlan, _$identity);

  /// Serializes this MealPlan to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as MealPlan;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is MealPlan &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            const DeepCollectionEquality().equals(other.days, _this.days));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as MealPlan;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.name,
      _this.type,
      const DeepCollectionEquality().hash(_this.days),
    );
  }

  @override
  String toString() {
    final _this = this as MealPlan;
    return 'MealPlan(id: ${_this.id}, name: ${_this.name}, type: ${_this.type}, days: ${_this.days})';
  }
}

/// @nodoc
abstract mixin class $MealPlanCopyWith<$Res> {
  factory $MealPlanCopyWith(MealPlan value, $Res Function(MealPlan) _then) =
      _$MealPlanCopyWithImpl;
  @useResult
  $Res call({String id, String name, String? type, List<PlanDay> days});
}

/// @nodoc
class _$MealPlanCopyWithImpl<$Res> implements $MealPlanCopyWith<$Res> {
  _$MealPlanCopyWithImpl(this._self, this._then);

  final MealPlan _self;
  final $Res Function(MealPlan) _then;

  /// Create a copy of MealPlan
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? type = freezed,
    Object? days = null,
  }) {
    return _then(
      MealPlan(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        type: freezed == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String?,
        days: null == days
            ? _self.days
            : days // ignore: cast_nullable_to_non_nullable
                  as List<PlanDay>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [MealPlan].
extension MealPlanPatterns on MealPlan {
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
    TResult Function(_MealPlan value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MealPlan() when $default != null:
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
    TResult Function(_MealPlan value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MealPlan():
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
    TResult? Function(_MealPlan value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MealPlan() when $default != null:
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
    TResult Function(String id, String name, String? type, List<PlanDay> days)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MealPlan() when $default != null:
        return $default(_that.id, _that.name, _that.type, _that.days);
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
    TResult Function(String id, String name, String? type, List<PlanDay> days)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MealPlan():
        return $default(_that.id, _that.name, _that.type, _that.days);
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
    TResult? Function(String id, String name, String? type, List<PlanDay> days)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MealPlan() when $default != null:
        return $default(_that.id, _that.name, _that.type, _that.days);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _MealPlan implements MealPlan {
  const _MealPlan({
    required this.id,
    required this.name,
    this.type,
    List<PlanDay> days = const <PlanDay>[],
  }) : _days = days;
  factory _MealPlan.fromJson(Map<String, dynamic> json) =>
      _$MealPlanFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String? type;
  final List<PlanDay> _days;
  @override
  @JsonKey()
  List<PlanDay> get days {
    if (_days is EqualUnmodifiableListView) return _days;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_days);
  }

  /// Create a copy of MealPlan
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$MealPlanCopyWith<_MealPlan> get copyWith =>
      __$MealPlanCopyWithImpl<_MealPlan>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$MealPlanToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _MealPlan &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.type, type) || other.type == type) &&
            const DeepCollectionEquality().equals(other.days, _days));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      name,
      type,
      const DeepCollectionEquality().hash(_days),
    );
  }

  @override
  String toString() {
    return 'MealPlan(id: $id, name: $name, type: $type, days: $days)';
  }
}

/// @nodoc
abstract mixin class _$MealPlanCopyWith<$Res>
    implements $MealPlanCopyWith<$Res> {
  factory _$MealPlanCopyWith(_MealPlan value, $Res Function(_MealPlan) _then) =
      __$MealPlanCopyWithImpl;
  @override
  @useResult
  $Res call({String id, String name, String? type, List<PlanDay> days});
}

/// @nodoc
class __$MealPlanCopyWithImpl<$Res> implements _$MealPlanCopyWith<$Res> {
  __$MealPlanCopyWithImpl(this._self, this._then);

  final _MealPlan _self;
  final $Res Function(_MealPlan) _then;

  /// Create a copy of MealPlan
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? type = freezed,
    Object? days = null,
  }) {
    return _then(
      _MealPlan(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        type: freezed == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String?,
        days: null == days
            ? _self._days
            : days // ignore: cast_nullable_to_non_nullable
                  as List<PlanDay>,
      ),
    );
  }
}

/// @nodoc
mixin _$PhotoCandidate {
  Food? get food;
  String get name;
  double get grams;
  double get confidence;
  double get kcal;
  double get proteinG;
  double get carbsG;
  double get fatG;

  /// Create a copy of PhotoCandidate
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $PhotoCandidateCopyWith<PhotoCandidate> get copyWith =>
      _$PhotoCandidateCopyWithImpl<PhotoCandidate>(
        this as PhotoCandidate,
        _$identity,
      );

  /// Serializes this PhotoCandidate to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as PhotoCandidate;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is PhotoCandidate &&
            (identical(other.food, _this.food) || other.food == _this.food) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.grams, _this.grams) ||
                other.grams == _this.grams) &&
            (identical(other.confidence, _this.confidence) ||
                other.confidence == _this.confidence) &&
            (identical(other.kcal, _this.kcal) || other.kcal == _this.kcal) &&
            (identical(other.proteinG, _this.proteinG) ||
                other.proteinG == _this.proteinG) &&
            (identical(other.carbsG, _this.carbsG) ||
                other.carbsG == _this.carbsG) &&
            (identical(other.fatG, _this.fatG) || other.fatG == _this.fatG));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as PhotoCandidate;
    return Object.hash(
      runtimeType,
      _this.food,
      _this.name,
      _this.grams,
      _this.confidence,
      _this.kcal,
      _this.proteinG,
      _this.carbsG,
      _this.fatG,
    );
  }

  @override
  String toString() {
    final _this = this as PhotoCandidate;
    return 'PhotoCandidate(food: ${_this.food}, name: ${_this.name}, grams: ${_this.grams}, confidence: ${_this.confidence}, kcal: ${_this.kcal}, proteinG: ${_this.proteinG}, carbsG: ${_this.carbsG}, fatG: ${_this.fatG})';
  }
}

/// @nodoc
abstract mixin class $PhotoCandidateCopyWith<$Res> {
  factory $PhotoCandidateCopyWith(
    PhotoCandidate value,
    $Res Function(PhotoCandidate) _then,
  ) = _$PhotoCandidateCopyWithImpl;
  @useResult
  $Res call({
    Food? food,
    String name,
    double grams,
    double confidence,
    double kcal,
    double proteinG,
    double carbsG,
    double fatG,
  });

  $FoodCopyWith<$Res>? get food;
}

/// @nodoc
class _$PhotoCandidateCopyWithImpl<$Res>
    implements $PhotoCandidateCopyWith<$Res> {
  _$PhotoCandidateCopyWithImpl(this._self, this._then);

  final PhotoCandidate _self;
  final $Res Function(PhotoCandidate) _then;

  /// Create a copy of PhotoCandidate
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? food = freezed,
    Object? name = null,
    Object? grams = null,
    Object? confidence = null,
    Object? kcal = null,
    Object? proteinG = null,
    Object? carbsG = null,
    Object? fatG = null,
  }) {
    return _then(
      PhotoCandidate(
        food: freezed == food
            ? _self.food
            : food // ignore: cast_nullable_to_non_nullable
                  as Food?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        grams: null == grams
            ? _self.grams
            : grams // ignore: cast_nullable_to_non_nullable
                  as double,
        confidence: null == confidence
            ? _self.confidence
            : confidence // ignore: cast_nullable_to_non_nullable
                  as double,
        kcal: null == kcal
            ? _self.kcal
            : kcal // ignore: cast_nullable_to_non_nullable
                  as double,
        proteinG: null == proteinG
            ? _self.proteinG
            : proteinG // ignore: cast_nullable_to_non_nullable
                  as double,
        carbsG: null == carbsG
            ? _self.carbsG
            : carbsG // ignore: cast_nullable_to_non_nullable
                  as double,
        fatG: null == fatG
            ? _self.fatG
            : fatG // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }

  /// Create a copy of PhotoCandidate
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $FoodCopyWith<$Res>? get food {
    if (_self.food == null) {
      return null;
    }

    return $FoodCopyWith<$Res>(_self.food!, (value) {
      return _then(_self.copyWith(food: value));
    });
  }
}

/// Adds pattern-matching-related methods to [PhotoCandidate].
extension PhotoCandidatePatterns on PhotoCandidate {
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
    TResult Function(_PhotoCandidate value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PhotoCandidate() when $default != null:
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
    TResult Function(_PhotoCandidate value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PhotoCandidate():
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
    TResult? Function(_PhotoCandidate value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PhotoCandidate() when $default != null:
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
      Food? food,
      String name,
      double grams,
      double confidence,
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PhotoCandidate() when $default != null:
        return $default(
          _that.food,
          _that.name,
          _that.grams,
          _that.confidence,
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
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
      Food? food,
      String name,
      double grams,
      double confidence,
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PhotoCandidate():
        return $default(
          _that.food,
          _that.name,
          _that.grams,
          _that.confidence,
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
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
      Food? food,
      String name,
      double grams,
      double confidence,
      double kcal,
      double proteinG,
      double carbsG,
      double fatG,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PhotoCandidate() when $default != null:
        return $default(
          _that.food,
          _that.name,
          _that.grams,
          _that.confidence,
          _that.kcal,
          _that.proteinG,
          _that.carbsG,
          _that.fatG,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _PhotoCandidate implements PhotoCandidate {
  const _PhotoCandidate({
    this.food,
    required this.name,
    this.grams = 0,
    this.confidence = 0,
    this.kcal = 0,
    this.proteinG = 0,
    this.carbsG = 0,
    this.fatG = 0,
  });
  factory _PhotoCandidate.fromJson(Map<String, dynamic> json) =>
      _$PhotoCandidateFromJson(json);

  @override
  final Food? food;
  @override
  final String name;
  @override
  @JsonKey()
  final double grams;
  @override
  @JsonKey()
  final double confidence;
  @override
  @JsonKey()
  final double kcal;
  @override
  @JsonKey()
  final double proteinG;
  @override
  @JsonKey()
  final double carbsG;
  @override
  @JsonKey()
  final double fatG;

  /// Create a copy of PhotoCandidate
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$PhotoCandidateCopyWith<_PhotoCandidate> get copyWith =>
      __$PhotoCandidateCopyWithImpl<_PhotoCandidate>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$PhotoCandidateToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _PhotoCandidate &&
            (identical(other.food, food) || other.food == food) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.grams, grams) || other.grams == grams) &&
            (identical(other.confidence, confidence) ||
                other.confidence == confidence) &&
            (identical(other.kcal, kcal) || other.kcal == kcal) &&
            (identical(other.proteinG, proteinG) ||
                other.proteinG == proteinG) &&
            (identical(other.carbsG, carbsG) || other.carbsG == carbsG) &&
            (identical(other.fatG, fatG) || other.fatG == fatG));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      food,
      name,
      grams,
      confidence,
      kcal,
      proteinG,
      carbsG,
      fatG,
    );
  }

  @override
  String toString() {
    return 'PhotoCandidate(food: $food, name: $name, grams: $grams, confidence: $confidence, kcal: $kcal, proteinG: $proteinG, carbsG: $carbsG, fatG: $fatG)';
  }
}

/// @nodoc
abstract mixin class _$PhotoCandidateCopyWith<$Res>
    implements $PhotoCandidateCopyWith<$Res> {
  factory _$PhotoCandidateCopyWith(
    _PhotoCandidate value,
    $Res Function(_PhotoCandidate) _then,
  ) = __$PhotoCandidateCopyWithImpl;
  @override
  @useResult
  $Res call({
    Food? food,
    String name,
    double grams,
    double confidence,
    double kcal,
    double proteinG,
    double carbsG,
    double fatG,
  });

  @override
  $FoodCopyWith<$Res>? get food;
}

/// @nodoc
class __$PhotoCandidateCopyWithImpl<$Res>
    implements _$PhotoCandidateCopyWith<$Res> {
  __$PhotoCandidateCopyWithImpl(this._self, this._then);

  final _PhotoCandidate _self;
  final $Res Function(_PhotoCandidate) _then;

  /// Create a copy of PhotoCandidate
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? food = freezed,
    Object? name = null,
    Object? grams = null,
    Object? confidence = null,
    Object? kcal = null,
    Object? proteinG = null,
    Object? carbsG = null,
    Object? fatG = null,
  }) {
    return _then(
      _PhotoCandidate(
        food: freezed == food
            ? _self.food
            : food // ignore: cast_nullable_to_non_nullable
                  as Food?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        grams: null == grams
            ? _self.grams
            : grams // ignore: cast_nullable_to_non_nullable
                  as double,
        confidence: null == confidence
            ? _self.confidence
            : confidence // ignore: cast_nullable_to_non_nullable
                  as double,
        kcal: null == kcal
            ? _self.kcal
            : kcal // ignore: cast_nullable_to_non_nullable
                  as double,
        proteinG: null == proteinG
            ? _self.proteinG
            : proteinG // ignore: cast_nullable_to_non_nullable
                  as double,
        carbsG: null == carbsG
            ? _self.carbsG
            : carbsG // ignore: cast_nullable_to_non_nullable
                  as double,
        fatG: null == fatG
            ? _self.fatG
            : fatG // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }

  /// Create a copy of PhotoCandidate
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $FoodCopyWith<$Res>? get food {
    if (_self.food == null) {
      return null;
    }

    return $FoodCopyWith<$Res>(_self.food!, (value) {
      return _then(_self.copyWith(food: value));
    });
  }
}

/// @nodoc
mixin _$PhotoRecognition {
  List<PhotoCandidate> get candidates;
  String? get aiOutputId;

  /// Create a copy of PhotoRecognition
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $PhotoRecognitionCopyWith<PhotoRecognition> get copyWith =>
      _$PhotoRecognitionCopyWithImpl<PhotoRecognition>(
        this as PhotoRecognition,
        _$identity,
      );

  /// Serializes this PhotoRecognition to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as PhotoRecognition;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is PhotoRecognition &&
            const DeepCollectionEquality().equals(
              other.candidates,
              _this.candidates,
            ) &&
            (identical(other.aiOutputId, _this.aiOutputId) ||
                other.aiOutputId == _this.aiOutputId));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as PhotoRecognition;
    return Object.hash(
      runtimeType,
      const DeepCollectionEquality().hash(_this.candidates),
      _this.aiOutputId,
    );
  }

  @override
  String toString() {
    final _this = this as PhotoRecognition;
    return 'PhotoRecognition(candidates: ${_this.candidates}, aiOutputId: ${_this.aiOutputId})';
  }
}

/// @nodoc
abstract mixin class $PhotoRecognitionCopyWith<$Res> {
  factory $PhotoRecognitionCopyWith(
    PhotoRecognition value,
    $Res Function(PhotoRecognition) _then,
  ) = _$PhotoRecognitionCopyWithImpl;
  @useResult
  $Res call({List<PhotoCandidate> candidates, String? aiOutputId});
}

/// @nodoc
class _$PhotoRecognitionCopyWithImpl<$Res>
    implements $PhotoRecognitionCopyWith<$Res> {
  _$PhotoRecognitionCopyWithImpl(this._self, this._then);

  final PhotoRecognition _self;
  final $Res Function(PhotoRecognition) _then;

  /// Create a copy of PhotoRecognition
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? candidates = null, Object? aiOutputId = freezed}) {
    return _then(
      PhotoRecognition(
        candidates: null == candidates
            ? _self.candidates
            : candidates // ignore: cast_nullable_to_non_nullable
                  as List<PhotoCandidate>,
        aiOutputId: freezed == aiOutputId
            ? _self.aiOutputId
            : aiOutputId // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [PhotoRecognition].
extension PhotoRecognitionPatterns on PhotoRecognition {
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
    TResult Function(_PhotoRecognition value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PhotoRecognition() when $default != null:
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
    TResult Function(_PhotoRecognition value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PhotoRecognition():
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
    TResult? Function(_PhotoRecognition value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PhotoRecognition() when $default != null:
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
    TResult Function(List<PhotoCandidate> candidates, String? aiOutputId)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PhotoRecognition() when $default != null:
        return $default(_that.candidates, _that.aiOutputId);
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
    TResult Function(List<PhotoCandidate> candidates, String? aiOutputId)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PhotoRecognition():
        return $default(_that.candidates, _that.aiOutputId);
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
    TResult? Function(List<PhotoCandidate> candidates, String? aiOutputId)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PhotoRecognition() when $default != null:
        return $default(_that.candidates, _that.aiOutputId);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _PhotoRecognition implements PhotoRecognition {
  const _PhotoRecognition({
    List<PhotoCandidate> candidates = const <PhotoCandidate>[],
    this.aiOutputId,
  }) : _candidates = candidates;
  factory _PhotoRecognition.fromJson(Map<String, dynamic> json) =>
      _$PhotoRecognitionFromJson(json);

  final List<PhotoCandidate> _candidates;
  @override
  @JsonKey()
  List<PhotoCandidate> get candidates {
    if (_candidates is EqualUnmodifiableListView) return _candidates;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_candidates);
  }

  @override
  final String? aiOutputId;

  /// Create a copy of PhotoRecognition
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$PhotoRecognitionCopyWith<_PhotoRecognition> get copyWith =>
      __$PhotoRecognitionCopyWithImpl<_PhotoRecognition>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$PhotoRecognitionToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _PhotoRecognition &&
            const DeepCollectionEquality().equals(
              other.candidates,
              _candidates,
            ) &&
            (identical(other.aiOutputId, aiOutputId) ||
                other.aiOutputId == aiOutputId));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      const DeepCollectionEquality().hash(_candidates),
      aiOutputId,
    );
  }

  @override
  String toString() {
    return 'PhotoRecognition(candidates: $candidates, aiOutputId: $aiOutputId)';
  }
}

/// @nodoc
abstract mixin class _$PhotoRecognitionCopyWith<$Res>
    implements $PhotoRecognitionCopyWith<$Res> {
  factory _$PhotoRecognitionCopyWith(
    _PhotoRecognition value,
    $Res Function(_PhotoRecognition) _then,
  ) = __$PhotoRecognitionCopyWithImpl;
  @override
  @useResult
  $Res call({List<PhotoCandidate> candidates, String? aiOutputId});
}

/// @nodoc
class __$PhotoRecognitionCopyWithImpl<$Res>
    implements _$PhotoRecognitionCopyWith<$Res> {
  __$PhotoRecognitionCopyWithImpl(this._self, this._then);

  final _PhotoRecognition _self;
  final $Res Function(_PhotoRecognition) _then;

  /// Create a copy of PhotoRecognition
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? candidates = null, Object? aiOutputId = freezed}) {
    return _then(
      _PhotoRecognition(
        candidates: null == candidates
            ? _self._candidates
            : candidates // ignore: cast_nullable_to_non_nullable
                  as List<PhotoCandidate>,
        aiOutputId: freezed == aiOutputId
            ? _self.aiOutputId
            : aiOutputId // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}
