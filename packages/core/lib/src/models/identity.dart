import 'package:freezed_annotation/freezed_annotation.dart';

part 'identity.freezed.dart';
part 'identity.g.dart';

@freezed
abstract class NotificationChannelPref with _$NotificationChannelPref {
  const factory NotificationChannelPref({@Default(true) bool push, @Default(true) bool email}) =
      _NotificationChannelPref;

  factory NotificationChannelPref.fromJson(Map<String, dynamic> json) => _$NotificationChannelPrefFromJson(json);
}

@freezed
abstract class QuietHours with _$QuietHours {
  const factory QuietHours({@Default(false) bool enabled, String? from, String? to}) = _QuietHours;

  factory QuietHours.fromJson(Map<String, dynamic> json) => _$QuietHoursFromJson(json);
}

/// **User** — see API_CONTRACT.md § Auth.
@freezed
abstract class User with _$User {
  const factory User({
    required String id,
    required String name,
    String? firstName,
    required String email,
    String? phone,
    String? avatarUrl,
    @Default('en') String locale,
    @Default('UTC') String timezone,
    @Default('metric') String units,
    @Default('system') String theme,
    @Default(false) bool emailVerified,
    @Default(false) bool twoFactorEnabled,
    String? currentTenantId,
    @Default(false) bool isSuperAdmin,
    @Default(<String, NotificationChannelPref>{}) Map<String, NotificationChannelPref> notificationPreferences,
    QuietHours? quietHours,
    DateTime? createdAt,
  }) = _User;

  factory User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);
}

/// **Tenant** — a coaching business (or the user's personal space).
@freezed
abstract class Tenant with _$Tenant {
  const Tenant._();

  const factory Tenant({
    required String id,
    @Default('business') String type,
    required String name,
    String? slug,
    String? logoUrl,
    String? brandColor,
    @Default('USD') String currency,
    String? country,
    String? timezone,
    String? locale,
    String? plan,
    DateTime? trialEndsAt,
    @Default(false) bool onboarded,
    @Default('client') String role,
  }) = _Tenant;

  factory Tenant.fromJson(Map<String, dynamic> json) => _$TenantFromJson(json);

  bool get isPersonal => type == 'personal';
  bool get isStaff => role == 'owner' || role == 'coach' || role == 'assistant';
}

/// **Tokens** — access + rotating refresh token.
@freezed
abstract class Tokens with _$Tokens {
  const factory Tokens({
    required String accessToken,
    required String refreshToken,
    @Default('Bearer') String tokenType,
    DateTime? expiresAt,
    DateTime? refreshExpiresAt,
  }) = _Tokens;

  factory Tokens.fromJson(Map<String, dynamic> json) => _$TokensFromJson(json);
}

/// **Session** = Tokens + `{user, tenants}`. Named `AuthSession` to avoid a
/// clash with workout sessions.
@freezed
abstract class AuthSession with _$AuthSession {
  const AuthSession._();

  const factory AuthSession({
    required String accessToken,
    required String refreshToken,
    @Default('Bearer') String tokenType,
    DateTime? expiresAt,
    DateTime? refreshExpiresAt,
    required User user,
    @Default(<Tenant>[]) List<Tenant> tenants,
  }) = _AuthSession;

  factory AuthSession.fromJson(Map<String, dynamic> json) => _$AuthSessionFromJson(json);

  Tokens get tokens => Tokens(
        accessToken: accessToken,
        refreshToken: refreshToken,
        tokenType: tokenType,
        expiresAt: expiresAt,
        refreshExpiresAt: refreshExpiresAt,
      );
}

/// `GET /me` and `POST /invitations/accept` → `{user, tenants}`.
@freezed
abstract class MeResponse with _$MeResponse {
  const factory MeResponse({required User user, @Default(<Tenant>[]) List<Tenant> tenants}) = _MeResponse;

  factory MeResponse.fromJson(Map<String, dynamic> json) => _$MeResponseFromJson(json);
}

@freezed
abstract class InvitationTenant with _$InvitationTenant {
  const factory InvitationTenant({required String name, String? logoUrl, String? brandColor}) = _InvitationTenant;

  factory InvitationTenant.fromJson(Map<String, dynamic> json) => _$InvitationTenantFromJson(json);
}

@freezed
abstract class InvitationCoach with _$InvitationCoach {
  const factory InvitationCoach({required String name}) = _InvitationCoach;

  factory InvitationCoach.fromJson(Map<String, dynamic> json) => _$InvitationCoachFromJson(json);
}

/// `GET /invitations/{code}`.
@freezed
abstract class InvitationPreview with _$InvitationPreview {
  const factory InvitationPreview({
    required String code,
    @Default('client') String role,
    String? email,
    String? name,
    required InvitationTenant tenant,
    InvitationCoach? coach,
  }) = _InvitationPreview;

  factory InvitationPreview.fromJson(Map<String, dynamic> json) => _$InvitationPreviewFromJson(json);
}

/// Device registration info sent with auth calls and `POST /me/devices`.
@freezed
abstract class DeviceInfo with _$DeviceInfo {
  const factory DeviceInfo({
    String? deviceName,
    String? platform,
    String? pushToken,
    String? app,
    String? appVersion,
    String? timezone,
  }) = _DeviceInfo;

  factory DeviceInfo.fromJson(Map<String, dynamic> json) => _$DeviceInfoFromJson(json);
}
