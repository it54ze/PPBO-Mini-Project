<?php
require_once __DIR__ . '/bootstrap.php';

$storage = new Storage(DATA_FILE);
$list = $storage->load();
$tasks = $list->getAll();

$successMessage = flash_get('success');
$errorMessage = flash_get('error');

$today = new DateTime('today');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>To-Do List OOP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

    <div class="navbar">
        <div class="logo">Tugas<span>Ku</span></div>
        <div class="profile">P</div>
    </div>

    <div class="hero">
        <div>
            <h1>To-Do List</h1>
            <p><?= count($tasks) ?> tugas · <?= $list->count() > 0 ? count(array_filter($tasks, fn($t) => $t->isCompleted())) : 0 ?> selesai</p>
        </div>
        <div class="quote">
            "Kerjakan yang penting dulu, bukan yang cepat dulu."
            <small>Mini Project PBO</small>
        </div>
    </div>

    <?php if ($successMessage): ?>
        <div class="alert success"><?= e($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert error"><?= e($errorMessage) ?></div>
    <?php endif; ?>

    <div class="dashboard">

        <div>
            <div class="add-task">
                <form method="post" action="actions.php">
                    <input type="hidden" name="action" value="add">
                    <input type="text" name="title" placeholder="Judul tugas baru..." required>

                    <select name="type" id="type">
                        <option value="simple">Biasa</option>
                        <option value="deadline">Deadline</option>
                        <option value="priority">Prioritas</option>
                    </select>

                    <input type="date" name="deadline" id="deadline" hidden>

                    <select name="priority" id="priority" hidden>
                        <?php foreach (PriorityTask::LEVELS as $level): ?>
                            <option value="<?= e($level) ?>"><?= e($level) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Tambah Tugas</button>
                </form>
            </div>

            <div class="task-section">
                <div class="section-header">
                    <h2>Daftar Tugas</h2>
                    <div class="filters">
                        <button type="button" class="filter active">Semua</button>
                        <button type="button" class="filter">Belum selesai</button>
                        <button type="button" class="filter">Selesai</button>
                    </div>
                </div>

                <div class="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-card">

                            <div class="task-check">
                                <form method="post" action="actions.php">
                                    <input type="hidden" name="action" value="done">
                                    <input type="hidden" name="id" value="<?= $task->getId() ?>">
                                    <?php if ($task->isCompleted()): ?>
                                        <div class="checkbox checked">✓</div>
                                    <?php else: ?>
                                        <button type="submit" class="checkbox" title="Tandai selesai"></button>
                                    <?php endif; ?>
                                </form>
                            </div>

                            <div class="task-info">
                                <h3 class="<?= $task->isCompleted() ? 'completed' : '' ?>">
                                    <?= e($task->getTitle()) ?>
                                </h3>
                                <p><?= e($task->getDetail()) ?></p>
                            </div>

                            <div>
                                <?php if ($task instanceof DeadlineTask): ?>
                                    <span class="badge deadline">Tugas Deadline</span>
                                <?php elseif ($task instanceof PriorityTask): ?>
                                    <span class="badge priority">Tugas Prioritas</span>
                                <?php else: ?>
                                    <span class="badge simple">Tugas Sederhana</span>
                                <?php endif; ?>
                            </div>

                            <div class="task-action">
                                <form method="post" action="actions.php">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $task->getId() ?>">
                                    <button type="submit" class="icon-button delete" title="Hapus tugas">×</button>
                                </form>
                            </div>

                        </div>
                    <?php endforeach; ?>

                    <?php if (count($tasks) === 0): ?>
                        <div class="empty-task">Belum ada tugas. Tambahkan yang pertama di atas.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="calendar">
            <div class="calendar-header">
                <button type="button">‹</button>
                <h3><?= $today->format('F Y') ?></h3>
                <button type="button">›</button>
            </div>
            <div class="calendar-week">
                <span>M</span><span>S</span><span>S</span><span>R</span><span>K</span><span>J</span><span>S</span>
            </div>
            <div class="calendar-days">
                <?php
                $daysInMonth = (int) $today->format('t');
                for ($d = 1; $d <= $daysInMonth; $d++):
                ?>
                    <span class="<?= $d === (int) $today->format('j') ? 'today' : '' ?>"><?= $d ?></span>
                <?php endfor; ?>
            </div>
            <div class="calendar-info">
                <strong>Ringkasan</strong>
                <p><?= $list->count() ?> total tugas</p>
                <small>Kalender ini masih dekoratif, belum terhubung ke deadline tugas.</small>
            </div>
        </div>

    </div>

    <footer>
        <div>
            <strong>To-Do List OOP</strong>
            <p>Mini Project Praktikum PBO</p>
        </div>
        <div class="footer-links">
            <span>PHP · OOP</span>
        </div>
    </footer>

</div>

<script>
    const type = document.getElementById('type');
    function toggleExtra() {
        document.getElementById('deadline').hidden = type.value !== 'deadline';
        document.getElementById('priority').hidden = type.value !== 'priority';
    }
    type.addEventListener('change', toggleExtra);
    toggleExtra();
</script>
</body>
</html>
