<?php

/**
 * bootstrap.php
 *
 * File ini adalah titik masuk konfigurasi aplikasi To-Do List.
 * Tanggung jawab file ini HANYA:
 *   1. Menyiapkan environment (error reporting, timezone, session).
 *   2. Mendefinisikan konstanta path yang dipakai seluruh anggota tim.
 *   3. Mendaftarkan autoloader untuk seluruh class di folder src/.
 *   4. Menyediakan helper function kecil yang dipakai lintas file
 *      (index.php, actions.php, tests.php).
 *
 * File ini TIDAK membuat objek Storage atau TaskList secara otomatis,
 * supaya:
 *   - tests.php bisa pakai file data terpisah (tidak menimpa data asli),
 *   - index.php / actions.php bebas menentukan sendiri kapan Storage
 *     dibuat dan file data mana yang dipakai.
 *
 * Dipakai oleh:
 *   - index.php   (P6)
 *   - actions.php (P5)
 *   - tests.php   (P7 + semua anggota)
 *
 * @author P7
 */

declare(strict_types=1);

// =========================================================================
// 1. ERROR REPORTING & ENVIRONMENT
// =========================================================================

/**
 * Set APP_DEBUG=false lewat environment variable saat deployment production.
 * Default: true (mode pengembangan/kuliah), supaya error langsung kelihatan.
 */
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

// =========================================================================
// 2. KONSTANTA PATH
// =========================================================================

if (!defined('ROOT_PATH')) {
    // Folder root project (tempat bootstrap.php ini berada)
    define('ROOT_PATH', __DIR__);
}

if (!defined('SRC_PATH')) {
    define('SRC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'src');
}

if (!defined('DATA_PATH')) {
    define('DATA_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'data');
}

if (!defined('DATA_FILE')) {
    // File tempat TaskList disimpan (dipakai oleh Storage.php milik P5).
    // tests.php sebaiknya OVERRIDE konstanta ini SEBELUM require bootstrap.php,
    // contoh:
    //   define('DATA_FILE', __DIR__ . '/data/tasks_test.dat');
    //   require __DIR__ . '/bootstrap.php';
    define('DATA_FILE', DATA_PATH . DIRECTORY_SEPARATOR . 'tasks.dat');
}

// Pastikan folder data/ selalu ada, supaya Storage.php tidak perlu
// pusing menangani "folder belum dibuat" saat pertama kali clone repo.
if (!is_dir(DATA_PATH)) {
    if (!mkdir(DATA_PATH, 0777, true) && !is_dir(DATA_PATH)) {
        throw new RuntimeException("Gagal membuat folder data di: " . DATA_PATH);
    }
}

// =========================================================================
// 3. AUTOLOADER
// =========================================================================

/**
 * Daftar class yang WAJIB ada di src/ sesuai pembagian tugas.
 * Dipakai untuk validasi & pesan error yang informatif saat integrasi
 * (Fase 3), supaya kalau ada anggota yang belum push filenya, pesan
 * errornya jelas menyebut nama file & nama PIC-nya.
 */
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

    // Class tidak ditemukan: beri pesan error yang membantu proses integrasi,
    // bukan sekadar "Class not found" bawaan PHP yang bikin bingung.
    $owner = EXPECTED_CLASS_OWNERS[$className] ?? null;

    $message = "Autoload gagal: class '{$className}' tidak ditemukan di "
        . str_replace(ROOT_PATH . DIRECTORY_SEPARATOR, '', $file) . ".";

    if ($owner !== null) {
        $message .= " File ini seharusnya dibuat oleh {$owner}. "
            . "Pastikan branch {$owner} sudah di-merge ke branch integrasi.";
    }

    throw new RuntimeException($message);
});

// =========================================================================
// 4. SESSION (untuk flash message setelah redirect di actions.php)
// =========================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================================================================
// 5. HELPER FUNCTIONS LINTAS FILE
// =========================================================================

if (!function_exists('e')) {
    /**
     * Escape string untuk output HTML yang aman (mencegah XSS).
     * Dipakai P6 di index.php setiap menampilkan data task ke browser.
     *
     * @param mixed $value Nilai yang akan ditampilkan (akan di-cast ke string)
     */
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('flash_set')) {
    /**
     * Menyimpan pesan flash (sekali tampil) ke session.
     * Dipakai P5 di actions.php sebelum redirect, contoh:
     *   flash_set('success', 'Task berhasil ditambahkan.');
     *   header('Location: index.php');
     *   exit;
     *
     * @param string $type  'success' | 'error' | 'info'
     * @param string $message
     */
    function flash_set(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }
}

if (!function_exists('flash_get')) {
    /**
     * Mengambil sekaligus menghapus pesan flash dari session.
     * Dipakai P6 di index.php untuk menampilkan notifikasi.
     *
     * @param string $type 'success' | 'error' | 'info'
     * @return string|null
     */
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
    /**
     * Mengambil kembali input form sebelumnya jika terjadi error validasi
     * (dipakai actions.php + index.php agar user tidak perlu ngetik ulang).
     *
     * @param string $key
     * @param string $default
     */
    function old_input(string $key, string $default = ''): string
    {
        if (!isset($_SESSION['old_input'][$key])) {
            return $default;
        }

        $value = $_SESSION['old_input'][$key];
        // Sengaja tidak langsung dihapus di sini karena bisa dipanggil
        // berkali-kali dalam satu request render form. Dibersihkan oleh
        // clear_old_input() setelah form selesai dirender, atau otomatis
        // saat form berhasil disubmit ulang.
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