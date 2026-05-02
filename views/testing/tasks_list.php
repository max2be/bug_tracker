<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Тестовые задачи</h1>
        <p class="text-muted mb-0">Создание и управление задачами для тестирования программиста.</p>
    </div>
    <a href="<?= e(pageUrl('test_tasks_create')) ?>" class="btn btn-primary">Создать тестовую задачу</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Активна</th>
                <th>Создана</th>
                <th>Вопросов</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$testTasks): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Тестовых задач пока нет.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($testTasks as $task): ?>
                <tr>
                    <td><?= e($task['id']) ?></td>
                    <td><?= e($task['code']) ?></td>
                    <td><?= e(boolLabel($task['is_active'])) ?></td>
                    <td><?= e(formatDateTime($task['created_at'])) ?></td>
                    <td><?= e($task['questions_count']) ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="<?= e(pageUrl('test_tasks_view', ['id' => $task['id']])) ?>" class="btn btn-sm btn-outline-primary">Открыть</a>
                            <a href="<?= e(pageUrl('test_tasks_edit', ['id' => $task['id']])) ?>" class="btn btn-sm btn-outline-secondary">Редактировать</a>
                            <form method="post" action="<?= e(pageUrl('test_tasks_delete', ['id' => $task['id']])) ?>" onsubmit="return confirm('Удалить тестовую задачу?');">
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
