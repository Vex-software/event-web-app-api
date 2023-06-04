<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
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
        $data = [
            'id' => $this->id,
            'name' => $this->status_name ?? '',
            'description' => $this->status_description ?? '',
            'color' => $this->status_color ?? '',
            'icon' => $this->status_icon ?? '',
            'slug' => $this->status_slug ?? '',
            'order' => $this->status_order ?? '',
        ];

        return $data;
    }
}
