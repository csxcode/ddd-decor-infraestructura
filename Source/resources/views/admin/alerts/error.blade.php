@if($errors->any())
    <div class="alert alert-warning alert-dismissible" role="alert">
        <span class="fa fa-exclamation-circle" aria-hidden="true"></span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <strong>Advertencia</strong>&nbsp;&nbsp;Verifique sus campos ya que surgieron algunos problemas:
        <ul style="padding-left: 25px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif