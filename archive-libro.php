<?php

/**
 * EJERCICIO 3: Template Archive para CPT "Libro"
 * 
 * Este template muestra la lista de libros con:
 * - Filtros por género, precio máximo y búsqueda
 * - Query personalizada con WP_Query
 * - Loop con cards responsive
 * - Paginación
 * 
 * Archive templates muestran listas de posts.
 * WordPress busca archive-{post_type}.php automáticamente.
 * 
 * @package BibliotecaWordPress
 */

get_header();  // Incluye header.php del tema
?>

<main class="biblioteca-main">
    <div class="biblioteca-container">

        <!-- ================================ -->
        <!-- TÍTULO DE LA PÁGINA              -->
        <!-- ================================ -->
        <header class="biblioteca-header">
            <h1 class="biblioteca-titulo">Biblioteca de Libros</h1>
            <p class="biblioteca-descripcion">Explora nuestra colección de libros</p>
        </header>

        <!-- ================================ -->
        <!-- FORMULARIO DE FILTROS            -->
        <!-- ================================ -->
        <?php
        /**
         * IMPORTANTE: se usa esc_attr() para escapar valores que van en atributos HTML
         */

        // Obtener valores actuales de la URL (ya sanitizados)
        $filtro_genero     = isset($_GET['genero']) ? sanitize_text_field($_GET['genero']) : '';
        // Fix: Verificar !empty para evitar que floatval("") devuelva 0 y sature el input
        $filtro_precio_max = !empty($_GET['precio_max']) ? floatval($_GET['precio_max']) : '';
        $filtro_busqueda   = isset($_GET['busqueda']) ? sanitize_text_field($_GET['busqueda']) : '';
        ?>

        <form class="biblioteca-filtros" method="GET" action="<?php echo esc_url(get_post_type_archive_link('libro')); ?>">

            <!-- Filtro: Género -->
            <div class="filtro-grupo">
                <label for="genero">Género</label>
                <select name="genero" id="genero">
                    <option value="">Todos los géneros</option>
                    <?php
                    // Cargar géneros dinámicamente desde la taxonomía
                    $generos = get_terms([
                        'taxonomy'   => 'genero',
                        'hide_empty' => false,  // Mostrar todos, incluso sin libros
                    ]);

                    if (!is_wp_error($generos)) {
                        foreach ($generos as $genero) {
                            // selected() es helper de WP - imprime 'selected' si coincide
                            printf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr($genero->slug),
                                selected($filtro_genero, $genero->slug, false),
                                esc_html($genero->name)
                            );
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- Filtro: Precio máximo -->
            <div class="filtro-grupo">
                <label for="precio_max">Precio máximo</label>
                <input
                    type="number"
                    name="precio_max"
                    id="precio_max"
                    min="0"
                    step="100"
                    placeholder="Ej: 50000"
                    value="<?php echo esc_attr($filtro_precio_max); ?>">
            </div>

            <!-- Filtro: Búsqueda por título -->
            <div class="filtro-grupo">
                <label for="busqueda">Buscar</label>
                <input
                    type="text"
                    name="busqueda"
                    id="busqueda"
                    placeholder="Buscar por título..."
                    value="<?php echo esc_attr($filtro_busqueda); ?>">
            </div>

            <!-- Botones -->
            <div class="filtro-botones">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="<?php echo esc_url(get_post_type_archive_link('libro')); ?>" class="btn btn-secondary" style="color: #ffffff !important;">
                    Limpiar filtros
                </a>
            </div>
        </form>

        <!-- ================================ -->
        <!-- QUERY PERSONALIZADA + LOOP       -->
        <!-- ================================ -->
        <?php

        // Obtener página actual para paginación
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        // Armar argumentos base
        $args = [
            'post_type'      => 'libro',
            'post_status'    => 'publish',
            'posts_per_page' => 12,          // 12 libros por página
            'paged'          => $paged,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];

        // Agregar búsqueda por título si existe
        if (!empty($filtro_busqueda)) {
            $args['s'] = $filtro_busqueda;
        }

        // Agregar filtro por taxonomía género
        if (!empty($filtro_genero)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'genero',
                    'field'    => 'slug',
                    'terms'    => $filtro_genero,
                ],
            ];
        }

        // Agregar filtro por precio máximo (meta field de ACF)
        // Agregar filtro por precio máximo (Considerando OFERTAS)
        if (!empty($filtro_precio_max)) {
            $args['meta_query'] = [
                'relation' => 'OR',
                // Opción 1: Precio normal es menor al filtro (y no es oferta o lo que sea)
                [
                    'key'     => 'precio',
                    'value'   => $filtro_precio_max,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ],
                // Opción 2: Es oferta Y el precio oferta es menor al filtro
                [
                    'relation' => 'AND',
                    [
                        'key'     => 'en_oferta',
                        'value'   => '1', // ACF True
                        'compare' => '=',
                    ],
                    [
                        'key'     => 'precio_oferta',
                        'value'   => $filtro_precio_max,
                        'compare' => '<=',
                        'type'    => 'NUMERIC',
                    ]
                ]
            ];
        }

        // Ejecutar query
        $libros_query = new WP_Query($args);
        ?>

        <!-- Grid de libros -->
        <?php if ($libros_query->have_posts()) : ?>

            <div class="biblioteca-grid">

                <?php while ($libros_query->have_posts()) : $libros_query->the_post(); ?>

                    <?php
                    // Obtener datos ACF del libro actual
                    $precio        = get_field('precio');
                    $en_oferta     = get_field('en_oferta');
                    $precio_oferta = get_field('precio_oferta');
                    $paginas       = get_field('paginas');
                    $stock         = get_field('stock_disponible');

                    // Obtener géneros del libro
                    $generos_libro = get_the_terms(get_the_ID(), 'genero');

                    // Imagen destacada o placeholder
                    $tiene_imagen = has_post_thumbnail();
                    ?>

                    <article class="libro-card <?php echo $en_oferta ? 'en-oferta' : ''; ?>">

                        <!-- Imagen -->
                        <div class="libro-card__imagen-wrapper">
                            <?php if ($tiene_imagen) : ?>
                                <?php the_post_thumbnail('medium', ['class' => 'libro-card__imagen']); ?>
                            <?php else : ?>
                                <div class="libro-card__placeholder">📚</div>
                            <?php endif; ?>

                            <?php if ($en_oferta) : ?>
                                <span class="libro-badge-oferta">EN OFERTA</span>
                            <?php endif; ?>

                            <?php if ($stock == 1) : ?>
                                <span class="libro-badge libro-badge--stock">¡ÚLTIMO!</span>
                            <?php endif; ?>
                        </div>

                        <!-- Contenido -->
                        <div class="libro-card__contenido">

                            <!-- Título con link al single -->
                            <h2 class="libro-card__titulo">
                                <a href="<?php echo esc_url(get_permalink()); ?>">
                                    <?php echo esc_html(get_the_title()); ?>
                                </a>
                            </h2>

                            <!-- Excerpt (máx 150 caracteres, podria ser más ) -->
                            <p class="libro-card__excerpt">
                                <?php
                                $excerpt = get_the_excerpt();
                                echo esc_html(mb_strimwidth($excerpt, 0, 150, '...'));
                                ?>
                            </p>

                            <!-- Precio -->
                            <div class="libro-card__precio">
                                <?php if ($en_oferta && $precio_oferta) : ?>
                                    <span class="precio-original">$<?php echo esc_html(number_format($precio, 0, ',', '.')); ?></span>
                                    <span class="precio-oferta">$<?php echo esc_html(number_format($precio_oferta, 0, ',', '.')); ?></span>
                                <?php elseif ($precio) : ?>
                                    <span class="precio-normal">$<?php echo esc_html(number_format($precio, 0, ',', '.')); ?></span>
                                <?php else : ?>
                                    <span class="precio-consultar">Consultar precio</span>
                                <?php endif; ?>
                            </div>

                            <!-- Meta: Géneros + Páginas -->
                            <div class="libro-card__meta">
                                <?php if ($generos_libro && !is_wp_error($generos_libro)) : ?>
                                    <div class="libro-card__generos">
                                        <?php foreach ($generos_libro as $genero) : ?>
                                            <a href="<?php echo esc_url(get_term_link($genero)); ?>" class="genero-badge">
                                                <?php echo esc_html($genero->name); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($paginas) : ?>
                                    <span class="libro-card__paginas">
                                        📄 <?php echo esc_html($paginas); ?> pág.
                                    </span>
                                <?php endif; ?>
                            </div>

                        </div>

                    </article>

                <?php endwhile; ?>

            </div>

            <!-- ================================ -->
            <!-- PAGINACIÓN                       -->
            <!-- ================================ -->
            <nav class="biblioteca-paginacion">
                <?php
                // the_posts_pagination() genera links de paginación
                // Funciona con WP_Query si se usa $libros_query->max_num_pages

                $big = 999999999; // Número grande para replace

                echo paginate_links([
                    'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                    'format'    => '?paged=%#%',
                    'current'   => max(1, $paged),
                    'total'     => $libros_query->max_num_pages,
                    'prev_text' => '← Anterior',
                    'next_text' => 'Siguiente →',
                    'mid_size'  => 2,
                ]);
                ?>
            </nav>

        <?php else : ?>

            <!-- Estado: Sin resultados -->
            <div class="biblioteca-sin-resultados">
                <div class="sin-resultados-icono">📭</div>
                <h2>No se encontraron libros</h2>
                <p>Intenta con otros filtros o
                    <a href="<?php echo esc_url(get_post_type_archive_link('libro')); ?>">ver todos los libros</a>
                </p>
            </div>

        <?php endif; ?>

        <?php
        // Es muy importante restaurar el post global después de WP_Query custom
        wp_reset_postdata();
        ?>

    </div>
</main>

<?php get_footer(); // Incluye footer.php del tema 
?>