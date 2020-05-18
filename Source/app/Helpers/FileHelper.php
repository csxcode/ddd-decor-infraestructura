<?php


namespace App\Helpers;


class FileHelper
{
    public static function GetExtensionFromFilename($filename){
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    public static function moveTmpFileToPathWithGuidAsName($file, $path, $guid) : void
    {
        $filename = $file->getClientOriginalName();
        $extension = self::GetExtensionFromFilename($filename);

        $safeName = $guid . '.' . $extension;

        $file->move($path, $safeName);
    }

    public static function checkImageIsValid($img)
    {
        $data_image = getimagesize($img);

        if (!$data_image) {
            return false;
        }

        return true;
    }

}
