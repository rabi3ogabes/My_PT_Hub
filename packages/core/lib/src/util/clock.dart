/// Injectable wall clock so timers and sync logic are testable.
///
/// Timers in the app are always computed from wall-clock timestamps (never by
/// counting ticks) so they survive backgrounding and process death.
abstract class Clock {
  const Clock();
  DateTime now();
}

class SystemClock extends Clock {
  const SystemClock();

  @override
  DateTime now() => DateTime.now();
}

/// A controllable clock for tests.
class FakeClock extends Clock {
  FakeClock(this._now);

  DateTime _now;

  @override
  DateTime now() => _now;

  void advance(Duration by) => _now = _now.add(by);

  void set(DateTime value) => _now = value;
}
