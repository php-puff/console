<?php

declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

namespace Puff\Console;

final class Registry
{
    /** @var array<string, Contract> */
    private array $commands = [];

    public function add(Contract $command): void
    {
        $name = $command->name();
        if (isset($this->commands[$name])) {
            throw new \InvalidArgumentException("Command [{$name}] is already registered.");
        }
        $this->commands[$name] = $command;
    }

    public function get(string $name): ?Contract
    {
        return $this->commands[$name] ?? null;
    }

    /** @return array<string, Contract> */
    public function all(): array
    {
        $commands = $this->commands;
        \ksort($commands);

        return $commands;
    }
}
