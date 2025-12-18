<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use SortedLinkedListLibrary\Enums\ListType;
use SortedLinkedListLibrary\Enums\SortDirection;
use SortedLinkedListLibrary\Exceptions\DifferentListTypesException;
use SortedLinkedListLibrary\Exceptions\DifferentSortDirectionsException;
use SortedLinkedListLibrary\Exceptions\EmptyIterableParameter;
use SortedLinkedListLibrary\Exceptions\EmptyListException;
use SortedLinkedListLibrary\Exceptions\IndexOutOfRangeException;
use SortedLinkedListLibrary\Exceptions\InvalidTypeException;

class SortedList implements SortedListInterface
{
    private ?ListNode $head = null;
    private ?ListNode $tail = null;

    /** @var non-negative-int $count */
    private int $count = 0;

    /**
     * Cache for the last insertion point to optimize sequential adds.
     * Points to the last inserted node (or a nearby insertion hint), or null if cache is invalid.
     * This makes sequential inserts (adding values in sorted order) O(1) instead of O(n).
     */
    private ?ListNode $lastInsertPoint = null;

    /**
     * Invalidate the insertion point cache.
     * Should be called whenever the list structure changes (remove, merge, reverse, etc.)
     */
    private function invalidateInsertPointCache(): void
    {
        $this->lastInsertPoint = null;
    }

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

    /**
     * Get the element at the specified index.
     *
     * Time complexity: O(n) where n is the index (worst case: O(n) for last element)
     * Space complexity: O(1)
     *
     * Edge cases:
     * - Negative indices throw IndexOutOfRangeException
     * - Indices >= list size throw IndexOutOfRangeException
     * - Empty list throws IndexOutOfRangeException for any index
     *
     * @param int $index The zero-based index
     * @return int|string The element at the index
     * @throws IndexOutOfRangeException If the index is out of range
     */
    public function getAt(int $index): int|string
    {
        if ($index < 0 || $index >= $this->count) {
            throw IndexOutOfRangeException::create($index, $this->count);
        }

        return $this->nodeAt($index)->value;
    }

    /**
     * Get the element at the specified index, or null if out of range.
     *
     * Time complexity: O(n) where n is the index (worst case: O(n) for last element)
     * Space complexity: O(1)
     *
     * Edge cases:
     * - Negative indices return null (no exception thrown)
     * - Indices >= list size return null (no exception thrown)
     * - Empty list returns null for any index
     *
     * @param int $index The zero-based index
     * @return int|string|null The element at the index, or null if out of range
     */
    public function getAtOrNull(int $index): int|string|null
    {
        if ($index < 0 || $index >= $this->count) {
            return null;
        }

        return $this->getAt($index);
    }

    /**
     * Get the node at the given index by traversing from the nearest end (head or tail).
     *
     * @param int $index
     * @return ListNode
     */
    private function nodeAt(int $index): ListNode
    {
        /** @var ListNode $head */
        $head = $this->head;
        /** @var ListNode $tail */
        $tail = $this->tail;

        $half = intdiv($this->count, 2);
        if ($index <= $half) {
            $current = $head;
            for ($i = 0; $i < $index; $i++) {
                /** @var ListNode $current */
                $current = $current->next;
            }
            return $current;
        }

        $current = $tail;
        for ($i = $this->count - 1; $i > $index; $i--) {
            /** @var ListNode $current */
            $current = $current->prev;
        }
        return $current;
    }


    /**
     * Insert while keeping the list sorted.
     *
     * Time complexity: O(n) worst case, O(1) best case (sequential inserts)
     * Space complexity: O(1)
     *
     * Optimizations:
     * - Uses insertion point cache to optimize sequential adds (values in sorted order)
     * - Sequential inserts are O(1) each, making n sequential inserts O(n) instead of O(n²)
     *
     * @param int|string $value The value to add (must match list type)
     * @return self Returns $this for method chaining
     * @throws InvalidTypeException If the value type doesn't match the list type
     */
    public function add(int|string $value): self
    {
        $this->assertType($value);

        $newNode = new ListNode($value);

        // Empty list so create first value and set head pointing to it
        if ($this->head === null) {
            $this->head = $newNode;
            $this->tail = $newNode;
            $this->count++;
            $this->invalidateInsertPointCache();
            return $this;
        }

        // Head is not null, list is not empty
        if ($this->shouldComeBefore($value, $this->head->value)) {
            // New value should come at the beginning of the list
            $newNode->next = $this->head;
            $this->head->prev = $newNode;
            $newNode->prev = null;
            $this->head = $newNode;
            $this->count++;
            $this->invalidateInsertPointCache();
            return $this;
        }

        // Fast-path: append to the end when value belongs after current tail.
        // This keeps sequential inserts O(1) without even touching the cache.
        if ($this->tail !== null && !$this->shouldComeBefore($value, $this->tail->value)) {
            $this->tail->next = $newNode;
            $newNode->prev = $this->tail;
            $this->tail = $newNode;
            $this->count++;
            $this->lastInsertPoint = $newNode;
            return $this;
        }

        // Try to use cached insertion point for optimization (sequential inserts)
        $prev = null;
        $current = null;

        if ($this->lastInsertPoint !== null) {
            // If the next value belongs BEFORE the cached point, we can traverse backwards
            // in a doubly-linked list instead of restarting from head.
            if ($this->shouldComeBefore($value, $this->lastInsertPoint->value)) {
                $current = $this->lastInsertPoint;

                while ($current->prev !== null && $this->shouldComeBefore($value, $current->prev->value)) {
                    $current = $current->prev;
                }

                // Insert before $current (and after $current->prev)
                $prev = $current->prev;
                if ($prev === null) {
                    // Should be unreachable because the "insert at head" case is handled above,
                    // but keep it safe.
                    $newNode->next = $this->head;
                    /** @var ListNode $head */
                    $head = $this->head;
                    $head->prev = $newNode;
                    $newNode->prev = null;
                    $this->head = $newNode;
                    $this->count++;
                    $this->invalidateInsertPointCache();
                    return $this;
                }

                $prev->next = $newNode;
                $newNode->prev = $prev;
                $newNode->next = $current;
                $current->prev = $newNode;
                $this->count++;
                $this->lastInsertPoint = $newNode;
                return $this;
            }

            // At this point, the value does NOT belong before the cached point,
            // so it belongs at/after it. Starting from cached point is always safe.
            $prev = $this->lastInsertPoint;
            $current = $this->lastInsertPoint->next;
        }

        // If cache not usable, start from head
        if ($prev === null) {
            $prev = $this->head;
            $current = $this->head->next;
        }

        // Find insertion point
        while ($current !== null && !$this->shouldComeBefore($value, $current->value)) {
            $prev = $current;
            $current = $current->next;
        }

        // Insert the node
        $prev->next = $newNode;
        $newNode->next = $current;
        $newNode->prev = $prev;
        if ($current !== null) {
            $current->prev = $newNode;
        } else {
            // Inserted at the end
            $this->tail = $newNode;
        }
        $this->count++;

        // Update cache to point to the node we just inserted
        $this->lastInsertPoint = $newNode;

        return $this;
    }

