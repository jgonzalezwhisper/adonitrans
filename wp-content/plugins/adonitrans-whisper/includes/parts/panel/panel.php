<?php 
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); 
?>
<div id="wrap-panel">
    <div class="tarjeta">
        <div class="wrap-titulo">
            <h3 class="titulo">ADONIGO</h3>
            <h4 class="subtitulo">¡Bienvenido a la forma más fácil y cómoda de moverte! 🚗💨 ¡Sube a bordo y disfruta del camino! 🚀</h4>
        </div>

        <div class="wrap wrap-acciones ">
            <div class="botones">

                <?php
                // Obtener el usuario actual
                $current_user = wp_get_current_user();
                $user_id = $current_user->ID;
                $user_role = $current_user->roles[0];

                // Array de roles permitidos
                $roles_admins = ['administrator', 'empresa', 'operaciones_1'];
                $roles_factur = ['facturacion', 'tesoreria'];
                ?>

                <div class="boton" data-action="recorrido">
                    <i class="icofont-map-pins"></i> <span>Recorridos</span>
                </div>

                <?php if ( in_array($user_role, $roles_admins) || in_array($user_role, $roles_factur) ): ?>
                    <div class="boton" data-action="pagos">
                        <i class="icofont-coins"></i> <span>Pagos</span>
                    </div>
                <?php endif ?>

                <?php if (in_array($user_role, $roles_admins)): ?>
                    <div class="boton" data-action="asignacion">
                        <i class="icofont-chart-flow"></i> <span>Asignaciones</span> 
                    </div>
                    <div class="boton" data-action="vehiculo">
                        <i class="icofont-car"></i><span>Vehículos</span> 
                    </div>
                    <div class="boton" data-action="usuario">
                        <i class="icofont-users-social"></i><span>Usuarios</span> 
                    </div>     
                    <div class="boton" data-action="empresa">
                        <i class="icofont-building-alt"></i><span>Empresas</span> 
                    </div>
                    <div class="boton" data-action="administracion">
                        <i class="icofont-architecture-alt"></i><span>Administración</span> 
                    </div>
                <?php endif ?>

                <li class="boton" data-action="cuenta">
                    <i class="icofont-user-alt-3"></i> <span>Cuenta</span> 
                </li>
                <li class="boton" data-action="logout">
                    <?php
                    $pagina_iniciar_sesion = get_field('pagina_iniciar_sesion', 'option');
                    $redirect_url = get_permalink($pagina_iniciar_sesion);
                    ?>
                    <a href="<?= wp_logout_url($redirect_url); ?>"><i class="icofont-logout"></i> <span>Cerrar sesión</span> </a>
                </li>
            </div>
        </div>
</div>