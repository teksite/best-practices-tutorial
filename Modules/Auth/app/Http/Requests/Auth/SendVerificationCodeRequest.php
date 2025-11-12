<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Enums\VerificationUsernameType;
use Modules\Auth\Rules\UsernameTypeRule;
use Modules\Auth\Services\VerificationCodeService;
use function PHPUnit\TestFixture\func;

class SendVerificationCodeRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'usernameType' => VerificationUsernameType::detectType($this->get('username', ''))?->value,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $username = $this->input('username', '');
        $action = $this->input('action', '');

        return [
            'usernameType' => ['bail', 'required', new Enum(VerificationUsernameType::class)],
            'action' => ['bail', 'required', 'string', new Enum(VerificationActionType::class)],
            'username' => ['bail', 'required', 'string', new UsernameTypeRule(), ...$this->getActionTypeRules($username, $action)]
        ];
    }


    protected function getActionTypeRules(string $username, string $action): array
    {
        $action = VerificationActionType::detectType($this->input('action', ''));
        $usernameType = $this->input('usernameType');

        $column = VerificationUsernameType::getColumn($usernameType);

        return $action === VerificationActionType::Register
            ? [Rule::unique('users', $column)]
            : [Rule::exists('users', $column)];

    }


    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) return;

                $recipient = $this->input('username');
                $recipientType = $this->input('usernameType');
                $action = $this->input('action');

                $service = new VerificationCodeService();
                $waitTime = $service->waitTime($recipient, VerificationActionType::from($action));
                if ($waitTime > 0 ) {
                    $validator->errors()->add('action', trans('auth::validation.wait_time' ,['time'=>$waitTime."s"]));
                };

            },
        ];
    }

}
