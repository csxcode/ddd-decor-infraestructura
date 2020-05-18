@if(Session::has('flash_message'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <span class="fa fa-check" aria-hidden="true"></span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        {!! Session::get('flash_message') !!}
        <ul style="padding-left: 25px;">
            @foreach($errors->all() as $error)
                <li>- {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif