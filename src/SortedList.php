<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use SortedLinkedListLibrary\Enums\ListType;
use SortedLinkedListLibrary\Enums\SortDirection;
use SortedLinkedListLibrary\Exceptions\DifferentListTypesException;
use SortedLinkedListLibrary\Exceptions\EmptyIterableParameter;
use SortedLinkedListLibrary\Exceptions\EmptyListException;
use SortedLinkedListLibrary\Exceptions\IndexOutOfRangeException;
use SortedLinkedListLibrary\Exceptions\InvalidTypeException;

class SortedList implements SortedListInterface
{
    private ?ListNode $head = null;

    /** @var non-negative-int $count */
    private int $count = 0;

    /**
     * @param ListType $type
     * @param SortDirection $sortDirection
     */
    public function __construct(
        private ListType $type,
        private SortDirection $sortDirection = SortDirection::ASC,
    ) {
    }

    public static function forInts(SortDirection $sortDirection = SortDirection::ASC): self
    {
        return new self(ListType::INT, $sortDirection);
    }

    public static function forStrings(SortDirection $sortDirection = SortDirection::ASC): self
    {
        return new self(ListType::STRING, $sortDirection);
    }

    /**
     * @return non-negative-int
     */
    public function count(): int
    {
        return $this->count;
    }

    public function isEmpty(): bool
    {
        return $this->head === null;
    }

    public function get(int $index): int|string
    {
        if ($index < 0 || $index >= $this->count) {
            throw IndexOutOfRangeException::create($index);
        }

        /** @var ListNode $current */
        $current = $this->head;
        for ($i = 0; $i < $index; $i++) {
            /** @var ListNode $current */
            $current = $current->next;
        }

        return $current->value;
    }


    /**
     * Insert while keeping the list sorted.
     */
    public function add(int|string $value): self
    {
        $this->assertType($value);

        $newNode = new ListNode($value);

        // Empty list → new head
        if ($this->head === null) {
            $this->head = $newNode;
            $this->count++;
            return $this;
        }

        // Insert at head?
        if ($this->shouldComeBefore($value, $this->head->value)) {
            $newNode->next = $this->head;
            $this->head = $newNode;
            $this->count++;
            return $this;
        }

        // Insert somewhere in the middle or at the end
        $prev = $this->head;
        $current = $this->head->next;

        while ($current !== null && !$this->shouldComeBefore($value, $current->value)) {
            $prev = $current;
            $current = $current->next;
        }

        $prev->next = $newNode;
        $newNode->next = $current;
        $this->count++;

        return $this;
    }

    public function remove(int|string $value): bool
    {
        $this->assertType($value);

        if ($this->head === null) {
            return false;
        }

        if ($this->head->value === $value) {
            $this->head = $this->head->next;
            if ($this->count > 0) {
                $this->count--;
            }
            return true;
        }

        $prev = $this->head;
        $current = $this->head->next;

        while ($current !== null) {
            if ($current->value === $value) {
                $prev->next = $current->next;
                if ($this->count > 0) {
                    $this->count--;
                }
                return true;
            }
            $prev = $current;
            $current = $current->next;
        }

        return false;
    }

    /**
     * Merge another sorted list (same type + direction) into this list in-place.
     * Reuses existing nodes for O(1) extra space and O(n+m) time.
     */
    public function merge(SortedListInterface $other): self
    {
        if ($other === $this || $other->isEmpty()) {
            return $this;
        }


        if ($this->type !== $other->type) {
            throw DifferentListTypesException::create();
        }

        $this->head = $this->mergeNodes($this->head, $other->head);
        $this->count += $other->count;

        // Detach merged list to avoid shared nodes / double-counting.
        $other->head = null;
        $other->count = 0;

        return $this;
    }

