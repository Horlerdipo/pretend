<?php

use Horlerdipo\Pretend\Events\ImpersonatedRequestProcessed;
use Horlerdipo\Pretend\Http\Middleware\CaptureImpersonatedRequests;
use Horlerdipo\Pretend\Tests\TestSupport\Models\User;

beforeEach(closure: function () {
    $this->user = User::query()->create([
        'email' => fake()->email,
        'name' => fake()->name,
    ]);

    $this->request = \Illuminate\Http\Request::create(
        uri: 'testing',
        parameters: [
            'action' => 'testing',
            'project' => 'pretend',
        ]
    );

    $this->content = json_encode([
        'action' => 'testing',
        'project' => 'pretend',
    ]);

    $this->next = function () {
        return response($this->content);
    };

    Event::fake();
});

it('does not capture unauthenticated requests', closure: function () {
    // ACT:
    $response = (new CaptureImpersonatedRequests)
        ->handle(
            request: $this->request,
            next: $this->next
        );

    (new CaptureImpersonatedRequests)->terminate($this->request, $response);
    // ASSERT:
    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())
        ->toBe($this->content);

    Event::assertNotDispatched(ImpersonatedRequestProcessed::class);
});

it('only capture requests with impersonated users', function () {
    // ARRANGE:
    $newAccessToken = $this->user->createToken(
        config()->string('pretend.auth_token_prefix').'-TESTING',
        ['*'],
        now()->addMinutes(30)
    );
    $this->request->headers->set('Authorization', "Bearer $newAccessToken->plainTextToken");

    $this->request->setUserResolver(fn () => $this->user);
    $this->user->withAccessToken($newAccessToken->accessToken);

    // ACT:
    $response = (new CaptureImpersonatedRequests)
        ->handle(
            request: $this->request,
            next: $this->next
        );
    (new CaptureImpersonatedRequests)->terminate($this->request, $response);

    // ASSERT:
    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())
        ->toBe($this->content);

    Event::assertNotDispatched(ImpersonatedRequestProcessed::class);
});

it('only capture requests when the config is set', function () {
    config()->set('pretend.allow_events_dispatching', false);

    $newAccessToken = $this->user->createToken(
        config()->string('pretend.auth_token_prefix'),
        ['*'],
        now()->addMinutes(30)
    );
    $this->request->headers->set('Authorization', "Bearer $newAccessToken->plainTextToken");

    $this->request->setUserResolver(fn () => $this->user);
    $this->user->withAccessToken($newAccessToken->accessToken);

    // ACT:
    $response = (new CaptureImpersonatedRequests)
        ->handle(
            request: $this->request,
            next: $this->next
        );
    (new CaptureImpersonatedRequests)->terminate($this->request, $response);

    // ASSERT:
    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())
        ->toBe($this->content);

    Event::assertNotDispatched(ImpersonatedRequestProcessed::class);
});

it('successfully dispatches the event when the request is from an impersonated user and the config is set', function () {
    config()->set('pretend.allow_events_dispatching', true);

    $newAccessToken = $this->user->createToken(
        config()->string('pretend.auth_token_prefix'),
        ['*'],
        now()->addMinutes(30)
    );
    $this->request->headers->set('Authorization', "Bearer $newAccessToken->plainTextToken");

    $this->request->setUserResolver(fn () => $this->user);
    $this->user->withAccessToken($newAccessToken->accessToken);

    // ACT:
    $response = (new CaptureImpersonatedRequests)
        ->handle(
            request: $this->request,
            next: $this->next
        );
    (new CaptureImpersonatedRequests)->terminate($this->request, $response);

    // ASSERT:
    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())
        ->toBe($this->content);

    Event::assertDispatched(ImpersonatedRequestProcessed::class);
});
