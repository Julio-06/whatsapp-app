<?php

namespace App\Rules;

use App\Models\Contact;
use Illuminate\Contracts\Validation\Rule;

class InvalidEmailRule implements Rule
{
    private $email;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($email = null)
    {
        $this->email = $email;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Contact::where('user_id', auth()->user()->id)
                ->whereHas('user', fn($q) => 
                    $q->where('email', $value)
                        ->when($this->email, fn($q) => 
                            $q->where('email', '!=', $this->email)
                        )
                    
                )
                ->count() === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El correo ingresado, ya se encuentra registrado.';
    }
}
