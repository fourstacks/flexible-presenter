<?php

namespace AdditionApps\FlexiblePresenter\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use AdditionApps\FlexiblePresenter\FlexiblePresenter;
use AdditionApps\FlexiblePresenter\Tests\Support\Models\Post;
use AdditionApps\FlexiblePresenter\Tests\Support\Models\Comment;
use AdditionApps\FlexiblePresenter\Exceptions\InvalidPresenterKeys;
use AdditionApps\FlexiblePresenter\Tests\Support\Presenters\PostPresenter;
use AdditionApps\FlexiblePresenter\Tests\Support\Presenters\CommentPresenter;

class FlexiblePresenterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('01/01/2020 13:00:00');
    }

    /** @test */
    public function new_presenter_instance_instantiated_with_make_method()
    {
        $post = factory(Post::class)->create();

        $presenter = PostPresenter::make($post);

        $this->assertInstanceOf(FlexiblePresenter::class, $presenter);
        $this->assertEquals($presenter->resource->id, $post->id);
    }

    /** @test */
    public function new_presenter_instance_instantiated_with_collection_method()
    {
        $post = factory(Post::class, 3)->create();

        $presenter = PostPresenter::collection($post);

        $this->assertInstanceOf(FlexiblePresenter::class, $presenter);
        $this->assertCount(3, $presenter->collection);
    }

    /** @test */
    public function new_presenter_instance_instantiated_with_new_method()
    {
        $presenter = PostPresenter::new();

        $this->assertInstanceOf(FlexiblePresenter::class, $presenter);
        $this->assertNull($presenter->resource);
        $this->assertNull($presenter->collection);
    }

    /** @test */
    public function new_keys_can_be_added_using_with_method_when_presenting_resource()
    {
        $post = $this->createPostAndComments();

        $return = PostPresenter::make($post)
            ->with(function ($post) {
                return ['new_key' => 'foo'];
            })
            ->get();

        $this->assertEquals([
            'id' => $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'published_at' => $post->published_at->toDateString(),
            'comment_count' => 3,
            'new_key' => 'foo',
        ], $return);
    }

    /** @test */
    public function keys_are_overwritten_using_with_method_when_presenting_resource()
    {
        $post = $this->createPostAndComments();

        $return = PostPresenter::make($post)
            ->with(function ($post) {
                return ['published_at' => $post->published_at->toDayDateTimeString()];
            })
            ->get();

        $this->assertEquals([
            'id' => $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'published_at' => $post->published_at->toDayDateTimeString(),
            'comment_count' => 3,
        ], $return);
    }

    /** @test */
    public function only_given_keys_are_returned_when_presenting_resource()
    {
        $post = factory(Post::class)->create();
        $presenter = PostPresenter::make($post);

        $usingStringsReturn = $presenter
            ->only('id', 'title')
            ->get();

        $this->assertEquals(['id' => $post->id, 'title' => $post->title], $usingStringsReturn);

        $usingArrayReturn = $presenter
            ->only(['title', 'body'])
            ->get();

        $this->assertEquals(['title' => $post->title, 'body' => $post->body], $usingArrayReturn);
    }

    /** @test */
    public function keys_except_those_given_are_returned_when_presenting_resource()
    {
        $post = factory(Post::class)->create();
        $presenter = PostPresenter::make($post);

        $usingStringsReturn = $presenter
            ->except('body', 'published_at', 'comment_count')
            ->get();

        $this->assertEquals([
            'id' => $post->id,
            'title' => $post->title,
        ], $usingStringsReturn);

        $usingArrayReturn = $presenter
            ->except(['id', 'title', 'comment_count'])
            ->get();

        $this->assertEquals([
            'body' => $post->body,
            'published_at' => $post->published_at->toDateString(),
        ], $usingArrayReturn);
    }

    public function lazy_keys_are_not_evaluated_unless_requested()
    {
        $post = $this->createPostAndComments();

        $presenter = PostPresenter::make($post);

        // Comments have not been loaded on post model
        // The following call should result in no DB queries

        DB::enableQueryLog();

        $presenter->only('id')->get();

        $this->assertCount(0, count(DB::getQueryLog()));

        DB::flushQueryLog();

        // Comments will be loaded on post model when comment_count is requested
        // The following call should result in one DB query

        DB::enableQueryLog();

        $presenter->only('id', 'comment_count')->get();

        $this->assertCount(1, count(DB::getQueryLog()));

        DB::flushQueryLog();
    }

    /** @test */
    public function all_keys_are_returned_for_resource()
    {
        $post = $this->createPostAndComments();

        $return = PostPresenter::make($post)->all();

        $this->assertEquals([
            'id' => $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'published_at' => $post->published_at->toDateString(),
            'comment_count' => 3,
        ], $return);
    }

    /** @test */
    public function null_returned_when_no_resource_passed_to_constructor()
    {
        $makeReturn = PostPresenter::make(null)->get();

        $this->assertNull($makeReturn);

        $collectionReturn = PostPresenter::collection(null)->get();

        $this->assertNull($collectionReturn);
    }

    /** @test */
    public function only_keys_for_preset_are_returned()
    {
        $post = factory(Post::class)->create();

        $return = PostPresenter::make($post)->preset('summary')->get();

        $this->assertEquals([
            'title' => $post->title,
            'body' => $post->body,
        ], $return);
    }

    /** @test */
    public function invalid_keys_given_to_only_method_trigger_exception()
    {
        $this->expectException(InvalidPresenterKeys::class);

        $post = factory(Post::class)->create();

        PostPresenter::make($post)->only('bad_key')->get();
    }

    /** @test */
    public function invalid_keys_given_to_except_method_trigger_exception()
    {
        $this->expectException(InvalidPresenterKeys::class);

        $post = factory(Post::class)->create();

        PostPresenter::make($post)->except('bad_key')->get();
    }

    /** @test */
    public function collection_of_models_are_presented()
    {
        $posts = factory(Post::class, 3)->create();

        $return = PostPresenter::collection($posts)->only('id')->get();

        $this->assertEquals([
            ['id' => 1], ['id' => 2], ['id' => 3],
        ], $return);
    }

    /** @test */
    public function another_presenter_can_be_used_as_a_value_when_presenting_resource()
    {
        $post = $this->createPostAndComments();

        $return = PostPresenter::make($post)->only('title')->with(function ($post) {
            return [
                'comments' => CommentPresenter::collection($post->comments)->only('id'),
            ];
        })->get();

        $this->assertEquals([
            'title' => $post->title,
            'comments' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ],
        ], $return);
    }

    /** @test */
    public function returns_null_if_relation_not_loaded_on_resource()
    {
        $post = factory(Post::class)->create();

        $return = PostPresenter::make($post)->preset('conditionalRelations')->get();

        $this->assertNull($return['comments']);
    }

    /** @test */
    public function returns_presented_relation_if_loaded_on_resource()
    {
        $post = $this->createPostAndComments();

        $post->load('comments');

        $return = PostPresenter::make($post)->preset('conditionalRelations')->get();

        $this->assertCount(3, $return['comments']);
    }

    private function createPostAndComments()
    {
        $post = factory(Post::class)->create();
        factory(Comment::class, 3)->create(['post_id' => $post->id]);

        return $post;
    }
}
