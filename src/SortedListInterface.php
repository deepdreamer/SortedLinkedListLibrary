<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use SortedLinkedListLibrary\Enums\ListType;
use SortedLinkedListLibrary\Enums\SortDirection;
use SortedLinkedListLibrary\Exceptions\DifferentListTypesException;
use SortedLinkedListLibrary\Exceptions\DifferentSortDirectionsException;
use SortedLinkedListLibrary\Exceptions\EmptyListException;
use SortedLinkedListLibrary\Exceptions\IndexOutOfRangeException;
use SortedLinkedListLibrary\Exceptions\InvalidTypeException;

/**
 * Interface for a sorted linked list data structure.
 *
 * This interface defines the contract for sorted list implementations.
 * All methods maintain the sorted order according to the list's sort direction.
 * The list can contain either integers or strings, but not both in the same instance.
 *
 * @extends \IteratorAggregate<int, int|string>
 */
interface SortedListInterface extends \IteratorAggregate, \Countable, \JsonSerializable
{
    // ============================================================================
    // Core Operations
    // ============================================================================

    /**
     * Add a value to the list, maintaining sorted order.
     *
     * @param int|string $value The value to add
     * @return self Returns $this for method chaining
     * @throws InvalidTypeException If the value type doesn't match the list type
     */
    public function add(int|string $value): self;

    /**
     * Remove the first occurrence of a value from the list.
     *
     * @param int|string $value The value to remove
     * @return bool True if the value was found and removed, false otherwise
     */
    public function remove(int|string $value): bool;

    /**
     * Remove all occurrences of a value from the list.
     *
     * @param int|string $value The value to remove
     * @return int The number of occurrences removed
     */
    public function removeEveryOccurrence(int|string $value): int;

    /**
     * Check if the list contains a value.
     *
     * @param int|string $value The value to search for
     * @return bool True if the value is found, false otherwise
     */
    public function contains(int|string $value): bool;

    /**
     * Check if the list is empty.
     *
     * @return bool True if the list has no elements, false otherwise
     */
    public function isEmpty(): bool;

    // ============================================================================
    // Bulk Operations
    // ============================================================================

    /**
     * Add all values from an iterable to the list.
     *
     * @param iterable<int|string> $values The values to add
     * @return self Returns $this for method chaining
     * @throws InvalidTypeException If any value type doesn't match the list type
     */
    public function addAll(iterable $values): self;

    /**
     * Remove the first occurrence of each value from the iterable.
     *
     * @param iterable<int|string> $values The values to remove
     * @return int The number of values successfully removed
     */
    public function removeAll(iterable $values): int;

    /**
     * Remove all occurrences of each value from the iterable.
     *
     * @param iterable<int|string> $values The values to remove
     * @return int The total number of occurrences removed
     */
    public function removeAllAndEveryOccurrence(iterable $values): int;

    /**
     * Remove all elements from the list.
     *
     * @return self Returns $this for method chaining
     */
    public function clear(): self;

    // ============================================================================
    // Accessors
    // ============================================================================

    /**
     * Get the first element in the list.
     *
     * @return int|string The first element
     * @throws EmptyListException If the list is empty
     */
    public function first(): int|string;

    /**
     * Get the first element in the list, or null if empty.
     *
     * @return int|string|null The first element, or null if the list is empty
     */
    public function firstOrNull(): int|string|null;

    /**
     * Get the last element in the list.
     *
     * @return int|string The last element
     * @throws EmptyListException If the list is empty
     */
    public function last(): int|string;

    /**
     * Get the last element in the list, or null if empty.
     *
     * @return int|string|null The last element, or null if the list is empty
     */
    public function lastOrNull(): int|string|null;

    /**
     * Get the element at the specified index.
     *
     * @param int $index The zero-based index
     * @return int|string The element at the index
     * @throws IndexOutOfRangeException If the index is out of range
     */
    public function getAt(int $index): int|string;

    /**
     * Get the element at the specified index, or null if out of range.
     *
     * @param int $index The zero-based index
     * @return int|string|null The element at the index, or null if out of range
     */
    public function getAtOrNull(int $index): int|string|null;

    /**
     * Find the index of the first occurrence of a value.
     *
     * @param int|string $value The value to search for
     * @return int|null The zero-based index, or null if not found
     */
    public function indexOf(int|string $value): int|null;

    // ============================================================================
    // Search and Filtering
    // ============================================================================

    /**
     * Find the first element that matches the predicate.
     *
     * @param callable(int|string): bool $predicate The predicate function
     * @return int|string|null The first matching element, or null if not found
     */
    public function find(callable $predicate): int|string|null;

    /**
     * Find all elements that match the predicate.
     *
     * @param callable(int|string): bool $predicate The predicate function
     * @return self A new list containing all matching elements
     */
    public function findAll(callable $predicate): self;

    /**
     * Filter the list to only include elements that match the predicate.
     *
     * @param callable(int|string): bool $predicate The predicate function
     * @return self A new list containing only matching elements
     */
    public function filter(callable $predicate): self;

    // ============================================================================
    // Range Queries
    // ============================================================================

