<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;
use SortedLinkedListLibrary\Exceptions\EmptyListException;
use SortedLinkedListLibrary\Exceptions\InvalidTypeException;

class BasicOperationsTest extends TestCase
{
    public function testIntsAreSortedAscendingByDefault(): void
    {
        $list = SortedList::forInts();

        $list->add(10)
            ->add(3)
            ->add(7)
            ->add(7)
            ->add(1);

        $this->assertSame([1, 3, 7, 7, 10], $list->toArray());
        $this->assertFalse($list->isEmpty());
    }

    public function testStringsCanBeSortedDescending(): void
    {
        $list = SortedList::forStrings(SortDirection::DESC);

        $list->add('banana')
            ->add('apple')
            ->add('kiwi');

        $this->assertSame(['kiwi', 'banana', 'apple'], $list->toArray());
    }

    public function testTypeEnforcementOnAdd(): void
    {
        $this->expectException(InvalidTypeException::class);

        $list = SortedList::forInts();
        $list->add('not-an-int');
    }

    public function testContainsAndRemove(): void
    {
        $list = SortedList::forInts();

        $list->add(5)
            ->add(1)
            ->add(10);

        $this->assertTrue($list->contains(5));
        $this->assertFalse($list->contains(7));

        $removed = $list->remove(5);
        $this->assertTrue($removed);
        $this->assertFalse($list->contains(5));
        $this->assertSame([1, 10], $list->toArray());

        $this->assertFalse($list->remove(999));
    }

    public function testFirstAndLastOnNonEmptyList(): void
    {
        $list = SortedList::forInts();

        $list->add(10)
            ->add(3)
            ->add(7);

        $this->assertSame(3, $list->first());
        $this->assertSame(10, $list->last());
    }

    public function testFirstAndLastThrowOnEmptyList(): void
    {
        $list = SortedList::forInts();

        $this->expectException(EmptyListException::class);
        $list->first();
    }

    public function testIterationYieldsSortedValues(): void
    {
        $list = SortedList::forInts();
        $list->add(5)->add(2)->add(9);

        $collected = [];
        foreach ($list as $value) {
            $collected[] = $value;
        }

        $this->assertSame([2, 5, 9], $collected);
    }

    public function testJsonSerializeAndToJson(): void
    {
        $list = SortedList::forStrings();
        $list->add('b')->add('a');

        $serialized = $list->jsonSerialize();
        $this->assertIsArray($serialized);

        $json = $list->toJson(JSON_PRETTY_PRINT);
        $this->assertIsString($json);
        $this->assertNotFalse($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
    }

    public function testIsEmptyOnNewList(): void
    {
        $list = SortedList::forInts();

        $this->assertTrue($list->isEmpty());
    }
}
