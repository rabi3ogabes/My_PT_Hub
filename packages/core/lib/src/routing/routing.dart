import 'package:flutter/foundation.dart';
import 'package:go_router/go_router.dart';

import '../state/auth_controller.dart';

/// Deep links understood by the apps.
sealed class DeepLink {
  const DeepLink();
}

/// `https://{domain}/invite/{code}` or `aurum://invite/{code}`.
class InviteLink extends DeepLink {
  const InviteLink(this.code);

  final String code;
}

/// `https://{domain}/auth/magic/{token}` or `aurum://auth/magic/{token}`.
class MagicLink extends DeepLink {
  const MagicLink(this.token);

  final String token;
}

/// Any other in-app path (e.g. push notification `link` = `/messages/123`).
class PathLink extends DeepLink {
  const PathLink(this.path);

  final String path;
}

/// Parses universal links and the custom scheme.
abstract final class DeepLinkParser {
  static const scheme = 'aurum';

  static DeepLink? parse(Uri uri, {Set<String> hosts = const {}}) {
    final isCustom = uri.scheme == scheme;
    final isWeb = (uri.scheme == 'https' || uri.scheme == 'http') &&
        (hosts.isEmpty || hosts.contains(uri.host) || hosts.any((h) => uri.host.endsWith('.$h')));
    if (!isCustom && !isWeb && uri.scheme.isNotEmpty) return null;

    // aurum://invite/ABC → host "invite", path "/ABC".
    final segments = [
      if (isCustom && uri.host.isNotEmpty) uri.host,
      ...uri.pathSegments.where((s) => s.isNotEmpty),
    ];
    if (segments.length >= 2 && segments[0] == 'invite') return InviteLink(segments[1]);
    if (segments.length >= 3 && segments[0] == 'auth' && segments[1] == 'magic') return MagicLink(segments[2]);
    if (segments.isEmpty) return null;
    final query = uri.hasQuery ? '?${uri.query}' : '';
    return PathLink('/${segments.join('/')}$query');
  }
}

/// Bridges a Riverpod-driven value into go_router's `refreshListenable`.
class RouterRefresh extends ChangeNotifier {
  void refresh() => notifyListeners();
}

/// Auth guard for go_router.
///
/// * While the session is being restored, stay on [splashPath].
/// * Signed-out users may only visit [publicPaths] (prefix match) — any other
///   target is remembered in `?from=` and they land on [signInPath].
/// * Signed-in users are moved off auth screens to `from` or [homePath].
String? authRedirect(
  AuthStatus status,
  GoRouterState state, {
  required String splashPath,
  required String signInPath,
  required String homePath,
  required List<String> publicPaths,
  List<String> signedInAllowedPublicPaths = const [],
}) {
  final location = state.matchedLocation;
  bool isPublic(String path) => publicPaths.any((p) => path == p || path.startsWith('$p/'));

  if (status == AuthStatus.unknown) {
    return location == splashPath ? null : '$splashPath?from=${Uri.encodeComponent(state.uri.toString())}';
  }
  if (status == AuthStatus.signedOut) {
    if (isPublic(location)) return null;
    if (location == splashPath) {
      final from = state.uri.queryParameters['from'];
      if (from != null && isPublic(Uri.parse(from).path)) return from;
      return signInPath;
    }
    return '$signInPath?from=${Uri.encodeComponent(state.uri.toString())}';
  }
  // Signed in.
  final allowed = signedInAllowedPublicPaths.any((p) => location == p || location.startsWith('$p/'));
  if (location == splashPath || (isPublic(location) && !allowed)) {
    final from = state.uri.queryParameters['from'];
    if (from != null && from.startsWith('/') && !isPublic(Uri.parse(from).path)) return from;
    return homePath;
  }
  return null;
}
