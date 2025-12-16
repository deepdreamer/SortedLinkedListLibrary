<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;

class BulkOperationsTest extends TestCase
{
    public function testAddAllWithArray(): void
    {
        $list = SortedList::forInts();
        $list->add(1);

        $list->addAll([5, 2, 8, 3]);

        $this->assertSame([1, 2, 3, 5, 8], $list->toArray());
        $this->assertCount(5, $list);
    }

    public function testAddAllWithEmptyArray(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2);

        $list->addAll([]);

        $this->assertSame([1, 2], $list->toArray());
        $this->assertCount(2, $list);
    }

    public function testAddAllWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $list->addAll([3, 1, 2]);

        $this->assertSame([1, 2, 3], $list->toArray());
        $this->assertCount(3, $list);
    }

    public function testAddAllWithDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1);

        $list->addAll([3, 2, 3, 1, 4]);

        $this->assertSame([1, 1, 2, 3, 3, 4], $list->toArray());
        $this->assertCount(6, $list);
    }

    public function testAddAllIsChainable(): void
    {
        $list = SortedList::forInts();

        $result = $list->addAll([1, 3])->addAll([2, 4]);

        $this->assertSame($list, $result);
        $this->assertSame([1, 2, 3, 4], $list->toArray());
    }

    public function testAddAllWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple');

        $list->addAll(['cherry', 'banana', 'date']);

        $this->assertSame(['apple', 'banana', 'cherry', 'date'], $list->toArray());
        $this->assertCount(4, $list);
    }

    public function testAddAllWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(10);

        $list->addAll([5, 8, 3]);

        $this->assertSame([10, 8, 5, 3], $list->toArray());
        $this->assertCount(4, $list);
    }

    public function testAddAllWithIterable(): void
    {
        $list = SortedList::forInts();
        
        $generator = function () {
            yield 5;
            yield 2;
            yield 8;
        };

        $list->addAll($generator());

        $this->assertSame([2, 5, 8], $list->toArray());
        $this->assertCount(3, $list);
    }

    public function testAddAllTypeEnforcement(): void
    {
        $this->expectException(\TypeError::class);

        $list = SortedList::forInts();
        $list->addAll(['not-an-int', 'also-not-int']);
    }

    public function testRemoveAllWithArray(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $removed = $list->removeAll([2, 4, 6]);

        $this->assertSame(2, $removed);
        $this->assertSame([1, 3, 5], $list->toArray());
        $this->assertCount(3, $list);
    }

    public function testRemoveAllWithEmptyArray(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $removed = $list->removeAll([]);

        $this->assertSame(0, $removed);
        $this->assertSame([1, 2, 3], $list->toArray());
        $this->assertCount(3, $list);
    }

    public function testRemoveAllWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $removed = $list->removeAll([1, 2, 3]);

        $this->assertSame(0, $removed);
        $this->assertTrue($list->isEmpty());
        $this->assertCount(0, $list);
    }

    public function testRemoveAllWithDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3);

        $removed = $list->removeAll([2, 3]);

        // removeAll removes first occurrence of each value (remove() only removes first match)
        $this->assertSame(2, $removed);
        $this->assertSame([1, 2, 3, 3], $list->toArray());
        $this->assertCount(4, $list);
    }

    public function testRemoveAllWithAllValues(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $removed = $list->removeAll([1, 2, 3]);

        $this->assertSame(3, $removed);
        $this->assertTrue($list->isEmpty());
        $this->assertCount(0, $list);
    }

    public function testRemoveAllWithNoMatches(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $removed = $list->removeAll([10, 20, 30]);

        $this->assertSame(0, $removed);
        $this->assertSame([1, 2, 3], $list->toArray());
        $this->assertCount(3, $list);
    }

    public function testRemoveAllWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry')->add('date');

        $removed = $list->removeAll(['banana', 'date', 'kiwi']);

        $this->assertSame(2, $removed);
        $this->assertSame(['apple', 'cherry'], $list->toArray());
        $this->assertCount(2, $list);
    }

    public function testRemoveAllWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $removed = $list->removeAll([4, 2]);

        $this->assertSame(2, $removed);
        $this->assertSame([5, 3, 1], $list->toArray());
        $this->assertCount(3, $list);
    }

    public function testRemoveAllWithIterable(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $generator = function () {
            yield 2;
            yield 4;
        };

        $removed = $list->removeAll($generator());

        $this->assertSame(2, $removed);
        $this->assertSame([1, 3, 5], $list->toArray());
        $this->assertCount(3, $list);
    }

    public function testRemoveAllTypeEnforcement(): void
    {
        $this->expectException(\TypeError::class);

        $list = SortedList::forInts();
        $list->add(1)->add(2);
        $list->removeAll(['not-an-int']);
    }

    public function testAddAllThenRemoveAll(): void
    {
        $list = SortedList::forInts();
        $list->add(1);

        $list->addAll([2, 3, 4, 5]);
        $this->assertSame([1, 2, 3, 4, 5], $list->toArray());

        $removed = $list->removeAll([2, 4]);
        $this->assertSame(2, $removed);
        $this->assertSame([1, 3, 5], $list->toArray());
    }

    public function testClearWithNonEmptyList(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $list->clear();

        $this->assertTrue($list->isEmpty());
        $this->assertCount(0, $list);
        $this->assertSame([], $list->toArray());
    }

    public function testClearWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $list->clear();

        $this->assertTrue($list->isEmpty());
        $this->assertCount(0, $list);
        $this->assertSame([], $list->toArray());
    }

    public function testClearIsChainable(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $result = $list->clear();

        $this->assertSame($list, $result);
        $this->assertTrue($list->isEmpty());
    }

    public function testClearWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry');

        $list->clear();

        $this->assertTrue($list->isEmpty());
        $this->assertCount(0, $list);
        $this->assertSame([], $list->toArray());
    }

    public function testClearWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $list->clear();

        $this->assertTrue($list->isEmpty());
        $this->assertCount(0, $list);
    }

    public function testClearThenAdd(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $list->clear();
        $this->assertTrue($list->isEmpty());

        $list->add(10)->add(5)->add(15);

        $this->assertFalse($list->isEmpty());
        $this->assertCount(3, $list);
        $this->assertSame([5, 10, 15], $list->toArray());
    }

    public function testClearWithSingleElement(): void
    {
        $list = SortedList::forInts();
        $list->add(42);

        $list->clear();

        $this->assertTrue($list->isEmpty());
        $this->assertCount(0, $list);
    }

    public function testClearWithDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3);

        $list->clear();

        $this->assertTrue($list->isEmpty());
        $this->assertCount(0, $list);
    }

    public function testClearMultipleTimes(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2);

        $list->clear();
        $this->assertTrue($list->isEmpty());

        $list->add(3);
        $list->clear();
        $this->assertTrue($list->isEmpty());

        $list->clear();
        $this->assertTrue($list->isEmpty());
    }

    public function testClearPreservesTypeAndSortOrder(): void
    {
        $list = SortedList::forStrings(SortDirection::DESC);
        $list->add('z')->add('y')->add('x');

        $list->clear();

        // After clear, type and sort order should be preserved
        $list->add('a')->add('b')->add('c');
        
        // Should still be descending
        $this->assertSame(['c', 'b', 'a'], $list->toArray());
    }
}
