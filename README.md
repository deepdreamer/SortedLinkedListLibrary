# Sorted Linked List Library

A PHP 8.4 library implementing a sorted singly linked list data structure with comprehensive operations for integers and strings.

## Features

- **Type-safe**: Supports integers and strings with strict type checking
- **Flexible sorting**: Ascending or descending order
- **Comprehensive API**: 40+ methods for list manipulation
- **Efficient operations**: In-place merge and reverse with O(1) space complexity
- **Iterable**: Implements `IteratorAggregate` for `foreach` support
- **JSON serializable**: Built-in JSON encoding support
- **Well-tested**: 200+ unit tests with 377+ assertions

## Requirements

- PHP 8.4 or higher

## Installation

```bash
composer require david/sorted-linked-list-library
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

// Get by index
$value = $list->get(0);    // 2
$value = $list->getOrNull(10);  // null (safe access)

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

// Merge list2 into list1 (in-place, O(n+m) time, O(1) space)
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
- `contains(int|string $value): bool` - Check if value exists
- `get(int $index): int|string` - Get value by index
- `getOrNull(int $index): int|string|null` - Safe get by index
- `first(): int|string` - Get first value
- `last(): int|string` - Get last value
- `isEmpty(): bool` - Check if list is empty
- `count(): int` - Get element count

### Bulk Operations

- `addAll(iterable $values): self` - Add multiple values
- `removeAll(iterable $values): int` - Remove multiple values (returns count)
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

- `union(SortedListInterface $other): self` - Union (returns new list)
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

### Standard Interfaces

- `toArray(): array` - Convert to array
- `toJson(int $options = 0, int $depth = 512): string|false` - Convert to JSON
- `jsonSerialize(): array` - For `json_encode()` support
- `getIterator(): \Traversable` - For `foreach` support

## Exceptions

The library uses custom exception classes for better error handling:

- `EmptyListException` - Thrown when accessing empty list (first/last)
- `IndexOutOfRangeException` - Thrown when index is out of range
- `DifferentListTypesException` - Thrown when merging lists of different types
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

- **Add**: O(n) - Linear search for insertion point to maintain sort order
- **Remove**: O(n) - Linear search to find value, then O(1) removal
- **Contains**: O(n) worst case - Linear search with early termination optimization (can exit early when value is out of range)
- **Get by index**: O(n) - Linear traversal to reach index
- **first()**: O(1) - Direct access to head node
- **last()**: O(n) - Must traverse to end of list
- **isEmpty()**: O(1) - Simple null check
- **count()**: O(1) - Stored count value

### Bulk Operations

- **addAll()**: O(m×n) - Where m is number of items to add, n is current list size. Each add is O(n).
- **removeAll()**: O(m×n) - Where m is number of items to remove, n is current list size. Each remove is O(n).
- **clear()**: O(1) - Simply sets head to null

### List Manipulation

- **Merge**: O(n+m) time, O(1) space - In-place merge of two sorted lists by reusing nodes
- **Reverse**: O(n) time, O(1) space - In-place reversal by manipulating pointers
- **copy()**: O(n) time, O(n) space - Creates new list with all elements

### Search and Filtering

- **find()**: O(n) - Linear search until predicate matches
- **findAll()**: O(n) time, O(k) space - Where k is number of matches
- **filter()**: O(n²) worst case - O(n) to collect items, then O(n) per removal
- **indexOf()**: O(n) - Linear search for value

### Range Queries

- **slice()**: O(n) time, O(k) space - Where k is slice length
- **range()**: O(n) time, O(k) space - Where k is number of values in range
- **valuesGreaterThan()**: O(n) time, O(k) space - Where k is number of matching values
- **valuesLessThan()**: O(n) time, O(k) space - Where k is number of matching values

### Set Operations

- **union()**: O(n×m) time, O(n+m) space - Where n and m are sizes of both lists. Uses contains() check for each element.
- **intersect()**: O(n×m) time, O(min(n,m)) space - Where n and m are sizes of both lists
- **diff()**: O(n×m) time, O(n) space - Where n and m are sizes of both lists
- **unique()**: O(n²) worst case - O(n) to identify duplicates, then O(n) per removal

### Factory Methods

- **fromArray()**: O(n²) - Where n is array size. Each add operation is O(n).
- **fromIterable()**: O(n²) - Where n is iterable size. Each add operation is O(n).

### Conversion

- **toArray()**: O(n) time, O(n) space - Linear traversal to build array
- **toJson()**: O(n) time, O(n) space - Calls toArray() then json_encode()

### Notes

- All operations maintain the sorted order of the list
- Early termination optimizations are used in `contains()` when possible
- Space complexity for operations returning new lists is proportional to result size
- Worst-case complexities assume no early termination benefits

## License

This library is open-source software. See the license file for details.

## Author

**davidkosvica** - david.kosvica@gmail.com

