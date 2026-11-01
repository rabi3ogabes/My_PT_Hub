// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'checkins.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$CheckinSummary {
  String get id;
  String? get formName;
  String get dueOn;
  String get status;
  DateTime? get submittedAt;
  DateTime? get reviewedAt;

  /// Create a copy of CheckinSummary
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $CheckinSummaryCopyWith<CheckinSummary> get copyWith =>
      _$CheckinSummaryCopyWithImpl<CheckinSummary>(
        this as CheckinSummary,
        _$identity,
      );

  /// Serializes this CheckinSummary to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as CheckinSummary;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is CheckinSummary &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.formName, _this.formName) ||
                other.formName == _this.formName) &&
            (identical(other.dueOn, _this.dueOn) ||
                other.dueOn == _this.dueOn) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status) &&
            (identical(other.submittedAt, _this.submittedAt) ||
                other.submittedAt == _this.submittedAt) &&
            (identical(other.reviewedAt, _this.reviewedAt) ||
                other.reviewedAt == _this.reviewedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as CheckinSummary;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.formName,
      _this.dueOn,
      _this.status,
      _this.submittedAt,
      _this.reviewedAt,
    );
  }

  @override
  String toString() {
    final _this = this as CheckinSummary;
    return 'CheckinSummary(id: ${_this.id}, formName: ${_this.formName}, dueOn: ${_this.dueOn}, status: ${_this.status}, submittedAt: ${_this.submittedAt}, reviewedAt: ${_this.reviewedAt})';
  }
}

/// @nodoc
abstract mixin class $CheckinSummaryCopyWith<$Res> {
  factory $CheckinSummaryCopyWith(
    CheckinSummary value,
    $Res Function(CheckinSummary) _then,
  ) = _$CheckinSummaryCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String? formName,
    String dueOn,
    String status,
    DateTime? submittedAt,
    DateTime? reviewedAt,
  });
}

/// @nodoc
class _$CheckinSummaryCopyWithImpl<$Res>
    implements $CheckinSummaryCopyWith<$Res> {
  _$CheckinSummaryCopyWithImpl(this._self, this._then);

  final CheckinSummary _self;
  final $Res Function(CheckinSummary) _then;

  /// Create a copy of CheckinSummary
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? formName = freezed,
    Object? dueOn = null,
    Object? status = null,
    Object? submittedAt = freezed,
    Object? reviewedAt = freezed,
  }) {
    return _then(
      CheckinSummary(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        formName: freezed == formName
            ? _self.formName
            : formName // ignore: cast_nullable_to_non_nullable
                  as String?,
        dueOn: null == dueOn
            ? _self.dueOn
            : dueOn // ignore: cast_nullable_to_non_nullable
                  as String,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        submittedAt: freezed == submittedAt
            ? _self.submittedAt
            : submittedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        reviewedAt: freezed == reviewedAt
            ? _self.reviewedAt
            : reviewedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [CheckinSummary].
extension CheckinSummaryPatterns on CheckinSummary {
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
    TResult Function(_CheckinSummary value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinSummary() when $default != null:
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
    TResult Function(_CheckinSummary value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinSummary():
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
    TResult? Function(_CheckinSummary value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinSummary() when $default != null:
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
      String? formName,
      String dueOn,
      String status,
      DateTime? submittedAt,
      DateTime? reviewedAt,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinSummary() when $default != null:
        return $default(
          _that.id,
          _that.formName,
          _that.dueOn,
          _that.status,
          _that.submittedAt,
          _that.reviewedAt,
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
      String? formName,
      String dueOn,
      String status,
      DateTime? submittedAt,
      DateTime? reviewedAt,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinSummary():
        return $default(
          _that.id,
          _that.formName,
          _that.dueOn,
          _that.status,
          _that.submittedAt,
          _that.reviewedAt,
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
      String? formName,
      String dueOn,
      String status,
      DateTime? submittedAt,
      DateTime? reviewedAt,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinSummary() when $default != null:
        return $default(
          _that.id,
          _that.formName,
          _that.dueOn,
          _that.status,
          _that.submittedAt,
          _that.reviewedAt,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _CheckinSummary implements CheckinSummary {
  const _CheckinSummary({
    required this.id,
    this.formName,
    required this.dueOn,
    this.status = 'pending',
    this.submittedAt,
    this.reviewedAt,
  });
  factory _CheckinSummary.fromJson(Map<String, dynamic> json) =>
      _$CheckinSummaryFromJson(json);

  @override
  final String id;
  @override
  final String? formName;
  @override
  final String dueOn;
  @override
  @JsonKey()
  final String status;
  @override
  final DateTime? submittedAt;
  @override
  final DateTime? reviewedAt;

  /// Create a copy of CheckinSummary
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$CheckinSummaryCopyWith<_CheckinSummary> get copyWith =>
      __$CheckinSummaryCopyWithImpl<_CheckinSummary>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$CheckinSummaryToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _CheckinSummary &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.formName, formName) ||
                other.formName == formName) &&
            (identical(other.dueOn, dueOn) || other.dueOn == dueOn) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.submittedAt, submittedAt) ||
                other.submittedAt == submittedAt) &&
            (identical(other.reviewedAt, reviewedAt) ||
                other.reviewedAt == reviewedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      formName,
      dueOn,
      status,
      submittedAt,
      reviewedAt,
    );
  }

  @override
  String toString() {
    return 'CheckinSummary(id: $id, formName: $formName, dueOn: $dueOn, status: $status, submittedAt: $submittedAt, reviewedAt: $reviewedAt)';
  }
}

/// @nodoc
abstract mixin class _$CheckinSummaryCopyWith<$Res>
    implements $CheckinSummaryCopyWith<$Res> {
  factory _$CheckinSummaryCopyWith(
    _CheckinSummary value,
    $Res Function(_CheckinSummary) _then,
  ) = __$CheckinSummaryCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String? formName,
    String dueOn,
    String status,
    DateTime? submittedAt,
    DateTime? reviewedAt,
  });
}

/// @nodoc
class __$CheckinSummaryCopyWithImpl<$Res>
    implements _$CheckinSummaryCopyWith<$Res> {
  __$CheckinSummaryCopyWithImpl(this._self, this._then);

  final _CheckinSummary _self;
  final $Res Function(_CheckinSummary) _then;

  /// Create a copy of CheckinSummary
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? formName = freezed,
    Object? dueOn = null,
    Object? status = null,
    Object? submittedAt = freezed,
    Object? reviewedAt = freezed,
  }) {
    return _then(
      _CheckinSummary(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        formName: freezed == formName
            ? _self.formName
            : formName // ignore: cast_nullable_to_non_nullable
                  as String?,
        dueOn: null == dueOn
            ? _self.dueOn
            : dueOn // ignore: cast_nullable_to_non_nullable
                  as String,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        submittedAt: freezed == submittedAt
            ? _self.submittedAt
            : submittedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
        reviewedAt: freezed == reviewedAt
            ? _self.reviewedAt
            : reviewedAt // ignore: cast_nullable_to_non_nullable
                  as DateTime?,
      ),
    );
  }
}

/// @nodoc
mixin _$CheckinQuestion {
  String get id;
  String get type;
  String get label;
  @JsonKey(name: 'required')
  bool get isRequired;
  List<String>? get options;
  double? get min;
  double? get max;
  String? get unit;

  /// Create a copy of CheckinQuestion
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $CheckinQuestionCopyWith<CheckinQuestion> get copyWith =>
      _$CheckinQuestionCopyWithImpl<CheckinQuestion>(
        this as CheckinQuestion,
        _$identity,
      );

  /// Serializes this CheckinQuestion to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as CheckinQuestion;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is CheckinQuestion &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.type, _this.type) || other.type == _this.type) &&
            (identical(other.label, _this.label) ||
                other.label == _this.label) &&
            (identical(other.isRequired, _this.isRequired) ||
                other.isRequired == _this.isRequired) &&
            const DeepCollectionEquality().equals(
              other.options,
              _this.options,
            ) &&
            (identical(other.min, _this.min) || other.min == _this.min) &&
            (identical(other.max, _this.max) || other.max == _this.max) &&
            (identical(other.unit, _this.unit) || other.unit == _this.unit));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as CheckinQuestion;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.type,
      _this.label,
      _this.isRequired,
      const DeepCollectionEquality().hash(_this.options),
      _this.min,
      _this.max,
      _this.unit,
    );
  }

  @override
  String toString() {
    final _this = this as CheckinQuestion;
    return 'CheckinQuestion(id: ${_this.id}, type: ${_this.type}, label: ${_this.label}, isRequired: ${_this.isRequired}, options: ${_this.options}, min: ${_this.min}, max: ${_this.max}, unit: ${_this.unit})';
  }
}

/// @nodoc
abstract mixin class $CheckinQuestionCopyWith<$Res> {
  factory $CheckinQuestionCopyWith(
    CheckinQuestion value,
    $Res Function(CheckinQuestion) _then,
  ) = _$CheckinQuestionCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String type,
    String label,
    @JsonKey(name: 'required') bool isRequired,
    List<String>? options,
    double? min,
    double? max,
    String? unit,
  });
}

