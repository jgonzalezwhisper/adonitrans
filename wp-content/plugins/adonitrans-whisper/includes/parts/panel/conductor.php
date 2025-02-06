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
                $user_id = wp_get_current_user()->ID;
                $user_key = 'user_' . $user_id;
                $cedula_usuario = get_field('cedula_usuario', $user_key);
                $telefono = get_field('telefono', $user_key);
                $direccion = get_field('direccion', $user_key);
                $foto_de_usuario = get_field('foto_de_usuario', $user_key);
                $foto_de_usuario = $foto_de_usuario? $foto_de_usuario['url']: URL_ADONITRANSPLUG."assets/images/profile.jpg";
                $user_roles = $current_user->roles;
                $user_role = !empty($user_roles) ? $user_roles[0] : '';
            ?>
            <form id="conductor-form" method="post" class="formplug" autocomplete="off">
                <div class="trayecto_info">
                    <div class="column-1">
                        <div class="wrap profile_photo">
                            <img id="profile-photo-preview" data-original="<?= $foto_de_usuario; ?>" src="<?= $foto_de_usuario; ?>" alt="<?= esc_attr(get_user_meta($user_id, 'first_name', true)); ?>">
                        </div>
                        <div class="wrap profile_info_user">
                            <input type="hidden" id="user-id" name="user-id" value="<?= esc_attr($user_id); ?>">
                            <input type="hidden" name="select_rolesusuario" value="<?= esc_attr($user_role); ?>">
                            <?php wp_nonce_field('create_user_action', 'create_user_nonce'); ?>
                            <div class="wrap">
                                <label for="user_name">Usuario</label>
                                <h4>Yuli Espinosa</h4>
                            </div>
                            <div class="wrap">
                                <label for="user_company">Empresa</label>
                                <h4>Cartón Colombia</h4>
                            </div>
                        </div>
                    </div>
                    <div class="column-2">
                        <div class="wrap trayecto">
                            <div class="trayecto_user">
                                <label for="user-destinoi"><i class="icofont-save"></i> Origen:</label>
                                <input type="text" id="user-destinoi" name="user-destinoi" value="<?= $direccion; ?>">
                                <input class="time_user" type="text" id="user-horainicio" name="user-horainicio" value="8:30am">
                            </div>
                            <div class="trayecto_user">
                                <label for="user-destinoi"><i class="icofont-save"></i> Destino:</label>
                                <input type="text" id="user-destinof" name="user-destinof" value="<?= $direccion; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="column-2">
                        <div class="wrap trayecto_acciones">
                            <div class="trayecto_bottons binicio">
                                <button>Punto de inicio</button>
                                <p>8:30 a.m</p>
                            </div>
                            <div class="trayecto_bottons bmedio">
                                <button>Inicia el recorrido</button>
                                <p>8:40 a.m</p>
                            </div>
                            <div class="trayecto_bottons bfinal">
                                <button>Finaliza el recorrido</button>
                                <p>9:55 a.m</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="trayecto_finalizar">
                    <h3 class="titulo">Finaliza el Servicio</h3>
                    <div class="trayecto_peajes">
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
                    </div>
                </div>
            
                
            </form>
        </div> 
    </div>
</div>
<?php else:
    exit('Recorrido no encontrado'); 
endif ?>