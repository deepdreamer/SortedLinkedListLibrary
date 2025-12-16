<?php

namespace SortedLinkedListLibrary\Exceptions;

class InvalidTypeException extends \TypeError
{
    public static function forInt(): self
    {
        return new self('This list only accepts int.');
    }

    public static function forString(): self
    {
        return new self('This list only accepts string.');
    }
}

