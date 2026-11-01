// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'workouts.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$ExerciseVideo {
  String? get provider;
  String? get url;
  String? get posterUrl;
  String? get previewUrl;
  String? get status;

  /// Create a copy of ExerciseVideo
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ExerciseVideoCopyWith<ExerciseVideo> get copyWith =>
      _$ExerciseVideoCopyWithImpl<ExerciseVideo>(
        this as ExerciseVideo,
        _$identity,
      );

  /// Serializes this ExerciseVideo to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as ExerciseVideo;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is ExerciseVideo &&
            (identical(other.provider, _this.provider) ||
                other.provider == _this.provider) &&
            (identical(other.url, _this.url) || other.url == _this.url) &&
            (identical(other.posterUrl, _this.posterUrl) ||
                other.posterUrl == _this.posterUrl) &&
            (identical(other.previewUrl, _this.previewUrl) ||
                other.previewUrl == _this.previewUrl) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as ExerciseVideo;
    return Object.hash(
      runtimeType,
      _this.provider,
      _this.url,
      _this.posterUrl,
      _this.previewUrl,
      _this.status,
    );
  }

  @override
  String toString() {
    final _this = this as ExerciseVideo;
    return 'ExerciseVideo(provider: ${_this.provider}, url: ${_this.url}, posterUrl: ${_this.posterUrl}, previewUrl: ${_this.previewUrl}, status: ${_this.status})';
  }
}

/// @nodoc
abstract mixin class $ExerciseVideoCopyWith<$Res> {
  factory $ExerciseVideoCopyWith(
    ExerciseVideo value,
    $Res Function(ExerciseVideo) _then,
  ) = _$ExerciseVideoCopyWithImpl;
  @useResult
  $Res call({
    String? provider,
    String? url,
    String? posterUrl,
    String? previewUrl,
    String? status,
  });
}

/// @nodoc
class _$ExerciseVideoCopyWithImpl<$Res>
    implements $ExerciseVideoCopyWith<$Res> {
  _$ExerciseVideoCopyWithImpl(this._self, this._then);

  final ExerciseVideo _self;
  final $Res Function(ExerciseVideo) _then;

  /// Create a copy of ExerciseVideo
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? provider = freezed,
    Object? url = freezed,
    Object? posterUrl = freezed,
    Object? previewUrl = freezed,
    Object? status = freezed,
  }) {
    return _then(
      ExerciseVideo(
        provider: freezed == provider
            ? _self.provider
            : provider // ignore: cast_nullable_to_non_nullable
                  as String?,
        url: freezed == url
            ? _self.url
            : url // ignore: cast_nullable_to_non_nullable
                  as String?,
        posterUrl: freezed == posterUrl
            ? _self.posterUrl
            : posterUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        previewUrl: freezed == previewUrl
            ? _self.previewUrl
            : previewUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        status: freezed == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [ExerciseVideo].
extension ExerciseVideoPatterns on ExerciseVideo {
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
    TResult Function(_ExerciseVideo value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ExerciseVideo() when $default != null:
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
    TResult Function(_ExerciseVideo value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseVideo():
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
    TResult? Function(_ExerciseVideo value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseVideo() when $default != null:
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
      String? provider,
      String? url,
      String? posterUrl,
      String? previewUrl,
      String? status,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ExerciseVideo() when $default != null:
        return $default(
          _that.provider,
          _that.url,
          _that.posterUrl,
          _that.previewUrl,
          _that.status,
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
      String? provider,
      String? url,
      String? posterUrl,
      String? previewUrl,
      String? status,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseVideo():
        return $default(
          _that.provider,
          _that.url,
          _that.posterUrl,
          _that.previewUrl,
          _that.status,
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
      String? provider,
      String? url,
      String? posterUrl,
      String? previewUrl,
      String? status,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ExerciseVideo() when $default != null:
        return $default(
          _that.provider,
          _that.url,
          _that.posterUrl,
          _that.previewUrl,
          _that.status,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _ExerciseVideo implements ExerciseVideo {
  const _ExerciseVideo({
    this.provider,
    this.url,
    this.posterUrl,
    this.previewUrl,
    this.status,
  });
  factory _ExerciseVideo.fromJson(Map<String, dynamic> json) =>
      _$ExerciseVideoFromJson(json);

  @override
  final String? provider;
  @override
  final String? url;
  @override
  final String? posterUrl;
  @override
  final String? previewUrl;
  @override
  final String? status;

  /// Create a copy of ExerciseVideo
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ExerciseVideoCopyWith<_ExerciseVideo> get copyWith =>
      __$ExerciseVideoCopyWithImpl<_ExerciseVideo>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ExerciseVideoToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _ExerciseVideo &&
            (identical(other.provider, provider) ||
                other.provider == provider) &&
            (identical(other.url, url) || other.url == url) &&
            (identical(other.posterUrl, posterUrl) ||
                other.posterUrl == posterUrl) &&
            (identical(other.previewUrl, previewUrl) ||
                other.previewUrl == previewUrl) &&
            (identical(other.status, status) || other.status == status));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      provider,
      url,
      posterUrl,
      previewUrl,
      status,
    );
  }

  @override
  String toString() {
    return 'ExerciseVideo(provider: $provider, url: $url, posterUrl: $posterUrl, previewUrl: $previewUrl, status: $status)';
  }
}

/// @nodoc
abstract mixin class _$ExerciseVideoCopyWith<$Res>
    implements $ExerciseVideoCopyWith<$Res> {
  factory _$ExerciseVideoCopyWith(
    _ExerciseVideo value,
    $Res Function(_ExerciseVideo) _then,
  ) = __$ExerciseVideoCopyWithImpl;
  @override
  @useResult
  $Res call({
    String? provider,
    String? url,
    String? posterUrl,
    String? previewUrl,
    String? status,
  });
}

/// @nodoc
class __$ExerciseVideoCopyWithImpl<$Res>
    implements _$ExerciseVideoCopyWith<$Res> {
  __$ExerciseVideoCopyWithImpl(this._self, this._then);

  final _ExerciseVideo _self;
  final $Res Function(_ExerciseVideo) _then;

  /// Create a copy of ExerciseVideo
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? provider = freezed,
    Object? url = freezed,
    Object? posterUrl = freezed,
    Object? previewUrl = freezed,
    Object? status = freezed,
  }) {
    return _then(
      _ExerciseVideo(
        provider: freezed == provider
            ? _self.provider
            : provider // ignore: cast_nullable_to_non_nullable
                  as String?,
        url: freezed == url
            ? _self.url
            : url // ignore: cast_nullable_to_non_nullable
                  as String?,
        posterUrl: freezed == posterUrl
            ? _self.posterUrl
            : posterUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        previewUrl: freezed == previewUrl
            ? _self.previewUrl
            : previewUrl // ignore: cast_nullable_to_non_nullable
                  as String?,
        status: freezed == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$Exercise {
  String get id;
  String? get slug;
  String get name;
  String? get primaryMuscle;
  List<String> get secondaryMuscles;
  List<String> get equipment;
  String? get pattern;
  String? get mechanics;
  String? get force;
  String? get level;
  bool get isUnilateral;
  bool get isBodyweight;
  List<String> get metrics;
  ExerciseVideo? get video;
  List<String> get instructions;
  List<String> get tips;
  List<String> get mistakes;
  List<IdName> get alternatives;
  bool get isCustom;
  bool get isFavorite;

  /// Create a copy of Exercise
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ExerciseCopyWith<Exercise> get copyWith =>
      _$ExerciseCopyWithImpl<Exercise>(this as Exercise, _$identity);

  /// Serializes this Exercise to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Exercise;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Exercise &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.slug, _this.slug) || other.slug == _this.slug) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.primaryMuscle, _this.primaryMuscle) ||
                other.primaryMuscle == _this.primaryMuscle) &&
            const DeepCollectionEquality().equals(
              other.secondaryMuscles,
              _this.secondaryMuscles,
            ) &&
            const DeepCollectionEquality().equals(
              other.equipment,
              _this.equipment,
            ) &&
            (identical(other.pattern, _this.pattern) ||
                other.pattern == _this.pattern) &&
            (identical(other.mechanics, _this.mechanics) ||
                other.mechanics == _this.mechanics) &&
            (identical(other.force, _this.force) ||
                other.force == _this.force) &&
            (identical(other.level, _this.level) ||
                other.level == _this.level) &&
            (identical(other.isUnilateral, _this.isUnilateral) ||
                other.isUnilateral == _this.isUnilateral) &&
            (identical(other.isBodyweight, _this.isBodyweight) ||
                other.isBodyweight == _this.isBodyweight) &&
            const DeepCollectionEquality().equals(
              other.metrics,
              _this.metrics,
            ) &&
            (identical(other.video, _this.video) ||
                other.video == _this.video) &&
            const DeepCollectionEquality().equals(
              other.instructions,
              _this.instructions,
            ) &&
            const DeepCollectionEquality().equals(other.tips, _this.tips) &&
            const DeepCollectionEquality().equals(
              other.mistakes,
              _this.mistakes,
            ) &&
            const DeepCollectionEquality().equals(
              other.alternatives,
              _this.alternatives,
            ) &&
            (identical(other.isCustom, _this.isCustom) ||
                other.isCustom == _this.isCustom) &&
            (identical(other.isFavorite, _this.isFavorite) ||
                other.isFavorite == _this.isFavorite));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Exercise;
    return Object.hashAll([
      runtimeType,
      _this.id,
      _this.slug,
      _this.name,
      _this.primaryMuscle,
      const DeepCollectionEquality().hash(_this.secondaryMuscles),
      const DeepCollectionEquality().hash(_this.equipment),
      _this.pattern,
      _this.mechanics,
      _this.force,
      _this.level,
      _this.isUnilateral,
      _this.isBodyweight,
      const DeepCollectionEquality().hash(_this.metrics),
      _this.video,
      const DeepCollectionEquality().hash(_this.instructions),
      const DeepCollectionEquality().hash(_this.tips),
      const DeepCollectionEquality().hash(_this.mistakes),
      const DeepCollectionEquality().hash(_this.alternatives),
      _this.isCustom,
      _this.isFavorite,
    ]);
  }

  @override
  String toString() {
    final _this = this as Exercise;
    return 'Exercise(id: ${_this.id}, slug: ${_this.slug}, name: ${_this.name}, primaryMuscle: ${_this.primaryMuscle}, secondaryMuscles: ${_this.secondaryMuscles}, equipment: ${_this.equipment}, pattern: ${_this.pattern}, mechanics: ${_this.mechanics}, force: ${_this.force}, level: ${_this.level}, isUnilateral: ${_this.isUnilateral}, isBodyweight: ${_this.isBodyweight}, metrics: ${_this.metrics}, video: ${_this.video}, instructions: ${_this.instructions}, tips: ${_this.tips}, mistakes: ${_this.mistakes}, alternatives: ${_this.alternatives}, isCustom: ${_this.isCustom}, isFavorite: ${_this.isFavorite})';
  }
}

/// @nodoc
abstract mixin class $ExerciseCopyWith<$Res> {
  factory $ExerciseCopyWith(Exercise value, $Res Function(Exercise) _then) =
      _$ExerciseCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String? slug,
    String name,
    String? primaryMuscle,
    List<String> secondaryMuscles,
    List<String> equipment,
    String? pattern,
    String? mechanics,
    String? force,
    String? level,
    bool isUnilateral,
    bool isBodyweight,
    List<String> metrics,
    ExerciseVideo? video,
    List<String> instructions,
    List<String> tips,
    List<String> mistakes,
    List<IdName> alternatives,
    bool isCustom,
    bool isFavorite,
  });

  $ExerciseVideoCopyWith<$Res>? get video;
}

/// @nodoc
class _$ExerciseCopyWithImpl<$Res> implements $ExerciseCopyWith<$Res> {
  _$ExerciseCopyWithImpl(this._self, this._then);

  final Exercise _self;
  final $Res Function(Exercise) _then;

  /// Create a copy of Exercise
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? slug = freezed,
    Object? name = null,
    Object? primaryMuscle = freezed,
    Object? secondaryMuscles = null,
    Object? equipment = null,
    Object? pattern = freezed,
    Object? mechanics = freezed,
    Object? force = freezed,
    Object? level = freezed,
    Object? isUnilateral = null,
    Object? isBodyweight = null,
    Object? metrics = null,
    Object? video = freezed,
    Object? instructions = null,
    Object? tips = null,
    Object? mistakes = null,
    Object? alternatives = null,
    Object? isCustom = null,
    Object? isFavorite = null,
  }) {
    return _then(
      Exercise(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        slug: freezed == slug
            ? _self.slug
            : slug // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        primaryMuscle: freezed == primaryMuscle
            ? _self.primaryMuscle
            : primaryMuscle // ignore: cast_nullable_to_non_nullable
                  as String?,
        secondaryMuscles: null == secondaryMuscles
            ? _self.secondaryMuscles
            : secondaryMuscles // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        equipment: null == equipment
            ? _self.equipment
            : equipment // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        pattern: freezed == pattern
            ? _self.pattern
            : pattern // ignore: cast_nullable_to_non_nullable
                  as String?,
        mechanics: freezed == mechanics
            ? _self.mechanics
            : mechanics // ignore: cast_nullable_to_non_nullable
                  as String?,
        force: freezed == force
            ? _self.force
            : force // ignore: cast_nullable_to_non_nullable
                  as String?,
        level: freezed == level
            ? _self.level
            : level // ignore: cast_nullable_to_non_nullable
                  as String?,
        isUnilateral: null == isUnilateral
            ? _self.isUnilateral
            : isUnilateral // ignore: cast_nullable_to_non_nullable
                  as bool,
        isBodyweight: null == isBodyweight
            ? _self.isBodyweight
            : isBodyweight // ignore: cast_nullable_to_non_nullable
                  as bool,
        metrics: null == metrics
            ? _self.metrics
            : metrics // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        video: freezed == video
            ? _self.video
            : video // ignore: cast_nullable_to_non_nullable
                  as ExerciseVideo?,
        instructions: null == instructions
            ? _self.instructions
            : instructions // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        tips: null == tips
            ? _self.tips
            : tips // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        mistakes: null == mistakes
            ? _self.mistakes
            : mistakes // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        alternatives: null == alternatives
            ? _self.alternatives
            : alternatives // ignore: cast_nullable_to_non_nullable
                  as List<IdName>,
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

  /// Create a copy of Exercise
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ExerciseVideoCopyWith<$Res>? get video {
    if (_self.video == null) {
      return null;
    }

    return $ExerciseVideoCopyWith<$Res>(_self.video!, (value) {
      return _then(_self.copyWith(video: value));
    });
  }
}

/// Adds pattern-matching-related methods to [Exercise].
extension ExercisePatterns on Exercise {
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
    TResult Function(_Exercise value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Exercise() when $default != null:
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
    TResult Function(_Exercise value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Exercise():
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
    TResult? Function(_Exercise value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Exercise() when $default != null:
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
      String? slug,
      String name,
      String? primaryMuscle,
      List<String> secondaryMuscles,
      List<String> equipment,
      String? pattern,
      String? mechanics,
      String? force,
      String? level,
      bool isUnilateral,
      bool isBodyweight,
      List<String> metrics,
      ExerciseVideo? video,
      List<String> instructions,
      List<String> tips,
      List<String> mistakes,
      List<IdName> alternatives,
      bool isCustom,
      bool isFavorite,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Exercise() when $default != null:
        return $default(
          _that.id,
          _that.slug,
          _that.name,
          _that.primaryMuscle,
          _that.secondaryMuscles,
          _that.equipment,
          _that.pattern,
          _that.mechanics,
          _that.force,
          _that.level,
          _that.isUnilateral,
          _that.isBodyweight,
          _that.metrics,
          _that.video,
          _that.instructions,
          _that.tips,
          _that.mistakes,
          _that.alternatives,
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
      String? slug,
      String name,
      String? primaryMuscle,
      List<String> secondaryMuscles,
      List<String> equipment,
      String? pattern,
      String? mechanics,
      String? force,
      String? level,
      bool isUnilateral,
      bool isBodyweight,
      List<String> metrics,
      ExerciseVideo? video,
      List<String> instructions,
      List<String> tips,
      List<String> mistakes,
      List<IdName> alternatives,
      bool isCustom,
      bool isFavorite,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Exercise():
        return $default(
          _that.id,
          _that.slug,
          _that.name,
          _that.primaryMuscle,
          _that.secondaryMuscles,
          _that.equipment,
          _that.pattern,
          _that.mechanics,
          _that.force,
          _that.level,
          _that.isUnilateral,
          _that.isBodyweight,
          _that.metrics,
          _that.video,
          _that.instructions,
          _that.tips,
          _that.mistakes,
          _that.alternatives,
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
      String? slug,
      String name,
      String? primaryMuscle,
      List<String> secondaryMuscles,
      List<String> equipment,
      String? pattern,
      String? mechanics,
      String? force,
      String? level,
      bool isUnilateral,
      bool isBodyweight,
      List<String> metrics,
      ExerciseVideo? video,
      List<String> instructions,
      List<String> tips,
      List<String> mistakes,
      List<IdName> alternatives,
      bool isCustom,
      bool isFavorite,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Exercise() when $default != null:
        return $default(
          _that.id,
          _that.slug,
          _that.name,
          _that.primaryMuscle,
          _that.secondaryMuscles,
          _that.equipment,
          _that.pattern,
          _that.mechanics,
          _that.force,
          _that.level,
          _that.isUnilateral,
          _that.isBodyweight,
          _that.metrics,
          _that.video,
          _that.instructions,
          _that.tips,
          _that.mistakes,
          _that.alternatives,
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
class _Exercise extends Exercise {
  const _Exercise({
    required this.id,
    this.slug,
    required this.name,
    this.primaryMuscle,
    List<String> secondaryMuscles = const <String>[],
    List<String> equipment = const <String>[],
    this.pattern,
    this.mechanics,
    this.force,
    this.level,
    this.isUnilateral = false,
    this.isBodyweight = false,
    List<String> metrics = const <String>['weight', 'reps'],
    this.video,
    List<String> instructions = const <String>[],
    List<String> tips = const <String>[],
    List<String> mistakes = const <String>[],
    List<IdName> alternatives = const <IdName>[],
    this.isCustom = false,
    this.isFavorite = false,
  }) : _secondaryMuscles = secondaryMuscles,
       _equipment = equipment,
       _metrics = metrics,
       _instructions = instructions,
       _tips = tips,
       _mistakes = mistakes,
       _alternatives = alternatives,
       super._();
  factory _Exercise.fromJson(Map<String, dynamic> json) =>
      _$ExerciseFromJson(json);

  @override
  final String id;
  @override
  final String? slug;
  @override
  final String name;
  @override
  final String? primaryMuscle;
  final List<String> _secondaryMuscles;
  @override
  @JsonKey()
  List<String> get secondaryMuscles {
    if (_secondaryMuscles is EqualUnmodifiableListView)
      return _secondaryMuscles;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_secondaryMuscles);
  }

  final List<String> _equipment;
  @override
  @JsonKey()
  List<String> get equipment {
    if (_equipment is EqualUnmodifiableListView) return _equipment;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_equipment);
  }

  @override
  final String? pattern;
  @override
  final String? mechanics;
  @override
  final String? force;
  @override
  final String? level;
  @override
  @JsonKey()
  final bool isUnilateral;
  @override
  @JsonKey()
  final bool isBodyweight;
  final List<String> _metrics;
  @override
  @JsonKey()
  List<String> get metrics {
    if (_metrics is EqualUnmodifiableListView) return _metrics;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_metrics);
  }

  @override
  final ExerciseVideo? video;
  final List<String> _instructions;
  @override
  @JsonKey()
  List<String> get instructions {
    if (_instructions is EqualUnmodifiableListView) return _instructions;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_instructions);
  }

  final List<String> _tips;
  @override
  @JsonKey()
  List<String> get tips {
    if (_tips is EqualUnmodifiableListView) return _tips;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_tips);
  }

  final List<String> _mistakes;
  @override
  @JsonKey()
  List<String> get mistakes {
    if (_mistakes is EqualUnmodifiableListView) return _mistakes;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_mistakes);
  }

  final List<IdName> _alternatives;
  @override
  @JsonKey()
  List<IdName> get alternatives {
    if (_alternatives is EqualUnmodifiableListView) return _alternatives;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_alternatives);
  }

  @override
  @JsonKey()
  final bool isCustom;
  @override
  @JsonKey()
  final bool isFavorite;

  /// Create a copy of Exercise
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ExerciseCopyWith<_Exercise> get copyWith =>
      __$ExerciseCopyWithImpl<_Exercise>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ExerciseToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Exercise &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.slug, slug) || other.slug == slug) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.primaryMuscle, primaryMuscle) ||
                other.primaryMuscle == primaryMuscle) &&
            const DeepCollectionEquality().equals(
              other.secondaryMuscles,
              _secondaryMuscles,
            ) &&
            const DeepCollectionEquality().equals(
              other.equipment,
              _equipment,
            ) &&
            (identical(other.pattern, pattern) || other.pattern == pattern) &&
            (identical(other.mechanics, mechanics) ||
                other.mechanics == mechanics) &&
            (identical(other.force, force) || other.force == force) &&
            (identical(other.level, level) || other.level == level) &&
            (identical(other.isUnilateral, isUnilateral) ||
                other.isUnilateral == isUnilateral) &&
            (identical(other.isBodyweight, isBodyweight) ||
                other.isBodyweight == isBodyweight) &&
            const DeepCollectionEquality().equals(other.metrics, _metrics) &&
            (identical(other.video, video) || other.video == video) &&
            const DeepCollectionEquality().equals(
              other.instructions,
              _instructions,
            ) &&
            const DeepCollectionEquality().equals(other.tips, _tips) &&
            const DeepCollectionEquality().equals(other.mistakes, _mistakes) &&
            const DeepCollectionEquality().equals(
              other.alternatives,
              _alternatives,
            ) &&
            (identical(other.isCustom, isCustom) ||
                other.isCustom == isCustom) &&
            (identical(other.isFavorite, isFavorite) ||
                other.isFavorite == isFavorite));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hashAll([
      runtimeType,
      id,
      slug,
      name,
      primaryMuscle,
      const DeepCollectionEquality().hash(_secondaryMuscles),
      const DeepCollectionEquality().hash(_equipment),
      pattern,
      mechanics,
      force,
      level,
      isUnilateral,
      isBodyweight,
      const DeepCollectionEquality().hash(_metrics),
      video,
      const DeepCollectionEquality().hash(_instructions),
      const DeepCollectionEquality().hash(_tips),
      const DeepCollectionEquality().hash(_mistakes),
      const DeepCollectionEquality().hash(_alternatives),
      isCustom,
      isFavorite,
    ]);
  }

  @override
  String toString() {
    return 'Exercise(id: $id, slug: $slug, name: $name, primaryMuscle: $primaryMuscle, secondaryMuscles: $secondaryMuscles, equipment: $equipment, pattern: $pattern, mechanics: $mechanics, force: $force, level: $level, isUnilateral: $isUnilateral, isBodyweight: $isBodyweight, metrics: $metrics, video: $video, instructions: $instructions, tips: $tips, mistakes: $mistakes, alternatives: $alternatives, isCustom: $isCustom, isFavorite: $isFavorite)';
  }
}

