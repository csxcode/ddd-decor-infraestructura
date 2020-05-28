<?php

namespace App\Transformers;

class StoreTransformer
{
    public function index($objectName, $dataName, $data)
    {
        $itemsTransformed = $data
            ->map(function($item) {
                return [
                    'id' => $item->store_id,
                    'name' => $item->name,
                    'active' => $item->enabled,
                    'branches' => self::branches($item->branches),
                ];
        })->toArray();

        return [
            'object' => $objectName,
            $dataName => $itemsTransformed
        ];
    }

    private function branches($data)
    {
        return $data->map(function ($item) {
            return [
                'id' => $item->branch_id,
                'name' => $item->name,
                'active' => $item->enabled,
                'locations' => self::branch_locations($item->branch_locations),
            ];
        });
    }

    private function branch_locations($data)
    {
        return $data->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'address' => $item->address,
                'active' => $item->enabled,
            ];
        });
    }

}
