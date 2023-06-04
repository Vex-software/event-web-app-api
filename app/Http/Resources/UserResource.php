<?php

namespace App\Http\Resources;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name ?? '',
            'surname' => $this->surname ?? '',
            'trust_score' => $this->trust_score ?? null,
        ];

        $loggedInUser = $request->user();

        if ($loggedInUser && $loggedInUser->isAdmin()) {
            $this->addAdminData($data);
        }

        if ($loggedInUser && $loggedInUser->id === $this->id) {
            $this->addSelfData($data);
        }

        $this->addOtherUserData($data);

        if (! isset($this->id)) {
            return [];
        }

        return $data;
    }

    /**
     * Add data for admin.
     */
    private function addAdminData(array &$data): void
    {
        $data = array_merge($data, [
            'phone_number' => $this->phone_number ?? null,
            'email' => $this->email ?? null,
            'profile_photo' => $this->profile_photo_path ?? null,
            'address' => $this->address ?? null,
            'city_id' => $this->city_id ?? new CityResource($this->city),
            'role' => new RoleResource($this->role()->first()),

            'social_media' => new SocialMediaLinkResource($this->socialMediaLink()->first()),
            'email_verified_at' => $this->email_verified_at ? $this->email_verified_at : null,
            'deleted_at' => $this->deleted_at ? $this->deleted_at : null,
            'google_id' => $this->google_id ?? null,
            'access_token_expires_at' => $this->access_token_expires_at ?? null,
            'last_login_at' => $this->last_login_at ?? null,
            'last_activity_at' => $this->last_activity_at ?? null,
            'phone_number_verified_at' => $this->phone_number_verified_at ?? null,
            'status' => Status::where('id', $this->status_id)->first()->status_name ?? null,
        ]);
    }

    /**
     * Add data for self.
     */
    private function addSelfData(array &$data): void
    {
        $data = array_merge($data, [
            'phone_number' => $this->phone_number ?? null,
            'email' => $this->email ?? null,
            'profile_photo' => $this->profile_photo_path ?? 'Profil fotoğrafı bulunmamaktadır.',
            'address' => $this->address ?? 'Adres bilgisi bulunmamaktadır.',
            'city' => new CityResource($this->city),
            'role' => new RoleResource($this->role()->first()) ?? 'Rol bilgisi bulunmamaktadır.',
            'social_media' => $this->social_media_id ?? null,
            'email_verified_at' => $this->email_verified_at->format('Y-m-d H:i:s') ?? null,
            'last_login_at' => $this->last_login_at ?? null,
            'status' => Status::where('id', $this->status_id)->first()->status_name ?? null,
        ]);
    }

    /**
     * Add data for other users.
     */
    private function addOtherUserData(array &$data): void
    {
        $data = array_merge($data, []);
    }
}
