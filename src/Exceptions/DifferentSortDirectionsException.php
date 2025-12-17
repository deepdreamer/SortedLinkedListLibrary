<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Exceptions;

class DifferentSortDirectionsException extends \InvalidArgumentException
{
    public static function create(
        string $operation = 'operation',
        string $direction1 = 'unknown',
        string $direction2 = 'unknown'
    ): self {
        return new self(
            "Cannot perform $operation on lists with different sort directions. " .
            "First list: $direction1, second list: $direction2. " .
            "Both lists must have the same sort direction (either ASC or DESC). " .
            "Use reverse() to change the sort direction if needed."
        );
    }
}
