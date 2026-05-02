<?php include APP_ROOT . '/views/layout/errors.php'; ?>

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">DEMAND</label>
        <input type="text" name="demand" class="form-control" value="<?= e($form['demand']) ?>" placeholder="DEMAND-999999 или 999999" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Задача разработки</label>
        <input type="text" name="dev_task_code" class="form-control" value="<?= e($form['dev_task_code']) ?>" placeholder="KARMADEV-999999 или 999999" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Задача, в которой возник баг</label>
        <input type="text" name="bug_task_code" class="form-control" value="<?= e($form['bug_task_code']) ?>" placeholder="KARMADEV-999999 или 999999" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Часы исправления бага</label>
        <input type="number" step="0.1" min="0" name="fix_hours" class="form-control" value="<?= e($form['fix_hours']) ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Причина бага</label>
        <select name="bug_reason" class="form-select">
            <?php foreach (BUG_REASON_OPTIONS as $option): ?>
                <option value="<?= e($option) ?>" <?= selected($form['bug_reason'], $option) ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Комментарий к причине</label>
        <input type="text" name="bug_reason_comment" class="form-control" value="<?= e($form['bug_reason_comment']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Дата обнаружения</label>
        <input type="date" name="discovered_at" class="form-control" value="<?= e($form['discovered_at']) ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Дата исправления</label>
        <input type="date" name="fixed_at" class="form-control" value="<?= e($form['fixed_at']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Кто нашел баг</label>
        <select name="found_by" class="form-select">
            <?php foreach (FOUND_BY_OPTIONS as $option): ?>
                <option value="<?= e($option) ?>" <?= selected($form['found_by'], $option) ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Этап обнаружения</label>
        <select name="found_stage" class="form-select">
            <?php foreach (FOUND_STAGE_OPTIONS as $option): ?>
                <option value="<?= e($option) ?>" <?= selected($form['found_stage'], $option) ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Критичность</label>
        <select name="severity" class="form-select">
            <?php foreach (SEVERITY_OPTIONS as $option): ?>
                <option value="<?= e($option) ?>" <?= selected($form['severity'], $option) ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Тип бага</label>
        <select name="bug_type" class="form-select">
            <?php foreach (BUG_TYPE_OPTIONS as $option): ?>
                <option value="<?= e($option) ?>" <?= selected($form['bug_type'], $option) ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Статус</label>
        <select name="status" class="form-select">
            <?php foreach (BUG_STATUS_OPTIONS as $option): ?>
                <option value="<?= e($option) ?>" <?= selected($form['status'], $option) ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
