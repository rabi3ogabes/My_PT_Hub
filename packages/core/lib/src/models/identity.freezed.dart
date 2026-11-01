// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'identity.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$NotificationChannelPref {
  bool get push;
  bool get email;

  /// Create a copy of NotificationChannelPref
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $NotificationChannelPrefCopyWith<NotificationChannelPref> get copyWith =>
      _$NotificationChannelPrefCopyWithImpl<NotificationChannelPref>(
        this as NotificationChannelPref,
        _$identity,
      );

  /// Serializes this NotificationChannelPref to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as NotificationChannelPref;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is NotificationChannelPref &&
            (identical(other.push, _this.push) || other.push == _this.push) &&
            (identical(other.email, _this.email) ||
                other.email == _this.email));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as NotificationChannelPref;
    return Object.hash(runtimeType, _this.push, _this.email);
  }

  @override
  String toString() {
    final _this = this as NotificationChannelPref;
    return 'NotificationChannelPref(push: ${_this.push}, email: ${_this.email})';
  }
}

/// @nodoc
abstract mixin class $NotificationChannelPrefCopyWith<$Res> {
  factory $NotificationChannelPrefCopyWith(
    NotificationChannelPref value,
    $Res Function(NotificationChannelPref) _then,
  ) = _$NotificationChannelPrefCopyWithImpl;
  @useResult
  $Res call({bool push, bool email});
}

