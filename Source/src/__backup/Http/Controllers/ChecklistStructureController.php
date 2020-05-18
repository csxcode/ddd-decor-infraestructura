<?php

namespace App\Http\Controllers;

use App\Enums\ActionEnum;
use App\Enums\ChecklistEnum;
use App\Models\Checklist\ChecklistItem;
use App\Models\Checklist\ChecklistItemType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ChecklistStructureController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function Index()
    {       
        $data = self::GetSearchData(false);                     

        $status_list = array(
            '' => trans('global.all_select'),
            '1' => 'Activo',
            '0' => 'Inactivo'
        );

        $can_create_edit_delete = self::CheckUserCanCreateEditAndDelete();

        return view('checklist_structure.index')->with(compact('data', 'status_list', 'can_create_edit_delete'));
    }
    
    public function GetGridViewData()
    {
        $data = self::GetSearchData(false); 
        $can_create_edit_delete = self::CheckUserCanCreateEditAndDelete();

        $html = view('checklist_structure.partials.grid', 
            compact('data', 'can_create_edit_delete')
        )->render();

        return response()->json($html);
    }

    public function Export()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');        

        $data = self::GetSearchData(true);        

        return Excel::create('EstructuraChecklist', function($excel) use ($data) {

            $excel->sheet('Datos', function($sheet) use ($data) {
                $sheet->setHeight(array(
                    1     =>  16,                    
                ));

                $sheet->setFontSize(10);

                $sheet->loadView('templates.excel.structure_checklist')->with(['data' => $data]);
                $sheet->setFreeze("A2");
                $sheet->setAutoFilter('A1:F1');

                $sheet->cells('A1:F1', function($cells) {
                    $cells->setAlignment('center');
                });
            });

        })->export('xlsx');

    }


    private function GetSearchData($export){

         // get filters
         $user = Auth::user();
         $type_name = $this->request->get('type_name');
         $type_status = $this->request->get('type_status');
         $subtype_name = $this->request->get('subtype_name');
         $subtype_status = $this->request->get('subtype_status');
         $item_name = $this->request->get('item_name');
         $item_status = $this->request->get('item_status');                   
 
         // get data  
         $params = [
             $type_name,
             $type_status,
             $subtype_name,
             $subtype_status,
             $item_name,
             $item_status,
             ($export ? 1 : 0)
         ];

         $data = DB::select('call sp_structure_checklist_search(?, ?, ?, ?, ?, ?, ?)', $params);  
         return $data;
    }

    public static function CheckUserCanCreateEditAndDelete(){

        $can = true;
        $user = Auth::user();

        if (!$user->hasRole(['visual', 'admin'])){
            $can = false;
        }       

        return $can;
    }


    /* --------------------------------------------- */
    /*          [Dataentry] Modal: Type              */
    /* --------------------------------------------- */
    public function ShowTypeDataEntry($id, $action)
    {
        $data = new ChecklistItemType();     
        $module_name = 'Tipo';   

        if($action == ActionEnum::CREATE){

            $order = 1;
            $max_display_order = ChecklistItemType::where('parent_id', null)->where('display_order', '<>', null)->max('display_order');            
            
            if($max_display_order != null){
                $order = $max_display_order + 1;
            }

            $data->type_status = 1;
            $data->display_order = $order;
        } else {
            $data = ChecklistItemType::where('id', $id)
                ->where('parent_id', null)
                ->first();
        }

        if (!$data) {
            return response()->json([
                'message' => trans('api/validation.not_exists', ['attribute' => $module_name]),
            ], Response::HTTP_NOT_FOUND);
        }

        $html = view('checklist_structure.type.dataentry', 
            compact('action', 'data', 'module_name')
        )->render();

        return response()->json(compact('html'));
    }    

    public function SaveTypeDataEntry($id)
    {
        if (!self::CheckUserCanCreateEditAndDelete()) {
            return response()->json(['errors' => ['Este usuario no puede guardar este tipo.']], Response::HTTP_BAD_REQUEST);
        }
      
        $niceNames = [
            'name' => '"Nombre"',
            'display_order' => '"Orden"'
        ];

        $title = null;
        $message = null;
        $prefix = "modal_type";

        $_name =  $this->request->get($prefix.'_name');        
        $_order =  $this->request->get($prefix.'_display_order');
        $_status =  $this->request->get($prefix.'_status');

        if ($id == 0) {

            $rules = [
                'name' => [
                    'required',
                    Rule::unique('checklist_item_type')->where(function ($query) {
                        return $query->where('parent_id', null);
                    })
                ],
                'display_order' => [
                    'required',
                    'numeric',                  
                    'min:1',
                    Rule::unique('checklist_item_type')->where(function ($query) {
                        return $query->where('parent_id', null);
                    }),                   
                ]
            ];

            $validator = \Validator::make([
                'name' => $_name,
                'display_order' => $_order
            ], $rules, [], $niceNames);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            ChecklistItemType::create(array(
                'name' => $_name,
                'parent_id' => null,
                'display_order' => $_order,
                'type_status' => ($_status == 'on' ? 1 : 0)                
            ));

            $title = 'Tipo Creado';
            $message = 'Se agregó correctamente el tipo <b>' . $_name . '</b>.';

        } else {

            $data = ChecklistItemType::findOrFail($id);

            $rules = [
                'name' => [
                    'required',
                    Rule::unique('checklist_item_type')->where(function ($query) use ($id) {
                        return $query->where('parent_id', null)
                            ->where('id', '<>', $id);
                    })
                ],
                'display_order' => [
                    'required',
                    'numeric',                  
                    'min:1',
                    Rule::unique('checklist_item_type')->where(function ($query) use ($id) {
                        return $query->where('parent_id', null)
                            ->where('id', '<>', $id);
                    })
                ]
            ];

            $validator = \Validator::make([
                'name' => $_name,
                'display_order' => $_order
            ], $rules, [], $niceNames);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $data->fill(array(
                'name' => $_name,
                'parent_id' => null,  
                'display_order' => $_order,              
                'type_status' => ($_status == 'on' ? 1 : 0),                
            ))->save();

            $title = 'Tipo Actualizado';
            $message = 'El tipo  <b>' . $_name . '</b> fue actualizado correctamente.';
        }               

        return response()->json([
            'title' => $title,
            'message' => $message
        ], Response::HTTP_OK);

    }

    public function DeleteType($id)
    {
        if (!self::CheckUserCanCreateEditAndDelete()) {
            return response(['errors' => 'Este usuario no puede eliminar este tipo.'], Response::HTTP_BAD_REQUEST);
        }

        $data = ChecklistItemType::find($id);

        if (is_null($data)) {
            return response(['errors' => 'Tipo no encontrado.'], Response::HTTP_BAD_REQUEST);
        }

        $items = ChecklistItem::CountRecordsRelatedByTypeOrSubtypeOrItem($data->id, ChecklistEnum::STRUCTURE_CHECKLIST_T_TYPE);        
        
        if ($items == 0) {
            $data->delete();                              
            return response(Response::HTTP_OK);

        } else {
            $msg = 'No se puede eliminar el tipo <b>' . $data->name . '</b> porque tiene items relacionados.';
            return response(['errors' => $msg], Response::HTTP_BAD_REQUEST);
        }
    }


    /* --------------------------------------------- */
    /*          [Dataentry] Modal: Subtype           */
    /* --------------------------------------------- */
    public function ShowSubtypeDataEntry($id, $type_id, $action)
    {
        $data = new ChecklistItemType();
        $module_name = 'Subtipo';     
        $type_name = null;        

        if($action == ActionEnum::CREATE){

            $order = 1;
            $max_display_order = ChecklistItemType::where('parent_id', $type_id)->where('display_order', '<>', null)->max('display_order');            
            
            if($max_display_order != null){
                $order = $max_display_order + 1;
            }

            $data->type_status = 1;
            $data->display_order = $order;
            $data->parent_id = $type_id;
        } else {
            $data = ChecklistItemType::where('id', $id)
                ->where('parent_id', $type_id)
                ->first();
        }

        if (!$data) {
            return response()->json([
                'message' => trans('api/validation.not_exists', ['attribute' => $module_name]),
            ], Response::HTTP_NOT_FOUND);
        }

        $type_data = ChecklistItemType::where('id', $type_id)->first();

        if($type_data){
            $type_name = $type_data->name;
        }

        $html = view('checklist_structure.subtype.dataentry', 
            compact('action', 'data', 'module_name', 'type_name')
        )->render();

        return response()->json(compact('html'));
    }    

    public function SaveSubtypeDataEntry($id)
    {
        if (!self::CheckUserCanCreateEditAndDelete()) {
            return response()->json(['errors' => ['Este usuario no puede guardar este subtipo.']], Response::HTTP_BAD_REQUEST);
        }
      
        $niceNames = [
            'name' => '"Nombre"',
            'display_order' => '"Orden"'
        ];

        $title = null;
        $message = null;
        $prefix = "modal_subtype";

        $_name =  $this->request->get($prefix.'_name');        
        $_order =  $this->request->get($prefix.'_display_order');
        $_status =  $this->request->get($prefix.'_status');
        $_type_id =  $this->request->get($prefix.'_type_id');

        if ($id == 0) {

            $rules = [
                'name' => [
                    'required',
                    Rule::unique('checklist_item_type')->where(function ($query) use ($_type_id) {
                        return $query->where('parent_id', $_type_id);
                    })
                ],
                'display_order' => [
                    'required',
                    'numeric',                  
                    'min:1',
                    Rule::unique('checklist_item_type')->where(function ($query) use ($_type_id)  {
                        return $query->where('parent_id', $_type_id);
                    }),                   
                ]
            ];

            $validator = \Validator::make([
                'name' => $_name,
                'display_order' => $_order
            ], $rules, [], $niceNames);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            ChecklistItemType::create(array(
                'name' => $_name,
                'parent_id' => $_type_id,
                'display_order' => $_order,
                'type_status' => ($_status == 'on' ? 1 : 0)                
            ));

            $title = 'Subtipo Creado';
            $message = 'Se agregó correctamente el subtipo <b>' . $_name . '</b>.';

        } else {

            $data = ChecklistItemType::findOrFail($id);

            $rules = [
                'name' => [
                    'required',
                    Rule::unique('checklist_item_type')->where(function ($query) use ($id, $_type_id) {
                        return $query->where('parent_id', $_type_id)
                            ->where('id', '<>', $id);
                    })
                ],
                'display_order' => [
                    'required',
                    'numeric',                  
                    'min:1',
                    Rule::unique('checklist_item_type')->where(function ($query) use ($id, $_type_id) {
                        return $query->where('parent_id', $_type_id)
                            ->where('id', '<>', $id);
                    })
                ]
            ];

            $validator = \Validator::make([
                'name' => $_name,
                'display_order' => $_order
            ], $rules, [], $niceNames);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $data->fill(array(
                'name' => $_name,
                'parent_id' => $_type_id,  
                'display_order' => $_order,              
                'type_status' => ($_status == 'on' ? 1 : 0),                
            ))->save();

            $title = 'Subtipo Actualizado';
            $message = 'El subtipo  <b>' . $_name . '</b> fue actualizado correctamente.';
        }        

        return response()->json([
            'title' => $title,
            'message' => $message
        ], Response::HTTP_OK);

    }

    public function DeleteSubtype($id)
    {            
        if (!self::CheckUserCanCreateEditAndDelete()) {
            return response(['errors' => 'Este usuario no puede eliminar este subtipo.'], Response::HTTP_BAD_REQUEST);
        }

        $data = ChecklistItemType::find($id);

        if (is_null($data)) {
            return response(['errors' => 'Subtipo no encontrado.'], Response::HTTP_BAD_REQUEST);
        }

        $items = ChecklistItem::CountRecordsRelatedByTypeOrSubtypeOrItem($data->id, ChecklistEnum::STRUCTURE_CHECKLIST_T_SUBTYPE);                
        
        if ($items == 0) {
            
            // delete items
            ChecklistItem::where('type', $data->id)->delete();

            // delete subtype
            $data->delete();     

            return response(Response::HTTP_OK);

        } else {
            $msg = 'No se puede eliminar el subtipo <b>' . $data->name . '</b> porque tiene items relacionados.';
            return response(['errors' => $msg], Response::HTTP_BAD_REQUEST);
        }
    }


    /* --------------------------------------------- */
    /*          [Dataentry] Modal: Item              */
    /* --------------------------------------------- */
    public function ShowItemDataEntry($id, $subtype_id, $action)
    {
        $data = new ChecklistItem();
        $module_name = 'Item';     
        $subtype_name = null;        

        if($action == ActionEnum::CREATE){

            $order = 1;
            $max_display_order = ChecklistItem::where('type', $subtype_id)->where('display_order', '<>', null)->max('display_order');            
            
            if($max_display_order != null){
                $order = $max_display_order + 1;
            }

            $data->item_status = 1;
            $data->display_order = $order;
            $data->type = $subtype_id;
        } else {
            $data = ChecklistItem::where('id', $id)
                ->where('type', $subtype_id)
                ->first();
        }

        if (!$data) {
            return response()->json([
                'message' => trans('api/validation.not_exists', ['attribute' => $module_name]),
            ], Response::HTTP_NOT_FOUND);
        }

        $subtype_data = ChecklistItemType::where('id', $subtype_id)->first();

        if($subtype_data){
            $subtype_name = $subtype_data->name;
        }

        $html = view('checklist_structure.item.dataentry', 
            compact('action', 'data', 'module_name', 'subtype_name')
        )->render();

        return response()->json(compact('html'));
    }    

    public function SaveItemDataEntry($id)
    {
        if (!self::CheckUserCanCreateEditAndDelete()) {
            return response()->json(['errors' => ['Este usuario no puede guardar este item.']], Response::HTTP_BAD_REQUEST);
        }
      
        $niceNames = [
            'name' => '"Nombre"',
            'display_order' => '"Orden"'
        ];

        $title = null;
        $message = null;
        $prefix = "modal_item";

        $_name =  $this->request->get($prefix.'_name');        
        $_description =  $this->request->get($prefix.'_description');
        $_order =  $this->request->get($prefix.'_display_order');        
        $_status =  $this->request->get($prefix.'_status');
        $_subtype_id =  $this->request->get($prefix.'_subtype_id');

        if ($id == 0) {

            $rules = [
                'name' => [
                    'required',
                    Rule::unique('checklist_item')->where(function ($query) use ($_subtype_id) {
                        return $query->where('type', $_subtype_id);
                    })
                ],
                'display_order' => [
                    'required',
                    'numeric',                  
                    'min:1',
                    Rule::unique('checklist_item')->where(function ($query) use ($_subtype_id)  {
                        return $query->where('type', $_subtype_id);
                    }),                   
                ]
            ];

            $validator = \Validator::make([
                'name' => $_name,
                'display_order' => $_order
            ], $rules, [], $niceNames);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            ChecklistItem::create(array(
                'name' => $_name,
                'description' => $_description,
                'type' => $_subtype_id,
                'display_order' => $_order,
                'item_status' => ($_status == 'on' ? 1 : 0)                
            ));

            $title = 'Item Creado';
            $message = 'Se agregó correctamente el item <b>' . $_name . '</b>.';

        } else {

            $data = ChecklistItem::findOrFail($id);

            $rules = [
                'name' => [
                    'required',
                    Rule::unique('checklist_item')->where(function ($query) use ($id, $_subtype_id) {
                        return $query->where('type', $_subtype_id)
                            ->where('id', '<>', $id);
                    })
                ],
                'display_order' => [
                    'required',
                    'numeric',                  
                    'min:1',
                    Rule::unique('checklist_item')->where(function ($query) use ($id, $_subtype_id) {
                        return $query->where('type', $_subtype_id)
                            ->where('id', '<>', $id);
                    })
                ]
            ];

            $validator = \Validator::make([
                'name' => $_name,
                'display_order' => $_order
            ], $rules, [], $niceNames);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $data->fill(array(
                'name' => $_name,
                'description' => $_description,
                'type' => $_subtype_id,  
                'display_order' => $_order,              
                'item_status' => ($_status == 'on' ? 1 : 0),                
            ))->save();

            $title = 'Item Actualizado';
            $message = 'El item  <b>' . $_name . '</b> fue actualizado correctamente.';
        }        

        return response()->json([
            'title' => $title,
            'message' => $message
        ], Response::HTTP_OK);

    }

    public function DeleteItem($id)
    {            
        if (!self::CheckUserCanCreateEditAndDelete()) {
            return response(['errors' => 'Este usuario no puede eliminar este item.'], Response::HTTP_BAD_REQUEST);
        }

        $data = ChecklistItem::find($id);

        if (is_null($data)) {
            return response(['errors' => 'Item no encontrado.'], Response::HTTP_BAD_REQUEST);
        }

        $checklists = ChecklistItem::CountRecordsRelatedByTypeOrSubtypeOrItem($data->id, ChecklistEnum::STRUCTURE_CHECKLIST_T_ITEM);                
        
        if ($checklists == 0) {
                                  
            $data->delete();     

            return response(Response::HTTP_OK);

        } else {
            $msg = 'No se puede eliminar el item <b>' . $data->name . '</b> porque tiene checklists relacionados.';
            return response(['errors' => $msg], Response::HTTP_BAD_REQUEST);
        }
    }
}
