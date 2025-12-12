<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

/**
 * @extends \IteratorAggregate<int, int|string>
 */
interface SortedListInterface extends \IteratorAggregate, \Countable, \JsonSerializable
{
    public function add(int|string $value): self;        // keep sorted, return $this for chaining
    public function remove(int|string $value): bool;     // true if removed
    public function contains(int|string $value): bool;

    public function first(): int|string;                 // throws if empty
    public function last(): int|string;                  // throws if empty

    public function isEmpty(): bool;

    /** @return array<int,int|string> */
    public function toArray(): array;
}
