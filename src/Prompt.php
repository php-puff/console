<?php

declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

namespace Puff\Console;

final class Prompt
{
    /** @param resource $input */
    public function __construct(private $input = STDIN)
    {
    }

    public function confirm(string $question, Output $output, bool $default = true): bool
    {
        $output->write($question . ($default ? ' (Y/n) ' : ' (y/N) '));
        $answer = \fgets($this->input);
        if ($answer === false || \trim($answer) === '') {
            return $default;
        }
        return \in_array(\strtolower(\trim($answer)), ['y', 'yes'], true);
    }
}
