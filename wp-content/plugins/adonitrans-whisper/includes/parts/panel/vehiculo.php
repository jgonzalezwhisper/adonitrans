<?php 
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');  
    if (!isset($_POST['action']) || empty($_POST['action'])) {
        exit('Acceso no autorizado');
    }
?>
<div id="wrap-vehiculos">
    <div class="tarjeta">
        <div class="wrap-titulo">
            <h3 class="titulo">VEHÍCULOS</h3>
            <h4 class="subtitulo">Gestiona los vehículos vinculados a la empresa</h4>
        </div>
        <p>Administra y gestiona los vehículos registrados en ADONITRANS desde este panel. Mantén toda la información organizada y actualizada.</p>

        <div class="wrap-listado-vehiculos">
            <a href="#" class="button" id="crear-vehiculo"><i class="icofont-plus-circle"></i> Crear Vehículo</a>
            <table id="table-vehiculos" class="display table-adoni">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Placa</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                        $argsveh = [
                            'post_type' => 'vehiculo',
                            'post_status' => 'publish',
                            'posts_per_page' => -1,
                        ];

                        $query = new WP_Query($argsveh);
                    ?>
                    <?php if ($query->have_posts()): ?>
                        <?php while($query->have_posts()): $query->the_post();?>
                            <?php
                                $placa_vehiculo = get_field('placa_vehiculo', get_the_ID());
                                $tipo_vehiculo = get_field('tipo_de_vehiculo', get_the_ID());
                                $estado_vehiculo = get_field('estado_del_vehiculo', get_the_ID());
                            ?>
                            <tr>
                                <td><?= get_the_ID(); ?></td>
                                <td><?= $placa_vehiculo ?></td>
                                <td><?= $tipo_vehiculo; ?></td>
                                <td class="<?= $estado_vehiculo; ?>"><?= $estado_vehiculo; ?></td>
                                <td>
                                    <div class="acciones">
                                        <button class="accion edit-vehiculo" data-id="<?= get_the_ID(); ?>"><i class="icofont-pencil"></i>Editar</button>
                                        <button class="accion delete-vehiculo delete-user" data-id="<?= get_the_ID(); ?>"><i class="icofont-info-circle"></i>Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile;wp_reset_postdata(); ?>                    
                    <?php endif ?>
                        
                </tbody>
            </table>
        </div>

        <div class="wrap-gestion-vehiculos" style="display:none">
            <div class="wrap wrap-title">
                <h3 class="title">Crear Vehiculo</h3>
            </div>
            <form id="vehiculo-form" method="post" class="formplug" autocomplete="off">
                <input type="hidden" id="vehiculo-id" name="vehiculo-id" value="">
                <?php wp_nonce_field('create_vehiculo_action', 'create_vehiculo_nonce'); ?>

                <div class="wrap wrap-2">
                    <label for="estado_del_vehiculo">Estado</label>
                    <select id="estado_del_vehiculo" name="estado_del_vehiculo">
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="wrap wrap-2">
                    <label for="placa_vehiculo">Placa</label>
                    <input type="text" id="placa_vehiculo" name="placa_vehiculo" value="">
                </div>

                <div class="wrap wrap-2">
                    <label for="tipo_de_vehiculo">Tipo de vehículo</label>
                    <select id="tipo_de_vehiculo" name="tipo_de_vehiculo">
                        <option value=""></option>
                        <option value="Automovil">Automovil</option>
                        <option value="Camioneta">Camioneta</option>
                        <option value="Doble Cabina">Doble Cabina</option>
                        <option value="Campero">Campero</option>
                        <option value="Van">Van</option>
                        <option value="Bus">Bus</option>
                        <option value="Buseta">Buseta</option>
                    </select>
                </div>

                <div class="wrap wrap-2">
                    <label for="modelo_vehiculo">Modelo</label>
                    <input type="text" id="modelo_vehiculo" name="modelo_vehiculo" value="">
                </div>
                <div class="wrap wrap-2">
                    <label for="cantidad_pasajeros_vehiculo">Cantidad de Pasajeros</label>
                    <input type="text" id="cantidad_pasajeros_vehiculo" name="cantidad_pasajeros_vehiculo" value="">
                </div>
                <div class="wrap wrap-2">
                    <label for="marca_vehiculo">Marca del Vehículo</label>
                    <input type="text" id="marca_vehiculo" name="marca_vehiculo" value="">
                </div>
                <div class="wrap wrap-2">
                    <label for="serial_vehiculo">Serial</label>
                    <input type="text" id="serial_vehiculo" name="serial_vehiculo" value="">
                </div>
                <div class="wrap wrap-2">
                    <label for="chasis_vehiculo"># Chasis</label>
                    <input type="text" id="chasis_vehiculo" name="chasis_vehiculo" value="">
                </div>
                <div class="wrap wrap-2">
                    <label for="fecha_vencimiento_soat">Fecha vencimiento SOAT (DD/MM/YYYY)</label>
                    <input type="date" id="fecha_vencimiento_soat" name="fecha_vencimiento_soat" value="" placeholder="dd/mm/yyyy">
                </div>
                <div class="wrap wrap-2">
                    <label for="fecha_vencimiento_tecno_mecanica">Fecha Vencimiento Tecno Mecanica</label>
                    <input type="date" id="fecha_vencimiento_tecno_mecanica" name="fecha_vencimiento_tecno_mecanica" value="" placeholder="dd/mm/yyyy">
                </div>
                <div class="wrap wrap-2">
                    <label for="propietario_de_vehiculo">Propietario de Vehículo</label>
                    <select id="propietario_de_vehiculo" name="propietario_de_vehiculo">
                        <option value=""></option>
                        <?php
                            // Obtén todos los usuarios con roles específicos
                            $roles = ['conductor', 'propietario_vehiculo', 'administrador'];
                            $args = [
                                'role__in' => $roles,
                                'orderby'  => 'display_name',
                                'order'    => 'ASC',
                            ];
                            $usuarios = get_users($args);
                        ?>
                        <?php foreach ($usuarios as $usuario) : $first_name = get_user_meta($usuario->ID, 'first_name', true);?>
                            <option value="<?php echo esc_attr($usuario->ID); ?>">
                                <?= esc_html($first_name . ' - ' . $usuario->user_email); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="wrap wrap-2">
                    <label for="conductor_del_vehiculo">Conductor del Vehiculo</label>
                    <select id="conductor_del_vehiculo" name="conductor_del_vehiculo">
                        <option value=""></option>
                        <?php
                            // Obtén todos los usuarios con roles específicos
                            $roles = ['conductor'];
                            $args = [
                                'role__in' => $roles,
                                'orderby'  => 'display_name',
                                'order'    => 'ASC',
                            ];
                            $usuarios = get_users($args);
                        ?>
                        <?php foreach ($usuarios as $usuario) : $first_name = get_user_meta($usuario->ID, 'first_name', true);?>
                            <option value="<?php echo esc_attr($usuario->ID); ?>">
                                <?= esc_html($first_name . ' - ' . $usuario->user_email); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="wrap">
                    <button class="button" type="submit" name="submit-user">Crear Vehículo</button>
                    <button class="button" type="button" id="cancelar-vehiculo-btn">Cancelar</button>
                </div>
            </form>
        </div> 
    </div>
</div>