#!/bin/bash

echo "Profiling Performance Tests..."
echo "================================"
echo ""

set -euo pipefail

# Prefer Docker Compose v2 syntax (docker compose)
DC="docker compose"

# Run the whole Performance Tests suite once, emit JUnit XML, then parse per-test times.
# This measures the actual PHPUnit-reported time for each testcase (no container-start noise),
# and avoids Python by using PHP to parse the XML.
JUNIT_PATH="/app/.junit-performance.xml"

echo "Running Performance Tests once (JUnit timing enabled)..."
$DC run --rm php-lib sh -lc "vendor/bin/phpunit -c phpunit.xml --testsuite 'Performance Tests' --log-junit '${JUNIT_PATH}' >/dev/null"

echo ""
echo "================================"
echo "Test Execution Times (sorted):"
echo "================================"

$DC run --rm php-lib sh -lc "php -r '
\$file = \"${JUNIT_PATH}\";
\$xml = simplexml_load_file(\$file);
if (!\$xml) { fwrite(STDERR, \"Failed to parse JUnit XML\\n\"); exit(1); }
\$cases = \$xml->xpath(\"//testcase\") ?: [];
\$rows = [];
foreach (\$cases as \$tc) {
    \$time = (float) (\$tc[\"time\"] ?? 0);
    \$name = (string) (\$tc[\"name\"] ?? \"\");
    \$class = (string) (\$tc[\"classname\"] ?? \"\");
    \$rows[] = [\$time, \$class, \$name];
}
usort(\$rows, fn(\$a, \$b) => \$b[0] <=> \$a[0]);
foreach (\$rows as [\$t, \$cls, \$name]) {
    \$label = \$cls !== \"\" ? \"{\$cls}::{\$name}\" : \$name;
    printf(\"%-70s %10.2f ms\\n\", \$label, \$t * 1000);
}
'"

