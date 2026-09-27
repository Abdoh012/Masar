<?php

/**
 * MASAR - Backend API Documentation Page
 *
 * Renders the backend root (`/`) documentation dashboard from the statically
 * discovered route catalog. This file performs no routing and executes no
 * endpoint logic; it only turns the discovered route data into HTML.
 */

require_once __DIR__ . '/route_catalog.php';

if (!function_exists('api_docs_render')) {

    function api_docs_render(array $app_config = []): void
    {
        $catalog = route_catalog_discover();

        $stats = $catalog['stats'] ?? [
            'total' => 0,
            'get' => 0,
            'post' => 0,
            'write' => 0,
            'delete' => 0,
            'groups' => 0,
        ];
        $groups = $catalog['groups'] ?? [];

        $environment = (string) ($app_config['environment'] ?? 'development');
        $api_prefix = (string) ($app_config['api_prefix'] ?? '/api/v1');
        $generated_at = (string) ($catalog['generated_at'] ?? date('c'));

        $escape = static function ($value): string {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };

        $access_classes = [
            'Admin' => 'a-admin',
            'Company' => 'a-company',
            'Student' => 'a-student',
            'Authenticated' => 'a-auth',
            'JWT' => 'a-jwt',
            'Public' => 'a-public',
        ];

        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>MASAR Backend API &middot; Documentation</title>
    <style>
        :root {
            --ink: #1B2A4A;
            --ink-soft: #2C3E63;
            --gold: #E8A33D;
            --gold-deep: #8A5A1A;
            --paper: #FAF7F1;
            --paper-2: #F2ECE1;
            --charcoal: #22283B;
            --sage: #6B8F71;
            --sage-deep: #41694A;
            --stone: #D8D2C4;
            --stone-soft: #E7E1D6;
            --clay: #A85B4B;
            --clay-deep: #8A4438;
            --muted: #7A7566;
            --sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            --serif: "Iowan Old Style", "Palatino Linotype", Palatino, Georgia, "Times New Roman", serif;
            --mono: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: var(--paper);
            color: var(--charcoal);
            font-family: var(--sans);
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }
        .hero {
            background:
                radial-gradient(1200px 400px at 85% -10%, rgba(232, 163, 61, 0.18), transparent 60%),
                linear-gradient(140deg, #1B2A4A 0%, #22283B 100%);
            color: var(--paper);
            border-bottom: 3px solid var(--gold);
        }
        .hero-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 46px 20px 54px;
        }
        .brand { display: flex; align-items: center; gap: 16px; }
        .brand-mark {
            display: grid;
            place-items: center;
            width: 54px;
            height: 54px;
            border-radius: 14px;
            background: linear-gradient(150deg, var(--gold), #C9871F);
            color: var(--ink);
            font-family: var(--serif);
            font-weight: 700;
            font-size: 28px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28);
        }
        .eyebrow {
            margin: 0;
            font-size: 12px;
            letter-spacing: 0.34em;
            text-transform: uppercase;
            color: var(--gold);
            font-weight: 700;
        }
        .hero h1 {
            margin: 2px 0 0;
            font-family: var(--serif);
            font-size: 38px;
            line-height: 1.05;
            letter-spacing: -0.01em;
        }
        .subtitle {
            margin: 18px 0 0;
            font-size: 17px;
            color: #EDE7DA;
            font-weight: 600;
        }
        .tagline {
            margin: 6px 0 0;
            font-size: 14px;
            color: #C9CFDC;
        }
        .tagline span { color: var(--gold); padding: 0 6px; }
        .pills { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 15px;
            border-radius: 999px;
            background: rgba(250, 247, 241, 0.08);
            border: 1px solid rgba(250, 247, 241, 0.22);
            color: var(--paper);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .pill strong { color: var(--gold); font-size: 15px; }
        .pill.env { border-color: rgba(232, 163, 61, 0.5); }
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gold);
            box-shadow: 0 0 0 3px rgba(232, 163, 61, 0.22);
        }
        .wrap {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 20px 72px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-top: 26px;
        }
        .stat {
            background: #fff;
            border: 1px solid var(--stone);
            border-radius: 14px;
            padding: 15px 18px;
            box-shadow: 0 6px 18px rgba(27, 42, 74, 0.05);
        }
        .stat-label {
            display: block;
            font-size: 11px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 700;
        }
        .stat-value {
            display: block;
            margin-top: 4px;
            font-family: var(--serif);
            font-size: 30px;
            line-height: 1;
            color: var(--ink);
        }
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 5;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            margin: 26px 0 18px;
            padding: 14px 0;
            background: rgba(250, 247, 241, 0.94);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--stone-soft);
        }
        .search-wrap {
            position: relative;
            flex: 1 1 300px;
            min-width: 220px;
        }
        .search-wrap svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
        }
        #api-search {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border-radius: 12px;
            border: 1px solid var(--stone);
            background: #fff;
            color: var(--charcoal);
            font-size: 14px;
            font-family: var(--sans);
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        #api-search:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(232, 163, 61, 0.22);
        }
        .chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .chip {
            border: 1px solid var(--stone);
            background: #fff;
            color: var(--ink);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.03em;
            padding: 8px 13px;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .chip:hover { border-color: var(--gold); }
        .chip.active {
            background: var(--ink);
            border-color: var(--ink);
            color: var(--paper);
        }
        .shown {
            flex: 0 0 auto;
            font-size: 13px;
            color: var(--muted);
            white-space: nowrap;
        }
        .shown strong { color: var(--ink); }
        .groups {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 16px;
            align-items: start;
        }
        .card {
            background: #fff;
            border: 1px solid var(--stone);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(27, 42, 74, 0.05);
        }
        .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 15px 18px;
            background: var(--paper-2);
            border-bottom: 1px solid var(--stone-soft);
            border-left: 4px solid var(--gold);
        }
        .card-head h2 {
            margin: 0;
            font-size: 14px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--ink);
            font-weight: 800;
        }
        .card-count {
            font-size: 12px;
            font-weight: 800;
            color: var(--ink);
            background: rgba(232, 163, 61, 0.28);
            border-radius: 999px;
            padding: 2px 10px;
            white-space: nowrap;
        }
        .ep-list { list-style: none; margin: 0; padding: 6px 10px 10px; }
        .ep {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 10px;
            padding: 9px 8px;
            border-bottom: 1px dashed var(--stone-soft);
        }
        .ep:last-child { border-bottom: none; }
        .ep[hidden] { display: none; }
        .method {
            min-width: 62px;
            text-align: center;
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.04em;
            padding: 4px 8px;
            border-radius: 7px;
        }
        .m-get { background: rgba(107, 143, 113, 0.16); color: var(--sage-deep); }
        .m-post { background: rgba(232, 163, 61, 0.22); color: var(--gold-deep); }
        .m-put { background: rgba(27, 42, 74, 0.12); color: var(--ink); }
        .m-patch { background: rgba(138, 127, 106, 0.20); color: #6B6252; }
        .m-delete { background: rgba(168, 91, 75, 0.16); color: var(--clay-deep); }
        .path {
            font-family: var(--mono);
            font-size: 12.5px;
            color: var(--charcoal);
            overflow-wrap: anywhere;
        }
        .access {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.03em;
            padding: 3px 9px;
            border-radius: 999px;
            border: 1px solid var(--stone);
            color: var(--muted);
            white-space: nowrap;
        }
        .a-admin { border-color: rgba(168, 91, 75, 0.5); color: var(--clay-deep); }
        .a-company { border-color: rgba(27, 42, 74, 0.4); color: var(--ink); }
        .a-student { border-color: rgba(107, 143, 113, 0.55); color: var(--sage-deep); }
        .a-auth, .a-jwt { border-color: rgba(232, 163, 61, 0.6); color: var(--gold-deep); }
        .a-public { border-color: var(--stone); color: #8A8172; }
        .a-unknown { border-style: dashed; color: #A39B8A; }
        .note {
            margin: 20px 0 0;
            font-size: 12.5px;
            color: var(--muted);
        }
        .empty {
            margin: 26px 0;
            padding: 26px;
            text-align: center;
            background: #fff;
            border: 1px dashed var(--stone);
            border-radius: 16px;
            color: var(--muted);
        }
        .foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-top: 30px;
            padding: 22px 24px;
            background: var(--ink);
            color: var(--paper);
            border-radius: 16px;
        }
        .foot-title {
            margin: 0;
            font-family: var(--serif);
            font-size: 18px;
            color: var(--paper);
        }
        .foot-sub { margin: 2px 0 0; font-size: 13px; color: #C9CFDC; }
        .foot-meta { margin: 0; font-size: 12px; color: #A9B1C2; text-align: right; }
        @media (max-width: 760px) {
            .hero h1 { font-size: 30px; }
            .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .groups { grid-template-columns: 1fr; }
            .foot { flex-direction: column; align-items: flex-start; }
            .foot-meta { text-align: left; }
        }
        @media (max-width: 420px) {
            .ep { grid-template-columns: auto 1fr; }
            .ep .access { grid-column: 2; justify-self: start; }
        }
    </style>
</head>
<body>
    <header class="hero">
        <div class="hero-inner">
            <div class="brand">
                <span class="brand-mark" aria-hidden="true">M</span>
                <div>
                    <p class="eyebrow">MASAR</p>
                    <h1>Backend API</h1>
                </div>
            </div>
            <p class="subtitle">Backend API Documentation</p>
            <p class="tagline">REST API <span>&middot;</span> JSON <span>&middot;</span> JWT Authentication</p>
            <div class="pills">
                <span class="pill"><strong><?= $escape($stats['total']) ?></strong> Endpoints</span>
                <span class="pill"><?= $escape($api_prefix) ?></span>
                <span class="pill">JWT</span>
                <span class="pill env"><span class="dot" aria-hidden="true"></span> ENV: <?= $escape($environment) ?></span>
            </div>
        </div>
    </header>

    <main class="wrap">
        <section class="stats" aria-label="Endpoint statistics">
            <div class="stat">
                <span class="stat-label">Total</span>
                <span class="stat-value"><?= $escape($stats['total']) ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">GET</span>
                <span class="stat-value"><?= $escape($stats['get']) ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">POST</span>
                <span class="stat-value"><?= $escape($stats['post']) ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">Write</span>
                <span class="stat-value"><?= $escape($stats['write']) ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">Delete</span>
                <span class="stat-value"><?= $escape($stats['delete']) ?></span>
            </div>
        </section>

        <section class="toolbar">
            <div class="search-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input id="api-search" type="search" autocomplete="off"
                       placeholder="Search APIs by path, method, or category..."
                       aria-label="Search APIs">
            </div>
            <div class="chips" role="group" aria-label="Filter by HTTP method">
                <button type="button" class="chip active" data-method-filter="ALL">All</button>
                <button type="button" class="chip" data-method-filter="GET">GET</button>
                <button type="button" class="chip" data-method-filter="POST">POST</button>
                <button type="button" class="chip" data-method-filter="PUT">PUT</button>
                <button type="button" class="chip" data-method-filter="PATCH">PATCH</button>
                <button type="button" class="chip" data-method-filter="DELETE">DELETE</button>
            </div>
            <span class="shown">Showing <strong id="shown-count"><?= $escape($stats['total']) ?></strong> endpoints</span>
        </section>

        <?php if ($groups === []): ?>
            <div class="empty">
                <p>No API routes were discovered. Add route declarations under <code>routes/</code> and reload this page.</p>
            </div>
        <?php else: ?>
            <section class="groups" id="groups">
                <?php foreach ($groups as $group): ?>
                    <section class="card" data-group-card data-group-key="<?= $escape($group['key']) ?>">
                        <div class="card-head">
                            <h2><?= $escape($group['label']) ?></h2>
                            <span class="card-count"><?= $escape($group['count']) ?></span>
                        </div>
                        <ul class="ep-list">
                            <?php foreach ($group['routes'] as $route): ?>
                                <?php
                                    $method = strtoupper((string) $route['method']);
                                    $method_class = 'm-' . strtolower($method);
                                    $access = isset($route['access']) ? (string) $route['access'] : '';
                                    $has_access = $access !== '';
                                    $access_class = $access_classes[$access] ?? 'a-unknown';
                                    $haystack = strtolower(implode(' ', array_filter([
                                        $method,
                                        (string) $route['path'],
                                        (string) $group['label'],
                                        $access,
                                    ])));
                                ?>
                                <li class="ep"
                                    data-endpoint
                                    data-method="<?= $escape($method) ?>"
                                    data-search="<?= $escape($haystack) ?>">
                                    <span class="method <?= $escape($method_class) ?>"><?= $escape($method) ?></span>
                                    <code class="path"><?= $escape($route['path']) ?></code>
                                    <?php if ($has_access): ?>
                                        <span class="access <?= $escape($access_class) ?>"><?= $escape($access) ?></span>
                                    <?php else: ?>
                                        <span class="access a-unknown" title="Access is enforced inside the controller or service layer.">&mdash;</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endforeach; ?>
            </section>
            <p class="empty" id="no-results" hidden>No endpoints match your search.</p>
        <?php endif; ?>

        <p class="note">
            Access chips are shown only where the route declaration enforces a role or middleware
            (<strong>Admin</strong>, <strong>Company</strong>, <strong>Student</strong>,
            <strong>JWT</strong>, <strong>Authenticated</strong>). Endpoints marked
            <span class="access a-unknown">&mdash;</span> enforce authorization inside the controller
            or service layer, or are public.
        </p>

        <footer class="foot">
            <div>
                <p class="foot-title">MASAR Backend API</p>
                <p class="foot-sub">Secure REST API for the MASAR platform.</p>
            </div>
            <p class="foot-meta">
                <?= $escape($stats['total']) ?> endpoints &middot; <?= $escape($stats['groups']) ?> groups
                &middot; generated <?= $escape($generated_at) ?>
            </p>
        </footer>
    </main>

    <script>
        (function () {
            var input = document.getElementById('api-search');
            var chips = Array.prototype.slice.call(document.querySelectorAll('[data-method-filter]'));
            var cards = Array.prototype.slice.call(document.querySelectorAll('[data-group-card]'));
            var shown = document.getElementById('shown-count');
            var noResults = document.getElementById('no-results');
            var activeMethod = 'ALL';

            function apply() {
                var query = (input && input.value ? input.value : '').trim().toLowerCase();
                var visible = 0;

                cards.forEach(function (card) {
                    var cardVisible = 0;

                    Array.prototype.forEach.call(card.querySelectorAll('[data-endpoint]'), function (row) {
                        var haystack = row.getAttribute('data-search') || '';
                        var method = row.getAttribute('data-method') || '';
                        var matchesQuery = query === '' || haystack.indexOf(query) !== -1;
                        var matchesMethod = activeMethod === 'ALL' || method === activeMethod;
                        var show = matchesQuery && matchesMethod;

                        row.hidden = !show;

                        if (show) {
                            cardVisible += 1;
                        }
                    });

                    card.hidden = cardVisible === 0;
                    visible += cardVisible;
                });

                if (shown) {
                    shown.textContent = String(visible);
                }
                if (noResults) {
                    noResults.hidden = visible !== 0;
                }
            }

            if (input) {
                input.addEventListener('input', apply);
                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        input.value = '';
                        apply();
                    }
                });
            }

            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    chips.forEach(function (other) {
                        other.classList.remove('active');
                    });
                    chip.classList.add('active');
                    activeMethod = chip.getAttribute('data-method-filter') || 'ALL';
                    apply();
                });
            });
        })();
    </script>
</body>
</html>
        <?php
    }
}
