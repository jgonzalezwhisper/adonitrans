<div id="franja">
    <div class="contimg intro-x">
        <a href="<?php echo get_home_url(); ?>">
            <img src="<?= URL_ADONITRANSPLUG ?>assets/images/adt-1.png" alt="<?= get_bloginfo( 'name' ) ?>">
        </a>
    </div>
    <div class="continfo">
        <div class="continfo_bread">
            <a href="#">ADONIGO</a> <span>></span> <span class="nombsecc">Panel</span>
        </div>
        <div class="continfo-right">
            <div class="continfo_noti">
                <i class="icofont-notification"></i>
            </div>
            <div class="continfo_user">
                <?php if (is_user_logged_in()):
                    $current_user = wp_get_current_user();
                    $user_id = $current_user->ID;
                    $user_key = 'user_' . $user_id;
                    $first_name = $current_user->user_firstname;
                    $last_name  = $current_user->user_lastname;
                    $foto_de_usuario = get_field('foto_de_usuario', $user_key);
                    $foto_de_usuario = $foto_de_usuario? $foto_de_usuario['url']: URL_ADONITRANSPLUG."assets/images/profile.jpg";
                ?>
                Hola, <?= $first_name.' '.$last_name ?>
                <?php endif ?>
                <img class="img_user" src="<?= $foto_de_usuario; ?>" alt="<?= $first_name ?>">
                <ul class="dropdown">
                    <li><a href="#">Soporte</a></li>
                    <li><a href="#">Contáctanos</a></li>
                    <?php
                    $pagina_iniciar_sesion = get_field('pagina_iniciar_sesion', 'option');
                    $redirect_url = get_permalink($pagina_iniciar_sesion);
                    ?>
                    <li><a href="<?= wp_logout_url($redirect_url); ?>"><i class="icofont-logout"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>