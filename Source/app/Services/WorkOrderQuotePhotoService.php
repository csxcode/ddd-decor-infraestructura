<?php


namespace App\Services;


use App\Helpers\Base64Helper;
use App\Helpers\FileHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrderQuote;

class WorkOrderQuotePhotoService
{
    private $pathFiles;
    private $photoNameField;
    private $photoGuidField;
    private $utilService;

    public function __construct(UtilService $utilService)
    {
        $this->pathFiles = \Config::get('app.path_woq_files');
        $this->photoNameField = 'photo{0}_name';
        $this->photoGuidField = 'photo{0}_guid';
        $this->utilService = $utilService;
    }

    public function getByWorkOrderQuoteAndGuid($id, $guid, &$photoName)
    {
        // get data
        $workOrderQuote = WorkOrderQuote::where('id', $id)
            ->where('photo1_guid', $guid)
            ->orWhere('photo2_guid', $guid)
            ->orWhere('photo3_guid', $guid)
            ->first();

        // get filename by guid
        $photoName = null;

        if($workOrderQuote){
            if($workOrderQuote->photo1_guid == $guid) {
                $photoName = $workOrderQuote->photo1_name;

            }else if($workOrderQuote->photo2_guid == $guid){
                $photoName = $workOrderQuote->photo2_name;

            }else if($workOrderQuote->photo3_guid == $guid){
                $photoName = $workOrderQuote->photo3_name;
            }
        }

        return $workOrderQuote;
    }

    public function postProcess($save, WorkOrderQuote $data, $user)
    {
        foreach ($save as $photo)
        {
            $base64 = $photo['base64'];
            $filename = $photo['name'];
            $order = $photo['order'];

            $photoNameField = str_replace('{0}', $order, $this->photoNameField);
            $photoGuidField = str_replace('{0}', $order, $this->photoGuidField);

            //---------------------------------------------------
            // Save File
            //---------------------------------------------------
            $folderPath = $this->pathFiles . $data->id. '/';
            $guid = \FunctionHelper::CreateGUID(16);
            \FunctionHelper::createFolder($folderPath);
            Base64Helper::saveBase64WithGuidAsName($folderPath, $filename, $base64, $guid);

            //---------------------------------------------------
            // Delete old file if exists
            //---------------------------------------------------
            $this->utilService->deleteGuidFile($folderPath, $data->$photoGuidField, $data->$photoNameField);

            //---------------------------------------------------
            // Save data
            //---------------------------------------------------
            $params = ['name' => $filename, 'guid' => $guid, 'order' => $order];
            $this->updateFileFields($data, $user, $params);
        }


        return $data;
    }

    public function delete(WorkOrderQuote $model, $user, $data) : void
    {
        $folderPath = $this->pathFiles . $model->id . '/';
        $this->utilService->deleteGuidFile($folderPath, $data['guid'], $data['name']);

        //---------------------------------------------------
        // Save data
        //---------------------------------------------------
        $params = ['name' => null, 'guid' => null, 'order' => $data['order']];
        $this->updateFileFields($model, $user, $params);
    }


    // ======================================================================================
    // ========================== private functions =========================================
    // ======================================================================================

    private function updateFileFields(WorkOrderQuote $data, $user, $params)
    {
        $photoNameField = str_replace('{0}', $params['order'], $this->photoNameField);
        $photoGuidField = str_replace('{0}', $params['order'], $this->photoGuidField);

        $data->$photoNameField = $params['name'];
        $data->$photoGuidField = $params['guid'];
        $data->updated_by_user = User::GetCreatedByUser($user);
        $data->updated_at = now();
        $data->save();

        return $data;
    }
}
