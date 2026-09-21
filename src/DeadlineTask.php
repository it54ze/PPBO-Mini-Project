<?php

require_once __DIR__ . '/Task.php';

/**
 * DeadlineTask.php  (Bagian P2)
 * --------------------------------
 * Tugas yang punya batas waktu (deadline).
 * Menunjukkan konsep: inheritance (extends Task),
 * dan overriding (getType() dan getDetail() ditulis ulang
 * dengan versi milik DeadlineTask sendiri).
 */
class DeadlineTask extends Task
{
    // Disimpan sebagai string format "YYYY-MM-DD" supaya gampang
    // ditampilkan dan dibandingkan.
    private string $deadline;

    public function __construct(string $title, string $deadline)
    {
        // Wajib panggil constructor parent agar validasi judul (Task)
        // tetap jalan. Ini contoh nyata parent::__construct().
        parent::__construct($title);
        $this->setDeadline($deadline);
    }

    public function getDeadline(): string
    {
        return $this->deadline;
    }

    public function setDeadline(string $deadline): void
    {
        if (!$this->isValidDate($deadline)) {
            throw new InvalidArgumentException(
                "Format tanggal deadline tidak valid: \"$deadline\". Gunakan format YYYY-MM-DD."
            );
        }
        $this->deadline = $deadline;
    }

    /**
     * Validasi tanggal secara ketat.
     * Menolak dua kasus:
     *  1) Format salah, mis. "30-02-2026", "2026/02/30", teks acak.
     *  2) Format benar tapi tanggalnya tidak ada, mis. "2026-02-30".
     *
     * DateTime::createFromFormat() PHP secara default "lentur"
     * (2026-02-30 otomatis dianggap 2 Maret 2026), jadi kita cek
     * DateTime::getLastErrors() untuk menangkap tanggal yang
     * sebenarnya tidak valid.
     */
    private function isValidDate(string $value): bool
    {
        // Format harus persis YYYY-MM-DD (cegah "2026-2-3", huruf, dll)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);
        if ($date === false) {
            return false;
        }

        $errors = DateTime::getLastErrors();
        if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            return false; // contoh: 2026-02-30 -> warning "tanggal tidak ada"
        }

        // Pastikan hasil parsing balik ke string yang sama persis
        return $date->format('Y-m-d') === $value;
    }

    public function isOverdue(): bool
    {
        // Tugas yang sudah selesai tidak dianggap terlambat,
        // walaupun deadline-nya sudah lewat.
        if ($this->isDone()) {
            return false;
        }

        $today = new DateTime('today');
        $deadlineDate = DateTime::createFromFormat('Y-m-d', $this->deadline);

        return $deadlineDate < $today;
    }

    public function getType(): string
    {
        return 'Deadline';
    }

    // Override getDetail() bawaan Task supaya deadline dan status
    // TERLAMBAT ikut ditampilkan. Inilah contoh polymorphism yang
    // dipakai P6 di index.php: satu pemanggilan getDetail(),
    // hasil berbeda tergantung jenis tugasnya.
    public function getDetail(): string
    {
        if ($this->isDone()) {
            $status = 'Selesai';
        } elseif ($this->isOverdue()) {
            $status = 'TERLAMBAT';
        } else {
            $status = 'Belum selesai';
        }

        return sprintf(
            '[%s] %s (deadline: %s) - %s',
            $this->getType(),
            $this->getTitle(),
            $this->deadline,
            $status
        );
    }
}