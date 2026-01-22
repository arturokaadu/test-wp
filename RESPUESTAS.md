# BIBLIOTECA WORDPRESS - RESPUESTAS

**Arturo Kaadú - Enero 2026**

Este README contiene respuestas a las preguntas teóricas de WordPress.

---

1 ## THE LOOP

1.1 ### ¿Diferencia entre loop nativo y WP_Query?

> El loop nativo usa la Query Principal que WordPress ya ejecutó según la URL. Si estoy en `/libros/`, WP ya cargó todos los libros.
>
> WP_Query crea una consulta NUEVA con parámetros custom. Pueden coexistir: el nativo muestra contenido principal, WP_Query muestra contenido extra (sidebar, secciones).

1.2 ### ¿Por qué es importante wp_reset_postdata()?

> `wp_reset_postdata()` restaura la variable global `$post` a la query principal despues de un loop secundario. Sin reset, `the_title()` seguiría apuntando al último post del loop secundario. Es como resetear el puntero.

1.3 ### ¿Qué hace setup_postdata($post)?

> Prepara las funciones de template para trabajar con el post que se le pasa. Se usa con `get_posts()` y `foreach` por ejemplo. Sobrescribe la variable global $post con el post actual para que funcionen las template tags. Por eso es obligatorio usar wp_reset_postdata() al final. Se usa siempre que se tenga un objeto de post guardado en una variable y se necesite cargar sus datos en el contexto global para que las funciones estándar de wordpress puedan leerlo.

---

2 ## HOOKS

2.1 ### ¿Diferencia entre add_action y add_filter?

> **Action** ejecuta código en un momento sin devolver nada (ej: registrar CPT al inicializar).
>
> **Filter** recibe un dato, lo modifica, y siempre debe devolverlo. Si no devuelve el dato se convierte en null y rompe la cadena de ejecución visual o lógica.

**Ejemplos en mi Challenge:**

**ACTION (Registrar el CPT):**
En `inc/post-types.php` se utilizó una acción. No devuelve nada, solo le avisa a WordPress que registre el tipo de post "Libro" cuando inicie (`init`).

```php
function registrar_libros() {
    register_post_type('libro', $args);
}
add_action('init', 'registrar_libros');
```

**FILTER (Modificar título - Ejemplo teórico):**
Si se quisiera que todos los títulos digan "LIBRO:" antes, se deberia usar un filtro. Se recibe el título, se pego el texto, y se devuelve con return.

```php
function agregar_prefijo_titulo($title) {
    return 'LIBRO: ' . $title; // siempre tiene que haber un return
}

2.2 ### ¿Problema con flush_rewrite_rules() en init?

> `flush_rewrite_rules()` reescribe todas las URLs en la DB. Es  pesado por lo que si lo ejecutamos en cada carga de página se vuelve muy lento.
>
La solución es ejecutarlo en un hook que solo corra una vez, por ejemplo al activar el tema o plugin.
---

3.1 # TEMPLATE HIERARCHY

### ¿Orden de búsqueda para CPT "libro"?

> WordPress va a buscar de más específico a más genérico:

1. `single-libro-el-quijote.php` ← Template específico libro
2. `single-libro.php` ← Template para TODOS los libros
3. `single.php` ← Template genérico para cualquier single
4. `singular.php` ← Template para singles Y pages
5. `index.php` ← Fallback (siempre debe existir)

`index.php` es obligatorio - es el fallback final.

### 3.2 ¿Diferencia entre get_template_part() e include?

> `get_template_part()` es para archivos de plantilla, son instrucciones de wordpress. Soporta child themes, es seguro, para partes del tema.

> `include`/`require` es para archivos de configuración, son instrucciones de php no de wordpress. No soporta child themes. Se usan para archivos de configuración, plugins, etc.

---

4.1 ## SEGURIDAD
<?php
// Input (sanitización)

$titulo = sanitize_text_field($_POST['titulo']); // quita etiquetas html, espacios en blanco, etc
$email = sanitize_email($_POST['email']); // solo deja formato email
$precio = absint($_POST['precio']); // solo deja números enteros positivos. si se quiere hacer otra verifacion se puede usar un if para verificar que sea un numero o un float.

// Output (escape)
echo '<h1>' . esc_html($titulo) . '</h1>'; // para etiquetas html
echo '<a href="' . esc_url($url) . '">Link</a>'; // para urls
echo '<div class="' . esc_attr($clase) . '">Contenido</div>'; // para atributos html
?>
```
