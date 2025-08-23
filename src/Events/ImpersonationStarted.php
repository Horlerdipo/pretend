<?php

namespace Horlerdipo\Pretend\Events;

use Horlerdipo\Pretend\Data\StartImpersonationData;
use Illuminate\Foundation\Events\Dispatchable;

class ImpersonationStarted
{
    use Dispatchable;

    public function __construct(public StartImpersonationData $impersonationData) {}
}
