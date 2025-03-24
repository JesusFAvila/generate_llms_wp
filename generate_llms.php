<?php
/*
Plugin Name: LLMs.txt Generator
Description: Genera un archivo llms.txt optimizado para modelos de lenguaje con metadatos del sitio, páginas, posts, productos y categorías.
Version: 2.2.1
Author: Tu Nombre
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
            'description' => '_yoast_wpseo_metadesc',
            'sitemap' => home_url('/sitemap_index.xml'),
        ],
        'aioseo' => [
            'name' => 'All in One SEO',
            'active' => function_exists('aioseo'),
            'title' => '_aioseo_title',
            'description' => '_aioseo_description',
            'sitemap' => home_url('/sitemap.xml'),
        ],
        'rankmath' => [
            'name' => 'Rank Math',
            'active' => function_exists('rank_math'),
            'title' => 'rank_math_title',
            'description' => 'rank_math_description',
            'sitemap' => home_url('/sitemap_index.xml'),
        ],
        'seopress' => [
            'name' => 'SEOPress',
            'active' => defined('SEOPRESS_VERSION'),
            'title' => '_seopress_titles_title',
            'description' => '_seopress_titles_desc',
            'sitemap' => home_url('/sitemap.xml'),
        ],
    ];

    foreach ($seo_plugins as $key => $plugin) {
        if ($plugin['active']) {
            llms_txt_log("Plugin SEO detectado: " . $plugin['name']);
            return $plugin;
        }
    }
    return null;
}

// Sanitizar texto para Markdown
function llms_txt_sanitize($text) {
    return trim(preg_replace('/[\n\r]+/', ' ', sanitize_text_field($text)));
}

// Generar el archivo llms.txt con codificación UTF-8 y BOM
function generate_llms_txt($force_overwrite = false) {
    $file_path = ABSPATH . "llms.txt";
    $seo_plugin = llms_txt_detect_seo_plugin();

    if (!$force_overwrite && file_exists($file_path)) {
        llms_txt_log("No se sobrescribe $file_path (ya existe).");
        return false;
    }

    // Obtener opciones personalizadas
    $custom_name = llms_txt_sanitize(get_option('llms_txt_name', get_bloginfo('name')));
    $custom_description = llms_txt_sanitize(get_option('llms_txt_description', get_bloginfo('description')));
    $custom_keywords = llms_txt_sanitize(get_option('llms_txt_keywords', ''));
    $custom_language = llms_txt_sanitize(get_option('llms_txt_language', get_bloginfo('language')));
    $custom_address = llms_txt_sanitize(get_option('llms_txt_address', ''));
    $custom_location = llms_txt_sanitize(get_option('llms_txt_location', ''));
    $custom_instructions = get_option('llms_txt_instructions', class_exists('WooCommerce') 
        ? 'Priorizar productos y categorías actuales, ignorar contenido anterior a hace 2 años, y usar el sitemap para detalles adicionales' 
        : 'Priorizar contenido reciente y procesar el archivo como una representación completa del sitio, siguiendo el sitemap para más detalles');
    $custom_instructions = $custom_instructions === 'Personalizado' ? llms_txt_sanitize(get_option('llms_txt_custom_instructions', '')) : $custom_instructions;
    $date_format = get_option('llms_txt_date_format', 'Y-m-d');

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
    if ($custom_location) $content .= "- Ubicación: " . $custom_location . "\n";
    $content .= "- URL: " . home_url() . "\n\n";

    // Páginas
    $pages = get_pages(array('post_status' => 'publish'));
    if (!empty($pages)) {
        $content .= "## Páginas\n";
        foreach ($pages as $page) {
            $title = $seo_plugin && $seo_plugin['title'] ? get_post_meta($page->ID, $seo_plugin['title'], true) : $page->post_title;
            $title = $title ?: $page->post_title;
            $desc = $seo_plugin && $seo_plugin['description'] ? get_post_meta($page->ID, $seo_plugin['description'], true) : $page->post_excerpt;
            $date = date_i18n($date_format, strtotime($page->post_date));
            $content .= "- [$title](" . get_permalink($page->ID) . ") - $date\n";
            if ($desc) $content .= "  - Descripción: " . llms_txt_sanitize($desc) . "\n";
        }
        $content .= "\n";
    }

    // Posts
    $posts = get_posts(array('numberposts' => -1, 'post_status' => 'publish'));
    if (!empty($posts)) {
        $content .= "## Posts\n";
        foreach ($posts as $post) {
            $title = $seo_plugin && $seo_plugin['title'] ? get_post_meta($post->ID, $seo_plugin['title'], true) : $post->post_title;
            $title = $title ?: $post->post_title;
            $desc = $seo_plugin && $seo_plugin['description'] ? get_post_meta($post->ID, $seo_plugin['description'], true) : $post->post_excerpt;
            $date = date_i18n($date_format, strtotime($post->post_date));
            $content .= "- [$title](" . get_permalink($post->ID) . ") - $date\n";
            if ($desc) $content .= "  - Descripción: " . llms_txt_sanitize($desc) . "\n";
        }
        $content .= "\n";
    }

    // Productos (WooCommerce)
    if (class_exists('WooCommerce')) {
        $products = wc_get_products(array('status' => 'publish', 'limit' => -1));
        if (!empty($products)) {
            $content .= "## Productos\n";
            foreach ($products as $product) {
                $title = $product->get_name();
                $desc = $product->get_short_description();
                $date = date_i18n($date_format, strtotime($product->get_date_created()));
                $content .= "- [$title](" . get_permalink($product->get_id()) . ") - $date\n";
                if ($desc) $content .= "  - Descripción: " . llms_txt_sanitize($desc) . "\n";
            }
            $content .= "\n";
        }

        $product_categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true));
        if (!empty($product_categories)) {
            $content .= "## Categorías de Producto\n";
            foreach ($product_categories as $category) {
                $desc = $category->description;
                $content .= "- [$category->name](" . get_term_link($category->term_id) . ")\n";
                if ($desc) $content .= "  - Descripción: " . llms_txt_sanitize($desc) . "\n";
            }
            $content .= "\n";
        }
    }

    // Añadir BOM para UTF-8 y escribir el archivo
    $content_with_bom = "\xEF\xBB\xBF" . $content;
    $result = file_put_contents($file_path, $content_with_bom);
    if ($result === false) {
        llms_txt_log("Error al escribir el archivo en $file_path.");
        return false;
    }
    llms_txt_log("Archivo generado correctamente en $file_path con codificación UTF-8.");
    return true;
}

// Función para borrar el archivo llms.txt
function delete_llms_txt() {
    $file_path = ABSPATH . "llms.txt";
    if (file_exists($file_path) && unlink($file_path)) {
        llms_txt_log("Archivo $file_path borrado correctamente.");
        return true;
    }
    llms_txt_log("Error al borrar el archivo $file_path o no existe.");
    return false;
}

// Hooks
register_activation_hook(__FILE__, function() {
    generate_llms_txt(true);
});
add_action('save_post', 'generate_llms_txt');

// Configuración del panel de administración
add_action('admin_menu', 'llms_txt_admin_menu');
function llms_txt_admin_menu() {
    add_options_page('LLMs.txt Settings', 'LLMs.txt', 'manage_options', 'llms-txt', 'llms_txt_settings_page');
}

add_action('admin_init', 'llms_txt_settings_init');
function llms_txt_settings_init() {
    register_setting('llms_txt_options', 'llms_txt_name');
    register_setting('llms_txt_options', 'llms_txt_description');
    register_setting('llms_txt_options', 'llms_txt_keywords');
    register_setting('llms_txt_options', 'llms_txt_language');
    register_setting('llms_txt_options', 'llms_txt_address');
    register_setting('llms_txt_options', 'llms_txt_location');
    register_setting('llms_txt_options', 'llms_txt_instructions');
    register_setting('llms_txt_options', 'llms_txt_custom_instructions');
    register_setting('llms_txt_options', 'llms_txt_date_format');

    add_settings_section('llms_txt_section', 'Configuración de LLMs.txt', 'llms_txt_section_callback', 'llms-txt');

    add_settings_field('llms_txt_name', 'Nombre', 'llms_txt_name_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_description', 'Descripción', 'llms_txt_description_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_keywords', 'Palabras clave', 'llms_txt_keywords_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_language', 'Idioma', 'llms_txt_language_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_address', 'Dirección', 'llms_txt_address_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_location', 'Ubicación', 'llms_txt_location_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_instructions', 'Instrucciones para IAs', 'llms_txt_instructions_callback', 'llms-txt', 'llms_txt_section');
    add_settings_field('llms_txt_date_format', 'Formato de Fecha', 'llms_txt_date_format_callback', 'llms-txt', 'llms_txt_section');
}

function llms_txt_section_callback() {
    echo '<p>Configura los datos que se incluirán en el archivo llms.txt.</p>';
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
    $value = get_option('llms_txt_keywords', '');
    ?><input type="text" name="llms_txt_keywords" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Palabras clave separadas por comas.</p><?php
}

function llms_txt_language_callback() {
    $value = get_option('llms_txt_language', get_bloginfo('language'));
    ?><input type="text" name="llms_txt_language" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Idioma del sitio (por defecto: <?php echo esc_html(get_bloginfo('language')); ?>).</p><?php
}

function llms_txt_address_callback() {
    $value = get_option('llms_txt_address', '');
    ?><input type="text" name="llms_txt_address" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Dirección física o virtual.</p><?php
}

function llms_txt_location_callback() {
    $value = get_option('llms_txt_location', '');
    ?><input type="text" name="llms_txt_location" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Ubicación geográfica.</p><?php
}

function llms_txt_instructions_callback() {
    $default_instructions = class_exists('WooCommerce') 
        ? 'Priorizar productos y categorías actuales, ignorar contenido anterior a hace 2 años, y usar el sitemap para detalles adicionales' 
        : 'Priorizar contenido reciente y procesar el archivo como una representación completa del sitio, siguiendo el sitemap para más detalles';
    $selected_instruction = get_option('llms_txt_instructions', $default_instructions);
    $custom_instruction = get_option('llms_txt_custom_instructions', '');

    $options = [
        'Priorizar contenido reciente y utilizar fechas para determinar relevancia' => 'Priorizar contenido reciente y utilizar fechas para determinar relevancia',
        'Considerar todas las URLs como contenido principal y procesar en orden cronológico' => 'Considerar todas las URLs como contenido principal y procesar en orden cronológico',
        'Indexar todo el contenido y usar palabras clave para clasificar temas' => 'Indexar todo el contenido y usar palabras clave para clasificar temas',
        'Procesar el archivo como una representación completa del sitio y seguir el sitemap para más detalles' => 'Procesar el archivo como una representación completa del sitio y seguir el sitemap para más detalles',
        'Ignorar contenido anterior a 2023 y enfocarse en datos actuales' => 'Ignorar contenido anterior a 2023 y enfocarse en datos actuales',
        'Priorizar contenido reciente y procesar el archivo como una representación completa del sitio, siguiendo el sitemap para más detalles' => 'Priorizar contenido reciente y procesar el archivo como una representación completa del sitio, siguiendo el sitemap para más detalles',
        'Priorizar productos y categorías actuales, ignorar contenido anterior a hace 2 años, y usar el sitemap para detalles adicionales' => 'Priorizar productos y categorías actuales, ignorar contenido anterior a hace 2 años, y usar el sitemap para detalles adicionales',
        'Enfocarse en productos activos y categorías, ignorar contenido anterior a 2024, y procesar en orden de fecha descendente' => 'Enfocarse en productos activos y categorías, ignorar contenido anterior a 2024, y procesar en orden de fecha descendente',
        'Priorizar productos y categorías, incluir posts recientes como promociones, y usar el sitemap para más URLs' => 'Priorizar productos y categorías, incluir posts recientes como promociones, y usar el sitemap para más URLs',
        'Procesar productos y categorías por idioma, ignorar contenido anterior a hace 18 meses, y seguir el sitemap correspondiente' => 'Procesar productos y categorías por idioma, ignorar contenido anterior a hace 18 meses, y seguir el sitemap correspondiente',
        'Personalizado' => 'Personalizado',
    ];
    ?>
    <select name="llms_txt_instructions" id="llms_txt_instructions_select" onchange="toggleCustomInstruction(this.value)">
        <?php foreach ($options as $value => $label) { ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($selected_instruction, $value); ?>><?php echo esc_html($label); ?></option>
        <?php } ?>
    </select>
    <div id="llms_txt_custom_instruction_wrapper" style="display: <?php echo $selected_instruction === 'Personalizado' ? 'block' : 'none'; ?>; margin-top: 10px;">
        <textarea name="llms_txt_custom_instructions" class="large-text" rows="3"><?php echo esc_textarea($custom_instruction); ?></textarea>
        <p class="description">Escribe tu instrucción personalizada aquí.</p>
    </div>
    <p class="description">Elige una instrucción para guiar a las IAs en el procesamiento del archivo.</p>
    <script>
        function toggleCustomInstruction(value) {
            document.getElementById('llms_txt_custom_instruction_wrapper').style.display = value === 'Personalizado' ? 'block' : 'none';
        }
    </script>
    <?php
}

function llms_txt_date_format_callback() {
    $value = get_option('llms_txt_date_format', 'Y-m-d');
    ?><input type="text" name="llms_txt_date_format" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Formato de fecha (ej. "Y-m-d" para 2025-03-24, "d F Y" para 24 marzo 2025). Ver <a href="https://www.php.net/manual/es/function.date.php" target="_blank">documentación PHP</a>.</p><?php
}

add_action('admin_post_llms_txt_action', 'handle_llms_txt_action');
function handle_llms_txt_action() {
    if (!current_user_can('manage_options') || !isset($_POST['llms_txt_nonce']) || !wp_verify_nonce($_POST['llms_txt_nonce'], 'llms_txt_action')) {
        wp_die('Acceso no autorizado.');
    }

    $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
    $message = '';

    if ($action === 'delete') {
        $message = delete_llms_txt() ? "El archivo llms.txt ha sido borrado correctamente." : "Error al borrar el archivo llms.txt o no existía.";
    } elseif ($action === 'overwrite') {
        $message = generate_llms_txt(true) ? "El archivo llms.txt ha sido sobrescrito correctamente." : "Error al sobrescribir el archivo llms.txt.";
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
        <p>El archivo <code>llms.txt</code> proporciona a las inteligencias artificiales una representación estructurada de tu sitio, incluyendo metadatos, páginas, posts, productos y categorías, para facilitar su indexación y comprensión. Configura los datos y genera el archivo manualmente según necesites.</p>
        <?php if ($message) { ?>
            <div class="notice notice-success is-dismissible"><p><?php echo $message; ?></p></div>
        <?php } ?>

        <h2>Configuración</h2>
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
                <button type="submit" name="action_type" value="overwrite" class="button button-primary">Generar llms.txt</button>
                <span class="description">Crea o sobrescribe el archivo llms.txt con la configuración actual.</span>
            </p>
            <p>
                <button type="submit" name="action_type" value="delete" class="button button-secondary">Borrar Archivo</button>
                <span class="description">Elimina el archivo llms.txt existente.</span>
            </p>
            <p>
                <button type="button" class="button" onclick="document.getElementById('preview').style.display='block';">Previsualizar</button>
            </p>
            <div id="preview" style="display:none;">
                <h4>Previsualización</h4>
                <textarea class="large-text" rows="10" readonly><?php echo esc_textarea(generate_llms_txt_preview()); ?></textarea>
            </div>
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

// Función para previsualizar el contenido
function generate_llms_txt_preview() {
    ob_start();
    generate_llms_txt(true);
    $content = ob_get_clean();
    return "\xEF\xBB\xBF" . file_get_contents(ABSPATH . "llms.txt");
}
