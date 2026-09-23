<div class="task-list">

    <?php foreach ($tasks as $task): ?>

        <div class="task-card">

            <!-- P1 - POSISI KODE P1
                 Bagian status task / isDone() -->

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
                 getTitle() dari Task nantinya digunakan di sini -->

            <div class="task-info">

                <h3 class="<?= $task['done'] ? 'completed' : '' ?>">

                    <?= htmlspecialchars(
                        $task['title'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </h3>


                <!-- P6 - POSISI POLYMORPHISM
                     getDetail() dari object Task nantinya
                     ditampilkan di bagian ini.
                     
                     P2/P3:
                     SimpleTask, DeadlineTask, dan PriorityTask
                     menyediakan implementasi getDetail() masing-masing.
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
                 Jenis task nantinya berasal dari object Task,
                 bukan data dummy. -->

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
                 actions.php nantinya terhubung dengan
                 tombol aksi task di bagian ini. -->

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
