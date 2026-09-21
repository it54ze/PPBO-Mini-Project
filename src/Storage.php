<?php

class Storage
{
    private const KEY = 'todo_list';

    public function load(): TaskList
    {
        if (isset($_SESSION[self::KEY])) {
            $list = unserialize($_SESSION[self::KEY]);
            if ($list instanceof TaskList) {
                return $list;
            }
        }
        return new TaskList();
    }

    public function save(TaskList $list): void
    {
        $_SESSION[self::KEY] = serialize($list);
    }
}