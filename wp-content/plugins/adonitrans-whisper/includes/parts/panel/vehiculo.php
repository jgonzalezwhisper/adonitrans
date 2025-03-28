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
                                <td class="<?= str_replace(' ', '-', strtolower($estado_vehiculo)); ?>"><?= $estado_vehiculo; ?></td>
                                <td>
                                    <div class="acciones">
                                        <button class="accion edit-vehiculo" data-id="<?= get_the_ID(); ?>"><i class="icofont-pencil"></i>Editar</button>
                                        <button class="accion delete-vehiculo delete-user" data-id="<?= get_the_ID(); ?>"><i class="icofont-info-circle"></i>Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile;wp_reset_postdata(); ?>     
                    <?php else: ?>
                        <p>No hay vehiculos creados.</p>                 
                    <?php endif ?>
                        
                </tbody>
            </table>
        </div>

        <div class="wrap-gestion-vehiculos" style="display:none">
            <div class="wrap wrap-title">
                <h3 class="title">Crear Vehiculo</h3>
            </div>
            <ul class="adonitrans-tabs-nav">
                <li class="active" data-tab="tab1">Datos del Vehículo</li>
                <li data-tab="tab2">Especificaciones Técnicas</li>
                <li data-tab="tab3">Documentos</li>
                <li data-tab="tab4">Archivos</li>
                <li data-tab="tab5">Imágenes</li>
            </ul>
            <form id="vehiculo-form" method="post" class="formplug" autocomplete="off" enctype="multipart/form-data">
                <input type="hidden" id="vehiculo-id" name="vehiculo-id" value="">
                <?php wp_nonce_field('create_vehiculo_action', 'create_vehiculo_nonce'); ?>

                <div class="adonitrans-tabs-content">

                    <!-- Datos del Vehículo -->
                    <div id="tab1" class="tab-content active">

                        <?php
                            $fields = [
                                [
                                    'id' => 'estado_del_vehiculo',
                                    'label' => 'Estado',
                                    'type' => 'select',
                                    'class' => 'wrap wrap-2',
                                    'options' => ['Activo', 'Inactivo']
                                ],
                                [
                                    'id' => 'ruta__movil_vehi',
                                    'label' => 'Ruta / Móvil',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'fecha_vinculacion_vehi',
                                    'label' => 'Fecha Vinculación',
                                    'type' => 'date',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'link_gps_vehi',
                                    'label' => 'Link GPS',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'placa_vehiculo',
                                    'label' => 'Placa',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'fecha_terminacion_vehi',
                                    'label' => 'Fecha Terminación',
                                    'type' => 'date',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'ciudad_vehiculo',
                                    'label' => 'Ciudad',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'empresa_vehi',
                                    'label' => 'Empresa',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'ultimo_mantenimiento_preventivo_vehi',
                                    'label' => 'Ultimo Mantenimiento Preventivo',
                                    'type' => 'date',
                                    'class' => 'wrap wrap-2'
                                ],                                
                                /*[
                                    'id' => 'propietario_de_vehiculo',
                                    'label' => 'Propietario de Vehículo',
                                    'type' => 'select',
                                    'class' => 'wrap wrap-2',
                                    'options' => 'users'
                                ]*/
                            ];
                            render_fields($fields);
                        ?>                        
                    </div>
                    <!-- Especificaciones Técnicas -->
                    <div id="tab2" class="tab-content">

                        <?php
                            $fields = [
                                [
                                    'id' => 'marca_vehiculo',
                                    'label' => 'Marca del Vehículo',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2',
                                ],
                                [
                                    'id' => 'servicio_vehi',
                                    'label' => 'Servicio',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'serial_vehiculo',
                                    'label' => 'No de Serie',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'combustible_vehi',
                                    'label' => 'Combustible',
                                    'type' => 'select',
                                    'class' => 'wrap wrap-2',
                                    'options' => ['Gasolina','Gasolina/Gas','Diesel','Gas','Electrico']
                                ],
                                [
                                    'id' => 'cantidad_pasajeros_vehiculo',
                                    'label' => 'Capacidad',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'modelo_vehiculo',
                                    'label' => 'Modelo',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'color_vehi',
                                    'label' => 'Color',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'no_de_motor_vehi',
                                    'label' => 'No de Motor',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'chasis_vehiculo',
                                    'label' => 'Chasis',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'linea_vehi',
                                    'label' => 'Línea',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'cilindraje_vehi',
                                    'label' => 'Cilindraje',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],
                                [
                                    'id' => 'tipo_de_vehiculo',
                                    'label' => 'Tipo de vehículo',
                                    'type' => 'select',
                                    'class' => 'wrap wrap-2',
                                    'options' => ['', 'Automovil', 'Camioneta', 'Doble Cabina', 'Campero', 'Van', 'Bus', 'Buseta']
                                ],
                                [
                                    'id' => 'carroceria_vehi',
                                    'label' => 'Carrocería',
                                    'type' => 'text',
                                    'class' => 'wrap wrap-2'
                                ],                                
                            ];
                            render_fields($fields);
                        ?>
                    </div>
                    <!-- Documentos -->
                    <div id="tab3" class="tab-content">

                        <?php
                            $fieldsets = [
                                [
                                    'legend' => 'Seguro Contractual-Extracontractual',
                                    'class' => 'fieldset-seguro wrap wrap-2',
                                    'fields' => [
                                        [
                                            'id' => 'seguro_fecha',
                                            'label' => 'Fecha',
                                            'type' => 'date',
                                            'class' => 'wrap wrap-2',
                                        ],
                                        [
                                            'id' => 'seguro_numero',
                                            'label' => 'Número',
                                            'type' => 'text',
                                            'class' => 'wrap wrap-2'
                                        ],
                                        [
                                            'id' => 'seguro_empresa',
                                            'label' => 'Empresa',
                                            'type' => 'text',
                                            'class' => 'wrap wrap-2',
                                        ]
                                    ]
                                ],
                                [
                                    'legend' => 'SOAT',
                                    'class' => 'fieldset-soat wrap wrap-2',
                                    'fields' => [
                                        [
                                            'id' => 'soat_fecha',
                                            'label' => 'Fecha',
                                            'type' => 'date',
                                            'class' => 'wrap wrap-2',
                                        ],
                                        [
                                            'id' => 'soat_numero',
                                            'label' => 'Número',
                                            'type' => 'text',
                                            'class' => 'wrap wrap-2'
                                        ],
                                        [
                                            'id' => 'soat_empresa',
                                            'label' => 'Empresa',
                                            'type' => 'text',
                                            'class' => 'wrap wrap-2'
                                        ]
                                    ]
                                ],
                                [
                                    'legend' => 'Tecnomecánica',
                                    'class' => 'fieldset-tecnomecanica wrap wrap-2',
                                    'fields' => [
                                        [
                                            'id' => 'tecno_fecha',
                                            'label' => 'Fecha',
                                            'type' => 'date',
                                            'class' => 'wrap wrap-2',
                                        ],
                                        [
                                            'id' => 'tecno_preventiva',
                                            'label' => 'Preventiva',
                                            'type' => 'date',
                                            'class' => 'wrap wrap-2',
                                        ],
                                        [
                                            'id' => 'tecno_fuec',
                                            'label' => 'FUEC',
                                            'type' => 'date',
                                            'class' => 'wrap wrap-2',
                                        ],
                                    ]
                                ],
                                [
                                    'legend' => 'Tarjeta de Operación',
                                    'class' => 'fieldset-tarjeta wrap wrap-2',
                                    'fields' => [
                                        [
                                            'id' => 'tarjeta_fecha',
                                            'label' => 'Fecha',
                                            'type' => 'date',
                                            'class' => 'wrap wrap-2',
                                        ],
                                        [
                                            'id' => 'tarjeta_numero',
                                            'label' => 'Número',
                                            'type' => 'text',
                                            'class' => 'wrap wrap-2',
                                        ],
                                        [
                                            'id' => 'tarjeta_fecha_matricula',
                                            'label' => 'Fecha Matricula',
                                            'type' => 'date',
                                            'class' => 'wrap wrap-2',
                                        ],
                                    ]
                                ]
                            ];

                            render_fields_group($fieldsets);
                        ?>                        
                    </div>
                    <!-- Archivos -->
                    <div id="tab4" class="tab-content">

                        <div id="archivos-vehiculo-wrap" class="wrap">

                            <div class="clonar" style="display: none;">
                                <div class="archivo-vehiculo">
                                    <div class="wrap wrap-2">                                        
                                        <label for="">
                                            Nombre Archivo
                                            <input type="text" name="nombre_archivo[]" class="nombre-archivo">
                                        </label>
                                    </div>
                                    <div class="wrap wrap-2 wrap-file">
                                        <label for="">
                                            Archivo
                                            <input type="file" name="file_archivo[]">
                                        </label>
                                        <i class="icofont-trash"></i>
                                    </div>
                                </div>
                            </div>

                            <div id="wrap-archivos-vehiculo" class="wrap"></div>

                            <button id="add-file-vehiculo" href="#" class="button">Añadir Archivo <i class="icofont-ui-file"></i></button>
                        </div>
                        
                    </div>
                    <!-- Imágenes -->
                    <div id="tab5" class="tab-content">

                        <div id="imagenes-vehiculo-wrap" class="wrap wrap-clonar">

                            <div class="clonar" style="display: none;">
                                <div class="imagen-vehiculo">
                                    <div class="wrap wrap-2">                                        
                                        <label for="">
                                            Nombre Archivo
                                            <input type="text" name="nombre_imagen[]" class="nombre-archivo">
                                        </label>
                                    </div>
                                    <div class="wrap wrap-2 wrap-file">
                                        <label for="">
                                            Imagen
                                            <input type="file" name="imagen_archivo[]" accept="image/*">
                                        </label>
                                        <i class="icofont-trash"></i>
                                    </div>
                                </div>
                            </div>

                            <div id="wrap-imagenes-vehiculo" class="wrap"></div>

                            <button id="add-imagen-vehiculo" href="#" class="button">Añadir Imagen <i class="icofont-image"></i></button>
                        </div>
                        
                    </div>
                    
                </div>

                <div class="wrap">
                    <button class="button button-add" type="submit" name="submit-user">Crear Vehículo</button>
                    <button class="button button-remove" type="button" id="cancelar-vehiculo-btn">Cancelar</button>
                </div>
            </form>
        </div> 
    </div>
</div>