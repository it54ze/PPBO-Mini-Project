<?php

/**
 * tests.php
 *
 * Kerangka pengujian untuk aplikasi To-Do List OOP.
 * Dibuat oleh: P7 (kerangka: fungsi check(), throwsA(), runner, summary)
 * Diisi oleh: SELURUH ANGGOTA (setiap bagian ditandai nama pemilik di bawah)
 *
 * CARA MENJALANKAN:
 *   php tests.php
 *
 * ATURAN TIM (lihat dokumen pembagian tugas, Fase 3):
 *   `php tests.php` WAJIB dijalankan sebelum push, dan integrasi dianggap
 *   selesai hanya jika hasil akhirnya menunjukkan 0 FAILED.
 *
 * ----------------------------------------------------------------------
 * KONTRAK API YANG DIASUMSIKAN (PENTING - BACA SEBELUM PROTES TES GAGAL)
 * ----------------------------------------------------------------------
 * Karena file ini dibuat di Fase 1 (sebelum semua class lain ada), saya
 * (P7) menuliskan tes berdasarkan desain yang kita sepakati. Kalau nama
 * method/parameter kalian berbeda, ada 2 pilihan:
 *   (a) sesuaikan nama method di class kalian supaya cocok dengan tes ini, ATAU
 *   (b) edit bagian tes milik kalian di file ini (silakan, ini milik bersama).
 * JANGAN diam-diam menghapus tes yang gagal supaya terlihat hijau — itu
 * melanggar tujuan testing kita.
 *
 * Completable (interface): complete(): void, isCompleted(): bool
 * Task (abstract, implements Completable):
 *   - __construct(int $id, string $title)
 *   - getId(): int
 *   - getTitle(): string
 *   - setTitle(string $title): void
 *   - abstract getDetail(): string
 *   - TIDAK punya setId() -> ID immutable setelah dibuat
 *   - title kosong / hanya spasi -> InvalidArgumentException
 *   - title di-trim otomatis
 * SimpleTask extends Task
 * DeadlineTask extends Task:
 *   - __construct(int $id, string $title, string $deadline) format Y-m-d
 *   - format/tanggal salah -> InvalidArgumentException
 *   - isOverdue(): bool (false jika sudah completed, walau lewat deadline)
 * PriorityTask extends Task:
 *   - const LEVELS = ['low','medium','high']
 *   - __construct(int $id, string $title, string $priority)
 *   - priority di luar LEVELS -> InvalidArgumentException
 * TaskList:
 *   - nextId(): int
 *   - add(Task $task): void
 *   - find(int $id): ?Task
 *   - complete(int $id): void      (id tak ada -> Exception)
 *   - remove(int $id): void        (id tak ada -> Exception)
 *   - count(): int
 *   - getAll(): array
 * Storage:
 *   - __construct(string $filePath)
 *   - save(TaskList $list): void
 *   - load(): TaskList             (file belum ada -> TaskList kosong)
 * TaskFactory:
 *   - static create(TaskList $list, string $type, array $data): Task
 *     $type: 'simple' | 'deadline' | 'priority'
 *     $data: ['title' => ..., 'deadline' => ..., 'priority' => ...]
 *     - type tak dikenal -> InvalidArgumentException
 *     - otomatis assign id via $list->nextId() dan add() ke $list
 * ----------------------------------------------------------------------
 */

declare(strict_types=1);

// Pakai file data KHUSUS TEST supaya tidak menimpa data asli aplikasi.
// (Lihat bootstrap.php: DATA_FILE bisa di-override sebelum di-require)
if (!defined('DATA_FILE')) {
    define('DATA_FILE', __DIR__ . '/data/tasks_test.dat');
}

require_once __DIR__ . '/bootstrap.php';

// =========================================================================
// BAGIAN 1: FRAMEWORK TEST (Dibuat oleh P7)
// =========================================================================

final class TestStats
{
    public static int $passed = 0;
    public static int $failed = 0;
    public static int $skipped = 0;
    /** @var string[] */
    public static array $failureMessages = [];
    /** @var string[] */
    public static array $skipMessages = [];
}

/**
 * Mencetak header sebuah bagian tes.
 */
function section(string $title): void
{
    echo "\n=== {$title} ===\n";
}

