jQuery(document).ready(function($) {

    $(document).on('click', '.adonitrans-tabs-nav li', function(event) {
        var tabId = $(this).data("tab");

        $(".adonitrans-tabs-nav li").removeClass("active");
        $(this).addClass("active");
        $(".tab-content").removeClass("active");
        $("#" + tabId).addClass("active");
    });

    /*FRANJA HORARIA*/
    $(document).on('click', '#add-franja-row', function(e) {
        e.preventDefault();

        var franjaCount = $('#wrap-franjas .franja').length;

        var newRow = $('.franja').last().clone();

        newRow.find('label').attr('for', 'franja-' + franjaCount);
        newRow.find('input').val('').attr('id', 'franja-' + franjaCount);

        newRow.find('.remove-payment-row').remove();

        $('#wrap-franjas').append(newRow);
    });

    $(document).on('click', '.remove-franja-row', function(e) {
        e.preventDefault();

        var $wrapFranjas = $('#wrap-franjas');
        var $franja = $(this).closest('.franja');

        if ($wrapFranjas.find('.franja').length > 1) {
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


    /*TARIFAS DESCUENTOS*/
    $(document).on('click', '#add-tarifa-row', function(e) {
        e.preventDefault();

        var franjaCount = $('#wrap-tarifas .row-tarifa').length;

        var newRow = $('.row-tarifa').last().clone();

        newRow.find('label').attr('for', 'franja-' + franjaCount);
        newRow.find('input').val('').attr('id', 'franja-' + franjaCount);

        /*newRow.find('.remove-tarifa-row').remove();
        newRow.append('<button type="button" class="button remove-tarifa-row">Eliminar Tarifa</button>');*/

        $('#wrap-tarifas').append(newRow);
    });

    $(document).on('click', '.remove-tarifa-row', function(e) {
        e.preventDefault();

        var $wrapTarifas = $('#wrap-tarifas');
        var $rowTarifa = $(this).closest('.row-tarifa');

        if ($wrapTarifas.find('.row-tarifa').length > 1) {
            $rowTarifa.remove();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No se puede eliminar',
                text: 'Debe haber al menos una tarifa configurada',
                confirmButtonText: 'Entendido'
            });
        }
    });

    // Enviar formulario vía AJAX
    $(document).on('focusin', '#ajustes-generales', function() {
        $(this).validate({
            rules: {
                // Validación general para campos específicos
                'franjas_horas_trabajo[][nombre]': {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
                },
                'franjas_horas_trabajo[][hora_inicio]': {
                    required: true,
                    time24: true, // Validación personalizada para formato 24 horas
                },
                'franjas_horas_trabajo[][hora_fin]': {
                    required: true,
                    time24: true, // Validación personalizada para formato 24 horas
                },
            },
            messages: {
                'franjas_horas_trabajo[][nombre]': {
                    required: 'El nombre de la franja es obligatorio.',
                    minlength: 'El nombre debe tener al menos 3 caracteres.',
                    maxlength: 'El nombre no puede tener más de 50 caracteres.',
                },
                'franjas_horas_trabajo[][hora_inicio]': {
                    required: 'La hora de inicio es obligatoria.',
                    time24: 'Ingrese una hora válida en formato 24 horas (HH:mm).',
                },
                'franjas_horas_trabajo[][hora_fin]': {
                    required: 'La hora de fin es obligatoria.',
                    time24: 'Ingrese una hora válida en formato 24 horas (HH:mm).',
                },
            },
            // Validación personalizada para formato 24 horas
            submitHandler: function(form) {
                var formData = new FormData(form);
                formData.append('action', 'update_generales');

                // Petición AJAX
                $.ajax({
                    url: administracionAjax.ajaxurl,
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
                                    $('#ajustes-generales')[0].reset();

                                    $.ajax({
                                        url: administracionAjax.plugin_url + 'includes/parts/panel/administracion.php',
                                        method: "POST",
                                        data: {
                                            action: 'render_html_panel',
                                        },
                                        success: function(response) {
                                            $('#informacion').html(response);
                                        },
                                        error: function() {
                                            $('#informacion').html('<p>Error al cargar el contenido. Intenta nuevamente.</p>');
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
    $(document).on('submit', '#ajustes-generales', function(e) {
        e.preventDefault();
        if ($(this).valid()) {
            $(this).submit();
        }
    });

});