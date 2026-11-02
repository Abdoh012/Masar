<?php

/**
 * MASAR - Co-Loaded Function Name Check
 *
 * The codebase is procedural: every file declares plain top-level functions
 * in one shared namespace, so two files declaring the same name is a FATAL
 * "Cannot redeclare function" the moment both are loaded into one process.
 *
 * That failure is expensive to diagnose, because it does not surface at the
 * declaration site. It takes down every endpoint that transitively requires the
 * second file, so the symptom is a wall of unrelated 500s in OTHER regression
 * suites. (This really happened here: a student education controller handler
 * and a student education service function both wanted to be
 * student_education_update(), and the result was 165 failures in the dashboard
 * suite plus failures across every applications suite before the cause was
 * found.)
 *
 * A naive "any name declared twice" scan is useless in this tree, which
 * legitimately contains:
 *   - never-required legacy files under app/core/helpers/;
 *   - admin controller/service pairs the admin routes do not wire together;
 *   - test drivers that each run as their own process and are never included
 *     together (they all define check(), run_api(), run_scenario()...).
 * None of those can collide at runtime.
 *
 * So this resolves the ACTUAL require graph from the real entry points
 * (public/index.php plus every routes/*.php, which is also how the
 * tests/*_case.php shims boot) and reports a duplicate only when the two files
 * can genuinely land in the same process.
 *
 * The traversal is an iterative worklist, not recursion: the graph HAS cycles
 * (for example a middleware requires auth.php, which requires request.php,
 * which is also required by the middleware's own caller), and a recursive
 * cycle guard silently truncates whole subtrees - which is exactly what a first
 * attempt at this check did, hiding a real collision.
 *
 * Usage: php tests/co_loaded_function_names.php
 * Exits 0 when nothing can collide, 1 otherwise.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE', dirname(__DIR__) . '/');

$scanRoots = ['app', 'routes', 'cron', 'public'];

/** Normalizes to a forward-slash real path, tolerating not-yet-created files. */
function masar_path(string $path): string
{
    $real = @realpath($path);

    if ($real !== false) {
        return str_replace('\\', '/', $real);
    }

    $parent = @realpath(dirname($path));
    $base   = $parent === false ? dirname($path) : $parent;

    return rtrim(str_replace('\\', '/', $base), '/') . '/' . basename($path);
}

/*
|--------------------------------------------------------------------------
| Discover Files
|--------------------------------------------------------------------------
*/

$files = [];

foreach ($scanRoots as $root) {
    $path = BASE . $root;

    if (!is_dir($path)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $entry) {
        if ($entry->isFile() && strtolower($entry->getExtension()) === 'php') {
            $files[masar_path($entry->getPathname())] = true;
        }
    }
}

$files = array_keys($files);
sort($files);

/*
|--------------------------------------------------------------------------
| Pass 1 - Declared Names
|--------------------------------------------------------------------------
|
| Only a `function` at the start of a line counts, which excludes closures
| assigned to variables and methods inside class bodies.
|
*/

/** @var array<string, string[]> $owners function name => declaring files */
$owners = [];

/** @var array<string, list<string>> $requires file => required files */
$requires = [];

