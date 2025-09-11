<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCurrentResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        $meta = $this->whenLoaded( 'meta' );
        $roles = $this->whenLoaded( 'roles' );

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'first_name' => $meta->first_name ?? null,
            'last_name' => $meta->last_name ?? null,
            // 'discount' => +$meta?->extra['discount'],
            'roles' => $roles?->pluck( 'name' ) ?? null,
        ];
    }
}
