import 'dart:async';

import 'package:app_links/app_links.dart';
import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../features/notifications/notifications_providers.dart';
import 'env.dart';
import 'router.dart';

/// Session-scoped side effects: starts/stops the sync engine with the
/// session, registers the device for push, routes deep links and
/// notification taps, and shows foreground pushes as local notifications.
class AppLifecycleScope extends ConsumerStatefulWidget {
  const AppLifecycleScope({super.key, required this.child});

  final Widget child;

  @override
  ConsumerState<AppLifecycleScope> createState() => _AppLifecycleScopeState();
}

class _AppLifecycleScopeState extends ConsumerState<AppLifecycleScope> {
  final _subs = <StreamSubscription<Object?>>[];
  final _appLinks = AppLinks();

  @override
  void initState() {
    super.initState();
    final push = ref.read(pushServiceProvider);
    _subs
      ..add(push.onOpened.listen((m) => _openLink(m.link)))
      ..add(push.onMessage.listen(_onForegroundPush))
      ..add(push.onTokenRefresh.listen((_) => _registerDevice()))
      ..add(_appLinks.uriLinkStream.listen(_onUri, onError: (Object _) {}));
    unawaited(_initialLinks());
  }

  Future<void> _initialLinks() async {
    try {
      final uri = await _appLinks.getInitialLink();
      if (uri != null) _onUri(uri);
    } on Object {
      // No initial link on this platform/launch.
    }
    final initialPush = await ref.read(pushServiceProvider).initialMessage();
    if (initialPush != null) _openLink(initialPush.link);
  }

  void _onUri(Uri uri) {
    final link = DeepLinkParser.parse(uri, hosts: AppEnv.linkHosts);
    final router = ref.read(routerProvider);
    switch (link) {
      case InviteLink(:final code):
        router.go('/invite/${Uri.encodeComponent(code)}');
      case MagicLink(:final token):
        router.go('/auth/magic/${Uri.encodeComponent(token)}');
      case PathLink(:final path):
        router.go(path);
      case null:
        break;
    }
  }

  void _openLink(String? link) {
    if (link == null || link.isEmpty) return;
    _onUri(Uri.parse(link));
  }

  Future<void> _onForegroundPush(PushMessage message) async {
    ref.invalidate(notificationsUnreadProvider);
    final title = message.title;
    if (title == null) return;
    await ref.read(localNotifierProvider).show(
          id: message.hashCode & 0x7fffffff,
          title: title,
          body: message.body,
          payload: message.link,
        );
  }

  Future<void> _registerDevice() async {
    try {
      ref.invalidate(deviceInfoProvider);
      final device = await ref.read(deviceInfoProvider.future);
      await ref.read(authRepositoryProvider).registerDevice(device);
    } on Object {
      // Registration is retried on the next launch / token refresh.
    }
  }

  Future<void> _onSignedIn() async {
    ref.read(syncEngineProvider).start();
    unawaited(ref.read(messagingRepositoryProvider).flushUploads());
    final granted = await ref.read(pushServiceProvider).requestPermission();
    if (granted || ref.read(servicesProvider).firebaseEnabled) await _registerDevice();
  }

  @override
  void dispose() {
    for (final s in _subs) {
      s.cancel();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    ref.listen(authControllerProvider.select((s) => s.status), (previous, next) {
      if (next == AuthStatus.signedIn && previous != AuthStatus.signedIn) {
        unawaited(_onSignedIn());
      } else if (next == AuthStatus.signedOut && previous == AuthStatus.signedIn) {
        unawaited(ref.read(syncEngineProvider).stop());
      }
    });
    ref.listen(onlineProvider, (previous, next) {
      if (next.value == true && previous?.value == false) {
        unawaited(ref.read(messagingRepositoryProvider).flushUploads());
      }
    });
    return widget.child;
  }
}
