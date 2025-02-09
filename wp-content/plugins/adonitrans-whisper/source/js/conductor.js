jQuery(document).ready(function($) {

    /*FRANJAS DE ASIGNACION*/
    $(document).on('click', '#wrap-peajes .button-add', function(e) {
        e.preventDefault();

        console.log("Entra");

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