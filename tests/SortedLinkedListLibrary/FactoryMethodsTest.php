<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;
use SortedLinkedListLibrary\Exceptions\EmptyIterableParameter;

class FactoryMethodsTest extends TestCase
{
    public function testFromArrayWithInts(): void
    {
        $list = SortedList::fromArray([5, 2, 8, 1, 3]);

        $this->assertSame([1, 2, 3, 5, 8], $list->toArray());
        $this->assertCount(5, $list);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $list = SortedList::fromArray([]);

        $this->assertTrue($list->isEmpty());
        $this->assertCount(0, $list);
    }

    public function testFromArrayWithStrings(): void
    {
        $list = SortedList::fromArray(['zebra', 'apple', 'banana']);

        $this->assertSame(['apple', 'banana', 'zebra'], $list->toArray());
    }

    public function testFromArrayWithDescendingOrder(): void
    {
        $list = SortedList::fromArray([1, 2, 3, 4, 5], SortDirection::DESC);

        $this->assertSame([5, 4, 3, 2, 1], $list->toArray());
    }

    public function testFromArrayWithDuplicates(): void
    {
        $list = SortedList::fromArray([3, 1, 3, 2, 1]);

        $this->assertSame([1, 1, 2, 3, 3], $list->toArray());
    }

    public function testFromArrayWithSingleElement(): void
    {
        $list = SortedList::fromArray([42]);

        $this->assertSame([42], $list->toArray());
        $this->assertCount(1, $list);
    }

    public function testFromIterableWithArray(): void
    {
        $list = SortedList::fromIterable([5, 2, 8, 1, 3]);

        $this->assertSame([1, 2, 3, 5, 8], $list->toArray());
    }

    public function testFromIterableWithGenerator(): void
    {
        $generator = function () {
            yield 5;
            yield 2;
            yield 8;
            yield 1;
        };

        $list = SortedList::fromIterable($generator());

        $this->assertSame([1, 2, 5, 8], $list->toArray());
    }

    public function testFromIterableWithEmptyIterable(): void
    {
        $this->expectException(EmptyIterableParameter::class);

        $list = SortedList::fromIterable([]);
    }

    public function testFromIterableWithStrings(): void
    {
        $list = SortedList::fromIterable(['zebra', 'apple', 'banana']);

        $this->assertSame(['apple', 'banana', 'zebra'], $list->toArray());
    }

    public function testFromIterableWithDescendingOrder(): void
    {
        $list = SortedList::fromIterable([1, 2, 3, 4, 5], SortDirection::DESC);

        $this->assertSame([5, 4, 3, 2, 1], $list->toArray());
    }

    public function testFromIterableWithIterator(): void
    {
        $iterator = new \ArrayIterator([5, 2, 8, 1, 3]);

        $list = SortedList::fromIterable($iterator);

        $this->assertSame([1, 2, 3, 5, 8], $list->toArray());
    }
}
