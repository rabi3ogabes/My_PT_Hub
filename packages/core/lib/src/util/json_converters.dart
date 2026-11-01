import 'package:json_annotation/json_annotation.dart';

/// Accepts numbers or numeric strings (Laravel decimal casts serialize as
/// strings) and always yields a [double].
class JsonDouble implements JsonConverter<double, Object?> {
  const JsonDouble();

  @override
  double fromJson(Object? json) => parseDouble(json) ?? 0;

  @override
  Object toJson(double object) => object;
}

/// Nullable variant of [JsonDouble].
class JsonDoubleN implements JsonConverter<double?, Object?> {
  const JsonDoubleN();

  @override
  double? fromJson(Object? json) => parseDouble(json);

  @override
  Object? toJson(double? object) => object;
}

double? parseDouble(Object? json) {
  if (json == null) return null;
  if (json is num) return json.toDouble();
  return double.tryParse(json.toString());
}

/// Formats a [DateTime] as an ISO-8601 UTC string the API understands.
String toApiDateTime(DateTime value) {
  final utc = value.toUtc();
  final ms = DateTime.fromMillisecondsSinceEpoch(utc.millisecondsSinceEpoch, isUtc: true);
  return ms.toIso8601String();
}

/// Formats a local calendar date as `YYYY-MM-DD`.
String toApiDate(DateTime value) {
  final y = value.year.toString().padLeft(4, '0');
  final m = value.month.toString().padLeft(2, '0');
  final d = value.day.toString().padLeft(2, '0');
  return '$y-$m-$d';
}

/// Parses a `YYYY-MM-DD` date as a local calendar date (no timezone shift).
DateTime parseApiDate(String value) {
  final parts = value.split('-').map(int.parse).toList();
  return DateTime(parts[0], parts[1], parts[2]);
}
