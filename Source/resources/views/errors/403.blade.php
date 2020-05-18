<!DOCTYPE html>
<html>
<head>
    <title>Acceso Denegado - {{Config::get('app.app_name')}}</title>

    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
    <link href="{{ asset('/assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />

    <style>
        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            color: #B0BEC5;
            display: table;
            font-weight: 100;
            font-family: 'Lato';
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        .title_code {
            font-size: 72px;
            font-weight: bold;
        }

        .title {
            font-size: 60px;
            margin-bottom: 40px;
            font-weight: bold;
        }

        .subtitle{
            color: #707070;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title_code mb-0">403</div>
        <div class="title mb-1">Acceso Denegado</div>
        <div class="subtitle mt-2">Usted no tiene permisos para acceder a este recurso.</div>
        <a href="/" class="btn btn-primary mt-3">Regresar</a>
    </div>
</div>
</body>
</html>
