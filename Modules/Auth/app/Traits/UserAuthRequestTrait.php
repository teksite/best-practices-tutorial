<?php

namespace Modules\Auth\Traits;

use Illuminate\Validation\Validator;
use Modules\Auth\Actions\DetectContactType;
use Modules\Auth\Actions\NormalizeContact;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Rules\ContactCheckRule;
use Modules\Auth\Services\VerificationCodeService;
use Modules\User\Models\User;

trait UserAuthRequestTrait
{

    public ContactType|null $contactType = null;
    public VerificationActionType|null $actionType = null;
    public string|null $contactValue = null;


    /**
     * @param Validator $validator
     * @return void
     */
    protected function appendContactData(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $this->contactType = DetectContactType::handle($this->input('contact'));
        $this->contactValue = NormalizeContact::handle($this->input('contact'));
        $this->actionType = VerificationActionType::tryFrom($this->input('action'));

        if (is_null($this->contactType) || is_null($this->contactValue)) {
            $validator->errors()->add('overall', trans('auth::messages.auth.troubles'));
            return;
        }

    }

    /**
     * @param Validator $validator
     * @return void
     */
    protected function checkExistenceContactCondition(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $contactField = $this->contactType->value;   // email , phone
        $contact = $this->contactValue;              //example@ex.ir , +989121111111
        $action = $this->input('action');            // register , login ,....

        $isUserExist = User::query()->where($contactField, $contact)->exists(); //true , false

        if ($action === VerificationActionType::REGISTER->value && $isUserExist) {
            $validator->errors()->add($contactField, trans('auth::messages.auth.user_exist'));
            return;
        }
        if ($action === VerificationActionType::LOGIN->value && !$isUserExist) {
            $validator->errors()->add($contactField, trans('auth::messages.auth.user_not_found'));
            return;

        }
    }


    protected function appendAltContactData(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $contactType = $this->contactType;
        $contactValue = $this->contactValue;

        $contactAltType= $contactType === ContactType::PHONE ? ContactType::EMAIL : ContactType::PHONE;

        'contact' => ['bail', 'required', 'string', 'min:5', 'max:100', new ContactCheckRule],

        if (is_null($this->contactType) || is_null($this->contactValue)) {
            $validator->errors()->add('overall', trans('auth::messages.auth.troubles'));
            return;
        }

    }
}
