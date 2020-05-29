<!-- begin #sidebar -->
<div id="sidebar" class="sidebar">
    <!-- begin sidebar scrollbar -->
    <div data-scrollbar="true" data-height="100%">
        <!-- begin sidebar user -->
        <ul class="nav">
            <li class="nav-profile">
                <a href="javascript:;" data-toggle="nav-profile">
                    <div class="cover with-shadow"></div>
                    <div class="image image-icon bg-black text-grey-darker">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="info">
                        <b class="caret pull-right"></b>
                        {{ Auth::user()->first_name.' '.Auth::user()->last_name }}
                        <small>{{ Auth::user()->role->display_name }}</small>
                    </div>
                </a>
            </li>
            <li>
                <ul class="nav nav-profile">
                        <li><a href="javascript:;"><i class="fa fa-cog"></i> Configuración</a></li>
                    <li><a href="javascript:;"><i class="fa fa-pencil-alt"></i> Enviar Comentarios</a></li>
                    <li><a href="javascript:;"><i class="fa fa-question-circle"></i> Ayuda</a></li>
                </ul>
            </li>
        </ul>
        <!-- end sidebar user -->
        <!-- begin sidebar nav -->
        <ul class="nav">
            <li class="nav-header">Navegación</li>

            <!-- begin Tiendas_Option -->
            <li class="has-sub {{LinkHelper::SetActiveRoute(['dashboard', 'checklist', 'tickets'], null, true)}}">
                <a href="javascript:;">
                    <b class="caret"></b>
                    <i class="fa fa-th-large"></i>
                    <span>Tiendas</span>
                </a>
                <ul class="sub-menu">
                    <li class="has-sub {{LinkHelper::SetActiveRoute(['dashboard'], null, true)}}">
                        <a href="{{ route('dashboard') }}">
                            <span>Resumen</span>
                        </a>
                    </li>                    
                    <li class="has-sub {{LinkHelper::SetActiveRoute(['checklist'], null, true)}}">
                        <a href="{{ route('checklist.index') }}">
                            <span>Checklist</span>
                        </a>
                    </li>
                    <li class="has-sub {{LinkHelper::SetActiveRoute(['tickets'], null, true)}}">
                        <a href="{{ route('tickets.index') }}">
                            <span>Tickets</span>
                        </a>
                    </li>                   
                </ul>
            </li>
            <!-- end Tiendas_Option -->

            <!-- begin Tablas_Option -->
            <li class="has-sub {{LinkHelper::SetActiveRoute(['tables', 'checklist_structure'], null, true)}}">
                <a href="javascript:;">
                    <b class="caret"></b>
                    <i class="fa fa-th-large"></i>
                    <span>Tablas</span>
                </a> 
                <ul class="sub-menu">
                    <li class="has-sub {{LinkHelper::SetActiveRoute(['tables', 'checklist_structure'], null, true)}}">
                        <a href="{{ route('checklist_structure.index') }}">
                            <span>Estructura Checklist</span>
                        </a>
                    </li>                                                   
                </ul>
            </li>
            <!-- end Tablas_Option -->


            <!-- begin Admin_Option -->
            @if(Auth::user()->hasRole('admin'))
            <li class="has-sub {{LinkHelper::SetActiveRoute(['users', 'sessions'], null, true)}}">
                <a href="javascript:;">
                    <b class="caret"></b>
                    <i class="fa fa-th-large"></i>
                    <span>Admin</span>
                </a>
                <ul class="sub-menu">
                    <li class="has-sub {{LinkHelper::SetActiveRoute(['users'], null, true)}}">
                        <a href="javascript:;">
                            <b class="caret"></b>
                            <span>Usuarios</span>
                        </a>
                        <ul class="sub-menu">
                            <li class="{{LinkHelper::SetActiveRoute(['users'], 'create')}}">
                                <a href="{{ route('users.create') }}">Agregar Usuario</a>
                            </li>
                            <li class="{{LinkHelper::SetActiveRoute(['users'])}}">
                                <a href="{{ route('users.index') }}">Lista de Usuarios</a>
                            </li>
                        </ul>
                    </li>
                    <li class="has-sub {{LinkHelper::SetActiveRoute(['sessions'], null, true)}}">
                        <a href="javascript:;">
                            <b class="caret"></b>
                            <span>Sessiones</span>
                        </a>
                        <ul class="sub-menu">
                            <li class="{{LinkHelper::SetActiveRoute(['sessions'])}}">
                                <a href="{{ route('sessions.index') }}">Lista de Sesiones</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
            @endif
            <!-- end Admin_Option -->

            <!-- begin sidebar minify button -->
            <li><a href="javascript:;" class="sidebar-minify-btn" data-click="sidebar-minify"><i class="fa fa-angle-double-left"></i></a></li>
            <!-- end sidebar minify button -->

        </ul>
        <!-- end sidebar nav -->
    </div>
    <!-- end sidebar scrollbar -->
</div>
<div class="sidebar-bg"></div>
<!-- end #sidebar -->



