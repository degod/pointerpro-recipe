<?php

namespace App\Http\Requests\Recipe;

use App\Enums\Visibility;
use App\Services\ResponseService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StoreRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'cuisine_type' => 'required|string|max:100',
            'ingredients' => 'required|string',
            'steps' => 'required|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'visibility' => 'required|in:' . implode(",", [Visibility::PRIVATE->value, Visibility::PUBLIC->value]),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = new ResponseService();

        throw new HttpResponseException(
            $response->error(
                422,
                'Validation failed',
                $validator->errors()->toArray()
            )
        );
    }
}
