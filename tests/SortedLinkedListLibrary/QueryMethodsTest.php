<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;
use SortedLinkedListLibrary\Enums\ListType;

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

    public function testGetOrNullWithValidIndex(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $this->assertSame(3, $list->getOrNull(2));
        $this->assertSame(1, $list->getOrNull(0));
        $this->assertSame(5, $list->getOrNull(4));
    }

    public function testGetOrNullWithNegativeIndex(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $this->assertNull($list->getOrNull(-1));
    }

    public function testGetOrNullWithIndexOutOfRange(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $this->assertNull($list->getOrNull(10));
    }

    public function testGetOrNullWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $this->assertNull($list->getOrNull(0));
    }

    public function testGetOrNullWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry');

        $this->assertSame('banana', $list->getOrNull(1));
    }

    public function testGetOrNullWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $this->assertSame(3, $list->getOrNull(2));
    }

    public function testGetOrNullAtBoundary(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $this->assertSame(1, $list->getOrNull(0));
        $this->assertSame(3, $list->getOrNull(2));
        $this->assertNull($list->getOrNull(3));
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

