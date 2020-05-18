<?php namespace App\Enums;

class AccessTypeEnum {
    const Web = 1;
    const Api = 2;

    public static function GetAll(){
        return [
            AccessTypeEnum::Web => 'Web',
            AccessTypeEnum::Api => 'Api',
        ];
    }
}