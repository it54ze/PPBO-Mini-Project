<?php
// src/Task.php
abstract class Task implements Completable
{
    private int $id = 0;
    private string $title = '';
    private bool $done = false;

    public function __construct(string $title)
    {
        $this->setTitle($title);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        if ($this->id !== 0) {
            throw new LogicException('ID tugas sudah ditetapkan dan tidak boleh diubah.');
        }
        $this->id = $id;
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

    public function markDone(): void
    {
        $this->done = true;
    }

    public function isDone(): bool
    {
        return $this->done;
    }

    // Abstract method: wajib diimplementasikan child class
    abstract public function getType(): string;

    // Method konkret: boleh di-override child class
    public function getDetail(): string
    {
        return '-';
    }
}