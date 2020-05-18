<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body style="font-size: 12px;font-family: Arial, Helvetica, sans-serif;">
<h2 style="color:#6156d8;">Nuevo Ticket <span style="color: #000000">fue creado</span></h2>

<div>
    <table cellpadding="10" cellspacing="0" border="1" style="table-layout: fixed;margin-top: 20px;margin-bottom: 20px; border: 1px solid #e09226">
        <thead style="background-color: #F59C1A;font-weight: bold;">
        <tr>
            <th colspan="2" style="text-align: left;">Detalles</th>
        </tr>
        </thead>
        <tr>
            <td style="vertical-align: top;background-color: #F59C1A;width: 140px;font-weight: bold;">Número de Ticket:</td>
            <td>{{ $data->ticket_number }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #F59C1A;width: 140px;font-weight: bold;">Tipo de Ticket:</td>
            <td>{{ $data->type_name }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #F59C1A;width: 140px;font-weight: bold;">Tienda:</td>
            <td>{{ $data->store_name }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #F59C1A;width: 140px;font-weight: bold;">Sucursal:</td>
            <td>{{ $data->branch_name }}</td>
        </tr>      
        <tr>
            <td style="vertical-align: top;background-color: #F59C1A;width: 140px;font-weight: bold;">Descripción:</td>
            <td>{!! nl2br(e($data->description)) !!}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #F59C1A;width: 140px;font-weight: bold;">Nombre del usuario que ha creado el ticket:</td>
            <td>{{ $data->created_by_user }}</td>
        </tr>
    </table>

    <h3>Decor <span style="color:#00ACAC;">{{ \Illuminate\Support\Facades\Config::get('app.app_name') }}</span></h3>

</div>

</body>
</html>