/// @nodoc
abstract mixin class _$ExerciseCopyWith<$Res>
    implements $ExerciseCopyWith<$Res> {
  factory _$ExerciseCopyWith(_Exercise value, $Res Function(_Exercise) _then) =
      __$ExerciseCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String? slug,
    String name,
    String? primaryMuscle,
    List<String> secondaryMuscles,
    List<String> equipment,
    String? pattern,
    String? mechanics,
    String? force,
    String? level,
    bool isUnilateral,
    bool isBodyweight,
    List<String> metrics,
    ExerciseVideo? video,
    List<String> instructions,
    List<String> tips,
    List<String> mistakes,
    List<IdName> alternatives,
    bool isCustom,
    bool isFavorite,
  });

  @override
  $ExerciseVideoCopyWith<$Res>? get video;
}

/// @nodoc
class __$ExerciseCopyWithImpl<$Res> implements _$ExerciseCopyWith<$Res> {
  __$ExerciseCopyWithImpl(this._self, this._then);

  final _Exercise _self;
  final $Res Function(_Exercise) _then;

  /// Create a copy of Exercise
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? slug = freezed,
    Object? name = null,
    Object? primaryMuscle = freezed,
    Object? secondaryMuscles = null,
    Object? equipment = null,
    Object? pattern = freezed,
    Object? mechanics = freezed,
    Object? force = freezed,
    Object? level = freezed,
    Object? isUnilateral = null,
    Object? isBodyweight = null,
    Object? metrics = null,
    Object? video = freezed,
    Object? instructions = null,
    Object? tips = null,
    Object? mistakes = null,
    Object? alternatives = null,
    Object? isCustom = null,
    Object? isFavorite = null,
  }) {
    return _then(
      _Exercise(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        slug: freezed == slug
            ? _self.slug
            : slug // ignore: cast_nullable_to_non_nullable
                  as String?,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        primaryMuscle: freezed == primaryMuscle
            ? _self.primaryMuscle
            : primaryMuscle // ignore: cast_nullable_to_non_nullable
                  as String?,
        secondaryMuscles: null == secondaryMuscles
            ? _self._secondaryMuscles
            : secondaryMuscles // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        equipment: null == equipment
            ? _self._equipment
            : equipment // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        pattern: freezed == pattern
            ? _self.pattern
            : pattern // ignore: cast_nullable_to_non_nullable
                  as String?,
        mechanics: freezed == mechanics
            ? _self.mechanics
            : mechanics // ignore: cast_nullable_to_non_nullable
                  as String?,
        force: freezed == force
            ? _self.force
            : force // ignore: cast_nullable_to_non_nullable
                  as String?,
        level: freezed == level
            ? _self.level
            : level // ignore: cast_nullable_to_non_nullable
                  as String?,
        isUnilateral: null == isUnilateral
            ? _self.isUnilateral
            : isUnilateral // ignore: cast_nullable_to_non_nullable
                  as bool,
        isBodyweight: null == isBodyweight
            ? _self.isBodyweight
            : isBodyweight // ignore: cast_nullable_to_non_nullable
                  as bool,
        metrics: null == metrics
            ? _self._metrics
            : metrics // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        video: freezed == video
            ? _self.video
            : video // ignore: cast_nullable_to_non_nullable
                  as ExerciseVideo?,
        instructions: null == instructions
            ? _self._instructions
            : instructions // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        tips: null == tips
            ? _self._tips
            : tips // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        mistakes: null == mistakes
            ? _self._mistakes
            : mistakes // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        alternatives: null == alternatives
            ? _self._alternatives
            : alternatives // ignore: cast_nullable_to_non_nullable
                  as List<IdName>,
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

  /// Create a copy of Exercise
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ExerciseVideoCopyWith<$Res>? get video {
    if (_self.video == null) {
      return null;
    }

    return $ExerciseVideoCopyWith<$Res>(_self.video!, (value) {
      return _then(_self.copyWith(video: value));
    });
  }
}

/// @nodoc
mixin _$SetTemplate {
  String? get id;
  int get position;
  @JsonKey(unknownEnumValue: SetType.working)
  SetType get setType;
  int? get repsMin;
  int? get repsMax;
  double? get weightKg;
  @JsonKey(name: 'percent_1rm')
  double? get percent1rm;
  double? get rpe;
  int? get rir;
  int? get durationSeconds;
  double? get distanceM;
  int? get restSeconds;

  /// Create a copy of SetTemplate
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $SetTemplateCopyWith<SetTemplate> get copyWith =>
      _$SetTemplateCopyWithImpl<SetTemplate>(this as SetTemplate, _$identity);

  /// Serializes this SetTemplate to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as SetTemplate;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is SetTemplate &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.position, _this.position) ||
                other.position == _this.position) &&
            (identical(other.setType, _this.setType) ||
                other.setType == _this.setType) &&
            (identical(other.repsMin, _this.repsMin) ||
                other.repsMin == _this.repsMin) &&
            (identical(other.repsMax, _this.repsMax) ||
                other.repsMax == _this.repsMax) &&
            (identical(other.weightKg, _this.weightKg) ||
                other.weightKg == _this.weightKg) &&
            (identical(other.percent1rm, _this.percent1rm) ||
                other.percent1rm == _this.percent1rm) &&
            (identical(other.rpe, _this.rpe) || other.rpe == _this.rpe) &&
            (identical(other.rir, _this.rir) || other.rir == _this.rir) &&
            (identical(other.durationSeconds, _this.durationSeconds) ||
                other.durationSeconds == _this.durationSeconds) &&
            (identical(other.distanceM, _this.distanceM) ||
                other.distanceM == _this.distanceM) &&
            (identical(other.restSeconds, _this.restSeconds) ||
                other.restSeconds == _this.restSeconds));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as SetTemplate;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.position,
      _this.setType,
      _this.repsMin,
      _this.repsMax,
      _this.weightKg,
      _this.percent1rm,
      _this.rpe,
      _this.rir,
      _this.durationSeconds,
      _this.distanceM,
      _this.restSeconds,
    );
  }

  @override
  String toString() {
    final _this = this as SetTemplate;
    return 'SetTemplate(id: ${_this.id}, position: ${_this.position}, setType: ${_this.setType}, repsMin: ${_this.repsMin}, repsMax: ${_this.repsMax}, weightKg: ${_this.weightKg}, percent1rm: ${_this.percent1rm}, rpe: ${_this.rpe}, rir: ${_this.rir}, durationSeconds: ${_this.durationSeconds}, distanceM: ${_this.distanceM}, restSeconds: ${_this.restSeconds})';
  }
}

/// @nodoc
abstract mixin class $SetTemplateCopyWith<$Res> {
  factory $SetTemplateCopyWith(
    SetTemplate value,
    $Res Function(SetTemplate) _then,
  ) = _$SetTemplateCopyWithImpl;
  @useResult
  $Res call({
    String? id,
    int position,
    @JsonKey(unknownEnumValue: SetType.working) SetType setType,
    int? repsMin,
    int? repsMax,
    double? weightKg,
    @JsonKey(name: 'percent_1rm') double? percent1rm,
    double? rpe,
    int? rir,
    int? durationSeconds,
    double? distanceM,
    int? restSeconds,
  });
}

/// @nodoc
class _$SetTemplateCopyWithImpl<$Res> implements $SetTemplateCopyWith<$Res> {
  _$SetTemplateCopyWithImpl(this._self, this._then);

  final SetTemplate _self;
  final $Res Function(SetTemplate) _then;

