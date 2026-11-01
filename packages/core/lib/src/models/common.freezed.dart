// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'common.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$Money {
  int get amountMinor;
  String get currency;
  String? get formatted;

  /// Create a copy of Money
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $MoneyCopyWith<Money> get copyWith =>
      _$MoneyCopyWithImpl<Money>(this as Money, _$identity);

  /// Serializes this Money to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Money;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Money &&
            (identical(other.amountMinor, _this.amountMinor) ||
                other.amountMinor == _this.amountMinor) &&
            (identical(other.currency, _this.currency) ||
                other.currency == _this.currency) &&
            (identical(other.formatted, _this.formatted) ||
                other.formatted == _this.formatted));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Money;
    return Object.hash(
      runtimeType,
      _this.amountMinor,
      _this.currency,
      _this.formatted,
    );
  }

  @override
  String toString() {
    final _this = this as Money;
    return 'Money(amountMinor: ${_this.amountMinor}, currency: ${_this.currency}, formatted: ${_this.formatted})';
  }
}

/// @nodoc
abstract mixin class $MoneyCopyWith<$Res> {
  factory $MoneyCopyWith(Money value, $Res Function(Money) _then) =
      _$MoneyCopyWithImpl;
  @useResult
  $Res call({int amountMinor, String currency, String? formatted});
}

/// @nodoc
class _$MoneyCopyWithImpl<$Res> implements $MoneyCopyWith<$Res> {
  _$MoneyCopyWithImpl(this._self, this._then);

  final Money _self;
  final $Res Function(Money) _then;

