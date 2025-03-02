<?php

namespace Laraigniter\Enum\Exceptions;

use Laraigniter\Enum\BaseEnum;

class InvalidEnumNameException extends \Exception
{
    /**
     * @param $key
     * @param class-string<BaseEnum<mixed>> $enum
     */
    public function __construct($key, string $enum)
    {
        $keyType = gettype($key);
        $enumKeys = implode(', ', $enum::getNames());
        $enumClassName = class_basename($enum);

        parent::__construct("Cannot construct an instance of {$enumClassName} using the name ({$keyType}) `{$key}`. Possible names are [{$enumKeys}].");
    }
}
