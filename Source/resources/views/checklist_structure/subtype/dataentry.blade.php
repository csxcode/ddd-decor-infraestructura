@php

    $prefix = "modal_subtype";
    $title = null;
    $icon = null;

    if($action == App\Enums\ActionEnum::CREATE){
        $title = "Nuevo " . $module_name;
        $icon = "fa-plus-circle";
    } else if($action == App\Enums\ActionEnum::EDIT){
        $title = "Actualizar " . $module_name;
        $icon = "fa-edit";
    } else if($action == App\Enums\ActionEnum::VIEW){
        $title = "Ver " . $module_name;
        $icon = "fa-eye";
    }
    
@endphp
    
<div class="modal fade" id="{{$prefix}}">    
    <form id="{{$prefix}}_form" action="{{route('checklist_structure.subtype.save', ':PARAM_ID', false)}}" class="form-horizontal" data-toggle="validator" role="form">   
        {{ csrf_field() }}

        <input type="hidden" id="{{$prefix}}_action" name="{{$prefix}}_action" value="{{$action}}" />
        <input type="hidden" id="{{$prefix}}_type_id" name="{{$prefix}}_type_id" value="{{$data->parent_id}}" />

        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-success"><i class="fas {{$icon}}"></i> &nbsp;{{ $title }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>

                @include('alerts.messages')                    

                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label($prefix.'_name', 'Nombre (*):') !!}
                        @if($action == App\Enums\ActionEnum::VIEW)
                            {!! Form::text($prefix.'_name', $data->name, ['class' => 'form-control', 'disabled']) !!}
                        @else
                            {!! Form::text($prefix.'_name', $data->name, ['class' => 'form-control', 'id' => $prefix.'_name', 'placeholder' => 'Ingrese nombre', 'data-error' => 'Nombre no es v&aacute;lido', 'required']) !!}
                            <div class="help-block with-errors"></div>
                        @endif
                    </div>   

                    <div class="form-group">
                        {!! Form::label($prefix.'_type_name', 'Tipo (*):') !!}
                        {!! Form::text($prefix.'_type_name', $type_name, ['class' => 'form-control', 'disabled']) !!}
                    </div>   

                    <div class="form-group">
                        {!! Form::label($prefix.'_display_order', 'Orden (*):') !!}
                        @if($action == App\Enums\ActionEnum::VIEW)                            
                            {!! Form::number($prefix.'_display_order', $data->display_order, ['class' => 'form-control', 'disabled']) !!}
                        @else
                            {!! Form::number($prefix.'_display_order', $data->display_order, ['class' => 'form-control col-md-2 text-center']) !!}
                            <div class="help-block with-errors"></div>
                        @endif
                    </div>   
                    
                    <div class="form-group">                            
                        <div class="form-check">
                            {!! Form::checkbox($prefix.'_status', null, (isset($data) ? $data->type_status : true), ['class' => 'form-check-input', 'id' => $prefix.'_status', ($action == App\Enums\ActionEnum::VIEW ?'disabled':'')] ) !!}
                            <label class="form-check-label" for="{{$prefix}}_status">
                                Activo
                            </label>
                        </div>
                    </div>                    
                    
                </div>

                <div class="modal-footer">
                    @if($action != App\Enums\ActionEnum::VIEW)
                        <button type="button" class="btn btn-success" onclick="SaveSubtypeModal({{$data->id}})"><i class="fa fa-save" aria-hidden="true"></i>&nbsp;Guardar</button>
                    @endif
                    <a href="javascript:;" data-dismiss="modal">
                        <button type="button" class="btn btn-purple"><i class="fa fa-window-close" aria-hidden="true"></i>&nbsp;Cerrar</button>
                    </a>
                </div>
            </div>
        </div>

    </form>

</div>    
