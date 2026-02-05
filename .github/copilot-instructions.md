# Copilot Instructions - Biblioteca WordPress

A WordPress theme project implementing a custom book library with CPT, REST API, and SCSS styling.

## Architecture Overview

**Purpose:** Custom WordPress theme with Books (libros) management system for a technical challenge.

**Core Components:**
- **Custom Post Type (CPT)** `inc/post-types.php` - Libro post type with Taxonomies (genero, autor_libro)
- **REST API** `inc/rest-api.php` - 3 custom endpoints: `/libros`, `/libros/{id}`, `/generos`
- **Templates** - Follows WordPress template hierarchy: `single-libro.php`, `archive-libro.php`, `taxonomy.php`
- **Styling** - SCSS with BEM methodology in `assets/scss/`, compiled to `assets/css/libros.css`
- **ACF Fields** - Book metadata stored via Advanced Custom Fields (exported JSON in `acf-export/libro-fields.json`)

**Data Flow:**
Book post → ACF fields (precio, en_oferta, stock, etc) → Templates via `get_field()` OR REST API endpoints → JSON responses

## Key Patterns & Conventions

### 1. CPT Registration Pattern (`inc/post-types.php`)
- Uses `add_action('init', ...)` hook to register CPT and taxonomies
- CPT "libro" configured with REST API enabled (`'show_in_rest' => true`)
- Two taxonomies: `genero` (hierarchical, like categories) and `autor_libro` (non-hierarchical, like tags)
- Slugs matter: uses `/libros/` and `/generos/` in URL rewrites

### 2. REST API Custom Endpoints (`inc/rest-api.php`)
- Namespace: `biblioteca/v1` (prevents conflicts)
- Always sanitize inputs: use `sanitize_text_field()`, `floatval()`, `absint()` with closures when needed
- Always return structured JSON: `{ "success": bool, "data": [], "pagination": {} }`
- Use `register_rest_route()` with callbacks, not decorators
- Validate numeric parameters with `validate_callback` function, not direct `is_numeric()`

### 3. Template Queries Pattern
- **Native loop** for main query (already filtered by WP based on URL)
- **WP_Query** for supplementary content (related books, filters): always `wp_reset_postdata()` after
- Syntax: prefer `<?php while(have_posts()): the_post(); ?>` + `<?php endwhile; ?>` for readability (alternative syntax)
- Use `setup_postdata()` only with `get_posts()` + manual iteration

### 4. ACF Field Access
- All book metadata via `get_field('field_name')` - e.g., `get_field('precio')`, `get_field('en_oferta')`
- Direct database values, no transformation needed
- Conditional rendering: check field existence before display

### 5. SCSS Structure & BEM
- **Organization:** `abstracts/` (_variables, _mixins) → `base/` (reset, typography) → `components/` (_cards, _badges, _buttons) → `layout/` → `pages/`
- **BEM Naming:** `.libro-card__titulo`, `.libro-badge--genero` (block__element--modifier)
- **Build:** `npm run build:css` (one-time) or `npm run watch:css` (development)
- CSS loaded conditionally in `functions.php`: only on libro archive/single/taxonomy pages

### 6. Security & Data Handling
- **Input:** Use `sanitize_text_field()`, `floatval()`, `absint()` based on expected type
- **Output in HTML:** `esc_html()`, in attributes: `esc_attr()`, in URLs: `esc_url()`
- **REST API:** always validate + sanitize parameters in registration

### 7. Template Hierarchy
When creating/modifying templates, WordPress searches in this order for book posts:
1. `single-libro-[post_slug].php` (specific post)
2. `single-libro.php` (all books - exists)
3. `single.php` (any post type)
4. `singular.php` (posts + pages)
5. `index.php` (fallback - must exist)

For archives: `archive-libro.php` (exists), falls back to `archive.php` → `index.php`

## Important Implementation Details

### Related Books (Fallback Pattern)
In `single-libro.php`, the "También te podría gustar" section uses two queries:
1. First: filter by same `genero` taxonomy
2. If empty: fallback to random books without genre filter (avoids empty sections)

This prevents showing "no related items" when a book is the only one in its genre.

### Conditional CSS Loading
CSS only loads where needed (performance optimization):
```php
if (is_post_type_archive('libro') || is_singular('libro') || is_tax(['genero', 'autor_libro'])) {
    wp_enqueue_style('libros-css', ...);
}
```

### Price Formatting Helper
Helper function `biblioteca_formatear_precio()` exists for consistent formatting: `$45.000` style (peso format).

### Rewrite Rules & Performance
- Do NOT call `flush_rewrite_rules()` in `init` hook (runs every page load, kills performance)
- CPT registered with `'rewrite' => ['slug' => 'libros']` - handles URL structure

## Development Workflow

### Local Setup
```bash
docker-compose up -d              # Start WordPress (see docker-compose.yml)
npm install                       # Install Sass dependencies
npm run watch:css                 # Auto-compile SCSS during development
```

### ACF Field Management
1. Import fields: WordPress admin → ACF → Tools → Import `acf-export/libro-fields.json`
2. Export after changes: ACF → Tools → Export (update JSON file)

### Common Tasks
- **Add book field:** Use ACF UI, then export JSON to `acf-export/`
- **Modify query filters:** Edit `WP_Query` in archive/taxonomy templates or REST endpoints
- **Style components:** Edit relevant SCSS in `assets/scss/components/`, rebuild CSS
- **REST endpoint:** Add `register_rest_route()` call in `inc/rest-api.php`

## API Reference (Quick)

### Endpoints
- `GET /wp-json/biblioteca/v1/libros` - List with filters: `?genero=ficcion&precio_max=30000&search=titulo&per_page=10&page=1`
- `GET /wp-json/biblioteca/v1/libros/{id}` - Single book with full ACF fields and reviews
- `GET /wp-json/biblioteca/v1/generos` - Genre list

### Response Structure
```json
{
  "success": true,
  "data": [...],
  "pagination": { "total": N, "pages": N, "current_page": N, "per_page": N }
}
```

## Critical Gotchas

1. **Missing `wp_head()` / `wp_footer()`** - Causes CSS/JS not to load. Must be in `header.php` / `footer.php`
2. **Forgetting `wp_reset_postdata()`** - Secondary loops leave `$post` pointing to wrong object
3. **REST sanitization closures** - `sanitize_callback` can't use `floatval()` directly (wrong arg count), wrap in closure
4. **CSS specificity with BEM** - Don't nest selectors too deeply or BEM loses advantage
5. **Template `$current_id` scope** - Must save post ID before exiting main loop (`endwhile`)
