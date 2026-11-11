<?php

namespace App\Casts;

use App\Enums\Enum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class EnumCast implements CastsAttributes
{
    /** @var string Fully-qualified enum class name */
    private $enumClass;

    /**
     * @param  string  $enumClass  The App\Enums class the column holds
     */
    public function __construct(string $enumClass)
    {
        $this->enumClass = ltrim($enumClass, '\\');
    }

    /**
     * Return the stored string as an enum instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string                               $key
     * @param  mixed                                $value
     * @param  array                                $attributes
     * @return \App\Enums\Enum|null
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $enumClass = $this->enumClass;

        return $enumClass::tryFrom($value);
    }

    /**
     * Turn an enum instance (or its value) into the string the column stores.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string                               $key
     * @param  mixed                                $value
     * @param  array                                $attributes
     * @return string|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof Enum) {
            return $value->value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return is_scalar($value) ? (string) $value : null;
    }
}
