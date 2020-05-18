@extends('layouts.app')
@section('title', 'Sesiones - '. Config::get('app.app_name'))

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
        <li class="breadcrumb-item"><a href="javascript:;">Admin</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Sesiones</a></li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fa fa-users"></i> &nbsp;Sesiones</h1>
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
                    <label for="type">Tipo de Acceso: </label>
                    {!! Form::select('access_type', $access_type, null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group col-md-4">
                    <div class="form-group">
                        <label for="type">Estado: </label>
                        {!! Form::select('status', $status, null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group input-group-sm col-md-4">
                    <label for="type">Inicio de Sesión: </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon" id="sizing-addon-from">Desde:</span>
                        {!! Form::text('login_from', null, array('id' => 'login_from', 'class' => 'form-control text-center', 'placeholder' => 'Desde...', 'aria-describedby' => 'sizing-addon-from')) !!}

                        <span class="input-group-addon iga-noBorderRadius" id="sizing-addon-to">Hasta:</span>
                        {!! Form::text('login_to', null, array('id' => 'login_to', 'class' => 'form-control text-center iga-withBorderRadius', 'placeholder' => 'Hasta...', 'aria-describedby' => 'sizing-addon-from')) !!}
                    </div>
                </div>
                <div class="form-group input-group-sm col-md-4">
                    <label for="type">Ultima Actividad: </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon" id="sizing-addon-from">Desde:</span>
                        {!! Form::text('last_activity_from', null, array('id' => 'last_activity_from', 'class' => 'form-control text-center', 'placeholder' => 'Desde...', 'aria-describedby' => 'sizing-addon-from')) !!}

                        <span class="input-group-addon iga-noBorderRadius" id="sizing-addon-to">Hasta:</span>
                        {!! Form::text('last_activity_to', null, array('id' => 'last_activity_to', 'class' => 'form-control text-center iga-withBorderRadius', 'placeholder' => 'Hasta...', 'aria-describedby' => 'sizing-addon-from')) !!}
                    </div>
                </div>
                <div class="form-group input-group-sm col-md-4">
                    <label for="type">Cierre de Sesión: </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon" id="sizing-addon-from">Desde:</span>
                        {!! Form::text('logout_from', null, array('id' => 'logout_from', 'class' => 'form-control text-center', 'placeholder' => 'Desde...', 'aria-describedby' => 'sizing-addon-from')) !!}

                        <span class="input-group-addon iga-noBorderRadius" id="sizing-addon-to">Hasta:</span>
                        {!! Form::text('logout_to', null, array('id' => 'logout_to', 'class' => 'form-control text-center iga-withBorderRadius', 'placeholder' => 'Hasta...', 'aria-describedby' => 'sizing-addon-from')) !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-4">
                    <label for="name">Dirección IP: </label>
                    {!! Form::text('ip_address', null, array('class' => 'form-control', 'placeholder' => 'Ingrese Dirección Ip')) !!}
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-purple ml-1" onclick="clearForm('frmFilter')"><i class="fa fa-sync-alt" aria-hidden="true"></i>&nbsp;Limpiar</button>
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
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortBySessions('login', 'Inicio de Sesión') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortBySessions('fullname_user', 'Usuario') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortBySessions('access_type', 'Tipo de Acceso') !!}</th>
                            <th class="text-nowrap text-center">{!! LinkHelper::getLinkForSortBySessions('status', 'Estado') !!}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($data->total()>0)
                            @foreach($data as $item)
                                <tr data-id="{{ $item->session_id }}" data-username="{{ $item->fullname_user }}" class="odd gradeX row_{{ $item->id }}">
                                    <td class="text-center">
                                        <a href="{{ route('sessions.show', $item->session_id) }}" class="btn btn-info btn-icon btn-circle btn-sm mr-1" title="Ver">
                                            <i class="fa fa-search"></i>
                                        </a>
                                        @if($item->status == \App\Enums\SessionStateEnum::Abierto && $item->access_type == \App\Enums\AccessTypeEnum::Api)
                                            <a href="javascript:void(0);" class="btn btn-danger btn-icon btn-circle btn-sm mr-1 btn-eject" title="Expulsar">
                                                <i class="fa fa-ban"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ ($item->login == null ? '' :  DatetimeHelper::GetDateTimeByTimeZone($item->login, null, 'd/m/y h:i a')) }}</td>
                                    <td class="text-center">{{ $item->fullname_user }}</td>
                                    <td class="text-center">{{ \App\Models\Session::getAccessTypeName($item->access_type) }}</td>
                                    <td class="text-center" id="td_status_{{ $item->session_id }}">{{ \App\Models\Session::getStatusName($item->status) }}</td>
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
                       Request::only(['name', 'access_type', 'status', 'login_from', 'login_to', 'last_activity_from', 'last_activity_to', 'logout_from', 'logout_to', 'ip_address', 'sort', 'direction']))->render()))
                        !!}
                    @endif
                </div>
            </div>
        </div>

    </div>

    {!! Form::open(['route' => ['sessions.eject', ':SESSION_ID'], 'method' => 'PATCH', 'id' => 'form-eject']) !!}
    {!! Form::close() !!}
