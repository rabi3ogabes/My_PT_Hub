// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'shop.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$Price {
  String get id;
  String get type;
  Money get amount;
  String? get interval;
  int? get intervalCount;
  int? get installments;
  int? get trialDays;

  /// Create a copy of Price
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $PriceCopyWith<Price> get copyWith =>
      _$PriceCopyWithImpl<Price>(this as Price, _$identity);

  /// Serializes this Price to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Price;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Price &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            (identical(other.amount, _this.amount) ||
                other.amount == _this.amount) &&
            (identical(other.interval, _this.interval) ||
                other.interval == _this.interval) &&
            (identical(other.intervalCount, _this.intervalCount) ||
                other.intervalCount == _this.intervalCount) &&
            (identical(other.installments, _this.installments) ||
                other.installments == _this.installments) &&
            (identical(other.trialDays, _this.trialDays) ||
                other.trialDays == _this.trialDays));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Price;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.type,
      _this.amount,
      _this.interval,
      _this.intervalCount,
      _this.installments,
      _this.trialDays,
    );
  }

  @override
  String toString() {
    final _this = this as Price;
    return 'Price(id: ${_this.id}, type: ${_this.type}, amount: ${_this.amount}, interval: ${_this.interval}, intervalCount: ${_this.intervalCount}, installments: ${_this.installments}, trialDays: ${_this.trialDays})';
  }
}

/// @nodoc
abstract mixin class $PriceCopyWith<$Res> {
  factory $PriceCopyWith(Price value, $Res Function(Price) _then) =
      _$PriceCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String type,
    Money amount,
    String? interval,
    int? intervalCount,
    int? installments,
    int? trialDays,
  });

  $MoneyCopyWith<$Res> get amount;
}

/// @nodoc
class _$PriceCopyWithImpl<$Res> implements $PriceCopyWith<$Res> {
  _$PriceCopyWithImpl(this._self, this._then);

  final Price _self;
  final $Res Function(Price) _then;

  /// Create a copy of Price
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? amount = null,
    Object? interval = freezed,
    Object? intervalCount = freezed,
    Object? installments = freezed,
    Object? trialDays = freezed,
  }) {
    return _then(
      Price(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        amount: null == amount
            ? _self.amount
            : amount // ignore: cast_nullable_to_non_nullable
                  as Money,
        interval: freezed == interval
            ? _self.interval
            : interval // ignore: cast_nullable_to_non_nullable
                  as String?,
        intervalCount: freezed == intervalCount
            ? _self.intervalCount
            : intervalCount // ignore: cast_nullable_to_non_nullable
                  as int?,
        installments: freezed == installments
            ? _self.installments
            : installments // ignore: cast_nullable_to_non_nullable
                  as int?,
        trialDays: freezed == trialDays
            ? _self.trialDays
            : trialDays // ignore: cast_nullable_to_non_nullable
                  as int?,
      ),
    );
  }

  /// Create a copy of Price
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MoneyCopyWith<$Res> get amount {
    return $MoneyCopyWith<$Res>(_self.amount, (value) {
      return _then(_self.copyWith(amount: value));
    });
  }
}

/// Adds pattern-matching-related methods to [Price].
extension PricePatterns on Price {
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
    TResult Function(_Price value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Price() when $default != null:
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
    TResult Function(_Price value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Price():
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
    TResult? Function(_Price value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Price() when $default != null:
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
      Money amount,
      String? interval,
      int? intervalCount,
      int? installments,
      int? trialDays,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Price() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.amount,
          _that.interval,
          _that.intervalCount,
          _that.installments,
          _that.trialDays,
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
      Money amount,
      String? interval,
      int? intervalCount,
      int? installments,
      int? trialDays,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Price():
        return $default(
          _that.id,
          _that.type,
          _that.amount,
          _that.interval,
          _that.intervalCount,
          _that.installments,
          _that.trialDays,
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
      Money amount,
      String? interval,
      int? intervalCount,
      int? installments,
      int? trialDays,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Price() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.amount,
          _that.interval,
          _that.intervalCount,
          _that.installments,
          _that.trialDays,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Price implements Price {
  const _Price({
    required this.id,
    this.type = 'one_time',
    required this.amount,
    this.interval,
    this.intervalCount,
    this.installments,
    this.trialDays,
  });
  factory _Price.fromJson(Map<String, dynamic> json) => _$PriceFromJson(json);

  @override
  final String id;
  @override
  @JsonKey()
  final String type;
  @override
  final Money amount;
  @override
  final String? interval;
  @override
  final int? intervalCount;
  @override
  final int? installments;
  @override
  final int? trialDays;

  /// Create a copy of Price
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$PriceCopyWith<_Price> get copyWith =>
      __$PriceCopyWithImpl<_Price>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$PriceToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Price &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.amount, amount) || other.amount == amount) &&
            (identical(other.interval, interval) ||
                other.interval == interval) &&
            (identical(other.intervalCount, intervalCount) ||
                other.intervalCount == intervalCount) &&
            (identical(other.installments, installments) ||
                other.installments == installments) &&
            (identical(other.trialDays, trialDays) ||
                other.trialDays == trialDays));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      type,
      amount,
      interval,
      intervalCount,
      installments,
      trialDays,
    );
  }

  @override
  String toString() {
    return 'Price(id: $id, type: $type, amount: $amount, interval: $interval, intervalCount: $intervalCount, installments: $installments, trialDays: $trialDays)';
  }
}

/// @nodoc
abstract mixin class _$PriceCopyWith<$Res> implements $PriceCopyWith<$Res> {
  factory _$PriceCopyWith(_Price value, $Res Function(_Price) _then) =
      __$PriceCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String type,
    Money amount,
    String? interval,
    int? intervalCount,
    int? installments,
    int? trialDays,
  });

  @override
  $MoneyCopyWith<$Res> get amount;
}

