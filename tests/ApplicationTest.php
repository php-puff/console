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
use Puff\Console\Console;
use Puff\Console\Contract;
use Puff\Console\Input;
use Puff\Console\Output;

final class ApplicationTest extends TestCase
{
    public function testRunsRegisteredCommand(): void
    {
        $stdout = \fopen('php://memory', 'w+');
        $stderr = \fopen('php://memory', 'w+');
        self::assertIsResource($stdout);
        self::assertIsResource($stderr);
        $application = new Console(
            [new TestCommand()],
            new Output($stdout, $stderr),
        );

        self::assertSame(0, $application->run(['puff', 'test', 'Puff']));
        \rewind($stdout);
        $contents = \stream_get_contents($stdout);
        self::assertNotFalse($contents);
        self::assertStringContainsString('Hello Puff', $contents);
    }

    public function testListAndHelpCommandsAreNotDefined(): void
    {
        $stdout = \fopen('php://memory', 'w+');
        $stderr = \fopen('php://memory', 'w+');
        self::assertIsResource($stdout);
        self::assertIsResource($stderr);
        $application = new Console([new TestCommand()], new Output($stdout, $stderr));

        self::assertSame(1, $application->run(['puff', 'list']));
        self::assertSame(1, $application->run(['puff', 'help']));

        \rewind($stderr);
        $contents = \stream_get_contents($stderr);
        self::assertNotFalse($contents);
        self::assertStringContainsString('Command [list] is not defined.', $contents);
        self::assertStringContainsString('Command [help] is not defined.', $contents);
    }

    public function testShowsCommandsWhenNoCommandIsProvided(): void
    {
        $stdout = \fopen('php://memory', 'w+');
        self::assertIsResource($stdout);
        $application = new Console([new TestCommand()], new Output($stdout));

        self::assertSame(0, $application->run(['puff']));
        \rewind($stdout);
        $contents = \stream_get_contents($stdout);
        self::assertNotFalse($contents);
        self::assertStringContainsString('Usage: puff <command> [arguments] [options]', $contents);
        self::assertStringContainsString('test  Test command', $contents);
    }

    public function testShowsCommandHelpOption(): void
    {
        $stdout = \fopen('php://memory', 'w+');
        self::assertIsResource($stdout);
        $application = new Console([new TestCommand()], new Output($stdout));

        self::assertSame(0, $application->run(['puff', 'test', '--help']));
        \rewind($stdout);
        $contents = \stream_get_contents($stdout);
        self::assertNotFalse($contents);
        self::assertStringContainsString('Usage: puff test <name>', $contents);
    }
}

final class TestCommand implements Contract
{
    public function name(): string
    {
        return 'test';
    }

    public function description(): string
    {
        return 'Test command';
    }

    public function usage(): string
    {
        return 'test <name>';
    }

    public function valueOptions(): array
    {
        return [];
    }

    public function flagOptions(): array
    {
        return [];
    }

    public function execute(Input $input, Output $output): int
    {
        $output->write('Hello ' . $input->argument(0));
        return 0;
    }
}
