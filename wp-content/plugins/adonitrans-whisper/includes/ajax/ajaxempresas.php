<?php

/* Acción AJAX para CREAR o EDITAR una EMPRESA */
add_action('wp_ajax_create_empresa', 'create_empresa_function');
add_action('wp_ajax_nopriv_create_empresa', 'create_empresa_function');
function create_empresa_function() {
    // Verificar nonce si es necesario (no se ha incluido en el ejemplo)
    if (!isset($_POST['create_empresa_nonce']) || !wp_verify_nonce($_POST['create_empresa_nonce'], 'create_empresa_action')) {
        wp_send_json_error(['message' => 'Nonce no válido.']);
        wp_die();
    }

    // Obtener los datos del formulario
    $estado_de_la_empresa = sanitize_text_field($_POST['estado_de_la_empresa']);
    $nombre_empresa = strtoupper(sanitize_text_field($_POST['nombre_empresa']));
    $administradores_empresa = array_map('intval', $_POST['administradores_empresa']);

    $accion1 = "Crear";   
    $accion2 = "Creada";   

    if (isset($_POST['empresa-id']) && !empty($_POST['empresa-id'])) {
        $post_id = $_POST['empresa-id'];
        $accion1 = "Editar";   
        $accion2 = "Editada"; 
    } else {
        // Crear el post
        $post_data = array(
            'post_type'   => 'empresa',
            'post_status' => 'publish',
            'post_title'  => $nombre_empresa,
        );
        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => 'Error al crear el empresa.'));
        }
    }

    // Guarda los valores en los campos ACF
    update_field('estado_de_la_empresa', $estado_de_la_empresa, $post_id);
    update_field('nombre_empresa', $nombre_empresa, $post_id);
    update_field('usuarios_administradores_empresa', $administradores_empresa, $post_id);

    // Verifica si los campos de los centros de costos existen y son arrays
    if (!empty($_POST['codigo_centro']) && !empty($_POST['nombre_centro']) && 
        is_array($_POST['codigo_centro']) && is_array($_POST['nombre_centro'])) {
        
        // Limpia los datos enviados
        $codigos_centro = array_map('sanitize_text_field', $_POST['codigo_centro']);
        $nombres_centro = array_map('sanitize_text_field', $_POST['nombre_centro']);

        // Asegúrate de que ambos arrays tengan la misma longitud
        $centros_de_costos = [];
        $cantidad = min(count($codigos_centro), count($nombres_centro));

        for ($i = 0; $i < $cantidad; $i++) {
            $centros_de_costos[] = [
                'codigo' => $codigos_centro[$i],
                'nombre' => $nombres_centro[$i],
            ];
        }

        // Guarda los datos en el campo repetidor
        if (!empty($centros_de_costos)) {
            update_field('centros_de_costos_empresa', $centros_de_costos, $post_id);
        }
    }

    // Manejo de documentos ELIMINADOS
    if (isset($_POST['documentos_eliminados']) && !empty($_POST['documentos_eliminados'])) {
        $documentos_eliminados = json_decode(stripslashes($_POST['documentos_eliminados']), true);
        if (!empty($documentos_eliminados) && is_array($documentos_eliminados)) {
            $existing_documents = get_field('documentos_de_la_empresa', $post_id);
            if ($existing_documents) {
                foreach ($existing_documents as $key => $documento) {
                    // Verificar si el documento está en la lista de documentos eliminados
                    if (isset($documento['documento']['ID']) && in_array((int) $documento['documento']['ID'], $documentos_eliminados)) {
                        // Eliminar el archivo de la biblioteca de medios
                        wp_delete_attachment($documento['documento']['ID'], true);

                        // Eliminar la fila del repetidor
                        unset($existing_documents[$key]);
                    }
                }
                // Reindexar el array y actualizar el campo repetidor
                update_field('documentos_de_la_empresa', array_values($existing_documents), $post_id);
            }
        }
    }

    // Verifica si se han subido nuevos documentos
    if (!empty($_FILES['documentos_de_la_empresa']['name'])) {
        $repeater_rows = [];

        foreach ($_FILES['documentos_de_la_empresa']['name'] as $index => $fileName) {
            if (!empty($fileName)) {
                $file = [
                    'name'     => $_FILES['documentos_de_la_empresa']['name'][$index],
                    'type'     => $_FILES['documentos_de_la_empresa']['type'][$index],
                    'tmp_name' => $_FILES['documentos_de_la_empresa']['tmp_name'][$index],
                    'error'    => $_FILES['documentos_de_la_empresa']['error'][$index],
                    'size'     => $_FILES['documentos_de_la_empresa']['size'][$index],
                ];

                // Subir archivo
                $upload = wp_handle_upload($file, ['test_form' => false]);

                if (!isset($upload['error']) && isset($upload['url'])) {
                    $attachment = [
                        'guid'           => $upload['url'],
                        'post_mime_type' => $upload['type'],
                        'post_title'     => sanitize_file_name($file['name']),
                        'post_content'   => '',
                        'post_status'    => 'inherit',
                    ];

                    $attachment_id = wp_insert_attachment($attachment, $upload['file']);

                    if (!is_wp_error($attachment_id)) {
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                        wp_update_attachment_metadata($attachment_id, $attachment_data);

                        $repeater_rows[] = [
                            'documento' => $attachment_id,
                        ];
                    }
                }
            }
        }

        if (!empty($repeater_rows)) {
            $existing_documents = get_field('documentos_de_la_empresa', $post_id);
            if ($existing_documents) {
                $repeater_rows = array_merge($existing_documents, $repeater_rows);
            }
            update_field('documentos_de_la_empresa', $repeater_rows, $post_id);
        }
    }

    // Devolver respuesta de éxito
    wp_send_json_success(['message' => 'Empresa '.$accion2.' exitosamente']);
}