/// @nodoc
class __$PriceCopyWithImpl<$Res> implements _$PriceCopyWith<$Res> {
  __$PriceCopyWithImpl(this._self, this._then);

  final _Price _self;
  final $Res Function(_Price) _then;

  /// Create a copy of Price
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? amount = null,
    Object? interval = freezed,
    Object? intervalCount = freezed,
    Object? installments = freezed,
    Object? trialDays = freezed,
  }) {
    return _then(
      _Price(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        amount: null == amount
            ? _self.amount
            : amount // ignore: cast_nullable_to_non_nullable
                  as Money,
        interval: freezed == interval
            ? _self.interval
            : interval // ignore: cast_nullable_to_non_nullable
                  as String?,
        intervalCount: freezed == intervalCount
            ? _self.intervalCount
            : intervalCount // ignore: cast_nullable_to_non_nullable
                  as int?,
        installments: freezed == installments
            ? _self.installments
            : installments // ignore: cast_nullable_to_non_nullable
                  as int?,
        trialDays: freezed == trialDays
            ? _self.trialDays
            : trialDays // ignore: cast_nullable_to_non_nullable
                  as int?,
      ),
    );
  }

  /// Create a copy of Price
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MoneyCopyWith<$Res> get amount {
    return $MoneyCopyWith<$Res>(_self.amount, (value) {
      return _then(_self.copyWith(amount: value));
    });
  }
}

/// @nodoc
mixin _$Product {
  String get id;
  String get type;
  String? get slug;
  String get name;
  String? get description;
  String? get imageUrl;
  List<Price> get prices;
  String get iosPurchaseMode;

  /// Create a copy of Product
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ProductCopyWith<Product> get copyWith =>
      _$ProductCopyWithImpl<Product>(this as Product, _$identity);

  /// Serializes this Product to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Product;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Product &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            (identical(other.slug, _this.slug) || other.slug == _this.slug) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.description, _this.description) ||
                other.description == _this.description) &&
            (identical(other.imageUrl, _this.imageUrl) ||
                other.imageUrl == _this.imageUrl) &&
            const DeepCollectionEquality().equals(other.prices, _this.prices) &&
            (identical(other.iosPurchaseMode, _this.iosPurchaseMode) ||
                other.iosPurchaseMode == _this.iosPurchaseMode));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Product;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.type,
      _this.slug,
      _this.name,
      _this.description,
      _this.imageUrl,
      const DeepCollectionEquality().hash(_this.prices),
      _this.iosPurchaseMode,
    );
  }

  @override
  String toString() {
    final _this = this as Product;
    return 'Product(id: ${_this.id}, type: ${_this.type}, slug: ${_this.slug}, name: ${_this.name}, description: ${_this.description}, imageUrl: ${_this.imageUrl}, prices: ${_this.prices}, iosPurchaseMode: ${_this.iosPurchaseMode})';
  }
}

/// @nodoc
abstract mixin class $ProductCopyWith<$Res> {
  factory $ProductCopyWith(Product value, $Res Function(Product) _then) =
      _$ProductCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String type,
    String? slug,
    String name,
    String? description,
    String? imageUrl,
    List<Price> prices,
    String iosPurchaseMode,
  });
}

/// @nodoc
class _$ProductCopyWithImpl<$Res> implements $ProductCopyWith<$Res> {
  _$ProductCopyWithImpl(this._self, this._then);

  final Product _self;
  final $Res Function(Product) _then;

  /// Create a copy of Product
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? slug = freezed,
    Object? name = null,
    Object? description = freezed,
    Object? imageUrl = freezed,
    Object? prices = null,
    Object? iosPurchaseMode = null,
  }) {
    return _then(
      Product(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        slug: freezed == slug
            ? _self.slug
            : slug // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        description: freezed == description
            ? _self.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String?,
        imageUrl: freezed == imageUrl
            ? _self.imageUrl
            : imageUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        prices: null == prices
            ? _self.prices
            : prices // ignore: cast_nullable_to_non_nullable
                  as List<Price>,
        iosPurchaseMode: null == iosPurchaseMode
            ? _self.iosPurchaseMode
            : iosPurchaseMode // ignore: cast_nullable_to_non_nullable
                  as String,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Product].
extension ProductPatterns on Product {
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
    TResult Function(_Product value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Product() when $default != null:
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
    TResult Function(_Product value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Product():
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
    TResult? Function(_Product value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Product() when $default != null:
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
      String? slug,
      String name,
      String? description,
      String? imageUrl,
      List<Price> prices,
      String iosPurchaseMode,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Product() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.slug,
          _that.name,
          _that.description,
          _that.imageUrl,
          _that.prices,
          _that.iosPurchaseMode,
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
      String? slug,
      String name,
      String? description,
      String? imageUrl,
      List<Price> prices,
      String iosPurchaseMode,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Product():
        return $default(
          _that.id,
          _that.type,
          _that.slug,
          _that.name,
          _that.description,
          _that.imageUrl,
          _that.prices,
          _that.iosPurchaseMode,
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
      String? slug,
      String name,
      String? description,
      String? imageUrl,
      List<Price> prices,
      String iosPurchaseMode,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Product() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.slug,
          _that.name,
          _that.description,
          _that.imageUrl,
          _that.prices,
          _that.iosPurchaseMode,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Product implements Product {
  const _Product({
    required this.id,
    required this.type,
    this.slug,
    required this.name,
    this.description,
    this.imageUrl,
    List<Price> prices = const <Price>[],
    this.iosPurchaseMode = 'external',
  }) : _prices = prices;
  factory _Product.fromJson(Map<String, dynamic> json) =>
      _$ProductFromJson(json);

  @override
  final String id;
  @override
  final String type;
  @override
  final String? slug;
  @override
  final String name;
  @override
  final String? description;
  @override
  final String? imageUrl;
  final List<Price> _prices;
  @override
  @JsonKey()
  List<Price> get prices {
    if (_prices is EqualUnmodifiableListView) return _prices;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_prices);
  }

  @override
  @JsonKey()
  final String iosPurchaseMode;

  /// Create a copy of Product
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ProductCopyWith<_Product> get copyWith =>
      __$ProductCopyWithImpl<_Product>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ProductToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Product &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.slug, slug) || other.slug == slug) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.imageUrl, imageUrl) ||
                other.imageUrl == imageUrl) &&
            const DeepCollectionEquality().equals(other.prices, _prices) &&
            (identical(other.iosPurchaseMode, iosPurchaseMode) ||
                other.iosPurchaseMode == iosPurchaseMode));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      type,
      slug,
      name,
      description,
      imageUrl,
      const DeepCollectionEquality().hash(_prices),
      iosPurchaseMode,
    );
  }

  @override
  String toString() {
    return 'Product(id: $id, type: $type, slug: $slug, name: $name, description: $description, imageUrl: $imageUrl, prices: $prices, iosPurchaseMode: $iosPurchaseMode)';
  }
}