/// @nodoc
class _$CheckinQuestionCopyWithImpl<$Res>
    implements $CheckinQuestionCopyWith<$Res> {
  _$CheckinQuestionCopyWithImpl(this._self, this._then);

  final CheckinQuestion _self;
  final $Res Function(CheckinQuestion) _then;

  /// Create a copy of CheckinQuestion
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? label = null,
    Object? isRequired = null,
    Object? options = freezed,
    Object? min = freezed,
    Object? max = freezed,
    Object? unit = freezed,
  }) {
    return _then(
      CheckinQuestion(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        label: null == label
            ? _self.label
            : label // ignore: cast_nullable_to_non_nullable
                  as String,
        isRequired: null == isRequired
            ? _self.isRequired
            : isRequired // ignore: cast_nullable_to_non_nullable
                  as bool,
        options: freezed == options
            ? _self.options
            : options // ignore: cast_nullable_to_non_nullable
                  as List<String>?,
        min: freezed == min
            ? _self.min
            : min // ignore: cast_nullable_to_non_nullable
                  as double?,
        max: freezed == max
            ? _self.max
            : max // ignore: cast_nullable_to_non_nullable
                  as double?,
        unit: freezed == unit
            ? _self.unit
            : unit // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [CheckinQuestion].
extension CheckinQuestionPatterns on CheckinQuestion {
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
    TResult Function(_CheckinQuestion value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinQuestion() when $default != null:
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
    TResult Function(_CheckinQuestion value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinQuestion():
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
    TResult? Function(_CheckinQuestion value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinQuestion() when $default != null:
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
      String label,
      @JsonKey(name: 'required') bool isRequired,
      List<String>? options,
      double? min,
      double? max,
      String? unit,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinQuestion() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.label,
          _that.isRequired,
          _that.options,
          _that.min,
          _that.max,
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
      String type,
      String label,
      @JsonKey(name: 'required') bool isRequired,
      List<String>? options,
      double? min,
      double? max,
      String? unit,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinQuestion():
        return $default(
          _that.id,
          _that.type,
          _that.label,
          _that.isRequired,
          _that.options,
          _that.min,
          _that.max,
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
      String type,
      String label,
      @JsonKey(name: 'required') bool isRequired,
      List<String>? options,
      double? min,
      double? max,
      String? unit,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinQuestion() when $default != null:
        return $default(
          _that.id,
          _that.type,
          _that.label,
          _that.isRequired,
          _that.options,
          _that.min,
          _that.max,
          _that.unit,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDoubleN()
class _CheckinQuestion implements CheckinQuestion {
  const _CheckinQuestion({
    required this.id,
    required this.type,
    required this.label,
    @JsonKey(name: 'required') this.isRequired = false,
    List<String>? options,
    this.min,
    this.max,
    this.unit,
  }) : _options = options;
  factory _CheckinQuestion.fromJson(Map<String, dynamic> json) =>
      _$CheckinQuestionFromJson(json);

  @override
  final String id;
  @override
  final String type;
  @override
  final String label;
  @override
  @JsonKey(name: 'required')
  final bool isRequired;
  final List<String>? _options;
  @override
  List<String>? get options {
    final value = _options;
    if (value == null) return null;
    if (_options is EqualUnmodifiableListView) return _options;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(value);
  }

  @override
  final double? min;
  @override
  final double? max;
  @override
  final String? unit;

  /// Create a copy of CheckinQuestion
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$CheckinQuestionCopyWith<_CheckinQuestion> get copyWith =>
      __$CheckinQuestionCopyWithImpl<_CheckinQuestion>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$CheckinQuestionToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _CheckinQuestion &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.label, label) || other.label == label) &&
            (identical(other.isRequired, isRequired) ||
                other.isRequired == isRequired) &&
            const DeepCollectionEquality().equals(other.options, _options) &&
            (identical(other.min, min) || other.min == min) &&
            (identical(other.max, max) || other.max == max) &&
            (identical(other.unit, unit) || other.unit == unit));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      type,
      label,
      isRequired,
      const DeepCollectionEquality().hash(_options),
      min,
      max,
      unit,
    );
  }

  @override
  String toString() {
    return 'CheckinQuestion(id: $id, type: $type, label: $label, isRequired: $isRequired, options: $options, min: $min, max: $max, unit: $unit)';
  }
}

/// @nodoc
abstract mixin class _$CheckinQuestionCopyWith<$Res>
    implements $CheckinQuestionCopyWith<$Res> {
  factory _$CheckinQuestionCopyWith(
    _CheckinQuestion value,
    $Res Function(_CheckinQuestion) _then,
  ) = __$CheckinQuestionCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String type,
    String label,
    @JsonKey(name: 'required') bool isRequired,
    List<String>? options,
    double? min,
    double? max,
    String? unit,
  });
}

/// @nodoc
class __$CheckinQuestionCopyWithImpl<$Res>
    implements _$CheckinQuestionCopyWith<$Res> {
  __$CheckinQuestionCopyWithImpl(this._self, this._then);

  final _CheckinQuestion _self;
  final $Res Function(_CheckinQuestion) _then;

  /// Create a copy of CheckinQuestion
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? type = null,
    Object? label = null,
    Object? isRequired = null,
    Object? options = freezed,
    Object? min = freezed,
    Object? max = freezed,
    Object? unit = freezed,
  }) {
    return _then(
      _CheckinQuestion(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        type: null == type
            ? _self.type
            : type // ignore: cast_nullable_to_non_nullable
                  as String,
        label: null == label
            ? _self.label
            : label // ignore: cast_nullable_to_non_nullable
                  as String,
        isRequired: null == isRequired
            ? _self.isRequired
            : isRequired // ignore: cast_nullable_to_non_nullable
                  as bool,
        options: freezed == options
            ? _self._options
            : options // ignore: cast_nullable_to_non_nullable
                  as List<String>?,
        min: freezed == min
            ? _self.min
            : min // ignore: cast_nullable_to_non_nullable
                  as double?,
        max: freezed == max
            ? _self.max
            : max // ignore: cast_nullable_to_non_nullable
                  as double?,
        unit: freezed == unit
            ? _self.unit
            : unit // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$CheckinForm {
  String get id;
  String get name;
  String? get intro;
  List<CheckinQuestion> get questions;
  bool get includeMetrics;
  bool get includePhotos;

  /// Create a copy of CheckinForm
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $CheckinFormCopyWith<CheckinForm> get copyWith =>
      _$CheckinFormCopyWithImpl<CheckinForm>(this as CheckinForm, _$identity);

  /// Serializes this CheckinForm to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as CheckinForm;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is CheckinForm &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.name, _this.name) || other.name == _this.name) &&
            (identical(other.intro, _this.intro) ||
                other.intro == _this.intro) &&
            const DeepCollectionEquality().equals(
              other.questions,
              _this.questions,
            ) &&
            (identical(other.includeMetrics, _this.includeMetrics) ||
                other.includeMetrics == _this.includeMetrics) &&
            (identical(other.includePhotos, _this.includePhotos) ||
                other.includePhotos == _this.includePhotos));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as CheckinForm;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.name,
      _this.intro,
      const DeepCollectionEquality().hash(_this.questions),
      _this.includeMetrics,
      _this.includePhotos,
    );
  }

  @override
  String toString() {
    final _this = this as CheckinForm;
    return 'CheckinForm(id: ${_this.id}, name: ${_this.name}, intro: ${_this.intro}, questions: ${_this.questions}, includeMetrics: ${_this.includeMetrics}, includePhotos: ${_this.includePhotos})';
  }
}

/// @nodoc
abstract mixin class $CheckinFormCopyWith<$Res> {
  factory $CheckinFormCopyWith(
    CheckinForm value,
    $Res Function(CheckinForm) _then,
  ) = _$CheckinFormCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String name,
    String? intro,
    List<CheckinQuestion> questions,
    bool includeMetrics,
    bool includePhotos,
  });
}

/// @nodoc
class _$CheckinFormCopyWithImpl<$Res> implements $CheckinFormCopyWith<$Res> {
  _$CheckinFormCopyWithImpl(this._self, this._then);

  final CheckinForm _self;
  final $Res Function(CheckinForm) _then;

