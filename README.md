# PRUEBA TÉCNICA WORDPRESS CORE - SEMI SENIOR

---

## ⚠️ IMPORTANTE: ENTREGA VÍA GIT

1. Hacer **PULL del master**
2. **Clonar** el repositorio
3. Crear una **nueva branch** con tu nombre: `test/tu-nombre-apellido`
4. Completar los ejercicios
5. **Commit y PUSH** de tu branch
6. Crear **Pull Request** hacia master

---

## CONTEXTO DEL PROYECTO

Crear un sistema de "Biblioteca de Libros" con:
- Custom Post Type "Libro"
- Campos ACF para información del libro
- REST API custom para consumir externamente
- Frontend con The Loop, queries personalizadas y **UI responsive**

---

## PARTE 1: PREGUNTAS TEÓRICAS

Responder en archivo `RESPUESTAS.md`

### 1. The Loop

**1.1** ¿Cuál es la diferencia entre estos dos códigos? (2 pts)

```php
// Código A
while (have_posts()) {
    the_post();
    the_title();
}

// Código B
$query = new WP_Query($args);
while ($query->have_posts()) {
    $query->the_post();
    the_title();
}
wp_reset_postdata();
```

**1.2** ¿Por qué es importante usar `wp_reset_postdata()` y cuándo debes usarlo?

**1.3** ¿Qué hace `setup_postdata($post)` y cuándo lo usarías?

---

### 2. Hooks

**2.1** Explica la diferencia entre `add_action` y `add_filter` con un ejemplo de cada uno.

**2.2** ¿Qué problema tiene este código y cómo lo corregirías?
```php
add_action('init', 'registrar_cpt');
function registrar_cpt() {
    register_post_type('libro', ['public' => true]);
    flush_rewrite_rules(); // ← Problema aquí
}
```

---

### 3. Template Hierarchy

**3.1** Si tienes un Custom Post Type "libro" con slug "el-quijote", ¿en qué orden WordPress buscará los templates para mostrar la vista individual de este libro? Lista los 5 primeros templates en orden de prioridad.

**3.2** ¿Cuál es la diferencia entre `get_template_part()` y `include`? ¿Cuándo usarías uno sobre el otro?

---

### 4. Seguridad

**4.1** Completa el código con las funciones de seguridad correctas:
```php
<?php
// Input (sanitización)
$titulo = ________($_POST['titulo']);
$email = ________($_POST['email']);
$precio = ________($_POST['precio']);

// Output (escape)
echo '<h1>' . ______($titulo) . '</h1>';
echo '<a href="' . ______($url) . '">Link</a>';
echo '<div class="' . ______($clase) . '">Contenido</div>';
?>
```

---

## PARTE 2: EJERCICIO PRÁCTICO

### PROYECTO: Sistema de Biblioteca de Libros

---

### EJERCICIO 1: Custom Post Type + Taxonomías

**Archivo a crear:** `inc/post-types.php`

Crear un Custom Post Type "Libro" con dos taxonomías.

**Requerimientos del CPT "Libro":**
- Slug: `libro`
- Labels apropiados (plural y singular)
- Soporte para: título, editor, excerpt, thumbnail, author
- Público y con archivo
- Habilitado para REST API
- Posición del menú: 5
- Ícono del menú: `dashicons-book`
- Rewrite slug: `libros`

**Taxonomía 1 - "Género" (jerárquica):**
- Slug: `genero`
- Jerárquica (como categorías)
- Labels apropiados
- REST API habilitado
- Mostrar en columna del admin
- Crear términos iniciales: Ficción, No Ficción, Técnico, Biografía

**Taxonomía 2 - "Autor" (no jerárquica):**
- Slug: `autor_libro`
- No jerárquica (como tags)
- Labels apropiados
- REST API habilitado
- Mostrar en columna del admin

**Criterios de evaluación:**
- ✅ CPT registrado correctamente y funcional
- ✅ Ambas taxonomías funcionan
- ✅ Términos iniciales creados
- ✅ Labels correctos en español
- ✅ REST API habilitado

---

### EJERCICIO 2: ACF Fields

Crear campos ACF para el CPT "Libro".

**Puedes elegir:**
- **Opción A:** Crear por UI y exportar JSON a `acf-export/libro-fields.json`
- **Opción B:** Crear por código en `inc/acf-fields.php`

**Campos requeridos:**

**Grupo 1: "Información Básica"**
- ISBN (text, required)
- Año de publicación (number, min: 1900, max: 2025)
- Editorial (text)
- Páginas (number, min: 1)

**Grupo 2: "Comercial"**
- Precio (number, required, min: 0)
- Stock disponible (number, default: 0)
- En oferta (true/false)
- Precio oferta (number, **solo visible si "En oferta" es true**)

**Grupo 3: "Multimedia"**
- Portada alternativa (image)
- PDF preview (file, solo .pdf, max: 5MB)
- Galería de imágenes (gallery, min: 0, max: 10)

**Grupo 4: "Reseñas" (Repeater)**
- Nombre del reviewer (text, required)
- Rating (range, min: 1, max: 5, step: 1)
- Comentario (textarea, 4 rows)

