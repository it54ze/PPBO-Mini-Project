<?php
// src/DeadlineTask.php

require_once __DIR__ . '/Task.php';

class DeadlineTask extends Task
{
    private string $deadline;

    public function __construct(int $id, string $title, string $deadline)
    {
        parent::__construct($id, $title);
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

    private function isValidDate(string $value): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);
        if ($date === false) {
            return false;
        }

        $errors = DateTime::getLastErrors();
        if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            return false; // contoh: 2026-02-30
        }

        return $date->format('Y-m-d') === $value;
    }

    public function isOverdue(): bool
    {
        if ($this->isCompleted()) {
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

    public function getDetail(): string
    {
        if ($this->isCompleted()) {
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