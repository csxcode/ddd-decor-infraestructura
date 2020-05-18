<!-- begin MAIN_INFO panel -->
<div class="panel panel-inverse">

    <div class="panel-heading">
        <h4 class="panel-title">Informacion Principal</h4>
    </div>

    <div class="panel-body">

        <div class="form-row">
            <div class="form-group col-md-4">
                {!! Form::label('name', 'Nombre de Tienda (*):') !!}
                @if($type == 'edit')
                    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nombre de Tienda', 'data-error' => 'Nombre de Tienda no es v&aacute;lido', 'required']) !!}
                    <div class="help-block with-errors"></div>
                @else
                    {!! Form::text('name', $data->name, ['class' => 'form-control', 'disabled']) !!}
                @endif
            </div>

            <div class="form-group col-md-4 offset-md-1">
                <label>&nbsp;</label>
                <div class="form-check">
                    {!! Form::checkbox('enabled', null, (isset($data) ? $data->enabled : true), ['class' => 'form-check-input', 'id' => 'enabled', ($type == 'show'?'disabled':'')] ) !!}
                    <label class="form-check-label" for="enabled">
                        Activo
                    </label>
                </div>
            </div>

        </div>

        @if($type == 'edit')
            <div class="form-row">
                <div class="form-group col-md-12 text-right">
                    <a id="btnAddBranch" onclick="{{$data->store_id ? 'AddNewBranch()' : 'AddNewBranchCustom()'}}">
                        <button type="button" class="btn btn-success ml-1">
                            <i class="fa fa-plus-circle" aria-hidden="true"></i>&nbsp;&nbsp;Agregar Sucursal
                        </button>
                    </a>
                </div>
            </div>
        @endif


        @include('stores.partials.branches')

    </div>

</div>
















