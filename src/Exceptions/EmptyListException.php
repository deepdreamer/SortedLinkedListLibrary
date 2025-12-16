<?php

namespace SortedLinkedListLibrary\Exceptions;

class EmptyListException extends \UnderflowException
{
    public static function create(): self
    {
        return new self('List is empty.');
    }
}

