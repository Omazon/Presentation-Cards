# Tarjetas

Plugin de WordPress para la creación, gestión y publicación de tarjetas de presentación digitales interactivas para colaboradores y equipos de trabajo.

---

## 📋 Descripción

**Tarjetas** es un plugin desarrollado para WordPress que añade un tipo de contenido personalizado (*Custom Post Type*) denominado `tarjeta`. Proporciona un entorno de edición a medida que sustituye el editor convencional de bloques (Gutenberg) por un formulario interactivo con previsualización en vivo en formato de teléfono móvil (*smartphone preview*), generación automática de códigos QR (tanto para la web como para importar la tarjeta de contacto directamente vía vCard) y exportación de archivos `.vcf`.

---

## ✨ Características Principales

- **Editor Personalizado con Vista Previa en Vivo:**
  - Formulario adaptado exclusivamente a datos de contacto profesionales.
  - Previsualización en tiempo real dentro de un mockup de dispositivo móvil.
  - Selector de color de marca personalizable por tarjeta (`wp-color-picker`).
- **Gestión Avanzada de Logos:**
  - Configuración de un logo corporativo predeterminado a nivel global desde la sección de ajustes.
  - Capacidad de sobrescribir el logo individualmente por tarjeta desde la biblioteca de medios.
  - Preservación histórica: las modificaciones al logo global no afectan retrospectivamente a tarjetas previamente emitidas a menos que se desee.
- **Códigos QR Integrados:**
  - **QR de la Tarjeta Web:** Enlace directo a la URL pública de la tarjeta en línea.
  - **QR de Contacto Directo (vCard 3.0):** Generado en formato vectorial (SVG) mediante JavaScript en el navegador, permitiendo al usuario escanear y agregar el contacto de inmediato a su agenda telefónica con un solo escaneo.
- **Descarga de Contacto vCard (.vcf):**
  - Botón público para descargar la tarjeta de contacto vCard compatible con iOS, Android, macOS y Windows.
  - Compatibilidad con fotografía de perfil embebida en base64 para máxima compatibilidad con clientes de correo y agendas.
- **Taxonomía de Divisiones y Departamentos:**
  - Clasificación jerárquica para organizar colaboradores según áreas de la empresa (`tarjeta_departamento`).
- **Validación Robusta:**
  - Validación frontend y backend obligatoria antes de permitir la publicación (evita tarjetas con información incompleta, números telefónicos inválidos o sin fotografía).
  - Opción de ocultar / pasar a borrador tarjetas previamente publicadas en un solo clic.
- **Plantilla Pública Aislada y Responsiva:**
  - Diseño limpio, profesional y optimizado para dispositivos móviles en `templates/single-tarjeta.php`.
  - Integración directa con enlaces telefónicos (`tel:`), mensajería (`WhatsApp`), correo electrónico (`mailto:`) y geolocalización en Google Maps.
  - Soporte de redes sociales fijas (LinkedIn, Instagram, Facebook, Sitio Web) y adición de enlaces sociales dinámicos ilimitados.

---

## 🛠️ Requisitos del Sistema

- **WordPress:** Versión 6.0 o superior.
- **PHP:** Versión 7.4 o superior (compatible con PHP 8.x).
- **Dependencias incluidas:**
  - Font Awesome 6.7.2 (iconografía cargada vía CDN para front y admin).
  - QRCode Generator JS (librería incluida localmente para generación de SVG).
  - WordPress Color Picker y Media Uploader nativos.

---

## 📁 Estructura del Proyecto

```text
tarjetas/
├── assets/
│   ├── css/
│   │   ├── admin-editor.css      # Estilos del panel de edición y mockup de smartphone
│   │   └── public-card.css       # Estilos de la tarjeta en la vista pública
│   ├── js/
│   │   ├── admin-editor.js       # Sincronización en vivo del editor y mockup
│   │   ├── contact-qr.js         # Lógica para generar y descargar el QR vCard
│   │   ├── logo-selector.js      # Controlador de medios para la gestión de logos
│   │   ├── qrcode-generator.js   # Generador de códigos QR en JavaScript (licencia MIT)
│   │   └── qrcode-generator.LICENSE
│   └── mc_gregor.png             # Logo predeterminado de respaldo
├── templates/
│   └── single-tarjeta.php        # Plantilla aislada para la visualización pública de cada tarjeta
├── tests/
│   └── logo-settings-test.php    # Pruebas unitarias/de integración ligeras ejecutables por CLI
├── logo-settings.php             # Lógica de ajustes globales de logo y persistencia por tarjeta
├── tarjetas.php                  # Archivo principal del plugin (CPT, metaboxes, vCard, QR)
└── README.md                     # Documentación del proyecto
```

