function re(n,a){return getComputedStyle(document.documentElement).getPropertyValue(n).trim()||a}function e(n,a={}){const s=String(n).split(".");let i=window.IMMA_CENTRAL&&window.IMMA_CENTRAL.i18n||{};for(const o of s)if(i&&typeof i=="object"&&o in i)i=i[o];else{i=null;break}let l=typeof i=="string"?i:n;return Object.keys(a||{}).forEach(o=>{l=l.replace(new RegExp(":"+o,"g"),String(a[o]))}),l}const d=(n,a=document)=>{const s=typeof a=="string"?document.querySelector(a):a||document;return s?s.querySelector(n):null},S=(n,a=document)=>{const s=typeof a=="string"?document.querySelector(a):a||document;return s?[...s.querySelectorAll(n)]:[]},dn={leads:[]},v=window.IMMA_CENTRAL||{};let r={view:"overview",branch:String(d("#branchScope")&&d("#branchScope").value||"all"),period:String(d("#periodScope")&&d("#periodScope").value||v.metrics&&v.metrics.period&&v.metrics.period.key||""),leadSearch:"",leadStatus:"all",leadBeautician:"all",leadBranch:"all",leadPage:1,leadPerPage:10,leadMonth:(function(){const n=new Date;return n.getFullYear()+"-"+String(n.getMonth()+1).padStart(2,"0")})(),leadCalYear:null,followBucket:"all",followSearch:"",followPage:1,importTab:"paste",paymentTab:"queue",paymentSearch:"",paymentCustomerId:null,paymentCustomerLabel:"",paymentBeautician:"all",paymentBranch:"all",paymentPage:1,customerSegment:"all",customerSearch:"",customerBranch:"all",customerPage:1,walletSegment:"all",walletSearch:"",walletTier:"all",walletPage:1,walletCustomerId:null,checkinStatus:"live",checkinSearch:"",checkinBranch:"all",checkinBeautician:"all",checkinPage:1,checkinDate:v.today||new Date().toLocaleDateString("en-CA"),checkinScope:"day",clearanceState:"waiting",clearanceSearch:"",clearanceBranch:"all",clearanceBeautician:"all",clearancePage:1};const E=d("#viewRoot");let L=v.metrics||null,Ma=L?JSON.stringify([r.branch,r.period]):null,fa=0,Q=[],Ta={raw:0,unique:0,duplicates:0,existing:0,converted:0,conversion_pct:0,all_time:{raw:0,unique:0,duplicates:0,existing:0,converted:0,conversion_pct:0}},P={statuses:[],sources:[],beauticians:[],branches:[],months:[]},ta=!1,Ka=null,xe=null,O=new Set,W="",Y="",Be=[],Ca={queue:0,overdue:0,due_today:0,no_response:0,lost:0},Pe={buckets:[],beauticians:[],branches:[],statuses:[]},na=!1,za=null,ye=[],Na={pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0},$e={statuses:[],beauticians:[],branches:[]},sa=!1,Ja=null,La={current_page:1,last_page:1,total:0},we=[],qa={total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0},Ee={segments:[],branches:[]},ia=!1,Ga=null,He={current_page:1,last_page:1,total:0},Re=[],Da={members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0},St={segments:[],tiers:[]},pe=!1,Je=0,Ue=!1,Xa=null,Ha={current_page:1,last_page:1,total:0},ue=[],Ra={live:0,scheduled:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0},xa={statuses:[],beauticians:[],branches:[]},me=!1,Ge=0,Fe=!1,Qa=null,Ua={current_page:1,last_page:1,total:0},ke=null,Ba=0,_e=[],Fa={waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0},Pa={states:[],beauticians:[],branches:[]},ve=!1,Xe=0,Ie=!1,Za=null,Ia={current_page:1,last_page:1,total:0};function N(n,a){return String(n||"").replace("__ID__",String(a))}function q(n=!0){const a={Accept:"application/json","X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":v.csrf||document.querySelector('meta[name="csrf-token"]')?.content||""};return n&&(a["Content-Type"]="application/json"),a}async function j(){const n=v.leadsUrl||"";if(!n){g(e("workspace.load_error"));return}const a=new URLSearchParams;r.leadSearch&&a.set("q",r.leadSearch),r.leadStatus&&r.leadStatus!=="all"&&a.set("status",r.leadStatus);const s=r.leadBranch!=="all"?r.leadBranch:r.branch||"all";s&&s!=="all"&&a.set("branch",s),r.leadBeautician&&r.leadBeautician!=="all"&&a.set("beautician",r.leadBeautician),r.leadMonth&&r.leadMonth!=="all"&&a.set("month",r.leadMonth),a.set("page",String(r.leadPage||1)),a.set("per_page",String(r.leadPerPage||10)),O.clear(),W="",Y="",ca(),ta=!0,st();try{const i=await fetch(`${n}?${a.toString()}`,{headers:q(!1),credentials:"same-origin"});if(!i.ok)throw new Error("leads "+i.status);const l=await i.json();je=l.meta||{},[10,50,100,200].includes(Number(je.per_page))&&(r.leadPerPage=Number(je.per_page)),Q=Array.isArray(l.data)?l.data:[],Ta=l.meta&&l.meta.summary||Ta,P=l.filters||P,dn.leads=Q}catch(i){console.error(i),g(e("workspace.load_error"))}finally{ta=!1,r.view==="leads"&&(Pt(),st(),oa(),Ve())}}function h(n){return Number(n||0).toLocaleString("en-MY")}function C(n){return"RM"+Number(n||0).toLocaleString("en-MY",{maximumFractionDigits:0})}function G(n){return Number(n||0).toLocaleString("en-MY",{maximumFractionDigits:1})+"%"}function t(n){return String(n??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;")}function ee(n,a=""){const s=Number(n||0),i=Math.abs(s).toFixed(1)+a;return s>0?`↑ +${i} ${e("overview.vs_prev_period")}`:s<0?`↓ -${i} ${e("overview.vs_prev_period")}`:`→ 0${a} ${e("overview.vs_prev_period")}`}function le(n,a){return`${e("overview.target")}: ${a}`}async function la(){const n=v.metricsUrl||"",a=++fa,s=r.branch||"all",i=r.period||"",l=JSON.stringify([s,i]),o=new URLSearchParams({branch:s,period:i});try{if(!n)throw new Error("Missing metrics endpoint");const c=await fetch(`${n}?${o.toString()}`,{headers:q(!1),credentials:"same-origin"});if(!c.ok)throw new Error("metrics "+c.status);const u=await c.json();if(a!==fa||l!==JSON.stringify([r.branch,r.period]))return;if(!u.metrics)throw new Error("Missing metrics payload");L=u.metrics,Ma=l,v.metrics=L,ha(),r.view==="overview"&&Lt(),r.view==="sales"&&Gt()}catch{if(a!==fa||l!==JSON.stringify([r.branch,r.period]))return;["overview","sales"].includes(r.view)&&Ma!==l?(E.innerHTML=`<section class="card"><div class="pay-empty" role="alert"><strong>${t(e("overview.metrics_error"))}</strong><button type="button" class="btn" id="retryMetrics">${t(e("reporting.refresh"))}</button></div></section>`,d("#retryMetrics").onclick=()=>la()):g(e("overview.metrics_error"))}}function ne(n,a={}){const s=Math.max(1,Number(a.last_page)||1),i=Math.max(1,Math.min(s,Number(a.current_page)||1)),l=s<=5?Array.from({length:s},(p,m)=>m+1):[...new Set([1,i-1,i,i+1,s].filter(p=>p>=1&&p<=s))].sort((p,m)=>p-m),o=(p,m,_="")=>`<button type="button" class="central-pagination__button" data-page="${p}" ${_}>${m}</button>`;let c="",u=0;return l.forEach(p=>{u&&p-u>1&&(c+='<span class="central-pagination__ellipsis" aria-hidden="true">…</span>'),c+=o(p,String(p),`aria-label="${t(e("pagination.page",{page:p}))}" ${p===i?'aria-current="page" disabled':""}`),u=p}),`<nav class="central-pagination" data-pager="${n}" aria-label="${t(e("pagination.label"))}">
    ${o(i-1,"‹",`id="${n}Prev" aria-label="${t(e("operations.previous"))}" ${i<=1?"disabled":""}`)}
    ${c}
    ${o(i+1,"›",`id="${n}Next" aria-label="${t(e("operations.next"))}" ${i>=s?"disabled":""}`)}
  </nav>`}function se(n,a){const s=d(`[data-pager="${n}"]`);s&&S("button[data-page]",s).forEach(i=>i.onclick=async()=>{if(i.disabled)return;const l=S("button:not(:disabled)",s);l.forEach(o=>o.disabled=!0),s.setAttribute("aria-busy","true");try{await a(Number(i.dataset.page))}finally{s.isConnected&&(l.forEach(o=>o.disabled=!1),s.removeAttribute("aria-busy"))}})}let je={},Mt={},et=1;function R(n){return"RM"+Number(n).toLocaleString("en-MY")}function x(n){const a=String(n??""),s=a.toUpperCase();let i="gray";return s.includes("VERIFIED")||s.includes("PAID")||s==="CONVERTED"||s==="COMPLETED"||s.includes("BANK CHECKED")||s.includes("PROOF")?i="success":s.includes("FOLLOW")||s.includes("PENDING")||s.includes("REVIEW")||s==="BOOKING"||s==="CLAIMED"||s.includes("PROCESSING")||s.includes("DECLARED")?i="warning":s.includes("HOLD")||s.includes("LOST")||s.includes("NO RESPONSE")||s.includes("CANCEL")||s.includes("REFUND")?i="danger":s==="NEW"&&(i="blue"),`<span class="badge ${i}"><span class="status-dot"></span>${t(a)}</span>`}function be(n,a,s=""){return`<div class="page-head"><div><h1 class="page-title">${n}</h1><div class="page-subtitle">${a}</div></div><div class="page-actions">${s}</div></div>`}function X(n,a,s,i,l,o="blue",c=null,u="",p=""){const m=t(u||a),_=t(a),b=t(s),k=t(i),$=t(l),f=t(p),w=/^[↑+]/.test(String(i).trim())||/above|\+|up/i.test(String(i)),y=/^[↓-]/.test(String(i).trim())||/below|down/i.test(String(i))?"down":w?"up":"flat";return`<div class="kpi-card kpi-card--${o}">
    <div class="kpi-card__head">
      <div class="kpi-card__icon" aria-hidden="true">${n}</div>
      <div class="kpi-label" title="${m}">${_}</div>
    </div>
    <div class="kpi-value mono">${b}</div>
    <div class="kpi-card__foot">
      <span class="kpi-trend kpi-trend--${y}">${k}</span>
      <span class="kpi-target">${$}</span>
    </div>
    ${p?`<div class="kpi-spark" id="${f}"></div>`:""}
    ${c!==null?`<div class="progress kpi-card__bar"><span style="width:${Math.min(100,c)}%"></span></div>`:""}
  </div>`}function U(n){const a={database:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v6c0 1.7 3.1 3 7 3s7-1.3 7-3V5"/><path d="M5 11v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"/></svg>',new:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="4"/><path d="M3 20c.7-4 2.7-6 6-6 2.1 0 3.7.8 4.8 2.3M18 8v6M15 11h6"/></svg>',repeated:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7h-9a5 5 0 0 0-5 5v1"/><path d="m17 4 3 3-3 3M4 17h9a5 5 0 0 0 5-5v-1"/><path d="m7 20-3-3 3-3"/></svg>',customers:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c.5-4 2.5-6 6-6s5.5 2 6 6M16 5.5a3 3 0 0 1 0 5.8M17 14c2.4.5 3.7 2.4 4 5"/></svg>',conversion:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 18 10 12l4 3 6-8"/><path d="M15 7h5v5"/><circle cx="6" cy="6" r="2"/></svg>',queue:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v12H4z"/><path d="M4 13h4l2 3h4l2-3h4"/></svg>',overdue:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M12 7v5l3 2M12 4V2M6.4 5.1 5 3.7"/></svg>',today:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16M8.5 15l2.2 2.2 4.8-5"/></svg>',no_response:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 18h10a4 4 0 0 0 4-4V8a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v9l2-2z"/><path d="M8 10h.01M12 10h.01M16 10h.01"/></svg>',lost:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 11a8 8 0 1 1-2.3-5.7"/><path d="M20 4v7h-7"/><path d="m8 12 2.5 2.5L16 9"/></svg>',revenue:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/></svg>',orders:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18H6z"/><path d="M9 3v4h6V3M9 12h6M9 16h4"/></svg>',average:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19 10 5l6 14M6 14h8M19 5v14M17 9h4"/></svg>',target:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="m15 9 5-5"/></svg>'};return a[n]||a.database}function F(n,a,s,i,l,o,c="blue"){return`<div class="kpi-card kpi-card--${c} lead-kpi-card lead-kpi-card--${c}">
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
      <span class="kpi-target lead-kpi__detail">${t(o)}</span>
    </div>
  </div>`}function Le(n,a,s,i=""){const l=i?` trade-pane__chart--${i}`:"";return`<section class="trade-pane">
    <div class="trade-pane__head">
      <div>
        <div class="trade-pane__eyebrow">${e("trade.section")}</div>
        <div class="trade-pane__title">${e(n)}</div>
        <div class="trade-pane__sub">${e(a)}</div>
      </div>
    </div>
    <div class="trade-pane__chart${l}" id="${s}"></div>
  </section>`}function pn(n=new Date){const a=["JAN","FEB","MAC","APR","MEI","JUN","JUL","OGOS","SEPT","OKT","NOV","DIS"];return`${String(n.getDate()).padStart(2,"0")} ${a[n.getMonth()]} ${n.getFullYear()}`}function un(){const n=L||{},a=n.kpis||{},s=n.targets||{},i=Array.isArray(n.beauticians)?n.beauticians:[],l=Number(a.new_buyers||0),o=Number(s.leads||0),c=o>0?Math.round(l/o*1e3)/10:0,u=!!(n.period&&n.period.key),p=n.period&&n.period.label?t(n.period.label):pn(),m=n.slogan||e("daily.slogan"),_=n.quote||e("daily.quote");return`<section class="daily-hero">
    <div class="daily-hero__top">
      <div class="daily-hero__copy">
        <div class="daily-hero__eyebrow">${e("daily.eyebrow")}</div>
        <h2 class="daily-hero__title">${e("daily.title")}</h2>
        <div class="daily-hero__meta">
          <span class="daily-hero__date">${p}</span>
          <span class="daily-hero__pill">${e("daily.live")}</span>
        </div>
        <p class="daily-hero__quote">${t(m)} -- "${t(_)}"</p>
      </div>
    </div>
    <div class="daily-hero__stats">
      <div class="daily-stat daily-stat--primary">
        <div class="daily-stat__label">${e(u?"overview.kpi_unique_leads":"daily.leads_today")}</div>
        <div class="daily-stat__value">${h(l)}</div>
        <div class="daily-stat__sub">${e("daily.beauticians_active",{count:i.length||0})}</div>
      </div>
      <div class="daily-stat">
        <div class="daily-stat__label">${e("daily.month_progress")}</div>
        <div class="daily-stat__value daily-stat__value--sm">${h(l)} / ${h(o)}</div>
        <div class="daily-stat__sub">${e("daily.of_monthly_target",{pct:c})}</div>
        <div class="progress daily-stat__bar"><span style="width:${Math.min(100,c)}%"></span></div>
      </div>
    </div>
  </section>`}const at=[["#1d4ed8","#60a5fa"],["#9a3412","#f59e0b"],["#065f46","#34d399"],["#6d28d9","#a78bfa"],["#be123c","#fb7185"],["#0e7490","#22d3ee"],["#a16207","#facc15"]];function mn(n){const a=String(n||"");let s=0;for(let l=0;l<a.length;l++)s=s*31+a.charCodeAt(l)>>>0;const i=at[Math.abs(s)%at.length];return`linear-gradient(145deg, ${i[0]}, ${i[1]})`}function _n(n){const a=String(n||"").trim().split(/\s+/).filter(Boolean);return((a[0]?.[0]||"")+(a[1]?.[0]||"")).toUpperCase()}function vn(){const n=L||{},a=!!(n.period&&n.period.key),s=Array.isArray(n.beauticians)?n.beauticians:[],i=Array.isArray(n.beauticians)?n.beauticians.length:0;Math.max(1,Number(n.beautician_count)||i||1);const l=Number(n.targets&&n.targets.beautician_leads||0)||112,o=[...s].sort((_,b)=>b.leads-_.leads),c=n.period&&n.period.label?t(n.period.label):e("common.this_month"),u=o.reduce((_,b)=>_+(Number(b.leads)||0),0),p=["🥇","🥈","🥉"],m=o.map((_,b)=>{const k=Number(_.leads)||0,$=Number(_.target)||l,f=$>0?Math.round(k/$*100):0,w=u>0?Math.max(4,Math.round(k/u*100)):0,M=b===0?"gold":b===1?"silver":b===2?"bronze":"",y=f>=100?"is-hit":f>=75?"is-close":"is-low",T=t(String(_.name||"")),B=b<3?`<span class="rank rank--medal rank--${M}" title="${e("daily.rank_title",{n:b+1})}">${p[b]}</span>`:`<span class="rank">${b+1}</span>`;return`<tr class="daily-row${M?` daily-row--${M}`:""}">
      <td class="daily-rank-cell">${B}</td>
      <td>
        <div class="person-cell">
          <div class="mini-avatar leader-avatar${M?` mini-avatar--${M}`:""}" style="background:${mn(_.name)}">${_n(_.name)}</div>
          <div class="person-cell__text">
            <strong>${T}${b===0?` <span class="leader-tag">${e("daily.leader")}</span>`:""}</strong>
            <small>${e("daily.target_month",{count:$})}<i class="daily-dot"></i>${c}</small>
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
          <div class="daily-panel__sub">${e(a?"daily.rank_sub_month":"daily.rank_sub",{date:c})}</div>
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
          <div class="daily-panel__sub">${e(a?"daily.leads_share_period":"daily.daily_share",a?{period:c}:{})}</div>
        </div>
      </div>
      <div class="chart-wrap daily-charts__bar"><canvas id="dailyLeadChart"></canvas></div>
      <div class="daily-charts__divider">
        <div class="daily-panel__eyebrow">${e("daily.trend")}</div>
        <div class="daily-panel__title daily-panel__title--sm">${e(a?"daily.trend_period":"daily.trend_7d",a?{period:c}:{})}</div>
      </div>
      <div class="chart-wrap daily-charts__trend"><canvas id="dailyTrendChart"></canvas></div>
    </section>
  </div>`}function Ea(n,a,s,i,l,o){const c=Math.min(o,i/2,l/2);n.beginPath(),n.moveTo(a+c,s),n.arcTo(a+i,s,a+i,s+l,c),n.arcTo(a+i,s+l,a,s+l,c),n.arcTo(a,s+l,a,s,c),n.arcTo(a,s,a+i,s,c),n.closePath()}function Tt(){const n=d("#dailyLeadChart");if(!n)return;Ke(n);const a=n.getContext("2d"),s=L||{},i=Array.isArray(s.beauticians)?s.beauticians:[],o=[...i.length?i.map(y=>({name:String(y.name||""),leads:Number(y.leads||0)})):[]].sort((y,T)=>T.leads-y.leads),c=o.map(y=>y.name),u=o.map(y=>y.leads),p=n.clientWidth,m=n.clientHeight,_={l:28,r:10,t:28,b:42},b=Math.max(...u,1)*1.2,k=Math.max(6,Math.min(12,p/60)),$=Math.max(14,(p-_.l-_.r-k*(u.length-1))/u.length),f=re("--navy","#1d4ed8"),w=re("--rose","#0ea5e9");a.fillStyle="#f8fafc",Ea(a,0,0,p,m,12),a.fill(),a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let y=0;y<4;y++){const T=_.t+y*((m-_.t-_.b)/3);a.beginPath(),a.moveTo(_.l,T),a.lineTo(p-_.r,T),a.stroke()}const M=[["#1d4ed8","#38bdf8"],["#2563eb","#7dd3fc"],["#0284c7","#67e8f9"]];u.forEach((y,T)=>{const B=_.l+T*($+k),D=Math.max(4,y/b*(m-_.t-_.b)),fe=m-_.b-D,[K,on]=M[Math.min(T,2)]||[f,w],ba=a.createLinearGradient(0,fe,0,m-_.b);ba.addColorStop(0,T<3?on:w),ba.addColorStop(1,T<3?K:"#93c5fd"),a.fillStyle=ba,Ea(a,B,fe,$,D,8),a.fill(),a.fillStyle="#0f172a",a.font="700 12px Poppins",a.textAlign="center",a.fillText(String(y),B+$/2,fe-8);const cn=c[T].length>7?c[T].slice(0,6)+"…":c[T];a.fillStyle="#475569",a.font="600 10px Poppins",a.fillText(cn,B+$/2,m-14)})}function Ct(){const n=d("#dailyTrendChart");if(!n)return;Ke(n);const a=n.getContext("2d"),s=L||{},i=s.leads_trend&&Array.isArray(s.leads_trend.actual)?s.leads_trend:null,l=i?[...i.actual]:[],o=i&&Array.isArray(i.labels)?i.labels:Array.from({length:l.length},(w,M)=>M===l.length-1?"Today":"D-"+(l.length-1-M)),c=n.clientWidth,u=n.clientHeight,p={l:28,r:14,t:22,b:28},m=Math.max(...l,1)*1.15,_=w=>p.l+w*((c-p.l-p.r)/Math.max(l.length-1,1)),b=w=>u-p.b-w/m*(u-p.t-p.b),k=re("--navy","#1d4ed8"),$=re("--rose","#0ea5e9");a.fillStyle="#f8fafc",Ea(a,0,0,c,u,12),a.fill(),a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let w=0;w<3;w++){const M=p.t+w*((u-p.t-p.b)/2);a.beginPath(),a.moveTo(p.l,M),a.lineTo(c-p.r,M),a.stroke()}const f=a.createLinearGradient(0,p.t,0,u-p.b);f.addColorStop(0,"rgba(14,165,233,.28)"),f.addColorStop(1,"rgba(37,99,235,.02)"),a.beginPath(),a.moveTo(_(0),u-p.b),l.forEach((w,M)=>a.lineTo(_(M),b(w))),a.lineTo(_(l.length-1),u-p.b),a.closePath(),a.fillStyle=f,a.fill(),a.beginPath(),l.forEach((w,M)=>M?a.lineTo(_(M),b(w)):a.moveTo(_(M),b(w))),a.strokeStyle=k,a.lineWidth=2.75,a.lineJoin="round",a.lineCap="round",a.stroke(),l.forEach((w,M)=>{const y=_(M),T=b(w);a.beginPath(),a.arc(y,T,5,0,Math.PI*2),a.fillStyle="#fff",a.fill(),a.lineWidth=2.5,a.strokeStyle=$,a.stroke(),a.beginPath(),a.arc(y,T,2.2,0,Math.PI*2),a.fillStyle=k,a.fill(),a.fillStyle="#0f172a",a.font="700 10px Poppins",a.textAlign="center",a.fillText(String(w),y,T-10),a.fillStyle="#64748b",a.font="600 10px Poppins",a.fillText(o[M],y,u-10)})}function hn(){const n="company_target",a=L||{},s=a.targets||{},i=Math.max(1,Number(a.beautician_count)||1),l=Number(s.leads)||0,o=Number(s.conv_pct)||0,c=Number(s.buyers)||0,u=Number(s.avg_sale)||0,p=Number(s.sales)||0,m=Number(s.beautician_leads)||112,_=Math.round(m*o/100),b=Math.round(_*u),k=T=>{const B=Number(T||0);return Math.abs(B)>=1e6?"RM"+(B/1e6).toFixed(1).replace(/\.0$/,"")+"M":Math.abs(B)>=1e3?"RM"+(B/1e3).toFixed(1).replace(/\.0$/,"")+"k":C(B)},$=h(l),f=G(o),w=h(c),M=C(u),y=k(p);return`<section class="company-target">
    <div class="company-target__hero">
      <div class="company-target__hero-copy">
        <div class="company-target__eyebrow">${e(n+".eyebrow")}</div>
        <h2 class="company-target__title">${e(n+".title",{leadgoal:$})}</h2>
        <p class="company-target__desc">${e(n+".desc",{leadgoal:$,convgoal:f,buyergoal:w,avggoal:M,salesgoal:y})}</p>
      </div>
      <div class="company-target__hero-goal">
        <div class="company-target__goal-label">${e(n+".sales_goal")}</div>
        <div class="company-target__goal-value">${C(p)}</div>
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
  </section>`}function Lt(){window.IMMA_TRADE&&IMMA_TRADE.dispose();const n=L||{},a=n.kpis||{},s=a.vs_prev||{},i=n.targets||{},l=n.dual||{},o=Number(l.sales_pct||0),c=Number(i.sales||0),u=Number(a.sales||0),p=u-c,m=t(n.period&&n.period.label||e("common.this_month")),_=n.ops||{},b=_.checkin||{},k=_.clearance||{},$=_.payments||{},f=e("ops.value_customers",{count:h(b.live||0)}),w=e("ops.meta_checkin",{waiting:h(b.waiting||0),treatment:h(b.in_treatment||0)}),M=e("ops.value_customers",{count:h(k.queue||0)}),y=e("ops.meta_clearance",{waiting:h(k.waiting||0),blocked:h(k.blocked||0)}),T=e("ops.value_pending",{count:h($.queue||0)}),B=e("ops.meta_payments",{processing:h($.processing||0),hold:h($.hold||0)});E.innerHTML=`${be(e("overview.title"),e("overview.subtitle"),`<button class="btn soft">${m}</button><button class="btn primary" data-jump="leads">${e("common.open_leads")}</button>`)}
  ${hn()}
  <div class="trade-grid trade-grid--2">
    ${Le("trade.dual_title","trade.dual_sub","tradeDualRing","sm")}
    ${Le("trade.equity_title","trade.equity_sub","tradeEquity","lg")}
  </div>
  <div class="section-label">${e("overview.actual_label")}</div>
  <div class="grid kpi-grid">
    ${X("♙",e("overview.kpi_unique_leads"),h(a.new_buyers),ee(s.new_buyers,"%"),le(e("overview.kpi_unique_leads"),h(i.leads||0)),"rose",Math.min(100,(a.new_buyers||0)/Math.max(1,i.leads||0)*100),"","sparkLeads")}
    ${X("▣",e("overview.kpi_buyers"),h(a.buyers),ee(s.buyers,"%"),le(e("overview.kpi_buyers"),h(i.buyers||0)),"blue",Math.min(100,(a.buyers||0)/Math.max(1,i.buyers||0)*100),"","sparkBuyers")}
    ${X("%",e("overview.kpi_conv_rate"),G(a.new_buyer_share_pct),ee(s.new_buyer_share_pct,"pp"),le(e("overview.kpi_conv_rate"),G(i.conv_pct||0)),"green",Math.min(100,Number(a.new_buyer_share_pct||0)),"","sparkConv")}
    ${X("◫",e("overview.kpi_sales"),C(a.sales),ee(s.sales,"%"),le(e("overview.kpi_sales"),C(c)),"rose",Math.min(100,o),"","sparkSales")}
    ${X("▥",e("overview.kpi_avg_sale"),C(a.avg_sale),ee(s.avg_sale,"%"),le(e("overview.kpi_avg_sale"),C(i.avg_sale||0)),"purple",Math.min(100,(a.avg_sale||0)/Math.max(1,i.avg_sale||0)*100),"","sparkAvg")}
  </div>
  <div class="trade-grid trade-grid--2" style="margin-top:12px">
    ${Le("trade.waterfall_title","trade.waterfall_sub","tradeWaterfall")}
    <section class="card">
      <div class="card-title-row"><div class="card-title">▥ ${e("overview.lead_status")}</div><button class="btn small soft">${m}</button></div>
      <div style="display:grid;grid-template-columns:180px 1fr;gap:14px;align-items:center">
        <div class="chart-wrap" style="height:180px"><canvas id="donutChart"></canvas></div>
        <div class="legend" id="leadLegend"></div>
      </div>
    </section>
  </div>
  <div class="trade-grid trade-grid--2">
    ${Le("trade.conversion_title","trade.conversion_sub","tradeConversion")}
    ${Le("trade.heatmap_title","trade.heatmap_sub","tradeHeatmap")}
  </div>

  <div class="grid split-60" style="margin-top:12px">
    <section class="card sales-card">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${e("overview.revenue")}</div>
          <div class="daily-panel__title">${e("overview.monthly_sales")}</div>
          <div class="daily-panel__sub">${e("overview.monthly_sales_sub")}</div>
        </div>
        <span class="sales-card__pill">${m} · ${C(u)}</span>
      </div>
      <div class="chart-wrap sales-card__chart"><canvas id="salesChart"></canvas></div>
    </section>
    <section class="card target-card ${o>=100?"target-card--over":""}">
      <div class="target-card__head">
        <div>
          <div class="target-card__eyebrow">${e("overview.monthly_goal")}</div>
          <div class="target-card__title">${e("overview.target_achievement")}</div>
        </div>
        <span class="target-card__pill">${o>=100?e("overview.above_target"):e("overview.below_target")}</span>
      </div>
      <div class="target-card__body">
        <div class="target-card__ring" style="--p:${Math.min(100,o)}">
          <svg viewBox="0 0 120 120" aria-hidden="true">
            <circle class="target-card__track" cx="60" cy="60" r="52"></circle>
            <circle class="target-card__prog" cx="60" cy="60" r="52"></circle>
          </svg>
          <div class="target-card__ring-value">
            <strong>${G(o)}</strong>
            <span>${e("overview.of_target")}</span>
          </div>
        </div>
        <div class="target-card__side">
          <div class="target-card__stat">
            <span>${e("overview.target")}</span>
            <strong>${C(c)}</strong>
          </div>
          <div class="target-card__stat">
            <span>${e("overview.actual")}</span>
            <strong>${C(u)}</strong>
          </div>
          <div class="target-card__delta">
            <strong>${p>=0?"+":""}${C(p)}</strong>
            <span>${e("overview.vs_target_month")}</span>
          </div>
        </div>
      </div>
    </section>
  </div>

  <section class="card" style="margin-top:12px"><div class="card-title-row"><div class="card-title">♙ ${e("overview.beauticians")}</div><button class="btn small primary" data-jump="beauticians">${e("common.view_all")}</button></div>${bn()}</section>

  <div class="grid three-col" style="margin-top:12px">
    ${(n.branches||[]).slice(0,6).map(D=>xt(D.name,D.new_buyers||0,D.buyers||0,D.conv||0,D.sales||0,D.avg||0,D.buyers||0)).join("")||`<section class="card"><div class="empty"><strong>${e("overview.no_branch_data")}</strong></div></section>`}
  </div>

  <div class="grid op-row" style="margin-top:12px">
    ${ga("♧",e("ops.checkin"),f,w,"checkin",e("ops.checkin"),"green")}
    ${ga("◷",e("ops.clearance"),M,y,"clearance",e("ops.clearance"),"warning")}
    ${ga("▣",e("ops.payments"),T,B,"payments",e("ops.payments"),"rose")}
  </div>
  <div style="margin-top:12px">${un()}</div>
  <div style="margin-top:12px">${vn()}</div>`,requestAnimationFrame(()=>{if(Tt(),Ct(),Xt(),ua(),ie(),window.IMMA_TRADE){const D=Object.assign({},n.ticker||{}),fe={leadsUp:Number(s.new_buyers||0)>=0,convUp:Number(s.new_buyer_share_pct||0)>=0,salesUp:Number(s.sales||0)>=0,targetUp:o>=100,avgUp:Number(s.avg_sale||0)>=0};IMMA_TRADE.render({ticker:Object.assign(D,fe),leadsPct:Number(l.buyers_pct||l.leads_pct||0),salesPct:o,equityActual:n.equity&&n.equity.actual||[],equityTarget:n.equity&&n.equity.target_path||[],equityLabels:n.equity&&n.equity.labels||[],waterfall:(n.waterfall||n.status_mix||[]).map(K=>({name:String(K.name||""),value:K.value})),beauticians:(n.beauticians||[]).map(K=>({name:String(K.name||""),leads:K.leads||0,converted:K.converted||0,conv:K.conv||0,sales:K.sales||0})),heatmap:n.heatmap||[],sparks:n.sparks||[]})}})}function bn(){const n=Array.isArray(L?.beauticians)?L.beauticians:[];return n.length?`<div class="table-wrap"><table class="data-table"><thead><tr><th>#</th><th>${e("overview.col_beautician")}</th><th title="${e("overview.kpi_unique_leads")}">${e("overview.unique")}</th><th>${e("overview.kpi_buyers")}</th><th title="${e("overview.kpi_conv_rate")}">${e("overview.conv_rate")}</th><th>${e("overview.kpi_sales")}</th><th>${e("overview.kpi_avg_sale")}</th><th>${e("overview.col_orders")}</th></tr></thead><tbody>${n.map((a,s)=>{const i=t(a.name);return`<tr><td><span class="rank">${s+1}</span></td><td><strong>${i}</strong></td><td>${h(a.leads)}</td><td>${h(a.buyers)}</td><td style="color:${a.conv>=40?"var(--success)":a.conv<35?"var(--danger)":"#b16e10"};font-weight:700">${G(a.conv)}</td><td>${C(a.sales)}</td><td>${C(a.avg)}</td><td>${h(a.orders||0)}</td></tr>`}).join("")}</tbody></table></div>`:`<div class="empty"><strong>${e("overview.no_beautician_data")}</strong></div>`}function xt(n,a,s,i,l,o,c){const u=i>=40?"good":i>=35?"ok":"low",p=t(n),m=t(String(n||"").slice(0,2).toUpperCase());return`<section class="card branch-card branch-card--${u}">
    <div class="branch-card__head">
      <div class="branch-card__identity">
        <div class="branch-card__mark" aria-hidden="true">${m}</div>
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
      <div class="branch-metric"><span title="${e("overview.kpi_avg_sale")}">${e("overview.kpi_avg_sale")}</span><strong>${R(o)}</strong></div>
      <div class="branch-metric"><span title="${e("overview.treat_done")}">${e("overview.treat_done")}</span><strong>${c.toLocaleString()}</strong></div>
    </div>
  </section>`}function ga(n,a,s,i,l,o,c){return`<section class="card op-card"><div class="op-icon" style="background:${c==="green"?"var(--success-soft)":c==="warning"?"var(--warning-soft)":"var(--rose-soft)"}">${n}</div><div class="op-body"><div class="op-title">${a}</div><div class="op-value">${s}</div><div class="op-meta">${i}</div></div><button class="btn small primary" data-jump="${l}">${o} →</button></section>`}function tt(n){(!r.leadMonth||r.leadMonth==="all")&&(r.leadMonth=We());const[a,s]=r.leadMonth.split("-").map(Number),i=new Date(a,s-1+n,1);r.leadMonth=i.getFullYear()+"-"+String(i.getMonth()+1).padStart(2,"0"),r.leadPage=1,j().then(()=>Ve())}function Oe(n){if(!n||n==="all")return null;const[a,s]=String(n).split("-").map(Number);return!a||!s?null:{y:a,m:s}}function Bt(){const n=(v.locale||"en").toLowerCase().startsWith("ms")?"ms-MY":"en-GB";return Array.from({length:12},(a,s)=>new Date(2e3,s,1).toLocaleString(n,{month:"short"}))}function ma(n){if(!n||n==="all")return e("workspace.all_months");const a=Oe(n);if(!a)return String(n);const i=(P.months||[]).find(l=>String(l.value)===String(n));return i?i.label:Bt()[a.m-1]+" "+a.y}function We(){const n=new Date;return n.getFullYear()+"-"+String(n.getMonth()+1).padStart(2,"0")}function fn(){const n=new Date;return We()+"-"+String(n.getDate()).padStart(2,"0")}function gn(){const n=Oe(r.leadMonth)||Oe(We()),a=r.leadCalYear||n.y,s=!!v.canCreateLead;return`
    <div class="lead-cal" id="leadCalendar">
      <button type="button" class="lead-cal__nav" id="leadMonthPrev" title="${t(e("workspace.month_prev"))}" aria-label="${t(e("workspace.month_prev"))}">‹</button>
      <button type="button" class="lead-cal__toggle" id="leadCalToggle" aria-expanded="false" aria-haspopup="dialog">
        <span class="lead-cal__icon" aria-hidden="true">▦</span>
        <span id="leadMonthLabel">${t(ma(r.leadMonth))}</span>
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
  `}function ra(){const n=d("#leadCalGrid"),a=d("#leadCalYearLabel");if(!n)return;const s=Oe(r.leadMonth),i=r.leadCalYear||(s?s.y:new Date().getFullYear());r.leadCalYear=i,a&&(a.textContent=String(i));const l=Bt(),o=We();n.innerHTML=l.map((c,u)=>{const p=`${i}-${String(u+1).padStart(2,"0")}`;return`<button type="button" class="lead-cal__month${String(r.leadMonth)===p?" is-selected":""}${p===o?" is-now":""}" data-month="${p}">${t(c)}</button>`}).join(""),S("[data-month]",n).forEach(c=>{c.onclick=()=>{r.leadMonth=c.dataset.month,r.leadPage=1,de(),Ve(),j()}})}function yn(){const n=d("#leadCalPanel"),a=d("#leadCalToggle");if(!n||!a)return;const s=Oe(r.leadMonth);r.leadCalYear=s?s.y:new Date().getFullYear(),n.classList.remove("hidden"),a.setAttribute("aria-expanded","true"),ra()}function de(){const n=d("#leadCalPanel"),a=d("#leadCalToggle");n&&n.classList.add("hidden"),a&&a.setAttribute("aria-expanded","false")}function Ve(){const n=d("#leadMonthLabel");n&&(n.textContent=ma(r.leadMonth)),d("#leadCalPanel")&&!d("#leadCalPanel").classList.contains("hidden")&&ra()}function nt(n){const a=d("#leadCalendar");!a||a.contains(n.target)||de()}function $n(){d("#leadMonthPrev")&&(d("#leadMonthPrev").onclick=()=>{de(),tt(-1)}),d("#leadMonthNext")&&(d("#leadMonthNext").onclick=()=>{de(),tt(1)}),d("#leadCalToggle")&&(d("#leadCalToggle").onclick=n=>{n.stopPropagation();const a=d("#leadCalPanel");a&&a.classList.contains("hidden")?yn():de()}),d("#leadCalYearPrev")&&(d("#leadCalYearPrev").onclick=n=>{n.stopPropagation(),r.leadCalYear=(r.leadCalYear||new Date().getFullYear())-1,ra()}),d("#leadCalYearNext")&&(d("#leadCalYearNext").onclick=n=>{n.stopPropagation(),r.leadCalYear=(r.leadCalYear||new Date().getFullYear())+1,ra()}),d("#leadMonthThis")&&(d("#leadMonthThis").onclick=n=>{n.stopPropagation(),r.leadMonth=We(),r.leadPage=1,de(),Ve(),j()}),d("#leadCalAll")&&(d("#leadCalAll").onclick=n=>{n.stopPropagation(),r.leadMonth="all",r.leadPage=1,de(),Ve(),j()}),document.removeEventListener("click",nt),document.addEventListener("click",nt)}function wn(){const n=!!v.canCreateLead,a=gn();E.innerHTML=`${be(e("workspace.title"),e("workspace.subtitle"),a)}
  <section class="lead-kpi-section" aria-labelledby="leadKpiTitle">
    <div class="lead-kpi-section__head">
      <div><span class="lead-kpi-section__eyebrow">${t(e("workspace.kpi_eyebrow"))}</span><h2 id="leadKpiTitle">${t(e("workspace.kpi_heading"))}</h2></div>
      <span class="lead-kpi-section__scope">${t(e("workspace.kpi_scope",{period:ma(r.leadMonth)}))}</span>
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
        <input class="lead-search__input" id="leadSearch" type="search" autocomplete="off" placeholder="${t(e("workspace.search_placeholder"))}" value="${t(r.leadSearch)}" />
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
  </section>`,Pt(),oa(),$n(),ie(),d("#addLeadBtn")&&(d("#addLeadBtn").onclick=At),j()}function Pt(){const n=d("#leadKpiMount");if(!n)return;const a=Ta||{},s=a.all_time||a,i=Number(a.raw||0),l=Number(a.unique||0),o=Number(a.duplicates||0),c=Number(a.existing||0),u=Number(a.converted||0),p=Number(a.conversion_pct||0),m=Number(s.raw||0),_=Number(s.unique||0),b=Number(s.duplicates||0),k=Number(s.existing||0),$=Number(s.converted||0),f=Number(s.conversion_pct||0),w=m>0?(_/m*100).toFixed(1):"0.0",M=m>0?(b/m*100).toFixed(1):"0.0",y=ma(r.leadMonth);n.innerHTML=`
    ${F(U("database"),e("workspace.total_leads_database"),h(m),e("workspace.unit_lead_records"),e("workspace.all_time"),e("workspace.period_added",{period:y,count:h(i)}),"blue")}
    ${F(U("new"),e("workspace.new_leads"),h(l),e("workspace.unit_new_leads"),y,e("workspace.all_time_unique",{count:h(_),pct:w}),"green")}
    ${F(U("repeated"),e("workspace.repeated_leads"),h(b),e("workspace.unit_repeated_records"),e("workspace.all_time"),e("workspace.period_repeated",{period:y,count:h(o),pct:M}),"rose")}
    ${F(U("customers"),e("workspace.existing_customers"),h(k),e("workspace.unit_registered_customers"),e("workspace.matched_phone"),e("workspace.period_matched",{period:y,count:h(c)}),"purple")}
    ${F(U("conversion"),e("workspace.conversion"),G(f),e("workspace.unit_conversion_rate"),e("workspace.all_time"),e("workspace.period_conversion",{period:y,pct:G(p),count:h(u),total:h($)}),"teal")}
  `}function oa(){const n=d("#leadStatus"),a=d("#leadBeautician"),s=d("#leadBranchFilter");if(n){const l=[{value:"all",label:e("workspace.all_status")},...P.statuses||[]];n.innerHTML=l.map(o=>`<option value="${t(o.value)}" ${String(r.leadStatus)===String(o.value)?"selected":""}>${t(o.label)}</option>`).join(""),n.onchange=o=>{r.leadStatus=o.target.value,r.leadPage=1,j()}}if(a){const l=[{id:"all",name:e("workspace.all_beauticians")},...P.beauticians||[]];a.innerHTML=l.map(o=>`<option value="${t(o.id)}" ${String(r.leadBeautician)===String(o.id)?"selected":""}>${t(o.name)}</option>`).join(""),a.onchange=o=>{r.leadBeautician=o.target.value,r.leadPage=1,j()}}if(s){const l=[{id:"all",name:e("workspace.all_branches")},...P.branches||v.branches||[]];s.innerHTML=l.map(o=>`<option value="${t(o.id)}" ${String(r.leadBranch)===String(o.id)?"selected":""}>${t(o.name)}</option>`).join(""),s.onchange=o=>{r.leadBranch=o.target.value,r.leadPage=1,j()}}const i=d("#leadSearch");i&&(i.oninput=l=>{r.leadSearch=l.target.value,clearTimeout(Ka),Ka=setTimeout(()=>{r.leadPage=1,j()},350)}),Et()}function ya(n,a){if(n==="status"){const s=(P.statuses||[]).find(i=>String(i.value)===String(a));return s?s.label:a}if(n==="beautician"){const s=(P.beauticians||[]).find(i=>String(i.id)===String(a));return s?s.name:a}if(n==="branch"){const i=(P.branches||v.branches||[]).find(l=>String(l.id)===String(a));return i?i.name:a}return a}function Et(){const n=d("#leadResultCount"),a=Number(je.total??(Array.isArray(Q)?Q.length:0));n&&(n.textContent=a===1?e("workspace.results_count_one"):e("workspace.results_count",{count:h(a)}));const s=d("#leadActiveFilters");if(!s)return;const i=[];if(r.leadSearch&&String(r.leadSearch).trim()&&i.push({key:"q",label:`“${String(r.leadSearch).trim()}”`}),r.leadStatus&&r.leadStatus!=="all"&&i.push({key:"status",label:ya("status",r.leadStatus)}),r.leadBeautician&&r.leadBeautician!=="all"&&i.push({key:"beautician",label:ya("beautician",r.leadBeautician)}),r.leadBranch&&r.leadBranch!=="all"&&i.push({key:"branch",label:ya("branch",r.leadBranch)}),!i.length){s.hidden=!0,s.innerHTML="";return}s.hidden=!1,s.innerHTML=`
    <div class="lead-chips">
      ${i.map(l=>`<span class="lead-chip">${t(l.label)}<button type="button" class="lead-chip__x" data-clear-filter="${t(l.key)}" aria-label="${t(e("workspace.clear_filters"))}">×</button></span>`).join("")}
      <button type="button" class="lead-chips__clear" id="leadClearFilters">${t(e("workspace.clear_filters"))}</button>
    </div>
  `,S("[data-clear-filter]",s).forEach(l=>{l.onclick=()=>{const o=l.dataset.clearFilter;o==="q"&&(r.leadSearch=""),o==="status"&&(r.leadStatus="all"),o==="beautician"&&(r.leadBeautician="all"),o==="branch"&&(r.leadBranch="all"),r.leadPage=1;const c=d("#leadSearch");c&&o==="q"&&(c.value=""),oa(),j()}}),d("#leadClearFilters")&&(d("#leadClearFilters").onclick=()=>{r.leadSearch="",r.leadStatus="all",r.leadBeautician="all",r.leadBranch="all",r.leadPage=1;const l=d("#leadSearch");l&&(l.value=""),oa(),j()})}function kn(n){return n==="status"?(P.statuses||[]).map(a=>`<option value="${t(a.value)}">${t(a.label)}</option>`).join(""):n==="source"?(P.sources||[]).map(a=>`<option value="${t(a.value)}">${t(a.label)}</option>`).join(""):n==="beautician_id"?`<option value="__none__">${t(e("workspace.bulk_unassigned"))}</option>${(P.beauticians||[]).map(a=>`<option value="${t(a.id)}">${t(a.name)}</option>`).join("")}`:n==="spa_branch_id"?`<option value="__none__">${t(e("workspace.bulk_unassigned"))}</option>${(P.branches||v.branches||[]).map(a=>`<option value="${t(a.id)}">${t(a.name)}</option>`).join("")}`:""}function ca(){const n=d("#leadBulkBar");if(!n)return;const a=O.size;if(!a){n.hidden=!0,n.innerHTML="",W="",Y="";return}const s=[];v.canEditLead&&s.push(["status",e("workspace.bulk_update_status")],["source",e("workspace.bulk_update_source")],["created_at",e("workspace.bulk_update_date")],["beautician_id",e("workspace.bulk_assign_beautician")],["spa_branch_id",e("workspace.bulk_assign_branch")]),v.canDeleteLead&&s.push(["delete",e("workspace.bulk_delete")]),s.some(([c])=>c===W)||(W="",Y="");const i=["status","source","created_at","beautician_id","spa_branch_id"].includes(W);n.hidden=!1,n.innerHTML=`
    <div class="lead-bulk-bar__summary"><strong>${t(e("workspace.bulk_selected",{count:h(a)}))}</strong></div>
    <div class="lead-bulk-bar__controls">
      <label class="sr-only" for="leadBulkAction">${t(e("workspace.bulk_action"))}</label>
      <select class="lead-bulk-control" id="leadBulkAction">
        <option value="">${t(e("workspace.bulk_choose_action"))}</option>
        ${s.map(([c,u])=>`<option value="${t(c)}">${t(u)}</option>`).join("")}
      </select>
      ${W==="created_at"?`<label class="sr-only" for="leadBulkValue">${t(e("workspace.bulk_choose_date"))}</label>
      <input class="lead-bulk-control lead-bulk-date" id="leadBulkValue" type="date" max="${fn()}" value="${t(Y)}" aria-label="${t(e("workspace.bulk_choose_date"))}">`:i?`<label class="sr-only" for="leadBulkValue">${t(e("workspace.bulk_choose_value"))}</label>
      <select class="lead-bulk-control" id="leadBulkValue">
        <option value="">${t(e("workspace.bulk_choose_value"))}</option>
        ${kn(W)}
      </select>`:""}
      <button type="button" class="btn ${W==="delete"?"danger":"primary"} lead-bulk-apply" id="leadBulkApply" ${!W||i&&!Y?"disabled":""}>${t(e("workspace.bulk_apply"))}</button>
      <button type="button" class="btn lead-bulk-clear" id="leadBulkClear">${t(e("workspace.bulk_clear"))}</button>
    </div>`;const l=d("#leadBulkAction");l.value=W,l.onchange=c=>{W=c.target.value,Y="",ca()};const o=d("#leadBulkValue");o&&(o.value=Y,o.onchange=c=>{Y=c.target.value,ca()}),d("#leadBulkApply").onclick=Tn,d("#leadBulkClear").onclick=()=>{O.clear(),W="",Y="",ea()}}function ea(){const n=Q.map(i=>String(i.id)),a=n.filter(i=>O.has(i)).length,s=d("#leadSelectAll");s&&(s.checked=n.length>0&&a===n.length,s.indeterminate=a>0&&a<n.length),S("[data-lead-select]").forEach(i=>{const l=O.has(String(i.value));i.checked=l,i.closest("tr")?.classList.toggle("is-selected",l)}),ca()}async function Sn(n,a,s){const i=v.leadBulkUpdateUrl||"";if(!i){g(e("workspace.bulk_update_error"));return}s.disabled=!0;try{const l=await fetch(i,{method:"PATCH",headers:q(!0),credentials:"same-origin",body:JSON.stringify({ids:[...O].map(Number),field:n,value:a})}),o=await l.json().catch(()=>({}));if(!l.ok){g(o.message||e("workspace.bulk_update_error"));return}g(o.message||e("workspace.bulk_updated",{count:O.size})),await j()}catch(l){console.error(l),g(e("workspace.bulk_update_error"))}finally{s.isConnected&&(s.disabled=!1)}}async function Mn(n){const a=v.leadBulkDeleteUrl||"";if(!a){g(e("workspace.bulk_delete_error"));return}n.disabled=!0;try{const s=await fetch(a,{method:"DELETE",headers:q(!0),credentials:"same-origin",body:JSON.stringify({ids:[...O].map(Number)})}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("workspace.bulk_delete_error"));return}Number(i.deleted||0)>=Q.length&&r.leadPage>1&&r.leadPage--,A(),g(i.message||e("workspace.bulk_deleted",{count:O.size})),await j()}catch(s){console.error(s),g(e("workspace.bulk_delete_error"))}finally{n.isConnected&&(n.disabled=!1)}}function Tn(){const n=O.size;if(!n){g(e("workspace.bulk_nothing_selected"));return}const a=W,s=d("#leadBulkApply");if(!a||!s)return;if(a==="delete"){z(e("workspace.bulk_delete"),e("workspace.bulk_delete_confirm",{count:h(n)}),"",`<button type="button" class="btn" data-action-drawer-close>${t(e("workspace.cancel"))}</button><button type="button" class="btn danger" id="confirmBulkDeleteLead">${t(e("workspace.bulk_delete"))}</button>`,e("workspace.bulk_action")),d("#confirmBulkDeleteLead").onclick=l=>Mn(l.currentTarget);return}if(!Y){d("#leadBulkValue")?.focus();return}const i=Y==="__none__"?null:["beautician_id","spa_branch_id"].includes(a)?Number(Y):Y;Sn(a,i,s)}function st(){const n=d("#leadTableMount");if(!n)return;if(Et(),ta){n.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${t(e("workspace.loading"))}</strong></div>`;return}const a=Q;if(!a.length){const c=!!v.canCreateLead;n.innerHTML=`<div class="lead-empty">
      <div class="lead-empty__icon" aria-hidden="true">◎</div>
      <strong>${t(e("workspace.no_leads"))}</strong>
      <p>${t(e("workspace.no_leads_hint"))}</p>
      ${c?`<button type="button" class="btn primary" id="emptyAddLeadBtn">${t(e("workspace.empty_cta"))}</button>`:""}
    </div>`,d("#emptyAddLeadBtn")&&(d("#emptyAddLeadBtn").onclick=At);return}const s=!!(v.canEditLead||v.canDeleteLead);n.innerHTML=`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    ${s?`<th class="lead-table__select"><input class="lead-select-box" type="checkbox" id="leadSelectAll" aria-label="${t(e("workspace.bulk_select_all"))}"></th>`:""}
    <th class="lead-col--id">${t(e("workspace.col_lead_id"))}</th><th class="lead-col--date">${t(e("workspace.col_date"))}</th><th class="lead-col--customer">${t(e("workspace.col_customer"))}</th>
    <th class="lead-col--phone">${t(e("workspace.col_phone"))}</th><th class="lead-col--email">${t(e("workspace.col_email"))}</th><th class="lead-col--source">${t(e("workspace.col_source"))}</th>
    <th class="lead-col--beautician">${t(e("workspace.col_beautician"))}</th><th class="lead-col--branch">${t(e("workspace.col_branch"))}</th><th>${t(e("workspace.col_status"))}</th>
    <th class="lead-col--payment">${t(e("workspace.col_payment"))}</th><th class="is-num lead-col--sales">${t(e("workspace.col_sales"))}</th><th class="lead-col--follow-up" title="${t(e("workspace.col_last_fu"))}">${t(e("workspace.col_last_fu"))}</th>
    <th class="lead-table__actions"><span class="sr-only">${t(e("workspace.col_action"))}</span></th>
  </tr></thead><tbody>${a.map(c=>{const u=String(c.name||""),p=t((u[0]||"?").toUpperCase()),m=!!v.canEditLead,_=!!v.canDeleteLead,b=String(c.email||"").trim(),k=String(c.source||"").trim(),$=String(c.beautician||"").trim(),f=String(c.branch||"").trim(),w=String(c.last||"").trim(),M=Number(c.sales||0);return`<tr data-payment-status="${t(c.payment_status||"pending")}">
      ${s?`<td class="lead-table__select"><input class="lead-select-box" type="checkbox" value="${t(c.id)}" data-lead-select aria-label="${t(e("workspace.bulk_select_lead",{name:u||c.code||c.id}))}"></td>`:""}
      <td class="lead-col--id"><span class="lead-code">${t(c.code||c.id)}</span></td>
      <td class="lead-col--date"><span class="lead-date">${t(c.date||"—")}</span></td>
      <td>
        <div class="person-cell person-cell--lead">
          <div class="mini-avatar" aria-hidden="true">${p}</div>
          <div class="person-cell__text">
            <strong>${t(u||"—")}</strong>
            ${c.customer?`<small>${t(c.customer)}</small>`:""}
          </div>
        </div>
      </td>
      <td><span class="lead-mono">${t(c.phone||"—")}</span></td>
      <td class="lead-col--email">${b?`<span class="lead-email" title="${t(b)}">${t(b)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td class="lead-col--source">${k?`<span class="lead-tag">${t(k)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td class="lead-col--beautician">${$?t($):'<span class="lead-muted">—</span>'}</td>
      <td class="lead-col--branch">${f?`<span class="lead-branch">${t(f)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td>${x(c.status)}</td>
      <td class="lead-col--payment">${x(c.payment)}</td>
      <td class="is-num"><span class="lead-money${M?"":" is-zero"}">${M?R(M):"RM0"}</span></td>
      <td class="lead-col--follow-up"><span class="lead-date">${w?t(w):"—"}</span></td>
      <td class="lead-table__actions">
        <div class="lead-menu">
          <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${t(e("workspace.row_actions"))}">
            <span class="lead-menu__dots" aria-hidden="true"></span>
          </button>
          <div class="lead-menu__panel" role="menu" hidden>
            <button type="button" class="lead-menu__item" role="menuitem" data-lead-id="${t(c.id)}">${t(e("workspace.view"))}</button>
            ${m?`<button type="button" class="lead-menu__item" role="menuitem" data-lead-edit="${t(c.id)}">${t(e("workspace.edit"))}</button>`:""}
            ${_?`<button type="button" class="lead-menu__item lead-menu__item--danger" role="menuitem" data-lead-del="${t(c.id)}">${t(e("workspace.delete"))}</button>`:""}
          </div>
        </div>
      </td>
    </tr>`}).join("")}</tbody></table></div>`;const i=[10,50,100,200];n.insertAdjacentHTML("beforeend",`<div class="lead-pagination-bar">
    <label class="lead-page-size" for="leadPerPage">
      <span>${t(e("workspace.rows_per_page"))}</span>
      <select id="leadPerPage" class="lead-page-size__select">
        ${i.map(c=>`<option value="${c}" ${Number(r.leadPerPage)===c?"selected":""}>${c}</option>`).join("")}
      </select>
    </label>
    ${ne("leads",je)}
  </div>`);const l=d("#leadPerPage",n);l&&(l.onchange=()=>{r.leadPerPage=Number(l.value)||10,r.leadPage=1,l.disabled=!0,j()}),se("leads",c=>(r.leadPage=c,j()));const o=d("#leadSelectAll",n);o&&(o.onchange=()=>{Q.forEach(c=>o.checked?O.add(String(c.id)):O.delete(String(c.id))),ea()}),S("[data-lead-select]",n).forEach(c=>c.onchange=()=>{c.checked?O.add(String(c.value)):O.delete(String(c.value)),ea()}),ea(),Ye(n),S("[data-lead-id]",n).forEach(c=>c.onclick=()=>{H(),_a(c.dataset.leadId)}),S("[data-lead-edit]",n).forEach(c=>c.onclick=()=>{H(),Nt(c.dataset.leadEdit)}),S("[data-lead-del]",n).forEach(c=>c.onclick=()=>{H(),Dt(c.dataset.leadDel)})}function jt(n){n&&(n.classList.remove("is-up"),n.style.top="",n.style.left="",n.style.right="",n.style.bottom="")}function Cn(n,a){if(!n||!a)return;const s=4,i=n.getBoundingClientRect();a.style.top="0px",a.style.left="0px",a.style.right="auto",a.style.bottom="auto";const l=a.getBoundingClientRect(),c=window.innerHeight-i.bottom<l.height+s+8;a.classList.toggle("is-up",c);let u=c?i.top-l.height-s:i.bottom+s,p=i.right-l.width;p=Math.max(8,Math.min(p,window.innerWidth-l.width-8)),u=Math.max(8,Math.min(u,window.innerHeight-l.height-8)),a.style.top=`${Math.round(u)}px`,a.style.left=`${Math.round(p)}px`}function H(n=null){S(".lead-menu").forEach(a=>{if(n&&a===n)return;const s=d(".lead-menu__btn",a),i=d(".lead-menu__panel",a);s&&s.setAttribute("aria-expanded","false"),i&&(i.hidden=!0,jt(i)),a.classList.remove("is-open")})}function it(n){n.target.closest&&n.target.closest(".lead-menu")||H()}function lt(n){n.key==="Escape"&&H()}function Qe(){H()}function Ye(n){S("[data-lead-menu]",n).forEach(a=>{a.onclick=s=>{s.stopPropagation();const i=a.closest(".lead-menu"),l=d(".lead-menu__panel",i),o=a.getAttribute("aria-expanded")==="true";H(o?null:i),o?(a.setAttribute("aria-expanded","false"),l&&(l.hidden=!0,jt(l)),i.classList.remove("is-open")):(a.setAttribute("aria-expanded","true"),l&&(l.hidden=!1,Cn(a,l)),i.classList.add("is-open"))}}),document.removeEventListener("click",it),document.addEventListener("click",it),document.removeEventListener("keydown",lt),document.addEventListener("keydown",lt),window.removeEventListener("scroll",Qe,!0),window.addEventListener("scroll",Qe,!0),window.removeEventListener("resize",Qe),window.addEventListener("resize",Qe)}function At(){xe=null,z(e("workspace.manual_entry"),e("workspace.manual_sub"),qt({},!1),`<button class="btn" type="button" data-action-drawer-close>${t(e("workspace.cancel"))}</button><button class="btn primary" type="button" id="saveLead">${t(e("workspace.save"))}</button>`,e("workspace.title"))}function Nt(n){const a=Q.find(s=>String(s.id)===String(n));if(!a){_a(n);return}xe=a.id,z(e("workspace.edit_entry"),e("workspace.edit_sub"),qt(a,!0),`<button class="btn" type="button" data-action-drawer-close>${t(e("workspace.cancel"))}</button><button class="btn primary" type="button" id="saveLead">${t(e("workspace.save_changes"))}</button>`,e("workspace.title"))}function qt(n={},a=!1){const s=(P.statuses||[]).map(p=>`<option value="${t(p.value)}" ${String(n.status_key||"")===String(p.value)?"selected":""}>${t(p.label)}</option>`).join(""),i=[{id:"",name:"—"},...P.branches||[]].map(p=>`<option value="${t(p.id)}" ${String(n.branch_id||"")===String(p.id)?"selected":""}>${t(p.name)}</option>`).join(""),l=[{id:"",name:"—"},...P.beauticians||[]].map(p=>`<option value="${t(p.id)}" ${String(n.beautician_id||"")===String(p.id)?"selected":""}>${t(p.name)}</option>`).join(""),o=String(n.source||"manual"),c=t((String(n.name||"?")[0]||"?").toUpperCase());return`<form class="lead-editor" id="leadEditorForm" novalidate>
    ${a?`<section class="lead-editor__profile" aria-label="${t(e("workspace.lead_profile"))}">
      <span class="lead-editor__avatar" aria-hidden="true">${c}</span>
      <div><strong>${t(n.name||"—")}</strong><span>${t(n.code||n.id||"—")} · ${t(e("workspace.lead_created",{date:n.date||"—"}))}</span></div>
      <div class="lead-editor__status">${x(n.status)}</div>
    </section>`:""}
    <div class="lead-editor__error" id="leadFormError" role="alert" hidden></div>
    <section class="lead-editor__section" aria-labelledby="leadContactTitle">
      <div class="lead-editor__section-head"><h4 id="leadContactTitle">${t(e("workspace.contact_details"))}</h4><p>${t(e("workspace.contact_hint"))}</p></div>
      <div class="lead-editor__grid">
        <div class="lead-editor__field"><label for="mName">${t(e("workspace.name"))}</label><input class="search" id="mName" autocomplete="name" required value="${t(n.name||"")}"></div>
        <div class="lead-editor__field"><label for="mPhone">${t(e("workspace.phone"))}</label><input class="search" id="mPhone" type="tel" inputmode="tel" autocomplete="tel" required value="${t(n.phone||"")}"></div>
        <div class="lead-editor__field lead-editor__field--full"><label for="mEmail">${t(e("workspace.email"))}</label><input class="search" id="mEmail" type="email" inputmode="email" autocomplete="email" value="${t(n.email||"")}"></div>
      </div>
    </section>
    <section class="lead-editor__section" aria-labelledby="leadQualificationTitle">
      <div class="lead-editor__section-head"><h4 id="leadQualificationTitle">${t(e("workspace.qualification"))}</h4><p>${t(e("workspace.qualification_hint"))}</p></div>
      <div class="lead-editor__grid">
        <div class="lead-editor__field"><label for="mSource">${t(e("workspace.source"))}</label><select class="control" id="mSource">${["manual","TikTok","WhatsApp","Facebook","import"].map(p=>`<option value="${p}" ${o===p?"selected":""}>${p}</option>`).join("")}</select></div>
        <div class="lead-editor__field"><label for="mStatus">${t(e("workspace.col_status"))}</label><select class="control" id="mStatus">${s||'<option value="new">NEW</option>'}</select></div>
      </div>
    </section>
    <section class="lead-editor__section" aria-labelledby="leadOwnershipTitle">
      <div class="lead-editor__section-head"><h4 id="leadOwnershipTitle">${t(e("workspace.ownership"))}</h4><p>${t(e("workspace.ownership_hint"))}</p></div>
      <div class="lead-editor__grid">
        <div class="lead-editor__field"><label for="mBranch">${t(e("workspace.col_branch"))}</label><select class="control" id="mBranch">${i}</select></div>
        <div class="lead-editor__field"><label for="mBeautician">${t(e("workspace.col_beautician"))}</label><select class="control" id="mBeautician">${l}</select></div>
      </div>
    </section>
  </form>`}function ge(n=""){const a=d("#leadFormError");a&&(a.hidden=!n,a.textContent=n)}async function rt(){const n=xe!=null,a=n?N(v.leadUpdateUrlTemplate,xe):v.leadStoreUrl;if(!a){g(e("workspace.save_error"));return}const s={name:(d("#mName")?.value||"").trim(),phone:(d("#mPhone")?.value||"").trim(),email:(d("#mEmail")?.value||"").trim()||null,source:d("#mSource")?.value||"manual",status:d("#mStatus")?.value||void 0,spa_branch_id:d("#mBranch")?.value?Number(d("#mBranch").value):null,beautician_id:d("#mBeautician")?.value?Number(d("#mBeautician").value):null};if(!s.name||!s.phone){const o=e("workspace.required_details");ge(o),g(o),d("#mName")?.focus({preventScroll:!0});return}ge("");const i=d("#saveLead"),l=i?.textContent;i&&(i.disabled=!0,i.setAttribute("aria-busy","true"),i.textContent=e("workspace.saving"));try{const o=await fetch(a,{method:n?"PUT":"POST",headers:q(!0),credentials:"same-origin",body:JSON.stringify(s)}),c=await o.json().catch(()=>({}));if(!o.ok){const u=c&&(c.message||Object.values(c.errors||{})[0]?.[0])||e(n?"workspace.update_error":"workspace.save_error");ge(u),g(u);return}Me=!1,A(),xe=null,g(c.message||e(n?"workspace.updated":"workspace.saved")),n||(r.leadPage=1),await j()}catch(o){console.error(o);const c=e(n?"workspace.update_error":"workspace.save_error");ge(c),g(c)}finally{i&&(i.disabled=!1,i.removeAttribute("aria-busy"),i.textContent=l||e("workspace.save"))}}function Dt(n){v.canDeleteLead&&(z(e("workspace.delete"),e("workspace.delete_confirm"),"",`<button type="button" class="btn" data-action-drawer-close>${t(e("workspace.cancel"))}</button><button type="button" class="btn danger" id="confirmDeleteLead">${t(e("workspace.delete"))}</button>`,e("nav.leads")),d("#confirmDeleteLead").onclick=async a=>{const s=a.currentTarget;s.disabled=!0,await Ln(n),s.disabled=!1})}async function Ln(n){if(!v.canDeleteLead)return;const a=N(v.leadDestroyUrlTemplate,n);if(!a){g(e("workspace.delete_error"));return}try{const s=await fetch(a,{method:"DELETE",headers:q(!1),credentials:"same-origin"}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("workspace.delete_error"));return}g(i.message||e("workspace.deleted")),A(),Z(),await j()}catch(s){console.error(s),g(e("workspace.delete_error"))}}async function _a(n){let s=Q.find(_=>String(_.id)===String(n))||Be.find(_=>String(_.id)===String(n));const i=v.leadShowUrlTemplate;if(i)try{const _=await fetch(N(i,n),{headers:q(!1),credentials:"same-origin"});if(_.ok){const b=await _.json();s=b.data||s,b.filters?.statuses&&(P.statuses=b.filters.statuses)}}catch(_){console.error(_)}if(!s)return;A(),Z(),Ae=document.activeElement,Va=document.body.style.overflow,document.body.style.overflow="hidden";const l=d("#leadDrawer .eyebrow");l&&(l.textContent=e("workspace.drawer_eyebrow")),d("#drawerName").textContent=s.name||"";const o=xn(s),c=(P.statuses||[]).map(_=>`<option value="${t(_.value)}" ${String(s.status_key)===String(_.value)?"selected":""}>${t(_.label)}</option>`).join(""),u=!!v.canEditLead,p=String(s.name||""),m=t((p[0]||"?").toUpperCase());d("#drawerBody").innerHTML=`
  <div class="journey">
    <div class="journey-hero">
      <div class="journey-hero__avatar" aria-hidden="true">${m}</div>
      <div class="journey-hero__meta">
        <div class="journey-hero__code">${t(s.code||s.id)}</div>
        <div class="journey-hero__badges">
          ${x(s.status)}
          <span class="journey-pill journey-pill--${t(o.healthTone)}">${t(o.healthLabel)}</span>
          <span class="journey-pill journey-pill--prio-${t(o.priorityTone)}">${t(o.priorityLabel)}</span>
        </div>
      </div>
    </div>

    <section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.drawer_analytics"))}</div>
      <div class="journey-stats">
        <div class="journey-stat">
          <span class="journey-stat__label">${t(e("workspace.drawer_health"))}</span>
          <strong class="journey-stat__value">${o.score}</strong>
          <div class="journey-meter"><span style="width:${o.score}%"></span></div>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${t(e("workspace.drawer_stage"))}</span>
          <strong class="journey-stat__value">${o.stagePct}%</strong>
          <div class="journey-meter journey-meter--stage"><span style="width:${o.stagePct}%"></span></div>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${t(e("workspace.drawer_pipeline_days"))}</span>
          <strong class="journey-stat__value">${t(e("workspace.drawer_days",{count:o.daysInPipeline}))}</strong>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${t(e("workspace.drawer_since_contact"))}</span>
          <strong class="journey-stat__value">${o.neverContacted?t(e("workspace.drawer_never_contacted")):t(e("workspace.drawer_days",{count:o.daysSinceContact}))}</strong>
        </div>
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.drawer_signals"))}</div>
      <div class="journey-signals">
        ${o.signals.map(_=>`<span class="journey-signal journey-signal--${t(_.tone)}">${t(_.label)}</span>`).join("")||`<span class="journey-signal journey-signal--ok">${t(e("workspace.drawer_signal_healthy"))}</span>`}
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.drawer_pipeline"))}</div>
      <div class="journey-pipeline" role="list">${o.pipelineHtml}</div>
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

    ${u?`<section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.update_status"))}</div>
      <div class="journey-status-row">
        <select class="control" id="drawerStatus">${c}</select>
        <button class="btn primary" type="button" id="drawerSaveStatus">${t(e("workspace.update_status"))}</button>
      </div>
    </section>`:""}

    <div class="journey-actions">
      <div class="journey-section__title">${t(e("workspace.drawer_actions"))}</div>
      <div class="journey-actions__row">
        ${u?`<button class="btn primary" type="button" id="drawerFollowBtn">${t(e("followup.mark"))}</button>`:""}
        ${u?`<button class="btn" type="button" id="drawerEditBtn">${t(e("workspace.edit"))}</button>`:""}
        ${v.canDeleteLead?`<button class="btn danger" type="button" id="drawerDeleteBtn">${t(e("workspace.delete"))}</button>`:""}
      </div>
    </div>
  </div>`,d("#leadDrawer").classList.add("show"),d("#drawerBackdrop").classList.add("show"),d("#leadDrawer").setAttribute("aria-hidden","false"),d("#leadDrawer").inert=!1,d(".app-shell").inert=!0,d("#drawerClose").focus({preventScroll:!0}),ie(),d("#drawerFollowBtn")&&(d("#drawerFollowBtn").onclick=()=>Jt(s.id)),d("#drawerEditBtn")&&(d("#drawerEditBtn").onclick=()=>{Z(),Nt(s.id)}),d("#drawerDeleteBtn")&&(d("#drawerDeleteBtn").onclick=()=>Dt(s.id)),d("#drawerSaveStatus")&&(d("#drawerSaveStatus").onclick=async()=>{const _=d("#drawerStatus")?.value,b=N(v.leadStatusUrlTemplate,s.id);if(!_||!b){g(e("workspace.status_error"));return}try{const k=await fetch(b,{method:"PATCH",headers:q(!0),credentials:"same-origin",body:JSON.stringify({status:_})}),$=await k.json().catch(()=>({}));if(!k.ok){g($.message||e("workspace.status_error"));return}g($.message||e("workspace.status_updated")),r.view==="followup"?await ce():await j(),_a(s.id)}catch(k){console.error(k),g(e("workspace.status_error"))}})}function xn(n){const a=["new","claimed","follow_up","booking","payment_verified","converted"],s=String(n.status_key||"new"),i=a.indexOf(s),l=s==="lost"||s==="no_response"?Math.max(10,Math.round((Math.max(i,0)+1)/a.length*100)):Math.round((Math.max(i,0)+1)/a.length*100),o=Number(n.days_in_pipeline!=null?n.days_in_pipeline:n.days_since_followup||0),c=!n.last_followed_up_at,u=Number(n.days_since_followup||0),p=String(n.followup_bucket)==="overdue"||(c?o>=2:u>=2);let m=28;s==="converted"?m=96:s==="payment_verified"?m=82:s==="booking"?m=68:s==="follow_up"?m=54:s==="claimed"?m=42:s==="new"?m=32:s==="no_response"?m=22:s==="lost"&&(m=12),n.existing&&(m+=8),n.duplicate&&(m-=6),p&&(m-=18),!c&&u===0&&(m+=6),n.beautician_id&&(m+=4),n.branch_id&&(m+=3),m=Math.max(5,Math.min(99,m));let _="warm",b=e("workspace.drawer_health_warm");m>=75?(_="hot",b=e("workspace.drawer_health_hot")):m<35||p||s==="lost"||s==="no_response"?(_="risk",b=e("workspace.drawer_health_risk")):m<50&&(_="cold",b=e("workspace.drawer_health_cold"));let k="med",$=e("workspace.drawer_priority_medium");p||s==="no_response"||!n.beautician_id&&o>=1?(k="high",$=e("workspace.drawer_priority_high")):(s==="converted"||s==="payment_verified")&&(k="low",$=e("workspace.drawer_priority_low"));const f=[];p&&f.push({tone:"danger",label:e("workspace.drawer_signal_overdue")}),o<=1&&s==="new"&&f.push({tone:"info",label:e("workspace.drawer_signal_fresh")}),n.existing&&f.push({tone:"ok",label:e("workspace.drawer_signal_existing")}),n.duplicate&&f.push({tone:"warn",label:e("workspace.drawer_signal_duplicate")}),!n.beautician_id&&s!=="converted"&&f.push({tone:"warn",label:e("workspace.drawer_signal_unassigned")}),!n.branch_id&&s!=="converted"&&f.push({tone:"warn",label:e("workspace.drawer_signal_no_branch")}),f.length||f.push({tone:"ok",label:e("workspace.drawer_signal_healthy")});const w=a.map((M,y)=>{const T=(P.statuses||[]).find(K=>K.value===M)?.label||M.replace(/_/g," ");return`<div class="journey-step${i>y||s==="converted"?" is-done":i===y?" is-active":""}" role="listitem"><span class="journey-step__dot"></span><span class="journey-step__label">${t(T)}</span></div>`}).join("");return{score:m,stagePct:l,daysInPipeline:o,daysSinceContact:u,neverContacted:c,healthTone:_,healthLabel:b,priorityTone:k,priorityLabel:$,signals:f,pipelineHtml:w}}let I=null,$a=[],wa={total_imports:0,raw:0,unique:0,duplicates:0,existing:0,invalid:0,imported:0},Se=!1;function Bn(){E.innerHTML=`${be(e("import.title"),e("import.subtitle"),`<button type="button" class="btn" data-jump="imports">${t(e("import.history_btn"))}</button><button type="button" class="btn primary" data-jump="leads">${t(e("import.view_leads"))}</button>`)}
  <section class="card">
    <div class="tabs" id="importTabs" role="tablist">${[["paste",e("import.tab_paste")],["excel",e("import.tab_excel")],["csv",e("import.tab_csv")],["manual",e("import.tab_manual")]].map(n=>`<button type="button" class="tab ${r.importTab===n[0]?"active":""}" role="tab" aria-selected="${r.importTab===n[0]?"true":"false"}" data-tab="${n[0]}">${t(n[1])}</button>`).join("")}</div>
    <div id="importPane" style="margin-top:14px"></div>
  </section>
  <section class="card hidden" id="previewCard" style="margin-top:12px"></section>`,Ht(),ie(),Pn()}function Pn(){const n=S("[data-tab]","#importTabs");n.forEach(a=>{a.onclick=s=>{s.preventDefault();const i=a.dataset.tab;if(!i||i===r.importTab)return;r.importTab=i,n.forEach(o=>{const c=o===a;o.classList.toggle("active",c),o.setAttribute("aria-selected",c?"true":"false")}),I=null;const l=d("#previewCard");l&&l.classList.add("hidden"),Ht()}})}function Ht(){const n=d("#importPane");if(!n)return;const a=!!v.canCreateLead;if(r.importTab==="paste")n.innerHTML=`<div><div class="card-title">${t(e("import.paste_title"))}</div><div class="card-subtitle">${t(e("import.paste_sub"))}</div><textarea class="paste-area" id="pasteArea" placeholder="${t(e("import.paste_placeholder"))}"></textarea><div style="display:flex;justify-content:flex-end;margin-top:10px"><button type="button" class="btn primary" id="parseBtn" ${a?"":"disabled"}>${t(e("import.parse"))}</button></div></div>`,d("#parseBtn")&&(d("#parseBtn").onclick=()=>En());else if(r.importTab==="manual")n.innerHTML=`<div class="detail-grid"><div><label class="kpi-label" for="manualName">${t(e("import.name"))}</label><input class="search" style="width:100%" id="manualName" autocomplete="name"></div><div><label class="kpi-label" for="manualPhone">${t(e("import.phone"))}</label><input class="search" style="width:100%" id="manualPhone" inputmode="tel" autocomplete="tel"></div><div><label class="kpi-label" for="manualEmail">${t(e("import.email"))}</label><input class="search" style="width:100%" id="manualEmail" type="email" autocomplete="email"></div><div><label class="kpi-label" for="manualSource">${t(e("import.source"))}</label><select class="control" style="width:100%" id="manualSource"><option value="TikTok">TikTok</option><option value="WhatsApp">WhatsApp</option><option value="Facebook">Facebook</option><option value="manual">Import</option></select></div><div style="grid-column:1/-1;text-align:right"><button type="button" class="btn primary" id="manualSaveBtn" ${a?"":"disabled"}>${t(e("import.save_lead"))}</button></div></div>`,d("#manualSaveBtn")&&(d("#manualSaveBtn").onclick=jn);else{const s=r.importTab==="excel";n.innerHTML=`<div class="import-zone" id="importDropZone" tabindex="0"><div class="import-icon">⇧</div><h3>${t(e(s?"import.drop_excel":"import.drop_csv"))}</h3><p>${t(e(s?"import.accepted_excel":"import.accepted_csv"))}</p><input type="file" id="fileInput" class="hidden" accept="${s?".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel":".csv,text/csv,text/plain"}"><button type="button" class="btn primary" id="browseBtn" ${a?"":"disabled"}>${t(e("import.browse"))}</button></div>`;const i=d("#fileInput"),l=d("#browseBtn"),o=d("#importDropZone");l&&i&&(l.onclick=c=>{c.preventDefault(),c.stopPropagation(),i.click()}),i&&(i.onchange=()=>ot(i.files?.[0])),o&&a&&(o.addEventListener("click",c=>{c.target===l||l?.contains(c.target)||i?.click()}),["dragenter","dragover"].forEach(c=>o.addEventListener(c,u=>{u.preventDefault(),u.stopPropagation(),o.classList.add("is-dragover")})),["dragleave","drop"].forEach(c=>o.addEventListener(c,u=>{u.preventDefault(),u.stopPropagation(),o.classList.remove("is-dragover")})),o.addEventListener("drop",c=>{const u=c.dataTransfer?.files?.[0];u&&ot(u)}),o.addEventListener("keydown",c=>{(c.key==="Enter"||c.key===" ")&&(c.preventDefault(),i?.click())}))}}async function En(){const n=d("#pasteArea")?.value||"";if(!n.trim()){g(e("import.paste_required"));return}await Oa({method:"paste",paste:n})}async function ot(n){if(!n){g(e("import.file_required"));return}const a=new FormData;a.append("method",r.importTab==="excel"?"excel":"csv"),a.append("file",n),r.branch&&r.branch!=="all"&&a.append("spa_branch_id",r.branch),await Oa(a,!0)}async function jn(){const n=(d("#manualName")?.value||"").trim(),a=(d("#manualPhone")?.value||"").trim(),s=(d("#manualEmail")?.value||"").trim(),i=(d("#manualSource")?.value||"manual").trim();if(!n||!a){g(e("import.rows_required"));return}await Oa({method:"manual",rows:[{name:n,phone:a,email:s||null,source:i}]})}async function Oa(n,a=!1){const s=v.importPreviewUrl;if(!s){g(e("import.preview_error"));return}if(!Se){Se=!0,g(e("import.parsing"));try{const i={method:"POST",credentials:"same-origin",headers:q(!a)};a?i.body=n:(r.branch&&r.branch!=="all"&&!n.spa_branch_id&&(n.spa_branch_id=Number(r.branch)||null),i.body=JSON.stringify(n));const l=await fetch(s,i),o=await l.json().catch(()=>({}));if(!l.ok){const c=o.message||Object.values(o.errors||{}).flat()[0]||e("import.preview_error");g(c);return}I=o.data||null,An()}catch(i){console.error(i),g(e("import.preview_error"))}finally{Se=!1}}}function An(){const n=d("#previewCard");if(!n||!I)return;const a=I.rows||[],s=I.summary||{},i=Number(s.ready||0)+Number(s.existing||0);n.classList.remove("hidden"),n.innerHTML=`<div class="card-title-row"><div><div class="card-title">${t(e("import.preview_title"))}</div><div class="card-subtitle">${t(e("import.preview_sub"))}</div></div><button class="btn small" type="button" id="closePreviewBtn">${t(e("import.close"))}</button></div>
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
  <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px"><button class="btn" type="button" id="cancelImportBtn">${t(e("import.cancel"))}</button><button class="btn primary" type="button" id="confirmImport" ${i>0?"":"disabled"}>${t(e("import.confirm",{count:i}))}</button></div>`,d("#closePreviewBtn")?.addEventListener("click",()=>n.classList.add("hidden")),d("#cancelImportBtn")?.addEventListener("click",()=>n.classList.add("hidden")),d("#confirmImport")?.addEventListener("click",Nn),n.scrollIntoView({behavior:"smooth",block:"start"})}async function Nn(){if(!I||Se)return;const n=v.importConfirmUrl;if(!n){g(e("import.confirm_error"));return}const a=(I.rows||[]).map(s=>({name:s.name,phone:s.phone_norm||s.phone_orig,email:s.email||null,source:s.source||I.source||"import",import:s.detection==="READY"||s.detection==="EXISTING"}));Se=!0,g(e("import.importing"));try{const s=await fetch(n,{method:"POST",credentials:"same-origin",headers:q(!0),body:JSON.stringify({method:I.method||"paste",rows:a,source:I.source||"import",file_name:I.file_name||null,spa_branch_id:I.spa_branch_id||null,beautician_id:I.beautician_id||null})}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("import.confirm_error"));return}g(i.message||e("import.imported",{count:i.data&&i.data.imported||0})),I=null,setTimeout(()=>V("leads"),700)}catch(s){console.error(s),g(e("import.confirm_error"))}finally{Se=!1}}async function qn(){E.innerHTML=`${be(e("import.history_title"),e("import.history_subtitle"),`<button class="btn primary" data-jump="import">${t(e("import.new_import"))}</button>`)}
  <div class="grid kpi-grid" id="importKpiMount"></div>
  <section class="card" style="margin-top:12px"><div id="importHistoryMount"><div class="empty">${t(e("workspace.loading"))}</div></div></section>`,ie(),await Rt()}async function Rt(){const n=v.importHistoryUrl,a=d("#importHistoryMount"),s=d("#importKpiMount");if(!n){a&&(a.innerHTML=`<div class="empty">${t(e("import.load_error"))}</div>`);return}try{const i=await fetch(n+"?per_page=50&page="+et,{headers:q(!1),credentials:"same-origin"}),l=await i.json().catch(()=>({}));if(!i.ok){g(l.message||e("import.load_error"));return}$a=l.data||[],wa=l.meta?.summary||wa;const o=wa;if(s&&(s.innerHTML=`${X("▤",e("import.kpi_total"),h(o.total_imports),e("common.this_month"),e("import.meta_batches"),"blue")}${X("♙",e("import.kpi_raw"),h(o.raw),e("import.meta_historical"),"","rose")}${X("✓",e("import.kpi_unique"),h(o.unique),e("import.meta_after_clean"),"","green")}${X("⧉",e("import.kpi_duplicates"),h(o.duplicates),e("import.meta_auditable"),"","purple")}${X("♧",e("import.kpi_existing"),h(o.existing),e("import.meta_phone"),"","blue")}`),!a)return;if(!$a.length){a.innerHTML=`<div class="empty"><strong>${t(e("import.empty"))}</strong>${t(e("import.empty_hint"))}</div>`;return}a.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
      <th>${t(e("import.col_batch"))}</th><th>${t(e("import.col_date"))}</th>
      <th title="${t(e("import.col_by"))}">${t(e("import.col_by"))}</th>
      <th>${t(e("import.col_method"))}</th><th>${t(e("import.col_file"))}</th>
      <th>${t(e("import.col_raw"))}</th><th>${t(e("import.col_unique"))}</th>
      <th>${t(e("import.col_duplicate"))}</th><th>${t(e("import.col_existing"))}</th>
      <th>${t(e("import.col_invalid"))}</th><th>${t(e("import.col_status"))}</th>
    </tr></thead><tbody>
    ${$a.map(c=>`<tr>
      <td><strong>${t(c.batch_code||"")}</strong></td><td>${t(c.date||"")}</td>
      <td>${t(c.by||"")}</td><td>${t(c.method||"")}</td><td>${t(c.file||"")}</td>
      <td>${h(c.raw)}</td><td>${h(c.unique)}</td><td>${h(c.duplicate)}</td>
      <td>${h(c.existing)}</td><td>${h(c.invalid)}</td><td>${x(c.status)}</td>
    </tr>`).join("")}
    </tbody></table></div>${ne("imports",l.meta||{})}`,se("imports",c=>(et=c,Rt()))}catch(i){console.error(i),a&&(a.innerHTML=`<div class="empty">${t(e("import.load_error"))}</div>`)}}function Ut(){const n=r.paymentCustomerId?`<div class="pay-customer-chip" id="payCustomerChip">
        <span>${t(e("payments.filtered_customer",{name:r.paymentCustomerLabel||"#"+r.paymentCustomerId}))}</span>
        <button type="button" class="btn small soft" id="payClearCustomer">${t(e("payments.clear_customer"))}</button>
      </div>`:"";E.innerHTML=`<div class="pay-shell">
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
          <p class="lead-panel__sub" id="payHeroCopy" style="margin:0">${t(r.paymentCustomerId?e("payments.filtered_customer",{name:r.paymentCustomerLabel||"#"+r.paymentCustomerId}):e("payments.subtitle"))}</p>
        </div>
        <div class="lead-panel__head-meta">
          <span class="lead-panel__count" id="payResultCount">—</span>
        </div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="payTabs" role="tablist"></div>
        <label class="lead-search" for="paySearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="paySearch" type="search" autocomplete="off" placeholder="${t(e("payments.search_placeholder"))}" value="${t(r.paymentSearch||"")}" />
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
  </div>`;const a=d("#payRefresh");a&&(a.onclick=()=>ae());const s=d("#payClearCustomer");s&&(s.onclick=()=>Dn()),Hn(),ae()}function Dn(){r.paymentCustomerId=null,r.paymentCustomerLabel="",r.paymentSearch="",r.paymentPage=1,r.view==="payments"?Ut():ae()}function Ft(n){if(!n)return;const a=Number(n.id||0);a&&(r.paymentCustomerId=a,r.paymentCustomerLabel=String(n.name||n.code||"#"+a),r.paymentSearch=String(n.phone||n.email||n.name||"").trim(),r.paymentTab="all",r.paymentPage=1,r.paymentBeautician="all",V("payments"))}function Hn(){const n=d("#paySearch");n&&(n.oninput=()=>{clearTimeout(Ja),Ja=setTimeout(()=>{r.paymentSearch=n.value.trim(),r.paymentPage=1,ae()},320)});const a=d("#payBeautician");a&&(a.onchange=()=>{r.paymentBeautician=a.value,r.paymentPage=1,ae()});const s=d("#payBranch");s&&(s.onchange=()=>{r.paymentBranch=s.value,r.paymentPage=1,ae()})}async function ae(){const n=v.paymentsUrl||"",a=d("#payTableMount");if(!n){a&&(a.innerHTML=`<div class="pay-empty"><strong>${t(e("payments.load_error"))}</strong></div>`);return}sa=!0,ct(),dt();const s=new URLSearchParams;r.paymentCustomerId?s.set("customer_id",String(r.paymentCustomerId)):r.paymentSearch&&s.set("q",r.paymentSearch),r.paymentTab&&r.paymentTab!=="all"&&s.set("status",r.paymentTab);const i=r.paymentBranch!=="all"?r.paymentBranch:r.branch||"all";i&&i!=="all"&&s.set("branch",i),r.paymentBeautician&&r.paymentBeautician!=="all"&&s.set("beautician",r.paymentBeautician),r.period&&s.set("period",r.period),s.set("page",String(r.paymentPage||1)),s.set("per_page","25");try{const l=await fetch(`${n}?${s.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("payments "+l.status);const o=await l.json();ye=Array.isArray(o.data)?o.data:[],Na=Object.assign({pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0},o.meta&&o.meta.summary||{}),$e=Object.assign({statuses:[],beauticians:[],branches:[]},o.filters||{}),La={current_page:o.meta&&o.meta.current_page||1,last_page:o.meta&&o.meta.last_page||1,total:o.meta&&o.meta.total||0}}catch(l){console.error(l),ye=[],g(e("payments.load_error"))}finally{sa=!1,ct(),Un(),Fn(),dt()}}function Rn(n){const a=String(n||"pending");return a==="paid"?{customer:"done",accountant:"done",hq:"done",active:null}:a==="processing"?{customer:"done",accountant:"done",hq:"active",active:"hq"}:a==="canceled"||a==="refunded"?{customer:"done",accountant:"active",hq:null,active:"accountant"}:{customer:"done",accountant:"active",hq:null,active:"accountant"}}function It(n){const a=Rn(n),s=l=>{const o=a[l];return`<span class="pay-pipe__node ${o==="done"?"is-done":o==="active"?"is-active":""}" title="${t(e("payments.flow_"+(l==="hq"?"hq":l)))}"></span>`},i=l=>`<span class="pay-pipe__line ${a[l]==="done"?"is-done":""}"></span>`;return`<div class="pay-pipe" title="${t(e("payments.pipeline"))}">${s("customer")}${i("customer")}${s("accountant")}${i("accountant")}${s("hq")}</div>`}function ct(){const n=Na,a=d("#payHeroCopy");a&&(a.textContent=n.queue>0?e("payments.pulse_busy",{count:h(n.queue),amount:R(n.pending_amount)}):e("payments.pulse_clear"));const s=d("#payHeroStats");s&&(s.innerHTML=`
      <div class="pay-metric payment-metric payment-metric--queue"><span>${t(e("payments.stat_queue"))}</span><strong>${h(n.queue)}</strong></div>
      <div class="pay-metric payment-metric payment-metric--paid"><span>${t(e("payments.stat_paid"))}</span><strong>${R(n.paid_amount)}</strong></div>
      <div class="pay-metric payment-metric payment-metric--today"><span>${t(e("payments.stat_today"))}</span><strong>${h(n.paid_today)}</strong></div>
      <div class="pay-metric payment-metric payment-metric--hold"><span>${t(e("payments.stat_hold"))}</span><strong>${h(n.hold)}</strong></div>
    `)}function Un(){const n=d("#payTabs");if(!n)return;const a=Na,s={all:(a.pending||0)+(a.processing||0)+(a.paid||0)+(a.hold||0)+(a.refunded||0),queue:a.queue||0,pending:a.pending||0,processing:a.processing||0,paid:a.paid||0,canceled:a.hold||0,refunded:a.refunded||0},l=[...$e.statuses&&$e.statuses.length?$e.statuses:[{value:"queue",label:e("payments.tab_queue")},{value:"all",label:e("payments.tab_all")},{value:"pending",label:e("payments.tab_pending")},{value:"processing",label:e("payments.tab_processing")},{value:"paid",label:e("payments.tab_paid")},{value:"canceled",label:e("payments.tab_hold")},{value:"refunded",label:e("payments.tab_refunded")}]].sort((o,c)=>(o.value==="queue"?-1:0)-(c.value==="queue"?-1:0));n.innerHTML=l.map(o=>{const c=o.value,u=r.paymentTab===c?"active":"",p=s[c],m=p!==void 0?`<span class="pay-tab-count">${h(p)}</span>`:"";return`<button type="button" class="tab ${u}" role="tab" data-ptab="${t(c)}">${t(o.label)}${m}</button>`}).join(""),S("[data-ptab]",n).forEach(o=>{o.onclick=()=>{r.paymentTab=o.dataset.ptab,r.paymentPage=1,ae()}})}function Fn(){const n=d("#payBeautician");if(n){const s=r.paymentBeautician;n.innerHTML=`<option value="all">${t(e("payments.all_beauticians"))}</option>`+($e.beauticians||[]).map(i=>`<option value="${i.id}" ${String(s)===String(i.id)?"selected":""}>${t(i.name)}</option>`).join("")}const a=d("#payBranch");if(a){const s=r.paymentBranch;a.innerHTML=`<option value="all">${t(e("payments.all_branches"))}</option>`+($e.branches||[]).map(i=>`<option value="${i.id}" ${String(s)===String(i.id)?"selected":""}>${t(i.code?i.code+" · "+i.name:i.name)}</option>`).join("")}}function dt(){const n=d("#payTableMount"),a=d("#payResultCount");if(a&&(a.textContent=e("payments.result_count",{count:h(La.total||ye.length)})),!n)return;if(sa){n.innerHTML=`<div class="pay-empty"><strong>${t(e("payments.loading"))}</strong></div>`;return}if(!ye.length){n.innerHTML=`<div class="pay-empty"><strong>${t(e("payments.empty"))}</strong><span>${t(e("payments.empty_hint"))}</span></div>`;return}const s=ye.map(l=>{const o=l.ref&&l.ref!=="—",c=[l.has_proof?`<span class="pay-chip pay-chip--ok">${t(e("payments.chip_proof"))}</span>`:`<span class="pay-chip pay-chip--muted">${t(e("payments.stage_declared"))}</span>`,o?`<span class="pay-chip pay-chip--ok">${t(e("payments.chip_ref"))}</span>`:`<span class="pay-chip pay-chip--warn">${t(e("payments.chip_no_ref"))}</span>`].join("");return`<tr>
      <td><span class="pay-id">${t(l.code||"ORD-"+l.id)}</span></td>
      <td><div class="person-cell"><div class="mini-avatar">${t(l.initial||"?")}</div><div><strong>${t(l.customer||"")}</strong><small>${t(l.phone||"")}</small></div></div></td>
      <td>${t(l.branch||"—")}</td>
      <td>${t(l.beautician||"—")}</td>
      <td><div class="pay-amount">${R(l.amount)}</div><div class="pay-method">${t(l.payment_method_label||"")}</div></td>
      <td><div class="pay-chips">${c}</div><div class="pay-method" title="${t(e("payments.col_ref"))}">${t(l.ref||"—")}</div></td>
      <td>${It(l.payment_status)}</td>
      <td>${x(l.payment_status_label||l.payment_status)}</td>
      <td><button type="button" class="pay-review-btn" data-payment="${l.id}">${t(e("payments.review"))}</button></td>
    </tr>`}).join(""),i=ne("pay",La);n.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("payments.col_id"))}</th>
    <th>${t(e("payments.col_customer"))}</th>
    <th>${t(e("payments.col_branch"))}</th>
    <th>${t(e("payments.col_beautician"))}</th>
    <th>${t(e("payments.col_amount"))}</th>
    <th>${t(e("payments.col_ref"))}</th>
    <th>${t(e("payments.pipeline"))}</th>
    <th>${t(e("payments.col_status"))}</th>
    <th>${t(e("payments.col_action"))}</th>
  </tr></thead><tbody>${s}</tbody></table></div>${i}`,S("[data-payment]",n).forEach(l=>l.onclick=()=>On(Number(l.dataset.payment))),se("pay",l=>(r.paymentPage=l,ae()))}function In(n){const a=n.proof;let s;if(!v.canViewOrder)s=`<p class="payment-proof__empty">${t(e("payments.proof_access"))}</p>`;else if(!a?.url)s=`<p class="payment-proof__empty">${t(e(n.has_proof?"payments.proof_unavailable":"payments.proof_missing"))}</p>`;else{const i=t(a.url),l=t(a.name||e("payments.proof_title")),o=`<a class="btn small" href="${i}" target="_blank" rel="noopener noreferrer">${t(e("payments.proof_open"))}</a>`;s=`${a.kind==="image"?`<a class="payment-proof__image" href="${i}" target="_blank" rel="noopener noreferrer"><img id="paymentProofImage" src="${i}" alt="${l}" loading="lazy"></a><p class="payment-proof__empty" id="paymentProofError" hidden>${t(e("payments.proof_unavailable"))}</p>`:a.kind==="pdf"?`<object class="payment-proof__pdf" data="${i}" type="application/pdf" aria-label="${l}"><p class="payment-proof__empty">${t(e("payments.proof_pdf_hint"))}</p></object>`:`<p class="payment-proof__empty">${t(e("payments.proof_pdf_hint"))}</p>`}<div class="payment-proof__file"><span>${l}</span>${o}</div>`}return`<section class="payment-proof"><h3>${t(e("payments.proof_title"))}</h3>${s}</section>`}function On(n){const a=ye.find(m=>Number(m.id)===Number(n));if(!a)return;const s=N(v.orderShowUrlTemplate,a.id),i=!!v.canEditOrder,l=!!v.canViewOrder,o=["identity","invoice","method","ref","proof","status"].map(m=>{const _=a.checklist?.[m],b=["completed","not_applicable"].includes(_)?_:"pending";return`<div class="pay-review__check is-${b}" data-check="${m}"><span class="pay-review__check-icon" aria-hidden="true">${b==="completed"?"✓":b==="not_applicable"?"—":"○"}</span><span>${t(e("payments.check_"+m))}</span><small>${t(e("payments.check_"+b))}</small></div>`}).join(""),c=`<div class="pay-review">
    <div class="pay-review__hero">
      <div class="pay-review__avatar">${t(a.initial||"?")}</div>
      <div style="min-width:0;flex:1">
        <div class="pay-id">${t(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${t(a.customer||"")}</strong>
        <div class="pay-method">${t(a.phone||"")} · ${t(a.branch_name||a.branch||"")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px;align-items:center">${x(a.payment_status_label||a.payment_status)}${It(a.payment_status)}</div>
      </div>
      <div style="text-align:right"><div class="pay-amount">${R(a.amount)}</div><div class="pay-method">${t(a.payment_method_label||"")}</div></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${t(e("payments.col_customer_stage"))}</label><strong>${t(a.customer_stage)}</strong></div>
      <div class="pay-review__card"><label>${t(e("payments.col_accountant"))}</label><strong>${t(a.accountant_stage)}</strong></div>
      <div class="pay-review__card"><label>${t(e("payments.col_hq"))}</label><strong>${t(a.hq_stage)}</strong></div>
    </div>
    ${In(a)}
    ${i?`<div class="detail-grid">
      <div class="detail-box"><label>${t(e("payments.bank_ref"))}</label><input class="control" id="payRefInput" value="${t(a.ref==="—"?"":a.ref)}" placeholder="${t(e("payments.bank_ref_ph"))}" /></div>
      <div class="detail-box" style="grid-column:span 2"><label>${t(e("payments.admin_note"))}</label><input class="control" id="payNoteInput" value="${t(a.admin_note||"")}" /></div>
    </div>`:""}
    <div class="journey-section"><div class="journey-section__title">${t(e("payments.checklist"))}</div><div class="pay-review__checks">${o}</div><p class="pay-review__check-note">${t(e("payments.check_note"))}</p></div>
    ${l?"":`<p class="card-subtitle">${t(e("payments.no_order_access"))}</p>`}
  </div>`,u=[l?`<a class="btn" href="${t(s)}" target="_blank" rel="noopener">${t(e("payments.open_order"))}</a>`:"",i?`<button type="button" class="btn" id="payMarkProcessing">${t(e("payments.mark_processing"))}</button>`:"",i?`<button type="button" class="btn danger" id="payMarkHold">${t(e("payments.mark_hold"))}</button>`:"",i?`<button type="button" class="btn success" id="payMarkPaid">${t(e("payments.mark_paid"))}</button>`:"",`<button type="button" class="btn" data-action-drawer-close>${t(e("payments.close"))}</button>`].filter(Boolean).join("");z(e("payments.review_title"),e("payments.review_sub",{code:a.code,customer:a.customer}),c,u,"Pay Verify");const p=d("#paymentProofImage");if(p){const m=()=>{p.closest("a").hidden=!0,d("#paymentProofError").hidden=!1};p.onerror=m,p.complete&&!p.naturalWidth&&m()}setTimeout(()=>{const m=d("#payMarkProcessing");m&&(m.onclick=()=>ka(a,"processing"));const _=d("#payMarkHold");_&&(_.onclick=()=>ka(a,"canceled"));const b=d("#payMarkPaid");b&&(b.onclick=()=>ka(a,"paid"))},0)}async function ka(n,a){const s=N(v.orderPaymentStatusUrlTemplate,n.id);if(!s||!v.canEditOrder){g(e("payments.update_error"));return}const i=d("#payRefInput"),l=d("#payNoteInput"),o=i?i.value.trim():"",c=l?l.value.trim():"";if(n.needs_reference&&(a==="paid"||a==="processing")&&!o&&(n.ref==="—"||!n.ref)){g(e("payments.ref_required"));return}try{const u=await fetch(s,{method:"PUT",headers:q(!0),credentials:"same-origin",body:JSON.stringify({payment_status:a,transaction_id:o||void 0,admin_note:c||void 0})}),p=await u.json().catch(()=>({}));if(!u.ok){g(p.message||e("payments.update_error"));return}A(),g(p.message||e("payments.updated")),await ae()}catch(u){console.error(u),g(e("payments.update_error"))}}function Vn(){E.innerHTML=`<div class="cin-shell">
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
          <input class="lead-search__input" id="cinSearch" aria-label="${t(e("checkin.search_placeholder"))}" type="search" autocomplete="off" placeholder="${t(e("checkin.search_placeholder"))}" value="${t(r.checkinSearch||"")}" />
        </label>
        <div class="lead-filter-grid ops-filter-grid">
          <label class="lead-field"><span class="lead-field__label">${t(e("checkin.filter_scope"))}</span>
            <select class="lead-field__control" id="cinScope">
              <option value="pipeline"${r.checkinScope==="pipeline"?" selected":""}>${t(e("checkin.scope_pipeline"))}</option>
              <option value="day"${r.checkinScope==="day"?" selected":""}>${t(e("checkin.scope_day"))}</option>
            </select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${t(e("checkin.filter_date"))}</span>
            <input class="lead-field__control" id="cinDate" type="date" value="${t(r.checkinDate||"")}"${r.checkinScope==="pipeline"?" disabled":""} />
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
  </div>`;const n=d("#cinRefresh");n&&(n.onclick=()=>J());const a=d("#cinScan");a&&(a.onclick=Qn),Wn(),J()}function Wn(){const n=d("#cinSearch");n&&(n.oninput=()=>{clearTimeout(Qa),Qa=setTimeout(()=>{r.checkinSearch=n.value.trim(),r.checkinPage=1,J()},320)});const a=d("#cinScope");a&&(a.onchange=()=>{r.checkinScope=a.value,a.value==="pipeline"&&(r.checkinStatus="live");const o=d("#cinDate");o&&(o.disabled=a.value==="pipeline"),r.checkinPage=1,J()});const s=d("#cinDate");s&&(s.onchange=()=>{r.checkinDate=s.value,r.checkinScope="day",d("#cinScope").value="day",r.checkinPage=1,J()});const i=d("#cinBeautician");i&&(i.onchange=()=>{r.checkinBeautician=i.value,r.checkinPage=1,J()});const l=d("#cinBranch");l&&(l.onchange=()=>{r.checkinBranch=l.value,r.checkinPage=1,J()})}async function J(){const n=++Ge;Fe=!1;const a=v.checkinUrl||"",s=d("#cinTableMount");if(!a){s&&(s.innerHTML=`<div class="pay-empty"><strong>${t(e("checkin.load_error"))}</strong></div>`);return}me=!0,pt(),ut();const i=new URLSearchParams;r.checkinSearch&&i.set("q",r.checkinSearch),i.set("status",r.checkinStatus||"live"),i.set("scope",r.checkinScope||"day"),r.checkinDate&&i.set("date",r.checkinDate);const l=r.checkinBranch!=="all"?r.checkinBranch:r.branch||"all";l&&l!=="all"&&i.set("branch",l),r.checkinBeautician&&r.checkinBeautician!=="all"&&i.set("beautician",r.checkinBeautician),i.set("page",String(r.checkinPage||1)),i.set("per_page","25");try{const o=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!o.ok)throw new Error("checkin "+o.status);const c=await o.json();if(n!==Ge)return;ue=Array.isArray(c.data)?c.data:[],Ra=Object.assign({live:0,scheduled:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0},c.meta&&c.meta.summary||{}),xa=Object.assign({statuses:[],beauticians:[],branches:[]},c.filters||{}),Ua={current_page:c.meta&&c.meta.current_page||1,last_page:c.meta&&c.meta.last_page||1,total:c.meta&&c.meta.total||0}}catch(o){if(n!==Ge)return;Fe=!0,console.error(o),ue=[],g(e("checkin.load_error"))}finally{if(n!==Ge)return;me=!1,pt(),Yn(),Kn(),ut()}}function pt(){const n=d("#cinMetrics");if(!n)return;const a=Ra;n.setAttribute("aria-busy",String(me));const s=i=>me||Fe?"—":h(i);n.innerHTML=`
    <div class="pay-metric checkin-metric checkin-metric--scheduled"><span>${t(e("checkin.stat_scheduled"))}</span><strong>${s(a.scheduled)}</strong></div>
    <div class="pay-metric checkin-metric checkin-metric--waiting"><span>${t(e("checkin.stat_waiting"))}</span><strong>${s(a.waiting)}</strong><small>${t(e("checkin.stat_waiting_meta",{minutes:s(a.avg_wait_mins),unpaid:s(a.unpaid)}))}</small></div>
    <div class="pay-metric checkin-metric checkin-metric--treatment"><span>${t(e("checkin.stat_treatment"))}</span><strong>${s(a.in_treatment)}</strong></div>
    <div class="pay-metric checkin-metric checkin-metric--done"><span>${t(e("checkin.stat_completed"))}</span><strong>${s(a.completed)}</strong></div>`}function Yn(){const n=d("#cinTabs");if(!n)return;const a=Ra,s=[["live",e("checkin.tab_live"),a.live],["booked",e("checkin.tab_booked"),a.scheduled],["waiting",e("checkin.tab_waiting"),a.waiting],["in_progress",e("checkin.tab_treatment"),a.in_treatment],["completed",e("checkin.tab_completed"),a.completed],["all",e("checkin.tab_all"),null]];n.innerHTML=s.filter(([i])=>r.checkinScope!=="pipeline"||!["completed","all"].includes(i)).map(([i,l,o])=>{const c=r.checkinStatus===i?"active":"",u=o==null?"":` (${h(o)})`;return`<button type="button" class="tab ${c}" aria-pressed="${!!c}" data-cintab="${i}">${t(l)}${u}</button>`}).join(""),S("[data-cintab]",n).forEach(i=>i.onclick=()=>{r.checkinStatus=i.dataset.cintab,["completed","all"].includes(r.checkinStatus)&&(r.checkinScope="day",d("#cinScope").value="day"),r.checkinPage=1,J()})}function Kn(){const n=d("#cinBeautician");if(n){const i=r.checkinBeautician||"all";n.innerHTML=`<option value="all">${t(e("checkin.all_beauticians"))}</option>`+(xa.beauticians||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const a=d("#cinBranch");if(a){const i=r.checkinBranch||"all";a.innerHTML=`<option value="all">${t(e("checkin.all_branches"))}</option>`+(xa.branches||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const s=d("#cinResultCount");s&&(s.textContent=e("checkin.result_count",{count:Fe?"—":h(Ua.total)}))}function ut(){const n=d("#cinTableMount");if(!n)return;if(n.setAttribute("aria-busy",String(me)),Fe){n.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("checkin.load_error"))}</strong><button type="button" class="btn" id="cinRetry">${t(e("checkin.refresh"))}</button></div>`,d("#cinRetry").onclick=()=>J();return}if(me){n.innerHTML=`<div class="pay-empty" role="status">${t(e("checkin.loading"))}</div>`;return}if(!ue.length){n.innerHTML=`<div class="pay-empty"><strong>${t(e("checkin.empty"))}</strong>${t(e("checkin.empty_hint"))}</div>`;return}const a=ue.map(s=>`<tr data-checkin-status="${t(s.status||"pending")}" data-arrival-state="${s.checked_in_at?"arrived":"booked"}">
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${t(s.initial||"?")}</div><div class="person-cell__text"><strong>${t(s.name||"")}</strong><small>${t(s.code||"")} · ${t(s.phone||"")}</small></div></div></td>
    <td><strong>${t(s.date_label||"")}</strong><small class="checkin-cell-meta">${t(s.time||"—")}</small></td>
    <td><strong>${t(s.branch_name||s.branch||"—")}</strong><small class="checkin-cell-meta">${t(s.beautician||"—")}</small></td>
    <td>${t(s.treatment||"—")}</td>
    <td><div class="checkin-status-stack">${x(s.payment_label)}${x(s.clearance_label)}</div></td>
    <td><div class="checkin-status-stack">${x(s.arrival_label)}<small>${t(s.waiting_label||"—")}</small></div></td>
    <td><div class="checkin-row-actions">${Ot(s,!0)}<button type="button" class="btn small soft" data-cin-view="${s.id}">${t(e("checkin.view"))}</button></div></td>
  </tr>`).join("");n.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("checkin.col_customer"))}</th><th>${t(e("checkin.col_time"))}</th>
    <th>${t(e("checkin.col_branch"))} / ${t(e("checkin.col_beautician"))}</th>
    <th>${t(e("checkin.col_treatment"))}</th><th>${t(e("checkin.col_payment"))} / ${t(e("checkin.col_clearance"))}</th>
    <th>${t(e("checkin.col_status"))}</th><th>${t(e("checkin.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${ne("cin",Ua)}`,S("[data-cin-view]",n).forEach(s=>s.onclick=()=>Zn(s.dataset.cinView)),Vt(n),se("cin",s=>(r.checkinPage=s,J()))}function Ot(n,a=!1){const s=Array.isArray(n.available_actions)?n.available_actions:[],i=a?"btn small":"btn";return s.includes("confirm_arrival")&&v.canConfirmCheckin?`<button type="button" class="${i} primary" data-cin-confirm="${n.id}">${t(e("checkin.confirm_arrival"))}</button>`:s.includes("open_clearance")?`<button type="button" class="${i} primary" data-cin-clearance="${n.id}">${t(e("checkin.go_clearance"))}</button>`:s.includes("open_crm")&&v.canViewTreatments&&v.treatmentReservationsUrl?`<a class="${i} primary" href="${t(v.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("checkin.open_crm"))}</a>`:""}function zn(n){return n.status==="completed"?e("checkin.action_help_completed"):n.status==="in_progress"?e("checkin.action_help_treatment"):e(n.checked_in_at?"checkin.action_help_waiting":"checkin.action_help_booked")}function Vt(n=document){S("[data-cin-confirm]",n).forEach(a=>a.onclick=()=>Jn(a.dataset.cinConfirm)),S("[data-cin-clearance]",n).forEach(a=>a.onclick=()=>{const s=ue.find(i=>Number(i.id)===Number(a.dataset.cinClearance));s&&(r.clearanceSearch=String(s.phone||s.code||s.name||"").trim()),A(),V("clearance")})}function Jn(n){const a=ue.find(s=>Number(s.id)===Number(n));a&&(z(e("checkin.confirm_title"),`${a.code} · ${a.name}`,`<div class="checkin-confirm"><div class="clearance-confirm__icon">${va(!0)}</div><p>${t(e("checkin.confirm_body"))}</p></div>`,`<button type="button" class="btn" data-action-drawer-close>${t(e("checkin.cancel"))}</button><button type="button" class="btn primary" id="confirmCheckinArrival">${t(e("checkin.confirm_action"))}</button>`,e("checkin.workflow_title")),d("#confirmCheckinArrival").onclick=s=>Gn(a,s.currentTarget))}async function Gn(n,a){const s=N(v.checkinConfirmUrlTemplate,n.id);if(!s||!v.canConfirmCheckin){g(e("checkin.confirm_error"));return}a.disabled=!0,a.setAttribute("aria-busy","true");try{const i=await fetch(s,{method:"POST",headers:q(!0),credentials:"same-origin",body:JSON.stringify({})}),l=await i.json().catch(()=>({}));if(!i.ok){g(l.message||e("checkin.confirm_error"));return}A(),g(l.message||e("checkin.checkin_confirmed",{code:n.code})),await J()}catch(i){console.error(i),g(e("checkin.confirm_error"))}finally{a.isConnected&&(a.disabled=!1,a.removeAttribute("aria-busy"))}}function Xn(n){try{const a=new URL(String(n||"").trim(),window.location.origin),s=new URL(v.checkinPassBaseUrl,window.location.origin);return a.origin===s.origin&&a.pathname.startsWith(s.pathname.replace(/\/$/,"")+"/")&&a.searchParams.has("expires")&&a.searchParams.has("signature")}catch{return!1}}function da(){cancelAnimationFrame(Ba),Ba=0,ke&&(ke.getTracks().forEach(a=>a.stop()),ke=null);const n=d("#cinScannerVideo");n&&(n.srcObject=null)}function Qn(){const n=`<div class="cin-scanner">
    <p class="cin-scanner__hint">${t(e("checkin.scanner_hint"))}</p>
    <div class="cin-scanner__viewport"><video id="cinScannerVideo" playsinline muted aria-label="${t(e("checkin.scanner_title"))}"></video><div class="cin-scanner__guide" aria-hidden="true"></div></div>
    <p class="cin-scanner__status" id="cinScannerStatus" aria-live="polite"></p>
    <button type="button" class="btn primary" id="cinScannerStart">${t(e("checkin.scanner_start"))}</button>
    <label class="lead-field cin-scanner__manual"><span class="lead-field__label">${t(e("checkin.scanner_manual_label"))}</span>
      <input class="lead-field__control" id="cinScannerInput" type="url" inputmode="url" autocomplete="off" placeholder="${t(e("checkin.scanner_manual_placeholder"))}">
    </label>
  </div>`,a=`<button type="button" class="btn primary" id="cinScannerOpen">${t(e("checkin.scanner_open"))}</button><button type="button" class="btn" data-action-drawer-close>${t(e("checkin.close"))}</button>`;z(e("checkin.scanner_title"),e("checkin.scanner_hint"),n,a,e("nav.checkin")),d("#cinScannerStart").onclick=Wt,d("#cinScannerOpen").onclick=()=>ja(d("#cinScannerInput").value),d("#cinScannerInput").onkeydown=s=>{s.key==="Enter"&&(s.preventDefault(),ja(s.currentTarget.value))}}function ja(n){if(!Xn(n)){const a=d("#cinScannerStatus");a&&(a.textContent=e("checkin.scanner_invalid"));return}da(),window.location.assign(String(n).trim())}async function Wt(){const n=d("#cinScannerStatus"),a=d("#cinScannerStart");if(!("BarcodeDetector"in window)||!navigator.mediaDevices?.getUserMedia){n.textContent=e("checkin.scanner_unsupported");return}a.disabled=!0,n.textContent=e("common.loading");try{if(!(BarcodeDetector.getSupportedFormats?await BarcodeDetector.getSupportedFormats():["qr_code"]).includes("qr_code"))throw new Error("QR format is unavailable");const i=new BarcodeDetector({formats:["qr_code"]});ke=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:"environment"}},audio:!1});const l=d("#cinScannerVideo");l.srcObject=ke,await l.play(),a.textContent=e("checkin.scanner_stop"),a.disabled=!1,a.onclick=()=>{da(),a.textContent=e("checkin.scanner_start"),a.onclick=Wt},n.textContent=e("checkin.scanner_hint");const o=async()=>{if(!(!ke||!l.isConnected)){try{const c=await i.detect(l);if(c[0]?.rawValue){ja(c[0].rawValue);return}}catch(c){console.error(c)}Ba=requestAnimationFrame(o)}};o()}catch(s){console.error(s),da(),a.disabled=!1,n.textContent=e("checkin.scanner_unsupported")}}function Zn(n){const a=ue.find(w=>Number(w.id)===Number(n));if(!a)return;const s=a.order_id&&v.orderShowUrlTemplate?N(v.orderShowUrlTemplate,a.order_id):"",i=a.customer_id&&v.userEditUrlTemplate?N(v.userEditUrlTemplate,a.customer_id):"",l=a.status||"pending",o=!!a.checked_in_at,c=[{label:e("checkin.step_booked"),state:o||l!=="pending"?"done":"active",time:""},{label:e("checkin.step_checked_in"),state:o?"done":"",time:o&&a.checked_in_label&&a.checked_in_label!=="—"?a.checked_in_label:""},{label:e("checkin.step_clearance"),state:o&&l==="pending"?"active":["in_progress","completed"].includes(l)?"done":"",time:""},{label:e("checkin.step_treatment"),state:l==="in_progress"?"active":l==="completed"?"done":"",time:""},{label:e("checkin.step_completed"),state:l==="completed"?"active done":"",time:""}],u=`<div class="cin-preview__block cin-timeline"><div class="journey-section__title">${t(e("checkin.timeline"))}</div>
    <div class="timeline">${c.map(w=>`<div class="timeline-step ${w.state}"><div class="timeline-dot">${va(w.state.includes("done"))}</div><span>${t(w.label)}${w.time?` <em>· ${t(w.time)}</em>`:""}</span></div>`).join("")}</div></div>`,p=`${a.phone||a.email?`<div class="cin-preview__contact">
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
    ${p}<div class="checkin-decision checkin-decision--${t(l==="pending"?o?"waiting":"booked":l)}"><strong>${t(a.arrival_label||a.status_label||"")}</strong><p>${t(zn(a))}</p></div>${u}${_}${m}
  </div>`,k=[Ot(a),i&&v.canViewUser?`<a class="btn" href="${t(i)}" target="_blank" rel="noopener">${t(e("checkin.open_customer"))}</a>`:"",s&&v.canViewOrder?`<a class="btn" href="${t(s)}" target="_blank" rel="noopener">${t(e("checkin.open_order"))}</a>`:"",!(Array.isArray(a.available_actions)&&a.available_actions.includes("open_crm"))&&v.canViewTreatments&&v.treatmentReservationsUrl?`<a class="btn" href="${t(v.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("checkin.open_crm"))}</a>`:"",`<button type="button" class="btn" data-action-drawer-close>${t(e("checkin.close"))}</button>`].filter(Boolean).join("");z(e("checkin.title"),a.code+" · "+a.name,b,k,e("nav.checkin")),Vt(d("#actionDrawer"));const $=d("#cinQrCanvas");if($&&window.QRCentral)try{QRCentral.render($,a.checkin_pass_url||"")}catch(w){console.error(w),$.closest(".cin-preview__qr-box")?.classList.add("hidden")}const f=d("#cinCopyCode");f&&(f.onclick=()=>es(a.code||"",e("checkin.copied")))}function es(n,a){const s=()=>g(a);if(!n){g(e("checkin.copy_missing"));return}navigator.clipboard&&window.isSecureContext?navigator.clipboard.writeText(n).then(s).catch(()=>mt(n,s)):mt(n,s)}function mt(n,a){const s=document.createElement("textarea");s.value=n,s.setAttribute("readonly",""),s.style.position="fixed",s.style.opacity="0",document.body.appendChild(s),s.select();try{document.execCommand("copy"),a()}catch{g(e("checkin.copy_missing"))}document.body.removeChild(s)}function as(){E.innerHTML=`<div class="clr-shell">
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
          <input class="lead-search__input" id="clrSearch" aria-label="${t(e("clearance.search_placeholder"))}" type="search" autocomplete="off" placeholder="${t(e("clearance.search_placeholder"))}" value="${t(r.clearanceSearch||"")}" />
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
  </div>`;const n=d("#clrRefresh");n&&(n.onclick=()=>te()),ie(),ts(),te()}function ts(){const n=d("#clrSearch");n&&(n.oninput=()=>{clearTimeout(Za),Za=setTimeout(()=>{r.clearanceSearch=n.value.trim(),r.clearancePage=1,te()},320)});const a=d("#clrBeautician");a&&(a.onchange=()=>{r.clearanceBeautician=a.value,r.clearancePage=1,te()});const s=d("#clrBranch");s&&(s.onchange=()=>{r.clearanceBranch=s.value,r.clearancePage=1,te()})}async function te(){const n=++Xe;Ie=!1;const a=v.clearanceUrl||"",s=d("#clrTableMount");if(!a){s&&(s.innerHTML=`<div class="pay-empty"><strong>${t(e("clearance.load_error"))}</strong></div>`);return}ve=!0,_t(),vt();const i=new URLSearchParams;r.clearanceSearch&&i.set("q",r.clearanceSearch),r.clearanceState&&i.set("state",r.clearanceState);const l=r.clearanceBranch!=="all"?r.clearanceBranch:r.branch||"all";l&&l!=="all"&&i.set("branch",l),r.clearanceBeautician&&r.clearanceBeautician!=="all"&&i.set("beautician",r.clearanceBeautician),i.set("page",String(r.clearancePage||1)),i.set("per_page","25");try{const o=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!o.ok)throw new Error("clearance "+o.status);const c=await o.json();if(n!==Xe)return;_e=Array.isArray(c.data)?c.data:[],Fa=Object.assign({waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0},c.meta&&c.meta.summary||{}),Pa=Object.assign({states:[],beauticians:[],branches:[]},c.filters||{}),Ia={current_page:c.meta&&c.meta.current_page||1,last_page:c.meta&&c.meta.last_page||1,total:c.meta&&c.meta.total||0}}catch(o){if(n!==Xe)return;Ie=!0,console.error(o),_e=[],g(e("clearance.load_error"))}finally{if(n!==Xe)return;ve=!1,_t(),ns(),ss(),vt()}}function _t(){const n=d("#clrMetrics");if(!n)return;const a=Fa;n.setAttribute("aria-busy",String(ve));const s=i=>ve||Ie?"—":h(i);n.innerHTML=`
    <div class="pay-metric clearance-metric clearance-metric--waiting"><span>${t(e("clearance.stat_waiting"))}</span><strong>${s(a.waiting)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--blocked"><span>${t(e("clearance.stat_blocked"))}</span><strong>${s(a.blocked)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--treatment"><span>${t(e("clearance.stat_treatment"))}</span><strong>${s(a.in_treatment)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--done"><span>${t(e("clearance.stat_done"))}</span><strong>${s(a.done_today)}</strong></div>`}function ns(){const n=d("#clrTabs");if(!n)return;const a=Fa,s=[["waiting",e("clearance.tab_waiting"),a.waiting],["blocked",e("clearance.tab_blocked"),a.blocked],["in_treatment",e("clearance.tab_treatment"),a.in_treatment],["all_queue",e("clearance.tab_queue"),a.queue],["done",e("clearance.tab_done"),a.done_today]];n.innerHTML=s.map(([i,l,o])=>{const c=r.clearanceState===i?"active":"";return`<button type="button" class="tab ${c}" aria-pressed="${!!c}" data-clrtab="${i}">${t(l)} (${h(o||0)})</button>`}).join(""),S("[data-clrtab]",n).forEach(i=>i.onclick=()=>{r.clearanceState=i.dataset.clrtab,r.clearancePage=1,te()})}function ss(){const n=d("#clrBeautician");if(n){const i=r.clearanceBeautician||"all";n.innerHTML=`<option value="all">${t(e("clearance.all_beauticians"))}</option>`+(Pa.beauticians||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const a=d("#clrBranch");if(a){const i=r.clearanceBranch||"all";a.innerHTML=`<option value="all">${t(e("clearance.all_branches"))}</option>`+(Pa.branches||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const s=d("#clrResultCount");s&&(s.textContent=e("clearance.result_count",{count:Ie?"—":h(Ia.total)}))}function vt(){const n=d("#clrTableMount");if(!n)return;if(n.setAttribute("aria-busy",String(ve)),Ie){n.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("clearance.load_error"))}</strong><button type="button" class="btn" id="clrRetry">${t(e("clearance.refresh"))}</button></div>`,d("#clrRetry").onclick=()=>te();return}if(ve){n.innerHTML=`<div class="pay-empty" role="status">${t(e("clearance.loading"))}</div>`;return}if(!_e.length){n.innerHTML=`<div class="pay-empty"><strong>${t(e("clearance.empty"))}</strong>${t(e("clearance.empty_hint"))}</div>`;return}const a=_e.map(s=>`<tr data-clearance-state="${t(s.clearance||"other")}">
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${t(s.initial||"?")}</div><div class="person-cell__text"><strong>${t(s.name||"")}</strong><small>${t(s.code||"")} · ${t(s.phone||"")}</small></div></div></td>
    <td><strong>${t(s.date_label||"")}</strong><small class="clearance-wait">${t(s.checked_in_at?e("clearance.arrival_waiting",{time:s.waiting_label||"—"}):s.time||"—")}</small></td>
    <td>${t(s.branch_name||s.branch||"—")}</td>
    <td>${t(s.treatment||"—")}</td>
    <td>${x(s.payment_label)}</td>
    <td>${x(s.clearance_label)}</td>
    <td><div class="clearance-row-actions">${Yt(s,!0)}<button type="button" class="btn small soft" data-clr-view="${s.id}">${t(e("clearance.view"))}</button></div></td>
  </tr>`).join("");n.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("clearance.col_customer"))}</th><th>${t(e("clearance.col_time"))}</th>
    <th>${t(e("clearance.col_branch"))}</th><th>${t(e("clearance.col_treatment"))}</th>
    <th>${t(e("clearance.col_payment"))}</th><th>${t(e("clearance.col_clearance"))}</th>
    <th>${t(e("clearance.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${ne("clr",Ia)}`,S("[data-clr-view]",n).forEach(s=>s.onclick=()=>os(s.dataset.clrView)),Kt(n),se("clr",s=>(r.clearancePage=s,te()))}function Yt(n,a=!1){const s=Array.isArray(n.available_actions)?n.available_actions:[],i=a?"btn small":"btn";if(s.includes("start_treatment")&&v.canEditTreatments)return`<button type="button" class="${i} primary" data-clr-status="in_progress" data-clr-id="${n.id}">${t(e("clearance.start_treatment"))}</button>`;if(s.includes("complete_treatment")&&v.canEditTreatments)return`<button type="button" class="${i} success" data-clr-status="completed" data-clr-id="${n.id}">${t(e("clearance.complete_treatment"))}</button>`;if(s.includes("resolve_payment")){if(n.order_id)return`<button type="button" class="${i} danger" data-clr-payment="${n.id}">${t(e("clearance.resolve_payment"))}</button>`;if(v.canViewTreatments&&v.treatmentReservationsUrl)return`<a class="${i} danger" href="${t(v.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("clearance.resolve_payment"))}</a>`}return""}function is(n){return e(`clearance.action_help_${{waiting:"waiting",blocked:"blocked",in_treatment:"treatment",done:"done"}[n.clearance]||"done"}`)}function va(n){return n?'<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 10 3 3 7-7"/></svg>':'<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10" cy="10" r="6"/></svg>'}function Kt(n=document){S("[data-clr-status]",n).forEach(a=>a.onclick=()=>ls(a.dataset.clrId,a.dataset.clrStatus)),S("[data-clr-payment]",n).forEach(a=>a.onclick=()=>{const s=_e.find(i=>Number(i.id)===Number(a.dataset.clrPayment));s&&(r.paymentSearch=String(s.phone||s.name||s.code||"").trim()),A(),V("payments")})}function ls(n,a){const s=_e.find(l=>Number(l.id)===Number(n));if(!s)return;const i=a==="in_progress";z(e(i?"clearance.start_confirm_title":"clearance.complete_confirm_title"),`${s.code} · ${s.name}`,`<div class="clearance-confirm"><div class="clearance-confirm__icon">${va(!0)}</div><p>${t(e(i?"clearance.start_confirm_body":"clearance.complete_confirm_body"))}</p></div>`,`<button type="button" class="btn" data-action-drawer-close>${t(e("clearance.cancel"))}</button><button type="button" class="btn ${i?"primary":"success"}" id="confirmClearanceStatus">${t(e("clearance.confirm_action"))}</button>`,e("clearance.workflow_title")),d("#confirmClearanceStatus").onclick=l=>rs(s,a,l.currentTarget)}async function rs(n,a,s){const i=N(v.clearanceStatusUrlTemplate,n.id);if(!i||!v.canEditTreatments){g(e("clearance.update_error"));return}s.disabled=!0,s.setAttribute("aria-busy","true");try{const l=await fetch(i,{method:"PATCH",headers:q(!0),credentials:"same-origin",body:JSON.stringify({status:a})}),o=await l.json().catch(()=>({}));if(!l.ok){g(o.message||e("clearance.update_error"));return}A(),g(o.message||e("clearance.status_updated")),await te()}catch(l){console.error(l),g(e("clearance.update_error"))}finally{s.isConnected&&(s.disabled=!1,s.removeAttribute("aria-busy"))}}function os(n){const a=_e.find(c=>Number(c.id)===Number(n));if(!a)return;const s=a.order_id&&v.orderShowUrlTemplate?N(v.orderShowUrlTemplate,a.order_id):"",i=a.customer_id&&v.userEditUrlTemplate?N(v.userEditUrlTemplate,a.customer_id):"",l=`<div class="pay-review">
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
    <div class="clearance-decision clearance-decision--${t(a.clearance||"other")}"><strong>${t(a.clearance_label||"")}</strong><p>${t(is(a))}</p></div>
    <div class="journey-section"><div class="journey-section__title">${t(e("clearance.workflow_title"))}</div><ol class="clearance-checklist">
      ${[[e("clearance.step_arrival"),!!a.checked_in_at||["in_treatment","done"].includes(a.clearance)],[e("clearance.step_payment"),!!a.payment_ok||["in_treatment","done"].includes(a.clearance)],[e("clearance.step_treatment"),["in_treatment","done"].includes(a.clearance)],[e("clearance.step_complete"),a.clearance==="done"]].map(([c,u])=>`<li class="${u?"is-done":""}"><span>${va(u)}</span><strong>${t(c)}</strong></li>`).join("")}
    </ol></div></div>`,o=[Yt(a),a.checkin_pass_url?`<a class="btn" href="${t(a.checkin_pass_url)}" target="_blank" rel="noopener">${t(e("clearance.view_checkin_pass"))}</a>`:"",i&&v.canViewUser?`<a class="btn" href="${t(i)}" target="_blank" rel="noopener">${t(e("clearance.open_customer"))}</a>`:"",s&&v.canViewOrder?`<a class="btn" href="${t(s)}" target="_blank" rel="noopener">${t(e("clearance.open_order"))}</a>`:"",v.canViewTreatments&&v.treatmentReservationsUrl?`<a class="btn" href="${t(v.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("clearance.open_crm"))}</a>`:"",`<button type="button" class="btn" data-action-drawer-close>${t(e("clearance.close"))}</button>`].filter(Boolean).join("");z(e("clearance.title"),a.code+" · "+a.name,l,o,e("nav.clearance")),setTimeout(()=>Kt(d("#actionDrawerFoot")),0)}function cs(){E.innerHTML=`<div class="wal-shell">
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
          <input class="lead-search__input" id="walSearch" aria-label="${t(e("wallet.search_placeholder"))}" type="search" autocomplete="off" placeholder="${t(e("wallet.search_placeholder"))}" value="${t(r.walletSearch||"")}" />
        </label>
        <div class="lead-filter-grid loyalty-filter-grid">
          <label class="lead-field"><span class="lead-field__label">${t(e("wallet.filter_tier"))}</span>
            <select class="lead-field__control" id="walTier"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="walTableMount"></div>
    </section>
  </div>`;const n=d("#walRefresh");n&&(n.onclick=()=>he()),ds(),he()}function ds(){const n=d("#walSearch");n&&(n.oninput=()=>{clearTimeout(Xa),Xa=setTimeout(()=>{r.walletSearch=n.value.trim(),r.walletPage=1,he()},320)});const a=d("#walTier");a&&(a.onchange=()=>{r.walletTier=a.value,r.walletPage=1,he()})}async function he(){const n=++Je;Ue=!1;const a=v.walletUrl||"",s=d("#walTableMount");if(!a){s&&(s.innerHTML=`<div class="pay-empty"><strong>${t(e("wallet.load_error"))}</strong></div>`);return}pe=!0,ht(),bt();const i=new URLSearchParams;r.walletSearch&&i.set("q",r.walletSearch),r.walletSegment&&r.walletSegment!=="all"&&i.set("segment",r.walletSegment),r.walletTier&&r.walletTier!=="all"&&i.set("tier",r.walletTier),i.set("page",String(r.walletPage||1)),i.set("per_page","25");try{const l=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("wallet "+l.status);const o=await l.json();if(n!==Je)return;Re=Array.isArray(o.data)?o.data:[],Da=Object.assign({members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0},o.meta&&o.meta.summary||{}),St=Object.assign({segments:[],tiers:[]},o.filters||{}),Ha={current_page:o.meta&&o.meta.current_page||1,last_page:o.meta&&o.meta.last_page||1,total:o.meta&&o.meta.total||0}}catch(l){if(n!==Je)return;Ue=!0,console.error(l),Re=[],g(e("wallet.load_error"))}finally{if(n!==Je)return;pe=!1,ht(),ps(),us(),bt()}}function ht(){const n=d("#walMetrics");if(!n)return;const a=Da;n.setAttribute("aria-busy",String(pe));const s=i=>pe||Ue?"—":h(i);n.innerHTML=`
    <div class="pay-metric loyalty-metric loyalty-metric--members"><span>${t(e("wallet.stat_members"))}</span><strong>${s(a.members)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--active"><span>${t(e("wallet.stat_balance"))}</span><strong>${s(a.with_balance)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--points"><span>${t(e("wallet.stat_points"))}</span><strong>${s(a.points_outstanding)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--rewards"><span>${t(e("wallet.stat_stamp"))}</span><strong>${s(a.stamp_ready)}</strong></div>`}function ps(){const n=d("#walTabs");if(!n)return;const a=Da,s=[["all",e("wallet.tab_all"),a.members],["active",e("wallet.tab_active"),a.with_balance],["zero",e("wallet.tab_zero"),a.zero_balance],["stamp_ready",e("wallet.tab_stamp"),a.stamp_ready]];n.innerHTML=s.map(([i,l,o])=>{const c=r.walletSegment===i?"active":"";return`<button type="button" class="tab ${c}" aria-pressed="${!!c}" data-waltab="${i}">${t(l)} (${h(o||0)})</button>`}).join(""),S("[data-waltab]",n).forEach(i=>i.onclick=()=>{r.walletSegment=i.dataset.waltab,r.walletPage=1,he()})}function us(){const n=d("#walTier");if(n){const s=r.walletTier||"all";n.innerHTML=`<option value="all">${t(e("wallet.all_tiers"))}</option>`+(St.tiers||[]).map(i=>`<option value="${i.id}"${String(s)===String(i.id)?" selected":""}>${t(i.name)}</option>`).join("")}const a=d("#walResultCount");a&&(a.textContent=e("wallet.result_count",{count:Ue?"—":h(Ha.total)}))}function bt(){const n=d("#walTableMount");if(!n)return;if(n.setAttribute("aria-busy",String(pe)),Ue){n.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("wallet.load_error"))}</strong><button type="button" class="btn" id="walRetry">${t(e("wallet.refresh"))}</button></div>`,d("#walRetry").onclick=()=>he();return}if(pe){n.innerHTML=`<div class="pay-empty" role="status">${t(e("wallet.loading"))}</div>`;return}if(!Re.length){n.innerHTML=`<div class="pay-empty"><strong>${t(e("wallet.empty"))}</strong>${t(e("wallet.empty_hint"))}</div>`;return}const a=Re.map(s=>{const i=s.avatar_url?`<div class="mini-avatar mini-avatar--photo"><img src="${t(s.avatar_url)}" alt=""></div>`:`<div class="mini-avatar">${t(s.initial||"?")}</div>`,l=s.stamp_ready?x(e("wallet.chip_stamp",{count:s.stamp_ready})):x(e("wallet.stamp_summary",{active:h(s.stamp_active||0),ready:0})),o=v.canShowLoyaltyMember&&v.loyaltyMemberShowUrlTemplate?N(v.loyaltyMemberShowUrlTemplate,s.id):"";return`<tr data-membership-segment="${t(s.segment||"zero")}">
      <td><div class="person-cell person-cell--lead">${i}<div class="person-cell__text"><strong>${t(s.name||"")}</strong><small>${t(s.code||"")} · ${t(s.phone||s.email||"—")}</small></div></div></td>
      <td><div class="loyalty-tier-cell">${s.tier?x(s.tier):"—"}<small>${t(e("wallet.tier_since"))}: ${t(s.tier_since_label||"—")}</small></div></td>
      <td class="is-num"><strong>${h(s.balance)}</strong></td>
      <td class="is-num">${R(s.lifetime_spend)}</td>
      <td><div class="loyalty-reward-cell">${l}<small>${t(e("wallet.stamp_summary",{active:h(s.stamp_active||0),ready:h(s.stamp_ready||0)}))}</small></div></td>
      <td><div class="loyalty-activity-cell"><strong>${t(e("wallet.activity_summary",{count:h(s.activity_count||0)}))}</strong><small>${t(s.last_activity_label||"—")}</small></div></td>
      <td class="lead-table__actions"><div class="lead-menu loyalty-action-menu">
        <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${t(e("wallet.row_actions",{name:s.name||e("wallet.guest")}))}"><span class="lead-menu__dots" aria-hidden="true"></span></button>
        <div class="lead-menu__panel" role="menu" hidden>
          ${o?`<a class="lead-menu__item" role="menuitem" href="${t(o)}" target="_blank" rel="noopener">${t(e("wallet.open_member"))}</a>`:""}
          <button type="button" class="lead-menu__item" role="menuitem" data-wal-view="${s.id}">${t(e("wallet.view"))}</button>
        </div>
      </div></td>
    </tr>`}).join("");n.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("wallet.col_customer"))}</th><th>${t(e("wallet.col_tier"))}</th><th class="is-num">${t(e("wallet.col_balance"))}</th>
    <th class="is-num">${t(e("wallet.col_spend"))}</th><th>${t(e("wallet.col_stamps"))}</th>
    <th>${t(e("wallet.col_activity"))}</th><th>${t(e("wallet.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${ne("wal",Ha)}`,Ye(n),S('.loyalty-action-menu a[role="menuitem"]',n).forEach(s=>s.onclick=()=>H()),S("[data-wal-view]",n).forEach(s=>s.onclick=()=>{H(),ms(s.dataset.walView)}),se("wal",s=>(r.walletPage=s,he()))}let Ae=null,Va="";function ms(n){const a=Re.find(p=>Number(p.id)===Number(n));if(!a)return;A(),Z(),Ae=document.activeElement,Va=document.body.style.overflow,document.body.style.overflow="hidden";const s=v.loyaltyMemberShowUrlTemplate?N(v.loyaltyMemberShowUrlTemplate,a.id):"",i=a.customer_id&&v.userEditUrlTemplate?N(v.userEditUrlTemplate,a.customer_id):"",l=(a.recent||[]).length?`<ol class="wallet-transactions">${a.recent.map(p=>`<li class="wallet-transaction">
        <div class="wallet-transaction__description"><strong>${t(p.description||p.type||"")}</strong><time>${t(p.created_label||"")}</time></div>
        <div class="wallet-transaction__amount"><strong class="${Number(p.points)>0?"is-credit":""}">${Number(p.points)>0?"+":""}${h(p.points)} <span>${t(e("wallet.col_balance"))}</span></strong><small>${t(e("wallet.transaction_balance",{balance:h(p.balance_after)}))}</small></div>
      </li>`).join("")}</ol>`:`<div class="pay-empty">${t(e("wallet.no_recent"))}</div>`,o=a.avatar_url?`<img class="wallet-detail__avatar" src="${t(a.avatar_url)}" alt="" />`:`<div class="wallet-detail__avatar" aria-hidden="true">${t(a.initial||"?")}</div>`,c=d("#leadDrawer");c.classList.add("wallet-drawer"),c.setAttribute("role","dialog"),c.setAttribute("aria-modal","true"),c.setAttribute("aria-labelledby","drawerName"),d("#leadDrawer .eyebrow").textContent=e("nav.wallet"),d("#drawerName").textContent=e("wallet.title"),d("#drawerClose").setAttribute("aria-label",e("wallet.close")),d("#drawerBody").innerHTML=`<div class="wallet-detail">
    <section class="wallet-detail__customer">${o}<div class="wallet-detail__identity">
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
  </div>`;const u=document.createElement("div");u.id="walletDrawerFoot",u.className="wallet-drawer__footer",u.innerHTML=[s&&v.canShowLoyaltyMember?`<a class="btn primary" href="${t(s)}" target="_blank" rel="noopener">${t(e("wallet.open_member"))}</a>`:"",i&&v.canViewUser?`<a class="btn" href="${t(i)}" target="_blank" rel="noopener">${t(e("wallet.open_profile"))}</a>`:"",`<button type="button" class="btn" data-wallet-close>${t(e("wallet.close"))}</button>`].filter(Boolean).join(""),c.appendChild(u),d("[data-wallet-close]",u).onclick=Z,c.classList.add("show"),c.setAttribute("aria-hidden","false"),c.inert=!1,d(".app-shell").inert=!0,d("#drawerBackdrop").classList.add("show"),d("#drawerBody").scrollTop=0,d("#drawerClose").focus({preventScroll:!0})}document.addEventListener("keydown",n=>{const a=d("#actionDrawer.show")||d("#leadDrawer.show");if(!a)return;const s=a.id==="actionDrawer"?A:Z;if(n.key==="Escape"){n.preventDefault(),s();return}if(n.key!=="Tab")return;const i=S('button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex="0"]',a).filter(c=>c.getClientRects().length),l=i[0],o=i[i.length-1];if(!l){n.preventDefault();return}n.shiftKey&&(document.activeElement===l||!a.contains(document.activeElement))?(n.preventDefault(),o.focus()):!n.shiftKey&&(document.activeElement===o||!a.contains(document.activeElement))&&(n.preventDefault(),l.focus())});const Te=Object.fromEntries(["beauticians","branches","audit"].map(n=>[n,{q:"",sort:"revenue",page:1}]));let Ze=0,ft=null,oe=null;function _s(){Wa("beauticians")}function vs(){Wa("branches")}function hs(){Wa("audit")}function Wa(n){const a=Te[n];E.innerHTML=`<div class="report-shell ${n==="beauticians"?"beautician-report-shell":n==="branches"?"branch-report-shell":n==="audit"?"audit-report-shell":""}">
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
  </div>`,d("#reportRefresh").onclick=()=>Ne(),d("#reportSearch").oninput=i=>{a.q=i.target.value,a.page=1,clearTimeout(ft),n==="audit"?ft=setTimeout(()=>{r.view===n&&Ne()},350):oe&&pa(n)};const s=d("#reportSort");s&&(s.onchange=i=>{a.sort=i.target.value,oe&&pa(n)}),Ne()}async function Ne(){const n=r.view;if(!Te[n])return;const a=++Ze,s=Te[n],i=d("#reportTable");if(!i)return;oe=null,d("#reportMetrics").innerHTML="",i.setAttribute("aria-busy","true"),i.innerHTML=`<div class="pay-empty" role="status">${t(e("reporting.loading"))}</div>`;const l=new URLSearchParams({view:n,page:String(s.page)});r.period&&l.set("period",r.period),r.branch&&r.branch!=="all"&&l.set("branch",r.branch),n==="audit"&&s.q.trim()&&l.set("q",s.q.trim());try{if(!v.reportingUrl)throw new Error("Missing reporting endpoint");const o=await fetch(v.reportingUrl+"?"+l.toString(),{headers:q(!1),credentials:"same-origin"});if(!o.ok)throw new Error("Reporting "+o.status);const c=await o.json();if(a!==Ze||r.view!==n)return;oe=c;const u=c.meta.summary,p=n==="audit"?["batches","imported","duplicates","invalid"]:["leads","converted","orders","revenue"];d("#reportMetrics").innerHTML=p.map((m,_)=>`<div class="pay-metric ${n==="beauticians"?"beautician-metric beautician-metric--"+_:n==="branches"?"branch-metric branch-metric--"+_:n==="audit"?"audit-metric audit-metric--"+_:""}"><span>${t(e("reporting."+m))}</span><strong>${m==="revenue"?R(u[m]):h(u[m])}</strong></div>`).join(""),d("#reportPeriod").textContent=c.meta.period,pa(n)}catch{if(a!==Ze||r.view!==n)return;d("#reportCount").textContent="—",i.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("reporting.error"))}</strong><button type="button" class="btn" id="reportRetry">${t(e("reporting.refresh"))}</button></div>`,d("#reportRetry").onclick=()=>Ne()}finally{a===Ze&&r.view===n&&i.setAttribute("aria-busy","false")}}function pa(n){const a=d("#reportTable");if(!a||!oe)return;const s=Te[n];let i=[...oe.data||[]];n!=="audit"&&(i=i.filter(m=>String(m.name).toLocaleLowerCase().includes(s.q.trim().toLocaleLowerCase())),i.sort((m,_)=>Number(_[s.sort])-Number(m[s.sort])||String(m.name).localeCompare(String(_.name))));const l=n==="audit"?oe.meta.total:i.length,o=n==="audit"?oe.meta:{current_page:Math.max(1,Math.min(s.page,Math.ceil(l/25)||1)),last_page:Math.ceil(l/25)||1};n!=="audit"&&(s.page=o.current_page,i=i.slice((s.page-1)*25,s.page*25)),d("#reportCount").textContent=e("reporting.results",{count:h(l)});const c=n==="audit"?["code","date","actor","branch","method","status","raw","imported","duplicates","invalid"]:["name","leads","converted","conversion","follow_up","lost","orders","revenue","average_order"],u=["code","date","actor","branch","method","status","name"],p=n==="beauticians"||n==="branches"?`<th>${t(e("reporting.actions"))}</th>`:"";a.innerHTML=i.length?`<div class="table-wrap report-table-wrap"><table class="data-table report-table"><thead><tr>${c.map(m=>`<th${u.includes(m)?"":' class="is-num"'}>${t(e("reporting."+m))}</th>`).join("")}${p}</tr></thead><tbody>${i.map(m=>`<tr>${c.map(_=>{let b=u.includes(_)?t(_==="status"?e("reporting.status_"+m[_]):m[_]):_==="conversion"?Number(m.leads)>0?Number(m[_]).toFixed(1)+"%":"—":["revenue","average_order"].includes(_)?R(m[_]):h(m[_]);return`<td${u.includes(_)?"":' class="is-num"'}>${["name","code"].includes(_)?`<strong>${b}</strong>`:b}</td>`}).join("")}${n==="beauticians"||n==="branches"?`<td class="lead-table__actions"><div class="lead-menu ${n==="beauticians"?"beautician":"branch"}-action-menu"><button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${t(e("reporting.row_actions",{name:m.name||""}))}"><span class="lead-menu__dots" aria-hidden="true"></span></button><div class="lead-menu__panel" role="menu" hidden><button type="button" class="lead-menu__item" role="menuitem" data-report-leads="${m.id}" data-report-scope="${n}">${t(e("reporting.view_leads"))}</button></div></div></td>`:""}</tr>`).join("")}</tbody></table></div>`:`<div class="pay-empty"><strong>${t(e("reporting.empty"))}</strong>${t(e("reporting.empty_hint"))}</div>`,(n==="beauticians"||n==="branches")&&(Ye(a),S("[data-report-leads]",a).forEach(m=>m.onclick=()=>{H(),m.dataset.reportScope==="branches"?r.leadBranch=m.dataset.reportLeads:r.leadBeautician=m.dataset.reportLeads,A(),V("leads")}),S('.beautician-action-menu a[role="menuitem"],.branch-action-menu a[role="menuitem"]',a).forEach(m=>m.onclick=()=>H())),a.insertAdjacentHTML("beforeend",ne("report",o)),se("report",m=>{if(s.page=m,n==="audit")return Ne();pa(n)})}function bs(n,a){E.innerHTML=`${be(t(n),t(a))}<section class="card"><div class="empty">${t(a)}</div></section>`}function fs(){E.innerHTML=`${be(e("followup.title"),e("followup.subtitle"),`<button type="button" class="btn" data-jump="leads">${t(e("followup.open_workspace"))}</button>`)}
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
        <input class="lead-search__input" id="followSearch" type="search" autocomplete="off" placeholder="${t(e("followup.search_placeholder"))}" value="${t(r.followSearch)}" />
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
  </section>`,ie(),zt(),ce()}async function ce(){const n=v.followUpsUrl||"";if(!n){g(e("followup.load_error"));return}const a=new URLSearchParams;r.followSearch&&a.set("q",r.followSearch),r.followBucket&&r.followBucket!=="all"&&a.set("bucket",r.followBucket);const s=r.leadBranch!=="all"?r.leadBranch:r.branch||"all";s&&s!=="all"&&a.set("branch",s),r.leadBeautician&&r.leadBeautician!=="all"&&a.set("beautician",r.leadBeautician),a.set("page",String(r.followPage||1)),na=!0,r.view==="followup"&&gt();try{const i=await fetch(n+"?"+a.toString(),{headers:q(!1),credentials:"same-origin"});if(!i.ok)throw new Error("followups "+i.status);const l=await i.json();Mt=l.meta||{},Be=Array.isArray(l.data)?l.data:[],Ca=l.meta&&l.meta.summary||Ca,Pe=l.filters||Pe,l.filters?.statuses&&(P.statuses=l.filters.statuses)}catch(i){console.error(i),g(e("followup.load_error"))}finally{na=!1,r.view==="followup"&&(gs(),zt(),gt())}}function zt(){const n=Pe.buckets||[{value:"all",label:e("followup.bucket_all")},{value:"overdue",label:e("followup.bucket_overdue")},{value:"due_today",label:e("followup.bucket_due_today")},{value:"no_response",label:e("followup.bucket_no_response")},{value:"lost",label:e("followup.bucket_lost")}],a=d("#followBuckets");a&&(a.innerHTML=n.map(o=>`<button type="button" class="tab ${String(r.followBucket)===String(o.value)?"active":""}" role="tab" data-follow-bucket="${t(o.value)}">${t(o.label)}</button>`).join(""),S("[data-follow-bucket]",a).forEach(o=>{o.onclick=()=>{r.followBucket=o.dataset.followBucket||"all",r.followPage=1,ce()}}));const s=d("#followBeautician");if(s){const o=[{id:"all",name:e("workspace.all_beauticians")},...Pe.beauticians||P.beauticians||[]];s.innerHTML=o.map(c=>`<option value="${t(c.id)}" ${String(r.leadBeautician)===String(c.id)?"selected":""}>${t(c.name)}</option>`).join(""),s.onchange=c=>{r.leadBeautician=c.target.value,r.followPage=1,ce()}}const i=d("#followBranch");if(i){const o=[{id:"all",name:e("workspace.all_branches")},...Pe.branches||v.branches||[]];i.innerHTML=o.map(c=>`<option value="${t(c.id)}" ${String(r.leadBranch)===String(c.id)?"selected":""}>${t(c.name)}</option>`).join(""),i.onchange=c=>{r.leadBranch=c.target.value,r.followPage=1,ce()}}const l=d("#followSearch");l&&(l.oninput=o=>{r.followSearch=o.target.value,clearTimeout(za),za=setTimeout(()=>{r.followPage=1,ce()},350)})}function gs(){const n=d("#followKpiMount");if(!n)return;const a=Ca||{};n.innerHTML=`
    ${F(U("queue"),e("followup.kpi_queue"),h(a.queue),e("followup.kpi_unit"),e("followup.kpi_queue_meta"),e("followup.kpi_queue_detail"),"blue")}
    ${F(U("overdue"),e("followup.kpi_overdue"),h(a.overdue),e("followup.kpi_unit"),e("followup.kpi_overdue_meta"),e("followup.kpi_overdue_detail"),"rose")}
    ${F(U("today"),e("followup.kpi_due_today"),h(a.due_today),e("followup.kpi_unit"),e("followup.kpi_due_meta"),e("followup.kpi_due_detail"),"green")}
    ${F(U("no_response"),e("followup.kpi_no_response"),h(a.no_response),e("followup.kpi_unit"),e("followup.kpi_no_response_meta"),e("followup.kpi_no_response_detail"),"purple")}
    ${F(U("lost"),e("followup.kpi_lost"),h(a.lost),e("followup.kpi_unit"),e("followup.kpi_lost_meta"),e("followup.kpi_lost_detail"),"teal")}
  `}function gt(){const n=d("#followTableMount");if(!n)return;const a=Array.isArray(Be)?Be.length:0,s=d("#followResultCount");if(s&&(s.textContent=a===1?e("followup.results_count_one"):e("followup.results_count",{count:h(a)})),na){n.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${t(e("followup.loading"))}</strong></div>`;return}const i=Be;if(!i.length){n.innerHTML=`<div class="lead-empty">
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
  </tr></thead><tbody>${i.map(o=>{const c=String(o.name||""),u=t((c[0]||"?").toUpperCase()),p=Number(o.days_since_followup||0),m=o.last_followed_up_at?e("followup.days",{count:p}):e("followup.never"),_=String(o.followup_bucket)==="overdue";return`<tr>
      <td><span class="lead-code">${t(o.code||o.id)}</span></td>
      <td><div class="person-cell person-cell--lead"><div class="mini-avatar" aria-hidden="true">${u}</div><div class="person-cell__text"><strong>${t(c||"—")}</strong><small>${t(o.customer||"")}</small></div></div></td>
      <td><span class="lead-mono">${t(o.phone||"—")}</span></td>
      <td>${t(o.beautician||"—")}</td>
      <td><span class="lead-branch">${t(o.branch||"—")}</span></td>
      <td>${x(o.status)}</td>
      <td><span class="lead-date">${t(o.last||"—")}</span></td>
      <td><span class="lead-wait${_?" is-overdue":""}">${t(m)}</span></td>
      <td class="lead-table__actions">
        <div class="lead-menu">
          <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${t(e("workspace.row_actions"))}">
            <span class="lead-menu__dots" aria-hidden="true"></span>
          </button>
          <div class="lead-menu__panel" role="menu" hidden>
            <button type="button" class="lead-menu__item" role="menuitem" data-follow-view="${t(o.id)}">${t(e("followup.view_lead"))}</button>
            ${l?`<button type="button" class="lead-menu__item" role="menuitem" data-follow-mark="${t(o.id)}">${t(e("followup.mark"))}</button>`:""}
          </div>
        </div>
      </td>
    </tr>`}).join("")}</tbody></table></div>`,n.insertAdjacentHTML("beforeend",ne("follow",Mt)),se("follow",o=>(r.followPage=o,ce())),Ye(n),S("[data-follow-view]",n).forEach(o=>o.onclick=()=>{H(),_a(o.dataset.followView)}),S("[data-follow-mark]",n).forEach(o=>o.onclick=()=>{H(),Jt(o.dataset.followMark)})}async function Jt(n){const a=N(v.leadFollowUpUrlTemplate,n);if(!a){g(e("followup.mark_error"));return}try{const s=await fetch(a,{method:"POST",headers:q(!0),credentials:"same-origin",body:JSON.stringify({})}),i=await s.json().catch(()=>({}));if(!s.ok){g(i.message||e("followup.mark_error"));return}g(i.message||e("followup.marked")),r.view==="followup"?await ce():await j()}catch(s){console.error(s),g(e("followup.mark_error"))}}function Gt(){const n=L||{},a=n.kpis||{},s=a.vs_prev||{},i=n.targets||{},l=n.dual||{},o=Number(l.sales_pct||n.target_board&&n.target_board.sales_pct||0),c=Number(i.sales||0),u=Number(a.sales||0),p=u-c,m=String(n.period&&n.period.label||e("common.this_month")),_=t(n.period&&n.period.label||e("common.this_month")),b=Array.isArray(n.sales_insights)?n.sales_insights:[],k=Array.isArray(n.waterfall)?n.waterfall:[],$=Math.max(1,...k.map(f=>Number(f.value||0)));E.innerHTML=`${be(e("sales.title"),e("sales.subtitle"),`<button type="button" class="btn soft" id="salesRefreshBtn">${t(e("sales.refresh"))}</button>
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
      ${F(U("revenue"),e("sales.kpi_sales"),C(a.sales),e("sales.kpi_unit_revenue"),ee(s.sales,"%"),le(e("sales.kpi_sales"),C(c)),"rose")}
      ${F(U("orders"),e("sales.kpi_orders"),h(a.orders||0),e("sales.kpi_unit_orders"),ee(s.orders||0,"%"),e("sales.kpi_orders_detail"),"blue")}
      ${F(U("customers"),e("sales.kpi_customers"),h(a.buyers),e("sales.kpi_unit_customers"),ee(s.buyers,"%"),le(e("sales.kpi_customers"),h(i.buyers||0)),"green")}
      ${F(U("average"),e("sales.kpi_avg"),C(a.avg_sale),e("sales.kpi_unit_revenue"),ee(s.avg_sale,"%"),le(e("sales.kpi_avg"),C(i.avg_sale||0)),"purple")}
      ${F(U("target"),e("sales.kpi_target"),G(o),e("sales.of_target"),p>=0?"+ "+C(Math.abs(p)):"− "+C(Math.abs(p)),e("sales.kpi_target_detail",{target:C(c),actual:C(u)}),"teal")}
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
        <span class="sales-card__pill">${C(u)}</span>
      </div>
      <div class="chart-wrap sales-card__chart"><canvas id="salesChart"></canvas></div>
    </section>
    <section class="card target-card ${o>=100?"target-card--over":""}">
      <div class="target-card__head">
        <div>
          <div class="target-card__eyebrow">${t(e("sales.target_card"))}</div>
          <div class="target-card__title">${t(e("overview.target_achievement"))}</div>
        </div>
        <span class="target-card__pill">${o>=100?t(e("overview.above_target")):t(e("overview.below_target"))}</span>
      </div>
      <div class="target-card__body">
        <div class="target-card__ring" style="--p:${Math.min(100,o)}">
          <svg viewBox="0 0 120 120" aria-hidden="true">
            <circle class="target-card__track" cx="60" cy="60" r="52"></circle>
            <circle class="target-card__prog" cx="60" cy="60" r="52"></circle>
          </svg>
          <div class="target-card__ring-value">
            <strong>${G(o)}</strong>
            <span>${t(e("sales.of_target"))}</span>
          </div>
        </div>
        <div class="target-card__side">
          <div class="target-card__stat"><span>${t(e("overview.target"))}</span><strong>${C(c)}</strong></div>
          <div class="target-card__stat"><span>${t(e("overview.actual"))}</span><strong>${C(u)}</strong></div>
          <div class="target-card__delta">
            <strong>${p>=0?"+":""}${C(p)}</strong>
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
    <div class="lead-panel__body">${ys()}</div>
  </section>

  <div class="grid three-col" style="margin-top:12px">
    ${(n.branches||[]).slice(0,6).map(f=>xt(f.name,f.new_buyers||0,f.buyers||0,f.conv||0,f.sales||0,f.avg||0,f.buyers||0)).join("")||`<section class="card"><div class="empty"><strong>${t(e("sales.empty_branches"))}</strong></div></section>`}
  </div>`,ie(),d("#salesRefreshBtn")&&(d("#salesRefreshBtn").onclick=()=>la()),requestAnimationFrame(()=>ua())}function ys(){const n=L&&L.beauticians&&L.beauticians.length?L.beauticians:[];return n.length?`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
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
    </tr>`}).join("")}</tbody></table></div>`:`<div class="lead-empty"><strong>${t(e("sales.empty_beauticians"))}</strong></div>`}function $s(){const n=(v.userEditUrlTemplate||"").replace(/\/__ID__\/edit$/,"").replace(/\/__ID__$/,"")||"";E.innerHTML=`<div class="cus-shell">
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
          <input class="lead-search__input" id="cusSearch" type="search" autocomplete="off" placeholder="${t(e("customers.search_placeholder"))}" value="${t(r.customerSearch||"")}" />
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
  </div>`;const a=d("#cusRefresh");a&&(a.onclick=()=>Ce()),ws(),Ce()}function ws(){const n=d("#cusSearch");n&&(n.oninput=()=>{clearTimeout(Ga),Ga=setTimeout(()=>{r.customerSearch=n.value.trim(),r.customerPage=1,Ce()},320)});const a=d("#cusBranch");a&&(a.onchange=()=>{r.customerBranch=a.value,r.customerPage=1,Ce()})}async function Ce(){const n=v.customersUrl||"",a=d("#cusTableMount");if(!n){a&&(a.innerHTML=`<div class="pay-empty"><strong>${t(e("customers.load_error"))}</strong></div>`);return}ia=!0,yt(),$t();const s=new URLSearchParams;r.customerSearch&&s.set("q",r.customerSearch),r.customerSegment&&r.customerSegment!=="all"&&s.set("segment",r.customerSegment);const i=r.customerBranch!=="all"?r.customerBranch:r.branch||"all";i&&i!=="all"&&s.set("branch",i),r.period&&s.set("period",r.period),s.set("page",String(r.customerPage||1)),s.set("per_page","25");try{const l=await fetch(`${n}?${s.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("customers "+l.status);const o=await l.json();we=Array.isArray(o.data)?o.data:[],qa=Object.assign({total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0},o.meta&&o.meta.summary||{}),Ee=Object.assign({segments:[],branches:[]},o.filters||{}),He={current_page:o.meta&&o.meta.current_page||1,last_page:o.meta&&o.meta.last_page||1,total:o.meta&&o.meta.total||0}}catch(l){console.error(l),we=[],g(e("customers.load_error"))}finally{ia=!1,yt(),ks(),Ss(),$t()}}function yt(){const n=qa,a=d("#cusPulse");a&&(a.textContent=He.total>0?e("customers.pulse_busy",{count:h(He.total)}):e("customers.pulse_clear"));const s=d("#cusMetrics");s&&(s.innerHTML=`
      <div class="pay-metric customer-metric customer-metric--total"><span>${t(e("customers.stat_total"))}</span><strong>${h(n.total)}</strong></div>
      <div class="pay-metric customer-metric customer-metric--buyers"><span>${t(e("customers.stat_buyers"))}</span><strong>${h(n.buyers)}</strong></div>
      <div class="pay-metric customer-metric customer-metric--new"><span>${t(e("customers.stat_new"))}</span><strong>${h(n.new_buyers)}</strong></div>
      <div class="pay-metric customer-metric customer-metric--sales"><span>${t(e("customers.stat_sales"))}</span><strong>${R(n.period_sales)}</strong></div>
    `)}function ks(){const n=d("#cusTabs");if(!n)return;const a=qa,s={all:a.total||0,buyers:a.buyers||0,new:a.new_buyers||0,returning:a.returning||0,leads:a.with_leads||0},i=Ee.segments&&Ee.segments.length?Ee.segments:[{value:"all",label:e("customers.tab_all")},{value:"buyers",label:e("customers.tab_buyers")},{value:"new",label:e("customers.tab_new")},{value:"returning",label:e("customers.tab_returning")},{value:"leads",label:e("customers.tab_leads")}];n.innerHTML=i.map(l=>{const o=l.value,c=r.customerSegment===o?"active":"",u=s[o],p=u!==void 0?`<span class="pay-tab-count">${h(u)}</span>`:"";return`<button type="button" class="tab ${c}" role="tab" data-ctab="${t(o)}">${t(l.label)}${p}</button>`}).join(""),S("[data-ctab]",n).forEach(l=>{l.onclick=()=>{r.customerSegment=l.dataset.ctab,r.customerPage=1,Ce()}})}function Ss(){const n=d("#cusBranch");if(n){const a=r.customerBranch;n.innerHTML=`<option value="all">${t(e("customers.all_branches"))}</option>`+(Ee.branches||[]).map(s=>`<option value="${s.id}" ${String(a)===String(s.id)?"selected":""}>${t(s.code?s.code+" · "+s.name:s.name)}</option>`).join("")}}function $t(){const n=d("#cusTableMount"),a=d("#cusResultCount");if(a&&(a.textContent=e("customers.result_count",{count:h(He.total||we.length)})),!n)return;if(ia){n.innerHTML=`<div class="pay-empty"><strong>${t(e("customers.loading"))}</strong></div>`;return}if(!we.length){n.innerHTML=`<div class="pay-empty"><strong>${t(e("customers.empty"))}</strong><span>${t(e("customers.empty_hint"))}</span></div>`;return}const s=we.map(l=>{const o=[`<span class="pay-chip ${l.segment==="new"||l.segment==="buyer"?"pay-chip--ok":"pay-chip--muted"}">${t(l.segment_label||"")}</span>`,l.has_lead?`<span class="pay-chip pay-chip--warn">${t(e("customers.chip_lead"))}</span>`:"",l.loyalty_tier?`<span class="pay-chip pay-chip--muted">${t(l.loyalty_tier)}</span>`:""].filter(Boolean).join(""),c=l.avatar_url?`<div class="mini-avatar mini-avatar--photo"><img src="${t(l.avatar_url)}" alt=""></div>`:`<div class="mini-avatar">${t(l.initial||"?")}</div>`,u=v.canViewUser&&v.userEditUrlTemplate?N(v.userEditUrlTemplate,l.id):"";return`<tr data-customer-segment="${t(l.segment||"registered")}">
      <td><span class="pay-id">${t(l.code||"CUS-"+l.id)}</span></td>
      <td><div class="person-cell">${c}<div><strong>${t(l.name||"")}</strong><small>${t(l.phone||l.email||"")}</small></div></div></td>
      <td>${t(l.branch||"—")}</td>
      <td><strong>${h(l.paid_orders_count)}</strong><div class="pay-method">${h(l.orders_count)} total</div></td>
      <td><div class="pay-amount">${R(l.paid_sales)}</div><div class="pay-method">${R(l.period_sales)} ${t(e("customers.detail_period").toLowerCase())}</div></td>
      <td>${t(l.last_order_label||"—")}</td>
      <td><div class="pay-chips">${o}</div></td>
      <td class="lead-table__actions"><div class="lead-menu customer-action-menu">
        <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${t(e("customers.row_actions",{name:l.name||e("customers.guest")}))}"><span class="lead-menu__dots" aria-hidden="true"></span></button>
        <div class="lead-menu__panel" role="menu" hidden>
          <button type="button" class="lead-menu__item" role="menuitem" data-customer="${l.id}">${t(e("customers.view"))}</button>
          <button type="button" class="lead-menu__item" role="menuitem" data-customer-payments="${l.id}" data-customer-name="${t(l.name||"")}" data-customer-phone="${t(l.phone||"")}" data-customer-email="${t(l.email||"")}">${t(e("customers.open_payments"))}</button>
          ${u?`<a class="lead-menu__item" role="menuitem" href="${t(u)}" target="_blank" rel="noopener">${t(e("customers.open_profile"))}</a>`:""}
        </div>
      </div></td>
    </tr>`}).join(""),i=ne("cus",He);n.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("customers.col_id"))}</th>
    <th>${t(e("customers.col_customer"))}</th>
    <th>${t(e("customers.col_branch"))}</th>
    <th>${t(e("customers.col_orders"))}</th>
    <th>${t(e("customers.col_sales"))}</th>
    <th>${t(e("customers.col_last"))}</th>
    <th>${t(e("customers.col_segment"))}</th>
    <th>${t(e("customers.col_action"))}</th>
  </tr></thead><tbody>${s}</tbody></table></div>${i}`,Ye(n),S("[data-customer]",n).forEach(l=>l.onclick=()=>{H(),Ms(Number(l.dataset.customer))}),S("[data-customer-payments]",n).forEach(l=>l.onclick=()=>{H(),Ft({id:l.dataset.customerPayments,name:l.dataset.customerName,phone:l.dataset.customerPhone,email:l.dataset.customerEmail})}),S('.customer-action-menu a[role="menuitem"]',n).forEach(l=>l.onclick=()=>H()),se("cus",l=>(r.customerPage=l,Ce()))}function Ms(n){const a=we.find(u=>Number(u.id)===Number(n));if(!a)return;const s=N(v.userEditUrlTemplate,a.id),i=!!v.canViewUser,o=`<div class="pay-review">
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
  </div>`,c=[i?`<a class="btn primary" href="${t(s)}" target="_blank" rel="noopener">${t(e("customers.open_profile"))}</a>`:"",`<button type="button" class="btn" data-jump="payments" data-customer-id="${t(String(a.id))}" data-customer-name="${t(a.name||"")}" data-customer-phone="${t(a.phone||"")}" data-customer-email="${t(a.email||"")}">${t(e("customers.open_payments"))}</button>`,`<button type="button" class="btn" data-action-drawer-close>${t(e("customers.close"))}</button>`].filter(Boolean).join("");z(e("customers.title"),a.code+" · "+a.name,o,c,"Customer"),setTimeout(()=>{S("#actionDrawerFoot [data-jump]").forEach(u=>u.onclick=()=>{if(A(),u.dataset.jump==="payments"&&u.dataset.customerId){Ft({id:u.dataset.customerId,name:u.dataset.customerName,phone:u.dataset.customerPhone,email:u.dataset.customerEmail});return}V(u.dataset.jump)})},0)}function Xt(){const n=d("#donutChart");if(!n)return;Ke(n);const a=n.getContext("2d"),s=n.clientWidth,i=n.clientHeight,l=L&&L.status_mix||[],o=["#2563eb","#0ea5e9","#059669","#d97706","#e11d48","#6366f1"],c=l.map(($,f)=>({label:$.name,value:Number($.value||0),color:o[f%o.length]})),u=c.reduce(($,f)=>$+f.value,0);if(!c.length||u<=0){a.clearRect(0,0,s,i),a.fillStyle="#94a3b8",a.font="12px Poppins,sans-serif",a.textAlign="center",a.fillText(e("overview.no_branch_data"),s/2,i/2);const $=d("#leadLegend");$&&($.innerHTML="");return}let p=-Math.PI/2;const m=s/2,_=i/2,b=Math.min(s,i)/2-8;c.forEach($=>{const f=p+$.value/u*Math.PI*2;a.beginPath(),a.moveTo(m,_),a.arc(m,_,b,p,f),a.closePath(),a.fillStyle=$.color,a.fill(),p=f}),a.beginPath(),a.arc(m,_,b*.58,0,Math.PI*2),a.fillStyle="#fff",a.fill();const k=d("#leadLegend");k&&(k.innerHTML=c.map($=>`<div class="legend-row"><span class="dot" style="background:${$.color}"></span><span>${t($.label)}</span><strong>${h($.value)}</strong><span class="pct">${($.value/u*100).toFixed(1)}%</span></div>`).join(""))}function ua(){const n=d("#salesChart");if(!n)return;Ke(n);const a=n.getContext("2d"),s=n.clientWidth,i=n.clientHeight,l=L&&L.equity||{},o=Array.isArray(l.labels)?l.labels:[];let c;l.actual&&l.actual.length?(c=l.actual.map((y,T)=>T===0?Number(y):Math.max(0,Number(y)-Number(l.actual[T-1]))),c=c.map(y=>Math.round(y/1e3))):c=[];const u=Math.round((L&&L.targets&&L.targets.sales||0)/1e3),p={l:36,r:16,t:28,b:36},m=Math.max(u,...c,1)*1.15,_=y=>p.l+y/Math.max(c.length-1,1)*(s-p.l-p.r),b=y=>i-p.b-y/m*(i-p.t-p.b);a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let y=0;y<4;y++){const T=p.t+(i-p.t-p.b)/3*y;a.beginPath(),a.moveTo(p.l,T),a.lineTo(s-p.r,T),a.stroke()}const k=b(u);a.setLineDash([5,5]),a.strokeStyle="#d97706",a.beginPath(),a.moveTo(p.l,k),a.lineTo(s-p.r,k),a.stroke(),a.setLineDash([]),a.fillStyle="#64748b",a.font="600 10px Poppins",a.textAlign="left",a.fillText("Target",p.l+4,k-6);const $="#1d4ed8",f="#0ea5e9",w=a.createLinearGradient(0,p.t,0,i-p.b);w.addColorStop(0,"rgba(37,99,235,.22)"),w.addColorStop(1,"rgba(14,165,233,.02)"),a.beginPath(),a.moveTo(_(0),i-p.b),c.forEach((y,T)=>a.lineTo(_(T),b(y))),a.lineTo(_(c.length-1),i-p.b),a.closePath(),a.fillStyle=w,a.fill(),a.beginPath(),c.forEach((y,T)=>T?a.lineTo(_(T),b(y)):a.moveTo(_(T),b(y))),a.strokeStyle=$,a.lineWidth=2.75,a.lineJoin="round",a.lineCap="round",a.stroke();const M=Math.max(1,Math.ceil(c.length/8));c.forEach((y,T)=>{if(T%M&&T!==c.length-1)return;const B=_(T),D=b(y);a.beginPath(),a.arc(B,D,4,0,Math.PI*2),a.fillStyle="#fff",a.fill(),a.lineWidth=2,a.strokeStyle=f,a.stroke(),a.fillStyle="#0f172a",a.font="700 10px Poppins",a.textAlign="center",a.fillText("RM"+y+"k",B,D-10),a.fillStyle="#64748b",a.font="600 10px Poppins",a.fillText(o[T]||"",B,i-12)})}function Ts(){if(!d("#branchChart"))return;const n=d("#branchChart"),a=n.getContext("2d"),s=n.clientWidth,i=n.clientHeight,l={l:45,r:20,t:20,b:35};Ke(n);const o=Array.isArray(L&&L.branches)?L.branches:[],c=o.map(b=>Number(b.new_buyer_share_pct)||Number(b.conv)||0),u=o.map(b=>String(b.name||"").slice(0,2).toUpperCase()||"—");if(!c.length)return;const p=Math.max(...c,50),m=Math.max(8,(s-l.l-l.r-70*c.length)/(c.length+1)),_=Math.min(70,(s-l.l-l.r-m*(c.length+1))/c.length);c.forEach((b,k)=>{const $=l.l+m+(_+m)*k,f=i-l.b-b/p*(i-l.t-l.b);a.fillStyle=[re("--brand","#38bdf8"),re("--rose","#0ea5e9"),re("--navy","#2563eb")][k%3],a.fillRect($,f,_,i-l.b-f),a.fillStyle=re("--navy","#1d4ed8"),a.textAlign="center",a.font="700 14px Poppins",a.fillText(b+"%",$+_/2,f-8),a.font="13px Poppins",a.fillText(u[k],$+_/2,i-12)})}function Ke(n){const a=Math.max(1,window.devicePixelRatio||1),s=n.getBoundingClientRect();n.width=s.width*a,n.height=s.height*a,n.getContext("2d").setTransform(a,0,0,a,0,0)}const Ya=["overview","leads","import","imports","followup","sales","payments","customers","wallet","checkin","clearance","beauticians","branches","audit"],qe=String(v.basePath||"").replace(/\/$/,"")||"/admin/leads/central";function wt(n){const a=Ya.includes(n)?n:"overview";return a==="overview"?qe:qe+"/"+a}function Qt(n){const a=String(location.pathname).replace(/\/$/,"");if(a===qe)return"overview";if(a.startsWith(qe+"/")){const s=a.slice(qe.length+1).split("/")[0];return Ya.includes(s)?s:"overview"}return"overview"}function kt(){const n=new URLSearchParams(location.search);return n.set("branch",r.branch||"all"),r.period?n.set("period",r.period):n.delete("period"),"?"+n.toString()}function Cs(n,{replace:a=!1,silent:s=!1}={}){const i=wt(n)+kt()+location.hash;!s&&(a||i!==location.pathname+location.search+location.hash)&&history[a?"replaceState":"pushState"]({view:n},"",i),S(".nav-item[data-view]").forEach(l=>l.href=wt(l.dataset.view)+kt())}function Ls(){const n=new URLSearchParams(location.search),a=n.get("branch")||"all",s=n.get("period")||d("#periodScope").options[0]?.value||"",i=d("#branchScope"),l=d("#periodScope");i.value=a,r.branch=i.value||"all",i.value=r.branch,/^\d{4}-(0[1-9]|1[0-2])$/.test(s)&&![...l.options].some(o=>o.value===s)&&l.add(new Option(s,s)),l.value=s,r.period=l.value||l.options[0]?.value||"",l.value=r.period}function Zt(){r.branch=d("#branchScope").value||"all",r.period=d("#periodScope").value||"",Te[r.view]&&(Te[r.view].page=1),V(r.view)}function V(n,a={}){ze();const s=Ya.includes(n)?n:"overview";Z(),A(),window.IMMA_TRADE&&s!=="overview"&&IMMA_TRADE.dispose(),r.view=s;const i=s==="wallet";for(const o of[d("#branchScope"),d("#periodScope")])o.disabled=i,o.title=i?e("wallet.scope_hint"):"";Cs(s,a),S(".nav-item[data-view]").forEach(o=>o.classList.toggle("active",o.dataset.view===s)),d("#crumbCurrent").textContent={overview:e("common.overview_crumb"),leads:e("nav.leads"),import:e("nav.import"),imports:e("nav.imports"),payments:e("nav.payments"),wallet:e("nav.wallet"),checkin:e("nav.checkin"),clearance:e("nav.clearance"),beauticians:e("nav.beauticians"),branches:e("nav.branches"),audit:e("nav.audit"),followup:e("nav.followup"),sales:e("nav.sales"),customers:e("nav.customers")}[s]||s;const l=Ma!==JSON.stringify([r.branch,r.period]);["overview","sales"].includes(s)&&l?(E.innerHTML=`<section class="card"><div class="pay-empty" role="status">${t(e("overview.loading"))}</div></section>`,la()):(({overview:Lt,leads:wn,import:Bn,imports:qn,followup:fs,sales:Gt,payments:Ut,customers:$s,wallet:cs,checkin:Vn,clearance:as,beauticians:_s,branches:vs,audit:hs}[s]||(()=>bs(e("nav."+s),e("operations.not_ready"))))(),l&&la()),a.silent||window.scrollTo({top:0,behavior:"smooth"}),innerWidth<1e3&&d("#sidebar").classList.remove("open")}function ie(){S("[data-jump]").forEach(n=>n.onclick=()=>{n.dataset.importTab&&(r.importTab=n.dataset.importTab),V(n.dataset.jump)})}let aa=null,en="",Me=!1;function z(n,a,s,i,l=""){Z(),A(),aa=document.activeElement,en=document.body.style.overflow,d("#actionDrawerEyebrow").textContent=l,d("#actionDrawerTitle").textContent=n,d("#actionDrawerBody").innerHTML=`<p class="action-drawer__subtitle">${t(a)}</p>${s}`,d("#actionDrawerFoot").innerHTML=i,d("#actionDrawerClose").setAttribute("aria-label",e("wallet.close")),d("#actionDrawer").classList.add("show"),d("#actionDrawer").setAttribute("aria-hidden","false"),d("#actionDrawer").inert=!1,d("#actionDrawerBackdrop").classList.add("show"),d(".app-shell").inert=!0,document.body.style.overflow="hidden",d("#actionDrawerBody").scrollTop=0,d("#actionDrawerClose").focus({preventScroll:!0});const o=d("#saveLead");o&&(o.onclick=()=>rt());const c=d("#leadEditorForm");c&&(Me=!1,c.addEventListener("submit",u=>{u.preventDefault(),rt()}),c.addEventListener("input",()=>{Me=!0,ge("")}),c.addEventListener("change",()=>{Me=!0,ge("")})),S("[data-action-drawer-close]",d("#actionDrawer")).forEach(u=>u.onclick=A)}function A(){const n=d("#actionDrawer");if(n.classList.contains("show"))return Me&&!window.confirm(e("workspace.discard_changes"))?!1:(da(),n.classList.remove("show"),n.setAttribute("aria-hidden","true"),n.inert=!0,d("#actionDrawerBackdrop").classList.remove("show"),d(".app-shell").inert=!1,document.body.style.overflow=en,aa?.isConnected&&aa.focus({preventScroll:!0}),aa=null,Me=!1,!0)}function Z(){const n=d("#leadDrawer");n.classList.contains("show")&&(n.classList.remove("show"),n.inert=!0,d(".app-shell").inert=!1,d("#drawerBackdrop").classList.remove("show"),n.setAttribute("aria-hidden","true"),n.classList.contains("wallet-drawer")&&(n.classList.remove("wallet-drawer"),d("#walletDrawerFoot")?.remove()),document.body.style.overflow=Va,Ae?.isConnected&&Ae.focus({preventScroll:!0}),Ae=null)}let Aa={};function an(){return String(v.notificationStorageKey||"imma-central.notifications.v1.guest")}function xs(){return`${r.branch||"all"}|${r.period||""}`}function tn(n){return`${xs()}|${n.id}|${n.count}`}function nn(){try{const n=JSON.parse(localStorage.getItem(an())||"{}");return n&&typeof n=="object"&&!Array.isArray(n)?n:{}}catch{return{...Aa}}}function Bs(n){const a=Date.now()-7776e6,s=Object.entries(n).filter(([,i])=>Number(i)>=a).sort((i,l)=>Number(i[1])-Number(l[1])).slice(-100);Aa=Object.fromEntries(s);try{localStorage.setItem(an(),JSON.stringify(Aa))}catch{}}function Sa(n){const a={payments:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/></svg>',clearance:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>',leads:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M3 20c.7-4 2.7-6 6-6 2.1 0 3.7.8 4.8 2.3M18 8v6M15 11h6"/></svg>'};return a[n]||a.leads}function sn(){const n=L||{},a=[],s=Number(n.ops?.payments?.queue||0),i=Number(n.ops?.clearance?.queue||0),l=Number(n.kpis?.new_buyers||n.kpis?.unique_leads||0);return s>0&&a.push({id:"payments",count:s,icon:Sa("payments"),title:e("notifications.payment_title"),detail:e("notifications.payment_detail",{count:h(s)}),view:"payments"}),i>0&&a.push({id:"clearance",count:i,icon:Sa("clearance"),title:e("notifications.clearance_title"),detail:e("notifications.clearance_detail",{count:h(i)}),view:"clearance"}),l>0&&a.push({id:"leads",count:l,icon:Sa("leads"),title:e("notifications.leads_title"),detail:e("notifications.leads_detail",{count:h(l),period:n.period?.label||e("notifications.selected_period")}),view:"leads"}),a}function ln(){const n=nn();return sn().filter(a=>!n[tn(a)])}function Ps(n){const a=nn(),s=Date.now();n.forEach(i=>{a[tn(i)]=s}),Bs(a)}function ha(){const n=d("#notificationButton"),a=d("#notificationMenu"),s=d("#notificationCount");if(!n||!a||!s)return;const i=sn(),l=ln();s.textContent=String(l.length),s.hidden=l.length===0,n.setAttribute("aria-label",l.length?e("notifications.button_count",{count:h(l.length)}):e("notifications.button"));const o=l.length?e("notifications.active_count",{count:h(l.length)}):i.length?e("notifications.cleared"):e("notifications.all_clear"),c=i.length?e("notifications.cleared_hint"):e("notifications.empty");a.innerHTML=`<div class="notification-menu__head"><div class="notification-menu__title"><span id="notificationMenuTitle">${t(e("notifications.title"))}</span><small>${t(o)}</small></div>${l.length?`<button type="button" class="notification-menu__clear" data-clear-notifications>${t(e("notifications.clear"))}</button>`:""}</div>`+(l.length?l.map(u=>`<button type="button" class="notification-menu__item" data-notification-view="${t(u.view)}"><span class="notification-menu__icon">${u.icon}</span><span><strong>${t(u.title)}</strong><small>${t(u.detail)}</small></span></button>`).join("")+`<p class="notification-menu__note">${t(e("notifications.clear_note"))}</p>`:`<div class="notification-menu__empty">${t(c)}</div>`)}function Es(){const n=ln();if(!n.length)return;ze();const a=`<div class="notification-clear-summary"><strong>${t(e("notifications.clear_summary",{count:h(n.length)}))}</strong><p>${t(e("notifications.clear_note"))}</p></div>`,s=`<button type="button" class="btn" data-action-drawer-close>${t(e("notifications.cancel"))}</button><button type="button" class="btn danger" id="notificationClearConfirm">${t(e("notifications.confirm_clear"))}</button>`;z(e("notifications.clear_title"),e("notifications.clear_subtitle"),a,s,e("notifications.title")),d("#notificationClearConfirm").onclick=()=>{Ps(n),A(),ha(),d("#notificationButton")?.focus({preventScroll:!0}),g(e("notifications.clear_success"))}}function js(n=null){const a=d("#notificationButton"),s=d("#notificationMenu");if(!a||!s)return;const i=n===null?s.hidden:!n;!s.hidden!==i&&(ha(),s.hidden=!i,a.setAttribute("aria-expanded",String(i)))}function ze(){const n=d("#notificationButton"),a=d("#notificationMenu");!n||!a||a.hidden||(a.hidden=!0,n.setAttribute("aria-expanded","false"))}function g(n){const a=d("#toast");a.textContent=n,a.classList.add("show"),clearTimeout(g._t),g._t=setTimeout(()=>a.classList.remove("show"),2300)}window.showToast=g;window.navigate=V;window.$=d;S(".nav-item[data-view]").forEach(n=>n.addEventListener("click",a=>{a.defaultPrevented||a.metaKey||a.ctrlKey||a.shiftKey||a.altKey||a.button!==0||(a.preventDefault(),n.dataset.view==="payments"&&(r.paymentCustomerId=null,r.paymentCustomerLabel=""),V(n.dataset.view))}));let De=null;function rn(){const n=d("#crumbCurrent").textContent,a=[d("#branchScope"),d("#periodScope"),...S('select,input[type="search"],input[type="date"]',E)].filter(i=>i&&!i.closest("[hidden]")).map(i=>{const l=i.tagName==="SELECT"?i.selectedOptions[0]?.textContent:i.value;if(!l?.trim())return"";const o=i.closest("label")?.querySelector(".lead-field__label")?.textContent||i.parentElement.querySelector("label")?.textContent||"";return o?o.trim()+": "+l.trim():l.trim()}).filter(Boolean);for(const i of[d("#branchScope"),d("#periodScope")]){const l=i.selectedOptions[0]?.textContent?.trim();l&&!a.includes(l)&&a.unshift(l)}S('.tab.active,[role="tab"][aria-selected="true"]',E).forEach(i=>a.push(i.textContent.trim())),d("#centralPrintHeader").innerHTML=`<div class="central-print-brand">${t(e("brand_subtitle"))}</div><h1>${t(n)}</h1><p>${t([...new Set(a)].join(" · "))}</p><p>${t(e("export_pdf.generated"))}: ${t(new Date().toLocaleString(v.locale==="ms"?"ms-MY":"en-MY"))}</p><small>${t(e("export_pdf.scope"))}</small>`;const s=["workspace","payments","customers","wallet","checkin","clearance"].map(i=>e(i+".col_action").toLowerCase());S("table",E).forEach(i=>{S("thead tr:last-child th",i).forEach((o,c)=>{s.includes(o.textContent.trim().toLowerCase())&&(o.classList.add("central-print-action"),S("tbody tr",i).forEach(u=>u.children[c]?.classList.add("central-print-action")))})}),De===null&&(De=document.title),document.title="Central - "+n+" - "+(r.period||"")}function As(){De!==null&&(document.title=De,De=null),S(".central-print-action",E).forEach(n=>n.classList.remove("central-print-action"))}d("#exportCentralPdf").onclick=async()=>{if({leads:ta,followup:na,payments:sa,customers:ia,wallet:pe,checkin:me,clearance:ve}[r.view]||d('[aria-busy="true"],[role="status"]',E)){g(e("export_pdf.loading"));return}const a=d("#exportCentralPdf");a.disabled=!0;try{document.fonts?.ready&&await document.fonts.ready,rn(),window.print()}finally{a.disabled=!1}};window.addEventListener("beforeprint",rn);window.addEventListener("afterprint",As);d("#menuToggle").onclick=()=>d("#sidebar").classList.toggle("open");d("#notificationButton").onclick=()=>js();d("#notificationMenu").onclick=n=>{if(n.target.closest("[data-clear-notifications]")){Es();return}const a=n.target.closest("[data-notification-view]");a&&(ze(),V(a.dataset.notificationView))};document.addEventListener("keydown",n=>{n.key==="Escape"&&!d("#notificationMenu").hidden&&(n.preventDefault(),ze(),d("#notificationButton").focus({preventScroll:!0}))});document.addEventListener("pointerdown",n=>{n.target.closest(".notification-wrap")||ze()},!0);ha();d("#drawerClose").onclick=Z;d("#drawerBackdrop").onclick=Z;d("#actionDrawerClose").onclick=A;d("#actionDrawerBackdrop").onclick=A;d("#branchScope").onchange=Zt;d("#periodScope").onchange=Zt;d("#globalSearch").addEventListener("keydown",n=>{n.key==="Enter"&&(V("leads"),r.leadSearch=n.target.value,r.leadPage=1)});window.addEventListener("resize",()=>{r.view==="overview"&&(Tt(),Ct(),Xt(),ua(),window.IMMA_TRADE&&IMMA_TRADE.resize()),r.view==="sales"&&ua(),r.view==="branches"&&Ts()});window.addEventListener("popstate",()=>{Ls(),V(Qt(),{silent:!0})});V(v.initialView||Qt(),{replace:!0});
