<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;

class QueryMethodsTest extends TestCase
{
    public function testGetSortOrderAfterReverse(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $this->assertSame(SortDirection::ASC, $list->getSortOrder());

        $list->reverse();

        $this->assertSame(SortDirection::DESC, $list->getSortOrder());
        $this->assertSame([3,2,1], $list->toArray());
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
     * @return array<string, array{
     *     setup: callable(): SortedList,
     *     index: int,
     *     expected: int|string|null
     * }>
     */
    public static function getAtOrNullEdgeCasesProvider(): array
    {
        return [
            'negative index' => [
                'setup' => fn (): SortedList => SortedList::forInts()->add(1)->add(2)->add(3),
                'index' => -1,
                'expected' => null,
            ],
            'index out of range' => [
                'setup' => fn (): SortedList => SortedList::forInts()->add(1)->add(2)->add(3),
                'index' => 10,
                'expected' => null,
            ],
            'empty list' => [
                'setup' => fn (): SortedList => SortedList::forInts(),
                'index' => 0,
                'expected' => null,
            ],
            'with strings' => [
                'setup' => fn (): SortedList => SortedList::forStrings()->add('apple')->add('banana')->add('cherry'),
                'index' => 1,
                'expected' => 'banana',
            ],
            'descending order' => [
                'setup' => fn (): SortedList => SortedList::forInts(SortDirection::DESC)->add(5)->add(4)->add(3)->add(2)->add(1),
                'index' => 2,
                'expected' => 3,
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
}
