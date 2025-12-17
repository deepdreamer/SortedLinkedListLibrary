<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;
use SortedLinkedListLibrary\Exceptions\InvalidTypeException;

class BulkOperationsTest extends TestCase
{
    public function testAddAllWithArray(): void
    {
        $list = SortedList::forInts();
        $list->add(1);

        $list->addAll([5, 2, 8, 3]);

        $this->assertSame([1, 2, 3, 5, 8], $list->toArray());
    }

    public function testAddAllWithEmptyArray(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2);

        $list->addAll([]);

        $this->assertSame([1, 2], $list->toArray());
    }

    public function testAddAllWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $list->addAll([3, 1, 2]);

        $this->assertSame([1, 2, 3], $list->toArray());
    }

    public function testAddAllWithDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1);

        $list->addAll([3, 2, 3, 1, 4]);

        $this->assertSame([1, 1, 2, 3, 3, 4], $list->toArray());
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
    }

    public function testAddAllWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(10);

        $list->addAll([5, 8, 3]);

        $this->assertSame([10, 8, 5, 3], $list->toArray());
    }

    public function testAddAllWithIterable(): void
    {
        $list = SortedList::forInts();
        
        $generator = function (): \Generator {
            yield 5;
            yield 2;
            yield 8;
        };

        $list->addAll($generator());

        $this->assertSame([2, 5, 8], $list->toArray());
    }

    public function testAddAllTypeEnforcement(): void
    {
        $this->expectException(InvalidTypeException::class);

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
    }

    public function testRemoveAllWithEmptyArray(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $removed = $list->removeAll([]);

        $this->assertSame(0, $removed);
        $this->assertSame([1, 2, 3], $list->toArray());
    }

    public function testRemoveAllWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $removed = $list->removeAll([1, 2, 3]);

        $this->assertSame(0, $removed);
        $this->assertTrue($list->isEmpty());
    }

    public function testRemoveAllKeepDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3);

        $removed = $list->removeAll([2, 3]);

        $this->assertSame(2, $removed);
        $this->assertSame([1, 2, 3, 3], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithArray(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3)->add(4)->add(5);

        $removed = $list->removeAllAndEveryOccurrence([2, 4, 6]);

        $this->assertSame(3, $removed);
        $this->assertSame([1, 3, 3, 3, 5], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithEmptyArray(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3);

        $removed = $list->removeAllAndEveryOccurrence([]);

        $this->assertSame(0, $removed);
        $this->assertSame([1, 2, 2, 3], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $removed = $list->removeAllAndEveryOccurrence([1, 2, 3]);

        $this->assertSame(0, $removed);
        $this->assertTrue($list->isEmpty());
    }

    public function testRemoveAllAndEveryOccurrenceWithAllValues(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(1)->add(2)->add(2)->add(3)->add(3);

        $removed = $list->removeAllAndEveryOccurrence([1, 2, 3]);

        $this->assertSame(6, $removed);
        $this->assertTrue($list->isEmpty());
    }

    public function testRemoveAllAndEveryOccurrenceWithNoMatches(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3);

        $removed = $list->removeAllAndEveryOccurrence([10, 20, 30]);

        $this->assertSame(0, $removed);
        $this->assertSame([1, 2, 2, 3], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('banana')->add('cherry')->add('date');

        $removed = $list->removeAllAndEveryOccurrence(['banana', 'date', 'kiwi']);

        $this->assertSame(3, $removed);
        $this->assertSame(['apple', 'cherry'], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(4)->add(3)->add(2)->add(2)->add(1);

        $removed = $list->removeAllAndEveryOccurrence([4, 2]);

        $this->assertSame(4, $removed);
        $this->assertSame([5, 3, 1], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithIterable(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3)->add(4)->add(5);

        $generator = function (): \Generator {
            yield 2;
            yield 3;
        };

        $removed = $list->removeAllAndEveryOccurrence($generator());

        $this->assertSame(5, $removed);
        $this->assertSame([1, 4, 5], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceTypeEnforcement(): void
    {
        $this->expectException(InvalidTypeException::class);

        $list = SortedList::forInts();
        $list->add(1)->add(2);
        $list->removeAllAndEveryOccurrence(['not-an-int']);
    }

    public function testRemoveAllAndEveryOccurrenceWithDuplicateValuesInInput(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3);

        $removed = $list->removeAllAndEveryOccurrence([2, 2, 3, 3]);

        $this->assertSame(5, $removed);
        $this->assertSame([1], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithSingleValueMultipleTimes(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(2)->add(3);

        $removed = $list->removeAllAndEveryOccurrence([2]);

        $this->assertSame(3, $removed);
        $this->assertSame([1, 3], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithConsecutiveDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(5)->add(5)->add(5)->add(5)->add(10);

        $removed = $list->removeAllAndEveryOccurrence([5]);

        $this->assertSame(4, $removed);
        $this->assertSame([1, 10], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithScatteredValues(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(2)->add(4)->add(2)->add(5);

        $removed = $list->removeAllAndEveryOccurrence([2, 4]);

        $this->assertSame(4, $removed);
        $this->assertSame([1, 3, 5], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrencePreservesOtherDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(1)->add(2)->add(2)->add(3)->add(3)->add(3);

        $removed = $list->removeAllAndEveryOccurrence([2]);

        $this->assertSame(2, $removed);
        $this->assertFalse($list->contains(2));
        $this->assertSame([1, 1, 3, 3, 3], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithZeroAndNegative(): void
    {
        $list = SortedList::forInts();
        $list->add(-2)->add(-1)->add(0)->add(0)->add(1)->add(2);

        $removed = $list->removeAllAndEveryOccurrence([0, -1]);

        $this->assertSame(3, $removed);
        $this->assertSame([-2, 1, 2], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithEmptyString(): void
    {
        $list = SortedList::forStrings();
        $list->add('')->add('a')->add('')->add('b')->add('')->add('c');

        $removed = $list->removeAllAndEveryOccurrence(['', 'b']);

        $this->assertSame(4, $removed);
        $this->assertSame(['a', 'c'], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceChaining(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3)->add(4);

        $removed1 = $list->removeAllAndEveryOccurrence([2]);
        $removed2 = $list->removeAllAndEveryOccurrence([3]);

        $this->assertSame(2, $removed1);
        $this->assertSame(3, $removed2);
        $this->assertSame([1, 4], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithLargeList(): void
    {
        $list = SortedList::forInts();
        for ($i = 0; $i < 100; $i++) {
            $list->add($i % 10);
        }

        $removed = $list->removeAllAndEveryOccurrence([5, 7]);

        $this->assertSame(20, $removed);
        $this->assertFalse($list->contains(5));
        $this->assertFalse($list->contains(7));
    }

    public function testRemoveAllAndEveryOccurrenceAfterReverse(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3);

        $list->reverse();
        $removed = $list->removeAllAndEveryOccurrence([3, 2]);

        $this->assertSame(5, $removed);
        $this->assertSame([1], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithHeadRemovalMultipleTimes(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(1)->add(1)->add(2)->add(3);

        $removed = $list->removeAllAndEveryOccurrence([1]);

        $this->assertSame(3, $removed);
        $this->assertFalse($list->contains(1));
        $this->assertSame([2, 3], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithOnlySameValues(): void
    {
        $list = SortedList::forInts();
        $list->add(5)->add(5)->add(5);

        $removed = $list->removeAllAndEveryOccurrence([5]);

        $this->assertSame(3, $removed);
        $this->assertTrue($list->isEmpty());
    }

    public function testRemoveAllAndEveryOccurrenceWithMixedRemovals(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(4)->add(4);

        $removed = $list->removeAllAndEveryOccurrence([2, 5, 4, 6]);

        $this->assertSame(4, $removed); // Only 2 and 4 exist
        $this->assertSame([1, 3], $list->toArray());
    }

    public function testRemoveAllAndEveryOccurrenceWithSingleElementList(): void
    {
        $list = SortedList::forInts();
        $list->add(42);

        $removed = $list->removeAllAndEveryOccurrence([42]);

        $this->assertSame(1, $removed);
        $this->assertTrue($list->isEmpty());
    }

    public function testRemoveAllAndEveryOccurrenceWithSingleElementListNoMatch(): void
    {
        $list = SortedList::forInts();
        $list->add(42);

        $removed = $list->removeAllAndEveryOccurrence([99]);

        $this->assertSame(0, $removed);
        $this->assertSame([42], $list->toArray());
    }

    public function testRemoveAllWithAllValues(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $removed = $list->removeAll([1, 2, 3]);

        $this->assertSame(3, $removed);
        $this->assertTrue($list->isEmpty());
    }

    public function testRemoveAllWithNoMatches(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $removed = $list->removeAll([10, 20, 30]);

        $this->assertSame(0, $removed);
        $this->assertSame([1, 2, 3], $list->toArray());
    }

    public function testRemoveAllWithStrings(): void
    {
        $list = SortedList::forStrings();
        $list->add('apple')->add('banana')->add('cherry')->add('date');

        $removed = $list->removeAll(['banana', 'date', 'kiwi']);

        $this->assertSame(2, $removed);
        $this->assertSame(['apple', 'cherry'], $list->toArray());
    }

    public function testRemoveAllWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $removed = $list->removeAll([4, 2]);

        $this->assertSame(2, $removed);
        $this->assertSame([5, 3, 1], $list->toArray());
    }

    public function testRemoveAllWithIterable(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3)->add(4)->add(5);

        $generator = function (): \Generator {
            yield 2;
            yield 4;
        };

        $removed = $list->removeAll($generator());

        $this->assertSame(2, $removed);
        $this->assertSame([1, 3, 5], $list->toArray());
    }

    public function testRemoveAllTypeEnforcement(): void
    {
        $this->expectException(InvalidTypeException::class);

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
        $this->assertSame([], $list->toArray());
    }

    public function testClearWithEmptyList(): void
    {
        $list = SortedList::forInts();

        $list->clear();

        $this->assertTrue($list->isEmpty());
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
        $this->assertSame([], $list->toArray());
    }

    public function testClearWithDescendingOrder(): void
    {
        $list = SortedList::forInts(SortDirection::DESC);
        $list->add(5)->add(4)->add(3)->add(2)->add(1);

        $list->clear();

        $this->assertTrue($list->isEmpty());
    }

    public function testClearThenAdd(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(3);

        $list->clear();
        $this->assertTrue($list->isEmpty());

        $list->add(10)->add(5)->add(15);

        $this->assertFalse($list->isEmpty());
        $this->assertSame([5, 10, 15], $list->toArray());
    }

    public function testClearWithSingleElement(): void
    {
        $list = SortedList::forInts();
        $list->add(42);

        $list->clear();

        $this->assertTrue($list->isEmpty());
    }

    public function testClearWithDuplicates(): void
    {
        $list = SortedList::forInts();
        $list->add(1)->add(2)->add(2)->add(3)->add(3)->add(3);

        $list->clear();

        $this->assertTrue($list->isEmpty());
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

        $list->add('a')->add('b')->add('c');
        
        $this->assertSame(['c', 'b', 'a'], $list->toArray());
    }
}
