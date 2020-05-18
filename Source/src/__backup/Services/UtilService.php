<?php


namespace App\Services;


use App\Helpers\FileHelper;

class UtilService
{
    public function deleteGuidFile($folderPath, $guid, $filename) : void
    {
        try {
            $filename = $guid . '.' . FileHelper::GetExtensionFromFilename($filename);
            $filePathToDelete = $folderPath . $filename;

            \FunctionHelper::deleteFile($filePathToDelete);
        } catch (\Exception $e){
            \Log::error($e);
        }
    }

}
