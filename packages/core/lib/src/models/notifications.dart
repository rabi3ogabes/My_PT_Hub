import 'package:freezed_annotation/freezed_annotation.dart';

part 'notifications.freezed.dart';
part 'notifications.g.dart';

/// Item of `GET /notifications`.
@freezed
abstract class AppNotification with _$AppNotification {
  const AppNotification._();

  const factory AppNotification({
    required String id,
    @Default('general') String category,
    required String title,
    String? body,
    String? link,
    @Default(<String, dynamic>{}) Map<String, dynamic> data,
    DateTime? readAt,
    required DateTime createdAt,
  }) = _AppNotification;

  factory AppNotification.fromJson(Map<String, dynamic> json) => _$AppNotificationFromJson(json);

  bool get isRead => readAt != null;
}
