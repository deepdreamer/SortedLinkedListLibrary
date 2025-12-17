<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\ListType;
use SortedLinkedListLibrary\Enums\SortDirection;
use SortedLinkedListLibrary\Exceptions\DifferentListTypesException;
use SortedLinkedListLibrary\Exceptions\DifferentSortDirectionsException;

class MergeOperationsTest extends TestCase
{
    public function testMergeAscendingInts(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(3)->add(5);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(4)->add(6);

        $list1->merge($list2);

        $this->assertSame([1, 2, 3, 4, 5, 6], $list1->toArray());
    }

    public function testMergeDescendingInts(): void
    {
        $list1 = SortedList::forInts(SortDirection::DESC);
        $list1->add(5)->add(3)->add(1);

        $list2 = SortedList::forInts(SortDirection::DESC);
        $list2->add(6)->add(4)->add(2);

        $list1->merge($list2);

        $this->assertSame([6, 5, 4, 3, 2, 1], $list1->toArray());
    }

    public function testMergeAscendingStrings(): void
    {
        $list1 = SortedList::forStrings();
        $list1->add('apple')->add('cherry');

        $list2 = SortedList::forStrings();
        $list2->add('banana')->add('date');

        $list1->merge($list2);

        $this->assertSame(['apple', 'banana', 'cherry', 'date'], $list1->toArray());
    }

    public function testMergeWithEmptyList(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();

        $list1->merge($list2);

        $this->assertSame([1, 2, 3], $list1->toArray());
    }

    public function testMergeEmptyListWithNonEmpty(): void
    {
        $list1 = SortedList::forInts();

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2)->add(3);

        $list1->merge($list2);

