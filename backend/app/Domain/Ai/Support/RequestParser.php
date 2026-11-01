<?php

namespace App\Domain\Ai\Support;

use App\Domain\Ai\Services\SafetyGuard;

/**
 * Pulls structured hints out of free-text requests in en/ar/fr/es
 * ("4-day upper lower, dumbbells only, 45 min"). The raw text is still sent
 * to the model; these hints drive retrieval and server-side checks.
 */
final class RequestParser
{
    private const EQUIPMENT = [
        'dumbbell' => ['dumbbell', 'dumbbells', 'db', 'haltere', 'halteres', 'mancuerna', 'mancuernas', 'دمبل', 'دمبلز', 'دامبل'],
        'barbell' => ['barbell', 'barbells', 'barre', 'barra olimpica', 'بار'],
        'kettlebell' => ['kettlebell', 'kettlebells', 'pesa rusa', 'كيتل'],
        'band' => ['band', 'bands', 'elastique', 'elastiques', 'banda', 'bandas', 'مطاط'],
        'bodyweight' => ['bodyweight', 'body weight', 'no equipment', 'poids du corps', 'sans materiel', 'peso corporal', 'sin equipo', 'وزن الجسم', 'بدون معدات'],
        'machine' => ['machine', 'machines', 'maquina', 'maquinas', 'اجهزه'],
        'cable' => ['cable', 'cables', 'poulie', 'polea'],
        'gym' => ['full gym', 'commercial gym', 'salle de sport', 'gimnasio', 'جيم', 'نادي رياضي'],
    ];

    private const GOALS = [
        'muscle_gain' => ['hypertrophy', 'muscle', 'bulk', 'mass', 'hypertrophie', 'prise de masse', 'muscler', 'hipertrofia', 'masa muscular', 'ضخامه', 'بناء العضلات', 'تضخيم'],
        'strength' => ['strength', 'strong', 'powerlifting', 'force', 'fuerza', 'قوه'],
        'fat_loss' => ['fat loss', 'lose fat', 'lose weight', 'weight loss', 'cut', 'shred', 'perte de poids', 'perdre du poids', 'seche', 'perder peso', 'perder grasa', 'definicion', 'تنشيف', 'خساره وزن', 'حرق الدهون'],
        'performance' => ['performance', 'athletic', 'sport', 'endurance', 'rendimiento', 'اداء'],
        'health' => ['health', 'mobility', 'longevity', 'sante', 'salud', 'صحه'],
    ];

    private const LEVELS = [
        'beginner' => ['beginner', 'novice', 'new to', 'debutant', 'debutante', 'principiante', 'مبتدئ', 'مبتدئه'],
        'advanced' => ['advanced', 'experienced', 'avance', 'avanzado', 'avanzada', 'متقدم', 'محترف'],
        'intermediate' => ['intermediate', 'intermediaire', 'intermedio', 'متوسط'],
    ];

    /** @return array{days_per_week: int|null, session_minutes: int|null, weeks: int|null, equipment: list<string>, goal: string|null, level: string|null} */
    public static function program(?string $text): array
    {
        $t = TextNormalizer::normalize($text);

        $days = null;
        if (preg_match('/(?<![\p{N}])([1-7])\s*(?:-|\s)?\s*(?:day|days|jours?|dias?|ايام|يوم|x\s*(?:per|a|par|por)\s*(?:week|semaine|semana))/u', $t, $m)) {
            $days = (int) $m[1];
        }
        $minutes = null;
        if (preg_match('/(?<![\p{N}])(\d{2,3})\s*(?:min|mins|minutes?|minutos?|دقيقه|دقائق|د)(?![\p{L}])/u', $t, $m)) {
            $minutes = max(15, min(180, (int) $m[1]));
        }
        $weeks = null;
        if (preg_match('/(?<![\p{N}])(\d{1,2})\s*(?:-|\s)?\s*(?:weeks?|semaines?|semanas?|اسابيع|اسبوع)/u', $t, $m)) {
            $weeks = max(1, min(16, (int) $m[1]));
        }

        return [
            'days_per_week' => $days,
            'session_minutes' => $minutes,
            'weeks' => $weeks,
            'equipment' => self::matchAll($t, self::EQUIPMENT),
            'goal' => self::matchFirst($t, self::GOALS),
            'level' => self::matchFirst($t, self::LEVELS),
        ];
    }

    /**
     * @param  array<string, list<string>>  $map
     * @return list<string>
     */
    private static function matchAll(string $t, array $map): array
    {
        $out = [];
        foreach ($map as $key => $words) {
            foreach ($words as $w) {
                if (preg_match(SafetyGuard::wordPattern(TextNormalizer::normalize($w)), $t)) {
                    $out[] = $key;
                    break;
                }
            }
        }

        return $out;
    }

    /** @param array<string, list<string>> $map */
    private static function matchFirst(string $t, array $map): ?string
    {
        return self::matchAll($t, $map)[0] ?? null;
    }
}
