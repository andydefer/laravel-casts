<?php

declare(strict_types=1);

namespace AndyDefer\LaravelCasts\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class MoneyCast implements CastsAttributes
{
    private const DECIMAL_PLACES = 2;
    private const UNIT_MULTIPLIER = 100;

    public function get(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        if ($value === null) {
            return null;
        }

        return round(
            num: (int) $value / self::UNIT_MULTIPLIER,
            precision: self::DECIMAL_PLACES
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        return (int) round(
            num: (float) $value * self::UNIT_MULTIPLIER,
            precision: 0
        );
    }
}
