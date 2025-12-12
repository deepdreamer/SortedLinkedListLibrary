<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

class SortedList implements SortedListInterface
{
    public const TYPE_INT = 'int';
    public const TYPE_STRING = 'string';

    private ?ListNode $head = null;

    /** @var non-negative-int $count */
    private int $count = 0;

    /**
     * @param 'int'|'string' $type
     * @param bool $ascending true = ascending, false = descending
     */
    public function __construct(
        private string $type,
        private bool $ascending = true,
    ) {
        if (!\in_array($type, [self::TYPE_INT, self::TYPE_STRING], true)) {
            throw new \InvalidArgumentException('Type must be "int" or "string".');
        }
    }

    public static function forInts(bool $ascending = true): self
    {
        return new self(self::TYPE_INT, $ascending);
    }

    public static function forStrings(bool $ascending = true): self
    {
        return new self(self::TYPE_STRING, $ascending);
    }

    /**
     * @return non-negative-int
     */
    public function count(): int
    {
        return $this->count;
    }

    public function isEmpty(): bool
    {
        return $this->head === null;
    }

    public function get(int $index): int|string
    {
        if ($index < 0 || $index >= $this->count) {
            throw new \OutOfRangeException("Index $index out of range");
        }

        /** @var ListNode $current */
        $current = $this->head;
        for ($i = 0; $i < $index; $i++) {
            /** @var ListNode $current */
            $current = $current->next;
        }

        return $current->value;
    }


    /**
     * Insert while keeping the list sorted.
     */
    public function add(int|string $value): self
    {
        $this->assertType($value);

        $newNode = new ListNode($value);

        // Empty list → new head
        if ($this->head === null) {
            $this->head = $newNode;
            $this->count++;
            return $this;
        }

        // Insert at head?
        if ($this->shouldComeBefore($value, $this->head->value)) {
            $newNode->next = $this->head;
            $this->head = $newNode;
            $this->count++;
            return $this;
        }

        // Insert somewhere in the middle or at the end
        $prev = $this->head;
        $current = $this->head->next;

        while ($current !== null && !$this->shouldComeBefore($value, $current->value)) {
            $prev = $current;
            $current = $current->next;
        }

        $prev->next = $newNode;
        $newNode->next = $current;
        $this->count++;

        return $this;
    }

    public function remove(int|string $value): bool
    {
        $this->assertType($value);

        if ($this->head === null) {
            return false;
        }

        if ($this->head->value === $value) {
            $this->head = $this->head->next;
            if ($this->count > 0) {
                $this->count--;
            }
            return true;
        }

        $prev = $this->head;
        $current = $this->head->next;

        while ($current !== null) {
            if ($current->value === $value) {
                $prev->next = $current->next;
                if ($this->count > 0) {
                    $this->count--;
                }
                return true;
            }
            $prev = $current;
            $current = $current->next;
        }

        return false;
    }

    public function getIterator(): \Traversable
    {
        $current = $this->head;

        while ($current !== null) {
            yield $current->value;
            $current = $current->next;
        }
    }

    public function contains(int|string $value): bool
    {
        $this->assertType($value);

        $current = $this->head;

        while ($current !== null) {
            $cmp = $this->compare($value, $current->value);

            if ($cmp === 0) {
                return true; // exact match
            }

            // Early-stop for ASC
            if ($this->ascending && $cmp < 0) {
                return false;
            }

            // Early-stop for DESC
            if (!$this->ascending && $cmp > 0) {
                return false;
            }

            $current = $current->next;
        }

        return false;
    }

    public function first(): int|string
    {
        if ($this->head === null) {
            throw new \UnderflowException('List is empty.');
        }

        return $this->head->value;
    }

    public function firstOrNull(): int|string|null
    {
        try {
            return $this->first();
        } catch (\UnderflowException) {
            return null;
        }
    }

    public function last(): int|string
    {
        if ($this->head === null) {
            throw new \UnderflowException('List is empty.');
        }

        $current = $this->head;
        while ($current->next !== null) {
            $current = $current->next;
        }

        return $current->value;
    }

    public function lastOrNull(): int|string|null
    {
        try {
            return $this->last();
        } catch (\UnderflowException) {
            return null;
        }
    }

    /**
     * @return array<int, int|string>
     */
    public function toArray(): array
    {
        $result = [];
        $current = $this->head;

        while ($current !== null) {
            $result[] = $current->value;
            $current = $current->next;
        }

        return $result;
    }

    /**
     * @return array{'type': 'int'|'string', 'ascending': bool, 'count': non-negative-int, 'values': array<int, int|string>}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,        // "int" or "string"
            'ascending' => $this->ascending,   // true / false
            'count' => $this->count,       // number of elements
            'values' => $this->toArray(),   // sorted values
        ];
    }

    /**
     * @param positive-int $depth
     */
    public function toJson(int $options = 0, int $depth = 512): string|false
    {
        return json_encode($this->jsonSerialize(), $options, $depth);
    }

    private function assertType(int|string $value): void
    {
        if ($this->type === self::TYPE_INT && !\is_int($value)) {
            throw new \TypeError('This list only accepts int.');
        }
        if ($this->type === self::TYPE_STRING && !\is_string($value)) {
            throw new \TypeError('This list only accepts string.');
        }
    }

    /**
     * Comparison helper respecting type + direction.
     */
    private function compare(int|string $a, int|string $b): int
    {
        if ($this->type === self::TYPE_INT) {
            /** @var int $a */
            /** @var int $b */
            return $a <=> $b;
        }

        /** @var string $a */
        /** @var string $b */
        return strcmp($a, $b);
    }

    /**
     * Should $a come before $b in the list?
     */
    private function shouldComeBefore(int|string $a, int|string $b): bool
    {
        $cmp = $this->compare($a, $b);

        return $this->ascending
            ? $cmp <= 0   // ascending: a <= b
            : $cmp >= 0;  // descending: a >= b
    }
}
