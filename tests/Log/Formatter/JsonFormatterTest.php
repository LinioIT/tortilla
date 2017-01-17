<?php

declare(strict_types=1);

namespace Linio\Tortilla\Log\Formatter;

class JsonFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAddingTime()
    {
        $formatter = new JsonFormatter();
        $record = $formatter->format(['datetime' => new \DateTime('2000-01-01 12:00:00', new \DateTimeZone('UTC'))]);
        $result = json_decode($record, true);

        $this->assertEquals('946728000.000', $result['time']);
    }
}