/// @nodoc
abstract mixin class _$ProductCopyWith<$Res> implements $ProductCopyWith<$Res> {
  factory _$ProductCopyWith(_Product value, $Res Function(_Product) _then) =
      __$ProductCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String type,
    String? slug,
    String name,
    String? description,
    String? imageUrl,
    List<Price> prices,
    String iosPurchaseMode,
  });
}

/// @nodoc
class __$ProductCopyWithImpl<$Res> implements _$ProductCopyWith<$Res> {
  __$ProductCopyWithImpl(this._self, this._then);

  final _Product _self;
  final $Res Function(_Product) _then;

  /// Create a copy of Product
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? slug = freezed,
    Object? name = null,
    Object? description = freezed,
    Object? imageUrl = freezed,
    Object? prices = null,
    Object? iosPurchaseMode = null,
  }) {
    return _then(
      _Product(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        slug: freezed == slug
            ? _self.slug
            : slug // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        description: freezed == description
            ? _self.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String?,
        imageUrl: freezed == imageUrl
            ? _self.imageUrl
            : imageUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        prices: null == prices
            ? _self._prices
            : prices // ignore: cast_nullable_to_non_nullable
                  as List<Price>,
        iosPurchaseMode: null == iosPurchaseMode
            ? _self.iosPurchaseMode
            : iosPurchaseMode // ignore: cast_nullable_to_non_nullable
                  as String,
      ),
    );
  }
}

/// @nodoc
mixin _$CheckoutResult {
  String get orderId;
  String? get checkoutUrl;
  String get status;

  /// Create a copy of CheckoutResult
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $CheckoutResultCopyWith<CheckoutResult> get copyWith =>
      _$CheckoutResultCopyWithImpl<CheckoutResult>(
        this as CheckoutResult,
        _$identity,
      );

  /// Serializes this CheckoutResult to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as CheckoutResult;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is CheckoutResult &&
            (identical(other.orderId, _this.orderId) ||
                other.orderId == _this.orderId) &&
            (identical(other.checkoutUrl, _this.checkoutUrl) ||
                other.checkoutUrl == _this.checkoutUrl) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as CheckoutResult;
    return Object.hash(
      runtimeType,
      _this.orderId,
      _this.checkoutUrl,
      _this.status,
    );
  }

  @override
  String toString() {
    final _this = this as CheckoutResult;
    return 'CheckoutResult(orderId: ${_this.orderId}, checkoutUrl: ${_this.checkoutUrl}, status: ${_this.status})';
  }
}

/// @nodoc
abstract mixin class $CheckoutResultCopyWith<$Res> {
  factory $CheckoutResultCopyWith(
    CheckoutResult value,
    $Res Function(CheckoutResult) _then,
  ) = _$CheckoutResultCopyWithImpl;
  @useResult
  $Res call({String orderId, String? checkoutUrl, String status});
}

