// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'messaging.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$Participant {
  String get id;
  String get name;
  String? get avatarUrl;
  String? get role;

  /// Create a copy of Participant
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ParticipantCopyWith<Participant> get copyWith =>
      _$ParticipantCopyWithImpl<Participant>(this as Participant, _$identity);

  /// Serializes this Participant to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Participant;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Participant &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.avatarUrl, _this.avatarUrl) ||
                other.avatarUrl == _this.avatarUrl) &&
            (identical(other.role, _this.role) || other.role == _this.role));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Participant;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.name,
      _this.avatarUrl,
      _this.role,
    );
  }

  @override
  String toString() {
    final _this = this as Participant;
    return 'Participant(id: ${_this.id}, name: ${_this.name}, avatarUrl: ${_this.avatarUrl}, role: ${_this.role})';
  }
}

/// @nodoc
abstract mixin class $ParticipantCopyWith<$Res> {
  factory $ParticipantCopyWith(
    Participant value,
    $Res Function(Participant) _then,
  ) = _$ParticipantCopyWithImpl;
  @useResult
  $Res call({String id, String name, String? avatarUrl, String? role});
}

/// @nodoc
class _$ParticipantCopyWithImpl<$Res> implements $ParticipantCopyWith<$Res> {
  _$ParticipantCopyWithImpl(this._self, this._then);

  final Participant _self;
  final $Res Function(Participant) _then;

  /// Create a copy of Participant
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? avatarUrl = freezed,
    Object? role = freezed,
  }) {
    return _then(
      Participant(
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
        role: freezed == role
            ? _self.role
            : role // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Participant].
extension ParticipantPatterns on Participant {
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
    TResult Function(_Participant value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Participant() when $default != null:
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
    TResult Function(_Participant value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Participant():
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
    TResult? Function(_Participant value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Participant() when $default != null:
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
    TResult Function(String id, String name, String? avatarUrl, String? role)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Participant() when $default != null:
        return $default(_that.id, _that.name, _that.avatarUrl, _that.role);
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
    TResult Function(String id, String name, String? avatarUrl, String? role)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Participant():
        return $default(_that.id, _that.name, _that.avatarUrl, _that.role);
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
    TResult? Function(String id, String name, String? avatarUrl, String? role)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Participant() when $default != null:
        return $default(_that.id, _that.name, _that.avatarUrl, _that.role);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Participant implements Participant {
  const _Participant({
    required this.id,
    required this.name,
    this.avatarUrl,
    this.role,
  });
  factory _Participant.fromJson(Map<String, dynamic> json) =>
      _$ParticipantFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String? avatarUrl;
  @override
  final String? role;

  /// Create a copy of Participant
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ParticipantCopyWith<_Participant> get copyWith =>
      __$ParticipantCopyWithImpl<_Participant>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ParticipantToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Participant &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.avatarUrl, avatarUrl) ||
                other.avatarUrl == avatarUrl) &&
            (identical(other.role, role) || other.role == role));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, name, avatarUrl, role);
  }

  @override
  String toString() {
    return 'Participant(id: $id, name: $name, avatarUrl: $avatarUrl, role: $role)';
  }
}

/// @nodoc
abstract mixin class _$ParticipantCopyWith<$Res>
    implements $ParticipantCopyWith<$Res> {
  factory _$ParticipantCopyWith(
    _Participant value,
    $Res Function(_Participant) _then,
  ) = __$ParticipantCopyWithImpl;
  @override
  @useResult
  $Res call({String id, String name, String? avatarUrl, String? role});
}

/// @nodoc
class __$ParticipantCopyWithImpl<$Res> implements _$ParticipantCopyWith<$Res> {
  __$ParticipantCopyWithImpl(this._self, this._then);

  final _Participant _self;
  final $Res Function(_Participant) _then;

  /// Create a copy of Participant
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? avatarUrl = freezed,
    Object? role = freezed,
  }) {
    return _then(
      _Participant(
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
        role: freezed == role
            ? _self.role
            : role // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$MessageAttachment {
  String get id;
  String? get mime;
  int? get size;
  String? get url;
  int? get durationMs;
  List<double>? get waveform;

  /// Create a copy of MessageAttachment
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $MessageAttachmentCopyWith<MessageAttachment> get copyWith =>
      _$MessageAttachmentCopyWithImpl<MessageAttachment>(
        this as MessageAttachment,
        _$identity,
      );

  /// Serializes this MessageAttachment to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as MessageAttachment;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is MessageAttachment &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.mime, _this.mime) || other.mime == _this.mime) &&
            (identical(other.size, _this.size) || other.size == _this.size) &&
            (identical(other.url, _this.url) || other.url == _this.url) &&
            (identical(other.durationMs, _this.durationMs) ||
                other.durationMs == _this.durationMs) &&
            const DeepCollectionEquality().equals(
              other.waveform,
              _this.waveform,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as MessageAttachment;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.mime,
      _this.size,
      _this.url,
      _this.durationMs,
      const DeepCollectionEquality().hash(_this.waveform),
    );
  }

  @override
  String toString() {
    final _this = this as MessageAttachment;
    return 'MessageAttachment(id: ${_this.id}, mime: ${_this.mime}, size: ${_this.size}, url: ${_this.url}, durationMs: ${_this.durationMs}, waveform: ${_this.waveform})';
  }
}

/// @nodoc
abstract mixin class $MessageAttachmentCopyWith<$Res> {
  factory $MessageAttachmentCopyWith(
    MessageAttachment value,
    $Res Function(MessageAttachment) _then,
  ) = _$MessageAttachmentCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String? mime,
    int? size,
    String? url,
    int? durationMs,
    List<double>? waveform,
  });
}

