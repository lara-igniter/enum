<?php

namespace Laraigniter\Enum\Concerns;

interface HasLabel
{
    /**
     * Implement labels at child enum for translation labels
     */
    public static function labels(): array;
}
