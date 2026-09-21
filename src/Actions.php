<?php

require_once __DIR__ . '/bootstrap.php';

$storage = new Storage();
$list = $storage->load();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $type = $_POST['type'] ?? 'simple';
            $extra = '';
            if ($type === 'deadline') {
                $extra = $_POST['deadline'] ?? '';
            } elseif ($type === 'priority') {
                $extra = $_POST['priority'] ?? '';
            }
            $list->add(TaskFactory::create($type, $_POST['title'] ?? '', $extra));
            $_SESSION['flash'] = ['ok', 'Tugas berhasil ditambahkan.'];
            break;

        case 'done':
            $list->complete((int) ($_POST['id'] ?? 0));
            $_SESSION['flash'] = ['ok', 'Tugas ditandai selesai.'];
            break;

        case 'delete':
            $list->remove((int) ($_POST['id'] ?? 0));
            $_SESSION['flash'] = ['ok', 'Tugas dihapus.'];
            break;
    }
    $storage->save($list);
} catch (InvalidArgumentException $e) {
    $_SESSION['flash'] = ['error', $e->getMessage()];
}

header('Location: index.php');
exit;