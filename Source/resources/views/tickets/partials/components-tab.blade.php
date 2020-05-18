<div class="panel-body">

        <div class="form-row">
            <div class="form-group col-md-12">
                <div class="panel-body" style="padding: 0px">
                    <table id="data-table-fixed-header" class="table table-striped table-bordered" style="margin-bottom: 0px">
                        <thead>
                        <tr>
                            <th class="text-nowrap text-center bg-success">Acción</th>
                            <th class="text-nowrap text-center bg-success">Tipo</th>
                            <th class="text-nowrap text-center bg-success">Código</th>
                            <th class="text-nowrap text-center bg-success">Nombre</th>
                            <th class="text-nowrap text-center bg-success">Cantidad</th>
                        </tr>
                        </thead>
                        <tbody id="datatable-body">
                        @if(!is_null($components))
                            @if($components->count()>0)
                                @foreach($components as $item)
                                    <tr data-value="{{ $item->name }}" class="odd gradeX">
                                        <td class="text-left">
                                            <img src="{{\App\Http\Controllers\TicketController::GetActionIconRelated($item->action_id)}}" style="padding-right: 5px;">
                                            {{ $item->action_name }}
                                        </td>
                                        <td class="text-center">{{ $item->type_name }}</td>
                                        <td class="text-center">{{ $item->code }}</td>
                                        <td class="text-center">{{ $item->name }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center height-100" style="line-height: 100px;">{{trans('global.datatable_no_records')}}</td>
                                </tr>
                            @endif
                        @else
                            <tr>
                                <td colspan="6" class="text-center height-100" style="line-height: 100px;">{{trans('global.datatable_no_records')}}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-12">
                <div class="panel-body" style="padding: 0px;">
                    Registros relacionadas:
                    @if(!is_null($components))
                        <span id="records_counter">{{$components->count()}}</span>
                    @else
                        <span id="records_counter">0</span>
                    @endif
                </div>
            </div>
        </div>

    </div>

