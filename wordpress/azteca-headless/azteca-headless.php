<?php
/**
 * Plugin Name: Azteca Headless
 * Description: Proyectos y servicios/parcelas de Constructora Azteca para el sitio Astro (WordPress headless). Incluye API REST e importador del sitio anterior.
 * Version: 1.0.0
 * Author: DiseñoPaginas.cl
 * Text Domain: azteca-headless
 */
if (!defined('ABSPATH')) exit;

define('AZTECA_VER', '1.0.0');
define('AZTECA_DIR', plugin_dir_path(__FILE__));
define('AZTECA_URL', plugin_dir_url(__FILE__));

/* ───────────── Imágenes ───────────── */
add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails');
    add_image_size('azteca_hero', 1920, 1280, false);
    add_image_size('azteca_card', 640, 480, true);
});

/* ───────────── Tipos de contenido ───────────── */
add_action('init', function () {
    $common = ['public' => false, 'show_ui' => true, 'show_in_menu' => true, 'show_in_rest' => true, 'has_archive' => false, 'publicly_queryable' => false, 'rewrite' => false];
    register_post_type('proyecto', $common + [
        'labels' => ['name' => 'Proyectos', 'singular_name' => 'Proyecto', 'add_new' => 'Añadir proyecto', 'add_new_item' => 'Añadir nuevo proyecto', 'edit_item' => 'Editar proyecto', 'all_items' => 'Todos los proyectos', 'menu_name' => 'Proyectos', 'featured_image' => 'Imagen principal', 'set_featured_image' => 'Elegir imagen principal', 'remove_featured_image' => 'Quitar imagen principal', 'use_featured_image' => 'Usar como imagen principal'],
        'menu_icon' => 'dashicons-building', 'menu_position' => 5,
        'supports' => ['title', 'thumbnail', 'page-attributes'],
    ]);
    register_post_type('servicio', $common + [
        'labels' => ['name' => 'Servicios y parcelas', 'singular_name' => 'Servicio', 'add_new' => 'Añadir servicio', 'add_new_item' => 'Añadir nuevo servicio o parcela', 'edit_item' => 'Editar servicio', 'all_items' => 'Todos los servicios', 'menu_name' => 'Servicios', 'featured_image' => 'Imagen principal', 'set_featured_image' => 'Elegir imagen principal', 'remove_featured_image' => 'Quitar imagen principal', 'use_featured_image' => 'Usar como imagen principal'],
        'menu_icon' => 'dashicons-hammer', 'menu_position' => 6,
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
    ]);
});

/* ───────────── Cajas de campos ───────────── */
function azteca_icons() {
    return ['house' => 'Casa', 'compass' => 'Diseño / arquitectura', 'land' => 'Terreno / parcela', 'water' => 'Sanitario (agua)', 'bolt' => 'Electricidad', 'file' => 'Documentos / regularización', 'survey' => 'Topografía'];
}

add_action('add_meta_boxes', function () {
    add_meta_box('azteca_proyecto', 'Datos del proyecto', 'azteca_box_proyecto', 'proyecto', 'normal', 'high');
    add_meta_box('azteca_galeria', 'Galería de imágenes', 'azteca_box_galeria', ['proyecto', 'servicio'], 'normal', 'default');
    add_meta_box('azteca_servicio', 'Datos del servicio', 'azteca_box_servicio', 'servicio', 'side', 'high');
});

