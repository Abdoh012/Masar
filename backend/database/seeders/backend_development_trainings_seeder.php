<?php

/**
 * MASAR - Backend Development Training Data Seeder
 *
 * Adds realistic, published Backend Development (specialization_id = 199)
 * training listings so the unified GET /api/v1/search/trainings endpoint has
 * real data to search, filter, sort and paginate.
 *
 * - Additive and idempotent: it only INSERTs. Records whose (title,
 *   company_id) pair already exists are skipped, so re-running never creates
 *   duplicates and existing rows are never touched.
 * - Uses ONLY existing rows from companies (approved, software-relevant) and
 *   skills (backend/CS skill ids resolved by name).
 * - Every new row has specialization_id = 199 (Backend Development).
 * - Mixes training_type (shadowing / hands_on / project_based), mode
 *   (onsite / remote / hybrid), paid/free with the project business rules for
 *   paid trainings (compensation_amount, trial_period_days) and varied
 *   capacities / dates so search filters and dynamic duration behave naturally.
 * - Dates always honour the application-window rules: published_at < starts_at,
 *   application_deadline = starts_at (exact) and starts_at < ends_at. A mix of
 *   currently-open, upcoming and already-started listings is produced
 *   (same id-mod bucket pattern as normalize_training_dates_seeder.php).
 *
 * Run from the backend root:
 *     php database/seeders/backend_development_trainings_seeder.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

const BD_BACKEND_SPECIALIZATION_ID = 199;

function bd_datetime(int $dayOffset, string $time = '10:00:00'): string
{
    return date('Y-m-d H:i:s', strtotime(($dayOffset > 0 ? '+' : ($dayOffset < 0 ? '-' : '')) . abs($dayOffset) . ' day ' . $time));
}

/**
 * [company_id, title, description, training_type, mode, is_paid,
 *  compensation_amount, trial_period_days, capacity, skills[]]
 */
