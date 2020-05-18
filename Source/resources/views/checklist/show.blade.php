@extends('layouts.app')
@section('title', 'Ver Checklist - '. Config::get('app.app_name'))


@section('header')
    <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
    <link href="{{asset('/assets/plugins/isotope/isotope.css')}}" rel="stylesheet" />
    <link href="{{asset('/assets/plugins/lightbox/css/lightbox.css')}}" rel="stylesheet" />
    <!-- ================== END PAGE LEVEL STYLE ================== --> 
@endsection


@section('content')
    <!-- begin breadcrumb -->
    <ol class="breadcrumb pull-right">
        <li class="breadcrumb-item"><a href="javascript:;">{{ Config::get('app.app_name') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Checklist</a></li>
        <li class="breadcrumb-item active text-blue">Ver</li>
    </ol>
    <!-- end breadcrumb -->
    <!-- begin page-header -->
    <h1 class="page-header"><i class="fas fa-th-list"></i> &nbsp;Ver Checklist</h1>
    <!-- end page-header -->

    <div class="row">
        <div class="col-lg-12">

            <!-- begin nav-tabs -->
            <ul class="nav nav-tabs nav-tabs-inverse">
                <li class="nav-item"><a href="#data-tab" data-toggle="tab" class="nav-link show active"><i class="fa fa-fw fa-lg fa-file-alt"></i> <span class="d-none d-lg-inline">Datos</span></a></li>
                <li class="nav-item"><a href="#history-tab" data-toggle="tab" class="nav-link show"><i class="fa fa-fw fa-lg fa-history"></i> <span class="d-none d-lg-inline">Historia</span></a></li>
            </ul>
            <!-- end nav-tabs -->

            <!-- begin tab-content -->
            <div class="tab-content">
                <div class="tab-pane fade active show" id="data-tab">
                    @include('checklist.tabs.data-tab')
                </div>

                <div class="tab-pane fade" id="history-tab">
                    @include('checklist.tabs.history-tab')
                </div>
            </div>
            <!-- end tab-content -->

        </div>

    </div>

    <input type="hidden" id="__checklist_id" name="__checklist_id" value="{{$checklist->id}}">

    <!-- begin FOOTER BUTTONS -->
    <a href="{{LinkHelper::GetUrlPrevious()}}" class="btn btn-purple m-r-5"><i class="fa fa-arrow-left"></i> &nbsp;Regresar</a>
    <!-- end FOOTER BUTTONS -->

    <div id="container-modal-item">
        @include('checklist.modals.modal-item')
    </div>

    
    
<!-- #modal-rejected -->
<div class="modal fade" id="modal_rejected">
    <form id="modal_rejected_form" class="form-horizontal" data-toggle="validator" role="form">   
            
        <div class="modal-dialog">
            <div class="modal-content">                   

                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('mrejected_status_reason', 'Ingrese la razón del rechazo:') !!}
                        {!! Form::textarea('mrejected_status_reason', null, ['class' => 'form-control', 'rows' => 6, 'placeholder' => 'Ingrese razón', 'oninvalid' => "this.setCustomValidity('Este campo es requerido')", 'required']) !!}                    
                        <div class="help-block with-errors"></div>
                    </div>                     
                </div>

                <div class="modal-footer">
                    <a href="javascript:;" data-dismiss="modal">
                        <button type="button" class="btn btn-purple"><i class="fa fa-window-close" aria-hidden="true"></i>&nbsp;Cerrar</button>                        
                    </a>
                    <button type="button" class="btn btn-success" onclick="UpdateChecklistStatusAjax(ChecklistStatus.REJECTED)"><i class="fa fa-save" aria-hidden="true"></i>&nbsp;Guardar</button>                        
                </div>
            
            </div>
        </div>
    </form>
</div>
<!-- end modal -->

@endsection

@section('scripts')

    <script src="{{ asset('/assets/plugins/bootstrap-validator/validator.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/media/js/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('/assets/plugins/bootstrap-sweetalert/sweetalert.min.js') }}"></script>

    <!-- ================== BEGIN PAGE LEVEL JS ================== -->
    <script src="{{asset('/assets/plugins/isotope/jquery.isotope.min.js')}}"></script>
    <script src="{{asset('/assets/plugins/lightbox/js/lightbox.min.js')}}"></script>
    <!-- ================== END PAGE LEVEL JS ================== -->

    <script>

        var url_photo = unescape("{{Config::get('app.web_checklist_photo_path')}}");           
        var url_video = unescape("{{Config::get('app.web_checklist_video_path')}}");                
       
        function UpdateChecklistStatus(status_id){

            var id = $('#__checklist_id').val();
            var status_name = (status_id == ChecklistStatus.APPROVED ? 'Aprobar' : 'Rechazar');

            swal({
                title: status_name + ' Checklist',
                text: 'Estas seguro de ' + status_name.toLowerCase() + ' este checklist?',
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Cancelar',
                        value: null,
                        visible: true,
                        className: 'btn btn-default',
                        closeModal: true,
                    },
                    confirm: {
                        text: 'OK',
                        value: true,
                        visible: true,
                        className: 'btn btn-warning',
                        closeModal: true
                    }
                }
            }).then((value) => {

                if(value){
                    if(status_id == ChecklistStatus.REJECTED){
                        
                        // call modal
                        var frm = $('#modal_rejected').find('form').trigger("reset");;  
                        $('#modal_rejected').modal('show');  

                    }else{
                        UpdateChecklistStatusAjax(status_id);
                    }
                }
                                                                                                         
            });                        
        }

        function UpdateChecklistStatusAjax(status_id)
        {                                 
            var id = $('#__checklist_id').val();
            var url = "{{ route('checklist.ajax.update_status') }}?id=" + id + "&status_id=" + status_id;

            var data = {
                "_token": "{{ csrf_token() }}",
                "id": id,
            };

            //--------------------------------------
            // Validations
            //--------------------------------------
            var pass_validation = false;
            
            if(status_id == ChecklistStatus.REJECTED){   
                                
                var form = $('#modal_rejected').find('form');                
                var errors = form.validator('validate').has('.has-error').length;
                
                if(errors === 0){                
                    pass_validation = true;
                    data.status_reason = $('#mrejected_status_reason').val()
                }

            } else {
                pass_validation = true;
            }

            //--------------------------------------
            // Call Ajax
            //--------------------------------------     
            if(pass_validation){                                                            
                $.post(url, data).fail(function () {
                    showErrorMessage();
                }).done(function (result) {
                    var text_to_show = (status_id === ChecklistStatus.APPROVED ? 'aprobado' : 'ha rechazado');
                    $('#data-tab').html(result.html_data_tab);
                    $('#history-tab').html(result.html_history_tab);
                    swal('Estado Actualizado', 'Se ' + text_to_show.toLowerCase() + ' el checklist', 'success');

                    if(status_id == ChecklistStatus.REJECTED){   
                        $("#modal_rejected").modal('hide').on('hidden.bs.modal', function () {
                            $('#mrejected_status_reason').val('')
                        });
                    }                    
                });
            }

        }

        function OpenModalItem(data){                                                       
            var checklist_id = $('#__checklist_id').val();
            
            $('#mi_title').prop('title', data.name);
            $('#mi_disagreement_reason').val(data.disagreement_reason);

            if(data.disagreement_generate_ticket == 1)
                $('#mi_disagreement_generate_ticket').prop('checked', true);
            else
                $('#mi_disagreement_generate_ticket').prop('checked' , false);
            
            //----------------------------
            // Photos
            //----------------------------
            if(data.photo1_guid == null && data.photo3_guid == null && data.photo3_guid == null){
                $('#mi_photo_container').hide();                
            } else {

                $('#mi_photo_container').show(); 

                // Photo 1
                if(data.photo1_guid == null) {
                    $('#mi_photo1_info').hide();

                    // remove attributes
                    $('#mi_photo1_href').removeAttr('href');
                    $('#mi_photo1_src').removeAttr('src');
                    $('#mi_photo1_src').removeAttr('title');                

                } else {
                    $('#mi_photo1_info').show(); 
                    var url_photo1 = url_photo.replace("{id}", checklist_id);
                    url_photo1 = url_photo1.replace("{guid}", data.photo1_guid);

                    $('#mi_photo1_href').prop('href', url_photo1);
                    $('#mi_photo1_src').prop('src', url_photo1);
                    $('#mi_photo1_src').prop('title', data.photo1_name);
                    $('#mi_photo1_name').text(data.photo1_name);
                    $('#mi_photo1_download_url').prop('href', $('#mi_photo1_download_url').attr('href').replace('_guid_', data.photo1_guid));                    
                }


                // Photo 2
                if(data.photo2_guid == null) {
                    $('#mi_photo2_info').hide();

                    // remove attributes
                    $('#mi_photo2_href').removeAttr('href');
                    $('#mi_photo2_src').removeAttr('src');
                    $('#mi_photo2_src').removeAttr('title');         
                } else {
                    $('#mi_photo2_info').show(); 
                    var url_photo2 = url_photo.replace("{id}", checklist_id);
                    url_photo2 = url_photo2.replace("{guid}", data.photo2_guid);

                    $('#mi_photo2_href').prop('href', url_photo2);
                    $('#mi_photo2_src').prop('src', url_photo2);
                    $('#mi_photo2_src').prop('title', data.photo2_name);
                    $('#mi_photo2_name').text(data.photo2_name);
                    $('#mi_photo2_download_url').prop('href', $('#mi_photo2_download_url').attr('href').replace('_guid_', data.photo2_guid));  
                }
            

                // Photo 3
                if(data.photo3_guid == null) {
                    $('#mi_photo3_info').hide();

                    // remove attributes
                    $('#mi_photo3_href').removeAttr('href');
                    $('#mi_photo3_src').removeAttr('src');
                    $('#mi_photo3_src').removeAttr('title'); 
                } else {
                    $('#mi_photo3_info').show(); 
                    var url_photo3 = url_photo.replace("{id}", checklist_id);
                    url_photo3 = url_photo3.replace("{guid}", data.photo3_guid);

                    $('#mi_photo3_href').prop('href', url_photo3);
                    $('#mi_photo3_src').prop('src', url_photo3);
                    $('#mi_photo3_src').prop('title', data.photo3_name);
                    $('#mi_photo3_name').text(data.photo3_name);     
                    $('#mi_photo3_download_url').prop('href', $('#mi_photo3_download_url').attr('href').replace('_guid_', data.photo3_guid));  
                }
            }
                
            //----------------------------
            // Video
            //----------------------------            
            if(data.video_guid == null){
                $('#mi_video_container').hide();                
            } else {
                $('#mi_video_container').show();

                var url_video1 = url_video.replace("{id}", checklist_id);
                url_video1 = url_video1.replace("{guid}", data.video_guid);

                $('#mi_video_src').prop('src', url_video1);
                $('#mi_video_name').text(data.video_name);
                $('#mi_video')[0].load();                

                $('#mi_video_download_url').prop('href', $('#mi_video_download_url').attr('href').replace('_guid_', data.video_guid));  
            }
            
            
            // Show modal
            $('#modal-item').modal('show');            
        }

    </script>
@endsection