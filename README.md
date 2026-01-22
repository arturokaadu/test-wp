# Biblioteca WordPress

Challenge técnico de WordPress para demostrar conocimientos de desarrollo con PHP, Custom Post Types, REST API.

## Datos del Candidato

- **Nombre:** Arturo Kaadú
- **Fecha:** Enero 2026
- **Tiempo empleado:** ~16 horas

---

## Cómo Ejecutar

### 1. Levantar Docker

```bash
docker-compose up -d
```

### 2. Acceder al sitio

- **Frontend:** http://localhost:8080
- **Admin:** http://localhost:8080/wp-admin
- **Usuario/Contraseña:** Los que configure WordPress en la instalación inicial

### 3. Importar Campos ACF

1. En el admin, ir a **ACF > Herramientas**
2. Importar el archivo `acf-export/libro-fields.json`
3. Esto creará todos los campos personalizados del CPT Libro

### 4. Compilar SCSS (opcional, para desarrollo)

```bash
npm install
npm run watch:css  # Compila SCSS a CSS en tiempo real
```

> **Nota:** El archivo `assets/css/libros.css` ya está compilado y listo para usar. Solo es necesario ejecutar este paso si se realizan cambios en los archivos SCSS.

---

## Estructura del Proyecto

```
biblioteca-wordpress/
├── functions.php           # Configuración principal del tema
├── header.php              # Cabecera HTML con wp_head()
├── footer.php              # Pie de página con wp_footer()
├── single-libro.php        # Detalle de un libro individual
├── archive-libro.php       # Listado de libros con filtros
├── taxonomy.php            # Listado por género o autor
├── inc/
│   ├── post-types.php      # CPT Libro + Taxonomías
│   └── rest-api.php        # API REST custom
├── assets/
│   ├── scss/               # Archivos fuente SCSS (BEM)
│   │   ├── main.scss
│   │   ├── abstracts/
│   │   ├── base/
│   │   ├── components/
│   │   ├── layout/
│   │   └── pages/
│   └── css/
│       └── libros.css      # CSS compilado desde SCSS
└── acf-export/             # Campos personalizados ACF
```

---

## Archivos Principales y Por Qué

### `single-libro.php`

Template para la vista de detalle de un libro. Incluye:

- Imagen de portada, título, autor, precio
- Badge de oferta si aplica
- Badge "¡Último en stock!" si `stock_disponible == 1`
- Descripción (usando excerpt)
- Sección de reseñas con comentarios
- **"También te podría gustar"**: Custom query que busca libros del mismo género. Si no encuentra, hace fallback a libros aleatorios.

### `archive-libro.php`

Listado de libros con:

- Filtros por género, precio máximo y búsqueda
- WP_Query personalizada con sanitización
- Cards responsive con toda la información requerida
- Paginación

### `taxonomy.php`

Template genérico para taxonomías. Muestra libros filtrados por género o autor cuando el usuario hace click en un badge.

### `inc/rest-api.php`

API REST custom con 3 endpoints:

- `GET /biblioteca/v1/libros` - Lista con filtros
- `GET /biblioteca/v1/libros/{id}` - Detalle con reseñas
- `GET /biblioteca/v1/generos` - Lista de géneros

---

## Capturas

### Archivo de Libros (Desktop)

![Archivo Desktop](./assets/img/Full%20web.png)

### Detalle de Libro

![Detalle Libro](./assets/img/Detalle%20libro.png)

### Vista Mobile

![Mobile](./assets/img/Mobile.png)

### API en Postman

![API Libros](./assets/img/api-libros.gif)

---

## 🛠 Ejemplos de Respuesta JSON

### 1. Generos (`GET /generos`)

```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "nombre": "Biografía",
      "slug": "biografia",
      "descripcion": "Historias de vida de personas reales",
      "count": 1
    },
    {
      "id": 2,
      "nombre": "Ficción",
      "slug": "ficcion",
      "descripcion": "Obras de imaginación narrativa",
      "count": 3
    },
    {
      "id": 3,
      "nombre": "No Ficción",
      "slug": "no-ficcion",
      "descripcion": "Obras basadas en hechos reales",
      "count": 1
    },
    {
      "id": 4,
      "nombre": "Técnico",
      "slug": "tecnico",
      "descripcion": "Libros técnicos y manuales",
      "count": 1
    }
  ]
}
```

