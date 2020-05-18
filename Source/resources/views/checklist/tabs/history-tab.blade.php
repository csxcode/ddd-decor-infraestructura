<div class="panel-body">

    <h5 class="card-title"><i class="fa fa-fw fa-lg fa-table"></i>&nbsp;&nbsp;Seguimiento de Cambios</h5>

    <div class="form-row">
        <div class="form-group col-md-6">
            {!! Form::label('created_by_user', 'Creado por:') !!}
            {!! Form::text('created_by_user', $checklist->created_by_user, ['class' => 'form-control', 'disabled']) !!}
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('created_at', 'Fecha y Hora:') !!}
            {!! Form::text('created_at', DatetimeHelper::GetDateTimeByTimeZone($checklist->created_at, null, 'd/m/y h:i a'), ['class' => 'form-control', 'disabled']) !!}
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            {!! Form::label('updated_by_user', 'Modificado por:') !!}
            {!! Form::text('updated_by_user', $checklist->updated_by_user, ['class' => 'form-control', 'disabled']) !!}
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('updated_at', 'Fecha y Hora:') !!}
            {!! Form::text('updated_at', DatetimeHelper::GetDateTimeByTimeZone($checklist->updated_at, null, 'd/m/y h:i a'), ['class' => 'form-control', 'disabled']) !!}
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            {!! Form::label('approved_by_user', 'Aprobado por:') !!}
            {!! Form::text('approved_by_user', $checklist->approved_by_user, ['class' => 'form-control', 'disabled']) !!}
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('approved_at', 'Fecha y Hora:') !!}
            {!! Form::text('approved_at', DatetimeHelper::GetDateTimeByTimeZone($checklist->approved_at, null, 'd/m/y h:i a'), ['class' => 'form-control', 'disabled']) !!}
        </div>
    </div>


    <div class="form-row">
        <div class="form-group col-md-6">
            {!! Form::label('rejected_by_user', 'Rechazado por:') !!}
            {!! Form::text('rejected_by_user', $checklist->rejected_by_user, ['class' => 'form-control', 'disabled']) !!}
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('rejected_at', 'Fecha y Hora:') !!}
            {!! Form::text('rejected_at', DatetimeHelper::GetDateTimeByTimeZone($checklist->rejected_at, null, 'd/m/y h:i a'), ['class' => 'form-control', 'disabled']) !!}
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-12">
            {!! Form::label('status_reason', 'Motivo:') !!}
            {!! Form::textarea('status_reason', $checklist->status_reason, ['class' => 'form-control', 'rows' => 6, 'disabled']) !!}
        </div>

    </div>

</div>
