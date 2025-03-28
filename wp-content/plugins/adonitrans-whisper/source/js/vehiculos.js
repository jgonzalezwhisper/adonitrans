jQuery(document).ready(function($) {

    $(document).on('click', '#crear-vehiculo', function(event) {
        event.preventDefault();

        $('#vehiculo-form')[0].reset();
        $('#wrap-vehiculos .wrap-gestion-vehiculos button[type="submit"]').text('Crear Vehículo');

        $("#wrap-vehiculos .wrap-listado-vehiculos").hide();
        $("#wrap-vehiculos .wrap-gestion-vehiculos").show();
    });

    $(document).on('click', '#wrap-vehiculos .wrap-gestion-vehiculos button[type="button"]', function(event) {
        $('#vehiculo-form')[0].reset();
        $("#vehiculo-id").text('').val('');
        $('#select-rolesusuario').val(null).trigger('change');
        $("#wrap-vehiculos .wrap-gestion-vehiculos").hide();
        $("#wrap-vehiculos .wrap-listado-vehiculos").show();
    });

    $(document).on('click', '#wrap-vehiculos .wrap-listado-vehiculos .edit-vehiculo', function(event) {

        $('#wrap-vehiculos .wrap-gestion-vehiculos button[type="submit"]').text('Editar Vehículo');
        $('#wrap-vehiculos .wrap-gestion-vehiculos .title').text('Editar Vehículo');
        $("#wrap-vehiculos .wrap-listado-vehiculos").hide();
        $("#wrap-vehiculos .wrap-gestion-vehiculos").show();

        let post_id = $(this).data('id');
        $("#vehiculo-form #vehiculo-id").val(post_id);
        $('body').addClass('actloader');

        // Enviar la solicitud AJAX para obtener los datos del usuario
        $.ajax({
            url: vehiculoAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'load_vehiculo_data',
                post_id: post_id
            },
            success: function(response) {
                if (response.success) {
                    $.each(response.data, function(key, value) {
                        if (key === 'tipo_de_vehiculo' || key === 'propietario_de_vehiculo' || key === 'conductor_del_vehiculo') {
                            $('#' + key).val(value).trigger('change');
                        } else {
                            $('#' + key).val(value);
                        }
                    });

                    // ✅ Si hay archivos en el repetidor, agregarlos dinámicamente
                    if (response.data.repetidor_archivos_vehi && response.data.repetidor_archivos_vehi.length > 0) {
                        $('#wrap-archivos-vehiculo').empty(); // Limpiar archivos previos
                        $.each(response.data.repetidor_archivos_vehi, function(index, archivo) {
                            addFileVehiculo(archivo.nombre, archivo.archivo.url);
                        });
                    }

                    // ✅ Si hay imagenes en el repetidor, agregarlos dinámicamente
                    if (response.data.repetidor_imagenes_vehi && response.data.repetidor_imagenes_vehi.length > 0) {
                        $('#wrap-imagenes-vehiculo').empty(); // Limpiar archivos previos
                        $.each(response.data.repetidor_imagenes_vehi, function(index, archivo) {
                            addImagenVehiculo(archivo.nombre, archivo.archivo);
                        });
                    }

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

    // ✅ Función para agregar dinámicamente archivos
    function addFileVehiculo(nombre, url) {
        let uniqueID = Date.now(); // Genera un ID único basado en el tiempo
        let $clone = $('.clonar .archivo-vehiculo').clone().removeClass('clonar').show(); // Clonar y mostrar

        // Modificar los atributos for e id de los inputs para que sean únicos
        $clone.find('label').each(function() {
            let $label = $(this);
            let $input = $label.siblings('input'); // Buscar el input dentro del label

            if ($input.length) {
                let name = $input.attr('name').replace('[]', '');
                let newID = name + '_' + uniqueID;

                $input.attr('id', newID);
                $label.attr('for', newID);
            }
        });

        // 🔹 Eliminar el input file
        $clone.find('.wrap-file input[type="file"]').remove();

        // 🔹 Añadir enlace con la URL del archivo
        let linkElement = $('<a>', {
            class: 'url-archivo',
            href: url,
            html: '<i class="icofont-search-document"></i> Ver archivo',
            target: '_blank'
        });

        $clone.find('.wrap-file').append(linkElement); // Agregar el enlace dentro del div .wrap-file

        // 🔹 Asignar el nombre del archivo en el input correspondiente
        $clone.find('input.nombre-archivo').val(nombre);

        // Agregar al contenedor
        $('#wrap-archivos-vehiculo').append($clone);
    }

    // ✅ Función para agregar dinámicamente archivos
    function addImagenVehiculo(nombre, url) {
        let uniqueID = Date.now(); // Genera un ID único basado en el tiempo
        let $clone = $('.clonar .imagen-vehiculo').clone(); // Clonar y mostrar

        // Modificar los atributos for e id de los inputs para que sean únicos
        $clone.find('label').each(function() {
            let $label = $(this);
            let $input = $label.siblings('input'); // Buscar el input dentro del label

            if ($input.length) {
                let name = $input.attr('name').replace('[]', '');
                let newID = name + '_' + uniqueID;

                $input.attr('id', newID);
                $label.attr('for', newID);
            }
        });

        // 🔹 Eliminar el input file
        $clone.find('.wrap-file input[type="file"]').remove();

        // 🔹 Añadir enlace con la URL del archivo
        let linkElement = $('<a>', {
            class: 'url-archivo',
            href: url,
            html: '<i class="icofont-search-document"></i> Ver archivo',
            target: '_blank'
        });

        $clone.find('.wrap-file').append(linkElement); // Agregar el enlace dentro del div .wrap-file

        // 🔹 Asignar el nombre del archivo en el input correspondiente
        $clone.find('input.nombre-archivo').val(nombre);

        // Agregar al contenedor
        $('#wrap-imagenes-vehiculo').append($clone);
    }


    $(document).on('click', '#wrap-vehiculos .wrap-listado-vehiculos .delete-vehiculo', function(event) {
        event.preventDefault();

        // Obtener el ID del usuario desde el botón
        let vehiculoid = $(this).data('id');

        // Mostrar la confirmación con SweetAlert
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará el Vehículo de forma permanente.',
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
                    url: vehiculoAjax.ajaxurl, // La URL de admin-ajax.php en WordPress
                    method: 'POST',
                    data: {
                        action: 'delete_vehiculo', // Acción personalizada en WordPress
                        post_id: vehiculoid
                    },
                    success: function(response) {
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            Swal.fire(
                                '¡Eliminado!',
                                'El vehiculo ha sido eliminado exitosamente.',
                                'success'
                            ).then(() => {
                                var fileUrl = vehiculoAjax.plugin_url + "includes/parts/panel/vehiculo.php";

                                $.ajax({
                                    url: fileUrl,
                                    method: "POST",
                                    data: {
                                        action: 'render_html_panel',
                                    },
                                    success: function(response) {
                                        $("#informacion").html(response);
                                        initVehiculos();
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

    /*ENVIO Y VALIDACION DE FORMULARIO*/
    $(document).on('focusin', '#vehiculo-form', function() {

        $(this).validate({
            rules: {
                placa_vehiculo: {
                    required: true,
                },
                tipo_de_vehiculo: {
                    required: true,
                },
                modelo_vehiculo: {
                    required: true,
                },
                cantidad_pasajeros_vehiculo: {
                    required: true,
                    number: true,
                },
                marca_vehiculo: {
                    required: true,
                },
                serial_vehiculo: {
                    required: true,
                },
                chasis_vehiculo: {
                    required: true,
                },
                fecha_vencimiento_soat: {
                    required: true,
                },
                fecha_vencimiento_tecno_mecanica: {
                    required: true,
                },
                propietario_de_vehiculo: {
                    required: true,
                },
            },
            messages: {
                placa_vehiculo: "Ingresa la placa del Vehículo",
                tipo_de_vehiculo: "Ingresa el tipo del Vehículo",
                modelo_vehiculo: "Ingresa el modelo del Vehículo",
                cantidad_pasajeros_vehiculo: {
                    required: "Ingresa la cantidad del Vehículo",
                    number: "La cantidad debe ser en números"
                },
                marca_vehiculo: "Ingresa la marca del Vehículo",
                serial_vehiculo: "Ingresa el # de serial del Vehículo",
                chasis_vehiculo: "Ingresa el # de chasis del Vehículo",
                fecha_vencimiento_soat: {
                    required: 'Este campo es obligatorio',
                },
                fecha_vencimiento_tecno_mecanica: {
                    required: 'Este campo es obligatorio',
                },
                propietario_de_vehiculo: "Ingresa el Propietario del Vehículo",
            },
            submitHandler: function(form) {

                // Recoger los datos del formulario
                var formData = new FormData(form);
                formData.append('action', 'create_vehiculo');

                // Realizar la petición AJAX
                $.ajax({
                    url: vehiculoAjax.ajaxurl, // Ruta del endpoint AJAX
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
                                    $('#vehiculo-form')[0].reset();

                                    // Cargar nuevo contenido
                                    var fileUrl =
                                        vehiculoAjax.plugin_url +
                                        'includes/parts/panel/vehiculo.php';

                                    $.ajax({
                                        url: fileUrl,
                                        method: "POST",
                                        data: {
                                            action: 'render_html_panel',
                                        },
                                        success: function(response) {
                                            $('#informacion').html(response);
                                            initVehiculos();
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

    /*Archivos Vehiculo*/

    $(document).on('click', '#add-file-vehiculo', function(event) {
        event.preventDefault();

        let uniqueID = Date.now(); // Genera un ID único basado en el tiempo
        let $clone = $('.clonar .archivo-vehiculo').clone(); // Clonar y mostrar

        // Modificar los atributos for e id de los inputs para que sean únicos
        $clone.find('label').each(function() {
            let $label = $(this);
            let $input = $label.find('input');

            if ($input.length) {
                let name = $input.attr('name').replace('[]', '');
                let newID = name + '_' + uniqueID;

                $input.attr('id', newID);
                $label.attr('for', newID);
            }
        });

        // Agregar la clonación al contenedor
        $('#wrap-archivos-vehiculo').append($clone);
    });

    $(document).on('click', '#wrap-archivos-vehiculo .archivo-vehiculo .icofont-trash', function(event) {
        event.preventDefault();
        $(this).closest('.archivo-vehiculo').remove();
    });

    /*Imagenes Vehiculo*/

    $(document).on('click', '#add-imagen-vehiculo', function(event) {
        event.preventDefault();

        let uniqueID = Date.now(); // Genera un ID único basado en el tiempo
        let $clone = $('.clonar .imagen-vehiculo').clone(); // Clonar y mostrar

        // Modificar los atributos for e id de los inputs para que sean únicos
        $clone.find('label').each(function() {
            let $label = $(this);
            let $input = $label.find('input');

            if ($input.length) {
                let name = $input.attr('name').replace('[]', '');
                let newID = name + '_' + uniqueID;

                $input.attr('id', newID);
                $label.attr('for', newID);
            }
        });

        // Agregar la clonación al contenedor
        $('#wrap-imagenes-vehiculo').append($clone);
    });

    $(document).on('click', '#wrap-imagenes-vehiculo .imagen-vehiculo .icofont-trash', function(event) {
        event.preventDefault();
        $(this).closest('.imagen-vehiculo').remove();
    });


});