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
define('PLUG_VERSION', '0.0.13');

include 'includes/roles.php';
include 'includes/redirecciones.php';
include 'includes/shortcodes/login.php';
include 'includes/shortcodes/panel-administracion.php';
include 'includes/shortcodes/panel-colaborador.php';
$ajaxPath = PATH_ADONITRANSPLUG . '/includes/ajax/';

foreach (scandir($ajaxPath) as $file) {
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

    wp_enqueue_style( 'adoni-general-css', URL_ADONITRANSPLUG.'assets/css/general.css', array(), PLUG_VERSION );

    if (is_page( 'iniciar-sesion' )) {
    	wp_enqueue_script('adoni-login-js', URL_ADONITRANSPLUG . '/assets/js/login-ajax.js', array('jquery'), null, true);
    	wp_localize_script('adoni-login-js', 'loginAjax', array(
	        'ajaxurl' => admin_url('admin-ajax.php')
	    ));

	    wp_enqueue_style( 'icofont-css', 'https://cdn.jsdelivr.net/npm/icofont@1.0.0/dist/icofont.min.css', array(), PLUG_VERSION );
	    wp_enqueue_style( 'adoni-login-css', URL_ADONITRANSPLUG.'/assets/css/iniciar-sesion.css', array(), PLUG_VERSION );
	}

	$panel_administracion = get_field('panel_administracion', 'option');
    $panel_colaborador = get_field('panel_colaborador', 'option');    

    if ( $panel_administracion || $panel_colaborador) {
    	$slugadm = $panel_administracion->post_name;
        $slugcol = $panel_colaborador->post_name;

        if ( is_page($slugadm) || is_page($slugcol) ) {
            wp_enqueue_style( 'icofont-css', 'https://cdn.jsdelivr.net/npm/icofont@1.0.0/dist/icofont.min.css', array(), PLUG_VERSION );
            wp_enqueue_style( 'adoni-administracion-css', URL_ADONITRANSPLUG.'assets/css/panel-administracion.css', array(), PLUG_VERSION );

            // Cargar los scripts de FullCalendar
            /*wp_enqueue_style( 'fullcalendar-core-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css', array(), PLUG_VERSION );*/
            wp_enqueue_script('fullcalendar-core-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js', array(), '6.1.15', true);


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

            wp_enqueue_script('adoni-recorridos-js', URL_ADONITRANSPLUG . '/assets/js/recorridos.js', array('jquery'), null, true);
            wp_localize_script('adoni-recorridos-js', 'recorridoAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'plugin_url' => URL_ADONITRANSPLUG,
            ));

            wp_enqueue_style( 'adoni-cuenta-css', URL_ADONITRANSPLUG.'assets/css/panel-cuenta.css', array(), PLUG_VERSION );

            wp_enqueue_style( 'adoni-asignaciones-css', URL_ADONITRANSPLUG.'assets/css/panel-asignaciones.css', array(), PLUG_VERSION );
            wp_enqueue_style( 'adoni-empresa-css', URL_ADONITRANSPLUG.'assets/css/panel-empresa.css', array(), PLUG_VERSION );
            wp_enqueue_style( 'adoni-usuarios-css', URL_ADONITRANSPLUG.'assets/css/panel-usuarios.css', array(), PLUG_VERSION );
            wp_enqueue_style( 'adoni-vehiculos-css', URL_ADONITRANSPLUG.'assets/css/panel-vehiculos.css', array(), PLUG_VERSION );

            wp_enqueue_script('adoni-asignaciones-js', URL_ADONITRANSPLUG . 'assets/js/asignaciones.js', array('jquery'), null, true);
            wp_localize_script('adoni-asignaciones-js', 'asignacionAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'urlsite' => get_site_url( ),
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

            wp_enqueue_script('adoni-conductor-js', URL_ADONITRANSPLUG . '/assets/js/conductor.js', array('jquery'), null, true);
            wp_localize_script('adoni-conductor-js', 'conductorAjax', array(
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

add_action('wp_ajax_render_html_panel', 'func_render_html_panel');
add_action('wp_ajax_nopriv_render_html_panel', 'func_render_html_panel');
function func_render_html_panel() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        exit('Acceso no autorizado');
    }
}

// Filtrar errores de Elementor
add_filter('elementor/debugger/log', function($log) {
    // Filtrar o modificar el log si es un aviso de Elementor
    if ( strpos($log, 'Elementor\Controls_Manager') !== false ) {
        return ''; // Ignorar este log
    }
    return $log;
});

// Filtrar errores específicos y registrar otros
function custom_error_filter($errno, $errstr, $errfile, $errline) {
    // Omite los errores de Elementor que contienen "sticky_divider"
    if ( strpos($errstr, 'Elementor\Controls_Manager') !== false ) {
        return true; // Ignorar este error
    }
    // Registrar otros errores
    return false;
}

// Agregar el filtro para omitir algunos errores
set_error_handler('custom_error_filter');

function format_date_for_input($date) {
    $date_obj = DateTime::createFromFormat('d/m/Y', $date);
    return $date_obj ? $date_obj->format('Y-m-d') : $date;
}

function format_time_input($hora12) {
    $hora24 = date("H:i", strtotime($hora12));
    return $hora24;
}

function get_mails_role($roles = []) {
    if (empty($roles)) {
        return [];
    }

    $usuarios = get_users([
        'role__in' => $roles,
        'fields'   => ['user_email']
    ]);

    return wp_list_pluck($usuarios, 'user_email');
}

function obtener_usuarios_colaborador($empresa_id, $usuario_actual_id = null) {
    if (!is_numeric($empresa_id)) {
        return []; // Validación para evitar consultas incorrectas
    }

    $args = [
        'role'       => 'colaborador',
        'meta_key'   => 'empresa_asociada_usuario',
        'meta_value' => $empresa_id,
        'fields'     => ['ID'] // Solo obtenemos el ID para optimizar la consulta
    ];

    if (!empty($usuario_actual_id) && is_numeric($usuario_actual_id)) {
        $args['exclude'] = [$usuario_actual_id]; // Excluir al usuario actual si está definido
    }

    $usuarios = get_users($args);
    $usuarios_array = [];

    foreach ($usuarios as $usuario) {
        $first_name = get_user_meta($usuario->ID, 'first_name', true);
        $last_name  = get_user_meta($usuario->ID, 'last_name', true);
        $nombre_completo = trim($first_name . ' ' . $last_name);

        $usuarios_array[] = [
            'id'   => $usuario->ID,
            'name' => $nombre_completo ?: 'Sin Nombre'
        ];
    }

    return $usuarios_array;
}

function send_email_token($to, $subject, $message_content) {
    
    $to = $to;

    // Validar el correo del destinatario
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log("Correo del destinatario no válido");
        return false; // Correo del destinatario no válido
    }

    // Obtener el correo del administrador para CC
    $cc = get_option('admin_email');

    // Obtener el remitente desde la página de opciones
    $sender_email = get_field('email_notifications_sender_email', 'option'); // ACF group
    if (!$sender_email || !filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
        error_log("Usar el correo del admin si no se configura o no es válido");
        $sender_email = $cc; // Usar el correo del admin si no se configura o no es válido
    }

    // Leer el contenido del template de correo
    $html_content = file_get_contents(PATH_ADONITRANSPLUG . 'includes/mail/token.html');
    if (!$html_content) {
        error_log("No se pudo leer el template");
        return false; // No se pudo leer el template
    }

    // Concatenar las partes del mensaje
    $custom_content = $message_content;

    // Reemplazar el marcador en el template
    $html_content = preg_replace(
        '/<span id="token-generado"><\/span>/',
        '<span id="token-generado">' . $custom_content . '</span>',
        $html_content
    );

    // Encabezados del correo
    $headers = [
        "From: {$sender_email}",
        "Content-Type: text/html; charset=UTF-8",
        "CC: {$cc}"
    ];
    $headers = implode("\r\n", $headers);

    // Enviar correo
    if (!wp_mail($to, $subject, $html_content, $headers)) {
        error_log('Error al enviar el correo a ' . $to);
        return false;
    }
    return true;
}

function send_email_notification($subject, $message_content, $recipient_email = null, $cc_emails = []) {
    // Si no se proporciona un destinatario, usar el del usuario actual autenticado
    if (!$recipient_email) {
        if (!is_user_logged_in()) {
            error_log("Error: Intento de enviar correo sin usuario autenticado y sin destinatario proporcionado.");
            return false; // No hay destinatario válido
        }
        $current_user = wp_get_current_user();
        $recipient_email = $current_user->user_email;
    }

    // Sanitizar y validar el correo del destinatario
    $to = sanitize_email($recipient_email);
    if (!is_email($to)) {
        error_log("Error: El correo del destinatario '{$recipient_email}' no es válido.");
        return false; // No enviar si el destinatario no es válido
    }

    // Obtener el remitente desde ACF o usar el del admin si no está configurado
    $sender_email = sanitize_email(get_field('email_notifications_sender_email', 'option')) ?: get_option('admin_email');
    if (!is_email($sender_email)) {
        error_log("Error: El correo del remitente '{$sender_email}' no es válido.");
        return false; // No enviar si el remitente no es válido
    }

    // Manejo de correos en CC
    if (!is_array($cc_emails)) {
        $cc_emails = [$cc_emails]; // Convertir en array si es un solo correo
    }

    $cc_emails = array_filter(array_map('sanitize_email', $cc_emails), 'is_email'); // Sanitizar y filtrar
    $cc_header = !empty($cc_emails) ? 'CC: ' . implode(', ', $cc_emails) : '';

    // Obtener y procesar la plantilla de correo
    ob_start();
    include PATH_ADONITRANSPLUG . 'includes/mail/operacion.html';
    $html_content = ob_get_clean();

    if (!$html_content) {
        error_log("Error: No se pudo cargar la plantilla de correo desde " . PATH_ADONITRANSPLUG . 'includes/mail/operacion.html');
        return false;
    }

    // Validar `$message_content` y construir el contenido personalizado
    $custom_content = is_array($message_content) ? implode('', array_map('wp_kses_post', $message_content)) : '';

    // Reemplazar el marcador en la plantilla
    $html_content = str_replace('<div id="cust-mensaje"></div>', 
                                '<div id="cust-mensaje">' . $custom_content . '</div>', 
                                $html_content);

    // Encabezados del correo
    $headers = [
        "From: {$sender_email}",
        "Content-Type: text/html; charset=UTF-8"
    ];

    // Agregar CC si hay correos válidos
    if (!empty($cc_header)) {
        $headers[] = $cc_header;
    }

    // Enviar el correo
    $sent = wp_mail($to, sanitize_text_field($subject), $html_content, $headers);

    if (!$sent) {
        error_log("Error al enviar el correo a '{$to}' con asunto: '{$subject}'");
    }

    return $sent;
}
