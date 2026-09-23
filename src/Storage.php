<?php
// src/Storage.php

class Storage
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function load(): TaskList
    {
        if (!is_file($this->filePath)) {
            return new TaskList();
        }

        $raw = file_get_contents($this->filePath);
        if ($raw === false || trim($raw) === '') {
            return new TaskList();
        }

        try {
            $list = unserialize($raw, [
                'allowed_classes' => [
                    TaskList::class,
                    Task::class,
                    SimpleTask::class,
                    DeadlineTask::class,
                    PriorityTask::class,
                ],
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Gagal membaca data dari {$this->filePath}: {$e->getMessage()}",
                0,
                $e
            );
        }

        if (!($list instanceof TaskList)) {
            throw new RuntimeException(
                "Data di {$this->filePath} rusak atau bukan format TaskList yang valid."
            );
        }

        return $list;
    }

    public function save(TaskList $list): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException("Gagal membuat folder penyimpanan: {$dir}");
        }

        $result = file_put_contents($this->filePath, serialize($list), LOCK_EX);
        if ($result === false) {
            throw new RuntimeException("Gagal menyimpan data ke {$this->filePath}.");
        }
    }
}