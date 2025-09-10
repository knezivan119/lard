<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $rules = [
            'name' => [ 'required' ],
            'email' => [ 'sometimes', 'email' ],
            'meta' => [ 'sometimes', 'array' ],
            'meta.first_name' => [ 'sometimes' ],
            'meta.extra' => [ 'sometimes', 'array' ],
            'meta.phones' => [ 'sometimes', 'array' ],
            'meta.addresses' => [ 'sometimes', 'array' ],
        ];

        if ( $this->isMethod('POST') ) {
            $rules = [ ...$rules, ...[
                'password' => [ 'required' ],
            ]];
        }

        return $rules;
    }
}
