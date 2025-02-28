<?php
/* CREAR O EDITAR PAGOS */
add_action('wp_ajax_gestionar_pago', 'func_gestionar_pago');
add_action('wp_ajax_nopriv_gestionar_pago', 'func_gestionar_pago');
function func_gestionar_pago() {
    // Verificar nonce si es necesario (no se ha incluido en el ejemplo)
    if (!isset($_POST['nonce_gestion_pago']) || !wp_verify_nonce($_POST['nonce_gestion_pago'], 'action_gestion_pago')) {
        wp_send_json_error(['message' => 'Nonce no válido.']);
        wp_die();
    }

    // Obtener los datos del formulario
    $usuario_asociado_al_pago = sanitize_text_field($_POST['usuario_asociado_al_pago']);
    $estado_del_pago = (!empty($_POST['estado_del_pago']) && $_POST['estado_del_pago'] != 0) ? sanitize_text_field($_POST['estado_del_pago']) : 'Pendiente';
    $fecha_del_pago = sanitize_text_field($_POST['fecha_del_pago']);
    $comentario_del_pago = sanitize_text_field($_POST['comentario_del_pago']);

    $user_data = get_userdata($usuario_asociado_al_pago);

    // Verificar si el usuario existe
    if ($user_data) {
        $first_name = $user_data->first_name;
        $last_name  = $user_data->last_name;

        $nombre_completo = trim("$first_name $last_name");
        $nombre_completo = !empty($nombre_completo) ? $nombre_completo : 'N/A '.$usuario_asociado_al_pago;
    }

    $accion1 = "Crear";   
    $accion2 = "Creado";   

    if (isset($_POST['post-id']) && !empty($_POST['post-id'])) {
        $post_id = $_POST['post-id'];
        $accion1 = "Editar";   
        $accion2 = "Editado";
    } else {
        // Obtener la fecha actual sin la hora
        $fecha_hoy = date('Y-m-d'); // Formato de fecha para comparar con post_date

        // Buscar si ya existe un post con el mismo usuario y la misma fecha de creación
        $chk_pago_hoy = array(
            'post_type'      => 'pago',
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => 'usuario_asociado_al_pago', 
                    'value'   => $usuario_asociado_al_pago,
                    'compare' => '='
                ),
            ),
            'date_query' => array(
                array(
                    'after'     => $fecha_hoy . ' 00:00:00',
                    'before'    => $fecha_hoy . ' 23:59:59',
                    'inclusive' => true,
                ),
            ),
        );

        $qr_pago_hoy = new WP_Query($chk_pago_hoy);

        // Si ya existe un post con esos datos, se devuelve un error
        if ($qr_pago_hoy->have_posts()) {
            wp_send_json_error(array('message' => 'Ya existe un pago registrado para este usuario en la fecha de hoy.'));
        }
        // Crear el post
        $post_data = array(
            'post_type'   => 'pago',
            'post_status' => 'publish',
            'post_title'  => $nombre_completo.' '.date('d/m/Y'),
        );
        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => 'Error al crear el empresa.'));
        }
    }

    // Validar y guardar los valores en los campos ACF
    if (!empty($usuario_asociado_al_pago)) {
        update_field('usuario_asociado_al_pago', $usuario_asociado_al_pago, $post_id);
    }

    if (!empty($estado_del_pago)) {
        update_field('estado_del_pago', $estado_del_pago, $post_id);
    }

    if (!empty($fecha_del_pago)) {
        update_field('fecha_del_pago', $fecha_del_pago, $post_id);
    }

    if (!empty($comentario_del_pago)) {
        update_field('comentario_del_pago', $comentario_del_pago, $post_id);
    }

    // Subir cuenta de cobro y foto del pago solo si son nuevos o diferentes
    $campos = ['cuenta_de_cobro', 'foto_del_pago'];
    foreach ($campos as $campo) {
        if (!empty($_FILES[$campo]['name'])) {
            $archivo_id = subir_archivo($_FILES[$campo], $post_id, $campo);
            if ($archivo_id) {
                update_field($campo, $archivo_id, $post_id);
            }
        }
    }

    // Obtener correos
    $roles = ['administrator', 'operaciones_1', 'operaciones_2'];
    $adonicc = get_mails_role($roles);
    $mail_conductor = get_userdata($usuario_asociado_al_pago)->user_email ?? false;

    // Asunto y mensaje
    $subject_conductor = 'Estado de Pago '.$accion2;
    $message_conductor = [
        '<h2>Notificación de Pago</h2>',
        '<p>Se ha ' . strtolower($accion2) . ' un pago asociado al conductor ' . esc_html($nombre_completo) . ' con los siguientes detalles:</p>',
        sprintf('<p><strong>Estado del Pago:</strong> %s</p>', esc_html($estado_del_pago)),
        sprintf('<p><strong>Fecha del Pago:</strong> %s</p>', esc_html($fecha_del_pago)),
        sprintf('<p><strong>Comentario:</strong> %s</p>', esc_html(wp_strip_all_tags($comentario_del_pago))),
        '<br>',
        '<p><strong>Adjuntos disponibles: </strong></p>',
    ];

    foreach ($campos as $campo) {

        $archivo_id = get_field($campo, $post_id)['ID'];

        if ($archivo_id) {
            $archivo_url = wp_get_attachment_url($archivo_id);
            $campo_formateado = ucwords(str_replace('_', ' ', $campo));
            $message_conductor[] = sprintf('<p><a href="%s" target="_blank">%s</a></p>', esc_url($archivo_url), "Ver ".esc_html($campo_formateado));
        }
    }

    send_email_notification($subject_conductor, $message_conductor, $mail_conductor, $adonicc);

    // Devolver respuesta de éxito
    wp_send_json_success(['message' => 'Pago '.$accion2.' exitosamente']);
}

