<?php

namespace Horlerdipo\Pretend\Contracts;

use Horlerdipo\Pretend\Data\RetrieveImpersonationData;
use Horlerdipo\Pretend\Data\StartImpersonationData;

interface HasImpersonationStorage
{
    public function store(StartImpersonationData $data): bool;

    public function retrieve(string $token): ?RetrieveImpersonationData;

    public function markAsUsed(string $key): bool;
}
