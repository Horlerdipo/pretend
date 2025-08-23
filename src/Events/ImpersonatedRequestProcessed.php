<?php

namespace Horlerdipo\Pretend\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Laravel\Sanctum\Contracts\HasAbilities;
use Symfony\Component\HttpFoundation\Response;

class ImpersonatedRequestProcessed
{
    use Dispatchable;

    public function __construct(
        public HasAbilities $accessToken,
        public Request $request,
        public Response $response,
    ) {}
}
