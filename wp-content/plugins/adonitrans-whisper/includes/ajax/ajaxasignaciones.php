<?php

/*Acción AJAX para CREAR o ACTUALIZAR una Asignación*/
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

/*Eliminar asignacion*/
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