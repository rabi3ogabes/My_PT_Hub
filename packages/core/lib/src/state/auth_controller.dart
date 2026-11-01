import 'dart:async';
import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_timezone/flutter_timezone.dart';

import '../models/identity.dart';
import '../network/api_failure.dart';
import '../repositories/auth_repository.dart';
import '../services/analytics.dart';
import 'core_providers.dart';
import 'preferences_controllers.dart';

enum AuthStatus { unknown, signedOut, signedIn }

/// Authentication state shared by both apps.
@immutable
class AuthState {
  const AuthState({this.status = AuthStatus.unknown, this.user, this.tenants = const [], this.activeTenantId, this.offline = false});

  final AuthStatus status;
  final User? user;
  final List<Tenant> tenants;
  final String? activeTenantId;

  /// True when restored from cache because the network was unreachable.
  final bool offline;

  bool get isSignedIn => status == AuthStatus.signedIn;

  Tenant? get activeTenant {
    for (final t in tenants) {
      if (t.id == activeTenantId) return t;
    }
    return tenants.isEmpty ? null : tenants.first;
  }

  /// A client of a coaching business (vs. a standalone/self-guided user).
  bool get hasCoach => activeTenant != null && !activeTenant!.isPersonal;

  AuthState copyWith({AuthStatus? status, User? user, List<Tenant>? tenants, String? activeTenantId, bool? offline}) =>
      AuthState(
        status: status ?? this.status,
        user: user ?? this.user,
        tenants: tenants ?? this.tenants,
        activeTenantId: activeTenantId ?? this.activeTenantId,
        offline: offline ?? this.offline,
      );
}

/// Device details sent with auth calls and `POST /me/devices`.
final deviceInfoProvider = FutureProvider<DeviceInfo>((ref) async {
  final config = ref.watch(apiConfigProvider);
  String? timezone;
  try {
    timezone = (await FlutterTimezone.getLocalTimezone()).identifier;
  } on Object {
    timezone = null;
  }
  String? pushToken;
  try {
    pushToken = await ref.read(pushServiceProvider).token();
  } on Object {
    pushToken = null;
  }
  return DeviceInfo(
    deviceName: Platform.localHostname,
    platform: Platform.isIOS ? 'ios' : (Platform.isAndroid ? 'android' : Platform.operatingSystem),
    app: config.app,
    appVersion: config.appVersion,
    timezone: timezone,
    pushToken: pushToken,
  );
});

class AuthController extends Notifier<AuthState> {
  static const _meCacheKey = 'me';
  static const _ownerKey = 'auth.owner_user_id';

  @override
  AuthState build() => const AuthState();

  AuthRepository get _repo => ref.read(authRepositoryProvider);

  /// Restores the session at app start (works offline from cache).
  Future<void> restore() async {
    final tokens = await ref.read(tokenStoreProvider).read();
    if (tokens == null) {
      state = const AuthState(status: AuthStatus.signedOut);
      return;
    }
    try {
      final me = await _repo.me();
      await _applyMe(me);
    } on ApiFailure catch (failure) {
      if (failure.isUnauthenticated) {
        state = const AuthState(status: AuthStatus.signedOut);
        return;
      }
      final cached = await ref.read(databaseProvider).readCachedJson(_meCacheKey);
      if (cached is Map) {
        final me = MeResponse.fromJson(cached.cast<String, dynamic>());
        _setSignedIn(me, offline: true);
      } else {
        state = const AuthState(status: AuthStatus.signedIn, offline: true);
      }
    }
  }

