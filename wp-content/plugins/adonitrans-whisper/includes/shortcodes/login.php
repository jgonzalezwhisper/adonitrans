<?php
if (!defined('ABSPATH')) {
    exit;
} 
// Crear un shortcode para el formulario de login con correo electrónico y nonce
function custom_login_form_shortcode() { 
    ob_start();
    ?>
    <div class="wrap-login">
        <h3 class="titulo">Iniciar Sesión</h3>
        <form action="<?= get_the_permalink() ?>" id="custom-login-form" class="formplug" method="POST">
            <?php wp_nonce_field('custom_user_login', 'custom_login_nonce'); ?>
            <label for="user_email">
                <p class="title_form_login">Usuario</p>
                <input type="email" name="user_email" id="user_email" placeholder="" required autocomplete="off">
            </label>
            <label for="user_password">
                <p class="title_form_login">Contraseña</p>
                <input type="password" name="user_password" id="user_password" placeholder="" required autocomplete="off">
                <i class="icofont-eye-blocked"></i>
            </label>       
            <button class="button" type="submit">Enviar</button>
            <br>            
            <!-- Campo de token, inicialmente oculto -->
            <div id="token-section" style="display: none;">
                <label for="user_token"><p class="title_form_login" style="text-align: center;">Ingresa el token recibido</p>
                    <input type="text" name="user_token" id="user_token" placeholder="Token" autocomplete="off">
                </label>           
                <button class="button" type="submit" id="submit-token">Verificar Token</button>
            </div>
        </form>
        <div id="login-result"></div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('adonitrans_login', 'custom_login_form_shortcode');
