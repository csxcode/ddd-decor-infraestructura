@php
    $substr_name_len = 90;        
@endphp

<div class="row">
    <div class="col-lg-12">        
        <table id="data-table-search" class="table table-striped table-bordered" style="margin-bottom: 0px">
            <thead>
                <tr>                                                  
                    <th class="text-nowrap text-center">Tipo / Subtipo / Item</th>                           
                    <th class="text-nowrap text-center">Conforme</th>     
                </tr>
            </thead>

            <tbody>
            @if(count($list)>0)
                @foreach($list as $type)                                                                                          
                    <tr class="odd gradeX">
                        <td>                                        
                            <span class="label label-success" style="font-size: 98% !important;" data-toggle="collapse" title="{{$type->name}}">
                                <i class="fas fa-angle-right"></i>&nbsp;{{$type->name}}
                            </span>
                        </td> 
                        <td></td>
                    </tr>

                    @foreach($type->sub_types as $sub_types)                                                                            
                        <tr class="odd gradeX">
                            <td>
                                <span class="label label-warning" style="font-size: 98% !important; margin-left:30px;" title="{{$sub_types->name}}">                                                                               
                                    <i class="fas fa-angle-right"></i>&nbsp;{{$sub_types->name}}
                                </span>
                            </td>   
                            <td></td>
                        </tr>

                        @foreach($sub_types->items as $item)                              
                            @php
                                $json_data = json_encode($item->toArray())
                            @endphp
                            <tr class="odd gradeX">
                                <td>
                                    <a href="javascript:void(0)" title="{{$item->description}}" class="btn btn-info btn-icon btn-circle btn-xs" style="margin-left:60px; margin-right:5px;">
                                        <i class="fa fa-info"></i>
                                    </a>

                                    <span class="label label-default" onclick="OpenModalItem({{$json_data}})" style="font-size: 98% !important; border: solid 1px #6d6a6a; cursor:pointer" title="Clic para mas detalles">                                               
                                        {{$item->name}}
                                    </span>
                                </td>         
                                <td class="text-center">
                                    @if($item->disagreement == 1)
                                        <i class="fas fa-thumbs-down fa-lg text-danger"></i>
                                    @else
                                        <i class="fas fa-thumbs-up fa-lg text-primary"></i>
                                    @endif                                
                                </td>
                            </tr>
                        @endforeach  

                    @endforeach
                                                                                                                            
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center height-100">No hay datos.</td>
                </tr>
            @endif
        </table>        
    </div>
</div>