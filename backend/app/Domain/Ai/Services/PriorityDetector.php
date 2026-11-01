<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Support\TextNormalizer;

/**
 * Inbox priority: urgent for pain/injury/medical/safety language or
 * cancellation intent (en/ar/fr/es), high for scheduling/payment problems.
 */
final class PriorityDetector
{
    private const CANCELLATION = [
        'cancel my', 'cancel the', 'want to cancel', 'cancelling', 'quit coaching', 'stop my subscription', 'end my subscription', 'refund', 'i want to stop', "i'm done with",
        'annuler', 'résilier', 'resilier mon abonnement', 'arrêter le coaching', 'arreter mon abonnement', 'remboursement', 'je veux arrêter',
        'cancelar', 'darme de baja', 'dar de baja', 'reembolso', 'quiero dejarlo', 'dejar el coaching',
        'إلغاء', 'الغاء', 'ألغي', 'الغي', 'استرداد', 'إيقاف الاشتراك', 'ايقاف الاشتراك', 'أريد التوقف', 'اريد التوقف',
    ];

    private const PAYMENT_OR_SCHEDULE = [
        'payment failed', 'card declined', 'charged twice', 'reschedule', "can't make it",
        'paiement refusé', 'carte refusée', 'débité deux fois', 'reporter la séance',
        'pago rechazado', 'tarjeta rechazada', 'cobrado dos veces', 'reprogramar',
        'فشل الدفع', 'البطاقة مرفوضة', 'خصم مرتين', 'تأجيل الموعد',
    ];

    public function __construct(private readonly SafetyGuard $guard) {}

    /** @return array{priority: string, reasons: list<string>} */
    public function detect(string $text): array
    {
        $reasons = [];
        $safety = $this->guard->screenText($text);
        foreach (['self_harm', 'eating_disorder', 'medical', 'injury'] as $c) {
            if ($safety->has($c)) {
                $reasons[] = $c === 'medical' ? 'medical' : ($c === 'injury' ? 'pain_or_injury' : $c);
            }
        }
        if ($this->contains($text, self::CANCELLATION)) {
            $reasons[] = 'cancellation_intent';
        }
        if ($reasons !== []) {
            return ['priority' => 'urgent', 'reasons' => $reasons];
        }
        if ($this->contains($text, self::PAYMENT_OR_SCHEDULE)) {
            return ['priority' => 'high', 'reasons' => ['payment_or_schedule']];
        }

        return ['priority' => 'normal', 'reasons' => []];
    }

    /** @param list<string> $phrases */
    private function contains(string $text, array $phrases): bool
    {
        $t = TextNormalizer::normalize($text);
        foreach ($phrases as $phrase) {
            $p = TextNormalizer::normalize($phrase);
            if ($p !== '' && preg_match(SafetyGuard::wordPattern($p), $t)) {
                return true;
            }
        }

        return false;
    }
}
