<?php
namespace App\Transformers;

use App\Helpers\PaginateHelper;

class WorkOrderQuoteTransformer
{
    public function index($objectName, $paginateName, $data)
    {
        $itemsTransformed = $data
            ->getCollection()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'status_id' => $item->status->id,
                    'status_name' => $item->status->name,
                    'vendor' => self::vendor($item->vendor),
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

    public function show($resource, $data)
    {
        return [
            'object' => $resource,
            'id' => $data->id,
            'work_order_id' => $data->work_order_id,
            'vendor' => self::vendor($data->vendor),
            'status_id' => $data->status->id,
            'status_name' => $data->status->name,
            'quote_file_guid' => $data->quote_file_guid,
            'quote_file_name' => $data->quote_file_name,
            'amount' => $data->amount,
            'currency' => $data->currency,
            'time_days' => $data->time_days,
            'time_hours' => $data->time_hours,
            'payment_type' => $data->payment_type,
            'work_terms' => $data->work_terms,
            'notes' => $data->notes,
            'photo1_guid' => $data->photo1_guid,
            'photo1_name' => $data->photo1_name,
            'photo2_guid' => $data->photo2_guid,
            'photo2_name' => $data->photo2_name,
            'photo3_guid' => $data->photo3_guid,
            'photo3_name' => $data->photo3_name,
            'created_at' => \DatetimeHelper::TransformToTimeStamp($data->created_at),
            'created_by_user' => $data->created_by_user,
            'updated_at' => \DatetimeHelper::TransformToTimeStamp($data->updated_at),
            'updated_by_user' => $data->updated_by_user,
            'approved_at' => \DatetimeHelper::TransformToTimeStamp($data->approved_at),
            'approved_by_user' => $data->approved_by_user,
            'rejected_at' => \DatetimeHelper::TransformToTimeStamp($data->rejected_at),
            'rejected_by_user' => $data->rejected_by_user,
            'notification' => $data->notification,
        ];
    }

    private function vendor($data)
    {
        if($data)
            return [
                'id' => $data->id,
                'name' => $data->name,
                'phone' => $data->phone,
                'email' => $data->email,
                'contact_name' => $data->contact_name,
            ];

        return null;
    }
}
