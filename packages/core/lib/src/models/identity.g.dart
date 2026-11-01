// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'identity.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_NotificationChannelPref _$NotificationChannelPrefFromJson(
  Map<String, dynamic> json,
) => _NotificationChannelPref(
  push: json['push'] as bool? ?? true,
  email: json['email'] as bool? ?? true,
);

Map<String, dynamic> _$NotificationChannelPrefToJson(
  _NotificationChannelPref instance,
) => <String, dynamic>{'push': instance.push, 'email': instance.email};

_QuietHours _$QuietHoursFromJson(Map<String, dynamic> json) => _QuietHours(
  enabled: json['enabled'] as bool? ?? false,
  from: json['from'] as String?,
  to: json['to'] as String?,
);

Map<String, dynamic> _$QuietHoursToJson(_QuietHours instance) =>
    <String, dynamic>{
      'enabled': instance.enabled,
      'from': instance.from,
      'to': instance.to,
    };

_User _$UserFromJson(Map<String, dynamic> json) => _User(
  id: json['id'] as String,
  name: json['name'] as String,
  firstName: json['first_name'] as String?,
  email: json['email'] as String,
  phone: json['phone'] as String?,
  avatarUrl: json['avatar_url'] as String?,
  locale: json['locale'] as String? ?? 'en',
  timezone: json['timezone'] as String? ?? 'UTC',
  units: json['units'] as String? ?? 'metric',
  theme: json['theme'] as String? ?? 'system',
  emailVerified: json['email_verified'] as bool? ?? false,
  twoFactorEnabled: json['two_factor_enabled'] as bool? ?? false,
  currentTenantId: json['current_tenant_id'] as String?,
  isSuperAdmin: json['is_super_admin'] as bool? ?? false,
  notificationPreferences:
      (json['notification_preferences'] as Map<String, dynamic>?)?.map(
        (k, e) => MapEntry(
          k,
          NotificationChannelPref.fromJson(e as Map<String, dynamic>),
        ),
      ) ??
      const <String, NotificationChannelPref>{},
  quietHours: json['quiet_hours'] == null
      ? null
      : QuietHours.fromJson(json['quiet_hours'] as Map<String, dynamic>),
  createdAt: json['created_at'] == null
      ? null
      : DateTime.parse(json['created_at'] as String),
);

Map<String, dynamic> _$UserToJson(_User instance) => <String, dynamic>{
  'id': instance.id,
  'name': instance.name,
  'first_name': instance.firstName,
  'email': instance.email,
  'phone': instance.phone,
  'avatar_url': instance.avatarUrl,
  'locale': instance.locale,
  'timezone': instance.timezone,
  'units': instance.units,
  'theme': instance.theme,
  'email_verified': instance.emailVerified,
  'two_factor_enabled': instance.twoFactorEnabled,
  'current_tenant_id': instance.currentTenantId,
  'is_super_admin': instance.isSuperAdmin,
  'notification_preferences': instance.notificationPreferences.map(
    (k, e) => MapEntry(k, e.toJson()),
  ),
  'quiet_hours': instance.quietHours?.toJson(),
  'created_at': instance.createdAt?.toIso8601String(),
};

_Tenant _$TenantFromJson(Map<String, dynamic> json) => _Tenant(
  id: json['id'] as String,
  type: json['type'] as String? ?? 'business',
  name: json['name'] as String,
  slug: json['slug'] as String?,
  logoUrl: json['logo_url'] as String?,
  brandColor: json['brand_color'] as String?,
  currency: json['currency'] as String? ?? 'USD',
  country: json['country'] as String?,
  timezone: json['timezone'] as String?,
  locale: json['locale'] as String?,
  plan: json['plan'] as String?,
  trialEndsAt: json['trial_ends_at'] == null
      ? null
      : DateTime.parse(json['trial_ends_at'] as String),
  onboarded: json['onboarded'] as bool? ?? false,
  role: json['role'] as String? ?? 'client',
);

Map<String, dynamic> _$TenantToJson(_Tenant instance) => <String, dynamic>{
  'id': instance.id,
  'type': instance.type,
  'name': instance.name,
  'slug': instance.slug,
  'logo_url': instance.logoUrl,
  'brand_color': instance.brandColor,
  'currency': instance.currency,
  'country': instance.country,
  'timezone': instance.timezone,
  'locale': instance.locale,
  'plan': instance.plan,
  'trial_ends_at': instance.trialEndsAt?.toIso8601String(),
  'onboarded': instance.onboarded,
  'role': instance.role,
};

