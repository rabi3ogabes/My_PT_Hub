import 'package:aurum_core/aurum_core.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final myProgramProvider = FutureProvider.autoDispose<Fetched<MyProgram?>>(
  (ref) => ref.watch(workoutRepositoryProvider).myProgram(),
);

final workoutProvider = FutureProvider.autoDispose.family<Fetched<Workout>, String>(
  (ref, id) => ref.watch(workoutRepositoryProvider).workout(id),
);

final recordsProvider = FutureProvider.autoDispose<Fetched<List<PersonalRecord>>>(
  (ref) => ref.watch(workoutRepositoryProvider).records(),
);

final exerciseHistoryProvider = FutureProvider.autoDispose.family<Fetched<ExerciseHistory>, String>(
  (ref, id) => ref.watch(workoutRepositoryProvider).history(id),
);

/// Local completed sessions not yet confirmed by the server.
final localCompletedSessionsProvider = FutureProvider.autoDispose<List<LocalSession>>(
  (ref) => ref.watch(sessionStoreProvider).completedSessions(),
);

/// Session history with cursor pagination.
class SessionHistory {
  const SessionHistory({this.items = const [], this.nextCursor, this.loadingMore = false});

  final List<WorkoutSession> items;
  final String? nextCursor;
  final bool loadingMore;

  bool get hasMore => nextCursor != null;
}

class SessionHistoryController extends AsyncNotifier<SessionHistory> {
  @override
  Future<SessionHistory> build() async {
    final page = await ref.read(workoutRepositoryProvider).sessions();
    return SessionHistory(items: page.items, nextCursor: page.nextCursor);
  }

  Future<void> loadMore() async {
    final current = state.value;
    if (current == null || !current.hasMore || current.loadingMore) return;
    state = AsyncData(SessionHistory(items: current.items, nextCursor: current.nextCursor, loadingMore: true));
    try {
      final page = await ref.read(workoutRepositoryProvider).sessions(cursor: current.nextCursor);
      state = AsyncData(SessionHistory(items: [...current.items, ...page.items], nextCursor: page.nextCursor));
    } on ApiFailure {
      state = AsyncData(SessionHistory(items: current.items, nextCursor: current.nextCursor));
    }
  }
}

final sessionHistoryProvider =
    AsyncNotifierProvider.autoDispose<SessionHistoryController, SessionHistory>(SessionHistoryController.new);

/// Calendar date of a program day.
DateTime programDayDate(String startDate, int weekNumber, int dayNumber) =>
    parseApiDate(startDate).add(Duration(days: (weekNumber - 1) * 7 + (dayNumber - 1)));