@endsection

@section('scripts')
    <script src="{{ asset('/assets/plugins/DataTables/media/js/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/bootstrap-sweetalert/sweetalert.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"></script>

    <script>
        $(document).ready(function(){

            //Login Dates
            $('#login_from').datepicker({
                todayHighlight: true,
                format: 'dd/mm/yyyy',
                autoclose: true,
                orientation: 'bottom'
            }).on('changeDate', function (selected) {
                var minDate = new Date(selected.date.valueOf());
                $('#login_to').datepicker('setStartDate', minDate);
            });

            $('#login_to').datepicker({
                todayHighlight: true,
                format: 'dd/mm/yyyy',
                autoclose: true,
                orientation: 'bottom'
            }).on('changeDate', function (selected) {
                var maxDate = new Date(selected.date.valueOf());
                $('#login_from').datepicker('setEndDate', maxDate);
            });

            //Last Activity Dates
            $('#last_activity_from').datepicker({
                todayHighlight: true,
                format: 'dd/mm/yyyy',
                autoclose: true,
                orientation: 'bottom'
            }).on('changeDate', function (selected) {
                var maxDate = new Date(selected.date.valueOf());
                $('#last_activity_to').datepicker('setEndDate', maxDate);
            });

            $('#last_activity_to').datepicker({
                todayHighlight: true,
                format: 'dd/mm/yyyy',
                autoclose: true,
                orientation: 'bottom'
            }).on('changeDate', function (selected) {
                var maxDate = new Date(selected.date.valueOf());
                $('#last_activity_from').datepicker('setEndDate', maxDate);
            });

            //Logout Dates
            $('#logout_from').datepicker({
                todayHighlight: true,
                format: 'dd/mm/yyyy',
                autoclose: true,
                orientation: 'bottom'
            }).on('changeDate', function (selected) {
                var maxDate = new Date(selected.date.valueOf());
                $('#logout_to').datepicker('setEndDate', maxDate);
            });

            $('#logout_to').datepicker({
                todayHighlight: true,
                format: 'dd/mm/yyyy',
                autoclose: true,
                orientation: 'bottom'
            }).on('changeDate', function (selected) {
                var maxDate = new Date(selected.date.valueOf());
                $('#logout_from').datepicker('setEndDate', maxDate);
            });

            window.setTimeout(function(){
                $('.alert-success').fadeOut('slow');
            }, 3000);

            $('.btn-eject').click(function (e) {
                e.preventDefault();
                var eject_icon = $(this);
                var row = $(this).parents('tr');
                var id = row.data('id');
                var username = row.data('username');

                swal({
                    title: 'Expulsar Usuario',
                    text: 'Estas seguro de expulsar el usuario: ' + username + ' de la sesión?',
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
                            text: 'Expulsar',
                            value: true,
                            visible: true,
                            className: 'btn btn-warning',
                            closeModal: true
                        }
                    }
                }).then((value) => {

                    if (value) {
                        var form = $('#form-eject');
                        var url = form.attr('action').replace(':SESSION_ID', id);
                        var data = form.serialize();
                        $.post(url, data, function (result) {

                        }).fail(function (result) {
                            var message = result.responseJSON.message;
                            if(!message){
                                message = 'Ha ocurrido un error al expulsar la sesión.';
                            }

                            swal("Error", message, "error");

                            //row.show();
                        }).done(function(result){
                            var message = result.message;
                            if(!message){
                                message = 'Sesión fue Expulsada.';
                            }
                            swal("", message, "success");

                            //hidden eject icon and change status name
                            eject_icon.remove();
                            $('#td_status_'+id).html('Expulsado');
                        });
                    }

                });

            });

        });
    </script>
@endsection