function azteca_field($post, $key, $label, $type = 'text', $help = '') {
    $v = get_post_meta($post->ID, $key, true);
    echo '<p><label for="' . esc_attr($key) . '"><strong>' . esc_html($label) . '</strong></label><br>';
    if ($type === 'textarea') echo '<textarea class="widefat" rows="6" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">' . esc_textarea($v) . '</textarea>';
    else echo '<input class="widefat" type="' . esc_attr($type) . '" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($v) . '">';
    if ($help) echo '<span class="description">' . esc_html($help) . '</span>';
    echo '</p>';
}
function azteca_box_proyecto($post) {
    wp_nonce_field('azteca_save', 'azteca_nonce');
    azteca_field($post, '_az_superficie', 'Superficie', 'text', 'Ejemplo: 91,69 m²');
    azteca_field($post, '_az_ubicacion', 'Ubicación', 'text', 'Ejemplo: Sector urbano - Ancud');
    azteca_field($post, '_az_caracteristicas', 'Características', 'textarea', 'Una por línea. Ejemplo: Casa de dos pisos / 3 dormitorios / 2 baños');
    echo '<p class="description">La foto grande del proyecto se elige en «Imagen principal» (columna derecha).</p>';
}
function azteca_box_servicio($post) {
    wp_nonce_field('azteca_save', 'azteca_nonce');
    $kind = get_post_meta($post->ID, '_az_tipo', true) ?: 'servicio';
    $icon = get_post_meta($post->ID, '_az_icono', true) ?: 'house';
    echo '<p><label><strong>Tipo</strong></label><br><select name="_az_tipo" class="widefat"><option value="servicio"' . selected($kind, 'servicio', false) . '>Servicio</option><option value="parcela"' . selected($kind, 'parcela', false) . '>Parcela en venta</option></select></p>';
    echo '<p><label><strong>Icono</strong></label><br><select name="_az_icono" class="widefat">';
    foreach (azteca_icons() as $k => $l) echo '<option value="' . esc_attr($k) . '"' . selected($icon, $k, false) . '>' . esc_html($l) . '</option>';
    echo '</select></p>';
    azteca_field($post, '_az_resumen', 'Resumen (tarjeta)', 'textarea', 'Texto corto que aparece en las tarjetas.');
    azteca_field($post, '_az_precio', 'Precio desde (CLP)', 'number', 'Solo para parcelas. Ejemplo: 30000000');
}
function azteca_box_galeria($post) {
    $ids = array_filter(array_map('intval', explode(',', (string) get_post_meta($post->ID, '_az_galeria', true))));
    echo '<div class="az-gal" data-input="_az_galeria"><ul class="az-gal__list">';
    foreach ($ids as $id) {
        $t = wp_get_attachment_image_url($id, 'thumbnail');
        if ($t) echo '<li data-id="' . $id . '"><img src="' . esc_url($t) . '" alt=""><button type="button" class="az-gal__rm" aria-label="Quitar">×</button></li>';
    }
    echo '</ul><input type="hidden" id="_az_galeria" name="_az_galeria" value="' . esc_attr(implode(',', $ids)) . '">';
    echo '<p><button type="button" class="button button-primary az-gal__add">Añadir imágenes</button> <span class="description">Puedes subir varias a la vez y arrastrarlas para ordenar.</span></p></div>';
}
add_action('save_post', function ($id) {
    if (!isset($_POST['azteca_nonce']) || !wp_verify_nonce($_POST['azteca_nonce'], 'azteca_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $id)) return;
    $text = ['_az_superficie', '_az_ubicacion', '_az_tipo', '_az_icono'];
    $area = ['_az_caracteristicas', '_az_resumen'];
    foreach ($text as $k) if (isset($_POST[$k])) update_post_meta($id, $k, sanitize_text_field(wp_unslash($_POST[$k])));
    foreach ($area as $k) if (isset($_POST[$k])) update_post_meta($id, $k, sanitize_textarea_field(wp_unslash($_POST[$k])));
    if (isset($_POST['_az_precio'])) update_post_meta($id, '_az_precio', absint($_POST['_az_precio']));
    if (isset($_POST['_az_galeria'])) update_post_meta($id, '_az_galeria', implode(',', array_filter(array_map('absint', explode(',', wp_unslash($_POST['_az_galeria']))))));
});

add_action('admin_enqueue_scripts', function ($hook) {
    $screen = get_current_screen();
    if (!$screen) return;
    $is_edit = in_array($hook, ['post.php', 'post-new.php'], true) && in_array($screen->post_type, ['proyecto', 'servicio'], true);
    if ($is_edit) wp_enqueue_media();
    if ($is_edit || strpos($hook, 'azteca') !== false) {
        wp_enqueue_style('azteca-admin', AZTECA_URL . 'assets/admin.css', [], AZTECA_VER);
        wp_enqueue_script('azteca-admin', AZTECA_URL . 'assets/admin.js', ['jquery', 'jquery-ui-sortable'], AZTECA_VER, true);
        wp_localize_script('azteca-admin', 'AZTECA', ['ajax' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('azteca_import')]);
    }
});

/* Columnas útiles en el listado */
add_filter('manage_proyecto_posts_columns', fn($c) => ['cb' => $c['cb'], 'az_thumb' => 'Imagen', 'title' => 'Proyecto', 'az_sup' => 'Superficie', 'az_ubi' => 'Ubicación', 'date' => 'Fecha']);
add_filter('manage_servicio_posts_columns', fn($c) => ['cb' => $c['cb'], 'az_thumb' => 'Imagen', 'title' => 'Servicio', 'az_tipo' => 'Tipo', 'date' => 'Fecha']);
foreach (['proyecto', 'servicio'] as $pt) {
    add_action("manage_{$pt}_posts_custom_column", function ($col, $id) {
        if ($col === 'az_thumb') echo get_the_post_thumbnail($id, [80, 60]);
        if ($col === 'az_sup') echo esc_html(get_post_meta($id, '_az_superficie', true));
        if ($col === 'az_ubi') echo esc_html(get_post_meta($id, '_az_ubicacion', true));
        if ($col === 'az_tipo') echo esc_html(get_post_meta($id, '_az_tipo', true) === 'parcela' ? 'Parcela' : 'Servicio');
    }, 10, 2);
}

/* ───────────── Ajustes ───────────── */
function azteca_settings_fields() {
    return ['deploy_hook' => ['Deploy hook (opcional): dirección que reconstruye el sitio Astro cada vez que se publica o edita un proyecto o servicio', '']];
}
add_action('admin_menu', function () {
    add_menu_page('Azteca', 'Azteca', 'manage_options', 'azteca', 'azteca_import_page', 'dashicons-admin-generic', 8);
    add_submenu_page('azteca', 'Importar sitio anterior', 'Importar sitio anterior', 'manage_options', 'azteca', 'azteca_import_page');
    add_submenu_page('azteca', 'Reconstruir sitio', 'Reconstruir sitio', 'manage_options', 'azteca-deploy', 'azteca_settings_page');
});
add_action('admin_init', function () {
    foreach (azteca_settings_fields() as $k => $_) register_setting('azteca', 'azteca_' . $k, ['sanitize_callback' => 'esc_url_raw']);
});
function azteca_opt($k) { $f = azteca_settings_fields(); return get_option('azteca_' . $k, $f[$k][1] ?? ''); }
function azteca_settings_page() {
    echo '<div class="wrap"><h1>Reconstruir sitio</h1><form method="post" action="options.php">';
    settings_fields('azteca');
    echo '<table class="form-table">';
    foreach (azteca_settings_fields() as $k => [$label, $def]) {
        echo '<tr><th><label for="azteca_' . esc_attr($k) . '">' . esc_html($label) . '</label></th><td><input class="regular-text" type="text" id="azteca_' . esc_attr($k) . '" name="azteca_' . esc_attr($k) . '" value="' . esc_attr(azteca_opt($k)) . '"></td></tr>';
    }
    echo '</table>'; submit_button(); echo '</form></div>';
}

/* ───────────── Datos → API ───────────── */
function azteca_img($id, $alt = '') {
    if (!$id) return null;
    $full = wp_get_attachment_image_src($id, 'azteca_hero');
    if (!$full) return null;
    $small = wp_get_attachment_image_src($id, 'azteca_card');
    return ['src' => $full[0], 'small' => $small ? $small[0] : $full[0], 'width' => (int) $full[1], 'height' => (int) $full[2], 'alt' => $alt ?: get_post_meta($id, '_wp_attachment_image_alt', true)];
}
function azteca_gallery($post_id, $title) {
    $out = [];
    foreach (array_filter(array_map('intval', explode(',', (string) get_post_meta($post_id, '_az_galeria', true)))) as $i => $id) {
        $im = azteca_img($id, $title . ' – imagen ' . ($i + 1)); if ($im) $out[] = $im;
    }
    return $out;
}
function azteca_lines($text) { return implode("\n", array_filter(array_map('trim', preg_split('/\R/', (string) $text)))); }

function azteca_content() {
    $q = fn($pt) => get_posts(['post_type' => $pt, 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC']]);
    $projects = [];
    foreach ($q('proyecto') as $p) {
        $hero = azteca_img(get_post_thumbnail_id($p), $p->post_title);
        $projects[] = ['slug' => $p->post_name, 'title' => $p->post_title, 'surface' => get_post_meta($p->ID, '_az_superficie', true) ?: null, 'description' => azteca_lines(get_post_meta($p->ID, '_az_caracteristicas', true)), 'location' => get_post_meta($p->ID, '_az_ubicacion', true), 'hero' => $hero, 'card' => $hero, 'gallery' => azteca_gallery($p->ID, $p->post_title)];
    }
    $services = [];
    foreach ($q('servicio') as $p) {
        $body = azteca_lines(wp_strip_all_tags(preg_replace('#</p>|<br\s*/?>#i', "\n", $p->post_content)));
        $resumen = get_post_meta($p->ID, '_az_resumen', true) ?: wp_trim_words($body, 26, '…');
        $precio = (int) get_post_meta($p->ID, '_az_precio', true);
        $services[] = ['slug' => $p->post_name, 'title' => $p->post_title, 'kind' => get_post_meta($p->ID, '_az_tipo', true) ?: 'servicio', 'icon' => get_post_meta($p->ID, '_az_icono', true) ?: 'house', 'excerpt' => $resumen, 'description' => $body, 'price' => $precio ?: null, 'hero' => azteca_img(get_post_thumbnail_id($p), $p->post_title), 'gallery' => azteca_gallery($p->ID, $p->post_title)];
    }
    return ['projects' => $projects, 'services' => $services];
}

add_action('rest_api_init', function () {
    register_rest_route('azteca/v1', '/content', ['methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => fn() => rest_ensure_response(azteca_content())]);
});

/* Reconstruir el sitio (deploy hook) al publicar cambios */
add_action('transition_post_status', function ($new, $old, $post) {
    if (!in_array($post->post_type, ['proyecto', 'servicio'], true)) return;
    if ($new !== 'publish' && $old !== 'publish') return;
    $hook = azteca_opt('deploy_hook');
    if ($hook && !get_transient('azteca_deploy_lock')) { set_transient('azteca_deploy_lock', 1, 60); wp_remote_post($hook, ['timeout' => 5, 'blocking' => false]); }
}, 10, 3);

/* ───────────── Importador del sitio anterior ───────────── */
function azteca_import_data() {
    static $d = null;
    if ($d === null) $d = json_decode(file_get_contents(AZTECA_DIR . 'import-data.json'), true) ?: ['projects' => [], 'services' => []];
    return $d;
}
function azteca_import_page() {
    $d = azteca_import_data();
    $total = count($d['projects']) + count($d['services']);
    echo '<div class="wrap"><h1>Importar sitio anterior</h1>';
    echo '<p>Copia a este WordPress los <strong>' . count($d['projects']) . ' proyectos</strong>, <strong>' . count($d['services']) . ' servicios/parcelas</strong> del sitio anterior, descargando sus fotos a la biblioteca de medios. Puedes repetirlo sin miedo: lo que ya existe se omite.</p>';
    echo '<p><button class="button button-primary button-hero" id="az-import" data-total="' . (int) $total . '">Importar contenido</button></p>';
    echo '<div id="az-progress" style="display:none;max-width:640px"><div style="background:#ddd;height:14px"><div id="az-bar" style="background:#F5A623;height:14px;width:0"></div></div><p id="az-log" style="font-family:monospace"></p></div></div>';
}
add_action('wp_ajax_azteca_import_step', function () {
    check_ajax_referer('azteca_import', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Sin permiso', 403);
    @set_time_limit(300);
    require_once ABSPATH . 'wp-admin/includes/media.php'; require_once ABSPATH . 'wp-admin/includes/file.php'; require_once ABSPATH . 'wp-admin/includes/image.php';
    $d = azteca_import_data(); $i = (int) ($_POST['i'] ?? 0);
    $items = [];
    foreach ($d['services'] as $x) $items[] = ['servicio', $x];
    foreach (array_reverse($d['projects']) as $x) $items[] = ['proyecto', $x]; // los más nuevos quedan con fecha más reciente
    if ($i >= count($items)) wp_send_json_success(['done' => true]);
    [$pt, $x] = $items[$i];
    $slug = sanitize_title($x['slug']);
    $exists = get_posts(['post_type' => $pt, 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1]);
    if ($exists) wp_send_json_success(['next' => $i + 1, 'label' => $x['title'] . ' (ya existía)']);
    $sideload = function ($url, $post_id) {
        if (!$url) return 0;
        $id = media_sideload_image($url, $post_id, null, 'id');
        return is_wp_error($id) ? 0 : (int) $id;
    };
    $post_id = wp_insert_post(['post_type' => $pt, 'post_status' => 'publish', 'post_title' => $x['title'], 'post_name' => $slug, 'menu_order' => (int) ($x['order'] ?? $i), 'post_content' => $pt === 'servicio' ? implode("\n\n", array_map(fn($l) => '<!-- wp:paragraph --><p>' . esc_html($l) . '</p><!-- /wp:paragraph -->', array_filter(explode("\n", $x['description'] ?? '')))) : ''], true);
    if (is_wp_error($post_id)) wp_send_json_error($post_id->get_error_message());
    $m = fn($k, $v) => update_post_meta($post_id, $k, $v);
    if ($pt === 'proyecto') { $m('_az_superficie', $x['surface'] ?? ''); $m('_az_ubicacion', $x['location'] ?? ''); $m('_az_caracteristicas', $x['description'] ?? ''); }
    if ($pt === 'servicio') { $m('_az_tipo', $x['kind']); $m('_az_icono', $x['icon']); $m('_az_resumen', $x['excerpt'] ?? ''); if (!empty($x['price'])) $m('_az_precio', (int) $x['price']); }
    $main = $sideload($x['image'] ?? '', $post_id); if ($main) set_post_thumbnail($post_id, $main);
    $ids = [];
    foreach (($x['gallery'] ?? []) as $u) { $g = $sideload($u, $post_id); if ($g) $ids[] = $g; }
    if ($ids) $m('_az_galeria', implode(',', $ids));
    wp_send_json_success(['next' => $i + 1, 'label' => $x['title'] . ' (' . count($ids) . ' fotos)']);
});
