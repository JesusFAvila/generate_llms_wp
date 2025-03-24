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
- **Fecha**: 24 de marzo de 2025 (inicio documentado).
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

## Versión 1.9 (Actual)
- **Fecha**: 24 de marzo de 2025.
- **Cambios**:
  - Simplificación de Páginas y Posts: solo título, URL y fecha de publicación.
  - Eliminados extractos y palabras clave de Páginas y Posts.
- **Funcionalidades**:
  - Formato en Páginas y Posts: `- [Título](URL) - Fecha (YYYY-MM-DD)`.
  - Mantiene detección SEO para títulos, panel de administración y campos personalizables.
- **Notas**: Versión más ligera y enfocada en metadatos básicos.

## Características Generales
- **Generación**: Automática al activar el plugin o guardar un post; manual mediante "Sobrescribir Ahora".
- **Gestión**: Panel en `Ajustes > LLMs.txt` con opciones automáticas y manuales.
- **SEO**: Integración con plugins SEO para títulos (opcional).
- **WooCommerce**: Soporte para categorías de producto si está instalado.
- **Depuración**: Logs en `wp-content/debug.log` si `WP_DEBUG` está activo.

## Notas Finales
- **Rendimiento**: En sitios con muchas páginas/posts, el archivo puede crecer significativamente. Considerar límites opcionales en futuras versiones.
- **Compatibilidad**: Probado en WordPress estándar con WooCommerce y plugins SEO populares.
- **Futuras Mejoras**: Posibilidad de añadir límites configurables, formato de fecha personalizado o soporte para más tipos de contenido (ej. productos individuales).

Última actualización: 24 de marzo de 2025.
