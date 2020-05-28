<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body style="font-size: 12px;font-family: Arial, Helvetica, sans-serif;">

<p>Estimado responsable de sede:</p>
<p>El siguiente ticket ha sido reportado como “Confirmado”</p>

<div>
    <table cellpadding="10" cellspacing="0" border="1" style="table-layout: fixed;margin-top: 20px;margin-bottom: 20px; border: 1px solid #171c21">
        <thead style="background-color: #242A30;font-weight: bold;">
        <tr>
            <th colspan="2" style="text-align: left;color:#fff">Detalles</th>
        </tr>
        </thead>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Número Ticket:</td>
            <td>{{ $ticket->ticket_number }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Sede:</td>
            <td>
                {{ $ticket->branch_location_name }}
                <br>
                {{ $ticket->branch_location_address }}
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Descripción Ticket:</td>
            <td>{{ $ticket->description }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Confirmado por:</td>
            <td>{{ $ticket->updated_by_user }}</td>
        </tr>        
    </table>

    <p>
        La OT (Orden de Trabajo) # {{$wo_number}} ha sido generada para llevar el seguimiento del trabajo.
    </p>

    <x-email.footer/>
</div>
</body>
</html>
