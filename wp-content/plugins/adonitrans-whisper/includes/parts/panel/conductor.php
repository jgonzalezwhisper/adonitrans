<?php 
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');  
    if (!isset($_POST['action']) || empty($_POST['action'])) {
        exit('Acceso no autorizado');
    }

    $post_id = false;

    // Verificar si el ID del post está en la solicitud
    if (isset($_POST['post_id']) && !empty($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);  // Asegurarse de que es un número entero
    }
?>
<?php if ($post_id): ?>  
<div id="wrap-conductor">
    <div class="tarjeta">
        <div class="wrap-titulo">
            <h3 class="titulo">CONDUCTOR</h3>
            <h4 class="subtitulo">Gestiona tus RECORRIDOS asignados desde este panel.</h4>
        </div>
        <p>Administra y gestiona los vehículos registrados en ADONITRANS desde este panel. Mantén toda la información organizada y actualizada.</p>

        <div class="wrap-gestion-conductor">
            <div class="wrap wrap-title">
                <h3 class="title">Información de Recorrido</h3>
            </div>
            <?php
                $id_solicitante = get_field('id_solicitante_recorrido', $post_id);
                $foto_de_usuario = get_field('foto_de_usuario', 'user_' . $id_solicitante['ID']);
                $empresa_solicitante_recorrido = get_field('empresa_solicitante_recorrido', $post_id);
                $tiempo_de_espera_para_recorrido = get_field('tiempo_de_espera_para_recorrido', $empresa_solicitante_recorrido);
                $fecha_inicio_recorrido = get_field('fecha_inicio_recorrido', $post_id);
                $hora_inicio_recorrido = get_field('hora_inicio_recorrido', $post_id);
                $ciudad_inicial_recorrido = get_field('ciudad_inicial_recorrido', $post_id)->ID;
                $ciudad_inicial_recorrido = get_field('ciudad_para_empresa', $ciudad_inicial_recorrido);
                $barrio_inicial_recorrido = get_field('barrio_inicial_recorrido', $post_id);
                $ciudad_final_recorrido = get_field('ciudad_final_recorrido', $post_id)->ID;
                $ciudad_final_recorrido = get_field('ciudad_para_empresa', $ciudad_final_recorrido);
                $barrio_final_recorrido = get_field('barrio_final_recorrido', $post_id);
                
                $foto_de_usuario = $foto_de_usuario? $foto_de_usuario['url']: URL_ADONITRANSPLUG."assets/images/profile.jpg";
            ?>
            <form id="conductor-form" method="post" class="formplug" autocomplete="off">
                <input type="hidden" name="post_id" value="<?= $post_id ?>">
                <input type="hidden" id="tiempo_espera" value="<?= $tiempo_de_espera_para_recorrido ?>">
                <div id="wrap-contador-espera" class="ocultar">
                    <div class="contador">00:00</div>
                    <a href="#" class="btn button" data-action="iniciar" disabled>Inicio de Ruta</a>
                    <input type="hidden" name="tiempo_de_espera" id="tiempo_de_espera" value="">
                </div>
                <div class="trayecto_info">
                    <div class="column column-1">
                        <div class="wrap profile_photo">
                            <img id="profile-photo-preview" data-original="<?= $foto_de_usuario; ?>" src="<?= $foto_de_usuario; ?>" >
                        </div>
                        <div class="wrap profile_info_user">
                            <?php wp_nonce_field('create_user_action', 'create_user_nonce'); ?>
                            <div class="wrap">
                                <label for="user_name">Usuario</label>
                                <h4><?= $id_solicitante['user_firstname']." ".$id_solicitante['user_lastname'] ?></h4>
                            </div>
                            <div class="wrap">
                                <label for="user_company">Empresa</label>
                                <h4><?= $empresa_solicitante_recorrido->post_title ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="column column-2">
                        <div class="wrap trayecto">
                            <div class="trayecto_user">
                                <label for="user-destinoi"><i class="icofont-save"></i> Origen:</label>
                                <p><?= $ciudad_inicial_recorrido." - ".$barrio_inicial_recorrido; ?></p>
                                <p><?= $hora_inicio_recorrido ?></p>
                            </div>
                            <div class="trayecto_user">
                                <label for="user-destinoi"><i class="icofont-save"></i> Destino:</label>
                                <p><?= $ciudad_final_recorrido." - ".$barrio_final_recorrido; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="column column-3">
                        <div class="wrap trayecto_acciones">
                            <div class="trayecto_bottons binicio">
                                <a href="#" class="btn button" data-action="llegada">Llegada al Destino</a>                               
                                <a href="#" class="btn button" data-action="cancelar">Cancelar Ruta</a>
                                <a href="#" class="btn button save-info ocultar" data-action="save-info">Guardar Informacion</a>
                                <a href="#" class="btn button end-recorrido ocultar" data-action="end-recorrido">Finalizar Recorrido</a>
                            </div>
                            <div class="trayecto_bottons bmedio">
                                <h5><strong>Track Time</strong></h5>
                                <ul id="list-info-recorrido">
                                    <li><strong>Hora Llegada (Conductor):</strong></strong> <span>8:30 a.m</span></li>
                                    <li><strong>Hora Inicio Recorrido:</strong> <span>8:40 a.m</span></li>
                                    <li><strong>Parada 1:</strong> <span>9:10 a.m</span></li>
                                    <li><strong>Parada 2:</strong> <span>9:10 a.m</span></li>
                                    <li><strong>Finalizacion Recorrido:</strong> <span>9:10 a.m</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wrap trayecto_finalizar">
                    <h3 class="titulo">Finaliza el Servicio</h3>

                    <div id="wrap-peajes" class="wrap wrap-fanjas">
                        <h5><strong>Peajes en el Camino</strong></h5>

                        <div id="clonar-peaje" style="display:none">

                            <div class="franja show">
                                <div class="franja_item">
                                    <label for="">Nombre del Peaje</label>
                                    <input type="text" name="nomb_peaje[]" placeholder="Nombre Peaje">
                                </div>
                                <div class="franja_item">
                                    <label for="">Valor del Peaje</label>
                                    <input type="text" name="valor_peaje[]" placeholder="Valor del peaje Peaje">
                                </div>
                                <div class="franja_item">
                                    <label for="">Comprobante de pago</label>
                                    <input type="file" name="comprobante_pago_peaje[]" accept="image/*">
                                </div>

                                <button type="button" class="button remove">Eliminar Peaje</button>
                            </div>         
                        </div>

                        <div id="wrap-peaje" class="wrap-franja">                            
                        </div>

                        <a class="button button-add"><i class="icofont-plus-circle"></i>Añadir Peaje</a>
                    </div>

                    <div class="trayecto_comments">
                        <label>Comentarios adicionales:</label>
                        <textarea id="trayecto_commen" name="trayecto_commen" rows="8" cols="115"></textarea>
                    </div>
                    <div class="trayecto_total">                        
                        <div class="trayecto_total_detail">
                            <ul>
                                <li><strong>Recorrido Inicial:</strong> <span class="symbol">$<span class="price">35.000</span></span></li>
                                <li><strong>Trayectos Adicionales:</strong> <span class="symbol">$<span class="price">55.000</span></span></li>
                                <li><strong>Peajes:</strong> <span class="symbol">$<span class="price">55.000</span></span></li>
                                <li><strong>Usuario(s) Adicional(es):</strong> <span class="symbol">$<span class="price">75.000</span></span></li>
                                <li><strong>Tiempo de Espera:</strong> <span class="symbol">$<span class="price">15.000</span></span></li>
                            </ul>
                        </div>
                        <h4>Total servicio: <span>$51.000</span></h4>
                    </div>
                </div>   
            </form>
        </div> 
    </div>
</div>
<?php else:
    exit('Recorrido no encontrado'); 
endif ?>