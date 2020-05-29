<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body style="font-size: 12px;font-family: Arial, Helvetica, sans-serif;">

<p>Estimado gestor de infraestructura:</p>
<p>El proveedor {{$vendor->name}} ha enviado su cotización para la siguiente OT (orden de trabajo)</p>

<div>
    <table cellpadding="10" cellspacing="0" border="1" style="table-layout: fixed;margin-top: 20px;margin-bottom: 20px; border: 1px solid #171c21">
        <thead style="background-color: #242A30;font-weight: bold;">
        <tr>
            <th colspan="2" style="text-align: left;color:#fff">Detalles</th>
        </tr>
        </thead>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Número OT:</td>
            <td>{{ $wo->wo_number }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Sede:</td>
            <td>
                {{ $wo->branch_location_name }}
                <br>
                {{ $wo->branch_location_address }}
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Requerimiento:</td>
            <td>{{ $wo->work_specs }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Monto Cotizado:</td>
            <td>{{ $quote->amount . ' ' . \FunctionHelper::getCurrencyName($quote->currency) }} </td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Tiempo Estimado:</td>
            <td>{{ $quote->time_days . ' dias ' . $quote->time_hours . ' horas' }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Condiciones de trabajo:</td>
            <td>{{ $quote->work_terms }}</td>
        </tr>
    </table>

    <x-email.footer/>

</div>
</body>
</html>
