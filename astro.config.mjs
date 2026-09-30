import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import { readdir, readFile, writeFile } from 'node:fs/promises';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';

// BASE_PATH=/prueba publica el sitio en una subcarpeta (ej. https://constructorazteca.cl/prueba)
const base = process.env.BASE_PATH || '/';
const prefix = base.replace(/\/$/, '');

// Antepone la subcarpeta a los enlaces internos ("/contacto" → "/prueba/contacto") del HTML generado.
function prefixBase() {
  const fix = (u) => (u.startsWith('/') && !u.startsWith('//') && u !== prefix && !u.startsWith(prefix + '/') ? prefix + u : u);
  const walk = async (d) => (await Promise.all((await readdir(d, { withFileTypes: true })).map((e) => (e.isDirectory() ? walk(join(d, e.name)) : [join(d, e.name)])))).flat();
  return {
    name: 'prefix-base',
    hooks: {
      'astro:build:done': async ({ dir }) => {
        if (!prefix) return;
        for (const f of (await walk(fileURLToPath(dir))).filter((f) => f.endsWith('.html'))) {
          const html = (await readFile(f, 'utf8'))
            .replace(/(\s(?:href|src|action|poster)=")([^"]*)"/g, (_, a, u) => `${a}${fix(u)}"`)
            .replace(/(\ssrcset=")([^"]*)"/g, (_, a, s) => `${a}${s.split(',').map((p) => p.trim().replace(/^\S+/, fix)).join(', ')}"`);
          await writeFile(f, html);
        }
      },
    },
  };
}

export default defineConfig({
  site: 'https://constructorazteca.cl',
  base,
  output: 'static',
  trailingSlash: 'ignore',
  integrations: [sitemap(), prefixBase()],
  build: { inlineStylesheets: 'auto' },
  image: { domains: ['constructorazteca.cl'] },
});