/// @nodoc
class _$CheckoutResultCopyWithImpl<$Res>
    implements $CheckoutResultCopyWith<$Res> {
  _$CheckoutResultCopyWithImpl(this._self, this._then);

  final CheckoutResult _self;
  final $Res Function(CheckoutResult) _then;

  /// Create a copy of CheckoutResult
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? orderId = null,
    Object? checkoutUrl = freezed,
    Object? status = null,
  }) {
    return _then(
      CheckoutResult(
        orderId: null == orderId
            ? _self.orderId
            : orderId // ignore: cast_nullable_to_non_nullable
                  as String,
        checkoutUrl: freezed == checkoutUrl
            ? _self.checkoutUrl
            : checkoutUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [CheckoutResult].
extension CheckoutResultPatterns on CheckoutResult {
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
    TResult Function(_CheckoutResult value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckoutResult() when $default != null:
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
    TResult Function(_CheckoutResult value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckoutResult():
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
    TResult? Function(_CheckoutResult value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckoutResult() when $default != null:
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
    TResult Function(String orderId, String? checkoutUrl, String status)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckoutResult() when $default != null:
        return $default(_that.orderId, _that.checkoutUrl, _that.status);
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
    TResult Function(String orderId, String? checkoutUrl, String status)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckoutResult():
        return $default(_that.orderId, _that.checkoutUrl, _that.status);
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
    TResult? Function(String orderId, String? checkoutUrl, String status)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckoutResult() when $default != null:
        return $default(_that.orderId, _that.checkoutUrl, _that.status);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _CheckoutResult implements CheckoutResult {
  const _CheckoutResult({
    required this.orderId,
    this.checkoutUrl,
    required this.status,
  });
  factory _CheckoutResult.fromJson(Map<String, dynamic> json) =>
      _$CheckoutResultFromJson(json);

  @override
  final String orderId;
  @override
  final String? checkoutUrl;
  @override
  final String status;

  /// Create a copy of CheckoutResult
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$CheckoutResultCopyWith<_CheckoutResult> get copyWith =>
      __$CheckoutResultCopyWithImpl<_CheckoutResult>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$CheckoutResultToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _CheckoutResult &&
            (identical(other.orderId, orderId) || other.orderId == orderId) &&
            (identical(other.checkoutUrl, checkoutUrl) ||
                other.checkoutUrl == checkoutUrl) &&
            (identical(other.status, status) || other.status == status));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, orderId, checkoutUrl, status);
  }

  @override
  String toString() {
    return 'CheckoutResult(orderId: $orderId, checkoutUrl: $checkoutUrl, status: $status)';
  }
}

/// @nodoc
abstract mixin class _$CheckoutResultCopyWith<$Res>
    implements $CheckoutResultCopyWith<$Res> {
  factory _$CheckoutResultCopyWith(
    _CheckoutResult value,
    $Res Function(_CheckoutResult) _then,
  ) = __$CheckoutResultCopyWithImpl;
  @override
  @useResult
  $Res call({String orderId, String? checkoutUrl, String status});
}

/// @nodoc
class __$CheckoutResultCopyWithImpl<$Res>
    implements _$CheckoutResultCopyWith<$Res> {
  __$CheckoutResultCopyWithImpl(this._self, this._then);

  final _CheckoutResult _self;
  final $Res Function(_CheckoutResult) _then;

  /// Create a copy of CheckoutResult
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? orderId = null,
    Object? checkoutUrl = freezed,
    Object? status = null,
  }) {
    return _then(
      _CheckoutResult(
        orderId: null == orderId
            ? _self.orderId
            : orderId // ignore: cast_nullable_to_non_nullable
                  as String,
        checkoutUrl: freezed == checkoutUrl
            ? _self.checkoutUrl
            : checkoutUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
      ),
    );
  }
}

/// @nodoc
mixin _$OrderStatus {
  String get status;
  bool get fulfilled;

  /// Create a copy of OrderStatus
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $OrderStatusCopyWith<OrderStatus> get copyWith =>
      _$OrderStatusCopyWithImpl<OrderStatus>(this as OrderStatus, _$identity);

  /// Serializes this OrderStatus to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as OrderStatus;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is OrderStatus &&
            (identical(other.status, _this.status) ||
                other.status == _this.status) &&
            (identical(other.fulfilled, _this.fulfilled) ||
                other.fulfilled == _this.fulfilled));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as OrderStatus;
    return Object.hash(runtimeType, _this.status, _this.fulfilled);
  }

  @override
  String toString() {
    final _this = this as OrderStatus;
    return 'OrderStatus(status: ${_this.status}, fulfilled: ${_this.fulfilled})';
  }
}

/// @nodoc
abstract mixin class $OrderStatusCopyWith<$Res> {
  factory $OrderStatusCopyWith(
    OrderStatus value,
    $Res Function(OrderStatus) _then,
  ) = _$OrderStatusCopyWithImpl;
  @useResult
  $Res call({String status, bool fulfilled});
}

/// @nodoc
class _$OrderStatusCopyWithImpl<$Res> implements $OrderStatusCopyWith<$Res> {
  _$OrderStatusCopyWithImpl(this._self, this._then);

  final OrderStatus _self;
  final $Res Function(OrderStatus) _then;