        $this->assertSame([1, 2, 3], $list1->toArray());
    }

    public function testMergeWithSelf(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $list->merge($list);

        $this->assertSame([1, 2, 3], $list->toArray());
    }

    public function testMergeThrowsOnDifferentTypes(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2);

        $list2 = SortedList::forStrings();
        $list2->add('a')->add('b');

        $this->expectException(DifferentListTypesException::class);
        $this->expectExceptionMessage('Cannot perform merge() on lists with different types');

        $list1->merge($list2);
    }

    public function testMergeThrowsOnDifferentSortDirections(): void
    {
        $list1 = SortedList::forInts(SortDirection::ASC);
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts(SortDirection::DESC);
        $list2->add(6)->add(5)->add(4);

        $this->expectException(DifferentSortDirectionsException::class);
        $this->expectExceptionMessage('Cannot perform merge() on lists with different sort directions');

        $list1->merge($list2);
    }

    public function testMergeWithInterleavedValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(5)->add(9);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(4)->add(6)->add(8)->add(10);

        $list1->merge($list2);

        $this->assertSame([1, 2, 4, 5, 6, 8, 9, 10], $list1->toArray());
    }

    public function testMergeWithDuplicateValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(3)->add(3)->add(5);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(3)->add(4);

        $list1->merge($list2);

        $this->assertSame([1, 2, 3, 3, 3, 4, 5], $list1->toArray());
    }

    public function testMergeAllSmallerValuesFirst(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(4)->add(5)->add(6);

        $list1->merge($list2);

        $this->assertSame([1, 2, 3, 4, 5, 6], $list1->toArray());
    }

    public function testMergeAllLargerValuesFirst(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(4)->add(5)->add(6);

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2)->add(3);

        $list1->merge($list2);

        $this->assertSame([1, 2, 3, 4, 5, 6], $list1->toArray());
    }

    public function testMergeEmptiesOtherList(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(4);

        $list1->merge($list2);

        $this->assertTrue($list2->isEmpty());
        $this->assertSame([], $list2->toArray());
    }

    public function testMergeIsChainable(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1);

        $list2 = SortedList::forInts();
        $list2->add(2);

        $list3 = SortedList::forInts();
        $list3->add(3);

        $result = $list1->merge($list2)->merge($list3);

        $this->assertSame($list1, $result);
        $this->assertSame([1, 2, 3], $list1->toArray());
    }

    public function testMergeSingleElementLists(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(2);

        $list2 = SortedList::forInts();
        $list2->add(1);

        $list1->merge($list2);

        $this->assertSame([1, 2], $list1->toArray());
    }

    public function testMergeLargeLists(): void
    {
        $list1 = SortedList::forInts();
        for ($i = 1; $i <= 100; $i += 2) {
            $list1->add($i);
        }

        $list2 = SortedList::forInts();
        for ($i = 2; $i <= 100; $i += 2) {
            $list2->add($i);
        }

        $list1->merge($list2);

        $expected = range(1, 100);
        $this->assertSame($expected, $list1->toArray());
    }

    public function testMergePreservesOrderWithEqualValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(5)->add(5)->add(5);

        $list2 = SortedList::forInts();
        $list2->add(5)->add(5);

        $list1->merge($list2);

        $this->assertSame([5, 5, 5, 5, 5], $list1->toArray());
    }

    /**
     * Tests merge() with a different SortedListInterface implementation (generic path).
     * This verifies the optimized generic path that converts to array then merges.
     */
    public function testMergeWithDifferentSortedListInterfaceImplementation(): void
    {
        // Create a simple mock implementation of SortedListInterface
        $mockList = new class implements SortedListInterface {
            private array $values = [2, 4, 6];
            private ListType $type = ListType::INT;
            private SortDirection $sortDirection = SortDirection::ASC;

            public function add(int|string $value): self { return $this; }
            public function remove(int|string $value): bool { return false; }
            public function removeEveryOccurrence(int|string $value): int { return 0; }
            public function contains(int|string $value): bool { return in_array($value, $this->values, true); }
            public function isEmpty(): bool { return empty($this->values); }
            public function addAll(iterable $values): self { return $this; }
            public function removeAll(iterable $values): int { return 0; }
            public function removeAllAndEveryOccurrence(iterable $values): int { return 0; }
            public function clear(): self { $this->values = []; return $this; }
            public function getAt(int $index): int|string { return $this->values[$index]; }
            public function getAtOrNull(int $index): int|string|null { return $this->values[$index] ?? null; }
            public function first(): int|string { return $this->values[0]; }
            public function firstOrNull(): int|string|null { return $this->values[0] ?? null; }
            public function last(): int|string { return $this->values[count($this->values) - 1]; }
            public function lastOrNull(): int|string|null { return $this->values[count($this->values) - 1] ?? null; }
            public function count(): int { return count($this->values); }
            public function getType(): ListType { return $this->type; }
            public function getSortOrder(): SortDirection { return $this->sortDirection; }
            public function toArray(): array { return $this->values; }
            public function toJson(int $options = 0, int $depth = 512): string|false { return json_encode($this->toArray(), $options, $depth); }
            public function jsonSerialize(): array { return $this->toArray(); }
            public function getIterator(): \Traversable { return new \ArrayIterator($this->values); }
            public function find(callable $predicate): int|string|null { return null; }
            public function findAll(callable $predicate): self { return $this; }
            public function filter(callable $predicate): self { return $this; }
            public function indexOf(int|string $value): int|null { return null; }
            public function slice(int $offset, ?int $length = null): self { return $this; }
            public function range(int|string $from, int|string $to): self { return $this; }
            public function valuesGreaterThan(int|string $value): self { return $this; }
            public function valuesLessThan(int|string $value): self { return $this; }
            public function union(SortedListInterface $other): self { return $this; }
            public function unionWithDuplicates(SortedListInterface $other): self { return $this; }
            public function intersect(SortedListInterface $other): self { return $this; }
            public function diff(SortedListInterface $other): self { return $this; }
            public function unique(): self { return $this; }
            public function merge(SortedListInterface $other): self { return $this; }
            public function reverse(): self { return $this; }
            public function copy(): self { return $this; }
            public function equals(SortedListInterface $other): bool { return false; }
            public function min(): int|string|null { return min($this->values) ?: null; }
            public function max(): int|string|null { return max($this->values) ?: null; }
            public function sum(): int|float { return array_sum($this->values); }
            public function removeAt(int $index): int|string { return array_splice($this->values, $index, 1)[0]; }
            public function removeFirst(int $count = 1): array { return array_splice($this->values, 0, $count); }
            public function removeLast(int $count = 1): array { return array_splice($this->values, -$count); }
        };

        $list1 = SortedList::forInts();
        $list1->add(1)->add(3)->add(5);

        // Merge with mock implementation (should use generic path)
        $list1->merge($mockList);

        // Verify merge worked correctly (generic path converts to array then merges)
        $this->assertSame([1, 2, 3, 4, 5, 6], $list1->toArray());
        $this->assertSame(6, $list1->count());
    }
}
