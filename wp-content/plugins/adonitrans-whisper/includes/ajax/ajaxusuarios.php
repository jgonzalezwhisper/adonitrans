<?php

/*Acción AJAX para CREAR un usuario*/
add_action('wp_ajax_create_user', 'create_user_function');
add_action('wp_ajax_nopriv_create_user', 'create_user_function');
function create_user_function() {
    // Verificar nonce si es necesario (no se ha incluido en el ejemplo)
    if (!isset($_POST['create_user_nonce']) || !wp_verify_nonce($_POST['create_user_nonce'], 'create_user_action')) {
        wp_send_json_error(['message' => 'Nonce no válido.']);
        wp_die();
    }

    // Obtener los datos del formulario
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $email = sanitize_email($_POST['user_email']);
    $password = sanitize_text_field($_POST['password']);
    $role = sanitize_key($_POST['select_rolesusuario']);
    
    // Validar rol permitido
    $roles_permitidos = [
        'comercial_1', 'comercial_2', 'tramites', 'talento_humano', 
        'operaciones_1', 'operaciones_2', 'facturacion', 'tesoreria', 
        'propietario_vehiculo', 'conductor', 'colaborador', 'empresa','administrator'
    ];
    
    if (!in_array($role, $roles_permitidos)) {
        wp_send_json_error(['message' => 'Rol no permitido']);
    }     

    $accion1 = "Crear";   
    $accion2 = "Creado";   

    if (isset($_POST['user-id']) && !empty($_POST['user-id'])) {
        $user_id = $_POST['user-id'];
        $accion1 = "Editar";   
        $accion2 = "Editado"; 

        if (!empty($password)) {
            // Actualizar la contraseña del usuario
            wp_set_password($password, $user_id);
        }
    }else{

        // Verificar si el email ya existe
        if (email_exists($email)) {
            wp_send_json_error(['message' => 'El correo electrónico ya está registrado.']);
            wp_die();
        }

        // Crear el usuario en WordPress
        $user_id = wp_create_user($email, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => 'Error al '.$accion1.' el usuario']);
        }
    }
    

    // Actualizar el nombre y apellidos
    wp_update_user([
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'role' => $role
    ]);

    // Guardar campos adicionales (ACF)
    if (isset($_POST['user-state']) && !empty($_POST['user-state'])) {
        update_field('estado_usuario', sanitize_text_field($_POST['user-state']), 'user_' . $user_id);
    }

    if (isset($_POST['user-cedula']) && !empty($_POST['user-cedula'])) {
        update_field('cedula_usuario', sanitize_text_field($_POST['user-cedula']), 'user_' . $user_id);
    }

    if (isset($_POST['user-telefono']) && !empty($_POST['user-telefono'])) {
        update_field('telefono', sanitize_text_field($_POST['user-telefono']), 'user_' . $user_id);
    }

    if (isset($_POST['user-direccion']) && !empty($_POST['user-direccion'])) {
        update_field('direccion', sanitize_text_field($_POST['user-direccion']), 'user_' . $user_id);
    }

    if (isset($_POST['sel_empresa_asociada']) && !empty($_POST['sel_empresa_asociada'])) {
        update_field('empresa_asociada_usuario', sanitize_text_field($_POST['sel_empresa_asociada']), 'user_' . $user_id);
    }


    // Subir la foto de usuario
    if (!empty($_FILES['user-foto']['name'])) {
        $file = $_FILES['user-foto'];
        $upload = wp_handle_upload($file, ['test_form' => false]);

        if (!isset($upload['error'])) {
            $attachment_id = wp_insert_attachment([
                'post_title' => sanitize_file_name($file['name']),
                'post_mime_type' => $upload['type'],
                'guid' => $upload['url'],
                'post_status' => 'inherit'
            ], $upload['file']);
            update_field('foto_de_usuario', $attachment_id, 'user_' .$user_id);
        }
    }

    // Guardar información de pago si existe
    if (isset($_POST['nombre_banco'])) {
        $payment_data = [];
        foreach ($_POST['nombre_banco'] as $key => $value) {
            $payment_data[] = [
                'nombre_banco' => sanitize_text_field($_POST['nombre_banco'][$key]),
                'no_cuenta' => sanitize_text_field($_POST['no_cuenta'][$key]),
                'tipo_de_cuenta' => sanitize_text_field($_POST['tipo_de_cuenta'][$key]),
            ];
        }
        
        // Especifica la jerarquía completa para el repetidor dentro del grupo
        update_field('informacion_de_pago_usuario', [
            'datos_informacion_de_pago_usuario' => $payment_data
        ], 'user_' . $user_id);
    }

    // Devolver respuesta de éxito
    wp_send_json_success(['message' => 'Usuario '.$accion2.' exitosamente']);
}