/// @nodoc
class _$MessageAttachmentCopyWithImpl<$Res>
    implements $MessageAttachmentCopyWith<$Res> {
  _$MessageAttachmentCopyWithImpl(this._self, this._then);

  final MessageAttachment _self;
  final $Res Function(MessageAttachment) _then;

  /// Create a copy of MessageAttachment
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? mime = freezed,
    Object? size = freezed,
    Object? url = freezed,
    Object? durationMs = freezed,
    Object? waveform = freezed,
  }) {
    return _then(
      MessageAttachment(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        mime: freezed == mime
            ? _self.mime
            : mime // ignore: cast_nullable_to_non_nullable
                  as String?,
        size: freezed == size
            ? _self.size
            : size // ignore: cast_nullable_to_non_nullable
                  as int?,
        url: freezed == url
            ? _self.url
            : url // ignore: cast_nullable_to_non_nullable
                  as String?,
        durationMs: freezed == durationMs
            ? _self.durationMs
            : durationMs // ignore: cast_nullable_to_non_nullable
                  as int?,
        waveform: freezed == waveform
            ? _self.waveform
            : waveform // ignore: cast_nullable_to_non_nullable
                  as List<double>?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [MessageAttachment].
extension MessageAttachmentPatterns on MessageAttachment {
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
    TResult Function(_MessageAttachment value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MessageAttachment() when $default != null:
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
    TResult Function(_MessageAttachment value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageAttachment():
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
    TResult? Function(_MessageAttachment value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageAttachment() when $default != null:
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
      String? mime,
      int? size,
      String? url,
      int? durationMs,
      List<double>? waveform,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MessageAttachment() when $default != null:
        return $default(
          _that.id,
          _that.mime,
          _that.size,
          _that.url,
          _that.durationMs,
          _that.waveform,
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
      String? mime,
      int? size,
      String? url,
      int? durationMs,
      List<double>? waveform,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageAttachment():
        return $default(
          _that.id,
          _that.mime,
          _that.size,
          _that.url,
          _that.durationMs,
          _that.waveform,
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
      String? mime,
      int? size,
      String? url,
      int? durationMs,
      List<double>? waveform,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageAttachment() when $default != null:
        return $default(
          _that.id,
          _that.mime,
          _that.size,
          _that.url,
          _that.durationMs,
          _that.waveform,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _MessageAttachment implements MessageAttachment {
  const _MessageAttachment({
    required this.id,
    this.mime,
    this.size,
    this.url,
    this.durationMs,
    List<double>? waveform,
  }) : _waveform = waveform;
  factory _MessageAttachment.fromJson(Map<String, dynamic> json) =>
      _$MessageAttachmentFromJson(json);

  @override
  final String id;
  @override
  final String? mime;
  @override
  final int? size;
  @override
  final String? url;
  @override
  final int? durationMs;
  final List<double>? _waveform;
  @override
  List<double>? get waveform {
    final value = _waveform;
    if (value == null) return null;
    if (_waveform is EqualUnmodifiableListView) return _waveform;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  /// Create a copy of MessageAttachment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$MessageAttachmentCopyWith<_MessageAttachment> get copyWith =>
      __$MessageAttachmentCopyWithImpl<_MessageAttachment>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$MessageAttachmentToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _MessageAttachment &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.mime, mime) || other.mime == mime) &&
            (identical(other.size, size) || other.size == size) &&
            (identical(other.url, url) || other.url == url) &&
            (identical(other.durationMs, durationMs) ||
                other.durationMs == durationMs) &&
            const DeepCollectionEquality().equals(other.waveform, _waveform));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      mime,
      size,
      url,
      durationMs,
      const DeepCollectionEquality().hash(_waveform),
    );
  }

  @override
  String toString() {
    return 'MessageAttachment(id: $id, mime: $mime, size: $size, url: $url, durationMs: $durationMs, waveform: $waveform)';
  }
}

/// @nodoc
abstract mixin class _$MessageAttachmentCopyWith<$Res>
    implements $MessageAttachmentCopyWith<$Res> {
  factory _$MessageAttachmentCopyWith(
    _MessageAttachment value,
    $Res Function(_MessageAttachment) _then,
  ) = __$MessageAttachmentCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String? mime,
    int? size,
    String? url,
    int? durationMs,
    List<double>? waveform,
  });
}

/// @nodoc
class __$MessageAttachmentCopyWithImpl<$Res>
    implements _$MessageAttachmentCopyWith<$Res> {
  __$MessageAttachmentCopyWithImpl(this._self, this._then);

  final _MessageAttachment _self;
  final $Res Function(_MessageAttachment) _then;

  /// Create a copy of MessageAttachment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? mime = freezed,
    Object? size = freezed,
    Object? url = freezed,
    Object? durationMs = freezed,
    Object? waveform = freezed,
  }) {
    return _then(
      _MessageAttachment(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        mime: freezed == mime
            ? _self.mime
            : mime // ignore: cast_nullable_to_non_nullable
                  as String?,
        size: freezed == size
            ? _self.size
            : size // ignore: cast_nullable_to_non_nullable
                  as int?,
        url: freezed == url
            ? _self.url
            : url // ignore: cast_nullable_to_non_nullable
                  as String?,
        durationMs: freezed == durationMs
            ? _self.durationMs
            : durationMs // ignore: cast_nullable_to_non_nullable
                  as int?,
        waveform: freezed == waveform
            ? _self._waveform
            : waveform // ignore: cast_nullable_to_non_nullable
                  as List<double>?,
      ),
    );
  }
}

/// @nodoc
mixin _$MessageReplyTo {
  String get id;
  String? get body;
  String? get senderName;

  /// Create a copy of MessageReplyTo
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $MessageReplyToCopyWith<MessageReplyTo> get copyWith =>
      _$MessageReplyToCopyWithImpl<MessageReplyTo>(
        this as MessageReplyTo,
        _$identity,
      );

  /// Serializes this MessageReplyTo to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as MessageReplyTo;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is MessageReplyTo &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.body, _this.body) || other.body == _this.body) &&
            (identical(other.senderName, _this.senderName) ||
                other.senderName == _this.senderName));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as MessageReplyTo;
    return Object.hash(runtimeType, _this.id, _this.body, _this.senderName);
  }

  @override
  String toString() {
    final _this = this as MessageReplyTo;
    return 'MessageReplyTo(id: ${_this.id}, body: ${_this.body}, senderName: ${_this.senderName})';
  }
}

