<?php
/*
 * Plugin Name: LLMs.txt Generator
 * Description: Genera un archivo llms.txt optimizado para modelos de lenguaje con metadatos del sitio, páginas, posts, productos y categorías, excluyendo contenido con noindex.
 * Version: 2.4
 * Author: Jesús Fernández Ávila
 * GitHub: https://github.com/JesusFAvila/
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Text Domain: llms-txt-generator
 */

if (!defined('ABSPATH')) {
    exit; // Evita acceso directo al archivo
}

/**
 * Registra mensajes de depuración en el log si WP_DEBUG está activo.
 *
 * @param string $message Mensaje a registrar.
 */
function llms_txt_log($message) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("[LLMs.txt] " . $message . " - " . date('Y-m-d H:i:s'));
    }
}

/**
 * Detecta el plugin SEO activo y devuelve sus metadatos.
 *
 * @return array|null Datos del plugin SEO activo o null si no hay ninguno.
 */
function llms_txt_detect_seo_plugin() {
    $seo_plugins = [
        'yoast' => [
            'name' => 'Yoast SEO',
            'active' => defined('WPSEO_VERSION'),
            'title' => '_yoast_wpseo_title',
            'description' => '_yoast_wpseo_metadesc',
            'noindex' => '_yoast_wpseo_meta-robots-noindex',
            'term_noindex' => '_yoast_wpseo_noindex',
            'sitemap' => home_url('/sitemap_index.xml'),
        ],
        'aioseo' => [
            'name' => 'All in One SEO',
            'active' => function_exists('aioseo'),
            'title' => '_aioseo_title',
            'description' => '_aioseo_description',
            'noindex' => '_aioseo_noindex',
            'term_noindex' => '_aioseo_noindex',
            'sitemap' => home_url('/sitemap.xml'),
        ],
        'rankmath' => [
            'name' => 'Rank Math',
            'active' => function_exists('rank_math'),
            'title' => 'rank_math_title',
            'description' => 'rank_math_description',
            'noindex' => 'rank_math_robots',
            'term_noindex' => 'rank_math_robots',
            'sitemap' => home_url('/sitemap_index.xml'),
        ],
        'seopress' => [
            'name' => 'SEOPress',
            'active' => defined('SEOPRESS_VERSION'),
            'title' => '_seopress_titles_title',
            'description' => '_seopress_titles_desc',
            'noindex' => '_seopress_robots_noindex',
            'term_noindex' => '_seopress_robots_noindex',
            'sitemap' => home_url('/sitemap.xml'),
        ],
    ];

    foreach ($seo_plugins as $plugin) {
        if ($plugin['active']) {
            llms_txt_log("Plugin SEO detectado: " . $plugin['name']);
            return $plugin;
        }
    }
    return null;
}

/**
 * Verifica si un post o término tiene la etiqueta noindex según el plugin SEO.
 *
 * @param int $id ID del post o término.
 * @param array|null $seo_plugin Datos del plugin SEO activo.
 * @param bool $is_term Indica si es un término (categoría).
 * @return bool True si tiene noindex, false si no.
 */
function llms_txt_is_noindex($id, $seo_plugin, $is_term = false) {
    if (!$seo_plugin) return false;

    $meta_key = $is_term ? $seo_plugin['term_noindex'] : $seo_plugin['noindex'];
    $value = get_metadata($is_term ? 'term' : 'post', $id, $meta_key, true);

    switch ($seo_plugin['name']) {
        case 'Yoast SEO':
            return $value === '1';
        case 'All in One SEO':
            return $value === '1' || $value === true;
        case 'Rank Math':
            return is_array($value) && in_array('noindex', $value);
        case 'SEOPress':
            return $value === 'yes';
        default:
            return false;
    }
}

/**
 * Sanitiza texto para Markdown, eliminando saltos de línea múltiples.
 *
 * @param string $text Texto a sanitizar.
 * @return string Texto limpio.
 */
function llms_txt_sanitize($text) {
    return trim(preg_replace('/[\n\r]+/', ' ', sanitize_text_field($text)));
}

