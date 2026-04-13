<?php

namespace Modules\Main\Foundation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiFormRequest extends FormRequest
{
    public function failedValidation(Validator $validator)
    {
        $exception = $validator->getException();
        $exc=(new $exception($validator))->errorBag($this->errorBag);

        return throw new HttpResponseException(response()->json([
            'message' => $exc->getMessage(),
            'errors' => $exc->errors(),
            'status' => 422,
            'data' => [],
        ])->setStatusCode(422));

    }


    public function failedAuthorization()
    {
        return throw new HttpResponseException(response()->json([
            'message' => 'This action is unauthorized.',
            'errors' => ['auth'=>"Forbidden You don't have permission"],
            'status' => 403,
            'data' => [],
        ])->setStatusCode(403));
    }
}
