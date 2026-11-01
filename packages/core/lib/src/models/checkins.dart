import 'package:freezed_annotation/freezed_annotation.dart';

import '../util/json_converters.dart';
import 'common.dart';
import 'sessions.dart';

part 'checkins.freezed.dart';
part 'checkins.g.dart';

/// Item of `GET /me/checkins`.
@freezed
abstract class CheckinSummary with _$CheckinSummary {
  const factory CheckinSummary({
    required String id,
    String? formName,
    required String dueOn,
    @Default('pending') String status,
    DateTime? submittedAt,
    DateTime? reviewedAt,
  }) = _CheckinSummary;

  factory CheckinSummary.fromJson(Map<String, dynamic> json) => _$CheckinSummaryFromJson(json);
}

@freezed
abstract class CheckinQuestion with _$CheckinQuestion {
  @JsonDoubleN()
  const factory CheckinQuestion({
    required String id,
    required String type,
    required String label,
    @JsonKey(name: 'required') @Default(false) bool isRequired,
    List<String>? options,
    double? min,
    double? max,
    String? unit,
  }) = _CheckinQuestion;

  factory CheckinQuestion.fromJson(Map<String, dynamic> json) => _$CheckinQuestionFromJson(json);
}

@freezed
abstract class CheckinForm with _$CheckinForm {
  const factory CheckinForm({
    required String id,
    required String name,
    String? intro,
    @Default(<CheckinQuestion>[]) List<CheckinQuestion> questions,
    @Default(true) bool includeMetrics,
    @Default(true) bool includePhotos,
  }) = _CheckinForm;

  factory CheckinForm.fromJson(Map<String, dynamic> json) => _$CheckinFormFromJson(json);
}

@freezed
abstract class CheckinComment with _$CheckinComment {
  const factory CheckinComment({required String id, required IdName user, required String body, DateTime? createdAt}) =
      _CheckinComment;

  factory CheckinComment.fromJson(Map<String, dynamic> json) => _$CheckinCommentFromJson(json);
}

/// `GET /me/checkins/{id}`.
@freezed
abstract class CheckinDetail with _$CheckinDetail {
  const factory CheckinDetail({
    required String id,
    @Default('pending') String status,
    required String dueOn,
    required CheckinForm form,
    @Default(<String, dynamic>{}) Map<String, dynamic> answers,
    String? coachFeedback,
    @Default(<CheckinComment>[]) List<CheckinComment> comments,
  }) = _CheckinDetail;

  factory CheckinDetail.fromJson(Map<String, dynamic> json) => _$CheckinDetailFromJson(json);
}

/// `POST /me/progress-photos` → `{id, pose, taken_on, url}`.
@freezed
abstract class ProgressPhoto with _$ProgressPhoto {
  const factory ProgressPhoto({required String id, required String pose, required String takenOn, String? url}) =
      _ProgressPhoto;

  factory ProgressPhoto.fromJson(Map<String, dynamic> json) => _$ProgressPhotoFromJson(json);
}

@freezed
abstract class WeightPoint with _$WeightPoint {
  @JsonDouble()
  const factory WeightPoint({required String date, required double weightKg}) = _WeightPoint;

  factory WeightPoint.fromJson(Map<String, dynamic> json) => _$WeightPointFromJson(json);
}

@freezed
abstract class ValuePoint with _$ValuePoint {
  @JsonDouble()
  const factory ValuePoint({required String date, required double value}) = _ValuePoint;

  factory ValuePoint.fromJson(Map<String, dynamic> json) => _$ValuePointFromJson(json);
}

@freezed
abstract class MeasurementPoint with _$MeasurementPoint {
  const MeasurementPoint._();

  const factory MeasurementPoint({required String date, @Default(<String, dynamic>{}) Map<String, dynamic> values}) =
      _MeasurementPoint;

  factory MeasurementPoint.fromJson(Map<String, dynamic> json) => _$MeasurementPointFromJson(json);

  double? valueOf(String key) => parseDouble(values[key]);
}

@freezed
abstract class Compliance with _$Compliance {
  @JsonDouble()
  const factory Compliance({
    @Default(0) double score,
    @Default(0) double workoutsPct,
    @Default(0) double nutritionPct,
    @Default(0) double habitsPct,
    @Default(0) double checkinsPct,
  }) = _Compliance;

  factory Compliance.fromJson(Map<String, dynamic> json) => _$ComplianceFromJson(json);
}

/// `GET /me/progress`.
@freezed
abstract class ProgressSummary with _$ProgressSummary {
  const factory ProgressSummary({
    @Default(<WeightPoint>[]) List<WeightPoint> weight,
    @Default(<ValuePoint>[]) List<ValuePoint> bodyFat,
    @Default(<MeasurementPoint>[]) List<MeasurementPoint> measurements,
    @Default(<ProgressPhoto>[]) List<ProgressPhoto> photos,
    @Default(<PersonalRecord>[]) List<PersonalRecord> records,
    @Default(Compliance()) Compliance compliance,
  }) = _ProgressSummary;

  factory ProgressSummary.fromJson(Map<String, dynamic> json) => _$ProgressSummaryFromJson(json);
}
