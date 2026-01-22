<?php

/**
 * EJERCICIO 1: Custom Post Type "Libro" + Taxonomías
 * 
 * Este archivo registra:
 * 1. CPT "Libro" - Para almacenar los libros de la biblioteca
 * 2. Taxonomía "Género" - Jerárquica (como categorías)
 * 3. Taxonomía "Autor" - No jerárquica (como tags)
 * 
 * @package BibliotecaWordPress
 */

// ============================================
// REGISTRAR CUSTOM POST TYPE: LIBRO
// ============================================

/**
 * Registra el Custom Post Type "Libro"
 * 
 * Hook usado: 'init' - Se ejecuta después de que WordPress carga pero antes
 * de enviar headers. Es el momento correcto para registrar CPTs.
 */
add_action('init', 'biblioteca_registrar_cpt_libro');

function biblioteca_registrar_cpt_libro()
{

    // LABELS: Textos que aparecen en el admin de WordPress
    // Definimos singular y plural para que el admin muestre textos correctos
    $labels = [
        'name'                  => 'Libros',                    // Nombre plural (menú)
        'singular_name'         => 'Libro',                     // Nombre singular
        'menu_name'             => 'Libros',                    // Texto en el menú admin
        'add_new'               => 'Añadir Nuevo',              // Botón añadir
        'add_new_item'          => 'Añadir Nuevo Libro',        // Título página añadir
        'edit_item'             => 'Editar Libro',              // Título página editar
        'new_item'              => 'Nuevo Libro',               // Nuevo item
        'view_item'             => 'Ver Libro',                 // Ver item
        'view_items'            => 'Ver Libros',                // Ver items
        'search_items'          => 'Buscar Libros',             // Buscar
        'not_found'             => 'No se encontraron libros',  // Sin resultados
        'not_found_in_trash'    => 'No hay libros en la papelera',
        'all_items'             => 'Todos los Libros',          // Submenú "todos"
        'archives'              => 'Archivo de Libros',         // Archive title
        'featured_image'        => 'Portada del Libro',         // Imagen destacada
        'set_featured_image'    => 'Establecer portada',
        'remove_featured_image' => 'Quitar portada',
        'use_featured_image'    => 'Usar como portada',
    ];

    // ARGUMENTOS: Configuración del CPT
    $args = [
        // === LABELS ===
        'labels'              => $labels,

        // === VISIBILIDAD ===
        'public'              => true,      // Visible en frontend y admin
        'publicly_queryable'  => true,      // Se puede consultar vía URL
        'show_ui'             => true,      // Mostrar interfaz en admin
        'show_in_menu'        => true,      // Mostrar en menú lateral
        'show_in_nav_menus'   => true,      // Disponible en menús de navegación
        'show_in_admin_bar'   => true,      // Mostrar en admin bar

        // === REST API ===
        'show_in_rest'        => true,      // ¡IMPORTANTE! Habilita REST API y Gutenberg
        'rest_base'           => 'libros',  // Endpoint: /wp-json/wp/v2/libros

        // === CAPACIDADES ===
        // 'capability_type'  => 'post',    // Usa las mismas capabilities que posts

        // === FEATURES SOPORTADAS ===
        'supports'            => [
            'title',           // Campo de título
            'editor',          // Editor de contenido (Gutenberg)
            'excerpt',         // Extracto/resumen
            'thumbnail',       // Imagen destacada (portada)
            'author',          // Selector de autor
            // 'comments',     // Comentarios (no pedido)
            // 'revisions',    // Historial de revisiones
        ],

        // === ARCHIVO ===
        'has_archive'         => true,      // Tiene página de archivo (/libros/)
        'archive_template'    => '',        // Usará archive-libro.php

        // === URLs ===
        'rewrite'             => [
            'slug'       => 'libros',       // URL: /libros/el-quijote
            'with_front' => true,           // Incluir prefijo del blog si existe
        ],

        // === ADMIN ===
        'menu_position'       => 5,         // Posición: 5 = debajo de Posts
        'menu_icon'           => 'dashicons-book',  // Ícono del menú

        // === QUERY ===
        'query_var'           => true,      // Permite ?libro=slug en queries
        'can_export'          => true,      // Exportable con herramientas WP

        // === JERÁRQUICO ===
        'hierarchical'        => false,     // false = como posts, true = como pages
    ];

    // register_post_type('nombre', $args)
    // 'nombre' debe ser max 20 caracteres, sin mayúsculas ni espacios
    register_post_type('libro', $args);
}


// ============================================
// REGISTRAR TAXONOMÍA: GÉNERO (Jerárquica)
// ============================================

/**
 * Registra la taxonomía "Género"
 */
add_action('init', 'biblioteca_registrar_taxonomia_genero');

