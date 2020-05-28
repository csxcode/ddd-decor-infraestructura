<?php namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Checklist\Checklist;
use App\Models\Store;
use App\Models\Ticket\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{

    protected $request;
    protected $niceNames;
    protected $user_type_allowed_sb;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->niceNames = [
            'name' => 'Nombre de Tienda'
        ];
        $this->datatable_empty_text = 'Ninguna sucursal relacionada.';
    }

    public function index()
    {
        if (!Auth::user()->hasRole('admin', 'tr', 'gerencia')) {
            return view('errors.403');
        }

        $store_name = $this->request->get('store_name');
        $branch_name = $this->request->get('branch_name');
        $status = $this->request->get('status');

        $sort = $this->request->get('sort');
        $direction = $this->request->get('direction');

        //get data
        $data = Store::Search(
            compact('store_name', 'branch_name', 'status'),
            compact('sort', 'direction')
        );

        $status = array(
            '' => 'Todos',
            '1' => 'Activo',
            '0' => 'Inactivo'
        );

        $datatable_empty_text = $this->datatable_empty_text;

        return view('stores.index')->with(compact('data', 'status', 'datatable_empty_text'));
    }

    public function create()
    {
        if (!Auth::user()->hasRole('admin', 'tr', 'gerencia')) {
            return view('errors.403');
        }

        $data = new Store();
        $data->enabled = 1;
        $branches = null;
        $datatable_empty_text = $this->datatable_empty_text;

        return view('stores.create')->with(compact('data', 'branches', 'datatable_empty_text'));
    }

    public function store()
    {
        if (!Auth::user()->hasRole('admin', 'tr', 'gerencia')) {
            return view('errors.403');
        }

        $was_saved = ($this->request->get('__was_saved') == 1 ? true : false);
        $id = $this->request->get('__store_id');

        $msg = null;
        $data = null;

        if ($was_saved) {

            $data = Store::findOrFail($id);

            $rules = [
                'name' => 'required|unique:store,name,' . $id . ',' . 'store_id'
            ];

            $validator = \Validator::make($this->request->all(), $rules, [], $this->niceNames);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $data->fill(array(
                'name' => $this->request->get('name'),
                'enabled' => ($this->request->get('enabled') == 'on' ? 1 : 0),
                'updated_at' => Carbon::now()
            ))->save();

            $msg = '&nbsp;&nbsp;La tienda  <strong>' . $this->request->get('name') . '</strong> fue actualizado correctamente.';

        } else {

            $rules = [
                'name' => 'required|unique:store,name'
            ];

            $validator = \Validator::make($this->request->all(), $rules, [], $this->niceNames);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $data = Store::create(array(
                'name' => $this->request->get('name'),
                'enabled' => ($this->request->get('enabled') == 'on' ? 1 : 0),
                'updated_at' => null
            ));

            $msg = '&nbsp;&nbsp;Se agregó correctamente la tienda <strong>' . $this->request->get('name') . '</strong>';
        }

        return response()->json([
            'success' => $msg,
            'store_id' => $data->store_id
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Auth::user()->hasRole('admin', 'tr', 'gerencia')) {
            return view('errors.403');
        }

        $data = Store::find($id);
        $branches = Branch::where('store_id', $id)->get();
        $datatable_empty_text = $this->datatable_empty_text;

        return view('stores.show')->with(compact('data', 'branches', 'datatable_empty_text'));
    }

    public function edit($id)
    {
        if (!Auth::user()->hasRole('admin', 'tr', 'gerencia')) {
            return view('errors.403');
        }

        $data = Store::find($id);
        $branches = Branch::where('store_id', $id)->get();
        $datatable_empty_text = $this->datatable_empty_text;

        return view('stores.edit')->with(compact('data', 'branches', 'datatable_empty_text'));
    }

    public function update($id)
    {
        if (!Auth::user()->hasRole('admin', 'tr', 'gerencia')) {
            return view('errors.403');
        }

        $data = Store::findOrFail($id);

        $rules = [
            'name' => 'required|unique:store,name,' . $id . ',' . 'store_id'
        ];

        $this->validate($this->request, $rules, [], $this->niceNames);

        $data->fill(array(
            'name' => $this->request->get('name'),
            'enabled' => ($this->request->get('enabled') == 'on' ? 1 : 0),
            'updated_at' => Carbon::now()
        ))->save();

        // Return
        Session::flash('flash_message', 'La tienda ' . $this->request->get('name') . ' fue actualizado correctamente.');

        return Redirect::route('stores.index');
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasRole('admin', 'tr', 'gerencia')) {
            return view('errors.403');
        }

        $store = Store::find($id);

        if (is_null($store)) {
            return response()->json('data not found', 404);
        }

        $branches = Branch::where('store_id', $id)->pluck('branch_id');        
        $checklist = Checklist::leftJoin('exhibition', 'exhibition.exhibition_id', 'checklist.exhibition_id')->whereIn('exhibition.branch_id', $branches)->get();
        $ticket = Ticket::whereIn('branch_id', $branches)->get();

        if ($checklist->count() == 0 && $ticket->count() == 0) {
            $store->delete();
            $msg = 'La tienda ' . $store->name . ' fue eliminado correctamente!';
            return response(['message' => $msg], Response::HTTP_OK);
        } else {
            $msg = 'No se puede eliminar la tienda ' . $store->name . ' porque tiene checklists o tickets relacionados.';
            return response(['message' => $msg], Response::HTTP_BAD_REQUEST);
        }

    }


    /* --------------------------------------------- */
    /* AJAX METHODS */
    /* --------------------------------------------- */
    public static function GetBranchData($id, $type){

        $new = $id == 0 ? true : false;
        $data = new Branch();

        if(!$new){
            $data = Branch::select('branch_id', 'name as branch_name', 'enabled as branch_enabled')->find($id);
        }

        $html = view('stores.partials.modal-branch', compact('type', 'data'))->render();

        return response()->json(compact('html'));
    }

    public function DestroyBranch($id)
    {
        if (!Auth::user()->hasRole('admin', 'tr', 'gerencia')) {
            return view('errors.403');
        }

        $branch = Branch::find($id);

        if (is_null($branch)) {
            return response()->json('data not found', 404);
        }
        
        $checklist = Checklist::leftJoin('exhibition', 'exhibition.exhibition_id', 'checklist.exhibition_id')->where('exhibition.branch_id', $branch->branch_id)->get();
        $ticket = Ticket::where('branch_id', $branch->branch_id)->get();

        if ($checklist->count() == 0 && $ticket->count() == 0) {

            $branch->delete();
            $msg = 'La sucursal ' . $branch->name . ' fue eliminado correctamente!';

            // Get and set data for branches list
            $branches = Branch::where('store_id', $branch->store_id)->get();
            $datatable_empty_text = $this->datatable_empty_text;
            $type = 'edit';
            $branches_html = view('stores.partials.branches', compact('branches', 'datatable_empty_text', 'type'))->render();

            return response([
                'message' => $msg,
                'branches_html' => $branches_html
            ], Response::HTTP_OK);

        } else {
            $msg = 'No se puede eliminar la sucursal ' . $branch->name . ' porque tiene checklists o tickets relacionados.';
            return response(['message' => $msg], Response::HTTP_BAD_REQUEST);
        }
    }

    public function SaveBranch($branch_id, $store_id)
    {
        if (!Auth::user()->hasRole('admin', 'tr', 'gerencia')) {
            return view('errors.403');
        }

        $store = Store::find($store_id);

        if (is_null($store)) {
            return response()->json('data not found', 404);
        }

        $niceNames = [
            'name' => 'Nombre de Sucursal'
        ];

        if ($branch_id == 0) {

            $rules = [
                'name' => [
                    'required',
                    Rule::unique('branch')->where(function ($query) use ($store, $branch_id) {
                        return $query->where('store_id', $store->store_id);
                    })
                ]
            ];

            $validator = \Validator::make([
                'name' => $this->request->get('branch_name')
            ], $rules, [], $niceNames);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            Branch::create(array(
                'name' => $this->request->get('branch_name'),
                'enabled' => ($this->request->get('branch_enabled') == 'on' ? 1 : 0),
                'store_id' => $store_id,
                'updated_at' => null
            ));

            $msg = '&nbsp;&nbsp;Se agregó correctamente la sucursal <strong>' . $this->request->get('branch_name') . '</strong>';

        } else {

            $data = Branch::findOrFail($branch_id);

            $rules = [
                'name' => [
                    'required',
                    Rule::unique('branch')->where(function ($query) use ($store, $branch_id) {
                        return $query->where('store_id', $store->store_id)
                            ->where('branch_id', '<>', $branch_id);
                    })
                ]
            ];

            $validator = \Validator::make([
                'name' => $this->request->get('branch_name')
            ], $rules, [], $niceNames);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $data->fill(array(
                'name' => $this->request->get('branch_name'),
                'enabled' => ($this->request->get('branch_enabled') == 'on' ? 1 : 0),
                'updated_at' => Carbon::now()
            ))->save();

            $msg = '&nbsp;&nbsp;La sucursal  <strong>' . $this->request->get('branch_name') . '</strong> fue actualizado correctamente.';

        }

        // Get and set data for branches list
        $branches = Branch::where('store_id', $store_id)->get();
        $datatable_empty_text = $this->datatable_empty_text;
        $type = 'edit';
        $branches_html = view('stores.partials.branches', compact('branches', 'datatable_empty_text', 'type'))->render();

        return response()->json([
            'success' => $msg,
            'branches_html' => $branches_html
        ], Response::HTTP_OK);

    }

}