<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Exceptions;

class EmptyIterableParameter extends \InvalidArgumentException
{
    public static function create(): self
    {
        return new self('Cannot create list from empty iterable.');
    }
}
