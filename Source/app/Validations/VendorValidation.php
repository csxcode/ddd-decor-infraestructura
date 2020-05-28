<?php
namespace App\Validations;

use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Response;

class VendorValidation
{
    public function index($request, $resource, &$params)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $vendor_status = StringHelper::Trim($request->get('vendor_status'));

        $params = [
            'user' => $user,
            'vendor_status' => $vendor_status,
        ];


        // ------------ Return ----------------
        //--------------------------------------
        return null;
    }

    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------

     public function checkVendor($resource, $id, $field)
     {
         if (!ctype_digit($id)) {
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


         $data = Vendor::find($id);
         if(!$data){
             return response()->json([
                 'object' => AppObjectNameEnum::ERROR,
                 'message' => trans('api/validation.not_exists', ['attribute' => $field]),
                 'error_code' => ErrorCodesEnum::not_exists,
                 'error_data' => [
                     'resource' => $resource,
                     'field' => $field
                 ]
             ], Response::HTTP_NOT_FOUND);
         }

         return null;
     }
}
