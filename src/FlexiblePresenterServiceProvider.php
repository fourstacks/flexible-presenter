<?php

namespace AdditionApps\FlexiblePresenter;

use Illuminate\Support\ServiceProvider;
use AdditionApps\FlexiblePresenter\Console\FlexiblePresenterMakeCommand;

class FlexiblePresenterServiceProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FlexiblePresenterMakeCommand::class,
            ]);
        }
    }
}