// Eliminar empresa y sus documentos
function handle_delete_empresa() {

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
    if ( ! $post || $post->post_type !== 'empresa' ) {
        wp_send_json_error( array( 'message' => 'El post no existe o no es un Empresa.' ) );
    }

    // Obtener los documentos del repetidor
    $documentos = get_field( 'documentos_de_la_empresa', $post_id );

    if ( ! empty( $documentos ) && is_array( $documentos ) ) {
        foreach ( $documentos as $documento ) {
            if ( isset( $documento['documento']['ID'] ) ) {
                // Eliminar el archivo de la biblioteca de medios
                wp_delete_attachment( $documento['documento']['ID'], true );
            }
        }
    }

    // Eliminar el post
    $deleted = wp_delete_post( $post_id, true );

    if ( $deleted ) {
        wp_send_json_success( array( 'message' => 'Empresa y sus documentos eliminados exitosamente.', 'post_id' => $post_id ) );
    } else {
        wp_send_json_error( array( 'message' => 'Error al eliminar el empresa.' ) );
    }
}
add_action( 'wp_ajax_delete_empresa', 'handle_delete_empresa' );
add_action( 'wp_ajax_nopriv_delete_empresa', 'handle_delete_empresa' );


/*ACCION AJAX PARA OBTENER DATOS DE UNA EMPRESA*/
add_action('wp_ajax_load_empresa_data', 'load_empresa_data_function');
add_action('wp_ajax_nopriv_load_empresa_data', 'load_empresa_data_function');
function load_empresa_data_function() {
    $post_id = intval($_POST['post_id']);
    if (!$post_id || get_post_type($post_id) !== 'empresa') {
        wp_send_json_error(['message' => 'Post no válido o no es un tipo de post empresa.']);
    }

    // Obtener datos del campo repetidor 'documentos_de_la_empresa'
    $documentos = get_field('documentos_de_la_empresa', $post_id);
    $documentos_data = [];

    if ($documentos) {
        foreach ($documentos as $documento) {
            if (isset($documento['documento']) && is_array($documento['documento'])) {
                $documentos_data[] = [
                    'id' => $documento['documento']['id'],   // URL del archivo
                    'url' => $documento['documento']['url'],   // URL del archivo
                    'nombre' => $documento['documento']['title']  // Nombre del archivo
                ];
            }
        }
    }


    wp_send_json_success([
        'administradores_empresa'      => get_field('usuarios_administradores_empresa', $post_id), 
        'estado_de_la_empresa'         => get_field('estado_de_la_empresa', $post_id),
        'nombre_empresa'               => get_the_title($post_id),        
        'centros_de_costos_empresa'    => get_field("centros_de_costos_empresa", $post_id),
        'documentos_de_la_empresa'     => $documentos_data, 
    ]);
}

// Obtener lista de empresas
add_action('wp_ajax_obtener_empresas', function () {
    $empresas = [];
    $query = new WP_Query(['post_type' => 'empresa', 'posts_per_page' => -1]);
    while ($query->have_posts()) {
        $query->the_post();
        $empresas[] = [
            'id' => get_the_ID(),
            'placa' => get_post_meta(get_the_ID(), 'placa', true),
            'modelo' => get_post_meta(get_the_ID(), 'modelo', true),
            'marca' => get_post_meta(get_the_ID(), 'marca', true),
            'estado' => get_post_meta(get_the_ID(), 'estado', true),
        ];
    }
    wp_reset_postdata();
    wp_send_json($empresas);
});

// Obtener datos de un empresa
add_action('wp_ajax_obtener_empresa', function () {
    $empresa_id = intval($_POST['id']);
    $empresa = [
        'id' => $empresa_id,
        'placa' => get_post_meta($empresa_id, 'placa', true),
        'modelo' => get_post_meta($empresa_id, 'modelo', true),
        'marca' => get_post_meta($empresa_id, 'marca', true),
        'estado' => get_post_meta($empresa_id, 'estado', true),
    ];
    wp_send_json($empresa);
});

// Activar/Inactivar empresa
add_action('wp_ajax_toggle_estado_empresa', function () {
    $empresa_id = intval($_POST['id']);
    $estado = get_post_meta($empresa_id, 'estado', true) === 'activo' ? 'inactivo' : 'activo';
    update_post_meta($empresa_id, 'estado', $estado);
    wp_send_json_success();
});
