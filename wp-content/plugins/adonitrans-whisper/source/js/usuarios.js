jQuery(document).ready(function($) {

    function generarContrasena() {
        // Definición de conjuntos de caracteres
        const caracteresEspeciales = "*#=-_./!¿?";
        const mayusculas = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        const minusculas = "abcdefghijklmnopqrstuvwxyz";
        const numeros = "0123456789";
        const todosCaracteres = mayusculas + minusculas + numeros + caracteresEspeciales;

        // Longitud aleatoria entre 8 y 15 caracteres
        const longitud = Math.floor(Math.random() * (15 - 8 + 1)) + 8;

        let contrasena = [];

        // Garantizar al menos una mayúscula, un número y un carácter especial
        contrasena.push(obtenerCaracterAleatorio(mayusculas)); // Una mayúscula
        contrasena.push(obtenerCaracterAleatorio(numeros)); // Un número
        contrasena.push(obtenerCaracterAleatorio(caracteresEspeciales)); // Un carácter especial

        // Rellenar con caracteres aleatorios hasta alcanzar la longitud deseada
        while (contrasena.length < longitud) {
            contrasena.push(obtenerCaracterAleatorio(todosCaracteres));
        }

        // Mezclar los caracteres para que no haya un patrón predecible
        contrasena = mezclarArray(contrasena);

        return contrasena.join("");
    }

    // Función para obtener un carácter aleatorio de un conjunto de caracteres
    function obtenerCaracterAleatorio(conjunto) {
        return conjunto.charAt(Math.floor(Math.random() * conjunto.length));
    }

    // Función para mezclar un array de forma aleatoria (Fisher-Yates shuffle)
    function mezclarArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]]; // Intercambiar posiciones
        }
        return array;
    }

    // Mostrar/Ocultar campos adicionales según el rol seleccionado
    function toggleFieldsByRole(role) {
        const extraFieldsContainer = $('#extra-fields-container');
        const paymentFieldsContainer = $('#payment-fields-container');
        const empresaFieldsContainer = $('#wrap-empresa-asociada');

        // Ocultar todo por defecto
        extraFieldsContainer.hide();
        paymentFieldsContainer.hide();

        if (role) {
            extraFieldsContainer.show();

            if (role === 'propietario_vehiculo' || role === 'conductor') {
                paymentFieldsContainer.show();
            }
            else if (role === 'colaborador' ) {
                empresaFieldsContainer.show();
            }
            else{
                paymentFieldsContainer.hide();
                empresaFieldsContainer.hide();
            }

        }
    }

    $(document).on('click', '#wrap-usuarios .wrap-listado-usuarios .edit-user', function(event) {

        $('body').addClass('actloader');
        var user_id = $(this).data('userid');

        $("#password").text('').val('').removeAttr("required");
        $("#user-form #user-id").val(user_id);

        // Enviar la solicitud AJAX para obtener los datos del usuario
        $.ajax({
            url: usuarioAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'load_user_data',
                user_id: user_id
            },
            success: function(response) {
                if (response.success) {

                    $('#user-id').val(user_id);
                    $('#first_name').val(response.data.first_name);
                    $('#last_name').val(response.data.last_name);
                    $('#user_email').val(response.data.email);
                    $('#select_rolesusuario').val(response.data.role).trigger('change');
                    $('#user_state').val(response.data.meta_estado).trigger('change');

                    toggleFieldsByRole(response.data.role);

                    $('#user-cedula').val(response.data.meta_cedula);
                    $('#user-telefono').val(response.data.meta_telefono);
                    $('#user-direccion').val(response.data.meta_direccion);

                    var info_pago = response.data.meta_pagos;

                    // Comprobamos si el array tiene elementos y si los campos dentro de ese objeto no están vacíos
                    if (Array.isArray(info_pago) && info_pago.length > 0) {
                        var validado = true;

                        $.each(info_pago, function(index, item) {
                            if (!item.nombre_banco || !item.no_cuenta || !item.tipo_de_cuenta) {
                                validado = false;
                                return false;
                            }
                        });

                        if (validado) {

                            $('.payment-row input').val('').text('');

                            // Llenar la primera fila con los datos del primer objeto
                            var firstPaymentRow = $('.payment-row').first();
                            firstPaymentRow.find('input[name="nombre_banco[]"]').val(info_pago[0].nombre_banco);
                            firstPaymentRow.find('input[name="no_cuenta[]"]').val(info_pago[0].no_cuenta);
                            firstPaymentRow.find('select[name="tipo_de_cuenta[]"]').val(info_pago[0].tipo_de_cuenta);

                            // Si hay más de un objeto en el array, clonar las filas adicionales y llenarlas
                            if (info_pago.length > 1) {
                                for (var i = 1; i < info_pago.length; i++) {
                                    // Clonar la última fila de pago
                                    var newRow = $('.payment-row').last().clone();

                                    // Limpiar los campos de la nueva fila
                                    newRow.find('input').val('');
                                    newRow.find('select').val('');

                                    // Llenar los campos con los datos del siguiente objeto
                                    newRow.find('input[name="nombre_banco[]"]').val(info_pago[i].nombre_banco);
                                    newRow.find('input[name="no_cuenta[]"]').val(info_pago[i].no_cuenta);
                                    newRow.find('select[name="tipo_de_cuenta[]"]').val(info_pago[i].tipo_de_cuenta);

                                    // Añadir un enlace para remover la fila
                                    newRow.append('<a href="#" class="remove-payment-row">✖</a>');

                                    // Insertar el nuevo clon en el contenedor
                                    $('#repeater-payment-fields').append(newRow);
                                }
                            }
                            $('#payment-fields-container').show();
                        }
                    } else {
                        $('.payment-row input').val('').text('');
                    }

                    $('#wrap-usuarios .wrap-gestion-usuarios button[type="submit"]').text('Editar Usuario');
                    $('#wrap-usuarios .wrap-gestion-usuarios .title').text('Editar Usuario');
                    $("#wrap-usuarios .wrap-listado-usuarios").hide();
                    $("#wrap-usuarios .wrap-gestion-usuarios").show();
                    $('body').removeClass('actloader');
                } else {
                    $('body').removeClass('actloader');
                    // Si la respuesta es exitosa, mostrar mensaje de éxito
                    Swal.fire({
                        title: 'Algo ha ocurrido!',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            }
        });
    });

    $(document).on('click', '#wrap-usuarios .wrap-listado-usuarios .delete-user', function(event) {
        event.preventDefault();

        // Obtener el ID del usuario desde el botón
        var userId = $(this).data('userid');

        // Mostrar la confirmación con SweetAlert
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará al usuario de forma permanente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario confirma, realiza la solicitud AJAX
                $.ajax({
                    url: usuarioAjax.ajaxurl, // La URL de admin-ajax.php en WordPress
                    method: 'POST',
                    data: {
                        action: 'delete_user', // Acción personalizada en WordPress
                        user_id: userId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            Swal.fire(
                                '¡Eliminado!',
                                'El usuario ha sido eliminado exitosamente.',
                                'success'
                            ).then(() => {
                                var fileUrl = usuarioAjax.plugin_url + "includes/parts/panel/usuario.php";

                                $.ajax({
                                    url: fileUrl,
                                    method: "POST",
                                    data: {
                                        action: 'render_html_panel',
                                    },
                                    success: function(response) {
                                        $("#informacion").html(response);
                                        initUsuarios();
                                    },
                                    error: function() {
                                        $("#informacion").html("<p>Error al cargar el contenido. Intenta nuevamente.</p>");
                                    }
                                });
                            });

                        } else {
                            // Mostrar mensaje de error
                            Swal.fire(
                                'Error',
                                response.data.message || 'No se pudo eliminar el usuario.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        // Mostrar mensaje de error si AJAX falla
                        Swal.fire(
                            'Error',
                            'Hubo un problema al intentar eliminar el usuario.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    $(document).on('click', '#crear-usuario', function(event) {
        event.preventDefault();

        $('#user-form')[0].reset();
        $('#wrap-usuarios .wrap-gestion-usuarios button[type="submit"]').text('Crear Usuario');

        $("#wrap-usuarios .wrap-listado-usuarios").hide();
        $("#wrap-usuarios .wrap-gestion-usuarios").show();
    });

    $(document).on('click', '#wrap-usuarios .wrap-gestion-usuarios button[type="button"]', function(event) {
        $('#user-form')[0].reset();
        $("#user-id").text('').val('');
        $('#select_rolesusuario').val(null).trigger('change');
        $("#wrap-usuarios .wrap-gestion-usuarios").hide();
        $("#wrap-usuarios .wrap-listado-usuarios").show();
    });

    // Evento al cambiar el rol
    $(document).on('change', '#select_rolesusuario', function(event) {
        event.preventDefault();

        const selectedRole = $(this).val();
        toggleFieldsByRole(selectedRole);
    });

    $(document).on('click', '#add-payment-row', function(e) {
        e.preventDefault(); // Evita el comportamiento predeterminado del enlace

        // Clonar la última fila de pago
        var newRow = $('.payment-row').last().clone();

        newRow.find('input').val('');
        newRow.find('select').val('');

        newRow.find('.remove-payment-row').remove();
        newRow.append('<a href="#" class="remove-payment-row">✖</a>');

        // Insertar el nuevo clon en el contenedor
        $('#repeater-payment-fields').append(newRow);
    });

    // Al hacer clic en el icono de eliminar
    $(document).on('click', '.remove-payment-row', function(e) {
        e.preventDefault(); // Evita el comportamiento predeterminado del enlace
        $(this).closest('.payment-row').remove(); // Elimina la fila de pago
    });

    $(document).on('click', '#generate-password', function(e) {
        e.preventDefault(); // Evita el comportamiento predeterminado del enlace
        var contrasenaGenerada = generarContrasena();
        $('#password').val(contrasenaGenerada); // Asignar la contraseña generada al campo de entrada
    });

    // Enviar formulario vía AJAX
    // Inicializar la validación del formulario al cargar dinámicamente
    $(document).on('focusin', '#user-form', function() {
        $(this).validate({
            rules: {
                first_name: {
                    required: true,
                },
                last_name: {
                    required: true,
                },
                user_email: {
                    required: true,
                    email: true,
                },
                select_rolesusuario: {
                    required: true,
                },
                password: {
                    required: function() {
                        // Validar solo si el campo #user-id está vacío
                        return $('#user-id').val() === '';
                    },
                    checkPassword: function() {
                        // Validar solo si el campo #user-id está vacío
                        return $('#user-id').val() === '';
                    },
                },
            },
            messages: {
                first_name: "Ingresa tu nombre",
                last_name: "Ingresa tu apellido",
                user_email: {
                    required: "Este campo es obligatorio",
                    email: "Ingresa un correo electrónico valido",
                },
                select_rolesusuario: "Seleccione un rol",
                password: {
                    required: "Ingresa tu contraseña nueva",
                    checkPassword: "Tu contraseña debe cumplir con los requisitos",
                },
            },
            submitHandler: function(form) {
                // Recoger los datos del formulario
                var formData = new FormData(form);
                formData.append('action', 'create_user');

                // Realizar la petición AJAX
                $.ajax({
                    url: usuarioAjax.ajaxurl, // Ruta del endpoint AJAX
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('body').addClass('actloader');
                    },
                    success: function(response) {
                        $('body').removeClass('actloader');
                        if (response.success) {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: response.data.message,
                                icon: 'success',
                                confirmButtonText: 'Aceptar',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Reiniciar formulario
                                    $('#user-form')[0].reset();

                                    actform = $('#user-form').data('action');
                                    urlredi = 'includes/parts/panel/usuario.php';

                                    if (actform == "misdatos") {
                                        urlredi = 'includes/parts/panel/cuenta.php';
                                    }

                                    // Cargar nuevo contenido
                                    var fileUrl =
                                        usuarioAjax.plugin_url + urlredi;

                                    $.ajax({
                                        url: fileUrl,
                                        method: "POST",
                                        data: {
                                            action: 'render_html_panel',
                                        },
                                        success: function(response) {
                                            $('#informacion').html(response);
                                            initUsuarios();
                                        },
                                        error: function() {
                                            $('#informacion').html(
                                                '<p>Error al cargar el contenido. Intenta nuevamente.</p>'
                                            );
                                        },
                                    });
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Algo ha ocurrido!',
                                text: response.data.message,
                                icon: 'error',
                                confirmButtonText: 'Aceptar',
                            });
                        }
                    },
                    error: function() {
                        $('body').removeClass('actloader');
                        Swal.fire({
                            title: '¡Error!',
                            text: 'Hubo un problema al procesar el formulario. Por favor intenta nuevamente.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                        });
                    },
                });
            },
        });
    });

    // Evento submit para prevenir el comportamiento por defecto y enviar correctamente
    $(document).on('submit', '#user-form', function(e) {
        e.preventDefault(); // Prevenir el envío predeterminado
        if ($(this).valid()) {
            // Si el formulario es válido, ejecutar la lógica de validación
            $(this).submit();
        }
    });


});