  /// Create a copy of SetTemplate
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = freezed,
    Object? position = null,
    Object? setType = null,
    Object? repsMin = freezed,
    Object? repsMax = freezed,
    Object? weightKg = freezed,
    Object? percent1rm = freezed,
    Object? rpe = freezed,
    Object? rir = freezed,
    Object? durationSeconds = freezed,
    Object? distanceM = freezed,
    Object? restSeconds = freezed,
  }) {
    return _then(
      SetTemplate(
        id: freezed == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String?,
        position: null == position
            ? _self.position
            : position // ignore: cast_nullable_to_non_nullable
                  as int,
        setType: null == setType
            ? _self.setType
            : setType // ignore: cast_nullable_to_non_nullable
                  as SetType,
        repsMin: freezed == repsMin
            ? _self.repsMin
            : repsMin // ignore: cast_nullable_to_non_nullable
                  as int?,
        repsMax: freezed == repsMax
            ? _self.repsMax
            : repsMax // ignore: cast_nullable_to_non_nullable
                  as int?,
        weightKg: freezed == weightKg
            ? _self.weightKg
            : weightKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        percent1rm: freezed == percent1rm
            ? _self.percent1rm
            : percent1rm // ignore: cast_nullable_to_non_nullable
                  as double?,
        rpe: freezed == rpe
            ? _self.rpe
            : rpe // ignore: cast_nullable_to_non_nullable
                  as double?,
        rir: freezed == rir
            ? _self.rir
            : rir // ignore: cast_nullable_to_non_nullable
                  as int?,
        durationSeconds: freezed == durationSeconds
            ? _self.durationSeconds
            : durationSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        distanceM: freezed == distanceM
            ? _self.distanceM
            : distanceM // ignore: cast_nullable_to_non_nullable
                  as double?,
        restSeconds: freezed == restSeconds
            ? _self.restSeconds
            : restSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [SetTemplate].
extension SetTemplatePatterns on SetTemplate {
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
    TResult Function(_SetTemplate value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SetTemplate() when $default != null:
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
    TResult Function(_SetTemplate value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SetTemplate():
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
    TResult? Function(_SetTemplate value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SetTemplate() when $default != null:
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
      String? id,
      int position,
      @JsonKey(unknownEnumValue: SetType.working) SetType setType,
      int? repsMin,
      int? repsMax,
      double? weightKg,
      @JsonKey(name: 'percent_1rm') double? percent1rm,
      double? rpe,
      int? rir,
      int? durationSeconds,
      double? distanceM,
      int? restSeconds,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _SetTemplate() when $default != null:
        return $default(
          _that.id,
          _that.position,
          _that.setType,
          _that.repsMin,
          _that.repsMax,
          _that.weightKg,
          _that.percent1rm,
          _that.rpe,
          _that.rir,
          _that.durationSeconds,
          _that.distanceM,
          _that.restSeconds,
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
      String? id,
      int position,
      @JsonKey(unknownEnumValue: SetType.working) SetType setType,
      int? repsMin,
      int? repsMax,
      double? weightKg,
      @JsonKey(name: 'percent_1rm') double? percent1rm,
      double? rpe,
      int? rir,
      int? durationSeconds,
      double? distanceM,
      int? restSeconds,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SetTemplate():
        return $default(
          _that.id,
          _that.position,
          _that.setType,
          _that.repsMin,
          _that.repsMax,
          _that.weightKg,
          _that.percent1rm,
          _that.rpe,
          _that.rir,
          _that.durationSeconds,
          _that.distanceM,
          _that.restSeconds,
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
      String? id,
      int position,
      @JsonKey(unknownEnumValue: SetType.working) SetType setType,
      int? repsMin,
      int? repsMax,
      double? weightKg,
      @JsonKey(name: 'percent_1rm') double? percent1rm,
      double? rpe,
      int? rir,
      int? durationSeconds,
      double? distanceM,
      int? restSeconds,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _SetTemplate() when $default != null:
        return $default(
          _that.id,
          _that.position,
          _that.setType,
          _that.repsMin,
          _that.repsMax,
          _that.weightKg,
          _that.percent1rm,
          _that.rpe,
          _that.rir,
          _that.durationSeconds,
          _that.distanceM,
          _that.restSeconds,
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
class _SetTemplate implements SetTemplate {
  const _SetTemplate({
    this.id,
    this.position = 1,
    @JsonKey(unknownEnumValue: SetType.working) this.setType = SetType.working,
    this.repsMin,
    this.repsMax,
    this.weightKg,
    @JsonKey(name: 'percent_1rm') this.percent1rm,
    this.rpe,
    this.rir,
    this.durationSeconds,
    this.distanceM,
    this.restSeconds,
  });
  factory _SetTemplate.fromJson(Map<String, dynamic> json) =>
      _$SetTemplateFromJson(json);

  @override
  final String? id;
  @override
  @JsonKey()
  final int position;
  @override
  @JsonKey(unknownEnumValue: SetType.working)
  final SetType setType;
  @override
  final int? repsMin;
  @override
  final int? repsMax;
  @override
  final double? weightKg;
  @override
  @JsonKey(name: 'percent_1rm')
  final double? percent1rm;
  @override
  final double? rpe;
  @override
  final int? rir;
  @override
  final int? durationSeconds;
  @override
  final double? distanceM;
  @override
  final int? restSeconds;

  /// Create a copy of SetTemplate
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$SetTemplateCopyWith<_SetTemplate> get copyWith =>
      __$SetTemplateCopyWithImpl<_SetTemplate>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$SetTemplateToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _SetTemplate &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.position, position) ||
                other.position == position) &&
            (identical(other.setType, setType) || other.setType == setType) &&
            (identical(other.repsMin, repsMin) || other.repsMin == repsMin) &&
            (identical(other.repsMax, repsMax) || other.repsMax == repsMax) &&
            (identical(other.weightKg, weightKg) ||
                other.weightKg == weightKg) &&
            (identical(other.percent1rm, percent1rm) ||
                other.percent1rm == percent1rm) &&
            (identical(other.rpe, rpe) || other.rpe == rpe) &&
            (identical(other.rir, rir) || other.rir == rir) &&
            (identical(other.durationSeconds, durationSeconds) ||
                other.durationSeconds == durationSeconds) &&
            (identical(other.distanceM, distanceM) ||
                other.distanceM == distanceM) &&
            (identical(other.restSeconds, restSeconds) ||
                other.restSeconds == restSeconds));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      position,
      setType,
      repsMin,
      repsMax,
      weightKg,
      percent1rm,
      rpe,
      rir,
      durationSeconds,
      distanceM,
      restSeconds,
    );
  }

  @override
  String toString() {
    return 'SetTemplate(id: $id, position: $position, setType: $setType, repsMin: $repsMin, repsMax: $repsMax, weightKg: $weightKg, percent1rm: $percent1rm, rpe: $rpe, rir: $rir, durationSeconds: $durationSeconds, distanceM: $distanceM, restSeconds: $restSeconds)';
  }
}

/// @nodoc
abstract mixin class _$SetTemplateCopyWith<$Res>
    implements $SetTemplateCopyWith<$Res> {
  factory _$SetTemplateCopyWith(
    _SetTemplate value,
    $Res Function(_SetTemplate) _then,
  ) = __$SetTemplateCopyWithImpl;
  @override
  @useResult
  $Res call({
    String? id,
    int position,
    @JsonKey(unknownEnumValue: SetType.working) SetType setType,
    int? repsMin,
    int? repsMax,
    double? weightKg,
    @JsonKey(name: 'percent_1rm') double? percent1rm,
    double? rpe,
    int? rir,
    int? durationSeconds,
    double? distanceM,
    int? restSeconds,
  });
}

/// @nodoc
class __$SetTemplateCopyWithImpl<$Res> implements _$SetTemplateCopyWith<$Res> {
  __$SetTemplateCopyWithImpl(this._self, this._then);

  final _SetTemplate _self;
  final $Res Function(_SetTemplate) _then;

  /// Create a copy of SetTemplate
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = freezed,
    Object? position = null,
    Object? setType = null,
    Object? repsMin = freezed,
    Object? repsMax = freezed,
    Object? weightKg = freezed,
    Object? percent1rm = freezed,
    Object? rpe = freezed,
    Object? rir = freezed,
    Object? durationSeconds = freezed,
    Object? distanceM = freezed,
    Object? restSeconds = freezed,
  }) {
    return _then(
      _SetTemplate(
        id: freezed == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String?,
        position: null == position
            ? _self.position
            : position // ignore: cast_nullable_to_non_nullable
                  as int,
        setType: null == setType
            ? _self.setType
            : setType // ignore: cast_nullable_to_non_nullable
                  as SetType,
        repsMin: freezed == repsMin
            ? _self.repsMin
            : repsMin // ignore: cast_nullable_to_non_nullable
                  as int?,
        repsMax: freezed == repsMax
            ? _self.repsMax
            : repsMax // ignore: cast_nullable_to_non_nullable
                  as int?,
        weightKg: freezed == weightKg
            ? _self.weightKg
            : weightKg // ignore: cast_nullable_to_non_nullable
                  as double?,
        percent1rm: freezed == percent1rm
            ? _self.percent1rm
            : percent1rm // ignore: cast_nullable_to_non_nullable
                  as double?,
        rpe: freezed == rpe
            ? _self.rpe
            : rpe // ignore: cast_nullable_to_non_nullable
                  as double?,
        rir: freezed == rir
            ? _self.rir
            : rir // ignore: cast_nullable_to_non_nullable
                  as int?,
        durationSeconds: freezed == durationSeconds
            ? _self.durationSeconds
            : durationSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        distanceM: freezed == distanceM
            ? _self.distanceM
            : distanceM // ignore: cast_nullable_to_non_nullable
                  as double?,
        restSeconds: freezed == restSeconds
            ? _self.restSeconds
            : restSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
      ),
    );
  }
}

/// @nodoc
mixin _$LastSet {
  int get setNumber;
  int? get reps;
  double? get weightKg;
  int? get durationSeconds;
  double? get distanceM;
  double? get rpe;

  /// Create a copy of LastSet
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $LastSetCopyWith<LastSet> get copyWith =>
      _$LastSetCopyWithImpl<LastSet>(this as LastSet, _$identity);

  /// Serializes this LastSet to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as LastSet;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is LastSet &&
            (identical(other.setNumber, _this.setNumber) ||
                other.setNumber == _this.setNumber) &&
            (identical(other.reps, _this.reps) || other.reps == _this.reps) &&
            (identical(other.weightKg, _this.weightKg) ||
                other.weightKg == _this.weightKg) &&
            (identical(other.durationSeconds, _this.durationSeconds) ||
                other.durationSeconds == _this.durationSeconds) &&
            (identical(other.distanceM, _this.distanceM) ||
                other.distanceM == _this.distanceM) &&
            (identical(other.rpe, _this.rpe) || other.rpe == _this.rpe));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as LastSet;
    return Object.hash(
      runtimeType,
      _this.setNumber,
      _this.reps,
      _this.weightKg,
      _this.durationSeconds,
      _this.distanceM,
      _this.rpe,
    );
  }

  @override
  String toString() {
    final _this = this as LastSet;
    return 'LastSet(setNumber: ${_this.setNumber}, reps: ${_this.reps}, weightKg: ${_this.weightKg}, durationSeconds: ${_this.durationSeconds}, distanceM: ${_this.distanceM}, rpe: ${_this.rpe})';
  }
}

/// @nodoc
abstract mixin class $LastSetCopyWith<$Res> {
  factory $LastSetCopyWith(LastSet value, $Res Function(LastSet) _then) =
      _$LastSetCopyWithImpl;
  @useResult
  $Res call({
    int setNumber,
    int? reps,
    double? weightKg,
    int? durationSeconds,
    double? distanceM,
    double? rpe,
  });
}

/// @nodoc
class _$LastSetCopyWithImpl<$Res> implements $LastSetCopyWith<$Res> {
  _$LastSetCopyWithImpl(this._self, this._then);

  final LastSet _self;
  final $Res Function(LastSet) _then;

  /// Create a copy of LastSet
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? setNumber = null,
    Object? reps = freezed,
    Object? weightKg = freezed,
    Object? durationSeconds = freezed,
    Object? distanceM = freezed,
    Object? rpe = freezed,
  }) {
    return _then(
      LastSet(
        setNumber: null == setNumber
            ? _self.setNumber
            : setNumber // ignore: cast_nullable_to_non_nullable
                  as int,
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
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [LastSet].
extension LastSetPatterns on LastSet {
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
    TResult Function(_LastSet value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _LastSet() when $default != null:
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
    TResult Function(_LastSet value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LastSet():
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
    TResult? Function(_LastSet value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LastSet() when $default != null:
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
      int setNumber,
      int? reps,
      double? weightKg,
      int? durationSeconds,
      double? distanceM,
      double? rpe,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _LastSet() when $default != null:
        return $default(
          _that.setNumber,
          _that.reps,
          _that.weightKg,
          _that.durationSeconds,
          _that.distanceM,
          _that.rpe,
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
      int setNumber,
      int? reps,
      double? weightKg,
      int? durationSeconds,
      double? distanceM,
      double? rpe,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LastSet():
        return $default(
          _that.setNumber,
          _that.reps,
          _that.weightKg,
          _that.durationSeconds,
          _that.distanceM,
          _that.rpe,
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
      int setNumber,
      int? reps,
      double? weightKg,
      int? durationSeconds,
      double? distanceM,
      double? rpe,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LastSet() when $default != null:
        return $default(
          _that.setNumber,
          _that.reps,
          _that.weightKg,
          _that.durationSeconds,
          _that.distanceM,
          _that.rpe,
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
class _LastSet implements LastSet {
  const _LastSet({
    required this.setNumber,
    this.reps,
    this.weightKg,
    this.durationSeconds,
    this.distanceM,
    this.rpe,
  });
  factory _LastSet.fromJson(Map<String, dynamic> json) =>
      _$LastSetFromJson(json);

  @override
  final int setNumber;
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

  /// Create a copy of LastSet
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$LastSetCopyWith<_LastSet> get copyWith =>
      __$LastSetCopyWithImpl<_LastSet>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$LastSetToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _LastSet &&
            (identical(other.setNumber, setNumber) ||
                other.setNumber == setNumber) &&
            (identical(other.reps, reps) || other.reps == reps) &&
            (identical(other.weightKg, weightKg) ||
                other.weightKg == weightKg) &&
            (identical(other.durationSeconds, durationSeconds) ||
                other.durationSeconds == durationSeconds) &&
            (identical(other.distanceM, distanceM) ||
                other.distanceM == distanceM) &&
            (identical(other.rpe, rpe) || other.rpe == rpe));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      setNumber,
      reps,
      weightKg,
      durationSeconds,
      distanceM,
      rpe,
    );
  }

  @override
  String toString() {
    return 'LastSet(setNumber: $setNumber, reps: $reps, weightKg: $weightKg, durationSeconds: $durationSeconds, distanceM: $distanceM, rpe: $rpe)';
  }
}

/// @nodoc
abstract mixin class _$LastSetCopyWith<$Res> implements $LastSetCopyWith<$Res> {
  factory _$LastSetCopyWith(_LastSet value, $Res Function(_LastSet) _then) =
      __$LastSetCopyWithImpl;
  @override
  @useResult
  $Res call({
    int setNumber,
    int? reps,
    double? weightKg,
    int? durationSeconds,
    double? distanceM,
    double? rpe,
  });
}

/// @nodoc
class __$LastSetCopyWithImpl<$Res> implements _$LastSetCopyWith<$Res> {
  __$LastSetCopyWithImpl(this._self, this._then);

  final _LastSet _self;
  final $Res Function(_LastSet) _then;

  /// Create a copy of LastSet
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? setNumber = null,
    Object? reps = freezed,
    Object? weightKg = freezed,
    Object? durationSeconds = freezed,
    Object? distanceM = freezed,
    Object? rpe = freezed,
  }) {
    return _then(
      _LastSet(
        setNumber: null == setNumber
            ? _self.setNumber
            : setNumber // ignore: cast_nullable_to_non_nullable
                  as int,
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
      ),
    );
  }
}

/// @nodoc
mixin _$LastPerformance {
  String get sessionId;
  DateTime get performedAt;
  List<LastSet> get sets;

  /// Create a copy of LastPerformance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $LastPerformanceCopyWith<LastPerformance> get copyWith =>
      _$LastPerformanceCopyWithImpl<LastPerformance>(
        this as LastPerformance,
        _$identity,
      );

  /// Serializes this LastPerformance to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as LastPerformance;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is LastPerformance &&
            (identical(other.sessionId, _this.sessionId) ||
                other.sessionId == _this.sessionId) &&
            (identical(other.performedAt, _this.performedAt) ||
                other.performedAt == _this.performedAt) &&
            const DeepCollectionEquality().equals(other.sets, _this.sets));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as LastPerformance;
    return Object.hash(
      runtimeType,
      _this.sessionId,
      _this.performedAt,
      const DeepCollectionEquality().hash(_this.sets),
    );
  }

  @override
  String toString() {
    final _this = this as LastPerformance;
    return 'LastPerformance(sessionId: ${_this.sessionId}, performedAt: ${_this.performedAt}, sets: ${_this.sets})';
  }
}

/// @nodoc
abstract mixin class $LastPerformanceCopyWith<$Res> {
  factory $LastPerformanceCopyWith(
    LastPerformance value,
    $Res Function(LastPerformance) _then,
  ) = _$LastPerformanceCopyWithImpl;
  @useResult
  $Res call({String sessionId, DateTime performedAt, List<LastSet> sets});
}

/// @nodoc
class _$LastPerformanceCopyWithImpl<$Res>
    implements $LastPerformanceCopyWith<$Res> {
  _$LastPerformanceCopyWithImpl(this._self, this._then);

  final LastPerformance _self;
  final $Res Function(LastPerformance) _then;

  /// Create a copy of LastPerformance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? sessionId = null,
    Object? performedAt = null,
    Object? sets = null,
  }) {
    return _then(
      LastPerformance(
        sessionId: null == sessionId
            ? _self.sessionId
            : sessionId // ignore: cast_nullable_to_non_nullable
                  as String,
        performedAt: null == performedAt
            ? _self.performedAt
            : performedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        sets: null == sets
            ? _self.sets
            : sets // ignore: cast_nullable_to_non_nullable
                  as List<LastSet>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [LastPerformance].
extension LastPerformancePatterns on LastPerformance {
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
    TResult Function(_LastPerformance value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _LastPerformance() when $default != null:
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
    TResult Function(_LastPerformance value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LastPerformance():
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
    TResult? Function(_LastPerformance value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LastPerformance() when $default != null:
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
      DateTime performedAt,
      List<LastSet> sets,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _LastPerformance() when $default != null:
        return $default(_that.sessionId, _that.performedAt, _that.sets);
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
    TResult Function(String sessionId, DateTime performedAt, List<LastSet> sets)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LastPerformance():
        return $default(_that.sessionId, _that.performedAt, _that.sets);
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
      DateTime performedAt,
      List<LastSet> sets,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _LastPerformance() when $default != null:
        return $default(_that.sessionId, _that.performedAt, _that.sets);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _LastPerformance implements LastPerformance {
  const _LastPerformance({
    required this.sessionId,
    required this.performedAt,
    List<LastSet> sets = const <LastSet>[],
  }) : _sets = sets;
  factory _LastPerformance.fromJson(Map<String, dynamic> json) =>
      _$LastPerformanceFromJson(json);

  @override
  final String sessionId;
  @override
  final DateTime performedAt;
  final List<LastSet> _sets;
  @override
  @JsonKey()
  List<LastSet> get sets {
    if (_sets is EqualUnmodifiableListView) return _sets;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_sets);
  }

  /// Create a copy of LastPerformance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$LastPerformanceCopyWith<_LastPerformance> get copyWith =>
      __$LastPerformanceCopyWithImpl<_LastPerformance>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$LastPerformanceToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _LastPerformance &&
            (identical(other.sessionId, sessionId) ||
                other.sessionId == sessionId) &&
            (identical(other.performedAt, performedAt) ||
                other.performedAt == performedAt) &&
            const DeepCollectionEquality().equals(other.sets, _sets));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      sessionId,
      performedAt,
      const DeepCollectionEquality().hash(_sets),
    );
  }

  @override
  String toString() {
    return 'LastPerformance(sessionId: $sessionId, performedAt: $performedAt, sets: $sets)';
  }
}

/// @nodoc
abstract mixin class _$LastPerformanceCopyWith<$Res>
    implements $LastPerformanceCopyWith<$Res> {
  factory _$LastPerformanceCopyWith(
    _LastPerformance value,
    $Res Function(_LastPerformance) _then,
  ) = __$LastPerformanceCopyWithImpl;
  @override
  @useResult
  $Res call({String sessionId, DateTime performedAt, List<LastSet> sets});
}

/// @nodoc
class __$LastPerformanceCopyWithImpl<$Res>
    implements _$LastPerformanceCopyWith<$Res> {
  __$LastPerformanceCopyWithImpl(this._self, this._then);

  final _LastPerformance _self;
  final $Res Function(_LastPerformance) _then;

  /// Create a copy of LastPerformance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? sessionId = null,
    Object? performedAt = null,
    Object? sets = null,
  }) {
    return _then(
      _LastPerformance(
        sessionId: null == sessionId
            ? _self.sessionId
            : sessionId // ignore: cast_nullable_to_non_nullable
                  as String,
        performedAt: null == performedAt
            ? _self.performedAt
            : performedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime,
        sets: null == sets
            ? _self._sets
            : sets // ignore: cast_nullable_to_non_nullable
                  as List<LastSet>,
      ),
    );
  }
}

/// @nodoc
mixin _$WorkoutExercise {
  String get id;
  int get position;
  Exercise get exercise;
  int? get restSeconds;
  String? get tempo;
  String? get notes;
  List<String> get alternativeIds;
  List<SetTemplate> get sets;
  LastPerformance? get lastPerformance;

  /// Create a copy of WorkoutExercise
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $WorkoutExerciseCopyWith<WorkoutExercise> get copyWith =>
      _$WorkoutExerciseCopyWithImpl<WorkoutExercise>(
        this as WorkoutExercise,
        _$identity,
      );

  /// Serializes this WorkoutExercise to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as WorkoutExercise;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is WorkoutExercise &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.position, _this.position) ||
                other.position == _this.position) &&
            (identical(other.exercise, _this.exercise) ||
                other.exercise == _this.exercise) &&
            (identical(other.restSeconds, _this.restSeconds) ||
                other.restSeconds == _this.restSeconds) &&
            (identical(other.tempo, _this.tempo) ||
                other.tempo == _this.tempo) &&
            (identical(other.notes, _this.notes) ||
                other.notes == _this.notes) &&
            const DeepCollectionEquality().equals(
              other.alternativeIds,
              _this.alternativeIds,
            ) &&
            const DeepCollectionEquality().equals(other.sets, _this.sets) &&
            (identical(other.lastPerformance, _this.lastPerformance) ||
                other.lastPerformance == _this.lastPerformance));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as WorkoutExercise;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.position,
      _this.exercise,
      _this.restSeconds,
      _this.tempo,
      _this.notes,
      const DeepCollectionEquality().hash(_this.alternativeIds),
      const DeepCollectionEquality().hash(_this.sets),
      _this.lastPerformance,
    );
  }

  @override
  String toString() {
    final _this = this as WorkoutExercise;
    return 'WorkoutExercise(id: ${_this.id}, position: ${_this.position}, exercise: ${_this.exercise}, restSeconds: ${_this.restSeconds}, tempo: ${_this.tempo}, notes: ${_this.notes}, alternativeIds: ${_this.alternativeIds}, sets: ${_this.sets}, lastPerformance: ${_this.lastPerformance})';
  }
}