/// @nodoc
class _$NotificationChannelPrefCopyWithImpl<$Res>
    implements $NotificationChannelPrefCopyWith<$Res> {
  _$NotificationChannelPrefCopyWithImpl(this._self, this._then);

  final NotificationChannelPref _self;
  final $Res Function(NotificationChannelPref) _then;

  /// Create a copy of NotificationChannelPref
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? push = null, Object? email = null}) {
    return _then(
      NotificationChannelPref(
        push: null == push
            ? _self.push
            : push // ignore: cast_nullable_to_non_nullable
                  as bool,
        email: null == email
            ? _self.email
            : email // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [NotificationChannelPref].
extension NotificationChannelPrefPatterns on NotificationChannelPref {
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
    TResult Function(_NotificationChannelPref value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _NotificationChannelPref() when $default != null:
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
    TResult Function(_NotificationChannelPref value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _NotificationChannelPref():
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
    TResult? Function(_NotificationChannelPref value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _NotificationChannelPref() when $default != null:
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
    TResult Function(bool push, bool email)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _NotificationChannelPref() when $default != null:
        return $default(_that.push, _that.email);
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
    TResult Function(bool push, bool email) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _NotificationChannelPref():
        return $default(_that.push, _that.email);
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
    TResult? Function(bool push, bool email)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _NotificationChannelPref() when $default != null:
        return $default(_that.push, _that.email);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _NotificationChannelPref implements NotificationChannelPref {
  const _NotificationChannelPref({this.push = true, this.email = true});
  factory _NotificationChannelPref.fromJson(Map<String, dynamic> json) =>
      _$NotificationChannelPrefFromJson(json);

  @override
  @JsonKey()
  final bool push;
  @override
  @JsonKey()
  final bool email;

  /// Create a copy of NotificationChannelPref
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$NotificationChannelPrefCopyWith<_NotificationChannelPref> get copyWith =>
      __$NotificationChannelPrefCopyWithImpl<_NotificationChannelPref>(
        this,
        _$identity,
      );

  @override
  Map<String, dynamic> toJson() {
    return _$NotificationChannelPrefToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _NotificationChannelPref &&
            (identical(other.push, push) || other.push == push) &&
            (identical(other.email, email) || other.email == email));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, push, email);
  }

  @override
  String toString() {
    return 'NotificationChannelPref(push: $push, email: $email)';
  }
}

/// @nodoc
abstract mixin class _$NotificationChannelPrefCopyWith<$Res>
    implements $NotificationChannelPrefCopyWith<$Res> {
  factory _$NotificationChannelPrefCopyWith(
    _NotificationChannelPref value,
    $Res Function(_NotificationChannelPref) _then,
  ) = __$NotificationChannelPrefCopyWithImpl;
  @override
  @useResult
  $Res call({bool push, bool email});
}

/// @nodoc
class __$NotificationChannelPrefCopyWithImpl<$Res>
    implements _$NotificationChannelPrefCopyWith<$Res> {
  __$NotificationChannelPrefCopyWithImpl(this._self, this._then);

  final _NotificationChannelPref _self;
  final $Res Function(_NotificationChannelPref) _then;

  /// Create a copy of NotificationChannelPref
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? push = null, Object? email = null}) {
    return _then(
      _NotificationChannelPref(
        push: null == push
            ? _self.push
            : push // ignore: cast_nullable_to_non_nullable
                  as bool,
        email: null == email
            ? _self.email
            : email // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// @nodoc
mixin _$QuietHours {
  bool get enabled;
  String? get from;
  String? get to;

  /// Create a copy of QuietHours
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $QuietHoursCopyWith<QuietHours> get copyWith =>
      _$QuietHoursCopyWithImpl<QuietHours>(this as QuietHours, _$identity);

  /// Serializes this QuietHours to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as QuietHours;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is QuietHours &&
            (identical(other.enabled, _this.enabled) ||
                other.enabled == _this.enabled) &&
            (identical(other.from, _this.from) || other.from == _this.from) &&
            (identical(other.to, _this.to) || other.to == _this.to));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as QuietHours;
    return Object.hash(runtimeType, _this.enabled, _this.from, _this.to);
  }

  @override
  String toString() {
    final _this = this as QuietHours;
    return 'QuietHours(enabled: ${_this.enabled}, from: ${_this.from}, to: ${_this.to})';
  }
}

/// @nodoc
abstract mixin class $QuietHoursCopyWith<$Res> {
  factory $QuietHoursCopyWith(
    QuietHours value,
    $Res Function(QuietHours) _then,
  ) = _$QuietHoursCopyWithImpl;
  @useResult
  $Res call({bool enabled, String? from, String? to});
}

/// @nodoc
class _$QuietHoursCopyWithImpl<$Res> implements $QuietHoursCopyWith<$Res> {
  _$QuietHoursCopyWithImpl(this._self, this._then);

  final QuietHours _self;
  final $Res Function(QuietHours) _then;

  /// Create a copy of QuietHours
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? enabled = null,
    Object? from = freezed,
    Object? to = freezed,
  }) {
    return _then(
      QuietHours(
        enabled: null == enabled
            ? _self.enabled
            : enabled // ignore: cast_nullable_to_non_nullable
                  as bool,
        from: freezed == from
            ? _self.from
            : from // ignore: cast_nullable_to_non_nullable
                  as String?,
        to: freezed == to
            ? _self.to
            : to // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [QuietHours].
extension QuietHoursPatterns on QuietHours {
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
    TResult Function(_QuietHours value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _QuietHours() when $default != null:
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
    TResult Function(_QuietHours value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _QuietHours():
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
    TResult? Function(_QuietHours value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _QuietHours() when $default != null:
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
    TResult Function(bool enabled, String? from, String? to)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _QuietHours() when $default != null:
        return $default(_that.enabled, _that.from, _that.to);
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
    TResult Function(bool enabled, String? from, String? to) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _QuietHours():
        return $default(_that.enabled, _that.from, _that.to);
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
    TResult? Function(bool enabled, String? from, String? to)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _QuietHours() when $default != null:
        return $default(_that.enabled, _that.from, _that.to);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _QuietHours implements QuietHours {
  const _QuietHours({this.enabled = false, this.from, this.to});
  factory _QuietHours.fromJson(Map<String, dynamic> json) =>
      _$QuietHoursFromJson(json);

  @override
  @JsonKey()
  final bool enabled;
  @override
  final String? from;
  @override
  final String? to;

  /// Create a copy of QuietHours
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$QuietHoursCopyWith<_QuietHours> get copyWith =>
      __$QuietHoursCopyWithImpl<_QuietHours>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$QuietHoursToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _QuietHours &&
            (identical(other.enabled, enabled) || other.enabled == enabled) &&
            (identical(other.from, from) || other.from == from) &&
            (identical(other.to, to) || other.to == to));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, enabled, from, to);
  }

  @override
  String toString() {
    return 'QuietHours(enabled: $enabled, from: $from, to: $to)';
  }
}

/// @nodoc
abstract mixin class _$QuietHoursCopyWith<$Res>
    implements $QuietHoursCopyWith<$Res> {
  factory _$QuietHoursCopyWith(
    _QuietHours value,
    $Res Function(_QuietHours) _then,
  ) = __$QuietHoursCopyWithImpl;
  @override
  @useResult
  $Res call({bool enabled, String? from, String? to});
}

/// @nodoc
class __$QuietHoursCopyWithImpl<$Res> implements _$QuietHoursCopyWith<$Res> {
  __$QuietHoursCopyWithImpl(this._self, this._then);

  final _QuietHours _self;
  final $Res Function(_QuietHours) _then;

  /// Create a copy of QuietHours
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? enabled = null,
    Object? from = freezed,
    Object? to = freezed,
  }) {
    return _then(
      _QuietHours(
        enabled: null == enabled
            ? _self.enabled
            : enabled // ignore: cast_nullable_to_non_nullable
                  as bool,
        from: freezed == from
            ? _self.from
            : from // ignore: cast_nullable_to_non_nullable
                  as String?,
        to: freezed == to
            ? _self.to
            : to // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$User {
  String get id;
  String get name;
  String? get firstName;
  String get email;
  String? get phone;
  String? get avatarUrl;
  String get locale;
  String get timezone;
  String get units;
  String get theme;
  bool get emailVerified;
  bool get twoFactorEnabled;
  String? get currentTenantId;
  bool get isSuperAdmin;
  Map<String, NotificationChannelPref> get notificationPreferences;
  QuietHours? get quietHours;
  DateTime? get createdAt;

  /// Create a copy of User
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $UserCopyWith<User> get copyWith =>
      _$UserCopyWithImpl<User>(this as User, _$identity);

  /// Serializes this User to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as User;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is User &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.firstName, _this.firstName) ||
                other.firstName == _this.firstName) &&
            (identical(other.email, _this.email) ||
                other.email == _this.email) &&
            (identical(other.phone, _this.phone) ||
                other.phone == _this.phone) &&
            (identical(other.avatarUrl, _this.avatarUrl) ||
                other.avatarUrl == _this.avatarUrl) &&
            (identical(other.locale, _this.locale) ||
                other.locale == _this.locale) &&
            (identical(other.timezone, _this.timezone) ||
                other.timezone == _this.timezone) &&
            (identical(other.units, _this.units) ||
                other.units == _this.units) &&
            (identical(other.theme, _this.theme) ||
                other.theme == _this.theme) &&
            (identical(other.emailVerified, _this.emailVerified) ||
                other.emailVerified == _this.emailVerified) &&
            (identical(other.twoFactorEnabled, _this.twoFactorEnabled) ||
                other.twoFactorEnabled == _this.twoFactorEnabled) &&
            (identical(other.currentTenantId, _this.currentTenantId) ||
                other.currentTenantId == _this.currentTenantId) &&
            (identical(other.isSuperAdmin, _this.isSuperAdmin) ||
                other.isSuperAdmin == _this.isSuperAdmin) &&
            const DeepCollectionEquality().equals(
              other.notificationPreferences,
              _this.notificationPreferences,
            ) &&
            (identical(other.quietHours, _this.quietHours) ||
                other.quietHours == _this.quietHours) &&
            (identical(other.createdAt, _this.createdAt) ||
                other.createdAt == _this.createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as User;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.name,
      _this.firstName,
      _this.email,
      _this.phone,
      _this.avatarUrl,
      _this.locale,
      _this.timezone,
      _this.units,
      _this.theme,
      _this.emailVerified,
      _this.twoFactorEnabled,
      _this.currentTenantId,
      _this.isSuperAdmin,
      const DeepCollectionEquality().hash(_this.notificationPreferences),
      _this.quietHours,
      _this.createdAt,
    );
  }

  @override
  String toString() {
    final _this = this as User;
    return 'User(id: ${_this.id}, name: ${_this.name}, firstName: ${_this.firstName}, email: ${_this.email}, phone: ${_this.phone}, avatarUrl: ${_this.avatarUrl}, locale: ${_this.locale}, timezone: ${_this.timezone}, units: ${_this.units}, theme: ${_this.theme}, emailVerified: ${_this.emailVerified}, twoFactorEnabled: ${_this.twoFactorEnabled}, currentTenantId: ${_this.currentTenantId}, isSuperAdmin: ${_this.isSuperAdmin}, notificationPreferences: ${_this.notificationPreferences}, quietHours: ${_this.quietHours}, createdAt: ${_this.createdAt})';
  }
}

/// @nodoc
abstract mixin class $UserCopyWith<$Res> {
  factory $UserCopyWith(User value, $Res Function(User) _then) =
      _$UserCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String name,
    String? firstName,
    String email,
    String? phone,
    String? avatarUrl,
    String locale,
    String timezone,
    String units,
    String theme,
    bool emailVerified,
    bool twoFactorEnabled,
    String? currentTenantId,
    bool isSuperAdmin,
    Map<String, NotificationChannelPref> notificationPreferences,
    QuietHours? quietHours,
    DateTime? createdAt,
  });

  $QuietHoursCopyWith<$Res>? get quietHours;
}

/// @nodoc
class _$UserCopyWithImpl<$Res> implements $UserCopyWith<$Res> {
  _$UserCopyWithImpl(this._self, this._then);

  final User _self;
  final $Res Function(User) _then;

  /// Create a copy of User
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? firstName = freezed,
    Object? email = null,
    Object? phone = freezed,
    Object? avatarUrl = freezed,
    Object? locale = null,
    Object? timezone = null,
    Object? units = null,
    Object? theme = null,
    Object? emailVerified = null,
    Object? twoFactorEnabled = null,
    Object? currentTenantId = freezed,
    Object? isSuperAdmin = null,
    Object? notificationPreferences = null,
    Object? quietHours = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      User(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        firstName: freezed == firstName
            ? _self.firstName
            : firstName // ignore: cast_nullable_to_non_nullable
                  as String?,
        email: null == email
            ? _self.email
            : email // ignore: cast_nullable_to_non_nullable
                  as String,
        phone: freezed == phone
            ? _self.phone
            : phone // ignore: cast_nullable_to_non_nullable
                  as String?,
        avatarUrl: freezed == avatarUrl
            ? _self.avatarUrl
            : avatarUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        locale: null == locale
            ? _self.locale
            : locale // ignore: cast_nullable_to_non_nullable
                  as String,
        timezone: null == timezone
            ? _self.timezone
            : timezone // ignore: cast_nullable_to_non_nullable
                  as String,
        units: null == units
            ? _self.units
            : units // ignore: cast_nullable_to_non_nullable
                  as String,
        theme: null == theme
            ? _self.theme
            : theme // ignore: cast_nullable_to_non_nullable
                  as String,
        emailVerified: null == emailVerified
            ? _self.emailVerified
            : emailVerified // ignore: cast_nullable_to_non_nullable
                  as bool,
        twoFactorEnabled: null == twoFactorEnabled
            ? _self.twoFactorEnabled
            : twoFactorEnabled // ignore: cast_nullable_to_non_nullable
                  as bool,
        currentTenantId: freezed == currentTenantId
            ? _self.currentTenantId
            : currentTenantId // ignore: cast_nullable_to_non_nullable
                  as String?,
        isSuperAdmin: null == isSuperAdmin
            ? _self.isSuperAdmin
            : isSuperAdmin // ignore: cast_nullable_to_non_nullable
                  as bool,
        notificationPreferences: null == notificationPreferences
            ? _self.notificationPreferences
            : notificationPreferences // ignore: cast_nullable_to_non_nullable
                  as Map<String, NotificationChannelPref>,
        quietHours: freezed == quietHours
            ? _self.quietHours
            : quietHours // ignore: cast_nullable_to_non_nullable
                  as QuietHours?,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of User
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $QuietHoursCopyWith<$Res>? get quietHours {
    if (_self.quietHours == null) {
      return null;
    }

    return $QuietHoursCopyWith<$Res>(_self.quietHours!, (value) {
      return _then(_self.copyWith(quietHours: value));
    });
  }
}

/// Adds pattern-matching-related methods to [User].
extension UserPatterns on User {
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
    TResult Function(_User value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _User() when $default != null:
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
  TResult map<TResult extends Object?>(TResult Function(_User value) $default) {
    final _that = this;
    switch (_that) {
      case _User():
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
    TResult? Function(_User value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _User() when $default != null:
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
      String? firstName,
      String email,
      String? phone,
      String? avatarUrl,
      String locale,
      String timezone,
      String units,
      String theme,
      bool emailVerified,
      bool twoFactorEnabled,
      String? currentTenantId,
      bool isSuperAdmin,
      Map<String, NotificationChannelPref> notificationPreferences,
      QuietHours? quietHours,
      DateTime? createdAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _User() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.firstName,
          _that.email,
          _that.phone,
          _that.avatarUrl,
          _that.locale,
          _that.timezone,
          _that.units,
          _that.theme,
          _that.emailVerified,
          _that.twoFactorEnabled,
          _that.currentTenantId,
          _that.isSuperAdmin,
          _that.notificationPreferences,
          _that.quietHours,
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
      String name,
      String? firstName,
      String email,
      String? phone,
      String? avatarUrl,
      String locale,
      String timezone,
      String units,
      String theme,
      bool emailVerified,
      bool twoFactorEnabled,
      String? currentTenantId,
      bool isSuperAdmin,
      Map<String, NotificationChannelPref> notificationPreferences,
      QuietHours? quietHours,
      DateTime? createdAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _User():
        return $default(
          _that.id,
          _that.name,
          _that.firstName,
          _that.email,
          _that.phone,
          _that.avatarUrl,
          _that.locale,
          _that.timezone,
          _that.units,
          _that.theme,
          _that.emailVerified,
          _that.twoFactorEnabled,
          _that.currentTenantId,
          _that.isSuperAdmin,
          _that.notificationPreferences,
          _that.quietHours,
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
      String name,
      String? firstName,
      String email,
      String? phone,
      String? avatarUrl,
      String locale,
      String timezone,
      String units,
      String theme,
      bool emailVerified,
      bool twoFactorEnabled,
      String? currentTenantId,
      bool isSuperAdmin,
      Map<String, NotificationChannelPref> notificationPreferences,
      QuietHours? quietHours,
      DateTime? createdAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _User() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.firstName,
          _that.email,
          _that.phone,
          _that.avatarUrl,
          _that.locale,
          _that.timezone,
          _that.units,
          _that.theme,
          _that.emailVerified,
          _that.twoFactorEnabled,
          _that.currentTenantId,
          _that.isSuperAdmin,
          _that.notificationPreferences,
          _that.quietHours,
          _that.createdAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _User implements User {
  const _User({
    required this.id,
    required this.name,
    this.firstName,
    required this.email,
    this.phone,
    this.avatarUrl,
    this.locale = 'en',
    this.timezone = 'UTC',
    this.units = 'metric',
    this.theme = 'system',
    this.emailVerified = false,
    this.twoFactorEnabled = false,
    this.currentTenantId,
    this.isSuperAdmin = false,
    Map<String, NotificationChannelPref> notificationPreferences =
        const <String, NotificationChannelPref>{},
    this.quietHours,
    this.createdAt,
  }) : _notificationPreferences = notificationPreferences;
  factory _User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String? firstName;
  @override
  final String email;
  @override
  final String? phone;
  @override
  final String? avatarUrl;
  @override
  @JsonKey()
  final String locale;
  @override
  @JsonKey()
  final String timezone;
  @override
  @JsonKey()
  final String units;
  @override
  @JsonKey()
  final String theme;
  @override
  @JsonKey()
  final bool emailVerified;
  @override
  @JsonKey()
  final bool twoFactorEnabled;
  @override
  final String? currentTenantId;
  @override
  @JsonKey()
  final bool isSuperAdmin;
  final Map<String, NotificationChannelPref> _notificationPreferences;
  @override
  @JsonKey()
  Map<String, NotificationChannelPref> get notificationPreferences {
    if (_notificationPreferences is EqualUnmodifiableMapView)
      return _notificationPreferences;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(_notificationPreferences);
  }

  @override
  final QuietHours? quietHours;
  @override
  final DateTime? createdAt;

  /// Create a copy of User
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$UserCopyWith<_User> get copyWith =>
      __$UserCopyWithImpl<_User>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$UserToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _User &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.firstName, firstName) ||
                other.firstName == firstName) &&
            (identical(other.email, email) || other.email == email) &&
            (identical(other.phone, phone) || other.phone == phone) &&
            (identical(other.avatarUrl, avatarUrl) ||
                other.avatarUrl == avatarUrl) &&
            (identical(other.locale, locale) || other.locale == locale) &&
            (identical(other.timezone, timezone) ||
                other.timezone == timezone) &&
            (identical(other.units, units) || other.units == units) &&
            (identical(other.theme, theme) || other.theme == theme) &&
            (identical(other.emailVerified, emailVerified) ||
                other.emailVerified == emailVerified) &&
            (identical(other.twoFactorEnabled, twoFactorEnabled) ||
                other.twoFactorEnabled == twoFactorEnabled) &&
            (identical(other.currentTenantId, currentTenantId) ||
                other.currentTenantId == currentTenantId) &&
            (identical(other.isSuperAdmin, isSuperAdmin) ||
                other.isSuperAdmin == isSuperAdmin) &&
            const DeepCollectionEquality().equals(
              other.notificationPreferences,
              _notificationPreferences,
            ) &&
            (identical(other.quietHours, quietHours) ||
                other.quietHours == quietHours) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      name,
      firstName,
      email,
      phone,
      avatarUrl,
      locale,
      timezone,
      units,
      theme,
      emailVerified,
      twoFactorEnabled,
      currentTenantId,
      isSuperAdmin,
      const DeepCollectionEquality().hash(_notificationPreferences),
      quietHours,
      createdAt,
    );
  }

  @override
  String toString() {
    return 'User(id: $id, name: $name, firstName: $firstName, email: $email, phone: $phone, avatarUrl: $avatarUrl, locale: $locale, timezone: $timezone, units: $units, theme: $theme, emailVerified: $emailVerified, twoFactorEnabled: $twoFactorEnabled, currentTenantId: $currentTenantId, isSuperAdmin: $isSuperAdmin, notificationPreferences: $notificationPreferences, quietHours: $quietHours, createdAt: $createdAt)';
  }
}

/// @nodoc
abstract mixin class _$UserCopyWith<$Res> implements $UserCopyWith<$Res> {
  factory _$UserCopyWith(_User value, $Res Function(_User) _then) =
      __$UserCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String? firstName,
    String email,
    String? phone,
    String? avatarUrl,
    String locale,
    String timezone,
    String units,
    String theme,
    bool emailVerified,
    bool twoFactorEnabled,
    String? currentTenantId,
    bool isSuperAdmin,
    Map<String, NotificationChannelPref> notificationPreferences,
    QuietHours? quietHours,
    DateTime? createdAt,
  });

  @override
  $QuietHoursCopyWith<$Res>? get quietHours;
}

/// @nodoc
class __$UserCopyWithImpl<$Res> implements _$UserCopyWith<$Res> {
  __$UserCopyWithImpl(this._self, this._then);

  final _User _self;
  final $Res Function(_User) _then;

  /// Create a copy of User
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? firstName = freezed,
    Object? email = null,
    Object? phone = freezed,
    Object? avatarUrl = freezed,
    Object? locale = null,
    Object? timezone = null,
    Object? units = null,
    Object? theme = null,
    Object? emailVerified = null,
    Object? twoFactorEnabled = null,
    Object? currentTenantId = freezed,
    Object? isSuperAdmin = null,
    Object? notificationPreferences = null,
    Object? quietHours = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(
      _User(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        firstName: freezed == firstName
            ? _self.firstName
            : firstName // ignore: cast_nullable_to_non_nullable
                  as String?,
        email: null == email
            ? _self.email
            : email // ignore: cast_nullable_to_non_nullable
                  as String,
        phone: freezed == phone
            ? _self.phone
            : phone // ignore: cast_nullable_to_non_nullable
                  as String?,
        avatarUrl: freezed == avatarUrl
            ? _self.avatarUrl
            : avatarUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        locale: null == locale
            ? _self.locale
            : locale // ignore: cast_nullable_to_non_nullable
                  as String,
        timezone: null == timezone
            ? _self.timezone
            : timezone // ignore: cast_nullable_to_non_nullable
                  as String,
        units: null == units
            ? _self.units
            : units // ignore: cast_nullable_to_non_nullable
                  as String,
        theme: null == theme
            ? _self.theme
            : theme // ignore: cast_nullable_to_non_nullable
                  as String,
        emailVerified: null == emailVerified
            ? _self.emailVerified
            : emailVerified // ignore: cast_nullable_to_non_nullable
                  as bool,
        twoFactorEnabled: null == twoFactorEnabled
            ? _self.twoFactorEnabled
            : twoFactorEnabled // ignore: cast_nullable_to_non_nullable
                  as bool,
        currentTenantId: freezed == currentTenantId
            ? _self.currentTenantId
            : currentTenantId // ignore: cast_nullable_to_non_nullable
                  as String?,
        isSuperAdmin: null == isSuperAdmin
            ? _self.isSuperAdmin
            : isSuperAdmin // ignore: cast_nullable_to_non_nullable
                  as bool,
        notificationPreferences: null == notificationPreferences
            ? _self._notificationPreferences
            : notificationPreferences // ignore: cast_nullable_to_non_nullable
                  as Map<String, NotificationChannelPref>,
        quietHours: freezed == quietHours
            ? _self.quietHours
            : quietHours // ignore: cast_nullable_to_non_nullable
                  as QuietHours?,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of User
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $QuietHoursCopyWith<$Res>? get quietHours {
    if (_self.quietHours == null) {
      return null;
    }

    return $QuietHoursCopyWith<$Res>(_self.quietHours!, (value) {
      return _then(_self.copyWith(quietHours: value));
    });
  }
}

/// @nodoc
mixin _$Tenant {
  String get id;
  String get type;
  String get name;
  String? get slug;
  String? get logoUrl;
  String? get brandColor;
  String get currency;
  String? get country;
  String? get timezone;
  String? get locale;
  String? get plan;
  DateTime? get trialEndsAt;
  bool get onboarded;
  String get role;

  /// Create a copy of Tenant
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $TenantCopyWith<Tenant> get copyWith =>
      _$TenantCopyWithImpl<Tenant>(this as Tenant, _$identity);

  /// Serializes this Tenant to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Tenant;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Tenant &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.slug, _this.slug) || other.slug == _this.slug) &&
            (identical(other.logoUrl, _this.logoUrl) ||
                other.logoUrl == _this.logoUrl) &&
            (identical(other.brandColor, _this.brandColor) ||
                other.brandColor == _this.brandColor) &&
            (identical(other.currency, _this.currency) ||
                other.currency == _this.currency) &&
            (identical(other.country, _this.country) ||
                other.country == _this.country) &&
            (identical(other.timezone, _this.timezone) ||
                other.timezone == _this.timezone) &&
            (identical(other.locale, _this.locale) ||
                other.locale == _this.locale) &&
            (identical(other.plan, _this.plan) || other.plan == _this.plan) &&
            (identical(other.trialEndsAt, _this.trialEndsAt) ||
                other.trialEndsAt == _this.trialEndsAt) &&
            (identical(other.onboarded, _this.onboarded) ||
                other.onboarded == _this.onboarded) &&
            (identical(other.role, _this.role) || other.role == _this.role));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Tenant;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.type,
      _this.name,
      _this.slug,
      _this.logoUrl,
      _this.brandColor,
      _this.currency,
      _this.country,
      _this.timezone,
      _this.locale,
      _this.plan,
      _this.trialEndsAt,
      _this.onboarded,
      _this.role,
    );
  }

  @override
  String toString() {
    final _this = this as Tenant;
    return 'Tenant(id: ${_this.id}, type: ${_this.type}, name: ${_this.name}, slug: ${_this.slug}, logoUrl: ${_this.logoUrl}, brandColor: ${_this.brandColor}, currency: ${_this.currency}, country: ${_this.country}, timezone: ${_this.timezone}, locale: ${_this.locale}, plan: ${_this.plan}, trialEndsAt: ${_this.trialEndsAt}, onboarded: ${_this.onboarded}, role: ${_this.role})';
  }
}

/// @nodoc
abstract mixin class $TenantCopyWith<$Res> {
  factory $TenantCopyWith(Tenant value, $Res Function(Tenant) _then) =
      _$TenantCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String type,
    String name,
    String? slug,
    String? logoUrl,
    String? brandColor,
    String currency,
    String? country,
    String? timezone,
    String? locale,
    String? plan,
    DateTime? trialEndsAt,
    bool onboarded,
    String role,
  });
}

/// @nodoc
class _$TenantCopyWithImpl<$Res> implements $TenantCopyWith<$Res> {
  _$TenantCopyWithImpl(this._self, this._then);

  final Tenant _self;
  final $Res Function(Tenant) _then;

  /// Create a copy of Tenant
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? name = null,
    Object? slug = freezed,
    Object? logoUrl = freezed,
    Object? brandColor = freezed,
    Object? currency = null,
    Object? country = freezed,
    Object? timezone = freezed,
    Object? locale = freezed,
    Object? plan = freezed,
    Object? trialEndsAt = freezed,
    Object? onboarded = null,
    Object? role = null,
  }) {
    return _then(
      Tenant(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        slug: freezed == slug
            ? _self.slug
            : slug // ignore: cast_nullable_to_non_nullable
                  as String?,
        logoUrl: freezed == logoUrl
            ? _self.logoUrl
            : logoUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        brandColor: freezed == brandColor
            ? _self.brandColor
            : brandColor // ignore: cast_nullable_to_non_nullable
                  as String?,
        currency: null == currency
            ? _self.currency
            : currency // ignore: cast_nullable_to_non_nullable
                  as String,
        country: freezed == country
            ? _self.country
            : country // ignore: cast_nullable_to_non_nullable
                  as String?,
        timezone: freezed == timezone
            ? _self.timezone
            : timezone // ignore: cast_nullable_to_non_nullable
                  as String?,
        locale: freezed == locale
            ? _self.locale
            : locale // ignore: cast_nullable_to_non_nullable
                  as String?,
        plan: freezed == plan
            ? _self.plan
            : plan // ignore: cast_nullable_to_non_nullable
                  as String?,
        trialEndsAt: freezed == trialEndsAt
            ? _self.trialEndsAt
            : trialEndsAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        onboarded: null == onboarded
            ? _self.onboarded
            : onboarded // ignore: cast_nullable_to_non_nullable
                  as bool,
        role: null == role
            ? _self.role
            : role // ignore: cast_nullable_to_non_nullable
                  as String,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Tenant].
extension TenantPatterns on Tenant {
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
    TResult Function(_Tenant value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Tenant() when $default != null:
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
    TResult Function(_Tenant value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Tenant():
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
    TResult? Function(_Tenant value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Tenant() when $default != null:
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
      String name,
      String? slug,
      String? logoUrl,
      String? brandColor,
      String currency,
      String? country,
      String? timezone,
      String? locale,
      String? plan,
      DateTime? trialEndsAt,
      bool onboarded,
      String role,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Tenant() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.name,
          _that.slug,
          _that.logoUrl,
          _that.brandColor,
          _that.currency,
          _that.country,
          _that.timezone,
          _that.locale,
          _that.plan,
          _that.trialEndsAt,
          _that.onboarded,
          _that.role,
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
      String name,
      String? slug,
      String? logoUrl,
      String? brandColor,
      String currency,
      String? country,
      String? timezone,
      String? locale,
      String? plan,
      DateTime? trialEndsAt,
      bool onboarded,
      String role,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Tenant():
        return $default(
          _that.id,
          _that.type,
          _that.name,
          _that.slug,
          _that.logoUrl,
          _that.brandColor,
          _that.currency,
          _that.country,
          _that.timezone,
          _that.locale,
          _that.plan,
          _that.trialEndsAt,
          _that.onboarded,
          _that.role,
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
      String name,
      String? slug,
      String? logoUrl,
      String? brandColor,
      String currency,
      String? country,
      String? timezone,
      String? locale,
      String? plan,
      DateTime? trialEndsAt,
      bool onboarded,
      String role,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Tenant() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.name,
          _that.slug,
          _that.logoUrl,
          _that.brandColor,
          _that.currency,
          _that.country,
          _that.timezone,
          _that.locale,
          _that.plan,
          _that.trialEndsAt,
          _that.onboarded,
          _that.role,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Tenant extends Tenant {
  const _Tenant({
    required this.id,
    this.type = 'business',
    required this.name,
    this.slug,
    this.logoUrl,
    this.brandColor,
    this.currency = 'USD',
    this.country,
    this.timezone,
    this.locale,
    this.plan,
    this.trialEndsAt,
    this.onboarded = false,
    this.role = 'client',
  }) : super._();
  factory _Tenant.fromJson(Map<String, dynamic> json) => _$TenantFromJson(json);

  @override
  final String id;
  @override
  @JsonKey()
  final String type;
  @override
  final String name;
  @override
  final String? slug;
  @override
  final String? logoUrl;
  @override
  final String? brandColor;
  @override
  @JsonKey()
  final String currency;
  @override
  final String? country;
  @override
  final String? timezone;
  @override
  final String? locale;
  @override
  final String? plan;
  @override
  final DateTime? trialEndsAt;
  @override
  @JsonKey()
  final bool onboarded;
  @override
  @JsonKey()
  final String role;

  /// Create a copy of Tenant
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$TenantCopyWith<_Tenant> get copyWith =>
      __$TenantCopyWithImpl<_Tenant>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$TenantToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Tenant &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.slug, slug) || other.slug == slug) &&
            (identical(other.logoUrl, logoUrl) || other.logoUrl == logoUrl) &&
            (identical(other.brandColor, brandColor) ||
                other.brandColor == brandColor) &&
            (identical(other.currency, currency) ||
                other.currency == currency) &&
            (identical(other.country, country) || other.country == country) &&
            (identical(other.timezone, timezone) ||
                other.timezone == timezone) &&
            (identical(other.locale, locale) || other.locale == locale) &&
            (identical(other.plan, plan) || other.plan == plan) &&
            (identical(other.trialEndsAt, trialEndsAt) ||
                other.trialEndsAt == trialEndsAt) &&
            (identical(other.onboarded, onboarded) ||
                other.onboarded == onboarded) &&
            (identical(other.role, role) || other.role == role));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      type,
      name,
      slug,
      logoUrl,
      brandColor,
      currency,
      country,
      timezone,
      locale,
      plan,
      trialEndsAt,
      onboarded,
      role,
    );
  }

  @override
  String toString() {
    return 'Tenant(id: $id, type: $type, name: $name, slug: $slug, logoUrl: $logoUrl, brandColor: $brandColor, currency: $currency, country: $country, timezone: $timezone, locale: $locale, plan: $plan, trialEndsAt: $trialEndsAt, onboarded: $onboarded, role: $role)';
  }
}

/// @nodoc
abstract mixin class _$TenantCopyWith<$Res> implements $TenantCopyWith<$Res> {
  factory _$TenantCopyWith(_Tenant value, $Res Function(_Tenant) _then) =
      __$TenantCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String type,
    String name,
    String? slug,
    String? logoUrl,
    String? brandColor,
    String currency,
    String? country,
    String? timezone,
    String? locale,
    String? plan,
    DateTime? trialEndsAt,
    bool onboarded,
    String role,
  });
}

/// @nodoc
class __$TenantCopyWithImpl<$Res> implements _$TenantCopyWith<$Res> {
  __$TenantCopyWithImpl(this._self, this._then);

  final _Tenant _self;
  final $Res Function(_Tenant) _then;

  /// Create a copy of Tenant
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? name = null,
    Object? slug = freezed,
    Object? logoUrl = freezed,
    Object? brandColor = freezed,
    Object? currency = null,
    Object? country = freezed,
    Object? timezone = freezed,
    Object? locale = freezed,
    Object? plan = freezed,
    Object? trialEndsAt = freezed,
    Object? onboarded = null,
    Object? role = null,
  }) {
    return _then(
      _Tenant(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        slug: freezed == slug
            ? _self.slug
            : slug // ignore: cast_nullable_to_non_nullable
                  as String?,
        logoUrl: freezed == logoUrl
            ? _self.logoUrl
            : logoUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        brandColor: freezed == brandColor
            ? _self.brandColor
            : brandColor // ignore: cast_nullable_to_non_nullable
                  as String?,
        currency: null == currency
            ? _self.currency
            : currency // ignore: cast_nullable_to_non_nullable
                  as String,
        country: freezed == country
            ? _self.country
            : country // ignore: cast_nullable_to_non_nullable
                  as String?,
        timezone: freezed == timezone
            ? _self.timezone
            : timezone // ignore: cast_nullable_to_non_nullable
                  as String?,
        locale: freezed == locale
            ? _self.locale
            : locale // ignore: cast_nullable_to_non_nullable
                  as String?,
        plan: freezed == plan
            ? _self.plan
            : plan // ignore: cast_nullable_to_non_nullable
                  as String?,
        trialEndsAt: freezed == trialEndsAt
            ? _self.trialEndsAt
            : trialEndsAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        onboarded: null == onboarded
            ? _self.onboarded
            : onboarded // ignore: cast_nullable_to_non_nullable
                  as bool,
        role: null == role
            ? _self.role
            : role // ignore: cast_nullable_to_non_nullable
                  as String,
      ),
    );
  }
}

