<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checklists</title>
    <style type="text/css">
        .header_datos {
            background-color: #d8e4bc;
        }

        .header_datos_sub {
            background-color: #ebf1de;
        }

        .header_history {
            background-color: #b7dee8;
        }

        .header_history_sub {
            background-color: #daeef3;
        }
    </style>
</head>
<body>
    <table >
        <thead>
            <tr>
                <th colspan="10" valign="middle" align="center" class="header_datos"><strong>Datos</strong></th>
                <th colspan="9" valign="middle" align="center" class="header_history"><strong>Historia</strong></th>
            </tr>
            <tr>
                <th class="header_datos_sub"><strong>Núm. Checklist</strong></th>                
                <th class="header_datos_sub"><strong>Puntos</strong></th>      
                <th class="header_datos_sub"><strong>Estado</strong></th>                
                <th class="header_datos_sub"><strong>Tienda</strong></th>   
                             
                <th class="header_datos_sub"><strong>Tipo</strong></th>
                <th class="header_datos_sub"><strong>Subtipo</strong></th>

                <th class="header_datos_sub"><strong>Item</strong></th>
                <th class="header_datos_sub"><strong>Conforme</strong></th>
                <th class="header_datos_sub"><strong>Comentarios</strong></th>
                <th class="header_datos_sub"><strong>Generar ticket</strong></th>

                <th class="header_history_sub"><strong>Creado por</strong></th>
                <th class="header_history_sub"><strong>Creado Fecha y Hora</strong></th>
                <th class="header_history_sub"><strong>Modificado por</strong></th>
                <th class="header_history_sub"><strong>Modificado Fecha y Hora</strong></th>
                <th class="header_history_sub"><strong>Aprobado por</strong></th>
                <th class="header_history_sub"><strong>Aprobado Fecha y Hora</strong></th>
                <th class="header_history_sub"><strong>Rechazado por</strong></th>
                <th class="header_history_sub"><strong>Rechazado Fecha y Hora</strong></th>
                <th class="header_history_sub"><strong>Motivo</strong></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                <tr>
                    <td align="center">{{$item['checklist_number']}}</td>                    
                    <td align="center">{{$item['total_points']}}</td>    
                    <td align="center">{{$item['status_name']}}</td>                                        
                    <td>{{$item['branch_name']}}</td>                                     
                
                    <td>{{$item['type_name']}}</td>
                    <td>{{$item['subtype_name']}}</td>

                    <td>{{$item['item_name']}}</td>
                    <td align="center">{{$item['item_confirmed']}}</td>                    
                    <td>{{$item['item_reason']}}</td>
                    <td align="center">{{$item['item_generate_ticket']}}</td>

                    <td>{{$item['created_by_user']}}</td>
                    <td align="center">{{DatetimeHelper::GetDateTimeByTimeZone($item['created_at'], null, 'd/m/y h:i a')}}</td>
                    <td>{{$item['updated_by_user']}}</td>
                    <td align="center">{{DatetimeHelper::GetDateTimeByTimeZone($item['updated_at'], null, 'd/m/y h:i a')}}</td>
                    <td>{{$item['approved_by_user']}}</td>
                    <td align="center">{{DatetimeHelper::GetDateTimeByTimeZone($item['approved_at'], null, 'd/m/y h:i a')}}</td>
                    <td>{{$item['rejected_by_user']}}</td>
                    <td align="center">{{DatetimeHelper::GetDateTimeByTimeZone($item['rejected_at'], null, 'd/m/y h:i a')}}</td>
                    <td>{{$item['status_reason']}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>