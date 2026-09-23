<?php
// src/TaskList.php

class TaskList
{
    private array $tasks = [];
    private int $lastId = 0;

    public function nextId(): int
    {
        $this->lastId++;
        return $this->lastId;
    }

    public function add(Task $task): void
    {
        $this->tasks[$task->getId()] = $task;
    }

    public function find(int $id): ?Task
    {
        return $this->tasks[$id] ?? null;
    }

    public function complete(int $id): void
    {
        $task = $this->find($id);
        if ($task === null) {
            throw new Exception("Task dengan id {$id} tidak ditemukan.");
        }
        $task->complete();
    }

    public function remove(int $id): void
    {
        if (!isset($this->tasks[$id])) {
            throw new Exception("Task dengan id {$id} tidak ditemukan.");
        }
        unset($this->tasks[$id]);
    }

    public function count(): int
    {
        return count($this->tasks);
    }

    public function getAll(): array
    {
        return array_values($this->tasks);
    }
}