<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Data\SafetyResult;
use App\Domain\Ai\Support\TextNormalizer;
use App\Domain\Tenancy\Models\ClientProfile;

/**
 * Safety guardrails for every AI surface.
 *
 * - Screens free text (en/ar/fr/es keyword + pattern lists) for eating-disorder
 *   language, self-harm, medical/diagnosis/medication requests, injury
 *   mentions, minors and extreme-deficit requests.
 * - Screens the client profile (age < 18, PAR-Q flag).
 * - Enforces hard caps on generated outputs: kcal floors (1,200 women /
 *   1,500 men and unknown) and a maximum loss rate of 1 kg/week.
 *
 * Refuse categories are routed to the coach (flagged) instead of answered.
 * Injury mentions and PAR-Q are flags: drafts are still produced for the
 * coach, with conservative instructions.
 */
final class SafetyGuard
{
    public const REFUSE = ['eating_disorder', 'self_harm', 'medical', 'minor', 'extreme_deficit'];

    public const KCAL_FLOOR_FEMALE = 1200;

    public const KCAL_FLOOR_MALE = 1500;

    public const MAX_LOSS_KG_PER_WEEK = 1.0;

    /** Keyword lists per category and language (natural spelling; normalised at match time). */
    private const KEYWORDS = [
        'eating_disorder' => [
            'en' => ['anorexia', 'anorexic', 'bulimia', 'bulimic', 'purge', 'purging', 'binge and purge', 'make myself throw up', 'make myself sick', 'throw up after eating', 'vomit after eating', 'laxatives', 'starve myself', 'starving myself', 'pro ana', 'pro-ana', 'thinspo', 'thinspiration', 'skip meals to lose', 'feel guilty after eating', 'hate my body', 'chew and spit'],
            'fr' => ['anorexie', 'anorexique', 'boulimie', 'boulimique', 'me faire vomir', 'vomir après manger', 'vomir après avoir mangé', 'laxatifs', "m'affamer", 'me priver de manger', 'sauter des repas pour maigrir', 'je déteste mon corps', 'culpabilise après avoir mangé'],
            'es' => ['anorexia', 'anoréxica', 'anoréxico', 'bulimia', 'bulímica', 'provocarme el vómito', 'vomitar después de comer', 'laxantes', 'matarme de hambre', 'no comer para adelgazar', 'saltarme comidas para adelgazar', 'odio mi cuerpo', 'culpa después de comer'],
            'ar' => ['فقدان الشهية العصبي', 'الشره المرضي', 'بوليميا', 'انوركسيا', 'أتقيأ بعد الأكل', 'اتقيأ بعد الاكل', 'أجبر نفسي على التقيؤ', 'استفرغ بعد الأكل', 'ملينات', 'أجوع نفسي', 'اجوع نفسي', 'تجويع نفسي', 'أكره جسمي', 'اكره جسمي', 'أشعر بالذنب بعد الأكل'],
        ],
        'self_harm' => [
            'en' => ['kill myself', 'suicide', 'suicidal', 'self harm', 'self-harm', 'hurt myself on purpose', 'end my life'],
            'fr' => ['me suicider', 'suicide', 'suicidaire', "m'automutiler", 'en finir avec la vie', 'me faire du mal'],
            'es' => ['suicidarme', 'suicidio', 'suicida', 'autolesión', 'hacerme daño', 'quitarme la vida'],
            'ar' => ['انتحار', 'أنتحر', 'انتحر', 'أقتل نفسي', 'اقتل نفسي', 'إيذاء نفسي', 'ايذاء نفسي', 'أنهي حياتي'],
        ],
        'medical' => [
            'en' => ['diagnose', 'diagnosis', "what's wrong with my", 'what is wrong with my', 'is it torn', 'is it broken', 'is it fractured', 'do i have a hernia', 'should i take ibuprofen', 'painkiller', 'painkillers', 'medication', 'medicine dose', 'prescription', 'dosage', 'steroids', 'insulin dose', 'chest pain', 'blood pressure medication', 'heart condition'],
            'fr' => ['diagnostiquer', 'diagnostic', "qu'est-ce que j'ai au", 'est-ce déchiré', 'est-ce cassé', 'médicament', 'médicaments', 'ordonnance', 'anti-inflammatoire', 'posologie', 'stéroïdes', 'douleur thoracique', 'douleur à la poitrine'],
            'es' => ['diagnosticar', 'diagnóstico', 'qué tengo en', 'está roto', 'está desgarrado', 'medicamento', 'medicamentos', 'receta médica', 'ibuprofeno', 'dosis', 'esteroides', 'dolor en el pecho', 'dolor torácico'],
            'ar' => ['تشخيص', 'شخص حالتي', 'هل هو تمزق', 'هل هو كسر', 'دواء', 'أدوية', 'ادويه', 'مسكن', 'مسكنات', 'وصفة طبية', 'جرعة', 'منشطات', 'ستيرويد', 'ألم في الصدر', 'الم في الصدر'],
        ],
        'injury' => [
            'en' => ['pain', 'painful', 'hurts', 'hurt my', 'injury', 'injured', 'sprain', 'sprained', 'torn', 'tear', 'swollen', 'swelling', 'numb', 'numbness', 'tingling', 'dizzy', 'pulled a muscle', 'herniated', 'slipped disc', 'fracture', 'strain'],
            'fr' => ['douleur', "j'ai mal", 'mal au', 'mal aux', 'blessure', 'blessé', 'blessée', 'entorse', 'déchirure', 'gonflé', 'engourdi', 'vertige', 'hernie', 'élongation'],
            'es' => ['dolor', 'me duele', 'lesión', 'lesionado', 'lesionada', 'esguince', 'desgarro', 'hinchado', 'hinchazón', 'mareo', 'hernia', 'entumecido', 'tirón'],
            'ar' => ['ألم', 'الم', 'الألم', 'يؤلمني', 'توجعني', 'إصابة', 'اصابة', 'مصاب', 'التواء', 'تمزق', 'تورم', 'دوخة', 'انزلاق غضروفي', 'كسر', 'شد عضلي'],
        ],
        'minor' => [
            'en' => ['my son', 'my daughter', 'my kid', 'my child', 'teenager', 'high school', 'middle school'],
            'fr' => ['mon fils', 'ma fille', 'mon enfant', 'adolescent', 'adolescente', 'au lycée', 'au collège'],
            'es' => ['mi hijo', 'mi hija', 'mi niño', 'adolescente', 'en la secundaria', 'en el instituto'],
            'ar' => ['ابني', 'ابنتي', 'بنتي', 'طفلي', 'مراهق', 'مراهقة', 'في المدرسة الثانوية', 'في الإعدادية'],
        ],
        'extreme_deficit' => [
            'en' => ['water fast', 'water fasting', 'stop eating', 'eat nothing', 'not eat at all', 'starve', 'only drink water', 'one meal a week', 'juice cleanse for'],
            'fr' => ['ne plus manger', 'arrêter de manger', 'ne rien manger', 'jeûne hydrique', "ne boire que de l'eau"],
            'es' => ['dejar de comer', 'no comer nada', 'ayuno de agua', 'solo beber agua', 'solo tomar agua'],
            'ar' => ['التوقف عن الأكل', 'التوقف عن الاكل', 'بطل أكل', 'بطل اكل', 'لا آكل شيئا', 'لا اكل شي', 'صيام ماء', 'أشرب ماء فقط', 'اشرب ماء فقط'],
        ],
    ];

