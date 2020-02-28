<?php

namespace AdditionApps\FlexiblePresenter\Console;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class FlexiblePresenterMakeCommand extends GeneratorCommand
{
    protected $name = 'make:presenter';

    protected $description = 'Create a new Flexible Presenter class';

    protected $type = 'Flexible Presenter';

    public function handle()
    {
        if (parent::handle() === false) {
            if (! $this->option('force')) {
                return;
            }
        }
    }

    protected function getStub()
    {
        return __DIR__.'/../../stubs/DummyFlexiblePresenter.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        if ($this->isCustomNamespace()) {
            return $rootNamespace;
        }

        return $rootNamespace.'\Presenters';
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the flexible presenter already exists'],
        ];
    }

    protected function isCustomNamespace(): bool
    {
        return Str::contains($this->argument('name'), '/');
    }
}
