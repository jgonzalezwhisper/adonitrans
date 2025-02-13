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
    $(document).ready(function() {
        let tiempoEspera; // Declarar la variable en un ámbito superior
        let contadorInterval;
        let startTime;
        let isWaitingPeriod;

        function startTimer() {
            startTime = new Date();
            isWaitingPeriod = true;
            $('.contador').css('color', 'red');
            contadorInterval = setInterval(updateTimer, 1000);
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
            $('#tiempo_calculado').val(totalMinutes);
        }

        $(document).on('click', '#conductor-form .button:not(.save-info)', function(e) {
            e.preventDefault();

            // Obtener el valor de tiempo_espera desde un campo oculto
            tiempoEspera = parseInt($('input[name="tiempo_espera"]').val(), 10); // Cambia esto según tu campo oculto
            if (isNaN(tiempoEspera)) {
                tiempoEspera = 1; // Valor por defecto para pruebas
            }

            const action = $(this).data('action');

            if (action === 'llegada') {
                $("#wrap-contador-espera").show();
                $("#conductor-form .button[data-action='cancelar']").hide();
                $(this).hide();
                startTimer();
            } else if (action === 'iniciar') {
                stopTimer();
                calculateAndSaveTime();
            }
        });
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