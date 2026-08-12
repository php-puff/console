# Puff Console

A small, dependency-free command-line component for PHP 8.2 and later. It provides command registration, argument and option parsing, command help, output streams, confirmation prompts, and exit-code handling.

Framework-specific commands and code generators belong in the application rather than this package.

## Installation

```bash
composer require puff/console
```

## Creating a Command

Commands implement `Puff\Console\Contract`:

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Puff\Console\Contract;
use Puff\Console\Input;
use Puff\Console\Output;

final class Greet implements Contract
{
    public function name(): string
    {
        return 'greet';
    }

    public function description(): string
    {
        return 'Greet a user';
    }

    public function usage(): string
    {
        return 'greet <name> [--greeting=<text>] [--uppercase]';
    }

    public function valueOptions(): array
    {
        return ['greeting' => 'g'];
    }

    public function flagOptions(): array
    {
        return ['uppercase' => 'u'];
    }

    public function execute(Input $input, Output $output): int
    {
        $name = $input->argument(0);
        if ($name === null) {
            throw new \InvalidArgumentException('A name is required.');
        }

        $greeting = (string) $input->option('greeting', 'Hello');
        $message = "{$greeting} {$name}";

        if ($input->hasOption('uppercase')) {
            $message = strtoupper($message);
        }

        $output->write($message);

        return 0;
    }
}
```

`valueOptions()` and `flagOptions()` map long option names to optional shortcuts. Value options require a value, while flags produce `true` when present.

## Running the Console

Register commands when creating the console application:

```php
#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Command\Greet;
use Puff\Console\Console;

$console = new Console(
    commands: [new Greet()],
    version: '1.0.0',
);

exit($console->run());
```

After making the script executable, commands can be invoked with long or short options:

```bash
./puff greet Puff
./puff greet Puff --greeting=Welcome
./puff greet Puff --greeting Welcome --uppercase
./puff greet Puff -g Welcome -u
```

Expected output:

```text
WELCOME PUFF
```

Commands can also be registered after construction:

```php
$console = new Console();
$console->add(new Greet());

$status = $console->run($_SERVER['argv']);
```

## Arguments

Arguments retain their original order:

```php
$first = $input->argument(0);
$second = $input->argument(1, 'default');
$all = $input->arguments();
$remaining = $input->arguments(1);
```

Use `--` to stop option parsing. Every following token is treated as an argument, even when it begins with a hyphen:

```bash
./puff greet -- --literal-name
```

## Options

Declare options on the command:

```php
public function valueOptions(): array
{
    return [
        'format' => 'f',
        'output' => 'o',
    ];
}

public function flagOptions(): array
{
    return [
        'force' => 'F',
        'quiet' => 'q',
    ];
}
```

Read normalized long names in `execute()` regardless of which alias the user supplied:

```php
$format = $input->option('format', 'json');
$force = $input->hasOption('force');
```

Supported value forms are:

```bash
--format=json
--format json
-f=json
-f json
```

Combined short flags such as `-Fq` are not expanded. Unknown options, missing values, and values passed to flags produce an error and exit code `1`.

## Help, Command List, and Version

Running without a command prints the registered command list:

```bash
./puff
```

Use `-h` or `--help` after a command to print its description and usage:

```bash
./puff greet --help
```

Use `-V` or `--version` to print the configured version:

```bash
./puff --version
```

`list` and `help` are not standalone commands. Register them explicitly if an application needs those names.

## Output and Errors

`Output` writes normal messages to `STDOUT` and errors to `STDERR`:

```php
$output->write('Operation completed.');
$output->error('Operation failed.');
```

Any exception thrown while resolving, parsing, or executing a command is caught by `Console::run()`. Its message is written to `STDERR`, and the console returns exit code `1`. Successful built-in output and command execution normally return `0`; a command may return another status code from `execute()` when needed.

Custom streams can be supplied for testing or embedding:

```php
$stdout = fopen('php://memory', 'w+');
$stderr = fopen('php://memory', 'w+');

$output = new Output($stdout, $stderr);
$console = new Console([new Greet()], $output);
```

## Confirmation Prompts

`Prompt` provides yes/no confirmation with a configurable default:

```php
use Puff\Console\Prompt;

$prompt = new Prompt();

if (!$prompt->confirm('Continue?', $output, default: false)) {
    $output->write('Cancelled.');
    return 0;
}
```

The prompt accepts `y` or `yes`, case-insensitively. An empty response selects the configured default.

## Testing Commands

Pass an explicit argument vector and memory streams to test a command without changing global CLI state:

```php
$stdout = fopen('php://memory', 'w+');
$stderr = fopen('php://memory', 'w+');
$console = new Console([new Greet()], new Output($stdout, $stderr));

$status = $console->run(['puff', 'greet', 'Puff', '--uppercase']);

rewind($stdout);
$result = stream_get_contents($stdout);

assert($status === 0);
assert($result === "HELLO PUFF\n");
```

## License

Puff Console is open-source software licensed under the MIT license.
