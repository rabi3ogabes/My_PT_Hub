// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'checkins.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_CheckinSummary _$CheckinSummaryFromJson(Map<String, dynamic> json) =>
    _CheckinSummary(
      id: json['id'] as String,
      formName: json['form_name'] as String?,
      dueOn: json['due_on'] as String,
      status: json['status'] as String? ?? 'pending',
      submittedAt: json['submitted_at'] == null
          ? null
          : DateTime.parse(json['submitted_at'] as String),
      reviewedAt: json['reviewed_at'] == null
          ? null
          : DateTime.parse(json['reviewed_at'] as String),
    );

Map<String, dynamic> _$CheckinSummaryToJson(_CheckinSummary instance) =>
    <String, dynamic>{
      'id': instance.id,
      'form_name': instance.formName,
      'due_on': instance.dueOn,
      'status': instance.status,
      'submitted_at': instance.submittedAt?.toIso8601String(),
      'reviewed_at': instance.reviewedAt?.toIso8601String(),
    };

_CheckinQuestion _$CheckinQuestionFromJson(Map<String, dynamic> json) =>
    _CheckinQuestion(
      id: json['id'] as String,
      type: json['type'] as String,
      label: json['label'] as String,
      isRequired: json['required'] as bool? ?? false,
      options: (json['options'] as List<dynamic>?)
          ?.map((e) => e as String)
          .toList(),
      min: const JsonDoubleN().fromJson(json['min']),
      max: const JsonDoubleN().fromJson(json['max']),
      unit: json['unit'] as String?,
    );

Map<String, dynamic> _$CheckinQuestionToJson(_CheckinQuestion instance) =>
    <String, dynamic>{
      'id': instance.id,
      'type': instance.type,
      'label': instance.label,
      'required': instance.isRequired,
      'options': instance.options,
      'min': const JsonDoubleN().toJson(instance.min),
      'max': const JsonDoubleN().toJson(instance.max),
      'unit': instance.unit,
    };

