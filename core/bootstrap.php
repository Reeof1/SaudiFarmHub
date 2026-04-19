<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'Core\\' => dirname(__DIR__) . '/core/',
        'Controllers\\' => dirname(__DIR__) . '/app/controllers/',
        'Models\\' => dirname(__DIR__) . '/app/models/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($class, $prefix, $len) !== 0) {
            continue;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/core/helpers.php';

