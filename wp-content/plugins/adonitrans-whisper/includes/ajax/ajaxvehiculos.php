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
    /*$conductor_del_vehiculo = sanitize_text_field($_POST['conductor_del_vehiculo']);*/

    $accion1 = "Crear";   
    $accion2 = "Creado";   

    if (isset($_POST['vehiculo-id']) && !empty($_POST['vehiculo-id'])) {
        $post_id = $_POST['vehiculo-id'];
        $accion1 = "Editar";   
        $accion2 = "Editado"; 

    }else{

        // Validar si ya existe un vehículo con la misma placa o título
        $existing_post = get_posts(array(
            'post_type'      => 'vehiculo',
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'   => 'placa_vehiculo',
                    'value' => $placa_vehiculo,
                ),
            ),
            'title'          => $placa_vehiculo, // Buscar por título
            'fields'         => 'ids', // Solo obtener los IDs
            'posts_per_page' => 1,
        ));

        if ($existing_post) {
            wp_send_json_error([
                'message' => 'Ya existe un vehículo con esta placa o título. ID Vehículo: '.$existing_post[0],
            ]);
            wp_die();
        }

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

    $fields = [
        'ruta__movil_vehi',
        'fecha_vinculacion_vehi',
        'link_gps_vehi',
        'fecha_terminacion_vehi',
        'ciudad_vehiculo',
        'empresa_vehi',
        'ultimo_mantenimiento_preventivo_vehi',
        'servicio_vehi',
        'combustible_vehi',
        'color_vehi',
        'no_de_motor_vehi',
        'linea_vehi',
        'cilindraje_vehi',
        'carroceria_vehi',
        'tipo_de_vehiculo'
    ];
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = sanitize_text_field($_POST[$field]);
            update_field($field, $value, $post_id);
        }
    }

    // Definición de los grupos ACF
    $acf_groups = [
        'seguro_contractual-extracontractual_vehi' => [
            'fecha' => 'seguro_fecha',
            'numero' => 'seguro_numero',
            'empresa' => 'seguro_empresa'
        ],
        'soat_vehi' => [
            'fecha' => 'soat_fecha',
            'numero' => 'soat_numero',
            'empresa' => 'soat_empresa'
        ],
        'tecnomecanica_vehi' => [
            'fecha' => 'tecno_fecha',
            'preventiva' => 'tecno_preventiva',
            'fuec' => 'tecno_fuec'
        ],
        'tarjeta_de_operacion_vehi' => [
            'fecha' => 'tarjeta_fecha',
            'numero' => 'tarjeta_numero',
            'fecha_matricula' => 'tarjeta_fecha_matricula'
        ]
    ];

    // Procesar y actualizar los campos ACF tipo grupo
    foreach ($acf_groups as $group_key => $fields) {
        $group_values = [];
        foreach ($fields as $acf_field => $post_key) {
            $group_values[$acf_field] = sanitize_text_field($_POST[$post_key] ?? '');
        }
        update_field($group_key, $group_values, $post_id);
    }
    

    // Guardar campos personalizados
    update_post_meta( $post_id, 'estado_del_vehiculo', $estado_del_vehiculo );
    update_post_meta( $post_id, 'placa_vehiculo', $placa_vehiculo );
    update_post_meta( $post_id, 'modelo_vehiculo', $modelo_vehiculo );
    update_post_meta( $post_id, 'cantidad_pasajeros_vehiculo', $cantidad_pasajeros_vehiculo );
    update_post_meta( $post_id, 'marca_vehiculo', $marca_vehiculo );
    update_post_meta( $post_id, 'serial_vehiculo', $serial_vehiculo );    
    update_post_meta( $post_id, 'chasis_vehiculo', $chasis_vehiculo );
    update_post_meta( $post_id, 'fecha_vencimiento_soat', $fecha_vencimiento_soat );
    update_post_meta( $post_id, 'fecha_vencimiento_tecno_mecanica', $fecha_vencimiento_tecno_mecanica );
    update_post_meta( $post_id, 'propietario_de_vehiculo', $propietario_de_vehiculo );
    /*update_post_meta( $post_id, 'conductor_del_vehiculo', $conductor_del_vehiculo );*/

    /*ARCHIVOS DEL VEHICULO*/
    $nombres = $_POST['nombre_archivo'];
    $archivos = $_FILES['file_archivo'];
    $repetidor_data = [];

    foreach ($nombres as $index => $nombre) {
        if (!empty($archivos['name'][$index])) {
            $archivo_id = subir_archivo_vehiculo($archivos, $index); // Obtener ID del adjunto

            if ($archivo_id) {
                $repetidor_data[] = [
                    'nombre'  => sanitize_text_field($nombre),
                    'archivo' => $archivo_id, // ACF espera el ID del adjunto, no la URL
                ];
            }
        }
    }

    // Guardar en el campo repetidor de ACF
    update_field('repetidor_archivos_vehi', $repetidor_data, $post_id);

    /*IMAGENES DEL VEHICULO*/
    $nombres_img = $_POST['nombre_imagen'];
    $archivos_img = $_FILES['imagen_archivo'];
    $repetidor_imagen_vehiculo = [];

    foreach ($nombres_img as $index => $nombre) {
        if (!empty($archivos_img['name'][$index])) {
            $archivo_id = subir_archivo_vehiculo($archivos_img, $index); // Obtener ID del adjunto

            if ($archivo_id) {
                $repetidor_imagen_vehiculo[] = [
                    'nombre'  => sanitize_text_field($nombre),
                    'archivo' => $archivo_id,
                ];
            }
        }
    }

    // Guardar en el campo repetidor de ACF
    update_field('repetidor_imagenes_vehi', $repetidor_imagen_vehiculo, $post_id);


    // Definir el asunto y cuerpo del mensaje
    $subject = 'Vehículo '.$accion2.' en AdoniGo';
    $message = [
        '<h2>Vehículo '.$accion2.'.</h2>',
        '<p>Un Vehículo ha sido eliminado en AdoniGo. Los datos del Vehículo son:</p>',
        sprintf('<p>Placa vehículo: <strong>%s</strong></p>', esc_html($placa_vehiculo)),
        sprintf('<p>Cantidad pasajeros vehiculo: <strong>%s</strong></p>', esc_html($cantidad_pasajeros_vehiculo)),
        sprintf('<p>Fecha vencimiento soat: <strong>%s</strong></p>', esc_html($fecha_vencimiento_soat)),
        sprintf('<p>Fecha vencimiento tecno mecanica: <strong>%s</strong></p>', esc_html($fecha_vencimiento_tecno_mecanica)),
    ];
    // Obtener el correo del usuario actual
    $current_user_email = wp_get_current_user()->user_email;

    $recipient_email = sanitize_email($current_user_email);
    
    $roles = ['operaciones_1', 'administrator'];
    $cc_emails = get_mails_role($roles); 

    // Eliminar el correo del usuario actual si existe en el array $cc_emails
    if (($key = array_search($current_user_email, $cc_emails)) !== false) {
        unset($cc_emails[$key]);
    }

    // Llamar a la función de notificación con los nuevos parámetros
    send_email_notification($subject, $message, $recipient_email, $cc_emails);


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

    // Obtener los datos meta del post
    $placa_vehiculo = get_post_meta( $post_id, 'placa_vehiculo', true );
    $cantidad_pasajeros_vehiculo = get_post_meta( $post_id, 'cantidad_pasajeros_vehiculo', true );
    $fecha_vencimiento_soat = get_post_meta( $post_id, 'fecha_vencimiento_soat', true );
    $fecha_vencimiento_tecno_mecanica = get_post_meta( $post_id, 'fecha_vencimiento_tecno_mecanica', true );

    // Eliminar el post
    $deleted = wp_delete_post( $post_id, true );

    if ( $deleted ) {

        // Definir el asunto y cuerpo del mensaje
        $subject = 'Vehículo '.$accion2.' en AdoniGo';
        $message = [
            '<h2>Vehículo '.$accion2.'.</h2>',
            '<p>Un Vehículo ha sido eliminado en AdoniGo. Los datos del Vehículo son:</p>',
            sprintf('<p>Placa vehículo: <strong>%s</strong></p>', esc_html($placa_vehiculo)),
            sprintf('<p>Cantidad pasajeros vehiculo: <strong>%s</strong></p>', esc_html($cantidad_pasajeros_vehiculo)),
            sprintf('<p>Fecha vencimiento soat: <strong>%s</strong></p>', esc_html($fecha_vencimiento_soat)),
            sprintf('<p>Fecha vencimiento tecno mecanica: <strong>%s</strong></p>', esc_html($fecha_vencimiento_tecno_mecanica)),
        ];

        // Obtener el correo del usuario actual
        $current_user_email = wp_get_current_user()->user_email;

        $recipient_email = sanitize_email($current_user_email);
        $roles = ['operaciones_1', 'administrator'];
        $users = get_users([
            'role__in' => $roles,
            'fields'   => ['user_email']
        ]);
        $cc_emails = wp_list_pluck($users, 'user_email');    

        // Eliminar el correo del usuario actual si existe en el array $cc_emails
        if (($key = array_search($current_user_email, $cc_emails)) !== false) {
            unset($cc_emails[$key]);
        }

        // Llamar a la función de notificación con los nuevos parámetros
        send_email_notification($subject, $message, $recipient_email, $cc_emails);

        wp_send_json_success( array( 'message' => 'Vehículo eliminado exitosamente.', 'post_id' => $post_id ) );
    } else {
        wp_send_json_error( array( 'message' => 'Error al eliminar el vehículo.' ) );
    }
}
add_action( 'wp_ajax_delete_vehiculo', 'handle_delete_vehiculo' );
add_action( 'wp_ajax_nopriv_delete_vehiculo', 'handle_delete_vehiculo' );

