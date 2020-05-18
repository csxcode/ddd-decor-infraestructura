@extends('layouts.app')
@section('title', 'Crear Usuario - '. Config::get('app.app_name'))

@section('content')
    <!-- begin breadcrumb -->
    <ol class="breadcrumb pull-right">
        <li class="breadcrumb-item"><a href="javascript:;">{{ Config::get('app.app_name') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Admin</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Usuario</a></li>
        <li class="breadcrumb-item active text-blue">Crear</li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fas fa-user"></i> &nbsp;Crear Usuario</h1>
    <!-- end page-header -->

    @include('alerts.error')

    {!! Form::open(['route' => 'users.store', 'class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form', 'autocomplete' => 'off']) !!}

    @include('users.partials.fields', ['type' => 'edit'])

    <!-- begin FOOTER BUTTONS -->
    <button type="submit" class="btn btn-lime"><i class="fa fa-save" aria-hidden="true"></i>&nbsp;Guardar</button>
    <a href="{{LinkHelper::GetUrlPrevious()}}" class="btn btn-purple m-r-5"><i class="fa fa-arrow-left"></i> &nbsp;Regresar</a>
    <!-- end FOOTER BUTTONS -->
    {!! Form::close() !!}

    <input type="hidden" id="uti_a_sb" value="{{$user_type_allowed_sb}}" />

@endsection

@section('scripts')
    <script src="{{ asset('/assets/plugins/bootstrap-validator/validator.min.js') }}"></script>

    <script>
        $(function(){
            // disable autocomplete
            setTimeout(function(){
                $('[autocomplete=off]').val('');
            }, 5000);

            LoadAndSetEventsUtilsForStoreAndBranches();
        });
    </script>
@endsection





