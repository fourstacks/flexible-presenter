<?php

namespace AdditionApps\FlexiblePresenter\Tests\Support\Presenters;

use AdditionApps\FlexiblePresenter\FlexiblePresenter;

class ImagePresenter extends FlexiblePresenter
{
    public function values(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'test' => $this->pivot->test,
        ];
    }
}
