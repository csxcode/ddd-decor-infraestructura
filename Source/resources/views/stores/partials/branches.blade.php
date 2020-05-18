<div id="container-branches">

<div class="form-row">
    <div class="form-group col-md-12">
        <div class="panel-body" style="padding: 0px">
            <table id="data-table-fixed-header" class="table table-striped table-bordered" style="margin-bottom: 0px">
                <thead>
                <tr>
                    <th class="text-nowrap text-center bg-success">Opciones</th>
                    <th class="text-nowrap text-center bg-success">Sucursal</th>
                    <th class="text-nowrap text-center bg-success">Activo / Inactivo</th>
                </tr>
                </thead>
                <tbody id="datatable-body">
                @if(!is_null($branches))
                    @if($branches->count()>0)
                        @foreach($branches as $item)
                            <tr data-value="{{ $item->name }}" class="odd gradeX row_{{ $item->branch_id }}">
                                <td class="text-center">
                                    <a href="javascript:void(0);" class="btn btn-info btn-icon btn-circle btn-sm mr-1" onclick="ShowBranch({{$item->branch_id}})" title="Ver">
                                        <i class="fa fa-search"></i>
                                    </a>

                                    @if($type == 'edit')
                                        <a href="javascript:void(0);" class="btn btn-warning btn-icon btn-circle btn-sm mr-1" onclick="EditBranch({{$item->branch_id}})" title="Editar">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="btn btn-danger btn-icon btn-circle btn-sm mr-1" onclick="DeleteBranch({{$item->branch_id}})" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->name }}</td>
                                <td class="text-center">{{ \App\Helpers\StringHelper::GetEnabledFormat($item->enabled) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center height-100" style="line-height: 100px;">{{$datatable_empty_text}}</td>
                        </tr>
                    @endif
                @else
                    <tr>
                        <td colspan="6" class="text-center height-100" style="line-height: 100px;">{{$datatable_empty_text}}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <div class="panel-body" style="padding: 0px;">
            Sucursales relacionadas:
            @if(!is_null($branches))
                <span id="records_counter">{{$branches->count()}}</span>
            @else
                <span id="records_counter">0</span>
            @endif
        </div>
    </div>
</div>

</div>