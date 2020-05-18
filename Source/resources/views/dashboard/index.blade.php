@extends('layouts.app')
@section('title', 'Resumen - '. Config::get('app.app_name'))

@section('header')
    <style>
        .table thead th, .table>thead>tr>th {
            color: #242a30;
            font-weight: 600;
            border-bottom: 1px solid #b6c2c9!important;
            background-color: #ffffff;
        }
        .counter-card-dashboard{
            font-size: 25px;
        }
        .label-card-dashboard{
            font-size: 100%;
            padding: 7px 13px;
        }
        .hyperlink{
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .hyperlink:hover
        {
            color: inherit;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
@endsection

@section('content')
    <!-- begin breadcrumb -->
    <ol class="breadcrumb pull-right">
        <li class="breadcrumb-item"><a href="javascript:;">{{ Config::get('app.app_name') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Tiendas</a></li>
        <li class="breadcrumb-item active text-blue">Resumen</li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fas fa-home"></i> &nbsp;Bienvenido <small>{{ Auth::user()->first_name.' '.Auth::user()->last_name }}</small></h1>

    <!-- begin row -->
    <div class="row">

        <div class="col-lg-12">
            <!-- begin panel -->
            <div class="panel panel-inverse" data-sortable-id="index-6">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger" data-click="panel-remove"><i class="fa fa-times"></i></a>
                    </div>
                    <h4 class="panel-title">Checklist</h4>
                </div>
                <div class="panel-body p-t-0">
                    <div class="table-responsive">
                        <table class="table table-valign-middle">
                            <tbody>
                                <tr>
                                    <td>
                                        <label class="label label-primary label-card-dashboard">
                                            <a href="{{$url_checklists_new}}" class="hyperlink">Nuevos (pendiente atender)</a>
                                        </label>
                                    </td>
                                    <td class="counter-card-dashboard">
                                        <a href="{{$url_checklists_new}}" class="hyperlink">
                                            {{$data->checklists_new}}
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- end panel -->

        </div>

    </div>
    <!-- end row -->

    <!-- begin row -->
    <div class="row">

        <div class="col-lg-12">
            <!-- begin panel -->
            <div class="panel panel-inverse" data-sortable-id="index-6">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger" data-click="panel-remove"><i class="fa fa-times"></i></a>
                    </div>
                    <h4 class="panel-title">Tickets</h4>
                </div>
                <div class="panel-body p-t-0">
                    <div class="table-responsive">
                        <table class="table table-valign-middle">
                            <tbody>
                                <tr>
                                    <td>
                                        <label class="label label-warning label-card-dashboard">
                                            <a href="{{$url_tickets_new}}" class="hyperlink">Nuevos (pendiente atender)</a>
                                        </label>
                                    </td>
                                    <td class="counter-card-dashboard">
                                        <a href="{{$url_tickets_new}}" class="hyperlink">
                                            {{$data->tickets_new}}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="label label-warning label-card-dashboard">
                                            <a href="{{$url_tickets_in_process}}" class="hyperlink">En proceso</a>
                                        </label>
                                    </td>
                                    <td class="counter-card-dashboard">
                                        <a href="{{$url_tickets_in_process}}" class="hyperlink">
                                            {{$data->tickets_in_process}}
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- end panel -->

        </div>

    </div>
    <!-- end row -->  


@endsection

@section('scripts')
    <script>
    </script>
@endsection





