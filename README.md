# Sorted Linked List Library

A PHP 8.4 library implementing a sorted singly linked list data structure with comprehensive operations for integers and strings.

## Features

- **Type-safe**: Supports integers and strings with strict type checking
- **Flexible sorting**: Ascending or descending order
- **Comprehensive API**: 50+ methods for list manipulation
- **Efficient operations**: In-place merge and reverse with O(1) space complexity
- **Iterable**: Implements `IteratorAggregate` for `foreach` support
- **JSON serializable**: Built-in JSON encoding support
- **Well-tested**: 302 unit tests with 1,251 assertions

## Requirements

- PHP 8.4 or higher

## Installation

### Using Composer with VCS Repository

Add the repository to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/yourusername/sorted-linked-list-library"
        }
    ],
    "require": {
        "david/sorted-linked-list-library": "dev-main"
    }
}
```

Then run:

```bash
composer install
```

Or if you already have a `composer.json`:

```bash
composer require david/sorted-linked-list-library:dev-main
```

### Alternative: Local Path Repository

For local development, you can use a path repository:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../SortedLinkedListLibrary"
        }
    ],
    "require": {
        "david/sorted-linked-list-library": "*"
    }
}
```

## Quick Start

### Creating Lists

```php
use SortedLinkedListLibrary\SortedList;
use SortedLinkedListLibrary\Enums\SortDirection;

// Create a list for integers (ascending by default)
$intList = SortedList::forInts();
$intList->add(5)->add(2)->add(8)->add(1)->add(3);
// Result: [1, 2, 3, 5, 8]

// Create a list for strings (descending)
$stringList = SortedList::forStrings(SortDirection::DESC);
$stringList->add('zebra')->add('apple')->add('banana');
// Result: ['zebra', 'banana', 'apple']

// Create from array
$list = SortedList::fromArray([5, 2, 8, 1, 3]);
// Result: [1, 2, 3, 5, 8]

// Create from iterable (generators, iterators, etc.)
$list = SortedList::fromIterable($someIterable);
```

### Basic Operations

```php
$list = SortedList::forInts();

// Add values (automatically sorted)
$list->add(5)->add(2)->add(8);
// List: [2, 5, 8]

// Remove values
$list->remove(5);
// List: [2, 8]

// Check if value exists
if ($list->contains(8)) {
    echo "Found!";
}

// Get first and last
$first = $list->first();  // 2
$last = $list->last();     // 8

// Safe access (returns null if empty)
$first = $list->firstOrNull();  // 2 or null
$last = $list->lastOrNull();    // 8 or null

// Get by index
$value = $list->getAt(0);    // 2
$value = $list->getAtOrNull(10);  // null (safe access)

// Iterate
foreach ($list as $value) {
    echo $value . "\n";
}

// Convert to array
$array = $list->toArray();
```

### Bulk Operations

```php
$list = SortedList::forInts();

// Add multiple values
$list->addAll([5, 2, 8, 1, 3]);
// List: [1, 2, 3, 5, 8]

// Remove multiple values (returns count removed)
$removed = $list->removeAll([2, 5]);
// Returns: 2
// List: [1, 3, 8]

// Remove all occurrences of multiple values
$removed = $list->removeAllAndEveryOccurrence([1, 1, 3]);
// Returns: 2 (removes all occurrences of 1 and 3)
// List: [8]

// Remove every occurrence of a value
$removed = $list->removeEveryOccurrence(2);
// Returns: count of removed items

// Clear the list
$list->clear();
// List: []
```

### Search and Filtering

```php
$list = SortedList::forInts();
$list->addAll([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

// Find first value matching predicate
$even = $list->find(fn($value) => $value % 2 === 0);
// Returns: 2

// Find all values matching predicate
$evens = $list->findAll(fn($value) => $value % 2 === 0);
// Returns: new SortedList([2, 4, 6, 8, 10])

// Filter in-place
$list->filter(fn($value) => $value > 5);
// List: [6, 7, 8, 9, 10]

// Find index of value
$index = $list->indexOf(8);
// Returns: 2 (or null if not found)
```

### Range Queries