  /// Create a copy of CheckinForm
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? intro = freezed,
    Object? questions = null,
    Object? includeMetrics = null,
    Object? includePhotos = null,
  }) {
    return _then(
      CheckinForm(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        intro: freezed == intro
            ? _self.intro
            : intro // ignore: cast_nullable_to_non_nullable
                  as String?,
        questions: null == questions
            ? _self.questions
            : questions // ignore: cast_nullable_to_non_nullable
                  as List<CheckinQuestion>,
        includeMetrics: null == includeMetrics
            ? _self.includeMetrics
            : includeMetrics // ignore: cast_nullable_to_non_nullable
                  as bool,
        includePhotos: null == includePhotos
            ? _self.includePhotos
            : includePhotos // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [CheckinForm].
extension CheckinFormPatterns on CheckinForm {
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
    TResult Function(_CheckinForm value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinForm() when $default != null:
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
    TResult Function(_CheckinForm value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinForm():
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
    TResult? Function(_CheckinForm value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinForm() when $default != null:
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
      String? intro,
      List<CheckinQuestion> questions,
      bool includeMetrics,
      bool includePhotos,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinForm() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.intro,
          _that.questions,
          _that.includeMetrics,
          _that.includePhotos,
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
      String? intro,
      List<CheckinQuestion> questions,
      bool includeMetrics,
      bool includePhotos,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinForm():
        return $default(
          _that.id,
          _that.name,
          _that.intro,
          _that.questions,
          _that.includeMetrics,
          _that.includePhotos,
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
      String? intro,
      List<CheckinQuestion> questions,
      bool includeMetrics,
      bool includePhotos,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinForm() when $default != null:
        return $default(
          _that.id,
          _that.name,
          _that.intro,
          _that.questions,
          _that.includeMetrics,
          _that.includePhotos,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _CheckinForm implements CheckinForm {
  const _CheckinForm({
    required this.id,
    required this.name,
    this.intro,
    List<CheckinQuestion> questions = const <CheckinQuestion>[],
    this.includeMetrics = true,
    this.includePhotos = true,
  }) : _questions = questions;
  factory _CheckinForm.fromJson(Map<String, dynamic> json) =>
      _$CheckinFormFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  final String? intro;
  final List<CheckinQuestion> _questions;
  @override
  @JsonKey()
  List<CheckinQuestion> get questions {
    if (_questions is EqualUnmodifiableListView) return _questions;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_questions);
  }

  @override
  @JsonKey()
  final bool includeMetrics;
  @override
  @JsonKey()
  final bool includePhotos;

  /// Create a copy of CheckinForm
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$CheckinFormCopyWith<_CheckinForm> get copyWith =>
      __$CheckinFormCopyWithImpl<_CheckinForm>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$CheckinFormToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _CheckinForm &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.intro, intro) || other.intro == intro) &&
            const DeepCollectionEquality().equals(
              other.questions,
              _questions,
            ) &&
            (identical(other.includeMetrics, includeMetrics) ||
                other.includeMetrics == includeMetrics) &&
            (identical(other.includePhotos, includePhotos) ||
                other.includePhotos == includePhotos));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      name,
      intro,
      const DeepCollectionEquality().hash(_questions),
      includeMetrics,
      includePhotos,
    );
  }

  @override
  String toString() {
    return 'CheckinForm(id: $id, name: $name, intro: $intro, questions: $questions, includeMetrics: $includeMetrics, includePhotos: $includePhotos)';
  }
}

/// @nodoc
abstract mixin class _$CheckinFormCopyWith<$Res>
    implements $CheckinFormCopyWith<$Res> {
  factory _$CheckinFormCopyWith(
    _CheckinForm value,
    $Res Function(_CheckinForm) _then,
  ) = __$CheckinFormCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String name,
    String? intro,
    List<CheckinQuestion> questions,
    bool includeMetrics,
    bool includePhotos,
  });
}

/// @nodoc
class __$CheckinFormCopyWithImpl<$Res> implements _$CheckinFormCopyWith<$Res> {
  __$CheckinFormCopyWithImpl(this._self, this._then);

  final _CheckinForm _self;
  final $Res Function(_CheckinForm) _then;

  /// Create a copy of CheckinForm
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? intro = freezed,
    Object? questions = null,
    Object? includeMetrics = null,
    Object? includePhotos = null,
  }) {
    return _then(
      _CheckinForm(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        name: null == name
            ? _self.name
            : name // ignore: cast_nullable_to_non_nullable
                  as String,
        intro: freezed == intro
            ? _self.intro
            : intro // ignore: cast_nullable_to_non_nullable
                  as String?,
        questions: null == questions
            ? _self._questions
            : questions // ignore: cast_nullable_to_non_nullable
                  as List<CheckinQuestion>,
        includeMetrics: null == includeMetrics
            ? _self.includeMetrics
            : includeMetrics // ignore: cast_nullable_to_non_nullable
                  as bool,
        includePhotos: null == includePhotos
            ? _self.includePhotos
            : includePhotos // ignore: cast_nullable_to_non_nullable
                  as bool,
      ),
    );
  }
}

/// @nodoc
mixin _$CheckinComment {
  String get id;
  IdName get user;
  String get body;
  DateTime? get createdAt;

  /// Create a copy of CheckinComment
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $CheckinCommentCopyWith<CheckinComment> get copyWith =>
      _$CheckinCommentCopyWithImpl<CheckinComment>(
        this as CheckinComment,
        _$identity,
      );

  /// Serializes this CheckinComment to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as CheckinComment;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is CheckinComment &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.user, _this.user) || other.user == _this.user) &&
            (identical(other.body, _this.body) || other.body == _this.body) &&
            (identical(other.createdAt, _this.createdAt) ||
                other.createdAt == _this.createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as CheckinComment;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.user,
      _this.body,
      _this.createdAt,
    );
  }

  @override
  String toString() {
    final _this = this as CheckinComment;
    return 'CheckinComment(id: ${_this.id}, user: ${_this.user}, body: ${_this.body}, createdAt: ${_this.createdAt})';
  }
}

/// @nodoc
abstract mixin class $CheckinCommentCopyWith<$Res> {
  factory $CheckinCommentCopyWith(
    CheckinComment value,
    $Res Function(CheckinComment) _then,
  ) = _$CheckinCommentCopyWithImpl;
  @useResult
  $Res call({String id, IdName user, String body, DateTime? createdAt});

  $IdNameCopyWith<$Res> get user;
}

/// @nodoc
class _$CheckinCommentCopyWithImpl<$Res>
    implements $CheckinCommentCopyWith<$Res> {
  _$CheckinCommentCopyWithImpl(this._self, this._then);

  final CheckinComment _self;
  final $Res Function(CheckinComment) _then;

  /// Create a copy of CheckinComment
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? user = null,
    Object? body = null,
    Object? createdAt = freezed,
  }) {
    return _then(
      CheckinComment(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        user: null == user
            ? _self.user
            : user // ignore: cast_nullable_to_non_nullable
                  as IdName,
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

  /// Create a copy of CheckinComment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $IdNameCopyWith<$Res> get user {
    return $IdNameCopyWith<$Res>(_self.user, (value) {
      return _then(_self.copyWith(user: value));
    });
  }
}

/// Adds pattern-matching-related methods to [CheckinComment].
extension CheckinCommentPatterns on CheckinComment {
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
    TResult Function(_CheckinComment value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinComment() when $default != null:
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
    TResult Function(_CheckinComment value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinComment():
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
    TResult? Function(_CheckinComment value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinComment() when $default != null:
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
    TResult Function(String id, IdName user, String body, DateTime? createdAt)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinComment() when $default != null:
        return $default(_that.id, _that.user, _that.body, _that.createdAt);
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
    TResult Function(String id, IdName user, String body, DateTime? createdAt)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinComment():
        return $default(_that.id, _that.user, _that.body, _that.createdAt);
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
    TResult? Function(String id, IdName user, String body, DateTime? createdAt)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinComment() when $default != null:
        return $default(_that.id, _that.user, _that.body, _that.createdAt);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _CheckinComment implements CheckinComment {
  const _CheckinComment({
    required this.id,
    required this.user,
    required this.body,
    this.createdAt,
  });
  factory _CheckinComment.fromJson(Map<String, dynamic> json) =>
      _$CheckinCommentFromJson(json);

  @override
  final String id;
  @override
  final IdName user;
  @override
  final String body;
  @override
  final DateTime? createdAt;

  /// Create a copy of CheckinComment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$CheckinCommentCopyWith<_CheckinComment> get copyWith =>
      __$CheckinCommentCopyWithImpl<_CheckinComment>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$CheckinCommentToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _CheckinComment &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.user, user) || other.user == user) &&
            (identical(other.body, body) || other.body == body) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, user, body, createdAt);
  }

  @override
  String toString() {
    return 'CheckinComment(id: $id, user: $user, body: $body, createdAt: $createdAt)';
  }
}

/// @nodoc
abstract mixin class _$CheckinCommentCopyWith<$Res>
    implements $CheckinCommentCopyWith<$Res> {
  factory _$CheckinCommentCopyWith(
    _CheckinComment value,
    $Res Function(_CheckinComment) _then,
  ) = __$CheckinCommentCopyWithImpl;
  @override
  @useResult
  $Res call({String id, IdName user, String body, DateTime? createdAt});

  @override
  $IdNameCopyWith<$Res> get user;
}

/// @nodoc
class __$CheckinCommentCopyWithImpl<$Res>
    implements _$CheckinCommentCopyWith<$Res> {
  __$CheckinCommentCopyWithImpl(this._self, this._then);

  final _CheckinComment _self;
  final $Res Function(_CheckinComment) _then;

  /// Create a copy of CheckinComment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? user = null,
    Object? body = null,
    Object? createdAt = freezed,
  }) {
    return _then(
      _CheckinComment(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        user: null == user
            ? _self.user
            : user // ignore: cast_nullable_to_non_nullable
                  as IdName,
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

  /// Create a copy of CheckinComment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $IdNameCopyWith<$Res> get user {
    return $IdNameCopyWith<$Res>(_self.user, (value) {
      return _then(_self.copyWith(user: value));
    });
  }
}

/// @nodoc
mixin _$CheckinDetail {
  String get id;
  String get status;
  String get dueOn;
  CheckinForm get form;
  Map<String, dynamic> get answers;
  String? get coachFeedback;
  List<CheckinComment> get comments;

  /// Create a copy of CheckinDetail
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $CheckinDetailCopyWith<CheckinDetail> get copyWith =>
      _$CheckinDetailCopyWithImpl<CheckinDetail>(
        this as CheckinDetail,
        _$identity,
      );

  /// Serializes this CheckinDetail to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as CheckinDetail;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is CheckinDetail &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.status, _this.status) ||
                other.status == _this.status) &&
            (identical(other.dueOn, _this.dueOn) ||
                other.dueOn == _this.dueOn) &&
            (identical(other.form, _this.form) || other.form == _this.form) &&
            const DeepCollectionEquality().equals(
              other.answers,
              _this.answers,
            ) &&
            (identical(other.coachFeedback, _this.coachFeedback) ||
                other.coachFeedback == _this.coachFeedback) &&
            const DeepCollectionEquality().equals(
              other.comments,
              _this.comments,
            ));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as CheckinDetail;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.status,
      _this.dueOn,
      _this.form,
      const DeepCollectionEquality().hash(_this.answers),
      _this.coachFeedback,
      const DeepCollectionEquality().hash(_this.comments),
    );
  }

  @override
  String toString() {
    final _this = this as CheckinDetail;
    return 'CheckinDetail(id: ${_this.id}, status: ${_this.status}, dueOn: ${_this.dueOn}, form: ${_this.form}, answers: ${_this.answers}, coachFeedback: ${_this.coachFeedback}, comments: ${_this.comments})';
  }
}

