<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Console\Tests;

use PHPUnit\Framework\TestCase;
use Puff\Console\Generator;

final class GeneratorTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = \sys_get_temp_dir() . '/puff-generator-' . \bin2hex(\random_bytes(6));
        self::assertTrue(\mkdir($this->root));
    }

    protected function tearDown(): void
    {
        $file = $this->root . '/app/Model/Admin/User.php';
        if (\is_file($file)) {
            \unlink($file);
            \rmdir(\dirname($file));
            \rmdir(\dirname($file, 2));
            \rmdir(\dirname($file, 3));
        }
        \rmdir($this->root);
    }

    public function testConvertsNamespaceDirectlyToPath(): void
    {
        $class = (new Generator($this->root))->generate(
            'Admin/User',
            'App\\Model',
            __DIR__ . '/fixtures/class.stub',
        );

        self::assertSame('App\\Model\\Admin\\User', $class);
        self::assertFileExists($this->root . '/app/Model/Admin/User.php');
    }

    public function testRefusesToOverwriteExistingClass(): void
    {
        $generator = new Generator($this->root);
        $generator->generate('Admin/User', 'App\\Model', __DIR__ . '/fixtures/class.stub');

        $this->expectException(\RuntimeException::class);
        $generator->generate('Admin/User', 'App\\Model', __DIR__ . '/fixtures/class.stub');
    }
}
