<?php

declare(strict_types=1);

putenv('DATABASE_PATH=' . (sys_get_temp_dir() . '/minirank_test_' . bin2hex(random_bytes(6)) . '.db'));

require __DIR__ . '/../app/bootstrap.php';