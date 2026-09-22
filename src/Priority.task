<?php
// src/PriorityTask.php

class PriorityTask extends Task
{
    public const LEVELS = ['Rendah', 'Sedang', 'Tinggi'];

    private string $priority = '';

    public function __construct(int $id, string $title, string $priority)
    {
        parent::__construct($title);
        $this->setId($id);
        $this->setPriority($priority);
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): void
    {
        if (!in_array($priority, self::LEVELS, true)) {
            throw new InvalidArgumentException(
                'Prioritas tidak valid. Pilih salah satu: ' . implode(', ', self::LEVELS) . '.'
            );
        }

        $this->priority = $priority;
    }

    // Wajib dari Task (abstract getType())
    public function getType(): string
    {
        return 'priority';
    }

    // Override dari Task (concrete getDetail())
    public function getDetail(): string
    {
        return 'Prioritas: ' . $this->priority;
    }
}