/// @nodoc
abstract mixin class $MessageReplyToCopyWith<$Res> {
  factory $MessageReplyToCopyWith(
    MessageReplyTo value,
    $Res Function(MessageReplyTo) _then,
  ) = _$MessageReplyToCopyWithImpl;
  @useResult
  $Res call({String id, String? body, String? senderName});
}

/// @nodoc
class _$MessageReplyToCopyWithImpl<$Res>
    implements $MessageReplyToCopyWith<$Res> {
  _$MessageReplyToCopyWithImpl(this._self, this._then);

  final MessageReplyTo _self;
  final $Res Function(MessageReplyTo) _then;

  /// Create a copy of MessageReplyTo
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? body = freezed,
    Object? senderName = freezed,
  }) {
    return _then(
      MessageReplyTo(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        body: freezed == body
            ? _self.body
            : body // ignore: cast_nullable_to_non_nullable
                  as String?,
        senderName: freezed == senderName
            ? _self.senderName
            : senderName // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [MessageReplyTo].
extension MessageReplyToPatterns on MessageReplyTo {
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
    TResult Function(_MessageReplyTo value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MessageReplyTo() when $default != null:
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
    TResult Function(_MessageReplyTo value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageReplyTo():
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
    TResult? Function(_MessageReplyTo value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageReplyTo() when $default != null:
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
    TResult Function(String id, String? body, String? senderName)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MessageReplyTo() when $default != null:
        return $default(_that.id, _that.body, _that.senderName);
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
    TResult Function(String id, String? body, String? senderName) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageReplyTo():
        return $default(_that.id, _that.body, _that.senderName);
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
    TResult? Function(String id, String? body, String? senderName)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageReplyTo() when $default != null:
        return $default(_that.id, _that.body, _that.senderName);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _MessageReplyTo implements MessageReplyTo {
  const _MessageReplyTo({required this.id, this.body, this.senderName});
  factory _MessageReplyTo.fromJson(Map<String, dynamic> json) =>
      _$MessageReplyToFromJson(json);

  @override
  final String id;
  @override
  final String? body;
  @override
  final String? senderName;

  /// Create a copy of MessageReplyTo
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$MessageReplyToCopyWith<_MessageReplyTo> get copyWith =>
      __$MessageReplyToCopyWithImpl<_MessageReplyTo>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$MessageReplyToToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _MessageReplyTo &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.body, body) || other.body == body) &&
            (identical(other.senderName, senderName) ||
                other.senderName == senderName));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, body, senderName);
  }

  @override
  String toString() {
    return 'MessageReplyTo(id: $id, body: $body, senderName: $senderName)';
  }
}

/// @nodoc
abstract mixin class _$MessageReplyToCopyWith<$Res>
    implements $MessageReplyToCopyWith<$Res> {
  factory _$MessageReplyToCopyWith(
    _MessageReplyTo value,
    $Res Function(_MessageReplyTo) _then,
  ) = __$MessageReplyToCopyWithImpl;
  @override
  @useResult
  $Res call({String id, String? body, String? senderName});
}

/// @nodoc
class __$MessageReplyToCopyWithImpl<$Res>
    implements _$MessageReplyToCopyWith<$Res> {
  __$MessageReplyToCopyWithImpl(this._self, this._then);

  final _MessageReplyTo _self;
  final $Res Function(_MessageReplyTo) _then;

  /// Create a copy of MessageReplyTo
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? body = freezed,
    Object? senderName = freezed,
  }) {
    return _then(
      _MessageReplyTo(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        body: freezed == body
            ? _self.body
            : body // ignore: cast_nullable_to_non_nullable
                  as String?,
        senderName: freezed == senderName
            ? _self.senderName
            : senderName // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$MessageReaction {
  String get emoji;
  int get count;
  bool get mine;

  /// Create a copy of MessageReaction
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $MessageReactionCopyWith<MessageReaction> get copyWith =>
      _$MessageReactionCopyWithImpl<MessageReaction>(
        this as MessageReaction,
        _$identity,
      );

  /// Serializes this MessageReaction to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as MessageReaction;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is MessageReaction &&
            (identical(other.emoji, _this.emoji) ||
                other.emoji == _this.emoji) &&
            (identical(other.count, _this.count) ||
                other.count == _this.count) &&
            (identical(other.mine, _this.mine) || other.mine == _this.mine));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as MessageReaction;
    return Object.hash(runtimeType, _this.emoji, _this.count, _this.mine);
  }

  @override
  String toString() {
    final _this = this as MessageReaction;
    return 'MessageReaction(emoji: ${_this.emoji}, count: ${_this.count}, mine: ${_this.mine})';
  }
}

/// @nodoc
abstract mixin class $MessageReactionCopyWith<$Res> {
  factory $MessageReactionCopyWith(
    MessageReaction value,
    $Res Function(MessageReaction) _then,
  ) = _$MessageReactionCopyWithImpl;
  @useResult
  $Res call({String emoji, int count, bool mine});
}

/// @nodoc
class _$MessageReactionCopyWithImpl<$Res>
    implements $MessageReactionCopyWith<$Res> {
  _$MessageReactionCopyWithImpl(this._self, this._then);

  final MessageReaction _self;
  final $Res Function(MessageReaction) _then;

  /// Create a copy of MessageReaction
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? emoji = null, Object? count = null, Object? mine = null}) {
    return _then(
      MessageReaction(
        emoji: null == emoji
            ? _self.emoji
            : emoji // ignore: cast_nullable_to_non_nullable
                  as String,
        count: null == count
            ? _self.count
            : count // ignore: cast_nullable_to_non_nullable
                  as int,
        mine: null == mine
            ? _self.mine
            : mine // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [MessageReaction].
extension MessageReactionPatterns on MessageReaction {
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
    TResult Function(_MessageReaction value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MessageReaction() when $default != null:
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
    TResult Function(_MessageReaction value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageReaction():
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
    TResult? Function(_MessageReaction value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageReaction() when $default != null:
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
    TResult Function(String emoji, int count, bool mine)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MessageReaction() when $default != null:
        return $default(_that.emoji, _that.count, _that.mine);
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
    TResult Function(String emoji, int count, bool mine) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageReaction():
        return $default(_that.emoji, _that.count, _that.mine);
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
    TResult? Function(String emoji, int count, bool mine)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MessageReaction() when $default != null:
        return $default(_that.emoji, _that.count, _that.mine);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _MessageReaction implements MessageReaction {
  const _MessageReaction({
    required this.emoji,
    this.count = 1,
    this.mine = false,
  });
  factory _MessageReaction.fromJson(Map<String, dynamic> json) =>
      _$MessageReactionFromJson(json);

  @override
  final String emoji;
  @override
  @JsonKey()
  final int count;
  @override
  @JsonKey()
  final bool mine;

  /// Create a copy of MessageReaction
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$MessageReactionCopyWith<_MessageReaction> get copyWith =>
      __$MessageReactionCopyWithImpl<_MessageReaction>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$MessageReactionToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _MessageReaction &&
            (identical(other.emoji, emoji) || other.emoji == emoji) &&
            (identical(other.count, count) || other.count == count) &&
            (identical(other.mine, mine) || other.mine == mine));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, emoji, count, mine);
  }

  @override
  String toString() {
    return 'MessageReaction(emoji: $emoji, count: $count, mine: $mine)';
  }
}

/// @nodoc
abstract mixin class _$MessageReactionCopyWith<$Res>
    implements $MessageReactionCopyWith<$Res> {
  factory _$MessageReactionCopyWith(
    _MessageReaction value,
    $Res Function(_MessageReaction) _then,
  ) = __$MessageReactionCopyWithImpl;
  @override
  @useResult
  $Res call({String emoji, int count, bool mine});
}

/// @nodoc
class __$MessageReactionCopyWithImpl<$Res>
    implements _$MessageReactionCopyWith<$Res> {
  __$MessageReactionCopyWithImpl(this._self, this._then);

  final _MessageReaction _self;
  final $Res Function(_MessageReaction) _then;

  /// Create a copy of MessageReaction
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? emoji = null, Object? count = null, Object? mine = null}) {
    return _then(
      _MessageReaction(
        emoji: null == emoji
            ? _self.emoji
            : emoji // ignore: cast_nullable_to_non_nullable
                  as String,
        count: null == count
            ? _self.count
            : count // ignore: cast_nullable_to_non_nullable
                  as int,
        mine: null == mine
            ? _self.mine
            : mine // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// @nodoc
mixin _$Message {
  String get id;
  String get conversationId;
  PersonRef? get sender;
  String get type;
  String? get body;
  Map<String, dynamic> get meta;
  List<MessageAttachment> get attachments;
  MessageReplyTo? get replyTo;
  List<MessageReaction> get reactions;
  String? get channel;
  DateTime? get editedAt;
  DateTime get createdAt;
  bool get deleted;

  /// Create a copy of Message
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $MessageCopyWith<Message> get copyWith =>
      _$MessageCopyWithImpl<Message>(this as Message, _$identity);

  /// Serializes this Message to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Message;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Message &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.conversationId, _this.conversationId) ||
                other.conversationId == _this.conversationId) &&
            (identical(other.sender, _this.sender) ||
                other.sender == _this.sender) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            (identical(other.body, _this.body) || other.body == _this.body) &&
            const DeepCollectionEquality().equals(other.meta, _this.meta) &&
            const DeepCollectionEquality().equals(
              other.attachments,
              _this.attachments,
            ) &&
            (identical(other.replyTo, _this.replyTo) ||
                other.replyTo == _this.replyTo) &&
            const DeepCollectionEquality().equals(
              other.reactions,
              _this.reactions,
            ) &&
            (identical(other.channel, _this.channel) ||
                other.channel == _this.channel) &&
            (identical(other.editedAt, _this.editedAt) ||
                other.editedAt == _this.editedAt) &&
            (identical(other.createdAt, _this.createdAt) ||
                other.createdAt == _this.createdAt) &&
            (identical(other.deleted, _this.deleted) ||
                other.deleted == _this.deleted));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Message;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.conversationId,
      _this.sender,
      _this.type,
      _this.body,
      const DeepCollectionEquality().hash(_this.meta),
      const DeepCollectionEquality().hash(_this.attachments),
      _this.replyTo,
      const DeepCollectionEquality().hash(_this.reactions),
      _this.channel,
      _this.editedAt,
      _this.createdAt,
      _this.deleted,
    );
  }

  @override
  String toString() {
    final _this = this as Message;
    return 'Message(id: ${_this.id}, conversationId: ${_this.conversationId}, sender: ${_this.sender}, type: ${_this.type}, body: ${_this.body}, meta: ${_this.meta}, attachments: ${_this.attachments}, replyTo: ${_this.replyTo}, reactions: ${_this.reactions}, channel: ${_this.channel}, editedAt: ${_this.editedAt}, createdAt: ${_this.createdAt}, deleted: ${_this.deleted})';
  }
}

/// @nodoc
abstract mixin class $MessageCopyWith<$Res> {
  factory $MessageCopyWith(Message value, $Res Function(Message) _then) =
      _$MessageCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String conversationId,
    PersonRef? sender,
    String type,
    String? body,
    Map<String, dynamic> meta,
    List<MessageAttachment> attachments,
    MessageReplyTo? replyTo,
    List<MessageReaction> reactions,
    String? channel,
    DateTime? editedAt,
    DateTime createdAt,
    bool deleted,
  });

  $PersonRefCopyWith<$Res>? get sender;
  $MessageReplyToCopyWith<$Res>? get replyTo;
}

