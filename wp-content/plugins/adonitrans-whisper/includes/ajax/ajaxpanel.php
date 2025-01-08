<?php 
add_action('wp_ajax_load_panel_content', 'load_panel_content'); // Para usuarios autenticados
add_action('wp_ajax_nopriv_load_panel_content', 'load_panel_content'); // Para usuarios no autenticados
function load_panel_content() {
    // Asegura que estás dentro del contexto de WordPress
    $action = sanitize_text_field($_POST['panel_action']);

    // Define el archivo PHP a cargar
    $file_path = plugin_dir_path(__FILE__) . 'includes/parts/panel/' . $action . '.php';

    if (file_exists($file_path)) {
        include $file_path; // Incluye el archivo dentro del contexto de WordPress
    } else {
        wp_send_json_error('Archivo no encontrado.', 404);
    }

    wp_die(); // Termina la ejecución correctamente
}
