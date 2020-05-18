<?php


namespace App\Services;


use App\Enums\AccessTypeEnum;
use App\Enums\ActionEnum;
use App\Enums\ActionFileEnum;
use App\Helpers\Base64Helper;
use App\Helpers\FileHelper;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrderPhoto;

class WorkOrderPhotoService
{
    private $pathFiles;
    private $workOrderQuoteService;
    private $workOrderQuoteFileService;
    private $workOrderSearch;
    private $utilService;

    public function __construct(WorkOrderQuoteService $workOrderQuoteService, WorkOrderQuoteFileService $workOrderQuoteFileService, WorkOrderSearch $workOrderSearch,
        UtilService $utilService)
    {
        $this->pathFiles = \Config::get('app.path_wo_files') ;
        $this->workOrderQuoteService = $workOrderQuoteService;
        $this->workOrderQuoteFileService = $workOrderQuoteFileService;
        $this->workOrderSearch = $workOrderSearch;
        $this->utilService = $utilService;
    }

    public function getByWorkOrderAndGuid($workOrderId, $guid)
    {
        return WorkOrderPhoto::where('work_order_id', $workOrderId)
            ->where('guid', $guid)
            ->first();
    }

    public function postProcess($save, $workOrderId) : void
    {
        $folderPath = $this->pathFiles . $workOrderId . '/';

        foreach ($save as $photo)
        {
            $action = $photo['action'];
            $guid = $photo['guid'];
            $name = $photo['name'];
            $order = $photo['order'];
            $base64 = $photo['base64'];

            $path = \Config::get('app.path_wo_files') . $workOrderId . '/';

            $params = [
                'work_order_id' => $workOrderId,
                'guid' => $guid,
                'name' => $name,
                'order' => $order,
            ];

            //---------------------------------------------------
            // Save data and photo
            //---------------------------------------------------

            \FunctionHelper::createFolder($path);

            if ($action == ActionFileEnum::CREATE) {

                // save model
                $this->create($params);

                // save new photo
                Base64Helper::saveBase64WithGuidAsName($path, $name, $base64, $guid);

            } elseif ($action == ActionFileEnum::EDIT) {

                // get model
                $workOrderPhoto = $this->getByWorkOrderAndGuid($workOrderId, $guid);

                // delete old photo
                $this->utilService->deleteGuidFile($folderPath, $workOrderPhoto->guid , $workOrderPhoto->name);

                // save model
                $workOrderPhoto->name = $name;
                $workOrderPhoto->order = $order;
                $workOrderPhoto->update();

                // save new photo
                Base64Helper::saveBase64WithGuidAsName($path, $name, $base64, $guid);

            } elseif ($action == ActionFileEnum::DELETE) {

                // get model
                $workOrderPhoto = $this->getByWorkOrderAndGuid($workOrderId, $guid);

                // delete photo file
                $this->utilService->deleteGuidFile($folderPath, $workOrderPhoto->guid , $workOrderPhoto->name);

                // delete model
                $workOrderPhoto->forceDelete();

            }
        }
    }

    public function create($params)
    {
        return WorkOrderPhoto::create($params);
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
                work_order_photo.id,
                work_order_photo.name,
                work_order_photo.guid,
                work_order_photo.order
            ';

            $columns = ltrim(rtrim($columns));
            $query = WorkOrderPhoto::select(\DB::raw($columns));

        } else if($accessType == AccessTypeEnum::Web) {

        }

        $query->join('v_work_order_search', 'work_order_photo.work_order_id', 'v_work_order_search.id');


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

    public function getPhotoOrderExists($workOrderID, $order, $guid = null)
    {
        $query = WorkOrderPhoto::where('work_order_id', $workOrderID)
            ->where('order', $order);

        if ($guid != null)
            $query->where('guid', '<>', $guid);

        return $query->first();
    }

    // =====================================================================
    // ========================= Private ===================================
    // =====================================================================
    private function columnsEquivalenceToSearch(){
        return array(
            [
                'name' => 'id',
                'equilavence' => 'work_order_photo.id'
            ],
            [
                'name' => 'order',
                'equilavence' => 'work_order_photo.order'
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
