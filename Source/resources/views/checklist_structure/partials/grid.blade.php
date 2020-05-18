@php
    $substr_name_len = 90;        
@endphp

<div class="row">
    <div class="col-lg-12">
        <div class="panel-body">
            <table id="data-table-search" class="table table-striped table-bordered" style="margin-bottom: 0px">
                <thead>
                    <tr>
                        <th class="text-nowrap text-center">Opciones</th>                           
                        <th class="text-nowrap text-center">Tipo / Subtipo / Item</th>     
                        <th class="text-nowrap text-center">Estado</th>    
                        <th class="text-nowrap text-center">Order</th>    
                    </tr>
                </thead>

                <tbody>
                @if(count($data)>0)
                    @foreach($data as $item)                            

                        @php
                            $class_collapse = '';
                            $group_id = '';   
                            $prefix_row_id = '';
                            
                            $href_to_collapse_subtypes = '';

                            if($item->type == \App\Enums\ChecklistEnum::STRUCTURE_CHECKLIST_T_TYPE){

                                $href_to_collapse_subtypes = 'group-type-' . $item->order;
                                $prefix_row_id = 'row-type-' . $item->id;;

                            } else if($item->type == \App\Enums\ChecklistEnum::STRUCTURE_CHECKLIST_T_SUBTYPE){
                                
                                $pos_first_point = strpos($item->order, '.');
                                $type_id = substr($item->order, 0, $pos_first_point);                                         

                                $group_id = 'group-type-' . $type_id;
                                $class_collapse = 'collapse show';
                                $prefix_row_id = 'row-subtype-' . $item->id;;
                                                                                                                                                      
                            }else if($item->type == \App\Enums\ChecklistEnum::STRUCTURE_CHECKLIST_T_ITEM){
                                
                                /*
                                $pos_first_point = strpos($item->order, '.');
                                $type_id = substr($item->order, 0, $pos_first_point);                                             

                                $pos_last_point = strrpos($item->order, '.');
                                $subtype_id = substr($item->order, ($pos_first_point + 1), (($pos_last_point - $pos_first_point) - 1));   

                                $group_id = 'group-subtype-' . $type_id . '-' . $subtype_id;
                                $class_collapse = 'collapse show';                                        
                                */

                                $pos_first_point = strpos($item->order, '.');
                                $type_id = substr($item->order, 0, $pos_first_point);                                         

                                $group_id = 'group-type-' . $type_id;
                                $class_collapse = 'collapse show';
                                $prefix_row_id = 'row-item-' . $item->id;
                            }                                                                            
                        @endphp
                        
                        <tr data-id="{{ $item->id }}" data-value="{{ $item->name }}" class="odd gradeX {{ $class_collapse }} {{$prefix_row_id}}" id="{{ $group_id }}" >

                            {{-----------------------------------------------}}
                            {{-- Options --}}
                            {{-----------------------------------------------}}
                            <td class="text-left">                                                                                   
                                
                                {{-- By Type and Role User --}}
                                @if($item->type == \App\Enums\ChecklistEnum::STRUCTURE_CHECKLIST_T_TYPE)

                                    @if (!$can_create_edit_delete)                                                                                          

                                        <a href="javascript:void(0);" onclick="ShowTypeModal({{$item->id}}, true);" class="btn btn-info btn-icon btn-circle btn-sm mr-1" title="Ver">
                                            <i class="fa fa-search"></i>
                                        </a>

                                    @else
                                                                            
                                        <a href="javascript:void(0);" onclick="ShowTypeModal({{$item->id}}, false);" class="btn btn-lime btn-icon btn-circle btn-sm mr-1" title="Editar">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="DeleteType({{$item->id}});" class="btn btn-danger btn-icon btn-circle btn-sm mr-1 btn-delete" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="ShowSubtypeModal(0, {{$item->id}}, false);" class="btn btn-grey btn-icon btn-circle btn-sm mr-1" title="New">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    
                                    @endif

                                @elseif($item->type == \App\Enums\ChecklistEnum::STRUCTURE_CHECKLIST_T_SUBTYPE)

                                    @if (!$can_create_edit_delete)                                                                                          

                                        <a href="javascript:void(0);"  onclick="ShowSubtypeModal({{$item->id}}, {{$item->parent_id}}, true);" class="btn btn-info btn-icon btn-circle btn-sm mr-1" title="Ver">
                                            <i class="fa fa-search"></i>
                                        </a>

                                    @else

                                        <a href="javascript:void(0);" onclick="ShowSubtypeModal({{$item->id}}, {{$item->parent_id}}, false);" class="btn btn-lime btn-icon btn-circle btn-sm mr-1" title="Editar">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="DeleteSubtype({{$item->id}});" class="btn btn-danger btn-icon btn-circle btn-sm mr-1 btn-delete" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="ShowItemModal(0, {{$item->id}},  false);" class="btn btn-grey btn-icon btn-circle btn-sm mr-1" title="New">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    
                                    @endif

                                @else

                                    @if (!$can_create_edit_delete)

                                        <a href="javascript:void(0);" onclick="ShowItemModal({{$item->id}}, {{$item->parent_id}}, true);" class="btn btn-info btn-icon btn-circle btn-sm mr-1" title="Ver">
                                            <i class="fa fa-search"></i>
                                        </a>

                                    @else

                                        <a href="javascript:void(0);" onclick="ShowItemModal({{$item->id}}, {{$item->parent_id}}, false);" class="btn btn-lime btn-icon btn-circle btn-sm mr-1" title="Editar">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="DeleteItem({{$item->id}});" class="btn btn-danger btn-icon btn-circle btn-sm mr-1 btn-delete" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </a>

                                    @endif                                                                                        

                                @endif
                                                                      
                            </td>                                   

                            {{-----------------------------------------------}}
                            {{-- Name --}}
                            {{-----------------------------------------------}}
                            @if($item->type == \App\Enums\ChecklistEnum::STRUCTURE_CHECKLIST_T_TYPE)
                                <td>                                        
                                    <span class="label label-success" style="font-size: 98% !important; cursor:pointer;" data-toggle="collapse" href="#{{$href_to_collapse_subtypes}}" title="{{$item->name}}">
                                        <i class="fas fa-angle-right"></i>&nbsp;
                                        {{ \App\Helpers\StringHelper::SubString($item->name, $substr_name_len) }}
                                    </span>
                                </td>                                                                                                             
                            @elseif($item->type == \App\Enums\ChecklistEnum::STRUCTURE_CHECKLIST_T_SUBTYPE)
                                <td>
                                    <span class="label label-warning" style="font-size: 98% !important; margin-left:30px;" title="{{$item->name}}">                                           
                                        
                                        <i class="fas fa-angle-right"></i>&nbsp;
                                        {{ \App\Helpers\StringHelper::SubString($item->name, $substr_name_len) }}
                                    </span>
                                </td>                                        
                            @elseif($item->type == \App\Enums\ChecklistEnum::STRUCTURE_CHECKLIST_T_ITEM)
                                <td>
                                    <span class="label label-default" style="font-size: 98% !important; border: solid 1px #6d6a6a; margin-left:60px;" title="{{$item->name}}">                                                
                                        {{ \App\Helpers\StringHelper::SubString($item->name, $substr_name_len) }}
                                    </span>
                                </td>                                                                            
                            @endif
                            
                            {{-----------------------------------------------}}
                            {{-- Status --}}
                            {{-----------------------------------------------}}
                            <td class="text-center">{{ \App\Helpers\StringHelper::GetEnabledFormat($item->status) }}</td>  
                            
                            {{-----------------------------------------------}}
                            {{-- Order --}}
                            {{-----------------------------------------------}}
                            <td class="text-center">{{ $item->display_order }}</td>  
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center height-100">No hay datos.</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 text-left">
        <div class="panel-body" style="padding-top: 0px;">
            @if(!is_null($data))
                <span id="records_counter">{{count($data)}}</span> Registro(s) encontrados
            @endif
        </div>
    </div>           
</div>