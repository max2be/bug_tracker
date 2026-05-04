# QA Metrics MVP on PHP

Простое внутреннее приложение на `PHP 8 + SQLite + PDO + Bootstrap 5 + Chart.js`.

## Что умеет

- создавать задачи разработки в разрезе `DEMAND`
- создавать баги с привязкой к `DEMAND` и задаче разработки
- показывать списки задач и багов с фильтрами
- считать метрики качества
- строить графики
- экспортировать отчет в `XLS`
- сохранять старый MVP для тестирования программиста

## Структура

```text
.
├── app
│   ├── db.php
│   ├── helpers.php
│   └── metrics.php
├── public
│   └── app.css
├── views
│   ├── bugs
│   ├── charts
│   ├── layout
│   ├── metrics
│   ├── tasks
│   └── testing
├── node_modules
├── package.json
├── storage
│   └── app.sqlite
├── index.php
├── composer.json
├── init_db.php
├── seed.php
└── README.md
```

## Установка зависимостей

Если `composer` есть в системе:

```bash
composer install
```

В этом MVP внешние PHP-библиотеки не обязательны: экспорт сделан быстрым HTML `XLS`.

Для локальных фронтенд-ассетов нужны npm-зависимости:

```bash
npm install
```

## Создание базы

```bash
php init_db.php
```

База появится в `storage/app.sqlite`.

## Наполнение тестовыми данными

```bash
php seed.php
```

Seed добавляет:

- `DEMAND-999999`
- задачи `KARMADEV-111111`, `KARMADEV-222222`
- несколько багов на разные месяцы
- тестовую задачу `KARMADEV-999999` для старого MVP тестирования

## Локальный запуск

```bash
php -S localhost:8000
```

## Какие страницы открыть

- `http://localhost:8000/`
- `http://localhost:8000/?page=tasks`
- `http://localhost:8000/?page=bugs`
- `http://localhost:8000/?page=metrics`
- `http://localhost:8000/?page=charts`
- `http://localhost:8000/?page=export`
- `http://localhost:8000/?page=test`

## Основные страницы

- `/?page=home`
- `/?page=tasks`
- `/?page=tasks_create`
- `/?page=tasks_view&id=1`
- `/?page=tasks_edit&id=1`
- `/?page=bugs`
- `/?page=bugs_create`
- `/?page=bugs_view&id=1`
- `/?page=bugs_edit&id=1`
- `/?page=metrics`
- `/?page=charts`
- `/?page=export`

## Старый MVP тестирования

- `/?page=test`
- `/?page=test_tasks`
- `/?page=test_tasks_create`
- `/?page=test_results`

## Нормализация кодов

Приложение автоматически приводит:

- `999999` -> `DEMAND-999999` для DEMAND
- `999999` -> `KARMADEV-999999` для задач

## Экспорт

Экспорт доступен по `/?page=export`.

Файл скачивается как:

`defects-report-YYYY-MM-DD.xls`
