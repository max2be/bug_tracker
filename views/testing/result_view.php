<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Попытка #<?= e($attempt['id']) ?></h1>
        <p class="text-muted mb-0"><?= e($attempt['full_name']) ?> / <?= e($attempt['task_code']) ?></p>
    </div>
    <a href="<?= e(pageUrl('test_results')) ?>" class="btn btn-outline-secondary">Назад</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><strong>Дата:</strong> <?= e($attempt['attempt_date']) ?></div>
            <div class="col-md-4"><strong>Начало:</strong> <?= e(formatDateTime($attempt['started_at'])) ?></div>
            <div class="col-md-4"><strong>Завершение:</strong> <?= e(formatDateTime($attempt['finished_at'])) ?></div>
            <div class="col-md-4"><strong>Длительность:</strong> <?= e($attempt['duration_seconds']) ?> сек</div>
            <div class="col-md-4"><strong>Правильных ответов:</strong> <?= e($attempt['correct_answers']) ?></div>
            <div class="col-md-4"><strong>Результат:</strong> <?= e($attempt['score_percent']) ?>%</div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h3 class="mb-3">Ответы</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>Вопрос</th>
                    <th>Ответ программиста</th>
                    <th>Правильный ответ</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($answers as $answer): ?>
                    <tr>
                        <td><?= e($answer['question_text']) ?></td>
                        <td><?= e($answer['selected_answer_text'] ?: 'Нет ответа') ?></td>
                        <td><?= e($answer['correct_answer_text']) ?></td>
                        <td><?= (int) $answer['is_correct'] === 1 ? 'Верно' : 'Ошибка' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
