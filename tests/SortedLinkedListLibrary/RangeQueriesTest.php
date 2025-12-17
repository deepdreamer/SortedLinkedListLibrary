<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;

class RangeQueriesTest extends TestCase
{
    /**
     * @return array<string, array{
     *     setup: callable(): SortedList,
     *     offset: int,
     *     length: int|null,
     *     expected: array<int|string>,
     *     shouldBeEmpty: bool
     * }>
     */
    public static function sliceProvider(): array
    {
        return [
            'with offset' => [
                'setup' => fn (): SortedList => SortedList::forInts()->add(1)->add(2)->add(3)->add(4)->add(5),
                'offset' => 2,
                'length' => null,
                'expected' => [3, 4, 5],
                'shouldBeEmpty' => false,
            ],
            'with offset and length' => [
                'setup' => fn (): SortedList => SortedList::forInts()->add(1)->add(2)->add(3)->add(4)->add(5),
                'offset' => 1,
                'length' => 3,
                'expected' => [2, 3, 4],
                'shouldBeEmpty' => false,
            ],
            'with zero offset' => [
                'setup' => fn (): SortedList => SortedList::forInts()->add(1)->add(2)->add(3),
                'offset' => 0,
                'length' => null,
                'expected' => [1, 2, 3],
                'shouldBeEmpty' => false,
            ],
            'with length zero' => [
                'setup' => fn (): SortedList => SortedList::forInts()->add(1)->add(2)->add(3),
                'offset' => 1,
                'length' => 0,
                'expected' => [],
                'shouldBeEmpty' => true,
            ],
            'with offset beyond length' => [
                'setup' => fn (): SortedList => SortedList::forInts()->add(1)->add(2)->add(3),
                'offset' => 10,
                'length' => null,
                'expected' => [],
                'shouldBeEmpty' => true,
            ],
            'with length beyond remaining' => [
                'setup' => fn (): SortedList => SortedList::forInts()->add(1)->add(2)->add(3),
                'offset' => 1,
                'length' => 100,
                'expected' => [2, 3],
                'shouldBeEmpty' => false,
            ],
            'with empty list' => [
                'setup' => fn (): SortedList => SortedList::forInts(),
                'offset' => 0,
                'length' => 5,
                'expected' => [],
                'shouldBeEmpty' => true,
            ],
            'with strings' => [
                'setup' => fn (): SortedList => SortedList::forStrings()->add('apple')->add('banana')->add('cherry')->add('date'),
                'offset' => 1,
                'length' => 2,
                'expected' => ['banana', 'cherry'],
                'shouldBeEmpty' => false,
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
