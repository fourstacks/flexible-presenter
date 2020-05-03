<?php

namespace AdditionApps\FlexiblePresenter\Tests\Support\Paginators;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\Paginator;

class CustomPaginator extends Paginator implements Arrayable
{

    public function toArray()
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'links' => [
                'link_1' => 'foo'
            ]
        ];
    }
}