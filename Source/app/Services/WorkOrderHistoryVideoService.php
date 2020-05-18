<?php
namespace App\Services;

use App\Helpers\FileHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrderHistory;

class WorkOrderHistoryVideoService
{
    private $pathFiles;
    private $utilService;

    public function __construct(UtilService $utilService)
    {
        $this->pathFiles = \Config::get('app.path_woh_files');
        $this->utilService = $utilService;
    }

    public function getByWorkOrderHistoryAndGuid($id, $guid)
    {
        return WorkOrderHistory::where('id', $id)
            ->where('video_guid', $guid)
            ->first();
    }

    public function postProcess($save, WorkOrderHistory $data, $user)
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

        //---------------------------------------------------
        // Save data
        //---------------------------------------------------
        $params = ['name' => $filename, 'guid' => $guid];
        return $this->updateFileFields($data, $user, $params);
    }

    // ======================================================================================
    // ========================== private functions =========================================
    // ======================================================================================

    private function updateFileFields(WorkOrderHistory $data, $user, $params)
    {
        $data->video_name = $params['name'];
        $data->video_guid = $params['guid'];
        $data->updated_by_user = User::GetCreatedByUser($user);
        $data->updated_at = now();
        $data->save();

        return $data;
    }

}
