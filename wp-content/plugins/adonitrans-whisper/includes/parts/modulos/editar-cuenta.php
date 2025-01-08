<div class="wrap-gestion-usuarios" >
    <div class="wrap wrap-title">
        <h3 class="title">Crear Usuario</h3>
    </div>
    <form id="user-form" method="post" enctype="multipart/form-data" class="formplug" autocomplete="off">
        <input type="hidden" id="user-id" name="user-id" value="">
        <?php wp_nonce_field('create_user_action', 'create_user_nonce'); ?>
        <div class="wrap wrap-2">
            <label for="first_name">Nombres</label>
            <input type="text" id="first_name" name="first_name" value="">
        </div>
        <div class="wrap wrap-2">
            <label for="last_name">Apellidos</label>
            <input type="text" id="last_name" name="last_name" value="">
        </div>
        <div class="wrap wrap-2">
            <label for="user_email">Correo</label>
            <input type="email" id="user_email" name="user_email" value="">
        </div>
        <div class="wrap wrap-2">
            <label for="select_rolesusuario">Rol</label>
            <select id="select_rolesusuario" name="select_rolesusuario">
                <option value=""></option>
                <?php
                $roles = [
                'comercial_1'       => 'Comercial 1',
                'comercial_2'       => 'Comercial 2',
                'tramites'          => 'Tramites',
                'talento_humano'    => 'Talento Humano',
                'operaciones_1'     => 'Operaciones 1',
                'operaciones_2'     => 'Operaciones 2',
                'facturacion'       => 'Facturacion',
                'tesoreria'         => 'Tesoreria',
                'propietario_vehiculo' => 'Propietario Vehiculo',
                'conductor'            => 'Conductor',
                'cliente'              => 'Cliente',
                'empresa'              => 'Empresa',
                ];
                foreach ($roles as $role_key => $role_name) {
                echo "<option value=\"$role_key\">$role_name</option>";
                }
                ?>
            </select>
        </div>
        <div class="wrap">
            <label for="password">Contraseña
                <input type="password" id="password" name="password" value="">
                <i class="icofont-eye-blocked"></i>
            </label>
            <a href="#" id="generate-password">Generar Contraseña</a>
            <div class="validapass">
                <h3 class="subtitle">Recuerda que tu contraseña deber tener:</h3>
                <div class="validaciones">
                    <ul>
                        <li>Entre 8 y 15 carácteres</li>
                        <li>Un número</li>
                        <li>Un carácter especial (*#=-_./!¿?)</li>
                        <li>Una mayúscula</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Campos adicionales generales -->
        <div class="wrap" id="extra-fields-container">
            <div class="wrap">
                <label for="user_state">Estado</label>
                <select id="user_state" name="user-state">
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
            </div>
            <div class="wrap wrap-2">
                <label for="user-cedula">Cédula</label>
                <input type="text" id="user-cedula" name="user-cedula">
            </div>
            <div class="wrap wrap-2">
                <label for="user-telefono">Teléfono</label>
                <input type="text" id="user-telefono" name="user-telefono">
            </div>
            <div class="wrap">
                <label for="user-direccion">Dirección</label>
                <input type="text" id="user-direccion" name="user-direccion">
            </div>
            <div class="wrap">
                <label for="user-foto">Foto Usuario</label>
                <input type="file" id="user-foto" name="user-foto" accept="image/*">
            </div>
        </div>
        <!-- Información de pago -->
        <div class="wrap" id="payment-fields-container" style="display:none;">
            <h4>Información de Pago</h4>
            <div class="wrap" id="repeater-payment-fields">
                <div class="wrap payment-row">
                    <div class="wrap">
                        <label>Nombre del Banco</label>
                        <input type="text" name="nombre_banco[]">
                    </div>
                    <div class="wrap">
                        <label>No. de Cuenta</label>
                        <input type="text" name="no_cuenta[]">
                    </div>
                    <div class="wrap">
                        <label>Tipo de Cuenta</label>
                        <select name="tipo_de_cuenta[]">
                            <option value="Ahorros">Ahorros</option>
                            <option value="Corriente">Corriente</option>
                        </select>
                    </div>
                </div>
            </div>
            <a href="#" class=""id="add-payment-row">Añadir información de pago</a>
        </div>
        <div class="wrap">
            <button class="button" type="submit" name="submit-user">Crear Usuario</button>
            <button class="button" type="button" id="cancelar-vehiculo-btn">Cancelar</button>
        </div>
    </form>
</div>