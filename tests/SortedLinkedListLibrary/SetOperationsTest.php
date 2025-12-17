<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;
use SortedLinkedListLibrary\Exceptions\DifferentListTypesException;

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
    }

    public function testUnionWithEmptyList(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();

        $result = $list1->union($list2);

        $this->assertSame([1, 2, 3], $result->toArray());
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

    public function testUnionWithDuplicatesInFirstList(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(4)->add(5);

        $result = $list1->union($list2);

        $this->assertSame([1, 2, 3, 4, 5], $result->toArray());
    }

    public function testUnionWithDuplicatesInSecondList(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(3)->add(3)->add(4)->add(4);

        $result = $list1->union($list2);

        $this->assertSame([1, 2, 3, 4], $result->toArray());
    }

    public function testUnionWithDuplicatesInBothLists(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(2)->add(3)->add(3)->add(4);

        $result = $list1->union($list2);

        $this->assertSame([1, 2, 3, 4], $result->toArray());
    }

    public function testUnionWithDescendingOrder(): void
    {
        $list1 = SortedList::forInts(SortDirection::DESC);
        $list1->add(5)->add(4)->add(3);

        $list2 = SortedList::forInts(SortDirection::DESC);
        $list2->add(3)->add(2)->add(1);

        $result = $list1->union($list2);

        $this->assertSame([5, 4, 3, 2, 1], $result->toArray());
    }

    public function testUnionWithEmptyFirstList(): void
    {
        $list1 = SortedList::forInts();

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2)->add(3);

        $result = $list1->union($list2);

        $this->assertSame([1, 2, 3], $result->toArray());
    }

    public function testUnionWithBothEmptyLists(): void
    {
        $list1 = SortedList::forInts();
        $list2 = SortedList::forInts();

        $result = $list1->union($list2);

        $this->assertTrue($result->isEmpty());
    }

    public function testUnionWithIdenticalLists(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2)->add(3);

        $result = $list1->union($list2);

        $this->assertSame([1, 2, 3], $result->toArray());
    }

    public function testUnionWithStringsAndDuplicates(): void
    {
        $list1 = SortedList::forStrings();
        $list1->add('apple')->add('banana')->add('banana');

        $list2 = SortedList::forStrings();
        $list2->add('banana')->add('cherry')->add('date');

        $result = $list1->union($list2);

        $this->assertSame(['apple', 'banana', 'cherry', 'date'], $result->toArray());
    }

    public function testUnionWithLargeLists(): void
    {
        $list1 = SortedList::forInts();
        for ($i = 0; $i < 50; $i++) {
            $list1->add($i);
        }

        $list2 = SortedList::forInts();
        for ($i = 25; $i < 75; $i++) {
            $list2->add($i);
        }

        $result = $list1->union($list2);

        $this->assertSame(range(0, 74), $result->toArray());
    }

    // ============================================================================
    // unionWithDuplicates tests
    // ============================================================================

    public function testUnionWithDuplicatesBasic(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(4)->add(5)->add(6);

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame([1, 2, 3, 4, 5, 6], $result->toArray());
        $this->assertNotSame($list1, $result);
    }

    public function testUnionWithDuplicatesPreservesAllDuplicates(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(3)->add(3)->add(4);

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame([1, 2, 2, 2, 3, 3, 3, 4], $result->toArray());
    }

    public function testUnionWithDuplicatesWithOverlappingValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(3)->add(4);

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame([1, 2, 2, 3, 3, 4], $result->toArray());
    }

    public function testUnionWithDuplicatesWithEmptyList(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame([1, 2, 3], $result->toArray());
    }

    public function testUnionWithDuplicatesWithEmptyFirstList(): void
    {
        $list1 = SortedList::forInts();

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2)->add(3);

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame([1, 2, 3], $result->toArray());
    }

    public function testUnionWithDuplicatesWithBothEmptyLists(): void
    {
        $list1 = SortedList::forInts();
        $list2 = SortedList::forInts();

        $result = $list1->unionWithDuplicates($list2);

        $this->assertTrue($result->isEmpty());
    }

    public function testUnionWithDuplicatesPreservesOriginalLists(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2);

        $list2 = SortedList::forInts();
        $list2->add(3)->add(4);

        $list1->unionWithDuplicates($list2);

        $this->assertSame([1, 2], $list1->toArray());
        $this->assertSame([3, 4], $list2->toArray());
    }

    public function testUnionWithDuplicatesWithStrings(): void
    {
        $list1 = SortedList::forStrings();
        $list1->add('apple')->add('banana');

        $list2 = SortedList::forStrings();
        $list2->add('cherry')->add('date');

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame(['apple', 'banana', 'cherry', 'date'], $result->toArray());
    }

    public function testUnionWithDuplicatesWithStringsAndDuplicates(): void
    {
        $list1 = SortedList::forStrings();
        $list1->add('apple')->add('banana')->add('banana');

        $list2 = SortedList::forStrings();
        $list2->add('banana')->add('cherry')->add('cherry')->add('date');

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame(['apple', 'banana', 'banana', 'banana', 'cherry', 'cherry', 'date'], $result->toArray());
    }

    public function testUnionWithDuplicatesWithDescendingOrder(): void
    {
        $list1 = SortedList::forInts(SortDirection::DESC);
        $list1->add(5)->add(4)->add(3);

        $list2 = SortedList::forInts(SortDirection::DESC);
        $list2->add(3)->add(2)->add(1);

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame([5, 4, 3, 3, 2, 1], $result->toArray());
    }

    public function testUnionWithDuplicatesWithManyDuplicates(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(2)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(2)->add(3)->add(3)->add(3)->add(4);

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame([1, 2, 2, 2, 2, 2, 3, 3, 3, 3, 4], $result->toArray());
    }

    public function testUnionWithDuplicatesWithIdenticalLists(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(1)->add(2)->add(3);

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame([1, 1, 2, 2, 3, 3], $result->toArray());
    }

    public function testUnionWithDuplicatesWithConsecutiveDuplicates(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(5)->add(5)->add(5);

        $list2 = SortedList::forInts();
        $list2->add(5)->add(5)->add(10);

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame([1, 5, 5, 5, 5, 5, 10], $result->toArray());
    }

    public function testUnionWithDuplicatesWithLargeLists(): void
    {
        $list1 = SortedList::forInts();
        for ($i = 0; $i < 50; $i++) {
            $list1->add($i % 10); // Creates duplicates
        }

        $list2 = SortedList::forInts();
        for ($i = 25; $i < 75; $i++) {
            $list2->add($i % 10); // Creates duplicates
        }

        $result = $list1->unionWithDuplicates($list2);

        // Should have all values from both lists with all duplicates preserved
        // Verify it's still sorted by checking values are in ascending order
        $prev = null;
        foreach ($result as $value) {
            if ($prev !== null) {
                $this->assertGreaterThanOrEqual($prev, $value, 'List should be sorted in ascending order');
            }
            $prev = $value;
            // Verify all values are in range [0, 9]
            $this->assertGreaterThanOrEqual(0, $value);
            $this->assertLessThanOrEqual(9, $value);
        }
    }

    public function testUnionWithDuplicatesComparisonWithUnion(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(3)->add(3)->add(4);

        $unionResult = $list1->union($list2);
        $unionWithDuplicatesResult = $list1->unionWithDuplicates($list2);

        // Union should have unique values
        $this->assertSame([1, 2, 3, 4], $unionResult->toArray());

        // UnionWithDuplicates should preserve all duplicates
        $this->assertSame([1, 2, 2, 2, 3, 3, 3, 4], $unionWithDuplicatesResult->toArray());
    }

    public function testIntersectWithCommonValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3)->add(4);

        $list2 = SortedList::forInts();
        $list2->add(2)->add(3)->add(5);

        $result = $list1->intersect($list2);

        $this->assertSame([2, 3], $result->toArray());
    }

    public function testIntersectWithNoCommonValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(1)->add(2)->add(3);

        $list2 = SortedList::forInts();
        $list2->add(4)->add(5)->add(6);

        $result = $list1->intersect($list2);

        $this->assertTrue($result->isEmpty());
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
    }

    public function testUniqueWithNoDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $list->unique();

        $this->assertSame([1, 2, 3], $list->toArray());
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
    }

    public function testUnionTypeEnforcement(): void
    {
        $this->expectException(DifferentListTypesException::class);

        $list1 = SortedList::forInts();
        $list1->add(1)->add(2);

        $list2 = SortedList::forStrings();
        $list2->add('a')->add('b');

        $list1->union($list2);
    }

    public function testUnionWithDuplicatesTypeEnforcement(): void
    {
        $this->expectException(DifferentListTypesException::class);

        $list1 = SortedList::forInts();
        $list1->add(1)->add(2);

        $list2 = SortedList::forStrings();
        $list2->add('a')->add('b');

        $list1->unionWithDuplicates($list2);
    }

    public function testUnionWithZeroAndNegativeValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(-2)->add(-1)->add(0);

        $list2 = SortedList::forInts();
        $list2->add(0)->add(1)->add(2);

        $result = $list1->union($list2);

        $this->assertSame([-2, -1, 0, 1, 2], $result->toArray());
    }

    public function testUnionWithDuplicatesWithZeroAndNegativeValues(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(-2)->add(-1)->add(0)->add(0);

        $list2 = SortedList::forInts();
        $list2->add(0)->add(0)->add(1)->add(2);

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame([-2, -1, 0, 0, 0, 0, 1, 2], $result->toArray());
    }

    public function testUnionWithSingleElementLists(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(5);

        $list2 = SortedList::forInts();
        $list2->add(10);

        $result = $list1->union($list2);

        $this->assertSame([5, 10], $result->toArray());
    }

    public function testUnionWithDuplicatesWithSingleElementLists(): void
    {
        $list1 = SortedList::forInts();
        $list1->add(5);

        $list2 = SortedList::forInts();
        $list2->add(5);

        $result = $list1->unionWithDuplicates($list2);

        $this->assertSame([5, 5], $result->toArray());
    }
}
