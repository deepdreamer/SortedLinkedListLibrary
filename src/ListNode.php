<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

class ListNode
{
    public int|string $value;
    public ?ListNode $next = null;

    public function __construct(int|string $value, ?ListNode $next = null)
    {
        $this->value = $value;
        $this->next = $next;
    }
}
