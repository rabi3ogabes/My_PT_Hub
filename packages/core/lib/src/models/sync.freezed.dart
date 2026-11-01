// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'sync.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$SyncMutation {
  String get id;
  String get entity;
  String get op;
  Map<String, dynamic> get payload;
  DateTime get clientTime;

  /// Create a copy of SyncMutation
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $SyncMutationCopyWith<SyncMutation> get copyWith =>
      _$SyncMutationCopyWithImpl<SyncMutation>(
        this as SyncMutation,
        _$identity,
      );

  /// Serializes this SyncMutation to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as SyncMutation;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is SyncMutation &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.entity, _this.entity) ||
                other.entity == _this.entity) &&
            (identical(other.op, _this.op) || other.op == _this.op) &&
            const DeepCollectionEquality().equals(
              other.payload,
              _this.payload,
            ) &&
            (identical(other.clientTime, _this.clientTime) ||
                other.clientTime == _this.clientTime));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as SyncMutation;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.entity,
      _this.op,
      const DeepCollectionEquality().hash(_this.payload),
      _this.clientTime,
    );
  }

  @override
  String toString() {
    final _this = this as SyncMutation;
    return 'SyncMutation(id: ${_this.id}, entity: ${_this.entity}, op: ${_this.op}, payload: ${_this.payload}, clientTime: ${_this.clientTime})';
  }
}

/// @nodoc
abstract mixin class $SyncMutationCopyWith<$Res> {
  factory $SyncMutationCopyWith(
    SyncMutation value,
    $Res Function(SyncMutation) _then,
  ) = _$SyncMutationCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String entity,
    String op,
    Map<String, dynamic> payload,
    DateTime clientTime,
  });
}

/// @nodoc
class _$SyncMutationCopyWithImpl<$Res> implements $SyncMutationCopyWith<$Res> {
  _$SyncMutationCopyWithImpl(this._self, this._then);

  final SyncMutation _self;
  final $Res Function(SyncMutation) _then;

