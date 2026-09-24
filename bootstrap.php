<?php

declare(strict_types=1);

if (!defined('APP_DEBUG')) {
    $envDebug = getenv('APP_DEBUG');
    define('APP_DEBUG', $envDebug === false ? true : filter_var($envDebug, FILTER_VALIDATE_BOOLEAN));
}

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
}

date_default_timezone_set('Asia/Jakarta');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

if (!defined('SRC_PATH')) {
    define('SRC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'src');
}

if (!defined('DATA_PATH')) {
    define('DATA_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'data');
}

if (!defined('DATA_FILE')) {
    define('DATA_FILE', DATA_PATH . DIRECTORY_SEPARATOR . 'tasks.dat');
}

if (!is_dir(DATA_PATH)) {
    if (!mkdir(DATA_PATH, 0777, true) && !is_dir(DATA_PATH)) {
        throw new RuntimeException("Gagal membuat folder data di: " . DATA_PATH);
    }
}

const EXPECTED_CLASS_OWNERS = [
    'Completable'   => 'P1',
    'Task'          => 'P1',
    'SimpleTask'    => 'P2',
    'DeadlineTask'  => 'P2',
    'PriorityTask'  => 'P3',
    'TaskList'      => 'P4',
    'Storage'       => 'P5',
    'TaskFactory'   => 'P7',
];

spl_autoload_register(function (string $className): void {
    $file = SRC_PATH . DIRECTORY_SEPARATOR . $className . '.php';

    if (is_file($file)) {
        require_once $file;
        return;
    }

    $owner = EXPECTED_CLASS_OWNERS[$className] ?? null;

    $message = "Autoload gagal: class '{$className}' tidak ditemukan di "
        . str_replace(ROOT_PATH . DIRECTORY_SEPARATOR, '', $file) . ".";

    if ($owner !== null) {
        $message .= " File ini seharusnya dibuat oleh {$owner}. "
            . "Pastikan branch {$owner} sudah di-merge ke branch integrasi.";
    }

    throw new RuntimeException($message);
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('flash_set')) {
    function flash_set(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }
}

if (!function_exists('flash_get')) {
    function flash_get(string $type): ?string
    {
        if (!isset($_SESSION['flash'][$type])) {
            return null;
        }

        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);

        return $message;
    }
}

if (!function_exists('old_input')) {
    function old_input(string $key, string $default = ''): string
    {
        if (!isset($_SESSION['old_input'][$key])) {
            return $default;
        }

        $value = $_SESSION['old_input'][$key];
        return $value;
    }
}

if (!function_exists('set_old_input')) {
    function set_old_input(array $data): void
    {
        $_SESSION['old_input'] = $data;
    }
}

if (!function_exists('clear_old_input')) {
    function clear_old_input(): void
    {
        unset($_SESSION['old_input']);
    }
}