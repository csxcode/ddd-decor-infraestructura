<?php namespace App\Helpers;

class Base64Helper
{


    public static function CheckBase64StringIsValid($data){
        $base64 = base64_decode($data, true);

        if (base64_encode($base64) === $data){

            return true;

        } else {
            return false;
        }
    }

    public static function saveBase64WithGuidAsName($path, $filename, $base64, $guid)
    {
        $extension = FileHelper::GetExtensionFromFilename($filename);
        $safeName = $guid . '.' . $extension;
        file_put_contents($path . $safeName, base64_decode($base64));
    }

    public static function checkBase64StringIsImageValid($data){
        $image = base64_decode($data, true);

        if (base64_encode($image) === $data){

            $data_image = getimagesizefromstring($image);
            //dd(image_type_to_extension($data_image[2]));

            if(!$data_image){
                return false;
            }

            return true;

        } else {
            return false;
        }
    }

}
