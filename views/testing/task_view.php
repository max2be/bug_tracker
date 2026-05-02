<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1"><?= e($task['code']) ?></h1>
        <p class="text-muted mb-0">Активна: <?= e(boolLabel($task['is_active'])) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= e(pageUrl('test_tasks_edit', ['id' => $task['id']])) ?>" class="btn btn-outline-primary">Редактировать</a>
        <a href="<?= e(pageUrl('test_tasks')) ?>" class="btn btn-outline-secondary">Назад</a>
    </div>
</div>

<?php foreach ($task['questions'] as $questionIndex => $question): ?>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h5>Вопрос <?= e($questionIndex + 1) ?></h5>
            <p><?= e($question['text']) ?></p>
            <ul class="mb-0">
                <?php foreach ($question['answers'] as $answer): ?>
                    <li>
                        <?= e($answer['text']) ?>
                        <?php if ((int) $answer['is_correct'] === 1): ?>
                            <strong>(правильный)</strong>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endforeach; ?>
