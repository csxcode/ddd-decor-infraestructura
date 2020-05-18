<?php

namespace App\Validations;

use App\Enums\ActionFileEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\CurrencyEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\PaymentTypeEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\FunctionHelper;
use App\Helpers\Base64Helper;
use App\Helpers\StringHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;

class BaseValidation
{
    public function checkPagination($resource, $page, $perPage)
    {
        if (!StringHelper::IsNullOrEmptyString($perPage)) {
            if (!ctype_digit($perPage)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'per_page']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'per_page'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        if (!StringHelper::IsNullOrEmptyString($page)) {
            if (!ctype_digit($page)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'page']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'page'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

    }

    public function checkThereIsChangesAndSetToModel($modalFieldName, $oldField, $newField, &$modelToUpdate, &$changes)
    {
        if ($oldField != $newField) {
            $modelToUpdate->$modalFieldName = $newField;
            $changes[$modalFieldName] = $newField;
        }
    }

    public function checkExcludedFields($resource, $requestAll, $excludedFields)
    {
        foreach($requestAll as $key => $value)
        {
            $key = StringHelper::Trim($key);

            if(in_array($key, $excludedFields))
            {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.key_not_allowed', ['attribute' => $key]),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => $key
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    public function checkUser($resource, $userID)
    {
        if (StringHelper::IsNullOrEmptyString($userID)) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'user_id']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'user_id'
                ]
            ], Response::HTTP_BAD_REQUEST);

        } else {

            if (!ctype_digit($userID)) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'user_id']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'user_id',
                    ]
                ], Response::HTTP_BAD_REQUEST);

            }


            $user = User::find($userID);

            if(!$user){

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'usuario']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'user'
                    ]
                ], Response::HTTP_NOT_FOUND);

            }


        }

    }

    public function checkDecimal($resource, $value, $field, $numberOfDecimals)
    {
        if (StringHelper::IsNullOrEmptyString($value)) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);

        } else {


            $validate = FunctionHelper::validateDecimals($value, $numberOfDecimals);

            if(!$validate)
            {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => $field,
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

        }

        return null;
    }

    public function checkCurrency($resource, $value, $field)
    {
        if (StringHelper::IsNullOrEmptyString($value)) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);

        } else {

            if (!ctype_digit($value)) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => $field,
                    ]
                ], Response::HTTP_BAD_REQUEST);

            }

            $validate = $value == CurrencyEnum::Soles || $value == CurrencyEnum::Dolares;

            if(!$validate)
            {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => $field,
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

        }

        return null;

    }

    public function checkIsInteger($resource, $value, $field)
    {
        if (StringHelper::IsNullOrEmptyString($value)) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);

        } else {

            if (!ctype_digit($value)) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => $field,
                    ]
                ], Response::HTTP_BAD_REQUEST);

            }

        }

        return null;

    }

    public function checkPaymentType($resource, $value, $field)
    {
        $error_response = $this->checkIsInteger($resource, $value, $field);

        if($error_response)
            return $error_response;

        $validate = $value == PaymentTypeEnum::Factura || $value == PaymentTypeEnum::ReciboPorHonorario;

        if(!$validate)
        {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field,
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkLen($resource, $value, $field, $lenAllow)
    {
        $value = StringHelper::Trim($value);
        $valueLen = strlen($value);

        if($valueLen > $lenAllow){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.limit', ['attribute' => 'caracteres']),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkValueMustBeRequired($resource, $value, $field)
    {
        if (StringHelper::IsNullOrEmptyString($value)) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkValueMustBeRequiredWithCustomMessage($resource, $value, $field, $message)
    {
        if (StringHelper::IsNullOrEmptyString($value)) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => $message,
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkBase64StringIsValid($resource, $value, $field)
    {
        if (!Base64Helper::CheckBase64StringIsValid($value)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field . ' no es un string de base64 o']),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkBase64StringIsImageValid($resource, $value, $field)
    {
        if (!Base64Helper::checkBase64StringIsImageValid($value)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field . ' no es una imagen (base64) o']),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkFileIsValidImage($resource, $image, $field)
    {
        if (!FileHelper::checkImageIsValid($image)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field . ' no es una imagen o']),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkArrayLength($resource, $array, $field, $message)
    {
        if($array == null){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => $message,
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        if (count($array) == 0) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => $message,
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkOrderNumberIsValid($resource, $value, $field)
    {
        // Check if the order field is an integer
        if (!ctype_digit($value)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkOrderNumberIsValidByPhoto($resource, $value, $field, $photoName)
    {
        // Check if the order field is an integer
        if (!ctype_digit($value)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field,
                    'photo_name' => $photoName
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkValueMustBeRequiredByPhoto($resource, $value, $field, $photoName)
    {
        if (StringHelper::IsNullOrEmptyString($value)) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field,
                    'photo_name' => $photoName
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkBase64StringIsImageValidByPhoto($resource, $value, $field, $photoName)
    {
        if (!Base64Helper::checkBase64StringIsImageValid($value)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field . ' no es una imagen (base64) o']),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field,
                    'photo_name' => $photoName
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkActionFileIsValidByPhoto($resource, $value, $field, $photoName)
    {
        $error_response = $this->checkOrderNumberIsValid($resource, $value, $field);

        if ($error_response)
            return $error_response;

        // check action file id
        $pass = false;

        if( $value == ActionFileEnum::CREATE ||
            $value == ActionFileEnum::EDIT ||
            $value == ActionFileEnum::KEEP ||
            $value == ActionFileEnum::DELETE)
            $pass  = true;

        if (!$pass) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field,
                    'photo_name' => $photoName
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkDuplicatesArrayByField($resource, $data, $field, $fieldNameError, $message)
    {
        $tempArr = array_unique(array_column($data, $field));

        if (count($data) != count($tempArr)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => $message,
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $fieldNameError
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkValueHasData($resource, $value, $field)
    {
        if (!$value) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkDateTimestampIsValid($resource, $value, $field)
    {
        try {
            $date = Carbon::createFromTimestamp($value);

            if(strval(\DatetimeHelper::TransformToTimeStamp($date)) !== strval($value))
                throw new \Exception('date is not valid');

        } catch (\Exception $exp) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkVideoExtensionIsAllowed($resource, $file, $field)
    {
        $videoExtensions = ['mp4', 'mpeg'];

        if (!in_array(strtolower($file->extension()), $videoExtensions)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.video_ext_not_allowed', ['attribute' => implode(', ', $videoExtensions)]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
