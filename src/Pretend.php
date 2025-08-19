<?php

namespace Horlerdipo\Pretend;

use Carbon\Unit;
use Horlerdipo\Pretend\Contracts\HasImpersonationStorage;
use Horlerdipo\Pretend\DTOs\ImpersonationData;
use Horlerdipo\Pretend\Exceptions\ModelMissingAuthenticatableInterface;
use Horlerdipo\Pretend\Exceptions\ModelMissingHasTokenTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

class Pretend
{
    // Pretend::from($admin)
    // ->toBe($user)
    // ->for('10 minutes')
    // ->minutes()
    // ->withAbilities(['*'])
    // ->start();

    public Model $impersonator;

    public Model $impersonated;

    public int $for;

    public Unit $duration;

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
     * @throws ReflectionException
     * @throws ModelMissingHasTokenTrait
     * @throws ModelMissingAuthenticatableInterface
     */
    public function toBe(Model $model): self
    {
        if ($model instanceof Authenticatable) {
            throw new ModelMissingAuthenticatableInterface("$model::class is missing the Authenticatable interface");
        }

        $reflectedClass = new ReflectionClass($model::class);
        if (
            ! in_array('Laravel\Sanctum\HasApiTokens', $reflectedClass->getTraitNames())
        ) {
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

    public function start(): string
    {

        $key = Str::random(config()->integer('pretend.impersonation_key_length'));

        /** @var HasImpersonationStorage $storageImplementation */
        $storageImplementation = app(HasImpersonationStorage::class);
        $storageImplementation->store($this->buildDto($key));

        return $key;
    }

    protected function buildDto(string $key): ImpersonationData
    {
        return new ImpersonationData(
            impersonatorType: $this->impersonator::class,
            impersonatorId: $this->impersonator->getKey(),
            impersonatedType: $this->impersonated::class,
            impersonatedId: $this->impersonated->getKey(),
            impersonationKey: $key,
            abilities: $this->abilities,
            expiresAt: Carbon::now()->add(
                $this->duration,
                $this->for
            )
        );
    }
}
