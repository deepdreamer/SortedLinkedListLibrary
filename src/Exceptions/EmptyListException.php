<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Exceptions;

class EmptyListException extends \UnderflowException
{
    public static function create(string $operation = 'operation'): self
    {
        return new self("Cannot perform $operation on an empty list. The list must contain at least one element.");
    }
}
