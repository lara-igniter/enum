<?php

namespace Laraigniter\Enum\Exceptions;

use Laraigniter\Enum\BaseEnum;

class InvalidClassTypeException extends \Exception
{
    /**
     * @param class-string<BaseEnum<mixed>> $enum
     */
    public function __construct(string $enum)
    {
        $enumClassName = class_basename($enum);

        parent::__construct("The class {$enumClassName} must be declared as final.");
    }
}
