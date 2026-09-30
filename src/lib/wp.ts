import local from '../data/content.json';
import slides from '../data/slides.json';
import type { Content } from './types';

/**
 * Origen de los datos:
 *  - Proyectos y servicios/parcelas → WordPress headless:
 *      {WP_URL}/wp-json/azteca/v1/content  (plugin "Azteca Headless")
 *    Si el WP no responde o aún no tiene contenido, se usa src/data/content.json
 *    (copia migrada del sitio anterior) para que el sitio nunca quede vacío.
 *  - Slider, inicio, nosotros y contacto → viven en Astro (src/data/slides.json y src/pages).
 */
const WP_URL = (import.meta.env.WP_URL ?? process.env.WP_URL ?? '').replace(/\/$/, '');

let cache: Promise<Content> | null = null;

async function fromWP(): Promise<Partial<Content> | null> {
  if (!WP_URL) return null;
  try {
    const res = await fetch(`${WP_URL}/wp-json/azteca/v1/content`, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return (await res.json()) as Partial<Content>;
  } catch (e) {
    console.warn(`[wp] No se pudo leer WordPress (${(e as Error).message}); se usa contenido local.`);
    return null;
  }
}

export function getContent(): Promise<Content> {
  cache ??= (async () => {
    const base = local as unknown as Omit<Content, 'slides' | 'source'>;
    const wp = await fromWP();
    const pick = <T,>(a: T[] | undefined, b: T[]) => (a && a.length ? a : b);
    return {
      slides: slides as unknown as Content['slides'],
      projects: pick(wp?.projects, base.projects),
      services: pick(wp?.services, base.services),
      about: base.about,
      source: wp?.projects?.length || wp?.services?.length ? ('wordpress' as const) : ('local' as const),
    };
  })();
  return cache;
}

export const money = (n?: number | null) =>
  n ? new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 }).format(n) : '';