  /// Create a copy of OrderStatus
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? status = null, Object? fulfilled = null}) {
    return _then(
      OrderStatus(
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        fulfilled: null == fulfilled
            ? _self.fulfilled
            : fulfilled // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [OrderStatus].
extension OrderStatusPatterns on OrderStatus {
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
    TResult Function(_OrderStatus value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _OrderStatus() when $default != null:
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
    TResult Function(_OrderStatus value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _OrderStatus():
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
    TResult? Function(_OrderStatus value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _OrderStatus() when $default != null:
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
    TResult Function(String status, bool fulfilled)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _OrderStatus() when $default != null:
        return $default(_that.status, _that.fulfilled);
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
    TResult Function(String status, bool fulfilled) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _OrderStatus():
        return $default(_that.status, _that.fulfilled);
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
    TResult? Function(String status, bool fulfilled)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _OrderStatus() when $default != null:
        return $default(_that.status, _that.fulfilled);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _OrderStatus implements OrderStatus {
  const _OrderStatus({required this.status, this.fulfilled = false});
  factory _OrderStatus.fromJson(Map<String, dynamic> json) =>
      _$OrderStatusFromJson(json);

  @override
  final String status;
  @override
  @JsonKey()
  final bool fulfilled;

  /// Create a copy of OrderStatus
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$OrderStatusCopyWith<_OrderStatus> get copyWith =>
      __$OrderStatusCopyWithImpl<_OrderStatus>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$OrderStatusToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _OrderStatus &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.fulfilled, fulfilled) ||
                other.fulfilled == fulfilled));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, status, fulfilled);
  }

  @override
  String toString() {
    return 'OrderStatus(status: $status, fulfilled: $fulfilled)';
  }
}

/// @nodoc
abstract mixin class _$OrderStatusCopyWith<$Res>
    implements $OrderStatusCopyWith<$Res> {
  factory _$OrderStatusCopyWith(
    _OrderStatus value,
    $Res Function(_OrderStatus) _then,
  ) = __$OrderStatusCopyWithImpl;
  @override
  @useResult
  $Res call({String status, bool fulfilled});
}

/// @nodoc
class __$OrderStatusCopyWithImpl<$Res> implements _$OrderStatusCopyWith<$Res> {
  __$OrderStatusCopyWithImpl(this._self, this._then);

  final _OrderStatus _self;
  final $Res Function(_OrderStatus) _then;

  /// Create a copy of OrderStatus
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? status = null, Object? fulfilled = null}) {
    return _then(
      _OrderStatus(
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        fulfilled: null == fulfilled
            ? _self.fulfilled
            : fulfilled // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// @nodoc
mixin _$ClientSubscription {
  String get id;
  String get productName;
  String get status;
  Money? get amount;
  String? get interval;
  DateTime? get currentPeriodEnd;
  bool get cancelAtPeriodEnd;

  /// Create a copy of ClientSubscription
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ClientSubscriptionCopyWith<ClientSubscription> get copyWith =>
      _$ClientSubscriptionCopyWithImpl<ClientSubscription>(
        this as ClientSubscription,
        _$identity,
      );

  /// Serializes this ClientSubscription to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as ClientSubscription;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is ClientSubscription &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.productName, _this.productName) ||
                other.productName == _this.productName) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status) &&
            (identical(other.amount, _this.amount) ||
                other.amount == _this.amount) &&
            (identical(other.interval, _this.interval) ||
                other.interval == _this.interval) &&
            (identical(other.currentPeriodEnd, _this.currentPeriodEnd) ||
                other.currentPeriodEnd == _this.currentPeriodEnd) &&
            (identical(other.cancelAtPeriodEnd, _this.cancelAtPeriodEnd) ||
                other.cancelAtPeriodEnd == _this.cancelAtPeriodEnd));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as ClientSubscription;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.productName,
      _this.status,
      _this.amount,
      _this.interval,
      _this.currentPeriodEnd,
      _this.cancelAtPeriodEnd,
    );
  }

  @override
  String toString() {
    final _this = this as ClientSubscription;
    return 'ClientSubscription(id: ${_this.id}, productName: ${_this.productName}, status: ${_this.status}, amount: ${_this.amount}, interval: ${_this.interval}, currentPeriodEnd: ${_this.currentPeriodEnd}, cancelAtPeriodEnd: ${_this.cancelAtPeriodEnd})';
  }
}

/// @nodoc
abstract mixin class $ClientSubscriptionCopyWith<$Res> {
  factory $ClientSubscriptionCopyWith(
    ClientSubscription value,
    $Res Function(ClientSubscription) _then,
  ) = _$ClientSubscriptionCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String productName,
    String status,
    Money? amount,
    String? interval,
    DateTime? currentPeriodEnd,
    bool cancelAtPeriodEnd,
  });

  $MoneyCopyWith<$Res>? get amount;
}

