<?php 
if (!defined('ABSPATH')) {
    exit;
}
// Crear un shortcode para el front del panel de administracion
function func_panel_administracion() { 
    ob_start();
    ?>
    <div id="panel-administracion" class="wrap-panel">
    	<?php include PATH_ADONITRANSPLUG . 'includes/parts/modulos/franja-top.php'; ?>
        <?php include PATH_ADONITRANSPLUG . 'includes/parts/modulos/lateral-panel.php'; ?>
    	        
    	<section id="informacion">
    		<?php
                $ruta = PATH_ADONITRANSPLUG . 'includes/parts/panel/panel.php';
                include $ruta;
            ?>

    	</section>  

        <!--menu usuario flotante--->
        <div class="dropdown-menu-user" id="menu">
            <div class="names">
                <h3>
                    <?php if (is_user_logged_in()): 
                    $current_user = wp_get_current_user();
                    $first_name = $current_user->user_firstname;
                    $last_name  = $current_user->user_lastname;
                    $roles = $current_user->roles; // Obtiene los roles del usuario actual
                    $role_display = ucfirst($roles[0]); // Toma el primer rol y lo convierte a mayúscula inicial
                ?>
                    <?= $first_name . ' ' . $last_name ?>
                    <span><?= $role_display ?></span>
                <?php endif; ?>

                </h3>
            </div>
            <div class="options">
                <ul>
                    <li><i class="icofont-user-alt-3"></i> Perfil</li>
                    <li><i class="icofont-user-alt-2"></i> Soporte</li>
                    <li><i class="icofont-logout"></i> Cerrar Sesión</li>
                </ul>
            </div>
        </div>    

          
    </div>
    
    <?php
    return ob_get_clean();
}
add_shortcode('panel_administracion', 'func_panel_administracion');