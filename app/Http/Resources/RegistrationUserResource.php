<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class RegistrationUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => 'user',
            'attributes' => [
                'full_name' => $this->name,
                'email' => $this->email,
                'username' => $this->username,
                'affiliation' => DB::table('user_profiles')->where('user_id', $this->id)->value('affiliation') ?? '',
                'status' => $this->status,
                'email_verified' => $this->email_verified_at !== null,
                'version' => $this->version,
            ],
        ];
    }
}
