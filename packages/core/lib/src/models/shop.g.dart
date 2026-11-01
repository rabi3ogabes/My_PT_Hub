// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'shop.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_Price _$PriceFromJson(Map<String, dynamic> json) => _Price(
  id: json['id'] as String,
  type: json['type'] as String? ?? 'one_time',
  amount: Money.fromJson(json['amount'] as Map<String, dynamic>),
  interval: json['interval'] as String?,
  intervalCount: (json['interval_count'] as num?)?.toInt(),
  installments: (json['installments'] as num?)?.toInt(),
  trialDays: (json['trial_days'] as num?)?.toInt(),
);

Map<String, dynamic> _$PriceToJson(_Price instance) => <String, dynamic>{
  'id': instance.id,
  'type': instance.type,
  'amount': instance.amount.toJson(),
  'interval': instance.interval,
  'interval_count': instance.intervalCount,
  'installments': instance.installments,
  'trial_days': instance.trialDays,
};

_Product _$ProductFromJson(Map<String, dynamic> json) => _Product(
  id: json['id'] as String,
  type: json['type'] as String,
  slug: json['slug'] as String?,
  name: json['name'] as String,
  description: json['description'] as String?,
  imageUrl: json['image_url'] as String?,
  prices:
      (json['prices'] as List<dynamic>?)
          ?.map((e) => Price.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <Price>[],
  iosPurchaseMode: json['ios_purchase_mode'] as String? ?? 'external',
);

Map<String, dynamic> _$ProductToJson(_Product instance) => <String, dynamic>{
  'id': instance.id,
  'type': instance.type,
  'slug': instance.slug,
  'name': instance.name,
  'description': instance.description,
  'image_url': instance.imageUrl,
  'prices': instance.prices.map((e) => e.toJson()).toList(),
  'ios_purchase_mode': instance.iosPurchaseMode,
};

_CheckoutResult _$CheckoutResultFromJson(Map<String, dynamic> json) =>
    _CheckoutResult(
      orderId: json['order_id'] as String,
      checkoutUrl: json['checkout_url'] as String?,
      status: json['status'] as String,
    );

Map<String, dynamic> _$CheckoutResultToJson(_CheckoutResult instance) =>
    <String, dynamic>{
      'order_id': instance.orderId,
      'checkout_url': instance.checkoutUrl,
      'status': instance.status,
    };

_OrderStatus _$OrderStatusFromJson(Map<String, dynamic> json) => _OrderStatus(
  status: json['status'] as String,
  fulfilled: json['fulfilled'] as bool? ?? false,
);

Map<String, dynamic> _$OrderStatusToJson(_OrderStatus instance) =>
    <String, dynamic>{
      'status': instance.status,
      'fulfilled': instance.fulfilled,
    };

_ClientSubscription _$ClientSubscriptionFromJson(Map<String, dynamic> json) =>
    _ClientSubscription(
      id: json['id'] as String,
      productName: json['product_name'] as String,
      status: json['status'] as String,
      amount: json['amount'] == null
          ? null
          : Money.fromJson(json['amount'] as Map<String, dynamic>),
      interval: json['interval'] as String?,
      currentPeriodEnd: json['current_period_end'] == null
          ? null
          : DateTime.parse(json['current_period_end'] as String),
      cancelAtPeriodEnd: json['cancel_at_period_end'] as bool? ?? false,
    );

Map<String, dynamic> _$ClientSubscriptionToJson(_ClientSubscription instance) =>
    <String, dynamic>{
      'id': instance.id,
      'product_name': instance.productName,
      'status': instance.status,
      'amount': instance.amount?.toJson(),
      'interval': instance.interval,
      'current_period_end': instance.currentPeriodEnd?.toIso8601String(),
      'cancel_at_period_end': instance.cancelAtPeriodEnd,
    };

_Order _$OrderFromJson(Map<String, dynamic> json) => _Order(
  id: json['id'] as String,
  number: json['number'] as String?,
  status: json['status'] as String?,
  total: json['total'] == null
      ? null
      : Money.fromJson(json['total'] as Map<String, dynamic>),
  productName: json['product_name'] as String?,
  createdAt: json['created_at'] == null
      ? null
      : DateTime.parse(json['created_at'] as String),
);

Map<String, dynamic> _$OrderToJson(_Order instance) => <String, dynamic>{
  'id': instance.id,
  'number': instance.number,
  'status': instance.status,
  'total': instance.total?.toJson(),
  'product_name': instance.productName,
  'created_at': instance.createdAt?.toIso8601String(),
};

_Purchases _$PurchasesFromJson(Map<String, dynamic> json) => _Purchases(
  orders:
      (json['orders'] as List<dynamic>?)
          ?.map((e) => Order.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <Order>[],
  subscriptions:
      (json['subscriptions'] as List<dynamic>?)
          ?.map((e) => ClientSubscription.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <ClientSubscription>[],
);

Map<String, dynamic> _$PurchasesToJson(_Purchases instance) =>
    <String, dynamic>{
      'orders': instance.orders.map((e) => e.toJson()).toList(),
      'subscriptions': instance.subscriptions.map((e) => e.toJson()).toList(),
    };
