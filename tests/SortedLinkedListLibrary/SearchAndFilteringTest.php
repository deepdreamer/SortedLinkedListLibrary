<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;

class SearchAndFilteringTest extends TestCase
{
    public function testFindWithMatchingValue(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $result = $list->find(fn (int|string $value): bool => \is_int($value) && $value > 3);

        $this->assertSame(4, $result);
    }

    public function testFindWithNoMatch(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $result = $list->find(fn (int|string $value): bool => \is_int($value) && $value > 10);

        $this->assertNull($result);
    }

    public function testFindWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $result = $list->find(fn (int|string $value): bool => \is_int($value) && $value > 0);

        $this->assertNull($result);
    }

    public function testFindWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry');

        $result = $list->find(fn (int|string $value): bool => \is_string($value) && strlen($value) > 5);

        $this->assertSame('banana', $result);
    }

    public function testFindFirstMatch(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        // Should return first match, not all matches
        $result = $list->find(fn (int|string $value): bool => \is_int($value) && $value % 2 === 0);

        $this->assertSame(2, $result);
    }

    public function testFindAllWithMatchingValues(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5)->add(6);

        $result = $list->findAll(fn (int|string $value): bool => \is_int($value) && $value % 2 === 0);

        $this->assertSame([2, 4, 6], $result->toArray());
        $this->assertNotSame($list, $result); // Should be a new list
    }

    public function testFindAllWithNoMatches(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $result = $list->findAll(fn (int|string $value): bool => \is_int($value) && $value > 10);

        $this->assertTrue($result->isEmpty());
    }

    public function testFindAllWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $result = $list->findAll(fn (int|string $value): bool => \is_int($value) && $value > 0);

        $this->assertTrue($result->isEmpty());
    }

    public function testFindAllPreservesSortOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $result = $list->findAll(fn (int|string $value): bool => \is_int($value) && $value > 2);

        $this->assertSame([5, 4, 3], $result->toArray());
    }

    public function testFindAllWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry')->add('date');

        $result = $list->findAll(fn (int|string $value): bool => \is_string($value) && strpos($value, 'a') !== false);

        $this->assertSame(['apple', 'banana', 'date'], $result->toArray());
    }

    public function testFilterRemovesNonMatchingValues(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $result = $list->filter(fn (int|string $value): bool => \is_int($value) && $value % 2 === 0);

        $this->assertSame($list, $result); // Should return same instance
        $this->assertSame([2, 4], $list->toArray());
    }

    public function testFilterWithAllMatching(): void
    {
        $list = SortedList::forInts();
        $list->add(2)->add(4)->add(6);

        $list->filter(fn (int|string $value): bool => \is_int($value) && $value % 2 === 0);

        $this->assertSame([2, 4, 6], $list->toArray());
    }

    public function testFilterWithNoMatches(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $list->filter(fn (int|string $value): bool => \is_int($value) && $value > 10);

        $this->assertTrue($list->isEmpty());
    }

    public function testFilterIsChainable(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $result = $list
            ->filter(fn (int|string $value): bool => \is_int($value) && $value > 2)
            ->filter(fn (int|string $value): bool => \is_int($value) && $value < 5);

        $this->assertSame($list, $result);
        $this->assertSame([3, 4], $list->toArray());
    }

    public function testFilterWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry')->add('date');

        $list->filter(fn (int|string $value): bool => \is_string($value) && strlen($value) <= 5);

        $this->assertSame(['apple', 'date'], $list->toArray());
    }

    public function testIndexOfWithExistingValue(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $index = $list->indexOf(3);

        $this->assertSame(2, $index);
    }

    public function testIndexOfWithFirstValue(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $index = $list->indexOf(1);

        $this->assertSame(0, $index);
    }

    public function testIndexOfWithLastValue(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $index = $list->indexOf(3);

        $this->assertSame(2, $index);
    }

    public function testIndexOfWithNonExistingValue(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $index = $list->indexOf(10);

        $this->assertNull($index);
    }

    public function testIndexOfWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $index = $list->indexOf(1);

        $this->assertNull($index);
    }

    public function testIndexOfWithDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3);

        $index = $list->indexOf(2);

        $this->assertSame(1, $index);
    }

    public function testIndexOfWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry');

        $index = $list->indexOf('banana');

        $this->assertSame(1, $index);
    }

    public function testIndexOfWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $index = $list->indexOf(3);

        $this->assertSame(2, $index);
    }
}
