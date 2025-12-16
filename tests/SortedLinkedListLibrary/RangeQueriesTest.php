<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;

class RangeQueriesTest extends TestCase
{
    public function testSliceWithOffset(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $result = $list->slice(2);

        $this->assertSame([3, 4, 5], $result->toArray());
        $this->assertCount(3, $result);
        $this->assertNotSame($list, $result); // Should be a new list
    }

    public function testSliceWithOffsetAndLength(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $result = $list->slice(1, 3);

        $this->assertSame([2, 3, 4], $result->toArray());
        $this->assertCount(3, $result);
    }

    public function testSliceWithZeroOffset(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $result = $list->slice(0);

        $this->assertSame([1, 2, 3], $result->toArray());
        $this->assertCount(3, $result);
    }

    public function testSliceWithLengthZero(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $result = $list->slice(1, 0);

        $this->assertTrue($result->isEmpty());
        $this->assertCount(0, $result);
    }

    public function testSliceWithOffsetBeyondLength(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $result = $list->slice(10);

        $this->assertTrue($result->isEmpty());
        $this->assertCount(0, $result);
    }

    public function testSliceWithLengthBeyondRemaining(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $result = $list->slice(1, 100);

        $this->assertSame([2, 3], $result->toArray());
        $this->assertCount(2, $result);
    }

    public function testSliceWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $result = $list->slice(0, 5);

        $this->assertTrue($result->isEmpty());
        $this->assertCount(0, $result);
    }

    public function testSliceWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry')->add('date');

        $result = $list->slice(1, 2);

        $this->assertSame(['banana', 'cherry'], $result->toArray());
        $this->assertCount(2, $result);
    }

    public function testSlicePreservesSortOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $result = $list->slice(1, 3);

        $this->assertSame([4, 3, 2], $result->toArray());
    }

    public function testRangeWithInclusiveBounds(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5)->add(6)->add(7);

        $result = $list->range(3, 5);

        $this->assertSame([3, 4, 5], $result->toArray());
        $this->assertCount(3, $result);
    }

    public function testRangeWithSingleValue(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $result = $list->range(3, 3);

        $this->assertSame([3], $result->toArray());
        $this->assertCount(1, $result);
    }

    public function testRangeWithNoMatches(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $result = $list->range(10, 20);

        $this->assertTrue($result->isEmpty());
        $this->assertCount(0, $result);
    }

    public function testRangeWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(7)->add(6)->add(5)->add(4)->add(3)->add(2)->add(1);

        $result = $list->range(5, 3);

        $this->assertSame([5, 4, 3], $result->toArray());
    }

    public function testRangeWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry')->add('date')->add('elderberry');

        $result = $list->range('banana', 'date');

        $this->assertSame(['banana', 'cherry', 'date'], $result->toArray());
    }

    public function testRangeWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $result = $list->range(1, 5);

        $this->assertTrue($result->isEmpty());
    }

    public function testValuesGreaterThan(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $result = $list->valuesGreaterThan(3);

        $this->assertSame([4, 5], $result->toArray());
        $this->assertCount(2, $result);
    }

    public function testValuesGreaterThanWithNoMatches(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $result = $list->valuesGreaterThan(10);

        $this->assertTrue($result->isEmpty());
    }

    public function testValuesGreaterThanWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $result = $list->valuesGreaterThan(3);

        $this->assertSame([2, 1], $result->toArray());
    }

    public function testValuesGreaterThanWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry')->add('date');

        $result = $list->valuesGreaterThan('banana');

        $this->assertSame(['cherry', 'date'], $result->toArray());
    }

    public function testValuesLessThan(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $result = $list->valuesLessThan(3);

        $this->assertSame([1, 2], $result->toArray());
        $this->assertCount(2, $result);
    }

    public function testValuesLessThanWithNoMatches(): void
    {
        $list = SortedList::forInts();
        $list->add(5)->add(6)->add(7);

        $result = $list->valuesLessThan(1);

        $this->assertTrue($result->isEmpty());
    }

    public function testValuesLessThanWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $result = $list->valuesLessThan(3);

        $this->assertSame([5, 4], $result->toArray());
    }

    public function testValuesLessThanWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry')->add('date');

        $result = $list->valuesLessThan('cherry');

        $this->assertSame(['apple', 'banana'], $result->toArray());
    }

    public function testValuesLessThanWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $result = $list->valuesLessThan(5);

        $this->assertTrue($result->isEmpty());
    }
}
