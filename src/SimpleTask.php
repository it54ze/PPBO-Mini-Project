<?php
// src/SimpleTask.php

require_once __DIR__ . '/Task.php';

class SimpleTask extends Task
{
    public function getType(): string
    {
        return 'Biasa';
    }

    public function getDetail(): string
    {
        return sprintf(
            '[%s] %s - %s',
            $this->getType(),
            $this->getTitle(),
            $this->isCompleted() ? 'Selesai' : 'Belum selesai'
        );
    }
}