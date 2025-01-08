<?php
if (!defined('ABSPATH')) {
    exit;
} 
// Crear un shortcode para el formulario de login con correo electrónico y nonce
function custom_login_form_shortcode() { 
    ob_start();
    ?>
    <div class="wrap-login">
        <h3 class="titulo">INICIA SESIÓN</h3>
        <form action="<?= get_the_permalink() ?>" id="custom-login-form" class="formplug" method="POST">
            <?php wp_nonce_field('custom_user_login', 'custom_login_nonce'); ?>
            <label for="user_email">
                Correo Electrónico
                <input type="email" name="user_email" id="user_email" placeholder="Correo electrónico" required autocomplete="off">
            </label>
            <label for="user_password">
                Contraseña
                <input type="password" name="user_password" id="user_password" placeholder="Contraseña" required autocomplete="off">
                <i class="icofont-eye-blocked"></i>
            </label>       
            <button class="button" type="submit">Iniciar sesión</button>
            <br>            
            <!-- Campo de token, inicialmente oculto -->
            <div id="token-section" style="display: none;">
                <label for="user_token">Ingresa el token recibido
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
