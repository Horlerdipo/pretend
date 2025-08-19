<?php

namespace Horlerdipo\Pretend\Storage;

use Horlerdipo\Pretend\Contracts\HasImpersonationStorage;
use Horlerdipo\Pretend\DTOs\ImpersonationData;
use Horlerdipo\Pretend\Models\Impersonation;

class DatabaseStorage implements HasImpersonationStorage
{

    public function store(ImpersonationData $data): bool
    {
        Impersonation::query()->create([
            'impersonator_type' => $data->impersonatedType,
            'impersonator_id' => $data->impersonatedId,
            'impersonated_type' => $data->impersonatedType,
            'impersonated_id' => $data->impersonatedId,
            'key' => $data->impersonatedId,
            'used' => false,
            'expires_at' => $data->expiresAt,
            'abilities' => $data->abilities,
        ]);
        return true;
    }

    public function retrieve(string $key): ?ImpersonationData
    {
        $impersonation = Impersonation::query()->where('key', $key)->first();
        if (is_null($impersonation)) {
            return null;
        }

        return new ImpersonationData(
            impersonatorType: $impersonation->impersonator_type,
            impersonatorId: $impersonation->impersonator_id,
            impersonatedType: $impersonation->impersonated_type,
            impersonatedId: $impersonation->impersonated_id,
            impersonationKey: $impersonation->key,
            abilities: $impersonation->abilities,
            expiresAt: $impersonation->expires_at
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
