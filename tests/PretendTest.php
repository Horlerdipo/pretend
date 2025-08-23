<?php

use Carbon\Unit;
use Horlerdipo\Pretend\Events\ImpersonationCompleted;
use Horlerdipo\Pretend\Events\ImpersonationStarted;
use Horlerdipo\Pretend\Exceptions\ImpersonatedModelNotFound;
use Horlerdipo\Pretend\Exceptions\ImpersonationTokenExpired;
use Horlerdipo\Pretend\Exceptions\ImpersonationTokenUsed;
use Horlerdipo\Pretend\Exceptions\ModelMissingAuthenticatableInterface;
use Horlerdipo\Pretend\Exceptions\ModelMissingHasTokenTrait;
use Horlerdipo\Pretend\Exceptions\UnknownImpersonationToken;
use Horlerdipo\Pretend\Models\Impersonation;
use Horlerdipo\Pretend\Pretend;
use Horlerdipo\Pretend\Tests\TestSupport\Models\Admin;
use Horlerdipo\Pretend\Tests\TestSupport\Models\User;
use Horlerdipo\Pretend\Tests\TestSupport\Models\UserWithoutAuthInterface;
use Horlerdipo\Pretend\Tests\TestSupport\Models\UserWithoutHasTokenInterface;
use Laravel\Sanctum\NewAccessToken;

beforeEach(function () {
    $this->user = User::query()->create([
        'email' => fake()->email,
        'name' => fake()->name,
    ]);

    $this->admin = Admin::query()->create([
        'email' => fake()->email,
        'name' => fake()->name,
    ]);

    Event::fake();
});

it('can successfully add the impersonator model', function () {
    // ACT:
    $object = Pretend::from($this->admin);

    // ASSERT:
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->impersonator)
        ->toBe($this->admin);
});

it('can successfully chain the impersonated model', function () {
    // ACT:
    $object = Pretend::from($this->admin)
        ->toBe($this->user);

    // ASSERT:
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->impersonator)
        ->toBe($this->admin)
        ->and($object->impersonated)
        ->toBe($this->user);
});

it('throws an exception if the impersonated model does not implement Authenticatable', function () {
    // ARRANGE:
    $userWithoutHasAuthInterface = UserWithoutAuthInterface::query()->create([
        'email' => fake()->email,
        'name' => fake()->name,
    ]);

    // ACT:
    $object = Pretend::from($this->admin)
        ->toBe($userWithoutHasAuthInterface);

})->throws(ModelMissingAuthenticatableInterface::class);

it('throws an exception if the impersonated model does not implement HasToken', function () {
    // ARRANGE:
    $userWithoutHasToken = UserWithoutHasTokenInterface::query()->create([
        'email' => fake()->email,
        'name' => fake()->name,
    ]);

    // ACT:
    $object = Pretend::from($this->admin)
        ->toBe($userWithoutHasToken);

})->throws(ModelMissingHasTokenTrait::class);

it('can successfully add time without unit', function () {
    // ACT:
    $object = Pretend::from($this->admin)
        ->for(10);

    // ASSERT:
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->duration->name)
        ->toEqual(Unit::Minute->name)
        ->and($object->for)
        ->toEqual(10);
});

it('can successfully add time and unit', function () {
    // ACT:
    $object = Pretend::from($this->admin)
        ->for(10, Unit::Hour);

    // ASSERT:
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->duration->name)
        ->toEqual(Unit::Hour->name)
        ->and($object->for)
        ->toEqual(10);
});

it('can successfully set different duration units', function () {
    // ACT:
    $object = Pretend::from($this->admin)
        ->for(10)
        ->seconds();

    // ASSERT:
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->duration->name)
        ->toEqual(Unit::Second->name);

    $object = $object->minutes();
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->duration->name)
        ->toEqual(Unit::Minute->name);

    $object = $object->minutes();
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->duration->name)
        ->toEqual(Unit::Minute->name);

    $object = $object->hours();
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->duration->name)
        ->toEqual(Unit::Hour->name);

    $object = $object->days();
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->duration->name)
        ->toEqual(Unit::Day->name);

    $object = $object->months();
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->duration->name)
        ->toEqual(Unit::Month->name);

    $object = $object->years();
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->duration->name)
        ->toEqual(Unit::Year->name);
});

it('can successfully add abilities', function () {
    // ACT:
    $object = Pretend::from($this->admin)
        ->withAbilities(['testing']);

    // ASSERT:
    expect($object)->toBeInstanceOf(Pretend::class)
        ->and($object->abilities)
        ->toEqual(['testing']);
});

it('can successfully start an impersonation and event is dispatched if the config is set', function () {
    // ARRANGE:
    config()->set('pretend.allow_events_dispatching', true);

    // ACT:
    $impersonationToken = Pretend::from($this->admin)
        ->toBe($this->user)
        ->for(10)
        ->minutes()
        ->withAbilities(['testing'])
        ->start();

    // ASSERT:
    expect($impersonationToken)->toBeString();

    $this->assertDatabaseHas('impersonations', [
        'impersonator_type' => Admin::class,
        'impersonator_id' => $this->admin->id,
        'impersonated_type' => User::class,
        'impersonated_id' => $this->user->id,
        'token' => $impersonationToken,
        'used' => 0,
        'expires_in' => 10,
        'duration' => Unit::Minute,
        'abilities' => json_encode(['testing']),
    ]);

    \Illuminate\Support\Facades\Event::assertDispatched(ImpersonationStarted::class);
});

