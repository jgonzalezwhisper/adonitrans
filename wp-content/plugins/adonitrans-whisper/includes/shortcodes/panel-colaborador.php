<?php 
if (!defined('ABSPATH')) {
    exit;
}
// Crear un shortcode para el front del panel de administracion
function func_panel_colaborador() { 
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
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('panel_colaborador', 'func_panel_colaborador');