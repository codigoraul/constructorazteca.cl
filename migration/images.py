import json, re, os, io, urllib.request, hashlib
from concurrent.futures import ThreadPoolExecutor
from PIL import Image, ImageOps
d=json.load(open('migration/content.json'))
urls=[]
def add(u):
    if u and '/uploads/' in u and u not in urls: urls.append(u)
for t in ('project','service'):
    for x in d[t]:
        add(x['og']); add(x['hero']); [add(g) for g in x['gallery']]
for p in d['pages'].values(): add(p['hero']); add(p['og'])
# popup / logos from home html
h=urllib.request.urlopen(urllib.request.Request('https://constructorazteca.cl/',headers={'User-Agent':'Mozilla/5.0'})).read().decode()
for m in re.findall(r'https://constructorazteca\.cl/wp-content/uploads/[^"\'\s)]+\.(?:png|jpe?g|webp|svg)',h):
    if 'egr' in m.lower(): add(re.sub(r'-\d+x\d+(?=\.\w+$)','',m))
print(len(urls),'urls')
os.makedirs('public/media',exist_ok=True)
def name(u):
    p=u.split('/uploads/')[1]; p=re.sub(r'\.\w+$','',p)
    return re.sub(r'[^a-zA-Z0-9]+','-',p).strip('-').lower()
meta={}
def work(u):
    n=name(u)
    try:
        b=urllib.request.urlopen(urllib.request.Request(u,headers={'User-Agent':'Mozilla/5.0'}),timeout=60).read()
        im=Image.open(io.BytesIO(b)); im=ImageOps.exif_transpose(im)
        if im.mode not in('RGB','RGBA'): im=im.convert('RGB')
        W,H=im.size
        for suffix,mw,q in (('',1920,80),('-640',640,76)):
            i2=im.copy()
            if i2.width>mw: i2=i2.resize((mw,round(i2.height*mw/i2.width)),Image.LANCZOS)
            i2.save(f'public/media/{n}{suffix}.webp','WEBP',quality=q,method=5)
            if suffix=='': fw,fh=i2.size
        meta[u]={'name':n,'w':fw,'h':fh}
    except Exception as e:
        meta[u]={'error':str(e)}
with ThreadPoolExecutor(8) as ex: list(ex.map(work,urls))
json.dump(meta,open('migration/images.json','w'),indent=1)
print('ok',sum('name' in v for v in meta.values()),'err',[ (k,v['error']) for k,v in meta.items() if 'error' in v][:10])
