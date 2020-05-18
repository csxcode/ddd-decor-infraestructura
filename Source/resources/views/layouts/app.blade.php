<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title>@yield('title')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
    @yield('header')
</head>
<body>
<!-- begin #page-loader -->
<div id="page-loader" class="fade show"><span class="spinner"></span></div>
<!-- end #page-loader -->
<!-- begin #page-container -->
<div id="page-container" class="page-container fade page-sidebar-fixed page-header-fixed">
    @include('partials.header')
    @include('partials.sidebar')

    <!-- begin #content -->
    <div id="content" class="content">
        @yield('content')
    </div>
    <!-- end #content -->
    <!-- begin scroll to top btn -->
    <a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
    <!-- end scroll to top btn -->
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
    });
</script>
@yield('scripts')
</body>
</html>





