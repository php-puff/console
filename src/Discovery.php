<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/console
 * https://github.com/php-puff/console/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Console;

use Composer\InstalledVersions;
use Psr\Container\ContainerInterface;

final class Discovery
{
    /** @return list<Contract> */
    public static function commands(string $root, ContainerInterface $container): array
    {
        $commands = [];
        foreach (InstalledVersions::getInstalledPackages() as $package) {
            $path = InstalledVersions::getInstallPath($package);
            if ($path === null || !\is_file($path . '/composer.json')) {
                continue;
            }

            $manifest = \json_decode(
                (string) \file_get_contents($path . '/composer.json'),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
            foreach ((array) ($manifest['extra']['puff']['commands'] ?? []) as $provider) {
                if (!\is_string($provider) || !\class_exists($provider)) {
                    throw new \UnexpectedValueException("Discovered Puff command provider [{$provider}] does not exist.");
                }
                $instance = new $provider();
                if (!$instance instanceof CommandProvider) {
                    throw new \UnexpectedValueException(
                        "Puff command provider [{$provider}] must implement " . CommandProvider::class . '.',
                    );
                }
                foreach ($instance->commands($root, $container) as $command) {
                    $commands[] = $command;
                }
            }
        }

        return $commands;
    }
}
