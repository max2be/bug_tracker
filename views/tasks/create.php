<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Создание задачи разработки</h1>
    <a href="<?= e(pageUrl('tasks')) ?>" class="btn btn-outline-secondary">Назад к списку</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= e(pageUrl('tasks_create')) ?>">
            <?php include APP_ROOT . '/views/tasks/form.php'; ?>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="<?= e(pageUrl('tasks')) ?>" class="btn btn-outline-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>