/**
 * Assertion utama. Gunakan untuk mengecek sebuah kondisi boolean.
 *
 * Contoh:
 *   check('Judul task ter-trim dengan benar', $task->getTitle() === 'Beli susu');
 */
function check(string $description, bool $condition): bool
{
    if ($condition) {
        TestStats::$passed++;
        echo "  [PASS] {$description}\n";
        return true;
    }

    TestStats::$failed++;
    TestStats::$failureMessages[] = $description;
    echo "  [FAIL] {$description}\n";
    return false;
}

/**
 * Assertion nilai (biar pesan gagal lebih informatif daripada check() biasa).
 *
 * Contoh:
 *   checkEquals('Jumlah task setelah 2x add', 2, $taskList->count());
 */
function checkEquals(string $description, $expected, $actual): bool
{
    $isEqual = $expected === $actual;

    if (!$isEqual) {
        $description .= sprintf(
            ' (diharapkan: %s, didapat: %s)',
            var_export($expected, true),
            var_export($actual, true)
        );
    }

    return check($description, $isEqual);
}

/**
 * Assertion untuk memastikan sebuah callback melempar exception tertentu.
 *
 * Contoh:
 *   throwsA(InvalidArgumentException::class, function () {
 *       new SimpleTask(1, '');
 *   }, 'Judul kosong ditolak dengan InvalidArgumentException');
 */
function throwsA(string $expectedExceptionClass, callable $callback, string $description): bool
{
    try {
        $callback();
    } catch (Throwable $e) {
        if ($e instanceof $expectedExceptionClass) {
            return check($description, true);
        }

        return check(
            $description . " (malah melempar " . get_class($e) . ": " . $e->getMessage() . ")",
            false
        );
    }

    return check($description . " (tidak ada exception yang dilempar)", false);
}

/**
 * Menandai sebuah pengecekan dilewati (bukan gagal), biasanya karena
 * class yang dibutuhkan belum tersedia (branch belum di-merge).
 */
function skip(string $description, string $reason): void
{
    TestStats::$skipped++;
    TestStats::$skipMessages[] = "{$description} -- {$reason}";
    echo "  [SKIP] {$description} -- {$reason}\n";
}

/**
 * Menjalankan satu section pengujian dengan aman:
 *  - Jika class yang dibutuhkan belum ada -> di-skip, tidak menghentikan skrip.
 *  - Jika terjadi error tak terduga di dalam section -> dicatat sebagai FAILED,
 *    tapi section lain tetap lanjut jalan (integrasi tidak berhenti total
 *    hanya karena satu bagian error).
 *
 * @param string   $title            Nama section, misal "P1 - Task & Completable"
 * @param string[] $requiredClasses  Nama class/interface yang harus ada
 * @param callable $fn               Isi pengujian
 */
function runSection(string $title, array $requiredClasses, callable $fn): void
{
    section($title);

    $missing = array_values(array_filter(
        $requiredClasses,
        fn(string $c) => !class_exists($c) && !interface_exists($c)
    ));

    if (!empty($missing)) {
        skip($title, 'Class/interface belum tersedia: ' . implode(', ', $missing));
        return;
    }

    try {
        $fn();
    } catch (Throwable $e) {
        TestStats::$failed++;
        $msg = "{$title} -- FATAL: " . get_class($e) . ': ' . $e->getMessage()
            . ' (di ' . $e->getFile() . ':' . $e->getLine() . ')';
        TestStats::$failureMessages[] = $msg;
        echo "  [FATAL] " . get_class($e) . ": {$e->getMessage()} "
            . "(di baris {$e->getLine()})\n";
    }
}

// Bersihkan file data test dari sisa run sebelumnya, supaya setiap
// eksekusi `php tests.php` selalu mulai dari kondisi bersih.
if (is_file(DATA_FILE)) {
    unlink(DATA_FILE);
}

echo "==================================================\n";
echo " MENJALANKAN TEST SUITE - TODO APP\n";
echo " File data test: " . DATA_FILE . "\n";
echo "==================================================\n";

// =========================================================================
// BAGIAN 2: P1 - Completable & Task (Abstraction, Encapsulation, Property)
// =========================================================================

