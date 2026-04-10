<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Data\HttpMiddlewareDefinition;

interface DefinesHttpMiddleware
{
    /**
     * @return array<int, HttpMiddlewareDefinition>
     */
    public function httpMiddlewareDefinitions(): array;
}
