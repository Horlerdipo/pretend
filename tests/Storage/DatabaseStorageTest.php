<?php


use Horlerdipo\Pretend\Data\RetrieveImpersonationData;
use Horlerdipo\Pretend\Data\StartImpersonationData;
use Horlerdipo\Pretend\Models\Impersonation;
use Horlerdipo\Pretend\Storage\DatabaseStorage;
use Horlerdipo\Pretend\Tests\TestSupport\Models\Admin;
use Horlerdipo\Pretend\Tests\TestSupport\Models\User;

beforeEach(function () {
    $this->startImpersonationData = new StartImpersonationData(
        impersonatorType: User::class,
        impersonatorId: strval(fake()->randomDigit()),
        impersonatedType: Admin::class,
        impersonatedId: strval(fake()->randomDigit()),
        impersonationToken: Str::random(),
        abilities: ['*'],
        expiresIn: fake()->randomDigit(),
        duration: fake()->randomElement(\Carbon\Unit::cases())
    );

    $this->databaseStorage = new DatabaseStorage();

});

it('can connect to database', function () {
    $response = \Horlerdipo\Pretend\Models\Impersonation::query()
        ->create([
            'impersonator_type' => 'hi',
            'impersonator_id' => 'hi',
            'impersonated_type' => 'hi',
            'impersonated_id' => 'hi',
            'token' => 'hi',
            'used' => true,
            'expires_in' => 'hi',
            'duration' => \Carbon\Unit::Year,
            'abilities' => 'hi',
        ]);

    expect($response)->toBeInstanceOf(Impersonation::class);
});

it('can store impersonation data successfully', function () {
    //ACT:
    $response = $this->databaseStorage->store($this->startImpersonationData);

    //ASSERT:
    expect($response)->toBeTrue();
    $this->assertDatabaseHas('impersonations', [
        'impersonator_type' => $this->startImpersonationData->impersonatorType,
        'impersonator_id' => $this->startImpersonationData->impersonatorId,
        'impersonated_type' => $this->startImpersonationData->impersonatedType,
        'impersonated_id' => $this->startImpersonationData->impersonatedId,
        'token' => $this->startImpersonationData->impersonationToken,
        'used' => 0,
        'expires_in' => $this->startImpersonationData->expiresIn,
        'duration' => $this->startImpersonationData->duration,
        'abilities' => json_encode($this->startImpersonationData->abilities),
    ]);
});

it('returns null when trying to retrieve an unknown impersonation token', function () {
    //ARRANGE:
    $unknownKey = \Illuminate\Support\Str::random();

    //ACT:
    $response = $this->databaseStorage->retrieve($unknownKey);

    //ASSERT:
    expect($response)->toBeNull();
});

it('can successfully retrieve impersonation token data', function () {
    //ARRANGE:
    Impersonation::query()->create([
        'impersonator_type' => $this->startImpersonationData->impersonatorType,
        'impersonator_id' => $this->startImpersonationData->impersonatorId,
        'impersonated_type' => $this->startImpersonationData->impersonatedType,
        'impersonated_id' => $this->startImpersonationData->impersonatedId,
        'token' => $this->startImpersonationData->impersonationToken,
        'used' => 0,
        'expires_in' => $this->startImpersonationData->expiresIn,
        'duration' => $this->startImpersonationData->duration,
        'abilities' => $this->startImpersonationData->abilities,
    ]);

    //ACT:
    /** @var RetrieveImpersonationData $response */
    $response = $this->databaseStorage->retrieve($this->startImpersonationData->impersonationToken);

    //ASSERT:
    expect($response)->toBeInstanceOf(RetrieveImpersonationData::class)
        ->and($response->impersonationToken)->toBe($this->startImpersonationData->impersonationToken)
        ->and($response->used)->toBeFalse()
        ->and($response->expiresIn)->toBe($this->startImpersonationData->expiresIn)
        ->and($response->duration)->toBe($this->startImpersonationData->duration)
        ->and($response->impersonatedId)->toBe($this->startImpersonationData->impersonatedId)
        ->and($response->impersonatedType)->toBe($this->startImpersonationData->impersonatedType)
        ->and($response->impersonatorType)->toBe($this->startImpersonationData->impersonatorType)
        ->and($response->impersonatorId)->toBe($this->startImpersonationData->impersonatorId)
        ->and($response->abilities)->toBe($this->startImpersonationData->abilities);
});

it('returns false when impersonation key cannot be marked as used', function () {
    //ARRANGE:
    $unknownKey = Str::random();

    //ACT:
    $response = $this->databaseStorage->markAsUsed($unknownKey);

    //ASSERT:
    expect($response)->toBeFalse();
});

it('can successfully mark an impersonation key as used and return false', function () {
    //ARRANGE:
    Impersonation::query()->create([
        'impersonator_type' => $this->startImpersonationData->impersonatorType,
        'impersonator_id' => $this->startImpersonationData->impersonatorId,
        'impersonated_type' => $this->startImpersonationData->impersonatedType,
        'impersonated_id' => $this->startImpersonationData->impersonatedId,
        'token' => $this->startImpersonationData->impersonationToken,
        'used' => 0,
        'expires_in' => $this->startImpersonationData->expiresIn,
        'duration' => $this->startImpersonationData->duration,
        'abilities' => $this->startImpersonationData->abilities,
    ]);

    //ACT:
    /** @var RetrieveImpersonationData $response */
    $response = $this->databaseStorage->markAsUsed($this->startImpersonationData->impersonationToken);

    //ASSERT:
    expect($response)->toBeTrue();

    $this->assertDatabaseHas('impersonations', [
        'impersonator_type' => $this->startImpersonationData->impersonatorType,
        'impersonator_id' => $this->startImpersonationData->impersonatorId,
        'impersonated_type' => $this->startImpersonationData->impersonatedType,
        'impersonated_id' => $this->startImpersonationData->impersonatedId,
        'token' => $this->startImpersonationData->impersonationToken,
        'used' => true,
        'expires_in' => $this->startImpersonationData->expiresIn,
        'duration' => $this->startImpersonationData->duration,
        'abilities' => json_encode($this->startImpersonationData->abilities),
    ]);
});