/// @nodoc
abstract mixin class $WorkoutExerciseCopyWith<$Res> {
  factory $WorkoutExerciseCopyWith(
    WorkoutExercise value,
    $Res Function(WorkoutExercise) _then,
  ) = _$WorkoutExerciseCopyWithImpl;
  @useResult
  $Res call({
    String id,
    int position,
    Exercise exercise,
    int? restSeconds,
    String? tempo,
    String? notes,
    List<String> alternativeIds,
    List<SetTemplate> sets,
    LastPerformance? lastPerformance,
  });

  $ExerciseCopyWith<$Res> get exercise;
  $LastPerformanceCopyWith<$Res>? get lastPerformance;
}

/// @nodoc
class _$WorkoutExerciseCopyWithImpl<$Res>
    implements $WorkoutExerciseCopyWith<$Res> {
  _$WorkoutExerciseCopyWithImpl(this._self, this._then);

  final WorkoutExercise _self;
  final $Res Function(WorkoutExercise) _then;

  /// Create a copy of WorkoutExercise
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? position = null,
    Object? exercise = null,
    Object? restSeconds = freezed,
    Object? tempo = freezed,
    Object? notes = freezed,
    Object? alternativeIds = null,
    Object? sets = null,
    Object? lastPerformance = freezed,
  }) {
    return _then(
      WorkoutExercise(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        position: null == position
            ? _self.position
            : position // ignore: cast_nullable_to_non_nullable
                  as int,
        exercise: null == exercise
            ? _self.exercise
            : exercise // ignore: cast_nullable_to_non_nullable
                  as Exercise,
        restSeconds: freezed == restSeconds
            ? _self.restSeconds
            : restSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        tempo: freezed == tempo
            ? _self.tempo
            : tempo // ignore: cast_nullable_to_non_nullable
                  as String?,
        notes: freezed == notes
            ? _self.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        alternativeIds: null == alternativeIds
            ? _self.alternativeIds
            : alternativeIds // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        sets: null == sets
            ? _self.sets
            : sets // ignore: cast_nullable_to_non_nullable
                  as List<SetTemplate>,
        lastPerformance: freezed == lastPerformance
            ? _self.lastPerformance
            : lastPerformance // ignore: cast_nullable_to_non_nullable
                  as LastPerformance?,
      ),
    );
  }

  /// Create a copy of WorkoutExercise
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ExerciseCopyWith<$Res> get exercise {
    return $ExerciseCopyWith<$Res>(_self.exercise, (value) {
      return _then(_self.copyWith(exercise: value));
    });
  }

  /// Create a copy of WorkoutExercise
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $LastPerformanceCopyWith<$Res>? get lastPerformance {
    if (_self.lastPerformance == null) {
      return null;
    }

    return $LastPerformanceCopyWith<$Res>(_self.lastPerformance!, (value) {
      return _then(_self.copyWith(lastPerformance: value));
    });
  }
}

/// Adds pattern-matching-related methods to [WorkoutExercise].
extension WorkoutExercisePatterns on WorkoutExercise {
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
    TResult Function(_WorkoutExercise value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _WorkoutExercise() when $default != null:
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
    TResult Function(_WorkoutExercise value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutExercise():
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
    TResult? Function(_WorkoutExercise value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutExercise() when $default != null:
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
      int position,
      Exercise exercise,
      int? restSeconds,
      String? tempo,
      String? notes,
      List<String> alternativeIds,
      List<SetTemplate> sets,
      LastPerformance? lastPerformance,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _WorkoutExercise() when $default != null:
        return $default(
          _that.id,
          _that.position,
          _that.exercise,
          _that.restSeconds,
          _that.tempo,
          _that.notes,
          _that.alternativeIds,
          _that.sets,
          _that.lastPerformance,
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
      int position,
      Exercise exercise,
      int? restSeconds,
      String? tempo,
      String? notes,
      List<String> alternativeIds,
      List<SetTemplate> sets,
      LastPerformance? lastPerformance,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutExercise():
        return $default(
          _that.id,
          _that.position,
          _that.exercise,
          _that.restSeconds,
          _that.tempo,
          _that.notes,
          _that.alternativeIds,
          _that.sets,
          _that.lastPerformance,
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
      int position,
      Exercise exercise,
      int? restSeconds,
      String? tempo,
      String? notes,
      List<String> alternativeIds,
      List<SetTemplate> sets,
      LastPerformance? lastPerformance,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutExercise() when $default != null:
        return $default(
          _that.id,
          _that.position,
          _that.exercise,
          _that.restSeconds,
          _that.tempo,
          _that.notes,
          _that.alternativeIds,
          _that.sets,
          _that.lastPerformance,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _WorkoutExercise implements WorkoutExercise {
  const _WorkoutExercise({
    required this.id,
    this.position = 1,
    required this.exercise,
    this.restSeconds,
    this.tempo,
    this.notes,
    List<String> alternativeIds = const <String>[],
    List<SetTemplate> sets = const <SetTemplate>[],
    this.lastPerformance,
  }) : _alternativeIds = alternativeIds,
       _sets = sets;
  factory _WorkoutExercise.fromJson(Map<String, dynamic> json) =>
      _$WorkoutExerciseFromJson(json);

  @override
  final String id;
  @override
  @JsonKey()
  final int position;
  @override
  final Exercise exercise;
  @override
  final int? restSeconds;
  @override
  final String? tempo;
  @override
  final String? notes;
  final List<String> _alternativeIds;
  @override
  @JsonKey()
  List<String> get alternativeIds {
    if (_alternativeIds is EqualUnmodifiableListView) return _alternativeIds;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_alternativeIds);
  }

  final List<SetTemplate> _sets;
  @override
  @JsonKey()
  List<SetTemplate> get sets {
    if (_sets is EqualUnmodifiableListView) return _sets;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_sets);
  }

  @override
  final LastPerformance? lastPerformance;

  /// Create a copy of WorkoutExercise
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$WorkoutExerciseCopyWith<_WorkoutExercise> get copyWith =>
      __$WorkoutExerciseCopyWithImpl<_WorkoutExercise>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$WorkoutExerciseToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _WorkoutExercise &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.position, position) ||
                other.position == position) &&
            (identical(other.exercise, exercise) ||
                other.exercise == exercise) &&
            (identical(other.restSeconds, restSeconds) ||
                other.restSeconds == restSeconds) &&
            (identical(other.tempo, tempo) || other.tempo == tempo) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            const DeepCollectionEquality().equals(
              other.alternativeIds,
              _alternativeIds,
            ) &&
            const DeepCollectionEquality().equals(other.sets, _sets) &&
            (identical(other.lastPerformance, lastPerformance) ||
                other.lastPerformance == lastPerformance));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      position,
      exercise,
      restSeconds,
      tempo,
      notes,
      const DeepCollectionEquality().hash(_alternativeIds),
      const DeepCollectionEquality().hash(_sets),
      lastPerformance,
    );
  }

  @override
  String toString() {
    return 'WorkoutExercise(id: $id, position: $position, exercise: $exercise, restSeconds: $restSeconds, tempo: $tempo, notes: $notes, alternativeIds: $alternativeIds, sets: $sets, lastPerformance: $lastPerformance)';
  }
}

/// @nodoc
abstract mixin class _$WorkoutExerciseCopyWith<$Res>
    implements $WorkoutExerciseCopyWith<$Res> {
  factory _$WorkoutExerciseCopyWith(
    _WorkoutExercise value,
    $Res Function(_WorkoutExercise) _then,
  ) = __$WorkoutExerciseCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    int position,
    Exercise exercise,
    int? restSeconds,
    String? tempo,
    String? notes,
    List<String> alternativeIds,
    List<SetTemplate> sets,
    LastPerformance? lastPerformance,
  });

  @override
  $ExerciseCopyWith<$Res> get exercise;
  @override
  $LastPerformanceCopyWith<$Res>? get lastPerformance;
}

/// @nodoc
class __$WorkoutExerciseCopyWithImpl<$Res>
    implements _$WorkoutExerciseCopyWith<$Res> {
  __$WorkoutExerciseCopyWithImpl(this._self, this._then);

  final _WorkoutExercise _self;
  final $Res Function(_WorkoutExercise) _then;

  /// Create a copy of WorkoutExercise
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? position = null,
    Object? exercise = null,
    Object? restSeconds = freezed,
    Object? tempo = freezed,
    Object? notes = freezed,
    Object? alternativeIds = null,
    Object? sets = null,
    Object? lastPerformance = freezed,
  }) {
    return _then(
      _WorkoutExercise(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        position: null == position
            ? _self.position
            : position // ignore: cast_nullable_to_non_nullable
                  as int,
        exercise: null == exercise
            ? _self.exercise
            : exercise // ignore: cast_nullable_to_non_nullable
                  as Exercise,
        restSeconds: freezed == restSeconds
            ? _self.restSeconds
            : restSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        tempo: freezed == tempo
            ? _self.tempo
            : tempo // ignore: cast_nullable_to_non_nullable
                  as String?,
        notes: freezed == notes
            ? _self.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        alternativeIds: null == alternativeIds
            ? _self._alternativeIds
            : alternativeIds // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        sets: null == sets
            ? _self._sets
            : sets // ignore: cast_nullable_to_non_nullable
                  as List<SetTemplate>,
        lastPerformance: freezed == lastPerformance
            ? _self.lastPerformance
            : lastPerformance // ignore: cast_nullable_to_non_nullable
                  as LastPerformance?,
      ),
    );
  }

  /// Create a copy of WorkoutExercise
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ExerciseCopyWith<$Res> get exercise {
    return $ExerciseCopyWith<$Res>(_self.exercise, (value) {
      return _then(_self.copyWith(exercise: value));
    });
  }

  /// Create a copy of WorkoutExercise
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $LastPerformanceCopyWith<$Res>? get lastPerformance {
    if (_self.lastPerformance == null) {
      return null;
    }

    return $LastPerformanceCopyWith<$Res>(_self.lastPerformance!, (value) {
      return _then(_self.copyWith(lastPerformance: value));
    });
  }
}

/// @nodoc
mixin _$WorkoutBlock {
  String get id;
  int get position;
  @JsonKey(unknownEnumValue: BlockType.straight)
  BlockType get type;
  String? get name;
  int? get rounds;
  int? get workSeconds;
  int? get restSeconds;
  int? get restBetweenRounds;
  int? get timeCapSeconds;
  String? get notes;
  List<WorkoutExercise> get exercises;

  /// Create a copy of WorkoutBlock
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $WorkoutBlockCopyWith<WorkoutBlock> get copyWith =>
      _$WorkoutBlockCopyWithImpl<WorkoutBlock>(
        this as WorkoutBlock,
        _$identity,
      );

  /// Serializes this WorkoutBlock to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as WorkoutBlock;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is WorkoutBlock &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.position, _this.position) ||
                other.position == _this.position) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.rounds, _this.rounds) ||
                other.rounds == _this.rounds) &&
            (identical(other.workSeconds, _this.workSeconds) ||
                other.workSeconds == _this.workSeconds) &&
            (identical(other.restSeconds, _this.restSeconds) ||
                other.restSeconds == _this.restSeconds) &&
            (identical(other.restBetweenRounds, _this.restBetweenRounds) ||
                other.restBetweenRounds == _this.restBetweenRounds) &&
            (identical(other.timeCapSeconds, _this.timeCapSeconds) ||
                other.timeCapSeconds == _this.timeCapSeconds) &&
            (identical(other.notes, _this.notes) ||
                other.notes == _this.notes) &&
            const DeepCollectionEquality().equals(
              other.exercises,
              _this.exercises,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as WorkoutBlock;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.position,
      _this.type,
      _this.name,
      _this.rounds,
      _this.workSeconds,
      _this.restSeconds,
      _this.restBetweenRounds,
      _this.timeCapSeconds,
      _this.notes,
      const DeepCollectionEquality().hash(_this.exercises),
    );
  }

  @override
  String toString() {
    final _this = this as WorkoutBlock;
    return 'WorkoutBlock(id: ${_this.id}, position: ${_this.position}, type: ${_this.type}, name: ${_this.name}, rounds: ${_this.rounds}, workSeconds: ${_this.workSeconds}, restSeconds: ${_this.restSeconds}, restBetweenRounds: ${_this.restBetweenRounds}, timeCapSeconds: ${_this.timeCapSeconds}, notes: ${_this.notes}, exercises: ${_this.exercises})';
  }
}

/// @nodoc
abstract mixin class $WorkoutBlockCopyWith<$Res> {
  factory $WorkoutBlockCopyWith(
    WorkoutBlock value,
    $Res Function(WorkoutBlock) _then,
  ) = _$WorkoutBlockCopyWithImpl;
  @useResult
  $Res call({
    String id,
    int position,
    @JsonKey(unknownEnumValue: BlockType.straight) BlockType type,
    String? name,
    int? rounds,
    int? workSeconds,
    int? restSeconds,
    int? restBetweenRounds,
    int? timeCapSeconds,
    String? notes,
    List<WorkoutExercise> exercises,
  });
}

