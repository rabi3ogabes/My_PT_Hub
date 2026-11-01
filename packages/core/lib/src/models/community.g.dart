// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'community.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_CommunitySpace _$CommunitySpaceFromJson(Map<String, dynamic> json) =>
    _CommunitySpace(
      id: json['id'] as String,
      name: json['name'] as String,
      description: json['description'] as String?,
      announcementOnly: json['announcement_only'] as bool? ?? false,
    );

Map<String, dynamic> _$CommunitySpaceToJson(_CommunitySpace instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'description': instance.description,
      'announcement_only': instance.announcementOnly,
    };

_Post _$PostFromJson(Map<String, dynamic> json) => _Post(
  id: json['id'] as String,
  communitySpaceId: json['community_space_id'] as String?,
  author: json['author'] == null
      ? null
      : PersonRef.fromJson(json['author'] as Map<String, dynamic>),
  type: json['type'] as String? ?? 'text',
  body: json['body'] as String?,
  meta: json['meta'] as Map<String, dynamic>? ?? const <String, dynamic>{},
  isPinned: json['is_pinned'] as bool? ?? false,
  commentsCount: (json['comments_count'] as num?)?.toInt() ?? 0,
  reactionsCount: (json['reactions_count'] as num?)?.toInt() ?? 0,
  reacted: json['reacted'] as bool? ?? false,
  createdAt: json['created_at'] == null
      ? null
      : DateTime.parse(json['created_at'] as String),
);

Map<String, dynamic> _$PostToJson(_Post instance) => <String, dynamic>{
  'id': instance.id,
  'community_space_id': instance.communitySpaceId,
  'author': instance.author?.toJson(),
  'type': instance.type,
  'body': instance.body,
  'meta': instance.meta,
  'is_pinned': instance.isPinned,
  'comments_count': instance.commentsCount,
  'reactions_count': instance.reactionsCount,
  'reacted': instance.reacted,
  'created_at': instance.createdAt?.toIso8601String(),
};

_PostComment _$PostCommentFromJson(Map<String, dynamic> json) => _PostComment(
  id: json['id'] as String,
  author: json['author'] == null
      ? null
      : PersonRef.fromJson(json['author'] as Map<String, dynamic>),
  body: json['body'] as String,
  createdAt: json['created_at'] == null
      ? null
      : DateTime.parse(json['created_at'] as String),
);

Map<String, dynamic> _$PostCommentToJson(_PostComment instance) =>
    <String, dynamic>{
      'id': instance.id,
      'author': instance.author?.toJson(),
      'body': instance.body,
      'created_at': instance.createdAt?.toIso8601String(),
    };

_LeaderboardEntry _$LeaderboardEntryFromJson(Map<String, dynamic> json) =>
    _LeaderboardEntry(
      rank: (json['rank'] as num).toInt(),
      user: PersonRef.fromJson(json['user'] as Map<String, dynamic>),
      score: json['score'] == null
          ? 0
          : const JsonDouble().fromJson(json['score']),
      team: json['team'] as String?,
      isMe: json['is_me'] as bool? ?? false,
    );

Map<String, dynamic> _$LeaderboardEntryToJson(_LeaderboardEntry instance) =>
    <String, dynamic>{
      'rank': instance.rank,
      'user': instance.user.toJson(),
      'score': const JsonDouble().toJson(instance.score),
      'team': instance.team,
      'is_me': instance.isMe,
    };

_Challenge _$ChallengeFromJson(Map<String, dynamic> json) => _Challenge(
  id: json['id'] as String,
  name: json['name'] as String,
  description: json['description'] as String?,
  metric: json['metric'] as String,
  goal: const JsonDoubleN().fromJson(json['goal']),
  startsOn: json['starts_on'] as String,
  endsOn: json['ends_on'] as String,
  badgeKey: json['badge_key'] as String?,
  joined: json['joined'] as bool? ?? false,
  participantsCount: (json['participants_count'] as num?)?.toInt() ?? 0,
  myScore: const JsonDoubleN().fromJson(json['my_score']),
  myRank: (json['my_rank'] as num?)?.toInt(),
  leaderboard:
      (json['leaderboard'] as List<dynamic>?)
          ?.map((e) => LeaderboardEntry.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <LeaderboardEntry>[],
);

Map<String, dynamic> _$ChallengeToJson(_Challenge instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'description': instance.description,
      'metric': instance.metric,
      'goal': const JsonDoubleN().toJson(instance.goal),
      'starts_on': instance.startsOn,
      'ends_on': instance.endsOn,
      'badge_key': instance.badgeKey,
      'joined': instance.joined,
      'participants_count': instance.participantsCount,
      'my_score': const JsonDoubleN().toJson(instance.myScore),
      'my_rank': instance.myRank,
      'leaderboard': instance.leaderboard.map((e) => e.toJson()).toList(),
    };
