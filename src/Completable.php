<?php
// src/Completable.php
interface Completable
{
    public function markDone(): void;
    public function isDone(): bool;
}