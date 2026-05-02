<?php

declare(strict_types=1);

function buildTaskMetricsFilter(array $filters): array
{
    $where = ['1 = 1'];
    $params = [];

    $demand = normalizeDemandCode((string) ($filters['demand'] ?? ''));
    $developer = trim((string) ($filters['responsible_developer'] ?? ''));

    if ($demand !== '') {
        $where[] = 'demands.code = :task_demand';
        $params['task_demand'] = $demand;
    }

    if ($developer !== '') {
        $where[] = 'tasks.responsible_developer LIKE :task_developer';
        $params['task_developer'] = '%' . $developer . '%';
    }

    return ['sql' => implode(' AND ', $where), 'params' => $params];
}

function buildBugMetricsFilter(array $filters): array
{
    $where = ['1 = 1'];
    $params = [];

    $from = trim((string) ($filters['from'] ?? ''));
    $to = trim((string) ($filters['to'] ?? ''));
    $demand = normalizeDemandCode((string) ($filters['demand'] ?? ''));
    $developer = trim((string) ($filters['responsible_developer'] ?? ''));

    if ($from !== '') {
        $where[] = 'date(bugs.discovered_at) >= :bug_from';
        $params['bug_from'] = $from;
    }

    if ($to !== '') {
        $where[] = 'date(bugs.discovered_at) <= :bug_to';
        $params['bug_to'] = $to;
    }

    if ($demand !== '') {
        $where[] = 'demands.code = :bug_demand';
        $params['bug_demand'] = $demand;
    }

    if ($developer !== '') {
        $where[] = 'tasks.responsible_developer LIKE :bug_developer';
        $params['bug_developer'] = '%' . $developer . '%';
    }

    return ['sql' => implode(' AND ', $where), 'params' => $params];
}

function calculateOverviewMetrics(array $filters): array
{
    $taskFilter = buildTaskMetricsFilter($filters);
    $bugFilter = buildBugMetricsFilter($filters);

    $taskStats = dbOne(
        'SELECT
            COUNT(tasks.id) AS tasks_count,
            COUNT(DISTINCT tasks.demand_id) AS demands_count,
            COALESCE(SUM(tasks.development_hours), 0) AS development_hours_sum,
            COALESCE(SUM(tasks.intro_testing_passed), 0) AS intro_testing_tasks_count,
            COALESCE(SUM(tasks.test_scenarios_count), 0) AS test_scenarios_sum
         FROM tasks
         JOIN demands ON demands.id = tasks.demand_id
         WHERE ' . $taskFilter['sql'],
        $taskFilter['params']
    ) ?: [];

    $bugStats = dbOne(
        'SELECT
            COUNT(bugs.id) AS bugs_count,
            COALESCE(SUM(bugs.fix_hours), 0) AS fix_hours_sum
         FROM bugs
         JOIN tasks ON tasks.id = bugs.dev_task_id
         JOIN demands ON demands.id = bugs.demand_id
         WHERE ' . $bugFilter['sql'],
        $bugFilter['params']
    ) ?: [];

    $tasksCount = (int) ($taskStats['tasks_count'] ?? 0);
    $demandsCount = (int) ($taskStats['demands_count'] ?? 0);
    $developmentHoursSum = (float) ($taskStats['development_hours_sum'] ?? 0);
    $introTestingTasksCount = (int) ($taskStats['intro_testing_tasks_count'] ?? 0);
    $testScenariosSum = (int) ($taskStats['test_scenarios_sum'] ?? 0);
    $bugsCount = (int) ($bugStats['bugs_count'] ?? 0);
    $fixHoursSum = (float) ($bugStats['fix_hours_sum'] ?? 0);

    return [
        'demands_count' => $demandsCount,
        'tasks_count' => $tasksCount,
        'bugs_count' => $bugsCount,
        'development_hours_sum' => round($developmentHoursSum, 2),
        'fix_hours_sum' => round($fixHoursSum, 2),
        'defects_per_40h' => safeRatio((float) $bugsCount, $developmentHoursSum, 40),
        'average_fix_hours' => safeRatio($fixHoursSum, (float) $bugsCount),
        'bug_fix_ratio' => safeRatio($fixHoursSum, $developmentHoursSum, 100),
        'intro_testing_coverage' => safeRatio((float) $introTestingTasksCount, (float) $tasksCount, 100),
        'test_scenarios_per_task' => safeRatio((float) $testScenariosSum, (float) $tasksCount),
        'bugs_per_test_scenario' => safeRatio((float) $bugsCount, (float) $testScenariosSum),
    ];
}

