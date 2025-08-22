<?php

namespace Horlerdipo\Pretend\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Contracts\HasAbilities;

class ImpersonatedRequestProcessedEvent
{
    use Dispatchable;

    public function __construct(
        public HasAbilities $accessToken,
        public Request $request,
        public Response $response,
    ) {}
}