```php
$list = SortedList::forInts();
$list->addAll([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

// Get slice
$slice = $list->slice(2, 3);
// Returns: [3, 4, 5]

// Get range (inclusive)
$range = $list->range(3, 7);
// Returns: [3, 4, 5, 6, 7]

// Get values greater than
$greater = $list->valuesGreaterThan(5);
// Returns: [6, 7, 8, 9, 10]

// Get values less than
$less = $list->valuesLessThan(5);
// Returns: [1, 2, 3, 4]
```

### Set Operations

```php
$list1 = SortedList::fromArray([1, 2, 3, 4, 5]);
$list2 = SortedList::fromArray([4, 5, 6, 7, 8]);

// Union (unique values from both)
$union = $list1->union($list2);
// Returns: [1, 2, 3, 4, 5, 6, 7, 8]

// Union with duplicates preserved - O(n+m)
$unionWithDups = $list1->unionWithDuplicates($list2);
// Returns: [1, 2, 3, 4, 4, 5, 5, 6, 7, 8] (preserves all duplicates)

// Intersection (common values)
$intersect = $list1->intersect($list2);
// Returns: [4, 5]

// Difference (values in list1 but not in list2)
$diff = $list1->diff($list2);
// Returns: [1, 2, 3]

// Remove duplicates
$list = SortedList::fromArray([1, 2, 2, 3, 3, 3]);
$list->unique();
// List: [1, 2, 3]
```

### Merge and Reverse

```php
$list1 = SortedList::fromArray([1, 3, 5]);
$list2 = SortedList::fromArray([2, 4, 6]);

// Merge list2 into list1 (in-place)
$list1->merge($list2);
// list1: [1, 2, 3, 4, 5, 6]
// list2: [] (detached)

// Reverse the list (in-place, flips sort direction)
$list = SortedList::fromArray([1, 2, 3, 4, 5]);
$list->reverse();
// List: [5, 4, 3, 2, 1] (now descending)
```

### Utility Methods

```php
$list = SortedList::fromArray([5, 2, 8, 1, 3]);

// Get min/max
$min = $list->min();  // 1
$max = $list->max();  // 8

// Sum (for numeric types)
$sum = $list->sum();  // 19

// Copy
$copy = $list->copy();

// Compare lists
$equals = $list->equals($copy);  // true

// Get metadata
$type = $list->getType();              // ListType::INT
$direction = $list->getSortOrder();    // SortDirection::ASC
$count = $list->count();                // 5
$isEmpty = $list->isEmpty();            // false
```

### Advanced Operations

```php
$list = SortedList::fromArray([1, 2, 3, 4, 5]);

// Remove at index
$removed = $list->removeAt(2);
// Returns: 3
// List: [1, 2, 4, 5]

// Remove first N values
$firstTwo = $list->removeFirst(2);
// Returns: [1, 2]
// List: [4, 5]

// Remove last N values
$lastTwo = $list->removeLast(2);
// Returns: [4, 5]
// List: []
```

### JSON Serialization

```php
$list = SortedList::fromArray([1, 2, 3]);

// Serialize to JSON
$json = $list->toJson();
// {"type":"int","ascending":true,"count":3,"values":[1,2,3]}

// Or use json_encode (implements JsonSerializable)
$json = json_encode($list);
```

## API Reference

### Factory Methods

- `SortedList::forInts(SortDirection $sortDirection = SortDirection::ASC): self`
- `SortedList::forStrings(SortDirection $sortDirection = SortDirection::ASC): self`
- `SortedList::fromArray(array $values, SortDirection $sortDirection = SortDirection::ASC): self`
- `SortedList::fromIterable(iterable $values, SortDirection $sortDirection = SortDirection::ASC): self`

### Core Operations

- `add(int|string $value): self` - Add value (maintains sort order)
- `remove(int|string $value): bool` - Remove first occurrence
- `removeEveryOccurrence(int|string $value): int` - Remove all occurrences (returns count)
- `contains(int|string $value): bool` - Check if value exists
- `getAt(int $index): int|string` - Get value by index
- `getAtOrNull(int $index): int|string|null` - Safe get by index
- `first(): int|string` - Get first value
- `firstOrNull(): int|string|null` - Safe get first value (returns null if empty)
- `last(): int|string` - Get last value
- `lastOrNull(): int|string|null` - Safe get last value (returns null if empty)
- `isEmpty(): bool` - Check if list is empty
- `count(): int` - Get element count

### Bulk Operations

