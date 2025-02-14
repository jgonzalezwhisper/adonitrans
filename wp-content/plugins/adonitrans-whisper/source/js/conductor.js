jQuery(document).ready(function($) {
    /*FRANJAS DE ASIGNACION*/
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

    /*ACCIONES BOTONES*/
    let tiempoEspera; // Declarar la variable en un ámbito superior
    let contadorInterval;
    let startTime;
    let isWaitingPeriod;

    let tiempoEsperado = localStorage.getItem('tiempo_espera');

    // Validar si hay un tiempo configurado y es un número válido
    if (tiempoEsperado !== null && isNaN(tiempoEsperado)) {

        console.log("Entra valido");

        const tiempoFormateado = convertirTiempo(tiempoEsperado);
        $('.contador').text(tiempoFormateado);
        $("#wrap-contador-espera").removeClass('ocultar');
        $("#conductor-form .button[data-action='llegada'], #conductor-form .button[data-action='cancelar']").addClass('ocultar');
        $("#conductor-form .button[data-action='save-info'], #conductor-form .button[data-action='end-recorrido']").removeClass('ocultar');
    } else {
    }

    // Usar el valor de tiempoEsperado en tu lógica
    console.log("Tiempo esperado a usar:", tiempoEsperado);

    function convertirTiempo(segundos) {
        // Calcular minutos y segundos
        const minutos = Math.floor(segundos / 60); // Obtener los minutos completos
        const segundosRestantes = segundos % 60; // Obtener los segundos restantes

        // Formatear para que siempre tenga dos dígitos
        const minutosFormateados = String(minutos).padStart(2, '0'); // Asegurar dos dígitos
        const segundosFormateados = String(segundosRestantes).padStart(2, '0'); // Asegurar dos dígitos

        // Devolver el tiempo en formato minutos:segundos
        return `${minutosFormateados}:${segundosFormateados}`;
    }

    // Ejemplo de uso
    const tiempoEnSegundos = 350; // 5 minutos y 50 segundos
    const tiempoFormateado = convertirTiempo(tiempoEnSegundos);
    console.log(tiempoFormateado); // Salida: "05:50"

    function startTimer() {
        startTime = new Date();
        isWaitingPeriod = true;
        $('.contador').css('color', 'red');
        contadorInterval = setInterval(updateTimer, 1000);

        // Guardar el tiempo de inicio en localStorage
        localStorage.setItem('startTime', startTime.getTime());
    }

    function stopTimer() {
        clearInterval(contadorInterval);
    }

    function updateTimer() {
        const now = new Date();
        const diff = Math.floor((now - startTime) / 1000); // Diferencia en segundos
        const minutes = Math.floor(diff / 60);
        const seconds = diff % 60;

        // Formatear el tiempo para mostrarlo en el contador
        const formattedTime = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        $('.contador').text(formattedTime);

        // Cambiar el color del contador después de que pase el tiempo de espera
        if (isWaitingPeriod && diff >= tiempoEspera * 60) {
            $('.contador').css('color', 'green');
            isWaitingPeriod = false;
        }
    }

    function calculateAndSaveTime() {
        const now = new Date();
        const diff = Math.floor((now - startTime) / 1000); // Diferencia en segundos
        const totalMinutes = Math.ceil(diff / 60); // Redondear hacia arriba

        // Guardar el tiempo calculado en el campo hidden
        $('#tiempo_de_espera').val(totalMinutes);
    }

    // Recuperar el tiempo de inicio desde localStorage al cargar la página
    const storedStartTime = localStorage.getItem('startTime');
    if (storedStartTime) {
        startTime = new Date(parseInt(storedStartTime, 10));
        isWaitingPeriod = true;
        $('.contador').css('color', 'red');
        contadorInterval = setInterval(updateTimer, 1000);
    }

    $(document).on('click', '#conductor-form .button:not(.save-info)', function(e) {
        e.preventDefault();

        // Obtener el valor de tiempo_espera desde un campo oculto
        tiempoEspera = parseInt($('#tiempo_espera').val(), 10); // Cambia esto según tu campo oculto
        if (isNaN(tiempoEspera)) {
            tiempoEspera = 1; // Valor por defecto para pruebas
        }

        const action = $(this).data('action');

        if (action === 'llegada') {
            $("#wrap-contador-espera").removeClass('ocultar');
            $("#conductor-form .button[data-action='cancelar']").addClass('ocultar');
            $(this).addClass('ocultar');
            startTimer();
        } else if (action === 'iniciar') {
            stopTimer();
            calculateAndSaveTime();

            tempstarttime = localStorage.getItem('startTime');
            localStorage.setItem('tiempo_espera', tempstarttime);

            // Limpiar localStorage cuando se inicia el proceso
            localStorage.removeItem('startTime');

            console.log(tempstarttime);
        }
    });

    $(document).on('click', '#conductor-form .save-info', function(e) {
        e.preventDefault(); // Evita el comportamiento por defecto del enlace

        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se guardarán los datos del recorrido.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, guardar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                let form = $('#conductor-form')[0]; // Obtiene el formulario
                let formData = new FormData(form);
                formData.append('action', 'guardar_peajes'); // Acción AJAX de WordPress

                $.ajax({
                    url: conductorAjax.ajaxurl, // admin-ajax.php
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire("¡Guardado!", "Los peajes se guardaron correctamente.", "success");
                        } else {
                            Swal.fire("Error", "Hubo un problema al guardar los peajes.", "error");
                        }
                    },
                    error: function() {
                        Swal.fire("Error", "Hubo un problema con la conexión.", "error");
                    }
                });
            }
        });
    });
});