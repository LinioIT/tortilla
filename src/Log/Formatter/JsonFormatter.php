<?php

declare(strict_types=1);

namespace Linio\Tortilla\Log\Formatter;

use Monolog\Formatter\JsonFormatter as BaseJsonFormatter;

class JsonFormatter extends BaseJsonFormatter
{
    public function format(array $record): string
    {
        if (isset($record['datetime'])) {
            $record['time'] = substr($record['datetime']->format('U.u'), 0, -3);
        }

        return parent::format($record);
    }
}