/// @nodoc
class _$MessageCopyWithImpl<$Res> implements $MessageCopyWith<$Res> {
  _$MessageCopyWithImpl(this._self, this._then);

  final Message _self;
  final $Res Function(Message) _then;

  /// Create a copy of Message
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? conversationId = null,
    Object? sender = freezed,
    Object? type = null,
    Object? body = freezed,
    Object? meta = null,
    Object? attachments = null,
    Object? replyTo = freezed,
    Object? reactions = null,
    Object? channel = freezed,
    Object? editedAt = freezed,
    Object? createdAt = null,
    Object? deleted = null,
  }) {
    return _then(
      Message(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        conversationId: null == conversationId
            ? _self.conversationId
            : conversationId // ignore: cast_nullable_to_non_nullable
                  as String,
        sender: freezed == sender
            ? _self.sender
            : sender // ignore: cast_nullable_to_non_nullable
                  as PersonRef?,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        body: freezed == body
            ? _self.body
            : body // ignore: cast_nullable_to_non_nullable
                  as String?,
        meta: null == meta
            ? _self.meta
            : meta // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>,
        attachments: null == attachments
            ? _self.attachments
            : attachments // ignore: cast_nullable_to_non_nullable
                  as List<MessageAttachment>,
        replyTo: freezed == replyTo
            ? _self.replyTo
            : replyTo // ignore: cast_nullable_to_non_nullable
                  as MessageReplyTo?,
        reactions: null == reactions
            ? _self.reactions
            : reactions // ignore: cast_nullable_to_non_nullable
                  as List<MessageReaction>,
        channel: freezed == channel
            ? _self.channel
            : channel // ignore: cast_nullable_to_non_nullable
                  as String?,
        editedAt: freezed == editedAt
            ? _self.editedAt
            : editedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdAt: null == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        deleted: null == deleted
            ? _self.deleted
            : deleted // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }

  /// Create a copy of Message
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $PersonRefCopyWith<$Res>? get sender {
    if (_self.sender == null) {
      return null;
    }

    return $PersonRefCopyWith<$Res>(_self.sender!, (value) {
      return _then(_self.copyWith(sender: value));
    });
  }

  /// Create a copy of Message
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MessageReplyToCopyWith<$Res>? get replyTo {
    if (_self.replyTo == null) {
      return null;
    }

    return $MessageReplyToCopyWith<$Res>(_self.replyTo!, (value) {
      return _then(_self.copyWith(replyTo: value));
    });
  }
}

/// Adds pattern-matching-related methods to [Message].
extension MessagePatterns on Message {
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
    TResult Function(_Message value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Message() when $default != null:
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
    TResult Function(_Message value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Message():
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
    TResult? Function(_Message value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Message() when $default != null:
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
      String conversationId,
      PersonRef? sender,
      String type,
      String? body,
      Map<String, dynamic> meta,
      List<MessageAttachment> attachments,
      MessageReplyTo? replyTo,
      List<MessageReaction> reactions,
      String? channel,
      DateTime? editedAt,
      DateTime createdAt,
      bool deleted,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Message() when $default != null:
        return $default(
          _that.id,
          _that.conversationId,
          _that.sender,
          _that.type,
          _that.body,
          _that.meta,
          _that.attachments,
          _that.replyTo,
          _that.reactions,
          _that.channel,
          _that.editedAt,
          _that.createdAt,
          _that.deleted,
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
      String conversationId,
      PersonRef? sender,
      String type,
      String? body,
      Map<String, dynamic> meta,
      List<MessageAttachment> attachments,
      MessageReplyTo? replyTo,
      List<MessageReaction> reactions,
      String? channel,
      DateTime? editedAt,
      DateTime createdAt,
      bool deleted,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Message():
        return $default(
          _that.id,
          _that.conversationId,
          _that.sender,
          _that.type,
          _that.body,
          _that.meta,
          _that.attachments,
          _that.replyTo,
          _that.reactions,
          _that.channel,
          _that.editedAt,
          _that.createdAt,
          _that.deleted,
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
      String conversationId,
      PersonRef? sender,
      String type,
      String? body,
      Map<String, dynamic> meta,
      List<MessageAttachment> attachments,
      MessageReplyTo? replyTo,
      List<MessageReaction> reactions,
      String? channel,
      DateTime? editedAt,
      DateTime createdAt,
      bool deleted,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Message() when $default != null:
        return $default(
          _that.id,
          _that.conversationId,
          _that.sender,
          _that.type,
          _that.body,
          _that.meta,
          _that.attachments,
          _that.replyTo,
          _that.reactions,
          _that.channel,
          _that.editedAt,
          _that.createdAt,
          _that.deleted,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Message implements Message {
  const _Message({
    required this.id,
    required this.conversationId,
    this.sender,
    this.type = 'text',
    this.body,
    Map<String, dynamic> meta = const <String, dynamic>{},
    List<MessageAttachment> attachments = const <MessageAttachment>[],
    this.replyTo,
    List<MessageReaction> reactions = const <MessageReaction>[],
    this.channel,
    this.editedAt,
    required this.createdAt,
    this.deleted = false,
  }) : _meta = meta,
       _attachments = attachments,
       _reactions = reactions;
  factory _Message.fromJson(Map<String, dynamic> json) =>
      _$MessageFromJson(json);

  @override
  final String id;
  @override
  final String conversationId;
  @override
  final PersonRef? sender;
  @override
  @JsonKey()
  final String type;
  @override
  final String? body;
  final Map<String, dynamic> _meta;
  @override
  @JsonKey()
  Map<String, dynamic> get meta {
    if (_meta is EqualUnmodifiableMapView) return _meta;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(_meta);
  }

  final List<MessageAttachment> _attachments;
  @override
  @JsonKey()
  List<MessageAttachment> get attachments {
    if (_attachments is EqualUnmodifiableListView) return _attachments;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_attachments);
  }

  @override
  final MessageReplyTo? replyTo;
  final List<MessageReaction> _reactions;
  @override
  @JsonKey()
  List<MessageReaction> get reactions {
    if (_reactions is EqualUnmodifiableListView) return _reactions;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_reactions);
  }

  @override
  final String? channel;
  @override
  final DateTime? editedAt;
  @override
  final DateTime createdAt;
  @override
  @JsonKey()
  final bool deleted;

  /// Create a copy of Message
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$MessageCopyWith<_Message> get copyWith =>
      __$MessageCopyWithImpl<_Message>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$MessageToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Message &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.conversationId, conversationId) ||
                other.conversationId == conversationId) &&
            (identical(other.sender, sender) || other.sender == sender) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.body, body) || other.body == body) &&
            const DeepCollectionEquality().equals(other.meta, _meta) &&
            const DeepCollectionEquality().equals(
              other.attachments,
              _attachments,
            ) &&
            (identical(other.replyTo, replyTo) || other.replyTo == replyTo) &&
            const DeepCollectionEquality().equals(
              other.reactions,
              _reactions,
            ) &&
            (identical(other.channel, channel) || other.channel == channel) &&
            (identical(other.editedAt, editedAt) ||
                other.editedAt == editedAt) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.deleted, deleted) || other.deleted == deleted));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      conversationId,
      sender,
      type,
      body,
      const DeepCollectionEquality().hash(_meta),
      const DeepCollectionEquality().hash(_attachments),
      replyTo,
      const DeepCollectionEquality().hash(_reactions),
      channel,
      editedAt,
      createdAt,
      deleted,
    );
  }

  @override
  String toString() {
    return 'Message(id: $id, conversationId: $conversationId, sender: $sender, type: $type, body: $body, meta: $meta, attachments: $attachments, replyTo: $replyTo, reactions: $reactions, channel: $channel, editedAt: $editedAt, createdAt: $createdAt, deleted: $deleted)';
  }
}

