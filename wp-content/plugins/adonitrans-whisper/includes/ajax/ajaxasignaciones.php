<?php

/*ACCION AJAX PARA CREAR O EDITAR DATOS DE UNA ASIGNACION*/
add_action('wp_ajax_create_asignacion', 'create_asignacion_function');
add_action('wp_ajax_nopriv_create_asignacion', 'create_asignacion_function');
function create_asignacion_function() {
    // Verificar nonce si es necesario (no se ha incluido en el ejemplo)
    if (!isset($_POST['create_asignacion_nonce']) || !wp_verify_nonce($_POST['create_asignacion_nonce'], 'create_asignacion_action')) {
        wp_send_json_error(['message' => 'Nonce no válido.']);
        wp_die();
    }

    // Obtener los datos del formulario
    $id_conductor_asignado = sanitize_text_field($_POST['id_conductor_asignado']);
    $inicio_semana_asignacion = sanitize_text_field($_POST['inicio_semana_asignacion']);
    $fin_semana_asignacion = sanitize_text_field($_POST['fin_semana_asignacion']);

    $dias_inicio = sanitize_text_field($_POST['dia_inicio_de_asignacion']);
    $dias_fin = sanitize_text_field($_POST['dia_fin_de_asignacion']);
    $franjas_horarias = sanitize_text_field($_POST['franja_horaria_asignacion']);

    // Obtener datos del usuario
	$user_info = get_userdata($id_conductor_asignado);

	if ($user_info) {
	    // Obtener el primer nombre
	    $first_name = get_user_meta($id_conductor_asignado, 'first_name', true);

	    // Obtener el correo electrónico
	    $email = $user_info->user_email;
	} 

	$titulo = "$first_name ($email) -- $inicio_semana_asignacion // $fin_semana_asignacion";

    $accion1 = "Crear";   
    $accion2 = "Creado";   

    if (isset($_POST['asignacion-id']) && !empty($_POST['asignacion-id'])) {
        $post_id = $_POST['asignacion-id'];
        $accion1 = "Editar";   
        $accion2 = "Editado"; 
    } else {
        $post_data = array(
            'post_type'   => 'asignacion',
            'post_status' => 'publish',
            'post_title'  => $titulo
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'Error al crear la asignación.']);
        }
    }

    // Guardar campos personalizados usando ACF
    update_field('id_conductor_asignado', $id_conductor_asignado, $post_id);
    update_field('inicio_semana_asignacion', $inicio_semana_asignacion, $post_id);
    update_field('fin_semana_asignacion', $fin_semana_asignacion, $post_id);

    if (isset($_POST['dia_inicio_de_asignacion'], $_POST['dia_fin_de_asignacion'], $_POST['franja_horaria_asignacion'])) {
        $dias_inicio = $_POST['dia_inicio_de_asignacion'];
        $dias_fin = $_POST['dia_fin_de_asignacion'];
        $franjas_horarias = $_POST['franja_horaria_asignacion'];

        if (count($dias_inicio) === count($dias_fin) && count($dias_inicio) === count($franjas_horarias)) {
            $asignaciones = [];

            foreach ($dias_inicio as $key => $dia_inicio) {
                $dia_fin = $dias_fin[$key] ?? '';
                $franja_horaria = $franjas_horarias[$key] ?? '';

                if (!empty($dia_inicio) && !empty($dia_fin) && !empty($franja_horaria)) {
                    $asignaciones[] = [
                        'dia_inicio_de_asignacion' => sanitize_text_field($dia_inicio),
                        'dia_fin_de_asignacion' => sanitize_text_field($dia_fin),
                        'franja_horaria_asignacion' => sanitize_text_field($franja_horaria),
                    ];
                }
            }
            if (!empty($asignaciones)) {
                update_field('asignaciones_de_la_semana', $asignaciones, $post_id);
            } 
        }
    }

    // Devolver respuesta de éxito
    wp_send_json_success(['message' => 'Asignación ' . $accion2 . ' exitosamente']);
}

/*ACCION AJAX PARA ELIMINAR DATOS DE UNA ASIGNACION*/
function handle_delete_asignacion() {

    // Verificar permisos del usuario
    if ( !current_user_can( 'delete_posts' ) ) {
        wp_send_json_error( array( 'message' => 'Permisos insuficientes.' ) );
    }

    // Verificar el ID del post
    if ( ! isset( $_POST['post_id'] ) || empty( $_POST['post_id'] ) || ! is_numeric( $_POST['post_id'] ) ) {
        wp_send_json_error( array( 'message' => 'ID de post inválido o no proporcionado.' ) );
    }

    $post_id = intval( $_POST['post_id'] );

    // Verificar que el post existe y que es del tipo 'asignacion'
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'asignacion' ) {
        wp_send_json_error( array( 'message' => 'El post no existe o no es una Asignación.' ) );
    }

    // Eliminar el post
    $deleted = wp_delete_post( $post_id, true );

    if ( $deleted ) {
        wp_send_json_success( array( 'message' => 'Asignación eliminada exitosamente.', 'post_id' => $post_id ) );
    } else {
        wp_send_json_error( array( 'message' => 'Error al eliminar la asignacion.' ) );
    }
}
add_action( 'wp_ajax_delete_asignacion', 'handle_delete_asignacion' );
add_action( 'wp_ajax_nopriv_delete_asignacion', 'handle_delete_asignacion' );

/*ACCION AJAX PARA OBTENER DATOS DE UNA ASIGNACION*/
add_action('wp_ajax_load_asignacion_data', 'load_asignacion_data_function');
add_action('wp_ajax_nopriv_load_asignacion_data', 'load_asignacion_data_function');
function load_asignacion_data_function() {
    $post_id = intval($_POST['post_id']);
    if (!$post_id || get_post_type($post_id) !== 'asignacion') {
        wp_send_json_error(['message' => 'Post no válido o no es un tipo de post Asignación.']);
    }

    // Obtener inicio y fin de semana y formatear las fechas
    $inicio_semana_asignacion = get_post_meta($post_id, 'inicio_semana_asignacion', true);
    $fin_semana_asignacion = get_post_meta($post_id, 'fin_semana_asignacion', true);

    $inicio_semana_asignacion = format_date_for_input($inicio_semana_asignacion);
    $fin_semana_asignacion = format_date_for_input($fin_semana_asignacion);

    // Procesar el campo repetidor 'asignaciones_de_la_semana'
    $asignaciones_de_la_semana = [];
    if (have_rows('asignaciones_de_la_semana', $post_id)) {
        while (have_rows('asignaciones_de_la_semana', $post_id)) {
            the_row();

            $dia_inicio_de_asignacion = get_sub_field('dia_inicio_de_asignacion');
            $dia_fin_de_asignacion = get_sub_field('dia_fin_de_asignacion');
            $franja_horaria_asignacion = get_sub_field('franja_horaria_asignacion');

            $asignaciones_de_la_semana[] = [
                'dia_inicio_de_asignacion' => format_date_for_input($dia_inicio_de_asignacion),
                'dia_fin_de_asignacion' => format_date_for_input($dia_fin_de_asignacion),
                'franja_horaria_asignacion' => $franja_horaria_asignacion,
            ];
        }
    }

    // Respuesta JSON
    wp_send_json_success([
        'id_conductor_asignado' => get_post_meta($post_id, 'id_conductor_asignado', true),
        'inicio_semana_asignacion' => $inicio_semana_asignacion,
        'fin_semana_asignacion' => $fin_semana_asignacion,
        'asignaciones_de_la_semana' => $asignaciones_de_la_semana,
    ]);
}
