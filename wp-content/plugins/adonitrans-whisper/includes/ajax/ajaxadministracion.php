<?php 

add_action('wp_ajax_update_generales', 'update_generales_handler');
add_action('wp_ajax_nopriv_update_generales', 'update_generales_handler');
function update_generales_handler() {

    if (isset($_POST['nombre_franja'], $_POST['inicio_franja'], $_POST['fin_franja'])) {
        $nombres = $_POST['nombre_franja'];
        $horas_inicio = $_POST['inicio_franja'];
        $horas_fin = $_POST['fin_franja'];

        $franjas_horas_trabajo = [];

        foreach ($nombres as $index => $nombre) {
            $franjas_horas_trabajo[] = [
                'nombre' => sanitize_text_field($nombre),
               	'hora_inicio' => date('g:i:s a', strtotime(sanitize_text_field($horas_inicio[$index]))),
            	'hora_fin' => date('g:i:s a', strtotime(sanitize_text_field($horas_fin[$index]))),
            ];
        }
    }else{
    	wp_send_json_error(['message' => 'No hay datos válidos para guardar.']);
    }

    if (isset($_POST['descripcion'], $_POST['valor'])) {
	    $descripciones = $_POST['descripcion'];
	    $valores = $_POST['valor'];
	    $tarifas_descuentos = [];

	    foreach ($descripciones as $index => $descripcion) {
	        if (isset($valores[$index])) {
	            $tarifas_descuentos[] = [
	                'grupo_tarifas_descuentos' => [
	                    'descripcion' => sanitize_text_field($descripcion),
	                    'valor' => sanitize_text_field($valores[$index]),
	                ],
	            ];
	        }
	    }
	}


    // Guardar datos en la base de datos (Opciones)
    update_field('franjas_horas_trabajo', $franjas_horas_trabajo, 'option');
    update_field('tarifas_descuentos', $tarifas_descuentos, 'option');
    /*update_option('franjas_horas_trabajo', $franjas_horas_trabajo);*/

    wp_send_json_success(['message' => 'Datos guardados exitosamente.']);
}