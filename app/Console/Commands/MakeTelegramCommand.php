<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeTelegramCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:make {name : The name of the command class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Telegram command class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Telegram Command';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return base_path('stubs/telegram.command.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Telegram\Commands';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $className = Str::afterLast($name, '\\');
        $commandName = Str::snake(Str::replaceLast('Command', '', $className));

        return str_replace(
            ['{{ command }}', '{{ description }}'],
            [$commandName, 'Description for ' . $commandName],
            $stub
        );
    }
}
