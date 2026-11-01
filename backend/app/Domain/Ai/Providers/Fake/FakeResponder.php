<?php

namespace App\Domain\Ai\Providers\Fake;

use App\Domain\Ai\Support\FormRules;
use App\Domain\Ai\Support\TextNormalizer;

/**
 * Builds realistic, schema-valid outputs for the Fake driver from the
 * structured context each feature attaches to its prompt (library
 * exercises, candidate foods, client metrics). Deterministic: same input,
 * same output.
 */
final class FakeResponder
{
    private const PHRASES = [
        'day' => ['en' => 'Day :n', 'ar' => 'اليوم :n', 'fr' => 'Jour :n', 'es' => 'Día :n'],
        'main' => ['en' => 'Main block', 'ar' => 'الجزء الرئيسي', 'fr' => 'Bloc principal', 'es' => 'Bloque principal'],
        'program_desc' => ['en' => 'A :days-day plan built from your exercise library, progressing week to week.', 'ar' => 'برنامج من :days أيام مبني من مكتبة التمارين لديك، مع تدرّج أسبوعي في الأحمال.', 'fr' => 'Un programme de :days jours construit à partir de votre bibliothèque, avec une progression chaque semaine.', 'es' => 'Un plan de :days días creado con tu biblioteca de ejercicios, con progresión semanal.'],
        'program_notes' => ['en' => 'Warm up 5–8 minutes before each session. Stop any exercise that causes pain and tell your coach.', 'ar' => 'قم بالإحماء من 5 إلى 8 دقائق قبل كل حصة. أوقف أي تمرين يسبب ألماً وأخبر مدربك.', 'fr' => 'Échauffez-vous 5 à 8 minutes avant chaque séance. Arrêtez tout exercice douloureux et prévenez votre coach.', 'es' => 'Calienta 5–8 minutos antes de cada sesión. Detén cualquier ejercicio que cause dolor y avisa a tu coach.'],
        'progression_notes' => ['en' => 'Add load when every set reaches the top of the rep range at RPE 8 or below.', 'ar' => 'زد الوزن عندما تصل كل المجموعات إلى الحد الأعلى من التكرارات بجهد 8 أو أقل.', 'fr' => 'Augmentez la charge quand toutes les séries atteignent le haut de la fourchette à RPE 8 ou moins.', 'es' => 'Sube la carga cuando todas las series lleguen al tope de repeticiones con RPE 8 o menos.'],
        'reason_ready' => ['en' => 'All sets completed with reps in reserve — ready for a small load increase.', 'ar' => 'أُنجزت جميع المجموعات مع وجود تكرارات احتياطية — جاهز لزيادة بسيطة في الوزن.', 'fr' => 'Toutes les séries réalisées avec de la marge — prêt pour une légère hausse de charge.', 'es' => 'Todas las series completadas con margen: listo para un pequeño aumento de carga.'],
        'reason_fatigue' => ['en' => 'High RPE or missed sets — reduce load to recover quality reps.', 'ar' => 'جهد مرتفع أو مجموعات غير مكتملة — خفّف الوزن لاستعادة جودة التكرارات.', 'fr' => 'RPE élevé ou séries manquées — baissez la charge pour retrouver des répétitions de qualité.', 'es' => 'RPE alto o series fallidas: baja la carga para recuperar repeticiones de calidad.'],
        'reason_plateau' => ['en' => 'Estimated 1RM has plateaued — add a set to drive new adaptation.', 'ar' => 'ثبات في القوة القصوى التقديرية — أضف مجموعة لتحفيز تكيف جديد.', 'fr' => '1RM estimé en plateau — ajoutez une série pour relancer la progression.', 'es' => '1RM estimado estancado: añade una serie para generar nueva adaptación.'],
        'reason_consolidate' => ['en' => 'Keep the current prescription for another week to consolidate.', 'ar' => 'حافظ على الوصفة الحالية أسبوعاً آخر لتثبيت التقدم.', 'fr' => 'Gardez la prescription actuelle une semaine de plus pour consolider.', 'es' => 'Mantén la prescripción actual una semana más para consolidar.'],
        'progression_summary' => ['en' => ':n exercises analysed over the last 4 weeks.', 'ar' => 'تم تحليل :n تمارين خلال الأسابيع الأربعة الماضية.', 'fr' => ':n exercices analysés sur les 4 dernières semaines.', 'es' => ':n ejercicios analizados en las últimas 4 semanas.'],
        'meal_plan_name' => ['en' => 'Balanced :cuisine plan', 'ar' => 'خطة :cuisine متوازنة', 'fr' => 'Plan :cuisine équilibré', 'es' => 'Plan :cuisine equilibrado'],
        'meal_plan_notes' => ['en' => 'Portions are calculated to match your targets. Drink water through the day.', 'ar' => 'الكميات محسوبة لتطابق أهدافك. اشرب الماء على مدار اليوم.', 'fr' => 'Les portions sont calculées pour atteindre vos objectifs. Hydratez-vous tout au long de la journée.', 'es' => 'Las porciones están calculadas para tus objetivos. Bebe agua durante el día.'],
        'slot_breakfast' => ['en' => 'Breakfast', 'ar' => 'الفطور', 'fr' => 'Petit-déjeuner', 'es' => 'Desayuno'],
        'slot_lunch' => ['en' => 'Lunch', 'ar' => 'الغداء', 'fr' => 'Déjeuner', 'es' => 'Almuerzo'],
        'slot_dinner' => ['en' => 'Dinner', 'ar' => 'العشاء', 'fr' => 'Dîner', 'es' => 'Cena'],
        'slot_snack' => ['en' => 'Snack', 'ar' => 'وجبة خفيفة', 'fr' => 'Collation', 'es' => 'Merienda'],
        'slot_suhoor' => ['en' => 'Suhoor', 'ar' => 'السحور', 'fr' => 'Suhoor', 'es' => 'Suhoor'],
        'slot_iftar' => ['en' => 'Iftar', 'ar' => 'الإفطار', 'fr' => 'Iftar', 'es' => 'Iftar'],
        'win_sessions' => ['en' => 'Completed :n training sessions in the last 14 days.', 'ar' => 'أكملت :n حصص تدريبية خلال آخر 14 يوماً.', 'fr' => ':n séances réalisées sur les 14 derniers jours.', 'es' => ':n sesiones completadas en los últimos 14 días.'],
        'win_weight' => ['en' => 'Weight changed by :kg kg over the period.', 'ar' => 'تغيّر الوزن بمقدار :kg كغ خلال الفترة.', 'fr' => 'Le poids a varié de :kg kg sur la période.', 'es' => 'El peso cambió :kg kg en el periodo.'],
        'struggle_logging' => ['en' => 'Food logged on only :n of the last 7 days.', 'ar' => 'تم تسجيل الطعام في :n أيام فقط من آخر 7 أيام.', 'fr' => 'Alimentation notée seulement :n jours sur 7.', 'es' => 'Comidas registradas solo :n de los últimos 7 días.'],
        'struggle_sessions' => ['en' => 'Training frequency dropped compared with the previous two weeks.', 'ar' => 'انخفض عدد الحصص مقارنة بالأسبوعين السابقين.', 'fr' => 'La fréquence d’entraînement a baissé par rapport aux deux semaines précédentes.', 'es' => 'La frecuencia de entrenamiento bajó respecto a las dos semanas anteriores.'],
        'action_logging' => ['en' => 'Agree on logging at least one meal per day.', 'ar' => 'الاتفاق على تسجيل وجبة واحدة على الأقل يومياً.', 'fr' => 'Convenir de noter au moins un repas par jour.', 'es' => 'Acordar registrar al menos una comida al día.'],
        'action_keep' => ['en' => 'Keep the current program and review again next week.', 'ar' => 'الاستمرار على البرنامج الحالي والمراجعة الأسبوع القادم.', 'fr' => 'Garder le programme actuel et refaire le point la semaine prochaine.', 'es' => 'Mantener el programa actual y revisar la próxima semana.'],
        'checkin_summary' => ['en' => ':name had a steady week with :n sessions completed.', 'ar' => 'كان أسبوع :name مستقراً مع إكمال :n حصص.', 'fr' => ':name a eu une semaine régulière avec :n séances réalisées.', 'es' => ':name tuvo una semana constante con :n sesiones completadas.'],
        'checkin_reply' => ['en' => 'Hi :name, thanks for your check-in! :n sessions in two weeks is solid work. This week let’s focus on logging your meals so we can fine-tune your nutrition.', 'ar' => 'أهلاً :name، شكراً على متابعتك! :n حصص في أسبوعين عمل ممتاز. هذا الأسبوع لنركّز على تسجيل وجباتك حتى نضبط تغذيتك بدقة.', 'fr' => 'Bonjour :name, merci pour ton bilan ! :n séances en deux semaines, c’est du solide. Cette semaine, concentrons-nous sur le suivi de tes repas pour affiner ta nutrition.', 'es' => 'Hola :name, ¡gracias por tu check-in! :n sesiones en dos semanas es un gran trabajo. Esta semana enfoquémonos en registrar tus comidas para ajustar tu nutrición.'],
        'reply' => ['en' => 'Hi :name, thanks for the message! Keep going with the plan this week and send me a quick update after your next session.', 'ar' => 'أهلاً :name، شكراً على رسالتك! استمر على الخطة هذا الأسبوع وأرسل لي تحديثاً سريعاً بعد حصتك القادمة.', 'fr' => 'Bonjour :name, merci pour ton message ! Continue le plan cette semaine et envoie-moi un petit point après ta prochaine séance.', 'es' => 'Hola :name, ¡gracias por tu mensaje! Sigue con el plan esta semana y mándame una actualización después de tu próxima sesión.'],
        'reply_urgent' => ['en' => 'Hi :name, I’m sorry you’re dealing with this. Please pause any exercise that causes pain — I’ll adjust your plan and we’ll talk today. If it’s severe, please see a medical professional.', 'ar' => 'أهلاً :name، يؤسفني ما تمر به. أوقف أي تمرين يسبب الألم — سأعدّل خطتك وسنتحدث اليوم. إذا كان الألم شديداً فيرجى مراجعة مختص طبي.', 'fr' => 'Bonjour :name, désolé que tu traverses ça. Mets en pause tout exercice douloureux — j’adapte ton plan et on en parle aujourd’hui. Si c’est sévère, consulte un professionnel de santé.', 'es' => 'Hola :name, siento que estés pasando por esto. Pausa cualquier ejercicio que cause dolor: ajustaré tu plan y hablamos hoy. Si es fuerte, consulta a un profesional de la salud.'],
        'thread_summary' => ['en' => ':n messages in this conversation. The client is following the plan and asked for adjustments.', 'ar' => ':n رسالة في هذه المحادثة. العميل يلتزم بالخطة وطلب بعض التعديلات.', 'fr' => ':n messages dans cette conversation. Le client suit le plan et a demandé des ajustements.', 'es' => ':n mensajes en esta conversación. El cliente sigue el plan y pidió ajustes.'],
        'churn' => ['en' => 'Hi :name, I’ve missed seeing your updates! How are you doing? Let’s plan one easy win for this week together.', 'ar' => 'أهلاً :name، اشتقت لتحديثاتك! كيف حالك؟ لنخطط معاً لإنجاز سهل هذا الأسبوع.', 'fr' => 'Bonjour :name, tes nouvelles me manquent ! Comment ça va ? Planifions ensemble une petite victoire pour cette semaine.', 'es' => 'Hola :name, ¡echo de menos tus novedades! ¿Cómo estás? Planeemos juntos una meta fácil para esta semana.'],
        'form_commentary' => ['en' => 'Overall solid :exercise technique across :n frames; one point to tidy up.', 'ar' => 'أداء جيد بشكل عام لتمرين :exercise عبر :n لقطات؛ هناك نقطة واحدة للتحسين.', 'fr' => 'Technique de :exercise globalement solide sur :n images ; un point à améliorer.', 'es' => 'Técnica de :exercise sólida en general en :n fotogramas; un punto a mejorar.'],
        'form_feedback' => ['en' => 'Nice work on your :exercise! Your setup looks consistent. Focus on the highlighted point for your next sets and film another set next week.', 'ar' => 'عمل رائع في تمرين :exercise! وضعيتك ثابتة. ركّز على النقطة المحددة في مجموعاتك القادمة وصوّر مجموعة أخرى الأسبوع القادم.', 'fr' => 'Beau travail sur ton :exercise ! Ta mise en place est régulière. Concentre-toi sur le point signalé et filme une autre série la semaine prochaine.', 'es' => '¡Buen trabajo en tu :exercise! Tu posición es constante. Concéntrate en el punto señalado y graba otra serie la próxima semana.'],
        'form_cue' => ['en' => 'Brace before each rep', 'ar' => 'شدّ عضلات الجذع قبل كل تكرار', 'fr' => 'Gainez avant chaque répétition', 'es' => 'Activa el core antes de cada repetición'],
        'form_ok' => ['en' => 'Looks good.', 'ar' => 'يبدو جيداً.', 'fr' => 'C’est bon.', 'es' => 'Se ve bien.'],
        'form_warn' => ['en' => 'Slight deviation in one frame — slow the descent.', 'ar' => 'انحراف بسيط في لقطة واحدة — أبطئ النزول.', 'fr' => 'Léger écart sur une image — ralentissez la descente.', 'es' => 'Ligera desviación en un fotograma: baja más despacio.'],
        'starter_reason' => ['en' => 'Best match for your goal, level and weekly schedule.', 'ar' => 'الأنسب لهدفك ومستواك وجدولك الأسبوعي.', 'fr' => 'Le plus adapté à ton objectif, ton niveau et ton planning.', 'es' => 'El que mejor encaja con tu objetivo, nivel y horario semanal.'],
        'recap_headline' => ['en' => ':name, :n sessions this week — nice consistency!', 'ar' => ':name، :n حصص هذا الأسبوع — التزام رائع!', 'fr' => ':name, :n séances cette semaine — belle régularité !', 'es' => ':name, :n sesiones esta semana: ¡gran constancia!'],
        'recap_volume' => ['en' => ':kg kg total volume lifted.', 'ar' => 'رفعت حجماً إجمالياً قدره :kg كغ.', 'fr' => ':kg kg de volume total soulevé.', 'es' => ':kg kg de volumen total levantado.'],
        'recap_food' => ['en' => 'Meals logged on :n days.', 'ar' => 'سجّلت وجباتك في :n أيام.', 'fr' => 'Repas notés sur :n jours.', 'es' => 'Comidas registradas :n días.'],
        'recap_focus' => ['en' => 'Aim for one extra session and log protein at every meal.', 'ar' => 'استهدف حصة إضافية وسجّل البروتين في كل وجبة.', 'fr' => 'Vise une séance de plus et note les protéines à chaque repas.', 'es' => 'Apunta a una sesión extra y registra la proteína en cada comida.'],
        'recap_encouragement' => ['en' => 'Small steps every week add up. Keep going!', 'ar' => 'الخطوات الصغيرة كل أسبوع تصنع الفرق. واصل!', 'fr' => 'Les petits pas chaque semaine font la différence. Continue !', 'es' => 'Los pequeños pasos cada semana suman. ¡Sigue así!'],
        'chat' => ['en' => 'Good question, :name! Focus on consistency first: train on your planned days, eat protein with every meal and sleep 7–9 hours. Progress steadily — about 0.5 kg per week is a sustainable pace if fat loss is your goal.', 'ar' => 'سؤال جيد يا :name! ركّز أولاً على الاستمرارية: تدرّب في أيامك المحددة، وتناول البروتين في كل وجبة، ونم من 7 إلى 9 ساعات. تقدّم بثبات — حوالي 0.5 كغ أسبوعياً وتيرة مستدامة إذا كان هدفك خسارة الدهون.', 'fr' => 'Bonne question, :name ! Mise d’abord sur la régularité : entraîne-toi les jours prévus, mange des protéines à chaque repas et dors 7 à 9 heures. Avance progressivement — environ 0,5 kg par semaine est un rythme durable si ton objectif est la perte de gras.', 'es' => '¡Buena pregunta, :name! Primero la constancia: entrena los días previstos, come proteína en cada comida y duerme 7–9 horas. Avanza de forma gradual: unos 0,5 kg por semana es un ritmo sostenible si tu objetivo es perder grasa.'],
        'content_caption' => ['en' => 'Strong is built one session at a time. :topic — join :brand and train with a plan that adapts to you.', 'ar' => 'القوة تُبنى حصة بعد حصة. :topic — انضم إلى :brand وتدرّب بخطة تتكيف معك.', 'fr' => 'La force se construit séance après séance. :topic — rejoins :brand et entraîne-toi avec un plan qui s’adapte à toi.', 'es' => 'La fuerza se construye sesión a sesión. :topic: únete a :brand y entrena con un plan que se adapta a ti.'],
        'content_long' => ['en' => ':topic. A structured, coach-designed plan with clear progressions, weekly check-ins and nutrition guidance — built for real schedules. Start today with :brand.', 'ar' => ':topic. خطة منظمة يصممها المدرب مع تدرّج واضح ومتابعات أسبوعية وإرشادات غذائية — مصممة لجدولك الحقيقي. ابدأ اليوم مع :brand.', 'fr' => ':topic. Un plan structuré conçu par ton coach, avec des progressions claires, des bilans hebdomadaires et des conseils nutrition — pensé pour la vraie vie. Commence aujourd’hui avec :brand.', 'es' => ':topic. Un plan estructurado diseñado por tu coach, con progresiones claras, check-ins semanales y guía nutricional, pensado para tu día a día. Empieza hoy con :brand.'],
    ];

