<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Exceptions;

class DifferentListTypesException extends \InvalidArgumentException
{
    public static function create(string $operation = 'operation', ?string $type1 = null, ?string $type2 = null): self
    {
        if ($type1 !== null && $type2 !== null) {
            return new self(
                "Cannot perform $operation on lists with different types. " .
                "First list type: $type1, second list type: $type2. " .
                "Both lists must have the same type (either int or string)."
            );
        }

        return new self(
            "Cannot perform $operation on lists with different types. " .
            "Both lists must have the same type (either int or string)."
        );
    }
}
