<?php

namespace Laraigniter\Enum\Traits;

trait Labels
{
    /**
     * Get the label for a single enum key.
     */
    public function getLabel(): ?string
    {
        return static::labels()[$this->value] ?? null;
    }
}