    /** @param array<string, mixed> $context */
    public function structured(string $feature, array $context): array
    {
        return match ($feature) {
            'program' => $this->program($context),
            'progression' => $this->progression($context),
            'meal_plan' => $this->mealPlan($context),
            'photo_food' => $this->photoFood($context),
            'checkin_summary' => $this->checkinSummary($context),
            'reply_draft' => $this->replyDraft($context),
            'thread_summary' => $this->threadSummary($context),
            'churn' => $this->churn($context),
            'form_check' => $this->formCheck($context),
            'business_ask' => $this->businessAsk($context),
            'content' => $this->content($context),
            'starter_plan' => $this->starterPlan($context),
            'weekly_recap' => $this->weeklyRecap($context),
            default => ['text' => $this->text($feature, $context)],
        };
    }

    /** @param array<string, mixed> $context */
    public function text(string $feature, array $context): string
    {
        $lang = $context['language'] ?? 'en';

        return $this->t('chat', $lang, ['name' => $context['first_name'] ?? '']);
    }

    public function transcript(string $language): string
    {
        return $this->t('reply', $language, ['name' => '']);
    }

    /** @param array<string, string|int|float> $vars */
    private function t(string $key, string $lang, array $vars = []): string
    {
        $text = self::PHRASES[$key][$lang] ?? self::PHRASES[$key]['en'];
        foreach ($vars as $k => $v) {
            $text = str_replace(':'.$k, (string) $v, $text);
        }

        return trim((string) preg_replace('/\s+([,!?.])/u', '$1', $text));
    }

