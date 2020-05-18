<?php

namespace App\Transformers;


class TicketCommentTransformer
{
    public function show($data)
    {        
        // Get types
        $return = collect($data)->map(function($data){   

            return [
                'id' => $data['id'],
                'description' => $data['description'],
                'created_at' => $data['created_at'],
                'created_by_user' => $data['created_by_user'],
            ];
        });              

        return $return;
    }

}