    /** @var array<string, list<string>>|null normalised keywords per category */
    private ?array $compiled = null;

    public function screenText(?string $text): SafetyResult
    {
        $normalized = TextNormalizer::normalize($text);
        if ($normalized === '') {
            return new SafetyResult;
        }

        $matches = [];
        foreach ($this->keywords() as $category => $words) {
            foreach ($words as $word) {
                if (preg_match(self::wordPattern($word), $normalized)) {
                    $matches[] = ['category' => $category, 'match' => $word];
                    break;
                }
            }
        }

        foreach ($this->ageMentions($normalized) as $age) {
            if ($age >= 5 && $age < 18) {
                $matches[] = ['category' => 'minor', 'match' => (string) $age];
                break;
            }
        }

        if (($kcal = $this->lowKcalMention($normalized)) !== null) {
            $matches[] = ['category' => 'extreme_deficit', 'match' => "{$kcal} kcal"];
        }
        if (($rate = $this->fastLossMention($normalized)) !== null) {
            $matches[] = ['category' => 'extreme_deficit', 'match' => round($rate, 2).' kg/week'];
        }

        return $this->result($matches);
    }

    /** Profile-based checks: minors and PAR-Q. */
    public function screenProfile(?ClientProfile $profile): SafetyResult
    {
        if ($profile === null) {
            return new SafetyResult;
        }
        $matches = [];
        $age = $profile->age();
        if ($age !== null && $age < 18) {
            $matches[] = ['category' => 'minor', 'match' => "age {$age}"];
        }
        if ($profile->parq_flagged) {
            $matches[] = ['category' => 'parq', 'match' => 'parq_flagged'];
        }

        return $this->result($matches);
    }

