<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Enums;

enum ListType: string
{
    case INT = 'int';
    case STRING = 'string';

    public function isInt(): bool
    {
        return $this === self::INT;
    }

    public function isString(): bool
    {
        return $this === self::STRING;
    }
}

