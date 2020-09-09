<?php

namespace AdditionApps\FlexiblePresenter\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as Orchestra;
use AdditionApps\FlexiblePresenter\FlexiblePresenterServiceProvider;

abstract class TestCase extends Orchestra
{
    protected $basePath;

    public function setUp(): void
    {
        parent::setUp();
        $this->basePath = realpath(__DIR__.'/..');
        $this->setUpDatabase($this->app);

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $namespace = 'AdditionApps\\FlexiblePresenter\\Tests\\Support\\Factories';

            $modelName = Str::after(
                $modelName,
                'AdditionApps\\FlexiblePresenter\\Tests\\Support\\Models'
            );

            return $namespace.$modelName.'Factory';
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            FlexiblePresenterServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function setUpDatabase($app)
    {
        $app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('posts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title')->nullable();
                $table->string('body')->nullable();
                $table->dateTime('published_at')->nullable();
                $table->timestamps();
            });

        $app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('comments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('post_id')->nullable();
                $table->string('body')->nullable();
                $table->timestamps();
            });

        $app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('images', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('url')->nullable();
                $table->timestamps();
            });

        $app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('image_post', function (Blueprint $table) {
                $table->bigInteger('post_id')->nullable();
                $table->bigInteger('image_id')->nullable();
                $table->string('test')->nullable();
            });
    }
}
