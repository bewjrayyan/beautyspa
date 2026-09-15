function ie(t,a){return getComputedStyle(document.documentElement).getPropertyValue(t).trim()||a}function e(t,a={}){const s=String(t).split(".");let i=window.IMMA_CENTRAL&&window.IMMA_CENTRAL.i18n||{};for(const r of s)if(i&&typeof i=="object"&&r in i)i=i[r];else{i=null;break}let l=typeof i=="string"?i:t;return Object.keys(a||{}).forEach(r=>{l=l.replace(new RegExp(":"+r,"g"),String(a[r]))}),l}const o=(t,a=document)=>{const s=typeof a=="string"?document.querySelector(a):a||document;return s?s.querySelector(t):null},T=(t,a=document)=>{const s=typeof a=="string"?document.querySelector(a):a||document;return s?[...s.querySelectorAll(t)]:[]},ln={leads:[]},m=window.IMMA_CENTRAL||{};let c={view:"overview",branch:String(o("#branchScope")&&o("#branchScope").value||"all"),period:String(o("#periodScope")&&o("#periodScope").value||m.metrics&&m.metrics.period&&m.metrics.period.key||""),leadSearch:"",leadStatus:"all",leadBeautician:"all",leadBranch:"all",leadPage:1,leadPerPage:10,leadMonth:(function(){const t=new Date;return t.getFullYear()+"-"+String(t.getMonth()+1).padStart(2,"0")})(),leadCalYear:null,followBucket:"all",followSearch:"",followPage:1,importTab:"paste",paymentTab:"queue",paymentSearch:"",paymentCustomerId:null,paymentCustomerLabel:"",paymentBeautician:"all",paymentBranch:"all",paymentPage:1,customerSegment:"all",customerSearch:"",customerBranch:"all",customerPage:1,walletSegment:"all",walletSearch:"",walletTier:"all",walletPage:1,walletCustomerId:null,checkinStatus:"live",checkinSearch:"",checkinBranch:"all",checkinBeautician:"all",checkinPage:1,checkinDate:m.today||new Date().toLocaleDateString("en-CA"),checkinScope:"day",clearanceState:"waiting",clearanceSearch:"",clearanceBranch:"all",clearanceBeautician:"all",clearancePage:1};const E=o("#viewRoot");let C=m.metrics||null,$a=C?JSON.stringify([c.branch,c.period]):null,va=0,G=[],wa={raw:0,unique:0,duplicates:0,existing:0,converted:0,conversion_pct:0,all_time:{raw:0,unique:0,duplicates:0,existing:0,converted:0,conversion_pct:0}},B={statuses:[],sources:[],beauticians:[],branches:[],months:[]},Ze=!1,Wa=null,Ce=null,I=new Set,F="",O="",Le=[],ka={queue:0,overdue:0,due_today:0,no_response:0,lost:0},xe={buckets:[],beauticians:[],branches:[],statuses:[]},ea=!1,Ya=null,be=[],Ba={pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0},fe={statuses:[],beauticians:[],branches:[]},aa=!1,Ka=null,Sa={current_page:1,last_page:1,total:0},ge=[],ja={total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0},Pe={segments:[],branches:[]},ta=!1,za=null,De={current_page:1,last_page:1,total:0},qe=[],Ea={members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0},$t={segments:[],tiers:[]},oe=!1,Ye=0,He=!1,Ja=null,Aa={current_page:1,last_page:1,total:0},de=[],Na={live:0,scheduled:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0},Ma={statuses:[],beauticians:[],branches:[]},pe=!1,Ke=0,Re=!1,Ga=null,Da={current_page:1,last_page:1,total:0},ye=null,Ta=0,ue=[],qa={waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0},Ca={states:[],beauticians:[],branches:[]},me=!1,ze=0,Ue=!1,Xa=null,Ha={current_page:1,last_page:1,total:0};function q(t,a){return String(t||"").replace("__ID__",String(a))}function N(t=!0){const a={Accept:"application/json","X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":m.csrf||document.querySelector('meta[name="csrf-token"]')?.content||""};return t&&(a["Content-Type"]="application/json"),a}async function A(){const t=m.leadsUrl||"";if(!t){g(e("workspace.load_error"));return}const a=new URLSearchParams;c.leadSearch&&a.set("q",c.leadSearch),c.leadStatus&&c.leadStatus!=="all"&&a.set("status",c.leadStatus);const s=c.leadBranch!=="all"?c.leadBranch:c.branch||"all";s&&s!=="all"&&a.set("branch",s),c.leadBeautician&&c.leadBeautician!=="all"&&a.set("beautician",c.leadBeautician),c.leadMonth&&c.leadMonth!=="all"&&a.set("month",c.leadMonth),a.set("page",String(c.leadPage||1)),a.set("per_page",String(c.leadPerPage||10)),I.clear(),F="",O="",la(),Ze=!0,tt();try{const i=await fetch(`${t}?${a.toString()}`,{headers:N(!1),credentials:"same-origin"});if(!i.ok)throw new Error("leads "+i.status);const l=await i.json();Be=l.meta||{},[10,50,100,200].includes(Number(Be.per_page))&&(c.leadPerPage=Number(Be.per_page)),G=Array.isArray(l.data)?l.data:[],wa=l.meta&&l.meta.summary||wa,B=l.filters||B,ln.leads=G}catch(i){console.error(i),g(e("workspace.load_error"))}finally{Ze=!1,c.view==="leads"&&(Lt(),tt(),ia(),Fe())}}function v(t){return Number(t||0).toLocaleString("en-MY")}function L(t){return"RM"+Number(t||0).toLocaleString("en-MY",{maximumFractionDigits:0})}function z(t){return Number(t||0).toLocaleString("en-MY",{maximumFractionDigits:1})+"%"}function n(t){return String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;")}function Q(t,a=""){const s=Number(t||0),i=Math.abs(s).toFixed(1)+a;return s>0?`↑ +${i} ${e("overview.vs_prev_period")}`:s<0?`↓ -${i} ${e("overview.vs_prev_period")}`:`→ 0${a} ${e("overview.vs_prev_period")}`}function se(t,a){return`${e("overview.target")}: ${a}`}async function na(){const t=m.metricsUrl||"",a=++va,s=c.branch||"all",i=c.period||"",l=JSON.stringify([s,i]),r=new URLSearchParams({branch:s,period:i});try{if(!t)throw new Error("Missing metrics endpoint");const d=await fetch(`${t}?${r.toString()}`,{headers:N(!1),credentials:"same-origin"});if(!d.ok)throw new Error("metrics "+d.status);const u=await d.json();if(a!==va||l!==JSON.stringify([c.branch,c.period]))return;if(!u.metrics)throw new Error("Missing metrics payload");C=u.metrics,$a=l,m.metrics=C,ua(),c.view==="overview"&&Mt(),c.view==="sales"&&Yt()}catch{if(a!==va||l!==JSON.stringify([c.branch,c.period]))return;["overview","sales"].includes(c.view)&&$a!==l?(E.innerHTML=`<section class="card"><div class="pay-empty" role="alert"><strong>${n(e("overview.metrics_error"))}</strong><button type="button" class="btn" id="retryMetrics">${n(e("reporting.refresh"))}</button></div></section>`,o("#retryMetrics").onclick=()=>na()):g(e("overview.metrics_error"))}}function ae(t,a={}){const s=Math.max(1,Number(a.last_page)||1),i=Math.max(1,Math.min(s,Number(a.current_page)||1)),l=s<=5?Array.from({length:s},(p,_)=>_+1):[...new Set([1,i-1,i,i+1,s].filter(p=>p>=1&&p<=s))].sort((p,_)=>p-_),r=(p,_,h="")=>`<button type="button" class="central-pagination__button" data-page="${p}" ${h}>${_}</button>`;let d="",u=0;return l.forEach(p=>{u&&p-u>1&&(d+='<span class="central-pagination__ellipsis" aria-hidden="true">…</span>'),d+=r(p,String(p),`aria-label="${n(e("pagination.page",{page:p}))}" ${p===i?'aria-current="page" disabled':""}`),u=p}),`<nav class="central-pagination" data-pager="${t}" aria-label="${n(e("pagination.label"))}">
    ${r(i-1,"‹",`id="${t}Prev" aria-label="${n(e("operations.previous"))}" ${i<=1?"disabled":""}`)}
    ${d}
    ${r(i+1,"›",`id="${t}Next" aria-label="${n(e("operations.next"))}" ${i>=s?"disabled":""}`)}
  </nav>`}function te(t,a){const s=o(`[data-pager="${t}"]`);s&&T("button[data-page]",s).forEach(i=>i.onclick=async()=>{if(i.disabled)return;const l=T("button:not(:disabled)",s);l.forEach(r=>r.disabled=!0),s.setAttribute("aria-busy","true");try{await a(Number(i.dataset.page))}finally{s.isConnected&&(l.forEach(r=>r.disabled=!1),s.removeAttribute("aria-busy"))}})}let Be={},wt={},Qa=1;function R(t){return"RM"+Number(t).toLocaleString("en-MY")}function x(t){const a=String(t??""),s=a.toUpperCase();let i="gray";return s.includes("VERIFIED")||s.includes("PAID")||s==="CONVERTED"||s==="COMPLETED"||s.includes("BANK CHECKED")||s.includes("PROOF")?i="success":s.includes("FOLLOW")||s.includes("PENDING")||s.includes("REVIEW")||s==="BOOKING"||s==="CLAIMED"||s.includes("PROCESSING")||s.includes("DECLARED")?i="warning":s.includes("HOLD")||s.includes("LOST")||s.includes("NO RESPONSE")||s.includes("CANCEL")||s.includes("REFUND")?i="danger":s==="NEW"&&(i="blue"),`<span class="badge ${i}"><span class="status-dot"></span>${n(a)}</span>`}function _e(t,a,s=""){return`<div class="page-head"><div><h1 class="page-title">${t}</h1><div class="page-subtitle">${a}</div></div><div class="page-actions">${s}</div></div>`}function j(t,a,s,i,l,r="blue",d=null,u="",p=""){const _=n(u||a),h=n(a),b=n(s),k=n(i),f=n(l),w=n(p),$=/^[↑+]/.test(String(i).trim())||/above|\+|up/i.test(String(i)),y=/^[↓-]/.test(String(i).trim())||/below|down/i.test(String(i))?"down":$?"up":"flat";return`<div class="kpi-card kpi-card--${r}">
    <div class="kpi-card__head">
      <div class="kpi-card__icon" aria-hidden="true">${t}</div>
      <div class="kpi-label" title="${_}">${h}</div>
    </div>
    <div class="kpi-value mono">${b}</div>
    <div class="kpi-card__foot">
      <span class="kpi-trend kpi-trend--${y}">${k}</span>
      <span class="kpi-target">${f}</span>
    </div>
    ${p?`<div class="kpi-spark" id="${w}"></div>`:""}
    ${d!==null?`<div class="progress kpi-card__bar"><span style="width:${Math.min(100,d)}%"></span></div>`:""}
  </div>`}function Se(t){const a={database:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v6c0 1.7 3.1 3 7 3s7-1.3 7-3V5"/><path d="M5 11v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"/></svg>',new:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="4"/><path d="M3 20c.7-4 2.7-6 6-6 2.1 0 3.7.8 4.8 2.3M18 8v6M15 11h6"/></svg>',repeated:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7h-9a5 5 0 0 0-5 5v1"/><path d="m17 4 3 3-3 3M4 17h9a5 5 0 0 0 5-5v-1"/><path d="m7 20-3-3 3-3"/></svg>',customers:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c.5-4 2.5-6 6-6s5.5 2 6 6M16 5.5a3 3 0 0 1 0 5.8M17 14c2.4.5 3.7 2.4 4 5"/></svg>',conversion:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 18 10 12l4 3 6-8"/><path d="M15 7h5v5"/><circle cx="6" cy="6" r="2"/></svg>'};return a[t]||a.database}function Me(t,a,s,i,l,r,d="blue"){return`<div class="kpi-card kpi-card--${d} lead-kpi-card lead-kpi-card--${d}">
    <div class="lead-kpi-card__glow" aria-hidden="true"></div>
    <div class="kpi-card__head">
      <div class="kpi-card__icon" aria-hidden="true">${t}</div>
      <div class="kpi-label">${n(a)}</div>
    </div>
    <div class="lead-kpi__value">
      <strong>${n(s)}</strong>
      <span>${n(i)}</span>
    </div>
    <div class="kpi-card__foot">
      <span class="kpi-trend kpi-trend--flat">${n(l)}</span>
      <span class="kpi-target lead-kpi__detail">${n(r)}</span>
    </div>
  </div>`}function Te(t,a,s,i=""){const l=i?` trade-pane__chart--${i}`:"";return`<section class="trade-pane">
    <div class="trade-pane__head">
      <div>
        <div class="trade-pane__eyebrow">${e("trade.section")}</div>
        <div class="trade-pane__title">${e(t)}</div>
        <div class="trade-pane__sub">${e(a)}</div>
      </div>
    </div>
    <div class="trade-pane__chart${l}" id="${s}"></div>
  </section>`}function rn(t=new Date){const a=["JAN","FEB","MAC","APR","MEI","JUN","JUL","OGOS","SEPT","OKT","NOV","DIS"];return`${String(t.getDate()).padStart(2,"0")} ${a[t.getMonth()]} ${t.getFullYear()}`}function cn(){const t=C||{},a=t.kpis||{},s=t.targets||{},i=Array.isArray(t.beauticians)?t.beauticians:[],l=Number(a.new_buyers||0),r=Number(s.leads||0),d=r>0?Math.round(l/r*1e3)/10:0,u=!!(t.period&&t.period.key),p=t.period&&t.period.label?n(t.period.label):rn(),_=t.slogan||e("daily.slogan"),h=t.quote||e("daily.quote");return`<section class="daily-hero">
    <div class="daily-hero__top">
      <div class="daily-hero__copy">
        <div class="daily-hero__eyebrow">${e("daily.eyebrow")}</div>
        <h2 class="daily-hero__title">${e("daily.title")}</h2>
        <div class="daily-hero__meta">
          <span class="daily-hero__date">${p}</span>
          <span class="daily-hero__pill">${e("daily.live")}</span>
        </div>
        <p class="daily-hero__quote">${n(_)} -- "${n(h)}"</p>
      </div>
    </div>
    <div class="daily-hero__stats">
      <div class="daily-stat daily-stat--primary">
        <div class="daily-stat__label">${e(u?"overview.kpi_unique_leads":"daily.leads_today")}</div>
        <div class="daily-stat__value">${v(l)}</div>
        <div class="daily-stat__sub">${e("daily.beauticians_active",{count:i.length||0})}</div>
      </div>
      <div class="daily-stat">
        <div class="daily-stat__label">${e("daily.month_progress")}</div>
        <div class="daily-stat__value daily-stat__value--sm">${v(l)} / ${v(r)}</div>
        <div class="daily-stat__sub">${e("daily.of_monthly_target",{pct:d})}</div>
        <div class="progress daily-stat__bar"><span style="width:${Math.min(100,d)}%"></span></div>
      </div>
    </div>
  </section>`}const Za=[["#1d4ed8","#60a5fa"],["#9a3412","#f59e0b"],["#065f46","#34d399"],["#6d28d9","#a78bfa"],["#be123c","#fb7185"],["#0e7490","#22d3ee"],["#a16207","#facc15"]];function on(t){const a=String(t||"");let s=0;for(let l=0;l<a.length;l++)s=s*31+a.charCodeAt(l)>>>0;const i=Za[Math.abs(s)%Za.length];return`linear-gradient(145deg, ${i[0]}, ${i[1]})`}function dn(t){const a=String(t||"").trim().split(/\s+/).filter(Boolean);return((a[0]?.[0]||"")+(a[1]?.[0]||"")).toUpperCase()}function pn(){const t=C||{},a=!!(t.period&&t.period.key),s=Array.isArray(t.beauticians)?t.beauticians:[],i=Array.isArray(t.beauticians)?t.beauticians.length:0;Math.max(1,Number(t.beautician_count)||i||1);const l=Number(t.targets&&t.targets.beautician_leads||0)||112,r=[...s].sort((h,b)=>b.leads-h.leads),d=t.period&&t.period.label?n(t.period.label):e("common.this_month"),u=r.reduce((h,b)=>h+(Number(b.leads)||0),0),p=["🥇","🥈","🥉"],_=r.map((h,b)=>{const k=Number(h.leads)||0,f=Number(h.target)||l,w=f>0?Math.round(k/f*100):0,$=u>0?Math.max(4,Math.round(k/u*100)):0,S=b===0?"gold":b===1?"silver":b===2?"bronze":"",y=w>=100?"is-hit":w>=75?"is-close":"is-low",M=n(String(h.name||"")),P=b<3?`<span class="rank rank--medal rank--${S}" title="${e("daily.rank_title",{n:b+1})}">${p[b]}</span>`:`<span class="rank">${b+1}</span>`;return`<tr class="daily-row${S?` daily-row--${S}`:""}">
      <td class="daily-rank-cell">${P}</td>
      <td>
        <div class="person-cell">
          <div class="mini-avatar leader-avatar${S?` mini-avatar--${S}`:""}" style="background:${on(h.name)}">${dn(h.name)}</div>
          <div class="person-cell__text">
            <strong>${M}${b===0?` <span class="leader-tag">${e("daily.leader")}</span>`:""}</strong>
            <small>${e("daily.target_month",{count:f})}<i class="daily-dot"></i>${d}</small>
          </div>
        </div>
      </td>
      <td class="daily-num-cell"><strong class="daily-num" title="${k.toLocaleString()} ${e("daily.leads")}">${k.toLocaleString()}</strong></td>
      <td>
        <div class="share-cell">
          <div class="share-cell__track"><span style="width:${Math.min(100,$)}%"></span></div>
          <strong>${$}%</strong>
        </div>
      </td>
      <td><span class="daily-target">${f}</span></td>
      <td>
        <div class="achieve">
          <strong class="achieve__pct ${y}">${w}%</strong>
          <div class="progress achieve__bar"><span class="achieve__fill ${y}" style="width:${Math.min(100,w)}%"></span></div>
        </div>
      </td>
    </tr>`}).join("")||`<tr><td colspan="6"><div class="empty"><strong>${e("overview.no_beautician_data")}</strong></div></td></tr>`;return`<div class="grid daily-split">
    <section class="card daily-board">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${e(a?"daily.rank_eyebrow":"daily.ranking")}</div>
          <div class="daily-panel__title">${e("daily.leaderboard")}</div>
          <div class="daily-panel__sub">${e(a?"daily.rank_sub_month":"daily.rank_sub",{date:d})}</div>
        </div>
        <button class="btn small soft" data-jump="beauticians">${e("common.full_view")}</button>
      </div>
      <div class="table-wrap daily-board__table">
        <table class="data-table daily-table">
          <thead>
            <tr>
              <th>${e("daily.col_rank")}</th>
              <th>${e("daily.col_beautician")}</th>
              <th>${e(a?"daily.col_leads":"daily.col_today")}</th>
              <th>${e("daily.col_share")}</th>
              <th>${e("daily.col_target")}</th>
              <th>${e("daily.col_ach")}</th>
            </tr>
          </thead>
          <tbody>${_}</tbody>
        </table>
      </div>
      <div class="daily-motto">${e("daily.motto")}</div>
    </section>
    <section class="card daily-charts">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${e("daily.distribution")}</div>
          <div class="daily-panel__title">${e("daily.by_beautician")}</div>
          <div class="daily-panel__sub">${e(a?"daily.leads_share_period":"daily.daily_share",a?{period:d}:{})}</div>
        </div>
      </div>
      <div class="chart-wrap daily-charts__bar"><canvas id="dailyLeadChart"></canvas></div>
      <div class="daily-charts__divider">
        <div class="daily-panel__eyebrow">${e("daily.trend")}</div>
        <div class="daily-panel__title daily-panel__title--sm">${e(a?"daily.trend_period":"daily.trend_7d",a?{period:d}:{})}</div>
      </div>
      <div class="chart-wrap daily-charts__trend"><canvas id="dailyTrendChart"></canvas></div>
    </section>
  </div>`}function La(t,a,s,i,l,r){const d=Math.min(r,i/2,l/2);t.beginPath(),t.moveTo(a+d,s),t.arcTo(a+i,s,a+i,s+l,d),t.arcTo(a+i,s+l,a,s+l,d),t.arcTo(a,s+l,a,s,d),t.arcTo(a,s,a+i,s,d),t.closePath()}function kt(){const t=o("#dailyLeadChart");if(!t)return;Ve(t);const a=t.getContext("2d"),s=C||{},i=Array.isArray(s.beauticians)?s.beauticians:[],r=[...i.length?i.map(y=>({name:String(y.name||""),leads:Number(y.leads||0)})):[]].sort((y,M)=>M.leads-y.leads),d=r.map(y=>y.name),u=r.map(y=>y.leads),p=t.clientWidth,_=t.clientHeight,h={l:28,r:10,t:28,b:42},b=Math.max(...u,1)*1.2,k=Math.max(6,Math.min(12,p/60)),f=Math.max(14,(p-h.l-h.r-k*(u.length-1))/u.length),w=ie("--navy","#1d4ed8"),$=ie("--rose","#0ea5e9");a.fillStyle="#f8fafc",La(a,0,0,p,_,12),a.fill(),a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let y=0;y<4;y++){const M=h.t+y*((_-h.t-h.b)/3);a.beginPath(),a.moveTo(h.l,M),a.lineTo(p-h.r,M),a.stroke()}const S=[["#1d4ed8","#38bdf8"],["#2563eb","#7dd3fc"],["#0284c7","#67e8f9"]];u.forEach((y,M)=>{const P=h.l+M*(f+k),H=Math.max(4,y/b*(_-h.t-h.b)),he=_-h.b-H,[W,nn]=S[Math.min(M,2)]||[w,$],ma=a.createLinearGradient(0,he,0,_-h.b);ma.addColorStop(0,M<3?nn:$),ma.addColorStop(1,M<3?W:"#93c5fd"),a.fillStyle=ma,La(a,P,he,f,H,8),a.fill(),a.fillStyle="#0f172a",a.font="700 12px Poppins",a.textAlign="center",a.fillText(String(y),P+f/2,he-8);const sn=d[M].length>7?d[M].slice(0,6)+"…":d[M];a.fillStyle="#475569",a.font="600 10px Poppins",a.fillText(sn,P+f/2,_-14)})}function St(){const t=o("#dailyTrendChart");if(!t)return;Ve(t);const a=t.getContext("2d"),s=C||{},i=s.leads_trend&&Array.isArray(s.leads_trend.actual)?s.leads_trend:null,l=i?[...i.actual]:[],r=i&&Array.isArray(i.labels)?i.labels:Array.from({length:l.length},($,S)=>S===l.length-1?"Today":"D-"+(l.length-1-S)),d=t.clientWidth,u=t.clientHeight,p={l:28,r:14,t:22,b:28},_=Math.max(...l,1)*1.15,h=$=>p.l+$*((d-p.l-p.r)/Math.max(l.length-1,1)),b=$=>u-p.b-$/_*(u-p.t-p.b),k=ie("--navy","#1d4ed8"),f=ie("--rose","#0ea5e9");a.fillStyle="#f8fafc",La(a,0,0,d,u,12),a.fill(),a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let $=0;$<3;$++){const S=p.t+$*((u-p.t-p.b)/2);a.beginPath(),a.moveTo(p.l,S),a.lineTo(d-p.r,S),a.stroke()}const w=a.createLinearGradient(0,p.t,0,u-p.b);w.addColorStop(0,"rgba(14,165,233,.28)"),w.addColorStop(1,"rgba(37,99,235,.02)"),a.beginPath(),a.moveTo(h(0),u-p.b),l.forEach(($,S)=>a.lineTo(h(S),b($))),a.lineTo(h(l.length-1),u-p.b),a.closePath(),a.fillStyle=w,a.fill(),a.beginPath(),l.forEach(($,S)=>S?a.lineTo(h(S),b($)):a.moveTo(h(S),b($))),a.strokeStyle=k,a.lineWidth=2.75,a.lineJoin="round",a.lineCap="round",a.stroke(),l.forEach(($,S)=>{const y=h(S),M=b($);a.beginPath(),a.arc(y,M,5,0,Math.PI*2),a.fillStyle="#fff",a.fill(),a.lineWidth=2.5,a.strokeStyle=f,a.stroke(),a.beginPath(),a.arc(y,M,2.2,0,Math.PI*2),a.fillStyle=k,a.fill(),a.fillStyle="#0f172a",a.font="700 10px Poppins",a.textAlign="center",a.fillText(String($),y,M-10),a.fillStyle="#64748b",a.font="600 10px Poppins",a.fillText(r[S],y,u-10)})}function un(){const t="company_target",a=C||{},s=a.targets||{},i=Math.max(1,Number(a.beautician_count)||1),l=Number(s.leads)||0,r=Number(s.conv_pct)||0,d=Number(s.buyers)||0,u=Number(s.avg_sale)||0,p=Number(s.sales)||0,_=Number(s.beautician_leads)||112,h=Math.round(_*r/100),b=Math.round(h*u),k=M=>{const P=Number(M||0);return Math.abs(P)>=1e6?"RM"+(P/1e6).toFixed(1).replace(/\.0$/,"")+"M":Math.abs(P)>=1e3?"RM"+(P/1e3).toFixed(1).replace(/\.0$/,"")+"k":L(P)},f=v(l),w=z(r),$=v(d),S=L(u),y=k(p);return`<section class="company-target">
    <div class="company-target__hero">
      <div class="company-target__hero-copy">
        <div class="company-target__eyebrow">${e(t+".eyebrow")}</div>
        <h2 class="company-target__title">${e(t+".title",{leadgoal:f})}</h2>
        <p class="company-target__desc">${e(t+".desc",{leadgoal:f,convgoal:w,buyergoal:$,avggoal:S,salesgoal:y})}</p>
      </div>
      <div class="company-target__hero-goal">
        <div class="company-target__goal-label">${e(t+".sales_goal")}</div>
        <div class="company-target__goal-value">${L(p)}</div>
        <div class="company-target__goal-sub">${e(t+".goal_sub",{beaucount:i})}</div>
      </div>
    </div>

    <div class="company-target__chain" aria-label="${e(t+".chain_aria")}">
      ${[[f,e(t+".step_leads"),e(t+".step_leads_meta")],[w,e(t+".step_conv"),e(t+".step_conv_meta")],[$,e(t+".step_buyers"),e(t+".step_buyers_meta")],[S,e(t+".step_avg"),e(t+".step_avg_meta")],[y,e(t+".step_sales"),e(t+".step_sales_meta")]].map((M,P)=>`
        ${P?'<div class="company-target__arrow" aria-hidden="true">↓</div>':""}
        <div class="company-target__step ${P===4?"company-target__step--goal":""}">
          <div class="company-target__step-value">${M[0]}</div>
          <div class="company-target__step-label">${M[1]}</div>
          <div class="company-target__step-meta">${M[2]}</div>
        </div>
      `).join("")}
    </div>

    <div class="grid company-target__kpis">
      <article class="ct-card ct-card--kpi1">
        <div class="ct-card__head">
          <span class="ct-card__badge">${e(t+".kpi1_badge")}</span>
          <span class="ct-card__icon">🎯</span>
        </div>
        <h3 class="ct-card__title">${e(t+".kpi1_title",{convgoal:w})}</h3>
        <p class="ct-card__text">${e(t+".kpi1_text",{leadgoal:f,buyergoal:$})}</p>
        <div class="ct-card__math">
          <div><span>${e(t+".kpi1_leads")}</span><strong>${f}</strong></div>
          <div><span>×</span><strong>${w}</strong></div>
          <div><span>${e(t+".kpi1_buyers")}</span><strong>${$}</strong></div>
        </div>
      </article>

      <article class="ct-card ct-card--kpi2">
        <div class="ct-card__head">
          <span class="ct-card__badge">${e(t+".kpi2_badge")}</span>
          <span class="ct-card__icon">💰</span>
        </div>
        <h3 class="ct-card__title">${e(t+".kpi2_title",{avggoal:S})}</h3>
        <p class="ct-card__text">${e(t+".kpi2_text",{buyergoal:$,avggoal:S,salesgoal:y})}</p>
        <div class="ct-card__math">
          <div><span>${e(t+".kpi2_buyers")}</span><strong>${$}</strong></div>
          <div><span>×</span><strong>${S}</strong></div>
          <div><span>${e(t+".kpi2_sales")}</span><strong>${y}</strong></div>
        </div>
      </article>
    </div>

    <div class="company-target__beauty-head">
      <div>
        <div class="daily-panel__eyebrow">${e(t+".per_beautician")}</div>
        <div class="daily-panel__title">${e(t+".per_title")}</div>
        <div class="daily-panel__sub">${e(t+".per_sub",{beaucount:i})}</div>
      </div>
    </div>
    <div class="grid company-target__beauty">
      <article class="ct-card">
        <div class="ct-card__head"><span class="ct-card__badge">${e(t+".badge_leads")}</span></div>
        <div class="ct-card__big">${v(_)}</div>
        <div class="ct-card__unit">${e(t+".unit_leads")}</div>
        <p class="ct-card__text">${e(t+".text_leads",{leadgoal:f,beaucount:i,leadtarget:v(_)})}</p>
      </article>
      <article class="ct-card">
        <div class="ct-card__head"><span class="ct-card__badge">${e(t+".badge_convert")}</span></div>
        <div class="ct-card__big">±${v(h)}</div>
        <div class="ct-card__unit">${e(t+".unit_convert")}</div>
        <p class="ct-card__text">${e(t+".text_convert",{leadtarget:v(_),convgoal:w,buytarget:v(h)})}</p>
      </article>
      <article class="ct-card ct-card--accent">
        <div class="ct-card__head"><span class="ct-card__badge">${e(t+".badge_sales")}</span></div>
        <div class="ct-card__big">${k(b)}</div>
        <div class="ct-card__unit">${e(t+".unit_sales")}</div>
        <p class="ct-card__text">${e(t+".text_sales",{buytarget:v(h),avggoal:S,salarget:L(b),beaucount:i,compact:k(b),total:y})}</p>
      </article>
    </div>
  </section>`}function Mt(){window.IMMA_TRADE&&IMMA_TRADE.dispose();const t=C||{},a=t.kpis||{},s=a.vs_prev||{},i=t.targets||{},l=t.dual||{},r=Number(l.sales_pct||0),d=Number(i.sales||0),u=Number(a.sales||0),p=u-d,_=n(t.period&&t.period.label||e("common.this_month")),h=t.ops||{},b=h.checkin||{},k=h.clearance||{},f=h.payments||{},w=e("ops.value_customers",{count:v(b.live||0)}),$=e("ops.meta_checkin",{waiting:v(b.waiting||0),treatment:v(b.in_treatment||0)}),S=e("ops.value_customers",{count:v(k.queue||0)}),y=e("ops.meta_clearance",{waiting:v(k.waiting||0),blocked:v(k.blocked||0)}),M=e("ops.value_pending",{count:v(f.queue||0)}),P=e("ops.meta_payments",{processing:v(f.processing||0),hold:v(f.hold||0)});E.innerHTML=`${_e(e("overview.title"),e("overview.subtitle"),`<button class="btn soft">${_}</button><button class="btn primary" data-jump="leads">${e("common.open_leads")}</button>`)}
  ${un()}
  <div class="trade-grid trade-grid--2">
    ${Te("trade.dual_title","trade.dual_sub","tradeDualRing","sm")}
    ${Te("trade.equity_title","trade.equity_sub","tradeEquity","lg")}
  </div>
  <div class="section-label">${e("overview.actual_label")}</div>
  <div class="grid kpi-grid">
    ${j("♙",e("overview.kpi_unique_leads"),v(a.new_buyers),Q(s.new_buyers,"%"),se(e("overview.kpi_unique_leads"),v(i.leads||0)),"rose",Math.min(100,(a.new_buyers||0)/Math.max(1,i.leads||0)*100),"","sparkLeads")}
    ${j("▣",e("overview.kpi_buyers"),v(a.buyers),Q(s.buyers,"%"),se(e("overview.kpi_buyers"),v(i.buyers||0)),"blue",Math.min(100,(a.buyers||0)/Math.max(1,i.buyers||0)*100),"","sparkBuyers")}
    ${j("%",e("overview.kpi_conv_rate"),z(a.new_buyer_share_pct),Q(s.new_buyer_share_pct,"pp"),se(e("overview.kpi_conv_rate"),z(i.conv_pct||0)),"green",Math.min(100,Number(a.new_buyer_share_pct||0)),"","sparkConv")}
    ${j("◫",e("overview.kpi_sales"),L(a.sales),Q(s.sales,"%"),se(e("overview.kpi_sales"),L(d)),"rose",Math.min(100,r),"","sparkSales")}
    ${j("▥",e("overview.kpi_avg_sale"),L(a.avg_sale),Q(s.avg_sale,"%"),se(e("overview.kpi_avg_sale"),L(i.avg_sale||0)),"purple",Math.min(100,(a.avg_sale||0)/Math.max(1,i.avg_sale||0)*100),"","sparkAvg")}
  </div>
  <div class="trade-grid trade-grid--2" style="margin-top:12px">
    ${Te("trade.waterfall_title","trade.waterfall_sub","tradeWaterfall")}
    <section class="card">
      <div class="card-title-row"><div class="card-title">▥ ${e("overview.lead_status")}</div><button class="btn small soft">${_}</button></div>
      <div style="display:grid;grid-template-columns:180px 1fr;gap:14px;align-items:center">
        <div class="chart-wrap" style="height:180px"><canvas id="donutChart"></canvas></div>
        <div class="legend" id="leadLegend"></div>
      </div>
    </section>
  </div>
  <div class="trade-grid trade-grid--2">
    ${Te("trade.conversion_title","trade.conversion_sub","tradeConversion")}
    ${Te("trade.heatmap_title","trade.heatmap_sub","tradeHeatmap")}
  </div>

  <div class="grid split-60" style="margin-top:12px">
    <section class="card sales-card">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${e("overview.revenue")}</div>
          <div class="daily-panel__title">${e("overview.monthly_sales")}</div>
          <div class="daily-panel__sub">${e("overview.monthly_sales_sub")}</div>
        </div>
        <span class="sales-card__pill">${_} · ${L(u)}</span>
      </div>
      <div class="chart-wrap sales-card__chart"><canvas id="salesChart"></canvas></div>
    </section>
    <section class="card target-card ${r>=100?"target-card--over":""}">
      <div class="target-card__head">
        <div>
          <div class="target-card__eyebrow">${e("overview.monthly_goal")}</div>
          <div class="target-card__title">${e("overview.target_achievement")}</div>
        </div>
        <span class="target-card__pill">${r>=100?e("overview.above_target"):e("overview.below_target")}</span>
      </div>
      <div class="target-card__body">
        <div class="target-card__ring" style="--p:${Math.min(100,r)}">
          <svg viewBox="0 0 120 120" aria-hidden="true">
            <circle class="target-card__track" cx="60" cy="60" r="52"></circle>
            <circle class="target-card__prog" cx="60" cy="60" r="52"></circle>
          </svg>
          <div class="target-card__ring-value">
            <strong>${z(r)}</strong>
            <span>${e("overview.of_target")}</span>
          </div>
        </div>
        <div class="target-card__side">
          <div class="target-card__stat">
            <span>${e("overview.target")}</span>
            <strong>${L(d)}</strong>
          </div>
          <div class="target-card__stat">
            <span>${e("overview.actual")}</span>
            <strong>${L(u)}</strong>
          </div>
          <div class="target-card__delta">
            <strong>${p>=0?"+":""}${L(p)}</strong>
            <span>${e("overview.vs_target_month")}</span>
          </div>
        </div>
      </div>
    </section>
  </div>

  <section class="card" style="margin-top:12px"><div class="card-title-row"><div class="card-title">♙ ${e("overview.beauticians")}</div><button class="btn small primary" data-jump="beauticians">${e("common.view_all")}</button></div>${mn()}</section>

  <div class="grid three-col" style="margin-top:12px">
    ${(t.branches||[]).slice(0,6).map(H=>Tt(H.name,H.new_buyers||0,H.buyers||0,H.conv||0,H.sales||0,H.avg||0,H.buyers||0)).join("")||`<section class="card"><div class="empty"><strong>${e("overview.no_branch_data")}</strong></div></section>`}
  </div>

  <div class="grid op-row" style="margin-top:12px">
    ${_a("♧",e("ops.checkin"),w,$,"checkin",e("ops.checkin"),"green")}
    ${_a("◷",e("ops.clearance"),S,y,"clearance",e("ops.clearance"),"warning")}
    ${_a("▣",e("ops.payments"),M,P,"payments",e("ops.payments"),"rose")}
  </div>
  <div style="margin-top:12px">${cn()}</div>
  <div style="margin-top:12px">${pn()}</div>`,requestAnimationFrame(()=>{if(kt(),St(),Kt(),oa(),ne(),window.IMMA_TRADE){const H=Object.assign({},t.ticker||{}),he={leadsUp:Number(s.new_buyers||0)>=0,convUp:Number(s.new_buyer_share_pct||0)>=0,salesUp:Number(s.sales||0)>=0,targetUp:r>=100,avgUp:Number(s.avg_sale||0)>=0};IMMA_TRADE.render({ticker:Object.assign(H,he),leadsPct:Number(l.buyers_pct||l.leads_pct||0),salesPct:r,equityActual:t.equity&&t.equity.actual||[],equityTarget:t.equity&&t.equity.target_path||[],equityLabels:t.equity&&t.equity.labels||[],waterfall:(t.waterfall||t.status_mix||[]).map(W=>({name:String(W.name||""),value:W.value})),beauticians:(t.beauticians||[]).map(W=>({name:String(W.name||""),leads:W.leads||0,converted:W.converted||0,conv:W.conv||0,sales:W.sales||0})),heatmap:t.heatmap||[],sparks:t.sparks||[]})}})}function mn(){const t=Array.isArray(C?.beauticians)?C.beauticians:[];return t.length?`<div class="table-wrap"><table class="data-table"><thead><tr><th>#</th><th>${e("overview.col_beautician")}</th><th title="${e("overview.kpi_unique_leads")}">${e("overview.unique")}</th><th>${e("overview.kpi_buyers")}</th><th title="${e("overview.kpi_conv_rate")}">${e("overview.conv_rate")}</th><th>${e("overview.kpi_sales")}</th><th>${e("overview.kpi_avg_sale")}</th><th>${e("overview.col_orders")}</th></tr></thead><tbody>${t.map((a,s)=>{const i=n(a.name);return`<tr><td><span class="rank">${s+1}</span></td><td><strong>${i}</strong></td><td>${v(a.leads)}</td><td>${v(a.buyers)}</td><td style="color:${a.conv>=40?"var(--success)":a.conv<35?"var(--danger)":"#b16e10"};font-weight:700">${z(a.conv)}</td><td>${L(a.sales)}</td><td>${L(a.avg)}</td><td>${v(a.orders||0)}</td></tr>`}).join("")}</tbody></table></div>`:`<div class="empty"><strong>${e("overview.no_beautician_data")}</strong></div>`}function Tt(t,a,s,i,l,r,d){const u=i>=40?"good":i>=35?"ok":"low",p=n(t),_=n(String(t||"").slice(0,2).toUpperCase());return`<section class="card branch-card branch-card--${u}">
    <div class="branch-card__head">
      <div class="branch-card__identity">
        <div class="branch-card__mark" aria-hidden="true">${_}</div>
        <div>
          <div class="branch-card__name">${p} ${e("overview.branch")}</div>
          <div class="branch-card__meta">${e("overview.this_month_meta")}</div>
        </div>
      </div>
      <button type="button" class="branch-card__link link-btn" data-branch="${p}">${e("common.details")}</button>
    </div>
    <div class="branch-card__hero">
      <div>
        <div class="branch-card__hero-label" title="${e("overview.conv_rate")}">${e("overview.conv_rate")}</div>
        <div class="branch-card__hero-value">${i}%</div>
      </div>
      <div class="branch-card__sales">
        <div class="branch-card__hero-label">${e("overview.kpi_sales")}</div>
        <div class="branch-card__sales-value">${R(l)}</div>
      </div>
    </div>
    <div class="branch-card__bar" aria-hidden="true"><span style="width:${Math.min(100,i)}%"></span></div>
    <div class="branch-card__metrics">
      <div class="branch-metric"><span title="${e("overview.unique")}">${e("overview.unique")}</span><strong>${a.toLocaleString()}</strong></div>
      <div class="branch-metric"><span>${e("overview.converted")}</span><strong>${s.toLocaleString()}</strong></div>
      <div class="branch-metric"><span title="${e("overview.kpi_avg_sale")}">${e("overview.kpi_avg_sale")}</span><strong>${R(r)}</strong></div>
      <div class="branch-metric"><span title="${e("overview.treat_done")}">${e("overview.treat_done")}</span><strong>${d.toLocaleString()}</strong></div>
    </div>
  </section>`}function _a(t,a,s,i,l,r,d){return`<section class="card op-card"><div class="op-icon" style="background:${d==="green"?"var(--success-soft)":d==="warning"?"var(--warning-soft)":"var(--rose-soft)"}">${t}</div><div class="op-body"><div class="op-title">${a}</div><div class="op-value">${s}</div><div class="op-meta">${i}</div></div><button class="btn small primary" data-jump="${l}">${r} →</button></section>`}function et(t){(!c.leadMonth||c.leadMonth==="all")&&(c.leadMonth=Oe());const[a,s]=c.leadMonth.split("-").map(Number),i=new Date(a,s-1+t,1);c.leadMonth=i.getFullYear()+"-"+String(i.getMonth()+1).padStart(2,"0"),c.leadPage=1,A().then(()=>Fe())}function Ie(t){if(!t||t==="all")return null;const[a,s]=String(t).split("-").map(Number);return!a||!s?null:{y:a,m:s}}function Ct(){const t=(m.locale||"en").toLowerCase().startsWith("ms")?"ms-MY":"en-GB";return Array.from({length:12},(a,s)=>new Date(2e3,s,1).toLocaleString(t,{month:"short"}))}function Ra(t){if(!t||t==="all")return e("workspace.all_months");const a=Ie(t);if(!a)return String(t);const i=(B.months||[]).find(l=>String(l.value)===String(t));return i?i.label:Ct()[a.m-1]+" "+a.y}function Oe(){const t=new Date;return t.getFullYear()+"-"+String(t.getMonth()+1).padStart(2,"0")}function vn(){const t=new Date;return Oe()+"-"+String(t.getDate()).padStart(2,"0")}function _n(){const t=Ie(c.leadMonth)||Ie(Oe()),a=c.leadCalYear||t.y,s=!!m.canCreateLead;return`
    <div class="lead-cal" id="leadCalendar">
      <button type="button" class="lead-cal__nav" id="leadMonthPrev" title="${n(e("workspace.month_prev"))}" aria-label="${n(e("workspace.month_prev"))}">‹</button>
      <button type="button" class="lead-cal__toggle" id="leadCalToggle" aria-expanded="false" aria-haspopup="dialog">
        <span class="lead-cal__icon" aria-hidden="true">▦</span>
        <span id="leadMonthLabel">${n(Ra(c.leadMonth))}</span>
      </button>
      <button type="button" class="lead-cal__nav" id="leadMonthNext" title="${n(e("workspace.month_next"))}" aria-label="${n(e("workspace.month_next"))}">›</button>
      <div class="lead-cal__panel hidden" id="leadCalPanel" role="dialog" aria-label="${n(e("workspace.month_label"))}">
        <div class="lead-cal__year-row">
          <button type="button" class="lead-cal__nav" id="leadCalYearPrev" aria-label="${n(e("workspace.year_prev"))}">‹</button>
          <strong id="leadCalYearLabel">${a}</strong>
          <button type="button" class="lead-cal__nav" id="leadCalYearNext" aria-label="${n(e("workspace.year_next"))}">›</button>
        </div>
        <div class="lead-cal__grid" id="leadCalGrid"></div>
        <div class="lead-cal__footer">
          <button type="button" class="btn soft small" id="leadCalAll">${n(e("workspace.all_months"))}</button>
          <button type="button" class="btn soft small" id="leadMonthThis">${n(e("workspace.this_month"))}</button>
        </div>
      </div>
    </div>
    ${s?`
      <button type="button" class="btn" data-jump="import" data-import-tab="paste">${n(e("workspace.paste_leads"))}</button>
      <button type="button" class="btn" data-jump="import" data-import-tab="excel">${n(e("workspace.upload_excel"))}</button>
      <button type="button" class="btn primary" data-jump="import" data-import-tab="paste">${n(e("workspace.import_leads"))}</button>
    `:""}
  `}function sa(){const t=o("#leadCalGrid"),a=o("#leadCalYearLabel");if(!t)return;const s=Ie(c.leadMonth),i=c.leadCalYear||(s?s.y:new Date().getFullYear());c.leadCalYear=i,a&&(a.textContent=String(i));const l=Ct(),r=Oe();t.innerHTML=l.map((d,u)=>{const p=`${i}-${String(u+1).padStart(2,"0")}`;return`<button type="button" class="lead-cal__month${String(c.leadMonth)===p?" is-selected":""}${p===r?" is-now":""}" data-month="${p}">${n(d)}</button>`}).join(""),T("[data-month]",t).forEach(d=>{d.onclick=()=>{c.leadMonth=d.dataset.month,c.leadPage=1,ce(),Fe(),A()}})}function hn(){const t=o("#leadCalPanel"),a=o("#leadCalToggle");if(!t||!a)return;const s=Ie(c.leadMonth);c.leadCalYear=s?s.y:new Date().getFullYear(),t.classList.remove("hidden"),a.setAttribute("aria-expanded","true"),sa()}function ce(){const t=o("#leadCalPanel"),a=o("#leadCalToggle");t&&t.classList.add("hidden"),a&&a.setAttribute("aria-expanded","false")}function Fe(){const t=o("#leadMonthLabel");t&&(t.textContent=Ra(c.leadMonth)),o("#leadCalPanel")&&!o("#leadCalPanel").classList.contains("hidden")&&sa()}function at(t){const a=o("#leadCalendar");!a||a.contains(t.target)||ce()}function bn(){o("#leadMonthPrev")&&(o("#leadMonthPrev").onclick=()=>{ce(),et(-1)}),o("#leadMonthNext")&&(o("#leadMonthNext").onclick=()=>{ce(),et(1)}),o("#leadCalToggle")&&(o("#leadCalToggle").onclick=t=>{t.stopPropagation();const a=o("#leadCalPanel");a&&a.classList.contains("hidden")?hn():ce()}),o("#leadCalYearPrev")&&(o("#leadCalYearPrev").onclick=t=>{t.stopPropagation(),c.leadCalYear=(c.leadCalYear||new Date().getFullYear())-1,sa()}),o("#leadCalYearNext")&&(o("#leadCalYearNext").onclick=t=>{t.stopPropagation(),c.leadCalYear=(c.leadCalYear||new Date().getFullYear())+1,sa()}),o("#leadMonthThis")&&(o("#leadMonthThis").onclick=t=>{t.stopPropagation(),c.leadMonth=Oe(),c.leadPage=1,ce(),Fe(),A()}),o("#leadCalAll")&&(o("#leadCalAll").onclick=t=>{t.stopPropagation(),c.leadMonth="all",c.leadPage=1,ce(),Fe(),A()}),document.removeEventListener("click",at),document.addEventListener("click",at)}function fn(){const t=!!m.canCreateLead,a=_n();E.innerHTML=`${_e(e("workspace.title"),e("workspace.subtitle"),a)}
  <div class="grid kpi-grid" id="leadKpiMount"></div>
  <p class="card-subtitle" style="margin:8px 2px 0">${n(e("workspace.kpi_note"))}</p>
  <section class="lead-panel card" style="margin-top:14px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${n(e("workspace.list_title"))}</h2>
        <p class="lead-panel__sub">${n(e("workspace.list_subtitle"))}</p>
      </div>
      <div class="lead-panel__head-meta">
        <span class="lead-panel__count" id="leadResultCount">—</span>
        ${t?`<button class="btn primary" type="button" id="addLeadBtn">${n(e("workspace.add_lead"))}</button>`:""}
      </div>
    </div>
    <div class="lead-panel__filters">
      <label class="lead-search" for="leadSearch">
        <span class="lead-search__icon" aria-hidden="true">⌕</span>
        <input class="lead-search__input" id="leadSearch" type="search" autocomplete="off" placeholder="${n(e("workspace.search_placeholder"))}" value="${n(c.leadSearch)}" />
      </label>
      <div class="lead-filter-grid">
        <label class="lead-field">
          <span class="lead-field__label">${n(e("workspace.filter_status"))}</span>
          <select class="lead-field__control" id="leadStatus"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${n(e("workspace.filter_beautician"))}</span>
          <select class="lead-field__control" id="leadBeautician"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${n(e("workspace.filter_branch"))}</span>
          <select class="lead-field__control" id="leadBranchFilter"></select>
        </label>
      </div>
    </div>
    <div class="lead-panel__chips" id="leadActiveFilters" hidden></div>
    <div class="lead-bulk-bar" id="leadBulkBar" hidden aria-live="polite"></div>
    <div class="lead-panel__body" id="leadTableMount"></div>
  </section>`,Lt(),ia(),bn(),ne(),o("#addLeadBtn")&&(o("#addLeadBtn").onclick=Bt),A()}function Lt(){const t=o("#leadKpiMount");if(!t)return;const a=wa||{},s=a.all_time||a,i=Number(a.raw||0),l=Number(a.unique||0),r=Number(a.duplicates||0),d=Number(a.existing||0),u=Number(a.converted||0),p=Number(a.conversion_pct||0),_=Number(s.raw||0),h=Number(s.unique||0),b=Number(s.duplicates||0),k=Number(s.existing||0),f=Number(s.converted||0),w=Number(s.conversion_pct||0),$=_>0?(h/_*100).toFixed(1):"0.0",S=_>0?(b/_*100).toFixed(1):"0.0",y=Ra(c.leadMonth);t.innerHTML=`
    ${Me(Se("database"),e("workspace.total_leads_database"),v(_),e("workspace.unit_lead_records"),e("workspace.all_time"),e("workspace.period_added",{period:y,count:v(i)}),"blue")}
    ${Me(Se("new"),e("workspace.new_leads"),v(l),e("workspace.unit_new_leads"),y,e("workspace.all_time_unique",{count:v(h),pct:$}),"green")}
    ${Me(Se("repeated"),e("workspace.repeated_leads"),v(b),e("workspace.unit_repeated_records"),e("workspace.all_time"),e("workspace.period_repeated",{period:y,count:v(r),pct:S}),"rose")}
    ${Me(Se("customers"),e("workspace.existing_customers"),v(k),e("workspace.unit_registered_customers"),e("workspace.matched_phone"),e("workspace.period_matched",{period:y,count:v(d)}),"purple")}
    ${Me(Se("conversion"),e("workspace.conversion"),z(w),e("workspace.unit_conversion_rate"),e("workspace.all_time"),e("workspace.period_conversion",{period:y,pct:z(p),count:v(u),total:v(f)}),"teal")}
  `}function ia(){const t=o("#leadStatus"),a=o("#leadBeautician"),s=o("#leadBranchFilter");if(t){const l=[{value:"all",label:e("workspace.all_status")},...B.statuses||[]];t.innerHTML=l.map(r=>`<option value="${n(r.value)}" ${String(c.leadStatus)===String(r.value)?"selected":""}>${n(r.label)}</option>`).join(""),t.onchange=r=>{c.leadStatus=r.target.value,c.leadPage=1,A()}}if(a){const l=[{id:"all",name:e("workspace.all_beauticians")},...B.beauticians||[]];a.innerHTML=l.map(r=>`<option value="${n(r.id)}" ${String(c.leadBeautician)===String(r.id)?"selected":""}>${n(r.name)}</option>`).join(""),a.onchange=r=>{c.leadBeautician=r.target.value,c.leadPage=1,A()}}if(s){const l=[{id:"all",name:e("workspace.all_branches")},...B.branches||m.branches||[]];s.innerHTML=l.map(r=>`<option value="${n(r.id)}" ${String(c.leadBranch)===String(r.id)?"selected":""}>${n(r.name)}</option>`).join(""),s.onchange=r=>{c.leadBranch=r.target.value,c.leadPage=1,A()}}const i=o("#leadSearch");i&&(i.oninput=l=>{c.leadSearch=l.target.value,clearTimeout(Wa),Wa=setTimeout(()=>{c.leadPage=1,A()},350)}),xt()}function ha(t,a){if(t==="status"){const s=(B.statuses||[]).find(i=>String(i.value)===String(a));return s?s.label:a}if(t==="beautician"){const s=(B.beauticians||[]).find(i=>String(i.id)===String(a));return s?s.name:a}if(t==="branch"){const i=(B.branches||m.branches||[]).find(l=>String(l.id)===String(a));return i?i.name:a}return a}function xt(){const t=o("#leadResultCount"),a=Number(Be.total??(Array.isArray(G)?G.length:0));t&&(t.textContent=a===1?e("workspace.results_count_one"):e("workspace.results_count",{count:v(a)}));const s=o("#leadActiveFilters");if(!s)return;const i=[];if(c.leadSearch&&String(c.leadSearch).trim()&&i.push({key:"q",label:`“${String(c.leadSearch).trim()}”`}),c.leadStatus&&c.leadStatus!=="all"&&i.push({key:"status",label:ha("status",c.leadStatus)}),c.leadBeautician&&c.leadBeautician!=="all"&&i.push({key:"beautician",label:ha("beautician",c.leadBeautician)}),c.leadBranch&&c.leadBranch!=="all"&&i.push({key:"branch",label:ha("branch",c.leadBranch)}),!i.length){s.hidden=!0,s.innerHTML="";return}s.hidden=!1,s.innerHTML=`
    <div class="lead-chips">
      ${i.map(l=>`<span class="lead-chip">${n(l.label)}<button type="button" class="lead-chip__x" data-clear-filter="${n(l.key)}" aria-label="${n(e("workspace.clear_filters"))}">×</button></span>`).join("")}
      <button type="button" class="lead-chips__clear" id="leadClearFilters">${n(e("workspace.clear_filters"))}</button>
    </div>
  `,T("[data-clear-filter]",s).forEach(l=>{l.onclick=()=>{const r=l.dataset.clearFilter;r==="q"&&(c.leadSearch=""),r==="status"&&(c.leadStatus="all"),r==="beautician"&&(c.leadBeautician="all"),r==="branch"&&(c.leadBranch="all"),c.leadPage=1;const d=o("#leadSearch");d&&r==="q"&&(d.value=""),ia(),A()}}),o("#leadClearFilters")&&(o("#leadClearFilters").onclick=()=>{c.leadSearch="",c.leadStatus="all",c.leadBeautician="all",c.leadBranch="all",c.leadPage=1;const l=o("#leadSearch");l&&(l.value=""),ia(),A()})}function gn(t){return t==="status"?(B.statuses||[]).map(a=>`<option value="${n(a.value)}">${n(a.label)}</option>`).join(""):t==="source"?(B.sources||[]).map(a=>`<option value="${n(a.value)}">${n(a.label)}</option>`).join(""):t==="beautician_id"?`<option value="__none__">${n(e("workspace.bulk_unassigned"))}</option>${(B.beauticians||[]).map(a=>`<option value="${n(a.id)}">${n(a.name)}</option>`).join("")}`:t==="spa_branch_id"?`<option value="__none__">${n(e("workspace.bulk_unassigned"))}</option>${(B.branches||m.branches||[]).map(a=>`<option value="${n(a.id)}">${n(a.name)}</option>`).join("")}`:""}function la(){const t=o("#leadBulkBar");if(!t)return;const a=I.size;if(!a){t.hidden=!0,t.innerHTML="",F="",O="";return}const s=[];m.canEditLead&&s.push(["status",e("workspace.bulk_update_status")],["source",e("workspace.bulk_update_source")],["created_at",e("workspace.bulk_update_date")],["beautician_id",e("workspace.bulk_assign_beautician")],["spa_branch_id",e("workspace.bulk_assign_branch")]),m.canDeleteLead&&s.push(["delete",e("workspace.bulk_delete")]),s.some(([d])=>d===F)||(F="",O="");const i=["status","source","created_at","beautician_id","spa_branch_id"].includes(F);t.hidden=!1,t.innerHTML=`
    <div class="lead-bulk-bar__summary"><strong>${n(e("workspace.bulk_selected",{count:v(a)}))}</strong></div>
    <div class="lead-bulk-bar__controls">
      <label class="sr-only" for="leadBulkAction">${n(e("workspace.bulk_action"))}</label>
      <select class="lead-bulk-control" id="leadBulkAction">
        <option value="">${n(e("workspace.bulk_choose_action"))}</option>
        ${s.map(([d,u])=>`<option value="${n(d)}">${n(u)}</option>`).join("")}
      </select>
      ${F==="created_at"?`<label class="sr-only" for="leadBulkValue">${n(e("workspace.bulk_choose_date"))}</label>
      <input class="lead-bulk-control lead-bulk-date" id="leadBulkValue" type="date" max="${vn()}" value="${n(O)}" aria-label="${n(e("workspace.bulk_choose_date"))}">`:i?`<label class="sr-only" for="leadBulkValue">${n(e("workspace.bulk_choose_value"))}</label>
      <select class="lead-bulk-control" id="leadBulkValue">
        <option value="">${n(e("workspace.bulk_choose_value"))}</option>
        ${gn(F)}
      </select>`:""}
      <button type="button" class="btn ${F==="delete"?"danger":"primary"} lead-bulk-apply" id="leadBulkApply" ${!F||i&&!O?"disabled":""}>${n(e("workspace.bulk_apply"))}</button>
      <button type="button" class="btn lead-bulk-clear" id="leadBulkClear">${n(e("workspace.bulk_clear"))}</button>
    </div>`;const l=o("#leadBulkAction");l.value=F,l.onchange=d=>{F=d.target.value,O="",la()};const r=o("#leadBulkValue");r&&(r.value=O,r.onchange=d=>{O=d.target.value,la()}),o("#leadBulkApply").onclick=wn,o("#leadBulkClear").onclick=()=>{I.clear(),F="",O="",Xe()}}function Xe(){const t=G.map(i=>String(i.id)),a=t.filter(i=>I.has(i)).length,s=o("#leadSelectAll");s&&(s.checked=t.length>0&&a===t.length,s.indeterminate=a>0&&a<t.length),T("[data-lead-select]").forEach(i=>{const l=I.has(String(i.value));i.checked=l,i.closest("tr")?.classList.toggle("is-selected",l)}),la()}async function yn(t,a,s){const i=m.leadBulkUpdateUrl||"";if(!i){g(e("workspace.bulk_update_error"));return}s.disabled=!0;try{const l=await fetch(i,{method:"PATCH",headers:N(!0),credentials:"same-origin",body:JSON.stringify({ids:[...I].map(Number),field:t,value:a})}),r=await l.json().catch(()=>({}));if(!l.ok){g(r.message||e("workspace.bulk_update_error"));return}g(r.message||e("workspace.bulk_updated",{count:I.size})),await A()}catch(l){console.error(l),g(e("workspace.bulk_update_error"))}finally{s.isConnected&&(s.disabled=!1)}}async function $n(t){const a=m.leadBulkDeleteUrl||"";if(!a){g(e("workspace.bulk_delete_error"));return}t.disabled=!0;try{const s=await fetch(a,{method:"DELETE",headers:N(!0),credentials:"same-origin",body:JSON.stringify({ids:[...I].map(Number)})}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("workspace.bulk_delete_error"));return}Number(i.deleted||0)>=G.length&&c.leadPage>1&&c.leadPage--,D(),g(i.message||e("workspace.bulk_deleted",{count:I.size})),await A()}catch(s){console.error(s),g(e("workspace.bulk_delete_error"))}finally{t.isConnected&&(t.disabled=!1)}}function wn(){const t=I.size;if(!t){g(e("workspace.bulk_nothing_selected"));return}const a=F,s=o("#leadBulkApply");if(!a||!s)return;if(a==="delete"){Y(e("workspace.bulk_delete"),e("workspace.bulk_delete_confirm",{count:v(t)}),"",`<button type="button" class="btn" data-action-drawer-close>${n(e("workspace.cancel"))}</button><button type="button" class="btn danger" id="confirmBulkDeleteLead">${n(e("workspace.bulk_delete"))}</button>`,e("workspace.bulk_action")),o("#confirmBulkDeleteLead").onclick=l=>$n(l.currentTarget);return}if(!O){o("#leadBulkValue")?.focus();return}const i=O==="__none__"?null:["beautician_id","spa_branch_id"].includes(a)?Number(O):O;yn(a,i,s)}function tt(){const t=o("#leadTableMount");if(!t)return;if(xt(),Ze){t.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${n(e("workspace.loading"))}</strong></div>`;return}const a=G;if(!a.length){const d=!!m.canCreateLead;t.innerHTML=`<div class="lead-empty">
      <div class="lead-empty__icon" aria-hidden="true">◎</div>
      <strong>${n(e("workspace.no_leads"))}</strong>
      <p>${n(e("workspace.no_leads_hint"))}</p>
      ${d?`<button type="button" class="btn primary" id="emptyAddLeadBtn">${n(e("workspace.empty_cta"))}</button>`:""}
    </div>`,o("#emptyAddLeadBtn")&&(o("#emptyAddLeadBtn").onclick=Bt);return}const s=!!(m.canEditLead||m.canDeleteLead);t.innerHTML=`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    ${s?`<th class="lead-table__select"><input class="lead-select-box" type="checkbox" id="leadSelectAll" aria-label="${n(e("workspace.bulk_select_all"))}"></th>`:""}
    <th class="lead-col--id">${n(e("workspace.col_lead_id"))}</th><th class="lead-col--date">${n(e("workspace.col_date"))}</th><th class="lead-col--customer">${n(e("workspace.col_customer"))}</th>
    <th class="lead-col--phone">${n(e("workspace.col_phone"))}</th><th class="lead-col--email">${n(e("workspace.col_email"))}</th><th class="lead-col--source">${n(e("workspace.col_source"))}</th>
    <th class="lead-col--beautician">${n(e("workspace.col_beautician"))}</th><th class="lead-col--branch">${n(e("workspace.col_branch"))}</th><th>${n(e("workspace.col_status"))}</th>
    <th class="lead-col--payment">${n(e("workspace.col_payment"))}</th><th class="is-num lead-col--sales">${n(e("workspace.col_sales"))}</th><th class="lead-col--follow-up" title="${n(e("workspace.col_last_fu"))}">${n(e("workspace.col_last_fu"))}</th>
    <th class="lead-table__actions"><span class="sr-only">${n(e("workspace.col_action"))}</span></th>
  </tr></thead><tbody>${a.map(d=>{const u=String(d.name||""),p=n((u[0]||"?").toUpperCase()),_=!!m.canEditLead,h=!!m.canDeleteLead,b=String(d.email||"").trim(),k=String(d.source||"").trim(),f=String(d.beautician||"").trim(),w=String(d.branch||"").trim(),$=String(d.last||"").trim(),S=Number(d.sales||0);return`<tr>
      ${s?`<td class="lead-table__select"><input class="lead-select-box" type="checkbox" value="${n(d.id)}" data-lead-select aria-label="${n(e("workspace.bulk_select_lead",{name:u||d.code||d.id}))}"></td>`:""}
      <td class="lead-col--id"><span class="lead-code">${n(d.code||d.id)}</span></td>
      <td class="lead-col--date"><span class="lead-date">${n(d.date||"—")}</span></td>
      <td>
        <div class="person-cell person-cell--lead">
          <div class="mini-avatar" aria-hidden="true">${p}</div>
          <div class="person-cell__text">
            <strong>${n(u||"—")}</strong>
            ${d.customer?`<small>${n(d.customer)}</small>`:""}
          </div>
        </div>
      </td>
      <td><span class="lead-mono">${n(d.phone||"—")}</span></td>
      <td class="lead-col--email">${b?`<span class="lead-email" title="${n(b)}">${n(b)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td class="lead-col--source">${k?`<span class="lead-tag">${n(k)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td class="lead-col--beautician">${f?n(f):'<span class="lead-muted">—</span>'}</td>
      <td class="lead-col--branch">${w?`<span class="lead-branch">${n(w)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td>${x(d.status)}</td>
      <td class="lead-col--payment">${x(d.payment)}</td>
      <td class="is-num"><span class="lead-money${S?"":" is-zero"}">${S?R(S):"RM0"}</span></td>
      <td class="lead-col--follow-up"><span class="lead-date">${$?n($):"—"}</span></td>
      <td class="lead-table__actions">
        <div class="lead-menu">
          <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${n(e("workspace.row_actions"))}">
            <span class="lead-menu__dots" aria-hidden="true"></span>
          </button>
          <div class="lead-menu__panel" role="menu" hidden>
            <button type="button" class="lead-menu__item" role="menuitem" data-lead-id="${n(d.id)}">${n(e("workspace.view"))}</button>
            ${_?`<button type="button" class="lead-menu__item" role="menuitem" data-lead-edit="${n(d.id)}">${n(e("workspace.edit"))}</button>`:""}
            ${h?`<button type="button" class="lead-menu__item lead-menu__item--danger" role="menuitem" data-lead-del="${n(d.id)}">${n(e("workspace.delete"))}</button>`:""}
          </div>
        </div>
      </td>
    </tr>`}).join("")}</tbody></table></div>`;const i=[10,50,100,200];t.insertAdjacentHTML("beforeend",`<div class="lead-pagination-bar">
    <label class="lead-page-size" for="leadPerPage">
      <span>${n(e("workspace.rows_per_page"))}</span>
      <select id="leadPerPage" class="lead-page-size__select">
        ${i.map(d=>`<option value="${d}" ${Number(c.leadPerPage)===d?"selected":""}>${d}</option>`).join("")}
      </select>
    </label>
    ${ae("leads",Be)}
  </div>`);const l=o("#leadPerPage",t);l&&(l.onchange=()=>{c.leadPerPage=Number(l.value)||10,c.leadPage=1,l.disabled=!0,A()}),te("leads",d=>(c.leadPage=d,A()));const r=o("#leadSelectAll",t);r&&(r.onchange=()=>{G.forEach(d=>r.checked?I.add(String(d.id)):I.delete(String(d.id))),Xe()}),T("[data-lead-select]",t).forEach(d=>d.onchange=()=>{d.checked?I.add(String(d.value)):I.delete(String(d.value)),Xe()}),Xe(),Ua(t),T("[data-lead-id]",t).forEach(d=>d.onclick=()=>{J(),da(d.dataset.leadId)}),T("[data-lead-edit]",t).forEach(d=>d.onclick=()=>{J(),jt(d.dataset.leadEdit)}),T("[data-lead-del]",t).forEach(d=>d.onclick=()=>{J(),At(d.dataset.leadDel)})}function Pt(t){t&&(t.classList.remove("is-up"),t.style.top="",t.style.left="",t.style.right="",t.style.bottom="")}function kn(t,a){if(!t||!a)return;const s=4,i=t.getBoundingClientRect();a.style.top="0px",a.style.left="0px",a.style.right="auto",a.style.bottom="auto";const l=a.getBoundingClientRect(),d=window.innerHeight-i.bottom<l.height+s+8;a.classList.toggle("is-up",d);let u=d?i.top-l.height-s:i.bottom+s,p=i.right-l.width;p=Math.max(8,Math.min(p,window.innerWidth-l.width-8)),u=Math.max(8,Math.min(u,window.innerHeight-l.height-8)),a.style.top=`${Math.round(u)}px`,a.style.left=`${Math.round(p)}px`}function J(t=null){T(".lead-menu").forEach(a=>{if(t&&a===t)return;const s=o(".lead-menu__btn",a),i=o(".lead-menu__panel",a);s&&s.setAttribute("aria-expanded","false"),i&&(i.hidden=!0,Pt(i)),a.classList.remove("is-open")})}function nt(t){t.target.closest&&t.target.closest(".lead-menu")||J()}function st(t){t.key==="Escape"&&J()}function Je(){J()}function Ua(t){T("[data-lead-menu]",t).forEach(a=>{a.onclick=s=>{s.stopPropagation();const i=a.closest(".lead-menu"),l=o(".lead-menu__panel",i),r=a.getAttribute("aria-expanded")==="true";J(r?null:i),r?(a.setAttribute("aria-expanded","false"),l&&(l.hidden=!0,Pt(l)),i.classList.remove("is-open")):(a.setAttribute("aria-expanded","true"),l&&(l.hidden=!1,kn(a,l)),i.classList.add("is-open"))}}),document.removeEventListener("click",nt),document.addEventListener("click",nt),document.removeEventListener("keydown",st),document.addEventListener("keydown",st),window.removeEventListener("scroll",Je,!0),window.addEventListener("scroll",Je,!0),window.removeEventListener("resize",Je),window.addEventListener("resize",Je)}function Bt(){Ce=null,Y(e("workspace.manual_entry"),e("workspace.manual_sub"),Et({}),`<button class="btn" type="button" data-action-drawer-close>${n(e("workspace.cancel"))}</button><button class="btn primary" type="button" id="saveLead">${n(e("workspace.save"))}</button>`,e("workspace.title"))}function jt(t){const a=G.find(s=>String(s.id)===String(t));if(!a){da(t);return}Ce=a.id,Y(e("workspace.edit_entry"),e("workspace.edit_sub"),Et(a),`<button class="btn" type="button" data-action-drawer-close>${n(e("workspace.cancel"))}</button><button class="btn primary" type="button" id="saveLead">${n(e("workspace.save"))}</button>`,e("workspace.title"))}function Et(t={}){const a=(B.statuses||[]).map(r=>`<option value="${n(r.value)}" ${String(t.status_key||"")===String(r.value)?"selected":""}>${n(r.label)}</option>`).join(""),s=[{id:"",name:"—"},...B.branches||[]].map(r=>`<option value="${n(r.id)}" ${String(t.branch_id||"")===String(r.id)?"selected":""}>${n(r.name)}</option>`).join(""),i=[{id:"",name:"—"},...B.beauticians||[]].map(r=>`<option value="${n(r.id)}" ${String(t.beautician_id||"")===String(r.id)?"selected":""}>${n(r.name)}</option>`).join(""),l=String(t.source||"manual");return`<div class="detail-grid">
      <div><label class="kpi-label">${n(e("workspace.name"))}</label><input class="search" style="width:100%" id="mName" autocomplete="name" value="${n(t.name||"")}"></div>
      <div><label class="kpi-label">${n(e("workspace.phone"))}</label><input class="search" style="width:100%" id="mPhone" autocomplete="tel" value="${n(t.phone||"")}"></div>
      <div style="grid-column:1/-1"><label class="kpi-label">${n(e("workspace.email"))}</label><input class="search" style="width:100%" id="mEmail" autocomplete="email" value="${n(t.email||"")}"></div>
      <div><label class="kpi-label">${n(e("workspace.source"))}</label>
        <select class="control" style="width:100%" id="mSource">
          ${["manual","TikTok","WhatsApp","Facebook","import"].map(r=>`<option value="${r}" ${l===r?"selected":""}>${r}</option>`).join("")}
        </select>
      </div>
      <div><label class="kpi-label">${n(e("workspace.col_status"))}</label>
        <select class="control" style="width:100%" id="mStatus">${a||'<option value="new">NEW</option>'}</select>
      </div>
      <div><label class="kpi-label">${n(e("workspace.col_branch"))}</label>
        <select class="control" style="width:100%" id="mBranch">${s}</select>
      </div>
      <div><label class="kpi-label">${n(e("workspace.col_beautician"))}</label>
        <select class="control" style="width:100%" id="mBeautician">${i}</select>
      </div>
    </div>`}async function Sn(){const t=Ce!=null,a=t?q(m.leadUpdateUrlTemplate,Ce):m.leadStoreUrl;if(!a){g(e("workspace.save_error"));return}const s={name:(o("#mName")?.value||"").trim(),phone:(o("#mPhone")?.value||"").trim(),email:(o("#mEmail")?.value||"").trim()||null,source:o("#mSource")?.value||"manual",status:o("#mStatus")?.value||void 0,spa_branch_id:o("#mBranch")?.value?Number(o("#mBranch").value):null,beautician_id:o("#mBeautician")?.value?Number(o("#mBeautician").value):null};if(!s.name||!s.phone){g(e("workspace.save_error"));return}try{const i=await fetch(a,{method:t?"PUT":"POST",headers:N(!0),credentials:"same-origin",body:JSON.stringify(s)}),l=await i.json().catch(()=>({}));if(!i.ok){const r=l&&(l.message||Object.values(l.errors||{})[0]?.[0])||e(t?"workspace.update_error":"workspace.save_error");g(r);return}D(),Ce=null,g(l.message||e(t?"workspace.updated":"workspace.saved")),t||(c.leadPage=1),await A()}catch(i){console.error(i),g(e(t?"workspace.update_error":"workspace.save_error"))}}function At(t){m.canDeleteLead&&(Y(e("workspace.delete"),e("workspace.delete_confirm"),"",`<button type="button" class="btn" data-action-drawer-close>${n(e("workspace.cancel"))}</button><button type="button" class="btn danger" id="confirmDeleteLead">${n(e("workspace.delete"))}</button>`,e("nav.leads")),o("#confirmDeleteLead").onclick=async a=>{const s=a.currentTarget;s.disabled=!0,await Mn(t),s.disabled=!1})}async function Mn(t){if(!m.canDeleteLead)return;const a=q(m.leadDestroyUrlTemplate,t);if(!a){g(e("workspace.delete_error"));return}try{const s=await fetch(a,{method:"DELETE",headers:N(!1),credentials:"same-origin"}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("workspace.delete_error"));return}g(i.message||e("workspace.deleted")),D(),X(),await A()}catch(s){console.error(s),g(e("workspace.delete_error"))}}async function da(t){let s=G.find(h=>String(h.id)===String(t))||Le.find(h=>String(h.id)===String(t));const i=m.leadShowUrlTemplate;if(i)try{const h=await fetch(q(i,t),{headers:N(!1),credentials:"same-origin"});if(h.ok){const b=await h.json();s=b.data||s,b.filters?.statuses&&(B.statuses=b.filters.statuses)}}catch(h){console.error(h)}if(!s)return;D(),X(),je=document.activeElement,Fa=document.body.style.overflow,document.body.style.overflow="hidden";const l=o("#leadDrawer .eyebrow");l&&(l.textContent=e("workspace.drawer_eyebrow")),o("#drawerName").textContent=s.name||"";const r=Tn(s),d=(B.statuses||[]).map(h=>`<option value="${n(h.value)}" ${String(s.status_key)===String(h.value)?"selected":""}>${n(h.label)}</option>`).join(""),u=!!m.canEditLead,p=String(s.name||""),_=n((p[0]||"?").toUpperCase());o("#drawerBody").innerHTML=`
  <div class="journey">
    <div class="journey-hero">
      <div class="journey-hero__avatar" aria-hidden="true">${_}</div>
      <div class="journey-hero__meta">
        <div class="journey-hero__code">${n(s.code||s.id)}</div>
        <div class="journey-hero__badges">
          ${x(s.status)}
          <span class="journey-pill journey-pill--${n(r.healthTone)}">${n(r.healthLabel)}</span>
          <span class="journey-pill journey-pill--prio-${n(r.priorityTone)}">${n(r.priorityLabel)}</span>
        </div>
      </div>
    </div>

    <section class="journey-section">
      <div class="journey-section__title">${n(e("workspace.drawer_analytics"))}</div>
      <div class="journey-stats">
        <div class="journey-stat">
          <span class="journey-stat__label">${n(e("workspace.drawer_health"))}</span>
          <strong class="journey-stat__value">${r.score}</strong>
          <div class="journey-meter"><span style="width:${r.score}%"></span></div>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${n(e("workspace.drawer_stage"))}</span>
          <strong class="journey-stat__value">${r.stagePct}%</strong>
          <div class="journey-meter journey-meter--stage"><span style="width:${r.stagePct}%"></span></div>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${n(e("workspace.drawer_pipeline_days"))}</span>
          <strong class="journey-stat__value">${n(e("workspace.drawer_days",{count:r.daysInPipeline}))}</strong>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${n(e("workspace.drawer_since_contact"))}</span>
          <strong class="journey-stat__value">${r.neverContacted?n(e("workspace.drawer_never_contacted")):n(e("workspace.drawer_days",{count:r.daysSinceContact}))}</strong>
        </div>
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${n(e("workspace.drawer_signals"))}</div>
      <div class="journey-signals">
        ${r.signals.map(h=>`<span class="journey-signal journey-signal--${n(h.tone)}">${n(h.label)}</span>`).join("")||`<span class="journey-signal journey-signal--ok">${n(e("workspace.drawer_signal_healthy"))}</span>`}
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${n(e("workspace.drawer_pipeline"))}</div>
      <div class="journey-pipeline" role="list">${r.pipelineHtml}</div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${n(e("workspace.drawer_contact"))}</div>
      <div class="journey-kv">
        <div><span>${n(e("workspace.col_phone"))}</span><strong>${n(s.phone||"—")}</strong></div>
        <div><span>${n(e("workspace.col_email"))}</span><strong title="${n(s.email||"")}">${n(s.email||"—")}</strong></div>
        <div><span>${n(e("workspace.drawer_source_label"))}</span><strong>${n(s.source||"—")}</strong></div>
        <div><span>${n(e("workspace.col_customer"))}</span><strong>${n(s.customer||"—")}</strong></div>
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${n(e("workspace.drawer_assignment"))}</div>
      <div class="journey-kv">
        <div><span>${n(e("workspace.col_beautician"))}</span><strong>${n(s.beautician||"—")}</strong></div>
        <div><span>${n(e("workspace.col_branch"))}</span><strong>${n(s.branch||"—")}</strong></div>
        <div><span>${n(e("workspace.col_last_fu"))}</span><strong>${n(s.last||"—")}</strong></div>
        <div><span>${n(e("workspace.col_date"))}</span><strong>${n(s.date||"—")}</strong></div>
      </div>
    </section>

    ${u?`<section class="journey-section">
      <div class="journey-section__title">${n(e("workspace.update_status"))}</div>
      <div class="journey-status-row">
        <select class="control" id="drawerStatus">${d}</select>
        <button class="btn primary" type="button" id="drawerSaveStatus">${n(e("workspace.update_status"))}</button>
      </div>
    </section>`:""}

    <div class="journey-actions">
      <div class="journey-section__title">${n(e("workspace.drawer_actions"))}</div>
      <div class="journey-actions__row">
        ${u?`<button class="btn primary" type="button" id="drawerFollowBtn">${n(e("followup.mark"))}</button>`:""}
        ${u?`<button class="btn" type="button" id="drawerEditBtn">${n(e("workspace.edit"))}</button>`:""}
        ${m.canDeleteLead?`<button class="btn danger" type="button" id="drawerDeleteBtn">${n(e("workspace.delete"))}</button>`:""}
      </div>
    </div>
  </div>`,o("#leadDrawer").classList.add("show"),o("#drawerBackdrop").classList.add("show"),o("#leadDrawer").setAttribute("aria-hidden","false"),o("#leadDrawer").inert=!1,o(".app-shell").inert=!0,o("#drawerClose").focus({preventScroll:!0}),ne(),o("#drawerFollowBtn")&&(o("#drawerFollowBtn").onclick=()=>Wt(s.id)),o("#drawerEditBtn")&&(o("#drawerEditBtn").onclick=()=>{X(),jt(s.id)}),o("#drawerDeleteBtn")&&(o("#drawerDeleteBtn").onclick=()=>At(s.id)),o("#drawerSaveStatus")&&(o("#drawerSaveStatus").onclick=async()=>{const h=o("#drawerStatus")?.value,b=q(m.leadStatusUrlTemplate,s.id);if(!h||!b){g(e("workspace.status_error"));return}try{const k=await fetch(b,{method:"PATCH",headers:N(!0),credentials:"same-origin",body:JSON.stringify({status:h})}),f=await k.json().catch(()=>({}));if(!k.ok){g(f.message||e("workspace.status_error"));return}g(f.message||e("workspace.status_updated")),c.view==="followup"?await re():await A(),da(s.id)}catch(k){console.error(k),g(e("workspace.status_error"))}})}function Tn(t){const a=["new","claimed","follow_up","booking","payment_verified","converted"],s=String(t.status_key||"new"),i=a.indexOf(s),l=s==="lost"||s==="no_response"?Math.max(10,Math.round((Math.max(i,0)+1)/a.length*100)):Math.round((Math.max(i,0)+1)/a.length*100),r=Number(t.days_in_pipeline!=null?t.days_in_pipeline:t.days_since_followup||0),d=!t.last_followed_up_at,u=Number(t.days_since_followup||0),p=String(t.followup_bucket)==="overdue"||(d?r>=2:u>=2);let _=28;s==="converted"?_=96:s==="payment_verified"?_=82:s==="booking"?_=68:s==="follow_up"?_=54:s==="claimed"?_=42:s==="new"?_=32:s==="no_response"?_=22:s==="lost"&&(_=12),t.existing&&(_+=8),t.duplicate&&(_-=6),p&&(_-=18),!d&&u===0&&(_+=6),t.beautician_id&&(_+=4),t.branch_id&&(_+=3),_=Math.max(5,Math.min(99,_));let h="warm",b=e("workspace.drawer_health_warm");_>=75?(h="hot",b=e("workspace.drawer_health_hot")):_<35||p||s==="lost"||s==="no_response"?(h="risk",b=e("workspace.drawer_health_risk")):_<50&&(h="cold",b=e("workspace.drawer_health_cold"));let k="med",f=e("workspace.drawer_priority_medium");p||s==="no_response"||!t.beautician_id&&r>=1?(k="high",f=e("workspace.drawer_priority_high")):(s==="converted"||s==="payment_verified")&&(k="low",f=e("workspace.drawer_priority_low"));const w=[];p&&w.push({tone:"danger",label:e("workspace.drawer_signal_overdue")}),r<=1&&s==="new"&&w.push({tone:"info",label:e("workspace.drawer_signal_fresh")}),t.existing&&w.push({tone:"ok",label:e("workspace.drawer_signal_existing")}),t.duplicate&&w.push({tone:"warn",label:e("workspace.drawer_signal_duplicate")}),!t.beautician_id&&s!=="converted"&&w.push({tone:"warn",label:e("workspace.drawer_signal_unassigned")}),!t.branch_id&&s!=="converted"&&w.push({tone:"warn",label:e("workspace.drawer_signal_no_branch")}),w.length||w.push({tone:"ok",label:e("workspace.drawer_signal_healthy")});const $=a.map((S,y)=>{const M=(B.statuses||[]).find(W=>W.value===S)?.label||S.replace(/_/g," ");return`<div class="journey-step${i>y||s==="converted"?" is-done":i===y?" is-active":""}" role="listitem"><span class="journey-step__dot"></span><span class="journey-step__label">${n(M)}</span></div>`}).join("");return{score:_,stagePct:l,daysInPipeline:r,daysSinceContact:u,neverContacted:d,healthTone:h,healthLabel:b,priorityTone:k,priorityLabel:f,signals:w,pipelineHtml:$}}let U=null,ba=[],fa={total_imports:0,raw:0,unique:0,duplicates:0,existing:0,invalid:0,imported:0},$e=!1;function Cn(){E.innerHTML=`${_e(e("import.title"),e("import.subtitle"),`<button type="button" class="btn" data-jump="imports">${n(e("import.history_btn"))}</button><button type="button" class="btn primary" data-jump="leads">${n(e("import.view_leads"))}</button>`)}
  <section class="card">
    <div class="tabs" id="importTabs" role="tablist">${[["paste",e("import.tab_paste")],["excel",e("import.tab_excel")],["csv",e("import.tab_csv")],["manual",e("import.tab_manual")]].map(t=>`<button type="button" class="tab ${c.importTab===t[0]?"active":""}" role="tab" aria-selected="${c.importTab===t[0]?"true":"false"}" data-tab="${t[0]}">${n(t[1])}</button>`).join("")}</div>
    <div id="importPane" style="margin-top:14px"></div>
  </section>
  <section class="card hidden" id="previewCard" style="margin-top:12px"></section>`,Nt(),ne(),Ln()}function Ln(){const t=T("[data-tab]","#importTabs");t.forEach(a=>{a.onclick=s=>{s.preventDefault();const i=a.dataset.tab;if(!i||i===c.importTab)return;c.importTab=i,t.forEach(r=>{const d=r===a;r.classList.toggle("active",d),r.setAttribute("aria-selected",d?"true":"false")}),U=null;const l=o("#previewCard");l&&l.classList.add("hidden"),Nt()}})}function Nt(){const t=o("#importPane");if(!t)return;const a=!!m.canCreateLead;if(c.importTab==="paste")t.innerHTML=`<div><div class="card-title">${n(e("import.paste_title"))}</div><div class="card-subtitle">${n(e("import.paste_sub"))}</div><textarea class="paste-area" id="pasteArea" placeholder="${n(e("import.paste_placeholder"))}"></textarea><div style="display:flex;justify-content:flex-end;margin-top:10px"><button type="button" class="btn primary" id="parseBtn" ${a?"":"disabled"}>${n(e("import.parse"))}</button></div></div>`,o("#parseBtn")&&(o("#parseBtn").onclick=()=>xn());else if(c.importTab==="manual")t.innerHTML=`<div class="detail-grid"><div><label class="kpi-label" for="manualName">${n(e("import.name"))}</label><input class="search" style="width:100%" id="manualName" autocomplete="name"></div><div><label class="kpi-label" for="manualPhone">${n(e("import.phone"))}</label><input class="search" style="width:100%" id="manualPhone" inputmode="tel" autocomplete="tel"></div><div><label class="kpi-label" for="manualEmail">${n(e("import.email"))}</label><input class="search" style="width:100%" id="manualEmail" type="email" autocomplete="email"></div><div><label class="kpi-label" for="manualSource">${n(e("import.source"))}</label><select class="control" style="width:100%" id="manualSource"><option value="TikTok">TikTok</option><option value="WhatsApp">WhatsApp</option><option value="Facebook">Facebook</option><option value="manual">Import</option></select></div><div style="grid-column:1/-1;text-align:right"><button type="button" class="btn primary" id="manualSaveBtn" ${a?"":"disabled"}>${n(e("import.save_lead"))}</button></div></div>`,o("#manualSaveBtn")&&(o("#manualSaveBtn").onclick=Pn);else{const s=c.importTab==="excel";t.innerHTML=`<div class="import-zone" id="importDropZone" tabindex="0"><div class="import-icon">⇧</div><h3>${n(e(s?"import.drop_excel":"import.drop_csv"))}</h3><p>${n(e(s?"import.accepted_excel":"import.accepted_csv"))}</p><input type="file" id="fileInput" class="hidden" accept="${s?".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel":".csv,text/csv,text/plain"}"><button type="button" class="btn primary" id="browseBtn" ${a?"":"disabled"}>${n(e("import.browse"))}</button></div>`;const i=o("#fileInput"),l=o("#browseBtn"),r=o("#importDropZone");l&&i&&(l.onclick=d=>{d.preventDefault(),d.stopPropagation(),i.click()}),i&&(i.onchange=()=>it(i.files?.[0])),r&&a&&(r.addEventListener("click",d=>{d.target===l||l?.contains(d.target)||i?.click()}),["dragenter","dragover"].forEach(d=>r.addEventListener(d,u=>{u.preventDefault(),u.stopPropagation(),r.classList.add("is-dragover")})),["dragleave","drop"].forEach(d=>r.addEventListener(d,u=>{u.preventDefault(),u.stopPropagation(),r.classList.remove("is-dragover")})),r.addEventListener("drop",d=>{const u=d.dataTransfer?.files?.[0];u&&it(u)}),r.addEventListener("keydown",d=>{(d.key==="Enter"||d.key===" ")&&(d.preventDefault(),i?.click())}))}}async function xn(){const t=o("#pasteArea")?.value||"";if(!t.trim()){g(e("import.paste_required"));return}await Ia({method:"paste",paste:t})}async function it(t){if(!t){g(e("import.file_required"));return}const a=new FormData;a.append("method",c.importTab==="excel"?"excel":"csv"),a.append("file",t),c.branch&&c.branch!=="all"&&a.append("spa_branch_id",c.branch),await Ia(a,!0)}async function Pn(){const t=(o("#manualName")?.value||"").trim(),a=(o("#manualPhone")?.value||"").trim(),s=(o("#manualEmail")?.value||"").trim(),i=(o("#manualSource")?.value||"manual").trim();if(!t||!a){g(e("import.rows_required"));return}await Ia({method:"manual",rows:[{name:t,phone:a,email:s||null,source:i}]})}async function Ia(t,a=!1){const s=m.importPreviewUrl;if(!s){g(e("import.preview_error"));return}if(!$e){$e=!0,g(e("import.parsing"));try{const i={method:"POST",credentials:"same-origin",headers:N(!a)};a?i.body=t:(c.branch&&c.branch!=="all"&&!t.spa_branch_id&&(t.spa_branch_id=Number(c.branch)||null),i.body=JSON.stringify(t));const l=await fetch(s,i),r=await l.json().catch(()=>({}));if(!l.ok){const d=r.message||Object.values(r.errors||{}).flat()[0]||e("import.preview_error");g(d);return}U=r.data||null,Bn()}catch(i){console.error(i),g(e("import.preview_error"))}finally{$e=!1}}}function Bn(){const t=o("#previewCard");if(!t||!U)return;const a=U.rows||[],s=U.summary||{},i=Number(s.ready||0)+Number(s.existing||0);t.classList.remove("hidden"),t.innerHTML=`<div class="card-title-row"><div><div class="card-title">${n(e("import.preview_title"))}</div><div class="card-subtitle">${n(e("import.preview_sub"))}</div></div><button class="btn small" type="button" id="closePreviewBtn">${n(e("import.close"))}</button></div>
  <div class="summary-strip">
    <div class="summary-chip"><label>${n(e("import.total_rows"))}</label><strong>${v(s.total||0)}</strong></div>
    <div class="summary-chip"><label>${n(e("import.ready"))}</label><strong>${v(s.ready||0)}</strong></div>
    <div class="summary-chip"><label>${n(e("import.duplicate"))}</label><strong>${v(s.duplicate||0)}</strong></div>
    <div class="summary-chip"><label>${n(e("import.existing"))}</label><strong>${v(s.existing||0)}</strong></div>
    <div class="summary-chip"><label>${n(e("import.invalid"))}</label><strong>${v(s.invalid||0)}</strong></div>
  </div>
  <div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${n(e("import.col_row"))}</th><th>${n(e("import.col_name"))}</th>
    <th title="${n(e("import.col_orig_phone"))}">${n(e("import.col_orig_phone"))}</th>
    <th title="${n(e("import.col_norm_phone"))}">${n(e("import.col_norm_phone"))}</th>
    <th>${n(e("import.col_email"))}</th><th>${n(e("import.col_detection"))}</th><th>${n(e("import.col_action"))}</th>
  </tr></thead><tbody>
  ${a.map(l=>`<tr><td>${l.row}</td><td><strong>${n(l.name||"")}</strong></td><td>${n(l.phone_orig||"")}</td><td>${n(l.phone_e164||l.phone_norm||"")}</td><td>${n(l.email||"")}</td><td>${x(l.detection)}</td><td>${l.detection==="READY"||l.detection==="EXISTING"?`<span class="badge success">${n(e("import.action_import"))}</span>`:`<span class="badge gray">${n(e("import.action_skip"))}</span>`}</td></tr>`).join("")||`<tr><td colspan="7"><div class="empty">${n(e("import.empty"))}</div></td></tr>`}
  </tbody></table></div>
  <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px"><button class="btn" type="button" id="cancelImportBtn">${n(e("import.cancel"))}</button><button class="btn primary" type="button" id="confirmImport" ${i>0?"":"disabled"}>${n(e("import.confirm",{count:i}))}</button></div>`,o("#closePreviewBtn")?.addEventListener("click",()=>t.classList.add("hidden")),o("#cancelImportBtn")?.addEventListener("click",()=>t.classList.add("hidden")),o("#confirmImport")?.addEventListener("click",jn),t.scrollIntoView({behavior:"smooth",block:"start"})}async function jn(){if(!U||$e)return;const t=m.importConfirmUrl;if(!t){g(e("import.confirm_error"));return}const a=(U.rows||[]).map(s=>({name:s.name,phone:s.phone_norm||s.phone_orig,email:s.email||null,source:s.source||U.source||"import",import:s.detection==="READY"||s.detection==="EXISTING"}));$e=!0,g(e("import.importing"));try{const s=await fetch(t,{method:"POST",credentials:"same-origin",headers:N(!0),body:JSON.stringify({method:U.method||"paste",rows:a,source:U.source||"import",file_name:U.file_name||null,spa_branch_id:U.spa_branch_id||null,beautician_id:U.beautician_id||null})}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("import.confirm_error"));return}g(i.message||e("import.imported",{count:i.data&&i.data.imported||0})),U=null,setTimeout(()=>V("leads"),700)}catch(s){console.error(s),g(e("import.confirm_error"))}finally{$e=!1}}async function En(){E.innerHTML=`${_e(e("import.history_title"),e("import.history_subtitle"),`<button class="btn primary" data-jump="import">${n(e("import.new_import"))}</button>`)}
  <div class="grid kpi-grid" id="importKpiMount"></div>
  <section class="card" style="margin-top:12px"><div id="importHistoryMount"><div class="empty">${n(e("workspace.loading"))}</div></div></section>`,ne(),await Dt()}async function Dt(){const t=m.importHistoryUrl,a=o("#importHistoryMount"),s=o("#importKpiMount");if(!t){a&&(a.innerHTML=`<div class="empty">${n(e("import.load_error"))}</div>`);return}try{const i=await fetch(t+"?per_page=50&page="+Qa,{headers:N(!1),credentials:"same-origin"}),l=await i.json().catch(()=>({}));if(!i.ok){g(l.message||e("import.load_error"));return}ba=l.data||[],fa=l.meta?.summary||fa;const r=fa;if(s&&(s.innerHTML=`${j("▤",e("import.kpi_total"),v(r.total_imports),e("common.this_month"),e("import.meta_batches"),"blue")}${j("♙",e("import.kpi_raw"),v(r.raw),e("import.meta_historical"),"","rose")}${j("✓",e("import.kpi_unique"),v(r.unique),e("import.meta_after_clean"),"","green")}${j("⧉",e("import.kpi_duplicates"),v(r.duplicates),e("import.meta_auditable"),"","purple")}${j("♧",e("import.kpi_existing"),v(r.existing),e("import.meta_phone"),"","blue")}`),!a)return;if(!ba.length){a.innerHTML=`<div class="empty"><strong>${n(e("import.empty"))}</strong>${n(e("import.empty_hint"))}</div>`;return}a.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
      <th>${n(e("import.col_batch"))}</th><th>${n(e("import.col_date"))}</th>
      <th title="${n(e("import.col_by"))}">${n(e("import.col_by"))}</th>
      <th>${n(e("import.col_method"))}</th><th>${n(e("import.col_file"))}</th>
      <th>${n(e("import.col_raw"))}</th><th>${n(e("import.col_unique"))}</th>
      <th>${n(e("import.col_duplicate"))}</th><th>${n(e("import.col_existing"))}</th>
      <th>${n(e("import.col_invalid"))}</th><th>${n(e("import.col_status"))}</th>
    </tr></thead><tbody>
    ${ba.map(d=>`<tr>
      <td><strong>${n(d.batch_code||"")}</strong></td><td>${n(d.date||"")}</td>
      <td>${n(d.by||"")}</td><td>${n(d.method||"")}</td><td>${n(d.file||"")}</td>
      <td>${v(d.raw)}</td><td>${v(d.unique)}</td><td>${v(d.duplicate)}</td>
      <td>${v(d.existing)}</td><td>${v(d.invalid)}</td><td>${x(d.status)}</td>
    </tr>`).join("")}
    </tbody></table></div>${ae("imports",l.meta||{})}`,te("imports",d=>(Qa=d,Dt()))}catch(i){console.error(i),a&&(a.innerHTML=`<div class="empty">${n(e("import.load_error"))}</div>`)}}function qt(){const t=c.paymentCustomerId?`<div class="pay-customer-chip" id="payCustomerChip">
        <span>${n(e("payments.filtered_customer",{name:c.paymentCustomerLabel||"#"+c.paymentCustomerId}))}</span>
        <button type="button" class="btn small soft" id="payClearCustomer">${n(e("payments.clear_customer"))}</button>
      </div>`:"";E.innerHTML=`<div class="pay-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${n(e("payments.title"))}</h1>
        <div class="page-subtitle">${n(e("payments.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="payRefresh">${n(e("payments.refresh"))}</button>
        ${m.canViewOrder&&m.ordersIndexUrl?`<a class="btn primary" href="${n(m.ordersIndexUrl)}" target="_blank" rel="noopener">${n(e("payments.open_orders"))}</a>`:""}
      </div>
    </div>
    ${t}
    <div class="pay-metrics" id="payHeroStats"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro">
          <p class="lead-panel__sub" id="payHeroCopy" style="margin:0">${n(c.paymentCustomerId?e("payments.filtered_customer",{name:c.paymentCustomerLabel||"#"+c.paymentCustomerId}):e("payments.subtitle"))}</p>
        </div>
        <div class="lead-panel__head-meta">
          <span class="lead-panel__count" id="payResultCount">—</span>
        </div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="payTabs" role="tablist"></div>
        <label class="lead-search" for="paySearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="paySearch" type="search" autocomplete="off" placeholder="${n(e("payments.search_placeholder"))}" value="${n(c.paymentSearch||"")}" />
        </label>
        <div class="lead-filter-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
          <label class="lead-field">
            <span class="lead-field__label">${n(e("payments.filter_beautician"))}</span>
            <select class="lead-field__control" id="payBeautician"></select>
          </label>
          <label class="lead-field">
            <span class="lead-field__label">${n(e("payments.filter_branch"))}</span>
            <select class="lead-field__control" id="payBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="payTableMount"></div>
    </section>
  </div>`;const a=o("#payRefresh");a&&(a.onclick=()=>Z());const s=o("#payClearCustomer");s&&(s.onclick=()=>An()),Dn(),Z()}function An(){c.paymentCustomerId=null,c.paymentCustomerLabel="",c.paymentSearch="",c.paymentPage=1,c.view==="payments"?qt():Z()}function Nn(t){if(!t)return;const a=Number(t.id||0);a&&(c.paymentCustomerId=a,c.paymentCustomerLabel=String(t.name||t.code||"#"+a),c.paymentSearch=String(t.phone||t.email||t.name||"").trim(),c.paymentTab="all",c.paymentPage=1,c.paymentBeautician="all",V("payments"))}function Dn(){const t=o("#paySearch");t&&(t.oninput=()=>{clearTimeout(Ka),Ka=setTimeout(()=>{c.paymentSearch=t.value.trim(),c.paymentPage=1,Z()},320)});const a=o("#payBeautician");a&&(a.onchange=()=>{c.paymentBeautician=a.value,c.paymentPage=1,Z()});const s=o("#payBranch");s&&(s.onchange=()=>{c.paymentBranch=s.value,c.paymentPage=1,Z()})}async function Z(){const t=m.paymentsUrl||"",a=o("#payTableMount");if(!t){a&&(a.innerHTML=`<div class="pay-empty"><strong>${n(e("payments.load_error"))}</strong></div>`);return}aa=!0,lt(),rt();const s=new URLSearchParams;c.paymentCustomerId?s.set("customer_id",String(c.paymentCustomerId)):c.paymentSearch&&s.set("q",c.paymentSearch),c.paymentTab&&c.paymentTab!=="all"&&s.set("status",c.paymentTab);const i=c.paymentBranch!=="all"?c.paymentBranch:c.branch||"all";i&&i!=="all"&&s.set("branch",i),c.paymentBeautician&&c.paymentBeautician!=="all"&&s.set("beautician",c.paymentBeautician),c.period&&s.set("period",c.period),s.set("page",String(c.paymentPage||1)),s.set("per_page","25");try{const l=await fetch(`${t}?${s.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("payments "+l.status);const r=await l.json();be=Array.isArray(r.data)?r.data:[],Ba=Object.assign({pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0},r.meta&&r.meta.summary||{}),fe=Object.assign({statuses:[],beauticians:[],branches:[]},r.filters||{}),Sa={current_page:r.meta&&r.meta.current_page||1,last_page:r.meta&&r.meta.last_page||1,total:r.meta&&r.meta.total||0}}catch(l){console.error(l),be=[],g(e("payments.load_error"))}finally{aa=!1,lt(),Hn(),Rn(),rt()}}function qn(t){const a=String(t||"pending");return a==="paid"?{customer:"done",accountant:"done",hq:"done",active:null}:a==="processing"?{customer:"done",accountant:"done",hq:"active",active:"hq"}:a==="canceled"||a==="refunded"?{customer:"done",accountant:"active",hq:null,active:"accountant"}:{customer:"done",accountant:"active",hq:null,active:"accountant"}}function Ht(t){const a=qn(t),s=l=>{const r=a[l];return`<span class="pay-pipe__node ${r==="done"?"is-done":r==="active"?"is-active":""}" title="${n(e("payments.flow_"+(l==="hq"?"hq":l)))}"></span>`},i=l=>`<span class="pay-pipe__line ${a[l]==="done"?"is-done":""}"></span>`;return`<div class="pay-pipe" title="${n(e("payments.pipeline"))}">${s("customer")}${i("customer")}${s("accountant")}${i("accountant")}${s("hq")}</div>`}function lt(){const t=Ba,a=o("#payHeroCopy");a&&(a.textContent=t.queue>0?e("payments.pulse_busy",{count:v(t.queue),amount:R(t.pending_amount)}):e("payments.pulse_clear"));const s=o("#payHeroStats");s&&(s.innerHTML=`
      <div class="pay-metric"><span>${n(e("payments.stat_queue"))}</span><strong>${v(t.queue)}</strong></div>
      <div class="pay-metric"><span>${n(e("payments.stat_paid"))}</span><strong>${R(t.paid_amount)}</strong></div>
      <div class="pay-metric"><span>${n(e("payments.stat_today"))}</span><strong>${v(t.paid_today)}</strong></div>
      <div class="pay-metric"><span>${n(e("payments.stat_hold"))}</span><strong>${v(t.hold)}</strong></div>
    `)}function Hn(){const t=o("#payTabs");if(!t)return;const a=Ba,s={all:(a.pending||0)+(a.processing||0)+(a.paid||0)+(a.hold||0)+(a.refunded||0),queue:a.queue||0,pending:a.pending||0,processing:a.processing||0,paid:a.paid||0,canceled:a.hold||0,refunded:a.refunded||0},l=[...fe.statuses&&fe.statuses.length?fe.statuses:[{value:"queue",label:e("payments.tab_queue")},{value:"all",label:e("payments.tab_all")},{value:"pending",label:e("payments.tab_pending")},{value:"processing",label:e("payments.tab_processing")},{value:"paid",label:e("payments.tab_paid")},{value:"canceled",label:e("payments.tab_hold")},{value:"refunded",label:e("payments.tab_refunded")}]].sort((r,d)=>(r.value==="queue"?-1:0)-(d.value==="queue"?-1:0));t.innerHTML=l.map(r=>{const d=r.value,u=c.paymentTab===d?"active":"",p=s[d],_=p!==void 0?`<span class="pay-tab-count">${v(p)}</span>`:"";return`<button type="button" class="tab ${u}" role="tab" data-ptab="${n(d)}">${n(r.label)}${_}</button>`}).join(""),T("[data-ptab]",t).forEach(r=>{r.onclick=()=>{c.paymentTab=r.dataset.ptab,c.paymentPage=1,Z()}})}function Rn(){const t=o("#payBeautician");if(t){const s=c.paymentBeautician;t.innerHTML=`<option value="all">${n(e("payments.all_beauticians"))}</option>`+(fe.beauticians||[]).map(i=>`<option value="${i.id}" ${String(s)===String(i.id)?"selected":""}>${n(i.name)}</option>`).join("")}const a=o("#payBranch");if(a){const s=c.paymentBranch;a.innerHTML=`<option value="all">${n(e("payments.all_branches"))}</option>`+(fe.branches||[]).map(i=>`<option value="${i.id}" ${String(s)===String(i.id)?"selected":""}>${n(i.code?i.code+" · "+i.name:i.name)}</option>`).join("")}}function rt(){const t=o("#payTableMount"),a=o("#payResultCount");if(a&&(a.textContent=e("payments.result_count",{count:v(Sa.total||be.length)})),!t)return;if(aa){t.innerHTML=`<div class="pay-empty"><strong>${n(e("payments.loading"))}</strong></div>`;return}if(!be.length){t.innerHTML=`<div class="pay-empty"><strong>${n(e("payments.empty"))}</strong><span>${n(e("payments.empty_hint"))}</span></div>`;return}const s=be.map(l=>{const r=l.ref&&l.ref!=="—",d=[l.has_proof?`<span class="pay-chip pay-chip--ok">${n(e("payments.chip_proof"))}</span>`:`<span class="pay-chip pay-chip--muted">${n(e("payments.stage_declared"))}</span>`,r?`<span class="pay-chip pay-chip--ok">${n(e("payments.chip_ref"))}</span>`:`<span class="pay-chip pay-chip--warn">${n(e("payments.chip_no_ref"))}</span>`].join("");return`<tr>
      <td><span class="pay-id">${n(l.code||"ORD-"+l.id)}</span></td>
      <td><div class="person-cell"><div class="mini-avatar">${n(l.initial||"?")}</div><div><strong>${n(l.customer||"")}</strong><small>${n(l.phone||"")}</small></div></div></td>
      <td>${n(l.branch||"—")}</td>
      <td>${n(l.beautician||"—")}</td>
      <td><div class="pay-amount">${R(l.amount)}</div><div class="pay-method">${n(l.payment_method_label||"")}</div></td>
      <td><div class="pay-chips">${d}</div><div class="pay-method" title="${n(e("payments.col_ref"))}">${n(l.ref||"—")}</div></td>
      <td>${Ht(l.payment_status)}</td>
      <td>${x(l.payment_status_label||l.payment_status)}</td>
      <td><button type="button" class="pay-review-btn" data-payment="${l.id}">${n(e("payments.review"))}</button></td>
    </tr>`}).join(""),i=ae("pay",Sa);t.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${n(e("payments.col_id"))}</th>
    <th>${n(e("payments.col_customer"))}</th>
    <th>${n(e("payments.col_branch"))}</th>
    <th>${n(e("payments.col_beautician"))}</th>
    <th>${n(e("payments.col_amount"))}</th>
    <th>${n(e("payments.col_ref"))}</th>
    <th>${n(e("payments.pipeline"))}</th>
    <th>${n(e("payments.col_status"))}</th>
    <th>${n(e("payments.col_action"))}</th>
  </tr></thead><tbody>${s}</tbody></table></div>${i}`,T("[data-payment]",t).forEach(l=>l.onclick=()=>In(Number(l.dataset.payment))),te("pay",l=>(c.paymentPage=l,Z()))}function Un(t){const a=t.proof;let s;if(!m.canViewOrder)s=`<p class="payment-proof__empty">${n(e("payments.proof_access"))}</p>`;else if(!a?.url)s=`<p class="payment-proof__empty">${n(e(t.has_proof?"payments.proof_unavailable":"payments.proof_missing"))}</p>`;else{const i=n(a.url),l=n(a.name||e("payments.proof_title")),r=`<a class="btn small" href="${i}" target="_blank" rel="noopener noreferrer">${n(e("payments.proof_open"))}</a>`;s=`${a.kind==="image"?`<a class="payment-proof__image" href="${i}" target="_blank" rel="noopener noreferrer"><img id="paymentProofImage" src="${i}" alt="${l}" loading="lazy"></a><p class="payment-proof__empty" id="paymentProofError" hidden>${n(e("payments.proof_unavailable"))}</p>`:a.kind==="pdf"?`<object class="payment-proof__pdf" data="${i}" type="application/pdf" aria-label="${l}"><p class="payment-proof__empty">${n(e("payments.proof_pdf_hint"))}</p></object>`:`<p class="payment-proof__empty">${n(e("payments.proof_pdf_hint"))}</p>`}<div class="payment-proof__file"><span>${l}</span>${r}</div>`}return`<section class="payment-proof"><h3>${n(e("payments.proof_title"))}</h3>${s}</section>`}function In(t){const a=be.find(_=>Number(_.id)===Number(t));if(!a)return;const s=q(m.orderShowUrlTemplate,a.id),i=!!m.canEditOrder,l=!!m.canViewOrder,r=["identity","invoice","method","ref","proof","status"].map(_=>{const h=a.checklist?.[_],b=["completed","not_applicable"].includes(h)?h:"pending";return`<div class="pay-review__check is-${b}" data-check="${_}"><span class="pay-review__check-icon" aria-hidden="true">${b==="completed"?"✓":b==="not_applicable"?"—":"○"}</span><span>${n(e("payments.check_"+_))}</span><small>${n(e("payments.check_"+b))}</small></div>`}).join(""),d=`<div class="pay-review">
    <div class="pay-review__hero">
      <div class="pay-review__avatar">${n(a.initial||"?")}</div>
      <div style="min-width:0;flex:1">
        <div class="pay-id">${n(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${n(a.customer||"")}</strong>
        <div class="pay-method">${n(a.phone||"")} · ${n(a.branch_name||a.branch||"")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px;align-items:center">${x(a.payment_status_label||a.payment_status)}${Ht(a.payment_status)}</div>
      </div>
      <div style="text-align:right"><div class="pay-amount">${R(a.amount)}</div><div class="pay-method">${n(a.payment_method_label||"")}</div></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${n(e("payments.col_customer_stage"))}</label><strong>${n(a.customer_stage)}</strong></div>
      <div class="pay-review__card"><label>${n(e("payments.col_accountant"))}</label><strong>${n(a.accountant_stage)}</strong></div>
      <div class="pay-review__card"><label>${n(e("payments.col_hq"))}</label><strong>${n(a.hq_stage)}</strong></div>
    </div>
    ${Un(a)}
    ${i?`<div class="detail-grid">
      <div class="detail-box"><label>${n(e("payments.bank_ref"))}</label><input class="control" id="payRefInput" value="${n(a.ref==="—"?"":a.ref)}" placeholder="${n(e("payments.bank_ref_ph"))}" /></div>
      <div class="detail-box" style="grid-column:span 2"><label>${n(e("payments.admin_note"))}</label><input class="control" id="payNoteInput" value="${n(a.admin_note||"")}" /></div>
    </div>`:""}
    <div class="journey-section"><div class="journey-section__title">${n(e("payments.checklist"))}</div><div class="pay-review__checks">${r}</div><p class="pay-review__check-note">${n(e("payments.check_note"))}</p></div>
    ${l?"":`<p class="card-subtitle">${n(e("payments.no_order_access"))}</p>`}
  </div>`,u=[l?`<a class="btn" href="${n(s)}" target="_blank" rel="noopener">${n(e("payments.open_order"))}</a>`:"",i?`<button type="button" class="btn" id="payMarkProcessing">${n(e("payments.mark_processing"))}</button>`:"",i?`<button type="button" class="btn danger" id="payMarkHold">${n(e("payments.mark_hold"))}</button>`:"",i?`<button type="button" class="btn success" id="payMarkPaid">${n(e("payments.mark_paid"))}</button>`:"",`<button type="button" class="btn" data-action-drawer-close>${n(e("payments.close"))}</button>`].filter(Boolean).join("");Y(e("payments.review_title"),e("payments.review_sub",{code:a.code,customer:a.customer}),d,u,"Pay Verify");const p=o("#paymentProofImage");if(p){const _=()=>{p.closest("a").hidden=!0,o("#paymentProofError").hidden=!1};p.onerror=_,p.complete&&!p.naturalWidth&&_()}setTimeout(()=>{const _=o("#payMarkProcessing");_&&(_.onclick=()=>ga(a,"processing"));const h=o("#payMarkHold");h&&(h.onclick=()=>ga(a,"canceled"));const b=o("#payMarkPaid");b&&(b.onclick=()=>ga(a,"paid"))},0)}async function ga(t,a){const s=q(m.orderPaymentStatusUrlTemplate,t.id);if(!s||!m.canEditOrder){g(e("payments.update_error"));return}const i=o("#payRefInput"),l=o("#payNoteInput"),r=i?i.value.trim():"",d=l?l.value.trim():"";if(t.needs_reference&&(a==="paid"||a==="processing")&&!r&&(t.ref==="—"||!t.ref)){g(e("payments.ref_required"));return}try{const u=await fetch(s,{method:"PUT",headers:N(!0),credentials:"same-origin",body:JSON.stringify({payment_status:a,transaction_id:r||void 0,admin_note:d||void 0})}),p=await u.json().catch(()=>({}));if(!u.ok){g(p.message||e("payments.update_error"));return}D(),g(p.message||e("payments.updated")),await Z()}catch(u){console.error(u),g(e("payments.update_error"))}}function Fn(){E.innerHTML=`<div class="cin-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${n(e("checkin.title"))}</h1>
        <div class="page-subtitle">${n(e("checkin.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="cinRefresh">${n(e("checkin.refresh"))}</button>
        ${m.canConfirmCheckin?`<button type="button" class="btn primary" id="cinScan">${n(e("checkin.scan"))}</button>`:""}
        ${m.canViewTreatments&&m.treatmentReservationsUrl?`<a class="btn" href="${n(m.treatmentReservationsUrl)}" target="_blank" rel="noopener">${n(e("checkin.open_crm"))}</a>`:""}
      </div>
    </div>
    <section class="clearance-flow checkin-flow" aria-labelledby="checkinFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CRM</span><h2 id="checkinFlowTitle">${n(e("checkin.workflow_title"))}</h2><p>${n(e("checkin.workflow_hint"))}</p></div>
      <ol class="clearance-flow__steps">
        ${[e("checkin.step_booked"),e("checkin.step_checked_in"),e("checkin.step_clearance"),e("checkin.step_treatment")].map((s,i)=>`<li><span aria-hidden="true">${i+1}</span><strong>${n(s)}</strong></li>`).join("")}
      </ol>
    </section>
    <div class="pay-metrics" id="cinMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${n(e("checkin.subtitle"))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="cinResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="cinTabs" role="group"></div>
        <label class="lead-search" for="cinSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="cinSearch" aria-label="${n(e("checkin.search_placeholder"))}" type="search" autocomplete="off" placeholder="${n(e("checkin.search_placeholder"))}" value="${n(c.checkinSearch||"")}" />
        </label>
        <div class="lead-filter-grid ops-filter-grid">
          <label class="lead-field"><span class="lead-field__label">${n(e("checkin.filter_scope"))}</span>
            <select class="lead-field__control" id="cinScope">
              <option value="pipeline"${c.checkinScope==="pipeline"?" selected":""}>${n(e("checkin.scope_pipeline"))}</option>
              <option value="day"${c.checkinScope==="day"?" selected":""}>${n(e("checkin.scope_day"))}</option>
            </select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${n(e("checkin.filter_date"))}</span>
            <input class="lead-field__control" id="cinDate" type="date" value="${n(c.checkinDate||"")}"${c.checkinScope==="pipeline"?" disabled":""} />
          </label>
          <label class="lead-field"><span class="lead-field__label">${n(e("checkin.filter_beautician"))}</span>
            <select class="lead-field__control" id="cinBeautician"></select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${n(e("checkin.filter_branch"))}</span>
            <select class="lead-field__control" id="cinBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="cinTableMount"></div>
    </section>
  </div>`;const t=o("#cinRefresh");t&&(t.onclick=()=>K());const a=o("#cinScan");a&&(a.onclick=Gn),On(),K()}function On(){const t=o("#cinSearch");t&&(t.oninput=()=>{clearTimeout(Ga),Ga=setTimeout(()=>{c.checkinSearch=t.value.trim(),c.checkinPage=1,K()},320)});const a=o("#cinScope");a&&(a.onchange=()=>{c.checkinScope=a.value,a.value==="pipeline"&&(c.checkinStatus="live");const r=o("#cinDate");r&&(r.disabled=a.value==="pipeline"),c.checkinPage=1,K()});const s=o("#cinDate");s&&(s.onchange=()=>{c.checkinDate=s.value,c.checkinScope="day",o("#cinScope").value="day",c.checkinPage=1,K()});const i=o("#cinBeautician");i&&(i.onchange=()=>{c.checkinBeautician=i.value,c.checkinPage=1,K()});const l=o("#cinBranch");l&&(l.onchange=()=>{c.checkinBranch=l.value,c.checkinPage=1,K()})}async function K(){const t=++Ke;Re=!1;const a=m.checkinUrl||"",s=o("#cinTableMount");if(!a){s&&(s.innerHTML=`<div class="pay-empty"><strong>${n(e("checkin.load_error"))}</strong></div>`);return}pe=!0,ct(),ot();const i=new URLSearchParams;c.checkinSearch&&i.set("q",c.checkinSearch),i.set("status",c.checkinStatus||"live"),i.set("scope",c.checkinScope||"day"),c.checkinDate&&i.set("date",c.checkinDate);const l=c.checkinBranch!=="all"?c.checkinBranch:c.branch||"all";l&&l!=="all"&&i.set("branch",l),c.checkinBeautician&&c.checkinBeautician!=="all"&&i.set("beautician",c.checkinBeautician),i.set("page",String(c.checkinPage||1)),i.set("per_page","25");try{const r=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!r.ok)throw new Error("checkin "+r.status);const d=await r.json();if(t!==Ke)return;de=Array.isArray(d.data)?d.data:[],Na=Object.assign({live:0,scheduled:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0},d.meta&&d.meta.summary||{}),Ma=Object.assign({statuses:[],beauticians:[],branches:[]},d.filters||{}),Da={current_page:d.meta&&d.meta.current_page||1,last_page:d.meta&&d.meta.last_page||1,total:d.meta&&d.meta.total||0}}catch(r){if(t!==Ke)return;Re=!0,console.error(r),de=[],g(e("checkin.load_error"))}finally{if(t!==Ke)return;pe=!1,ct(),Vn(),Wn(),ot()}}function ct(){const t=o("#cinMetrics");if(!t)return;const a=Na;t.setAttribute("aria-busy",String(pe));const s=i=>pe||Re?"—":v(i);t.innerHTML=`
    <div class="pay-metric checkin-metric checkin-metric--scheduled"><span>${n(e("checkin.stat_scheduled"))}</span><strong>${s(a.scheduled)}</strong></div>
    <div class="pay-metric checkin-metric checkin-metric--waiting"><span>${n(e("checkin.stat_waiting"))}</span><strong>${s(a.waiting)}</strong><small>${n(e("checkin.stat_waiting_meta",{minutes:s(a.avg_wait_mins),unpaid:s(a.unpaid)}))}</small></div>
    <div class="pay-metric checkin-metric checkin-metric--treatment"><span>${n(e("checkin.stat_treatment"))}</span><strong>${s(a.in_treatment)}</strong></div>
    <div class="pay-metric checkin-metric checkin-metric--done"><span>${n(e("checkin.stat_completed"))}</span><strong>${s(a.completed)}</strong></div>`}function Vn(){const t=o("#cinTabs");if(!t)return;const a=Na,s=[["live",e("checkin.tab_live"),a.live],["booked",e("checkin.tab_booked"),a.scheduled],["waiting",e("checkin.tab_waiting"),a.waiting],["in_progress",e("checkin.tab_treatment"),a.in_treatment],["completed",e("checkin.tab_completed"),a.completed],["all",e("checkin.tab_all"),null]];t.innerHTML=s.filter(([i])=>c.checkinScope!=="pipeline"||!["completed","all"].includes(i)).map(([i,l,r])=>{const d=c.checkinStatus===i?"active":"",u=r==null?"":` (${v(r)})`;return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-cintab="${i}">${n(l)}${u}</button>`}).join(""),T("[data-cintab]",t).forEach(i=>i.onclick=()=>{c.checkinStatus=i.dataset.cintab,["completed","all"].includes(c.checkinStatus)&&(c.checkinScope="day",o("#cinScope").value="day"),c.checkinPage=1,K()})}function Wn(){const t=o("#cinBeautician");if(t){const i=c.checkinBeautician||"all";t.innerHTML=`<option value="all">${n(e("checkin.all_beauticians"))}</option>`+(Ma.beauticians||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${n(l.name)}</option>`).join("")}const a=o("#cinBranch");if(a){const i=c.checkinBranch||"all";a.innerHTML=`<option value="all">${n(e("checkin.all_branches"))}</option>`+(Ma.branches||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${n(l.name)}</option>`).join("")}const s=o("#cinResultCount");s&&(s.textContent=e("checkin.result_count",{count:Re?"—":v(Da.total)}))}function ot(){const t=o("#cinTableMount");if(!t)return;if(t.setAttribute("aria-busy",String(pe)),Re){t.innerHTML=`<div class="pay-empty" role="alert"><strong>${n(e("checkin.load_error"))}</strong><button type="button" class="btn" id="cinRetry">${n(e("checkin.refresh"))}</button></div>`,o("#cinRetry").onclick=()=>K();return}if(pe){t.innerHTML=`<div class="pay-empty" role="status">${n(e("checkin.loading"))}</div>`;return}if(!de.length){t.innerHTML=`<div class="pay-empty"><strong>${n(e("checkin.empty"))}</strong>${n(e("checkin.empty_hint"))}</div>`;return}const a=de.map(s=>`<tr data-checkin-status="${n(s.status||"pending")}" data-arrival-state="${s.checked_in_at?"arrived":"booked"}">
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${n(s.initial||"?")}</div><div class="person-cell__text"><strong>${n(s.name||"")}</strong><small>${n(s.code||"")} · ${n(s.phone||"")}</small></div></div></td>
    <td><strong>${n(s.date_label||"")}</strong><small class="checkin-cell-meta">${n(s.time||"—")}</small></td>
    <td><strong>${n(s.branch_name||s.branch||"—")}</strong><small class="checkin-cell-meta">${n(s.beautician||"—")}</small></td>
    <td>${n(s.treatment||"—")}</td>
    <td><div class="checkin-status-stack">${x(s.payment_label)}${x(s.clearance_label)}</div></td>
    <td><div class="checkin-status-stack">${x(s.arrival_label)}<small>${n(s.waiting_label||"—")}</small></div></td>
    <td><div class="checkin-row-actions">${Rt(s,!0)}<button type="button" class="btn small soft" data-cin-view="${s.id}">${n(e("checkin.view"))}</button></div></td>
  </tr>`).join("");t.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${n(e("checkin.col_customer"))}</th><th>${n(e("checkin.col_time"))}</th>
    <th>${n(e("checkin.col_branch"))} / ${n(e("checkin.col_beautician"))}</th>
    <th>${n(e("checkin.col_treatment"))}</th><th>${n(e("checkin.col_payment"))} / ${n(e("checkin.col_clearance"))}</th>
    <th>${n(e("checkin.col_status"))}</th><th>${n(e("checkin.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${ae("cin",Da)}`,T("[data-cin-view]",t).forEach(s=>s.onclick=()=>Xn(s.dataset.cinView)),Ut(t),te("cin",s=>(c.checkinPage=s,K()))}function Rt(t,a=!1){const s=Array.isArray(t.available_actions)?t.available_actions:[],i=a?"btn small":"btn";return s.includes("confirm_arrival")&&m.canConfirmCheckin?`<button type="button" class="${i} primary" data-cin-confirm="${t.id}">${n(e("checkin.confirm_arrival"))}</button>`:s.includes("open_clearance")?`<button type="button" class="${i} primary" data-cin-clearance="${t.id}">${n(e("checkin.go_clearance"))}</button>`:s.includes("open_crm")&&m.canViewTreatments&&m.treatmentReservationsUrl?`<a class="${i} primary" href="${n(m.treatmentReservationsUrl)}" target="_blank" rel="noopener">${n(e("checkin.open_crm"))}</a>`:""}function Yn(t){return t.status==="completed"?e("checkin.action_help_completed"):t.status==="in_progress"?e("checkin.action_help_treatment"):e(t.checked_in_at?"checkin.action_help_waiting":"checkin.action_help_booked")}function Ut(t=document){T("[data-cin-confirm]",t).forEach(a=>a.onclick=()=>Kn(a.dataset.cinConfirm)),T("[data-cin-clearance]",t).forEach(a=>a.onclick=()=>{const s=de.find(i=>Number(i.id)===Number(a.dataset.cinClearance));s&&(c.clearanceSearch=String(s.phone||s.code||s.name||"").trim()),D(),V("clearance")})}function Kn(t){const a=de.find(s=>Number(s.id)===Number(t));a&&(Y(e("checkin.confirm_title"),`${a.code} · ${a.name}`,`<div class="checkin-confirm"><div class="clearance-confirm__icon">${pa(!0)}</div><p>${n(e("checkin.confirm_body"))}</p></div>`,`<button type="button" class="btn" data-action-drawer-close>${n(e("checkin.cancel"))}</button><button type="button" class="btn primary" id="confirmCheckinArrival">${n(e("checkin.confirm_action"))}</button>`,e("checkin.workflow_title")),o("#confirmCheckinArrival").onclick=s=>zn(a,s.currentTarget))}async function zn(t,a){const s=q(m.checkinConfirmUrlTemplate,t.id);if(!s||!m.canConfirmCheckin){g(e("checkin.confirm_error"));return}a.disabled=!0,a.setAttribute("aria-busy","true");try{const i=await fetch(s,{method:"POST",headers:N(!0),credentials:"same-origin",body:JSON.stringify({})}),l=await i.json().catch(()=>({}));if(!i.ok){g(l.message||e("checkin.confirm_error"));return}D(),g(l.message||e("checkin.checkin_confirmed",{code:t.code})),await K()}catch(i){console.error(i),g(e("checkin.confirm_error"))}finally{a.isConnected&&(a.disabled=!1,a.removeAttribute("aria-busy"))}}function Jn(t){try{const a=new URL(String(t||"").trim(),window.location.origin),s=new URL(m.checkinPassBaseUrl,window.location.origin);return a.origin===s.origin&&a.pathname.startsWith(s.pathname.replace(/\/$/,"")+"/")&&a.searchParams.has("expires")&&a.searchParams.has("signature")}catch{return!1}}function ra(){cancelAnimationFrame(Ta),Ta=0,ye&&(ye.getTracks().forEach(a=>a.stop()),ye=null);const t=o("#cinScannerVideo");t&&(t.srcObject=null)}function Gn(){const t=`<div class="cin-scanner">
    <p class="cin-scanner__hint">${n(e("checkin.scanner_hint"))}</p>
    <div class="cin-scanner__viewport"><video id="cinScannerVideo" playsinline muted aria-label="${n(e("checkin.scanner_title"))}"></video><div class="cin-scanner__guide" aria-hidden="true"></div></div>
    <p class="cin-scanner__status" id="cinScannerStatus" aria-live="polite"></p>
    <button type="button" class="btn primary" id="cinScannerStart">${n(e("checkin.scanner_start"))}</button>
    <label class="lead-field cin-scanner__manual"><span class="lead-field__label">${n(e("checkin.scanner_manual_label"))}</span>
      <input class="lead-field__control" id="cinScannerInput" type="url" inputmode="url" autocomplete="off" placeholder="${n(e("checkin.scanner_manual_placeholder"))}">
    </label>
  </div>`,a=`<button type="button" class="btn primary" id="cinScannerOpen">${n(e("checkin.scanner_open"))}</button><button type="button" class="btn" data-action-drawer-close>${n(e("checkin.close"))}</button>`;Y(e("checkin.scanner_title"),e("checkin.scanner_hint"),t,a,e("nav.checkin")),o("#cinScannerStart").onclick=It,o("#cinScannerOpen").onclick=()=>xa(o("#cinScannerInput").value),o("#cinScannerInput").onkeydown=s=>{s.key==="Enter"&&(s.preventDefault(),xa(s.currentTarget.value))}}function xa(t){if(!Jn(t)){const a=o("#cinScannerStatus");a&&(a.textContent=e("checkin.scanner_invalid"));return}ra(),window.location.assign(String(t).trim())}async function It(){const t=o("#cinScannerStatus"),a=o("#cinScannerStart");if(!("BarcodeDetector"in window)||!navigator.mediaDevices?.getUserMedia){t.textContent=e("checkin.scanner_unsupported");return}a.disabled=!0,t.textContent=e("common.loading");try{if(!(BarcodeDetector.getSupportedFormats?await BarcodeDetector.getSupportedFormats():["qr_code"]).includes("qr_code"))throw new Error("QR format is unavailable");const i=new BarcodeDetector({formats:["qr_code"]});ye=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:"environment"}},audio:!1});const l=o("#cinScannerVideo");l.srcObject=ye,await l.play(),a.textContent=e("checkin.scanner_stop"),a.disabled=!1,a.onclick=()=>{ra(),a.textContent=e("checkin.scanner_start"),a.onclick=It},t.textContent=e("checkin.scanner_hint");const r=async()=>{if(!(!ye||!l.isConnected)){try{const d=await i.detect(l);if(d[0]?.rawValue){xa(d[0].rawValue);return}}catch(d){console.error(d)}Ta=requestAnimationFrame(r)}};r()}catch(s){console.error(s),ra(),a.disabled=!1,t.textContent=e("checkin.scanner_unsupported")}}function Xn(t){const a=de.find($=>Number($.id)===Number(t));if(!a)return;const s=a.order_id&&m.orderShowUrlTemplate?q(m.orderShowUrlTemplate,a.order_id):"",i=a.customer_id&&m.userEditUrlTemplate?q(m.userEditUrlTemplate,a.customer_id):"",l=a.status||"pending",r=!!a.checked_in_at,d=[{label:e("checkin.step_booked"),state:r||l!=="pending"?"done":"active",time:""},{label:e("checkin.step_checked_in"),state:r?"done":"",time:r&&a.checked_in_label&&a.checked_in_label!=="—"?a.checked_in_label:""},{label:e("checkin.step_clearance"),state:r&&l==="pending"?"active":["in_progress","completed"].includes(l)?"done":"",time:""},{label:e("checkin.step_treatment"),state:l==="in_progress"?"active":l==="completed"?"done":"",time:""},{label:e("checkin.step_completed"),state:l==="completed"?"active done":"",time:""}],u=`<div class="cin-preview__block cin-timeline"><div class="journey-section__title">${n(e("checkin.timeline"))}</div>
    <div class="timeline">${d.map($=>`<div class="timeline-step ${$.state}"><div class="timeline-dot">${pa($.state.includes("done"))}</div><span>${n($.label)}${$.time?` <em>· ${n($.time)}</em>`:""}</span></div>`).join("")}</div></div>`,p=`${a.phone||a.email?`<div class="cin-preview__contact">
    ${a.phone?`<a href="tel:${n(a.phone)}"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5.2 2.8 7.5 6 6 7.5c1.2 2.4 2.1 3.3 4.5 4.5l1.5-1.5 3.2 2.3c.5.4.7 1 .5 1.6-.4 1-1.4 1.7-2.5 1.6C7.8 15.6 4.4 12.2 4 6.8c-.1-1.1.6-2.1 1.6-2.5.6-.2 1.2 0 1.6.5Z"/></svg>${n(a.phone)}</a>`:""}
    ${a.email?`<a href="mailto:${n(a.email)}"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="2.5" y="4" width="15" height="12" rx="2"/><path d="m3.5 6 6.5 5 6.5-5"/></svg>${n(a.email)}</a>`:""}
  </div>`:""}`,_=a.checkin_pass_url?`<div class="cin-preview__block cin-preview__qr">
    <div class="journey-section__title">${n(e("checkin.qr_title"))}</div>
    <div class="cin-preview__qr-box"><canvas id="cinQrCanvas" role="img" aria-label="${n(e("checkin.qr_title"))}"></canvas></div>
    <div class="cin-preview__qr-code">${n(a.code||"")}</div>
    <p class="cin-preview__qr-hint">${n(e("checkin.qr_hint"))}</p>
    <div class="cin-preview__qr-actions"><button type="button" class="btn small soft" id="cinCopyCode">${n(e("checkin.copy_code"))}</button><a class="btn small" href="${n(a.checkin_pass_url)}" target="_blank" rel="noopener">${n(e("checkin.view_checkin_pass"))}</a></div>
  </div>`:"",h=`<div class="cin-preview__block"><div class="journey-section__title">${n(e("checkin.details"))}</div>
    <div class="journey-kv">
      <div><span>${n(e("checkin.col_treatment"))}</span><strong>${n(a.treatment||"—")}</strong></div>
      <div><span>${n(e("checkin.col_beautician"))}</span><strong>${n(a.beautician||"—")}</strong></div>
      <div><span>${n(e("checkin.col_branch"))}</span><strong>${n(a.branch_name||a.branch||"—")}</strong></div>
      <div><span>${n(e("checkin.col_payment"))}</span><strong>${n(a.payment_label||"—")}</strong></div>
      <div><span>${n(e("checkin.col_date"))}</span><strong>${n((a.date_label||"")+" "+(a.time||""))}</strong></div>
      <div><span>${n(e("checkin.col_checked_in"))}</span><strong>${n(a.checked_in_label||"—")}</strong></div>
      <div><span>${n(e("checkin.col_wait"))}</span><strong>${n(a.waiting_label||"—")}</strong></div>
      <div><span>${n(e("checkin.col_clearance"))}</span><strong>${n(a.clearance_label||"—")}</strong></div>
    </div></div>`,b=`<div class="pay-review cin-preview">
    <div class="pay-review__hero pay-review__hero--cin">
      <div class="pay-review__avatar cin-avatar" aria-hidden="true">${n(a.initial||"?")}</div>
      <div style="min-width:0;flex:1">
        <div class="pay-id">${n(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${n(a.name||"")}</strong>
        <div class="pay-method">${n(a.date_label||"")} ${n(a.time||"")} · ${n(a.branch_name||a.branch||"—")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${x(a.status_label)}${x(a.arrival_label)}</div>
      </div>
    </div>
    ${p}<div class="checkin-decision checkin-decision--${n(l==="pending"?r?"waiting":"booked":l)}"><strong>${n(a.arrival_label||a.status_label||"")}</strong><p>${n(Yn(a))}</p></div>${u}${h}${_}
  </div>`,k=[Rt(a),i&&m.canViewUser?`<a class="btn" href="${n(i)}" target="_blank" rel="noopener">${n(e("checkin.open_customer"))}</a>`:"",s&&m.canViewOrder?`<a class="btn" href="${n(s)}" target="_blank" rel="noopener">${n(e("checkin.open_order"))}</a>`:"",!(Array.isArray(a.available_actions)&&a.available_actions.includes("open_crm"))&&m.canViewTreatments&&m.treatmentReservationsUrl?`<a class="btn" href="${n(m.treatmentReservationsUrl)}" target="_blank" rel="noopener">${n(e("checkin.open_crm"))}</a>`:"",`<button type="button" class="btn" data-action-drawer-close>${n(e("checkin.close"))}</button>`].filter(Boolean).join("");Y(e("checkin.title"),a.code+" · "+a.name,b,k,e("nav.checkin")),Ut(o("#actionDrawer"));const f=o("#cinQrCanvas");if(f&&window.QRCentral)try{QRCentral.render(f,a.checkin_pass_url||"")}catch($){console.error($),f.closest(".cin-preview__qr-box")?.classList.add("hidden")}const w=o("#cinCopyCode");w&&(w.onclick=()=>Qn(a.code||"",e("checkin.copied")))}function Qn(t,a){const s=()=>g(a);if(!t){g(e("checkin.copy_missing"));return}navigator.clipboard&&window.isSecureContext?navigator.clipboard.writeText(t).then(s).catch(()=>dt(t,s)):dt(t,s)}function dt(t,a){const s=document.createElement("textarea");s.value=t,s.setAttribute("readonly",""),s.style.position="fixed",s.style.opacity="0",document.body.appendChild(s),s.select();try{document.execCommand("copy"),a()}catch{g(e("checkin.copy_missing"))}document.body.removeChild(s)}function Zn(){E.innerHTML=`<div class="clr-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${n(e("clearance.title"))}</h1>
        <div class="page-subtitle">${n(e("clearance.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="clrRefresh">${n(e("clearance.refresh"))}</button>
        <button type="button" class="btn" data-jump="payments">${n(e("clearance.open_payments"))}</button>
        ${m.canViewTreatments&&m.treatmentReservationsUrl?`<a class="btn primary" href="${n(m.treatmentReservationsUrl)}" target="_blank" rel="noopener">${n(e("clearance.open_crm"))}</a>`:""}
      </div>
    </div>
    <section class="clearance-flow" aria-labelledby="clearanceFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CRM</span><h2 id="clearanceFlowTitle">${n(e("clearance.workflow_title"))}</h2><p>${n(e("clearance.workflow_hint"))}</p></div>
      <ol class="clearance-flow__steps">
        ${[e("clearance.step_arrival"),e("clearance.step_payment"),e("clearance.step_treatment"),e("clearance.step_complete")].map((a,s)=>`<li><span aria-hidden="true">${s+1}</span><strong>${n(a)}</strong></li>`).join("")}
      </ol>
    </section>
    <div class="pay-metrics" id="clrMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${n(e("clearance.subtitle"))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="clrResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="clrTabs" role="group"></div>
        <label class="lead-search" for="clrSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="clrSearch" aria-label="${n(e("clearance.search_placeholder"))}" type="search" autocomplete="off" placeholder="${n(e("clearance.search_placeholder"))}" value="${n(c.clearanceSearch||"")}" />
        </label>
        <div class="lead-filter-grid ops-filter-grid ops-filter-grid--two">
          <label class="lead-field"><span class="lead-field__label">${n(e("clearance.filter_beautician"))}</span>
            <select class="lead-field__control" id="clrBeautician"></select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${n(e("clearance.filter_branch"))}</span>
            <select class="lead-field__control" id="clrBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="clrTableMount"></div>
    </section>
  </div>`;const t=o("#clrRefresh");t&&(t.onclick=()=>ee()),ne(),es(),ee()}function es(){const t=o("#clrSearch");t&&(t.oninput=()=>{clearTimeout(Xa),Xa=setTimeout(()=>{c.clearanceSearch=t.value.trim(),c.clearancePage=1,ee()},320)});const a=o("#clrBeautician");a&&(a.onchange=()=>{c.clearanceBeautician=a.value,c.clearancePage=1,ee()});const s=o("#clrBranch");s&&(s.onchange=()=>{c.clearanceBranch=s.value,c.clearancePage=1,ee()})}async function ee(){const t=++ze;Ue=!1;const a=m.clearanceUrl||"",s=o("#clrTableMount");if(!a){s&&(s.innerHTML=`<div class="pay-empty"><strong>${n(e("clearance.load_error"))}</strong></div>`);return}me=!0,pt(),ut();const i=new URLSearchParams;c.clearanceSearch&&i.set("q",c.clearanceSearch),c.clearanceState&&i.set("state",c.clearanceState);const l=c.clearanceBranch!=="all"?c.clearanceBranch:c.branch||"all";l&&l!=="all"&&i.set("branch",l),c.clearanceBeautician&&c.clearanceBeautician!=="all"&&i.set("beautician",c.clearanceBeautician),i.set("page",String(c.clearancePage||1)),i.set("per_page","25");try{const r=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!r.ok)throw new Error("clearance "+r.status);const d=await r.json();if(t!==ze)return;ue=Array.isArray(d.data)?d.data:[],qa=Object.assign({waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0},d.meta&&d.meta.summary||{}),Ca=Object.assign({states:[],beauticians:[],branches:[]},d.filters||{}),Ha={current_page:d.meta&&d.meta.current_page||1,last_page:d.meta&&d.meta.last_page||1,total:d.meta&&d.meta.total||0}}catch(r){if(t!==ze)return;Ue=!0,console.error(r),ue=[],g(e("clearance.load_error"))}finally{if(t!==ze)return;me=!1,pt(),as(),ts(),ut()}}function pt(){const t=o("#clrMetrics");if(!t)return;const a=qa;t.setAttribute("aria-busy",String(me));const s=i=>me||Ue?"—":v(i);t.innerHTML=`
    <div class="pay-metric clearance-metric clearance-metric--waiting"><span>${n(e("clearance.stat_waiting"))}</span><strong>${s(a.waiting)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--blocked"><span>${n(e("clearance.stat_blocked"))}</span><strong>${s(a.blocked)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--treatment"><span>${n(e("clearance.stat_treatment"))}</span><strong>${s(a.in_treatment)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--done"><span>${n(e("clearance.stat_done"))}</span><strong>${s(a.done_today)}</strong></div>`}function as(){const t=o("#clrTabs");if(!t)return;const a=qa,s=[["waiting",e("clearance.tab_waiting"),a.waiting],["blocked",e("clearance.tab_blocked"),a.blocked],["in_treatment",e("clearance.tab_treatment"),a.in_treatment],["all_queue",e("clearance.tab_queue"),a.queue],["done",e("clearance.tab_done"),a.done_today]];t.innerHTML=s.map(([i,l,r])=>{const d=c.clearanceState===i?"active":"";return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-clrtab="${i}">${n(l)} (${v(r||0)})</button>`}).join(""),T("[data-clrtab]",t).forEach(i=>i.onclick=()=>{c.clearanceState=i.dataset.clrtab,c.clearancePage=1,ee()})}function ts(){const t=o("#clrBeautician");if(t){const i=c.clearanceBeautician||"all";t.innerHTML=`<option value="all">${n(e("clearance.all_beauticians"))}</option>`+(Ca.beauticians||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${n(l.name)}</option>`).join("")}const a=o("#clrBranch");if(a){const i=c.clearanceBranch||"all";a.innerHTML=`<option value="all">${n(e("clearance.all_branches"))}</option>`+(Ca.branches||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${n(l.name)}</option>`).join("")}const s=o("#clrResultCount");s&&(s.textContent=e("clearance.result_count",{count:Ue?"—":v(Ha.total)}))}function ut(){const t=o("#clrTableMount");if(!t)return;if(t.setAttribute("aria-busy",String(me)),Ue){t.innerHTML=`<div class="pay-empty" role="alert"><strong>${n(e("clearance.load_error"))}</strong><button type="button" class="btn" id="clrRetry">${n(e("clearance.refresh"))}</button></div>`,o("#clrRetry").onclick=()=>ee();return}if(me){t.innerHTML=`<div class="pay-empty" role="status">${n(e("clearance.loading"))}</div>`;return}if(!ue.length){t.innerHTML=`<div class="pay-empty"><strong>${n(e("clearance.empty"))}</strong>${n(e("clearance.empty_hint"))}</div>`;return}const a=ue.map(s=>`<tr data-clearance-state="${n(s.clearance||"other")}">
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${n(s.initial||"?")}</div><div class="person-cell__text"><strong>${n(s.name||"")}</strong><small>${n(s.code||"")} · ${n(s.phone||"")}</small></div></div></td>
    <td><strong>${n(s.date_label||"")}</strong><small class="clearance-wait">${n(s.checked_in_at?e("clearance.arrival_waiting",{time:s.waiting_label||"—"}):s.time||"—")}</small></td>
    <td>${n(s.branch_name||s.branch||"—")}</td>
    <td>${n(s.treatment||"—")}</td>
    <td>${x(s.payment_label)}</td>
    <td>${x(s.clearance_label)}</td>
    <td><div class="clearance-row-actions">${Ft(s,!0)}<button type="button" class="btn small soft" data-clr-view="${s.id}">${n(e("clearance.view"))}</button></div></td>
  </tr>`).join("");t.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${n(e("clearance.col_customer"))}</th><th>${n(e("clearance.col_time"))}</th>
    <th>${n(e("clearance.col_branch"))}</th><th>${n(e("clearance.col_treatment"))}</th>
    <th>${n(e("clearance.col_payment"))}</th><th>${n(e("clearance.col_clearance"))}</th>
    <th>${n(e("clearance.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${ae("clr",Ha)}`,T("[data-clr-view]",t).forEach(s=>s.onclick=()=>ls(s.dataset.clrView)),Ot(t),te("clr",s=>(c.clearancePage=s,ee()))}function Ft(t,a=!1){const s=Array.isArray(t.available_actions)?t.available_actions:[],i=a?"btn small":"btn";if(s.includes("start_treatment")&&m.canEditTreatments)return`<button type="button" class="${i} primary" data-clr-status="in_progress" data-clr-id="${t.id}">${n(e("clearance.start_treatment"))}</button>`;if(s.includes("complete_treatment")&&m.canEditTreatments)return`<button type="button" class="${i} success" data-clr-status="completed" data-clr-id="${t.id}">${n(e("clearance.complete_treatment"))}</button>`;if(s.includes("resolve_payment")){if(t.order_id)return`<button type="button" class="${i} danger" data-clr-payment="${t.id}">${n(e("clearance.resolve_payment"))}</button>`;if(m.canViewTreatments&&m.treatmentReservationsUrl)return`<a class="${i} danger" href="${n(m.treatmentReservationsUrl)}" target="_blank" rel="noopener">${n(e("clearance.resolve_payment"))}</a>`}return""}function ns(t){return e(`clearance.action_help_${{waiting:"waiting",blocked:"blocked",in_treatment:"treatment",done:"done"}[t.clearance]||"done"}`)}function pa(t){return t?'<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 10 3 3 7-7"/></svg>':'<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10" cy="10" r="6"/></svg>'}function Ot(t=document){T("[data-clr-status]",t).forEach(a=>a.onclick=()=>ss(a.dataset.clrId,a.dataset.clrStatus)),T("[data-clr-payment]",t).forEach(a=>a.onclick=()=>{const s=ue.find(i=>Number(i.id)===Number(a.dataset.clrPayment));s&&(c.paymentSearch=String(s.phone||s.name||s.code||"").trim()),D(),V("payments")})}function ss(t,a){const s=ue.find(l=>Number(l.id)===Number(t));if(!s)return;const i=a==="in_progress";Y(e(i?"clearance.start_confirm_title":"clearance.complete_confirm_title"),`${s.code} · ${s.name}`,`<div class="clearance-confirm"><div class="clearance-confirm__icon">${pa(!0)}</div><p>${n(e(i?"clearance.start_confirm_body":"clearance.complete_confirm_body"))}</p></div>`,`<button type="button" class="btn" data-action-drawer-close>${n(e("clearance.cancel"))}</button><button type="button" class="btn ${i?"primary":"success"}" id="confirmClearanceStatus">${n(e("clearance.confirm_action"))}</button>`,e("clearance.workflow_title")),o("#confirmClearanceStatus").onclick=l=>is(s,a,l.currentTarget)}async function is(t,a,s){const i=q(m.clearanceStatusUrlTemplate,t.id);if(!i||!m.canEditTreatments){g(e("clearance.update_error"));return}s.disabled=!0,s.setAttribute("aria-busy","true");try{const l=await fetch(i,{method:"PATCH",headers:N(!0),credentials:"same-origin",body:JSON.stringify({status:a})}),r=await l.json().catch(()=>({}));if(!l.ok){g(r.message||e("clearance.update_error"));return}D(),g(r.message||e("clearance.status_updated")),await ee()}catch(l){console.error(l),g(e("clearance.update_error"))}finally{s.isConnected&&(s.disabled=!1,s.removeAttribute("aria-busy"))}}function ls(t){const a=ue.find(d=>Number(d.id)===Number(t));if(!a)return;const s=a.order_id&&m.orderShowUrlTemplate?q(m.orderShowUrlTemplate,a.order_id):"",i=a.customer_id&&m.userEditUrlTemplate?q(m.userEditUrlTemplate,a.customer_id):"",l=`<div class="pay-review">
    <div class="pay-review__hero"><div class="pay-review__avatar">${n(a.initial||"?")}</div>
      <div style="min-width:0;flex:1"><div class="pay-id">${n(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${n(a.name||"")}</strong>
        <div class="pay-method">${n(a.phone||"—")} · ${n(a.treatment||"")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${x(a.clearance_label)}${x(a.payment_label)}</div>
      </div></div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${n(e("clearance.col_time"))}</label><strong>${n((a.date_label||"")+" "+(a.time||""))}</strong></div>
      <div class="pay-review__card"><label>${n(e("clearance.col_branch"))}</label><strong>${n(a.branch_name||a.branch||"—")}</strong></div>
      <div class="pay-review__card"><label>${n(e("checkin.col_beautician"))}</label><strong>${n(a.beautician||"—")}</strong></div>
    </div>
    <div class="clearance-decision clearance-decision--${n(a.clearance||"other")}"><strong>${n(a.clearance_label||"")}</strong><p>${n(ns(a))}</p></div>
    <div class="journey-section"><div class="journey-section__title">${n(e("clearance.workflow_title"))}</div><ol class="clearance-checklist">
      ${[[e("clearance.step_arrival"),!!a.checked_in_at||["in_treatment","done"].includes(a.clearance)],[e("clearance.step_payment"),!!a.payment_ok||["in_treatment","done"].includes(a.clearance)],[e("clearance.step_treatment"),["in_treatment","done"].includes(a.clearance)],[e("clearance.step_complete"),a.clearance==="done"]].map(([d,u])=>`<li class="${u?"is-done":""}"><span>${pa(u)}</span><strong>${n(d)}</strong></li>`).join("")}
    </ol></div></div>`,r=[Ft(a),a.checkin_pass_url?`<a class="btn" href="${n(a.checkin_pass_url)}" target="_blank" rel="noopener">${n(e("clearance.view_checkin_pass"))}</a>`:"",i&&m.canViewUser?`<a class="btn" href="${n(i)}" target="_blank" rel="noopener">${n(e("clearance.open_customer"))}</a>`:"",s&&m.canViewOrder?`<a class="btn" href="${n(s)}" target="_blank" rel="noopener">${n(e("clearance.open_order"))}</a>`:"",m.canViewTreatments&&m.treatmentReservationsUrl?`<a class="btn" href="${n(m.treatmentReservationsUrl)}" target="_blank" rel="noopener">${n(e("clearance.open_crm"))}</a>`:"",`<button type="button" class="btn" data-action-drawer-close>${n(e("clearance.close"))}</button>`].filter(Boolean).join("");Y(e("clearance.title"),a.code+" · "+a.name,l,r,e("nav.clearance")),setTimeout(()=>Ot(o("#actionDrawerFoot")),0)}function rs(){E.innerHTML=`<div class="wal-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${n(e("wallet.title"))}</h1>
        <div class="page-subtitle">${n(e("wallet.subtitle"))} <span class="loyalty-scope-badge">${n(e("wallet.scope_badge"))}</span></div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="walRefresh">${n(e("wallet.refresh"))}</button>
        ${m.canViewLoyalty&&m.loyaltyMembersUrl?`<a class="btn primary" href="${n(m.loyaltyMembersUrl)}" target="_blank" rel="noopener">${n(e("wallet.open_loyalty"))}</a>`:""}
      </div>
    </div>
    <section class="clearance-flow loyalty-flow" aria-labelledby="loyaltyFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">LOYALTY</span><h2 id="loyaltyFlowTitle">${n(e("wallet.workflow_title"))}</h2><p>${n(e("wallet.workflow_hint"))}</p></div>
      <ol class="clearance-flow__steps">
        ${[e("wallet.step_enrolled"),e("wallet.step_earn"),e("wallet.step_tier"),e("wallet.step_redeem")].map((a,s)=>`<li><span aria-hidden="true">${s+1}</span><strong>${n(a)}</strong></li>`).join("")}
      </ol>
    </section>
    <div class="pay-metrics" id="walMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${n(e("wallet.subtitle"))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="walResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="walTabs" role="group"></div>
        <label class="lead-search" for="walSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="walSearch" aria-label="${n(e("wallet.search_placeholder"))}" type="search" autocomplete="off" placeholder="${n(e("wallet.search_placeholder"))}" value="${n(c.walletSearch||"")}" />
        </label>
        <div class="lead-filter-grid loyalty-filter-grid">
          <label class="lead-field"><span class="lead-field__label">${n(e("wallet.filter_tier"))}</span>
            <select class="lead-field__control" id="walTier"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="walTableMount"></div>
    </section>
  </div>`;const t=o("#walRefresh");t&&(t.onclick=()=>ve()),cs(),ve()}function cs(){const t=o("#walSearch");t&&(t.oninput=()=>{clearTimeout(Ja),Ja=setTimeout(()=>{c.walletSearch=t.value.trim(),c.walletPage=1,ve()},320)});const a=o("#walTier");a&&(a.onchange=()=>{c.walletTier=a.value,c.walletPage=1,ve()})}async function ve(){const t=++Ye;He=!1;const a=m.walletUrl||"",s=o("#walTableMount");if(!a){s&&(s.innerHTML=`<div class="pay-empty"><strong>${n(e("wallet.load_error"))}</strong></div>`);return}oe=!0,mt(),vt();const i=new URLSearchParams;c.walletSearch&&i.set("q",c.walletSearch),c.walletSegment&&c.walletSegment!=="all"&&i.set("segment",c.walletSegment),c.walletTier&&c.walletTier!=="all"&&i.set("tier",c.walletTier),i.set("page",String(c.walletPage||1)),i.set("per_page","25");try{const l=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("wallet "+l.status);const r=await l.json();if(t!==Ye)return;qe=Array.isArray(r.data)?r.data:[],Ea=Object.assign({members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0},r.meta&&r.meta.summary||{}),$t=Object.assign({segments:[],tiers:[]},r.filters||{}),Aa={current_page:r.meta&&r.meta.current_page||1,last_page:r.meta&&r.meta.last_page||1,total:r.meta&&r.meta.total||0}}catch(l){if(t!==Ye)return;He=!0,console.error(l),qe=[],g(e("wallet.load_error"))}finally{if(t!==Ye)return;oe=!1,mt(),os(),ds(),vt()}}function mt(){const t=o("#walMetrics");if(!t)return;const a=Ea;t.setAttribute("aria-busy",String(oe));const s=i=>oe||He?"—":v(i);t.innerHTML=`
    <div class="pay-metric loyalty-metric loyalty-metric--members"><span>${n(e("wallet.stat_members"))}</span><strong>${s(a.members)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--active"><span>${n(e("wallet.stat_balance"))}</span><strong>${s(a.with_balance)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--points"><span>${n(e("wallet.stat_points"))}</span><strong>${s(a.points_outstanding)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--rewards"><span>${n(e("wallet.stat_stamp"))}</span><strong>${s(a.stamp_ready)}</strong></div>`}function os(){const t=o("#walTabs");if(!t)return;const a=Ea,s=[["all",e("wallet.tab_all"),a.members],["active",e("wallet.tab_active"),a.with_balance],["zero",e("wallet.tab_zero"),a.zero_balance],["stamp_ready",e("wallet.tab_stamp"),a.stamp_ready]];t.innerHTML=s.map(([i,l,r])=>{const d=c.walletSegment===i?"active":"";return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-waltab="${i}">${n(l)} (${v(r||0)})</button>`}).join(""),T("[data-waltab]",t).forEach(i=>i.onclick=()=>{c.walletSegment=i.dataset.waltab,c.walletPage=1,ve()})}function ds(){const t=o("#walTier");if(t){const s=c.walletTier||"all";t.innerHTML=`<option value="all">${n(e("wallet.all_tiers"))}</option>`+($t.tiers||[]).map(i=>`<option value="${i.id}"${String(s)===String(i.id)?" selected":""}>${n(i.name)}</option>`).join("")}const a=o("#walResultCount");a&&(a.textContent=e("wallet.result_count",{count:He?"—":v(Aa.total)}))}function vt(){const t=o("#walTableMount");if(!t)return;if(t.setAttribute("aria-busy",String(oe)),He){t.innerHTML=`<div class="pay-empty" role="alert"><strong>${n(e("wallet.load_error"))}</strong><button type="button" class="btn" id="walRetry">${n(e("wallet.refresh"))}</button></div>`,o("#walRetry").onclick=()=>ve();return}if(oe){t.innerHTML=`<div class="pay-empty" role="status">${n(e("wallet.loading"))}</div>`;return}if(!qe.length){t.innerHTML=`<div class="pay-empty"><strong>${n(e("wallet.empty"))}</strong>${n(e("wallet.empty_hint"))}</div>`;return}const a=qe.map(s=>{const i=s.avatar_url?`<div class="mini-avatar mini-avatar--photo"><img src="${n(s.avatar_url)}" alt=""></div>`:`<div class="mini-avatar">${n(s.initial||"?")}</div>`,l=s.stamp_ready?x(e("wallet.chip_stamp",{count:s.stamp_ready})):x(e("wallet.stamp_summary",{active:v(s.stamp_active||0),ready:0})),r=m.canShowLoyaltyMember&&m.loyaltyMemberShowUrlTemplate?q(m.loyaltyMemberShowUrlTemplate,s.id):"";return`<tr data-membership-segment="${n(s.segment||"zero")}">
      <td><div class="person-cell person-cell--lead">${i}<div class="person-cell__text"><strong>${n(s.name||"")}</strong><small>${n(s.code||"")} · ${n(s.phone||s.email||"—")}</small></div></div></td>
      <td><div class="loyalty-tier-cell">${s.tier?x(s.tier):"—"}<small>${n(e("wallet.tier_since"))}: ${n(s.tier_since_label||"—")}</small></div></td>
      <td class="is-num"><strong>${v(s.balance)}</strong></td>
      <td class="is-num">${R(s.lifetime_spend)}</td>
      <td><div class="loyalty-reward-cell">${l}<small>${n(e("wallet.stamp_summary",{active:v(s.stamp_active||0),ready:v(s.stamp_ready||0)}))}</small></div></td>
      <td><div class="loyalty-activity-cell"><strong>${n(e("wallet.activity_summary",{count:v(s.activity_count||0)}))}</strong><small>${n(s.last_activity_label||"—")}</small></div></td>
      <td class="lead-table__actions"><div class="lead-menu loyalty-action-menu">
        <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${n(e("wallet.row_actions",{name:s.name||e("wallet.guest")}))}"><span class="lead-menu__dots" aria-hidden="true"></span></button>
        <div class="lead-menu__panel" role="menu" hidden>
          ${r?`<a class="lead-menu__item" role="menuitem" href="${n(r)}" target="_blank" rel="noopener">${n(e("wallet.open_member"))}</a>`:""}
          <button type="button" class="lead-menu__item" role="menuitem" data-wal-view="${s.id}">${n(e("wallet.view"))}</button>
        </div>
      </div></td>
    </tr>`}).join("");t.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${n(e("wallet.col_customer"))}</th><th>${n(e("wallet.col_tier"))}</th><th class="is-num">${n(e("wallet.col_balance"))}</th>
    <th class="is-num">${n(e("wallet.col_spend"))}</th><th>${n(e("wallet.col_stamps"))}</th>
    <th>${n(e("wallet.col_activity"))}</th><th>${n(e("wallet.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${ae("wal",Aa)}`,Ua(t),T('.loyalty-action-menu a[role="menuitem"]',t).forEach(s=>s.onclick=()=>J()),T("[data-wal-view]",t).forEach(s=>s.onclick=()=>{J(),ps(s.dataset.walView)}),te("wal",s=>(c.walletPage=s,ve()))}let je=null,Fa="";function ps(t){const a=qe.find(p=>Number(p.id)===Number(t));if(!a)return;D(),X(),je=document.activeElement,Fa=document.body.style.overflow,document.body.style.overflow="hidden";const s=m.loyaltyMemberShowUrlTemplate?q(m.loyaltyMemberShowUrlTemplate,a.id):"",i=a.customer_id&&m.userEditUrlTemplate?q(m.userEditUrlTemplate,a.customer_id):"",l=(a.recent||[]).length?`<ol class="wallet-transactions">${a.recent.map(p=>`<li class="wallet-transaction">
        <div class="wallet-transaction__description"><strong>${n(p.description||p.type||"")}</strong><time>${n(p.created_label||"")}</time></div>
        <div class="wallet-transaction__amount"><strong class="${Number(p.points)>0?"is-credit":""}">${Number(p.points)>0?"+":""}${v(p.points)} <span>${n(e("wallet.col_balance"))}</span></strong><small>${n(e("wallet.transaction_balance",{balance:v(p.balance_after)}))}</small></div>
      </li>`).join("")}</ol>`:`<div class="pay-empty">${n(e("wallet.no_recent"))}</div>`,r=a.avatar_url?`<img class="wallet-detail__avatar" src="${n(a.avatar_url)}" alt="" />`:`<div class="wallet-detail__avatar" aria-hidden="true">${n(a.initial||"?")}</div>`,d=o("#leadDrawer");d.classList.add("wallet-drawer"),d.setAttribute("role","dialog"),d.setAttribute("aria-modal","true"),d.setAttribute("aria-labelledby","drawerName"),o("#leadDrawer .eyebrow").textContent=e("nav.wallet"),o("#drawerName").textContent=e("wallet.title"),o("#drawerClose").setAttribute("aria-label",e("wallet.close")),o("#drawerBody").innerHTML=`<div class="wallet-detail">
    <section class="wallet-detail__customer">${r}<div class="wallet-detail__identity">
      <span class="pay-id">${n(a.code||"")}</span>
      <h3>${n(a.name||"")}</h3>
      <p>${n(a.phone||"—")}</p><p>${n(a.email||"—")}</p>
      <div class="wallet-detail__badges">${a.tier?x(a.tier):""}${x(a.segment_label)}</div>
      <p>${n(e("wallet.member_since"))}: ${n(a.member_since_label||"—")} · ${n(e("wallet.tier_since"))}: ${n(a.tier_since_label||"—")}</p>
    </div></section>
    <section class="wallet-detail__balance"><span>${n(e("wallet.col_balance"))}</span><strong>${v(a.balance)}</strong></section>
    <div class="wallet-detail__metrics">
      <section><span>${n(e("wallet.col_spend"))}</span><strong>${R(a.lifetime_spend)}</strong></section>
      <section><span>${n(e("wallet.col_stamps"))}</span><strong>${n(e("wallet.stamp_summary",{active:v(a.stamp_active),ready:v(a.stamp_ready)}))}</strong></section>
      <section><span>${n(e("wallet.col_activity"))}</span><strong>${n(e("wallet.activity_summary",{count:v(a.activity_count||0)}))}</strong></section>
      <section><span>${n(e("wallet.last_activity"))}</span><strong>${n(a.last_activity_label||"—")}</strong></section>
    </div>
    <section class="wallet-detail__history"><h3>${n(e("wallet.detail_recent"))}</h3>${l}</section>
  </div>`;const u=document.createElement("div");u.id="walletDrawerFoot",u.className="wallet-drawer__footer",u.innerHTML=[s&&m.canShowLoyaltyMember?`<a class="btn primary" href="${n(s)}" target="_blank" rel="noopener">${n(e("wallet.open_member"))}</a>`:"",i&&m.canViewUser?`<a class="btn" href="${n(i)}" target="_blank" rel="noopener">${n(e("wallet.open_profile"))}</a>`:"",`<button type="button" class="btn" data-wallet-close>${n(e("wallet.close"))}</button>`].filter(Boolean).join(""),d.appendChild(u),o("[data-wallet-close]",u).onclick=X,d.classList.add("show"),d.setAttribute("aria-hidden","false"),d.inert=!1,o(".app-shell").inert=!0,o("#drawerBackdrop").classList.add("show"),o("#drawerBody").scrollTop=0,o("#drawerClose").focus({preventScroll:!0})}document.addEventListener("keydown",t=>{const a=o("#actionDrawer.show")||o("#leadDrawer.show");if(!a)return;const s=a.id==="actionDrawer"?D:X;if(t.key==="Escape"){t.preventDefault(),s();return}if(t.key!=="Tab")return;const i=T('button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex="0"]',a).filter(d=>d.getClientRects().length),l=i[0],r=i[i.length-1];if(!l){t.preventDefault();return}t.shiftKey&&(document.activeElement===l||!a.contains(document.activeElement))?(t.preventDefault(),r.focus()):!t.shiftKey&&(document.activeElement===r||!a.contains(document.activeElement))&&(t.preventDefault(),l.focus())});const we=Object.fromEntries(["beauticians","branches","audit"].map(t=>[t,{q:"",sort:"revenue",page:1}]));let Ge=0,_t=null,le=null;function us(){Oa("beauticians")}function ms(){Oa("branches")}function vs(){Oa("audit")}function Oa(t){const a=we[t];E.innerHTML=`<div class="report-shell">
    <div class="page-head"><div><h1 class="page-title">${n(e("nav."+t))}</h1><p class="page-subtitle">${n(e("reporting."+(t==="audit"?"audit_subtitle":"subtitle")))}</p></div>
      <button type="button" class="btn" id="reportRefresh">${n(e("reporting.refresh"))}</button></div>
    <div class="pay-metrics" id="reportMetrics" aria-live="polite"></div>
    <section class="lead-panel card"><div class="lead-panel__head"><div><h2 class="card-title">${n(e("reporting."+(t==="audit"?"recorded_imports":"performance")))}</h2><p class="lead-panel__sub" id="reportPeriod"></p></div><span class="lead-panel__count" id="reportCount">—</span></div>
      <div class="lead-panel__filters report-filters"><label class="lead-field"><span class="lead-field__label">${n(e("reporting.search"))}</span><input class="lead-field__control" type="search" id="reportSearch" maxlength="150" value="${n(a.q)}" placeholder="${n(e("reporting."+(t==="audit"?"search_batch":"search_name")))}"></label>
      ${t!=="audit"?`<label class="lead-field"><span class="lead-field__label">${n(e("reporting.sort"))}</span><select id="reportSort" class="lead-field__control">${["revenue","leads","conversion","orders"].map(i=>`<option value="${i}"${a.sort===i?" selected":""}>${n(e("reporting."+i))}</option>`).join("")}</select></label>`:""}</div>
      <div id="reportTable" class="lead-panel__body pay-table" aria-live="polite"></div>
    </section>
    <p class="report-note">${n(e("reporting."+(t==="audit"?"audit_note":t==="beauticians"?"methodology_beauticians":"methodology_branches")))}</p>
  </div>`,o("#reportRefresh").onclick=()=>Ee(),o("#reportSearch").oninput=i=>{a.q=i.target.value,a.page=1,clearTimeout(_t),t==="audit"?_t=setTimeout(()=>{c.view===t&&Ee()},350):le&&ca(t)};const s=o("#reportSort");s&&(s.onchange=i=>{a.sort=i.target.value,le&&ca(t)}),Ee()}async function Ee(){const t=c.view;if(!we[t])return;const a=++Ge,s=we[t],i=o("#reportTable");if(!i)return;le=null,o("#reportMetrics").innerHTML="",i.setAttribute("aria-busy","true"),i.innerHTML=`<div class="pay-empty" role="status">${n(e("reporting.loading"))}</div>`;const l=new URLSearchParams({view:t,page:String(s.page)});c.period&&l.set("period",c.period),c.branch&&c.branch!=="all"&&l.set("branch",c.branch),t==="audit"&&s.q.trim()&&l.set("q",s.q.trim());try{if(!m.reportingUrl)throw new Error("Missing reporting endpoint");const r=await fetch(m.reportingUrl+"?"+l.toString(),{headers:N(!1),credentials:"same-origin"});if(!r.ok)throw new Error("Reporting "+r.status);const d=await r.json();if(a!==Ge||c.view!==t)return;le=d;const u=d.meta.summary,p=t==="audit"?["batches","imported","duplicates","invalid"]:["leads","converted","orders","revenue"];o("#reportMetrics").innerHTML=p.map(_=>`<div class="pay-metric"><span>${n(e("reporting."+_))}</span><strong>${_==="revenue"?R(u[_]):v(u[_])}</strong></div>`).join(""),o("#reportPeriod").textContent=d.meta.period,ca(t)}catch{if(a!==Ge||c.view!==t)return;o("#reportCount").textContent="—",i.innerHTML=`<div class="pay-empty" role="alert"><strong>${n(e("reporting.error"))}</strong><button type="button" class="btn" id="reportRetry">${n(e("reporting.refresh"))}</button></div>`,o("#reportRetry").onclick=()=>Ee()}finally{a===Ge&&c.view===t&&i.setAttribute("aria-busy","false")}}function ca(t){const a=o("#reportTable");if(!a||!le)return;const s=we[t];let i=[...le.data||[]];t!=="audit"&&(i=i.filter(p=>String(p.name).toLocaleLowerCase().includes(s.q.trim().toLocaleLowerCase())),i.sort((p,_)=>Number(_[s.sort])-Number(p[s.sort])||String(p.name).localeCompare(String(_.name))));const l=t==="audit"?le.meta.total:i.length,r=t==="audit"?le.meta:{current_page:Math.max(1,Math.min(s.page,Math.ceil(l/25)||1)),last_page:Math.ceil(l/25)||1};t!=="audit"&&(s.page=r.current_page,i=i.slice((s.page-1)*25,s.page*25)),o("#reportCount").textContent=e("reporting.results",{count:v(l)});const d=t==="audit"?["code","date","actor","branch","method","status","raw","imported","duplicates","invalid"]:["name","leads","converted","conversion","follow_up","lost","orders","revenue","average_order"],u=["code","date","actor","branch","method","status","name"];a.innerHTML=i.length?`<div class="table-wrap report-table-wrap"><table class="data-table report-table"><thead><tr>${d.map(p=>`<th${u.includes(p)?"":' class="is-num"'}>${n(e("reporting."+p))}</th>`).join("")}</tr></thead><tbody>${i.map(p=>`<tr>${d.map(_=>{let h=u.includes(_)?n(_==="status"?e("reporting.status_"+p[_]):p[_]):_==="conversion"?Number(p.leads)>0?Number(p[_]).toFixed(1)+"%":"—":["revenue","average_order"].includes(_)?R(p[_]):v(p[_]);return`<td${u.includes(_)?"":' class="is-num"'}>${["name","code"].includes(_)?`<strong>${h}</strong>`:h}</td>`}).join("")}</tr>`).join("")}</tbody></table></div>`:`<div class="pay-empty"><strong>${n(e("reporting.empty"))}</strong>${n(e("reporting.empty_hint"))}</div>`,a.insertAdjacentHTML("beforeend",ae("report",r)),te("report",p=>{if(s.page=p,t==="audit")return Ee();ca(t)})}function _s(t,a){E.innerHTML=`${_e(n(t),n(a))}<section class="card"><div class="empty">${n(a)}</div></section>`}function hs(){E.innerHTML=`${_e(e("followup.title"),e("followup.subtitle"),`<button type="button" class="btn" data-jump="leads">${n(e("followup.open_workspace"))}</button>`)}
  <div class="grid kpi-grid" id="followKpiMount"></div>
  <section class="lead-panel card" style="margin-top:14px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${n(e("followup.title"))}</h2>
        <p class="lead-panel__sub">${n(e("followup.subtitle"))}</p>
      </div>
      <div class="lead-panel__head-meta">
        <span class="lead-panel__count" id="followResultCount">—</span>
      </div>
    </div>
    <div class="lead-panel__filters">
      <div class="tabs" id="followBuckets" role="tablist"></div>
      <label class="lead-search" for="followSearch">
        <span class="lead-search__icon" aria-hidden="true">⌕</span>
        <input class="lead-search__input" id="followSearch" type="search" autocomplete="off" placeholder="${n(e("followup.search_placeholder"))}" value="${n(c.followSearch)}" />
      </label>
      <div class="lead-filter-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
        <label class="lead-field">
          <span class="lead-field__label">${n(e("workspace.filter_beautician"))}</span>
          <select class="lead-field__control" id="followBeautician"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${n(e("workspace.filter_branch"))}</span>
          <select class="lead-field__control" id="followBranch"></select>
        </label>
      </div>
    </div>
    <div class="lead-panel__body" id="followTableMount"></div>
  </section>`,ne(),Vt(),re()}async function re(){const t=m.followUpsUrl||"";if(!t){g(e("followup.load_error"));return}const a=new URLSearchParams;c.followSearch&&a.set("q",c.followSearch),c.followBucket&&c.followBucket!=="all"&&a.set("bucket",c.followBucket);const s=c.leadBranch!=="all"?c.leadBranch:c.branch||"all";s&&s!=="all"&&a.set("branch",s),c.leadBeautician&&c.leadBeautician!=="all"&&a.set("beautician",c.leadBeautician),a.set("page",String(c.followPage||1)),ea=!0,c.view==="followup"&&ht();try{const i=await fetch(t+"?"+a.toString(),{headers:N(!1),credentials:"same-origin"});if(!i.ok)throw new Error("followups "+i.status);const l=await i.json();wt=l.meta||{},Le=Array.isArray(l.data)?l.data:[],ka=l.meta&&l.meta.summary||ka,xe=l.filters||xe,l.filters?.statuses&&(B.statuses=l.filters.statuses)}catch(i){console.error(i),g(e("followup.load_error"))}finally{ea=!1,c.view==="followup"&&(bs(),Vt(),ht())}}function Vt(){const t=xe.buckets||[{value:"all",label:e("followup.bucket_all")},{value:"overdue",label:e("followup.bucket_overdue")},{value:"due_today",label:e("followup.bucket_due_today")},{value:"no_response",label:e("followup.bucket_no_response")},{value:"lost",label:e("followup.bucket_lost")}],a=o("#followBuckets");a&&(a.innerHTML=t.map(r=>`<button type="button" class="tab ${String(c.followBucket)===String(r.value)?"active":""}" role="tab" data-follow-bucket="${n(r.value)}">${n(r.label)}</button>`).join(""),T("[data-follow-bucket]",a).forEach(r=>{r.onclick=()=>{c.followBucket=r.dataset.followBucket||"all",c.followPage=1,re()}}));const s=o("#followBeautician");if(s){const r=[{id:"all",name:e("workspace.all_beauticians")},...xe.beauticians||B.beauticians||[]];s.innerHTML=r.map(d=>`<option value="${n(d.id)}" ${String(c.leadBeautician)===String(d.id)?"selected":""}>${n(d.name)}</option>`).join(""),s.onchange=d=>{c.leadBeautician=d.target.value,c.followPage=1,re()}}const i=o("#followBranch");if(i){const r=[{id:"all",name:e("workspace.all_branches")},...xe.branches||m.branches||[]];i.innerHTML=r.map(d=>`<option value="${n(d.id)}" ${String(c.leadBranch)===String(d.id)?"selected":""}>${n(d.name)}</option>`).join(""),i.onchange=d=>{c.leadBranch=d.target.value,c.followPage=1,re()}}const l=o("#followSearch");l&&(l.oninput=r=>{c.followSearch=r.target.value,clearTimeout(Ya),Ya=setTimeout(()=>{c.followPage=1,re()},350)})}function bs(){const t=o("#followKpiMount");if(!t)return;const a=ka||{};t.innerHTML=`
    ${j("↻",e("followup.kpi_queue"),v(a.queue),e("followup.kpi_queue_meta"),"—","blue")}
    ${j("⏱",e("followup.kpi_overdue"),v(a.overdue),e("followup.kpi_overdue_meta"),"—","rose")}
    ${j("✓",e("followup.kpi_due_today"),v(a.due_today),e("followup.kpi_due_meta"),"—","green")}
    ${j("⌀",e("followup.kpi_no_response"),v(a.no_response),e("followup.kpi_no_response_meta"),"—","purple")}
    ${j("✕",e("followup.kpi_lost"),v(a.lost),e("followup.kpi_lost_meta"),"—","rose")}
  `}function ht(){const t=o("#followTableMount");if(!t)return;const a=Array.isArray(Le)?Le.length:0,s=o("#followResultCount");if(s&&(s.textContent=a===1?e("followup.results_count_one"):e("followup.results_count",{count:v(a)})),ea){t.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${n(e("followup.loading"))}</strong></div>`;return}const i=Le;if(!i.length){t.innerHTML=`<div class="lead-empty">
      <div class="lead-empty__icon" aria-hidden="true">↻</div>
      <strong>${n(e("followup.empty"))}</strong>
      <p>${n(e("followup.empty_hint"))}</p>
      <button type="button" class="btn" data-jump="leads">${n(e("followup.open_workspace"))}</button>
    </div>`,ne();return}const l=!!m.canEditLead;t.innerHTML=`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${n(e("workspace.col_lead_id"))}</th>
    <th>${n(e("workspace.col_customer"))}</th>
    <th>${n(e("workspace.col_phone"))}</th>
    <th>${n(e("workspace.col_beautician"))}</th>
    <th>${n(e("workspace.col_branch"))}</th>
    <th>${n(e("workspace.col_status"))}</th>
    <th>${n(e("workspace.col_last_fu"))}</th>
    <th>${n(e("followup.col_waiting"))}</th>
    <th class="lead-table__actions"><span class="sr-only">${n(e("workspace.col_action"))}</span></th>
  </tr></thead><tbody>${i.map(r=>{const d=String(r.name||""),u=n((d[0]||"?").toUpperCase()),p=Number(r.days_since_followup||0),_=r.last_followed_up_at?e("followup.days",{count:p}):e("followup.never"),h=String(r.followup_bucket)==="overdue";return`<tr>
      <td><span class="lead-code">${n(r.code||r.id)}</span></td>
      <td><div class="person-cell person-cell--lead"><div class="mini-avatar" aria-hidden="true">${u}</div><div class="person-cell__text"><strong>${n(d||"—")}</strong><small>${n(r.customer||"")}</small></div></div></td>
      <td><span class="lead-mono">${n(r.phone||"—")}</span></td>
      <td>${n(r.beautician||"—")}</td>
      <td><span class="lead-branch">${n(r.branch||"—")}</span></td>
      <td>${x(r.status)}</td>
      <td><span class="lead-date">${n(r.last||"—")}</span></td>
      <td><span class="lead-wait${h?" is-overdue":""}">${n(_)}</span></td>
      <td class="lead-table__actions">
        <div class="lead-menu">
          <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${n(e("workspace.row_actions"))}">
            <span class="lead-menu__dots" aria-hidden="true"></span>
          </button>
          <div class="lead-menu__panel" role="menu" hidden>
            <button type="button" class="lead-menu__item" role="menuitem" data-follow-view="${n(r.id)}">${n(e("followup.view_lead"))}</button>
            ${l?`<button type="button" class="lead-menu__item" role="menuitem" data-follow-mark="${n(r.id)}">${n(e("followup.mark"))}</button>`:""}
          </div>
        </div>
      </td>
    </tr>`}).join("")}</tbody></table></div>`,t.insertAdjacentHTML("beforeend",ae("follow",wt)),te("follow",r=>(c.followPage=r,re())),Ua(t),T("[data-follow-view]",t).forEach(r=>r.onclick=()=>{J(),da(r.dataset.followView)}),T("[data-follow-mark]",t).forEach(r=>r.onclick=()=>{J(),Wt(r.dataset.followMark)})}async function Wt(t){const a=q(m.leadFollowUpUrlTemplate,t);if(!a){g(e("followup.mark_error"));return}try{const s=await fetch(a,{method:"POST",headers:N(!0),credentials:"same-origin",body:JSON.stringify({})}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("followup.mark_error"));return}g(i.message||e("followup.marked")),c.view==="followup"?await re():await A()}catch(s){console.error(s),g(e("followup.mark_error"))}}function Yt(){const t=C||{},a=t.kpis||{},s=a.vs_prev||{},i=t.targets||{},l=t.dual||{},r=Number(l.sales_pct||t.target_board&&t.target_board.sales_pct||0),d=Number(i.sales||0),u=Number(a.sales||0),p=u-d,_=n(t.period&&t.period.label||e("common.this_month")),h=Array.isArray(t.sales_insights)?t.sales_insights:[],b=Array.isArray(t.waterfall)?t.waterfall:[],k=Math.max(1,...b.map(f=>Number(f.value||0)));E.innerHTML=`${_e(e("sales.title"),e("sales.subtitle"),`<button type="button" class="btn soft" id="salesRefreshBtn">${n(e("sales.refresh"))}</button>
     <button type="button" class="btn" data-jump="payments">${n(e("sales.open_payments"))}</button>
     <button type="button" class="btn primary" data-jump="leads">${n(e("sales.open_leads"))}</button>`)}
  <section class="sales-insights card">
    <div class="sales-insights__head">
      <div>
        <div class="journey-section__title">${n(e("sales.insights"))}</div>
        <p class="card-subtitle" style="margin:0">${_}</p>
      </div>
    </div>
    <div class="sales-insights__grid">
      ${h.length?h.map(f=>`<article class="sales-insight sales-insight--${n(f.tone||"info")}"><strong>${n(f.title||"")}</strong><p>${n(f.body||"")}</p></article>`).join(""):`<article class="sales-insight sales-insight--info"><strong>${n(e("sales.title"))}</strong><p>${n(e("sales.subtitle"))}</p></article>`}
    </div>
  </section>

  <div class="grid kpi-grid" style="margin-top:12px">
    ${j("◫",e("sales.kpi_sales"),L(a.sales),Q(s.sales,"%"),se(e("sales.kpi_sales"),L(d)),"rose",Math.min(100,r),"","")}
    ${j("▣",e("sales.kpi_orders"),v(a.orders||0),Q(s.orders||0,"%"),"—","blue")}
    ${j("♙",e("sales.kpi_customers"),v(a.buyers),Q(s.buyers,"%"),se(e("sales.kpi_customers"),v(i.buyers||0)),"green")}
    ${j("▥",e("sales.kpi_avg"),L(a.avg_sale),Q(s.avg_sale,"%"),se(e("sales.kpi_avg"),L(i.avg_sale||0)),"purple")}
    ${j("%",e("sales.kpi_target"),z(r),p>=0?"↑ "+L(Math.abs(p)):"↓ "+L(Math.abs(p)),e("sales.of_target"),"green",Math.min(100,r))}
  </div>

  <div class="grid split-60" style="margin-top:12px">
    <section class="card sales-card">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${n(_)}</div>
          <div class="daily-panel__title">${n(e("sales.revenue_trend"))}</div>
          <div class="daily-panel__sub">${n(e("sales.revenue_sub"))}</div>
        </div>
        <span class="sales-card__pill">${L(u)}</span>
      </div>
      <div class="chart-wrap sales-card__chart"><canvas id="salesChart"></canvas></div>
    </section>
    <section class="card target-card ${r>=100?"target-card--over":""}">
      <div class="target-card__head">
        <div>
          <div class="target-card__eyebrow">${n(e("sales.target_card"))}</div>
          <div class="target-card__title">${n(e("overview.target_achievement"))}</div>
        </div>
        <span class="target-card__pill">${r>=100?n(e("overview.above_target")):n(e("overview.below_target"))}</span>
      </div>
      <div class="target-card__body">
        <div class="target-card__ring" style="--p:${Math.min(100,r)}">
          <svg viewBox="0 0 120 120" aria-hidden="true">
            <circle class="target-card__track" cx="60" cy="60" r="52"></circle>
            <circle class="target-card__prog" cx="60" cy="60" r="52"></circle>
          </svg>
          <div class="target-card__ring-value">
            <strong>${z(r)}</strong>
            <span>${n(e("sales.of_target"))}</span>
          </div>
        </div>
        <div class="target-card__side">
          <div class="target-card__stat"><span>${n(e("overview.target"))}</span><strong>${L(d)}</strong></div>
          <div class="target-card__stat"><span>${n(e("overview.actual"))}</span><strong>${L(u)}</strong></div>
          <div class="target-card__delta">
            <strong>${p>=0?"+":""}${L(p)}</strong>
            <span>${n(e("sales.gap"))}</span>
          </div>
        </div>
      </div>
    </section>
  </div>

  <section class="card" style="margin-top:12px">
    <div class="card-title-row">
      <div>
        <div class="card-title">${n(e("sales.pipeline"))}</div>
        <div class="card-subtitle">${n(e("sales.pipeline_sub"))}</div>
      </div>
      <span class="badge blue">${n(e("sales.conv_rate"))}: ${z(a.new_buyer_share_pct||0)}</span>
    </div>
    <div class="sales-funnel">
      ${b.length?b.map((f,w)=>{const $=Number(f.value||0),S=Math.max(8,Math.round($/k*100));return`<div class="sales-funnel__step">
              <div class="sales-funnel__meta"><span>${n(f.name||"")}</span><strong>${v($)}</strong></div>
              <div class="sales-funnel__bar"><span style="width:${S}%"></span></div>
            </div>`}).join(""):`<div class="empty"><strong>${n(e("sales.empty_pipeline"))}</strong></div>`}
    </div>
  </section>

  <section class="lead-panel card" style="margin-top:12px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${n(e("sales.attribution"))}</h2>
        <p class="lead-panel__sub">${n(e("sales.attribution_sub"))}</p>
      </div>
    </div>
    <div class="lead-panel__body">${fs()}</div>
  </section>

  <div class="grid three-col" style="margin-top:12px">
    ${(t.branches||[]).slice(0,6).map(f=>Tt(f.name,f.new_buyers||0,f.buyers||0,f.conv||0,f.sales||0,f.avg||0,f.buyers||0)).join("")||`<section class="card"><div class="empty"><strong>${n(e("sales.empty_branches"))}</strong></div></section>`}
  </div>`,ne(),o("#salesRefreshBtn")&&(o("#salesRefreshBtn").onclick=()=>na()),requestAnimationFrame(()=>oa())}function fs(){const t=C&&C.beauticians&&C.beauticians.length?C.beauticians:[];return t.length?`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${n(e("sales.col_rank"))}</th>
    <th>${n(e("sales.col_beautician"))}</th>
    <th class="is-num">${n(e("sales.col_sales"))}</th>
    <th class="is-num">${n(e("sales.col_customers"))}</th>
    <th class="is-num">${n(e("sales.col_orders"))}</th>
    <th class="is-num">${n(e("sales.col_avg"))}</th>
    <th class="is-num">${n(e("sales.col_leads"))}</th>
    <th class="is-num">${n(e("sales.col_conv"))}</th>
  </tr></thead><tbody>${t.map((a,s)=>{const i=String(a.name||"—"),l=n((i[0]||"?").toUpperCase());return`<tr>
      <td><span class="rank">${s+1}</span></td>
      <td><div class="person-cell person-cell--lead"><div class="mini-avatar" aria-hidden="true">${l}</div><div class="person-cell__text"><strong>${n(i)}</strong></div></div></td>
      <td class="is-num"><span class="lead-money">${L(a.sales||0)}</span></td>
      <td class="is-num">${v(a.buyers||0)}</td>
      <td class="is-num">${v(a.orders||a.order_count||0)}</td>
      <td class="is-num">${L(a.avg||0)}</td>
      <td class="is-num">${v(a.leads||0)}</td>
      <td class="is-num">${z(a.conv||0)}</td>
    </tr>`}).join("")}</tbody></table></div>`:`<div class="lead-empty"><strong>${n(e("sales.empty_beauticians"))}</strong></div>`}function gs(){const t=(m.userEditUrlTemplate||"").replace(/\/__ID__\/edit$/,"").replace(/\/__ID__$/,"")||"";E.innerHTML=`<div class="cus-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${n(e("customers.title"))}</h1>
        <div class="page-subtitle">${n(e("customers.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="cusRefresh">${n(e("customers.refresh"))}</button>
        ${m.canViewUser&&t?`<a class="btn primary" href="${n(t)}" target="_blank" rel="noopener">${n(e("customers.open_users"))}</a>`:""}
      </div>
    </div>
    <div class="pay-metrics" id="cusMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro">
          <p class="lead-panel__sub" id="cusPulse" style="margin:0">${n(e("customers.subtitle"))}</p>
        </div>
        <div class="lead-panel__head-meta">
          <span class="lead-panel__count" id="cusResultCount">—</span>
        </div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="cusTabs" role="tablist"></div>
        <label class="lead-search" for="cusSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="cusSearch" type="search" autocomplete="off" placeholder="${n(e("customers.search_placeholder"))}" value="${n(c.customerSearch||"")}" />
        </label>
        <div class="lead-filter-grid" style="grid-template-columns:minmax(0,1fr)">
          <label class="lead-field">
            <span class="lead-field__label">${n(e("customers.filter_branch"))}</span>
            <select class="lead-field__control" id="cusBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="cusTableMount"></div>
    </section>
  </div>`;const a=o("#cusRefresh");a&&(a.onclick=()=>ke()),ys(),ke()}function ys(){const t=o("#cusSearch");t&&(t.oninput=()=>{clearTimeout(za),za=setTimeout(()=>{c.customerSearch=t.value.trim(),c.customerPage=1,ke()},320)});const a=o("#cusBranch");a&&(a.onchange=()=>{c.customerBranch=a.value,c.customerPage=1,ke()})}async function ke(){const t=m.customersUrl||"",a=o("#cusTableMount");if(!t){a&&(a.innerHTML=`<div class="pay-empty"><strong>${n(e("customers.load_error"))}</strong></div>`);return}ta=!0,bt(),ft();const s=new URLSearchParams;c.customerSearch&&s.set("q",c.customerSearch),c.customerSegment&&c.customerSegment!=="all"&&s.set("segment",c.customerSegment);const i=c.customerBranch!=="all"?c.customerBranch:c.branch||"all";i&&i!=="all"&&s.set("branch",i),c.period&&s.set("period",c.period),s.set("page",String(c.customerPage||1)),s.set("per_page","25");try{const l=await fetch(`${t}?${s.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("customers "+l.status);const r=await l.json();ge=Array.isArray(r.data)?r.data:[],ja=Object.assign({total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0},r.meta&&r.meta.summary||{}),Pe=Object.assign({segments:[],branches:[]},r.filters||{}),De={current_page:r.meta&&r.meta.current_page||1,last_page:r.meta&&r.meta.last_page||1,total:r.meta&&r.meta.total||0}}catch(l){console.error(l),ge=[],g(e("customers.load_error"))}finally{ta=!1,bt(),$s(),ws(),ft()}}function bt(){const t=ja,a=o("#cusPulse");a&&(a.textContent=De.total>0?e("customers.pulse_busy",{count:v(De.total)}):e("customers.pulse_clear"));const s=o("#cusMetrics");s&&(s.innerHTML=`
      <div class="pay-metric"><span>${n(e("customers.stat_total"))}</span><strong>${v(t.total)}</strong></div>
      <div class="pay-metric"><span>${n(e("customers.stat_buyers"))}</span><strong>${v(t.buyers)}</strong></div>
      <div class="pay-metric"><span>${n(e("customers.stat_new"))}</span><strong>${v(t.new_buyers)}</strong></div>
      <div class="pay-metric"><span>${n(e("customers.stat_sales"))}</span><strong>${R(t.period_sales)}</strong></div>
    `)}function $s(){const t=o("#cusTabs");if(!t)return;const a=ja,s={all:a.total||0,buyers:a.buyers||0,new:a.new_buyers||0,returning:a.returning||0,leads:a.with_leads||0},i=Pe.segments&&Pe.segments.length?Pe.segments:[{value:"all",label:e("customers.tab_all")},{value:"buyers",label:e("customers.tab_buyers")},{value:"new",label:e("customers.tab_new")},{value:"returning",label:e("customers.tab_returning")},{value:"leads",label:e("customers.tab_leads")}];t.innerHTML=i.map(l=>{const r=l.value,d=c.customerSegment===r?"active":"",u=s[r],p=u!==void 0?`<span class="pay-tab-count">${v(u)}</span>`:"";return`<button type="button" class="tab ${d}" role="tab" data-ctab="${n(r)}">${n(l.label)}${p}</button>`}).join(""),T("[data-ctab]",t).forEach(l=>{l.onclick=()=>{c.customerSegment=l.dataset.ctab,c.customerPage=1,ke()}})}function ws(){const t=o("#cusBranch");if(t){const a=c.customerBranch;t.innerHTML=`<option value="all">${n(e("customers.all_branches"))}</option>`+(Pe.branches||[]).map(s=>`<option value="${s.id}" ${String(a)===String(s.id)?"selected":""}>${n(s.code?s.code+" · "+s.name:s.name)}</option>`).join("")}}function ft(){const t=o("#cusTableMount"),a=o("#cusResultCount");if(a&&(a.textContent=e("customers.result_count",{count:v(De.total||ge.length)})),!t)return;if(ta){t.innerHTML=`<div class="pay-empty"><strong>${n(e("customers.loading"))}</strong></div>`;return}if(!ge.length){t.innerHTML=`<div class="pay-empty"><strong>${n(e("customers.empty"))}</strong><span>${n(e("customers.empty_hint"))}</span></div>`;return}const s=ge.map(l=>{const r=[`<span class="pay-chip ${l.segment==="new"||l.segment==="buyer"?"pay-chip--ok":"pay-chip--muted"}">${n(l.segment_label||"")}</span>`,l.has_lead?`<span class="pay-chip pay-chip--warn">${n(e("customers.chip_lead"))}</span>`:"",l.loyalty_tier?`<span class="pay-chip pay-chip--muted">${n(l.loyalty_tier)}</span>`:""].filter(Boolean).join(""),d=l.avatar_url?`<div class="mini-avatar mini-avatar--photo"><img src="${n(l.avatar_url)}" alt=""></div>`:`<div class="mini-avatar">${n(l.initial||"?")}</div>`;return`<tr>
      <td><span class="pay-id">${n(l.code||"CUS-"+l.id)}</span></td>
      <td><div class="person-cell">${d}<div><strong>${n(l.name||"")}</strong><small>${n(l.phone||l.email||"")}</small></div></div></td>
      <td>${n(l.branch||"—")}</td>
      <td><strong>${v(l.paid_orders_count)}</strong><div class="pay-method">${v(l.orders_count)} total</div></td>
      <td><div class="pay-amount">${R(l.paid_sales)}</div><div class="pay-method">${R(l.period_sales)} ${n(e("customers.detail_period").toLowerCase())}</div></td>
      <td>${n(l.last_order_label||"—")}</td>
      <td><div class="pay-chips">${r}</div></td>
      <td><button type="button" class="pay-review-btn" data-customer="${l.id}">${n(e("customers.view"))}</button></td>
    </tr>`}).join(""),i=ae("cus",De);t.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${n(e("customers.col_id"))}</th>
    <th>${n(e("customers.col_customer"))}</th>
    <th>${n(e("customers.col_branch"))}</th>
    <th>${n(e("customers.col_orders"))}</th>
    <th>${n(e("customers.col_sales"))}</th>
    <th>${n(e("customers.col_last"))}</th>
    <th>${n(e("customers.col_segment"))}</th>
    <th>${n(e("customers.col_action"))}</th>
  </tr></thead><tbody>${s}</tbody></table></div>${i}`,T("[data-customer]",t).forEach(l=>l.onclick=()=>ks(Number(l.dataset.customer))),te("cus",l=>(c.customerPage=l,ke()))}function ks(t){const a=ge.find(u=>Number(u.id)===Number(t));if(!a)return;const s=q(m.userEditUrlTemplate,a.id),i=!!m.canViewUser,r=`<div class="pay-review">
    <div class="pay-review__hero">
      ${a.avatar_url?`<div class="pay-review__avatar" style="padding:0;overflow:hidden"><img src="${n(a.avatar_url)}" alt="" style="width:100%;height:100%;object-fit:cover"></div>`:`<div class="pay-review__avatar">${n(a.initial||"?")}</div>`}
      <div style="min-width:0;flex:1">
        <div class="pay-id">${n(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${n(a.name||"")}</strong>
        <div class="pay-method">${n(a.phone||"—")} · ${n(a.email||"—")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${x(a.segment_label)}${a.loyalty_tier?x(a.loyalty_tier):""}</div>
      </div>
      <div style="text-align:right"><div class="pay-amount">${R(a.paid_sales)}</div><div class="pay-method">${n(e("customers.detail_sales"))}</div></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${n(e("customers.detail_orders"))}</label><strong>${v(a.paid_orders_count)}</strong></div>
      <div class="pay-review__card"><label>${n(e("customers.detail_period"))}</label><strong>${R(a.period_sales)}</strong></div>
      <div class="pay-review__card"><label>${n(e("customers.detail_leads"))}</label><strong>${v(a.leads_count)}</strong></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${n(e("customers.col_branch"))}</label><strong>${n(a.branch_name||a.branch||"—")}</strong></div>
      <div class="pay-review__card"><label>${n(e("customers.col_last"))}</label><strong>${n(a.last_order_label||"—")}</strong></div>
      <div class="pay-review__card"><label>${n(e("customers.col_orders"))}</label><strong>${v(a.orders_count)}</strong></div>
    </div>
  </div>`,d=[i?`<a class="btn primary" href="${n(s)}" target="_blank" rel="noopener">${n(e("customers.open_profile"))}</a>`:"",`<button type="button" class="btn" data-jump="payments" data-customer-id="${n(String(a.id))}" data-customer-name="${n(a.name||"")}" data-customer-phone="${n(a.phone||"")}" data-customer-email="${n(a.email||"")}">${n(e("customers.open_payments"))}</button>`,`<button type="button" class="btn" data-action-drawer-close>${n(e("customers.close"))}</button>`].filter(Boolean).join("");Y(e("customers.title"),a.code+" · "+a.name,r,d,"Customer"),setTimeout(()=>{T("#actionDrawerFoot [data-jump]").forEach(u=>u.onclick=()=>{if(D(),u.dataset.jump==="payments"&&u.dataset.customerId){Nn({id:u.dataset.customerId,name:u.dataset.customerName,phone:u.dataset.customerPhone,email:u.dataset.customerEmail});return}V(u.dataset.jump)})},0)}function Kt(){const t=o("#donutChart");if(!t)return;Ve(t);const a=t.getContext("2d"),s=t.clientWidth,i=t.clientHeight,l=C&&C.status_mix||[],r=["#2563eb","#0ea5e9","#059669","#d97706","#e11d48","#6366f1"],d=l.map((f,w)=>({label:f.name,value:Number(f.value||0),color:r[w%r.length]})),u=d.reduce((f,w)=>f+w.value,0);if(!d.length||u<=0){a.clearRect(0,0,s,i),a.fillStyle="#94a3b8",a.font="12px Poppins,sans-serif",a.textAlign="center",a.fillText(e("overview.no_branch_data"),s/2,i/2);const f=o("#leadLegend");f&&(f.innerHTML="");return}let p=-Math.PI/2;const _=s/2,h=i/2,b=Math.min(s,i)/2-8;d.forEach(f=>{const w=p+f.value/u*Math.PI*2;a.beginPath(),a.moveTo(_,h),a.arc(_,h,b,p,w),a.closePath(),a.fillStyle=f.color,a.fill(),p=w}),a.beginPath(),a.arc(_,h,b*.58,0,Math.PI*2),a.fillStyle="#fff",a.fill();const k=o("#leadLegend");k&&(k.innerHTML=d.map(f=>`<div class="legend-row"><span class="dot" style="background:${f.color}"></span><span>${n(f.label)}</span><strong>${v(f.value)}</strong><span class="pct">${(f.value/u*100).toFixed(1)}%</span></div>`).join(""))}function oa(){const t=o("#salesChart");if(!t)return;Ve(t);const a=t.getContext("2d"),s=t.clientWidth,i=t.clientHeight,l=C&&C.equity||{},r=Array.isArray(l.labels)?l.labels:[];let d;l.actual&&l.actual.length?(d=l.actual.map((y,M)=>M===0?Number(y):Math.max(0,Number(y)-Number(l.actual[M-1]))),d=d.map(y=>Math.round(y/1e3))):d=[];const u=Math.round((C&&C.targets&&C.targets.sales||0)/1e3),p={l:36,r:16,t:28,b:36},_=Math.max(u,...d,1)*1.15,h=y=>p.l+y/Math.max(d.length-1,1)*(s-p.l-p.r),b=y=>i-p.b-y/_*(i-p.t-p.b);a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let y=0;y<4;y++){const M=p.t+(i-p.t-p.b)/3*y;a.beginPath(),a.moveTo(p.l,M),a.lineTo(s-p.r,M),a.stroke()}const k=b(u);a.setLineDash([5,5]),a.strokeStyle="#d97706",a.beginPath(),a.moveTo(p.l,k),a.lineTo(s-p.r,k),a.stroke(),a.setLineDash([]),a.fillStyle="#64748b",a.font="600 10px Poppins",a.textAlign="left",a.fillText("Target",p.l+4,k-6);const f="#1d4ed8",w="#0ea5e9",$=a.createLinearGradient(0,p.t,0,i-p.b);$.addColorStop(0,"rgba(37,99,235,.22)"),$.addColorStop(1,"rgba(14,165,233,.02)"),a.beginPath(),a.moveTo(h(0),i-p.b),d.forEach((y,M)=>a.lineTo(h(M),b(y))),a.lineTo(h(d.length-1),i-p.b),a.closePath(),a.fillStyle=$,a.fill(),a.beginPath(),d.forEach((y,M)=>M?a.lineTo(h(M),b(y)):a.moveTo(h(M),b(y))),a.strokeStyle=f,a.lineWidth=2.75,a.lineJoin="round",a.lineCap="round",a.stroke();const S=Math.max(1,Math.ceil(d.length/8));d.forEach((y,M)=>{if(M%S&&M!==d.length-1)return;const P=h(M),H=b(y);a.beginPath(),a.arc(P,H,4,0,Math.PI*2),a.fillStyle="#fff",a.fill(),a.lineWidth=2,a.strokeStyle=w,a.stroke(),a.fillStyle="#0f172a",a.font="700 10px Poppins",a.textAlign="center",a.fillText("RM"+y+"k",P,H-10),a.fillStyle="#64748b",a.font="600 10px Poppins",a.fillText(r[M]||"",P,i-12)})}function Ss(){if(!o("#branchChart"))return;const t=o("#branchChart"),a=t.getContext("2d"),s=t.clientWidth,i=t.clientHeight,l={l:45,r:20,t:20,b:35};Ve(t);const r=Array.isArray(C&&C.branches)?C.branches:[],d=r.map(b=>Number(b.new_buyer_share_pct)||Number(b.conv)||0),u=r.map(b=>String(b.name||"").slice(0,2).toUpperCase()||"—");if(!d.length)return;const p=Math.max(...d,50),_=Math.max(8,(s-l.l-l.r-70*d.length)/(d.length+1)),h=Math.min(70,(s-l.l-l.r-_*(d.length+1))/d.length);d.forEach((b,k)=>{const f=l.l+_+(h+_)*k,w=i-l.b-b/p*(i-l.t-l.b);a.fillStyle=[ie("--brand","#38bdf8"),ie("--rose","#0ea5e9"),ie("--navy","#2563eb")][k%3],a.fillRect(f,w,h,i-l.b-w),a.fillStyle=ie("--navy","#1d4ed8"),a.textAlign="center",a.font="700 14px Poppins",a.fillText(b+"%",f+h/2,w-8),a.font="13px Poppins",a.fillText(u[k],f+h/2,i-12)})}function Ve(t){const a=Math.max(1,window.devicePixelRatio||1),s=t.getBoundingClientRect();t.width=s.width*a,t.height=s.height*a,t.getContext("2d").setTransform(a,0,0,a,0,0)}const Va=["overview","leads","import","imports","followup","sales","payments","customers","wallet","checkin","clearance","beauticians","branches","audit"],Ae=String(m.basePath||"").replace(/\/$/,"")||"/admin/leads/central";function gt(t){const a=Va.includes(t)?t:"overview";return a==="overview"?Ae:Ae+"/"+a}function zt(t){const a=String(location.pathname).replace(/\/$/,"");if(a===Ae)return"overview";if(a.startsWith(Ae+"/")){const s=a.slice(Ae.length+1).split("/")[0];return Va.includes(s)?s:"overview"}return"overview"}function yt(){const t=new URLSearchParams(location.search);return t.set("branch",c.branch||"all"),c.period?t.set("period",c.period):t.delete("period"),"?"+t.toString()}function Ms(t,{replace:a=!1,silent:s=!1}={}){const i=gt(t)+yt()+location.hash;!s&&(a||i!==location.pathname+location.search+location.hash)&&history[a?"replaceState":"pushState"]({view:t},"",i),T(".nav-item[data-view]").forEach(l=>l.href=gt(l.dataset.view)+yt())}function Ts(){const t=new URLSearchParams(location.search),a=t.get("branch")||"all",s=t.get("period")||o("#periodScope").options[0]?.value||"",i=o("#branchScope"),l=o("#periodScope");i.value=a,c.branch=i.value||"all",i.value=c.branch,/^\d{4}-(0[1-9]|1[0-2])$/.test(s)&&![...l.options].some(r=>r.value===s)&&l.add(new Option(s,s)),l.value=s,c.period=l.value||l.options[0]?.value||"",l.value=c.period}function Jt(){c.branch=o("#branchScope").value||"all",c.period=o("#periodScope").value||"",we[c.view]&&(we[c.view].page=1),V(c.view)}function V(t,a={}){We();const s=Va.includes(t)?t:"overview";X(),D(),window.IMMA_TRADE&&s!=="overview"&&IMMA_TRADE.dispose(),c.view=s;const i=s==="wallet";for(const r of[o("#branchScope"),o("#periodScope")])r.disabled=i,r.title=i?e("wallet.scope_hint"):"";Ms(s,a),T(".nav-item[data-view]").forEach(r=>r.classList.toggle("active",r.dataset.view===s)),o("#crumbCurrent").textContent={overview:e("common.overview_crumb"),leads:e("nav.leads"),import:e("nav.import"),imports:e("nav.imports"),payments:e("nav.payments"),wallet:e("nav.wallet"),checkin:e("nav.checkin"),clearance:e("nav.clearance"),beauticians:e("nav.beauticians"),branches:e("nav.branches"),audit:e("nav.audit"),followup:e("nav.followup"),sales:e("nav.sales"),customers:e("nav.customers")}[s]||s;const l=$a!==JSON.stringify([c.branch,c.period]);["overview","sales"].includes(s)&&l?(E.innerHTML=`<section class="card"><div class="pay-empty" role="status">${n(e("overview.loading"))}</div></section>`,na()):(({overview:Mt,leads:fn,import:Cn,imports:En,followup:hs,sales:Yt,payments:qt,customers:gs,wallet:rs,checkin:Fn,clearance:Zn,beauticians:us,branches:ms,audit:vs}[s]||(()=>_s(e("nav."+s),e("operations.not_ready"))))(),l&&na()),a.silent||window.scrollTo({top:0,behavior:"smooth"}),innerWidth<1e3&&o("#sidebar").classList.remove("open")}function ne(){T("[data-jump]").forEach(t=>t.onclick=()=>{t.dataset.importTab&&(c.importTab=t.dataset.importTab),V(t.dataset.jump)})}let Qe=null,Gt="";function Y(t,a,s,i,l=""){X(),D(),Qe=document.activeElement,Gt=document.body.style.overflow,o("#actionDrawerEyebrow").textContent=l,o("#actionDrawerTitle").textContent=t,o("#actionDrawerBody").innerHTML=`<p class="action-drawer__subtitle">${n(a)}</p>${s}`,o("#actionDrawerFoot").innerHTML=i,o("#actionDrawerClose").setAttribute("aria-label",e("wallet.close")),o("#actionDrawer").classList.add("show"),o("#actionDrawer").setAttribute("aria-hidden","false"),o("#actionDrawer").inert=!1,o("#actionDrawerBackdrop").classList.add("show"),o(".app-shell").inert=!0,document.body.style.overflow="hidden",o("#actionDrawerBody").scrollTop=0,o("#actionDrawerClose").focus({preventScroll:!0});const r=o("#saveLead");r&&(r.onclick=()=>Sn()),T("[data-action-drawer-close]",o("#actionDrawer")).forEach(d=>d.onclick=D)}function D(){const t=o("#actionDrawer");t.classList.contains("show")&&(ra(),t.classList.remove("show"),t.setAttribute("aria-hidden","true"),t.inert=!0,o("#actionDrawerBackdrop").classList.remove("show"),o(".app-shell").inert=!1,document.body.style.overflow=Gt,Qe?.isConnected&&Qe.focus({preventScroll:!0}),Qe=null)}function X(){const t=o("#leadDrawer");t.classList.contains("show")&&(t.classList.remove("show"),t.inert=!0,o(".app-shell").inert=!1,o("#drawerBackdrop").classList.remove("show"),t.setAttribute("aria-hidden","true"),t.classList.contains("wallet-drawer")&&(t.classList.remove("wallet-drawer"),o("#walletDrawerFoot")?.remove()),document.body.style.overflow=Fa,je?.isConnected&&je.focus({preventScroll:!0}),je=null)}let Pa={};function Xt(){return String(m.notificationStorageKey||"imma-central.notifications.v1.guest")}function Cs(){return`${c.branch||"all"}|${c.period||""}`}function Qt(t){return`${Cs()}|${t.id}|${t.count}`}function Zt(){try{const t=JSON.parse(localStorage.getItem(Xt())||"{}");return t&&typeof t=="object"&&!Array.isArray(t)?t:{}}catch{return{...Pa}}}function Ls(t){const a=Date.now()-7776e6,s=Object.entries(t).filter(([,i])=>Number(i)>=a).sort((i,l)=>Number(i[1])-Number(l[1])).slice(-100);Pa=Object.fromEntries(s);try{localStorage.setItem(Xt(),JSON.stringify(Pa))}catch{}}function ya(t){const a={payments:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/></svg>',clearance:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>',leads:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M3 20c.7-4 2.7-6 6-6 2.1 0 3.7.8 4.8 2.3M18 8v6M15 11h6"/></svg>'};return a[t]||a.leads}function en(){const t=C||{},a=[],s=Number(t.ops?.payments?.queue||0),i=Number(t.ops?.clearance?.queue||0),l=Number(t.kpis?.new_buyers||t.kpis?.unique_leads||0);return s>0&&a.push({id:"payments",count:s,icon:ya("payments"),title:e("notifications.payment_title"),detail:e("notifications.payment_detail",{count:v(s)}),view:"payments"}),i>0&&a.push({id:"clearance",count:i,icon:ya("clearance"),title:e("notifications.clearance_title"),detail:e("notifications.clearance_detail",{count:v(i)}),view:"clearance"}),l>0&&a.push({id:"leads",count:l,icon:ya("leads"),title:e("notifications.leads_title"),detail:e("notifications.leads_detail",{count:v(l),period:t.period?.label||e("notifications.selected_period")}),view:"leads"}),a}function an(){const t=Zt();return en().filter(a=>!t[Qt(a)])}function xs(t){const a=Zt(),s=Date.now();t.forEach(i=>{a[Qt(i)]=s}),Ls(a)}function ua(){const t=o("#notificationButton"),a=o("#notificationMenu"),s=o("#notificationCount");if(!t||!a||!s)return;const i=en(),l=an();s.textContent=String(l.length),s.hidden=l.length===0,t.setAttribute("aria-label",l.length?e("notifications.button_count",{count:v(l.length)}):e("notifications.button"));const r=l.length?e("notifications.active_count",{count:v(l.length)}):i.length?e("notifications.cleared"):e("notifications.all_clear"),d=i.length?e("notifications.cleared_hint"):e("notifications.empty");a.innerHTML=`<div class="notification-menu__head"><div class="notification-menu__title"><span id="notificationMenuTitle">${n(e("notifications.title"))}</span><small>${n(r)}</small></div>${l.length?`<button type="button" class="notification-menu__clear" data-clear-notifications>${n(e("notifications.clear"))}</button>`:""}</div>`+(l.length?l.map(u=>`<button type="button" class="notification-menu__item" data-notification-view="${n(u.view)}"><span class="notification-menu__icon">${u.icon}</span><span><strong>${n(u.title)}</strong><small>${n(u.detail)}</small></span></button>`).join("")+`<p class="notification-menu__note">${n(e("notifications.clear_note"))}</p>`:`<div class="notification-menu__empty">${n(d)}</div>`)}function Ps(){const t=an();if(!t.length)return;We();const a=`<div class="notification-clear-summary"><strong>${n(e("notifications.clear_summary",{count:v(t.length)}))}</strong><p>${n(e("notifications.clear_note"))}</p></div>`,s=`<button type="button" class="btn" data-action-drawer-close>${n(e("notifications.cancel"))}</button><button type="button" class="btn danger" id="notificationClearConfirm">${n(e("notifications.confirm_clear"))}</button>`;Y(e("notifications.clear_title"),e("notifications.clear_subtitle"),a,s,e("notifications.title")),o("#notificationClearConfirm").onclick=()=>{xs(t),D(),ua(),o("#notificationButton")?.focus({preventScroll:!0}),g(e("notifications.clear_success"))}}function Bs(t=null){const a=o("#notificationButton"),s=o("#notificationMenu");if(!a||!s)return;const i=t===null?s.hidden:!t;!s.hidden!==i&&(ua(),s.hidden=!i,a.setAttribute("aria-expanded",String(i)))}function We(){const t=o("#notificationButton"),a=o("#notificationMenu");!t||!a||a.hidden||(a.hidden=!0,t.setAttribute("aria-expanded","false"))}function g(t){const a=o("#toast");a.textContent=t,a.classList.add("show"),clearTimeout(g._t),g._t=setTimeout(()=>a.classList.remove("show"),2300)}window.showToast=g;window.navigate=V;window.$=o;T(".nav-item[data-view]").forEach(t=>t.addEventListener("click",a=>{a.defaultPrevented||a.metaKey||a.ctrlKey||a.shiftKey||a.altKey||a.button!==0||(a.preventDefault(),t.dataset.view==="payments"&&(c.paymentCustomerId=null,c.paymentCustomerLabel=""),V(t.dataset.view))}));let Ne=null;function tn(){const t=o("#crumbCurrent").textContent,a=[o("#branchScope"),o("#periodScope"),...T('select,input[type="search"],input[type="date"]',E)].filter(i=>i&&!i.closest("[hidden]")).map(i=>{const l=i.tagName==="SELECT"?i.selectedOptions[0]?.textContent:i.value;if(!l?.trim())return"";const r=i.closest("label")?.querySelector(".lead-field__label")?.textContent||i.parentElement.querySelector("label")?.textContent||"";return r?r.trim()+": "+l.trim():l.trim()}).filter(Boolean);for(const i of[o("#branchScope"),o("#periodScope")]){const l=i.selectedOptions[0]?.textContent?.trim();l&&!a.includes(l)&&a.unshift(l)}T('.tab.active,[role="tab"][aria-selected="true"]',E).forEach(i=>a.push(i.textContent.trim())),o("#centralPrintHeader").innerHTML=`<div class="central-print-brand">${n(e("brand_subtitle"))}</div><h1>${n(t)}</h1><p>${n([...new Set(a)].join(" · "))}</p><p>${n(e("export_pdf.generated"))}: ${n(new Date().toLocaleString(m.locale==="ms"?"ms-MY":"en-MY"))}</p><small>${n(e("export_pdf.scope"))}</small>`;const s=["workspace","payments","customers","wallet","checkin","clearance"].map(i=>e(i+".col_action").toLowerCase());T("table",E).forEach(i=>{T("thead tr:last-child th",i).forEach((r,d)=>{s.includes(r.textContent.trim().toLowerCase())&&(r.classList.add("central-print-action"),T("tbody tr",i).forEach(u=>u.children[d]?.classList.add("central-print-action")))})}),Ne===null&&(Ne=document.title),document.title="Central - "+t+" - "+(c.period||"")}function js(){Ne!==null&&(document.title=Ne,Ne=null),T(".central-print-action",E).forEach(t=>t.classList.remove("central-print-action"))}o("#exportCentralPdf").onclick=async()=>{if({leads:Ze,followup:ea,payments:aa,customers:ta,wallet:oe,checkin:pe,clearance:me}[c.view]||o('[aria-busy="true"],[role="status"]',E)){g(e("export_pdf.loading"));return}const a=o("#exportCentralPdf");a.disabled=!0;try{document.fonts?.ready&&await document.fonts.ready,tn(),window.print()}finally{a.disabled=!1}};window.addEventListener("beforeprint",tn);window.addEventListener("afterprint",js);o("#menuToggle").onclick=()=>o("#sidebar").classList.toggle("open");o("#notificationButton").onclick=()=>Bs();o("#notificationMenu").onclick=t=>{if(t.target.closest("[data-clear-notifications]")){Ps();return}const a=t.target.closest("[data-notification-view]");a&&(We(),V(a.dataset.notificationView))};document.addEventListener("keydown",t=>{t.key==="Escape"&&!o("#notificationMenu").hidden&&(t.preventDefault(),We(),o("#notificationButton").focus({preventScroll:!0}))});document.addEventListener("pointerdown",t=>{t.target.closest(".notification-wrap")||We()},!0);ua();o("#drawerClose").onclick=X;o("#drawerBackdrop").onclick=X;o("#actionDrawerClose").onclick=D;o("#actionDrawerBackdrop").onclick=D;o("#branchScope").onchange=Jt;o("#periodScope").onchange=Jt;o("#globalSearch").addEventListener("keydown",t=>{t.key==="Enter"&&(V("leads"),c.leadSearch=t.target.value,c.leadPage=1)});window.addEventListener("resize",()=>{c.view==="overview"&&(kt(),St(),Kt(),oa(),window.IMMA_TRADE&&IMMA_TRADE.resize()),c.view==="sales"&&oa(),c.view==="branches"&&Ss()});window.addEventListener("popstate",()=>{Ts(),V(zt(),{silent:!0})});V(m.initialView||zt(),{replace:!0});
