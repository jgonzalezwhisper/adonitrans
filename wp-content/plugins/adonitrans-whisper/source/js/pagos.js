jQuery(document).ready(function($) {

    // Extender jQuery Validation para que funcione con select2 y elementos dinámicos
    $.validator.setDefaults({
        ignore: ':hidden:not(.select2-hidden-accessible)',
    });

    // Validación personalizada para select2
    $.validator.addMethod("select2Required", function(value, element, param) {
        return value !== null && value !== "";
    }, "Este dato es obligatorio");

    /*ICONOS PARA DOCUMENTOS*/
    const fileIcons = {
        pdf: pagosAjax.plugin_url + 'assets/images/PDF.svg',
        doc: pagosAjax.plugin_url + 'assets/images/WORD.svg',
        docx: pagosAjax.plugin_url + 'assets/images/WORD.svg',
        xls: pagosAjax.plugin_url + 'assets/images/EXCEL.svg',
        xlsx: pagosAjax.plugin_url + 'assets/images/EXCEL.svg',
        default: pagosAjax.plugin_url + 'assets/images/OTRO.svg',
    };

    $(document).on('click', '#crear-pago', function(event) {
        event.preventDefault();

        $("#wrap-pagos .wrap-listado-pagos").hide();
        $("#wrap-pagos .wrap-gestion-pagos").show();
    });

    $(document).on('click', '#form-pagos .button-cancelar', function(event) {
        event.preventDefault();

        $('#form-pagos')[0].reset();
        $("#form-pagos #post-id").text('').val('');
        $('#wrap-pagos .wrap-gestion-pagos .title span').text('Registrar');

        $("#wrap-pagos .archivo-previo").remove();
        $('#estado_del_pago, #usuario_asociado_al_pago').val(null).trigger('change');

        $("#wrap-pagos .wrap-listado-pagos").show();
        $("#wrap-pagos .wrap-gestion-pagos").hide();
    });

    // Mostrar ícono dinámico al seleccionar archivo
    $(document).on('change', '.documento-input', function() {
        const file = this.files[0];
        if (file) {
            const fileType = file.name.split('.').pop().toLowerCase();
            const iconUrl = fileIcons[fileType] || fileIcons.default;
            $(this).siblings('.document-icon').attr('src', iconUrl);
        }
    });

    $(document).on('click', '#wrap-pagos .wrap-listado-pagos .eliminar', function(event) {
        event.preventDefault();

        // Obtener el ID del usuario desde el botón
        let post_id = $(this).data('id');
        console.log("Post ID " + post_id);

        // Mostrar la confirmación con SweetAlert
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará el Pago de forma permanente.',
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
                    url: pagosAjax.ajaxurl, // La URL de admin-ajax.php en WordPress
                    method: 'POST',
                    data: {
                        action: 'eliminar_pago', // Acción personalizada en WordPress
                        post_id: post_id
                    },
                    beforeSend: function() {
                        $('body').addClass('actloader');
                    },
                    success: function(response) {
                        $('body').removeClass('actloader');
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            Swal.fire(
                                '¡Eliminado!',
                                'El pago ha sido eliminado exitosamente.',
                                'success'
                            ).then(() => {
                                var fileUrl = pagosAjax.plugin_url + "includes/parts/panel/pagos.php";

                                $.ajax({
                                    url: fileUrl,
                                    method: "POST",
                                    data: {
                                        action: 'render_html_panel',
                                    },
                                    success: function(response) {
                                        $("#informacion").html(response);
                                        initPagos();
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
                                response.data.message || 'No se pudo eliminar el Pago.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        $('body').removeClass('actloader');
                        // Mostrar mensaje de error si AJAX falla
                        Swal.fire(
                            'Error',
                            'Hubo un problema al intentar eliminar el Pago.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    $(document).on('click', '#wrap-pagos .wrap-listado-pagos .editar', function(event) {

        event.preventDefault();

        $('#wrap-pagos .wrap-gestion-pagos button[type="submit"]').text('Guardar Cambios');
        $('#wrap-pagos .wrap-gestion-pagos .title span').text('Editar');
        $("#wrap-pagos .wrap-listado-pagos").hide();
        $("#wrap-pagos .wrap-gestion-pagos").show();

        let id_pago = $(this).data('id');
        $("#form-pagos #post-id").val(id_pago);

        // Enviar la solicitud AJAX para obtener los datos del usuario
        $.ajax({
            url: pagosAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'cargar_datos_pago',
                post_id: id_pago
            },
            beforeSend: function() {
                $('body').addClass('actloader');
            },
            success: function(response) {
                if (response.success) {

                    if (response.data.fecha_del_pago) {
                        $('#fecha_del_pago').val(response.data.fecha_del_pago);
                    }
                    if (response.data.estado_del_pago) {
                        $('#estado_del_pago').val(response.data.estado_del_pago).trigger('change');
                    }
                    if (response.data.comentario_del_pago) {
                        $('#comentario_del_pago').val(response.data.comentario_del_pago);
                    }
                    if (response.data.usuario_asociado_al_pago) {
                        let $select = $('#usuario_asociado_al_pago');

                        // Asegurar que Select2 está inicializado
                        if ($select.data('select2')) {
                            $select.val(response.data.usuario_asociado_al_pago).trigger('change');
                        } else {
                            // Esperar a que Select2 se cargue
                            $select.select2().val(response.data.usuario_asociado_al_pago).trigger('change');
                        }
                    }

                    if (response.data.documentos_pago && response.data.documentos_pago.cuenta_de_cobro) {
                        let cuentaCobro = response.data.documentos_pago.cuenta_de_cobro;
                        let fileUrl = cuentaCobro.url; // URL del archivo
                        let fileId = cuentaCobro.id; // ID del archivo en WordPress
                        let fileContainer = $('#cuenta_de_cobro').parent(); // Contenedor del input file
                        fileContainer.append(`
                            <div class="archivo-previo" id="archivo_cuenta_de_cobro">
                                <a href="${fileUrl}" target="_blank" class="file-link">📂 Ver Archivo</a>
                                <a href="#" class="remove-file" data-id="${fileId}">❌ Eliminar</a>
                            </div>
                        `);
                    }

                    if (response.data.documentos_pago && response.data.documentos_pago.foto_del_pago) {
                        let cuentaCobro = response.data.documentos_pago.foto_del_pago;
                        let fileUrl = cuentaCobro.url; // URL del archivo
                        let fileId = cuentaCobro.id; // ID del archivo en WordPress
                        let fileContainer = $('#foto_del_pago').parent(); // Contenedor del input file
                        fileContainer.append(`
                            <div class="archivo-previo" id="archivo_cuenta_de_cobro">
                                <a href="${fileUrl}" target="_blank" class="file-link">📂 Ver Archivo</a>
                                <a href="#" class="remove-file" data-id="${fileId}">❌ Eliminar</a>
                            </div>
                        `);
                    }
                } else {
                    $('body').removeClass('actloader');
                    Swal.fire({
                        title: 'Algo ha ocurrido!',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            complete: function() {
                $('body').removeClass('actloader');
            }
        });
    });

    /*ENVIO Y VALIDACION DE FORMULARIO*/
    $(document).on('focusin', '#form-pagos', function() {

        $(this).validate({
            rules: {
                usuario_asociado_al_pago: {
                    required: true,
                },
                cuenta_de_cobro: {
                    required: true,
                },
                estado_del_pago: {
                    required: true,
                },
            },
            messages: {
                usuario_asociado_al_pago: "Esta información es necesaria",
                cuenta_de_cobro: "Esta información es necesaria",
                estado_del_pago: "Esta información es necesaria",
            },
            submitHandler: function(form) {

                // Recoger los datos del formulario
                var formData = new FormData(form);

                // Realizar la petición AJAX
                $.ajax({
                    url: pagosAjax.ajaxurl,
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
                                    $('#form-pagos')[0].reset();

                                    // Cargar nuevo contenido
                                    var fileUrl =
                                        pagosAjax.plugin_url +
                                        'includes/parts/panel/pagos.php';

                                    $.ajax({
                                        url: fileUrl,
                                        method: "POST",
                                        data: {
                                            action: 'render_html_panel',
                                        },
                                        success: function(response) {
                                            $('#informacion').html(response);
                                            initPagos();
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
});