/// @nodoc
class _$WorkoutBlockCopyWithImpl<$Res> implements $WorkoutBlockCopyWith<$Res> {
  _$WorkoutBlockCopyWithImpl(this._self, this._then);

  final WorkoutBlock _self;
  final $Res Function(WorkoutBlock) _then;

  /// Create a copy of WorkoutBlock
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? position = null,
    Object? type = null,
    Object? name = freezed,
    Object? rounds = freezed,
    Object? workSeconds = freezed,
    Object? restSeconds = freezed,
    Object? restBetweenRounds = freezed,
    Object? timeCapSeconds = freezed,
    Object? notes = freezed,
    Object? exercises = null,
  }) {
    return _then(
      WorkoutBlock(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        position: null == position
            ? _self.position
            : position // ignore: cast_nullable_to_non_nullable
                  as int,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as BlockType,
        name: freezed == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String?,
        rounds: freezed == rounds
            ? _self.rounds
            : rounds // ignore: cast_nullable_to_non_nullable
                  as int?,
        workSeconds: freezed == workSeconds
            ? _self.workSeconds
            : workSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        restSeconds: freezed == restSeconds
            ? _self.restSeconds
            : restSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        restBetweenRounds: freezed == restBetweenRounds
            ? _self.restBetweenRounds
            : restBetweenRounds // ignore: cast_nullable_to_non_nullable
                  as int?,
        timeCapSeconds: freezed == timeCapSeconds
            ? _self.timeCapSeconds
            : timeCapSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        notes: freezed == notes
            ? _self.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        exercises: null == exercises
            ? _self.exercises
            : exercises // ignore: cast_nullable_to_non_nullable
                  as List<WorkoutExercise>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [WorkoutBlock].
extension WorkoutBlockPatterns on WorkoutBlock {
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
    TResult Function(_WorkoutBlock value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _WorkoutBlock() when $default != null:
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
    TResult Function(_WorkoutBlock value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutBlock():
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
    TResult? Function(_WorkoutBlock value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutBlock() when $default != null:
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
      int position,
      @JsonKey(unknownEnumValue: BlockType.straight) BlockType type,
      String? name,
      int? rounds,
      int? workSeconds,
      int? restSeconds,
      int? restBetweenRounds,
      int? timeCapSeconds,
      String? notes,
      List<WorkoutExercise> exercises,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _WorkoutBlock() when $default != null:
        return $default(
          _that.id,
          _that.position,
          _that.type,
          _that.name,
          _that.rounds,
          _that.workSeconds,
          _that.restSeconds,
          _that.restBetweenRounds,
          _that.timeCapSeconds,
          _that.notes,
          _that.exercises,
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
      int position,
      @JsonKey(unknownEnumValue: BlockType.straight) BlockType type,
      String? name,
      int? rounds,
      int? workSeconds,
      int? restSeconds,
      int? restBetweenRounds,
      int? timeCapSeconds,
      String? notes,
      List<WorkoutExercise> exercises,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutBlock():
        return $default(
          _that.id,
          _that.position,
          _that.type,
          _that.name,
          _that.rounds,
          _that.workSeconds,
          _that.restSeconds,
          _that.restBetweenRounds,
          _that.timeCapSeconds,
          _that.notes,
          _that.exercises,
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
      int position,
      @JsonKey(unknownEnumValue: BlockType.straight) BlockType type,
      String? name,
      int? rounds,
      int? workSeconds,
      int? restSeconds,
      int? restBetweenRounds,
      int? timeCapSeconds,
      String? notes,
      List<WorkoutExercise> exercises,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutBlock() when $default != null:
        return $default(
          _that.id,
          _that.position,
          _that.type,
          _that.name,
          _that.rounds,
          _that.workSeconds,
          _that.restSeconds,
          _that.restBetweenRounds,
          _that.timeCapSeconds,
          _that.notes,
          _that.exercises,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _WorkoutBlock implements WorkoutBlock {
  const _WorkoutBlock({
    required this.id,
    this.position = 1,
    @JsonKey(unknownEnumValue: BlockType.straight)
    this.type = BlockType.straight,
    this.name,
    this.rounds,
    this.workSeconds,
    this.restSeconds,
    this.restBetweenRounds,
    this.timeCapSeconds,
    this.notes,
    List<WorkoutExercise> exercises = const <WorkoutExercise>[],
  }) : _exercises = exercises;
  factory _WorkoutBlock.fromJson(Map<String, dynamic> json) =>
      _$WorkoutBlockFromJson(json);

  @override
  final String id;
  @override
  @JsonKey()
  final int position;
  @override
  @JsonKey(unknownEnumValue: BlockType.straight)
  final BlockType type;
  @override
  final String? name;
  @override
  final int? rounds;
  @override
  final int? workSeconds;
  @override
  final int? restSeconds;
  @override
  final int? restBetweenRounds;
  @override
  final int? timeCapSeconds;
  @override
  final String? notes;
  final List<WorkoutExercise> _exercises;
  @override
  @JsonKey()
  List<WorkoutExercise> get exercises {
    if (_exercises is EqualUnmodifiableListView) return _exercises;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_exercises);
  }

  /// Create a copy of WorkoutBlock
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$WorkoutBlockCopyWith<_WorkoutBlock> get copyWith =>
      __$WorkoutBlockCopyWithImpl<_WorkoutBlock>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$WorkoutBlockToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _WorkoutBlock &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.position, position) ||
                other.position == position) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.rounds, rounds) || other.rounds == rounds) &&
            (identical(other.workSeconds, workSeconds) ||
                other.workSeconds == workSeconds) &&
            (identical(other.restSeconds, restSeconds) ||
                other.restSeconds == restSeconds) &&
            (identical(other.restBetweenRounds, restBetweenRounds) ||
                other.restBetweenRounds == restBetweenRounds) &&
            (identical(other.timeCapSeconds, timeCapSeconds) ||
                other.timeCapSeconds == timeCapSeconds) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            const DeepCollectionEquality().equals(other.exercises, _exercises));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      position,
      type,
      name,
      rounds,
      workSeconds,
      restSeconds,
      restBetweenRounds,
      timeCapSeconds,
      notes,
      const DeepCollectionEquality().hash(_exercises),
    );
  }

  @override
  String toString() {
    return 'WorkoutBlock(id: $id, position: $position, type: $type, name: $name, rounds: $rounds, workSeconds: $workSeconds, restSeconds: $restSeconds, restBetweenRounds: $restBetweenRounds, timeCapSeconds: $timeCapSeconds, notes: $notes, exercises: $exercises)';
  }
}

/// @nodoc
abstract mixin class _$WorkoutBlockCopyWith<$Res>
    implements $WorkoutBlockCopyWith<$Res> {
  factory _$WorkoutBlockCopyWith(
    _WorkoutBlock value,
    $Res Function(_WorkoutBlock) _then,
  ) = __$WorkoutBlockCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    int position,
    @JsonKey(unknownEnumValue: BlockType.straight) BlockType type,
    String? name,
    int? rounds,
    int? workSeconds,
    int? restSeconds,
    int? restBetweenRounds,
    int? timeCapSeconds,
    String? notes,
    List<WorkoutExercise> exercises,
  });
}

/// @nodoc
class __$WorkoutBlockCopyWithImpl<$Res>
    implements _$WorkoutBlockCopyWith<$Res> {
  __$WorkoutBlockCopyWithImpl(this._self, this._then);

  final _WorkoutBlock _self;
  final $Res Function(_WorkoutBlock) _then;

  /// Create a copy of WorkoutBlock
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? position = null,
    Object? type = null,
    Object? name = freezed,
    Object? rounds = freezed,
    Object? workSeconds = freezed,
    Object? restSeconds = freezed,
    Object? restBetweenRounds = freezed,
    Object? timeCapSeconds = freezed,
    Object? notes = freezed,
    Object? exercises = null,
  }) {
    return _then(
      _WorkoutBlock(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        position: null == position
            ? _self.position
            : position // ignore: cast_nullable_to_non_nullable
                  as int,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as BlockType,
        name: freezed == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String?,
        rounds: freezed == rounds
            ? _self.rounds
            : rounds // ignore: cast_nullable_to_non_nullable
                  as int?,
        workSeconds: freezed == workSeconds
            ? _self.workSeconds
            : workSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        restSeconds: freezed == restSeconds
            ? _self.restSeconds
            : restSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        restBetweenRounds: freezed == restBetweenRounds
            ? _self.restBetweenRounds
            : restBetweenRounds // ignore: cast_nullable_to_non_nullable
                  as int?,
        timeCapSeconds: freezed == timeCapSeconds
            ? _self.timeCapSeconds
            : timeCapSeconds // ignore: cast_nullable_to_non_nullable
                  as int?,
        notes: freezed == notes
            ? _self.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        exercises: null == exercises
            ? _self._exercises
            : exercises // ignore: cast_nullable_to_non_nullable
                  as List<WorkoutExercise>,
      ),
    );
  }
}

/// @nodoc
mixin _$Workout {
  String get id;
  String get name;
  String? get description;
  String? get difficulty;
  int? get estimatedMinutes;
  List<String> get tags;
  List<String> get equipmentSummary;
  List<WorkoutBlock> get blocks;
  DateTime? get updatedAt;
  int? get downloadSizeBytes;

  /// Create a copy of Workout
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $WorkoutCopyWith<Workout> get copyWith =>
      _$WorkoutCopyWithImpl<Workout>(this as Workout, _$identity);

  /// Serializes this Workout to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Workout;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Workout &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.description, _this.description) ||
                other.description == _this.description) &&
            (identical(other.difficulty, _this.difficulty) ||
                other.difficulty == _this.difficulty) &&
            (identical(other.estimatedMinutes, _this.estimatedMinutes) ||
                other.estimatedMinutes == _this.estimatedMinutes) &&
            const DeepCollectionEquality().equals(other.tags, _this.tags) &&
            const DeepCollectionEquality().equals(
              other.equipmentSummary,
              _this.equipmentSummary,
            ) &&
            const DeepCollectionEquality().equals(other.blocks, _this.blocks) &&
            (identical(other.updatedAt, _this.updatedAt) ||
                other.updatedAt == _this.updatedAt) &&
            (identical(other.downloadSizeBytes, _this.downloadSizeBytes) ||
                other.downloadSizeBytes == _this.downloadSizeBytes));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Workout;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.name,
      _this.description,
      _this.difficulty,
      _this.estimatedMinutes,
      const DeepCollectionEquality().hash(_this.tags),
      const DeepCollectionEquality().hash(_this.equipmentSummary),
      const DeepCollectionEquality().hash(_this.blocks),
      _this.updatedAt,
      _this.downloadSizeBytes,
    );
  }

  @override
  String toString() {
    final _this = this as Workout;
    return 'Workout(id: ${_this.id}, name: ${_this.name}, description: ${_this.description}, difficulty: ${_this.difficulty}, estimatedMinutes: ${_this.estimatedMinutes}, tags: ${_this.tags}, equipmentSummary: ${_this.equipmentSummary}, blocks: ${_this.blocks}, updatedAt: ${_this.updatedAt}, downloadSizeBytes: ${_this.downloadSizeBytes})';
  }
}

/// @nodoc
abstract mixin class $WorkoutCopyWith<$Res> {
  factory $WorkoutCopyWith(Workout value, $Res Function(Workout) _then) =
      _$WorkoutCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String name,
    String? description,
    String? difficulty,
    int? estimatedMinutes,
    List<String> tags,
    List<String> equipmentSummary,
    List<WorkoutBlock> blocks,
    DateTime? updatedAt,
    int? downloadSizeBytes,
  });
}

/// @nodoc
class _$WorkoutCopyWithImpl<$Res> implements $WorkoutCopyWith<$Res> {
  _$WorkoutCopyWithImpl(this._self, this._then);

  final Workout _self;
  final $Res Function(Workout) _then;

  /// Create a copy of Workout
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = freezed,
    Object? difficulty = freezed,
    Object? estimatedMinutes = freezed,
    Object? tags = null,
    Object? equipmentSummary = null,
    Object? blocks = null,
    Object? updatedAt = freezed,
    Object? downloadSizeBytes = freezed,
  }) {
    return _then(
      Workout(
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
        difficulty: freezed == difficulty
            ? _self.difficulty
            : difficulty // ignore: cast_nullable_to_non_nullable
                  as String?,
        estimatedMinutes: freezed == estimatedMinutes
            ? _self.estimatedMinutes
            : estimatedMinutes // ignore: cast_nullable_to_non_nullable
                  as int?,
        tags: null == tags
            ? _self.tags
            : tags // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        equipmentSummary: null == equipmentSummary
            ? _self.equipmentSummary
            : equipmentSummary // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        blocks: null == blocks
            ? _self.blocks
            : blocks // ignore: cast_nullable_to_non_nullable
                  as List<WorkoutBlock>,
        updatedAt: freezed == updatedAt
            ? _self.updatedAt
            : updatedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        downloadSizeBytes: freezed == downloadSizeBytes
            ? _self.downloadSizeBytes
            : downloadSizeBytes // ignore: cast_nullable_to_non_nullable
                  as int?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Workout].
extension WorkoutPatterns on Workout {
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
    TResult Function(_Workout value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Workout() when $default != null:
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
    TResult Function(_Workout value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Workout():
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
    TResult? Function(_Workout value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Workout() when $default != null:
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
      String? difficulty,
      int? estimatedMinutes,
      List<String> tags,
      List<String> equipmentSummary,
      List<WorkoutBlock> blocks,
      DateTime? updatedAt,
      int? downloadSizeBytes,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Workout() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.difficulty,
          _that.estimatedMinutes,
          _that.tags,
          _that.equipmentSummary,
          _that.blocks,
          _that.updatedAt,
          _that.downloadSizeBytes,
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
      String? difficulty,
      int? estimatedMinutes,
      List<String> tags,
      List<String> equipmentSummary,
      List<WorkoutBlock> blocks,
      DateTime? updatedAt,
      int? downloadSizeBytes,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Workout():
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.difficulty,
          _that.estimatedMinutes,
          _that.tags,
          _that.equipmentSummary,
          _that.blocks,
          _that.updatedAt,
          _that.downloadSizeBytes,
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
      String? difficulty,
      int? estimatedMinutes,
      List<String> tags,
      List<String> equipmentSummary,
      List<WorkoutBlock> blocks,
      DateTime? updatedAt,
      int? downloadSizeBytes,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Workout() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.difficulty,
          _that.estimatedMinutes,
          _that.tags,
          _that.equipmentSummary,
          _that.blocks,
          _that.updatedAt,
          _that.downloadSizeBytes,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Workout extends Workout {
  const _Workout({
    required this.id,
    required this.name,
    this.description,
    this.difficulty,
    this.estimatedMinutes,
    List<String> tags = const <String>[],
    List<String> equipmentSummary = const <String>[],
    List<WorkoutBlock> blocks = const <WorkoutBlock>[],
    this.updatedAt,
    this.downloadSizeBytes,
  }) : _tags = tags,
       _equipmentSummary = equipmentSummary,
       _blocks = blocks,
       super._();
  factory _Workout.fromJson(Map<String, dynamic> json) =>
      _$WorkoutFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String? description;
  @override
  final String? difficulty;
  @override
  final int? estimatedMinutes;
  final List<String> _tags;
  @override
  @JsonKey()
  List<String> get tags {
    if (_tags is EqualUnmodifiableListView) return _tags;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_tags);
  }

  final List<String> _equipmentSummary;
  @override
  @JsonKey()
  List<String> get equipmentSummary {
    if (_equipmentSummary is EqualUnmodifiableListView)
      return _equipmentSummary;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_equipmentSummary);
  }

  final List<WorkoutBlock> _blocks;
  @override
  @JsonKey()
  List<WorkoutBlock> get blocks {
    if (_blocks is EqualUnmodifiableListView) return _blocks;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_blocks);
  }

  @override
  final DateTime? updatedAt;
  @override
  final int? downloadSizeBytes;

  /// Create a copy of Workout
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$WorkoutCopyWith<_Workout> get copyWith =>
      __$WorkoutCopyWithImpl<_Workout>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$WorkoutToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Workout &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.difficulty, difficulty) ||
                other.difficulty == difficulty) &&
            (identical(other.estimatedMinutes, estimatedMinutes) ||
                other.estimatedMinutes == estimatedMinutes) &&
            const DeepCollectionEquality().equals(other.tags, _tags) &&
            const DeepCollectionEquality().equals(
              other.equipmentSummary,
              _equipmentSummary,
            ) &&
            const DeepCollectionEquality().equals(other.blocks, _blocks) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt) &&
            (identical(other.downloadSizeBytes, downloadSizeBytes) ||
                other.downloadSizeBytes == downloadSizeBytes));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      name,
      description,
      difficulty,
      estimatedMinutes,
      const DeepCollectionEquality().hash(_tags),
      const DeepCollectionEquality().hash(_equipmentSummary),
      const DeepCollectionEquality().hash(_blocks),
      updatedAt,
      downloadSizeBytes,
    );
  }

  @override
  String toString() {
    return 'Workout(id: $id, name: $name, description: $description, difficulty: $difficulty, estimatedMinutes: $estimatedMinutes, tags: $tags, equipmentSummary: $equipmentSummary, blocks: $blocks, updatedAt: $updatedAt, downloadSizeBytes: $downloadSizeBytes)';
  }
}

