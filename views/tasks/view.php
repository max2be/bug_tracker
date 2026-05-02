<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1"><?= e($task['code']) ?></h1>
        <p class="text-muted mb-0"><?= e($task['title']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= e(pageUrl('bugs_create', ['task' => $task['code'], 'demand' => $task['demand_code']])) ?>" class="btn btn-danger">Добавить баг</a>
        <a href="<?= e(pageUrl('tasks_edit', ['id' => $task['id']])) ?>" class="btn btn-outline-primary">Редактировать</a>
        <a href="<?= e(pageUrl('tasks')) ?>" class="btn btn-outline-secondary">Назад</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm summary-card">
            <div class="card-body">
                <div class="text-muted">Количество багов</div>
                <div class="summary-value"><?= e($taskMetrics['bugs_count']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm summary-card">
            <div class="card-body">
                <div class="text-muted">Defects per 40h</div>
                <div class="summary-value"><?= e($taskMetrics['defects_per_40h']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm summary-card">
            <div class="card-body">
                <div class="text-muted">Часы исправления</div>
                <div class="summary-value"><?= e($taskMetrics['fix_hours_sum']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm summary-card">
            <div class="card-body">
                <div class="text-muted">Bug Fix Ratio</div>
                <div class="summary-value"><?= e($taskMetrics['bug_fix_ratio']) ?>%</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><strong>DEMAND:</strong> <?= e($task['demand_code']) ?></div>
            <div class="col-md-4"><strong>Часы разработки:</strong> <?= e($task['development_hours']) ?></div>
            <div class="col-md-4"><strong>Вводное тестирование:</strong> <?= e(boolLabel($task['intro_testing_passed'])) ?></div>
            <div class="col-md-4"><strong>Количество сценариев:</strong> <?= e($task['test_scenarios_count']) ?></div>
            <div class="col-md-4"><strong>Ответственный:</strong> <?= e($task['responsible_developer']) ?></div>
            <div class="col-md-4"><strong>Создана:</strong> <?= e(formatDateTime($task['created_at'])) ?></div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h3 class="mb-3">Баги по задаче</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Дата обнаружения</th>
                    <th>Задача бага</th>
                    <th>Часы исправления</th>
                    <th>Причина</th>
                    <th>Этап</th>
                    <th>Критичность</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$bugs): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">По этой задаче багов пока нет.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($bugs as $bug): ?>
                    <tr>
                        <td><?= e($bug['id']) ?></td>
                        <td><?= e(formatDate($bug['discovered_at'])) ?></td>
                        <td><?= e($bug['bug_task_code']) ?></td>
                        <td><?= e($bug['fix_hours']) ?></td>
                        <td><?= e($bug['bug_reason']) ?></td>
                        <td><?= e($bug['found_stage']) ?></td>
                        <td><?= e($bug['severity']) ?></td>
                        <td><?= e($bug['status']) ?></td>
                        <td><a href="<?= e(pageUrl('bugs_view', ['id' => $bug['id']])) ?>" class="btn btn-sm btn-outline-primary">Открыть</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
