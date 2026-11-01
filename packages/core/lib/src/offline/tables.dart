import 'package:drift/drift.dart';

/// Outbox of local mutations awaiting `POST /sync/push`.
///
/// `id` is the mutation ULID (also the idempotency key the server
/// de-duplicates on); ULIDs sort by creation time so the queue replays in
/// order.
@DataClassName('SyncQueueEntry')
class SyncQueue extends Table {
  TextColumn get id => text()();
  TextColumn get entity => text()();
  TextColumn get op => text()();
  TextColumn get payload => text()();
  IntColumn get attempts => integer().withDefault(const Constant(0))();

  /// `pending` | `rejected`. Applied/duplicate rows are deleted.
  TextColumn get status => text().withDefault(const Constant('pending'))();
  TextColumn get lastError => text().nullable()();
  DateTimeColumn get createdAt => dateTime()();
  DateTimeColumn get nextAttemptAt => dateTime().nullable()();

  @override
  Set<Column<Object>> get primaryKey => {id};
}

/// Key/value metadata (pull watermark, last sync time …).
@DataClassName('SyncMetaEntry')
class SyncMeta extends Table {
  TextColumn get key => text()();
  TextColumn get value => text()();

  @override
  Set<Column<Object>> get primaryKey => {key};
}

/// Generic cache of GET responses for offline reads (`today:2026-11-01`,
/// `program`, `nutrition:2026-11-01`, …).
@DataClassName('CachedResponse')
class CachedResponses extends Table {
  TextColumn get key => text()();
  TextColumn get json => text()();
  DateTimeColumn get updatedAt => dateTime()();

  @override
  Set<Column<Object>> get primaryKey => {key};
}

/// Entities received from `GET /sync/pull`, keyed by collection + id.
@DataClassName('CachedEntity')
class CachedEntities extends Table {
  TextColumn get collection => text()();
  TextColumn get id => text()();
  TextColumn get json => text()();
  DateTimeColumn get updatedAt => dateTime()();

  @override
  Set<Column<Object>> get primaryKey => {collection, id};
}

/// Workout sessions created on this device (the logger's source of truth).
@DataClassName('LocalSession')
class LocalSessions extends Table {
  TextColumn get id => text()();
  TextColumn get workoutId => text().nullable()();
  TextColumn get programDayId => text().nullable()();
  TextColumn get programAssignmentId => text().nullable()();
  TextColumn get name => text().nullable()();

  /// `in_progress` | `completed` | `abandoned`.
  TextColumn get status => text().withDefault(const Constant('in_progress'))();
  DateTimeColumn get startedAt => dateTime()();
  DateTimeColumn get endedAt => dateTime().nullable()();
  IntColumn get perceivedEffort => integer().nullable()();
  TextColumn get mood => text().nullable()();
  TextColumn get notes => text().nullable()();

  /// Snapshot of the workout (with last performance) used to rebuild the
  /// logger offline and after process death.
  TextColumn get workoutJson => text().nullable()();

  /// Logger UI state: cursor, rest timer end timestamp, block timer, AMRAP
  /// rounds, swaps, notes.
  TextColumn get stateJson => text().nullable()();

  /// Server response after completion (PRs, volume) once available.
  TextColumn get resultJson => text().nullable()();
  DateTimeColumn get updatedAt => dateTime()();

  @override
  Set<Column<Object>> get primaryKey => {id};
}

/// Sets logged on this device.
@DataClassName('LocalSessionSet')
class LocalSessionSets extends Table {
  TextColumn get id => text()();
  TextColumn get sessionId => text().references(LocalSessions, #id, onDelete: KeyAction.cascade)();
  TextColumn get exerciseId => text()();
  TextColumn get workoutExerciseId => text().nullable()();
  IntColumn get setNumber => integer()();
  TextColumn get setType => text().withDefault(const Constant('working'))();
  IntColumn get reps => integer().nullable()();
  RealColumn get weightKg => real().nullable()();
  IntColumn get durationSeconds => integer().nullable()();
  RealColumn get distanceM => real().nullable()();
  RealColumn get rpe => real().nullable()();
  BoolColumn get isCompleted => boolean().withDefault(const Constant(false))();
  DateTimeColumn get completedAt => dateTime().nullable()();
  DateTimeColumn get updatedAt => dateTime()();

  @override
  Set<Column<Object>> get primaryKey => {id};
}

/// Media messages composed offline (voice notes, images) awaiting upload.
@DataClassName('PendingUpload')
class PendingUploads extends Table {
  TextColumn get id => text()();
  TextColumn get conversationId => text()();
  TextColumn get type => text()();
  TextColumn get body => text().nullable()();
  TextColumn get replyToId => text().nullable()();
  TextColumn get filePath => text()();
  IntColumn get durationMs => integer().nullable()();
  IntColumn get attempts => integer().withDefault(const Constant(0))();
  TextColumn get lastError => text().nullable()();
  DateTimeColumn get createdAt => dateTime()();

  @override
  Set<Column<Object>> get primaryKey => {id};
}
