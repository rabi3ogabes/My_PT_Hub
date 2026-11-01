import '../models/identity.dart';
import '../network/api_client.dart';
import '../network/api_failure.dart';
import '../storage/token_store.dart';

/// Result of `POST /auth/login`: either a session or a 2FA challenge.
sealed class LoginResult {
  const LoginResult();
}

class LoginSuccess extends LoginResult {
  const LoginSuccess(this.session);

  final AuthSession session;
}

class LoginTwoFactorRequired extends LoginResult {
  const LoginTwoFactorRequired(this.challenge);

  final String challenge;
}

/// Account types accepted by `POST /auth/register`.
enum AccountType { coach, client, standalone }

/// Authentication and account endpoints.
class AuthRepository {
  AuthRepository(this.api, this.tokens);

  final ApiClient api;
  final TokenStore tokens;

  Future<LoginResult> login({required String email, required String password, DeviceInfo? device}) async {
    final envelope = await api.post(
      '/auth/login',
      data: {'email': email, 'password': password, ..._device(device)},
      options: ApiClient.public,
    );
    final data = envelope.dataMap;
    if (data['two_factor_required'] == true) return LoginTwoFactorRequired(data['challenge'].toString());
    return LoginSuccess(await _persist(data));
  }

  Future<AuthSession> twoFactor({required String challenge, required String code, DeviceInfo? device}) async {
    final envelope = await api.post(
      '/auth/two-factor',
      data: {'challenge': challenge, 'code': code, ..._device(device)},
      options: ApiClient.public,
    );
    return _persist(envelope.dataMap);
  }

  Future<AuthSession> register({
    required String name,
    required String email,
    required String password,
    required AccountType accountType,
    String? inviteCode,
    String? locale,
    String? timezone,
    String? country,
    DeviceInfo? device,
  }) async {
    final envelope = await api.post(
      '/auth/register',
      data: compactJson({
        'name': name,
        'email': email,
        'password': password,
        'account_type': accountType.name,
        'invite_code': inviteCode,
        'locale': locale,
        'timezone': timezone,
        'country': country,
        'terms': true,
        ..._device(device),
      }),
      options: ApiClient.public,
    );
    return _persist(envelope.dataMap);
  }

  Future<void> requestMagicLink(String email) =>
      api.post('/auth/magic-link', data: {'email': email}, options: ApiClient.public);

  Future<AuthSession> verifyMagicLink(String token, {DeviceInfo? device}) async {
    final envelope = await api.post(
      '/auth/magic-link/verify',
      data: {'token': token, ..._device(device)},
      options: ApiClient.public,
    );
    return _persist(envelope.dataMap);
  }

  Future<AuthSession> social({
    required String provider,
    required String token,
    String? name,
    AccountType? accountType,
    String? inviteCode,
  }) async {
    final envelope = await api.post(
      '/auth/social/$provider',
      data: compactJson({'token': token, 'name': name, 'account_type': accountType?.name, 'invite_code': inviteCode}),
      options: ApiClient.public,
    );
    return _persist(envelope.dataMap);
  }

  Future<void> forgotPassword(String email) =>
      api.post('/auth/forgot-password', data: {'email': email}, options: ApiClient.public);

  Future<InvitationPreview> invitation(String code) async {
    final envelope = await api.get('/invitations/${Uri.encodeComponent(code)}', options: ApiClient.public);
    return InvitationPreview.fromJson(envelope.dataMap);
  }

  Future<MeResponse> acceptInvitation(String code) async {
    final envelope = await api.post('/invitations/accept', data: {'code': code});
    return MeResponse.fromJson(envelope.dataMap);
  }

  Future<MeResponse> me() async => MeResponse.fromJson((await api.get('/me')).dataMap);

  Future<User> updateMe(Map<String, dynamic> fields) async {
    final envelope = await api.patch('/me', data: fields);
    final data = envelope.dataMap;
    return User.fromJson(data.containsKey('user') ? (data['user'] as Map).cast<String, dynamic>() : data);
  }

  Future<void> registerDevice(DeviceInfo device) => api.post('/me/devices', data: compactJson(device.toJson()));

  Future<MeResponse> switchTenant(String tenantId) async {
    final envelope = await api.post('/tenants/switch', data: {'tenant_id': tenantId});
    return MeResponse.fromJson(envelope.dataMap);
  }

  Future<void> requestExport() => api.post('/me/export');

  Future<void> deleteAccount({String? password}) => api.delete('/me', data: compactJson({'password': password}));

  /// Revokes the token server-side (best effort) and clears local tokens.
  Future<void> logout() async {
    try {
      await api.post('/auth/logout');
    } on ApiFailure {
      // Signing out must always succeed locally, even offline.
    }
    await tokens.clear();
  }

  Future<AuthSession> _persist(Map<String, dynamic> data) async {
    final session = AuthSession.fromJson(data);
    await tokens.write(session.tokens);
    return session;
  }

  static Map<String, dynamic> _device(DeviceInfo? device) => device == null ? const {} : compactJson(device.toJson());
}
