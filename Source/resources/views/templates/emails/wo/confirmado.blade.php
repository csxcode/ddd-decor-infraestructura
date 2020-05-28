<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body style="font-size: 12px;font-family: Arial, Helvetica, sans-serif;">

<p>Estimado gestor de infraestructura:</p>
<p>La siguiente OT (orden de trabajo) ha sido reportado como “Confirmado” (visto bueno del trabajo terminado)</p>

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
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Reportado por:</td>
            <td>{{ $woh->created_by_user }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Comentario:</td>
            <td>{{ $woh->work_report }}</td>
        </tr>
    </table>

    <p>
        Como siguiente paso, usted debe verificar el trabajo del proveedor y marcar la OT como “Cerrado”.
    </p>

    <x-email.footer/>

</div>
</body>
</html>