    /**
     * Reverse the list in-place and flip the sort order.
     * O(n) time complexity, O(1) space complexity.
     */
    public function reverse(): self
    {
        $this->sortDirection = $this->sortDirection === SortDirection::ASC
            ? SortDirection::DESC
            : SortDirection::ASC;

        // Empty list or single element, no need to reverse
        if ($this->head === null || $this->head->next === null) {
            return $this;
        }

        $prev = null;
        $current = $this->head;

        // Reverse the linked list by reversing pointers
        while ($current !== null) {
            $next = $current->next; // Store the next node
            $current->next = $prev; // Reverse the pointer
            $prev = $current; // Move the pointers forward
            $current = $next; // Move the current pointer forward
        }

        $this->head = $prev;

        return $this;
    }

    public function getIterator(): \Traversable
    {
        $current = $this->head;

        while ($current !== null) {
            yield $current->value;
            $current = $current->next;
        }
    }

    public function contains(int|string $value): bool
    {
        $this->assertType($value);

        $current = $this->head;

        while ($current !== null) {
            $cmp = $this->compare($value, $current->value);

            if ($cmp === 0) {
                return true; // exact match
            }

            // Early-stop for ASC
            if ($this->sortDirection->isAscending() && $cmp < 0) {
                return false;
            }

            // Early-stop for DESC
            if ($this->sortDirection->isDescending() && $cmp > 0) {
                return false;
            }

            $current = $current->next;
        }

        return false;
    }

    public function first(): int|string
    {
        if ($this->head === null) {
            throw EmptyListException::create();
        }

        return $this->head->value;
    }

    public function firstOrNull(): int|string|null
    {
        try {
            return $this->first();
        } catch (EmptyListException) {
            return null;
        }
    }

    public function last(): int|string
    {
        if ($this->head === null) {
            throw EmptyListException::create();
        }

        $current = $this->head;
        while ($current->next !== null) {
            $current = $current->next;
        }

        return $current->value;
    }

    public function lastOrNull(): int|string|null
    {
        try {
            return $this->last();
        } catch (EmptyListException) {
            return null;
        }
    }

    /**
     * @return array<int, int|string>
     */
    public function toArray(): array
    {
        $result = [];
        $current = $this->head;

        while ($current !== null) {
            $result[] = $current->value;
            $current = $current->next;
        }

        return $result;
    }

    /**
     * @return array{'type': 'int'|'string', 'ascending': bool, 'count': non-negative-int, 'values': array<int, int|string>}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->value,        // "int" or "string"
            'ascending' => $this->sortDirection->isAscending(),   // true / false
            'count' => $this->count,       // number of elements
            'values' => $this->toArray(),   // sorted values
        ];
    }

    /**
     * @param positive-int $depth
     */
    public function toJson(int $options = 0, int $depth = 512): string|false
    {
        return json_encode($this->jsonSerialize(), $options, $depth);
    }

    private function assertType(int|string $value): void
    {
        if ($this->type === ListType::INT && !\is_int($value)) {
            throw InvalidTypeException::forInt();
        }
        if ($this->type === ListType::STRING && !\is_string($value)) {
            throw InvalidTypeException::forString();
        }
    }

    /**
     * Comparison helper respecting type + direction.
     */
    private function compare(int|string $a, int|string $b): int
    {
        if ($this->type === ListType::INT) {
            /** @var int $a */
            /** @var int $b */
            return $a <=> $b;
        }

        /** @var string $a */
        /** @var string $b */
        return strcmp($a, $b);
    }

    /**
     * Should $a come before $b in the list?
     */
    private function shouldComeBefore(int|string $a, int|string $b): bool
    {
        $cmp = $this->compare($a, $b);

        return $this->sortDirection->isAscending()
            ? $cmp <= 0   // ascending: a <= b
            : $cmp >= 0;  // descending: a >= b
    }

