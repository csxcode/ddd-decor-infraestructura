@extends('layouts.app')
@section('title', 'Usuarios - '. Config::get('app.app_name'))

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
        <li class="breadcrumb-item"><a href="javascript:;">Admin</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Usuarios</a></li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fa fa-users"></i> &nbsp;Usuarios</h1>
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
                    <label for="name">Usuario, Nombre &oacute; E-mail: </label>
                    {!! Form::text('name', null, array('class' => 'form-control', 'placeholder' => 'Ingrese usuario, nombre, &oacute; e-mail')) !!}
                </div>
                <div class="form-group col-md-4">
                    <label for="type">Tipo de Usuario: </label>
                    {!! Form::select('role', $roles, null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group col-md-4">
                    <div class="form-group">
                        <label for="enabled">Estado: </label>
                        {!! Form::select('enabled', $conditions, null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-purple ml-1" onclick="clearForm('frmFilter')"><i class="fa fa-sync-alt" aria-hidden="true"></i>&nbsp;Limpiar</button>
                    <button type="submit" class="btn btn-lime"><i class="fa fa-search" aria-hidden="true"></i>&nbsp;Buscar</button>
                    <a href="{{ route('users.create') }}">
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
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByUsers('username', 'Usuario') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByUsers('first_name', 'Nombres') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByUsers('role', 'Tipo de Usuario') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByUsers('email', 'E-mail') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortByUsers('enabled', 'Estado') !!}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($users->total()>0)
                            @foreach($users as $user)
                                <tr data-id="{{ $user->user_id }}" data-email="{{ $user->email }}" class="odd gradeX row_{{ $user->user_id }}">
                                    <td class="text-center">
                                        <a href="{{ route('users.show', $user->user_id) }}" class="btn btn-info btn-icon btn-circle btn-sm mr-1" title="Ver">
                                            <i class="fa fa-search"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user->user_id) }}" class="btn btn-warning btn-icon btn-circle btn-sm mr-1" title="Editar">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="btn btn-danger btn-icon btn-circle btn-sm mr-1 btn-delete" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">{{ $user->username }}</td>
                                    <td class="text-center">{{ $user->first_name. ' ' . $user->last_name }}</td>
                                    <td class="text-center">{{ $user->role->display_name }}</td>
                                    <td class="text-center">{{ $user->email }}</td>
                                    <td class="text-center">{{ $user->status }}</td>
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
                @if(!is_null($users))
                    <span id="records_counter">{{$users->total()}}</span> Registro(s) encontrados
                @endif
                </div>
            </div>
            <div class="col-lg-9 text-right">
                <div class="panel-body" style="display:inline-block;padding-top: 0px;">
                @if(!is_null($users))
                    {!! str_replace('/?', '?', ($users->appends(
                    Request::only(['name', 'role', 'enabled', 'sort', 'direction']))->render()))
                    !!}
                @endif
                </div>
            </div>
        </div>
    </div>
    <!-- end panel -->

    {!! Form::open(['route' => ['users.destroy', ':USER_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
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

            var totals = {{$users->total()}}

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
                var username = row.data('email');

                swal({
                    title: 'Eliminación de Usuario',
                    text: 'Estas seguro de eliminar el usuario: ' + username + '?',
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
                        var url = form.attr('action').replace(':USER_ID', id);
                        var data = form.serialize();
                        $.post(url, data, function (result) {

                        }).fail(function (result) {
                            var message = result.responseJSON.message;
                            if(!message){
                                message = 'Ha ocurrido un error al eliminar el usuario.';
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