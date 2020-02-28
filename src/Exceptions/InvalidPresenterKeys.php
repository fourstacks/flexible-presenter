<?php

namespace AdditionApps\FlexiblePresenter\Exceptions;

use Exception;

class InvalidPresenterKeys extends Exception
{
    public static function keysNotDefined($invalidKeys, $method)
    {
        $invalidKeyPrefix = count($invalidKeys) === 1 ? 'key is' : 'keys are';
        $invalidKeyString = collect($invalidKeys)->join(', ', ' and ');

        $message = "Invalid keys passed to {$method}() method. ";
        $message .= "The invalid {$invalidKeyPrefix}: {$invalidKeyString}";

        return new static($message);
    }
}