/// @nodoc
mixin _$Tokens {
  String get accessToken;
  String get refreshToken;
  String get tokenType;
  DateTime? get expiresAt;
  DateTime? get refreshExpiresAt;

  /// Create a copy of Tokens
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $TokensCopyWith<Tokens> get copyWith =>
      _$TokensCopyWithImpl<Tokens>(this as Tokens, _$identity);

  /// Serializes this Tokens to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Tokens;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Tokens &&
            (identical(other.accessToken, _this.accessToken) ||
                other.accessToken == _this.accessToken) &&
            (identical(other.refreshToken, _this.refreshToken) ||
                other.refreshToken == _this.refreshToken) &&
            (identical(other.tokenType, _this.tokenType) ||
                other.tokenType == _this.tokenType) &&
            (identical(other.expiresAt, _this.expiresAt) ||
                other.expiresAt == _this.expiresAt) &&
            (identical(other.refreshExpiresAt, _this.refreshExpiresAt) ||
                other.refreshExpiresAt == _this.refreshExpiresAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Tokens;
    return Object.hash(
      runtimeType,
      _this.accessToken,
      _this.refreshToken,
      _this.tokenType,
      _this.expiresAt,
      _this.refreshExpiresAt,
    );
  }

  @override
  String toString() {
    final _this = this as Tokens;
    return 'Tokens(accessToken: ${_this.accessToken}, refreshToken: ${_this.refreshToken}, tokenType: ${_this.tokenType}, expiresAt: ${_this.expiresAt}, refreshExpiresAt: ${_this.refreshExpiresAt})';
  }
}

