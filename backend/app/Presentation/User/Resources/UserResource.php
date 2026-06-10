<?php

namespace App\Presentation\User\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // the resource is expected to be given a UserEntity
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