/*ACCION AJAX PARA OBTENER DATOS DE UN VEHICULO*/
add_action('wp_ajax_load_vehiculo_data', 'load_vehiculo_data_function');
add_action('wp_ajax_nopriv_load_vehiculo_data', 'load_vehiculo_data_function');
function load_vehiculo_data_function() {
    $post_id = intval($_POST['post_id']);
    if (!$post_id || get_post_type($post_id) !== 'vehiculo') {
        wp_send_json_error(['message' => 'Post no válido o no es un tipo de post vehiculo.']);
    }

    $fields = [
        'estado_del_vehiculo',
        'placa_vehiculo',
        'modelo_vehiculo',
        'cantidad_pasajeros_vehiculo',
        'marca_vehiculo',
        'serial_vehiculo',
        'chasis_vehiculo',
        'propietario_de_vehiculo',
        'conductor_del_vehiculo',
        'ruta__movil_vehi',
        'fecha_vinculacion_vehi',
        'link_gps_vehi',
        'fecha_terminacion_vehi',
        'ciudad_vehiculo',
        'empresa_vehi',
        'ultimo_mantenimiento_preventivo_vehi',
        'servicio_vehi',
        'combustible_vehi',
        'color_vehi',
        'no_de_motor_vehi',
        'linea_vehi',
        'cilindraje_vehi',
        'carroceria_vehi',
        'tipo_de_vehiculo',
        'fecha_vencimiento_soat',
        'fecha_vencimiento_tecno_mecanica'
    ];

    $acf_groups = [
        'seguro_contractual-extracontractual_vehi' => [
            'fecha'   => 'seguro_fecha',
            'numero'  => 'seguro_numero',
            'empresa' => 'seguro_empresa'
        ],
        'soat_vehi' => [
            'fecha'   => 'soat_fecha',
            'numero'  => 'soat_numero',
            'empresa' => 'soat_empresa'
        ],
        'tecnomecanica_vehi' => [
            'fecha'      => 'tecno_fecha',
            'preventiva' => 'tecno_preventiva',
            'fuec'       => 'tecno_fuec'
        ],
        'tarjeta_de_operacion_vehi' => [
            'fecha'           => 'tarjeta_fecha',
            'numero'          => 'tarjeta_numero',
            'fecha_matricula' => 'tarjeta_fecha_matricula'
        ]
    ];

    $response = [];

    foreach ($fields as $field) {
        $value = get_field($field, $post_id);
        $response[$field] = in_array($field, ['fecha_vencimiento_soat', 'fecha_vencimiento_tecno_mecanica', 'fecha_vinculacion_vehi', 'fecha_terminacion_vehi', 'ultimo_mantenimiento_preventivo_vehi'])
            ? format_date_for_input($value)
            : $value;
    }

    // Obtener datos de los grupos ACF
    foreach ($acf_groups as $group_key => $subfields) {
        $group_values = get_field($group_key, $post_id);
        if ($group_values) {
            foreach ($subfields as $sub_key => $mapped_key) {
                $value = $group_values[$sub_key] ?? '';
                // Aplicar formato si el sub_key contiene 'fecha'
                $response[$mapped_key] = (strpos($sub_key, 'fecha') !== false || strpos($sub_key, 'preventiva') !== false) ? format_date_for_input($value) : $value;
            }
        }
    }

    // Obtener los datos del repetidor de Archivos del Vehiculo
    $repetidor_archivos = get_field('repetidor_archivos_vehi', $post_id);
    $response['repetidor_archivos_vehi'] = [];

    if (!empty($repetidor_archivos)) {
        foreach ($repetidor_archivos as $archivo) {
            $response['repetidor_archivos_vehi'][] = [
                'nombre'  => $archivo['nombre'] ?? '',
                'archivo' => $archivo['archivo'] ?? '',
            ];
        }
    }

    // Obtener los datos del repetidor de Imagenes del Vehiculo
    $repetidor_imagenes = get_field('repetidor_imagenes_vehi', $post_id);
    $response['repetidor_imagenes_vehi'] = [];

    if (!empty($repetidor_imagenes)) {
        foreach ($repetidor_imagenes as $imagen) {
            $archivo = $imagen['archivo'] ?? '';
            // Si el valor es un ID, obtener la URL
            if (is_numeric($archivo)) {
                $archivo = wp_get_attachment_url($archivo);
            }

            $response['repetidor_imagenes_vehi'][] = [
                'nombre'  => $imagen['nombre'] ?? '',
                'archivo' => $archivo,
            ];
        }
    }


    wp_send_json_success($response);
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
