<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Метрики качества</h1>
        <p class="text-muted mb-0">Агрегаты по задачам, багам и покрытию тестированием.</p>
    </div>
    <a href="<?= e(pageUrl('export', $filters)) ?>" class="btn btn-success">Экспорт в XLS</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">
            <input type="hidden" name="page" value="metrics">
            <div class="col-md-3">
                <label class="form-label">Период от</label>
                <input type="date" name="from" class="form-control" value="<?= e($filters['from']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Период до</label>
                <input type="date" name="to" class="form-control" value="<?= e($filters['to']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">DEMAND</label>
                <input type="text" name="demand" class="form-control" value="<?= e($filters['demand']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Ответственный разработчик</label>
                <input type="text" name="responsible_developer" class="form-control" value="<?= e($filters['responsible_developer']) ?>">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary">Применить</button>
                <a href="<?= e(pageUrl('metrics')) ?>" class="btn btn-outline-secondary">Сбросить</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php
    $metricCards = [
        'Всего DEMAND' => $metrics['demands_count'],
        'Всего задач' => $metrics['tasks_count'],
        'Всего багов' => $metrics['bugs_count'],
        'Всего часов разработки' => $metrics['development_hours_sum'],
        'Всего часов исправления' => $metrics['fix_hours_sum'],
        'Defects per 40h' => $metrics['defects_per_40h'],
        'Средние часы исправления бага' => $metrics['average_fix_hours'],
        'Bug Fix Ratio (%)' => $metrics['bug_fix_ratio'],
        'Доля задач с вводным тестированием (%)' => $metrics['intro_testing_coverage'],
        'Среднее количество тест-сценариев на задачу' => $metrics['test_scenarios_per_task'],
        'Bugs per Test Scenario' => $metrics['bugs_per_test_scenario'],
    ];
    ?>
    <?php foreach ($metricCards as $label => $value): ?>
        <div class="col-md-4 col-xl-3">
            <div class="card shadow-sm summary-card">
                <div class="card-body">
                    <div class="text-muted"><?= e($label) ?></div>
                    <div class="summary-value"><?= e($value) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h3 class="mb-3">Метрики по DEMAND</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>DEMAND</th>
                    <th>Задачи</th>
                    <th>Баги</th>
                    <th>Часы разработки</th>
                    <th>Часы исправления</th>
                    <th>Defects per 40h</th>
                    <th>Average Fix Hours</th>
                    <th>Bug Fix Ratio</th>
                    <th>Intro Testing Coverage</th>
                    <th>Test Scenarios per Task</th>
                    <th>Bugs per Test Scenario</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$demandMetrics): ?>
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">Нет данных по выбранным фильтрам.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($demandMetrics as $row): ?>
                    <tr>
                        <td><?= e($row['demand_code']) ?></td>
                        <td><?= e($row['tasks_count']) ?></td>
                        <td><?= e($row['bugs_count']) ?></td>
                        <td><?= e($row['development_hours_sum']) ?></td>
                        <td><?= e($row['fix_hours_sum']) ?></td>
                        <td><?= e($row['defects_per_40h']) ?></td>
                        <td><?= e($row['average_fix_hours']) ?></td>
                        <td><?= e($row['bug_fix_ratio']) ?>%</td>
                        <td><?= e($row['intro_testing_coverage']) ?>%</td>
                        <td><?= e($row['test_scenarios_per_task']) ?></td>
                        <td><?= e($row['bugs_per_test_scenario']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