/// @nodoc
abstract mixin class $TokensCopyWith<$Res> {
  factory $TokensCopyWith(Tokens value, $Res Function(Tokens) _then) =
      _$TokensCopyWithImpl;
  @useResult
  $Res call({
    String accessToken,
    String refreshToken,
    String tokenType,
    DateTime? expiresAt,
    DateTime? refreshExpiresAt,
  });
}

/// @nodoc
class _$TokensCopyWithImpl<$Res> implements $TokensCopyWith<$Res> {
  _$TokensCopyWithImpl(this._self, this._then);

  final Tokens _self;
  final $Res Function(Tokens) _then;

  /// Create a copy of Tokens
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? accessToken = null,
    Object? refreshToken = null,
    Object? tokenType = null,
    Object? expiresAt = freezed,
    Object? refreshExpiresAt = freezed,
  }) {
    return _then(
      Tokens(
        accessToken: null == accessToken
            ? _self.accessToken
            : accessToken // ignore: cast_nullable_to_non_nullable
                  as String,
        refreshToken: null == refreshToken
            ? _self.refreshToken
            : refreshToken // ignore: cast_nullable_to_non_nullable
                  as String,
        tokenType: null == tokenType
            ? _self.tokenType
            : tokenType // ignore: cast_nullable_to_non_nullable
                  as String,
        expiresAt: freezed == expiresAt
            ? _self.expiresAt
            : expiresAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        refreshExpiresAt: freezed == refreshExpiresAt
            ? _self.refreshExpiresAt
            : refreshExpiresAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Tokens].
extension TokensPatterns on Tokens {
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
    TResult Function(_Tokens value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Tokens() when $default != null:
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
    TResult Function(_Tokens value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Tokens():
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
    TResult? Function(_Tokens value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Tokens() when $default != null:
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
      String accessToken,
      String refreshToken,
      String tokenType,
      DateTime? expiresAt,
      DateTime? refreshExpiresAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Tokens() when $default != null:
        return $default(
          _that.accessToken,
          _that.refreshToken,
          _that.tokenType,
          _that.expiresAt,
          _that.refreshExpiresAt,
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
      String accessToken,
      String refreshToken,
      String tokenType,
      DateTime? expiresAt,
      DateTime? refreshExpiresAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Tokens():
        return $default(
          _that.accessToken,
          _that.refreshToken,
          _that.tokenType,
          _that.expiresAt,
          _that.refreshExpiresAt,
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
      String accessToken,
      String refreshToken,
      String tokenType,
      DateTime? expiresAt,
      DateTime? refreshExpiresAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Tokens() when $default != null:
        return $default(
          _that.accessToken,
          _that.refreshToken,
          _that.tokenType,
          _that.expiresAt,
          _that.refreshExpiresAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Tokens implements Tokens {
  const _Tokens({
    required this.accessToken,
    required this.refreshToken,
    this.tokenType = 'Bearer',
    this.expiresAt,
    this.refreshExpiresAt,
  });
  factory _Tokens.fromJson(Map<String, dynamic> json) => _$TokensFromJson(json);

  @override
  final String accessToken;
  @override
  final String refreshToken;
  @override
  @JsonKey()
  final String tokenType;
  @override
  final DateTime? expiresAt;
  @override
  final DateTime? refreshExpiresAt;

  /// Create a copy of Tokens
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$TokensCopyWith<_Tokens> get copyWith =>
      __$TokensCopyWithImpl<_Tokens>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$TokensToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Tokens &&
            (identical(other.accessToken, accessToken) ||
                other.accessToken == accessToken) &&
            (identical(other.refreshToken, refreshToken) ||
                other.refreshToken == refreshToken) &&
            (identical(other.tokenType, tokenType) ||
                other.tokenType == tokenType) &&
            (identical(other.expiresAt, expiresAt) ||
                other.expiresAt == expiresAt) &&
            (identical(other.refreshExpiresAt, refreshExpiresAt) ||
                other.refreshExpiresAt == refreshExpiresAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      accessToken,
      refreshToken,
      tokenType,
      expiresAt,
      refreshExpiresAt,
    );
  }

  @override
  String toString() {
    return 'Tokens(accessToken: $accessToken, refreshToken: $refreshToken, tokenType: $tokenType, expiresAt: $expiresAt, refreshExpiresAt: $refreshExpiresAt)';
  }
}

/// @nodoc
abstract mixin class _$TokensCopyWith<$Res> implements $TokensCopyWith<$Res> {
  factory _$TokensCopyWith(_Tokens value, $Res Function(_Tokens) _then) =
      __$TokensCopyWithImpl;
  @override
  @useResult
  $Res call({
    String accessToken,
    String refreshToken,
    String tokenType,
    DateTime? expiresAt,
    DateTime? refreshExpiresAt,
  });
}

/// @nodoc
class __$TokensCopyWithImpl<$Res> implements _$TokensCopyWith<$Res> {
  __$TokensCopyWithImpl(this._self, this._then);

  final _Tokens _self;
  final $Res Function(_Tokens) _then;

  /// Create a copy of Tokens
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? accessToken = null,
    Object? refreshToken = null,
    Object? tokenType = null,
    Object? expiresAt = freezed,
    Object? refreshExpiresAt = freezed,
  }) {
    return _then(
      _Tokens(
        accessToken: null == accessToken
            ? _self.accessToken
            : accessToken // ignore: cast_nullable_to_non_nullable
                  as String,
        refreshToken: null == refreshToken
            ? _self.refreshToken
            : refreshToken // ignore: cast_nullable_to_non_nullable
                  as String,
        tokenType: null == tokenType
            ? _self.tokenType
            : tokenType // ignore: cast_nullable_to_non_nullable
                  as String,
        expiresAt: freezed == expiresAt
            ? _self.expiresAt
            : expiresAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        refreshExpiresAt: freezed == refreshExpiresAt
            ? _self.refreshExpiresAt
            : refreshExpiresAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
mixin _$AuthSession {
  String get accessToken;
  String get refreshToken;
  String get tokenType;
  DateTime? get expiresAt;
  DateTime? get refreshExpiresAt;
  User get user;
  List<Tenant> get tenants;

  /// Create a copy of AuthSession
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $AuthSessionCopyWith<AuthSession> get copyWith =>
      _$AuthSessionCopyWithImpl<AuthSession>(this as AuthSession, _$identity);

  /// Serializes this AuthSession to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as AuthSession;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is AuthSession &&
            (identical(other.accessToken, _this.accessToken) ||
                other.accessToken == _this.accessToken) &&
            (identical(other.refreshToken, _this.refreshToken) ||
                other.refreshToken == _this.refreshToken) &&
            (identical(other.tokenType, _this.tokenType) ||
                other.tokenType == _this.tokenType) &&
            (identical(other.expiresAt, _this.expiresAt) ||
                other.expiresAt == _this.expiresAt) &&
            (identical(other.refreshExpiresAt, _this.refreshExpiresAt) ||
                other.refreshExpiresAt == _this.refreshExpiresAt) &&
            (identical(other.user, _this.user) || other.user == _this.user) &&
            const DeepCollectionEquality().equals(
              other.tenants,
              _this.tenants,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as AuthSession;
    return Object.hash(
      runtimeType,
      _this.accessToken,
      _this.refreshToken,
      _this.tokenType,
      _this.expiresAt,
      _this.refreshExpiresAt,
      _this.user,
      const DeepCollectionEquality().hash(_this.tenants),
    );
  }

  @override
  String toString() {
    final _this = this as AuthSession;
    return 'AuthSession(accessToken: ${_this.accessToken}, refreshToken: ${_this.refreshToken}, tokenType: ${_this.tokenType}, expiresAt: ${_this.expiresAt}, refreshExpiresAt: ${_this.refreshExpiresAt}, user: ${_this.user}, tenants: ${_this.tenants})';
  }
}

/// @nodoc
abstract mixin class $AuthSessionCopyWith<$Res> {
  factory $AuthSessionCopyWith(
    AuthSession value,
    $Res Function(AuthSession) _then,
  ) = _$AuthSessionCopyWithImpl;
  @useResult
  $Res call({
    String accessToken,
    String refreshToken,
    String tokenType,
    DateTime? expiresAt,
    DateTime? refreshExpiresAt,
    User user,
    List<Tenant> tenants,
  });

  $UserCopyWith<$Res> get user;
}

/// @nodoc
class _$AuthSessionCopyWithImpl<$Res> implements $AuthSessionCopyWith<$Res> {
  _$AuthSessionCopyWithImpl(this._self, this._then);

  final AuthSession _self;
  final $Res Function(AuthSession) _then;

  /// Create a copy of AuthSession
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? accessToken = null,
    Object? refreshToken = null,
    Object? tokenType = null,
    Object? expiresAt = freezed,
    Object? refreshExpiresAt = freezed,
    Object? user = null,
    Object? tenants = null,
  }) {
    return _then(
      AuthSession(
        accessToken: null == accessToken
            ? _self.accessToken
            : accessToken // ignore: cast_nullable_to_non_nullable
                  as String,
        refreshToken: null == refreshToken
            ? _self.refreshToken
            : refreshToken // ignore: cast_nullable_to_non_nullable
                  as String,
        tokenType: null == tokenType
            ? _self.tokenType
            : tokenType // ignore: cast_nullable_to_non_nullable
                  as String,
        expiresAt: freezed == expiresAt
            ? _self.expiresAt
            : expiresAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        refreshExpiresAt: freezed == refreshExpiresAt
            ? _self.refreshExpiresAt
            : refreshExpiresAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        user: null == user
            ? _self.user
            : user // ignore: cast_nullable_to_non_nullable
                  as User,
        tenants: null == tenants
            ? _self.tenants
            : tenants // ignore: cast_nullable_to_non_nullable
                  as List<Tenant>,
      ),
    );
  }

  /// Create a copy of AuthSession
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $UserCopyWith<$Res> get user {
    return $UserCopyWith<$Res>(_self.user, (value) {
      return _then(_self.copyWith(user: value));
    });
  }
}

/// Adds pattern-matching-related methods to [AuthSession].
extension AuthSessionPatterns on AuthSession {
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
    TResult Function(_AuthSession value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _AuthSession() when $default != null:
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
    TResult Function(_AuthSession value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _AuthSession():
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
    TResult? Function(_AuthSession value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _AuthSession() when $default != null:
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
      String accessToken,
      String refreshToken,
      String tokenType,
      DateTime? expiresAt,
      DateTime? refreshExpiresAt,
      User user,
      List<Tenant> tenants,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _AuthSession() when $default != null:
        return $default(
          _that.accessToken,
          _that.refreshToken,
          _that.tokenType,
          _that.expiresAt,
          _that.refreshExpiresAt,
          _that.user,
          _that.tenants,
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
      String accessToken,
      String refreshToken,
      String tokenType,
      DateTime? expiresAt,
      DateTime? refreshExpiresAt,
      User user,
      List<Tenant> tenants,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _AuthSession():
        return $default(
          _that.accessToken,
          _that.refreshToken,
          _that.tokenType,
          _that.expiresAt,
          _that.refreshExpiresAt,
          _that.user,
          _that.tenants,
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
      String accessToken,
      String refreshToken,
      String tokenType,
      DateTime? expiresAt,
      DateTime? refreshExpiresAt,
      User user,
      List<Tenant> tenants,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _AuthSession() when $default != null:
        return $default(
          _that.accessToken,
          _that.refreshToken,
          _that.tokenType,
          _that.expiresAt,
          _that.refreshExpiresAt,
          _that.user,
          _that.tenants,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _AuthSession extends AuthSession {
  const _AuthSession({
    required this.accessToken,
    required this.refreshToken,
    this.tokenType = 'Bearer',
    this.expiresAt,
    this.refreshExpiresAt,
    required this.user,
    List<Tenant> tenants = const <Tenant>[],
  }) : _tenants = tenants,
       super._();
  factory _AuthSession.fromJson(Map<String, dynamic> json) =>
      _$AuthSessionFromJson(json);

  @override
  final String accessToken;
  @override
  final String refreshToken;
  @override
  @JsonKey()
  final String tokenType;
  @override
  final DateTime? expiresAt;
  @override
  final DateTime? refreshExpiresAt;
  @override
  final User user;
  final List<Tenant> _tenants;
  @override
  @JsonKey()
  List<Tenant> get tenants {
    if (_tenants is EqualUnmodifiableListView) return _tenants;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_tenants);
  }

  /// Create a copy of AuthSession
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$AuthSessionCopyWith<_AuthSession> get copyWith =>
      __$AuthSessionCopyWithImpl<_AuthSession>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$AuthSessionToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _AuthSession &&
            (identical(other.accessToken, accessToken) ||
                other.accessToken == accessToken) &&
            (identical(other.refreshToken, refreshToken) ||
                other.refreshToken == refreshToken) &&
            (identical(other.tokenType, tokenType) ||
                other.tokenType == tokenType) &&
            (identical(other.expiresAt, expiresAt) ||
                other.expiresAt == expiresAt) &&
            (identical(other.refreshExpiresAt, refreshExpiresAt) ||
                other.refreshExpiresAt == refreshExpiresAt) &&
            (identical(other.user, user) || other.user == user) &&
            const DeepCollectionEquality().equals(other.tenants, _tenants));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      accessToken,
      refreshToken,
      tokenType,
      expiresAt,
      refreshExpiresAt,
      user,
      const DeepCollectionEquality().hash(_tenants),
    );
  }

  @override
  String toString() {
    return 'AuthSession(accessToken: $accessToken, refreshToken: $refreshToken, tokenType: $tokenType, expiresAt: $expiresAt, refreshExpiresAt: $refreshExpiresAt, user: $user, tenants: $tenants)';
  }
}

/// @nodoc
abstract mixin class _$AuthSessionCopyWith<$Res>
    implements $AuthSessionCopyWith<$Res> {
  factory _$AuthSessionCopyWith(
    _AuthSession value,
    $Res Function(_AuthSession) _then,
  ) = __$AuthSessionCopyWithImpl;
  @override
  @useResult
  $Res call({
    String accessToken,
    String refreshToken,
    String tokenType,
    DateTime? expiresAt,
    DateTime? refreshExpiresAt,
    User user,
    List<Tenant> tenants,
  });

  @override
  $UserCopyWith<$Res> get user;
}

/// @nodoc
class __$AuthSessionCopyWithImpl<$Res> implements _$AuthSessionCopyWith<$Res> {
  __$AuthSessionCopyWithImpl(this._self, this._then);

  final _AuthSession _self;
  final $Res Function(_AuthSession) _then;

  /// Create a copy of AuthSession
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? accessToken = null,
    Object? refreshToken = null,
    Object? tokenType = null,
    Object? expiresAt = freezed,
    Object? refreshExpiresAt = freezed,
    Object? user = null,
    Object? tenants = null,
  }) {
    return _then(
      _AuthSession(
        accessToken: null == accessToken
            ? _self.accessToken
            : accessToken // ignore: cast_nullable_to_non_nullable
                  as String,
        refreshToken: null == refreshToken
            ? _self.refreshToken
            : refreshToken // ignore: cast_nullable_to_non_nullable
                  as String,
        tokenType: null == tokenType
            ? _self.tokenType
            : tokenType // ignore: cast_nullable_to_non_nullable
                  as String,
        expiresAt: freezed == expiresAt
            ? _self.expiresAt
            : expiresAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        refreshExpiresAt: freezed == refreshExpiresAt
            ? _self.refreshExpiresAt
            : refreshExpiresAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        user: null == user
            ? _self.user
            : user // ignore: cast_nullable_to_non_nullable
                  as User,
        tenants: null == tenants
            ? _self._tenants
            : tenants // ignore: cast_nullable_to_non_nullable
                  as List<Tenant>,
      ),
    );
  }

  /// Create a copy of AuthSession
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $UserCopyWith<$Res> get user {
    return $UserCopyWith<$Res>(_self.user, (value) {
      return _then(_self.copyWith(user: value));
    });
  }
}

/// @nodoc
mixin _$MeResponse {
  User get user;
  List<Tenant> get tenants;

  /// Create a copy of MeResponse
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $MeResponseCopyWith<MeResponse> get copyWith =>
      _$MeResponseCopyWithImpl<MeResponse>(this as MeResponse, _$identity);

  /// Serializes this MeResponse to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as MeResponse;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is MeResponse &&
            (identical(other.user, _this.user) || other.user == _this.user) &&
            const DeepCollectionEquality().equals(
              other.tenants,
              _this.tenants,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as MeResponse;
    return Object.hash(
      runtimeType,
      _this.user,
      const DeepCollectionEquality().hash(_this.tenants),
    );
  }

  @override
  String toString() {
    final _this = this as MeResponse;
    return 'MeResponse(user: ${_this.user}, tenants: ${_this.tenants})';
  }
}

/// @nodoc
abstract mixin class $MeResponseCopyWith<$Res> {
  factory $MeResponseCopyWith(
    MeResponse value,
    $Res Function(MeResponse) _then,
  ) = _$MeResponseCopyWithImpl;
  @useResult
  $Res call({User user, List<Tenant> tenants});

  $UserCopyWith<$Res> get user;
}

/// @nodoc
class _$MeResponseCopyWithImpl<$Res> implements $MeResponseCopyWith<$Res> {
  _$MeResponseCopyWithImpl(this._self, this._then);

  final MeResponse _self;
  final $Res Function(MeResponse) _then;

  /// Create a copy of MeResponse
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? user = null, Object? tenants = null}) {
    return _then(
      MeResponse(
        user: null == user
            ? _self.user
            : user // ignore: cast_nullable_to_non_nullable
                  as User,
        tenants: null == tenants
            ? _self.tenants
            : tenants // ignore: cast_nullable_to_non_nullable
                  as List<Tenant>,
      ),
    );
  }

  /// Create a copy of MeResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $UserCopyWith<$Res> get user {
    return $UserCopyWith<$Res>(_self.user, (value) {
      return _then(_self.copyWith(user: value));
    });
  }
}

/// Adds pattern-matching-related methods to [MeResponse].
extension MeResponsePatterns on MeResponse {
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
    TResult Function(_MeResponse value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MeResponse() when $default != null:
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
    TResult Function(_MeResponse value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MeResponse():
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
    TResult? Function(_MeResponse value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MeResponse() when $default != null:
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
    TResult Function(User user, List<Tenant> tenants)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MeResponse() when $default != null:
        return $default(_that.user, _that.tenants);
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
    TResult Function(User user, List<Tenant> tenants) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MeResponse():
        return $default(_that.user, _that.tenants);
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
    TResult? Function(User user, List<Tenant> tenants)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MeResponse() when $default != null:
        return $default(_that.user, _that.tenants);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _MeResponse implements MeResponse {
  const _MeResponse({
    required this.user,
    List<Tenant> tenants = const <Tenant>[],
  }) : _tenants = tenants;
  factory _MeResponse.fromJson(Map<String, dynamic> json) =>
      _$MeResponseFromJson(json);

  @override
  final User user;
  final List<Tenant> _tenants;
  @override
  @JsonKey()
  List<Tenant> get tenants {
    if (_tenants is EqualUnmodifiableListView) return _tenants;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_tenants);
  }

  /// Create a copy of MeResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$MeResponseCopyWith<_MeResponse> get copyWith =>
      __$MeResponseCopyWithImpl<_MeResponse>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$MeResponseToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _MeResponse &&
            (identical(other.user, user) || other.user == user) &&
            const DeepCollectionEquality().equals(other.tenants, _tenants));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      user,
      const DeepCollectionEquality().hash(_tenants),
    );
  }

  @override
  String toString() {
    return 'MeResponse(user: $user, tenants: $tenants)';
  }
}

/// @nodoc
abstract mixin class _$MeResponseCopyWith<$Res>
    implements $MeResponseCopyWith<$Res> {
  factory _$MeResponseCopyWith(
    _MeResponse value,
    $Res Function(_MeResponse) _then,
  ) = __$MeResponseCopyWithImpl;
  @override
  @useResult
  $Res call({User user, List<Tenant> tenants});

  @override
  $UserCopyWith<$Res> get user;
}

/// @nodoc
class __$MeResponseCopyWithImpl<$Res> implements _$MeResponseCopyWith<$Res> {
  __$MeResponseCopyWithImpl(this._self, this._then);

  final _MeResponse _self;
  final $Res Function(_MeResponse) _then;

  /// Create a copy of MeResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? user = null, Object? tenants = null}) {
    return _then(
      _MeResponse(
        user: null == user
            ? _self.user
            : user // ignore: cast_nullable_to_non_nullable
                  as User,
        tenants: null == tenants
            ? _self._tenants
            : tenants // ignore: cast_nullable_to_non_nullable
                  as List<Tenant>,
      ),
    );
  }

  /// Create a copy of MeResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $UserCopyWith<$Res> get user {
    return $UserCopyWith<$Res>(_self.user, (value) {
      return _then(_self.copyWith(user: value));
    });
  }
}

/// @nodoc
mixin _$InvitationTenant {
  String get name;
  String? get logoUrl;
  String? get brandColor;

  /// Create a copy of InvitationTenant
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $InvitationTenantCopyWith<InvitationTenant> get copyWith =>
      _$InvitationTenantCopyWithImpl<InvitationTenant>(
        this as InvitationTenant,
        _$identity,
      );

  /// Serializes this InvitationTenant to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as InvitationTenant;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is InvitationTenant &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.logoUrl, _this.logoUrl) ||
                other.logoUrl == _this.logoUrl) &&
            (identical(other.brandColor, _this.brandColor) ||
                other.brandColor == _this.brandColor));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as InvitationTenant;
    return Object.hash(
      runtimeType,
      _this.name,
      _this.logoUrl,
      _this.brandColor,
    );
  }

  @override
  String toString() {
    final _this = this as InvitationTenant;
    return 'InvitationTenant(name: ${_this.name}, logoUrl: ${_this.logoUrl}, brandColor: ${_this.brandColor})';
  }
}

/// @nodoc
abstract mixin class $InvitationTenantCopyWith<$Res> {
  factory $InvitationTenantCopyWith(
    InvitationTenant value,
    $Res Function(InvitationTenant) _then,
  ) = _$InvitationTenantCopyWithImpl;
  @useResult
  $Res call({String name, String? logoUrl, String? brandColor});
}

/// @nodoc
class _$InvitationTenantCopyWithImpl<$Res>
    implements $InvitationTenantCopyWith<$Res> {
  _$InvitationTenantCopyWithImpl(this._self, this._then);

  final InvitationTenant _self;
  final $Res Function(InvitationTenant) _then;

  /// Create a copy of InvitationTenant
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? name = null,
    Object? logoUrl = freezed,
    Object? brandColor = freezed,
  }) {
    return _then(
      InvitationTenant(
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        logoUrl: freezed == logoUrl
            ? _self.logoUrl
            : logoUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        brandColor: freezed == brandColor
            ? _self.brandColor
            : brandColor // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [InvitationTenant].
extension InvitationTenantPatterns on InvitationTenant {
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
    TResult Function(_InvitationTenant value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _InvitationTenant() when $default != null:
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
    TResult Function(_InvitationTenant value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationTenant():
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
    TResult? Function(_InvitationTenant value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationTenant() when $default != null:
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
    TResult Function(String name, String? logoUrl, String? brandColor)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _InvitationTenant() when $default != null:
        return $default(_that.name, _that.logoUrl, _that.brandColor);
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
    TResult Function(String name, String? logoUrl, String? brandColor) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationTenant():
        return $default(_that.name, _that.logoUrl, _that.brandColor);
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
    TResult? Function(String name, String? logoUrl, String? brandColor)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationTenant() when $default != null:
        return $default(_that.name, _that.logoUrl, _that.brandColor);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _InvitationTenant implements InvitationTenant {
  const _InvitationTenant({required this.name, this.logoUrl, this.brandColor});
  factory _InvitationTenant.fromJson(Map<String, dynamic> json) =>
      _$InvitationTenantFromJson(json);

  @override
  final String name;
  @override
  final String? logoUrl;
  @override
  final String? brandColor;

  /// Create a copy of InvitationTenant
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$InvitationTenantCopyWith<_InvitationTenant> get copyWith =>
      __$InvitationTenantCopyWithImpl<_InvitationTenant>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$InvitationTenantToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _InvitationTenant &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.logoUrl, logoUrl) || other.logoUrl == logoUrl) &&
            (identical(other.brandColor, brandColor) ||
                other.brandColor == brandColor));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, name, logoUrl, brandColor);
  }

  @override
  String toString() {
    return 'InvitationTenant(name: $name, logoUrl: $logoUrl, brandColor: $brandColor)';
  }
}

/// @nodoc
abstract mixin class _$InvitationTenantCopyWith<$Res>
    implements $InvitationTenantCopyWith<$Res> {
  factory _$InvitationTenantCopyWith(
    _InvitationTenant value,
    $Res Function(_InvitationTenant) _then,
  ) = __$InvitationTenantCopyWithImpl;
  @override
  @useResult
  $Res call({String name, String? logoUrl, String? brandColor});
}

/// @nodoc
class __$InvitationTenantCopyWithImpl<$Res>
    implements _$InvitationTenantCopyWith<$Res> {
  __$InvitationTenantCopyWithImpl(this._self, this._then);

  final _InvitationTenant _self;
  final $Res Function(_InvitationTenant) _then;

  /// Create a copy of InvitationTenant
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? name = null,
    Object? logoUrl = freezed,
    Object? brandColor = freezed,
  }) {
    return _then(
      _InvitationTenant(
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        logoUrl: freezed == logoUrl
            ? _self.logoUrl
            : logoUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        brandColor: freezed == brandColor
            ? _self.brandColor
            : brandColor // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$InvitationCoach {
  String get name;

  /// Create a copy of InvitationCoach
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $InvitationCoachCopyWith<InvitationCoach> get copyWith =>
      _$InvitationCoachCopyWithImpl<InvitationCoach>(
        this as InvitationCoach,
        _$identity,
      );

  /// Serializes this InvitationCoach to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as InvitationCoach;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is InvitationCoach &&
            (identical(other.name, _this.name) || other.name == _this.name));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as InvitationCoach;
    return Object.hash(runtimeType, _this.name);
  }

  @override
  String toString() {
    final _this = this as InvitationCoach;
    return 'InvitationCoach(name: ${_this.name})';
  }
}

/// @nodoc
abstract mixin class $InvitationCoachCopyWith<$Res> {
  factory $InvitationCoachCopyWith(
    InvitationCoach value,
    $Res Function(InvitationCoach) _then,
  ) = _$InvitationCoachCopyWithImpl;
  @useResult
  $Res call({String name});
}

/// @nodoc
class _$InvitationCoachCopyWithImpl<$Res>
    implements $InvitationCoachCopyWith<$Res> {
  _$InvitationCoachCopyWithImpl(this._self, this._then);

  final InvitationCoach _self;
  final $Res Function(InvitationCoach) _then;

  /// Create a copy of InvitationCoach
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? name = null}) {
    return _then(
      InvitationCoach(
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [InvitationCoach].
extension InvitationCoachPatterns on InvitationCoach {
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
    TResult Function(_InvitationCoach value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _InvitationCoach() when $default != null:
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
    TResult Function(_InvitationCoach value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationCoach():
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
    TResult? Function(_InvitationCoach value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationCoach() when $default != null:
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
    TResult Function(String name)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _InvitationCoach() when $default != null:
        return $default(_that.name);
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
    TResult Function(String name) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationCoach():
        return $default(_that.name);
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
    TResult? Function(String name)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationCoach() when $default != null:
        return $default(_that.name);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _InvitationCoach implements InvitationCoach {
  const _InvitationCoach({required this.name});
  factory _InvitationCoach.fromJson(Map<String, dynamic> json) =>
      _$InvitationCoachFromJson(json);

  @override
  final String name;

  /// Create a copy of InvitationCoach
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$InvitationCoachCopyWith<_InvitationCoach> get copyWith =>
      __$InvitationCoachCopyWithImpl<_InvitationCoach>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$InvitationCoachToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _InvitationCoach &&
            (identical(other.name, name) || other.name == name));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, name);
  }

  @override
  String toString() {
    return 'InvitationCoach(name: $name)';
  }
}

/// @nodoc
abstract mixin class _$InvitationCoachCopyWith<$Res>
    implements $InvitationCoachCopyWith<$Res> {
  factory _$InvitationCoachCopyWith(
    _InvitationCoach value,
    $Res Function(_InvitationCoach) _then,
  ) = __$InvitationCoachCopyWithImpl;
  @override
  @useResult
  $Res call({String name});
}

/// @nodoc
class __$InvitationCoachCopyWithImpl<$Res>
    implements _$InvitationCoachCopyWith<$Res> {
  __$InvitationCoachCopyWithImpl(this._self, this._then);

  final _InvitationCoach _self;
  final $Res Function(_InvitationCoach) _then;

  /// Create a copy of InvitationCoach
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? name = null}) {
    return _then(
      _InvitationCoach(
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
      ),
    );
  }
}

/// @nodoc
mixin _$InvitationPreview {
  String get code;
  String get role;
  String? get email;
  String? get name;
  InvitationTenant get tenant;
  InvitationCoach? get coach;

  /// Create a copy of InvitationPreview
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $InvitationPreviewCopyWith<InvitationPreview> get copyWith =>
      _$InvitationPreviewCopyWithImpl<InvitationPreview>(
        this as InvitationPreview,
        _$identity,
      );

  /// Serializes this InvitationPreview to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as InvitationPreview;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is InvitationPreview &&
            (identical(other.code, _this.code) || other.code == _this.code) &&
            (identical(other.role, _this.role) || other.role == _this.role) &&
            (identical(other.email, _this.email) ||
                other.email == _this.email) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.tenant, _this.tenant) ||
                other.tenant == _this.tenant) &&
            (identical(other.coach, _this.coach) ||
                other.coach == _this.coach));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as InvitationPreview;
    return Object.hash(
      runtimeType,
      _this.code,
      _this.role,
      _this.email,
      _this.name,
      _this.tenant,
      _this.coach,
    );
  }

  @override
  String toString() {
    final _this = this as InvitationPreview;
    return 'InvitationPreview(code: ${_this.code}, role: ${_this.role}, email: ${_this.email}, name: ${_this.name}, tenant: ${_this.tenant}, coach: ${_this.coach})';
  }
}

