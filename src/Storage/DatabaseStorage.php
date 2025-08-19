<?php

namespace Horlerdipo\Pretend\Storage;

use Horlerdipo\Pretend\Contracts\HasImpersonationStorage;
use Horlerdipo\Pretend\Data\RetrieveImpersonationData;
use Horlerdipo\Pretend\Data\StartImpersonationData;
use Horlerdipo\Pretend\Models\Impersonation;

class DatabaseStorage implements HasImpersonationStorage
{
    public function store(StartImpersonationData $data): bool
    {
        Impersonation::query()->create([
            'impersonator_type' => $data->impersonatedType,
            'impersonator_id' => $data->impersonatedId,
            'impersonated_type' => $data->impersonatedType,
            'impersonated_id' => $data->impersonatedId,
            'key' => $data->impersonatedId,
            'used' => false,
            'expires_in' => $data->expiresIn,
            'duration' => $data->duration,
            'abilities' => $data->abilities,
        ]);

        return true;
    }

    public function retrieve(string $key): ?RetrieveImpersonationData
    {
        $impersonation = Impersonation::query()->where('key', $key)->first();
        if (is_null($impersonation)) {
            return null;
        }

        return new RetrieveImpersonationData(
            impersonatorType: $impersonation->impersonator_type,
            impersonatorId: $impersonation->impersonator_id,
            impersonatedType: $impersonation->impersonated_type,
            impersonatedId: $impersonation->impersonated_id,
            impersonationToken: $impersonation->key,
            abilities: $impersonation->abilities,
            expiresIn: $impersonation->expires_in,
            duration: $impersonation->duration,
            used: $impersonation->used,
            createdAt: $impersonation->created_at,
        );
    }

    public function markAsUsed(string $key): bool
    {
        $count = Impersonation::query()->where('key', $key)->update([
            'used' => true,
        ]);

        return $count > 0;
    }
}
