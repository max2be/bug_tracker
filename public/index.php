<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Volgograd');

require dirname(__DIR__) . '/app/db.php';
require dirname(__DIR__) . '/app/helpers.php';
require dirname(__DIR__) . '/app/metrics.php';

ensureSessionStarted();
initializeDatabase();

$page = currentPage();

switch ($page) {
    case 'home':
        showHomePage();
        break;

    case 'tasks':
        showTasksListPage();
        break;

    case 'tasks_create':
        handleTaskCreatePage();
        break;

    case 'tasks_view':
        showTaskViewPage();
        break;

    case 'tasks_edit':
        handleTaskEditPage();
        break;

    case 'tasks_delete':
        handleTaskDeleteAction();
        break;

    case 'bugs':
        showBugsListPage();
        break;

    case 'bugs_create':
        handleBugCreatePage();
        break;

    case 'bugs_view':
        showBugViewPage();
        break;

    case 'bugs_edit':
        handleBugEditPage();
        break;

    case 'bugs_delete':
        handleBugDeleteAction();
        break;

    case 'metrics':
        showMetricsPage();
        break;

    case 'charts':
        showChartsPage();
        break;

    case 'export':
        exportQualityReport();
        break;

    case 'test':
        showTestingHomePage();
        break;

    case 'test_start':
        handleTestStartAction();
        break;

    case 'test_submit':
        handleTestSubmitAction();
        break;

    case 'test_result':
        showTestResultPage();
        break;

    case 'test_tasks':
        showTestTasksPage();
        break;

    case 'test_tasks_create':
        handleTestTaskCreatePage();
        break;

    case 'test_tasks_view':
        showTestTaskViewPage();
        break;

    case 'test_tasks_edit':
        handleTestTaskEditPage();
        break;

    case 'test_tasks_delete':
        handleTestTaskDeleteAction();
        break;

    case 'test_results':
        showTestResultsPage();
        break;

    case 'test_result_view':
        showTestResultDetailsPage();
        break;

    case 'test_export':
        exportTestingReport();
        break;

    default:
        http_response_code(404);
        render('home', [
            'pageTitle' => '404',
            'counts' => getHomeCounts(),
        ]);
        break;
}

function showHomePage(): void
{
    render('home', [
        'pageTitle' => 'Главная',
        'counts' => getHomeCounts(),
    ]);
}

function getHomeCounts(): array
{
    return [
        'demands_count' => (int) dbValue('SELECT COUNT(*) FROM demands'),
        'tasks_count' => (int) dbValue('SELECT COUNT(*) FROM tasks'),
        'bugs_count' => (int) dbValue('SELECT COUNT(*) FROM bugs'),
        'test_attempts_count' => (int) dbValue('SELECT COUNT(*) FROM test_attempts'),
    ];
}

function showTasksListPage(): void
{
    $filters = [
        'demand' => trim((string) ($_GET['demand'] ?? '')),
        'task' => trim((string) ($_GET['task'] ?? '')),
        'responsible_developer' => trim((string) ($_GET['responsible_developer'] ?? '')),
    ];

    $where = ['1 = 1'];
    $params = [];

    if ($filters['demand'] !== '') {
        $filters['demand'] = normalizeDemandCode($filters['demand']);
        $where[] = 'demands.code = :demand';
        $params['demand'] = $filters['demand'];
    }

    if ($filters['task'] !== '') {
        $filters['task'] = normalizeKarmaDevCode($filters['task']);
        $where[] = 'tasks.code = :task_code';
        $params['task_code'] = $filters['task'];
    }

    if ($filters['responsible_developer'] !== '') {
        $where[] = 'tasks.responsible_developer LIKE :developer';
        $params['developer'] = '%' . $filters['responsible_developer'] . '%';
    }

    $tasks = dbAll(
        'SELECT
            tasks.*,
            demands.code AS demand_code,
            COUNT(bugs.id) AS bugs_count
         FROM tasks
         JOIN demands ON demands.id = tasks.demand_id
         LEFT JOIN bugs ON bugs.dev_task_id = tasks.id
         WHERE ' . implode(' AND ', $where) . '
         GROUP BY tasks.id
         ORDER BY tasks.id DESC',
        $params
    );

    render('tasks/list', [
        'pageTitle' => 'Список задач',
        'filters' => $filters,
        'tasks' => $tasks,
    ]);
}

