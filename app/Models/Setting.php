<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value', 'description'])]
class Setting extends Model
{
    public static function value(string $key, string|int|float|null $default = null): string|int|float|null
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }
}
