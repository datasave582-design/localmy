/* MyLocal Hostinger data adapter
   Firebase is used only for Auth. App data is stored in Hostinger/MySQL. */
(function(){
  const listeners = new Map();
  const timers = new Map();
  const API = './api/db.php';

  function token(){
    try { return window.firebase?.auth?.().currentUser?.getIdToken?.(); } catch(e){ return null; }
  }
  async function request(url, opts={}){
    const t = await token();
    const headers = Object.assign({'Accept':'application/json'}, opts.headers||{});
    if(t) headers.Authorization = 'Bearer '+t;
    const r = await fetch(url, Object.assign({},opts,{headers}));
    let j={}; try{ j=await r.json(); }catch(e){}
    if(!r.ok || j.success===false) throw new Error(j.error||('API error '+r.status));
    return j;
  }
  function deepSet(root, parts, value){
    if(!parts.length) return value;
    const out = (root && typeof root==='object') ? structuredClone(root) : {};
    let cur=out;
    parts.forEach((p,i)=>{ if(i===parts.length-1) cur[p]=value; else { if(!cur[p]||typeof cur[p]!=='object') cur[p]={}; cur=cur[p]; } });
    return out;
  }
  class Snap{
    constructor(v){this._v=v;}
    val(){return this._v;}
    exists(){return this._v!==null && this._v!==undefined;}
  }
  class Ref{
    constructor(path='', query={}){this.path=path.replace(/^\/|\/$/g,'');this.query=query;this._pending=null;}
    child(k){return new Ref(this.path+'/'+String(k),this.query);}
    orderByChild(k){return new Ref(this.path,Object.assign({},this.query,{orderByChild:k}));}
    equalTo(v){return new Ref(this.path,Object.assign({},this.query,{equalTo:v}));}
    limitToLast(v){return new Ref(this.path,Object.assign({},this.query,{limitToLast:v}));}
    async once(){
      const q=new URLSearchParams({path:this.path}); Object.entries(this.query).forEach(([k,v])=>q.set(k,String(v)));
      const j=await request(API+'?'+q.toString()); return new Snap(j.value);
    }
    async set(v){await request(API,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'set',path:this.path,value:v})}); return v;}
    async update(v){await request(API,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'update',path:this.path,value:v})}); return null;}
    async remove(){await request(API,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'remove',path:this.path})});}
    push(v){
      const self=this;
      const key='-'+Date.now().toString(36)+Math.random().toString(36).slice(2,10);
      const child=new Ref(self.path+'/'+key);
      child.key=key;
      if(arguments.length) child._pending=child.set(v);
      return child;
    }
    async transaction(fn){const s=await this.once();const next=fn(s.val());if(next===undefined)return {committed:false,snapshot:s};await this.set(next);return {committed:true,snapshot:new Snap(next)};}
    then(resolve,reject){ return (this._pending||Promise.resolve(this)).then(()=>resolve?resolve(this):this,reject); }
    catch(reject){ return (this._pending||Promise.resolve(this)).catch(reject); }
    on(event, handler){
      if(event!=='value') return handler;
      const id=Symbol(); const run=async()=>{try{handler(await this.once());}catch(e){console.warn('Hostinger listener:',e.message);}};
      listeners.set(id,{ref:this,handler}); run();
      const timer=setInterval(run,8000); timers.set(id,timer);
      return handler;
    }
    off(event,handler){
      for(const [id,x] of listeners){if(x.ref===this && (!handler||x.handler===handler)){clearInterval(timers.get(id));timers.delete(id);listeners.delete(id);}}
    }
  }
  const HostingerDB={ref:(p)=>new Ref(p)};
  window.HostingerDB=HostingerDB;
  window.HostingerDBTimestamp=()=>Date.now();
  // Compatibility for the existing app's firebase.database.ServerValue.TIMESTAMP usage.
  const install=()=>{try{ if(window.firebase){ window.firebase.database=function(){return HostingerDB;}; window.firebase.database.ServerValue={TIMESTAMP:{'.sv':'timestamp'}}; } }catch(e){} };
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',install); else install();
})();