_CheckinForm _$CheckinFormFromJson(Map<String, dynamic> json) => _CheckinForm(
  id: json['id'] as String,
  name: json['name'] as String,
  intro: json['intro'] as String?,
  questions:
      (json['questions'] as List<dynamic>?)
          ?.map((e) => CheckinQuestion.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <CheckinQuestion>[],
  includeMetrics: json['include_metrics'] as bool? ?? true,
  includePhotos: json['include_photos'] as bool? ?? true,
);

Map<String, dynamic> _$CheckinFormToJson(_CheckinForm instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'intro': instance.intro,
      'questions': instance.questions.map((e) => e.toJson()).toList(),
      'include_metrics': instance.includeMetrics,
      'include_photos': instance.includePhotos,
    };

_CheckinComment _$CheckinCommentFromJson(Map<String, dynamic> json) =>
    _CheckinComment(
      id: json['id'] as String,
      user: IdName.fromJson(json['user'] as Map<String, dynamic>),
      body: json['body'] as String,
      createdAt: json['created_at'] == null
          ? null
          : DateTime.parse(json['created_at'] as String),
    );

Map<String, dynamic> _$CheckinCommentToJson(_CheckinComment instance) =>
    <String, dynamic>{
      'id': instance.id,
      'user': instance.user.toJson(),
      'body': instance.body,
      'created_at': instance.createdAt?.toIso8601String(),
    };

_CheckinDetail _$CheckinDetailFromJson(Map<String, dynamic> json) =>
    _CheckinDetail(
      id: json['id'] as String,
      status: json['status'] as String? ?? 'pending',
      dueOn: json['due_on'] as String,
      form: CheckinForm.fromJson(json['form'] as Map<String, dynamic>),
      answers:
          json['answers'] as Map<String, dynamic>? ?? const <String, dynamic>{},
      coachFeedback: json['coach_feedback'] as String?,
      comments:
          (json['comments'] as List<dynamic>?)
              ?.map((e) => CheckinComment.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <CheckinComment>[],
    );

Map<String, dynamic> _$CheckinDetailToJson(_CheckinDetail instance) =>
    <String, dynamic>{
      'id': instance.id,
      'status': instance.status,
      'due_on': instance.dueOn,
      'form': instance.form.toJson(),
      'answers': instance.answers,
      'coach_feedback': instance.coachFeedback,
      'comments': instance.comments.map((e) => e.toJson()).toList(),
    };

_ProgressPhoto _$ProgressPhotoFromJson(Map<String, dynamic> json) =>
    _ProgressPhoto(
      id: json['id'] as String,
      pose: json['pose'] as String,
      takenOn: json['taken_on'] as String,
      url: json['url'] as String?,
    );

Map<String, dynamic> _$ProgressPhotoToJson(_ProgressPhoto instance) =>
    <String, dynamic>{
      'id': instance.id,
      'pose': instance.pose,
      'taken_on': instance.takenOn,
      'url': instance.url,
    };

_WeightPoint _$WeightPointFromJson(Map<String, dynamic> json) => _WeightPoint(
  date: json['date'] as String,
  weightKg: const JsonDouble().fromJson(json['weight_kg']),
);

Map<String, dynamic> _$WeightPointToJson(_WeightPoint instance) =>
    <String, dynamic>{
      'date': instance.date,
      'weight_kg': const JsonDouble().toJson(instance.weightKg),
    };

_ValuePoint _$ValuePointFromJson(Map<String, dynamic> json) => _ValuePoint(
  date: json['date'] as String,
  value: const JsonDouble().fromJson(json['value']),
);

Map<String, dynamic> _$ValuePointToJson(_ValuePoint instance) =>
    <String, dynamic>{
      'date': instance.date,
      'value': const JsonDouble().toJson(instance.value),
    };

_MeasurementPoint _$MeasurementPointFromJson(Map<String, dynamic> json) =>
    _MeasurementPoint(
      date: json['date'] as String,
      values:
          json['values'] as Map<String, dynamic>? ?? const <String, dynamic>{},
    );

Map<String, dynamic> _$MeasurementPointToJson(_MeasurementPoint instance) =>
    <String, dynamic>{'date': instance.date, 'values': instance.values};

_Compliance _$ComplianceFromJson(Map<String, dynamic> json) => _Compliance(
  score: json['score'] == null ? 0 : const JsonDouble().fromJson(json['score']),
  workoutsPct: json['workouts_pct'] == null
      ? 0
      : const JsonDouble().fromJson(json['workouts_pct']),
  nutritionPct: json['nutrition_pct'] == null
      ? 0
      : const JsonDouble().fromJson(json['nutrition_pct']),
  habitsPct: json['habits_pct'] == null
      ? 0
      : const JsonDouble().fromJson(json['habits_pct']),
  checkinsPct: json['checkins_pct'] == null
      ? 0
      : const JsonDouble().fromJson(json['checkins_pct']),
);

Map<String, dynamic> _$ComplianceToJson(_Compliance instance) =>
    <String, dynamic>{
      'score': const JsonDouble().toJson(instance.score),
      'workouts_pct': const JsonDouble().toJson(instance.workoutsPct),
      'nutrition_pct': const JsonDouble().toJson(instance.nutritionPct),
      'habits_pct': const JsonDouble().toJson(instance.habitsPct),
      'checkins_pct': const JsonDouble().toJson(instance.checkinsPct),
    };

_ProgressSummary _$ProgressSummaryFromJson(Map<String, dynamic> json) =>
    _ProgressSummary(
      weight:
          (json['weight'] as List<dynamic>?)
              ?.map((e) => WeightPoint.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <WeightPoint>[],
      bodyFat:
          (json['body_fat'] as List<dynamic>?)
              ?.map((e) => ValuePoint.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <ValuePoint>[],
      measurements:
          (json['measurements'] as List<dynamic>?)
              ?.map((e) => MeasurementPoint.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <MeasurementPoint>[],
      photos:
          (json['photos'] as List<dynamic>?)
              ?.map((e) => ProgressPhoto.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <ProgressPhoto>[],
      records:
          (json['records'] as List<dynamic>?)
              ?.map((e) => PersonalRecord.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <PersonalRecord>[],
      compliance: json['compliance'] == null
          ? const Compliance()
          : Compliance.fromJson(json['compliance'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$ProgressSummaryToJson(_ProgressSummary instance) =>
    <String, dynamic>{
      'weight': instance.weight.map((e) => e.toJson()).toList(),
      'body_fat': instance.bodyFat.map((e) => e.toJson()).toList(),
      'measurements': instance.measurements.map((e) => e.toJson()).toList(),
      'photos': instance.photos.map((e) => e.toJson()).toList(),
      'records': instance.records.map((e) => e.toJson()).toList(),
      'compliance': instance.compliance.toJson(),
    };
