<?php

namespace SortedLinkedListLibrary\Exceptions;

class EmptyIterableParameter extends \InvalidArgumentException
{
    static function create(): self
    {
        return new self('Cannot create list from empty iterable.');
    }
}