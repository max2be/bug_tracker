<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Список задач разработки</h1>
        <p class="text-muted mb-0">Учет задач в разрезе DEMAND и ответственных разработчиков.</p>
    </div>
    <a href="<?= e(pageUrl('tasks_create')) ?>" class="btn btn-primary">Добавить задачу</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">
            <input type="hidden" name="page" value="tasks">
            <div class="col-md-3">
                <label class="form-label">DEMAND</label>
                <input type="text" name="demand" class="form-control" value="<?= e($filters['demand']) ?>" placeholder="DEMAND-999999 или 999999">
            </div>
            <div class="col-md-3">
                <label class="form-label">Задача</label>
                <input type="text" name="task" class="form-control" value="<?= e($filters['task']) ?>" placeholder="KARMADEV-999999 или 999999">
            </div>
            <div class="col-md-3">
                <label class="form-label">Ответственный</label>
                <input type="text" name="responsible_developer" class="form-control" value="<?= e($filters['responsible_developer']) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-primary">Фильтровать</button>
                <a href="<?= e(pageUrl('tasks')) ?>" class="btn btn-outline-secondary">Сбросить</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>DEMAND</th>
                <th>Задача</th>
                <th>Название</th>
                <th>Часы разработки</th>
                <th>Вводное тестирование</th>
                <th>Сценарии</th>
                <th>Ответственный</th>
                <th>Баги</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$tasks): ?>
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">Задачи не найдены.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= e($task['id']) ?></td>
                    <td><?= e($task['demand_code']) ?></td>
                    <td><?= e($task['code']) ?></td>
                    <td><?= e($task['title']) ?></td>
                    <td><?= e($task['development_hours']) ?></td>
                    <td><?= e(boolLabel($task['intro_testing_passed'])) ?></td>
                    <td><?= e($task['test_scenarios_count']) ?></td>
                    <td><?= e($task['responsible_developer']) ?></td>
                    <td><?= e($task['bugs_count']) ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="<?= e(pageUrl('tasks_view', ['id' => $task['id']])) ?>" class="btn btn-sm btn-outline-primary">Открыть</a>
                            <a href="<?= e(pageUrl('tasks_edit', ['id' => $task['id']])) ?>" class="btn btn-sm btn-outline-secondary">Редактировать</a>
                            <a href="<?= e(pageUrl('bugs_create', ['task' => $task['code'], 'demand' => $task['demand_code']])) ?>" class="btn btn-sm btn-outline-danger">Добавить баг</a>
                            <form method="post" action="<?= e(pageUrl('tasks_delete', ['id' => $task['id']])) ?>" onsubmit="return confirm('Удалить задачу и связанные баги?');">
                                <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
