// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'notifications.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$AppNotification {
  String get id;
  String get category;
  String get title;
  String? get body;
  String? get link;
  Map<String, dynamic> get data;
  DateTime? get readAt;
  DateTime get createdAt;

  /// Create a copy of AppNotification
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $AppNotificationCopyWith<AppNotification> get copyWith =>
      _$AppNotificationCopyWithImpl<AppNotification>(
        this as AppNotification,
        _$identity,
      );

  /// Serializes this AppNotification to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as AppNotification;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is AppNotification &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.category, _this.category) ||
                other.category == _this.category) &&
            (identical(other.title, _this.title) ||
                other.title == _this.title) &&
            (identical(other.body, _this.body) || other.body == _this.body) &&
            (identical(other.link, _this.link) || other.link == _this.link) &&
            const DeepCollectionEquality().equals(other.data, _this.data) &&
            (identical(other.readAt, _this.readAt) ||
                other.readAt == _this.readAt) &&
            (identical(other.createdAt, _this.createdAt) ||
                other.createdAt == _this.createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as AppNotification;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.category,
      _this.title,
      _this.body,
      _this.link,
      const DeepCollectionEquality().hash(_this.data),
      _this.readAt,
      _this.createdAt,
    );
  }

  @override
  String toString() {
    final _this = this as AppNotification;
    return 'AppNotification(id: ${_this.id}, category: ${_this.category}, title: ${_this.title}, body: ${_this.body}, link: ${_this.link}, data: ${_this.data}, readAt: ${_this.readAt}, createdAt: ${_this.createdAt})';
  }
}

/// @nodoc
abstract mixin class $AppNotificationCopyWith<$Res> {
  factory $AppNotificationCopyWith(
    AppNotification value,
    $Res Function(AppNotification) _then,
  ) = _$AppNotificationCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String category,
    String title,
    String? body,
    String? link,
    Map<String, dynamic> data,
    DateTime? readAt,
    DateTime createdAt,
  });
}