    /**
     * Merge two sorted linked lists (already validated to share type).
     */
    private function mergeNodes(?ListNode $a, ?ListNode $b): ?ListNode
    {
        if ($a === null) {
            return $b;
        }
        if ($b === null) {
            return $a;
        }

        // Initialize head
        if ($this->shouldComeBefore($a->value, $b->value)) {
            $head = $a;
            $a = $a->next;
        } else {
            $head = $b;
            $b = $b->next;
        }

        $tail = $head;

        // Merge remainder
        while ($a !== null && $b !== null) {
            if ($this->shouldComeBefore($a->value, $b->value)) {
                $tail->next = $a;
                $a = $a->next;
            } else {
                $tail->next = $b;
                $b = $b->next;
            }
            $tail = $tail->next;
        }

        // Attach leftovers
        $tail->next = $a ?? $b;

        return $head;
    }

    // ============================================================================
    // Bulk operations
    // ============================================================================

    public function addAll(iterable $values): self
    {
        foreach ($values as $value) {
            $this->add($value);
        }

        return $this;
    }

    public function removeAll(iterable $values): int
    {
        $removed = 0;
        foreach ($values as $value) {
            if ($this->remove($value)) {
                $removed++;
            }
        }

        return $removed;
    }

    public function clear(): self
    {
        $this->head = null;
        $this->count = 0;
        return $this;
    }

    // ============================================================================
    // Search and filtering
    // ============================================================================

    public function find(callable $predicate): int|string|null
    {
        foreach ($this as $value) {
            if ($predicate($value)) {
                return $value;
            }
        }
        return null;
    }

    public function findAll(callable $predicate): self
    {
        $result = new self($this->type, $this->sortDirection);
        foreach ($this as $value) {
            if ($predicate($value)) {
                $result->add($value);
            }
        }
        return $result;
    }

    public function filter(callable $predicate): self
    {
        $toRemove = [];
        foreach ($this as $value) {
            if (!$predicate($value)) {
                $toRemove[] = $value;
            }
        }
        foreach ($toRemove as $value) {
            $this->remove($value);
        }
        return $this;
    }

    public function indexOf(int|string $value): int|null
    {
        $index = 0;
        foreach ($this as $val) {
            if ($val === $value) {
                return $index;
            }
            $index++;
        }
        return null;
    }

    // ============================================================================
    // Range queries
    // ============================================================================

    public function slice(int $offset, ?int $length = null): self
    {
        $result = new self($this->type, $this->sortDirection);
        $index = 0;
        $added = 0;
        foreach ($this as $value) {
            if ($index >= $offset) {
                if ($length !== null && $added >= $length) {
                    break;
                }
                $result->add($value);
                $added++;
            }
            $index++;
        }
        return $result;
    }

    public function range(int|string $from, int|string $to): self
    {
        $result = new self($this->type, $this->sortDirection);
        foreach ($this as $value) {
            $cmpFrom = $this->compare($value, $from);
            $cmpTo = $this->compare($value, $to);
            if ($this->sortDirection->isAscending()) {
                if ($cmpFrom >= 0 && $cmpTo <= 0) {
                    $result->add($value);
                }
            } else {
                if ($cmpFrom <= 0 && $cmpTo >= 0) {
                    $result->add($value);
                }
            }
        }
        return $result;
    }

    public function valuesGreaterThan(int|string $value): self
    {
        $result = new self($this->type, $this->sortDirection);
        foreach ($this as $val) {
            $cmp = $this->compare($val, $value);
            if (($this->sortDirection->isAscending() && $cmp > 0) || ($this->sortDirection->isDescending() && $cmp < 0)) {
                $result->add($val);
            }
        }
        return $result;
    }

    public function valuesLessThan(int|string $value): self
    {
        $result = new self($this->type, $this->sortDirection);
        foreach ($this as $val) {
            $cmp = $this->compare($val, $value);
            if (($this->sortDirection->isAscending() && $cmp < 0) || ($this->sortDirection->isDescending() && $cmp > 0)) {
                $result->add($val);
            }
        }
        return $result;
    }

    // ============================================================================
    // Set operations
    // ============================================================================

    public function union(SortedListInterface $other): self
    {
        $result = $this->copy();
        foreach ($other as $value) {
            if (!$result->contains($value)) {
                $result->add($value);
            }
        }
        return $result;
    }