runSection('P1 - Completable & Task', ['Completable', 'Task'], function (): void {

    check(
        'Task adalah abstract class (tidak bisa diinstansiasi langsung)',
        (new ReflectionClass('Task'))->isAbstract()
    );

    check(
        'Task mengimplementasikan interface Completable',
        in_array('Completable', class_implements('Task') ?: [], true)
    );

    // Gunakan anonymous class untuk menguji Task secara terisolasi,
    // karena Task sendiri abstract dan butuh implementasi getDetail().
    $makeConcreteTask = function (int $id, string $title) {
        return new class($id, $title) extends Task {
            public function getType(): string
            {
                return 'Anonymous';
            }
        
            public function getDetail(): string
            {
                return $this->getTitle();
            }
        };
    };

    $task = $makeConcreteTask(1, 'Belajar PHP OOP');
    checkEquals('getId() mengembalikan id sesuai konstruktor', 1, $task->getId());
    checkEquals('getTitle() mengembalikan judul sesuai konstruktor', 'Belajar PHP OOP', $task->getTitle());

    $trimmedTask = $makeConcreteTask(2, '   Beli susu   ');
    checkEquals('Judul dengan spasi di awal/akhir otomatis di-trim', 'Beli susu', $trimmedTask->getTitle());

    throwsA(InvalidArgumentException::class, function () use ($makeConcreteTask): void {
        $makeConcreteTask(3, '');
    }, 'Judul kosong ditolak saat konstruksi (InvalidArgumentException)');

    throwsA(InvalidArgumentException::class, function () use ($makeConcreteTask): void {
        $makeConcreteTask(4, '     ');
    }, 'Judul yang hanya berisi spasi ditolak (InvalidArgumentException)');

    if (method_exists($task, 'setTitle')) {
        throwsA(InvalidArgumentException::class, function () use ($task): void {
            $task->setTitle('   ');
        }, 'setTitle() dengan judul kosong ditolak (InvalidArgumentException)');

        $task->setTitle('  Judul baru  ');
        checkEquals('setTitle() men-trim judul baru', 'Judul baru', $task->getTitle());
    } else {
        skip('setTitle() validasi judul kosong', 'method setTitle() tidak ditemukan di Task');
    }

    check(
        'ID task tidak bisa diubah setelah dibuat (tidak ada method setId)',
        !method_exists($task, 'setId')
    );

    check('Task baru berstatus belum selesai (isCompleted() === false)', $task->isCompleted() === false);
    $task->complete();
    check('Setelah complete() dipanggil, isCompleted() menjadi true', $task->isCompleted() === true);
});

// =========================================================================
// BAGIAN 3: P2 - SimpleTask & DeadlineTask (Inheritance, Overriding)
// =========================================================================

runSection('P2 - SimpleTask & DeadlineTask', ['SimpleTask', 'DeadlineTask', 'Task'], function (): void {

    $simple = new SimpleTask(10, 'Cuci piring');
    check('SimpleTask adalah instance dari Task', $simple instanceof Task);
    checkEquals('SimpleTask menyimpan judul dengan benar', 'Cuci piring', $simple->getTitle());
    check('SimpleTask::getDetail() mengembalikan string tidak kosong', $simple->getDetail() !== '');

    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $futureDeadline = new DeadlineTask(11, 'Kumpul tugas', $tomorrow);
    check('DeadlineTask instance dari Task', $futureDeadline instanceof Task);
    check('Deadline besok belum overdue', $futureDeadline->isOverdue() === false);

    $pastDeadline = new DeadlineTask(12, 'Bayar tagihan', $yesterday);
    check('Deadline kemarin dan belum selesai -> overdue = true', $pastDeadline->isOverdue() === true);

    $pastDeadline->complete();
    check('Deadline kemarin tapi SUDAH completed -> overdue = false', $pastDeadline->isOverdue() === false);

    throwsA(InvalidArgumentException::class, function (): void {
        new DeadlineTask(13, 'Format salah', '31-12-2026');
    }, 'Format tanggal salah (bukan Y-m-d) ditolak (InvalidArgumentException)');

    throwsA(InvalidArgumentException::class, function (): void {
        new DeadlineTask(14, 'Tanggal tidak valid', '2026-02-30');
    }, 'Tanggal kalender tidak valid, mis. 30 Februari, ditolak (InvalidArgumentException)');

    throwsA(InvalidArgumentException::class, function (): void {
        new DeadlineTask(15, 'Bukan tanggal sama sekali', 'kemarin-lusa');
    }, 'String acak (bukan format tanggal) ditolak (InvalidArgumentException)');

    check(
        'SimpleTask::getDetail() dan DeadlineTask::getDetail() berbeda isinya (overriding bekerja)',
        $simple->getDetail() !== $futureDeadline->getDetail()
    );
});

