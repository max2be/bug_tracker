<?php

declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (!defined('STORAGE_DIR')) {
    define('STORAGE_DIR', APP_ROOT . '/storage');
}

if (!defined('DB_PATH')) {
    define('DB_PATH', STORAGE_DIR . '/app.sqlite');
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!is_dir(STORAGE_DIR)) {
        mkdir(STORAGE_DIR, 0777, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;
}

function dbExecute(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement;
}

function dbOne(string $sql, array $params = []): ?array
{
    $row = dbExecute($sql, $params)->fetch();

    return $row === false ? null : $row;
}

function dbAll(string $sql, array $params = []): array
{
    return dbExecute($sql, $params)->fetchAll();
}

function dbValue(string $sql, array $params = []): mixed
{
    $row = dbExecute($sql, $params)->fetch(PDO::FETCH_NUM);

    return $row === false ? null : $row[0];
}

function dbLastInsertId(): int
{
    return (int) db()->lastInsertId();
}

function dbTransaction(callable $callback): mixed
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $result = $callback($pdo);
        $pdo->commit();

        return $result;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function initializeDatabase(): void
{
    $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS demands (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT UNIQUE NOT NULL,
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    demand_id INTEGER NOT NULL,
    code TEXT NOT NULL UNIQUE,
    title TEXT,
    development_hours REAL DEFAULT 0,
    intro_testing_passed INTEGER DEFAULT 0,
    test_scenarios_count INTEGER DEFAULT 0,
    responsible_developer TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT,
    FOREIGN KEY(demand_id) REFERENCES demands(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bugs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    demand_id INTEGER NOT NULL,
    dev_task_id INTEGER NOT NULL,
    bug_task_code TEXT NOT NULL,
    fix_hours REAL DEFAULT 0,
    bug_reason TEXT,
    bug_reason_comment TEXT,
    discovered_at TEXT,
    fixed_at TEXT,
    found_by TEXT,
    found_stage TEXT,
    severity TEXT,
    bug_type TEXT,
    status TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT,
    FOREIGN KEY(demand_id) REFERENCES demands(id) ON DELETE CASCADE,
    FOREIGN KEY(dev_task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_demands_code ON demands(code);
CREATE INDEX IF NOT EXISTS idx_tasks_code ON tasks(code);
CREATE INDEX IF NOT EXISTS idx_tasks_demand_id ON tasks(demand_id);
CREATE INDEX IF NOT EXISTS idx_bugs_demand_id ON bugs(demand_id);
CREATE INDEX IF NOT EXISTS idx_bugs_dev_task_id ON bugs(dev_task_id);
CREATE INDEX IF NOT EXISTS idx_bugs_discovered_at ON bugs(discovered_at);

CREATE TABLE IF NOT EXISTS test_tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS test_questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    test_task_id INTEGER NOT NULL,
    text TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY(test_task_id) REFERENCES test_tasks(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS test_answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    question_id INTEGER NOT NULL,
    text TEXT NOT NULL,
    is_correct INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY(question_id) REFERENCES test_questions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS test_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    task_code TEXT NOT NULL,
    test_task_id INTEGER,
    attempt_date TEXT NOT NULL,
    started_at TEXT NOT NULL,
    finished_at TEXT NOT NULL,
    duration_seconds INTEGER NOT NULL,
    total_questions INTEGER NOT NULL,
    correct_answers INTEGER NOT NULL,
    score_percent REAL NOT NULL,
    created_at TEXT NOT NULL,
    FOREIGN KEY(test_task_id) REFERENCES test_tasks(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS test_attempt_answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    attempt_id INTEGER NOT NULL,
    question_id INTEGER,
    answer_id INTEGER,
    question_text TEXT NOT NULL,
    selected_answer_text TEXT,
    correct_answer_text TEXT,
    is_correct INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY(attempt_id) REFERENCES test_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY(question_id) REFERENCES test_questions(id) ON DELETE SET NULL,
    FOREIGN KEY(answer_id) REFERENCES test_answers(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_test_tasks_code ON test_tasks(code);
CREATE INDEX IF NOT EXISTS idx_test_questions_task ON test_questions(test_task_id);
CREATE INDEX IF NOT EXISTS idx_test_attempts_task ON test_attempts(test_task_id);
SQL;

    db()->exec($sql);
}
