<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Models\AiSetting;
use App\Domain\Ai\Support\TextNormalizer;

/** Applies the coach's voice rules to drafted text (banned words, emoji, sign-off). */
final class ToneEnforcer
{
    /** @return array<string, mixed> template variables describing the coach's voice */
    public function variables(AiSetting $s): array
    {
        return [
            'tone' => $s->tone ?: 'motivational',
            'sign_off' => $s->sign_off ?: '-',
            'use_emoji' => (bool) ($s->use_emoji ?? true),
            'philosophy' => $s->philosophy ?: '-',
            'banned_words' => array_values($s->banned_words ?? []),
            'sample_replies' => array_slice(array_values($s->sample_replies ?? []), 0, 3),
        ];
    }

    /** @return list<string> banned words present in the text */
    public function bannedWordsIn(string $text, AiSetting $s): array
    {
        $normalized = ' '.TextNormalizer::normalize($text).' ';
        $found = [];
        foreach ($s->banned_words ?? [] as $word) {
            $w = TextNormalizer::normalize((string) $word);
            if ($w !== '' && preg_match(SafetyGuard::wordPattern($w), $normalized)) {
                $found[] = (string) $word;
            }
        }

        return $found;
    }

    /** Final polish once validation passed: strip emoji when disabled, ensure sign-off. */
    public function polish(string $text, AiSetting $s): string
    {
        if (! ($s->use_emoji ?? true)) {
            $text = (string) preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}\x{200D}]/u', '', $text);
            $text = (string) preg_replace('/[ \t]{2,}/', ' ', $text);
        }
        $signOff = trim((string) $s->sign_off);
        if ($signOff !== '' && ! str_contains(TextNormalizer::normalize($text), TextNormalizer::normalize($signOff))) {
            $text = rtrim($text)."\n\n".$signOff;
        }

        return trim($text);
    }
}
