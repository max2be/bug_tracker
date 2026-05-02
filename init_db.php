<?php

declare(strict_types=1);

require __DIR__ . '/app/db.php';
require __DIR__ . '/app/helpers.php';
require __DIR__ . '/app/metrics.php';

initializeDatabase();

echo "Database initialized: " . DB_PATH . PHP_EOL;
