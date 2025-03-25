# LLMs.txt Generator - Changelog

Este archivo documenta las versiones del plugin "LLMs.txt Generator", creado para generar un archivo `llms.txt` optimizado para modelos de lenguaje en WordPress. A continuación, se detallan los cambios, funcionalidades y notas por versión.

## Versión 1.0 (Base Inicial)
- **Fecha**: No especificada (versión inicial teórica).
- **Funcionalidades**:
  - Generación básica de un archivo `llms.txt` en la raíz del sitio con información del sitio (nombre, descripción) y hasta 5 posts recientes.
  - Activación del plugin genera el archivo automáticamente.
  - Regeneración del archivo al guardar un post mediante el hook `save_post`.
- **Notas**: Versión inicial simple sin interfaz de administración ni opciones avanzadas.

## Versión 1.2
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Introducción de la opción de sobrescritura automática configurable (`llms_txt_overwrite`).
  - Añadido un panel de administración en `Ajustes > LLMs.txt` para controlar si el archivo se sobrescribe automáticamente.
  - Estructura del archivo en Markdown con secciones "Acerca de", "Contenido Principal" y "Recursos Adicionales".
- **Funcionalidades**:
  - Campos estáticos en "Acerca de": Nombre, Descripción, Palabras clave, Idioma, Ubicación, URL.
  - Hasta 5 posts en "Contenido Principal" con título, enlace y extracto.
- **Notas**: Primera versión con control de usuario y estructura optimizada para LLMs.

## Versión 1.3
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Añadida marca temporal (`Última generación`) en el encabezado para verificar actualizaciones.
  - Implementación de logs de depuración (`llms_txt_log`) para rastrear la generación del archivo.
- **Funcionalidades**:
  - Registro en `error_log` de intentos de generación, éxito o errores (como permisos).
- **Notas**: Mejora para diagnosticar problemas de sobrescritura.

## Versión 1.4
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Ampliación del panel de administración con sección "Gestión Manual".
- **Funcionalidades**:
  - Botones para "Sobrescribir Ahora" (forzando generación) y "Borrar Archivo".
  - Mensajes de éxito/error tras acciones manuales.
  - Información del sistema: ubicación y estado del archivo.
- **Notas**: Mayor control manual y seguridad con nonces en las acciones.

## Versión 1.5
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Introducción de detección de plugins SEO (Yoast, AIOSEO, Rank Math, SEOPress).
  - Eliminado límite de 5 posts en "Contenido Principal".
- **Funcionalidades**:
  - Uso de títulos, descripciones y palabras clave SEO en los posts si un plugin está activo.
  - Indicación de la fuente SEO en el archivo (`> Fuente SEO:`).
  - Mostrar enlace al sitemap XML del plugin SEO en el panel.
- **Notas**: Primera versión con integración SEO, pero causó un error crítico en algunos entornos.

## Versión 1.5.1
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Corrección de errores: eliminación de funciones anónimas para compatibilidad con PHP antiguo.
  - Simplificación de la detección SEO usando funciones más confiables (`function_exists`).
  - Manejo de valores vacíos en títulos y descripciones.
- **Funcionalidades**:
  - Mantiene integración SEO y sitemap, pero más robusta.
- **Notas**: Resolución del error crítico reportado.

## Versión 1.6
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Campos de "Acerca de" ahora personalizables mediante campos de texto en el panel.
- **Funcionalidades**:
  - Campos editables: Nombre, Descripción, Palabras clave, Idioma, Dirección, Ubicación.
  - Valores por defecto basados en WordPress o estáticos si no se personalizan.
- **Notas**: Mayor flexibilidad para los usuarios.

## Versión 1.7
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Eliminada la línea "Fuente SEO" del archivo.
  - "Contenido Principal" dividido en "Páginas", "Posts" y "Categorías de Producto".
  - Campos vacíos en "Acerca de" no se imprimen.
