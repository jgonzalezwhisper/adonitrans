<?php 
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');  
    if (!isset($_POST['action']) || empty($_POST['action'])) {
        exit('Acceso no autorizado');
    }
?>
<div id="wrap-asignaciones">
    <div class="tarjeta">
        <div class="wrap-titulo">
            <h3 class="titulo">ASIGNACIONES</h3>
            <h4 class="subtitulo">Gestiona las asignaciones realizados en la plataforma</h4>
        </div>
        <p>Administra y gestiona las asignaciones registradas en ADONITRANS desde este panel. Mantén toda la información organizada y actualizada.</p>

        <div class="wrap volver" style="display:none">
            <span class="button"><i class="icofont-double-left"></i> Volver</span>
        </div>

        <div class="wrap wrap-acciones ">
            <div class="botones">
                <div class="boton" data-action="asginar">
                    <i class="icofont-connection"></i> <span>Asignar</span>
                </div>

                <div class="boton" data-action="ver-calendario">
                    <i class="icofont-calendar"></i> <span>Ver Calendario</span>
                </div>                

                <div class="boton" data-action="ver-tabla">
                    <i class="icofont-table"></i> <span>Ver Tabla</span>
                </div>
                
                <div class="boton" data-action="exportar">
                    <i class="icofont-file-excel"></i> <span>Reportes</span>
                </div>
                <div class="boton" data-action="exportar-pdf">
                    <i class="icofont-file-pdf"></i> <span>CUENTA DE COBRO Conductor</span>
                </div>
                <div class="boton" data-action="exportar-pdf-movil">
                    <i class="icofont-file-pdf"></i> <span>CUENTA DE COBRO # Móvil</span>
                </div>
            </div>
        </div>

        <div class="wrap wrap-gestion wrap-gestion-asignaciones" data-target="asginar" style="display:none">
            <div class="wrap wrap-title">
                <h3 class="title">Crear Asignación</h3>
            </div>

            <form id="asignacion-form" method="post" class="formplug" autocomplete="off">
                <?php wp_nonce_field('create_asignacion_action', 'create_asignacion_nonce'); ?>
                <input type="hidden" id="asignacion-id" name="asignacion-id" value="">           

                <?php
                    $argscon = array(
                        'role'    => 'conductor',
                        'orderby' => 'display_name',
                        'order'   => 'ASC',
                        'meta_query' => array(
                            array(
                                'key'   => 'estado_usuario',
                                'value' => 'Activo',
                                'compare' => '='
                            )
                        )
                    );
                    $user_query = new WP_User_Query($argscon);
                    $conductores = $user_query->get_results();
                ?>
                <div class="wrap">
                    <label for="id_conductor_asignado">Conductor Asignado</label>
                    <select id="id_conductor_asignado" name="id_conductor_asignado" required>
                        <option value="">Selecciona un Conductor</option>
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

                <div class="wrap wrap-2">
                    <label for="inicio_semana_asignacion">Fecha Inicio Semana</label>
                    <input type="date" id="inicio_semana_asignacion" name="inicio_semana_asignacion" min="<?= date('Y-m-d') ?>" value="" placeholder="dd/mm/yyyy">
                </div>

                <div class="wrap wrap-2">
                    <label for="fin_semana_asignacion">Fecha Fin Semana</label>
                    <input type="date" id="fin_semana_asignacion" name="fin_semana_asignacion" min="<?= date('Y-m-d') ?>" value="" placeholder="dd/mm/yyyy">
                </div>

                <div class="wrap"></div>

                <div id="wrap-asignacion-dias" class="wrap wrap-fanjas wrap-franjas-repetidor">

                    <div id="clonar-asignacion" style="display:none">
                       <div class="franja">
                            <div class="franja_item">
                                <label for="dia_inicio_de_asignacion">Día Inicio</label>
                                <input type="date" id="dia_inicio_de_asignacion" name="dia_inicio_de_asignacion[]" min="<?= date('Y-m-d') ?>" value="" placeholder="dd/mm/yyyy">
                            </div>
                            <div class="franja_item">
                                <label for="dia_fin_de_asignacion">Día Fin</label>
                                <input type="date" id="dia_fin_de_asignacion" name="dia_fin_de_asignacion[]" min="<?= date('Y-m-d') ?>" value="" placeholder="dd/mm/yyyy" >
                            </div>
                            <div class="franja_item">
                                <label for="">Franja Horaria</label>
                                <select id="" class="select_franja_asignacion" name="franja_horaria_asignacion[]"  >
                                    <option value="">Selecciona una Franja</option>
                                    <?php
                                        $franjas = get_field('franjas_horas_trabajo', 'option');
                                        $nombres_franjas = [];

                                        if (!empty($franjas) && is_array($franjas)) {
                                            foreach ($franjas as $franja) {
                                                if (!empty($franja['nombre'])) {
                                                    $nombres_franjas[] = $franja['nombre'];
                                                }
                                            }
                                        }
                                    ?>
                                    <?php foreach ($nombres_franjas as $key => $nombre_franja): ?>
                                        <option value="<?= $nombre_franja ?>"><?= $nombre_franja ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="franja_item">
                                <label for="">Vehículo</label>
                                <select class="select" name="vehiculo_asignado[]"  >
                                    <option value="">Seleccione un Vehículo</option>
                                    <?php
                                        $vehiculos = get_posts([
                                            'post_type'      => 'vehiculo',
                                            'posts_per_page' => -1,
                                            'fields'         => 'ids',
                                            'meta_query'     => [
                                                [
                                                    'key'   => 'estado_del_vehiculo',
                                                    'value' => 'Activo'
                                                ]
                                            ]
                                        ]);

                                        $vehiculos_arr = array_map(function($id) {
                                            return [
                                                'ID'    => $id,
                                                'placa' => get_field('placa_vehiculo', $id)
                                            ];
                                        }, $vehiculos);
                                    ?>
                                    <?php foreach ($vehiculos_arr as $key => $vehiculo): ?>
                                        <option value="<?= $vehiculo['ID'] ?>"><?= $vehiculo['placa'] ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <button type="button" class="button remove">Eliminar Día(s)</button>
                        </div>         
                    </div>

                    <div id="wrap-asignacion-dia" class="wrap-franja">

                        <div class="franja">
                            <div class="franja_item">
                                <label for="dia_inicio_de_asignacion">Día Inicio</label>
                                <input type="date" id="dia_inicio_de_asignacion" name="dia_inicio_de_asignacion[]" min="<?= date('Y-m-d') ?>" value="" placeholder="dd/mm/yyyy">
                            </div>
                            <div class="franja_item">
                                <label for="dia_fin_de_asignacion">Día Fin</label>
                                <input type="date" id="dia_fin_de_asignacion" name="dia_fin_de_asignacion[]" min="<?= date('Y-m-d') ?>" value="" placeholder="dd/mm/yyyy" >
                            </div>
                            <div class="franja_item">
                                <label for="">Franja Horaria</label>
                                <select id="" class="select_franja_asignacion" name="franja_horaria_asignacion[]"  >
                                    <option value="">Selecciona una Franja</option>
                                    <?php
                                        $franjas = get_field('franjas_horas_trabajo', 'option');
                                        $nombres_franjas = [];

                                        if (!empty($franjas) && is_array($franjas)) {
                                            foreach ($franjas as $franja) {
                                                if (!empty($franja['nombre'])) {
                                                    $nombres_franjas[] = $franja['nombre'];
                                                }
                                            }
                                        }
                                    ?>
                                    <?php foreach ($nombres_franjas as $key => $nombre_franja): ?>
                                        <option value="<?= $nombre_franja ?>"><?= $nombre_franja ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="franja_item">
                                <label for="">Vehículo</label>
                                <select class="select_vehiculo" name="vehiculo_asignado[]"  >
                                    <option value="">Seleccione un Vehículo</option>
                                    <?php
                                        $vehiculos = get_posts([
                                            'post_type'      => 'vehiculo',
                                            'posts_per_page' => -1,
                                            'fields'         => 'ids',
                                            'meta_query'     => [
                                                [
                                                    'key'   => 'estado_del_vehiculo',
                                                    'value' => 'Activo'
                                                ]
                                            ]
                                        ]);

                                        $vehiculos_arr = array_map(function($id) {
                                            return [
                                                'ID'    => $id,
                                                'placa' => get_field('placa_vehiculo', $id)
                                            ];
                                        }, $vehiculos);
                                    ?>
                                    <?php foreach ($vehiculos_arr as $key => $vehiculo): ?>
                                        <option value="<?= $vehiculo['ID'] ?>"><?= $vehiculo['placa'] ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <button type="button" class="button remove">Eliminar Día(s)</button>
                        </div>                        
                    </div>

                    <a class="button button-add"><i class="icofont-plus-circle"></i>Añadir Día(s)</a>
                    
                </div>

                <div class="wrap">
                    <button class="button button-add" type="submit" name="submit-user">Crear Asignación</button>
                    <button class="button cancelar button-remove" type="button" id="cancelar-asignacion-btn">Cancelar</button>
                </div>
            </form>           
        </div> 

        <div class="wrap wrap-gestion wrap-listado-asignaciones" data-target="ver-tabla" style="display:none">
            <table id="table-asignaciones" class="display table-adoni">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Conductor</th>
                        <th>Periodo</th>
                        <th>Franja</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $args = [
                            'post_type'      => 'asignacion',
                            'posts_per_page' => -1,
                        ];
                        $query = new WP_Query($args);
                    ?>
                    
                    <?php if ($query->have_posts()): ?>
                        <?php while($query->have_posts()): $query->the_post();?>
                            <?php
                                $inicio_semana_asignacion = get_field('inicio_semana_asignacion', get_the_ID());
                                $fin_semana_asignacion = get_field('fin_semana_asignacion', get_the_ID());

                                $first_name = '';
                                $email = '';
                                $user_id_tabla = get_field('id_conductor_asignado', get_the_ID())['ID'];
                                if ($user_id_tabla) {
                                    $user_info_tabla = get_userdata($user_id_tabla);
                                    if ($user_info_tabla) {
                                        $first_name = $user_info_tabla->first_name;
                                        $email = $user_info_tabla->user_email;
                                    }
                                }
                                $franja = "";
                                if (have_rows('asignaciones_de_la_semana', get_the_ID())) {
                                    $valores = [];
                                    while (have_rows('asignaciones_de_la_semana')) {
                                        the_row();
                                        if ($franja = get_sub_field('franja_horaria_asignacion')) $valores[] = $franja;
                                    }
                                    $franja = implode(', ', $valores);
                                }
                            ?>
                            <tr>
                                <td><?= get_the_ID(); ?></td>
                                <td class="center"><?= $first_name." - ".$email ?></td>
                                <td class="center"><?= $inicio_semana_asignacion." -- ".$fin_semana_asignacion ?></td>
                                <td class="center"><?= $franja ?></td>
                                <td>
                                    <div class="acciones">
                                        <button class="accion edit-asignacion" data-id="<?= get_the_ID(); ?>">Editar</button>
                                        <button class="accion delete-asignacion" data-id="<?= get_the_ID(); ?>">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile;wp_reset_postdata(); ?>    
                    <?php else: ?>
                        <p>No asignaciones creadas.</p>                
                    <?php endif ?>
                        
                </tbody>
            </table>
        </div>

        <div class="wrap wrap-gestion wrap-calendario-asignaciones" data-target="ver-calendario" style="display:none">
            <div class="wrap">
                <form id="filt-cal-form" method="post" class="formplug" autocomplete="off">
                    <?php
                        $argscon = array(
                            'role'    => 'conductor',
                            'orderby' => 'display_name',
                            'order'   => 'ASC',
                            'meta_query' => array(
                                array(
                                    'key'   => 'estado_usuario',
                                    'value' => 'Activo',
                                    'compare' => '='
                                )
                            )
                        );
                        $user_query = new WP_User_Query($argscon);
                        $conductores = $user_query->get_results();
                    ?>
                    <div class="wrap">
                        <label for="id_conductor_asignado_filtcal">Conductor Asignado</label>
                        <select id="id_conductor_asignado_filtcal" name="id_conductor_asignado" required>
                            <option value="">Selecciona un Conductor</option>
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
                </form>
            </div>
            <br>
                
            <div id="calendar"></div>
        </div>

        <div class="wrap wrap-gestion wrap-exportar-asignaciones" data-target="exportar" style="display:none">
            <div class="wrap">
                <form id="filt-excel-form" method="post" class="formplug" autocomplete="off">
                    <?php
                        $argscon = array(
                            'role'    => 'conductor',
                            'orderby' => 'display_name',
                            'order'   => 'ASC',
                            'meta_query' => array(
                                array(
                                    'key'   => 'estado_usuario',
                                    'value' => 'Activo',
                                    'compare' => '='
                                )
                            )
                        );
                        $user_query = new WP_User_Query($argscon);
                        $conductores = $user_query->get_results();

                        $empresa_posts = get_posts([
                            'post_type' => 'empresa',
                            'numberposts' => -1,
                            'fields' => ['ID', 'post_title'],
                        ]);
                        $empresa_posts_data = array_map(function($post) {
                            return [
                                'ID' => $post->ID,
                                'post_title' => $post->post_title,
                            ];
                        }, $empresa_posts);

                        $argscol = [
                            'role' => 'colaborador',
                            'orderby' => 'display_name',
                            'order'   => 'ASC',
                            'meta_query' => array(
                                array(
                                    'key'   => 'estado_usuario',
                                    'value' => 'Activo',
                                    'compare' => '='
                                )
                            ),
                            'fields' => ['ID', 'user_login', 'user_email', 'first_name', 'last_name'], // Campos que necesitamos
                        ];

                        $user_query_col = new WP_User_Query($argscol);
                        $colaboradores = $user_query_col->get_results();
                    ?>
                    <div class="wrap">
                        <div for="id_conductor_asignado_filtcal"><strong>Filtrar Por:</strong></div>
                        <div class="radio-button">
                            <div class="radio">
                                <input type="radio" id="radfiltexcel1" name="tipo-consulta" value="conductor" checked>
                                <label for="radfiltexcel1" data-valor="conductor">Conductor</label>
                            </div>
                            <div class="radio">
                                <input type="radio" id="radfiltexcel5" name="tipo-consulta" value="recxconductor">
                                <label for="radfiltexcel5" data-valor="conductor">Recorrido por Conductor</label>
                            </div>
                            <div class="radio">
                                <input type="radio" id="radfiltexcel2" name="tipo-consulta" value="empresa">
                                <label for="radfiltexcel2" data-valor="empresa">Empresa</label>
                            </div>
                            <div class="radio">
                                <input type="radio" id="radfiltexcel3" name="tipo-consulta" value="colaborador">
                                <label for="radfiltexcel3" data-valor="colaborador">Colaborador</label>
                            </div>
                            <div class="radio">
                                <input type="radio" id="radfiltexcel4" name="tipo-consulta" value="recorrido">
                                <label for="radfiltexcel4" data-valor="recorrido">Recorrido</label>
                            </div>
                            <div class="radio">
                                <input type="radio" id="radfiltexcel6" name="tipo-consulta" value="nume_movil">
                                <label for="radfiltexcel6" data-valor="nume_movil"># Móvil</label>
                            </div>

                            <div class="radio">
                                <input type="radio" id="radfiltexcel7" name="tipo-consulta" value="tirilla">
                                <label for="radfiltexcel7" data-valor="tirilla">Tirilla</label>
                            </div>
                        </div>
                    </div>
                    <div class="wrap wrap-2">
                        <label for="desde_formexcel">Desde: </label>
                        <input type="date" id="desde_formexcel" name="desde_formexcel" value="" placeholder="dd/mm/yyyy" required>
                    </div>
                    <div class="wrap wrap-2">
                        <label for="hasta_formexcel">Hasta: </label>
                        <input type="date" id="hasta_formexcel" name="hasta_formexcel" value="" placeholder="dd/mm/yyyy" required>
                    </div>
                    <div class="wrap wrap-select" data-select="conductor">
                        <label for="selexc_conductor">Conductor</label>
                        <select id="selexc_conductor" name="selexc_conductor" >
                            <option value="">Selecciona un Conductor</option>
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

                    <div class="wrap wrap-select" data-select="empresa" style="display:none">
                        <label for="selexc_empresa">Empresa</label>
                        <select id="selexc_empresa" name="selexc_empresa" >
                            <option value="">Selecciona una Empresa</option>
                            <?php foreach ($empresa_posts_data as $post_data): ?>
                                <option value="<?php echo $post_data['ID']; ?>"><?php echo esc_html($post_data['post_title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>  

                    <div class="wrap wrap-select" data-select="selexc_colaboradorxempresa" style="display:none">
                        <label for="selexc_colaboradorxempresa">Colaborador</label>
                        <select id="selexc_colaboradorxempresa" name="selexc_colaboradorxempresa" >
                            <option value="">Selecciona un Colaborador</option>
                        </select>
                    </div>

                    <div class="wrap wrap-select" data-select="colaborador" style="display:none">
                        <label for="selexc_colaborador">Colaboradores</label>
                        <select id="selexc_colaborador" name="selexc_colaborador" >
                            <option value="">Selecciona un Colaborador</option>
                            <?php foreach ($colaboradores as $user): ?>
                                <?php
                                    $user_id = $user->ID;
                                    $first_name = get_user_meta($user_id, 'first_name', true);
                                    $last_name = get_user_meta($user_id, 'last_name', true);
                                    
                                    $user_name = $first_name . ' ' . $last_name;
                                    $user_email = $user->user_email;
                                ?>
                                <option value="<?= $user_id; ?>"><?= "$user_name ($user_email)"; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="wrap wrap-select" data-select="nume_movil" style="display:none">
                        <label for="select_nume_movil"># Móvil</label>
                        <select id="select_nume_movil" name="select_nume_movil" >
                            <option value="">Selecciona una Placa</option>
                            <?php
                                $titulos_vehiculos = get_posts([
                                    'post_type'      => 'vehiculo',
                                    'post_status'    => 'publish',
                                    'posts_per_page' => -1, 
                                    'fields'         => 'titles',
                                ]);

                                $placas_vehiculo = wp_list_pluck($titulos_vehiculos, 'post_title'); 
                            ?>
                            <?php foreach ($placas_vehiculo as $placa): ?>
                                <option value="<?= $placa; ?>"><?= "$placa"; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="wrap">
                        <button class="button button-add" type="submit">Generar Reporte</button>
                    </div>  
                </form>
            </div>
        </div>

        <div class="wrap wrap-gestion wrap-exportar-pdf" data-target="exportar-pdf" style="display:none">
            <div class="wrap">
                <form id="expor-pdf-conductor" method="post" class="formplug" autocomplete="off">
                    <input type="hidden" name="tipo_consulta" value="pdf-conductor">
                    <?php
                        $argscon = array(
                            'role'    => 'conductor',
                            'orderby' => 'display_name',
                            'order'   => 'ASC',
                            'meta_query' => array(
                                array(
                                    'key'   => 'estado_usuario',
                                    'value' => 'Activo',
                                    'compare' => '='
                                )
                            )
                        );
                        $user_query = new WP_User_Query($argscon);
                        $conductores = $user_query->get_results();

                        // Argumentos para la consulta de posts
                        $args_vehiculos = array(
                            'post_type'      => 'vehiculo', // Reemplaza 'vehiculo' con el nombre de tu CPT
                            'posts_per_page' => -1, // Trae todos los posts
                            'post_status'    => 'publish', // Solo posts publicados
                            'fields'         => 'ids', // Solo obtener los IDs de los posts
                        );
                        // Realizar la consulta
                        $vehiculos_ids = get_posts($args_vehiculos);

                    ?>
                    <div class="wrap wrap-2">
                        <label for="desde_formpdf">Desde: </label>
                        <input type="date" id="desde_formpdf" name="desde_formpdf" value="" placeholder="dd/mm/yyyy" required>
                    </div>
                    <div class="wrap wrap-2">
                        <label for="hasta_formpdf">Hasta: </label>
                        <input type="date" id="hasta_formpdf" name="hasta_formpdf" value="" placeholder="dd/mm/yyyy" required>
                    </div>
                    <div class="wrap wrap-2 wrap-select" data-select="conductor">
                        <label for="sel_condpdf">Conductor</label>
                        <select id="sel_condpdf" name="sel_condpdf" required>
                            <option value="">Selecciona un Conductor</option>
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

                    <div class="wrap ">
                        <div id="wrap-tarifas-descuentos" class="tarifas-dcto-main">
                            <div id="wrap-tarifas" class="wrap-tarifas">
                                <?php
                                $tarifas_descuentos = get_field('tarifas_descuentos', 'option');
                                ?>
                                <?php foreach ($tarifas_descuentos as $valtardesc):  ?>
                                <div class="row-tarifa">
                                    <div class="tarifa-item">
                                        <label for="elm-tarifa-<?php echo $valtardesc['descripcion']; ?>">Descripcion
                                            <input type="text" id="elm-tarifa-<?php echo $valtardesc['descripcion']; ?>" name="descripcion[]" value="<?php echo esc_attr($valtardesc['descripcion']); ?>"  />
                                        </label>
                                    </div>
                                    <div class="tarifa-item">
                                        <label for="elm-tarifa-<?php echo $valtardesc['valor']; ?>">Valor
                                            <input type="text" id="elm-tarifa-<?php echo $valtardesc['valor']; ?>" name="valor[]" value="<?php echo esc_attr($valtardesc['valor']) ?>"  />
                                        </label>
                                    </div>
                                    <button type="button" class="button button-remove remove-tarifa-row"><i class="icofont-info-circle"></i>Eliminar Tarifa</button>
                                </div>
                                <?php endforeach ?>
                            </div>
                            <button type="button" id="add-tarifa-row" class="button button-add"><i class="icofont-plus-circle"></i>Añadir Tarifa</button>
                        </div>
                    </div>

                    <div class="wrap">
                        <button class="button button-add" type="submit">Generar PDF</button>
                    </div>  
                </form>
            </div>
        </div>

        <div class="wrap wrap-gestion exportar-pdf-movil" data-target="exportar-pdf-movil" style="display:none">
            <div class="wrap">
                <form id="expor-pdf-movil" method="post" class="formplug" autocomplete="off">
                    <input type="hidden" name="tipo_consulta" value="pdf-movil">
                    <?php
                        $titulos_vehiculos = get_posts([
                            'post_type'      => 'vehiculo',
                            'post_status'    => 'publish',
                            'posts_per_page' => -1, 
                            'fields'         => 'titles',
                        ]);

                        $placas_vehiculo = wp_list_pluck($titulos_vehiculos, 'post_title'); 

                    ?>
                    <div class="wrap wrap-2">
                        <label for="desde_formpdf_movil">Desde: </label>
                        <input type="date" id="desde_formpdf_movil" name="desde_formpdf_movil" value="" placeholder="dd/mm/yyyy" required>
                    </div>
                    <div class="wrap wrap-2">
                        <label for="hasta_formpdf_movil">Hasta: </label>
                        <input type="date" id="hasta_formpdf_movil" name="hasta_formpdf_movil" value="" placeholder="dd/mm/yyyy" required>
                    </div>
                    <div class="wrap wrap-2 wrap-select" data-select="conductor">
                        <label for="sel_movilpdf"># Móvil</label>
                        <select id="sel_movilpdf" name="sel_movilpdf" required >
                            <option value="">Selecciona un # Móvil</option>
                            <?php foreach ($placas_vehiculo as $placa): ?>
                                <option value="<?= $placa; ?>"> <?= $placa; ?>  </option>
                            <?php endforeach; ?>
                        </select>
                    </div> 

                    <div class="wrap ">
                        <div id="wrap-tarifas-descuentos" class="tarifas-dcto-main">
                            <div id="wrap-tarifas" class="wrap-tarifas">
                                <?php
                                $tarifas_descuentos = get_field('tarifas_descuentos', 'option');
                                ?>
                                <?php foreach ($tarifas_descuentos as $valtardesc):  ?>
                                <div class="row-tarifa">
                                    <div class="tarifa-item">
                                        <label for="elm-tarifa-<?php echo $valtardesc['descripcion']; ?>">Descripcion
                                            <input type="text" id="elm-tarifa-<?php echo $valtardesc['descripcion']; ?>" name="descripcion[]" value="<?php echo esc_attr($valtardesc['descripcion']); ?>"  />
                                        </label>
                                    </div>
                                    <div class="tarifa-item">
                                        <label for="elm-tarifa-<?php echo $valtardesc['valor']; ?>">Valor
                                            <input type="text" id="elm-tarifa-<?php echo $valtardesc['valor']; ?>" name="valor[]" value="<?php echo esc_attr($valtardesc['valor']) ?>"  />
                                        </label>
                                    </div>
                                    <button type="button" class="button button-remove remove-tarifa-row"><i class="icofont-info-circle"></i>Eliminar Tarifa</button>
                                </div>
                                <?php endforeach ?>
                            </div>
                            <button type="button" id="add-tarifa-row" class="button button-add"><i class="icofont-plus-circle"></i>Añadir Tarifa</button>
                        </div>
                    </div>

                    <div class="wrap">
                        <button class="button button-add" type="submit">Generar PDF</button>
                    </div>  
                </form>
            </div>
        </div>
</div>