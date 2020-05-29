<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body style="font-size: 12px;font-family: Arial, Helvetica, sans-serif;">
<h2>Notificación <span style="color:#ff0000;">{{\Illuminate\Support\Facades\Config::get('app.app_name')}}</span></h2>

<div>
    Se ha producido un error al procesar la solicitud en el sitio web. Por favor, revise el registro de errores para más detalles:

    <table cellpadding="10" cellspacing="0" border="1" style="table-layout: fixed;margin-top: 20px;margin-bottom: 20px;">
        <thead style="background-color: #e0964c;font-weight: bold;">
            <tr>
                <th colspan="2" style="text-align: left">Detalles del Registro de Error</th>
            </tr>
        </thead>
        <tr>
            <td style="vertical-align: top;background-color: #e0964c;width: 140px;font-weight: bold;">URL:</td>
            <td>{{ $url }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #e0964c;width: 140px;font-weight: bold;">Method:</td>
            <td>{{ $method }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #e0964c;width: 140px;font-weight: bold;">Codigo de Error:</td>
            <td>{{ $e->getCode() }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #e0964c;width: 140px;font-weight: bold;">Tiempo de Error:</td>
            <td>{{ $date }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #e0964c;width: 140px;font-weight: bold;">Mensaje:</td>
            <td>{{ $e->getMessage() }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #e0964c;width: 140px;font-weight: bold;">Archivo:</td>
            <td>{{ $e->getFile() }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #e0964c;width: 140px;font-weight: bold;">Linea:</td>
            <td>{{ $e->getLine() }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #e0964c;width: 140px;font-weight: bold;">Stack Trace:</td>
            <td>{{ $e->getTraceAsString() }}</td>
        </tr>
    </table>

    <h3>Decor <span style="color:#00ACAC;">{{ \Illuminate\Support\Facades\Config::get('app.app_name') }}</span></h3>

</div>

</body>
</html>