**Criterios de evaluación:**
- ✅ Todos los campos creados y funcionales
- ✅ Conditional logic del "Precio oferta" funciona
- ✅ Campos solo aparecen en CPT "libro"
- ✅ Validaciones correctas (min/max, required)

---

### EJERCICIO 3: Template Archive + UI Responsive

**Archivo a crear:** `archive-libro.php`

Crear template de archivo para libros con filtros, búsqueda y diseño responsive.

**Requerimientos funcionales:**

**A) Formulario de Filtros**
- Select para filtrar por género (cargar géneros dinámicamente)
- Input numérico para precio máximo
- Input de texto para búsqueda por título
- Botón "Filtrar"
- Botón/Link "Limpiar filtros"

**B) Query Personalizada con WP_Query**
- **Sanitizar TODOS los parámetros GET**
- Filtro por taxonomía género (tax_query)
- Filtro por precio máximo (meta_query)
- Búsqueda por título (parámetro 's')
- Paginación de 12 libros por página
- Ordenar por título A-Z
- Manejar caso sin resultados

**C) Loop mostrando cada libro**

Cada card de libro debe mostrar:
- Thumbnail (o placeholder si no hay imagen)
- Título con link al single
- Excerpt (máximo 150 caracteres)
- Precio formateado (ej: $45.000)
- Si está en oferta: mostrar precio tachado y precio de oferta
- Badge "EN OFERTA" visible si corresponde
- Géneros como tags/badges
- Número de páginas con ícono
- **Escapar TODAS las salidas correctamente**

**D) CSS Responsive**

Crear archivo `assets/css/libros.css` con:

**Desktop (>1024px):**
- Grid de 3 columnas
- Cards con imagen arriba, contenido abajo
- Hover effect sutil en cards
- Espaciado generoso

**Tablet (768px - 1024px):**
- Grid de 2 columnas
- Ajustar espaciados

**Mobile (<768px):**
- 1 columna
- Filtros en stack vertical
- Botones full-width
- Imagen de altura reducida

**Elementos de diseño requeridos:**
- Cards con borde redondeado y sombra
- Badge de oferta en esquina de imagen
- Precio tachado con color gris
- Precio de oferta en color destacado (rojo/verde)
- Géneros como pills/badges con fondo de color
- Botones con estados hover
- Paginación centrada y estilizada

**Criterios de evaluación:**
- ✅ Filtros funcionan correctamente
- ✅ Query sanitizada y optimizada
- ✅ Todos los datos mostrados correctamente
- ✅ Escape de salidas correcto
- ✅ Responsive funciona en 3 breakpoints
- ✅ Diseño visual profesional

---

### EJERCICIO 4: REST API Custom

**Archivo a crear:** `inc/rest-api.php`

Crear 3 endpoints REST API.

#### 4.1 GET `/wp-json/biblioteca/v1/libros`

**Parámetros soportados:**
- `per_page` (default: 10, max: 50)
- `page` (default: 1)
- `genero` (slug del género)
- `precio_max` (número)
- `en_oferta` (boolean)
- `search` (búsqueda por título)

**Estructura de respuesta esperada:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "titulo": "El Quijote",
      "slug": "el-quijote",
      "excerpt": "Las aventuras de...",
      "link": "https://sitio.com/libros/el-quijote",
      "imagen_url": "https://sitio.com/imagen.jpg",
      "generos": ["ficcion", "clasico"],
      "autores": ["Miguel de Cervantes"],
      "precio": 25000,
      "en_oferta": true,
      "precio_oferta": 18000,
      "paginas": 863,
      "isbn": "978-84-376-0494-7"
    }
  ],
  "pagination": {
    "total": 45,
    "pages": 5,
    "current_page": 1,
    "per_page": 10
  }
}
```

**Requerimientos:**
- Sanitizar y validar TODOS los parámetros
- Aplicar filtros solicitados
- Incluir todos los campos ACF necesarios
- Manejar errores apropiadamente

#### 4.2 GET `/wp-json/biblioteca/v1/libros/<id>`

**Respuesta esperada:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "titulo": "El Quijote",
    "slug": "el-quijote",
    "contenido": "<p>Contenido completo...</p>",
    "excerpt": "Las aventuras de...",
    "fecha_publicacion": "2024-01-15",
    "link": "https://sitio.com/libros/el-quijote",
    "imagen_url": "https://sitio.com/imagen.jpg",
    "generos": ["ficcion"],
    "autores": ["Miguel de Cervantes"],
    "precio": 25000,
    "en_oferta": true,
    "precio_oferta": 18000,
    "stock": 15,
    "paginas": 863,
    "isbn": "978-84-376-0494-7",
    "editorial": "Penguin",
    "ano_publicacion": 1605,
    "resenas": [
      {
        "nombre": "Juan Pérez",
        "rating": 5,
        "comentario": "Obra maestra!"
      }
    ],
    "rating_promedio": 4.8,
    "total_resenas": 5
  }
}
```

