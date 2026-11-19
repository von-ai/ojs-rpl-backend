<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicationPermissionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'confirmed' => ['required', 'accepted'],
            'license' => ['required', 'string', 'max:191'],
            'agreement_reference' => $this->routeIs('eic.*') ? ['required', 'string', 'max:255'] : ['prohibited'],
        ];
    }
}