_Tokens _$TokensFromJson(Map<String, dynamic> json) => _Tokens(
  accessToken: json['access_token'] as String,
  refreshToken: json['refresh_token'] as String,
  tokenType: json['token_type'] as String? ?? 'Bearer',
  expiresAt: json['expires_at'] == null
      ? null
      : DateTime.parse(json['expires_at'] as String),
  refreshExpiresAt: json['refresh_expires_at'] == null
      ? null
      : DateTime.parse(json['refresh_expires_at'] as String),
);

Map<String, dynamic> _$TokensToJson(_Tokens instance) => <String, dynamic>{
  'access_token': instance.accessToken,
  'refresh_token': instance.refreshToken,
  'token_type': instance.tokenType,
  'expires_at': instance.expiresAt?.toIso8601String(),
  'refresh_expires_at': instance.refreshExpiresAt?.toIso8601String(),
};

_AuthSession _$AuthSessionFromJson(Map<String, dynamic> json) => _AuthSession(
  accessToken: json['access_token'] as String,
  refreshToken: json['refresh_token'] as String,
  tokenType: json['token_type'] as String? ?? 'Bearer',
  expiresAt: json['expires_at'] == null
      ? null
      : DateTime.parse(json['expires_at'] as String),
  refreshExpiresAt: json['refresh_expires_at'] == null
      ? null
      : DateTime.parse(json['refresh_expires_at'] as String),
  user: User.fromJson(json['user'] as Map<String, dynamic>),
  tenants:
      (json['tenants'] as List<dynamic>?)
          ?.map((e) => Tenant.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <Tenant>[],
);

Map<String, dynamic> _$AuthSessionToJson(_AuthSession instance) =>
    <String, dynamic>{
      'access_token': instance.accessToken,
      'refresh_token': instance.refreshToken,
      'token_type': instance.tokenType,
      'expires_at': instance.expiresAt?.toIso8601String(),
      'refresh_expires_at': instance.refreshExpiresAt?.toIso8601String(),
      'user': instance.user.toJson(),
      'tenants': instance.tenants.map((e) => e.toJson()).toList(),
    };

_MeResponse _$MeResponseFromJson(Map<String, dynamic> json) => _MeResponse(
  user: User.fromJson(json['user'] as Map<String, dynamic>),
  tenants:
      (json['tenants'] as List<dynamic>?)
          ?.map((e) => Tenant.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <Tenant>[],
);

Map<String, dynamic> _$MeResponseToJson(_MeResponse instance) =>
    <String, dynamic>{
      'user': instance.user.toJson(),
      'tenants': instance.tenants.map((e) => e.toJson()).toList(),
    };

_InvitationTenant _$InvitationTenantFromJson(Map<String, dynamic> json) =>
    _InvitationTenant(
      name: json['name'] as String,
      logoUrl: json['logo_url'] as String?,
      brandColor: json['brand_color'] as String?,
    );

Map<String, dynamic> _$InvitationTenantToJson(_InvitationTenant instance) =>
    <String, dynamic>{
      'name': instance.name,
      'logo_url': instance.logoUrl,
      'brand_color': instance.brandColor,
    };

_InvitationCoach _$InvitationCoachFromJson(Map<String, dynamic> json) =>
    _InvitationCoach(name: json['name'] as String);

Map<String, dynamic> _$InvitationCoachToJson(_InvitationCoach instance) =>
    <String, dynamic>{'name': instance.name};

_InvitationPreview _$InvitationPreviewFromJson(Map<String, dynamic> json) =>
    _InvitationPreview(
      code: json['code'] as String,
      role: json['role'] as String? ?? 'client',
      email: json['email'] as String?,
      name: json['name'] as String?,
      tenant: InvitationTenant.fromJson(json['tenant'] as Map<String, dynamic>),
      coach: json['coach'] == null
          ? null
          : InvitationCoach.fromJson(json['coach'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$InvitationPreviewToJson(_InvitationPreview instance) =>
    <String, dynamic>{
      'code': instance.code,
      'role': instance.role,
      'email': instance.email,
      'name': instance.name,
      'tenant': instance.tenant.toJson(),
      'coach': instance.coach?.toJson(),
    };

_DeviceInfo _$DeviceInfoFromJson(Map<String, dynamic> json) => _DeviceInfo(
  deviceName: json['device_name'] as String?,
  platform: json['platform'] as String?,
  pushToken: json['push_token'] as String?,
  app: json['app'] as String?,
  appVersion: json['app_version'] as String?,
  timezone: json['timezone'] as String?,
);

Map<String, dynamic> _$DeviceInfoToJson(_DeviceInfo instance) =>
    <String, dynamic>{
      'device_name': instance.deviceName,
      'platform': instance.platform,
      'push_token': instance.pushToken,
      'app': instance.app,
      'app_version': instance.appVersion,
      'timezone': instance.timezone,
    };