    /**
     * Remove and return the element at the specified index.
     *
     * Time complexity: O(n) where n is the index + O(n) for removal = O(n)
     * Space complexity: O(1)
     *
     * Edge cases:
     * - Negative indices throw IndexOutOfRangeException
     * - Indices >= list size throw IndexOutOfRangeException
     * - Empty list throws IndexOutOfRangeException for any index
     *
     * @param int $index The zero-based index
     * @return int|string The removed element
     * @throws IndexOutOfRangeException If the index is out of range
     */
    public function removeAt(int $index): int|string
    {
        if ($index < 0 || $index >= $this->count) {
            throw IndexOutOfRangeException::create($index, $this->count);
        }

        // Single traversal to find and remove (O(min(index, n-index)))
        if ($index === 0) {
            /** @var ListNode $head */
            $head = $this->head;
            $value = $head->value;
            $this->head = $head->next;
            if ($this->head !== null) {
                $this->head->prev = null;
            } else {
                $this->tail = null;
            }
            $this->count--;
            $this->invalidateInsertPointCache();
            return $value;
        }

        // Fast-path: remove tail
        if ($index === $this->count - 1) {
            /** @var ListNode $tail */
            $tail = $this->tail;
            $value = $tail->value;
            $this->tail = $tail->prev;
            if ($this->tail !== null) {
                $this->tail->next = null;
            } else {
                $this->head = null;
            }
            $this->count--;
            $this->invalidateInsertPointCache();
            return $value;
        }

        $current = $this->nodeAt($index);
        $value = $current->value;
        /** @var ListNode $prev */
        $prev = $current->prev;
        /** @var ListNode $next */
        $next = $current->next;
        $prev->next = $next;
        $next->prev = $prev;
        $this->count--;
        $this->invalidateInsertPointCache();

        return $value;
    }

    /**
     * Remove and return the first N elements from the list.
     *
     * Time complexity: O(count) - direct pointer manipulation
     * Space complexity: O(count) for the returned array
     *
     * Edge cases:
     * - If count is 0 or negative, returns empty array
     * - If count exceeds list size, returns all elements
     * - If list is empty, returns empty array
     *
     * @param int $count The number of elements to remove (default: 1, must be >= 0)
     * @return array<int, int|string> An array of the removed elements in order
     */
    public function removeFirst(int $count = 1): array
    {
        if ($count <= 0 || $this->head === null) {
            return [];
        }

        $removed = [];
        $actualCount = min($count, $this->count);

        // Directly remove from head (O(count))
        for ($i = 0; $i < $actualCount; $i++) {
            if ($this->head === null) {
                break;
            }
            /** @var ListNode $head */
            $head = $this->head;
            $removed[] = $head->value;
            $this->head = $head->next;
            if ($this->head !== null) {
                $this->head->prev = null;
            } else {
                $this->tail = null;
            }
            if ($this->count > 0) {
                $this->count--;
            }
            $this->invalidateInsertPointCache();
        }

        return $removed;
    }

    /**
     * Remove and return the last N elements from the list.
     *
     * Time complexity: O(n) - single pass to find Nth-to-last element
     * Space complexity: O(count) for the returned array
     *
     * Edge cases:
     * - If count is 0 or negative, returns empty array
     * - If count exceeds list size, returns all elements
     * - If list is empty, returns empty array
     *
     * @param int $count The number of elements to remove (default: 1, must be >= 0)
     * @return array<int, int|string> An array of the removed elements in order
     */
    public function removeLast(int $count = 1): array
    {
        if ($count <= 0 || $this->tail === null) {
            return [];
        }

        $actualCount = min($count, $this->count);

        // Removing everything
        if ($actualCount >= $this->count) {
            $removed = $this->toArray();
            $this->head = null;
            $this->tail = null;
            $this->count = 0;
            $this->invalidateInsertPointCache();
            return $removed;
        }

        // Walk backwards from tail (O(actualCount)) and collect values.
        $removedReversed = [];
        $current = $this->tail;
        for ($i = 0; $i < $actualCount; $i++) {
            /** @var ListNode $current */
            $removedReversed[] = $current->value;
            $current = $current->prev;
        }

        /** @var ListNode $current */
        $this->tail = $current;
        $this->tail->next = null;

        // Safe to subtract: actualCount is min(count, $this->count), so $this->count >= actualCount
        /** @var int<0, max> $newCount */
        $newCount = $this->count - $actualCount;
        $this->count = max(0, $newCount);

        $this->invalidateInsertPointCache();
        return array_reverse($removedReversed);
    }