- `addAll(iterable $values): self` - Add multiple values
- `removeAll(iterable $values): int` - Remove multiple values (returns count)
- `removeAllAndEveryOccurrence(iterable $values): int` - Remove all occurrences of multiple values (returns count)
- `clear(): self` - Clear all elements

### Search and Filtering

- `find(callable $predicate): int|string|null` - Find first matching value
- `findAll(callable $predicate): self` - Find all matching values (returns new list)
- `filter(callable $predicate): self` - Filter in-place
- `indexOf(int|string $value): int|null` - Get index of value

### Range Queries

- `slice(int $offset, ?int $length = null): self` - Get slice (returns new list)
- `range(int|string $from, int|string $to): self` - Get range (returns new list)
- `valuesGreaterThan(int|string $value): self` - Get values > value (returns new list)
- `valuesLessThan(int|string $value): self` - Get values < value (returns new list)

### Set Operations

- `union(SortedListInterface $other): self` - Union with unique values (returns new list, O(n+m))
- `unionWithDuplicates(SortedListInterface $other): self` - Union preserving all duplicates (returns new list, O(n+m))
- `intersect(SortedListInterface $other): self` - Intersection (returns new list)
- `diff(SortedListInterface $other): self` - Difference (returns new list)
- `unique(): self` - Remove duplicates (in-place)

### List Manipulation

- `merge(SortedListInterface $other): self` - Merge another list (in-place)
- `reverse(): self` - Reverse list and flip sort direction (in-place)
- `copy(): self` - Create shallow copy

### Utility Methods

- `equals(SortedListInterface $other): bool` - Compare lists
- `min(): int|string|null` - Get minimum value
- `max(): int|string|null` - Get maximum value
- `sum(): int|float` - Sum all values (numeric types)

### Advanced Operations

- `removeAt(int $index): int|string` - Remove and return value at index
- `removeFirst(int $count = 1): array` - Remove and return first N values
- `removeLast(int $count = 1): array` - Remove and return last N values

### Query Methods

- `getType(): ListType` - Get list type (INT or STRING)
- `getSortOrder(): SortDirection` - Get sort direction (ASC or DESC)

### Conversion and Serialization

- `toArray(): array` - Convert to array
- `toJson(int $options = 0, int $depth = 512): string|false` - Convert to JSON string
- `jsonSerialize(): array` - For `json_encode()` support (implements JsonSerializable)
- `getIterator(): \Traversable` - For `foreach` support (implements IteratorAggregate)

## Exceptions

The library uses custom exception classes for better error handling:

- `EmptyListException` - Thrown when accessing empty list (first/last)
- `IndexOutOfRangeException` - Thrown when index is out of range
- `DifferentListTypesException` - Thrown when merging lists of different types
- `DifferentSortDirectionsException` - Thrown when merging lists with different sort directions
- `InvalidTypeException` - Thrown when adding wrong type to list
- `EmptyIterableParameter` - Thrown when creating list from empty iterable

## Testing

Run the test suite:

```bash
composer test
```

Or using Docker:

```bash
docker-compose run --rm php vendor/bin/phpunit
```

## Development

### Code Quality

```bash
# Run PHPStan static analysis
composer analyse

# Run Easy Coding Standard
composer ecs

# Fix coding standard issues
composer ecs-fix
```

### Project Structure

```
src/
├── Enums/
│   ├── ListType.php          # INT, STRING enum
│   └── SortDirection.php      # ASC, DESC enum
├── Exceptions/
│   ├── DifferentListTypesException.php
│   ├── EmptyIterableParameter.php
│   ├── EmptyListException.php
│   ├── IndexOutOfRangeException.php
│   └── InvalidTypeException.php
├── ListNode.php               # Internal node class
├── SortedList.php             # Main implementation
└── SortedListInterface.php    # Public interface

tests/
└── SortedLinkedListLibrary/
    ├── AdvancedFeaturesTest.php
    ├── BasicOperationsTest.php
    ├── BulkOperationsTest.php
    ├── FactoryMethodsTest.php
    ├── MergeOperationsTest.php
    ├── QueryMethodsTest.php
    ├── RangeQueriesTest.php
    ├── ReverseOperationsTest.php
    ├── SearchAndFilteringTest.php
    ├── SetOperationsTest.php
    └── UtilityMethodsTest.php
```