  /// Create a copy of Money
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? amountMinor = null,
    Object? currency = null,
    Object? formatted = freezed,
  }) {
    return _then(
      Money(
        amountMinor: null == amountMinor
            ? _self.amountMinor
            : amountMinor // ignore: cast_nullable_to_non_nullable
                  as int,
        currency: null == currency
            ? _self.currency
            : currency // ignore: cast_nullable_to_non_nullable
                  as String,
        formatted: freezed == formatted
            ? _self.formatted
            : formatted // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Money].
extension MoneyPatterns on Money {
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
    TResult Function(_Money value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Money() when $default != null:
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
    TResult Function(_Money value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Money():
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
    TResult? Function(_Money value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Money() when $default != null:
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
    TResult Function(int amountMinor, String currency, String? formatted)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Money() when $default != null:
        return $default(_that.amountMinor, _that.currency, _that.formatted);
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
    TResult Function(int amountMinor, String currency, String? formatted)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Money():
        return $default(_that.amountMinor, _that.currency, _that.formatted);
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
    TResult? Function(int amountMinor, String currency, String? formatted)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Money() when $default != null:
        return $default(_that.amountMinor, _that.currency, _that.formatted);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Money extends Money {
  const _Money({
    required this.amountMinor,
    required this.currency,
    this.formatted,
  }) : super._();
  factory _Money.fromJson(Map<String, dynamic> json) => _$MoneyFromJson(json);

  @override
  final int amountMinor;
  @override
  final String currency;
  @override
  final String? formatted;

  /// Create a copy of Money
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$MoneyCopyWith<_Money> get copyWith =>
      __$MoneyCopyWithImpl<_Money>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$MoneyToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Money &&
            (identical(other.amountMinor, amountMinor) ||
                other.amountMinor == amountMinor) &&
            (identical(other.currency, currency) ||
                other.currency == currency) &&
            (identical(other.formatted, formatted) ||
                other.formatted == formatted));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, amountMinor, currency, formatted);
  }

  @override
  String toString() {
    return 'Money(amountMinor: $amountMinor, currency: $currency, formatted: $formatted)';
  }
}

/// @nodoc
abstract mixin class _$MoneyCopyWith<$Res> implements $MoneyCopyWith<$Res> {
  factory _$MoneyCopyWith(_Money value, $Res Function(_Money) _then) =
      __$MoneyCopyWithImpl;
  @override
  @useResult
  $Res call({int amountMinor, String currency, String? formatted});
}

/// @nodoc
class __$MoneyCopyWithImpl<$Res> implements _$MoneyCopyWith<$Res> {
  __$MoneyCopyWithImpl(this._self, this._then);

  final _Money _self;
  final $Res Function(_Money) _then;

  /// Create a copy of Money
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? amountMinor = null,
    Object? currency = null,
    Object? formatted = freezed,
  }) {
    return _then(
      _Money(
        amountMinor: null == amountMinor
            ? _self.amountMinor
            : amountMinor // ignore: cast_nullable_to_non_nullable
                  as int,
        currency: null == currency
            ? _self.currency
            : currency // ignore: cast_nullable_to_non_nullable
                  as String,
        formatted: freezed == formatted
            ? _self.formatted
            : formatted // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$IdName {
  String get id;
  String get name;

  /// Create a copy of IdName
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $IdNameCopyWith<IdName> get copyWith =>
      _$IdNameCopyWithImpl<IdName>(this as IdName, _$identity);

  /// Serializes this IdName to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as IdName;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is IdName &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as IdName;
    return Object.hash(runtimeType, _this.id, _this.name);
  }

  @override
  String toString() {
    final _this = this as IdName;
    return 'IdName(id: ${_this.id}, name: ${_this.name})';
  }
}

/// @nodoc
abstract mixin class $IdNameCopyWith<$Res> {
  factory $IdNameCopyWith(IdName value, $Res Function(IdName) _then) =
      _$IdNameCopyWithImpl;
  @useResult
  $Res call({String id, String name});
}

/// @nodoc
class _$IdNameCopyWithImpl<$Res> implements $IdNameCopyWith<$Res> {
  _$IdNameCopyWithImpl(this._self, this._then);

  final IdName _self;
  final $Res Function(IdName) _then;

  /// Create a copy of IdName
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? id = null, Object? name = null}) {
    return _then(
      IdName(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [IdName].
extension IdNamePatterns on IdName {
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
    TResult Function(_IdName value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _IdName() when $default != null:
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
    TResult Function(_IdName value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _IdName():
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
    TResult? Function(_IdName value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _IdName() when $default != null:
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
    TResult Function(String id, String name)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _IdName() when $default != null:
        return $default(_that.id, _that.name);
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
    TResult Function(String id, String name) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _IdName():
        return $default(_that.id, _that.name);
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
    TResult? Function(String id, String name)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _IdName() when $default != null:
        return $default(_that.id, _that.name);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _IdName implements IdName {
  const _IdName({required this.id, required this.name});
  factory _IdName.fromJson(Map<String, dynamic> json) => _$IdNameFromJson(json);

  @override
  final String id;
  @override
  final String name;

  /// Create a copy of IdName
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$IdNameCopyWith<_IdName> get copyWith =>
      __$IdNameCopyWithImpl<_IdName>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$IdNameToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _IdName &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, name);
  }

  @override
  String toString() {
    return 'IdName(id: $id, name: $name)';
  }
}

/// @nodoc
abstract mixin class _$IdNameCopyWith<$Res> implements $IdNameCopyWith<$Res> {
  factory _$IdNameCopyWith(_IdName value, $Res Function(_IdName) _then) =
      __$IdNameCopyWithImpl;
  @override
  @useResult
  $Res call({String id, String name});
}

/// @nodoc
class __$IdNameCopyWithImpl<$Res> implements _$IdNameCopyWith<$Res> {
  __$IdNameCopyWithImpl(this._self, this._then);

  final _IdName _self;
  final $Res Function(_IdName) _then;

  /// Create a copy of IdName
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? id = null, Object? name = null}) {
    return _then(
      _IdName(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
      ),
    );
  }
}

/// @nodoc
mixin _$PersonRef {
  String get id;
  String get name;
  String? get avatarUrl;

  /// Create a copy of PersonRef
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $PersonRefCopyWith<PersonRef> get copyWith =>
      _$PersonRefCopyWithImpl<PersonRef>(this as PersonRef, _$identity);

  /// Serializes this PersonRef to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as PersonRef;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is PersonRef &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.avatarUrl, _this.avatarUrl) ||
                other.avatarUrl == _this.avatarUrl));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as PersonRef;
    return Object.hash(runtimeType, _this.id, _this.name, _this.avatarUrl);
  }

  @override
  String toString() {
    final _this = this as PersonRef;
    return 'PersonRef(id: ${_this.id}, name: ${_this.name}, avatarUrl: ${_this.avatarUrl})';
  }
}

/// @nodoc
abstract mixin class $PersonRefCopyWith<$Res> {
  factory $PersonRefCopyWith(PersonRef value, $Res Function(PersonRef) _then) =
      _$PersonRefCopyWithImpl;
  @useResult
  $Res call({String id, String name, String? avatarUrl});
}

/// @nodoc
class _$PersonRefCopyWithImpl<$Res> implements $PersonRefCopyWith<$Res> {
  _$PersonRefCopyWithImpl(this._self, this._then);

  final PersonRef _self;
  final $Res Function(PersonRef) _then;

  /// Create a copy of PersonRef
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? avatarUrl = freezed,
  }) {
    return _then(
      PersonRef(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        avatarUrl: freezed == avatarUrl
            ? _self.avatarUrl
            : avatarUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [PersonRef].
extension PersonRefPatterns on PersonRef {
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
    TResult Function(_PersonRef value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PersonRef() when $default != null:
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
    TResult Function(_PersonRef value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PersonRef():
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
    TResult? Function(_PersonRef value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PersonRef() when $default != null:
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
    TResult Function(String id, String name, String? avatarUrl)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PersonRef() when $default != null:
        return $default(_that.id, _that.name, _that.avatarUrl);
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
    TResult Function(String id, String name, String? avatarUrl) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PersonRef():
        return $default(_that.id, _that.name, _that.avatarUrl);
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
    TResult? Function(String id, String name, String? avatarUrl)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PersonRef() when $default != null:
        return $default(_that.id, _that.name, _that.avatarUrl);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _PersonRef implements PersonRef {
  const _PersonRef({required this.id, required this.name, this.avatarUrl});
  factory _PersonRef.fromJson(Map<String, dynamic> json) =>
      _$PersonRefFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String? avatarUrl;

  /// Create a copy of PersonRef
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$PersonRefCopyWith<_PersonRef> get copyWith =>
      __$PersonRefCopyWithImpl<_PersonRef>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$PersonRefToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _PersonRef &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.avatarUrl, avatarUrl) ||
                other.avatarUrl == avatarUrl));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, name, avatarUrl);
  }

  @override
  String toString() {
    return 'PersonRef(id: $id, name: $name, avatarUrl: $avatarUrl)';
  }
}

/// @nodoc
abstract mixin class _$PersonRefCopyWith<$Res>
    implements $PersonRefCopyWith<$Res> {
  factory _$PersonRefCopyWith(
    _PersonRef value,
    $Res Function(_PersonRef) _then,
  ) = __$PersonRefCopyWithImpl;
  @override
  @useResult
  $Res call({String id, String name, String? avatarUrl});
}

/// @nodoc
class __$PersonRefCopyWithImpl<$Res> implements _$PersonRefCopyWith<$Res> {
  __$PersonRefCopyWithImpl(this._self, this._then);

  final _PersonRef _self;
  final $Res Function(_PersonRef) _then;

  /// Create a copy of PersonRef
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? avatarUrl = freezed,
  }) {
    return _then(
      _PersonRef(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        avatarUrl: freezed == avatarUrl
            ? _self.avatarUrl
            : avatarUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}