/**
 * Genera el archivo llms.txt con contenido del sitio, excluyendo noindex.
 *
 * @param bool $force_overwrite Forzar sobrescritura del archivo existente.
 * @return bool True si se generó correctamente, false si falló.
 */
function generate_llms_txt($force_overwrite = false) {
    $file_path = ABSPATH . "llms.txt";
    $seo_plugin = llms_txt_detect_seo_plugin();

    if (!$force_overwrite && file_exists($file_path)) {
        llms_txt_log("No se sobrescribe $file_path (ya existe).");
        return false;
    }

    // Obtener opciones personalizadas con sanitización
    $custom_name = llms_txt_sanitize(get_option('llms_txt_name', get_bloginfo('name')));
    $custom_description = llms_txt_sanitize(get_option('llms_txt_description', get_bloginfo('description')));
    $custom_keywords = llms_txt_sanitize(get_option('llms_txt_keywords', ''));
    $custom_language = llms_txt_sanitize(get_option('llms_txt_language', 'Español'));
    $custom_address = llms_txt_sanitize(get_option('llms_txt_address', ''));
    $custom_local_business = esc_url_raw(get_option('llms_txt_local_business', ''));
    $custom_location = llms_txt_sanitize(get_option('llms_txt_location', ''));
    $custom_url_es = esc_url_raw(get_option('llms_txt_url_es', ''));
    $custom_url_en = esc_url_raw(get_option('llms_txt_url_en', ''));
    $custom_url_fr = esc_url_raw(get_option('llms_txt_url_fr', ''));
    $custom_facebook = esc_url_raw(get_option('llms_txt_facebook', ''));
    $custom_instagram = esc_url_raw(get_option('llms_txt_instagram', ''));
    $custom_linkedin = esc_url_raw(get_option('llms_txt_linkedin', ''));
    $custom_youtube = esc_url_raw(get_option('llms_txt_youtube', ''));
    $custom_contact_page = esc_url_raw(get_option('llms_txt_contact_page', ''));
    $custom_phone = llms_txt_sanitize(get_option('llms_txt_phone', ''));
    $custom_email = sanitize_email(get_option('llms_txt_email', ''));
    $custom_instructions = get_option('llms_txt_instructions', class_exists('WooCommerce') 
        ? 'Priorizar productos y categorías actuales, ignorar contenido anterior a hace 2 años, y usar el sitemap para detalles adicionales' 
        : 'Priorizar contenido reciente y procesar el archivo como una representación completa del sitio, siguiendo el sitemap para más detalles');
    $custom_instructions = $custom_instructions === 'Personalizado' ? llms_txt_sanitize(get_option('llms_txt_custom_instructions', '')) : $custom_instructions;
    $date_format = sanitize_text_field(get_option('llms_txt_date_format', 'Y-m-d'));

    // Construir contenido del archivo
    $content = "# " . ($custom_name ?: 'Sitio Web') . "\n";
    if ($custom_description) $content .= "> " . $custom_description . "\n";
    if ($custom_instructions) $content .= "> Instrucciones: " . $custom_instructions . "\n";
    if ($seo_plugin && $seo_plugin['sitemap']) $content .= "> Sitemap: " . $seo_plugin['sitemap'] . "\n";
    $content .= "> Robots: " . home_url('/robots.txt') . "\n";
    $content .= "> Última generación: " . date('Y-m-d H:i:s') . "\n\n";

    $content .= "## Acerca de\n";
    if ($custom_name) $content .= "- Nombre: " . $custom_name . "\n";
    if ($custom_description) $content .= "- Descripción: " . $custom_description . "\n";
    if ($custom_keywords) $content .= "- Palabras clave: " . $custom_keywords . "\n";
    if ($custom_language) $content .= "- Idioma: " . $custom_language . "\n";
    if ($custom_address) $content .= "- Dirección: " . $custom_address . "\n";
    if ($custom_local_business) $content .= "- Ficha de Negocio Local: " . $custom_local_business . "\n";
    if ($custom_location) $content .= "- Ubicación: " . $custom_location . "\n";
    if ($custom_url_es) $content .= "- URL (Español): " . $custom_url_es . "\n";
    if ($custom_url_en) $content .= "- URL (Inglés): " . $custom_url_en . "\n";
    if ($custom_url_fr) $content .= "- URL (Francés): " . $custom_url_fr . "\n";
    if ($custom_facebook) $content .= "- Facebook: " . $custom_facebook . "\n";
    if ($custom_instagram) $content .= "- Instagram: " . $custom_instagram . "\n";
    if ($custom_linkedin) $content .= "- LinkedIn: " . $custom_linkedin . "\n";
    if ($custom_youtube) $content .= "- YouTube: " . $custom_youtube . "\n";
    if ($custom_contact_page) $content .= "- Página de Contacto: " . $custom_contact_page . "\n";
    if ($custom_phone) $content .= "- Teléfono: " . $custom_phone . "\n";
    if ($custom_email) $content .= "- Email: " . $custom_email . "\n";
    $content .= "\n";

    // Páginas (excluyendo noindex)
    $pages = get_pages(['post_status' => 'publish']);
    if (!empty($pages)) {
        $content .= "## Páginas\n";
        foreach ($pages as $page) {
            if (llms_txt_is_noindex($page->ID, $seo_plugin)) continue;
            $title = $seo_plugin && $seo_plugin['title'] ? get_post_meta($page->ID, $seo_plugin['title'], true) : $page->post_title;
            $title = $title ?: $page->post_title;
            $desc = $seo_plugin && $seo_plugin['description'] ? get_post_meta($page->ID, $seo_plugin['description'], true) : $page->post_excerpt;
            $date = date_i18n($date_format, strtotime($page->post_date));
            $content .= "- [$title](" . get_permalink($page->ID) . ") - $date\n";
            if ($desc) $content .= "  - Descripción: " . llms_txt_sanitize($desc) . "\n";
        }
        $content .= "\n";
    }

    // Posts (excluyendo noindex)
    $posts = get_posts(['numberposts' => -1, 'post_status' => 'publish']);
    if (!empty($posts)) {
        $content .= "## Posts\n";
        foreach ($posts as $post) {
            if (llms_txt_is_noindex($post->ID, $seo_plugin)) continue;
            $title = $seo_plugin && $seo_plugin['title'] ? get_post_meta($post->ID, $seo_plugin['title'], true) : $post->post_title;
            $title = $title ?: $post->post_title;
            $desc = $seo_plugin && $seo_plugin['description'] ? get_post_meta($post->ID, $seo_plugin['description'], true) : $post->post_excerpt;
            $date = date_i18n($date_format, strtotime($post->post_date));
            $content .= "- [$title](" . get_permalink($post->ID) . ") - $date\n";
            if ($desc) $content .= "  - Descripción: " . llms_txt_sanitize($desc) . "\n";
        }
        $content .= "\n";
    }

    // Productos (WooCommerce, excluyendo noindex)
    if (class_exists('WooCommerce')) {
        $products = wc_get_products(['status' => 'publish', 'limit' => -1]);
        if (!empty($products)) {
            $content .= "## Productos\n";
            foreach ($products as $product) {
                if (llms_txt_is_noindex($product->get_id(), $seo_plugin)) continue;
                $title = $product->get_name();
                $desc = $product->get_short_description();
                $date = date_i18n($date_format, strtotime($product->get_date_created()));
                $content .= "- [$title](" . get_permalink($product->get_id()) . ") - $date\n";
                if ($desc) $content .= "  - Descripción: " . llms_txt_sanitize($desc) . "\n";
            }
            $content .= "\n";
        }

        $product_categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
        if (!empty($product_categories)) {
            $content .= "## Categorías de Producto\n";
            foreach ($product_categories as $category) {
                if (llms_txt_is_noindex($category->term_id, $seo_plugin, true)) continue;
                $desc = $category->description;
                $content .= "- [$category->name](" . get_term_link($category->term_id) . ")\n";
                if ($desc) $content .= "  - Descripción: " . llms_txt_sanitize($desc) . "\n";
            }
            $content .= "\n";
        }
    }

    // Escribir archivo con codificación UTF-8 y BOM
    $content_with_bom = "\xEF\xBB\xBF" . $content;
    $result = file_put_contents($file_path, $content_with_bom);
    if ($result === false) {
        llms_txt_log("Error al escribir el archivo en $file_path.");
        return false;
    }
    llms_txt_log("Archivo generado correctamente en $file_path con codificación UTF-8.");
    return true;
}