    /** @param array<string, mixed> $c */
    private function program(array $c): array
    {
        $r = $c['request'];
        $lang = $c['language'] ?? 'en';
        $days = max(1, min(7, (int) $r['days_per_week']));
        $candidates = $c['candidates'];
        $split = match (true) {
            $days <= 3 => array_fill(0, $days, 'full'),
            $days === 4 => ['upper', 'lower', 'upper', 'lower'],
            $days === 5 => ['push', 'pull', 'legs', 'upper', 'lower'],
            default => array_slice(['push', 'pull', 'legs', 'push', 'pull', 'legs', 'full'], 0, $days),
        };
        $patterns = [
            'full' => ['squat', 'push_h', 'pull_h', 'hinge', 'push_v', 'pull_v', 'lunge', 'core'],
            'upper' => ['push_h', 'pull_h', 'push_v', 'pull_v', 'push_h', 'pull_h', 'core'],
            'lower' => ['squat', 'hinge', 'lunge', 'core', 'squat', 'hinge'],
            'push' => ['push_h', 'push_v', 'push_h', 'push_v', 'core'],
            'pull' => ['pull_v', 'pull_h', 'pull_v', 'pull_h', 'core'],
            'legs' => ['squat', 'hinge', 'lunge', 'core', 'squat'],
        ];
        [$sets, $repsMin, $repsMax, $rpe, $rest] = match ($r['goal']) {
            'strength' => [4, 3, 6, 8, 180],
            'muscle_gain' => [3, 8, 12, 8, 90],
            'fat_loss' => [3, 12, 15, 7, 60],
            'performance' => [4, 5, 8, 7.5, 120],
            default => [3, 8, 12, 7, 75],
        };
        $perDay = max(3, min(8, (int) round(((int) $r['session_minutes']) / 9)));
        $dayNumbers = match ($days) {
            1 => [1], 2 => [1, 4], 3 => [1, 3, 5], 4 => [1, 2, 4, 5], 5 => [1, 2, 3, 5, 6], 6 => [1, 2, 3, 4, 5, 6], default => [1, 2, 3, 4, 5, 6, 7],
        };
        $titles = ['full' => 'Full Body', 'upper' => 'Upper', 'lower' => 'Lower', 'push' => 'Push', 'pull' => 'Pull', 'legs' => 'Legs'];

        $outDays = [];
        foreach ($split as $i => $focus) {
            $used = [];
            $exercises = [];
            foreach (array_slice($patterns[$focus], 0, $perDay) as $k => $pattern) {
                $pick = $this->pickExercise($candidates, $pattern, $used, $i + $k);
                if ($pick === null) {
                    continue;
                }
                $used[] = $pick['id'];
                $exercises[] = [
                    'exercise_id' => $pick['id'],
                    'exercise_name' => $pick['name'],
                    'rest_seconds' => $rest,
                    'tempo' => null,
                    'notes' => null,
                    'sets' => array_fill(0, $sets, ['set_type' => 'working', 'reps_min' => $repsMin, 'reps_max' => $repsMax, 'rpe' => $rpe, 'duration_seconds' => null, 'rest_seconds' => $rest]),
                ];
            }
            while (count($exercises) < min(3, count($candidates))) {
                $pick = $this->pickExercise($candidates, null, $used, $i);
                if ($pick === null) {
                    break;
                }
                $used[] = $pick['id'];
                $exercises[] = ['exercise_id' => $pick['id'], 'exercise_name' => $pick['name'], 'rest_seconds' => $rest, 'tempo' => null, 'notes' => null,
                    'sets' => array_fill(0, $sets, ['set_type' => 'working', 'reps_min' => $repsMin, 'reps_max' => $repsMax, 'rpe' => $rpe, 'duration_seconds' => null, 'rest_seconds' => $rest])];
            }
            $title = $titles[$focus].' '.chr(65 + $i);
            $outDays[] = [
                'day_number' => $dayNumbers[$i],
                'title' => $title,
                'workout' => [
                    'name' => $title,
                    'estimated_minutes' => max(10, min(180, (int) $r['session_minutes'])),
                    'blocks' => [['type' => 'straight', 'name' => $this->t('main', $lang), 'rounds' => 1, 'rest_seconds' => $rest, 'exercises' => $exercises]],
                ],
            ];
        }

        $weeks = max(1, min(16, (int) $r['weeks']));
        $goalLabel = ucfirst(str_replace('_', ' ', $r['goal']));

        return [
            'name' => "{$days}-Day ".($days === 4 ? 'Upper/Lower' : ($days === 5 ? 'Push/Pull/Legs' : 'Full Body'))." — {$goalLabel}",
            'description' => $this->t('program_desc', $lang, ['days' => $days]),
            'goal' => in_array($r['goal'], ['fat_loss', 'muscle_gain', 'strength', 'performance', 'health', 'general'], true) ? $r['goal'] : 'general',
            'level' => in_array($r['level'], ['beginner', 'intermediate', 'advanced'], true) ? $r['level'] : 'intermediate',
            'days_per_week' => count($outDays),
            'weeks_count' => $weeks,
            'deload_week' => $weeks >= 4 ? $weeks : null,
            'progression' => [
                'type' => in_array($r['goal'], ['strength', 'muscle_gain'], true) ? 'linear_load' : 'rep_progression',
                'increment_kg' => in_array($r['goal'], ['strength', 'muscle_gain'], true) ? 2.5 : null,
                'notes' => $this->t('progression_notes', $lang),
            ],
            'days' => $outDays,
            'notes' => $this->t('program_notes', $lang),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $candidates
     * @param  list<string>  $used
     * @return array<string, mixed>|null
     */
    private function pickExercise(array $candidates, ?string $pattern, array $used, int $offset): ?array
    {
        $pool = array_values(array_filter($candidates, fn ($e) => ! in_array($e['id'], $used, true) && ($pattern === null || $e['pattern'] === $pattern)));
        if ($pool === []) {
            return null;
        }

        return $pool[$offset % count($pool)];
    }

    /** @param array<string, mixed> $c */
    private function progression(array $c): array
    {
        $lang = $c['language'] ?? 'en';
        $suggestions = array_map(fn (array $b) => [
            'exercise_id' => $b['exercise_id'],
            'exercise_name' => $b['exercise_name'],
            'action' => $b['action'],
            'current' => $b['current'],
            'proposed' => $b['proposed'],
            'swap_exercise_id' => null,
            'rationale' => $this->t('reason_'.$b['reason'], $lang),
        ], $c['baseline']);

        return ['summary' => $this->t('progression_summary', $lang, ['n' => count($suggestions)]), 'suggestions' => $suggestions];
    }

    /** @param array<string, mixed> $c */
    private function mealPlan(array $c): array
    {
        $lang = $c['language'] ?? 'en';
        $t = $c['targets'];
        $groups = ['protein' => [], 'carb' => [], 'fat' => []];
        foreach ($c['foods'] as $f) {
            $groups[$f['group']][] = $f;
        }
        $slots = $c['slots'];
        $main = array_values(array_filter($slots, fn ($s) => $s !== 'snack'));
        $snacks = count($slots) - count($main);
        $mainShare = $snacks > 0 ? 0.8 / max(1, count($main)) : 1 / max(1, count($main));
        $snackShare = $snacks > 0 ? 0.2 / $snacks : 0;

        $days = [];
        for ($d = 0; $d < (int) $c['days']; $d++) {
            $meals = [];
            foreach ($slots as $i => $slot) {
                $share = $slot === 'snack' ? $snackShare : $mainShare;
                $items = [];
                $protein = $this->rotate($groups['protein'], $d + $i);
                $carb = $this->rotate($groups['carb'], $d + $i);
                $fat = $this->rotate($groups['fat'], $d + $i);
                if ($protein) {
                    $items[] = $this->foodItem($protein, $t['protein_g'] * $share * 0.8, 'protein_g');
                }
                if ($carb) {
                    $items[] = $this->foodItem($carb, $t['carbs_g'] * $share * 0.9, 'carbs_g');
                }
                if ($fat && $slot !== 'snack') {
                    $items[] = $this->foodItem($fat, $t['fat_g'] * $share * 0.5, 'fat_g');
                }
                if ($items === []) {
                    continue;
                }
                $meals[] = ['slot' => $slot, 'name' => $this->t('slot_'.$slot, $lang), 'time' => null, 'items' => $items];
            }
            $days[] = ['day_number' => $d + 1, 'label' => $this->t('day', $lang, ['n' => $d + 1]), 'meals' => $meals];
        }

        return [
            'name' => $c['name'] ?? $this->t('meal_plan_name', $lang, ['cuisine' => ucfirst((string) ($c['cuisine'] ?? ''))]),
            'notes' => $this->t('meal_plan_notes', $lang),
            'days' => $days,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $list
     * @return array<string, mixed>|null
     */
    private function rotate(array $list, int $i): ?array
    {
        return $list === [] ? null : $list[$i % count($list)];
    }

    /** @param array<string, mixed> $food */
    private function foodItem(array $food, float $grams, string $macro): array
    {
        $per = (float) ($food['per'][$macro] ?? 0);
        $q = $per > 0 ? $grams / $per : 100;

        return ['food_id' => $food['id'], 'recipe_id' => null, 'quantity' => round(max(5, min(600, $q))), 'unit' => 'g'];
    }

    /** @param array<string, mixed> $c */
    private function photoFood(array $c): array
    {
        return [
            'items' => [
                ['name' => 'grilled chicken breast', 'estimated_grams' => 150, 'confidence' => 0.86, 'kcal' => 248, 'protein_g' => 46.5, 'carbs_g' => 0, 'fat_g' => 5.4],
                ['name' => 'white rice cooked', 'estimated_grams' => 180, 'confidence' => 0.8, 'kcal' => 234, 'protein_g' => 4.9, 'carbs_g' => 50.4, 'fat_g' => 0.5],
                ['name' => 'mixed green salad', 'estimated_grams' => 80, 'confidence' => 0.55, 'kcal' => 16, 'protein_g' => 1.1, 'carbs_g' => 2.9, 'fat_g' => 0.2],
            ],
            'notes' => null,
        ];
    }

    /** @param array<string, mixed> $c */
    private function checkinSummary(array $c): array
    {
        $lang = $c['language'] ?? 'en';
        $m = $c['metrics'];
        $name = $c['first_name'] ?? '';
        $wins = [$this->t('win_sessions', $lang, ['n' => $m['sessions_last_14_days']])];
        if ($m['weight_change_kg'] !== null) {
            $wins[] = $this->t('win_weight', $lang, ['kg' => $m['weight_change_kg']]);
        }
        $struggles = [];
        $actions = [];
        if ($m['food_days_logged_last_7'] < 5) {
            $struggles[] = $this->t('struggle_logging', $lang, ['n' => $m['food_days_logged_last_7']]);
            $actions[] = $this->t('action_logging', $lang);
        }
        if ($m['sessions_last_14_days'] < $m['sessions_previous_14_days']) {
            $struggles[] = $this->t('struggle_sessions', $lang);
        }
        $actions[] = $this->t('action_keep', $lang);

        $trends = [['metric' => 'sessions', 'label' => 'Sessions (14 days)', 'value' => (float) $m['sessions_last_14_days'], 'change' => (float) ($m['sessions_last_14_days'] - $m['sessions_previous_14_days']), 'unit' => 'sessions', 'period' => '14d']];
        if ($m['weight_change_kg'] !== null) {
            $last = end($m['weights']);
            $trends[] = ['metric' => 'weight', 'label' => 'Body weight', 'value' => (float) $last['weight_kg'], 'change' => (float) $m['weight_change_kg'], 'unit' => 'kg', 'period' => 'recent'];
        }
        $risks = array_map(fn (string $flag) => ['type' => $flag, 'severity' => 'high', 'evidence' => 'check-in answers'], $c['safety'] ?? []);

        return [
            'summary' => $this->t('checkin_summary', $lang, ['name' => $name, 'n' => $m['sessions_last_14_days']]),
            'wins' => $wins,
            'struggles' => $struggles,
            'trends' => $trends,
            'risk_flags' => $risks,
            'suggested_actions' => $actions,
            'reply_draft' => $this->t('checkin_reply', $lang, ['name' => $name, 'n' => $m['sessions_last_14_days']]),
            'language' => $lang,
        ];
    }

    /** @param array<string, mixed> $c */
    private function replyDraft(array $c): array
    {
        $lang = $c['language'] ?? 'en';
        $urgent = ($c['priority']['priority'] ?? 'normal') === 'urgent' && array_intersect(['pain_or_injury', 'medical'], $c['priority']['reasons'] ?? []) !== [];

        return ['reply' => $this->t($urgent ? 'reply_urgent' : 'reply', $lang, ['name' => $c['first_name'] ?? '']), 'language' => $lang];
    }

    /** @param array<string, mixed> $c */
    private function threadSummary(array $c): array
    {
        $lang = $c['language'] ?? 'en';
        $urgent = ($c['priority']['priority'] ?? 'normal') === 'urgent';

        return [
            'summary' => $this->t('thread_summary', $lang, ['n' => $c['messages']]),
            'key_points' => array_map(fn (string $r) => str_replace('_', ' ', $r), $c['priority']['reasons'] ?? []),
            'open_questions' => [],
            'action_items' => $urgent ? [$this->t('reply_urgent', $lang, ['name' => $c['first_name'] ?? ''])] : [],
            'sentiment' => $urgent ? 'negative' : 'neutral',
        ];
    }

    /** @param array<string, mixed> $c */
    private function churn(array $c): array
    {
        return ['messages' => array_map(fn (array $client) => [
            'client_key' => $client['client_key'],
            'message' => $this->t('churn', $client['language'] ?? 'en', ['name' => $client['first_name']]),
        ], $c['clients'])];
    }

    /** @param array<string, mixed> $c */
    private function formCheck(array $c): array
    {
        $lang = $c['language'] ?? 'en';
        $rules = $c['rules'] ?? FormRules::DEFAULT;
        $checklist = [];
        foreach ($rules as $i => $rule) {
            $checklist[] = ['rule_key' => $rule, 'status' => $i === 0 ? 'warn' : 'pass', 'frame_index' => $i === 0 ? 0 : null, 'comment' => $this->t($i === 0 ? 'form_warn' : 'form_ok', $lang)];
        }

        return [
            'overall' => 'needs_work',
            'checklist' => $checklist,
            'commentary' => $this->t('form_commentary', $lang, ['exercise' => $c['exercise_name'] ?? '', 'n' => $c['frames'] ?? ($c['image_count'] ?? 1)]),
            'cues' => [$this->t('form_cue', $lang)],
            'draft_feedback' => $this->t('form_feedback', $lang, ['exercise' => $c['exercise_name'] ?? '']),
        ];
    }

    /** @param array<string, mixed> $c */
    private function businessAsk(array $c): array
    {
        $q = TextNormalizer::normalize((string) ($c['question'] ?? ''));
        $map = [
            'unpaid_clients' => ['unpaid', 'not paid', 'hasn t paid', 'hasnt paid', 'haven t paid', 'owe', 'overdue payment', 'impaye', 'pas paye', 'no ha pagado', 'no han pagado', 'impago', 'لم يدفع', 'ما دفع', 'غير مدفوع'],
            'best_retention_program' => ['retention', 'retain', 'fidelisation', 'retencion', 'احتفاظ', 'افضل برنامج', 'best program'],
            'at_risk_clients' => ['at risk', 'churn', 'risque', 'riesgo', 'خطر', 'about to leave', 'losing'],
            'revenue' => ['revenue', 'income', 'earn', 'made this month', 'sales', 'chiffre d affaires', 'revenu', 'ingresos', 'ganado', 'ايرادات', 'دخل', 'مبيعات'],
            'pending_checkins' => ['check in', 'checkin', 'check-ins', 'bilan', 'review', 'pending', 'en attente', 'pendiente', 'متابعه', 'مراجعه'],
        ];
        $tool = 'unsupported';
        foreach ($map as $candidate => $words) {
            foreach ($words as $w) {
                if (str_contains($q, TextNormalizer::normalize($w))) {
                    $tool = $candidate;
                    break 2;
                }
            }
        }
        $last = (bool) preg_match('/last month|previous month|mois dernier|mes pasado|الشهر الماضي/u', $q);

        return ['tool' => $tool, 'period' => $last ? 'last_month' : 'this_month', 'limit' => null, 'reasoning' => "Matched question to {$tool}."];
    }

    /** @param array<string, mixed> $c */
    private function content(array $c): array
    {
        $lang = $c['language'] ?? 'en';
        $vars = ['topic' => (string) ($c['topic'] ?? ''), 'brand' => (string) ($c['brand'] ?? '')];
        $short = in_array($c['type'], ['instagram_caption', 'story'], true);

        return [
            'title' => $short ? null : (string) ($c['topic'] ?? $c['brand'] ?? ''),
            'body' => $this->t($short ? 'content_caption' : 'content_long', $lang, $vars),
            'hashtags' => $short ? ['#fitness', '#coaching', '#'.preg_replace('/\W+/u', '', (string) ($c['brand'] ?? 'training'))] : [],
            'language' => $lang,
        ];
    }

    /** @param array<string, mixed> $c */
    private function starterPlan(array $c): array
    {
        $r = $c['request'];
        $best = null;
        $bestScore = -INF;
        foreach ($c['templates'] as $tpl) {
            $score = ($tpl['goal'] === ($r['goal'] ?? null) ? 3 : 0)
                + ($tpl['level'] === ($r['level'] ?? null) ? 2 : 0)
                - (isset($r['days_per_week']) ? abs((int) $tpl['days_per_week'] - (int) $r['days_per_week']) : 0);
            if ($score > $bestScore) {
                [$best, $bestScore] = [$tpl, $score];
            }
        }

        return ['template_id' => $best['id'], 'reason' => $this->t('starter_reason', $c['language'] ?? 'en')];
    }

    /** @param array<string, mixed> $c */
    private function weeklyRecap(array $c): array
    {
        $lang = $c['language'] ?? 'en';
        $s = $c['stats'];
        $highlights = [];
        if ($s['total_volume_kg'] > 0) {
            $highlights[] = $this->t('recap_volume', $lang, ['kg' => $s['total_volume_kg']]);
        }
        if ($s['food_days_logged'] > 0) {
            $highlights[] = $this->t('recap_food', $lang, ['n' => $s['food_days_logged']]);
        }

        return [
            'headline' => $this->t('recap_headline', $lang, ['name' => $c['first_name'] ?? '', 'n' => $s['sessions_completed']]),
            'highlights' => $highlights,
            'focus_next_week' => $this->t('recap_focus', $lang),
            'encouragement' => $this->t('recap_encouragement', $lang),
        ];
    }
}