/// @nodoc
abstract mixin class _$WorkoutCopyWith<$Res> implements $WorkoutCopyWith<$Res> {
  factory _$WorkoutCopyWith(_Workout value, $Res Function(_Workout) _then) =
      __$WorkoutCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String? description,
    String? difficulty,
    int? estimatedMinutes,
    List<String> tags,
    List<String> equipmentSummary,
    List<WorkoutBlock> blocks,
    DateTime? updatedAt,
    int? downloadSizeBytes,
  });
}

/// @nodoc
class __$WorkoutCopyWithImpl<$Res> implements _$WorkoutCopyWith<$Res> {
  __$WorkoutCopyWithImpl(this._self, this._then);

  final _Workout _self;
  final $Res Function(_Workout) _then;

  /// Create a copy of Workout
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = freezed,
    Object? difficulty = freezed,
    Object? estimatedMinutes = freezed,
    Object? tags = null,
    Object? equipmentSummary = null,
    Object? blocks = null,
    Object? updatedAt = freezed,
    Object? downloadSizeBytes = freezed,
  }) {
    return _then(
      _Workout(
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
        difficulty: freezed == difficulty
            ? _self.difficulty
            : difficulty // ignore: cast_nullable_to_non_nullable
                  as String?,
        estimatedMinutes: freezed == estimatedMinutes
            ? _self.estimatedMinutes
            : estimatedMinutes // ignore: cast_nullable_to_non_nullable
                  as int?,
        tags: null == tags
            ? _self._tags
            : tags // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        equipmentSummary: null == equipmentSummary
            ? _self._equipmentSummary
            : equipmentSummary // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        blocks: null == blocks
            ? _self._blocks
            : blocks // ignore: cast_nullable_to_non_nullable
                  as List<WorkoutBlock>,
        updatedAt: freezed == updatedAt
            ? _self.updatedAt
            : updatedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        downloadSizeBytes: freezed == downloadSizeBytes
            ? _self.downloadSizeBytes
            : downloadSizeBytes // ignore: cast_nullable_to_non_nullable
                  as int?,
      ),
    );
  }
}

/// @nodoc
mixin _$WorkoutRef {
  String get id;
  String get name;
  int? get estimatedMinutes;

  /// Create a copy of WorkoutRef
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $WorkoutRefCopyWith<WorkoutRef> get copyWith =>
      _$WorkoutRefCopyWithImpl<WorkoutRef>(this as WorkoutRef, _$identity);

  /// Serializes this WorkoutRef to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as WorkoutRef;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is WorkoutRef &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.estimatedMinutes, _this.estimatedMinutes) ||
                other.estimatedMinutes == _this.estimatedMinutes));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as WorkoutRef;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.name,
      _this.estimatedMinutes,
    );
  }

  @override
  String toString() {
    final _this = this as WorkoutRef;
    return 'WorkoutRef(id: ${_this.id}, name: ${_this.name}, estimatedMinutes: ${_this.estimatedMinutes})';
  }
}

/// @nodoc
abstract mixin class $WorkoutRefCopyWith<$Res> {
  factory $WorkoutRefCopyWith(
    WorkoutRef value,
    $Res Function(WorkoutRef) _then,
  ) = _$WorkoutRefCopyWithImpl;
  @useResult
  $Res call({String id, String name, int? estimatedMinutes});
}

/// @nodoc
class _$WorkoutRefCopyWithImpl<$Res> implements $WorkoutRefCopyWith<$Res> {
  _$WorkoutRefCopyWithImpl(this._self, this._then);

  final WorkoutRef _self;
  final $Res Function(WorkoutRef) _then;

  /// Create a copy of WorkoutRef
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? estimatedMinutes = freezed,
  }) {
    return _then(
      WorkoutRef(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        estimatedMinutes: freezed == estimatedMinutes
            ? _self.estimatedMinutes
            : estimatedMinutes // ignore: cast_nullable_to_non_nullable
                  as int?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [WorkoutRef].
extension WorkoutRefPatterns on WorkoutRef {
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
    TResult Function(_WorkoutRef value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _WorkoutRef() when $default != null:
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
    TResult Function(_WorkoutRef value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutRef():
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
    TResult? Function(_WorkoutRef value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutRef() when $default != null:
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
    TResult Function(String id, String name, int? estimatedMinutes)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _WorkoutRef() when $default != null:
        return $default(_that.id, _that.name, _that.estimatedMinutes);
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
    TResult Function(String id, String name, int? estimatedMinutes) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutRef():
        return $default(_that.id, _that.name, _that.estimatedMinutes);
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
    TResult? Function(String id, String name, int? estimatedMinutes)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WorkoutRef() when $default != null:
        return $default(_that.id, _that.name, _that.estimatedMinutes);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _WorkoutRef implements WorkoutRef {
  const _WorkoutRef({
    required this.id,
    required this.name,
    this.estimatedMinutes,
  });
  factory _WorkoutRef.fromJson(Map<String, dynamic> json) =>
      _$WorkoutRefFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final int? estimatedMinutes;

  /// Create a copy of WorkoutRef
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$WorkoutRefCopyWith<_WorkoutRef> get copyWith =>
      __$WorkoutRefCopyWithImpl<_WorkoutRef>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$WorkoutRefToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _WorkoutRef &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.estimatedMinutes, estimatedMinutes) ||
                other.estimatedMinutes == estimatedMinutes));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, name, estimatedMinutes);
  }

  @override
  String toString() {
    return 'WorkoutRef(id: $id, name: $name, estimatedMinutes: $estimatedMinutes)';
  }
}

/// @nodoc
abstract mixin class _$WorkoutRefCopyWith<$Res>
    implements $WorkoutRefCopyWith<$Res> {
  factory _$WorkoutRefCopyWith(
    _WorkoutRef value,
    $Res Function(_WorkoutRef) _then,
  ) = __$WorkoutRefCopyWithImpl;
  @override
  @useResult
  $Res call({String id, String name, int? estimatedMinutes});
}

/// @nodoc
class __$WorkoutRefCopyWithImpl<$Res> implements _$WorkoutRefCopyWith<$Res> {
  __$WorkoutRefCopyWithImpl(this._self, this._then);

  final _WorkoutRef _self;
  final $Res Function(_WorkoutRef) _then;

  /// Create a copy of WorkoutRef
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? estimatedMinutes = freezed,
  }) {
    return _then(
      _WorkoutRef(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        estimatedMinutes: freezed == estimatedMinutes
            ? _self.estimatedMinutes
            : estimatedMinutes // ignore: cast_nullable_to_non_nullable
                  as int?,
      ),
    );
  }
}

/// @nodoc
mixin _$ProgramDay {
  String get id;
  int get dayNumber;
  String? get title;
  bool get isRest;
  WorkoutRef? get workout;
  String? get mealPlanId;
  List<String> get habitIds;

  /// Create a copy of ProgramDay
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ProgramDayCopyWith<ProgramDay> get copyWith =>
      _$ProgramDayCopyWithImpl<ProgramDay>(this as ProgramDay, _$identity);

  /// Serializes this ProgramDay to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as ProgramDay;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is ProgramDay &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.dayNumber, _this.dayNumber) ||
                other.dayNumber == _this.dayNumber) &&
            (identical(other.title, _this.title) ||
                other.title == _this.title) &&
            (identical(other.isRest, _this.isRest) ||
                other.isRest == _this.isRest) &&
            (identical(other.workout, _this.workout) ||
                other.workout == _this.workout) &&
            (identical(other.mealPlanId, _this.mealPlanId) ||
                other.mealPlanId == _this.mealPlanId) &&
            const DeepCollectionEquality().equals(
              other.habitIds,
              _this.habitIds,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as ProgramDay;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.dayNumber,
      _this.title,
      _this.isRest,
      _this.workout,
      _this.mealPlanId,
      const DeepCollectionEquality().hash(_this.habitIds),
    );
  }

  @override
  String toString() {
    final _this = this as ProgramDay;
    return 'ProgramDay(id: ${_this.id}, dayNumber: ${_this.dayNumber}, title: ${_this.title}, isRest: ${_this.isRest}, workout: ${_this.workout}, mealPlanId: ${_this.mealPlanId}, habitIds: ${_this.habitIds})';
  }
}

/// @nodoc
abstract mixin class $ProgramDayCopyWith<$Res> {
  factory $ProgramDayCopyWith(
    ProgramDay value,
    $Res Function(ProgramDay) _then,
  ) = _$ProgramDayCopyWithImpl;
  @useResult
  $Res call({
    String id,
    int dayNumber,
    String? title,
    bool isRest,
    WorkoutRef? workout,
    String? mealPlanId,
    List<String> habitIds,
  });

  $WorkoutRefCopyWith<$Res>? get workout;
}

/// @nodoc
class _$ProgramDayCopyWithImpl<$Res> implements $ProgramDayCopyWith<$Res> {
  _$ProgramDayCopyWithImpl(this._self, this._then);

  final ProgramDay _self;
  final $Res Function(ProgramDay) _then;

  /// Create a copy of ProgramDay
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? dayNumber = null,
    Object? title = freezed,
    Object? isRest = null,
    Object? workout = freezed,
    Object? mealPlanId = freezed,
    Object? habitIds = null,
  }) {
    return _then(
      ProgramDay(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        dayNumber: null == dayNumber
            ? _self.dayNumber
            : dayNumber // ignore: cast_nullable_to_non_nullable
                  as int,
        title: freezed == title
            ? _self.title
            : title // ignore: cast_nullable_to_non_nullable
                  as String?,
        isRest: null == isRest
            ? _self.isRest
            : isRest // ignore: cast_nullable_to_non_nullable
                  as bool,
        workout: freezed == workout
            ? _self.workout
            : workout // ignore: cast_nullable_to_non_nullable
                  as WorkoutRef?,
        mealPlanId: freezed == mealPlanId
            ? _self.mealPlanId
            : mealPlanId // ignore: cast_nullable_to_non_nullable
                  as String?,
        habitIds: null == habitIds
            ? _self.habitIds
            : habitIds // ignore: cast_nullable_to_non_nullable
                  as List<String>,
      ),
    );
  }

  /// Create a copy of ProgramDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $WorkoutRefCopyWith<$Res>? get workout {
    if (_self.workout == null) {
      return null;
    }

    return $WorkoutRefCopyWith<$Res>(_self.workout!, (value) {
      return _then(_self.copyWith(workout: value));
    });
  }
}

/// Adds pattern-matching-related methods to [ProgramDay].
extension ProgramDayPatterns on ProgramDay {
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
    TResult Function(_ProgramDay value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ProgramDay() when $default != null:
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
    TResult Function(_ProgramDay value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramDay():
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
    TResult? Function(_ProgramDay value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramDay() when $default != null:
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
      int dayNumber,
      String? title,
      bool isRest,
      WorkoutRef? workout,
      String? mealPlanId,
      List<String> habitIds,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ProgramDay() when $default != null:
        return $default(
          _that.id,
          _that.dayNumber,
          _that.title,
          _that.isRest,
          _that.workout,
          _that.mealPlanId,
          _that.habitIds,
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
      int dayNumber,
      String? title,
      bool isRest,
      WorkoutRef? workout,
      String? mealPlanId,
      List<String> habitIds,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramDay():
        return $default(
          _that.id,
          _that.dayNumber,
          _that.title,
          _that.isRest,
          _that.workout,
          _that.mealPlanId,
          _that.habitIds,
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
      int dayNumber,
      String? title,
      bool isRest,
      WorkoutRef? workout,
      String? mealPlanId,
      List<String> habitIds,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramDay() when $default != null:
        return $default(
          _that.id,
          _that.dayNumber,
          _that.title,
          _that.isRest,
          _that.workout,
          _that.mealPlanId,
          _that.habitIds,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _ProgramDay implements ProgramDay {
  const _ProgramDay({
    required this.id,
    required this.dayNumber,
    this.title,
    this.isRest = false,
    this.workout,
    this.mealPlanId,
    List<String> habitIds = const <String>[],
  }) : _habitIds = habitIds;
  factory _ProgramDay.fromJson(Map<String, dynamic> json) =>
      _$ProgramDayFromJson(json);

  @override
  final String id;
  @override
  final int dayNumber;
  @override
  final String? title;
  @override
  @JsonKey()
  final bool isRest;
  @override
  final WorkoutRef? workout;
  @override
  final String? mealPlanId;
  final List<String> _habitIds;
  @override
  @JsonKey()
  List<String> get habitIds {
    if (_habitIds is EqualUnmodifiableListView) return _habitIds;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_habitIds);
  }

  /// Create a copy of ProgramDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ProgramDayCopyWith<_ProgramDay> get copyWith =>
      __$ProgramDayCopyWithImpl<_ProgramDay>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ProgramDayToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _ProgramDay &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.dayNumber, dayNumber) ||
                other.dayNumber == dayNumber) &&
            (identical(other.title, title) || other.title == title) &&
            (identical(other.isRest, isRest) || other.isRest == isRest) &&
            (identical(other.workout, workout) || other.workout == workout) &&
            (identical(other.mealPlanId, mealPlanId) ||
                other.mealPlanId == mealPlanId) &&
            const DeepCollectionEquality().equals(other.habitIds, _habitIds));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      dayNumber,
      title,
      isRest,
      workout,
      mealPlanId,
      const DeepCollectionEquality().hash(_habitIds),
    );
  }

  @override
  String toString() {
    return 'ProgramDay(id: $id, dayNumber: $dayNumber, title: $title, isRest: $isRest, workout: $workout, mealPlanId: $mealPlanId, habitIds: $habitIds)';
  }
}

/// @nodoc
abstract mixin class _$ProgramDayCopyWith<$Res>
    implements $ProgramDayCopyWith<$Res> {
  factory _$ProgramDayCopyWith(
    _ProgramDay value,
    $Res Function(_ProgramDay) _then,
  ) = __$ProgramDayCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    int dayNumber,
    String? title,
    bool isRest,
    WorkoutRef? workout,
    String? mealPlanId,
    List<String> habitIds,
  });

  @override
  $WorkoutRefCopyWith<$Res>? get workout;
}

/// @nodoc
class __$ProgramDayCopyWithImpl<$Res> implements _$ProgramDayCopyWith<$Res> {
  __$ProgramDayCopyWithImpl(this._self, this._then);

  final _ProgramDay _self;
  final $Res Function(_ProgramDay) _then;

  /// Create a copy of ProgramDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? dayNumber = null,
    Object? title = freezed,
    Object? isRest = null,
    Object? workout = freezed,
    Object? mealPlanId = freezed,
    Object? habitIds = null,
  }) {
    return _then(
      _ProgramDay(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        dayNumber: null == dayNumber
            ? _self.dayNumber
            : dayNumber // ignore: cast_nullable_to_non_nullable
                  as int,
        title: freezed == title
            ? _self.title
            : title // ignore: cast_nullable_to_non_nullable
                  as String?,
        isRest: null == isRest
            ? _self.isRest
            : isRest // ignore: cast_nullable_to_non_nullable
                  as bool,
        workout: freezed == workout
            ? _self.workout
            : workout // ignore: cast_nullable_to_non_nullable
                  as WorkoutRef?,
        mealPlanId: freezed == mealPlanId
            ? _self.mealPlanId
            : mealPlanId // ignore: cast_nullable_to_non_nullable
                  as String?,
        habitIds: null == habitIds
            ? _self._habitIds
            : habitIds // ignore: cast_nullable_to_non_nullable
                  as List<String>,
      ),
    );
  }

  /// Create a copy of ProgramDay
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $WorkoutRefCopyWith<$Res>? get workout {
    if (_self.workout == null) {
      return null;
    }

    return $WorkoutRefCopyWith<$Res>(_self.workout!, (value) {
      return _then(_self.copyWith(workout: value));
    });
  }
}

