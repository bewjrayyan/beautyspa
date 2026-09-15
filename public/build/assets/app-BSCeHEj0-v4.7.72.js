function re(n,a){return getComputedStyle(document.documentElement).getPropertyValue(n).trim()||a}function e(n,a={}){const s=String(n).split(".");let i=window.IMMA_CENTRAL&&window.IMMA_CENTRAL.i18n||{};for(const r of s)if(i&&typeof i=="object"&&r in i)i=i[r];else{i=null;break}let l=typeof i=="string"?i:n;return Object.keys(a||{}).forEach(r=>{l=l.replace(new RegExp(":"+r,"g"),String(a[r]))}),l}const c=(n,a=document)=>{const s=typeof a=="string"?document.querySelector(a):a||document;return s?s.querySelector(n):null},S=(n,a=document)=>{const s=typeof a=="string"?document.querySelector(a):a||document;return s?[...s.querySelectorAll(n)]:[]},rn={leads:[]},v=window.IMMA_CENTRAL||{};let o={view:"overview",branch:String(c("#branchScope")&&c("#branchScope").value||"all"),period:String(c("#periodScope")&&c("#periodScope").value||v.metrics&&v.metrics.period&&v.metrics.period.key||""),leadSearch:"",leadStatus:"all",leadBeautician:"all",leadBranch:"all",leadPage:1,leadPerPage:10,leadMonth:(function(){const n=new Date;return n.getFullYear()+"-"+String(n.getMonth()+1).padStart(2,"0")})(),leadCalYear:null,followBucket:"all",followSearch:"",followPage:1,importTab:"paste",paymentTab:"queue",paymentSearch:"",paymentCustomerId:null,paymentCustomerLabel:"",paymentBeautician:"all",paymentBranch:"all",paymentPage:1,customerSegment:"all",customerSearch:"",customerBranch:"all",customerPage:1,walletSegment:"all",walletSearch:"",walletTier:"all",walletPage:1,walletCustomerId:null,checkinStatus:"live",checkinSearch:"",checkinBranch:"all",checkinBeautician:"all",checkinPage:1,checkinDate:v.today||new Date().toLocaleDateString("en-CA"),checkinScope:"day",clearanceState:"waiting",clearanceSearch:"",clearanceBranch:"all",clearanceBeautician:"all",clearancePage:1};const j=c("#viewRoot");let L=v.metrics||null,ka=L?JSON.stringify([o.branch,o.period]):null,ha=0,Q=[],Sa={raw:0,unique:0,duplicates:0,existing:0,converted:0,conversion_pct:0,all_time:{raw:0,unique:0,duplicates:0,existing:0,converted:0,conversion_pct:0}},P={statuses:[],sources:[],beauticians:[],branches:[],months:[]},ea=!1,Wa=null,Ce=null,O=new Set,W="",Y="",Le=[],Ma={queue:0,overdue:0,due_today:0,no_response:0,lost:0},xe={buckets:[],beauticians:[],branches:[],statuses:[]},aa=!1,Ya=null,ge=[],Ea={pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0},ye={statuses:[],beauticians:[],branches:[]},ta=!1,Ka=null,Ta={current_page:1,last_page:1,total:0},$e=[],Aa={total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0},Be={segments:[],branches:[]},na=!1,za=null,qe={current_page:1,last_page:1,total:0},De=[],Na={members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0},$t={segments:[],tiers:[]},pe=!1,Ke=0,He=!1,Ja=null,qa={current_page:1,last_page:1,total:0},ue=[],Da={live:0,scheduled:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0},Ca={statuses:[],beauticians:[],branches:[]},me=!1,ze=0,Re=!1,Ga=null,Ha={current_page:1,last_page:1,total:0},we=null,La=0,_e=[],Ra={waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0},xa={states:[],beauticians:[],branches:[]},ve=!1,Je=0,Ue=!1,Xa=null,Ua={current_page:1,last_page:1,total:0};function N(n,a){return String(n||"").replace("__ID__",String(a))}function q(n=!0){const a={Accept:"application/json","X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":v.csrf||document.querySelector('meta[name="csrf-token"]')?.content||""};return n&&(a["Content-Type"]="application/json"),a}async function E(){const n=v.leadsUrl||"";if(!n){g(e("workspace.load_error"));return}const a=new URLSearchParams;o.leadSearch&&a.set("q",o.leadSearch),o.leadStatus&&o.leadStatus!=="all"&&a.set("status",o.leadStatus);const s=o.leadBranch!=="all"?o.leadBranch:o.branch||"all";s&&s!=="all"&&a.set("branch",s),o.leadBeautician&&o.leadBeautician!=="all"&&a.set("beautician",o.leadBeautician),o.leadMonth&&o.leadMonth!=="all"&&a.set("month",o.leadMonth),a.set("page",String(o.leadPage||1)),a.set("per_page",String(o.leadPerPage||10)),O.clear(),W="",Y="",ra(),ea=!0,tt();try{const i=await fetch(`${n}?${a.toString()}`,{headers:q(!1),credentials:"same-origin"});if(!i.ok)throw new Error("leads "+i.status);const l=await i.json();Pe=l.meta||{},[10,50,100,200].includes(Number(Pe.per_page))&&(o.leadPerPage=Number(Pe.per_page)),Q=Array.isArray(l.data)?l.data:[],Sa=l.meta&&l.meta.summary||Sa,P=l.filters||P,rn.leads=Q}catch(i){console.error(i),g(e("workspace.load_error"))}finally{ea=!1,o.view==="leads"&&(Lt(),tt(),la(),Fe())}}function h(n){return Number(n||0).toLocaleString("en-MY")}function C(n){return"RM"+Number(n||0).toLocaleString("en-MY",{maximumFractionDigits:0})}function G(n){return Number(n||0).toLocaleString("en-MY",{maximumFractionDigits:1})+"%"}function t(n){return String(n??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;")}function ee(n,a=""){const s=Number(n||0),i=Math.abs(s).toFixed(1)+a;return s>0?`↑ +${i} ${e("overview.vs_prev_period")}`:s<0?`↓ -${i} ${e("overview.vs_prev_period")}`:`→ 0${a} ${e("overview.vs_prev_period")}`}function le(n,a){return`${e("overview.target")}: ${a}`}async function sa(){const n=v.metricsUrl||"",a=++ha,s=o.branch||"all",i=o.period||"",l=JSON.stringify([s,i]),r=new URLSearchParams({branch:s,period:i});try{if(!n)throw new Error("Missing metrics endpoint");const d=await fetch(`${n}?${r.toString()}`,{headers:q(!1),credentials:"same-origin"});if(!d.ok)throw new Error("metrics "+d.status);const p=await d.json();if(a!==ha||l!==JSON.stringify([o.branch,o.period]))return;if(!p.metrics)throw new Error("Missing metrics payload");L=p.metrics,ka=l,v.metrics=L,_a(),o.view==="overview"&&Mt(),o.view==="sales"&&Kt()}catch{if(a!==ha||l!==JSON.stringify([o.branch,o.period]))return;["overview","sales"].includes(o.view)&&ka!==l?(j.innerHTML=`<section class="card"><div class="pay-empty" role="alert"><strong>${t(e("overview.metrics_error"))}</strong><button type="button" class="btn" id="retryMetrics">${t(e("reporting.refresh"))}</button></div></section>`,c("#retryMetrics").onclick=()=>sa()):g(e("overview.metrics_error"))}}function ne(n,a={}){const s=Math.max(1,Number(a.last_page)||1),i=Math.max(1,Math.min(s,Number(a.current_page)||1)),l=s<=5?Array.from({length:s},(u,m)=>m+1):[...new Set([1,i-1,i,i+1,s].filter(u=>u>=1&&u<=s))].sort((u,m)=>u-m),r=(u,m,_="")=>`<button type="button" class="central-pagination__button" data-page="${u}" ${_}>${m}</button>`;let d="",p=0;return l.forEach(u=>{p&&u-p>1&&(d+='<span class="central-pagination__ellipsis" aria-hidden="true">…</span>'),d+=r(u,String(u),`aria-label="${t(e("pagination.page",{page:u}))}" ${u===i?'aria-current="page" disabled':""}`),p=u}),`<nav class="central-pagination" data-pager="${n}" aria-label="${t(e("pagination.label"))}">
    ${r(i-1,"‹",`id="${n}Prev" aria-label="${t(e("operations.previous"))}" ${i<=1?"disabled":""}`)}
    ${d}
    ${r(i+1,"›",`id="${n}Next" aria-label="${t(e("operations.next"))}" ${i>=s?"disabled":""}`)}
  </nav>`}function se(n,a){const s=c(`[data-pager="${n}"]`);s&&S("button[data-page]",s).forEach(i=>i.onclick=async()=>{if(i.disabled)return;const l=S("button:not(:disabled)",s);l.forEach(r=>r.disabled=!0),s.setAttribute("aria-busy","true");try{await a(Number(i.dataset.page))}finally{s.isConnected&&(l.forEach(r=>r.disabled=!1),s.removeAttribute("aria-busy"))}})}let Pe={},wt={},Qa=1;function R(n){return"RM"+Number(n).toLocaleString("en-MY")}function x(n){const a=String(n??""),s=a.toUpperCase();let i="gray";return s.includes("VERIFIED")||s.includes("PAID")||s==="CONVERTED"||s==="COMPLETED"||s.includes("BANK CHECKED")||s.includes("PROOF")?i="success":s.includes("FOLLOW")||s.includes("PENDING")||s.includes("REVIEW")||s==="BOOKING"||s==="CLAIMED"||s.includes("PROCESSING")||s.includes("DECLARED")?i="warning":s.includes("HOLD")||s.includes("LOST")||s.includes("NO RESPONSE")||s.includes("CANCEL")||s.includes("REFUND")?i="danger":s==="NEW"&&(i="blue"),`<span class="badge ${i}"><span class="status-dot"></span>${t(a)}</span>`}function be(n,a,s=""){return`<div class="page-head"><div><h1 class="page-title">${n}</h1><div class="page-subtitle">${a}</div></div><div class="page-actions">${s}</div></div>`}function X(n,a,s,i,l,r="blue",d=null,p="",u=""){const m=t(p||a),_=t(a),b=t(s),k=t(i),$=t(l),f=t(u),w=/^[↑+]/.test(String(i).trim())||/above|\+|up/i.test(String(i)),y=/^[↓-]/.test(String(i).trim())||/below|down/i.test(String(i))?"down":w?"up":"flat";return`<div class="kpi-card kpi-card--${r}">
    <div class="kpi-card__head">
      <div class="kpi-card__icon" aria-hidden="true">${n}</div>
      <div class="kpi-label" title="${m}">${_}</div>
    </div>
    <div class="kpi-value mono">${b}</div>
    <div class="kpi-card__foot">
      <span class="kpi-trend kpi-trend--${y}">${k}</span>
      <span class="kpi-target">${$}</span>
    </div>
    ${u?`<div class="kpi-spark" id="${f}"></div>`:""}
    ${d!==null?`<div class="progress kpi-card__bar"><span style="width:${Math.min(100,d)}%"></span></div>`:""}
  </div>`}function U(n){const a={database:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v6c0 1.7 3.1 3 7 3s7-1.3 7-3V5"/><path d="M5 11v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"/></svg>',new:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="4"/><path d="M3 20c.7-4 2.7-6 6-6 2.1 0 3.7.8 4.8 2.3M18 8v6M15 11h6"/></svg>',repeated:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7h-9a5 5 0 0 0-5 5v1"/><path d="m17 4 3 3-3 3M4 17h9a5 5 0 0 0 5-5v-1"/><path d="m7 20-3-3 3-3"/></svg>',customers:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c.5-4 2.5-6 6-6s5.5 2 6 6M16 5.5a3 3 0 0 1 0 5.8M17 14c2.4.5 3.7 2.4 4 5"/></svg>',conversion:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 18 10 12l4 3 6-8"/><path d="M15 7h5v5"/><circle cx="6" cy="6" r="2"/></svg>',queue:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v12H4z"/><path d="M4 13h4l2 3h4l2-3h4"/></svg>',overdue:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M12 7v5l3 2M12 4V2M6.4 5.1 5 3.7"/></svg>',today:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16M8.5 15l2.2 2.2 4.8-5"/></svg>',no_response:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 18h10a4 4 0 0 0 4-4V8a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v9l2-2z"/><path d="M8 10h.01M12 10h.01M16 10h.01"/></svg>',lost:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 11a8 8 0 1 1-2.3-5.7"/><path d="M20 4v7h-7"/><path d="m8 12 2.5 2.5L16 9"/></svg>',revenue:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/></svg>',orders:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18H6z"/><path d="M9 3v4h6V3M9 12h6M9 16h4"/></svg>',average:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19 10 5l6 14M6 14h8M19 5v14M17 9h4"/></svg>',target:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="m15 9 5-5"/></svg>'};return a[n]||a.database}function I(n,a,s,i,l,r,d="blue"){return`<div class="kpi-card kpi-card--${d} lead-kpi-card lead-kpi-card--${d}">
    <div class="lead-kpi-card__glow" aria-hidden="true"></div>
    <div class="kpi-card__head">
      <div class="kpi-card__icon" aria-hidden="true">${n}</div>
      <div class="kpi-label">${t(a)}</div>
    </div>
    <div class="lead-kpi__value">
      <strong>${t(s)}</strong>
      <span>${t(i)}</span>
    </div>
    <div class="kpi-card__foot">
      <span class="kpi-trend kpi-trend--flat">${t(l)}</span>
      <span class="kpi-target lead-kpi__detail">${t(r)}</span>
    </div>
  </div>`}function Te(n,a,s,i=""){const l=i?` trade-pane__chart--${i}`:"";return`<section class="trade-pane">
    <div class="trade-pane__head">
      <div>
        <div class="trade-pane__eyebrow">${e("trade.section")}</div>
        <div class="trade-pane__title">${e(n)}</div>
        <div class="trade-pane__sub">${e(a)}</div>
      </div>
    </div>
    <div class="trade-pane__chart${l}" id="${s}"></div>
  </section>`}function on(n=new Date){const a=["JAN","FEB","MAC","APR","MEI","JUN","JUL","OGOS","SEPT","OKT","NOV","DIS"];return`${String(n.getDate()).padStart(2,"0")} ${a[n.getMonth()]} ${n.getFullYear()}`}function cn(){const n=L||{},a=n.kpis||{},s=n.targets||{},i=Array.isArray(n.beauticians)?n.beauticians:[],l=Number(a.new_buyers||0),r=Number(s.leads||0),d=r>0?Math.round(l/r*1e3)/10:0,p=!!(n.period&&n.period.key),u=n.period&&n.period.label?t(n.period.label):on(),m=n.slogan||e("daily.slogan"),_=n.quote||e("daily.quote");return`<section class="daily-hero">
    <div class="daily-hero__top">
      <div class="daily-hero__copy">
        <div class="daily-hero__eyebrow">${e("daily.eyebrow")}</div>
        <h2 class="daily-hero__title">${e("daily.title")}</h2>
        <div class="daily-hero__meta">
          <span class="daily-hero__date">${u}</span>
          <span class="daily-hero__pill">${e("daily.live")}</span>
        </div>
        <p class="daily-hero__quote">${t(m)} -- "${t(_)}"</p>
      </div>
    </div>
    <div class="daily-hero__stats">
      <div class="daily-stat daily-stat--primary">
        <div class="daily-stat__label">${e(p?"overview.kpi_unique_leads":"daily.leads_today")}</div>
        <div class="daily-stat__value">${h(l)}</div>
        <div class="daily-stat__sub">${e("daily.beauticians_active",{count:i.length||0})}</div>
      </div>
      <div class="daily-stat">
        <div class="daily-stat__label">${e("daily.month_progress")}</div>
        <div class="daily-stat__value daily-stat__value--sm">${h(l)} / ${h(r)}</div>
        <div class="daily-stat__sub">${e("daily.of_monthly_target",{pct:d})}</div>
        <div class="progress daily-stat__bar"><span style="width:${Math.min(100,d)}%"></span></div>
      </div>
    </div>
  </section>`}const Za=[["#1d4ed8","#60a5fa"],["#9a3412","#f59e0b"],["#065f46","#34d399"],["#6d28d9","#a78bfa"],["#be123c","#fb7185"],["#0e7490","#22d3ee"],["#a16207","#facc15"]];function dn(n){const a=String(n||"");let s=0;for(let l=0;l<a.length;l++)s=s*31+a.charCodeAt(l)>>>0;const i=Za[Math.abs(s)%Za.length];return`linear-gradient(145deg, ${i[0]}, ${i[1]})`}function pn(n){const a=String(n||"").trim().split(/\s+/).filter(Boolean);return((a[0]?.[0]||"")+(a[1]?.[0]||"")).toUpperCase()}function un(){const n=L||{},a=!!(n.period&&n.period.key),s=Array.isArray(n.beauticians)?n.beauticians:[],i=Array.isArray(n.beauticians)?n.beauticians.length:0;Math.max(1,Number(n.beautician_count)||i||1);const l=Number(n.targets&&n.targets.beautician_leads||0)||112,r=[...s].sort((_,b)=>b.leads-_.leads),d=n.period&&n.period.label?t(n.period.label):e("common.this_month"),p=r.reduce((_,b)=>_+(Number(b.leads)||0),0),u=["🥇","🥈","🥉"],m=r.map((_,b)=>{const k=Number(_.leads)||0,$=Number(_.target)||l,f=$>0?Math.round(k/$*100):0,w=p>0?Math.max(4,Math.round(k/p*100)):0,M=b===0?"gold":b===1?"silver":b===2?"bronze":"",y=f>=100?"is-hit":f>=75?"is-close":"is-low",T=t(String(_.name||"")),B=b<3?`<span class="rank rank--medal rank--${M}" title="${e("daily.rank_title",{n:b+1})}">${u[b]}</span>`:`<span class="rank">${b+1}</span>`;return`<tr class="daily-row${M?` daily-row--${M}`:""}">
      <td class="daily-rank-cell">${B}</td>
      <td>
        <div class="person-cell">
          <div class="mini-avatar leader-avatar${M?` mini-avatar--${M}`:""}" style="background:${dn(_.name)}">${pn(_.name)}</div>
          <div class="person-cell__text">
            <strong>${T}${b===0?` <span class="leader-tag">${e("daily.leader")}</span>`:""}</strong>
            <small>${e("daily.target_month",{count:$})}<i class="daily-dot"></i>${d}</small>
          </div>
        </div>
      </td>
      <td class="daily-num-cell"><strong class="daily-num" title="${k.toLocaleString()} ${e("daily.leads")}">${k.toLocaleString()}</strong></td>
      <td>
        <div class="share-cell">
          <div class="share-cell__track"><span style="width:${Math.min(100,w)}%"></span></div>
          <strong>${w}%</strong>
        </div>
      </td>
      <td><span class="daily-target">${$}</span></td>
      <td>
        <div class="achieve">
          <strong class="achieve__pct ${y}">${f}%</strong>
          <div class="progress achieve__bar"><span class="achieve__fill ${y}" style="width:${Math.min(100,f)}%"></span></div>
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
          <tbody>${m}</tbody>
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
  </div>`}function Ba(n,a,s,i,l,r){const d=Math.min(r,i/2,l/2);n.beginPath(),n.moveTo(a+d,s),n.arcTo(a+i,s,a+i,s+l,d),n.arcTo(a+i,s+l,a,s+l,d),n.arcTo(a,s+l,a,s,d),n.arcTo(a,s,a+i,s,d),n.closePath()}function kt(){const n=c("#dailyLeadChart");if(!n)return;We(n);const a=n.getContext("2d"),s=L||{},i=Array.isArray(s.beauticians)?s.beauticians:[],r=[...i.length?i.map(y=>({name:String(y.name||""),leads:Number(y.leads||0)})):[]].sort((y,T)=>T.leads-y.leads),d=r.map(y=>y.name),p=r.map(y=>y.leads),u=n.clientWidth,m=n.clientHeight,_={l:28,r:10,t:28,b:42},b=Math.max(...p,1)*1.2,k=Math.max(6,Math.min(12,u/60)),$=Math.max(14,(u-_.l-_.r-k*(p.length-1))/p.length),f=re("--navy","#1d4ed8"),w=re("--rose","#0ea5e9");a.fillStyle="#f8fafc",Ba(a,0,0,u,m,12),a.fill(),a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let y=0;y<4;y++){const T=_.t+y*((m-_.t-_.b)/3);a.beginPath(),a.moveTo(_.l,T),a.lineTo(u-_.r,T),a.stroke()}const M=[["#1d4ed8","#38bdf8"],["#2563eb","#7dd3fc"],["#0284c7","#67e8f9"]];p.forEach((y,T)=>{const B=_.l+T*($+k),D=Math.max(4,y/b*(m-_.t-_.b)),fe=m-_.b-D,[K,sn]=M[Math.min(T,2)]||[f,w],va=a.createLinearGradient(0,fe,0,m-_.b);va.addColorStop(0,T<3?sn:w),va.addColorStop(1,T<3?K:"#93c5fd"),a.fillStyle=va,Ba(a,B,fe,$,D,8),a.fill(),a.fillStyle="#0f172a",a.font="700 12px Poppins",a.textAlign="center",a.fillText(String(y),B+$/2,fe-8);const ln=d[T].length>7?d[T].slice(0,6)+"…":d[T];a.fillStyle="#475569",a.font="600 10px Poppins",a.fillText(ln,B+$/2,m-14)})}function St(){const n=c("#dailyTrendChart");if(!n)return;We(n);const a=n.getContext("2d"),s=L||{},i=s.leads_trend&&Array.isArray(s.leads_trend.actual)?s.leads_trend:null,l=i?[...i.actual]:[],r=i&&Array.isArray(i.labels)?i.labels:Array.from({length:l.length},(w,M)=>M===l.length-1?"Today":"D-"+(l.length-1-M)),d=n.clientWidth,p=n.clientHeight,u={l:28,r:14,t:22,b:28},m=Math.max(...l,1)*1.15,_=w=>u.l+w*((d-u.l-u.r)/Math.max(l.length-1,1)),b=w=>p-u.b-w/m*(p-u.t-u.b),k=re("--navy","#1d4ed8"),$=re("--rose","#0ea5e9");a.fillStyle="#f8fafc",Ba(a,0,0,d,p,12),a.fill(),a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let w=0;w<3;w++){const M=u.t+w*((p-u.t-u.b)/2);a.beginPath(),a.moveTo(u.l,M),a.lineTo(d-u.r,M),a.stroke()}const f=a.createLinearGradient(0,u.t,0,p-u.b);f.addColorStop(0,"rgba(14,165,233,.28)"),f.addColorStop(1,"rgba(37,99,235,.02)"),a.beginPath(),a.moveTo(_(0),p-u.b),l.forEach((w,M)=>a.lineTo(_(M),b(w))),a.lineTo(_(l.length-1),p-u.b),a.closePath(),a.fillStyle=f,a.fill(),a.beginPath(),l.forEach((w,M)=>M?a.lineTo(_(M),b(w)):a.moveTo(_(M),b(w))),a.strokeStyle=k,a.lineWidth=2.75,a.lineJoin="round",a.lineCap="round",a.stroke(),l.forEach((w,M)=>{const y=_(M),T=b(w);a.beginPath(),a.arc(y,T,5,0,Math.PI*2),a.fillStyle="#fff",a.fill(),a.lineWidth=2.5,a.strokeStyle=$,a.stroke(),a.beginPath(),a.arc(y,T,2.2,0,Math.PI*2),a.fillStyle=k,a.fill(),a.fillStyle="#0f172a",a.font="700 10px Poppins",a.textAlign="center",a.fillText(String(w),y,T-10),a.fillStyle="#64748b",a.font="600 10px Poppins",a.fillText(r[M],y,p-10)})}function mn(){const n="company_target",a=L||{},s=a.targets||{},i=Math.max(1,Number(a.beautician_count)||1),l=Number(s.leads)||0,r=Number(s.conv_pct)||0,d=Number(s.buyers)||0,p=Number(s.avg_sale)||0,u=Number(s.sales)||0,m=Number(s.beautician_leads)||112,_=Math.round(m*r/100),b=Math.round(_*p),k=T=>{const B=Number(T||0);return Math.abs(B)>=1e6?"RM"+(B/1e6).toFixed(1).replace(/\.0$/,"")+"M":Math.abs(B)>=1e3?"RM"+(B/1e3).toFixed(1).replace(/\.0$/,"")+"k":C(B)},$=h(l),f=G(r),w=h(d),M=C(p),y=k(u);return`<section class="company-target">
    <div class="company-target__hero">
      <div class="company-target__hero-copy">
        <div class="company-target__eyebrow">${e(n+".eyebrow")}</div>
        <h2 class="company-target__title">${e(n+".title",{leadgoal:$})}</h2>
        <p class="company-target__desc">${e(n+".desc",{leadgoal:$,convgoal:f,buyergoal:w,avggoal:M,salesgoal:y})}</p>
      </div>
      <div class="company-target__hero-goal">
        <div class="company-target__goal-label">${e(n+".sales_goal")}</div>
        <div class="company-target__goal-value">${C(u)}</div>
        <div class="company-target__goal-sub">${e(n+".goal_sub",{beaucount:i})}</div>
      </div>
    </div>

    <div class="company-target__chain" aria-label="${e(n+".chain_aria")}">
      ${[[$,e(n+".step_leads"),e(n+".step_leads_meta")],[f,e(n+".step_conv"),e(n+".step_conv_meta")],[w,e(n+".step_buyers"),e(n+".step_buyers_meta")],[M,e(n+".step_avg"),e(n+".step_avg_meta")],[y,e(n+".step_sales"),e(n+".step_sales_meta")]].map((T,B)=>`
        ${B?'<div class="company-target__arrow" aria-hidden="true">↓</div>':""}
        <div class="company-target__step ${B===4?"company-target__step--goal":""}">
          <div class="company-target__step-value">${T[0]}</div>
          <div class="company-target__step-label">${T[1]}</div>
          <div class="company-target__step-meta">${T[2]}</div>
        </div>
      `).join("")}
    </div>

    <div class="grid company-target__kpis">
      <article class="ct-card ct-card--kpi1">
        <div class="ct-card__head">
          <span class="ct-card__badge">${e(n+".kpi1_badge")}</span>
          <span class="ct-card__icon">🎯</span>
        </div>
        <h3 class="ct-card__title">${e(n+".kpi1_title",{convgoal:f})}</h3>
        <p class="ct-card__text">${e(n+".kpi1_text",{leadgoal:$,buyergoal:w})}</p>
        <div class="ct-card__math">
          <div><span>${e(n+".kpi1_leads")}</span><strong>${$}</strong></div>
          <div><span>×</span><strong>${f}</strong></div>
          <div><span>${e(n+".kpi1_buyers")}</span><strong>${w}</strong></div>
        </div>
      </article>

      <article class="ct-card ct-card--kpi2">
        <div class="ct-card__head">
          <span class="ct-card__badge">${e(n+".kpi2_badge")}</span>
          <span class="ct-card__icon">💰</span>
        </div>
        <h3 class="ct-card__title">${e(n+".kpi2_title",{avggoal:M})}</h3>
        <p class="ct-card__text">${e(n+".kpi2_text",{buyergoal:w,avggoal:M,salesgoal:y})}</p>
        <div class="ct-card__math">
          <div><span>${e(n+".kpi2_buyers")}</span><strong>${w}</strong></div>
          <div><span>×</span><strong>${M}</strong></div>
          <div><span>${e(n+".kpi2_sales")}</span><strong>${y}</strong></div>
        </div>
      </article>
    </div>

    <div class="company-target__beauty-head">
      <div>
        <div class="daily-panel__eyebrow">${e(n+".per_beautician")}</div>
        <div class="daily-panel__title">${e(n+".per_title")}</div>
        <div class="daily-panel__sub">${e(n+".per_sub",{beaucount:i})}</div>
      </div>
    </div>
    <div class="grid company-target__beauty">
      <article class="ct-card">
        <div class="ct-card__head"><span class="ct-card__badge">${e(n+".badge_leads")}</span></div>
        <div class="ct-card__big">${h(m)}</div>
        <div class="ct-card__unit">${e(n+".unit_leads")}</div>
        <p class="ct-card__text">${e(n+".text_leads",{leadgoal:$,beaucount:i,leadtarget:h(m)})}</p>
      </article>
      <article class="ct-card">
        <div class="ct-card__head"><span class="ct-card__badge">${e(n+".badge_convert")}</span></div>
        <div class="ct-card__big">±${h(_)}</div>
        <div class="ct-card__unit">${e(n+".unit_convert")}</div>
        <p class="ct-card__text">${e(n+".text_convert",{leadtarget:h(m),convgoal:f,buytarget:h(_)})}</p>
      </article>
      <article class="ct-card ct-card--accent">
        <div class="ct-card__head"><span class="ct-card__badge">${e(n+".badge_sales")}</span></div>
        <div class="ct-card__big">${k(b)}</div>
        <div class="ct-card__unit">${e(n+".unit_sales")}</div>
        <p class="ct-card__text">${e(n+".text_sales",{buytarget:h(_),avggoal:M,salarget:C(b),beaucount:i,compact:k(b),total:y})}</p>
      </article>
    </div>
  </section>`}function Mt(){window.IMMA_TRADE&&IMMA_TRADE.dispose();const n=L||{},a=n.kpis||{},s=a.vs_prev||{},i=n.targets||{},l=n.dual||{},r=Number(l.sales_pct||0),d=Number(i.sales||0),p=Number(a.sales||0),u=p-d,m=t(n.period&&n.period.label||e("common.this_month")),_=n.ops||{},b=_.checkin||{},k=_.clearance||{},$=_.payments||{},f=e("ops.value_customers",{count:h(b.live||0)}),w=e("ops.meta_checkin",{waiting:h(b.waiting||0),treatment:h(b.in_treatment||0)}),M=e("ops.value_customers",{count:h(k.queue||0)}),y=e("ops.meta_clearance",{waiting:h(k.waiting||0),blocked:h(k.blocked||0)}),T=e("ops.value_pending",{count:h($.queue||0)}),B=e("ops.meta_payments",{processing:h($.processing||0),hold:h($.hold||0)});j.innerHTML=`${be(e("overview.title"),e("overview.subtitle"),`<button class="btn soft">${m}</button><button class="btn primary" data-jump="leads">${e("common.open_leads")}</button>`)}
  ${mn()}
  <div class="trade-grid trade-grid--2">
    ${Te("trade.dual_title","trade.dual_sub","tradeDualRing","sm")}
    ${Te("trade.equity_title","trade.equity_sub","tradeEquity","lg")}
  </div>
  <div class="section-label">${e("overview.actual_label")}</div>
  <div class="grid kpi-grid">
    ${X("♙",e("overview.kpi_unique_leads"),h(a.new_buyers),ee(s.new_buyers,"%"),le(e("overview.kpi_unique_leads"),h(i.leads||0)),"rose",Math.min(100,(a.new_buyers||0)/Math.max(1,i.leads||0)*100),"","sparkLeads")}
    ${X("▣",e("overview.kpi_buyers"),h(a.buyers),ee(s.buyers,"%"),le(e("overview.kpi_buyers"),h(i.buyers||0)),"blue",Math.min(100,(a.buyers||0)/Math.max(1,i.buyers||0)*100),"","sparkBuyers")}
    ${X("%",e("overview.kpi_conv_rate"),G(a.new_buyer_share_pct),ee(s.new_buyer_share_pct,"pp"),le(e("overview.kpi_conv_rate"),G(i.conv_pct||0)),"green",Math.min(100,Number(a.new_buyer_share_pct||0)),"","sparkConv")}
    ${X("◫",e("overview.kpi_sales"),C(a.sales),ee(s.sales,"%"),le(e("overview.kpi_sales"),C(d)),"rose",Math.min(100,r),"","sparkSales")}
    ${X("▥",e("overview.kpi_avg_sale"),C(a.avg_sale),ee(s.avg_sale,"%"),le(e("overview.kpi_avg_sale"),C(i.avg_sale||0)),"purple",Math.min(100,(a.avg_sale||0)/Math.max(1,i.avg_sale||0)*100),"","sparkAvg")}
  </div>
  <div class="trade-grid trade-grid--2" style="margin-top:12px">
    ${Te("trade.waterfall_title","trade.waterfall_sub","tradeWaterfall")}
    <section class="card">
      <div class="card-title-row"><div class="card-title">▥ ${e("overview.lead_status")}</div><button class="btn small soft">${m}</button></div>
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
        <span class="sales-card__pill">${m} · ${C(p)}</span>
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
            <strong>${G(r)}</strong>
            <span>${e("overview.of_target")}</span>
          </div>
        </div>
        <div class="target-card__side">
          <div class="target-card__stat">
            <span>${e("overview.target")}</span>
            <strong>${C(d)}</strong>
          </div>
          <div class="target-card__stat">
            <span>${e("overview.actual")}</span>
            <strong>${C(p)}</strong>
          </div>
          <div class="target-card__delta">
            <strong>${u>=0?"+":""}${C(u)}</strong>
            <span>${e("overview.vs_target_month")}</span>
          </div>
        </div>
      </div>
    </section>
  </div>

  <section class="card" style="margin-top:12px"><div class="card-title-row"><div class="card-title">♙ ${e("overview.beauticians")}</div><button class="btn small primary" data-jump="beauticians">${e("common.view_all")}</button></div>${_n()}</section>

  <div class="grid three-col" style="margin-top:12px">
    ${(n.branches||[]).slice(0,6).map(D=>Tt(D.name,D.new_buyers||0,D.buyers||0,D.conv||0,D.sales||0,D.avg||0,D.buyers||0)).join("")||`<section class="card"><div class="empty"><strong>${e("overview.no_branch_data")}</strong></div></section>`}
  </div>

  <div class="grid op-row" style="margin-top:12px">
    ${ba("♧",e("ops.checkin"),f,w,"checkin",e("ops.checkin"),"green")}
    ${ba("◷",e("ops.clearance"),M,y,"clearance",e("ops.clearance"),"warning")}
    ${ba("▣",e("ops.payments"),T,B,"payments",e("ops.payments"),"rose")}
  </div>
  <div style="margin-top:12px">${cn()}</div>
  <div style="margin-top:12px">${un()}</div>`,requestAnimationFrame(()=>{if(kt(),St(),zt(),da(),ie(),window.IMMA_TRADE){const D=Object.assign({},n.ticker||{}),fe={leadsUp:Number(s.new_buyers||0)>=0,convUp:Number(s.new_buyer_share_pct||0)>=0,salesUp:Number(s.sales||0)>=0,targetUp:r>=100,avgUp:Number(s.avg_sale||0)>=0};IMMA_TRADE.render({ticker:Object.assign(D,fe),leadsPct:Number(l.buyers_pct||l.leads_pct||0),salesPct:r,equityActual:n.equity&&n.equity.actual||[],equityTarget:n.equity&&n.equity.target_path||[],equityLabels:n.equity&&n.equity.labels||[],waterfall:(n.waterfall||n.status_mix||[]).map(K=>({name:String(K.name||""),value:K.value})),beauticians:(n.beauticians||[]).map(K=>({name:String(K.name||""),leads:K.leads||0,converted:K.converted||0,conv:K.conv||0,sales:K.sales||0})),heatmap:n.heatmap||[],sparks:n.sparks||[]})}})}function _n(){const n=Array.isArray(L?.beauticians)?L.beauticians:[];return n.length?`<div class="table-wrap"><table class="data-table"><thead><tr><th>#</th><th>${e("overview.col_beautician")}</th><th title="${e("overview.kpi_unique_leads")}">${e("overview.unique")}</th><th>${e("overview.kpi_buyers")}</th><th title="${e("overview.kpi_conv_rate")}">${e("overview.conv_rate")}</th><th>${e("overview.kpi_sales")}</th><th>${e("overview.kpi_avg_sale")}</th><th>${e("overview.col_orders")}</th></tr></thead><tbody>${n.map((a,s)=>{const i=t(a.name);return`<tr><td><span class="rank">${s+1}</span></td><td><strong>${i}</strong></td><td>${h(a.leads)}</td><td>${h(a.buyers)}</td><td style="color:${a.conv>=40?"var(--success)":a.conv<35?"var(--danger)":"#b16e10"};font-weight:700">${G(a.conv)}</td><td>${C(a.sales)}</td><td>${C(a.avg)}</td><td>${h(a.orders||0)}</td></tr>`}).join("")}</tbody></table></div>`:`<div class="empty"><strong>${e("overview.no_beautician_data")}</strong></div>`}function Tt(n,a,s,i,l,r,d){const p=i>=40?"good":i>=35?"ok":"low",u=t(n),m=t(String(n||"").slice(0,2).toUpperCase());return`<section class="card branch-card branch-card--${p}">
    <div class="branch-card__head">
      <div class="branch-card__identity">
        <div class="branch-card__mark" aria-hidden="true">${m}</div>
        <div>
          <div class="branch-card__name">${u} ${e("overview.branch")}</div>
          <div class="branch-card__meta">${e("overview.this_month_meta")}</div>
        </div>
      </div>
      <button type="button" class="branch-card__link link-btn" data-branch="${u}">${e("common.details")}</button>
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
  </section>`}function ba(n,a,s,i,l,r,d){return`<section class="card op-card"><div class="op-icon" style="background:${d==="green"?"var(--success-soft)":d==="warning"?"var(--warning-soft)":"var(--rose-soft)"}">${n}</div><div class="op-body"><div class="op-title">${a}</div><div class="op-value">${s}</div><div class="op-meta">${i}</div></div><button class="btn small primary" data-jump="${l}">${r} →</button></section>`}function et(n){(!o.leadMonth||o.leadMonth==="all")&&(o.leadMonth=Oe());const[a,s]=o.leadMonth.split("-").map(Number),i=new Date(a,s-1+n,1);o.leadMonth=i.getFullYear()+"-"+String(i.getMonth()+1).padStart(2,"0"),o.leadPage=1,E().then(()=>Fe())}function Ie(n){if(!n||n==="all")return null;const[a,s]=String(n).split("-").map(Number);return!a||!s?null:{y:a,m:s}}function Ct(){const n=(v.locale||"en").toLowerCase().startsWith("ms")?"ms-MY":"en-GB";return Array.from({length:12},(a,s)=>new Date(2e3,s,1).toLocaleString(n,{month:"short"}))}function pa(n){if(!n||n==="all")return e("workspace.all_months");const a=Ie(n);if(!a)return String(n);const i=(P.months||[]).find(l=>String(l.value)===String(n));return i?i.label:Ct()[a.m-1]+" "+a.y}function Oe(){const n=new Date;return n.getFullYear()+"-"+String(n.getMonth()+1).padStart(2,"0")}function vn(){const n=new Date;return Oe()+"-"+String(n.getDate()).padStart(2,"0")}function hn(){const n=Ie(o.leadMonth)||Ie(Oe()),a=o.leadCalYear||n.y,s=!!v.canCreateLead;return`
    <div class="lead-cal" id="leadCalendar">
      <button type="button" class="lead-cal__nav" id="leadMonthPrev" title="${t(e("workspace.month_prev"))}" aria-label="${t(e("workspace.month_prev"))}">‹</button>
      <button type="button" class="lead-cal__toggle" id="leadCalToggle" aria-expanded="false" aria-haspopup="dialog">
        <span class="lead-cal__icon" aria-hidden="true">▦</span>
        <span id="leadMonthLabel">${t(pa(o.leadMonth))}</span>
      </button>
      <button type="button" class="lead-cal__nav" id="leadMonthNext" title="${t(e("workspace.month_next"))}" aria-label="${t(e("workspace.month_next"))}">›</button>
      <div class="lead-cal__panel hidden" id="leadCalPanel" role="dialog" aria-label="${t(e("workspace.month_label"))}">
        <div class="lead-cal__year-row">
          <button type="button" class="lead-cal__nav" id="leadCalYearPrev" aria-label="${t(e("workspace.year_prev"))}">‹</button>
          <strong id="leadCalYearLabel">${a}</strong>
          <button type="button" class="lead-cal__nav" id="leadCalYearNext" aria-label="${t(e("workspace.year_next"))}">›</button>
        </div>
        <div class="lead-cal__grid" id="leadCalGrid"></div>
        <div class="lead-cal__footer">
          <button type="button" class="btn soft small" id="leadCalAll">${t(e("workspace.all_months"))}</button>
          <button type="button" class="btn soft small" id="leadMonthThis">${t(e("workspace.this_month"))}</button>
        </div>
      </div>
    </div>
    ${s?`
      <button type="button" class="btn" data-jump="import" data-import-tab="paste">${t(e("workspace.paste_leads"))}</button>
      <button type="button" class="btn" data-jump="import" data-import-tab="excel">${t(e("workspace.upload_excel"))}</button>
      <button type="button" class="btn primary" data-jump="import" data-import-tab="paste">${t(e("workspace.import_leads"))}</button>
    `:""}
  `}function ia(){const n=c("#leadCalGrid"),a=c("#leadCalYearLabel");if(!n)return;const s=Ie(o.leadMonth),i=o.leadCalYear||(s?s.y:new Date().getFullYear());o.leadCalYear=i,a&&(a.textContent=String(i));const l=Ct(),r=Oe();n.innerHTML=l.map((d,p)=>{const u=`${i}-${String(p+1).padStart(2,"0")}`;return`<button type="button" class="lead-cal__month${String(o.leadMonth)===u?" is-selected":""}${u===r?" is-now":""}" data-month="${u}">${t(d)}</button>`}).join(""),S("[data-month]",n).forEach(d=>{d.onclick=()=>{o.leadMonth=d.dataset.month,o.leadPage=1,de(),Fe(),E()}})}function bn(){const n=c("#leadCalPanel"),a=c("#leadCalToggle");if(!n||!a)return;const s=Ie(o.leadMonth);o.leadCalYear=s?s.y:new Date().getFullYear(),n.classList.remove("hidden"),a.setAttribute("aria-expanded","true"),ia()}function de(){const n=c("#leadCalPanel"),a=c("#leadCalToggle");n&&n.classList.add("hidden"),a&&a.setAttribute("aria-expanded","false")}function Fe(){const n=c("#leadMonthLabel");n&&(n.textContent=pa(o.leadMonth)),c("#leadCalPanel")&&!c("#leadCalPanel").classList.contains("hidden")&&ia()}function at(n){const a=c("#leadCalendar");!a||a.contains(n.target)||de()}function fn(){c("#leadMonthPrev")&&(c("#leadMonthPrev").onclick=()=>{de(),et(-1)}),c("#leadMonthNext")&&(c("#leadMonthNext").onclick=()=>{de(),et(1)}),c("#leadCalToggle")&&(c("#leadCalToggle").onclick=n=>{n.stopPropagation();const a=c("#leadCalPanel");a&&a.classList.contains("hidden")?bn():de()}),c("#leadCalYearPrev")&&(c("#leadCalYearPrev").onclick=n=>{n.stopPropagation(),o.leadCalYear=(o.leadCalYear||new Date().getFullYear())-1,ia()}),c("#leadCalYearNext")&&(c("#leadCalYearNext").onclick=n=>{n.stopPropagation(),o.leadCalYear=(o.leadCalYear||new Date().getFullYear())+1,ia()}),c("#leadMonthThis")&&(c("#leadMonthThis").onclick=n=>{n.stopPropagation(),o.leadMonth=Oe(),o.leadPage=1,de(),Fe(),E()}),c("#leadCalAll")&&(c("#leadCalAll").onclick=n=>{n.stopPropagation(),o.leadMonth="all",o.leadPage=1,de(),Fe(),E()}),document.removeEventListener("click",at),document.addEventListener("click",at)}function gn(){const n=!!v.canCreateLead,a=hn();j.innerHTML=`${be(e("workspace.title"),e("workspace.subtitle"),a)}
  <section class="lead-kpi-section" aria-labelledby="leadKpiTitle">
    <div class="lead-kpi-section__head">
      <div><span class="lead-kpi-section__eyebrow">${t(e("workspace.kpi_eyebrow"))}</span><h2 id="leadKpiTitle">${t(e("workspace.kpi_heading"))}</h2></div>
      <span class="lead-kpi-section__scope">${t(e("workspace.kpi_scope",{period:pa(o.leadMonth)}))}</span>
    </div>
    <div class="grid kpi-grid" id="leadKpiMount"></div>
    <p class="card-subtitle">${t(e("workspace.kpi_note"))}</p>
  </section>
  <section class="lead-panel card" style="margin-top:14px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${t(e("workspace.list_title"))}</h2>
        <p class="lead-panel__sub">${t(e("workspace.list_subtitle"))}</p>
      </div>
      <div class="lead-panel__head-meta">
        <span class="lead-panel__count" id="leadResultCount">—</span>
        ${n?`<button class="btn primary" type="button" id="addLeadBtn">${t(e("workspace.add_lead"))}</button>`:""}
      </div>
    </div>
    <div class="lead-panel__filters">
      <label class="lead-search" for="leadSearch">
        <span class="lead-search__icon" aria-hidden="true">⌕</span>
        <input class="lead-search__input" id="leadSearch" type="search" autocomplete="off" placeholder="${t(e("workspace.search_placeholder"))}" value="${t(o.leadSearch)}" />
      </label>
      <div class="lead-filter-grid">
        <label class="lead-field">
          <span class="lead-field__label">${t(e("workspace.filter_status"))}</span>
          <select class="lead-field__control" id="leadStatus"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${t(e("workspace.filter_beautician"))}</span>
          <select class="lead-field__control" id="leadBeautician"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${t(e("workspace.filter_branch"))}</span>
          <select class="lead-field__control" id="leadBranchFilter"></select>
        </label>
      </div>
    </div>
    <div class="lead-panel__chips" id="leadActiveFilters" hidden></div>
    <div class="lead-bulk-bar" id="leadBulkBar" hidden aria-live="polite"></div>
    <div class="lead-panel__body" id="leadTableMount"></div>
  </section>`,Lt(),la(),fn(),ie(),c("#addLeadBtn")&&(c("#addLeadBtn").onclick=Pt),E()}function Lt(){const n=c("#leadKpiMount");if(!n)return;const a=Sa||{},s=a.all_time||a,i=Number(a.raw||0),l=Number(a.unique||0),r=Number(a.duplicates||0),d=Number(a.existing||0),p=Number(a.converted||0),u=Number(a.conversion_pct||0),m=Number(s.raw||0),_=Number(s.unique||0),b=Number(s.duplicates||0),k=Number(s.existing||0),$=Number(s.converted||0),f=Number(s.conversion_pct||0),w=m>0?(_/m*100).toFixed(1):"0.0",M=m>0?(b/m*100).toFixed(1):"0.0",y=pa(o.leadMonth);n.innerHTML=`
    ${I(U("database"),e("workspace.total_leads_database"),h(m),e("workspace.unit_lead_records"),e("workspace.all_time"),e("workspace.period_added",{period:y,count:h(i)}),"blue")}
    ${I(U("new"),e("workspace.new_leads"),h(l),e("workspace.unit_new_leads"),y,e("workspace.all_time_unique",{count:h(_),pct:w}),"green")}
    ${I(U("repeated"),e("workspace.repeated_leads"),h(b),e("workspace.unit_repeated_records"),e("workspace.all_time"),e("workspace.period_repeated",{period:y,count:h(r),pct:M}),"rose")}
    ${I(U("customers"),e("workspace.existing_customers"),h(k),e("workspace.unit_registered_customers"),e("workspace.matched_phone"),e("workspace.period_matched",{period:y,count:h(d)}),"purple")}
    ${I(U("conversion"),e("workspace.conversion"),G(f),e("workspace.unit_conversion_rate"),e("workspace.all_time"),e("workspace.period_conversion",{period:y,pct:G(u),count:h(p),total:h($)}),"teal")}
  `}function la(){const n=c("#leadStatus"),a=c("#leadBeautician"),s=c("#leadBranchFilter");if(n){const l=[{value:"all",label:e("workspace.all_status")},...P.statuses||[]];n.innerHTML=l.map(r=>`<option value="${t(r.value)}" ${String(o.leadStatus)===String(r.value)?"selected":""}>${t(r.label)}</option>`).join(""),n.onchange=r=>{o.leadStatus=r.target.value,o.leadPage=1,E()}}if(a){const l=[{id:"all",name:e("workspace.all_beauticians")},...P.beauticians||[]];a.innerHTML=l.map(r=>`<option value="${t(r.id)}" ${String(o.leadBeautician)===String(r.id)?"selected":""}>${t(r.name)}</option>`).join(""),a.onchange=r=>{o.leadBeautician=r.target.value,o.leadPage=1,E()}}if(s){const l=[{id:"all",name:e("workspace.all_branches")},...P.branches||v.branches||[]];s.innerHTML=l.map(r=>`<option value="${t(r.id)}" ${String(o.leadBranch)===String(r.id)?"selected":""}>${t(r.name)}</option>`).join(""),s.onchange=r=>{o.leadBranch=r.target.value,o.leadPage=1,E()}}const i=c("#leadSearch");i&&(i.oninput=l=>{o.leadSearch=l.target.value,clearTimeout(Wa),Wa=setTimeout(()=>{o.leadPage=1,E()},350)}),xt()}function fa(n,a){if(n==="status"){const s=(P.statuses||[]).find(i=>String(i.value)===String(a));return s?s.label:a}if(n==="beautician"){const s=(P.beauticians||[]).find(i=>String(i.id)===String(a));return s?s.name:a}if(n==="branch"){const i=(P.branches||v.branches||[]).find(l=>String(l.id)===String(a));return i?i.name:a}return a}function xt(){const n=c("#leadResultCount"),a=Number(Pe.total??(Array.isArray(Q)?Q.length:0));n&&(n.textContent=a===1?e("workspace.results_count_one"):e("workspace.results_count",{count:h(a)}));const s=c("#leadActiveFilters");if(!s)return;const i=[];if(o.leadSearch&&String(o.leadSearch).trim()&&i.push({key:"q",label:`“${String(o.leadSearch).trim()}”`}),o.leadStatus&&o.leadStatus!=="all"&&i.push({key:"status",label:fa("status",o.leadStatus)}),o.leadBeautician&&o.leadBeautician!=="all"&&i.push({key:"beautician",label:fa("beautician",o.leadBeautician)}),o.leadBranch&&o.leadBranch!=="all"&&i.push({key:"branch",label:fa("branch",o.leadBranch)}),!i.length){s.hidden=!0,s.innerHTML="";return}s.hidden=!1,s.innerHTML=`
    <div class="lead-chips">
      ${i.map(l=>`<span class="lead-chip">${t(l.label)}<button type="button" class="lead-chip__x" data-clear-filter="${t(l.key)}" aria-label="${t(e("workspace.clear_filters"))}">×</button></span>`).join("")}
      <button type="button" class="lead-chips__clear" id="leadClearFilters">${t(e("workspace.clear_filters"))}</button>
    </div>
  `,S("[data-clear-filter]",s).forEach(l=>{l.onclick=()=>{const r=l.dataset.clearFilter;r==="q"&&(o.leadSearch=""),r==="status"&&(o.leadStatus="all"),r==="beautician"&&(o.leadBeautician="all"),r==="branch"&&(o.leadBranch="all"),o.leadPage=1;const d=c("#leadSearch");d&&r==="q"&&(d.value=""),la(),E()}}),c("#leadClearFilters")&&(c("#leadClearFilters").onclick=()=>{o.leadSearch="",o.leadStatus="all",o.leadBeautician="all",o.leadBranch="all",o.leadPage=1;const l=c("#leadSearch");l&&(l.value=""),la(),E()})}function yn(n){return n==="status"?(P.statuses||[]).map(a=>`<option value="${t(a.value)}">${t(a.label)}</option>`).join(""):n==="source"?(P.sources||[]).map(a=>`<option value="${t(a.value)}">${t(a.label)}</option>`).join(""):n==="beautician_id"?`<option value="__none__">${t(e("workspace.bulk_unassigned"))}</option>${(P.beauticians||[]).map(a=>`<option value="${t(a.id)}">${t(a.name)}</option>`).join("")}`:n==="spa_branch_id"?`<option value="__none__">${t(e("workspace.bulk_unassigned"))}</option>${(P.branches||v.branches||[]).map(a=>`<option value="${t(a.id)}">${t(a.name)}</option>`).join("")}`:""}function ra(){const n=c("#leadBulkBar");if(!n)return;const a=O.size;if(!a){n.hidden=!0,n.innerHTML="",W="",Y="";return}const s=[];v.canEditLead&&s.push(["status",e("workspace.bulk_update_status")],["source",e("workspace.bulk_update_source")],["created_at",e("workspace.bulk_update_date")],["beautician_id",e("workspace.bulk_assign_beautician")],["spa_branch_id",e("workspace.bulk_assign_branch")]),v.canDeleteLead&&s.push(["delete",e("workspace.bulk_delete")]),s.some(([d])=>d===W)||(W="",Y="");const i=["status","source","created_at","beautician_id","spa_branch_id"].includes(W);n.hidden=!1,n.innerHTML=`
    <div class="lead-bulk-bar__summary"><strong>${t(e("workspace.bulk_selected",{count:h(a)}))}</strong></div>
    <div class="lead-bulk-bar__controls">
      <label class="sr-only" for="leadBulkAction">${t(e("workspace.bulk_action"))}</label>
      <select class="lead-bulk-control" id="leadBulkAction">
        <option value="">${t(e("workspace.bulk_choose_action"))}</option>
        ${s.map(([d,p])=>`<option value="${t(d)}">${t(p)}</option>`).join("")}
      </select>
      ${W==="created_at"?`<label class="sr-only" for="leadBulkValue">${t(e("workspace.bulk_choose_date"))}</label>
      <input class="lead-bulk-control lead-bulk-date" id="leadBulkValue" type="date" max="${vn()}" value="${t(Y)}" aria-label="${t(e("workspace.bulk_choose_date"))}">`:i?`<label class="sr-only" for="leadBulkValue">${t(e("workspace.bulk_choose_value"))}</label>
      <select class="lead-bulk-control" id="leadBulkValue">
        <option value="">${t(e("workspace.bulk_choose_value"))}</option>
        ${yn(W)}
      </select>`:""}
      <button type="button" class="btn ${W==="delete"?"danger":"primary"} lead-bulk-apply" id="leadBulkApply" ${!W||i&&!Y?"disabled":""}>${t(e("workspace.bulk_apply"))}</button>
      <button type="button" class="btn lead-bulk-clear" id="leadBulkClear">${t(e("workspace.bulk_clear"))}</button>
    </div>`;const l=c("#leadBulkAction");l.value=W,l.onchange=d=>{W=d.target.value,Y="",ra()};const r=c("#leadBulkValue");r&&(r.value=Y,r.onchange=d=>{Y=d.target.value,ra()}),c("#leadBulkApply").onclick=kn,c("#leadBulkClear").onclick=()=>{O.clear(),W="",Y="",Qe()}}function Qe(){const n=Q.map(i=>String(i.id)),a=n.filter(i=>O.has(i)).length,s=c("#leadSelectAll");s&&(s.checked=n.length>0&&a===n.length,s.indeterminate=a>0&&a<n.length),S("[data-lead-select]").forEach(i=>{const l=O.has(String(i.value));i.checked=l,i.closest("tr")?.classList.toggle("is-selected",l)}),ra()}async function $n(n,a,s){const i=v.leadBulkUpdateUrl||"";if(!i){g(e("workspace.bulk_update_error"));return}s.disabled=!0;try{const l=await fetch(i,{method:"PATCH",headers:q(!0),credentials:"same-origin",body:JSON.stringify({ids:[...O].map(Number),field:n,value:a})}),r=await l.json().catch(()=>({}));if(!l.ok){g(r.message||e("workspace.bulk_update_error"));return}g(r.message||e("workspace.bulk_updated",{count:O.size})),await E()}catch(l){console.error(l),g(e("workspace.bulk_update_error"))}finally{s.isConnected&&(s.disabled=!1)}}async function wn(n){const a=v.leadBulkDeleteUrl||"";if(!a){g(e("workspace.bulk_delete_error"));return}n.disabled=!0;try{const s=await fetch(a,{method:"DELETE",headers:q(!0),credentials:"same-origin",body:JSON.stringify({ids:[...O].map(Number)})}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("workspace.bulk_delete_error"));return}Number(i.deleted||0)>=Q.length&&o.leadPage>1&&o.leadPage--,A(),g(i.message||e("workspace.bulk_deleted",{count:O.size})),await E()}catch(s){console.error(s),g(e("workspace.bulk_delete_error"))}finally{n.isConnected&&(n.disabled=!1)}}function kn(){const n=O.size;if(!n){g(e("workspace.bulk_nothing_selected"));return}const a=W,s=c("#leadBulkApply");if(!a||!s)return;if(a==="delete"){z(e("workspace.bulk_delete"),e("workspace.bulk_delete_confirm",{count:h(n)}),"",`<button type="button" class="btn" data-action-drawer-close>${t(e("workspace.cancel"))}</button><button type="button" class="btn danger" id="confirmBulkDeleteLead">${t(e("workspace.bulk_delete"))}</button>`,e("workspace.bulk_action")),c("#confirmBulkDeleteLead").onclick=l=>wn(l.currentTarget);return}if(!Y){c("#leadBulkValue")?.focus();return}const i=Y==="__none__"?null:["beautician_id","spa_branch_id"].includes(a)?Number(Y):Y;$n(a,i,s)}function tt(){const n=c("#leadTableMount");if(!n)return;if(xt(),ea){n.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${t(e("workspace.loading"))}</strong></div>`;return}const a=Q;if(!a.length){const d=!!v.canCreateLead;n.innerHTML=`<div class="lead-empty">
      <div class="lead-empty__icon" aria-hidden="true">◎</div>
      <strong>${t(e("workspace.no_leads"))}</strong>
      <p>${t(e("workspace.no_leads_hint"))}</p>
      ${d?`<button type="button" class="btn primary" id="emptyAddLeadBtn">${t(e("workspace.empty_cta"))}</button>`:""}
    </div>`,c("#emptyAddLeadBtn")&&(c("#emptyAddLeadBtn").onclick=Pt);return}const s=!!(v.canEditLead||v.canDeleteLead);n.innerHTML=`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    ${s?`<th class="lead-table__select"><input class="lead-select-box" type="checkbox" id="leadSelectAll" aria-label="${t(e("workspace.bulk_select_all"))}"></th>`:""}
    <th class="lead-col--id">${t(e("workspace.col_lead_id"))}</th><th class="lead-col--date">${t(e("workspace.col_date"))}</th><th class="lead-col--customer">${t(e("workspace.col_customer"))}</th>
    <th class="lead-col--phone">${t(e("workspace.col_phone"))}</th><th class="lead-col--email">${t(e("workspace.col_email"))}</th><th class="lead-col--source">${t(e("workspace.col_source"))}</th>
    <th class="lead-col--beautician">${t(e("workspace.col_beautician"))}</th><th class="lead-col--branch">${t(e("workspace.col_branch"))}</th><th>${t(e("workspace.col_status"))}</th>
    <th class="lead-col--payment">${t(e("workspace.col_payment"))}</th><th class="is-num lead-col--sales">${t(e("workspace.col_sales"))}</th><th class="lead-col--follow-up" title="${t(e("workspace.col_last_fu"))}">${t(e("workspace.col_last_fu"))}</th>
    <th class="lead-table__actions"><span class="sr-only">${t(e("workspace.col_action"))}</span></th>
  </tr></thead><tbody>${a.map(d=>{const p=String(d.name||""),u=t((p[0]||"?").toUpperCase()),m=!!v.canEditLead,_=!!v.canDeleteLead,b=String(d.email||"").trim(),k=String(d.source||"").trim(),$=String(d.beautician||"").trim(),f=String(d.branch||"").trim(),w=String(d.last||"").trim(),M=Number(d.sales||0);return`<tr data-payment-status="${t(d.payment_status||"pending")}">
      ${s?`<td class="lead-table__select"><input class="lead-select-box" type="checkbox" value="${t(d.id)}" data-lead-select aria-label="${t(e("workspace.bulk_select_lead",{name:p||d.code||d.id}))}"></td>`:""}
      <td class="lead-col--id"><span class="lead-code">${t(d.code||d.id)}</span></td>
      <td class="lead-col--date"><span class="lead-date">${t(d.date||"—")}</span></td>
      <td>
        <div class="person-cell person-cell--lead">
          <div class="mini-avatar" aria-hidden="true">${u}</div>
          <div class="person-cell__text">
            <strong>${t(p||"—")}</strong>
            ${d.customer?`<small>${t(d.customer)}</small>`:""}
          </div>
        </div>
      </td>
      <td><span class="lead-mono">${t(d.phone||"—")}</span></td>
      <td class="lead-col--email">${b?`<span class="lead-email" title="${t(b)}">${t(b)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td class="lead-col--source">${k?`<span class="lead-tag">${t(k)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td class="lead-col--beautician">${$?t($):'<span class="lead-muted">—</span>'}</td>
      <td class="lead-col--branch">${f?`<span class="lead-branch">${t(f)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td>${x(d.status)}</td>
      <td class="lead-col--payment">${x(d.payment)}</td>
      <td class="is-num"><span class="lead-money${M?"":" is-zero"}">${M?R(M):"RM0"}</span></td>
      <td class="lead-col--follow-up"><span class="lead-date">${w?t(w):"—"}</span></td>
      <td class="lead-table__actions">
        <div class="lead-menu">
          <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${t(e("workspace.row_actions"))}">
            <span class="lead-menu__dots" aria-hidden="true"></span>
          </button>
          <div class="lead-menu__panel" role="menu" hidden>
            <button type="button" class="lead-menu__item" role="menuitem" data-lead-id="${t(d.id)}">${t(e("workspace.view"))}</button>
            ${m?`<button type="button" class="lead-menu__item" role="menuitem" data-lead-edit="${t(d.id)}">${t(e("workspace.edit"))}</button>`:""}
            ${_?`<button type="button" class="lead-menu__item lead-menu__item--danger" role="menuitem" data-lead-del="${t(d.id)}">${t(e("workspace.delete"))}</button>`:""}
          </div>
        </div>
      </td>
    </tr>`}).join("")}</tbody></table></div>`;const i=[10,50,100,200];n.insertAdjacentHTML("beforeend",`<div class="lead-pagination-bar">
    <label class="lead-page-size" for="leadPerPage">
      <span>${t(e("workspace.rows_per_page"))}</span>
      <select id="leadPerPage" class="lead-page-size__select">
        ${i.map(d=>`<option value="${d}" ${Number(o.leadPerPage)===d?"selected":""}>${d}</option>`).join("")}
      </select>
    </label>
    ${ne("leads",Pe)}
  </div>`);const l=c("#leadPerPage",n);l&&(l.onchange=()=>{o.leadPerPage=Number(l.value)||10,o.leadPage=1,l.disabled=!0,E()}),se("leads",d=>(o.leadPage=d,E()));const r=c("#leadSelectAll",n);r&&(r.onchange=()=>{Q.forEach(d=>r.checked?O.add(String(d.id)):O.delete(String(d.id))),Qe()}),S("[data-lead-select]",n).forEach(d=>d.onchange=()=>{d.checked?O.add(String(d.value)):O.delete(String(d.value)),Qe()}),Qe(),Ve(n),S("[data-lead-id]",n).forEach(d=>d.onclick=()=>{H(),ua(d.dataset.leadId)}),S("[data-lead-edit]",n).forEach(d=>d.onclick=()=>{H(),jt(d.dataset.leadEdit)}),S("[data-lead-del]",n).forEach(d=>d.onclick=()=>{H(),At(d.dataset.leadDel)})}function Bt(n){n&&(n.classList.remove("is-up"),n.style.top="",n.style.left="",n.style.right="",n.style.bottom="")}function Sn(n,a){if(!n||!a)return;const s=4,i=n.getBoundingClientRect();a.style.top="0px",a.style.left="0px",a.style.right="auto",a.style.bottom="auto";const l=a.getBoundingClientRect(),d=window.innerHeight-i.bottom<l.height+s+8;a.classList.toggle("is-up",d);let p=d?i.top-l.height-s:i.bottom+s,u=i.right-l.width;u=Math.max(8,Math.min(u,window.innerWidth-l.width-8)),p=Math.max(8,Math.min(p,window.innerHeight-l.height-8)),a.style.top=`${Math.round(p)}px`,a.style.left=`${Math.round(u)}px`}function H(n=null){S(".lead-menu").forEach(a=>{if(n&&a===n)return;const s=c(".lead-menu__btn",a),i=c(".lead-menu__panel",a);s&&s.setAttribute("aria-expanded","false"),i&&(i.hidden=!0,Bt(i)),a.classList.remove("is-open")})}function nt(n){n.target.closest&&n.target.closest(".lead-menu")||H()}function st(n){n.key==="Escape"&&H()}function Ge(){H()}function Ve(n){S("[data-lead-menu]",n).forEach(a=>{a.onclick=s=>{s.stopPropagation();const i=a.closest(".lead-menu"),l=c(".lead-menu__panel",i),r=a.getAttribute("aria-expanded")==="true";H(r?null:i),r?(a.setAttribute("aria-expanded","false"),l&&(l.hidden=!0,Bt(l)),i.classList.remove("is-open")):(a.setAttribute("aria-expanded","true"),l&&(l.hidden=!1,Sn(a,l)),i.classList.add("is-open"))}}),document.removeEventListener("click",nt),document.addEventListener("click",nt),document.removeEventListener("keydown",st),document.addEventListener("keydown",st),window.removeEventListener("scroll",Ge,!0),window.addEventListener("scroll",Ge,!0),window.removeEventListener("resize",Ge),window.addEventListener("resize",Ge)}function Pt(){Ce=null,z(e("workspace.manual_entry"),e("workspace.manual_sub"),Et({}),`<button class="btn" type="button" data-action-drawer-close>${t(e("workspace.cancel"))}</button><button class="btn primary" type="button" id="saveLead">${t(e("workspace.save"))}</button>`,e("workspace.title"))}function jt(n){const a=Q.find(s=>String(s.id)===String(n));if(!a){ua(n);return}Ce=a.id,z(e("workspace.edit_entry"),e("workspace.edit_sub"),Et(a),`<button class="btn" type="button" data-action-drawer-close>${t(e("workspace.cancel"))}</button><button class="btn primary" type="button" id="saveLead">${t(e("workspace.save"))}</button>`,e("workspace.title"))}function Et(n={}){const a=(P.statuses||[]).map(r=>`<option value="${t(r.value)}" ${String(n.status_key||"")===String(r.value)?"selected":""}>${t(r.label)}</option>`).join(""),s=[{id:"",name:"—"},...P.branches||[]].map(r=>`<option value="${t(r.id)}" ${String(n.branch_id||"")===String(r.id)?"selected":""}>${t(r.name)}</option>`).join(""),i=[{id:"",name:"—"},...P.beauticians||[]].map(r=>`<option value="${t(r.id)}" ${String(n.beautician_id||"")===String(r.id)?"selected":""}>${t(r.name)}</option>`).join(""),l=String(n.source||"manual");return`<div class="detail-grid">
      <div><label class="kpi-label">${t(e("workspace.name"))}</label><input class="search" style="width:100%" id="mName" autocomplete="name" value="${t(n.name||"")}"></div>
      <div><label class="kpi-label">${t(e("workspace.phone"))}</label><input class="search" style="width:100%" id="mPhone" autocomplete="tel" value="${t(n.phone||"")}"></div>
      <div style="grid-column:1/-1"><label class="kpi-label">${t(e("workspace.email"))}</label><input class="search" style="width:100%" id="mEmail" autocomplete="email" value="${t(n.email||"")}"></div>
      <div><label class="kpi-label">${t(e("workspace.source"))}</label>
        <select class="control" style="width:100%" id="mSource">
          ${["manual","TikTok","WhatsApp","Facebook","import"].map(r=>`<option value="${r}" ${l===r?"selected":""}>${r}</option>`).join("")}
        </select>
      </div>
      <div><label class="kpi-label">${t(e("workspace.col_status"))}</label>
        <select class="control" style="width:100%" id="mStatus">${a||'<option value="new">NEW</option>'}</select>
      </div>
      <div><label class="kpi-label">${t(e("workspace.col_branch"))}</label>
        <select class="control" style="width:100%" id="mBranch">${s}</select>
      </div>
      <div><label class="kpi-label">${t(e("workspace.col_beautician"))}</label>
        <select class="control" style="width:100%" id="mBeautician">${i}</select>
      </div>
    </div>`}async function Mn(){const n=Ce!=null,a=n?N(v.leadUpdateUrlTemplate,Ce):v.leadStoreUrl;if(!a){g(e("workspace.save_error"));return}const s={name:(c("#mName")?.value||"").trim(),phone:(c("#mPhone")?.value||"").trim(),email:(c("#mEmail")?.value||"").trim()||null,source:c("#mSource")?.value||"manual",status:c("#mStatus")?.value||void 0,spa_branch_id:c("#mBranch")?.value?Number(c("#mBranch").value):null,beautician_id:c("#mBeautician")?.value?Number(c("#mBeautician").value):null};if(!s.name||!s.phone){g(e("workspace.save_error"));return}try{const i=await fetch(a,{method:n?"PUT":"POST",headers:q(!0),credentials:"same-origin",body:JSON.stringify(s)}),l=await i.json().catch(()=>({}));if(!i.ok){const r=l&&(l.message||Object.values(l.errors||{})[0]?.[0])||e(n?"workspace.update_error":"workspace.save_error");g(r);return}A(),Ce=null,g(l.message||e(n?"workspace.updated":"workspace.saved")),n||(o.leadPage=1),await E()}catch(i){console.error(i),g(e(n?"workspace.update_error":"workspace.save_error"))}}function At(n){v.canDeleteLead&&(z(e("workspace.delete"),e("workspace.delete_confirm"),"",`<button type="button" class="btn" data-action-drawer-close>${t(e("workspace.cancel"))}</button><button type="button" class="btn danger" id="confirmDeleteLead">${t(e("workspace.delete"))}</button>`,e("nav.leads")),c("#confirmDeleteLead").onclick=async a=>{const s=a.currentTarget;s.disabled=!0,await Tn(n),s.disabled=!1})}async function Tn(n){if(!v.canDeleteLead)return;const a=N(v.leadDestroyUrlTemplate,n);if(!a){g(e("workspace.delete_error"));return}try{const s=await fetch(a,{method:"DELETE",headers:q(!1),credentials:"same-origin"}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("workspace.delete_error"));return}g(i.message||e("workspace.deleted")),A(),Z(),await E()}catch(s){console.error(s),g(e("workspace.delete_error"))}}async function ua(n){let s=Q.find(_=>String(_.id)===String(n))||Le.find(_=>String(_.id)===String(n));const i=v.leadShowUrlTemplate;if(i)try{const _=await fetch(N(i,n),{headers:q(!1),credentials:"same-origin"});if(_.ok){const b=await _.json();s=b.data||s,b.filters?.statuses&&(P.statuses=b.filters.statuses)}}catch(_){console.error(_)}if(!s)return;A(),Z(),je=document.activeElement,Fa=document.body.style.overflow,document.body.style.overflow="hidden";const l=c("#leadDrawer .eyebrow");l&&(l.textContent=e("workspace.drawer_eyebrow")),c("#drawerName").textContent=s.name||"";const r=Cn(s),d=(P.statuses||[]).map(_=>`<option value="${t(_.value)}" ${String(s.status_key)===String(_.value)?"selected":""}>${t(_.label)}</option>`).join(""),p=!!v.canEditLead,u=String(s.name||""),m=t((u[0]||"?").toUpperCase());c("#drawerBody").innerHTML=`
  <div class="journey">
    <div class="journey-hero">
      <div class="journey-hero__avatar" aria-hidden="true">${m}</div>
      <div class="journey-hero__meta">
        <div class="journey-hero__code">${t(s.code||s.id)}</div>
        <div class="journey-hero__badges">
          ${x(s.status)}
          <span class="journey-pill journey-pill--${t(r.healthTone)}">${t(r.healthLabel)}</span>
          <span class="journey-pill journey-pill--prio-${t(r.priorityTone)}">${t(r.priorityLabel)}</span>
        </div>
      </div>
    </div>

    <section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.drawer_analytics"))}</div>
      <div class="journey-stats">
        <div class="journey-stat">
          <span class="journey-stat__label">${t(e("workspace.drawer_health"))}</span>
          <strong class="journey-stat__value">${r.score}</strong>
          <div class="journey-meter"><span style="width:${r.score}%"></span></div>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${t(e("workspace.drawer_stage"))}</span>
          <strong class="journey-stat__value">${r.stagePct}%</strong>
          <div class="journey-meter journey-meter--stage"><span style="width:${r.stagePct}%"></span></div>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${t(e("workspace.drawer_pipeline_days"))}</span>
          <strong class="journey-stat__value">${t(e("workspace.drawer_days",{count:r.daysInPipeline}))}</strong>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${t(e("workspace.drawer_since_contact"))}</span>
          <strong class="journey-stat__value">${r.neverContacted?t(e("workspace.drawer_never_contacted")):t(e("workspace.drawer_days",{count:r.daysSinceContact}))}</strong>
        </div>
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.drawer_signals"))}</div>
      <div class="journey-signals">
        ${r.signals.map(_=>`<span class="journey-signal journey-signal--${t(_.tone)}">${t(_.label)}</span>`).join("")||`<span class="journey-signal journey-signal--ok">${t(e("workspace.drawer_signal_healthy"))}</span>`}
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.drawer_pipeline"))}</div>
      <div class="journey-pipeline" role="list">${r.pipelineHtml}</div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.drawer_contact"))}</div>
      <div class="journey-kv">
        <div><span>${t(e("workspace.col_phone"))}</span><strong>${t(s.phone||"—")}</strong></div>
        <div><span>${t(e("workspace.col_email"))}</span><strong title="${t(s.email||"")}">${t(s.email||"—")}</strong></div>
        <div><span>${t(e("workspace.drawer_source_label"))}</span><strong>${t(s.source||"—")}</strong></div>
        <div><span>${t(e("workspace.col_customer"))}</span><strong>${t(s.customer||"—")}</strong></div>
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.drawer_assignment"))}</div>
      <div class="journey-kv">
        <div><span>${t(e("workspace.col_beautician"))}</span><strong>${t(s.beautician||"—")}</strong></div>
        <div><span>${t(e("workspace.col_branch"))}</span><strong>${t(s.branch||"—")}</strong></div>
        <div><span>${t(e("workspace.col_last_fu"))}</span><strong>${t(s.last||"—")}</strong></div>
        <div><span>${t(e("workspace.col_date"))}</span><strong>${t(s.date||"—")}</strong></div>
      </div>
    </section>

    ${p?`<section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.update_status"))}</div>
      <div class="journey-status-row">
        <select class="control" id="drawerStatus">${d}</select>
        <button class="btn primary" type="button" id="drawerSaveStatus">${t(e("workspace.update_status"))}</button>
      </div>
    </section>`:""}

    <div class="journey-actions">
      <div class="journey-section__title">${t(e("workspace.drawer_actions"))}</div>
      <div class="journey-actions__row">
        ${p?`<button class="btn primary" type="button" id="drawerFollowBtn">${t(e("followup.mark"))}</button>`:""}
        ${p?`<button class="btn" type="button" id="drawerEditBtn">${t(e("workspace.edit"))}</button>`:""}
        ${v.canDeleteLead?`<button class="btn danger" type="button" id="drawerDeleteBtn">${t(e("workspace.delete"))}</button>`:""}
      </div>
    </div>
  </div>`,c("#leadDrawer").classList.add("show"),c("#drawerBackdrop").classList.add("show"),c("#leadDrawer").setAttribute("aria-hidden","false"),c("#leadDrawer").inert=!1,c(".app-shell").inert=!0,c("#drawerClose").focus({preventScroll:!0}),ie(),c("#drawerFollowBtn")&&(c("#drawerFollowBtn").onclick=()=>Yt(s.id)),c("#drawerEditBtn")&&(c("#drawerEditBtn").onclick=()=>{Z(),jt(s.id)}),c("#drawerDeleteBtn")&&(c("#drawerDeleteBtn").onclick=()=>At(s.id)),c("#drawerSaveStatus")&&(c("#drawerSaveStatus").onclick=async()=>{const _=c("#drawerStatus")?.value,b=N(v.leadStatusUrlTemplate,s.id);if(!_||!b){g(e("workspace.status_error"));return}try{const k=await fetch(b,{method:"PATCH",headers:q(!0),credentials:"same-origin",body:JSON.stringify({status:_})}),$=await k.json().catch(()=>({}));if(!k.ok){g($.message||e("workspace.status_error"));return}g($.message||e("workspace.status_updated")),o.view==="followup"?await ce():await E(),ua(s.id)}catch(k){console.error(k),g(e("workspace.status_error"))}})}function Cn(n){const a=["new","claimed","follow_up","booking","payment_verified","converted"],s=String(n.status_key||"new"),i=a.indexOf(s),l=s==="lost"||s==="no_response"?Math.max(10,Math.round((Math.max(i,0)+1)/a.length*100)):Math.round((Math.max(i,0)+1)/a.length*100),r=Number(n.days_in_pipeline!=null?n.days_in_pipeline:n.days_since_followup||0),d=!n.last_followed_up_at,p=Number(n.days_since_followup||0),u=String(n.followup_bucket)==="overdue"||(d?r>=2:p>=2);let m=28;s==="converted"?m=96:s==="payment_verified"?m=82:s==="booking"?m=68:s==="follow_up"?m=54:s==="claimed"?m=42:s==="new"?m=32:s==="no_response"?m=22:s==="lost"&&(m=12),n.existing&&(m+=8),n.duplicate&&(m-=6),u&&(m-=18),!d&&p===0&&(m+=6),n.beautician_id&&(m+=4),n.branch_id&&(m+=3),m=Math.max(5,Math.min(99,m));let _="warm",b=e("workspace.drawer_health_warm");m>=75?(_="hot",b=e("workspace.drawer_health_hot")):m<35||u||s==="lost"||s==="no_response"?(_="risk",b=e("workspace.drawer_health_risk")):m<50&&(_="cold",b=e("workspace.drawer_health_cold"));let k="med",$=e("workspace.drawer_priority_medium");u||s==="no_response"||!n.beautician_id&&r>=1?(k="high",$=e("workspace.drawer_priority_high")):(s==="converted"||s==="payment_verified")&&(k="low",$=e("workspace.drawer_priority_low"));const f=[];u&&f.push({tone:"danger",label:e("workspace.drawer_signal_overdue")}),r<=1&&s==="new"&&f.push({tone:"info",label:e("workspace.drawer_signal_fresh")}),n.existing&&f.push({tone:"ok",label:e("workspace.drawer_signal_existing")}),n.duplicate&&f.push({tone:"warn",label:e("workspace.drawer_signal_duplicate")}),!n.beautician_id&&s!=="converted"&&f.push({tone:"warn",label:e("workspace.drawer_signal_unassigned")}),!n.branch_id&&s!=="converted"&&f.push({tone:"warn",label:e("workspace.drawer_signal_no_branch")}),f.length||f.push({tone:"ok",label:e("workspace.drawer_signal_healthy")});const w=a.map((M,y)=>{const T=(P.statuses||[]).find(K=>K.value===M)?.label||M.replace(/_/g," ");return`<div class="journey-step${i>y||s==="converted"?" is-done":i===y?" is-active":""}" role="listitem"><span class="journey-step__dot"></span><span class="journey-step__label">${t(T)}</span></div>`}).join("");return{score:m,stagePct:l,daysInPipeline:r,daysSinceContact:p,neverContacted:d,healthTone:_,healthLabel:b,priorityTone:k,priorityLabel:$,signals:f,pipelineHtml:w}}let F=null,ga=[],ya={total_imports:0,raw:0,unique:0,duplicates:0,existing:0,invalid:0,imported:0},ke=!1;function Ln(){j.innerHTML=`${be(e("import.title"),e("import.subtitle"),`<button type="button" class="btn" data-jump="imports">${t(e("import.history_btn"))}</button><button type="button" class="btn primary" data-jump="leads">${t(e("import.view_leads"))}</button>`)}
  <section class="card">
    <div class="tabs" id="importTabs" role="tablist">${[["paste",e("import.tab_paste")],["excel",e("import.tab_excel")],["csv",e("import.tab_csv")],["manual",e("import.tab_manual")]].map(n=>`<button type="button" class="tab ${o.importTab===n[0]?"active":""}" role="tab" aria-selected="${o.importTab===n[0]?"true":"false"}" data-tab="${n[0]}">${t(n[1])}</button>`).join("")}</div>
    <div id="importPane" style="margin-top:14px"></div>
  </section>
  <section class="card hidden" id="previewCard" style="margin-top:12px"></section>`,Nt(),ie(),xn()}function xn(){const n=S("[data-tab]","#importTabs");n.forEach(a=>{a.onclick=s=>{s.preventDefault();const i=a.dataset.tab;if(!i||i===o.importTab)return;o.importTab=i,n.forEach(r=>{const d=r===a;r.classList.toggle("active",d),r.setAttribute("aria-selected",d?"true":"false")}),F=null;const l=c("#previewCard");l&&l.classList.add("hidden"),Nt()}})}function Nt(){const n=c("#importPane");if(!n)return;const a=!!v.canCreateLead;if(o.importTab==="paste")n.innerHTML=`<div><div class="card-title">${t(e("import.paste_title"))}</div><div class="card-subtitle">${t(e("import.paste_sub"))}</div><textarea class="paste-area" id="pasteArea" placeholder="${t(e("import.paste_placeholder"))}"></textarea><div style="display:flex;justify-content:flex-end;margin-top:10px"><button type="button" class="btn primary" id="parseBtn" ${a?"":"disabled"}>${t(e("import.parse"))}</button></div></div>`,c("#parseBtn")&&(c("#parseBtn").onclick=()=>Bn());else if(o.importTab==="manual")n.innerHTML=`<div class="detail-grid"><div><label class="kpi-label" for="manualName">${t(e("import.name"))}</label><input class="search" style="width:100%" id="manualName" autocomplete="name"></div><div><label class="kpi-label" for="manualPhone">${t(e("import.phone"))}</label><input class="search" style="width:100%" id="manualPhone" inputmode="tel" autocomplete="tel"></div><div><label class="kpi-label" for="manualEmail">${t(e("import.email"))}</label><input class="search" style="width:100%" id="manualEmail" type="email" autocomplete="email"></div><div><label class="kpi-label" for="manualSource">${t(e("import.source"))}</label><select class="control" style="width:100%" id="manualSource"><option value="TikTok">TikTok</option><option value="WhatsApp">WhatsApp</option><option value="Facebook">Facebook</option><option value="manual">Import</option></select></div><div style="grid-column:1/-1;text-align:right"><button type="button" class="btn primary" id="manualSaveBtn" ${a?"":"disabled"}>${t(e("import.save_lead"))}</button></div></div>`,c("#manualSaveBtn")&&(c("#manualSaveBtn").onclick=Pn);else{const s=o.importTab==="excel";n.innerHTML=`<div class="import-zone" id="importDropZone" tabindex="0"><div class="import-icon">⇧</div><h3>${t(e(s?"import.drop_excel":"import.drop_csv"))}</h3><p>${t(e(s?"import.accepted_excel":"import.accepted_csv"))}</p><input type="file" id="fileInput" class="hidden" accept="${s?".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel":".csv,text/csv,text/plain"}"><button type="button" class="btn primary" id="browseBtn" ${a?"":"disabled"}>${t(e("import.browse"))}</button></div>`;const i=c("#fileInput"),l=c("#browseBtn"),r=c("#importDropZone");l&&i&&(l.onclick=d=>{d.preventDefault(),d.stopPropagation(),i.click()}),i&&(i.onchange=()=>it(i.files?.[0])),r&&a&&(r.addEventListener("click",d=>{d.target===l||l?.contains(d.target)||i?.click()}),["dragenter","dragover"].forEach(d=>r.addEventListener(d,p=>{p.preventDefault(),p.stopPropagation(),r.classList.add("is-dragover")})),["dragleave","drop"].forEach(d=>r.addEventListener(d,p=>{p.preventDefault(),p.stopPropagation(),r.classList.remove("is-dragover")})),r.addEventListener("drop",d=>{const p=d.dataTransfer?.files?.[0];p&&it(p)}),r.addEventListener("keydown",d=>{(d.key==="Enter"||d.key===" ")&&(d.preventDefault(),i?.click())}))}}async function Bn(){const n=c("#pasteArea")?.value||"";if(!n.trim()){g(e("import.paste_required"));return}await Ia({method:"paste",paste:n})}async function it(n){if(!n){g(e("import.file_required"));return}const a=new FormData;a.append("method",o.importTab==="excel"?"excel":"csv"),a.append("file",n),o.branch&&o.branch!=="all"&&a.append("spa_branch_id",o.branch),await Ia(a,!0)}async function Pn(){const n=(c("#manualName")?.value||"").trim(),a=(c("#manualPhone")?.value||"").trim(),s=(c("#manualEmail")?.value||"").trim(),i=(c("#manualSource")?.value||"manual").trim();if(!n||!a){g(e("import.rows_required"));return}await Ia({method:"manual",rows:[{name:n,phone:a,email:s||null,source:i}]})}async function Ia(n,a=!1){const s=v.importPreviewUrl;if(!s){g(e("import.preview_error"));return}if(!ke){ke=!0,g(e("import.parsing"));try{const i={method:"POST",credentials:"same-origin",headers:q(!a)};a?i.body=n:(o.branch&&o.branch!=="all"&&!n.spa_branch_id&&(n.spa_branch_id=Number(o.branch)||null),i.body=JSON.stringify(n));const l=await fetch(s,i),r=await l.json().catch(()=>({}));if(!l.ok){const d=r.message||Object.values(r.errors||{}).flat()[0]||e("import.preview_error");g(d);return}F=r.data||null,jn()}catch(i){console.error(i),g(e("import.preview_error"))}finally{ke=!1}}}function jn(){const n=c("#previewCard");if(!n||!F)return;const a=F.rows||[],s=F.summary||{},i=Number(s.ready||0)+Number(s.existing||0);n.classList.remove("hidden"),n.innerHTML=`<div class="card-title-row"><div><div class="card-title">${t(e("import.preview_title"))}</div><div class="card-subtitle">${t(e("import.preview_sub"))}</div></div><button class="btn small" type="button" id="closePreviewBtn">${t(e("import.close"))}</button></div>
  <div class="summary-strip">
    <div class="summary-chip"><label>${t(e("import.total_rows"))}</label><strong>${h(s.total||0)}</strong></div>
    <div class="summary-chip"><label>${t(e("import.ready"))}</label><strong>${h(s.ready||0)}</strong></div>
    <div class="summary-chip"><label>${t(e("import.duplicate"))}</label><strong>${h(s.duplicate||0)}</strong></div>
    <div class="summary-chip"><label>${t(e("import.existing"))}</label><strong>${h(s.existing||0)}</strong></div>
    <div class="summary-chip"><label>${t(e("import.invalid"))}</label><strong>${h(s.invalid||0)}</strong></div>
  </div>
  <div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("import.col_row"))}</th><th>${t(e("import.col_name"))}</th>
    <th title="${t(e("import.col_orig_phone"))}">${t(e("import.col_orig_phone"))}</th>
    <th title="${t(e("import.col_norm_phone"))}">${t(e("import.col_norm_phone"))}</th>
    <th>${t(e("import.col_email"))}</th><th>${t(e("import.col_detection"))}</th><th>${t(e("import.col_action"))}</th>
  </tr></thead><tbody>
  ${a.map(l=>`<tr><td>${l.row}</td><td><strong>${t(l.name||"")}</strong></td><td>${t(l.phone_orig||"")}</td><td>${t(l.phone_e164||l.phone_norm||"")}</td><td>${t(l.email||"")}</td><td>${x(l.detection)}</td><td>${l.detection==="READY"||l.detection==="EXISTING"?`<span class="badge success">${t(e("import.action_import"))}</span>`:`<span class="badge gray">${t(e("import.action_skip"))}</span>`}</td></tr>`).join("")||`<tr><td colspan="7"><div class="empty">${t(e("import.empty"))}</div></td></tr>`}
  </tbody></table></div>
  <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px"><button class="btn" type="button" id="cancelImportBtn">${t(e("import.cancel"))}</button><button class="btn primary" type="button" id="confirmImport" ${i>0?"":"disabled"}>${t(e("import.confirm",{count:i}))}</button></div>`,c("#closePreviewBtn")?.addEventListener("click",()=>n.classList.add("hidden")),c("#cancelImportBtn")?.addEventListener("click",()=>n.classList.add("hidden")),c("#confirmImport")?.addEventListener("click",En),n.scrollIntoView({behavior:"smooth",block:"start"})}async function En(){if(!F||ke)return;const n=v.importConfirmUrl;if(!n){g(e("import.confirm_error"));return}const a=(F.rows||[]).map(s=>({name:s.name,phone:s.phone_norm||s.phone_orig,email:s.email||null,source:s.source||F.source||"import",import:s.detection==="READY"||s.detection==="EXISTING"}));ke=!0,g(e("import.importing"));try{const s=await fetch(n,{method:"POST",credentials:"same-origin",headers:q(!0),body:JSON.stringify({method:F.method||"paste",rows:a,source:F.source||"import",file_name:F.file_name||null,spa_branch_id:F.spa_branch_id||null,beautician_id:F.beautician_id||null})}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("import.confirm_error"));return}g(i.message||e("import.imported",{count:i.data&&i.data.imported||0})),F=null,setTimeout(()=>V("leads"),700)}catch(s){console.error(s),g(e("import.confirm_error"))}finally{ke=!1}}async function An(){j.innerHTML=`${be(e("import.history_title"),e("import.history_subtitle"),`<button class="btn primary" data-jump="import">${t(e("import.new_import"))}</button>`)}
  <div class="grid kpi-grid" id="importKpiMount"></div>
  <section class="card" style="margin-top:12px"><div id="importHistoryMount"><div class="empty">${t(e("workspace.loading"))}</div></div></section>`,ie(),await qt()}async function qt(){const n=v.importHistoryUrl,a=c("#importHistoryMount"),s=c("#importKpiMount");if(!n){a&&(a.innerHTML=`<div class="empty">${t(e("import.load_error"))}</div>`);return}try{const i=await fetch(n+"?per_page=50&page="+Qa,{headers:q(!1),credentials:"same-origin"}),l=await i.json().catch(()=>({}));if(!i.ok){g(l.message||e("import.load_error"));return}ga=l.data||[],ya=l.meta?.summary||ya;const r=ya;if(s&&(s.innerHTML=`${X("▤",e("import.kpi_total"),h(r.total_imports),e("common.this_month"),e("import.meta_batches"),"blue")}${X("♙",e("import.kpi_raw"),h(r.raw),e("import.meta_historical"),"","rose")}${X("✓",e("import.kpi_unique"),h(r.unique),e("import.meta_after_clean"),"","green")}${X("⧉",e("import.kpi_duplicates"),h(r.duplicates),e("import.meta_auditable"),"","purple")}${X("♧",e("import.kpi_existing"),h(r.existing),e("import.meta_phone"),"","blue")}`),!a)return;if(!ga.length){a.innerHTML=`<div class="empty"><strong>${t(e("import.empty"))}</strong>${t(e("import.empty_hint"))}</div>`;return}a.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
      <th>${t(e("import.col_batch"))}</th><th>${t(e("import.col_date"))}</th>
      <th title="${t(e("import.col_by"))}">${t(e("import.col_by"))}</th>
      <th>${t(e("import.col_method"))}</th><th>${t(e("import.col_file"))}</th>
      <th>${t(e("import.col_raw"))}</th><th>${t(e("import.col_unique"))}</th>
      <th>${t(e("import.col_duplicate"))}</th><th>${t(e("import.col_existing"))}</th>
      <th>${t(e("import.col_invalid"))}</th><th>${t(e("import.col_status"))}</th>
    </tr></thead><tbody>
    ${ga.map(d=>`<tr>
      <td><strong>${t(d.batch_code||"")}</strong></td><td>${t(d.date||"")}</td>
      <td>${t(d.by||"")}</td><td>${t(d.method||"")}</td><td>${t(d.file||"")}</td>
      <td>${h(d.raw)}</td><td>${h(d.unique)}</td><td>${h(d.duplicate)}</td>
      <td>${h(d.existing)}</td><td>${h(d.invalid)}</td><td>${x(d.status)}</td>
    </tr>`).join("")}
    </tbody></table></div>${ne("imports",l.meta||{})}`,se("imports",d=>(Qa=d,qt()))}catch(i){console.error(i),a&&(a.innerHTML=`<div class="empty">${t(e("import.load_error"))}</div>`)}}function Dt(){const n=o.paymentCustomerId?`<div class="pay-customer-chip" id="payCustomerChip">
        <span>${t(e("payments.filtered_customer",{name:o.paymentCustomerLabel||"#"+o.paymentCustomerId}))}</span>
        <button type="button" class="btn small soft" id="payClearCustomer">${t(e("payments.clear_customer"))}</button>
      </div>`:"";j.innerHTML=`<div class="pay-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${t(e("payments.title"))}</h1>
        <div class="page-subtitle">${t(e("payments.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="payRefresh">${t(e("payments.refresh"))}</button>
        ${v.canViewOrder&&v.ordersIndexUrl?`<a class="btn primary" href="${t(v.ordersIndexUrl)}" target="_blank" rel="noopener">${t(e("payments.open_orders"))}</a>`:""}
      </div>
    </div>
    ${n}
    <section class="clearance-flow payment-flow" aria-labelledby="paymentFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CONTROL</span><h2 id="paymentFlowTitle">${t(e("payments.workflow_title"))}</h2><p>${t(e("payments.workflow_hint"))}</p></div>
      <ol class="clearance-flow__steps">${[e("payments.step_booked"),e("payments.step_verify"),e("payments.step_paid")].map((i,l)=>`<li><span aria-hidden="true">${l+1}</span><strong>${t(i)}</strong></li>`).join("")}</ol>
    </section>
    <div class="pay-metrics" id="payHeroStats"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro">
          <p class="lead-panel__sub" id="payHeroCopy" style="margin:0">${t(o.paymentCustomerId?e("payments.filtered_customer",{name:o.paymentCustomerLabel||"#"+o.paymentCustomerId}):e("payments.subtitle"))}</p>
        </div>
        <div class="lead-panel__head-meta">
          <span class="lead-panel__count" id="payResultCount">—</span>
        </div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="payTabs" role="tablist"></div>
        <label class="lead-search" for="paySearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="paySearch" type="search" autocomplete="off" placeholder="${t(e("payments.search_placeholder"))}" value="${t(o.paymentSearch||"")}" />
        </label>
        <div class="lead-filter-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
          <label class="lead-field">
            <span class="lead-field__label">${t(e("payments.filter_beautician"))}</span>
            <select class="lead-field__control" id="payBeautician"></select>
          </label>
          <label class="lead-field">
            <span class="lead-field__label">${t(e("payments.filter_branch"))}</span>
            <select class="lead-field__control" id="payBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="payTableMount"></div>
    </section>
  </div>`;const a=c("#payRefresh");a&&(a.onclick=()=>ae());const s=c("#payClearCustomer");s&&(s.onclick=()=>Nn()),qn(),ae()}function Nn(){o.paymentCustomerId=null,o.paymentCustomerLabel="",o.paymentSearch="",o.paymentPage=1,o.view==="payments"?Dt():ae()}function Ht(n){if(!n)return;const a=Number(n.id||0);a&&(o.paymentCustomerId=a,o.paymentCustomerLabel=String(n.name||n.code||"#"+a),o.paymentSearch=String(n.phone||n.email||n.name||"").trim(),o.paymentTab="all",o.paymentPage=1,o.paymentBeautician="all",V("payments"))}function qn(){const n=c("#paySearch");n&&(n.oninput=()=>{clearTimeout(Ka),Ka=setTimeout(()=>{o.paymentSearch=n.value.trim(),o.paymentPage=1,ae()},320)});const a=c("#payBeautician");a&&(a.onchange=()=>{o.paymentBeautician=a.value,o.paymentPage=1,ae()});const s=c("#payBranch");s&&(s.onchange=()=>{o.paymentBranch=s.value,o.paymentPage=1,ae()})}async function ae(){const n=v.paymentsUrl||"",a=c("#payTableMount");if(!n){a&&(a.innerHTML=`<div class="pay-empty"><strong>${t(e("payments.load_error"))}</strong></div>`);return}ta=!0,lt(),rt();const s=new URLSearchParams;o.paymentCustomerId?s.set("customer_id",String(o.paymentCustomerId)):o.paymentSearch&&s.set("q",o.paymentSearch),o.paymentTab&&o.paymentTab!=="all"&&s.set("status",o.paymentTab);const i=o.paymentBranch!=="all"?o.paymentBranch:o.branch||"all";i&&i!=="all"&&s.set("branch",i),o.paymentBeautician&&o.paymentBeautician!=="all"&&s.set("beautician",o.paymentBeautician),o.period&&s.set("period",o.period),s.set("page",String(o.paymentPage||1)),s.set("per_page","25");try{const l=await fetch(`${n}?${s.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("payments "+l.status);const r=await l.json();ge=Array.isArray(r.data)?r.data:[],Ea=Object.assign({pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0},r.meta&&r.meta.summary||{}),ye=Object.assign({statuses:[],beauticians:[],branches:[]},r.filters||{}),Ta={current_page:r.meta&&r.meta.current_page||1,last_page:r.meta&&r.meta.last_page||1,total:r.meta&&r.meta.total||0}}catch(l){console.error(l),ge=[],g(e("payments.load_error"))}finally{ta=!1,lt(),Hn(),Rn(),rt()}}function Dn(n){const a=String(n||"pending");return a==="paid"?{customer:"done",accountant:"done",hq:"done",active:null}:a==="processing"?{customer:"done",accountant:"done",hq:"active",active:"hq"}:a==="canceled"||a==="refunded"?{customer:"done",accountant:"active",hq:null,active:"accountant"}:{customer:"done",accountant:"active",hq:null,active:"accountant"}}function Rt(n){const a=Dn(n),s=l=>{const r=a[l];return`<span class="pay-pipe__node ${r==="done"?"is-done":r==="active"?"is-active":""}" title="${t(e("payments.flow_"+(l==="hq"?"hq":l)))}"></span>`},i=l=>`<span class="pay-pipe__line ${a[l]==="done"?"is-done":""}"></span>`;return`<div class="pay-pipe" title="${t(e("payments.pipeline"))}">${s("customer")}${i("customer")}${s("accountant")}${i("accountant")}${s("hq")}</div>`}function lt(){const n=Ea,a=c("#payHeroCopy");a&&(a.textContent=n.queue>0?e("payments.pulse_busy",{count:h(n.queue),amount:R(n.pending_amount)}):e("payments.pulse_clear"));const s=c("#payHeroStats");s&&(s.innerHTML=`
      <div class="pay-metric payment-metric payment-metric--queue"><span>${t(e("payments.stat_queue"))}</span><strong>${h(n.queue)}</strong></div>
      <div class="pay-metric payment-metric payment-metric--paid"><span>${t(e("payments.stat_paid"))}</span><strong>${R(n.paid_amount)}</strong></div>
      <div class="pay-metric payment-metric payment-metric--today"><span>${t(e("payments.stat_today"))}</span><strong>${h(n.paid_today)}</strong></div>
      <div class="pay-metric payment-metric payment-metric--hold"><span>${t(e("payments.stat_hold"))}</span><strong>${h(n.hold)}</strong></div>
    `)}function Hn(){const n=c("#payTabs");if(!n)return;const a=Ea,s={all:(a.pending||0)+(a.processing||0)+(a.paid||0)+(a.hold||0)+(a.refunded||0),queue:a.queue||0,pending:a.pending||0,processing:a.processing||0,paid:a.paid||0,canceled:a.hold||0,refunded:a.refunded||0},l=[...ye.statuses&&ye.statuses.length?ye.statuses:[{value:"queue",label:e("payments.tab_queue")},{value:"all",label:e("payments.tab_all")},{value:"pending",label:e("payments.tab_pending")},{value:"processing",label:e("payments.tab_processing")},{value:"paid",label:e("payments.tab_paid")},{value:"canceled",label:e("payments.tab_hold")},{value:"refunded",label:e("payments.tab_refunded")}]].sort((r,d)=>(r.value==="queue"?-1:0)-(d.value==="queue"?-1:0));n.innerHTML=l.map(r=>{const d=r.value,p=o.paymentTab===d?"active":"",u=s[d],m=u!==void 0?`<span class="pay-tab-count">${h(u)}</span>`:"";return`<button type="button" class="tab ${p}" role="tab" data-ptab="${t(d)}">${t(r.label)}${m}</button>`}).join(""),S("[data-ptab]",n).forEach(r=>{r.onclick=()=>{o.paymentTab=r.dataset.ptab,o.paymentPage=1,ae()}})}function Rn(){const n=c("#payBeautician");if(n){const s=o.paymentBeautician;n.innerHTML=`<option value="all">${t(e("payments.all_beauticians"))}</option>`+(ye.beauticians||[]).map(i=>`<option value="${i.id}" ${String(s)===String(i.id)?"selected":""}>${t(i.name)}</option>`).join("")}const a=c("#payBranch");if(a){const s=o.paymentBranch;a.innerHTML=`<option value="all">${t(e("payments.all_branches"))}</option>`+(ye.branches||[]).map(i=>`<option value="${i.id}" ${String(s)===String(i.id)?"selected":""}>${t(i.code?i.code+" · "+i.name:i.name)}</option>`).join("")}}function rt(){const n=c("#payTableMount"),a=c("#payResultCount");if(a&&(a.textContent=e("payments.result_count",{count:h(Ta.total||ge.length)})),!n)return;if(ta){n.innerHTML=`<div class="pay-empty"><strong>${t(e("payments.loading"))}</strong></div>`;return}if(!ge.length){n.innerHTML=`<div class="pay-empty"><strong>${t(e("payments.empty"))}</strong><span>${t(e("payments.empty_hint"))}</span></div>`;return}const s=ge.map(l=>{const r=l.ref&&l.ref!=="—",d=[l.has_proof?`<span class="pay-chip pay-chip--ok">${t(e("payments.chip_proof"))}</span>`:`<span class="pay-chip pay-chip--muted">${t(e("payments.stage_declared"))}</span>`,r?`<span class="pay-chip pay-chip--ok">${t(e("payments.chip_ref"))}</span>`:`<span class="pay-chip pay-chip--warn">${t(e("payments.chip_no_ref"))}</span>`].join("");return`<tr>
      <td><span class="pay-id">${t(l.code||"ORD-"+l.id)}</span></td>
      <td><div class="person-cell"><div class="mini-avatar">${t(l.initial||"?")}</div><div><strong>${t(l.customer||"")}</strong><small>${t(l.phone||"")}</small></div></div></td>
      <td>${t(l.branch||"—")}</td>
      <td>${t(l.beautician||"—")}</td>
      <td><div class="pay-amount">${R(l.amount)}</div><div class="pay-method">${t(l.payment_method_label||"")}</div></td>
      <td><div class="pay-chips">${d}</div><div class="pay-method" title="${t(e("payments.col_ref"))}">${t(l.ref||"—")}</div></td>
      <td>${Rt(l.payment_status)}</td>
      <td>${x(l.payment_status_label||l.payment_status)}</td>
      <td><button type="button" class="pay-review-btn" data-payment="${l.id}">${t(e("payments.review"))}</button></td>
    </tr>`}).join(""),i=ne("pay",Ta);n.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("payments.col_id"))}</th>
    <th>${t(e("payments.col_customer"))}</th>
    <th>${t(e("payments.col_branch"))}</th>
    <th>${t(e("payments.col_beautician"))}</th>
    <th>${t(e("payments.col_amount"))}</th>
    <th>${t(e("payments.col_ref"))}</th>
    <th>${t(e("payments.pipeline"))}</th>
    <th>${t(e("payments.col_status"))}</th>
    <th>${t(e("payments.col_action"))}</th>
  </tr></thead><tbody>${s}</tbody></table></div>${i}`,S("[data-payment]",n).forEach(l=>l.onclick=()=>In(Number(l.dataset.payment))),se("pay",l=>(o.paymentPage=l,ae()))}function Un(n){const a=n.proof;let s;if(!v.canViewOrder)s=`<p class="payment-proof__empty">${t(e("payments.proof_access"))}</p>`;else if(!a?.url)s=`<p class="payment-proof__empty">${t(e(n.has_proof?"payments.proof_unavailable":"payments.proof_missing"))}</p>`;else{const i=t(a.url),l=t(a.name||e("payments.proof_title")),r=`<a class="btn small" href="${i}" target="_blank" rel="noopener noreferrer">${t(e("payments.proof_open"))}</a>`;s=`${a.kind==="image"?`<a class="payment-proof__image" href="${i}" target="_blank" rel="noopener noreferrer"><img id="paymentProofImage" src="${i}" alt="${l}" loading="lazy"></a><p class="payment-proof__empty" id="paymentProofError" hidden>${t(e("payments.proof_unavailable"))}</p>`:a.kind==="pdf"?`<object class="payment-proof__pdf" data="${i}" type="application/pdf" aria-label="${l}"><p class="payment-proof__empty">${t(e("payments.proof_pdf_hint"))}</p></object>`:`<p class="payment-proof__empty">${t(e("payments.proof_pdf_hint"))}</p>`}<div class="payment-proof__file"><span>${l}</span>${r}</div>`}return`<section class="payment-proof"><h3>${t(e("payments.proof_title"))}</h3>${s}</section>`}function In(n){const a=ge.find(m=>Number(m.id)===Number(n));if(!a)return;const s=N(v.orderShowUrlTemplate,a.id),i=!!v.canEditOrder,l=!!v.canViewOrder,r=["identity","invoice","method","ref","proof","status"].map(m=>{const _=a.checklist?.[m],b=["completed","not_applicable"].includes(_)?_:"pending";return`<div class="pay-review__check is-${b}" data-check="${m}"><span class="pay-review__check-icon" aria-hidden="true">${b==="completed"?"✓":b==="not_applicable"?"—":"○"}</span><span>${t(e("payments.check_"+m))}</span><small>${t(e("payments.check_"+b))}</small></div>`}).join(""),d=`<div class="pay-review">
    <div class="pay-review__hero">
      <div class="pay-review__avatar">${t(a.initial||"?")}</div>
      <div style="min-width:0;flex:1">
        <div class="pay-id">${t(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${t(a.customer||"")}</strong>
        <div class="pay-method">${t(a.phone||"")} · ${t(a.branch_name||a.branch||"")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px;align-items:center">${x(a.payment_status_label||a.payment_status)}${Rt(a.payment_status)}</div>
      </div>
      <div style="text-align:right"><div class="pay-amount">${R(a.amount)}</div><div class="pay-method">${t(a.payment_method_label||"")}</div></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${t(e("payments.col_customer_stage"))}</label><strong>${t(a.customer_stage)}</strong></div>
      <div class="pay-review__card"><label>${t(e("payments.col_accountant"))}</label><strong>${t(a.accountant_stage)}</strong></div>
      <div class="pay-review__card"><label>${t(e("payments.col_hq"))}</label><strong>${t(a.hq_stage)}</strong></div>
    </div>
    ${Un(a)}
    ${i?`<div class="detail-grid">
      <div class="detail-box"><label>${t(e("payments.bank_ref"))}</label><input class="control" id="payRefInput" value="${t(a.ref==="—"?"":a.ref)}" placeholder="${t(e("payments.bank_ref_ph"))}" /></div>
      <div class="detail-box" style="grid-column:span 2"><label>${t(e("payments.admin_note"))}</label><input class="control" id="payNoteInput" value="${t(a.admin_note||"")}" /></div>
    </div>`:""}
    <div class="journey-section"><div class="journey-section__title">${t(e("payments.checklist"))}</div><div class="pay-review__checks">${r}</div><p class="pay-review__check-note">${t(e("payments.check_note"))}</p></div>
    ${l?"":`<p class="card-subtitle">${t(e("payments.no_order_access"))}</p>`}
  </div>`,p=[l?`<a class="btn" href="${t(s)}" target="_blank" rel="noopener">${t(e("payments.open_order"))}</a>`:"",i?`<button type="button" class="btn" id="payMarkProcessing">${t(e("payments.mark_processing"))}</button>`:"",i?`<button type="button" class="btn danger" id="payMarkHold">${t(e("payments.mark_hold"))}</button>`:"",i?`<button type="button" class="btn success" id="payMarkPaid">${t(e("payments.mark_paid"))}</button>`:"",`<button type="button" class="btn" data-action-drawer-close>${t(e("payments.close"))}</button>`].filter(Boolean).join("");z(e("payments.review_title"),e("payments.review_sub",{code:a.code,customer:a.customer}),d,p,"Pay Verify");const u=c("#paymentProofImage");if(u){const m=()=>{u.closest("a").hidden=!0,c("#paymentProofError").hidden=!1};u.onerror=m,u.complete&&!u.naturalWidth&&m()}setTimeout(()=>{const m=c("#payMarkProcessing");m&&(m.onclick=()=>$a(a,"processing"));const _=c("#payMarkHold");_&&(_.onclick=()=>$a(a,"canceled"));const b=c("#payMarkPaid");b&&(b.onclick=()=>$a(a,"paid"))},0)}async function $a(n,a){const s=N(v.orderPaymentStatusUrlTemplate,n.id);if(!s||!v.canEditOrder){g(e("payments.update_error"));return}const i=c("#payRefInput"),l=c("#payNoteInput"),r=i?i.value.trim():"",d=l?l.value.trim():"";if(n.needs_reference&&(a==="paid"||a==="processing")&&!r&&(n.ref==="—"||!n.ref)){g(e("payments.ref_required"));return}try{const p=await fetch(s,{method:"PUT",headers:q(!0),credentials:"same-origin",body:JSON.stringify({payment_status:a,transaction_id:r||void 0,admin_note:d||void 0})}),u=await p.json().catch(()=>({}));if(!p.ok){g(u.message||e("payments.update_error"));return}A(),g(u.message||e("payments.updated")),await ae()}catch(p){console.error(p),g(e("payments.update_error"))}}function Fn(){j.innerHTML=`<div class="cin-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${t(e("checkin.title"))}</h1>
        <div class="page-subtitle">${t(e("checkin.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="cinRefresh">${t(e("checkin.refresh"))}</button>
        ${v.canConfirmCheckin?`<button type="button" class="btn primary" id="cinScan">${t(e("checkin.scan"))}</button>`:""}
        ${v.canViewTreatments&&v.treatmentReservationsUrl?`<a class="btn" href="${t(v.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("checkin.open_crm"))}</a>`:""}
      </div>
    </div>
    <section class="clearance-flow checkin-flow" aria-labelledby="checkinFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CRM</span><h2 id="checkinFlowTitle">${t(e("checkin.workflow_title"))}</h2><p>${t(e("checkin.workflow_hint"))}</p></div>
      <ol class="clearance-flow__steps">
        ${[e("checkin.step_booked"),e("checkin.step_checked_in"),e("checkin.step_clearance"),e("checkin.step_treatment")].map((s,i)=>`<li><span aria-hidden="true">${i+1}</span><strong>${t(s)}</strong></li>`).join("")}
      </ol>
    </section>
    <div class="pay-metrics" id="cinMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${t(e("checkin.subtitle"))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="cinResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="cinTabs" role="group"></div>
        <label class="lead-search" for="cinSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="cinSearch" aria-label="${t(e("checkin.search_placeholder"))}" type="search" autocomplete="off" placeholder="${t(e("checkin.search_placeholder"))}" value="${t(o.checkinSearch||"")}" />
        </label>
        <div class="lead-filter-grid ops-filter-grid">
          <label class="lead-field"><span class="lead-field__label">${t(e("checkin.filter_scope"))}</span>
            <select class="lead-field__control" id="cinScope">
              <option value="pipeline"${o.checkinScope==="pipeline"?" selected":""}>${t(e("checkin.scope_pipeline"))}</option>
              <option value="day"${o.checkinScope==="day"?" selected":""}>${t(e("checkin.scope_day"))}</option>
            </select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${t(e("checkin.filter_date"))}</span>
            <input class="lead-field__control" id="cinDate" type="date" value="${t(o.checkinDate||"")}"${o.checkinScope==="pipeline"?" disabled":""} />
          </label>
          <label class="lead-field"><span class="lead-field__label">${t(e("checkin.filter_beautician"))}</span>
            <select class="lead-field__control" id="cinBeautician"></select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${t(e("checkin.filter_branch"))}</span>
            <select class="lead-field__control" id="cinBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="cinTableMount"></div>
    </section>
  </div>`;const n=c("#cinRefresh");n&&(n.onclick=()=>J());const a=c("#cinScan");a&&(a.onclick=Gn),On(),J()}function On(){const n=c("#cinSearch");n&&(n.oninput=()=>{clearTimeout(Ga),Ga=setTimeout(()=>{o.checkinSearch=n.value.trim(),o.checkinPage=1,J()},320)});const a=c("#cinScope");a&&(a.onchange=()=>{o.checkinScope=a.value,a.value==="pipeline"&&(o.checkinStatus="live");const r=c("#cinDate");r&&(r.disabled=a.value==="pipeline"),o.checkinPage=1,J()});const s=c("#cinDate");s&&(s.onchange=()=>{o.checkinDate=s.value,o.checkinScope="day",c("#cinScope").value="day",o.checkinPage=1,J()});const i=c("#cinBeautician");i&&(i.onchange=()=>{o.checkinBeautician=i.value,o.checkinPage=1,J()});const l=c("#cinBranch");l&&(l.onchange=()=>{o.checkinBranch=l.value,o.checkinPage=1,J()})}async function J(){const n=++ze;Re=!1;const a=v.checkinUrl||"",s=c("#cinTableMount");if(!a){s&&(s.innerHTML=`<div class="pay-empty"><strong>${t(e("checkin.load_error"))}</strong></div>`);return}me=!0,ot(),ct();const i=new URLSearchParams;o.checkinSearch&&i.set("q",o.checkinSearch),i.set("status",o.checkinStatus||"live"),i.set("scope",o.checkinScope||"day"),o.checkinDate&&i.set("date",o.checkinDate);const l=o.checkinBranch!=="all"?o.checkinBranch:o.branch||"all";l&&l!=="all"&&i.set("branch",l),o.checkinBeautician&&o.checkinBeautician!=="all"&&i.set("beautician",o.checkinBeautician),i.set("page",String(o.checkinPage||1)),i.set("per_page","25");try{const r=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!r.ok)throw new Error("checkin "+r.status);const d=await r.json();if(n!==ze)return;ue=Array.isArray(d.data)?d.data:[],Da=Object.assign({live:0,scheduled:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0},d.meta&&d.meta.summary||{}),Ca=Object.assign({statuses:[],beauticians:[],branches:[]},d.filters||{}),Ha={current_page:d.meta&&d.meta.current_page||1,last_page:d.meta&&d.meta.last_page||1,total:d.meta&&d.meta.total||0}}catch(r){if(n!==ze)return;Re=!0,console.error(r),ue=[],g(e("checkin.load_error"))}finally{if(n!==ze)return;me=!1,ot(),Vn(),Wn(),ct()}}function ot(){const n=c("#cinMetrics");if(!n)return;const a=Da;n.setAttribute("aria-busy",String(me));const s=i=>me||Re?"—":h(i);n.innerHTML=`
    <div class="pay-metric checkin-metric checkin-metric--scheduled"><span>${t(e("checkin.stat_scheduled"))}</span><strong>${s(a.scheduled)}</strong></div>
    <div class="pay-metric checkin-metric checkin-metric--waiting"><span>${t(e("checkin.stat_waiting"))}</span><strong>${s(a.waiting)}</strong><small>${t(e("checkin.stat_waiting_meta",{minutes:s(a.avg_wait_mins),unpaid:s(a.unpaid)}))}</small></div>
    <div class="pay-metric checkin-metric checkin-metric--treatment"><span>${t(e("checkin.stat_treatment"))}</span><strong>${s(a.in_treatment)}</strong></div>
    <div class="pay-metric checkin-metric checkin-metric--done"><span>${t(e("checkin.stat_completed"))}</span><strong>${s(a.completed)}</strong></div>`}function Vn(){const n=c("#cinTabs");if(!n)return;const a=Da,s=[["live",e("checkin.tab_live"),a.live],["booked",e("checkin.tab_booked"),a.scheduled],["waiting",e("checkin.tab_waiting"),a.waiting],["in_progress",e("checkin.tab_treatment"),a.in_treatment],["completed",e("checkin.tab_completed"),a.completed],["all",e("checkin.tab_all"),null]];n.innerHTML=s.filter(([i])=>o.checkinScope!=="pipeline"||!["completed","all"].includes(i)).map(([i,l,r])=>{const d=o.checkinStatus===i?"active":"",p=r==null?"":` (${h(r)})`;return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-cintab="${i}">${t(l)}${p}</button>`}).join(""),S("[data-cintab]",n).forEach(i=>i.onclick=()=>{o.checkinStatus=i.dataset.cintab,["completed","all"].includes(o.checkinStatus)&&(o.checkinScope="day",c("#cinScope").value="day"),o.checkinPage=1,J()})}function Wn(){const n=c("#cinBeautician");if(n){const i=o.checkinBeautician||"all";n.innerHTML=`<option value="all">${t(e("checkin.all_beauticians"))}</option>`+(Ca.beauticians||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const a=c("#cinBranch");if(a){const i=o.checkinBranch||"all";a.innerHTML=`<option value="all">${t(e("checkin.all_branches"))}</option>`+(Ca.branches||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const s=c("#cinResultCount");s&&(s.textContent=e("checkin.result_count",{count:Re?"—":h(Ha.total)}))}function ct(){const n=c("#cinTableMount");if(!n)return;if(n.setAttribute("aria-busy",String(me)),Re){n.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("checkin.load_error"))}</strong><button type="button" class="btn" id="cinRetry">${t(e("checkin.refresh"))}</button></div>`,c("#cinRetry").onclick=()=>J();return}if(me){n.innerHTML=`<div class="pay-empty" role="status">${t(e("checkin.loading"))}</div>`;return}if(!ue.length){n.innerHTML=`<div class="pay-empty"><strong>${t(e("checkin.empty"))}</strong>${t(e("checkin.empty_hint"))}</div>`;return}const a=ue.map(s=>`<tr data-checkin-status="${t(s.status||"pending")}" data-arrival-state="${s.checked_in_at?"arrived":"booked"}">
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${t(s.initial||"?")}</div><div class="person-cell__text"><strong>${t(s.name||"")}</strong><small>${t(s.code||"")} · ${t(s.phone||"")}</small></div></div></td>
    <td><strong>${t(s.date_label||"")}</strong><small class="checkin-cell-meta">${t(s.time||"—")}</small></td>
    <td><strong>${t(s.branch_name||s.branch||"—")}</strong><small class="checkin-cell-meta">${t(s.beautician||"—")}</small></td>
    <td>${t(s.treatment||"—")}</td>
    <td><div class="checkin-status-stack">${x(s.payment_label)}${x(s.clearance_label)}</div></td>
    <td><div class="checkin-status-stack">${x(s.arrival_label)}<small>${t(s.waiting_label||"—")}</small></div></td>
    <td><div class="checkin-row-actions">${Ut(s,!0)}<button type="button" class="btn small soft" data-cin-view="${s.id}">${t(e("checkin.view"))}</button></div></td>
  </tr>`).join("");n.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("checkin.col_customer"))}</th><th>${t(e("checkin.col_time"))}</th>
    <th>${t(e("checkin.col_branch"))} / ${t(e("checkin.col_beautician"))}</th>
    <th>${t(e("checkin.col_treatment"))}</th><th>${t(e("checkin.col_payment"))} / ${t(e("checkin.col_clearance"))}</th>
    <th>${t(e("checkin.col_status"))}</th><th>${t(e("checkin.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${ne("cin",Ha)}`,S("[data-cin-view]",n).forEach(s=>s.onclick=()=>Xn(s.dataset.cinView)),It(n),se("cin",s=>(o.checkinPage=s,J()))}function Ut(n,a=!1){const s=Array.isArray(n.available_actions)?n.available_actions:[],i=a?"btn small":"btn";return s.includes("confirm_arrival")&&v.canConfirmCheckin?`<button type="button" class="${i} primary" data-cin-confirm="${n.id}">${t(e("checkin.confirm_arrival"))}</button>`:s.includes("open_clearance")?`<button type="button" class="${i} primary" data-cin-clearance="${n.id}">${t(e("checkin.go_clearance"))}</button>`:s.includes("open_crm")&&v.canViewTreatments&&v.treatmentReservationsUrl?`<a class="${i} primary" href="${t(v.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("checkin.open_crm"))}</a>`:""}function Yn(n){return n.status==="completed"?e("checkin.action_help_completed"):n.status==="in_progress"?e("checkin.action_help_treatment"):e(n.checked_in_at?"checkin.action_help_waiting":"checkin.action_help_booked")}function It(n=document){S("[data-cin-confirm]",n).forEach(a=>a.onclick=()=>Kn(a.dataset.cinConfirm)),S("[data-cin-clearance]",n).forEach(a=>a.onclick=()=>{const s=ue.find(i=>Number(i.id)===Number(a.dataset.cinClearance));s&&(o.clearanceSearch=String(s.phone||s.code||s.name||"").trim()),A(),V("clearance")})}function Kn(n){const a=ue.find(s=>Number(s.id)===Number(n));a&&(z(e("checkin.confirm_title"),`${a.code} · ${a.name}`,`<div class="checkin-confirm"><div class="clearance-confirm__icon">${ma(!0)}</div><p>${t(e("checkin.confirm_body"))}</p></div>`,`<button type="button" class="btn" data-action-drawer-close>${t(e("checkin.cancel"))}</button><button type="button" class="btn primary" id="confirmCheckinArrival">${t(e("checkin.confirm_action"))}</button>`,e("checkin.workflow_title")),c("#confirmCheckinArrival").onclick=s=>zn(a,s.currentTarget))}async function zn(n,a){const s=N(v.checkinConfirmUrlTemplate,n.id);if(!s||!v.canConfirmCheckin){g(e("checkin.confirm_error"));return}a.disabled=!0,a.setAttribute("aria-busy","true");try{const i=await fetch(s,{method:"POST",headers:q(!0),credentials:"same-origin",body:JSON.stringify({})}),l=await i.json().catch(()=>({}));if(!i.ok){g(l.message||e("checkin.confirm_error"));return}A(),g(l.message||e("checkin.checkin_confirmed",{code:n.code})),await J()}catch(i){console.error(i),g(e("checkin.confirm_error"))}finally{a.isConnected&&(a.disabled=!1,a.removeAttribute("aria-busy"))}}function Jn(n){try{const a=new URL(String(n||"").trim(),window.location.origin),s=new URL(v.checkinPassBaseUrl,window.location.origin);return a.origin===s.origin&&a.pathname.startsWith(s.pathname.replace(/\/$/,"")+"/")&&a.searchParams.has("expires")&&a.searchParams.has("signature")}catch{return!1}}function oa(){cancelAnimationFrame(La),La=0,we&&(we.getTracks().forEach(a=>a.stop()),we=null);const n=c("#cinScannerVideo");n&&(n.srcObject=null)}function Gn(){const n=`<div class="cin-scanner">
    <p class="cin-scanner__hint">${t(e("checkin.scanner_hint"))}</p>
    <div class="cin-scanner__viewport"><video id="cinScannerVideo" playsinline muted aria-label="${t(e("checkin.scanner_title"))}"></video><div class="cin-scanner__guide" aria-hidden="true"></div></div>
    <p class="cin-scanner__status" id="cinScannerStatus" aria-live="polite"></p>
    <button type="button" class="btn primary" id="cinScannerStart">${t(e("checkin.scanner_start"))}</button>
    <label class="lead-field cin-scanner__manual"><span class="lead-field__label">${t(e("checkin.scanner_manual_label"))}</span>
      <input class="lead-field__control" id="cinScannerInput" type="url" inputmode="url" autocomplete="off" placeholder="${t(e("checkin.scanner_manual_placeholder"))}">
    </label>
  </div>`,a=`<button type="button" class="btn primary" id="cinScannerOpen">${t(e("checkin.scanner_open"))}</button><button type="button" class="btn" data-action-drawer-close>${t(e("checkin.close"))}</button>`;z(e("checkin.scanner_title"),e("checkin.scanner_hint"),n,a,e("nav.checkin")),c("#cinScannerStart").onclick=Ft,c("#cinScannerOpen").onclick=()=>Pa(c("#cinScannerInput").value),c("#cinScannerInput").onkeydown=s=>{s.key==="Enter"&&(s.preventDefault(),Pa(s.currentTarget.value))}}function Pa(n){if(!Jn(n)){const a=c("#cinScannerStatus");a&&(a.textContent=e("checkin.scanner_invalid"));return}oa(),window.location.assign(String(n).trim())}async function Ft(){const n=c("#cinScannerStatus"),a=c("#cinScannerStart");if(!("BarcodeDetector"in window)||!navigator.mediaDevices?.getUserMedia){n.textContent=e("checkin.scanner_unsupported");return}a.disabled=!0,n.textContent=e("common.loading");try{if(!(BarcodeDetector.getSupportedFormats?await BarcodeDetector.getSupportedFormats():["qr_code"]).includes("qr_code"))throw new Error("QR format is unavailable");const i=new BarcodeDetector({formats:["qr_code"]});we=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:"environment"}},audio:!1});const l=c("#cinScannerVideo");l.srcObject=we,await l.play(),a.textContent=e("checkin.scanner_stop"),a.disabled=!1,a.onclick=()=>{oa(),a.textContent=e("checkin.scanner_start"),a.onclick=Ft},n.textContent=e("checkin.scanner_hint");const r=async()=>{if(!(!we||!l.isConnected)){try{const d=await i.detect(l);if(d[0]?.rawValue){Pa(d[0].rawValue);return}}catch(d){console.error(d)}La=requestAnimationFrame(r)}};r()}catch(s){console.error(s),oa(),a.disabled=!1,n.textContent=e("checkin.scanner_unsupported")}}function Xn(n){const a=ue.find(w=>Number(w.id)===Number(n));if(!a)return;const s=a.order_id&&v.orderShowUrlTemplate?N(v.orderShowUrlTemplate,a.order_id):"",i=a.customer_id&&v.userEditUrlTemplate?N(v.userEditUrlTemplate,a.customer_id):"",l=a.status||"pending",r=!!a.checked_in_at,d=[{label:e("checkin.step_booked"),state:r||l!=="pending"?"done":"active",time:""},{label:e("checkin.step_checked_in"),state:r?"done":"",time:r&&a.checked_in_label&&a.checked_in_label!=="—"?a.checked_in_label:""},{label:e("checkin.step_clearance"),state:r&&l==="pending"?"active":["in_progress","completed"].includes(l)?"done":"",time:""},{label:e("checkin.step_treatment"),state:l==="in_progress"?"active":l==="completed"?"done":"",time:""},{label:e("checkin.step_completed"),state:l==="completed"?"active done":"",time:""}],p=`<div class="cin-preview__block cin-timeline"><div class="journey-section__title">${t(e("checkin.timeline"))}</div>
    <div class="timeline">${d.map(w=>`<div class="timeline-step ${w.state}"><div class="timeline-dot">${ma(w.state.includes("done"))}</div><span>${t(w.label)}${w.time?` <em>· ${t(w.time)}</em>`:""}</span></div>`).join("")}</div></div>`,u=`${a.phone||a.email?`<div class="cin-preview__contact">
    ${a.phone?`<a href="tel:${t(a.phone)}"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5.2 2.8 7.5 6 6 7.5c1.2 2.4 2.1 3.3 4.5 4.5l1.5-1.5 3.2 2.3c.5.4.7 1 .5 1.6-.4 1-1.4 1.7-2.5 1.6C7.8 15.6 4.4 12.2 4 6.8c-.1-1.1.6-2.1 1.6-2.5.6-.2 1.2 0 1.6.5Z"/></svg>${t(a.phone)}</a>`:""}
    ${a.email?`<a href="mailto:${t(a.email)}"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="2.5" y="4" width="15" height="12" rx="2"/><path d="m3.5 6 6.5 5 6.5-5"/></svg>${t(a.email)}</a>`:""}
  </div>`:""}`,m=a.checkin_pass_url?`<div class="cin-preview__block cin-preview__qr">
    <div class="journey-section__title">${t(e("checkin.qr_title"))}</div>
    <div class="cin-preview__qr-box"><canvas id="cinQrCanvas" role="img" aria-label="${t(e("checkin.qr_title"))}"></canvas></div>
    <div class="cin-preview__qr-code">${t(a.code||"")}</div>
    <p class="cin-preview__qr-hint">${t(e("checkin.qr_hint"))}</p>
    <div class="cin-preview__qr-actions"><button type="button" class="btn small soft" id="cinCopyCode">${t(e("checkin.copy_code"))}</button><a class="btn small" href="${t(a.checkin_pass_url)}" target="_blank" rel="noopener">${t(e("checkin.view_checkin_pass"))}</a></div>
  </div>`:"",_=`<div class="cin-preview__block"><div class="journey-section__title">${t(e("checkin.details"))}</div>
    <div class="journey-kv">
      <div><span>${t(e("checkin.col_treatment"))}</span><strong>${t(a.treatment||"—")}</strong></div>
      <div><span>${t(e("checkin.col_beautician"))}</span><strong>${t(a.beautician||"—")}</strong></div>
      <div><span>${t(e("checkin.col_branch"))}</span><strong>${t(a.branch_name||a.branch||"—")}</strong></div>
      <div><span>${t(e("checkin.col_payment"))}</span><strong>${t(a.payment_label||"—")}</strong></div>
      <div><span>${t(e("checkin.col_date"))}</span><strong>${t((a.date_label||"")+" "+(a.time||""))}</strong></div>
      <div><span>${t(e("checkin.col_checked_in"))}</span><strong>${t(a.checked_in_label||"—")}</strong></div>
      <div><span>${t(e("checkin.col_wait"))}</span><strong>${t(a.waiting_label||"—")}</strong></div>
      <div><span>${t(e("checkin.col_clearance"))}</span><strong>${t(a.clearance_label||"—")}</strong></div>
    </div></div>`,b=`<div class="pay-review cin-preview">
    <div class="pay-review__hero pay-review__hero--cin">
      <div class="pay-review__avatar cin-avatar" aria-hidden="true">${t(a.initial||"?")}</div>
      <div style="min-width:0;flex:1">
        <div class="pay-id">${t(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${t(a.name||"")}</strong>
        <div class="pay-method">${t(a.date_label||"")} ${t(a.time||"")} · ${t(a.branch_name||a.branch||"—")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${x(a.status_label)}${x(a.arrival_label)}</div>
      </div>
    </div>
    ${u}<div class="checkin-decision checkin-decision--${t(l==="pending"?r?"waiting":"booked":l)}"><strong>${t(a.arrival_label||a.status_label||"")}</strong><p>${t(Yn(a))}</p></div>${p}${_}${m}
  </div>`,k=[Ut(a),i&&v.canViewUser?`<a class="btn" href="${t(i)}" target="_blank" rel="noopener">${t(e("checkin.open_customer"))}</a>`:"",s&&v.canViewOrder?`<a class="btn" href="${t(s)}" target="_blank" rel="noopener">${t(e("checkin.open_order"))}</a>`:"",!(Array.isArray(a.available_actions)&&a.available_actions.includes("open_crm"))&&v.canViewTreatments&&v.treatmentReservationsUrl?`<a class="btn" href="${t(v.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("checkin.open_crm"))}</a>`:"",`<button type="button" class="btn" data-action-drawer-close>${t(e("checkin.close"))}</button>`].filter(Boolean).join("");z(e("checkin.title"),a.code+" · "+a.name,b,k,e("nav.checkin")),It(c("#actionDrawer"));const $=c("#cinQrCanvas");if($&&window.QRCentral)try{QRCentral.render($,a.checkin_pass_url||"")}catch(w){console.error(w),$.closest(".cin-preview__qr-box")?.classList.add("hidden")}const f=c("#cinCopyCode");f&&(f.onclick=()=>Qn(a.code||"",e("checkin.copied")))}function Qn(n,a){const s=()=>g(a);if(!n){g(e("checkin.copy_missing"));return}navigator.clipboard&&window.isSecureContext?navigator.clipboard.writeText(n).then(s).catch(()=>dt(n,s)):dt(n,s)}function dt(n,a){const s=document.createElement("textarea");s.value=n,s.setAttribute("readonly",""),s.style.position="fixed",s.style.opacity="0",document.body.appendChild(s),s.select();try{document.execCommand("copy"),a()}catch{g(e("checkin.copy_missing"))}document.body.removeChild(s)}function Zn(){j.innerHTML=`<div class="clr-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${t(e("clearance.title"))}</h1>
        <div class="page-subtitle">${t(e("clearance.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="clrRefresh">${t(e("clearance.refresh"))}</button>
        <button type="button" class="btn" data-jump="payments">${t(e("clearance.open_payments"))}</button>
        ${v.canViewTreatments&&v.treatmentReservationsUrl?`<a class="btn primary" href="${t(v.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("clearance.open_crm"))}</a>`:""}
      </div>
    </div>
    <section class="clearance-flow" aria-labelledby="clearanceFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CRM</span><h2 id="clearanceFlowTitle">${t(e("clearance.workflow_title"))}</h2><p>${t(e("clearance.workflow_hint"))}</p></div>
      <ol class="clearance-flow__steps">
        ${[e("clearance.step_arrival"),e("clearance.step_payment"),e("clearance.step_treatment"),e("clearance.step_complete")].map((a,s)=>`<li><span aria-hidden="true">${s+1}</span><strong>${t(a)}</strong></li>`).join("")}
      </ol>
    </section>
    <div class="pay-metrics" id="clrMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${t(e("clearance.subtitle"))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="clrResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="clrTabs" role="group"></div>
        <label class="lead-search" for="clrSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="clrSearch" aria-label="${t(e("clearance.search_placeholder"))}" type="search" autocomplete="off" placeholder="${t(e("clearance.search_placeholder"))}" value="${t(o.clearanceSearch||"")}" />
        </label>
        <div class="lead-filter-grid ops-filter-grid ops-filter-grid--two">
          <label class="lead-field"><span class="lead-field__label">${t(e("clearance.filter_beautician"))}</span>
            <select class="lead-field__control" id="clrBeautician"></select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${t(e("clearance.filter_branch"))}</span>
            <select class="lead-field__control" id="clrBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="clrTableMount"></div>
    </section>
  </div>`;const n=c("#clrRefresh");n&&(n.onclick=()=>te()),ie(),es(),te()}function es(){const n=c("#clrSearch");n&&(n.oninput=()=>{clearTimeout(Xa),Xa=setTimeout(()=>{o.clearanceSearch=n.value.trim(),o.clearancePage=1,te()},320)});const a=c("#clrBeautician");a&&(a.onchange=()=>{o.clearanceBeautician=a.value,o.clearancePage=1,te()});const s=c("#clrBranch");s&&(s.onchange=()=>{o.clearanceBranch=s.value,o.clearancePage=1,te()})}async function te(){const n=++Je;Ue=!1;const a=v.clearanceUrl||"",s=c("#clrTableMount");if(!a){s&&(s.innerHTML=`<div class="pay-empty"><strong>${t(e("clearance.load_error"))}</strong></div>`);return}ve=!0,pt(),ut();const i=new URLSearchParams;o.clearanceSearch&&i.set("q",o.clearanceSearch),o.clearanceState&&i.set("state",o.clearanceState);const l=o.clearanceBranch!=="all"?o.clearanceBranch:o.branch||"all";l&&l!=="all"&&i.set("branch",l),o.clearanceBeautician&&o.clearanceBeautician!=="all"&&i.set("beautician",o.clearanceBeautician),i.set("page",String(o.clearancePage||1)),i.set("per_page","25");try{const r=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!r.ok)throw new Error("clearance "+r.status);const d=await r.json();if(n!==Je)return;_e=Array.isArray(d.data)?d.data:[],Ra=Object.assign({waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0},d.meta&&d.meta.summary||{}),xa=Object.assign({states:[],beauticians:[],branches:[]},d.filters||{}),Ua={current_page:d.meta&&d.meta.current_page||1,last_page:d.meta&&d.meta.last_page||1,total:d.meta&&d.meta.total||0}}catch(r){if(n!==Je)return;Ue=!0,console.error(r),_e=[],g(e("clearance.load_error"))}finally{if(n!==Je)return;ve=!1,pt(),as(),ts(),ut()}}function pt(){const n=c("#clrMetrics");if(!n)return;const a=Ra;n.setAttribute("aria-busy",String(ve));const s=i=>ve||Ue?"—":h(i);n.innerHTML=`
    <div class="pay-metric clearance-metric clearance-metric--waiting"><span>${t(e("clearance.stat_waiting"))}</span><strong>${s(a.waiting)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--blocked"><span>${t(e("clearance.stat_blocked"))}</span><strong>${s(a.blocked)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--treatment"><span>${t(e("clearance.stat_treatment"))}</span><strong>${s(a.in_treatment)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--done"><span>${t(e("clearance.stat_done"))}</span><strong>${s(a.done_today)}</strong></div>`}function as(){const n=c("#clrTabs");if(!n)return;const a=Ra,s=[["waiting",e("clearance.tab_waiting"),a.waiting],["blocked",e("clearance.tab_blocked"),a.blocked],["in_treatment",e("clearance.tab_treatment"),a.in_treatment],["all_queue",e("clearance.tab_queue"),a.queue],["done",e("clearance.tab_done"),a.done_today]];n.innerHTML=s.map(([i,l,r])=>{const d=o.clearanceState===i?"active":"";return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-clrtab="${i}">${t(l)} (${h(r||0)})</button>`}).join(""),S("[data-clrtab]",n).forEach(i=>i.onclick=()=>{o.clearanceState=i.dataset.clrtab,o.clearancePage=1,te()})}function ts(){const n=c("#clrBeautician");if(n){const i=o.clearanceBeautician||"all";n.innerHTML=`<option value="all">${t(e("clearance.all_beauticians"))}</option>`+(xa.beauticians||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const a=c("#clrBranch");if(a){const i=o.clearanceBranch||"all";a.innerHTML=`<option value="all">${t(e("clearance.all_branches"))}</option>`+(xa.branches||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const s=c("#clrResultCount");s&&(s.textContent=e("clearance.result_count",{count:Ue?"—":h(Ua.total)}))}function ut(){const n=c("#clrTableMount");if(!n)return;if(n.setAttribute("aria-busy",String(ve)),Ue){n.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("clearance.load_error"))}</strong><button type="button" class="btn" id="clrRetry">${t(e("clearance.refresh"))}</button></div>`,c("#clrRetry").onclick=()=>te();return}if(ve){n.innerHTML=`<div class="pay-empty" role="status">${t(e("clearance.loading"))}</div>`;return}if(!_e.length){n.innerHTML=`<div class="pay-empty"><strong>${t(e("clearance.empty"))}</strong>${t(e("clearance.empty_hint"))}</div>`;return}const a=_e.map(s=>`<tr data-clearance-state="${t(s.clearance||"other")}">
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${t(s.initial||"?")}</div><div class="person-cell__text"><strong>${t(s.name||"")}</strong><small>${t(s.code||"")} · ${t(s.phone||"")}</small></div></div></td>
    <td><strong>${t(s.date_label||"")}</strong><small class="clearance-wait">${t(s.checked_in_at?e("clearance.arrival_waiting",{time:s.waiting_label||"—"}):s.time||"—")}</small></td>
    <td>${t(s.branch_name||s.branch||"—")}</td>
    <td>${t(s.treatment||"—")}</td>
    <td>${x(s.payment_label)}</td>
    <td>${x(s.clearance_label)}</td>
    <td><div class="clearance-row-actions">${Ot(s,!0)}<button type="button" class="btn small soft" data-clr-view="${s.id}">${t(e("clearance.view"))}</button></div></td>
  </tr>`).join("");n.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("clearance.col_customer"))}</th><th>${t(e("clearance.col_time"))}</th>
    <th>${t(e("clearance.col_branch"))}</th><th>${t(e("clearance.col_treatment"))}</th>
    <th>${t(e("clearance.col_payment"))}</th><th>${t(e("clearance.col_clearance"))}</th>
    <th>${t(e("clearance.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${ne("clr",Ua)}`,S("[data-clr-view]",n).forEach(s=>s.onclick=()=>ls(s.dataset.clrView)),Vt(n),se("clr",s=>(o.clearancePage=s,te()))}function Ot(n,a=!1){const s=Array.isArray(n.available_actions)?n.available_actions:[],i=a?"btn small":"btn";if(s.includes("start_treatment")&&v.canEditTreatments)return`<button type="button" class="${i} primary" data-clr-status="in_progress" data-clr-id="${n.id}">${t(e("clearance.start_treatment"))}</button>`;if(s.includes("complete_treatment")&&v.canEditTreatments)return`<button type="button" class="${i} success" data-clr-status="completed" data-clr-id="${n.id}">${t(e("clearance.complete_treatment"))}</button>`;if(s.includes("resolve_payment")){if(n.order_id)return`<button type="button" class="${i} danger" data-clr-payment="${n.id}">${t(e("clearance.resolve_payment"))}</button>`;if(v.canViewTreatments&&v.treatmentReservationsUrl)return`<a class="${i} danger" href="${t(v.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("clearance.resolve_payment"))}</a>`}return""}function ns(n){return e(`clearance.action_help_${{waiting:"waiting",blocked:"blocked",in_treatment:"treatment",done:"done"}[n.clearance]||"done"}`)}function ma(n){return n?'<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 10 3 3 7-7"/></svg>':'<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10" cy="10" r="6"/></svg>'}function Vt(n=document){S("[data-clr-status]",n).forEach(a=>a.onclick=()=>ss(a.dataset.clrId,a.dataset.clrStatus)),S("[data-clr-payment]",n).forEach(a=>a.onclick=()=>{const s=_e.find(i=>Number(i.id)===Number(a.dataset.clrPayment));s&&(o.paymentSearch=String(s.phone||s.name||s.code||"").trim()),A(),V("payments")})}function ss(n,a){const s=_e.find(l=>Number(l.id)===Number(n));if(!s)return;const i=a==="in_progress";z(e(i?"clearance.start_confirm_title":"clearance.complete_confirm_title"),`${s.code} · ${s.name}`,`<div class="clearance-confirm"><div class="clearance-confirm__icon">${ma(!0)}</div><p>${t(e(i?"clearance.start_confirm_body":"clearance.complete_confirm_body"))}</p></div>`,`<button type="button" class="btn" data-action-drawer-close>${t(e("clearance.cancel"))}</button><button type="button" class="btn ${i?"primary":"success"}" id="confirmClearanceStatus">${t(e("clearance.confirm_action"))}</button>`,e("clearance.workflow_title")),c("#confirmClearanceStatus").onclick=l=>is(s,a,l.currentTarget)}async function is(n,a,s){const i=N(v.clearanceStatusUrlTemplate,n.id);if(!i||!v.canEditTreatments){g(e("clearance.update_error"));return}s.disabled=!0,s.setAttribute("aria-busy","true");try{const l=await fetch(i,{method:"PATCH",headers:q(!0),credentials:"same-origin",body:JSON.stringify({status:a})}),r=await l.json().catch(()=>({}));if(!l.ok){g(r.message||e("clearance.update_error"));return}A(),g(r.message||e("clearance.status_updated")),await te()}catch(l){console.error(l),g(e("clearance.update_error"))}finally{s.isConnected&&(s.disabled=!1,s.removeAttribute("aria-busy"))}}function ls(n){const a=_e.find(d=>Number(d.id)===Number(n));if(!a)return;const s=a.order_id&&v.orderShowUrlTemplate?N(v.orderShowUrlTemplate,a.order_id):"",i=a.customer_id&&v.userEditUrlTemplate?N(v.userEditUrlTemplate,a.customer_id):"",l=`<div class="pay-review">
    <div class="pay-review__hero"><div class="pay-review__avatar">${t(a.initial||"?")}</div>
      <div style="min-width:0;flex:1"><div class="pay-id">${t(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${t(a.name||"")}</strong>
        <div class="pay-method">${t(a.phone||"—")} · ${t(a.treatment||"")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${x(a.clearance_label)}${x(a.payment_label)}</div>
      </div></div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${t(e("clearance.col_time"))}</label><strong>${t((a.date_label||"")+" "+(a.time||""))}</strong></div>
      <div class="pay-review__card"><label>${t(e("clearance.col_branch"))}</label><strong>${t(a.branch_name||a.branch||"—")}</strong></div>
      <div class="pay-review__card"><label>${t(e("checkin.col_beautician"))}</label><strong>${t(a.beautician||"—")}</strong></div>
    </div>
    <div class="clearance-decision clearance-decision--${t(a.clearance||"other")}"><strong>${t(a.clearance_label||"")}</strong><p>${t(ns(a))}</p></div>
    <div class="journey-section"><div class="journey-section__title">${t(e("clearance.workflow_title"))}</div><ol class="clearance-checklist">
      ${[[e("clearance.step_arrival"),!!a.checked_in_at||["in_treatment","done"].includes(a.clearance)],[e("clearance.step_payment"),!!a.payment_ok||["in_treatment","done"].includes(a.clearance)],[e("clearance.step_treatment"),["in_treatment","done"].includes(a.clearance)],[e("clearance.step_complete"),a.clearance==="done"]].map(([d,p])=>`<li class="${p?"is-done":""}"><span>${ma(p)}</span><strong>${t(d)}</strong></li>`).join("")}
    </ol></div></div>`,r=[Ot(a),a.checkin_pass_url?`<a class="btn" href="${t(a.checkin_pass_url)}" target="_blank" rel="noopener">${t(e("clearance.view_checkin_pass"))}</a>`:"",i&&v.canViewUser?`<a class="btn" href="${t(i)}" target="_blank" rel="noopener">${t(e("clearance.open_customer"))}</a>`:"",s&&v.canViewOrder?`<a class="btn" href="${t(s)}" target="_blank" rel="noopener">${t(e("clearance.open_order"))}</a>`:"",v.canViewTreatments&&v.treatmentReservationsUrl?`<a class="btn" href="${t(v.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("clearance.open_crm"))}</a>`:"",`<button type="button" class="btn" data-action-drawer-close>${t(e("clearance.close"))}</button>`].filter(Boolean).join("");z(e("clearance.title"),a.code+" · "+a.name,l,r,e("nav.clearance")),setTimeout(()=>Vt(c("#actionDrawerFoot")),0)}function rs(){j.innerHTML=`<div class="wal-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${t(e("wallet.title"))}</h1>
        <div class="page-subtitle">${t(e("wallet.subtitle"))} <span class="loyalty-scope-badge">${t(e("wallet.scope_badge"))}</span></div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="walRefresh">${t(e("wallet.refresh"))}</button>
        ${v.canViewLoyalty&&v.loyaltyMembersUrl?`<a class="btn primary" href="${t(v.loyaltyMembersUrl)}" target="_blank" rel="noopener">${t(e("wallet.open_loyalty"))}</a>`:""}
      </div>
    </div>
    <section class="clearance-flow loyalty-flow" aria-labelledby="loyaltyFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">LOYALTY</span><h2 id="loyaltyFlowTitle">${t(e("wallet.workflow_title"))}</h2><p>${t(e("wallet.workflow_hint"))}</p></div>
      <ol class="clearance-flow__steps">
        ${[e("wallet.step_enrolled"),e("wallet.step_earn"),e("wallet.step_tier"),e("wallet.step_redeem")].map((a,s)=>`<li><span aria-hidden="true">${s+1}</span><strong>${t(a)}</strong></li>`).join("")}
      </ol>
    </section>
    <div class="pay-metrics" id="walMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${t(e("wallet.subtitle"))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="walResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="walTabs" role="group"></div>
        <label class="lead-search" for="walSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="walSearch" aria-label="${t(e("wallet.search_placeholder"))}" type="search" autocomplete="off" placeholder="${t(e("wallet.search_placeholder"))}" value="${t(o.walletSearch||"")}" />
        </label>
        <div class="lead-filter-grid loyalty-filter-grid">
          <label class="lead-field"><span class="lead-field__label">${t(e("wallet.filter_tier"))}</span>
            <select class="lead-field__control" id="walTier"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="walTableMount"></div>
    </section>
  </div>`;const n=c("#walRefresh");n&&(n.onclick=()=>he()),os(),he()}function os(){const n=c("#walSearch");n&&(n.oninput=()=>{clearTimeout(Ja),Ja=setTimeout(()=>{o.walletSearch=n.value.trim(),o.walletPage=1,he()},320)});const a=c("#walTier");a&&(a.onchange=()=>{o.walletTier=a.value,o.walletPage=1,he()})}async function he(){const n=++Ke;He=!1;const a=v.walletUrl||"",s=c("#walTableMount");if(!a){s&&(s.innerHTML=`<div class="pay-empty"><strong>${t(e("wallet.load_error"))}</strong></div>`);return}pe=!0,mt(),_t();const i=new URLSearchParams;o.walletSearch&&i.set("q",o.walletSearch),o.walletSegment&&o.walletSegment!=="all"&&i.set("segment",o.walletSegment),o.walletTier&&o.walletTier!=="all"&&i.set("tier",o.walletTier),i.set("page",String(o.walletPage||1)),i.set("per_page","25");try{const l=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("wallet "+l.status);const r=await l.json();if(n!==Ke)return;De=Array.isArray(r.data)?r.data:[],Na=Object.assign({members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0},r.meta&&r.meta.summary||{}),$t=Object.assign({segments:[],tiers:[]},r.filters||{}),qa={current_page:r.meta&&r.meta.current_page||1,last_page:r.meta&&r.meta.last_page||1,total:r.meta&&r.meta.total||0}}catch(l){if(n!==Ke)return;He=!0,console.error(l),De=[],g(e("wallet.load_error"))}finally{if(n!==Ke)return;pe=!1,mt(),cs(),ds(),_t()}}function mt(){const n=c("#walMetrics");if(!n)return;const a=Na;n.setAttribute("aria-busy",String(pe));const s=i=>pe||He?"—":h(i);n.innerHTML=`
    <div class="pay-metric loyalty-metric loyalty-metric--members"><span>${t(e("wallet.stat_members"))}</span><strong>${s(a.members)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--active"><span>${t(e("wallet.stat_balance"))}</span><strong>${s(a.with_balance)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--points"><span>${t(e("wallet.stat_points"))}</span><strong>${s(a.points_outstanding)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--rewards"><span>${t(e("wallet.stat_stamp"))}</span><strong>${s(a.stamp_ready)}</strong></div>`}function cs(){const n=c("#walTabs");if(!n)return;const a=Na,s=[["all",e("wallet.tab_all"),a.members],["active",e("wallet.tab_active"),a.with_balance],["zero",e("wallet.tab_zero"),a.zero_balance],["stamp_ready",e("wallet.tab_stamp"),a.stamp_ready]];n.innerHTML=s.map(([i,l,r])=>{const d=o.walletSegment===i?"active":"";return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-waltab="${i}">${t(l)} (${h(r||0)})</button>`}).join(""),S("[data-waltab]",n).forEach(i=>i.onclick=()=>{o.walletSegment=i.dataset.waltab,o.walletPage=1,he()})}function ds(){const n=c("#walTier");if(n){const s=o.walletTier||"all";n.innerHTML=`<option value="all">${t(e("wallet.all_tiers"))}</option>`+($t.tiers||[]).map(i=>`<option value="${i.id}"${String(s)===String(i.id)?" selected":""}>${t(i.name)}</option>`).join("")}const a=c("#walResultCount");a&&(a.textContent=e("wallet.result_count",{count:He?"—":h(qa.total)}))}function _t(){const n=c("#walTableMount");if(!n)return;if(n.setAttribute("aria-busy",String(pe)),He){n.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("wallet.load_error"))}</strong><button type="button" class="btn" id="walRetry">${t(e("wallet.refresh"))}</button></div>`,c("#walRetry").onclick=()=>he();return}if(pe){n.innerHTML=`<div class="pay-empty" role="status">${t(e("wallet.loading"))}</div>`;return}if(!De.length){n.innerHTML=`<div class="pay-empty"><strong>${t(e("wallet.empty"))}</strong>${t(e("wallet.empty_hint"))}</div>`;return}const a=De.map(s=>{const i=s.avatar_url?`<div class="mini-avatar mini-avatar--photo"><img src="${t(s.avatar_url)}" alt=""></div>`:`<div class="mini-avatar">${t(s.initial||"?")}</div>`,l=s.stamp_ready?x(e("wallet.chip_stamp",{count:s.stamp_ready})):x(e("wallet.stamp_summary",{active:h(s.stamp_active||0),ready:0})),r=v.canShowLoyaltyMember&&v.loyaltyMemberShowUrlTemplate?N(v.loyaltyMemberShowUrlTemplate,s.id):"";return`<tr data-membership-segment="${t(s.segment||"zero")}">
      <td><div class="person-cell person-cell--lead">${i}<div class="person-cell__text"><strong>${t(s.name||"")}</strong><small>${t(s.code||"")} · ${t(s.phone||s.email||"—")}</small></div></div></td>
      <td><div class="loyalty-tier-cell">${s.tier?x(s.tier):"—"}<small>${t(e("wallet.tier_since"))}: ${t(s.tier_since_label||"—")}</small></div></td>
      <td class="is-num"><strong>${h(s.balance)}</strong></td>
      <td class="is-num">${R(s.lifetime_spend)}</td>
      <td><div class="loyalty-reward-cell">${l}<small>${t(e("wallet.stamp_summary",{active:h(s.stamp_active||0),ready:h(s.stamp_ready||0)}))}</small></div></td>
      <td><div class="loyalty-activity-cell"><strong>${t(e("wallet.activity_summary",{count:h(s.activity_count||0)}))}</strong><small>${t(s.last_activity_label||"—")}</small></div></td>
      <td class="lead-table__actions"><div class="lead-menu loyalty-action-menu">
        <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${t(e("wallet.row_actions",{name:s.name||e("wallet.guest")}))}"><span class="lead-menu__dots" aria-hidden="true"></span></button>
        <div class="lead-menu__panel" role="menu" hidden>
          ${r?`<a class="lead-menu__item" role="menuitem" href="${t(r)}" target="_blank" rel="noopener">${t(e("wallet.open_member"))}</a>`:""}
          <button type="button" class="lead-menu__item" role="menuitem" data-wal-view="${s.id}">${t(e("wallet.view"))}</button>
        </div>
      </div></td>
    </tr>`}).join("");n.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("wallet.col_customer"))}</th><th>${t(e("wallet.col_tier"))}</th><th class="is-num">${t(e("wallet.col_balance"))}</th>
    <th class="is-num">${t(e("wallet.col_spend"))}</th><th>${t(e("wallet.col_stamps"))}</th>
    <th>${t(e("wallet.col_activity"))}</th><th>${t(e("wallet.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${ne("wal",qa)}`,Ve(n),S('.loyalty-action-menu a[role="menuitem"]',n).forEach(s=>s.onclick=()=>H()),S("[data-wal-view]",n).forEach(s=>s.onclick=()=>{H(),ps(s.dataset.walView)}),se("wal",s=>(o.walletPage=s,he()))}let je=null,Fa="";function ps(n){const a=De.find(u=>Number(u.id)===Number(n));if(!a)return;A(),Z(),je=document.activeElement,Fa=document.body.style.overflow,document.body.style.overflow="hidden";const s=v.loyaltyMemberShowUrlTemplate?N(v.loyaltyMemberShowUrlTemplate,a.id):"",i=a.customer_id&&v.userEditUrlTemplate?N(v.userEditUrlTemplate,a.customer_id):"",l=(a.recent||[]).length?`<ol class="wallet-transactions">${a.recent.map(u=>`<li class="wallet-transaction">
        <div class="wallet-transaction__description"><strong>${t(u.description||u.type||"")}</strong><time>${t(u.created_label||"")}</time></div>
        <div class="wallet-transaction__amount"><strong class="${Number(u.points)>0?"is-credit":""}">${Number(u.points)>0?"+":""}${h(u.points)} <span>${t(e("wallet.col_balance"))}</span></strong><small>${t(e("wallet.transaction_balance",{balance:h(u.balance_after)}))}</small></div>
      </li>`).join("")}</ol>`:`<div class="pay-empty">${t(e("wallet.no_recent"))}</div>`,r=a.avatar_url?`<img class="wallet-detail__avatar" src="${t(a.avatar_url)}" alt="" />`:`<div class="wallet-detail__avatar" aria-hidden="true">${t(a.initial||"?")}</div>`,d=c("#leadDrawer");d.classList.add("wallet-drawer"),d.setAttribute("role","dialog"),d.setAttribute("aria-modal","true"),d.setAttribute("aria-labelledby","drawerName"),c("#leadDrawer .eyebrow").textContent=e("nav.wallet"),c("#drawerName").textContent=e("wallet.title"),c("#drawerClose").setAttribute("aria-label",e("wallet.close")),c("#drawerBody").innerHTML=`<div class="wallet-detail">
    <section class="wallet-detail__customer">${r}<div class="wallet-detail__identity">
      <span class="pay-id">${t(a.code||"")}</span>
      <h3>${t(a.name||"")}</h3>
      <p>${t(a.phone||"—")}</p><p>${t(a.email||"—")}</p>
      <div class="wallet-detail__badges">${a.tier?x(a.tier):""}${x(a.segment_label)}</div>
      <p>${t(e("wallet.member_since"))}: ${t(a.member_since_label||"—")} · ${t(e("wallet.tier_since"))}: ${t(a.tier_since_label||"—")}</p>
    </div></section>
    <section class="wallet-detail__balance"><span>${t(e("wallet.col_balance"))}</span><strong>${h(a.balance)}</strong></section>
    <div class="wallet-detail__metrics">
      <section><span>${t(e("wallet.col_spend"))}</span><strong>${R(a.lifetime_spend)}</strong></section>
      <section><span>${t(e("wallet.col_stamps"))}</span><strong>${t(e("wallet.stamp_summary",{active:h(a.stamp_active),ready:h(a.stamp_ready)}))}</strong></section>
      <section><span>${t(e("wallet.col_activity"))}</span><strong>${t(e("wallet.activity_summary",{count:h(a.activity_count||0)}))}</strong></section>
      <section><span>${t(e("wallet.last_activity"))}</span><strong>${t(a.last_activity_label||"—")}</strong></section>
    </div>
    <section class="wallet-detail__history"><h3>${t(e("wallet.detail_recent"))}</h3>${l}</section>
  </div>`;const p=document.createElement("div");p.id="walletDrawerFoot",p.className="wallet-drawer__footer",p.innerHTML=[s&&v.canShowLoyaltyMember?`<a class="btn primary" href="${t(s)}" target="_blank" rel="noopener">${t(e("wallet.open_member"))}</a>`:"",i&&v.canViewUser?`<a class="btn" href="${t(i)}" target="_blank" rel="noopener">${t(e("wallet.open_profile"))}</a>`:"",`<button type="button" class="btn" data-wallet-close>${t(e("wallet.close"))}</button>`].filter(Boolean).join(""),d.appendChild(p),c("[data-wallet-close]",p).onclick=Z,d.classList.add("show"),d.setAttribute("aria-hidden","false"),d.inert=!1,c(".app-shell").inert=!0,c("#drawerBackdrop").classList.add("show"),c("#drawerBody").scrollTop=0,c("#drawerClose").focus({preventScroll:!0})}document.addEventListener("keydown",n=>{const a=c("#actionDrawer.show")||c("#leadDrawer.show");if(!a)return;const s=a.id==="actionDrawer"?A:Z;if(n.key==="Escape"){n.preventDefault(),s();return}if(n.key!=="Tab")return;const i=S('button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex="0"]',a).filter(d=>d.getClientRects().length),l=i[0],r=i[i.length-1];if(!l){n.preventDefault();return}n.shiftKey&&(document.activeElement===l||!a.contains(document.activeElement))?(n.preventDefault(),r.focus()):!n.shiftKey&&(document.activeElement===r||!a.contains(document.activeElement))&&(n.preventDefault(),l.focus())});const Se=Object.fromEntries(["beauticians","branches","audit"].map(n=>[n,{q:"",sort:"revenue",page:1}]));let Xe=0,vt=null,oe=null;function us(){Oa("beauticians")}function ms(){Oa("branches")}function _s(){Oa("audit")}function Oa(n){const a=Se[n];j.innerHTML=`<div class="report-shell ${n==="beauticians"?"beautician-report-shell":n==="branches"?"branch-report-shell":n==="audit"?"audit-report-shell":""}">
    <div class="page-head"><div><h1 class="page-title">${t(e("nav."+n))}</h1><p class="page-subtitle">${t(e("reporting."+(n==="audit"?"audit_subtitle":"subtitle")))}</p></div>
      <button type="button" class="btn" id="reportRefresh">${t(e("reporting.refresh"))}</button></div>
    ${n==="beauticians"||n==="branches"||n==="audit"?`<section class="clearance-flow ${n==="beauticians"?"beautician-flow":n==="branches"?"branch-flow":"audit-flow"}" aria-labelledby="${n}FlowTitle"><div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">${n==="audit"?"CONTROL":"CRM"}</span><h2 id="${n}FlowTitle">${t(e("reporting."+(n==="beauticians"?"beautician":n==="branches"?"branch":"audit")+"_workflow_title"))}</h2><p>${t(e("reporting."+(n==="beauticians"?"beautician":n==="branches"?"branch":"audit")+"_workflow_hint"))}</p></div><ol class="clearance-flow__steps">${[1,2,3].map((i,l)=>`<li><span aria-hidden="true">${l+1}</span><strong>${t(e("reporting."+(n==="beauticians"?"beautician":n==="branches"?"branch":"audit")+"_step_"+i))}</strong></li>`).join("")}</ol></section>`:""}
    <div class="pay-metrics" id="reportMetrics" aria-live="polite"></div>
    <section class="lead-panel card"><div class="lead-panel__head"><div><h2 class="card-title">${t(e("reporting."+(n==="audit"?"recorded_imports":"performance")))}</h2><p class="lead-panel__sub" id="reportPeriod"></p></div><span class="lead-panel__count" id="reportCount">—</span></div>
      <div class="lead-panel__filters report-filters"><label class="lead-field"><span class="lead-field__label">${t(e("reporting.search"))}</span><input class="lead-field__control" type="search" id="reportSearch" maxlength="150" value="${t(a.q)}" placeholder="${t(e("reporting."+(n==="audit"?"search_batch":"search_name")))}"></label>
      ${n!=="audit"?`<label class="lead-field"><span class="lead-field__label">${t(e("reporting.sort"))}</span><select id="reportSort" class="lead-field__control">${["revenue","leads","conversion","orders"].map(i=>`<option value="${i}"${a.sort===i?" selected":""}>${t(e("reporting."+i))}</option>`).join("")}</select></label>`:""}</div>
      <div id="reportTable" class="lead-panel__body pay-table" aria-live="polite"></div>
    </section>
    <p class="report-note">${t(e("reporting."+(n==="audit"?"audit_note":n==="beauticians"?"methodology_beauticians":"methodology_branches")))}</p>
  </div>`,c("#reportRefresh").onclick=()=>Ee(),c("#reportSearch").oninput=i=>{a.q=i.target.value,a.page=1,clearTimeout(vt),n==="audit"?vt=setTimeout(()=>{o.view===n&&Ee()},350):oe&&ca(n)};const s=c("#reportSort");s&&(s.onchange=i=>{a.sort=i.target.value,oe&&ca(n)}),Ee()}async function Ee(){const n=o.view;if(!Se[n])return;const a=++Xe,s=Se[n],i=c("#reportTable");if(!i)return;oe=null,c("#reportMetrics").innerHTML="",i.setAttribute("aria-busy","true"),i.innerHTML=`<div class="pay-empty" role="status">${t(e("reporting.loading"))}</div>`;const l=new URLSearchParams({view:n,page:String(s.page)});o.period&&l.set("period",o.period),o.branch&&o.branch!=="all"&&l.set("branch",o.branch),n==="audit"&&s.q.trim()&&l.set("q",s.q.trim());try{if(!v.reportingUrl)throw new Error("Missing reporting endpoint");const r=await fetch(v.reportingUrl+"?"+l.toString(),{headers:q(!1),credentials:"same-origin"});if(!r.ok)throw new Error("Reporting "+r.status);const d=await r.json();if(a!==Xe||o.view!==n)return;oe=d;const p=d.meta.summary,u=n==="audit"?["batches","imported","duplicates","invalid"]:["leads","converted","orders","revenue"];c("#reportMetrics").innerHTML=u.map((m,_)=>`<div class="pay-metric ${n==="beauticians"?"beautician-metric beautician-metric--"+_:n==="branches"?"branch-metric branch-metric--"+_:n==="audit"?"audit-metric audit-metric--"+_:""}"><span>${t(e("reporting."+m))}</span><strong>${m==="revenue"?R(p[m]):h(p[m])}</strong></div>`).join(""),c("#reportPeriod").textContent=d.meta.period,ca(n)}catch{if(a!==Xe||o.view!==n)return;c("#reportCount").textContent="—",i.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("reporting.error"))}</strong><button type="button" class="btn" id="reportRetry">${t(e("reporting.refresh"))}</button></div>`,c("#reportRetry").onclick=()=>Ee()}finally{a===Xe&&o.view===n&&i.setAttribute("aria-busy","false")}}function ca(n){const a=c("#reportTable");if(!a||!oe)return;const s=Se[n];let i=[...oe.data||[]];n!=="audit"&&(i=i.filter(m=>String(m.name).toLocaleLowerCase().includes(s.q.trim().toLocaleLowerCase())),i.sort((m,_)=>Number(_[s.sort])-Number(m[s.sort])||String(m.name).localeCompare(String(_.name))));const l=n==="audit"?oe.meta.total:i.length,r=n==="audit"?oe.meta:{current_page:Math.max(1,Math.min(s.page,Math.ceil(l/25)||1)),last_page:Math.ceil(l/25)||1};n!=="audit"&&(s.page=r.current_page,i=i.slice((s.page-1)*25,s.page*25)),c("#reportCount").textContent=e("reporting.results",{count:h(l)});const d=n==="audit"?["code","date","actor","branch","method","status","raw","imported","duplicates","invalid"]:["name","leads","converted","conversion","follow_up","lost","orders","revenue","average_order"],p=["code","date","actor","branch","method","status","name"],u=n==="beauticians"||n==="branches"?`<th>${t(e("reporting.actions"))}</th>`:"";a.innerHTML=i.length?`<div class="table-wrap report-table-wrap"><table class="data-table report-table"><thead><tr>${d.map(m=>`<th${p.includes(m)?"":' class="is-num"'}>${t(e("reporting."+m))}</th>`).join("")}${u}</tr></thead><tbody>${i.map(m=>`<tr>${d.map(_=>{let b=p.includes(_)?t(_==="status"?e("reporting.status_"+m[_]):m[_]):_==="conversion"?Number(m.leads)>0?Number(m[_]).toFixed(1)+"%":"—":["revenue","average_order"].includes(_)?R(m[_]):h(m[_]);return`<td${p.includes(_)?"":' class="is-num"'}>${["name","code"].includes(_)?`<strong>${b}</strong>`:b}</td>`}).join("")}${n==="beauticians"||n==="branches"?`<td class="lead-table__actions"><div class="lead-menu ${n==="beauticians"?"beautician":"branch"}-action-menu"><button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${t(e("reporting.row_actions",{name:m.name||""}))}"><span class="lead-menu__dots" aria-hidden="true"></span></button><div class="lead-menu__panel" role="menu" hidden><button type="button" class="lead-menu__item" role="menuitem" data-report-leads="${m.id}" data-report-scope="${n}">${t(e("reporting.view_leads"))}</button></div></div></td>`:""}</tr>`).join("")}</tbody></table></div>`:`<div class="pay-empty"><strong>${t(e("reporting.empty"))}</strong>${t(e("reporting.empty_hint"))}</div>`,(n==="beauticians"||n==="branches")&&(Ve(a),S("[data-report-leads]",a).forEach(m=>m.onclick=()=>{H(),m.dataset.reportScope==="branches"?o.leadBranch=m.dataset.reportLeads:o.leadBeautician=m.dataset.reportLeads,A(),V("leads")}),S('.beautician-action-menu a[role="menuitem"],.branch-action-menu a[role="menuitem"]',a).forEach(m=>m.onclick=()=>H())),a.insertAdjacentHTML("beforeend",ne("report",r)),se("report",m=>{if(s.page=m,n==="audit")return Ee();ca(n)})}function vs(n,a){j.innerHTML=`${be(t(n),t(a))}<section class="card"><div class="empty">${t(a)}</div></section>`}function hs(){j.innerHTML=`${be(e("followup.title"),e("followup.subtitle"),`<button type="button" class="btn" data-jump="leads">${t(e("followup.open_workspace"))}</button>`)}
  <section class="follow-kpi-section" aria-labelledby="followKpiTitle">
    <div class="follow-kpi-section__head">
      <div><span class="follow-kpi-section__eyebrow">${t(e("followup.kpi_eyebrow"))}</span><h2 id="followKpiTitle">${t(e("followup.kpi_heading"))}</h2></div>
      <span class="follow-kpi-section__scope">${t(e("followup.kpi_scope"))}</span>
    </div>
    <div class="grid kpi-grid" id="followKpiMount"></div>
    <p class="card-subtitle">${t(e("followup.kpi_note"))}</p>
  </section>
  <section class="lead-panel card" style="margin-top:14px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${t(e("followup.title"))}</h2>
        <p class="lead-panel__sub">${t(e("followup.subtitle"))}</p>
      </div>
      <div class="lead-panel__head-meta">
        <span class="lead-panel__count" id="followResultCount">—</span>
      </div>
    </div>
    <div class="lead-panel__filters">
      <div class="tabs" id="followBuckets" role="tablist"></div>
      <label class="lead-search" for="followSearch">
        <span class="lead-search__icon" aria-hidden="true">⌕</span>
        <input class="lead-search__input" id="followSearch" type="search" autocomplete="off" placeholder="${t(e("followup.search_placeholder"))}" value="${t(o.followSearch)}" />
      </label>
      <div class="lead-filter-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
        <label class="lead-field">
          <span class="lead-field__label">${t(e("workspace.filter_beautician"))}</span>
          <select class="lead-field__control" id="followBeautician"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${t(e("workspace.filter_branch"))}</span>
          <select class="lead-field__control" id="followBranch"></select>
        </label>
      </div>
    </div>
    <div class="lead-panel__body" id="followTableMount"></div>
  </section>`,ie(),Wt(),ce()}async function ce(){const n=v.followUpsUrl||"";if(!n){g(e("followup.load_error"));return}const a=new URLSearchParams;o.followSearch&&a.set("q",o.followSearch),o.followBucket&&o.followBucket!=="all"&&a.set("bucket",o.followBucket);const s=o.leadBranch!=="all"?o.leadBranch:o.branch||"all";s&&s!=="all"&&a.set("branch",s),o.leadBeautician&&o.leadBeautician!=="all"&&a.set("beautician",o.leadBeautician),a.set("page",String(o.followPage||1)),aa=!0,o.view==="followup"&&ht();try{const i=await fetch(n+"?"+a.toString(),{headers:q(!1),credentials:"same-origin"});if(!i.ok)throw new Error("followups "+i.status);const l=await i.json();wt=l.meta||{},Le=Array.isArray(l.data)?l.data:[],Ma=l.meta&&l.meta.summary||Ma,xe=l.filters||xe,l.filters?.statuses&&(P.statuses=l.filters.statuses)}catch(i){console.error(i),g(e("followup.load_error"))}finally{aa=!1,o.view==="followup"&&(bs(),Wt(),ht())}}function Wt(){const n=xe.buckets||[{value:"all",label:e("followup.bucket_all")},{value:"overdue",label:e("followup.bucket_overdue")},{value:"due_today",label:e("followup.bucket_due_today")},{value:"no_response",label:e("followup.bucket_no_response")},{value:"lost",label:e("followup.bucket_lost")}],a=c("#followBuckets");a&&(a.innerHTML=n.map(r=>`<button type="button" class="tab ${String(o.followBucket)===String(r.value)?"active":""}" role="tab" data-follow-bucket="${t(r.value)}">${t(r.label)}</button>`).join(""),S("[data-follow-bucket]",a).forEach(r=>{r.onclick=()=>{o.followBucket=r.dataset.followBucket||"all",o.followPage=1,ce()}}));const s=c("#followBeautician");if(s){const r=[{id:"all",name:e("workspace.all_beauticians")},...xe.beauticians||P.beauticians||[]];s.innerHTML=r.map(d=>`<option value="${t(d.id)}" ${String(o.leadBeautician)===String(d.id)?"selected":""}>${t(d.name)}</option>`).join(""),s.onchange=d=>{o.leadBeautician=d.target.value,o.followPage=1,ce()}}const i=c("#followBranch");if(i){const r=[{id:"all",name:e("workspace.all_branches")},...xe.branches||v.branches||[]];i.innerHTML=r.map(d=>`<option value="${t(d.id)}" ${String(o.leadBranch)===String(d.id)?"selected":""}>${t(d.name)}</option>`).join(""),i.onchange=d=>{o.leadBranch=d.target.value,o.followPage=1,ce()}}const l=c("#followSearch");l&&(l.oninput=r=>{o.followSearch=r.target.value,clearTimeout(Ya),Ya=setTimeout(()=>{o.followPage=1,ce()},350)})}function bs(){const n=c("#followKpiMount");if(!n)return;const a=Ma||{};n.innerHTML=`
    ${I(U("queue"),e("followup.kpi_queue"),h(a.queue),e("followup.kpi_unit"),e("followup.kpi_queue_meta"),e("followup.kpi_queue_detail"),"blue")}
    ${I(U("overdue"),e("followup.kpi_overdue"),h(a.overdue),e("followup.kpi_unit"),e("followup.kpi_overdue_meta"),e("followup.kpi_overdue_detail"),"rose")}
    ${I(U("today"),e("followup.kpi_due_today"),h(a.due_today),e("followup.kpi_unit"),e("followup.kpi_due_meta"),e("followup.kpi_due_detail"),"green")}
    ${I(U("no_response"),e("followup.kpi_no_response"),h(a.no_response),e("followup.kpi_unit"),e("followup.kpi_no_response_meta"),e("followup.kpi_no_response_detail"),"purple")}
    ${I(U("lost"),e("followup.kpi_lost"),h(a.lost),e("followup.kpi_unit"),e("followup.kpi_lost_meta"),e("followup.kpi_lost_detail"),"teal")}
  `}function ht(){const n=c("#followTableMount");if(!n)return;const a=Array.isArray(Le)?Le.length:0,s=c("#followResultCount");if(s&&(s.textContent=a===1?e("followup.results_count_one"):e("followup.results_count",{count:h(a)})),aa){n.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${t(e("followup.loading"))}</strong></div>`;return}const i=Le;if(!i.length){n.innerHTML=`<div class="lead-empty">
      <div class="lead-empty__icon" aria-hidden="true">↻</div>
      <strong>${t(e("followup.empty"))}</strong>
      <p>${t(e("followup.empty_hint"))}</p>
      <button type="button" class="btn" data-jump="leads">${t(e("followup.open_workspace"))}</button>
    </div>`,ie();return}const l=!!v.canEditLead;n.innerHTML=`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${t(e("workspace.col_lead_id"))}</th>
    <th>${t(e("workspace.col_customer"))}</th>
    <th>${t(e("workspace.col_phone"))}</th>
    <th>${t(e("workspace.col_beautician"))}</th>
    <th>${t(e("workspace.col_branch"))}</th>
    <th>${t(e("workspace.col_status"))}</th>
    <th>${t(e("workspace.col_last_fu"))}</th>
    <th>${t(e("followup.col_waiting"))}</th>
    <th class="lead-table__actions"><span class="sr-only">${t(e("workspace.col_action"))}</span></th>
  </tr></thead><tbody>${i.map(r=>{const d=String(r.name||""),p=t((d[0]||"?").toUpperCase()),u=Number(r.days_since_followup||0),m=r.last_followed_up_at?e("followup.days",{count:u}):e("followup.never"),_=String(r.followup_bucket)==="overdue";return`<tr>
      <td><span class="lead-code">${t(r.code||r.id)}</span></td>
      <td><div class="person-cell person-cell--lead"><div class="mini-avatar" aria-hidden="true">${p}</div><div class="person-cell__text"><strong>${t(d||"—")}</strong><small>${t(r.customer||"")}</small></div></div></td>
      <td><span class="lead-mono">${t(r.phone||"—")}</span></td>
      <td>${t(r.beautician||"—")}</td>
      <td><span class="lead-branch">${t(r.branch||"—")}</span></td>
      <td>${x(r.status)}</td>
      <td><span class="lead-date">${t(r.last||"—")}</span></td>
      <td><span class="lead-wait${_?" is-overdue":""}">${t(m)}</span></td>
      <td class="lead-table__actions">
        <div class="lead-menu">
          <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${t(e("workspace.row_actions"))}">
            <span class="lead-menu__dots" aria-hidden="true"></span>
          </button>
          <div class="lead-menu__panel" role="menu" hidden>
            <button type="button" class="lead-menu__item" role="menuitem" data-follow-view="${t(r.id)}">${t(e("followup.view_lead"))}</button>
            ${l?`<button type="button" class="lead-menu__item" role="menuitem" data-follow-mark="${t(r.id)}">${t(e("followup.mark"))}</button>`:""}
          </div>
        </div>
      </td>
    </tr>`}).join("")}</tbody></table></div>`,n.insertAdjacentHTML("beforeend",ne("follow",wt)),se("follow",r=>(o.followPage=r,ce())),Ve(n),S("[data-follow-view]",n).forEach(r=>r.onclick=()=>{H(),ua(r.dataset.followView)}),S("[data-follow-mark]",n).forEach(r=>r.onclick=()=>{H(),Yt(r.dataset.followMark)})}async function Yt(n){const a=N(v.leadFollowUpUrlTemplate,n);if(!a){g(e("followup.mark_error"));return}try{const s=await fetch(a,{method:"POST",headers:q(!0),credentials:"same-origin",body:JSON.stringify({})}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("followup.mark_error"));return}g(i.message||e("followup.marked")),o.view==="followup"?await ce():await E()}catch(s){console.error(s),g(e("followup.mark_error"))}}function Kt(){const n=L||{},a=n.kpis||{},s=a.vs_prev||{},i=n.targets||{},l=n.dual||{},r=Number(l.sales_pct||n.target_board&&n.target_board.sales_pct||0),d=Number(i.sales||0),p=Number(a.sales||0),u=p-d,m=String(n.period&&n.period.label||e("common.this_month")),_=t(n.period&&n.period.label||e("common.this_month")),b=Array.isArray(n.sales_insights)?n.sales_insights:[],k=Array.isArray(n.waterfall)?n.waterfall:[],$=Math.max(1,...k.map(f=>Number(f.value||0)));j.innerHTML=`${be(e("sales.title"),e("sales.subtitle"),`<button type="button" class="btn soft" id="salesRefreshBtn">${t(e("sales.refresh"))}</button>
     <button type="button" class="btn" data-jump="payments">${t(e("sales.open_payments"))}</button>
     <button type="button" class="btn primary" data-jump="leads">${t(e("sales.open_leads"))}</button>`)}
  <section class="clearance-flow sales-flow" aria-labelledby="salesFlowTitle">
    <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CRM</span><h2 id="salesFlowTitle">${t(e("sales.workflow_title"))}</h2><p>${t(e("sales.workflow_hint"))}</p></div>
    <ol class="clearance-flow__steps">${[e("sales.step_leads"),e("sales.step_customer"),e("sales.step_revenue")].map((f,w)=>`<li><span aria-hidden="true">${w+1}</span><strong>${t(f)}</strong></li>`).join("")}</ol>
  </section>
  <section class="sales-kpi-section" aria-labelledby="salesKpiTitle">
    <div class="sales-kpi-section__head">
      <div><span class="sales-kpi-section__eyebrow">${t(e("sales.kpi_eyebrow"))}</span><h2 id="salesKpiTitle">${t(e("sales.kpi_heading"))}</h2></div>
      <span class="sales-kpi-section__scope">${t(e("sales.kpi_scope",{period:m}))}</span>
    </div>
    <div class="grid kpi-grid" id="salesKpiMount">
      ${I(U("revenue"),e("sales.kpi_sales"),C(a.sales),e("sales.kpi_unit_revenue"),ee(s.sales,"%"),le(e("sales.kpi_sales"),C(d)),"rose")}
      ${I(U("orders"),e("sales.kpi_orders"),h(a.orders||0),e("sales.kpi_unit_orders"),ee(s.orders||0,"%"),e("sales.kpi_orders_detail"),"blue")}
      ${I(U("customers"),e("sales.kpi_customers"),h(a.buyers),e("sales.kpi_unit_customers"),ee(s.buyers,"%"),le(e("sales.kpi_customers"),h(i.buyers||0)),"green")}
      ${I(U("average"),e("sales.kpi_avg"),C(a.avg_sale),e("sales.kpi_unit_revenue"),ee(s.avg_sale,"%"),le(e("sales.kpi_avg"),C(i.avg_sale||0)),"purple")}
      ${I(U("target"),e("sales.kpi_target"),G(r),e("sales.of_target"),u>=0?"+ "+C(Math.abs(u)):"− "+C(Math.abs(u)),e("sales.kpi_target_detail",{target:C(d),actual:C(p)}),"teal")}
    </div>
    <p class="card-subtitle">${t(e("sales.kpi_note"))}</p>
  </section>
  <section class="sales-insights card">
    <div class="sales-insights__head">
      <div>
        <div class="journey-section__title">${t(e("sales.insights"))}</div>
        <p class="card-subtitle" style="margin:0">${t(e("sales.insights_hint"))} · ${_}</p>
      </div>
      <span class="sales-insights__legend"><i class="is-ok"></i>${t(e("sales.insight_positive"))}<i class="is-warn"></i>${t(e("sales.insight_attention"))}</span>
    </div>
    <div class="sales-insights__grid">
      ${b.length?b.map(f=>`<article class="sales-insight sales-insight--${t(f.tone||"info")}"><span class="sales-insight__status">${t(f.tone==="ok"?e("sales.insight_positive"):f.tone==="warn"?e("sales.insight_attention"):e("sales.insight_info"))}</span><strong>${t(f.title||"")}</strong><p>${t(f.body||"")}</p></article>`).join(""):`<article class="sales-insight sales-insight--info"><strong>${t(e("sales.title"))}</strong><p>${t(e("sales.subtitle"))}</p></article>`}
    </div>
  </section>

  <div class="grid split-60" style="margin-top:12px">
    <section class="card sales-card">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${t(_)}</div>
          <div class="daily-panel__title">${t(e("sales.revenue_trend"))}</div>
          <div class="daily-panel__sub">${t(e("sales.revenue_sub"))}</div>
        </div>
        <span class="sales-card__pill">${C(p)}</span>
      </div>
      <div class="chart-wrap sales-card__chart"><canvas id="salesChart"></canvas></div>
    </section>
    <section class="card target-card ${r>=100?"target-card--over":""}">
      <div class="target-card__head">
        <div>
          <div class="target-card__eyebrow">${t(e("sales.target_card"))}</div>
          <div class="target-card__title">${t(e("overview.target_achievement"))}</div>
        </div>
        <span class="target-card__pill">${r>=100?t(e("overview.above_target")):t(e("overview.below_target"))}</span>
      </div>
      <div class="target-card__body">
        <div class="target-card__ring" style="--p:${Math.min(100,r)}">
          <svg viewBox="0 0 120 120" aria-hidden="true">
            <circle class="target-card__track" cx="60" cy="60" r="52"></circle>
            <circle class="target-card__prog" cx="60" cy="60" r="52"></circle>
          </svg>
          <div class="target-card__ring-value">
            <strong>${G(r)}</strong>
            <span>${t(e("sales.of_target"))}</span>
          </div>
        </div>
        <div class="target-card__side">
          <div class="target-card__stat"><span>${t(e("overview.target"))}</span><strong>${C(d)}</strong></div>
          <div class="target-card__stat"><span>${t(e("overview.actual"))}</span><strong>${C(p)}</strong></div>
          <div class="target-card__delta">
            <strong>${u>=0?"+":""}${C(u)}</strong>
            <span>${t(e("sales.gap"))}</span>
          </div>
        </div>
      </div>
    </section>
  </div>

  <section class="card" style="margin-top:12px">
    <div class="card-title-row">
      <div>
        <div class="card-title">${t(e("sales.pipeline"))}</div>
        <div class="card-subtitle">${t(e("sales.pipeline_sub"))}</div>
      </div>
      <span class="badge blue">${t(e("sales.conv_rate"))}: ${G(a.new_buyer_share_pct||0)}</span>
    </div>
    <div class="sales-funnel">
      ${k.length?k.map((f,w)=>{const M=Number(f.value||0),y=Math.max(8,Math.round(M/$*100));return`<div class="sales-funnel__step">
              <div class="sales-funnel__meta"><span>${t(f.name||"")}</span><strong>${h(M)}</strong></div>
              <div class="sales-funnel__bar"><span style="width:${y}%"></span></div>
            </div>`}).join(""):`<div class="empty"><strong>${t(e("sales.empty_pipeline"))}</strong></div>`}
    </div>
  </section>

  <section class="lead-panel card" style="margin-top:12px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${t(e("sales.attribution"))}</h2>
        <p class="lead-panel__sub">${t(e("sales.attribution_sub"))}</p>
      </div>
    </div>
    <div class="lead-panel__body">${fs()}</div>
  </section>

  <div class="grid three-col" style="margin-top:12px">
    ${(n.branches||[]).slice(0,6).map(f=>Tt(f.name,f.new_buyers||0,f.buyers||0,f.conv||0,f.sales||0,f.avg||0,f.buyers||0)).join("")||`<section class="card"><div class="empty"><strong>${t(e("sales.empty_branches"))}</strong></div></section>`}
  </div>`,ie(),c("#salesRefreshBtn")&&(c("#salesRefreshBtn").onclick=()=>sa()),requestAnimationFrame(()=>da())}function fs(){const n=L&&L.beauticians&&L.beauticians.length?L.beauticians:[];return n.length?`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${t(e("sales.col_rank"))}</th>
    <th>${t(e("sales.col_beautician"))}</th>
    <th class="is-num">${t(e("sales.col_sales"))}</th>
    <th class="is-num">${t(e("sales.col_customers"))}</th>
    <th class="is-num">${t(e("sales.col_orders"))}</th>
    <th class="is-num">${t(e("sales.col_avg"))}</th>
    <th class="is-num">${t(e("sales.col_leads"))}</th>
    <th class="is-num">${t(e("sales.col_conv"))}</th>
  </tr></thead><tbody>${n.map((a,s)=>{const i=String(a.name||"—"),l=t((i[0]||"?").toUpperCase());return`<tr>
      <td><span class="rank">${s+1}</span></td>
      <td><div class="person-cell person-cell--lead"><div class="mini-avatar" aria-hidden="true">${l}</div><div class="person-cell__text"><strong>${t(i)}</strong></div></div></td>
      <td class="is-num"><span class="lead-money">${C(a.sales||0)}</span></td>
      <td class="is-num">${h(a.buyers||0)}</td>
      <td class="is-num">${h(a.orders||a.order_count||0)}</td>
      <td class="is-num">${C(a.avg||0)}</td>
      <td class="is-num">${h(a.leads||0)}</td>
      <td class="is-num">${G(a.conv||0)}</td>
    </tr>`}).join("")}</tbody></table></div>`:`<div class="lead-empty"><strong>${t(e("sales.empty_beauticians"))}</strong></div>`}function gs(){const n=(v.userEditUrlTemplate||"").replace(/\/__ID__\/edit$/,"").replace(/\/__ID__$/,"")||"";j.innerHTML=`<div class="cus-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${t(e("customers.title"))}</h1>
        <div class="page-subtitle">${t(e("customers.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="cusRefresh">${t(e("customers.refresh"))}</button>
        ${v.canViewUser&&n?`<a class="btn primary" href="${t(n)}" target="_blank" rel="noopener">${t(e("customers.open_users"))}</a>`:""}
      </div>
    </div>
    <section class="clearance-flow customer-flow" aria-labelledby="customerFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CRM</span><h2 id="customerFlowTitle">${t(e("customers.workflow_title"))}</h2><p>${t(e("customers.workflow_hint"))}</p></div>
      <ol class="clearance-flow__steps">
        ${[e("customers.step_identify"),e("customers.step_engage"),e("customers.step_retain")].map((s,i)=>`<li><span aria-hidden="true">${i+1}</span><strong>${t(s)}</strong></li>`).join("")}
      </ol>
    </section>
    <div class="pay-metrics" id="cusMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro">
          <p class="lead-panel__sub" id="cusPulse" style="margin:0">${t(e("customers.subtitle"))}</p>
        </div>
        <div class="lead-panel__head-meta">
          <span class="lead-panel__count" id="cusResultCount">—</span>
        </div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="cusTabs" role="tablist"></div>
        <label class="lead-search" for="cusSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="cusSearch" type="search" autocomplete="off" placeholder="${t(e("customers.search_placeholder"))}" value="${t(o.customerSearch||"")}" />
        </label>
        <div class="lead-filter-grid" style="grid-template-columns:minmax(0,1fr)">
          <label class="lead-field">
            <span class="lead-field__label">${t(e("customers.filter_branch"))}</span>
            <select class="lead-field__control" id="cusBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="cusTableMount"></div>
    </section>
  </div>`;const a=c("#cusRefresh");a&&(a.onclick=()=>Me()),ys(),Me()}function ys(){const n=c("#cusSearch");n&&(n.oninput=()=>{clearTimeout(za),za=setTimeout(()=>{o.customerSearch=n.value.trim(),o.customerPage=1,Me()},320)});const a=c("#cusBranch");a&&(a.onchange=()=>{o.customerBranch=a.value,o.customerPage=1,Me()})}async function Me(){const n=v.customersUrl||"",a=c("#cusTableMount");if(!n){a&&(a.innerHTML=`<div class="pay-empty"><strong>${t(e("customers.load_error"))}</strong></div>`);return}na=!0,bt(),ft();const s=new URLSearchParams;o.customerSearch&&s.set("q",o.customerSearch),o.customerSegment&&o.customerSegment!=="all"&&s.set("segment",o.customerSegment);const i=o.customerBranch!=="all"?o.customerBranch:o.branch||"all";i&&i!=="all"&&s.set("branch",i),o.period&&s.set("period",o.period),s.set("page",String(o.customerPage||1)),s.set("per_page","25");try{const l=await fetch(`${n}?${s.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("customers "+l.status);const r=await l.json();$e=Array.isArray(r.data)?r.data:[],Aa=Object.assign({total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0},r.meta&&r.meta.summary||{}),Be=Object.assign({segments:[],branches:[]},r.filters||{}),qe={current_page:r.meta&&r.meta.current_page||1,last_page:r.meta&&r.meta.last_page||1,total:r.meta&&r.meta.total||0}}catch(l){console.error(l),$e=[],g(e("customers.load_error"))}finally{na=!1,bt(),$s(),ws(),ft()}}function bt(){const n=Aa,a=c("#cusPulse");a&&(a.textContent=qe.total>0?e("customers.pulse_busy",{count:h(qe.total)}):e("customers.pulse_clear"));const s=c("#cusMetrics");s&&(s.innerHTML=`
      <div class="pay-metric customer-metric customer-metric--total"><span>${t(e("customers.stat_total"))}</span><strong>${h(n.total)}</strong></div>
      <div class="pay-metric customer-metric customer-metric--buyers"><span>${t(e("customers.stat_buyers"))}</span><strong>${h(n.buyers)}</strong></div>
      <div class="pay-metric customer-metric customer-metric--new"><span>${t(e("customers.stat_new"))}</span><strong>${h(n.new_buyers)}</strong></div>
      <div class="pay-metric customer-metric customer-metric--sales"><span>${t(e("customers.stat_sales"))}</span><strong>${R(n.period_sales)}</strong></div>
    `)}function $s(){const n=c("#cusTabs");if(!n)return;const a=Aa,s={all:a.total||0,buyers:a.buyers||0,new:a.new_buyers||0,returning:a.returning||0,leads:a.with_leads||0},i=Be.segments&&Be.segments.length?Be.segments:[{value:"all",label:e("customers.tab_all")},{value:"buyers",label:e("customers.tab_buyers")},{value:"new",label:e("customers.tab_new")},{value:"returning",label:e("customers.tab_returning")},{value:"leads",label:e("customers.tab_leads")}];n.innerHTML=i.map(l=>{const r=l.value,d=o.customerSegment===r?"active":"",p=s[r],u=p!==void 0?`<span class="pay-tab-count">${h(p)}</span>`:"";return`<button type="button" class="tab ${d}" role="tab" data-ctab="${t(r)}">${t(l.label)}${u}</button>`}).join(""),S("[data-ctab]",n).forEach(l=>{l.onclick=()=>{o.customerSegment=l.dataset.ctab,o.customerPage=1,Me()}})}function ws(){const n=c("#cusBranch");if(n){const a=o.customerBranch;n.innerHTML=`<option value="all">${t(e("customers.all_branches"))}</option>`+(Be.branches||[]).map(s=>`<option value="${s.id}" ${String(a)===String(s.id)?"selected":""}>${t(s.code?s.code+" · "+s.name:s.name)}</option>`).join("")}}function ft(){const n=c("#cusTableMount"),a=c("#cusResultCount");if(a&&(a.textContent=e("customers.result_count",{count:h(qe.total||$e.length)})),!n)return;if(na){n.innerHTML=`<div class="pay-empty"><strong>${t(e("customers.loading"))}</strong></div>`;return}if(!$e.length){n.innerHTML=`<div class="pay-empty"><strong>${t(e("customers.empty"))}</strong><span>${t(e("customers.empty_hint"))}</span></div>`;return}const s=$e.map(l=>{const r=[`<span class="pay-chip ${l.segment==="new"||l.segment==="buyer"?"pay-chip--ok":"pay-chip--muted"}">${t(l.segment_label||"")}</span>`,l.has_lead?`<span class="pay-chip pay-chip--warn">${t(e("customers.chip_lead"))}</span>`:"",l.loyalty_tier?`<span class="pay-chip pay-chip--muted">${t(l.loyalty_tier)}</span>`:""].filter(Boolean).join(""),d=l.avatar_url?`<div class="mini-avatar mini-avatar--photo"><img src="${t(l.avatar_url)}" alt=""></div>`:`<div class="mini-avatar">${t(l.initial||"?")}</div>`,p=v.canViewUser&&v.userEditUrlTemplate?N(v.userEditUrlTemplate,l.id):"";return`<tr data-customer-segment="${t(l.segment||"registered")}">
      <td><span class="pay-id">${t(l.code||"CUS-"+l.id)}</span></td>
      <td><div class="person-cell">${d}<div><strong>${t(l.name||"")}</strong><small>${t(l.phone||l.email||"")}</small></div></div></td>
      <td>${t(l.branch||"—")}</td>
      <td><strong>${h(l.paid_orders_count)}</strong><div class="pay-method">${h(l.orders_count)} total</div></td>
      <td><div class="pay-amount">${R(l.paid_sales)}</div><div class="pay-method">${R(l.period_sales)} ${t(e("customers.detail_period").toLowerCase())}</div></td>
      <td>${t(l.last_order_label||"—")}</td>
      <td><div class="pay-chips">${r}</div></td>
      <td class="lead-table__actions"><div class="lead-menu customer-action-menu">
        <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${t(e("customers.row_actions",{name:l.name||e("customers.guest")}))}"><span class="lead-menu__dots" aria-hidden="true"></span></button>
        <div class="lead-menu__panel" role="menu" hidden>
          <button type="button" class="lead-menu__item" role="menuitem" data-customer="${l.id}">${t(e("customers.view"))}</button>
          <button type="button" class="lead-menu__item" role="menuitem" data-customer-payments="${l.id}" data-customer-name="${t(l.name||"")}" data-customer-phone="${t(l.phone||"")}" data-customer-email="${t(l.email||"")}">${t(e("customers.open_payments"))}</button>
          ${p?`<a class="lead-menu__item" role="menuitem" href="${t(p)}" target="_blank" rel="noopener">${t(e("customers.open_profile"))}</a>`:""}
        </div>
      </div></td>
    </tr>`}).join(""),i=ne("cus",qe);n.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("customers.col_id"))}</th>
    <th>${t(e("customers.col_customer"))}</th>
    <th>${t(e("customers.col_branch"))}</th>
    <th>${t(e("customers.col_orders"))}</th>
    <th>${t(e("customers.col_sales"))}</th>
    <th>${t(e("customers.col_last"))}</th>
    <th>${t(e("customers.col_segment"))}</th>
    <th>${t(e("customers.col_action"))}</th>
  </tr></thead><tbody>${s}</tbody></table></div>${i}`,Ve(n),S("[data-customer]",n).forEach(l=>l.onclick=()=>{H(),ks(Number(l.dataset.customer))}),S("[data-customer-payments]",n).forEach(l=>l.onclick=()=>{H(),Ht({id:l.dataset.customerPayments,name:l.dataset.customerName,phone:l.dataset.customerPhone,email:l.dataset.customerEmail})}),S('.customer-action-menu a[role="menuitem"]',n).forEach(l=>l.onclick=()=>H()),se("cus",l=>(o.customerPage=l,Me()))}function ks(n){const a=$e.find(p=>Number(p.id)===Number(n));if(!a)return;const s=N(v.userEditUrlTemplate,a.id),i=!!v.canViewUser,r=`<div class="pay-review">
    <div class="pay-review__hero">
      ${a.avatar_url?`<div class="pay-review__avatar" style="padding:0;overflow:hidden"><img src="${t(a.avatar_url)}" alt="" style="width:100%;height:100%;object-fit:cover"></div>`:`<div class="pay-review__avatar">${t(a.initial||"?")}</div>`}
      <div style="min-width:0;flex:1">
        <div class="pay-id">${t(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${t(a.name||"")}</strong>
        <div class="pay-method">${t(a.phone||"—")} · ${t(a.email||"—")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${x(a.segment_label)}${a.loyalty_tier?x(a.loyalty_tier):""}</div>
      </div>
      <div style="text-align:right"><div class="pay-amount">${R(a.paid_sales)}</div><div class="pay-method">${t(e("customers.detail_sales"))}</div></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${t(e("customers.detail_orders"))}</label><strong>${h(a.paid_orders_count)}</strong></div>
      <div class="pay-review__card"><label>${t(e("customers.detail_period"))}</label><strong>${R(a.period_sales)}</strong></div>
      <div class="pay-review__card"><label>${t(e("customers.detail_leads"))}</label><strong>${h(a.leads_count)}</strong></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${t(e("customers.col_branch"))}</label><strong>${t(a.branch_name||a.branch||"—")}</strong></div>
      <div class="pay-review__card"><label>${t(e("customers.col_last"))}</label><strong>${t(a.last_order_label||"—")}</strong></div>
      <div class="pay-review__card"><label>${t(e("customers.col_orders"))}</label><strong>${h(a.orders_count)}</strong></div>
    </div>
  </div>`,d=[i?`<a class="btn primary" href="${t(s)}" target="_blank" rel="noopener">${t(e("customers.open_profile"))}</a>`:"",`<button type="button" class="btn" data-jump="payments" data-customer-id="${t(String(a.id))}" data-customer-name="${t(a.name||"")}" data-customer-phone="${t(a.phone||"")}" data-customer-email="${t(a.email||"")}">${t(e("customers.open_payments"))}</button>`,`<button type="button" class="btn" data-action-drawer-close>${t(e("customers.close"))}</button>`].filter(Boolean).join("");z(e("customers.title"),a.code+" · "+a.name,r,d,"Customer"),setTimeout(()=>{S("#actionDrawerFoot [data-jump]").forEach(p=>p.onclick=()=>{if(A(),p.dataset.jump==="payments"&&p.dataset.customerId){Ht({id:p.dataset.customerId,name:p.dataset.customerName,phone:p.dataset.customerPhone,email:p.dataset.customerEmail});return}V(p.dataset.jump)})},0)}function zt(){const n=c("#donutChart");if(!n)return;We(n);const a=n.getContext("2d"),s=n.clientWidth,i=n.clientHeight,l=L&&L.status_mix||[],r=["#2563eb","#0ea5e9","#059669","#d97706","#e11d48","#6366f1"],d=l.map(($,f)=>({label:$.name,value:Number($.value||0),color:r[f%r.length]})),p=d.reduce(($,f)=>$+f.value,0);if(!d.length||p<=0){a.clearRect(0,0,s,i),a.fillStyle="#94a3b8",a.font="12px Poppins,sans-serif",a.textAlign="center",a.fillText(e("overview.no_branch_data"),s/2,i/2);const $=c("#leadLegend");$&&($.innerHTML="");return}let u=-Math.PI/2;const m=s/2,_=i/2,b=Math.min(s,i)/2-8;d.forEach($=>{const f=u+$.value/p*Math.PI*2;a.beginPath(),a.moveTo(m,_),a.arc(m,_,b,u,f),a.closePath(),a.fillStyle=$.color,a.fill(),u=f}),a.beginPath(),a.arc(m,_,b*.58,0,Math.PI*2),a.fillStyle="#fff",a.fill();const k=c("#leadLegend");k&&(k.innerHTML=d.map($=>`<div class="legend-row"><span class="dot" style="background:${$.color}"></span><span>${t($.label)}</span><strong>${h($.value)}</strong><span class="pct">${($.value/p*100).toFixed(1)}%</span></div>`).join(""))}function da(){const n=c("#salesChart");if(!n)return;We(n);const a=n.getContext("2d"),s=n.clientWidth,i=n.clientHeight,l=L&&L.equity||{},r=Array.isArray(l.labels)?l.labels:[];let d;l.actual&&l.actual.length?(d=l.actual.map((y,T)=>T===0?Number(y):Math.max(0,Number(y)-Number(l.actual[T-1]))),d=d.map(y=>Math.round(y/1e3))):d=[];const p=Math.round((L&&L.targets&&L.targets.sales||0)/1e3),u={l:36,r:16,t:28,b:36},m=Math.max(p,...d,1)*1.15,_=y=>u.l+y/Math.max(d.length-1,1)*(s-u.l-u.r),b=y=>i-u.b-y/m*(i-u.t-u.b);a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let y=0;y<4;y++){const T=u.t+(i-u.t-u.b)/3*y;a.beginPath(),a.moveTo(u.l,T),a.lineTo(s-u.r,T),a.stroke()}const k=b(p);a.setLineDash([5,5]),a.strokeStyle="#d97706",a.beginPath(),a.moveTo(u.l,k),a.lineTo(s-u.r,k),a.stroke(),a.setLineDash([]),a.fillStyle="#64748b",a.font="600 10px Poppins",a.textAlign="left",a.fillText("Target",u.l+4,k-6);const $="#1d4ed8",f="#0ea5e9",w=a.createLinearGradient(0,u.t,0,i-u.b);w.addColorStop(0,"rgba(37,99,235,.22)"),w.addColorStop(1,"rgba(14,165,233,.02)"),a.beginPath(),a.moveTo(_(0),i-u.b),d.forEach((y,T)=>a.lineTo(_(T),b(y))),a.lineTo(_(d.length-1),i-u.b),a.closePath(),a.fillStyle=w,a.fill(),a.beginPath(),d.forEach((y,T)=>T?a.lineTo(_(T),b(y)):a.moveTo(_(T),b(y))),a.strokeStyle=$,a.lineWidth=2.75,a.lineJoin="round",a.lineCap="round",a.stroke();const M=Math.max(1,Math.ceil(d.length/8));d.forEach((y,T)=>{if(T%M&&T!==d.length-1)return;const B=_(T),D=b(y);a.beginPath(),a.arc(B,D,4,0,Math.PI*2),a.fillStyle="#fff",a.fill(),a.lineWidth=2,a.strokeStyle=f,a.stroke(),a.fillStyle="#0f172a",a.font="700 10px Poppins",a.textAlign="center",a.fillText("RM"+y+"k",B,D-10),a.fillStyle="#64748b",a.font="600 10px Poppins",a.fillText(r[T]||"",B,i-12)})}function Ss(){if(!c("#branchChart"))return;const n=c("#branchChart"),a=n.getContext("2d"),s=n.clientWidth,i=n.clientHeight,l={l:45,r:20,t:20,b:35};We(n);const r=Array.isArray(L&&L.branches)?L.branches:[],d=r.map(b=>Number(b.new_buyer_share_pct)||Number(b.conv)||0),p=r.map(b=>String(b.name||"").slice(0,2).toUpperCase()||"—");if(!d.length)return;const u=Math.max(...d,50),m=Math.max(8,(s-l.l-l.r-70*d.length)/(d.length+1)),_=Math.min(70,(s-l.l-l.r-m*(d.length+1))/d.length);d.forEach((b,k)=>{const $=l.l+m+(_+m)*k,f=i-l.b-b/u*(i-l.t-l.b);a.fillStyle=[re("--brand","#38bdf8"),re("--rose","#0ea5e9"),re("--navy","#2563eb")][k%3],a.fillRect($,f,_,i-l.b-f),a.fillStyle=re("--navy","#1d4ed8"),a.textAlign="center",a.font="700 14px Poppins",a.fillText(b+"%",$+_/2,f-8),a.font="13px Poppins",a.fillText(p[k],$+_/2,i-12)})}function We(n){const a=Math.max(1,window.devicePixelRatio||1),s=n.getBoundingClientRect();n.width=s.width*a,n.height=s.height*a,n.getContext("2d").setTransform(a,0,0,a,0,0)}const Va=["overview","leads","import","imports","followup","sales","payments","customers","wallet","checkin","clearance","beauticians","branches","audit"],Ae=String(v.basePath||"").replace(/\/$/,"")||"/admin/leads/central";function gt(n){const a=Va.includes(n)?n:"overview";return a==="overview"?Ae:Ae+"/"+a}function Jt(n){const a=String(location.pathname).replace(/\/$/,"");if(a===Ae)return"overview";if(a.startsWith(Ae+"/")){const s=a.slice(Ae.length+1).split("/")[0];return Va.includes(s)?s:"overview"}return"overview"}function yt(){const n=new URLSearchParams(location.search);return n.set("branch",o.branch||"all"),o.period?n.set("period",o.period):n.delete("period"),"?"+n.toString()}function Ms(n,{replace:a=!1,silent:s=!1}={}){const i=gt(n)+yt()+location.hash;!s&&(a||i!==location.pathname+location.search+location.hash)&&history[a?"replaceState":"pushState"]({view:n},"",i),S(".nav-item[data-view]").forEach(l=>l.href=gt(l.dataset.view)+yt())}function Ts(){const n=new URLSearchParams(location.search),a=n.get("branch")||"all",s=n.get("period")||c("#periodScope").options[0]?.value||"",i=c("#branchScope"),l=c("#periodScope");i.value=a,o.branch=i.value||"all",i.value=o.branch,/^\d{4}-(0[1-9]|1[0-2])$/.test(s)&&![...l.options].some(r=>r.value===s)&&l.add(new Option(s,s)),l.value=s,o.period=l.value||l.options[0]?.value||"",l.value=o.period}function Gt(){o.branch=c("#branchScope").value||"all",o.period=c("#periodScope").value||"",Se[o.view]&&(Se[o.view].page=1),V(o.view)}function V(n,a={}){Ye();const s=Va.includes(n)?n:"overview";Z(),A(),window.IMMA_TRADE&&s!=="overview"&&IMMA_TRADE.dispose(),o.view=s;const i=s==="wallet";for(const r of[c("#branchScope"),c("#periodScope")])r.disabled=i,r.title=i?e("wallet.scope_hint"):"";Ms(s,a),S(".nav-item[data-view]").forEach(r=>r.classList.toggle("active",r.dataset.view===s)),c("#crumbCurrent").textContent={overview:e("common.overview_crumb"),leads:e("nav.leads"),import:e("nav.import"),imports:e("nav.imports"),payments:e("nav.payments"),wallet:e("nav.wallet"),checkin:e("nav.checkin"),clearance:e("nav.clearance"),beauticians:e("nav.beauticians"),branches:e("nav.branches"),audit:e("nav.audit"),followup:e("nav.followup"),sales:e("nav.sales"),customers:e("nav.customers")}[s]||s;const l=ka!==JSON.stringify([o.branch,o.period]);["overview","sales"].includes(s)&&l?(j.innerHTML=`<section class="card"><div class="pay-empty" role="status">${t(e("overview.loading"))}</div></section>`,sa()):(({overview:Mt,leads:gn,import:Ln,imports:An,followup:hs,sales:Kt,payments:Dt,customers:gs,wallet:rs,checkin:Fn,clearance:Zn,beauticians:us,branches:ms,audit:_s}[s]||(()=>vs(e("nav."+s),e("operations.not_ready"))))(),l&&sa()),a.silent||window.scrollTo({top:0,behavior:"smooth"}),innerWidth<1e3&&c("#sidebar").classList.remove("open")}function ie(){S("[data-jump]").forEach(n=>n.onclick=()=>{n.dataset.importTab&&(o.importTab=n.dataset.importTab),V(n.dataset.jump)})}let Ze=null,Xt="";function z(n,a,s,i,l=""){Z(),A(),Ze=document.activeElement,Xt=document.body.style.overflow,c("#actionDrawerEyebrow").textContent=l,c("#actionDrawerTitle").textContent=n,c("#actionDrawerBody").innerHTML=`<p class="action-drawer__subtitle">${t(a)}</p>${s}`,c("#actionDrawerFoot").innerHTML=i,c("#actionDrawerClose").setAttribute("aria-label",e("wallet.close")),c("#actionDrawer").classList.add("show"),c("#actionDrawer").setAttribute("aria-hidden","false"),c("#actionDrawer").inert=!1,c("#actionDrawerBackdrop").classList.add("show"),c(".app-shell").inert=!0,document.body.style.overflow="hidden",c("#actionDrawerBody").scrollTop=0,c("#actionDrawerClose").focus({preventScroll:!0});const r=c("#saveLead");r&&(r.onclick=()=>Mn()),S("[data-action-drawer-close]",c("#actionDrawer")).forEach(d=>d.onclick=A)}function A(){const n=c("#actionDrawer");n.classList.contains("show")&&(oa(),n.classList.remove("show"),n.setAttribute("aria-hidden","true"),n.inert=!0,c("#actionDrawerBackdrop").classList.remove("show"),c(".app-shell").inert=!1,document.body.style.overflow=Xt,Ze?.isConnected&&Ze.focus({preventScroll:!0}),Ze=null)}function Z(){const n=c("#leadDrawer");n.classList.contains("show")&&(n.classList.remove("show"),n.inert=!0,c(".app-shell").inert=!1,c("#drawerBackdrop").classList.remove("show"),n.setAttribute("aria-hidden","true"),n.classList.contains("wallet-drawer")&&(n.classList.remove("wallet-drawer"),c("#walletDrawerFoot")?.remove()),document.body.style.overflow=Fa,je?.isConnected&&je.focus({preventScroll:!0}),je=null)}let ja={};function Qt(){return String(v.notificationStorageKey||"imma-central.notifications.v1.guest")}function Cs(){return`${o.branch||"all"}|${o.period||""}`}function Zt(n){return`${Cs()}|${n.id}|${n.count}`}function en(){try{const n=JSON.parse(localStorage.getItem(Qt())||"{}");return n&&typeof n=="object"&&!Array.isArray(n)?n:{}}catch{return{...ja}}}function Ls(n){const a=Date.now()-7776e6,s=Object.entries(n).filter(([,i])=>Number(i)>=a).sort((i,l)=>Number(i[1])-Number(l[1])).slice(-100);ja=Object.fromEntries(s);try{localStorage.setItem(Qt(),JSON.stringify(ja))}catch{}}function wa(n){const a={payments:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/></svg>',clearance:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>',leads:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M3 20c.7-4 2.7-6 6-6 2.1 0 3.7.8 4.8 2.3M18 8v6M15 11h6"/></svg>'};return a[n]||a.leads}function an(){const n=L||{},a=[],s=Number(n.ops?.payments?.queue||0),i=Number(n.ops?.clearance?.queue||0),l=Number(n.kpis?.new_buyers||n.kpis?.unique_leads||0);return s>0&&a.push({id:"payments",count:s,icon:wa("payments"),title:e("notifications.payment_title"),detail:e("notifications.payment_detail",{count:h(s)}),view:"payments"}),i>0&&a.push({id:"clearance",count:i,icon:wa("clearance"),title:e("notifications.clearance_title"),detail:e("notifications.clearance_detail",{count:h(i)}),view:"clearance"}),l>0&&a.push({id:"leads",count:l,icon:wa("leads"),title:e("notifications.leads_title"),detail:e("notifications.leads_detail",{count:h(l),period:n.period?.label||e("notifications.selected_period")}),view:"leads"}),a}function tn(){const n=en();return an().filter(a=>!n[Zt(a)])}function xs(n){const a=en(),s=Date.now();n.forEach(i=>{a[Zt(i)]=s}),Ls(a)}function _a(){const n=c("#notificationButton"),a=c("#notificationMenu"),s=c("#notificationCount");if(!n||!a||!s)return;const i=an(),l=tn();s.textContent=String(l.length),s.hidden=l.length===0,n.setAttribute("aria-label",l.length?e("notifications.button_count",{count:h(l.length)}):e("notifications.button"));const r=l.length?e("notifications.active_count",{count:h(l.length)}):i.length?e("notifications.cleared"):e("notifications.all_clear"),d=i.length?e("notifications.cleared_hint"):e("notifications.empty");a.innerHTML=`<div class="notification-menu__head"><div class="notification-menu__title"><span id="notificationMenuTitle">${t(e("notifications.title"))}</span><small>${t(r)}</small></div>${l.length?`<button type="button" class="notification-menu__clear" data-clear-notifications>${t(e("notifications.clear"))}</button>`:""}</div>`+(l.length?l.map(p=>`<button type="button" class="notification-menu__item" data-notification-view="${t(p.view)}"><span class="notification-menu__icon">${p.icon}</span><span><strong>${t(p.title)}</strong><small>${t(p.detail)}</small></span></button>`).join("")+`<p class="notification-menu__note">${t(e("notifications.clear_note"))}</p>`:`<div class="notification-menu__empty">${t(d)}</div>`)}function Bs(){const n=tn();if(!n.length)return;Ye();const a=`<div class="notification-clear-summary"><strong>${t(e("notifications.clear_summary",{count:h(n.length)}))}</strong><p>${t(e("notifications.clear_note"))}</p></div>`,s=`<button type="button" class="btn" data-action-drawer-close>${t(e("notifications.cancel"))}</button><button type="button" class="btn danger" id="notificationClearConfirm">${t(e("notifications.confirm_clear"))}</button>`;z(e("notifications.clear_title"),e("notifications.clear_subtitle"),a,s,e("notifications.title")),c("#notificationClearConfirm").onclick=()=>{xs(n),A(),_a(),c("#notificationButton")?.focus({preventScroll:!0}),g(e("notifications.clear_success"))}}function Ps(n=null){const a=c("#notificationButton"),s=c("#notificationMenu");if(!a||!s)return;const i=n===null?s.hidden:!n;!s.hidden!==i&&(_a(),s.hidden=!i,a.setAttribute("aria-expanded",String(i)))}function Ye(){const n=c("#notificationButton"),a=c("#notificationMenu");!n||!a||a.hidden||(a.hidden=!0,n.setAttribute("aria-expanded","false"))}function g(n){const a=c("#toast");a.textContent=n,a.classList.add("show"),clearTimeout(g._t),g._t=setTimeout(()=>a.classList.remove("show"),2300)}window.showToast=g;window.navigate=V;window.$=c;S(".nav-item[data-view]").forEach(n=>n.addEventListener("click",a=>{a.defaultPrevented||a.metaKey||a.ctrlKey||a.shiftKey||a.altKey||a.button!==0||(a.preventDefault(),n.dataset.view==="payments"&&(o.paymentCustomerId=null,o.paymentCustomerLabel=""),V(n.dataset.view))}));let Ne=null;function nn(){const n=c("#crumbCurrent").textContent,a=[c("#branchScope"),c("#periodScope"),...S('select,input[type="search"],input[type="date"]',j)].filter(i=>i&&!i.closest("[hidden]")).map(i=>{const l=i.tagName==="SELECT"?i.selectedOptions[0]?.textContent:i.value;if(!l?.trim())return"";const r=i.closest("label")?.querySelector(".lead-field__label")?.textContent||i.parentElement.querySelector("label")?.textContent||"";return r?r.trim()+": "+l.trim():l.trim()}).filter(Boolean);for(const i of[c("#branchScope"),c("#periodScope")]){const l=i.selectedOptions[0]?.textContent?.trim();l&&!a.includes(l)&&a.unshift(l)}S('.tab.active,[role="tab"][aria-selected="true"]',j).forEach(i=>a.push(i.textContent.trim())),c("#centralPrintHeader").innerHTML=`<div class="central-print-brand">${t(e("brand_subtitle"))}</div><h1>${t(n)}</h1><p>${t([...new Set(a)].join(" · "))}</p><p>${t(e("export_pdf.generated"))}: ${t(new Date().toLocaleString(v.locale==="ms"?"ms-MY":"en-MY"))}</p><small>${t(e("export_pdf.scope"))}</small>`;const s=["workspace","payments","customers","wallet","checkin","clearance"].map(i=>e(i+".col_action").toLowerCase());S("table",j).forEach(i=>{S("thead tr:last-child th",i).forEach((r,d)=>{s.includes(r.textContent.trim().toLowerCase())&&(r.classList.add("central-print-action"),S("tbody tr",i).forEach(p=>p.children[d]?.classList.add("central-print-action")))})}),Ne===null&&(Ne=document.title),document.title="Central - "+n+" - "+(o.period||"")}function js(){Ne!==null&&(document.title=Ne,Ne=null),S(".central-print-action",j).forEach(n=>n.classList.remove("central-print-action"))}c("#exportCentralPdf").onclick=async()=>{if({leads:ea,followup:aa,payments:ta,customers:na,wallet:pe,checkin:me,clearance:ve}[o.view]||c('[aria-busy="true"],[role="status"]',j)){g(e("export_pdf.loading"));return}const a=c("#exportCentralPdf");a.disabled=!0;try{document.fonts?.ready&&await document.fonts.ready,nn(),window.print()}finally{a.disabled=!1}};window.addEventListener("beforeprint",nn);window.addEventListener("afterprint",js);c("#menuToggle").onclick=()=>c("#sidebar").classList.toggle("open");c("#notificationButton").onclick=()=>Ps();c("#notificationMenu").onclick=n=>{if(n.target.closest("[data-clear-notifications]")){Bs();return}const a=n.target.closest("[data-notification-view]");a&&(Ye(),V(a.dataset.notificationView))};document.addEventListener("keydown",n=>{n.key==="Escape"&&!c("#notificationMenu").hidden&&(n.preventDefault(),Ye(),c("#notificationButton").focus({preventScroll:!0}))});document.addEventListener("pointerdown",n=>{n.target.closest(".notification-wrap")||Ye()},!0);_a();c("#drawerClose").onclick=Z;c("#drawerBackdrop").onclick=Z;c("#actionDrawerClose").onclick=A;c("#actionDrawerBackdrop").onclick=A;c("#branchScope").onchange=Gt;c("#periodScope").onchange=Gt;c("#globalSearch").addEventListener("keydown",n=>{n.key==="Enter"&&(V("leads"),o.leadSearch=n.target.value,o.leadPage=1)});window.addEventListener("resize",()=>{o.view==="overview"&&(kt(),St(),zt(),da(),window.IMMA_TRADE&&IMMA_TRADE.resize()),o.view==="sales"&&da(),o.view==="branches"&&Ss()});window.addEventListener("popstate",()=>{Ts(),V(Jt(),{silent:!0})});V(v.initialView||Jt(),{replace:!0});
