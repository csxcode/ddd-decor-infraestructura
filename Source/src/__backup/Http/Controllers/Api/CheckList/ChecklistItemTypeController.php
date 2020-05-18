<?php
namespace App\Http\Controllers\Api\CheckList;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\StringHelper;
use App\Transformers\CheckListItemTypeTransformer;
use App\Http\Controllers\Api\CheckList\Validations\ChecklistItemTypeValidation;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Http\Controllers\Controller;
use App\Models\Checklist\ChecklistItemType;
use App\Models\User;
use App\Models\UserStoreBranch;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChecklistItemTypeController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function All()
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($this->request->bearerToken());
        $checklist_id = StringHelper::Trim($this->request->get('checklist_id'));
        $types = StringHelper::Trim($this->request->get('types'));

        // --------------------------------------------
        // Validations
        // --------------------------------------------
        $error_response = ChecklistItemTypeValidation::AllValidation($this->request);

        if($error_response)
            return $error_response;

        try {

            //-------------------------------------------
            // Get data
            // ------------------------------------------
            $data_to_return = self::GetDataForAll($user, $checklist_id, $types);

            return response()->json(
                [
                    'object' => AppObjectNameEnum::CHECKLIST_ITEM_TYPE,
                    'types' => $data_to_return
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    private static function GetDataForAll($user, $checklist_id, $types){

        $usb_allowed = null;
        $showItems = $types == 1 ? false : true;

        if (!StringHelper::IsNullOrEmptyString($checklist_id)) {
            // check if user needs filter by branch
            if(GlobalValidation::UserNeedToFilterData($user)){
                $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
            }
        }

        //--------------------------------------------------
        // Apply filter to get data
        //--------------------------------------------------

        //-------------------------
        // BEGIN [TYPES]
        //-------------------------
        $query_to_types = ChecklistItemType::
            select('checklist_item_type.id', 'checklist_item_type.name')->
            with(['sub_types' => function ($query) use ($usb_allowed, $checklist_id, $showItems) {

                //-------------------------
                // BEGIN [SUB_TYPES]
                //-------------------------
                $query_to_subItems = $query->select('checklist_item_type.id', 'checklist_item_type.name', 'checklist_item_type.parent_id');

                if ($showItems) {

                    //-------------------------
                    // BEGIN [ITEMS]
                    //-------------------------
                    $query_to_subItems->with(['items' => function ($query) use ($usb_allowed, $checklist_id) {

                        $query_to_items = $query->select(['checklist_item.id', 'checklist_item.name', 'checklist_item.type', 'checklist_item.description']);

                        if (!StringHelper::IsNullOrEmptyString($checklist_id)) {

                            // mostrar solo los checklist items del checklist
                            $query_to_items->join('checklist_item_details', 'checklist_item.id', 'checklist_item_details.checklist_item_id')
                                ->where('checklist_item_details.checklist_id', $checklist_id);

                            if ($usb_allowed != null) {
                                $query_to_items->whereIn('checklist.branch_id', $usb_allowed);
                            }

                        } else {

                            // aplica la regla de status (enabled) a la tabla checklist_item
                            $query_to_items->where('checklist_item.item_status', 1);
                        }

                        $query_to_items->orderBy('checklist_item.display_order', 'asc');
                    }]);

                    //-------------------------
                    // END [ITEMS]
                    //-------------------------
                }

                // filter for checklist_id
                if (!StringHelper::IsNullOrEmptyString($checklist_id)) {

                    $query_to_subItems
                        ->join('checklist_item', 'checklist_item_type.id', 'checklist_item.type')
                        ->join('checklist_item_details', 'checklist_item.id', 'checklist_item_details.checklist_item_id')
                        ->where('checklist_item_details.checklist_id', $checklist_id);

                }else{
                    // aplica la regla de status (enabled) a la tabla checklist_item_type (sub_types = child)
                    $query_to_subItems->where('type_status', 1);
                }

                $query_to_subItems
                    ->groupBy(['checklist_item_type.id', 'checklist_item_type.name'])
                    ->orderBy('checklist_item_type.display_order', 'asc');

                //-------------------------
                // END [SUB_TYPES]
                //-------------------------
        }]);

        // filter for checklist_id
        if (!StringHelper::IsNullOrEmptyString($checklist_id)) {

            // mostrar solo los checklist_item_type del checklist
            $query_to_types
                ->join('checklist_item_type as sub_type', 'checklist_item_type.id', 'sub_type.parent_id')
                ->join('checklist_item', 'sub_type.id', 'checklist_item.type')
                ->join('checklist_item_details', 'checklist_item.id', 'checklist_item_details.checklist_item_id')
                ->where('checklist_item_details.checklist_id', $checklist_id);

        }else{

            // aplica la regla de status (enabled) a la tabla checklist_item_type (types = parent)
            $query_to_types->where('checklist_item_type.type_status', 1);
        }

        $data = $query_to_types
            ->where('checklist_item_type.parent_id', null)
            ->groupBy(['checklist_item_type.id', 'checklist_item_type.name'])
            ->orderBy('checklist_item_type.display_order', 'asc')
            ->get()
            ->toArray();

        //-------------------------
        // END [TYPES]
        //-------------------------

        return (new CheckListItemTypeTransformer)->all($data);
    }

}