function bd_trainings(): array
{
    return [
        [
            100144, 'Laravel REST API Mastery',
            'A project-based program where trainees build a production-grade Laravel REST API from scratch: migrations, Eloquent relationships, form requests, API resources and optimized JSON responses. The training covers authentication with Laravel Sanctum, rate limiting, exception handling and writing PHPUnit feature tests so every endpoint is verified before shipping. Trainees deploy the finished API with Docker and document it, mirroring how NileTech ships backend services for its logistics and fintech clients.',
            'project_based', 'onsite', true, 3000.00, 14, 6,
            ['PHP', 'Laravel', 'MySQL', 'Redis', 'Git'],
        ],
        [
            100144, 'PHP Performance & Caching Essentials',
            'A hands-on training focused on making slow PHP backends fast. Trainees profile real request bottlenecks with Xdebug, apply opcode caching, tune OPcache and MySQL query plans with EXPLAIN, and introduce Redis-backed application caching with automatic invalidation. Each session includes a measurable performance benchmark before and after the change, so participants leave with a repeatable optimization workflow for Laravel and plain PHP applications.',
            'hands_on', 'remote', true, 2200.00, 10, 8,
            ['PHP', 'MySQL', 'Redis', 'Linux'],
        ],
        [
            100144, 'Docker & Container Orchestration',
            'A project-based training on containerized backend delivery. Trainees Dockerize a Laravel application, split it into multi-stage images, wire it up with docker-compose (nginx, PHP-FPM, MySQL, Redis) and then practice orchestration concepts: health checks, dependency ordering, volume management and rolling updates. The final deliverable is a repeatable container stack that starts, upgrades and rolls back reliably.',
            'project_based', 'hybrid', true, 2600.00, 14, 5,
            ['Docker', 'Linux', 'CI/CD', 'Git'],
        ],
        [
            100144, 'MySQL Query Optimization', 
            'A shadowing training inside NileTech\'s data layer, where trainees observe senior engineers tuning the queries behind the company\'s logistics dashboards. Topics include index design, composite indexes, covering indexes, JOIN strategies, EXPLAIN analysis and query rewriting. Trainees review real slow-query logs and propose index/query changes that are then applied and measured against production-shaped data.',
            'shadowing', 'onsite', false, null, null, 4,
            ['MySQL', 'SQL', 'Linux'],
        ],
        [
            100144, 'Secure API Authentication Patterns',
            'A hands-on hybrid workshop covering the authentication and authorization decisions behind customer-facing APIs. Trainees implement registration, login, token refresh and password reset flows in PHP/Laravel, compare session-based and stateless JWT approaches, enforce role-based access control, and protect against common API security issues such as mass assignment, token leakage and brute-force login attempts. Code review of the finished endpoints closes the program.',
            'hands_on', 'hybrid', false, null, null, 7,
            ['PHP', 'Laravel', 'MySQL', 'Docker'],
        ],
        [
            100144, 'CI/CD Pipeline Automation',
            'A project-based remote training on end-to-end delivery automation. Trainees write GitHub Actions workflows that run PHP_CodeSniffer, static analysis and PHPUnit, build Docker images, push them to a registry and deploy to a staging server, then promote the same artifact to production. The training stresses pipeline reliability: fast feedback, cached dependencies, secret handling and rollback-ready releases.',
            'project_based', 'remote', true, 2400.00, 7, 6,
            ['CI/CD', 'GitHub', 'Docker', 'Linux'],
        ],
        [
            100144, 'Backend Unit Testing Essentials',
            'An onsite hands-on training that turns trainees into confident test writers. Starting from pure unit tests in PHPUnit, it progresses through database-backed tests, Laravel factories and feature tests, covering test doubles, data providers and coverage reports. Trainees apply the practices to a real internal project while pairing with NileTech engineers, finishing with a green, meaningful test suite instead of tests written just to satisfy coverage.',
            'hands_on', 'onsite', true, 1500.00, 7, 8,
            ['PHP', 'Laravel', 'MySQL', 'Git'],
        ],
        [
            100144, 'Redis for Scalable Backends',
            'A shadowing training in which trainees follow backend engineers as they model a high-traffic checkout flow with Redis. They observe caching strategies, cache invalidation, Redis-backed sessions and queues in a live architecture, then re-create the patterns in guided labs. Participants learn when Redis is the right tool versus when a database or message queue fits better, and how to reason about data expiry and consistency.',
            'shadowing', 'hybrid', false, null, null, 5,
            ['Redis', 'Laravel', 'PHP'],
        ],
        [
            100152, 'Node.js Microservices Architecture',
            'A project-based training where trainees decompose a monolithic Node.js backend into small, independently deployable microservices connected by an internal message flow. The program covers Express.js service design, API contracts, Docker packaging of each service, PostgreSQL per-service data isolation and health/readiness endpoints. Trainees finish with a running multi-service stack they can start, scale and debug end to end.',
            'project_based', 'remote', true, 3200.00, 14, 6,
            ['Node.js', 'JavaScript', 'Docker', 'REST API', 'PostgreSQL'],
        ],
        [
            100152, 'GraphQL API Design',
            'A hands-on hybrid training on building a GraphQL API the right way. Trainees design the schema first, then bring it to life with Apollo Server on Node.js backed by MongoDB and TypeScript: resolvers, data loaders to avoid N+1 queries, input validators, subscriptions and field-level authorization. The training ends with schema-first documentation and a client-ready API contract.',
            'hands_on', 'hybrid', true, 2800.00, 10, 6,
            ['GraphQL', 'Node.js', 'MongoDB', 'TypeScript'],
        ],
        [
            100152, 'PostgreSQL Power User Track',
            'An onsite project-based training on PostgreSQL for production backends. Trainees design normalized and denormalized schemas, write correct migrations, use EXPLAIN to chase down slow queries, add functional and partial indexes, and implement stored procedures for reporting. Realistic data volumes make index choice and query planning matter, giving participants durable database instincts.',
            'project_based', 'onsite', false, null, null, 5,
            ['PostgreSQL', 'SQL', 'Linux', 'Git'],
        ],
        [
            100152, 'TypeScript Backend Services',
            'A hands-on remote training on writing backend services in TypeScript. Trainees scaffold an Express.js API from scratch: typed request/response contracts, DTOs, dependency injection, validation and PostgreSQL access via a typed query layer. The focus is on compile-time safety that catches integration bugs early, plus clean project structure that survives refactoring.',
            'hands_on', 'remote', false, null, null, 8,
            ['TypeScript', 'Node.js', 'Express.js', 'PostgreSQL'],
        ],
        [
            100152, 'MongoDB Data Modeling',
            'A shadowing training at FutureWorks where trainees observe how data engineers model documents for high-read product surfaces. They analyze real collections, learn to decide between embedding and referencing, design aggregation pipelines and measure query plan performance. Participants then model a new feature end to end and defend the document design in review.',
            'shadowing', 'hybrid', true, 1800.00, 7, 4,
            ['MongoDB', 'Node.js', 'JavaScript'],
        ],
        [
            100152, 'AWS Serverless Functions', 
            'A project-based remote training on running backend logic without managing servers. Trainees build and deploy AWS Lambda functions behind API Gateway, triggered both by HTTP and S3 events, with proper IAM policies, environment-based configuration and CloudWatch monitoring. The capstone ties functions into a CI/CD pipeline so a merge deploys a tested, live endpoint.',
            'project_based', 'remote', false, null, null, 7,
            ['AWS', 'Node.js', 'Docker', 'CI/CD'],
        ],
        [
            100152, 'Express.js API Deep Dive',
            'An onsite hands-on training that goes beyond toy examples and into the complexities of real Express.js APIs. Trainees implement middleware chains, request validation, centralized error handling, file uploads, pagination and idempotent endpoints, then battle-test the API with stress requests. The result is a clean, well-structured Express service ready for production hardening.',
            'hands_on', 'onsite', false, null, null, 8,
            ['Express.js', 'JavaScript', 'Node.js', 'MongoDB'],
        ],
        [
            100145, 'Python Backend Data Services',
            'A project-based onsite training for backend engineers who want to serve data over HTTP with Python. Trainees build a service layer in Python that reads from PostgreSQL, applies business logic and exposes RESTful endpoints, packaged with Docker and version controlled with Git. Emphasis is placed on clean function boundaries, typed interfaces and readable, testable code.',
            'project_based', 'onsite', false, null, null, 8,
            ['Python', 'PostgreSQL', 'Docker', 'Git'],
        ],
        [
            100145, 'Machine Learning API Deployment',
            'A project-based hybrid training on turning trained machine learning models into live APIs. Trainees bundle models with Docker, build a Python inference service with validation and monitoring endpoints, and deploy it to a cloud host with a CI/CD pipeline. The program covers model versioning, input schema contracts, latency budgets and graceful failure when the model is unavailable.',
            'project_based', 'hybrid', true, 3600.00, 14, 5,
            ['Python', 'Docker', 'AWS', 'REST API'],
        ],
        [
            100145, 'Data Analytics Pipelines',
            'A hands-on remote training on dependable data pipelines for analytics teams. Trainees build Python extraction/load/scoring jobs against PostgreSQL, add idempotency and retries, schedule them cleanly and surface quality checks. Participants learn how analytics pipelines feed dashboards and how to keep them correct and observable.',
            'hands_on', 'remote', false, null, null, 9,
            ['Python', 'SQL', 'Data Analysis', 'PostgreSQL'],
        ],
        [
            100145, 'Database Design for Analytics',
            'A shadowing training at Alexandria Digital Labs centered on read-optimized database design. Trainees observe how star schemas, dimensions, facts and materialized views are designed for reporting, then design a small warehouse themselves. SQL exercises cover window functions, grouping and building the aggregation tables analysts rely on.',
            'shadowing', 'onsite', false, null, null, 5,
            ['PostgreSQL', 'SQL', 'Data Analysis'],
        ],
        [
            100145, 'Secure Data APIs',
            'A hands-on hybrid training on exposing internal data safely. Trainees wrap PostgreSQL datasets in a Python REST API with token authentication, per-route authorization, input sanitization and audit logging, then run security checks against the result. The training emphasizes API security controls that scale with the number of consumers.',
            'hands_on', 'hybrid', true, 2600.00, 10, 6,
            ['Python', 'REST API', 'Docker', 'Linux'],
        ],
        [
            100145, 'Automated Testing for Data Services',
            'A project-based remote training on keeping data services trustworthy. Trainees write unit tests for transformation logic, integration tests against a disposable PostgreSQL test database and smoke tests for deployed endpoints, all wired into a CI pipeline. The program values fast, deterministic tests over broad coverage and ends with a reviewed test suite on a live service.',
            'project_based', 'remote', true, 2100.00, 7, 6,
            ['Python', 'SQL', 'Docker', 'Git'],
        ],
        [
            100145, 'Linux Server Hardening for Backends',
            'A shadowing onsite training inside Alexandria Digital Labs\' platform team. Trainees follow engineers as they harden application servers: SSH hardening, least-privilege users, firewall rules, fail2ban, file permissions and Docker daemon security. Each topic is demonstrated on a staging host and re-created by the trainee on a practice VM.',
            'shadowing', 'onsite', false, null, null, 4,
            ['Linux', 'Docker', 'CI/CD', 'Git'],
        ],
    ];
}

