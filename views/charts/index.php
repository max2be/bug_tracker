<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Графики</h1>
        <p class="text-muted mb-0">Визуализация багов, причин и метрик по DEMAND.</p>
    </div>
    <a href="<?= e(pageUrl('metrics', $filters)) ?>" class="btn btn-outline-secondary">К метрикам</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">
            <input type="hidden" name="page" value="charts">
            <div class="col-md-4">
                <label class="form-label">Период от</label>
                <input type="date" name="from" class="form-control" value="<?= e($filters['from']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Период до</label>
                <input type="date" name="to" class="form-control" value="<?= e($filters['to']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">DEMAND</label>
                <input type="text" name="demand" class="form-control" value="<?= e($filters['demand']) ?>">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary">Применить</button>
                <a href="<?= e(pageUrl('charts')) ?>" class="btn btn-outline-secondary">Сбросить</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm chart-box">
            <div class="card-body">
                <h4>Баги по месяцам</h4>
                <canvas id="bugsByMonthChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card shadow-sm chart-box">
            <div class="card-body">
                <h4>Баги по месяцам и DEMAND</h4>
                <canvas id="bugsByMonthDemandChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm chart-box">
            <div class="card-body">
                <h4>Defects per 40h по DEMAND</h4>
                <canvas id="defectsPerDemandChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm chart-box">
            <div class="card-body">
                <h4>Причины багов</h4>
                <canvas id="bugReasonsChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm chart-box">
            <div class="card-body">
                <h4>Этапы обнаружения багов</h4>
                <canvas id="foundStagesChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm chart-box">
            <div class="card-body">
                <h4>Часы разработки vs часы исправления по DEMAND</h4>
                <canvas id="devVsFixChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const bugsByMonth = <?= json_encode($chartData['bugs_by_month'], JSON_UNESCAPED_UNICODE) ?>;
    const bugsByMonthDemand = <?= json_encode($chartData['bugs_by_month_demand'], JSON_UNESCAPED_UNICODE) ?>;
    const defectsPerDemand = <?= json_encode($chartData['defects_per_40h_by_demand'], JSON_UNESCAPED_UNICODE) ?>;
    const bugReasons = <?= json_encode($chartData['bug_reasons'], JSON_UNESCAPED_UNICODE) ?>;
    const foundStages = <?= json_encode($chartData['found_stages'], JSON_UNESCAPED_UNICODE) ?>;
    const devVsFix = <?= json_encode($chartData['dev_vs_fix_by_demand'], JSON_UNESCAPED_UNICODE) ?>;

    new Chart(document.getElementById('bugsByMonthChart'), {
        type: 'bar',
        data: {
            labels: bugsByMonth.map(item => item.month),
            datasets: [{
                label: 'Баги',
                data: bugsByMonth.map(item => Number(item.bugs_count)),
                backgroundColor: '#dc3545'
            }]
        }
    });

    const stackedMonths = [...new Set(bugsByMonthDemand.map(item => item.month))];
    const stackedDemands = [...new Set(bugsByMonthDemand.map(item => item.demand_code))];
    const stackedDatasets = stackedDemands.map((demandCode, index) => ({
        label: demandCode,
        data: stackedMonths.map(month => {
            const match = bugsByMonthDemand.find(item => item.month === month && item.demand_code === demandCode);
            return match ? Number(match.bugs_count) : 0;
        }),
        backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#dc3545', '#6f42c1', '#198754'][index % 6]
    }));

    new Chart(document.getElementById('bugsByMonthDemandChart'), {
        type: 'bar',
        data: {
            labels: stackedMonths,
            datasets: stackedDatasets
        },
        options: {
            responsive: true,
            scales: {
                x: { stacked: true },
                y: { stacked: true }
            }
        }
    });

    new Chart(document.getElementById('defectsPerDemandChart'), {
        type: 'bar',
        data: {
            labels: defectsPerDemand.map(item => item.label),
            datasets: [{
                label: 'Defects per 40h',
                data: defectsPerDemand.map(item => Number(item.value)),
                backgroundColor: '#0d6efd'
            }]
        }
    });

    new Chart(document.getElementById('bugReasonsChart'), {
        type: 'bar',
        data: {
            labels: bugReasons.map(item => item.label || 'Не указано'),
            datasets: [{
                label: 'Количество багов',
                data: bugReasons.map(item => Number(item.value)),
                backgroundColor: '#fd7e14'
            }]
        }
    });

    new Chart(document.getElementById('foundStagesChart'), {
        type: 'bar',
        data: {
            labels: foundStages.map(item => item.label || 'Не указано'),
            datasets: [{
                label: 'Количество багов',
                data: foundStages.map(item => Number(item.value)),
                backgroundColor: '#20c997'
            }]
        }
    });

    new Chart(document.getElementById('devVsFixChart'), {
        type: 'bar',
        data: {
            labels: devVsFix.map(item => item.label),
            datasets: [
                {
                    label: 'Часы разработки',
                    data: devVsFix.map(item => Number(item.development_hours_sum)),
                    backgroundColor: '#0d6efd'
                },
                {
                    label: 'Часы исправления',
                    data: devVsFix.map(item => Number(item.fix_hours_sum)),
                    backgroundColor: '#dc3545'
                }
            ]
        },
        options: {
            responsive: true
        }
    });
});
</script>
