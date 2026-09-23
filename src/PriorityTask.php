<?php
// src/PriorityTask.php

require_once __DIR__ . '/Task.php';

class PriorityTask extends Task
{
    public const LEVELS = ['Rendah', 'Sedang', 'Tinggi'];

    private string $priority = '';

    public function __construct(int $id, string $title, string $priority)
    {
        parent::__construct($id, $title);
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

    public function getType(): string
    {
        return 'priority';
    }

    public function getDetail(): string
    {
        return sprintf('[%s] %s - Prioritas: %s', $this->getType(), $this->getTitle(), $this->priority);
    }
}