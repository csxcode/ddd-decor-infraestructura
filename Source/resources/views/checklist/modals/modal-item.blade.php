
<!-- #modal-branch -->
<div class="modal fade" id="modal-item">

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-warning"><i class="fas fa-info-circle" id="mi_title" style="cursor:pointer"></i> &nbsp;Ver Item</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>            

            <div class="modal-body">

                {{-------------------------------------------------------}}
                {{-- Fields --}}
                {{-------------------------------------------------------}}

                <div class="form-group">
                    {!! Form::label('mi_disagreement_reason', 'Comentarios:') !!}
                    {!! Form::textarea('mi_disagreement_reason', null, ['class' => 'form-control', 'rows' => 6, 'disabled']) !!}
                </div>

                <div class="form-group">
                    <div class="checkbox checkbox-css checkbox-inline">
                        <input type="checkbox" id="mi_disagreement_generate_ticket" disabled>
                        <label for="mi_disagreement_generate_ticket">Generar ticket</label>
                    </div>
                </div>   
                
                {{-------------------------------------------------------}}
                {{-- Fotos --}}
                {{-------------------------------------------------------}}
                <div id="mi_photo_container">
                    <h5 class="card-title mt-5"><i class="fa fa-fw fa-lg fa-image"></i>&nbsp;&nbsp;Fotos de referencia</h5>

                    <div class="form-group">
                        <div id="gallery" class="gallery isotope text-center" style="position: relative; overflow: hidden; height: 210px;">

                            {{-----------------------------------------------}}
                            {{-- Image 1 --}}
                            {{-----------------------------------------------}}
                            <!-- begin image -->
                            <div class="image gallery-checklist isotope-item" style="width: 30%;position: absolute; left: 0px; top: 0px; transform: translate3d(0px, 0px, 0px);">
                                <div class="image-inner" style="border: 1px solid #bfbdbd; width: 135px; height: 135px;">
                                    <a id="mi_photo1_href" data-lightbox='gallery-checklist'>
                                        <img id="mi_photo1_src" alt="" style="width: 135px; height: 135px;">
                                    </a>
                                </div>

                                <div class="image-info" id="mi_photo1_info">
                                    <h6 class="title text-center" style="font-size: 12px" id="mi_photo1_name"></h6>
                                    <div class="pull-right">
                                        <a id="mi_photo1_download_url" href="{{ route('checklist.photos.download', [$checklist->id, '_guid_']) }}" class="btn btn-primary ml-1 btn-xs"><i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;Descargar</a>
                                    </div>
                                </div>
                            </div>
                            <!-- end image -->


                            {{-----------------------------------------------}}
                            {{-- Image 2 --}}
                            {{-----------------------------------------------}}
                            <!-- begin image -->
                            <div class="image gallery-checklist isotope-item" style="width: 30%;position: absolute; left: 150px; top: 0px; transform: translate3d(0px, 0px, 0px);">
                                <div class="image-inner" style="border: 1px solid #bfbdbd; width: 135px; height: 135px;">
                                    <a id="mi_photo2_href" data-lightbox='gallery-checklist'>
                                        <img id="mi_photo2_src" alt="" style="width: 135px; height: 135px;">
                                    </a>
                                </div>
                                <div class="image-info" id="mi_photo2_info">
                                    <h6 class="title text-center" style="font-size: 12px" id="mi_photo2_name"></h6>
                                    <div class="pull-right">
                                        <a id="mi_photo2_download_url" href="{{ route('checklist.photos.download', [$checklist->id, '_guid_']) }}" class="btn btn-primary ml-1 btn-xs"><i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;Descargar</a>
                                    </div>
                                </div>
                            </div>
                            <!-- end image -->


                            {{-----------------------------------------------}}
                            {{-- Image 3 --}}
                            {{-----------------------------------------------}}
                            <!-- begin image -->
                            <div class="image gallery-checklist isotope-item" style="width: 30%;position: absolute; left: 300px; top: 0px; transform: translate3d(0px, 0px, 0px);">
                                <div class="image-inner" style="border: 1px solid #bfbdbd; width: 135px; height: 135px;">
                                    <a id="mi_photo3_href" data-lightbox='gallery-checklist'>
                                        <img id="mi_photo3_src" alt="" style="width: 135px; height: 135px;">
                                    </a>
                                </div>
                                <div class="image-info" id="mi_photo3_info">
                                    <h6 class="title text-center" style="font-size: 12px" id="mi_photo3_name"></h6>
                                    <div class="pull-right">
                                        <a id="mi_photo3_download_url" href="{{ route('checklist.photos.download', [$checklist->id, '_guid_']) }}" class="btn btn-primary ml-1 btn-xs"><i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;Descargar</a>
                                    </div>
                                </div>
                            </div>
                            <!-- end image -->
                        </div>
                    </div>   
                </div>
                
                
                {{-------------------------------------------------------}}
                {{-- Video --}}
                {{-------------------------------------------------------}}
                <div id="mi_video_container">
                    <h5 class="card-title mt-5"><i class="fa fa-fw fa-lg fa-image"></i>&nbsp;&nbsp;Video de referencia</h5>                                     

                    <div class="embed-responsive embed-responsive-16by9">
                        <video class="embed-responsive-item" id="mi_video" controls>
                            <source autostart="false" id="mi_video_src" >
                            Your browser does not support the video tag.
                        </video>
                    </div>

                    <div class="form-group">
                        <div class="image-info" style="margin-top: 15px;">
                            <h6 class="title text-center" style="font-size: 12px" id="mi_video_name"></h6>
                            <div class="text-center">
                                <a id="mi_video_download_url" href="{{ route('checklist.videos.download', [$checklist->id, '_guid_']) }}" class="btn btn-primary ml-1 btn-xs"><i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;Descargar</a>
                            </div>
                        </div>
                    </div>  
                </div> 

            </div>

            <div class="modal-footer">
                <a href="javascript:;" data-dismiss="modal">
                    <button type="button" class="btn btn-purple"><i class="fa fa-window-close" aria-hidden="true"></i>&nbsp;Cerrar</button>
                </a>
            </div>
        
        </div>
    </div>


</div>
<!-- end modal -->