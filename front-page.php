<?php

/**
 * Front Page Template
 * 
 * Si el usuario entra al home ('/'), lo redirigimos al archivo de libros ('/libros/').
 * Esto asegura que la "Home" sea la Biblioteca.
 * 
 * @package BibliotecaWordPress
 */

// Redirección al archivo de libros (Home = Biblioteca)
wp_safe_redirect(get_post_type_archive_link('libro'));
exit;
