<?php

namespace AdditionApps\FlexiblePresenter\Exceptions;

use Exception;

class InvalidPresenterPreset extends Exception
{
    public static function methodNotFound($method)
    {
        return new static("There is no preset method on this class with the name '{$method}'");
    }
}