**Requerimientos:**
- Validar que el ID existe y es un libro
- Retornar 404 si no existe
- Incluir contenido completo
- Calcular rating promedio de reseñas
- Incluir todas las reseñas

#### 4.3 GET `/wp-json/biblioteca/v1/generos`

**Respuesta esperada:**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "nombre": "Ficción",
      "slug": "ficcion",
      "descripcion": "Libros de ficción",
      "count": 23
    }
  ]
}
```

**Requerimientos:**
- Listar todos los géneros
- Incluir contador de libros por género
- Solo géneros que tienen libros (hide_empty)

**Criterios generales de evaluación:**
- ✅ Los 3 endpoints funcionan correctamente
- ✅ Respuestas en formato JSON esperado
- ✅ Validación y sanitización de parámetros
- ✅ Manejo de errores apropiado
- ✅ Incluye todos los campos requeridos

---

## ESTRUCTURA DE ENTREGA

**Tu branch debe contener estos archivos:**

```
biblioteca-wordpress/
├── inc/
│   ├── post-types.php              # Ejercicio 1
│   ├── rest-api.php                # Ejercicio 4
│   └── acf-fields.php              # Ejercicio 2 (si usas código)
│
├── acf-export/
│   └── libro-fields.json           # Ejercicio 2 (si usas UI)
│
├── assets/
│   └── css/
│       └── libros.css              # Ejercicio 3 - CSS
│
├── archive-libro.php               # Ejercicio 3 - Template
│
├── functions.php                   # Includes
│
├── RESPUESTAS.md                   # Parte 1 - Teóricas
│
└── README.md                       # Instrucciones
```

### Contenido mínimo del functions.php

```php
<?php
// Incluir archivos
require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/rest-api.php';

// Si ACF por código:
// require_once get_template_directory() . '/inc/acf-fields.php';

// Enqueue CSS
function enqueue_libros_styles() {
    if (is_post_type_archive('libro') || is_tax(['genero', 'autor_libro'])) {
        wp_enqueue_style(
            'libros-css',
            get_template_directory_uri() . '/assets/css/libros.css',
            [],
            '1.0.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_libros_styles');

// Theme support
add_theme_support('post-thumbnails');
?>
```

---

## README.md REQUERIDO

Crea un README.md con:

```markdown
# Sistema de Biblioteca WordPress

**Desarrollador:** [Tu Nombre]
**Fecha:** [Fecha]
**Tiempo empleado:** [X horas]

## Instalación

1. Clonar repositorio
2. Copiar archivos al tema
3. Importar campos ACF (si usaste UI)
4. Activar tema
5. Ir a Ajustes > Enlaces permanentes > Guardar

## Testing

### Crear libros de prueba
- Crear 5-6 libros mínimo
- Asignar géneros y autores
- Completar todos los campos ACF
- Agregar imágenes destacadas

### Probar Archive
- Visitar /libros/
- Probar filtros
- Verificar responsive

### Probar API
```bash
GET /wp-json/biblioteca/v1/libros
GET /wp-json/biblioteca/v1/libros/123
GET /wp-json/biblioteca/v1/generos
```

## Decisiones técnicas

[Explica brevemente tus decisiones de implementación]

## Problemas encontrados

[Si tuviste algún problema o limitación]
```

---

## ✅ CHECKLIST ANTES DE ENTREGAR

Verifica que completaste:

- [ ] `RESPUESTAS.md` con las 4 respuestas teóricas
- [ ] `inc/post-types.php` con CPT y 2 taxonomías funcionando
- [ ] `inc/rest-api.php` con los 3 endpoints funcionando
- [ ] `acf-export/libro-fields.json` O `inc/acf-fields.php` con campos ACF
- [ ] `archive-libro.php` con filtros, query y loop completo
- [ ] `assets/css/libros.css` con estilos responsive
- [ ] `functions.php` con los includes necesarios
- [ ] `README.md` con instrucciones
- [ ] Al menos 5 libros de prueba creados
- [ ] Tested en desktop y mobile
- [ ] Tested los endpoints con Postman/Insomnia
- [ ] Commits con mensajes descriptivos
- [ ] Branch con formato correcto: `test/tu-nombre-apellido`

---

## CRITERIOS DE EVALUACIÓN

### Funcionalidad
- CPT, taxonomías y términos se crean correctamente
- ACF fields guardados y funcionan
- Filtros y queries funcionan correctamente
- REST API retorna datos válidos en formato esperado
- UI responsive funciona en mobile y desktop

### Código Limpio
- Código organizado y legible
- Nombres descriptivos
- Comentarios donde sea necesario (no excesivos)
- Sin código repetido
- Buena estructura de archivos

### Seguridad
- **CRÍTICO:** Sanitización de TODOS los inputs
- **CRÍTICO:** Escape de TODAS las salidas
- Validación de datos en REST API
- Prepared statements si usas $wpdb (no es necesario en este test)

### WordPress Best Practices
- Hooks usados correctamente
- Template hierarchy respetado
- `wp_reset_postdata()` donde corresponde
- Nomenclatura WordPress (snake_case)
- Enqueue de estilos correcto

¡Buena suerte! 📚✨

