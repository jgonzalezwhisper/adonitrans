<?php
add_action('wp_ajax_get_barrios', 'obtener_barrios_por_ciudad');
add_action('wp_ajax_nopriv_get_barrios', 'obtener_barrios_por_ciudad');
function obtener_barrios_por_ciudad() {
    if (!isset($_POST['ciudad_id']) || empty($_POST['ciudad_id'])) {
        wp_send_json_error(['message' => 'ID de ciudad no enviado.']);
    }

    $ciudad_id = intval($_POST['ciudad_id']);
    $barrios = [];
    $grupo_datos = get_field('grupo_datos_de_barrios', $ciudad_id);

    if (!empty($grupo_datos['repetidor_de_barrios'])) {
        foreach ($grupo_datos['repetidor_de_barrios'] as $barrio) {
            $barrios[] = $barrio['barrio'];
        }
    }

    if (!empty($barrios)) {
        wp_send_json_success($barrios);
    } else {
        wp_send_json_error(['message' => 'No se encontraron barrios para esta ciudad.']);
    }

    wp_die();
}

add_action('wp_ajax_get_centros_de_costo', 'func_get_centros_de_costo');
add_action('wp_ajax_nopriv_get_centros_de_costo', 'func_get_centros_de_costo');
function func_get_centros_de_costo() {
    // Validar nonce si es necesario (opcional)
    if (!isset($_POST['id_solicitante']) || empty($_POST['id_solicitante'])) {
        wp_send_json_error(['message' => 'ID del solicitante no proporcionado.']);
    }

    $user_id = intval($_POST['id_solicitante']);
    
    // Obtener la empresa asociada al usuario
    $empresa_asociada = get_field('empresa_asociada_usuario', 'user_' . $user_id);

    if (!$empresa_asociada || empty($empresa_asociada->ID)) {
        wp_send_json_error(['message' => 'No se encontró una empresa asociada para este usuario.']);
    }

    // Obtener los centros de costo de la empresa
    $centros_costo_empresa = get_field('centros_de_costos_empresa', $empresa_asociada->ID);

    if (empty($centros_costo_empresa)) {
        wp_send_json_error(['message' => 'No se encontraron centros de costo para esta empresa.']);
    }

    // Preparar los datos para retornarlos como JSON
    $centros = [];
    foreach ($centros_costo_empresa as $centro) {
        $centros[] = [
            'codigo' => $centro['codigo'],
            'nombre' => $centro['nombre'],
        ];
    }

    // Retornar los datos
    wp_send_json_success($centros);
}

/*Acción AJAX para CREAR o ACTUALIZAR un Recorrido*/
add_action('wp_ajax_create_recorrido', 'create_recorrido_function');
add_action('wp_ajax_nopriv_create_recorrido', 'create_recorrido_function');
function create_recorrido_function() {
    // Verificar nonce si es necesario (no se ha incluido en el ejemplo)
    if (!isset($_POST['create_recorrido_nonce']) || !wp_verify_nonce($_POST['create_recorrido_nonce'], 'create_recorrido_action')) {
        wp_send_json_error(['message' => 'Nonce no válido.']);
        wp_die();
    }

    // Obtener los datos del formulario
    $id_solicitante_recorrido = sanitize_text_field($_POST['id_solicitante_recorrido']);
    $empresa_solicitante_recorrido = get_field('empresa_asociada_usuario', 'user_' . $id_solicitante_recorrido);
    $id_conductor_recorrido = sanitize_text_field($_POST['id_conductor_recorrido']);
    $ciudad_inicio = sanitize_text_field($_POST['ciudad_inicio']);
    $nombre_inicio = get_the_title( $ciudad_inicio );
    $barrio_inicio = sanitize_text_field($_POST['barrio_inicio']);
    $ciudad_fin = sanitize_text_field($_POST['ciudad_fin']);
    $nombre_fin = get_the_title( $ciudad_fin );
    $barrio_fin = sanitize_text_field($_POST['barrio_fin']);
    $fecha_inicio_recorrido = sanitize_text_field($_POST['fecha_inicio_recorrido']);
    $hora_inicio_recorrido = sanitize_text_field($_POST['hora_inicio_recorrido']);
    $centro_de_costo = sanitize_text_field($_POST['centro_de_costo']);

    if ($ciudad_inicio === $ciudad_fin) {
	    $titulo = "Recorrido $nombre_inicio [$barrio_inicio - $barrio_fin]";
	} else {
	    $titulo = "Recorrido $nombre_inicio - $nombre_fin [$barrio_inicio - $barrio_fin]";
	}

    $accion1 = "Crear";   
    $accion2 = "Creado";   

    if (isset($_POST['recorrido-id']) && !empty($_POST['recorrido-id'])) {
        $post_id = $_POST['recorrido-id'];
        $accion1 = "Editar";   
        $accion2 = "Editado"; 
    } else {
        $post_data = array(
            'post_type'   => 'recorrido',
            'post_status' => 'publish',
            'post_title'  => $titulo
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'Error al crear el vehículo.']);
        }
    }

    // Guardar campos personalizados usando ACF
    update_field('empresa_solicitante_recorrido', $empresa_solicitante_recorrido, $post_id);
    update_field('id_solicitante_recorrido', $id_solicitante_recorrido, $post_id);
    if (!empty($id_conductor_recorrido)) {
        update_field('id_conductor_recorrido', $id_conductor_recorrido, $post_id);
    }
    update_field('ciudad_inicial_recorrido', $nombre_inicio, $post_id);
    update_field('ciudad_inicial_recorrido_codigo', $ciudad_inicio, $post_id);
    update_field('barrio_inicial_recorrido', $barrio_inicio, $post_id);
    update_field('ciudad_final_recorrido', $nombre_fin, $post_id);
    update_field('ciudad_final_recorrido_codigo', $ciudad_fin, $post_id);
    update_field('barrio_final_recorrido', $barrio_fin, $post_id);
    update_field('fecha_inicio_recorrido', $fecha_inicio_recorrido, $post_id);
    update_field('hora_inicio_recorrido', $hora_inicio_recorrido, $post_id);
    update_field('estado_del_recorrido', 'Pendiente', $post_id);
    if (!empty($centro_de_costo)) {
        update_field('centro_de_costo', $centro_de_costo, $post_id);
    }

    // Devolver respuesta de éxito
    wp_send_json_success(['message' => 'Recorrido ' . $accion2 . ' exitosamente']);
}

