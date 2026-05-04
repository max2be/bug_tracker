<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Internal QA MVP') ?></title>
    <link href="/node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/public/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-dark navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= e(pageUrl('home')) ?>">QA Metrics MVP</a>
        <div class="navbar-nav flex-wrap">
            <a class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>" href="<?= e(pageUrl('home')) ?>">Главная</a>
            <a class="nav-link <?= str_starts_with($currentPage, 'tasks') ? 'active' : '' ?>" href="<?= e(pageUrl('tasks')) ?>">Задачи</a>
            <a class="nav-link <?= str_starts_with($currentPage, 'bugs') ? 'active' : '' ?>" href="<?= e(pageUrl('bugs')) ?>">Баги</a>
            <a class="nav-link <?= $currentPage === 'metrics' ? 'active' : '' ?>" href="<?= e(pageUrl('metrics')) ?>">Метрики</a>
            <a class="nav-link <?= $currentPage === 'charts' ? 'active' : '' ?>" href="<?= e(pageUrl('charts')) ?>">Графики</a>
            <a class="nav-link <?= str_starts_with($currentPage, 'test') ? 'active' : '' ?>" href="<?= e(pageUrl('test')) ?>">Тестирование</a>
        </div>
    </div>
</nav>

<div class="container page-wrap">
    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= e($flash['type'] === 'error' ? 'danger' : $flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
