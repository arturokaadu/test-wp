<?php
// WordPress necesita un index.php mínimo
// Este archivo existe para que el tema sea válido
get_header();
?>

<main>
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            the_content();
        }
    }
    ?>
</main>

<?php get_footer(); ?>