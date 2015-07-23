<?php

namespace Linio\Tortilla;

class ErrorHandler
{
    public static function register()
    {
        set_error_handler([__CLASS__, 'handleError']);
        register_shutdown_function([__CLASS__, 'handleFatal']);
    }

    public static function handleError($severity, $message, $file, $line)
    {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleFatal()
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        throw new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
    }
}
