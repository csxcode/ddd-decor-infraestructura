<?php
namespace App\Services;

use App\Enums\AccessTypeEnum;
use App\Enums\ActionEnum;
use App\Helpers\FileHelper;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrderFile;

class WorkOrderFileService
{
    private $pathFiles;
    private $workOrderQuoteService;
    private $workOrderQuoteFileService;
    private $workOrderSearch;
    private $utilService;

    public function __construct(WorkOrderQuoteService $workOrderQuoteService, WorkOrderQuoteFileService $workOrderQuoteFileService, WorkOrderSearch $workOrderSearch,
        UtilService $utilService)
    {
        $this->pathFiles = \Config::get('app.path_wo_files');
        $this->workOrderQuoteService = $workOrderQuoteService;
        $this->workOrderQuoteFileService = $workOrderQuoteFileService;
        $this->workOrderSearch = $workOrderSearch;
        $this->utilService = $utilService;
    }

    public function getByWorkOrderAndGuid($workOrderId, $guid)
    {
        return WorkOrderFile::where('work_order_id', $workOrderId)
            ->where('guid', $guid)
            ->first();
    }

    public function getFileOrderExists($workOrderID, $order, $guid = null)
    {
        $query = WorkOrderFile::where('work_order_id', $workOrderID)
            ->where('order', $order);

        if ($guid != null)
            $query->where('guid', '<>', $guid);

        return $query->first();
    }

    public function postProcess($save, $workOrderId)
    {
        $return = null;

        $file = $save['file'];
        $action = $save['action'];
        $guid = $save['guid'];
        $name = $file->getClientOriginalName();

        $folderPath = $this->pathFiles . $workOrderId . '/';

        $params = [
            'work_order_id' => $workOrderId,
            'guid' => $guid,
            'name' => $name,
        ];

        //---------------------------------------------------
        // Save data and file
        //---------------------------------------------------

        \FunctionHelper::createFolder($folderPath);

        if ($action == ActionEnum::CREATE) {

            // save model
            $return = $this->create($params);

            // save new file
            FileHelper::moveTmpFileToPathWithGuidAsName($file, $folderPath, $guid);

        } elseif ($action == ActionEnum::EDIT) {

            // get model
            $workOrderFile = $this->getByWorkOrderAndGuid($workOrderId, $guid);

            // delete old file
            $this->utilService->deleteGuidFile($folderPath, $workOrderFile->guid, $workOrderFile->name);

            // save model
            $workOrderFile->name = $name;
            $workOrderFile->update();

            // save new file
            FileHelper::moveTmpFileToPathWithGuidAsName($file, $folderPath, $guid);

            $return = $workOrderFile;
        }

        return $return;
    }

    public function delete($workOrderFile)
    {
        // delete file
        $folderPath = $this->pathFiles . $workOrderFile->work_order_id . '/';
        $this->utilService->deleteGuidFile($folderPath, $workOrderFile->guid, $workOrderFile->name);

        // delete model
        $workOrderFile->forceDelete();
    }

    public function create($params)
    {
        return WorkOrderFile::create($params);
    }

    public function search($accessType, $filterParams, $sortByParams = null)
    {
        // -------------------------------------
        // Set Columns
        // -------------------------------------
        $columns = null;
        $query = null;

        if($accessType == AccessTypeEnum::Api) {

            $columns = '
                work_order_file.id,
                work_order_file.name,
                work_order_file.guid
            ';

            $columns = ltrim(rtrim($columns));
            $query = WorkOrderFile::select(\DB::raw($columns));

        } else if($accessType == AccessTypeEnum::Web) {

        }

        $query->join('v_work_order_search', 'work_order_file.work_order_id', 'v_work_order_search.id');


        // -------------------------------------
        // Set Filters
        // -------------------------------------
        $this->setFilterForSearch($filterParams, $accessType, $query);


        // -------------------------------------
        // Set Paginate
        // -------------------------------------
        $page = null;
        $per_page = null;

        if ($accessType == AccessTypeEnum::Api) {
            $page = $filterParams['page'];
            $per_page = $filterParams['per_page'];
        }

        PaginateHelper::SetPaginateDefaultValues($page, $per_page);

        // -------------------------------------
        // Set OrderBy
        // -------------------------------------
        $direction = !StringHelper::IsNullOrEmptyString($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = PaginateHelper::getEquivalenceSort($sortByParams['sort'], $this->columnsEquivalenceToSearch(), 'id');
        $query->orderBy($sort, $direction);


        // -------------------------------------
        // Return Data
        // -------------------------------------
        return $query->paginate($per_page);
    }

    // =====================================================================
    // ========================= Private ===================================
    // =====================================================================
    private function columnsEquivalenceToSearch(){
        return array(
            [
                'name' => 'id',
                'equilavence' => 'work_order_file.id'
            ]
        );
    }

    private function setFilterForSearch($filterParams, $accessType, &$query)
    {
        if (isset($filterParams['work_order_id']) && !StringHelper::IsNullOrEmptyString($filterParams['work_order_id'])) {
            $query->where('work_order_id', $filterParams['work_order_id']);
        }

        $this->workOrderSearch->scopeFilterByRole($query, $filterParams['user']);
    }
}
