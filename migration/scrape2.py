import re, json, urllib.request
from bs4 import BeautifulSoup
def get(u): return urllib.request.urlopen(urllib.request.Request(u,headers={'User-Agent':'Mozilla/5.0'}),timeout=30).read().decode('utf8','replace')
full=lambda s: re.sub(r'-\d+x\d+(?=\.\w+$)','',s.split('?')[0])
def txt(e):
    for br in e.find_all('br'): br.replace_with('\n')
    return '\n'.join(l.strip() for l in e.get_text().split('\n') if l.strip())
def parse(u):
    s=BeautifulSoup(get(u),'html.parser')
    for x in s(['script','style','noscript','svg']): x.decompose()
    el=s.select_one('.elementor[data-elementor-type="wp-post"]') or s.select_one('.elementor[data-elementor-type="wp-page"]') or s
    og=s.select_one('meta[property="og:image"]')
    gal=[]
    for g in el.select('.gallery a[href]'):
        h=g['href'].split('?')[0]
        if '/uploads/' in h and h not in gal: gal.append(h)
    hero=None
    for i in el.find_all('img'):
        if i.find_parent(class_='gallery'): continue
        src=i.get('src') or ''
        if '/uploads/' in src: hero=full(src); break
    fields=[]
    for m in el.select('.master-heading'):
        h=m.find(['h1','h2','h3']); sub=m.select_one('.sub-heading')
        fields.append({'label':txt(h) if h else '','text':txt(sub) if sub else ''})
    # services (no master-heading class after strip?) fallback
    heads=[txt(h) for h in el.find_all(['h1','h2','h3'])]
    return {'url':u,'slug':u.rstrip('/').split('/')[-1],'title':re.sub(r'\s*-\s*constructorazteca.*$','',s.title.string or ''),'og':full(og['content']) if og else None,'hero':hero,'gallery':gal,'fields':fields,'heads':heads,'body':txt(el)[:4000]}
old=json.load(open('migration/scraped.json'))
out={'project':[],'service':[]}
for t in out:
    for x in old[t]:
        out[t].append(parse(x['url'])); print(t,x['slug'])
out['pages']={p:parse('https://constructorazteca.cl'+p) for p in ['/','/nosotros/','/servicios/','/parcelas/','/contactanos/','/proyectos/']}
json.dump(out,open('migration/content.json','w'),ensure_ascii=False,indent=1)
