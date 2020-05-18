<?php
namespace App\Transformers;

use App\Helpers\PaginateHelper;

class WorkOrderHistoryTransformer
{
    public function show($resource, $data)
    {
        return [
            'object' => $resource,
            'id' => $data->id,
            'work_order_id' => $data->work_order_id,
            'start_date' => \DatetimeHelper::TransformToTimeStamp($data->end_date),
            'end_date' => \DatetimeHelper::TransformToTimeStamp($data->end_date),
            'work_report' => $data->work_report,
            'photo1_guid' => $data->photo1_guid,
            'photo1_name' => $data->photo1_name,
            'photo2_guid' => $data->photo2_guid,
            'photo2_name' => $data->photo2_name,
            'photo3_guid' => $data->photo3_guid,
            'photo3_name' => $data->photo3_name,
            'video_guid' => $data->video_guid,
            'video_name' => $data->video_name,
            'approval_file_guid' => $data->approval_file_guid,
            'approval_file_name' => $data->approval_file_name,
            'created_by_user' => $data->created_by_user,
            'created_at' => \DatetimeHelper::TransformToTimeStamp($data->created_at),
            'updated_by_user' => $data->updated_by_user,
            'updated_at' => \DatetimeHelper::TransformToTimeStamp($data->updated_at),
            'work_order_status_id' => $data->work_order_status_id,
            'work_order_status_name' => $data->status_name,
        ];
    }

}
