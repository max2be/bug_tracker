<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Баг #<?= e($bug['id']) ?></h1>
        <p class="text-muted mb-0"><?= e($bug['bug_task_code']) ?> / <?= e($bug['dev_task_code']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= e(pageUrl('bugs_edit', ['id' => $bug['id']])) ?>" class="btn btn-outline-primary">Редактировать</a>
        <a href="<?= e(pageUrl('bugs')) ?>" class="btn btn-outline-secondary">Назад</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><strong>DEMAND:</strong> <?= e($bug['demand_code']) ?></div>
            <div class="col-md-4"><strong>Задача разработки:</strong> <?= e($bug['dev_task_code']) ?></div>
            <div class="col-md-4"><strong>Задача бага:</strong> <?= e($bug['bug_task_code']) ?></div>
            <div class="col-md-4"><strong>Часы разработки задачи:</strong> <?= e($bug['development_hours']) ?></div>
            <div class="col-md-4"><strong>Часы исправления бага:</strong> <?= e($bug['fix_hours']) ?></div>
            <div class="col-md-4"><strong>Причина:</strong> <?= e($bug['bug_reason']) ?></div>
            <div class="col-md-6"><strong>Комментарий:</strong> <?= e($bug['bug_reason_comment']) ?></div>
            <div class="col-md-3"><strong>Дата обнаружения:</strong> <?= e(formatDate($bug['discovered_at'])) ?></div>
            <div class="col-md-3"><strong>Дата исправления:</strong> <?= e(formatDate($bug['fixed_at'])) ?></div>
            <div class="col-md-3"><strong>Кто нашел:</strong> <?= e($bug['found_by']) ?></div>
            <div class="col-md-3"><strong>Этап:</strong> <?= e($bug['found_stage']) ?></div>
            <div class="col-md-3"><strong>Критичность:</strong> <?= e($bug['severity']) ?></div>
            <div class="col-md-3"><strong>Тип:</strong> <?= e($bug['bug_type']) ?></div>
            <div class="col-md-3"><strong>Статус:</strong> <?= e($bug['status']) ?></div>
            <div class="col-md-3"><strong>Создан:</strong> <?= e(formatDateTime($bug['created_at'])) ?></div>
        </div>
    </div>
</div>
