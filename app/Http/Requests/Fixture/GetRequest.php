<?php

namespace App\Http\Requests\Fixture;

use Illuminate\Foundation\Http\FormRequest;

class GetRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'week' => 'required|int'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