/// @nodoc
abstract mixin class $InvitationPreviewCopyWith<$Res> {
  factory $InvitationPreviewCopyWith(
    InvitationPreview value,
    $Res Function(InvitationPreview) _then,
  ) = _$InvitationPreviewCopyWithImpl;
  @useResult
  $Res call({
    String code,
    String role,
    String? email,
    String? name,
    InvitationTenant tenant,
    InvitationCoach? coach,
  });

  $InvitationTenantCopyWith<$Res> get tenant;
  $InvitationCoachCopyWith<$Res>? get coach;
}

/// @nodoc
class _$InvitationPreviewCopyWithImpl<$Res>
    implements $InvitationPreviewCopyWith<$Res> {
  _$InvitationPreviewCopyWithImpl(this._self, this._then);

  final InvitationPreview _self;
  final $Res Function(InvitationPreview) _then;

  /// Create a copy of InvitationPreview
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? code = null,
    Object? role = null,
    Object? email = freezed,
    Object? name = freezed,
    Object? tenant = null,
    Object? coach = freezed,
  }) {
    return _then(
      InvitationPreview(
        code: null == code
            ? _self.code
            : code // ignore: cast_nullable_to_non_nullable
                  as String,
        role: null == role
            ? _self.role
            : role // ignore: cast_nullable_to_non_nullable
                  as String,
        email: freezed == email
            ? _self.email
            : email // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: freezed == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String?,
        tenant: null == tenant
            ? _self.tenant
            : tenant // ignore: cast_nullable_to_non_nullable
                  as InvitationTenant,
        coach: freezed == coach
            ? _self.coach
            : coach // ignore: cast_nullable_to_non_nullable
                  as InvitationCoach?,
      ),
    );
  }

  /// Create a copy of InvitationPreview
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $InvitationTenantCopyWith<$Res> get tenant {
    return $InvitationTenantCopyWith<$Res>(_self.tenant, (value) {
      return _then(_self.copyWith(tenant: value));
    });
  }

  /// Create a copy of InvitationPreview
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $InvitationCoachCopyWith<$Res>? get coach {
    if (_self.coach == null) {
      return null;
    }

    return $InvitationCoachCopyWith<$Res>(_self.coach!, (value) {
      return _then(_self.copyWith(coach: value));
    });
  }
}