/// @nodoc
mixin _$ProgramWeek {
  String get id;
  int get number;
  String? get phase;
  bool get isDeload;
  String? get notes;
  List<ProgramDay> get days;

  /// Create a copy of ProgramWeek
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ProgramWeekCopyWith<ProgramWeek> get copyWith =>
      _$ProgramWeekCopyWithImpl<ProgramWeek>(this as ProgramWeek, _$identity);

  /// Serializes this ProgramWeek to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as ProgramWeek;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is ProgramWeek &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.number, _this.number) ||
                other.number == _this.number) &&
            (identical(other.phase, _this.phase) ||
                other.phase == _this.phase) &&
            (identical(other.isDeload, _this.isDeload) ||
                other.isDeload == _this.isDeload) &&
            (identical(other.notes, _this.notes) ||
                other.notes == _this.notes) &&
            const DeepCollectionEquality().equals(other.days, _this.days));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as ProgramWeek;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.number,
      _this.phase,
      _this.isDeload,
      _this.notes,
      const DeepCollectionEquality().hash(_this.days),
    );
  }

  @override
  String toString() {
    final _this = this as ProgramWeek;
    return 'ProgramWeek(id: ${_this.id}, number: ${_this.number}, phase: ${_this.phase}, isDeload: ${_this.isDeload}, notes: ${_this.notes}, days: ${_this.days})';
  }
}

/// @nodoc
abstract mixin class $ProgramWeekCopyWith<$Res> {
  factory $ProgramWeekCopyWith(
    ProgramWeek value,
    $Res Function(ProgramWeek) _then,
  ) = _$ProgramWeekCopyWithImpl;
  @useResult
  $Res call({
    String id,
    int number,
    String? phase,
    bool isDeload,
    String? notes,
    List<ProgramDay> days,
  });
}

/// @nodoc
class _$ProgramWeekCopyWithImpl<$Res> implements $ProgramWeekCopyWith<$Res> {
  _$ProgramWeekCopyWithImpl(this._self, this._then);

  final ProgramWeek _self;
  final $Res Function(ProgramWeek) _then;

  /// Create a copy of ProgramWeek
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? number = null,
    Object? phase = freezed,
    Object? isDeload = null,
    Object? notes = freezed,
    Object? days = null,
  }) {
    return _then(
      ProgramWeek(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        number: null == number
            ? _self.number
            : number // ignore: cast_nullable_to_non_nullable
                  as int,
        phase: freezed == phase
            ? _self.phase
            : phase // ignore: cast_nullable_to_non_nullable
                  as String?,
        isDeload: null == isDeload
            ? _self.isDeload
            : isDeload // ignore: cast_nullable_to_non_nullable
                  as bool,
        notes: freezed == notes
            ? _self.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        days: null == days
            ? _self.days
            : days // ignore: cast_nullable_to_non_nullable
                  as List<ProgramDay>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [ProgramWeek].
extension ProgramWeekPatterns on ProgramWeek {
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
    TResult Function(_ProgramWeek value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ProgramWeek() when $default != null:
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
    TResult Function(_ProgramWeek value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramWeek():
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
    TResult? Function(_ProgramWeek value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramWeek() when $default != null:
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
      int number,
      String? phase,
      bool isDeload,
      String? notes,
      List<ProgramDay> days,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ProgramWeek() when $default != null:
        return $default(
          _that.id,
          _that.number,
          _that.phase,
          _that.isDeload,
          _that.notes,
          _that.days,
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
      int number,
      String? phase,
      bool isDeload,
      String? notes,
      List<ProgramDay> days,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramWeek():
        return $default(
          _that.id,
          _that.number,
          _that.phase,
          _that.isDeload,
          _that.notes,
          _that.days,
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
      int number,
      String? phase,
      bool isDeload,
      String? notes,
      List<ProgramDay> days,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramWeek() when $default != null:
        return $default(
          _that.id,
          _that.number,
          _that.phase,
          _that.isDeload,
          _that.notes,
          _that.days,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _ProgramWeek implements ProgramWeek {
  const _ProgramWeek({
    required this.id,
    required this.number,
    this.phase,
    this.isDeload = false,
    this.notes,
    List<ProgramDay> days = const <ProgramDay>[],
  }) : _days = days;
  factory _ProgramWeek.fromJson(Map<String, dynamic> json) =>
      _$ProgramWeekFromJson(json);

  @override
  final String id;
  @override
  final int number;
  @override
  final String? phase;
  @override
  @JsonKey()
  final bool isDeload;
  @override
  final String? notes;
  final List<ProgramDay> _days;
  @override
  @JsonKey()
  List<ProgramDay> get days {
    if (_days is EqualUnmodifiableListView) return _days;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_days);
  }

  /// Create a copy of ProgramWeek
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ProgramWeekCopyWith<_ProgramWeek> get copyWith =>
      __$ProgramWeekCopyWithImpl<_ProgramWeek>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ProgramWeekToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _ProgramWeek &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.number, number) || other.number == number) &&
            (identical(other.phase, phase) || other.phase == phase) &&
            (identical(other.isDeload, isDeload) ||
                other.isDeload == isDeload) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            const DeepCollectionEquality().equals(other.days, _days));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      number,
      phase,
      isDeload,
      notes,
      const DeepCollectionEquality().hash(_days),
    );
  }

  @override
  String toString() {
    return 'ProgramWeek(id: $id, number: $number, phase: $phase, isDeload: $isDeload, notes: $notes, days: $days)';
  }
}

/// @nodoc
abstract mixin class _$ProgramWeekCopyWith<$Res>
    implements $ProgramWeekCopyWith<$Res> {
  factory _$ProgramWeekCopyWith(
    _ProgramWeek value,
    $Res Function(_ProgramWeek) _then,
  ) = __$ProgramWeekCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    int number,
    String? phase,
    bool isDeload,
    String? notes,
    List<ProgramDay> days,
  });
}

/// @nodoc
class __$ProgramWeekCopyWithImpl<$Res> implements _$ProgramWeekCopyWith<$Res> {
  __$ProgramWeekCopyWithImpl(this._self, this._then);

  final _ProgramWeek _self;
  final $Res Function(_ProgramWeek) _then;

  /// Create a copy of ProgramWeek
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? number = null,
    Object? phase = freezed,
    Object? isDeload = null,
    Object? notes = freezed,
    Object? days = null,
  }) {
    return _then(
      _ProgramWeek(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        number: null == number
            ? _self.number
            : number // ignore: cast_nullable_to_non_nullable
                  as int,
        phase: freezed == phase
            ? _self.phase
            : phase // ignore: cast_nullable_to_non_nullable
                  as String?,
        isDeload: null == isDeload
            ? _self.isDeload
            : isDeload // ignore: cast_nullable_to_non_nullable
                  as bool,
        notes: freezed == notes
            ? _self.notes
            : notes // ignore: cast_nullable_to_non_nullable
                  as String?,
        days: null == days
            ? _self._days
            : days // ignore: cast_nullable_to_non_nullable
                  as List<ProgramDay>,
      ),
    );
  }
}

/// @nodoc
mixin _$Program {
  String get id;
  String get name;
  String? get description;
  String? get goal;
  String? get level;
  int? get daysPerWeek;
  List<String> get equipment;
  List<String> get tags;
  String? get visibility;
  bool get isTemplate;
  int? get weeksCount;
  List<ProgramWeek> get weeks;

  /// Create a copy of Program
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ProgramCopyWith<Program> get copyWith =>
      _$ProgramCopyWithImpl<Program>(this as Program, _$identity);

  /// Serializes this Program to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Program;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Program &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.description, _this.description) ||
                other.description == _this.description) &&
            (identical(other.goal, _this.goal) || other.goal == _this.goal) &&
            (identical(other.level, _this.level) ||
                other.level == _this.level) &&
            (identical(other.daysPerWeek, _this.daysPerWeek) ||
                other.daysPerWeek == _this.daysPerWeek) &&
            const DeepCollectionEquality().equals(
              other.equipment,
              _this.equipment,
            ) &&
            const DeepCollectionEquality().equals(other.tags, _this.tags) &&
            (identical(other.visibility, _this.visibility) ||
                other.visibility == _this.visibility) &&
            (identical(other.isTemplate, _this.isTemplate) ||
                other.isTemplate == _this.isTemplate) &&
            (identical(other.weeksCount, _this.weeksCount) ||
                other.weeksCount == _this.weeksCount) &&
            const DeepCollectionEquality().equals(other.weeks, _this.weeks));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Program;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.name,
      _this.description,
      _this.goal,
      _this.level,
      _this.daysPerWeek,
      const DeepCollectionEquality().hash(_this.equipment),
      const DeepCollectionEquality().hash(_this.tags),
      _this.visibility,
      _this.isTemplate,
      _this.weeksCount,
      const DeepCollectionEquality().hash(_this.weeks),
    );
  }

  @override
  String toString() {
    final _this = this as Program;
    return 'Program(id: ${_this.id}, name: ${_this.name}, description: ${_this.description}, goal: ${_this.goal}, level: ${_this.level}, daysPerWeek: ${_this.daysPerWeek}, equipment: ${_this.equipment}, tags: ${_this.tags}, visibility: ${_this.visibility}, isTemplate: ${_this.isTemplate}, weeksCount: ${_this.weeksCount}, weeks: ${_this.weeks})';
  }
}

/// @nodoc
abstract mixin class $ProgramCopyWith<$Res> {
  factory $ProgramCopyWith(Program value, $Res Function(Program) _then) =
      _$ProgramCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String name,
    String? description,
    String? goal,
    String? level,
    int? daysPerWeek,
    List<String> equipment,
    List<String> tags,
    String? visibility,
    bool isTemplate,
    int? weeksCount,
    List<ProgramWeek> weeks,
  });
}

/// @nodoc
class _$ProgramCopyWithImpl<$Res> implements $ProgramCopyWith<$Res> {
  _$ProgramCopyWithImpl(this._self, this._then);

  final Program _self;
  final $Res Function(Program) _then;

  /// Create a copy of Program
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = freezed,
    Object? goal = freezed,
    Object? level = freezed,
    Object? daysPerWeek = freezed,
    Object? equipment = null,
    Object? tags = null,
    Object? visibility = freezed,
    Object? isTemplate = null,
    Object? weeksCount = freezed,
    Object? weeks = null,
  }) {
    return _then(
      Program(
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
        goal: freezed == goal
            ? _self.goal
            : goal // ignore: cast_nullable_to_non_nullable
                  as String?,
        level: freezed == level
            ? _self.level
            : level // ignore: cast_nullable_to_non_nullable
                  as String?,
        daysPerWeek: freezed == daysPerWeek
            ? _self.daysPerWeek
            : daysPerWeek // ignore: cast_nullable_to_non_nullable
                  as int?,
        equipment: null == equipment
            ? _self.equipment
            : equipment // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        tags: null == tags
            ? _self.tags
            : tags // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        visibility: freezed == visibility
            ? _self.visibility
            : visibility // ignore: cast_nullable_to_non_nullable
                  as String?,
        isTemplate: null == isTemplate
            ? _self.isTemplate
            : isTemplate // ignore: cast_nullable_to_non_nullable
                  as bool,
        weeksCount: freezed == weeksCount
            ? _self.weeksCount
            : weeksCount // ignore: cast_nullable_to_non_nullable
                  as int?,
        weeks: null == weeks
            ? _self.weeks
            : weeks // ignore: cast_nullable_to_non_nullable
                  as List<ProgramWeek>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Program].
extension ProgramPatterns on Program {
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
    TResult Function(_Program value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Program() when $default != null:
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
    TResult Function(_Program value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Program():
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
    TResult? Function(_Program value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Program() when $default != null:
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
      String? goal,
      String? level,
      int? daysPerWeek,
      List<String> equipment,
      List<String> tags,
      String? visibility,
      bool isTemplate,
      int? weeksCount,
      List<ProgramWeek> weeks,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Program() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.goal,
          _that.level,
          _that.daysPerWeek,
          _that.equipment,
          _that.tags,
          _that.visibility,
          _that.isTemplate,
          _that.weeksCount,
          _that.weeks,
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
      String? goal,
      String? level,
      int? daysPerWeek,
      List<String> equipment,
      List<String> tags,
      String? visibility,
      bool isTemplate,
      int? weeksCount,
      List<ProgramWeek> weeks,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Program():
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.goal,
          _that.level,
          _that.daysPerWeek,
          _that.equipment,
          _that.tags,
          _that.visibility,
          _that.isTemplate,
          _that.weeksCount,
          _that.weeks,
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
      String? goal,
      String? level,
      int? daysPerWeek,
      List<String> equipment,
      List<String> tags,
      String? visibility,
      bool isTemplate,
      int? weeksCount,
      List<ProgramWeek> weeks,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Program() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.description,
          _that.goal,
          _that.level,
          _that.daysPerWeek,
          _that.equipment,
          _that.tags,
          _that.visibility,
          _that.isTemplate,
          _that.weeksCount,
          _that.weeks,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _Program implements Program {
  const _Program({
    required this.id,
    required this.name,
    this.description,
    this.goal,
    this.level,
    this.daysPerWeek,
    List<String> equipment = const <String>[],
    List<String> tags = const <String>[],
    this.visibility,
    this.isTemplate = false,
    this.weeksCount,
    List<ProgramWeek> weeks = const <ProgramWeek>[],
  }) : _equipment = equipment,
       _tags = tags,
       _weeks = weeks;
  factory _Program.fromJson(Map<String, dynamic> json) =>
      _$ProgramFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String? description;
  @override
  final String? goal;
  @override
  final String? level;
  @override
  final int? daysPerWeek;
  final List<String> _equipment;
  @override
  @JsonKey()
  List<String> get equipment {
    if (_equipment is EqualUnmodifiableListView) return _equipment;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_equipment);
  }

  final List<String> _tags;
  @override
  @JsonKey()
  List<String> get tags {
    if (_tags is EqualUnmodifiableListView) return _tags;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_tags);
  }

  @override
  final String? visibility;
  @override
  @JsonKey()
  final bool isTemplate;
  @override
  final int? weeksCount;
  final List<ProgramWeek> _weeks;
  @override
  @JsonKey()
  List<ProgramWeek> get weeks {
    if (_weeks is EqualUnmodifiableListView) return _weeks;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_weeks);
  }

  /// Create a copy of Program
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ProgramCopyWith<_Program> get copyWith =>
      __$ProgramCopyWithImpl<_Program>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ProgramToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Program &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.goal, goal) || other.goal == goal) &&
            (identical(other.level, level) || other.level == level) &&
            (identical(other.daysPerWeek, daysPerWeek) ||
                other.daysPerWeek == daysPerWeek) &&
            const DeepCollectionEquality().equals(
              other.equipment,
              _equipment,
            ) &&
            const DeepCollectionEquality().equals(other.tags, _tags) &&
            (identical(other.visibility, visibility) ||
                other.visibility == visibility) &&
            (identical(other.isTemplate, isTemplate) ||
                other.isTemplate == isTemplate) &&
            (identical(other.weeksCount, weeksCount) ||
                other.weeksCount == weeksCount) &&
            const DeepCollectionEquality().equals(other.weeks, _weeks));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      name,
      description,
      goal,
      level,
      daysPerWeek,
      const DeepCollectionEquality().hash(_equipment),
      const DeepCollectionEquality().hash(_tags),
      visibility,
      isTemplate,
      weeksCount,
      const DeepCollectionEquality().hash(_weeks),
    );
  }

  @override
  String toString() {
    return 'Program(id: $id, name: $name, description: $description, goal: $goal, level: $level, daysPerWeek: $daysPerWeek, equipment: $equipment, tags: $tags, visibility: $visibility, isTemplate: $isTemplate, weeksCount: $weeksCount, weeks: $weeks)';
  }
}

/// @nodoc
abstract mixin class _$ProgramCopyWith<$Res> implements $ProgramCopyWith<$Res> {
  factory _$ProgramCopyWith(_Program value, $Res Function(_Program) _then) =
      __$ProgramCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String? description,
    String? goal,
    String? level,
    int? daysPerWeek,
    List<String> equipment,
    List<String> tags,
    String? visibility,
    bool isTemplate,
    int? weeksCount,
    List<ProgramWeek> weeks,
  });
}

/// @nodoc
class __$ProgramCopyWithImpl<$Res> implements _$ProgramCopyWith<$Res> {
  __$ProgramCopyWithImpl(this._self, this._then);

  final _Program _self;
  final $Res Function(_Program) _then;

  /// Create a copy of Program
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? description = freezed,
    Object? goal = freezed,
    Object? level = freezed,
    Object? daysPerWeek = freezed,
    Object? equipment = null,
    Object? tags = null,
    Object? visibility = freezed,
    Object? isTemplate = null,
    Object? weeksCount = freezed,
    Object? weeks = null,
  }) {
    return _then(
      _Program(
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
        goal: freezed == goal
            ? _self.goal
            : goal // ignore: cast_nullable_to_non_nullable
                  as String?,
        level: freezed == level
            ? _self.level
            : level // ignore: cast_nullable_to_non_nullable
                  as String?,
        daysPerWeek: freezed == daysPerWeek
            ? _self.daysPerWeek
            : daysPerWeek // ignore: cast_nullable_to_non_nullable
                  as int?,
        equipment: null == equipment
            ? _self._equipment
            : equipment // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        tags: null == tags
            ? _self._tags
            : tags // ignore: cast_nullable_to_non_nullable
                  as List<String>,
        visibility: freezed == visibility
            ? _self.visibility
            : visibility // ignore: cast_nullable_to_non_nullable
                  as String?,
        isTemplate: null == isTemplate
            ? _self.isTemplate
            : isTemplate // ignore: cast_nullable_to_non_nullable
                  as bool,
        weeksCount: freezed == weeksCount
            ? _self.weeksCount
            : weeksCount // ignore: cast_nullable_to_non_nullable
                  as int?,
        weeks: null == weeks
            ? _self._weeks
            : weeks // ignore: cast_nullable_to_non_nullable
                  as List<ProgramWeek>,
      ),
    );
  }
}

