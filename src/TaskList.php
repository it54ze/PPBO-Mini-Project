<?php
// src/TaskList.php

class TaskList
{
    private array $tasks = [];
    private int $lastId = 0;

    // Generasi ID otomatis
    public function nextId(): int
    {
        $this->lastId++;
        return $this->lastId;
    }

    // Menambah task ke list
    public function add(Task $task): void
    {
        $this->tasks[$task->getId()] = $task;
    }

    // Mencari task berdasarkan ID
    public function find(int $id): ?Task
    {
        return $this->tasks[$id] ?? null;
    }

    // Tandai selesai (memanggil markDone milik temanmu)
    public function complete(int $id): void
    {
        $task = $this->find($id);
        if ($task !== null) {
            $task->markDone();
        }
    }

    // Hapus task dari list
    public function remove(int $id): void
    {
        unset($this->tasks[$id]);
    }

    // Hitung jumlah seluruh task
    public function count(): int
    {
        return count($this->tasks);
    }

    // Ambil semua task
    public function getAll(): array
    {
        return array_values($this->tasks);
    }
}