/// @nodoc
abstract mixin class _$MessageCopyWith<$Res> implements $MessageCopyWith<$Res> {
  factory _$MessageCopyWith(_Message value, $Res Function(_Message) _then) =
      __$MessageCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String conversationId,
    PersonRef? sender,
    String type,
    String? body,
    Map<String, dynamic> meta,
    List<MessageAttachment> attachments,
    MessageReplyTo? replyTo,
    List<MessageReaction> reactions,
    String? channel,
    DateTime? editedAt,
    DateTime createdAt,
    bool deleted,
  });

  @override
  $PersonRefCopyWith<$Res>? get sender;
  @override
  $MessageReplyToCopyWith<$Res>? get replyTo;
}

/// @nodoc
class __$MessageCopyWithImpl<$Res> implements _$MessageCopyWith<$Res> {
  __$MessageCopyWithImpl(this._self, this._then);

  final _Message _self;
  final $Res Function(_Message) _then;

  /// Create a copy of Message
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? conversationId = null,
    Object? sender = freezed,
    Object? type = null,
    Object? body = freezed,
    Object? meta = null,
    Object? attachments = null,
    Object? replyTo = freezed,
    Object? reactions = null,
    Object? channel = freezed,
    Object? editedAt = freezed,
    Object? createdAt = null,
    Object? deleted = null,
  }) {
    return _then(
      _Message(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        conversationId: null == conversationId
            ? _self.conversationId
            : conversationId // ignore: cast_nullable_to_non_nullable
                  as String,
        sender: freezed == sender
            ? _self.sender
            : sender // ignore: cast_nullable_to_non_nullable
                  as PersonRef?,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        body: freezed == body
            ? _self.body
            : body // ignore: cast_nullable_to_non_nullable
                  as String?,
        meta: null == meta
            ? _self._meta
            : meta // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>,
        attachments: null == attachments
            ? _self._attachments
            : attachments // ignore: cast_nullable_to_non_nullable
                  as List<MessageAttachment>,
        replyTo: freezed == replyTo
            ? _self.replyTo
            : replyTo // ignore: cast_nullable_to_non_nullable
                  as MessageReplyTo?,
        reactions: null == reactions
            ? _self._reactions
            : reactions // ignore: cast_nullable_to_non_nullable
                  as List<MessageReaction>,
        channel: freezed == channel
            ? _self.channel
            : channel // ignore: cast_nullable_to_non_nullable
                  as String?,
        editedAt: freezed == editedAt
            ? _self.editedAt
            : editedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        createdAt: null == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        deleted: null == deleted
            ? _self.deleted
            : deleted // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }

  /// Create a copy of Message
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $PersonRefCopyWith<$Res>? get sender {
    if (_self.sender == null) {
      return null;
    }

    return $PersonRefCopyWith<$Res>(_self.sender!, (value) {
      return _then(_self.copyWith(sender: value));
    });
  }

  /// Create a copy of Message
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MessageReplyToCopyWith<$Res>? get replyTo {
    if (_self.replyTo == null) {
      return null;
    }

    return $MessageReplyToCopyWith<$Res>(_self.replyTo!, (value) {
      return _then(_self.copyWith(replyTo: value));
    });
  }
}

/// @nodoc
mixin _$Conversation {
  String get id;
  String get type;
  String? get title;
  List<Participant> get participants;
  Message? get lastMessage;
  int get unreadCount;
  bool get isMuted;
  DateTime? get updatedAt;

  /// Create a copy of Conversation
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ConversationCopyWith<Conversation> get copyWith =>
      _$ConversationCopyWithImpl<Conversation>(
        this as Conversation,
        _$identity,
      );

  /// Serializes this Conversation to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Conversation;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Conversation &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            (identical(other.title, _this.title) ||
                other.title == _this.title) &&
            const DeepCollectionEquality().equals(
              other.participants,
              _this.participants,
            ) &&
            (identical(other.lastMessage, _this.lastMessage) ||
                other.lastMessage == _this.lastMessage) &&
            (identical(other.unreadCount, _this.unreadCount) ||
                other.unreadCount == _this.unreadCount) &&
            (identical(other.isMuted, _this.isMuted) ||
                other.isMuted == _this.isMuted) &&
            (identical(other.updatedAt, _this.updatedAt) ||
                other.updatedAt == _this.updatedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Conversation;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.type,
      _this.title,
      const DeepCollectionEquality().hash(_this.participants),
      _this.lastMessage,
      _this.unreadCount,
      _this.isMuted,
      _this.updatedAt,
    );
  }

  @override
  String toString() {
    final _this = this as Conversation;
    return 'Conversation(id: ${_this.id}, type: ${_this.type}, title: ${_this.title}, participants: ${_this.participants}, lastMessage: ${_this.lastMessage}, unreadCount: ${_this.unreadCount}, isMuted: ${_this.isMuted}, updatedAt: ${_this.updatedAt})';
  }
}

