<?php
/*
Plugin Name: LLMs.txt Generator
Description: Genera un archivo llms.txt optimizado para modelos de lenguaje con opciones de gestión, detección de plugins SEO y campos personalizables.
Version: 1.9
Author: Jesús Fernández Ávila
GitHub: https://github.com/JesusFAvila
*/

if (!defined('ABSPATH')) {
    exit;
}

// Función para registrar mensajes de depuración
function llms_txt_log($message) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("[LLMs.txt] " . $message . " - " . date('Y-m-d H:i:s'));
    }
}

// Detectar plugin SEO activo y extraer datos
function llms_txt_detect_seo_plugin() {
    $seo_plugins = [
        'yoast' => [
            'name' => 'Yoast SEO',
            'active' => defined('WPSEO_VERSION'),
            'title' => '_yoast_wpseo_title',
            'sitemap' => home_url('/sitemap_index.xml'),
        ],
        'aioseo' => [
            'name' => 'All in One SEO',
            'active' => function_exists('aioseo'),
            'title' => '_aioseo_title',
            'sitemap' => home_url('/sitemap.xml'),
        ],
        'rankmath' => [
            'name' => 'Rank Math',
            'active' => function_exists('rank_math'),
            'title' => 'rank_math_title',
            'sitemap' => home_url('/sitemap_index.xml'),
        ],
        'seopress' => [
            'name' => 'SEOPress',
            'active' => defined('SEOPRESS_VERSION'),
            'title' => '_seopress_titles_title',
            'sitemap' => home_url('/sitemap.xml'),
        ],
    ];

    foreach ($seo_plugins as $key => $plugin) {
        if ($plugin['active']) {
            llms_txt_log("Plugin SEO detectado: " . $plugin['name']);
            return $plugin;
        }
    }
    llms_txt_log("No se detectó ningún plugin SEO conocido.");
    return null;
}

// Función para generar el archivo llms.txt
function generate_llms_txt($force_overwrite = false) {
    $file_path = ABSPATH . 'llms.txt';
    $overwrite = get_option('llms_txt_overwrite', 'yes');
    $seo_plugin = llms_txt_detect_seo_plugin();

    if (!$force_overwrite && $overwrite === 'no' && file_exists($file_path)) {
        llms_txt_log("No se sobrescribe automáticamente porque la opción está en 'no'.");
        return false;
    }

    // Obtener valores personalizados
    $custom_name = get_option('llms_txt_name', get_bloginfo('name'));
    $custom_description = get_option('llms_txt_description', get_bloginfo('description'));
    $custom_keywords = get_option('llms_txt_keywords', 'programación, WordPress, IA, desarrollo web');
    $custom_language = get_option('llms_txt_language', get_bloginfo('language'));
    $custom_address = get_option('llms_txt_address', 'Madrid, España');
    $custom_location = get_option('llms_txt_location', 'Madrid, España');

    $content = "# " . esc_html($custom_name) . "\n";
    if ($custom_description) $content .= "> " . esc_html($custom_description) . "\n";
    $content .= "> Última generación: " . date('Y-m-d H:i:s') . "\n\n";

    $content .= "## Acerca de\n";
    if ($custom_name) $content .= "- Nombre: " . esc_html($custom_name) . "\n";
    if ($custom_description) $content .= "- Descripción: " . esc_html($custom_description) . "\n";
    if ($custom_keywords) $content .= "- Palabras clave: " . esc_html($custom_keywords) . "\n";
    if ($custom_language) $content .= "- Idioma: " . esc_html($custom_language) . "\n";
    if ($custom_address) $content .= "- Dirección: " . esc_html($custom_address) . "\n";
    if ($custom_location) $content .= "- Ubicación: " . esc_html($custom_location) . "\n";
    $content .= "- URL: " . home_url() . "\n\n";

    // Páginas (sin límite)
    $pages = get_pages(array('post_status' => 'publish'));
    if (!empty($pages)) {
        $content .= "## Páginas\n";
        foreach ($pages as $page) {
            $title = $seo_plugin && $seo_plugin['title'] ? get_post_meta($page->ID, $seo_plugin['title'], true) : $page->post_title;
            $title = $title ?: $page->post_title;
            $date = get_the_date('Y-m-d', $page->ID);
            $content .= "- [$title](" . get_permalink($page->ID) . ") - $date\n";
        }
        $content .= "\n";
    }

    // Posts (sin límite)
    $posts = get_posts(array('numberposts' => -1, 'post_status' => 'publish'));
    if (!empty($posts)) {
        $content .= "## Posts\n";
        foreach ($posts as $post) {
            $title = $seo_plugin && $seo_plugin['title'] ? get_post_meta($post->ID, $seo_plugin['title'], true) : $post->post_title;
            $title = $title ?: $post->post_title;
            $date = get_the_date('Y-m-d', $post->ID);
            $content .= "- [$title](" . get_permalink($post->ID) . ") - $date\n";
        }
        $content .= "\n";
    }

    // Categorías de producto (límite de 5)
    if (class_exists('WooCommerce')) {
        $product_categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 5));
        if (!empty($product_categories)) {
            $content .= "## Categorías de Producto\n";
            foreach ($product_categories as $category) {
                $content .= "- [$category->name](" . get_term_link($category->term_id) . "): " . wp_trim_words($category->description, 20, '...') . "\n";
            }
            $content .= "\n";
        }
    }

    $content .= "## Recursos Adicionales\n";
    $content .= "- [Documentación](https://misitio.com/docs): Referencias técnicas.\n";

    $result = file_put_contents($file_path, $content);
    if ($result === false) {
        llms_txt_log("Error al escribir el archivo en $file_path.");
        return false;
    }
    llms_txt_log("Archivo generado correctamente en $file_path.");
    return true;
}

