<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Exceptions;

class IndexOutOfRangeException extends \OutOfRangeException
{
    public static function create(int $index): self
    {
        return new self("Index $index out of range");
    }
}
