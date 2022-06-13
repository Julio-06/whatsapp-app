<?php

namespace App\Http\Requests;

use App\Rules\InvalidEmailRule;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'email' => [
                'required', 
                'email', 'exists:users', 
                Rule::notIn(auth()->user()->email),
                new InvalidEmailRule($this->contact->user->email)
            ]
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es requerido.',
            'email.required' => 'El correo es requerido.',
            'email.email' => 'No es un correo valido.',
            'email.exists' => 'El correo debe estar registrado en la aplicación.',
            'email.not_in' => 'No te puedes registrar como contacto.'

        ];
    }
}
