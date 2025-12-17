#!/bin/bash

echo "Profiling Performance Tests..."
echo "================================"
echo ""

# Get list of test methods
TESTS=$(docker-compose run --rm php-lib vendor/bin/phpunit --testsuite 'Performance Tests' --list-tests 2>/dev/null | grep '::' | sed 's/.*::\(.*\)/\1/')

declare -A times

for test in $TESTS; do
    echo -n "Running $test... "
    START=$(date +%s.%N)
    docker-compose run --rm php-lib vendor/bin/phpunit --filter "$test" --testsuite 'Performance Tests' > /dev/null 2>&1
    EXIT_CODE=$?
    END=$(date +%s.%N)
    DURATION=$(echo "$END - $START" | bc)
    
    if [ $EXIT_CODE -eq 0 ]; then
        echo "✓ (${DURATION}s)"
        times["$test"]=$DURATION
    else
        echo "✗ FAILED (${DURATION}s)"
        times["$test"]=$DURATION
    fi
done

echo ""
echo "================================"
echo "Test Execution Times (sorted):"
echo "================================"

# Sort by time (descending)
for test in "${!times[@]}"; do
    echo "${times[$test]} $test"
done | sort -rn | awk '{printf "%-50s %10.2fs\n", $2, $1}'