function bd_run(PDO $pdo): array
{
    $specName = $pdo->query('SELECT name FROM specializations WHERE id = ' . BD_BACKEND_SPECIALIZATION_ID)->fetchColumn();
    if ($specName !== 'Backend Development') {
        throw new RuntimeException('Specialization ' . BD_BACKEND_SPECIALIZATION_ID . ' is not "Backend Development" (' . var_export($specName, true) . '). Aborting.');
    }

    $companies = $pdo->query('SELECT id, city FROM companies WHERE approval_status = \'approved\'')->fetchAll();
    $companyIds = array_column($companies, 'id');
    $companyCity = [];
    foreach ($companies as $c) {
        $companyCity[(int) $c['id']] = $c['city'];
    }

    $insTraining = $pdo->prepare(
        'INSERT INTO training_listings
            (company_id, specialization_id, title, description, training_type, mode, may_lead_to_employment,
             is_paid, compensation_amount, compensation_currency, trial_period_days, capacity, status,
             published_at, starts_at, ends_at, application_deadline, location, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $insSkill = $pdo->prepare('INSERT INTO training_skills (training_id, skill_id) VALUES (?, ?)');
    $insSpecLink = $pdo->prepare('INSERT INTO training_specializations (training_id, specialization_id) VALUES (?, ?)');

    $resolveSkillId = static function (PDO $pdo, string $name): ?int {
        $id = $pdo->prepare('SELECT id FROM skills WHERE name = ?');
        $id->execute([$name]);
        $value = $id->fetchColumn();
        return $value === false ? null : (int) $value;
    };

    $inserted = [];
    $skipped = [];

    foreach (bd_trainings() as $i => $t) {
        [$companyId, $title] = $t;

        if (!in_array($companyId, $companyIds, true)) {
            $skipped[] = ['title' => $title, 'reason' => 'company not approved'];
            continue;
        }

        $exists = $pdo->prepare('SELECT id FROM training_listings WHERE title = ? AND company_id = ? LIMIT 1');
        $exists->execute([$title, $companyId]);
        if ($exists->fetchColumn() !== false) {
            $skipped[] = ['title' => $title, 'reason' => 'already exists'];
            continue;
        }

        $bucket = $i % 7;
        if ($bucket >= 3 && $bucket <= 4) {
            $startsOffset = 46 + (($i * 13) % 45);
            $publishedOffset = -(12 + ($i % 18));
        } elseif ($bucket >= 5) {
            $startsOffset = -(6 + ($i % 16));
            $publishedOffset = $startsOffset - (12 + ($i % 18));
        } else {
            $startsOffset = 3 + (($i * 13) % 26);
            $publishedOffset = -(12 + ($i % 18));
        }
        $endsOffset = $startsOffset + (30 + (($i * 13) % 20));

        $publishedAt = bd_datetime($publishedOffset);
        $startsAt = bd_datetime($startsOffset, '09:00:00');
        $endsAt = bd_datetime($endsOffset, '17:00:00');
        $deadlineAt = $startsAt;

        $insTraining->execute([
            $companyId,
            BD_BACKEND_SPECIALIZATION_ID,
            $title,
            $t[2],
            $t[3],
            $t[4],
            $i % 3 === 0 ? 1 : 0,
            $t[5] ? 1 : 0,
            $t[5] ? $t[6] : null,
            'EGP',
            $t[5] ? $t[7] : null,
            $t[8],
            'published',
            $publishedAt,
            $startsAt,
            $endsAt,
            $deadlineAt,
            $companyCity[$companyId] ?? null,
            $publishedAt,
            $publishedAt,
        ]);

        $trainingId = (int) $pdo->lastInsertId();

        foreach ($t[9] as $skillName) {
            $skillId = $resolveSkillId($pdo, $skillName);
            if ($skillId !== null) {
                $insSkill->execute([$trainingId, $skillId]);
            }
        }
        $insSpecLink->execute([$trainingId, BD_BACKEND_SPECIALIZATION_ID]);

        $inserted[] = ['id' => $trainingId, 'title' => $title];
    }

    return ['inserted' => $inserted, 'skipped' => $skipped];
}

if (PHP_SAPI === 'cli') {
    try {
        $pdo = get_database_connection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->beginTransaction();

        $result = bd_run($pdo);

        $pdo->commit();

        foreach ($result['inserted'] as $r) {
            echo "INSERTED #{$r['id']} {$r['title']}\n";
        }
        foreach ($result['skipped'] as $r) {
            echo "SKIPPED {$r['title']} ({$r['reason']})\n";
        }
        echo sprintf("Backend Development seeder complete: %d inserted, %d skipped.\n", count($result['inserted']), count($result['skipped']));
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, 'Seeder failed: ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}