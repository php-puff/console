<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Console;

final readonly class Generator
{
    public function __construct(private string $root)
    {
    }

    public function generate(
        string $name,
        string $namespace,
        string $stub,
        bool $force = false,
    ): string {
        $class = $this->qualify($name, $namespace);
        $path = \rtrim($this->root, '/\\') . '/' . \lcfirst(\str_replace('\\', '/', $class)) . '.php';
        if (!$force && \is_file($path)) {
            throw new \RuntimeException("{$path} already exists.");
        }

        $directory = \dirname($path);
        if (!\is_dir($directory) && !@\mkdir($directory, 0777, true) && !\is_dir($directory)) {
            throw new \RuntimeException("Unable to create directory: {$directory}");
        }

        $template = \file_get_contents($stub);
        if ($template === false) {
            throw new \RuntimeException("Unable to read [{$stub}].");
        }
        $position = \strrpos($class, '\\');
        $classNamespace = $position === false ? '' : \substr($class, 0, $position);
        $className = $position === false ? $class : \substr($class, $position + 1);
        $source = \str_replace(['%NAMESPACE%', '%CLASS%'], [$classNamespace, $className], $template);
        if (\file_put_contents($path, $source, LOCK_EX) === false) {
            throw new \RuntimeException("Unable to write file: {$path}");
        }

        return $class;
    }

    private function qualify(string $name, string $namespace): string
    {
        $name = \trim(\str_replace('/', '\\', $name), "\\/ \t\n\r\0\x0B");
        $namespace = \trim($namespace, '\\');
        $pattern = '/^(?:[A-Za-z_][A-Za-z0-9_]*\\\\)*[A-Za-z_][A-Za-z0-9_]*$/';
        if ($name === '' || \preg_match($pattern, $name) !== 1) {
            throw new \InvalidArgumentException('Invalid class name.');
        }
        if (\preg_match($pattern, $namespace) !== 1) {
            throw new \InvalidArgumentException('Invalid namespace.');
        }

        return $namespace . '\\' . $name;
    }
}
