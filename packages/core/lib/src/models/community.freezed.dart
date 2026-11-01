// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'community.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$CommunitySpace {
  String get id;
  String get name;
  String? get description;
  bool get announcementOnly;

  /// Create a copy of CommunitySpace
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $CommunitySpaceCopyWith<CommunitySpace> get copyWith =>
      _$CommunitySpaceCopyWithImpl<CommunitySpace>(
        this as CommunitySpace,
        _$identity,
      );

  /// Serializes this CommunitySpace to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as CommunitySpace;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is CommunitySpace &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.description, _this.description) ||
                other.description == _this.description) &&
            (identical(other.announcementOnly, _this.announcementOnly) ||
                other.announcementOnly == _this.announcementOnly));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as CommunitySpace;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.name,
      _this.description,
      _this.announcementOnly,
    );
  }

  @override
  String toString() {
    final _this = this as CommunitySpace;
    return 'CommunitySpace(id: ${_this.id}, name: ${_this.name}, description: ${_this.description}, announcementOnly: ${_this.announcementOnly})';
  }
}

/// @nodoc
abstract mixin class $CommunitySpaceCopyWith<$Res> {
  factory $CommunitySpaceCopyWith(
    CommunitySpace value,
    $Res Function(CommunitySpace) _then,
  ) = _$CommunitySpaceCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String name,
    String? description,
    bool announcementOnly,
  });
}

/// @nodoc
class _$CommunitySpaceCopyWithImpl<$Res>
    implements $CommunitySpaceCopyWith<$Res> {
  _$CommunitySpaceCopyWithImpl(this._self, this._then);

  final CommunitySpace _self;
  final $Res Function(CommunitySpace) _then;

  /// Create a copy of CommunitySpace
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = freezed,
    Object? announcementOnly = null,
  }) {
    return _then(
      CommunitySpace(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        description: freezed == description
            ? _self.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String?,
        announcementOnly: null == announcementOnly
            ? _self.announcementOnly
            : announcementOnly // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [CommunitySpace].
extension CommunitySpacePatterns on CommunitySpace {
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
    TResult Function(_CommunitySpace value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CommunitySpace() when $default != null:
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
    TResult Function(_CommunitySpace value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CommunitySpace():
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
    TResult? Function(_CommunitySpace value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CommunitySpace() when $default != null:
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
      String? description,
      bool announcementOnly,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CommunitySpace() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.announcementOnly,
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
      String? description,
      bool announcementOnly,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CommunitySpace():
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.announcementOnly,
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
      String? description,
      bool announcementOnly,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CommunitySpace() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.announcementOnly,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _CommunitySpace implements CommunitySpace {
  const _CommunitySpace({
    required this.id,
    required this.name,
    this.description,
    this.announcementOnly = false,
  });
  factory _CommunitySpace.fromJson(Map<String, dynamic> json) =>
      _$CommunitySpaceFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String? description;
  @override
  @JsonKey()
  final bool announcementOnly;

  /// Create a copy of CommunitySpace
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$CommunitySpaceCopyWith<_CommunitySpace> get copyWith =>
      __$CommunitySpaceCopyWithImpl<_CommunitySpace>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$CommunitySpaceToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _CommunitySpace &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.announcementOnly, announcementOnly) ||
                other.announcementOnly == announcementOnly));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, name, description, announcementOnly);
  }

  @override
  String toString() {
    return 'CommunitySpace(id: $id, name: $name, description: $description, announcementOnly: $announcementOnly)';
  }
}

/// @nodoc
abstract mixin class _$CommunitySpaceCopyWith<$Res>
    implements $CommunitySpaceCopyWith<$Res> {
  factory _$CommunitySpaceCopyWith(
    _CommunitySpace value,
    $Res Function(_CommunitySpace) _then,
  ) = __$CommunitySpaceCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String? description,
    bool announcementOnly,
  });
}

