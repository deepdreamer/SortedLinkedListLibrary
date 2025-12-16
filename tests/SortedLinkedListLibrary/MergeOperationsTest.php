<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;

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
        $this->assertCount(6, $list1);
    }

    public function testMergeDescendingInts(): void
    {
        $list1 = SortedList::forInts(SortDirection::DESC);
        $list1->add(5)->add(3)->add(1);

        $list2 = SortedList::forInts(SortDirection::DESC);
        $list2->add(6)->add(4)->add(2);

        $list1->merge($list2);

        $this->assertSame([6, 5, 4, 3, 2, 1], $list1->toArray());
        $this->assertCount(6, $list1);
    }

    public function testMergeAscendingStrings(): void
    {
        $list1 = SortedList::forStrings();
        $list1->add('apple')->add('cherry');

        $list2 = SortedList::forStrings();
        $list2->add('banana')->add('date');

        $list1->merge($list2);

        $this->assertSame(['apple', 'banana', 'cherry', 'date'], $list1->toArray());
        $this->assertCount(4, $list1);
    }

    public function testMergeWithEmptyList(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();

        $list1->merge($list2);

        $this->assertSame([1, 2, 3], $list1->toArray());
        $this->assertCount(3, $list1);
    }

    public function testMergeEmptyListWithNonEmpty(): void
    {
        $list1 = SortedList::forInts();

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2)->add(3);

        $list1->merge($list2);

        $this->assertSame([1, 2, 3], $list1->toArray());
        $this->assertCount(3, $list1);
    }

    public function testMergeWithSelf(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $list->merge($list);

        $this->assertSame([1, 2, 3], $list->toArray());
        $this->assertCount(3, $list);
    }

    public function testMergeThrowsOnDifferentTypes(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2);

        $list2 = SortedList::forStrings();
        $list2->add('a')->add('b');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot merge lists of different types.');

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
        $this->assertCount(8, $list1);
    }

    public function testMergeWithDuplicateValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(3)->add(3)->add(5);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(3)->add(4);

        $list1->merge($list2);

        $this->assertSame([1, 2, 3, 3, 3, 4, 5], $list1->toArray());
        $this->assertCount(7, $list1);
    }

    public function testMergeAllSmallerValuesFirst(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(4)->add(5)->add(6);

        $list1->merge($list2);

        $this->assertSame([1, 2, 3, 4, 5, 6], $list1->toArray());
        $this->assertCount(6, $list1);
    }

    public function testMergeAllLargerValuesFirst(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(4)->add(5)->add(6);

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2)->add(3);

        $list1->merge($list2);

        $this->assertSame([1, 2, 3, 4, 5, 6], $list1->toArray());
        $this->assertCount(6, $list1);
    }

    public function testMergeEmptiesOtherList(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(4);

        $list1->merge($list2);

        $this->assertTrue($list2->isEmpty());
        $this->assertCount(0, $list2);
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
        $this->assertCount(3, $list1);
    }

    public function testMergeSingleElementLists(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(2);

        $list2 = SortedList::forInts();
        $list2->add(1);

        $list1->merge($list2);

        $this->assertSame([1, 2], $list1->toArray());
        $this->assertCount(2, $list1);
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
        $this->assertCount(100, $list1);
    }

    public function testMergePreservesOrderWithEqualValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(5)->add(5)->add(5);

        $list2 = SortedList::forInts();
        $list2->add(5)->add(5);

        $list1->merge($list2);

        $this->assertSame([5, 5, 5, 5, 5], $list1->toArray());
        $this->assertCount(5, $list1);
    }
}
