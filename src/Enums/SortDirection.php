<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Enums;

enum SortDirection: string
{
    case ASC = 'asc';
    case DESC = 'desc';

    public function isAscending(): bool
    {
        return $this === self::ASC;
    }

    public function isDescending(): bool
    {
        return $this === self::DESC;
    }
}
