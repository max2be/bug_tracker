<div class="card shadow-sm text-center">
    <div class="card-body py-5">
        <h1 class="mb-3">Результат теста</h1>
        <p class="text-muted"><?= e($attempt['full_name']) ?></p>
        <div class="display-3 fw-bold text-primary mb-3"><?= e($attempt['score_percent']) ?>%</div>
        <p class="lead"><?= e($attempt['correct_answers']) ?> правильных ответов из <?= e($attempt['total_questions']) ?></p>
        <p class="text-muted">Длительность: <?= e($attempt['duration_seconds']) ?> сек</p>
        <a href="<?= e(pageUrl('test')) ?>" class="btn btn-primary">Пройти еще раз</a>
    </div>
</div>
