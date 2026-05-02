<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Тест по задаче <?= e($task['code']) ?></h1>
        <p class="text-muted mb-0">Программист: <?= e($fullName) ?></p>
    </div>
    <a href="<?= e(pageUrl('test')) ?>" class="btn btn-outline-secondary">Назад</a>
</div>

<form method="post" action="<?= e(pageUrl('test_submit')) ?>">
    <input type="hidden" name="full_name" value="<?= e($fullName) ?>">
    <input type="hidden" name="test_task_id" value="<?= e($task['id']) ?>">
    <input type="hidden" name="task_code" value="<?= e($task['code']) ?>">
    <input type="hidden" name="started_at" value="<?= e($startedAt) ?>">

    <?php foreach ($task['questions'] as $questionIndex => $question): ?>
        <div class="question-card shadow-sm">
            <h5>Вопрос <?= e($questionIndex + 1) ?></h5>
            <p><?= e($question['text']) ?></p>
            <?php foreach ($question['answers'] as $answerIndex => $answer): ?>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="question_<?= e($question['id']) ?>" value="<?= e($answer['id']) ?>" <?= $answerIndex === 0 ? 'required' : '' ?>>
                    <label class="form-check-label"><?= e($answer['text']) ?></label>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-primary">Завершить тест</button>
</form>
