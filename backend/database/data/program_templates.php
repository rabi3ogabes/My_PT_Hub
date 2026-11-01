<?php

/*
| Platform-public starter programs (seeded by Database\Seeders\Modules\ProgramTemplateSeeder).
|
| workouts: key => [name, difficulty, tags, blocks]
|   block: ['type' => straight|superset|giant|circuit|tabata|emom|amrap|interval|warmup|cooldown, ...block fields, 'ex' => rows]
|   row:   [exercise slug, set spec, rest seconds?, progression?]
|   set spec: comma-separated segments "[w|a|d|f]NxVALUE[@PCT%|@RPE]" — w warm-up, a AMRAP, d drop, f failure;
|             VALUE = reps "8" / range "8-12" / time "30s" / distance "400m".
|   progression: linear (+2.5 kg), linear5 (+5 kg), reps (8–12 double progression), pct (5/3/1-style % per week).
|
| programs: [slug, names[en,ar,fr,es], desc[en,ar,fr,es], goal, level, days_per_week, equipment, tags,
|            weeks, deload weeks, phase, days {day_number: workout key}, days_b (alternate weeks, optional)]
*/

$workouts = [
    // ---------------------------------------------------------------- Beginner full body
    'fb_a' => ['Full Body A', 'beginner', ['full_body', 'beginner'], [
        ['type' => 'warmup', 'name' => 'Warm-up', 'ex' => [['worlds-greatest-stretch', '1x6'], ['bodyweight-squat', '1x10']]],
        ['type' => 'straight', 'ex' => [['goblet-squat', '3x8-12', 90, 'reps'], ['dumbbell-bench-press', '3x8-12', 90, 'reps'], ['dumbbell-row', '3x10-12', 75, 'reps']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['glute-bridge', '3x12'], ['plank', '3x30s']]],
        ['type' => 'cooldown', 'ex' => [['childs-pose', '1x60s']]],
    ]],
    'fb_b' => ['Full Body B', 'beginner', ['full_body', 'beginner'], [
        ['type' => 'warmup', 'ex' => [['cat-cow', '1x8'], ['jumping-jack', '1x30s']]],
        ['type' => 'straight', 'ex' => [['dumbbell-romanian-deadlift', '3x8-12', 90, 'reps'], ['lat-pulldown', '3x8-12', 90, 'reps'], ['dumbbell-shoulder-press', '3x8-12', 90, 'reps']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['reverse-lunge', '3x10'], ['dead-bug', '3x10']]],
        ['type' => 'cooldown', 'ex' => [['hip-flexor-stretch', '1x45s']]],
    ]],
    // ---------------------------------------------------------------- Push / Pull / Legs
    'push_1' => ['Push — Chest, Shoulders, Triceps', 'intermediate', ['push', 'hypertrophy'], [
        ['type' => 'warmup', 'ex' => [['band-pull-apart', '2x15'], ['push-up', '1x10']]],
        ['type' => 'straight', 'ex' => [['barbell-bench-press', 'w1x8,4x6-8', 150, 'linear'], ['overhead-press', '3x6-8', 120, 'linear'], ['incline-dumbbell-press', '3x8-12', 90, 'reps']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 75, 'ex' => [['lateral-raise', '3x12-15'], ['triceps-pushdown', '3x10-15']]],
        ['type' => 'straight', 'ex' => [['overhead-triceps-extension', '2x10-12', 60]]],
    ]],
    'pull_1' => ['Pull — Back & Biceps', 'intermediate', ['pull', 'hypertrophy'], [
        ['type' => 'warmup', 'ex' => [['dead-hang', '1x30s'], ['scapular-pull-up', '1x8']]],
        ['type' => 'straight', 'ex' => [['deadlift', 'w1x5,3x5', 180, 'linear5'], ['pull-up', '4x6-10', 120, 'reps'], ['barbell-row', '3x8-10', 90, 'linear']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 75, 'ex' => [['face-pull', '3x15'], ['barbell-curl', '3x8-12']]],
        ['type' => 'straight', 'ex' => [['hammer-curl', '2x10-12', 60]]],
    ]],
    'legs_1' => ['Legs — Quads, Hamstrings, Glutes', 'intermediate', ['legs', 'hypertrophy'], [
        ['type' => 'warmup', 'ex' => [['ankle-mobility-drill', '1x10'], ['bodyweight-squat', '1x15']]],
        ['type' => 'straight', 'ex' => [['back-squat', 'w1x8,4x6-8', 180, 'linear'], ['romanian-deadlift', '3x8-10', 120, 'linear'], ['leg-press', '3x10-12', 90, 'reps']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['leg-curl', '3x10-12'], ['standing-calf-raise', '3x12-15']]],
        ['type' => 'straight', 'ex' => [['hanging-leg-raise', '3x10', 60]]],
    ]],
    // ---------------------------------------------------------------- Upper / Lower
    'upper_a' => ['Upper A — Strength', 'intermediate', ['upper', 'strength'], [
        ['type' => 'warmup', 'ex' => [['band-shoulder-dislocate', '1x10'], ['band-pull-apart', '1x15']]],
        ['type' => 'straight', 'ex' => [['barbell-bench-press', 'w1x8,4x5', 180, 'linear'], ['barbell-row', '4x6', 120, 'linear']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 90, 'ex' => [['dumbbell-shoulder-press', '3x8-10'], ['chin-up', '3x6-8']]],
        ['type' => 'superset', 'rounds' => 2, 'rest_between_rounds' => 60, 'ex' => [['skull-crusher', '2x10'], ['dumbbell-curl', '2x10']]],
    ]],
    'lower_a' => ['Lower A — Strength', 'intermediate', ['lower', 'strength'], [
        ['type' => 'warmup', 'ex' => [['worlds-greatest-stretch', '1x6'], ['glute-bridge', '1x12']]],
        ['type' => 'straight', 'ex' => [['back-squat', 'w1x5,4x5', 180, 'linear'], ['romanian-deadlift', '3x8', 120, 'linear']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 75, 'ex' => [['walking-lunge', '3x10'], ['standing-calf-raise', '3x12']]],
        ['type' => 'straight', 'ex' => [['ab-wheel-rollout', '3x8', 60]]],
    ]],
    'upper_b' => ['Upper B — Hypertrophy', 'intermediate', ['upper', 'hypertrophy'], [
        ['type' => 'straight', 'ex' => [['incline-dumbbell-press', '4x8-12', 90, 'reps'], ['lat-pulldown', '4x8-12', 90, 'reps'], ['seated-cable-row', '3x10-12', 75, 'reps']]],
        ['type' => 'giant', 'rounds' => 3, 'rest_between_rounds' => 90, 'ex' => [['lateral-raise', '3x15'], ['rear-delt-fly', '3x15'], ['cable-crossover', '3x12']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['triceps-pushdown', '3x12'], ['incline-dumbbell-curl', '3x12']]],
    ]],
    'lower_b' => ['Lower B — Hypertrophy', 'intermediate', ['lower', 'hypertrophy'], [
        ['type' => 'straight', 'ex' => [['front-squat', '3x8', 150, 'linear'], ['hip-thrust', '4x8-12', 90, 'reps'], ['bulgarian-split-squat', '3x10', 90]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['leg-extension', '3x12-15'], ['leg-curl', '3x12-15']]],
        ['type' => 'straight', 'ex' => [['seated-calf-raise', '3x15', 45], ['side-plank', '2x30s', 30]]],
    ]],
    // ---------------------------------------------------------------- 5/3/1-style
    'w531_squat' => ['5/3/1 Squat Day', 'advanced', ['strength', '531'], [
        ['type' => 'warmup', 'ex' => [['back-squat', 'w1x5@40%,w1x5@50%,w1x3@60%', 90]]],
        ['type' => 'straight', 'name' => 'Main lift', 'ex' => [['back-squat', '2x5@65%,a1x5@65%', 180, 'pct']]],
        ['type' => 'straight', 'name' => 'Boring But Big', 'ex' => [['back-squat', '5x10@50%', 120]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['leg-curl', '3x10'], ['hanging-leg-raise', '3x10']]],
    ]],
    'w531_bench' => ['5/3/1 Bench Day', 'advanced', ['strength', '531'], [
        ['type' => 'warmup', 'ex' => [['barbell-bench-press', 'w1x5@40%,w1x5@50%,w1x3@60%', 90]]],
        ['type' => 'straight', 'name' => 'Main lift', 'ex' => [['barbell-bench-press', '2x5@65%,a1x5@65%', 180, 'pct']]],
        ['type' => 'straight', 'name' => 'Boring But Big', 'ex' => [['barbell-bench-press', '5x10@50%', 90]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['dumbbell-row', '3x10'], ['face-pull', '3x15']]],
    ]],
    'w531_deadlift' => ['5/3/1 Deadlift Day', 'advanced', ['strength', '531'], [
        ['type' => 'warmup', 'ex' => [['deadlift', 'w1x5@40%,w1x5@50%,w1x3@60%', 90]]],
        ['type' => 'straight', 'name' => 'Main lift', 'ex' => [['deadlift', '2x5@65%,a1x5@65%', 180, 'pct']]],
        ['type' => 'straight', 'name' => 'Boring But Big', 'ex' => [['deadlift', '5x10@50%', 120]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['walking-lunge', '3x10'], ['ab-wheel-rollout', '3x8']]],
    ]],
    'w531_ohp' => ['5/3/1 Press Day', 'advanced', ['strength', '531'], [
        ['type' => 'warmup', 'ex' => [['overhead-press', 'w1x5@40%,w1x5@50%,w1x3@60%', 90]]],
        ['type' => 'straight', 'name' => 'Main lift', 'ex' => [['overhead-press', '2x5@65%,a1x5@65%', 180, 'pct']]],
        ['type' => 'straight', 'name' => 'Boring But Big', 'ex' => [['overhead-press', '5x10@50%', 90]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['chin-up', '3x8'], ['dumbbell-curl', '3x12']]],
    ]],
    // ---------------------------------------------------------------- Home, no equipment
    'home_a' => ['Home Strength Circuit', 'beginner', ['home', 'no_equipment'], [
        ['type' => 'warmup', 'ex' => [['jumping-jack', '1x45s'], ['cat-cow', '1x8']]],
        ['type' => 'circuit', 'rounds' => 4, 'rest_seconds' => 15, 'rest_between_rounds' => 90, 'ex' => [['bodyweight-squat', '4x15'], ['push-up', '4x8-12'], ['reverse-lunge', '4x10'], ['superman', '4x12'], ['plank', '4x30s']]],
        ['type' => 'cooldown', 'ex' => [['figure-four-stretch', '1x45s']]],
    ]],
    'home_b' => ['Home Tabata Burner', 'intermediate', ['home', 'no_equipment', 'conditioning'], [
        ['type' => 'warmup', 'ex' => [['high-knees', '1x30s'], ['worlds-greatest-stretch', '1x6']]],
        ['type' => 'tabata', 'name' => 'Tabata 1', 'rounds' => 8, 'work_seconds' => 20, 'rest_seconds' => 10, 'rest_between_rounds' => 60, 'ex' => [['jump-squat', ''], ['mountain-climber', '']]],
        ['type' => 'tabata', 'name' => 'Tabata 2', 'rounds' => 8, 'work_seconds' => 20, 'rest_seconds' => 10, 'rest_between_rounds' => 60, 'ex' => [['burpee', ''], ['russian-twist', '']]],
        ['type' => 'cooldown', 'ex' => [['childs-pose', '1x60s']]],
    ]],
    'home_c' => ['Home Lower Body & Core', 'beginner', ['home', 'no_equipment'], [
        ['type' => 'straight', 'ex' => [['split-squat', '3x10', 60], ['single-leg-glute-bridge', '3x12', 45], ['wall-sit', '3x40s', 45]]],
        ['type' => 'emom', 'rounds' => 10, 'work_seconds' => 60, 'ex' => [['bodyweight-squat', '5x15'], ['dead-bug', '5x10']]],
    ]],
    // ---------------------------------------------------------------- Dumbbell-only hypertrophy
    'db_upper_a' => ['DB Upper A', 'intermediate', ['dumbbell_only', 'hypertrophy'], [
        ['type' => 'straight', 'ex' => [['dumbbell-bench-press', '4x8-12', 90, 'reps'], ['dumbbell-row', '4x8-12', 90, 'reps'], ['dumbbell-shoulder-press', '3x8-12', 90, 'reps']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['dumbbell-fly', '3x12-15'], ['rear-delt-fly', '3x12-15']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['dumbbell-curl', '3x10-12'], ['overhead-triceps-extension', '3x10-12']]],
    ]],
    'db_lower_a' => ['DB Lower A', 'intermediate', ['dumbbell_only', 'hypertrophy'], [
        ['type' => 'straight', 'ex' => [['goblet-squat', '4x10-15', 90, 'reps'], ['dumbbell-romanian-deadlift', '4x8-12', 90, 'reps'], ['bulgarian-split-squat', '3x8-12', 90]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['single-leg-hip-thrust', '3x12'], ['single-leg-calf-raise', '3x15']]],
    ]],
    'db_upper_b' => ['DB Upper B', 'intermediate', ['dumbbell_only', 'hypertrophy'], [
        ['type' => 'straight', 'ex' => [['incline-dumbbell-press', '4x8-12', 90, 'reps'], ['chest-supported-row', '4x10-12', 90, 'reps'], ['arnold-press', '3x10-12', 75]]],
        ['type' => 'giant', 'rounds' => 3, 'rest_between_rounds' => 75, 'ex' => [['lateral-raise', '3x15'], ['hammer-curl', '3x12'], ['floor-press-dumbbell', '3x12']]],
    ]],
    'db_lower_b' => ['DB Lower B', 'intermediate', ['dumbbell_only', 'hypertrophy'], [
        ['type' => 'straight', 'ex' => [['walking-lunge', '4x10-12', 90], ['single-leg-romanian-deadlift', '3x10', 75], ['step-up', '3x10', 75]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['dumbbell-swing', '3x15'], ['plank', '3x45s']]],
    ]],
    // ---------------------------------------------------------------- Calisthenics
    'cali_push' => ['Calisthenics Push', 'intermediate', ['calisthenics', 'bodyweight'], [
        ['type' => 'warmup', 'ex' => [['band-shoulder-dislocate', '1x10'], ['incline-push-up', '1x10']]],
        ['type' => 'straight', 'ex' => [['pike-push-up', '4x6-10', 90, 'reps'], ['chest-dip', '4x6-10', 90, 'reps'], ['archer-push-up', '3x5', 90]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['diamond-push-up', '3x8-12'], ['hollow-body-hold', '3x20s']]],
    ]],
    'cali_pull' => ['Calisthenics Pull', 'intermediate', ['calisthenics', 'bodyweight'], [
        ['type' => 'warmup', 'ex' => [['dead-hang', '1x30s'], ['scapular-pull-up', '2x8']]],
        ['type' => 'straight', 'ex' => [['pull-up', '4x5-8', 120, 'reps'], ['inverted-row', '4x8-12', 90, 'reps'], ['negative-pull-up', '2x3', 90]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['chin-up', '3x6'], ['tuck-l-sit', '3x15s']]],
    ]],
    'cali_legs' => ['Calisthenics Legs & Core', 'intermediate', ['calisthenics', 'bodyweight'], [
        ['type' => 'straight', 'ex' => [['assisted-pistol-squat', '4x5', 90], ['nordic-curl', '3x4', 120], ['jump-squat', '3x8', 60]]],
        ['type' => 'circuit', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['hanging-knee-raise', '3x10'], ['side-plank', '3x30s'], ['single-leg-calf-raise', '3x15']]],
    ]],
    // ---------------------------------------------------------------- HYROX
    'hyrox_strength' => ['HYROX Strength', 'intermediate', ['hyrox', 'strength'], [
        ['type' => 'straight', 'ex' => [['back-squat', '4x6', 150, 'linear'], ['deadlift', '3x5', 150, 'linear5']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 90, 'ex' => [['sandbag-lunge', '3x20m'], ['sled-push', '3x25m']]],
        ['type' => 'straight', 'ex' => [['farmers-carry', '3x50m', 90]]],
    ]],
    'hyrox_engine' => ['HYROX Engine Intervals', 'intermediate', ['hyrox', 'conditioning'], [
        ['type' => 'warmup', 'ex' => [['rowing-machine', '1x500m']]],
        ['type' => 'interval', 'name' => 'Run intervals', 'rounds' => 6, 'work_seconds' => 180, 'rest_seconds' => 90, 'ex' => [['treadmill-run', '6x800m']]],
        ['type' => 'emom', 'rounds' => 12, 'work_seconds' => 60, 'ex' => [['ski-erg', '4x200m'], ['wall-ball', '4x15'], ['burpee-broad-jump', '4x8']]],
    ]],
    'hyrox_sim' => ['HYROX Half Simulation', 'advanced', ['hyrox', 'simulation'], [
        ['type' => 'circuit', 'name' => 'Run + station ×4', 'rounds' => 4, 'rest_seconds' => 0, 'rest_between_rounds' => 0, 'ex' => [['outdoor-run', '4x1000m'], ['ski-erg', '1x1000m'], ['sled-pull', '1x50m'], ['burpee-broad-jump', '1x40m'], ['rowing-machine', '1x1000m']]],
        ['type' => 'cooldown', 'ex' => [['hamstring-stretch', '1x45s'], ['hip-flexor-stretch', '1x45s']]],
    ]],
    // ---------------------------------------------------------------- Ramadan-friendly, low volume
    'ramadan_a' => ['Ramadan Strength (30 min)', 'beginner', ['ramadan', 'low_volume'], [
        ['type' => 'warmup', 'ex' => [['cat-cow', '1x8']]],
        ['type' => 'straight', 'ex' => [['goblet-squat', '2x8-10', 120], ['dumbbell-bench-press', '2x8-10', 120], ['dumbbell-row', '2x8-10', 120]]],
        ['type' => 'cooldown', 'ex' => [['childs-pose', '1x60s']]],
    ]],
    'ramadan_b' => ['Ramadan Mobility & Core (25 min)', 'beginner', ['ramadan', 'low_volume', 'mobility'], [
        ['type' => 'straight', 'ex' => [['dumbbell-romanian-deadlift', '2x10', 120], ['band-pull-apart', '2x15', 60], ['glute-bridge', '2x12', 60]]],
        ['type' => 'cooldown', 'ex' => [['worlds-greatest-stretch', '1x6'], ['dead-bug', '2x8'], ['thoracic-rotation', '1x8']]],
    ]],
    // ---------------------------------------------------------------- Glute focus
    'glute_a' => ['Glutes & Hamstrings', 'intermediate', ['glutes', 'lower'], [
        ['type' => 'warmup', 'ex' => [['banded-lateral-walk', '2x12'], ['glute-bridge', '1x15']]],
        ['type' => 'straight', 'ex' => [['hip-thrust', 'w1x10,4x8-12', 120, 'reps'], ['romanian-deadlift', '3x8-10', 120, 'linear'], ['bulgarian-split-squat', '3x10', 90]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['cable-pull-through', '3x15'], ['hip-abduction-machine', '3x15-20']]],
    ]],
    'glute_b' => ['Glutes & Quads', 'intermediate', ['glutes', 'lower'], [
        ['type' => 'straight', 'ex' => [['back-squat', '4x6-8', 150, 'linear'], ['walking-lunge', '3x12', 90], ['leg-press', '3x12-15', 90, 'reps']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['single-leg-glute-bridge', '3x12'], ['banded-lateral-walk', '3x15']]],
    ]],
    'glute_c' => ['Upper Body & Glute Pump', 'beginner', ['glutes', 'upper'], [
        ['type' => 'straight', 'ex' => [['lat-pulldown', '3x10-12', 75, 'reps'], ['dumbbell-shoulder-press', '3x10-12', 75], ['seated-cable-row', '3x10-12', 75]]],
        ['type' => 'giant', 'rounds' => 3, 'rest_between_rounds' => 75, 'ex' => [['glute-bridge', '3x20'], ['hip-abduction-machine', '3x20'], ['cable-pull-through', '3x15']]],
    ]],
    // ---------------------------------------------------------------- Kettlebell
    'kb_a' => ['Kettlebell Power', 'intermediate', ['kettlebell'], [
        ['type' => 'warmup', 'ex' => [['kettlebell-halo', '1x8'], ['kettlebell-deadlift', '1x10']]],
        ['type' => 'emom', 'rounds' => 10, 'work_seconds' => 60, 'ex' => [['kettlebell-swing', '10x15']]],
        ['type' => 'straight', 'ex' => [['kettlebell-goblet-squat', '3x10', 75], ['kettlebell-press', '3x6-8', 75], ['kettlebell-row', '3x10', 60]]],
    ]],
    'kb_b' => ['Kettlebell Complex', 'intermediate', ['kettlebell', 'conditioning'], [
        ['type' => 'amrap', 'time_cap_seconds' => 900, 'ex' => [['kettlebell-clean', '1x5'], ['kettlebell-press', '1x5'], ['kettlebell-goblet-squat', '1x10'], ['kettlebell-swing', '1x15']]],
        ['type' => 'straight', 'ex' => [['turkish-get-up', '3x2', 60], ['suitcase-carry', '3x40m', 60]]],
    ]],
    // ---------------------------------------------------------------- Linear strength 5×5
    'sl_a' => ['Strength A', 'beginner', ['strength', '5x5'], [
        ['type' => 'straight', 'ex' => [['back-squat', 'w2x5,5x5', 180, 'linear'], ['barbell-bench-press', 'w1x5,5x5', 180, 'linear'], ['barbell-row', '5x5', 150, 'linear']]],
    ]],
    'sl_b' => ['Strength B', 'beginner', ['strength', '5x5'], [
        ['type' => 'straight', 'ex' => [['back-squat', 'w2x5,5x5', 180, 'linear'], ['overhead-press', 'w1x5,5x5', 180, 'linear'], ['deadlift', 'w1x5,1x5', 180, 'linear5']]],
    ]],
    // ---------------------------------------------------------------- Conditioning
    'hiit_a' => ['HIIT Fat Burner', 'intermediate', ['conditioning', 'fat_loss'], [
        ['type' => 'warmup', 'ex' => [['jump-rope', '1x120s']]],
        ['type' => 'interval', 'rounds' => 8, 'work_seconds' => 40, 'rest_seconds' => 20, 'ex' => [['assault-bike', '8x40s'], ['battle-ropes', '8x40s']]],
        ['type' => 'circuit', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['dumbbell-swing', '3x15'], ['thruster-dumbbell', '3x10'], ['mountain-climber', '3x30s']]],
    ]],
    'metcon_b' => ['Metcon AMRAP 20', 'intermediate', ['conditioning', 'fat_loss'], [
        ['type' => 'amrap', 'time_cap_seconds' => 1200, 'ex' => [['rowing-machine', '1x250m'], ['goblet-squat', '1x12'], ['push-up', '1x10'], ['medicine-ball-slam', '1x10']]],
        ['type' => 'cooldown', 'ex' => [['pigeon-stretch', '1x45s']]],
    ]],
    // ---------------------------------------------------------------- Bro split
    'chest_day' => ['Chest Day', 'intermediate', ['chest', 'split'], [
        ['type' => 'straight', 'ex' => [['barbell-bench-press', 'w1x10,4x6-10', 150, 'linear'], ['incline-dumbbell-press', '3x8-12', 90, 'reps'], ['machine-chest-press', '3x10-12', 90]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['cable-crossover', '3x12-15'], ['chest-dip', 'f3x10']]],
    ]],
    'back_day' => ['Back Day', 'intermediate', ['back', 'split'], [
        ['type' => 'straight', 'ex' => [['pull-up', '4x6-10', 120, 'reps'], ['t-bar-row', '4x8-10', 120], ['lat-pulldown', '3x10-12', 90, 'reps'], ['dumbbell-row', '3x10-12', 75]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['straight-arm-pulldown', '3x12'], ['back-extension', '3x12']]],
    ]],
    'shoulders_day' => ['Shoulders Day', 'intermediate', ['shoulders', 'split'], [
        ['type' => 'straight', 'ex' => [['overhead-press', '4x6-8', 150, 'linear'], ['arnold-press', '3x10', 90]]],
        ['type' => 'giant', 'rounds' => 4, 'rest_between_rounds' => 90, 'ex' => [['lateral-raise', '4x12-15'], ['reverse-pec-deck', '4x15'], ['upright-row', '4x12']]],
        ['type' => 'straight', 'ex' => [['barbell-shrug', '3x12', 60]]],
    ]],
    'arms_day' => ['Arms Day', 'intermediate', ['arms', 'split'], [
        ['type' => 'superset', 'rounds' => 4, 'rest_between_rounds' => 90, 'ex' => [['close-grip-bench-press', '4x8'], ['barbell-curl', '4x8-10']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['skull-crusher', '3x10-12'], ['incline-dumbbell-curl', '3x10-12']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['triceps-pushdown', 'd3x12'], ['hammer-curl', '3x12']]],
    ]],
    // ---------------------------------------------------------------- Mobility, posture, core, special populations
    'mobility_flow' => ['Mobility Flow (20 min)', 'beginner', ['mobility', 'recovery'], [
        ['type' => 'circuit', 'rounds' => 2, 'rest_seconds' => 0, 'rest_between_rounds' => 30, 'ex' => [['cat-cow', '2x10'], ['worlds-greatest-stretch', '2x5'], ['thoracic-rotation', '2x8'], ['hip-flexor-stretch', '2x45s'], ['deep-squat-hold', '2x45s']]],
        ['type' => 'cooldown', 'ex' => [['foam-roll-quads', '1x60s'], ['childs-pose', '1x60s']]],
    ]],
    'posture_a' => ['Desk Worker Posture Fix', 'beginner', ['posture', 'mobility'], [
        ['type' => 'warmup', 'ex' => [['thoracic-rotation', '1x8'], ['band-shoulder-dislocate', '1x10']]],
        ['type' => 'straight', 'ex' => [['face-pull', '3x15', 60], ['chest-supported-row', '3x12', 75], ['glute-bridge', '3x15', 60]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 45, 'ex' => [['bird-dog', '3x10'], ['band-pull-apart', '3x20']]],
        ['type' => 'cooldown', 'ex' => [['hip-flexor-stretch', '1x60s']]],
    ]],
    'core_blast' => ['Core Blast', 'intermediate', ['core', 'abs'], [
        ['type' => 'circuit', 'rounds' => 3, 'rest_seconds' => 10, 'rest_between_rounds' => 60, 'ex' => [['hanging-leg-raise', '3x10'], ['ab-wheel-rollout', '3x8'], ['pallof-press', '3x10'], ['bicycle-crunch', '3x20'], ['hollow-body-hold', '3x30s']]],
    ]],
    'runner_strength' => ['Strength for Runners', 'intermediate', ['running', 'strength'], [
        ['type' => 'straight', 'ex' => [['bulgarian-split-squat', '3x8', 90], ['single-leg-romanian-deadlift', '3x8', 75], ['step-up', '3x10', 60]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['single-leg-calf-raise', '3x15'], ['copenhagen-plank', '3x20s']]],
        ['type' => 'straight', 'ex' => [['box-jump', '3x5', 60]]],
    ]],
    'machine_a' => ['Gym Machines A', 'beginner', ['machines', 'beginner'], [
        ['type' => 'straight', 'ex' => [['leg-press', '3x10-12', 90, 'reps'], ['machine-chest-press', '3x10-12', 90, 'reps'], ['lat-pulldown', '3x10-12', 90, 'reps']]],
        ['type' => 'superset', 'rounds' => 2, 'rest_between_rounds' => 60, 'ex' => [['leg-curl', '2x12'], ['machine-shoulder-press', '2x12']]],
    ]],
    'machine_b' => ['Gym Machines B', 'beginner', ['machines', 'beginner'], [
        ['type' => 'straight', 'ex' => [['hack-squat', '3x10-12', 90, 'reps'], ['seated-cable-row', '3x10-12', 90, 'reps'], ['pec-deck', '3x12-15', 75]]],
        ['type' => 'superset', 'rounds' => 2, 'rest_between_rounds' => 60, 'ex' => [['leg-extension', '2x15'], ['reverse-pec-deck', '2x15']]],
    ]],
    'db_full_a' => ['Home Dumbbell Full Body A', 'beginner', ['dumbbell_only', 'home'], [
        ['type' => 'straight', 'ex' => [['goblet-squat', '3x10-12', 75, 'reps'], ['floor-press-dumbbell', '3x10-12', 75, 'reps'], ['dumbbell-row', '3x10-12', 75, 'reps']]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['dumbbell-swing', '3x15'], ['plank', '3x40s']]],
    ]],
    'db_full_b' => ['Home Dumbbell Full Body B', 'beginner', ['dumbbell_only', 'home'], [
        ['type' => 'straight', 'ex' => [['dumbbell-romanian-deadlift', '3x10-12', 75, 'reps'], ['dumbbell-shoulder-press', '3x10-12', 75], ['reverse-lunge', '3x10', 75]]],
        ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 60, 'ex' => [['hammer-curl', '3x12'], ['bench-dip', '3x12']]],
    ]],
    'tabata_express' => ['Tabata Express (20 min)', 'beginner', ['conditioning', 'quick'], [
        ['type' => 'warmup', 'ex' => [['jumping-jack', '1x60s']]],
        ['type' => 'tabata', 'rounds' => 8, 'work_seconds' => 20, 'rest_seconds' => 10, 'rest_between_rounds' => 60, 'ex' => [['bodyweight-squat', ''], ['push-up', '']]],
        ['type' => 'tabata', 'rounds' => 8, 'work_seconds' => 20, 'rest_seconds' => 10, 'rest_between_rounds' => 60, 'ex' => [['high-knees', ''], ['plank', '']]],
    ]],
    'aging_a' => ['Healthy Strength 50+', 'beginner', ['healthy_aging', 'balance'], [
        ['type' => 'warmup', 'ex' => [['cat-cow', '1x8'], ['ankle-mobility-drill', '1x8']]],
        ['type' => 'straight', 'ex' => [['goblet-squat', '2x10', 90], ['incline-push-up', '2x10', 75], ['band-row', '2x12', 75], ['step-up', '2x8', 75]]],
        ['type' => 'straight', 'ex' => [['suitcase-carry', '2x30m', 60], ['bird-dog', '2x8', 45]]],
    ]],
];

$programs = [
    ['beginner-full-body-3x', ['Beginner Full Body (3×/week)', 'برنامج الجسم الكامل للمبتدئين (٣ أيام)', 'Full body débutant (3×/semaine)', 'Cuerpo completo principiante (3×/semana)'],
        ['Learn the key movement patterns with simple dumbbell and machine work, alternating two full-body sessions.', 'تعلّم أنماط الحركة الأساسية بتمارين بسيطة بالدمبل والأجهزة، مع التناوب بين حصتين للجسم كامل.', 'Apprenez les mouvements clés avec des exercices simples aux haltères et machines, en alternant deux séances full body.', 'Aprende los patrones de movimiento clave con ejercicios sencillos de mancuernas y máquinas, alternando dos sesiones de cuerpo completo.'],
        'general_fitness', 'beginner', 3, ['dumbbell', 'cable', 'bench'], ['beginner', 'full_body'], 6, [6], 'hypertrophy', [1 => 'fb_a', 3 => 'fb_b', 5 => 'fb_a'], [1 => 'fb_b', 3 => 'fb_a', 5 => 'fb_b']],
    ['beginner-full-body-2x', ['Minimalist Full Body (2×/week)', 'الجسم الكامل المختصر (يومان أسبوعيًا)', 'Full body minimaliste (2×/semaine)', 'Cuerpo completo minimalista (2×/semana)'],
        ['Two efficient sessions a week for busy schedules — enough to build strength and consistency.', 'حصتان فعّالتان أسبوعيًا للمشغولين؛ كافيتان لبناء القوة والالتزام.', 'Deux séances efficaces par semaine pour les emplois du temps chargés : de quoi gagner en force et en régularité.', 'Dos sesiones eficaces por semana para agendas apretadas: suficiente para ganar fuerza y constancia.'],
        'general_fitness', 'beginner', 2, ['dumbbell', 'cable', 'bench'], ['beginner', 'busy'], 4, [], 'base', [2 => 'fb_a', 5 => 'fb_b']],
    ['push-pull-legs-6x', ['Push / Pull / Legs (6 days)', 'دفع / سحب / أرجل (٦ أيام)', 'Push / Pull / Legs (6 jours)', 'Empuje / Tirón / Piernas (6 días)'],
        ['The classic high-frequency split for intermediate lifters: every muscle twice a week with progressive overload.', 'التقسيم الكلاسيكي عالي التكرار للمتدربين المتوسطين: كل عضلة مرتين أسبوعيًا مع زيادة تدريجية في الأحمال.', 'Le split classique à haute fréquence pour pratiquants intermédiaires : chaque muscle deux fois par semaine avec surcharge progressive.', 'La rutina clásica de alta frecuencia para nivel intermedio: cada músculo dos veces por semana con sobrecarga progresiva.'],
        'muscle_gain', 'intermediate', 6, ['barbell', 'dumbbell', 'cable', 'machine', 'pull_up_bar'], ['ppl', 'hypertrophy'], 6, [6], 'hypertrophy', [1 => 'push_1', 2 => 'pull_1', 3 => 'legs_1', 4 => 'push_1', 5 => 'pull_1', 6 => 'legs_1']],
    ['push-pull-legs-3x', ['Push / Pull / Legs (3 days)', 'دفع / سحب / أرجل (٣ أيام)', 'Push / Pull / Legs (3 jours)', 'Empuje / Tirón / Piernas (3 días)'],
        ['A balanced three-day split that trains every muscle group once a week with plenty of recovery.', 'تقسيم متوازن على ثلاثة أيام يدرّب كل مجموعة عضلية مرة أسبوعيًا مع وقت كافٍ للاستشفاء.', 'Un split équilibré sur trois jours qui travaille chaque groupe musculaire une fois par semaine avec beaucoup de récupération.', 'Una rutina equilibrada de tres días que trabaja cada grupo muscular una vez por semana con buena recuperación.'],
        'muscle_gain', 'intermediate', 3, ['barbell', 'dumbbell', 'cable', 'pull_up_bar'], ['ppl'], 4, [], 'hypertrophy', [1 => 'push_1', 3 => 'pull_1', 5 => 'legs_1']],
    ['upper-lower-4x', ['Upper / Lower (4 days)', 'علوي / سفلي (٤ أيام)', 'Haut / Bas (4 jours)', 'Torso / Pierna (4 días)'],
        ['Strength and hypertrophy days for upper and lower body — the most popular intermediate template.', 'أيام للقوة وأخرى للتضخيم للجزء العلوي والسفلي؛ البرنامج الأكثر شعبية للمستوى المتوسط.', 'Des journées force et hypertrophie pour le haut et le bas du corps : le modèle intermédiaire le plus populaire.', 'Días de fuerza e hipertrofia para torso y pierna: la plantilla intermedia más popular.'],
        'strength', 'intermediate', 4, ['barbell', 'dumbbell', 'cable', 'machine'], ['upper_lower'], 8, [4, 8], 'strength', [1 => 'upper_a', 2 => 'lower_a', 4 => 'upper_b', 5 => 'lower_b']],
    ['five-three-one-style', ['5/3/1-Style Strength Cycle', 'دورة القوة على طريقة ٥/٣/١', 'Cycle de force type 5/3/1', 'Ciclo de fuerza estilo 5/3/1'],
        ['Percentage-based training on the four big lifts with weekly progression and a built-in deload.', 'تدريب قائم على النسب المئوية للرفعات الأربع الكبرى مع تدرّج أسبوعي وأسبوع تخفيف مدمج.', 'Entraînement au pourcentage sur les quatre grands mouvements, avec progression hebdomadaire et décharge intégrée.', 'Entrenamiento por porcentajes en los cuatro grandes levantamientos con progresión semanal y descarga incluida.'],
        'strength', 'advanced', 4, ['barbell', 'bench', 'pull_up_bar'], ['531', 'powerlifting'], 8, [4, 8], 'strength', [1 => 'w531_ohp', 2 => 'w531_deadlift', 4 => 'w531_bench', 5 => 'w531_squat']],
    ['home-no-equipment', ['Home Workout — No Equipment', 'تمارين منزلية بدون معدات', 'Entraînement maison sans matériel', 'Entrenamiento en casa sin material'],
        ['Train anywhere with bodyweight circuits, Tabata and EMOM blocks — no gym needed.', 'تدرّب في أي مكان بدوائر وزن الجسم وتمارين التاباتا وEMOM دون الحاجة إلى نادٍ رياضي.', 'Entraînez-vous partout avec des circuits au poids du corps, du Tabata et de l’EMOM, sans salle de sport.', 'Entrena donde quieras con circuitos de peso corporal, Tabata y EMOM, sin gimnasio.'],
        'fat_loss', 'beginner', 3, ['bodyweight'], ['home', 'no_equipment'], 4, [], 'conditioning', [1 => 'home_a', 3 => 'home_b', 5 => 'home_c']],
    ['dumbbell-only-hypertrophy-4x', ['Dumbbell-Only Hypertrophy (4 days)', 'التضخيم العضلي بالدمبل فقط (٤ أيام)', 'Hypertrophie 100 % haltères (4 jours)', 'Hipertrofia solo con mancuernas (4 días)'],
        ['Build muscle with nothing but a pair of dumbbells and a bench, using double progression.', 'ابنِ العضلات بزوج من الدمبلز وبنش فقط، باستخدام التدرّج المزدوج.', 'Prenez du muscle avec une simple paire d’haltères et un banc, grâce à la double progression.', 'Gana músculo solo con un par de mancuernas y un banco, usando doble progresión.'],
        'muscle_gain', 'intermediate', 4, ['dumbbell', 'bench'], ['dumbbell_only', 'home', 'hypertrophy'], 6, [6], 'hypertrophy', [1 => 'db_upper_a', 2 => 'db_lower_a', 4 => 'db_upper_b', 5 => 'db_lower_b']],
    ['calisthenics-foundations', ['Calisthenics Foundations', 'أساسيات الكاليسثنكس', 'Fondamentaux de la callisthénie', 'Fundamentos de calistenia'],
        ['Bodyweight strength with clear progressions toward pull-ups, dips, pistols and L-sits.', 'قوة بوزن الجسم مع تدرّجات واضحة نحو العقلة والمتوازي وسكوات المسدس وجلوس L.', 'Force au poids du corps avec des progressions claires vers les tractions, dips, pistols et L-sit.', 'Fuerza con peso corporal y progresiones claras hacia dominadas, fondos, pistols y L-sit.'],
        'strength', 'intermediate', 3, ['pull_up_bar', 'dip_bars', 'parallettes'], ['calisthenics', 'bodyweight'], 6, [6], 'strength', [1 => 'cali_push', 3 => 'cali_pull', 5 => 'cali_legs']],
    ['hyrox-race-prep', ['HYROX Race Prep (8 weeks)', 'التحضير لسباق هايروكس (٨ أسابيع)', 'Préparation HYROX (8 semaines)', 'Preparación HYROX (8 semanas)'],
        ['Running engine, station strength and race simulations to get you HYROX-ready.', 'تحمّل في الجري وقوة في المحطات ومحاكاة للسباق لتكون جاهزًا لهايروكس.', 'Endurance de course, force sur les stations et simulations pour être prêt pour HYROX.', 'Motor de carrera, fuerza en estaciones y simulaciones para llegar listo a HYROX.'],
        'performance', 'intermediate', 4, ['sled', 'rower', 'ski_erg', 'wall_ball', 'sandbag', 'kettlebell'], ['hyrox', 'hybrid'], 8, [4], 'conditioning', [1 => 'hyrox_strength', 2 => 'hyrox_engine', 4 => 'hyrox_strength', 6 => 'hyrox_sim']],
    ['ramadan-low-volume', ['Ramadan-Friendly Low Volume', 'برنامج رمضان منخفض الحجم', 'Programme Ramadan à faible volume', 'Programa de Ramadán de bajo volumen'],
        ['Short, low-fatigue sessions to keep your strength during fasting — best done after iftar.', 'حصص قصيرة وقليلة الإجهاد للحفاظ على قوتك أثناء الصيام، ويُفضّل أداؤها بعد الإفطار.', 'Des séances courtes et peu fatigantes pour garder votre force pendant le jeûne, idéalement après l’iftar.', 'Sesiones cortas y poco fatigantes para mantener la fuerza durante el ayuno, mejor después del iftar.'],
        'maintenance', 'beginner', 3, ['dumbbell', 'resistance_band'], ['ramadan', 'low_volume'], 4, [], 'base', [1 => 'ramadan_a', 3 => 'ramadan_b', 5 => 'ramadan_a']],
    ['womens-glute-focus', ['Women’s Glute Focus', 'برنامج تركيز الأرداف للنساء', 'Programme fessiers pour femmes', 'Programa de glúteos para mujeres'],
        ['Three lower-body-led sessions built around hip thrusts, squats and hinges, plus an upper day.', 'ثلاث حصص تركّز على الجزء السفلي حول دفع الورك والسكوات والانحناء، مع يوم للجزء العلوي.', 'Trois séances axées sur le bas du corps autour du hip thrust, du squat et des charnières, plus une séance haut du corps.', 'Tres sesiones centradas en el tren inferior con hip thrust, sentadillas y bisagras, más un día de torso.'],
        'muscle_gain', 'intermediate', 4, ['barbell', 'dumbbell', 'cable', 'machine', 'resistance_band'], ['glutes', 'women'], 6, [6], 'hypertrophy', [1 => 'glute_a', 2 => 'glute_c', 4 => 'glute_b', 6 => 'glute_a']],
    ['kettlebell-only', ['Kettlebell Only', 'برنامج الكيتلبل فقط', 'Programme 100 % kettlebell', 'Solo pesa rusa'],
        ['Swings, cleans, presses and get-ups for strength and conditioning with a single kettlebell.', 'أرجحة وكلين وضغط ونهوض تركي لبناء القوة واللياقة بكيتلبل واحد.', 'Swings, épaulés, développés et get-ups pour la force et le cardio avec une seule kettlebell.', 'Swings, cargadas, presses y get-ups para fuerza y acondicionamiento con una sola pesa rusa.'],
        'general_fitness', 'intermediate', 3, ['kettlebell'], ['kettlebell', 'home'], 4, [], 'conditioning', [1 => 'kb_a', 3 => 'kb_b', 5 => 'kb_a'], [1 => 'kb_b', 3 => 'kb_a', 5 => 'kb_b']],
    ['linear-strength-5x5', ['Linear Strength 5×5', 'القوة الخطية ٥×٥', 'Force linéaire 5×5', 'Fuerza lineal 5×5'],
        ['Add weight every session on the big barbell lifts — the fastest way for beginners to get strong.', 'أضف وزنًا في كل حصة على الرفعات الكبرى بالبار؛ أسرع طريق للمبتدئين لاكتساب القوة.', 'Ajoutez du poids à chaque séance sur les grands mouvements à la barre : la voie la plus rapide pour les débutants.', 'Sube peso en cada sesión en los grandes levantamientos con barra: la vía más rápida para principiantes.'],
        'strength', 'beginner', 3, ['barbell', 'bench'], ['5x5', 'strength'], 8, [], 'strength', [1 => 'sl_a', 3 => 'sl_b', 5 => 'sl_a'], [1 => 'sl_b', 3 => 'sl_a', 5 => 'sl_b']],
    ['fat-loss-conditioning', ['Fat Loss Conditioning', 'لياقة لحرق الدهون', 'Conditionnement perte de gras', 'Acondicionamiento para perder grasa'],
        ['Intervals, metcons and full-body strength to maximise calorie burn while keeping muscle.', 'تمارين متقطعة ودوائر أيضية وقوة للجسم كامل لزيادة حرق السعرات مع الحفاظ على العضلات.', 'Intervalles, metcons et force full body pour brûler un maximum de calories tout en gardant le muscle.', 'Intervalos, metcons y fuerza de cuerpo completo para quemar más calorías sin perder músculo.'],
        'fat_loss', 'intermediate', 4, ['dumbbell', 'assault_bike', 'rower', 'battle_ropes'], ['fat_loss', 'conditioning'], 4, [], 'conditioning', [1 => 'hiit_a', 2 => 'fb_a', 4 => 'metcon_b', 5 => 'fb_b']],
    ['classic-bro-split-5x', ['Classic Body-Part Split (5 days)', 'التقسيم الكلاسيكي حسب العضلة (٥ أيام)', 'Split classique par groupe musculaire (5 jours)', 'Rutina dividida clásica (5 días)'],
        ['One muscle group per day with high volume — chest, back, shoulders, legs and arms.', 'مجموعة عضلية واحدة يوميًا بحجم تدريبي عالٍ: الصدر والظهر والأكتاف والأرجل والذراعين.', 'Un groupe musculaire par jour avec un gros volume : pectoraux, dos, épaules, jambes et bras.', 'Un grupo muscular por día con alto volumen: pecho, espalda, hombros, piernas y brazos.'],
        'muscle_gain', 'intermediate', 5, ['barbell', 'dumbbell', 'cable', 'machine'], ['split', 'bodybuilding'], 6, [6], 'hypertrophy', [1 => 'chest_day', 2 => 'back_day', 3 => 'legs_1', 4 => 'shoulders_day', 5 => 'arms_day']],
    ['mobility-and-recovery', ['Mobility & Recovery', 'المرونة والاستشفاء', 'Mobilité et récupération', 'Movilidad y recuperación'],
        ['Gentle daily flows to move better, reduce stiffness and recover between hard sessions.', 'تمارين انسيابية يومية لطيفة لتحسين الحركة وتقليل التيبّس والاستشفاء بين الحصص الشاقة.', 'Des enchaînements doux pour mieux bouger, réduire les raideurs et récupérer entre les grosses séances.', 'Secuencias suaves para moverte mejor, reducir la rigidez y recuperarte entre sesiones duras.'],
        'mobility', 'beginner', 3, ['mat', 'foam_roller', 'resistance_band'], ['mobility', 'recovery'], 4, [], 'base', [1 => 'mobility_flow', 3 => 'mobility_flow', 6 => 'mobility_flow']],
    ['powerbuilding-4x', ['Powerbuilding (4 days)', 'باوربيلدنغ (٤ أيام)', 'Powerbuilding (4 jours)', 'Powerbuilding (4 días)'],
        ['Heavy compound lifts early in the week, bodybuilding volume later — strength and size together.', 'رفعات مركّبة ثقيلة في بداية الأسبوع وحجم تدريبي لبناء العضلات لاحقًا؛ قوة وحجم معًا.', 'Gros mouvements polyarticulaires en début de semaine, volume bodybuilding ensuite : force et masse réunies.', 'Básicos pesados a principio de semana y volumen de culturismo después: fuerza y tamaño a la vez.'],
        'strength', 'intermediate', 4, ['barbell', 'dumbbell', 'cable', 'machine'], ['powerbuilding'], 6, [6], 'strength', [1 => 'sl_a', 2 => 'sl_b', 4 => 'upper_b', 5 => 'lower_b']],
    ['runner-strength', ['Strength Training for Runners', 'تدريب القوة للعدّائين', 'Renforcement pour coureurs', 'Fuerza para corredores'],
        ['Single-leg strength, plyometrics and core work to run faster and stay injury-free.', 'قوة الساق الواحدة وتمارين البليومترك والجذع لتجري أسرع وتتجنّب الإصابات.', 'Force unilatérale, pliométrie et gainage pour courir plus vite et éviter les blessures.', 'Fuerza unilateral, pliometría y core para correr más rápido y evitar lesiones.'],
        'performance', 'intermediate', 2, ['dumbbell', 'box', 'bench'], ['running', 'injury_prevention'], 6, [], 'strength', [2 => 'runner_strength', 5 => 'core_blast']],
    ['desk-worker-posture', ['Desk Worker Posture Reset', 'إعادة ضبط القوام لموظفي المكاتب', 'Posture pour travail de bureau', 'Postura para trabajo de oficina'],
        ['Undo long hours of sitting with upper-back strength, hip mobility and core stability.', 'تخلّص من آثار الجلوس الطويل بتقوية أعلى الظهر ومرونة الورك وثبات الجذع.', 'Compensez les longues heures assis grâce au renforcement du haut du dos, à la mobilité des hanches et au gainage.', 'Contrarresta las horas sentado con fuerza de espalda alta, movilidad de cadera y estabilidad del core.'],
        'health', 'beginner', 3, ['resistance_band', 'cable', 'dumbbell', 'mat'], ['posture', 'office'], 4, [], 'base', [1 => 'posture_a', 3 => 'mobility_flow', 5 => 'posture_a']],
    ['core-and-abs', ['Core & Abs Builder', 'بناء الجذع وعضلات البطن', 'Programme gainage et abdos', 'Programa de core y abdominales'],
        ['Stronger, more stable core with anti-rotation, hanging and rollout progressions plus conditioning.', 'جذع أقوى وأكثر ثباتًا بتمارين مقاومة الدوران والتعلّق والعجلة مع تمارين اللياقة.', 'Une sangle abdominale plus forte et stable : anti-rotation, suspension, roue abdominale et cardio.', 'Un core más fuerte y estable con antirrotación, colgados, rueda abdominal y acondicionamiento.'],
        'general_fitness', 'intermediate', 3, ['pull_up_bar', 'ab_wheel', 'cable'], ['core', 'abs'], 4, [], 'conditioning', [1 => 'core_blast', 3 => 'hiit_a', 5 => 'core_blast']],
    ['arms-specialization', ['Arms Specialization', 'برنامج تخصّص الذراعين', 'Spécialisation bras', 'Especialización de brazos'],
        ['Extra arm volume on top of two upper-body days for bigger biceps and triceps.', 'حجم تدريبي إضافي للذراعين مع يومين للجزء العلوي لباي وتراي أكبر.', 'Volume supplémentaire pour les bras en plus de deux séances haut du corps pour des biceps et triceps plus gros.', 'Volumen extra de brazos además de dos días de torso para bíceps y tríceps más grandes.'],
        'muscle_gain', 'intermediate', 4, ['barbell', 'dumbbell', 'cable'], ['arms', 'specialization'], 4, [], 'hypertrophy', [1 => 'upper_a', 2 => 'arms_day', 4 => 'upper_b', 5 => 'lower_a']],
    ['beginner-gym-machines', ['First Steps in the Gym (Machines)', 'خطواتك الأولى في النادي (الأجهزة)', 'Premiers pas en salle (machines)', 'Primeros pasos en el gimnasio (máquinas)'],
        ['Confidence-building machine workouts for complete beginners, with simple progressions.', 'تمارين على الأجهزة لبناء الثقة للمبتدئين تمامًا، مع تدرّجات بسيطة.', 'Des séances sur machines pour prendre confiance quand on débute, avec des progressions simples.', 'Entrenamientos en máquinas para ganar confianza desde cero, con progresiones sencillas.'],
        'general_fitness', 'beginner', 3, ['machine', 'cable'], ['beginner', 'machines'], 4, [], 'base', [1 => 'machine_a', 3 => 'machine_b', 5 => 'machine_a'], [1 => 'machine_b', 3 => 'machine_a', 5 => 'machine_b']],
    ['home-dumbbell-full-body', ['Home Dumbbell Full Body', 'الجسم الكامل بالدمبل في المنزل', 'Full body haltères à la maison', 'Cuerpo completo con mancuernas en casa'],
        ['Three full-body sessions a week with adjustable dumbbells — perfect for a home setup.', 'ثلاث حصص أسبوعيًا للجسم كامل بدمبلز قابلة للتعديل، مثالية للتدريب المنزلي.', 'Trois séances full body par semaine avec des haltères réglables : idéal pour s’entraîner chez soi.', 'Tres sesiones de cuerpo completo por semana con mancuernas ajustables: ideal para casa.'],
        'general_fitness', 'beginner', 3, ['dumbbell'], ['home', 'dumbbell_only'], 6, [6], 'hypertrophy', [1 => 'db_full_a', 3 => 'db_full_b', 5 => 'db_full_a'], [1 => 'db_full_b', 3 => 'db_full_a', 5 => 'db_full_b']],
    ['tabata-express', ['Tabata Express (20 minutes)', 'تاباتا السريع (٢٠ دقيقة)', 'Tabata express (20 minutes)', 'Tabata exprés (20 minutos)'],
        ['Short, intense Tabata sessions you can fit into any day — no equipment required.', 'حصص تاباتا قصيرة ومكثّفة تناسب أي يوم دون الحاجة إلى معدات.', 'Des séances Tabata courtes et intenses à caser dans n’importe quelle journée, sans matériel.', 'Sesiones de Tabata cortas e intensas para cualquier día, sin material.'],
        'fat_loss', 'beginner', 4, ['bodyweight'], ['tabata', 'quick', 'home'], 4, [], 'conditioning', [1 => 'tabata_express', 2 => 'mobility_flow', 4 => 'tabata_express', 6 => 'tabata_express']],
    ['healthy-strength-50-plus', ['Healthy Strength 50+', 'قوة صحية لمن تجاوزوا الخمسين', 'Force et santé 50+', 'Fuerza saludable 50+'],
        ['Joint-friendly strength, balance and mobility to stay active and independent.', 'تمارين قوة وتوازن ومرونة لطيفة على المفاصل للبقاء نشيطًا ومستقلًا.', 'Force, équilibre et mobilité en douceur pour les articulations, pour rester actif et autonome.', 'Fuerza, equilibrio y movilidad respetuosos con las articulaciones para seguir activo e independiente.'],
        'health', 'beginner', 2, ['dumbbell', 'resistance_band', 'step'], ['healthy_aging', 'balance'], 6, [], 'base', [2 => 'aging_a', 5 => 'mobility_flow']],
];

return ['workouts' => $workouts, 'programs' => $programs];