// =========================================================================
// BAGIAN 4: P3 - PriorityTask (Class Constants & Validation)
// =========================================================================

runSection('P3 - PriorityTask', ['PriorityTask', 'Task'], function (): void {

    check(
        'PriorityTask punya konstanta LEVELS berupa array tidak kosong',
        defined('PriorityTask::LEVELS') && is_array(PriorityTask::LEVELS) && count(PriorityTask::LEVELS) > 0
    );

    $levels = PriorityTask::LEVELS;
    $validLevel = $levels[0] ?? 'low';

    $priorityTask = new PriorityTask(20, 'Tugas penting', $validLevel);
    check('PriorityTask adalah instance dari Task', $priorityTask instanceof Task);
    check(
        "PriorityTask::getDetail() memuat informasi priority ('{$validLevel}')",
        stripos($priorityTask->getDetail(), (string) $validLevel) !== false
    );

    throwsA(InvalidArgumentException::class, function (): void {
        new PriorityTask(21, 'Prioritas ngawur', 'super-urgent-banget');
    }, 'Priority di luar daftar LEVELS ditolak (InvalidArgumentException)');

    throwsA(InvalidArgumentException::class, function (): void {
        new PriorityTask(22, 'Prioritas kosong', '');
    }, 'Priority string kosong ditolak (InvalidArgumentException)');
});

// =========================================================================
// BAGIAN 5: P4 - TaskList (Collection & Type Hinting)
// =========================================================================

runSection('P4 - TaskList', ['TaskList', 'SimpleTask'], function (): void {

    $list = new TaskList();
    checkEquals('TaskList baru kosong (count() === 0)', 0, $list->count());

    $firstId = $list->nextId();
    $list->add(new SimpleTask($firstId, 'Task pertama'));
    checkEquals('Setelah 1x add(), count() === 1', 1, $list->count());

    $secondId = $list->nextId();
    check('nextId() menghasilkan id berbeda setiap dipanggil setelah add()', $secondId !== $firstId);
    $list->add(new SimpleTask($secondId, 'Task kedua'));
    checkEquals('Setelah 2x add(), count() === 2', 2, $list->count());

    $found = $list->find($firstId);
    check('find() menemukan task dengan id yang benar', $found !== null && $found->getId() === $firstId);

    $list->complete($firstId);
    check('complete(id) menandai task sesuai id sebagai selesai', $list->find($firstId)->isCompleted() === true);

    $list->remove($secondId);
    checkEquals('Setelah remove(), count() berkurang jadi 1', 1, $list->count());
    check('find() mengembalikan null untuk id yang sudah dihapus', $list->find($secondId) === null);

    throwsA(Exception::class, function () use ($list): void {
        $list->complete(9999);
    }, 'complete() dengan id yang tidak ada melempar Exception');

    throwsA(Exception::class, function () use ($list): void {
        $list->remove(9999);
    }, 'remove() dengan id yang tidak ada melempar Exception');

    check(
        'getAll() mengembalikan array berisi seluruh task tersisa',
        is_array($list->getAll()) && count($list->getAll()) === $list->count()
    );
});

// =========================================================================
// BAGIAN 6: P5 - Storage (Persistence & Exception Handling)
// =========================================================================

