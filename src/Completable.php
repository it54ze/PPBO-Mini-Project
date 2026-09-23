<?php
// src/Completable.php
interface Completable
{
    public function complete(): void;
    public function isCompleted(): bool;
}