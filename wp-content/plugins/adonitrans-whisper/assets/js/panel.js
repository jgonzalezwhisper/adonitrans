function e(e){var a=/\d/.test(e),t=/[*\-_.\/!¿?]/.test(e),l=/[A-Z]/.test(e),e=8<=e.length&&e.length<=15;return jQuery("li:contains('Entre 8 y 15 carácteres')").toggleClass("tachado",e),jQuery("li:contains('Un número')").toggleClass("tachado",a),jQuery("li:contains('Un carácter especial (*-_./!¿?)')").toggleClass("tachado",t),jQuery("li:contains('Una mayúscula')").toggleClass("tachado",l),a&&t&&l&&e}function t(){jQuery("#table-usuarios").DataTable({language:{url:"https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"}}),jQuery("#select-rolesusuario").select2({placeholder:"Selecciona un rol",allowClear:!0,width:"100%"})}function l(){jQuery("#table-vehiculos").DataTable({language:{url:"https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"}}),jQuery("#tipo_de_vehiculo, #propietario_de_vehiculo, #conductor_del_vehiculo").select2({placeholder:"Selecciona un Valor",allowClear:!0,width:"100%"})}function o(){jQuery("#table-empresas").DataTable({language:{url:"https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"}}),jQuery("#administradores_empresa").select2({placeholder:"Selecciona un Valor",allowClear:!0,width:"100%"})}jQuery(document).ready(function(n){n.validator.addMethod("checkPassword",function(e,a){var t=/\d/.test(e),l=/[*#=\-_.\/!¿?]/.test(e),o=/[A-Z]/.test(e),e=8<=e.length&&e.length<=15;return n("li:contains('Entre 8 y 15 carácteres')").toggleClass("tachado",e),n("li:contains('Un número')").toggleClass("tachado",t),n("li:contains('Un carácter especial (*#=-_./!¿?)')").toggleClass("tachado",l),n("li:contains('Una mayúscula')").toggleClass("tachado",o),this.optional(a)||t&&l&&o&&e},"La contraseña debe cumplir con los requisitos."),n.validator.addMethod("time24",function(e,a){return this.optional(a)||/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(e)},"Por favor, ingrese una hora válida en formato 24 horas (HH:mm).");new PerfectScrollbar("#informacion",{wheelSpeed:2,wheelPropagation:!0,minScrollbarLength:20});n(document).on("click",".acordeon-header",function(e){e.preventDefault();let a=n(this).next(".acordeon-body"),t=n(this).find("i");a.slideToggle(function(){a.is(":visible")?t.removeClass("icofont-plus").addClass("icofont-minus"):t.removeClass("icofont-minus").addClass("icofont-plus")})}),n("#lateral ul li[data-action]").on("click",function(){n("#lateral ul li").removeClass("active"),n(this).addClass("active");var e,a=n(this).data("action");"logout"!==a&&(n("body").addClass("actloader"),e=panelAjax.plugin_url+"includes/parts/panel/"+a+".php",n.ajax({url:e,method:"GET",success:function(e){n("body").removeClass("actloader"),n("#informacion").html(e),"usuario"==a&&t(),"vehiculo"==a&&l(),"empresa"==a&&o()},error:function(){n("body").removeClass("actloader"),n("#informacion").html("<p>Error al cargar el contenido. Intenta nuevamente.</p>")}}))})});
//# sourceMappingURL=panel.js.map
function toggleMenu() {
    const menu = document.getElementById('menu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Opcional: Cierra el menú al hacer clic fuera de él
function toggleMenu() {
    const menu = document.getElementById('menu');

    // Alternar la clase 'active' para mostrar/ocultar el menú
    if (menu.classList.contains('active')) {
        menu.classList.remove('active');
        setTimeout(() => {
            menu.style.display = 'none'; // Asegura que se oculta después de la transición
        }, 300); // Coincide con la duración de la transición
    } else {
        menu.style.display = 'block'; // Asegura que se muestra antes de aplicar la transición
        setTimeout(() => {
            menu.classList.add('active');
        }, 10); // Pequeño retraso para permitir la transición
    }
}

document.addEventListener('click', (e) => {
    const menu = document.getElementById('menu');
    const targetElement = e.target.closest('.continfo_user');
    if (!targetElement && menu.classList.contains('active')) {
        menu.classList.remove('active');
        setTimeout(() => {
            menu.style.display = 'none';
        }, 300);
    }
});
