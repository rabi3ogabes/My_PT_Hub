<?php

namespace App\Domain\Tenancy\Services;

final class DefaultContent
{
    /** @return list<array<string, mixed>> */
    public static function weeklyCheckinQuestions(): array
    {
        return [
            ['id' => 'energy', 'type' => 'scale', 'label' => ['en' => 'Energy levels this week', 'ar' => 'مستوى الطاقة هذا الأسبوع', 'fr' => 'Niveau d’énergie cette semaine', 'es' => 'Nivel de energía esta semana'], 'min' => 1, 'max' => 10, 'required' => true],
            ['id' => 'sleep', 'type' => 'scale', 'label' => ['en' => 'Sleep quality', 'ar' => 'جودة النوم', 'fr' => 'Qualité du sommeil', 'es' => 'Calidad del sueño'], 'min' => 1, 'max' => 10, 'required' => true],
            ['id' => 'stress', 'type' => 'scale', 'label' => ['en' => 'Stress levels', 'ar' => 'مستوى التوتر', 'fr' => 'Niveau de stress', 'es' => 'Nivel de estrés'], 'min' => 1, 'max' => 10, 'required' => true],
            ['id' => 'adherence', 'type' => 'scale', 'label' => ['en' => 'How well did you stick to the plan?', 'ar' => 'إلى أي مدى التزمت بالخطة؟', 'fr' => 'Avez-vous respecté le plan ?', 'es' => '¿Cuánto cumpliste el plan?'], 'min' => 1, 'max' => 10, 'required' => true],
            ['id' => 'wins', 'type' => 'long_text', 'label' => ['en' => 'Biggest win this week', 'ar' => 'أكبر إنجاز هذا الأسبوع', 'fr' => 'Plus belle victoire de la semaine', 'es' => 'Mayor logro de la semana'], 'required' => false],
            ['id' => 'struggles', 'type' => 'long_text', 'label' => ['en' => 'What was hard?', 'ar' => 'ما الذي كان صعبًا؟', 'fr' => 'Qu’est-ce qui a été difficile ?', 'es' => '¿Qué fue difícil?'], 'required' => false],
            ['id' => 'weight', 'type' => 'measurement', 'label' => ['en' => 'Morning body weight', 'ar' => 'الوزن الصباحي', 'fr' => 'Poids du matin', 'es' => 'Peso por la mañana'], 'unit' => 'kg', 'required' => false],
            ['id' => 'photos', 'type' => 'photo', 'label' => ['en' => 'Progress photos (front, side, back)', 'ar' => 'صور التقدم (أمام، جانب، خلف)', 'fr' => 'Photos de progression (face, profil, dos)', 'es' => 'Fotos de progreso (frente, lado, espalda)'], 'required' => false],
        ];
    }
}
