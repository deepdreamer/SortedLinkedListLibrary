<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Exceptions;

class IndexOutOfRangeException extends \OutOfRangeException
{
    public static function create(int $index, int $listSize = 0): self
    {
        if ($listSize === 0) {
            return new self(
                "Index $index is out of range. The list is empty (size: 0). " .
                "No valid indices available. Add elements to the list first."
            );
        }

        $validRange = $listSize === 1 ? '0' : "0 to " . ($listSize - 1);
        return new self(
            "Index $index is out of range. The list has $listSize element(s). " .
            "Valid indices are $validRange."
        );
    }
}