function handleTaskCreatePage(): void
{
    $form = [
        'demand' => trim((string) ($_GET['demand'] ?? '')),
        'code' => '',
        'title' => '',
        'development_hours' => '0',
        'intro_testing_passed' => '0',
        'test_scenarios_count' => '0',
        'responsible_developer' => '',
    ];
    $errors = [];

    if (isPost()) {
        $form = [
            'demand' => trim((string) ($_POST['demand'] ?? '')),
            'code' => trim((string) ($_POST['code'] ?? '')),
            'title' => trim((string) ($_POST['title'] ?? '')),
            'development_hours' => trim((string) ($_POST['development_hours'] ?? '0')),
            'intro_testing_passed' => (string) ($_POST['intro_testing_passed'] ?? '0'),
            'test_scenarios_count' => trim((string) ($_POST['test_scenarios_count'] ?? '0')),
            'responsible_developer' => trim((string) ($_POST['responsible_developer'] ?? '')),
        ];

        $errors = validateTaskForm($form);

        if (!$errors) {
            $demand = findOrCreateDemand($form['demand']);

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
                    'code' => normalizeKarmaDevCode($form['code']),
                    'title' => $form['title'],
                    'development_hours' => parseFloatValue($form['development_hours']),
                    'intro_testing_passed' => (int) $form['intro_testing_passed'],
                    'test_scenarios_count' => parseIntValue($form['test_scenarios_count']),
                    'responsible_developer' => $form['responsible_developer'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            setFlash('success', 'Задача разработки создана.');
            redirectTo('tasks');
        }
    }

    render('tasks/create', [
        'pageTitle' => 'Создание задачи',
        'form' => $form,
        'errors' => $errors,
    ]);
}

function validateTaskForm(array &$form, ?int $taskId = null): array
{
    $errors = [];
    $form['demand'] = normalizeDemandCode($form['demand']);
    $form['code'] = normalizeKarmaDevCode($form['code']);

    if ($form['demand'] === '') {
        $errors[] = 'DEMAND обязателен.';
    }

    if ($form['code'] === '') {
        $errors[] = 'Номер задачи разработки обязателен.';
    }

    $developmentHours = parseFloatValue($form['development_hours']);
    if ($developmentHours < 0) {
        $errors[] = 'Часы разработки должны быть больше или равны 0.';
    }

    $testScenariosCount = parseIntValue($form['test_scenarios_count']);
    if ($testScenariosCount < 0) {
        $errors[] = 'Количество сценариев должно быть больше или равно 0.';
    }

    $existingTask = dbOne(
        'SELECT id FROM tasks WHERE code = :code' . ($taskId ? ' AND id != :id' : ''),
        $taskId ? ['code' => $form['code'], 'id' => $taskId] : ['code' => $form['code']]
    );

    if ($existingTask) {
        $errors[] = 'Задача с таким номером уже существует.';
    }

    $form['development_hours'] = (string) $developmentHours;
    $form['test_scenarios_count'] = (string) max(0, $testScenariosCount);
    $form['intro_testing_passed'] = (string) ((int) $form['intro_testing_passed'] === 1 ? 1 : 0);

    return $errors;
}

function showTaskViewPage(): void
{
    $taskId = (int) ($_GET['id'] ?? 0);
    $task = dbOne(
        'SELECT tasks.*, demands.code AS demand_code
         FROM tasks
         JOIN demands ON demands.id = tasks.demand_id
         WHERE tasks.id = :id',
        ['id' => $taskId]
    );

    if (!$task) {
        setFlash('error', 'Задача не найдена.');
        redirectTo('tasks');
    }

    $bugs = dbAll(
        'SELECT * FROM bugs WHERE dev_task_id = :task_id ORDER BY discovered_at DESC, id DESC',
        ['task_id' => $taskId]
    );

    render('tasks/view', [
        'pageTitle' => $task['code'],
        'task' => $task,
        'bugs' => $bugs,
        'taskMetrics' => calculateTaskMetrics($taskId),
    ]);
}

function handleTaskEditPage(): void
{
    $taskId = (int) ($_GET['id'] ?? 0);
    $task = dbOne(
        'SELECT tasks.*, demands.code AS demand_code
         FROM tasks
         JOIN demands ON demands.id = tasks.demand_id
         WHERE tasks.id = :id',
        ['id' => $taskId]
    );

    if (!$task) {
        setFlash('error', 'Задача не найдена.');
        redirectTo('tasks');
    }

    $form = [
        'demand' => $task['demand_code'],
        'code' => $task['code'],
        'title' => (string) $task['title'],
        'development_hours' => (string) $task['development_hours'],
        'intro_testing_passed' => (string) $task['intro_testing_passed'],
        'test_scenarios_count' => (string) $task['test_scenarios_count'],
        'responsible_developer' => (string) $task['responsible_developer'],
    ];
    $errors = [];

    if (isPost()) {
        $form = [
            'demand' => trim((string) ($_POST['demand'] ?? '')),
            'code' => trim((string) ($_POST['code'] ?? '')),
            'title' => trim((string) ($_POST['title'] ?? '')),
            'development_hours' => trim((string) ($_POST['development_hours'] ?? '0')),
            'intro_testing_passed' => (string) ($_POST['intro_testing_passed'] ?? '0'),
            'test_scenarios_count' => trim((string) ($_POST['test_scenarios_count'] ?? '0')),
            'responsible_developer' => trim((string) ($_POST['responsible_developer'] ?? '')),
        ];

        $errors = validateTaskForm($form, $taskId);

        if (!$errors) {
            $demand = findOrCreateDemand($form['demand']);

            dbExecute(
                'UPDATE tasks SET
                    demand_id = :demand_id,
                    code = :code,
                    title = :title,
                    development_hours = :development_hours,
                    intro_testing_passed = :intro_testing_passed,
                    test_scenarios_count = :test_scenarios_count,
                    responsible_developer = :responsible_developer,
                    updated_at = :updated_at
                 WHERE id = :id',
                [
                    'demand_id' => $demand['id'],
                    'code' => normalizeKarmaDevCode($form['code']),
                    'title' => $form['title'],
                    'development_hours' => parseFloatValue($form['development_hours']),
                    'intro_testing_passed' => (int) $form['intro_testing_passed'],
                    'test_scenarios_count' => parseIntValue($form['test_scenarios_count']),
                    'responsible_developer' => $form['responsible_developer'],
                    'updated_at' => now(),
                    'id' => $taskId,
                ]
            );

            setFlash('success', 'Задача разработки обновлена.');
            redirectTo('tasks_view', ['id' => $taskId]);
        }
    }

    render('tasks/edit', [
        'pageTitle' => 'Редактирование задачи',
        'form' => $form,
        'errors' => $errors,
        'taskId' => $taskId,
    ]);
}

function handleTaskDeleteAction(): void
{
    if (!isPost()) {
        redirectTo('tasks');
    }

    $taskId = (int) ($_GET['id'] ?? 0);
    dbExecute('DELETE FROM tasks WHERE id = :id', ['id' => $taskId]);
    setFlash('success', 'Задача и связанные баги удалены.');
    redirectTo('tasks');
}

function showBugsListPage(): void
{
    $filters = [
        'from' => trim((string) ($_GET['from'] ?? '')),
        'to' => trim((string) ($_GET['to'] ?? '')),
        'month' => trim((string) ($_GET['month'] ?? '')),
        'demand' => trim((string) ($_GET['demand'] ?? '')),
        'task' => trim((string) ($_GET['task'] ?? '')),
        'bug_reason' => trim((string) ($_GET['bug_reason'] ?? '')),
        'found_stage' => trim((string) ($_GET['found_stage'] ?? '')),
        'severity' => trim((string) ($_GET['severity'] ?? '')),
        'status' => trim((string) ($_GET['status'] ?? '')),
    ];

    [$where, $params, $filters] = buildBugListQueryParts($filters);

    $bugs = dbAll(
        'SELECT
            bugs.*,
            demands.code AS demand_code,
            tasks.code AS dev_task_code
         FROM bugs
         JOIN demands ON demands.id = bugs.demand_id
         JOIN tasks ON tasks.id = bugs.dev_task_id
         WHERE ' . implode(' AND ', $where) . '
         ORDER BY bugs.discovered_at DESC, bugs.id DESC',
        $params
    );

    render('bugs/list', [
        'pageTitle' => 'Список багов',
        'filters' => $filters,
        'bugs' => $bugs,
    ]);
}

function buildBugListQueryParts(array $filters): array
{
    $where = ['1 = 1'];
    $params = [];

    if ($filters['from'] !== '') {
        $where[] = 'date(bugs.discovered_at) >= :from';
        $params['from'] = $filters['from'];
    }

    if ($filters['to'] !== '') {
        $where[] = 'date(bugs.discovered_at) <= :to';
        $params['to'] = $filters['to'];
    }

    if ($filters['month'] !== '') {
        $where[] = 'substr(bugs.discovered_at, 1, 7) = :month';
        $params['month'] = $filters['month'];
    }

    if ($filters['demand'] !== '') {
        $filters['demand'] = normalizeDemandCode($filters['demand']);
        $where[] = 'demands.code = :demand';
        $params['demand'] = $filters['demand'];
    }

    if ($filters['task'] !== '') {
        $filters['task'] = normalizeKarmaDevCode($filters['task']);
        $where[] = 'tasks.code = :task_code';
        $params['task_code'] = $filters['task'];
    }

    foreach (['bug_reason', 'found_stage', 'severity', 'status'] as $field) {
        if ($filters[$field] !== '') {
            $where[] = 'bugs.' . $field . ' = :' . $field;
            $params[$field] = $filters[$field];
        }
    }

    return [$where, $params, $filters];
}

function handleBugCreatePage(): void
{
    $form = [
        'demand' => trim((string) ($_GET['demand'] ?? '')),
        'dev_task_code' => trim((string) ($_GET['task'] ?? '')),
        'bug_task_code' => trim((string) ($_GET['task'] ?? '')),
        'fix_hours' => '0',
        'bug_reason' => BUG_REASON_OPTIONS[0],
        'bug_reason_comment' => '',
        'discovered_at' => date('Y-m-d'),
        'fixed_at' => '',
        'found_by' => FOUND_BY_OPTIONS[0],
        'found_stage' => FOUND_STAGE_OPTIONS[3],
        'severity' => SEVERITY_OPTIONS[1],
        'bug_type' => BUG_TYPE_OPTIONS[0],
        'status' => BUG_STATUS_OPTIONS[0],
    ];
    $errors = [];

    if (isPost()) {
        $form = getBugFormDataFromPost();
        $errors = validateBugForm($form);

        if (!$errors) {
            $devTask = getTaskByCode($form['dev_task_code']);
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
                [
                    'demand_id' => $devTask['demand_id'],
                    'dev_task_id' => $devTask['id'],
                    'bug_task_code' => normalizeKarmaDevCode($form['bug_task_code']),
                    'fix_hours' => parseFloatValue($form['fix_hours']),
                    'bug_reason' => $form['bug_reason'],
                    'bug_reason_comment' => $form['bug_reason_comment'],
                    'discovered_at' => $form['discovered_at'],
                    'fixed_at' => $form['fixed_at'] !== '' ? $form['fixed_at'] : null,
                    'found_by' => $form['found_by'],
                    'found_stage' => $form['found_stage'],
                    'severity' => $form['severity'],
                    'bug_type' => $form['bug_type'],
                    'status' => $form['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            setFlash('success', 'Баг сохранен.');
            redirectTo('bugs');
        }
    }

    render('bugs/create', [
        'pageTitle' => 'Добавление бага',
        'form' => $form,
        'errors' => $errors,
    ]);
}

function getBugFormDataFromPost(): array
{
    return [
        'demand' => trim((string) ($_POST['demand'] ?? '')),
        'dev_task_code' => trim((string) ($_POST['dev_task_code'] ?? '')),
        'bug_task_code' => trim((string) ($_POST['bug_task_code'] ?? '')),
        'fix_hours' => trim((string) ($_POST['fix_hours'] ?? '0')),
        'bug_reason' => trim((string) ($_POST['bug_reason'] ?? BUG_REASON_OPTIONS[0])),
        'bug_reason_comment' => trim((string) ($_POST['bug_reason_comment'] ?? '')),
        'discovered_at' => trim((string) ($_POST['discovered_at'] ?? '')),
        'fixed_at' => trim((string) ($_POST['fixed_at'] ?? '')),
        'found_by' => trim((string) ($_POST['found_by'] ?? FOUND_BY_OPTIONS[0])),
        'found_stage' => trim((string) ($_POST['found_stage'] ?? FOUND_STAGE_OPTIONS[0])),
        'severity' => trim((string) ($_POST['severity'] ?? SEVERITY_OPTIONS[0])),
        'bug_type' => trim((string) ($_POST['bug_type'] ?? BUG_TYPE_OPTIONS[0])),
        'status' => trim((string) ($_POST['status'] ?? BUG_STATUS_OPTIONS[0])),
    ];
}

function validateBugForm(array &$form, ?int $bugId = null): array
{
    $errors = [];
    $form['demand'] = normalizeDemandCode($form['demand']);
    $form['dev_task_code'] = normalizeKarmaDevCode($form['dev_task_code']);
    $form['bug_task_code'] = normalizeKarmaDevCode($form['bug_task_code']);

    if ($form['demand'] === '') {
        $errors[] = 'DEMAND обязателен.';
    }

    if ($form['dev_task_code'] === '') {
        $errors[] = 'Задача разработки обязательна.';
    }

    if ($form['bug_task_code'] === '') {
        $errors[] = 'Задача, в которой возник баг, обязательна.';
    }

    $fixHours = parseFloatValue($form['fix_hours']);
    if ($fixHours < 0) {
        $errors[] = 'Часы исправления должны быть больше или равны 0.';
    }

    if ($form['discovered_at'] === '') {
        $errors[] = 'Дата обнаружения обязательна.';
    }

    $devTask = getTaskByCode($form['dev_task_code']);
    if (!$devTask) {
        $errors[] = 'Задача разработки не найдена. Сначала создайте задачу.';
    } elseif ($form['demand'] !== '' && $devTask['demand_code'] !== $form['demand']) {
        $errors[] = 'DEMAND должен совпадать с DEMAND выбранной задачи разработки.';
    }

    $form['fix_hours'] = (string) $fixHours;

    return $errors;
}

function showBugViewPage(): void
{
    $bugId = (int) ($_GET['id'] ?? 0);
    $bug = dbOne(
        'SELECT
            bugs.*,
            demands.code AS demand_code,
            tasks.code AS dev_task_code,
            tasks.title AS dev_task_title,
            tasks.development_hours
         FROM bugs
         JOIN demands ON demands.id = bugs.demand_id
         JOIN tasks ON tasks.id = bugs.dev_task_id
         WHERE bugs.id = :id',
        ['id' => $bugId]
    );

    if (!$bug) {
        setFlash('error', 'Баг не найден.');
        redirectTo('bugs');
    }

    render('bugs/view', [
        'pageTitle' => 'Баг #' . $bug['id'],
        'bug' => $bug,
    ]);
}

function handleBugEditPage(): void
{
    $bugId = (int) ($_GET['id'] ?? 0);
    $bug = dbOne(
        'SELECT
            bugs.*,
            demands.code AS demand_code,
            tasks.code AS dev_task_code
         FROM bugs
         JOIN demands ON demands.id = bugs.demand_id
         JOIN tasks ON tasks.id = bugs.dev_task_id
         WHERE bugs.id = :id',
        ['id' => $bugId]
    );

    if (!$bug) {
        setFlash('error', 'Баг не найден.');
        redirectTo('bugs');
    }

    $form = [
        'demand' => $bug['demand_code'],
        'dev_task_code' => $bug['dev_task_code'],
        'bug_task_code' => $bug['bug_task_code'],
        'fix_hours' => (string) $bug['fix_hours'],
        'bug_reason' => (string) $bug['bug_reason'],
        'bug_reason_comment' => (string) $bug['bug_reason_comment'],
        'discovered_at' => (string) $bug['discovered_at'],
        'fixed_at' => (string) $bug['fixed_at'],
        'found_by' => (string) $bug['found_by'],
        'found_stage' => (string) $bug['found_stage'],
        'severity' => (string) $bug['severity'],
        'bug_type' => (string) $bug['bug_type'],
        'status' => (string) $bug['status'],
    ];
    $errors = [];

    if (isPost()) {
        $form = getBugFormDataFromPost();
        $errors = validateBugForm($form, $bugId);

        if (!$errors) {
            $devTask = getTaskByCode($form['dev_task_code']);
            dbExecute(
                'UPDATE bugs SET
                    demand_id = :demand_id,
                    dev_task_id = :dev_task_id,
                    bug_task_code = :bug_task_code,
                    fix_hours = :fix_hours,
                    bug_reason = :bug_reason,
                    bug_reason_comment = :bug_reason_comment,
                    discovered_at = :discovered_at,
                    fixed_at = :fixed_at,
                    found_by = :found_by,
                    found_stage = :found_stage,
                    severity = :severity,
                    bug_type = :bug_type,
                    status = :status,
                    updated_at = :updated_at
                 WHERE id = :id',
                [
                    'demand_id' => $devTask['demand_id'],
                    'dev_task_id' => $devTask['id'],
                    'bug_task_code' => normalizeKarmaDevCode($form['bug_task_code']),
                    'fix_hours' => parseFloatValue($form['fix_hours']),
                    'bug_reason' => $form['bug_reason'],
                    'bug_reason_comment' => $form['bug_reason_comment'],
                    'discovered_at' => $form['discovered_at'],
                    'fixed_at' => $form['fixed_at'] !== '' ? $form['fixed_at'] : null,
                    'found_by' => $form['found_by'],
                    'found_stage' => $form['found_stage'],
                    'severity' => $form['severity'],
                    'bug_type' => $form['bug_type'],
                    'status' => $form['status'],
                    'updated_at' => now(),
                    'id' => $bugId,
                ]
            );

            setFlash('success', 'Баг обновлен.');
            redirectTo('bugs_view', ['id' => $bugId]);
        }
    }

    render('bugs/edit', [
        'pageTitle' => 'Редактирование бага',
        'form' => $form,
        'errors' => $errors,
        'bugId' => $bugId,
    ]);
}

function handleBugDeleteAction(): void
{
    if (!isPost()) {
        redirectTo('bugs');
    }

    $bugId = (int) ($_GET['id'] ?? 0);
    dbExecute('DELETE FROM bugs WHERE id = :id', ['id' => $bugId]);
    setFlash('success', 'Баг удален.');
    redirectTo('bugs');
}

function showMetricsPage(): void
{
    $filters = [
        'from' => trim((string) ($_GET['from'] ?? '')),
        'to' => trim((string) ($_GET['to'] ?? '')),
        'demand' => trim((string) ($_GET['demand'] ?? '')),
        'responsible_developer' => trim((string) ($_GET['responsible_developer'] ?? '')),
    ];

    if ($filters['demand'] !== '') {
        $filters['demand'] = normalizeDemandCode($filters['demand']);
    }

    render('metrics/index', [
        'pageTitle' => 'Метрики',
        'filters' => $filters,
        'metrics' => calculateOverviewMetrics($filters),
        'demandMetrics' => getDemandMetricsRows($filters),
    ]);
}

function showChartsPage(): void
{
    $filters = [
        'from' => trim((string) ($_GET['from'] ?? '')),
        'to' => trim((string) ($_GET['to'] ?? '')),
        'demand' => trim((string) ($_GET['demand'] ?? '')),
    ];

    if ($filters['demand'] !== '') {
        $filters['demand'] = normalizeDemandCode($filters['demand']);
    }

    render('charts/index', [
        'pageTitle' => 'Графики',
        'filters' => $filters,
        'chartData' => getChartsData($filters),
        'includeCharts' => true,
    ]);
}

function exportQualityReport(): void
{
    $filters = [
        'from' => trim((string) ($_GET['from'] ?? '')),
        'to' => trim((string) ($_GET['to'] ?? '')),
        'demand' => trim((string) ($_GET['demand'] ?? '')),
        'responsible_developer' => trim((string) ($_GET['responsible_developer'] ?? '')),
    ];

    if ($filters['demand'] !== '') {
        $filters['demand'] = normalizeDemandCode($filters['demand']);
    }

    $taskFilter = buildTaskMetricsFilter($filters);
    $bugFilter = buildBugMetricsFilter($filters);

    $tasks = dbAll(
        'SELECT tasks.*, demands.code AS demand_code
         FROM tasks
         JOIN demands ON demands.id = tasks.demand_id
         WHERE ' . $taskFilter['sql'] . '
         ORDER BY tasks.id DESC',
        $taskFilter['params']
    );

    $bugs = dbAll(
        'SELECT bugs.*, demands.code AS demand_code, tasks.code AS dev_task_code
         FROM bugs
         JOIN tasks ON tasks.id = bugs.dev_task_id
         JOIN demands ON demands.id = bugs.demand_id
         WHERE ' . $bugFilter['sql'] . '
         ORDER BY bugs.discovered_at DESC, bugs.id DESC',
        $bugFilter['params']
    );

    $demandMetrics = getDemandMetricsRows($filters);
    $monthlyMetrics = getMonthlyMetricsRows($filters);

    $fileName = 'defects-report-' . date('Y-m-d') . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');

    echo "\xEF\xBB\xBF";
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h1>Defects Report</h1>';

    echo '<h2>Список задач</h2><table border="1"><tr><th>ID</th><th>DEMAND</th><th>Задача</th><th>Название</th><th>Часы разработки</th><th>Вводное тестирование</th><th>Сценарии</th><th>Ответственный</th><th>Создана</th></tr>';
    foreach ($tasks as $task) {
        echo '<tr>';
        echo '<td>' . e($task['id']) . '</td>';
        echo '<td>' . e($task['demand_code']) . '</td>';
        echo '<td>' . e($task['code']) . '</td>';
        echo '<td>' . e($task['title']) . '</td>';
        echo '<td>' . e($task['development_hours']) . '</td>';
        echo '<td>' . e(boolLabel($task['intro_testing_passed'])) . '</td>';
        echo '<td>' . e($task['test_scenarios_count']) . '</td>';
        echo '<td>' . e($task['responsible_developer']) . '</td>';
        echo '<td>' . e($task['created_at']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '<h2>Список багов</h2><table border="1"><tr><th>ID</th><th>Дата обнаружения</th><th>DEMAND</th><th>Задача разработки</th><th>Задача бага</th><th>Часы исправления</th><th>Причина</th><th>Этап</th><th>Критичность</th><th>Тип</th><th>Статус</th></tr>';
    foreach ($bugs as $bug) {
        echo '<tr>';
        echo '<td>' . e($bug['id']) . '</td>';
        echo '<td>' . e($bug['discovered_at']) . '</td>';
        echo '<td>' . e($bug['demand_code']) . '</td>';
        echo '<td>' . e($bug['dev_task_code']) . '</td>';
        echo '<td>' . e($bug['bug_task_code']) . '</td>';
        echo '<td>' . e($bug['fix_hours']) . '</td>';
        echo '<td>' . e($bug['bug_reason']) . '</td>';
        echo '<td>' . e($bug['found_stage']) . '</td>';
        echo '<td>' . e($bug['severity']) . '</td>';
        echo '<td>' . e($bug['bug_type']) . '</td>';
        echo '<td>' . e($bug['status']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '<h2>Метрики по DEMAND</h2><table border="1"><tr><th>DEMAND</th><th>Задачи</th><th>Баги</th><th>Часы разработки</th><th>Часы исправления</th><th>Defects per 40h</th><th>Average Fix Hours</th><th>Bug Fix Ratio</th><th>Intro Testing Coverage</th><th>Test Scenarios per Task</th><th>Bugs per Test Scenario</th></tr>';
    foreach ($demandMetrics as $row) {
        echo '<tr>';
        echo '<td>' . e($row['demand_code']) . '</td>';
        echo '<td>' . e($row['tasks_count']) . '</td>';
        echo '<td>' . e($row['bugs_count']) . '</td>';
        echo '<td>' . e($row['development_hours_sum']) . '</td>';
        echo '<td>' . e($row['fix_hours_sum']) . '</td>';
        echo '<td>' . e($row['defects_per_40h']) . '</td>';
        echo '<td>' . e($row['average_fix_hours']) . '</td>';
        echo '<td>' . e($row['bug_fix_ratio']) . '</td>';
        echo '<td>' . e($row['intro_testing_coverage']) . '</td>';
        echo '<td>' . e($row['test_scenarios_per_task']) . '</td>';
        echo '<td>' . e($row['bugs_per_test_scenario']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '<h2>Метрики по месяцам</h2><table border="1"><tr><th>Месяц</th><th>Количество багов</th><th>Часы исправления</th><th>Average Fix Hours</th></tr>';
    foreach ($monthlyMetrics as $row) {
        echo '<tr>';
        echo '<td>' . e($row['month']) . '</td>';
        echo '<td>' . e($row['bugs_count']) . '</td>';
        echo '<td>' . e($row['fix_hours_sum']) . '</td>';
        echo '<td>' . e($row['average_fix_hours']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '</body></html>';
    exit;
}

function showTestingHomePage(?array $form = null, array $errors = []): void
{
    render('testing/home', [
        'pageTitle' => 'Тестирование',
        'form' => $form ?? ['full_name' => '', 'task_code' => ''],
        'errors' => $errors,
    ]);
}

function handleTestStartAction(): void
{
    if (!isPost()) {
        redirectTo('test');
    }

    $form = [
        'full_name' => trim((string) ($_POST['full_name'] ?? '')),
        'task_code' => trim((string) ($_POST['task_code'] ?? '')),
    ];
    $errors = [];

    $form['task_code'] = normalizeKarmaDevCode($form['task_code']);

    if ($form['full_name'] === '') {
        $errors[] = 'ФИО обязательно.';
    }

    if ($form['task_code'] === '') {
        $errors[] = 'Номер тестовой задачи обязателен.';
    }

    $task = $form['task_code'] !== ''
        ? getTestTaskByCode($form['task_code'])
        : null;

    if (!$errors && (!$task || (int) $task['is_active'] !== 1)) {
        $errors[] = 'Активная тестовая задача не найдена.';
    }

    if (!$errors) {
        $taskWithQuestions = getTestTaskWithQuestions((int) $task['id']);
        if (!$taskWithQuestions || !$taskWithQuestions['questions']) {
            $errors[] = 'У этой тестовой задачи нет вопросов.';
        } else {
            render('testing/questions', [
                'pageTitle' => 'Тест ' . $taskWithQuestions['code'],
                'task' => $taskWithQuestions,
                'fullName' => $form['full_name'],
                'startedAt' => date('c'),
            ]);
            return;
        }
    }

    showTestingHomePage($form, $errors);
}

function getTestTaskByCode(string $code): ?array
{
    return dbOne('SELECT * FROM test_tasks WHERE code = :code', ['code' => normalizeKarmaDevCode($code)]);
}

function handleTestSubmitAction(): void
{
    if (!isPost()) {
        redirectTo('test');
    }

    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $testTaskId = (int) ($_POST['test_task_id'] ?? 0);
    $taskCode = normalizeKarmaDevCode((string) ($_POST['task_code'] ?? ''));
    $startedAt = trim((string) ($_POST['started_at'] ?? ''));

    if ($fullName === '' || $testTaskId <= 0 || $taskCode === '') {
        setFlash('error', 'Некорректные данные тестирования.');
        redirectTo('test');
    }

    $task = getTestTaskWithQuestions($testTaskId);
    if (!$task || $task['code'] !== $taskCode) {
        setFlash('error', 'Тестовая задача не найдена.');
        redirectTo('test');
    }

    $correctAnswers = 0;
    $attemptAnswers = [];

    foreach ($task['questions'] as $question) {
        $selectedAnswerId = isset($_POST['question_' . $question['id']]) ? (int) $_POST['question_' . $question['id']] : 0;
        $selectedAnswer = $selectedAnswerId > 0
            ? dbOne(
                'SELECT * FROM test_answers WHERE id = :id AND question_id = :question_id',
                ['id' => $selectedAnswerId, 'question_id' => $question['id']]
            )
            : null;

        $correctAnswer = dbOne(
            'SELECT * FROM test_answers WHERE question_id = :question_id AND is_correct = 1 LIMIT 1',
            ['question_id' => $question['id']]
        );

        $isCorrect = $selectedAnswer && $correctAnswer && (int) $selectedAnswer['id'] === (int) $correctAnswer['id'];
        if ($isCorrect) {
            $correctAnswers++;
        }

        $attemptAnswers[] = [
            'question_id' => $question['id'],
            'answer_id' => $selectedAnswer['id'] ?? null,
            'question_text' => $question['text'],
            'selected_answer_text' => $selectedAnswer['text'] ?? '',
            'correct_answer_text' => $correctAnswer['text'] ?? '',
            'is_correct' => $isCorrect ? 1 : 0,
        ];
    }

    $finishedAt = date('c');
    $startedTimestamp = strtotime($startedAt);
    $finishedTimestamp = strtotime($finishedAt);
    $durationSeconds = $startedTimestamp !== false && $finishedTimestamp !== false
        ? max(0, $finishedTimestamp - $startedTimestamp)
        : 0;
    $totalQuestions = count($task['questions']);
    $scorePercent = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

    $attemptId = dbTransaction(function () use (
        $fullName,
        $taskCode,
        $testTaskId,
        $startedAt,
        $finishedAt,
        $durationSeconds,
        $totalQuestions,
        $correctAnswers,
        $scorePercent,
        $attemptAnswers
    ): int {
        dbExecute(
            'INSERT INTO test_attempts (
                full_name,
                task_code,
                test_task_id,
                attempt_date,
                started_at,
                finished_at,
                duration_seconds,
                total_questions,
                correct_answers,
                score_percent,
                created_at
            ) VALUES (
                :full_name,
                :task_code,
                :test_task_id,
                :attempt_date,
                :started_at,
                :finished_at,
                :duration_seconds,
                :total_questions,
                :correct_answers,
                :score_percent,
                :created_at
            )',
            [
                'full_name' => $fullName,
                'task_code' => $taskCode,
                'test_task_id' => $testTaskId,
                'attempt_date' => date('Y-m-d'),
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
                'duration_seconds' => $durationSeconds,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'score_percent' => $scorePercent,
                'created_at' => now(),
            ]
        );

        $attemptId = dbLastInsertId();

        foreach ($attemptAnswers as $row) {
            dbExecute(
                'INSERT INTO test_attempt_answers (
                    attempt_id,
                    question_id,
                    answer_id,
                    question_text,
                    selected_answer_text,
                    correct_answer_text,
                    is_correct
                ) VALUES (
                    :attempt_id,
                    :question_id,
                    :answer_id,
                    :question_text,
                    :selected_answer_text,
                    :correct_answer_text,
                    :is_correct
                )',
                [
                    'attempt_id' => $attemptId,
                    'question_id' => $row['question_id'],
                    'answer_id' => $row['answer_id'],
                    'question_text' => $row['question_text'],
                    'selected_answer_text' => $row['selected_answer_text'],
                    'correct_answer_text' => $row['correct_answer_text'],
                    'is_correct' => $row['is_correct'],
                ]
            );
        }

        return $attemptId;
    });

    redirectTo('test_result', ['id' => $attemptId]);
}

function showTestResultPage(): void
{
    $attemptId = (int) ($_GET['id'] ?? 0);
    $attempt = dbOne('SELECT * FROM test_attempts WHERE id = :id', ['id' => $attemptId]);

    if (!$attempt) {
        setFlash('error', 'Результат тестирования не найден.');
        redirectTo('test');
    }

    render('testing/result', [
        'pageTitle' => 'Результат теста',
        'attempt' => $attempt,
    ]);
}

function showTestTasksPage(): void
{
    $testTasks = dbAll(
        'SELECT
            test_tasks.*,
            COUNT(test_questions.id) AS questions_count
         FROM test_tasks
         LEFT JOIN test_questions ON test_questions.test_task_id = test_tasks.id
         GROUP BY test_tasks.id
         ORDER BY test_tasks.id DESC'
    );

    render('testing/tasks_list', [
        'pageTitle' => 'Тестовые задачи',
        'testTasks' => $testTasks,
    ]);
}

function handleTestTaskCreatePage(): void
{
    $form = [
        'code' => '',
        'is_active' => '1',
    ];
    $questions = [];
    $errors = [];

    if (isPost()) {
        $form = [
            'code' => normalizeKarmaDevCode((string) ($_POST['code'] ?? '')),
            'is_active' => (string) ((int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0),
        ];
        $questions = normalizeTestQuestions($_POST['questions'] ?? []);

        if ($form['code'] === '') {
            $errors[] = 'Code тестовой задачи обязателен.';
        }

        if (dbOne('SELECT id FROM test_tasks WHERE code = :code', ['code' => $form['code']])) {
            $errors[] = 'Тестовая задача с таким кодом уже существует.';
        }

        if (!validateTestQuestions($questions)) {
            $errors[] = 'Добавьте хотя бы один вопрос, минимум два варианта ответа и один правильный.';
        }

        if (!$errors) {
            $testTaskId = dbTransaction(function () use ($form, $questions): int {
                dbExecute(
                    'INSERT INTO test_tasks (code, is_active, created_at) VALUES (:code, :is_active, :created_at)',
                    [
                        'code' => $form['code'],
                        'is_active' => (int) $form['is_active'],
                        'created_at' => now(),
                    ]
                );

                $testTaskId = dbLastInsertId();
                saveTestQuestions($testTaskId, $questions);

                return $testTaskId;
            });

            setFlash('success', 'Тестовая задача создана.');
            redirectTo('test_tasks_view', ['id' => $testTaskId]);
        }
    }

    render('testing/tasks_create', [
        'pageTitle' => 'Создание тестовой задачи',
        'form' => $form,
        'questions' => $questions,
        'errors' => $errors,
    ]);
}

function showTestTaskViewPage(): void
{
    $taskId = (int) ($_GET['id'] ?? 0);
    $task = getTestTaskWithQuestions($taskId);

    if (!$task) {
        setFlash('error', 'Тестовая задача не найдена.');
        redirectTo('test_tasks');
    }

    render('testing/task_view', [
        'pageTitle' => $task['code'],
        'task' => $task,
    ]);
}

function handleTestTaskEditPage(): void
{
    $taskId = (int) ($_GET['id'] ?? 0);
    $task = getTestTaskWithQuestions($taskId);

    if (!$task) {
        setFlash('error', 'Тестовая задача не найдена.');
        redirectTo('test_tasks');
    }

    $form = [
        'code' => $task['code'],
        'is_active' => (string) $task['is_active'],
    ];
    $questions = $task['questions'];
    $errors = [];

    if (isPost()) {
        $form = [
            'code' => normalizeKarmaDevCode((string) ($_POST['code'] ?? '')),
            'is_active' => (string) ((int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0),
        ];
        $questions = normalizeTestQuestions($_POST['questions'] ?? []);

        if ($form['code'] === '') {
            $errors[] = 'Code тестовой задачи обязателен.';
        }

        if (dbOne('SELECT id FROM test_tasks WHERE code = :code AND id != :id', ['code' => $form['code'], 'id' => $taskId])) {
            $errors[] = 'Тестовая задача с таким кодом уже существует.';
        }

        if (!validateTestQuestions($questions)) {
            $errors[] = 'Добавьте хотя бы один вопрос, минимум два варианта ответа и один правильный.';
        }

        if (!$errors) {
            dbTransaction(function () use ($taskId, $form, $questions): void {
                dbExecute(
                    'UPDATE test_tasks SET code = :code, is_active = :is_active WHERE id = :id',
                    [
                        'code' => $form['code'],
                        'is_active' => (int) $form['is_active'],
                        'id' => $taskId,
                    ]
                );

                saveTestQuestions($taskId, $questions);
            });

            setFlash('success', 'Тестовая задача обновлена.');
            redirectTo('test_tasks_view', ['id' => $taskId]);
        }
    }

    render('testing/tasks_edit', [
        'pageTitle' => 'Редактирование тестовой задачи',
        'form' => $form,
        'questions' => $questions,
        'errors' => $errors,
        'taskId' => $taskId,
    ]);
}

function handleTestTaskDeleteAction(): void
{
    if (!isPost()) {
        redirectTo('test_tasks');
    }

    $taskId = (int) ($_GET['id'] ?? 0);
    dbExecute('DELETE FROM test_tasks WHERE id = :id', ['id' => $taskId]);
    setFlash('success', 'Тестовая задача удалена.');
    redirectTo('test_tasks');
}

function showTestResultsPage(): void
{
    $attempts = dbAll('SELECT * FROM test_attempts ORDER BY id DESC');

    render('testing/results_list', [
        'pageTitle' => 'Логи тестирования',
        'attempts' => $attempts,
    ]);
}

function showTestResultDetailsPage(): void
{
    $attemptId = (int) ($_GET['id'] ?? 0);
    $attempt = dbOne('SELECT * FROM test_attempts WHERE id = :id', ['id' => $attemptId]);

    if (!$attempt) {
        setFlash('error', 'Попытка тестирования не найдена.');
        redirectTo('test_results');
    }

    $answers = dbAll(
        'SELECT question_text, selected_answer_text, correct_answer_text, is_correct
         FROM test_attempt_answers
         WHERE attempt_id = :attempt_id
         ORDER BY id ASC',
        ['attempt_id' => $attemptId]
    );

    render('testing/result_view', [
        'pageTitle' => 'Попытка #' . $attemptId,
        'attempt' => $attempt,
        'answers' => $answers,
    ]);
}

function exportTestingReport(): void
{
    $attempts = dbAll('SELECT * FROM test_attempts ORDER BY id DESC');

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="testing-results-' . date('Y-m-d') . '.xls"');

    echo "\xEF\xBB\xBF";
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h1>Testing Results</h1>';
    echo '<table border="1"><tr><th>ID</th><th>ФИО</th><th>Задача</th><th>Дата</th><th>Начало</th><th>Завершение</th><th>Длительность</th><th>Всего вопросов</th><th>Правильных</th><th>Процент</th><th>Ответы</th></tr>';

    foreach ($attempts as $attempt) {
        $answers = dbAll(
            'SELECT question_text, selected_answer_text, correct_answer_text, is_correct
             FROM test_attempt_answers
             WHERE attempt_id = :attempt_id
             ORDER BY id ASC',
            ['attempt_id' => $attempt['id']]
        );

        $answerParts = [];
        foreach ($answers as $index => $answer) {
            $answerParts[] =
                ($index + 1) . '. ' .
                $answer['question_text'] .
                ' | ответ: ' . ($answer['selected_answer_text'] ?: 'Нет ответа') .
                ' | правильный: ' . ($answer['correct_answer_text'] ?: '') .
                ' | статус: ' . ((int) $answer['is_correct'] === 1 ? 'Верно' : 'Ошибка');
        }

        echo '<tr>';
        echo '<td>' . e($attempt['id']) . '</td>';
        echo '<td>' . e($attempt['full_name']) . '</td>';
        echo '<td>' . e($attempt['task_code']) . '</td>';
        echo '<td>' . e($attempt['attempt_date']) . '</td>';
        echo '<td>' . e($attempt['started_at']) . '</td>';
        echo '<td>' . e($attempt['finished_at']) . '</td>';
        echo '<td>' . e($attempt['duration_seconds']) . '</td>';
        echo '<td>' . e($attempt['total_questions']) . '</td>';
        echo '<td>' . e($attempt['correct_answers']) . '</td>';
        echo '<td>' . e($attempt['score_percent']) . '</td>';
        echo '<td>' . nl2br(e(implode("\n", $answerParts))) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '</body></html>';
    exit;
}
