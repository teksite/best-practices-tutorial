<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Auth\Rules\ContactCheckRule;
use Modules\Main\Foundation\BaseAuthRequest;

class CheckUserFormRequest extends BaseAuthRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'contact' => ['bail', 'required', 'string', new ContactCheckRule],
        ];
    }

    public function after(): array
    {
        return [
            fn(Validator $validator) => $this->appendContactData($validator),
        ];
    }


}