- **Funcionalidades**:
  - Páginas y Posts ilimitados con título, URL, extracto y palabras clave (si aplica).
  - Categorías de producto (hasta 5) si WooCommerce está activo.
- **Notas**: Estructura más clara y específica para diferentes tipos de contenido.

## Versión 1.8
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Eliminado el límite de impresión para Páginas y Posts.
- **Funcionalidades**:
  - Todas las páginas publicadas y todos los posts publicados se incluyen.
  - Categorías de producto mantienen límite de 5.
- **Notas**: Respuesta a la solicitud de mostrar todo el contenido disponible.

## Versión 1.9
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Simplificación de Páginas y Posts: solo título, URL y fecha de publicación.
  - Eliminados extractos y palabras clave de Páginas y Posts.
- **Funcionalidades**:
  - Formato en Páginas y Posts: `- [Título](URL) - Fecha (YYYY-MM-DD)`.
  - Mantiene detección SEO para títulos, panel de administración y campos personalizables.
- **Notas**: Versión más ligera y enfocada en metadatos básicos.

## Versión 2.0
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Eliminado límite para Categorías de Producto (todas incluidas).
  - Simplificación de la detección SEO (solo títulos).
  - Estructura mejorada con nuevas secciones y opciones avanzadas.
- **Funcionalidades**:
  - Nueva sección "Productos" con todos los productos de WooCommerce (título, URL, fecha).
  - Formato de fecha personalizable mediante campo en el panel.
  - Metadatos adicionales: Autor en Páginas y Posts; Categorías en Posts.
  - Sección "## Sitemap" con URL del sitemap XML si un plugin SEO está activo.
  - Campo "Instrucciones" en el panel, mostrado como `> Instrucciones:`.
  - Validación y sanitización de texto.
  - Vista previa por idioma en el panel.
  - Soporte multilingüe con archivos separados (ej. `llms-es.txt`).
- **Notas**: Versión más completa y robusta, sin límites configurables por solicitud del usuario.

## Versión 2.1
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Campo "Instrucciones" convertido en un `<select>` con opciones predefinidas y personalizable.
- **Funcionalidades**:
  - 11 opciones de instrucciones (6 genéricas, 4 para e-commerce, 1 personalizada).
  - Opción "Personalizado" muestra un `textarea` para entrada manual.
  - Valor por defecto ajustado según presencia de WooCommerce.
- **Notas**: Mayor facilidad para seleccionar instrucciones optimizadas para IAs.

## Versión 2.2
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Eliminado soporte multilingüe; ahora solo genera un único archivo `llms.txt`.
  - Eliminada sección "## Recursos Adicionales" del archivo.
  - Eliminada opción "Sobrescribir Automáticamente" del panel y lógica.
  - Añadida descripción en el panel sobre el propósito del archivo `llms.txt`.
  - Eliminado campo "Autor" de Páginas, Posts, Productos y Categorías.
  - Añadida "Descripción" en Páginas, Posts, Productos y Categorías (si está definida, desde SEO o nativa).
  - URLs de `sitemap.xml` y `robots.txt` movidas a la cabecera tras las instrucciones.
- **Funcionalidades**:
  - Generación manual con botón "Generar llms.txt" o automática al activar/guardar contenido.
  - Descripciones extraídas de metadatos SEO (si disponibles) o nativas (excerpts, short descriptions).
  - Encabezado incluye: Nombre, Instrucciones, Sitemap, Robots y Última Generación.
  - Estructura: Acerca de, Páginas, Posts, Productos, Categorías de Producto.
- **Notas**: 
  - Simplificación para un único archivo `llms.txt`, eliminando conflictos con archivos multilingües.
  - Enfocado en descripciones relevantes para IAs, con URLs clave en la cabecera.

## Versión 2.2.1
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Añadida codificación UTF-8 con BOM (`\xEF\xBB\xBF`) al generar el archivo `llms.txt`.
  - Actualizada la previsualización para incluir BOM.
