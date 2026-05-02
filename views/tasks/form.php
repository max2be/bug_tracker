<?php include APP_ROOT . '/views/layout/errors.php'; ?>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">DEMAND</label>
        <input type="text" name="demand" class="form-control" value="<?= e($form['demand']) ?>" placeholder="DEMAND-999999 или 999999" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Номер задачи разработки</label>
        <input type="text" name="code" class="form-control" value="<?= e($form['code']) ?>" placeholder="KARMADEV-999999 или 999999" required>
    </div>
    <div class="col-12">
        <label class="form-label">Название задачи</label>
        <input type="text" name="title" class="form-control" value="<?= e($form['title']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Часы разработки</label>
        <input type="number" step="0.1" min="0" name="development_hours" class="form-control" value="<?= e($form['development_hours']) ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Вводное тестирование</label>
        <select name="intro_testing_passed" class="form-select">
            <option value="1" <?= selected($form['intro_testing_passed'], '1') ?>>Да</option>
            <option value="0" <?= selected($form['intro_testing_passed'], '0') ?>>Нет</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Количество сценариев</label>
        <input type="number" min="0" name="test_scenarios_count" class="form-control" value="<?= e($form['test_scenarios_count']) ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Ответственный разработчик</label>
        <input type="text" name="responsible_developer" class="form-control" value="<?= e($form['responsible_developer']) ?>">
    </div>
</div>
