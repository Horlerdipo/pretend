<?php

namespace Horlerdipo\Pretend\Data;

use Horlerdipo\Pretend\Enums\Unit;

readonly class StartImpersonationData
{
    /**
     * @param  string[]  $abilities
     */
    public function __construct(
        public string $impersonatorType,   // e.g. App\Models\Admin
        public mixed $impersonatorId,
        public string $impersonatedType,   // e.g. App\Models\User
        public mixed $impersonatedId,
        public string $impersonationToken,
        public array $abilities,
        public int $expiresIn,
        public Unit $duration
    ) {}
}