/*ELIMINAR PAGOS*/
function func_eliminar_pago() {

    // Verificar permisos del usuario
    if ( ! current_user_can( 'delete_posts' ) ) {
        wp_send_json_error( array( 'message' => 'Permisos insuficientes.' ) );
    }

    // Verificar el ID del post
    if ( ! isset( $_POST['post_id'] ) || empty( $_POST['post_id'] ) || ! is_numeric( $_POST['post_id'] ) ) {
        wp_send_json_error( array( 'message' => 'ID de post inválido o no proporcionado.' ) );
    }

    $post_id = intval( $_POST['post_id'] );

    // Verificar que el post existe y que es del tipo 'empresa'
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'pago' ) {
        wp_send_json_error( array( 'message' => 'El post no existe o no es un Pago.' ) );
    }

    // Lista de campos ACF que contienen archivos a eliminar
    $campos_archivos = ['cuenta_de_cobro', 'foto_del_pago'];

    foreach ($campos_archivos as $campo) {
        $archivo_id = get_field($campo, $post_id);

        if ($archivo_id) {
            wp_delete_attachment($archivo_id, true);
            delete_field($campo, $post_id);
        }
    }

    // Eliminar el post
    $deleted = wp_delete_post( $post_id, true );

    if ( $deleted ) {
        wp_send_json_success( array( 'message' => 'Pago y sus documentos eliminados exitosamente.', 'post_id' => $post_id ) );
    } else {
        wp_send_json_error( array( 'message' => 'Error al eliminar el Pago.' ) );
    }
}
add_action( 'wp_ajax_eliminar_pago', 'func_eliminar_pago' );
add_action( 'wp_ajax_nopriv_eliminar_pago', 'func_eliminar_pago' );


/*ACCION AJAX PARA OBTENER DATOS DE UNA EMPRESA*/
add_action('wp_ajax_cargar_datos_pago', 'func_cargar_datos_pago');
add_action('wp_ajax_nopriv_cargar_datos_pago', 'func_cargar_datos_pago');
function func_cargar_datos_pago() {
    $post_id = intval($_POST['post_id']);
    if (!$post_id || get_post_type($post_id) !== 'pago') {
        wp_send_json_error(['message' => 'Post no válido o no es un tipo de post Pago.']);
    }

    $campos_archivos = ['cuenta_de_cobro', 'foto_del_pago'];
    $documentos_data = [];

    foreach ($campos_archivos as $campo) {
        $archivo_id = get_field($campo, $post_id);

        if ($archivo_id) {
            $archivo_url = wp_get_attachment_url($archivo_id['ID']);

            $documentos_data[$campo] = [
                'id'  => $archivo_id['ID'],
                'url' => $archivo_url
            ];
        } else {
            $documentos_data[$campo] = null; // Si no hay archivo, se devuelve null
        }
    }

    $fecha_original = get_field('fecha_del_pago', $post_id); // "27/02/2025"

    if (!empty($fecha_original)) {
        $fecha_obj = DateTime::createFromFormat('d/m/Y', $fecha_original);
        $fecha_formateada = $fecha_obj ? $fecha_obj->format('Y-m-d') : null;
    } 
    else {
        $fecha_formateada = null;
    }

    wp_send_json_success([
        'comentario_del_pago'       => wp_strip_all_tags(get_field('comentario_del_pago', $post_id)), 
        'estado_del_pago'           => get_field('estado_del_pago', $post_id),
        'fecha_del_pago'            => $fecha_formateada,
        'usuario_asociado_al_pago'  => get_field('usuario_asociado_al_pago', $post_id)['ID'],
        'documentos_pago'           => $documentos_data, 
    ]);
}