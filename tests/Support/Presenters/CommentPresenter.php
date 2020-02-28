<?php

namespace AdditionApps\FlexiblePresenter\Tests\Support\Presenters;

use AdditionApps\FlexiblePresenter\FlexiblePresenter;

class CommentPresenter extends FlexiblePresenter
{

    public function values(): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
        ];
    }
}
