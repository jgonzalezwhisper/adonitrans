<?php 
if (!defined('ABSPATH')) {
    exit;
}
// Crear un shortcode para el front del panel de administracion
function func_panel_colaborador() { 
    ob_start();
    ?>
    <div id="panel-administracion" class="wrap-panel">
    	<div id="franja">
    		<div class="contimg">
    			<a href="<?php echo get_home_url(); ?>">
    				<img src="<?= URL_ADONITRANSPLUG ?>assets/images/adt-1.png" alt="<?= get_bloginfo( 'name' ) ?>">
    			</a>
    		</div>
            <div class="continfo">
                <?php if (is_user_logged_in()): 
                    $current_user = wp_get_current_user();
                    $first_name = $current_user->user_firstname;
                    $last_name  = $current_user->user_lastname;
                ?>
                    Hola, <?= $first_name.' '.$last_name ?>                   
                <?php endif ?>
                
            </div>
    	</div>
    	<aside id="lateral">
    		<ul>
                <li data-action="panel">
                    <i class="icofont-dashboard-web"></i> Panel
                </li>    
                <li data-action="recorrido">
                    <i class="icofont-map-pins"></i> Recorridos
                </li>
    			<li data-action="cuenta">
    				<i class="icofont-user-alt-3"></i> Mi Cuenta
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
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('panel_colaborador', 'func_panel_colaborador');