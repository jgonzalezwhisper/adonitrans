<?php 
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');  
    if (!isset($_POST['action']) || empty($_POST['action'])) {
        exit('Acceso no autorizado');
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
            <a href="#" class="button" id="crear-recorrido"><i class="icofont-plus-circle"></i> Solicitar Recorrido</a>
            <table id="table-recorridos" class="display table-adoni">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Recorrido</th>
                        <th>Empresa</th>
                        <th>Colaborador</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Obtener el usuario actual
                        $current_user = wp_get_current_user();
                        $user_id = $current_user->ID;
                        $user_role = $current_user->roles[0];

                        // Base de la consulta
                        $args = [
                            'post_type'      => 'recorrido',
                            'posts_per_page' => -1,
                        ];
                        if ( $user_role === 'colaborador' ) {
                            $args['meta_query'] = [
                                [
                                    'key'     => 'id_solicitante_recorrido',
                                    'value'   => $user_id,
                                    'compare' => '='
                                ]
                            ];
                        } elseif ( $user_role === 'conductor' ) {
                            $args['meta_query'] = [
                                [
                                    'key'     => 'id_conductor_recorrido',
                                    'value'   => $user_id,
                                    'compare' => '='
                                ]
                            ];
                        }
                        elseif ( $user_role === 'empresa' ) {
                            // Obtener el ID del post con el campo personalizado igual a 23
                            $empresa_id = get_posts(array(
                                'post_type'  => 'empresa',
                                'meta_query' => array(
                                    array(
                                        'key'     => 'usuarios_administradores_empresa',
                                        'value'   => '"' . $user_id . '"',
                                        'compare' => 'LIKE',
                                    ),
                                ),
                                'fields'     => 'ids',
                                'numberposts' => 1,
                            ))[0] ?? null;

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
                            ?>
                            <tr>
                                <td><?= get_the_ID(); ?></td>
                                <td><?= get_the_title( ) ?></td>
                                <td><?= get_the_title( $empresa_asociada ); ?></td>
                                <td class="colaborador"><?= $first_name ?> - <span class="email"><?= $email ?></span></td>
                                <td class="<?= str_replace(' ', '-', strtolower($estado_recorrido)); ?>"><?= $estado_recorrido; ?></td>
                                <td>
                                    <div class="acciones">
                                        <?php if ($user_role !== 'colaborador'): ?>
                                            <button class="accion edit-recorrido" data-id="<?= get_the_ID(); ?>">Editar</button>
                                        <?php endif ?>
                                        <button class="accion delete-recorrido" data-id="<?= get_the_ID(); ?>">Eliminar</button>
                                        <?php if ($estado_recorrido !== 'Por Asignar' && $estado_recorrido !== 'Cancelado'): ?>
                                            <button class="accion ver-recorrido" data-id="<?= get_the_ID(); ?>">Ver</button>
                                        <?php endif; ?>
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
                        'posts_per_page' => -1,       // Obtener todos los posts (puedes limitarlo si es necesario)
                        'meta_key'       => 'empresa_asociada_a_ciudad', // Campo ACF
                        'meta_value'     => $empresa_asociada->ID,           // Valor a comparar
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
                            $ciudad_para_empresa = get_field('ciudad_para_empresa',get_the_ID());

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
                            <label for="id_conductor_recorrido">Conductor Asignado</label>
                            <select id="id_conductor_recorrido" name="id_conductor_recorrido">
                                <option value="0">Selecciona un Conductor</option>
                            </select>
                        </div>
                    <?php endif ?>
                <?php endif ?>
                <?php if ($user_role === 'colaborador'): ?>
                    <input type="hidden" id="id_solicitante_recorrido_col" name="id_solicitante_recorrido" value="<?= $user_id ?>">
                <?php endif ?>

                <div class="wrap"></div>

                <div class="wrap wrap-2">
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

                <div class="wrap wrap-2">
                    <label for="barrio_inicio">Barrio Inicio</label>
                    <select id="barrio_inicio" name="barrio_inicio" disabled required>
                        <option value="">Selecciona un Barrio</option>
                    </select>
                </div>

                <div class="wrap wrap-2">
                    <label for="ciudad_fin">Ciudad Fin</label>
                    <select id="ciudad_fin" name="ciudad_fin" disabled required>
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
                <div class="wrap wrap-2">
                    <label for="barrio_fin">Barrio Fin</label>
                    <select id="barrio_fin" name="barrio_fin" disabled required>
                        <option value="">Selecciona un Barrio</option>
                    </select>
                </div>

                <div id="wrap-puntos-recorrido" class="wrap wrap-fanjas">
                    <h5>Añadir Punto Recorrido</h5>

                    <!-- Plantilla oculta -->
                    <div id="plantilla-recorrido" style="display: none;">
                        <div class="franja">
                            <div class="franja_item">
                                <label for="ciudad_adicional_recorrido">Ciudad</label>
                                <select class="ciudad" name="ciudad_adicional_recorrido[]">
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
                                <select class="barrio" name="barrio_adicional_recorrido[]">
                                    <option value="">Selecciona un barrio</option>
                                </select>
                            </div>
                            <button type="button" class="button remove">Eliminar Punto</button>
                        </div>
                    </div>

                    <div id="wrap-punto-recorrido" class="wrap-franja">

                        <div class="franja">
                            <div class="franja_item">
                                <label for="ciudad_adicional_recorrido">Ciudad</label>
                                <select class="ciudad_adicional_recorrido" name="ciudad_adicional_recorrido[]" >
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
                                <select class="barrio_adicional_recorrido" name="barrio_adicional_recorrido[]">
                                    <option value="">Selecciona un barrio</option>
                                </select>
                            </div>
                        </div>                        
                    </div>

                    <a class="button button-add"><i class="icofont-plus-circle"></i>Añadir Punto</a>   
                </div>

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
                <?php if ($user_role === 'empresa' || $user_role === 'colaborador' || $user_role === 'administrator'): ?>
                    <?php if ($user_role === 'administrator'): ?>
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
                            <select id="centro_de_costo" name="centro_de_costo" required>
                                <option value="">Selecciona un Centro de Costo</option>
                                <?php foreach ($centros_costo_empresa as $key => $value): ?>
                                    <option value="<?= $value['codigo']; ?>"><?= $value['nombre']; ?></option>
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