<?php


namespace App\Helpers;


class FileHelper
{
    public static function GetExtensionFromFilename($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    public static function moveTmpFileToPathWithGuidAsName($file, $path, $guid): void
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

    public static function getFileNamePathByGuid($modulePath, $id, $guid, $name)
    {
        $extension = FileHelper::GetExtensionFromFilename($name);
        $filename = $guid . '.' . $extension;

        return $modulePath . $id . '/' . $filename;
    }

    public static function copyFileByGuid(array $fromData, array $toData)
    {
        $source = self::getFileNamePathByGuid($fromData['module_path'], $fromData['id'], $fromData['guid'], $fromData['name']);
        $destination = self::getFileNamePathByGuid($toData['module_path'], $toData['id'], $toData['guid'], $toData['name']);
        copy($source, $destination);
    }
}
