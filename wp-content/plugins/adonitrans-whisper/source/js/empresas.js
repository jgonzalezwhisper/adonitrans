jQuery(document).ready(function($) {

    $(document).on('click', '#crear-empresa', function(event) {
        event.preventDefault();

        $('#empresa-form')[0].reset();
        $('#wrap-empresas .wrap-gestion-empresas button[type="submit"]').text('Crear Empresa');

        $("#wrap-empresas .wrap-listado-empresas").hide();
        $("#wrap-empresas .wrap-gestion-empresas").show();
    });

    $(document).on('click', '#wrap-empresas .wrap-gestion-empresas #cancelar-empresa-btn', function(event) {
        $('#empresa-form')[0].reset();
        $("#empresa-id").text('').val('');
        $('#administradores_empresa').val(null).trigger('change');
        $('.select2-selection__clear').remove();
        $('.centro-costo:gt(0)').remove();
        $('.centro-costo input').val('');
        $("#wrap-empresas .wrap-gestion-empresas").hide();
        $("#wrap-empresas .wrap-listado-empresas").show();
    });

    $(document).on('click', '#wrap-empresas .wrap-listado-empresas .edit-empresa', function(event) {

        $('#wrap-empresas .wrap-gestion-empresas button[type="submit"]').text('Editar Empresa');
        $('#wrap-empresas .wrap-gestion-empresas .title').text('Editar Empresa');
        $("#wrap-empresas .wrap-listado-empresas").hide();
        $("#wrap-empresas .wrap-gestion-empresas").show();

        let post_id = $(this).data('id');
        $("#empresa-form #empresa-id").val(post_id);
        $('body').addClass('actloader');

        // Enviar la solicitud AJAX para obtener los datos del usuario
        $.ajax({
            url: empresaAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'load_empresa_data',
                post_id: post_id
            },
            success: function(response) {
                if (response.success) {
                    $.each(response.data, function(key, value) {
                        if (key === 'administradores_empresa') {
                            var ids = value.map(function(admin) {
                                return admin.ID;
                            });
                            var select = $('#administradores_empresa');
                            select.val(ids).trigger('change');
                        } else if (key === 'estado_de_la_empresa') {
                            $('#' + key).val(value).trigger('change');
                        } else if (key !== 'centros_de_costos_empresa' || key !== 'administradores_empresa') {
                            $('#' + key).val(value);
                        }
                    });
                    if (response.data.centros_de_costos_empresa && response.data.centros_de_costos_empresa.length > 0) {

                        $.each(response.data.centros_de_costos_empresa, function(index, centro) {
                            var newRow = $('.centro-costo').first().clone();

                            // Actualizar los IDs y valores
                            newRow.find('label').attr('for', 'nombre-' + index);
                            newRow.find('input[name="codigo_centro[]"]')
                                .val(centro.codigo)
                                .attr('id', 'codigo-' + index)
                                .attr('name', 'codigo_centro[]');
                            newRow.find('input[name="nombre_centro[]"]')
                                .val(centro.nombre)
                                .attr('id', 'nombre-' + index)
                                .attr('name', 'nombre_centro[]');
                            $('.wrap-datos').append(newRow);
                        });
                    }

                    $('.centro-costo').first().remove();
                    $('body').removeClass('actloader');
                } else {
                    $('body').removeClass('actloader');
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

    $(document).on('click', '#wrap-empresas .wrap-listado-empresas .delete-empresa', function(event) {
        event.preventDefault();

        // Obtener el ID del usuario desde el botón
        let empresaid = $(this).data('id');

        // Mostrar la confirmación con SweetAlert
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará la Empresa de forma permanente.',
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
                    url: empresaAjax.ajaxurl, // La URL de admin-ajax.php en WordPress
                    method: 'POST',
                    data: {
                        action: 'delete_empresa', // Acción personalizada en WordPress
                        post_id: empresaid
                    },
                    success: function(response) {
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            Swal.fire(
                                '¡Eliminado!',
                                'El empresa ha sido eliminado exitosamente.',
                                'success'
                            ).then(() => {
                                var fileUrl = empresaAjax.plugin_url + "includes/parts/panel/empresa.php";

                                $.ajax({
                                    url: fileUrl,
                                    method: "GET",
                                    success: function(response) {
                                        $("#informacion").html(response);
                                        initEmpresas();
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
                                response.data.message || 'No se pudo eliminar el vehículo.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        // Mostrar mensaje de error si AJAX falla
                        Swal.fire(
                            'Error',
                            'Hubo un problema al intentar eliminar el vehículo.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    $(document).on('click', '#add-centro-row', function(e) {
        e.preventDefault();

        var franjaCount = $('.wrap-datos .centro-costo').length;

        var newRow = $('.centro-costo').last().clone();

        newRow.find('label').attr('for', 'centro-costo-' + franjaCount);
        newRow.find('input').val('').attr('id', 'centro-costo-' + franjaCount);

        newRow.find('.remove-payment-row').remove();

        $('.wrap-datos').append(newRow);
    });

    $(document).on('click', '.remove-centro-row', function(e) {
        e.preventDefault();

        var $wrapFranjas = $('.wrap-datos');
        var $franja = $(this).closest('.centro-costo');

        if ($wrapFranjas.find('.centro-costo').length > 1) {
            $franja.remove();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No se puede eliminar',
                text: 'Debe haber al menos una franja horaria configurada',
                confirmButtonText: 'Entendido'
            });
        }
    });

    /*DOCUMENTOS DE LA EMPRESA*/
    const fileIcons = {
        pdf: 'https://upload.wikimedia.org/wikipedia/commons/8/87/PDF_file_icon.svg',
        doc: 'https://es.wikipedia.org/wiki/Archivo:Microsoft_Office_Word_(2019%E2%80%93present).svg',
        docx: 'https://es.wikipedia.org/wiki/Archivo:Microsoft_Office_Word_(2019%E2%80%93present).svg',
        xls: 'https://upload.wikimedia.org/wikipedia/commons/3/34/Microsoft_Office_Excel_%282019%E2%80%93present%29.svg',
        xlsx: 'https://upload.wikimedia.org/wikipedia/commons/3/34/Microsoft_Office_Excel_%282019%E2%80%93present%29.svg',
        default: 'https://upload.wikimedia.org/wikipedia/commons/a/ac/No_image_available.svg',
    };

    // Añadir una nueva fila para documentos
    $(document).on('click', '#add-documento', function(evt) {
        evt.preventDefault();
        const newRow = `
                <div class="document-row">
                    <input type="file" name="documentos_de_la_empresa[][documento]" class="documento-input">
                    <img src="${fileIcons.default}" alt="Icono del archivo" class="document-icon" style="width: 32px; height: 32px;">
                    <button type="button" class="button remove-documento">Eliminar</button>
                </div>
            `;
        $('#documentos-container').append(newRow);
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

    // Eliminar una fila de documentos
    $(document).on('click', '.remove-documento', function() {
        $(this).closest('.document-row').remove();
    });

    /*ENVIO Y VALIDACION DE FORMULARIO*/
    $(document).on('focusin', '#empresa-form', function() {

        $(this).validate({
            rules: {
                estado_de_la_empresa: {
                    required: true,
                },
                nombre_empresa: {
                    required: true,
                },
                administradores_empresa: {
                    required: true,
                },
            },
            messages: {
                estado_de_la_empresa: "Esta información es necesaria",
                nombre_empresa: "Esta información es necesaria",
                administradores_empresa: "Esta información es necesaria",
            },
            submitHandler: function(form) {

                // Recoger los datos del formulario
                var formData = new FormData(form);
                formData.append('action', 'create_empresa');

                // Realizar la petición AJAX
                $.ajax({
                    url: empresaAjax.ajaxurl,
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
                                    $('#empresa-form')[0].reset();

                                    // Cargar nuevo contenido
                                    var fileUrl =
                                        empresaAjax.plugin_url +
                                        'includes/parts/panel/empresa.php';

                                    $.ajax({
                                        url: fileUrl,
                                        method: 'GET',
                                        success: function(response) {
                                            $('#informacion').html(response);
                                            initEmpresas();
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

function initEmpresas() {
    jQuery('#table-empresas').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        }
    });

    jQuery('#administradores_empresa').select2({
        placeholder: "Selecciona un Valor",
        allowClear: true,
        width: '100%'
    });
}