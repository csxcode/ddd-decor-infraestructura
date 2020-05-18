<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tickets</title>
    <style type="text/css">
        .header_datos {
            background-color: #d8e4bc;
        }

        .header_datos_sub {
            background-color: #ebf1de;
        }

        .header_comp {
            background-color: #e6b8b7;
        }

        .header_comp_sub {
            background-color: #f2dcdb;
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
                <th colspan="5" valign="middle" align="center" class="header_comp"><strong>Componentes</strong></th>
                <th colspan="9" valign="middle" align="center" class="header_history"><strong>Historia</strong></th>
            </tr>
            <tr>
                <th class="header_datos_sub"><strong>Núm. Ticket</strong></th>
                <th class="header_datos_sub"><strong>Fecha</strong></th>
                <th class="header_datos_sub"><strong>Tipo</strong></th>
                <th class="header_datos_sub"><strong>Estado</strong></th>
                <th class="header_datos_sub"><strong>Motivo</strong></th>
                <th class="header_datos_sub"><strong>Exhibición asociada</strong></th>
                <th class="header_datos_sub"><strong>Tienda</strong></th>
                <th class="header_datos_sub"><strong>Sucursal</strong></th>
                <th class="header_datos_sub"><strong>Descripción</strong></th>
                <th class="header_datos_sub"><strong>Fecha de Entrega</strong></th>

                <th class="header_comp_sub"><strong>Acción</strong></th>
                <th class="header_comp_sub"><strong>Tipo</strong></th>
                <th class="header_comp_sub"><strong>Codigo</strong></th>
                <th class="header_comp_sub"><strong>Nombre</strong></th>
                <th class="header_comp_sub"><strong>Cantidad</strong></th>

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
                    <td align="center">{{$item['ticket_number']}}</td>
                    <td align="center">{{DatetimeHelper::GetDateTimeByTimeZone($item['created_at'], null, 'd/m/y h:i a')}}</td>
                    <td>{{$item['ticket_type_name']}}</td>
                    <td align="center">{{$item['status_name']}}</td>
                    <td>{{$item['subtype_name']}}</td>
                    <td>{{$item['exhibition_name']}}</td>
                    <td>{{$item['store_name']}}</td>
                    <td>{{$item['branch_name']}}</td>
                    <td>{{$item['description']}}</td>
                    <td align="center">{{DatetimeHelper::GetDateTimeByTimeZone($item['delivery_date'], null, 'd/m/y')}}</td>

                    <td>{{$item['action_name']}}</td>
                    <td align="center">{{$item['type_name']}}</td>
                    <td>{{$item['code']}}</td>
                    <td>{{$item['name']}}</td>
                    <td>{{$item['quantity']}}</td>

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
