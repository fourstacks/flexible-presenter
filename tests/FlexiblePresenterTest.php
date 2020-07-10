<?php

namespace AdditionApps\FlexiblePresenter\Tests;

use AdditionApps\FlexiblePresenter\Tests\Support\Models\Image;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use AdditionApps\FlexiblePresenter\FlexiblePresenter;
use AdditionApps\FlexiblePresenter\Tests\Support\Models\Post;
use AdditionApps\FlexiblePresenter\Tests\Support\Models\Comment;
use AdditionApps\FlexiblePresenter\Exceptions\InvalidPresenterKeys;
use AdditionApps\FlexiblePresenter\Tests\Support\Presenters\PostPresenter;
use AdditionApps\FlexiblePresenter\Tests\Support\Paginators\CustomPaginator;
use AdditionApps\FlexiblePresenter\Tests\Support\Presenters\CommentPresenter;
use AdditionApps\FlexiblePresenter\Tests\Support\Presenters\StandalonePresenter;

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
        $presenter = StandalonePresenter::new();

        $this->assertInstanceOf(FlexiblePresenter::class, $presenter);
        $this->assertNull($presenter->resource);
        $this->assertNull($presenter->collection);

        $this->assertEquals(['foo' => 'bar'], $presenter->toArray());
    }

    /** @test */
    public function new_presenter_instance_instantiated_input_paginator()
    {
        $currentPage = 1;
        $perPage = 2;

        $posts = factory(Post::class, 3)->create();

        $paginationCollection = new Paginator(
            $posts->forPage($currentPage, $perPage),
            $perPage,
            $currentPage
        );

        $presenter = PostPresenter::collection($paginationCollection);

        $this->assertInstanceOf(FlexiblePresenter::class, $presenter);
        $this->assertInstanceOf(Paginator::class, $presenter->paginationCollection);
        $this->assertCount(2, $presenter->paginationCollection->getCollection());
    }

    /** @test */
    public function new_presenter_instance_instantiated_input_length_aware_paginator()
    {
        $currentPage = 1;
        $perPage = 2;

        $posts = factory(Post::class, 3)->create();

        $paginationCollection = new LengthAwarePaginator(
            $posts->forPage($currentPage, $perPage),
            $posts->count(),
            $perPage,
            $currentPage
        );

        $presenter = PostPresenter::collection($paginationCollection);

        $this->assertInstanceOf(FlexiblePresenter::class, $presenter);
        $this->assertInstanceOf(LengthAwarePaginator::class, $presenter->paginationCollection);
        $this->assertCount(2, $presenter->paginationCollection->getCollection());
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
    public function paginator_collection_of_models_are_presented()
    {
        $posts = factory(Post::class, 3)->create();

        $paginationCollection = new Paginator(
            $posts->forPage($currentPage = 1, $perPage = 2),
            $perPage,
            $currentPage
        );

        $return = PostPresenter::collection($paginationCollection)->only('id')->get();

        $this->assertEquals([
            'current_page' => 1,
            'data' => [
                ['id' => 1],
                ['id' => 2],
            ],
            'first_page_url' => '/?page=1',
            'from' => 1,
            'next_page_url' => null,
            'path' => '/',
            'per_page' => 2,
            'prev_page_url' => null,
            'to' => 2,
        ], $return);
    }

    /** @test */
    public function length_aware_paginator_collection_of_models_are_presented()
    {
        $posts = factory(Post::class, 3)->create();

        $paginationCollection = new LengthAwarePaginator(
            $posts->forPage($currentPage = 1, $perPage = 2),
            $posts->count(),
            $perPage,
            $currentPage
        );

        $return = PostPresenter::collection($paginationCollection)->only('id')->get();

        $this->assertEquals([
            'current_page' => 1,
            'data' => [
                ['id' => 1],
                ['id' => 2],
            ],
            'first_page_url' => '/?page=1',
            'from' => 1,
            'last_page' => 2,
            'last_page_url' => '/?page=2',
            'next_page_url' => '/?page=2',
            'path' => '/',
            'per_page' => 2,
            'prev_page_url' => null,
            'to' => 2,
            'total' => 3,
        ], $return);
    }

    /** @test */
    public function extra_key_value_pairs_are_appended_to_wrapped_presenter_when_keys_do_not_exist()
    {
        $posts = factory(Post::class, 3)->create();

        $paginationCollection = new Paginator(
            $posts->forPage($currentPage = 1, $perPage = 2),
            $perPage,
            $currentPage
        );

        $return = PostPresenter::collection($paginationCollection)
            ->only('id')
            ->appends(['foo' => 'bar', 'baz' => 'qux'])
            ->get();

        $this->assertEquals([
            'current_page' => 1,
            'data' => [
                ['id' => 1],
                ['id' => 2],
            ],
            'first_page_url' => '/?page=1',
            'from' => 1,
            'next_page_url' => null,
            'path' => '/',
            'per_page' => 2,
            'prev_page_url' => null,
            'to' => 2,
            'foo' => 'bar',
            'baz' => 'qux',
        ], $return);
    }

    /** @test */
    public function extra_key_value_pairs_are_appended_to_wrapped_presenter_recursively()
    {
        $posts = factory(Post::class, 3)->create();

        $paginationCollection = new CustomPaginator(
            $posts->forPage($currentPage = 1, $perPage = 2),
            $perPage,
            $currentPage
        );

        $return = PostPresenter::collection($paginationCollection)
            ->only('id')
            ->appends([
                'foo' => ['test' => 'foo'],
                'links' => ['link_2' => 'bar'],
            ])
            ->get();

        $this->assertEquals([
            'current_page' => 1,
            'data' => [
                ['id' => 1],
                ['id' => 2],
            ],
            'first_page_url' => '/?page=1',
            'from' => 1,
            'next_page_url' => null,
            'path' => '/',
            'per_page' => 2,
            'prev_page_url' => null,
            'to' => 2,
            'foo' => [
                'test' => 'foo',
            ],
            'links' => [
                'link_1' => 'foo',
                'link_2' => 'bar',
            ],
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

    /** @test */
    public function can_use_pivot_data_on_nested_presenter_resource()
    {
        $post = factory(Post::class)->create();
        $images = factory(Image::class, 3)->create();

        $attachments = $images->mapWithKeys(function($image){
            return [$image->id => ['test' => 'foo_' . $image->id]];
        })->all();
        $post->images()->attach($attachments);
        $post->load('images');

        $return = PostPresenter::make($post)->preset('pivotRelations')->get();

        $this->assertCount(3, $return['images']);
        $this->assertEquals([
            'id' => 1,
            'url' => 'foo',
            'test' => 'foo_1'
        ],$return['images'][0]);
        $this->assertEquals([
            'id' => 2,
            'url' => 'foo',
            'test' => 'foo_2'
        ],$return['images'][1]);
        $this->assertEquals([
            'id' => 3,
            'url' => 'foo',
            'test' => 'foo_3'
        ],$return['images'][2]);
    }

    private function createPostAndComments()
    {
        $post = factory(Post::class)->create();
        factory(Comment::class, 3)->create(['post_id' => $post->id]);

        return $post;
    }
}
