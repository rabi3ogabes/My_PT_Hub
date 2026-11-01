import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:local_auth/local_auth.dart';

import '../shared/l10n_ext.dart';

/// Biometric unlock via the platform (Face ID / Touch ID / fingerprint).
abstract class BiometricAuth {
  Future<bool> isAvailable();
  Future<bool> authenticate(String reason);
}

class LocalBiometricAuth implements BiometricAuth {
  final _auth = LocalAuthentication();

  @override
  Future<bool> isAvailable() async {
    try {
      return await _auth.isDeviceSupported();
    } on Object {
      return false;
    }
  }

  @override
  Future<bool> authenticate(String reason) async {
    try {
      return await _auth.authenticate(localizedReason: reason, persistAcrossBackgrounding: true);
    } on Object {
      return false;
    }
  }
}

final biometricAuthProvider = Provider<BiometricAuth>((ref) => LocalBiometricAuth());

class BiometricLockController extends Notifier<bool> {
  @override
  bool build() {
    final prefs = ref.read(appPrefsProvider);
    return prefs.biometricUnlock;
  }

  bool get enabled => ref.read(appPrefsProvider).biometricUnlock;

  void lock() {
    if (enabled) state = true;
  }

  void unlock() => state = false;
}

/// True while the app is locked behind biometrics.
final biometricLockedProvider = NotifierProvider<BiometricLockController, bool>(BiometricLockController.new);

/// Covers the app with a lock screen on launch and after it has been in the
/// background for more than [grace], when the user enabled biometric unlock.
class BiometricLockGate extends ConsumerStatefulWidget {
  const BiometricLockGate({super.key, required this.child, this.grace = const Duration(minutes: 2)});

  final Widget child;
  final Duration grace;

  @override
  ConsumerState<BiometricLockGate> createState() => _BiometricLockGateState();
}

class _BiometricLockGateState extends ConsumerState<BiometricLockGate> {
  late final AppLifecycleListener _listener;
  DateTime? _backgroundedAt;
  bool _prompting = false;

  @override
  void initState() {
    super.initState();
    _listener = AppLifecycleListener(
      onHide: () => _backgroundedAt = DateTime.now(),
      onShow: () {
        final at = _backgroundedAt;
        if (at != null && DateTime.now().difference(at) > widget.grace) {
          ref.read(biometricLockedProvider.notifier).lock();
        }
      },
    );
  }

  @override
  void dispose() {
    _listener.dispose();
    super.dispose();
  }

  Future<void> _unlock() async {
    if (_prompting) return;
    _prompting = true;
    final ok = await ref.read(biometricAuthProvider).authenticate(context.l10n.biometricReason);
    _prompting = false;
    if (ok) ref.read(biometricLockedProvider.notifier).unlock();
  }

  @override
  Widget build(BuildContext context) {
    final locked = ref.watch(biometricLockedProvider) &&
        ref.watch(authControllerProvider.select((s) => s.status == AuthStatus.signedIn));
    if (locked && !_prompting) {
      WidgetsBinding.instance.addPostFrameCallback((_) => unawaited(_unlock()));
    }
    return Stack(
      children: [
        widget.child,
        if (locked)
          Positioned.fill(
            child: _LockScreen(onUnlock: _unlock),
          ),
      ],
    );
  }
}

class _LockScreen extends StatelessWidget {
  const _LockScreen({required this.onUnlock});

  final VoidCallback onUnlock;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Material(
      color: au.background,
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsetsDirectional.all(AuSpacing.s8),
          child: Column(
            children: [
              const Spacer(),
              Container(
                width: 84,
                height: 84,
                decoration: BoxDecoration(shape: BoxShape.circle, gradient: au.goldGradient, boxShadow: au.glow),
                child: Icon(Icons.lock_outline_rounded, size: 38, color: au.onAccent),
              ),
              const SizedBox(height: AuSpacing.s6),
              Text(context.l10n.lockedTitle, style: au.text.h2, textAlign: TextAlign.center),
              const SizedBox(height: AuSpacing.s2),
              Text(context.l10n.lockedMessage, style: au.text.body.copyWith(color: au.textSecondary), textAlign: TextAlign.center),
              const Spacer(),
              AuButton(label: context.l10n.unlock, icon: Icons.fingerprint_rounded, onPressed: onUnlock),
            ],
          ),
        ),
      ),
    );
  }
}
