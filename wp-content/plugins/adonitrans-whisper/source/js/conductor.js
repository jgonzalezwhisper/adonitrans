let storedData = JSON.parse(localStorage.getItem('dataRecorrido')) || {};
jQuery(document).ready(function($) {

    /*** FRANJAS DE ASIGNACIÓN ***/
    $(document).on('click', '#wrap-peajes .button-add', function(e) {
        e.preventDefault();

        let franjaCount = $('#wrap-peajes .franja').length;
        let newRow = $('#clonar-peaje .franja').clone();

        newRow.find('label').attr('for', `franja-${franjaCount}`);
        newRow.find('input').val('').attr('id', `franja-${franjaCount}`);

        // Restablecer y configurar select2
        let newSelect = newRow.find('.select').addClass('select_vehiculo');
        $('#wrap-peaje').append(newRow);

        newSelect.select2({
            placeholder: "Selecciona un Valor",
            allowClear: true,
            width: '100%'
        });
    });

    $(document).on('click', '#wrap-peajes .remove', function(e) {
        e.preventDefault();
        $(this).closest('.franja').remove();
    });

    /*** GESTIÓN DEL TEMPORIZADOR ***/
    let tiempoEspera = 1;
    let contadorInterval, startTime, isWaitingPeriod;

    function convertirTiempo(segundos) {
        let minutos = String(Math.floor(segundos / 60)).padStart(2, '0');
        let segundosRestantes = String(segundos % 60).padStart(2, '0');
        return `${minutos}:${segundosRestantes}`;
    }

    function actualizarListaRecorrido() {
        let dataRecorrido = JSON.parse(localStorage.getItem('dataRecorrido')) || {};
        let listHtml = '';

        if (dataRecorrido.horaLlegada) {
            listHtml += `<li><strong>Hora Llegada (Conductor):</strong> <span>${dataRecorrido.horaLlegada}</span></li>`;
        }
        if (dataRecorrido.horaInicio) {
            listHtml += `<li><strong>Hora Inicio Recorrido:</strong> <span>${dataRecorrido.horaInicio}</span></li>`;
        }
        if (dataRecorrido.horaFin) {
            listHtml += `<li><strong>Finalización Recorrido:</strong> <span>${dataRecorrido.horaFin}</span></li>`;
        }

        $('#list-info-recorrido').html(listHtml);
    }

    function guardarEnLocalStorage(clave, valor) {
        let dataRecorrido = JSON.parse(localStorage.getItem('dataRecorrido')) || {};
        dataRecorrido[clave] = valor;
        localStorage.setItem('dataRecorrido', JSON.stringify(dataRecorrido));
        actualizarListaRecorrido();
    }

    function startTimer() {
        startTime = new Date();
        isWaitingPeriod = true;
        $('.contador').css('color', 'red');
        contadorInterval = setInterval(updateTimer, 1000);
        localStorage.setItem('startTime', startTime.getTime());
    }

    function stopTimer() {
        clearInterval(contadorInterval);
    }

    function updateTimer() {
        let now = new Date();
        let diff = Math.floor((now - startTime) / 1000);
        $('.contador').text(convertirTiempo(diff));

        if (isWaitingPeriod && diff >= tiempoEspera * 60) {
            $('.contador').css('color', 'green');
            isWaitingPeriod = false;
        }
    }
    console.log("dataRecorrido " + storedData);
    if (storedData) {
        actualizarListaRecorrido();
        console.log("Entra IF " + storedData);
    }

    $(document).on('click', '#conductor-form .button:not(.save-info)', function(e) {
        e.preventDefault();
        tiempoEspera = parseInt($('#tiempo_espera').val(), 10) || 1;

        let action = $(this).data('action');
        let horaActual = new Date().toLocaleTimeString();

        if (action === 'llegada') {
            $("#wrap-contador-espera").removeClass('ocultar');
            $("#conductor-form .button[data-action='cancelar']").addClass('ocultar');
            $(this).addClass('ocultar');
            startTimer();
            guardarEnLocalStorage('horaLlegada', horaActual);
        } else if (action === 'iniciar') {
            stopTimer();
            clearInterval(contadorInterval);
            isWaitingPeriod = false;
            startTime = null;
            guardarEnLocalStorage('horaInicio', horaActual);
            $("#conductor-form .button[data-action='save-info'], #conductor-form .button[data-action='end-recorrido']").removeClass('ocultar');
        } else if (action === 'end-recorrido') {
            guardarEnLocalStorage('horaFin', horaActual);
            localStorage.removeItem('dataRecorrido');
            actualizarListaRecorrido();
        }
    });

    $(document).on('click', '#conductor-form .save-info', function(e) {
        e.preventDefault();
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se guardarán los datos del recorrido.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, guardar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData($('#conductor-form')[0]);
                formData.append('action', 'guardar_peajes');
                $('body').addClass('actloader');
                $.ajax({
                    url: conductorAjax.ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('body').removeClass('actloader');
                        Swal.fire(response.success ? "¡Guardado!" : "Error", response.success ? "Información Actualizada." : "Hubo un problema al guardar los peajes.", response.success ? "success" : "error");
                    },
                    error: function() {
                        $('body').removeClass('actloader');
                        Swal.fire("Error", "Hubo un problema con la conexión.", "error");
                    }
                });
            }
        });
    });

    $(document).on('click', '#wrap-recorridos .wrap-listado-recorridos .iniciar-recorrido', function(event) {
        event.preventDefault();

        // Obtener el ID del usuario desde el botón
        let recorridoid = $(this).data('id');

        // Mostrar la confirmación con SweetAlert
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede reversar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, Iniciar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $('body').addClass('actloader');
                $.ajax({
                    url: recorridoAjax.ajaxurl, // La URL de admin-ajax.php en WordPress
                    method: 'POST',
                    data: {
                        action: 'iniciar_recorrido', // Acción personalizada en WordPress
                        post_id: recorridoid
                    },
                    success: function(response) {
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            Swal.fire(
                                '¡Recorrido Iniciado!',
                                'Has comenzado tu recorrido con éxito. ¡Disfruta del viaje y mantente seguro!',
                                'success'
                            ).then(() => {
                                var fileUrl = recorridoAjax.plugin_url + "includes/parts/panel/conductor.php";

                                $.ajax({
                                    url: fileUrl,
                                    method: "POST",
                                    data: {
                                        action: 'render_html_panel',
                                        post_id: recorridoid
                                    },
                                    success: function(response) {
                                        $("#informacion").html(response);
                                        initRecorridos();
                                    },
                                    error: function() {
                                        $("#informacion").html("<p>Error al cargar el contenido. Intenta nuevamente.</p>");
                                    }
                                });
                            });
                            $('body').removeClass('actloader');

                        } else {
                            $('body').removeClass('actloader');
                            // Mostrar mensaje de error
                            Swal.fire(
                                'Error',
                                response.data.message || 'No se pudo eliminar el vehículo.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        $('body').removeClass('actloader');
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

    $(document).on('click', '#wrap-recorridos .wrap-listado-recorridos .panel-recorrido', function(event) {
        event.preventDefault();

        // Obtener el ID del usuario desde el botón
        let recorridoid = $(this).data('id');

        // Mostrar la confirmación con SweetAlert
        Swal.fire({
            title: '¿Desea Continuar?',
            text: 'Esta seguro de retomar el servicio?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, Continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $('body').addClass('actloader');

                var fileUrl = recorridoAjax.plugin_url + "includes/parts/panel/conductor.php";

                $.ajax({
                    url: fileUrl,
                    method: "POST",
                    data: {
                        action: 'render_html_panel',
                        post_id: recorridoid
                    },
                    success: function(response) {
                        $("#informacion").html(response);
                        console.log("Entra AJAX ANTES " + storedData);
                        if (storedData) {
                            actualizarListaRecorrido();
                            console.log("Entra AJAX TAL " + storedData);
                        }
                        $('body').removeClass('actloader');
                    },
                    error: function() {
                        $("#informacion").html("<p>Error al cargar el contenido. Intenta nuevamente.</p>");
                    }
                });

            }
        });
    });
});