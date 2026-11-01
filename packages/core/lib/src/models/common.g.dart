// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'common.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_Money _$MoneyFromJson(Map<String, dynamic> json) => _Money(
  amountMinor: (json['amount_minor'] as num).toInt(),
  currency: json['currency'] as String,
  formatted: json['formatted'] as String?,
);

Map<String, dynamic> _$MoneyToJson(_Money instance) => <String, dynamic>{
  'amount_minor': instance.amountMinor,
  'currency': instance.currency,
  'formatted': instance.formatted,
};

_IdName _$IdNameFromJson(Map<String, dynamic> json) =>
    _IdName(id: json['id'] as String, name: json['name'] as String);

Map<String, dynamic> _$IdNameToJson(_IdName instance) => <String, dynamic>{
  'id': instance.id,
  'name': instance.name,
};

_PersonRef _$PersonRefFromJson(Map<String, dynamic> json) => _PersonRef(
  id: json['id'] as String,
  name: json['name'] as String,
  avatarUrl: json['avatar_url'] as String?,
);

Map<String, dynamic> _$PersonRefToJson(_PersonRef instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'avatar_url': instance.avatarUrl,
    };
