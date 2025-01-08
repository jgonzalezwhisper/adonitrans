jQuery(document).ready(function($) {

    $('#custom-login-form input:not("#custom_login_nonce")').text('').val('');

    // Manejar la lógica para el inicio de sesión
    $('#custom-login-form').submit(function(event) {
        event.preventDefault();

        // Verificar si el campo de token está visible
        if ($('#token-section').is(":visible")) {
            var token = $('#user_token').val(); // Obtener el token ingresado por el usuario

            // Enviar el token por AJAX para validarlo
            $.ajax({
                url: loginAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'validate_token',
                    token: token
                },
                success: function(response) {
                    if (response.success) {
                        // Si el token es válido, iniciar sesión
                        Swal.fire({
                            title: '¡Bienvenido!',
                            text: 'Inicio de sesión exitoso.',
                            icon: 'success',
                            confirmButtonText: 'Ir al Panel'
                        }).then(function() {
                            window.location.href = response.data.redirect_url; 
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.data.message,
                            icon: 'error',
                            confirmButtonText: 'Intentar de nuevo'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un error al procesar la solicitud.',
                        icon: 'error',
                        confirmButtonText: 'Intentar de nuevo'
                    });
                }
            });
        } else {
            // Si el campo de token no está visible, proceder con el inicio de sesión normal
            var email = $('#user_email').val();
            var password = $('#user_password').val();
            var nonce = $('#custom_login_nonce').val(); // Obtener el nonce

            // Mostrar loading
            Swal.fire({
                title: 'Cargando...',
                text: 'Iniciando sesión...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar datos con AJAX
            $.ajax({
                url: loginAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'custom_user_login',
                    email: email,
                    password: password,
                    custom_login_nonce: nonce
                },
                success: function(response) {
                    Swal.close(); // Cerrar el loading

                    if (response.success) {
                        // Si los datos son correctos, mostrar el campo para el token
                        $('#token-section').show();
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.data.message,
                            icon: 'error',
                            confirmButtonText: 'Intentar de nuevo'
                        });
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un error al procesar la solicitud.',
                        icon: 'error',
                        confirmButtonText: 'Intentar de nuevo'
                    });
                }
            });
        }
    });  
});