### 2. Detalle de Libro (`GET /libros/35`)

```json
{
  "success": true,
  "data": {
    "id": 35,
    "titulo": "Attack on Titan: Lost Girls (Novela)",
    "slug": "attack-on-titan-lost-girls-novela",
    "contenido": "",
    "excerpt": "Novela ligera spin-off de la aclamada serie Attack on Titan. Esta obra profundiza en las historias no contadas de Annie Leonhart y Mikasa Ackerman, explorando sus motivaciones y el mundo cruel en el que viven más allá de la trama principal.",
    "fecha_publicacion": "2026-01-20",
    "link": "http://localhost:8080/libros/attack-on-titan-lost-girls-novela/",
    "imagen_url": "http://localhost:8080/wp-content/uploads/2026/01/attack-on-titan-lost-girls.jpg",
    "generos": ["ficcion"],
    "autores": ["Hiroshi Seko / Hajime Isayama"],
    "precio": 25000,
    "en_oferta": false,
    "precio_oferta": null,
    "stock": 3,
    "paginas": 224,
    "isbn": "978-84-679-3142-6",
    "editorial": "Penguin random house",
    "ano_publicacion": 2018,
    "resenas": [],
    "rating_promedio": 0,
    "total_resenas": 0
  }
}
```

### 3. Búsqueda (`GET /libros?search=confesion`)

```json
{
  "success": true,
  "data": [
    {
      "id": 30,
      "titulo": "Confesión",
      "slug": "confesion",
      "excerpt": "La autobiografía definitiva del legendario vocalista de Judas Priest. Un relato crudo, honesto y conmovedor sobre la vida en la carretera, la historia del heavy&hellip;",
      "link": "http://localhost:8080/libros/confesion/",
      "imagen_url": "http://localhost:8080/wp-content/uploads/2026/01/RHalfordConfessBook400-196x300.jpg",
      "generos": ["biografia"],
      "autores": ["Rob Halford"],
      "precio": 72000,
      "en_oferta": false,
      "precio_oferta": null,
      "paginas": 380,
      "isbn": "978-84-17645-15-1"
    }
  ],
  "pagination": {
    "total": 1,
    "pages": 1,
    "current_page": 1,
    "per_page": 10
  }
}
```

---

## API REST - Endpoints

### Listar libros

```
GET /wp-json/biblioteca/v1/libros
```

Parámetros opcionales:

- `genero` - Filtrar por slug de género (ej: `?genero=ficcion`)
- `precio_max` - Precio máximo (ej: `?precio_max=30000`)
- `search` - Buscar por título (ej: `?search=confesion`)
- `per_page` - Libros por página (default: 10)
- `page` - Número de página

**Respuesta ejemplo:**

```json
{
  "success": true,
  "data": [
    {
      "id": 35,
      "titulo": "Attack on Titan: Lost Girls",
      "slug": "attack-on-titan-lost-girls-novela",
      "excerpt": "Novela ligera spin-off...",
      "generos": ["ficcion"],
      "autores": ["Hiroshi Seko / Hajime Isayama"],
      "precio": 25000,
      "en_oferta": false
    }
  ],
  "pagination": { "total": 5, "pages": 1, "current_page": 1 }
}
```

### Detalle de libro

```
GET /wp-json/biblioteca/v1/libros/{id}
```

Incluye todos los campos ACF, reseñas y rating promedio.

### Listar géneros

```
GET /wp-json/biblioteca/v1/generos
```

**Respuesta ejemplo:**

```json
{
  "success": true,
  "data": [
    { "id": 5, "nombre": "Biografía", "slug": "biografia", "count": 1 },
    { "id": 2, "nombre": "Ficción", "slug": "ficcion", "count": 3 }
  ]
}
```

