<?php 
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');  
    if (!isset($_POST['action']) || empty($_POST['action'])) {
        exit('Acceso no autorizado');
    }
?>
<div id="wrap-usuarios">
    <div class="tarjeta">
        <div class="wrap-titulo">
            <h3 class="titulo">USUARIOS</h3>
            <h4 class="subtitulo">Gestiona los usuarios adscritos a la empresa</h4>
        </div>
        <p>Administra y gestiona fácilmente los usuarios registrados en tu empresa desde este panel. Mantén toda la información organizada y actualizada.</p>
    </div>

    <div class="wrap-listado-usuarios">
        <a href="#" class="button" id="crear-usuario">Crear Usuario</a>
        <table id="table-usuarios" class="display">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (is_user_logged_in()) {
                    $current_user = wp_get_current_user();
                } else {
                    echo 'No estás autenticado.';
                }

                $is_admin = in_array('administrator', $current_user->roles);

                // Obtener usuarios, excluyendo administradores si el usuario actual no es administrador
                $args = array(
                    'role__not_in' => $is_admin ? '' : ['administrator'],  // Excluir administradores si no eres administrador
                    'orderby'      => 'user_login',
                    'order'        => 'ASC',
                );
                $users = get_users($args);

                

                foreach ($users as $user) :
                    // Excluir al usuario actual
                    if ($user->ID === $current_user->ID) {
                        continue;
                    }

                    // Obtener el nombre y apellido del usuario
                    $first_name = get_user_meta($user->ID, 'first_name', true);
                    $last_name = get_user_meta($user->ID, 'last_name', true);

                    // Obtener el rol principal del usuario
                    $roles = $user->roles;
                    $primary_role = !empty($roles) ? ucwords(str_replace('_', ' ', $roles[0])) : 'Sin rol';
                    $estado_usuario = get_field('estado_usuario', 'user_'. $user->ID);
                ?>
                    <tr>
                        <td><?= $user->ID; ?></td>
                        <td><?= $first_name . ' ' . $last_name; ?></td>
                        <td><?= $user->user_email; ?></td>
                        <td><?= $primary_role; ?></td>
                        <td class="<?= $estado_usuario; ?>"><?= $estado_usuario; ?></td>
                        <td>
                            <div class="acciones">
                                <button class="accion edit-user" data-userid="<?= $user->ID; ?>">Editar</button>
                                <button class="accion delete-user" data-userid="<?= $user->ID; ?>">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
        $editcuenta = PATH_ADONITRANSPLUG . 'includes/parts/modulos/editar-cuenta.php';
        include $editcuenta;
    ?>
</div>