/// @nodoc
abstract mixin class $ConversationCopyWith<$Res> {
  factory $ConversationCopyWith(
    Conversation value,
    $Res Function(Conversation) _then,
  ) = _$ConversationCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String type,
    String? title,
    List<Participant> participants,
    Message? lastMessage,
    int unreadCount,
    bool isMuted,
    DateTime? updatedAt,
  });

  $MessageCopyWith<$Res>? get lastMessage;
}

/// @nodoc
class _$ConversationCopyWithImpl<$Res> implements $ConversationCopyWith<$Res> {
  _$ConversationCopyWithImpl(this._self, this._then);

  final Conversation _self;
  final $Res Function(Conversation) _then;

  /// Create a copy of Conversation
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? title = freezed,
    Object? participants = null,
    Object? lastMessage = freezed,
    Object? unreadCount = null,
    Object? isMuted = null,
    Object? updatedAt = freezed,
  }) {
    return _then(
      Conversation(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        title: freezed == title
            ? _self.title
            : title // ignore: cast_nullable_to_non_nullable
                  as String?,
        participants: null == participants
            ? _self.participants
            : participants // ignore: cast_nullable_to_non_nullable
                  as List<Participant>,
        lastMessage: freezed == lastMessage
            ? _self.lastMessage
            : lastMessage // ignore: cast_nullable_to_non_nullable
                  as Message?,
        unreadCount: null == unreadCount
            ? _self.unreadCount
            : unreadCount // ignore: cast_nullable_to_non_nullable
                  as int,
        isMuted: null == isMuted
            ? _self.isMuted
            : isMuted // ignore: cast_nullable_to_non_nullable
                  as bool,
        updatedAt: freezed == updatedAt
            ? _self.updatedAt
            : updatedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of Conversation
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MessageCopyWith<$Res>? get lastMessage {
    if (_self.lastMessage == null) {
      return null;
    }

    return $MessageCopyWith<$Res>(_self.lastMessage!, (value) {
      return _then(_self.copyWith(lastMessage: value));
    });
  }
}

/// Adds pattern-matching-related methods to [Conversation].
extension ConversationPatterns on Conversation {
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
    TResult Function(_Conversation value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Conversation() when $default != null:
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
    TResult Function(_Conversation value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Conversation():
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
    TResult? Function(_Conversation value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Conversation() when $default != null:
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
      String? title,
      List<Participant> participants,
      Message? lastMessage,
      int unreadCount,
      bool isMuted,
      DateTime? updatedAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Conversation() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.title,
          _that.participants,
          _that.lastMessage,
          _that.unreadCount,
          _that.isMuted,
          _that.updatedAt,
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
      String? title,
      List<Participant> participants,
      Message? lastMessage,
      int unreadCount,
      bool isMuted,
      DateTime? updatedAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Conversation():
        return $default(
          _that.id,
          _that.type,
          _that.title,
          _that.participants,
          _that.lastMessage,
          _that.unreadCount,
          _that.isMuted,
          _that.updatedAt,
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
      String? title,
      List<Participant> participants,
      Message? lastMessage,
      int unreadCount,
      bool isMuted,
      DateTime? updatedAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Conversation() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.title,
          _that.participants,
          _that.lastMessage,
          _that.unreadCount,
          _that.isMuted,
          _that.updatedAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Conversation implements Conversation {
  const _Conversation({
    required this.id,
    this.type = 'direct',
    this.title,
    List<Participant> participants = const <Participant>[],
    this.lastMessage,
    this.unreadCount = 0,
    this.isMuted = false,
    this.updatedAt,
  }) : _participants = participants;
  factory _Conversation.fromJson(Map<String, dynamic> json) =>
      _$ConversationFromJson(json);

  @override
  final String id;
  @override
  @JsonKey()
  final String type;
  @override
  final String? title;
  final List<Participant> _participants;
  @override
  @JsonKey()
  List<Participant> get participants {
    if (_participants is EqualUnmodifiableListView) return _participants;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_participants);
  }

  @override
  final Message? lastMessage;
  @override
  @JsonKey()
  final int unreadCount;
  @override
  @JsonKey()
  final bool isMuted;
  @override
  final DateTime? updatedAt;

  /// Create a copy of Conversation
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ConversationCopyWith<_Conversation> get copyWith =>
      __$ConversationCopyWithImpl<_Conversation>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ConversationToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Conversation &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.title, title) || other.title == title) &&
            const DeepCollectionEquality().equals(
              other.participants,
              _participants,
            ) &&
            (identical(other.lastMessage, lastMessage) ||
                other.lastMessage == lastMessage) &&
            (identical(other.unreadCount, unreadCount) ||
                other.unreadCount == unreadCount) &&
            (identical(other.isMuted, isMuted) || other.isMuted == isMuted) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      type,
      title,
      const DeepCollectionEquality().hash(_participants),
      lastMessage,
      unreadCount,
      isMuted,
      updatedAt,
    );
  }

  @override
  String toString() {
    return 'Conversation(id: $id, type: $type, title: $title, participants: $participants, lastMessage: $lastMessage, unreadCount: $unreadCount, isMuted: $isMuted, updatedAt: $updatedAt)';
  }
}

/// @nodoc
abstract mixin class _$ConversationCopyWith<$Res>
    implements $ConversationCopyWith<$Res> {
  factory _$ConversationCopyWith(
    _Conversation value,
    $Res Function(_Conversation) _then,
  ) = __$ConversationCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String type,
    String? title,
    List<Participant> participants,
    Message? lastMessage,
    int unreadCount,
    bool isMuted,
    DateTime? updatedAt,
  });

  @override
  $MessageCopyWith<$Res>? get lastMessage;
}

