<?php

namespace AdditionApps\FlexiblePresenter\Tests;

use Illuminate\Support\Facades\File;

class PresenterMakeCommandTestTest extends TestCase
{

    /** @test */
    public function it_can_create_a_presenter_class()
    {

        $this->artisan('make:presenter', [
            'name' => 'PostPresenter',
            '--force' => true,
        ])
            ->expectsOutput('Flexible Presenter created successfully.')
            ->assertExitCode(0);

        $shouldOutputFilePath = $this->app['path'].'/Presenters/PostPresenter.php';

        $this->assertTrue(File::exists($shouldOutputFilePath), 'File exists in default app/Presenters folder');

        $contents = File::get($shouldOutputFilePath);

        $this->assertStringContainsString('namespace App\Presenters;', $contents);

        $this->assertStringContainsString('class PostPresenter extends FlexiblePresenter', $contents);
    }

    /** @test */
    public function it_can_create_a_view_model_with_a_custom_namespace()
    {
        $this->artisan('make:presenter', [
            'name' => 'Blog/PostPresenter',
            '--force' => true,
        ])
            ->expectsOutput('Flexible Presenter created successfully.')
            ->assertExitCode(0);

        $shouldOutputFilePath = $this->app['path'].'/Blog/PostPresenter.php';

        $this->assertTrue(File::exists($shouldOutputFilePath), 'File exists in custom app/Blog folder');

        $contents = File::get($shouldOutputFilePath);

        $this->assertStringContainsString('namespace App\Blog;', $contents);

        $this->assertStringContainsString('class PostPresenter extends FlexiblePresenter', $contents);
    }

}
