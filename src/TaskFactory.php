<?php

/**
 * src/TaskFactory.php
 *
 * Factory Pattern untuk membuat instance Task (SimpleTask, DeadlineTask,
 * PriorityTask) tanpa index.php/actions.php perlu tahu detail konstruksi
 * masing-masing subclass.
 *
 * Konsep OOP yang didemonstrasikan:
 *   - Object creation terpusat (Factory Method Pattern)
 *   - Static method
 *   - Polymorphism (mengembalikan Task, tipe konkretnya berbeda-beda)
 *
 * Dibuat oleh: P7
 *
 * KONTRAK (harus selalu sinkron dengan tests.php):
 *   TaskFactory::create(TaskList $list, string $type, array $data): Task
 *
 *   $type yang didukung: 'simple', 'deadline', 'priority'
 *   $data:
 *     - 'simple'   -> ['title' => string]
 *     - 'deadline' -> ['title' => string, 'deadline' => string (format Y-m-d)]
 *     - 'priority' -> ['title' => string, 'priority' => string]
 *
 *   Perilaku:
 *     - ID task diambil otomatis dari $list->nextId(), PEMANGGIL TIDAK
 *       perlu (dan tidak boleh) memberikan id secara manual.
 *     - Task yang berhasil dibuat otomatis ditambahkan ke $list via add(),
 *       sehingga pemanggil tidak perlu memanggil $list->add() lagi.
 *     - $type tidak dikenal -> InvalidArgumentException.
 *     - Field wajib hilang di $data (misal 'title' tidak diisi) ->
 *       InvalidArgumentException, dengan pesan yang menyebut field mana
 *       yang kurang.
 *     - Validasi lanjutan (judul kosong, format tanggal salah, priority
 *       tidak valid, dll) tetap ditangani oleh masing-masing class Task
 *       (SimpleTask/DeadlineTask/PriorityTask) sendiri -- TaskFactory
 *       TIDAK menduplikasi validasi tersebut, hanya meneruskan.
 */

declare(strict_types=1);

final class TaskFactory
{
    /**
     * Daftar tipe task yang dikenali factory ini.
     * Dijadikan konstanta supaya index.php (P6) bisa membangun dropdown
     * pilihan jenis task secara dinamis tanpa hardcode string di dua tempat.
     */
    public const TYPES = ['simple', 'deadline', 'priority'];

    /**
     * Mencegah instansiasi: TaskFactory murni kumpulan static method.
     */
    private function __construct()
    {
    }

    /**
     * Membuat sebuah Task baru sesuai $type, memasukkannya ke $list,
     * dan mengembalikan instance Task yang baru dibuat.
     *
     * @param TaskList             $list Daftar task tempat task baru akan ditambahkan
     * @param string               $type 'simple' | 'deadline' | 'priority'
     * @param array<string, mixed> $data Data pembuatan task, tergantung $type
     *
     * @return Task Instance Task yang baru dibuat (SimpleTask/DeadlineTask/PriorityTask)
     *
     * @throws InvalidArgumentException jika $type tidak dikenal atau field
     *                                   wajib pada $data tidak ada.
     */
    public static function create(TaskList $list, string $type, array $data): Task
    {
        $normalizedType = strtolower(trim($type));

        $task = match ($normalizedType) {
            'simple'   => self::createSimpleTask($list, $data),
            'deadline' => self::createDeadlineTask($list, $data),
            'priority' => self::createPriorityTask($list, $data),
            default    => throw new InvalidArgumentException(sprintf(
                "Tipe task '%s' tidak dikenal. Tipe yang valid: %s.",
                $type,
                implode(', ', self::TYPES)
            )),
        };

        $list->add($task);

        return $task;
    }

    /**
     * Mengambil sebuah field wajib dari $data, atau melempar exception
     * dengan pesan yang jelas (menyebut nama tipe & nama field) jika
     * field tersebut tidak ada / bukan string.
     *
     * @param array<string, mixed> $data
     */
    private static function requireStringField(array $data, string $field, string $type): string
    {
        if (!array_key_exists($field, $data)) {
            throw new InvalidArgumentException(sprintf(
                "Data untuk task tipe '%s' tidak lengkap: field '%s' wajib diisi.",
                $type,
                $field
            ));
        }

        if (!is_string($data[$field])) {
            throw new InvalidArgumentException(sprintf(
                "Field '%s' untuk task tipe '%s' harus berupa string, %s diberikan.",
                $field,
                $type,
                gettype($data[$field])
            ));
        }

        return $data[$field];
    }

    private static function createSimpleTask(TaskList $list, array $data): SimpleTask
    {
        $title = self::requireStringField($data, 'title', 'simple');

        return new SimpleTask($list->nextId(), $title);
    }

    private static function createDeadlineTask(TaskList $list, array $data): DeadlineTask
    {
        $title    = self::requireStringField($data, 'title', 'deadline');
        $deadline = self::requireStringField($data, 'deadline', 'deadline');

        return new DeadlineTask($list->nextId(), $title, $deadline);
    }

    private static function createPriorityTask(TaskList $list, array $data): PriorityTask
    {
        $title    = self::requireStringField($data, 'title', 'priority');
        $priority = self::requireStringField($data, 'priority', 'priority');

        return new PriorityTask($list->nextId(), $title, $priority);
    }
}