it('can successfully start an impersonation and event is not dispatched if the config is set to false', function () {
    // ARRANGE:
    config()->set('pretend.allow_events_dispatching', false);

    // ACT:
    $impersonationToken = Pretend::from($this->admin)
        ->toBe($this->user)
        ->for(10)
        ->minutes()
        ->withAbilities(['testing'])
        ->start();

    // ASSERT:
    expect($impersonationToken)->toBeString();

    $this->assertDatabaseHas('impersonations', [
        'impersonator_type' => Admin::class,
        'impersonator_id' => $this->admin->id,
        'impersonated_type' => User::class,
        'impersonated_id' => $this->user->id,
        'token' => $impersonationToken,
        'used' => 0,
        'expires_in' => 10,
        'duration' => Unit::Minute,
        'abilities' => json_encode(['testing']),
    ]);

    \Illuminate\Support\Facades\Event::assertNotDispatched(ImpersonationStarted::class);
});

it('can successfully complete impersonation and dispatch event if the config is set', function () {
    // ARRANGE:
    config()->set('pretend.impersonation_token_ttl', 10);
    $impersonationToken = Str::random(32);

    Impersonation::query()->create([
        'impersonator_type' => Admin::class,
        'impersonator_id' => strval($this->admin->id),
        'impersonated_type' => User::class,
        'impersonated_id' => strval($this->user->id),
        'token' => $impersonationToken,
        'used' => 0,
        'expires_in' => 10,
        'duration' => Unit::Minute,
        'abilities' => ['testing'],
    ]);

    // ACT:
    $accessToken = Pretend::complete($impersonationToken);

    // ASSERT:
    expect($accessToken)->toBeInstanceOf(NewAccessToken::class);
    Event::assertDispatched(ImpersonationCompleted::class);

    $this->assertDatabaseHas('impersonations', [
        'impersonator_type' => Admin::class,
        'impersonator_id' => $this->admin->id,
        'impersonated_type' => User::class,
        'impersonated_id' => $this->user->id,
        'token' => $impersonationToken,
        'used' => 1,
        'expires_in' => 10,
        'duration' => Unit::Minute,
        'abilities' => json_encode(['testing']),
    ]);
});

describe('Pretend::complete', function () {

    it('throws an exception if the impersonation token is not found', function () {
        // ARRANGE:
        $impersonationToken = Str::random(32);

        // ACT:
        $accessToken = Pretend::complete($impersonationToken);

    })->throws(UnknownImpersonationToken::class);

    it('throws an exception if the impersonation token has been used', function () {
        // ARRANGE:
        $impersonationToken = Str::random(32);
        Impersonation::query()->create([
            'impersonator_type' => Admin::class,
            'impersonator_id' => strval($this->admin->id),
            'impersonated_type' => User::class,
            'impersonated_id' => strval($this->user->id),
            'token' => $impersonationToken,
            'used' => true,
            'expires_in' => 10,
            'duration' => Unit::Minute,
            'abilities' => ['testing'],
        ]);

        // ACT:
        $accessToken = Pretend::complete($impersonationToken);

    })->throws(ImpersonationTokenUsed::class);

    it('throws an exception if the impersonation token has expired', function () {
        // ARRANGE:
        config()->set('pretend.impersonation_token_ttl', 10);
        \Pest\Laravel\travelTo(now()->startOfDay());

        $impersonationToken = Str::random(32);
        Impersonation::query()->create([
            'impersonator_type' => Admin::class,
            'impersonator_id' => strval($this->admin->id),
            'impersonated_type' => User::class,
            'impersonated_id' => strval($this->user->id),
            'token' => $impersonationToken,
            'used' => false,
            'expires_in' => 10,
            'duration' => Unit::Minute,
            'abilities' => ['testing'],
        ]);
        \Pest\Laravel\travelBack();

        // ACT:
        $accessToken = Pretend::complete($impersonationToken);

    })->throws(ImpersonationTokenExpired::class);

    it('throws an exception if the impersonated model no longer exists', function () {
        // ARRANGE:
        $impersonationToken = Str::random(32);
        Impersonation::query()->create([
            'impersonator_type' => Admin::class,
            'impersonator_id' => strval($this->admin->id),
            'impersonated_type' => User::class,
            'impersonated_id' => strval($this->user->id),
            'token' => $impersonationToken,
            'used' => false,
            'expires_in' => 10,
            'duration' => Unit::Minute,
            'abilities' => ['testing'],
        ]);
        $this->user->delete();

        // ACT:
        $accessToken = Pretend::complete($impersonationToken);

    })->throws(ImpersonatedModelNotFound::class);

    it('can successfully complete impersonation and will not dispatch event if the config is set to falsse', function () {
        // ARRANGE:
        config()->set('pretend.impersonation_token_ttl', 10);
        config()->set('pretend.allow_events_dispatching', false);
        $impersonationToken = Str::random(32);

        Impersonation::query()->create([
            'impersonator_type' => Admin::class,
            'impersonator_id' => strval($this->admin->id),
            'impersonated_type' => User::class,
            'impersonated_id' => strval($this->user->id),
            'token' => $impersonationToken,
            'used' => 0,
            'expires_in' => 10,
            'duration' => Unit::Minute,
            'abilities' => ['testing'],
        ]);

        // ACT:
        $accessToken = Pretend::complete($impersonationToken);

        // ASSERT:
        expect($accessToken)->toBeInstanceOf(NewAccessToken::class);
        Event::assertNotDispatched(ImpersonationCompleted::class);

        $this->assertDatabaseHas('impersonations', [
            'impersonator_type' => Admin::class,
            'impersonator_id' => $this->admin->id,
            'impersonated_type' => User::class,
            'impersonated_id' => $this->user->id,
            'token' => $impersonationToken,
            'used' => 1,
            'expires_in' => 10,
            'duration' => Unit::Minute,
            'abilities' => json_encode(['testing']),
        ]);
    });
});
