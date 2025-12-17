<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;

class UtilityMethodsTest extends TestCase
{
    public function testCopyCreatesNewInstance(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = $list1->copy();

        $this->assertNotSame($list1, $list2);
        $this->assertSame([1, 2, 3], $list2->toArray());
    }

    public function testCopyIsIndependent(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = $list1->copy();
        $list1->add(4);

        $this->assertSame([1, 2, 3, 4], $list1->toArray());
        $this->assertSame([1, 2, 3], $list2->toArray());
    }

    public function testCopyWithEmptyList(): void
    {
        $list1 = SortedList::forInts();

        $list2 = $list1->copy();

        $this->assertTrue($list2->isEmpty());
    }

    public function testCopyPreservesTypeAndSortOrder(): void
    {
        $list1 = SortedList::forStrings(SortDirection::DESC);
        $list1->add('z')->add('y')->add('x');

        $list2 = $list1->copy();

        $this->assertSame(['z', 'y', 'x'], $list2->toArray());
        // Adding to copy should maintain descending order
        $list2->add('a');
        $this->assertSame(['z', 'y', 'x', 'a'], $list2->toArray());
    }

    public function testEqualsWithIdenticalLists(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2)->add(3);

        $this->assertTrue($list1->equals($list2));
    }

    public function testEqualsWithDifferentValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2)->add(4);

        $this->assertFalse($list1->equals($list2));
    }

    public function testEqualsWithDifferentCounts(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2);

        $this->assertFalse($list1->equals($list2));
    }

    public function testEqualsWithDifferentTypes(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2);

        $list2 = SortedList::forStrings();
        $list2->add('1')->add('2');

        $this->assertFalse($list1->equals($list2));
    }

    public function testEqualsWithEmptyLists(): void
    {
        $list1 = SortedList::forInts();
        $list2 = SortedList::forInts();

        $this->assertTrue($list1->equals($list2));
    }

    public function testEqualsWithStrings(): void
    {
        $list1 = SortedList::forStrings();
        $list1->add('a')->add('b')->add('c');

        $list2 = SortedList::forStrings();
        $list2->add('a')->add('b')->add('c');

        $this->assertTrue($list1->equals($list2));
    }

    public function testMinWithAscendingList(): void
    {
        $list = SortedList::forInts();
        $list->add(5)->add(2)->add(8)->add(1)->add(3);

        $this->assertSame(1, $list->min());
    }

    public function testMinWithDescendingList(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(2)->add(8)->add(1)->add(3);

        $this->assertSame(1, $list->min());
    }

    public function testMinWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $this->assertNull($list->min());
    }

    public function testMinWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('zebra')->add('apple')->add('banana');

        $this->assertSame('apple', $list->min());
    }

    public function testMaxWithAscendingList(): void
    {
        $list = SortedList::forInts();
        $list->add(5)->add(2)->add(8)->add(1)->add(3);

        $this->assertSame(8, $list->max());
    }

    public function testMaxWithDescendingList(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(2)->add(8)->add(1)->add(3);

        $this->assertSame(8, $list->max());
    }

    public function testMaxWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $this->assertNull($list->max());
    }

    public function testMaxWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('zebra')->add('banana');

        $this->assertSame('zebra', $list->max());
    }

    public function testSumWithIntegers(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $this->assertSame(15, $list->sum());
    }

    public function testSumWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $this->assertSame(0, $list->sum());
    }

    public function testSumWithSingleValue(): void
    {
        $list = SortedList::forInts();
        $list->add(42);

        $this->assertSame(42, $list->sum());
    }

    public function testSumWithNegativeValues(): void
    {
        $list = SortedList::forInts();
        $list->add(-5)->add(-2)->add(3)->add(4);

        $this->assertSame(0, $list->sum());
    }

    public function testSumWithLargeNumbers(): void
    {
        $list = SortedList::forInts();
        $list->add(100)->add(200)->add(300);

        $this->assertSame(600, $list->sum());
    }

    public function testCountWithEmptyList(): void
    {
        $list = SortedList::forInts();
        $this->assertSame(0, $list->count());
    }

    public function testCountAfterAdd(): void
    {
        $list = SortedList::forInts();
        $this->assertSame(0, $list->count());

        $list->add(1);
        $this->assertSame(1, $list->count());

        $list->add(2)->add(3);
        $this->assertSame(3, $list->count());
    }

    public function testCountAfterRemove(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);
        $this->assertSame(3, $list->count());

        $list->remove(2);
        $this->assertSame(2, $list->count());

        $list->remove(1);
        $list->remove(3);
        $this->assertSame(0, $list->count());
    }

    public function testCountAfterRemoveEveryOccurrence(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3);
        $this->assertSame(4, $list->count());

        $list->removeEveryOccurrence(2);
        $this->assertSame(2, $list->count());
    }

    public function testCountAfterClear(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);
        $this->assertSame(3, $list->count());

        $list->clear();
        $this->assertSame(0, $list->count());
    }

    public function testCountWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('a')->add('b')->add('c');
        $this->assertSame(3, $list->count());
    }

    public function testCountWithLargeList(): void
    {
        $list = SortedList::forInts();
        for ($i = 1; $i <= 100; $i++) {
            $list->add($i);
        }
        $this->assertSame(100, $list->count());
    }

    public function testCountAfterMerge(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2);
        $this->assertSame(2, $list1->count());

        $list2 = SortedList::forInts();
        $list2->add(3)->add(4);
        $this->assertSame(2, $list2->count());

        $list1->merge($list2);
        $this->assertSame(4, $list1->count());
    }
}