/// @nodoc
abstract mixin class $CheckinDetailCopyWith<$Res> {
  factory $CheckinDetailCopyWith(
    CheckinDetail value,
    $Res Function(CheckinDetail) _then,
  ) = _$CheckinDetailCopyWithImpl;
  @useResult
  $Res call({
    String id,
    String status,
    String dueOn,
    CheckinForm form,
    Map<String, dynamic> answers,
    String? coachFeedback,
    List<CheckinComment> comments,
  });

  $CheckinFormCopyWith<$Res> get form;
}

/// @nodoc
class _$CheckinDetailCopyWithImpl<$Res>
    implements $CheckinDetailCopyWith<$Res> {
  _$CheckinDetailCopyWithImpl(this._self, this._then);

  final CheckinDetail _self;
  final $Res Function(CheckinDetail) _then;

  /// Create a copy of CheckinDetail
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? status = null,
    Object? dueOn = null,
    Object? form = null,
    Object? answers = null,
    Object? coachFeedback = freezed,
    Object? comments = null,
  }) {
    return _then(
      CheckinDetail(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        dueOn: null == dueOn
            ? _self.dueOn
            : dueOn // ignore: cast_nullable_to_non_nullable
                  as String,
        form: null == form
            ? _self.form
            : form // ignore: cast_nullable_to_non_nullable
                  as CheckinForm,
        answers: null == answers
            ? _self.answers
            : answers // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>,
        coachFeedback: freezed == coachFeedback
            ? _self.coachFeedback
            : coachFeedback // ignore: cast_nullable_to_non_nullable
                  as String?,
        comments: null == comments
            ? _self.comments
            : comments // ignore: cast_nullable_to_non_nullable
                  as List<CheckinComment>,
      ),
    );
  }

  /// Create a copy of CheckinDetail
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $CheckinFormCopyWith<$Res> get form {
    return $CheckinFormCopyWith<$Res>(_self.form, (value) {
      return _then(_self.copyWith(form: value));
    });
  }
}

/// Adds pattern-matching-related methods to [CheckinDetail].
extension CheckinDetailPatterns on CheckinDetail {
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
    TResult Function(_CheckinDetail value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinDetail() when $default != null:
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
    TResult Function(_CheckinDetail value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinDetail():
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
    TResult? Function(_CheckinDetail value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinDetail() when $default != null:
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
      String status,
      String dueOn,
      CheckinForm form,
      Map<String, dynamic> answers,
      String? coachFeedback,
      List<CheckinComment> comments,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _CheckinDetail() when $default != null:
        return $default(
          _that.id,
          _that.status,
          _that.dueOn,
          _that.form,
          _that.answers,
          _that.coachFeedback,
          _that.comments,
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
      String status,
      String dueOn,
      CheckinForm form,
      Map<String, dynamic> answers,
      String? coachFeedback,
      List<CheckinComment> comments,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinDetail():
        return $default(
          _that.id,
          _that.status,
          _that.dueOn,
          _that.form,
          _that.answers,
          _that.coachFeedback,
          _that.comments,
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
      String status,
      String dueOn,
      CheckinForm form,
      Map<String, dynamic> answers,
      String? coachFeedback,
      List<CheckinComment> comments,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _CheckinDetail() when $default != null:
        return $default(
          _that.id,
          _that.status,
          _that.dueOn,
          _that.form,
          _that.answers,
          _that.coachFeedback,
          _that.comments,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _CheckinDetail implements CheckinDetail {
  const _CheckinDetail({
    required this.id,
    this.status = 'pending',
    required this.dueOn,
    required this.form,
    Map<String, dynamic> answers = const <String, dynamic>{},
    this.coachFeedback,
    List<CheckinComment> comments = const <CheckinComment>[],
  }) : _answers = answers,
       _comments = comments;
  factory _CheckinDetail.fromJson(Map<String, dynamic> json) =>
      _$CheckinDetailFromJson(json);

  @override
  final String id;
  @override
  @JsonKey()
  final String status;
  @override
  final String dueOn;
  @override
  final CheckinForm form;
  final Map<String, dynamic> _answers;
  @override
  @JsonKey()
  Map<String, dynamic> get answers {
    if (_answers is EqualUnmodifiableMapView) return _answers;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(_answers);
  }

  @override
  final String? coachFeedback;
  final List<CheckinComment> _comments;
  @override
  @JsonKey()
  List<CheckinComment> get comments {
    if (_comments is EqualUnmodifiableListView) return _comments;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_comments);
  }

  /// Create a copy of CheckinDetail
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$CheckinDetailCopyWith<_CheckinDetail> get copyWith =>
      __$CheckinDetailCopyWithImpl<_CheckinDetail>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$CheckinDetailToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _CheckinDetail &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.dueOn, dueOn) || other.dueOn == dueOn) &&
            (identical(other.form, form) || other.form == form) &&
            const DeepCollectionEquality().equals(other.answers, _answers) &&
            (identical(other.coachFeedback, coachFeedback) ||
                other.coachFeedback == coachFeedback) &&
            const DeepCollectionEquality().equals(other.comments, _comments));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      id,
      status,
      dueOn,
      form,
      const DeepCollectionEquality().hash(_answers),
      coachFeedback,
      const DeepCollectionEquality().hash(_comments),
    );
  }

  @override
  String toString() {
    return 'CheckinDetail(id: $id, status: $status, dueOn: $dueOn, form: $form, answers: $answers, coachFeedback: $coachFeedback, comments: $comments)';
  }
}

/// @nodoc
abstract mixin class _$CheckinDetailCopyWith<$Res>
    implements $CheckinDetailCopyWith<$Res> {
  factory _$CheckinDetailCopyWith(
    _CheckinDetail value,
    $Res Function(_CheckinDetail) _then,
  ) = __$CheckinDetailCopyWithImpl;
  @override
  @useResult
  $Res call({
    String id,
    String status,
    String dueOn,
    CheckinForm form,
    Map<String, dynamic> answers,
    String? coachFeedback,
    List<CheckinComment> comments,
  });

  @override
  $CheckinFormCopyWith<$Res> get form;
}

/// @nodoc
class __$CheckinDetailCopyWithImpl<$Res>
    implements _$CheckinDetailCopyWith<$Res> {
  __$CheckinDetailCopyWithImpl(this._self, this._then);

  final _CheckinDetail _self;
  final $Res Function(_CheckinDetail) _then;

  /// Create a copy of CheckinDetail
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? status = null,
    Object? dueOn = null,
    Object? form = null,
    Object? answers = null,
    Object? coachFeedback = freezed,
    Object? comments = null,
  }) {
    return _then(
      _CheckinDetail(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        status: null == status
            ? _self.status
            : status // ignore: cast_nullable_to_non_nullable
                  as String,
        dueOn: null == dueOn
            ? _self.dueOn
            : dueOn // ignore: cast_nullable_to_non_nullable
                  as String,
        form: null == form
            ? _self.form
            : form // ignore: cast_nullable_to_non_nullable
                  as CheckinForm,
        answers: null == answers
            ? _self._answers
            : answers // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>,
        coachFeedback: freezed == coachFeedback
            ? _self.coachFeedback
            : coachFeedback // ignore: cast_nullable_to_non_nullable
                  as String?,
        comments: null == comments
            ? _self._comments
            : comments // ignore: cast_nullable_to_non_nullable
                  as List<CheckinComment>,
      ),
    );
  }

  /// Create a copy of CheckinDetail
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $CheckinFormCopyWith<$Res> get form {
    return $CheckinFormCopyWith<$Res>(_self.form, (value) {
      return _then(_self.copyWith(form: value));
    });
  }
}

