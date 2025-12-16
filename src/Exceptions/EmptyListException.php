<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Exceptions;

class EmptyListException extends \UnderflowException
{
    public static function create(): self
    {
        return new self('List is empty.');
    }
}
