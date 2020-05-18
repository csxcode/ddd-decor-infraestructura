<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/16/2019
 * Time: 5:49 PM
 */

namespace App\Enums;


class AppObjectNameEnum
{
    const AUTH = "auth";
    const STORE = "store";
    const MAJOR_ACCOUNT = "major_account";
    const COST_CENTER = "cost_center";
    const DASHBOARD = "dashboard";
    const CONTACT = "contact";
    const MAINTENANCE = "maintenance";
    const VENDOR = "vendor";

    const CHECKLIST = "checklist";
    const CHECKLIST_STATUS = "checklist_status";
    const CHECKLIST_ITEM_DETAILS = "checklist_item_details";
    const CHECKLIST_ITEM_TYPE = "checklist_item_type";
    const CHECKLIST_ITEM = "checklist_item";

    const TICKET = "ticket";
    const TICKET_STATUS = "ticket_status";
    const TICKET_TYPE = "ticket_type";
    const TICKET_PHOTO = "ticket_photo";
    const TICKET_ACTION = "ticket_action";
    const TICKET_COMMENT = "ticket_comment";

    const WORK_ORDER = "work_order";
    const WORK_ORDER_PHOTO = "work_order_photo";
    const WORK_ORDER_FILE = "work_order_file";

    const WORK_ORDER_CONTACT = "work_order_contact";

    const WORK_ORDER_QUOTE = "work_order_quote";
    const WORK_ORDER_QUOTE_FILE = "work_order_quote_file";
    const WORK_ORDER_QUOTE_PHOTO = "work_order_quote_photo";

    const WORK_ORDER_COST_CENTER = "work_order_cost_center";

    const WORK_ORDER_HISTORY = "work_order_history";
    const WORK_ORDER_HISTORY_FILE = "work_order_history_file";
    const WORK_ORDER_HISTORY_PHOTO = "work_order_history_photo";
    const WORK_ORDER_HISTORY_VIDEO = "work_order_history_video";

    const ERROR = "error";
}
