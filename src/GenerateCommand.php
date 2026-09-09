<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Console;

final readonly class GenerateCommand implements Contract
{
    /** @param array<string, string> $variables */
    public function __construct(
        private string $name,
        private string $namespace,
        private string $stub,
        private Generator $generator,
        private array $variables = [],
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return "Create a Puff {$this->name}";
    }

    public function usage(): string
    {
        return "{$this->name} <name> [options]";
    }

    public function valueOptions(): array
    {
        return ['namespace' => 'N'];
    }

    public function flagOptions(): array
    {
        return ['force' => 'f'];
    }

    public function execute(Input $input, Output $output): int
    {
        $name = (string) $input->argument(0);
        if ($name === '') {
            throw new \InvalidArgumentException('Command name is required.');
        }

        $namespace = (string) ($input->option('namespace') ?: $this->namespace);
        $class = $this->generator->generate(
            $name,
            $namespace,
            $this->stub,
            $input->hasOption('force'),
            $this->variables,
        );
        $output->write("{$class} created successfully.");

        return 0;
    }
}
