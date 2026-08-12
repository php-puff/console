<?php

declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

namespace Puff\Console;

final class Output
{
    /**
     * @param resource $stdout
     * @param resource $stderr
     */
    public function __construct(
        private mixed $stdout = STDOUT,
        private mixed $stderr = STDERR,
    ) {
    }

    public function write(string $message = ''): void
    {
        \fwrite($this->stdout, $message . PHP_EOL);
    }

    public function error(string $message): void
    {
        \fwrite($this->stderr, $message . PHP_EOL);
    }
}
