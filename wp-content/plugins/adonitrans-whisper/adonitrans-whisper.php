<?php
/**
 * Plugin Name: Adonitrans Whisper MKT
 * Description: Modulo a la medida para el flujo de funciones internas de roles y areas encargadas.
 * Version: 1.0.0
 * Author: Whisper MKT
 * Text Domain: adonitrans-whisper
 * Domain Path: /languages
 * Requires Plugins: advanced-custom-fields-pro
 */

define('PATH_ADONITRANSPLUG',plugin_dir_path(__FILE__));
define('URL_ADONITRANSPLUG',plugin_dir_url(__FILE__));

include 'includes/roles.php';
include 'includes/redirecciones.php';
include 'includes/shortcodes/login.php';
include 'includes/shortcodes/panel-administracion.php';
$ajaxPath = PATH_ADONITRANSPLUG . '/includes/ajax/';

foreach (scandir($ajaxPath) as $file) {
    // Verifica que el archivo no sea '.' o '..' y que tenga extensión .php
    if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        include $ajaxPath . $file;
    }
}

function custom_class_body($classes) {
    $classes[] = 'adonitrans-plug';
    return $classes;
}
add_filter('body_class', 'custom_class_body');

function enqueue_custom_login_scripts() {

    wp_enqueue_script('jquery');
    wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11.6.0/dist/sweetalert2.all.min.js', array(), '11.6.0', true);  
    wp_enqueue_script('adoni-general-js', URL_ADONITRANSPLUG . '/assets/js/adonitrans.js', array('jquery'), null, true); 

    wp_enqueue_style( 'adoni-general-css', URL_ADONITRANSPLUG.'/assets/css/general.css', array(), "34234" );

    if (is_page( 'iniciar-sesion' )) {
    	wp_enqueue_script('adoni-login-js', URL_ADONITRANSPLUG . '/assets/js/login-ajax.js', array('jquery'), null, true);
    	wp_localize_script('adoni-login-js', 'loginAjax', array(
	        'ajaxurl' => admin_url('admin-ajax.php')
	    ));

	    wp_enqueue_style( 'icofont-css', 'https://cdn.jsdelivr.net/npm/icofont@1.0.0/dist/icofont.min.css', array(), "34234" );
	    wp_enqueue_style( 'adoni-login-css', URL_ADONITRANSPLUG.'/assets/css/iniciar-sesion.css', array(), "34234" );
	}

	$panel_administracion = get_field('panel_administracion', 'option');
    if ( $panel_administracion ) {
    	$slug = $panel_administracion->post_name;
    	if (is_page($slug)) {
    		wp_enqueue_style( 'icofont-css', 'https://cdn.jsdelivr.net/npm/icofont@1.0.0/dist/icofont.min.css', array(), "34234" );
    		wp_enqueue_style( 'adoni-administracion-css', URL_ADONITRANSPLUG.'/assets/css/panel-administracion.css', array(), "34234" );
            wp_enqueue_style( 'adoni-empresa-css', URL_ADONITRANSPLUG.'/assets/css/panel-empresa.css', array(), "34234" );
            wp_enqueue_style( 'adoni-usuarios-css', URL_ADONITRANSPLUG.'/assets/css/panel-usuarios.css', array(), "34234" );
            wp_enqueue_style( 'adoni-vehiculos-css', URL_ADONITRANSPLUG.'/assets/css/panel-vehiculos.css', array(), "34234" );
            wp_enqueue_style( 'adoni-cuenta-css', URL_ADONITRANSPLUG.'/assets/css/panel-cuenta.css', array(), "34234" );

            wp_enqueue_script('jqueryvalidate-js', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js', array('jquery'), "234234", true);
            wp_enqueue_style( 'perfect-scrollbar', 'https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.0/css/perfect-scrollbar.min.css' );
            wp_enqueue_script( 'perfect-scrollbar', 'https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.0/dist/perfect-scrollbar.min.js', array('jquery'), null, true );
            wp_enqueue_style('datatable-css', 'https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css');
            wp_enqueue_script('datatable-js', 'https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js', array('jquery'), null, true);
            wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css');
            wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', array('jquery'), null, true);


            wp_enqueue_script('adoni-panel-js', URL_ADONITRANSPLUG . '/assets/js/panel.js', array('jquery'), null, true);
            wp_localize_script('adoni-panel-js', 'panelAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'plugin_url' => URL_ADONITRANSPLUG,
            ));                  

            wp_enqueue_script('adoni-empresas-js', URL_ADONITRANSPLUG . '/assets/js/empresas.js', array('jquery'), null, true);
            wp_localize_script('adoni-empresas-js', 'empresaAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'plugin_url' => URL_ADONITRANSPLUG,
            ));

            wp_enqueue_script('adoni-vehiculos-js', URL_ADONITRANSPLUG . '/assets/js/vehiculos.js', array('jquery'), null, true);
            wp_localize_script('adoni-vehiculos-js', 'vehiculoAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'plugin_url' => URL_ADONITRANSPLUG,
            ));

            wp_enqueue_script('adoni-usuarios-js', URL_ADONITRANSPLUG . '/assets/js/usuarios.js', array('jquery'), null, true);
            wp_localize_script('adoni-usuarios-js', 'usuarioAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'plugin_url' => URL_ADONITRANSPLUG,
            ));

            wp_enqueue_script('adoni-administracion-js', URL_ADONITRANSPLUG . '/assets/js/administracion.js', array('jquery'), null, true);
            wp_localize_script('adoni-administracion-js', 'administracionAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'plugin_url' => URL_ADONITRANSPLUG,
            ));
		}
    }   
}
add_action('wp_enqueue_scripts', 'enqueue_custom_login_scripts');



add_filter('acf/settings/save_json', function ($path) {
    $plugin_json_path = plugin_dir_path(__FILE__) . 'acf-json';

    return $plugin_json_path;
});

add_filter('acf/settings/load_json', function ($paths) {
    $paths[] = plugin_dir_path(__FILE__) . 'acf-json';

    return $paths;
});


add_action('wp_footer', 'add_custom_loader_to_footer');
function add_custom_loader_to_footer() {
    ?>
    <div id="contloader">
        <span class="loader"></span>
        <h5 class="text">Enviando datos...</h5>
    </div>
    <?php
}