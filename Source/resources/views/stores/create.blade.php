@extends('layouts.app')
@section('title', 'Crear Tienda - '. Config::get('app.app_name'))

@section('content')
    <!-- begin breadcrumb -->
    <ol class="breadcrumb pull-right">
        <li class="breadcrumb-item"><a href="javascript:;">{{ Config::get('app.app_name') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Tiendas</a></li>        
        <li class="breadcrumb-item active text-blue">Crear</li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fas fa-building"></i> &nbsp;Crear Tienda</h1>
    <!-- end page-header -->

    @include('alerts.messages')

    {!! Form::open(['route' => 'stores.store', 'class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form', 'id' => 'form-data']) !!}

    @include('stores.partials.fields', ['type' => 'edit'])

    <!-- begin FOOTER BUTTONS -->
    <button type="submit" class="btn btn-lime" id="btnSave"><i class="fa fa-save" aria-hidden="true"></i>&nbsp;Guardar</button>
    <a href="{{LinkHelper::GetUrlPrevious()}}" class="btn btn-purple m-r-5"><i class="fa fa-arrow-left"></i> &nbsp;Regresar</a>
    <!-- end FOOTER BUTTONS -->

    <input type="hidden" id="__store_index_url" name="__store_index_url" value="{{route('stores.index')}}"/>
    <input type="hidden" id="__was_clicked_btn_add_branch" name="__was_saved" value="0"/>
    <input type="hidden" id="__was_saved" name="__was_saved" value="0"/>
    <input type="hidden" id="__store_id" name="__store_id" value="0"/>

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

            UtilsForAjaxMessages();

            $("#form-data").validator().on("submit", function (event) {
                if (event.isDefaultPrevented()) {
                    // handle the invalid form...
                } else {
                    // everything looks good!
                    event.preventDefault();
                    submitForm();
                }
            });

        });

        function AddNewBranchCustom() {

            // close alerts that was shown before
            CloseAllAlters();

            if ($('#__was_saved').val() == 1) {
                AddNewBranch();
            } else {
                $('#__was_clicked_btn_add_branch').val(1);
                $("#form-data").submit();
            }
        }

        function submitForm(){
            var form = $('#form-data');
            var url = form.attr('action');
            var data = form.serialize();

            $.post(url, data).fail(function (result) {

                ShowAlertErrors(result.responseJSON.errors, null);

            }).done(function (result) {

                $('#__store_id').val(result.store_id);

                if($('#__was_clicked_btn_add_branch').val() == 1){
                    $('#modal-branch').modal('show');
                }else{
                    ShowAlertSuccess(result.success, null);

                    setTimeout(function () {
                        window.location.href = $('#__store_index_url').val();
                    }, 500);
                }

                $('#__was_clicked_btn_add_branch').val(0);
                $('#__was_saved').val(1);
            });
        }
    </script>
@endsection