/// Adds pattern-matching-related methods to [InvitationPreview].
extension InvitationPreviewPatterns on InvitationPreview {
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
    TResult Function(_InvitationPreview value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _InvitationPreview() when $default != null:
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
    TResult Function(_InvitationPreview value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationPreview():
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
    TResult? Function(_InvitationPreview value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationPreview() when $default != null:
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
      String code,
      String role,
      String? email,
      String? name,
      InvitationTenant tenant,
      InvitationCoach? coach,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _InvitationPreview() when $default != null:
        return $default(
          _that.code,
          _that.role,
          _that.email,
          _that.name,
          _that.tenant,
          _that.coach,
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
      String code,
      String role,
      String? email,
      String? name,
      InvitationTenant tenant,
      InvitationCoach? coach,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationPreview():
        return $default(
          _that.code,
          _that.role,
          _that.email,
          _that.name,
          _that.tenant,
          _that.coach,
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
      String code,
      String role,
      String? email,
      String? name,
      InvitationTenant tenant,
      InvitationCoach? coach,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _InvitationPreview() when $default != null:
        return $default(
          _that.code,
          _that.role,
          _that.email,
          _that.name,
          _that.tenant,
          _that.coach,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _InvitationPreview implements InvitationPreview {
  const _InvitationPreview({
    required this.code,
    this.role = 'client',
    this.email,
    this.name,
    required this.tenant,
    this.coach,
  });
  factory _InvitationPreview.fromJson(Map<String, dynamic> json) =>
      _$InvitationPreviewFromJson(json);

  @override
  final String code;
  @override
  @JsonKey()
  final String role;
  @override
  final String? email;
  @override
  final String? name;
  @override
  final InvitationTenant tenant;
  @override
  final InvitationCoach? coach;

  /// Create a copy of InvitationPreview
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$InvitationPreviewCopyWith<_InvitationPreview> get copyWith =>
      __$InvitationPreviewCopyWithImpl<_InvitationPreview>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$InvitationPreviewToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _InvitationPreview &&
            (identical(other.code, code) || other.code == code) &&
            (identical(other.role, role) || other.role == role) &&
            (identical(other.email, email) || other.email == email) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.tenant, tenant) || other.tenant == tenant) &&
            (identical(other.coach, coach) || other.coach == coach));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, code, role, email, name, tenant, coach);
  }

  @override
  String toString() {
    return 'InvitationPreview(code: $code, role: $role, email: $email, name: $name, tenant: $tenant, coach: $coach)';
  }
}

/// @nodoc
abstract mixin class _$InvitationPreviewCopyWith<$Res>
    implements $InvitationPreviewCopyWith<$Res> {
  factory _$InvitationPreviewCopyWith(
    _InvitationPreview value,
    $Res Function(_InvitationPreview) _then,
  ) = __$InvitationPreviewCopyWithImpl;
  @override
  @useResult
  $Res call({
    String code,
    String role,
    String? email,
    String? name,
    InvitationTenant tenant,
    InvitationCoach? coach,
  });

  @override
  $InvitationTenantCopyWith<$Res> get tenant;
  @override
  $InvitationCoachCopyWith<$Res>? get coach;
}

/// @nodoc
class __$InvitationPreviewCopyWithImpl<$Res>
    implements _$InvitationPreviewCopyWith<$Res> {
  __$InvitationPreviewCopyWithImpl(this._self, this._then);

  final _InvitationPreview _self;
  final $Res Function(_InvitationPreview) _then;

  /// Create a copy of InvitationPreview
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? code = null,
    Object? role = null,
    Object? email = freezed,
    Object? name = freezed,
    Object? tenant = null,
    Object? coach = freezed,
  }) {
    return _then(
      _InvitationPreview(
        code: null == code
            ? _self.code
            : code // ignore: cast_nullable_to_non_nullable
                  as String,
        role: null == role
            ? _self.role
            : role // ignore: cast_nullable_to_non_nullable
                  as String,
        email: freezed == email
            ? _self.email
            : email // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: freezed == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String?,
        tenant: null == tenant
            ? _self.tenant
            : tenant // ignore: cast_nullable_to_non_nullable
                  as InvitationTenant,
        coach: freezed == coach
            ? _self.coach
            : coach // ignore: cast_nullable_to_non_nullable
                  as InvitationCoach?,
      ),
    );
  }

