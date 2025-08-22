<?php

namespace Horlerdipo\Pretend\Http\Middleware;

use Closure;
use Horlerdipo\Pretend\Events\ImpersonatedRequestProcessedEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\Contracts\HasApiTokens;

class PreventImpersonatedUserRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ?HasApiTokens $user */
        $user = $request->user();

        if (!$user) {
            return new Response('Unauthorized', 401);
        }

        /**
         * @phpstan-ignore-next-line
         */
        if (optional($user->currentAccessToken())?->name === config()->string('pretend.auth_token_prefix')) {
            return new Response(config()->string('pretend.unauthorized_action_message'), 403);
        }

        return $next($request);
    }
}