    /**
     * Remove the first occurrence of the given value.
     *
     * Time complexity: O(n) in the worst case
     * Space complexity: O(1)
     *
     * Uses early termination optimization: stops searching once we've passed
     * the value's position in the sorted list.
     *
     * @param int|string $value The value to remove
     * @return bool True if the value was found and removed, false otherwise
     * @throws InvalidTypeException If the value type doesn't match the list type
     */
    public function remove(int|string $value): bool
    {
        $this->assertType($value);

        if ($this->head === null) {
            return false;
        }

        if ($this->head->value === $value) {
            $this->head = $this->head->next; // remove
            if ($this->head !== null) {
                $this->head->prev = null;
            } else {
                $this->tail = null;
            }
            if ($this->count > 0) {
                $this->count--;
            }
            $this->invalidateInsertPointCache();
            return true;
        }

        $prev = $this->head;
        $current = $this->head->next;

        while ($current !== null) {
            if ($this->sortDirection->isAscending() && $current->value > $value) {
                break;
            }

            if ($this->sortDirection->isDescending() && $current->value < $value) {
                break;
            }

            if ($current->value === $value) {
                $prev->next = $current->next; // remove
                if ($current->next !== null) {
                    $current->next->prev = $prev;
                } else {
                    $this->tail = $prev;
                }
                if ($this->count > 0) {
                    $this->count--;
                }
                $this->invalidateInsertPointCache();
                return true;
            }

            $prev = $current;
            $current = $current->next;
        }

        return false;
    }

    /**
     * Remove all occurrences of the given value.
     *
     * Time complexity: O(n) - single pass through the list
     * Space complexity: O(1)
     *
     * Uses early termination optimization: stops searching once we've passed
     * all occurrences of the value in the sorted list.
     *
     * @param int|string $value The value to remove
     * @return int The number of occurrences removed
     * @throws InvalidTypeException If the value type doesn't match the list type
     */
    public function removeEveryOccurrence(int|string $value): int
    {
        $this->assertType($value);

        if ($this->head === null) {
            return 0;
        }

        $deletedCount = 0;
        $current = $this->head;
        $isAscending = $this->sortDirection->isAscending();

        while ($current !== null) {
            if (($isAscending && $current->value > $value) || (!$isAscending && $current->value < $value)) {
                break;
            }

            if ($current->value === $value) {
                $next = $current->next;
                $prev = $current->prev;

                // Remove current
                if ($prev !== null) {
                    $prev->next = $next;
                } else {
                    $this->head = $next;
                }
                if ($next !== null) {
                    $next->prev = $prev;
                } else {
                    $this->tail = $prev;
                }

                if ($this->count > 0) {
                    $this->count--;
                }
                $deletedCount++;

                $current = $next;
                continue;
            } elseif ($deletedCount > 0) {
                // Early break: sorted list, if current value is not equal to the value to remove,
                // we removed all occurrences of it already
                break;
            }

            $current = $current->next;
        }

        if ($deletedCount > 0) {
            $this->invalidateInsertPointCache();
        }

        return $deletedCount;
    }