## Performance Characteristics

### Core Operations

- **Add**: O(n) worst case, O(1) best case (sequential inserts)
- **Remove**: O(n) - Linear search to find value, then O(1) removal
- **Contains**: O(n) worst case - Linear search with early termination (can exit early when value is out of range)
- **Get by index**: O(n) - Linear traversal to reach index
- **first()**: O(1) - Direct access to head node
- **last()**: O(n) - Must traverse to end of list
- **isEmpty()**: O(1) - Simple null check
- **count()**: O(1) - Stored count value

### Bulk Operations

- **addAll()**: O(m log m + n + m) - Where m is number of items to add, n is current list size. Sorts new values then merges with existing list.
- **removeAll()**: O(m log m + n + m) - Sorts values to remove then does single-pass merge-like removal
- **removeAllAndEveryOccurrence()**: O(m log m + n + m) - Sorts values to remove then does single-pass merge-like removal of all occurrences
- **removeFirst()**: O(count) - Direct pointer manipulation
- **removeLast()**: O(n) - Single pass using two-pointer technique to find Nth-to-last element
- **removeAt()**: O(n) - Single traversal to find and remove
- **clear()**: O(1) - Simply sets head to null

### List Manipulation

- **Merge**: O(n+m) time, O(1) space - In-place merge of two sorted lists by reusing nodes
- **Reverse**: O(n) time, O(1) space - In-place reversal by manipulating pointers
- **copy()**: O(n) time, O(n) space - Directly copies nodes

### Search and Filtering

- **find()**: O(n) - Linear search until predicate matches
- **findAll()**: O(n) time, O(k) space - Collects matching values then builds list in O(k) where k is number of matches
- **filter()**: O(n) - Collects values to keep then rebuilds list in O(k) where k is kept items
- **indexOf()**: O(n) - Linear search for value with early termination

### Range Queries

- **slice()**: O(n) time, O(k) space - Collects values then builds list in O(k) where k is slice length
- **range()**: O(n) time, O(k) space - Collects matching values with early termination, then builds list in O(k) where k is number of values in range
- **valuesGreaterThan()**: O(n) time, O(k) space - Collects matching values then builds list directly
- **valuesLessThan()**: O(n) time, O(k) space - Collects matching values then builds list directly

### Set Operations

- **union()**: O(n+m) time, O(n+m) space - Merge + deduplication in single pass
- **unionWithDuplicates()**: O(n+m) time, O(n+m) space - Merge preserving all duplicates
- **intersect()**: O(n+m) time, O(min(n,m)) space - Merge-like approach since both lists are sorted
- **diff()**: O(n+m) time, O(n) space - Merge-like approach since both lists are sorted
- **unique()**: O(n) - Collects unique values then rebuilds list in O(k) where k is unique items

### Factory Methods

- **fromArray()**: O(m log m + m) - Uses addAll() which sorts then merges
- **fromIterable()**: O(m log m + m) - Converts to array then uses addAll()

### Conversion

- **toArray()**: O(n) time, O(n) space - Linear traversal to build array
- **toJson()**: O(n) time, O(n) space - Calls toArray() then json_encode()

### Notes

- All operations maintain the sorted order of the list
- Sequential inserts (adding values in sorted order) are O(1) each
- Early termination is used in `contains()`, `valuesGreaterThan()`, and `valuesLessThan()` when possible
- Space complexity for operations returning new lists is proportional to result size
- Worst-case complexities assume no early termination benefits
- Operations that modify the list in-place (like `merge()`, `reverse()`) have O(1) space complexity
- Operations that return new lists have space complexity proportional to the result size

## Performance Benchmarks

The following benchmarks were run on a system with realistic dataset sizes (10K-50K elements). All tests use parallel execution where applicable.

### Test Execution Times

