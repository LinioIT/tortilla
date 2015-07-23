<?php

namespace Linio\Tortilla;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException ErrorException
     * @expectedExceptionMessage foobar
     * @expectedExceptionCode 0
     */
    public function testIsConvertingError()
    {
        ErrorHandler::handleError(0, 'foobar', 'foobar.php', 1);
    }
}
