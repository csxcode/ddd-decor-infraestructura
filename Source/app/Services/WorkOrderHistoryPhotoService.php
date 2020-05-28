<?php
namespace App\Services;

use App\Helpers\Base64Helper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrderHistory;

class WorkOrderHistoryPhotoService
{
    private $pathFiles;
    private $photoNameField;
    private $photoGuidField;
    private $utilService;

    public function __construct(UtilService $utilService)
    {
        $this->pathFiles = \Config::get('app.path_woh_files');
        $this->photoNameField = 'photo{0}_name';
        $this->photoGuidField = 'photo{0}_guid';
        $this->utilService = $utilService;
    }

    public function getByWorkOrderHistoryAndGuid($id, $guid, &$photoName)
    {
        // get data
        $workOrderHistory = WorkOrderHistory::where('id', $id)
            ->where('photo1_guid', $guid)
            ->orWhere('photo2_guid', $guid)
            ->orWhere('photo3_guid', $guid)
            ->first();

        // get filename by guid
        $photoName = null;

        if($workOrderHistory){
            if($workOrderHistory->photo1_guid == $guid) {
                $photoName = $workOrderHistory->photo1_name;

            }else if($workOrderHistory->photo2_guid == $guid){
                $photoName = $workOrderHistory->photo2_name;

            }else if($workOrderHistory->photo3_guid == $guid){
                $photoName = $workOrderHistory->photo3_name;
            }
        }

        return $workOrderHistory;
    }

    public function postProcess($save, WorkOrderHistory $data, $user)
    {
        foreach ($save as $photo)
        {
            $base64 = $photo['base64'];
            $filename = $photo['name'];
            $order = $photo['order'];

            //---------------------------------------------------
            // Save File
            //---------------------------------------------------
            $folderPath = $this->pathFiles . $data->id. '/';
            $guid = \FunctionHelper::CreateGUID(16);
            \FunctionHelper::createFolder($folderPath);
            Base64Helper::saveBase64WithGuidAsName($folderPath, $filename, $base64, $guid);

            //---------------------------------------------------
            // Save data
            //---------------------------------------------------
            $params = ['name' => $filename, 'guid' => $guid, 'order' => $order];
            $this->updateFileFields($data, $user, $params);
        }

        return $data;
    }


    // ======================================================================================
    // ========================== private functions =========================================
    // ======================================================================================

    private function updateFileFields(WorkOrderHistory $data, $user, $params)
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
