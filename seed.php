<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Volgograd');

require __DIR__ . '/app/db.php';
require __DIR__ . '/app/helpers.php';
require __DIR__ . '/app/metrics.php';

initializeDatabase();

$demand = findOrCreateDemand('DEMAND-999999');

$taskA = getTaskByCode('KARMADEV-111111');
if (!$taskA) {
    dbExecute(
        'INSERT INTO tasks (
            demand_id,
            code,
            title,
            development_hours,
            intro_testing_passed,
            test_scenarios_count,
            responsible_developer,
            created_at,
            updated_at
        ) VALUES (
            :demand_id,
            :code,
            :title,
            :development_hours,
            :intro_testing_passed,
            :test_scenarios_count,
            :responsible_developer,
            :created_at,
            :updated_at
        )',
        [
            'demand_id' => $demand['id'],
            'code' => 'KARMADEV-111111',
            'title' => 'Разработка интеграции с внешним сервисом',
            'development_hours' => 40,
            'intro_testing_passed' => 1,
            'test_scenarios_count' => 8,
            'responsible_developer' => 'Иван Петров',
            'created_at' => '2026-01-10 10:00:00',
            'updated_at' => '2026-01-10 10:00:00',
        ]
    );
    $taskA = getTaskByCode('KARMADEV-111111');
}

$taskB = getTaskByCode('KARMADEV-222222');
if (!$taskB) {
    dbExecute(
        'INSERT INTO tasks (
            demand_id,
            code,
            title,
            development_hours,
            intro_testing_passed,
            test_scenarios_count,
            responsible_developer,
            created_at,
            updated_at
        ) VALUES (
            :demand_id,
            :code,
            :title,
            :development_hours,
            :intro_testing_passed,
            :test_scenarios_count,
            :responsible_developer,
            :created_at,
            :updated_at
        )',
        [
            'demand_id' => $demand['id'],
            'code' => 'KARMADEV-222222',
            'title' => 'Доработка бизнес-логики расчета KPI',
            'development_hours' => 55,
            'intro_testing_passed' => 0,
            'test_scenarios_count' => 5,
            'responsible_developer' => 'Анна Соколова',
            'created_at' => '2026-02-05 11:30:00',
            'updated_at' => '2026-02-05 11:30:00',
        ]
    );
    $taskB = getTaskByCode('KARMADEV-222222');
}

if ((int) dbValue('SELECT COUNT(*) FROM bugs') === 0) {
    $bugs = [
        [
            'demand_id' => $demand['id'],
            'dev_task_id' => $taskA['id'],
            'bug_task_code' => 'KARMADEV-111111',
            'fix_hours' => 2.5,
            'bug_reason' => 'ошибка разработки',
            'bug_reason_comment' => 'Неверная обработка null в ответе API',
            'discovered_at' => '2026-01-15',
            'fixed_at' => '2026-01-16',
            'found_by' => 'тестировщик',
            'found_stage' => 'тестирование',
            'severity' => 'medium',
            'bug_type' => 'functional',
            'status' => 'исправлен',
        ],
        [
            'demand_id' => $demand['id'],
            'dev_task_id' => $taskA['id'],
            'bug_task_code' => 'KARMADEV-111111',
            'fix_hours' => 5,
            'bug_reason' => 'ошибка интеграции',
            'bug_reason_comment' => 'Несовпадение формата данных партнера',
            'discovered_at' => '2026-02-03',
            'fixed_at' => '2026-02-05',
            'found_by' => 'аналитик',
            'found_stage' => 'приемка',
            'severity' => 'high',
            'bug_type' => 'integration',
            'status' => 'проверен',
        ],
        [
            'demand_id' => $demand['id'],
            'dev_task_id' => $taskB['id'],
            'bug_task_code' => 'KARMADEV-222222',
            'fix_hours' => 3.5,
            'bug_reason' => 'регрессия',
            'bug_reason_comment' => 'Сломался ранее рабочий сценарий отчета',
            'discovered_at' => '2026-03-12',
            'fixed_at' => '2026-03-13',
            'found_by' => 'автотест',
            'found_stage' => 'тестирование',
            'severity' => 'high',
            'bug_type' => 'business logic',
            'status' => 'исправлен',
        ],
        [
            'demand_id' => $demand['id'],
            'dev_task_id' => $taskB['id'],
            'bug_task_code' => 'KARMADEV-222222',
            'fix_hours' => 1.5,
            'bug_reason' => 'ошибка конфигурации',
            'bug_reason_comment' => 'Неверный флаг окружения',
            'discovered_at' => '2026-04-05',
            'fixed_at' => '2026-04-05',
            'found_by' => 'production',
            'found_stage' => 'production',
            'severity' => 'low',
            'bug_type' => 'configuration',
            'status' => 'проверен',
        ],
    ];

    foreach ($bugs as $bug) {
        dbExecute(
            'INSERT INTO bugs (
                demand_id,
                dev_task_id,
                bug_task_code,
                fix_hours,
                bug_reason,
                bug_reason_comment,
                discovered_at,
                fixed_at,
                found_by,
                found_stage,
                severity,
                bug_type,
                status,
                created_at,
                updated_at
            ) VALUES (
                :demand_id,
                :dev_task_id,
                :bug_task_code,
                :fix_hours,
                :bug_reason,
                :bug_reason_comment,
                :discovered_at,
                :fixed_at,
                :found_by,
                :found_stage,
                :severity,
                :bug_type,
                :status,
                :created_at,
                :updated_at
            )',
            array_merge($bug, [
                'created_at' => now(),
                'updated_at' => now(),
            ])
        );
    }
}

$testTask = getTestTaskByCode('KARMADEV-999999');
if (!$testTask) {
    dbExecute(
        'INSERT INTO test_tasks (code, is_active, created_at) VALUES (:code, :is_active, :created_at)',
        [
            'code' => 'KARMADEV-999999',
            'is_active' => 1,
            'created_at' => now(),
        ]
    );

    $testTaskId = dbLastInsertId();

    saveTestQuestions($testTaskId, [
        [
            'text' => 'Какой HTTP-метод обычно используют для создания новой записи?',
            'answers' => ['GET', 'POST', 'DELETE', 'PATCH'],
            'correctIndex' => 1,
        ],
        [
            'text' => 'Что вернет выражение typeof null в JavaScript?',
            'answers' => ['null', 'object', 'undefined', 'number'],
            'correctIndex' => 1,
        ],
        [
            'text' => 'Какой SQL-запрос используется для выборки данных?',
            'answers' => ['UPDATE', 'DELETE', 'SELECT', 'INSERT'],
            'correctIndex' => 2,
        ],
    ]);
}

echo "Seed completed." . PHP_EOL;
