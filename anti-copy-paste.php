<?php
/**
 * Plugin Name:       Anti Copy-Paste – Aprendiz de SEO
 * Plugin URI:        https://aprendizdeseo.top/anti-copy-paste/
 * Description:       Bloquea la selección de texto (user-select: none) para dificultar el copiado. Permite excepciones por rol, tipo de contenido y selectores.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Aprendiz De Seo
 * Author URI:        https://aprendizdeseo.top
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       anti-copy-paste
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) exit;

/**
 * Opciones por defecto.
 */
function acpas_default_options(): array {
    return [
        'enabled'               => true,
        'exclude_roles'         => ['administrator', 'editor'],
        'exclude_post_types'    => ['attachment'],
        'extra_allow_selectors' => "input, textarea, select, [contenteditable='true'], pre, code, .wp-block-code",
        'block_contextmenu'     => false,
        'block_copy_event'      => false,
    ];
}

/**
 * Obtiene opciones fusionadas con defaults.
 */
function acpas_get_options(): array {
    $saved = (array) get_option('acpas_options', []);
    return array_merge(acpas_default_options(), $saved);
}

/**
 * Página de ajustes.
 */
add_action('admin_menu', function () {
    add_options_page(
        __('Anti Copy-Paste', 'anti-copy-paste'),
        __('Anti Copy-Paste', 'anti-copy-paste'),
        'manage_options',
        'acpas-settings',
        'acpas_render_settings_page'
    );
});

/**
 * Registro de ajustes.
 */
add_action('admin_init', function () {
    register_setting('acpas_settings_group', 'acpas_options', [
        'type'              => 'array',
        'sanitize_callback' => 'acpas_sanitize_options',
        'default'           => acpas_default_options(),
    ]);

    add_settings_section('acpas_main', __('Ajustes generales', 'anti-copy-paste'), function () {
        echo '<p>' . esc_html__('Controla dónde se aplica user-select:none y define excepciones.', 'anti-copy-paste') . '</p>';
    }, 'acpas-settings');

    // Activado global
    add_settings_field('acpas_enabled', __('Activar protección global', 'anti-copy-paste'), function () {
        $opt = acpas_get_options();
        echo '<label><input type="checkbox" name="acpas_options[enabled]" value="1" ' . checked(true, (bool) $opt['enabled'], false) . '> ' . esc_html__('Activar', 'anti-copy-paste') . '</label>';
    }, 'acpas-settings', 'acpas_main');

    // Excluir roles
    add_settings_field('acpas_exclude_roles', __('Excluir roles', 'anti-copy-paste'), function () {
        $opt = acpas_get_options();
        global $wp_roles;
        $roles = $wp_roles->roles ?? [];
        foreach ($roles as $role_key => $role_data) {
            $checked = in_array($role_key, (array) $opt['exclude_roles'], true);
            echo '<label style="display:inline-block;margin-right:12px"><input type="checkbox" name="acpas_options[exclude_roles][]" value="' . esc_attr($role_key) . '" ' . checked(true, $checked, false) . '> ' . esc_html(translate_user_role($role_data['name'])) . '</label>';
        }
    }, 'acpas-settings', 'acpas_main');

    // Excluir CPT
    add_settings_field('acpas_exclude_post_types', __('Excluir tipos de contenido', 'anti-copy-paste'), function () {
        $opt = acpas_get_options();
        $post_types = get_post_types(['public' => true], 'objects');
        foreach ($post_types as $pt) {
            $checked = in_array($pt->name, (array) $opt['exclude_post_types'], true);
            echo '<label style="display:inline-block;margin-right:12px"><input type="checkbox" name="acpas_options[exclude_post_types][]" value="' . esc_attr($pt->name) . '" ' . checked(true, $checked, false) . '> ' . esc_html($pt->labels->singular_name) . '</label>';
        }
    }, 'acpas-settings', 'acpas_main');

    // Selectores permitidos
    add_settings_field('acpas_extra_allow_selectors', __('Selectores con selección permitida', 'anti-copy-paste'), function () {
        $opt = acpas_get_options();
        echo '<textarea name="acpas_options[extra_allow_selectors]" rows="3" style="width:100%">' . esc_textarea($opt['extra_allow_selectors']) . '</textarea>';
        echo '<p class="description">' . esc_html__('Separados por comas. Ej.: input, textarea, select, [contenteditable="true"], pre, code, .wp-block-code', 'anti-copy-paste') . '</p>';
    }, 'acpas-settings', 'acpas_main');

    // Bloquear clic derecho
    add_settings_field('acpas_block_contextmenu', __('Bloquear clic derecho', 'anti-copy-paste'), function () {
        $opt = acpas_get_options();
        echo '<label><input type="checkbox" name="acpas_options[block_contextmenu]" value="1" ' . checked(true, (bool) $opt['block_contextmenu'], false) . '> ' . esc_html__('Desactivar menú contextual (opcional)', 'anti-copy-paste') . '</label>';
    }, 'acpas-settings', 'acpas_main');

    // Bloquear evento copiar
    add_settings_field('acpas_block_copy_event', __('Bloquear evento copiar', 'anti-copy-paste'), function () {
        $opt = acpas_get_options();
        echo '<label><input type="checkbox" name="acpas_options[block_copy_event]" value="1" ' . checked(true, (bool) $opt['block_copy_event'], false) . '> ' . esc_html__('Intentar bloquear Ctrl/Cmd+C (opcional)', 'anti-copy-paste') . '</label>';
    }, 'acpas-settings', 'acpas_main');
});

