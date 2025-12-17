<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\ListType;
use SortedLinkedListLibrary\Enums\SortDirection;

class QueryMethodsTest extends TestCase
{
    public function testIsSortedWithValidAscendingList(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $this->thenIsSortedAsc($list);
    }

    public function testIsSortedWithValidDescendingList(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $this->thenIsSortedDesc($list);
    }


    public function testIsSortedWithDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3);

        $this->thenIsSortedAsc($list);
    }

    public function testIsSortedWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry');

        $this->thenIsSortedAsc($list);
    }

    public function testGetSortOrderReturnsTrueForAscending(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $this->assertSame(SortDirection::ASC, $list->getSortOrder());
    }

    public function testGetSortOrderReturnsFalseForDescending(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(3)->add(2)->add(1);

        $this->assertSame(SortDirection::DESC, $list->getSortOrder());
    }

    public function testGetSortOrderAfterReverse(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $this->assertSame(SortDirection::ASC, $list->getSortOrder());

        $list->reverse();

        $this->assertSame(SortDirection::DESC, $list->getSortOrder());
    }

    public function testGetTypeReturnsInt(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $this->assertSame(ListType::INT, $list->getType());
    }

    public function testGetTypeReturnsString(): void
    {
        $list = SortedList::forStrings();
        $list->add('a')->add('b');

        $this->assertSame(ListType::STRING, $list->getType());
    }

    public function testGetTypeWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $this->assertSame(ListType::INT, $list->getType());
    }

    public function testGetAtOrNullWithValidIndex(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $this->assertSame(3, $list->getAtOrNull(2));
        $this->assertSame(1, $list->getAtOrNull(0));
        $this->assertSame(5, $list->getAtOrNull(4));
    }

    /**
     * @return array<string, array{callable(): SortedList, int, int|string|null}>
     */
    public static function getAtOrNullEdgeCasesProvider(): array
    {
        return [
            'negative index' => [
                fn () => SortedList::forInts()->add(1)->add(2)->add(3),
                -1,
                null,
            ],
            'index out of range' => [
                fn () => SortedList::forInts()->add(1)->add(2)->add(3),
                10,
                null,
            ],
            'empty list' => [
                fn () => SortedList::forInts(),
                0,
                null,
            ],
            'with strings' => [
                fn () => SortedList::forStrings()->add('apple')->add('banana')->add('cherry'),
                1,
                'banana',
            ],
            'descending order' => [
                fn () => SortedList::forInts(SortDirection::DESC)->add(5)->add(4)->add(3)->add(2)->add(1),
                2,
                3,
            ],
        ];
    }

    /**
     * @param callable(): SortedList $setup
     */
    #[DataProvider('getAtOrNullEdgeCasesProvider')]
    public function testGetAtOrNullEdgeCases(
        callable $setup,
        int $index,
        int|string|null $expected
    ): void {
        /** @var SortedList $list */
        $list = $setup();
        $this->assertSame($expected, $list->getAtOrNull($index));
    }

    public function testGetAtOrNullAtBoundary(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $this->assertSame(1, $list->getAtOrNull(0));
        $this->assertSame(3, $list->getAtOrNull(2));
        $this->assertNull($list->getAtOrNull(3));
    }

    private function thenIsSortedAsc(SortedListInterface $list): void
    {
        $previous = null;
        foreach ($list as $value) {
            if ($previous !== null) {
                $this->assertGreaterThanOrEqual($previous, $value);
            }
            $previous = $value;
        }
    }

    private function thenIsSortedDesc(SortedListInterface $list): void
    {
        $previous = null;
        foreach ($list as $value) {
            if ($previous !== null) {
                $this->assertLessThanOrEqual($previous, $value);
            }
            $previous = $value;
        }
    }
}
