<?php

add_action('wp_ajax_get_rutas', 'func_get_rutas');
add_action('wp_ajax_nopriv_get_rutas', 'func_get_rutas');
function func_get_rutas() {
    if (!isset($_POST['id_solicitante']) || empty($_POST['id_solicitante'])) {
        wp_send_json_error(['message' => 'ID de ciudad no enviado.']);
    }

    $id_solicitante = intval($_POST['id_solicitante']);
    // Obtener la empresa asociada al usuario
    $empresa_asociada = get_field('empresa_asociada_usuario', 'user_' . $id_solicitante);   
    
    $rutas = [];

    $args = [
        'post_type'      => 'tarifa',
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'posts_per_page' => 1,
        'meta_query'     => [
            [
                'key'     => 'empresa_aplicar_tarifa',
                'value'   => $empresa_asociada->ID,
                'compare' => '=LIKE'
            ]
        ]
    ];
    
    $tarifa_ids = get_posts($args);

    if (empty($tarifa_ids)) 
        wp_send_json_error(['message' => 'No se encontraron rutas para la empresa asociada.']);

    $tarifa_id = $tarifa_ids[0]; // Obtener el primer ID
    $repetidor = get_field('repetidor_de_tarifas', $tarifa_id);

    
    if ($repetidor) {
        foreach ($repetidor as $item) {
            $response[] = [
                'codigo' => $item['codigo'],
                'nombre_de_ruta' => $item['nombre_de_ruta'],
                'valor' => $item['valor'],
            ];
        }
    }
    wp_send_json_success($response);

    wp_die();
}

function consultar_ciudades_por_empresa() {
    // Verificar si se recibió el user_id por POST
    if (!isset($_POST['id_solicitante'])) {
        wp_send_json_error('No se recibió el user_id');
        return;
    }

    $user_id = intval($_POST['id_solicitante']);

    // Obtener la empresa asociada al usuario
    $empresa_asociada = get_field('empresa_asociada_usuario', 'user_' . $user_id);

    if (!$empresa_asociada) {
        wp_send_json_error('No se encontró una empresa asociada al usuario');
        return;
    }

    // Argumentos para WP_Query
    $argsciudadxcol = array(
        'post_type'      => 'ciudad', // Tipo de post
        'posts_per_page' => -1,       // Obtener todos los posts
        'meta_key'       => 'empresa_asociada_a_ciudad', // Campo ACF
        'meta_value'     => $empresa_asociada->ID,       // Valor a comparar
    );

    // Ejecutar la consulta
    $queryxcolciu = new WP_Query($argsciudadxcol);

    // Array para almacenar los resultados
    $resultados = array();

    // Verificar si hay posts
    if ($queryxcolciu->have_posts()) {
        while ($queryxcolciu->have_posts()) {
            $queryxcolciu->the_post();

            // Obtener el valor del campo ACF 'ciudad_para_empresa'
            $ciudad_para_empresa = get_field('ciudad_para_empresa', get_the_ID());

            // Almacenar en el array
            $resultados[] = array(
                'id' => get_the_ID(),
                'ciudad_para_empresa' => $ciudad_para_empresa
            );
        }
        wp_reset_postdata();
    }

    // Devolver los resultados en formato JSON
    wp_send_json_success($resultados);
}
add_action('wp_ajax_consultar_ciudades_por_empresa', 'consultar_ciudades_por_empresa');
add_action('wp_ajax_nopriv_consultar_ciudades_por_empresa', 'consultar_ciudades_por_empresa');

