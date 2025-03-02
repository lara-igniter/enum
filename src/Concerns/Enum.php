<?php

namespace Laraigniter\Enum\Concerns;

interface Enum
{
    /**
     * Checks if this instance is equal to the given enum instance or value.
     *
     * @param mixed $value
     * @return bool
     */
    public function is($value): bool;
}
