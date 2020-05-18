<?php

namespace App\Transformers;

use App\Helpers\DatetimeHelper;
use App\Helpers\PaginateHelper;

class WorkOrderTransformer
{
    public function show($resource, $workOrder)
    {
        return [
            'object' => $resource,
            'id' => $workOrder->id,
            'wo_number' => $workOrder->wo_number,
            'required_days' => $workOrder->required_days,
            'work_specs' => $workOrder->work_specs,
            'branch_location_id' => $workOrder->branch_location_id,
            'branch_location_address' => $workOrder->branch_location_address,
            'branch_location_name' => $workOrder->branch_location_name,
            'branch_id' => $workOrder->branch_id,
            'branch_name' => $workOrder->branch_name,
            'major_account_id' => $workOrder->major_account_id,
            'major_account_code' => $workOrder->major_account_code,
            'major_account_name' => $workOrder->major_account_name,
            'sap_description' => $workOrder->sap_description,
            'video_guid' => $workOrder->video_guid,
            'video_name' => $workOrder->video_name,
            'status_id' => $workOrder->work_order_status_id,
            'status_name' => $workOrder->work_order_status_name,
            'start_date' => DatetimeHelper::TransformToTimeStamp($workOrder->start_date),
            'end_date' => DatetimeHelper::TransformToTimeStamp($workOrder->end_date),
            'created_at' => DatetimeHelper::TransformToTimeStamp($workOrder->created_at),
            'created_by_user' => $workOrder->created_by_user,
            'updated_at' => DatetimeHelper::TransformToTimeStamp($workOrder->updated_at),
            'updated_by_user' => $workOrder->updated_by_user,
            'ticket_id' => $workOrder->ticket_id,
            'maintenance_id' => $workOrder->maintenance_id,
        ];
    }

    public function index($objectName, $paginateName, $data)
    {
        $itemsTransformed = $data
            ->getCollection()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'wo_number' => $item->wo_number,
                    'created_at' => $item->created_at,
                    'branch_id' => $item->branch_id,
                    'branch_name' => $item->branch_name,
                    'contacts' => self::contacts($item->wo_contacts),
                    'status_id' => $item->work_order_status_id,
                    'status_name' => $item->work_order_status_name,
                    'ticket_id' => $item->ticket_id,
                    'ticket_number' => $item->ticket_number,
                    'maintenance_id' => $item->maintenance_id
                ];
        })->toArray();

        $itemsTransformedAndPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $itemsTransformed,
            $data->total(),
            $data->perPage(),
            $data->currentPage(), [
                'query' => [
                    'page' => $data->currentPage()
                ]
            ]
        );

        return PaginateHelper::TransformPaginateData($objectName, $paginateName, $itemsTransformedAndPaginated);
    }

    private function contacts($data)
    {
        return $data->map(function ($contact) {
            return [
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'phone' => $contact->phone,
            ];
        });
    }

}