/// @nodoc
class _$ClientSubscriptionCopyWithImpl<$Res>
    implements $ClientSubscriptionCopyWith<$Res> {
  _$ClientSubscriptionCopyWithImpl(this._self, this._then);

  final ClientSubscription _self;
  final $Res Function(ClientSubscription) _then;

  /// Create a copy of ClientSubscription
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? productName = null,
    Object? status = null,
    Object? amount = freezed,
    Object? interval = freezed,
    Object? currentPeriodEnd = freezed,
    Object? cancelAtPeriodEnd = null,
  }) {
    return _then(
      ClientSubscription(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        productName: null == productName
            ? _self.productName
            : productName // ignore: cast_nullable_to_non_nullable
                  as String,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        amount: freezed == amount
            ? _self.amount
            : amount // ignore: cast_nullable_to_non_nullable
                  as Money?,
        interval: freezed == interval
            ? _self.interval
            : interval // ignore: cast_nullable_to_non_nullable
                  as String?,
        currentPeriodEnd: freezed == currentPeriodEnd
            ? _self.currentPeriodEnd
            : currentPeriodEnd // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        cancelAtPeriodEnd: null == cancelAtPeriodEnd
            ? _self.cancelAtPeriodEnd
            : cancelAtPeriodEnd // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }

  /// Create a copy of ClientSubscription
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MoneyCopyWith<$Res>? get amount {
    if (_self.amount == null) {
      return null;
    }

    return $MoneyCopyWith<$Res>(_self.amount!, (value) {
      return _then(_self.copyWith(amount: value));
    });
  }
}

/// Adds pattern-matching-related methods to [ClientSubscription].
extension ClientSubscriptionPatterns on ClientSubscription {
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
    TResult Function(_ClientSubscription value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ClientSubscription() when $default != null:
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
    TResult Function(_ClientSubscription value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ClientSubscription():
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
    TResult? Function(_ClientSubscription value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ClientSubscription() when $default != null:
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
      String productName,
      String status,
      Money? amount,
      String? interval,
      DateTime? currentPeriodEnd,
      bool cancelAtPeriodEnd,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ClientSubscription() when $default != null:
        return $default(
          _that.id,
          _that.productName,
          _that.status,
          _that.amount,
          _that.interval,
          _that.currentPeriodEnd,
          _that.cancelAtPeriodEnd,
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
      String productName,
      String status,
      Money? amount,
      String? interval,
      DateTime? currentPeriodEnd,
      bool cancelAtPeriodEnd,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ClientSubscription():
        return $default(
          _that.id,
          _that.productName,
          _that.status,
          _that.amount,
          _that.interval,
          _that.currentPeriodEnd,
          _that.cancelAtPeriodEnd,
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
      String productName,
      String status,
      Money? amount,
      String? interval,
      DateTime? currentPeriodEnd,
      bool cancelAtPeriodEnd,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ClientSubscription() when $default != null:
        return $default(
          _that.id,
          _that.productName,
          _that.status,
          _that.amount,
          _that.interval,
          _that.currentPeriodEnd,
          _that.cancelAtPeriodEnd,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _ClientSubscription implements ClientSubscription {
  const _ClientSubscription({
    required this.id,
    required this.productName,
    required this.status,
    this.amount,
    this.interval,
    this.currentPeriodEnd,
    this.cancelAtPeriodEnd = false,
  });
  factory _ClientSubscription.fromJson(Map<String, dynamic> json) =>
      _$ClientSubscriptionFromJson(json);

  @override
  final String id;
  @override
  final String productName;
  @override
  final String status;
  @override
  final Money? amount;
  @override
  final String? interval;
  @override
  final DateTime? currentPeriodEnd;
  @override
  @JsonKey()
  final bool cancelAtPeriodEnd;

  /// Create a copy of ClientSubscription
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ClientSubscriptionCopyWith<_ClientSubscription> get copyWith =>
      __$ClientSubscriptionCopyWithImpl<_ClientSubscription>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ClientSubscriptionToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _ClientSubscription &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.productName, productName) ||
                other.productName == productName) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.amount, amount) || other.amount == amount) &&
            (identical(other.interval, interval) ||
                other.interval == interval) &&
            (identical(other.currentPeriodEnd, currentPeriodEnd) ||
                other.currentPeriodEnd == currentPeriodEnd) &&
            (identical(other.cancelAtPeriodEnd, cancelAtPeriodEnd) ||
                other.cancelAtPeriodEnd == cancelAtPeriodEnd));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      productName,
      status,
      amount,
      interval,
      currentPeriodEnd,
      cancelAtPeriodEnd,
    );
  }

  @override
  String toString() {
    return 'ClientSubscription(id: $id, productName: $productName, status: $status, amount: $amount, interval: $interval, currentPeriodEnd: $currentPeriodEnd, cancelAtPeriodEnd: $cancelAtPeriodEnd)';
  }
}

/// @nodoc
abstract mixin class _$ClientSubscriptionCopyWith<$Res>
    implements $ClientSubscriptionCopyWith<$Res> {
  factory _$ClientSubscriptionCopyWith(
    _ClientSubscription value,
    $Res Function(_ClientSubscription) _then,
  ) = __$ClientSubscriptionCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String productName,
    String status,
    Money? amount,
    String? interval,
    DateTime? currentPeriodEnd,
    bool cancelAtPeriodEnd,
  });

  @override
  $MoneyCopyWith<$Res>? get amount;
}

/// @nodoc
class __$ClientSubscriptionCopyWithImpl<$Res>
    implements _$ClientSubscriptionCopyWith<$Res> {
  __$ClientSubscriptionCopyWithImpl(this._self, this._then);

  final _ClientSubscription _self;
  final $Res Function(_ClientSubscription) _then;

  /// Create a copy of ClientSubscription
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? productName = null,
    Object? status = null,
    Object? amount = freezed,
    Object? interval = freezed,
    Object? currentPeriodEnd = freezed,
    Object? cancelAtPeriodEnd = null,
  }) {
    return _then(
      _ClientSubscription(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        productName: null == productName
            ? _self.productName
            : productName // ignore: cast_nullable_to_non_nullable
                  as String,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        amount: freezed == amount
            ? _self.amount
            : amount // ignore: cast_nullable_to_non_nullable
                  as Money?,
        interval: freezed == interval
            ? _self.interval
            : interval // ignore: cast_nullable_to_non_nullable
                  as String?,
        currentPeriodEnd: freezed == currentPeriodEnd
            ? _self.currentPeriodEnd
            : currentPeriodEnd // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        cancelAtPeriodEnd: null == cancelAtPeriodEnd
            ? _self.cancelAtPeriodEnd
            : cancelAtPeriodEnd // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }

  /// Create a copy of ClientSubscription
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MoneyCopyWith<$Res>? get amount {
    if (_self.amount == null) {
      return null;
    }

    return $MoneyCopyWith<$Res>(_self.amount!, (value) {
      return _then(_self.copyWith(amount: value));
    });
  }
}

/// @nodoc
mixin _$Order {
  String get id;
  String? get number;
  String? get status;
  Money? get total;
  String? get productName;
  DateTime? get createdAt;

  /// Create a copy of Order
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $OrderCopyWith<Order> get copyWith =>
      _$OrderCopyWithImpl<Order>(this as Order, _$identity);

  /// Serializes this Order to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Order;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Order &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.number, _this.number) ||
                other.number == _this.number) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status) &&
            (identical(other.total, _this.total) ||
                other.total == _this.total) &&
            (identical(other.productName, _this.productName) ||
                other.productName == _this.productName) &&
            (identical(other.createdAt, _this.createdAt) ||
                other.createdAt == _this.createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Order;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.number,
      _this.status,
      _this.total,
      _this.productName,
      _this.createdAt,
    );
  }

  @override
  String toString() {
    final _this = this as Order;
    return 'Order(id: ${_this.id}, number: ${_this.number}, status: ${_this.status}, total: ${_this.total}, productName: ${_this.productName}, createdAt: ${_this.createdAt})';
  }
}

