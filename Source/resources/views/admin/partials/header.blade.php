<!-- begin #header -->
<div id="header" class="header navbar-default">
    <!-- begin navbar-header -->
    <div class="navbar-header">
        <a href="/" class="navbar-brand"><span class="navbar-logo"></span> <b>{{ Config::get('app.app_name') }}</b></a>

        <button type="button" class="navbar-toggle" data-click="sidebar-toggled">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
    <!-- end navbar-header -->
    <!-- begin header-nav -->
    <ul class="navbar-nav navbar-right">

        <li>
            <form class="navbar-form">
                <div class="form-group">
                    <a href="{{ Config::get('app.url_download_app_android') }}">
                        <button type="button" class="btn btn-success ml-1">
                            <i class="fa cloud-download-alt" aria-hidden="true"></i>&nbsp;Descargar App
                        </button>
                    </a>
                </div>
            </form>
        </li>
        <li class="dropdown navbar-user">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <div class="image image-icon bg-black text-grey-darker">
                    <i class="fa fa-user"></i>
                </div>
                <span class="d-none d-md-inline">{{ Auth::user()->first_name.' '.Auth::user()->last_name }}</span> <b class="caret"></b>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="javascript:;" class="dropdown-item">Editar Perfil</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/auth/logout"> Cerrar Sesión </a>
            </div>
        </li>
    </ul>
    <!-- end header navigation right -->
</div>
<!-- begin #header -->