runSection('P5 - Storage', ['Storage', 'TaskList', 'SimpleTask', 'DeadlineTask'], function (): void {

    $storageFile = __DIR__ . '/data/tasks_test_storage.dat';
    if (is_file($storageFile)) {
        unlink($storageFile);
    }

    $storage = new Storage($storageFile);

    $emptyLoad = $storage->load();
    check(
        'load() pada file yang belum ada mengembalikan TaskList kosong (bukan error)',
        $emptyLoad instanceof TaskList && $emptyLoad->count() === 0
    );

    $original = new TaskList();
    $id1 = $original->nextId();
    $original->add(new SimpleTask($id1, 'Task untuk disimpan'));
    $id2 = $original->nextId();
    $original->add(new DeadlineTask($id2, 'Task dengan deadline', date('Y-m-d', strtotime('+3 day'))));

    $storage->save($original);
    check('File data berhasil dibuat setelah save()', is_file($storageFile));

    $reloaded = (new Storage($storageFile))->load();
    checkEquals('Jumlah task setelah load() sama dengan sebelum save()', $original->count(), $reloaded->count());

    $reloadedTask1 = $reloaded->find($id1);
    check(
        'Tipe class task tetap SimpleTask setelah serialize/unserialize (polymorphism terjaga)',
        $reloadedTask1 instanceof SimpleTask
    );

    $reloadedTask2 = $reloaded->find($id2);
    check(
        'Tipe class task tetap DeadlineTask setelah serialize/unserialize',
        $reloadedTask2 instanceof DeadlineTask
    );

    check(
        'Judul task tetap sama persis setelah round-trip save/load',
        $reloadedTask1 !== null && $reloadedTask1->getTitle() === 'Task untuk disimpan'
    );

    if (is_file($storageFile)) {
        unlink($storageFile);
    }
});

// =========================================================================
// BAGIAN 7: P7 - TaskFactory (Factory Pattern & Static Method)
// =========================================================================

runSection('P7 - TaskFactory', ['TaskFactory', 'TaskList', 'SimpleTask', 'DeadlineTask', 'PriorityTask'], function (): void {

    check(
        'TaskFactory::create adalah static method',
        (new ReflectionMethod('TaskFactory', 'create'))->isStatic()
    );

    $list = new TaskList();

    $simple = TaskFactory::create($list, 'simple', ['title' => 'Dibuat via factory']);
    check('Factory type "simple" menghasilkan instance SimpleTask', $simple instanceof SimpleTask);
    checkEquals('Task hasil factory otomatis masuk ke TaskList', 1, $list->count());

    $deadline = TaskFactory::create($list, 'deadline', [
        'title' => 'Deadline via factory',
        'deadline' => date('Y-m-d', strtotime('+5 day')),
    ]);
    check('Factory type "deadline" menghasilkan instance DeadlineTask', $deadline instanceof DeadlineTask);

    $priority = TaskFactory::create($list, 'priority', [
        'title' => 'Priority via factory',
        'priority' => PriorityTask::LEVELS[0] ?? 'low',
    ]);
    check('Factory type "priority" menghasilkan instance PriorityTask', $priority instanceof PriorityTask);

    checkEquals('Total task di TaskList setelah 3x create via factory', 3, $list->count());

    check(
        'ID yang diberikan factory ke tiap task unik satu sama lain',
        count(array_unique([$simple->getId(), $deadline->getId(), $priority->getId()])) === 3
    );

    throwsA(InvalidArgumentException::class, function () use ($list): void {
        TaskFactory::create($list, 'jenis-tidak-dikenal', ['title' => 'Gagal']);
    }, 'Tipe task tidak dikenal ditolak factory (InvalidArgumentException)');
});

// =========================================================================
// BAGIAN 8: P6 - Polymorphism & Keamanan Output (getDetail(), htmlspecialchars)
// =========================================================================

runSection('P6 - Polymorphism & Keamanan Output', ['SimpleTask', 'DeadlineTask', 'PriorityTask'], function (): void {

    $tasks = [
        new SimpleTask(90, 'Simple'),
        new DeadlineTask(91, 'Deadline', date('Y-m-d', strtotime('+1 day'))),
        new PriorityTask(92, 'Priority', PriorityTask::LEVELS[0] ?? 'low'),
    ];

    $details = array_map(fn(Task $t) => $t->getDetail(), $tasks);

    check(
        'Setiap subclass Task menghasilkan getDetail() yang tidak kosong (polymorphism)',
        count(array_filter($details, fn($d) => trim((string) $d) !== '')) === count($tasks)
    );

    check(
        'getDetail() pada tiap subclass menghasilkan output yang berbeda satu sama lain',
        count(array_unique($details)) === count($details)
    );

    if (function_exists('e')) {
        $dangerous = '<script>alert("xss")</script>';
        $escaped = e($dangerous);
        check('Fungsi e() meng-escape tag <script> (mencegah XSS)', strpos($escaped, '<script>') === false);
        check('Fungsi e() menghasilkan entitas HTML yang benar', strpos($escaped, '&lt;script&gt;') !== false);
    } else {
        skip('Uji fungsi e() untuk keamanan output', 'fungsi e() tidak ditemukan di bootstrap.php');
    }
});

