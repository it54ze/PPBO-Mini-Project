<?php 

// ============================================================
// DATA DUMMY P6
// Data ini hanya digunakan untuk preview tampilan UI.
// Nantinya dapat digantikan dengan data dari P1-P5/P7.
// ============================================================

$tasks = [
    [
        'id' => 1,
        'title' => 'Tugas Rekayasa Perangkat Lunak',
        'detail' => 'Deadline: 3 hari lagi | Kesulitan: Sulit',
        'type' => 'Tugas Deadline',
        'done' => false
    ],
    [
        'id' => 2,
        'title' => 'Tugas Manajemen Rantai Pasok',
        'detail' => 'Deadline: 5 hari lagi | Kesulitan: Sedang',
        'type' => 'Tugas Prioritas',
        'done' => false
    ],
    [
        'id' => 3,
        'title' => 'Tugas Basis Data',
        'detail' => 'Deadline: 10 hari lagi | Kesulitan: Mudah',
        'type' => 'Tugas Sederhana',
        'done' => true
    ]
];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDoApp</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">

        <header class="navbar">

            <div class="logo">
                ToDo<span>App</span>
            </div>

            <div class="profile">
                ♡
            </div>

        </header>


        <main>

            <section class="hero">

                <div>

                    <h1>
                        Selamat <span> Datang!</span>
                    </h1>

                    <p>
                        Sedikit demi sedikit, tugasmu akan selesai.
                    </p>

                </div>

                <div class="quote">
                    "Kerjakan hari ini,
                    <br>
                    agar esok lebih tenang."
                </div>

            </section>


            <div class="dashboard">

                <div class="main-content">


                    <section class="add-task">

                        <form>

                            <input
                                type="text"
                                name="title"
                                placeholder="Tambah tugas baru..."
                            >

                            <select name="type">

                                <option value="simple">
                                    Tugas Sederhana
                                </option>

                                <option value="deadline">
                                    Tugas Deadline
                                </option>

                                <option value="priority">
                                    Tugas Prioritas
                                </option>

                            </select>

                            <button type="submit">
                                Tambah
                            </button>

                        </form>

                    </section>



                    <section class="task-section">

                        <div class="section-header">

                            <h2>
                                Daftar Tugas
                            </h2>

                            <div class="filters">

                                <button class="filter active">
                                    Semua
                                </button>

                                <button class="filter">
                                    Belum Selesai
                                </button>

                                <button class="filter">
                                    Selesai
                                </button>

                            </div>

                        </div>



                        <div class="task-list">

                            <?php foreach ($tasks as $task): ?>

                                <div class="task-card">


                                    <!-- P1 - POSISI KODE P1
                                         Status task nantinya berasal
                                         dari method isDone() pada Task.
                                    -->

                                    <div class="task-check">

                                        <?php if ($task['done']): ?>

                                            <div class="checkbox checked">
                                                ✓
                                            </div>

                                        <?php else: ?>

                                            <div class="checkbox"></div>

                                        <?php endif; ?>

                                    </div>



                                    <!-- P1 - POSISI KODE P1
                                         getTitle() dari object Task
                                         nantinya digunakan di bagian ini.
                                    -->

                                    <div class="task-info">

                                        <h3 class="<?= $task['done'] ? 'completed' : '' ?>">

                                            <?= htmlspecialchars(
                                                $task['title'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </h3>



                                        <!-- P6 - POSISI POLYMORPHISM
                                             getDetail() dari object Task
                                             nantinya ditampilkan di sini.

                                             P2/P3:
                                             SimpleTask, DeadlineTask, dan
                                             PriorityTask memiliki implementasi
                                             getDetail() masing-masing.
                                        -->

                                        <p>

                                            <?= htmlspecialchars(
                                                $task['detail'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </p>

                                    </div>



                                    <!-- P2/P3 - POSISI KODE
                                         Jenis task nantinya berasal dari
                                         object Task, bukan data dummy.
                                    -->

                                    <div>

                                        <?php if ($task['type'] === 'Tugas Deadline'): ?>

                                            <span class="badge deadline">
                                                Tugas Deadline
                                            </span>

                                        <?php elseif ($task['type'] === 'Tugas Prioritas'): ?>

                                            <span class="badge priority">
                                                Tugas Prioritas
                                            </span>

                                        <?php else: ?>

                                            <span class="badge simple">
                                                Tugas Sederhana
                                            </span>

                                        <?php endif; ?>

                                    </div>



                                    <!-- P5 - POSISI KODE P5
                                         actions.php nantinya terhubung
                                         dengan tombol aksi task di sini.
                                    -->

                                    <div class="task-action">

                                        <button
                                            type="button"
                                            class="icon-button"
                                            title="Edit tugas"
                                        >
                                            ✎
                                        </button>

                                        <button
                                            type="button"
                                            class="icon-button delete"
                                            title="Hapus tugas"
                                        >
                                            ×
                                        </button>

                                    </div>


                                </div>

                            <?php endforeach; ?>

                        </div>

                    </section>

                </div>



                <aside class="calendar">

                    <div class="calendar-header">

                        <button type="button">
                            ‹
                        </button>

                        <h3>
                            September 2026
                        </h3>

                        <button type="button">
                            ›
                        </button>

                    </div>


                    <div class="calendar-week">

                        <span>Min</span>
                        <span>Sen</span>
                        <span>Sel</span>
                        <span>Rab</span>
                        <span>Kam</span>
                        <span>Jum</span>
                        <span>Sab</span>

                    </div>


                    <div class="calendar-days">

                        <span></span>
                        <span></span>
                        <span>1</span>
                        <span>2</span>
                        <span>3</span>
                        <span>4</span>
                        <span>5</span>

                        <span>6</span>
                        <span>7</span>
                        <span>8</span>
                        <span>9</span>
                        <span>10</span>
                        <span>11</span>
                        <span>12</span>

                        <span>13</span>
                        <span>14</span>
                        <span>15</span>
                        <span>16</span>
                        <span>17</span>
                        <span>18</span>
                        <span>19</span>

                        <span>20</span>
                        <span>21</span>

                        <span class="today">
                            22
                        </span>

                        <span>23</span>
                        <span>24</span>
                        <span>25</span>
                        <span>26</span>

                        <span>27</span>
                        <span>28</span>
                        <span>29</span>
                        <span>30</span>

                    </div>


                    <div class="calendar-info">

                        <strong>
                            Tugas Terdekat
                        </strong>

                        <p>
                            Tugas Rekayasa Perangkat Lunak
                        </p>

                        <small>
                            Deadline 25 September
                        </small>

                    </div>

                </aside>

            </div>

        </main>



        <footer>

            <div>

                <strong>
                    Deadline Rescue
                </strong>

                <p>
                    Atur tugasmu sebelum deadline.
                </p>

            </div>


            <div class="footer-links">

                <span>
                    Bantuan
                </span>

                <span>|</span>

                <span>
                    Kontak
                </span>

                <span>|</span>

                <span>
                    2026
                </span>

            </div>

        </footer>


    </div>

</body>
</html>
