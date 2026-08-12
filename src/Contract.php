<?php

declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

namespace Puff\Console;

interface Contract
{
    public function name(): string;

    public function description(): string;

    public function usage(): string;

    /** @return array<string, string> Long option name => shortcut. */
    public function valueOptions(): array;

    /** @return array<string, string> Long option name => shortcut. */
    public function flagOptions(): array;

    public function execute(Input $input, Output $output): int;
}