---

## Decisiones Técnicas

### SCSS + BEM

Se usó SCSS en lugar de CSS plano por:

- **Modularidad:** Archivos separados por componente (`_cards.scss`, `_badges.scss`, etc.)
- **Mantenibilidad:** Variables para colores, tipografía, espaciados
- **BEM:** Naming convention que evita conflictos de especificidad

El SCSS compila a `assets/css/libros.css` que es lo que WordPress carga.

### Sintaxis `endwhile;` / `endif;`

Se usa la sintaxis alternativa de PHP en templates porque mejora la legibilidad cuando se mezcla PHP con HTML. Es la convención recomendada en WordPress Core.

### Libros Relacionados con Fallback

La query de "También te podría gustar" primero busca libros del mismo género. Si no encuentra resultados (ej: el libro es el único de su género), hace una segunda query sin filtro de género para mostrar libros aleatorios. Evita mostrar la sección vacía.

### Carga Condicional de CSS

En `functions.php`, el CSS solo se carga en páginas de libros (`is_singular('libro')`, `is_post_type_archive('libro')`, `is_tax()`). Mejora performance al no cargar estilos innecesarios en otras páginas.

### Fondo Animado (Mesh Gradient)

Se agregó un gradient animado sutil en el fondo para dar un toque visual moderno sin distraer del contenido.

```scss
background: linear-gradient(-45deg, #f8f9fa, #e8eef3, #dfe6ed, #f0f4f8);
animation: meshGradient 15s ease infinite;
```

---

## Problemas Encontrados

### 1. Theme sin `header.php` es deprecated

**Problema:** WordPress 6.x muestra warning si el tema no tiene `header.php` y `footer.php`.

**Solución:** Se crearon ambos archivos con `wp_head()` y `wp_footer()` respectivamente. Sin estos hooks, los estilos y scripts no se cargan.

### 2. Clases CSS no aplicaban a las cards

**Problema:** El SCSS usaba naming BEM (`libro-card__titulo`) pero el HTML usaba clases diferentes (`libro-titulo`).

**Solución:** Se alinearon todas las clases del HTML con el naming BEM del SCSS.

### 3. "También te podría gustar" no mostraba resultados

**Problema:** La query filtraba por género (`tax_query`), pero si el libro era el único de su género, no encontraba otros libros.

**Solución:** Se agregó fallback. Si la primera query no encuentra resultados, se ejecuta una segunda sin filtro de género.

### 4. Variable `$current_id` fuera de scope

**Problema:** Las secciones de reseñas y libros relacionados estaban fuera del `while` loop principal, perdiendo acceso a `$current_id`.

**Solución:** Se movió el `endwhile` al final del template, después de todas las secciones que necesitan datos del post actual.

### 5. `sanitize_callback` con `floatval` en REST API

**Problema:** Al usar `'sanitize_callback' => 'floatval'` en los argumentos de la REST API, WordPress lanzaba error porque pasa 3 argumentos al callback pero `floatval()` solo acepta 1.

**Solución:** Envolver en closure: `'sanitize_callback' => function($value) { return floatval($value); }`

---

## Respuestas Teóricas

Ver archivo [RESPUESTAS.md](./RESPUESTAS.md) con las respuestas a las preguntas teóricas del challenge.

---

## Anexo: Uso de Herramientas de IA

Para este proyecto se utilizó asistencia de IA como herramienta de productividad en las siguientes áreas:

- **Organización de archivos SCSS/BEM:** Sugerencias para estructura de carpetas y modularización.
- **Estructura base de documentación:** Boilerplate inicial de README.md.
- **Debugging:** Análisis de scope de variables PHP en templates.
- **Generación de JSON para ACF:** Creación de la estructura de campos personalizados bajo supervisión.
- **Boilerplate de Código:** Scaffolding inicial para Custom Post Types y Taxonomías en `functions.php`/`inc`.
- **Datos de Prueba:** Generación de contenido "dummy" (títulos, sinopsis) para poblar la base de datos.
