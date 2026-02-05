<?php

/**
 * Functions.php - Biblioteca WordPress
 * 
 * Este archivo es el punto de entrada del tema.
 * Incluye todos los demás archivos y configura funcionalidades básicas.
 * 
 * @package BibliotecaWordPress
 */

// ============================================
// INCLUIR ARCHIVOS
// ============================================

/**
 * get_template_directory() devuelve la ruta absoluta del tema.
 * Se usa require_once (no include) porque si falla, queremos
 * que WordPress muestre error - son archivos críticos.
 */

// Ejercicio 1: Custom Post Type + Taxonomías
require_once get_template_directory() . '/inc/post-types.php';

// Ejercicio 4: REST API Custom
require_once get_template_directory() . '/inc/rest-api.php';

// Ejercicio 2 (si ACF por código): Descomentar si usás código en vez de JSON
// require_once get_template_directory() . '/inc/acf-fields.php';


// ============================================
// ENQUEUE DE ESTILOS
// ============================================

/**
 * Carga el CSS de la biblioteca solo donde se necesita
 * 
 * Hook: 'wp_enqueue_scripts' - para frontend (no admin)
 */
add_action('wp_enqueue_scripts', 'biblioteca_enqueue_styles');

function biblioteca_enqueue_styles()
{

    // Solo cargar en archive de libros o taxonomías relacionadas
    // Esto mejora performance - no se carga CSS innecesario en otras páginas
    if (is_post_type_archive('libro') || is_singular('libro') || is_tax(['genero', 'autor_libro'])) {

        wp_enqueue_style(
            'libros-css',                                           // Handle (identificador único)
            get_template_directory_uri() . '/assets/css/libros.css', // URL del archivo
            [],                                                      // Dependencias (ninguna)
            time() + 9                                              // Versión (Cache busting: time())
        );
    }
}


// ============================================
// THEME SUPPORT
// ============================================

/**
 * Habilita características del tema
 * 
 * Hook: 'after_setup_theme' - se ejecuta después de cargar el tema
 */
add_action('after_setup_theme', 'biblioteca_theme_setup');

function biblioteca_theme_setup()
{

    // Habilitar imágenes destacadas (thumbnails)
    add_theme_support('post-thumbnails');

    // Habilitar title-tag (WP maneja el <title>)
    add_theme_support('title-tag');

    // Habilitar HTML5 para formularios y galerías
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'gallery',
        'caption',
    ]);
}


// ============================================
// TAMAÑOS DE IMAGEN CUSTOM (Opcional)
// ============================================

/**
 * Define tamaños de imagen personalizados para las portadas
 */
add_image_size('libro-card', 400, 300, true);      // Para cards del archive
add_image_size('libro-single', 800, 600, true);    // Para página single


// ============================================
// HELPER: FORMATEAR PRECIO
// ============================================

/**
 * Formatea un número como precio en pesos (ARS/CLP style)
 * 
 * @param float $precio - El precio a formatear
 * @return string - Precio formateado (ej: "$45.000")
 */
function biblioteca_formatear_precio($precio)
{
    if (empty($precio) || !is_numeric($precio)) {
        return 'Consultar';
    }

    return '$' . number_format($precio, 0, ',', '.');
}
