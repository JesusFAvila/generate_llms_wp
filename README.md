=== LLMs.txt Generator ===
Contributors: jesusfa
Tags: llms, ai, metadata, seo, woocommerce
Requires at least: 5.0
Tested up to: 6.7.2
Stable tag: 2.4
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Genera un archivo llms.txt optimizado para modelos de lenguaje con metadatos del sitio, excluyendo contenido noindex.

== Description ==
LLMs.txt Generator crea un archivo `llms.txt` en la raíz de tu sitio WordPress, proporcionando a las IAs una representación estructurada de tu contenido. Incluye metadatos como nombre, descripción, URLs por idioma, redes sociales, contacto, páginas, posts, productos y categorías (si usas WooCommerce), respetando las etiquetas `noindex` de plugins SEO como Yoast, Rank Math, AIOSEO o SEOPress.

Características:
- Configuración personalizable desde Ajustes > LLMs.txt.
- Soporte para redes sociales, teléfono, email y página de contacto.
- Exclusión automática de contenido con `noindex`.
- Compatible con WooCommerce y plugins SEO populares.
- Codificación UTF-8 con BOM para caracteres especiales.

== Installation ==
1. Descarga el plugin desde el directorio de WordPress.org.
2. Sube la carpeta `llms-txt-generator` a `/wp-content/plugins/` o instálalo desde el panel de WordPress.
3. Activa el plugin desde "Plugins" en el administrador.
4. Configura las opciones en "Ajustes > LLMs.txt" y genera el archivo manualmente.

== Frequently Asked Questions ==
= ¿Qué hace el archivo llms.txt? =
Proporciona a las IAs un resumen estructurado de tu sitio para mejorar su comprensión e indexación.

= ¿Es compatible con mi plugin SEO? =
Sí, detecta y respeta `noindex` de Yoast, Rank Math, AIOSEO y SEOPress.

= ¿Puedo personalizar las URLs? =
Sí, incluye campos para URLs por idioma, redes sociales y más.

== Screenshots ==
1. Panel de configuración en Ajustes > LLMs.txt.
2. Ejemplo de archivo llms.txt generado.

== Changelog ==
= 2.4 =
* Idioma por defecto cambiado a "Español".
* Añadidos campos para redes sociales, Local Business, contacto, teléfono y email.
* URLs de idiomas vacías por defecto.

= 2.3 =
* Campos reorganizados en tabla de 2 columnas.
* Soporte básico para traducción.

[Ver changelog completo en el plugin.]

== Upgrade Notice ==
= 2.4 =
Actualiza para añadir soporte a redes sociales y datos de contacto.

== License ==
Este plugin está licenciado bajo GPLv2 o posterior. Puedes modificarlo y distribuirlo libremente bajo los mismos términos.
