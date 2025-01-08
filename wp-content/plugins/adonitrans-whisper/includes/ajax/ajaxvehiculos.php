<?php

/*Acción AJAX para CREAR un Vehiculo*/
add_action('wp_ajax_create_vehiculo', 'create_vehiculo_function');
add_action('wp_ajax_nopriv_create_vehiculo', 'create_vehiculo_function');
function create_vehiculo_function() {
    // Verificar nonce si es necesario (no se ha incluido en el ejemplo)
    if (!isset($_POST['create_vehiculo_nonce']) || !wp_verify_nonce($_POST['create_vehiculo_nonce'], 'create_vehiculo_action')) {
        wp_send_json_error(['message' => 'Nonce no válido.']);
        wp_die();
    }

    // Obtener los datos del formulario
    $estado_del_vehiculo = sanitize_text_field($_POST['estado_del_vehiculo']);
    $placa_vehiculo = strtoupper(sanitize_text_field($_POST['placa_vehiculo']));
    $tipo_de_vehiculo = sanitize_text_field($_POST['tipo_de_vehiculo']);
    $modelo_vehiculo = sanitize_text_field($_POST['modelo_vehiculo']);
    $cantidad_pasajeros_vehiculo = sanitize_text_field($_POST['cantidad_pasajeros_vehiculo']);
    $marca_vehiculo = sanitize_text_field($_POST['marca_vehiculo']);
    $serial_vehiculo = sanitize_text_field($_POST['serial_vehiculo']);
    $chasis_vehiculo = sanitize_text_field($_POST['chasis_vehiculo']);
    $fecha_vencimiento_soat = sanitize_text_field($_POST['fecha_vencimiento_soat']);
    $fecha_vencimiento_tecno_mecanica = sanitize_text_field($_POST['fecha_vencimiento_tecno_mecanica']);
    $propietario_de_vehiculo = sanitize_text_field($_POST['propietario_de_vehiculo']);
    $conductor_del_vehiculo = sanitize_text_field($_POST['conductor_del_vehiculo']);

    $accion1 = "Crear";   
    $accion2 = "Creado";   

    if (isset($_POST['vehiculo-id']) && !empty($_POST['vehiculo-id'])) {
        $post_id = $_POST['vehiculo-id'];
        $accion1 = "Editar";   
        $accion2 = "Editado"; 

    }else{

        // Crear el post
        $post_data = array(
            'post_type'   => 'vehiculo',
            'post_status' => 'publish',
            'post_title'  => $placa_vehiculo, // Usar la placa como título
        );

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( array( 'message' => 'Error al crear el vehículo.' ) );
        }
    }

    // Guardar campos personalizados
    update_post_meta( $post_id, 'estado_del_vehiculo', $estado_del_vehiculo );
    update_post_meta( $post_id, 'placa_vehiculo', $placa_vehiculo );
    update_post_meta( $post_id, 'tipo_de_vehiculo', $tipo_de_vehiculo );
    update_post_meta( $post_id, 'modelo_vehiculo', $modelo_vehiculo );
    update_post_meta( $post_id, 'cantidad_pasajeros_vehiculo', $cantidad_pasajeros_vehiculo );
    update_post_meta( $post_id, 'marca_vehiculo', $marca_vehiculo );
    update_post_meta( $post_id, 'serial_vehiculo', $serial_vehiculo );    
    update_post_meta( $post_id, 'chasis_vehiculo', $chasis_vehiculo );
    update_post_meta( $post_id, 'fecha_vencimiento_soat', $fecha_vencimiento_soat );
    update_post_meta( $post_id, 'fecha_vencimiento_tecno_mecanica', $fecha_vencimiento_tecno_mecanica );
    update_post_meta( $post_id, 'propietario_de_vehiculo', $propietario_de_vehiculo );
    update_post_meta( $post_id, 'conductor_del_vehiculo', $conductor_del_vehiculo );

    // Devolver respuesta de éxito
    wp_send_json_success(['message' => 'Vehículo '.$accion2.' exitosamente']);
}

// Eliminar vehículo
function handle_delete_vehiculo() {

    // Verificar permisos del usuario
    if ( ! current_user_can( 'delete_posts' ) ) {
        wp_send_json_error( array( 'message' => 'Permisos insuficientes.' ) );
    }

    // Verificar el ID del post
    if ( ! isset( $_POST['post_id'] ) || empty( $_POST['post_id'] ) || ! is_numeric( $_POST['post_id'] ) ) {
        wp_send_json_error( array( 'message' => 'ID de post inválido o no proporcionado.' ) );
    }

    $post_id = intval( $_POST['post_id'] );

    // Verificar que el post existe y que es del tipo 'vehiculo'
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'vehiculo' ) {
        wp_send_json_error( array( 'message' => 'El post no existe o no es un Vehículo.' ) );
    }

    // Eliminar el post
    $deleted = wp_delete_post( $post_id, true );

    if ( $deleted ) {
        wp_send_json_success( array( 'message' => 'Vehículo eliminado exitosamente.', 'post_id' => $post_id ) );
    } else {
        wp_send_json_error( array( 'message' => 'Error al eliminar el vehículo.' ) );
    }
}
add_action( 'wp_ajax_delete_vehiculo', 'handle_delete_vehiculo' );
add_action( 'wp_ajax_nopriv_delete_vehiculo', 'handle_delete_vehiculo' );

