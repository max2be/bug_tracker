<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Редактирование бага</h1>
    <a href="<?= e(pageUrl('bugs_view', ['id' => $bugId])) ?>" class="btn btn-outline-secondary">Назад к карточке</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= e(pageUrl('bugs_edit', ['id' => $bugId])) ?>">
            <?php include APP_ROOT . '/views/bugs/form.php'; ?>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-danger">Сохранить</button>
                <a href="<?= e(pageUrl('bugs_view', ['id' => $bugId])) ?>" class="btn btn-outline-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>
