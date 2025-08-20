<?php

namespace Horlerdipo\Pretend\Events;

use Horlerdipo\Pretend\Data\RetrieveImpersonationData;
use Illuminate\Foundation\Events\Dispatchable;
use Laravel\Sanctum\NewAccessToken;

class ImpersonationCompletedEvent
{
    use Dispatchable;

    public function __construct(public RetrieveImpersonationData $impersonationData, public NewAccessToken $accessToken) {}
}
