<?php
namespace App\Services;

use App\Helpers\FileHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderPhoto;

class WorkOrderVideoService
{
    private $pathFiles;
    private $utilService;

    public function __construct(UtilService $utilService)
    {
        $this->pathFiles = \Config::get('app.path_wo_files');
        $this->utilService = $utilService;
    }

    public function getByWorkOrderAndGuid($workOrderId, $guid)
    {
        return WorkOrder::where('id', $workOrderId)
            ->where('video_guid', $guid)
            ->first();
    }

    public function postProcess($save, WorkOrder $data, $user)
    {
        //---------------------------------------------------
        // Save File
        //---------------------------------------------------
        $file = $save['file'];
        $filename = $file->getClientOriginalName();
        $folderPath = $this->pathFiles . $data->id. '/';
        $guid = \FunctionHelper::CreateGUID(16);
        \FunctionHelper::createFolder($folderPath);
        FileHelper::moveTmpFileToPathWithGuidAsName($file, $folderPath, $guid);

        // delete old file if exists
        $this->utilService->deleteGuidFile($folderPath, $data->video_guid, $data->video_name);

        //---------------------------------------------------
        // Save data
        //---------------------------------------------------
        $params = ['name' => $filename, 'guid' => $guid];
        return $this->updateVideoFields($data, $user, $params);
    }

    public function delete($workOrder, $user)
    {
        // delete file
        $folderPath = $this->pathFiles . $workOrder->id . '/';
        $this->utilService->deleteGuidFile($folderPath, $workOrder->video_guid, $workOrder->video_name);

        // update model
        $params = ['name' => null, 'guid' => null];
        $this->updateVideoFields($workOrder, $user, $params);
    }

    // ======================================================================================
    // ========================== private functions =========================================
    // ======================================================================================

    private function updateVideoFields(WorkOrder $data, $user, $params)
    {
        $data->video_name = $params['name'];
        $data->video_guid = $params['guid'];
        $data->updated_by_user = User::GetCreatedByUser($user);
        $data->updated_at = now();
        $data->save();

        return $data;
    }

}
