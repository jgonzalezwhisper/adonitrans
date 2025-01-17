<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');  ?>

<div id="wrap-empresas">
    <div class="tarjeta">
        <div class="wrap-titulo">
            <h3 class="titulo">EMPRESAS</h3>
            <h4 class="subtitulo">Gestiona empresas solicitantes de los servicios.</h4>
        </div>
        <p>Administra y gestiona empresas registrados en ADONITRANS desde este panel. Mantén toda la información organizada y actualizada.</p>

        <div class="wrap-listado-empresas">
            <a href="#" class="button" id="crear-empresa"><i class="icofont-plus-circle"></i> Crear Empresa</a>
            <table id="table-empresas" class="display table-adoni">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th># Administradores</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                        $argsveh = [
                            'post_type' => 'empresa',
                            'post_status' => 'publish',
                            'posts_per_page' => -1,
                        ];

                        $query = new WP_Query($argsveh);
                    ?>
                    <?php if ($query->have_posts()): ?>
                        <?php while($query->have_posts()): $query->the_post();?>
                            <?php
                                $estado_de_la_empresa = get_field('estado_de_la_empresa', get_the_ID());
                                $numero_usuarios = 0;
                                $usuarios_administradores = get_field('usuarios_administradores_empresa');
                                if (!empty($usuarios_administradores) && is_array($usuarios_administradores)) {
    					            $numero_usuarios = count($usuarios_administradores);
    					        } 
                            ?>
                            <tr>
                                <td><?= get_the_ID(); ?></td>
                                <td><?= get_the_title() ?></td>
                                <td class="<?= $estado_de_la_empresa; ?>"><?= $estado_de_la_empresa; ?></td>
                                <td class="center"><?= $numero_usuarios; ?></td>
                                <td class="center">
                                    <div class="acciones">
                                        <button class="accion edit-empresa" data-id="<?= get_the_ID(); ?>"><i class="icofont-pencil"></i>Editar</button>
                                        <button class="accion delete-empresa delete-user" data-id="<?= get_the_ID(); ?>"><i class="icofont-info-circle"></i>Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile;wp_reset_postdata(); ?>                    
                    <?php endif ?>
                        
                </tbody>
            </table>
        </div>

        <div class="wrap-gestion-empresas" style="display:none">
            <div class="wrap wrap-title">
                <h3 class="title">Crear Empresa</h3>
            </div>
            <form id="empresa-form" method="post" class="formplug" autocomplete="off">
                <input type="hidden" id="empresa-id" name="empresa-id" value="">
                <?php wp_nonce_field('create_empresa_action', 'create_empresa_nonce'); ?>

                <div class="wrap wrap-2">
                    <label for="estado_de_la_empresa">Estado</label>
                    <select id="estado_de_la_empresa" name="estado_de_la_empresa">
                        <option value="Activa">Activa</option>
                        <option value="Inactiva">Inactiva</option>
                    </select>
                </div>
                <div class="wrap wrap-2">
                    <label for="nombre_empresa">Nombre</label>
                    <input type="text" id="nombre_empresa" name="nombre_empresa" value="">
                </div>

                <div class="wrap">
                    <label for="administradores_empresa">Administradores de la Empresa</label>
                    <select id="administradores_empresa" name="administradores_empresa[]" multiple="multiple">
                        <option value=""></option>
                        <?php
                            // Obtén todos los usuarios con roles específicos
                            $roles = ['empresa'];
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
    				<div class="acordeon">
    					<div class="acordeon-item">
    						<div class="acordeon-header">Centros de Costos <i class="icofont-plus"></i></div>
    						<div class="acordeon-body">
    							<div class="wrap-centro-costos">	
    								<div class="wrap-datos">								
    									<?php 
    										$centros_de_costos_empresa = get_field('centros_de_costos_empresa', 'option');
    									?>
    									<?php if ($centros_de_costos_empresa): ?>

    										<?php foreach ($centros_de_costos_empresa as $key => $value): ?>
    											<div class="centro-costo">
                                                    <label for="codigo-0">Código
                                                        <input type="text" id="codigo-0" name="codigo_centro[]" value="<?= $value ?>">
                                                    </label>
    												<label for="nombre-0">Nombre
    													<input type="text" id="nombre-0" name="nombre_centro[]" value="<?= $value ?>">
    												</label>
    												<button type="button" class="button remove-centro-row">Eliminar Información</button>
    											</div>											
    										<?php endforeach ?>
    										
    									<?php else: ?>
    										<div class="centro-costo">
                                                <label for="codigo-0">Código
                                                    <input type="text" id="codigo-0" name="codigo_centro[]">
                                                </label>
    											<label for="nombre-0">Nombre
    												<input type="text" id="nombre-0" name="nombre_centro[]">
    											</label>
    											<button type="button" class="button remove-centro-row"><i class="icofont-info-circle"></i>Eliminar Información</button>
    										</div>
    									<?php endif ?>
    								</div>
    								<button type="button" id="add-centro-row" class="button button-add"><i class="icofont-plus-circle"></i> Añadir Centro de Costo</button>
    							</div>
    						</div>
    					</div>
    					<div class="acordeon-item">
    						<div class="acordeon-header">Documentos de la Empresa <i class="icofont-plus"></i></div>
                            <div class="acordeon-body">
                                <!-- Campo ACF repetidor para documentos -->
                                <div id="documentos-repetidor">
                                    <h3>Documentos de la Empresa</h3>
                                    <!-- Contenedor dinámico para filas -->
                                    <div id="documentos-container"></div>
                                    <button type="button" class="button" id="add-documento">Añadir Documento</button>
                                </div>
                            </div>
    					</div>
    				</div>
                </div>

                
                <div class="wrap">
                    <button class="button" type="submit" id="submit-empresa" name="submit-empresa"><i class="icofont-check"></i> Crear Empresa</button>
                    <button class="button" type="button" id="cancelar-empresa-btn"><i class="icofont-exit"></i>Cancelar</button>
                </div>
            </form>
        </div> 
    </div>
</div>