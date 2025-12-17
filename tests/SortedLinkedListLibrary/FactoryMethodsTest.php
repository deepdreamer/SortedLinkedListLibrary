<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;
use SortedLinkedListLibrary\Exceptions\EmptyIterableParameter;

class FactoryMethodsTest extends TestCase
{
    /**
     * @return array<string, array{
     *     input: array<int|string>,
     *     expected: array<int|string>
     * }>
     */
    public static function fromArrayProvider(): array
    {
        return [
            'ints' => [
                'input' => [5, 2, 8, 1, 3],
                'expected' => [1, 2, 3, 5, 8],
            ],
            'duplicate ints' => [
                'input' => [3, 1, 3, 2, 1],
                'expected' => [1, 1, 2, 3, 3],
            ],
            'single element' => [
                'input' => [42],
                'expected' => [42],
            ],
            'empty array' => [
                'input' => [],
                'expected' => [],
            ],
            'strings' => [
                'input' => ['zebra', 'apple', 'banana'],
                'expected' => ['apple', 'banana', 'zebra'],
            ],
        ];
    }

    /**
     * @param array<int|string> $input
     * @param array<int|string> $expected
     */
    #[DataProvider('fromArrayProvider')]
    public function testFromArray(array $input, array $expected): void
    {
        $list = SortedList::fromArray($input);

        $this->assertSame($expected, $list->toArray());
    }

    public function testFromArrayWithDescendingOrder(): void
    {
        $list = SortedList::fromArray([1, 2, 3, 4, 5], SortDirection::DESC);

        $this->assertSame([5, 4, 3, 2, 1], $list->toArray());
    }

    public function testFromIterableWithArray(): void
    {
        $list = SortedList::fromIterable([5, 2, 8, 1, 3]);

        $this->assertSame([1, 2, 3, 5, 8], $list->toArray());
    }

    public function testFromIterableWithGenerator(): void
    {
        $generator = function (): \Generator {
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

        SortedList::fromIterable([]);
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
