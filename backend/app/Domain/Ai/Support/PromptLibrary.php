<?php

namespace App\Domain\Ai\Support;

use InvalidArgumentException;

/**
 * Versioned prompt templates in resources/prompts/{feature}/{version}.md.
 * A template has an optional front-matter block, then "# System" and
 * "# User" sections. {{variable}} placeholders are substituted; arrays are
 * rendered as JSON. A missing variable is a programming error.
 */
final class PromptLibrary
{
    public function path(string $feature, string $version): string
    {
        return resource_path("prompts/{$feature}/{$version}.md");
    }

    public function exists(string $feature, string $version): bool
    {
        return is_file($this->path($feature, $version));
    }

    /**
     * @param  array<string, mixed>  $variables
     * @return array{system: string, user: string}
     */
    public function render(string $feature, string $version, array $variables): array
    {
        $file = $this->path($feature, $version);
        if (! is_file($file)) {
            throw new InvalidArgumentException("Prompt template [{$feature}/{$version}] not found.");
        }
        $raw = (string) file_get_contents($file);
        $raw = (string) preg_replace('/\A---\R.*?\R---\R/s', '', $raw);

        if (! preg_match('/^# System\s*\R(.*?)^# User\s*\R(.*)\z/ms', $raw, $m)) {
            throw new InvalidArgumentException("Prompt template [{$feature}/{$version}] needs '# System' and '# User' sections.");
        }

        return [
            'system' => $this->substitute(trim($m[1]), $variables, $feature),
            'user' => $this->substitute(trim($m[2]), $variables, $feature),
        ];
    }

    /** @param array<string, mixed> $variables */
    private function substitute(string $text, array $variables, string $feature): string
    {
        return (string) preg_replace_callback('/\{\{\s*([a-z0-9_]+)\s*\}\}/i', function (array $m) use ($variables, $feature) {
            if (! array_key_exists($m[1], $variables)) {
                throw new InvalidArgumentException("Prompt [{$feature}] is missing variable [{$m[1]}].");
            }
            $value = $variables[$m[1]];

            return match (true) {
                is_array($value) => (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                is_bool($value) => $value ? 'yes' : 'no',
                $value === null => '-',
                default => (string) $value,
            };
        }, $text);
    }
}
