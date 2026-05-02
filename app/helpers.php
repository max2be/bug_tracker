<?php

declare(strict_types=1);

const BUG_REASON_OPTIONS = [
    'ошибка анализа',
    'неполные требования',
    'ошибка разработки',
    'ошибка интеграции',
    'ошибка конфигурации',
    'неучтенный сценарий',
    'регрессия',
    'ошибка тестирования',
    'внешняя зависимость',
    'другое',
];

const FOUND_BY_OPTIONS = [
    'аналитик',
    'тестировщик',
    'разработчик',
    'пользователь',
    'автотест',
    'production',
];

const FOUND_STAGE_OPTIONS = [
    'до разработки',
    'во время разработки',
    'code review',
    'тестирование',
    'приемка',
    'production',
];

const SEVERITY_OPTIONS = ['low', 'medium', 'high', 'critical'];

const BUG_TYPE_OPTIONS = [
    'functional',
    'UI',
    'integration',
    'performance',
    'security',
    'data',
    'business logic',
    'configuration',
    'other',
];

const BUG_STATUS_OPTIONS = ['открыт', 'исправлен', 'проверен', 'отклонен'];

function ensureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function pageUrl(string $page, array $params = []): string
{
    $query = array_merge(['page' => $page], $params);

    return '/?' . http_build_query($query);
}

function redirectTo(string $page, array $params = []): never
{
    header('Location: ' . pageUrl($page, $params));
    exit;
}

function currentPage(): string
{
    return (string) ($_GET['page'] ?? 'home');
}

function isPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pullFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

function normalizeDemandCode(string $input): string
{
    $value = strtoupper(trim($input));

    if ($value === '') {
        return '';
    }

    if (preg_match('/^\d+$/', $value) === 1) {
        return 'DEMAND-' . $value;
    }

    if (str_starts_with($value, 'DEMAND-')) {
        return $value;
    }

    return $value;
}