    /** Kcal floor for a gender (female 1,200; male and unknown 1,500). */
    public function kcalFloor(?string $gender): int
    {
        return in_array(strtolower((string) $gender), ['female', 'woman', 'f'], true) ? self::KCAL_FLOOR_FEMALE : self::KCAL_FLOOR_MALE;
    }

    /**
     * Clamps a kcal target to the floor unless the coach set an explicit override.
     *
     * @return array{0: int, 1: bool} [kcal, floorApplied]
     */
    public function enforceKcal(int $kcal, ?string $gender, bool $coachOverride = false): array
    {
        $floor = $this->kcalFloor($gender);
        if ($coachOverride || $kcal >= $floor) {
            return [$kcal, false];
        }

        return [$floor, true];
    }

    public function enforceLossRate(float $kgPerWeek): float
    {
        return min(self::MAX_LOSS_KG_PER_WEEK, max(0.0, $kgPerWeek));
    }

    /**
     * Checks generated text (chat replies, recaps) for unsafe advice: kcal
     * targets below the floor or loss rates above 1 kg/week.
     */
    public function unsafeAdvice(string $text, ?string $gender = null): ?string
    {
        $normalized = TextNormalizer::normalize($text);
        $kcal = $this->lowKcalMention($normalized, $this->kcalFloor($gender));
        if ($kcal !== null) {
            return "kcal:{$kcal}";
        }
        $rate = $this->fastLossMention($normalized);

        return $rate !== null ? 'loss_rate:'.round($rate, 2) : null;
    }

    public function disclaimer(?string $locale = null): string
    {
        return __('ai.disclaimer.health', [], $locale);
    }

    public function refusalMessage(SafetyResult $result, ?string $locale = null): string
    {
        $category = collect(['self_harm', 'eating_disorder', 'medical', 'extreme_deficit', 'minor', 'parq'])
            ->first(fn (string $c) => $result->has($c)) ?? 'medical';

        return __("ai.refusal.{$category}", [], $locale);
    }

    /**
     * Whole-word pattern; Arabic keywords also match with attached
     * conjunction/preposition/article prefixes (و، ب، ل، ال، بال، لل).
     */
    public static function wordPattern(string $word): string
    {
        $prefix = preg_match('/^\p{Arabic}/u', $word) ? '(?:و?(?:بال|وال|لل|ال|ب|ل|ك)?)' : '';

        return '/(?<![\p{L}\p{N}])'.$prefix.preg_quote($word, '/').'(?![\p{L}\p{N}])/u';
    }

    /** @param list<array{category: string, match: string}> $matches */
    private function result(array $matches): SafetyResult
    {
        $categories = array_values(array_unique(array_column($matches, 'category')));

        return new SafetyResult($categories, array_intersect($categories, self::REFUSE) !== [], $matches);
    }

    /** @return array<string, list<string>> */
    private function keywords(): array
    {
        if ($this->compiled === null) {
            $this->compiled = [];
            foreach (self::KEYWORDS as $category => $languages) {
                $words = [];
                foreach ($languages as $list) {
                    foreach ($list as $word) {
                        $words[] = TextNormalizer::normalize($word);
                    }
                }
                $this->compiled[$category] = array_values(array_unique(array_filter($words)));
            }
        }

        return $this->compiled;
    }

