import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/foundation.dart';

/// Estimated one-rep max formulas.
abstract final class OneRepMax {
  /// Epley: w × (1 + reps / 30).
  static double epley(double weight, int reps) => reps <= 1 ? weight : weight * (1 + reps / 30);

  /// Brzycki: w × 36 / (37 − reps). Valid up to ~12 reps.
  static double brzycki(double weight, int reps) {
    if (reps <= 1) return weight;
    if (reps >= 37) return epley(weight, reps);
    return weight * 36 / (37 - reps);
  }

  /// Average of Epley and Brzycki (more stable across rep ranges).
  static double estimate(double weight, int reps) => (epley(weight, reps) + brzycki(weight, reps)) / 2;

  /// Working weights at common percentages of [oneRm].
  static List<(int percent, double weight)> percentages(double oneRm) =>
      [for (final p in const [95, 90, 85, 80, 75, 70, 65, 60, 50]) (p, oneRm * p / 100)];
}

/// Result of a plate calculation (per side of the bar).
@immutable
class PlateLoad {
  const PlateLoad({required this.perSide, required this.achieved, required this.remainder});

  final List<double> perSide;
  final double achieved;

  /// Total that could not be loaded with the available plates.
  final double remainder;

  bool get exact => remainder.abs() < 1e-6;
}

/// Greedy plate loader (largest plates first), in the display unit.
abstract final class PlateCalculator {
  static const kgPlates = [25.0, 20.0, 15.0, 10.0, 5.0, 2.5, 1.25];
  static const lbPlates = [45.0, 35.0, 25.0, 10.0, 5.0, 2.5];

  static double defaultBar(UnitSystem units) => units == UnitSystem.imperial ? 45 : 20;

  static List<double> defaultPlates(UnitSystem units) => units == UnitSystem.imperial ? lbPlates : kgPlates;

  static PlateLoad calculate({required double target, required double bar, required List<double> plates}) {
    if (target <= bar) return PlateLoad(perSide: const [], achieved: bar, remainder: target < bar ? 0 : 0);
    var perSideRemaining = (target - bar) / 2;
    final sorted = [...plates]..sort((a, b) => b.compareTo(a));
    final perSide = <double>[];
    for (final plate in sorted) {
      while (perSideRemaining + 1e-9 >= plate) {
        perSide.add(plate);
        perSideRemaining -= plate;
      }
    }
    final achieved = bar + perSide.fold(0.0, (s, p) => s + p) * 2;
    return PlateLoad(perSide: perSide, achieved: achieved, remainder: target - achieved);
  }
}
