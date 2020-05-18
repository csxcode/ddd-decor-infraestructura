@extends('layouts.app')
@section('title', 'Ver Sesión - '. Config::get('app.app_name'))

@section('content')
    <!-- begin breadcrumb -->
    <ol class="breadcrumb pull-right">
        <li class="breadcrumb-item"><a href="javascript:;">{{ Config::get('app.app_name') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Admin</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Sesión</a></li>
        <li class="breadcrumb-item active text-blue">Ver</li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fas fa-user"></i> &nbsp;Ver Sesión</h1>
    <!-- end page-header -->

    <!-- begin MAIN_INFO panel -->
    <div class="panel panel-inverse">

        <div class="panel-heading">
            <h4 class="panel-title">Informacion Principal</h4>
        </div>

        <div class="panel-body">

            <div class="form-row">
                <div class="form-group col-md-4">
                    {!! Form::label('fullname_user', 'Nombre Completo (Usuario):', ['class' => 'control-label']) !!}
                    {!! Form::text('fullname_user', $data->fullname_user, ['class' => 'form-control', 'disabled']) !!}
                </div>

                <div class="form-group col-md-4">
                    {!! Form::label('email', 'Email:', ['class' => 'control-label']) !!}
                    {!! Form::email('email', $data->email, ['class' => 'form-control', 'disabled']) !!}
                </div>

                <div class="form-group col-md-4">
                    {!! Form::label('ip_address', 'Dirección Ip:', ['class' => 'control-label']) !!}
                    {!! Form::text('ip_address', $data->ip_address, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    {!! Form::label('user_agent', 'User Agent:', ['class' => 'control-label']) !!}
                    {!! Form::textarea('user_agent', $data->user_agent, ['class' => 'form-control', 'rows' => '4', 'disabled']) !!}
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    {!! Form::label('access_type', 'Tipo de Acceso:', ['class' => 'control-label']) !!}
                    {!! Form::text('access_type', \App\Models\Session::getAccessTypeName($data->access_type), ['class' => 'form-control', 'disabled']) !!}
                </div>

                <div class="form-group col-md-4">
                    {!! Form::label('login', 'Inicio de Sesión:', ['class' => 'control-label']) !!}
                    {!! Form::text('login', DatetimeHelper::GetDateTimeByTimeZone($data->login, null, 'd/m/y h:i a'), ['class' => 'form-control', 'disabled']) !!}
                </div>

                <div class="form-group col-md-4">
                    {!! Form::label('last_activity', 'Ultima Actividad:', ['class' => 'control-label']) !!}
                    {!! Form::text('last_activity', ($data->last_activity == null ? '' :  DatetimeHelper::GetDateTimeByTimeZone($data->last_activity, null, 'd/m/y h:i a')), ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    {!! Form::label('token', 'Token:', ['class' => ' control-label']) !!}
                    {!! Form::textarea('token', $data->token, ['class' => 'form-control', 'rows' => '2', 'disabled']) !!}
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    {!! Form::label('logout', 'Cierre de Sesión:', ['class' => 'control-label']) !!}
                    {!! Form::text('logout', ($data->logout == null ? '' :  DatetimeHelper::GetDateTimeByTimeZone($data->logout, null, 'd/m/y h:i a')), ['class' => 'form-control', 'disabled']) !!}
                </div>

                <div class="form-group col-md-4">
                    {!! Form::label('status', 'Estado:', ['class' => 'control-label']) !!}
                    {!! Form::text('status', \App\Models\Session::getStatusName($data->status), ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

        </div>
    </div>
    <!-- end MAIN_INFO panel -->


    <!-- begin FOOTER BUTTONS -->
    <a href="{{LinkHelper::GetUrlPrevious()}}" class="btn btn-purple m-r-5"><i class="fa fa-arrow-left"></i> &nbsp;Regresar</a>
    <!-- end FOOTER BUTTONS -->
@endsection