/// @nodoc
class __$CommunitySpaceCopyWithImpl<$Res>
    implements _$CommunitySpaceCopyWith<$Res> {
  __$CommunitySpaceCopyWithImpl(this._self, this._then);

  final _CommunitySpace _self;
  final $Res Function(_CommunitySpace) _then;

  /// Create a copy of CommunitySpace
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = freezed,
    Object? announcementOnly = null,
  }) {
    return _then(
      _CommunitySpace(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        description: freezed == description
            ? _self.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String?,
        announcementOnly: null == announcementOnly
            ? _self.announcementOnly
            : announcementOnly // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// @nodoc
mixin _$Post {
  String get id;
  String? get communitySpaceId;
  PersonRef? get author;
  String get type;
  String? get body;
  Map<String, dynamic> get meta;
  bool get isPinned;
  int get commentsCount;
  int get reactionsCount;
  bool get reacted;
  DateTime? get createdAt;

  /// Create a copy of Post
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $PostCopyWith<Post> get copyWith =>
      _$PostCopyWithImpl<Post>(this as Post, _$identity);

  /// Serializes this Post to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Post;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Post &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.communitySpaceId, _this.communitySpaceId) ||
                other.communitySpaceId == _this.communitySpaceId) &&
            (identical(other.author, _this.author) ||
                other.author == _this.author) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            (identical(other.body, _this.body) || other.body == _this.body) &&
            const DeepCollectionEquality().equals(other.meta, _this.meta) &&
            (identical(other.isPinned, _this.isPinned) ||
                other.isPinned == _this.isPinned) &&
            (identical(other.commentsCount, _this.commentsCount) ||
                other.commentsCount == _this.commentsCount) &&
            (identical(other.reactionsCount, _this.reactionsCount) ||
                other.reactionsCount == _this.reactionsCount) &&
            (identical(other.reacted, _this.reacted) ||
                other.reacted == _this.reacted) &&
            (identical(other.createdAt, _this.createdAt) ||
                other.createdAt == _this.createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Post;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.communitySpaceId,
      _this.author,
      _this.type,
      _this.body,
      const DeepCollectionEquality().hash(_this.meta),
      _this.isPinned,
      _this.commentsCount,
      _this.reactionsCount,
      _this.reacted,
      _this.createdAt,
    );
  }

  @override
  String toString() {
    final _this = this as Post;
    return 'Post(id: ${_this.id}, communitySpaceId: ${_this.communitySpaceId}, author: ${_this.author}, type: ${_this.type}, body: ${_this.body}, meta: ${_this.meta}, isPinned: ${_this.isPinned}, commentsCount: ${_this.commentsCount}, reactionsCount: ${_this.reactionsCount}, reacted: ${_this.reacted}, createdAt: ${_this.createdAt})';
  }
}

/// @nodoc
abstract mixin class $PostCopyWith<$Res> {
  factory $PostCopyWith(Post value, $Res Function(Post) _then) =
      _$PostCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String? communitySpaceId,
    PersonRef? author,
    String type,
    String? body,
    Map<String, dynamic> meta,
    bool isPinned,
    int commentsCount,
    int reactionsCount,
    bool reacted,
    DateTime? createdAt,
  });

  $PersonRefCopyWith<$Res>? get author;
}

/// @nodoc
class _$PostCopyWithImpl<$Res> implements $PostCopyWith<$Res> {
  _$PostCopyWithImpl(this._self, this._then);

  final Post _self;
  final $Res Function(Post) _then;