function normalizeKarmaDevCode(string $input): string
{
    $value = strtoupper(trim($input));

    if ($value === '') {
        return '';
    }

    if (preg_match('/^\d+$/', $value) === 1) {
        return 'KARMADEV-' . $value;
    }

    if (str_starts_with($value, 'KARMADEV-')) {
        return $value;
    }

    return $value;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function formatDate(?string $value): string
{
    if (!$value) {
        return '';
    }

    $timestamp = strtotime($value);

    return $timestamp === false ? $value : date('d.m.Y', $timestamp);
}

function formatDateTime(?string $value): string
{
    if (!$value) {
        return '';
    }

    $timestamp = strtotime($value);

    return $timestamp === false ? $value : date('d.m.Y H:i', $timestamp);
}

function selected(string|int|float|null $value, string|int|float|null $expected): string
{
    return (string) $value === (string) $expected ? 'selected' : '';
}

function checked(bool|int|string $condition): string
{
    return $condition ? 'checked' : '';
}

function boolLabel(int|string|null $value): string
{
    return (int) $value === 1 ? 'Да' : 'Нет';
}

function parseFloatValue(mixed $value): float
{
    $stringValue = trim((string) $value);
    if ($stringValue === '') {
        return 0.0;
    }

    return (float) str_replace(',', '.', $stringValue);
}

function parseIntValue(mixed $value): int
{
    return (int) trim((string) $value);
}

function render(string $view, array $data = []): void
{
    $flash = pullFlash();
    $pageTitle = $data['pageTitle'] ?? 'Internal QA MVP';
    $currentPage = currentPage();
    extract($data, EXTR_SKIP);

    include APP_ROOT . '/views/layout/header.php';
    include APP_ROOT . '/views/' . $view . '.php';
    include APP_ROOT . '/views/layout/footer.php';
}

function findOrCreateDemand(string $code): array
{
    $normalizedCode = normalizeDemandCode($code);
    $demand = dbOne('SELECT * FROM demands WHERE code = :code', ['code' => $normalizedCode]);

    if ($demand) {
        return $demand;
    }

    dbExecute(
        'INSERT INTO demands (code, created_at) VALUES (:code, :created_at)',
        [
            'code' => $normalizedCode,
            'created_at' => now(),
        ]
    );

    return dbOne('SELECT * FROM demands WHERE id = :id', ['id' => dbLastInsertId()]);
}

function getDemandByCode(string $code): ?array
{
    $normalizedCode = normalizeDemandCode($code);
    if ($normalizedCode === '') {
        return null;
    }

    return dbOne('SELECT * FROM demands WHERE code = :code', ['code' => $normalizedCode]);
}

function getTaskByCode(string $code): ?array
{
    $normalizedCode = normalizeKarmaDevCode($code);
    if ($normalizedCode === '') {
        return null;
    }

    return dbOne(
        'SELECT tasks.*, demands.code AS demand_code
         FROM tasks
         JOIN demands ON demands.id = tasks.demand_id
         WHERE tasks.code = :code',
        ['code' => $normalizedCode]
    );
}

function getDemandOptions(): array
{
    return dbAll('SELECT id, code FROM demands ORDER BY code ASC');
}

function getTaskOptions(): array
{
    return dbAll(
        'SELECT tasks.id, tasks.code, tasks.title, demands.code AS demand_code
         FROM tasks
         JOIN demands ON demands.id = tasks.demand_id
         ORDER BY tasks.code ASC'
    );
}

function getDeveloperOptions(): array
{
    return dbAll(
        'SELECT DISTINCT responsible_developer
         FROM tasks
         WHERE responsible_developer IS NOT NULL AND TRIM(responsible_developer) != ""
         ORDER BY responsible_developer ASC'
    );
}

function safeRatio(float $numerator, float $denominator, float $multiplier = 1): float
{
    if ($denominator <= 0) {
        return 0.0;
    }

    return round(($numerator / $denominator) * $multiplier, 2);
}

function normalizeTestQuestions(array $rawQuestions): array
{
    $result = [];

    foreach ($rawQuestions as $rawQuestion) {
        $text = trim((string) ($rawQuestion['text'] ?? ''));
        if ($text === '') {
            continue;
        }

        $answers = [];
        foreach (($rawQuestion['answers'] ?? []) as $rawAnswer) {
            $answerText = trim((string) ($rawAnswer['text'] ?? ''));
            if ($answerText !== '') {
                $answers[] = $answerText;
            }
        }

        $result[] = [
            'text' => $text,
            'answers' => $answers,
            'correctIndex' => isset($rawQuestion['correctIndex']) ? (int) $rawQuestion['correctIndex'] : -1,
        ];
    }

    return $result;
}

function validateTestQuestions(array $questions): bool
{
    if (!$questions) {
        return false;
    }

    foreach ($questions as $question) {
        if (count($question['answers']) < 2) {
            return false;
        }

        $correctIndex = (int) $question['correctIndex'];
        if ($correctIndex < 0 || $correctIndex >= count($question['answers'])) {
            return false;
        }
    }

    return true;
}

function saveTestQuestions(int $testTaskId, array $questions): void
{
    dbExecute(
        'DELETE FROM test_answers WHERE question_id IN (SELECT id FROM test_questions WHERE test_task_id = :test_task_id)',
        ['test_task_id' => $testTaskId]
    );
    dbExecute('DELETE FROM test_questions WHERE test_task_id = :test_task_id', ['test_task_id' => $testTaskId]);

    foreach ($questions as $questionIndex => $question) {
        dbExecute(
            'INSERT INTO test_questions (test_task_id, text, sort_order) VALUES (:test_task_id, :text, :sort_order)',
            [
                'test_task_id' => $testTaskId,
                'text' => $question['text'],
                'sort_order' => $questionIndex,
            ]
        );

        $questionId = dbLastInsertId();

        foreach ($question['answers'] as $answerIndex => $answerText) {
            dbExecute(
                'INSERT INTO test_answers (question_id, text, is_correct) VALUES (:question_id, :text, :is_correct)',
                [
                    'question_id' => $questionId,
                    'text' => $answerText,
                    'is_correct' => $answerIndex === (int) $question['correctIndex'] ? 1 : 0,
                ]
            );
        }
    }
}

function getTestTaskWithQuestions(int $id): ?array
{
    $task = dbOne('SELECT * FROM test_tasks WHERE id = :id', ['id' => $id]);

    if (!$task) {
        return null;
    }

    $questions = dbAll(
        'SELECT * FROM test_questions WHERE test_task_id = :test_task_id ORDER BY sort_order ASC, id ASC',
        ['test_task_id' => $id]
    );

    foreach ($questions as &$question) {
        $question['answers'] = dbAll(
            'SELECT * FROM test_answers WHERE question_id = :question_id ORDER BY id ASC',
            ['question_id' => $question['id']]
        );
    }
    unset($question);

    $task['questions'] = $questions;

    return $task;
}