foreach ($files as $file) {
    $source = (string) @file_get_contents($file);

    if ($source === '') {
        continue;
    }

    /*
     * The require graph is collected in the same pass so the file is read once.
     * Only literal __DIR__ concatenations are resolved. A dynamic require can
     * only make this check UNDER-report a duplicate, never invent one, so
     * skipping it stays safe.
     */
    if (preg_match_all('/require(?:_once)?\s+([^;]+);/', $source, $requires_match)) {
        foreach ($requires_match[1] as $expression) {
            if (preg_match("/^__DIR__\s*\.\s*'([^']+)'\s*$/", trim($expression), $parts)) {
                $requires[$file][] = masar_path(dirname($file) . '/' . $parts[1]);
            }
        }
    }

    if (preg_match_all('/^\s*function\s+([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)\s*\(/m', $source, $functions_match)) {
        foreach ($functions_match[1] as $name) {
            $owners[$name][] = $file;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Pass 2 - Reachability (iterative worklist, cycle-safe)
|--------------------------------------------------------------------------
|
| public/index.php is the real front controller. The routes/*.php files are
| added because every tests/*_case.php shim requires a route file directly, so
| a collision is reachable from the test harness as well as from HTTP.
|
*/

$entries = [masar_path(BASE . 'public/index.php')];

foreach (glob(BASE . 'routes/*.php') ?: [] as $route) {
    $entries[] = masar_path($route);
}

/** @var array<string, bool> $reachable */
$reachable = [];
$queue     = [];

foreach ($entries as $entry) {
    if (is_file($entry) && !isset($reachable[$entry])) {
        $reachable[$entry] = true;
        $queue[]           = $entry;
    }
}

while ($queue) {
    $current = array_pop($queue);

    foreach ($requires[$current] ?? [] as $target) {
        if (isset($reachable[$target])) {
            continue;
        }

        $reachable[$target] = true;
        $queue[]            = $target;
    }
}

/*
|--------------------------------------------------------------------------
| Pass 3 - Report
|--------------------------------------------------------------------------
|
| Two loadable files collide when they appear together in the closure of SOME
| single entry point, because that is what puts them in the same process.
|
| The rule is deliberately not "one requires the other": two sibling files that
| a common parent requires (a controller pulling in two services, say) collide
| just as surely as a direct parent/child pair, and testing only the direct
| relationship misses exactly that shape - which is how the real
| student_education_update() collision slipped through a first version of this
 * check.
|
| Two route files that are each separately reachable are NOT a collision: a
| request loads one route, never both.
|
*/

$entries = array_values(array_filter($entries, 'is_file'));

/** @var array<int, array<string, bool>> $entry_closures */
$entry_closures = [];

foreach ($entries as $entry) {
    $closure = masar_closure($requires, $entry);
    $entry_closures[] = $closure;
}

$collisions = [];

foreach ($owners as $name => $declaring) {
    $declaring = array_values(array_unique($declaring));

    if (count($declaring) < 2) {
        continue;
    }

    for ($i = 0; $i < count($declaring); $i++) {
        for ($j = $i + 1; $j < count($declaring); $j++) {
            $a = $declaring[$i];
            $b = $declaring[$j];

            foreach ($entry_closures as $closure) {
                if (!isset($closure[$a], $closure[$b])) {
                    continue;
                }

                $collisions[$name][] = [ $a, $b ];
                continue 2;
            }
        }
    }
}

/** Everything $from transitively requires, itself included (cycle-safe). */
function masar_closure(array $requires, string $from): array
{
    $seen  = [ $from => true ];
    $queue = [ $from ];

    while ($queue) {
        $current = array_pop($queue);

        foreach ($requires[$current] ?? [] as $target) {
            if (isset($seen[$target])) {
                continue;
            }

            $seen[$target] = true;
            $queue[]       = $target;
        }
    }

    return $seen;
}

ksort($collisions);

echo 'scanned ' . count($files) . ' PHP files, '
    . count($reachable) . ' reachable from the entry points, '
    . count($owners) . " distinct function names\n";

if (empty($collisions)) {
    echo "\n== Result ==\n";
    echo "NO CO-LOADED DUPLICATE FUNCTION NAMES\n";
    exit(0);
}

echo "\n== Duplicated function names that can be loaded together ==\n";

foreach ($collisions as $name => $pairs) {
    foreach ($pairs as [$a, $b]) {
        echo "\n  {$name}()\n";
        echo '    ' . str_replace('\\', '/', str_replace(BASE, '', $a)) . "\n";
        echo '    ' . str_replace('\\', '/', str_replace(BASE, '', $b)) . "\n";
    }
}

echo "\n== Result ==\n";
echo 'FAILURES: ' . count($collisions) . " co-loaded duplicate function name(s)\n";
echo "Each of these is a fatal 'Cannot redeclare function' in a single request.\n";

exit(1);
