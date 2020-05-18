<!-- begin MAIN_INFO panel -->
<div class="panel panel-inverse">

    <div class="panel-heading">
        <h4 class="panel-title">Informacion Principal</h4>
    </div>

    <div class="panel-body">

        <div class="form-row">
            <div class="form-group col-md-4">
                {!! Form::label('username', 'Nombre de Usuario (*):') !!}
                @if($type == 'edit')
                    {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'Nombre de Usuario', 'data-error' => 'Nombre de Usuario no es v&aacute;lido', 'autocomplete' => 'off', 'required']) !!}
                    <div class="help-block with-errors"></div>
                @else
                    {!! Form::text('username', $user->username, ['class' => 'form-control', 'disabled']) !!}
                @endif
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                {!! Form::label('email', 'Email (*):') !!}
                @if($type == 'edit')
                    {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => 'Email', 'data-error' => 'Ese email no es v&aacute;lido', 'required']) !!}
                    <div class="help-block with-errors"></div>
                @else
                    {!! Form::email('email', $user->email, ['class' => 'form-control', 'disabled']) !!}
                @endif
            </div>

            <div class="form-group col-md-4">
                {!! Form::label('first_name', 'Nombres: (*)', ['class' => 'control-label']) !!}
                @if($type == 'edit')
                    {!! Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => 'Nombres', 'required']) !!}
                @else
                    {!! Form::text('email', $user->first_name, ['class' => 'form-control', 'disabled']) !!}
                @endif
            </div>

            <div class="form-group col-md-4">
                {!! Form::label('last_name', 'Apellidos (*):', ['class' => 'control-label']) !!}
                @if($type == 'edit')
                    {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => 'Apellidos', 'required']) !!}
                @else
                    {!! Form::text('last_name', $user->last_name, ['class' => 'form-control', 'disabled']) !!}
                @endif
            </div>

    </div>

        <div class="form-row">
            <div class="form-group col-md-5">
                {!! Form::label('type', 'Tipo de Usuario (*) :', ['class' => 'control-label']) !!}
                @if($type == 'edit')
                    {!! Form::select('role_id', $roles, $user->role_id, ['class' => 'form-control', 'id' => 'role_id']) !!}
                @else
                    {!! Form::select('role_id', $roles, $user->role_id, ['class' => 'form-control',  'id' => 'role_id', 'disabled']) !!}
                @endif
            </div>
        </div>

        @if($type != 'show')
            <div class="form-row">
                <div class="form-group col-md-6">
                    {!! Form::label('password', 'Contraseña (*) :', ['class' => 'control-label']) !!}
                    {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Contraseña', 'data-minlength' => 6, 'autocomplete' => 'new-password', (Route::currentRouteName() == 'users.create' ? 'required' : '')]) !!}
                    <span class="help-block">M&iacute;nimo de 6 caracteres</span>
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('password_confirmation', 'Confirmar Contraseña (*) :', ['class' => ' control-label']) !!}
                    {!! Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Confirmar Contraseña', 'data-match' => '#password', 'data-match-error' => 'Las contraseñas no coinciden', 'data-error' => 'Por favor ingrese contraseña', 'autocomplete' => 'new-password', (Route::currentRouteName() == 'users.create' ? 'required' : '')]) !!}
                    <div class="help-block with-errors"></div>
                </div>
            </div>
        @endif

        <div class="form-row">
            <div class="form-group col-md-12">
                <div class="checkbox" style="display: inline-block;">
                    <label>
                        {!! Form::checkbox('enabled', null, (isset($user) ? $user->enabled : true), [($type == 'show'?'disabled':'')] ) !!} Activo
                    </label>
                </div>

                <div class="checkbox" style="display: inline-block;margin-left: 10px;">
                    <label>
                        {!! Form::checkbox('multiple_sessions', null, (isset($user) ? $user->multiple_sessions : true), [($type == 'show'?'disabled':'')] ) !!} Múltiple dispositivos móviles
                    </label>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- end MAIN_INFO panel -->

<!-- begin MAIN_INFO panel -->
<div class="panel panel-inverse" id="section_sb_list" style="display: {{\App\Helpers\UserHelper::CheckUserTypeCanSeeStoresAndBranches($user->role_id) ? '' : 'none'}}">

    <div class="panel-heading">
        <h4 class="panel-title">Tiendas y Sucursales</h4>
    </div>

    <div class="panel-body">

        <div class="form-row">
            <div class="form-group col-md-6">

                @foreach($sb_list as $item)

                    <!-- begin #accordion -->
                    <div id="accordion_store_{{$item['store_id']}}" class="card-accordion">
                        <!-- begin card -->
                        <div class="card">
                            <div class="card-header bg-black text-white pointer-cursor" data-toggle="collapse" data-target="#collapse_store_{{$item['store_id']}}">
                                <i class="fa fa-building" style="font-size: 16px"></i>&nbsp;&nbsp;{{$item['store_name']}}
                            </div>

                            <div id="collapse_store_{{$item['store_id']}}" class="collapse show bg-silver" data-parent="#accordion_store_{{$item['store_id']}}">
                                <div class="card-body">

                                    @foreach($item['branches'] as $branch)
                                        <div class="checkbox checkbox-css">
                                            <input type="checkbox" id="tdl_chk_subcateg_{{$branch['branch_id']}}" {{$branch['selected'] ? 'checked': ''}} name="branches[]" value="{{$branch['branch_id']}}" {{($type == 'edit') ? '' : 'disabled'}}>
                                            <label for="tdl_chk_subcateg_{{$branch['branch_id']}}" class="p-l-15" style="cursor: pointer; font-weight: normal; padding-left: 25px !important;">
                                                {{$branch['branch_name']}}
                                            </label>
                                        </div>
                                    @endforeach

                                </div>
                            </div>

                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end #accordion -->
                @endforeach

            </div>
        </div>

    </div>

</div >
<!-- end MAIN_INFO panel -->














