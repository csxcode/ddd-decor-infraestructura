@php
    $disabled = '';

    if($action == \App\Enums\ActionEnum::VIEW){
        $disabled = 'disabled';
    }
@endphp

{!! Form::open(['route' => ['tickets.ajax.save_data', ':TICKET_ID'], 'class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form', 'id' => 'form-ticket-save-data']) !!}

<input type="hidden" id="__reason" name="__reason">

<div class="panel-body">

        <div class="form-row">
            <div class="form-group col-md-6">
                {!! Form::label('ticket_number', 'Núm. Ticket:') !!}
                {!! Form::text('ticket_number', $ticket->ticket_number, ['class' => 'form-control', 'disabled']) !!}
            </div>

            <div class="form-group col-md-6">
                {!! Form::label('status', 'Estado:') !!}
                {!! Form::select('status', $status, $ticket->status_id, ['class' => 'form-control', $disabled, 'id' => 'status']) !!}
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                {!! Form::label('type', 'Tipo de ticket:') !!}
                {!! Form::select('type', $types, $ticket->type_id, ['class' => 'form-control', 'disabled']) !!}
            </div>

            <div class="form-group col-md-6">
                {!! Form::label('subtype_name', 'Motivo:') !!}
                {!! Form::text('subtype_name', $ticket->subtype_name, ['class' => 'form-control', 'disabled']) !!}
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                {!! Form::label('exhibitions', 'Exhibición asociada:') !!}
                {!! Form::select('exhibitions', $exhibitions, $ticket->exhibition_id, ['class' => 'form-control', 'disabled']) !!}
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                {!! Form::label('store', 'Tienda:') !!}
                {!! Form::text('store', $ticket->store_name, ['class' => 'form-control', 'disabled']) !!}
            </div>

            <div class="form-group col-md-6">
                {!! Form::label('branch', 'Sucursal:') !!}
                {!! Form::text('branch', $ticket->branch_name, ['class' => 'form-control', 'disabled']) !!}
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-12">
                {!! Form::label('description', 'Descripción:') !!}
                {!! Form::textarea('description', $ticket->description, ['class' => 'form-control', $disabled, 'rows' => '6']) !!}
            </div>
        </div>

        <div class="form-row">
            <div class="form-group offset-9 col-md-3">
                {!! Form::label('delivery_date', 'Fecha de Entrega:') !!}
                {!! Form::text('delivery_date', DatetimeHelper::GetDateTimeByTimeZone($ticket->delivery_date, null, 'd/m/Y'), ['class' => 'form-control text-center', 'aria-describedby' => 'sizing-addon-from', 'id' => 'delivery_date', $disabled]) !!}
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-12 text-center">

                @if($action == \App\Enums\ActionEnum::EDIT)
                    @if (\App\Http\Controllers\TicketController::CheckIfUserCanEdit($ticket->status_id))
                        <button type="button" onclick="SaveData()" class="btn btn-lime"><i class="fa fa-save" aria-hidden="true"></i>&nbsp;&nbsp;Guardar</button>
                    @endif
                @endif

            </div>
        </div>

    </div>

{!! Form::close() !!}
