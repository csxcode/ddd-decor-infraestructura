@extends('layouts.app')
@section('title', 'Ver Ticket - '. Config::get('app.app_name'))


@section('header')
    <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
    <link rel="stylesheet" href="{{ asset('/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}">
    <link href="{{asset('/assets/plugins/isotope/isotope.css')}}" rel="stylesheet" />
    <link href="{{asset('/assets/plugins/lightbox/css/lightbox.css')}}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/DataTables/extensions/FixedHeader/css/fixedHeader.bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css') }}" rel="stylesheet" />
    <!-- ================== END PAGE LEVEL STYLE ================== -->
@endsection


@section('content')
    <!-- begin breadcrumb -->
    <ol class="breadcrumb pull-right">
        <li class="breadcrumb-item"><a href="javascript:;">{{ Config::get('app.app_name') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Ticket</a></li>
        <li class="breadcrumb-item active text-blue">Ver</li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fas fa-file"></i> &nbsp;Ver Ticket</h1>
    <!-- end page-header -->

    @include('alerts.messages')

    <div class="row">
        <div class="col-lg-12">

            <!-- begin nav-tabs -->
            <ul class="nav nav-tabs nav-tabs-inverse">
                <li class="nav-item"><a href="#data-tab" data-toggle="tab" class="nav-link show active"><i class="fa fa-fw fa-lg fa-file-alt"></i> <span class="d-none d-lg-inline">Datos</span></a></li>
                <li class="nav-item"><a href="#components-tab" data-toggle="tab" class="nav-link show"><i class="fa fa-fw fa-lg fa-align-justify"></i> <span class="d-none d-lg-inline">Componentes</span></a></li>
                <li class="nav-item"><a href="#photos-tab" data-toggle="tab" class="nav-link show"><i class="fa fa-fw fa-lg fa-images"></i> <span class="d-none d-lg-inline">Fotos</span></a></li>
                <li class="nav-item"><a href="#history-tab" data-toggle="tab" class="nav-link show"><i class="fa fa-fw fa-lg fa-history"></i> <span class="d-none d-lg-inline">Historia</span></a></li>
            </ul>
            <!-- end nav-tabs -->

            <!-- begin tab-content -->
            <div class="tab-content">
                <div class="tab-pane fade active show" id="data-tab">
                    @include('tickets.partials.data-tab', ['action' => $action])
                </div>

                <div class="tab-pane fade" id="components-tab">
                    @include('tickets.partials.components-tab', ['action' => $action])
                </div>

                <div class="tab-pane fade" id="photos-tab">
                    @include('tickets.partials.photos-tab')
                </div>

                <div class="tab-pane fade" id="history-tab">
                    @include('tickets.partials.history-tab')
                </div>

            </div>
            <!-- end tab-content -->

        </div>

    </div>

    <!-- begin FOOTER BUTTONS -->
    <a id="back_url" href="{{LinkHelper::GetUrlPrevious()}}" class="btn btn-purple m-r-5"><i class="fa fa-arrow-left"></i> &nbsp;Regresar</a>
    <!-- end FOOTER BUTTONS -->

@endsection

@section('scripts')

    <script src="{{ asset('/assets/plugins/DataTables/media/js/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/bootstrap-sweetalert/sweetalert.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"></script>


    <!-- ================== BEGIN PAGE LEVEL JS ================== -->
    <script src="{{asset('/assets/plugins/isotope/jquery.isotope.min.js')}}"></script>
    <script src="{{asset('/assets/plugins/lightbox/js/lightbox.min.js')}}"></script>
    <!-- ================== END PAGE LEVEL JS ================== -->

    <script>

        lightbox.option({
            'albumLabel': "Imagen %1 de %2"
        });

        $(document).ready(function(){
            $('#delivery_date').datepicker({
                todayHighlight: true,
                format: 'dd/mm/yyyy',
                autoclose: true,
                orientation: 'top'
            });
        });

        function SaveData() {

            var pass = true;
            var type_id = '{{$ticket->type_id}}';
            var status_cerrado = '{{\App\Models\Ticket\TicketStatus::TICKET_STATUS_CLOSED}}';
            var status_rechazado = '{{\App\Models\Ticket\TicketStatus::TICKET_STATUS_REJECTED}}';
            var status_cancelado = '{{\App\Models\Ticket\TicketStatus::TICKET_STATUS_CANCELED}}';
            var type_nueva_exhibicion = '{{\App\Models\Ticket\TicketType::TICKET_TYPE_NUEVA_EXHIBICION}}';
            var status = $('#status').val();
            $('#__reason').val('');

            if(type_id == type_nueva_exhibicion && status == status_cerrado){
                swal('No se puede actualizar este ticket', 'Este ticket debe ser cerrado desde el app Android, luego de haber sido registrado la nueva exhibición.', 'warning');
                pass = false;
                return false;
            }

            if(status == status_rechazado || status == status_cancelado){

                swal({
                    title: 'Ingrese Motivo',
                    content: {
                        element: "input",
                        attributes: {
                            placeholder: "Motivo..."
                        },
                    }
                }).then((value) => {

                    if (value === false || value ==="") {
                        pass = false;
                        return false;
                    }

                    $('#__reason').val(value);
                    SendSaveData();

                });

                return false;
            }

            if(pass){

                if(status == status_cerrado){

                    swal({
                        title: 'Actualizar Ticket',
                        text: '¿Está seguro de cerrar el ticket?',
                        icon: 'warning',
                        buttons: {
                            cancel: {
                                text: 'No',
                                value: null,
                                visible: true,
                                className: 'btn btn-default',
                                closeModal: true,
                            },
                            confirm: {
                                text: 'Si',
                                value: true,
                                visible: true,
                                className: 'btn btn-warning',
                                closeModal: true
                            }
                        }
                    }).then((value) => {

                        if (value) {
                            SendSaveData();
                        }

                    });

                } else {
                    SendSaveData();
                }

            }
        }

        function SendSaveData(){
            var id = '{{$ticket->id}}';
            var form = $('#form-ticket-save-data');
            var url = form.attr('action').replace(':TICKET_ID', id);
            var data = form.serialize();

            $.post(url, data).fail(function (result) {
                ShowAlertErrors(result.responseJSON.errors);
            }).done(function () {
                swal({
                    title: 'Ticket Actualizado',
                    text: 'Se actualizado correctamente este ticket',
                    icon: 'success'
                }).then((value) => {
                    var url = $('#back_url').attr('href');
                    window.location.href = url;
                });
            });
        }

    </script>
@endsection