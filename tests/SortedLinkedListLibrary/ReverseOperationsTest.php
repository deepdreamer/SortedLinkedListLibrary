<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;

class ReverseOperationsTest extends TestCase
{
    public function testReverseAscendingInts(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $this->assertSame(SortDirection::ASC, $list->getSortOrder());

        $list->reverse();

        $this->assertSame(SortDirection::DESC, $list->getSortOrder());
        $this->assertSame([5, 4, 3, 2, 1], $list->toArray());
    }

    public function testReverseDescendingInts(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $this->assertSame(SortDirection::DESC, $list->getSortOrder());

        $list->reverse();

        $this->assertSame(SortDirection::ASC, $list->getSortOrder());
        $this->assertSame([1, 2, 3, 4, 5], $list->toArray());
    }

    public function testReverseEmptyList(): void
    {
        $list = SortedList::forInts();

        $list->reverse();

        $this->assertTrue($list->isEmpty());
    }

    public function testReverseSingleElement(): void
    {
        $list = SortedList::forInts();
        $list->add(42);

        $list->reverse();

        $this->assertSame([42], $list->toArray());
    }

    public function testReverseStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry');

        $list->reverse();

        $this->assertSame(['cherry', 'banana', 'apple'], $list->toArray());
    }

    public function testReverseTwiceReturnsToOriginal(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $original = $list->toArray();
        $list->reverse()->reverse();

        $this->assertSame($original, $list->toArray());
    }

    public function testReverseIsChainable(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $result = $list->reverse();

        $this->assertSame($list, $result);
        $this->assertSame([3, 2, 1], $list->toArray());
    }

    public function testReversePreservesCount(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $originalCount = $list->count();
        $list->reverse();

        $this->assertSame($originalCount, $list->count());
    }

    public function testReverseWithDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3);

        $list->reverse();

        $this->assertSame([3, 3, 3, 2, 2, 1], $list->toArray());
    }

    public function testReverseMaintainsSortOrderAfterReversal(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(3)->add(5);

        // After reverse, it should be descending
        $list->reverse();
        $this->assertSame(SortDirection::DESC, $list->getSortOrder());
        $this->assertSame([5, 3, 1], $list->toArray());

        // Adding a new value should maintain descending order
        $list->add(4);
        $this->assertSame([5, 4, 3, 1], $list->toArray());
    }
}
