<?php

namespace AdditionApps\FlexiblePresenter\Tests\Support\Presenters;

use AdditionApps\FlexiblePresenter\FlexiblePresenter;

class StandalonePresenter extends FlexiblePresenter
{
    public function values(): array
    {
        return [
            'foo' => 'bar',
        ];
    }
}
