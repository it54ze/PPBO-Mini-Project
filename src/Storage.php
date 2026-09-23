<?php
// src/Storage.php

class Storage
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function load(): TaskList
    {
        if (!is_file($this->filePath)) {
            return new TaskList();
        }

        $contents = file_get_contents($this->filePath);
        if ($contents === false || $contents === '') {
            return new TaskList();
        }

        $list = unserialize($contents);
        if (!$list instanceof TaskList) {
            return new TaskList();
        }

        return $list;
    }

    public function save(TaskList $list): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->filePath, serialize($list));
    }
}