- **Funcionalidades**:
  - Soluciona problemas con tildes y caracteres especiales (ej. á, é, ñ) en el archivo generado.
  - Mantiene todas las características de la versión 2.2 sin cambios adicionales en la estructura o lógica.
- **Notas**: 
  - Respuesta a reportes de problemas con caracteres acentuados en varias webs.
  - El BOM asegura que los clientes identifiquen correctamente la codificación UTF-8.

## Versión 2.2.2
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Añadida exclusión de contenido marcado como `noindex` en Páginas, Posts, Productos y Categorías de Producto.
  - Actualizada la detección de plugins SEO para incluir claves de metadatos `noindex` y `term_noindex`.
  - Nueva función `llms_txt_is_noindex` para verificar el estado `noindex` según el plugin SEO activo.
- **Funcionalidades**:
  - Solo se incluye contenido indexable (sin `noindex`) en el archivo `llms.txt`, respetando las configuraciones SEO.
  - Compatible con Yoast SEO, Rank Math, AIOSEO y SEOPress para posts y términos.
  - Sin cambios en la estructura del archivo ni en otras funcionalidades.
- **Notas**: 
  - Si no hay plugin SEO activo, no se aplica filtro de `noindex` y se incluye todo el contenido publicado.
  - Alinea el archivo `llms.txt` con las preferencias de indexación del sitio.

## Versión 2.3 (Actual)
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Panel de administración: Campos de configuración reorganizados en una tabla de 2 columnas para reducir espacio visual.
  - Campo "Descripción" convertido en un `<textarea>` más amplio (5 filas) para mayor visibilidad.
  - Añadidos campos para URLs específicas por idioma (español, inglés, francés) con valores por defecto basados en `home_url()`.
  - Especificada compatibilidad en el encabezado: WordPress 5.0+ y PHP 7.4+.
  - Código completamente comentado con PHPDoc y comentarios en línea para facilitar la comprensión.
  - Aplicadas buenas prácticas: sanitización y escapado en entradas/salidas, uso de nonces, modularidad y legibilidad.
  - Añadido soporte básico para traducción con `Text Domain: llms-txt-generator`.
- **Funcionalidades**:
  - Nuevas opciones `llms_txt_url_es`, `llms_txt_url_en`, `llms_txt_url_fr` incluidas en la sección "Acerca de" del archivo.
  - Interfaz de administración optimizada y más segura.
  - Mantiene exclusión de `noindex`, codificación UTF-8 con BOM y todas las características previas.
- **Notas**: 
  - Mejora la usabilidad del panel y la flexibilidad para sitios multilingües sin generar archivos separados.
  - Compatible con instalaciones modernas de WordPress y PHP; probado para seguridad y rendimiento.

## Características Generales
- **Generación**: Automática al activar el plugin o guardar un post; manual mediante "Generar llms.txt".
- **Gestión**: Panel en `Ajustes > LLMs.txt` con configuración en tabla de 2 columnas y acciones manuales.
- **SEO**: Integración con plugins SEO para títulos, descripciones y estado `noindex`; detección de sitemap.
- **WooCommerce**: Soporte completo para productos y categorías con descripciones, excluyendo `noindex`.
- **Codificación**: UTF-8 con BOM para compatibilidad con caracteres especiales.
- **Multilingüe**: URLs por idioma (español, inglés, francés) configurables en el panel.
- **Depuración**: Logs en `wp-content/debug.log` si `WP_DEBUG` está activo.

## Notas Finales
- **Rendimiento**: Sin límites, el archivo puede crecer en sitios grandes; considerar caché si es necesario.
- **Compatibilidad**: Probado con WordPress 5.0+, PHP 7.4+, WooCommerce y plugins SEO populares.
- **Futuras Mejoras**: Posibilidad de añadir soporte para CPTs específicos, filtros de contenido o internacionalización completa.

Última actualización: 24 de marzo de 2025.
