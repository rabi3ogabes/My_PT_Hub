<?php

namespace App\Domain\Ai\Support;

/** Rule checklist per movement pattern for AI form checks. */
final class FormRules
{
    public const BY_PATTERN = [
        'squat' => ['depth', 'knee_tracking', 'heels_down', 'neutral_spine', 'torso_angle'],
        'hinge' => ['neutral_spine', 'hip_hinge', 'bar_close', 'lockout', 'knee_bend'],
        'push_h' => ['elbow_angle', 'scapular_retraction', 'wrist_stack', 'full_rom', 'bar_path'],
        'push_v' => ['core_bracing', 'bar_path', 'lockout', 'rib_flare'],
        'pull_h' => ['neutral_spine', 'scapular_retraction', 'full_rom', 'torso_stability'],
        'pull_v' => ['full_rom', 'scapular_depression', 'no_kipping', 'controlled_eccentric'],
        'lunge' => ['knee_tracking', 'torso_upright', 'step_length', 'balance'],
        'carry' => ['posture', 'core_bracing', 'grip'],
        'core' => ['neutral_spine', 'breathing', 'hip_position'],
        'rotation' => ['neutral_spine', 'hip_drive', 'control'],
    ];

    public const DEFAULT = ['posture', 'range_of_motion', 'control', 'tempo'];

    /** @return list<string> */
    public static function for(?string $pattern): array
    {
        return self::BY_PATTERN[$pattern] ?? self::DEFAULT;
    }

    /** @return list<string> */
    public static function all(): array
    {
        return array_values(array_unique(array_merge(self::DEFAULT, ...array_values(self::BY_PATTERN))));
    }
}