  /// Create a copy of Post
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? communitySpaceId = freezed,
    Object? author = freezed,
    Object? type = null,
    Object? body = freezed,
    Object? meta = null,
    Object? isPinned = null,
    Object? commentsCount = null,
    Object? reactionsCount = null,
    Object? reacted = null,
    Object? createdAt = freezed,
  }) {
    return _then(
      Post(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        communitySpaceId: freezed == communitySpaceId
            ? _self.communitySpaceId
            : communitySpaceId // ignore: cast_nullable_to_non_nullable
                  as String?,
        author: freezed == author
            ? _self.author
            : author // ignore: cast_nullable_to_non_nullable
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
        isPinned: null == isPinned
            ? _self.isPinned
            : isPinned // ignore: cast_nullable_to_non_nullable
                  as bool,
        commentsCount: null == commentsCount
            ? _self.commentsCount
            : commentsCount // ignore: cast_nullable_to_non_nullable
                  as int,
        reactionsCount: null == reactionsCount
            ? _self.reactionsCount
            : reactionsCount // ignore: cast_nullable_to_non_nullable
                  as int,
        reacted: null == reacted
            ? _self.reacted
            : reacted // ignore: cast_nullable_to_non_nullable
                  as bool,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of Post
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $PersonRefCopyWith<$Res>? get author {
    if (_self.author == null) {
      return null;
    }

    return $PersonRefCopyWith<$Res>(_self.author!, (value) {
      return _then(_self.copyWith(author: value));
    });
  }
}

/// Adds pattern-matching-related methods to [Post].
extension PostPatterns on Post {
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
    TResult Function(_Post value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Post() when $default != null:
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
  TResult map<TResult extends Object?>(TResult Function(_Post value) $default) {
    final _that = this;
    switch (_that) {
      case _Post():
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
    TResult? Function(_Post value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Post() when $default != null:
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
      String? communitySpaceId,
      PersonRef? author,
      String type,
      String? body,
      Map<String, dynamic> meta,
      bool isPinned,
      int commentsCount,
      int reactionsCount,
      bool reacted,
      DateTime? createdAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Post() when $default != null:
        return $default(
          _that.id,
          _that.communitySpaceId,
          _that.author,
          _that.type,
          _that.body,
          _that.meta,
          _that.isPinned,
          _that.commentsCount,
          _that.reactionsCount,
          _that.reacted,
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
      String? communitySpaceId,
      PersonRef? author,
      String type,
      String? body,
      Map<String, dynamic> meta,
      bool isPinned,
      int commentsCount,
      int reactionsCount,
      bool reacted,
      DateTime? createdAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Post():
        return $default(
          _that.id,
          _that.communitySpaceId,
          _that.author,
          _that.type,
          _that.body,
          _that.meta,
          _that.isPinned,
          _that.commentsCount,
          _that.reactionsCount,
          _that.reacted,
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
      String? communitySpaceId,
      PersonRef? author,
      String type,
      String? body,
      Map<String, dynamic> meta,
      bool isPinned,
      int commentsCount,
      int reactionsCount,
      bool reacted,
      DateTime? createdAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Post() when $default != null:
        return $default(
          _that.id,
          _that.communitySpaceId,
          _that.author,
          _that.type,
          _that.body,
          _that.meta,
          _that.isPinned,
          _that.commentsCount,
          _that.reactionsCount,
          _that.reacted,
          _that.createdAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Post implements Post {
  const _Post({
    required this.id,
    this.communitySpaceId,
    this.author,
    this.type = 'text',
    this.body,
    Map<String, dynamic> meta = const <String, dynamic>{},
    this.isPinned = false,
    this.commentsCount = 0,
    this.reactionsCount = 0,
    this.reacted = false,
    this.createdAt,
  }) : _meta = meta;
  factory _Post.fromJson(Map<String, dynamic> json) => _$PostFromJson(json);

  @override
  final String id;
  @override
  final String? communitySpaceId;
  @override
  final PersonRef? author;
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

  @override
  @JsonKey()
  final bool isPinned;
  @override
  @JsonKey()
  final int commentsCount;
  @override
  @JsonKey()
  final int reactionsCount;
  @override
  @JsonKey()
  final bool reacted;
  @override
  final DateTime? createdAt;

  /// Create a copy of Post
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$PostCopyWith<_Post> get copyWith =>
      __$PostCopyWithImpl<_Post>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$PostToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Post &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.communitySpaceId, communitySpaceId) ||
                other.communitySpaceId == communitySpaceId) &&
            (identical(other.author, author) || other.author == author) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.body, body) || other.body == body) &&
            const DeepCollectionEquality().equals(other.meta, _meta) &&
            (identical(other.isPinned, isPinned) ||
                other.isPinned == isPinned) &&
            (identical(other.commentsCount, commentsCount) ||
                other.commentsCount == commentsCount) &&
            (identical(other.reactionsCount, reactionsCount) ||
                other.reactionsCount == reactionsCount) &&
            (identical(other.reacted, reacted) || other.reacted == reacted) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      communitySpaceId,
      author,
      type,
      body,
      const DeepCollectionEquality().hash(_meta),
      isPinned,
      commentsCount,
      reactionsCount,
      reacted,
      createdAt,
    );
  }

  @override
  String toString() {
    return 'Post(id: $id, communitySpaceId: $communitySpaceId, author: $author, type: $type, body: $body, meta: $meta, isPinned: $isPinned, commentsCount: $commentsCount, reactionsCount: $reactionsCount, reacted: $reacted, createdAt: $createdAt)';
  }
}

/// @nodoc
abstract mixin class _$PostCopyWith<$Res> implements $PostCopyWith<$Res> {
  factory _$PostCopyWith(_Post value, $Res Function(_Post) _then) =
      __$PostCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String? communitySpaceId,
    PersonRef? author,
    String type,
    String? body,
    Map<String, dynamic> meta,
    bool isPinned,
    int commentsCount,
    int reactionsCount,
    bool reacted,
    DateTime? createdAt,
  });

  @override
  $PersonRefCopyWith<$Res>? get author;
}

/// @nodoc
class __$PostCopyWithImpl<$Res> implements _$PostCopyWith<$Res> {
  __$PostCopyWithImpl(this._self, this._then);

  final _Post _self;
  final $Res Function(_Post) _then;

  /// Create a copy of Post
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? communitySpaceId = freezed,
    Object? author = freezed,
    Object? type = null,
    Object? body = freezed,
    Object? meta = null,
    Object? isPinned = null,
    Object? commentsCount = null,
    Object? reactionsCount = null,
    Object? reacted = null,
    Object? createdAt = freezed,
  }) {
    return _then(
      _Post(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        communitySpaceId: freezed == communitySpaceId
            ? _self.communitySpaceId
            : communitySpaceId // ignore: cast_nullable_to_non_nullable
                  as String?,
        author: freezed == author
            ? _self.author
            : author // ignore: cast_nullable_to_non_nullable
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
        isPinned: null == isPinned
            ? _self.isPinned
            : isPinned // ignore: cast_nullable_to_non_nullable
                  as bool,
        commentsCount: null == commentsCount
            ? _self.commentsCount
            : commentsCount // ignore: cast_nullable_to_non_nullable
                  as int,
        reactionsCount: null == reactionsCount
            ? _self.reactionsCount
            : reactionsCount // ignore: cast_nullable_to_non_nullable
                  as int,
        reacted: null == reacted
            ? _self.reacted
            : reacted // ignore: cast_nullable_to_non_nullable
                  as bool,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of Post
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $PersonRefCopyWith<$Res>? get author {
    if (_self.author == null) {
      return null;
    }

    return $PersonRefCopyWith<$Res>(_self.author!, (value) {
      return _then(_self.copyWith(author: value));
    });
  }
}

/// @nodoc
mixin _$PostComment {
  String get id;
  PersonRef? get author;
  String get body;
  DateTime? get createdAt;

  /// Create a copy of PostComment
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $PostCommentCopyWith<PostComment> get copyWith =>
      _$PostCommentCopyWithImpl<PostComment>(this as PostComment, _$identity);

  /// Serializes this PostComment to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as PostComment;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is PostComment &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.author, _this.author) ||
                other.author == _this.author) &&
            (identical(other.body, _this.body) || other.body == _this.body) &&
            (identical(other.createdAt, _this.createdAt) ||
                other.createdAt == _this.createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as PostComment;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.author,
      _this.body,
      _this.createdAt,
    );
  }

  @override
  String toString() {
    final _this = this as PostComment;
    return 'PostComment(id: ${_this.id}, author: ${_this.author}, body: ${_this.body}, createdAt: ${_this.createdAt})';
  }
}

/// @nodoc
abstract mixin class $PostCommentCopyWith<$Res> {
  factory $PostCommentCopyWith(
    PostComment value,
    $Res Function(PostComment) _then,
  ) = _$PostCommentCopyWithImpl;
  @useResult
  $Res call({String id, PersonRef? author, String body, DateTime? createdAt});

  $PersonRefCopyWith<$Res>? get author;
}

/// @nodoc
class _$PostCommentCopyWithImpl<$Res> implements $PostCommentCopyWith<$Res> {
  _$PostCommentCopyWithImpl(this._self, this._then);

  final PostComment _self;
  final $Res Function(PostComment) _then;

  /// Create a copy of PostComment
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? author = freezed,
    Object? body = null,
    Object? createdAt = freezed,
  }) {
    return _then(
      PostComment(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        author: freezed == author
            ? _self.author
            : author // ignore: cast_nullable_to_non_nullable
                  as PersonRef?,
        body: null == body
            ? _self.body
            : body // ignore: cast_nullable_to_non_nullable
                  as String,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of PostComment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $PersonRefCopyWith<$Res>? get author {
    if (_self.author == null) {
      return null;
    }

    return $PersonRefCopyWith<$Res>(_self.author!, (value) {
      return _then(_self.copyWith(author: value));
    });
  }
}

/// Adds pattern-matching-related methods to [PostComment].
extension PostCommentPatterns on PostComment {
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
    TResult Function(_PostComment value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PostComment() when $default != null:
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
    TResult Function(_PostComment value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PostComment():
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
    TResult? Function(_PostComment value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PostComment() when $default != null:
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
      PersonRef? author,
      String body,
      DateTime? createdAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _PostComment() when $default != null:
        return $default(_that.id, _that.author, _that.body, _that.createdAt);
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
      PersonRef? author,
      String body,
      DateTime? createdAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PostComment():
        return $default(_that.id, _that.author, _that.body, _that.createdAt);
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
      PersonRef? author,
      String body,
      DateTime? createdAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _PostComment() when $default != null:
        return $default(_that.id, _that.author, _that.body, _that.createdAt);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _PostComment implements PostComment {
  const _PostComment({
    required this.id,
    this.author,
    required this.body,
    this.createdAt,
  });
  factory _PostComment.fromJson(Map<String, dynamic> json) =>
      _$PostCommentFromJson(json);

  @override
  final String id;
  @override
  final PersonRef? author;
  @override
  final String body;
  @override
  final DateTime? createdAt;

  /// Create a copy of PostComment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$PostCommentCopyWith<_PostComment> get copyWith =>
      __$PostCommentCopyWithImpl<_PostComment>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$PostCommentToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _PostComment &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.author, author) || other.author == author) &&
            (identical(other.body, body) || other.body == body) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, author, body, createdAt);
  }

  @override
  String toString() {
    return 'PostComment(id: $id, author: $author, body: $body, createdAt: $createdAt)';
  }
}

/// @nodoc
abstract mixin class _$PostCommentCopyWith<$Res>
    implements $PostCommentCopyWith<$Res> {
  factory _$PostCommentCopyWith(
    _PostComment value,
    $Res Function(_PostComment) _then,
  ) = __$PostCommentCopyWithImpl;
  @override
  @useResult
  $Res call({String id, PersonRef? author, String body, DateTime? createdAt});

  @override
  $PersonRefCopyWith<$Res>? get author;
}

/// @nodoc
class __$PostCommentCopyWithImpl<$Res> implements _$PostCommentCopyWith<$Res> {
  __$PostCommentCopyWithImpl(this._self, this._then);

  final _PostComment _self;
  final $Res Function(_PostComment) _then;

  /// Create a copy of PostComment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? author = freezed,
    Object? body = null,
    Object? createdAt = freezed,
  }) {
    return _then(
      _PostComment(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        author: freezed == author
            ? _self.author
            : author // ignore: cast_nullable_to_non_nullable
                  as PersonRef?,
        body: null == body
            ? _self.body
            : body // ignore: cast_nullable_to_non_nullable
                  as String,
        createdAt: freezed == createdAt
            ? _self.createdAt
            : createdAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }

  /// Create a copy of PostComment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $PersonRefCopyWith<$Res>? get author {
    if (_self.author == null) {
      return null;
    }

    return $PersonRefCopyWith<$Res>(_self.author!, (value) {
      return _then(_self.copyWith(author: value));
    });
  }
}

/// @nodoc
mixin _$LeaderboardEntry {
  int get rank;
  PersonRef get user;
  double get score;
  String? get team;
  bool get isMe;

  /// Create a copy of LeaderboardEntry
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $LeaderboardEntryCopyWith<LeaderboardEntry> get copyWith =>
      _$LeaderboardEntryCopyWithImpl<LeaderboardEntry>(
        this as LeaderboardEntry,
        _$identity,
      );

  /// Serializes this LeaderboardEntry to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as LeaderboardEntry;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is LeaderboardEntry &&
            (identical(other.rank, _this.rank) || other.rank == _this.rank) &&
            (identical(other.user, _this.user) || other.user == _this.user) &&
            (identical(other.score, _this.score) ||
                other.score == _this.score) &&
            (identical(other.team, _this.team) || other.team == _this.team) &&
            (identical(other.isMe, _this.isMe) || other.isMe == _this.isMe));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as LeaderboardEntry;
    return Object.hash(
      runtimeType,
      _this.rank,
      _this.user,
      _this.score,
      _this.team,
      _this.isMe,
    );
  }

  @override
  String toString() {
    final _this = this as LeaderboardEntry;
    return 'LeaderboardEntry(rank: ${_this.rank}, user: ${_this.user}, score: ${_this.score}, team: ${_this.team}, isMe: ${_this.isMe})';
  }
}

