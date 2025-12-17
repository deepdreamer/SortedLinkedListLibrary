<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Exceptions;

class EmptyIterableParameter extends \InvalidArgumentException
{
    public static function create(string $method = 'fromIterable'): self
    {
        return new self(
            "Cannot create list from empty iterable in $method(). " .
            "The iterable must contain at least one element to determine the list type (int or string). " .
            "Use SortedList::forInts() or SortedList::forStrings() to create an empty list, " .
            "or use SortedList::fromArray([]) which creates an int list by default."
        );
    }
}