    /**
     * Get a slice of the list.
     *
     * @param int $offset The starting index (can be negative)
     * @param int|null $length The number of elements to include (null for all remaining)
     * @return self A new list containing the slice
     */
    public function slice(int $offset, ?int $length = null): self;

    /**
     * Get all elements in the range [from, to] (inclusive).
     *
     * @param int|string $from The lower bound (inclusive)
     * @param int|string $to The upper bound (inclusive)
     * @return self A new list containing elements in the range
     */
    public function range(int|string $from, int|string $to): self;

    /**
     * Get all elements greater than the specified value.
     *
     * @param int|string $value The threshold value
     * @return self A new list containing elements greater than the value
     */
    public function valuesGreaterThan(int|string $value): self;

    /**
     * Get all elements less than the specified value.
     *
     * @param int|string $value The threshold value
     * @return self A new list containing elements less than the value
     */
    public function valuesLessThan(int|string $value): self;

    // ============================================================================
    // Set Operations
    // ============================================================================

    /**
     * Create a union of this list and another (no duplicates).
     *
     * @param SortedListInterface $other The other list to union with
     * @return self A new list containing unique elements from both lists
     * @throws DifferentListTypesException If the lists have different types
     */
    public function union(SortedListInterface $other): self;

    /**
     * Create a union of this list and another (with duplicates).
     *
     * @param SortedListInterface $other The other list to union with
     * @return self A new list containing all elements from both lists
     * @throws DifferentListTypesException If the lists have different types
     */
    public function unionWithDuplicates(SortedListInterface $other): self;

    /**
     * Create an intersection of this list and another.
     *
     * @param SortedListInterface $other The other list to intersect with
     * @return self A new list containing elements present in both lists
     * @throws DifferentListTypesException If the lists have different types
     */
    public function intersect(SortedListInterface $other): self;

    /**
     * Create a difference of this list and another (elements in this but not in other).
     *
     * @param SortedListInterface $other The other list to diff with
     * @return self A new list containing elements in this list but not in the other
     * @throws DifferentListTypesException If the lists have different types
     */
    public function diff(SortedListInterface $other): self;

    /**
     * Remove duplicate values, keeping only unique elements.
     *
     * @return self A new list containing only unique elements
     */
    public function unique(): self;

    // ============================================================================
    // List Manipulation
    // ============================================================================

    /**
     * Merge another list into this list (modifies this list).
     *
     * @param SortedListInterface $other The other list to merge
     * @return self Returns $this for method chaining
     * @throws DifferentListTypesException If the lists have different types
     * @throws DifferentSortDirectionsException If the lists have different sort directions
     */
    public function merge(SortedListInterface $other): self;

    /**
     * Reverse the order of elements in the list.
     *
     * @return self Returns $this for method chaining
     */
    public function reverse(): self;

    /**
     * Create a shallow copy of the list.
     *
     * @return self A new list with the same elements
     */
    public function copy(): self;

    /**
     * Check if this list equals another list (same elements in same order).
     *
     * @param SortedListInterface $other The other list to compare
     * @return bool True if the lists are equal, false otherwise
     */
    public function equals(SortedListInterface $other): bool;

    // ============================================================================
    // Removal by Index
    // ============================================================================

    /**
     * Remove and return the element at the specified index.
     *
     * @param int $index The zero-based index
     * @return int|string The removed element
     * @throws IndexOutOfRangeException If the index is out of range
     */
    public function removeAt(int $index): int|string;

    /**
     * Remove and return the first N elements from the list.
     *
     * @param int $count The number of elements to remove (default: 1)
     * @return array<int, int|string> An array of the removed elements
     */
    public function removeFirst(int $count = 1): array;

    /**
     * Remove and return the last N elements from the list.
     *
     * @param int $count The number of elements to remove (default: 1)
     * @return array<int, int|string> An array of the removed elements
     */
    public function removeLast(int $count = 1): array;

    // ============================================================================
    // Aggregation
    // ============================================================================

    /**
     * Get the minimum value in the list.
     *
     * @return int|string|null The minimum value, or null if the list is empty
     */
    public function min(): int|string|null;

    /**
     * Get the maximum value in the list.
     *
     * @return int|string|null The maximum value, or null if the list is empty
     */
    public function max(): int|string|null;

    /**
     * Calculate the sum of all values in the list.
     *
     * @return int|float The sum of all values
     */
    public function sum(): int|float;

    // ============================================================================
    // Conversion
    // ============================================================================

    /**
     * Convert the list to an array.
     *
     * @return array<int, int|string> An array containing all elements in order
     */
    public function toArray(): array;

    /**
     * Convert the list to a JSON string.
     *
     * @param int $options JSON encoding options (see json_encode)
     * @param int $depth Maximum nesting depth
     * @return string|false The JSON string, or false on failure
     */
    public function toJson(int $options = 0, int $depth = 512): string|false;

    // ============================================================================
    // Metadata
    // ============================================================================

    /**
     * Get the sort direction of the list.
     *
     * @return SortDirection The sort direction (ASC or DESC)
     */
    public function getSortOrder(): SortDirection;

    /**
     * Get the type of values this list can contain.
     *
     * @return ListType The list type (INT or STRING)
     */
    public function getType(): ListType;
}