add_action('wp_ajax_get_barrios', 'obtener_barrios_por_ciudad');
add_action('wp_ajax_nopriv_get_barrios', 'obtener_barrios_por_ciudad');
function obtener_barrios_por_ciudad() {
    if (!isset($_POST['ciudad_id']) || empty($_POST['ciudad_id'])) {
        wp_send_json_error(['message' => 'ID de ciudad no enviado.']);
    }

    $ciudad_id = intval($_POST['ciudad_id']);
    $barrios = [];
    $repetidor_de_barrios = get_field('repetidor_de_barrios', $ciudad_id);
    // Array para almacenar los valores de zona y barrio
    $barrios = array();

    // Verificar si el repetidor tiene datos
    if ($repetidor_de_barrios && is_array($repetidor_de_barrios)) {
        // Iterar sobre cada fila del repetidor
        foreach ($repetidor_de_barrios as $fila) {
            // Obtener los valores de los subcampos 'zona' y 'barrio'
            $zona = isset($fila['zona']) ? $fila['zona'] : '';
            $barrio = isset($fila['barrio']) ? $fila['barrio'] : '';

            // Almacenar en el array
            $barrios[] = array(
                'zona' => $zona,
                'barrio' => $barrio
            );
        }
        // Función de comparación para ordenar por 'barrio'
        usort($barrios, function($a, $b) {
            return strcmp($a['barrio'], $b['barrio']);
        });
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
    if (isset($_POST['id_conductor_recorrido']) && !empty($_POST['id_conductor_recorrido'])) {
        $id_conductor_recorrido = sanitize_text_field($_POST['id_conductor_recorrido']);
    }    
    $ciudad_inicio = sanitize_text_field($_POST['ciudad_inicio']);
    $nombre_inicio = get_the_title( $ciudad_inicio );
    $barrio_inicio = sanitize_text_field($_POST['barrio_inicio']);
    if (!empty($_POST['barrio_zona_inicio'])) {
        $barrio_zona_inicio = sanitize_text_field($_POST['barrio_zona_inicio']);
    }
    if (!empty($_POST['barrio_zona_fin'])) {
        $barrio_zona_fin = sanitize_text_field($_POST['barrio_zona_fin']);
    }
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

    $estado_actual = get_field('estado_del_recorrido', $post_id);

    // Guardar campos personalizados usando ACF
    update_field('empresa_solicitante_recorrido', $empresa_solicitante_recorrido, $post_id);
    update_field('id_solicitante_recorrido', $id_solicitante_recorrido, $post_id);
    if (!empty($id_conductor_recorrido)) {
        update_field('id_conductor_recorrido', $id_conductor_recorrido, $post_id);

        $dato_vehiculo = obtener_vehiculo_asignado($fecha_inicio_recorrido, $id_conductor_recorrido);

        if ($dato_vehiculo) {
            update_field('id_vehiculo_recorrido', $dato_vehiculo['id_post_vehiculo'], $post_id);
            update_field('placa_vehiculo_recorrido', $dato_vehiculo['placa_vehiculo'], $post_id);
        }

        /*MENSAJE A CONDUCTOR CC OPERADORES*/
        $usuario        = get_field('id_solicitante_recorrido', $post_id);
        $nomb_usuario   = $usuario['user_firstname']." ".$usuario['user_lastname'];
        $mail_usuario   = $usuario['user_email'];

        $conductor      = get_field('id_conductor_recorrido', $post_id);
        $nomb_conductor = $conductor['user_firstname']." ".$conductor['user_lastname'];
        $mail_conductor = $conductor['user_email'];

        $empresa     = get_field('empresa_solicitante_recorrido', $post_id);
        $nomb_empresa   = $empresa->post_title;
        $usuarios_administradores = get_field('usuarios_administradores_empresa', $empresa) ?: [];
        $ids_usuarios = array_column($usuarios_administradores, 'ID');

        $mailemprcc = array_filter(array_map(function($user_id) {
            $user_data = get_userdata($user_id);
            return $user_data ? $user_data->user_email : null;
        }, $ids_usuarios));

        // Definir el asunto y cuerpo del mensaje
        $subject = 'Recorrido Asignado en AdoniGo';
        $message = [
            '<h2>Conductor Asignado</h2>',
            '<p>Un Conductor ha sido asignado al recorrido:</p>',
            sprintf('<p>ID Servicio: <strong>%s</strong></p>', esc_html($post_id)),
            sprintf('<p>Fecha Inicio: <strong>%s</strong></p>', esc_html($fecha_inicio_recorrido)),
            sprintf('<p>Hora Inicio: <strong>%s</strong></p>', esc_html($hora_inicio_recorrido)),
            sprintf('<p>Ciudad Inicio: <strong>%s</strong></p>', esc_html($nombre_inicio)),
            sprintf('<p>Barrio Inicio: <strong>%s</strong></p>', esc_html($barrio_inicio)),
            sprintf('<p>Nombre Conductor: <strong>%s</strong></p>', esc_html($nomb_conductor)),
            sprintf('<p>Placa Vehículo: <strong>%s</strong></p>', esc_html($dato_vehiculo['placa_vehiculo'])),
            sprintf('<p>Solicitante: <strong>%s</strong></p>', esc_html($nomb_usuario)),
            sprintf('<p>Empresa Solicitante: <strong>%s</strong></p>', esc_html($nomb_empresa)),
            
        ];

        /*Correo al usuario y administradores de la empresa el usuario*/
        send_email_notification($subject, $message, $mail_usuario, $mailemprcc);

        /*Correo al conductor y operadores de la empresa adonigo*/
        $roles = ['operaciones_1', 'operaciones_2'];
        $adonicc = get_mails_role($roles);
        send_email_notification($subject, $message, $mail_conductor, $adonicc);


        if (empty($estado_actual) || $estado_actual=='Por Asignar') {
            update_field('estado_del_recorrido', 'Pendiente', $post_id);
        }
    }
    update_field('ciudad_inicial_recorrido', $ciudad_inicio, $post_id);
    update_field('barrio_inicial_recorrido', $barrio_inicio, $post_id);
    update_field('ciudad_final_recorrido', $ciudad_fin, $post_id);
    update_field('barrio_final_recorrido', $barrio_fin, $post_id);
    update_field('fecha_inicio_recorrido', $fecha_inicio_recorrido, $post_id);
    update_field('hora_inicio_recorrido', $hora_inicio_recorrido, $post_id);
    

    if (empty(get_field('estado_del_recorrido', $post_id))) {
        update_field('estado_del_recorrido', 'Por Asignar', $post_id);
    }

    if (isset($_POST['ciudad_adicional_recorrido']) && isset($_POST['barrio_adicional_recorrido'])) {
        // Obtener los valores de los campos
        $ciudades = $_POST['ciudad_adicional_recorrido'];
        $barrios = $_POST['barrio_adicional_recorrido'];     
        if (!empty($_POST['barrio_adicional_zona'])) {
            $zonas = $_POST['barrio_adicional_zona'];
        }   
        $puntos_recorrido = array();

        $total_francjas = count($ciudades);
        for ($i = 0; $i < $total_francjas; $i++) {
            if (!empty($ciudades[$i]) && !empty($barrios[$i])) {
                $puntos_recorrido[] = array(
                    'ciudad' => $ciudades[$i],
                    'zona_barrio' => $zonas[$i],
                    'nombre_del_barrio' => $barrios[$i]
                );
            }
        }
        update_field('puntos_recorrido_adicionales', $puntos_recorrido, $post_id);
    }

    if (!empty($_POST['barrio_zona_inicio'])) {
        update_field('barrio_zona_inicial_recorrido', $barrio_zona_inicio, $post_id);
    }
    if (!empty($_POST['barrio_zona_fin'])) {
        update_field('barrio_final_recorrido_copiar', $barrio_zona_fin, $post_id);
    }

    if (!empty($centro_de_costo)) {
        update_field('centro_de_costo', $centro_de_costo, $post_id);
    }

    if (!empty($_POST['tarifa_codigo'])) {
        update_field('codigo_de_ruta_recorrido', $_POST['tarifa_codigo'], $post_id);
    }
    if (!empty($_POST['tarifa_ruta'])) {
        update_field('nombre_ruta_recorrido', $_POST['tarifa_ruta'], $post_id);
    }
    if (!empty($_POST['tarifa_valor'])) {
        update_field('valor_ruta_recorrido',$_POST['tarifa_valor'], $post_id);
    }

    $ciudad_fin = sanitize_text_field($_POST['ciudad_fin']);

    if (isset($_POST['sel_id_usuario_adicional'], $_POST['origen_adicional'], $_POST['direccion_origen_adicional'], $_POST['destino_adicional'], $_POST['direccion_destino_adicional'])) {
        $sel_id_usuario_adicional = $_POST['sel_id_usuario_adicional'];
        $origen_adicional = $_POST['origen_adicional'];
        $direccion_origen_adicional = $_POST['direccion_origen_adicional'];
        $destino_adicional = $_POST['destino_adicional'];
        $direccion_destino_adicional = $_POST['direccion_destino_adicional'];

        $total = count($sel_id_usuario_adicional);

        if ($total === count($origen_adicional) && $total === count($direccion_origen_adicional) && $total === count($destino_adicional) && $total === count($direccion_destino_adicional)) {
            $usuarios_adicionales = [];

            foreach ($sel_id_usuario_adicional as $key => $usuario_adicional) {
                $fila_origen = $origen_adicional[$key] ?? '';
                $fila_dirori = $direccion_origen_adicional[$key] ?? '';
                $fila_destin = $destino_adicional[$key] ?? '';
                $fila_dirdes = $direccion_destino_adicional[$key] ?? '';

                if (!empty($usuario_adicional) && !empty($fila_origen) && !empty($fila_destin)) {
                    $usuarios_adicionales[] = [
                        'id_usuario_adicional' => sanitize_text_field($usuario_adicional),
                        'origen' => sanitize_text_field($fila_origen),
                        'direccion_origen' => sanitize_text_field($fila_dirori),
                        'destino' => sanitize_text_field($fila_destin),
                        'direccion_destino' => sanitize_text_field($fila_dirdes),
                    ];
                }
            }
            update_field('usuarios_adicionales_recorrido', $usuarios_adicionales, $post_id);
        }
    }

    /*ENVIO DE CORREO*/
    $first_name = get_user_meta($id_solicitante_recorrido, 'first_name', true);
    $last_name = get_user_meta($id_solicitante_recorrido, 'last_name', true);

    $nombre_solicitante = "$first_name $last_name";

    // Definir el asunto y cuerpo del mensaje
    $subject = 'Recorrido '.$accion2.' en AdoniGo';
    $message = [
        '<h2>Recorrido '.$accion2.'.</h2>',
        '<p>Un Recorrido ha sido '.$accion2.' en AdoniGo. Los datos del Recorrido son:</p>',
        sprintf('<p>ID Servicio: <strong>%s</strong></p>', esc_html($post_id)),
        sprintf('<p>Fecha Inicio: <strong>%s</strong></p>', esc_html($fecha_inicio_recorrido)),
        sprintf('<p>Hora Inicio: <strong>%s</strong></p>', esc_html($hora_inicio_recorrido)),
        sprintf('<p>Ciudad Inicio: <strong>%s</strong></p>', esc_html($nombre_inicio)),
        sprintf('<p>Barrio Inicio: <strong>%s</strong></p>', esc_html($barrio_inicio)),
        sprintf('<p>Ciudad Fin: <strong>%s</strong></p>', esc_html($nombre_fin)),
        sprintf('<p>Solicitante: <strong>%s</strong></p>', esc_html($nombre_solicitante)),
        sprintf('<p>Empresa Solicitante: <strong>%s</strong></p>', esc_html(get_the_title( $empresa_solicitante_recorrido ))),        
    ];

    // Obtener el correo del usuario actual
    $current_user_email = wp_get_current_user()->user_email;

    // Asignar el correo del usuario a la variable $recipient_email
    $recipient_email = sanitize_email($current_user_email);

    // Obtener los roles del usuario actual
    $current_user_roles = wp_get_current_user()->roles;

    $allowed_roles = ['colaborador', 'empresa', 'administrator', 'operaciones_1'];

    // Comprobar si el usuario NO tiene el rol 'colaborador'
    if (!in_array('colaborador', $current_user_roles)) {
        // Si no es colaborador, añadir su correo a $combined_emails
        $user_solicitante = get_userdata($id_solicitante_recorrido);
        $combined_emails[] = $user_solicitante->user_email;
    }

    // Obtener usuarios con los roles 'operaciones_1' y 'administrator'
    $roles = ['operaciones_1', 'administrator'];
    $users = get_users([
        'role__in' => $roles,
        'fields'   => ['user_email']
    ]);

    // Obtener correos de los usuarios con los roles mencionados
    $cc_emails = wp_list_pluck($users, 'user_email'); 

    // Argumentos para la consulta de usuarios relacionados con el rol 'empresa'
    $args_emp = [
        'role'         => 'empresa',
        'meta_key'     => 'empresa_asociada_usuario',
        'meta_value'   => $empresa_solicitante_recorrido,
        'fields'       => 'user_email'
    ];

    // Realiza la consulta de usuarios relacionados con 'empresa'
    $users_emp = get_users($args_emp);

    // Combina los correos de ambos arrays
    $combined_emails = array_merge($cc_emails, wp_list_pluck($users_emp, 'user_email'));

    // Eliminar el correo del usuario actual si existe en el array combinado
    $cc_emails = array_diff($combined_emails, [$current_user_email]);


    // Llamar a la función de notificación con los nuevos parámetros
    send_email_notification($subject, $message, $recipient_email, $cc_emails);

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

    // Obtener datos del post usando ACF
    $fecha_inicio_recorrido = get_field('fecha_inicio_recorrido', $post_id);
    $fecha_inicio_recorrido = format_date_for_input($fecha_inicio_recorrido); // Si tienes esta función para formatear
    $hora_inicio_recorrido = format_time_input(get_field('hora_inicio_recorrido', $post_id));
    $ciudad_inicio = get_field('ciudad_inicial_recorrido', $post_id)->ID;
    $ciudad_inicio = get_the_title( $ciudad_inicio );
    $barrio_inicio = get_field('barrio_inicial_recorrido', $post_id);
    $ciudad_fin = get_field('ciudad_final_recorrido', $post_id)->ID;
    $ciudad_fin = get_the_title( $ciudad_fin );
    $id_solicitante_recorrido = get_the_title( $id_solicitante_recorrido );
    $empresa_solicitante_recorrido = get_field('empresa_asociada_usuario', 'user_' . $id_solicitante_recorrido);

    $first_name = get_user_meta($id_solicitante_recorrido, 'first_name', true);
    $last_name = get_user_meta($id_solicitante_recorrido, 'last_name', true);

    $nombre_soli = "$first_name $last_name";

    // Eliminar el post
    $deleted = wp_delete_post( $post_id, true );

    if ( $deleted ) {

        // Definir el asunto y cuerpo del mensaje
        $subject = 'Recorrido Eliminado en AdoniGo';

        $message = [
            '<h2>Recorrido Asignado.</h2>',
            '<p>Un Recorrido ha sido Asignado en AdoniGo. Los datos del Recorrido son:</p>',
            sprintf('<p>Fecha Inicio: <strong>%s</strong></p>', esc_html($fecha_inicio_recorrido)),
            sprintf('<p>Hora Inicio: <strong>%s</strong></p>', esc_html($hora_inicio_recorrido)),
            sprintf('<p>Ciudad Inicio: <strong>%s</strong></p>', esc_html($ciudad_inicio)),
            sprintf('<p>Barrio Inicio: <strong>%s</strong></p>', esc_html($barrio_inicio)),
            sprintf('<p>Ciudad Fin: <strong>%s</strong></p>', esc_html($ciudad_fin)),
            sprintf('<p>Solicitante: <strong>%s</strong></p>', esc_html($nombre_solicitante)),
            sprintf('<p>Empresa Solicitante: <strong>%s</strong></p>', esc_html(get_the_title( $empresa_solicitante_recorrido ))),        
        ];

        // Obtener el correo del usuario actual
        $current_user_email = wp_get_current_user()->user_email;

        // Asignar el correo del usuario a la variable $recipient_email
        $recipient_email = sanitize_email($current_user_email);

        // Obtener los roles del usuario actual
        $current_user_roles = wp_get_current_user()->roles;

        $allowed_roles = ['colaborador', 'empresa', 'administrator', 'operaciones_1'];

        // Comprobar si el usuario NO tiene el rol 'colaborador'
        if (!in_array('colaborador', $current_user_roles)) {
            // Si no es colaborador, añadir su correo a $combined_emails
            $user_solicitante = get_userdata($id_solicitante_recorrido);
            $combined_emails[] = $user_solicitante->user_email;
        }

        // Obtener usuarios con los roles 'operaciones_1' y 'administrator'
        $roles = ['operaciones_1', 'administrator'];
        $users = get_users([
            'role__in' => $roles,
            'fields'   => ['user_email']
        ]);

        // Obtener correos de los usuarios con los roles mencionados
        $cc_emails = wp_list_pluck($users, 'user_email'); 

        // Argumentos para la consulta de usuarios relacionados con el rol 'empresa'
        $args_emp = [
            'role'         => 'empresa',
            'meta_key'     => 'empresa_asociada_usuario',
            'meta_value'   => $empresa_solicitante_recorrido,
            'fields'       => 'user_email'
        ];

        // Realiza la consulta de usuarios relacionados con 'empresa'
        $users_emp = get_users($args_emp);

        // Combina los correos de ambos arrays
        $combined_emails = array_merge($cc_emails, wp_list_pluck($users_emp, 'user_email'));

        // Eliminar el correo del usuario actual si existe en el array combinado
        $cc_emails = array_diff($combined_emails, [$current_user_email]);


        // Llamar a la función de notificación con los nuevos parámetros
        send_email_notification($subject, $message, $recipient_email, $cc_emails);

        wp_send_json_success( array( 'message' => 'Recorrido eliminado exitosamente.', 'post_id' => $post_id ) );
    } else {
        wp_send_json_error( array( 'message' => 'Error al eliminar el recorrido.' ) );
    }
}
add_action( 'wp_ajax_delete_recorrido', 'handle_delete_recorrido' );
add_action( 'wp_ajax_nopriv_delete_recorrido', 'handle_delete_recorrido' );

/*ACCION AJAX PARA INICIAR RECORRIDO*/
function handle_iniciar_recorrido() {

    // Verificar el ID del post
    if ( empty( $_POST['post_id'] ) ) {
        wp_send_json_error( array( 'message' => 'ID de post inválido o no proporcionado.' ) );
    }

    $post_id = intval( $_POST['post_id'] );    

    // Verificar que el post existe y que es del tipo 'recorrido'
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'recorrido' ) {
        wp_send_json_error( array( 'message' => 'El post no existe o no es un Recorrido.' ) );
    }
    $estado_actual = get_field('estado_del_recorrido', $post_id);

    if ( $estado_actual=='Finalizado' || $estado_actual=='Cancelado' || $estado_actual=='En curso') {
        wp_send_json_error( array( 'message' => 'El Recorrido no se puede iniciar.' ) );
    }

    if (empty($estado_actual) || $estado_actual=='Pendiente') {
        update_field('estado_del_recorrido', 'En curso', $post_id);
    }

    /*MENSAJE A CONDUCTOR CC OPERADORES*/
    $usuario        = get_field('id_solicitante_recorrido', $post_id);
    $nomb_usuario   = $usuario['user_firstname']." ".$usuario['user_lastname'];
    $mail_usuario   = $usuario['user_email'];

    $conductor      = get_field('id_conductor_recorrido', $post_id);
    $nomb_conductor = $conductor['user_firstname']." ".$conductor['user_lastname'];
    $mail_conductor = $conductor['user_email'];

    $empresa     = get_field('empresa_solicitante_recorrido', $post_id);
    $nomb_empresa   = $empresa->post_title;
    $usuarios_administradores = get_field('usuarios_administradores_empresa', $empresa) ?: [];
    $ids_usuarios = array_column($usuarios_administradores, 'ID');

    $mailemprcc = array_filter(array_map(function($user_id) {
        $user_data = get_userdata($user_id);
        return $user_data ? $user_data->user_email : null;
    }, $ids_usuarios));

    // Definir el asunto y cuerpo del mensaje
    $subject = 'Recorrido Iniciado en AdoniGo';
    $message = [
        '<h2>El conductor ha llegado</h2>',
        '<p>El conductor ha llegado al punto de inicio:</p>',
        sprintf('<p>ID Servicio: <strong>%s</strong></p>', esc_html($post_id)),
        sprintf('<p>Nombre Conductor: <strong>%s</strong></p>', esc_html($nomb_conductor)),
        sprintf('<p>Solicitante: <strong>%s</strong></p>', esc_html($nomb_usuario)),
        sprintf('<p>Empresa Solicitante: <strong>%s</strong></p>', esc_html($nomb_empresa)),
    ];

    /*Correo al usuario y administradores de la empresa el usuario*/
    send_email_notification($subject, $message, $mail_usuario, $mailemprcc);

    /*Correo al conductores y operadores de la empresa adonigo*/
    $roles = ['operaciones_1', 'operaciones_2'];
    $adonicc = get_mails_role($roles);
    send_email_notification($subject, $message, $mail_conductor, $adonicc);

    wp_send_json_success( array( 'message' => 'Recorrido Iniciado.', 'post_id' => $post_id ) );      
}
add_action( 'wp_ajax_iniciar_recorrido', 'handle_iniciar_recorrido' );
add_action( 'wp_ajax_nopriv_iniciar_recorrido', 'handle_iniciar_recorrido' );

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
        'ciudad_inicio'          => get_field('ciudad_inicial_recorrido', $post_id)->ID,
        'barrio_inicio'          => get_field('barrio_inicial_recorrido', $post_id),
        'ciudad_fin'             => get_field('ciudad_final_recorrido', $post_id)->ID,
        'barrio_fin'             => get_field('barrio_final_recorrido', $post_id),
        'estado_del_recorrido'   => str_replace(' ', '-', strtolower(get_field('estado_del_recorrido', $post_id))),
    ];
    $response['usuarios_adicionales_recorrido'] = get_field('usuarios_adicionales_recorrido', $post_id);

    $puntos_recorrido = array();

    if (have_rows('puntos_recorrido_adicionales', $post_id)) {
        while (have_rows('puntos_recorrido_adicionales', $post_id)) {
            the_row();
            $puntos_recorrido[] = array(
                'ciudad'           => get_sub_field('ciudad')->ID,
                'zona_barrio'      => get_sub_field('zona_barrio'),
                'nombre_del_barrio'=> get_sub_field('nombre_del_barrio')
            );
        }
    }

    if (!empty($puntos_recorrido)) {
        $response['puntos_recorrido_adicionales'] = $puntos_recorrido;
    }

    // Si el usuario es administrador o empresa, añadir más datos
    if ($user_role === 'administrator' || $user_role === 'empresa' || $user_role === 'operaciones_1') {
        $response['id_solicitante_recorrido'] = get_field('id_solicitante_recorrido', $post_id)['ID'];
        $response['id_conductor_recorrido']   = get_field('id_conductor_recorrido', $post_id);
        $response['centro_de_costo']          = get_field('centro_de_costo', $post_id);
        $response['codigo_de_ruta_recorrido'] = get_field('codigo_de_ruta_recorrido', $post_id);
    }

    // Si el usuario es colaborador, solo incluir centro_de_costo
    if ($user_role === 'colaborador') {
        $response['centro_de_costo'] = get_field('centro_de_costo', $post_id);
    }

    // Devolver la respuesta en formato JSON
    wp_send_json_success($response);
}

