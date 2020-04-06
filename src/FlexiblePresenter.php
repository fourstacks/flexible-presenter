<?php

namespace AdditionApps\FlexiblePresenter;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\DelegatesToResource;
use AdditionApps\FlexiblePresenter\Exceptions\InvalidPresenterKeys;
use AdditionApps\FlexiblePresenter\Exceptions\InvalidPresenterPreset;
use AdditionApps\FlexiblePresenter\Contracts\FlexiblePresenterContract;

abstract class FlexiblePresenter implements FlexiblePresenterContract, Arrayable
{
    use DelegatesToResource;

    /** @var \Illuminate\Support\Collection */
    public $collection;

    /** @var mixed */
    public $resource;

    /** @var array */
    public $only = [];

    /** @var array */
    public $except = [];

    /** @var array */
    public $with = [];

    /** @var callable|null */
    protected $withCallback;

    public function __construct($data = null)
    {
        if ($data instanceof Collection) {
            $this->collection = $data;
        } else {
            $this->resource = $data;
        }
    }

    public static function make($resource): self
    {
        return new static($resource);
    }

    public static function collection($collection): self
    {
        if (is_null($collection)) {
            return new static(null);
        }

        return new static(Collection::wrap($collection));
    }

    public static function new()
    {
        return new static();
    }

    public function with(callable $callback): self
    {
        if ($this->resource) {
            $this->with = $callback($this->resource);
        } else {
            $this->withCallback = $callback;
        }

        return $this;
    }

    public function only(...$includes): self
    {
        $this->only = collect($includes)->flatten()->all();

        return $this;
    }

    public function except(...$excludes): self
    {
        $this->except = collect($excludes)->flatten()->all();

        return $this;
    }

    public function lazy($expression)
    {
        return function () use ($expression) {
            return $expression;
        };
    }

    public function all(): array
    {
        return collect($this->values())
            ->mapWithKeys(function ($value, $key) {
                return [
                    $key => ($value instanceof Closure) ? App::call($value) : $value,
                ];
            })
            ->mapWithKeys(function ($value, $key) {
                return [
                    $key => ($value instanceof Arrayable) ? $value->toArray() : $value,
                ];
            })
            ->all();
    }

    public function preset($name): self
    {
        $method = Str::start(ucfirst($name), 'preset');

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        throw InvalidPresenterPreset::methodNotFound($method);
    }

    public function get(): ?array
    {
        if (is_null($this->resource) && is_null($this->collection)) {
            return null;
        }

        if ($this->collection) {
            return $this->buildCollection();
        }

        $this->validateKeys();

        return collect($this->values())
            ->filter(function ($value, $key) {
                return empty($this->only)
                    ? true
                    : in_array($key, $this->only);
            })
            ->reject(function ($value, $key) {
                return empty($this->except)
                    ? false
                    : in_array($key, $this->except);
            })
            ->merge($this->with)
            ->mapWithKeys(function ($value, $key) {
                return [
                    $key => ($value instanceof Closure) ? App::call($value) : $value,
                ];
            })
            ->mapWithKeys(function ($value, $key) {
                return [
                    $key => ($value instanceof Arrayable) ? $value->toArray() : $value,
                ];
            })
            ->all();
    }

    public function toArray(): ?array
    {
        return $this->get();
    }

    public function whenLoaded(string $relationship)
    {
        if (! $this->resource->relationLoaded($relationship)) {
            return;
        }

        return $this->resource->{$relationship};
    }

    protected function buildCollection(): array
    {
        return $this->collection
            ->map(function ($resource) {
                $presenter = new static($resource);
                $presenter->only = $this->only;
                $presenter->except = $this->except;
                if ($this->withCallback) {
                    $presenter->with($this->withCallback);
                }

                return $presenter->get();
            })
            ->all();
    }

    protected function validateKeys(): void
    {
        $validKeys = array_merge(
            array_keys($this->values()),
            array_keys($this->with)
        );

        $this->allKeysAreValid('only', $validKeys);
        $this->allKeysAreValid('except', $validKeys);
    }

    protected function allKeysAreValid($method, $validKeys): void
    {
        if (count($invalidKeys = array_diff($this->{$method}, $validKeys))) {
            throw InvalidPresenterKeys::keysNotDefined($invalidKeys, $method);
        }
    }
}