function biblioteca_registrar_taxonomia_genero()
{

    $labels = [
        'name'                       => 'Géneros',
        'singular_name'              => 'Género',
        'menu_name'                  => 'Géneros',
        'all_items'                  => 'Todos los Géneros',
        'edit_item'                  => 'Editar Género',
        'view_item'                  => 'Ver Género',
        'update_item'                => 'Actualizar Género',
        'add_new_item'               => 'Añadir Nuevo Género',
        'new_item_name'              => 'Nombre del Nuevo Género',
        'parent_item'                => 'Género Padre',      // Solo jerárquicas
        'parent_item_colon'          => 'Género Padre:',
        'search_items'               => 'Buscar Géneros',
        'popular_items'              => 'Géneros Populares',
        'separate_items_with_commas' => 'Separar géneros con comas',
        'add_or_remove_items'        => 'Añadir o quitar géneros',
        'choose_from_most_used'      => 'Elegir de los más usados',
        'not_found'                  => 'No se encontraron géneros',
        'back_to_items'              => '← Volver a Géneros',
    ];

    $args = [
        'labels'              => $labels,

        // === TIPO ===
        'hierarchical'        => true,      // TRUE = como categorías (con padres)

        // === VISIBILIDAD ===
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_tagcloud'       => true,
        'show_in_quick_edit'  => true,
        'show_admin_column'   => true,      // Mostrar columna en listado admin

        // === REST API ===
        'show_in_rest'        => true,      // Habilitar REST API
        'rest_base'           => 'generos', // Endpoint: /wp-json/wp/v2/generos

        // === URLs ===
        'rewrite'             => [
            'slug'         => 'genero',     // URL: /genero/ficcion
            'with_front'   => true,
            'hierarchical' => true,         // URLs con jerarquía: /genero/ficcion/novela
        ],

        // === QUERY ===
        'query_var'           => true,
    ];

    // register_taxonomy('nombre', 'post_types', $args)
    // Segundo parámetro: array de CPTs donde aplica esta taxonomía
    register_taxonomy('genero', ['libro'], $args);
}


// ============================================
// REGISTRAR TAXONOMÍA: AUTOR (No Jerárquica)
// ============================================

/**
 * Registra la taxonomía "Autor del Libro"
 * 
 * Es NO jerárquica (como tags) - no tiene relación padre/hijo
 * Nota: Usamos 'autor_libro' para no confundir con 'author' de WordPress
 */
add_action('init', 'biblioteca_registrar_taxonomia_autor');

function biblioteca_registrar_taxonomia_autor()
{

    $labels = [
        'name'                       => 'Autores',
        'singular_name'              => 'Autor',
        'menu_name'                  => 'Autores',
        'all_items'                  => 'Todos los Autores',
        'edit_item'                  => 'Editar Autor',
        'view_item'                  => 'Ver Autor',
        'update_item'                => 'Actualizar Autor',
        'add_new_item'               => 'Añadir Nuevo Autor',
        'new_item_name'              => 'Nombre del Nuevo Autor',
        'search_items'               => 'Buscar Autores',
        'popular_items'              => 'Autores Populares',
        'separate_items_with_commas' => 'Separar autores con comas',
        'add_or_remove_items'        => 'Añadir o quitar autores',
        'choose_from_most_used'      => 'Elegir de los más usados',
        'not_found'                  => 'No se encontraron autores',
        'back_to_items'              => '← Volver a Autores',
    ];

    $args = [
        'labels'              => $labels,

        // === TIPO ===
        'hierarchical'        => false,     // FALSE = como tags (sin padres)

        // === VISIBILIDAD ===
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_tagcloud'       => true,
        'show_in_quick_edit'  => true,
        'show_admin_column'   => true,      // Columna en admin

        // === REST API ===
        'show_in_rest'        => true,
        'rest_base'           => 'autores',

        // === URLs ===
        'rewrite'             => [
            'slug'       => 'autor',        // URL: /autor/cervantes
            'with_front' => true,
        ],

        // === QUERY ===
        'query_var'           => true,
    ];

    register_taxonomy('autor_libro', ['libro'], $args);
}


// ============================================
// CREAR TÉRMINOS INICIALES (Géneros por defecto)
// ============================================

/**
 * Crea los géneros iniciales al activar el tema
 * 
 * Se usa 'after_switch_theme' para ejecutar solo una vez.
 * al activar el tema, no en cada page load.
 * 
 * wp_insert_term('nombre', 'taxonomia', $args) crea un término
 */
add_action('after_switch_theme', 'biblioteca_crear_generos_iniciales');

function biblioteca_crear_generos_iniciales()
{

    // Array de géneros a crear
    $generos = [
        'Ficción'    => 'Obras de imaginación narrativa',
        'No Ficción' => 'Obras basadas en hechos reales',
        'Técnico'    => 'Libros técnicos y manuales',
        'Biografía'  => 'Historias de vida de personas reales',
    ];

    foreach ($generos as $nombre => $descripcion) {
        // Verificar si ya existe para no duplicar
        if (!term_exists($nombre, 'genero')) {
            wp_insert_term(
                $nombre,            // Nombre del término
                'genero',           // Taxonomía
                [
                    'description' => $descripcion,
                    'slug'        => sanitize_title($nombre),  // Genera slug limpio
                ]
            );
        }
    }
}

// También ejecutar al init para desarrollo (útil si ya se activó el tema)
add_action('init', 'biblioteca_crear_generos_iniciales', 20); // Prioridad 20 = después de registrar taxonomía


// ============================================
// FLUSH REWRITE RULES (Solo al activar tema)
// ============================================

/**
 * Regenera las reglas de reescritura de URLs
 * 
 * IMPORTANTE: Solo se hace al activar el tema, NUNCA en cada page load

 */
add_action('after_switch_theme', 'biblioteca_flush_rewrites');

function biblioteca_flush_rewrites()
{
    // Primero registrar todo
    biblioteca_registrar_cpt_libro();
    biblioteca_registrar_taxonomia_genero();
    biblioteca_registrar_taxonomia_autor();

    // Luego flush
    flush_rewrite_rules();
}
