import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';

/// Abstraction over network reachability so the sync engine is testable.
abstract class ConnectivitySource {
  Future<bool> isOnline();

  /// Emits whenever reachability changes.
  Stream<bool> get onlineChanges;
}

class PlusConnectivitySource implements ConnectivitySource {
  PlusConnectivitySource([Connectivity? connectivity]) : _connectivity = connectivity ?? Connectivity();

  final Connectivity _connectivity;

  static bool _online(List<ConnectivityResult> results) =>
      results.any((r) => r != ConnectivityResult.none);

  @override
  Future<bool> isOnline() async => _online(await _connectivity.checkConnectivity());

  @override
  Stream<bool> get onlineChanges => _connectivity.onConnectivityChanged.map(_online).distinct();
}

/// Manually driven connectivity (tests, demo mode).
class ManualConnectivitySource implements ConnectivitySource {
  ManualConnectivitySource({bool online = true}) : _online = online; // ignore: prefer_initializing_formals

  bool _online;
  final _controller = StreamController<bool>.broadcast();

  set online(bool value) {
    if (value == _online) return;
    _online = value;
    _controller.add(value);
  }

  @override
  Future<bool> isOnline() async => _online;

  @override
  Stream<bool> get onlineChanges => _controller.stream;

  Future<void> dispose() => _controller.close();
}