/**
 * Sanitización de opciones.
 */
function acpas_sanitize_options($input) {
    $defaults = acpas_default_options();
    $clean = [];

    $clean['enabled'] = !empty($input['enabled']);
    $clean['exclude_roles'] = array_values(array_filter(array_map('sanitize_key', $input['exclude_roles'] ?? [])));
    $clean['exclude_post_types'] = array_values(array_filter(array_map('sanitize_key', $input['exclude_post_types'] ?? [])));

    $sel = isset($input['extra_allow_selectors']) ? (string) $input['extra_allow_selectors'] : $defaults['extra_allow_selectors'];
    $clean['extra_allow_selectors'] = wp_kses_post($sel);

    $clean['block_contextmenu'] = !empty($input['block_contextmenu']);
    $clean['block_copy_event']   = !empty($input['block_copy_event']);

    return array_merge($defaults, $clean);
}

/**
 * Render de la página de ajustes.
 */
function acpas_render_settings_page() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Anti Copy-Paste – Aprendiz de SEO', 'anti-copy-paste'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('acpas_settings_group');
            do_settings_sections('acpas-settings');
            submit_button();
            ?>
        </form>
        <p><em><?php esc_html_e('Nota: esta protección es disuasoria; usuarios avanzados pueden desactivarla con herramientas del navegador.', 'anti-copy-paste'); ?></em></p>
    </div>
    <?php
}

/**
 * Añade clase al body si procede.
 */
add_filter('body_class', function ($classes) {
    if (is_admin()) return $classes;

    $opt = acpas_get_options();
    if (!$opt['enabled']) return $classes;

    if (is_user_logged_in() && !empty($opt['exclude_roles'])) {
        foreach ($opt['exclude_roles'] as $role) {
            if (current_user_can($role)) {
                return $classes;
            }
        }
    }

    if (is_singular()) {
        $post = get_queried_object();
        if (!empty($post->post_type) && in_array($post->post_type, (array) $opt['exclude_post_types'], true)) {
            return $classes;
        }
    }

    $classes[] = 'acpas-no-select';
    return $classes;
});

/**
 * Inyecta CSS y JS opcional.
 */
add_action('wp_head', function () {
    if (is_admin()) return;
    $opt = acpas_get_options();

    if (!in_array('acpas-no-select', get_body_class(), true)) return;

    $allow = trim($opt['extra_allow_selectors']);
    if ($allow === '') {
        $allow = 'input, textarea, select, [contenteditable="true"]';
    }
    ?>
    <style id="acpas-css">
    .acpas-no-select, .acpas-no-select * {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    .acpas-no-select <?php echo esc_html($allow); ?> {
        -webkit-user-select: text;
        -moz-user-select: text;
        -ms-user-select: text;
        user-select: text;
    }
    </style>
    <?php if (!empty($opt['block_contextmenu'])): ?>
        <script id="acpas-contextmenu">
        document.addEventListener('contextmenu', function(e){
            const allowed = document.querySelectorAll('<?php echo esc_js($allow); ?>');
            let allow = false;
            allowed.forEach(function (el) {
                if (el.contains(e.target)) allow = true;
            });
            if (!allow) e.preventDefault();
        }, {passive:false});
        </script>
    <?php endif; ?>
    <?php if (!empty($opt['block_copy_event'])): ?>
        <script id="acpas-copy">
        document.addEventListener('copy', function(e){
            const allowed = document.querySelectorAll('<?php echo esc_js($allow); ?>');
            let allow = false;
            allowed.forEach(function (el) {
                if (el.contains(window.getSelection()?.anchorNode)) allow = true;
            });
            if (!allow) {
                e.preventDefault();
                try { navigator.clipboard && navigator.clipboard.writeText(''); } catch(e){}
            }
        });
        </script>
    <?php endif;
});

/**
 * Enlace rápido a ajustes desde Plugins.
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $url = admin_url('options-general.php?page=acpas-settings');
    $links[] = '<a href="' . esc_url($url) . '">' . esc_html__('Ajustes', 'anti-copy-paste') . '</a>';
    return $links;
});