// Función para borrar el archivo llms.txt
function delete_llms_txt() {
    $file_path = ABSPATH . 'llms.txt';
    if (file_exists($file_path) && unlink($file_path)) {
        llms_txt_log("Archivo $file_path borrado correctamente.");
        return true;
    }
    llms_txt_log("Error al borrar el archivo $file_path o no existe.");
    return false;
}

// Hooks
register_activation_hook(__FILE__, 'generate_llms_txt');
add_action('save_post', 'generate_llms_txt');

// Configuración del panel de administración
add_action('admin_menu', 'llms_txt_admin_menu');
function llms_txt_admin_menu() {
    add_options_page('LLMs.txt Settings', 'LLMs.txt', 'manage_options', 'llms-txt', 'llms_txt_settings_page');
}

add_action('admin_init', 'llms_txt_settings_init');
function llms_txt_settings_init() {
    // Registro de opciones
    register_setting('llms_txt_options', 'llms_txt_overwrite');
    register_setting('llms_txt_options', 'llms_txt_name');
    register_setting('llms_txt_options', 'llms_txt_description');
    register_setting('llms_txt_options', 'llms_txt_keywords');
    register_setting('llms_txt_options', 'llms_txt_language');
    register_setting('llms_txt_options', 'llms_txt_address');
    register_setting('llms_txt_options', 'llms_txt_location');

    add_settings_section('llms_txt_section', 'Configuración de LLMs.txt', 'llms_txt_section_callback', 'llms-txt');

    add_settings_field('llms_txt_overwrite', 'Sobrescribir automáticamente', 'llms_txt_overwrite_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_name', 'Nombre', 'llms_txt_name_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_description', 'Descripción', 'llms_txt_description_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_keywords', 'Palabras clave', 'llms_txt_keywords_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_language', 'Idioma', 'llms_txt_language_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_address', 'Dirección', 'llms_txt_address_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_location', 'Ubicación', 'llms_txt_location_callback', 'llms-txt', 'llms_txt_section');
}

function llms_txt_section_callback() {
    echo '<p>Configura cómo se genera y gestiona el archivo llms.txt en tu sitio.</p>';
}

function llms_txt_overwrite_callback() {
    $option = get_option('llms_txt_overwrite', 'yes');
    ?><select name="llms_txt_overwrite">
        <option value="yes" <?php selected($option, 'yes'); ?>>Sí (sobrescribir siempre)</option>
        <option value="no" <?php selected($option, 'no'); ?>>No (mantener existente)</option>
    </select>
    <p class="description">Elige si el archivo se sobescribe automáticamente al guardar contenido o al activar el plugin.</p><?php
}

function llms_txt_name_callback() {
    $value = get_option('llms_txt_name', get_bloginfo('name'));
    ?><input type="text" name="llms_txt_name" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Nombre del sitio (por defecto: <?php echo esc_html(get_bloginfo('name')); ?>).</p><?php
}

