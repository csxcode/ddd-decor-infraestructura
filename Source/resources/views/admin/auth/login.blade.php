<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
    <title>{{ Config::get('app.app_name') }} - Login</title>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />

    <!-- ================== BEGIN BASE CSS STYLE ================== -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/jquery-ui/jquery-ui.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/font-awesome/css/all.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/plugins/animate/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/css/default/style.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/css/default/style-responsive.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/css/default/theme/default.css') }}" rel="stylesheet" id="theme" />
    <!-- ================== END BASE CSS STYLE ================== -->
    <!-- ================== BEGIN BASE JS ================== -->
    <script src="{{ asset('/js/Initialization.js') }}"></script>
    <script src="{{ asset('/assets/plugins/pace/pace.min.js') }}"></script>
    <!-- ================== END BASE JS ================== -->
</head>
<body class="pace-top">
<!-- begin #page-loader -->
<div id="page-loader" class="fade show"><span class="spinner"></span></div>
<!-- end #page-loader -->
<!-- begin #page-container -->
<div id="page-container" class="fade">
    <!-- begin login -->
    <div class="login bg-black animated fadeInDown">
        <!-- begin brand -->
        <div class="login-header">
            <div class="brand">
                <span class="logo"></span> <b>{{Config::get('app.app_name')}}</b>
            </div>
            <div class="icon">
                <i class="fa fa-lock"></i>
            </div>
        </div>
        <!-- end brand -->
        <!-- begin login-content -->
        <div class="login-content">
            <form action="{{ url('/auth/login') }}" method="POST" class="margin-bottom-0" role="form">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                @if (count($errors) > 0)
                    <div class="validation-summary-errors text-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group m-b-20">
                    <input name="username" type="text" class="form-control form-control-lg inverse-mode" placeholder="Nombre de Usuario" value="{{ old('username') }}" required />
                </div>
                <div class="form-group m-b-20">
                    <input name="password" type="password" class="form-control form-control-lg inverse-mode" placeholder="Contraseña" required />
                </div>
                <div class="checkbox checkbox-css m-b-20">
                    <input type="checkbox" id="remember_checkbox" />
                    <label for="remember_checkbox">
                        Recuérdame
                    </label>
                </div>
                <div class="login-buttons">
                    <button type="submit" class="btn btn-danger btn-block btn-lg">Ingresar</button>
                </div>
            </form>
        </div>
        <!-- end login-content -->
    </div>
    <!-- end login -->
</div>
<!-- end page container -->
<!-- ================== BEGIN BASE JS ================== -->
<!--[if lt IE 9]>
<script src="{{ asset('/assets/crossbrowserjs/html5shiv.js') }}"></script>
<script src="{{ asset('/assets/crossbrowserjs/respond.min.js') }}"></script>
<script src="{{ asset('/assets/crossbrowserjs/excanvas.min.js') }}"></script>
<![endif]-->
<script src="{{ asset('/assets/plugins/jquery/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('/assets/plugins/jquery/jquery-migrate-1.1.0.min.js') }}"></script>
<script src="{{ asset('/assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('/assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('/assets/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('/assets/plugins/js-cookie/js.cookie.js') }}"></script>
<script src="{{ asset('/assets/js/apps.min.js') }}"></script>
<script src="{{ asset('/js/jquery.unobtrusive-ajax.min.js') }}"></script>
<script src="{{ asset('/js/globals.js') }}"></script>
<!-- ================== END BASE JS ================== -->
<script>
    $(document).ready(function () {
        App.init();
    });</script>
</body>
</html>
