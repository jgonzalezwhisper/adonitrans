<?php 

if (!defined('ABSPATH')) {
    exit;
}

function redirect_after_logout() {
    $pagina_iniciar_sesion = get_field('pagina_iniciar_sesion', 'option');
    $redirect_url = get_permalink($pagina_iniciar_sesion);
    wp_safe_redirect($redirect_url);
    exit;
}
add_action('wp_logout', 'redirect_after_logout');

function redirigir_si_logueado() {

    $panel_administracion = get_field('panel_administracion', 'option');
    $pagina_iniciar_sesion = get_field('pagina_iniciar_sesion', 'option');

    if ( is_user_logged_in() ) {

        if ( is_page('iniciar-sesion') ) {            
            if ( $panel_administracion ) {
                $redirect_url = get_permalink($panel_administracion);
                wp_redirect($redirect_url);
                exit;
            }
        }
    }else{
        if( is_page($panel_administracion->post_name) ){
            $redirect_url = get_permalink($pagina_iniciar_sesion);
            wp_redirect($redirect_url);
            exit;
        }
    }
}
add_action('template_redirect', 'redirigir_si_logueado');