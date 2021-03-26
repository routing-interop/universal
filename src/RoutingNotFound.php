<?php

namespace Interop\Routing;

use InvalidArgumentException;

final class RoutingNotFound extends InvalidArgumentException
{
    protected $message = 'No routing component found. Please install one of routing-interop/dispatcher-aura, routing-interop/dispatcher-alto, routing-interop/dispatcher-fastroute, or routing-interop/dispatcher-symfony';
}
