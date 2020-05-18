@extends('layouts.app')
@section('title', 'Actualizar Usuario - '. Config::get('app.app_name'))

@section('header')
    <link href="{{ asset('/assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/DataTables/extensions/FixedHeader/css/fixedHeader.bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <!-- begin breadcrumb -->
    <ol class="breadcrumb pull-right">
        <li class="breadcrumb-item"><a href="javascript:;">{{ Config::get('app.app_name') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Tiendas</a></li>        
        <li class="breadcrumb-item active text-blue">Editar</li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fas fa-building"></i> &nbsp;Editar Tienda</h1>
    <!-- end page-header -->

    @include('alerts.error')
    @include('alerts.messages')

    {!! Form::model($data, ['method' => 'PATCH', 'route' => ['stores.update', $data->store_id], 'class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form']) !!}

        @include('stores.partials.fields', ['type' => 'edit'])

        <!-- begin FOOTER BUTTONS -->
        <button type="submit" class="btn btn-lime"><i class="fa fa-save" aria-hidden="true"></i>&nbsp;Guardar</button>
        <a href="{{LinkHelper::GetUrlPrevious()}}" class="btn btn-purple m-r-5"><i class="fa fa-arrow-left"></i> &nbsp;Regresar</a>
        <!-- end FOOTER BUTTONS -->

        <input type="hidden" id="__store_id" name="__store_id" value="{{$data->store_id}}"/>

    {!! Form::close() !!}

    {!! Form::open(['route' => ['stores.ajax.destroy_branch', ':BRANCH_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

    @include('stores.partials.modal-branch', ['type' => 'edit'])

@endsection

@section('scripts')
    <script src="{{ asset('/assets/plugins/bootstrap-validator/validator.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/media/js/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/bootstrap-sweetalert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('/js/store.js') }}"></script>

    <script>
        $(function(){
            window.setTimeout(function(){
                $('.alert-success').fadeOut('slow');
            }, 3000);
        });
    </script>

@endsection