/// @nodoc
class __$ConversationCopyWithImpl<$Res>
    implements _$ConversationCopyWith<$Res> {
  __$ConversationCopyWithImpl(this._self, this._then);

  final _Conversation _self;
  final $Res Function(_Conversation) _then;

  /// Create a copy of Conversation
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? title = freezed,
    Object? participants = null,
    Object? lastMessage = freezed,
    Object? unreadCount = null,
    Object? isMuted = null,
    Object? updatedAt = freezed,
  }) {
    return _then(
      _Conversation(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        title: freezed == title
            ? _self.title
            : title // ignore: cast_nullable_to_non_nullable
                  as String?,
        participants: null == participants
            ? _self._participants
            : participants // ignore: cast_nullable_to_non_nullable
                  as List<Participant>,
        lastMessage: freezed == lastMessage
            ? _self.lastMessage
            : lastMessage // ignore: cast_nullable_to_non_nullable
                  as Message?,
        unreadCount: null == unreadCount
            ? _self.unreadCount
            : unreadCount // ignore: cast_nullable_to_non_nullable
                  as int,
        isMuted: null == isMuted
            ? _self.isMuted
            : isMuted // ignore: cast_nullable_to_non_nullable
                  as bool,
        updatedAt: freezed == updatedAt
            ? _self.updatedAt
            : updatedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of Conversation
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $MessageCopyWith<$Res>? get lastMessage {
    if (_self.lastMessage == null) {
      return null;
    }

    return $MessageCopyWith<$Res>(_self.lastMessage!, (value) {
      return _then(_self.copyWith(lastMessage: value));
    });
  }
}
