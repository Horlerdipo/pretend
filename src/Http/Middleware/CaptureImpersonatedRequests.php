<?php

namespace Horlerdipo\Pretend\Http\Middleware;

use Closure;
use Horlerdipo\Pretend\Events\ImpersonatedRequestProcessed;
use Illuminate\Http\Request;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Symfony\Component\HttpFoundation\Response;

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
                    ImpersonatedRequestProcessed::dispatch(
                        $user->currentAccessToken(),
                        $request,
                        $response
                    );
                }
            }
        }
    }
}
