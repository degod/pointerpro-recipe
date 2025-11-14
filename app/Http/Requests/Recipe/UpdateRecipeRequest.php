<?php

namespace App\Http\Requests\Recipe;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $recipe = $this->route('recipe');
        return $recipe && auth('sanctum')->id() === $recipe->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'cuisine_type' => 'sometimes|required|string|max:100',
            'ingredients' => 'sometimes|required|string',
            'steps' => 'sometimes|required|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
