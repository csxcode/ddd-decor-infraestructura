@extends('layouts.app')
@section('title', 'Ver Usuario - '. Config::get('app.app_name'))

@section('content')
    <!-- begin breadcrumb -->
    <ol class="breadcrumb pull-right">
        <li class="breadcrumb-item"><a href="javascript:;">{{ Config::get('app.app_name') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Admin</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Usuario</a></li>
        <li class="breadcrumb-item active text-blue">Ver</li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fas fa-user"></i> &nbsp;Ver Usuario</h1>
    <!-- end page-header -->

    @include('users.partials.fields', ['type' => 'show'])

    <!-- begin FOOTER BUTTONS -->
    <a href="{{LinkHelper::GetUrlPrevious()}}" class="btn btn-purple m-r-5"><i class="fa fa-arrow-left"></i> &nbsp;Regresar</a>
    <!-- end FOOTER BUTTONS -->
@endsection

@section('scripts')
    <script src="{{ asset('/assets/plugins/bootstrap-validator/validator.min.js') }}"></script>
@endsection




