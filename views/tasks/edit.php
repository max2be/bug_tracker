<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Редактирование задачи</h1>
    <a href="<?= e(pageUrl('tasks_view', ['id' => $taskId])) ?>" class="btn btn-outline-secondary">Назад к карточке</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= e(pageUrl('tasks_edit', ['id' => $taskId])) ?>">
            <?php include APP_ROOT . '/views/tasks/form.php'; ?>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="<?= e(pageUrl('tasks_view', ['id' => $taskId])) ?>" class="btn btn-outline-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>
