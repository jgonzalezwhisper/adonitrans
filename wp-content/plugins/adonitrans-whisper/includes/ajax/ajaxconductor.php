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

add_action('wp_ajax_finalizar_recorrido_conductor', 'func_finalizar_recorrido_conductor');
add_action('wp_ajax_nopriv_finalizar_recorrido_conductor', 'func_finalizar_recorrido_conductor');
function func_finalizar_recorrido_conductor() {
    if (empty($_POST['post_id'])) {
        wp_send_json_error('Datos incompletos');
        return;
    }

    $post_id = intval($_POST['post_id']);

    $contpeajes     = 0; /*Peajes*/
    $contrecogidos  = 0; /*Usuarios adicionales recogidos*/
    $trayecto_commen = isset($_POST['trayecto_commen']) ? $_POST['trayecto_commen'] : "";

    /*DATOS DE PEAJES*/
    $nombres = $_POST['nomb_peaje'];
    $valores = $_POST['valor_peaje'];
    $comprobantes = $_FILES['comprobante_pago_peaje'];

    if (!empty($_POST['valor_peaje'])) {
        $contpeajes = count($_POST['valor_peaje']);
    }
    
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

    /*USUARIOS ADICIONALES*/

    $barrio_inicial = get_field('barrio_inicial_recorrido');
    $barrio_final = get_field('barrio_final_recorrido');
    $usuarios_adicionales = get_field('usuarios_adicionales_recorrido');

    // Inicializar contadores
    $contador_coincidencias = 0;
    $contador_no_coincidencias = 0;

    // Verificar si el campo repetidor tiene datos
    if ($usuarios_adicionales) {
        foreach ($usuarios_adicionales as $usuario) {
            $origen = $usuario['origen'];
            $destino = $usuario['destino'];

            // Comparar los valores
            if ($barrio_inicial === $origen && $barrio_final === $destino) {
                $contador_coincidencias++;
            } else {
                $contador_no_coincidencias++;
            }
        }
    }
    if (isset($_POST['usuarios_adicionales']) && !empty($_POST['usuarios_adicionales'])) {

        $usuarios_adicionales = array_filter($_POST['usuarios_adicionales']);
        $contrecogidos = count($usuarios_adicionales);

        update_field('pasajeros_recogidos_recorrido', $contrecogidos, $post_id);
    }

    /*DATOS DE TIEMPO*/
    update_field('hora_llegada_recorrido', $_POST['horaLlegada'], $post_id);
    update_field('hora_inicio_recorrido_conductor', $_POST['horaInicio'], $post_id);
    update_field('hora_final_recorrido', $_POST['horaFin'], $post_id);
    update_field('tiempo_de_espera_recorrido', $_POST['minutosExtras'], $post_id);    

    /*TOMAR VALORES PARA ENVIAR LOS TOTALES*/
    $args_tarifa = array(
        'post_type'      => 'tarifa',
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => 'empresa_aplicar_tarifa',
                'value' => get_field('empresa_solicitante_recorrido',$post_id)->ID,
            ),
            array(
                'key'   => 'ano_aplicar_tarifa',
                'value' => date('Y'),
            ),
        ),
        'fields'         => 'ids',
        'posts_per_page' => 1,
    );

    $tarifas = get_posts($args_tarifa);
    $res_tarifa = [];

    if (empty($tarifas)) {
        wp_send_json_error('No hay tarifas configuradas para la empresa.');
        return;
    }

    if (!empty($tarifas)) {
        $tarifa_id = $tarifas[0];
        $tarifas_base_empresa = get_field('tarifas_base_empresa', $tarifa_id);

        /*error_log("ACF TARIFAS BASE EMPRESA ".print_r($tarifas_base_empresa,true));*/

        if ($tarifas_base_empresa) {            

            foreach ($tarifas_base_empresa as $tarifa_base) {
                $res_tarifa[] = [
                    'codigo' => $tarifa_base['codigo'],
                    'nombre' => $tarifa_base['nombre'],
                    'valor'  => $tarifa_base['valor'],
                ];
            }
        }
    }    

    /*Haciendo calculos*/
    $total_recorrido = 0;
    $costos = [
        ['motivo' => 'Recorrido Inicial', 'valor' => intval(get_field('valor_ruta_recorrido',$post_id)) ],
    ];

    $total_recorrido += intval(get_field('valor_ruta_recorrido',$post_id));

    foreach ($res_tarifa as $key => $value) {

        $nombre_tarifa_normalizado = strtolower($value['nombre']);

        if (strpos($nombre_tarifa_normalizado, 'pasajero adicional') !== false && $contador_no_coincidencias > 0) {
            $costos[] = ['motivo' => 'Usuario(s) Adicional(es)', 'valor' => intval($contrecogidos * $value['valor']) ];
            $total_recorrido += intval($contrecogidos * $value['valor']);
        }
        if (strpos($nombre_tarifa_normalizado, 'peajes') !== false && !empty($_POST['valor_peaje'][0])) {
            $total_peajes = array_sum($valores);
            $nume_peajes = $contpeajes * $value['valor'];
            $costos[] = ['motivo' => 'Peajes', 'valor' => intval($total_peajes + $nume_peajes) ];
            $total_recorrido += intval($total_peajes + $nume_peajes);
        }
        if (strpos($nombre_tarifa_normalizado, 'tiempo espera') !== false) {
            $costos[] = ['motivo' => 'Tiempo de Espera', 'valor' => intval($_POST['minutosExtras'] * $value['valor']) ];
            $total_recorrido += intval($_POST['minutosExtras'] * $value['valor']);
        }
    }

    $costos[] = ['motivo' => 'Total Recorrido', 'valor' => intval($total_recorrido) ];

    $arr_costos_calculados = [];

    // Recorrer los datos y agregarlos al array
    foreach ($costos as $dato) {
        $arr_costos_calculados[] = [
            'motivo' => $dato['motivo'],
            'valor' => $dato['valor']
        ];
    }

    update_field('costo_calculado_del_recorrido', $arr_costos_calculados, $post_id);
    update_field('comentario_conductor_final_recorrido', $trayecto_commen, $post_id);

    /*CAMBIO DE ESTADO*/
    update_field('estado_del_recorrido', 'Finalizado', $post_id);


    $response = array(
        'message' => 'Recorrido Finalizado Correctamente',
        'datos'    => $costos
    );

    wp_send_json_success($response);
}