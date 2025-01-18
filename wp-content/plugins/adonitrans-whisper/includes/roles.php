<?php 

if (!defined('ABSPATH')) {
    exit;
}
function add_custom_user_roles() {
    // Obtener capacidades del rol base (por ejemplo, 'editor')
    $editor_role = get_role('editor');
    if (!$editor_role) {
        return; // Si no existe el rol 'editor', detener ejecución
    }
    
    $editor_capabilities = $editor_role->capabilities;

    // Arreglo de roles internos y sus capacidades adicionales
    $internal_roles = [
        'comercial_1'       => 'Comercial 1',
        'comercial_2'       => 'Comercial 2',
        'tramites'          => 'Tramites',
        'talento_humano'    => 'Talento Humano',
        'operaciones_1'     => 'Operaciones 1',
        'operaciones_2'     => 'Operaciones 2',
        'facturacion'       => 'Facturacion',
        'tesoreria'         => 'Tesoreria',
    ];

    // Arreglo de roles externos y sus capacidades adicionales
    $external_roles = [
        'propietario_vehiculo' => 'Propietario Vehiculo',
        'conductor'            => 'Conductor',
        'colaborador'          => 'Colaborador',
        'empresa'              => 'Empresa',
    ];

    // Función para crear roles personalizados
    $create_roles = function ($roles) use ($editor_capabilities) {
        foreach ($roles as $role_slug => $role_name) {
            // Verificar si el rol ya existe
            $role = get_role($role_slug);
            
            if (!$role) {
                // Crear rol con las capacidades del rol 'editor'
                $role = add_role($role_slug, $role_name, $editor_capabilities);
            }
            
            if ($role) {
                // Asegurarse de que tenga la capacidad de 'edit_posts'
                if (!$role->has_cap('edit_posts')) {
                    $role->add_cap('edit_posts');
                }

                // Agregar capacidad para duplicar posts si es necesario
                if ($role_slug === 'comercial_1' || $role_slug === 'comercial_2' || $role_slug === 'empresa') {
                    $role->add_cap('duplicate_posts'); // Asumiendo que tienes un plugin de duplicación de posts
                }
            }
        }
    };

    // Crear roles internos
    $create_roles($internal_roles);

    // Crear roles externos
    $create_roles($external_roles);
}
add_action('init', 'add_custom_user_roles');

// Bloquear acceso al dashboard y ocultar barra de administrador
function restrict_dashboard_access_and_admin_bar() {
    // Obtener el usuario actual
    $current_user = wp_get_current_user();

    // Roles que no deberían acceder al dashboard
    $restricted_roles = [
        'comercial_1',
        'comercial_2',
        'tramites',
        'talento_humano',
        'operaciones_1',
        'operaciones_2',
        'facturacion',
        'tesoreria',
        'propietario_vehiculo',
        'conductor',
        'colaborador',
        'empresa'
    ];

    // Si el usuario tiene un rol restringido, redirigir al home y ocultar la barra
    if (array_intersect($restricted_roles, $current_user->roles)) {
        // Redirigir si intenta acceder al dashboard
        if (is_admin() && !defined('DOING_AJAX')) {
            wp_safe_redirect(home_url());
            exit;
        }

        // Ocultar la barra de administrador
        add_filter('show_admin_bar', '__return_false');
    }
}
add_action('init', 'restrict_dashboard_access_and_admin_bar');
