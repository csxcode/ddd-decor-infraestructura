<div class="panel-body">

    @if($photos)

        <div id="gallery" class="gallery isotope text-center" style="position: relative; overflow: hidden; height: 650px;">

        @for ($i = 1; $i <= 6; $i++)
            @php
                $exists_photo = false;
                $position = null;
                $photo_url = \App\Models\Ticket\Ticket::GetImageURL(null, null);
                $photo_name = null;
                $enable_lightbox = null;
                $guid = null;

                if($i == 1){
                    $position = 'transform: translate3d(0px, 0px, 0px);';
                }else if ($i == 2){
                    $position = 'transform: translate3d(270px, 0px, 0px);';
                }else if ($i == 3){
                    $position = 'transform: translate3d(540px, 0px, 0px);';
                }else if ($i == 4){
                    $position = 'transform: translate3d(0px, 339px, 0px);';
                }else if ($i == 5){
                    $position = 'transform: translate3d(270px, 339px, 0px);';
                }else if ($i == 6){
                    $position = 'transform: translate3d(540px, 339px, 0px);';
                }

            @endphp

            @foreach($photos as $item)
                @if($item->order == $i)
                    @php
                        $exists_photo = true;
                        $photo_url = \App\Models\Ticket\Ticket::GetImageURL($item->ticket_id, $item->guid);
                        $photo_name = $item->name;
                        $enable_lightbox = "data-lightbox='gallery-group-1'";
                        $guid =  $item->guid;
                    @endphp
                @endif
            @endforeach

            <!-- begin image -->
                <div class="image gallery-group-1 isotope-item" style="position: absolute; left: 0px; top: 0px; {{$position}}">
                    <div class="image-inner" style="border: 1px solid #bfbdbd;">
                        <a href="{{$exists_photo ? $photo_url : 'javascript:;'}}" {{$enable_lightbox}}>
                            <img src="{{$photo_url}}" alt="">
                        </a>
                        <p class="image-caption">#{{$i}}</p>
                    </div>
                    <div class="image-info">
                        <h6 class="title text-center" style="font-size: 12px">{{$photo_name}}</h6>
                        <div class="pull-right">
                            @if($exists_photo)
                                <a href="{{ route('tickets.photos.download', [$ticket->id, $guid]) }}" class="btn btn-primary ml-1 btn-xs"><i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;Descargar</a>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- end image -->

            @endfor

        </div>

    @else

        No hay fotos relacionadas.

    @endif

</div>