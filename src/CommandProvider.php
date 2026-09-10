<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Console;

use Psr\Container\ContainerInterface;

interface CommandProvider
{
    /** @return iterable<Contract> */
    public function commands(string $root, ContainerInterface $container): iterable;
}
