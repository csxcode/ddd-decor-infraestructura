@extends('layouts.app')
@section('title', 'Tiendas - '. Config::get('app.app_name'))

@section('header')
    <link href="{{ asset('/assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/DataTables/extensions/FixedHeader/css/fixedHeader.bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css') }}" rel="stylesheet" />
@endsection

@section('content')

    @include('alerts.success')

    <!-- begin breadcrumb -->
    <ol class="breadcrumb pull-right">
        <li class="breadcrumb-item"><a href="javascript:;">{{ Config::get('app.app_name') }}</a></li>        
        <li class="breadcrumb-item active text-blue">Tiendas</li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fa fa-users"></i> &nbsp;Tiendas</h1>
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
                    <label for="name">Tienda: </label>
                    {!! Form::text('store_name', null, array('class' => 'form-control', 'placeholder' => 'Ingrese tienda')) !!}
                </div>
                <div class="form-group col-md-4">
                    <label for="name">Sucursal: </label>
                    {!! Form::text('branch_name', null, array('class' => 'form-control', 'placeholder' => 'Ingrese sucursal')) !!}
                </div>
                <div class="form-group col-md-4">
                    <div class="form-group">
                        <label for="enabled">Estado: </label>
                        {!! Form::select('status', $status, null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-purple ml-1" onclick="clearForm('frmFilter')"><i class="fa fa-sync-alt" aria-hidden="true"></i>&nbsp;Limpiar</button>
                    <button type="submit" class="btn btn-lime"><i class="fa fa-search" aria-hidden="true"></i>&nbsp;Buscar</button>
                    <a href="{{ route('stores.create') }}">
                        <button type="button" class="btn btn-warning ml-1">
                            <i class="fa fa-plus-circle" aria-hidden="true"></i>&nbsp;Nuevo
                        </button>
                    </a>
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
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByStores('name', 'Nombre Tienda') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByStores('enabled', 'Activo / Inactivo') !!}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($data->total()>0)
                            @foreach($data as $item)
                                <tr data-id="{{ $item->store_id }}" data-value="{{ $item->name }}" class="odd gradeX row_{{ $item->store_id }}">
                                    <td class="text-center">
                                        <a href="{{ route('stores.show', $item->store_id) }}" class="btn btn-info btn-icon btn-circle btn-sm mr-1" title="Ver">
                                            <i class="fa fa-search"></i>
                                        </a>
                                        <a href="{{ route('stores.edit', $item->store_id) }}" class="btn btn-warning btn-icon btn-circle btn-sm mr-1" title="Editar">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="btn btn-danger btn-icon btn-circle btn-sm mr-1 btn-delete" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">{{ $item->name }}</td>
                                    <td class="text-center">{{ \App\Helpers\StringHelper::GetEnabledFormat($item->status) }}</td>
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
                    Request::only(['store_name', 'branch_name', 'status', 'sort', 'direction']))->render()))
                    !!}
                @endif
                </div>
            </div>
        </div>
    </div>
    <!-- end panel -->

    {!! Form::open(['route' => ['stores.destroy', ':STORE_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}
@endsection

@section('scripts')
    <script src="{{ asset('/assets/plugins/DataTables/media/js/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/bootstrap-sweetalert/sweetalert.min.js') }}"></script>

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

            var totals = {{$data->total()}}

            window.setTimeout(function(){
                $('.alert-success').fadeOut('slow');
            }, 3000);

            if (totals > 0) {
                InitDataTable();
            }

            $('.btn-delete').click(function (e) {
                e.preventDefault();
                var row = $(this).parents('tr');
                var id = row.data('id');
                var value = row.data('value');

                swal({
                    title: 'Eliminación de Tienda',
                    text: 'Estas seguro de eliminar la tienda: ' + value + '?',
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            text: 'Cancelar',
                            value: null,
                            visible: true,
                            className: 'btn btn-default',
                            closeModal: true,
                        },
                        confirm: {
                            text: 'Eliminar',
                            value: true,
                            visible: true,
                            className: 'btn btn-warning',
                            closeModal: true
                        }
                    }
                }).then((value) => {

                    if (value) {
                        var form = $('#form-delete');
                        var url = form.attr('action').replace(':STORE_ID', id);
                        var data = form.serialize();
                        $.post(url, data, function (result) {

                        }).fail(function (result) {
                            var message = result.responseJSON.message;
                            if(!message){
                                message = 'Ha ocurrido un error al eliminar la tienda.';
                            }

                            swal("Error", message, "error");

                        }).done(function (result) {

                            var message = result.message;
                            if(!message){
                                message = 'Registro Eliminado.';
                            }
                            swal("Registro Eliminado", message, "success");

                            $('.row_' + id).fadeOut('slow');
                            var current_counter = $('#records_counter').text();
                            $('#records_counter').text(current_counter - 1);
                        });
                    }

                });

            });

        });
    </script>
@endsection