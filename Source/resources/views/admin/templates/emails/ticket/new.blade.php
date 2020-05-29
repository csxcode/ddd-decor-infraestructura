<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="utf-8">
</head>

<body style="font-size: 12px;font-family: Arial, Helvetica, sans-serif;">

    <p>Nuevo Ticket fue creado</p>

    <div>
        <table cellpadding="10" cellspacing="0" border="1"
            style="table-layout: fixed;margin-top: 20px;margin-bottom: 20px; border: 1px solid #171c21">
            <thead style="background-color: #242A30;font-weight: bold;">
                <tr>
                    <th colspan="2" style="text-align: left;color:#fff">Detalles</th>
                </tr>
            </thead>
            <tr>
                <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">
                    Número de Ticket:
                </td>
                <td>{{ $data->ticket_number }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">
                    Tipo de Ticket:
                </td>
                <td>{{ $data->type_name }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">
                    Tienda:
                </td>
                <td>{{ $data->store_name }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">
                    Sucursal:
                </td>
                <td>{{ $data->branch_name }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">
                    Descripción:
                </td>
                <td>{{ $data->description }}</td>
            </tr>

            <tr>
                <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">
                    Ubicación:
                </td>
                <td>{{ $data->location }}</td>
            </tr>

            <tr>
                <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">
                    Prioridad:
                </td>
                <td>{{ $data->priority_name }}</td>
            </tr>

            <tr>
                <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">
                    Creado por:
                </td>
                <td>{{ $data->created_by_user }}</td>
            </tr>
        </table>

        <x-email.footer />
    </div>

</body>

</html>