  /// Create a copy of SyncMutation
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? entity = null,
    Object? op = null,
    Object? payload = null,
    Object? clientTime = null,
  }) {
    return _then(
      SyncMutation(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        entity: null == entity
            ? _self.entity
            : entity // ignore: cast_nullable_to_non_nullable
                  as String,
        op: null == op
            ? _self.op
            : op // ignore: cast_nullable_to_non_nullable
                  as String,
        payload: null == payload
            ? _self.payload
            : payload // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>,
        clientTime: null == clientTime
            ? _self.clientTime
            : clientTime // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [SyncMutation].
extension SyncMutationPatterns on SyncMutation {
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
    TResult Function(_SyncMutation value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SyncMutation() when $default != null:
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
    TResult Function(_SyncMutation value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncMutation():
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
    TResult? Function(_SyncMutation value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncMutation() when $default != null:
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
      String entity,
      String op,
      Map<String, dynamic> payload,
      DateTime clientTime,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SyncMutation() when $default != null:
        return $default(
          _that.id,
          _that.entity,
          _that.op,
          _that.payload,
          _that.clientTime,
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
      String entity,
      String op,
      Map<String, dynamic> payload,
      DateTime clientTime,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncMutation():
        return $default(
          _that.id,
          _that.entity,
          _that.op,
          _that.payload,
          _that.clientTime,
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
      String entity,
      String op,
      Map<String, dynamic> payload,
      DateTime clientTime,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncMutation() when $default != null:
        return $default(
          _that.id,
          _that.entity,
          _that.op,
          _that.payload,
          _that.clientTime,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _SyncMutation implements SyncMutation {
  const _SyncMutation({
    required this.id,
    required this.entity,
    required this.op,
    required Map<String, dynamic> payload,
    required this.clientTime,
  }) : _payload = payload;
  factory _SyncMutation.fromJson(Map<String, dynamic> json) =>
      _$SyncMutationFromJson(json);

  @override
  final String id;
  @override
  final String entity;
  @override
  final String op;
  final Map<String, dynamic> _payload;
  @override
  Map<String, dynamic> get payload {
    if (_payload is EqualUnmodifiableMapView) return _payload;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(_payload);
  }

  @override
  final DateTime clientTime;

  /// Create a copy of SyncMutation
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$SyncMutationCopyWith<_SyncMutation> get copyWith =>
      __$SyncMutationCopyWithImpl<_SyncMutation>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$SyncMutationToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _SyncMutation &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.entity, entity) || other.entity == entity) &&
            (identical(other.op, op) || other.op == op) &&
            const DeepCollectionEquality().equals(other.payload, _payload) &&
            (identical(other.clientTime, clientTime) ||
                other.clientTime == clientTime));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      entity,
      op,
      const DeepCollectionEquality().hash(_payload),
      clientTime,
    );
  }

  @override
  String toString() {
    return 'SyncMutation(id: $id, entity: $entity, op: $op, payload: $payload, clientTime: $clientTime)';
  }
}

/// @nodoc
abstract mixin class _$SyncMutationCopyWith<$Res>
    implements $SyncMutationCopyWith<$Res> {
  factory _$SyncMutationCopyWith(
    _SyncMutation value,
    $Res Function(_SyncMutation) _then,
  ) = __$SyncMutationCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String entity,
    String op,
    Map<String, dynamic> payload,
    DateTime clientTime,
  });
}

/// @nodoc
class __$SyncMutationCopyWithImpl<$Res>
    implements _$SyncMutationCopyWith<$Res> {
  __$SyncMutationCopyWithImpl(this._self, this._then);

  final _SyncMutation _self;
  final $Res Function(_SyncMutation) _then;

  /// Create a copy of SyncMutation
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? entity = null,
    Object? op = null,
    Object? payload = null,
    Object? clientTime = null,
  }) {
    return _then(
      _SyncMutation(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        entity: null == entity
            ? _self.entity
            : entity // ignore: cast_nullable_to_non_nullable
                  as String,
        op: null == op
            ? _self.op
            : op // ignore: cast_nullable_to_non_nullable
                  as String,
        payload: null == payload
            ? _self._payload
            : payload // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>,
        clientTime: null == clientTime
            ? _self.clientTime
            : clientTime // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// @nodoc
mixin _$SyncPushResultItem {
  String get id;
  @JsonKey(unknownEnumValue: SyncResultStatus.rejected)
  SyncResultStatus get status;
  String? get error;

  /// Create a copy of SyncPushResultItem
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $SyncPushResultItemCopyWith<SyncPushResultItem> get copyWith =>
      _$SyncPushResultItemCopyWithImpl<SyncPushResultItem>(
        this as SyncPushResultItem,
        _$identity,
      );

  /// Serializes this SyncPushResultItem to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as SyncPushResultItem;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is SyncPushResultItem &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status) &&
            (identical(other.error, _this.error) ||
                other.error == _this.error));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as SyncPushResultItem;
    return Object.hash(runtimeType, _this.id, _this.status, _this.error);
  }

  @override
  String toString() {
    final _this = this as SyncPushResultItem;
    return 'SyncPushResultItem(id: ${_this.id}, status: ${_this.status}, error: ${_this.error})';
  }
}

/// @nodoc
abstract mixin class $SyncPushResultItemCopyWith<$Res> {
  factory $SyncPushResultItemCopyWith(
    SyncPushResultItem value,
    $Res Function(SyncPushResultItem) _then,
  ) = _$SyncPushResultItemCopyWithImpl;
  @useResult
  $Res call({
    String id,
    @JsonKey(unknownEnumValue: SyncResultStatus.rejected)
    SyncResultStatus status,
    String? error,
  });
}

/// @nodoc
class _$SyncPushResultItemCopyWithImpl<$Res>
    implements $SyncPushResultItemCopyWith<$Res> {
  _$SyncPushResultItemCopyWithImpl(this._self, this._then);

  final SyncPushResultItem _self;
  final $Res Function(SyncPushResultItem) _then;

  /// Create a copy of SyncPushResultItem
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? status = null,
    Object? error = freezed,
  }) {
    return _then(
      SyncPushResultItem(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as SyncResultStatus,
        error: freezed == error
            ? _self.error
            : error // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [SyncPushResultItem].
extension SyncPushResultItemPatterns on SyncPushResultItem {
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
    TResult Function(_SyncPushResultItem value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SyncPushResultItem() when $default != null:
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
    TResult Function(_SyncPushResultItem value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPushResultItem():
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
    TResult? Function(_SyncPushResultItem value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPushResultItem() when $default != null:
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
      @JsonKey(unknownEnumValue: SyncResultStatus.rejected)
      SyncResultStatus status,
      String? error,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SyncPushResultItem() when $default != null:
        return $default(_that.id, _that.status, _that.error);
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
      @JsonKey(unknownEnumValue: SyncResultStatus.rejected)
      SyncResultStatus status,
      String? error,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPushResultItem():
        return $default(_that.id, _that.status, _that.error);
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
      @JsonKey(unknownEnumValue: SyncResultStatus.rejected)
      SyncResultStatus status,
      String? error,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPushResultItem() when $default != null:
        return $default(_that.id, _that.status, _that.error);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _SyncPushResultItem implements SyncPushResultItem {
  const _SyncPushResultItem({
    required this.id,
    @JsonKey(unknownEnumValue: SyncResultStatus.rejected) required this.status,
    this.error,
  });
  factory _SyncPushResultItem.fromJson(Map<String, dynamic> json) =>
      _$SyncPushResultItemFromJson(json);

  @override
  final String id;
  @override
  @JsonKey(unknownEnumValue: SyncResultStatus.rejected)
  final SyncResultStatus status;
  @override
  final String? error;

  /// Create a copy of SyncPushResultItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$SyncPushResultItemCopyWith<_SyncPushResultItem> get copyWith =>
      __$SyncPushResultItemCopyWithImpl<_SyncPushResultItem>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$SyncPushResultItemToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _SyncPushResultItem &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.error, error) || other.error == error));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, status, error);
  }

  @override
  String toString() {
    return 'SyncPushResultItem(id: $id, status: $status, error: $error)';
  }
}

/// @nodoc
abstract mixin class _$SyncPushResultItemCopyWith<$Res>
    implements $SyncPushResultItemCopyWith<$Res> {
  factory _$SyncPushResultItemCopyWith(
    _SyncPushResultItem value,
    $Res Function(_SyncPushResultItem) _then,
  ) = __$SyncPushResultItemCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    @JsonKey(unknownEnumValue: SyncResultStatus.rejected)
    SyncResultStatus status,
    String? error,
  });
}

/// @nodoc
class __$SyncPushResultItemCopyWithImpl<$Res>
    implements _$SyncPushResultItemCopyWith<$Res> {
  __$SyncPushResultItemCopyWithImpl(this._self, this._then);

  final _SyncPushResultItem _self;
  final $Res Function(_SyncPushResultItem) _then;

  /// Create a copy of SyncPushResultItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? status = null,
    Object? error = freezed,
  }) {
    return _then(
      _SyncPushResultItem(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as SyncResultStatus,
        error: freezed == error
            ? _self.error
            : error // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$SyncPushResponse {
  List<SyncPushResultItem> get results;
  DateTime? get serverTime;

  /// Create a copy of SyncPushResponse
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $SyncPushResponseCopyWith<SyncPushResponse> get copyWith =>
      _$SyncPushResponseCopyWithImpl<SyncPushResponse>(
        this as SyncPushResponse,
        _$identity,
      );

  /// Serializes this SyncPushResponse to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as SyncPushResponse;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is SyncPushResponse &&
            const DeepCollectionEquality().equals(
              other.results,
              _this.results,
            ) &&
            (identical(other.serverTime, _this.serverTime) ||
                other.serverTime == _this.serverTime));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as SyncPushResponse;
    return Object.hash(
      runtimeType,
      const DeepCollectionEquality().hash(_this.results),
      _this.serverTime,
    );
  }

  @override
  String toString() {
    final _this = this as SyncPushResponse;
    return 'SyncPushResponse(results: ${_this.results}, serverTime: ${_this.serverTime})';
  }
}

/// @nodoc
abstract mixin class $SyncPushResponseCopyWith<$Res> {
  factory $SyncPushResponseCopyWith(
    SyncPushResponse value,
    $Res Function(SyncPushResponse) _then,
  ) = _$SyncPushResponseCopyWithImpl;
  @useResult
  $Res call({List<SyncPushResultItem> results, DateTime? serverTime});
}

/// @nodoc
class _$SyncPushResponseCopyWithImpl<$Res>
    implements $SyncPushResponseCopyWith<$Res> {
  _$SyncPushResponseCopyWithImpl(this._self, this._then);

  final SyncPushResponse _self;
  final $Res Function(SyncPushResponse) _then;

  /// Create a copy of SyncPushResponse
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? results = null, Object? serverTime = freezed}) {
    return _then(
      SyncPushResponse(
        results: null == results
            ? _self.results
            : results // ignore: cast_nullable_to_non_nullable
                  as List<SyncPushResultItem>,
        serverTime: freezed == serverTime
            ? _self.serverTime
            : serverTime // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [SyncPushResponse].
extension SyncPushResponsePatterns on SyncPushResponse {
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
    TResult Function(_SyncPushResponse value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SyncPushResponse() when $default != null:
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
    TResult Function(_SyncPushResponse value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPushResponse():
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
    TResult? Function(_SyncPushResponse value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPushResponse() when $default != null:
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
    TResult Function(List<SyncPushResultItem> results, DateTime? serverTime)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SyncPushResponse() when $default != null:
        return $default(_that.results, _that.serverTime);
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
    TResult Function(List<SyncPushResultItem> results, DateTime? serverTime)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPushResponse():
        return $default(_that.results, _that.serverTime);
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
    TResult? Function(List<SyncPushResultItem> results, DateTime? serverTime)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPushResponse() when $default != null:
        return $default(_that.results, _that.serverTime);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _SyncPushResponse implements SyncPushResponse {
  const _SyncPushResponse({
    List<SyncPushResultItem> results = const <SyncPushResultItem>[],
    this.serverTime,
  }) : _results = results;
  factory _SyncPushResponse.fromJson(Map<String, dynamic> json) =>
      _$SyncPushResponseFromJson(json);

  final List<SyncPushResultItem> _results;
  @override
  @JsonKey()
  List<SyncPushResultItem> get results {
    if (_results is EqualUnmodifiableListView) return _results;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_results);
  }

  @override
  final DateTime? serverTime;

  /// Create a copy of SyncPushResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$SyncPushResponseCopyWith<_SyncPushResponse> get copyWith =>
      __$SyncPushResponseCopyWithImpl<_SyncPushResponse>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$SyncPushResponseToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _SyncPushResponse &&
            const DeepCollectionEquality().equals(other.results, _results) &&
            (identical(other.serverTime, serverTime) ||
                other.serverTime == serverTime));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      const DeepCollectionEquality().hash(_results),
      serverTime,
    );
  }

  @override
  String toString() {
    return 'SyncPushResponse(results: $results, serverTime: $serverTime)';
  }
}

/// @nodoc
abstract mixin class _$SyncPushResponseCopyWith<$Res>
    implements $SyncPushResponseCopyWith<$Res> {
  factory _$SyncPushResponseCopyWith(
    _SyncPushResponse value,
    $Res Function(_SyncPushResponse) _then,
  ) = __$SyncPushResponseCopyWithImpl;
  @override
  @useResult
  $Res call({List<SyncPushResultItem> results, DateTime? serverTime});
}

/// @nodoc
class __$SyncPushResponseCopyWithImpl<$Res>
    implements _$SyncPushResponseCopyWith<$Res> {
  __$SyncPushResponseCopyWithImpl(this._self, this._then);

  final _SyncPushResponse _self;
  final $Res Function(_SyncPushResponse) _then;

  /// Create a copy of SyncPushResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? results = null, Object? serverTime = freezed}) {
    return _then(
      _SyncPushResponse(
        results: null == results
            ? _self._results
            : results // ignore: cast_nullable_to_non_nullable
                  as List<SyncPushResultItem>,
        serverTime: freezed == serverTime
            ? _self.serverTime
            : serverTime // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
mixin _$SyncPullResponse {
  DateTime get serverTime;
  Map<String, List<dynamic>> get changes;
  Map<String, List<dynamic>> get deleted;

  /// Create a copy of SyncPullResponse
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $SyncPullResponseCopyWith<SyncPullResponse> get copyWith =>
      _$SyncPullResponseCopyWithImpl<SyncPullResponse>(
        this as SyncPullResponse,
        _$identity,
      );

  /// Serializes this SyncPullResponse to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as SyncPullResponse;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is SyncPullResponse &&
            (identical(other.serverTime, _this.serverTime) ||
                other.serverTime == _this.serverTime) &&
            const DeepCollectionEquality().equals(
              other.changes,
              _this.changes,
            ) &&
            const DeepCollectionEquality().equals(
              other.deleted,
              _this.deleted,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as SyncPullResponse;
    return Object.hash(
      runtimeType,
      _this.serverTime,
      const DeepCollectionEquality().hash(_this.changes),
      const DeepCollectionEquality().hash(_this.deleted),
    );
  }

  @override
  String toString() {
    final _this = this as SyncPullResponse;
    return 'SyncPullResponse(serverTime: ${_this.serverTime}, changes: ${_this.changes}, deleted: ${_this.deleted})';
  }
}

/// @nodoc
abstract mixin class $SyncPullResponseCopyWith<$Res> {
  factory $SyncPullResponseCopyWith(
    SyncPullResponse value,
    $Res Function(SyncPullResponse) _then,
  ) = _$SyncPullResponseCopyWithImpl;
  @useResult
  $Res call({
    DateTime serverTime,
    Map<String, List<dynamic>> changes,
    Map<String, List<dynamic>> deleted,
  });
}

/// @nodoc
class _$SyncPullResponseCopyWithImpl<$Res>
    implements $SyncPullResponseCopyWith<$Res> {
  _$SyncPullResponseCopyWithImpl(this._self, this._then);

  final SyncPullResponse _self;
  final $Res Function(SyncPullResponse) _then;

  /// Create a copy of SyncPullResponse
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? serverTime = null,
    Object? changes = null,
    Object? deleted = null,
  }) {
    return _then(
      SyncPullResponse(
        serverTime: null == serverTime
            ? _self.serverTime
            : serverTime // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        changes: null == changes
            ? _self.changes
            : changes // ignore: cast_nullable_to_non_nullable
                  as Map<String, List<dynamic>>,
        deleted: null == deleted
            ? _self.deleted
            : deleted // ignore: cast_nullable_to_non_nullable
                  as Map<String, List<dynamic>>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [SyncPullResponse].
extension SyncPullResponsePatterns on SyncPullResponse {
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
    TResult Function(_SyncPullResponse value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SyncPullResponse() when $default != null:
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
    TResult Function(_SyncPullResponse value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPullResponse():
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
    TResult? Function(_SyncPullResponse value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPullResponse() when $default != null:
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
      DateTime serverTime,
      Map<String, List<dynamic>> changes,
      Map<String, List<dynamic>> deleted,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SyncPullResponse() when $default != null:
        return $default(_that.serverTime, _that.changes, _that.deleted);
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
      DateTime serverTime,
      Map<String, List<dynamic>> changes,
      Map<String, List<dynamic>> deleted,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPullResponse():
        return $default(_that.serverTime, _that.changes, _that.deleted);
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
      DateTime serverTime,
      Map<String, List<dynamic>> changes,
      Map<String, List<dynamic>> deleted,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SyncPullResponse() when $default != null:
        return $default(_that.serverTime, _that.changes, _that.deleted);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _SyncPullResponse implements SyncPullResponse {
  const _SyncPullResponse({
    required this.serverTime,
    Map<String, List<dynamic>> changes = const <String, List<dynamic>>{},
    Map<String, List<dynamic>> deleted = const <String, List<dynamic>>{},
  }) : _changes = changes,
       _deleted = deleted;
  factory _SyncPullResponse.fromJson(Map<String, dynamic> json) =>
      _$SyncPullResponseFromJson(json);

  @override
  final DateTime serverTime;
  final Map<String, List<dynamic>> _changes;
  @override
  @JsonKey()
  Map<String, List<dynamic>> get changes {
    if (_changes is EqualUnmodifiableMapView) return _changes;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(_changes);
  }

  final Map<String, List<dynamic>> _deleted;
  @override
  @JsonKey()
  Map<String, List<dynamic>> get deleted {
    if (_deleted is EqualUnmodifiableMapView) return _deleted;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(_deleted);
  }

  /// Create a copy of SyncPullResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$SyncPullResponseCopyWith<_SyncPullResponse> get copyWith =>
      __$SyncPullResponseCopyWithImpl<_SyncPullResponse>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$SyncPullResponseToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _SyncPullResponse &&
            (identical(other.serverTime, serverTime) ||
                other.serverTime == serverTime) &&
            const DeepCollectionEquality().equals(other.changes, _changes) &&
            const DeepCollectionEquality().equals(other.deleted, _deleted));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      serverTime,
      const DeepCollectionEquality().hash(_changes),
      const DeepCollectionEquality().hash(_deleted),
    );
  }

  @override
  String toString() {
    return 'SyncPullResponse(serverTime: $serverTime, changes: $changes, deleted: $deleted)';
  }
}

/// @nodoc
abstract mixin class _$SyncPullResponseCopyWith<$Res>
    implements $SyncPullResponseCopyWith<$Res> {
  factory _$SyncPullResponseCopyWith(
    _SyncPullResponse value,
    $Res Function(_SyncPullResponse) _then,
  ) = __$SyncPullResponseCopyWithImpl;
  @override
  @useResult
  $Res call({
    DateTime serverTime,
    Map<String, List<dynamic>> changes,
    Map<String, List<dynamic>> deleted,
  });
}

/// @nodoc
class __$SyncPullResponseCopyWithImpl<$Res>
    implements _$SyncPullResponseCopyWith<$Res> {
  __$SyncPullResponseCopyWithImpl(this._self, this._then);

  final _SyncPullResponse _self;
  final $Res Function(_SyncPullResponse) _then;

  /// Create a copy of SyncPullResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? serverTime = null,
    Object? changes = null,
    Object? deleted = null,
  }) {
    return _then(
      _SyncPullResponse(
        serverTime: null == serverTime
            ? _self.serverTime
            : serverTime // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        changes: null == changes
            ? _self._changes
            : changes // ignore: cast_nullable_to_non_nullable
                  as Map<String, List<dynamic>>,
        deleted: null == deleted
            ? _self._deleted
            : deleted // ignore: cast_nullable_to_non_nullable
                  as Map<String, List<dynamic>>,
      ),
    );
  }
}
