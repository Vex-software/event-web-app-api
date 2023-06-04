<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialMediaLinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! isset($this->id)) {
            return [];
        }

        return [
            'id' => $this->id ?? null,
            'name' => $this->name ?? null,
            'link' => $this->link ?? null,
            // 'icon' => $this->icon ?? null,
        ];
    }
}
