<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary\Exceptions;

class InvalidTypeException extends \TypeError
{
    public static function forInt(mixed $value): self
    {
        $type = get_debug_type($value);
        return new self(
            "Invalid type: expected int, got $type. " .
            "This list only accepts integer values. " .
            "Value provided: " . (is_scalar($value) ? var_export($value, true) : "[$type]")
        );
    }

    public static function forString(mixed $value): self
    {
        $type = get_debug_type($value);
        return new self(
            "Invalid type: expected string, got $type. " .
            "This list only accepts string values. " .
            "Value provided: " . (is_scalar($value) ? var_export($value, true) : "[$type]")
        );
    }
}
