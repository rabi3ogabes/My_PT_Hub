import 'package:audio_session/audio_session.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:just_audio/just_audio.dart';

enum Cue { tick, go, restOver, finish }

/// Short training cues (rest over, interval go, countdown ticks).
abstract class AudioCues {
  Future<void> play(Cue cue);
  Future<void> dispose();
}

class SilentCues implements AudioCues {
  const SilentCues();

  @override
  Future<void> play(Cue cue) async {}

  @override
  Future<void> dispose() async {}
}

/// just_audio implementation configured to MIX with other audio so Spotify /
/// Apple Music keep playing (ducked briefly) during workouts.
class JustAudioCues implements AudioCues {
  JustAudioCues({required this.enabled});

  final bool Function() enabled;
  final Map<Cue, AudioPlayer> _players = {};
  AudioSession? _session;
  bool _configured = false;

  static const _assets = {
    Cue.tick: 'assets/sounds/tick.wav',
    Cue.go: 'assets/sounds/go.wav',
    Cue.restOver: 'assets/sounds/rest_over.wav',
    Cue.finish: 'assets/sounds/finish.wav',
  };

  Future<void> _configure() async {
    if (_configured) return;
    _configured = true;
    final session = await AudioSession.instance;
    await session.configure(
      const AudioSessionConfiguration(
        avAudioSessionCategory: AVAudioSessionCategory.playback,
        avAudioSessionCategoryOptions: AVAudioSessionCategoryOptions.mixWithOthers,
        avAudioSessionMode: AVAudioSessionMode.defaultMode,
        avAudioSessionSetActiveOptions: AVAudioSessionSetActiveOptions.notifyOthersOnDeactivation,
        androidAudioAttributes: AndroidAudioAttributes(
          contentType: AndroidAudioContentType.sonification,
          usage: AndroidAudioUsage.assistanceSonification,
        ),
        // Transient "may duck": music lowers for the cue instead of pausing.
        androidAudioFocusGainType: AndroidAudioFocusGainType.gainTransientMayDuck,
        androidWillPauseWhenDucked: false,
      ),
    );
    _session = session;
  }

  Future<AudioPlayer> _player(Cue cue) async {
    final existing = _players[cue];
    if (existing != null) return existing;
    // We manage the session ourselves so just_audio never interrupts other apps.
    final player = AudioPlayer(handleInterruptions: false, handleAudioSessionActivation: false);
    await player.setAsset(_assets[cue]!);
    _players[cue] = player;
    return player;
  }

  @override
  Future<void> play(Cue cue) async {
    if (!enabled()) return;
    try {
      await _configure();
      await _session?.setActive(true);
      final player = await _player(cue);
      await player.seek(Duration.zero);
      await player.play();
      await _session?.setActive(false);
    } on Object catch (e) {
      debugPrint('[audio] cue failed: $e');
    }
  }

  @override
  Future<void> dispose() async {
    for (final p in _players.values) {
      await p.dispose();
    }
    _players.clear();
  }
}

/// Overridden in `main()` with [JustAudioCues]; silent in tests.
final audioCuesProvider = Provider<AudioCues>((ref) => const SilentCues());
