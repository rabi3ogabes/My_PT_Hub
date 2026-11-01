import 'package:freezed_annotation/freezed_annotation.dart';

import 'common.dart';

part 'shop.freezed.dart';
part 'shop.g.dart';

@freezed
abstract class Price with _$Price {
  const factory Price({
    required String id,
    @Default('one_time') String type,
    required Money amount,
    String? interval,
    int? intervalCount,
    int? installments,
    int? trialDays,
  }) = _Price;

  factory Price.fromJson(Map<String, dynamic> json) => _$PriceFromJson(json);
}

/// **Product**.
@freezed
abstract class Product with _$Product {
  const factory Product({
    required String id,
    required String type,
    String? slug,
    required String name,
    String? description,
    String? imageUrl,
    @Default(<Price>[]) List<Price> prices,
    @Default('external') String iosPurchaseMode,
  }) = _Product;

  factory Product.fromJson(Map<String, dynamic> json) => _$ProductFromJson(json);
}

/// `POST /me/shop/{product}/checkout`.
@freezed
abstract class CheckoutResult with _$CheckoutResult {
  const factory CheckoutResult({required String orderId, String? checkoutUrl, required String status}) =
      _CheckoutResult;

  factory CheckoutResult.fromJson(Map<String, dynamic> json) => _$CheckoutResultFromJson(json);
}

/// `GET /public/orders/{id}/status`.
@freezed
abstract class OrderStatus with _$OrderStatus {
  const factory OrderStatus({required String status, @Default(false) bool fulfilled}) = _OrderStatus;

  factory OrderStatus.fromJson(Map<String, dynamic> json) => _$OrderStatusFromJson(json);
}

@freezed
abstract class ClientSubscription with _$ClientSubscription {
  const factory ClientSubscription({
    required String id,
    required String productName,
    required String status,
    Money? amount,
    String? interval,
    DateTime? currentPeriodEnd,
    @Default(false) bool cancelAtPeriodEnd,
  }) = _ClientSubscription;

  factory ClientSubscription.fromJson(Map<String, dynamic> json) => _$ClientSubscriptionFromJson(json);
}

/// An order row from `GET /me/purchases`. The contract leaves order fields
/// open, so commonly present fields are typed and everything is optional.
@freezed
abstract class Order with _$Order {
  const factory Order({
    required String id,
    String? number,
    String? status,
    Money? total,
    String? productName,
    DateTime? createdAt,
  }) = _Order;

  factory Order.fromJson(Map<String, dynamic> json) => _$OrderFromJson(json);
}

/// `GET /me/purchases`.
@freezed
abstract class Purchases with _$Purchases {
  const factory Purchases({
    @Default(<Order>[]) List<Order> orders,
    @Default(<ClientSubscription>[]) List<ClientSubscription> subscriptions,
  }) = _Purchases;

  factory Purchases.fromJson(Map<String, dynamic> json) => _$PurchasesFromJson(json);
}
