<?php

namespace Modules\Auth\Http\Requests\Afters;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\Validator;
use Modules\Auth\Actions\DetectContactType;
use Modules\Auth\Actions\NormalizeContact;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\User\Models\User;

trait AuthDataRequestTrait
{
    public User|Authenticatable|null $user =null;

    public VerificationActionType|null $actionType = null;
    public ContactType|null $contactType = null;

    public string|null $contactValue = null;
    public ContactType|null $contactAltType = null;

    public string|null $contactAltValue = null;


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

        $this->user = User::query()->firstWhere($this->contactType->value, $this->contactValue);

        if (is_null($this->contactType) || is_null($this->contactValue)) {
            $validator->errors()->add('overall', trans('auth::messages.auth.troubles'));
            return;
        }

    }

    /**
     * @param Validator $validator
     * @return void
     */
    protected function appendAltContactData(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        if (is_null($this->contactType) || is_null($this->contactValue)) {
            $validator->errors()->add('overall', trans('auth::messages.auth.troubles'));
            return;
        }

        $contactAltType= $this->contactType === ContactType::PHONE ? ContactType::EMAIL : ContactType::PHONE;

        $existenceUserByAltContact = User::query()->where($contactAltType->value,  $this->contactValue)->exists();
        if ($existenceUserByAltContact) {
            $validator->errors()->add($contactAltType->value, trans('auth::messages.auth.contact_is_used_before' , ['attribute' => $contactAltType->value]));
            return;
        }

        $this->contactAltType = $contactAltType;
        $this->contactAltValue = NormalizeContact::handle($this->input('contact_alt'));
        return;
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
            $validator->errors()->add($contactField, trans('auth::messages.auth.contact_is_used_before', ['attribute' => $contactField]));
            return;
        }

        if ($action === VerificationActionType::LOGIN->value && !$isUserExist) {
            $validator->errors()->add($contactField, trans('auth::messages.auth.user_not_found'));
            return;
        }
    }

    public function checkIfContactIsNull(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;
        $contactType = $this->contactType;
        $contactValue = $this->contactValue;

        if (auth('sanctum')->user()->verifiedContacts($contactType)) {
            $validator->errors()->add($contactType->value, trans('auth::messages.auth.contact_verified_before' , ['attribute' => $contactType->value]));
            return;
        }


    }

}
