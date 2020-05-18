<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/16/2019
 * Time: 2:59 PM
 */

namespace App\Enums;


class UserRoleEnum
{
    /* Role Name */
    const ADMIN = 'admin';
    const GESTOR_INFRAESTRUCTURA = 'gestor';
    const RESPONSABLE_SEDE = 'sede';
    const PROVEEDOR = 'prov';

    /* Role ID */
    const ADMIN_ID = 1;
    const GESTOR_INFRAESTRUCTURA_ID = 4;
    const RESPONSABLE_SEDE_ID = 5;
    const PROVEEDOR_ID = 6;

    /*
     * SB = Store and Branches     
     * 5 = RESPONSABLE_SEDE_ID
     * */
    const ROLES_IDS_ALLOWED_TO_ADD_SB = '5';
}