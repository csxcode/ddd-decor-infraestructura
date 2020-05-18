<?php
namespace App\Services;

use App\Helpers\FileHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrderHistory;

class WorkOrderHistoryFileService
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
            ->where('approval_file_guid', $guid)
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
        // Delete old file if exists
        //---------------------------------------------------
        $this->utilService->deleteGuidFile($folderPath, $data->approval_file_guid, $data->approval_file_name);

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
        $data->approval_file_name = $params['name'];
        $data->approval_file_guid = $params['guid'];
        $data->updated_by_user = User::GetCreatedByUser($user);
        $data->updated_at = now();
        $data->save();

        return $data;
    }

}
