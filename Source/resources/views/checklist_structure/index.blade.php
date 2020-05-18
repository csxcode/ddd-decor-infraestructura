@extends('layouts.app')
@section('title', 'Estructura Checklist - '. Config::get('app.app_name'))

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
        <li class="breadcrumb-item"><a href="javascript:;">Tablas</a></li>
        <li class="breadcrumb-item active text-blue">Estructura Checklist</li>
    </ol>
    <!-- end breadcrumb -->

    <!-- begin page-header -->
    <h1 class="page-header"><i class="fa fa-check-square"></i> &nbsp;Estructura Checklist</h1>
    <!-- end page-header -->    

    <!-- begin panel -->
    <div class="panel panel-inverse">

        <div class="panel-heading">
            <h4 class="panel-title">Búsqueda</h4>
        </div>

        <div class="panel-body">        
            {!! Form::model(Request::all(), ['route' => Route::currentRouteName(), 'method' => 'GET', 'role' => 'search', 'id' => 'frmFilter']) !!}
            <div class="form-row">
                <div class="form-group col-md-7">
                    <label for="type_name">Tipo :</label>
                    {!! Form::text('type_name', null, array('class' => 'form-control', 'placeholder' => 'Ingrese nombre del tipo')) !!}
                </div>
                <div class="form-group col-md-2">
                    <label for="type_status">Estado: </label>
                    {!! Form::select('type_status', $status_list, null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-7">
                    <label for="subtype_name">Subtipo: </label>
                    {!! Form::text('subtype_name', null, array('class' => 'form-control', 'placeholder' => 'Ingrese nombre del subtipo')) !!}
                </div>
                <div class="form-group col-md-2">
                    <label for="subtype_status">Estado: </label>
                    {!! Form::select('subtype_status', $status_list, null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-7">
                    <label for="item_name">Item: </label>
                    {!! Form::text('item_name', null, array('class' => 'form-control', 'placeholder' => 'Ingrese nombre del item')) !!}
                </div>
                <div class="form-group col-md-2">
                    <label for="item_status">Estado: </label>
                    {!! Form::select('item_status', $status_list, null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 text-left">
                    <span class="label label-success" style="font-size: 80% !important;">&nbsp;&nbsp;</span>&nbsp; Tipo
                    <span class="label label-warning" style="font-size: 80% !important; margin-left:10px;">&nbsp;&nbsp;</span>&nbsp; Subtipo
                    <span class="label label-default" style="font-size: 80% !important; border: solid 1px #6d6a6a; margin-left:10px;">&nbsp;&nbsp;</span>&nbsp; Item
                </div>

                <div class="col-md-8 text-right">
                    <button type="button" class="btn btn-purple ml-1" onclick="clearForm('frmFilter')"><i class="fa fa-sync-alt" aria-hidden="true"></i>&nbsp;Limpiar</button>

                    @if ($can_create_edit_delete)
                        <a onclick="ShowTypeModal(0, false);">
                            <button type="button" class="btn btn-warning ml-1">
                                <i class="fa fa-plus-circle" aria-hidden="true"></i>&nbsp;Nuevo Tipo
                            </button>
                        </a>
                    @endif                  
                    <button type="button" onclick="ExportData()" id="btnExport" data-export-url="{{ route('checklist_structure.export') }}" class="btn btn-lime"><i class="fa fa-download" aria-hidden="true"></i>&nbsp;Exportar</button>
                    <button type="button" onclick="SearchData()" id="btnSearch" data-request-url="{{ route('checklist_structure.grid.partial') }}" class="btn btn-lime"><i class="fa fa-search" aria-hidden="true"></i>&nbsp;Buscar</button>
                </div>             
        
            </div>
            {!! Form::close() !!}        
        </div>

        <div id="TableDataContainer">
            @include('checklist_structure.partials.grid')
        </div>      
        
    </div>
    <!-- end panel -->   

    {{-- Modals --}}
    <div id="container_modal_type"></div>
    <div id="container_modal_subtype"></div>
    <div id="container_modal_item"></div>
    
    {{-- Delete Forms --}}
    {!! Form::open(['route' => ['checklist_structure.type.delete', ':PARAM_ID'], 'method' => 'DELETE', 'id' => 'modal_type_form_delete']) !!}
    {!! Form::close() !!}
   
    {!! Form::open(['route' => ['checklist_structure.subtype.delete', ':PARAM_ID'], 'method' => 'DELETE', 'id' => 'modal_subtype_form_delete']) !!}
    {!! Form::close() !!}

    {!! Form::open(['route' => ['checklist_structure.item.delete', ':PARAM_ID'], 'method' => 'DELETE', 'id' => 'modal_item_form_delete']) !!}
    {!! Form::close() !!}         

@endsection

@section('scripts')
    <script src="{{ asset('/assets/plugins/bootstrap-validator/validator.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/media/js/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/bootstrap-sweetalert/sweetalert.min.js') }}"></script>    
    <script src="{{ asset('/js/checklist_structure.js') }}"></script>

    <script type="text/javascript">

        var route_show_type = "{{ route('checklist_structure.type.show', ['PARAM_ID', 'PARAM_ACTION']) }}";
        var route_show_subtype = "{{ route('checklist_structure.subtype.show', ['PARAM_ID', 'TYPE_ID', 'PARAM_ACTION']) }}";
        var route_show_item = "{{ route('checklist_structure.item.show', ['PARAM_ID', 'SUBTYPE_ID', 'PARAM_ACTION']) }}";
        
        var prefix_modal_type = "modal_type";
        var prefix_modal_subtype = "modal_subtype";
        var prefix_modal_item = "modal_item";      

        $(document).ready(function(){          
            window.setTimeout(function(){
                $('.alert-success').fadeOut('slow');
            }, 3000);                     

            $('body').on('shown.bs.modal', '#modal_type', function () {                
                $('#modal_type_name').focus();
            })                                
        });
    </script>
@endsection