    /**
     * Merge another sorted list (same type + direction) into this list in-place.
     * Optimized: Reuses existing nodes for O(1) extra space when merging SortedList instances.
     * Falls back to O(m) space when merging other SortedListInterface implementations.
     * Time complexity: O(n+m) in both cases.
     */
    public function merge(SortedListInterface $other): self
    {
        if ($other === $this || $other->isEmpty()) {
            return $this;
        }

        if ($this->type !== $other->getType()) {
            throw DifferentListTypesException::create(
                'merge()',
                $this->type->value,
                $other->getType()->value
            );
        }

        if ($this->sortDirection !== $other->getSortOrder()) {
            throw DifferentSortDirectionsException::create(
                'merge()',
                $this->sortDirection->value,
                $other->getSortOrder()->value
            );
        }

        // Optimized path: reuse nodes when merging SortedList instances
        if ($other instanceof SortedList) {
            if ($this->head === null) {
                // This list is empty: take over other's chain directly
                $this->head = $other->head;
                $this->tail = $other->tail;
                $this->count = $other->count;
            } else {
                $this->head = $this->mergeNodes($this->head, $other->head);
                $this->count += $other->count;
            }

            // Detach merged list to avoid shared nodes / double-counting.
            $other->head = null;
            $other->tail = null;
            $other->count = 0;
        } else {
            // Generic path: convert other list to array and merge
            // Since other is already sorted, we can merge directly without re-sorting
            // This is O(n+m) instead of O(n×m) when using add() in a loop
            $otherArray = $other->toArray();
            if (!empty($otherArray)) {
                $this->head = $this->mergeArrayWithList($otherArray, $this->head);
                $this->count += count($otherArray);
            }
        }

        $this->invalidateInsertPointCache();
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

        $current = $this->head;

        // Reverse the linked list by swapping next/prev pointers
        while ($current !== null) {
            $next = $current->next; // Store original next
            $current->next = $current->prev;
            $current->prev = $next;
            $current = $next;
        }

        // Swap head and tail
        $oldHead = $this->head;
        $this->head = $this->tail;
        $this->tail = $oldHead;

        // Ensure proper termination
        /** @var ListNode $head */
        $head = $this->head;
        /** @var ListNode $tail */
        $tail = $this->tail;
        $head->prev = null;
        $tail->next = null;
        $this->invalidateInsertPointCache();

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

    /**
     * Check if the list contains a value.
     *
     * Time complexity: O(n) in the worst case, but uses early termination
     * Space complexity: O(1)
     *
     * Early termination: stops searching once we've passed the value's
     * position in the sorted list (since values are sorted).
     *
     * @param int|string $value The value to search for
     * @return bool True if the value is found, false otherwise
     * @throws InvalidTypeException If the value type doesn't match the list type
     */
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
            throw EmptyListException::create('first()');
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
        if ($this->tail === null) {
            throw EmptyListException::create('last()');
        }
        return $this->tail->value;
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
            throw InvalidTypeException::forInt($value);
        }
        if ($this->type === ListType::STRING && !\is_string($value)) {
            throw InvalidTypeException::forString($value);
        }
    }


    // ============================================================================
    // Bulk operations
    // ============================================================================

    /** @param iterable<int|string> $values */
    public function addAll(iterable $values): self
    {
        // Collect values into array with type checking
        $newValues = [];
        foreach ($values as $value) {
            /** @var int|string $value */
            $this->assertType($value);
            $newValues[] = $value;
        }

        if (empty($newValues)) {
            return $this;
        }

        // Sort the new values according to list's sort direction
        if ($this->type === ListType::INT) {
            sort($newValues, SORT_NUMERIC);
        } else {
            sort($newValues, SORT_STRING);
        }

        if ($this->sortDirection === SortDirection::DESC) {
            $newValues = array_reverse($newValues);
        }

        // Merge sorted array with existing sorted linked list (O(n + m))
        $this->head = $this->mergeArrayWithList($newValues, $this->head);
        $this->count += count($newValues);
        $this->invalidateInsertPointCache();

        return $this;
    }

    /**
     * Remove the first occurrence of each value in the iterable.
     *
     * Time complexity: O(m log m + n + m) - Optimized: sorts values to remove then does single-pass merge-like removal
     * Space complexity: O(m) for the sorted values array
     *
     * Optimizations:
     * - Sorts values to remove, then performs single-pass removal
     * - O(n+m) removal instead of O(n×m) when calling remove() for each value
     *
     * @param iterable<int|string> $values The values to remove
     * @return int The number of values successfully removed
     * @throws InvalidTypeException If any value type doesn't match the list type
     */
    public function removeAll(iterable $values): int
    {
        // Convert to array and sort for optimized single-pass removal
        $valuesArray = [];
        foreach ($values as $value) {
            /** @var int|string $value */
            $this->assertType($value);
            $valuesArray[] = $value;
        }

        if (empty($valuesArray)) {
            return 0;
        }

        // Sort values according to list's sort direction for efficient merging
        if ($this->type === ListType::INT) {
            sort($valuesArray, SORT_NUMERIC);
        } else {
            sort($valuesArray, SORT_STRING);
        }

        if ($this->sortDirection === SortDirection::DESC) {
            $valuesArray = array_reverse($valuesArray);
        }

        // Single-pass removal: O(n+m) instead of O(n×m)
        $removed = 0;
        $valueIndex = 0;
        $valueCount = count($valuesArray);
        $prev = null;
        $current = $this->head;
        $isAscending = $this->sortDirection->isAscending();

        while ($current !== null && $valueIndex < $valueCount) {
            $valueToRemove = $valuesArray[$valueIndex];
            $cmp = $this->compare($current->value, $valueToRemove);

            if ($cmp === 0) {
                // Found value to remove - remove first occurrence only
                $next = $current->next;
                if ($prev !== null) {
                    $prev->next = $next;
                } else {
                    $this->head = $next;
                }
                if ($next !== null) {
                    $next->prev = $prev;
                } else {
                    $this->tail = $prev;
                }

                if ($this->count > 0) {
                    $this->count--;
                }
                $removed++;

                // Move to next value to remove (skip duplicates in values array)
                while ($valueIndex < $valueCount && $valuesArray[$valueIndex] === $valueToRemove) {
                    $valueIndex++;
                }

                // Advance current but don't advance prev (it stays at node before removed)
                $current = $next;
            } elseif (($isAscending && $cmp < 0) || (!$isAscending && $cmp > 0)) {
                // Current value is before value to remove in sorted order - advance in list
                $prev = $current;
                $current = $current->next;
            } else {
                // Current value is after value to remove in sorted order - advance in values array
                $valueIndex++;
            }
        }

        if ($removed > 0) {
            $this->invalidateInsertPointCache();
        }
        return $removed;
    }

    /**
     * Remove all occurrences of each value in the iterable.
     *
     * Time complexity: O(m log m + n + m) - Optimized: sorts values to remove then does single-pass merge-like removal
     * Space complexity: O(m) for the sorted values array
     *
     * Optimizations:
     * - Sorts values to remove, then performs single-pass removal of all occurrences
     * - O(n+m) removal instead of O(n×m) when calling removeEveryOccurrence() for each value
     *
     * @param iterable<int|string> $values The values to remove
     * @return int The total number of occurrences removed
     * @throws InvalidTypeException If any value type doesn't match the list type
     */
    public function removeAllAndEveryOccurrence(iterable $values): int
    {
        // Convert to array and sort for optimized single-pass removal
        $valuesArray = [];
        foreach ($values as $value) {
            /** @var int|string $value */
            $this->assertType($value);
            $valuesArray[] = $value;
        }

        if (empty($valuesArray)) {
            return 0;
        }

        // Sort values according to list's sort direction for efficient merging
        if ($this->type === ListType::INT) {
            sort($valuesArray, SORT_NUMERIC);
        } else {
            sort($valuesArray, SORT_STRING);
        }

        if ($this->sortDirection === SortDirection::DESC) {
            $valuesArray = array_reverse($valuesArray);
        }

        // Single-pass removal: O(n+m) instead of O(n×m)
        // Remove ALL occurrences of each value
        $removed = 0;
        $valueIndex = 0;
        $valueCount = count($valuesArray);
        $prev = null;
        $current = $this->head;
        $isAscending = $this->sortDirection->isAscending();

        while ($current !== null && $valueIndex < $valueCount) {
            $valueToRemove = $valuesArray[$valueIndex];
            $cmp = $this->compare($current->value, $valueToRemove);

            if ($cmp === 0) {
                // Found value to remove - remove this occurrence
                $next = $current->next;
                if ($prev !== null) {
                    $prev->next = $next;
                } else {
                    $this->head = $next;
                }
                if ($next !== null) {
                    $next->prev = $prev;
                } else {
                    $this->tail = $prev;
                }

                if ($this->count > 0) {
                    $this->count--;
                }
                $removed++;

                // Advance current but don't advance prev (it stays at node before removed)
                $current = $next;
            } elseif (($isAscending && $cmp < 0) || (!$isAscending && $cmp > 0)) {
                // Current value is before value to remove in sorted order - advance in list
                $prev = $current;
                $current = $current->next;
            } else {
                // Current value is after value to remove in sorted order - advance in values array
                // Skip duplicates in values array
                while ($valueIndex < $valueCount && $valuesArray[$valueIndex] === $valueToRemove) {
                    $valueIndex++;
                }
            }
        }

        if ($removed > 0) {
            $this->invalidateInsertPointCache();
        }
        return $removed;
    }

    public function clear(): self
    {
        $this->head = null;
        $this->tail = null;
        $this->count = 0;
        $this->invalidateInsertPointCache();
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
        // Collect matching values (O(n))
        $matches = [];
        foreach ($this as $value) {
            if ($predicate($value)) {
                $matches[] = $value;
            }
        }

        // Build linked list from collected values (O(k) where k = matches)
        // Values are already sorted since we iterated in order
        $result = new self($this->type, $this->sortDirection);
        if (!empty($matches)) {
            $result->head = $result->arrayToLinkedList($matches);
            $result->count = count($matches);
        }

        return $result;
    }

    public function filter(callable $predicate): self
    {
        // Collect values to keep (O(n))
        $toKeep = [];
        foreach ($this as $value) {
            if ($predicate($value)) {
                $toKeep[] = $value;
            }
        }

        // Rebuild linked list from kept values (O(k) where k = kept items)
        // Values are already sorted since we iterated in order
        $this->rebuildFromSortedArray($toKeep);

        return $this;
    }

    public function indexOf(int|string $value): int|null
    {
        $this->assertType($value);

        if ($this->head === null || $this->tail === null) {
            return null;
        }

        // Quick bounds check (O(1)) using both ends.
        $min = $this->sortDirection->isAscending() ? $this->head->value : $this->tail->value;
        $max = $this->sortDirection->isAscending() ? $this->tail->value : $this->head->value;
        if ($this->compare($value, $min) < 0 || $this->compare($value, $max) > 0) {
            return null;
        }

        // Bidirectional scan using next/prev.
        // This can reduce work when the match (or the failure) is near the tail.
        $left = $this->head;
        $right = $this->tail;
        $leftIndex = 0;
        $rightIndex = $this->count - 1;

        while ($leftIndex <= $rightIndex) {
            if ($left->value === $value) {
                return $leftIndex; // first occurrence by definition
            }

            if ($right->value === $value) {
                // Walk back to the first occurrence of this value (duplicates are consecutive in a sorted list).
                while ($right->prev !== null && $right->prev->value === $value) {
                    $right = $right->prev;
                    $rightIndex--;
                }
                return $rightIndex;
            }

            // Narrow search interval from both ends
            /** @var ListNode $left */
            $left = $left->next;
            /** @var ListNode $right */
            $right = $right->prev;
            $leftIndex++;
            $rightIndex--;
        }

        return null;
    }

    // ============================================================================
    // Range queries
    // ============================================================================

    public function slice(int $offset, ?int $length = null): self
    {
        // Collect values for slice (O(n))
        $sliceValues = [];
        $index = 0;
        foreach ($this as $value) {
            if ($index >= $offset) {
                if ($length !== null && count($sliceValues) >= $length) {
                    break;
                }
                $sliceValues[] = $value;
            }
            $index++;
        }

        // Build linked list from collected values (O(k) where k = slice length)
        // Values are already sorted since we iterated in order
        $result = new self($this->type, $this->sortDirection);
        if (!empty($sliceValues)) {
            $result->head = $result->arrayToLinkedList($sliceValues);
            $result->count = count($sliceValues);
        }

        return $result;
    }

    public function range(int|string $from, int|string $to): self
    {
        $this->assertType($from);
        $this->assertType($to);

        // Collect values in range (O(n))
        $rangeValues = [];
        foreach ($this as $value) {
            $cmpFrom = $this->compare($value, $from);
            $cmpTo = $this->compare($value, $to);
            if ($this->sortDirection->isAscending()) {
                if ($cmpFrom >= 0 && $cmpTo <= 0) {
                    $rangeValues[] = $value;
                } elseif ($cmpFrom > 0) {
                    // Early termination: we've passed the range
                    break;
                }
            } else {
                if ($cmpFrom <= 0 && $cmpTo >= 0) {
                    $rangeValues[] = $value;
                } elseif ($cmpFrom < 0) {
                    // Early termination: we've passed the range
                    break;
                }
            }
        }

        // Build linked list from collected values (O(k) where k = range size)
        // Values are already sorted since we iterated in order
        $result = new self($this->type, $this->sortDirection);
        if (!empty($rangeValues)) {
            $result->head = $result->arrayToLinkedList($rangeValues);
            $result->count = count($rangeValues);
        }

        return $result;
    }

    /**
     * Get all values greater than the given value.
     *
     * Time complexity: O(n) - Optimized: collects matching values then builds list directly
     * Space complexity: O(k) where k is the number of matching values
     *
     * Optimizations:
     * - Collects matching values into array (already sorted), then builds list directly
     * - O(n) instead of O(n²) when using add() in a loop
     * - Uses early termination for descending lists
     *
     * @param int|string $value The value to compare against
     * @return self A new list containing values greater than the given value
     */
    public function valuesGreaterThan(int|string $value): self
    {
        // Doubly-linked optimization:
        // - ASC: values > $value are a contiguous suffix, so traverse from tail backwards (O(k))
        // - DESC: values > $value are a contiguous prefix, so traverse from head forwards with early stop (O(k))
        $matchingValues = [];
        if ($this->tail !== null && $this->sortDirection->isAscending()) {
            $current = $this->tail;
            while ($current !== null) {
                if ($this->compare($current->value, $value) > 0) {
                    $matchingValues[] = $current->value;
                    $current = $current->prev;
                } else {
                    break;
                }
            }
            $matchingValues = array_reverse($matchingValues);
        } else {
            foreach ($this as $val) {
                $cmp = $this->compare($val, $value);
                if ($this->sortDirection->isDescending()) {
                    if ($cmp > 0) {
                        $matchingValues[] = $val;
                    } else {
                        break; // early stop for descending lists
                    }
                } else {
                    if ($cmp > 0) {
                        $matchingValues[] = $val;
                    }
                }
            }
        }

        $result = new self($this->type, $this->sortDirection);
        if (!empty($matchingValues)) {
            $result->head = $result->arrayToLinkedList($matchingValues);
            $result->count = count($matchingValues);
        }

        return $result;
    }

    /**
     * Get all values less than the given value.
     *
     * Time complexity: O(n) - Optimized: collects matching values then builds list directly
     * Space complexity: O(k) where k is the number of matching values
     *
     * Optimizations:
     * - Collects matching values into array (already sorted), then builds list directly
     * - O(n) instead of O(n²) when using add() in a loop
     * - Uses early termination for ascending lists
     *
     * @param int|string $value The value to compare against
     * @return self A new list containing values less than the given value
     */
    public function valuesLessThan(int|string $value): self
    {
        // Doubly-linked optimization:
        // - DESC: values < $value are a contiguous suffix, so traverse from tail backwards (O(k))
        // - ASC: values < $value are a contiguous prefix, so traverse from head forwards with early stop (O(k))
        $matchingValues = [];
        if ($this->tail !== null && $this->sortDirection->isDescending()) {
            $current = $this->tail;
            while ($current !== null) {
                if ($this->compare($current->value, $value) < 0) {
                    $matchingValues[] = $current->value;
                    $current = $current->prev;
                } else {
                    break;
                }
            }
            // We collected from smallest→largest; reverse to keep DESC order.
            $matchingValues = array_reverse($matchingValues);
        } else {
            foreach ($this as $val) {
                $cmp = $this->compare($val, $value);
                if ($this->sortDirection->isAscending()) {
                    if ($cmp < 0) {
                        $matchingValues[] = $val;
                    } else {
                        break; // early stop for ascending lists
                    }
                } else {
                    if ($cmp < 0) {
                        $matchingValues[] = $val;
                    }
                }
            }
        }

        $result = new self($this->type, $this->sortDirection);
        if (!empty($matchingValues)) {
            $result->head = $result->arrayToLinkedList($matchingValues);
            $result->count = count($matchingValues);
        }

        return $result;
    }

    // ============================================================================
    // Set operations
    // ============================================================================

    public function union(SortedListInterface $other): self
    {
        if ($this->type !== $other->getType()) {
            throw DifferentListTypesException::create(
                'union()',
                $this->type->value,
                $other->getType()->value
            );
        }

        // Merge both lists first (O(n+m))
        $result = $this->copy();
        $otherCopy = $other->copy();
        $result->merge($otherCopy);

        // Deduplicate in a single pass (O(n+m))
        // Since merged list is sorted, duplicates are consecutive
        if ($result->head === null) {
            return $result;
        }

        $prev = $result->head;
        $prev->prev = null;
        $current = $result->head->next;
        $result->count = 1; // Reset count, we'll recalculate

        while ($current !== null) {
            if ($prev->value !== $current->value) {
                // Different value, keep it
                $prev->next = $current;
                $current->prev = $prev;
                $prev = $current;
                $result->count++;
            }
            // If same value, skip it (don't update prev)
            $current = $current->next;
        }

        // Terminate the list
        $prev->next = null;
        $result->tail = $prev;

        return $result;
    }

    public function unionWithDuplicates(SortedListInterface $other): self
    {
        if ($this->type !== $other->getType()) {
            throw DifferentListTypesException::create(
                'unionWithDuplicates()',
                $this->type->value,
                $other->getType()->value
            );
        }

        // Simply merge - it's already O(n+m) and preserves duplicates
        $result = $this->copy();
        $otherCopy = $other->copy();
        $result->merge($otherCopy);

        return $result;
    }

    public function intersect(SortedListInterface $other): self
    {
        if ($this->type !== $other->getType()) {
            throw DifferentListTypesException::create(
                'intersect()',
                $this->type->value,
                $other->getType()->value
            );
        }

        // Optimized: O(n+m) merge-like approach since both lists are sorted
        // Convert to arrays for easier iteration (O(n+m))
        $thisArray = $this->toArray();
        $otherArray = $other->toArray();

        $intersectValues = [];
        $thisIndex = 0;
        $otherIndex = 0;
        $thisCount = count($thisArray);
        $otherCount = count($otherArray);

        // Merge-like intersection: advance both indices
        while ($thisIndex < $thisCount && $otherIndex < $otherCount) {
            $thisValue = $thisArray[$thisIndex];
            $otherValue = $otherArray[$otherIndex];
            $cmp = $this->compare($thisValue, $otherValue);

            if ($cmp === 0) {
                // Values match - add to intersection
                $intersectValues[] = $thisValue;
                $thisIndex++;
                $otherIndex++;
            } elseif ($cmp < 0) {
                // This value is smaller, advance this index
                $thisIndex++;
            } else {
                // Other value is smaller, advance other index
                $otherIndex++;
            }
        }

        // Build linked list from collected values (O(k) where k = intersection size)
        $result = new self($this->type, $this->sortDirection);
        if (!empty($intersectValues)) {
            $result->head = $result->arrayToLinkedList($intersectValues);
            $result->count = count($intersectValues);
        }

        return $result;
    }

    public function diff(SortedListInterface $other): self
    {
        if ($this->type !== $other->getType()) {
            throw DifferentListTypesException::create(
                'diff()',
                $this->type->value,
                $other->getType()->value
            );
        }

        // Optimized: O(n+m) merge-like approach since both lists are sorted
        // Convert to arrays for easier iteration (O(n+m))
        $thisArray = $this->toArray();
        $otherArray = $other->toArray();

        $diffValues = [];
        $thisIndex = 0;
        $otherIndex = 0;
        $thisCount = count($thisArray);
        $otherCount = count($otherArray);

        // Merge-like diff: find values in this list that are not in other
        while ($thisIndex < $thisCount) {
            if ($otherIndex >= $otherCount) {
                // Other list exhausted, all remaining this values are in diff
                $diffValues[] = $thisArray[$thisIndex];
                $thisIndex++;
            } else {
                $thisValue = $thisArray[$thisIndex];
                $otherValue = $otherArray[$otherIndex];
                $cmp = $this->compare($thisValue, $otherValue);

                if ($cmp === 0) {
                    // Values match - skip (not in diff)
                    $thisIndex++;
                    $otherIndex++;
                } elseif ($cmp < 0) {
                    // This value is smaller - it's in diff
                    $diffValues[] = $thisValue;
                    $thisIndex++;
                } else {
                    // Other value is smaller - advance other index
                    $otherIndex++;
                }
            }
        }

        // Build linked list from collected values (O(k) where k = diff size)
        $result = new self($this->type, $this->sortDirection);
        if (!empty($diffValues)) {
            $result->head = $result->arrayToLinkedList($diffValues);
            $result->count = count($diffValues);
        }

        return $result;
    }

    public function unique(): self
    {
        // Collect unique values in order (O(n))
        $unique = [];
        $seen = [];
        foreach ($this as $value) {
            if (!isset($seen[$value])) {
                $unique[] = $value;
                $seen[$value] = true;
            }
        }

        // Rebuild linked list from unique values (O(k) where k = unique items)
        // Values are already sorted since we iterated in order
        $this->rebuildFromSortedArray($unique);

        return $this;
    }

    // ============================================================================
    // Utility methods
    // ============================================================================

    /**
     * Create a shallow copy of this list.
     *
     * Time complexity: O(n) - Optimized: directly copies nodes without re-sorting
     * Space complexity: O(n) for the new list
     *
     * Optimizations:
     * - Directly copies nodes since source is already sorted
     * - O(n) instead of O(n²) when using add() for each element
     *
     * @return self A new list with the same elements and configuration
     */
    public function copy(): self
    {
        $result = new self($this->type, $this->sortDirection);

        if ($this->head === null) {
            return $result;
        }

        // Directly copy nodes since source is already sorted
        // This is O(n) - just iterate and create new nodes
        // Much faster than using add() which is O(n) per element = O(n²) total
        /** @var ListNode $current */
        $current = $this->head;
        $resultHead = new ListNode($current->value);
        $resultHead->prev = null;
        $resultCurrent = $resultHead;
        $result->count = 1;

        $current = $current->next;
        while ($current !== null) {
            $newNode = new ListNode($current->value);
            $newNode->prev = $resultCurrent;
            $resultCurrent->next = $newNode;
            $resultCurrent = $newNode;
            $result->count++;
            $current = $current->next;
        }

        $result->head = $resultHead;
        $result->tail = $resultCurrent;
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
            /** @var int|string $value */
            if (\is_int($value)) {
                $sum += $value;
            } else {
                $sum += (float) $value;
            }
        }
        return $sum;
    }

    // ============================================================================
    // Factory/construction methods
    // ============================================================================

    /**
     * Creates a SortedList from an array of values.
     *
     * The list type (int or string) is automatically determined from the first value.
     * If the array is empty, an integer list is created by default.
     *
     * Time complexity: O(m log m + m) - Optimized: uses addAll() which sorts then merges
     * Space complexity: O(m) for the new list
     *
     * Optimizations:
     * - Uses addAll() instead of individual add() calls
     * - O(m log m + m) instead of O(m²) when using add() for each element
     *
     * @param array<int|string> $values The values to add to the list
     * @param SortDirection $sortDirection The sort direction (default: ASC)
     * @return self A new sorted list instance
     */
    public static function fromArray(array $values, SortDirection $sortDirection = SortDirection::ASC): self
    {
        if (empty($values)) {
            return new self(ListType::INT, $sortDirection);
        }
        $firstValue = reset($values);
        /** @var int|string $firstValue */
        $type = \is_int($firstValue) ? ListType::INT : ListType::STRING;
        $list = new self($type, $sortDirection);
        // Use addAll() instead of individual add() calls - O(m log m + m) instead of O(m²)
        $list->addAll($values);
        return $list;
    }

    /**
     * Creates a SortedList from any iterable (array, generator, iterator, etc.).
     *
     * The list type (int or string) is automatically determined from the first value.
     * Throws EmptyIterableParameter if the iterable is empty.
     *
     * Time complexity: O(m log m + m) - Optimized: converts to array then uses addAll()
     * Space complexity: O(m) for the new list and temporary array
     *
     * Optimizations:
     * - Converts iterable to array, then uses addAll() instead of individual add() calls
     * - O(m log m + m) instead of O(m²) when using add() for each element
     *
     * @param iterable<int|string> $values The iterable to create the list from
     * @param SortDirection $sortDirection The sort direction (default: ASC)
     * @return self A new sorted list instance
     * @throws EmptyIterableParameter If the iterable is empty
     */
    public static function fromIterable(iterable $values, SortDirection $sortDirection = SortDirection::ASC): self
    {
        // Convert iterable to array first (needed for type detection and addAll)
        $valuesArray = [];
        $firstValue = null;
        foreach ($values as $value) {
            /** @var int|string $value */
            if ($firstValue === null) {
                $firstValue = $value;
            }
            $valuesArray[] = $value;
        }

        if ($firstValue === null) {
            throw EmptyIterableParameter::create();
        }

        /** @var int|string $firstValue */
        $type = \is_int($firstValue) ? ListType::INT : ListType::STRING;
        $list = new self($type, $sortDirection);
        // Use addAll() instead of individual add() calls - O(m log m + m) instead of O(m²)
        $list->addAll($valuesArray);
        return $list;
    }

    public function getSortOrder(): SortDirection
    {
        return $this->sortDirection;
    }

    public function getType(): ListType
    {
        return $this->type;
    }

    /**
     * if $a >= $b, returns -1, if $a == $b, returns 0, if $a < $b, returns 1
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
            ? $cmp <= 0   // if ascending $a must be <= $b, in order to come before
            : $cmp >= 0;  // if descending $a must be >= $b, in order to come before
    }

    /**
     * Merge two sorted linked lists (already validated to share type).
     */
    private function mergeNodes(?ListNode $a, ?ListNode $b): ?ListNode
    {
        if ($a === null) {
            $head = $b;
            $prev = null;
            $tail = null;
            while ($head !== null) {
                $head->prev = $prev;
                $tail = $head;
                $prev = $head;
                $head = $head->next;
            }
            $this->tail = $tail;
            return $b;
        }
        if ($b === null) {
            $head = $a;
            $prev = null;
            $tail = null;
            while ($head !== null) {
                $head->prev = $prev;
                $tail = $head;
                $prev = $head;
                $head = $head->next;
            }
            $this->tail = $tail;
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

        $head->prev = null;
        $tail = $head;

        // Merge remainder (fix prev pointers as we attach)
        while ($a !== null || $b !== null) {
            if ($b === null || ($a !== null && $this->shouldComeBefore($a->value, $b->value))) {
                $tail->next = $a;
                /** @var ListNode $a */
                $a->prev = $tail;
                $tail = $a;
                $a = $a->next;
            } else {
                $tail->next = $b;
                /** @var ListNode $b */
                $b->prev = $tail;
                $tail = $b;
                $b = $b->next;
            }
        }

        $tail->next = null;
        $this->tail = $tail;
        return $head;
    }

    /**
     * Merge a sorted array with a sorted linked list.
     * Returns the head of the merged list.
     * @param array<int, int|string> $sortedArray
     */
    private function mergeArrayWithList(array $sortedArray, ?ListNode $listHead): ?ListNode
    {
        if (empty($sortedArray)) {
            return $listHead;
        }
        if ($listHead === null) {
            return $this->arrayToLinkedList($sortedArray);
        }

        $arrayIndex = 0;
        $arrayCount = count($sortedArray);

        // Initialize head
        /** @var int|string $firstValue */
        $firstValue = $sortedArray[$arrayIndex];
        if ($this->shouldComeBefore($firstValue, $listHead->value)) {
            $head = new ListNode($firstValue);
            $arrayIndex++;
        } else {
            $head = $listHead;
            $listHead = $listHead->next;
        }

        $head->prev = null;
        $tail = $head;

        // Merge remainder (fix prev pointers as we attach)
        while ($arrayIndex < $arrayCount || $listHead !== null) {
            if ($listHead === null) {
                /** @var int|string $arrayValue */
                $arrayValue = $sortedArray[$arrayIndex];
                $node = new ListNode($arrayValue);
                $arrayIndex++;
            } elseif ($arrayIndex >= $arrayCount) {
                $node = $listHead;
                $listHead = $listHead->next;
            } else {
                /** @var int|string $arrayValue */
                $arrayValue = $sortedArray[$arrayIndex];
                if ($this->shouldComeBefore($arrayValue, $listHead->value)) {
                    $node = new ListNode($arrayValue);
                    $arrayIndex++;
                } else {
                    $node = $listHead;
                    $listHead = $listHead->next;
                }
            }

            $tail->next = $node;
            $node->prev = $tail;
            $tail = $node;
        }

        $tail->next = null;
        $this->tail = $tail;
        return $head;
    }

    /**
     * Rebuild linked list from a sorted array.
     * Assumes array is already sorted according to this list's sort direction.
     * @param array<int, int|string> $sortedValues
     */
    private function rebuildFromSortedArray(array $sortedValues): void
    {
        $this->head = null;
        $this->tail = null;
        $this->count = 0;
        $this->invalidateInsertPointCache(); // Invalidate cache when rebuilding

        if (empty($sortedValues)) {
            return;
        }

        // Build linked list directly from sorted array (O(k))
        /** @var int|string $firstValue */
        $firstValue = $sortedValues[0];
        $this->head = new ListNode($firstValue);
        $this->head->prev = null;
        $current = $this->head;
        $this->tail = $this->head;
        $this->count = 1;

        for ($i = 1; $i < count($sortedValues); $i++) {
            /** @var int|string $value */
            $value = $sortedValues[$i];
            $newNode = new ListNode($value);
            $newNode->prev = $current;
            $current->next = $newNode;
            $current = $newNode;
            $this->tail = $newNode;
            $this->count++;
        }
    }

    /**
     * Convert a sorted array to a linked list.
     * @param array<int, int|string> $sortedArray
     */
    private function arrayToLinkedList(array $sortedArray): ?ListNode
    {
        if (empty($sortedArray)) {
            $this->tail = null;
            return null;
        }

        /** @var int|string $firstValue */
        $firstValue = $sortedArray[0];
        $head = new ListNode($firstValue);
        $head->prev = null;
        $current = $head;
        $this->tail = $head;

        for ($i = 1; $i < count($sortedArray); $i++) {
            /** @var int|string $value */
            $value = $sortedArray[$i];
            $newNode = new ListNode($value);
            $newNode->prev = $current;
            $current->next = $newNode;
            $current = $newNode;
            $this->tail = $newNode;
        }

        return $head;
    }
}