/// @nodoc
abstract mixin class $LeaderboardEntryCopyWith<$Res> {
  factory $LeaderboardEntryCopyWith(
    LeaderboardEntry value,
    $Res Function(LeaderboardEntry) _then,
  ) = _$LeaderboardEntryCopyWithImpl;
  @useResult
  $Res call({int rank, PersonRef user, double score, String? team, bool isMe});

  $PersonRefCopyWith<$Res> get user;
}

/// @nodoc
class _$LeaderboardEntryCopyWithImpl<$Res>
    implements $LeaderboardEntryCopyWith<$Res> {
  _$LeaderboardEntryCopyWithImpl(this._self, this._then);

  final LeaderboardEntry _self;
  final $Res Function(LeaderboardEntry) _then;

  /// Create a copy of LeaderboardEntry
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? rank = null,
    Object? user = null,
    Object? score = null,
    Object? team = freezed,
    Object? isMe = null,
  }) {
    return _then(
      LeaderboardEntry(
        rank: null == rank
            ? _self.rank
            : rank // ignore: cast_nullable_to_non_nullable
                  as int,
        user: null == user
            ? _self.user
            : user // ignore: cast_nullable_to_non_nullable
                  as PersonRef,
        score: null == score
            ? _self.score
            : score // ignore: cast_nullable_to_non_nullable
                  as double,
        team: freezed == team
            ? _self.team
            : team // ignore: cast_nullable_to_non_nullable
                  as String?,
        isMe: null == isMe
            ? _self.isMe
            : isMe // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }

  /// Create a copy of LeaderboardEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $PersonRefCopyWith<$Res> get user {
    return $PersonRefCopyWith<$Res>(_self.user, (value) {
      return _then(_self.copyWith(user: value));
    });
  }
}

/// Adds pattern-matching-related methods to [LeaderboardEntry].
extension LeaderboardEntryPatterns on LeaderboardEntry {
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
    TResult Function(_LeaderboardEntry value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _LeaderboardEntry() when $default != null:
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
    TResult Function(_LeaderboardEntry value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LeaderboardEntry():
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
    TResult? Function(_LeaderboardEntry value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LeaderboardEntry() when $default != null:
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
      int rank,
      PersonRef user,
      double score,
      String? team,
      bool isMe,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _LeaderboardEntry() when $default != null:
        return $default(
          _that.rank,
          _that.user,
          _that.score,
          _that.team,
          _that.isMe,
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
      int rank,
      PersonRef user,
      double score,
      String? team,
      bool isMe,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LeaderboardEntry():
        return $default(
          _that.rank,
          _that.user,
          _that.score,
          _that.team,
          _that.isMe,
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
      int rank,
      PersonRef user,
      double score,
      String? team,
      bool isMe,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LeaderboardEntry() when $default != null:
        return $default(
          _that.rank,
          _that.user,
          _that.score,
          _that.team,
          _that.isMe,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _LeaderboardEntry implements LeaderboardEntry {
  const _LeaderboardEntry({
    required this.rank,
    required this.user,
    this.score = 0,
    this.team,
    this.isMe = false,
  });
  factory _LeaderboardEntry.fromJson(Map<String, dynamic> json) =>
      _$LeaderboardEntryFromJson(json);

  @override
  final int rank;
  @override
  final PersonRef user;
  @override
  @JsonKey()
  final double score;
  @override
  final String? team;
  @override
  @JsonKey()
  final bool isMe;

  /// Create a copy of LeaderboardEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$LeaderboardEntryCopyWith<_LeaderboardEntry> get copyWith =>
      __$LeaderboardEntryCopyWithImpl<_LeaderboardEntry>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$LeaderboardEntryToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _LeaderboardEntry &&
            (identical(other.rank, rank) || other.rank == rank) &&
            (identical(other.user, user) || other.user == user) &&
            (identical(other.score, score) || other.score == score) &&
            (identical(other.team, team) || other.team == team) &&
            (identical(other.isMe, isMe) || other.isMe == isMe));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, rank, user, score, team, isMe);
  }

  @override
  String toString() {
    return 'LeaderboardEntry(rank: $rank, user: $user, score: $score, team: $team, isMe: $isMe)';
  }
}

/// @nodoc
abstract mixin class _$LeaderboardEntryCopyWith<$Res>
    implements $LeaderboardEntryCopyWith<$Res> {
  factory _$LeaderboardEntryCopyWith(
    _LeaderboardEntry value,
    $Res Function(_LeaderboardEntry) _then,
  ) = __$LeaderboardEntryCopyWithImpl;
  @override
  @useResult
  $Res call({int rank, PersonRef user, double score, String? team, bool isMe});

  @override
  $PersonRefCopyWith<$Res> get user;
}

/// @nodoc
class __$LeaderboardEntryCopyWithImpl<$Res>
    implements _$LeaderboardEntryCopyWith<$Res> {
  __$LeaderboardEntryCopyWithImpl(this._self, this._then);

  final _LeaderboardEntry _self;
  final $Res Function(_LeaderboardEntry) _then;

  /// Create a copy of LeaderboardEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? rank = null,
    Object? user = null,
    Object? score = null,
    Object? team = freezed,
    Object? isMe = null,
  }) {
    return _then(
      _LeaderboardEntry(
        rank: null == rank
            ? _self.rank
            : rank // ignore: cast_nullable_to_non_nullable
                  as int,
        user: null == user
            ? _self.user
            : user // ignore: cast_nullable_to_non_nullable
                  as PersonRef,
        score: null == score
            ? _self.score
            : score // ignore: cast_nullable_to_non_nullable
                  as double,
        team: freezed == team
            ? _self.team
            : team // ignore: cast_nullable_to_non_nullable
                  as String?,
        isMe: null == isMe
            ? _self.isMe
            : isMe // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }

  /// Create a copy of LeaderboardEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $PersonRefCopyWith<$Res> get user {
    return $PersonRefCopyWith<$Res>(_self.user, (value) {
      return _then(_self.copyWith(user: value));
    });
  }
}

/// @nodoc
mixin _$Challenge {
  String get id;
  String get name;
  String? get description;
  String get metric;
  double? get goal;
  String get startsOn;
  String get endsOn;
  String? get badgeKey;
  bool get joined;
  int get participantsCount;
  double? get myScore;
  int? get myRank;
  List<LeaderboardEntry> get leaderboard;

  /// Create a copy of Challenge
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ChallengeCopyWith<Challenge> get copyWith =>
      _$ChallengeCopyWithImpl<Challenge>(this as Challenge, _$identity);

  /// Serializes this Challenge to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Challenge;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Challenge &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.description, _this.description) ||
                other.description == _this.description) &&
            (identical(other.metric, _this.metric) ||
                other.metric == _this.metric) &&
            (identical(other.goal, _this.goal) || other.goal == _this.goal) &&
            (identical(other.startsOn, _this.startsOn) ||
                other.startsOn == _this.startsOn) &&
            (identical(other.endsOn, _this.endsOn) ||
                other.endsOn == _this.endsOn) &&
            (identical(other.badgeKey, _this.badgeKey) ||
                other.badgeKey == _this.badgeKey) &&
            (identical(other.joined, _this.joined) ||
                other.joined == _this.joined) &&
            (identical(other.participantsCount, _this.participantsCount) ||
                other.participantsCount == _this.participantsCount) &&
            (identical(other.myScore, _this.myScore) ||
                other.myScore == _this.myScore) &&
            (identical(other.myRank, _this.myRank) ||
                other.myRank == _this.myRank) &&
            const DeepCollectionEquality().equals(
              other.leaderboard,
              _this.leaderboard,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Challenge;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.name,
      _this.description,
      _this.metric,
      _this.goal,
      _this.startsOn,
      _this.endsOn,
      _this.badgeKey,
      _this.joined,
      _this.participantsCount,
      _this.myScore,
      _this.myRank,
      const DeepCollectionEquality().hash(_this.leaderboard),
    );
  }

  @override
  String toString() {
    final _this = this as Challenge;
    return 'Challenge(id: ${_this.id}, name: ${_this.name}, description: ${_this.description}, metric: ${_this.metric}, goal: ${_this.goal}, startsOn: ${_this.startsOn}, endsOn: ${_this.endsOn}, badgeKey: ${_this.badgeKey}, joined: ${_this.joined}, participantsCount: ${_this.participantsCount}, myScore: ${_this.myScore}, myRank: ${_this.myRank}, leaderboard: ${_this.leaderboard})';
  }
}

/// @nodoc
abstract mixin class $ChallengeCopyWith<$Res> {
  factory $ChallengeCopyWith(Challenge value, $Res Function(Challenge) _then) =
      _$ChallengeCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String name,
    String? description,
    String metric,
    double? goal,
    String startsOn,
    String endsOn,
    String? badgeKey,
    bool joined,
    int participantsCount,
    double? myScore,
    int? myRank,
    List<LeaderboardEntry> leaderboard,
  });
}

/// @nodoc
class _$ChallengeCopyWithImpl<$Res> implements $ChallengeCopyWith<$Res> {
  _$ChallengeCopyWithImpl(this._self, this._then);

  final Challenge _self;
  final $Res Function(Challenge) _then;

  /// Create a copy of Challenge
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = freezed,
    Object? metric = null,
    Object? goal = freezed,
    Object? startsOn = null,
    Object? endsOn = null,
    Object? badgeKey = freezed,
    Object? joined = null,
    Object? participantsCount = null,
    Object? myScore = freezed,
    Object? myRank = freezed,
    Object? leaderboard = null,
  }) {
    return _then(
      Challenge(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        description: freezed == description
            ? _self.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String?,
        metric: null == metric
            ? _self.metric
            : metric // ignore: cast_nullable_to_non_nullable
                  as String,
        goal: freezed == goal
            ? _self.goal
            : goal // ignore: cast_nullable_to_non_nullable
                  as double?,
        startsOn: null == startsOn
            ? _self.startsOn
            : startsOn // ignore: cast_nullable_to_non_nullable
                  as String,
        endsOn: null == endsOn
            ? _self.endsOn
            : endsOn // ignore: cast_nullable_to_non_nullable
                  as String,
        badgeKey: freezed == badgeKey
            ? _self.badgeKey
            : badgeKey // ignore: cast_nullable_to_non_nullable
                  as String?,
        joined: null == joined
            ? _self.joined
            : joined // ignore: cast_nullable_to_non_nullable
                  as bool,
        participantsCount: null == participantsCount
            ? _self.participantsCount
            : participantsCount // ignore: cast_nullable_to_non_nullable
                  as int,
        myScore: freezed == myScore
            ? _self.myScore
            : myScore // ignore: cast_nullable_to_non_nullable
                  as double?,
        myRank: freezed == myRank
            ? _self.myRank
            : myRank // ignore: cast_nullable_to_non_nullable
                  as int?,
        leaderboard: null == leaderboard
            ? _self.leaderboard
            : leaderboard // ignore: cast_nullable_to_non_nullable
                  as List<LeaderboardEntry>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Challenge].
extension ChallengePatterns on Challenge {
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
    TResult Function(_Challenge value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Challenge() when $default != null:
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
    TResult Function(_Challenge value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Challenge():
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
    TResult? Function(_Challenge value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Challenge() when $default != null:
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
      String? description,
      String metric,
      double? goal,
      String startsOn,
      String endsOn,
      String? badgeKey,
      bool joined,
      int participantsCount,
      double? myScore,
      int? myRank,
      List<LeaderboardEntry> leaderboard,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Challenge() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.metric,
          _that.goal,
          _that.startsOn,
          _that.endsOn,
          _that.badgeKey,
          _that.joined,
          _that.participantsCount,
          _that.myScore,
          _that.myRank,
          _that.leaderboard,
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
      String? description,
      String metric,
      double? goal,
      String startsOn,
      String endsOn,
      String? badgeKey,
      bool joined,
      int participantsCount,
      double? myScore,
      int? myRank,
      List<LeaderboardEntry> leaderboard,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Challenge():
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.metric,
          _that.goal,
          _that.startsOn,
          _that.endsOn,
          _that.badgeKey,
          _that.joined,
          _that.participantsCount,
          _that.myScore,
          _that.myRank,
          _that.leaderboard,
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
      String? description,
      String metric,
      double? goal,
      String startsOn,
      String endsOn,
      String? badgeKey,
      bool joined,
      int participantsCount,
      double? myScore,
      int? myRank,
      List<LeaderboardEntry> leaderboard,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Challenge() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.metric,
          _that.goal,
          _that.startsOn,
          _that.endsOn,
          _that.badgeKey,
          _that.joined,
          _that.participantsCount,
          _that.myScore,
          _that.myRank,
          _that.leaderboard,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDoubleN()
class _Challenge implements Challenge {
  const _Challenge({
    required this.id,
    required this.name,
    this.description,
    required this.metric,
    this.goal,
    required this.startsOn,
    required this.endsOn,
    this.badgeKey,
    this.joined = false,
    this.participantsCount = 0,
    this.myScore,
    this.myRank,
    List<LeaderboardEntry> leaderboard = const <LeaderboardEntry>[],
  }) : _leaderboard = leaderboard;
  factory _Challenge.fromJson(Map<String, dynamic> json) =>
      _$ChallengeFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String? description;
  @override
  final String metric;
  @override
  final double? goal;
  @override
  final String startsOn;
  @override
  final String endsOn;
  @override
  final String? badgeKey;
  @override
  @JsonKey()
  final bool joined;
  @override
  @JsonKey()
  final int participantsCount;
  @override
  final double? myScore;
  @override
  final int? myRank;
  final List<LeaderboardEntry> _leaderboard;
  @override
  @JsonKey()
  List<LeaderboardEntry> get leaderboard {
    if (_leaderboard is EqualUnmodifiableListView) return _leaderboard;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_leaderboard);
  }

  /// Create a copy of Challenge
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ChallengeCopyWith<_Challenge> get copyWith =>
      __$ChallengeCopyWithImpl<_Challenge>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ChallengeToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Challenge &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.metric, metric) || other.metric == metric) &&
            (identical(other.goal, goal) || other.goal == goal) &&
            (identical(other.startsOn, startsOn) ||
                other.startsOn == startsOn) &&
            (identical(other.endsOn, endsOn) || other.endsOn == endsOn) &&
            (identical(other.badgeKey, badgeKey) ||
                other.badgeKey == badgeKey) &&
            (identical(other.joined, joined) || other.joined == joined) &&
            (identical(other.participantsCount, participantsCount) ||
                other.participantsCount == participantsCount) &&
            (identical(other.myScore, myScore) || other.myScore == myScore) &&
            (identical(other.myRank, myRank) || other.myRank == myRank) &&
            const DeepCollectionEquality().equals(
              other.leaderboard,
              _leaderboard,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      name,
      description,
      metric,
      goal,
      startsOn,
      endsOn,
      badgeKey,
      joined,
      participantsCount,
      myScore,
      myRank,
      const DeepCollectionEquality().hash(_leaderboard),
    );
  }

  @override
  String toString() {
    return 'Challenge(id: $id, name: $name, description: $description, metric: $metric, goal: $goal, startsOn: $startsOn, endsOn: $endsOn, badgeKey: $badgeKey, joined: $joined, participantsCount: $participantsCount, myScore: $myScore, myRank: $myRank, leaderboard: $leaderboard)';
  }
}

/// @nodoc
abstract mixin class _$ChallengeCopyWith<$Res>
    implements $ChallengeCopyWith<$Res> {
  factory _$ChallengeCopyWith(
    _Challenge value,
    $Res Function(_Challenge) _then,
  ) = __$ChallengeCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String? description,
    String metric,
    double? goal,
    String startsOn,
    String endsOn,
    String? badgeKey,
    bool joined,
    int participantsCount,
    double? myScore,
    int? myRank,
    List<LeaderboardEntry> leaderboard,
  });
}