    public function intersect(SortedListInterface $other): self
    {
        $result = new self($this->type, $this->sortDirection);
        foreach ($this as $value) {
            if ($other->contains($value)) {
                $result->add($value);
            }
        }
        return $result;
    }

    public function diff(SortedListInterface $other): self
    {
        $result = new self($this->type, $this->sortDirection);
        foreach ($this as $value) {
            if (!$other->contains($value)) {
                $result->add($value);
            }
        }
        return $result;
    }

    public function unique(): self
    {
        $seen = [];
        $toRemove = [];
        foreach ($this as $value) {
            if (isset($seen[$value])) {
                $toRemove[] = $value;
            } else {
                $seen[$value] = true;
            }
        }
        foreach ($toRemove as $value) {
            $this->remove($value);
        }
        return $this;
    }

    // ============================================================================
    // Utility methods
    // ============================================================================

    public function copy(): self
    {
        $result = new self($this->type, $this->sortDirection);
        foreach ($this as $value) {
            $result->add($value);
        }
        return $result;
    }

    public function equals(SortedListInterface $other): bool
    {
        if ($this->count !== $other->count()) {
            return false;
        }
        if ($this->type !== $other->getType()) {
            return false;
        }
        $thisArray = $this->toArray();
        $otherArray = $other->toArray();
        return $thisArray === $otherArray;
    }

    public function min(): int|string|null
    {
        if ($this->isEmpty()) {
            return null;
        }
        return $this->sortDirection->isAscending() ? $this->first() : $this->last();
    }

    public function max(): int|string|null
    {
        if ($this->isEmpty()) {
            return null;
        }
        return $this->sortDirection->isAscending() ? $this->last() : $this->first();
    }

    public function sum(): int|float
    {
        $sum = 0;
        foreach ($this as $value) {
            $sum += $value;
        }
        return $sum;
    }

    // ============================================================================
    // Factory/construction methods
    // ============================================================================

    public static function fromArray(array $values, SortDirection $sortDirection = SortDirection::ASC): self
    {
        if (empty($values)) {
            return new self(ListType::INT, $sortDirection);
        }
        $firstValue = reset($values);
        $type = \is_int($firstValue) ? ListType::INT : ListType::STRING;
        $list = new self($type, $sortDirection);
        foreach ($values as $value) {
            $list->add($value);
        }
        return $list;
    }

    public static function fromIterable(iterable $values, SortDirection $sortDirection = SortDirection::ASC): self
    {
        $firstValue = null;
        foreach ($values as $value) {
            $firstValue = $value;
            break;
        }

        if ($firstValue === null) {
            throw EmptyIterableParameter::create();
        }

        $type = \is_int($firstValue) ? ListType::INT : ListType::STRING;
        $list = new self($type, $sortDirection);
        foreach ($values as $value) {
            $list->add($value);
        }

        return $list;
    }

    public function removeAt(int $index): int|string
    {
        if ($index < 0 || $index >= $this->count) {
            throw IndexOutOfRangeException::create($index);
        }
        $value = $this->get($index);
        $this->remove($value);
        return $value;
    }

    public function removeFirst(int $count = 1): array
    {
        $removed = [];
        for ($i = 0; $i < $count && !$this->isEmpty(); $i++) {
            $removed[] = $this->first();
            $this->remove($this->first());
        }
        return $removed;
    }

    public function removeLast(int $count = 1): array
    {
        $removed = [];
        for ($i = 0; $i < $count && !$this->isEmpty(); $i++) {
            $removed[] = $this->last();
            $this->remove($this->last());
        }

        return array_reverse($removed);
    }

    public function getSortOrder(): SortDirection
    {
        return $this->sortDirection;
    }

    public function getType(): ListType
    {
        return $this->type;
    }

    public function getOrNull(int $index): int|string|null
    {
        if ($index < 0 || $index >= $this->count) {
            return null;
        }

        return $this->get($index);
    }
}
