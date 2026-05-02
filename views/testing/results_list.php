<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Логи тестирования</h1>
        <p class="text-muted mb-0">Попытки прохождения тестовых задач программистами.</p>
    </div>
    <a href="<?= e(pageUrl('test_export')) ?>" class="btn btn-success">Экспорт в XLS</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>ФИО</th>
                <th>Задача</th>
                <th>Дата</th>
                <th>Начало</th>
                <th>Завершение</th>
                <th>Длительность</th>
                <th>Результат</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$attempts): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">Логов пока нет.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($attempts as $attempt): ?>
                <tr>
                    <td><?= e($attempt['id']) ?></td>
                    <td><?= e($attempt['full_name']) ?></td>
                    <td><?= e($attempt['task_code']) ?></td>
                    <td><?= e($attempt['attempt_date']) ?></td>
                    <td><?= e(formatDateTime($attempt['started_at'])) ?></td>
                    <td><?= e(formatDateTime($attempt['finished_at'])) ?></td>
                    <td><?= e($attempt['duration_seconds']) ?> сек</td>
                    <td><?= e($attempt['correct_answers']) ?>/<?= e($attempt['total_questions']) ?> (<?= e($attempt['score_percent']) ?>%)</td>
                    <td><a href="<?= e(pageUrl('test_result_view', ['id' => $attempt['id']])) ?>" class="btn btn-sm btn-outline-primary">Открыть</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
