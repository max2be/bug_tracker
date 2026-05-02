<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Список багов</h1>
        <p class="text-muted mb-0">Реестр багов по DEMAND и задачам разработки.</p>
    </div>
    <a href="<?= e(pageUrl('bugs_create')) ?>" class="btn btn-danger">Добавить баг</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">
            <input type="hidden" name="page" value="bugs">
            <div class="col-md-2">
                <label class="form-label">Период от</label>
                <input type="date" name="from" class="form-control" value="<?= e($filters['from']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Период до</label>
                <input type="date" name="to" class="form-control" value="<?= e($filters['to']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Месяц</label>
                <input type="month" name="month" class="form-control" value="<?= e($filters['month']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">DEMAND</label>
                <input type="text" name="demand" class="form-control" value="<?= e($filters['demand']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Задача разработки</label>
                <input type="text" name="task" class="form-control" value="<?= e($filters['task']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Причина</label>
                <select name="bug_reason" class="form-select">
                    <option value="">Все</option>
                    <?php foreach (BUG_REASON_OPTIONS as $option): ?>
                        <option value="<?= e($option) ?>" <?= selected($filters['bug_reason'], $option) ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Этап</label>
                <select name="found_stage" class="form-select">
                    <option value="">Все</option>
                    <?php foreach (FOUND_STAGE_OPTIONS as $option): ?>
                        <option value="<?= e($option) ?>" <?= selected($filters['found_stage'], $option) ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Критичность</label>
                <select name="severity" class="form-select">
                    <option value="">Все</option>
                    <?php foreach (SEVERITY_OPTIONS as $option): ?>
                        <option value="<?= e($option) ?>" <?= selected($filters['severity'], $option) ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Статус</label>
                <select name="status" class="form-select">
                    <option value="">Все</option>
                    <?php foreach (BUG_STATUS_OPTIONS as $option): ?>
                        <option value="<?= e($option) ?>" <?= selected($filters['status'], $option) ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-primary">Фильтровать</button>
                <a href="<?= e(pageUrl('bugs')) ?>" class="btn btn-outline-secondary">Сбросить</a>
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
                <th>Дата обнаружения</th>
                <th>DEMAND</th>
                <th>Задача разработки</th>
                <th>Задача бага</th>
                <th>Часы исправления</th>
                <th>Причина</th>
                <th>Этап обнаружения</th>
                <th>Критичность</th>
                <th>Тип</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$bugs): ?>
                <tr>
                    <td colspan="12" class="text-center text-muted py-4">Баги не найдены.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($bugs as $bug): ?>
                <tr>
                    <td><?= e($bug['id']) ?></td>
                    <td><?= e(formatDate($bug['discovered_at'])) ?></td>
                    <td><?= e($bug['demand_code']) ?></td>
                    <td><?= e($bug['dev_task_code']) ?></td>
                    <td><?= e($bug['bug_task_code']) ?></td>
                    <td><?= e($bug['fix_hours']) ?></td>
                    <td><?= e($bug['bug_reason']) ?></td>
                    <td><?= e($bug['found_stage']) ?></td>
                    <td><?= e($bug['severity']) ?></td>
                    <td><?= e($bug['bug_type']) ?></td>
                    <td><?= e($bug['status']) ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="<?= e(pageUrl('bugs_view', ['id' => $bug['id']])) ?>" class="btn btn-sm btn-outline-primary">Открыть</a>
                            <a href="<?= e(pageUrl('bugs_edit', ['id' => $bug['id']])) ?>" class="btn btn-sm btn-outline-secondary">Редактировать</a>
                            <form method="post" action="<?= e(pageUrl('bugs_delete', ['id' => $bug['id']])) ?>" onsubmit="return confirm('Удалить баг?');">
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
