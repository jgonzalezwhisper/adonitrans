<?php

function generate_secure_token() {
    do {
        $token = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT); // Generar un número aleatorio de 6 dígitos
    } while (
        preg_match('/(\d)\1{1}/', $token) || // Evitar números repetidos consecutivos (como 00 o 11)
        preg_match('/012345|123456|234567|345678|456789|543210|432109|321098|210987/', $token) // Evitar secuencias consecutivas ascendentes/descendentes
    );
    return $token;
}

function custom_user_login() {
    // Verificar el nonce
    if (!isset($_POST['custom_login_nonce']) || !wp_verify_nonce($_POST['custom_login_nonce'], 'custom_user_login')) {
        wp_send_json_error(array('message' => 'Nonce inválido'));
    }

    // Verificar si los datos fueron enviados correctamente
    if ( isset( $_POST['email'] ) && isset( $_POST['password'] ) ) {
        $email = sanitize_email( $_POST['email'] );
        $password = sanitize_text_field( $_POST['password'] );

        // Intentar autenticar al usuario
        $user = get_user_by( 'email', $email );
        
        if ( ! $user ) {
            wp_send_json_error(array( 'message' => 'El correo electrónico no está registrado.' ));
        }

        $user = wp_authenticate( $email, $password );

        if ( is_wp_error( $user ) ) {
            // Si hay un error, devolver un mensaje de error
            wp_send_json_error(array(
                'message' => 'Correo electrónico o contraseña incorrectos.'
            ));
        } else {
            // Si la autenticación es exitosa, generar token y enviarlo por correo
            $token = generate_secure_token(); // Generar un token aleatorio
            $expiration_time = time() + 60 * 60; // Token expira en 60 minutos

            // Guardar el token en la base de datos
            update_user_meta( $user->ID, '_login_token', $token );
            update_user_meta( $user->ID, '_login_token_expiration', $expiration_time );

            // Enviar el token por correo electrónico
            $subject = 'Tu Token de Verificación de Inicio de Sesión';
            $message = 'Tu token de verificación es: ' . $token;
            wp_mail( $email, $subject, $message );

            // Devolver respuesta indicando que se requiere el token
            wp_send_json_success(array(
                'message' => 'Datos correctos. Un token ha sido enviado a tu correo electrónico.',
                'token_required' => true
            ));
        }
    }

    // En caso de error en el envío de datos
    wp_send_json_error(array(
        'message' => 'Datos incompletos.'
    ));
}
add_action('wp_ajax_custom_user_login', 'custom_user_login');
add_action('wp_ajax_nopriv_custom_user_login', 'custom_user_login');

function validate_token() {
    if ( isset( $_POST['token'] ) ) {
        $token = sanitize_text_field( $_POST['token'] );

        // Buscar al usuario con el token proporcionado
        $users = get_users(array(
            'meta_key'   => '_login_token',
            'meta_value' => $token,
            'number'     => 1,
            'fields'     => 'ID',
        ));

        if ( empty($users) ) {
            wp_send_json_error(array('message' => 'Token no encontrado o expirado.'));
        }

        $user_id = $users[0];
        $token_expiry = get_user_meta($user_id, '_login_token_expiration', true);

        if ( time() < $token_expiry ) {
            // El token es válido, iniciar sesión
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);

            $panel_administracion = get_field('panel_administracion', 'option');
            if ( $panel_administracion ) {
                $redirect_url = get_permalink($panel_administracion);
            }

            wp_send_json_success(array(
                'message' => 'Token verificado correctamente.',
                'redirect_url' => $redirect_url,
            ));
        } else {
            wp_send_json_error(array('message' => 'Token inválido o expirado.'));
        }
    }

    wp_send_json_error(array('message' => 'Token no proporcionado.'));
}
add_action('wp_ajax_validate_token', 'validate_token');
add_action('wp_ajax_nopriv_validate_token', 'validate_token');