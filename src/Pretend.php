<?php

namespace Horlerdipo\Pretend;

use Horlerdipo\Pretend\Enums\Duration;
use Horlerdipo\Pretend\Exceptions\ModelMissingAuthenticatableInterface;
use Horlerdipo\Pretend\Exceptions\ModelMissingHasTokenTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
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

    public Model $from;
    public Model $toBe;

    public int $for;

    public Duration $duration;

    /**
     * @var string[]
     */
    public array $abilities = ['*'];


    final public function __construct(Model $from)
    {
        $this->from = $from;
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
        if(
            !in_array( 'Laravel\Sanctum\HasApiTokens', $reflectedClass->getTraitNames())
        ){
            throw new ModelMissingHasTokenTrait("$model::class missing the Laravel\\Sanctum\\HasApiTokens trait");
        }

        $this->toBe = $model;
        return $this;
    }

    public function for(int $time, Duration $duration = Duration::MINUTE): self
    {
        $this->for = $time;
        $this->duration = $duration;
        return $this;
    }

    public function seconds(): self {
        $this->duration = Duration::SECOND;
        return $this;
    }

    public function minutes(): self {
        $this->duration = Duration::MINUTE;
        return $this;
    }

    public function hours(): self {
        $this->duration = Duration::HOUR;
        return $this;
    }

    public function days(): self {
        $this->duration = Duration::DAY;
        return $this;
    }

    public function months(): self {
        $this->duration = Duration::MONTH;
        return $this;
    }

    public function years(): self {
        $this->duration = Duration::YEAR;
        return $this;
    }

    /**
     * @param string[] $abilities
     * @return $this
     */
    public function withAbilities(array $abilities): self {
        $this->abilities = $abilities;
        return $this;
    }

    public function start(): string {
        return '';
    }
}
