<?php

require_once __DIR__ . '/Task.php';

/**
 * SimpleTask.php  (Bagian P2)
 * -----------------------------
 * Tugas biasa tanpa deadline dan tanpa prioritas.
 * Contoh paling sederhana dari turunan Task (inheritance),
 * karena tidak butuh property/perilaku tambahan apa pun —
 * cukup memberi tahu jenisnya lewat getType().
 */
class SimpleTask extends Task
{
    public function getType(): string
    {
        return 'Biasa';
    }

    // Tidak perlu override getDetail(): SimpleTask cukup pakai
    // implementasi default dari Task, karena tidak ada info tambahan
    // (seperti deadline pada DeadlineTask) yang perlu ditampilkan.
}