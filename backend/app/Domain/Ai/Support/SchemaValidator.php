<?php

namespace App\Domain\Ai\Support;

/**
 * Minimal JSON-schema validator for model outputs (type, enum, properties,
 * required, additionalProperties, items, min/max, minItems/maxItems,
 * minLength/maxLength). Errors use JSON-pointer-ish paths so they can be fed
 * back to the model verbatim.
 */
final class SchemaValidator
{
    private const PROVIDER_UNSUPPORTED = [
        'minimum', 'maximum', 'exclusiveMinimum', 'exclusiveMaximum', 'minLength', 'maxLength',
        'pattern', 'format', 'minItems', 'maxItems', 'uniqueItems', 'multipleOf', 'default',
    ];

    /**
     * @param  array<string, mixed>  $schema
     * @return list<string>
     */
    public function validate(mixed $data, array $schema, string $path = '$'): array
    {
        $errors = [];
        $types = (array) ($schema['type'] ?? []);

        if ($types !== [] && ! $this->matchesType($data, $types)) {
            return ["{$path}: expected ".implode('|', $types).', got '.get_debug_type($data)];
        }

        if (isset($schema['enum']) && ! in_array($data, $schema['enum'], true)) {
            $errors[] = "{$path}: must be one of ".implode(', ', array_map(fn ($v) => json_encode($v), $schema['enum']));
        }

        if (is_string($data)) {
            if (isset($schema['minLength']) && mb_strlen($data) < $schema['minLength']) {
                $errors[] = "{$path}: must be at least {$schema['minLength']} characters";
            }
            if (isset($schema['maxLength']) && mb_strlen($data) > $schema['maxLength']) {
                $errors[] = "{$path}: must be at most {$schema['maxLength']} characters";
            }
        }

        if (is_int($data) || is_float($data)) {
            if (isset($schema['minimum']) && $data < $schema['minimum']) {
                $errors[] = "{$path}: must be >= {$schema['minimum']}";
            }
            if (isset($schema['maximum']) && $data > $schema['maximum']) {
                $errors[] = "{$path}: must be <= {$schema['maximum']}";
            }
        }

        if (is_array($data) && array_is_list($data) && in_array('array', $types, true)) {
            if (isset($schema['minItems']) && count($data) < $schema['minItems']) {
                $errors[] = "{$path}: must contain at least {$schema['minItems']} items";
            }
            if (isset($schema['maxItems']) && count($data) > $schema['maxItems']) {
                $errors[] = "{$path}: must contain at most {$schema['maxItems']} items";
            }
            if (isset($schema['items']) && is_array($schema['items'])) {
                foreach ($data as $i => $item) {
                    array_push($errors, ...$this->validate($item, $schema['items'], "{$path}[{$i}]"));
                }
            }
        } elseif (is_array($data) && in_array('object', $types, true)) {
            $properties = $schema['properties'] ?? [];
            foreach ($schema['required'] ?? [] as $key) {
                if (! array_key_exists($key, $data)) {
                    $errors[] = "{$path}.{$key}: is required";
                }
            }
            foreach ($data as $key => $value) {
                if (isset($properties[$key])) {
                    array_push($errors, ...$this->validate($value, $properties[$key], "{$path}.{$key}"));
                } elseif (($schema['additionalProperties'] ?? true) === false) {
                    $errors[] = "{$path}.{$key}: unknown property";
                }
            }
        }

        return $errors;
    }

    /**
     * Schema sent to providers' strict modes: every object closed and fully
     * required, validation keywords the providers reject stripped (they are
     * still enforced locally by validate()).
     *
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    public function forProvider(array $schema): array
    {
        foreach (self::PROVIDER_UNSUPPORTED as $key) {
            unset($schema[$key]);
        }
        $types = (array) ($schema['type'] ?? []);
        if (in_array('object', $types, true)) {
            $props = [];
            foreach ($schema['properties'] ?? [] as $key => $prop) {
                $props[$key] = $this->forProvider($prop);
            }
            $schema['properties'] = (object) $props;
            $schema['required'] = array_keys($props);
            $schema['additionalProperties'] = false;
        }
        if (isset($schema['items']) && is_array($schema['items'])) {
            $schema['items'] = $this->forProvider($schema['items']);
        }

        return $schema;
    }

    /** @param list<string> $types */
    private function matchesType(mixed $data, array $types): bool
    {
        foreach ($types as $type) {
            $ok = match ($type) {
                'string' => is_string($data),
                'integer' => is_int($data) || (is_float($data) && floor($data) === $data),
                'number' => is_int($data) || is_float($data),
                'boolean' => is_bool($data),
                'null' => $data === null,
                'array' => is_array($data) && array_is_list($data),
                'object' => is_array($data) && ($data === [] || ! array_is_list($data)),
                default => false,
            };
            if ($ok) {
                return true;
            }
        }

        return false;
    }
}
