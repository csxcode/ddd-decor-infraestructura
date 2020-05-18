<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/27/2019
 * Time: 4:16 PM
 */

namespace Support\Enums;


class ChecklistEnum
{
    // STATUS
    const CHECKLIST_STATUS_NEW = 1;
    const CHECKLIST_STATUS_APPROVED = 2;
    const CHECKLIST_STATUS_REJECTED = 3;

    // EDIT_STATUS
    const EDIT_STATUS_EDITING = 0;
    const EDIT_STATUS_COMPLETED = 1;

    // STRUCTURE CHECKLIST
    const STRUCTURE_CHECKLIST_T_TYPE = 1;
    const STRUCTURE_CHECKLIST_T_SUBTYPE = 2;
    const STRUCTURE_CHECKLIST_T_ITEM = 3;
}
