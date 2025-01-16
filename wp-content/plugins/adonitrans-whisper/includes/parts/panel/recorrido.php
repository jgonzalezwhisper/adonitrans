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
    </div>

    <div class="wrap-listado-recorridos">
        <a href="#" class="button" id="crear-recorrido">Solicitar Recorrido</a>
        <table id="table-recorridos" class="display">
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
                            <td><?= $first_name." - $email" ?></td>
                            <td class="<?= $estado_recorrido; ?>"><?= $estado_recorrido; ?></td>
                            <td>
                                <div class="acciones">
                                    <button class="accion edit-recorrido" data-id="<?= get_the_ID(); ?>">Editar</button>
                                    <button class="accion delete-recorrido" data-id="<?= get_the_ID(); ?>">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile;wp_reset_postdata(); ?>                    
                <?php endif ?>
                    
            </tbody>
        </table>
    </div>

    <div class="wrap-gestion-recorridos" style="display:none">
        <div class="wrap wrap-title">
            <h3 class="title">Crear Solicitud Recorrido</h3>
        </div>
        <?php
            $ciudades = get_posts([
                'post_type'      => 'ciudad',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'fields'         => ['ID', 'post_title'], 
            ]);

            if ($user_role === 'administrator') {
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
                $conductores = $user_query->get_results(); // Obtener resultados
            }
        ?>
        <form id="recorrido-form" method="post" class="formplug" autocomplete="off">
            <?php wp_nonce_field('create_recorrido_action', 'create_recorrido_nonce'); ?>
            <input type="hidden" id="recorrido-id" name="recorrido-id" value="">           

            <?php if ($user_role === 'administrator' || $user_role === 'empresa'): ?>
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
                <div class="wrap wrap-2">
                    <label for="id_conductor_recorrido">Conductor Asignado</label>
                    <select id="id_conductor_recorrido" name="id_conductor_recorrido">
                        <option value="0">Selecciona un Conductor</option>
                        <?php foreach ($conductores as $conductor): ?>
                            <?php
                            $user_id = $conductor->ID;
                            $first_name = get_user_meta($user_id, 'first_name', true);
                            $last_name = get_user_meta($user_id, 'last_name', true);
                            $email = $conductor->user_email;

                            $name = trim("$first_name $last_name");
                            $display_name = $name ? $name : $conductor->display_name;
                            ?>
                            <option value="<?php echo esc_attr($user_id); ?>">
                                <?php echo esc_html("$display_name ($email)"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif ?>
            <?php if ($user_role === 'colaborador'): ?>
                <input type="hidden" id="id_solicitante_recorrido" name="id_solicitante_recorrido" value="<?= $user_id ?>">
            <?php endif ?>

            <div class="wrap wrap-2">
                <label for="ciudad_inicio">Ciudad Inicio</label>
                <select id="ciudad_inicio" name="ciudad_inicio" required>
                    <option value="">Selecciona una ciudad</option>
                    <?php if (!empty($ciudades)): ?>
                        <?php foreach ($ciudades as $ciudad): ?>
                            <option value="<?php echo esc_attr($ciudad->ID); ?>">
                                <?php echo esc_html($ciudad->post_title); ?>
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
                    <?php if (!empty($ciudades)): ?>
                        <?php foreach ($ciudades as $ciudad): ?>
                            <option value="<?php echo esc_attr($ciudad->ID); ?>">
                                <?php echo esc_html($ciudad->post_title); ?>
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

                    $empresa_asociada = get_field('empresa_asociada_usuario', 'user_' . $user_id);
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
                <button class="button" type="submit" name="submit-user">Crear Solicitud</button>
                <button class="button" type="button" id="cancelar-recorrido-btn">Cancelar</button>
            </div>
        </form>
    </div> 
</div>