<?php
// src/Task.php

abstract class Task implements Completable
{
    private int $id;
    private string $title;
    private bool $completed = false;

    /**
     * ID diberikan sekali saat konstruksi dan TIDAK BISA diubah lagi
     * setelahnya -- sengaja tidak ada method setId() sama sekali
     * (bukan cuma private) supaya immutability-nya benar-benar terjaga.
     */
    public function __construct(int $id, string $title)
    {
        $this->id = $id;
        $this->setTitle($title);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $title = trim($title);
        if ($title === '') {
            throw new InvalidArgumentException('Judul tugas tidak boleh kosong.');
        }
        $this->title = $title;
    }

    public function complete(): void
    {
        $this->completed = true;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    // Abstract: wajib diimplementasikan child class
    abstract public function getType(): string;

    // Konkret: boleh di-override child class
    public function getDetail(): string
    {
        return '-';
    }
}