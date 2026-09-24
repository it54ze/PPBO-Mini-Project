<?php


declare(strict_types=1);

if (!defined('DATA_FILE')) {
    define('DATA_FILE', __DIR__ . '/data/tasks_test.dat');
}

require_once __DIR__ . '/bootstrap.php';

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

function section(string $title): void
{
    echo "\n=== {$title} ===\n";
}

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

function skip(string $description, string $reason): void
{
    TestStats::$skipped++;
    TestStats::$skipMessages[] = "{$description} -- {$reason}";
    echo "  [SKIP] {$description} -- {$reason}\n";
}

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

if (is_file(DATA_FILE)) {
    unlink(DATA_FILE);
}

echo "==================================================\n";
echo " MENJALANKAN TEST SUITE - TODO APP\n";
echo " File data test: " . DATA_FILE . "\n";
echo "==================================================\n";

runSection('P1 - Completable & Task', ['Completable', 'Task'], function (): void {

    check(
        'Task adalah abstract class (tidak bisa diinstansiasi langsung)',
        (new ReflectionClass('Task'))->isAbstract()
    );

    check(
        'Task mengimplementasikan interface Completable',
        in_array('Completable', class_implements('Task') ?: [], true)
    );

    $makeConcreteTask = function (int $id, string $title) {
    return new class($id, $title) extends Task {
        public function getDetail(): string
        {
            return $this->getTitle();
        }

        public function getType(): string
        {
            return 'Generic';
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


runSection('Integrasi - Alur Lengkap Aplikasi', [
    'TaskFactory', 'TaskList', 'Storage', 'SimpleTask', 'DeadlineTask', 'PriorityTask',
], function (): void {

    $integrationFile = __DIR__ . '/data/tasks_test_integration.dat';
    if (is_file($integrationFile)) {
        unlink($integrationFile);
    }

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

    $list->complete($t2->getId());
    check('Integrasi: task deadline yang sudah lewat tapi di-complete tidak lagi overdue', $t2->isOverdue() === false);

    $list->remove($t1->getId());
    checkEquals('Integrasi: count() berkurang setelah remove()', 2, $list->count());

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