<?php

namespace App\Transformers;


class CheckListItemTypeTransformer
{
    public function all($data)
    {
        // Get types
        $return = collect($data)->map(function($type){

            // Get sub_types
            $sub_types = collect($type['sub_types'])->map(function ($sub_type){

                $items = [];

                // Only show items key when has data
                if(isset($sub_type['items'])) {

                    // Get items
                    $items = collect($sub_type['items'])->map(function ($item) {

                        // Array of items [Level 3]
                        return [
                            'item_id' => $item['id'],
                            'name' => $item['name'],
                            'description' => $item['description']
                        ];
                    });
                }else{

                    // items is not available to show
                    $items = null;
                }

                // Array of sub_types [Level 2]
                $ret_sub_type = [
                    'sub_type_id' => $sub_type['id'],
                    'name' => $sub_type['name']
                ];

                // add items only when has data
                if($items != null){
                    $ret_sub_type['items'] = $items;
                }

                return $ret_sub_type;
            });

            // Array of types [Level 1]
            return [
                'type_id' => $type['id'],
                'name' => $type['name'],
                'sub_types' => $sub_types
            ];
        });

        return $return;
    }

}