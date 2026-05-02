<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Внутреннее приложение по качеству разработки</h1>
        <p class="text-muted mb-0">Фиксация задач, багов, метрик и сценариев тестирования.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm home-link-card">
            <div class="card-body">
                <h5 class="card-title">Пройти тестирование</h5>
                <p class="text-muted">Функционал текущего MVP: ввод ФИО, номер тестовой задачи, прохождение вопросов и результат.</p>
                <a href="<?= e(pageUrl('test')) ?>" class="btn btn-primary">Открыть тест</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm home-link-card">
            <div class="card-body">
                <h5 class="card-title">Добавить баг</h5>
                <p class="text-muted">Быстрое создание бага с привязкой к DEMAND и задаче разработки.</p>
                <a href="<?= e(pageUrl('bugs_create')) ?>" class="btn btn-danger">Создать баг</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm home-link-card">
            <div class="card-body">
                <h5 class="card-title">Метрики качества</h5>
                <p class="text-muted">Карточки метрик, разрез по DEMAND, графики и экспорт отчета.</p>
                <a href="<?= e(pageUrl('metrics')) ?>" class="btn btn-success">Открыть метрики</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card summary-card shadow-sm">
            <div class="card-body">
                <div class="text-muted">DEMAND</div>
                <div class="summary-value"><?= e($counts['demands_count']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Задачи разработки</div>
                <div class="summary-value"><?= e($counts['tasks_count']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Баги</div>
                <div class="summary-value"><?= e($counts['bugs_count']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Попытки тестирования</div>
                <div class="summary-value"><?= e($counts['test_attempts_count']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title">Разработка и баги</h4>
                <div class="list-group">
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('tasks_create')) ?>">Добавить задачу</a>
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('tasks')) ?>">Список задач</a>
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('bugs_create')) ?>">Добавить баг</a>
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('bugs')) ?>">Список багов</a>
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('metrics')) ?>">Метрики</a>
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('charts')) ?>">Графики</a>
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('export')) ?>">Экспорт в XLS</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title">Тестирование из старого MVP</h4>
                <div class="list-group">
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('test')) ?>">Пройти тест</a>
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('test_tasks')) ?>">Тестовые задачи</a>
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('test_tasks_create')) ?>">Создать тестовую задачу</a>
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('test_results')) ?>">Логи тестирования</a>
                    <a class="list-group-item list-group-item-action" href="<?= e(pageUrl('test_export')) ?>">Экспорт логов тестирования</a>
                </div>
            </div>
        </div>
    </div>
</div>
