<?php

namespace App\Enums;

use ReflectionClass;
use ValueError;
use JsonSerializable;

abstract class Enum implements JsonSerializable
{
    /** @var array<string, static[]> singleton cache, keyed by class */
    private static $instances = [];

    /** @var array<string, array> constants cache, keyed by class */
    private static $constants = [];

    /** @var string */
    public $name;

    /** @var mixed */
    public $value;

    final private function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Handles Status::PENDING() style access.
     *
     * @return static
     */
    public static function __callStatic($name, $arguments)
    {
        $constants = static::getConstants();

        if (!array_key_exists($name, $constants)) {
            throw new \Error(sprintf(
                'Call to undefined method %s::%s()',
                static::class,
                $name
            ));
        }

        return static::instance($name, $constants[$name]);
    }

    /**
     * @return static
     */
    private static function instance($name, $value)
    {
        $key = static::class . '::' . $name;

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static($name, $value);
        }

        return self::$instances[$key];
    }

    /**
     * Returns all cases as singleton instances, like native enum::cases().
     *
     * @return static[]
     */
    public static function cases()
    {
        $cases = [];

        foreach (static::getConstants() as $name => $value) {
            $cases[] = static::instance($name, $value);
        }

        return $cases;
    }

    /**
     * Like native BackedEnum::from().
     *
     * @return static
     */
    public static function from($value)
    {
        foreach (static::getConstants() as $name => $constValue) {
            if ($constValue === $value) {
                return static::instance($name, $value);
            }
        }

        throw new ValueError(sprintf(
            '%s is not a valid backing value for enum %s',
            var_export($value, true),
            static::class
        ));
    }

    /**
     * Like native BackedEnum::tryFrom().
     *
     * @return static|null
     */
    public static function tryFrom($value)
    {
        try {
            return static::from($value);
        } catch (ValueError $e) {
            return null;
        }
    }

    /**
     * @return array
     */
    protected static function getConstants()
    {
        $class = static::class;

        if (!isset(self::$constants[$class])) {
            self::$constants[$class] = (new ReflectionClass($class))->getConstants();
        }

        return self::$constants[$class];
    }

    public static function isValidValue($value)
    {
        return in_array($value, static::getConstants(), true);
    }

    public static function isValidName($name)
    {
        return array_key_exists($name, static::getConstants());
    }

    public static function values()
    {
        return array_values(static::getConstants());
    }

    public static function names()
    {
        return array_keys(static::getConstants());
    }

    /**
     * Identity check — works because cases are singletons.
     */
    public function is(self $case)
    {
        return $this === $case;
    }

    public function equals(self $case)
    {
        return static::class === get_class($case) && $this->value === $case->value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
