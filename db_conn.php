<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

// Legacy compatibility alias for older code that expected a connection variable.
$conn = $pdo;
