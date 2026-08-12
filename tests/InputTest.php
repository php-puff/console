<?php

declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

namespace Puff\Console\Tests;

use PHPUnit\Framework\TestCase;
use Puff\Console\Input;

final class InputTest extends TestCase
{
    public function testParsesArgumentsAndOptions(): void
    {
        $input = Input::parse(
            ['controller', 'User', '--app=src', '-N', 'Domain', '-f'],
            ['app' => 'a', 'namespace' => 'N'],
            ['force' => 'f'],
        );

        self::assertSame('controller', $input->argument(0));
        self::assertSame('User', $input->argument(1));
        self::assertSame(['controller', 'User'], $input->arguments());
        self::assertSame(['User'], $input->arguments(1));
        self::assertSame('src', $input->option('app'));
        self::assertSame('Domain', $input->option('namespace'));
        self::assertTrue($input->hasOption('force'));
    }

    public function testRejectsUnknownOption(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown option [unknown].');

        Input::parse(['--unknown']);
    }

    public function testDoubleDashStopsOptionParsing(): void
    {
        $input = Input::parse(['--', '--literal']);

        self::assertSame('--literal', $input->argument(0));
    }
}
