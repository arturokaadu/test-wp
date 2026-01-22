<?php

/**
 * EJERCICIO 4: REST API Custom para Biblioteca
 * 
 * Este archivo registra 3 endpoints custom:
 * 1. GET /wp-json/biblioteca/v1/libros       - Lista libros con filtros
 * 2. GET /wp-json/biblioteca/v1/libros/{id}  - Detalle de un libro
 * 3. GET /wp-json/biblioteca/v1/generos      - Lista géneros

 * @package BibliotecaWordPress
 */


// REGISTRAR ENDPOINTS


/**
 * Hook 'rest_api_init' - Se ejecuta cuando la REST API se inicializa
 */
add_action('rest_api_init', 'biblioteca_registrar_endpoints');

function biblioteca_registrar_endpoints()
{

    // ENDPOINT 1: GET /wp-json/biblioteca/v1/libros
    // Lista todos los libros con filtros opcionales
    register_rest_route(
        'biblioteca/v1',          // Namespace (prefijo de la API)
        '/libros',                 // Ruta del endpoint
        [
            'methods'             => 'GET',                              // Método HTTP
            'callback'            => 'biblioteca_get_libros',            // Función que responde
            'permission_callback' => '__return_true',                    // Público (sin auth)
            'args'                => biblioteca_get_libros_args(),       // Parámetros permitidos
        ]
    );

    // ENDPOINT 2: GET /wp-json/biblioteca/v1/libros/{id}
    // Detalle de un libro específico
    register_rest_route(
        'biblioteca/v1',
        '/libros/(?P<id>\d+)',    // (?P<id>\d+) = captura números como 'id'
        [
            'methods'             => 'GET',
            'callback'            => 'biblioteca_get_libro_single',
            'permission_callback' => '__return_true',
            'args'                => [
                'id' => [
                    'validate_callback' => function ($param) {
                        return is_numeric($param);  // Validar que sea número
                    },
                    'required'          => true,
                ],
            ],
        ]
    );

    // ENDPOINT 3: GET /wp-json/biblioteca/v1/generos
    // Lista todos los géneros
    register_rest_route(
        'biblioteca/v1',
        '/generos',
        [
            'methods'             => 'GET',
            'callback'            => 'biblioteca_get_generos',
            'permission_callback' => '__return_true',
        ]
    );
}


// ============================================
// DEFINIR ARGUMENTOS DEL ENDPOINT /libros
// ============================================

/**
 * Define los parámetros aceptados por GET /libros
 * 
 * SE USA 'sanitize_callback' para limpiar datos de entrada
 * 'validate_callback' verifica que sean válidos
 * 'default' es el valor si no se envía
 */
