<?php

namespace Horlerdipo\Pretend\DTOs;

use Carbon\CarbonInterface;

readonly class ImpersonationData
{
    /**
     * @param  string[]  $abilities
     */
    public function __construct(
        public string $impersonatorType,   // e.g. App\Models\Admin
        public mixed $impersonatorId,
        public string $impersonatedType,   // e.g. App\Models\User
        public mixed $impersonatedId,
        public string $impersonationKey,
        public array $abilities,
        public CarbonInterface $expiresAt,
    ) {}
}
