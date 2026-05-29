<?php

declare(strict_types=1);

namespace AndyDefer\LaravelCasts\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;

final class JsonCast implements CastsAttributes
{
    private const MAX_JSON_DEPTH = 512;

    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            return $this->decodeJsonString($value);
        }

        return null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && $this->isValidJsonString($value)) {
            return $value;
        }

        return $this->encodeToJson($value);
    }

    private function decodeJsonString(string $jsonString): ?array
    {
        try {
            $decoded = json_decode(
                json: $jsonString,
                associative: true,
                depth: self::MAX_JSON_DEPTH,
                flags: JSON_THROW_ON_ERROR
            );

            return is_array($decoded) ? $decoded : [];
        } catch (JsonException) {
            return null;
        }
    }

    private function encodeToJson(mixed $value): string
    {
        return json_encode(
            value: $value,
            flags: JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
        );
    }

    private function isValidJsonString(string $value): bool
    {
        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
