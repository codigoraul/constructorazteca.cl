import re, json, sys, urllib.request, urllib.parse
from bs4 import BeautifulSoup
B='https://constructorazteca.cl'
def get(u):
    r=urllib.request.Request(u,headers={'User-Agent':'Mozilla/5.0'})
    return urllib.request.urlopen(r,timeout=30).read().decode('utf8','replace')
def sm(u):
    try: return re.findall(r'<loc>([^<]+)</loc>',get(u))
    except Exception as e: return []
idx=sm(B+'/sitemap_index.xml')
print('index',idx)
urls={'project':[],'service':[]}
for s in idx:
    for t in urls:
        if t+'-sitemap' in s: urls[t]+=[u for u in sm(s) if '/%s/'%t in u]
print({k:len(v) for k,v in urls.items()})
full=lambda s: re.sub(r'-\d+x\d+(?=\.\w+$)','',s.split('?')[0])
out={}
for t,lst in urls.items():
    out[t]=[]
    for u in lst:
        soup=BeautifulSoup(get(u),'html.parser')
        for x in soup(['script','style','noscript']): x.decompose()
        el=soup.select_one('.elementor[data-elementor-type="wp-post"]') or soup.select_one('article')
        imgs=[]
        for i in (el or soup).find_all('img'):
            s=i.get('data-src') or i.get('src') or ''
            if '/uploads/' in s:
                f=full(s)
                if f not in imgs: imgs.append(f)
        texts=[]
        for e in (el or soup).select('h1,h2,h3,h4,p,li,.elementor-heading-title,.elementor-widget-text-editor'):
            tx=re.sub(r'\s+',' ',e.get_text(' ',strip=True))
            if tx and tx not in texts: texts.append(tx)
        og=soup.select_one('meta[property="og:image"]')
        out[t].append({'url':u,'slug':u.rstrip('/').split('/')[-1],'title':(soup.title.string or '').replace(' - constructorazteca','').strip(),'og':full(og['content']) if og else None,'imgs':imgs,'texts':texts,'date':(soup.select_one('meta[property="article:published_time"]') or {}).get('content') if soup.select_one('meta[property="article:published_time"]') else None})
        print(t,u,len(imgs),len(texts))
json.dump(out,open('migration/scraped.json','w'),ensure_ascii=False,indent=1)
