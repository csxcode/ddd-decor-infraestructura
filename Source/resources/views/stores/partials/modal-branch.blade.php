
<div id="container-modal-branch">

    <!-- #modal-branch -->
    <div class="modal fade" id="modal-branch">
        {!! Form::open(['route' => ['stores.ajax.save_branch', ':BRANCH_ID', ':STORE_ID'], 'class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form', 'id' => 'form-branch']) !!}

            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-success"><i class="fas fa-th-large"></i> &nbsp;Sucursal</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>

                    @include('alerts.messages')

                    <div class="modal-body">
                        <div class="form-group">
                            {!! Form::label('branch_name', 'Nombre de Sucursal (*):') !!}
                            @if($type == 'edit')
                                {!! Form::text('branch_name', $data->branch_name, ['class' => 'form-control', 'placeholder' => 'Ingrese sucursal', 'data-error' => 'Nombre de Sucursal no es v&aacute;lido', 'required']) !!}
                                <div class="help-block with-errors"></div>
                            @else
                                {!! Form::text('branch_name', $data->branch_name, ['class' => 'form-control', 'disabled']) !!}
                            @endif
                        </div>

                        <div class="form-check">
                            {!! Form::checkbox('branch_enabled', null, (isset($data) ? $data->enabled : true), ['class' => 'form-check-input', 'id' => 'branch_enabled', ($type == 'readonly'?'disabled':''), ($data->branch_enabled?'checked':'')] ) !!}
                            <label class="form-check-label" for="branch_enabled" {{$type=="edit" ? "disabled" : ""}}>Activo</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        @if($type == 'edit')
                            <button type="button" class="btn btn-success" onclick="SaveBranch({{$data->branch_id}})"><i class="fa fa-save" aria-hidden="true"></i>&nbsp;Guardar</button>
                        @endif
                        <a href="javascript:;" data-dismiss="modal">
                            <button type="button" class="btn btn-purple"><i class="fa fa-window-close" aria-hidden="true"></i>&nbsp;Cerrar</button>
                        </a>
                    </div>
                </div>
            </div>

        {!! Form::close() !!}

    </div>
    <!-- end modal -->

</div>