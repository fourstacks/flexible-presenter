<?php

namespace AdditionApps\FlexiblePresenter;

use AdditionApps\FlexiblePresenter\Console\FlexiblePresenterMakeCommand;
use Illuminate\Support\ServiceProvider;

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