/// @nodoc
abstract mixin class $OrderCopyWith<$Res> {
  factory $OrderCopyWith(Order value, $Res Function(Order) _then) =
      _$OrderCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String? number,
    String? status,
    Money? total,
    String? productName,
    DateTime? createdAt,
  });

  $MoneyCopyWith<$Res>? get total;
}

/// @nodoc
class _$OrderCopyWithImpl<$Res> implements $OrderCopyWith<$Res> {
  _$OrderCopyWithImpl(this._self, this._then);

  final Order _self;
  final $Res Function(Order) _then;

  /// Create a copy of Order
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? number = freezed,
    Object? status = freezed,
    Object? total = freezed,
    Object? productName = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      Order(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        number: freezed == number
            ? _self.number
            : number // ignore: cast_nullable_to_non_nullable
                  as String?,
        status: freezed == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String?,
        total: freezed == total
            ? _self.total
            : total // ignore: cast_nullable_to_non_nullable
                  as Money?,
        productName: freezed == productName
            ? _self.productName
            : productName // ignore: cast_nullable_to_non_nullable
                  as String?,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of Order
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MoneyCopyWith<$Res>? get total {
    if (_self.total == null) {
      return null;
    }

    return $MoneyCopyWith<$Res>(_self.total!, (value) {
      return _then(_self.copyWith(total: value));
    });
  }
}

/// Adds pattern-matching-related methods to [Order].
extension OrderPatterns on Order {
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
    TResult Function(_Order value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Order() when $default != null:
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
    TResult Function(_Order value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Order():
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
    TResult? Function(_Order value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Order() when $default != null:
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
      String? number,
      String? status,
      Money? total,
      String? productName,
      DateTime? createdAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Order() when $default != null:
        return $default(
          _that.id,
          _that.number,
          _that.status,
          _that.total,
          _that.productName,
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
      String? number,
      String? status,
      Money? total,
      String? productName,
      DateTime? createdAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Order():
        return $default(
          _that.id,
          _that.number,
          _that.status,
          _that.total,
          _that.productName,
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
      String? number,
      String? status,
      Money? total,
      String? productName,
      DateTime? createdAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Order() when $default != null:
        return $default(
          _that.id,
          _that.number,
          _that.status,
          _that.total,
          _that.productName,
          _that.createdAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Order implements Order {
  const _Order({
    required this.id,
    this.number,
    this.status,
    this.total,
    this.productName,
    this.createdAt,
  });
  factory _Order.fromJson(Map<String, dynamic> json) => _$OrderFromJson(json);

  @override
  final String id;
  @override
  final String? number;
  @override
  final String? status;
  @override
  final Money? total;
  @override
  final String? productName;
  @override
  final DateTime? createdAt;

  /// Create a copy of Order
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$OrderCopyWith<_Order> get copyWith =>
      __$OrderCopyWithImpl<_Order>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$OrderToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Order &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.number, number) || other.number == number) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.total, total) || other.total == total) &&
            (identical(other.productName, productName) ||
                other.productName == productName) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      number,
      status,
      total,
      productName,
      createdAt,
    );
  }

  @override
  String toString() {
    return 'Order(id: $id, number: $number, status: $status, total: $total, productName: $productName, createdAt: $createdAt)';
  }
}

/// @nodoc
abstract mixin class _$OrderCopyWith<$Res> implements $OrderCopyWith<$Res> {
  factory _$OrderCopyWith(_Order value, $Res Function(_Order) _then) =
      __$OrderCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String? number,
    String? status,
    Money? total,
    String? productName,
    DateTime? createdAt,
  });

  @override
  $MoneyCopyWith<$Res>? get total;
}

/// @nodoc
class __$OrderCopyWithImpl<$Res> implements _$OrderCopyWith<$Res> {
  __$OrderCopyWithImpl(this._self, this._then);

  final _Order _self;
  final $Res Function(_Order) _then;

  /// Create a copy of Order
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? number = freezed,
    Object? status = freezed,
    Object? total = freezed,
    Object? productName = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _Order(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        number: freezed == number
            ? _self.number
            : number // ignore: cast_nullable_to_non_nullable
                  as String?,
        status: freezed == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String?,
        total: freezed == total
            ? _self.total
            : total // ignore: cast_nullable_to_non_nullable
                  as Money?,
        productName: freezed == productName
            ? _self.productName
            : productName // ignore: cast_nullable_to_non_nullable
                  as String?,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of Order
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MoneyCopyWith<$Res>? get total {
    if (_self.total == null) {
      return null;
    }

    return $MoneyCopyWith<$Res>(_self.total!, (value) {
      return _then(_self.copyWith(total: value));
    });
  }
}

/// @nodoc
mixin _$Purchases {
  List<Order> get orders;
  List<ClientSubscription> get subscriptions;
  Credits? get credits;

  /// Create a copy of Purchases
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $PurchasesCopyWith<Purchases> get copyWith =>
      _$PurchasesCopyWithImpl<Purchases>(this as Purchases, _$identity);

  /// Serializes this Purchases to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Purchases;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Purchases &&
            const DeepCollectionEquality().equals(other.orders, _this.orders) &&
            const DeepCollectionEquality().equals(
              other.subscriptions,
              _this.subscriptions,
            ) &&
            (identical(other.credits, _this.credits) ||
                other.credits == _this.credits));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Purchases;
    return Object.hash(
      runtimeType,
      const DeepCollectionEquality().hash(_this.orders),
      const DeepCollectionEquality().hash(_this.subscriptions),
      _this.credits,
    );
  }

  @override
  String toString() {
    final _this = this as Purchases;
    return 'Purchases(orders: ${_this.orders}, subscriptions: ${_this.subscriptions}, credits: ${_this.credits})';
  }
}