function calculateTaskMetrics(int $taskId): array
{
    $stats = dbOne(
        'SELECT
            COUNT(id) AS bugs_count,
            COALESCE(SUM(fix_hours), 0) AS fix_hours_sum
         FROM bugs
         WHERE dev_task_id = :task_id',
        ['task_id' => $taskId]
    ) ?: [];

    $task = dbOne('SELECT development_hours FROM tasks WHERE id = :id', ['id' => $taskId]) ?: [];

    $bugsCount = (int) ($stats['bugs_count'] ?? 0);
    $fixHoursSum = (float) ($stats['fix_hours_sum'] ?? 0);
    $developmentHours = (float) ($task['development_hours'] ?? 0);

    return [
        'bugs_count' => $bugsCount,
        'fix_hours_sum' => round($fixHoursSum, 2),
        'defects_per_40h' => safeRatio((float) $bugsCount, $developmentHours, 40),
        'bug_fix_ratio' => safeRatio($fixHoursSum, $developmentHours, 100),
    ];
}

function getDemandMetricsRows(array $filters = []): array
{
    $taskFilter = buildTaskMetricsFilter($filters);
    $bugFilter = buildBugMetricsFilter($filters);

    $taskRows = dbAll(
        'SELECT
            demands.id AS demand_id,
            demands.code,
            COUNT(tasks.id) AS tasks_count,
            COALESCE(SUM(tasks.development_hours), 0) AS development_hours_sum,
            COALESCE(SUM(tasks.intro_testing_passed), 0) AS intro_testing_tasks_count,
            COALESCE(SUM(tasks.test_scenarios_count), 0) AS test_scenarios_sum
         FROM tasks
         JOIN demands ON demands.id = tasks.demand_id
         WHERE ' . $taskFilter['sql'] . '
         GROUP BY demands.id
         ORDER BY demands.code ASC',
        $taskFilter['params']
    );

    $bugRows = dbAll(
        'SELECT
            demands.id AS demand_id,
            COUNT(bugs.id) AS bugs_count,
            COALESCE(SUM(bugs.fix_hours), 0) AS fix_hours_sum
         FROM bugs
         JOIN tasks ON tasks.id = bugs.dev_task_id
         JOIN demands ON demands.id = bugs.demand_id
         WHERE ' . $bugFilter['sql'] . '
         GROUP BY demands.id',
        $bugFilter['params']
    );

    $bugMap = [];
    foreach ($bugRows as $bugRow) {
        $bugMap[(int) $bugRow['demand_id']] = $bugRow;
    }

    $result = [];
    foreach ($taskRows as $taskRow) {
        $demandId = (int) $taskRow['demand_id'];
        $bugsCount = (int) ($bugMap[$demandId]['bugs_count'] ?? 0);
        $fixHoursSum = (float) ($bugMap[$demandId]['fix_hours_sum'] ?? 0);
        $developmentHoursSum = (float) $taskRow['development_hours_sum'];
        $tasksCount = (int) $taskRow['tasks_count'];
        $testScenariosSum = (int) $taskRow['test_scenarios_sum'];

        $result[] = [
            'demand_code' => $taskRow['code'],
            'tasks_count' => $tasksCount,
            'bugs_count' => $bugsCount,
            'development_hours_sum' => round($developmentHoursSum, 2),
            'fix_hours_sum' => round($fixHoursSum, 2),
            'defects_per_40h' => safeRatio((float) $bugsCount, $developmentHoursSum, 40),
            'average_fix_hours' => safeRatio($fixHoursSum, (float) $bugsCount),
            'bug_fix_ratio' => safeRatio($fixHoursSum, $developmentHoursSum, 100),
            'intro_testing_coverage' => safeRatio((float) $taskRow['intro_testing_tasks_count'], (float) $tasksCount, 100),
            'test_scenarios_per_task' => safeRatio((float) $testScenariosSum, (float) $tasksCount),
            'bugs_per_test_scenario' => safeRatio((float) $bugsCount, (float) $testScenariosSum),
        ];
    }

    return $result;
}

