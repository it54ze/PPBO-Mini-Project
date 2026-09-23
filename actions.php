<?php

require_once __DIR__ . '/bootstrap.php';

$storage = new Storage(DATA_FILE);
$list = $storage->load();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $type = $_POST['type'] ?? 'simple';
            $data = ['title' => $_POST['title'] ?? ''];

            if ($type === 'deadline') {
                $data['deadline'] = $_POST['deadline'] ?? '';
            } elseif ($type === 'priority') {
                $data['priority'] = $_POST['priority'] ?? '';
            }

            // TaskFactory::create() sudah otomatis add() ke $list,
            // jadi TIDAK perlu $list->add(...) lagi di sini.
            TaskFactory::create($list, $type, $data);
            flash_set('success', 'Tugas berhasil ditambahkan.');
            break;

        case 'done':
            $list->complete((int) ($_POST['id'] ?? 0));
            flash_set('success', 'Tugas ditandai selesai.');
            break;

        case 'delete':
            $list->remove((int) ($_POST['id'] ?? 0));
            flash_set('success', 'Tugas dihapus.');
            break;
    }
    $storage->save($list);
} catch (InvalidArgumentException $e) {
    // Input tidak valid: judul kosong, format tanggal salah, prioritas tidak dikenal, dll.
    flash_set('error', $e->getMessage());
} catch (Exception $e) {
    // complete()/remove() pada id yang tidak ditemukan.
    flash_set('error', $e->getMessage());
}

header('Location: index.php');
exit;