    /** @return list<int> */
    private function ageMentions(string $t): array
    {
        $patterns = [
            '/(?<![\p{L}\p{N}])(\d{1,2})\s*(?:years?|yrs?)[\s\-]*old/u',
            '/(?<![\p{L}\p{N}])(\d{1,2})\s*(?:yo|y o)(?![\p{L}\p{N}])/u',
            '/(?<![\p{L}\p{N}])(?:i am|i m|im)\s+(?:only\s+)?(\d{1,2})(?!\s*(?:\d|kg|kilo|lb|pound|%|cm|min|rep|set|day|week|month|hour|sec|km|mile|times|x\b))(?![\p{L}\p{N}.,])/u',
            '/(?<![\p{L}\p{N}])j ai\s+(\d{1,2})\s*ans(?![\p{L}\p{N}])/u',
            '/(?<![\p{L}\p{N}])tengo\s+(\d{1,2})\s*anos(?![\p{L}\p{N}])/u',
            '/(?<![\p{L}\p{N}])(?:عمري|سني)\s*(\d{1,2})/u',
            '/(?<![\p{L}\p{N}])(\d{1,2})\s*(?:سنه|سنوات|عاما|عام)(?![\p{L}\p{N}])/u',
        ];
        $ages = [];
        foreach ($patterns as $p) {
            if (preg_match_all($p, $t, $m)) {
                foreach ($m[1] as $age) {
                    $ages[] = (int) $age;
                }
            }
        }

        return $ages;
    }

    private function lowKcalMention(string $t, int $floor = self::KCAL_FLOOR_FEMALE): ?int
    {
        if (preg_match_all('/(?<![\p{N}.,])(\d{2,4})\s*(?:kcal|kcals|kilocalories|calories|calorie|calorias|caloria|cals|cal|سعره حراريه|سعرات حراريه|سعرات|سعره|كالوري|كالوريز)(?![\p{L}])/u', $t, $m)) {
            foreach ($m[1] as $value) {
                $v = (int) $value;
                // Per-meal or per-food numbers ("300 kcal snack") are not daily targets:
                // only flag when the text frames it as a daily intake.
                if ($v > 0 && $v < $floor && $this->framesDaily($t)) {
                    return $v;
                }
            }
        }

        return null;
    }

    private function framesDaily(string $t): bool
    {
        return (bool) preg_match('/(?<![\p{L}])(a day|per day|daily|each day|every day|par jour|quotidien|al dia|por dia|diario|diarias|يوميا|في اليوم|باليوم|كل يوم|eat|eating|diet|target|manger|regime|comer|dieta|اكل|رجيم|حميه)(?![\p{L}])/u', $t);
    }

    private function fastLossMention(string $t): ?float
    {
        $units = '(kg|kgs|kilos?|kilograms?|lbs?|pounds?|كيلو|كيلوغرام|كيلوجرام|كغ|كجم)';
        $periods = '(days?|weeks?|months?|jours?|semaines?|mois|dias?|semanas?|mes|meses|يوم|ايام|اسبوع|اسابيع|شهر|اشهر|يومين|اسبوعين|شهرين)';
        $patterns = [
            '/(?:lose|drop|lost|cut|perdre|perdu|maigrir de|perder|bajar|اخسر|انزل|انقص|خساره|خسارة|نزول)\s+(\d+(?:[.,]\d+)?)\s*'.$units.'.{0,24}?(?:in|within|en|dans|في|خلال)\s+(?:(\d+|a|an|one|un|une|una|un solo)\s+)?'.$periods.'/u',
        ];
        foreach ($patterns as $p) {
            if (! preg_match($p, $t, $m)) {
                continue;
            }
            $amount = (float) str_replace(',', '.', $m[1]);
            $unit = $m[2];
            $count = isset($m[3]) && is_numeric($m[3]) ? (float) $m[3] : 1.0;
            $period = $m[4];

            if (preg_match('/^(lb|lbs|pound|pounds)$/', $unit)) {
                $amount *= 0.4536;
            }
            if (in_array($period, ['يومين', 'اسبوعين', 'شهرين'], true)) {
                $count = 2.0;
            }
            $weeks = match (true) {
                (bool) preg_match('/^(day|days|jour|jours|dia|dias|يوم|ايام|يومين)$/u', $period) => $count / 7,
                (bool) preg_match('/^(month|months|mois|mes|meses|شهر|اشهر|شهرين)$/u', $period) => $count * 4.345,
                default => $count,
            };
            if ($weeks > 0 && $amount / $weeks > self::MAX_LOSS_KG_PER_WEEK) {
                return $amount / $weeks;
            }
        }

        return null;
    }
}
