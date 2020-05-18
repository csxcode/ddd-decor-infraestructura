@extends('layouts.app')
@section('title', 'Ver Usuario - '. Config::get('app.app_name'))

@section('content')
    <!-- begin breadcrumb -->
    <ol class="breadcrumb pull-right">
        <li class="breadcrumb-item"><a href="javascript:;">{{ Config::get('app.app_name') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Tiendas</a></li>        
        <li class="breadcrumb-item active text-blue">Ver</li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fas fa-building"></i> &nbsp;Ver Tienda</h1>
    <!-- end page-header -->

    @include('stores.partials.fields', ['type' => 'show'])

    @include('stores.partials.modal-branch', ['type' => 'show'])

    <!-- begin FOOTER BUTTONS -->
    <a href="{{LinkHelper::GetUrlPrevious()}}" class="btn btn-purple m-r-5"><i class="fa fa-arrow-left"></i> &nbsp;Regresar</a>
    <!-- end FOOTER BUTTONS -->
@endsection

@section('scripts')
    <script src="{{ asset('/assets/plugins/bootstrap-validator/validator.min.js') }}"></script>
    <script src="{{ asset('/js/store.js') }}"></script>
@endsection




