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
                <div class="trayecto_info">
                    <div class="column-1">
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
                    <div class="column-2">
                        <div class="wrap trayecto">
                            <div class="trayecto_user">
                                <label for="user-destinoi"><i class="icofont-save"></i> Origen:</label>
                                <input type="text" id="user-destinoi" name="user-destinoi" value="<?= $ciudad_inicial_recorrido." - ".$barrio_inicial_recorrido; ?>">
                                <input class="time_user" type="text" id="user-horainicio" name="user-horainicio" value="<?= $hora_inicio_recorrido ?>">
                            </div>
                            <div class="trayecto_user">
                                <label for="user-destinoi"><i class="icofont-save"></i> Destino:</label>
                                <input type="text" id="user-destinof" name="user-destinof" value="<?= $ciudad_final_recorrido." - ".$barrio_final_recorrido; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="column-2">
                        <div class="wrap trayecto_acciones">
                            <div class="trayecto_bottons binicio">
                                <a href="#" class="btn button save-info">Guardar Informacion</a>
                                <p>Hora de Inicio: 8:30 a.m</p>
                            </div>
                            <div class="trayecto_bottons bmedio">
                                <!-- <button>Inicia el recorrido</button>
                                <p>8:40 a.m</p> -->
                            </div>
                            <div class="trayecto_bottons bfinal">
                                <a href="#" class="btn button end-recorrido">Finalizar Recorrido</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wrap trayecto_finalizar">
                    <h3 class="titulo">Finaliza el Servicio</h3>

                    <div id="wrap-peajes" class="wrap wrap-fanjas">
                        <h5>Añadir Pasajero Adicional</h5>

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

                        <a class="button button-add"><i class="icofont-plus-circle"></i>Añadir</a>
                    </div>

                    <!-- <div class="trayecto_peajes">
                        <h4>Ruta con peajes?</h4>
                        <div class="peaje_option">
                            <input type="radio" id="peajesi" name="peajesi" value="SI"><label>SI</label>
                        </div>
                        <div class="peaje_option peaje_option_no">
                            <input type="radio" id="peajeno" name="peajeno" value="NO"><label>NO</label>
                        </div>
                        <div class="peaje_si_aplica">
                            <input type="text" id="peaje_nombre" name="peaje_nombre" placeholder="Nombre peaje">
                            <input type="number" id="peaje_valor" name="peaje_valor" placeholder="Valor del peaje">
                            <label>Comprobante pago</label>
                            <input type="file" id="peaje_pago" name="peaje_pago" placeholder="Nombre peaje">
                        </div>
                    </div>
                    <div class="trayecto_comments">
                        <label>Comentarios adicionales:</label>
                        <textarea id="trayecto_commen" name="trayecto_commen" rows="8" cols="115"></textarea>
                    </div>
                    <div class="trayecto_total">
                        <h4>Total servicio: <span>$51.000</span></h4>
                        <div class="trayecto_total_detail">
                            <p>Recorrido 100587 CL - PL - CL <span>$35.000</span></p>
                            <p>Recorrido 100587 CL - PL - CL <span>$35.000</span></p>
                            <p>Recorrido 100587 CL - PL - CL <span>$35.000</span></p>
                            <p>Recorrido 100587 CL - PL - CL <span>$35.000</span></p>
                        </div>
                    </div> -->
                </div>   
            </form>
        </div> 
    </div>
</div>
<?php else:
    exit('Recorrido no encontrado'); 
endif ?>