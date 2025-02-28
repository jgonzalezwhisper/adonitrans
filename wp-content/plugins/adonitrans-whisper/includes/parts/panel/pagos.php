<?php 
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');  
    if (!isset($_POST['action']) || empty($_POST['action'])) {
        exit('Acceso no autorizado');
    }

    $rol_actual = wp_get_current_user()->roles[0];
?>
<div id="wrap-pagos">
    <div class="tarjeta">
        <div class="wrap-titulo">
            <h3 class="titulo">PAGOS</h3>
            <h4 class="subtitulo">Gestiona los pagos realizados a los Conductores</h4>
        </div>
        <p>Gestiona pagos a conductores/propietarios. Permite cargar archivos como soporte de cuentas de cobro y comprobantes de pagos realizados, facilitando el control y seguimiento.</p>

        <div class="wrap-listado-pagos">
            <a href="#" class="button" id="crear-pago"><i class="icofont-plus-circle"></i> Registrar Pago</a>
            <table id="table-pagos" class="display table-adoni">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Persona</th>
                        <th>Fecha Registro</th>
                        <th>Fecha Pago</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $args = array(
                            'post_type'      => 'pago', 
                            'post_status'    => 'publish', 
                            'posts_per_page' => -1,
                            'fields'         => 'ids',
                        );

                        $qr_pagos = new WP_Query($args);

                        $post_ids = $qr_pagos->posts;  
                    ?>

                    <?php if (!empty($post_ids)): ?>
                        <?php foreach ($post_ids as $key => $pago): ?>
                            <?php
                                $nombre_completo = "N/A";
                                $usuario_pago = get_field('usuario_asociado_al_pago', $pago);
                                $nombre_completo = $usuario_pago['user_firstname'].' '.$usuario_pago['user_lastname'];

                                $post_date = get_post_field('post_date', $pago);
                                $fecha_registro = date('d/m/Y', strtotime($post_date));
                                $fecha_pago = get_field('fecha_del_pago', $pago) ? get_field('fecha_del_pago', $pago) : "N/A";
                                $estado = get_field('estado_del_pago', $pago);
                            ?>
                            <tr>
                                <td><?= $pago; ?></td>
                                <td><?= $nombre_completo; ?></td>
                                <td><?= $fecha_registro; ?></td>
                                <td><?= $fecha_pago; ?></td>
                                <td class="<?= str_replace(' ', '-', strtolower($estado)); ?>"><?= $estado; ?></td>
                                <td>
                                    <div class="acciones">
                                        <?php
                                            $rols_eliminar = ['administrator'];
                                        ?>
                                        <button class="accion editar" data-id="<?= $pago; ?>" data-action="editar">
                                            <i class="icofont-pencil"></i> Editar
                                        </button>
                                        <?php if (in_array($rol_actual,  $rols_eliminar)): ?>
                                            
                                        <?php endif ?>
                                        <button class="accion eliminar" data-id="<?= $pago; ?>" data-action="eliminar">
                                            <i class="icofont-info-circle"></i> Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>                            
                        <?php endforeach ?>                    
                    <?php endif ?>
                </tbody>
            </table>
        </div>

        <div class="wrap-gestion-pagos" style="display: none;">
            <div class="wrap wrap-title">
                <h3 class="title"><span>Registrar</span> Pago</h3>
            </div>
            <form id="form-pagos" method="post" enctype="multipart/form-data" class="formplug" autocomplete="off">
                <input type="hidden" id="post-id" name="post-id" value="">
                <input type="hidden" id="action" name="action" value="gestionar_pago">
                <?php wp_nonce_field('action_gestion_pago', 'nonce_gestion_pago'); ?>

                <div class="wrap">
                    <label for="usuario_asociado_al_pago">Usuario Asociado al pago</label>
                    <?php
                        $roles = array('propietario_vehiculo', 'conductor');
                        $usuarios_array = array();

                        foreach ($roles as $rol) {
                            $usuarios = get_users(array(
                                'role'    => $rol,
                                'fields'  => array('ID'),
                            ));

                            foreach ($usuarios as $usuario) {
                                $first_name = get_user_meta($usuario->ID, 'first_name', true);
                                $last_name = get_user_meta($usuario->ID, 'last_name', true);
                                $nombre_completo = trim($first_name . ' ' . $last_name);
                                $usuarios_array[] = array(
                                    'id'   => $usuario->ID,
                                    'name' => $nombre_completo,
                                );
                            }
                        }
                    ?>
                    <select name="usuario_asociado_al_pago" id="usuario_asociado_al_pago">
                        <option value="0">Seleccione un Valor</option>
                        <?php if (!empty($usuarios_array)): ?>
                            <?php foreach ($usuarios_array as $datousuario): ?>
                                <option value="<?= esc_attr($datousuario['id']) ?>"><?= esc_attr($datousuario['name']) ?></option>
                            <?php endforeach ?>
                        <?php endif ?>
                    </select>
                </div>

                <div class="wrap">
                    <label for="cuenta_de_cobro">Cuenta de Cobro</label>
                    <input type="file" id="cuenta_de_cobro" name="cuenta_de_cobro" class="documento-input" accept=".png,.doc,.docx,.pdf" required>
                    <img src="<?= URL_ADONITRANSPLUG.'/assets/images/OTRO.svg' ?>" alt="Icono del archivo" class="document-icon" style="width: 32px; height: 32px;">
                </div>

                <div class="wrap">
                    <label for="foto_del_pago">Comprobante del Pago</label>
                    <input type="file" id="foto_del_pago" name="foto_del_pago" class="documento-input" accept=".jpg,.jpeg,.png,.doc,.docx,.pdf">
                    <img src="<?= URL_ADONITRANSPLUG.'/assets/images/OTRO.svg' ?>" alt="Icono del archivo" class="document-icon" style="width: 32px; height: 32px;">
                </div>

                <div class="wrap wrap-2">
                    <label for="estado_del_pago">Estado del Pago</label>
                    <select name="estado_del_pago" id="estado_del_pago">
                        <option value="0">Seleccione un Valor</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Pagado">Pagado</option>
                        <option value="Rechazado">Rechazado</option>
                    </select>
                </div>
                <div class="wrap wrap-2">
                    <label for="fecha_del_pago">Fecha de Pago</label>
                    <input type="date" id="fecha_del_pago" name="fecha_del_pago" value="">
                </div>
                <div class="wrap">
                    <label for="comentario_del_pago">Comentario</label>
                    <textarea name="comentario_del_pago" id="comentario_del_pago" value=""></textarea>
                </div>
                
                <div class="wrap">
                    <button class="button button-enviar" type="submit">Crear Pago</button>
                    <button class="button button-cancelar" type="button">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>