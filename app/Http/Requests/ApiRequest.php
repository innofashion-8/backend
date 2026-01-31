<?php

namespace App\Http\Requests;

use App\Utils\HttpResponse;
use App\Utils\HttpResponseCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiRequest extends FormRequest
{
    use HttpResponse;
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->error(
            $validator->errors()->first(), 
            HttpResponseCode::HTTP_UNPROCESSABLE_ENTITY,
            $validator->errors()
        ));
    }
}