<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;

class RangeQueriesTest extends TestCase
{
    /**
     * @return array<string, array{callable(): SortedList, int, int|null, array<int|string>, bool}>
     */
    public static function sliceProvider(): array
    {
        return [
            'with offset' => [
                fn () => SortedList::forInts()->add(1)->add(2)->add(3)->add(4)->add(5),
                2,
                null,
                [3, 4, 5],
                false,
            ],
            'with offset and length' => [
                fn () => SortedList::forInts()->add(1)->add(2)->add(3)->add(4)->add(5),
                1,
                3,
                [2, 3, 4],
                false,
            ],
            'with zero offset' => [
                fn () => SortedList::forInts()->add(1)->add(2)->add(3),
                0,
                null,
                [1, 2, 3],
                false,
            ],
            'with length zero' => [
                fn () => SortedList::forInts()->add(1)->add(2)->add(3),
                1,
                0,
                [],
                true, // isEmpty
            ],
            'with offset beyond length' => [
                fn () => SortedList::forInts()->add(1)->add(2)->add(3),
                10,
                null,
                [],
                true, // isEmpty
            ],
            'with length beyond remaining' => [
                fn () => SortedList::forInts()->add(1)->add(2)->add(3),
                1,
                100,
                [2, 3],
                false,
            ],
            'with empty list' => [
                fn () => SortedList::forInts(),
                0,
                5,
                [],
                true, // isEmpty
            ],
            'with strings' => [
                fn () => SortedList::forStrings()->add('apple')->add('banana')->add('cherry')->add('date'),
                1,
                2,
                ['banana', 'cherry'],
                false,
            ],
        ];
    }

    /**
     * @param callable(): SortedList $setup
     * @param array<int|string> $expected
     */
    #[DataProvider('sliceProvider')]
    public function testSlice(
        callable $setup,
        int $offset,
        ?int $length,
        array $expected,
        bool $shouldBeEmpty
    ): void {
        /** @var SortedList $list */
        $list = $setup();
        $result = $list->slice($offset, $length);

        if ($shouldBeEmpty) {
            $this->assertTrue($result->isEmpty());
        } else {
            $this->assertSame($expected, $result->toArray());
        }
        $this->assertNotSame($list, $result); // Should be a new list
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
    }

    public function testRangeWithSingleValue(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $result = $list->range(3, 3);

        $this->assertSame([3], $result->toArray());
    }

    public function testRangeWithNoMatches(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $result = $list->range(10, 20);

        $this->assertTrue($result->isEmpty());
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

        $this->assertSame([5, 4], $result->toArray());
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

        $this->assertSame([2, 1], $result->toArray());
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