function biblioteca_get_libros_args()
{
    return [
        'per_page' => [
            'description'       => 'Libros por página (max 50)',
            'type'              => 'integer',
            'default'           => 10,
            'minimum'           => 1,
            'maximum'           => 50,
            'sanitize_callback' => 'absint',  // Convierte a integer positivo
        ],
        'page' => [
            'description'       => 'Número de página',
            'type'              => 'integer',
            'default'           => 1,
            'minimum'           => 1,
            'sanitize_callback' => 'absint',
        ],
        'genero' => [
            'description'       => 'Filtrar por slug de género',
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'precio_max' => [
            'description'       => 'Precio máximo',
            'type'              => 'number',
            'sanitize_callback' => function ($value) {
                return floatval($value);
            },
        ],
        'en_oferta' => [
            'description'       => 'Solo libros en oferta',
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
        ],
        'search' => [
            'description'       => 'Buscar por título',
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
    ];
}


// ============================================
// CALLBACK: GET /libros
// ============================================

/**
 * Responde al endpoint GET /wp-json/biblioteca/v1/libros
 * 
 * @param WP_REST_Request $request - Objeto con los datos de la petición
 * @return WP_REST_Response - Respuesta JSON
 * 
 */
function biblioteca_get_libros($request)
{

    // === OBTENER Y SANITIZAR PARÁMETROS ===
    // Ya fueron sanitizados por los callbacks definidos arriba,
    // pero igual se accede de forma segura via $request->get_param()
    $per_page   = $request->get_param('per_page') ?: 10;
    $page       = $request->get_param('page') ?: 1;
    $genero     = $request->get_param('genero');
    $precio_max = $request->get_param('precio_max');
    $en_oferta  = $request->get_param('en_oferta');
    $search     = $request->get_param('search');

    // === ARMAR ARGUMENTOS DE WP_QUERY ===
    $args = [
        'post_type'      => 'libro',
        'post_status'    => 'publish',         // Solo publicados
        'posts_per_page' => min($per_page, 50), // Máximo 50
        'paged'          => $page,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ];

    // FILTRO: Búsqueda por título
    // Parámetro 's' de WP_Query busca en título y contenido
    if (!empty($search)) {
        $args['s'] = $search;
    }

    // FILTRO: Por taxonomía género
    // tax_query permite filtrar por términos de taxonomías
    if (!empty($genero)) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'genero',
                'field'    => 'slug',      // Buscar por slug, no por ID
                'terms'    => $genero,
            ],
        ];
    }

    // FILTRO: Por meta fields (precio, en_oferta)
    // meta_query filtra por campos ACF/meta
    $meta_query = [];

    if (!empty($precio_max)) {
        $meta_query[] = [
            'key'     => 'precio',          // Nombre del campo ACF
            'value'   => $precio_max,
            'compare' => '<=',              // Menor o igual
            'type'    => 'NUMERIC',         // Comparar como número
        ];
    }

    if ($en_oferta === true) {
        $meta_query[] = [
            'key'     => 'en_oferta',
            'value'   => '1',               // ACF guarda true/false como 1/0
            'compare' => '=',
        ];
    }

    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }

    // === EJECUTAR QUERY ===
    $query = new WP_Query($args);

    // === FORMATEAR RESPUESTA ===
    $libros = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $libros[] = biblioteca_formatear_libro_lista(get_the_ID());
        }
        wp_reset_postdata();  // es importante restaurar post global
    }

    // === RETORNAR RESPUESTA ===
    return new WP_REST_Response([
        'success'    => true,
        'data'       => $libros,
        'pagination' => [
            'total'        => (int) $query->found_posts,
            'pages'        => (int) $query->max_num_pages,
            'current_page' => (int) $page,
            'per_page'     => (int) $per_page,
        ],
    ], 200);  // 200 = HTTP OK
}


// ============================================
// CALLBACK: GET /libros/{id}
// ============================================

/**
 * Responde al endpoint GET /wp-json/biblioteca/v1/libros/{id}
 * Devuelve detalle completo de un libro
 */
function biblioteca_get_libro_single($request)
{

    $id = (int) $request->get_param('id');

    // Verificar que existe y es un libro publicado
    $post = get_post($id);

    if (!$post || $post->post_type !== 'libro' || $post->post_status !== 'publish') {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Libro no encontrado',
        ], 404);  // 404 = Not Found
    }

    // Formatear respuesta completa
    $libro = biblioteca_formatear_libro_detalle($id);

    return new WP_REST_Response([
        'success' => true,
        'data'    => $libro,
    ], 200);
}


// ============================================
// CALLBACK: GET /generos
// ============================================

/**
 * Responde al endpoint GET /wp-json/biblioteca/v1/generos
 * Lista todos los géneros con contador de libros
 */
function biblioteca_get_generos($request)
{

    // get_terms() obtiene términos de una taxonomía
    $terms = get_terms([
        'taxonomy'   => 'genero',
        'hide_empty' => true,      // Solo géneros que tienen libros
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    // Manejar error
    if (is_wp_error($terms)) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Error al obtener géneros',
        ], 500);
    }

    // Formatear respuesta
    $generos = [];

    foreach ($terms as $term) {
        $generos[] = [
            'id'          => (int) $term->term_id,
            'nombre'      => esc_html($term->name),       // ESCAPE: sanitizar salida
            'slug'        => esc_attr($term->slug),
            'descripcion' => esc_html($term->description),
            'count'       => (int) $term->count,          // Cantidad de libros
        ];
    }

    return new WP_REST_Response([
        'success' => true,
        'data'    => $generos,
    ], 200);
}


// ============================================
// FUNCIONES HELPER: FORMATEAR DATOS
// ============================================

/**
 * Formatea un libro para la lista (menos datos)
 * 
 * @param int $post_id - ID del libro
 * @return array - Datos formateados
 */