---

## 🚀 Instalación y Activación

1. Copia o clona la carpeta `tarjetas` en el directorio de plugins de WordPress:
   ```bash
   wp-content/plugins/tarjetas
   ```
2. Accede al panel de administración de WordPress (`/wp-admin`).
3. Ve a **Plugins > Plugins instalados**.
4. Localiza **Tarjetas** y haz clic en **Activar**.
5. Al activarse, se registrarán automáticamente el tipo de contenido `tarjeta`, la taxonomía `tarjeta_departamento` y se actualizarán las reglas de reescritura de URLs (*rewrite rules*).

---

## 📖 Guía de Uso

### 1. Ajustes Generales del Logo
- Dirígete al menú **Tarjetas > Ajustes**.
- Selecciona el logo corporativo predeterminado desde la biblioteca de medios.
- Este logo se aplicará por defecto a todas las tarjetas nuevas creadas a partir de ese momento.

### 2. Gestión de Divisiones / Departamentos
- Accede a **Tarjetas > Divisiones / Departamentos**.
- Crea las diferentes divisiones (por ejemplo: *Ventas*, *Tecnología*, *Operaciones*, *Recursos Humanos*).

### 3. Crear una Nueva Tarjeta
- Ve a **Tarjetas > Añadir nueva**.
- Completa las secciones del formulario:
  - **Apariencia:** Selecciona el color de acento principal para la tarjeta.
  - **Información del Colaborador:**
    - Fotografía del colaborador *(Obligatorio)*.
    - Logo de la empresa (puedes mantener el predeterminado o cambiarlo específicamente para este colaborador).
    - Nombre completo *(Obligatorio)*.
    - Cargo *(Obligatorio)*.
    - Empresa.
    - División / Departamento *(Obligatorio)*.
  - **Información de Contacto:**
    - Teléfono y WhatsApp con formato internacional (ej. `+505 1234 5678`) *(Obligatorios)*.
    - Correo electrónico válido *(Obligatorio)*.
    - Ubicación geográfica / dirección *(Obligatoria)*.
  - **Redes Sociales:**
    - Perfiles fijados (LinkedIn, Instagram, Facebook, Sitio Web).
    - Botón para agregar redes o enlaces adicionales personalizados.
- Observa cómo la maqueta del teléfono a la derecha se actualiza automáticamente con tus cambios.
- Haz clic en **Publicar tarjeta**.

### 4. Códigos QR y Descargas
- **QR Web:** Permite escanear la tarjeta para abrir su página web pública responsive.
- **QR vCard de Contacto:** Al guardar la tarjeta, se genera un código QR que incluye la información de contacto vCard para que cualquier persona pueda escanearlo y guardarlo directamente en su agenda sin necesidad de conexión o descargas adicionales.
- **Descargar SVG:** En la administración puedes descargar el código QR en formato vectorial SVG para su impresión en tarjetas de presentación físicas o material POP.
- **Descargar vCard (.vcf):** En la vista pública de la tarjeta, los visitantes cuentan con el botón interactivo **"Guardar contacto"**, que descarga el archivo `.vcf` con la información y foto de perfil.

---

## 🧪 Pruebas

El proyecto cuenta con un conjunto de pruebas ligeras y aisladas que no requieren conexión a base de datos ni inicializar todo el entorno de WordPress:

```bash
php wp-content/plugins/tarjetas/tests/logo-settings-test.php
```

Estas pruebas verifican:
- Reglas de negocio para la asignación y herencia de logos iniciales.
- Sanitización y validación de identificadores de adjuntos multimedia.
- Formateo y estandarización internacional E.164 de números telefónicos.
- Generación y escape correcto de campos vCard 3.0.

---

## 🔒 Seguridad y Buenas Prácticas

- **Verificación de Nonces:** Todas las peticiones de guardado y cambio de estado están protegidas con nonces criptográficos de WordPress (`tarjetas_datos_nonce`).
- **Control de Capacidades:** Únicamente usuarios con permisos de administrador o la capacidad explícita `create_tarjetas` y `edit_post` pueden crear y modificar tarjetas.
- **Sanitización y Escape:** Uso sistemático de funciones del estándar de WordPress como `sanitize_text_field`, `sanitize_email`, `sanitize_hex_color`, `esc_url_raw`, `esc_html`, `esc_attr` y `wp_strip_all_tags`.

---

## 📄 Metadatos y Licencia

- **Nombre:** Tarjetas
- **Versión:** 1.3.2
- **Autor:** Guegue Tarjeta
- **Text Domain:** `tarjetas`
