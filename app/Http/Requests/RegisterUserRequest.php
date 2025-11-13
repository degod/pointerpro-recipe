<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Services\ResponseService;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed', // expects password_confirmation field
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = new ResponseService();

        throw new HttpResponseException(
            $response->error(
                422,
                'Validation failed',
                $validator->errors()
            )
        );
    }
}
