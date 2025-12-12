<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;

class SortedListTest extends TestCase
{
    public function testIntsAreSortedAscendingByDefault(): void
    {
        $list = SortedList::forInts(); // ascending = true by default?

        $list->add(10)
            ->add(3)
            ->add(7)
            ->add(7)
            ->add(1);

        $this->assertSame([1, 3, 7, 7, 10], $list->toArray());
        $this->assertCount(5, $list);
        $this->assertFalse($list->isEmpty());
    }

    public function testStringsCanBeSortedDescending(): void
    {
        $list = SortedList::forStrings(ascending: false);

        $list->add('banana')
            ->add('apple')
            ->add('kiwi');

        // Descending (strcmp) → "kiwi", "banana", "apple"
        $this->assertSame(['kiwi', 'banana', 'apple'], $list->toArray());
    }

    public function testTypeEnforcementOnAdd(): void
    {
        $this->expectException(\TypeError::class);

        $list = SortedList::forInts();
        $list->add('not-an-int'); // should throw
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

        // Removing non-existing value
        $this->assertFalse($list->remove(999));
    }

    public function testFirstAndLastOnNonEmptyList(): void
    {
        $list = SortedList::forInts();

        $list->add(10)
            ->add(3)
            ->add(7);

        // Sorted: [3, 7, 10]
        $this->assertSame(3, $list->first());
        $this->assertSame(10, $list->last());
    }

    public function testFirstAndLastThrowOnEmptyList(): void
    {
        $list = SortedList::forInts();

        $this->expectException(\UnderflowException::class);
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

        // Assuming jsonSerialize returns an array like:
        // ['values' => ['a', 'b'], 'type' => 'string', 'ascending' => true]
        $serialized = $list->jsonSerialize();

        $this->assertSame(['a', 'b'], $serialized['values']);

        $json = $list->toJson(JSON_PRETTY_PRINT);

        $this->assertIsString($json);
        $this->assertStringContainsString('"values"', $json);
        $this->assertStringContainsString('"a"', $json);
        $this->assertStringContainsString('"b"', $json);
    }

    public function testIsEmptyOnNewList(): void
    {
        $list = SortedList::forInts();

        $this->assertTrue($list->isEmpty());
        $this->assertCount(0, $list);
    }
}
