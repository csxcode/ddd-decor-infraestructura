<?php

namespace App\Transformers;

use Carbon\Carbon;

class TicketTransformer
{
    public function show($resource, $data)
    {
        return [
            'object' => $resource,
            'id' => $data->id,
            'ticket_number' => $data->ticket_number,
            'status_id' => $data->status_id,
            'status_name' => $data->status_name,
            'status_reason' => $data->status_reason,
            'type_id' => $data->type_id,
            'type_name' => $data->type_name,
            'branch_location_id' => $data->branch_location_id,
            'branch_location_address' => $data->branch_location_address,
            'branch_location_name' => $data->branch_location_name,
            'branch_id' => $data->branch_id,
            'branch_name' => $data->branch_name,
            'store_id' => $data->store_id,
            'store_name' => $data->store_name,
            'description' => $data->description,
            'photos' => $data->photos,
            'video_name' => $data->video_name,
            'video_guid' => $data->video_guid,
            'delivery_date' => (null == $data->delivery_date ? null : Carbon::parse($data->delivery_date)->timestamp),
            'subtype_id' => $data->subtype_id,
            'subtype_name' => $data->subtype_name,
            'priority_id' => $data->priority_id,
            'priority_name' => $data->priority_name,
            'location' => $data->location,
            'reference_doc' => $data->reference_doc,
            'created_by_user' => $data->created_by_user,
            'created_at' => Carbon::parse($data->created_at)->timestamp,
            'updated_by_user' => $data->updated_by_user,
            'updated_at' => (null == $data->updated_at ? null : Carbon::parse($data->updated_at)->timestamp),
            'approved_by_user' => $data->approved_by_user,
            'approved_at' => (null == $data->approved_at ? null : Carbon::parse($data->approved_at)->timestamp),
            'rejected_by_user' => $data->rejected_by_user,
            'rejected_at' => (null == $data->rejected_at ? null : Carbon::parse($data->rejected_at)->timestamp),
        ];
    }
}
