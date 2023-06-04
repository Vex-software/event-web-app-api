<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id ?? null,
            'name' => $this->name ?? null,
            'title' => $this->title ?? null,
            'description' => $this->description ?? null,
            'start_time' => $this->start_time ?? null,
            'end_time' => $this->end_time ?? null,
            'location' => $this->location ?? null,
            'image' => $this->image ?? null,
            'quota' => $this->quota ?? null,
            'user_count' => $this->users()->count() ?? null,
            'event_category' => new EventCategoryResource($this->category) ?? null,
        ];

        if ($request->has('include') && ! in_array('owner_club', explode(',', $request->input('include')))) {
            $data['owner_club'] = new ClubResource($this->club);
        }

        if (! isset($this->id)) {
            return [];
        }

        return $data;
    }
}
