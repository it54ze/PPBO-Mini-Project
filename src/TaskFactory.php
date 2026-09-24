<?php

declare(strict_types=1);

final class TaskFactory
{
    public const TYPES = ['simple', 'deadline', 'priority'];

    private function __construct()
    {
    }

    public static function create(TaskList $list, string $type, array $data): Task
    {
        $normalizedType = strtolower(trim($type));

        $task = match ($normalizedType) {
            'simple'   => self::createSimpleTask($list, $data),
            'deadline' => self::createDeadlineTask($list, $data),
            'priority' => self::createPriorityTask($list, $data),
            default    => throw new InvalidArgumentException(sprintf(
                "Tipe task '%s' tidak dikenal. Tipe yang valid: %s.",
                $type,
                implode(', ', self::TYPES)
            )),
        };

        $list->add($task);

        return $task;
    }

    private static function requireStringField(array $data, string $field, string $type): string
    {
        if (!array_key_exists($field, $data)) {
            throw new InvalidArgumentException(sprintf(
                "Data untuk task tipe '%s' tidak lengkap: field '%s' wajib diisi.",
                $type,
                $field
            ));
        }

        if (!is_string($data[$field])) {
            throw new InvalidArgumentException(sprintf(
                "Field '%s' untuk task tipe '%s' harus berupa string, %s diberikan.",
                $field,
                $type,
                gettype($data[$field])
            ));
        }

        return $data[$field];
    }

    private static function createSimpleTask(TaskList $list, array $data): SimpleTask
    {
        $title = self::requireStringField($data, 'title', 'simple');

        return new SimpleTask($list->nextId(), $title);
    }

    private static function createDeadlineTask(TaskList $list, array $data): DeadlineTask
    {
        $title    = self::requireStringField($data, 'title', 'deadline');
        $deadline = self::requireStringField($data, 'deadline', 'deadline');

        return new DeadlineTask($list->nextId(), $title, $deadline);
    }

    private static function createPriorityTask(TaskList $list, array $data): PriorityTask
    {
        $title    = self::requireStringField($data, 'title', 'priority');
        $priority = self::requireStringField($data, 'priority', 'priority');

        return new PriorityTask($list->nextId(), $title, $priority);
    }
}