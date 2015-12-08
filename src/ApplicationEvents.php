<?php
declare(strict_types=1);

namespace Linio\Tortilla;

final class ApplicationEvents
{
    /**
     * @var string
     */
    const REQUEST = 'application.request';

    /**
     * @var string
     */
    const RESPONSE = 'application.response';

    /**
     * @var string
     */
    const TERMINATE = 'application.terminate';

    /**
     * @var string
     */
    const EXCEPTION = 'application.exception';
}
