# constructorazteca.cl — Astro + WordPress headless

Rediseño del sitio de Constructora Azteca. Frontend en **Astro** (estático); el contenido
(proyectos, servicios/parcelas y slider principal) se administra en **WordPress** (`/admin`).

## Cómo funciona
- **En WordPress (`/admin`)**: solo **Proyectos** y **Servicios/Parcelas** (con galería de fotos).
- **En Astro**: inicio, slider principal (`src/data/slides.json` + `public/slider/`), nosotros y contacto.
- `src/lib/wp.ts` lee `WP_URL/wp-json/azteca/v1/content` (plugin **Azteca Headless**). Si el WP no responde
  o está vacío, usa `src/data/content.json` (copia migrada del sitio anterior); el sitio nunca queda en blanco.
- Como el sitio es estático, tras editar en WordPress hay que reconstruirlo: `npm run build`, o automático con un
  *deploy hook* (menú Azteca → Reconstruir sitio).

## Comandos
```bash
npm install
cp .env.example .env      # WP_URL=https://constructorazteca.cl/admin
npm run dev               # http://localhost:4321
npm run build             # genera /dist
```

## WordPress
1. Plugins → Añadir → Subir → `wordpress/azteca-headless.zip` → Activar.
2. Azteca → Importar sitio anterior → «Importar contenido» (31 proyectos y 9 servicios con sus fotos).
3. La clienta edita desde los menús **Proyectos** y **Servicios**.

## Paleta (Figma)
Naranja `#F5A623` (solo acentos) · Carbón `#2D2D2D` · Naranja oscuro `#D4891A` · Fondos: blanco `#FFFFFF` y gris claro `#F5F5F5` (sin crema).
Tokens en `src/styles/global.css`. Logos vectoriales en `public/logo.svg` y `public/logo-light.svg`.

## Migración
`migration/` contiene los scripts usados para extraer el contenido del sitio anterior y optimizar imágenes (WebP).