/*Eliminar recorrido*/
function handle_delete_recorrido() {

    // Verificar permisos del usuario
    if ( !current_user_can( 'delete_posts' ) ) {
        wp_send_json_error( array( 'message' => 'Permisos insuficientes.' ) );
    }

    // Verificar el ID del post
    if ( ! isset( $_POST['post_id'] ) || empty( $_POST['post_id'] ) || ! is_numeric( $_POST['post_id'] ) ) {
        wp_send_json_error( array( 'message' => 'ID de post inválido o no proporcionado.' ) );
    }

    $post_id = intval( $_POST['post_id'] );

    // Verificar que el post existe y que es del tipo 'recorrido'
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'recorrido' ) {
        wp_send_json_error( array( 'message' => 'El post no existe o no es un Recorrido.' ) );
    }

    // Eliminar el post
    $deleted = wp_delete_post( $post_id, true );

    if ( $deleted ) {
        wp_send_json_success( array( 'message' => 'Recorrido eliminado exitosamente.', 'post_id' => $post_id ) );
    } else {
        wp_send_json_error( array( 'message' => 'Error al eliminar el recorrido.' ) );
    }
}
add_action( 'wp_ajax_delete_recorrido', 'handle_delete_recorrido' );
add_action( 'wp_ajax_nopriv_delete_recorrido', 'handle_delete_recorrido' );

/*ACCION AJAX PARA OBTENER DATOS DE UN RECORRIDO*/
add_action('wp_ajax_load_recorrido_data', 'load_recorrido_data_function');
add_action('wp_ajax_nopriv_load_recorrido_data', 'load_recorrido_data_function');
function load_recorrido_data_function() {
    // Verificar que la solicitud sea válida
    $post_id = intval($_POST['post_id']);
    if (!$post_id || get_post_type($post_id) !== 'recorrido') {
        wp_send_json_error(['message' => 'Post no válido o no es un tipo de post recorrido.']);
    }

    // Obtener el usuario actual y su rol
    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles; // Puede haber múltiples roles
    $user_role = !empty($user_roles) ? $user_roles[0] : '';

    // Obtener datos del post usando ACF
    $fecha_inicio_recorrido = get_field('fecha_inicio_recorrido', $post_id);
    $fecha_inicio_recorrido = format_date_for_input($fecha_inicio_recorrido); // Si tienes esta función para formatear

    // Preparar la respuesta dependiendo del rol del usuario
    $response = [
        'fecha_inicio_recorrido' => $fecha_inicio_recorrido,
        'hora_inicio_recorrido'  => format_time_input(get_field('hora_inicio_recorrido', $post_id)),
        'ciudad_inicio'          => get_field('ciudad_inicial_recorrido_codigo', $post_id),
        'barrio_inicio'          => get_field('barrio_inicial_recorrido', $post_id),
        'ciudad_fin'             => get_field('ciudad_final_recorrido_codigo', $post_id),
        'barrio_fin'             => get_field('barrio_final_recorrido', $post_id),
    ];

    // Si el usuario es administrador o empresa, añadir más datos
    if ($user_role === 'administrator' || $user_role === 'empresa') {
        $response['id_solicitante_recorrido'] = get_field('id_solicitante_recorrido', $post_id)['ID'];
        $response['id_conductor_recorrido']   = get_field('id_conductor_recorrido', $post_id);
        $response['centro_de_costo']          = get_field('centro_de_costo', $post_id);
    }

    // Si el usuario es colaborador, solo incluir centro_de_costo
    if ($user_role === 'colaborador') {
        $response['centro_de_costo'] = get_field('centro_de_costo', $post_id);
    }

    // Devolver la respuesta en formato JSON
    wp_send_json_success($response);
}