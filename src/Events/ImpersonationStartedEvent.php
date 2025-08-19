<?php

namespace Horlerdipo\Pretend\Events;

use Horlerdipo\Pretend\Data\StartImpersonationData;
use Illuminate\Foundation\Events\Dispatchable;

class ImpersonationStartedEvent
{
    use Dispatchable;

    public function __construct(public StartImpersonationData $impersonationData)
    {
    }
}
