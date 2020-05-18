@extends('layouts.app')
@section('title', 'Tickets - '. Config::get('app.app_name'))

@section('header')
    <link rel="stylesheet" href="{{ asset('/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}">
    <link href="{{ asset('/assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/DataTables/extensions/FixedHeader/css/fixedHeader.bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css') }}" rel="stylesheet" />
@endsection

@section('content')

    @include('alerts.success')

    <!-- begin breadcrumb -->
    <ol class="breadcrumb pull-right">
        <li class="breadcrumb-item"><a href="javascript:;">{{ Config::get('app.app_name') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Tiendas</a></li>
        <li class="breadcrumb-item active text-blue">Tickets</li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fa fa-clone"></i> &nbsp;Tickets</h1>
    <!-- end page-header -->
    <!-- begin panel -->
    <div class="panel panel-inverse">

        <div class="panel-heading">
            <h4 class="panel-title">Búsqueda</h4>
        </div>

        <div class="panel-body">
            {!! Form::model(Request::all(), ['route' => Route::currentRouteName(), 'method' => 'GET', 'role' => 'search', 'id' => 'frmFilter']) !!}
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="ticket_number">Num. Ticket: </label>
                    {!! Form::text('ticket_number', null, array('class' => 'form-control', 'placeholder' => 'Ingrese numero ticket')) !!}
                </div>
                <div class="form-group col-md-4">
                    <label for="store">Tienda: </label>
                    {!! Form::select('store', $stores, null, ['class' => 'form-control', 'id' => 'store']) !!}
                </div>
                <div class="form-group col-md-4">
                    <div class="form-group">
                        <label for="branch">Sucursal: </label>
                        {!! Form::select('branch', $branches, null, ['class' => 'form-control', 'id' => 'branch']) !!}
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="type">Tipo: </label>
                    {!! Form::select('type', $types, null, ['class' => 'form-control']) !!}
                </div>

                <div class="form-group input-group-sm col-md-4">
                    <label for="date_from">Fecha: </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon" id="sizing-addon-from">Desde:</span>
                        {!! Form::text('date_from', null, array('id' => 'date_from', 'class' => 'form-control text-center', 'placeholder' => 'Desde...', 'aria-describedby' => 'sizing-addon-from')) !!}

                        <span class="input-group-addon iga-noBorderRadius" id="sizing-addon-to">Hasta:</span>
                        {!! Form::text('date_to', null, array('id' => 'date_to', 'class' => 'form-control text-center iga-withBorderRadius', 'placeholder' => 'Hasta...', 'aria-describedby' => 'sizing-addon-from')) !!}
                    </div>
                </div>

                <div class="form-group col-md-4">
                    <label for="status">Estado: </label>
                    {!! Form::select('status', $status, null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-purple ml-1" onclick="clearForm('frmFilter')"><i class="fa fa-sync-alt" aria-hidden="true"></i>&nbsp;Limpiar</button>
                    <a class="btn btn-lime border-none btn-rounded-corner" href="{{ LinkHelper::GetUrlExportExcelTickets() }}">
                        <i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;Exportar
                    </a>
                    <button type="submit" class="btn btn-lime"><i class="fa fa-search" aria-hidden="true"></i>&nbsp;Buscar</button>
                </div>
            </div>
            {!! Form::close() !!}

        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="panel-body">
                    <table id="data-table-fixed-header" class="table table-striped table-bordered" style="margin-bottom: 0px">
                        <thead>
                        <tr>
                            <th class="text-nowrap text-center">Opciones</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByTickets('ticket_number', 'Núm. Ticket') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByTickets('created_at', 'Fecha') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByTickets('type_name', 'Tipo') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByTickets('store_name', 'Tienda') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByTickets('status_name', 'Estado') !!}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($data->total()>0)
                            @foreach($data as $item)
                                <tr data-id="{{ $item->ticket_number }}" data-value="{{ $item->ticket_number }}" class="odd gradeX">
                                    <td class="text-center">
                                        <a href="{{ route('tickets.show', $item->ticket_number) }}" class="btn btn-info btn-icon btn-circle btn-sm mr-1" title="Ver">
                                            <i class="fa fa-search"></i>
                                        </a>

                                        @if (\App\Http\Controllers\TicketController::CheckIfUserCanEdit($item->status_id))
                                            <a href="{{ route('tickets.edit', $item->ticket_number) }}" class="btn btn-warning btn-icon btn-circle btn-sm mr-1" title="Editar">
                                                <i class="fa fa-pencil-alt"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->ticket_number }}</td>
                                    <td class="text-center">{{ DatetimeHelper::GetDateTimeByTimeZone($item->created_at, null, 'd/m/y h:i a') }}</td>
                                    <td class="text-center">{{ $item->type_name }}</td>
                                    <td class="text-center">{{ $item->store_name }}</td>
                                    <td class="text-center">{{ $item->status_name }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center height-100">No hay datos.</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 text-left">
                <div class="panel-body" style="padding-top: 0px;">
                @if(!is_null($data))
                    <span id="records_counter">{{$data->total()}}</span> Registro(s) encontrados
                @endif
                </div>
            </div>
            <div class="col-lg-9 text-right">
                <div class="panel-body" style="display:inline-block;padding-top: 0px;">
                @if(!is_null($data))
                    {!! str_replace('/?', '?', ($data->appends(
                    Request::only(['ticket_number', 'store', 'branch', 'type', 'date_from', 'date_to', 'status', 'sort', 'direction']))->render()))
                    !!}
                @endif
                </div>
            </div>
        </div>
    </div>
    <!-- end panel -->

@endsection

@section('scripts')
    <script src="{{ asset('/assets/plugins/DataTables/media/js/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/bootstrap-sweetalert/sweetalert.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"></script>

    <script>
        function InitDataTable() {
            if ($('#data-table-fixed-header tbody tr').hasClass('odd')) {
                $('#data-table-fixed-header').DataTable({
                    lengthMenu: [20, 40, 60],
                    fixedHeader: {
                        header: true,
                        headerOffset: $('#header').height()
                    },
                    responsive: true,
                    paging: false,
                    searching: false,
                    info: false,
                    bSort: false
                });
            }
        }

        $(document).ready(function(){

            $("#store").change(function(){

                var url = "{{ route('global.ajax.get_branches_by_store') }}?store=" + $(this).val();

                $.get(url).fail(function () {
                    swal('Error', 'Ha ocurrido un error al recuperar las sucursales', 'error');
                }).done(function (result) {
                    $('#branch').html(result.html);
                });

            });


            var totals = {{$data->total()}}

            window.setTimeout(function(){
                $('.alert-success').fadeOut('slow');
            }, 3000);

            if (totals > 0) {
                InitDataTable();
            }

            //Fecha
            $('#date_from').datepicker({
                todayHighlight: true,
                format: 'dd/mm/yyyy',
                autoclose: true,
                orientation: 'bottom'
            }).on('changeDate', function (selected) {
                var minDate = new Date(selected.date.valueOf());
                $('#date_to').datepicker('setStartDate', minDate);
            });

            $('#date_to').datepicker({
                todayHighlight: true,
                format: 'dd/mm/yyyy',
                autoclose: true,
                orientation: 'bottom'
            }).on('changeDate', function (selected) {
                var maxDate = new Date(selected.date.valueOf());
                $('#date_from').datepicker('setEndDate', maxDate);
            });


        });
    </script>
@endsection