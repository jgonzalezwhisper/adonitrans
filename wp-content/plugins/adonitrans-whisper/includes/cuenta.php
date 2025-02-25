<?php
require_once('../../../../wp-load.php'); 

function formatear_fecha_pdf($fecha) {
    if (empty($fecha)) {
        return ''; // Retornar vacío si no hay fecha válida
    }

    try {
        $dateTime = new DateTime($fecha);
        
        // Verificar si la extensión Intl está habilitada
        if (!class_exists('IntlDateFormatter')) {
            return $dateTime->format('d/m/Y'); // Formato alternativo si Intl no está disponible
        }

        // Intentar crear el formateador con 'es' en lugar de 'es_ES'
        $locale = 'es';
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        $formatter->setPattern('EEEE, d \'de\' MMMM \'de\' yyyy');

        return ucfirst($formatter->format($dateTime));
    } catch (Exception $e) {
        return ''; // Manejo de error en caso de fecha inválida
    }
}


if (!isset($_POST['tipo_consulta']) || !is_user_logged_in()) {
    echo "No tienes ACCESO a esta información.";
    return;
}

$dcto_motivo    = $_POST['descripcion'];
$dcto_valor     = $_POST['valor'];
$placa_movil    = "N/A";

if (!empty($_POST['tipo_consulta']) && $_POST['tipo_consulta'] === 'pdf-conductor') {
    if ( !empty($_POST['sel_condpdf']) && is_user_logged_in() ) {
        $id_conductor = intval($_POST['sel_condpdf']);
        $desde = sanitize_text_field($_POST['desde_formpdf']);
        $hasta = sanitize_text_field($_POST['hasta_formpdf']);

        /*CONDUCTOR*/
        $user_data = get_userdata($id_conductor);
        $cond_nomb = "N/A";
        $cond_mail = "N/A";
        $cond_cedu = "N/A";

        if ($user_data) {
            // Obtener el correo, nombre y apellido
            $cond_nomb = $user_data->first_name." ".$user_data->last_name;
            $cond_mail = $user_data->user_email;
            $cond_cedu = get_field('cedula_usuario', 'user_' . $id_conductor);

            $informacion_pago = get_field('informacion_de_pago_usuario', 'user_' . $id_conductor);

            if (!empty($informacion_pago['datos_informacion_de_pago_usuario'])) {
                $primer_dato = $informacion_pago['datos_informacion_de_pago_usuario'][0]; // Obtiene el primer repetidor

                $nombre_banco = $primer_dato['nombre_banco'] ?? '';
                $no_cuenta = $primer_dato['no_cuenta'] ?? '';
                $tipo_de_cuenta = $primer_dato['tipo_de_cuenta'] ?? '';
            }
        } 

        $args = [
            'post_type'      => 'recorrido',
            'posts_per_page' => -1, // Obtener todos los posts que cumplan con los criterios
            'post_status'    => 'publish', // Solo posts publicados
            'fields'         => 'ids', // Solo obtener los IDs de los posts
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => 'id_conductor_recorrido',
                    'value'   => $id_conductor,
                    'compare' => '='
                ],
                [
                    'key'     => 'fecha_inicio_recorrido',
                    'value'   => [$desde, $hasta],
                    'compare' => 'BETWEEN',
                    'type'    => 'DATE'
                ],
                [
                    'key'     => 'estado_del_recorrido',
                    'value'   => 'Finalizado',
                    'compare' => '='
                ]
            ]
        ];

        // Realizar la consulta
        $query = new WP_Query($args);

        $recorridos = $query->posts;
        $recorridos = array_unique($recorridos);
    }
}else{

    $desde = sanitize_text_field($_POST['desde_formpdf_movil']);
    $hasta = sanitize_text_field($_POST['hasta_formpdf_movil']);

    $placa_movil = $_POST['sel_movilpdf'];

    $args = [
        'post_type'      => 'recorrido',
        'posts_per_page' => -1, // Obtener todos los posts que cumplan con los criterios
        'post_status'    => 'publish', // Solo posts publicados
        'fields'         => 'ids', // Solo obtener los IDs de los posts
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => 'placa_vehiculo_recorrido',
                'value'   => $placa_movil,
                'compare' => '='
            ],
            [
                'key'     => 'fecha_inicio_recorrido',
                'value'   => [$desde, $hasta],
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            ],
            [
                'key'     => 'estado_del_recorrido',
                'value'   => 'Finalizado',
                'compare' => '='
            ]
        ]
    ];

    $query = new WP_Query($args);
    $recorridos = $query->posts;
    $recorridos = array_unique($recorridos);
}

    $recorrido_corporativo = 0;
    $total_recorrido = 0;

    $base = 0;
    $peajes = 0;
    $tiempo_espera = 0;
    $pas_adicional = 0;
    $transporte = 0;

    $total_transporte =0;
    $liquidacion20 = 0;
    

    foreach ($recorridos as $recorrido):
        $costo_calculado = get_field('costo_calculado_del_recorrido', $recorrido);
        $nomb_empresa = get_field('empresa_solicitante_recorrido', $recorrido);
        $placa_vehiculo = get_field('placa_vehiculo_recorrido', $recorrido);
        $base_recorrido = get_field('valor_ruta_recorrido', $recorrido);
        $fecha_inicio   = get_field('fecha_inicio_recorrido', $recorrido);

        if (!empty($costo_calculado)) {
            // Recorrer el array $costo_calculado
            foreach ($costo_calculado as $item) {
                if (stripos($item['motivo'], 'Recorrido Inicial') !== false) {
                    $base += $item['valor'];
                }
                if (stripos($item['motivo'], 'Tiempo') !== false) {
                    $tiempo_espera += $item['valor'];
                }
                if (stripos($item['motivo'], 'Usuario') !== false) {
                    $pas_adicional += $item['valor'];
                }

                if ($item['motivo'] !== 'Total Recorrido' && $item['motivo'] !== 'Peaje') {
                    $recorrido_corporativo += $item['valor'];
                }
                if (stripos($item['motivo'], 'peaje') !== false) {
                    $peajes += $item['valor'];
                }
                if ($item['motivo'] === 'Total Recorrido') {
                    $total_recorrido += $item['valor'];
                }
            }
        }
        
    endforeach;

    $ultimo_recorrido = end($recorridos);
    $total_transporte = $base+$tiempo_espera+$pas_adicional;
    $descuento = $total_transporte * 0.20; 
    $liquidacion20 = $total_transporte - $descuento;

    if (!empty($_POST['tipo_consulta']) && $_POST['tipo_consulta'] === 'pdf-movil') {

        /*CONDUCTOR*/
        $id_conductor = get_field('id_conductor_recorrido', $ultimo_recorrido)['ID'];
        $user_data = get_userdata($id_conductor);
        $cond_nomb = "N/A";
        $cond_mail = "N/A";
        $cond_cedu = "N/A";

        if ($user_data) {
            // Obtener el correo, nombre y apellido
            $cond_nomb = $user_data->first_name." ".$user_data->last_name;
            $cond_mail = $user_data->user_email;
            $cond_cedu = get_field('cedula_usuario', 'user_' . $id_conductor);

            $informacion_pago = get_field('informacion_de_pago_usuario', 'user_' . $id_conductor);

            if (!empty($informacion_pago['datos_informacion_de_pago_usuario'])) {
                $primer_dato = $informacion_pago['datos_informacion_de_pago_usuario'][0]; // Obtiene el primer repetidor

                $nombre_banco = $primer_dato['nombre_banco'] ?? '';
                $no_cuenta = $primer_dato['no_cuenta'] ?? '';
                $tipo_de_cuenta = $primer_dato['tipo_de_cuenta'] ?? '';
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script> -->
        <title>Cuenta de Cobro - <?= (isset($_POST['tipo_consulta']) && $_POST['tipo_consulta'] === 'pdf-movil') ? esc_html($placa_movil) : esc_html($cond_nomb); ?>
</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                background-color: #fff;
                color: #333;
            }
            .container {
                width: 90%;
                margin: auto;
                background: white;
                padding: 20px;
                border: 2px solid #000;
            }
            .header-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            .header-table th, .header-table td {
                border: 1px solid black;
                padding: 8px;
                text-align: center;
                font-weight: bold;
            }
            .header-logo {
                width: 120px;
            }
            .header-logo img {
                width: 70%;
                height: auto;
            }
            .highlight {
                background: #0070c0;
                color: #fff;
                font-weight: bold;
            }
            .bg-yellow {
                background: yellow;
            }
            tbody {
                border: 2px solid #000;
            }
        </style>
    </head>
    <body>
        <div class="container" id="pdfContent">
            <table class="header-table">
                <tr>
                    <td>Código: F-G-13</td>
                    <td colspan="2">PROCESO GERENCIAL</td>
                    <td colspan="2" rowspan="5" class="header-logo">
                        <img src="https://jagonzalez.org/wp-content/uploads/2025/02/adt-1-blue.png" alt="Logo de ADONITRANS">
                    </td>
                </tr>
                <tr>
                    <td>Versión: 4</td>
                    <td rowspan="2" colspan="2">CUENTA DE COBRO</td>
                </tr>
                <tr>
                    <td>Fecha elaboración: 25/05/2021</td>
                </tr>
                <tr>
                    <td>Fecha aprobación: 25/05/2021</td>
                    <td colspan="2">Documento elaborado por: <strong>ÁREA CONTABLE</strong></td>
                </tr>
                <tr>
                    <td>Vigencia a partir de: 01/04/2022</td>
                    <td colspan="2">Aprobado por: <strong>COMITÉ DE CALIDAD</strong></td>
                </tr>
            </table>
            <table class="header-table box-2">
                <tr>
                    <td>ADONITRANS S.A.S.</td>
                    <td>Fecha elaboración: <?= date('d/m/Y') ?></td>
                </tr>
                <tr>
                    <td>NIT. 900.527.861-2</td>
                </tr>
            </table>
            <table class="header-table">
                <tr>
                    <td colspan="4" class="highlight">DEBE A:</td>
                </tr>
                <tr>
                    <td>Nombre:</td>
                    <td><?= $cond_nomb ?></td>
                    <td>NIT/CC:</td>
                    <td><?= $cond_cedu ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="highlight">POR CONCEPTO DE:</td>
                </tr>
                <tr>
                    <td colspan="4">Marca con X según corresponda</td>
                </tr>
                <tr>
                    <td>Servicio de Transporte:</td>
                    <td>(X)</td>
                    <td>Placa: </td>
                    <td><?= $placa_movil; ?></td>
                </tr>
                <tr>
                    <td>Servicio de Mensajería: </td>
                    <td>()</td>
                    <td>Empresa</td>
                    <td><?= $nomb_empresa->post_title ?> </td>
                </tr>
                <tr>
                    <td>Servicio de Mantenimiento:</td>
                    <td>()</td>
                    <td>Servicios: </td>
                    <td> --- </td>
                </tr>
            </table>
            <table class="header-table">    
                <tr>
                    <td>LIQUIDACION PERIODO:</td>
                    <td>Transporte</td>
                    <td><?= formatear_moneda_colombia($total_transporte) ?></td>
                    <td>Total Periodo</td>
                    <td><?= formatear_moneda_colombia($total_transporte+$peajes) ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Peajes</td>
                    <td><?= formatear_moneda_colombia($peajes) ?></td>
                    <td>Liquidación -20% </td>
                    <td><?= formatear_moneda_colombia($liquidacion20) ?></td>
                </tr>
            </table>

            <table class="header-table">    
                <tr>
                    <td>Desde:</td>
                    <td><?= formatear_fecha_pdf($desde) ?> </td>
                    <td>Hasta:</td>
                    <td><?= formatear_fecha_pdf($hasta) ?></td>
                </tr>
                <tr>
                    <td>Cantidad</td>
                    <td>Descripción</td>
                    <td>Base Liquidación</td>
                    <td>Valor Total</td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Recorrido Corporativo</td>
                    <td><?= formatear_moneda_colombia($liquidacion20) ?></td>
                    <td style="text-align: right;"><?= formatear_moneda_colombia($liquidacion20) ?></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Subtotal</td>
                    <td style="text-align: right;"><?= formatear_moneda_colombia($liquidacion20) ?></td>
                </tr>
            </table>

            <!-- TABLA DE DESCUENTOS -->
            <table class="header-table">
               <tr>
                  <td colspan="5">DESCUENTOS:</td>
               </tr>
               <tr>
                  <td class="bg-yellw">NOTA OTROS DESCUENTOS: </td>
                  <td>
                     <table>
                        <?php
                            $total_dctos = 0;
                            foreach ($dcto_motivo as $key => $motivo): ?>

                                <?php $total_dctos += $dcto_valor[$key]; ?>

                            <tr>
                               <td><?= formatear_moneda_colombia($dcto_valor[$key]); ?></td>
                               <td><?= $motivo ?></td>
                            </tr>
                            
                        <?php endforeach ?>
                        <tr>
                           <td><?= formatear_moneda_colombia($total_dctos); ?></td>
                           <td>Total Descuentos</td>
                        </tr>
                     </table>
                  </td>
                  <td></td>
                  <td>
                     <table>
                        <?php
                            
                        $rete35 = ($liquidacion20 * 3.5) / 100;
                        $total_pagar = $liquidacion20 - $rete35 - $total_dctos;
                        ?>
                        <tr>
                           <td>Retención 3.5%</td>
                           <td>- <?= formatear_moneda_colombia($rete35); ?></td>
                        </tr>
                        <tr>
                           <td>Otros</td>
                           <td>- <?= formatear_moneda_colombia($total_dctos); ?></td>
                        </tr>
                        <tr>
                           <td>Total a pagar</td>
                           <td><?= formatear_moneda_colombia($total_pagar); ?></td>
                        </tr>
                     </table>
                  </td>
               </tr>
            </table>

            <!-- Metodo de pago -->
            <table class="header-table">    
                <tr>
                    <td colspan="4" class="highlight">METODO DE PAGO:</td>
                </tr>
                <tr>
                    <td>Pago en Efectivo:</td>
                    <td>(  )</td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td rowspan="7">Transferencia Bancaria:</td>
                    <td>( X )</td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td>No. de Cuenta</td>
                    <td><?= $no_cuenta ?></td>
                </tr>
                <tr>
                    <td>Corriente</td>
                    <td>( <?= (strpos($tipo_de_cuenta, 'Corriente') !== false) ? 'x' : ''; ?> )</td>
                </tr>
                <tr>
                    <td>Ahorros</td>
                    <td>( <?= (strpos($tipo_de_cuenta, 'Ahorros') !== false) ? 'x' : ''; ?> )</td>
                </tr>
                <tr>
                    <td>Banco</td>
                    <td><?= $nombre_banco ?></td>
                </tr>
                <tr>
                    <td>Titular</td>
                    <td><?= $cond_nomb ?></td>
                </tr>
                <tr>
                    <td>Cedula</td>
                    <td><?= $cond_cedu ?></td>
                </tr>
            </table>
            <!-- FOOTER -->
            <table class="header-table">    
                <tr>
                    <td>Cordialmente,</td>
                </tr>
                <tr>
                    <td>Firma:</td>
                </tr>
                <tr>
                    <td>Nombre:</td>
                    <td><?= $cond_nomb ?></td>
                </tr>
                <tr>
                    <td>NIT/CC:</td>
                    <td><?= $cond_cedu ?></td>
                </tr>
            </table>
            <table class="header-table">    
                <tr>
                    <td>Para los efectos relacionados con las normas fiscales, declaro: (1) Que soy persona natural no responsable del Impuesto sobre las Ventas (IVA). (2) De
                    acuerdo a los articulos 18 y 122 de la ley 1943 de 2018, no estoy obligado a emitir factura.
                    En conformidad con la normatividad fiscal, no estoy bajo la obligacion de expedir factura ni documento equivalente.
                    Pago en Efectivo:
                    Otros $ 32.000
                    Total a pagar $ 1.446.924
                    METODO DE PAGO
                    Para los efectos relacionados con las normas fiscales, declaro: (1) Que soy persona natural no responsable del Impuesto sobre las Ventas (IVA). (2) De
                    acuerdo a los articulos 18 y 122 de la ley 1943 de 2018, no estoy obligado a emitir factura</td>
                </tr>
            </table>
        </div>
        
        <!-- <button id="downloadPDF">Descargar PDF</button> -->

       <!--  <script>
            document.getElementById("downloadPDF").addEventListener("click", function () {
                const element = document.getElementById("pdfContent"); // Asegúrate de que este ID corresponda al contenedor de la cuenta de cobro

                const options = {
                    margin: 10,
                    filename: 'cuenta_de_cobro.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 1 },
                    jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' }
                };

                html2pdf().set(options).from(element).save();
            });
        </script> -->
    </body>
</html>