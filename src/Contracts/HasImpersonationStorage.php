<?php

namespace Horlerdipo\Pretend\Contracts;

use Horlerdipo\Pretend\DTOs\ImpersonationData;

interface HasImpersonationStorage
{
    public function store(ImpersonationData $data): bool;

    public function retrieve(string $key): ?ImpersonationData;

    public function markAsUsed(string $key): bool;
}
