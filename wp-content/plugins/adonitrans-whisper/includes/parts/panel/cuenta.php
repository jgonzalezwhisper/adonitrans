<?php 
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');  
    if (!isset($_POST['action']) || empty($_POST['action'])) {
        exit('Acceso no autorizado');
    }
?>
<div class="tarjeta" id="wrap-cuenta">
	<div class="wrap-titulo">
		<h3 class="titulo">Gestionar Mis Datos</h3>
		<h4 class="subtitulo">Gestiona los datos de tu cuenta en la plataforma.</h4>
	</div>
	
	<div class="wrap-gestion-usuarios" >
		<?php
			$user_id = wp_get_current_user()->ID;
			$user_key = 'user_' . $user_id;
			$cedula_usuario = get_field('cedula_usuario', $user_key);
			$telefono = get_field('telefono', $user_key);
			$direccion = get_field('direccion', $user_key);
			$foto_de_usuario = get_field('foto_de_usuario', $user_key);
			$user_roles = $current_user->roles;
			$user_role = !empty($user_roles) ? $user_roles[0] : '';
		?>
		<form id="user-form" method="post" enctype="multipart/form-data" class="formplug" autocomplete="off" data-action="misdatos">

			<div class="column-1">
				<div class="wrap profile_photo">
					<img src="<?= URL_ADONITRANSPLUG ?>assets/images/profile.jpg" alt="<?= get_bloginfo( 'name' ) ?>">
					<label for="user-foto">Cambiar<i class="icofont-camera"></i></label>
					<input style="display:none;" type="file" id="user-foto" name="user-foto" accept="image/*" value="<?= $foto_de_usuario; ?>">
				</div>
				<div class="wrap profile_info">
					<input type="hidden" id="user-id" name="user-id" value="<?= esc_attr($user_id); ?>">
					<input type="hidden" name="select_rolesusuario" value="<?= esc_attr($user_role); ?>">
					<?php wp_nonce_field('create_user_action', 'create_user_nonce'); ?>
					<div class="wrap">
						<label for="first_name">Nombres</label>
						<input type="text" id="first_name" name="first_name" value="<?= esc_attr(get_user_meta($user_id, 'first_name', true)); ?>">

						<label for="last_name">Apellidos</label>
						<input type="text" id="last_name" name="last_name" value="<?= esc_attr(get_user_meta($user_id, 'last_name', true)); ?>">
					</div>
				</div>
			</div>

			<div class="column-2">
				<h3>Datos de Contacto</h3>
				<div class="wrap">
					<label for="user_email"> Correo</label>
					<input type="email" id="user_email" name="user_email" value="<?= esc_attr($current_user->user_email); ?>">

					<label for="user-cedula">Cédula</label>
					<input type="text" id="user-cedula" name="user-cedula" value="<?= $cedula_usuario; ?>">
					
					<label for="user-telefono">Teléfono</label>
					<input type="text" id="user-telefono" name="user-telefono" value="<?= $telefono; ?>">

					<label for="user-direccion">Dirección</label>
					<input type="text" id="user-direccion" name="user-direccion" value="<?= $direccion; ?>">
				</div>
			</div>
			<div class="column-2 wrap">
				<h3>Actualizar Contraseña</h3>
				<div class="cont_password">
					<label for="password">Contraseña
						<input type="password" id="password" name="password" value="">
						<i class="icofont-eye-blocked"></i>
					</label>
					<a href="#" id="generate-password">Generar Contraseña</a>
					<div class="validapass">
						<h4 class="subtitle">Recuerda que tu contraseña deber tener:</h4>
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
				<button class="button" type="submit" name="submit-user">Actualizar Usuario</button>
			</div>
			
		</form>
	</div>
</div>