/// @nodoc
abstract mixin class $PurchasesCopyWith<$Res> {
  factory $PurchasesCopyWith(Purchases value, $Res Function(Purchases) _then) =
      _$PurchasesCopyWithImpl;
  @useResult
  $Res call({
    List<Order> orders,
    List<ClientSubscription> subscriptions,
    Credits? credits,
  });

  $CreditsCopyWith<$Res>? get credits;
}

/// @nodoc
class _$PurchasesCopyWithImpl<$Res> implements $PurchasesCopyWith<$Res> {
  _$PurchasesCopyWithImpl(this._self, this._then);

  final Purchases _self;
  final $Res Function(Purchases) _then;

  /// Create a copy of Purchases
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? orders = null,
    Object? subscriptions = null,
    Object? credits = freezed,
  }) {
    return _then(
      Purchases(
        orders: null == orders
            ? _self.orders
            : orders // ignore: cast_nullable_to_non_nullable
                  as List<Order>,
        subscriptions: null == subscriptions
            ? _self.subscriptions
            : subscriptions // ignore: cast_nullable_to_non_nullable
                  as List<ClientSubscription>,
        credits: freezed == credits
            ? _self.credits
            : credits // ignore: cast_nullable_to_non_nullable
                  as Credits?,
      ),
    );
  }

  /// Create a copy of Purchases
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $CreditsCopyWith<$Res>? get credits {
    if (_self.credits == null) {
      return null;
    }

    return $CreditsCopyWith<$Res>(_self.credits!, (value) {
      return _then(_self.copyWith(credits: value));
    });
  }
}

/// Adds pattern-matching-related methods to [Purchases].
extension PurchasesPatterns on Purchases {
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
    TResult Function(_Purchases value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Purchases() when $default != null:
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
    TResult Function(_Purchases value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Purchases():
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
    TResult? Function(_Purchases value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Purchases() when $default != null:
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
      List<Order> orders,
      List<ClientSubscription> subscriptions,
      Credits? credits,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Purchases() when $default != null:
        return $default(_that.orders, _that.subscriptions, _that.credits);
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
      List<Order> orders,
      List<ClientSubscription> subscriptions,
      Credits? credits,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Purchases():
        return $default(_that.orders, _that.subscriptions, _that.credits);
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
      List<Order> orders,
      List<ClientSubscription> subscriptions,
      Credits? credits,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Purchases() when $default != null:
        return $default(_that.orders, _that.subscriptions, _that.credits);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Purchases implements Purchases {
  const _Purchases({
    List<Order> orders = const <Order>[],
    List<ClientSubscription> subscriptions = const <ClientSubscription>[],
    this.credits,
  }) : _orders = orders,
       _subscriptions = subscriptions;
  factory _Purchases.fromJson(Map<String, dynamic> json) =>
      _$PurchasesFromJson(json);

  final List<Order> _orders;
  @override
  @JsonKey()
  List<Order> get orders {
    if (_orders is EqualUnmodifiableListView) return _orders;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_orders);
  }

  final List<ClientSubscription> _subscriptions;
  @override
  @JsonKey()
  List<ClientSubscription> get subscriptions {
    if (_subscriptions is EqualUnmodifiableListView) return _subscriptions;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_subscriptions);
  }

  @override
  final Credits? credits;

  /// Create a copy of Purchases
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$PurchasesCopyWith<_Purchases> get copyWith =>
      __$PurchasesCopyWithImpl<_Purchases>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$PurchasesToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Purchases &&
            const DeepCollectionEquality().equals(other.orders, _orders) &&
            const DeepCollectionEquality().equals(
              other.subscriptions,
              _subscriptions,
            ) &&
            (identical(other.credits, credits) || other.credits == credits));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      const DeepCollectionEquality().hash(_orders),
      const DeepCollectionEquality().hash(_subscriptions),
      credits,
    );
  }

  @override
  String toString() {
    return 'Purchases(orders: $orders, subscriptions: $subscriptions, credits: $credits)';
  }
}

/// @nodoc
abstract mixin class _$PurchasesCopyWith<$Res>
    implements $PurchasesCopyWith<$Res> {
  factory _$PurchasesCopyWith(
    _Purchases value,
    $Res Function(_Purchases) _then,
  ) = __$PurchasesCopyWithImpl;
  @override
  @useResult
  $Res call({
    List<Order> orders,
    List<ClientSubscription> subscriptions,
    Credits? credits,
  });

  @override
  $CreditsCopyWith<$Res>? get credits;
}

/// @nodoc
class __$PurchasesCopyWithImpl<$Res> implements _$PurchasesCopyWith<$Res> {
  __$PurchasesCopyWithImpl(this._self, this._then);

  final _Purchases _self;
  final $Res Function(_Purchases) _then;

  /// Create a copy of Purchases
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? orders = null,
    Object? subscriptions = null,
    Object? credits = freezed,
  }) {
    return _then(
      _Purchases(
        orders: null == orders
            ? _self._orders
            : orders // ignore: cast_nullable_to_non_nullable
                  as List<Order>,
        subscriptions: null == subscriptions
            ? _self._subscriptions
            : subscriptions // ignore: cast_nullable_to_non_nullable
                  as List<ClientSubscription>,
        credits: freezed == credits
            ? _self.credits
            : credits // ignore: cast_nullable_to_non_nullable
                  as Credits?,
      ),
    );
  }

  /// Create a copy of Purchases
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $CreditsCopyWith<$Res>? get credits {
    if (_self.credits == null) {
      return null;
    }

    return $CreditsCopyWith<$Res>(_self.credits!, (value) {
      return _then(_self.copyWith(credits: value));
    });
  }
}
