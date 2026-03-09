# Documentación Técnica y Manual de Usuario
# Zalbi Aisia eta Abentura — `zalbi.eu`

> **Versión:** 1.0  
> **Fecha:** Marzo 2026  
> **Repositorio:** [github.com/Garridoparrayeray/zalbi-theme](https://github.com/Garridoparrayeray/zalbi-theme)  
> **Autor del tema:** Yeray Garrido  
> **Redacción técnica:** Documentación interna del proyecto

---

## Tabla de Contenidos

1. [Resumen del Proyecto](#1-resumen-del-proyecto)
2. [Arquitectura y Entorno de Trabajo](#2-arquitectura-y-entorno-de-trabajo)
3. [Estructura del Tema WordPress](#3-estructura-del-tema-wordpress)
4. [Estructura de Datos: CPTs y Taxonomías](#4-estructura-de-datos-cpts-y-taxonomías)
5. [Guía de Rendimiento y Caché (PageSpeed 100/100)](#5-guía-de-rendimiento-y-caché-pagespeed-100100)
6. [Seguridad y Accesibilidad](#6-seguridad-y-accesibilidad)
7. [Manual de Usuario (Administrador)](#7-manual-de-usuario-administrador)
8. [Solución de Problemas Frecuentes](#8-solución-de-problemas-frecuentes)

---

## 1. Resumen del Proyecto

### 1.1 ¿Qué es Zalbi Aisia eta Abentura?

**Zalbi Aisia eta Abentura** (`zalbi.eu`) es el sitio web corporativo de una empresa especializada en el **alquiler de hinchables y organización de eventos de ocio** en Bizkaia y Euskadi. Con más de 15 años de experiencia, la empresa presta servicio a ayuntamientos, colegios, asociaciones y particulares, ofreciendo desde castillos hinchables clásicos hasta atracciones acuáticas, deportivas y animación musical.

La web cumple tres objetivos fundamentales:

- **Catálogo online:** Presentar todos los productos de alquiler organizados por categorías (hinchables, acuáticos, deportivos, juegos).
- **Sección de eventos:** Mostrar los servicios de animación y organización de eventos.
- **Captación de contactos:** Facilitar el presupuesto y el contacto directo con la empresa a través de formularios.

### 1.2 Tech Stack

| Capa | Tecnología |
|------|-----------|
| **CMS** | WordPress (última versión estable) |
| **Tema** | Custom Theme a medida basado en `_s` (Underscores) |
| **Lenguajes del tema** | PHP 8.x, HTML5 semántico, CSS3, JavaScript (Vanilla) |
| **Internacionalización** | Plugin Polylang (Castellano 🇪🇸 / Euskera 🏴) |
| **Formularios** | Contact Form 7 |
| **Optimización de caché** | LiteSpeed Cache + QUIC.cloud |
| **Hosting** | Hostinger (servidor LiteSpeed) |
| **Control de versiones** | Git / GitHub |
| **CI/CD** | Webhook de auto-deploy (GitHub → Hostinger) |
| **Gestor de paquetes (dev)** | npm + Composer |

---

## 2. Arquitectura y Entorno de Trabajo

### 2.1 Visión General del Flujo CI/CD

El proyecto implementa un flujo de trabajo profesional que conecta el entorno de desarrollo local con el servidor de producción de forma **completamente automatizada**. El diagrama de alto nivel es el siguiente:

```
┌─────────────────────┐       git push       ┌──────────────────────┐
│  Entorno Local       │ ──────────────────► │  GitHub Repository    │
│  (VS Code / MAMP)   │                      │  (rama: main)         │
└─────────────────────┘                      └──────────┬───────────┘
                                                         │
                                                  Webhook Event
                                                  (push trigger)
                                                         │
                                                         ▼
                                             ┌──────────────────────┐
                                             │  Hostinger Servidor  │
                                             │  (LiteSpeed + WP)    │
                                             │  Auto-pull del repo  │
                                             └──────────────────────┘
```

### 2.2 Detalles del Flujo de Trabajo

#### Paso 1: Desarrollo Local

Todos los cambios al tema se realizan en el **entorno local**:

- Se editan los archivos PHP, CSS o JS del tema `zalbi-theme` en el editor de código.
- Se prueba visualmente en `localhost` con un servidor local (MAMP, LocalWP o similar).
- Los archivos del tema se encuentran en: `wp-content/themes/zalbi-theme/`.

#### Paso 2: Commit y Push a GitHub

Una vez validados los cambios, se suben al repositorio remoto:

```bash
# Añadir los cambios al staging area
git add .

# Crear un commit descriptivo
git commit -m "feat: actualiza estilos del catálogo de hinchables"

# Subir a la rama principal
git push origin main
```

> **Convención de commits recomendada:** Usar prefijos semánticos como `feat:` (nueva funcionalidad), `fix:` (corrección de error), `style:` (cambios de CSS/UI), `docs:` (documentación).

#### Paso 3: Auto-Deploy vía Webhook

En el momento en que GitHub recibe el `push`, **dispara automáticamente un Webhook HTTP** hacia Hostinger. Este webhook está configurado en:

- **GitHub:** `Settings → Webhooks → Payload URL` (URL del endpoint en Hostinger).
- **Hostinger:** Script receptor que ejecuta un `git pull` en el directorio del tema en producción.

El proceso completo tarda **menos de 15 segundos** desde el push hasta que los cambios están visibles en producción.

#### ⚠️ Importante: Purga de Caché Post-Deploy

Después de cualquier deploy, **es obligatorio purgar la caché de LiteSpeed** para que los cambios sean visibles para los visitantes. Ver la sección [7.3 Cómo Purgar la Caché](#73-cómo-purgar-la-caché-de-litespeed) para instrucciones detalladas.

### 2.3 Estructura de Directorios del Tema

```
zalbi-theme/
├── img/                    # Imágenes estáticas del tema (logo, iconos SVG)
├── inc/                    # Archivos PHP de soporte (funciones auxiliares)
│   ├── custom-header.php
│   ├── template-functions.php
│   └── template-tags.php
├── js/                     # Scripts JavaScript del tema
│   └── navigation.js       # Menú hamburguesa responsive
├── languages/              # Archivos de traducción (.pot, .po, .mo)
├── template-parts/         # Partes de plantilla reutilizables
├── 404.php                 # Página de error 404
├── archive.php             # Plantilla de archivos
├── footer.php              # Pie de página global
├── front-page.php          # Portada (homepage)
├── functions.php           # ⭐ Core del tema: CPTs, hooks, seguridad, assets
├── header.php              # Cabecera global
├── index.php               # Plantilla fallback
├── page-catalogo.php       # Página del catálogo de hinchables
├── page-eventos.php        # Página de eventos
├── page.php                # Plantilla de páginas genéricas
├── single-hinchable.php    # Plantilla individual de hinchable
├── single-evento.php       # Plantilla individual de evento
├── single.php              # Plantilla fallback para posts individuales
├── style.css               # ⭐ Hoja de estilos principal + cabecera del tema
├── style-rtl.css           # Estilos para idiomas RTL (right-to-left)
├── composer.json           # Dependencias PHP (PHPCS)
└── package.json            # Dependencias Node.js (compilación SASS)
```

---

## 3. Estructura del Tema WordPress

### 3.1 El archivo `functions.php`

Este es el archivo más importante del tema. Centraliza toda la lógica de registro, optimización y seguridad. Sus responsabilidades principales son:

1. **Registro de soporte del tema** (`add_theme_support`): thumbnails, HTML5, título dinámico.
2. **Registro de Custom Post Types** (Hinchables y Eventos).
3. **Registro de Taxonomías** (Tipos de Hinchable).
4. **Carga asíncrona de fuentes e iconos** (FontAwesome, Google Fonts).
5. **Inyección de cabeceras HTTP de seguridad**.
6. **Enqueue de scripts y estilos**.

### 3.2 Jerarquía de Plantillas WordPress

WordPress sigue una jerarquía de resolución de plantillas. Para `zalbi-theme`, las rutas más relevantes son:

| URL de la web | Plantilla utilizada |
|---|---|
| `zalbi.eu/` | `front-page.php` |
| `zalbi.eu/catalogo/` | `page-catalogo.php` |
| `zalbi.eu/eventos/` | `page-eventos.php` |
| `zalbi.eu/catalogo/nombre-hinchable/` | `single-hinchable.php` |
| `zalbi.eu/eventos/nombre-evento/` | `single-evento.php` |
| Cualquier otra página | `page.php` |
| Post de blog (si aplica) | `single.php` |
| URL no encontrada | `404.php` |

---

## 4. Estructura de Datos: CPTs y Taxonomías

### 4.1 Custom Post Type: Hinchables

Los productos de alquiler se gestionan como un **Custom Post Type (CPT)** personalizado, separado de los posts y páginas estándar de WordPress.

| Parámetro | Valor |
|-----------|-------|
| **Slug interno** | `hinchable` |
| **Slug en URL** | `/catalogo/nombre-del-hinchable/` |
| **Soporte** | Título, Editor, Miniatura, Extracto |
| **Visibilidad** | Público, indexable por buscadores |
| **Plantilla individual** | `single-hinchable.php` |

**Registro en `functions.php`:**

```php
function zalbi_register_cpt_hinchables() {
    $labels = array(
        'name'               => __( 'Hinchables', 'zalbi-theme' ),
        'singular_name'      => __( 'Hinchable', 'zalbi-theme' ),
        'add_new_item'       => __( 'Añadir nuevo hinchable', 'zalbi-theme' ),
        'edit_item'          => __( 'Editar hinchable', 'zalbi-theme' ),
    );
    $args = array(
        'labels'      => $labels,
        'public'      => true,
        'has_archive' => true,
        'rewrite'     => array( 'slug' => 'catalogo' ),
        'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'menu_icon'   => 'dashicons-star-filled',
    );
    register_post_type( 'hinchable', $args );
}
add_action( 'init', 'zalbi_register_cpt_hinchables' );
```

### 4.2 Taxonomía: Tipos de Hinchable

Los hinchables se clasifican mediante una **taxonomía jerárquica** (funciona igual que las categorías de WordPress) asociada exclusivamente al CPT `hinchable`.

| Parámetro | Valor |
|-----------|-------|
| **Slug interno** | `tipo_hinchable` |
| **Slug en URL** | `/tipo/nombre-categoria/` |
| **Tipo** | Jerárquica (como categorías) |
| **Asociada a** | CPT `hinchable` |

**Términos de taxonomía actuales:**

| Término | Slug URL | Descripción |
|---------|----------|-------------|
| Hinchables | `hinchables` | Castillos y toboganes clásicos |
| Acuáticos | `acuaticos` | Hinchables de agua y espuma |
| Deportivos | `deportivos` | Atracciones para la actividad física |
| Juegos | `juegos` | Juegos de mesa y actividades lúdicas |

**Registro en `functions.php`:**

```php
function zalbi_register_taxonomy_tipos() {
    $labels = array(
        'name'          => __( 'Tipos de Hinchable', 'zalbi-theme' ),
        'singular_name' => __( 'Tipo de Hinchable', 'zalbi-theme' ),
    );
    $args = array(
        'labels'       => $labels,
        'public'       => true,
        'hierarchical' => true, // Jerárquica = como categorías
        'rewrite'      => array( 'slug' => 'tipo' ),
    );
    register_taxonomy( 'tipo_hinchable', array( 'hinchable' ), $args );
}
add_action( 'init', 'zalbi_register_taxonomy_tipos' );
```

> **Nota sobre URLs de filtrado:** Las categorías del catálogo se pueden filtrar directamente desde la URL usando el parámetro `?cat=`. Por ejemplo: `zalbi.eu/catalogo/?cat=acuaticos`. Esto permite enlazar directamente a una categoría específica desde la homepage o desde redes sociales.

### 4.3 Custom Post Type: Eventos

Los servicios de animación y organización de eventos se gestionan con un segundo CPT.

| Parámetro | Valor |
|-----------|-------|
| **Slug interno** | `evento` |
| **Slug en URL** | `/eventos/nombre-del-evento/` |
| **Soporte** | Título, Editor, Miniatura, Extracto |
| **Plantilla individual** | `single-evento.php` |

---

## 5. Guía de Rendimiento y Caché (PageSpeed 100/100)

> **Objetivo alcanzado:** Puntuación **100/100 en Google PageSpeed Insights (móvil)**, el estándar más exigente de medición de rendimiento web.

### 5.1 Plugin Principal: LiteSpeed Cache

LiteSpeed Cache (LSCache) es el plugin de optimización elegido por su **integración nativa con el servidor web LiteSpeed** de Hostinger. A diferencia de plugins como W3 Total Cache o WP Rocket, LiteSpeed Cache tiene acceso directo al servidor, lo que le permite operar a nivel de sistema y no solo a nivel de aplicación PHP.

**Configuración activa:**

| Función | Estado | Descripción |
|---------|--------|-------------|
| Caché de página completa | ✅ Activo | Sirve HTML estático sin ejecutar PHP |
| Minificación CSS | ✅ Activo | Elimina espacios, comentarios y caracteres innecesarios |
| Minificación JS | ✅ Activo | Reduce el tamaño de los archivos JavaScript |
| Combinación CSS | ✅ Activo | Fusiona múltiples hojas de estilo en un solo archivo |
| Combinación JS | ✅ Activo | Fusiona múltiples scripts en un solo archivo |
| Lazy Load de imágenes | ✅ Activo | Carga imágenes solo cuando entran en el viewport |
| Add Missing Sizes | ✅ Activo | Genera automáticamente los tamaños de imagen faltantes |
| Conversión a WebP | ✅ Activo | Convierte imágenes a WebP on-the-fly para formatos modernos |
| JavaScript Diferido | ✅ Activo | Retrasa la carga de JS no crítico para mejorar el LCP |
| CSS Crítico (UCSS) | ✅ Activo | Solo carga el CSS necesario para el contenido visible |

### 5.2 Gestión del CSS Crítico (UCSS) con QUIC.cloud

El **CSS crítico** (o "above-the-fold CSS") es la técnica que garantiza que el contenido visible sin hacer scroll se renderice instantáneamente, sin bloquear el hilo principal del navegador.

#### ¿Cómo funciona en este proyecto?

1. LiteSpeed Cache genera una versión "esquelética" de la página y la envía al servicio de **QUIC.cloud** (la CDN y servicio de optimización oficial de LiteSpeed).
2. QUIC.cloud analiza el CSS completo y extrae únicamente las reglas necesarias para renderizar la primera pantalla (el "above the fold").
3. El CSS crítico resultante se inyecta **inline** directamente en el `<head>` del HTML.
4. El CSS completo se carga de forma **diferida y asíncrona** (no bloqueante).

#### ⚠️ AVISO CRÍTICO: No mezclar CDN de QUIC.cloud con el CDN de Hostinger

Este es el punto de configuración más delicado del proyecto. Hostinger ofrece su propio CDN integrado en el panel de control (Cloudflare o su CDN propio). **Activar ambos simultáneamente provoca conflictos que destruyen la puntuación de PageSpeed y pueden romper el CSS de la web.**

**¿Por qué son incompatibles?**

- El CDN de Hostinger sirve los assets desde sus propios servidores con sus propias cabeceras de caché.
- QUIC.cloud también intenta cachear y optimizar esos mismos assets desde sus servidores.
- El resultado son **conflictos de caché en capas**, donde un CDN sirve versiones antiguas que el otro no puede invalidar, generando CSS desactualizado o JavaScript roto.

**Regla de oro:**

> **Usar QUIC.cloud como única capa de CDN/optimización. Mantener el CDN de Hostinger desactivado.**

| Servicio | Estado correcto |
|----------|----------------|
| QUIC.cloud (vía LiteSpeed Cache) | ✅ Activo |
| CDN de Hostinger | ❌ Desactivado |

### 5.3 JavaScript Diferido y Exclusiones Estrictas

El JavaScript diferido (`defer`) es esencial para conseguir un **Time to Interactive (TTI)** bajo, ya que impide que los scripts bloqueen el renderizado del HTML. Sin embargo, diferir todos los scripts indiscriminadamente rompe funcionalidades esenciales.

#### Exclusiones configuradas en LiteSpeed Cache

Las siguientes rutas y scripts están **excluidos del proceso de diferido** para evitar errores en consola:

```
wp-includes/js
jquery.min.js
contact-form-7
```

**Justificación de cada exclusión:**

| Script excluido | Razón |
|----------------|-------|
| `wp-includes/js` | Contiene scripts del núcleo de WordPress (como `wp-embed`) que deben ejecutarse sincrónicamente para no romper funcionalidades internas del CMS. |
| `jquery.min.js` | jQuery es una dependencia de Contact Form 7 y de otros scripts del tema. Si se difiere, los scripts que dependen de él se ejecutan antes que él, lanzando `ReferenceError: $ is not defined` en la consola. |
| `contact-form-7` | Los scripts de CF7 gestionan la validación AJAX de formularios. Si se difieren, los formularios pueden fallar silenciosamente o perder la validación del lado del cliente. |

### 5.4 Carga Asíncrona de Fuentes e Iconos

Google Fonts y FontAwesome son recursos externos que, cargados de forma estándar (con `<link rel="stylesheet">`), **bloquean el renderizado** hasta que el navegador descarga y procesa la hoja de estilos.

La solución implementada utiliza el truco de `media="print"` para convertir la carga en completamente no bloqueante:

**Implementación en `functions.php`:**

```php
/**
 * Carga asíncrona de hojas de estilo externas (Google Fonts, FontAwesome).
 * Utiliza el truco media="print" para evitar el bloqueo del renderizado.
 * El atributo onload="this.media='all'" aplica los estilos una vez descargados.
 */
function zalbi_async_style_loader( $html, $handle, $href, $media ) {
    // Solo aplicar a estos handles específicos
    $async_handles = array( 'zalbi-google-fonts', 'zalbi-fontawesome' );

    if ( in_array( $handle, $async_handles, true ) ) {
        // El truco: media="print" hace que el navegador lo descargue
        // con baja prioridad (no bloqueante). onload cambia el media a "all"
        // para aplicar los estilos cuando la descarga termina.
        $html = '<link rel="stylesheet" id="' . $handle . '-css" href="' . $href . '" media="print" onload="this.media=\'all\'" />';
        // Fallback para navegadores con JS desactivado
        $html .= '<noscript><link rel="stylesheet" href="' . $href . '" /></noscript>';
    }

    return $html;
}
add_filter( 'style_loader_tag', 'zalbi_async_style_loader', 10, 4 );
```

**¿Cómo funciona el truco `media="print"`?**

1. El navegador descarga la hoja de estilos con `media="print"`, lo que significa que la considera solo relevante para impresión.
2. Al ser de baja prioridad, **no bloquea el renderizado** de la página.
3. Cuando la descarga finaliza, el atributo `onload` cambia `media` a `'all'`, aplicando los estilos a todos los medios.
4. El bloque `<noscript>` sirve como fallback para los contados usuarios con JavaScript desactivado.

---

## 6. Seguridad y Accesibilidad

### 6.1 Cabeceras HTTP de Seguridad

Las cabeceras de seguridad son instrucciones que el servidor envía al navegador del usuario para indicarle cómo debe comportarse ante ciertos escenarios de riesgo. Están implementadas **directamente en `functions.php`** mediante un hook de WordPress.

**Implementación en `functions.php`:**

```php
/**
 * Inyecta cabeceras de seguridad HTTP en todas las respuestas del servidor.
 * Se usa send_headers action para asegurar que se envían antes que cualquier output.
 */
function zalbi_security_headers() {
    // Fuerza conexiones HTTPS durante 1 año. includeSubDomains aplica a subdominios.
    // preload permite incluir el dominio en la lista precargada de HSTS de navegadores.
    header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload' );

    // Impide que la web sea cargada dentro de un <iframe> de otro dominio.
    // Protege contra ataques de clickjacking.
    header( 'X-Frame-Options: SAMEORIGIN' );

    // Política moderna de aislamiento entre ventanas. Reemplaza en parte a X-Frame-Options
    // en navegadores modernos.
    header( 'Cross-Origin-Opener-Policy: same-origin' );

    // Impide que el navegador "adivine" el tipo MIME de un recurso.
    // Protege contra ataques de MIME-sniffing que podrían ejecutar scripts disfrazados.
    header( 'X-Content-Type-Options: nosniff' );
}
add_action( 'send_headers', 'zalbi_security_headers' );
```

### 6.2 Análisis de Cada Cabecera

| Cabecera | Valor | Protección |
|----------|-------|-----------|
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains; preload` | Fuerza HTTPS durante 1 año. Impide ataques de downgrade a HTTP. |
| `X-Frame-Options` | `SAMEORIGIN` | Bloquea que la web sea embebida en iframes de terceros (anti-clickjacking). |
| `Cross-Origin-Opener-Policy` | `same-origin` | Aísla el contexto de navegación. Mitiga ataques Spectre/Meltdown via JS. |
| `X-Content-Type-Options` | `nosniff` | Evita que el navegador ejecute archivos con MIME type incorrecto. |

### 6.3 ¿Por Qué en Código y No en un Plugin?

La decisión de implementar las cabeceras directamente en `functions.php` en lugar de usar un plugin de seguridad (como Wordfence o Headers and Footers Scripts) es una decisión de arquitectura deliberada con múltiples ventajas:

**Ventajas del enfoque en código:**

1. **Sin dependencias externas:** No hay un plugin adicional que pueda quedar desactualizado, entrar en conflicto con otros plugins o ser abandonado por su desarrollador.
2. **Rendimiento:** Los plugins de seguridad típicamente cargan clases, opciones de la base de datos y lógica adicional. Una función PHP simple es muchos órdenes de magnitud más ligera.
3. **Control total:** Las cabeceras están documentadas y versionadas junto al resto del código del tema. Cualquier cambio queda registrado en el historial de Git.
4. **Portabilidad:** Si se migra el sitio a otro servidor, las cabeceras viajan con el tema. No hay configuración extra que recordar.
5. **Seguridad per se:** Tener menos plugins activos reduce la superficie de ataque del sitio.

### 6.4 Accesibilidad (WCAG)

El tema incorpora las siguientes medidas de accesibilidad:

| Elemento | Implementación | Estándar |
|----------|---------------|----------|
| Estructura semántica | Uso de `<main>`, `<header>`, `<footer>`, `<nav>`, `<article>` | WCAG 1.3.1 |
| Menú móvil | Botón hamburguesa con `aria-label="Menú"` y `aria-expanded` | WCAG 4.1.2 |
| Contraste de colores | Esquema de colores validado con ratio mínimo 4.5:1 (AA) | WCAG 1.4.3 |
| Imágenes | Atributos `alt` descriptivos en todas las imágenes de contenido | WCAG 1.1.1 |
| Formularios | Labels correctamente asociados a inputs mediante `for`/`id` | WCAG 1.3.1 |

---

## 7. Manual de Usuario (Administrador)

> Este apartado está dirigido al **administrador o cliente** que gestiona el contenido de la web. No se requieren conocimientos técnicos de programación.

### 7.1 Cómo Añadir un Nuevo Hinchable

Los hinchables son los productos que aparecen en la sección **Catálogo** de la web (`zalbi.eu/catalogo/`).

**Paso 1: Acceder al panel de administración**
- Ve a `zalbi.eu/wp-admin` e introduce tus credenciales.

**Paso 2: Crear el nuevo hinchable**
- En el menú lateral izquierdo, busca la opción **"Hinchables"**.
- Haz clic en **"Añadir nuevo"**.

**Paso 3: Rellenar la información**

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| **Título** | Nombre del hinchable (ej: "Tobogán Gigante Doble") | ✅ Sí |
| **Descripción** (editor) | Texto detallado: medidas, edad recomendada, características | ✅ Sí |
| **Extracto** | Resumen breve que aparece en el listado del catálogo (2-3 líneas) | ✅ Sí |
| **Imagen destacada** | Foto principal del hinchable. Formato recomendado: JPG/PNG, mínimo 800×600px | ✅ Sí |

> **Sobre el formato de imagen:** LiteSpeed Cache convertirá automáticamente la imagen a WebP. No hace falta subir imágenes en WebP manualmente.

**Paso 4: Asignar la categoría (Tipo de Hinchable)**
- En el panel lateral derecho, busca el apartado **"Tipos de Hinchable"**.
- Marca la categoría que corresponda:
  - ☑ **Hinchables** — Castillos y toboganes tradicionales.
  - ☑ **Acuáticos** — Para uso con agua o espuma.
  - ☑ **Deportivos** — Orientados a la actividad física.
  - ☑ **Juegos** — Juegos y actividades lúdicas.
- Un hinchable puede pertenecer a **más de una categoría** si es necesario.

**Paso 5: Publicar**
- Haz clic en el botón azul **"Publicar"**.
- El hinchable aparecerá automáticamente en el catálogo.

**Paso 6: Purgar la caché**
- Sigue el proceso descrito en la sección [7.3](#73-cómo-purgar-la-caché-de-litespeed) para que el nuevo hinchable sea visible para todos los usuarios.

---

### 7.2 Cómo Crear un Nuevo Evento

Los eventos son los servicios de animación y organización que aparecen en `zalbi.eu/eventos/`.

**Paso 1: Acceder al panel de administración**
- Ve a `zalbi.eu/wp-admin`.

**Paso 2: Crear el nuevo evento**
- En el menú lateral, busca **"Eventos"**.
- Haz clic en **"Añadir nuevo"**.

**Paso 3: Rellenar la información**

| Campo | Descripción | Obligatorio |
|-------|-------------|-------------|
| **Título** | Nombre del servicio (ej: "Animación Musical para Fiestas") | ✅ Sí |
| **Descripción** (editor) | Descripción completa del servicio: qué incluye, para quién es, etc. | ✅ Sí |
| **Extracto** | Resumen que aparece en el listado de eventos | ✅ Sí |
| **Imagen destacada** | Foto representativa del evento o servicio | Recomendado |

**Paso 4: Publicar**
- Haz clic en **"Publicar"**.

**Paso 5: Purgar la caché**
- Obligatorio tras publicar. Ver [sección 7.3](#73-cómo-purgar-la-caché-de-litespeed).

---

### 7.3 Cómo Purgar la Caché de LiteSpeed

> **¿Por qué es necesario purgar la caché?**  
> LiteSpeed Cache guarda copias estáticas de las páginas para servirlas ultra-rápido. Cuando se añade contenido nuevo o se actualiza el código, esas copias guardadas están desactualizadas. Purgar la caché fuerza a LiteSpeed a generar copias nuevas con el contenido actualizado.

#### Cuándo purgar la caché

Debes purgar la caché en los siguientes casos:

- ✅ Después de añadir o editar un hinchable o evento.
- ✅ Después de que el desarrollador haya subido un commit con cambios al tema.
- ✅ Después de actualizar textos o imágenes de cualquier página.
- ✅ Cuando un visitante te reporte que "la web se ve diferente a lo que cambié".

#### Método 1: Desde la barra de administración (recomendado)

1. Asegúrate de estar **logueado en WordPress**.
2. En la **barra superior negra** (que solo tú ves), busca el menú **"LiteSpeed Cache"** (icono de rayo ⚡ verde).
3. Haz clic en él para desplegar el menú.
4. Selecciona **"Purgar Todo"**.
5. Aparecerá un mensaje de confirmación verde: *"Cache Purged Successfully"*.

#### Método 2: Desde el panel de administración

1. Ve a `zalbi.eu/wp-admin`.
2. En el menú lateral, ve a **LiteSpeed Cache → Gestionar**.
3. Haz clic en el botón **"Purgar Todo"** (botón naranja/rojo).

#### Método 3: Purga inteligente (solo la página modificada)

Para no invalidar toda la caché cuando solo se modifica una página concreta:

1. Ve a la página modificada en el frontend (la web visible).
2. En la barra superior, haz clic en **LiteSpeed Cache → Purgar esta URL**.
3. Solo se invalida la caché de esa página específica, el resto sigue servido rápidamente.

> **Consejo:** Después de un deploy desde GitHub, usa siempre **"Purgar Todo"** para asegurarte de que ningún archivo CSS o JS antiguo queda en caché.

---

## 8. Solución de Problemas Frecuentes

### Problema: "He subido cambios pero la web sigue igual"

**Causa:** La caché de LiteSpeed está sirviendo la versión antigua.  
**Solución:** Purgar toda la caché siguiendo el [paso 7.3](#73-cómo-purgar-la-caché-de-litespeed).

---

### Problema: "El nuevo hinchable no aparece en el catálogo"

**Causa posible 1:** El hinchable no está publicado (está en borrador).  
**Solución:** Ve a `Hinchables → Todos los hinchables`, verifica que el estado sea **"Publicado"** y no **"Borrador"**.

**Causa posible 2:** La caché no se ha purgado.  
**Solución:** Purgar toda la caché.

**Causa posible 3:** El hinchable no tiene imagen destacada.  
**Solución:** La plantilla `page-catalogo.php` puede requerir imagen destacada para mostrar la tarjeta del hinchable. Añade una imagen destacada y vuelve a publicar.

---

### Problema: "El formulario de contacto no envía"

**Causa posible 1:** El JS de Contact Form 7 fue incluido en el proceso de diferido de LiteSpeed.  
**Solución (técnica):** Verificar en LiteSpeed Cache → Gestión de JS que `contact-form-7` está en la lista de exclusiones del diferido.

**Causa posible 2:** Configuración SMTP del servidor de correo.  
**Solución:** Verificar en `Contact Form 7 → Formularios` que el email de destino es correcto. Considerar instalar un plugin SMTP (como WP Mail SMTP) si los correos no llegan.

---

### Problema: "La web se ve rota en móvil después de un deploy"

**Causa:** LiteSpeed puede estar sirviendo el CSS crítico antiguo (UCSS) que no incluye los nuevos estilos.  
**Solución:**
1. Purgar toda la caché.
2. Si persiste, ir a **LiteSpeed Cache → Optimización de página → CSS** y forzar la regeneración del CSS crítico (UCSS) mediante QUIC.cloud.

---

### Problema: "El CDN de Hostinger está activo accidentalmente"

**Síntoma:** CSS roto, estilos inconsistentes, puntuación de PageSpeed que cae drásticamente.  
**Solución:**
1. Accede al panel de Hostinger (hPanel).
2. Ve a **"Acelerar el sitio web"** o **"CDN"**.
3. Asegúrate de que el CDN de Hostinger está **desactivado**.
4. Purga toda la caché de LiteSpeed.
5. Espera 5-10 minutos y vuelve a medir en PageSpeed Insights.

---

### Problema: "La puntuación de PageSpeed ha bajado tras una actualización de WordPress"

**Causa posible:** WordPress o un plugin actualizó sus scripts y los nuevos no están correctamente diferidos o minificados.  
**Proceso de diagnóstico:**
1. Abre las DevTools del navegador (F12) → Consola. ¿Hay errores JavaScript?
2. Si hay errores de tipo `ReferenceError` o `$ is not defined`, revisa las exclusiones del diferido de JS en LiteSpeed Cache.
3. Purga toda la caché y regenera el UCSS.
4. Si el problema persiste, desactiva temporalmente la combinación de JS para aislar qué script está causando el conflicto.

---

## Apéndice: Recursos y Referencias

| Recurso | URL |
|---------|-----|
| Repositorio del tema | [github.com/Garridoparrayeray/zalbi-theme](https://github.com/Garridoparrayeray/zalbi-theme) |
| Web en producción | [zalbi.eu](https://zalbi.eu) |
| Panel WordPress | [zalbi.eu/wp-admin](https://zalbi.eu/wp-admin) |
| Panel Hostinger (hPanel) | [hpanel.hostinger.com](https://hpanel.hostinger.com) |
| QUIC.cloud | [quic.cloud](https://quic.cloud) |
| Google PageSpeed Insights | [pagespeed.web.dev](https://pagespeed.web.dev) |
| Documentación LiteSpeed Cache | [docs.litespeedtech.com](https://docs.litespeedtech.com/lscache/lscwp/) |
| Documentación Polylang | [polylang.pro/doc](https://polylang.pro/doc/) |
| Documentación Contact Form 7 | [contactform7.com/docs](https://contactform7.com/docs/) |

---

*Documentación generada para uso interno del proyecto Zalbi Aisia eta Abentura. Última actualización: Marzo 2026.*