  /// Create a copy of InvitationPreview
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $InvitationTenantCopyWith<$Res> get tenant {
    return $InvitationTenantCopyWith<$Res>(_self.tenant, (value) {
      return _then(_self.copyWith(tenant: value));
    });
  }

  /// Create a copy of InvitationPreview
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $InvitationCoachCopyWith<$Res>? get coach {
    if (_self.coach == null) {
      return null;
    }

    return $InvitationCoachCopyWith<$Res>(_self.coach!, (value) {
      return _then(_self.copyWith(coach: value));
    });
  }
}

/// @nodoc
mixin _$DeviceInfo {
  String? get deviceName;
  String? get platform;
  String? get pushToken;
  String? get app;
  String? get appVersion;
  String? get timezone;

  /// Create a copy of DeviceInfo
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $DeviceInfoCopyWith<DeviceInfo> get copyWith =>
      _$DeviceInfoCopyWithImpl<DeviceInfo>(this as DeviceInfo, _$identity);

  /// Serializes this DeviceInfo to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as DeviceInfo;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is DeviceInfo &&
            (identical(other.deviceName, _this.deviceName) ||
                other.deviceName == _this.deviceName) &&
            (identical(other.platform, _this.platform) ||
                other.platform == _this.platform) &&
            (identical(other.pushToken, _this.pushToken) ||
                other.pushToken == _this.pushToken) &&
            (identical(other.app, _this.app) || other.app == _this.app) &&
            (identical(other.appVersion, _this.appVersion) ||
                other.appVersion == _this.appVersion) &&
            (identical(other.timezone, _this.timezone) ||
                other.timezone == _this.timezone));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as DeviceInfo;
    return Object.hash(
      runtimeType,
      _this.deviceName,
      _this.platform,
      _this.pushToken,
      _this.app,
      _this.appVersion,
      _this.timezone,
    );
  }

  @override
  String toString() {
    final _this = this as DeviceInfo;
    return 'DeviceInfo(deviceName: ${_this.deviceName}, platform: ${_this.platform}, pushToken: ${_this.pushToken}, app: ${_this.app}, appVersion: ${_this.appVersion}, timezone: ${_this.timezone})';
  }
}

