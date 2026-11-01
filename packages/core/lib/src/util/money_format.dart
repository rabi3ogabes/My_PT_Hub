import 'dart:math' as math;

import 'package:intl/intl.dart';

/// Formats integer minor units with an ISO currency for display.
///
/// Money is never handled as a float in business logic; this helper only
/// produces strings. The API also returns a server-formatted string which is
/// preferred when available (see `Money.formatted`).
abstract final class MoneyFormat {
  static String format(int amountMinor, String currency, {String? locale}) {
    final digits = currencyDigits(currency);
    final format = NumberFormat.currency(locale: locale, name: currency, decimalDigits: digits);
    return format.format(amountMinor / math.pow(10, digits));
  }

  /// Compact form for dashboards, e.g. `QAR 12K`.
  static String compact(int amountMinor, String currency, {String? locale}) {
    final digits = currencyDigits(currency);
    final format = NumberFormat.compactCurrency(locale: locale, name: currency, decimalDigits: 0);
    return format.format(amountMinor / math.pow(10, digits));
  }

  /// Number of fraction digits for [currency] (ISO 4217).
  static int currencyDigits(String currency) {
    switch (currency.toUpperCase()) {
      case 'BHD':
      case 'KWD':
      case 'OMR':
      case 'JOD':
      case 'TND':
      case 'LYD':
      case 'IQD':
        return 3;
      case 'JPY':
      case 'KRW':
      case 'XOF':
      case 'XAF':
      case 'CLP':
        return 0;
      default:
        return 2;
    }
  }
}