add_action('wp_ajax_ver_recorrido_data', 'ver_recorrido_data_function');
add_action('wp_ajax_nopriv_ver_recorrido_data', 'ver_recorrido_data_function');
function ver_recorrido_data_function() {

    // Verificar que la solicitud sea válida
    $post_id = intval($_POST['post_id']);

    if (!$post_id || get_post_type($post_id) !== 'recorrido') {
        wp_send_json_error(['message' => 'Post no válido o no es un tipo de post recorrido.']);
    }

    $fecha = get_field('fecha_inicio_recorrido', $post_id); // Formato: d/m/Y
    $hora = get_field('hora_inicio_recorrido', $post_id);  // Formato: g:i a
    $fecha_final = "";

    if ($fecha && $hora) {
        $fecha_obj = DateTime::createFromFormat('d/m/Y', $fecha);
        if ($fecha_obj) {
            $dias = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'];
            $meses = ['January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre', 'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'];

            $dia = $dias[$fecha_obj->format('l')];
            $numero_dia = $fecha_obj->format('j');
            $mes = $meses[$fecha_obj->format('F')];
            $anio = $fecha_obj->format('Y');

            $fecha_final = "$dia, $numero_dia de $mes, $anio " . strtoupper($hora);
        }
    }

    $ciudad_inicio = get_field('ciudad_inicial_recorrido',$post_id);
    $nomciu_inicio = get_field('ciudad_para_empresa', $ciudad_inicio->ID);
    $barrio_inicio = get_field('barrio_inicial_recorrido',$post_id);
    $ciudad_fin = get_field('ciudad_final_recorrido',$post_id);
    $nomciu_fin = get_field('ciudad_para_empresa', $ciudad_fin->ID);
    $barrio_fin = get_field('barrio_final_recorrido',$post_id);

    $id_solicitante = get_field('id_solicitante_recorrido',$post_id)['ID'];

    $first_name = get_user_meta($id_solicitante, 'first_name', true);
    $last_name = get_user_meta($id_solicitante, 'last_name', true);

    $response = [
        'fecha_inicio_recorrido' => $fecha_final,
        'destino_inicio'         => "$nomciu_inicio - $barrio_inicio",
        'destino_final'          => "$nomciu_fin - $barrio_fin",
        'nomb_usuario'           => "$first_name $last_name",
    ];

    // Devolver la respuesta en formato JSON
    wp_send_json_success($response);
}