function llms_txt_description_callback() {
    $value = get_option('llms_txt_description', get_bloginfo('description'));
    ?><input type="text" name="llms_txt_description" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Descripción del sitio (por defecto: <?php echo esc_html(get_bloginfo('description')); ?>).</p><?php
}

function llms_txt_keywords_callback() {
    $value = get_option('llms_txt_keywords', 'programación, WordPress, IA, desarrollo web');
    ?><input type="text" name="llms_txt_keywords" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Palabras clave separadas por comas (por defecto: programación, WordPress, IA, desarrollo web).</p><?php
}

function llms_txt_language_callback() {
    $value = get_option('llms_txt_language', get_bloginfo('language'));
    ?><input type="text" name="llms_txt_language" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Idioma del sitio (por defecto: <?php echo esc_html(get_bloginfo('language')); ?>).</p><?php
}

function llms_txt_address_callback() {
    $value = get_option('llms_txt_address', 'Madrid, España');
    ?><input type="text" name="llms_txt_address" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Dirección física o virtual (por defecto: Madrid, España).</p><?php
}

function llms_txt_location_callback() {
    $value = get_option('llms_txt_location', 'Madrid, España');
    ?><input type="text" name="llms_txt_location" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Ubicación geográfica (por defecto: Madrid, España).</p><?php
}

add_action('admin_post_llms_txt_action', 'handle_llms_txt_action');
function handle_llms_txt_action() {
    if (!current_user_can('manage_options') || !isset($_POST['llms_txt_nonce']) || !wp_verify_nonce($_POST['llms_txt_nonce'], 'llms_txt_action')) {
        wp_die('Acceso no autorizado.');
    }

    $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
    $message = '';

    if ($action === 'delete') {
        $message = delete_llms_txt() ? 'El archivo llms.txt ha sido borrado correctamente.' : 'Error al borrar el archivo llms.txt o no existía.';
    } elseif ($action === 'overwrite') {
        $message = generate_llms_txt(true) ? 'El archivo llms.txt ha sido sobrescrito correctamente.' : 'Error al sobrescribir el archivo llms.txt.';
    }

    wp_redirect(admin_url('options-general.php?page=llms-txt&message=' . urlencode($message)));
    exit;
}

function llms_txt_settings_page() {
    $message = isset($_GET['message']) ? esc_html($_GET['message']) : '';
    $seo_plugin = llms_txt_detect_seo_plugin();
    $sitemap_url = $seo_plugin ? $seo_plugin['sitemap'] : 'No disponible';
    ?>
    <div class="wrap">
        <h1>LLMs.txt Settings</h1>
        <?php if ($message) { ?>
            <div class="notice notice-success is-dismissible"><p><?php echo $message; ?></p></div>
        <?php } ?>

        <h2>Configuración Automática</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('llms_txt_options');
            do_settings_sections('llms-txt');
            submit_button('Guardar Configuración');
            ?>
        </form>

        <h2>Gestión Manual</h2>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="llms_txt_action">
            <?php wp_nonce_field('llms_txt_action', 'llms_txt_nonce'); ?>
            <p>
                <button type="submit" name="action_type" value="overwrite" class="button button-primary">Sobrescribir Ahora</button>
                <span class="description">Genera un nuevo archivo llms.txt, ignorando la configuración automática.</span>
            </p>
            <p>
                <button type="submit" name="action_type" value="delete" class="button button-secondary">Borrar Archivo</button>
                <span class="description">Elimina el archivo llms.txt existente.</span>
            </p>
        </form>

        <h2>Información del Sistema</h2>
        <p><strong>Ubicación del archivo:</strong> <?php echo ABSPATH . 'llms.txt'; ?></p>
        <p><strong>Estado:</strong> <?php echo file_exists(ABSPATH . 'llms.txt') ? 'Existe' : 'No existe'; ?></p>
        <p><strong>Plugin SEO Detectado:</strong> <?php echo $seo_plugin ? $seo_plugin['name'] : 'Ninguno'; ?></p>
        <p><strong>Sitemap XML:</strong> 
            <?php if ($seo_plugin && $sitemap_url) { ?>
                <a href="<?php echo esc_url($sitemap_url); ?>" target="_blank"><?php echo esc_url($sitemap_url); ?></a>
            <?php } else { ?>
                No disponible (no se detectó un plugin SEO con sitemap).
            <?php } ?>
        </p>
    </div>
    <?php
}
