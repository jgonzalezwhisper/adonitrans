<?php 
add_action('wp_ajax_guardar_peajes', 'func_guardar_peajes');
add_action('wp_ajax_nopriv_guardar_peajes', 'func_guardar_peajes');

function func_guardar_peajes() {
    if (empty($_POST['post_id'])) {
        wp_send_json_error('Datos incompletos');
        return;
    }

    $post_id = intval($_POST['post_id']); // Asegura que es un número entero
    $nombres = $_POST['nomb_peaje'];
    $valores = $_POST['valor_peaje'];
    $comprobantes = $_FILES['comprobante_pago_peaje'];

    $peajes = [];

    foreach ($nombres as $index => $nombre) {
        $valor = $valores[$index];
        $comprobante_id = '';

        if (!empty($comprobantes['name'][$index])) {
            $file = [
                'name'     => $comprobantes['name'][$index],
                'type'     => $comprobantes['type'][$index],
                'tmp_name' => $comprobantes['tmp_name'][$index],
                'error'    => $comprobantes['error'][$index],
                'size'     => $comprobantes['size'][$index],
            ];

            $upload = wp_handle_upload($file, ['test_form' => false]);

            if (!isset($upload['error'])) {
                $attachment = [
                    'post_mime_type' => $upload['type'],
                    'post_title'     => sanitize_file_name($file['name']),
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                ];
                $attach_id = wp_insert_attachment($attachment, $upload['file']);

                require_once ABSPATH . 'wp-admin/includes/image.php';
                $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);

                $comprobante_id = $attach_id; // Guarda el ID en lugar de la URL
            }
        }

        if (!empty($nombre) && !empty($valor)) {
            $peajes[] = [
                'nombre'      => sanitize_text_field($nombre),
                'valor'       => sanitize_text_field($valor),
                'comprobante' => $comprobante_id, // Ahora guarda el ID de la imagen
            ];
        }
    }

    update_field('peajes_del_recorrido', $peajes, $post_id);

    wp_send_json_success('Peajes guardados correctamente');
}
