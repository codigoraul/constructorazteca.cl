// Datos fijos del sitio. Los que puede editar la clienta viven en WordPress
// (Ajustes → Azteca) y sobrescriben estos valores cuando el WP responde.
export const SITE = {
  name: 'Constructora Azteca',
  tagline: 'Constructora e Inmobiliaria',
  description:
    'Constructora e inmobiliaria en Ancud, Chiloé. Construcción de viviendas sociales (DS-01) y privadas, diseño y arquitectura, regularizaciones, proyectos sanitarios y eléctricos, topografía y venta de parcelas.',
  url: import.meta.env.PUBLIC_SITE_URL ?? 'https://constructorazteca.cl',
  phone: '+56 9 9746 4297',
  phoneRaw: '+56997464297',
  email: 'contacto@constructorazteca.cl',
  address: 'Ancud, Chiloé',
  facebook: 'https://www.facebook.com/people/Constructora-Azteca-Ltda/100054509767400/',
  instagram: 'https://www.instagram.com/constructoraaztecaltda/',
  ep: {
    title: 'Entidad Patrocinante (EP)',
    subtitle: 'DS-01 y DS-10',
    text:
      'Dando cumplimiento a la normativa vigente dictada por el Minvu, contamos con Entidad Patrocinante para el desarrollo de las viviendas sociales producto de subsidios DS-01, otorgados por el Ministerio de Vivienda.',
    email: 'contacto@egrchile.cl',
    phone: '+56 9 5145 7865',
  },
};
export type Site = typeof SITE;
