<?php

function convertir_a_24h($hora) {
    return date("H:i:s", strtotime($hora));
}

function obtener_vehiculo_asignado($fecha_consulta, $id_conductor_asignado) {

    $fecha_consulta_format = date('Y-m-d', strtotime($fecha_consulta));    
    $args = [
        'post_type'      => 'asignacion',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query' => [
        'relation' => 'AND',
            [
                'key' => 'id_conductor_asignado',
                'value' => $id_conductor_asignado,
                'compare' => '='
            ],
            [
                'key' => 'inicio_semana_asignacion',
                'value' => $fecha_consulta_format,
                'compare' => '<=', // La fecha de inicio debe ser menor o igual a la fecha de solicitud
                'type'    => 'DATE'
            ],
            [
                'key' => 'fin_semana_asignacion',
                'value' => $fecha_consulta_format,
                'compare' => '>=', // La fecha de fin debe ser mayor o igual a la fecha de solicitud
                'type'    => 'DATE'
            ]
        ]
    ];
    
    $query = new WP_Query($args);
    $ids_asig = $query->posts;
    wp_reset_postdata();

    $asignaciones = get_field('asignaciones_de_la_semana', $ids_asig[0]);

    if ($asignaciones) {
        foreach ($asignaciones as $asignacion) {
            $inicio_asignacion = DateTime::createFromFormat('d/m/Y', $asignacion['dia_inicio_de_asignacion'])->format('Y-m-d');
            $fin_asignacion = DateTime::createFromFormat('d/m/Y', $asignacion['dia_fin_de_asignacion'])->format('Y-m-d');

            if ($fecha_consulta_format >= $inicio_asignacion && $fecha_consulta_format <= $fin_asignacion) {
                return [
                    'id_post_vehiculo' => $asignacion['id_post_vehiculo'],
                    'placa_vehiculo'   => $asignacion['placa_vehiculo']
                ];
            }
        }
    }     
    
    return null;
}

