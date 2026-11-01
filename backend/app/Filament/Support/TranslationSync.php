<?php

namespace App\Filament\Support;

use App\Domain\Tenancy\Models\Translation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Imports lang/{locale}/{group}.php and lang/{locale}.json into the
 * translations table so admins can edit them. Existing DB values are kept
 * unless $overwrite is true (DB edits are the admin's source of truth).
 */
final class TranslationSync
{
    /** @return array{created: int, updated: int} */
    public function sync(bool $overwrite = false, ?string $langPath = null): array
    {
        $langPath ??= lang_path();
        $rows = [];

        foreach (Locales::all() as $locale) {
            foreach (glob($langPath.'/'.$locale.'/*.php') ?: [] as $file) {
                $group = basename($file, '.php');
                $data = require $file;
                if (! is_array($data)) {
                    continue;
                }
                foreach (Arr::dot($data) as $key => $value) {
                    if (is_scalar($value)) {
                        $rows[] = ['locale' => $locale, 'group' => $group, 'key' => (string) $key, 'value' => (string) $value];
                    }
                }
            }

            $json = $langPath.'/'.$locale.'.json';
            if (is_file($json)) {
                $data = json_decode((string) file_get_contents($json), true);
                foreach (is_array($data) ? $data : [] as $key => $value) {
                    if (is_scalar($value)) {
                        $rows[] = ['locale' => $locale, 'group' => '*', 'key' => (string) $key, 'value' => (string) $value];
                    }
                }
            }
        }

        $existing = Translation::query()->get(['id', 'locale', 'group', 'key', 'value'])
            ->keyBy(fn (Translation $t): string => $t->locale.'|'.$t->group.'|'.$t->key);

        $created = 0;
        $updated = 0;
        $insert = [];
        $now = now();

        DB::transaction(function () use ($rows, $existing, $overwrite, $now, &$insert, &$created, &$updated): void {
            foreach ($rows as $row) {
                $id = $row['locale'].'|'.$row['group'].'|'.$row['key'];
                $current = $existing->get($id);
                if ($current === null) {
                    $insert[$id] = $row + ['id' => (string) Str::ulid(), 'created_at' => $now, 'updated_at' => $now];
                    $created++;
                } elseif ($overwrite && $current->value !== $row['value']) {
                    $current->update(['value' => $row['value']]);
                    $updated++;
                }
            }
            foreach (array_chunk(array_values($insert), 500) as $chunk) {
                Translation::query()->insert($chunk);
            }
        });

        return ['created' => count($insert), 'updated' => $updated];
    }
}
