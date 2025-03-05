<?php

namespace Laraigniter\Enum\Traits;

trait Icons
{
    /**
     * Get the icon for a single enum key.
     */
    public function getIcon(): ?string
    {
        return static::icons()[$this->value] ?? null;
    }
}
