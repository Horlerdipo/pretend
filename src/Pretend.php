<?php

namespace Horlerdipo\Pretend;

use Horlerdipo\Pretend\Contracts\HasImpersonationStorage;
use Horlerdipo\Pretend\Data\StartImpersonationData;
use Horlerdipo\Pretend\Enums\Unit;
use Horlerdipo\Pretend\Events\ImpersonationCompleted;
use Horlerdipo\Pretend\Events\ImpersonationStarted;
use Horlerdipo\Pretend\Exceptions\ImpersonatedModelNotFound;
use Horlerdipo\Pretend\Exceptions\ImpersonatedModelNotSet;
use Horlerdipo\Pretend\Exceptions\ImpersonationTokenExpired;
use Horlerdipo\Pretend\Exceptions\ImpersonationTokenUsed;
use Horlerdipo\Pretend\Exceptions\ModelMissingAuthenticatableInterface;
use Horlerdipo\Pretend\Exceptions\ModelMissingHasTokenTrait;
use Horlerdipo\Pretend\Exceptions\UnknownImpersonationToken;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

class Pretend
{
    public Model $impersonator;

    public Model $impersonated;

    public int $for = 60;

    public Unit $duration = Unit::Minute;

    /**
     * @var string[]
     */
    public array $abilities = ['*'];

    final public function __construct(Model $from)
    {
        $this->impersonator = $from;
    }

    public static function from(Model $model): static
    {
        return new static($model);
    }

    /**
     * @throws ModelMissingHasTokenTrait
     * @throws ModelMissingAuthenticatableInterface
     */
    public function toBe(Model $model): self
    {
        if (! $model instanceof Authenticatable) {
            throw new ModelMissingAuthenticatableInterface("$model::class is missing the Authenticatable interface");
        }

        if (! $model instanceof HasApiTokens) {
            throw new ModelMissingHasTokenTrait("$model::class missing the Laravel\\Sanctum\\HasApiTokens trait");
        }

        $this->impersonated = $model;

        return $this;
    }

    public function for(int $time, Unit $duration = Unit::Minute): self
    {
        $this->for = $time;
        $this->duration = $duration;

        return $this;
    }

    public function seconds(): self
    {
        $this->duration = Unit::Second;

        return $this;
    }

    public function minutes(): self
    {
        $this->duration = Unit::Minute;

        return $this;
    }

    public function hours(): self
    {
        $this->duration = Unit::Hour;

        return $this;
    }

    public function days(): self
    {
        $this->duration = Unit::Day;

        return $this;
    }

    public function months(): self
    {
        $this->duration = Unit::Month;

        return $this;
    }

    public function years(): self
    {
        $this->duration = Unit::Year;

        return $this;
    }

    /**
     * @param  string[]  $abilities
     * @return $this
     */
    public function withAbilities(array $abilities): self
    {
        $this->abilities = $abilities;

        return $this;
    }

    /**
     * @throws ImpersonatedModelNotSet
     */
    public function start(): string
    {

        if (empty($this->impersonated)) {
            throw new ImpersonatedModelNotSet;
        }

        $token = Str::random(config()->integer('pretend.impersonation_token_length'));

        /** @var HasImpersonationStorage $storageImplementation */
        $storageImplementation = app(HasImpersonationStorage::class);
        $storageImplementation->store($dto = $this->buildDto($token));

        ImpersonationStarted::dispatchIf(config()->boolean('pretend.allow_events_dispatching'), $dto);

        return $token;
    }

    /**
     * @throws ImpersonatedModelNotFound
     * @throws ImpersonationTokenExpired
     * @throws ImpersonationTokenUsed
     * @throws ModelMissingHasTokenTrait
     * @throws UnknownImpersonationToken
     */
    public static function complete(string $token): NewAccessToken
    {
        /** @var HasImpersonationStorage $storageImplementation */
        $storageImplementation = app(HasImpersonationStorage::class);
        $impersonationEntry = $storageImplementation->retrieve($token);

        if (is_null($impersonationEntry)) {
            throw new UnknownImpersonationToken("Impersonation token $token does not exist");
        }

        if ($impersonationEntry->used) {
            throw new ImpersonationTokenUsed("Impersonation token $token has been used already");
        }

        if (is_null($impersonationEntry->createdAt)) {
            throw new ImpersonationTokenExpired("Impersonation token $token has expired");
        }

        if (
            $impersonationEntry->createdAt->diffInMinutes(Carbon::now()) >
            config()->integer('pretend.impersonation_token_ttl')) {
            throw new ImpersonationTokenExpired("Impersonation token $token has expired");
        }

        /** @var Model $userClass */
        $userClass = ($impersonationEntry->impersonatedType);

        $user = $userClass::query()
            ->find($impersonationEntry->impersonatedId);

        if (is_null($user)) {
            throw new ImpersonatedModelNotFound(
                'Impersonated Model does not exist');
        }

        if (! $user instanceof HasApiTokens) {
            throw new ModelMissingHasTokenTrait("$userClass is missing the Laravel\\Sanctum\\Contracts\\HasApiTokens interface");
        }

        $newAccessToken = $user->createToken(
            config()->string('pretend.auth_token_prefix'),
            $impersonationEntry->abilities,
            now()->add($impersonationEntry->duration->value, $impersonationEntry->expiresIn)
        );

        $storageImplementation->markAsUsed($token);

        ImpersonationCompleted::dispatchIf(config()->boolean('pretend.allow_events_dispatching'), $impersonationEntry, $newAccessToken);

        return $newAccessToken;
    }

    protected function buildDto(string $key): StartImpersonationData
    {
        return new StartImpersonationData(
            impersonatorType: $this->impersonator::class,
            impersonatorId: $this->impersonator->getKey(),
            impersonatedType: $this->impersonated::class,
            impersonatedId: $this->impersonated->getKey(),
            impersonationToken: $key,
            abilities: $this->abilities,
            expiresIn: $this->for,
            duration: $this->duration
        );
    }
}
