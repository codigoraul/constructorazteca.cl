export interface Img { src: string; small?: string; width: number; height: number; alt?: string; position?: string; srcset?: string; layout?: 'right' }
export interface Slide { title: string; subtitle: string; cta?: string; link?: string; image: Img }
export interface Project {
  slug: string; title: string; surface?: string | null; description: string;
  location?: string; hero: Img | null; card: Img | null; gallery: Img[];
}
export interface Service {
  slug: string; title: string; kind: 'servicio' | 'parcela'; icon: string; excerpt: string;
  description: string; price?: number | null; hero: Img | null; gallery: Img[];
}
export interface Content {
  slides: Slide[]; projects: Project[]; services: Service[];
  about: { hero: Img | null };
  source: 'wordpress' | 'local';
}
