<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body style="font-size: 12px;font-family: Arial, Helvetica, sans-serif;">

<p>Estimado proveedor:</p>
<p>El trabajo completado en la siguiente OT ha sido reportado como “Inconforme”</p>

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

    <p>Para más detalles lo invitamos a ingresar al aplicativo Decor Center Infraestructura (Android Play Store) o al sitio web a través del siguiente enlace</p>
    <p><a href="https://infraestructura.decorcenter.pe" target="parent">https://infraestructura.decorcenter.pe</a></p>

    <br>
    <h3><span style="color:#b71610;">{{ \Illuminate\Support\Facades\Config::get('app.app_name') }}</span></h3>

</div>
</body>
</html>
