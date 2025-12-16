<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;

class SetOperationsTest extends TestCase
{
    public function testUnionWithUniqueValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(4)->add(5)->add(6);

        $result = $list1->union($list2);

        $this->assertSame([1, 2, 3, 4, 5, 6], $result->toArray());
        $this->assertCount(6, $result);
        $this->assertNotSame($list1, $result); // Should be a new list
    }

    public function testUnionWithOverlappingValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(3)->add(4);

        $result = $list1->union($list2);

        $this->assertSame([1, 2, 3, 4], $result->toArray());
        $this->assertCount(4, $result);
    }

    public function testUnionWithEmptyList(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();

        $result = $list1->union($list2);

        $this->assertSame([1, 2, 3], $result->toArray());
        $this->assertCount(3, $result);
    }

    public function testUnionWithStrings(): void
    {
        $list1 = SortedList::forStrings();
        $list1->add('apple')->add('banana');

        $list2 = SortedList::forStrings();
        $list2->add('cherry')->add('date');

        $result = $list1->union($list2);

        $this->assertSame(['apple', 'banana', 'cherry', 'date'], $result->toArray());
    }

    public function testUnionPreservesOriginalLists(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2);

        $list2 = SortedList::forInts();
        $list2->add(3)->add(4);

        $list1->union($list2);

        $this->assertSame([1, 2], $list1->toArray());
        $this->assertSame([3, 4], $list2->toArray());
    }

    public function testIntersectWithCommonValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3)->add(4);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(3)->add(5);

        $result = $list1->intersect($list2);

        $this->assertSame([2, 3], $result->toArray());
        $this->assertCount(2, $result);
    }

    public function testIntersectWithNoCommonValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(4)->add(5)->add(6);

        $result = $list1->intersect($list2);

        $this->assertTrue($result->isEmpty());
        $this->assertCount(0, $result);
    }

    public function testIntersectWithEmptyList(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();

        $result = $list1->intersect($list2);

        $this->assertTrue($result->isEmpty());
    }

    public function testIntersectWithStrings(): void
    {
        $list1 = SortedList::forStrings();
        $list1->add('apple')->add('banana')->add('cherry');

        $list2 = SortedList::forStrings();
        $list2->add('banana')->add('date');

        $result = $list1->intersect($list2);

        $this->assertSame(['banana'], $result->toArray());
    }

    public function testDiffWithValuesInFirstButNotSecond(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3)->add(4);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(4);

        $result = $list1->diff($list2);

        $this->assertSame([1, 3], $result->toArray());
        $this->assertCount(2, $result);
    }

    public function testDiffWithNoDifference(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2)->add(3);

        $result = $list1->diff($list2);

        $this->assertTrue($result->isEmpty());
    }

    public function testDiffWithEmptySecondList(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();

        $result = $list1->diff($list2);

        $this->assertSame([1, 2, 3], $result->toArray());
    }

    public function testDiffWithStrings(): void
    {
        $list1 = SortedList::forStrings();
        $list1->add('apple')->add('banana')->add('cherry');

        $list2 = SortedList::forStrings();
        $list2->add('banana');

        $result = $list1->diff($list2);

        $this->assertSame(['apple', 'cherry'], $result->toArray());
    }

    public function testUniqueWithDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3);

        $result = $list->unique();

        $this->assertSame($list, $result); // Should return same instance
        $this->assertSame([1, 2, 3], $list->toArray());
        $this->assertCount(3, $list);
    }

    public function testUniqueWithNoDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $list->unique();

        $this->assertSame([1, 2, 3], $list->toArray());
        $this->assertCount(3, $list);
    }

    public function testUniqueWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $list->unique();

        $this->assertTrue($list->isEmpty());
    }

    public function testUniqueWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('banana')->add('cherry');

        $list->unique();

        $this->assertSame(['apple', 'banana', 'cherry'], $list->toArray());
    }

    public function testUniqueIsChainable(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(1)->add(2)->add(2);

        $result = $list->unique();

        $this->assertSame($list, $result);
        $this->assertSame([1, 2], $list->toArray());
    }

    public function testUniqueWithAllDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(5)->add(5)->add(5)->add(5);

        $list->unique();

        $this->assertSame([5], $list->toArray());
        $this->assertCount(1, $list);
    }
}
