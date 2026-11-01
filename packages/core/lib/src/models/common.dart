import 'package:freezed_annotation/freezed_annotation.dart';

import '../util/money_format.dart';

part 'common.freezed.dart';
part 'common.g.dart';

/// `{amount_minor, currency, formatted}`.
@freezed
abstract class Money with _$Money {
  const Money._();

  const factory Money({required int amountMinor, required String currency, String? formatted}) = _Money;

  factory Money.fromJson(Map<String, dynamic> json) => _$MoneyFromJson(json);

  /// Server-formatted string when present, otherwise a locale format.
  String display([String? locale]) => formatted ?? MoneyFormat.format(amountMinor, currency, locale: locale);
}

/// Generic `{id, name}` reference used across the contract.
@freezed
abstract class IdName with _$IdName {
  const factory IdName({required String id, required String name}) = _IdName;

  factory IdName.fromJson(Map<String, dynamic> json) => _$IdNameFromJson(json);
}

/// `{id, name, avatar_url}` person reference.
@freezed
abstract class PersonRef with _$PersonRef {
  const factory PersonRef({required String id, required String name, String? avatarUrl}) = _PersonRef;

  factory PersonRef.fromJson(Map<String, dynamic> json) => _$PersonRefFromJson(json);
}

/// A page of a cursor-paginated list (`meta: {per_page, next_cursor, prev_cursor}`).
class CursorPage<T> {
  const CursorPage({required this.items, this.nextCursor, this.prevCursor, this.perPage, this.meta = const {}});

  final List<T> items;
  final String? nextCursor;
  final String? prevCursor;
  final int? perPage;
  final Map<String, dynamic> meta;

  bool get hasMore => nextCursor != null;

  static CursorPage<T> fromEnvelope<T>(
    Object? data,
    Map<String, dynamic> meta,
    T Function(Map<String, dynamic>) decode,
  ) {
    final list = (data as List<dynamic>? ?? const []).cast<Map<String, dynamic>>().map(decode).toList();
    return CursorPage<T>(
      items: list,
      nextCursor: meta['next_cursor'] as String?,
      prevCursor: meta['prev_cursor'] as String?,
      perPage: (meta['per_page'] as num?)?.toInt(),
      meta: meta,
    );
  }
}
