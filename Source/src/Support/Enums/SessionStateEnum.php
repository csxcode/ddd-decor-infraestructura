<?php namespace Support\Enums;

class SessionStateEnum {
    const Fallo = 0;
    const Abierto = 1;
    const Cerrado = 2;
    const Expirado = 3;
    const Expulsado = 4;

    public static function getAllSessionState(){
        return [
            SessionStateEnum::Fallo => 'Fallo',
            SessionStateEnum::Abierto => 'Abierto',
            SessionStateEnum::Cerrado => 'Cerrado',
            SessionStateEnum::Expirado => 'Expirado',
            SessionStateEnum::Expulsado => 'Expulsado',
        ];
    }
}
