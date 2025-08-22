<?php

use Horlerdipo\Pretend\Http\Middleware\PreventImpersonatedUserRequests;
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
});

it('prevent unauthenticated requests from going through', closure: function () {
    // ACT:
    /** @var \Illuminate\Http\Response $response */
    $response = (new PreventImpersonatedUserRequests())
        ->handle(
            request: $this->request,
            next: $this->next
        );

    // ASSERT:
    expect($response->getStatusCode())->toBe(401)
        ->and($response->getContent())
        ->toBe('Unauthorized');
});

it('prevent impersonated users requests from going through', function () {
    // ARRANGE:
    $newAccessToken = $this->user->createToken(
        config()->string('pretend.auth_token_prefix'),
        ['*'],
        now()->addMinutes(30)
    );
    $this->request->headers->set('Authorization', "Bearer $newAccessToken->plainTextToken");

    $this->request->setUserResolver(fn () => $this->user);
    $this->user->withAccessToken($newAccessToken->accessToken);

    // ACT:
    $response = (new PreventImpersonatedUserRequests())
        ->handle(
            request: $this->request,
            next: $this->next
        );

    // ASSERT:
    expect($response->getStatusCode())->toBe(403)
        ->and($response->getContent())
        ->toBe(config()->string('pretend.unauthorized_action_message'));
});

it('allows normal requests to go through', function () {
    $newAccessToken = $this->user->createToken(
        config()->string('pretend.auth_token_prefix'). '-TESTING',
        ['*'],
        now()->addMinutes(30)
    );
    $this->request->headers->set('Authorization', "Bearer $newAccessToken->plainTextToken");

    $this->request->setUserResolver(fn () => $this->user);
    $this->user->withAccessToken($newAccessToken->accessToken);

    // ACT:
    $response = (new PreventImpersonatedUserRequests())
        ->handle(
            request: $this->request,
            next: $this->next
        );

    // ASSERT:
    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())
        ->toBe($this->content);
});
