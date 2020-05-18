<?php
namespace App\Services;

use App\Helpers\Base64Helper;
use App\Helpers\FileHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrderQuote;

class WorkOrderQuoteFileService
{
    private $workOrderQuoteService;
    private $pathFiles;
    private $utilService;

    public function __construct(WorkOrderQuoteService $workOrderQuoteService, UtilService $utilService)
    {
        $this->pathFiles = \Config::get('app.path_woq_files');
        $this->workOrderQuoteService = $workOrderQuoteService;
        $this->utilService = $utilService;
    }

    public function getByWorkOrderQuoteAndGuid($id, $guid)
    {
        return WorkOrderQuote::where('id', $id)
            ->where('quote_file_guid', $guid)
            ->first();
    }

    public function postProcess($save, WorkOrderQuote $data, $user)
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
        $this->utilService->deleteGuidFile($folderPath, $data->quote_file_guid, $data->quote_file_name);

        //---------------------------------------------------
        // Save data
        //---------------------------------------------------
        $params = ['name' => $filename, 'guid' => $guid];
        return $this->updateFileFields($data, $user, $params);
    }

    public function delete(WorkOrderQuote $data, $user) : void
    {
        $folderPath = $this->pathFiles . $data->id . '/';
        $this->utilService->deleteGuidFile($folderPath, $data->quote_file_guid, $data->quote_file_name);

        //---------------------------------------------------
        // Save data
        //---------------------------------------------------
        $params = ['name' => null, 'guid' => null];
        $this->updateFileFields($data, $user, $params);
    }


    // ======================================================================================
    // ========================== private functions =========================================
    // ======================================================================================

    private function updateFileFields(WorkOrderQuote $data, $user, $params)
    {
        $data->quote_file_name = $params['name'];
        $data->quote_file_guid = $params['guid'];
        $data->updated_by_user = User::GetCreatedByUser($user);
        $data->updated_at = now();
        $data->save();

        return $data;
    }

}
