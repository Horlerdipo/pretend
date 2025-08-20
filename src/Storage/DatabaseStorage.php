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
            'impersonator_type' => $data->impersonatorType,
            'impersonator_id' => $data->impersonatorId,
            'impersonated_type' => $data->impersonatedType,
            'impersonated_id' => $data->impersonatedId,
            'used' => false,
            'expires_in' => $data->expiresIn,
            'duration' => $data->duration,
            'abilities' => $data->abilities,
            'token' => $data->impersonationToken,
        ]);

        return true;

    }

    public function retrieve(string $token): ?RetrieveImpersonationData
    {
        $impersonation = Impersonation::query()->where('token', $token)->first();
        if (is_null($impersonation)) {
            return null;
        }

        return new RetrieveImpersonationData(
            impersonatorType: $impersonation->impersonator_type,
            impersonatorId: $impersonation->impersonator_id,
            impersonatedType: $impersonation->impersonated_type,
            impersonatedId: $impersonation->impersonated_id,
            impersonationToken: $impersonation->token,
            abilities: $impersonation->abilities,
            expiresIn: $impersonation->expires_in,
            duration: $impersonation->duration,
            used: $impersonation->used,
            createdAt: $impersonation->created_at,
        );
    }

    public function markAsUsed(string $key): bool
    {
        $count = Impersonation::query()->where('token', $key)->update([
            'used' => true,
        ]);

        return $count > 0;
    }
}
