import 'package:freezed_annotation/freezed_annotation.dart';

import '../util/json_converters.dart';
import 'common.dart';

part 'community.freezed.dart';
part 'community.g.dart';

// Community shapes follow the database schema (community_spaces, posts,
// challenges, challenge_entries) since API_CONTRACT.md reuses those columns.

@freezed
abstract class CommunitySpace with _$CommunitySpace {
  const factory CommunitySpace({
    required String id,
    required String name,
    String? description,
    @Default(false) bool announcementOnly,
  }) = _CommunitySpace;

  factory CommunitySpace.fromJson(Map<String, dynamic> json) => _$CommunitySpaceFromJson(json);
}

@freezed
abstract class Post with _$Post {
  const factory Post({
    required String id,
    String? communitySpaceId,
    PersonRef? author,
    @Default('text') String type,
    String? body,
    @Default(<String, dynamic>{}) Map<String, dynamic> meta,
    @Default(false) bool isPinned,
    @Default(0) int commentsCount,
    @Default(0) int reactionsCount,
    @Default(false) bool reacted,
    DateTime? createdAt,
  }) = _Post;

  factory Post.fromJson(Map<String, dynamic> json) => _$PostFromJson(json);
}

@freezed
abstract class PostComment with _$PostComment {
  const factory PostComment({required String id, PersonRef? author, required String body, DateTime? createdAt}) =
      _PostComment;

  factory PostComment.fromJson(Map<String, dynamic> json) => _$PostCommentFromJson(json);
}

@freezed
abstract class LeaderboardEntry with _$LeaderboardEntry {
  @JsonDouble()
  const factory LeaderboardEntry({
    required int rank,
    required PersonRef user,
    @Default(0) double score,
    String? team,
    @Default(false) bool isMe,
  }) = _LeaderboardEntry;

  factory LeaderboardEntry.fromJson(Map<String, dynamic> json) => _$LeaderboardEntryFromJson(json);
}

@freezed
abstract class Challenge with _$Challenge {
  @JsonDoubleN()
  const factory Challenge({
    required String id,
    required String name,
    String? description,
    required String metric,
    double? goal,
    required String startsOn,
    required String endsOn,
    String? badgeKey,
    @Default(false) bool joined,
    @Default(0) int participantsCount,
    double? myScore,
    int? myRank,
    @Default(<LeaderboardEntry>[]) List<LeaderboardEntry> leaderboard,
  }) = _Challenge;

  factory Challenge.fromJson(Map<String, dynamic> json) => _$ChallengeFromJson(json);
}
