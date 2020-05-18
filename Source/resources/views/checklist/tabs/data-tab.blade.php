<div class="panel-body">

        <div class="form-row">
            <div class="form-group col-md-3">
                {!! Form::label('checklist_number', 'Núm. Checklist:') !!}
                {!! Form::text('checklist_number', $checklist->checklist_number, ['class' => 'form-control', 'disabled']) !!}
            </div>

            <div class="form-group col-md-3">
                {!! Form::label('status_name', 'Estado:') !!}
                {!! Form::text('status_name', $checklist->status_name, ['class' => 'form-control', 'disabled']) !!}
            </div>

            <div class="form-group col-md-3">
                {!! Form::label('sucursal_name', 'Tienda:') !!}
                {!! Form::text('sucursal_name', $checklist->branch_name, ['class' => 'form-control', 'disabled']) !!}
            </div>

        </div>      

        <div class="form-row mt-3">
            <div class="form-group col-md-12 text-right">

                @if (Auth::user()->hasRole(['store_manager']))

                    @if($checklist->status == App\Enums\ChecklistEnum::CHECKLIST_STATUS_NEW)
                        <button type="button" onclick="UpdateChecklistStatus({{App\Enums\ChecklistEnum::CHECKLIST_STATUS_APPROVED}})" class="btn btn-lime"><i class="fa fa-check" aria-hidden="true"></i>&nbsp;Aprobar</button>
                        <button type="button" onclick="UpdateChecklistStatus({{App\Enums\ChecklistEnum::CHECKLIST_STATUS_REJECTED}})" class="btn btn-danger"><i class="fa fa-ban" aria-hidden="true"></i>&nbsp;Rechazar</button>
                    @endif                   

                @endif

            </div>
        </div>

        @include('checklist.tabs.partials.data-tab-grid')

    </div>
