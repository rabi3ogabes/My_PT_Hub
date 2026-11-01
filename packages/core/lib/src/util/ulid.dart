import 'dart:math';

/// Generates ULIDs (Universally Unique Lexicographically Sortable Identifiers).
///
/// 26 Crockford base32 characters: 48-bit millisecond timestamp + 80 bits of
/// randomness. Generation is monotonic within the same millisecond so ids
/// created in quick succession (e.g. a burst of logged sets) still sort in
/// creation order, which the server relies on for append-only logs.
class Ulid {
  Ulid({Random? random, int Function()? now})
      : _random = random ?? Random.secure(),
        _now = now ?? (() => DateTime.now().millisecondsSinceEpoch);

  static const _alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
  static final Ulid _shared = Ulid();

  final Random _random;
  final int Function() _now;
  int _lastTime = -1;
  final List<int> _lastRandom = List<int>.filled(16, 0);

  /// A new ULID from the shared generator.
  static String generate() => _shared.next();

  /// Whether [value] is a syntactically valid ULID.
  static bool isValid(String value) =>
      value.length == 26 && RegExp(r'^[0-7][0-9A-HJKMNP-TV-Z]{25}$').hasMatch(value);

  /// Extracts the creation time embedded in [ulid].
  static DateTime timestampOf(String ulid) {
    var ms = 0;
    for (var i = 0; i < 10; i++) {
      ms = ms * 32 + _alphabet.indexOf(ulid[i]);
    }
    return DateTime.fromMillisecondsSinceEpoch(ms, isUtc: true);
  }

  String next() {
    var time = _now();
    if (time <= _lastTime) {
      time = _lastTime;
      _increment();
    } else {
      _lastTime = time;
      for (var i = 0; i < 16; i++) {
        _lastRandom[i] = _random.nextInt(32);
      }
    }
    final buffer = StringBuffer();
    final timeChars = List<String>.filled(10, '0');
    var t = time;
    for (var i = 9; i >= 0; i--) {
      timeChars[i] = _alphabet[t % 32];
      t ~/= 32;
    }
    buffer.writeAll(timeChars);
    for (final r in _lastRandom) {
      buffer.write(_alphabet[r]);
    }
    return buffer.toString();
  }

  void _increment() {
    for (var i = 15; i >= 0; i--) {
      if (_lastRandom[i] < 31) {
        _lastRandom[i]++;
        return;
      }
      _lastRandom[i] = 0;
    }
  }
}
