<?php

/**
 * Template: Single Libro
 * 
 * Muestra el detalle de un libro individual y libros relacionados.
 */
get_header();
?>

<div class="container main-content">

    <a href="<?php echo get_post_type_archive_link('libro'); ?>" class="btn-volver">
        <span class="btn-volver__icono">←</span> Volver a la Biblioteca
    </a>

    <?php while (have_posts()) : the_post();
        $current_id = get_the_ID();
        $precio = get_field('precio');
        $en_oferta = get_field('en_oferta');
        $precio_oferta = get_field('precio_oferta');
    ?>

        <!-- DETALLE DEL LIBRO -->
        <article class="libro-detalle">

            <!-- Columna Izquierda: Imagen -->
            <div class="libro-detalle__imagen">
                <?php
                if (has_post_thumbnail()) {
                    the_post_thumbnail('large');
                } else {
                    echo '<img src="https://placehold.co/400x600?text=No+Image" alt="Sin portada">';
                }
                ?>
            </div>

            <!-- Columna Derecha: Info -->
            <div class="libro-detalle__info">
                <h1 class="libro-detalle__titulo"><?php the_title(); ?></h1>

                <div class="libro-detalle__meta">
                    <?php
                    $generos = get_the_terms($current_id, 'genero');
                    if ($generos) {
                        foreach ($generos as $genero) {
                            echo '<span class="libro-badge libro-badge--genero">' . esc_html($genero->name) . '</span>';
                        }
                    }

                    $autores = get_the_terms($current_id, 'autor_libro');
                    if ($autores) {
                        echo '<p class="libro-detalle__autor">Por: <strong>' . esc_html($autores[0]->name) . '</strong></p>';
                    }
                    ?>
                </div>

                <div class="libro-card__precio-box">
                    <?php if ($en_oferta) : ?>
                        <span class="libro-card__precio libro-card__precio--tachado">
                            <?php echo biblioteca_formatear_precio($precio); ?>
                        </span>
                        <span class="libro-card__precio libro-card__precio--oferta">
                            <?php echo biblioteca_formatear_precio($precio_oferta); ?>
                        </span>
                        <span class="libro-badge libro-badge--oferta">OFERTA</span>
                    <?php else : ?>
                        <span class="libro-card__precio">
                            <?php echo biblioteca_formatear_precio($precio); ?>
                        </span>
                    <?php endif; ?>

                    <?php
                    $stock = get_field('stock_disponible');
                    if ($stock == 1) :
                    ?>
                        <span class="libro-badge libro-badge--stock">¡ÚLTIMO EN STOCK!</span>
                    <?php endif; ?>
                </div>

                <div class="libro-detalle__descripcion">
                    <?php
                    // Mostrar excerpt como descripción
                    $excerpt = get_the_excerpt();
                    if ($excerpt) {
                        echo '<p>' . esc_html($excerpt) . '</p>';
                    } else {
                        echo '<p><em>Sin descripción disponible.</em></p>';
                    }
                    ?>
                </div>

                <div class="libro-detalle__extra">
                    <p><strong>ISBN:</strong> <?php echo esc_html(get_field('isbn')); ?></p>
                    <p><strong>Páginas:</strong> <?php echo esc_html(get_field('paginas')); ?></p>
                    <p><strong>Editorial:</strong> <?php echo esc_html(get_field('editorial')); ?></p>
                </div>
            </div>

        </article>

        <hr class="separador">

        <!-- SECCIÓN DE RESEÑAS -->
        <section class="libro-resenas">
            <h3 class="libro-resenas__titulo">Opiniones de los Lectores</h3>

            <div class="libro-resenas__grid">
                <?php
                // se obtiene comentarios aprobados
                $comments = get_comments([
                    'post_id' => get_the_ID(),
                    'status'  => 'approve'
                ]);

                if ($comments) :
                    foreach ($comments as $comment) :
                        // Simula rating 5 si no existe (logic igual a la API)
                        $rating = (int) get_comment_meta($comment->comment_ID, 'rating', true);
                        if (!$rating) $rating = 5;
                ?>
                        <div class="resena-card">
                            <div class="resena-card__header">
                                <div class="resena-card__avatar">
                                    <?php echo get_avatar($comment, 48); ?>
                                </div>
                                <div class="resena-card__meta">
                                    <span class="resena-card__autor"><?php echo esc_html($comment->comment_author); ?></span>
                                    <div class="resena-card__stars" title="<?php echo $rating; ?> de 5 estrellas">
                                        <?php
                                        // Renderizar estrellas
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo ($i <= $rating) ? '<span class="star filled">★</span>' : '<span class="star empty">☆</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="resena-card__body">
                                <?php comment_text($comment->comment_ID); ?>
                            </div>
                        </div>
                    <?php
                    endforeach;
                else :
                    ?>
                    <p class="libro-resenas__vacio">Todavía no hay reseñas para este libro. ¡Contanos qué te pareció en los comentarios!</p>
                <?php endif; ?>
            </div>

            <!-- Link para dejar comentario (opcional, lleva al form nativo si existiera) -->
            <?php if (comments_open()) : ?>
                <div class="libro-resenas__accion">
                    <?php comment_form(); // Muestra el formulario nativo 
                    ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- LIBROS RELACIONADOS -->
        <section class="libros-relacionados">
            <h3>También te podría gustar</h3>

            <?php
            // LOGICA DE RELACIONADOS
            $args_relacionados = [
                'post_type'      => 'libro',
                'post_status'    => 'publish',
                'posts_per_page' => 3,
                'post__not_in'   => [$current_id],
                'orderby'        => 'rand',
            ];

            // Si tiene géneros, filtrar por ellos
            $generos_ids = wp_get_post_terms($current_id, 'genero', ['fields' => 'ids']);
            if ($generos_ids && !is_wp_error($generos_ids) && !empty($generos_ids)) {
                $args_relacionados['tax_query'] = [
                    [
                        'taxonomy' => 'genero',
                        'field'    => 'term_id',
                        'terms'    => $generos_ids,
                    ]
                ];
            }

            $relacionados = new WP_Query($args_relacionados);

            // Si no hay resultados con el mismo género, buscar aleatorios sin filtro
            if (!$relacionados->have_posts() && !empty($generos_ids)) {
                unset($args_relacionados['tax_query']);
                $relacionados = new WP_Query($args_relacionados);
            }

            if ($relacionados->have_posts()) :
                echo '<div class="biblioteca-grid">';
                while ($relacionados->have_posts()) : $relacionados->the_post();
                    $r_precio    = get_field('precio');
                    $r_en_oferta = get_field('en_oferta');
                    $r_precio_of = get_field('precio_oferta');
            ?>
                    <article class="libro-card <?php echo $r_en_oferta ? 'en-oferta' : ''; ?>">
                        <div class="libro-card__imagen-wrapper">
                            <a href="<?php the_permalink(); ?>">
                                <?php
                                if (has_post_thumbnail()) {
                                    the_post_thumbnail('medium', ['class' => 'libro-card__imagen']);
                                } else {
                                    echo '<div class="libro-card__placeholder">📚</div>';
                                }
                                ?>
                            </a>
                            <?php if ($r_en_oferta) : ?>
                                <span class="libro-badge-oferta">EN OFERTA</span>
                            <?php endif; ?>
                        </div>
                        <div class="libro-card__contenido">
                            <h4 class="libro-card__titulo">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                            <div class="libro-card__precio">
                                <?php if ($r_en_oferta && $r_precio_of) : ?>
                                    <span class="precio-original"><?php echo biblioteca_formatear_precio($r_precio); ?></span>
                                    <span class="precio-oferta"><?php echo biblioteca_formatear_precio($r_precio_of); ?></span>
                                <?php else : ?>
                                    <span class="precio-normal"><?php echo biblioteca_formatear_precio($r_precio); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
            <?php
                endwhile;
                echo '</div>';
                wp_reset_postdata();
            else :
                echo '<p>No hay libros relacionados por ahora.</p>';
            endif;
            ?>
        </section>

    <?php endwhile; ?>

</div>

<?php get_footer(); ?>