<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

class ListNode
{
    public int|string $value;
    public ?ListNode $next = null;
    public ?ListNode $prev = null;

    public function __construct(int|string $value, ?ListNode $next = null, ?ListNode $prev = null)
    {
        $this->value = $value;
        $this->next = $next;
        $this->prev = $prev;
    }
}