// =========================================================================
// BAGIAN 9: INTEGRASI (Dijalankan P7 di Fase 3 - alur end-to-end)
// =========================================================================

runSection('Integrasi - Alur Lengkap Aplikasi', [
    'TaskFactory', 'TaskList', 'Storage', 'SimpleTask', 'DeadlineTask', 'PriorityTask',
], function (): void {

    $integrationFile = __DIR__ . '/data/tasks_test_integration.dat';
    if (is_file($integrationFile)) {
        unlink($integrationFile);
    }

    // 1. Buat TaskList kosong, tambahkan 3 jenis task lewat Factory.
    $list = new TaskList();
    $t1 = TaskFactory::create($list, 'simple', ['title' => 'Belanja bulanan']);
    $t2 = TaskFactory::create($list, 'deadline', [
        'title' => 'Laporan mingguan',
        'deadline' => date('Y-m-d', strtotime('-1 day')), // sengaja lewat deadline
    ]);
    $t3 = TaskFactory::create($list, 'priority', [
        'title' => 'Meeting klien',
        'priority' => PriorityTask::LEVELS[count(PriorityTask::LEVELS) - 1] ?? 'high',
    ]);

    checkEquals('Integrasi: 3 task berhasil dibuat via factory', 3, $list->count());

    // 2. Selesaikan salah satu task, cek isOverdue() jadi false setelahnya.
    $list->complete($t2->getId());
    check('Integrasi: task deadline yang sudah lewat tapi di-complete tidak lagi overdue', $t2->isOverdue() === false);

    // 3. Hapus salah satu task.
    $list->remove($t1->getId());
    checkEquals('Integrasi: count() berkurang setelah remove()', 2, $list->count());

    // 4. Simpan ke Storage, lalu muat ulang dari file (simulasi request baru).
    $storage = new Storage($integrationFile);
    $storage->save($list);
    $reloadedList = (new Storage($integrationFile))->load();

    checkEquals('Integrasi: jumlah task konsisten setelah save & load', $list->count(), $reloadedList->count());

    $reloadedT2 = $reloadedList->find($t2->getId());
    check(
        'Integrasi: status completed task tetap tersimpan setelah reload',
        $reloadedT2 !== null && $reloadedT2->isCompleted() === true
    );

    $reloadedT3 = $reloadedList->find($t3->getId());
    check(
        'Integrasi: tipe class PriorityTask tetap terjaga end-to-end (factory -> list -> storage -> reload)',
        $reloadedT3 instanceof PriorityTask
    );

    if (is_file($integrationFile)) {
        unlink($integrationFile);
    }
});

// =========================================================================
// BAGIAN 10: RINGKASAN HASIL (Dibuat oleh P7)
// =========================================================================

if (is_file(DATA_FILE)) {
    unlink(DATA_FILE);
}

echo "\n==================================================\n";
echo " RINGKASAN HASIL TEST\n";
echo "==================================================\n";
echo "  Passed  : " . TestStats::$passed . "\n";
echo "  Failed  : " . TestStats::$failed . "\n";
echo "  Skipped : " . TestStats::$skipped . "\n";

if (TestStats::$failed > 0) {
    echo "\n  Daftar kegagalan:\n";
    foreach (TestStats::$failureMessages as $i => $msg) {
        echo "   " . ($i + 1) . ". {$msg}\n";
    }
}

if (TestStats::$skipped > 0) {
    echo "\n  Daftar yang di-skip (class belum tersedia):\n";
    foreach (TestStats::$skipMessages as $i => $msg) {
        echo "   " . ($i + 1) . ". {$msg}\n";
    }
}

echo "\n";

if (TestStats::$failed > 0) {
    echo "HASIL AKHIR: GAGAL (" . TestStats::$failed . " test tidak lolos). Jangan push dulu.\n";
    exit(1);
}

if (TestStats::$skipped > 0) {
    echo "HASIL AKHIR: BELUM LENGKAP (" . TestStats::$skipped . " bagian di-skip karena class belum ada).\n";
    exit(0);
}

echo "HASIL AKHIR: SEMUA TEST LOLOS. 0 FAILED. Siap untuk integrasi/push.\n";
exit(0);