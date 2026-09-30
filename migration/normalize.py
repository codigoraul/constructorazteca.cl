import json, re
d=json.load(open('migration/content.json')); im=json.load(open('migration/images.json')); order=json.load(open('migration/project_order.json'))
def img(u,alt=''):
    if not u or u not in im or 'name' not in im[u]: return None
    m=im[u]; return {'src':f"/media/{m['name']}.webp",'small':f"/media/{m['name']}-640.webp",'width':m['w'],'height':m['h'],'alt':alt}
def clean(s): return re.sub(r'[ \t]+',' ',s).strip()
projects=[]
for p in d['project']:
    F=p['fields']; name=clean(F[0]['text']) if F and F[0]['text'] else re.sub(r'^proyecto[\s\-:]*','',p['title'],flags=re.I).strip()
    sup=None; desc=''; ubi=''
    for f in F[1:]:
        l=f['label']
        if l.lower().startswith('superficie'):
            rest=clean(l[len('Superficie'):]); t=clean(f['text'])
            if rest: sup=rest; desc=f['text']
            else: sup=t
        elif l.lower().startswith('ubicaci'): ubi=clean(f['text'])
    if sup:
        m=re.search(r'\d[\d.,]*',sup); sup=(m.group(0).replace(' ','')+' m²') if m else sup
    desc='\n'.join(clean(x) for x in desc.split('\n') if x.strip())
    gal=[img(g,f'{name} – imagen {i+1}') for i,g in enumerate(p['gallery'])]
    hero=img(p['hero'],name) or (gal[0] if gal else None)
    card=img(p['og'],name) or hero
    gal=[g for g in gal if g]
    projects.append({'slug':p['slug'],'title':name,'surface':sup,'description':desc,'location':ubi,'hero':hero,'card':card,'gallery':gal,'order':order.index(p['slug']) if p['slug'] in order else 99})
projects.sort(key=lambda x:x['order'])
icons={'construccion-de-viviendas':'house','diseno-y-arquitectura':'compass','inmobiliaria':'land','proyectos-sanitarios':'water','proyectos-de-electricidad':'bolt','regularizacion-de-viviendas':'file','topografia':'survey'}
srv_order=['construccion-de-viviendas','diseno-y-arquitectura','inmobiliaria','regularizacion-de-viviendas','proyectos-sanitarios','proyectos-de-electricidad','topografia','parcelas-mechaico','parcela-piruquina']
services=[]
for s in d['service']:
    body=s['body'].split('\n'); title=s['title']
    lines=[l for l in body if l.strip() and l.strip().upper()!='CONTÁCTANOS' and l.strip().lower()!=title.lower() and l.strip().upper()!=title.upper()]
    price=None
    for l in lines:
        if re.fullmatch(r'\s*\d{1,3}(\.\d{3})+\.?-?\s*',l): price=int(re.sub(r'\D','',l)); 
    lines=[l for l in lines if not re.fullmatch(r'\s*\d{1,3}(\.\d{3})+\.?-?\s*',l) and l.strip().lower()!='precio desde' and l.strip()!='Mechaico' and l.strip()!='Piruquina']
    kind='parcela' if 'parcela' in s['slug'] else 'servicio'
    gal=[img(g,f'{title} – imagen {i+1}') for i,g in enumerate(s['gallery'])]; gal=[g for g in gal if g]
    hero=img(s['hero'],title) or img(s['og'],title)
    services.append({'slug':s['slug'],'title':title,'kind':kind,'icon':icons.get(s['slug'],'land'),'excerpt':clean(lines[0])[:170] if lines else '','description':'\n'.join(clean(l) for l in lines),'price':price,'hero':hero,'gallery':gal,'order':srv_order.index(s['slug']) if s['slug'] in srv_order else 99})
services.sort(key=lambda x:x['order'])
E=lambda n: next((img(k) for k in im if im[k].get('name')==n),None)
def byname(sub):
    for k,v in im.items():
        if 'name' in v and sub in v['name']: return img(k)
slides=[
 {'title':'Azteca Construcción','subtitle':'Empresa dedicada al desarrollo de proyectos de vivienda social y privada, en sector urbano y rural.','cta':'Ver servicios','link':'/servicios','image':byname('arquitect-copia')},
 {'title':'Proyectos de vivienda social y privada','subtitle':'En sector urbano y rural; construyendo y respaldando el sueño de muchas familias.','cta':'Ver proyectos','link':'/proyectos','image':projects[0]['hero']},
 {'title':'Inmobiliaria','subtitle':'Parcelas en el archipiélago de Chiloé, con bosques nativos, praderas y vertientes naturales.','cta':'Ver parcelas','link':'/parcelas','image':byname('parcela5')},
]
out={'slides':[s for s in slides if s['image']],'projects':projects,'services':services,'about':{'hero':byname('construc-casa') or projects[1]['hero'],'egr':byname('egr')}}
json.dump(out,open('src/data/content.json','w'),ensure_ascii=False,indent=1)
print(len(projects),len(services),[ (s['slug'],s['price'],bool(s['hero']),len(s['gallery'])) for s in services])
print([p['title'] for p in projects][:8], sum(1 for p in projects if not p['hero']))
print([ (s['title'],s['image']['src']) for s in out['slides']], out['about'])
