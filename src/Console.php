<?php

declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

namespace Puff\Console;

final class Console
{
    private Registry $registry;
    private Output $output;

    /** @param iterable<Contract> $commands */
    public function __construct(
        iterable $commands = [],
        ?Output $output = null,
        private readonly string $version = '1.0.0',
    ) {
        $this->registry = new Registry();
        $this->output = $output ?? new Output();
        foreach ($commands as $command) {
            $this->add($command);
        }
    }

    public function add(Contract $command): void
    {
        $this->registry->add($command);
    }

    /** @param list<string>|null $argv */
    public function run(?array $argv = null): int
    {
        $argv ??= \array_values((array) ($_SERVER['argv'] ?? []));
        $name = $argv[1] ?? null;

        try {
            if (\in_array($name, ['-V', '--version'], true)) {
                $this->output->write("Puff {$this->version}");
                return 0;
            }
            if ($name === null) {
                return $this->commands();
            }

            $command = $this->registry->get($name);
            if ($command === null) {
                throw new \InvalidArgumentException("Command [{$name}] is not defined.");
            }
            $tokens = \array_slice($argv, 2);
            if (\array_intersect($tokens, ['-h', '--help']) !== []) {
                return $this->help($command);
            }
            $input = Input::parse($tokens, $command->valueOptions(), $command->flagOptions());
            return $command->execute($input, $this->output);
        } catch (\Throwable $exception) {
            $this->output->error($exception->getMessage());
            return 1;
        }
    }

    private function help(Contract $command): int
    {
        $this->output->write($command->description());
        $this->output->write('Usage: puff ' . $command->usage());

        return 0;
    }

    private function commands(): int
    {
        $commands = $this->registry->all();
        $width = $commands === [] ? 0 : \max(\array_map('strlen', \array_keys($commands)));

        $this->output->write('Usage: puff <command> [arguments] [options]');
        $this->output->write();
        $this->output->write('Commands:');
        foreach ($commands as $name => $command) {
            $this->output->write('  ' . \str_pad($name, $width + 2) . $command->description());
        }

        return 0;
    }
}
