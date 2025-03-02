<?php

namespace Laraigniter\Enum;

use Elegant\Support\Collection;
use Elegant\Support\Traits\Macroable;
use Elegant\View\Contracts\Arrayable;
use JsonSerializable;
use Laraigniter\Enum\Concerns\Enum as EnumContract;
use Laraigniter\Enum\Exceptions\InvalidClassTypeException;
use Laraigniter\Enum\Exceptions\InvalidEnumNameException;
use Laraigniter\Enum\Exceptions\InvalidEnumValueException;
use ReflectionClass;

abstract class BaseEnum implements EnumContract, Arrayable, JsonSerializable
{
    use Macroable {
        __callStatic as macroCallStatic;
        __call as macroCall;
    }

    /**
     * The name of one of the enum members.
     *
     * @var string
     */
    public string $name;

    /**
     * The value of one of the enum members.
     *
     * @var mixed
     */
    public $value;

    /**
     * Caches reflections of enum subclasses.
     *
     * @var array<class-string<static>, ReflectionClass<static>>
     */
    protected static array $reflectionClass = [];

    /**
     * Construct an Enum instance.
     *
     * @param $value
     * @throws InvalidClassTypeException|InvalidEnumValueException
     */
    public function __construct($value)
    {
        if (!static::hasValue($value)) {
            throw new InvalidEnumValueException($value, static::class);
        }

        if(!self::getReflection()->isFinal()) {
            throw new InvalidClassTypeException(static::class);
        }

        $this->name = static::getName($value);
        $this->value = $value;
    }

    /**
     * Make a new instance from an enum value.
     *
     * @param mixed $value
     * @return static
     * @throws InvalidEnumValueException|InvalidClassTypeException
     */
    public static function from($value): BaseEnum
    {
        if ($value instanceof static) {
            return $value;
        }

        return new static($value); // @phpstan-ignore return.type (generic variance)
    }

    /**
     * Returns a reflection of the enum subclass.
     *
     * @return ReflectionClass<static>
     */
    protected static function getReflection(): ReflectionClass
    {
        $class = static::class;

        return static::$reflectionClass[$class] ??= new ReflectionClass($class);
    }

    /**
     * Make an enum instance from a given name.
     *
     * @throws InvalidEnumNameException|InvalidEnumValueException|InvalidClassTypeException
     */
    public static function tryFrom(string $name): BaseEnum
    {
        if (static::hasName($name)) {
            $enumValue = static::getValue($name);

            return new static($enumValue);
        }

        throw new InvalidEnumNameException($name, static::class);
    }

    /**
     * Attempt to instantiate an enum by calling the enum name as a static method.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws InvalidEnumNameException|InvalidEnumValueException|InvalidClassTypeException
     */
    public static function __callStatic(string $method, array $parameters)
    {
        if (static::hasMacro($method)) {
            return static::macroCallStatic($method, $parameters);
        }

        return static::tryFrom($method);
    }

    /**
     *  Delegate magic method calls to macro's or the static call.
     *
     *  While it is not typical to use the magic instantiation dynamically, it may happen
     *  incidentally when calling the instantiation in an instance method of itself.
     *  Even when using the `static::KEY()` syntax, PHP still interprets this is a call to
     *  an instance method when it happens inside an instance method of the same class.
     *
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws InvalidEnumNameException|InvalidEnumValueException|InvalidClassTypeException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return self::__callStatic($method, $parameters);
    }

    /**
     * Checks if this instance is equal to the given enum instance or value.
     *
     * @param mixed $value
     * @return bool
     */
    public function is($value): bool
    {
        if ($value instanceof static) {
            return $this->value === $value->value;
        }

        return $this->value === $value;
    }

    /**
     * Checks if this instance is not equal to the given enum instance or value.
     *
     * @param mixed $value
     * @return bool
     */
    public function isNot($value): bool
    {
        return !$this->is($value);
    }

    /**
     * Checks if a matching enum instance or value is in the given values.
     *
     * @param iterable $values
     * @return bool
     */
    public function in(iterable $values): bool
    {
        foreach ($values as $value) {
            if ($this->is($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a matching enum instance or value is not in the given values.
     *
     * @param iterable $values
     * @return bool
     */
    public function notIn(iterable $values): bool
    {
        foreach ($values as $value) {
            if ($this->is($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return instances of all the contained values.
     *
     * @return Collection
     * @throws InvalidEnumValueException|InvalidClassTypeException
     */
    public static function cases(): Collection
    {
        return collect(array_values(array_map(
            static fn($constantValue): self => new static($constantValue),
            static::getConstants()
        )));
    }

    /**
     * Get all constants defined in the class.
     *
     * @return array
     */
    protected static function getConstants(): array
    {
        return self::getReflection()->getConstants();
    }

    /**
     * Get all or a custom set of the enum values.
     *
     * @param string|array|null $keys
     * @return array
     */
    public static function getValues($keys = null): array
    {
        if ($keys === null) {
            return array_values(static::getConstants());
        }

        return array_map(
            [static::class, 'getValue'],
            is_array($keys) ? $keys : func_get_args(),
        );
    }

    /**
     * Get the value for a single enum key.
     *
     * @param string $key
     * @return mixed
     */
    public static function getValue(string $key)
    {
        return static::getConstants()[$key];
    }

    /**
     * Get all or a custom set of the enum names.
     *
     * @param mixed|null $values
     * @return array
     */
    public static function getNames($values = null): array
    {
        if ($values === null) {
            return array_keys(static::getConstants());
        }

        return array_map(
            [static::class, 'getName'],
            is_array($values) ? $values : func_get_args(),
        );
    }

    /**
     * Get the name for a single enum value.
     *
     * @param mixed $value
     * @return string
     * @throws InvalidEnumValueException
     */
    public static function getName($value): string
    {
        $result = array_search($value, static::getConstants(), true);

        if ($result === false) {
            throw new InvalidEnumValueException($value, static::class);
        }

        return $result;
    }

    /**
     * Return the enum as an array.
     *
     * @return array
     */
    public static function asArray(): array
    {
        return static::getConstants();
    }

    /**
     * Get the enum as an array formatted for a select.
     *
     * @return array
     */
    public static function toSelectArray(): array
    {
        $selectArray = [];

        foreach (static::asArray() as $value) {
            $selectArray[$value] = static::labels()[$value];
        }

        return $selectArray;
    }

    /**
     * Check that the enum contains a specific name.
     *
     * @param string $name
     * @return bool
     */
    public static function hasName(string $name): bool
    {
        return in_array($name, static::getNames(), true);
    }

    /**
     * Check that the enum contains a specific value.
     *
     * @param mixed $value
     * @param bool $strict
     * @return bool
     */
    public static function hasValue($value, bool $strict = true): bool
    {
        $values = static::getValues();

        if ($strict) {
            return in_array($value, $values, true);
        }

        return in_array((string) $value, array_map('strval', $values), true);
    }

    /**
     * Return a plain representation of the enum.
     *
     * @return mixed
     */
    public function toArray()
    {
        return $this->value;
    }

    /**
     * Return a JSON-serializable representation of the enum.
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * Return a string representation of the enum.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}
