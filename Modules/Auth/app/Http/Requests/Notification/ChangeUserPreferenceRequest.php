<?php

namespace Modules\Auth\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Http\Requests\Base\BaseAuthRequest;
use Modules\Auth\Rules\UsernameTypeRule;
use Modules\Auth\Services\VerificationTokenService;
use function PHPUnit\TestFixture\func;

class ChangeUserPreferenceRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'type' => ['bail', 'required', 'string', Rule::in(array_keys(config('user.notifications.defaults', []))),],
            'channel' => ['bail', 'required', 'string', Rule::in(array_keys(config('user.notifications.channels', []))),],
            'value' => ['bail', 'required','boolean'],
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'value' => filter_var($this->input('value'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
        ]);
    }

}