/// @nodoc
mixin _$ProgramAssignment {
  String get id;
  IdName get program;
  IdName? get client;
  String get startDate;
  String get status;
  String get unlockMode;
  int get currentWeek;
  double get progressPct;

  /// Create a copy of ProgramAssignment
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ProgramAssignmentCopyWith<ProgramAssignment> get copyWith =>
      _$ProgramAssignmentCopyWithImpl<ProgramAssignment>(
        this as ProgramAssignment,
        _$identity,
      );

  /// Serializes this ProgramAssignment to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as ProgramAssignment;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is ProgramAssignment &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.program, _this.program) ||
                other.program == _this.program) &&
            (identical(other.client, _this.client) ||
                other.client == _this.client) &&
            (identical(other.startDate, _this.startDate) ||
                other.startDate == _this.startDate) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status) &&
            (identical(other.unlockMode, _this.unlockMode) ||
                other.unlockMode == _this.unlockMode) &&
            (identical(other.currentWeek, _this.currentWeek) ||
                other.currentWeek == _this.currentWeek) &&
            (identical(other.progressPct, _this.progressPct) ||
                other.progressPct == _this.progressPct));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as ProgramAssignment;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.program,
      _this.client,
      _this.startDate,
      _this.status,
      _this.unlockMode,
      _this.currentWeek,
      _this.progressPct,
    );
  }

  @override
  String toString() {
    final _this = this as ProgramAssignment;
    return 'ProgramAssignment(id: ${_this.id}, program: ${_this.program}, client: ${_this.client}, startDate: ${_this.startDate}, status: ${_this.status}, unlockMode: ${_this.unlockMode}, currentWeek: ${_this.currentWeek}, progressPct: ${_this.progressPct})';
  }
}

/// @nodoc
abstract mixin class $ProgramAssignmentCopyWith<$Res> {
  factory $ProgramAssignmentCopyWith(
    ProgramAssignment value,
    $Res Function(ProgramAssignment) _then,
  ) = _$ProgramAssignmentCopyWithImpl;
  @useResult
  $Res call({
    String id,
    IdName program,
    IdName? client,
    String startDate,
    String status,
    String unlockMode,
    int currentWeek,
    double progressPct,
  });

  $IdNameCopyWith<$Res> get program;
  $IdNameCopyWith<$Res>? get client;
}

/// @nodoc
class _$ProgramAssignmentCopyWithImpl<$Res>
    implements $ProgramAssignmentCopyWith<$Res> {
  _$ProgramAssignmentCopyWithImpl(this._self, this._then);

  final ProgramAssignment _self;
  final $Res Function(ProgramAssignment) _then;

  /// Create a copy of ProgramAssignment
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? program = null,
    Object? client = freezed,
    Object? startDate = null,
    Object? status = null,
    Object? unlockMode = null,
    Object? currentWeek = null,
    Object? progressPct = null,
  }) {
    return _then(
      ProgramAssignment(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        program: null == program
            ? _self.program
            : program // ignore: cast_nullable_to_non_nullable
                  as IdName,
        client: freezed == client
            ? _self.client
            : client // ignore: cast_nullable_to_non_nullable
                  as IdName?,
        startDate: null == startDate
            ? _self.startDate
            : startDate // ignore: cast_nullable_to_non_nullable
                  as String,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        unlockMode: null == unlockMode
            ? _self.unlockMode
            : unlockMode // ignore: cast_nullable_to_non_nullable
                  as String,
        currentWeek: null == currentWeek
            ? _self.currentWeek
            : currentWeek // ignore: cast_nullable_to_non_nullable
                  as int,
        progressPct: null == progressPct
            ? _self.progressPct
            : progressPct // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }

  /// Create a copy of ProgramAssignment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $IdNameCopyWith<$Res> get program {
    return $IdNameCopyWith<$Res>(_self.program, (value) {
      return _then(_self.copyWith(program: value));
    });
  }

  /// Create a copy of ProgramAssignment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $IdNameCopyWith<$Res>? get client {
    if (_self.client == null) {
      return null;
    }

    return $IdNameCopyWith<$Res>(_self.client!, (value) {
      return _then(_self.copyWith(client: value));
    });
  }
}

/// Adds pattern-matching-related methods to [ProgramAssignment].
extension ProgramAssignmentPatterns on ProgramAssignment {
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
    TResult Function(_ProgramAssignment value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ProgramAssignment() when $default != null:
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
    TResult Function(_ProgramAssignment value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramAssignment():
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
    TResult? Function(_ProgramAssignment value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramAssignment() when $default != null:
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
      IdName program,
      IdName? client,
      String startDate,
      String status,
      String unlockMode,
      int currentWeek,
      double progressPct,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ProgramAssignment() when $default != null:
        return $default(
          _that.id,
          _that.program,
          _that.client,
          _that.startDate,
          _that.status,
          _that.unlockMode,
          _that.currentWeek,
          _that.progressPct,
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
      IdName program,
      IdName? client,
      String startDate,
      String status,
      String unlockMode,
      int currentWeek,
      double progressPct,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramAssignment():
        return $default(
          _that.id,
          _that.program,
          _that.client,
          _that.startDate,
          _that.status,
          _that.unlockMode,
          _that.currentWeek,
          _that.progressPct,
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
      IdName program,
      IdName? client,
      String startDate,
      String status,
      String unlockMode,
      int currentWeek,
      double progressPct,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgramAssignment() when $default != null:
        return $default(
          _that.id,
          _that.program,
          _that.client,
          _that.startDate,
          _that.status,
          _that.unlockMode,
          _that.currentWeek,
          _that.progressPct,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _ProgramAssignment implements ProgramAssignment {
  const _ProgramAssignment({
    required this.id,
    required this.program,
    this.client,
    required this.startDate,
    this.status = 'active',
    this.unlockMode = 'all',
    this.currentWeek = 1,
    this.progressPct = 0,
  });
  factory _ProgramAssignment.fromJson(Map<String, dynamic> json) =>
      _$ProgramAssignmentFromJson(json);

  @override
  final String id;
  @override
  final IdName program;
  @override
  final IdName? client;
  @override
  final String startDate;
  @override
  @JsonKey()
  final String status;
  @override
  @JsonKey()
  final String unlockMode;
  @override
  @JsonKey()
  final int currentWeek;
  @override
  @JsonKey()
  final double progressPct;

  /// Create a copy of ProgramAssignment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ProgramAssignmentCopyWith<_ProgramAssignment> get copyWith =>
      __$ProgramAssignmentCopyWithImpl<_ProgramAssignment>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ProgramAssignmentToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _ProgramAssignment &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.program, program) || other.program == program) &&
            (identical(other.client, client) || other.client == client) &&
            (identical(other.startDate, startDate) ||
                other.startDate == startDate) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.unlockMode, unlockMode) ||
                other.unlockMode == unlockMode) &&
            (identical(other.currentWeek, currentWeek) ||
                other.currentWeek == currentWeek) &&
            (identical(other.progressPct, progressPct) ||
                other.progressPct == progressPct));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      program,
      client,
      startDate,
      status,
      unlockMode,
      currentWeek,
      progressPct,
    );
  }

  @override
  String toString() {
    return 'ProgramAssignment(id: $id, program: $program, client: $client, startDate: $startDate, status: $status, unlockMode: $unlockMode, currentWeek: $currentWeek, progressPct: $progressPct)';
  }
}

/// @nodoc
abstract mixin class _$ProgramAssignmentCopyWith<$Res>
    implements $ProgramAssignmentCopyWith<$Res> {
  factory _$ProgramAssignmentCopyWith(
    _ProgramAssignment value,
    $Res Function(_ProgramAssignment) _then,
  ) = __$ProgramAssignmentCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    IdName program,
    IdName? client,
    String startDate,
    String status,
    String unlockMode,
    int currentWeek,
    double progressPct,
  });

  @override
  $IdNameCopyWith<$Res> get program;
  @override
  $IdNameCopyWith<$Res>? get client;
}

/// @nodoc
class __$ProgramAssignmentCopyWithImpl<$Res>
    implements _$ProgramAssignmentCopyWith<$Res> {
  __$ProgramAssignmentCopyWithImpl(this._self, this._then);

  final _ProgramAssignment _self;
  final $Res Function(_ProgramAssignment) _then;

  /// Create a copy of ProgramAssignment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? program = null,
    Object? client = freezed,
    Object? startDate = null,
    Object? status = null,
    Object? unlockMode = null,
    Object? currentWeek = null,
    Object? progressPct = null,
  }) {
    return _then(
      _ProgramAssignment(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        program: null == program
            ? _self.program
            : program // ignore: cast_nullable_to_non_nullable
                  as IdName,
        client: freezed == client
            ? _self.client
            : client // ignore: cast_nullable_to_non_nullable
                  as IdName?,
        startDate: null == startDate
            ? _self.startDate
            : startDate // ignore: cast_nullable_to_non_nullable
                  as String,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        unlockMode: null == unlockMode
            ? _self.unlockMode
            : unlockMode // ignore: cast_nullable_to_non_nullable
                  as String,
        currentWeek: null == currentWeek
            ? _self.currentWeek
            : currentWeek // ignore: cast_nullable_to_non_nullable
                  as int,
        progressPct: null == progressPct
            ? _self.progressPct
            : progressPct // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }

  /// Create a copy of ProgramAssignment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $IdNameCopyWith<$Res> get program {
    return $IdNameCopyWith<$Res>(_self.program, (value) {
      return _then(_self.copyWith(program: value));
    });
  }

  /// Create a copy of ProgramAssignment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $IdNameCopyWith<$Res>? get client {
    if (_self.client == null) {
      return null;
    }

    return $IdNameCopyWith<$Res>(_self.client!, (value) {
      return _then(_self.copyWith(client: value));
    });
  }
}

/// @nodoc
mixin _$MyProgram {
  ProgramAssignment get assignment;
  Program get program;
  String? get unlockedThrough;

  /// Create a copy of MyProgram
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $MyProgramCopyWith<MyProgram> get copyWith =>
      _$MyProgramCopyWithImpl<MyProgram>(this as MyProgram, _$identity);

  /// Serializes this MyProgram to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as MyProgram;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is MyProgram &&
            (identical(other.assignment, _this.assignment) ||
                other.assignment == _this.assignment) &&
            (identical(other.program, _this.program) ||
                other.program == _this.program) &&
            (identical(other.unlockedThrough, _this.unlockedThrough) ||
                other.unlockedThrough == _this.unlockedThrough));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as MyProgram;
    return Object.hash(
      runtimeType,
      _this.assignment,
      _this.program,
      _this.unlockedThrough,
    );
  }

  @override
  String toString() {
    final _this = this as MyProgram;
    return 'MyProgram(assignment: ${_this.assignment}, program: ${_this.program}, unlockedThrough: ${_this.unlockedThrough})';
  }
}

/// @nodoc
abstract mixin class $MyProgramCopyWith<$Res> {
  factory $MyProgramCopyWith(MyProgram value, $Res Function(MyProgram) _then) =
      _$MyProgramCopyWithImpl;
  @useResult
  $Res call({
    ProgramAssignment assignment,
    Program program,
    String? unlockedThrough,
  });

  $ProgramAssignmentCopyWith<$Res> get assignment;
  $ProgramCopyWith<$Res> get program;
}

/// @nodoc
class _$MyProgramCopyWithImpl<$Res> implements $MyProgramCopyWith<$Res> {
  _$MyProgramCopyWithImpl(this._self, this._then);

  final MyProgram _self;
  final $Res Function(MyProgram) _then;

  /// Create a copy of MyProgram
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? assignment = null,
    Object? program = null,
    Object? unlockedThrough = freezed,
  }) {
    return _then(
      MyProgram(
        assignment: null == assignment
            ? _self.assignment
            : assignment // ignore: cast_nullable_to_non_nullable
                  as ProgramAssignment,
        program: null == program
            ? _self.program
            : program // ignore: cast_nullable_to_non_nullable
                  as Program,
        unlockedThrough: freezed == unlockedThrough
            ? _self.unlockedThrough
            : unlockedThrough // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }

  /// Create a copy of MyProgram
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ProgramAssignmentCopyWith<$Res> get assignment {
    return $ProgramAssignmentCopyWith<$Res>(_self.assignment, (value) {
      return _then(_self.copyWith(assignment: value));
    });
  }

  /// Create a copy of MyProgram
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ProgramCopyWith<$Res> get program {
    return $ProgramCopyWith<$Res>(_self.program, (value) {
      return _then(_self.copyWith(program: value));
    });
  }
}

/// Adds pattern-matching-related methods to [MyProgram].
extension MyProgramPatterns on MyProgram {
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
    TResult Function(_MyProgram value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MyProgram() when $default != null:
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
    TResult Function(_MyProgram value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MyProgram():
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
    TResult? Function(_MyProgram value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MyProgram() when $default != null:
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
      ProgramAssignment assignment,
      Program program,
      String? unlockedThrough,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MyProgram() when $default != null:
        return $default(_that.assignment, _that.program, _that.unlockedThrough);
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
      ProgramAssignment assignment,
      Program program,
      String? unlockedThrough,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MyProgram():
        return $default(_that.assignment, _that.program, _that.unlockedThrough);
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
      ProgramAssignment assignment,
      Program program,
      String? unlockedThrough,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MyProgram() when $default != null:
        return $default(_that.assignment, _that.program, _that.unlockedThrough);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _MyProgram implements MyProgram {
  const _MyProgram({
    required this.assignment,
    required this.program,
    this.unlockedThrough,
  });
  factory _MyProgram.fromJson(Map<String, dynamic> json) =>
      _$MyProgramFromJson(json);

  @override
  final ProgramAssignment assignment;
  @override
  final Program program;
  @override
  final String? unlockedThrough;

  /// Create a copy of MyProgram
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$MyProgramCopyWith<_MyProgram> get copyWith =>
      __$MyProgramCopyWithImpl<_MyProgram>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$MyProgramToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _MyProgram &&
            (identical(other.assignment, assignment) ||
                other.assignment == assignment) &&
            (identical(other.program, program) || other.program == program) &&
            (identical(other.unlockedThrough, unlockedThrough) ||
                other.unlockedThrough == unlockedThrough));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, assignment, program, unlockedThrough);
  }

  @override
  String toString() {
    return 'MyProgram(assignment: $assignment, program: $program, unlockedThrough: $unlockedThrough)';
  }
}

/// @nodoc
abstract mixin class _$MyProgramCopyWith<$Res>
    implements $MyProgramCopyWith<$Res> {
  factory _$MyProgramCopyWith(
    _MyProgram value,
    $Res Function(_MyProgram) _then,
  ) = __$MyProgramCopyWithImpl;
  @override
  @useResult
  $Res call({
    ProgramAssignment assignment,
    Program program,
    String? unlockedThrough,
  });

  @override
  $ProgramAssignmentCopyWith<$Res> get assignment;
  @override
  $ProgramCopyWith<$Res> get program;
}

/// @nodoc
class __$MyProgramCopyWithImpl<$Res> implements _$MyProgramCopyWith<$Res> {
  __$MyProgramCopyWithImpl(this._self, this._then);

  final _MyProgram _self;
  final $Res Function(_MyProgram) _then;

  /// Create a copy of MyProgram
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? assignment = null,
    Object? program = null,
    Object? unlockedThrough = freezed,
  }) {
    return _then(
      _MyProgram(
        assignment: null == assignment
            ? _self.assignment
            : assignment // ignore: cast_nullable_to_non_nullable
                  as ProgramAssignment,
        program: null == program
            ? _self.program
            : program // ignore: cast_nullable_to_non_nullable
                  as Program,
        unlockedThrough: freezed == unlockedThrough
            ? _self.unlockedThrough
            : unlockedThrough // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }

  /// Create a copy of MyProgram
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ProgramAssignmentCopyWith<$Res> get assignment {
    return $ProgramAssignmentCopyWith<$Res>(_self.assignment, (value) {
      return _then(_self.copyWith(assignment: value));
    });
  }

  /// Create a copy of MyProgram
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ProgramCopyWith<$Res> get program {
    return $ProgramCopyWith<$Res>(_self.program, (value) {
      return _then(_self.copyWith(program: value));
    });
  }
}
