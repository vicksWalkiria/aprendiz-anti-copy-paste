=== Anti Copy-Paste – Aprendiz de SEO ===
Contributors: aprendizdeseo
Donate link: https://aprendizdeseo.top
Tags: copy, protect, selection, user-select, content-protection
Requires at least: 5.2
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bloquea la selección de texto (user-select: none) para dificultar el copiado. Permite excepciones por rol, tipo de contenido y selectores.

== Description ==

Este plugin añade una clase al `<body>` y aplica `user-select: none` para impedir que se seleccione texto con el ratón. Incluye:

* Activación/desactivación global.
* Exclusión por **roles** (ej.: administrador, editor).
* Exclusión por **tipos de contenido** (post, page, CPT).
* Lista de **selectores permitidos** donde sí se puede seleccionar (por defecto: inputs, textarea, select, `[contenteditable]`, `pre`, `code`, `.wp-block-code`).
* Opcional: bloquear **menú contextual** (clic derecho).
* Opcional: intentar bloquear evento **copiar** (Ctrl/Cmd+C).

> Nota: Esta protección es disuasoria; usuarios avanzados pueden desactivarla con herramientas del navegador.

== Installation ==

1. Sube la carpeta `anti-copy-paste-aprendiz-de-seo` al directorio `/wp-content/plugins/`.
2. Activa el plugin desde **Plugins** en tu Escritorio de WordPress.
3. Ve a **Ajustes → Anti Copy-Paste** para configurarlo.

== Frequently Asked Questions ==

= ¿Bloquea 100% el copiado? =
No. Es una medida disuasoria. Usuarios con conocimientos pueden evadirla.

= ¿Afecta al editor del admin? =
No. Solo actúa en el frontal. Además, por defecto excluye a administradores y editores.

= ¿Puedo permitir selección en zonas concretas? =
Sí, usando la lista de selectores permitidos (por defecto incluye campos de formulario y bloques de código).

== Screenshots ==

1. Pantalla de ajustes con opciones de exclusión y selectores permitidos.

== Changelog ==

= 1.0.0 =
* Versión inicial.

== Upgrade Notice ==

= 1.0.0 =
Primera versión estable.

