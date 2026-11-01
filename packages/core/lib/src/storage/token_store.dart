import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../models/identity.dart';

/// Persists auth tokens. Production uses the platform keychain/keystore.
abstract class TokenStore {
  Future<Tokens?> read();
  Future<void> write(Tokens tokens);
  Future<void> clear();
}

/// Keychain (iOS) / EncryptedSharedPreferences-backed keystore (Android).
class SecureTokenStore implements TokenStore {
  SecureTokenStore({FlutterSecureStorage? storage, this.key = 'aurum.tokens'})
      : _storage = storage ?? const FlutterSecureStorage();

  final FlutterSecureStorage _storage;
  final String key;
  Tokens? _cache;
  bool _loaded = false;

  @override
  Future<Tokens?> read() async {
    if (_loaded) return _cache;
    final raw = await _storage.read(key: key);
    _cache = raw == null ? null : Tokens.fromJson(jsonDecode(raw) as Map<String, dynamic>);
    _loaded = true;
    return _cache;
  }

  @override
  Future<void> write(Tokens tokens) async {
    _cache = tokens;
    _loaded = true;
    await _storage.write(key: key, value: jsonEncode(tokens.toJson()));
  }

  @override
  Future<void> clear() async {
    _cache = null;
    _loaded = true;
    await _storage.delete(key: key);
  }
}

/// In-memory store (tests and ephemeral sessions).
class MemoryTokenStore implements TokenStore {
  MemoryTokenStore([this._tokens]);

  Tokens? _tokens;

  @override
  Future<Tokens?> read() async => _tokens;

  @override
  Future<void> write(Tokens tokens) async => _tokens = tokens;

  @override
  Future<void> clear() async => _tokens = null;
}
