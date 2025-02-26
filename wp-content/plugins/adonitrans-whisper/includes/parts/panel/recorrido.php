<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
if (!isset($_POST['action']) || empty($_POST['action'])) {
exit('Acceso no autorizado');
}

// Obtener el usuario actual
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$user_role = $current_user->roles[0];

$roles_solrecorrido = ['administrator', 'empresa', 'operaciones_1', 'colaborador', 'supervisores', 'flotantes'];


function mostrar_razones($empresa_id) {
    if (get_field('razon_de_uso_para_el_recorrido', $empresa_id) && have_rows('razon_de_uso_para_el_recorrido', $empresa_id)) {
        while (have_rows('razon_de_uso_para_el_recorrido', $empresa_id)) {
            the_row();
            $razon = get_sub_field('razon');
            if (!empty($razon)) {
                echo '<option value="' . esc_attr($razon) . '">' . esc_html($razon) . '</option>';
            }
        }
    }
}

?>
<div id="wrap-recorridos">
    <div class="tarjeta">
        <div class="wrap-titulo">
            <h3 class="titulo">RECORRIDOS</h3>
            <h4 class="subtitulo">Gestiona los recorridos realizados en la plataforma</h4>
        </div>
        <p>Administra y gestiona los recorridos registrados en ADONITRANS desde este panel. Mantén toda la información organizada y actualizada.</p>
        <div class="wrap-listado-recorridos">
            <?php if ( $user_role === 'colaborador' ): ?>
                <a href="#" class="button" id="crear-recorrido"><i class="icofont-plus-circle"></i> Solicitar Recorrido</a>
            <?php endif ?>
            <table id="table-recorridos" class="display table-adoni">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Recorrido</th>
                        <th>Fecha y Hr. Inicio</th>
                        <?php if ($user_role !== 'colaborador'): ?>
                        <th>Empresa</th>
                        <th>Colaborador</th>
                        <?php endif ?>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Base de la consulta
                        $args = [
                            'post_type'      => 'recorrido',
                            'posts_per_page' => -1,
                        ];

                        if ($user_role === 'colaborador') {
                            $args['meta_query'] = [
                                [
                                    'key'     => 'id_solicitante_recorrido',
                                    'value'   => $user_id,
                                    'compare' => '='
                                ]
                            ];
                        } elseif ($user_role === 'conductor') {
                            $args['meta_query'] = [
                                [
                                    'key'     => 'id_conductor_recorrido',
                                    'value'   => $user_id,
                                    'compare' => '='
                                ]
                            ];
                        } elseif ($user_role === 'empresa') {
                            // Obtener el ID del post con el campo personalizado igual a 23
                            $empresa_id = get_posts([
                                'post_type'  => 'empresa',
                                'meta_query' => [
                                    [
                                        'key'     => 'usuarios_administradores_empresa',
                                        'value'   => '"' . $user_id . '"',
                                        'compare' => 'LIKE',
                                    ],
                                ],
                                'fields'     => 'ids',
                                'numberposts' => 1,
                            ])[0] ?? null;

                            $args['meta_query'] = [
                                [
                                    'key'     => 'empresa_solicitante_recorrido',
                                    'value'   => $empresa_id,
                                    'compare' => '='
                                ]
                            ];
                        }

                        $query = new WP_Query($args);
                    ?>
                    <?php if ($query->have_posts()): ?>
                        <?php while($query->have_posts()): $query->the_post();?>
                            <?php
                                $placa_recorrido = get_field('placa_recorrido', get_the_ID());
                                $ciudad_inicial_recorrido = get_field('ciudad_inicial_recorrido', get_the_ID());
                                $barrio_inicial_recorrido = get_field('barrio_inicial_recorrido', get_the_ID());
                                $colaborador_id = get_field('id_solicitante_recorrido', get_the_ID())['ID'];
                                $colaborador = get_userdata($colaborador_id);
                                $empresa_asociada = get_user_meta($colaborador_id, 'empresa_asociada_usuario', true);
                                $first_name = "";
                                $email = "";
                                if ($colaborador) {
                                    $first_name = $colaborador->first_name;
                                    $email = $colaborador->user_email;
                                }
                                $estado_recorrido = get_field('estado_del_recorrido', get_the_ID());
                                $fecha_inicio = get_field('fecha_inicio_recorrido', get_the_ID());
                                $hora_inicio = get_field('hora_inicio_recorrido', get_the_ID());
                            ?>
                        <tr>
                            <td><?= get_the_ID(); ?></td>
                            <td><?= get_the_title( ) ?></td>
                            <td><?= $fecha_inicio.' - '.$hora_inicio ?></td>
                            <?php if ($user_role !== 'colaborador'): ?>
                            <td class="empresa"><?= get_the_title( $empresa_asociada ); ?></td>
                            <td class="colaborador"><?= $first_name ?> - <span class="email"><?= $email ?></span></td>
                            <?php endif ?>
                            <td class="<?= str_replace(' ', '-', strtolower($estado_recorrido)); ?>"><?= $estado_recorrido; ?></td>
                            <td>
                                <div class="acciones">
                                    <?php if ( $user_role !== 'conductor' && $user_role !== 'colaborador' && $estado_recorrido !== 'Finalizado' ): ?>
                                        <button class="accion edit-recorrido" data-id="<?= get_the_ID(); ?>">Editar</button>
                                    <?php endif ?>
                                    
                                    <?php if ($user_role === 'conductor' && $estado_recorrido === 'Conductor Asignado'): ?>
                                        <button class="accion iniciar-recorrido" data-id="<?= get_the_ID(); ?>">Iniciar</button>
                                    <?php endif ?>

                                    <?php if ($user_role === 'conductor' && $estado_recorrido === 'En curso'): ?>
                                        <button class="accion panel-recorrido" data-id="<?= get_the_ID(); ?>">Continuar Servicio</button>
                                    <?php endif ?>

                                    <?php
                                        $roles_cancelar = ['administrator', 'empresa', 'operaciones_1', 'operaciones_2', 'colaborador'];
                                        $estad_cancelar = ['Por Asignar'];
                                    ?>
                                    <?php if (in_array($user_role,  $roles_cancelar) && in_array($estado_recorrido,  $estad_cancelar)): ?>
                                        <button class="accion cancelar-recorrido" data-id="<?= get_the_ID(); ?>">Cancelar</button>
                                    <?php endif; ?>
                                    
                                    <button class="accion ver-recorrido" data-id="<?= get_the_ID(); ?>">Ver Detalle</button>

                                    <?php $rols_ver_ingr = ['administrator', 'operaciones_1', 'operaciones_2', 'conductor']; ?>
                                    <?php if (in_array($user_role,  $rols_ver_ingr) && $estado_recorrido === 'Finalizado'): ?>
                                        <button class="accion ver-ingresos" data-id="<?= get_the_ID(); ?>">Ver ingresos</button>
                                    <?php endif ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile;wp_reset_postdata(); ?>
                    <?php else: ?>
                    <p>No hay recorridos creados.</p>
                    <?php endif ?>
                    
                </tbody>
            </table>
        </div>
        <div class="wrap-gestion-recorridos" style="display:none">
            <div id="wrap-titform-recorrido" class="wrap wrap-title">
                <h3 class="title">Crear Solicitud Recorrido</h3>
            </div>
            <?php
                if ($user_role === 'colaborador') {
                    $empresa_asociada = get_field('empresa_asociada_usuario', 'user_' . $user_id);

                    // Argumentos para WP_Query
                    $argsciudadxcol = array(
                        'post_type'      => 'ciudad', // Tipo de post
                        'posts_per_page' => -1,      // Obtener todos los posts (puedes limitarlo si es necesario)
                        'meta_key'       => 'empresa_asociada_a_ciudad', // Campo ACF
                        'meta_value'     => $empresa_asociada->ID,       // Valor a comparar
                    );

                    // Ejecutar la consulta
                    $queryxcolciu = new WP_Query($argsciudadxcol);

                    // Array para almacenar los resultados
                    $resultados = array();

                    // Verificar si hay posts
                    if ($queryxcolciu->have_posts()) {
                        while ($queryxcolciu->have_posts()) {
                            $queryxcolciu->the_post();

                            // Obtener el valor del campo ACF 'ciudad_para_empresa' usando get_post_meta
                            $ciudad_para_empresa = get_field('ciudad_para_empresa', get_the_ID());

                            // Almacenar en el array
                            $resultados[] = array(
                                'id' => get_the_ID(),
                                'ciudad_para_empresa' => $ciudad_para_empresa
                            );
                        }
                        wp_reset_postdata();
                    }
                }

                if ($user_role === 'administrator' || $user_role === 'operaciones_1') {
                    $argscol = array(
                        'role'    => 'colaborador',
                        'orderby' => 'display_name',
                        'order'   => 'ASC',
                    );
                    $user_query = new WP_User_Query($argscol);
                    $colaboradores = $user_query->get_results(); // Obtener resultados

                    $argscon = array(
                        'role'    => 'conductor',
                        'orderby' => 'display_name',
                        'order'   => 'ASC',
                    );
                    $user_query = new WP_User_Query($argscon);
                    $conductores = $user_query->get_results();
                }

                if ($user_role === 'empresa') {
                    $argscolemp = array(
                        'role'    => 'colaborador',
                        'orderby' => 'display_name',
                        'order'   => 'ASC',
                        'meta_query' => array(
                            array(
                                'key'     => 'empresa_asociada_usuario',
                                'value'   => $empresa_id,
                                'compare' => '=',
                            ),
                        ),
                    );
                    $user_query = new WP_User_Query($argscolemp);
                    $colaboradores = $user_query->get_results();
                }
            ?>
            <form id="recorrido-form" method="post" class="formplug" autocomplete="off">
                <?php wp_nonce_field('create_recorrido_action', 'create_recorrido_nonce'); ?>
                <input type="hidden" id="recorrido-id" name="recorrido-id" value="">
                <input type="hidden" id="barrio_zona_inicio" name="barrio_zona_inicio" value="">
                <input type="hidden" id="barrio_zona_fin" name="barrio_zona_fin" value="">
                <?php if ($user_role === 'administrator' || $user_role === 'empresa' || $user_role === 'operaciones_1'): ?>
                <div class="wrap wrap-2">
                    <label for="id_solicitante_recorrido">Colaborador Solicitante</label>
                    <select id="id_solicitante_recorrido" name="id_solicitante_recorrido" class="<?php echo $user_role === 'administrator' ? 'admin-select-solicitante' : ''; ?>" required>
                        <option value="">Selecciona un Colaborador</option>
                        <?php foreach ($colaboradores as $colaborador): ?>
                        <?php
                            // Obtener los datos del usuario
                            $user_id = $colaborador->ID;
                            $first_name = get_user_meta($user_id, 'first_name', true);
                            $last_name = get_user_meta($user_id, 'last_name', true);
                            $email = $colaborador->user_email;
                            $name = trim("$first_name $last_name");
                            $display_name = $name ? $name : $colaborador->display_name;
                        ?>
                        <option value="<?php echo esc_attr($user_id); ?>">
                            <?php echo esc_html("$display_name ($email)"); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($user_role === 'administrator' || $user_role === 'operaciones_1' ): ?>
                <div class="wrap wrap-2">
                    <label for="id_conductor_recorrido">Vehículo Asigando</label>
                    <select id="id_conductor_recorrido" name="id_conductor_recorrido">
                        <option value="0">Selecciona un Móvil</option>
                    </select>
                </div>
                <?php endif ?>
                <?php endif ?>
                <?php if ($user_role === 'colaborador'): ?>
                <input type="hidden" id="id_solicitante_recorrido_col" name="id_solicitante_recorrido" value="<?= $user_id ?>">
                <?php endif ?>
                <div class="wrap"></div>

                <div class="wrap wrap-3">

                    <div class="wrap">
                        <label for="ciudad_inicio">Ciudad Inicio</label>
                        <select id="ciudad_inicio" name="ciudad_inicio" required>
                            <option value="">Selecciona una ciudad</option>
                            <?php if (!empty($resultados)): ?>
                                <?php foreach ($resultados as $ciudad): ?>
                                    <option value="<?php echo esc_attr($ciudad['id']); ?>">
                                        <?php echo esc_html($ciudad['ciudad_para_empresa']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="wrap">
                        <label for="barrio_inicio">Barrio Inicio</label>
                        <select id="barrio_inicio" name="barrio_inicio" disabled required>
                            <option value="">Selecciona un Barrio</option>
                        </select>
                    </div>
                    <div class="wrap">
                        <label for="dir_inicial_recorrido">Dirección Inicio</label>
                        <input type="text" id="dir_inicial_recorrido" name="dir_inicial_recorrido" disabled required>
                    </div>                    
                </div>

                <div class="wrap wrap-3">

                    <div class="wrap">
                        <label for="ciudad_fin">Ciudad Fin</label>
                        <select id="ciudad_fin" name="ciudad_fin">
                            <option value="">Selecciona una ciudad</option>
                            <?php if (!empty($resultados)): ?>
                                <?php foreach ($resultados as $ciudad): ?>
                                    <option value="<?php echo esc_attr($ciudad['id']); ?>">
                                        <?php echo esc_html($ciudad['ciudad_para_empresa']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="wrap">
                        <label for="barrio_fin">Barrio Fin</label>
                        <select id="barrio_fin" name="barrio_fin">
                            <option value="">Selecciona un Barrio</option>
                        </select>
                    </div>
                    <div class="wrap">
                        <label for="dir_final_recorrido">Dirección Final</label>
                        <input type="text" id="dir_final_recorrido" name="dir_final_recorrido">
                    </div>                    
                </div>

                <div class="wrap">
                    <label for="comentario_colaborador_inicio_recorrido">Comentarios sobre el servicio</label>
                    <textarea id="comentario_colaborador_inicio_recorrido" name="comentario_colaborador_inicio_recorrido"></textarea>
                </div>

                <div class="wrap wrap-2">
                    <label for="razon_uso_recorrido">Razón de Uso</label>
                    <select name="razon_uso_recorrido" id="razon_uso_recorrido" required>
                        <option value="">Seleccione un valor</option>
                        <?php
                            $vali_empresa_razon = ['empresa', 'colaborador'];
                            $vali_adonitr_razon = ['administrator', 'operaciones_1', 'operaciones_2']; 

                            if (in_array($user_role, $vali_empresa_razon)) {
                                $id_empresa_razon = ($user_role === 'empresa') ? $empresa_id : $empresa_asociada;
                                mostrar_razones($id_empresa_razon);
                            }

                        ?>                        
                    </select>
                </div>
                <div class="wrap wrap-2">
                    <label for="persona_autoriza_recorrido">Persona que autoriza</label>
                    <select name="persona_autoriza_recorrido" id="persona_autoriza_recorrido" required>
                        <option value="">Seleccione un valor</option>
                        <?php
                            if ($user_role === 'empresa' || $user_role === 'colaborador'):

                                $id_empresa_razon = 0;

                                if ($user_role === 'empresa') {
                                    $id_empresa_razon = $empresa_id;
                                }
                                if ($user_role === 'colaborador') {
                                    $id_empresa_razon = $empresa_asociada;
                                }

                                $empr_adms = get_field('usuarios_administradores_empresa', $id_empresa_razon);

                                if (!empty($empr_adms)) {
                                    foreach ($empr_adms as $key => $value) {
                                        echo '<option value="' . esc_attr($value['ID']) . '">' . esc_html($value['user_firstname']) . ' ' . esc_html($value['user_lastname']) . '</option>';
                                    }
                                }

                            endif;
                        ?>                        
                    </select>
                </div>
                
                <?php if ($user_role === 'administrator' || $user_role === 'operaciones_1'): ?>
                    <?php
                        $args = [
                            'post_type'      => 'tarifa',
                            'post_status'    => 'publish',
                            'fields'         => 'ids',
                            'posts_per_page' => 1,
                            'meta_query'     => [
                                [
                                    'key'     => 'empresa_aplicar_tarifa',
                                    'value'   => '227',
                                    'compare' => '=LIKE'
                                ]
                            ]
                        ];

                        $tarifa_ids = get_posts($args);
                    ?>
                    <?php if (!empty($tarifa_ids)): ?>                    
                        <div class="wrap wrap-2">
                            <label for="tarifaxempresa">Seleccionar Ruta</label>

                            <select id="tarifaxempresa" name="tarifaxempresa" required>
                                <option value="">Selecciona una Ruta</option>
                            </select>
                        </div>
                    <?php endif ?>
                <?php endif ?>
                <div id="wrap-puntos-recorrido" class="wrap wrap-fanjas">
                    <h5>Añadir Parada</h5>
                    <!-- Plantilla oculta -->
                    <div id="plantilla-recorrido" style="display: none;">
                        <div class="franja show">
                            <div class="franja_item">
                                <label for="ciudad_adicional_recorrido">Ciudad</label>
                                <select class="ciudad" name="ciudad_adicional_recorrido[]" disabled>
                                    <option value="">Selecciona una ciudad</option>
                                    <?php if (!empty($resultados)): ?>
                                        <?php foreach ($resultados as $ciudad): ?>
                                        <option value="<?php echo esc_attr($ciudad['id']); ?>">
                                            <?php echo esc_html($ciudad['ciudad_para_empresa']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="franja_item">
                                <label for="barrio_adicional_recorrido">Barrio</label>
                                <input type="hidden" class="barrio_adicional_zona" name="barrio_adicional_zona[]" value="">
                                <select class="barrio" name="barrio_adicional_recorrido[]" disabled>
                                    <option value="">Selecciona un barrio</option>
                                </select>
                            </div>
                            <div class="franja_item">
                                <label for="direccion_adicional_zona">Direccion</label>
                                <input type="text" class="direccion_adicional_zona" name="direccion_adicional_zona[]" value="" disabled>
                            </div>
                            <button type="button" class="button remove">Eliminar Punto</button>
                        </div>
                    </div>
                    <div id="wrap-punto-recorrido" class="wrap-franja">
                    </div>
                    <a class="button button-add"><i class="icofont-plus-circle"></i>Añadir Parada</a>
                </div>

                <?php
                    $roles_pasadicionales = array('administrator', 'colaborador', 'operaciones_1', 'operaciones_2', 'empresa');
                ?>
                <?php if (in_array($user_role, $roles_pasadicionales)): ?>

                    <div id="wrap-usuarios-adicionales" class="wrap wrap-fanjas">
                        <h5>Añadir Pasajero Adicional</h5>

                        <div id="clonar-pas-adicional" style="display:none">

                            <?php
                                if ($user_role == 'colaborador') {
                                    $usuarios = obtener_usuarios_colaborador($empresa_asociada->ID, $user_id);
                                }    
                            ?>

                            <div class="franja show">
                                <div class="wrap">
                                    <div class="franja_item">
                                        <label for="">Usuario</label>
                                        <select class="select sel_adicional_usuario" name="sel_id_usuario_adicional[]" disabled >
                                            <option value="">Seleccione un Usuario</option>
                                            <?php if (!empty($usuarios)): ?>
                                                <?php foreach ($usuarios as $key => $usuario): ?>
                                                    <option value="<?php echo esc_attr($usuario['id']); ?>">
                                                        <?php echo esc_html($usuario['name']); ?>
                                                    </option>
                                                <?php endforeach ?>
                                            <?php endif ?>
                                        </select>
                                    </div>
                                    <div class="franja_item">
                                        <label for="ciudad_origen_pasajero_adicional">Ciudad Origen</label>
                                        <select class="ciudad_origen_pasajero_adicional" name="ciudad_origen_pasajero_adicional[]" disabled>
                                            <option value="">Selecciona una ciudad Origen</option>
                                            <?php if (!empty($resultados)): ?>
                                                <?php foreach ($resultados as $ciudad): ?>
                                                <option value="<?php echo esc_attr($ciudad['id']); ?>">
                                                    <?php echo esc_html($ciudad['ciudad_para_empresa']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="franja_item">
                                        <label for="">Barrio Origen</label>
                                        <select class="barrio_origen_pasajero_adi" name="barrio_origen_pasajero_adi[]" disabled >
                                            <option value="">Seleccione un Barrio</option>
                                        </select>
                                    </div>
                                    <div class="franja_item">
                                        <label for="">Dirección Origen</label>
                                        <input type="text" class="direccion_origen_adicional" name="direccion_origen_adicional[]" value="" disabled>
                                    </div>
                                </div>

                                <div class="wrap">
                                    <div class="franja_item">
                                        <label for="ciudad_destino_pasajero_adicional">Ciudad Destino</label>
                                        <select class="ciudad_destino_pasajero_adicional" name="ciudad_destino_pasajero_adicional[]" disabled>
                                            <option value="">Selecciona una ciudad Destino</option>
                                            <?php if (!empty($resultados)): ?>
                                                <?php foreach ($resultados as $ciudad): ?>
                                                <option value="<?php echo esc_attr($ciudad['id']); ?>">
                                                    <?php echo esc_html($ciudad['ciudad_para_empresa']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="franja_item">
                                        <label for="">Barrio Destino</label>
                                        <select class="barrio_destino_pasajero_adi" name="barrio_destino_pasajero_adi[]" disabled >
                                            <option value="">Seleccione un Barrio</option>
                                        </select>
                                    </div>
                                    <div class="franja_item">
                                        <label for="">Dirección Destino</label>
                                        <input type="text" class="direccion_destino_adicional" name="direccion_destino_adicional[]" value="" disabled>
                                    </div>
                                    <button type="button" class="button remove">Eliminar Pasajero</button>
                                </div>
                            </div>         
                        </div>

                        <div id="wrap-usuario-adicional" class="wrap-franja">                            
                        </div>

                        <a class="button button-add"><i class="icofont-plus-circle"></i>Añadir</a>
                    </div>
                    
                <?php endif ?>

                <div class="wrap wrap-2">
                    <label for="fecha_inicio_recorrido">Fecha Inicio (DD/MM/YYYY)</label>
                    <input type="date" id="fecha_inicio_recorrido" name="fecha_inicio_recorrido" value="" placeholder="dd/mm/yyyy">
                </div>
                <div class="wrap wrap-2 time">
                    <label for="hora_inicio_recorrido">
                        Hora Inicio
                        <input type="time" id="hora_inicio_recorrido" name="hora_inicio_recorrido" value=""  />
                    </label>
                </div>
                <?php if ($user_role === 'empresa' || $user_role === 'colaborador' || $user_role === 'administrator' || $user_role === 'operaciones_1' || $user_role === 'operaciones_2'): ?>
                    <?php if ($user_role === 'administrator' || $user_role === 'operaciones_1' || $user_role === 'operaciones_2'): ?>
                        <div class="wrap">
                            <label for="centro_de_costo">Centro de Costo</label>
                            <select id="centro_de_costo" name="centro_de_costo" disabled>
                                <option value="0">Selecciona un Centro de Costo</option>
                            </select>
                        </div>
                    <?php endif ?>
                    <?php if ( $user_role === 'colaborador' || $user_role === 'empresa' ):
                        $centros_costo_empresa = get_field('centros_de_costos_empresa', $empresa_asociada->ID); ?>
                        <div class="wrap">
                            <label for="centro_de_costo">Centro de Costo</label>
                            <select id="centro_de_costo" name="centro_de_costo">
                                <option value="">Selecciona un Centro de Costo</option>
                                <?php foreach ($centros_costo_empresa as $key => $value): ?>
                                <option value="<?= $value['codigo']; ?>"><?= '( '.$value['codigo'].' ) '.$value['nombre']; ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    <?php endif; ?>
                <?php endif ?>
                <div class="wrap">
                    <button class="button button-add" type="submit" name="submit-user">Crear Solicitud</button>
                    <button class="button button-remove cancelar" type="button" id="cancelar-recorrido-btn">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-recorrido" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="mod-datosrec" class="wrap wrap-2">
                <h3>DATOS DEL RECORRIDO</h3>
                <div class="datos">
                    <p><strong>ID: </strong><span id="mod-idrec"></span></p>
                    <p><strong>Fecha y Hora: </strong><span id="mod-daterec"></span></p>
                    <p class="inicio"><strong>Inicio: </strong><span class="color"></span><span id="mod-desinirec"></span></p>
                    <p class="fin"><strong>Final: </strong><span class="color"></span><span id="mod-desfinrec"></span></p>
                </div>
                <div class="list-icons recorridos-adicionales" style="display:none">
                    <h5>Paradas Adicionales</h5>
                    <ul></ul>
                </div>
                <div class="list-icons usuarios-adicionales" style="display:none">
                    <h5>Usuarios Adicionales</h5>
                    <ul></ul>
                </div>                
            </div>
            <div id="mod-datusurec" class="wrap wrap-2">
                <h3>DATOS DEL USUARIO</h3>
                <div class="datos">
                    <p><strong>Nombre: </strong><span id="mod-nombrec"></span></p>
                    <p><strong>Empresa: </strong><span id="mod-emprec"></span></p>
                </div>
            </div>
            <p id="modal-text"></p>
        </div>
    </div>

    <div id="modal-ingresos" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="mod-datosrec" class="wrap wrap-2">
                <h3>DATOS DEL RECORRIDO</h3>
                <div class="datos">
                    <p><strong>ID: </strong><span class="mod-idrec"></span></p>
                    <p><strong>Fecha y Hora: </strong><span class="mod-daterec"></span></p>
                </div>
                <div class="list-icons ingresos-recorrido" style="display:none">
                    <h5>Paradas Adicionales</h5>
                    <ul></ul>
                </div>   
            </div>
            <div id="mod-datusurec" class="wrap wrap-2">
                <h3>DATOS DEL USUARIO</h3>
                <div class="datos">
                    <p><strong>Nombre: </strong><span class="mod-nombrec"></span></p>
                    <p><strong>Empresa: </strong><span class="mod-emprec"></span></p>
                </div>
            </div>
            <p id="modal-text"></p>
        </div>
    </div>

    <div id="modal-cancelar" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="info-cancelarservicio" class="wrap">
                <h3>Cancelar Servicio de Recorrido</h3>
                <p>Describe brevemente el motivo de la cancelación. Esto nos ayudará a mejorar y brindarte una mejor experiencia en el futuro.</p>

                <form id="cancelarServicioForm" class="formplug">
                    <?php wp_nonce_field('cancelar_recorrido_action', 'cancelar_recorrido_nonce'); ?>
                    <input type="hidden" id="idServicio" name="idServicio" value="">
                    <div class="wrap">
                        <label for="motivoCancelacion">Motivo de la cancelación:</label>
                        <textarea id="motivoCancelacion" name="motivoCancelacion" rows="4" cols="50" placeholder="Por favor, describe el motivo de la cancelación..." required></textarea>
                    </div>
                    <div>
                        <button class="button button-add" type="submit">Confirmar Cancelación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>