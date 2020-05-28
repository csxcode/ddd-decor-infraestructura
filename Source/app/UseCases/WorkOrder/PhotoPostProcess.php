<?php

namespace App\UseCases\WorkOrder;

use App\Enums\ActionFileEnum;
use App\Helpers\Base64Helper;
use App\Models\WorkOrder\WorkOrderPhoto;
use App\Services\UtilService;
use App\Services\WorkOrderPhotoService;

class PhotoPostProcess
{
    private $pathFiles;
    private $utilService;
    private $workOrderPhotoService;

    public function __construct()
    {
        $this->pathFiles = \Config::get('app.path_wo_files');
        $this->utilService = new UtilService();
        $this->workOrderPhotoService = new WorkOrderPhotoService();
    }


    public function execute($save, $workOrderId): void
    {
        $folderPath = $this->pathFiles . $workOrderId . '/';

        foreach ($save as $photo) {
            $action = $photo['action'];
            $guid = $photo['guid'];
            $name = $photo['name'];
            $order = $photo['order'];
            $base64 = $photo['base64'];

            $path = $this->pathFiles . $workOrderId . '/';

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
                WorkOrderPhoto::create($params);

                // save new photo
                Base64Helper::saveBase64WithGuidAsName($path, $name, $base64, $guid);
            } elseif ($action == ActionFileEnum::EDIT) {

                // get model
                $workOrderPhoto = $this->workOrderPhotoService->getByWorkOrderAndGuid($workOrderId, $guid);

                // delete old photo
                $this->utilService->deleteGuidFile($folderPath, $workOrderPhoto->guid, $workOrderPhoto->name);

                // save model
                $workOrderPhoto->name = $name;
                $workOrderPhoto->order = $order;
                $workOrderPhoto->update();

                // save new photo
                Base64Helper::saveBase64WithGuidAsName($path, $name, $base64, $guid);
            } elseif ($action == ActionFileEnum::DELETE) {

                // get model
                $workOrderPhoto = $this->workOrderPhotoService->getByWorkOrderAndGuid($workOrderId, $guid);

                // delete photo file
                $this->utilService->deleteGuidFile($folderPath, $workOrderPhoto->guid, $workOrderPhoto->name);

                // delete model
                $workOrderPhoto->forceDelete();
            }
        }
    }
}