  Future<LoginResult> signIn(String email, String password) async {
    final device = await _device();
    final result = await _repo.login(email: email, password: password, device: device);
    if (result is LoginSuccess) {
      await _applySession(result.session);
      unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.login(method: 'password')));
    }
    return result;
  }

  Future<void> completeTwoFactor(String challenge, String code) async {
    final session = await _repo.twoFactor(challenge: challenge, code: code, device: await _device());
    await _applySession(session);
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
    required AccountType accountType,
    String? inviteCode,
  }) async {
    final device = await _device();
    final session = await _repo.register(
      name: name,
      email: email,
      password: password,
      accountType: accountType,
      inviteCode: inviteCode,
      locale: ref.read(effectiveLocaleProvider).languageCode,
      timezone: device.timezone,
      device: device,
    );
    await _applySession(session);
    unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.signUp(method: 'password', accountType: accountType.name)));
  }

  Future<void> requestMagicLink(String email) => _repo.requestMagicLink(email);

  Future<void> verifyMagicLink(String token) async {
    final session = await _repo.verifyMagicLink(token, device: await _device());
    await _applySession(session);
    unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.login(method: 'magic_link')));
  }

  Future<void> acceptInvitation(String code) async {
    final me = await _repo.acceptInvitation(code);
    // Switch to the tenant that just invited us (the newest membership).
    final previous = state.tenants.map((t) => t.id).toSet();
    final joined = me.tenants.where((t) => !previous.contains(t.id)).firstOrNull;
    await _applyMe(me, preferTenantId: joined?.id);
    unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.inviteAccepted()));
  }

  Future<void> switchTenant(String tenantId) async {
    await ref.read(appPrefsProvider).setTenantId(tenantId);
    ref.read(requestContextProvider).tenantId = tenantId;
    state = state.copyWith(activeTenantId: tenantId);
  }

  Future<void> refreshMe() async => _applyMe(await _repo.me());

  Future<void> updateUser(Map<String, dynamic> fields) async {
    final user = await _repo.updateMe(fields);
    state = state.copyWith(user: user);
  }

  /// Explicit sign-out: revokes the token and wipes local data.
  Future<void> signOut() async {
    await ref.read(syncEngineProvider).stop();
    await _repo.logout();
    await ref.read(databaseProvider).clearAll();
    await ref.read(appPrefsProvider).clearAccount();
    ref.read(requestContextProvider).tenantId = null;
    state = const AuthState(status: AuthStatus.signedOut);
  }

  /// Called by the auth interceptor when the refresh token is rejected.
  /// Local data is kept so unsynced work survives re-authentication.
  void onSessionExpired() {
    if (state.status == AuthStatus.signedOut) return;
    state = const AuthState(status: AuthStatus.signedOut);
  }

  Future<void> _applySession(AuthSession session) async {
    await _applyMe(MeResponse(user: session.user, tenants: session.tenants));
  }

  Future<void> _applyMe(MeResponse me, {String? preferTenantId}) async {
    final db = ref.read(databaseProvider);
    final owner = await db.readMeta(_ownerKey);
    if (owner != null && owner != me.user.id) {
      // A different account signed in on this device: never mix data.
      await db.clearAll();
    }
    await db.writeMeta(_ownerKey, me.user.id);
    await db.cacheJson(_meCacheKey, me.toJson());
    if (preferTenantId != null) await ref.read(appPrefsProvider).setTenantId(preferTenantId);
    _setSignedIn(me);
    unawaited(ref.read(analyticsProvider).setUserId(me.user.id));
    unawaited(ref.read(crashReporterProvider).setUserId(me.user.id));
  }

  void _setSignedIn(MeResponse me, {bool offline = false}) {
    final saved = ref.read(appPrefsProvider).tenantId;
    final ids = me.tenants.map((t) => t.id).toSet();
    final active = (saved != null && ids.contains(saved))
        ? saved
        : (me.user.currentTenantId != null && ids.contains(me.user.currentTenantId))
            ? me.user.currentTenantId
            : me.tenants.firstOrNull?.id;
    ref.read(requestContextProvider).tenantId = active;
    state = AuthState(status: AuthStatus.signedIn, user: me.user, tenants: me.tenants, activeTenantId: active, offline: offline);
  }

  Future<DeviceInfo> _device() async {
    try {
      return await ref.read(deviceInfoProvider.future);
    } on Object {
      return DeviceInfo(app: ref.read(apiConfigProvider).app);
    }
  }
}

final authControllerProvider = NotifierProvider<AuthController, AuthState>(AuthController.new);
