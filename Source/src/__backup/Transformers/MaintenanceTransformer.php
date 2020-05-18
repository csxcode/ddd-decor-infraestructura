<?php

namespace App\Transformers;

use App\Helpers\PaginateHelper;

class MaintenanceTransformer
{
    public function show($resource, $data)
    {
        return [
            'object' => $resource,
            'id' => $data->id,
            'number' => $data->maintenance_number,
            'title' => $data->maintenance_title,
            'date' => \DatetimeHelper::TransformToTimeStamp($data->maintenance_date),
            'description' => $data->description,
            'reminder1' => \DatetimeHelper::TransformToTimeStamp($data->reminder1),
            'reminder2' => \DatetimeHelper::TransformToTimeStamp($data->reminder2),
            'created_at' => \DatetimeHelper::TransformToTimeStamp($data->created_at),
            'created_by_user' => $data->created_by_user,
            'updated_at' => \DatetimeHelper::TransformToTimeStamp($data->updated_at),
            'updated_by_user' => $data->updated_by_user,
            'status_id' => $data->status_id,
            'status_name' => $data->status_name,
            'work_order_id' => $data->work_order_id,
            'wo_number' => $data->wo_number,
            'branch_location_name' => $data->branch_location_name,
            'branch_name' => $data->branch_name,
        ];
    }

    public function index($objectName, $paginateName, $data)
    {
        $itemsTransformed = $data
            ->getCollection()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'status_id' => $item->status_id,
                    'status_name' => $item->status_name,
                    'branch_id' => $item->branch_id,
                    'branch_name' => $item->branch_name,
                    'branch_location_name' => $item->branch_location_name,
                    'reminder1' => \DatetimeHelper::TransformToTimeStamp($item->reminder1),
                    'title' => $item->maintenance_title,
                    'number' => $item->maintenance_number,
                    'date' => \DatetimeHelper::TransformToTimeStamp($item->maintenance_date),
                    'work_order_id' => $item->work_order_id,
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
}
