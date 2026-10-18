  (() => {
    const rh = React.createElement;
    const {useEffect, useMemo, useState, useRef} = React;
    // Let Chromium expose its native PWA install UI. We intentionally do not
    // call preventDefault() on beforeinstallprompt on normal website pages,
    // which avoids the misleading "Banner not shown" console diagnostic.
    window.__lpcmsInstallPrompt=null;
    window.addEventListener('appinstalled',()=>{ window.__lpcmsInstallPrompt=null; window.dispatchEvent(new CustomEvent('lpcms:install-ready')); });
    const rootEl = document.getElementById('root');
    const rootDataset = document.documentElement?.dataset || {};
    const configLanguages = String(rootDataset.lpcmsLangs||'en').split(',').map(x=>x.trim().toLowerCase()).filter(Boolean).map(code=>({code,label:code.toUpperCase(),name:code.toUpperCase()}));
    const CLIENT_CONFIG = {
      basePath:String(rootDataset.lpcmsBase||''),
      serverLang:String(rootDataset.lpcmsServerLang||document.documentElement?.lang||'en').toLowerCase(),
      defaultLang:String(rootDataset.lpcmsDefaultLang||'en').toLowerCase(),
      languages:configLanguages
    };
    const seed = {version:'',settings:{defaultLang:CLIENT_CONFIG.defaultLang,languages:CLIENT_CONFIG.languages,localization:{},theme:{grid:'container',customCss:''},analytics:{},cookies:{enabled:false},app:{enabled:false},hcaptcha:{enabled:false}},header:{logoText:'',logoImage:'',menu:[]},footer:{menu:[],social:[]},products:{basketEnabled:false,shopEnabled:false,cartEnabled:false},chatbot:{enabled:false}};
    const I18N = {languages:CLIENT_CONFIG.languages,strings:{},adminStrings:{},adminTerms:{}};
    async function cmsResponseJson(response){
      const body=await response.text();
      try{return JSON.parse(body)}catch(err){
        const preview=body.replace(/<[^>]*>/g,' ').replace(/\s+/g,' ').trim().slice(0,220);
        throw new Error(preview ? `Server returned non-JSON (HTTP ${response.status}): ${preview}` : `Server returned invalid JSON (HTTP ${response.status}).`);
      }
    }
    const CMS_BASE_PATH = String(CLIENT_CONFIG.basePath||'');
    const SERVER_LANG = String(CLIENT_CONFIG.serverLang||seed.settings?.defaultLang||'en').toLowerCase();
    const DEFAULT_LANG = String(seed.settings?.defaultLang||'en').toLowerCase();
    const LANGUAGE_CODES = (seed.settings?.languages||I18N.languages||[]).map(x=>String(x.code||'').toLowerCase()).filter(Boolean);
    const ADMIN_UI_STRINGS=new Set();
    function adminTranslatedText(code,text){
      if(typeof text!=='string'||!text)return text;
      const lead=(text.match(/^\s*/)||[''])[0], trail=(text.match(/\s*$/)||[''])[0];
      const core=text.slice(lead.length,text.length-trail.length||text.length);
      if(!core)return text;
      const exact=I18N.adminStrings?.[code]?.[core];
      if(typeof exact==='string')return lead+exact+trail;
      // Keep technical samples, routes, identifiers and provider URLs byte-for-byte.
      // Human-facing explanations that contain technical text are covered by exact translations above.
      const technicalLiteral=/^(?:https?:\/\/|\?api=|settings\.|products\.|blog\.|header\.|services\.|pricing\.|hero\.|contact\.|footer\.|demoIntake\.|redirects\.)/i.test(core)
        || core.includes('?api=') || core.includes('{ site {') || core.includes('=>');
      if(technicalLiteral)return text;
      const dynamicAdmin=/^(?:User|Product|Service|Item|Blog post|Request|Reminder|Step|Option|Page|Feature|Gallery|Testimonial)\s+\d+\b/i.test(core);
      if(!ADMIN_UI_STRINGS.has(core)&&!ADMIN_UI_STRINGS.has(text)&&!dynamicAdmin)return text;
      const terms=I18N.adminTerms?.[code]||{};
      const caseWord=(dst,src)=>{ dst=String(dst); if(src===src.toUpperCase()&&/[A-Z]/.test(src))return dst.toUpperCase(); if(src[0]&&src[0]===src[0].toUpperCase())return dst.charAt(0).toUpperCase()+dst.slice(1); return dst; };
      // Replace against the original English text in one pass. Sequential replacement could
      // accidentally re-translate fragments inside an already translated Unicode word.
      const entries=Object.entries(terms).sort((a,b)=>b[0].length-a[0].length);
      if(!entries.length)return text;
      const byLower=Object.fromEntries(entries.map(([src,dst])=>[String(src).toLowerCase(),dst]));
      const alt=entries.map(([src])=>String(src).replace(/[.*+?^${}()|[\]\\]/g,'\\$&')).join('|');
      const rx=new RegExp('(^|[^A-Za-z0-9_])('+alt+')(?=$|[^A-Za-z0-9_])','gi');
      const out=core.replace(rx,(m,prefix,matched)=>prefix+caseWord(byLower[String(matched).toLowerCase()]??matched,matched));
      return lead+out+trail;
    }
    function translatedText(lang,text){ if(typeof text!=='string'||!text)return text; const code=String(lang||DEFAULT_LANG).toLowerCase(); if(code===DEFAULT_LANG||code==='en')return text; const direct=I18N.strings?.[code]?.[text]; if(typeof direct==='string')return direct; const trimmed=text.trim(); const adminExact=I18N.adminStrings?.[code]?.[trimmed]; if(typeof adminExact==='string'){ const lead=(text.match(/^\s*/)||[''])[0],trail=(text.match(/\s*$/)||[''])[0]; return lead+adminExact+trail; } return adminTranslatedText(code,text); }
    function localizeHtml(lang,html){ return translatedText(lang,html); }
    function h(type,props,...children){
      const lang=window.__lpcmsLang||SERVER_LANG||DEFAULT_LANG;
      let p=props;
      if(p){ p={...p}; ['placeholder','title','aria-label','alt'].forEach(k=>{if(typeof p[k]==='string')p[k]=translatedText(lang,p[k]);}); if(p.dangerouslySetInnerHTML?.__html){p.dangerouslySetInnerHTML={...p.dangerouslySetInnerHTML,__html:localizeHtml(lang,p.dangerouslySetInnerHTML.__html)};} }
      const preserveLiteral=type==='pre'||type==='code';
      const mapChild=v=>typeof v==='string'?(preserveLiteral?v:translatedText(lang,v)):(Array.isArray(v)?v.map(mapChild):v);
      return rh(type,p,...children.map(mapChild));
    }
    function pathPartsRelative(){ let path=location.pathname; if(CMS_BASE_PATH&&path.startsWith(CMS_BASE_PATH))path=path.slice(CMS_BASE_PATH.length); return path.split('/').filter(Boolean); }
    function explicitLangFromPath(){ const first=String(pathPartsRelative()[0]||'').toLowerCase(); return LANGUAGE_CODES.includes(first)?first:''; }
    function browserLanguage(){ const map={nb:'no',nn:'no',no:'no'}; for(const raw of (navigator.languages||[navigator.language||''])){const code=String(raw||'').toLowerCase().split('-')[0];const c=map[code]||code;if(LANGUAGE_CODES.includes(c))return c;} return DEFAULT_LANG; }
    function languageUrl(code){
      code=String(code||DEFAULT_LANG).toLowerCase();
      if(!LANGUAGE_CODES.includes(code))code=DEFAULT_LANG;
      let parts=pathPartsRelative();
      if(parts.length&&LANGUAGE_CODES.includes(String(parts[0]).toLowerCase()))parts.shift();
      const segments=code===DEFAULT_LANG?parts:[code,...parts];
      let pathname=((CMS_BASE_PATH||'')+'/'+segments.join('/')).replace(/\/+/g,'/')||'/';
      if(!segments.length) pathname=(CMS_BASE_PATH||'')+'/' || '/';
      else if(location.pathname.endsWith('/')&&!pathname.endsWith('/')) pathname+='/';
      return pathname+location.search+location.hash;
    }
    function internalHref(lang,href){ if(typeof href!=='string'||!href||href.startsWith('#')||/^(?:https?:|mailto:|tel:|javascript:)/i.test(href))return href; if(!href.startsWith('/'))return href; const code=String(lang||DEFAULT_LANG).toLowerCase(); const base=CMS_BASE_PATH||''; let rel=href; if(base&&rel.startsWith(base+'/'))rel=rel.slice(base.length); const parts=rel.split('/').filter(Boolean); if(parts.length&&LANGUAGE_CODES.includes(String(parts[0]).toLowerCase()))parts.shift(); const pref=code===DEFAULT_LANG?[]:[code]; let out=((base||'')+'/'+[...pref,...parts].join('/')).replace(/\/+/g,'/')||'/'; if(href.endsWith('/')&&out!=='/'&&!out.endsWith('/'))out+='/'; return out; }

    const LPCMS_DEMO_PRODUCTS = [];
    const initialBoot = { ok:true, admin:false, email:'', csrf:'', route:null, server:null, lang:SERVER_LANG, data: seed };

    function cls(...xs){ return xs.filter(Boolean).join(' '); }
    function get(obj, path){ return String(path).split('.').reduce((o,k)=> o && o[k] !== undefined ? o[k] : undefined, obj); }
    function set(obj, path, val){ const keys=String(path).split('.'); const copy=Array.isArray(obj)?[...obj]:{...obj}; let cur=copy; keys.forEach((k,i)=>{ if(i===keys.length-1){cur[k]=val;return;} cur[k]=Array.isArray(cur[k])?[...cur[k]]:{...cur[k]}; cur=cur[k]; }); return copy; }
    const LOCALIZED_CONTENT_ROOTS=new Set(['header','hero','about','stats','features','services','wpThemes','process','gallery','apps','testimonials','cards','contentList','team','growth','industries','logos','products','booking','adminShowcase','pricing','demoIntake','blog','faq','contact','cta','footer','chatbot','pages']);
    const LOCALIZED_CONTENT_LEAVES=new Set(['title','subtitle','text','body','label','role','badge','buttonText','excerpt','question','answer','description','caption','heading','subheading','placeholder','confirmationText','welcome','welcomeMessage','fallback','message','instructions','address','hours','cartTitle','buyButtonText','quoteButtonText','cartButtonText','checkoutButtonText','discountLabel','suggestedQuestions','copy','period','formButtonText','thankYou','acceptText','declineText','eyebrow','alt','name','logoText','price','secondaryPrice','features','shortName','offlineMessage','domainTitle','domainText','tableTitle','tableSubtitle','ease','preheader','superAdminTitle','superAdminText','noteTitle','noteText','ukNote','footerText','moreLabel','allAppsLabel']);
    function isLocalizableContentPath(path){
      const parts=String(path||'').split('.').filter(Boolean); if(!parts.length||parts.includes('i18n'))return false;
      const leaf=parts[parts.length-1];
      const specialSettings=parts.length>=3&&parts[0]==='settings'&&((parts[1]==='seo'&&['title','description','keywords'].includes(leaf))||(parts[1]==='cookies'&&['message','acceptText','declineText'].includes(leaf))||(parts[1]==='app'&&['name','shortName','description','offlineMessage'].includes(leaf)));
      if(!specialSettings&&!LOCALIZED_CONTENT_ROOTS.has(parts[0]))return false;
      if(/^\d+$/.test(leaf))return parts.length>1;
      return LOCALIZED_CONTENT_LEAVES.has(leaf);
    }
    function localizedDataPath(path,lang=(window.__lpcmsLang||SERVER_LANG||DEFAULT_LANG)){
      const code=String(lang||DEFAULT_LANG).toLowerCase(); const raw=String(path||'');
      if(code===DEFAULT_LANG||!isLocalizableContentPath(raw))return raw;
      const parts=raw.split('.'); const leaf=parts.pop();
      if(/^\d+$/.test(leaf)&&parts.length){ const prop=parts.pop(); return [...parts,'i18n',code,prop,leaf].join('.'); }
      return [...parts,'i18n',code,leaf].join('.');
    }
    function localizedDataValue(data,path,lang=(window.__lpcmsLang||SERVER_LANG||DEFAULT_LANG)){
      const code=String(lang||DEFAULT_LANG).toLowerCase(); const raw=String(path||''); const base=get(data,raw);
      if(code===DEFAULT_LANG||!isLocalizableContentPath(raw))return base;
      const lp=localizedDataPath(raw,code); const local=get(data,lp);
      if(local!==undefined&&local!==null)return local;
      if(Array.isArray(base))return base.map(v=>typeof v==='string'?translatedText(code,v):v);
      return typeof base==='string'?translatedText(code,base):base;
    }
    function absolute(url){ if(!url) return location.href; try{return new URL(url, location.href).href}catch(e){return location.href} }
    function mediaUrl(value){
      if(typeof value!=='string')return value;
      let v=value.trim(); if(!v)return v;
      if(v.startsWith('./assets/'))v=v.slice(2);
      if(v.startsWith('/assets/'))return (((CMS_BASE_PATH||'')+v).replace(/\/+/g,'/'));
      if(v.startsWith('assets/'))return (((CMS_BASE_PATH||'')+'/'+v).replace(/\/+/g,'/'));
      return v;
    }
    function slug(s){ return String(s||'item').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''); }
    function pretty(group,item,lang=(window.__lpcmsLang||DEFAULT_LANG)){ const s=slug(item?.slug||item?.title||'item'); const root=CMS_BASE_PATH||''; const lp=lang===DEFAULT_LANG?'':'/'+lang; if(group==='blog') return root+lp+'/blog/'+s; if(group==='products') return root+lp+'/product/'+s; if(group==='pages') return root+lp+'/page/'+s; return root+lp+'/?slug='+encodeURIComponent(s); }
    function icon(name, fallback){
      const ref=String(name||fallback||'bi bi-circle');
      if(ref.includes(':')){
        const [prefix,...rest]=ref.split(':'); const n=rest.join(':');
        const url='https://api.iconify.design/'+encodeURIComponent(prefix)+'/'+encodeURIComponent(n)+'.svg';
        return h('span',{className:'cms-external-icon','aria-hidden':'true'},
          h('img',{className:'cms-live-icon-img',src:url,alt:'',loading:'lazy',decoding:'async',fetchPriority:'low',onError:e=>{e.currentTarget.style.display='none';const fb=e.currentTarget.nextElementSibling;if(fb)fb.style.display='inline-block'}}),
          h('i',{className:'bi bi-stars cms-external-icon-fallback'})
        );
      }
      return h('i',{className:ref});
    }

    async function prepareImageForUpload(file){
      const filename=String(file?.name||'');
      const mime=String(file?.type||'').toLowerCase();
      const imageLike=mime.startsWith('image/')||/\.(?:jpe?g|png|gif|webp|avif|heic|heif)$/i.test(filename);
      if(!file || !imageLike) throw new Error('Please choose a JPG, PNG, GIF, WebP, HEIC, or AVIF image.');
      if((window.__LPCMS_MEDIA_SETTINGS||{}).browserAvif===false) return file;
      if(mime==='image/avif'||/\.avif$/i.test(filename)) return file;
      let cleanup=()=>{};
      try{
        let source, width, height;
        if(window.createImageBitmap){
          source=await createImageBitmap(file,{imageOrientation:'from-image'});
          width=source.width; height=source.height;
          cleanup=()=>source.close?.();
        }else{
          const url=URL.createObjectURL(file);
          cleanup=()=>URL.revokeObjectURL(url);
          source=await new Promise((resolve,reject)=>{ const img=new Image(); img.onload=()=>resolve(img); img.onerror=()=>reject(new Error('Image could not be read.')); img.src=url; });
          width=source.naturalWidth; height=source.naturalHeight;
        }
        const mediaSettings=(window.__LPCMS_MEDIA_SETTINGS||{}); const maxW=Number(mediaSettings.boxWidth||mediaSettings.maxImageWidth||1920); const maxH=Number(mediaSettings.boxHeight||maxW); const ratio=Math.min(1,maxW/Math.max(1,width),maxH/Math.max(1,height));
        const canvas=document.createElement('canvas');
        canvas.width=Math.max(1,Math.round(width*ratio));
        canvas.height=Math.max(1,Math.round(height*ratio));
        const ctx=canvas.getContext('2d',{alpha:true});
        if(!ctx) throw new Error('Image processing is unavailable in this browser.');
        ctx.drawImage(source,0,0,canvas.width,canvas.height);
        const blob=await new Promise(resolve=>canvas.toBlob(resolve,'image/avif',Math.max(.1,Math.min(1,Number((window.__LPCMS_MEDIA_SETTINGS||{}).avifQuality||72)/100))));
        if(blob && blob.type==='image/avif'){
          const base=filename.replace(/\.[^.]+$/,'')||'image';
          return new File([blob],base+'.avif',{type:'image/avif',lastModified:Date.now()});
        }
      }catch(err){
        // The server has GD/Imagick/ffmpeg fallbacks when browser AVIF encoding is unavailable.
      }finally{
        cleanup();
      }
      return file;
    }

    async function uploadCmsImage(api,file,target='img'){
      const prepared=await prepareImageForUpload(file);
      const fd=new FormData();
      fd.append('file',prepared,prepared.name||file.name||'image');
      fd.append('target',target);
      const ms=(window.__LPCMS_MEDIA_SETTINGS||{}); fd.append('boxPreset',String(ms.boxPreset||'large')); fd.append('boxWidth',String(ms.boxWidth||ms.maxImageWidth||1920)); fd.append('boxHeight',String(ms.boxHeight||ms.boxWidth||ms.maxImageWidth||1920)); fd.append('quality',String(ms.avifQuality||72));
      const response=await fetch('?api=upload',{method:'POST',headers:{'X-CMS-CSRF':api.csrf},body:fd});
      let result={};
      try{ result=await cmsResponseJson(response); }catch(err){ throw new Error('The upload server returned an invalid response.'); }
      if(!response.ok || !result.ok) throw new Error(result.error||'Image upload failed.');
      return result;
    }


    async function uploadCmsVideo(api,file){
      if(!file) throw new Error('Choose an MP4 or WebM video.');
      if(!/\.(mp4|webm)$/i.test(String(file.name||'')) && !['video/mp4','video/webm'].includes(String(file.type||'').toLowerCase())) throw new Error('Please choose an MP4 or WebM video.');
      const fd=new FormData(); fd.append('file',file,file.name||'hero-video.mp4'); fd.append('target','video');
      const response=await fetch('?api=upload',{method:'POST',headers:{'X-CMS-CSRF':api.csrf},body:fd}); let result={};
      try{result=await cmsResponseJson(response)}catch(err){throw new Error('The upload server returned an invalid response.')}
      if(!response.ok||!result.ok) throw new Error(result.error||'Video upload failed.'); return result;
    }

    function VideoUploadField({api,path,label='Hero video'}){
      const inputRef=useRef(null); const [busy,setBusy]=useState(false); const [status,setStatus]=useState(''); const value=String(get(api.data,path)||'');
      async function upload(file){if(!file||busy)return;setBusy(true);setStatus('Uploading & optimizing video...');try{const result=await uploadCmsVideo(api,file);api.update(path,result.url);setStatus(result.message||'Video uploaded.')}catch(err){const msg=err?.message||'Video upload failed.';setStatus(msg);api.setNotice(msg)}finally{setBusy(false);if(inputRef.current)inputRef.current.value=''}}
      return h('div',{className:'mb-3'},h('label',{className:'form-label fw-semibold'},api.t(label)),h('div',{className:'input-group'},h('input',{className:'form-control',value,onChange:e=>api.update(path,e.target.value),placeholder:'/assets/video/hero-demo.mp4'}),h('button',{type:'button',className:'btn btn-outline-secondary',disabled:busy,onClick:()=>inputRef.current?.click()},busy?api.t('Uploading...'):api.t('Upload video'))),h('input',{ref:inputRef,type:'file',accept:'video/mp4,video/webm,.mp4,.webm',hidden:true,onChange:e=>upload(e.target.files?.[0])}),status&&h('div',{className:'small muted mt-2'},api.t(status)),h('div',{className:'small muted mt-1'},api.t('MP4/WebM up to 50MB. When ffmpeg is available LPCMS re-encodes the hero video to lightweight 1280px H.264 MP4.')));
    }


    async function cmsCropImageFile(file,{aspect='4:3',x=50,y=50,zoom=1}={}){
      const url=URL.createObjectURL(file);
      try{
        const img=await new Promise((resolve,reject)=>{ const el=new Image(); el.onload=()=>resolve(el); el.onerror=()=>reject(new Error('This image cannot be cropped in the browser. Try uploading the original.')); el.src=url; });
        const sw=img.naturalWidth||img.width, sh=img.naturalHeight||img.height;
        if(!sw||!sh) throw new Error('Image dimensions could not be read.');
        const ratios={'1:1':1,'4:3':4/3,'16:9':16/9,'3:2':3/2};
        const targetAspect=aspect==='original' ? sw/sh : (ratios[aspect]||4/3);
        let cropW=sw, cropH=sh;
        if(sw/sh>targetAspect) cropW=sh*targetAspect; else cropH=sw/targetAspect;
        const z=Math.max(1,Math.min(3,Number(zoom)||1));
        cropW/=z; cropH/=z;
        const px=Math.max(0,Math.min(100,Number(x)||50))/100;
        const py=Math.max(0,Math.min(100,Number(y)||50))/100;
        const sx=(sw-cropW)*px, sy=(sh-cropH)*py;
        const ms=(window.__LPCMS_MEDIA_SETTINGS||{}); const maxW=Number(ms.boxWidth||ms.maxImageWidth||1920); const maxH=Number(ms.boxHeight||maxW);
        const ratioOut=Math.min(1,maxW/Math.max(1,cropW),maxH/Math.max(1,cropH));
        const outW=Math.max(1,Math.round(cropW*ratioOut));
        const outH=Math.max(1,Math.round(cropH*ratioOut));
        const canvas=document.createElement('canvas'); canvas.width=outW; canvas.height=outH;
        const ctx=canvas.getContext('2d',{alpha:true}); if(!ctx) throw new Error('Image cropping is unavailable in this browser.');
        ctx.drawImage(img,sx,sy,cropW,cropH,0,0,outW,outH);
        const blob=await new Promise(resolve=>canvas.toBlob(resolve,'image/png',.94));
        if(!blob) throw new Error('The cropped image could not be created.');
        const base=String(file.name||'image').replace(/\.[^.]+$/,'')||'image';
        return new File([blob],base+'-cropped.png',{type:'image/png',lastModified:Date.now()});
      }finally{ URL.revokeObjectURL(url); }
    }

    function ImageCropDialog({file,onCancel,onOriginal,onCrop}){
      const [aspect,setAspect]=useState('4:3'); const [x,setX]=useState(50); const [y,setY]=useState(50); const [zoom,setZoom]=useState(1); const [natural,setNatural]=useState(null); const [working,setWorking]=useState(false);
      const url=useMemo(()=>file?URL.createObjectURL(file):'',[file]);
      useEffect(()=>()=>{ if(url) URL.revokeObjectURL(url); },[url]);
      const ratios={'1:1':1,'4:3':4/3,'16:9':16/9,'3:2':3/2};
      const ratio=aspect==='original'&&natural ? natural.w/natural.h : (ratios[aspect]||4/3);
      async function confirmCrop(){ setWorking(true); try{ const cropped=await cmsCropImageFile(file,{aspect,x,y,zoom}); await onCrop(cropped); } finally{ setWorking(false); } }
      const dialog=h('div',{className:'cms-modal-backdrop',onClick:working?undefined:onCancel}, h('div',{className:'cms-modal cms-crop-modal',onClick:e=>e.stopPropagation()},
        h('div',{className:'d-flex justify-content-between align-items-center gap-3 mb-3'}, h('div',null,h('h3',{className:'h5 mb-1'},'Cover & crop image'),h('div',{className:'small muted'},'Choose a crop if needed. Every uploaded image is then scaled to the selected media box and stored as optimized AVIF.')), h('button',{type:'button',className:'icon-btn',disabled:working,onClick:onCancel},icon('bi bi-x-lg'))),
        h('div',{className:'cms-crop-preview',style:{aspectRatio:String(ratio)}}, h('img',{src:url,alt:'Crop preview',onLoad:e=>setNatural({w:e.currentTarget.naturalWidth,h:e.currentTarget.naturalHeight}),style:{objectPosition:`${x}% ${y}%`,transform:`scale(${zoom})`}})),
        h('div',{className:'mb-3'}, h('div',{className:'small fw-bold mb-2'},'Crop shape'), h('div',{className:'cms-crop-aspects'}, ['original','1:1','4:3','3:2','16:9'].map(a=>h('button',{type:'button',key:a,className:aspect===a?'active':'',onClick:()=>setAspect(a)},a==='original'?'Original ratio':a)))),
        h('div',{className:'cms-crop-controls'},
          h('label',null,'Horizontal focus',h('input',{type:'range',min:0,max:100,value:x,onChange:e=>setX(Number(e.target.value))})),
          h('label',null,'Vertical focus',h('input',{type:'range',min:0,max:100,value:y,onChange:e=>setY(Number(e.target.value))})),
          h('label',null,'Zoom',h('input',{type:'range',min:1,max:3,step:.05,value:zoom,onChange:e=>setZoom(Number(e.target.value))})),
          h('div',{className:'small muted align-self-end'},'Cover crop keeps the chosen frame filled without stretching the image.')
        ),
        h('div',{className:'d-flex flex-wrap justify-content-end gap-2 mt-4'}, h('button',{type:'button',className:'btn-ghost',disabled:working,onClick:onCancel},'Cancel'), h('button',{type:'button',className:'btn-ghost',disabled:working,onClick:onOriginal},'Keep full image'), h('button',{type:'button',className:'btn-cms',disabled:working,onClick:confirmCrop},working?'Cropping...':'Crop & upload'))
      ));
      return ReactDOM.createPortal(dialog,document.body);
    }

    function FreeImageDialog({api,path,target='img',onClose}){
      const [provider,setProvider]=useState('free'); const [q,setQ]=useState('business'); const [results,setResults]=useState([]); const [busy,setBusy]=useState(false); const [importing,setImporting]=useState(''); const [error,setError]=useState('');
      const topics=['business','technology','nature','food','travel','people','office','architecture'];
      async function search(term=q){const text=String(term||'').trim();if(!text)return;setBusy(true);setError('');try{const r=await fetch('?api=media-search&provider='+encodeURIComponent(provider)+'&q='+encodeURIComponent(text)+'&page=1',{cache:'no-store'});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Media search failed.');const rows=j.results||[];setResults(rows);setQ(text);if(!rows.length)setError('No free images found for this search. Try another keyword or source.')}catch(e){const msg=e?.message||'Media search failed.';setResults([]);setError(msg);api.setNotice(msg)}finally{setBusy(false)}}
      async function choose(item){setImporting(item.id||item.url);setError('');try{const r=await fetch('?api=media-import',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':api.csrf},body:JSON.stringify({url:item.url,thumb:item.thumb,target,title:item.title,creator:item.creator,source:item.source,license:item.license,licenseUrl:item.licenseUrl,landing:item.landing,...(window.__LPCMS_MEDIA_SETTINGS||{})})});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Import failed.');api.update(path,j.url);api.setNotice('Free image imported into '+path+'.');onClose()}catch(e){const msg=e?.message||'Import failed.';setError(msg);api.setNotice(msg)}finally{setImporting('')}}
      useEffect(()=>{search('business')},[provider]);
      const dialog=h('div',{className:'cms-modal-backdrop',onClick:onClose},h('div',{className:'cms-modal',onClick:e=>e.stopPropagation()},
        h('div',{className:'d-flex justify-content-between align-items-start gap-3 mb-3'},h('div',null,h('h3',{className:'h4 mb-1'},'Choose a free image'),h('p',{className:'small muted mb-0'},'Search public free/open image libraries, preview the result, then import a local optimized copy.')),h('button',{className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))),
        h('div',{className:'media-source-toolbar'},h('select',{className:'form-select',value:provider,onChange:e=>{setProvider(e.target.value);setResults([]);setError('')}},h('option',{value:'free'},'Free images · automatic'),h('option',{value:'openverse'},'Openverse · no key'),h('option',{value:'commons'},'Wikimedia Commons · no key'),h('option',{value:'pexels'},'Pexels · no key'),h('option',{value:'pixabay'},'Pixabay · no key'),h('option',{value:'unsplash'},'Unsplash · no key')),h('input',{className:'form-control',value:q,onChange:e=>setQ(e.target.value),onKeyDown:e=>{if(e.key==='Enter')search()},placeholder:'Search free images...'}),h('button',{className:'btn-cms',disabled:busy,onClick:()=>search()},busy?'Searching...':'Search')),
        h('div',{className:'topic-chips'},topics.map(t=>h('button',{type:'button',className:'topic-chip',key:t,onClick:()=>search(t)},t))),
        error&&h('div',{className:'alert alert-warning py-2 small'},error),
        results.length?h('div',{className:'free-media-grid'},results.slice(0,18).map(item=>h('article',{className:'free-media-card',key:item.id||item.url},h('img',{src:item.thumb,alt:item.title||'',loading:'lazy',decoding:'async',fetchPriority:'low'}),h('div',{className:'meta'},h('div',{className:'title'},item.title||'Free image'),h('div',{className:'credit'},[item.creator,item.source,item.license].filter(Boolean).join(' · ')),item.landing&&h('a',{className:'small',href:item.landing,target:'_blank',rel:'noopener noreferrer'},'Source / licence'),h('button',{className:'btn-ghost',disabled:!!importing,onClick:()=>choose(item)},importing===(item.id||item.url)?'Importing...':'Use this image'))))):!error&&h('div',{className:'small muted py-4 text-center'},busy?'Searching...':'Search to load free images.')
      ));
      return ReactDOM.createPortal(dialog,document.body);
    }

    function EditableImage({api,path,alt='',className='',fallback=null,fallbackSrc='',errorSrc='',target='img',hideWhenEmpty=false,onView=null,eager=false,iconPath=''}){
      const inputRef=useRef(null);
      const [busy,setBusy]=useState(false);
      const [dragging,setDragging]=useState(false);
      const [status,setStatus]=useState('');
      const [pendingFile,setPendingFile]=useState(null);
      const [freeOpen,setFreeOpen]=useState(false);
      const stored=String(get(api.data,path)||'');
      const selectedIcon=iconPath?String(get(api.data,iconPath)||''):'';
      const value=mediaUrl(stored||fallbackSrc||'');
      // Versioned, local image assets ship 640px previews alongside 1200px originals.
      // Editor-uploaded images do not use this path and remain fully editable.
      const bundledResponsive=/^\/assets\/img\/(?:blog|portfolio)\/v48\/[^?#]+\.(?:webp|avif)$/i.test(stored);
      const mediaSmall=bundledResponsive?mediaUrl(stored.replace(/\.(webp|avif)$/i,'-640.$1')):'';
      const mediaSet=mediaSmall?`${mediaSmall} 640w, ${value} 1200w`:undefined;
      const mediaSizes=bundledResponsive?(stored.includes('/blog/')?'(max-width: 767px) 100vw, (max-width: 1199px) 50vw, 33vw':'(max-width: 767px) 100vw, (max-width: 1199px) 50vw, 33vw'):undefined;
      if(!api.admin && !value && hideWhenEmpty) return null;
      async function performUpload(file){
        if(!file || busy) return;
        setBusy(true); setStatus('Uploading image...');
        try{
          const result=await uploadCmsImage(api,file,target);
          api.update(path,result.url);
          setStatus(result.width?`${String(result.format||'image').toUpperCase()} ${result.width}×${result.height}`:`${String(result.format||'image').toUpperCase()} uploaded`);
          setPendingFile(null);
        }catch(err){
          const message=err?.message||'Image upload failed.';
          setStatus(message); api.setNotice(message);
        }finally{
          setBusy(false); setDragging(false);
          if(inputRef.current) inputRef.current.value='';
        }
      }
      function accept(file){ if(!file || busy) return; setDragging(false); setStatus('Choose cover / crop options'); setPendingFile(file); }
      const wrapperProps={className:cls('cms-image-editor',className,api.admin&&'is-admin',!value&&'is-empty',iconPath&&'has-icon-choice',selectedIcon&&'has-selected-icon',dragging&&'is-dragging',busy&&'is-busy'),title:status||undefined};
      if(api.admin){
        wrapperProps.onDragEnter=e=>{e.preventDefault();setDragging(true)};
        wrapperProps.onDragOver=e=>{e.preventDefault();e.dataTransfer.dropEffect='copy';setDragging(true)};
        wrapperProps.onDragLeave=e=>{e.preventDefault();if(!e.currentTarget.contains(e.relatedTarget))setDragging(false)};
        wrapperProps.onDrop=e=>{e.preventDefault();setDragging(false);accept(e.dataTransfer.files?.[0])};
      }else if(onView && value){
        wrapperProps.onClick=onView; wrapperProps.role='button'; wrapperProps.tabIndex=0;
        wrapperProps.onKeyDown=e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();onView(e)}};
      }
      return h(React.Fragment,null,
        h('div',wrapperProps,
          value?h('img',{src:value,alt,srcSet:mediaSet,sizes:mediaSizes,width:1200,height:stored.includes('/blog/')?675:750,loading:eager?'eager':'lazy',decoding:'async',fetchPriority:eager?'high':'low','data-lightbox':'site','data-lightbox-src':value,onError:errorSrc?e=>{const src=mediaUrl(errorSrc);const img=e.currentTarget;if(!img.dataset.cmsImageFallback&&src){img.dataset.cmsImageFallback='1';img.removeAttribute('srcset');img.removeAttribute('sizes');img.src=src;img.dataset.lightboxSrc=src;}}:undefined}):h('div',{className:'cms-image-fallback'},iconPath?icon(selectedIcon||'bi bi-plus-lg'):(fallback||icon('bi bi-image'))),
          api.admin&&h(React.Fragment,null,
            iconPath&&!value&&h('button',{type:'button',className:'cms-icon-select-primary','aria-label':selectedIcon?'Change icon':'Choose icon',title:selectedIcon?'Change icon':'Choose icon',onClick:e=>{e.preventDefault();e.stopPropagation();try{sessionStorage.setItem('lpcms_icon_target',iconPath)}catch(x){}api.setAdminRequestedTab('icons');api.setAdminOpen(true)}},h('span',{className:'cms-icon-select-badge'},icon(selectedIcon?'bi bi-pencil':'bi bi-plus-lg')),h('span',{className:'visually-hidden'},selectedIcon?'Change icon':'Choose icon')),
            h('input',{ref:inputRef,type:'file',accept:'image/*,.avif,.heic,.heif',hidden:true,onChange:e=>accept(e.target.files?.[0])}),
            h('button',{type:'button',className:'cms-image-overlay',disabled:busy,onClick:e=>{e.preventDefault();e.stopPropagation();inputRef.current?.click()}},icon(busy?'bi bi-arrow-repeat':'bi bi-crop'),h('span',null,busy?'Uploading...':dragging?'Drop image here':status||'Upload / cover / crop')),
            iconPath&&value&&h('button',{type:'button',className:'cms-image-icon-choice','aria-label':'Use an icon instead',title:'Use an icon instead',onClick:e=>{e.preventDefault();e.stopPropagation();try{sessionStorage.setItem('lpcms_icon_target',iconPath)}catch(x){}api.setAdminRequestedTab('icons');api.setAdminOpen(true)}},icon('bi bi-grid')),
            value&&h('button',{type:'button',className:'cms-image-view','aria-label':api.t('Preview image'),title:api.t('Preview image'),onClick:e=>{e.preventDefault();e.stopPropagation();const img=e.currentTarget.closest('.cms-image-editor')?.querySelector('img');if(img)window.dispatchEvent(new CustomEvent('lpcms:open-lightbox',{detail:{img}}))}},icon('bi bi-arrows-fullscreen')),
            h('button',{type:'button',className:'cms-image-free','aria-label':'Choose free image',title:'Choose free image',onClick:e=>{e.preventDefault();e.stopPropagation();setFreeOpen(true)}},icon('bi bi-search')),
            stored&&h('button',{type:'button',className:'cms-image-remove','aria-label':'Remove image',title:'Remove image',onClick:e=>{e.preventDefault();e.stopPropagation();api.update(path,'');setStatus('Image removed')}},icon('bi bi-trash'))
          )
        ),
        pendingFile&&h(ImageCropDialog,{file:pendingFile,onCancel:()=>{setPendingFile(null);setStatus('')},onOriginal:()=>performUpload(pendingFile),onCrop:performUpload}),
        freeOpen&&h(FreeImageDialog,{api,path,target,onClose:()=>setFreeOpen(false)})
      );
    }

    const InlineEditContext = React.createContext(null);

    function AiInlineDialog({cms,path,current,html=false,onClose}){
      const [mode,setMode]=useState('improve'); const [instruction,setInstruction]=useState(''); const [language,setLanguage]=useState(''); const [output,setOutput]=useState(''); const [busy,setBusy]=useState(false);
      const modes={write:'Write new content for this field',improve:'Improve this content while keeping its meaning',shorten:'Make this shorter and clearer',clearer:'Rewrite this in simple, clear language',professional:'Rewrite in a professional business tone',friendly:'Rewrite in a friendly approachable tone',translate:'Translate this content',seo:'Rewrite this to be SEO-friendly and useful to humans'};
      function promptText(){ return `${modes[mode]||modes.improve}.${language?` Target language: ${language}.`:''}${instruction?` Extra instruction: ${instruction}.`:''}${html?' Return clean HTML suitable for a website content field.':' Return only the replacement text.'}`; }
      async function generate(){setBusy(true);setOutput('');try{const r=await fetch('?api=ai-generate',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':cms.csrf},body:JSON.stringify({prompt:promptText(),context:String(current||''),tone:mode,target:path})});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'AI generation failed.');setOutput(j.text||'')}catch(e){cms.setNotice?.(e.message||'AI generation failed.')}finally{setBusy(false)}}
      async function copyPrompt(){const text=`${promptText()}\n\nCurrent content:\n${String(current||'')}`;try{await navigator.clipboard.writeText(text);cms.setNotice?.('AI prompt copied.')}catch(e){cms.setNotice?.('Copy failed.')}}
      function apply(){if(!output.trim())return;cms.update(path,output.trim());cms.setNotice?.('AI content applied. Save JSON when ready.');onClose();}
      return ReactDOM.createPortal(h('div',{className:'cms-modal-backdrop',onClick:onClose},h('div',{className:'cms-modal small ai-inline-modal',onClick:e=>e.stopPropagation()},
        h('div',{className:'d-flex justify-content-between align-items-start gap-2 mb-3'},h('div',null,h('h3',{className:'h5 mb-1'},'AI content tools'),h('div',{className:'small muted'},path)),h('button',{className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))),
        h(Field,{label:'Action'},h('select',{className:'form-select',value:mode,onChange:e=>setMode(e.target.value)},Object.entries(modes).map(([k,v])=>h('option',{key:k,value:k},v)))),
        mode==='translate'&&h(Field,{label:'Target language'},h('input',{className:'form-control',value:language,onChange:e=>setLanguage(e.target.value),placeholder:'e.g. Latvian, English, German'})),
        h(Field,{label:'Optional instruction'},h('input',{className:'form-control',value:instruction,onChange:e=>setInstruction(e.target.value),placeholder:'Keywords, audience, offer, length...'})),
        h('div',{className:'small muted mb-2'},'Current content: ',String(current||'').replace(/<[^>]+>/g,' ').slice(0,220)),
        h('div',{className:'d-flex gap-2 flex-wrap mb-3'},h('button',{className:'btn-cms',disabled:busy,onClick:generate},busy?'Generating...':'Generate'),h('button',{className:'btn-ghost',onClick:copyPrompt},'Copy prompt')),
        h(Field,{label:'AI result'},h('textarea',{className:'form-control',value:output,onChange:e=>setOutput(e.target.value),placeholder:'Generated content appears here. You can edit it before applying.'})),
        h('div',{className:'d-flex justify-content-end gap-2'},h('button',{className:'btn-ghost',onClick:onClose},'Cancel'),h('button',{className:'btn-cms',disabled:!output.trim(),onClick:apply},'Apply'))
      )),document.body);
    }

    function Editable({path,tag='span',className='',html=false,placeholder=''}){
      const cms=React.useContext(InlineEditContext);
      if(!cms) return null;
      const {data,admin,update,requestSavePrompt,csrf,setNotice,lang}=cms;
      const Tag=tag; const editPath=localizedDataPath(path,lang); const baseVal=get(data,path) ?? ''; const val=localizedDataValue(data,path,lang) ?? ''; const canEdit=admin; const elRef=useRef(null); const toolbarRef=useRef(null); const pendingInline=useRef(false); const [active,setActive]=useState(false); const [aiOpen,setAiOpen]=useState(false); const [pos,setPos]=useState({top:8,left:8});
      const parts=String(path).split('.'); const leaf=parts[parts.length-1]||''; const supportsStyle=!html && !/^\d+$/.test(leaf); const stylePath=supportsStyle?[...parts.slice(0,-1),leaf+'Style'].join('.'):''; const rawStyle=supportsStyle?get(data,stylePath):null; const savedStyle=(rawStyle&&typeof rawStyle==='object'&&!Array.isArray(rawStyle))?rawStyle:{};
      function editorStyle(){ const out={}; if(savedStyle.textAlign) out.textAlign=savedStyle.textAlign; if(savedStyle.color) out.color=savedStyle.color; if(savedStyle.fontWeight) out.fontWeight=savedStyle.fontWeight; if(savedStyle.fontSize) out.fontSize=savedStyle.fontSize; if(savedStyle.fontStyle) out.fontStyle=savedStyle.fontStyle; if(savedStyle.textDecoration) out.textDecoration=savedStyle.textDecoration; return out; }
      function positionToolbar(){ if(!elRef.current)return; const r=elRef.current.getBoundingClientRect(); const width=620; setPos({top:Math.max(8,r.top-46),left:Math.max(8,Math.min(window.innerWidth-width-8,r.left))}); }
      function markInlineChanged(){ pendingInline.current=true; }
      function askToSaveInline(){ if(!pendingInline.current)return; pendingInline.current=false; requestSavePrompt?.('You changed inline content or formatting. Save these changes to data.json now?'); }
      function commitCurrentText(){ if(!elRef.current)return false; const next=html?elRef.current.innerHTML:elRef.current.textContent; const changed=String(next??'')!==String(val??''); if(changed){ update(editPath,next); markInlineChanged(); } return changed; }
      function changeStyle(key,value){ commitCurrentText(); const current=get(data,stylePath); const next=(current&&typeof current==='object'&&!Array.isArray(current))?{...current}:{}; if(value===''||value==null) delete next[key]; else next[key]=value; update(stylePath,next); markInlineChanged(); }
      function clearStyle(){ commitCurrentText(); update(stylePath,{}); markInlineChanged(); }
      useEffect(()=>{ if(!active)return; positionToolbar(); const move=()=>positionToolbar(); const outside=e=>{ if(elRef.current?.contains(e.target)||toolbarRef.current?.contains(e.target))return; setActive(false); askToSaveInline(); }; window.addEventListener('resize',move); window.addEventListener('scroll',move,true); document.addEventListener('pointerdown',outside,true); return()=>{window.removeEventListener('resize',move);window.removeEventListener('scroll',move,true);document.removeEventListener('pointerdown',outside,true)}; },[active]);
      const props={ref:elRef,className:cls('cms-editable',canEdit&&'admin',className),suppressContentEditableWarning:true,style:editorStyle()};
      if(canEdit){ props.contentEditable=true; props.onFocus=()=>{setActive(supportsStyle);setTimeout(positionToolbar,0)}; props.onClick=()=>{if(supportsStyle){setActive(true);setTimeout(positionToolbar,0)}}; props.onBlur=e=>{ const next=html?e.currentTarget.innerHTML:e.currentTarget.textContent; const changed=String(next??'')!==String(val??''); if(changed){ update(editPath,next); markInlineChanged(); } const movedToToolbar=toolbarRef.current?.contains(e.relatedTarget); if(!movedToToolbar) askToSaveInline(); }; props.onKeyDown=e=>{if(e.key==='Escape'){setActive(false);askToSaveInline();}}; props.title=supportsStyle?'Click to edit - alignment, colour and weight tools available':'Click to edit'; }
      const node=html?h(Tag,{...props,dangerouslySetInnerHTML:{__html:val||placeholder}}):h(Tag,props,val||placeholder);
      if(!canEdit||!supportsStyle||!active) return node;
      const toolbar=h('div',{ref:toolbarRef,className:'cms-inline-toolbar',style:{top:pos.top,left:pos.left},onPointerDown:e=>{if(e.target.closest?.('button'))e.preventDefault()}},
        h('select',{title:'Text size','aria-label':'Text size',value:String(savedStyle.fontSize||''),onChange:e=>changeStyle('fontSize',e.target.value)},h('option',{value:''},'Size'),h('option',{value:'.875rem'},'Small'),h('option',{value:'1rem'},'Normal'),h('option',{value:'1.25rem'},'Large'),h('option',{value:'1.6rem'},'XL'),h('option',{value:'2.2rem'},'2XL')),
        h('select',{title:'Font weight','aria-label':'Font weight',value:String(savedStyle.fontWeight||''),onChange:e=>changeStyle('fontWeight',e.target.value)},h('option',{value:''},'Weight'),h('option',{value:'400'},'Regular'),h('option',{value:'500'},'Medium'),h('option',{value:'600'},'Semi'),h('option',{value:'700'},'Bold'),h('option',{value:'800'},'Extra')),
        h('button',{type:'button',className:savedStyle.fontStyle==='italic'?'active':'',title:'Italic',onClick:()=>changeStyle('fontStyle',savedStyle.fontStyle==='italic'?'':'italic')},icon('bi bi-type-italic')),
        h('button',{type:'button',className:savedStyle.textDecoration==='underline'?'active':'',title:'Underline',onClick:()=>changeStyle('textDecoration',savedStyle.textDecoration==='underline'?'':'underline')},icon('bi bi-type-underline')),
        h('span',{className:'toolbar-divider'}),
        ['left','center','right'].map(a=>h('button',{type:'button',key:a,className:savedStyle.textAlign===a?'active':'',title:'Align '+a,'aria-label':'Align '+a,onClick:()=>changeStyle('textAlign',savedStyle.textAlign===a?'':a)},icon('bi bi-text-'+a))),
        h('span',{className:'toolbar-divider'}),
        h('label',{className:'cms-inline-color',title:'Text colour'},h('span',{className:'visually-hidden'},'Text colour'),h('input',{type:'color','aria-label':'Text colour',value:savedStyle.color||'#1f2937',onInput:e=>changeStyle('color',e.target.value)})),
        h('button',{type:'button',title:'Clear text styling','aria-label':'Clear text styling',onClick:clearStyle},icon('bi bi-eraser')),
        data.settings?.ai?.enabled&&h('button',{type:'button',className:'ai-inline-btn',title:'AI write / improve / translate',onClick:()=>{commitCurrentText();setAiOpen(true)}},icon('bi bi-stars'))
      );
      return h(React.Fragment,null,node,ReactDOM.createPortal(toolbar,document.body),aiOpen&&h(AiInlineDialog,{cms:{data,update,csrf,setNotice},path:editPath,current:localizedDataValue(data,path,lang)||'',html,onClose:()=>setAiOpen(false)}));
    }

    function GlobalImageLightbox(){
      const [state,setState]=useState(null);
      function toUrl(value){const raw=mediaUrl(String(value||'').trim());if(!raw)return '';try{return new URL(raw,location.origin+(CMS_BASE_PATH||'/')).href}catch(e){try{return new URL(raw,document.baseURI).href}catch(x){return raw}}}
      function looksLikeImageUrl(value){return /\.(?:avif|webp|png|jpe?g|gif|svg)(?:[?#].*)?$/i.test(String(value||''))||/^https?:\/\/(?:images\.|images\.unsplash\.com|.*\.(?:cloudfront|wikimedia)\.)/i.test(String(value||''));}
      function imageItem(img){if(!img)return null;const attr=img.getAttribute('data-lightbox-src')||img.getAttribute('data-full-src')||'';const rendered=img.currentSrc||img.getAttribute('src')||img.src||'';const anchor=img.closest('a');const href=anchor?.getAttribute('href')||'';const source=attr||(looksLikeImageUrl(href)?href:'')||rendered;const src=toUrl(source),fallback=toUrl(rendered);if(!src)return null;return {src,fallback:fallback&&fallback!==src?fallback:'',alt:img.alt||anchor?.getAttribute('aria-label')||'',node:img};}
      function eligible(img,allowAdmin=false){if(!img||img.closest('.admin-panel,.site-header,.site-footer,.no-lightbox,.cms-crop-preview,.free-media-grid,.cms-upload-result'))return false;if(!allowAdmin&&img.closest('.cms-image-editor.is-admin'))return false;return img.hasAttribute('data-lightbox')||!!img.closest('.admin-super-image,.admin-shot-card');}
      function openFrom(img,allowAdmin=false){if(!eligible(img,allowAdmin))return;const group=img.getAttribute('data-lightbox')||'site';const scope=img.closest('section')||img.closest('.cms-modal')||document.body;let candidates=[...scope.querySelectorAll('img[data-lightbox]')].filter(x=>eligible(x,allowAdmin));if(group&&group!=='site')candidates=candidates.filter(x=>(x.getAttribute('data-lightbox')||'site')===group);const rawItems=candidates.map(imageItem).filter(Boolean),seen=new Set(),items=[];for(const it of rawItems){if(seen.has(it.src))continue;seen.add(it.src);items.push(it)}const clicked=imageItem(img);if(!clicked)return;let index=items.findIndex(it=>it.src===clicked.src);if(index<0){items.unshift(clicked);index=0}setState({items,index,error:''});}
      useEffect(()=>{function click(e){const img=e.target?.closest?.('img');if(!eligible(img,false)||img.closest('.product-gallery'))return;const anchor=img.closest('a'),href=anchor?.getAttribute('href')||'';if(anchor&&!looksLikeImageUrl(href)&&!anchor.classList.contains('admin-super-image')&&!anchor.closest('.admin-shot-card'))return;e.preventDefault();e.stopPropagation();openFrom(img,false)}function custom(e){const img=e?.detail?.img;if(img)openFrom(img,true)}document.addEventListener('click',click,true);window.addEventListener('lpcms:open-lightbox',custom);return()=>{document.removeEventListener('click',click,true);window.removeEventListener('lpcms:open-lightbox',custom)}},[]);
      useEffect(()=>{if(!state)return;const key=e=>{if(e.key==='Escape')setState(null);if(e.key==='ArrowLeft')setState(v=>v?{...v,index:(v.index-1+v.items.length)%v.items.length,error:''}:v);if(e.key==='ArrowRight')setState(v=>v?{...v,index:(v.index+1)%v.items.length,error:''}:v)};document.addEventListener('keydown',key);const old=document.body.style.overflow;document.body.style.overflow='hidden';return()=>{document.removeEventListener('keydown',key);document.body.style.overflow=old}},[!!state]);
      if(!state)return null;const item=state.items[state.index]||{},many=state.items.length>1;function imageError(){if(item.fallback&&item.fallback!==item.src){setState(v=>{if(!v)return v;const items=v.items.slice();items[v.index]={...items[v.index],src:item.fallback,fallback:''};return {...v,items,error:''}})}else setState(v=>v?{...v,error:'Image preview could not be loaded.'}:v)}
      return ReactDOM.createPortal(h('div',{className:'cms-global-lightbox',role:'dialog','aria-modal':'true','aria-label':'Image preview',onClick:()=>setState(null)},h('div',{className:'cms-global-lightbox-frame',onClick:e=>e.stopPropagation()},h('button',{type:'button',className:'cms-global-lightbox-close','aria-label':'Close',title:'Close',onClick:()=>setState(null)},icon('bi bi-x-lg')),many&&h('button',{type:'button',className:'cms-global-lightbox-nav cms-global-lightbox-prev','aria-label':'Previous image',onClick:()=>setState(v=>({...v,index:(v.index-1+v.items.length)%v.items.length,error:''}))},icon('bi bi-chevron-left')),state.error?h('div',{className:'cms-global-lightbox-status',role:'status'},state.error):h('img',{key:item.src,className:'cms-global-lightbox-image',src:item.src,alt:item.alt||'',onError:imageError,decoding:'async'}),many&&h('button',{type:'button',className:'cms-global-lightbox-nav cms-global-lightbox-next','aria-label':'Next image',onClick:()=>setState(v=>({...v,index:(v.index+1)%v.items.length,error:''}))},icon('bi bi-chevron-right')),item.alt&&h('div',{className:'cms-global-lightbox-caption'},item.alt))),document.body);
    }

    function App(){
      const [boot,setBoot] = useState(initialBoot);
      const [data,setData] = useState(seed);
      const [bootReady,setBootReady] = useState(false);
      const [bootError,setBootError] = useState('');
      const [lang,setLangState] = useState(SERVER_LANG || DEFAULT_LANG);
      const [adminOpen,setAdminOpen] = useState(false);
      const [adminRequestedTab,setAdminRequestedTab] = useState('');
      const [modal,setModal] = useState(null);
      const [quoteItems,setQuoteItems] = useState(()=>{ try{return JSON.parse(localStorage.getItem('cms_quote_items')||'[]')}catch(e){return[]} });
      const [shopCartItems,setShopCartItems] = useState(()=>{ try{return JSON.parse(localStorage.getItem('cms_shop_cart')||'[]')}catch(e){return[]} });
      const [notice,setNotice] = useState('');
      const [dirty,setDirty] = useState(false);
      const [savePrompt,setSavePrompt] = useState(null);
      const admin = boot.admin;
      const csrf = boot.csrf;
      const theme = data.settings.theme || {};
      window.__LPCMS_MEDIA_SETTINGS = data.settings.media || {};
      window.__lpcmsLang = lang;
      function setLang(code){
        code=String(code||DEFAULT_LANG).toLowerCase(); if(!LANGUAGE_CODES.includes(code))code=DEFAULT_LANG;
        try{localStorage.setItem('lpcms_lang',code);localStorage.setItem('lpcms_lang_manual','1')}catch(e){}
        const next=languageUrl(code); if(next!==location.pathname+location.search+location.hash){location.assign(next);return;} setLangState(code);
      }
      function t(text){ return translatedText(lang,text); }
      function href(v){ return internalHref(lang,v); }

      useEffect(()=>{
        document.documentElement.lang=lang;
        const explicit=explicitLangFromPath();
        const cfg=data.settings?.localization||{};
        if(explicit){try{localStorage.setItem('lpcms_lang',explicit)}catch(e){};return;}
        let desired=''; try{desired=cfg.persistChoice!==false?localStorage.getItem('lpcms_lang')||'':''}catch(e){}
        if(!desired&&cfg.autoDetect!==false)desired=browserLanguage();
        if(desired&&LANGUAGE_CODES.includes(desired)&&desired!==DEFAULT_LANG){ const next=languageUrl(desired); if(next!==location.pathname+location.search+location.hash)location.replace(next); }
      },[]);
      useEffect(()=>{ fetch('?api=site',{cache:'no-store'}).then(r=>cmsResponseJson(r)).then(j=>{ if(!j.ok)throw new Error(j.error||'Could not load site data.'); if(j.i18n&&typeof j.i18n==='object'){ Object.keys(I18N).forEach(k=>delete I18N[k]); Object.assign(I18N,j.i18n); } setBoot(j); setData(j.data); if(j.lang&&LANGUAGE_CODES.includes(String(j.lang).toLowerCase()))setLangState(String(j.lang).toLowerCase()); setBootReady(true); }).catch(err=>{ const message=err?.message||'Could not load site data.'; console.error('LPCMS API boot failed:',err); setBootError(message); setBootReady(true); }); }, []);
      useEffect(()=>{
        function scrollHash(behavior='auto'){const raw=String(location.hash||'').replace(/^#/,'');if(!raw)return false;let id=raw;try{id=decodeURIComponent(raw)}catch(e){}const target=document.getElementById(id);if(!target)return false;const header=document.querySelector('.site-header');const offset=(header?.getBoundingClientRect().height||0)+10;const top=Math.max(0,target.getBoundingClientRect().top+window.scrollY-offset);window.scrollTo({top,behavior});return true;}
        const run=()=>{if(location.hash)scrollHash('auto')};requestAnimationFrame(()=>{run();setTimeout(run,80);setTimeout(run,350)});const onHash=()=>requestAnimationFrame(()=>scrollHash('smooth'));window.addEventListener('hashchange',onHash);return()=>window.removeEventListener('hashchange',onHash);
      },[lang,data.version]);
      useEffect(()=>{ document.documentElement.style.setProperty('--cms-brand', theme.brand || '#0d6efd'); document.documentElement.style.setProperty('--cms-brand2', theme.brand2 || '#6610f2'); document.documentElement.style.setProperty('--cms-accent', theme.accent || '#20c997'); document.documentElement.style.setProperty('--cms-text', theme.text || '#1f2937'); document.documentElement.style.setProperty('--cms-muted', theme.muted || '#6b7280'); document.documentElement.style.setProperty('--cms-bg', theme.bg || '#fff'); document.documentElement.style.setProperty('--cms-soft', theme.softBg || '#f6f8fb'); document.documentElement.style.setProperty('--cms-radius', theme.radius || '22px'); }, [theme]);
      useEffect(()=>{ if(window.AOS) window.AOS.init({once:true,duration:700}); }, []);
      useEffect(()=>{ const nodes=[...document.querySelectorAll('.reveal-section')]; if(!nodes.length)return; if(!('IntersectionObserver' in window)){nodes.forEach(el=>el.classList.add('is-visible'));return;} const io=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('is-visible');io.unobserve(entry.target);}}),{threshold:.08,rootMargin:'0px 0px -7% 0px'}); nodes.forEach(el=>io.observe(el)); return()=>io.disconnect(); }, [lang,data.settings?.sectionOrder,data.settings?.sectionPatterns]);
      useEffect(()=>{ const styleId='cms-custom-live'; let st=document.getElementById(styleId); if(!st){ st=document.createElement('style'); st.id=styleId; document.head.appendChild(st);} st.textContent=data.settings.theme.customCss||''; }, [data.settings.theme.customCss]);
      useEffect(()=>{ maybeLoadGA(data); }, [data.settings.analytics, data.settings.cookies]);
      useEffect(()=>{ localStorage.setItem('cms_quote_items', JSON.stringify(quoteItems)); }, [quoteItems]);
      useEffect(()=>{ localStorage.setItem('cms_shop_cart', JSON.stringify(shopCartItems)); }, [shopCartItems]);
      useEffect(()=>{ if(data.settings?.app?.enabled && 'serviceWorker' in navigator && (location.protocol==='https:'||location.hostname==='localhost')) navigator.serviceWorker.register((CMS_BASE_PATH||'')+'/service-worker.js').catch(()=>{}); }, [data.settings?.app?.enabled]);
      useEffect(()=>{ const token=new URLSearchParams(location.search).get('recover-cart'); if(!token)return; fetch('?api=abandoned-cart-recover&token='+encodeURIComponent(token)).then(r=>cmsResponseJson(r)).then(j=>{if(j.ok&&Array.isArray(j.cart?.lines)){setShopCartItems(j.cart.lines);setModal({type:'cart',email:j.email||''});setNotice('Your saved cart has been restored.');const u=new URL(location.href);u.searchParams.delete('recover-cart');history.replaceState({},'',u.pathname+u.search+u.hash);}}).catch(()=>{}); },[]);

      if(!bootReady) return h('div',{className:'lpcms-boot-screen','aria-hidden':'true'});
      if(bootError) return h('main',{className:'lpcms-boot-error',role:'alert'},h('strong',null,'Website could not be loaded.'),h('span',null,bootError));

      function numberValue(v){ if(typeof v==='number') return v; const m=String(v||'').replace(',','.').match(/[0-9]+(?:\.[0-9]+)?/); return m ? Number(m[0]) : 0; }
      function productVariationList(item){ return Array.isArray(item?.variations) ? item.variations.filter(v=>v&&typeof v==='object') : []; }
      function variationLabel(v){ const attrs=v?.attributes||{}; const bits=Object.keys(attrs).map(k=>`${k}: ${attrs[k]}`).filter(Boolean); return bits.length?bits.join(', '):(v?.sku||v?.id||'Variation'); }
      function variationKey(v){ return v?.id||v?.sku||variationLabel(v)||''; }
      function productPriceValue(item,variation=null){ if(variation) return numberValue(variation.price || item?.price); const vars=productVariationList(item).map(v=>numberValue(v.price)).filter(n=>n>0); if((item?.type==='variable'||vars.length) && vars.length) return Math.min(...vars); return numberValue(item?.price); }
      function productOldPriceValue(item,variation=null){ if(variation) return numberValue(variation.oldPrice || item?.oldPrice); const vars=productVariationList(item).map(v=>numberValue(v.oldPrice||v.price)).filter(n=>n>0); if((item?.type==='variable'||vars.length) && vars.length) return Math.min(...vars); return numberValue(item?.oldPrice); }
      function formatMoney(value,currency){ return new Intl.NumberFormat(undefined,{style:'currency',currency:currency||data.products?.currency||'EUR'}).format(Number(value||0)); }
      function formatProductPrice(item,variation=null){ const currency=variation?.currency || item?.currency || data.products?.currency || 'EUR'; const price=productPriceValue(item,variation); const prefix=(!variation && productVariationList(item).length>0) ? t('From')+' ' : ''; return price>0 ? prefix + formatMoney(price,currency) : t('Price on request'); }
      function basketLine(item){ const qty=item.qty||1; const sku=item.sku ? ` [${item.sku}]` : ''; const vars=item.variationLabel ? ` (${item.variationLabel})` : ''; return `${qty} × ${t(item.title||'Product')}${sku}${vars} - ${formatProductPrice(item)}`; }
      function normaliseProductLine(item,variation=null,qty=1){ return {title:item.title||'Product',slug:item.slug||'',id:item.id||'',sku:variation?.sku||item.sku||'',variationId:variationKey(variation),variationLabel:variation?variationLabel(variation):'',attributes:variation?.attributes||item.attributes||{},qty:Math.max(1,Number(qty)||1),price:productPriceValue(item,variation),currency:variation?.currency||item.currency||data.products?.currency||'EUR'}; }
      function addToQuote(item,variation=null,qty=1){ const line=normaliseProductLine(item,variation,qty); setQuoteItems(items=>{ const key=(line.slug||line.title)+'|'+line.variationId; if(items.some(x=>((x.slug||x.title)+'|'+(x.variationId||''))===key)) return items; return [...items,line]; }); setNotice('Added to quote basket'); }
      function addToCart(item,variation=null,qty=1){ const line=normaliseProductLine(item,variation,qty); if(line.price<=0){ addToQuote(item,variation,qty); return; } setShopCartItems(items=>{ const key=(line.slug||line.title)+'|'+line.variationId; const found=items.findIndex(x=>((x.slug||x.title)+'|'+(x.variationId||''))===key); if(found>=0){ const next=[...items]; next[found]={...next[found],qty:(next[found].qty||1)+line.qty}; return next; } return [...items,line]; }); setNotice('Added to shopping cart'); }
      function removeQuote(i){ setQuoteItems(items=>items.filter((_,x)=>x!==i)); }
      function clearQuote(){ setQuoteItems([]); }
      function removeCart(i){ setShopCartItems(items=>items.filter((_,x)=>x!==i)); }
      function clearCart(){ setShopCartItems([]); }
      function cartTotal(){ return shopCartItems.reduce((sum,it)=>sum+(Number(it.price||0)*Number(it.qty||1)),0); }
      function quoteMessage(){ return quoteItems.length ? t('Quote request products:')+'\n' + quoteItems.map((it,i)=>`${i+1}. ${basketLine(it)}`).join('\n') + '\n\n'+t('Message:') : ''; }
      function orderMessage(serverCart=null){ const cfg=data.products||{}; const pay=cfg.payment?.directDebit||{}; const cart=serverCart||null; const items=cart?.lines||shopCartItems; const total=cart?.total??cartTotal(); const lines=items.map((it,i)=>`${i+1}. ${(it.qty||1)} × ${t(it.title||'Product')}${it.sku?' ['+it.sku+']':''}${it.variationLabel?' ('+it.variationLabel+')':''} - ${formatMoney(it.price||0,it.currency||cfg.currency||'EUR')}`).join('\n'); const details=cart?`\n${t('Subtotal')}: ${formatMoney(cart.subtotal||0,cfg.currency||'EUR')}${Number(cart.discount||0)>0?'\n'+t('Discount')+': -'+formatMoney(cart.discount,cfg.currency||'EUR'):''}${cart.shippingLabel?'\n'+t('Shipping')+': '+t(cart.shippingLabel)+' '+formatMoney(cart.shipping||0,cfg.currency||'EUR'):''}`:''; const payment=pay.enabled ? `\n\n${t('Payment method:')} ${t(pay.title||'Direct bank transfer')}\n${t(pay.instructions||'')}${pay.accountName?'\n'+t('Account:')+' '+pay.accountName:''}${pay.iban?'\nIBAN: '+pay.iban:''}${pay.swift?'\nSWIFT/BIC: '+pay.swift:''}${pay.bankName?'\n'+t('Bank:')+' '+pay.bankName:''}` : ''; return items.length ? t('Order products:')+'\n' + lines + details + `\n\n${t('Total')}: ${formatMoney(total,cfg.currency||'EUR')}` + payment + '\n\n'+t('Customer message:') : ''; }

      function update(path,val){ setData(d=>set(d,path,val)); setDirty(true); }
      function updateLocalized(path,val){ update(localizedDataPath(path,lang),val); }
      function localizedValue(path){ return localizedDataValue(data,path,lang); }
      function replace(next){ setData(next); setDirty(true); }
      function requestSavePrompt(message){ setSavePrompt(current=>current||{message:message||'You have unsaved inline changes. Save them now?'}); }
      async function save(options={}){ const silent=!!options?.silent; try{ const r=await fetch('?api=save',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':csrf},body:JSON.stringify(data)}); const j=await cmsResponseJson(r); if(j.ok){setData(j.data);setDirty(false);if(!silent)setNotice('Saved to data.json');return {ok:true,data:j.data};} const error=j.error||'Save failed'; if(!silent)setNotice(error); return {ok:false,error}; }catch(err){ const error=err?.message||'Save failed'; if(!silent)setNotice(error); return {ok:false,error}; } }
      async function logout(){ await fetch('?api=logout',{method:'POST',headers:{'X-CMS-CSRF':csrf}}); location.reload(); }
      const api = {data,setData,update,updateLocalized,localizedPath:(path)=>localizedDataPath(path,lang),localizedValue,replace,Editable,admin,t,href,email:boot.email,role:boot.role||'',isSuper:!!boot.isSuper,setModal,lang,setLang,save,logout,dirty,notice,setNotice,adminOpen,setAdminOpen,adminRequestedTab,setAdminRequestedTab,csrf,boot,setBoot,quoteItems,shopCartItems,addToQuote,addToCart,removeQuote,clearQuote,removeCart,clearCart,cartTotal,quoteMessage,orderMessage,formatProductPrice,productPriceValue,productOldPriceValue,variationLabel,productVariationList};
      const sectionRegistry={hero:Hero,about:About,stats:Stats,features:FeatureBlocks,services:Services,wpThemes:WpThemes,process:Process,gallery:Gallery,apps:DigitalPulseApps,testimonials:Testimonials,cards:ImageCards,contentList:ContentList,team:TeamLead,growth:GrowthMarketing,industries:Industries,logos:LogoStrip,products:Products,booking:Booking,adminShowcase:AdminShowcase,pricing:Pricing,demoIntake:DemoIntake,blog:Blog,faq:FAQ,contact:Contact,cta:CTA};
      const defaultSectionOrder=Object.keys(sectionRegistry); const savedOrder=Array.isArray(data.settings?.sectionOrder)?data.settings.sectionOrder:defaultSectionOrder; const sectionOrder=[...new Set([...savedOrder.filter(x=>sectionRegistry[x]),...defaultSectionOrder.filter(x=>!savedOrder.includes(x))])];
      return h(InlineEditContext.Provider,{value:{data,admin,update,requestSavePrompt,csrf,setNotice,lang}},
        h(Header,{api}),
        boot.route?.item && h(RoutePage,{api,route:boot.route}),
        ...sectionOrder.map((key,sectionIndex)=>{const Comp=sectionRegistry[key];if(!Comp||!data[key]?.enabled)return null;const pattern=String(data.settings?.sectionPatterns?.[key]||'1');const reveal=key==='hero'?'':(sectionIndex%2===0?' reveal-section reveal-left':' reveal-section reveal-right');return h('div',{key,className:'section-pattern-'+pattern+reveal},h(Comp,{api}));}),
        h(Footer,{api}),
        h('button',{className:'btn-cms scroll-up',onClick:()=>scrollTo({top:0,behavior:'smooth'})},'↑'),
        admin && h('button',{className:'btn-cms admin-login',onClick:()=>setAdminOpen(true)}, icon('bi bi-sliders'), ' Admin'),
        !admin && h(LoginButton,{api}),
        data.products?.basketEnabled && quoteItems.length>0 && h('button',{className:'btn-cms basket-btn quote-floating',onClick:()=>setModal({type:'basket'})}, icon('bi bi-bag'), ' Quote ', h('span',{className:'basket-count'},quoteItems.length)),
        data.products?.shopEnabled && data.products?.cartEnabled && shopCartItems.length>0 && h('button',{className:'btn-cms basket-btn cart-floating',onClick:()=>setModal({type:'cart'})}, icon('bi bi-cart3'), ' Cart ', h('span',{className:'basket-count'},shopCartItems.length)),
        admin && h(AdminPanel,{api,open:adminOpen,onClose:()=>setAdminOpen(false)}),
        h(GlobalImageLightbox),
        h(CookieBanner,{api}),
        data.chatbot?.enabled && !admin && h(ChatbotWidget,{api}),
        modal && h(Modal,{modal,setModal,api}),
        savePrompt && h(SaveChangesDialog,{api,prompt:savePrompt,onClose:()=>setSavePrompt(null)}),
        notice && h('div',{className:'cms-modal-backdrop',onClick:()=>setNotice('')}, h('div',{className:'cms-modal small'}, h('p',{className:'mb-3'},notice), h('button',{className:'btn-cms',onClick:()=>setNotice('')},'OK')))
      );
    }

    function maybeLoadGA(data){
      const id=data.settings?.analytics?.ga4Id; if(!id || window.__cmsGaLoaded) return;
      const consentNeeded=data.settings?.analytics?.loadAfterCookieConsent && data.settings?.cookies?.enabled;
      if(consentNeeded && localStorage.getItem('cms_cookie_ok')!=='yes') return;
      window.__cmsGaLoaded=true;
      const s=document.createElement('script'); s.async=true; s.src='https://www.googletagmanager.com/gtag/js?id='+encodeURIComponent(id); document.head.appendChild(s);
      window.dataLayer=window.dataLayer||[]; function gtag(){dataLayer.push(arguments)} window.gtag=gtag; gtag('js',new Date()); gtag('config',id);
    }

    function translate(api,obj,key){ const raw=obj?.i18n?.[api.lang]?.[key] ?? obj?.[key] ?? ''; return api.t(raw); }
    function RoutePage({api,route}){ const item=route.item||{}; const group=route.group||'pages'; const path=`${group}.items.${route.index}`; const E=api.Editable; const isProduct=group==='products'; const price=api.productPriceValue(item); const mode=price<=0?'quote':String(item.purchaseMode||api.data.products?.defaultPurchaseMode||'quote'); const showBuy=isProduct&&mode==='checkout'&&api.data.products?.showBuyButton&&item.buyButton!==false&&price>0; const showQuote=isProduct&&mode==='quote'&&api.data.products?.showQuoteButton&&item.quoteButton!==false; return h('section',{className:'section cms-soft'}, h(Container,{api}, h('article',{className:'cms-card mx-auto',style:{maxWidth:980}}, h('div',{className:'eyebrow text-primary fw-bold mb-2'}, isProduct?'Product':group==='blog'?'Blog':'Page'), h(EditableImage,{api,path:path+'.image',alt:item.title||'',className:'admin-image-field mb-4',hideWhenEmpty:true,eager:true}), h(E,{path:path+'.title',tag:'h1',className:'display-5 fw-bold'}), item.date&&h('p',{className:'muted'},item.date), isProduct&&h(ProductPrice,{api,item,path}), h(E,{path:path+'.body',tag:'div',html:true,className:'fs-5'}), isProduct&&Array.isArray(item.features)&&item.features.length>0&&h('ul',{className:'mt-3'},item.features.map((f,i)=>h('li',{key:i}, h(E,{path:path+'.features.'+i})))), isProduct&&h('div',{className:'product-actions'}, showBuy&&(item.checkoutUrl?h('a',{className:'btn-cms',href:item.checkoutUrl,target:'_blank',rel:'noopener noreferrer'},item.checkoutButtonText||api.data.products?.buyButtonText||'Checkout now'):h('button',{className:'btn-cms',onClick:()=>api.addToCart(item)},item.checkoutButtonText||api.data.products?.buyButtonText||'Checkout now')), showQuote&&h('button',{className:'btn-cms',onClick:()=>api.addToQuote(item)},api.data.products?.quoteButtonText||'Add to quote')), h('div',{className:'share-row'}, shareLinks(location.href,item.title).map(s=>h('a',{key:s.label,href:s.url,target:'_blank',rel:'noopener'},s.label)))))); }
    function Container({api,children,className=''}){ const grid=api.data.settings.theme.grid || 'container'; return h('div',{className:cls(grid,className)},children); }
    function SectionTitle({api,base}){ const E=api.Editable; const defaults={stats:'Highlights',features:'Features',services:'Services',wpThemes:'WordPress sector themes',process:'How it works',gallery:'Gallery',apps:'DigitalPulse apps',testimonials:'Testimonials',cards:'Highlights',contentList:'Details',team:'Team lead',growth:'Marketing, SEO & AI content',industries:'Industries',logos:'Trusted by',products:'Products',booking:'Booking',adminShowcase:'Admin & editing',pricing:'Pricing',demoIntake:'Free demo',blog:'Blog',faq:'FAQ',contact:'Contact'}; const fallback=api.t(defaults[base]||String(base||'').replace(/([a-z])([A-Z])/g,'$1 $2').replace(/^./,m=>m.toUpperCase())); return h('div',{className:'section-title'}, h(E,{path:base+'.preheader',tag:'div',className:'eyebrow',placeholder:fallback}), h(E,{path:base+'.title',tag:'h2'}), h(E,{path:base+'.subtitle',tag:'p'})); }

    function Header({api}){ const {data,setModal,lang,setLang,admin,update}=api; const [open,setOpen]=useState(false); const [langOpen,setLangOpen]=useState(false); const langRef=useRef(null); const E=api.Editable; const menu=data.header.menu||[]; const flagMap={en:'/assets/img/flags/gb.svg',lv:'/assets/img/flags/lv.svg',ru:'/assets/img/flags/ru.svg',fr:'/assets/img/flags/fr.svg',es:'/assets/img/flags/es.svg',de:'/assets/img/flags/de.svg',pl:'/assets/img/flags/pl.svg',lt:'/assets/img/flags/lt.svg',et:'/assets/img/flags/ee.svg',da:'/assets/img/flags/dk.svg',no:'/assets/img/flags/no.svg',sv:'/assets/img/flags/se.svg',fi:'/assets/img/flags/fi.svg',is:'/assets/img/flags/is.svg'}; const languageNames={en:'English',lv:'Latviešu',ru:'Русский',fr:'Français',es:'Español',de:'Deutsch',pl:'Polski',lt:'Lietuvių',et:'Eesti',da:'Dansk',no:'Norsk',sv:'Svenska',fi:'Suomi',is:'Íslenska'}; const languages=data.settings.languages||I18N.languages||[]; const activeLang=languages.find(l=>String(l.code).toLowerCase()===String(lang).toLowerCase())||{code:lang,label:String(lang||'EN').toUpperCase()}; useEffect(()=>{ if(!langOpen)return; const close=e=>{if(langRef.current&&!langRef.current.contains(e.target))setLangOpen(false)}; const esc=e=>{if(e.key==='Escape')setLangOpen(false)}; document.addEventListener('pointerdown',close);document.addEventListener('keydown',esc);return()=>{document.removeEventListener('pointerdown',close);document.removeEventListener('keydown',esc)}; },[langOpen]); useEffect(()=>{ if(!open)return; const prevBodyOverflow=document.body.style.overflow; const prevHtmlOverflow=document.documentElement.style.overflow; document.body.style.overflow='hidden'; document.documentElement.style.overflow='hidden'; const esc=e=>{if(e.key==='Escape')setOpen(false)}; const resize=()=>{if(window.innerWidth>=1200)setOpen(false)}; document.addEventListener('keydown',esc); window.addEventListener('resize',resize,{passive:true}); return()=>{document.removeEventListener('keydown',esc);window.removeEventListener('resize',resize);document.body.style.overflow=prevBodyOverflow;document.documentElement.style.overflow=prevHtmlOverflow}; },[open]); function chooseLanguage(code){setLangOpen(false);setLang(code)} function linkProps(it,mobile=false){const blank=it?.target==='_blank';return {className:mobile?'':'nav-linkx',href:api.href(it?.href||'#'),target:blank?'_blank':undefined,rel:blank?'noopener noreferrer':undefined,onClick:()=>setOpen(false)}} function renderMenu(items,mobile=false){ return h('ul',null,items.map((it,i)=>h('li',{key:i,className:!mobile&&it.children?.length?'has-sub':''}, h('a',linkProps(it,mobile), translate(api,it,'label'), it.children?.length? h('span',{className:'ms-1'},'▾'):null), it.children?.length?h('ul',{className:mobile?'mobile-submenu':'submenu'},it.children.map((c,j)=>h('li',{key:j},h('a',linkProps(c,mobile),translate(api,c,'label'))))):null))); }
      const languagePicker=h('div',{className:'lang-picker',ref:langRef}, h('button',{className:'icon-btn lang-flag-btn',type:'button','aria-label':'Language: '+(languageNames[lang]||activeLang.name||activeLang.label||lang),'aria-haspopup':'menu','aria-expanded':langOpen?'true':'false',title:languageNames[lang]||activeLang.name||activeLang.label||lang,onClick:()=>setLangOpen(v=>!v)},h('img',{className:'flag-img',src:mediaUrl(flagMap[lang]||flagMap.en),alt:'',width:22,height:16,'aria-hidden':'true'})), langOpen&&h('div',{className:'lang-menu',role:'menu'},languages.map(l=>{const code=String(l.code||'').toLowerCase();return h('button',{key:code,type:'button',role:'menuitem',className:cls('lang-option',code===lang&&'active'),onClick:()=>chooseLanguage(code)},h('img',{className:'flag-img',src:mediaUrl(flagMap[code]||flagMap.en),alt:'',width:24,height:18,'aria-hidden':'true'}),h('span',null,l.name||languageNames[code]||l.label||code),h('span',{className:'lang-code'},code.toLowerCase()))}))); 
      const mobileMenu=open&&ReactDOM.createPortal(h(React.Fragment,null,
        h('button',{type:'button',className:'mobile-menu-backdrop','aria-label':api.t('Close menu')||'Close menu',onClick:()=>setOpen(false)}),
        h('aside',{className:'mobile-panel open',role:'dialog','aria-modal':'true','aria-label':api.t('Mobile navigation')||'Mobile navigation'}, h('div',{className:'mobile-panel-head d-flex justify-content-between align-items-center'}, h('a',{href:api.href('#home'),className:'mobile-panel-brand',onClick:()=>setOpen(false)}, data.header.logoImage ? h('img',{src:mediaUrl(data.header.logoImage),alt:data.settings.siteName,loading:'eager',decoding:'async'}) : h('strong',null,data.settings.siteName)), h('button',{className:'icon-btn',type:'button','aria-label':api.t('Close menu')||'Close menu',onClick:()=>setOpen(false)},icon('bi bi-x-lg'))), h('nav',{className:'mobile-nav','aria-label':api.t('Mobile navigation')||'Mobile navigation'},renderMenu(menu,true)), admin && h('button',{className:'btn-ghost w-100 mt-3',onClick:()=>{api.setAdminOpen(true);setOpen(false)}},'Admin settings'))
      ),document.body);
      return h(React.Fragment,null,
        h('header',{className:'site-header'}, h(Container,{api,className:'header-inner py-3 d-flex align-items-center justify-content-between'},
          h('a',{href:api.href('#home'),className:'brand-mark'}, data.header.logoImage ? h('img',{src:mediaUrl(data.header.logoImage),alt:data.settings.siteName,loading:'eager',decoding:'async',fetchPriority:'high'}) : h('span',{className:'brand-dot'}, data.settings?.branding?.placeholderMark||'LP'), h(E,{path:'header.logoText'})),
          h('nav',{className:'main-nav'}, renderMenu(menu)),
          h('div',{className:'nav-actions'}, h('button',{className:'icon-btn',onClick:()=>setModal({type:'search'}),'aria-label':'Search'},icon('bi bi-search')), languagePicker, h('button',{className:'icon-btn hamburger',onClick:()=>setOpen(true), 'aria-label':'Open menu'},icon('bi bi-list')))
        )),
        mobileMenu
      );
    }

    function HeroParticles(){
      const ref=useRef(null);
      useEffect(()=>{
        const canvas=ref.current;if(!canvas)return;
        const ctx=canvas.getContext('2d',{alpha:true});if(!ctx)return;
        const host=canvas.closest('.hero')||canvas;
        const reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let raf=0,w=0,hgt=0,dpr=1,nodes=[],brand='#0d6efd',brand2='#6610f2',gridStep=84;
        const pointer={x:0,y:0,active:false};
        const rand=(a,b)=>a+Math.random()*(b-a);
        const readTheme=()=>{const cs=getComputedStyle(canvas);brand=cs.getPropertyValue('--cms-brand').trim()||'#0d6efd';brand2=cs.getPropertyValue('--cms-brand2').trim()||'#6610f2';};
        const reset=()=>{
          const r=canvas.getBoundingClientRect();w=Math.max(1,r.width);hgt=Math.max(1,r.height);dpr=Math.min(window.devicePixelRatio||1,1.5);
          canvas.width=Math.round(w*dpr);canvas.height=Math.round(hgt*dpr);ctx.setTransform(dpr,0,0,dpr,0,0);readTheme();
          gridStep=Math.max(72,Math.min(96,w/14));
          const count=Math.max(34,Math.min(82,Math.round(w/22)));
          nodes=Array.from({length:count},(_,i)=>({
            x:rand(0,w),y:rand(0,hgt),vx:rand(-.11,.11),vy:rand(-.085,.085),
            r:i%10===0?rand(2.8,3.8):rand(1.15,2.15),phase:rand(0,Math.PI*2),hub:i%10===0
          }));
        };
        const stroke=(colour,alpha,width=1)=>{ctx.strokeStyle=colour;ctx.globalAlpha=alpha;ctx.lineWidth=width;};
        const fill=(colour,alpha)=>{ctx.fillStyle=colour;ctx.globalAlpha=alpha;};
        const drawGrid=()=>{
          ctx.save();stroke(brand,.027,.7);ctx.beginPath();
          for(let x=gridStep*.5;x<w;x+=gridStep){ctx.moveTo(x,0);ctx.lineTo(x,hgt)}
          for(let y=gridStep*.5;y<hgt;y+=gridStep){ctx.moveTo(0,y);ctx.lineTo(w,y)}
          ctx.stroke();ctx.restore();
        };
        const draw=(t=0)=>{
          raf=0;ctx.clearRect(0,0,w,hgt);drawGrid();
          const maxDist=Math.min(176,Math.max(118,w*.125));
          const mouseDist=Math.min(250,Math.max(190,w*.18));
          for(let i=0;i<nodes.length;i++){
            const a=nodes[i];
            if(!reduce){
              a.x+=a.vx+Math.sin(t*.00035+a.phase)*.012;a.y+=a.vy+Math.cos(t*.00031+a.phase)*.01;
              if(a.x<-24)a.x=w+24;if(a.x>w+24)a.x=-24;if(a.y<-24)a.y=hgt+24;if(a.y>hgt+24)a.y=-24;
            }
            for(let j=i+1;j<nodes.length;j++){
              const b=nodes[j],dx=a.x-b.x,dy=a.y-b.y,dist=Math.hypot(dx,dy);
              if(dist>=maxDist)continue;
              const proximity=1-dist/maxDist;
              stroke((a.hub||b.hub)?brand2:brand,.035+proximity*.16,.65+proximity*.42);
              ctx.beginPath();ctx.moveTo(a.x,a.y);ctx.lineTo(b.x,b.y);ctx.stroke();
              if(!reduce&&dist<maxDist*.78&&((i*11+j*7)%29===0)){
                const progress=(t*.000075+(i+j)*.071)%1;
                const px=a.x+(b.x-a.x)*progress,py=a.y+(b.y-a.y)*progress;
                fill(brand2,.24+proximity*.28);ctx.beginPath();ctx.arc(px,py,1.45,0,Math.PI*2);ctx.fill();
              }
            }
          }
          if(pointer.active){
            nodes.forEach(n=>{
              const dx=n.x-pointer.x,dy=n.y-pointer.y,dist=Math.hypot(dx,dy);if(dist>=mouseDist)return;
              const proximity=1-dist/mouseDist;
              stroke(n.hub?brand2:brand,.035+proximity*.24,.72+proximity*.55);ctx.beginPath();ctx.moveTo(n.x,n.y);ctx.lineTo(pointer.x,pointer.y);ctx.stroke();
            });
            stroke(brand,.13,1);ctx.beginPath();ctx.arc(pointer.x,pointer.y,9+Math.sin(t*.002)*1.5,0,Math.PI*2);ctx.stroke();
            fill(brand2,.22);ctx.beginPath();ctx.arc(pointer.x,pointer.y,1.8,0,Math.PI*2);ctx.fill();
          }
          nodes.forEach((n,i)=>{
            const pulse=reduce?0:(Math.sin(t*.0011+n.phase)+1)*.5;
            if(n.hub){
              stroke(brand2,.07+pulse*.075,1);ctx.beginPath();ctx.arc(n.x,n.y,n.r+5+pulse*4.5,0,Math.PI*2);ctx.stroke();
              stroke(brand,.055+pulse*.04,.8);ctx.beginPath();ctx.moveTo(n.x-8-pulse*2,n.y);ctx.lineTo(n.x-4,n.y);ctx.moveTo(n.x+4,n.y);ctx.lineTo(n.x+8+pulse*2,n.y);ctx.moveTo(n.x,n.y-8-pulse*2);ctx.lineTo(n.x,n.y-4);ctx.moveTo(n.x,n.y+4);ctx.lineTo(n.x,n.y+8+pulse*2);ctx.stroke();
            }
            fill(i%5===0?brand2:brand,n.hub?.58:.34);ctx.beginPath();ctx.arc(n.x,n.y,n.r+(n.hub?pulse*.28:0),0,Math.PI*2);ctx.fill();
          });
          ctx.globalAlpha=1;
          if(!reduce&&!document.hidden)raf=requestAnimationFrame(draw);
        };
        const redraw=()=>{cancelAnimationFrame(raf);reset();draw(0)};
        const onPointer=e=>{const r=canvas.getBoundingClientRect();pointer.x=e.clientX-r.left;pointer.y=e.clientY-r.top;pointer.active=pointer.x>=0&&pointer.y>=0&&pointer.x<=r.width&&pointer.y<=r.height};
        const onLeave=()=>{pointer.active=false};
        const onVisibility=()=>{cancelAnimationFrame(raf);raf=0;if(!document.hidden)draw(performance.now())};
        reset();draw(0);
        const ro=typeof ResizeObserver!=='undefined'?new ResizeObserver(redraw):null;if(ro)ro.observe(canvas);
        const onResize=()=>{if(!ro)redraw()};
        window.addEventListener('resize',onResize,{passive:true});host.addEventListener('pointermove',onPointer,{passive:true});host.addEventListener('pointerleave',onLeave,{passive:true});document.addEventListener('visibilitychange',onVisibility);
        return()=>{cancelAnimationFrame(raf);if(ro)ro.disconnect();window.removeEventListener('resize',onResize);host.removeEventListener('pointermove',onPointer);host.removeEventListener('pointerleave',onLeave);document.removeEventListener('visibilitychange',onVisibility)};
      },[]);
      return h('canvas',{ref,className:'hero-particles','aria-hidden':'true'});
    }

    function Hero({api}){
      const {data}=api; const slides=data.hero?.slides||[]; const offers=data.hero?.offers||[]; const promo=data.hero?.lpcmsPromo||{}; const E=api.Editable; const swiperEl=useRef(null);
      useEffect(()=>{
        const el=document.getElementById('home'); if(!el)return; let raf=0;
        const update=()=>{raf=0;const rect=el.getBoundingClientRect();const vh=window.innerHeight||800;const progress=Math.max(-1,Math.min(1,(vh/2-(rect.top+rect.height/2))/Math.max(vh,rect.height)));el.style.setProperty('--hero-parallax-y',(progress*34).toFixed(1)+'px');};
        const onScroll=()=>{if(!raf)raf=requestAnimationFrame(update)}; update(); window.addEventListener('scroll',onScroll,{passive:true}); window.addEventListener('resize',onScroll,{passive:true});
        return()=>{if(raf)cancelAnimationFrame(raf);window.removeEventListener('scroll',onScroll);window.removeEventListener('resize',onScroll)};
      },[]);
      useEffect(()=>{
        if(!swiperEl.current||!window.Swiper||!slides.length)return;
        const inst=new window.Swiper(swiperEl.current,{
          loop:slides.length>1,
          speed:760,
          grabCursor:slides.length>1,
          keyboard:{enabled:true},
          autoplay:slides.length>1?{delay:5600,disableOnInteraction:false,pauseOnMouseEnter:true}:false,
          pagination:{el:swiperEl.current.querySelector('.hero-swiper-pagination'),clickable:true},
          navigation:{nextEl:swiperEl.current.querySelector('.hero-swiper-next'),prevEl:swiperEl.current.querySelector('.hero-swiper-prev')},
          a11y:{enabled:true}
        });
        return()=>{try{inst.destroy(true,true)}catch(e){}};
      },[slides.length,api.lang]);
      function slideMedia(slide,i){
        const mediaType=String(slide.mediaType||((slide.video||'')?'video':'image')).toLowerCase(); const videoSrc=mediaUrl(slide.video||''); const poster=mediaUrl(slide.videoPoster||slide.image||'');
        if(mediaType==='video'&&videoSrc){
          return h('div',{className:'hero-card p-2 hero-media-card'},h('div',{className:'hero-video-wrap'},h('video',{className:'hero-video',src:videoSrc,poster,autoPlay:slide.videoAutoplay!==false,loop:slide.videoLoop!==false,muted:true,playsInline:true,preload:'metadata','aria-label':api.t('Website development workflow demo')})),h('div',{className:'hero-video-steps'},h('div',{className:'hero-video-step'},icon('bi bi-pencil-square'),h('span',null,api.t('Edit content'))),h('div',{className:'hero-video-step'},icon('bi bi-image'),h('span',null,api.t('Add images'))),h('div',{className:'hero-video-step'},icon('bi bi-grid'),h('span',null,api.t('Change icons'))),h('div',{className:'hero-video-step'},icon('bi bi-check2-circle'),h('span',null,api.t('Save & publish')))));
        }
        return h('div',{className:'hero-card p-2'},h(EditableImage,{api,path:`hero.slides.${i}.image`,alt:slide.title||'',className:'hero-img',eager:i===0}));
      }
      const offerGrid=offers.length?h('div',{className:'hero-offer-grid'},offers.map((o,i)=>{const external=/^https?:\/\//i.test(String(o.href||''));return h('a',{key:i,className:'hero-offer-card',href:o.href||'#services',target:external?'_blank':undefined,rel:external?'noopener noreferrer':undefined},h('span',{className:'hero-offer-icon'},icon(o.icon||'bi bi-arrow-up-right')),h('span',null,h('strong',null,api.t(o.title||'')),h('span',null,api.t(o.text||''))))})):null;
      const lpcmsPromo=promo.enabled!==false&&promo.title?h('aside',{className:'lpcms-hero-promo'},
        h('div',{className:'lpcms-promo-icon'},icon(promo.icon||'bi bi-lightning-charge-fill')),
        h('div',{className:'lpcms-promo-copy'},h(E,{path:'hero.lpcmsPromo.eyebrow',tag:'span',className:'lpcms-promo-eyebrow'}),h(E,{path:'hero.lpcmsPromo.title',tag:'strong'}),h(E,{path:'hero.lpcmsPromo.text',tag:'p'})),
        h('a',{className:'btn-cms lpcms-promo-btn',href:promo.href||'https://lpcms.dev4.click/',target:'_blank',rel:'noopener noreferrer'},h(E,{path:'hero.lpcmsPromo.buttonText'}),icon('bi bi-arrow-up-right'))
      ):null;
      return h('section',{id:'home',className:'hero section'},h('div',{className:'hero-parallax-bg','aria-hidden':'true'}),h(HeroParticles),h(Container,{api},
        h('div',{className:'swiper hero-swiper',ref:swiperEl},
          h('div',{className:'swiper-wrapper'},slides.map((slide,i)=>h('div',{className:'swiper-slide',key:i},
            h('div',{className:'hero-slide-grid'},
              h('div',{className:'hero-copy-col'},h('div',{className:'hero-slide-copy'},
                h('div',{className:'eyebrow text-primary fw-bold mb-3'},h(E,{path:`hero.slides.${i}.eyebrow`})),
                h(E,{path:`hero.slides.${i}.title`,tag:i===0?'h1':'h2',className:'hero-slide-title'}),
                h(E,{path:`hero.slides.${i}.text`,tag:'p',className:'my-4'}),
                h('a',{className:'btn-cms',href:slide.buttonHref||'#contact'},h(E,{path:`hero.slides.${i}.buttonText`}))
              )),
              h('div',{className:'hero-media-col'},slideMedia(slide,i))
            )
          ))),
          slides.length>1&&h('div',{className:'hero-swiper-controls'},
            h('button',{className:'hero-swiper-prev',type:'button','aria-label':api.t('Previous')||'Previous'},icon('bi bi-arrow-left')),
            h('div',{className:'hero-swiper-pagination'}),
            h('button',{className:'hero-swiper-next',type:'button','aria-label':api.t('Next')||'Next'},icon('bi bi-arrow-right'))
          )
        ),
        offerGrid,
        lpcmsPromo
      ));
    }

    function About({api}){ const d=api.data.about, E=api.Editable; return h('section',{id:'about',className:'section'}, h(Container,{api}, h('div',{className:'row align-items-center g-5'}, h('div',{className:'col-lg-6'}, h('div',{className:'about-team-visual-wrap'}, h(EditableImage,{api,path:'about.image',alt:d.title||'',className:'feature-panel about-team-visual'}), h('span',{className:'about-float-badge badge-wp'},icon('bi bi-wordpress'),' WordPress'), h('span',{className:'about-float-badge badge-react'},icon('bi bi-filetype-jsx'),' React'), h('span',{className:'about-float-badge badge-ai'},icon('bi bi-stars'),' AI workflows'))), h('div',{className:'col-lg-6'}, h(E,{path:'about.preheader',tag:'div',className:'eyebrow mb-2',placeholder:api.t('About')}), h(E,{path:'about.title',tag:'h2',className:'display-5'}), h(E,{path:'about.subtitle',tag:'p',className:'lead muted'}), h(E,{path:'about.body',tag:'div',html:true,className:'fs-5'}))))); }

    function Stats({api}){ const E=api.Editable; const items=api.data.stats?.items||[]; return h('section',{id:'stats',className:'section pt-0'}, h(Container,{api}, h(SectionTitle,{api,base:'stats'}), h('div',{className:'row g-4'}, items.map((it,i)=>h('div',{className:'col-6 col-lg-3',key:i}, h('div',{className:'cms-card stat-card'}, h(E,{path:`stats.items.${i}.number`,tag:'div',className:'stat-number'}), h(E,{path:`stats.items.${i}.label`,tag:'p',className:'muted mb-0'}))))))); }

    function FeatureBlocks({api}){ const E=api.Editable; const d=api.data.features||{}; return h('section',{id:'features',className:'section cms-soft'}, h(Container,{api}, h('div',{className:'row g-4 g-xl-5 align-items-center features-top-row'}, h('div',{className:'col-lg-5'}, h('div',{className:'eyebrow mb-2'},'Features'), h(E,{path:'features.title',tag:'h2',className:'display-5'}), h(E,{path:'features.subtitle',tag:'p',className:'lead muted mb-0'})), h('div',{className:'col-lg-7'}, h(EditableImage,{api,path:'features.image',alt:d.title||'',className:'feature-panel feature-panel-compact'}))), h('div',{className:'row g-3 g-xl-4 mt-2 feature-grid-row'}, (d.items||[]).map((it,i)=>h('div',{className:'col-md-6 col-xl-4',key:i}, h('article',{className:'cms-card feature-card-compact d-flex gap-3 h-100'}, h(EditableImage,{api,path:`features.items.${i}.image`,iconPath:`features.items.${i}.icon`,alt:it.title||'',className:'cms-icon flex-shrink-0 cms-image-icon',fallback:icon(it.icon,'bi bi-check2-circle')}), h('div',null, h(E,{path:`features.items.${i}.title`,tag:'h3',className:'h5'}), h(E,{path:`features.items.${i}.text`,tag:'p',className:'muted mb-0'})))))))); }

    function LoadMoreGrid({api,base,render,columns}){ const section=api.data[base]||{}; const startCount=Math.max(1,Number(section.homeLimit||section.batch||3)); const addCount=Math.max(1,Number(section.batch||startCount)); const items=section.items||[]; const [count,setCount]=useState(startCount); useEffect(()=>setCount(startCount),[base,startCount]); return h(React.Fragment,null, h('div',{className:'row g-4'}, items.slice(0,count).map((item,i)=>render(item,i,columns))), section.showButton && count<items.length && h('div',{className:'load-more-wrap'}, h('button',{className:'btn-cms',onClick:()=>setCount(c=>Math.min(items.length,c+addCount))}, api.localizedValue(base+'.buttonText')||'Load more'))); }

    function Services({api}){ const E=api.Editable; const serviceItems=api.data.services?.items||[]; return h('section',{id:'services',className:'section cms-soft'}, h(Container,{api}, h(SectionTitle,{api,base:'services'}), h(LoadMoreGrid,{api,base:'services',render:(it,i)=>{const external=/^https?:\/\//i.test(String(it.buttonHref||''));const wide=i===serviceItems.length-1;return h('div',{className:wide?'col-12 service-wide-card':'col-md-6 col-lg-4',key:i,'data-aos':'fade-up'}, h('article',{className:'cms-card'}, h(EditableImage,{api,path:`services.items.${i}.image`,iconPath:`services.items.${i}.icon`,alt:it.title||'',className:'cms-icon cms-image-icon',fallback:icon(it.icon,'bi bi-stars')}), h(E,{path:`services.items.${i}.title`,tag:'h3',className:'h4 fw-bold'}), h(E,{path:`services.items.${i}.text`,tag:'p',className:'muted'}), it.buttonText && h('a',{className:'btn-ghost mt-2',href:it.buttonHref||'#contact',target:external?'_blank':undefined,rel:external?'noopener noreferrer':undefined}, h(E,{path:`services.items.${i}.buttonText`})) ))}}))); }

    function WpThemes({api}){
      const d=api.data.wpThemes||{}, E=api.Editable, items=Array.isArray(d.items)?d.items:[], prices=d.webPrices||{}, priceItems=Array.isArray(prices.items)?prices.items:[], extras=Array.isArray(prices.extras)?prices.extras:[];
      const tech=[['bi bi-wordpress','WordPress'],['bi bi-bootstrap','Bootstrap'],['bi bi-grid-3x3-gap','Gutenberg'],['bi bi-cart3','WooCommerce']];
      const uk=String(api.lang||'').toLowerCase()==='en';
      const localeMap={en:'en-GB',lv:'lv-LV',ru:'ru-RU',fr:'fr-FR',es:'es-ES',de:'de-DE',pl:'pl-PL',lt:'lt-LT',et:'et-EE',da:'da-DK',no:'nb-NO',sv:'sv-SE',fi:'fi-FI',is:'is-IS'};
      const currency=uk?'GBP':'EUR';
      const formatPrice=item=>{const value=Number(uk?item?.gbp:item?.eur);if(!Number.isFinite(value))return '';try{return new Intl.NumberFormat(localeMap[api.lang]||undefined,{style:'currency',currency,maximumFractionDigits:0,minimumFractionDigits:0}).format(value)}catch(e){return (uk?'£':'€')+Math.round(value).toLocaleString()}};
      const unitLabel=unit=>unit==='hour'?'per hour':unit==='month'?'per month':'';
      const priceBlock=priceItems.length||extras.length?h('div',{className:'wp-web-prices'},
        h('div',{className:'wp-web-prices-head'},
          h(E,{path:'wpThemes.webPrices.preheader',tag:'div',className:'eyebrow mb-2',placeholder:api.t('Web development prices')}),
          h(E,{path:'wpThemes.webPrices.title',tag:'h3',className:'wp-web-prices-title'}),
          h(E,{path:'wpThemes.webPrices.subtitle',tag:'p',className:'muted wp-web-prices-subtitle'}),
          uk&&prices.ukNote&&h('div',{className:'wp-web-prices-note'},icon('bi bi-currency-pound'),h(E,{path:'wpThemes.webPrices.ukNote',tag:'span'}))
        ),
        priceItems.length&&h('div',{className:'wp-web-price-grid'},priceItems.map((it,i)=>h('article',{className:'wp-web-price-card',key:i},
          h('div',{className:'wp-web-price-icon'},icon(it.icon||'bi bi-check2-circle')),
          h(E,{path:`wpThemes.webPrices.items.${i}.title`,tag:'h4'}),
          h(E,{path:`wpThemes.webPrices.items.${i}.text`,tag:'p',className:'muted'}),
          h('div',{className:'wp-web-price-value'},h('span',null,'From'),h('strong',null,formatPrice(it)))
        ))),
        extras.length&&h('div',{className:'wp-web-price-extras'},extras.map((it,i)=>h('div',{className:'wp-web-price-row',key:i},
          h(E,{path:`wpThemes.webPrices.extras.${i}.title`,tag:'span',className:'wp-web-price-row-title'}),
          it.unit==='quote'?h('strong',null,it.price||'Quoted to scope'):h('span',{className:'wp-web-price-row-value'},h('small',null,'From'),h('strong',null,formatPrice(it)),unitLabel(it.unit)&&h('small',null,unitLabel(it.unit)))
        )))
      ):null;
      return h('section',{id:'wp-themes',className:'section wp-themes-section'},h(Container,{api},
        h('div',{className:'wp-themes-head'},
          h(SectionTitle,{api,base:'wpThemes'}),
          h('div',{className:'wp-themes-tech','aria-label':api.t('WordPress technology stack')},tech.map(([ic,label])=>h('span',{className:'wp-tech-pill',key:label},icon(ic),label)))
        ),
        h('div',{className:'wp-themes-layout'},
          h('div',{className:'wp-themes-media-card'},
            h(EditableImage,{api,path:'wpThemes.image',alt:api.localizedValue('wpThemes.alt')||api.localizedValue('wpThemes.title')||'WordPress themes',className:'wp-themes-image-frame',hideWhenEmpty:true}),
            d.badge&&h('div',{className:'wp-themes-media-badge'},icon('bi bi-wordpress'),h(E,{path:'wpThemes.badge'}))
          ),
          h('div',null,
            h('div',{className:'wp-themes-benefits'},items.map((it,i)=>h('article',{className:'wp-theme-benefit',key:i},
              h('div',{className:'wp-theme-benefit-icon'},icon(it.icon||'bi bi-check2-circle')),
              h(E,{path:`wpThemes.items.${i}.title`,tag:'h3'}),
              h(E,{path:`wpThemes.items.${i}.text`,tag:'p',className:'muted mb-0'})
            ))),
            h('div',{className:'wp-themes-note'},
              h(E,{path:'wpThemes.noteTitle',tag:'strong'}),
              h(E,{path:'wpThemes.noteText',tag:'p',className:'muted'})
            ),
            d.buttonText&&h('div',{className:'wp-themes-cta'},h('a',{className:'btn-cms',href:d.buttonHref||'#contact'},h(E,{path:'wpThemes.buttonText'})),h('span',{className:'small muted'},api.t('Professional WordPress delivery for business and commerce.')))
          )
        ),
        priceBlock
      ));
    }

    function Gallery({api}){
      const cfg=api.data.gallery||{}, E=api.Editable, items=cfg.items||[], categories=cfg.categories||[];
      const [cat,setCat]=useState('all'); const startCount=Math.max(1,Number(cfg.homeLimit||9)); const batch=Math.max(1,Number(cfg.batch||3)); const [count,setCount]=useState(startCount);
      useEffect(()=>setCount(startCount),[cat,startCount,items.length]);
      const filtered=cat==='all'?items:items.filter(it=>it.category===cat);
      const categoryTitle=slug=>{const c=categories.find(x=>x.slug===slug);return c?api.t(c.title||slug):api.t(slug||'');};
      return h('section',{id:'gallery',className:'section'}, h(Container,{api},
        h(SectionTitle,{api,base:'gallery'}),
        h('div',{className:'portfolio-toolbar','aria-label':api.t('Portfolio filters')},categories.map((c,i)=>h('button',{key:c.slug||i,type:'button',className:cls('portfolio-filter',(c.slug||'all')===cat&&'active'),onClick:()=>setCat(c.slug||'all')},api.t(c.title||c.slug||'All work')))),
        h('div',{className:'portfolio-count'},filtered.length,' ',api.t(filtered.length===1?'project':'projects')),
        h('div',{className:'row g-4'},filtered.slice(0,count).map(it=>{const i=items.indexOf(it);return h('div',{className:'col-md-6 col-lg-4',key:it.slug||i},h('article',{className:'cms-card portfolio-card'},h('div',{className:'portfolio-image-wrap'},h(EditableImage,{api,path:`gallery.items.${i}.image`,alt:api.localizedValue(`gallery.items.${i}.alt`)||it.alt||it.title||'',className:'portfolio-image',target:'gallery',errorSrc:it.fallbackImage||''})),h('div',{className:'portfolio-card-body'},h('div',{className:'portfolio-meta'},h('span',{className:'portfolio-kind'},categoryTitle(it.category)||api.t(it.kind||'')),h('span',{className:cls('portfolio-status',String(it.status||'').toLowerCase().includes('development')&&'is-development')},api.t(it.status||'Live project'))),h(E,{path:`gallery.items.${i}.title`,tag:'h3',className:'h4 fw-bold'}),h(E,{path:`gallery.items.${i}.text`,tag:'p',className:'muted mb-0'}),Array.isArray(it.tech)&&h('div',{className:'tech-chips'},it.tech.map((t,j)=>h('span',{key:j,className:'tech-chip'},t))),h('div',{className:'portfolio-actions'},h('button',{type:'button',className:'btn-ghost',onClick:()=>api.setModal({type:'item',group:'gallery',index:i})},api.t('View project')),it.url&&/^https:\/\//i.test(String(it.url))&&h('a',{className:'btn-cms portfolio-visit-link',href:it.url,target:'_blank',rel:'noopener noreferrer',title:api.t('Open app in a new tab')+': '+(api.localizedValue(`gallery.items.${i}.title`)||it.title)},icon('bi bi-box-arrow-up-right'),api.t(it.linkText||'Open app'))))))})),
        cfg.showButton&&count<filtered.length&&h('div',{className:'load-more-wrap'},h('button',{className:'btn-cms',onClick:()=>setCount(c=>Math.min(filtered.length,c+batch))},api.localizedValue('gallery.buttonText')||api.t('Load more projects')))
      ));
    }

    function DigitalPulseApps({api}){
      const cfg=api.data.apps||{};
      const E=api.Editable;
      const items=Array.isArray(cfg.items)?cfg.items:[];
      return h('section',{id:'apps',className:'section digitalpulse-apps-section'},h(Container,{api},
        h('div',{className:'digitalpulse-apps-heading'},
          h(SectionTitle,{api,base:'apps'}),
          h('span',{className:'digitalpulse-apps-count'},icon('bi bi-grid-3x3-gap'),items.length===17?api.t('17 apps'):String(items.length))
        ),
        h('div',{className:'digitalpulse-apps-grid'},items.map((app,i)=>{
          // Disallow script/data URLs in content-managed external links.
          const url=/^https:\/\/[^\s]+$/i.test(String(app.url||''))?String(app.url):'#apps';
          const name=api.localizedValue(`apps.items.${i}.title`)||app.title||'App';
          return h('a',{key:app.slug||i,className:'digitalpulse-app-tile',href:url,target:url==='#apps'?undefined:'_blank',rel:url==='#apps'?undefined:'noopener noreferrer',
            'aria-label':api.t('Open app in a new tab')+': '+name,
            onClick:api.admin?e=>{e.preventDefault()}:undefined},
            h('span',{className:'digitalpulse-app-icon','aria-hidden':'true'},icon(app.icon||'bi bi-app-indicator')),
            h('span',{className:'digitalpulse-app-content'},
              h(E,{path:`apps.items.${i}.title`,tag:'strong',className:'digitalpulse-app-name'}),
              h(E,{path:`apps.items.${i}.text`,tag:'span',className:'digitalpulse-app-description'})
            ),
            h('span',{className:'digitalpulse-app-arrow','aria-hidden':'true'},icon('bi bi-arrow-up-right'))
          );
        }))
      ));
    }

    function Testimonials({api}){ const E=api.Editable; return h('section',{id:'testimonials',className:'section cms-soft'}, h(Container,{api}, h(SectionTitle,{api,base:'testimonials'}), h(LoadMoreGrid,{api,base:'testimonials',render:(it,i)=>h('div',{className:'col-md-6 col-lg-4',key:i}, h('article',{className:'cms-card'}, h('div',{className:'d-flex align-items-center gap-3 mb-3'}, h(EditableImage,{api,path:`testimonials.items.${i}.photo`,alt:it.name||'',className:'avatar cms-image-avatar',fallback:icon('bi bi-person')}), h('div',null,h(E,{path:`testimonials.items.${i}.name`,tag:'strong'}), h(E,{path:`testimonials.items.${i}.role`,tag:'div',className:'small muted'}))), h(E,{path:`testimonials.items.${i}.text`,tag:'p',className:'mb-0'}))) }))); }

    function FAQ({api}){
      const E=api.Editable;
      const [open,setOpen]=useState(0);
      const items=api.data.faq?.items||[];
      return h('section',{id:'faq',className:'section'},
        h(Container,{api},
          h(SectionTitle,{api,base:'faq'}),
          h('div',{className:'mx-auto faq-list',style:{maxWidth:860}},
            ...items.map((it,i)=>{
              const isOpen=open===i;
              const answerId='faq-answer-'+i;
              return h('div',{className:cls('faq-item',isOpen&&'is-open'),key:i},
                h('button',{type:'button',className:'faq-q','aria-expanded':isOpen?'true':'false','aria-controls':answerId,onClick:()=>setOpen(isOpen?null:i)},
                  h('span',{className:'faq-q-text'},h(E,{path:`faq.items.${i}.question`})),
                  h('span',{className:'faq-toggle','aria-hidden':'true'},isOpen?'−':'+')
                ),
                isOpen?h('div',{id:answerId,className:'faq-a'},h(E,{path:`faq.items.${i}.answer`,tag:'div',html:true})):null
              );
            })
          )
        )
      );
    }

    function Process({api}){ const E=api.Editable; return h('section',{id:'process',className:'section'}, h(Container,{api}, h(SectionTitle,{api,base:'process'}), h('div',{className:'row justify-content-center'}, h('div',{className:'col-lg-8'}, (api.data.process?.items||[]).map((it,i)=>h('article',{className:'timeline-step d-flex gap-3 mb-4',key:i}, h(EditableImage,{api,path:`process.items.${i}.image`,iconPath:`process.items.${i}.icon`,alt:it.title||'',className:'cms-icon flex-shrink-0 cms-image-icon',fallback:icon(it.icon,'bi bi-check')}), h('div',{className:'cms-card flex-fill'}, h('div',{className:'small muted mb-1'},'0'+(i+1)), h(E,{path:`process.items.${i}.title`,tag:'h3',className:'h4'}), h(E,{path:`process.items.${i}.text`,tag:'p',className:'muted mb-0'})))))))); }

    function ImageCards({api}){
      const d=api.data.cards||{}, E=api.Editable;
      const cols=Math.max(2,Math.min(4,Number(d.columns||3)));
      return h('section',{id:'cards',className:'section'},
        h(Container,{api},
          h(SectionTitle,{api,base:'cards'}),
          h('div',{className:'row g-4'},(d.items||[]).map((it,i)=>{
            const external=/^https?:\/\//i.test(String(it.buttonHref||''));
            return h('div',{className:`col-md-6 col-lg-${12/cols}`,key:i},
              h('article',{className:'cms-card'},
                h(EditableImage,{api,path:`cards.items.${i}.image`,iconPath:`cards.items.${i}.icon`,alt:it.title||'',className:it.image?'image-card-media mb-3':'cms-icon cms-image-icon mb-3',fallback:icon(it.icon||'lucide:layout-template')}),
                h(E,{path:`cards.items.${i}.title`,tag:'h3',className:'h5'}),
                h(E,{path:`cards.items.${i}.text`,tag:'p',className:'muted'}),
                it.buttonText&&h('a',{className:'btn-ghost',href:it.buttonHref||'#contact',target:external?'_blank':undefined,rel:external?'noopener noreferrer':undefined},h(E,{path:`cards.items.${i}.buttonText`}))
              )
            );
          }))
        )
      );
    }

    function ContentList({api}){
      const d=api.data.contentList||{}, E=api.Editable; const items=d.items||[]; const [open,setOpen]=useState(false);
      function openEncoded(encoded){ if(!encoded)return; try{const url=atob(String(encoded));if(!/^https?:\/\//i.test(url))return;const win=window.open(url,'_blank','noopener,noreferrer');if(win)win.opener=null;}catch(e){} }
      return h('section',{id:'content-list',className:'section cms-soft','data-nosnippet':''},h(Container,{api},
        h(SectionTitle,{api,base:'contentList'}),
        h('div',{className:'agency-exp-accordion','data-nosnippet':''},
          h('button',{type:'button',className:'agency-exp-toggle','aria-expanded':open,onClick:()=>setOpen(v=>!v)},
            h('span',null,api.t(d.accordionLabel||'View selected UK agency projects'),h('span',{className:'agency-exp-count'},items.length-1,'+')),
            icon(open?'bi bi-chevron-up':'bi bi-chevron-down')
          ),
          open&&h('div',{className:'agency-exp-panel','data-nosnippet':''},
            h('p',{className:'agency-exp-note'},api.t(d.privacyNote||'')),
            h('div',{className:'agency-exp-grid'},items.map((it,i)=>h('button',{type:'button',key:i,className:cls('agency-exp-item',it.urlEncoded&&'can-open'),onClick:it.urlEncoded?()=>openEncoded(it.urlEncoded):undefined,'aria-label':it.urlEncoded?(api.t('Open')+' '+(it.title||'')):undefined},
              h('span',null,h(E,{path:`contentList.items.${i}.title`,tag:'strong'}),h(E,{path:`contentList.items.${i}.text`,tag:'small'})),
              icon(it.urlEncoded?'bi bi-box-arrow-up-right':'bi bi-plus-circle')
            )))
          )
        )
      ));
    }

    function GrowthMarketing({api}){
      const d=api.data.growth||{}, E=api.Editable; const items=d.items||[];
      return h('section',{id:'growth',className:'section growth-section'},h(Container,{api},
        h(SectionTitle,{api,base:'growth'}),
        h('div',{className:'row g-4 growth-grid'},items.map((it,i)=>h('div',{className:'col-md-6 col-xl-4',key:i},
          h('article',{className:'cms-card growth-card h-100'},
            h('span',{className:'growth-icon'},icon(it.icon||'bi bi-graph-up-arrow')),
            h(E,{path:`growth.items.${i}.title`,tag:'h3',className:'h5 fw-bold'}),
            h(E,{path:`growth.items.${i}.text`,tag:'p',className:'muted mb-0'}),
            Array.isArray(it.points)&&it.points.length?h('ul',{className:'growth-points'},it.points.map((point,j)=>h('li',{key:j},icon('bi bi-check2'),h(E,{path:`growth.items.${i}.points.${j}`})))):null
          )
        ))),
        h('div',{className:'growth-note cms-card'},h('div',null,h(E,{path:'growth.noteTitle',tag:'strong'}),h(E,{path:'growth.noteText',tag:'p',className:'muted mb-0'})),h('a',{className:'btn-cms',href:'#contact'},h(E,{path:'growth.buttonText'})))
      ));
    }

    function Industries({api}){
      const d=api.data.industries||{}, E=api.Editable; const items=d.items||[];
      return h('section',{id:'industries',className:'section cms-soft industries-section'},h(Container,{api},
        h(SectionTitle,{api,base:'industries'}),
        h('div',{className:'industry-grid'},items.map((it,i)=>h('article',{className:'cms-card industry-card',key:i},
          h('div',{className:'industry-card-top'},h('span',{className:'industry-icon'},icon(it.icon||'bi bi-buildings')),h(E,{path:`industries.items.${i}.title`,tag:'h3',className:'h5 fw-bold mb-0'})),
          h(E,{path:`industries.items.${i}.text`,tag:'p',className:'muted'}),
          Array.isArray(it.solutions)&&it.solutions.length?h('div',{className:'industry-solutions'},it.solutions.map((x,j)=>h('span',{key:j},x))):null
        ))),
        h('div',{className:'industries-footer'},h(E,{path:'industries.footerText',tag:'p',className:'mb-0'}),h('a',{className:'btn-ghost',href:'#contact'},h(E,{path:'industries.buttonText'})))
      ));
    }

    function TeamLead({api}){
      const d=api.data.team||{}, E=api.Editable;
      const [open,setOpen]=useState(0);
      const [visiblePortfolio,setVisiblePortfolio]=useState(6);
      const gallery=api.data.gallery?.items||[];
      const projectsFor=it=>{
        const references=Array.isArray(it.portfolioSlugs)&&it.portfolioSlugs.length?it.portfolioSlugs:(it.portfolioIndexes||[]);
        const seen=new Set();
        return references.map(ref=>{
          const idx=typeof ref==='number'?ref:gallery.findIndex(p=>p.slug===ref);
          return {it:gallery[idx],idx};
        }).filter(({it,idx})=>{
          if(!it||idx<0||seen.has(idx))return false;
          seen.add(idx);return true;
        });
      };
      return h('section',{id:'team',className:'section'},h(Container,{api},
        h(SectionTitle,{api,base:'team'}),
        h('div',{className:'team-lead-shell'},
          h('aside',{className:'team-lead-profile cms-card'},
            h('div',{className:'team-photo-wrap'},h(EditableImage,{api,path:'team.photo',alt:d.name||'Team lead',className:'team-photo'})),
            h(E,{path:'team.name',tag:'h3',className:'h3 mb-1'}),
            h(E,{path:'team.role',tag:'div',className:'team-role'}),
            h(E,{path:'team.summary',tag:'p',className:'muted team-summary'}),
            h('div',{className:'team-tech-chips'},(d.technologies||[]).map((tech,i)=>h('span',{key:i},tech))),
            d.linkedin&&h('a',{className:'btn-cms team-linkedin',href:d.linkedin,target:'_blank',rel:'noopener noreferrer'},icon('bi bi-linkedin'),h(E,{path:'team.buttonText'}))
          ),
          h('div',{className:'team-accordion'},(d.items||[]).map((it,i)=>{
            const isOpen=open===i;
            const projects=it.type==='portfolio'?projectsFor(it):[];
            const agencies=it.type==='agency-portfolio'?(it.agencyItems||[]):[];
            const contentId=`dp-team-accordion-panel-${i}`;
            return h('article',{className:cls('team-accordion-item',isOpen&&'open'),key:i},
              h('button',{type:'button',className:'team-accordion-toggle','aria-expanded':isOpen,'aria-controls':contentId,onClick:()=>{setOpen(isOpen?null:i);setVisiblePortfolio(6);}},
                h('span',{className:'team-accordion-icon'},icon(it.icon||'bi bi-plus-circle')),
                h(E,{path:`team.items.${i}.title`,tag:'strong'}),
                icon(isOpen?'bi bi-dash-lg':'bi bi-plus-lg')
              ),
              isOpen&&h('div',{id:contentId,className:'team-accordion-body',...(agencies.length?{'data-nosnippet':''}:{})},
                h(E,{path:`team.items.${i}.text`,tag:'div',html:true,className:agencies.length?'team-agency-note':'muted'}),
                agencies.length>0&&h('div',{className:'team-agency-grid'},agencies.map((agency,j)=>{const canOpen=!!agency.urlEncoded;return h('button',{type:'button',key:j,className:cls('team-agency-btn',canOpen&&'can-open'),disabled:!canOpen,onClick:()=>{if(!canOpen)return;try{const url=window.atob(agency.urlEncoded);window.open(url,'_blank','noopener,noreferrer')}catch(e){}}},h('span',null,agency.title),icon(canOpen?'bi bi-box-arrow-up-right':'bi bi-plus-circle'));})),
                projects.length>0&&h(React.Fragment,null,
                  h('div',{className:'team-mini-portfolio'},projects.slice(0,visiblePortfolio).map(({it:p,idx})=>h('button',{type:'button',className:'team-mini-project',key:p.slug||idx,'aria-label':api.localizedValue(`gallery.items.${idx}.title`)||p.title,onClick:()=>api.setModal({type:'item',group:'gallery',index:idx})},
                    h('div',{className:'team-mini-project-media'},
                      h('img',{src:mediaUrl(p.image),alt:api.localizedValue(`gallery.items.${idx}.alt`)||p.title||'',loading:'lazy',decoding:'async',fetchPriority:'low',width:1200,height:900,onError:p.fallbackImage?e=>{const img=e.currentTarget;if(!img.dataset.cmsImageFallback){img.dataset.cmsImageFallback='1';img.src=mediaUrl(p.fallbackImage);}}:undefined}),
                      String(p.status||'').toLowerCase().includes('development')&&h('span',{className:'team-mini-project-status'},api.t('In development'))
                    ),
                    h('span',{className:'team-mini-project-copy'},
                      h('strong',null,api.localizedValue(`gallery.items.${idx}.title`)||p.title),
                      h('small',null,api.localizedValue(`gallery.items.${idx}.kind`)||p.kind||p.category||''),
                      h('span',{className:'team-mini-project-arrow','aria-hidden':'true'},icon('bi bi-arrow-up-right'))
                    )
                  ))),
                  h('div',{className:'team-mini-project-actions'},
                    projects.length>visiblePortfolio&&h('button',{type:'button',className:'btn-ghost team-project-more',onClick:()=>setVisiblePortfolio(v=>Math.min(v+6,projects.length))},
                      api.localizedValue(`team.items.${i}.moreLabel`)||'Show more projects',
                      h('span',{className:'team-mini-project-count'},`${Math.min(visiblePortfolio,projects.length)} / ${projects.length}`),
                      icon('bi bi-chevron-down')
                    ),
                    h('a',{className:'team-project-all',href:'#apps'},api.localizedValue(`team.items.${i}.allAppsLabel`)||'Explore all apps',icon('bi bi-arrow-right'))
                  )
                )
              )
            );
          }))
        )
      ));
    }

    function LogoStrip({api}){
      const d=api.data.logos||{}, E=api.Editable, items=d.items||[];
      const group=(copyKey)=>h('div',{className:'tech-marquee-group','aria-hidden':copyKey==='copy'?true:undefined,key:copyKey},items.map((it,i)=>h('a',{className:'tech-pill',href:it.href||'#features',key:copyKey+'-'+i,tabIndex:copyKey==='copy'?-1:undefined},it.icon?icon(it.icon):icon('bi bi-code-slash'),h(E,{path:`logos.items.${i}.label`,tag:'span'}))));
      return h('section',{id:'logos',className:'section tech-stack-strip'},h(Container,{api},
        h('div',{className:'tech-stack-head'},
          h('div',null,h(E,{path:'logos.preheader',tag:'div',className:'eyebrow'}),h(E,{path:'logos.title',tag:'h2'})),
          h(E,{path:'logos.subtitle',tag:'p'})
        ),
        h('div',{className:'tech-swiper tech-marquee','aria-label':api.localizedValue('logos.title')||'Technology stack'},
          h('div',{className:'tech-marquee-track'},group('main'),group('copy'))
        )
      ));
    }
    function ProductPrice({api,item,path,variation=null}){ const price=api.productPriceValue(item,variation); const old=api.productOldPriceValue(item,variation); return h('div',{className:'d-flex align-items-center gap-2 flex-wrap mb-3'}, h('span',{className:'product-price'}, api.formatProductPrice(item,variation)), old>price&&price>0&&h('span',{className:'old-price'}, new Intl.NumberFormat(undefined,{style:'currency',currency:variation?.currency||item.currency||api.data.products?.currency||'EUR'}).format(old)), item.discountLabel&&h('span',{className:'discount-badge'},item.discountLabel)); }
    function ProductButtons({api,item,variation=null,qty=1}){ const price=api.productPriceValue(item,variation); const cfg=api.data.products||{}; const vars=api.productVariationList(item); const needsChoice=vars.length>0 && !variation; const mode=price<=0?'quote':String(item.purchaseMode||cfg.defaultPurchaseMode||'quote'); const showBuy=mode==='checkout'&&cfg.showBuyButton&&item.buyButton!==false&&price>0; const showQuote=mode==='quote'&&cfg.showQuoteButton&&item.quoteButton!==false; const direct=String(item.checkoutUrl||'').trim(); const idx=(cfg.items||[]).indexOf(item); const itemButton=idx>=0?api.localizedValue(`products.items.${idx}.checkoutButtonText`):''; const buyLabel=itemButton||api.localizedValue('products.buyButtonText')||'Checkout now'; const quoteLabel=api.localizedValue('products.quoteButtonText')||'Add to quote'; return h('div',{className:'product-actions'}, showBuy&&(direct&&!needsChoice?h('a',{className:'btn-cms',href:direct,target:'_blank',rel:'noopener noreferrer'},buyLabel):h('button',{className:'btn-cms',onClick:()=>needsChoice?api.setModal({type:'item',group:'products',index:idx}):api.addToCart(item,variation,qty)},buyLabel)), showQuote&&h('button',{className:'btn-cms',onClick:()=>api.addToQuote(item,variation,qty)},quoteLabel)); }
    function isColourKey(key){ return ['colour','color','colours','colors','tone','shade'].includes(String(key||'').toLowerCase()); }
    function colourValueCss(value){ const raw=String(value||'').trim(); const key=raw.toLowerCase().replace(/[^a-z0-9#]/g,''); if(/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i.test(raw)) return raw; const map={black:'#111827',white:'#ffffff',blue:'#2563eb',navy:'#1e3a8a',sky:'#38bdf8',red:'#ef4444',green:'#22c55e',lime:'#84cc16',yellow:'#facc15',orange:'#f97316',purple:'#8b5cf6',violet:'#7c3aed',pink:'#ec4899',rose:'#f43f5e',brown:'#92400e',beige:'#d6b98c',cream:'#fff7ed',gray:'#9ca3af',grey:'#9ca3af',silver:'#cbd5e1',gold:'#f59e0b'}; if(map[key]) return map[key]; const parts=key.match(/black|white|blue|navy|sky|red|green|lime|yellow|orange|purple|violet|pink|rose|brown|beige|cream|gray|grey|silver|gold/g); if(parts&&parts.length){ const colors=parts.map(x=>map[x]||x); return colors.length>1 ? `linear-gradient(135deg,${colors.join(',')})` : colors[0]; } let hash=0; for(let i=0;i<raw.length;i++) hash=(hash*31+raw.charCodeAt(i))>>>0; return `hsl(${hash%360} 72% 52%)`; }
    function ColourSwatch({value,selected=false,className='',title=''}){ const v=String(value||'').trim(); const low=v.toLowerCase(); return h('span',{className:cls('colour-swatch',(low==='white'||low==='cream')&&'is-white',low.includes('light')&&'is-light',selected&&'active',className),style:{background:colourValueCss(v)},title:title||v,'aria-label':title||v}, h('span',{className:'visually-hidden'},title||v)); }
    function ProductFilterDropdown({label,active,children,onClear}){ const [open,setOpen]=useState(false); const ref=useRef(null); useEffect(()=>{ if(!open) return; const close=e=>{ if(ref.current&&!ref.current.contains(e.target)) setOpen(false); }; document.addEventListener('mousedown',close); return()=>document.removeEventListener('mousedown',close); },[open]); return h('div',{className:'filter-dropdown',ref}, h('button',{type:'button',className:cls('filter-trigger',open&&'open',active&&'active'),onClick:()=>setOpen(!open),'aria-expanded':open}, h('span',null,label), active&&h('strong',null,active), icon('bi bi-chevron-down')), open&&h('div',{className:'filter-panel'}, h('div',{className:'d-flex align-items-center justify-content-between mb-2'}, h('div',{className:'filter-panel-title'},label), onClear&&h('button',{type:'button',className:'filter-clear',onClick:()=>{onClear();setOpen(false);}},'Clear')), children)); }
    function CategoryFilter({categories,value,onChange}){ const active=value!=='all' ? ((categories.find(c=>c.slug===value)||{}).title||value) : ''; return h(ProductFilterDropdown,{label:'Category',active,onClear:()=>onChange('all')}, h('div',{className:'filter-option-list'}, h('button',{type:'button',className:cls('filter-option',value==='all'&&'active'),onClick:()=>onChange('all')}, h('span',null,'All categories'), value==='all'&&icon('bi bi-check2')), categories.map(c=>h('button',{type:'button',key:c.slug,className:cls('filter-option',value===c.slug&&'active'),onClick:()=>onChange(c.slug)}, h('span',null,c.title||c.slug), value===c.slug&&icon('bi bi-check2'))))); }
    function AttributeFilter({def,value,onChange}){ const opts=def.options||[]; if(isColourKey(def.key)) return h(ProductFilterDropdown,{label:def.label||'Colour',active:value?h('span',{className:'filter-colour-inline'},h(ColourSwatch,{value}),value):'',onClear:()=>onChange('')}, h('div',{className:'swatch-grid'}, h('button',{type:'button',className:cls('swatch-option is-all',!value&&'active'),onClick:()=>onChange('')}, 'All'), opts.map(o=>h('button',{type:'button',key:o,className:cls('swatch-option',value===o&&'active'),title:o,'aria-label':o,onClick:()=>onChange(o)}, h(ColourSwatch,{value:o,selected:value===o}), h('span',null,o))))); return h(ProductFilterDropdown,{label:def.label||def.key,active:value||'',onClear:()=>onChange('')}, h('div',{className:'filter-option-list'}, h('button',{type:'button',className:cls('filter-option',!value&&'active'),onClick:()=>onChange('')}, h('span',null,'All'), !value&&icon('bi bi-check2')), opts.map(o=>h('button',{type:'button',key:o,className:cls('filter-option',value===o&&'active'),onClick:()=>onChange(o)}, h('span',null,o), value===o&&icon('bi bi-check2'))))); }
    function PriceRangeFilter({api,items,value,onChange}){ const prices=useMemo(()=>{ const vals=[]; (items||[]).forEach(p=>{ const base=api.productPriceValue(p); if(base>0) vals.push(base); (api.productVariationList(p)||[]).forEach(v=>{ const n=api.productPriceValue(p,v); if(n>0) vals.push(n); }); }); return vals; },[items]); if(!prices.length) return null; const cur=api.data.products?.currency||'EUR'; const minLimit=Math.floor(Math.min(...prices)); const maxLimit=Math.ceil(Math.max(...prices)); const minVal=value.min===''?minLimit:Number(value.min); const maxVal=value.max===''?maxLimit:Number(value.max); const money=n=>new Intl.NumberFormat(undefined,{style:'currency',currency:cur,maximumFractionDigits:0}).format(Number(n)||0); const active=(value.min!==''||value.max!=='') ? `${money(minVal)} – ${money(maxVal)}` : ''; function setMin(v){ const n=Math.min(Number(v), maxVal); onChange({min:n,max:value.max===''?'':maxVal}); } function setMax(v){ const n=Math.max(Number(v), minVal); onChange({min:value.min===''?'':minVal,max:n}); } return h(ProductFilterDropdown,{label:'Price',active,onClear:()=>onChange({min:'',max:''})}, h('div',{className:'price-filter'}, h('div',{className:'price-filter-fields'}, h('label',null,'Min', h('input',{className:'form-control',type:'number',min:minLimit,max:maxLimit,value:minVal,onChange:e=>setMin(e.target.value)})), h('label',null,'Max', h('input',{className:'form-control',type:'number',min:minLimit,max:maxLimit,value:maxVal,onChange:e=>setMax(e.target.value)}))), h('div',{className:'range-stack'}, h('input',{type:'range',min:minLimit,max:maxLimit,value:minVal,onChange:e=>setMin(e.target.value)}), h('input',{type:'range',min:minLimit,max:maxLimit,value:maxVal,onChange:e=>setMax(e.target.value)})), h('div',{className:'price-label-row'}, h('span',null,money(minLimit)), h('span',null,money(maxLimit))))); }
    function ActiveFilterChips({q,cat,categories,attrs,price,setQ,setCat,setAttrs,setPrice}){ const chips=[]; if(q) chips.push(['Search',q,()=>setQ('')]); if(cat!=='all') chips.push(['Category',(categories.find(c=>c.slug===cat)||{}).title||cat,()=>setCat('all')]); Object.entries(attrs).forEach(([k,v])=>{ if(v) chips.push([k,v,()=>setAttrs({...attrs,[k]:''})]); }); if(price.min!==''||price.max!=='') chips.push(['Price',`${price.min||'min'} – ${price.max||'max'}`,()=>setPrice({min:'',max:''})]); if(!chips.length) return null; return h('div',{className:'active-filter-chips'}, chips.map(([k,v,clear],i)=>h('span',{className:'active-filter-chip',key:i}, h('strong',null,k+':'), String(v), h('button',{type:'button',onClick:clear,'aria-label':'Remove filter'},'×')))); }
    function VariationChoice({api,item,variation,active,onClick}){ const attrs=variation?.attributes||{}; const colourKey=Object.keys(attrs).find(isColourKey); const other=Object.entries(attrs).filter(([k])=>k!==colourKey).map(([k,v])=>v).join(' / '); return h('button',{type:'button',className:cls('variation-choice',active&&'active'),onClick,title:api.variationLabel(variation)}, colourKey&&h(ColourSwatch,{value:attrs[colourKey],selected:active,title:attrs[colourKey]}), h('small',null, other || variation.sku || variation.id || 'Option'), h('small',null,api.formatProductPrice(item,variation))); }
    function ProductBadges({item}){ const attrs=item.attributes||{}; return h('div',{className:'product-badges'}, item.id&&h('span',null,'ID ',item.id), item.sku&&h('span',null,item.sku), item.category&&h('span',null,item.category), Object.keys(attrs).slice(0,3).map(k=>isColourKey(k)?h('span',{key:k,className:'colour-badge',title:`${k}: ${attrs[k]}`},h(ColourSwatch,{value:attrs[k],title:`${k}: ${attrs[k]}`})):h('span',{key:k},k,': ',attrs[k]))); }
    function ProductCard({api,it,i}){ const E=api.Editable; return h('div',{className:'col-12 col-md-6 col-xl-3',key:i,'data-aos':'fade-up'}, h('article',{className:'cms-card product-card'}, h(EditableImage,{api,path:`products.items.${i}.image`,fallbackSrc:it.gallery?.[0]||'',alt:it.title||'',className:'product-card-img mb-3'}), it.discountLabel&&h('span',{className:'discount-badge mb-2'},h(E,{path:`products.items.${i}.discountLabel`})), h(E,{path:`products.items.${i}.title`,tag:'h3',className:'h5 fw-bold'}), h(ProductBadges,{item:it}), h(ProductPrice,{api,item:it,path:`products.items.${i}`}), h(E,{path:`products.items.${i}.text`,tag:'p',className:'muted'}), h('div',{className:'product-actions'}, h('a',{className:'btn-ghost',href:pretty('products',it),onClick:e=>{ if(!e.metaKey&&!e.ctrlKey){ e.preventDefault(); api.setModal({type:'item',group:'products',index:i}); }}},'Details')), h(ProductButtons,{api,item:it}))); }
    function Products({api}){ const cfg=api.data.products||{}; const items=cfg.items||[]; const [q,setQ]=useState(''); const [cat,setCat]=useState('all'); const [sort,setSort]=useState('default'); const [attrs,setAttrs]=useState({}); const [price,setPrice]=useState({min:'',max:''}); const start=Number(cfg.homeLimit||8); const batch=Number(cfg.batch||8); const [count,setCount]=useState(start); useEffect(()=>setCount(start),[start,items.length]); const attrDefs=useMemo(()=>{ const fromCfg=Array.isArray(cfg.attributeFilters)?cfg.attributeFilters:[]; const map={}; fromCfg.forEach(a=>{ if(a?.key) map[a.key]={key:a.key,label:a.label||a.key,options:[...(a.options||[])]}; }); items.forEach(p=>{ Object.entries(p.attributes||{}).forEach(([k,v])=>{ if(!map[k]) map[k]={key:k,label:k,options:[]}; if(v&&!map[k].options.includes(v)) map[k].options.push(v); }); (p.variations||[]).forEach(v=>Object.entries(v.attributes||{}).forEach(([k,val])=>{ if(!map[k]) map[k]={key:k,label:k,options:[]}; if(val&&!map[k].options.includes(val)) map[k].options.push(val); })); }); return Object.values(map); },[cfg.attributeFilters,items]); const categories=useMemo(()=>{ const c=(cfg.categories||[]).map(x=>typeof x==='string'?{slug:x,title:x}:x); items.forEach(p=>{ if(p.category&&!c.some(x=>x.slug===p.category)) c.push({slug:p.category,title:p.category}); }); return c; },[cfg.categories,items]); function itemText(p){ return JSON.stringify([p.id,p.sku,p.title,p.category,p.brand,p.tags,p.text,p.body,p.attributes,p.variations]).toLowerCase(); } function attrMatch(p,key,val){ if(!val) return true; if(String((p.attributes||{})[key]||'').toLowerCase()===val.toLowerCase()) return true; return (p.variations||[]).some(v=>String((v.attributes||{})[key]||'').toLowerCase()===val.toLowerCase()); } function priceMatch(p){ if(price.min===''&&price.max==='') return true; const val=api.productPriceValue(p); if(val<=0) return false; const min=price.min===''?null:Number(price.min); const max=price.max===''?null:Number(price.max); return (min===null||val>=min)&&(max===null||val<=max); } function resetAll(){ setQ(''); setCat('all'); setSort('default'); setAttrs({}); setPrice({min:'',max:''}); setCount(start); } const filtered=useMemo(()=>{ const low=q.trim().toLowerCase(); let out=[...items].reverse().filter(p=>(cat==='all'||p.category===cat) && (!low||itemText(p).includes(low)) && Object.entries(attrs).every(([k,v])=>attrMatch(p,k,v)) && priceMatch(p)); if(sort==='price-asc') out=[...out].sort((a,b)=>api.productPriceValue(a)-api.productPriceValue(b)); if(sort==='price-desc') out=[...out].sort((a,b)=>api.productPriceValue(b)-api.productPriceValue(a)); if(sort==='title') out=[...out].sort((a,b)=>String(a.title||'').localeCompare(String(b.title||''))); return out; },[items,q,cat,attrs,price.min,price.max,sort]); return h('section',{id:'products',className:'section'}, h(Container,{api}, h(SectionTitle,{api,base:'products'}), cfg.shopEnabled&&h(React.Fragment,null,
        h('div',{className:'catalog-toolbar'},
          cfg.searchEnabled!==false&&h('div',{className:'catalog-search-wrap'}, icon('bi bi-search'), h('input',{className:'form-control catalog-search',placeholder:'Search by title, ID, SKU, category, tag, colour, size...',value:q,onChange:e=>{setQ(e.target.value);setCount(start)}})),
          h('div',{className:'catalog-filter-row'},
            cfg.filterEnabled!==false&&h(React.Fragment,null,
              h(CategoryFilter,{categories,value:cat,onChange:v=>{setCat(v);setCount(start)}}),
              h(PriceRangeFilter,{api,items,value:price,onChange:v=>{setPrice(v);setCount(start)}}),
              attrDefs.map(a=>h(AttributeFilter,{key:a.key,def:a,value:attrs[a.key]||'',onChange:v=>{setAttrs({...attrs,[a.key]:v});setCount(start)}}))
            ),
            h('select',{className:'form-select catalog-select',value:sort,onChange:e=>setSort(e.target.value)}, h('option',{value:'default'},'Newest'), h('option',{value:'title'},'Title A-Z'), h('option',{value:'price-asc'},'Price low-high'), h('option',{value:'price-desc'},'Price high-low')),
            h('button',{type:'button',className:'btn-ghost filter-reset',onClick:resetAll},'Reset')
          )
        ),
        h(ActiveFilterChips,{q,cat,categories,attrs,price,setQ,setCat,setAttrs,setPrice})
      ), h('div',{className:'small muted mb-3'}, filtered.length,' product',filtered.length===1?'':'s',' found'), h('div',{className:'row g-4'}, filtered.slice(0,count).map((item)=>h(ProductCard,{api,it:item,i:items.indexOf(item),key:item.slug||item.sku||item.title}))), cfg.showButton && count<filtered.length && h('div',{className:'load-more-wrap'}, h('button',{className:'btn-cms',onClick:()=>setCount(c=>c+batch)}, cfg.buttonText||'Load more')))); }

    function HCaptchaWidget({api,onToken,className='',resetKey=0}){
      const hc=api.data.settings?.hcaptcha||{}; const host=useRef(null); const widgetId=useRef(null);
      useEffect(()=>{
        onToken?.('');
        if(!hc.enabled||!hc.siteKey||!host.current)return;
        let dead=false;
        const render=()=>{
          if(dead||widgetId.current!==null||!host.current||!window.hcaptcha)return;
          try{widgetId.current=window.hcaptcha.render(host.current,{sitekey:hc.siteKey,callback:(token)=>onToken?.(token||''),'expired-callback':()=>onToken?.(''),'error-callback':()=>onToken?.('')});}catch(e){}
        };
        render(); const timer=setInterval(render,300); const stop=setTimeout(()=>clearInterval(timer),6500);
        return()=>{dead=true;clearInterval(timer);clearTimeout(stop);if(window.hcaptcha&&widgetId.current!==null){try{window.hcaptcha.remove(widgetId.current)}catch(e){}}widgetId.current=null;};
      },[hc.enabled,hc.siteKey,api.lang]);
      useEffect(()=>{if(resetKey&&window.hcaptcha&&widgetId.current!==null){try{window.hcaptcha.reset(widgetId.current)}catch(e){}onToken?.('');}},[resetKey]);
      if(!hc.enabled||!hc.siteKey)return null;
      return h('div',{className:className||'hcaptcha-wrap',ref:host});
    }

    function Booking({api}){
      const d=api.data.booking||{};
      const services=(d.services||[]).filter(x=>x.enabled!==false);
      const [service,setService]=useState(services[0]?.id||'');
      const [date,setDate]=useState('');
      const [slots,setSlots]=useState([]);
      const [time,setTime]=useState('');
      const [form,setForm]=useState({name:'',email:'',phone:''});
      const [busy,setBusy]=useState(false);
      const [msg,setMsg]=useState('');
      const [captcha,setCaptcha]=useState(''); const [captchaReset,setCaptchaReset]=useState(0);
      const hc=api.data.settings?.hcaptcha||{};
      async function loadSlots(v,sv=service){
        setDate(v); setTime(''); setSlots([]); if(!v)return;
        try{const r=await fetch('?api=booking-slots&date='+encodeURIComponent(v)+'&serviceId='+encodeURIComponent(sv));const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Could not load times.');setSlots(j.slots||[])}catch(e){setMsg(e.message)}
      }
      async function book(e){
        e.preventDefault(); setBusy(true); setMsg('');
        try{const r=await fetch('?api=booking-create',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({...form,serviceId:service,date,time,hcaptcha:captcha})});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Booking failed.');setMsg(j.message||'Booking confirmed.');setTime('');setCaptcha('');setCaptchaReset(v=>v+1);await loadSlots(date,service)}catch(e){setMsg(e.message||'Booking failed.')}finally{setBusy(false)}
      }
      const serviceCards=h('div',{className:'booking-service-grid mb-4'},services.map(sv=>{
        const meta=`${sv.durationMinutes||sv.duration||60} min${Number(sv.price||0)>0?' · '+new Intl.NumberFormat(undefined,{style:'currency',currency:d.currency||api.data.products?.currency||'EUR'}).format(Number(sv.price)):''}`;
        return h('button',{type:'button',className:cls('booking-service',service===sv.id&&'active'),key:sv.id,onClick:()=>{setService(sv.id);if(date)loadSlots(date,sv.id)}},h('strong',null,translate(api,sv,'title')||sv.name||'Service'),h('div',{className:'small muted'},meta));
      }));
      const slotList=h('div',{className:'booking-slots'},slots.length?slots.map(t=>h('button',{type:'button',className:cls('booking-slot',time===t&&'active'),key:t,onClick:()=>setTime(t)},t)):h('span',{className:'small muted'},date?'No available times for this date.':'Choose a date first.'));
      const bookingForm=h('form',{className:'cms-card',onSubmit:book},
        h('div',{className:'row g-3'},
          h('div',{className:'col-md-6'},h(Field,{label:'Choose date'},h('input',{type:'date',className:'form-control',min:new Date().toISOString().slice(0,10),value:date,onChange:e=>loadSlots(e.target.value),required:true}))),
          h('div',{className:'col-md-6'},h('div',{className:'small fw-bold mb-2'},'Available time'),slotList),
          h('div',{className:'col-md-4'},h('input',{className:'form-control',placeholder:'Name',value:form.name,onChange:e=>setForm({...form,name:e.target.value}),required:true})),
          h('div',{className:'col-md-4'},h('input',{className:'form-control',type:'email',placeholder:'Email',value:form.email,onChange:e=>setForm({...form,email:e.target.value}),required:true})),
          h('div',{className:'col-md-4'},h('input',{className:'form-control',placeholder:'Phone',value:form.phone,onChange:e=>setForm({...form,phone:e.target.value})}))
        ),
        h(HCaptchaWidget,{api,onToken:setCaptcha,resetKey:captchaReset,className:'hcaptcha-wrap booking-hcaptcha'}),
        h('button',{className:'btn-cms mt-3',disabled:busy||!time||(hc.enabled&&!captcha)},busy?'Confirming...':'Book → confirmation in real time'),
        msg&&h('div',{className:'booking-confirm mt-3'},msg)
      );
      return h('section',{id:'booking',className:'section cms-soft'},h(Container,{api},h('div',{className:'booking-shell'},h(SectionTitle,{api,base:'booking'}),serviceCards,bookingForm)));
    }


    function AdminShowcase({api}){
      const d=api.data.adminShowcase||{}, E=api.Editable;
      const options=Array.isArray(d.domainOptions)?d.domainOptions:[];
      const shots=Array.isArray(d.items)?d.items:[];
      const rows=Array.isArray(d.rows)?d.rows:[];
      const permission=v=>v
        ?h('span',{className:'permission-yes'},icon('bi bi-check-circle-fill'),api.t('Yes'))
        :h('span',{className:'permission-no'},icon('bi bi-lock-fill'),api.t('No'));
      const domainCards=options.map((opt,i)=>h('div',{className:'admin-domain-option',key:i},
        h('span',{className:'cms-icon'},icon(opt.icon||'bi bi-globe2')),
        h('div',null,
          h(E,{path:`adminShowcase.domainOptions.${i}.title`,tag:'h4',className:'h6 mb-1'}),
          h(E,{path:`adminShowcase.domainOptions.${i}.text`,tag:'p',className:'small muted mb-0'}),
          opt.example&&h('span',{className:'domain-example'},opt.example)
        )
      ));
      const superItems=Array.isArray(d.superAdminItems)?d.superAdminItems:[];
      const superPanel=d.superAdminImage?h('div',{className:'admin-super-showcase'},
        h('div',{className:'admin-super-copy'},h(E,{path:'adminShowcase.superAdminTitle',tag:'h3',className:'h4'}),h(E,{path:'adminShowcase.superAdminText',tag:'p',className:'muted'}),h('div',{className:'admin-super-feature-grid'},superItems.map((it,i)=>h('div',{className:'admin-super-feature',key:i},h('span',{className:'admin-super-feature-icon'},icon(it.icon||'bi bi-check2-circle')),h('div',null,h(E,{path:`adminShowcase.superAdminItems.${i}.title`,tag:'strong'}),h(E,{path:`adminShowcase.superAdminItems.${i}.text`,tag:'span',className:'small muted'})))))),
        h('a',{className:'admin-super-image',href:mediaUrl(d.superAdminImage),'aria-label':api.localizedValue('adminShowcase.superAdminTitle')||'Super Admin'},h('img',{src:mediaUrl(d.superAdminImage),alt:api.localizedValue('adminShowcase.superAdminTitle')||'Super Admin',loading:'lazy',decoding:'async',fetchPriority:'low','data-lightbox':'admin-showcase','data-lightbox-src':mediaUrl(d.superAdminImage)}))
      ):null;
      const shotCards=shots.map((it,i)=>h('article',{className:'admin-shot-card',key:i},
        h('div',{className:'admin-shot-icon','aria-hidden':'true'},icon(it.icon||['bi bi-window-stack','bi bi-images','bi bi-bag-check'][i]||'bi bi-grid')),
        h('div',{className:'admin-shot-copy'},
          h(E,{path:`adminShowcase.items.${i}.title`,tag:'h3',className:'h6'}),
          h(E,{path:`adminShowcase.items.${i}.text`,tag:'p',className:'small muted mb-0'})
        )
      ));
      const capabilityRows=rows.map((row,i)=>h('tr',{key:i},
        h('td',null,h(E,{path:`adminShowcase.rows.${i}.title`,tag:'strong'})),
        h('td',null,permission(!!row.editor)),
        h('td',null,permission(!!row.super)),
        h('td',{className:'admin-ease'},h(E,{path:`adminShowcase.rows.${i}.ease`}))
      ));
      const capabilityTable=h('div',{className:'table-responsive admin-cap-table-wrap'},
        h('table',{className:'admin-cap-table'},
          h('thead',null,h('tr',null,
            h('th',null,api.t('Task')),
            h('th',null,api.t('Editor')),
            h('th',null,api.t('Super Admin')),
            h('th',null,api.t('How easy?'))
          )),
          h('tbody',null,...capabilityRows)
        )
      );
      return h('section',{id:'admin-tools',className:'section admin-showcase'},
        h(Container,{api},
          h(SectionTitle,{api,base:'adminShowcase'}),
          h('div',{className:'admin-showcase-shell'},
            h('div',{className:'admin-domain-card'},
              h(E,{path:'adminShowcase.domainTitle',tag:'h3',className:'h4 mb-2'}),
              h(E,{path:'adminShowcase.domainText',tag:'p',className:'muted mb-0'}),
              h('div',{className:'admin-domain-options'},...domainCards)
            ),
            superPanel,h('div',{className:'admin-shot-grid'},...shotCards),
            h('div',null,
              h(E,{path:'adminShowcase.tableTitle',tag:'h3',className:'h4 mb-1'}),
              h(E,{path:'adminShowcase.tableSubtitle',tag:'p',className:'muted mb-3'}),
              capabilityTable
            )
          )
        )
      );
    }

    function Pricing({api}){
      const E=api.Editable;
      const pricing=api.data.pricing||{};
      const items=pricing.items||[];
      const [category,setCategory]=useState('all');
      const swiperEl=useRef(null);
      const instRef=useRef(null);
      const lang=String(api.lang||'en').toLowerCase();
      const langStrings=pricing.i18n?.[lang]||{};
      const labels={
        filterAll:'All plans',filterLpcms:'LPCMS',filterWebsites:'Websites',filterCommerce:'E-commerce',
        filterCustom:'Custom development',filterCare:'Care',fromLabel:'From',ukLabel:'UK',euLabel:'Europe',lvLabel:'Latvia',
        quoteCaveat:'Indicative pricing, not a binding quote.',
        enterpriseTitle:pricing.enterpriseTitle||'Custom & Enterprise Development',
        enterpriseText:pricing.enterpriseText||'',enterpriseButton:pricing.enterpriseButton||'Request a scoped quote',note:pricing.note||''
      };
      const word=(key)=>langStrings[key]||labels[key]||'';
      const categories=[
        ['all','filterAll'],['lpcms','filterLpcms'],['websites','filterWebsites'],
        ['commerce','filterCommerce'],['custom','filterCustom'],['care','filterCare']
      ];
      const selectedItems=items.map((item,index)=>({item,index})).filter(({item})=>category==='all'||(item.group||'websites')===category);
      useEffect(()=>{
        const setup=()=>{
          if(!swiperEl.current||!window.Swiper)return;
          if(window.innerWidth<768){
            if(instRef.current){try{instRef.current.update()}catch(e){}return;}
            const paginationEl=swiperEl.current.querySelector('.pricing-swiper-pagination');
            instRef.current=new window.Swiper(swiperEl.current,{
              slidesPerView:1,spaceBetween:18,speed:450,loop:false,rewind:false,
              watchOverflow:true,observer:true,observeParents:true,
              autoplay:false,
              pagination:{el:paginationEl,clickable:true,hideOnClick:false,dynamicBullets:false},
              a11y:{enabled:true}
            });
          }else if(instRef.current){try{instRef.current.destroy(true,true)}catch(e){}instRef.current=null;}
        };
        setup();
        const paginationEl=swiperEl.current?.querySelector('.pricing-swiper-pagination');
        const onPaginationClick=(event)=>{
          const bullet=event.target?.closest?.('.swiper-pagination-bullet');
          if(!paginationEl||!bullet||!paginationEl.contains(bullet)||!instRef.current)return;
          const index=Array.from(paginationEl.querySelectorAll('.swiper-pagination-bullet')).indexOf(bullet);
          if(index>=0)try{instRef.current.slideTo(index,450)}catch(e){}
        };
        paginationEl?.addEventListener('click',onPaginationClick);
        window.addEventListener('resize',setup);
        return()=>{
          paginationEl?.removeEventListener('click',onPaginationClick);
          window.removeEventListener('resize',setup);
          if(instRef.current){try{instRef.current.destroy(true,true)}catch(e){}instRef.current=null;}
        };
      },[category,selectedItems.length,lang]);
      const currencyLocale={en:'en-GB',lv:'lv-LV',ru:'ru-RU',fr:'fr-FR',es:'es-ES',de:'de-DE',pl:'pl-PL',lt:'lt-LT',et:'et-EE',da:'da-DK',no:'nb-NO',sv:'sv-SE',fi:'fi-FI',is:'is-IS'};
      const formatPrice=(raw,currency)=>{
        const ungrouped=String(raw||'').replace(/,/g,'').replace(/[^\d.]/g,'');
        const amount=Number(ungrouped);
        if(!ungrouped||!Number.isFinite(amount))return String(raw||'');
        const decimal=!Number.isInteger(amount);
        return new Intl.NumberFormat(currencyLocale[lang]||'en-GB',{
          style:'currency',currency,minimumFractionDigits:decimal?2:0,maximumFractionDigits:decimal?2:0
        }).format(amount);
      };
      const offer=(item,price,label,currency,fieldPath,secondary=false)=>h('div',{className:secondary?'pricing-offer-secondary':'pricing-offer-primary'},
        h('span',{className:'pricing-price-label'},label),
        h('div',{className:secondary?'secondary':'pricing-price'},
          item.isFrom?word('fromLabel')+' ':null,
          api.admin&&fieldPath?h(E,{path:fieldPath,tag:'span',className:'dp-pricing-admin-raw'}):formatPrice(price,currency)
        )
      );
      return h('section',{id:'pricing',className:'section cms-soft'},h(Container,{api},
        h(SectionTitle,{api,base:'pricing'}),
        h('div',{className:'dp-pricing-filterbar',role:'group','aria-label':api.t('Pricing')},
          categories.map(([id,labelKey])=>h('button',{
            type:'button',key:id,className:'dp-pricing-filter',
            'aria-pressed':category===id,onClick:()=>setCategory(id)
          },word(labelKey)))
        ),
        h('div',{className:'swiper pricing-swiper',ref:swiperEl,key:category},
          h('div',{className:'swiper-wrapper row g-4 pricing-grid-clean'},selectedItems.map(({item:it,index:i})=>
            h('div',{className:'swiper-slide col-12 col-md-6 col-xl-3 pricing-slide',key:it.id||i},
              h('article',{className:cls('cms-card pricing-card pricing-card-clean',it.id==='wp-business'&&'featured')},
                h('div',{className:'pricing-card-head'},
                  it.badge&&h('span',{className:'plan-badge'},h(E,{path:`pricing.items.${i}.badge`})),
                  h(E,{path:`pricing.items.${i}.title`,tag:'h3',className:'pricing-title'}),
                  h('div',{className:'pricing-dual-price'},
                    lang==='en'?
                      h(React.Fragment,null,
                        offer(it,it.price,word('ukLabel')+' · GBP','GBP',`pricing.items.${i}.price`),
                        offer(it,it.secondaryPrice||it.priceEur,word('euLabel')+' · EUR','EUR',`pricing.items.${i}.secondaryPrice`,true)
                      ):
                      lang==='lv'?offer(it,it.latviaPrice||it.secondaryPrice||it.price,word('lvLabel')+' · EUR','EUR',`pricing.items.${i}.latviaPrice`):
                        offer(it,it.secondaryPrice||it.priceEur||it.price,word('euLabel')+' · EUR','EUR',`pricing.items.${i}.secondaryPrice`),
                    h(E,{path:`pricing.items.${i}.period`,tag:'div',className:'pricing-period muted'})
                  ),
                  h(E,{path:`pricing.items.${i}.text`,tag:'p',className:'pricing-intro muted'})
                ),
                Array.isArray(it.features)&&h('div',{className:'pricing-features'},it.features.map((f,j)=>
                  h('div',{key:j,className:'plan-feature'},h('span',{className:'plan-feature-icon'},icon('bi bi-check2')),h(E,{path:`pricing.items.${i}.features.${j}`}))
                )),
                h('a',{className:'btn-cms pricing-action',href:it.buttonHref||'#contact'},h(E,{path:`pricing.items.${i}.buttonText`}))
              )
            )
          )),
          h('div',{className:'pricing-swiper-pagination'})
        ),
        h('p',{className:'dp-pricing-note'},h('strong',null,word('quoteCaveat')+' '),word('note')),
        h('div',{className:'dp-pricing-enterprise'},
          h('div',{className:'dp-pricing-enterprise-text'},
            h('h3',null,word('enterpriseTitle')),
            h('p',null,word('enterpriseText'))
          ),
          h('a',{className:'btn-cms',href:'#contact'},word('enterpriseButton'),icon('bi bi-arrow-up-right'))
        )
      ));
    }

    function DemoIntake({api}){
      const cfg=api.data.demoIntake||{};
      const plans=Array.isArray(cfg.plans)?cfg.plans:[];
      const [step,setStep]=useState(0);
      const [logo,setLogo]=useState(null);
      const [busy,setBusy]=useState(false);
      const [result,setResult]=useState(null);
      const [error,setError]=useState('');
      const captchaRef=useRef(null), captchaId=useRef(null);
      const [form,setForm]=useState({plan:'premium',currency:api.lang==='en'?'GBP':'EUR',name:'',email:'',phone:'',company:'',industry:'',tagline:'',about:'',services:'',products:'',address:'',existingWebsite:'',preferredDomain:'',language:'English',goals:'',primaryColor:'#0d6efd',style:'clean',chatChoice:'chatbot',whatsapp:'',bookingNeeded:false,socials:'',consent:false,_hp:''});
      const hc=api.data.settings?.hcaptcha||{};
      const steps=Array.isArray(cfg.steps)&&cfg.steps.length===4?cfg.steps:['Your business','Website content','Branding & tools','Review'];
      function setf(k,v){setForm(f=>({...f,[k]:v}));setError('');}
      function validCurrent(){
        if(step===0 && (!form.company.trim()||!form.name.trim()||!/^\S+@\S+\.\S+$/.test(form.email))) return 'Add your business name, contact name and a valid email.';
        if(step===1 && (!form.about.trim()||!form.services.trim())) return 'Add a short business description and at least one service or product.';
        if(step===2 && form.chatChoice==='whatsapp' && !form.whatsapp.trim()) return 'Add the WhatsApp number or choose another chat option.';
        return '';
      }
      function next(){const e=validCurrent();if(e){setError(e);return;}setStep(v=>Math.min(3,v+1));}
      useEffect(()=>{
        if(step!==3||!hc.enabled||!hc.siteKey||!captchaRef.current||captchaId.current!==null)return;
        const tryRender=()=>{ if(window.hcaptcha&&captchaRef.current&&captchaId.current===null){ try{captchaId.current=window.hcaptcha.render(captchaRef.current,{sitekey:hc.siteKey});}catch(e){} } };
        tryRender(); const t=setInterval(tryRender,400); const stop=setTimeout(()=>clearInterval(t),6000); return()=>{clearInterval(t);clearTimeout(stop)};
      },[step,hc.enabled,hc.siteKey]);
      async function submit(e){
        e.preventDefault(); setError('');
        const v=validCurrent(); if(v){setError(v);return;} if(!form.consent){setError('Please confirm the demo preparation consent.');return;}
        setBusy(true);
        try{
          const fd=new FormData(); Object.entries(form).forEach(([k,v])=>fd.append(k,typeof v==='boolean'?(v?'1':'0'):String(v??''))); if(logo)fd.append('logo',logo,logo.name);
          if(hc.enabled&&window.hcaptcha&&captchaId.current!==null)fd.append('hcaptcha',window.hcaptcha.getResponse(captchaId.current)||'');
          const r=await fetch('?api=demo-intake',{method:'POST',body:fd}); const j=await cmsResponseJson(r); if(!r.ok||!j.ok)throw new Error(j.error||'Could not submit the demo request.'); setResult(j); setStep(0);
        }catch(err){setError(err.message||'Could not submit the demo request.');}
        finally{setBusy(false);}
      }
      if(result)return h('section',{id:'demo-request',className:'section'},h(Container,{api},h('div',{className:'demo-intake-shell demo-success'},icon('bi bi-check2-circle'),h('h2',null,'Demo request received'),h('p',{className:'lead'},api.localizedValue('demoIntake.thankYou')||result.message||'Your draft was created for review.'),result.requestId&&h('div',{className:'plan-pill'},'Reference ',result.requestId))));
      const stepDots=h('div',{className:'demo-steps'},steps.map((label,i)=>h('div',{key:i,className:cls('demo-step',i===step&&'active',i<step&&'done')},h('span',null,i<step?icon('bi bi-check-lg'):String(i+1)),h('small',null,label))));
      let content=null;
      if(step===0)content=h('div',{className:'row g-3'},
        h('div',{className:'col-12'},h('div',{className:'demo-plan-grid'},plans.map(p=>{
          const priceItem=(api.data.pricing?.items||[]).find(it=>it.id===({'premium':'lpcms-premium','premium-pro':'lpcms-pro','wordpress':'wp-business','woocommerce':'woo-standard'}[p.id]||''));
          const loc=priceItem?.i18n?.[api.lang]||{};
          const price=api.lang==='en'?p.price:api.lang==='lv'?(p.priceLatvia||p.priceEur||p.price):(p.priceEur||p.price);
          const fromLabel=api.data.pricing?.i18n?.[api.lang]?.fromLabel||'From';
          const localizedPrice=api.lang==='en'?price:String(price).replace(/^from /i,fromLabel+' ').replace(/\/ month$/i,api.t('/ month'));
          return h('button',{type:'button',key:p.id,className:cls('demo-plan-choice',form.plan===p.id&&'active'),onClick:()=>setf('plan',p.id)},
            h('strong',null,loc.title||p.title),h('span',null,loc.badge||p.description),h('small',null,localizedPrice));
        }))),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Business / brand name *'),h('input',{className:'form-control',value:form.company,onChange:e=>setf('company',e.target.value)})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Industry'),h('input',{className:'form-control',value:form.industry,onChange:e=>setf('industry',e.target.value),placeholder:'e.g. beauty salon, construction, consulting'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Your name *'),h('input',{className:'form-control',value:form.name,onChange:e=>setf('name',e.target.value)})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Email *'),h('input',{className:'form-control',type:'email',value:form.email,onChange:e=>setf('email',e.target.value)})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Phone'),h('input',{className:'form-control',value:form.phone,onChange:e=>setf('phone',e.target.value)})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Billing currency'),h('select',{className:'form-select',value:form.currency,onChange:e=>setf('currency',e.target.value)},h('option',{value:'GBP'},'GBP (£)'),h('option',{value:'EUR'},'EUR (€)')))
      );
      if(step===1)content=h('div',{className:'row g-3'},
        h('div',{className:'col-12'},h('label',{className:'form-label'},'Main headline / tagline'),h('input',{className:'form-control',value:form.tagline,onChange:e=>setf('tagline',e.target.value),placeholder:'What should visitors understand first?'})),
        h('div',{className:'col-12'},h('label',{className:'form-label'},'About the business *'),h('textarea',{className:'form-control',rows:4,value:form.about,onChange:e=>setf('about',e.target.value),placeholder:'Describe the business, customers and what makes it different.'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Services * — one per line'),h('textarea',{className:'form-control',rows:6,value:form.services,onChange:e=>setf('services',e.target.value),placeholder:'Consultation — Short description\nInstallation — Short description'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Products / prices — Premium Pro'),h('textarea',{className:'form-control',rows:6,value:form.products,onChange:e=>setf('products',e.target.value),placeholder:'Product name | 29.99 | Short description\nSecond product | 49.00'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Address'),h('input',{className:'form-control',value:form.address,onChange:e=>setf('address',e.target.value)})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Existing website'),h('input',{className:'form-control',value:form.existingWebsite,onChange:e=>setf('existingWebsite',e.target.value),placeholder:'https://...'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Preferred domain / subdomain'),h('input',{className:'form-control',value:form.preferredDomain,onChange:e=>setf('preferredDomain',e.target.value),placeholder:'yourbrand.com or yourbrand'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Main language'),h('input',{className:'form-control',value:form.language,onChange:e=>setf('language',e.target.value)})),
        h('div',{className:'col-12'},h('label',{className:'form-label'},'Website goals / extra content'),h('textarea',{className:'form-control',rows:3,value:form.goals,onChange:e=>setf('goals',e.target.value)}))
      );
      if(step===2)content=h('div',{className:'row g-3'},
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Logo image'),h('input',{className:'form-control',type:'file',accept:'image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif',onChange:e=>setLogo(e.target.files?.[0]||null)}),h('div',{className:'small muted mt-1'},logo?logo.name:'PNG, JPG, WebP, HEIC or AVIF.')),
        h('div',{className:'col-md-3'},h('label',{className:'form-label'},'Brand colour'),h('input',{className:'form-control form-control-color w-100',type:'color',value:form.primaryColor,onChange:e=>setf('primaryColor',e.target.value)})),
        h('div',{className:'col-md-3'},h('label',{className:'form-label'},'Visual style'),h('select',{className:'form-select',value:form.style,onChange:e=>setf('style',e.target.value)},['clean','bold','minimal','friendly','luxury'].map(v=>h('option',{key:v,value:v},v.charAt(0).toUpperCase()+v.slice(1))))),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Website chat'),h('select',{className:'form-select',value:form.chatChoice,onChange:e=>setf('chatChoice',e.target.value)},h('option',{value:'chatbot'},'AI chatbot'),h('option',{value:'whatsapp'},'WhatsApp'),h('option',{value:'none'},'No chat button'))),
        form.chatChoice==='whatsapp'&&h('div',{className:'col-md-6'},h('label',{className:'form-label'},'WhatsApp number'),h('input',{className:'form-control',value:form.whatsapp,onChange:e=>setf('whatsapp',e.target.value),placeholder:'+44...'})),
        h('div',{className:'col-12'},h('label',{className:'form-label'},'Social links / usernames'),h('textarea',{className:'form-control',rows:3,value:form.socials,onChange:e=>setf('socials',e.target.value),placeholder:'Facebook, Instagram, LinkedIn, TikTok...'})),
        h('div',{className:'col-12'},h('label',{className:'form-check'},h('input',{className:'form-check-input',type:'checkbox',checked:form.bookingNeeded,onChange:e=>setf('bookingNeeded',e.target.checked)}),h('span',{className:'form-check-label ms-2'},'Include booking / appointment functionality in the demo')))
      );
      if(step===3)content=h('div',null,
        h('div',{className:'demo-review-grid'},
          h('div',null,h('small',null,'Plan'),h('strong',null,plans.find(p=>p.id===form.plan)?.title||form.plan,' · ',form.currency)),
          h('div',null,h('small',null,'Business'),h('strong',null,form.company)),
          h('div',null,h('small',null,'Contact'),h('strong',null,form.name,' · ',form.email)),
          h('div',null,h('small',null,'Chat'),h('strong',null,form.chatChoice)),
          h('div',null,h('small',null,'Booking'),h('strong',null,form.bookingNeeded?'Included':'Not requested')),
          h('div',null,h('small',null,'Logo'),h('strong',null,logo?.name||'No logo uploaded'))
        ),
        h('p',{className:'small muted mt-3'},'A private website draft will be generated from these answers. Super Admin must approve and load it before the public site changes.'),
        hc.enabled&&hc.siteKey?h('div',{className:'mb-3',ref:captchaRef}):null,
        h('label',{className:'form-check'},h('input',{className:'form-check-input',type:'checkbox',checked:form.consent,onChange:e=>setf('consent',e.target.checked)}),h('span',{className:'form-check-label ms-2'},cfg.privacyText||'I approve use of this information for my website demo.')),
        h('input',{type:'text',value:form._hp,onChange:e=>setf('_hp',e.target.value),tabIndex:-1,autoComplete:'off',style:{position:'absolute',left:'-9999px'},'aria-hidden':'true'})
      );
      return h('section',{id:'demo-request',className:'section'},h(Container,{api},h(SectionTitle,{api,base:'demoIntake'}),h('form',{className:'demo-intake-shell',onSubmit:submit},stepDots,h('div',{className:'demo-form-body'},content),error&&h('div',{className:'alert alert-danger mt-3 mb-0'},error),h('div',{className:'demo-form-actions'},step>0?h('button',{type:'button',className:'btn-ghost',disabled:busy,onClick:()=>{setError('');setStep(v=>v-1)}},'Back'):h('span'),step<3?h('button',{type:'button',className:'btn-cms',onClick:next},'Continue ',icon('bi bi-arrow-right')):h('button',{className:'btn-cms',disabled:busy},busy?'Creating draft…':(api.localizedValue('demoIntake.buttonText')||'Create my demo request'))))));
    }

    function Blog({api}){
      const E=api.Editable;
      return h('section',{id:'blog',className:'section cms-soft'},
        h(Container,{api},
          h(SectionTitle,{api,base:'blog'}),
          h(LoadMoreGrid,{api,base:'blog',render:(it,i)=>
            h('div',{className:'col-md-6 col-lg-4',key:i},
              h('article',{className:'cms-card'},
                h(EditableImage,{api,path:`blog.items.${i}.image`,alt:api.localizedValue(`blog.items.${i}.title`)||it.title||'',className:'product-card-img mb-3',hideWhenEmpty:true,errorSrc:it.fallbackImage||''}),
                h('small',{className:'muted'},it.date),
                h(E,{path:`blog.items.${i}.title`,tag:'h3',className:'h4 fw-bold mt-2'}),
                h(E,{path:`blog.items.${i}.excerpt`,tag:'p',className:'muted'}),
                h('a',{className:'btn-ghost',href:pretty('blog',it),onClick:e=>{if(!e.metaKey&&!e.ctrlKey){e.preventDefault();api.setModal({type:'item',group:'blog',index:i});}}},api.t('Read article'))
              )
            )
          })
        )
      );
    }

    function shareLinks(url,title){ const u=encodeURIComponent(url), t=encodeURIComponent(title||document.title); return [ {label:'Facebook',url:`https://www.facebook.com/sharer/sharer.php?u=${u}`}, {label:'X',url:`https://twitter.com/intent/tweet?url=${u}&text=${t}`}, {label:'LinkedIn',url:`https://www.linkedin.com/sharing/share-offsite/?url=${u}`}, {label:'WhatsApp',url:`https://wa.me/?text=${t}%20${u}`} ]; }

    function DeliveryMap({locations=[]}){
      const mapRef=useRef(null);
      useEffect(()=>{
        if(!mapRef.current||!window.L)return;
        const L=window.L;
        const defaults=[
          {key:'uk',label:'Northampton, England',short:'Northampton',lat:52.2405,lng:-0.9027},
          {key:'lv',label:'Riga, Latvia',short:'Riga',lat:56.9496,lng:24.1052}
        ];
        const points=defaults.map((fallback,i)=>{
          const loc=locations?.[i]||{};
          const lat=Number.isFinite(Number(loc.lat))?Number(loc.lat):fallback.lat;
          const lng=Number.isFinite(Number(loc.lng))?Number(loc.lng):fallback.lng;
          const label=String(loc.city||fallback.label);
          return {...fallback,label,short:(label.split(',')[0]||fallback.short).trim(),lat,lng};
        });
        const map=L.map(mapRef.current,{zoomControl:true,scrollWheelZoom:false,attributionControl:true,preferCanvas:true,minZoom:2,maxZoom:18});
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{
          minZoom:2,maxZoom:18,
          attribution:'&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors'
        }).addTo(map);
        const pinIcon=L.divIcon({className:'delivery-leaflet-pin-wrap',html:'<span class="delivery-leaflet-pin"></span>',iconSize:[38,46],iconAnchor:[19,43]});
        points.forEach((point,i)=>{
          const marker=L.marker([point.lat,point.lng],{icon:pinIcon,title:point.label,keyboard:true,riseOnHover:true}).addTo(map);
          const label=document.createElement('span'); label.textContent=point.short;
          marker.bindTooltip(label,{permanent:true,direction:i===0?'right':'left',offset:i===0?[13,-3]:[-13,-3],className:'delivery-leaflet-tooltip',opacity:1});
        });
        const bounds=L.latLngBounds(points.map(point=>[point.lat,point.lng]));
        const fit=()=>{map.invalidateSize(false);map.fitBounds(bounds,{paddingTopLeft:[68,58],paddingBottomRight:[68,58],maxZoom:5,animate:false});};
        requestAnimationFrame(fit);
        const timer=setTimeout(fit,140);
        const resize=()=>map.invalidateSize(false);
        window.addEventListener('resize',resize,{passive:true});
        return()=>{clearTimeout(timer);window.removeEventListener('resize',resize);try{map.remove()}catch(e){}};
      },[locations]);
      return h('div',{className:'delivery-map-embed'},
        h('div',{ref:mapRef,className:'map-frame delivery-map-real delivery-leaflet-map',role:'img','aria-label':'Map showing DigitalPulse locations in Northampton, England and Riga, Latvia'})
      );
    }

    function Contact({api}){
      const d=api.data.contact||{}, E=api.Editable;
      const info=[['phone','Phone','bi bi-telephone'],['email','Email','bi bi-envelope'],['hours','Hours','bi bi-clock']];
      const socials=(d.social||api.data.footer?.social||[]).filter(s=>s&&s.url); const locations=d.locations||[];
      const locationCards=locations.length?h('div',{className:'contact-locations'},locations.map((loc,i)=>h('div',{className:'contact-location-card',key:i},
        h('span',{'aria-hidden':'true'},icon(loc.icon||'bi bi-geo-alt-fill')),
        h('div',null,h(E,{path:`contact.locations.${i}.region`,tag:'strong'}),h(E,{path:`contact.locations.${i}.city`,tag:'b'}),h(E,{path:`contact.locations.${i}.text`,tag:'small'}))
      ))):null;
      const details=h('div',{className:'contact-detail-list'},...info.map(([k,label,ico])=>h('div',{key:k,className:'contact-detail-row'},h('span',{className:'contact-detail-icon','aria-hidden':'true'},icon(ico)),h('p',null,h('strong',null,api.t(label)+': '),h(E,{path:`contact.details.${k}`})))));
      const socialRow=socials.length?h('div',{className:'contact-socials','aria-label':api.t('Social media')},...socials.map((social,i)=>{const label=api.t(social.label||'Social media'),external=social.url&&social.url!=='#';return h('a',{key:i,href:social.url,className:'contact-social-link',target:external?'_blank':undefined,rel:external?'noopener noreferrer':undefined,'aria-label':label,title:label},icon(social.icon||'bi bi-link-45deg'));})):null;
      const actions=h('div',{className:'contact-actions'},
        h('button',{className:'btn-cms contact-form-btn',onClick:()=>api.setModal({type:'form'})},api.localizedValue('contact.formButtonText')||api.t('Open contact form')),
        h('button',{className:'btn-ghost business-card-btn',onClick:()=>api.setModal({type:'business-card'})},icon('bi bi-person-vcard'),api.t('Business Card'))
      );
      const box=h('div',{className:'contact-box'},locationCards,details,actions,socialRow);
      const map=h(DeliveryMap,{locations});
      return h('section',{id:'contact',className:'section'},h(Container,{api},h(SectionTitle,{api,base:'contact'}),h('div',{className:'row g-4 contact-layout'},h('div',{className:'col-lg-8'},h('div',{className:'delivery-map-shell'},map)),h('div',{className:'col-lg-4'},box))));
    }

    function CTA({api}){ const E=api.Editable; return h('section',{className:'section'}, h(Container,{api}, h('div',{className:'cta text-center'}, h(E,{path:'cta.title',tag:'h2',className:'display-6 fw-bold'}), h(E,{path:'cta.text',tag:'div',html:true,className:'lead'}), h('button',{className:'btn-ghost',onClick:()=>api.setModal({type:'form'})}, h(E,{path:'cta.buttonText'}))))); }

    function Footer({api}){
      const d=api.data.footer||{};
      const app=api.data.settings?.app||{};
      const year=new Date().getFullYear();
      const showApps=!!app.enabled&&app.footerBadgesEnabled!==false;
      const installBadge=(platform)=>{
        const apple=platform==='apple';
        const label=apple?'iPhone / iPad':'Android';
        const small=apple?api.t('Add to home screen'):api.t('Install app on');
        return h('button',{type:'button',className:'footer-app-badge',onClick:()=>api.setModal({type:'install',platform}),'aria-label':api.t('Install mobile app')+' - '+label,title:api.t('Install mobile app')+' - '+label},
          h('span',{className:'footer-app-icon'},icon(apple?'bi bi-apple':'bi bi-google-play')),
          h('span',{className:'footer-app-copy'},h('small',null,small),h('strong',null,label))
        );
      };
      const rawCopy=String(api.localizedValue('footer.copy')??d.copy??'');
      const copy=rawCopy.replace(/^Copyright\s+/i,'© ').replace('{year}',year);
      return h('footer',{className:'site-footer'},
        h(Container,{api},
          h('div',{className:'footer-layout'+(showApps?'':' no-apps')},
            h('div',{className:'footer-copyline'},copy),
            h('nav',{className:'footer-menu','aria-label':api.t('Footer links')},
              (d.menu||[]).map((m,i)=>h('a',{key:i,href:api.href(m.href)},translate(api,m,'label')))
            ),
            showApps&&h('div',{className:'footer-apps'},
              h('div',{className:'footer-apps-label'},api.t('Install our app')),
              h('div',{className:'footer-app-badges'},installBadge('android'),installBadge('apple'))
            ),
            h('div',{className:'footer-socials','aria-label':api.t('Social media')},
              (d.social||[]).map((s,i)=>{
                const label=api.t(translate(api,s,'label')||'Social media');
                const safeUrl=/^https:\/\/[^\s]+$/i.test(String(s.url||''))?s.url:'#';
                return h('a',{key:i,href:safeUrl,target:safeUrl==='#'?undefined:'_blank',rel:safeUrl==='#'?undefined:'noopener noreferrer',className:cls('footer-social-link',s.iconImage&&'footer-social-image-link'),'aria-label':label,title:label},s.iconImage?h('img',{src:mediaUrl(s.iconImage),alt:'',loading:'lazy',decoding:'async',fetchPriority:'low',width:23,height:23}):icon(s.icon));
              })
            )
          )
        )
      );
    }

    function LoginButton({api}){
      const [open,setOpen]=useState(false); const [email,setEmail]=useState(''); const [msg,setMsg]=useState(''); const [captcha,setCaptcha]=useState(''); const [captchaReset,setCaptchaReset]=useState(0);
      const hc=api.data.settings?.hcaptcha||{};
      async function submit(e){ e.preventDefault(); setMsg('Sending...'); const r=await fetch('?api=login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email,hcaptcha:captcha})}); const j=await cmsResponseJson(r); setMsg(j.error||j.message||'Check your email.'); if(j.ok){setCaptcha('');setCaptchaReset(v=>v+1);} }
      return h(React.Fragment,null,
        h('button',{className:'btn-cms admin-login',onClick:()=>setOpen(true),'aria-label':'Admin login'}, icon('bi bi-person-lock')),
        open&&h('div',{className:'cms-modal-backdrop',onClick:()=>setOpen(false)},
          h('form',{className:'cms-modal small',onClick:e=>e.stopPropagation(),onSubmit:submit},
            h('div',{className:'d-flex justify-content-between align-items-center mb-3'}, h('h3',null,'Admin login'), h('button',{type:'button',className:'icon-btn',onClick:()=>setOpen(false)},icon('bi bi-x-lg'))),
            h('p',{className:'muted'},'Enter an administrator email. A secure login URL will be sent to that address.'),
            h('input',{className:'form-control mb-3',type:'email',value:email,onChange:e=>setEmail(e.target.value),placeholder:'admin@example.com',required:true}),
            h(HCaptchaWidget,{api,onToken:setCaptcha,resetKey:captchaReset,className:'hcaptcha-wrap mb-3'}),
            h('button',{className:'btn-cms w-100',disabled:!!hc.enabled&&!captcha},'Send login link'),
            msg&&h('p',{className:'mt-3 mb-0'},msg)
          )
        )
      );
    }

    function SaveChangesDialog({api,prompt,onClose}){ const [busy,setBusy]=useState(false); const [error,setError]=useState(''); async function confirm(){ if(busy)return; setBusy(true); setError(''); const result=await api.save({silent:true}); if(result?.ok){ onClose(); } else { setError(result?.error||'Could not save changes.'); setBusy(false); } } return h('div',{className:'cms-modal-backdrop cms-save-backdrop',onClick:busy?undefined:onClose}, h('div',{className:'cms-modal small',onClick:e=>e.stopPropagation()}, h('div',{className:'d-flex justify-content-between align-items-start gap-3 mb-3'}, h('div',null,h('h3',{className:'h5 mb-1'},'Save changes?'),h('div',{className:'small muted'},'Your changes are currently only previewed in the editor.')), h('button',{type:'button',className:'icon-btn',disabled:busy,onClick:onClose,'aria-label':'Keep editing'},icon('bi bi-x-lg'))), h('p',{className:'mb-3'},prompt?.message||'Save your inline changes to data.json now?'), error&&h('div',{className:'alert alert-danger py-2'},error), h('div',{className:'d-flex justify-content-end gap-2'}, h('button',{type:'button',className:'btn-ghost',disabled:busy,onClick:onClose},'Keep editing'), h('button',{type:'button',className:'btn-cms',disabled:busy,onClick:confirm},icon('bi bi-save'),busy?' Saving...':' Save changes')))); }

    function ChatbotWidget({api}){
      const cfg=api.data.chatbot||{};
      const mode=cfg.mode||'chatbot';
      const position=cfg.position==='right'?'right':'left';
      const wa=cfg.whatsapp||{};
      const [open,setOpen]=useState(false);
      const [messages,setMessages]=useState(()=>[{role:'bot',text:api.localizedValue('chatbot.welcome')||api.localizedValue('chatbot.welcomeMessage')||'Hi! How can I help?'}]);
      const [q,setQ]=useState('');
      const [busy,setBusy]=useState(false);
      const end=useRef(null);

      useEffect(()=>{ end.current?.scrollIntoView({behavior:'smooth'}); },[messages,open]);

      async function send(e,forced=''){
        e?.preventDefault();
        const text=String(forced||q).trim();
        if(!text||busy)return;
        const history=messages.slice(-8).map(m=>({role:m.role==='bot'?'assistant':'user',text:m.text}));
        setQ('');
        setMessages(m=>[...m,{role:'user',text}]);
        setBusy(true);
        try{
          const r=await fetch('?api=chatbot',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:text,history})});
          const j=await cmsResponseJson(r);
          if(!r.ok||!j.ok)throw new Error(j.error||api.localizedValue('chatbot.fallback')||'Please contact us.');
          setMessages(m=>[...m,{role:'bot',text:j.reply||api.localizedValue('chatbot.fallback')||'Please contact us.'}]);
        }catch(err){
          setMessages(m=>[...m,{role:'bot',text:err.message||api.localizedValue('chatbot.fallback')||'Please contact us.'}]);
        }finally{ setBusy(false); }
      }

      if(mode==='whatsapp'){
        const number=String(wa.number||'').replace(/[^0-9]/g,'');
        if(!number)return null;
        const waMessage=api.localizedValue('chatbot.whatsapp.message')||wa.message||''; const href='https://wa.me/'+number+(waMessage?'?text='+encodeURIComponent(waMessage):'');
        return h('a',{className:'chatbot-launcher contact-chat-launcher whatsapp '+position,href,target:'_blank',rel:'noopener noreferrer','aria-label':api.localizedValue('chatbot.whatsapp.buttonText')||wa.buttonText||'WhatsApp'},icon('bi bi-whatsapp'));
      }

      const localizedQuestions=api.localizedValue('chatbot.suggestedQuestions'); const questions=Array.isArray(localizedQuestions)?localizedQuestions.filter(Boolean).slice(0,6):[];
      const messageList=h('div',{className:'chatbot-messages'},
        ...messages.map((m,i)=>h('div',{className:'chat-msg '+m.role,key:i},m.text)),
        busy?h('div',{className:'chat-msg bot'},'Thinking…'):null,
        h('span',{ref:end})
      );
      const compose=h('form',{className:'chatbot-compose',onSubmit:send},
        h('input',{className:'form-control',value:q,onChange:e=>setQ(e.target.value),placeholder:api.localizedValue('chatbot.placeholder')||'Ask a question...'}),
        h('button',{className:'btn-cms',disabled:busy||!q.trim(),'aria-label':'Send'},icon('bi bi-arrow-up'))
      );
      const panel=open?h('div',{className:'chatbot-panel '+position},
        h('div',{className:'chatbot-head'},
          h('div',null,h('strong',null,api.localizedValue('chatbot.title')||'AI assistant'),h('div',{className:'small muted'},'Ask about this website')),
          h('button',{className:'icon-btn',onClick:()=>setOpen(false)},icon('bi bi-x-lg'))
        ),
        messageList,
        questions.length?h('div',{className:'chatbot-questions'},questions.map((text,i)=>h('button',{type:'button',key:i,disabled:busy,onClick:e=>send(e,text)},text))):null,
        compose
      ):null;
      return h(React.Fragment,null,
        h('button',{className:'chatbot-launcher contact-chat-launcher '+position,'aria-label':api.localizedValue('chatbot.buttonText')||'AI chat',onClick:()=>setOpen(v=>!v)},icon(open?'bi bi-x-lg':'bi bi-chat-dots-fill')),
        panel
      );
    }

    function BusinessCardModal({api,onClose}){
      const cfg=api.data.contact?.businessCard||{}; const preview=mediaUrl(cfg.preview||'/assets/img/digitalpulse-business-card-preview.png'); const download=mediaUrl(cfg.download||'/assets/docs/digitalpulse-business-card-print.pdf');
      return h('div',{className:'cms-modal-backdrop',onClick:onClose},
        h('div',{className:'cms-modal business-card-modal',onClick:e=>e.stopPropagation()},
          h('div',{className:'d-flex justify-content-between align-items-center mb-3'},
            h('div',null,h('div',{className:'eyebrow mb-1'},api.t('DigitalPulse contact')),h('h3',{className:'mb-0'},api.t('Business Card'))),
            h('button',{type:'button',className:'icon-btn',onClick:onClose,'aria-label':api.t('Close')},icon('bi bi-x-lg'))
          ),
          h('img',{src:preview,alt:api.t('DigitalPulse business card preview'),className:'business-card-preview',loading:'eager',decoding:'async'}),
          h('div',{className:'business-card-actions'},
            h('a',{className:'btn-ghost',href:preview,target:'_blank',rel:'noopener noreferrer'},icon('bi bi-arrows-fullscreen'),api.t('View full size')),
            h('a',{className:'btn-cms',href:download,download:'digitalpulse-business-card-print.pdf'},icon('bi bi-download'),api.t('Download business card'))
          )
        )
      );
    }

    function Modal({modal,setModal,api}){ if(modal.type==='search') return h(SearchModal,{api,onClose:()=>setModal(null)}); if(modal.type==='form') return h(FormModal,{api,modal,onClose:()=>setModal(null)}); if(modal.type==='basket') return h(BasketModal,{api,onClose:()=>setModal(null)}); if(modal.type==='cart') return h(CartModal,{api,onClose:()=>setModal(null)}); if(modal.type==='install') return h(InstallModal,{api,platform:modal.platform||'',onClose:()=>setModal(null)}); if(modal.type==='business-card') return h(BusinessCardModal,{api,onClose:()=>setModal(null)}); if(modal.type==='item') return h(ItemModal,{api,modal,onClose:()=>setModal(null)}); return null; }
    function SearchModal({api,onClose}){ const [q,setQ]=useState(''); const results=useMemo(()=>{ if(!q.trim()) return []; const low=q.toLowerCase(); const out=[]; ['services','features','process','pricing','faq','products','blog','gallery','pages'].forEach(group=>(api.data[group]?.items||[]).forEach((it,i)=>{ if(JSON.stringify(it).toLowerCase().includes(low)) out.push({group,i,title:it.title||it.alt||group,text:it.excerpt||it.text||it.alt||'',href:['blog','products','pages'].includes(group)?pretty(group,it):'#'+group}); })); return out.slice(0,20); },[q,api.data]); return h('div',{className:'cms-modal-backdrop',onClick:onClose}, h('div',{className:'cms-modal',onClick:e=>e.stopPropagation()}, h('div',{className:'d-flex justify-content-between align-items-center mb-3'}, h('h3',null,'Ajax search'), h('button',{className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))), h('input',{autoFocus:true,className:'form-control form-control-lg',placeholder:'Search this page...',value:q,onChange:e=>setQ(e.target.value)}), h('div',{className:'mt-3'}, results.map((r,i)=>h('a',{key:i,className:'search-result',href:r.href,onClick:onClose}, h('strong',null,r.title), h('span',{className:'badge text-bg-light ms-2'},r.group), h('p',{className:'muted mb-0'},r.text)))))); }

    function ItemModal({api,modal,onClose}){
      const item=api.data[modal.group]?.items?.[modal.index]||{};
      const path=`${modal.group}.items.${modal.index}`;
      const E=api.Editable;
      const url=absolute(pretty(modal.group,item));
      const isProduct=modal.group==='products';
      const isPortfolio=modal.group==='gallery';
      const imgs=[item.image,...(item.gallery||[])].filter((v,i,a)=>v&&a.indexOf(v)===i);
      const variations=api.productVariationList(item);
      const [active,setActive]=useState(imgs[0]||'');
      const [variationId,setVariationId]=useState(variations[0]?api.variationLabel(variations[0]):'');
      const [qty,setQty]=useState(1);
      useEffect(()=>{
        setActive(imgs[0]||'');
        setVariationId(variations[0]?api.variationLabel(variations[0]):'');
        setQty(1);
      },[modal.group,modal.index]);
      const selected=variations.find(v=>api.variationLabel(v)===variationId)||variations[0]||null;
      return h('div',{className:'cms-modal-backdrop',onClick:onClose},
        h('article',{className:'cms-modal',onClick:e=>e.stopPropagation()},
          h('button',{className:'icon-btn float-end',onClick:onClose},icon('bi bi-x-lg')),
          active&&h('img',{
            src:mediaUrl(active),
            alt:api.localizedValue(path+'.title')||item.title,
            className:cls('img-fluid rounded-4 mb-3 product-main-lightbox',isPortfolio&&'portfolio-main-lightbox',modal.group==='blog'&&'blog-main-lightbox'),
            loading:'lazy',decoding:'async',fetchPriority:'low',width:1200,height:900,
            'data-lightbox':isPortfolio?'portfolio':'product',
            'data-lightbox-src':mediaUrl(active),onError:item.fallbackImage?e=>{const img=e.currentTarget;if(!img.dataset.cmsImageFallback){img.dataset.cmsImageFallback='1';img.removeAttribute('srcset');img.src=mediaUrl(item.fallbackImage);img.dataset.lightboxSrc=mediaUrl(item.fallbackImage);}}:undefined
          }),
          imgs.length>1&&h('div',{className:cls('product-gallery',isPortfolio&&'portfolio-gallery')},
            imgs.map((src,i)=>h('img',{
              key:i,src:mediaUrl(src),alt:api.localizedValue(path+'.title')||item.title,
              loading:'lazy',decoding:'async',fetchPriority:'low',width:1200,height:900,onClick:()=>setActive(src)
            }))
          ),
          h(E,{path:path+'.title',tag:'h2',className:'fw-bold'}),
          item.date&&h('p',{className:'muted'},item.date),
          isPortfolio&&h('div',{className:'portfolio-modal-meta'},
            item.kind&&h('span',{className:'demo-pill'},api.t(item.kind)),
            item.status&&h('span',{className:cls('portfolio-status',String(item.status||'').toLowerCase().includes('development')&&'is-development')},api.t(item.status)),
            item.client&&h('span',{className:'demo-pill'},api.t(item.client)),
            ...(item.tech||[]).map((t,i)=>h('span',{key:i,className:'tech-chip'},t))
          ),
          isProduct&&h(ProductBadges,{item}),
          isProduct?h(ProductPrice,{api,item,path,variation:selected}):(item.price&&h(E,{path:path+'.price',tag:'strong',className:'text-primary d-block mb-3'})),
          isProduct&&variations.length>0&&h('div',{className:'variation-box'},
            h('label',{className:'form-label small fw-bold'},'Choose variation'),
            h('div',{className:'variation-swatches'},
              variations.map((v,i)=>h(VariationChoice,{
                api,item,variation:v,key:i,
                active:api.variationLabel(v)===variationId,
                onClick:()=>{setVariationId(api.variationLabel(v));if(v.image)setActive(v.image);}
              }))
            )
          ),
          isProduct&&h('div',{className:'qty-box'},
            h('label',{className:'form-label small fw-bold'},'Quantity'),
            h('input',{className:'form-control',type:'number',min:1,value:qty,onChange:e=>setQty(Math.max(1,Number(e.target.value)||1))})
          ),
          item.sku&&h('p',{className:'small muted mb-2'},'SKU: ',selected?.sku||item.sku),
          h(E,{path:path+'.body',tag:'div',html:true,className:'fs-5'}),
          isProduct&&Array.isArray(item.features)&&item.features.length>0&&h('ul',{className:'mt-3'},
            item.features.map((f,i)=>h('li',{key:i},h(E,{path:path+'.features.'+i})))
          ),
          isProduct&&h(ProductButtons,{api,item,variation:selected,qty}),
          isPortfolio&&item.url&&h('a',{
            className:'btn-cms portfolio-external',href:item.url,
            target:/^https?:\/\//i.test(String(item.url))?'_blank':undefined,
            rel:/^https?:\/\//i.test(String(item.url))?'noopener noreferrer':undefined,
            onClick:/^#/.test(String(item.url))?onClose:undefined
          },api.t(item.linkText||'Build something similar')),
          isPortfolio&&item.repositoryUrl&&/^https:\/\//i.test(String(item.repositoryUrl))&&h('a',{
            className:'btn-ghost portfolio-source-link',href:item.repositoryUrl,target:'_blank',rel:'noopener noreferrer'
          },icon('bi bi-github'),' ',api.t('View source code')),
          h('div',{className:'share-row'},
            shareLinks(url,item.title).map(s=>h('a',{key:s.label,href:s.url,target:'_blank',rel:'noopener'},s.label))
          )
        )
      );
    }

    function BasketModal({api,onClose}){ const cfg=api.data.products||{}; return h('div',{className:'cms-modal-backdrop',onClick:onClose}, h('div',{className:'cms-modal small',onClick:e=>e.stopPropagation()}, h('div',{className:'d-flex justify-content-between align-items-center mb-3'}, h('h3',null,cfg.basketTitle||'Quote basket'), h('button',{className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))), api.quoteItems.length===0?h('p',{className:'muted'},'Quote basket is empty.') : h(React.Fragment,null, api.quoteItems.map((it,i)=>h('div',{className:'basket-line',key:i}, h('div',null,h('strong',null,it.title), h('div',{className:'small muted'}, (it.qty||1),' × ',api.formatProductPrice(it), it.sku?` · ${it.sku}`:'', it.variationLabel?` · ${it.variationLabel}`:'')), h('button',{className:'icon-btn',onClick:()=>api.removeQuote(i)},icon('bi bi-trash')))), h('div',{className:'d-flex gap-2 mt-3'}, h('button',{className:'btn-cms flex-fill',onClick:()=>api.setModal({type:'form',formType:'quote',message:api.quoteMessage()})},cfg.basketButtonText||'Send quote request'), h('button',{className:'btn-ghost',onClick:api.clearQuote},'Clear'))))); }

    function CartModal({api,onClose}){ const cfg=api.data.products||{}; const pay=cfg.payment?.directDebit||{}; const methods=(cfg.shippingMethods||[]).filter(x=>x.enabled!==false); const codes=(cfg.discountCodes||[]).filter(x=>x.enabled!==false); const [busy,setBusy]=useState(false); const [error,setError]=useState(''); const [email,setEmail]=useState(''); const [shippingId,setShippingId]=useState(methods[0]?.id||''); const [discountCode,setDiscountCode]=useState(''); const subtotal=api.cartTotal(); const ship=methods.find(x=>x.id===shippingId); const dc=codes.find(x=>String(x.code||'').toLowerCase()===discountCode.trim().toLowerCase()); const discount=dc?(dc.type==='fixed'?Math.min(subtotal,Number(dc.value||0)):subtotal*Math.max(0,Math.min(100,Number(dc.value||0)))/100):0; const shipping=Number(ship?.price||0); const total=Math.max(0,subtotal-discount+shipping); async function checkout(){setBusy(true);setError('');try{if(api.data.settings?.abandonedCart?.enabled&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))throw new Error('Enter your email so the cart can be saved and checkout can continue.');if(api.data.settings?.abandonedCart?.enabled){await fetch('?api=abandoned-cart',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'capture',email,items:api.shopCartItems,shippingId,discountCode})})}const r=await fetch('?api=checkout-start',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({items:api.shopCartItems,shippingId,discountCode,customer:{email}})});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Checkout could not start.');if(j.mode==='order-form'){api.setModal({type:'form',formType:'order',message:api.orderMessage(j.cart),email});return;}if(j.url){location.href=j.url;return;}throw new Error('Checkout did not return a payment URL.')}catch(e){setError(e.message||'Checkout failed.')}finally{setBusy(false)}} return h('div',{className:'cms-modal-backdrop',onClick:onClose}, h('div',{className:'cms-modal small',onClick:e=>e.stopPropagation()}, h('div',{className:'d-flex justify-content-between align-items-center mb-3'}, h('h3',null,cfg.cartTitle||'Shopping cart'), h('button',{className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))), api.shopCartItems.length===0?h('p',{className:'muted'},'Cart is empty.') : h(React.Fragment,null, api.shopCartItems.map((it,i)=>h('div',{className:'basket-line',key:i}, h('div',null,h('strong',null,it.title), h('div',{className:'small muted'}, (it.qty||1),' × ',api.formatProductPrice(it), it.sku?` · ${it.sku}`:'', it.variationLabel?` · ${it.variationLabel}`:'')), h('button',{className:'icon-btn',onClick:()=>api.removeCart(i)},icon('bi bi-trash')))), h('div',{className:'cart-options mt-3'},api.data.settings?.abandonedCart?.enabled&&h(Field,{label:'Email for checkout & saved-cart reminder'},h('input',{className:'form-control',type:'email',value:email,onChange:e=>setEmail(e.target.value),placeholder:'you@example.com'})),methods.length>0&&h(Field,{label:'Delivery / fulfilment'},h('select',{className:'form-select',value:shippingId,onChange:e=>setShippingId(e.target.value)},methods.map(m=>h('option',{key:m.id,value:m.id},`${m.label} · ${api.formatMoney?api.formatMoney(m.price,cfg.currency):new Intl.NumberFormat(undefined,{style:'currency',currency:cfg.currency||'EUR'}).format(Number(m.price||0))}`)))),codes.length>0&&h(Field,{label:'Discount code'},h('input',{className:'form-control',value:discountCode,onChange:e=>setDiscountCode(e.target.value),placeholder:'Code'})),h('div',{className:'cart-summary-row'},h('span',null,'Subtotal'),h('strong',null,new Intl.NumberFormat(undefined,{style:'currency',currency:cfg.currency||'EUR'}).format(subtotal))),discount>0&&h('div',{className:'cart-summary-row'},h('span',null,'Discount'),h('strong',null,'−'+new Intl.NumberFormat(undefined,{style:'currency',currency:cfg.currency||'EUR'}).format(discount))),h('div',{className:'cart-summary-row'},h('span',null,'Shipping'),h('strong',null,new Intl.NumberFormat(undefined,{style:'currency',currency:cfg.currency||'EUR'}).format(shipping))),h('div',{className:'cart-summary-row total'},h('span',null,'Total'),h('span',null,new Intl.NumberFormat(undefined,{style:'currency',currency:cfg.currency||'EUR'}).format(total)))), pay.enabled&&h('div',{className:'payment-note'}, h('strong',null,pay.title||'Direct bank transfer'), h('p',null,pay.instructions||'Submit order and we will confirm payment details.')), error&&h('div',{className:'alert alert-danger py-2 mt-3'},error), h('div',{className:'d-flex gap-2 mt-3'}, h('button',{className:'btn-cms flex-fill',disabled:busy,onClick:checkout},busy?'Starting checkout...':(cfg.cartButtonText||'Checkout now')), h('button',{className:'btn-ghost',disabled:busy,onClick:api.clearCart},'Clear'))))); }

    function FormModal({api,modal,onClose}){
      const initialMessage=modal?.message || (modal?.formType==='quote'?api.quoteMessage():'');
      const [form,setForm]=useState({name:'',email:modal?.email||'',phone:'',message:initialMessage});
      const [msg,setMsg]=useState(''); const [captcha,setCaptcha]=useState(''); const [captchaReset,setCaptchaReset]=useState(0); const hc=api.data.settings.hcaptcha||{};
      async function submit(e){
        e.preventDefault(); setMsg('Sending...');
        const r=await fetch('?api=form',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({...form,hcaptcha:captcha,type:modal?.formType||'contact'})});
        const j=await cmsResponseJson(r); setMsg(j.error||j.message||'Sent');
        if(j.ok){ setForm({name:'',email:'',phone:'',message:''}); setCaptcha(''); setCaptchaReset(v=>v+1); if(modal?.formType==='quote') api.clearQuote(); if(modal?.formType==='order') api.clearCart(); }
      }
      const sendLabel=modal?.formType==='order'?(api.data.products?.checkoutButtonText||'Send order'):(modal?.formType==='quote'?'Send quote request':'Send');
      return h('div',{className:'cms-modal-backdrop',onClick:onClose},
        h('form',{className:'cms-modal small',onClick:e=>e.stopPropagation(),onSubmit:submit},
          h('div',{className:'d-flex justify-content-between align-items-center mb-3'},
            h('h3',null,modal?.formType==='order'?'Checkout order':(modal?.formType==='quote'?'Send quote request':'Send request')),
            h('button',{type:'button',className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))
          ),
          ['name','email','phone'].map(k=>h('input',{key:k,className:'form-control mb-3',placeholder:k.charAt(0).toUpperCase()+k.slice(1),type:k==='email'?'email':'text',value:form[k],onChange:e=>setForm({...form,[k]:e.target.value}),required:k!=='phone'})),
          h('textarea',{className:'form-control mb-3',rows:7,placeholder:'Message',value:form.message,onChange:e=>setForm({...form,message:e.target.value}),required:true}),
          h(HCaptchaWidget,{api,onToken:setCaptcha,resetKey:captchaReset,className:'hcaptcha-wrap mb-3'}),
          h('button',{className:'btn-cms w-100',disabled:hc.enabled&&!captcha},sendLabel),
          msg&&h('p',{className:'mt-3 mb-0'},msg)
        )
      );
    }

    function InstallModal({api,platform='',onClose}){ const app=api.data.settings.app||{}; const [promptEvent,setPromptEvent]=useState(window.__lpcmsInstallPrompt||null); useEffect(()=>{ const fn=()=>setPromptEvent(window.__lpcmsInstallPrompt||null); window.addEventListener('lpcms:install-ready',fn); return()=>window.removeEventListener('lpcms:install-ready',fn); },[]); const androidUrl=api.href(app.androidInstallUrl||'/?install=android'); const appleUrl=api.href(app.appleInstallUrl||'/?install=apple'); async function install(){ const target=platform||(/iphone|ipad|ipod/i.test(navigator.userAgent)?'apple':'android'); if(target!=='apple'&&promptEvent){ promptEvent.prompt(); await promptEvent.userChoice; window.__lpcmsInstallPrompt=null; setPromptEvent(null); } else { location.href=target==='apple'?appleUrl:androidUrl; } } const primaryLabel=platform==='apple'?api.t('View iPhone / iPad install steps'):promptEvent?api.t('Install on Android now'):api.t('Open install guide'); return h('div',{className:'cms-modal-backdrop',onClick:onClose}, h('div',{className:'cms-modal small',onClick:e=>e.stopPropagation()}, h('div',{className:'d-flex justify-content-between align-items-center mb-3'}, h('h3',null,api.t('Install mobile app')), h('button',{className:'icon-btn',onClick:onClose,'aria-label':'Close'},icon('bi bi-x-lg'))), h('p',{className:'muted'},api.localizedValue('settings.app.description')||api.t(app.description||'Install this website as a mobile app.')), h('div',{className:'d-grid gap-2'}, h('button',{className:'btn-cms',onClick:install}, icon(platform==='apple'?'bi bi-apple':'bi bi-download'), ' ', primaryLabel), h('a',{className:'btn-ghost text-center',href:androidUrl},icon('bi bi-google-play'),' ',api.t('Android guide')), h('a',{className:'btn-ghost text-center',href:appleUrl},icon('bi bi-apple'),' ',api.t('Apple guide'))))); }

    function CookieBanner({api}){ const data=api.data,cfg=data.settings.cookies||{}; const [show,setShow]=useState(()=>cfg.enabled && !localStorage.getItem('cms_cookie_ok')); if(!show) return null; return h('div',{className:'cookie-box'}, h('div',{className:'d-flex flex-column flex-lg-row align-items-lg-center gap-3 justify-content-between'}, h('p',{className:'mb-0'},api.localizedValue('settings.cookies.message')||cfg.message), h('div',{className:'d-flex gap-2'}, h('button',{className:'btn-ghost',onClick:()=>{localStorage.setItem('cms_cookie_ok','no');setShow(false)}},api.localizedValue('settings.cookies.declineText')||cfg.declineText||'Decline'), h('button',{className:'btn-cms',onClick:()=>{localStorage.setItem('cms_cookie_ok','yes');setShow(false);maybeLoadGA(data)}},api.localizedValue('settings.cookies.acceptText')||cfg.acceptText||'Accept')))); }

    function Field({label,children}){ return h('label',{className:'form-label w-100 mb-3'}, h('span',{className:'small fw-bold d-block mb-1'},label), children); }

    ReactDOM.createRoot(rootEl).render(h(App));
  })();