function format_date_for_input($date) {
    $date_obj = DateTime::createFromFormat('d/m/Y', $date);
    return $date_obj ? $date_obj->format('Y-m-d') : $date;
}

/*ACCION AJAX PARA OBTENER DATOS DE UN VEHICULO*/
add_action('wp_ajax_load_vehiculo_data', 'load_vehiculo_data_function');
add_action('wp_ajax_nopriv_load_vehiculo_data', 'load_vehiculo_data_function');
function load_vehiculo_data_function() {
    $post_id = intval($_POST['post_id']);
    if (!$post_id || get_post_type($post_id) !== 'vehiculo') {
        wp_send_json_error(['message' => 'Post no válido o no es un tipo de post vehiculo.']);
    }

    $fecha_vencimiento_soat = get_post_meta($post_id, 'fecha_vencimiento_soat', true);
    $fecha_vencimiento_tecno_mecanica = get_post_meta($post_id, 'fecha_vencimiento_tecno_mecanica', true);

    // Convertir las fechas al formato YYYY-MM-DD
    $fecha_vencimiento_soat_formatted = format_date_for_input($fecha_vencimiento_soat);
    $fecha_vencimiento_tecno_mecanica_formatted = format_date_for_input($fecha_vencimiento_tecno_mecanica);

    wp_send_json_success([
        'estado_del_vehiculo'               => get_post_meta($post_id, 'estado_del_vehiculo', true),
        'placa_vehiculo'                    => get_post_meta($post_id, 'placa_vehiculo', true),
        'tipo_de_vehiculo'                  => get_post_meta($post_id, 'tipo_de_vehiculo', true),
        'modelo_vehiculo'                   => get_post_meta($post_id, 'modelo_vehiculo', true),
        'cantidad_pasajeros_vehiculo'       => get_post_meta($post_id, 'cantidad_pasajeros_vehiculo', true),
        'marca_vehiculo'                    => get_post_meta($post_id, 'marca_vehiculo', true),
        'serial_vehiculo'                   => get_post_meta($post_id, 'serial_vehiculo', true),
        'chasis_vehiculo'                   => get_post_meta($post_id, 'chasis_vehiculo', true),
        'fecha_vencimiento_soat'            => $fecha_vencimiento_soat_formatted,
        'fecha_vencimiento_tecno_mecanica'  => $fecha_vencimiento_tecno_mecanica_formatted,
        'propietario_de_vehiculo'           => get_post_meta($post_id, 'propietario_de_vehiculo', true),
        'conductor_del_vehiculo'            => get_post_meta($post_id, 'conductor_del_vehiculo', true),
    ]);
}



// Obtener lista de vehículos
add_action('wp_ajax_obtener_vehiculos', function () {
    $vehiculos = [];
    $query = new WP_Query(['post_type' => 'vehiculo', 'posts_per_page' => -1]);
    while ($query->have_posts()) {
        $query->the_post();
        $vehiculos[] = [
            'id' => get_the_ID(),
            'placa' => get_post_meta(get_the_ID(), 'placa', true),
            'modelo' => get_post_meta(get_the_ID(), 'modelo', true),
            'marca' => get_post_meta(get_the_ID(), 'marca', true),
            'estado' => get_post_meta(get_the_ID(), 'estado', true),
        ];
    }
    wp_reset_postdata();
    wp_send_json($vehiculos);
});

// Obtener datos de un vehículo
add_action('wp_ajax_obtener_vehiculo', function () {
    $vehiculo_id = intval($_POST['id']);
    $vehiculo = [
        'id' => $vehiculo_id,
        'placa' => get_post_meta($vehiculo_id, 'placa', true),
        'modelo' => get_post_meta($vehiculo_id, 'modelo', true),
        'marca' => get_post_meta($vehiculo_id, 'marca', true),
        'estado' => get_post_meta($vehiculo_id, 'estado', true),
    ];
    wp_send_json($vehiculo);
});

// Activar/Inactivar vehículo
add_action('wp_ajax_toggle_estado_vehiculo', function () {
    $vehiculo_id = intval($_POST['id']);
    $estado = get_post_meta($vehiculo_id, 'estado', true) === 'activo' ? 'inactivo' : 'activo';
    update_post_meta($vehiculo_id, 'estado', $estado);
    wp_send_json_success();
});
