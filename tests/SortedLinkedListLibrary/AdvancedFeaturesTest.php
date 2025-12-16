<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;
use SortedLinkedListLibrary\Enums\ListType;

class AdvancedFeaturesTest extends TestCase
{
    public function testRemoveAtWithValidIndex(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $removed = $list->removeAt(2);

        $this->assertSame(3, $removed);
        $this->assertSame([1, 2, 4, 5], $list->toArray());
        $this->assertCount(4, $list);
    }

    public function testRemoveAtWithFirstIndex(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $removed = $list->removeAt(0);

        $this->assertSame(1, $removed);
        $this->assertSame([2, 3], $list->toArray());
    }

    public function testRemoveAtWithLastIndex(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $removed = $list->removeAt(2);

        $this->assertSame(3, $removed);
        $this->assertSame([1, 2], $list->toArray());
    }

    public function testRemoveAtThrowsOnNegativeIndex(): void
    {
        $this->expectException(\OutOfRangeException::class);

        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);
        $list->removeAt(-1);
    }

    public function testRemoveAtThrowsOnIndexOutOfRange(): void
    {
        $this->expectException(\OutOfRangeException::class);

        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);
        $list->removeAt(10);
    }

    public function testRemoveAtWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry');

        $removed = $list->removeAt(1);

        $this->assertSame('banana', $removed);
        $this->assertSame(['apple', 'cherry'], $list->toArray());
    }

    public function testRemoveFirstWithDefaultCount(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $removed = $list->removeFirst();

        $this->assertSame([1], $removed);
        $this->assertSame([2, 3, 4, 5], $list->toArray());
        $this->assertCount(4, $list);
    }

    public function testRemoveFirstWithCustomCount(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $removed = $list->removeFirst(3);

        $this->assertSame([1, 2, 3], $removed);
        $this->assertSame([4, 5], $list->toArray());
        $this->assertCount(2, $list);
    }

    public function testRemoveFirstWithCountGreaterThanList(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $removed = $list->removeFirst(10);

        $this->assertSame([1, 2, 3], $removed);
        $this->assertTrue($list->isEmpty());
    }

    public function testRemoveFirstWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $removed = $list->removeFirst(5);

        $this->assertSame([], $removed);
        $this->assertTrue($list->isEmpty());
    }

    public function testRemoveFirstWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry');

        $removed = $list->removeFirst(2);

        $this->assertSame(['apple', 'banana'], $removed);
        $this->assertSame(['cherry'], $list->toArray());
    }

    public function testRemoveLastWithDefaultCount(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $removed = $list->removeLast();

        $this->assertSame([5], $removed);
        $this->assertSame([1, 2, 3, 4], $list->toArray());
        $this->assertCount(4, $list);
    }

    public function testRemoveLastWithCustomCount(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $removed = $list->removeLast(3);

        // removeLast returns values in reverse order (as removed from end)
        $this->assertSame([3, 4, 5], $removed);
        $this->assertSame([1, 2], $list->toArray());
        $this->assertCount(2, $list);
    }

    public function testRemoveLastWithCountGreaterThanList(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $removed = $list->removeLast(10);

        // removeLast returns values in reverse order
        $this->assertSame([1, 2, 3], $removed);
        $this->assertTrue($list->isEmpty());
    }

    public function testRemoveLastWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $removed = $list->removeLast(5);

        $this->assertSame([], $removed);
        $this->assertTrue($list->isEmpty());
    }

    public function testRemoveLastWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry');

        $removed = $list->removeLast(2);

        // removeLast returns values in reverse order
        $this->assertSame(['banana', 'cherry'], $removed);
        $this->assertSame(['apple'], $list->toArray());
    }

    public function testRemoveLastWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $removed = $list->removeLast(2);

        // In descending order, "last" means smallest values, returned in reverse order
        $this->assertSame([2, 1], $removed);
        $this->assertSame([5, 4, 3], $list->toArray());
    }
}