function biblioteca_formatear_libro_lista($post_id)
{

    // get_field() es de ACF - obtiene campos custom
    // Si ACF no está instalado, devuelve null
    $precio        = get_field('precio', $post_id);
    $en_oferta     = get_field('en_oferta', $post_id);
    $precio_oferta = get_field('precio_oferta', $post_id);
    $paginas       = get_field('paginas', $post_id);
    $isbn          = get_field('isbn', $post_id);

    // Obtener términos de taxonomías
    $generos = wp_get_post_terms($post_id, 'genero', ['fields' => 'slugs']);
    $autores = wp_get_post_terms($post_id, 'autor_libro', ['fields' => 'names']);

    // Imagen destacada
    $imagen_url = get_the_post_thumbnail_url($post_id, 'medium');

    return [
        'id'            => (int) $post_id,
        'titulo'        => esc_html(get_the_title($post_id)),
        'slug'          => get_post_field('post_name', $post_id),
        'excerpt'       => esc_html(wp_trim_words(get_the_excerpt($post_id), 25)),
        'link'          => esc_url(get_permalink($post_id)),
        'imagen_url'    => esc_url($imagen_url ?: ''),
        'generos'       => is_array($generos) ? $generos : [],
        'autores'       => is_array($autores) ? $autores : [],
        'precio'        => (float) ($precio ?: 0),
        'en_oferta'     => (bool) $en_oferta,
        'precio_oferta' => $en_oferta ? (float) $precio_oferta : null,
        'paginas'       => (int) ($paginas ?: 0),
        'isbn'          => esc_html($isbn ?: ''),
    ];
}

/**
 * Formatea un libro para detalle (todos los datos)
 * 
 * @param int $post_id - ID del libro
 * @return array - Datos completos
 */
function biblioteca_formatear_libro_detalle($post_id)
{

    // Obtener todos los campos ACF
    $isbn            = get_field('isbn', $post_id);
    $ano_publicacion = get_field('ano_publicacion', $post_id);
    $editorial       = get_field('editorial', $post_id);
    $paginas         = get_field('paginas', $post_id);
    $precio          = get_field('precio', $post_id);
    $stock           = get_field('stock_disponible', $post_id);
    $en_oferta       = get_field('en_oferta', $post_id);
    $precio_oferta   = get_field('precio_oferta', $post_id);
    $resenas         = get_field('resenas', $post_id);  // Repeater field

    // Taxonomías
    $generos = wp_get_post_terms($post_id, 'genero', ['fields' => 'slugs']);
    $autores = wp_get_post_terms($post_id, 'autor_libro', ['fields' => 'names']);

    // Imagen
    $imagen_url = get_the_post_thumbnail_url($post_id, 'large');

    // Formatear reseñas y calcular promedio
    $resenas_formateadas = [];
    $total_rating = 0;

    if ($resenas && is_array($resenas)) {
        foreach ($resenas as $resena) {
            $rating = (int) ($resena['rating'] ?? 0);
            $total_rating += $rating;

            $resenas_formateadas[] = [
                'nombre'     => esc_html($resena['nombre_del_reviewer'] ?? ''),
                'rating'     => $rating,
                'comentario' => esc_html($resena['comentario'] ?? ''),
            ];
        }
    }

    $total_resenas   = count($resenas_formateadas);
    $rating_promedio = $total_resenas > 0 ? round($total_rating / $total_resenas, 1) : 0;

    return [
        'id'               => (int) $post_id,
        'titulo'           => esc_html(get_the_title($post_id)),
        'slug'             => get_post_field('post_name', $post_id),
        'contenido'        => apply_filters('the_content', get_post_field('post_content', $post_id)),
        'excerpt'          => esc_html(get_the_excerpt($post_id)),
        'fecha_publicacion' => get_the_date('Y-m-d', $post_id),
        'link'             => esc_url(get_permalink($post_id)),
        'imagen_url'       => esc_url($imagen_url ?: ''),
        'generos'          => is_array($generos) ? $generos : [],
        'autores'          => is_array($autores) ? $autores : [],
        'precio'           => (float) ($precio ?: 0),
        'en_oferta'        => (bool) $en_oferta,
        'precio_oferta'    => $en_oferta ? (float) $precio_oferta : null,
        'stock'            => (int) ($stock ?: 0),
        'paginas'          => (int) ($paginas ?: 0),
        'isbn'             => esc_html($isbn ?: ''),
        'editorial'        => esc_html($editorial ?: ''),
        'ano_publicacion'  => (int) ($ano_publicacion ?: 0),
        'resenas'          => $resenas_formateadas,
        'rating_promedio'  => $rating_promedio,
        'total_resenas'    => $total_resenas,
    ];
}
