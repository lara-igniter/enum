<?php

namespace Laraigniter\Enum\Exceptions;

use Laraigniter\Enum\BaseEnum;

class InvalidEnumValueException extends \Exception
{
    /**
     * @param $value
     * @param class-string<BaseEnum<mixed>> $enum
     */
    public function __construct($value, string $enum)
    {
        $valueType = gettype($value);
        $enumValues = implode(', ', $enum::getValues());
        $enumClassName = class_basename($enum);

        parent::__construct("Cannot construct an instance of {$enumClassName} using the value ({$valueType}) `{$value}`. Possible values are [{$enumValues}].");
    }
}