add_action('wp_ajax_get_colegas_empresa', 'get_colegas_empresa');
add_action('wp_ajax_nopriv_get_colegas_empresa', 'get_colegas_empresa');
function get_colegas_empresa() {

    $col_id = intval($_POST['colaborador_id']);
    $empresa_id = get_field('empresa_asociada_usuario', 'user_' . $col_id)->ID; 

    $argscol = [
        'role' => 'colaborador',
        'orderby' => 'display_name',
        'order'   => 'ASC',
        'meta_query' => array(
            array(
                'key'   => 'estado_usuario',
                'value' => 'Activo',
                'compare' => '='
            ),
            array(
                'key'   => 'empresa_asociada_usuario',
                'value' => $empresa_id,
                'compare' => '='
            )
        ),
        'exclude'   => [$col_id],
        'fields' => ['ID', 'user_email'],
    ];

    $user_query_col = new WP_User_Query($argscol);
    $colaboradores = $user_query_col->get_results();

    $response = array();
    foreach ($colaboradores as $colaborador) {
        $first_name = get_user_meta($colaborador->ID, 'first_name', true);
        $last_name = get_user_meta($colaborador->ID, 'last_name', true);
        $user_email = $colaborador->user_email; // El correo electrónico ya está disponible en el objeto

        $response[] = array(
            'ID' => $colaborador->ID,
            'display_name' => trim($first_name . ' ' . $last_name) ?: $colaborador->display_name,
            'user_email' => $user_email
        );
    }

    wp_send_json_success($response);
}