/// @nodoc
abstract mixin class $DeviceInfoCopyWith<$Res> {
  factory $DeviceInfoCopyWith(
    DeviceInfo value,
    $Res Function(DeviceInfo) _then,
  ) = _$DeviceInfoCopyWithImpl;
  @useResult
  $Res call({
    String? deviceName,
    String? platform,
    String? pushToken,
    String? app,
    String? appVersion,
    String? timezone,
  });
}

/// @nodoc
class _$DeviceInfoCopyWithImpl<$Res> implements $DeviceInfoCopyWith<$Res> {
  _$DeviceInfoCopyWithImpl(this._self, this._then);

  final DeviceInfo _self;
  final $Res Function(DeviceInfo) _then;

  /// Create a copy of DeviceInfo
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? deviceName = freezed,
    Object? platform = freezed,
    Object? pushToken = freezed,
    Object? app = freezed,
    Object? appVersion = freezed,
    Object? timezone = freezed,
  }) {
    return _then(
      DeviceInfo(
        deviceName: freezed == deviceName
            ? _self.deviceName
            : deviceName // ignore: cast_nullable_to_non_nullable
                  as String?,
        platform: freezed == platform
            ? _self.platform
            : platform // ignore: cast_nullable_to_non_nullable
                  as String?,
        pushToken: freezed == pushToken
            ? _self.pushToken
            : pushToken // ignore: cast_nullable_to_non_nullable
                  as String?,
        app: freezed == app
            ? _self.app
            : app // ignore: cast_nullable_to_non_nullable
                  as String?,
        appVersion: freezed == appVersion
            ? _self.appVersion
            : appVersion // ignore: cast_nullable_to_non_nullable
                  as String?,
        timezone: freezed == timezone
            ? _self.timezone
            : timezone // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [DeviceInfo].
extension DeviceInfoPatterns on DeviceInfo {
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
    TResult Function(_DeviceInfo value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _DeviceInfo() when $default != null:
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
    TResult Function(_DeviceInfo value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _DeviceInfo():
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
    TResult? Function(_DeviceInfo value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _DeviceInfo() when $default != null:
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
      String? deviceName,
      String? platform,
      String? pushToken,
      String? app,
      String? appVersion,
      String? timezone,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _DeviceInfo() when $default != null:
        return $default(
          _that.deviceName,
          _that.platform,
          _that.pushToken,
          _that.app,
          _that.appVersion,
          _that.timezone,
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
      String? deviceName,
      String? platform,
      String? pushToken,
      String? app,
      String? appVersion,
      String? timezone,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _DeviceInfo():
        return $default(
          _that.deviceName,
          _that.platform,
          _that.pushToken,
          _that.app,
          _that.appVersion,
          _that.timezone,
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
      String? deviceName,
      String? platform,
      String? pushToken,
      String? app,
      String? appVersion,
      String? timezone,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _DeviceInfo() when $default != null:
        return $default(
          _that.deviceName,
          _that.platform,
          _that.pushToken,
          _that.app,
          _that.appVersion,
          _that.timezone,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _DeviceInfo implements DeviceInfo {
  const _DeviceInfo({
    this.deviceName,
    this.platform,
    this.pushToken,
    this.app,
    this.appVersion,
    this.timezone,
  });
  factory _DeviceInfo.fromJson(Map<String, dynamic> json) =>
      _$DeviceInfoFromJson(json);

  @override
  final String? deviceName;
  @override
  final String? platform;
  @override
  final String? pushToken;
  @override
  final String? app;
  @override
  final String? appVersion;
  @override
  final String? timezone;

  /// Create a copy of DeviceInfo
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$DeviceInfoCopyWith<_DeviceInfo> get copyWith =>
      __$DeviceInfoCopyWithImpl<_DeviceInfo>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$DeviceInfoToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _DeviceInfo &&
            (identical(other.deviceName, deviceName) ||
                other.deviceName == deviceName) &&
            (identical(other.platform, platform) ||
                other.platform == platform) &&
            (identical(other.pushToken, pushToken) ||
                other.pushToken == pushToken) &&
            (identical(other.app, app) || other.app == app) &&
            (identical(other.appVersion, appVersion) ||
                other.appVersion == appVersion) &&
            (identical(other.timezone, timezone) ||
                other.timezone == timezone));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      deviceName,
      platform,
      pushToken,
      app,
      appVersion,
      timezone,
    );
  }

  @override
  String toString() {
    return 'DeviceInfo(deviceName: $deviceName, platform: $platform, pushToken: $pushToken, app: $app, appVersion: $appVersion, timezone: $timezone)';
  }
}

/// @nodoc
abstract mixin class _$DeviceInfoCopyWith<$Res>
    implements $DeviceInfoCopyWith<$Res> {
  factory _$DeviceInfoCopyWith(
    _DeviceInfo value,
    $Res Function(_DeviceInfo) _then,
  ) = __$DeviceInfoCopyWithImpl;
  @override
  @useResult
  $Res call({
    String? deviceName,
    String? platform,
    String? pushToken,
    String? app,
    String? appVersion,
    String? timezone,
  });
}

/// @nodoc
class __$DeviceInfoCopyWithImpl<$Res> implements _$DeviceInfoCopyWith<$Res> {
  __$DeviceInfoCopyWithImpl(this._self, this._then);

  final _DeviceInfo _self;
  final $Res Function(_DeviceInfo) _then;

  /// Create a copy of DeviceInfo
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? deviceName = freezed,
    Object? platform = freezed,
    Object? pushToken = freezed,
    Object? app = freezed,
    Object? appVersion = freezed,
    Object? timezone = freezed,
  }) {
    return _then(
      _DeviceInfo(
        deviceName: freezed == deviceName
            ? _self.deviceName
            : deviceName // ignore: cast_nullable_to_non_nullable
                  as String?,
        platform: freezed == platform
            ? _self.platform
            : platform // ignore: cast_nullable_to_non_nullable
                  as String?,
        pushToken: freezed == pushToken
            ? _self.pushToken
            : pushToken // ignore: cast_nullable_to_non_nullable
                  as String?,
        app: freezed == app
            ? _self.app
            : app // ignore: cast_nullable_to_non_nullable
                  as String?,
        appVersion: freezed == appVersion
            ? _self.appVersion
            : appVersion // ignore: cast_nullable_to_non_nullable
                  as String?,
        timezone: freezed == timezone
            ? _self.timezone
            : timezone // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}