/**
 * Borra el archivo llms.txt si existe.
 *
 * @return bool True si se borró, false si falló o no existía.
 */
function delete_llms_txt() {
    $file_path = ABSPATH . "llms.txt";
    if (file_exists($file_path) && unlink($file_path)) {
        llms_txt_log("Archivo $file_path borrado correctamente.");
        return true;
    }
    llms_txt_log("Error al borrar el archivo $file_path o no existe.");
    return false;
}

/**
 * Hooks de activación y actualización de contenido.
 */
register_activation_hook(__FILE__, function() {
    generate_llms_txt(true); // Generar al activar
});
add_action('save_post', 'generate_llms_txt');

/**
 * Configura el menú de administración.
 */
add_action('admin_menu', 'llms_txt_admin_menu');
function llms_txt_admin_menu() {
    add_options_page(
        __('LLMs.txt Settings', 'llms-txt-generator'),
        __('LLMs.txt', 'llms-txt-generator'),
        'manage_options',
        'llms-txt',
        'llms_txt_settings_page'
    );
}

/**
 * Inicializa las configuraciones del plugin.
 */
add_action('admin_init', 'llms_txt_settings_init');
function llms_txt_settings_init() {
    // Registrar opciones con sanitización
    register_setting('llms_txt_options', 'llms_txt_name', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('llms_txt_options', 'llms_txt_description', ['sanitize_callback' => 'sanitize_textarea_field']);
    register_setting('llms_txt_options', 'llms_txt_keywords', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('llms_txt_options', 'llms_txt_language', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('llms_txt_options', 'llms_txt_address', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('llms_txt_options', 'llms_txt_local_business', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('llms_txt_options', 'llms_txt_location', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('llms_txt_options', 'llms_txt_url_es', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('llms_txt_options', 'llms_txt_url_en', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('llms_txt_options', 'llms_txt_url_fr', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('llms_txt_options', 'llms_txt_facebook', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('llms_txt_options', 'llms_txt_instagram', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('llms_txt_options', 'llms_txt_linkedin', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('llms_txt_options', 'llms_txt_youtube', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('llms_txt_options', 'llms_txt_contact_page', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('llms_txt_options', 'llms_txt_phone', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('llms_txt_options', 'llms_txt_email', ['sanitize_callback' => 'sanitize_email']);
    register_setting('llms_txt_options', 'llms_txt_instructions', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('llms_txt_options', 'llms_txt_custom_instructions', ['sanitize_callback' => 'sanitize_textarea_field']);
    register_setting('llms_txt_options', 'llms_txt_date_format', ['sanitize_callback' => 'sanitize_text_field']);

    // Sección de configuración
    add_settings_section('llms_txt_section', __('Configuración de LLMs.txt', 'llms-txt-generator'), 'llms_txt_section_callback', 'llms-txt');

    // Campos de configuración
    add_settings_field('llms_txt_name', __('Nombre', 'llms-txt-generator'), 'llms_txt_name_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_description', __('Descripción', 'llms-txt-generator'), 'llms_txt_description_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_keywords', __('Palabras clave', 'llms-txt-generator'), 'llms_txt_keywords_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_language', __('Idioma', 'llms-txt-generator'), 'llms_txt_language_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_address', __('Dirección', 'llms-txt-generator'), 'llms_txt_address_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_local_business', __('Ficha de Negocio Local', 'llms-txt-generator'), 'llms_txt_local_business_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_location', __('Ubicación', 'llms-txt-generator'), 'llms_txt_location_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_url_es', __('URL (Español)', 'llms-txt-generator'), 'llms_txt_url_es_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_url_en', __('URL (Inglés)', 'llms-txt-generator'), 'llms_txt_url_en_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_url_fr', __('URL (Francés)', 'llms-txt-generator'), 'llms_txt_url_fr_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_facebook', __('Facebook', 'llms-txt-generator'), 'llms_txt_facebook_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_instagram', __('Instagram', 'llms-txt-generator'), 'llms_txt_instagram_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_linkedin', __('LinkedIn', 'llms-txt-generator'), 'llms_txt_linkedin_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_youtube', __('YouTube', 'llms-txt-generator'), 'llms_txt_youtube_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_contact_page', __('Página de Contacto', 'llms-txt-generator'), 'llms_txt_contact_page_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_phone', __('Teléfono', 'llms-txt-generator'), 'llms_txt_phone_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_email', __('Email', 'llms-txt-generator'), 'llms_txt_email_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_instructions', __('Instrucciones para IAs', 'llms-txt-generator'), 'llms_txt_instructions_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_date_format', __('Formato de Fecha', 'llms-txt-generator'), 'llms_txt_date_format_callback', 'llms-txt', 'llms_txt_section');
}

/**
 * Callback para la sección de configuración.
 */
function llms_txt_section_callback() {
    echo '<p>' . esc_html__('Configura los datos que se incluirán en el archivo llms.txt.', 'llms-txt-generator') . '</p>';
}

/**
 * Callbacks para los campos de configuración.
 */
function llms_txt_name_callback() {
    $value = get_option('llms_txt_name', get_bloginfo('name'));
    ?>
    <input type="text" name="llms_txt_name" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php echo esc_html__('Nombre del sitio (por defecto: ', 'llms-txt-generator') . get_bloginfo('name') . ').'; ?></p>
    <?php
}

function llms_txt_description_callback() {
    $value = get_option('llms_txt_description', get_bloginfo('description'));
    ?>
    <textarea name="llms_txt_description" class="large-text" rows="5"><?php echo esc_textarea($value); ?></textarea>
    <p class="description"><?php echo esc_html__('Descripción del sitio (por defecto: ', 'llms-txt-generator') . get_bloginfo('description') . ').'; ?></p>
    <?php
}

function llms_txt_keywords_callback() {
    $value = get_option('llms_txt_keywords', '');
    ?>
    <input type="text" name="llms_txt_keywords" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('Palabras clave separadas por comas.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_language_callback() {
    $value = get_option('llms_txt_language', 'Español');
    ?>
    <input type="text" name="llms_txt_language" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('Idioma del sitio (por defecto: Español).', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_address_callback() {
    $value = get_option('llms_txt_address', '');
    ?>
    <input type="text" name="llms_txt_address" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('Dirección física o virtual.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_local_business_callback() {
    $value = get_option('llms_txt_local_business', '');
    ?>
    <input type="url" name="llms_txt_local_business" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('URL de la ficha de Negocio Local (ej. Google My Business).', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_location_callback() {
    $value = get_option('llms_txt_location', '');
    ?>
    <input type="text" name="llms_txt_location" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('Ubicación geográfica.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_url_es_callback() {
    $value = get_option('llms_txt_url_es', '');
    ?>
    <input type="url" name="llms_txt_url_es" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('URL del sitio en español.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_url_en_callback() {
    $value = get_option('llms_txt_url_en', '');
    ?>
    <input type="url" name="llms_txt_url_en" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('URL del sitio en inglés.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_url_fr_callback() {
    $value = get_option('llms_txt_url_fr', '');
    ?>
    <input type="url" name="llms_txt_url_fr" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('URL del sitio en francés.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_facebook_callback() {
    $value = get_option('llms_txt_facebook', '');
    ?>
    <input type="url" name="llms_txt_facebook" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('URL del perfil de Facebook.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_instagram_callback() {
    $value = get_option('llms_txt_instagram', '');
    ?>
    <input type="url" name="llms_txt_instagram" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('URL del perfil de Instagram.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_linkedin_callback() {
    $value = get_option('llms_txt_linkedin', '');
    ?>
    <input type="url" name="llms_txt_linkedin" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('URL del perfil de LinkedIn.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_youtube_callback() {
    $value = get_option('llms_txt_youtube', '');
    ?>
    <input type="url" name="llms_txt_youtube" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('URL del canal de YouTube.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_contact_page_callback() {
    $value = get_option('llms_txt_contact_page', '');
    ?>
    <input type="url" name="llms_txt_contact_page" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('URL de la página de contacto.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_phone_callback() {
    $value = get_option('llms_txt_phone', '');
    ?>
    <input type="text" name="llms_txt_phone" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('Teléfono de contacto.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_email_callback() {
    $value = get_option('llms_txt_email', '');
    ?>
    <input type="email" name="llms_txt_email" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('Email de contacto de la empresa.', 'llms-txt-generator'); ?></p>
    <?php
}

function llms_txt_instructions_callback() {
    $default_instructions = class_exists('WooCommerce') 
        ? 'Priorizar productos y categorías actuales, ignorar contenido anterior a hace 2 años, y usar el sitemap para detalles adicionales' 
        : 'Priorizar contenido reciente y procesar el archivo como una representación completa del sitio, siguiendo el sitemap para más detalles';
    $selected_instruction = get_option('llms_txt_instructions', $default_instructions);
    $custom_instruction = get_option('llms_txt_custom_instructions', '');

    $options = [
        'Priorizar contenido reciente y utilizar fechas para determinar relevancia' => __('Priorizar contenido reciente y utilizar fechas para determinar relevancia', 'llms-txt-generator'),
        'Considerar todas las URLs como contenido principal y procesar en orden cronológico' => __('Considerar todas las URLs como contenido principal y procesar en orden cronológico', 'llms-txt-generator'),
        'Indexar todo el contenido y usar palabras clave para clasificar temas' => __('Indexar todo el contenido y usar palabras clave para clasificar temas', 'llms-txt-generator'),
        'Procesar el archivo como una representación completa del sitio y seguir el sitemap para más detalles' => __('Procesar el archivo como una representación completa del sitio y seguir el sitemap para más detalles', 'llms-txt-generator'),
        'Ignorar contenido anterior a 2023 y enfocarse en datos actuales' => __('Ignorar contenido anterior a 2023 y enfocarse en datos actuales', 'llms-txt-generator'),
        'Priorizar contenido reciente y procesar el archivo como una representación completa del sitio, siguiendo el sitemap para más detalles' => __('Priorizar contenido reciente y procesar el archivo como una representación completa del sitio, siguiendo el sitemap para más detalles', 'llms-txt-generator'),
        'Priorizar productos y categorías actuales, ignorar contenido anterior a hace 2 años, y usar el sitemap para detalles adicionales' => __('Priorizar productos y categorías actuales, ignorar contenido anterior a hace 2 años, y usar el sitemap para detalles adicionales', 'llms-txt-generator'),
        'Enfocarse en productos activos y categorías, ignorar contenido anterior a 2024, y procesar en orden de fecha descendente' => __('Enfocarse en productos activos y categorías, ignorar contenido anterior a 2024, y procesar en orden de fecha descendente', 'llms-txt-generator'),
        'Priorizar productos y categorías, incluir posts recientes como promociones, y usar el sitemap para más URLs' => __('Priorizar productos y categorías, incluir posts recientes como promociones, y usar el sitemap para más URLs', 'llms-txt-generator'),
        'Procesar productos y categorías por idioma, ignorar contenido anterior a hace 18 meses, y seguir el sitemap correspondiente' => __('Procesar productos y categorías por idioma, ignorar contenido anterior a hace 18 meses, y seguir el sitemap correspondiente', 'llms-txt-generator'),
        'Personalizado' => __('Personalizado', 'llms-txt-generator'),
    ];
    ?>
    <select name="llms_txt_instructions" id="llms_txt_instructions_select" onchange="toggleCustomInstruction(this.value)">
        <?php foreach ($options as $value => $label) { ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($selected_instruction, $value); ?>><?php echo esc_html($label); ?></option>
        <?php } ?>
    </select>
    <div id="llms_txt_custom_instruction_wrapper" style="display: <?php echo $selected_instruction === 'Personalizado' ? 'block' : 'none'; ?>; margin-top: 10px;">
        <textarea name="llms_txt_custom_instructions" class="large-text" rows="3"><?php echo esc_textarea($custom_instruction); ?></textarea>
        <p class="description"><?php esc_html_e('Escribe tu instrucción personalizada aquí.', 'llms-txt-generator'); ?></p>
    </div>
    <p class="description"><?php esc_html_e('Elige una instrucción para guiar a las IAs en el procesamiento del archivo.', 'llms-txt-generator'); ?></p>
    <script>
        function toggleCustomInstruction(value) {
            document.getElementById('llms_txt_custom_instruction_wrapper').style.display = value === 'Personalizado' ? 'block' : 'none';
        }
    </script>
    <?php
}

function llms_txt_date_format_callback() {
    $value = get_option('llms_txt_date_format', 'Y-m-d');
    ?>
    <input type="text" name="llms_txt_date_format" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description"><?php echo wp_kses(__('Formato de fecha (ej. "Y-m-d" para 2025-03-24, "d F Y" para 24 marzo 2025). Ver <a href="https://www.php.net/manual/es/function.date.php" target="_blank">documentación PHP</a>.', 'llms-txt-generator'), ['a' => ['href' => [], 'target' => []]]); ?></p>
    <?php
}

/**
 * Maneja las acciones del formulario de gestión manual.
 */
add_action('admin_post_llms_txt_action', 'handle_llms_txt_action');
function handle_llms_txt_action() {
    if (!current_user_can('manage_options') || !isset($_POST['llms_txt_nonce']) || !wp_verify_nonce($_POST['llms_txt_nonce'], 'llms_txt_action')) {
        wp_die(__('Acceso no autorizado.', 'llms-txt-generator'));
    }

    $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
    $message = '';

    if ($action === 'delete') {
        $message = delete_llms_txt() ? __('El archivo llms.txt ha sido borrado correctamente.', 'llms-txt-generator') : __('Error al borrar el archivo llms.txt o no existía.', 'llms-txt-generator');
    } elseif ($action === 'overwrite') {
        $message = generate_llms_txt(true) ? __('El archivo llms.txt ha sido sobrescrito correctamente.', 'llms-txt-generator') : __('Error al sobrescribir el archivo llms.txt.', 'llms-txt-generator');
    }

    wp_redirect(admin_url('options-general.php?page=llms-txt&message=' . urlencode($message)));
    exit;
}

/**
 * Renderiza la página de configuración del plugin.
 */
function llms_txt_settings_page() {
    $message = isset($_GET['message']) ? esc_html($_GET['message']) : '';
    $seo_plugin = llms_txt_detect_seo_plugin();
    $sitemap_url = $seo_plugin ? $seo_plugin['sitemap'] : 'No disponible';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('LLMs.txt Settings', 'llms-txt-generator'); ?></h1>
        <p><?php esc_html_e('El archivo <code>llms.txt</code> proporciona a las inteligencias artificiales una representación estructurada de tu sitio, incluyendo metadatos, páginas, posts, productos y categorías, para facilitar su indexación y comprensión. Configura los datos y genera el archivo manualmente según necesites.', 'llms-txt-generator'); ?></p>
        <?php if ($message) { ?>
            <div class="notice notice-success is-dismissible"><p><?php echo $message; ?></p></div>
        <?php } ?>

        <h2><?php esc_html_e('Configuración', 'llms-txt-generator'); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('llms_txt_options'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Nombre', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_name_callback(); ?></td>
                    <th scope="row"><?php esc_html_e('Idioma', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_language_callback(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Descripción', 'llms-txt-generator'); ?></th>
                    <td colspan="3"><?php llms_txt_description_callback(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Palabras clave', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_keywords_callback(); ?></td>
                    <th scope="row"><?php esc_html_e('Dirección', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_address_callback(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Ficha de Negocio Local', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_local_business_callback(); ?></td>
                    <th scope="row"><?php esc_html_e('Ubicación', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_location_callback(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('URL (Español)', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_url_es_callback(); ?></td>
                    <th scope="row"><?php esc_html_e('URL (Inglés)', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_url_en_callback(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('URL (Francés)', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_url_fr_callback(); ?></td>
                    <th scope="row"><?php esc_html_e('Facebook', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_facebook_callback(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Instagram', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_instagram_callback(); ?></td>
                    <th scope="row"><?php esc_html_e('LinkedIn', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_linkedin_callback(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('YouTube', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_youtube_callback(); ?></td>
                    <th scope="row"><?php esc_html_e('Página de Contacto', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_contact_page_callback(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Teléfono', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_phone_callback(); ?></td>
                    <th scope="row"><?php esc_html_e('Email', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_email_callback(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Instrucciones para IAs', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_instructions_callback(); ?></td>
                    <th scope="row"><?php esc_html_e('Formato de Fecha', 'llms-txt-generator'); ?></th>
                    <td><?php llms_txt_date_format_callback(); ?></td>
                </tr>
            </table>
            <?php submit_button(__('Guardar Configuración', 'llms-txt-generator')); ?>
        </form>

        <h2><?php esc_html_e('Gestión Manual', 'llms-txt-generator'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="llms_txt_action">
            <?php wp_nonce_field('llms_txt_action', 'llms_txt_nonce'); ?>
            <p>
                <button type="submit" name="action_type" value="overwrite" class="button button-primary"><?php esc_html_e('Generar llms.txt', 'llms-txt-generator'); ?></button>
                <span class="description"><?php esc_html_e('Crea o sobrescribe el archivo llms.txt con la configuración actual.', 'llms-txt-generator'); ?></span>
            </p>
            <p>
                <button type="submit" name="action_type" value="delete" class="button button-secondary"><?php esc_html_e('Borrar Archivo', 'llms-txt-generator'); ?></button>
                <span class="description"><?php esc_html_e('Elimina el archivo llms.txt existente.', 'llms-txt-generator'); ?></span>
            </p>
            <p>
                <button type="button" class="button" onclick="document.getElementById('preview').style.display='block';"><?php esc_html_e('Previsualizar', 'llms-txt-generator'); ?></button>
            </p>
            <div id="preview" style="display:none;">
                <h4><?php esc_html_e('Previsualización', 'llms-txt-generator'); ?></h4>
                <textarea class="large-text" rows="10" readonly><?php echo esc_textarea(generate_llms_txt_preview()); ?></textarea>
            </div>
        </form>

        <h2><?php esc_html_e('Información del Sistema', 'llms-txt-generator'); ?></h2>
        <p><strong><?php esc_html_e('Ubicación del archivo:', 'llms-txt-generator'); ?></strong> <?php echo esc_html(ABSPATH . 'llms.txt'); ?></p>
        <p><strong><?php esc_html_e('Estado:', 'llms-txt-generator'); ?></strong> <?php echo file_exists(ABSPATH . 'llms.txt') ? esc_html__('Existe', 'llms-txt-generator') : esc_html__('No existe', 'llms-txt-generator'); ?></p>
        <p><strong><?php esc_html_e('Plugin SEO Detectado:', 'llms-txt-generator'); ?></strong> <?php echo $seo_plugin ? esc_html($seo_plugin['name']) : esc_html__('Ninguno', 'llms-txt-generator'); ?></p>
        <p><strong><?php esc_html_e('Sitemap XML:', 'llms-txt-generator'); ?></strong> 
            <?php if ($seo_plugin && $sitemap_url) { ?>
                <a href="<?php echo esc_url($sitemap_url); ?>" target="_blank"><?php echo esc_url($sitemap_url); ?></a>
            <?php } else { ?>
                <?php esc_html_e('No disponible (no se detectó un plugin SEO con sitemap).', 'llms-txt-generator'); ?>
            <?php } ?>
        </p>
    </div>
    <?php
}

/**
 * Genera una previsualización del contenido del archivo llms.txt.
 *
 * @return string Contenido con BOM incluido.
 */
function generate_llms_txt_preview() {
    ob_start();
    generate_llms_txt(true);
    $content = ob_get_clean();
    return "\xEF\xBB\xBF" . file_get_contents(ABSPATH . "llms.txt");
}