/*Acción AJAX para ELIMINAR un usuario*/
add_action('wp_ajax_delete_user', 'delete_user_ajax_handler');
function delete_user_ajax_handler() {
    if (!current_user_can('delete_users')) {
        wp_send_json_error(['message' => 'No tienes permisos para eliminar usuarios.']);
    }

    $user_id = intval($_POST['user_id']);

    if (!$user_id || !get_userdata($user_id)) {
        wp_send_json_error(['message' => 'Usuario no válido.']);
    }

    // Obtener el ID de la imagen asociada al campo foto_de_usuario y eliminarla
    $foto = get_field('foto_de_usuario', 'user_' . $user_id);
    if (!empty($foto['ID'])) {
        wp_delete_attachment($foto['ID'], true); // Eliminar la imagen permanentemente
    }
    $result = wp_delete_user($user_id);

    if ($result) {
        wp_send_json_success(['message' => 'Usuario eliminado exitosamente.']);
    } else {
        wp_send_json_error(['message' => 'No se pudo eliminar al usuario.']);
    }
}

/*ACCION AJAX PARA OBTENER DATOS DE UN USUARIO*/
add_action('wp_ajax_load_user_data', 'load_user_data_function');
function load_user_data_function() {
    $user_id = intval($_POST['user_id']);
    if (!$user_id || !get_userdata($user_id)) {
        wp_send_json_error(['message' => 'Usuario no válido.']);
    }

    $user_data = get_userdata($user_id);
    
    $user_key = 'user_' . $user_id;

    // Obtener los campos individuales
    $estado_usuario = get_field('estado_usuario', $user_key);
    $cedula_usuario = get_field('cedula_usuario', $user_key);
    $telefono = get_field('telefono', $user_key);
    $direccion = get_field('direccion', $user_key);
    $foto_de_usuario = get_field('foto_de_usuario', $user_key);

    // Obtener el grupo "informacion_de_pago_usuario"
    $informacion_de_pago_usuario = get_field('informacion_de_pago_usuario', $user_key);

    // Crear un array vacío para almacenar los datos del repetidor
    $informacion_pago_array = [];

    // Si el grupo tiene el repetidor "datos_informacion_de_pago_usuario"
    if (!empty($informacion_de_pago_usuario['datos_informacion_de_pago_usuario'])) {
        $datos_informacion_de_pago_usuario = $informacion_de_pago_usuario['datos_informacion_de_pago_usuario'];

        // Recorremos el repetidor para obtener los detalles y almacenarlos en el array
        foreach ($datos_informacion_de_pago_usuario as $dato) {
            // Extraemos los valores de cada campo
            $nombre_banco = $dato['nombre_banco'];
            $no_cuenta = $dato['no_cuenta'];
            $tipo_de_cuenta = $dato['tipo_de_cuenta'];

            // Creamos un array asociativo para esta fila y lo agregamos al array principal
            $informacion_pago_array[] = [
                'nombre_banco' => $nombre_banco,
                'no_cuenta' => $no_cuenta,
                'tipo_de_cuenta' => $tipo_de_cuenta,
            ];
        }
    }

    wp_send_json_success([
        'first_name'        => $user_data->first_name,
        'last_name'         => $user_data->last_name,
        'email'             => $user_data->user_email,
        'role'              => $user_data->roles[0],
        'meta_pagos'        => $informacion_pago_array,
        'meta_foto'         => $foto_de_usuario,
        'meta_direccion'    => $direccion,
        'meta_telefono'     => $telefono,
        'meta_cedula'       => $cedula_usuario,
        'meta_estado'       => $estado_usuario,

    ]);
}
