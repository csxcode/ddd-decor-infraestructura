<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/16/2019
 * Time: 5:48 AM
 */

namespace App\Enums;


class ErrorCodesEnum
{
    const missing_field = 1000;
    const not_valid = 1001;
    const required_list = 1002;
    const not_exists = 1003;
    const already_exists = 1004;
    const unauthorized = 1005;
    const forbidden = 1006;
    const server_error = 1007;
    const no_changes = 1008;
}