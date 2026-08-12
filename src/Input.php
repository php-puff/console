<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Console;

final readonly class Input
{
    /**
     * @param list<string> $arguments
     * @param array<string, string|bool> $options
     */
    private function __construct(
        private array $arguments,
        private array $options,
    ) {
    }

    /**
     * @param list<string> $tokens
     * @param array<string, string> $valueOptions
     * @param array<string, string> $flagOptions
     */
    public static function parse(array $tokens, array $valueOptions = [], array $flagOptions = []): self
    {
        $values = self::aliases($valueOptions);
        $flags = self::aliases($flagOptions);
        $arguments = [];
        $options = [];
        $literal = false;

        for ($index = 0, $count = \count($tokens); $index < $count; ++$index) {
            $token = $tokens[$index];
            if ($literal) {
                $arguments[] = $token;
                continue;
            }
            if ($token === '--') {
                $literal = true;
                continue;
            }
            if (!\str_starts_with($token, '-') || $token === '-') {
                $arguments[] = $token;
                continue;
            }

            [$name, $inline] = self::parseOption($token);
            if (isset($flags[$name])) {
                if ($inline !== null) {
                    throw new \InvalidArgumentException("Option [{$name}] does not accept a value.");
                }
                $options[$flags[$name]] = true;
                continue;
            }
            if (!isset($values[$name])) {
                throw new \InvalidArgumentException("Unknown option [{$name}].");
            }
            $value = $inline;
            if ($value === null) {
                $value = $tokens[++$index] ?? null;
            }
            if ($value === null || $value === '' || $value === '--') {
                throw new \InvalidArgumentException("Option [{$name}] requires a value.");
            }
            $options[$values[$name]] = $value;
        }

        return new self($arguments, $options);
    }

    public function argument(int $index, ?string $default = null): ?string
    {
        return $this->arguments[$index] ?? $default;
    }

    /** @return list<string> */
    public function arguments(int $offset = 0): array
    {
        return \array_slice($this->arguments, $offset);
    }

    public function option(string $name, string|bool|null $default = null): string|bool|null
    {
        return $this->options[$name] ?? $default;
    }

    public function hasOption(string $name): bool
    {
        return \array_key_exists($name, $this->options);
    }

    /**
     * @param array<string, string> $options
     * @return array<string, string>
     */
    private static function aliases(array $options): array
    {
        $aliases = [];
        foreach ($options as $name => $shortcut) {
            $aliases[$name] = $name;
            if ($shortcut !== '') {
                $aliases[$shortcut] = $name;
            }
        }
        return $aliases;
    }

    /** @return array{string, ?string} */
    private static function parseOption(string $token): array
    {
        $option = \ltrim($token, '-');
        $position = \strpos($option, '=');
        return $position === false
            ? [$option, null]
            : [\substr($option, 0, $position), \substr($option, $position + 1)];
    }
}
