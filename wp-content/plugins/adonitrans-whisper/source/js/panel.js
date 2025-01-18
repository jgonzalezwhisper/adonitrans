jQuery(document).ready(function($) {

    $.validator.addMethod(
        "checkPassword",
        function(value, element) {
            // Validaciones individuales
            const hasNumber = /\d/.test(value); // Contiene al menos un número
            const hasSpecialChar = /[*#=\-_.\/!¿?]/.test(value); // Contiene al menos un carácter especial
            const hasUpperCase = /[A-Z]/.test(value); // Contiene al menos una mayúscula
            const lengthValid = value.length >= 8 && value.length <= 15; // Longitud entre 8 y 15 caracteres

            // Actualización de la lista de requisitos visualmente
            $("li:contains('Entre 8 y 15 carácteres')").toggleClass(
                "tachado",
                lengthValid
            );
            $("li:contains('Un número')").toggleClass("tachado", hasNumber);
            $("li:contains('Un carácter especial (*#=-_./!¿?)')").toggleClass(
                "tachado",
                hasSpecialChar
            );
            $("li:contains('Una mayúscula')").toggleClass("tachado", hasUpperCase);

            // Retorna verdadero si todos los requisitos se cumplen
            return (
                this.optional(element) ||
                (hasNumber && hasSpecialChar && hasUpperCase && lengthValid)
            );
        },
        "La contraseña debe cumplir con los requisitos."
    );

    // Agregar método personalizado para validación de tiempo en formato 24 horas
    $.validator.addMethod(
        'time24',
        function(value, element) {
            return this.optional(element) || /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(value);
        },
        'Por favor, ingrese una hora válida en formato 24 horas (HH:mm).'
    );

    var ps = new PerfectScrollbar('#informacion', {
        wheelSpeed: 2,
        wheelPropagation: true,
        minScrollbarLength: 20
    });

    $(document).on('click', '.acordeon-header', function(event) {
        event.preventDefault();
        let body = $(this).next('.acordeon-body');
        let icon = $(this).find('i');
        body.slideToggle(function() {
            if (body.is(':visible')) {
                icon.removeClass('icofont-plus').addClass('icofont-minus');
            } else {
                icon.removeClass('icofont-minus').addClass('icofont-plus');
            }
        });
    });

    $("#lateral ul li[data-action]").on("click", function() {

        $("#lateral ul li").removeClass('active');
        $(this).addClass('active');

        var data_action = $(this).data("action");
        if (data_action === "logout") {
            return;
        }
        $('body').addClass('actloader');
        var fileUrl = panelAjax.plugin_url + "includes/parts/panel/" + data_action + ".php";

        $.ajax({
            url: fileUrl,
            method: "POST",
            data: {
                action: 'render_html_panel',
            },
            cache: false,
            success: function(response) {
                $('body').removeClass('actloader');
                $("#informacion").html(response);

                if (data_action == 'empresa') {
                    initEmpresas();
                }
                if (data_action == 'recorrido') {
                    initRecorridos();
                }
                if (data_action == 'vehiculo') {
                    initVehiculos();
                }
                if (data_action == 'usuario') {
                    initUsuarios();
                }
            },
            error: function() {
                $('body').removeClass('actloader');
                $("#informacion").html("<p>Error al cargar el contenido. Intenta nuevamente.</p>");
            }
        });
    });
});

function checkPassword(value) {
    let hasNumber = /\d/.test(value);
    let hasSpecialChar = /[*\-_.\/!¿?]/.test(value);
    let hasUpperCase = /[A-Z]/.test(value);
    let lengthValid = value.length >= 8 && value.length <= 15;

    // Actualizar la UI de los requisitos de la contraseña
    jQuery("li:contains('Entre 8 y 15 carácteres')").toggleClass("tachado", lengthValid);
    jQuery("li:contains('Un número')").toggleClass("tachado", hasNumber);
    jQuery("li:contains('Un carácter especial (*-_./!¿?)')").toggleClass("tachado", hasSpecialChar);
    jQuery("li:contains('Una mayúscula')").toggleClass("tachado", hasUpperCase);

    // Devolver el resultado de la validación
    return hasNumber && hasSpecialChar && hasUpperCase && lengthValid;
}

window.initUsuarios = function initUsuarios() {
    jQuery('#table-usuarios').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        order: [[0, 'desc']]
    });
    jQuery('#select_rolesusuario, #sel_empresa_asociada').select2({
        placeholder: "Selecciona un rol",
        allowClear: true,
        width: '100%'
    });
}

window.initRecorridos = function initRecorridos() {
    jQuery('#table-recorridos').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        order: [[0, 'desc']]
    });

    jQuery('#ciudad_inicio, #barrio_inicio, #ciudad_fin, #barrio_fin').select2({
        placeholder: "Selecciona un Valor",
        width: '100%'
    });
    if (jQuery('#id_solicitante_recorrido').length > 0) {
        jQuery('#id_solicitante_recorrido, #id_conductor_recorrido').select2({
            placeholder: "Selecciona un Valor",
            width: '100%'
        });
    }
}

window.initVehiculos = function initVehiculos() {
    jQuery('#table-vehiculos').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        order: [[0, 'desc']]
    });

    jQuery('#tipo_de_vehiculo, #propietario_de_vehiculo, #conductor_del_vehiculo').select2({
        placeholder: "Selecciona un Valor",
        allowClear: true,
        width: '100%'
    });
}

window.initEmpresas = function initEmpresas() {
    jQuery('#table-empresas').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        order: [[0, 'desc']]
    });

    jQuery('#administradores_empresa').select2({
        placeholder: "Selecciona un Valor",
        allowClear: true,
        width: '100%'
    });
}