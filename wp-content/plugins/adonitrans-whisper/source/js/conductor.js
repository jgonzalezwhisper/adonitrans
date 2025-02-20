let storedData = JSON.parse(localStorage.getItem('dataRecorrido')) || {};
let contadorInterval, startTime, isWaitingPeriod, timetempsav = 0;
if (!storedData.estado) {
    storedData.estado = "sin-iniciar";
    localStorage.setItem('dataRecorrido', JSON.stringify(storedData));
}

function actualizarStoredData() {
    storedData = JSON.parse(localStorage.getItem('dataRecorrido')) || {};
}


function formatoMonedaColombiana(valor) {
    valor = valor.toString().split('.')[0];
    return valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

jQuery(document).ready(function($) {
    const tiempoEspera = 1;

    // Verificar si hay un tiempo de inicio guardado
    const savedStartTime = localStorage.getItem('startTime');
    if (savedStartTime) {
        startTime = new Date(parseInt(savedStartTime, 10));
        isWaitingPeriod = true;
        contadorInterval = setInterval(updateTimer, 1000);
        updateTimer(); // Actualizar el contador inmediatamente
    }

    function guardarStoredData() {
        localStorage.setItem('dataRecorrido', JSON.stringify(storedData));
    }

    function guardarRecorridoId(recorridoid) {
        actualizarStoredData();
        storedData.recorridoid = recorridoid;
        guardarStoredData();
    }

    function verificarRecorridoId(recorridoid) {
        actualizarStoredData();
        if (storedData.recorridoid && storedData.recorridoid !== recorridoid) {
            Swal.fire({
                title: 'Recorrido en curso',
                text: `Actualmente tienes un recorrido en curso con ID: ${storedData.recorridoid}. Por favor, finalízalo antes de iniciar otro.`,
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return false;
        }
        return true;
    }

    // Función para actualizar el estado en localStorage
    function actualizarEstado(nuevoEstado) {
        actualizarStoredData();
        storedData.estado = nuevoEstado;
        guardarStoredData();
    }

    // Función para convertir segundos a formato MM:SS
    function convertirTiempo(segundos) {
        const minutos = String(Math.floor(segundos / 60)).padStart(2, '0');
        const segundosRestantes = String(segundos % 60).padStart(2, '0');
        return `Tiempo de espera total: ${minutos}:${segundosRestantes}`;
    }

    function calcularTiempoExtra() {
        // Obtener el valor del tiempo de espera en minutos desde el input
        const tiempoEsperaMinutos = parseInt($('#tiempo_espera').val(), 10) || 0;

        // Convertir el tiempo de espera a segundos
        const tiempoEsperaSegundos = tiempoEsperaMinutos * 60;

        // Obtener el tiempo de espera almacenado (si existe)
        const storedTimeEspera = storedData.timeEspera || 0;

        // Calcular la diferencia en segundos
        const diferenciaSegundos = storedTimeEspera - tiempoEsperaSegundos;

        // Convertir la diferencia a minutos y redondear hacia arriba
        const minutosExtras = Math.ceil(diferenciaSegundos / 60);

        // Retornar el tiempo extra en minutos
        return minutosExtras;
    }

    // Función para actualizar la lista de recorrido en la interfaz
    function refreshFront() {
        actualizarStoredData();
        let listHtml = '';

        if (storedData.horaLlegada) {
            listHtml += `<li><strong>Hora Llegada (Conductor):</strong> <span>${storedData.horaLlegada}</span></li>`;
        }
        if (storedData.horaInicio) {
            listHtml += `<li><strong>Hora Inicio Recorrido:</strong> <span>${storedData.horaInicio}</span></li>`;
        }

        if (storedData.estado == "llegada" || storedData.estado == "iniciar") {
            $("#wrap-contador-espera, #wrap-listadocheck, #wrap_trayecto_finalizar").removeClass('ocultar');

            // Obtener el tiempo de espera definido en el input
            const tiempoEspera = parseInt($("#tiempo_espera").val(), 10) || 1;

            // Cambiar el color del contador según el tiempo transcurrido
            if ((timetempsav < tiempoEspera * 60)) {
                $('.contador').css('color', 'red');
            } else {
                $('.contador').css('color', 'green');
            }
        }

        if (storedData.estado == "llegada") {
            $("#conductor-form .button[data-action='cancelar'], #conductor-form .button[data-action='llegada']").addClass('ocultar');
            $("#wrap-listadocheck").removeClass('ocultar');
        }

        if (storedData.estado == "iniciar") {
            $("#conductor-form .button[data-action='iniciar']").addClass('ocultar');
            $("#conductor-form .button[data-action='cancelar'], #conductor-form .button[data-action='llegada']").addClass('ocultar');
            $("#conductor-form .button[data-action='save-info'], #conductor-form .button[data-action='end-recorrido']").removeClass('ocultar');
            $("#wrap-listadocheck").removeClass('ocultar');

            $('.contador').text(convertirTiempo(storedData.timeEspera)).css('color', 'green');
            const minutosExtras = calcularTiempoExtra();

            if (minutosExtras > 0) {
                listHtml += `<li><strong>Minutos Extra:</strong> <span>${minutosExtras}</span></li>`;
            }
        }

        if (storedData.horaFin) {
            listHtml += `<li><strong>Finalización Recorrido:</strong> <span>${storedData.horaFin}</span></li>`;
        }

        $('#list-info-recorrido').html(listHtml);
    }

    // Función para guardar datos en localStorage
    function guardarEnLocalStorage(clave, valor) {
        actualizarStoredData();
        storedData[clave] = valor;
        guardarStoredData();
        refreshFront();
    }

    // Función para iniciar el temporizador
    function startTimer() {
        startTime = new Date();
        isWaitingPeriod = true;
        $('.contador').css('color', 'red');
        contadorInterval = setInterval(updateTimer, 1000);
        localStorage.setItem('startTime', startTime.getTime());
    }

    // Función para detener el temporizador
    function stopTimer() {
        clearInterval(contadorInterval);
        localStorage.removeItem('startTime');
    }

    // Función para actualizar el temporizador
    function updateTimer() {
        const now = new Date();
        const diff = Math.floor((now - startTime) / 1000);
        timetempsav = diff;
        $('.contador').text(convertirTiempo(diff));

        if (isWaitingPeriod && diff >= tiempoEspera * 60) {
            $('.contador').css('color', 'green');
            isWaitingPeriod = false;
        }
    }

    // Evento para manejar los botones del formulario del conductor
    $(document).on('click', '#conductor-form .button:not(.save-info)', function(e) {
        e.preventDefault();
        const tiempoEspera = parseInt($('#tiempo_espera').val(), 10) || 1;
        const action = $(this).data('action');
        const horaActual = new Date().toLocaleTimeString();

        if (action === 'llegada') {
            $("#conductor-form .button[data-action='cancelar']").addClass('ocultar');
            $(this).addClass('ocultar');
            startTimer();
            actualizarEstado("llegada");
            guardarEnLocalStorage('horaLlegada', horaActual);
            refreshFront();
            $("#wrap-contador-espera").removeClass('ocultar');
        } else if (action === 'iniciar') {
            stopTimer();
            clearInterval(contadorInterval);
            isWaitingPeriod = false;
            startTime = null;

            /*Calcular tiempo a guardar en localstorage*/
            let timeEmpSeg = (tiempoEspera * 60);
            let extramincond = 0;
            if (timetempsav > timeEmpSeg) {
                extramincond = Math.ceil((timetempsav - timeEmpSeg) / 60)
            }

            actualizarEstado("iniciar");
            guardarEnLocalStorage('horaInicio', horaActual);
            guardarEnLocalStorage('timeEspera', timetempsav);
            guardarEnLocalStorage('minutosExtras', extramincond);
            refreshFront();
            $(this).addClass('ocultar');
            $("#conductor-form .button[data-action='save-info'], #conductor-form .button[data-action='end-recorrido']").removeClass('ocultar');
        } else if (action === 'cancelar') {
            return;
        } else if (action === 'end-recorrido') {

            var valcomentconductor = $('#trayecto_commen').val().trim();

            if (valcomentconductor === "") {
                Swal.fire(
                    'Importante',
                    'Es necesario que realice un comentario sobre el recorrido antes de finalizarlo.',
                    'error'
                );
                return;
            } else {
                Swal.fire({
                    title: "¿Estás seguro de finalizar el recorrido?",
                    text: "Una vez que finalices el recorrido, no podrás revertir esta acción. Asegúrate de haber completado todos los pasos necesarios antes de continuar.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, Finalizar Recorrido",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {

                        actualizarEstado("end-recorrido");
                        guardarEnLocalStorage('horaFin', horaActual);
                        refreshFront();

                        const formData = new FormData($('#conductor-form')[0]);
                        formData.append('action', 'finalizar_recorrido_conductor');

                        // Obtener los datos del localStorage
                        const dataRecorrido = JSON.parse(localStorage.getItem('dataRecorrido'));

                        // Agregar los datos del localStorage al FormData
                        for (const key in dataRecorrido) {
                            if (dataRecorrido.hasOwnProperty(key)) {
                                formData.append(key, dataRecorrido[key]);
                            }
                        }
                        
                        $.ajax({
                            url: conductorAjax.ajaxurl,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {

                                if (response.data.datos) {

                                    var costos = response.data.datos;
                                    var ulContent = '';
                                    var totalRecorrido = 0;

                                    // Recorrer los costos para construir el contenido del ul
                                    $.each(costos, function(index, costo) {
                                        if (costo.motivo !== 'Total Recorrido') {
                                            ulContent += '<li><strong>' + costo.motivo + ':</strong> <span class="symbol">$<span class="price">' + formatoMonedaColombiana(costo.valor) + '</span></span></li>';
                                        } else {
                                            totalRecorrido = costo.valor;
                                        }
                                    });

                                    $('#wrap-total-calculo .trayecto_total_detail ul').empty().html(ulContent);

                                    $('#wrap-total-calculo h4 span').text('$' + formatoMonedaColombiana(totalRecorrido));

                                    $('#wrap-total-calculo').removeClass('ocultar');
                                }

                                $("#conductor-form .trayecto_bottons .btn").addClass('ocultar');

                                $('body').removeClass('actloader');
                                Swal.fire(response.success ? "¡Guardado!" : "Error", response.success ? "Recorrido Finalizado." : "Hubo un problema al guardar los datos.", response.success ? "success" : "error");

                                localStorage.clear();
                            },
                            error: function() {
                                $('body').removeClass('actloader');
                                Swal.fire("Error", "Hubo un problema con la conexión.", "error");
                            }
                        });
                    }
                });
            }
        }
    });

    // Evento para guardar información del recorrido
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
                const formData = new FormData($('#conductor-form')[0]);
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

    // Evento para iniciar un recorrido
    $(document).on('click', '#wrap-recorridos .wrap-listado-recorridos .iniciar-recorrido', function(event) {
        event.preventDefault();
        const recorridoid = $(this).data('id');

        // Verificar si el recorridoid es el mismo
        if (!verificarRecorridoId(recorridoid)) {
            return;
        }

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
                guardarRecorridoId(recorridoid); // Guardar el recorridoid
                $.ajax({
                    url: recorridoAjax.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'iniciar_recorrido',
                        post_id: recorridoid
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                '¡Recorrido Iniciado!',
                                'Has comenzado tu recorrido con éxito. ¡Disfruta del viaje y mantente seguro!',
                                'success'
                            ).then(() => {
                                const fileUrl = recorridoAjax.plugin_url + "includes/parts/panel/conductor.php";

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
                                        actualizarEstado("sin-iniciar");
                                    },
                                    error: function() {
                                        $("#informacion").html("<p>Error al cargar el contenido. Intenta nuevamente.</p>");
                                    }
                                });
                            });
                            $('body').removeClass('actloader');

                        } else {
                            $('body').removeClass('actloader');
                            Swal.fire(
                                'Error',
                                response.data.message || 'No se pudo eliminar el vehículo.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        $('body').removeClass('actloader');
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

    // Evento para continuar un recorrido
    $(document).on('click', '#wrap-recorridos .wrap-listado-recorridos .panel-recorrido', function(event) {
        event.preventDefault();
        const recorridoid = $(this).data('id');

        // Verificar si el recorridoid es el mismo
        if (!verificarRecorridoId(recorridoid)) {
            return;
        }

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
                guardarRecorridoId(recorridoid); // Guardar el recorridoid

                const fileUrl = recorridoAjax.plugin_url + "includes/parts/panel/conductor.php";

                $.ajax({
                    url: fileUrl,
                    method: "POST",
                    data: {
                        action: 'render_html_panel',
                        post_id: recorridoid
                    },
                    success: function(response) {
                        $("#informacion").html(response);
                        if (!storedData.estado) {
                            storedData.estado = "sin-iniciar";
                            guardarStoredData();
                        }
                        actualizarStoredData();
                        if (storedData) {
                            refreshFront();
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

    /*PEAJES*/
    $(document).on('click', '#wrap-peajes .button-add', function(e) {
        e.preventDefault();

        var franjaCount = $('#wrap-peajes .franja').length;
        var newRow = $('#clonar-peaje .franja').clone();

        newRow.find('label').attr('for', 'franja-' + franjaCount);
        newRow.find('input').val('').attr('id', 'franja-' + franjaCount);

        // Obtener el select clonado y restablecer su estado
        var newSelect = newRow.find('.select');
        newSelect.addClass('select_vehiculo');

        // Agregar la nueva fila al DOM
        $('#wrap-peaje').append(newRow);

        newSelect.select2({
            placeholder: "Selecciona un Valor",
            allowClear: true,
            width: '100%'
        });
    });

    $(document).on('click', '#wrap-peajes .remove', function(e) {
        e.preventDefault();

        var $wrapFranjas = $('#wrap-peaje');
        var $franja = $(this).closest('.franja');

        $franja.remove();
    });
});