```
testIntersectPerformanceWithLargeLists                   1.04s
testAddPerformanceWithLargeDataset                       1.02s
testLinearComplexityForMerge                             1.02s
testRapidAddRemoveSequence                               0.95s
testUnionPerformanceWithLargeLists                       0.94s
testRealWorldUserIdsScenario                             0.94s
testRealWorldLogProcessingScenario                       0.93s
testMultipleReverseOperations                            0.93s
testMergePerformanceWithLargeLists                       0.90s
testRealWorldInventoryScenario                           0.88s
testAddPerformanceWithSortedData                         0.87s
testMemoryUsageWithLargeList                             0.86s
testWithVeryLargeIntegers                                0.85s
testAddAllPerformanceWithLargeDataset                    0.84s
testLinearComplexityForContains                          0.84s
testMemoryUsageWithStrings                               0.84s
testFilterPerformanceWithLargeList                       0.83s
testWithManyDuplicateValues                              0.82s
testRepeatedAddRemoveOperations                          0.80s
testLinearComplexityForAdd                               0.80s
testRemoveAllPerformanceWithLargeList                    0.80s
testAddPerformanceWithReverseSortedData                  0.80s
testUniquePerformanceWithLargeList                       0.80s
testAddAllPerformanceWithLargeDataset                    0.79s
testWithVeryLongStrings                                  0.78s
```

### Benchmark Details

- **Dataset Sizes**: 
  - Large: 10,000 elements
  - Very Large: 50,000 elements
- **Test Environment**: Docker container with PHP 8.4.15
- **Parallel Execution**: Tests run with Paratest using 20 processes
- **Total Test Suite Time**: ~69 seconds for all 24 performance tests

### Key Observations

1. **Most operations complete in under 1 second** even with 10K-50K elements
2. **Sequential inserts**: `testLinearComplexityForAdd` completes in 0.80s
3. **Union/Merge operations**: Complete in ~1 second for 50K element lists
4. **Bulk operations**: All complete efficiently
5. **Real-world scenarios** (user IDs, log processing, inventory) all complete efficiently

## Error Handling

The library provides descriptive error messages to help with debugging:

### Exception Types

- **`EmptyListException`**: Thrown when trying to access elements from an empty list
  - Operations: `first()`, `last()`, `getAt()` (when list is empty)
  - Example: "Cannot perform first() on an empty list. The list must contain at least one element."

- **`IndexOutOfRangeException`**: Thrown when accessing an invalid index
  - Operations: `getAt()`, `removeAt()`
  - Example: "Index 5 is out of range. The list has 3 element(s). Valid indices are 0 to 2."

- **`InvalidTypeException`**: Thrown when adding a value of the wrong type
  - Operations: `add()`, `addAll()`
  - Example: "Invalid type: expected int, got string. This list only accepts integer values. Value provided: 'hello'."

- **`DifferentListTypesException`**: Thrown when performing operations on lists with different types
  - Operations: `merge()`, `union()`, `intersect()`, `diff()`
  - Example: "Cannot perform merge() on lists with different types. First list type: int, second list type: string. Both lists must have the same type (either int or string)."

- **`EmptyIterableParameter`**: Thrown when creating a list from an empty iterable
  - Operations: `fromIterable()`
  - Example: "Cannot create list from empty iterable in fromIterable(). The iterable must contain at least one element to determine the list type (int or string)."

### Error Message Features

All error messages include:
- The operation that failed
- Context about what went wrong
- Helpful suggestions for fixing the issue
- Relevant values (indices, types, etc.)

## Edge Cases

The library handles various edge cases gracefully:

### Empty Lists
- All methods handle empty lists appropriately
- `firstOrNull()`, `lastOrNull()`, `getAtOrNull()` return `null` for empty lists
- `isEmpty()` returns `true` for empty lists
- Operations like `merge()`, `union()` work correctly with empty lists

### Single Element Lists
- All operations work correctly with single-element lists
- Removing the only element results in an empty list
- Reversing a single-element list is a no-op

### Duplicate Values
- The list preserves duplicate values in sorted order
- `remove()` removes only the first occurrence
- `removeEveryOccurrence()` removes all occurrences
- `unique()` removes all duplicates

### Boundary Conditions
- Negative indices are handled (return `null` for `getAtOrNull()`, throw exception for `getAt()`)
- Indices beyond list size are handled appropriately
- Empty strings and zero values are handled correctly

### Large Lists
- The library can handle large lists efficiently
- Memory usage is proportional to list size (O(n))
- Operations maintain their complexity guarantees even with large lists

### Type Safety
- Type checking is enforced at runtime
- Mixing int and string values is prevented
- Clear error messages when type violations occur

## License

This library is open-source software. See the license file for details.

## Author

**davidkosvica** - david.kosvica@gmail.com