/// @nodoc
class _$AppNotificationCopyWithImpl<$Res>
    implements $AppNotificationCopyWith<$Res> {
  _$AppNotificationCopyWithImpl(this._self, this._then);

  final AppNotification _self;
  final $Res Function(AppNotification) _then;

  /// Create a copy of AppNotification
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? category = null,
    Object? title = null,
    Object? body = freezed,
    Object? link = freezed,
    Object? data = null,
    Object? readAt = freezed,
    Object? createdAt = null,
  }) {
    return _then(
      AppNotification(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        category: null == category
            ? _self.category
            : category // ignore: cast_nullable_to_non_nullable
                  as String,
        title: null == title
            ? _self.title
            : title // ignore: cast_nullable_to_non_nullable
                  as String,
        body: freezed == body
            ? _self.body
            : body // ignore: cast_nullable_to_non_nullable
                  as String?,
        link: freezed == link
            ? _self.link
            : link // ignore: cast_nullable_to_non_nullable
                  as String?,
        data: null == data
            ? _self.data
            : data // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>,
        readAt: freezed == readAt
            ? _self.readAt
            : readAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdAt: null == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [AppNotification].
extension AppNotificationPatterns on AppNotification {
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
    TResult Function(_AppNotification value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _AppNotification() when $default != null:
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
    TResult Function(_AppNotification value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _AppNotification():
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
    TResult? Function(_AppNotification value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _AppNotification() when $default != null:
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
      String category,
      String title,
      String? body,
      String? link,
      Map<String, dynamic> data,
      DateTime? readAt,
      DateTime createdAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _AppNotification() when $default != null:
        return $default(
          _that.id,
          _that.category,
          _that.title,
          _that.body,
          _that.link,
          _that.data,
          _that.readAt,
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
      String category,
      String title,
      String? body,
      String? link,
      Map<String, dynamic> data,
      DateTime? readAt,
      DateTime createdAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _AppNotification():
        return $default(
          _that.id,
          _that.category,
          _that.title,
          _that.body,
          _that.link,
          _that.data,
          _that.readAt,
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
      String category,
      String title,
      String? body,
      String? link,
      Map<String, dynamic> data,
      DateTime? readAt,
      DateTime createdAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _AppNotification() when $default != null:
        return $default(
          _that.id,
          _that.category,
          _that.title,
          _that.body,
          _that.link,
          _that.data,
          _that.readAt,
          _that.createdAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _AppNotification extends AppNotification {
  const _AppNotification({
    required this.id,
    this.category = 'general',
    required this.title,
    this.body,
    this.link,
    Map<String, dynamic> data = const <String, dynamic>{},
    this.readAt,
    required this.createdAt,
  }) : _data = data,
       super._();
  factory _AppNotification.fromJson(Map<String, dynamic> json) =>
      _$AppNotificationFromJson(json);

  @override
  final String id;
  @override
  @JsonKey()
  final String category;
  @override
  final String title;
  @override
  final String? body;
  @override
  final String? link;
  final Map<String, dynamic> _data;
  @override
  @JsonKey()
  Map<String, dynamic> get data {
    if (_data is EqualUnmodifiableMapView) return _data;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(_data);
  }

  @override
  final DateTime? readAt;
  @override
  final DateTime createdAt;

  /// Create a copy of AppNotification
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$AppNotificationCopyWith<_AppNotification> get copyWith =>
      __$AppNotificationCopyWithImpl<_AppNotification>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$AppNotificationToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _AppNotification &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.category, category) ||
                other.category == category) &&
            (identical(other.title, title) || other.title == title) &&
            (identical(other.body, body) || other.body == body) &&
            (identical(other.link, link) || other.link == link) &&
            const DeepCollectionEquality().equals(other.data, _data) &&
            (identical(other.readAt, readAt) || other.readAt == readAt) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      category,
      title,
      body,
      link,
      const DeepCollectionEquality().hash(_data),
      readAt,
      createdAt,
    );
  }

  @override
  String toString() {
    return 'AppNotification(id: $id, category: $category, title: $title, body: $body, link: $link, data: $data, readAt: $readAt, createdAt: $createdAt)';
  }
}

/// @nodoc
abstract mixin class _$AppNotificationCopyWith<$Res>
    implements $AppNotificationCopyWith<$Res> {
  factory _$AppNotificationCopyWith(
    _AppNotification value,
    $Res Function(_AppNotification) _then,
  ) = __$AppNotificationCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String category,
    String title,
    String? body,
    String? link,
    Map<String, dynamic> data,
    DateTime? readAt,
    DateTime createdAt,
  });
}

/// @nodoc
class __$AppNotificationCopyWithImpl<$Res>
    implements _$AppNotificationCopyWith<$Res> {
  __$AppNotificationCopyWithImpl(this._self, this._then);

  final _AppNotification _self;
  final $Res Function(_AppNotification) _then;

  /// Create a copy of AppNotification
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? category = null,
    Object? title = null,
    Object? body = freezed,
    Object? link = freezed,
    Object? data = null,
    Object? readAt = freezed,
    Object? createdAt = null,
  }) {
    return _then(
      _AppNotification(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        category: null == category
            ? _self.category
            : category // ignore: cast_nullable_to_non_nullable
                  as String,
        title: null == title
            ? _self.title
            : title // ignore: cast_nullable_to_non_nullable
                  as String,
        body: freezed == body
            ? _self.body
            : body // ignore: cast_nullable_to_non_nullable
                  as String?,
        link: freezed == link
            ? _self.link
            : link // ignore: cast_nullable_to_non_nullable
                  as String?,
        data: null == data
            ? _self._data
            : data // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>,
        readAt: freezed == readAt
            ? _self.readAt
            : readAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdAt: null == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
      ),
    );
  }
}
