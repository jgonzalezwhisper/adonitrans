<?php
    // Obtener el usuario actual
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_role = $current_user->roles[0];

    // Array de roles permitidos
    $roles_admins = ['administrador', 'empresa'];
?>
<aside id="lateral">
    <ul>
        <li data-action="panel">
            <i class="icofont-dashboard-web"></i> Panel
        </li>
        <li data-action="recorrido">
            <i class="icofont-map-pins"></i> Recorridos
        </li>   

        <?php if (in_array($user_role, $roles_admins)): ?>
            <li data-action="vehiculo">
                <i class="icofont-car"></i> Vehículos
            </li>
            <li data-action="usuario">
                <i class="icofont-users-social"></i> Usuarios
            </li>        
            <li data-action="empresa">
                <i class="icofont-building-alt"></i> Empresas
            </li>
            <li data-action="administracion">
                <i class="icofont-architecture-alt"></i> Administración
            </li>
        <?php endif ?>
        
        <li data-action="cuenta">
            <i class="icofont-user-alt-3"></i> Cuenta
        </li>
        <li data-action="logout">
            <?php
            $pagina_iniciar_sesion = get_field('pagina_iniciar_sesion', 'option');
            $redirect_url = get_permalink($pagina_iniciar_sesion);
            ?>
            <a href="<?= wp_logout_url($redirect_url); ?>"><i class="icofont-logout"></i> Cerrar sesión</a>
        </li>
    </ul>
</aside>    