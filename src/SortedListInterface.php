<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use SortedLinkedListLibrary\Enums\ListType;
use SortedLinkedListLibrary\Enums\SortDirection;

/**
 * @extends \IteratorAggregate<int, int|string>
 */
interface SortedListInterface extends \IteratorAggregate, \Countable, \JsonSerializable
{
    public function add(int|string $value): self;        // keep sorted, return $this for chaining
    public function remove(int|string $value): bool;     // true if removed
    public function contains(int|string $value): bool;
    public function merge(self $other): self;            // merge another sorted list into this one
    public function reverse(): self;                     // reverse the list in-place and flip sort order

    public function first(): int|string;                 // throws if empty
    public function last(): int|string;                  // throws if empty

    public function isEmpty(): bool;

    /** @return array<int,int|string> */
    public function toArray(): array;

    // Bulk operations
    public function addAll(iterable $values): self;
    public function removeAll(iterable $values): int;    // returns count removed
    public function clear(): self;

    // Search and filtering
    public function find(callable $predicate): int|string|null;
    public function findAll(callable $predicate): self;  // returns new list
    public function filter(callable $predicate): self;
    public function indexOf(int|string $value): int|null; // returns index or null if not found

    // Range queries
    public function slice(int $offset, ?int $length = null): self; // returns new list
    public function range(int|string $from, int|string $to): self; // values between
    public function valuesGreaterThan(int|string $value): self;
    public function valuesLessThan(int|string $value): self;

    // Set operations
    public function union(SortedListInterface $other): self;           // merge unique
    public function intersect(SortedListInterface $other): self;        // common values
    public function diff(SortedListInterface $other): self;             // values in this but not other
    public function unique(): self;                      // remove duplicates

    // Utility methods
    public function copy(): self;                        // shallow copy
    public function equals(SortedListInterface $other): bool;
    public function min(): int|string|null;
    public function max(): int|string|null;
    public function sum(): int|float;                    // for numeric types

    // Factory/construction methods
    public static function fromArray(array $values, SortDirection $sortDirection = SortDirection::ASC): self;
    public static function fromIterable(iterable $values, SortDirection $sortDirection = SortDirection::ASC): self;

    // Advanced features
    public function removeAt(int $index): int|string;   // returns removed value
    public function removeFirst(int $count = 1): array; // remove and return
    public function removeLast(int $count = 1): array;

    // Query methods
    public function getSortOrder(): SortDirection;       // returns ascending/descending
    public function getType(): ListType;                 // returns ListType enum
    public function getOrNull(int $index): int|string|null;
}