/// @nodoc
class __$ChallengeCopyWithImpl<$Res> implements _$ChallengeCopyWith<$Res> {
  __$ChallengeCopyWithImpl(this._self, this._then);

  final _Challenge _self;
  final $Res Function(_Challenge) _then;

  /// Create a copy of Challenge
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = freezed,
    Object? metric = null,
    Object? goal = freezed,
    Object? startsOn = null,
    Object? endsOn = null,
    Object? badgeKey = freezed,
    Object? joined = null,
    Object? participantsCount = null,
    Object? myScore = freezed,
    Object? myRank = freezed,
    Object? leaderboard = null,
  }) {
    return _then(
      _Challenge(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        description: freezed == description
            ? _self.description
            : description // ignore: cast_nullable_to_non_nullable
                  as String?,
        metric: null == metric
            ? _self.metric
            : metric // ignore: cast_nullable_to_non_nullable
                  as String,
        goal: freezed == goal
            ? _self.goal
            : goal // ignore: cast_nullable_to_non_nullable
                  as double?,
        startsOn: null == startsOn
            ? _self.startsOn
            : startsOn // ignore: cast_nullable_to_non_nullable
                  as String,
        endsOn: null == endsOn
            ? _self.endsOn
            : endsOn // ignore: cast_nullable_to_non_nullable
                  as String,
        badgeKey: freezed == badgeKey
            ? _self.badgeKey
            : badgeKey // ignore: cast_nullable_to_non_nullable
                  as String?,
        joined: null == joined
            ? _self.joined
            : joined // ignore: cast_nullable_to_non_nullable
                  as bool,
        participantsCount: null == participantsCount
            ? _self.participantsCount
            : participantsCount // ignore: cast_nullable_to_non_nullable
                  as int,
        myScore: freezed == myScore
            ? _self.myScore
            : myScore // ignore: cast_nullable_to_non_nullable
                  as double?,
        myRank: freezed == myRank
            ? _self.myRank
            : myRank // ignore: cast_nullable_to_non_nullable
                  as int?,
        leaderboard: null == leaderboard
            ? _self._leaderboard
            : leaderboard // ignore: cast_nullable_to_non_nullable
                  as List<LeaderboardEntry>,
      ),
    );
  }
}