/// @nodoc
mixin _$ProgressPhoto {
  String get id;
  String get pose;
  String get takenOn;
  String? get url;

  /// Create a copy of ProgressPhoto
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ProgressPhotoCopyWith<ProgressPhoto> get copyWith =>
      _$ProgressPhotoCopyWithImpl<ProgressPhoto>(
        this as ProgressPhoto,
        _$identity,
      );

  /// Serializes this ProgressPhoto to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as ProgressPhoto;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is ProgressPhoto &&
            (identical(other.id, _this.id) || other.id == _this.id) &&
            (identical(other.pose, _this.pose) || other.pose == _this.pose) &&
            (identical(other.takenOn, _this.takenOn) ||
                other.takenOn == _this.takenOn) &&
            (identical(other.url, _this.url) || other.url == _this.url));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as ProgressPhoto;
    return Object.hash(
      runtimeType,
      _this.id,
      _this.pose,
      _this.takenOn,
      _this.url,
    );
  }

  @override
  String toString() {
    final _this = this as ProgressPhoto;
    return 'ProgressPhoto(id: ${_this.id}, pose: ${_this.pose}, takenOn: ${_this.takenOn}, url: ${_this.url})';
  }
}

/// @nodoc
abstract mixin class $ProgressPhotoCopyWith<$Res> {
  factory $ProgressPhotoCopyWith(
    ProgressPhoto value,
    $Res Function(ProgressPhoto) _then,
  ) = _$ProgressPhotoCopyWithImpl;
  @useResult
  $Res call({String id, String pose, String takenOn, String? url});
}

/// @nodoc
class _$ProgressPhotoCopyWithImpl<$Res>
    implements $ProgressPhotoCopyWith<$Res> {
  _$ProgressPhotoCopyWithImpl(this._self, this._then);

  final ProgressPhoto _self;
  final $Res Function(ProgressPhoto) _then;

  /// Create a copy of ProgressPhoto
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? pose = null,
    Object? takenOn = null,
    Object? url = freezed,
  }) {
    return _then(
      ProgressPhoto(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        pose: null == pose
            ? _self.pose
            : pose // ignore: cast_nullable_to_non_nullable
                  as String,
        takenOn: null == takenOn
            ? _self.takenOn
            : takenOn // ignore: cast_nullable_to_non_nullable
                  as String,
        url: freezed == url
            ? _self.url
            : url // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [ProgressPhoto].
extension ProgressPhotoPatterns on ProgressPhoto {
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
    TResult Function(_ProgressPhoto value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ProgressPhoto() when $default != null:
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
    TResult Function(_ProgressPhoto value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgressPhoto():
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
    TResult? Function(_ProgressPhoto value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgressPhoto() when $default != null:
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
    TResult Function(String id, String pose, String takenOn, String? url)?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ProgressPhoto() when $default != null:
        return $default(_that.id, _that.pose, _that.takenOn, _that.url);
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
    TResult Function(String id, String pose, String takenOn, String? url)
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgressPhoto():
        return $default(_that.id, _that.pose, _that.takenOn, _that.url);
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
    TResult? Function(String id, String pose, String takenOn, String? url)?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgressPhoto() when $default != null:
        return $default(_that.id, _that.pose, _that.takenOn, _that.url);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _ProgressPhoto implements ProgressPhoto {
  const _ProgressPhoto({
    required this.id,
    required this.pose,
    required this.takenOn,
    this.url,
  });
  factory _ProgressPhoto.fromJson(Map<String, dynamic> json) =>
      _$ProgressPhotoFromJson(json);

  @override
  final String id;
  @override
  final String pose;
  @override
  final String takenOn;
  @override
  final String? url;

  /// Create a copy of ProgressPhoto
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ProgressPhotoCopyWith<_ProgressPhoto> get copyWith =>
      __$ProgressPhotoCopyWithImpl<_ProgressPhoto>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ProgressPhotoToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _ProgressPhoto &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.pose, pose) || other.pose == pose) &&
            (identical(other.takenOn, takenOn) || other.takenOn == takenOn) &&
            (identical(other.url, url) || other.url == url));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, id, pose, takenOn, url);
  }

  @override
  String toString() {
    return 'ProgressPhoto(id: $id, pose: $pose, takenOn: $takenOn, url: $url)';
  }
}

/// @nodoc
abstract mixin class _$ProgressPhotoCopyWith<$Res>
    implements $ProgressPhotoCopyWith<$Res> {
  factory _$ProgressPhotoCopyWith(
    _ProgressPhoto value,
    $Res Function(_ProgressPhoto) _then,
  ) = __$ProgressPhotoCopyWithImpl;
  @override
  @useResult
  $Res call({String id, String pose, String takenOn, String? url});
}

/// @nodoc
class __$ProgressPhotoCopyWithImpl<$Res>
    implements _$ProgressPhotoCopyWith<$Res> {
  __$ProgressPhotoCopyWithImpl(this._self, this._then);

  final _ProgressPhoto _self;
  final $Res Function(_ProgressPhoto) _then;

  /// Create a copy of ProgressPhoto
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? id = null,
    Object? pose = null,
    Object? takenOn = null,
    Object? url = freezed,
  }) {
    return _then(
      _ProgressPhoto(
        id: null == id
            ? _self.id
            : id // ignore: cast_nullable_to_non_nullable
                  as String,
        pose: null == pose
            ? _self.pose
            : pose // ignore: cast_nullable_to_non_nullable
                  as String,
        takenOn: null == takenOn
            ? _self.takenOn
            : takenOn // ignore: cast_nullable_to_non_nullable
                  as String,
        url: freezed == url
            ? _self.url
            : url // ignore: cast_nullable_to_non_nullable
                  as String?,
      ),
    );
  }
}

/// @nodoc
mixin _$WeightPoint {
  String get date;
  double get weightKg;

  /// Create a copy of WeightPoint
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $WeightPointCopyWith<WeightPoint> get copyWith =>
      _$WeightPointCopyWithImpl<WeightPoint>(this as WeightPoint, _$identity);

  /// Serializes this WeightPoint to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as WeightPoint;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is WeightPoint &&
            (identical(other.date, _this.date) || other.date == _this.date) &&
            (identical(other.weightKg, _this.weightKg) ||
                other.weightKg == _this.weightKg));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as WeightPoint;
    return Object.hash(runtimeType, _this.date, _this.weightKg);
  }

  @override
  String toString() {
    final _this = this as WeightPoint;
    return 'WeightPoint(date: ${_this.date}, weightKg: ${_this.weightKg})';
  }
}

/// @nodoc
abstract mixin class $WeightPointCopyWith<$Res> {
  factory $WeightPointCopyWith(
    WeightPoint value,
    $Res Function(WeightPoint) _then,
  ) = _$WeightPointCopyWithImpl;
  @useResult
  $Res call({String date, double weightKg});
}

/// @nodoc
class _$WeightPointCopyWithImpl<$Res> implements $WeightPointCopyWith<$Res> {
  _$WeightPointCopyWithImpl(this._self, this._then);

  final WeightPoint _self;
  final $Res Function(WeightPoint) _then;

