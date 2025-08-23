<?php

namespace Horlerdipo\Pretend\Http\Middleware;

use Closure;
use Horlerdipo\Pretend\Events\ImpersonatedRequestProcessedEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\Contracts\HasApiTokens;

class CaptureImpersonatedRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (config()->boolean('pretend.allow_events_dispatching')) {
            /** @var ?HasApiTokens $user */
            $user = $request->user();

            if ($user) {
                /**
                 * @phpstan-ignore-next-line
                 */
                if (optional($user->currentAccessToken())?->name === config()->string('pretend.auth_token_prefix')) {
                    ImpersonatedRequestProcessedEvent::dispatch(
                        $user->currentAccessToken(),
                        $request,
                        $response
                    );
                }
            }
        }
    }
}