function obtener_conductores_asignados() {
    // Verificar si se ha enviado la fecha de solicitud por AJAX
    if (!isset($_POST['id_recorrido'])) {
        wp_send_json_error('Fecha de solicitud no proporcionada.');
    }

    $id_recorrido = intval($_POST['id_recorrido']);
    $fecha_solicitud = get_field('fecha_inicio_recorrido', $id_recorrido);
    $fecha_solicitud = date('Y-m-d', strtotime(str_replace('/', '-', $fecha_solicitud)));
    $hora_solicitud = get_field('hora_inicio_recorrido', $id_recorrido);
    $franjas_general = get_field('franjas_horas_trabajo', 'option');
    $hora_solicitud_24h = convertir_a_24h($hora_solicitud);
    $franja_consulta ="";

    foreach ($franjas_general as $franja) {
        $hora_inicio_franja = convertir_a_24h($franja['hora_inicio']);
        $hora_fin_franja = convertir_a_24h($franja['hora_fin']);
        
        if ($hora_solicitud_24h >= $hora_inicio_franja && $hora_solicitud_24h < $hora_fin_franja) {
            $franja_consulta = $franja['nombre'];
            break;
        }
    }

    $argscon = array(
        'role'    => 'conductor',
        'orderby' => 'display_name',
        'order'   => 'ASC',
        'meta_query' => array(
            array(
                'key'   => 'estado_usuario',
                'value' => 'Activo',
                'compare' => '='
            )
        ),
        'fields' => 'ID'
    );

    $user_query = new WP_User_Query($argscon);
    $conductores = $user_query->get_results();

    // Array para almacenar los conductores que cumplen con las condiciones
    $conductores_asignados = array();

    foreach ($conductores as $conductor) {
        $conductor_id = $conductor;

        // Obtener las asignaciones del conductor donde la fecha de solicitud esté entre inicio_semana_asignacion y fin_semana_asignacion
        $args_asignaciones = [
            'post_type'  => 'asignacion',
            'post_status' => 'publish',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'id_conductor_asignado',
                    'value' => $conductor_id,
                    'compare' => '='
                ],
                [
                    'key' => 'inicio_semana_asignacion',
                    'value' => $fecha_solicitud,
                    'compare' => '<=', // La fecha de inicio debe ser menor o igual a la fecha de solicitud
                    'type'    => 'DATE'
                ],
                [
                    'key' => 'fin_semana_asignacion',
                    'value' => $fecha_solicitud,
                    'compare' => '>=', // La fecha de fin debe ser mayor o igual a la fecha de solicitud
                    'type'    => 'DATE'
                ]
            ]
        ];

        $query_asignaciones = new WP_Query($args_asignaciones);

        if ($query_asignaciones->have_posts()) {
            while ($query_asignaciones->have_posts()) {
                $query_asignaciones->the_post();

                // Obtener el campo repetidor de asignaciones de la semana
                $asignaciones_semana = get_field('asignaciones_de_la_semana');

                if ($asignaciones_semana) {
                    foreach ($asignaciones_semana as $asignacion) {
                        $dia_inicio = $asignacion['dia_inicio_de_asignacion'];
                        $dia_fin = $asignacion['dia_fin_de_asignacion'];
                        $franja_horaria = $asignacion['franja_horaria_asignacion'];

                        // Convertimos las fechas para la comparación
                        $fecha_solicitud_dt = DateTime::createFromFormat('Y-m-d', $fecha_solicitud);
                        $dia_inicio_dt = DateTime::createFromFormat('d/m/Y', $dia_inicio);
                        $dia_fin_dt = DateTime::createFromFormat('d/m/Y', $dia_fin);

                        // Verificar si la fecha de solicitud está dentro del rango de días de la asignación
                        if ($fecha_solicitud_dt >= $dia_inicio_dt && $fecha_solicitud_dt <= $dia_fin_dt) {
                            if ($franja_horaria == $franja_consulta) {
                                // Convertimos la hora de solicitud al formato de comparación
                                $hora_solicitud_dt = DateTime::createFromFormat('H:i', $hora_solicitud);

                                // Buscar la franja válida en la que cae la hora de solicitud
                                foreach ($franjas_general as $franja) {
                                    $hora_inicio_dt = DateTime::createFromFormat('H:i', $franja['hora_inicio']);
                                    $hora_fin_dt = DateTime::createFromFormat('H:i', $franja['hora_fin']);

                                    // Si la hora de solicitud está dentro del rango de la franja horaria
                                    if ($hora_solicitud_dt >= $hora_inicio_dt && $hora_solicitud_dt <= $hora_fin_dt) {
                                        // Verificar si la franja horaria del conductor coincide con la franja de solicitud
                                        if ($franja['nombre'] == $franja_horaria) {
                                            $nombre = get_user_meta($conductor, 'first_name', true);
                                            $apellido = get_user_meta($conductor, 'last_name', true);
                                            $conductor_info = array(
                                                'id' => $conductor,
                                                'nombre' => $nombre . " " . $apellido
                                            );

                                            // Evitar duplicados en $conductores_asignados
                                            if (!in_array($conductor_info, $conductores_asignados)) {
                                                $conductores_asignados[] = $conductor_info;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    wp_send_json_error(array('message' => 'No hay asignaciones para la fecha, consulta a soporte.'));
                }
            }
        }
        wp_reset_postdata();
    }

    // Devolver los conductores asignados en formato JSON
    wp_send_json_success($conductores_asignados);
}
add_action('wp_ajax_obtener_conductores_asignados', 'obtener_conductores_asignados');
add_action('wp_ajax_nopriv_obtener_conductores_asignados', 'obtener_conductores_asignados');

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
    $accion2 = "Creada";   

    if (isset($_POST['asignacion-id']) && !empty($_POST['asignacion-id'])) {
        $post_id = $_POST['asignacion-id'];
        $accion1 = "Editar";   
        $accion2 = "Editada"; 
    } else {

        // Verificar si ya existe un post con el mismo conductor y rango de fechas
        $args = [
            'post_type'  => 'asignacion',
            'post_status' => 'publish',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'id_conductor_asignado',
                    'value' => $id_conductor_asignado,
                    'compare' => '='
                ],
                [
                    'key' => 'inicio_semana_asignacion',
                    'value' => $inicio_semana_asignacion,
                    'compare' => '='
                ],
                [
                    'key' => 'fin_semana_asignacion',
                    'value' => $fin_semana_asignacion,
                    'compare' => '='
                ]
            ]
        ];
        
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            wp_send_json_error(['message' => 'Ya existe una asignación con este conductor y rango de fechas.']);
            wp_die();
        }


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

    if (isset($_POST['dia_inicio_de_asignacion'], $_POST['dia_fin_de_asignacion'], $_POST['franja_horaria_asignacion'], $_POST['vehiculo_asignado'])) {
        $dias_inicio = $_POST['dia_inicio_de_asignacion'];
        $dias_fin = $_POST['dia_fin_de_asignacion'];
        $franjas_horarias = $_POST['franja_horaria_asignacion'];
        $vehiculo_asignado = $_POST['vehiculo_asignado'];

        if (count($dias_inicio) === count($dias_fin) && count($dias_inicio) === count($franjas_horarias) && count($dias_inicio) === count($vehiculo_asignado)) {
            $asignaciones = [];

            foreach ($dias_inicio as $key => $dia_inicio) {
                $dia_fin = $dias_fin[$key] ?? '';
                $franja_horaria = $franjas_horarias[$key] ?? '';
                $id_post_vehiculo = $vehiculo_asignado[$key] ?? '';
                $placa_vehiculo = get_field('placa_vehiculo', $id_post_vehiculo);

                if (!empty($dia_inicio) && !empty($dia_fin) && !empty($franja_horaria) && !empty($vehiculo_asignado)) {
                    $asignaciones[] = [
                        'dia_inicio_de_asignacion' => sanitize_text_field($dia_inicio),
                        'dia_fin_de_asignacion' => sanitize_text_field($dia_fin),
                        'franja_horaria_asignacion' => sanitize_text_field($franja_horaria),
                        'id_post_vehiculo' => sanitize_text_field($id_post_vehiculo),
                        'placa_vehiculo' => sanitize_text_field($placa_vehiculo),
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
            $id_post_vehiculo = get_sub_field('id_post_vehiculo');
            $placa_vehiculo = get_sub_field('placa_vehiculo');

            $asignaciones_de_la_semana[] = [
                'dia_inicio_de_asignacion' => format_date_for_input($dia_inicio_de_asignacion),
                'dia_fin_de_asignacion' => format_date_for_input($dia_fin_de_asignacion),
                'franja_horaria_asignacion' => $franja_horaria_asignacion,
                'id_post_vehiculo' => $id_post_vehiculo,
                'placa_vehiculo' => $placa_vehiculo,
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

/*GENERAR JSON PARA MOSTRAR EN FULL CALENDAR*/
add_action('wp_ajax_filtrar_asignaciones', 'func_filtrar_asignaciones');
add_action('wp_ajax_nopriv_filtrar_asignaciones', 'func_filtrar_asignaciones');
function func_filtrar_asignaciones() {
    // Verifica y sanitiza los datos recibidos
    $conductor_id = isset($_POST['conductor_id']) ? intval($_POST['conductor_id']) : 0;

    if (!$conductor_id) {
        wp_send_json_error('No se proporcionó un ID de conductor válido.');
    }

    // Consulta para obtener los posts relacionados
    $args = array(
        'post_type'      => 'asignacion',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'id_conductor_asignado',
                'value'   => $conductor_id,
                'compare' => '='
            )
        )
    );
    $query = new WP_Query($args);
    $events = array();

    // Obtener datos del usuario
    $user_info = get_userdata($conductor_id);

    if ($user_info) {
        // Obtener el primer nombre
        $first_name = get_user_meta($conductor_id, 'first_name', true);

        // Obtener el correo electrónico
        $email = $user_info->user_email;
    }

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            // Obtén el campo repetidor
            $repetidor = get_field('asignaciones_de_la_semana'); // Usando ACF

            if ($repetidor) {
                foreach ($repetidor as $asignacion) {
                    $events[] = array(
                        'title'  =>  $asignacion['franja_horaria_asignacion'],
                        'namcond'=> $first_name,
                        'mailcon'=> $email,
                        'start'  => format_date_for_input($asignacion['dia_inicio_de_asignacion']),
                        'end'    => format_date_for_input($asignacion['dia_fin_de_asignacion']),
                    );
                }
            }
        }
    }

    wp_reset_postdata();

    // Enviar los eventos como respuesta en formato JSON
    wp_send_json($events);
}

add_action('wp_ajax_get_colaboradores_by_empresa', 'get_colaboradores_by_empresa');
add_action('wp_ajax_nopriv_get_colaboradores_by_empresa', 'get_colaboradores_by_empresa');
function get_colaboradores_by_empresa() {
    $empresa_id = intval($_POST['empresa_id']);

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

// Incluir las clases necesarias de PhpSpreadsheet
require PATH_ADONITRANSPLUG . 'includes/librerias/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/* GENERAR EXCEL PARA EL REPORTE */
function func_gen_reporte_excel() {

    if ( !isset($_POST['tipo-consulta']) || !isset($_POST['desde_formexcel']) || !isset($_POST['hasta_formexcel']) ) {
        wp_send_json_error('Faltan datos necesarios para generar el reporte.');
    }

    if ( empty($_POST['tipo-consulta']) || empty($_POST['desde_formexcel']) || empty($_POST['hasta_formexcel']) ) {
        wp_send_json_error('No se recibió la suficiente información para generar el reporte.');
    }

    $tipo_consulta = $_POST['tipo-consulta'];
    $desde_formexcel = $_POST['desde_formexcel'];
    $hasta_formexcel = $_POST['hasta_formexcel'];

    if ($tipo_consulta == 'conductor') {
        if ( !isset($_POST['selexc_conductor']) || empty($_POST['selexc_conductor']) ) {
            wp_send_json_error('Es necesario seleccionar un Conductor.');
        }

        $id_conductor = intval($_POST['selexc_conductor']); 

        $first_name = get_user_meta($id_conductor, 'first_name', true);
        $last_name = get_user_meta($id_conductor, 'last_name', true);

        $query = new WP_Query([
            'post_type'  => 'asignacion',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'id_conductor_asignado',
                    'value'   => $id_conductor,
                    'compare' => '='
                ],
                [
                    'key'     => 'inicio_semana_asignacion',
                    'value'   => $desde_formexcel,
                    'compare' => '>='
                ],
                [
                    'key'     => 'fin_semana_asignacion',
                    'value'   => $hasta_formexcel,
                    'compare' => '<='
                ],
            ],
            'fields'         => 'ids',
        ]);

        error_log(print_r($desde_formexcel,true));
        error_log(print_r($hasta_formexcel,true));
        error_log(print_r($query,true));

        if ($query->have_posts()) {
            $headers = ['Fecha Inicio', 'Fecha Final', 'Franja Horaria', 'Placa Vehículo'];
            $filtpor = "Conductor: $first_name $last_name";
            $data = [];

            // Recorrer los posts
            foreach ($query->posts as $post_id) {
                $repetidor = get_field('asignaciones_de_la_semana', $post_id); // Recuperar el campo ACF

                if ($repetidor) {
                    foreach ($repetidor as $fila) {
                        $data[] = [
                            $fila['dia_inicio_de_asignacion'],
                            $fila['dia_fin_de_asignacion'],
                            $fila['franja_horaria_asignacion'],
                            $fila['placa_vehiculo']
                        ];
                    }
                }
            }

            wp_reset_postdata();
        } else {
            wp_send_json_error('No se encontraron datos para generar el reporte.');
        }
    } 
    else if ($tipo_consulta == 'recxconductor') {
        if ( !isset($_POST['selexc_conductor']) || empty($_POST['selexc_conductor']) ) {
            wp_send_json_error('Es necesario seleccionar un Conductor.');
        }

        $id_conductor = intval($_POST['selexc_conductor']); 

        $first_name = get_user_meta($id_conductor, 'first_name', true);
        $last_name = get_user_meta($id_conductor, 'last_name', true);

        $args = [
            'post_type'      => 'recorrido',
            'post_status'    => 'publish',
            'fields'         => 'ids', // Solo retorna los IDs
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => 'id_conductor_recorrido',
                    'value'   => $id_conductor,
                    'compare' => '='
                ],
                [
                    'key'     => 'fecha_inicio_recorrido',
                    'value'   => [$desde_formexcel, $hasta_formexcel],
                    'compare' => 'BETWEEN',
                    'type'    => 'DATE'
                ],
                [
                    'key'     => 'estado_del_recorrido',
                    'value'   => 'Finalizado',
                    'compare' => '='
                ]
            ]
        ];

        $query = new WP_Query($args);
        $post_ids = $query->posts; // Obtiene solo los IDs

        if ($query->have_posts()) {
            $headers = [
                'Ruta',
                'Valor Ruta',
                'Fecha Recorrido',
                'T. Espera',
                '# Recorrido',
                'Categoria',
                'HI. Servicio',
                'HF. Servicio',
                'Serv. Trasmilenio (Horas)',
                'Barrio',
                'Placa',
                'Nombre del Conductor',
                'Observaciones',
                'Nombre del Usuario'
            ];
            $filtpor = "Conductor: $first_name $last_name";
            $data = [];

            // Recorrer los posts
            foreach ($post_ids as $post_id) {
                $adicionales_recorrido = get_field('adicionales_realizados_recorrido', $post_id);
                $ciudad_inicio = get_field('ciudad_para_empresa',get_field('ciudad_inicial_recorrido', $post_id));
                $barrio_inicio = get_field('barrio_inicial_recorrido', $post_id);
                $ciudad_fin = get_field('ciudad_para_empresa',get_field('ciudad_final_recorrido', $post_id));;
                $barrio_fin = get_field('barrio_final_recorrido', $post_id);

                $id_colaborador = get_field('id_solicitante_recorrido', $post_id)['ID'];

                $nombre_usuario = get_user_meta($id_colaborador, 'first_name', true)." ".get_user_meta($id_colaborador, 'last_name', true);

                $nombre_conductor = "Sin Asignar";

                if (get_field('id_conductor_recorrido', $post_id)) {
                    $id_conductor_recorrido = get_field('id_conductor_recorrido', $post_id)['ID'];
                    $nombre_conductor = get_user_meta($id_conductor_recorrido, 'first_name', true)." ".get_user_meta($id_conductor_recorrido, 'last_name', true);
                }



                $data[] = [
                    "$ciudad_inicio ($barrio_inicio) - $ciudad_fin ($barrio_fin)",
                    '100000',
                    get_field('fecha_inicio_recorrido', $post_id),
                    '0',
                    $post_id,
                    'Cat',
                    get_field('hora_inicio_recorrido', $post_id),
                    get_field('hora_final_recorrido', $post_id),
                    "N/A",
                    $barrio_inicio,
                    get_field('placa_vehiculo_recorrido', $post_id),
                    $nombre_conductor,
                    "N/A",
                    $nombre_usuario
                ];

                if ($adicionales_recorrido) {
                    foreach ($adicionales_recorrido as $adicional) {
                        $data[] = [
                            $adicional['nombre_adicional'],
                            $adicional['valor'],
                            get_field('fecha_inicio_recorrido', $post_id),
                            '0',
                            $post_id,
                            'Cat',
                            get_field('hora_inicio_recorrido', $post_id),
                            get_field('hora_final_recorrido', $post_id),
                            "N/A",
                            $barrio_inicio,
                            get_field('placa_vehiculo_recorrido', $post_id),
                            $nombre_conductor,
                            "N/A",
                            $nombre_usuario
                        ];
                    }
                }
            }

            wp_reset_postdata(); // Restablecer la consulta
        } else {
            wp_send_json_error('No se encontraron datos para generar el reporte.');
        }
    }
    else if ($tipo_consulta == 'empresa') {
        if ( !isset($_POST['selexc_empresa']) || empty($_POST['selexc_empresa']) ) {
            wp_send_json_error('Es necesario seleccionar una Empresa.');
        }

        $id_empresa = intval($_POST['selexc_empresa']); 

        $query = new WP_Query([
            'post_type'      => 'recorrido', // Cambia esto al tipo de post que corresponda
            'post_status'    => 'publish',
            'posts_per_page' => -1,    // Sin límite, para obtener todos los resultados
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => 'empresa_solicitante_recorrido',
                    'value'   => 'i:' . $id_empresa . ';',
                    'compare' => 'LIKE'
                ],
                [
                    'key'     => 'fecha_inicio_recorrido',
                    'value'   => [$desde_formexcel, $hasta_formexcel],
                    'compare' => 'BETWEEN',
                    'type'    => 'DATE' // Especifica que las fechas son de tipo "DATE"
                ],
            ],
            'fields'         => 'ids', // Solo obtener IDs
        ]);

        if ($query->have_posts()) {
            $headers = ['Solicitante', 'Conductor', 'Placa Vehículo', 'Estado', 'Fecha Inicio', 'Hora Inicio', 'Ciudad Inicio', 'Barrio Inicio', 'Centro de Costo'];
            $filtpor = 'Empresa: ' . get_the_title( $id_empresa );
            $data = []; // Inicializar fuera del foreach para agrupar todas las asignaciones

            // Recorrer los posts
            foreach ($query->posts as $post_id) {

                $id_solicitante_recorrido = get_field('id_solicitante_recorrido', $post_id)['ID'];
                $nombre_solicitante = get_user_meta($id_solicitante_recorrido, 'first_name', true)." ".get_user_meta($id_solicitante_recorrido, 'last_name', true);

                $nombre_conductor = "Sin Asignar";

                if (get_field('id_conductor_recorrido', $post_id)) {
                    $id_conductor_recorrido = get_field('id_conductor_recorrido', $post_id)['ID'];
                    $nombre_conductor = get_user_meta($id_conductor_recorrido, 'first_name', true)." ".get_user_meta($id_conductor_recorrido, 'last_name', true);
                }

                $nombciu_inicio = get_field('ciudad_inicial_recorrido', $post_id)->ID;
                $nombciu_inicio = get_field('ciudad_para_empresa', $nombciu_inicio);

                $data[] = [
                    $nombre_solicitante,
                    $nombre_conductor,
                    get_field('placa_vehiculo_recorrido', $post_id),
                    get_field('estado_del_recorrido', $post_id),
                    get_field('fecha_inicio_recorrido', $post_id),
                    get_field('hora_inicio_recorrido', $post_id),
                    $nombciu_inicio,
                    get_field('barrio_inicial_recorrido', $post_id),
                    get_field('centro_de_costo', $post_id),
                ];
            }

            wp_reset_postdata();
        } else {
            wp_send_json_error('No se encontraron datos para generar el reporte.');
        }
    } else if ($tipo_consulta == 'colaborador') {
        if ( !isset($_POST['selexc_colaborador']) || empty($_POST['selexc_colaborador']) ) {
            wp_send_json_error('Es necesario seleccionar un Colaborador.');
        }
        $id_colaborador = intval($_POST['selexc_colaborador']); 

        $first_name = get_user_meta($id_colaborador, 'first_name', true);
        $last_name = get_user_meta($id_colaborador, 'last_name', true);

        $query = new WP_Query([
            'post_type'      => 'recorrido', // Cambia esto al tipo de post que corresponda
            'posts_per_page' => -1,    // Sin límite, para obtener todos los resultados
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => 'id_solicitante_recorrido',
                    'value'   => $id_colaborador,
                    'compare' => '='
                ],
                [
                    'key'     => 'fecha_inicio_recorrido',
                    'value'   => [$desde_formexcel, $hasta_formexcel],
                    'compare' => 'BETWEEN',
                    'type'    => 'DATE' // Especifica que las fechas son de tipo "DATE"
                ],
            ],
            'fields'         => 'ids', // Solo obtener IDs
        ]);

        if ($query->have_posts()) {
            $headers = ['Empresa', 'Conductor', 'Placa Vehículo', 'Estado', 'Fecha Inicio', 'Hora Inicio', 'Ciudad Inicio', 'Barrio Inicio', 'Centro de Costo'];
            $filtpor = "Colaborador: $first_name $last_name";
            $data = []; // Inicializar fuera del foreach para agrupar todas las asignaciones

            // Recorrer los posts
            foreach ($query->posts as $post_id) {

                $empresa_solicitante_recorrido = get_field('empresa_solicitante_recorrido', $post_id);
                $nombre_empresa = get_the_title( $empresa_solicitante_recorrido );

                $nombre_conductor = "Sin Asignar";

                if (get_field('id_conductor_recorrido', $post_id)) {
                    $id_conductor_recorrido = get_field('id_conductor_recorrido', $post_id)['ID'];
                    $nombre_conductor = get_user_meta($id_conductor_recorrido, 'first_name', true)." ".get_user_meta($id_conductor_recorrido, 'last_name', true);
                }

                $nombciu_inicio = get_field('ciudad_inicial_recorrido', $post_id)->ID;
                $nombciu_inicio = get_field('ciudad_para_empresa', $nombciu_inicio);

                $data[] = [
                    $nombre_empresa,
                    $nombre_conductor,
                    get_field('placa_vehiculo_recorrido', $post_id),
                    get_field('estado_del_recorrido', $post_id),
                    get_field('fecha_inicio_recorrido', $post_id),
                    get_field('hora_inicio_recorrido', $post_id),
                    $nombciu_inicio,
                    get_field('barrio_inicial_recorrido', $post_id),
                    get_field('centro_de_costo', $post_id),
                ];
            }

            wp_reset_postdata();
        } else {
            wp_send_json_error('No se encontraron datos para generar el reporte.');
        }
    } else if ($tipo_consulta == 'recorrido') {

        $meta_query = [
            'relation' => 'AND',
            [
                'key'     => 'fecha_inicio_recorrido',
                'value'   => [$desde_formexcel, $hasta_formexcel],
                'compare' => 'BETWEEN',
                'type'    => 'DATE' // Especifica que las fechas son de tipo "DATE"
            ]
        ];

        // Agregar filtro por empresa si se recibe un valor en selexc_empresa
        if (!empty($_POST['selexc_empresa'])) {
            $meta_query[] = [
                'key'     => 'empresa_solicitante_recorrido',
                'value'   => 'i:' . $_POST['selexc_empresa'] . ';',
                'compare' => 'LIKE'
            ];
        }

        // Agregar filtro por colaborador si se recibe un valor en selexc_colaboradorxempresa
        if (!empty($_POST['selexc_colaboradorxempresa'])) {
            $meta_query[] = [
                'key'     => 'id_solicitante_recorrido',
                'value'   => $_POST['selexc_colaboradorxempresa'],
                'compare' => '='
            ];
        }

        $query = new WP_Query([
            'post_type'      => 'recorrido', 
            'posts_per_page' => -1,    
            'meta_query'     => $meta_query,
            'fields'         => 'ids', 
        ]);

        $id_empresa = (isset($_POST['selexc_empresa']) && !empty($_POST['selexc_empresa'])) ? intval($_POST['selexc_empresa']) : 0;
        $post_empresa = get_post($id_empresa); 
        $nomb_empresa = ($post_empresa) ? esc_html(get_the_title($id_empresa)) : "Todas";

        if ($query->have_posts()) {
            $headers = ['Colaborador', 'Conductor', 'Placa Vehículo', 'Estado', 'Fecha Inicio', 'Hora Inicio', 'Ciudad Inicio', 'Barrio Inicio', 'Centro de Costo'];
            $filtpor = 'Empresa: ' . $nomb_empresa;
            $data = []; 

            // Recorrer los posts
            foreach ($query->posts as $post_id) {

                $id_solicitante_recorrido = get_field('id_solicitante_recorrido', $post_id)['ID'];
                $nombre_colaborador = get_user_meta($id_solicitante_recorrido, 'first_name', true)." ".get_user_meta($id_solicitante_recorrido, 'last_name', true);

                $nombre_conductor = "Sin Asignar";

                if (get_field('id_conductor_recorrido', $post_id)) {
                    $id_conductor_recorrido = get_field('id_conductor_recorrido', $post_id)['ID'];
                    $nombre_conductor = get_user_meta($id_conductor_recorrido, 'first_name', true)." ".get_user_meta($id_conductor_recorrido, 'last_name', true);
                }

                $nombciu_inicio = get_field('ciudad_inicial_recorrido', $post_id)->ID;
                $nombciu_inicio = get_field('ciudad_para_empresa', $nombciu_inicio);

                $data[] = [
                    $nombre_colaborador,
                    $nombre_conductor,
                    get_field('placa_vehiculo_recorrido', $post_id),
                    get_field('estado_del_recorrido', $post_id),
                    get_field('fecha_inicio_recorrido', $post_id),
                    get_field('hora_inicio_recorrido', $post_id),
                    $nombciu_inicio,
                    get_field('barrio_inicial_recorrido', $post_id),
                    get_field('centro_de_costo', $post_id),
                ];
            }

            wp_reset_postdata();
        } else {
            wp_send_json_error('No se encontraron datos para generar el reporte.');
        }
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Reporte de Clientes');

    // Ajuste de anchos y combinación de celdas
    $sheet->getColumnDimension('A')->setWidth(30);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(35);

    $sheet->mergeCells('A1:C6');
    $sheet->mergeCells('A7:C7');

    $sheet->getStyle('A1:C6')->applyFromArray([
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ]);

    // Ruta del logo
    $image_url = 'https://jagonzalez.org/wp-content/uploads/2025/02/adt-1-blue.png'; // Cambia por tu logo
    $tmp_image_path = download_image_to_temp($image_url);

    if ($tmp_image_path) {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo de la Empresa');
        $drawing->setPath($tmp_image_path);
        $drawing->setHeight(80); // Ajusta la altura de la imagen
        $drawing->setCoordinates('A1'); // Coordenada inicial
        $drawing->setOffsetX(50); // Centrar el logo horizontalmente
        $drawing->setOffsetY(10); // Ajustar el espacio vertical
        $drawing->setWorksheet($sheet);
    }

    // Nombre de la empresa
    $company_name = 'AdoniGO';
    $sheet->setCellValue('A7', $company_name);
    $sheet->getStyle('A7')->applyFromArray([
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'font' => [
            'bold' => true,
            'size' => 18,
        ],
    ]);
    $sheet->getRowDimension(7)->setRowHeight(45);

    // Filtrado por
    $sheet->setCellValue('A8', 'Filtrado por: ' . ucfirst(strtolower($tipo_consulta)));
    $sheet->getStyle('A8')->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 14,
        ],
    ]);

    // Conductor
    $sheet->setCellValue('A9', $filtpor);
    $sheet->getStyle('A9')->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 14,
        ],
    ]);

    // Fecha inicio consulta
    $sheet->setCellValue('A10', 'Fecha Inicio Consulta: ' . $desde_formexcel);
    $sheet->getStyle('A10')->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 14,
        ],
    ]);

    // Fecha final consulta
    $sheet->setCellValue('A11', 'Fecha Final Consulta: ' . $hasta_formexcel);
    $sheet->getStyle('A11')->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 14,
        ],
    ]);

    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '13', $header);
        $sheet->getStyle($col . '13')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);
        $col++;
    }

    // Encabezados de columnas para los datos
    /*$sheet->setCellValue('A13', 'Fecha Inicio')
          ->setCellValue('B13', 'Fecha Final')
          ->setCellValue('C13', 'Franja');
    $sheet->getStyle('A13:C13')->applyFromArray([
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'font' => [
            'bold' => true,
            'size' => 16,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ]);*/

    // Datos del reporte
    /*$data = $asignaciones;
    $row = 14;
    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $item['dia_inicio_de_asignacion'])
              ->setCellValue('B' . $row, $item['dia_fin_de_asignacion'])
              ->setCellValue('C' . $row, $item['franja_horaria_asignacion']);
        $row++;
    }*/

    $row = 14;
    foreach ($data as $rowData) {
        $col = 'A';
        foreach ($rowData as $cellData) {
            $sheet->setCellValue($col . $row, $cellData);
            $col++;
        }
        $row++;
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $fecha_hora = date('Y-m-d_H-i-s');
    $file_path = wp_upload_dir()['path'] . '/Reporte-'.$tipo_consulta.'-'. $fecha_hora . '.xlsx';
    $writer->save($file_path);

    wp_send_json_success(['file_url' => wp_upload_dir()['url'] . '/Reporte-'.$tipo_consulta.'-'. $fecha_hora . '.xlsx']);
}
add_action('wp_ajax_gen_reporte_excel', 'func_gen_reporte_excel');
add_action('wp_ajax_nopriv_gen_reporte_excel', 'func_gen_reporte_excel');

// Función para descargar la imagen desde una URL a un archivo temporal
function download_image_to_temp($image_url) {
    // Obtener la imagen de la URL
    $image_data = file_get_contents($image_url);

    if ($image_data === false) {
        return false; // Error al descargar la imagen
    }

    // Crear un archivo temporal para guardar la imagen
    $tmp_file_path = sys_get_temp_dir() . '/' . basename($image_url);

    // Guardar la imagen en el archivo temporal
    file_put_contents($tmp_file_path, $image_data);

    return $tmp_file_path;
}