<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Exceptions;

class DifferentListTypesException extends \InvalidArgumentException
{
    public static function create(): self
    {
        return new self('Cannot merge lists of different types.');
    }
}
