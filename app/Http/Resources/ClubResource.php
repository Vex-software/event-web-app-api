<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'email' => $this->email,
            'logo' => $this->logo,
            'phone_number' => $this->phone_number,
            'website' => $this->website,
            'founded_year' => $this->founded_year,
        ];

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));

            if (in_array('manager', $includes) && ! $this->relationLoaded('manager')) {
                $data['manager'] = new UserResource($this->manager);
            }

            if (in_array('users', $includes) && ! $this->relationLoaded('users')) {
                $data['users'] = UserResource::collection($this->users);
            }
        }

        if (! isset($this->id)) {
            return [];
        }

        return $data;
    }
}
