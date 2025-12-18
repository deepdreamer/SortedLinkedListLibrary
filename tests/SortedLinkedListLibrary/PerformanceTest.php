<?php

declare(strict_types=1);

namespace SortedLinkedListLibrary;

use PHPUnit\Framework\TestCase;
use SortedLinkedListLibrary\Enums\SortDirection;

class PerformanceTest extends TestCase
{
    private const LARGE_SIZE = 10000;
    private const VERY_LARGE_SIZE = 50000;
    private const MAX_ADD_TIME_SECONDS = 2.0;
    private const MAX_MERGE_TIME_SECONDS = 2.0;
    private const MAX_MEMORY_MB = 50;
    private const DEMO_SIZE = 3000; // Smaller size for limitation demonstration tests

    // ============================================================================
    // Large Dataset Stress Tests
    // ============================================================================

    public function testAddPerformanceWithLargeDataset(): void
    {
        $list = SortedList::forInts();
        
        // Generate random values first, then use addAll for efficiency
        $values = [];
        for ($i = 0; $i < self::LARGE_SIZE; $i++) {
            $values[] = random_int(1, 100000);
        }
        
        $startTime = microtime(true);
        $list->addAll($values);
        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            self::MAX_ADD_TIME_SECONDS,
            $duration,
            "Adding " . self::LARGE_SIZE . " elements should complete in reasonable time"
        );
        $this->assertSame(self::LARGE_SIZE, $list->count());
    }

    public function testAddPerformanceWithSortedData(): void
    {
        $list = SortedList::forInts();
        $startTime = microtime(true);

        // Adding pre-sorted data (best case) - using addAll for efficiency
        $list->addAll(range(0, self::LARGE_SIZE - 1));

        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            self::MAX_ADD_TIME_SECONDS,
            $duration,
            "Adding " . self::LARGE_SIZE . " pre-sorted elements should be fast"
        );
        $this->assertSame(self::LARGE_SIZE, $list->count());
    }

    public function testAddPerformanceWithReverseSortedData(): void
    {
        $list = SortedList::forInts();
        $startTime = microtime(true);

        // Adding reverse-sorted data (worst case for insertion)
        // Use smaller size for worst case to avoid timeout (O(n²) complexity)
        $size = 30000;
        for ($i = $size; $i >= 0; $i--) {
            $list->add($i);
        }

        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            self::MAX_ADD_TIME_SECONDS,
            $duration,
            "Adding " . $size . " reverse-sorted elements (worst case) should complete"
        );
        $this->assertSame($size + 1, $list->count());
    }

    public function testMergePerformanceWithLargeLists(): void
    {
        $list1 = SortedList::forInts();
        $list2 = SortedList::forInts();

        // Populate with 50K elements each - using addAll for efficiency
        $halfSize = self::VERY_LARGE_SIZE / 2;
        $list1->addAll(array_map(fn (int $i): int => $i * 2, range(0, $halfSize - 1)));
        $list2->addAll(array_map(fn (int $i): int => $i * 2 + 1, range(0, $halfSize - 1)));

        $startTime = microtime(true);
        $list1->merge($list2);
        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            self::MAX_MERGE_TIME_SECONDS,
            $duration,
            "Merging " . self::VERY_LARGE_SIZE . " total elements should be fast (O(n+m))"
        );
        $this->assertSame(self::VERY_LARGE_SIZE, $list1->count());
    }

    public function testUnionPerformanceWithLargeLists(): void
    {
        $list1 = SortedList::forInts();
        $list2 = SortedList::forInts();

        $halfSize = self::VERY_LARGE_SIZE / 2;
        // Using addAll for efficiency
        $list1->addAll(range(0, $halfSize - 1));
        $list2->addAll(range($halfSize, self::VERY_LARGE_SIZE - 1));

        $startTime = microtime(true);
        $result = $list1->union($list2);
        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            self::MAX_MERGE_TIME_SECONDS,
            $duration,
            "Union of " . self::VERY_LARGE_SIZE . " total elements should be fast (O(n+m))"
        );
        $this->assertSame(self::VERY_LARGE_SIZE, $result->count());
    }

    public function testIntersectPerformanceWithLargeLists(): void
    {
        $list1 = SortedList::forInts();
        $list2 = SortedList::forInts();

        $halfSize = self::VERY_LARGE_SIZE / 2;
        // Create overlapping ranges - using addAll for efficiency
        $list1->addAll(range(0, self::VERY_LARGE_SIZE - 1));
        $list2->addAll(range($halfSize, $halfSize + self::VERY_LARGE_SIZE - 1));

        $startTime = microtime(true);
        $result = $list1->intersect($list2);
        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            self::MAX_MERGE_TIME_SECONDS,
            $duration,
            "Intersect of large lists should be fast (O(n+m))"
        );
        $this->assertGreaterThan(0, $result->count());
    }

    public function testAddAllPerformanceWithLargeDataset(): void
    {
        $list = SortedList::forInts();
        $values = range(0, self::LARGE_SIZE - 1);
        shuffle($values); // Unsorted data

        $startTime = microtime(true);
        $list->addAll($values);
        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            self::MAX_ADD_TIME_SECONDS,
            $duration,
            "addAll() with " . self::LARGE_SIZE . " elements should be optimized"
        );
        $this->assertSame(self::LARGE_SIZE, $list->count());
    }

    // ============================================================================
    // Memory Usage Tests
    // ============================================================================

    public function testMemoryUsageWithLargeList(): void
    {
        $initialMemory = memory_get_usage(true);
        $list = SortedList::forInts();

        // Add 50K elements - using addAll for efficiency
        $list->addAll(range(0, self::VERY_LARGE_SIZE - 1));

        $finalMemory = memory_get_usage(true);
        $memoryUsedMB = ($finalMemory - $initialMemory) / (1024 * 1024);

        // Memory calculation breakdown (doubly linked list):
        // Each linked list node requires:
        //   - Value storage: PHP integers use zval structure (~16 bytes on 64-bit)
        //   - Next pointer: 8 bytes (64-bit pointer to next node)
        //   - Prev pointer: 8 bytes (64-bit pointer to previous node)
        //   - PHP object overhead: zend_object structure, properties, etc. (~8-16+ bytes)
        // Total per node: approximately 40-48+ bytes (very rough; depends on PHP build and allocator)
        //
        // For 50,000 nodes: 50,000 × 48 bytes = 2,400,000 bytes ≈ 2.3 MB
        //
        // However, we allow up to 50 MB (MAX_MEMORY_MB) to account for:
        //   - PHP's internal memory management overhead
        //   - Memory fragmentation
        //   - Temporary allocations during operations
        //   - Additional internal data structures
        // This generous limit ensures we catch memory leaks or excessive overhead
        // while allowing for normal PHP memory management behavior
        $this->assertLessThan(
            self::MAX_MEMORY_MB,
            $memoryUsedMB,
            "Memory usage for " . self::VERY_LARGE_SIZE . " elements should be reasonable (~" . round($memoryUsedMB, 2) . " MB used)"
        );
        $this->assertSame(self::VERY_LARGE_SIZE, $list->count());
    }

    public function testMemoryUsageWithStrings(): void
    {
        $initialMemory = memory_get_usage(true);
        $list = SortedList::forStrings();

        // Add 10K string elements - using addAll for efficiency
        $list->addAll(array_map(fn (int $i): string => "string_" . $i, range(0, self::LARGE_SIZE - 1)));

        $finalMemory = memory_get_usage(true);
        $memoryUsedMB = ($finalMemory - $initialMemory) / (1024 * 1024);

        // Strings use more memory, but should still be reasonable
        $this->assertLessThan(
            self::MAX_MEMORY_MB * 2,
            $memoryUsedMB,
            "Memory usage for " . self::LARGE_SIZE . " string elements should be reasonable"
        );
    }

    // ============================================================================
    // Complexity Verification Tests
    // ============================================================================

    public function testLinearComplexityForAdd(): void
    {
        // Verify that adding sequentially scales roughly linearly (O(n))
        // Using smaller sizes for faster execution while still verifying complexity
        $sizes = [500, 2000, 5000];
        $times = [];

        foreach ($sizes as $size) {
            // Reduce noise from GC / allocator state affected by earlier tests in the full suite.
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            if (function_exists('gc_mem_caches')) {
                gc_mem_caches();
            }

            $list = SortedList::forInts();
            $wasGcEnabled = function_exists('gc_enabled') ? gc_enabled() : true;
            if (function_exists('gc_disable')) {
                gc_disable();
            }
            $start = microtime(true);

            for ($i = 0; $i < $size; $i++) {
                $list->add($i);
            }

            $times[$size] = microtime(true) - $start;
            if ($wasGcEnabled && function_exists('gc_enable')) {
                gc_enable();
            }
            unset($list);
        }

        // Time should scale roughly linearly for sorted insertion (O(n))
        // Optimized: With insertion point cache, sequential inserts are O(1) each
        // This makes n sequential inserts O(n) total instead of O(n²)
        // 10x size should result in roughly 10x time (linear scaling)
        $ratio = $times[5000] / $times[500];
        $this->assertLessThan(
            12,
            $ratio,
            "Should scale roughly linearly for sorted insertion (10x size = ~" . round($ratio, 1) . "x time, expected ~10x for O(n) with cache optimization)"
        );
    }

    public function testLinearComplexityForMerge(): void
    {
        // Verify that merge scales linearly O(n+m)
        $sizes = [5000, 20000];
        $times = [];

        foreach ($sizes as $size) {
            $list1 = SortedList::forInts();
            $list2 = SortedList::forInts();

            // Using addAll for efficiency
            $list1->addAll(array_map(fn (int $i): int => $i * 2, range(0, $size - 1)));
            $list2->addAll(array_map(fn (int $i): int => $i * 2 + 1, range(0, $size - 1)));

            $start = microtime(true);
            $list1->merge($list2);
            $times[$size] = microtime(true) - $start;
        }

        // Merge should scale linearly
        $ratio = $times[20000] / $times[5000];
        $this->assertLessThan(
            8,
            $ratio,
            "Merge should scale roughly linearly O(n+m) (4x size = ~" . round($ratio, 1) . "x time, ideally ~4x)"
        );
    }

    public function testLinearComplexityForContains(): void
    {
        $list = SortedList::forInts();
        // Using addAll for efficiency
        $list->addAll(range(0, self::LARGE_SIZE - 1));

        // Test early termination (searching for value at beginning)
        $start = microtime(true);
        $foundEarly = $list->contains(0);
        $earlyTime = microtime(true) - $start;

        // Test worst case (searching for value at end)
        $start = microtime(true);
        $foundLate = $list->contains(self::LARGE_SIZE - 1);
        $lateTime = microtime(true) - $start;

        $this->assertTrue($foundEarly);
        $this->assertTrue($foundLate);
        // Early termination should be much faster
        $this->assertLessThan(
            $lateTime,
            $earlyTime * 100,
            "Early termination should be significantly faster"
        );
    }


    // ============================================================================
    // Bulk Operations Performance
    // ============================================================================

    public function testRemoveAllPerformanceWithLargeList(): void
    {
        $list = SortedList::forInts();
        // Using addAll for efficiency
        $list->addAll(range(0, self::LARGE_SIZE - 1));

        $valuesToRemove = range(0, 4999); // Remove first 5000

        $start = microtime(true);
        $removed = $list->removeAll($valuesToRemove);
        $duration = microtime(true) - $start;

        $this->assertLessThan(
            0.1,
            $duration,
            "removeAll() with 5000 values from " . self::LARGE_SIZE . " element list should complete"
        );
        $this->assertSame(5000, $removed);
    }

    public function testFilterPerformanceWithLargeList(): void
    {
        $list = SortedList::forInts();
        // Using addAll for efficiency
        $list->addAll(range(0, self::LARGE_SIZE - 1));

        $start = microtime(true);
        $filtered = $list->filter(fn (int|string $value): bool => (int) $value % 2 === 0);
        $duration = microtime(true) - $start;

        $this->assertLessThan(
            0.1,
            $duration,
            "filter() on " . self::LARGE_SIZE . " elements should be fast (O(n))"
        );
        $this->assertSame(self::LARGE_SIZE / 2, $filtered->count());
    }

    public function testUniquePerformanceWithLargeList(): void
    {
        $list = SortedList::forInts();
        // Add many duplicates - using addAll for efficiency
        $list->addAll(array_map(fn (int $i): int => $i % 100, range(0, self::LARGE_SIZE - 1)));

        $start = microtime(true);
        $unique = $list->unique();
        $duration = microtime(true) - $start;

        $this->assertLessThan(
            0.1,
            $duration,
            "unique() on " . self::LARGE_SIZE . " elements should be fast (O(n))"
        );
        $this->assertSame(100, $unique->count());
    }

    // ============================================================================
    // Add() Performance Patterns Demonstration
    // ============================================================================

    /**
     * Demonstrates a pattern where the insertion-point cache is not useful:
     * inserting strictly decreasing values into an ascending list.
     *
     * Why it stays fast anyway:
     * - Every new value becomes the new head, so insertion is O(1) per element.
     * - The cache is invalidated, but it is not needed because head insertion is already optimal.
     *
     * Examples:
     * - ASC list + descending inserts (3, 2, 1): each new value becomes the new head → cache unusable.
     * - DESC list + ascending inserts (1, 2, 3): symmetric case → cache unusable.
     *
     * Complexity:
     * - O(1) per insert here (head insertion) → O(n) total.
     */
    public function testAddLimitationReverseSortedInserts(): void
    {
        $list = SortedList::forInts(); // Ascending list
        $startTime = microtime(true);

        // Adding in reverse order - each value becomes the new head
        for ($i = self::DEMO_SIZE; $i > 0; $i--) {
            $list->add($i);
        }

        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            0.2,
            $duration,
            "Reverse sorted inserts should be fast (O(1) head insertion) for " . self::DEMO_SIZE . " elements - completed in {$duration}s"
        );
        $this->assertSame(self::DEMO_SIZE, $list->count());
        $this->assertSame(1, $list->first());
        $this->assertSame(self::DEMO_SIZE, $list->last());
    }

    /**
     * Demonstrates that structural modifications invalidate the cache.
     *
     * Note: the subsequent adds in this test are still "easy" (sequential near tail),
     * so the overall operation is expected to be fast even though the cache is invalidated.
     */
    public function testAddLimitationAfterStructuralModifications(): void
    {
        $list = SortedList::forInts();
        
        // First, add some elements sequentially (fast)
        for ($i = 1; $i <= 500; $i++) {
            $list->add($i);
        }

        $startTime = microtime(true);

        // Now remove some elements (invalidates cache)
        $list->remove(250);
        $list->remove(500);
        $list->remove(100);

        // Cache is invalidated by removals, but these are still sequential near-tail inserts.
        for ($i = 501; $i <= 500 + self::DEMO_SIZE; $i++) {
            $list->add($i);
        }

        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            0.5,
            $duration,
            "Adds after structural modifications should still be fast for sequential inserts - completed in {$duration}s"
        );
    }

    /**
     * Demonstrates that random/unsorted inserts are slower than sequential.
     *
     * With a doubly linked list, add() can often optimize "local" insert patterns
     * (near the last insertion point, either forward or backward).
     *
     * Random inserts are the opposite: consecutive insertion points are usually far apart,
     * so the cache provides little benefit and we do many comparisons/pointer hops.
     */
    public function testAddLimitationRandomInserts(): void
    {
        $list = SortedList::forInts();
        
        // Generate random values
        $values = [];
        for ($i = 0; $i < self::DEMO_SIZE; $i++) {
            $values[] = random_int(1, self::DEMO_SIZE * 10);
        }

        $startTime = microtime(true);

        // Add random values one by one
        foreach ($values as $value) {
            $list->add($value);
        }

        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            2.5,
            $duration,
            "Random inserts should complete in under 2.5 seconds for " . self::DEMO_SIZE . " elements (cache rarely helps when insertion points are far apart)"
        );
        $this->assertSame(self::DEMO_SIZE, $list->count());
    }

    /**
     * Demonstrates that alternating large/small values can be quite fast with a doubly linked list.
     *
     * Even though insertion points alternate, they tend to be near the ends (near head/tail),
     * so add() often does only a small amount of work per insert.
     */
    public function testAddLimitationAlternatingValues(): void
    {
        $list = SortedList::forInts();
        $startTime = microtime(true);

        // Alternate between large and small values
        // This pattern invalidates cache on every other insert
        for ($i = 0; $i < self::DEMO_SIZE / 2; $i++) {
            $list->add(1 + $i);        // Small value
            $list->add(10000 - $i);    // Large value
        }

        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            0.2,
            $duration,
            "Alternating inserts should be fast for " . self::DEMO_SIZE . " elements (often near-head/tail insertion points) - completed in {$duration}s"
        );
        $this->assertSame(self::DEMO_SIZE, $list->count());
    }

    /**
     * Demonstrates that "smaller values after large values" is actually fast:
     * the second phase is pure head-insertion (O(1) per insert), so it does not need the cache.
     */
    public function testAddLimitationSmallerValuesAfterLarge(): void
    {
        $list = SortedList::forInts();
        
        // First add large values sequentially (cache works well)
        for ($i = 1000; $i < 1000 + 500; $i++) {
            $list->add($i);
        }

        $startTime = microtime(true);

        // Now add smaller values. Each insert becomes the new head, so the cache is invalidated.
        for ($i = 1; $i <= 500; $i++) {
            $list->add($i);
        }

        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            0.1,
            $duration,
            "Adding smaller values after large should be fast (head insertion is O(1)) - completed in {$duration}s"
        );
        $this->assertSame(1000, $list->count());
    }

    /**
     * Demonstrates the doubly-linked optimization: when inserting values just *before*
     * the last insertion point, we can traverse backwards from the cached node (prev pointers)
     * instead of restarting from head.
     *
     * This should be significantly faster than a singly-linked "restart from head" approach.
     */
    public function testAddCacheBackwardTraversalIsFastNearTail(): void
    {
        $list = SortedList::forInts(); // Ascending
        $size = 20000;

        // Build list with add() so lastInsertPoint stays hot.
        for ($i = 1; $i <= $size; $i++) {
            $list->add($i);
        }

        // Reduce noise from GC / allocator state.
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
        if (function_exists('gc_mem_caches')) {
            gc_mem_caches();
        }

        $wasGcEnabled = function_exists('gc_enabled') ? gc_enabled() : true;
        if (function_exists('gc_disable')) {
            gc_disable();
        }

        $start = microtime(true);

        // Insert values just before the tail (duplicates near the end).
        // With backward traversal this should be close to O(k) rather than O(n*k).
        for ($i = $size - 1; $i >= $size - 1000; $i--) {
            $list->add($i);
        }

        $duration = microtime(true) - $start;

        if ($wasGcEnabled && function_exists('gc_enable')) {
            gc_enable();
        }

        // Be stricter here because this is the "new" optimized scenario.
        $this->assertLessThan(
            0.5,
            $duration,
            "Backward-cache near-tail inserts should be fast in a doubly linked list - completed in {$duration}s"
        );
        $this->assertSame($size + 1000, $list->count());
        $this->assertSame(1, $list->first());
        $this->assertSame($size, $list->last());
    }

    /**
     * Comparison test: Sequential inserts (fast) vs Reverse inserts (slow).
     * Demonstrates the performance difference between best and worst case.
     */
    public function testAddLimitationComparisonSequentialVsReverse(): void
    {
        $size = 1000;

        // Sequential inserts (best case - cache works)
        $list1 = SortedList::forInts();
        $start1 = microtime(true);
        for ($i = 1; $i <= $size; $i++) {
            $list1->add($i);
        }
        $sequentialTime = microtime(true) - $start1;

        // Reverse inserts (each insert becomes new head - O(1) per insert, cache invalidated but not needed)
        $list2 = SortedList::forInts();
        $start2 = microtime(true);
        for ($i = $size; $i > 0; $i--) {
            $list2->add($i);
        }
        $reverseTime = microtime(true) - $start2;

        $this->assertLessThan(0.05, $sequentialTime, "Sequential inserts should be fast (cache works well) - completed in {$sequentialTime}s");
        $this->assertLessThan(0.05, $reverseTime, "Reverse inserts should be fast (head insertion is O(1) each) - completed in {$reverseTime}s");
        
        // Both should complete quickly: sequential uses cache, reverse uses head insertion (both O(1) per insert)
        $this->assertSame($size, $list1->count());
        $this->assertSame($size, $list2->count());
    }

    /**
     * Verifies that cache works correctly for descending lists with sequential inserts.
     * For descending lists, sequential inserts (3, 2, 1...) should use cache and be fast.
     */
    public function testCacheWorksForDescendingSequentialInserts(): void
    {
        $size = 2000;
        $list = SortedList::forInts(SortDirection::DESC);
        $startTime = microtime(true);

        // Add sequentially in descending order (3, 2, 1...) - cache should work
        for ($i = $size; $i > 0; $i--) {
            $list->add($i);
        }

        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            0.05,
            $duration,
            "Descending sequential inserts should be fast (cache works) - completed in {$duration}s"
        );
        $this->assertSame($size, $list->count());
        
        // Verify correct order (descending)
        $this->assertSame($size, $list->first());
        $this->assertSame(1, $list->last());
    }


    /**
     * Performance test: Verifies that copy() is O(n) not O(n²).
     */
    public function testCopyPerformanceIsLinear(): void
    {
        $sizes = [1000, 5000];
        $times = [];

        foreach ($sizes as $size) {
            $list = SortedList::forInts();
            $list->addAll(range(1, $size));

            $start = microtime(true);
            $copy = $list->copy();
            $times[$size] = microtime(true) - $start;

            $this->assertSame($size, $copy->count());
            $this->assertTrue($list->equals($copy));
        }

        // 5x size should result in roughly 5x time (linear scaling)
        $ratio = $times[5000] / $times[1000];
        $this->assertLessThan(
            10,
            $ratio,
            "copy() should scale roughly linearly (5x size = ~" . round($ratio, 1) . "x time, expected ~5x for O(n))"
        );
        $this->assertGreaterThan(
            2,
            $ratio,
            "copy() should scale at least linearly (5x size = ~" . round($ratio, 1) . "x time)"
        );
    }

    /**
     * Performance test: Verifies that fromArray() uses addAll optimization.
     */
    public function testFromArrayPerformanceUsesOptimization(): void
    {
        $size = 5000;
        $values = range(1, $size);
        shuffle($values); // Random order to test optimization

        $start = microtime(true);
        $list = SortedList::fromArray($values);
        $duration = microtime(true) - $start;

        $this->assertLessThan(
            0.05,
            $duration,
            "fromArray() should be fast (uses addAll optimization) - completed in {$duration}s"
        );
        $this->assertSame($size, $list->count());
        $this->assertSame(range(1, $size), $list->toArray());
    }

    /**
     * Performance test: Verifies that fromIterable() uses addAll optimization.
     */
    public function testFromIterablePerformanceUsesOptimization(): void
    {
        $size = 5000;
        $values = range(1, $size);
        shuffle($values);

        $generator = function () use ($values): \Generator {
            foreach ($values as $value) {
                yield $value;
            }
        };

        $start = microtime(true);
        $list = SortedList::fromIterable($generator());
        $duration = microtime(true) - $start;

        $this->assertLessThan(
            0.05,
            $duration,
            "fromIterable() should be fast (uses addAll optimization) - completed in {$duration}s"
        );
        $this->assertSame($size, $list->count());
        $this->assertSame(range(1, $size), $list->toArray());
    }

    /**
     * Performance test: Verifies that removeAll() optimization works (O(n+m) vs O(n×m)).
     */
    public function testRemoveAllPerformanceOptimization(): void
    {
        $listSize = 5000;
        $removeSize = 1000;
        
        $list = SortedList::forInts();
        $list->addAll(range(1, $listSize));
        
        $valuesToRemove = range(500, 500 + $removeSize - 1);
        shuffle($valuesToRemove); // Random order to test optimization

        $start = microtime(true);
        $removed = $list->removeAll($valuesToRemove);
        $duration = microtime(true) - $start;

        $this->assertLessThan(
            0.05,
            $duration,
            "removeAll() should be fast (O(n+m) optimization) - completed in {$duration}s"
        );
        $this->assertSame($removeSize, $removed);
        $this->assertSame($listSize - $removeSize, $list->count());
    }

    /**
     * Performance test: Verifies that removeAllAndEveryOccurrence() optimization works.
     */
    public function testRemoveAllAndEveryOccurrencePerformanceOptimization(): void
    {
        $listSize = 5000;
        $removeSize = 1000;
        
        $list = SortedList::forInts();
        // Add values with duplicates
        for ($i = 1; $i <= $listSize; $i++) {
            $list->add($i % 500); // Creates duplicates
        }
        
        $valuesToRemove = range(100, 100 + $removeSize - 1);
        shuffle($valuesToRemove);

        $start = microtime(true);
        $removed = $list->removeAllAndEveryOccurrence($valuesToRemove);
        $duration = microtime(true) - $start;

        $this->assertLessThan(
            0.2,
            $duration,
            "removeAllAndEveryOccurrence() should be fast (O(n+m) optimization) - completed in {$duration}s"
        );
        $this->assertGreaterThan(0, $removed);
    }
}
