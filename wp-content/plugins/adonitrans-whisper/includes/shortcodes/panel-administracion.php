<?php 
if (!defined('ABSPATH')) {
    exit;
}
// Crear un shortcode para el front del panel de administracion
function func_panel_administracion() { 
    ob_start();
    ?>
    <div id="panel-administracion" class="wrap-panel">
    	<div id="franja">
    		<div class="contimg intro-x">
    			<a href="<?php echo get_home_url(); ?>">
    				<img src="<?= URL_ADONITRANSPLUG ?>assets/images/adt-1.png" alt="<?= get_bloginfo( 'name' ) ?>">
    			</a>
    		</div>
            <div class="continfo">
                <div class="continfo_bread">
                    <a href="#">Escritorio</a> <span>></span> Nombre sección
                </div>
                <div class="continfo-right">
                    <div class="continfo_noti">
                        <i class="icofont-notification"></i>
                    </div>
                    <div class="continfo_user">
                        <?php if (is_user_logged_in()): 
                        $current_user = wp_get_current_user();
                        $first_name = $current_user->user_firstname;
                        $last_name  = $current_user->user_lastname;
                        ?>
                        Hola, <?= $first_name.' '.$last_name ?>                   
                        <?php endif ?>
                        <img onclick="toggleMenu()" class="img_user" src="<?= URL_ADONITRANSPLUG ?>assets/images/profile.jpg" alt="<?= get_bloginfo( 'name' ) ?>">
                    </div>
                </div>
            </div>
    	</div>
    	<aside id="lateral">
    		<ul>
                <li data-action="panel">
                    <i class="icofont-dashboard-web"></i> Panel
                </li>
    			<li data-action="empresa">
    				<i class="icofont-building-alt"></i> Empresas
    			</li>
                <li data-action="vehiculo">
                    <i class="icofont-car"></i> Vehículos
                </li>                      
                <li data-action="usuario">
                    <i class="icofont-users-social"></i> Usuarios
                </li>        
                <li data-action="administracion">
                    <i class="icofont-architecture-alt"></i> Administración
                </li>
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