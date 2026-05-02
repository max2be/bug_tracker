<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Тестирование программиста</h1>
        <p class="text-muted mb-0">Функционал старого MVP: ввод ФИО, номер тестовой задачи и результат в процентах.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= e(pageUrl('test_tasks')) ?>" class="btn btn-outline-primary">Тестовые задачи</a>
        <a href="<?= e(pageUrl('test_results')) ?>" class="btn btn-outline-secondary">Логи</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php include APP_ROOT . '/views/layout/errors.php'; ?>
        <form method="post" action="<?= e(pageUrl('test_start')) ?>" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">ФИО</label>
                <input type="text" name="full_name" class="form-control" value="<?= e($form['full_name']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Номер тестовой задачи</label>
                <input type="text" name="task_code" class="form-control" value="<?= e($form['task_code']) ?>" placeholder="KARMADEV-999999" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Начать тест</button>
            </div>
        </form>
    </div>
</div>