function getMonthlyMetricsRows(array $filters = []): array
{
    $bugFilter = buildBugMetricsFilter($filters);

    $rows = dbAll(
        'SELECT
            substr(bugs.discovered_at, 1, 7) AS month,
            COUNT(bugs.id) AS bugs_count,
            COALESCE(SUM(bugs.fix_hours), 0) AS fix_hours_sum
         FROM bugs
         JOIN tasks ON tasks.id = bugs.dev_task_id
         JOIN demands ON demands.id = bugs.demand_id
         WHERE ' . $bugFilter['sql'] . '
         GROUP BY substr(bugs.discovered_at, 1, 7)
         ORDER BY month ASC',
        $bugFilter['params']
    );

    foreach ($rows as &$row) {
        $row['bugs_count'] = (int) $row['bugs_count'];
        $row['fix_hours_sum'] = round((float) $row['fix_hours_sum'], 2);
        $row['average_fix_hours'] = safeRatio((float) $row['fix_hours_sum'], (float) $row['bugs_count']);
    }
    unset($row);

    return $rows;
}

function getChartsData(array $filters = []): array
{
    $bugFilter = buildBugMetricsFilter($filters);
    $demandRows = getDemandMetricsRows($filters);

    $bugsByMonth = dbAll(
        'SELECT
            substr(bugs.discovered_at, 1, 7) AS month,
            COUNT(bugs.id) AS bugs_count
         FROM bugs
         JOIN tasks ON tasks.id = bugs.dev_task_id
         JOIN demands ON demands.id = bugs.demand_id
         WHERE ' . $bugFilter['sql'] . '
         GROUP BY substr(bugs.discovered_at, 1, 7)
         ORDER BY month ASC',
        $bugFilter['params']
    );

    $bugsByMonthDemand = dbAll(
        'SELECT
            substr(bugs.discovered_at, 1, 7) AS month,
            demands.code AS demand_code,
            COUNT(bugs.id) AS bugs_count
         FROM bugs
         JOIN tasks ON tasks.id = bugs.dev_task_id
         JOIN demands ON demands.id = bugs.demand_id
         WHERE ' . $bugFilter['sql'] . '
         GROUP BY month, demands.code
         ORDER BY month ASC, demands.code ASC',
        $bugFilter['params']
    );

    $bugReasons = dbAll(
        'SELECT bugs.bug_reason AS label, COUNT(bugs.id) AS value
         FROM bugs
         JOIN tasks ON tasks.id = bugs.dev_task_id
         JOIN demands ON demands.id = bugs.demand_id
         WHERE ' . $bugFilter['sql'] . '
         GROUP BY bugs.bug_reason
         ORDER BY value DESC, label ASC',
        $bugFilter['params']
    );

    $foundStages = dbAll(
        'SELECT bugs.found_stage AS label, COUNT(bugs.id) AS value
         FROM bugs
         JOIN tasks ON tasks.id = bugs.dev_task_id
         JOIN demands ON demands.id = bugs.demand_id
         WHERE ' . $bugFilter['sql'] . '
         GROUP BY bugs.found_stage
         ORDER BY value DESC, label ASC',
        $bugFilter['params']
    );

    return [
        'bugs_by_month' => $bugsByMonth,
        'bugs_by_month_demand' => $bugsByMonthDemand,
        'defects_per_40h_by_demand' => array_map(
            static fn (array $row): array => ['label' => $row['demand_code'], 'value' => $row['defects_per_40h']],
            $demandRows
        ),
        'bug_reasons' => $bugReasons,
        'found_stages' => $foundStages,
        'dev_vs_fix_by_demand' => array_map(
            static fn (array $row): array => [
                'label' => $row['demand_code'],
                'development_hours_sum' => $row['development_hours_sum'],
                'fix_hours_sum' => $row['fix_hours_sum'],
            ],
            $demandRows
        ),
    ];
}
