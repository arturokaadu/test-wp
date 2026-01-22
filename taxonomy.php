<?php

/**
 * Template para Taxonomías (Género, Autor)
 * 
 * Muestra los libros de una categoría específica.
 * Usa el Loop nativo de WordPress (que ya trae los libros filtrados).
 * 
 * @package BibliotecaWordPress
 */

get_header();

// Obtener el objeto actual (el género o autor que se está viendo)
$term = get_queried_object();
?>

<main class="biblioteca-main">
    <div class="biblioteca-container">

        <!-- CABECERA DE TAXONOMÍA -->
        <header class="biblioteca-header">
            <h1 class="biblioteca-titulo">
                <span class="subtitulo-tax">Libros de:</span>
                <?php echo esc_html($term->name); ?>
            </h1>

            <?php if (!empty($term->description)) : ?>
                <p class="biblioteca-descripcion"><?php echo esc_html($term->description); ?></p>
            <?php endif; ?>

            <div class="biblioteca-header__acciones">
                <a href="<?php echo esc_url(get_post_type_archive_link('libro')); ?>" class="btn btn--secondary">
                    ← Volver a toda la Biblioteca
                </a>
            </div>
        </header>

        <!-- GRID DE LIBROS (LOOP NATIVO) -->
        <?php if (have_posts()) : ?>

            <div class="biblioteca-grid">

                <?php while (have_posts()) : the_post(); ?>

                    <?php
                    // Recopilamos datos para la card
                    $precio        = get_field('precio');
                    $en_oferta     = get_field('en_oferta');
                    $precio_oferta = get_field('precio_oferta');
                    $paginas       = get_field('paginas');
                    $stock         = get_field('stock_disponible');
                    $generos_libro = get_the_terms(get_the_ID(), 'genero');
                    $tiene_imagen  = has_post_thumbnail();
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
                                <span class="libro-badge libro-badge--stock">¡ÚLTIMO EN STOCK!</span>
                            <?php endif; ?>
                        </div>

                        <!-- Contenido -->
                        <div class="libro-card__contenido">

                            <h2 class="libro-card__titulo">
                                <a href="<?php echo esc_url(get_permalink()); ?>">
                                    <?php echo esc_html(get_the_title()); ?>
                                </a>
                            </h2>

                            <p class="libro-card__excerpt">
                                <?php echo esc_html(mb_strimwidth(get_the_excerpt(), 0, 150, '...')); ?>
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

                            <!-- Meta -->
                            <div class="libro-card__meta">
                                <?php if ($generos_libro && !is_wp_error($generos_libro)) : ?>
                                    <div class="libro-card__generos">
                                        <?php foreach ($generos_libro as $genero) : ?>
                                            <!-- Link al género (recursivo, lleva a esta misma plantilla) -->
                                            <a href="<?php echo esc_url(get_term_link($genero)); ?>" class="genero-badge">
                                                <?php echo esc_html($genero->name); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>

                    </article>

                <?php endwhile; ?>

            </div>

            <!-- PAGINACIÓN -->
            <nav class="biblioteca-paginacion">
                <?php
                echo paginate_links([
                    'prev_text' => '← Anterior',
                    'next_text' => 'Siguiente →',
                ]);
                ?>
            </nav>

        <?php else : ?>

            <div class="biblioteca-sin-resultados">
                <div class="sin-resultados-icono">📭</div>
                <h2>No hay libros en esta categoría</h2>
                <p>
                    <a href="<?php echo esc_url(get_post_type_archive_link('libro')); ?>">Volver a ver todos</a>
                </p>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>