  /// Create a copy of WeightPoint
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? date = null, Object? weightKg = null}) {
    return _then(
      WeightPoint(
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        weightKg: null == weightKg
            ? _self.weightKg
            : weightKg // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [WeightPoint].
extension WeightPointPatterns on WeightPoint {
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
    TResult Function(_WeightPoint value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _WeightPoint() when $default != null:
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
    TResult Function(_WeightPoint value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WeightPoint():
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
    TResult? Function(_WeightPoint value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WeightPoint() when $default != null:
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
    TResult Function(String date, double weightKg)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _WeightPoint() when $default != null:
        return $default(_that.date, _that.weightKg);
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
    TResult Function(String date, double weightKg) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WeightPoint():
        return $default(_that.date, _that.weightKg);
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
    TResult? Function(String date, double weightKg)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _WeightPoint() when $default != null:
        return $default(_that.date, _that.weightKg);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _WeightPoint implements WeightPoint {
  const _WeightPoint({required this.date, required this.weightKg});
  factory _WeightPoint.fromJson(Map<String, dynamic> json) =>
      _$WeightPointFromJson(json);

  @override
  final String date;
  @override
  final double weightKg;

  /// Create a copy of WeightPoint
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$WeightPointCopyWith<_WeightPoint> get copyWith =>
      __$WeightPointCopyWithImpl<_WeightPoint>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$WeightPointToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _WeightPoint &&
            (identical(other.date, date) || other.date == date) &&
            (identical(other.weightKg, weightKg) ||
                other.weightKg == weightKg));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, date, weightKg);
  }

  @override
  String toString() {
    return 'WeightPoint(date: $date, weightKg: $weightKg)';
  }
}

/// @nodoc
abstract mixin class _$WeightPointCopyWith<$Res>
    implements $WeightPointCopyWith<$Res> {
  factory _$WeightPointCopyWith(
    _WeightPoint value,
    $Res Function(_WeightPoint) _then,
  ) = __$WeightPointCopyWithImpl;
  @override
  @useResult
  $Res call({String date, double weightKg});
}

/// @nodoc
class __$WeightPointCopyWithImpl<$Res> implements _$WeightPointCopyWith<$Res> {
  __$WeightPointCopyWithImpl(this._self, this._then);

  final _WeightPoint _self;
  final $Res Function(_WeightPoint) _then;

  /// Create a copy of WeightPoint
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? date = null, Object? weightKg = null}) {
    return _then(
      _WeightPoint(
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        weightKg: null == weightKg
            ? _self.weightKg
            : weightKg // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }
}

/// @nodoc
mixin _$ValuePoint {
  String get date;
  double get value;

  /// Create a copy of ValuePoint
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ValuePointCopyWith<ValuePoint> get copyWith =>
      _$ValuePointCopyWithImpl<ValuePoint>(this as ValuePoint, _$identity);

  /// Serializes this ValuePoint to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as ValuePoint;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is ValuePoint &&
            (identical(other.date, _this.date) || other.date == _this.date) &&
            (identical(other.value, _this.value) ||
                other.value == _this.value));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as ValuePoint;
    return Object.hash(runtimeType, _this.date, _this.value);
  }

  @override
  String toString() {
    final _this = this as ValuePoint;
    return 'ValuePoint(date: ${_this.date}, value: ${_this.value})';
  }
}

/// @nodoc
abstract mixin class $ValuePointCopyWith<$Res> {
  factory $ValuePointCopyWith(
    ValuePoint value,
    $Res Function(ValuePoint) _then,
  ) = _$ValuePointCopyWithImpl;
  @useResult
  $Res call({String date, double value});
}

/// @nodoc
class _$ValuePointCopyWithImpl<$Res> implements $ValuePointCopyWith<$Res> {
  _$ValuePointCopyWithImpl(this._self, this._then);

  final ValuePoint _self;
  final $Res Function(ValuePoint) _then;

  /// Create a copy of ValuePoint
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? date = null, Object? value = null}) {
    return _then(
      ValuePoint(
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        value: null == value
            ? _self.value
            : value // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [ValuePoint].
extension ValuePointPatterns on ValuePoint {
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
    TResult Function(_ValuePoint value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ValuePoint() when $default != null:
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
    TResult Function(_ValuePoint value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ValuePoint():
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
    TResult? Function(_ValuePoint value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ValuePoint() when $default != null:
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
    TResult Function(String date, double value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ValuePoint() when $default != null:
        return $default(_that.date, _that.value);
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
    TResult Function(String date, double value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ValuePoint():
        return $default(_that.date, _that.value);
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
    TResult? Function(String date, double value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ValuePoint() when $default != null:
        return $default(_that.date, _that.value);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _ValuePoint implements ValuePoint {
  const _ValuePoint({required this.date, required this.value});
  factory _ValuePoint.fromJson(Map<String, dynamic> json) =>
      _$ValuePointFromJson(json);

  @override
  final String date;
  @override
  final double value;

  /// Create a copy of ValuePoint
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ValuePointCopyWith<_ValuePoint> get copyWith =>
      __$ValuePointCopyWithImpl<_ValuePoint>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ValuePointToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _ValuePoint &&
            (identical(other.date, date) || other.date == date) &&
            (identical(other.value, value) || other.value == value));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(runtimeType, date, value);
  }

  @override
  String toString() {
    return 'ValuePoint(date: $date, value: $value)';
  }
}

/// @nodoc
abstract mixin class _$ValuePointCopyWith<$Res>
    implements $ValuePointCopyWith<$Res> {
  factory _$ValuePointCopyWith(
    _ValuePoint value,
    $Res Function(_ValuePoint) _then,
  ) = __$ValuePointCopyWithImpl;
  @override
  @useResult
  $Res call({String date, double value});
}

/// @nodoc
class __$ValuePointCopyWithImpl<$Res> implements _$ValuePointCopyWith<$Res> {
  __$ValuePointCopyWithImpl(this._self, this._then);

  final _ValuePoint _self;
  final $Res Function(_ValuePoint) _then;

  /// Create a copy of ValuePoint
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? date = null, Object? value = null}) {
    return _then(
      _ValuePoint(
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        value: null == value
            ? _self.value
            : value // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }
}

/// @nodoc
mixin _$MeasurementPoint {
  String get date;
  Map<String, dynamic> get values;

  /// Create a copy of MeasurementPoint
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $MeasurementPointCopyWith<MeasurementPoint> get copyWith =>
      _$MeasurementPointCopyWithImpl<MeasurementPoint>(
        this as MeasurementPoint,
        _$identity,
      );

  /// Serializes this MeasurementPoint to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as MeasurementPoint;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is MeasurementPoint &&
            (identical(other.date, _this.date) || other.date == _this.date) &&
            const DeepCollectionEquality().equals(other.values, _this.values));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as MeasurementPoint;
    return Object.hash(
      runtimeType,
      _this.date,
      const DeepCollectionEquality().hash(_this.values),
    );
  }

  @override
  String toString() {
    final _this = this as MeasurementPoint;
    return 'MeasurementPoint(date: ${_this.date}, values: ${_this.values})';
  }
}

/// @nodoc
abstract mixin class $MeasurementPointCopyWith<$Res> {
  factory $MeasurementPointCopyWith(
    MeasurementPoint value,
    $Res Function(MeasurementPoint) _then,
  ) = _$MeasurementPointCopyWithImpl;
  @useResult
  $Res call({String date, Map<String, dynamic> values});
}

/// @nodoc
class _$MeasurementPointCopyWithImpl<$Res>
    implements $MeasurementPointCopyWith<$Res> {
  _$MeasurementPointCopyWithImpl(this._self, this._then);

  final MeasurementPoint _self;
  final $Res Function(MeasurementPoint) _then;

  /// Create a copy of MeasurementPoint
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({Object? date = null, Object? values = null}) {
    return _then(
      MeasurementPoint(
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        values: null == values
            ? _self.values
            : values // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [MeasurementPoint].
extension MeasurementPointPatterns on MeasurementPoint {
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
    TResult Function(_MeasurementPoint value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MeasurementPoint() when $default != null:
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
    TResult Function(_MeasurementPoint value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MeasurementPoint():
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
    TResult? Function(_MeasurementPoint value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MeasurementPoint() when $default != null:
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
    TResult Function(String date, Map<String, dynamic> values)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _MeasurementPoint() when $default != null:
        return $default(_that.date, _that.values);
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
    TResult Function(String date, Map<String, dynamic> values) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MeasurementPoint():
        return $default(_that.date, _that.values);
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
    TResult? Function(String date, Map<String, dynamic> values)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _MeasurementPoint() when $default != null:
        return $default(_that.date, _that.values);
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _MeasurementPoint extends MeasurementPoint {
  const _MeasurementPoint({
    required this.date,
    Map<String, dynamic> values = const <String, dynamic>{},
  }) : _values = values,
       super._();
  factory _MeasurementPoint.fromJson(Map<String, dynamic> json) =>
      _$MeasurementPointFromJson(json);

  @override
  final String date;
  final Map<String, dynamic> _values;
  @override
  @JsonKey()
  Map<String, dynamic> get values {
    if (_values is EqualUnmodifiableMapView) return _values;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(_values);
  }

  /// Create a copy of MeasurementPoint
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$MeasurementPointCopyWith<_MeasurementPoint> get copyWith =>
      __$MeasurementPointCopyWithImpl<_MeasurementPoint>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$MeasurementPointToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _MeasurementPoint &&
            (identical(other.date, date) || other.date == date) &&
            const DeepCollectionEquality().equals(other.values, _values));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      date,
      const DeepCollectionEquality().hash(_values),
    );
  }

  @override
  String toString() {
    return 'MeasurementPoint(date: $date, values: $values)';
  }
}

/// @nodoc
abstract mixin class _$MeasurementPointCopyWith<$Res>
    implements $MeasurementPointCopyWith<$Res> {
  factory _$MeasurementPointCopyWith(
    _MeasurementPoint value,
    $Res Function(_MeasurementPoint) _then,
  ) = __$MeasurementPointCopyWithImpl;
  @override
  @useResult
  $Res call({String date, Map<String, dynamic> values});
}

/// @nodoc
class __$MeasurementPointCopyWithImpl<$Res>
    implements _$MeasurementPointCopyWith<$Res> {
  __$MeasurementPointCopyWithImpl(this._self, this._then);

  final _MeasurementPoint _self;
  final $Res Function(_MeasurementPoint) _then;

  /// Create a copy of MeasurementPoint
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({Object? date = null, Object? values = null}) {
    return _then(
      _MeasurementPoint(
        date: null == date
            ? _self.date
            : date // ignore: cast_nullable_to_non_nullable
                  as String,
        values: null == values
            ? _self._values
            : values // ignore: cast_nullable_to_non_nullable
                  as Map<String, dynamic>,
      ),
    );
  }
}

/// @nodoc
mixin _$Compliance {
  double get score;
  double get workoutsPct;
  double get nutritionPct;
  double get habitsPct;
  double get checkinsPct;

  /// Create a copy of Compliance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ComplianceCopyWith<Compliance> get copyWith =>
      _$ComplianceCopyWithImpl<Compliance>(this as Compliance, _$identity);

  /// Serializes this Compliance to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as Compliance;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is Compliance &&
            (identical(other.score, _this.score) ||
                other.score == _this.score) &&
            (identical(other.workoutsPct, _this.workoutsPct) ||
                other.workoutsPct == _this.workoutsPct) &&
            (identical(other.nutritionPct, _this.nutritionPct) ||
                other.nutritionPct == _this.nutritionPct) &&
            (identical(other.habitsPct, _this.habitsPct) ||
                other.habitsPct == _this.habitsPct) &&
            (identical(other.checkinsPct, _this.checkinsPct) ||
                other.checkinsPct == _this.checkinsPct));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as Compliance;
    return Object.hash(
      runtimeType,
      _this.score,
      _this.workoutsPct,
      _this.nutritionPct,
      _this.habitsPct,
      _this.checkinsPct,
    );
  }

  @override
  String toString() {
    final _this = this as Compliance;
    return 'Compliance(score: ${_this.score}, workoutsPct: ${_this.workoutsPct}, nutritionPct: ${_this.nutritionPct}, habitsPct: ${_this.habitsPct}, checkinsPct: ${_this.checkinsPct})';
  }
}

/// @nodoc
abstract mixin class $ComplianceCopyWith<$Res> {
  factory $ComplianceCopyWith(
    Compliance value,
    $Res Function(Compliance) _then,
  ) = _$ComplianceCopyWithImpl;
  @useResult
  $Res call({
    double score,
    double workoutsPct,
    double nutritionPct,
    double habitsPct,
    double checkinsPct,
  });
}

/// @nodoc
class _$ComplianceCopyWithImpl<$Res> implements $ComplianceCopyWith<$Res> {
  _$ComplianceCopyWithImpl(this._self, this._then);

  final Compliance _self;
  final $Res Function(Compliance) _then;

  /// Create a copy of Compliance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? score = null,
    Object? workoutsPct = null,
    Object? nutritionPct = null,
    Object? habitsPct = null,
    Object? checkinsPct = null,
  }) {
    return _then(
      Compliance(
        score: null == score
            ? _self.score
            : score // ignore: cast_nullable_to_non_nullable
                  as double,
        workoutsPct: null == workoutsPct
            ? _self.workoutsPct
            : workoutsPct // ignore: cast_nullable_to_non_nullable
                  as double,
        nutritionPct: null == nutritionPct
            ? _self.nutritionPct
            : nutritionPct // ignore: cast_nullable_to_non_nullable
                  as double,
        habitsPct: null == habitsPct
            ? _self.habitsPct
            : habitsPct // ignore: cast_nullable_to_non_nullable
                  as double,
        checkinsPct: null == checkinsPct
            ? _self.checkinsPct
            : checkinsPct // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }
}

/// Adds pattern-matching-related methods to [Compliance].
extension CompliancePatterns on Compliance {
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
    TResult Function(_Compliance value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Compliance() when $default != null:
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
    TResult Function(_Compliance value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Compliance():
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
    TResult? Function(_Compliance value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Compliance() when $default != null:
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
      double score,
      double workoutsPct,
      double nutritionPct,
      double habitsPct,
      double checkinsPct,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _Compliance() when $default != null:
        return $default(
          _that.score,
          _that.workoutsPct,
          _that.nutritionPct,
          _that.habitsPct,
          _that.checkinsPct,
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
      double score,
      double workoutsPct,
      double nutritionPct,
      double habitsPct,
      double checkinsPct,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Compliance():
        return $default(
          _that.score,
          _that.workoutsPct,
          _that.nutritionPct,
          _that.habitsPct,
          _that.checkinsPct,
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
      double score,
      double workoutsPct,
      double nutritionPct,
      double habitsPct,
      double checkinsPct,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _Compliance() when $default != null:
        return $default(
          _that.score,
          _that.workoutsPct,
          _that.nutritionPct,
          _that.habitsPct,
          _that.checkinsPct,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
@JsonDouble()
class _Compliance implements Compliance {
  const _Compliance({
    this.score = 0,
    this.workoutsPct = 0,
    this.nutritionPct = 0,
    this.habitsPct = 0,
    this.checkinsPct = 0,
  });
  factory _Compliance.fromJson(Map<String, dynamic> json) =>
      _$ComplianceFromJson(json);

  @override
  @JsonKey()
  final double score;
  @override
  @JsonKey()
  final double workoutsPct;
  @override
  @JsonKey()
  final double nutritionPct;
  @override
  @JsonKey()
  final double habitsPct;
  @override
  @JsonKey()
  final double checkinsPct;

  /// Create a copy of Compliance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ComplianceCopyWith<_Compliance> get copyWith =>
      __$ComplianceCopyWithImpl<_Compliance>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ComplianceToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _Compliance &&
            (identical(other.score, score) || other.score == score) &&
            (identical(other.workoutsPct, workoutsPct) ||
                other.workoutsPct == workoutsPct) &&
            (identical(other.nutritionPct, nutritionPct) ||
                other.nutritionPct == nutritionPct) &&
            (identical(other.habitsPct, habitsPct) ||
                other.habitsPct == habitsPct) &&
            (identical(other.checkinsPct, checkinsPct) ||
                other.checkinsPct == checkinsPct));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      score,
      workoutsPct,
      nutritionPct,
      habitsPct,
      checkinsPct,
    );
  }

  @override
  String toString() {
    return 'Compliance(score: $score, workoutsPct: $workoutsPct, nutritionPct: $nutritionPct, habitsPct: $habitsPct, checkinsPct: $checkinsPct)';
  }
}

/// @nodoc
abstract mixin class _$ComplianceCopyWith<$Res>
    implements $ComplianceCopyWith<$Res> {
  factory _$ComplianceCopyWith(
    _Compliance value,
    $Res Function(_Compliance) _then,
  ) = __$ComplianceCopyWithImpl;
  @override
  @useResult
  $Res call({
    double score,
    double workoutsPct,
    double nutritionPct,
    double habitsPct,
    double checkinsPct,
  });
}

/// @nodoc
class __$ComplianceCopyWithImpl<$Res> implements _$ComplianceCopyWith<$Res> {
  __$ComplianceCopyWithImpl(this._self, this._then);

  final _Compliance _self;
  final $Res Function(_Compliance) _then;

  /// Create a copy of Compliance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? score = null,
    Object? workoutsPct = null,
    Object? nutritionPct = null,
    Object? habitsPct = null,
    Object? checkinsPct = null,
  }) {
    return _then(
      _Compliance(
        score: null == score
            ? _self.score
            : score // ignore: cast_nullable_to_non_nullable
                  as double,
        workoutsPct: null == workoutsPct
            ? _self.workoutsPct
            : workoutsPct // ignore: cast_nullable_to_non_nullable
                  as double,
        nutritionPct: null == nutritionPct
            ? _self.nutritionPct
            : nutritionPct // ignore: cast_nullable_to_non_nullable
                  as double,
        habitsPct: null == habitsPct
            ? _self.habitsPct
            : habitsPct // ignore: cast_nullable_to_non_nullable
                  as double,
        checkinsPct: null == checkinsPct
            ? _self.checkinsPct
            : checkinsPct // ignore: cast_nullable_to_non_nullable
                  as double,
      ),
    );
  }
}

/// @nodoc
mixin _$ProgressSummary {
  List<WeightPoint> get weight;
  List<ValuePoint> get bodyFat;
  List<MeasurementPoint> get measurements;
  List<ProgressPhoto> get photos;
  List<PersonalRecord> get records;
  Compliance get compliance;

  /// Create a copy of ProgressSummary
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  $ProgressSummaryCopyWith<ProgressSummary> get copyWith =>
      _$ProgressSummaryCopyWithImpl<ProgressSummary>(
        this as ProgressSummary,
        _$identity,
      );

  /// Serializes this ProgressSummary to a JSON map.
  Map<String, dynamic> toJson();

  @override
  bool operator ==(Object other) {
    final _this = this as ProgressSummary;
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is ProgressSummary &&
            const DeepCollectionEquality().equals(other.weight, _this.weight) &&
            const DeepCollectionEquality().equals(
              other.bodyFat,
              _this.bodyFat,
            ) &&
            const DeepCollectionEquality().equals(
              other.measurements,
              _this.measurements,
            ) &&
            const DeepCollectionEquality().equals(other.photos, _this.photos) &&
            const DeepCollectionEquality().equals(
              other.records,
              _this.records,
            ) &&
            (identical(other.compliance, _this.compliance) ||
                other.compliance == _this.compliance));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    final _this = this as ProgressSummary;
    return Object.hash(
      runtimeType,
      const DeepCollectionEquality().hash(_this.weight),
      const DeepCollectionEquality().hash(_this.bodyFat),
      const DeepCollectionEquality().hash(_this.measurements),
      const DeepCollectionEquality().hash(_this.photos),
      const DeepCollectionEquality().hash(_this.records),
      _this.compliance,
    );
  }

  @override
  String toString() {
    final _this = this as ProgressSummary;
    return 'ProgressSummary(weight: ${_this.weight}, bodyFat: ${_this.bodyFat}, measurements: ${_this.measurements}, photos: ${_this.photos}, records: ${_this.records}, compliance: ${_this.compliance})';
  }
}

/// @nodoc
abstract mixin class $ProgressSummaryCopyWith<$Res> {
  factory $ProgressSummaryCopyWith(
    ProgressSummary value,
    $Res Function(ProgressSummary) _then,
  ) = _$ProgressSummaryCopyWithImpl;
  @useResult
  $Res call({
    List<WeightPoint> weight,
    List<ValuePoint> bodyFat,
    List<MeasurementPoint> measurements,
    List<ProgressPhoto> photos,
    List<PersonalRecord> records,
    Compliance compliance,
  });

  $ComplianceCopyWith<$Res> get compliance;
}

/// @nodoc
class _$ProgressSummaryCopyWithImpl<$Res>
    implements $ProgressSummaryCopyWith<$Res> {
  _$ProgressSummaryCopyWithImpl(this._self, this._then);

  final ProgressSummary _self;
  final $Res Function(ProgressSummary) _then;

  /// Create a copy of ProgressSummary
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? weight = null,
    Object? bodyFat = null,
    Object? measurements = null,
    Object? photos = null,
    Object? records = null,
    Object? compliance = null,
  }) {
    return _then(
      ProgressSummary(
        weight: null == weight
            ? _self.weight
            : weight // ignore: cast_nullable_to_non_nullable
                  as List<WeightPoint>,
        bodyFat: null == bodyFat
            ? _self.bodyFat
            : bodyFat // ignore: cast_nullable_to_non_nullable
                  as List<ValuePoint>,
        measurements: null == measurements
            ? _self.measurements
            : measurements // ignore: cast_nullable_to_non_nullable
                  as List<MeasurementPoint>,
        photos: null == photos
            ? _self.photos
            : photos // ignore: cast_nullable_to_non_nullable
                  as List<ProgressPhoto>,
        records: null == records
            ? _self.records
            : records // ignore: cast_nullable_to_non_nullable
                  as List<PersonalRecord>,
        compliance: null == compliance
            ? _self.compliance
            : compliance // ignore: cast_nullable_to_non_nullable
                  as Compliance,
      ),
    );
  }

  /// Create a copy of ProgressSummary
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ComplianceCopyWith<$Res> get compliance {
    return $ComplianceCopyWith<$Res>(_self.compliance, (value) {
      return _then(_self.copyWith(compliance: value));
    });
  }
}

/// Adds pattern-matching-related methods to [ProgressSummary].
extension ProgressSummaryPatterns on ProgressSummary {
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
    TResult Function(_ProgressSummary value)? $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ProgressSummary() when $default != null:
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
    TResult Function(_ProgressSummary value) $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgressSummary():
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
    TResult? Function(_ProgressSummary value)? $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgressSummary() when $default != null:
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
      List<WeightPoint> weight,
      List<ValuePoint> bodyFat,
      List<MeasurementPoint> measurements,
      List<ProgressPhoto> photos,
      List<PersonalRecord> records,
      Compliance compliance,
    )?
    $default, {
    required TResult orElse(),
  }) {
    final _that = this;
    switch (_that) {
      case _ProgressSummary() when $default != null:
        return $default(
          _that.weight,
          _that.bodyFat,
          _that.measurements,
          _that.photos,
          _that.records,
          _that.compliance,
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
      List<WeightPoint> weight,
      List<ValuePoint> bodyFat,
      List<MeasurementPoint> measurements,
      List<ProgressPhoto> photos,
      List<PersonalRecord> records,
      Compliance compliance,
    )
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgressSummary():
        return $default(
          _that.weight,
          _that.bodyFat,
          _that.measurements,
          _that.photos,
          _that.records,
          _that.compliance,
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
      List<WeightPoint> weight,
      List<ValuePoint> bodyFat,
      List<MeasurementPoint> measurements,
      List<ProgressPhoto> photos,
      List<PersonalRecord> records,
      Compliance compliance,
    )?
    $default,
  ) {
    final _that = this;
    switch (_that) {
      case _ProgressSummary() when $default != null:
        return $default(
          _that.weight,
          _that.bodyFat,
          _that.measurements,
          _that.photos,
          _that.records,
          _that.compliance,
        );
      case _:
        return null;
    }
  }
}

/// @nodoc
@JsonSerializable()
class _ProgressSummary implements ProgressSummary {
  const _ProgressSummary({
    List<WeightPoint> weight = const <WeightPoint>[],
    List<ValuePoint> bodyFat = const <ValuePoint>[],
    List<MeasurementPoint> measurements = const <MeasurementPoint>[],
    List<ProgressPhoto> photos = const <ProgressPhoto>[],
    List<PersonalRecord> records = const <PersonalRecord>[],
    this.compliance = const Compliance(),
  }) : _weight = weight,
       _bodyFat = bodyFat,
       _measurements = measurements,
       _photos = photos,
       _records = records;
  factory _ProgressSummary.fromJson(Map<String, dynamic> json) =>
      _$ProgressSummaryFromJson(json);

  final List<WeightPoint> _weight;
  @override
  @JsonKey()
  List<WeightPoint> get weight {
    if (_weight is EqualUnmodifiableListView) return _weight;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_weight);
  }

  final List<ValuePoint> _bodyFat;
  @override
  @JsonKey()
  List<ValuePoint> get bodyFat {
    if (_bodyFat is EqualUnmodifiableListView) return _bodyFat;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_bodyFat);
  }

  final List<MeasurementPoint> _measurements;
  @override
  @JsonKey()
  List<MeasurementPoint> get measurements {
    if (_measurements is EqualUnmodifiableListView) return _measurements;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_measurements);
  }

  final List<ProgressPhoto> _photos;
  @override
  @JsonKey()
  List<ProgressPhoto> get photos {
    if (_photos is EqualUnmodifiableListView) return _photos;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_photos);
  }

  final List<PersonalRecord> _records;
  @override
  @JsonKey()
  List<PersonalRecord> get records {
    if (_records is EqualUnmodifiableListView) return _records;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_records);
  }

  @override
  @JsonKey()
  final Compliance compliance;

  /// Create a copy of ProgressSummary
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  @pragma('vm:prefer-inline')
  _$ProgressSummaryCopyWith<_ProgressSummary> get copyWith =>
      __$ProgressSummaryCopyWithImpl<_ProgressSummary>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$ProgressSummaryToJson(this);
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _ProgressSummary &&
            const DeepCollectionEquality().equals(other.weight, _weight) &&
            const DeepCollectionEquality().equals(other.bodyFat, _bodyFat) &&
            const DeepCollectionEquality().equals(
              other.measurements,
              _measurements,
            ) &&
            const DeepCollectionEquality().equals(other.photos, _photos) &&
            const DeepCollectionEquality().equals(other.records, _records) &&
            (identical(other.compliance, compliance) ||
                other.compliance == compliance));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode {
    return Object.hash(
      runtimeType,
      const DeepCollectionEquality().hash(_weight),
      const DeepCollectionEquality().hash(_bodyFat),
      const DeepCollectionEquality().hash(_measurements),
      const DeepCollectionEquality().hash(_photos),
      const DeepCollectionEquality().hash(_records),
      compliance,
    );
  }

  @override
  String toString() {
    return 'ProgressSummary(weight: $weight, bodyFat: $bodyFat, measurements: $measurements, photos: $photos, records: $records, compliance: $compliance)';
  }
}

/// @nodoc
abstract mixin class _$ProgressSummaryCopyWith<$Res>
    implements $ProgressSummaryCopyWith<$Res> {
  factory _$ProgressSummaryCopyWith(
    _ProgressSummary value,
    $Res Function(_ProgressSummary) _then,
  ) = __$ProgressSummaryCopyWithImpl;
  @override
  @useResult
  $Res call({
    List<WeightPoint> weight,
    List<ValuePoint> bodyFat,
    List<MeasurementPoint> measurements,
    List<ProgressPhoto> photos,
    List<PersonalRecord> records,
    Compliance compliance,
  });

  @override
  $ComplianceCopyWith<$Res> get compliance;
}

/// @nodoc
class __$ProgressSummaryCopyWithImpl<$Res>
    implements _$ProgressSummaryCopyWith<$Res> {
  __$ProgressSummaryCopyWithImpl(this._self, this._then);

  final _ProgressSummary _self;
  final $Res Function(_ProgressSummary) _then;

  /// Create a copy of ProgressSummary
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $Res call({
    Object? weight = null,
    Object? bodyFat = null,
    Object? measurements = null,
    Object? photos = null,
    Object? records = null,
    Object? compliance = null,
  }) {
    return _then(
      _ProgressSummary(
        weight: null == weight
            ? _self._weight
            : weight // ignore: cast_nullable_to_non_nullable
                  as List<WeightPoint>,
        bodyFat: null == bodyFat
            ? _self._bodyFat
            : bodyFat // ignore: cast_nullable_to_non_nullable
                  as List<ValuePoint>,
        measurements: null == measurements
            ? _self._measurements
            : measurements // ignore: cast_nullable_to_non_nullable
                  as List<MeasurementPoint>,
        photos: null == photos
            ? _self._photos
            : photos // ignore: cast_nullable_to_non_nullable
                  as List<ProgressPhoto>,
        records: null == records
            ? _self._records
            : records // ignore: cast_nullable_to_non_nullable
                  as List<PersonalRecord>,
        compliance: null == compliance
            ? _self.compliance
            : compliance // ignore: cast_nullable_to_non_nullable
                  as Compliance,
      ),
    );
  }

  /// Create a copy of ProgressSummary
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ComplianceCopyWith<$Res> get compliance {
    return $ComplianceCopyWith<$Res>(_self.compliance, (value) {
      return _then(_self.copyWith(compliance: value));
    });
  }
}
