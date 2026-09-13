function Z(t,a){return getComputedStyle(document.documentElement).getPropertyValue(t).trim()||a}function e(t,a={}){const n=String(t).split(".");let i=window.IMMA_CENTRAL&&window.IMMA_CENTRAL.i18n||{};for(const o of n)if(i&&typeof i=="object"&&o in i)i=i[o];else{i=null;break}let l=typeof i=="string"?i:t;return Object.keys(a||{}).forEach(o=>{l=l.replace(new RegExp(":"+o,"g"),String(a[o]))}),l}const c=(t,a=document)=>{const n=typeof a=="string"?document.querySelector(a):a||document;return n?n.querySelector(t):null},T=(t,a=document)=>{const n=typeof a=="string"?document.querySelector(a):a||document;return n?[...n.querySelectorAll(t)]:[]},Ut={leads:[]},_=window.IMMA_CENTRAL||{};let r={view:"overview",branch:String(c("#branchScope")&&c("#branchScope").value||"all"),period:String(c("#periodScope")&&c("#periodScope").value||_.metrics&&_.metrics.period&&_.metrics.period.key||""),leadSearch:"",leadStatus:"all",leadBeautician:"all",leadBranch:"all",leadPage:1,leadMonth:(function(){const t=new Date;return t.getFullYear()+"-"+String(t.getMonth()+1).padStart(2,"0")})(),leadCalYear:null,followBucket:"all",followSearch:"",followPage:1,importTab:"paste",paymentTab:"queue",paymentSearch:"",paymentCustomerId:null,paymentCustomerLabel:"",paymentBeautician:"all",paymentBranch:"all",paymentPage:1,customerSegment:"all",customerSearch:"",customerBranch:"all",customerPage:1,walletSegment:"all",walletSearch:"",walletTier:"all",walletPage:1,walletCustomerId:null,checkinStatus:"live",checkinSearch:"",checkinBranch:"all",checkinBeautician:"all",checkinPage:1,checkinDate:_.today||new Date().toLocaleDateString("en-CA"),checkinScope:"day",clearanceState:"waiting",clearanceSearch:"",clearanceBranch:"all",clearanceBeautician:"all",clearancePage:1};const B=c("#viewRoot");let L=_.metrics||null,ca=L?JSON.stringify([r.branch,r.period]):null,sa=0,ie=[],ue={raw:0,unique:0,duplicates:0,existing:0,converted:0,conversion_pct:0},E={statuses:[],beauticians:[],branches:[],months:[]},Ve=!1,Ba=null,$e=null,we=[],da={queue:0,overdue:0,due_today:0,no_response:0,lost:0},ke={buckets:[],beauticians:[],branches:[],statuses:[]},Ye=!1,Ea=null,me=[],ba={pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0},ve={statuses:[],beauticians:[],branches:[]},Ke=!1,Aa=null,pa={current_page:1,last_page:1,total:0},he=[],ga={total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0},Se={segments:[],branches:[]},ze=!1,qa=null,xe={current_page:1,last_page:1,total:0},Pe=[],ya={members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0},ot={segments:[],tiers:[]},le=!1,Re=0,je=!1,Na=null,fa={current_page:1,last_page:1,total:0},Be=[],$a={live:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0},ua={statuses:[],beauticians:[],branches:[]},re=!1,Ue=0,Ee=!1,Ha=null,wa={current_page:1,last_page:1,total:0},_e=null,ma=0,Ae=[],ka={waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0},va={states:[],beauticians:[],branches:[]},oe=!1,Ie=0,qe=!1,Da=null,Sa={current_page:1,last_page:1,total:0};function U(t,a){return String(t||"").replace("__ID__",String(a))}function D(t=!0){const a={Accept:"application/json","X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":_.csrf||document.querySelector('meta[name="csrf-token"]')?.content||""};return t&&(a["Content-Type"]="application/json"),a}async function q(){const t=_.leadsUrl||"";if(!t){$(e("workspace.load_error"));return}const a=new URLSearchParams;r.leadSearch&&a.set("q",r.leadSearch),r.leadStatus&&r.leadStatus!=="all"&&a.set("status",r.leadStatus);const n=r.leadBranch!=="all"?r.leadBranch:r.branch||"all";n&&n!=="all"&&a.set("branch",n),r.leadBeautician&&r.leadBeautician!=="all"&&a.set("beautician",r.leadBeautician),r.leadMonth&&r.leadMonth!=="all"&&a.set("month",r.leadMonth),a.set("page",String(r.leadPage||1)),a.set("per_page","50"),Ve=!0,Oa();try{const i=await fetch(`${t}?${a.toString()}`,{headers:D(!1),credentials:"same-origin"});if(!i.ok)throw new Error("leads "+i.status);const l=await i.json();ct=l.meta||{},ie=Array.isArray(l.data)?l.data:[],ue=l.meta&&l.meta.summary||ue,E=l.filters||E,Ut.leads=ie}catch(i){console.error(i),$(e("workspace.load_error"))}finally{Ve=!1,r.view==="leads"&&(bt(),Oa(),Je(),He())}}function h(t){return Number(t||0).toLocaleString("en-MY")}function C(t){return"RM"+Number(t||0).toLocaleString("en-MY",{maximumFractionDigits:0})}function W(t){return Number(t||0).toLocaleString("en-MY",{maximumFractionDigits:1})+"%"}function s(t){return String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;")}function Y(t,a=""){const n=Number(t||0),i=Math.abs(n).toFixed(1)+a;return n>0?`↑ +${i} ${e("overview.vs_prev_period")}`:n<0?`↓ -${i} ${e("overview.vs_prev_period")}`:`→ 0${a} ${e("overview.vs_prev_period")}`}function Q(t,a){return`${e("overview.target")}: ${a}`}async function Ma(){const t=_.metricsUrl||"",a=++sa,n=r.branch||"all",i=r.period||"",l=JSON.stringify([n,i]),o=new URLSearchParams({branch:n,period:i});try{if(!t)throw new Error("Missing metrics endpoint");const d=await fetch(`${t}?${o.toString()}`,{headers:D(!1),credentials:"same-origin"});if(!d.ok)throw new Error("metrics "+d.status);const u=await d.json();if(a!==sa||l!==JSON.stringify([r.branch,r.period]))return;if(!u.metrics)throw new Error("Missing metrics payload");L=u.metrics,ca=l,_.metrics=L,Pa(),r.view==="overview"&&mt(),r.view==="sales"&&Bt()}catch{if(a!==sa||l!==JSON.stringify([r.branch,r.period]))return;["overview","sales"].includes(r.view)&&ca!==l?(B.innerHTML=`<section class="card"><div class="pay-empty" role="alert"><strong>${s(e("overview.metrics_error"))}</strong><button type="button" class="btn" id="retryMetrics">${s(e("reporting.refresh"))}</button></div></section>`,c("#retryMetrics").onclick=()=>Ma()):$(e("overview.metrics_error"))}}function G(t,a={}){const n=Math.max(1,Number(a.last_page)||1),i=Math.max(1,Math.min(n,Number(a.current_page)||1)),l=n<=5?Array.from({length:n},(p,m)=>m+1):[...new Set([1,i-1,i,i+1,n].filter(p=>p>=1&&p<=n))].sort((p,m)=>p-m),o=(p,m,v="")=>`<button type="button" class="central-pagination__button" data-page="${p}" ${v}>${m}</button>`;let d="",u=0;return l.forEach(p=>{u&&p-u>1&&(d+='<span class="central-pagination__ellipsis" aria-hidden="true">…</span>'),d+=o(p,String(p),`aria-label="${s(e("pagination.page",{page:p}))}" ${p===i?'aria-current="page" disabled':""}`),u=p}),`<nav class="central-pagination" data-pager="${t}" aria-label="${s(e("pagination.label"))}">
    ${o(i-1,"‹",`id="${t}Prev" aria-label="${s(e("operations.previous"))}" ${i<=1?"disabled":""}`)}
    ${d}
    ${o(i+1,"›",`id="${t}Next" aria-label="${s(e("operations.next"))}" ${i>=n?"disabled":""}`)}
  </nav>`}function J(t,a){const n=c(`[data-pager="${t}"]`);n&&T("button[data-page]",n).forEach(i=>i.onclick=async()=>{if(i.disabled)return;const l=T("button:not(:disabled)",n);l.forEach(o=>o.disabled=!0),n.setAttribute("aria-busy","true");try{await a(Number(i.dataset.page))}finally{n.isConnected&&(l.forEach(o=>o.disabled=!1),n.removeAttribute("aria-busy"))}})}let ct={},dt={},Ra=1;function N(t){return"RM"+Number(t).toLocaleString("en-MY")}function P(t){const a=String(t??""),n=a.toUpperCase();let i="gray";return n.includes("VERIFIED")||n.includes("PAID")||n==="CONVERTED"||n==="COMPLETED"||n.includes("BANK CHECKED")||n.includes("PROOF")?i="success":n.includes("FOLLOW")||n.includes("PENDING")||n.includes("REVIEW")||n==="BOOKING"||n==="CLAIMED"||n.includes("PROCESSING")||n.includes("DECLARED")?i="warning":n.includes("HOLD")||n.includes("LOST")||n.includes("NO RESPONSE")||n.includes("CANCEL")||n.includes("REFUND")?i="danger":n==="NEW"&&(i="blue"),`<span class="badge ${i}"><span class="status-dot"></span>${s(a)}</span>`}function de(t,a,n=""){return`<div class="page-head"><div><h1 class="page-title">${t}</h1><div class="page-subtitle">${a}</div></div><div class="page-actions">${n}</div></div>`}function x(t,a,n,i,l,o="blue",d=null,u="",p=""){const m=s(u||a),v=s(a),b=s(n),w=s(i),g=s(l),f=s(p),S=/^[↑+]/.test(String(i).trim())||/above|\+|up/i.test(String(i)),y=/^[↓-]/.test(String(i).trim())||/below|down/i.test(String(i))?"down":S?"up":"flat";return`<div class="kpi-card kpi-card--${o}">
    <div class="kpi-card__head">
      <div class="kpi-card__icon" aria-hidden="true">${t}</div>
      <div class="kpi-label" title="${m}">${v}</div>
    </div>
    <div class="kpi-value mono">${b}</div>
    <div class="kpi-card__foot">
      <span class="kpi-trend kpi-trend--${y}">${w}</span>
      <span class="kpi-target">${g}</span>
    </div>
    ${p?`<div class="kpi-spark" id="${f}"></div>`:""}
    ${d!==null?`<div class="progress kpi-card__bar"><span style="width:${Math.min(100,d)}%"></span></div>`:""}
  </div>`}function fe(t,a,n,i=""){const l=i?` trade-pane__chart--${i}`:"";return`<section class="trade-pane">
    <div class="trade-pane__head">
      <div>
        <div class="trade-pane__eyebrow">${e("trade.section")}</div>
        <div class="trade-pane__title">${e(t)}</div>
        <div class="trade-pane__sub">${e(a)}</div>
      </div>
    </div>
    <div class="trade-pane__chart${l}" id="${n}"></div>
  </section>`}function It(t=new Date){const a=["JAN","FEB","MAC","APR","MEI","JUN","JUL","OGOS","SEPT","OKT","NOV","DIS"];return`${String(t.getDate()).padStart(2,"0")} ${a[t.getMonth()]} ${t.getFullYear()}`}function Ft(){const t=L||{},a=t.kpis||{},n=t.targets||{},i=Array.isArray(t.beauticians)?t.beauticians:[],l=Number(a.new_buyers||0),o=Number(n.leads||0),d=o>0?Math.round(l/o*1e3)/10:0,u=!!(t.period&&t.period.key),p=t.period&&t.period.label?s(t.period.label):It(),m=t.slogan||e("daily.slogan"),v=t.quote||e("daily.quote");return`<section class="daily-hero">
    <div class="daily-hero__top">
      <div class="daily-hero__copy">
        <div class="daily-hero__eyebrow">${e("daily.eyebrow")}</div>
        <h2 class="daily-hero__title">${e("daily.title")}</h2>
        <div class="daily-hero__meta">
          <span class="daily-hero__date">${p}</span>
          <span class="daily-hero__pill">${e("daily.live")}</span>
        </div>
        <p class="daily-hero__quote">${s(m)} -- "${s(v)}"</p>
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
        <div class="daily-stat__sub">${e("daily.of_monthly_target",{pct:d})}</div>
        <div class="progress daily-stat__bar"><span style="width:${Math.min(100,d)}%"></span></div>
      </div>
    </div>
  </section>`}const Ua=[["#1d4ed8","#60a5fa"],["#9a3412","#f59e0b"],["#065f46","#34d399"],["#6d28d9","#a78bfa"],["#be123c","#fb7185"],["#0e7490","#22d3ee"],["#a16207","#facc15"]];function Ot(t){const a=String(t||"");let n=0;for(let l=0;l<a.length;l++)n=n*31+a.charCodeAt(l)>>>0;const i=Ua[Math.abs(n)%Ua.length];return`linear-gradient(145deg, ${i[0]}, ${i[1]})`}function Wt(t){const a=String(t||"").trim().split(/\s+/).filter(Boolean);return((a[0]?.[0]||"")+(a[1]?.[0]||"")).toUpperCase()}function Vt(){const t=L||{},a=!!(t.period&&t.period.key),n=Array.isArray(t.beauticians)?t.beauticians:[],i=Array.isArray(t.beauticians)?t.beauticians.length:0;Math.max(1,Number(t.beautician_count)||i||1);const l=Number(t.targets&&t.targets.beautician_leads||0)||112,o=[...n].sort((v,b)=>b.leads-v.leads),d=t.period&&t.period.label?s(t.period.label):e("common.this_month"),u=o.reduce((v,b)=>v+(Number(b.leads)||0),0),p=["🥇","🥈","🥉"],m=o.map((v,b)=>{const w=Number(v.leads)||0,g=Number(v.target)||l,f=g>0?Math.round(w/g*100):0,S=u>0?Math.max(4,Math.round(w/u*100)):0,M=b===0?"gold":b===1?"silver":b===2?"bronze":"",y=f>=100?"is-hit":f>=75?"is-close":"is-low",k=s(String(v.name||"")),j=b<3?`<span class="rank rank--medal rank--${M}" title="${e("daily.rank_title",{n:b+1})}">${p[b]}</span>`:`<span class="rank">${b+1}</span>`;return`<tr class="daily-row${M?` daily-row--${M}`:""}">
      <td class="daily-rank-cell">${j}</td>
      <td>
        <div class="person-cell">
          <div class="mini-avatar leader-avatar${M?` mini-avatar--${M}`:""}" style="background:${Ot(v.name)}">${Wt(v.name)}</div>
          <div class="person-cell__text">
            <strong>${k}${b===0?` <span class="leader-tag">${e("daily.leader")}</span>`:""}</strong>
            <small>${e("daily.target_month",{count:g})}<i class="daily-dot"></i>${d}</small>
          </div>
        </div>
      </td>
      <td class="daily-num-cell"><strong class="daily-num" title="${w.toLocaleString()} ${e("daily.leads")}">${w.toLocaleString()}</strong></td>
      <td>
        <div class="share-cell">
          <div class="share-cell__track"><span style="width:${Math.min(100,S)}%"></span></div>
          <strong>${S}%</strong>
        </div>
      </td>
      <td><span class="daily-target">${g}</span></td>
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
  </div>`}function ha(t,a,n,i,l,o){const d=Math.min(o,i/2,l/2);t.beginPath(),t.moveTo(a+d,n),t.arcTo(a+i,n,a+i,n+l,d),t.arcTo(a+i,n+l,a,n+l,d),t.arcTo(a,n+l,a,n,d),t.arcTo(a,n,a+i,n,d),t.closePath()}function pt(){const t=c("#dailyLeadChart");if(!t)return;De(t);const a=t.getContext("2d"),n=L||{},i=Array.isArray(n.beauticians)?n.beauticians:[],o=[...i.length?i.map(y=>({name:String(y.name||""),leads:Number(y.leads||0)})):[]].sort((y,k)=>k.leads-y.leads),d=o.map(y=>y.name),u=o.map(y=>y.leads),p=t.clientWidth,m=t.clientHeight,v={l:28,r:10,t:28,b:42},b=Math.max(...u,1)*1.2,w=Math.max(6,Math.min(12,p/60)),g=Math.max(14,(p-v.l-v.r-w*(u.length-1))/u.length),f=Z("--navy","#1d4ed8"),S=Z("--rose","#0ea5e9");a.fillStyle="#f8fafc",ha(a,0,0,p,m,12),a.fill(),a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let y=0;y<4;y++){const k=v.t+y*((m-v.t-v.b)/3);a.beginPath(),a.moveTo(v.l,k),a.lineTo(p-v.r,k),a.stroke()}const M=[["#1d4ed8","#38bdf8"],["#2563eb","#7dd3fc"],["#0284c7","#67e8f9"]];u.forEach((y,k)=>{const j=v.l+k*(g+w),A=Math.max(4,y/b*(m-v.t-v.b)),pe=m-v.b-A,[F,Dt]=M[Math.min(k,2)]||[f,S],ta=a.createLinearGradient(0,pe,0,m-v.b);ta.addColorStop(0,k<3?Dt:S),ta.addColorStop(1,k<3?F:"#93c5fd"),a.fillStyle=ta,ha(a,j,pe,g,A,8),a.fill(),a.fillStyle="#0f172a",a.font="700 12px Poppins",a.textAlign="center",a.fillText(String(y),j+g/2,pe-8);const Rt=d[k].length>7?d[k].slice(0,6)+"…":d[k];a.fillStyle="#475569",a.font="600 10px Poppins",a.fillText(Rt,j+g/2,m-14)})}function ut(){const t=c("#dailyTrendChart");if(!t)return;De(t);const a=t.getContext("2d"),n=L||{},i=n.leads_trend&&Array.isArray(n.leads_trend.actual)?n.leads_trend:null,l=i?[...i.actual]:[],o=i&&Array.isArray(i.labels)?i.labels:Array.from({length:l.length},(S,M)=>M===l.length-1?"Today":"D-"+(l.length-1-M)),d=t.clientWidth,u=t.clientHeight,p={l:28,r:14,t:22,b:28},m=Math.max(...l,1)*1.15,v=S=>p.l+S*((d-p.l-p.r)/Math.max(l.length-1,1)),b=S=>u-p.b-S/m*(u-p.t-p.b),w=Z("--navy","#1d4ed8"),g=Z("--rose","#0ea5e9");a.fillStyle="#f8fafc",ha(a,0,0,d,u,12),a.fill(),a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let S=0;S<3;S++){const M=p.t+S*((u-p.t-p.b)/2);a.beginPath(),a.moveTo(p.l,M),a.lineTo(d-p.r,M),a.stroke()}const f=a.createLinearGradient(0,p.t,0,u-p.b);f.addColorStop(0,"rgba(14,165,233,.28)"),f.addColorStop(1,"rgba(37,99,235,.02)"),a.beginPath(),a.moveTo(v(0),u-p.b),l.forEach((S,M)=>a.lineTo(v(M),b(S))),a.lineTo(v(l.length-1),u-p.b),a.closePath(),a.fillStyle=f,a.fill(),a.beginPath(),l.forEach((S,M)=>M?a.lineTo(v(M),b(S)):a.moveTo(v(M),b(S))),a.strokeStyle=w,a.lineWidth=2.75,a.lineJoin="round",a.lineCap="round",a.stroke(),l.forEach((S,M)=>{const y=v(M),k=b(S);a.beginPath(),a.arc(y,k,5,0,Math.PI*2),a.fillStyle="#fff",a.fill(),a.lineWidth=2.5,a.strokeStyle=g,a.stroke(),a.beginPath(),a.arc(y,k,2.2,0,Math.PI*2),a.fillStyle=w,a.fill(),a.fillStyle="#0f172a",a.font="700 10px Poppins",a.textAlign="center",a.fillText(String(S),y,k-10),a.fillStyle="#64748b",a.font="600 10px Poppins",a.fillText(o[M],y,u-10)})}function Yt(){const t="company_target",a=L||{},n=a.targets||{},i=Math.max(1,Number(a.beautician_count)||1),l=Number(n.leads)||0,o=Number(n.conv_pct)||0,d=Number(n.buyers)||0,u=Number(n.avg_sale)||0,p=Number(n.sales)||0,m=Number(n.beautician_leads)||112,v=Math.round(m*o/100),b=Math.round(v*u),w=k=>{const j=Number(k||0);return Math.abs(j)>=1e6?"RM"+(j/1e6).toFixed(1).replace(/\.0$/,"")+"M":Math.abs(j)>=1e3?"RM"+(j/1e3).toFixed(1).replace(/\.0$/,"")+"k":C(j)},g=h(l),f=W(o),S=h(d),M=C(u),y=w(p);return`<section class="company-target">
    <div class="company-target__hero">
      <div class="company-target__hero-copy">
        <div class="company-target__eyebrow">${e(t+".eyebrow")}</div>
        <h2 class="company-target__title">${e(t+".title",{leadgoal:g})}</h2>
        <p class="company-target__desc">${e(t+".desc",{leadgoal:g,convgoal:f,buyergoal:S,avggoal:M,salesgoal:y})}</p>
      </div>
      <div class="company-target__hero-goal">
        <div class="company-target__goal-label">${e(t+".sales_goal")}</div>
        <div class="company-target__goal-value">${C(p)}</div>
        <div class="company-target__goal-sub">${e(t+".goal_sub",{beaucount:i})}</div>
      </div>
    </div>

    <div class="company-target__chain" aria-label="${e(t+".chain_aria")}">
      ${[[g,e(t+".step_leads"),e(t+".step_leads_meta")],[f,e(t+".step_conv"),e(t+".step_conv_meta")],[S,e(t+".step_buyers"),e(t+".step_buyers_meta")],[M,e(t+".step_avg"),e(t+".step_avg_meta")],[y,e(t+".step_sales"),e(t+".step_sales_meta")]].map((k,j)=>`
        ${j?'<div class="company-target__arrow" aria-hidden="true">↓</div>':""}
        <div class="company-target__step ${j===4?"company-target__step--goal":""}">
          <div class="company-target__step-value">${k[0]}</div>
          <div class="company-target__step-label">${k[1]}</div>
          <div class="company-target__step-meta">${k[2]}</div>
        </div>
      `).join("")}
    </div>

    <div class="grid company-target__kpis">
      <article class="ct-card ct-card--kpi1">
        <div class="ct-card__head">
          <span class="ct-card__badge">${e(t+".kpi1_badge")}</span>
          <span class="ct-card__icon">🎯</span>
        </div>
        <h3 class="ct-card__title">${e(t+".kpi1_title",{convgoal:f})}</h3>
        <p class="ct-card__text">${e(t+".kpi1_text",{leadgoal:g,buyergoal:S})}</p>
        <div class="ct-card__math">
          <div><span>${e(t+".kpi1_leads")}</span><strong>${g}</strong></div>
          <div><span>×</span><strong>${f}</strong></div>
          <div><span>${e(t+".kpi1_buyers")}</span><strong>${S}</strong></div>
        </div>
      </article>

      <article class="ct-card ct-card--kpi2">
        <div class="ct-card__head">
          <span class="ct-card__badge">${e(t+".kpi2_badge")}</span>
          <span class="ct-card__icon">💰</span>
        </div>
        <h3 class="ct-card__title">${e(t+".kpi2_title",{avggoal:M})}</h3>
        <p class="ct-card__text">${e(t+".kpi2_text",{buyergoal:S,avggoal:M,salesgoal:y})}</p>
        <div class="ct-card__math">
          <div><span>${e(t+".kpi2_buyers")}</span><strong>${S}</strong></div>
          <div><span>×</span><strong>${M}</strong></div>
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
        <div class="ct-card__big">${h(m)}</div>
        <div class="ct-card__unit">${e(t+".unit_leads")}</div>
        <p class="ct-card__text">${e(t+".text_leads",{leadgoal:g,beaucount:i,leadtarget:h(m)})}</p>
      </article>
      <article class="ct-card">
        <div class="ct-card__head"><span class="ct-card__badge">${e(t+".badge_convert")}</span></div>
        <div class="ct-card__big">±${h(v)}</div>
        <div class="ct-card__unit">${e(t+".unit_convert")}</div>
        <p class="ct-card__text">${e(t+".text_convert",{leadtarget:h(m),convgoal:f,buytarget:h(v)})}</p>
      </article>
      <article class="ct-card ct-card--accent">
        <div class="ct-card__head"><span class="ct-card__badge">${e(t+".badge_sales")}</span></div>
        <div class="ct-card__big">${w(b)}</div>
        <div class="ct-card__unit">${e(t+".unit_sales")}</div>
        <p class="ct-card__text">${e(t+".text_sales",{buytarget:h(v),avggoal:M,salarget:C(b),beaucount:i,compact:w(b),total:y})}</p>
      </article>
    </div>
  </section>`}function mt(){window.IMMA_TRADE&&IMMA_TRADE.dispose();const t=L||{},a=t.kpis||{},n=a.vs_prev||{},i=t.targets||{},l=t.dual||{},o=Number(l.sales_pct||0),d=Number(i.sales||0),u=Number(a.sales||0),p=u-d,m=s(t.period&&t.period.label||e("common.this_month")),v=t.ops||{},b=v.checkin||{},w=v.clearance||{},g=v.payments||{},f=e("ops.value_customers",{count:h(b.live||0)}),S=e("ops.meta_checkin",{waiting:h(b.waiting||0),treatment:h(b.in_treatment||0)}),M=e("ops.value_customers",{count:h(w.queue||0)}),y=e("ops.meta_clearance",{waiting:h(w.waiting||0),blocked:h(w.blocked||0)}),k=e("ops.value_pending",{count:h(g.queue||0)}),j=e("ops.meta_payments",{processing:h(g.processing||0),hold:h(g.hold||0)});B.innerHTML=`${de(e("overview.title"),e("overview.subtitle"),`<button class="btn soft">${m}</button><button class="btn primary" data-jump="leads">${e("common.open_leads")}</button>`)}
  ${Yt()}
  <div class="trade-grid trade-grid--2">
    ${fe("trade.dual_title","trade.dual_sub","tradeDualRing","sm")}
    ${fe("trade.equity_title","trade.equity_sub","tradeEquity","lg")}
  </div>
  <div class="section-label">${e("overview.actual_label")}</div>
  <div class="grid kpi-grid">
    ${x("♙",e("overview.kpi_unique_leads"),h(a.new_buyers),Y(n.new_buyers,"%"),Q(e("overview.kpi_unique_leads"),h(i.leads||0)),"rose",Math.min(100,(a.new_buyers||0)/Math.max(1,i.leads||0)*100),"","sparkLeads")}
    ${x("▣",e("overview.kpi_buyers"),h(a.buyers),Y(n.buyers,"%"),Q(e("overview.kpi_buyers"),h(i.buyers||0)),"blue",Math.min(100,(a.buyers||0)/Math.max(1,i.buyers||0)*100),"","sparkBuyers")}
    ${x("%",e("overview.kpi_conv_rate"),W(a.new_buyer_share_pct),Y(n.new_buyer_share_pct,"pp"),Q(e("overview.kpi_conv_rate"),W(i.conv_pct||0)),"green",Math.min(100,Number(a.new_buyer_share_pct||0)),"","sparkConv")}
    ${x("◫",e("overview.kpi_sales"),C(a.sales),Y(n.sales,"%"),Q(e("overview.kpi_sales"),C(d)),"rose",Math.min(100,o),"","sparkSales")}
    ${x("▥",e("overview.kpi_avg_sale"),C(a.avg_sale),Y(n.avg_sale,"%"),Q(e("overview.kpi_avg_sale"),C(i.avg_sale||0)),"purple",Math.min(100,(a.avg_sale||0)/Math.max(1,i.avg_sale||0)*100),"","sparkAvg")}
  </div>
  <div class="trade-grid trade-grid--2" style="margin-top:12px">
    ${fe("trade.waterfall_title","trade.waterfall_sub","tradeWaterfall")}
    <section class="card">
      <div class="card-title-row"><div class="card-title">▥ ${e("overview.lead_status")}</div><button class="btn small soft">${m}</button></div>
      <div style="display:grid;grid-template-columns:180px 1fr;gap:14px;align-items:center">
        <div class="chart-wrap" style="height:180px"><canvas id="donutChart"></canvas></div>
        <div class="legend" id="leadLegend"></div>
      </div>
    </section>
  </div>
  <div class="trade-grid trade-grid--2">
    ${fe("trade.scatter_title","trade.scatter_sub","tradeScatter")}
    ${fe("trade.heatmap_title","trade.heatmap_sub","tradeHeatmap")}
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
            <strong>${W(o)}</strong>
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

  <section class="card" style="margin-top:12px"><div class="card-title-row"><div class="card-title">♙ ${e("overview.beauticians")}</div><button class="btn small primary" data-jump="beauticians">${e("common.view_all")}</button></div>${Kt()}</section>

  <div class="grid three-col" style="margin-top:12px">
    ${(t.branches||[]).slice(0,6).map(A=>vt(A.name,A.new_buyers||0,A.buyers||0,A.conv||0,A.sales||0,A.avg||0,A.buyers||0)).join("")||`<section class="card"><div class="empty"><strong>${e("overview.no_branch_data")}</strong></div></section>`}
  </div>

  <div class="grid op-row" style="margin-top:12px">
    ${na("♧",e("ops.checkin"),f,S,"checkin",e("ops.checkin"),"green")}
    ${na("◷",e("ops.clearance"),M,y,"clearance",e("ops.clearance"),"warning")}
    ${na("▣",e("ops.payments"),k,j,"payments",e("ops.payments"),"rose")}
  </div>
  <div style="margin-top:12px">${Ft()}</div>
  <div style="margin-top:12px">${Vt()}</div>`,requestAnimationFrame(()=>{if(pt(),ut(),Et(),Ze(),X(),window.IMMA_TRADE){const A=Object.assign({},t.ticker||{}),pe={leadsUp:Number(n.new_buyers||0)>=0,convUp:Number(n.new_buyer_share_pct||0)>=0,salesUp:Number(n.sales||0)>=0,targetUp:o>=100,avgUp:Number(n.avg_sale||0)>=0};IMMA_TRADE.render({ticker:Object.assign(A,pe),leadsPct:Number(l.buyers_pct||l.leads_pct||0),salesPct:o,equityActual:t.equity&&t.equity.actual||[],equityTarget:t.equity&&t.equity.target_path||[],equityLabels:t.equity&&t.equity.labels||[],waterfall:(t.waterfall||t.status_mix||[]).map(F=>({name:String(F.name||""),value:F.value})),beauticians:(t.beauticians||[]).map(F=>({name:String(F.name||""),leads:F.leads||0,conv:F.conv||0,sales:F.sales||0})),heatmap:t.heatmap||[],sparks:t.sparks||[]})}})}function Kt(){const t=Array.isArray(L?.beauticians)?L.beauticians:[];return t.length?`<div class="table-wrap"><table class="data-table"><thead><tr><th>#</th><th>${e("overview.col_beautician")}</th><th title="${e("overview.kpi_unique_leads")}">${e("overview.unique")}</th><th>${e("overview.kpi_buyers")}</th><th title="${e("overview.kpi_conv_rate")}">${e("overview.conv_rate")}</th><th>${e("overview.kpi_sales")}</th><th>${e("overview.kpi_avg_sale")}</th><th>${e("overview.col_orders")}</th></tr></thead><tbody>${t.map((a,n)=>{const i=s(a.name);return`<tr><td><span class="rank">${n+1}</span></td><td><strong>${i}</strong></td><td>${h(a.leads)}</td><td>${h(a.buyers)}</td><td style="color:${a.conv>=40?"var(--success)":a.conv<35?"var(--danger)":"#b16e10"};font-weight:700">${W(a.conv)}</td><td>${C(a.sales)}</td><td>${C(a.avg)}</td><td>${h(a.orders||0)}</td></tr>`}).join("")}</tbody></table></div>`:`<div class="empty"><strong>${e("overview.no_beautician_data")}</strong></div>`}function vt(t,a,n,i,l,o,d){const u=i>=40?"good":i>=35?"ok":"low",p=s(t),m=s(String(t||"").slice(0,2).toUpperCase());return`<section class="card branch-card branch-card--${u}">
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
        <div class="branch-card__sales-value">${N(l)}</div>
      </div>
    </div>
    <div class="branch-card__bar" aria-hidden="true"><span style="width:${Math.min(100,i)}%"></span></div>
    <div class="branch-card__metrics">
      <div class="branch-metric"><span title="${e("overview.unique")}">${e("overview.unique")}</span><strong>${a.toLocaleString()}</strong></div>
      <div class="branch-metric"><span>${e("overview.converted")}</span><strong>${n.toLocaleString()}</strong></div>
      <div class="branch-metric"><span title="${e("overview.kpi_avg_sale")}">${e("overview.kpi_avg_sale")}</span><strong>${N(o)}</strong></div>
      <div class="branch-metric"><span title="${e("overview.treat_done")}">${e("overview.treat_done")}</span><strong>${d.toLocaleString()}</strong></div>
    </div>
  </section>`}function na(t,a,n,i,l,o,d){return`<section class="card op-card"><div class="op-icon" style="background:${d==="green"?"var(--success-soft)":d==="warning"?"var(--warning-soft)":"var(--rose-soft)"}">${t}</div><div class="op-body"><div class="op-title">${a}</div><div class="op-value">${n}</div><div class="op-meta">${i}</div></div><button class="btn small primary" data-jump="${l}">${o} →</button></section>`}function Ia(t){(!r.leadMonth||r.leadMonth==="all")&&(r.leadMonth=ea());const[a,n]=r.leadMonth.split("-").map(Number),i=new Date(a,n-1+t,1);r.leadMonth=i.getFullYear()+"-"+String(i.getMonth()+1).padStart(2,"0"),r.leadPage=1,q().then(()=>He())}function Ne(t){if(!t||t==="all")return null;const[a,n]=String(t).split("-").map(Number);return!a||!n?null:{y:a,m:n}}function ht(){const t=(_.locale||"en").toLowerCase().startsWith("ms")?"ms-MY":"en-GB";return Array.from({length:12},(a,n)=>new Date(2e3,n,1).toLocaleString(t,{month:"short"}))}function _t(t){if(!t||t==="all")return e("workspace.all_months");const a=Ne(t);if(!a)return String(t);const i=(E.months||[]).find(l=>String(l.value)===String(t));return i?i.label:ht()[a.m-1]+" "+a.y}function ea(){const t=new Date;return t.getFullYear()+"-"+String(t.getMonth()+1).padStart(2,"0")}function zt(){const t=Ne(r.leadMonth)||Ne(ea()),a=r.leadCalYear||t.y,n=!!_.canCreateLead;return`
    <div class="lead-cal" id="leadCalendar">
      <button type="button" class="lead-cal__nav" id="leadMonthPrev" title="${s(e("workspace.month_prev"))}" aria-label="${s(e("workspace.month_prev"))}">‹</button>
      <button type="button" class="lead-cal__toggle" id="leadCalToggle" aria-expanded="false" aria-haspopup="dialog">
        <span class="lead-cal__icon" aria-hidden="true">▦</span>
        <span id="leadMonthLabel">${s(_t(r.leadMonth))}</span>
      </button>
      <button type="button" class="lead-cal__nav" id="leadMonthNext" title="${s(e("workspace.month_next"))}" aria-label="${s(e("workspace.month_next"))}">›</button>
      <div class="lead-cal__panel hidden" id="leadCalPanel" role="dialog" aria-label="${s(e("workspace.month_label"))}">
        <div class="lead-cal__year-row">
          <button type="button" class="lead-cal__nav" id="leadCalYearPrev" aria-label="${s(e("workspace.year_prev"))}">‹</button>
          <strong id="leadCalYearLabel">${a}</strong>
          <button type="button" class="lead-cal__nav" id="leadCalYearNext" aria-label="${s(e("workspace.year_next"))}">›</button>
        </div>
        <div class="lead-cal__grid" id="leadCalGrid"></div>
        <div class="lead-cal__footer">
          <button type="button" class="btn soft small" id="leadCalAll">${s(e("workspace.all_months"))}</button>
          <button type="button" class="btn soft small" id="leadMonthThis">${s(e("workspace.this_month"))}</button>
        </div>
      </div>
    </div>
    ${n?`
      <button type="button" class="btn" data-jump="import" data-import-tab="paste">${s(e("workspace.paste_leads"))}</button>
      <button type="button" class="btn" data-jump="import" data-import-tab="excel">${s(e("workspace.upload_excel"))}</button>
      <button type="button" class="btn primary" data-jump="import" data-import-tab="paste">${s(e("workspace.import_leads"))}</button>
    `:""}
  `}function Ge(){const t=c("#leadCalGrid"),a=c("#leadCalYearLabel");if(!t)return;const n=Ne(r.leadMonth),i=r.leadCalYear||(n?n.y:new Date().getFullYear());r.leadCalYear=i,a&&(a.textContent=String(i));const l=ht(),o=ea();t.innerHTML=l.map((d,u)=>{const p=`${i}-${String(u+1).padStart(2,"0")}`;return`<button type="button" class="lead-cal__month${String(r.leadMonth)===p?" is-selected":""}${p===o?" is-now":""}" data-month="${p}">${s(d)}</button>`}).join(""),T("[data-month]",t).forEach(d=>{d.onclick=()=>{r.leadMonth=d.dataset.month,r.leadPage=1,ne(),He(),q()}})}function Gt(){const t=c("#leadCalPanel"),a=c("#leadCalToggle");if(!t||!a)return;const n=Ne(r.leadMonth);r.leadCalYear=n?n.y:new Date().getFullYear(),t.classList.remove("hidden"),a.setAttribute("aria-expanded","true"),Ge()}function ne(){const t=c("#leadCalPanel"),a=c("#leadCalToggle");t&&t.classList.add("hidden"),a&&a.setAttribute("aria-expanded","false")}function He(){const t=c("#leadMonthLabel");t&&(t.textContent=_t(r.leadMonth)),c("#leadCalPanel")&&!c("#leadCalPanel").classList.contains("hidden")&&Ge()}function Fa(t){const a=c("#leadCalendar");!a||a.contains(t.target)||ne()}function Jt(){c("#leadMonthPrev")&&(c("#leadMonthPrev").onclick=()=>{ne(),Ia(-1)}),c("#leadMonthNext")&&(c("#leadMonthNext").onclick=()=>{ne(),Ia(1)}),c("#leadCalToggle")&&(c("#leadCalToggle").onclick=t=>{t.stopPropagation();const a=c("#leadCalPanel");a&&a.classList.contains("hidden")?Gt():ne()}),c("#leadCalYearPrev")&&(c("#leadCalYearPrev").onclick=t=>{t.stopPropagation(),r.leadCalYear=(r.leadCalYear||new Date().getFullYear())-1,Ge()}),c("#leadCalYearNext")&&(c("#leadCalYearNext").onclick=t=>{t.stopPropagation(),r.leadCalYear=(r.leadCalYear||new Date().getFullYear())+1,Ge()}),c("#leadMonthThis")&&(c("#leadMonthThis").onclick=t=>{t.stopPropagation(),r.leadMonth=ea(),r.leadPage=1,ne(),He(),q()}),c("#leadCalAll")&&(c("#leadCalAll").onclick=t=>{t.stopPropagation(),r.leadMonth="all",r.leadPage=1,ne(),He(),q()}),document.removeEventListener("click",Fa),document.addEventListener("click",Fa)}function Xt(){const t=!!_.canCreateLead,a=zt();B.innerHTML=`${de(e("workspace.title"),e("workspace.subtitle"),a)}
  <div class="grid kpi-grid" id="leadKpiMount"></div>
  <p class="card-subtitle" style="margin:8px 2px 0">${s(e("workspace.kpi_note"))}</p>
  <section class="lead-panel card" style="margin-top:14px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${s(e("workspace.list_title"))}</h2>
        <p class="lead-panel__sub">${s(e("workspace.list_subtitle"))}</p>
      </div>
      <div class="lead-panel__head-meta">
        <span class="lead-panel__count" id="leadResultCount">—</span>
        ${t?`<button class="btn primary" type="button" id="addLeadBtn">${s(e("workspace.add_lead"))}</button>`:""}
      </div>
    </div>
    <div class="lead-panel__filters">
      <label class="lead-search" for="leadSearch">
        <span class="lead-search__icon" aria-hidden="true">⌕</span>
        <input class="lead-search__input" id="leadSearch" type="search" autocomplete="off" placeholder="${s(e("workspace.search_placeholder"))}" value="${s(r.leadSearch)}" />
      </label>
      <div class="lead-filter-grid">
        <label class="lead-field">
          <span class="lead-field__label">${s(e("workspace.filter_status"))}</span>
          <select class="lead-field__control" id="leadStatus"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${s(e("workspace.filter_beautician"))}</span>
          <select class="lead-field__control" id="leadBeautician"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${s(e("workspace.filter_branch"))}</span>
          <select class="lead-field__control" id="leadBranchFilter"></select>
        </label>
      </div>
    </div>
    <div class="lead-panel__chips" id="leadActiveFilters" hidden></div>
    <div class="lead-panel__body" id="leadTableMount"></div>
  </section>`,bt(),Je(),Jt(),X(),c("#addLeadBtn")&&(c("#addLeadBtn").onclick=$t),q()}function bt(){const t=c("#leadKpiMount");if(!t)return;const a=ue||{},n=Number(a.raw||0),i=Number(a.unique||0),l=Number(a.duplicates||0),o=Number(a.existing||0),d=Number(a.converted||0),u=Number(a.conversion_pct||0),p=n>0?(i/n*100).toFixed(1):"0.0",m=n>0?(l/n*100).toFixed(1):"0.0";t.innerHTML=`
    ${x("▤",e("workspace.raw_leads"),h(n),"—","—","blue")}
    ${x("♙",e("workspace.unique_leads"),h(i),e("workspace.clean_rate",{pct:p}),"—","green")}
    ${x("⧉",e("workspace.duplicates"),h(l),e("workspace.of_raw",{pct:m}),"—","rose")}
    ${x("♧",e("workspace.existing"),h(o),e("workspace.matched_phone"),"—","purple")}
    ${x("%",e("workspace.conversion"),W(u),e("workspace.converted_customers",{count:h(d)}),e("workspace.target_conv"),"green")}
  `}function Je(){const t=c("#leadStatus"),a=c("#leadBeautician"),n=c("#leadBranchFilter");if(t){const l=[{value:"all",label:e("workspace.all_status")},...E.statuses||[]];t.innerHTML=l.map(o=>`<option value="${s(o.value)}" ${String(r.leadStatus)===String(o.value)?"selected":""}>${s(o.label)}</option>`).join(""),t.onchange=o=>{r.leadStatus=o.target.value,r.leadPage=1,q()}}if(a){const l=[{id:"all",name:e("workspace.all_beauticians")},...E.beauticians||[]];a.innerHTML=l.map(o=>`<option value="${s(o.id)}" ${String(r.leadBeautician)===String(o.id)?"selected":""}>${s(o.name)}</option>`).join(""),a.onchange=o=>{r.leadBeautician=o.target.value,r.leadPage=1,q()}}if(n){const l=[{id:"all",name:e("workspace.all_branches")},...E.branches||_.branches||[]];n.innerHTML=l.map(o=>`<option value="${s(o.id)}" ${String(r.leadBranch)===String(o.id)?"selected":""}>${s(o.name)}</option>`).join(""),n.onchange=o=>{r.leadBranch=o.target.value,r.leadPage=1,q()}}const i=c("#leadSearch");i&&(i.oninput=l=>{r.leadSearch=l.target.value,clearTimeout(Ba),Ba=setTimeout(()=>{r.leadPage=1,q()},350)}),gt()}function ia(t,a){if(t==="status"){const n=(E.statuses||[]).find(i=>String(i.value)===String(a));return n?n.label:a}if(t==="beautician"){const n=(E.beauticians||[]).find(i=>String(i.id)===String(a));return n?n.name:a}if(t==="branch"){const i=(E.branches||_.branches||[]).find(l=>String(l.id)===String(a));return i?i.name:a}return a}function gt(){const t=c("#leadResultCount"),a=Array.isArray(ie)?ie.length:ue&&ue.unique!=null?Number(ue.unique):0;t&&(t.textContent=a===1?e("workspace.results_count_one"):e("workspace.results_count",{count:h(a)}));const n=c("#leadActiveFilters");if(!n)return;const i=[];if(r.leadSearch&&String(r.leadSearch).trim()&&i.push({key:"q",label:`“${String(r.leadSearch).trim()}”`}),r.leadStatus&&r.leadStatus!=="all"&&i.push({key:"status",label:ia("status",r.leadStatus)}),r.leadBeautician&&r.leadBeautician!=="all"&&i.push({key:"beautician",label:ia("beautician",r.leadBeautician)}),r.leadBranch&&r.leadBranch!=="all"&&i.push({key:"branch",label:ia("branch",r.leadBranch)}),!i.length){n.hidden=!0,n.innerHTML="";return}n.hidden=!1,n.innerHTML=`
    <div class="lead-chips">
      ${i.map(l=>`<span class="lead-chip">${s(l.label)}<button type="button" class="lead-chip__x" data-clear-filter="${s(l.key)}" aria-label="${s(e("workspace.clear_filters"))}">×</button></span>`).join("")}
      <button type="button" class="lead-chips__clear" id="leadClearFilters">${s(e("workspace.clear_filters"))}</button>
    </div>
  `,T("[data-clear-filter]",n).forEach(l=>{l.onclick=()=>{const o=l.dataset.clearFilter;o==="q"&&(r.leadSearch=""),o==="status"&&(r.leadStatus="all"),o==="beautician"&&(r.leadBeautician="all"),o==="branch"&&(r.leadBranch="all"),r.leadPage=1;const d=c("#leadSearch");d&&o==="q"&&(d.value=""),Je(),q()}}),c("#leadClearFilters")&&(c("#leadClearFilters").onclick=()=>{r.leadSearch="",r.leadStatus="all",r.leadBeautician="all",r.leadBranch="all",r.leadPage=1;const l=c("#leadSearch");l&&(l.value=""),Je(),q()})}function Oa(){const t=c("#leadTableMount");if(!t)return;if(gt(),Ve){t.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${s(e("workspace.loading"))}</strong></div>`;return}const a=ie;if(!a.length){const n=!!_.canCreateLead;t.innerHTML=`<div class="lead-empty">
      <div class="lead-empty__icon" aria-hidden="true">◎</div>
      <strong>${s(e("workspace.no_leads"))}</strong>
      <p>${s(e("workspace.no_leads_hint"))}</p>
      ${n?`<button type="button" class="btn primary" id="emptyAddLeadBtn">${s(e("workspace.empty_cta"))}</button>`:""}
    </div>`,c("#emptyAddLeadBtn")&&(c("#emptyAddLeadBtn").onclick=$t);return}t.innerHTML=`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${s(e("workspace.col_lead_id"))}</th><th>${s(e("workspace.col_date"))}</th><th>${s(e("workspace.col_customer"))}</th>
    <th>${s(e("workspace.col_phone"))}</th><th>${s(e("workspace.col_email"))}</th><th>${s(e("workspace.col_source"))}</th>
    <th>${s(e("workspace.col_beautician"))}</th><th>${s(e("workspace.col_branch"))}</th><th>${s(e("workspace.col_status"))}</th>
    <th>${s(e("workspace.col_payment"))}</th><th class="is-num">${s(e("workspace.col_sales"))}</th><th title="${s(e("workspace.col_last_fu"))}">${s(e("workspace.col_last_fu"))}</th>
    <th class="lead-table__actions"><span class="sr-only">${s(e("workspace.col_action"))}</span></th>
  </tr></thead><tbody>${a.map(n=>{const i=String(n.name||""),l=s((i[0]||"?").toUpperCase()),o=!!_.canEditLead,d=!!_.canDeleteLead,u=String(n.email||"").trim(),p=String(n.source||"").trim(),m=String(n.beautician||"").trim(),v=String(n.branch||"").trim(),b=String(n.last||"").trim(),w=Number(n.sales||0);return`<tr>
      <td><span class="lead-code">${s(n.code||n.id)}</span></td>
      <td><span class="lead-date">${s(n.date||"—")}</span></td>
      <td>
        <div class="person-cell person-cell--lead">
          <div class="mini-avatar" aria-hidden="true">${l}</div>
          <div class="person-cell__text">
            <strong>${s(i||"—")}</strong>
            ${n.customer?`<small>${s(n.customer)}</small>`:""}
          </div>
        </div>
      </td>
      <td><span class="lead-mono">${s(n.phone||"—")}</span></td>
      <td>${u?`<span class="lead-email" title="${s(u)}">${s(u)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td>${p?`<span class="lead-tag">${s(p)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td>${m?s(m):'<span class="lead-muted">—</span>'}</td>
      <td>${v?`<span class="lead-branch">${s(v)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td>${P(n.status)}</td>
      <td>${P(n.payment)}</td>
      <td class="is-num"><span class="lead-money${w?"":" is-zero"}">${w?N(w):"RM0"}</span></td>
      <td><span class="lead-date">${b?s(b):"—"}</span></td>
      <td class="lead-table__actions">
        <div class="lead-menu">
          <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${s(e("workspace.row_actions"))}">
            <span class="lead-menu__dots" aria-hidden="true"></span>
          </button>
          <div class="lead-menu__panel" role="menu" hidden>
            <button type="button" class="lead-menu__item" role="menuitem" data-lead-id="${s(n.id)}">${s(e("workspace.view"))}</button>
            ${o?`<button type="button" class="lead-menu__item" role="menuitem" data-lead-edit="${s(n.id)}">${s(e("workspace.edit"))}</button>`:""}
            ${d?`<button type="button" class="lead-menu__item lead-menu__item--danger" role="menuitem" data-lead-del="${s(n.id)}">${s(e("workspace.delete"))}</button>`:""}
          </div>
        </div>
      </td>
    </tr>`}).join("")}</tbody></table></div>`,t.insertAdjacentHTML("beforeend",G("leads",ct)),J("leads",n=>(r.leadPage=n,q())),ft(t),T("[data-lead-id]",t).forEach(n=>n.onclick=()=>{K(),aa(n.dataset.leadId)}),T("[data-lead-edit]",t).forEach(n=>n.onclick=()=>{K(),wt(n.dataset.leadEdit)}),T("[data-lead-del]",t).forEach(n=>n.onclick=()=>{K(),St(n.dataset.leadDel)})}function yt(t){t&&(t.classList.remove("is-up"),t.style.top="",t.style.left="",t.style.right="",t.style.bottom="")}function Qt(t,a){if(!t||!a)return;const n=4,i=t.getBoundingClientRect();a.style.top="0px",a.style.left="0px",a.style.right="auto",a.style.bottom="auto";const l=a.getBoundingClientRect(),d=window.innerHeight-i.bottom<l.height+n+8;a.classList.toggle("is-up",d);let u=d?i.top-l.height-n:i.bottom+n,p=i.right-l.width;p=Math.max(8,Math.min(p,window.innerWidth-l.width-8)),u=Math.max(8,Math.min(u,window.innerHeight-l.height-8)),a.style.top=`${Math.round(u)}px`,a.style.left=`${Math.round(p)}px`}function K(t=null){T(".lead-menu").forEach(a=>{if(t&&a===t)return;const n=c(".lead-menu__btn",a),i=c(".lead-menu__panel",a);n&&n.setAttribute("aria-expanded","false"),i&&(i.hidden=!0,yt(i)),a.classList.remove("is-open")})}function Wa(t){t.target.closest&&t.target.closest(".lead-menu")||K()}function Va(t){t.key==="Escape"&&K()}function Fe(){K()}function ft(t){T("[data-lead-menu]",t).forEach(a=>{a.onclick=n=>{n.stopPropagation();const i=a.closest(".lead-menu"),l=c(".lead-menu__panel",i),o=a.getAttribute("aria-expanded")==="true";K(o?null:i),o?(a.setAttribute("aria-expanded","false"),l&&(l.hidden=!0,yt(l)),i.classList.remove("is-open")):(a.setAttribute("aria-expanded","true"),l&&(l.hidden=!1,Qt(a,l)),i.classList.add("is-open"))}}),document.removeEventListener("click",Wa),document.addEventListener("click",Wa),document.removeEventListener("keydown",Va),document.addEventListener("keydown",Va),window.removeEventListener("scroll",Fe,!0),window.addEventListener("scroll",Fe,!0),window.removeEventListener("resize",Fe),window.addEventListener("resize",Fe)}function $t(){$e=null,se(e("workspace.manual_entry"),e("workspace.manual_sub"),kt({}),`<button class="btn" type="button" data-action-drawer-close>${s(e("workspace.cancel"))}</button><button class="btn primary" type="button" id="saveLead">${s(e("workspace.save"))}</button>`,e("workspace.title"))}function wt(t){const a=ie.find(n=>String(n.id)===String(t));if(!a){aa(t);return}$e=a.id,se(e("workspace.edit_entry"),e("workspace.edit_sub"),kt(a),`<button class="btn" type="button" data-action-drawer-close>${s(e("workspace.cancel"))}</button><button class="btn primary" type="button" id="saveLead">${s(e("workspace.save"))}</button>`,e("workspace.title"))}function kt(t={}){const a=(E.statuses||[]).map(o=>`<option value="${s(o.value)}" ${String(t.status_key||"")===String(o.value)?"selected":""}>${s(o.label)}</option>`).join(""),n=[{id:"",name:"—"},...E.branches||[]].map(o=>`<option value="${s(o.id)}" ${String(t.branch_id||"")===String(o.id)?"selected":""}>${s(o.name)}</option>`).join(""),i=[{id:"",name:"—"},...E.beauticians||[]].map(o=>`<option value="${s(o.id)}" ${String(t.beautician_id||"")===String(o.id)?"selected":""}>${s(o.name)}</option>`).join(""),l=String(t.source||"manual");return`<div class="detail-grid">
      <div><label class="kpi-label">${s(e("workspace.name"))}</label><input class="search" style="width:100%" id="mName" autocomplete="name" value="${s(t.name||"")}"></div>
      <div><label class="kpi-label">${s(e("workspace.phone"))}</label><input class="search" style="width:100%" id="mPhone" autocomplete="tel" value="${s(t.phone||"")}"></div>
      <div style="grid-column:1/-1"><label class="kpi-label">${s(e("workspace.email"))}</label><input class="search" style="width:100%" id="mEmail" autocomplete="email" value="${s(t.email||"")}"></div>
      <div><label class="kpi-label">${s(e("workspace.source"))}</label>
        <select class="control" style="width:100%" id="mSource">
          ${["manual","TikTok","WhatsApp","Facebook","import"].map(o=>`<option value="${o}" ${l===o?"selected":""}>${o}</option>`).join("")}
        </select>
      </div>
      <div><label class="kpi-label">${s(e("workspace.col_status"))}</label>
        <select class="control" style="width:100%" id="mStatus">${a||'<option value="new">NEW</option>'}</select>
      </div>
      <div><label class="kpi-label">${s(e("workspace.col_branch"))}</label>
        <select class="control" style="width:100%" id="mBranch">${n}</select>
      </div>
      <div><label class="kpi-label">${s(e("workspace.col_beautician"))}</label>
        <select class="control" style="width:100%" id="mBeautician">${i}</select>
      </div>
    </div>`}async function Zt(){const t=$e!=null,a=t?U(_.leadUpdateUrlTemplate,$e):_.leadStoreUrl;if(!a){$(e("workspace.save_error"));return}const n={name:(c("#mName")?.value||"").trim(),phone:(c("#mPhone")?.value||"").trim(),email:(c("#mEmail")?.value||"").trim()||null,source:c("#mSource")?.value||"manual",status:c("#mStatus")?.value||void 0,spa_branch_id:c("#mBranch")?.value?Number(c("#mBranch").value):null,beautician_id:c("#mBeautician")?.value?Number(c("#mBeautician").value):null};if(!n.name||!n.phone){$(e("workspace.save_error"));return}try{const i=await fetch(a,{method:t?"PUT":"POST",headers:D(!0),credentials:"same-origin",body:JSON.stringify(n)}),l=await i.json().catch(()=>({}));if(!i.ok){const o=l&&(l.message||Object.values(l.errors||{})[0]?.[0])||e(t?"workspace.update_error":"workspace.save_error");$(o);return}R(),$e=null,$(l.message||e(t?"workspace.updated":"workspace.saved")),t||(r.leadPage=1),await q()}catch(i){console.error(i),$(e(t?"workspace.update_error":"workspace.save_error"))}}function St(t){_.canDeleteLead&&(se(e("workspace.delete"),e("workspace.delete_confirm"),"",`<button type="button" class="btn" data-action-drawer-close>${s(e("workspace.cancel"))}</button><button type="button" class="btn danger" id="confirmDeleteLead">${s(e("workspace.delete"))}</button>`,e("nav.leads")),c("#confirmDeleteLead").onclick=async a=>{const n=a.currentTarget;n.disabled=!0,await es(t),n.disabled=!1})}async function es(t){if(!_.canDeleteLead)return;const a=U(_.leadDestroyUrlTemplate,t);if(!a){$(e("workspace.delete_error"));return}try{const n=await fetch(a,{method:"DELETE",headers:D(!1),credentials:"same-origin"}),i=await n.json().catch(()=>({}));if(!n.ok){$(i.message||e("workspace.delete_error"));return}$(i.message||e("workspace.deleted")),R(),V(),await q()}catch(n){console.error(n),$(e("workspace.delete_error"))}}async function aa(t){let n=ie.find(v=>String(v.id)===String(t))||we.find(v=>String(v.id)===String(t));const i=_.leadShowUrlTemplate;if(i)try{const v=await fetch(U(i,t),{headers:D(!1),credentials:"same-origin"});if(v.ok){const b=await v.json();n=b.data||n,b.filters?.statuses&&(E.statuses=b.filters.statuses)}}catch(v){console.error(v)}if(!n)return;R(),V(),Me=document.activeElement,La=document.body.style.overflow,document.body.style.overflow="hidden";const l=c("#leadDrawer .eyebrow");l&&(l.textContent=e("workspace.drawer_eyebrow")),c("#drawerName").textContent=n.name||"";const o=as(n),d=(E.statuses||[]).map(v=>`<option value="${s(v.value)}" ${String(n.status_key)===String(v.value)?"selected":""}>${s(v.label)}</option>`).join(""),u=!!_.canEditLead,p=String(n.name||""),m=s((p[0]||"?").toUpperCase());c("#drawerBody").innerHTML=`
  <div class="journey">
    <div class="journey-hero">
      <div class="journey-hero__avatar" aria-hidden="true">${m}</div>
      <div class="journey-hero__meta">
        <div class="journey-hero__code">${s(n.code||n.id)}</div>
        <div class="journey-hero__badges">
          ${P(n.status)}
          <span class="journey-pill journey-pill--${s(o.healthTone)}">${s(o.healthLabel)}</span>
          <span class="journey-pill journey-pill--prio-${s(o.priorityTone)}">${s(o.priorityLabel)}</span>
        </div>
      </div>
    </div>

    <section class="journey-section">
      <div class="journey-section__title">${s(e("workspace.drawer_analytics"))}</div>
      <div class="journey-stats">
        <div class="journey-stat">
          <span class="journey-stat__label">${s(e("workspace.drawer_health"))}</span>
          <strong class="journey-stat__value">${o.score}</strong>
          <div class="journey-meter"><span style="width:${o.score}%"></span></div>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${s(e("workspace.drawer_stage"))}</span>
          <strong class="journey-stat__value">${o.stagePct}%</strong>
          <div class="journey-meter journey-meter--stage"><span style="width:${o.stagePct}%"></span></div>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${s(e("workspace.drawer_pipeline_days"))}</span>
          <strong class="journey-stat__value">${s(e("workspace.drawer_days",{count:o.daysInPipeline}))}</strong>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${s(e("workspace.drawer_since_contact"))}</span>
          <strong class="journey-stat__value">${o.neverContacted?s(e("workspace.drawer_never_contacted")):s(e("workspace.drawer_days",{count:o.daysSinceContact}))}</strong>
        </div>
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${s(e("workspace.drawer_signals"))}</div>
      <div class="journey-signals">
        ${o.signals.map(v=>`<span class="journey-signal journey-signal--${s(v.tone)}">${s(v.label)}</span>`).join("")||`<span class="journey-signal journey-signal--ok">${s(e("workspace.drawer_signal_healthy"))}</span>`}
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${s(e("workspace.drawer_pipeline"))}</div>
      <div class="journey-pipeline" role="list">${o.pipelineHtml}</div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${s(e("workspace.drawer_contact"))}</div>
      <div class="journey-kv">
        <div><span>${s(e("workspace.col_phone"))}</span><strong>${s(n.phone||"—")}</strong></div>
        <div><span>${s(e("workspace.col_email"))}</span><strong title="${s(n.email||"")}">${s(n.email||"—")}</strong></div>
        <div><span>${s(e("workspace.drawer_source_label"))}</span><strong>${s(n.source||"—")}</strong></div>
        <div><span>${s(e("workspace.col_customer"))}</span><strong>${s(n.customer||"—")}</strong></div>
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${s(e("workspace.drawer_assignment"))}</div>
      <div class="journey-kv">
        <div><span>${s(e("workspace.col_beautician"))}</span><strong>${s(n.beautician||"—")}</strong></div>
        <div><span>${s(e("workspace.col_branch"))}</span><strong>${s(n.branch||"—")}</strong></div>
        <div><span>${s(e("workspace.col_last_fu"))}</span><strong>${s(n.last||"—")}</strong></div>
        <div><span>${s(e("workspace.col_date"))}</span><strong>${s(n.date||"—")}</strong></div>
      </div>
    </section>

    ${u?`<section class="journey-section">
      <div class="journey-section__title">${s(e("workspace.update_status"))}</div>
      <div class="journey-status-row">
        <select class="control" id="drawerStatus">${d}</select>
        <button class="btn primary" type="button" id="drawerSaveStatus">${s(e("workspace.update_status"))}</button>
      </div>
    </section>`:""}

    <div class="journey-actions">
      <div class="journey-section__title">${s(e("workspace.drawer_actions"))}</div>
      <div class="journey-actions__row">
        ${u?`<button class="btn primary" type="button" id="drawerFollowBtn">${s(e("followup.mark"))}</button>`:""}
        ${u?`<button class="btn" type="button" id="drawerEditBtn">${s(e("workspace.edit"))}</button>`:""}
        ${_.canDeleteLead?`<button class="btn danger" type="button" id="drawerDeleteBtn">${s(e("workspace.delete"))}</button>`:""}
      </div>
    </div>
  </div>`,c("#leadDrawer").classList.add("show"),c("#drawerBackdrop").classList.add("show"),c("#leadDrawer").setAttribute("aria-hidden","false"),c("#leadDrawer").inert=!1,c(".app-shell").inert=!0,c("#drawerClose").focus({preventScroll:!0}),X(),c("#drawerFollowBtn")&&(c("#drawerFollowBtn").onclick=()=>jt(n.id)),c("#drawerEditBtn")&&(c("#drawerEditBtn").onclick=()=>{V(),wt(n.id)}),c("#drawerDeleteBtn")&&(c("#drawerDeleteBtn").onclick=()=>St(n.id)),c("#drawerSaveStatus")&&(c("#drawerSaveStatus").onclick=async()=>{const v=c("#drawerStatus")?.value,b=U(_.leadStatusUrlTemplate,n.id);if(!v||!b){$(e("workspace.status_error"));return}try{const w=await fetch(b,{method:"PATCH",headers:D(!0),credentials:"same-origin",body:JSON.stringify({status:v})}),g=await w.json().catch(()=>({}));if(!w.ok){$(g.message||e("workspace.status_error"));return}$(g.message||e("workspace.status_updated")),r.view==="followup"?await ae():await q(),aa(n.id)}catch(w){console.error(w),$(e("workspace.status_error"))}})}function as(t){const a=["new","claimed","follow_up","booking","payment_verified","converted"],n=String(t.status_key||"new"),i=a.indexOf(n),l=n==="lost"||n==="no_response"?Math.max(10,Math.round((Math.max(i,0)+1)/a.length*100)):Math.round((Math.max(i,0)+1)/a.length*100),o=Number(t.days_in_pipeline!=null?t.days_in_pipeline:t.days_since_followup||0),d=!t.last_followed_up_at,u=Number(t.days_since_followup||0),p=String(t.followup_bucket)==="overdue"||(d?o>=2:u>=2);let m=28;n==="converted"?m=96:n==="payment_verified"?m=82:n==="booking"?m=68:n==="follow_up"?m=54:n==="claimed"?m=42:n==="new"?m=32:n==="no_response"?m=22:n==="lost"&&(m=12),t.existing&&(m+=8),t.duplicate&&(m-=6),p&&(m-=18),!d&&u===0&&(m+=6),t.beautician_id&&(m+=4),t.branch_id&&(m+=3),m=Math.max(5,Math.min(99,m));let v="warm",b=e("workspace.drawer_health_warm");m>=75?(v="hot",b=e("workspace.drawer_health_hot")):m<35||p||n==="lost"||n==="no_response"?(v="risk",b=e("workspace.drawer_health_risk")):m<50&&(v="cold",b=e("workspace.drawer_health_cold"));let w="med",g=e("workspace.drawer_priority_medium");p||n==="no_response"||!t.beautician_id&&o>=1?(w="high",g=e("workspace.drawer_priority_high")):(n==="converted"||n==="payment_verified")&&(w="low",g=e("workspace.drawer_priority_low"));const f=[];p&&f.push({tone:"danger",label:e("workspace.drawer_signal_overdue")}),o<=1&&n==="new"&&f.push({tone:"info",label:e("workspace.drawer_signal_fresh")}),t.existing&&f.push({tone:"ok",label:e("workspace.drawer_signal_existing")}),t.duplicate&&f.push({tone:"warn",label:e("workspace.drawer_signal_duplicate")}),!t.beautician_id&&n!=="converted"&&f.push({tone:"warn",label:e("workspace.drawer_signal_unassigned")}),!t.branch_id&&n!=="converted"&&f.push({tone:"warn",label:e("workspace.drawer_signal_no_branch")}),f.length||f.push({tone:"ok",label:e("workspace.drawer_signal_healthy")});const S=a.map((M,y)=>{const k=(E.statuses||[]).find(F=>F.value===M)?.label||M.replace(/_/g," ");return`<div class="journey-step${i>y||n==="converted"?" is-done":i===y?" is-active":""}" role="listitem"><span class="journey-step__dot"></span><span class="journey-step__label">${s(k)}</span></div>`}).join("");return{score:m,stagePct:l,daysInPipeline:o,daysSinceContact:u,neverContacted:d,healthTone:v,healthLabel:b,priorityTone:w,priorityLabel:g,signals:f,pipelineHtml:S}}let H=null,la=[],ra={total_imports:0,raw:0,unique:0,duplicates:0,existing:0,invalid:0,imported:0},be=!1;function ts(){B.innerHTML=`${de(e("import.title"),e("import.subtitle"),`<button type="button" class="btn" data-jump="imports">${s(e("import.history_btn"))}</button><button type="button" class="btn primary" data-jump="leads">${s(e("import.view_leads"))}</button>`)}
  <section class="card">
    <div class="tabs" id="importTabs" role="tablist">${[["paste",e("import.tab_paste")],["excel",e("import.tab_excel")],["csv",e("import.tab_csv")],["manual",e("import.tab_manual")]].map(t=>`<button type="button" class="tab ${r.importTab===t[0]?"active":""}" role="tab" aria-selected="${r.importTab===t[0]?"true":"false"}" data-tab="${t[0]}">${s(t[1])}</button>`).join("")}</div>
    <div id="importPane" style="margin-top:14px"></div>
  </section>
  <section class="card hidden" id="previewCard" style="margin-top:12px"></section>`,Mt(),X(),ss()}function ss(){const t=T("[data-tab]","#importTabs");t.forEach(a=>{a.onclick=n=>{n.preventDefault();const i=a.dataset.tab;if(!i||i===r.importTab)return;r.importTab=i,t.forEach(o=>{const d=o===a;o.classList.toggle("active",d),o.setAttribute("aria-selected",d?"true":"false")}),H=null;const l=c("#previewCard");l&&l.classList.add("hidden"),Mt()}})}function Mt(){const t=c("#importPane");if(!t)return;const a=!!_.canCreateLead;if(r.importTab==="paste")t.innerHTML=`<div><div class="card-title">${s(e("import.paste_title"))}</div><div class="card-subtitle">${s(e("import.paste_sub"))}</div><textarea class="paste-area" id="pasteArea" placeholder="${s(e("import.paste_placeholder"))}"></textarea><div style="display:flex;justify-content:flex-end;margin-top:10px"><button type="button" class="btn primary" id="parseBtn" ${a?"":"disabled"}>${s(e("import.parse"))}</button></div></div>`,c("#parseBtn")&&(c("#parseBtn").onclick=()=>ns());else if(r.importTab==="manual")t.innerHTML=`<div class="detail-grid"><div><label class="kpi-label" for="manualName">${s(e("import.name"))}</label><input class="search" style="width:100%" id="manualName" autocomplete="name"></div><div><label class="kpi-label" for="manualPhone">${s(e("import.phone"))}</label><input class="search" style="width:100%" id="manualPhone" inputmode="tel" autocomplete="tel"></div><div><label class="kpi-label" for="manualEmail">${s(e("import.email"))}</label><input class="search" style="width:100%" id="manualEmail" type="email" autocomplete="email"></div><div><label class="kpi-label" for="manualSource">${s(e("import.source"))}</label><select class="control" style="width:100%" id="manualSource"><option value="TikTok">TikTok</option><option value="WhatsApp">WhatsApp</option><option value="Facebook">Facebook</option><option value="manual">Import</option></select></div><div style="grid-column:1/-1;text-align:right"><button type="button" class="btn primary" id="manualSaveBtn" ${a?"":"disabled"}>${s(e("import.save_lead"))}</button></div></div>`,c("#manualSaveBtn")&&(c("#manualSaveBtn").onclick=is);else{const n=r.importTab==="excel";t.innerHTML=`<div class="import-zone" id="importDropZone" tabindex="0"><div class="import-icon">⇧</div><h3>${s(e(n?"import.drop_excel":"import.drop_csv"))}</h3><p>${s(e(n?"import.accepted_excel":"import.accepted_csv"))}</p><input type="file" id="fileInput" class="hidden" accept="${n?".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel":".csv,text/csv,text/plain"}"><button type="button" class="btn primary" id="browseBtn" ${a?"":"disabled"}>${s(e("import.browse"))}</button></div>`;const i=c("#fileInput"),l=c("#browseBtn"),o=c("#importDropZone");l&&i&&(l.onclick=d=>{d.preventDefault(),d.stopPropagation(),i.click()}),i&&(i.onchange=()=>Ya(i.files?.[0])),o&&a&&(o.addEventListener("click",d=>{d.target===l||l?.contains(d.target)||i?.click()}),["dragenter","dragover"].forEach(d=>o.addEventListener(d,u=>{u.preventDefault(),u.stopPropagation(),o.classList.add("is-dragover")})),["dragleave","drop"].forEach(d=>o.addEventListener(d,u=>{u.preventDefault(),u.stopPropagation(),o.classList.remove("is-dragover")})),o.addEventListener("drop",d=>{const u=d.dataTransfer?.files?.[0];u&&Ya(u)}),o.addEventListener("keydown",d=>{(d.key==="Enter"||d.key===" ")&&(d.preventDefault(),i?.click())}))}}async function ns(){const t=c("#pasteArea")?.value||"";if(!t.trim()){$(e("import.paste_required"));return}await Ta({method:"paste",paste:t})}async function Ya(t){if(!t){$(e("import.file_required"));return}const a=new FormData;a.append("method",r.importTab==="excel"?"excel":"csv"),a.append("file",t),r.branch&&r.branch!=="all"&&a.append("spa_branch_id",r.branch),await Ta(a,!0)}async function is(){const t=(c("#manualName")?.value||"").trim(),a=(c("#manualPhone")?.value||"").trim(),n=(c("#manualEmail")?.value||"").trim(),i=(c("#manualSource")?.value||"manual").trim();if(!t||!a){$(e("import.rows_required"));return}await Ta({method:"manual",rows:[{name:t,phone:a,email:n||null,source:i}]})}async function Ta(t,a=!1){const n=_.importPreviewUrl;if(!n){$(e("import.preview_error"));return}if(!be){be=!0,$(e("import.parsing"));try{const i={method:"POST",credentials:"same-origin",headers:D(!a)};a?i.body=t:(r.branch&&r.branch!=="all"&&!t.spa_branch_id&&(t.spa_branch_id=Number(r.branch)||null),i.body=JSON.stringify(t));const l=await fetch(n,i),o=await l.json().catch(()=>({}));if(!l.ok){const d=o.message||Object.values(o.errors||{}).flat()[0]||e("import.preview_error");$(d);return}H=o.data||null,ls()}catch(i){console.error(i),$(e("import.preview_error"))}finally{be=!1}}}function ls(){const t=c("#previewCard");if(!t||!H)return;const a=H.rows||[],n=H.summary||{},i=Number(n.ready||0)+Number(n.existing||0);t.classList.remove("hidden"),t.innerHTML=`<div class="card-title-row"><div><div class="card-title">${s(e("import.preview_title"))}</div><div class="card-subtitle">${s(e("import.preview_sub"))}</div></div><button class="btn small" type="button" id="closePreviewBtn">${s(e("import.close"))}</button></div>
  <div class="summary-strip">
    <div class="summary-chip"><label>${s(e("import.total_rows"))}</label><strong>${h(n.total||0)}</strong></div>
    <div class="summary-chip"><label>${s(e("import.ready"))}</label><strong>${h(n.ready||0)}</strong></div>
    <div class="summary-chip"><label>${s(e("import.duplicate"))}</label><strong>${h(n.duplicate||0)}</strong></div>
    <div class="summary-chip"><label>${s(e("import.existing"))}</label><strong>${h(n.existing||0)}</strong></div>
    <div class="summary-chip"><label>${s(e("import.invalid"))}</label><strong>${h(n.invalid||0)}</strong></div>
  </div>
  <div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${s(e("import.col_row"))}</th><th>${s(e("import.col_name"))}</th>
    <th title="${s(e("import.col_orig_phone"))}">${s(e("import.col_orig_phone"))}</th>
    <th title="${s(e("import.col_norm_phone"))}">${s(e("import.col_norm_phone"))}</th>
    <th>${s(e("import.col_email"))}</th><th>${s(e("import.col_detection"))}</th><th>${s(e("import.col_action"))}</th>
  </tr></thead><tbody>
  ${a.map(l=>`<tr><td>${l.row}</td><td><strong>${s(l.name||"")}</strong></td><td>${s(l.phone_orig||"")}</td><td>${s(l.phone_e164||l.phone_norm||"")}</td><td>${s(l.email||"")}</td><td>${P(l.detection)}</td><td>${l.detection==="READY"||l.detection==="EXISTING"?`<span class="badge success">${s(e("import.action_import"))}</span>`:`<span class="badge gray">${s(e("import.action_skip"))}</span>`}</td></tr>`).join("")||`<tr><td colspan="7"><div class="empty">${s(e("import.empty"))}</div></td></tr>`}
  </tbody></table></div>
  <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px"><button class="btn" type="button" id="cancelImportBtn">${s(e("import.cancel"))}</button><button class="btn primary" type="button" id="confirmImport" ${i>0?"":"disabled"}>${s(e("import.confirm",{count:i}))}</button></div>`,c("#closePreviewBtn")?.addEventListener("click",()=>t.classList.add("hidden")),c("#cancelImportBtn")?.addEventListener("click",()=>t.classList.add("hidden")),c("#confirmImport")?.addEventListener("click",rs),t.scrollIntoView({behavior:"smooth",block:"start"})}async function rs(){if(!H||be)return;const t=_.importConfirmUrl;if(!t){$(e("import.confirm_error"));return}const a=(H.rows||[]).map(n=>({name:n.name,phone:n.phone_norm||n.phone_orig,email:n.email||null,source:n.source||H.source||"import",import:n.detection==="READY"||n.detection==="EXISTING"}));be=!0,$(e("import.importing"));try{const n=await fetch(t,{method:"POST",credentials:"same-origin",headers:D(!0),body:JSON.stringify({method:H.method||"paste",rows:a,source:H.source||"import",file_name:H.file_name||null,spa_branch_id:H.spa_branch_id||null,beautician_id:H.beautician_id||null})}),i=await n.json().catch(()=>({}));if(!n.ok){$(i.message||e("import.confirm_error"));return}$(i.message||e("import.imported",{count:i.data&&i.data.imported||0})),H=null,setTimeout(()=>I("leads"),700)}catch(n){console.error(n),$(e("import.confirm_error"))}finally{be=!1}}async function os(){B.innerHTML=`${de(e("import.history_title"),e("import.history_subtitle"),`<button class="btn primary" data-jump="import">${s(e("import.new_import"))}</button>`)}
  <div class="grid kpi-grid" id="importKpiMount"></div>
  <section class="card" style="margin-top:12px"><div id="importHistoryMount"><div class="empty">${s(e("workspace.loading"))}</div></div></section>`,X(),await Tt()}async function Tt(){const t=_.importHistoryUrl,a=c("#importHistoryMount"),n=c("#importKpiMount");if(!t){a&&(a.innerHTML=`<div class="empty">${s(e("import.load_error"))}</div>`);return}try{const i=await fetch(t+"?per_page=50&page="+Ra,{headers:D(!1),credentials:"same-origin"}),l=await i.json().catch(()=>({}));if(!i.ok){$(l.message||e("import.load_error"));return}la=l.data||[],ra=l.meta?.summary||ra;const o=ra;if(n&&(n.innerHTML=`${x("▤",e("import.kpi_total"),h(o.total_imports),e("common.this_month"),e("import.meta_batches"),"blue")}${x("♙",e("import.kpi_raw"),h(o.raw),e("import.meta_historical"),"","rose")}${x("✓",e("import.kpi_unique"),h(o.unique),e("import.meta_after_clean"),"","green")}${x("⧉",e("import.kpi_duplicates"),h(o.duplicates),e("import.meta_auditable"),"","purple")}${x("♧",e("import.kpi_existing"),h(o.existing),e("import.meta_phone"),"","blue")}`),!a)return;if(!la.length){a.innerHTML=`<div class="empty"><strong>${s(e("import.empty"))}</strong>${s(e("import.empty_hint"))}</div>`;return}a.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
      <th>${s(e("import.col_batch"))}</th><th>${s(e("import.col_date"))}</th>
      <th title="${s(e("import.col_by"))}">${s(e("import.col_by"))}</th>
      <th>${s(e("import.col_method"))}</th><th>${s(e("import.col_file"))}</th>
      <th>${s(e("import.col_raw"))}</th><th>${s(e("import.col_unique"))}</th>
      <th>${s(e("import.col_duplicate"))}</th><th>${s(e("import.col_existing"))}</th>
      <th>${s(e("import.col_invalid"))}</th><th>${s(e("import.col_status"))}</th>
    </tr></thead><tbody>
    ${la.map(d=>`<tr>
      <td><strong>${s(d.batch_code||"")}</strong></td><td>${s(d.date||"")}</td>
      <td>${s(d.by||"")}</td><td>${s(d.method||"")}</td><td>${s(d.file||"")}</td>
      <td>${h(d.raw)}</td><td>${h(d.unique)}</td><td>${h(d.duplicate)}</td>
      <td>${h(d.existing)}</td><td>${h(d.invalid)}</td><td>${P(d.status)}</td>
    </tr>`).join("")}
    </tbody></table></div>${G("imports",l.meta||{})}`,J("imports",d=>(Ra=d,Tt()))}catch(i){console.error(i),a&&(a.innerHTML=`<div class="empty">${s(e("import.load_error"))}</div>`)}}function Lt(){const t=r.paymentCustomerId?`<div class="pay-customer-chip" id="payCustomerChip">
        <span>${s(e("payments.filtered_customer",{name:r.paymentCustomerLabel||"#"+r.paymentCustomerId}))}</span>
        <button type="button" class="btn small soft" id="payClearCustomer">${s(e("payments.clear_customer"))}</button>
      </div>`:"";B.innerHTML=`<div class="pay-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${s(e("payments.title"))}</h1>
        <div class="page-subtitle">${s(e("payments.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="payRefresh">${s(e("payments.refresh"))}</button>
        ${_.canViewOrder&&_.ordersIndexUrl?`<a class="btn primary" href="${s(_.ordersIndexUrl)}" target="_blank" rel="noopener">${s(e("payments.open_orders"))}</a>`:""}
      </div>
    </div>
    ${t}
    <div class="pay-metrics" id="payHeroStats"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro">
          <p class="lead-panel__sub" id="payHeroCopy" style="margin:0">${s(r.paymentCustomerId?e("payments.filtered_customer",{name:r.paymentCustomerLabel||"#"+r.paymentCustomerId}):e("payments.subtitle"))}</p>
        </div>
        <div class="lead-panel__head-meta">
          <span class="lead-panel__count" id="payResultCount">—</span>
        </div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="payTabs" role="tablist"></div>
        <label class="lead-search" for="paySearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="paySearch" type="search" autocomplete="off" placeholder="${s(e("payments.search_placeholder"))}" value="${s(r.paymentSearch||"")}" />
        </label>
        <div class="lead-filter-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
          <label class="lead-field">
            <span class="lead-field__label">${s(e("payments.filter_beautician"))}</span>
            <select class="lead-field__control" id="payBeautician"></select>
          </label>
          <label class="lead-field">
            <span class="lead-field__label">${s(e("payments.filter_branch"))}</span>
            <select class="lead-field__control" id="payBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="payTableMount"></div>
    </section>
  </div>`;const a=c("#payRefresh");a&&(a.onclick=()=>z());const n=c("#payClearCustomer");n&&(n.onclick=()=>cs()),ps(),z()}function cs(){r.paymentCustomerId=null,r.paymentCustomerLabel="",r.paymentSearch="",r.paymentPage=1,r.view==="payments"?Lt():z()}function ds(t){if(!t)return;const a=Number(t.id||0);a&&(r.paymentCustomerId=a,r.paymentCustomerLabel=String(t.name||t.code||"#"+a),r.paymentSearch=String(t.phone||t.email||t.name||"").trim(),r.paymentTab="all",r.paymentPage=1,r.paymentBeautician="all",I("payments"))}function ps(){const t=c("#paySearch");t&&(t.oninput=()=>{clearTimeout(Aa),Aa=setTimeout(()=>{r.paymentSearch=t.value.trim(),r.paymentPage=1,z()},320)});const a=c("#payBeautician");a&&(a.onchange=()=>{r.paymentBeautician=a.value,r.paymentPage=1,z()});const n=c("#payBranch");n&&(n.onchange=()=>{r.paymentBranch=n.value,r.paymentPage=1,z()})}async function z(){const t=_.paymentsUrl||"",a=c("#payTableMount");if(!t){a&&(a.innerHTML=`<div class="pay-empty"><strong>${s(e("payments.load_error"))}</strong></div>`);return}Ke=!0,Ka(),za();const n=new URLSearchParams;r.paymentCustomerId?n.set("customer_id",String(r.paymentCustomerId)):r.paymentSearch&&n.set("q",r.paymentSearch),r.paymentTab&&r.paymentTab!=="all"&&n.set("status",r.paymentTab);const i=r.paymentBranch!=="all"?r.paymentBranch:r.branch||"all";i&&i!=="all"&&n.set("branch",i),r.paymentBeautician&&r.paymentBeautician!=="all"&&n.set("beautician",r.paymentBeautician),r.period&&n.set("period",r.period),n.set("page",String(r.paymentPage||1)),n.set("per_page","25");try{const l=await fetch(`${t}?${n.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("payments "+l.status);const o=await l.json();me=Array.isArray(o.data)?o.data:[],ba=Object.assign({pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0},o.meta&&o.meta.summary||{}),ve=Object.assign({statuses:[],beauticians:[],branches:[]},o.filters||{}),pa={current_page:o.meta&&o.meta.current_page||1,last_page:o.meta&&o.meta.last_page||1,total:o.meta&&o.meta.total||0}}catch(l){console.error(l),me=[],$(e("payments.load_error"))}finally{Ke=!1,Ka(),ms(),vs(),za()}}function us(t){const a=String(t||"pending");return a==="paid"?{customer:"done",accountant:"done",hq:"done",active:null}:a==="processing"?{customer:"done",accountant:"done",hq:"active",active:"hq"}:a==="canceled"||a==="refunded"?{customer:"done",accountant:"active",hq:null,active:"accountant"}:{customer:"done",accountant:"active",hq:null,active:"accountant"}}function Ct(t){const a=us(t),n=l=>{const o=a[l];return`<span class="pay-pipe__node ${o==="done"?"is-done":o==="active"?"is-active":""}" title="${s(e("payments.flow_"+(l==="hq"?"hq":l)))}"></span>`},i=l=>`<span class="pay-pipe__line ${a[l]==="done"?"is-done":""}"></span>`;return`<div class="pay-pipe" title="${s(e("payments.pipeline"))}">${n("customer")}${i("customer")}${n("accountant")}${i("accountant")}${n("hq")}</div>`}function Ka(){const t=ba,a=c("#payHeroCopy");a&&(a.textContent=t.queue>0?e("payments.pulse_busy",{count:h(t.queue),amount:N(t.pending_amount)}):e("payments.pulse_clear"));const n=c("#payHeroStats");n&&(n.innerHTML=`
      <div class="pay-metric"><span>${s(e("payments.stat_queue"))}</span><strong>${h(t.queue)}</strong></div>
      <div class="pay-metric"><span>${s(e("payments.stat_paid"))}</span><strong>${N(t.paid_amount)}</strong></div>
      <div class="pay-metric"><span>${s(e("payments.stat_today"))}</span><strong>${h(t.paid_today)}</strong></div>
      <div class="pay-metric"><span>${s(e("payments.stat_hold"))}</span><strong>${h(t.hold)}</strong></div>
    `)}function ms(){const t=c("#payTabs");if(!t)return;const a=ba,n={all:(a.pending||0)+(a.processing||0)+(a.paid||0)+(a.hold||0)+(a.refunded||0),queue:a.queue||0,pending:a.pending||0,processing:a.processing||0,paid:a.paid||0,canceled:a.hold||0,refunded:a.refunded||0},l=[...ve.statuses&&ve.statuses.length?ve.statuses:[{value:"queue",label:e("payments.tab_queue")},{value:"all",label:e("payments.tab_all")},{value:"pending",label:e("payments.tab_pending")},{value:"processing",label:e("payments.tab_processing")},{value:"paid",label:e("payments.tab_paid")},{value:"canceled",label:e("payments.tab_hold")},{value:"refunded",label:e("payments.tab_refunded")}]].sort((o,d)=>(o.value==="queue"?-1:0)-(d.value==="queue"?-1:0));t.innerHTML=l.map(o=>{const d=o.value,u=r.paymentTab===d?"active":"",p=n[d],m=p!==void 0?`<span class="pay-tab-count">${h(p)}</span>`:"";return`<button type="button" class="tab ${u}" role="tab" data-ptab="${s(d)}">${s(o.label)}${m}</button>`}).join(""),T("[data-ptab]",t).forEach(o=>{o.onclick=()=>{r.paymentTab=o.dataset.ptab,r.paymentPage=1,z()}})}function vs(){const t=c("#payBeautician");if(t){const n=r.paymentBeautician;t.innerHTML=`<option value="all">${s(e("payments.all_beauticians"))}</option>`+(ve.beauticians||[]).map(i=>`<option value="${i.id}" ${String(n)===String(i.id)?"selected":""}>${s(i.name)}</option>`).join("")}const a=c("#payBranch");if(a){const n=r.paymentBranch;a.innerHTML=`<option value="all">${s(e("payments.all_branches"))}</option>`+(ve.branches||[]).map(i=>`<option value="${i.id}" ${String(n)===String(i.id)?"selected":""}>${s(i.code?i.code+" · "+i.name:i.name)}</option>`).join("")}}function za(){const t=c("#payTableMount"),a=c("#payResultCount");if(a&&(a.textContent=e("payments.result_count",{count:h(pa.total||me.length)})),!t)return;if(Ke){t.innerHTML=`<div class="pay-empty"><strong>${s(e("payments.loading"))}</strong></div>`;return}if(!me.length){t.innerHTML=`<div class="pay-empty"><strong>${s(e("payments.empty"))}</strong><span>${s(e("payments.empty_hint"))}</span></div>`;return}const n=me.map(l=>{const o=l.ref&&l.ref!=="—",d=[l.has_proof?`<span class="pay-chip pay-chip--ok">${s(e("payments.chip_proof"))}</span>`:`<span class="pay-chip pay-chip--muted">${s(e("payments.stage_declared"))}</span>`,o?`<span class="pay-chip pay-chip--ok">${s(e("payments.chip_ref"))}</span>`:`<span class="pay-chip pay-chip--warn">${s(e("payments.chip_no_ref"))}</span>`].join("");return`<tr>
      <td><span class="pay-id">${s(l.code||"ORD-"+l.id)}</span></td>
      <td><div class="person-cell"><div class="mini-avatar">${s(l.initial||"?")}</div><div><strong>${s(l.customer||"")}</strong><small>${s(l.phone||"")}</small></div></div></td>
      <td>${s(l.branch||"—")}</td>
      <td>${s(l.beautician||"—")}</td>
      <td><div class="pay-amount">${N(l.amount)}</div><div class="pay-method">${s(l.payment_method_label||"")}</div></td>
      <td><div class="pay-chips">${d}</div><div class="pay-method" title="${s(e("payments.col_ref"))}">${s(l.ref||"—")}</div></td>
      <td>${Ct(l.payment_status)}</td>
      <td>${P(l.payment_status_label||l.payment_status)}</td>
      <td><button type="button" class="pay-review-btn" data-payment="${l.id}">${s(e("payments.review"))}</button></td>
    </tr>`}).join(""),i=G("pay",pa);t.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${s(e("payments.col_id"))}</th>
    <th>${s(e("payments.col_customer"))}</th>
    <th>${s(e("payments.col_branch"))}</th>
    <th>${s(e("payments.col_beautician"))}</th>
    <th>${s(e("payments.col_amount"))}</th>
    <th>${s(e("payments.col_ref"))}</th>
    <th>${s(e("payments.pipeline"))}</th>
    <th>${s(e("payments.col_status"))}</th>
    <th>${s(e("payments.col_action"))}</th>
  </tr></thead><tbody>${n}</tbody></table></div>${i}`,T("[data-payment]",t).forEach(l=>l.onclick=()=>_s(Number(l.dataset.payment))),J("pay",l=>(r.paymentPage=l,z()))}function hs(t){const a=t.proof;let n;if(!_.canViewOrder)n=`<p class="payment-proof__empty">${s(e("payments.proof_access"))}</p>`;else if(!a?.url)n=`<p class="payment-proof__empty">${s(e(t.has_proof?"payments.proof_unavailable":"payments.proof_missing"))}</p>`;else{const i=s(a.url),l=s(a.name||e("payments.proof_title")),o=`<a class="btn small" href="${i}" target="_blank" rel="noopener noreferrer">${s(e("payments.proof_open"))}</a>`;n=`${a.kind==="image"?`<a class="payment-proof__image" href="${i}" target="_blank" rel="noopener noreferrer"><img id="paymentProofImage" src="${i}" alt="${l}" loading="lazy"></a><p class="payment-proof__empty" id="paymentProofError" hidden>${s(e("payments.proof_unavailable"))}</p>`:a.kind==="pdf"?`<object class="payment-proof__pdf" data="${i}" type="application/pdf" aria-label="${l}"><p class="payment-proof__empty">${s(e("payments.proof_pdf_hint"))}</p></object>`:`<p class="payment-proof__empty">${s(e("payments.proof_pdf_hint"))}</p>`}<div class="payment-proof__file"><span>${l}</span>${o}</div>`}return`<section class="payment-proof"><h3>${s(e("payments.proof_title"))}</h3>${n}</section>`}function _s(t){const a=me.find(m=>Number(m.id)===Number(t));if(!a)return;const n=U(_.orderShowUrlTemplate,a.id),i=!!_.canEditOrder,l=!!_.canViewOrder,o=["identity","invoice","method","ref","proof","status"].map(m=>{const v=a.checklist?.[m],b=["completed","not_applicable"].includes(v)?v:"pending";return`<div class="pay-review__check is-${b}" data-check="${m}"><span class="pay-review__check-icon" aria-hidden="true">${b==="completed"?"✓":b==="not_applicable"?"—":"○"}</span><span>${s(e("payments.check_"+m))}</span><small>${s(e("payments.check_"+b))}</small></div>`}).join(""),d=`<div class="pay-review">
    <div class="pay-review__hero">
      <div class="pay-review__avatar">${s(a.initial||"?")}</div>
      <div style="min-width:0;flex:1">
        <div class="pay-id">${s(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${s(a.customer||"")}</strong>
        <div class="pay-method">${s(a.phone||"")} · ${s(a.branch_name||a.branch||"")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px;align-items:center">${P(a.payment_status_label||a.payment_status)}${Ct(a.payment_status)}</div>
      </div>
      <div style="text-align:right"><div class="pay-amount">${N(a.amount)}</div><div class="pay-method">${s(a.payment_method_label||"")}</div></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${s(e("payments.col_customer_stage"))}</label><strong>${s(a.customer_stage)}</strong></div>
      <div class="pay-review__card"><label>${s(e("payments.col_accountant"))}</label><strong>${s(a.accountant_stage)}</strong></div>
      <div class="pay-review__card"><label>${s(e("payments.col_hq"))}</label><strong>${s(a.hq_stage)}</strong></div>
    </div>
    ${hs(a)}
    ${i?`<div class="detail-grid">
      <div class="detail-box"><label>${s(e("payments.bank_ref"))}</label><input class="control" id="payRefInput" value="${s(a.ref==="—"?"":a.ref)}" placeholder="${s(e("payments.bank_ref_ph"))}" /></div>
      <div class="detail-box" style="grid-column:span 2"><label>${s(e("payments.admin_note"))}</label><input class="control" id="payNoteInput" value="${s(a.admin_note||"")}" /></div>
    </div>`:""}
    <div class="journey-section"><div class="journey-section__title">${s(e("payments.checklist"))}</div><div class="pay-review__checks">${o}</div><p class="pay-review__check-note">${s(e("payments.check_note"))}</p></div>
    ${l?"":`<p class="card-subtitle">${s(e("payments.no_order_access"))}</p>`}
  </div>`,u=[l?`<a class="btn" href="${s(n)}" target="_blank" rel="noopener">${s(e("payments.open_order"))}</a>`:"",i?`<button type="button" class="btn" id="payMarkProcessing">${s(e("payments.mark_processing"))}</button>`:"",i?`<button type="button" class="btn danger" id="payMarkHold">${s(e("payments.mark_hold"))}</button>`:"",i?`<button type="button" class="btn success" id="payMarkPaid">${s(e("payments.mark_paid"))}</button>`:"",`<button type="button" class="btn" data-action-drawer-close>${s(e("payments.close"))}</button>`].filter(Boolean).join("");se(e("payments.review_title"),e("payments.review_sub",{code:a.code,customer:a.customer}),d,u,"Pay Verify");const p=c("#paymentProofImage");if(p){const m=()=>{p.closest("a").hidden=!0,c("#paymentProofError").hidden=!1};p.onerror=m,p.complete&&!p.naturalWidth&&m()}setTimeout(()=>{const m=c("#payMarkProcessing");m&&(m.onclick=()=>oa(a,"processing"));const v=c("#payMarkHold");v&&(v.onclick=()=>oa(a,"canceled"));const b=c("#payMarkPaid");b&&(b.onclick=()=>oa(a,"paid"))},0)}async function oa(t,a){const n=U(_.orderPaymentStatusUrlTemplate,t.id);if(!n||!_.canEditOrder){$(e("payments.update_error"));return}const i=c("#payRefInput"),l=c("#payNoteInput"),o=i?i.value.trim():"",d=l?l.value.trim():"";if(t.needs_reference&&(a==="paid"||a==="processing")&&!o&&(t.ref==="—"||!t.ref)){$(e("payments.ref_required"));return}try{const u=await fetch(n,{method:"PUT",headers:D(!0),credentials:"same-origin",body:JSON.stringify({payment_status:a,transaction_id:o||void 0,admin_note:d||void 0})}),p=await u.json().catch(()=>({}));if(!u.ok){$(p.message||e("payments.update_error"));return}R(),$(p.message||e("payments.updated")),await z()}catch(u){console.error(u),$(e("payments.update_error"))}}function bs(){B.innerHTML=`<div class="cin-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${s(e("checkin.title"))}</h1>
        <div class="page-subtitle">${s(e("checkin.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="cinRefresh">${s(e("checkin.refresh"))}</button>
        ${_.canConfirmCheckin?`<button type="button" class="btn primary" id="cinScan">${s(e("checkin.scan"))}</button>`:""}
        ${_.canViewTreatments&&_.treatmentReservationsUrl?`<a class="btn primary" href="${s(_.treatmentReservationsUrl)}" target="_blank" rel="noopener">${s(e("checkin.open_crm"))}</a>`:""}
      </div>
    </div>
    <div class="pay-metrics" id="cinMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${s(e("checkin.subtitle"))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="cinResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="cinTabs" role="group"></div>
        <label class="lead-search" for="cinSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="cinSearch" aria-label="${s(e("checkin.search_placeholder"))}" type="search" autocomplete="off" placeholder="${s(e("checkin.search_placeholder"))}" value="${s(r.checkinSearch||"")}" />
        </label>
        <div class="lead-filter-grid ops-filter-grid">
          <label class="lead-field"><span class="lead-field__label">${s(e("checkin.filter_scope"))}</span>
            <select class="lead-field__control" id="cinScope">
              <option value="pipeline"${r.checkinScope==="pipeline"?" selected":""}>${s(e("checkin.scope_pipeline"))}</option>
              <option value="day"${r.checkinScope==="day"?" selected":""}>${s(e("checkin.scope_day"))}</option>
            </select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${s(e("checkin.filter_date"))}</span>
            <input class="lead-field__control" id="cinDate" type="date" value="${s(r.checkinDate||"")}" />
          </label>
          <label class="lead-field"><span class="lead-field__label">${s(e("checkin.filter_beautician"))}</span>
            <select class="lead-field__control" id="cinBeautician"></select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${s(e("checkin.filter_branch"))}</span>
            <select class="lead-field__control" id="cinBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="cinTableMount"></div>
    </section>
  </div>`;const t=c("#cinRefresh");t&&(t.onclick=()=>O());const a=c("#cinScan");a&&(a.onclick=ws),gs(),O()}function gs(){const t=c("#cinSearch");t&&(t.oninput=()=>{clearTimeout(Ha),Ha=setTimeout(()=>{r.checkinSearch=t.value.trim(),r.checkinPage=1,O()},320)});const a=c("#cinScope");a&&(a.onchange=()=>{r.checkinScope=a.value,a.value==="pipeline"&&(r.checkinStatus="live"),r.checkinPage=1,O()});const n=c("#cinDate");n&&(n.onchange=()=>{r.checkinDate=n.value,r.checkinScope="day",c("#cinScope").value="day",r.checkinPage=1,O()});const i=c("#cinBeautician");i&&(i.onchange=()=>{r.checkinBeautician=i.value,r.checkinPage=1,O()});const l=c("#cinBranch");l&&(l.onchange=()=>{r.checkinBranch=l.value,r.checkinPage=1,O()})}async function O(){const t=++Ue;Ee=!1;const a=_.checkinUrl||"",n=c("#cinTableMount");if(!a){n&&(n.innerHTML=`<div class="pay-empty"><strong>${s(e("checkin.load_error"))}</strong></div>`);return}re=!0,Ga(),Ja();const i=new URLSearchParams;r.checkinSearch&&i.set("q",r.checkinSearch),i.set("status",r.checkinStatus||"live"),i.set("scope",r.checkinScope||"day"),r.checkinDate&&i.set("date",r.checkinDate);const l=r.checkinBranch!=="all"?r.checkinBranch:r.branch||"all";l&&l!=="all"&&i.set("branch",l),r.checkinBeautician&&r.checkinBeautician!=="all"&&i.set("beautician",r.checkinBeautician),i.set("page",String(r.checkinPage||1)),i.set("per_page","25");try{const o=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!o.ok)throw new Error("checkin "+o.status);const d=await o.json();if(t!==Ue)return;Be=Array.isArray(d.data)?d.data:[],$a=Object.assign({live:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0},d.meta&&d.meta.summary||{}),ua=Object.assign({statuses:[],beauticians:[],branches:[]},d.filters||{}),wa={current_page:d.meta&&d.meta.current_page||1,last_page:d.meta&&d.meta.last_page||1,total:d.meta&&d.meta.total||0}}catch(o){if(t!==Ue)return;Ee=!0,console.error(o),Be=[],$(e("checkin.load_error"))}finally{if(t!==Ue)return;re=!1,Ga(),ys(),fs(),Ja()}}function Ga(){const t=c("#cinMetrics");if(!t)return;const a=$a;t.setAttribute("aria-busy",String(re));const n=i=>re||Ee?"—":h(i);t.innerHTML=`
    <div class="pay-metric"><span>${s(e("checkin.stat_live"))}</span><strong>${n(a.live)}</strong></div>
    <div class="pay-metric"><span>${s(e("checkin.stat_waiting"))}</span><strong>${n(a.waiting)}</strong></div>
    <div class="pay-metric"><span>${s(e("checkin.stat_treatment"))}</span><strong>${n(a.in_treatment)}</strong></div>
    <div class="pay-metric"><span>${s(e("checkin.stat_completed"))}</span><strong>${n(a.completed)}</strong></div>`}function ys(){const t=c("#cinTabs");if(!t)return;const a=$a,n=[["live",e("checkin.tab_live"),a.live],["waiting",e("checkin.tab_waiting"),a.waiting],["in_progress",e("checkin.tab_treatment"),a.in_treatment],["completed",e("checkin.tab_completed"),a.completed],["all",e("checkin.tab_all"),null]];t.innerHTML=n.filter(([i])=>r.checkinScope!=="pipeline"||!["completed","all"].includes(i)).map(([i,l,o])=>{const d=r.checkinStatus===i?"active":"",u=o==null?"":` (${h(o)})`;return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-cintab="${i}">${s(l)}${u}</button>`}).join(""),T("[data-cintab]",t).forEach(i=>i.onclick=()=>{r.checkinStatus=i.dataset.cintab,["completed","all"].includes(r.checkinStatus)&&(r.checkinScope="day",c("#cinScope").value="day"),r.checkinPage=1,O()})}function fs(){const t=c("#cinBeautician");if(t){const i=r.checkinBeautician||"all";t.innerHTML=`<option value="all">${s(e("checkin.all_beauticians"))}</option>`+(ua.beauticians||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${s(l.name)}</option>`).join("")}const a=c("#cinBranch");if(a){const i=r.checkinBranch||"all";a.innerHTML=`<option value="all">${s(e("checkin.all_branches"))}</option>`+(ua.branches||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${s(l.name)}</option>`).join("")}const n=c("#cinResultCount");n&&(n.textContent=e("checkin.result_count",{count:Ee?"—":h(wa.total)}))}function Ja(){const t=c("#cinTableMount");if(!t)return;if(t.setAttribute("aria-busy",String(re)),Ee){t.innerHTML=`<div class="pay-empty" role="alert"><strong>${s(e("checkin.load_error"))}</strong><button type="button" class="btn" id="cinRetry">${s(e("checkin.refresh"))}</button></div>`,c("#cinRetry").onclick=()=>O();return}if(re){t.innerHTML=`<div class="pay-empty" role="status">${s(e("checkin.loading"))}</div>`;return}if(!Be.length){t.innerHTML=`<div class="pay-empty"><strong>${s(e("checkin.empty"))}</strong>${s(e("checkin.empty_hint"))}</div>`;return}const a=Be.map(n=>`<tr>
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${s(n.initial||"?")}</div><div class="person-cell__text"><strong>${s(n.name||"")}</strong><small>${s(n.code||"")} · ${s(n.phone||"")}</small></div></div></td>
    <td>${s(n.date_label||"")} · ${s(n.time||"")}</td>
    <td>${s(n.branch_name||n.branch||"—")}</td>
    <td>${s(n.beautician||"—")}</td>
    <td>${s(n.treatment||"—")}</td>
    <td>${P(n.payment_label)}</td>
    <td>${P(n.clearance_label)}</td>
    <td>${s(n.waiting_label||"—")}</td>
    <td>${P(n.status_label)}</td>
    <td><button type="button" class="btn small soft" data-cin-view="${n.id}">${s(e("checkin.view"))}</button></td>
  </tr>`).join("");t.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${s(e("checkin.col_customer"))}</th><th>${s(e("checkin.col_time"))}</th>
    <th>${s(e("checkin.col_branch"))}</th><th>${s(e("checkin.col_beautician"))}</th>
    <th>${s(e("checkin.col_treatment"))}</th><th>${s(e("checkin.col_payment"))}</th>
    <th>${s(e("checkin.col_clearance"))}</th><th>${s(e("checkin.col_wait"))}</th>
    <th>${s(e("checkin.col_status"))}</th><th>${s(e("checkin.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${G("cin",wa)}`,T("[data-cin-view]").forEach(n=>n.onclick=()=>ks(n.dataset.cinView)),J("cin",n=>(r.checkinPage=n,O()))}function $s(t){try{const a=new URL(String(t||"").trim(),window.location.origin),n=new URL(_.checkinPassBaseUrl,window.location.origin);return a.origin===n.origin&&a.pathname.startsWith(n.pathname.replace(/\/$/,"")+"/")&&a.searchParams.has("expires")&&a.searchParams.has("signature")}catch{return!1}}function Xe(){cancelAnimationFrame(ma),ma=0,_e&&(_e.getTracks().forEach(a=>a.stop()),_e=null);const t=c("#cinScannerVideo");t&&(t.srcObject=null)}function ws(){const t=`<div class="cin-scanner">
    <p class="cin-scanner__hint">${s(e("checkin.scanner_hint"))}</p>
    <div class="cin-scanner__viewport"><video id="cinScannerVideo" playsinline muted aria-label="${s(e("checkin.scanner_title"))}"></video><div class="cin-scanner__guide" aria-hidden="true"></div></div>
    <p class="cin-scanner__status" id="cinScannerStatus" aria-live="polite"></p>
    <button type="button" class="btn primary" id="cinScannerStart">${s(e("checkin.scanner_start"))}</button>
    <label class="lead-field cin-scanner__manual"><span class="lead-field__label">${s(e("checkin.scanner_manual_label"))}</span>
      <input class="lead-field__control" id="cinScannerInput" type="url" inputmode="url" autocomplete="off" placeholder="${s(e("checkin.scanner_manual_placeholder"))}">
    </label>
  </div>`,a=`<button type="button" class="btn primary" id="cinScannerOpen">${s(e("checkin.scanner_open"))}</button><button type="button" class="btn" data-action-drawer-close>${s(e("checkin.close"))}</button>`;se(e("checkin.scanner_title"),e("checkin.scanner_hint"),t,a,e("nav.checkin")),c("#cinScannerStart").onclick=xt,c("#cinScannerOpen").onclick=()=>_a(c("#cinScannerInput").value),c("#cinScannerInput").onkeydown=n=>{n.key==="Enter"&&(n.preventDefault(),_a(n.currentTarget.value))}}function _a(t){if(!$s(t)){const a=c("#cinScannerStatus");a&&(a.textContent=e("checkin.scanner_invalid"));return}Xe(),window.location.assign(String(t).trim())}async function xt(){const t=c("#cinScannerStatus"),a=c("#cinScannerStart");if(!("BarcodeDetector"in window)||!navigator.mediaDevices?.getUserMedia){t.textContent=e("checkin.scanner_unsupported");return}a.disabled=!0,t.textContent=e("common.loading");try{if(!(BarcodeDetector.getSupportedFormats?await BarcodeDetector.getSupportedFormats():["qr_code"]).includes("qr_code"))throw new Error("QR format is unavailable");const i=new BarcodeDetector({formats:["qr_code"]});_e=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:"environment"}},audio:!1});const l=c("#cinScannerVideo");l.srcObject=_e,await l.play(),a.textContent=e("checkin.scanner_stop"),a.disabled=!1,a.onclick=()=>{Xe(),a.textContent=e("checkin.scanner_start"),a.onclick=xt},t.textContent=e("checkin.scanner_hint");const o=async()=>{if(!(!_e||!l.isConnected)){try{const d=await i.detect(l);if(d[0]?.rawValue){_a(d[0].rawValue);return}}catch(d){console.error(d)}ma=requestAnimationFrame(o)}};o()}catch(n){console.error(n),Xe(),a.disabled=!1,t.textContent=e("checkin.scanner_unsupported")}}function ks(t){const a=Be.find(f=>Number(f.id)===Number(t));if(!a)return;const n=a.order_id&&_.orderShowUrlTemplate?U(_.orderShowUrlTemplate,a.order_id):"",i=a.status||"pending",l=!!a.checked_in_at,o=[{label:e("checkin.step_booked"),state:i==="pending"?"active":"done",time:""},{label:e("checkin.step_checked_in"),state:l?"done":"",time:l&&a.checked_in_label&&a.checked_in_label!=="—"?a.checked_in_label:""},{label:e("checkin.step_treatment"),state:i==="in_progress"?"active":i==="completed"?"done":"",time:""},{label:e("checkin.step_completed"),state:i==="completed"?"active done":"",time:""}],d=`<div class="cin-preview__block cin-timeline"><div class="journey-section__title">${s(e("checkin.timeline"))}</div>
    <div class="timeline">${o.map(f=>`<div class="timeline-step ${f.state}"><div class="timeline-dot">${f.state.includes("done")?"✓":f.state.includes("active")?"●":""}</div><span>${s(f.label)}${f.time?` <em>· ${s(f.time)}</em>`:""}</span></div>`).join("")}</div></div>`,u=`${a.phone||a.email?`<div class="cin-preview__contact">
    ${a.phone?`<a href="tel:${s(a.phone)}"><span aria-hidden="true">📞</span>${s(a.phone)}</a>`:""}
    ${a.email?`<a href="mailto:${s(a.email)}"><span aria-hidden="true">✉️</span>${s(a.email)}</a>`:""}
  </div>`:""}`,p=`<div class="cin-preview__block cin-preview__qr">
    <div class="journey-section__title">${s(e("checkin.qr_title"))}</div>
    <div class="cin-preview__qr-box"><canvas id="cinQrCanvas" role="img" aria-label="${s(e("checkin.qr_title"))}"></canvas></div>
    <div class="cin-preview__qr-code">${s(a.code||"")}</div>
    <p class="cin-preview__qr-hint">${s(e("checkin.qr_hint"))}</p>
    <button type="button" class="btn small soft" id="cinCopyCode">${s(e("checkin.copy_code"))}</button>
  </div>`,m=`<div class="cin-preview__block"><div class="journey-section__title">${s(e("checkin.details"))}</div>
    <div class="journey-kv">
      <div><span>${s(e("checkin.col_treatment"))}</span><strong>${s(a.treatment||"—")}</strong></div>
      <div><span>${s(e("checkin.col_beautician"))}</span><strong>${s(a.beautician||"—")}</strong></div>
      <div><span>${s(e("checkin.col_branch"))}</span><strong>${s(a.branch_name||a.branch||"—")}</strong></div>
      <div><span>${s(e("checkin.col_payment"))}</span><strong>${s(a.payment_label||"—")}</strong></div>
      <div><span>${s(e("checkin.col_date"))}</span><strong>${s((a.date_label||"")+" "+(a.time||""))}</strong></div>
      <div><span>${s(e("checkin.col_checked_in"))}</span><strong>${s(a.checked_in_label||"—")}</strong></div>
      <div><span>${s(e("checkin.col_wait"))}</span><strong>${s(a.waiting_label||"—")}</strong></div>
      <div><span>${s(e("checkin.col_clearance"))}</span><strong>${s(a.clearance_label||"—")}</strong></div>
    </div></div>`,v=`<div class="pay-review cin-preview">
    <div class="pay-review__hero pay-review__hero--cin">
      <div class="pay-review__avatar cin-avatar" aria-hidden="true">${s(a.initial||"?")}</div>
      <div style="min-width:0;flex:1">
        <div class="pay-id">${s(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${s(a.name||"")}</strong>
        <div class="pay-method">${s(a.date_label||"")} ${s(a.time||"")} · ${s(a.branch_name||a.branch||"—")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${P(a.status_label)}${P(a.arrival_label)}</div>
      </div>
    </div>
    ${u}${p}${d}${m}
  </div>`,b=[n&&_.canViewOrder?`<a class="btn primary" href="${s(n)}" target="_blank" rel="noopener">${s(e("checkin.open_order"))}</a>`:"",_.canViewTreatments&&_.treatmentReservationsUrl?`<a class="btn" href="${s(_.treatmentReservationsUrl)}" target="_blank" rel="noopener">${s(e("checkin.open_crm"))}</a>`:"",`<button type="button" class="btn" data-action-drawer-close>${s(e("checkin.close"))}</button>`].filter(Boolean).join("");se(e("checkin.title"),a.code+" · "+a.name,v,b,e("nav.checkin"));const w=c("#cinQrCanvas");if(w&&window.QRCentral)try{QRCentral.render(w,a.checkin_pass_url||"")}catch(f){console.error(f),w.closest(".cin-preview__qr-box")?.classList.add("hidden")}const g=c("#cinCopyCode");g&&(g.onclick=()=>Ss(a.code||"",e("checkin.copied")))}function Ss(t,a){const n=()=>$(a);if(!t){$(e("checkin.copy_missing"));return}navigator.clipboard&&window.isSecureContext?navigator.clipboard.writeText(t).then(n).catch(()=>Xa(t,n)):Xa(t,n)}function Xa(t,a){const n=document.createElement("textarea");n.value=t,n.setAttribute("readonly",""),n.style.position="fixed",n.style.opacity="0",document.body.appendChild(n),n.select();try{document.execCommand("copy"),a()}catch{$(e("checkin.copy_missing"))}document.body.removeChild(n)}function Ms(){B.innerHTML=`<div class="clr-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${s(e("clearance.title"))}</h1>
        <div class="page-subtitle">${s(e("clearance.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="clrRefresh">${s(e("clearance.refresh"))}</button>
        <button type="button" class="btn" data-jump="payments">${s(e("clearance.open_payments"))}</button>
        ${_.canViewTreatments&&_.treatmentReservationsUrl?`<a class="btn primary" href="${s(_.treatmentReservationsUrl)}" target="_blank" rel="noopener">${s(e("clearance.open_crm"))}</a>`:""}
      </div>
    </div>
    <div class="pay-metrics" id="clrMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${s(e("clearance.subtitle"))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="clrResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="clrTabs" role="group"></div>
        <label class="lead-search" for="clrSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="clrSearch" aria-label="${s(e("clearance.search_placeholder"))}" type="search" autocomplete="off" placeholder="${s(e("clearance.search_placeholder"))}" value="${s(r.clearanceSearch||"")}" />
        </label>
        <div class="lead-filter-grid ops-filter-grid ops-filter-grid--two">
          <label class="lead-field"><span class="lead-field__label">${s(e("clearance.filter_beautician"))}</span>
            <select class="lead-field__control" id="clrBeautician"></select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${s(e("clearance.filter_branch"))}</span>
            <select class="lead-field__control" id="clrBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="clrTableMount"></div>
    </section>
  </div>`;const t=c("#clrRefresh");t&&(t.onclick=()=>te()),X(),Ts(),te()}function Ts(){const t=c("#clrSearch");t&&(t.oninput=()=>{clearTimeout(Da),Da=setTimeout(()=>{r.clearanceSearch=t.value.trim(),r.clearancePage=1,te()},320)});const a=c("#clrBeautician");a&&(a.onchange=()=>{r.clearanceBeautician=a.value,r.clearancePage=1,te()});const n=c("#clrBranch");n&&(n.onchange=()=>{r.clearanceBranch=n.value,r.clearancePage=1,te()})}async function te(){const t=++Ie;qe=!1;const a=_.clearanceUrl||"",n=c("#clrTableMount");if(!a){n&&(n.innerHTML=`<div class="pay-empty"><strong>${s(e("clearance.load_error"))}</strong></div>`);return}oe=!0,Qa(),Za();const i=new URLSearchParams;r.clearanceSearch&&i.set("q",r.clearanceSearch),r.clearanceState&&i.set("state",r.clearanceState);const l=r.clearanceBranch!=="all"?r.clearanceBranch:r.branch||"all";l&&l!=="all"&&i.set("branch",l),r.clearanceBeautician&&r.clearanceBeautician!=="all"&&i.set("beautician",r.clearanceBeautician),i.set("page",String(r.clearancePage||1)),i.set("per_page","25");try{const o=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!o.ok)throw new Error("clearance "+o.status);const d=await o.json();if(t!==Ie)return;Ae=Array.isArray(d.data)?d.data:[],ka=Object.assign({waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0},d.meta&&d.meta.summary||{}),va=Object.assign({states:[],beauticians:[],branches:[]},d.filters||{}),Sa={current_page:d.meta&&d.meta.current_page||1,last_page:d.meta&&d.meta.last_page||1,total:d.meta&&d.meta.total||0}}catch(o){if(t!==Ie)return;qe=!0,console.error(o),Ae=[],$(e("clearance.load_error"))}finally{if(t!==Ie)return;oe=!1,Qa(),Ls(),Cs(),Za()}}function Qa(){const t=c("#clrMetrics");if(!t)return;const a=ka;t.setAttribute("aria-busy",String(oe));const n=i=>oe||qe?"—":h(i);t.innerHTML=`
    <div class="pay-metric"><span>${s(e("clearance.stat_waiting"))}</span><strong>${n(a.waiting)}</strong></div>
    <div class="pay-metric"><span>${s(e("clearance.stat_blocked"))}</span><strong>${n(a.blocked)}</strong></div>
    <div class="pay-metric"><span>${s(e("clearance.stat_treatment"))}</span><strong>${n(a.in_treatment)}</strong></div>
    <div class="pay-metric"><span>${s(e("clearance.stat_done"))}</span><strong>${n(a.done_today)}</strong></div>`}function Ls(){const t=c("#clrTabs");if(!t)return;const a=ka,n=[["waiting",e("clearance.tab_waiting"),a.waiting],["blocked",e("clearance.tab_blocked"),a.blocked],["in_treatment",e("clearance.tab_treatment"),a.in_treatment],["all_queue",e("clearance.tab_queue"),a.queue],["done",e("clearance.tab_done"),a.done_today]];t.innerHTML=n.map(([i,l,o])=>{const d=r.clearanceState===i?"active":"";return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-clrtab="${i}">${s(l)} (${h(o||0)})</button>`}).join(""),T("[data-clrtab]",t).forEach(i=>i.onclick=()=>{r.clearanceState=i.dataset.clrtab,r.clearancePage=1,te()})}function Cs(){const t=c("#clrBeautician");if(t){const i=r.clearanceBeautician||"all";t.innerHTML=`<option value="all">${s(e("clearance.all_beauticians"))}</option>`+(va.beauticians||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${s(l.name)}</option>`).join("")}const a=c("#clrBranch");if(a){const i=r.clearanceBranch||"all";a.innerHTML=`<option value="all">${s(e("clearance.all_branches"))}</option>`+(va.branches||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${s(l.name)}</option>`).join("")}const n=c("#clrResultCount");n&&(n.textContent=e("clearance.result_count",{count:qe?"—":h(Sa.total)}))}function Za(){const t=c("#clrTableMount");if(!t)return;if(t.setAttribute("aria-busy",String(oe)),qe){t.innerHTML=`<div class="pay-empty" role="alert"><strong>${s(e("clearance.load_error"))}</strong><button type="button" class="btn" id="clrRetry">${s(e("clearance.refresh"))}</button></div>`,c("#clrRetry").onclick=()=>te();return}if(oe){t.innerHTML=`<div class="pay-empty" role="status">${s(e("clearance.loading"))}</div>`;return}if(!Ae.length){t.innerHTML=`<div class="pay-empty"><strong>${s(e("clearance.empty"))}</strong>${s(e("clearance.empty_hint"))}</div>`;return}const a=Ae.map(n=>`<tr>
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${s(n.initial||"?")}</div><div class="person-cell__text"><strong>${s(n.name||"")}</strong><small>${s(n.code||"")} · ${s(n.phone||"")}</small></div></div></td>
    <td>${s(n.date_label||"")} · ${s(n.time||"")}</td>
    <td>${s(n.branch_name||n.branch||"—")}</td>
    <td>${s(n.treatment||"—")}</td>
    <td>${P(n.payment_label)}</td>
    <td>${P(n.clearance_label)}</td>
    <td><button type="button" class="btn small soft" data-clr-view="${n.id}">${s(e("clearance.view"))}</button></td>
  </tr>`).join("");t.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${s(e("clearance.col_customer"))}</th><th>${s(e("clearance.col_time"))}</th>
    <th>${s(e("clearance.col_branch"))}</th><th>${s(e("clearance.col_treatment"))}</th>
    <th>${s(e("clearance.col_payment"))}</th><th>${s(e("clearance.col_clearance"))}</th>
    <th>${s(e("clearance.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${G("clr",Sa)}`,T("[data-clr-view]").forEach(n=>n.onclick=()=>xs(n.dataset.clrView)),J("clr",n=>(r.clearancePage=n,te()))}function xs(t){const a=Ae.find(o=>Number(o.id)===Number(t));if(!a)return;const n=a.order_id&&_.orderShowUrlTemplate?U(_.orderShowUrlTemplate,a.order_id):"",i=`<div class="pay-review">
    <div class="pay-review__hero"><div class="pay-review__avatar">${s(a.initial||"?")}</div>
      <div style="min-width:0;flex:1"><div class="pay-id">${s(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${s(a.name||"")}</strong>
        <div class="pay-method">${s(a.phone||"—")} · ${s(a.treatment||"")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${P(a.clearance_label)}${P(a.payment_label)}</div>
      </div></div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${s(e("clearance.col_time"))}</label><strong>${s((a.date_label||"")+" "+(a.time||""))}</strong></div>
      <div class="pay-review__card"><label>${s(e("clearance.col_branch"))}</label><strong>${s(a.branch_name||a.branch||"—")}</strong></div>
      <div class="pay-review__card"><label>${s(e("checkin.col_beautician"))}</label><strong>${s(a.beautician||"—")}</strong></div>
    </div></div>`,l=[n&&_.canViewOrder?`<a class="btn primary" href="${s(n)}" target="_blank" rel="noopener">${s(e("clearance.open_order"))}</a>`:"",_.canViewTreatments&&_.treatmentReservationsUrl?`<a class="btn" href="${s(_.treatmentReservationsUrl)}" target="_blank" rel="noopener">${s(e("clearance.open_crm"))}</a>`:"",`<button type="button" class="btn" data-action-drawer-close>${s(e("clearance.close"))}</button>`].filter(Boolean).join("");se(e("clearance.title"),a.code+" · "+a.name,i,l,e("nav.clearance")),setTimeout(()=>{T("#actionDrawerFoot [data-jump]").forEach(o=>o.onclick=()=>{R(),I(o.dataset.jump)})},0)}function Ps(){B.innerHTML=`<div class="wal-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${s(e("wallet.title"))}</h1>
        <div class="page-subtitle">${s(e("wallet.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="walRefresh">${s(e("wallet.refresh"))}</button>
        ${_.canViewLoyalty&&_.loyaltyMembersUrl?`<a class="btn primary" href="${s(_.loyaltyMembersUrl)}" target="_blank" rel="noopener">${s(e("wallet.open_loyalty"))}</a>`:""}
      </div>
    </div>
    <div class="pay-metrics" id="walMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${s(e("wallet.subtitle"))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="walResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="walTabs" role="group"></div>
        <label class="lead-search" for="walSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="walSearch" aria-label="${s(e("wallet.search_placeholder"))}" type="search" autocomplete="off" placeholder="${s(e("wallet.search_placeholder"))}" value="${s(r.walletSearch||"")}" />
        </label>
        <div class="lead-filter-grid" style="grid-template-columns:minmax(0,1fr)">
          <label class="lead-field"><span class="lead-field__label">${s(e("wallet.filter_tier"))}</span>
            <select class="lead-field__control" id="walTier"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="walTableMount"></div>
    </section>
  </div>`;const t=c("#walRefresh");t&&(t.onclick=()=>ce()),js(),ce()}function js(){const t=c("#walSearch");t&&(t.oninput=()=>{clearTimeout(Na),Na=setTimeout(()=>{r.walletSearch=t.value.trim(),r.walletPage=1,ce()},320)});const a=c("#walTier");a&&(a.onchange=()=>{r.walletTier=a.value,r.walletPage=1,ce()})}async function ce(){const t=++Re;je=!1;const a=_.walletUrl||"",n=c("#walTableMount");if(!a){n&&(n.innerHTML=`<div class="pay-empty"><strong>${s(e("wallet.load_error"))}</strong></div>`);return}le=!0,et(),at();const i=new URLSearchParams;r.walletSearch&&i.set("q",r.walletSearch),r.walletSegment&&r.walletSegment!=="all"&&i.set("segment",r.walletSegment),r.walletTier&&r.walletTier!=="all"&&i.set("tier",r.walletTier),i.set("page",String(r.walletPage||1)),i.set("per_page","25");try{const l=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("wallet "+l.status);const o=await l.json();if(t!==Re)return;Pe=Array.isArray(o.data)?o.data:[],ya=Object.assign({members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0},o.meta&&o.meta.summary||{}),ot=Object.assign({segments:[],tiers:[]},o.filters||{}),fa={current_page:o.meta&&o.meta.current_page||1,last_page:o.meta&&o.meta.last_page||1,total:o.meta&&o.meta.total||0}}catch(l){if(t!==Re)return;je=!0,console.error(l),Pe=[],$(e("wallet.load_error"))}finally{if(t!==Re)return;le=!1,et(),Bs(),Es(),at()}}function et(){const t=c("#walMetrics");if(!t)return;const a=ya;t.setAttribute("aria-busy",String(le));const n=i=>le||je?"—":h(i);t.innerHTML=`
    <div class="pay-metric"><span>${s(e("wallet.stat_members"))}</span><strong>${n(a.members)}</strong></div>
    <div class="pay-metric"><span>${s(e("wallet.stat_balance"))}</span><strong>${n(a.with_balance)}</strong></div>
    <div class="pay-metric"><span>${s(e("wallet.stat_points"))}</span><strong>${n(a.points_outstanding)}</strong></div>
    <div class="pay-metric"><span>${s(e("wallet.stat_stamp"))}</span><strong>${n(a.stamp_ready)}</strong></div>`}function Bs(){const t=c("#walTabs");if(!t)return;const a=ya,n=[["all",e("wallet.tab_all"),a.members],["active",e("wallet.tab_active"),a.with_balance],["zero",e("wallet.tab_zero"),a.zero_balance],["stamp_ready",e("wallet.tab_stamp"),a.stamp_ready]];t.innerHTML=n.map(([i,l,o])=>{const d=r.walletSegment===i?"active":"";return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-waltab="${i}">${s(l)} (${h(o||0)})</button>`}).join(""),T("[data-waltab]",t).forEach(i=>i.onclick=()=>{r.walletSegment=i.dataset.waltab,r.walletPage=1,ce()})}function Es(){const t=c("#walTier");if(t){const n=r.walletTier||"all";t.innerHTML=`<option value="all">${s(e("wallet.all_tiers"))}</option>`+(ot.tiers||[]).map(i=>`<option value="${i.id}"${String(n)===String(i.id)?" selected":""}>${s(i.name)}</option>`).join("")}const a=c("#walResultCount");a&&(a.textContent=e("wallet.result_count",{count:je?"—":h(fa.total)}))}function at(){const t=c("#walTableMount");if(!t)return;if(t.setAttribute("aria-busy",String(le)),je){t.innerHTML=`<div class="pay-empty" role="alert"><strong>${s(e("wallet.load_error"))}</strong><button type="button" class="btn" id="walRetry">${s(e("wallet.refresh"))}</button></div>`,c("#walRetry").onclick=()=>ce();return}if(le){t.innerHTML=`<div class="pay-empty" role="status">${s(e("wallet.loading"))}</div>`;return}if(!Pe.length){t.innerHTML=`<div class="pay-empty"><strong>${s(e("wallet.empty"))}</strong>${s(e("wallet.empty_hint"))}</div>`;return}const a=Pe.map(n=>{const i=n.avatar_url?`<div class="mini-avatar mini-avatar--photo"><img src="${s(n.avatar_url)}" alt=""></div>`:`<div class="mini-avatar">${s(n.initial||"?")}</div>`,l=n.stamp_ready?P(e("wallet.chip_stamp",{count:n.stamp_ready})):"";return`<tr>
      <td><strong>${s(n.code||"")}</strong></td>
      <td><div class="person-cell person-cell--lead">${i}<div class="person-cell__text"><strong>${s(n.name||"")}</strong><small>${s(n.phone||n.email||"")}</small></div></div></td>
      <td>${n.tier?P(n.tier):"—"}</td>
      <td class="is-num"><strong>${h(n.balance)}</strong></td>
      <td class="is-num">${N(n.lifetime_spend)}</td>
      <td>${l||h(n.stamp_active||0)}</td>
      <td><button type="button" class="btn small soft" data-wal-view="${n.id}">${s(e("wallet.view"))}</button></td>
    </tr>`}).join("");t.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${s(e("wallet.col_id"))}</th><th>${s(e("wallet.col_customer"))}</th>
    <th>${s(e("wallet.col_tier"))}</th><th class="is-num">${s(e("wallet.col_balance"))}</th>
    <th class="is-num">${s(e("wallet.col_spend"))}</th><th>${s(e("wallet.col_stamps"))}</th>
    <th>${s(e("wallet.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${G("wal",fa)}`,T("[data-wal-view]").forEach(n=>n.onclick=()=>As(n.dataset.walView)),J("wal",n=>(r.walletPage=n,ce()))}let Me=null,La="";function As(t){const a=Pe.find(p=>Number(p.id)===Number(t));if(!a)return;R(),V(),Me=document.activeElement,La=document.body.style.overflow,document.body.style.overflow="hidden";const n=_.loyaltyMemberShowUrlTemplate?U(_.loyaltyMemberShowUrlTemplate,a.id):"",i=a.customer_id&&_.userEditUrlTemplate?U(_.userEditUrlTemplate,a.customer_id):"",l=(a.recent||[]).length?`<ol class="wallet-transactions">${a.recent.map(p=>`<li class="wallet-transaction">
        <div class="wallet-transaction__description"><strong>${s(p.description||p.type||"")}</strong><time>${s(p.created_label||"")}</time></div>
        <div class="wallet-transaction__amount"><strong class="${Number(p.points)>0?"is-credit":""}">${Number(p.points)>0?"+":""}${h(p.points)} <span>${s(e("wallet.col_balance"))}</span></strong><small>${s(e("wallet.transaction_balance",{balance:h(p.balance_after)}))}</small></div>
      </li>`).join("")}</ol>`:`<div class="pay-empty">${s(e("wallet.no_recent"))}</div>`,o=a.avatar_url?`<img class="wallet-detail__avatar" src="${s(a.avatar_url)}" alt="" />`:`<div class="wallet-detail__avatar" aria-hidden="true">${s(a.initial||"?")}</div>`,d=c("#leadDrawer");d.classList.add("wallet-drawer"),d.setAttribute("role","dialog"),d.setAttribute("aria-modal","true"),d.setAttribute("aria-labelledby","drawerName"),c("#leadDrawer .eyebrow").textContent=e("nav.wallet"),c("#drawerName").textContent=e("wallet.title"),c("#drawerClose").setAttribute("aria-label",e("wallet.close")),c("#drawerBody").innerHTML=`<div class="wallet-detail">
    <section class="wallet-detail__customer">${o}<div class="wallet-detail__identity">
      <span class="pay-id">${s(a.code||"")}</span>
      <h3>${s(a.name||"")}</h3>
      <p>${s(a.phone||"—")}</p><p>${s(a.email||"—")}</p>
      <div class="wallet-detail__badges">${a.tier?P(a.tier):""}${P(a.segment_label)}</div>
    </div></section>
    <section class="wallet-detail__balance"><span>${s(e("wallet.col_balance"))}</span><strong>${h(a.balance)}</strong></section>
    <div class="wallet-detail__metrics">
      <section><span>${s(e("wallet.col_spend"))}</span><strong>${N(a.lifetime_spend)}</strong></section>
      <section><span>${s(e("wallet.col_stamps"))}</span><strong>${s(e("wallet.stamp_summary",{active:h(a.stamp_active),ready:h(a.stamp_ready)}))}</strong></section>
    </div>
    <section class="wallet-detail__history"><h3>${s(e("wallet.detail_recent"))}</h3>${l}</section>
  </div>`;const u=document.createElement("div");u.id="walletDrawerFoot",u.className="wallet-drawer__footer",u.innerHTML=[n&&_.canShowLoyaltyMember?`<a class="btn primary" href="${s(n)}" target="_blank" rel="noopener">${s(e("wallet.open_member"))}</a>`:"",i&&_.canViewUser?`<a class="btn" href="${s(i)}" target="_blank" rel="noopener">${s(e("wallet.open_profile"))}</a>`:"",`<button type="button" class="btn" data-wallet-close>${s(e("wallet.close"))}</button>`].filter(Boolean).join(""),d.appendChild(u),c("[data-wallet-close]",u).onclick=V,d.classList.add("show"),d.setAttribute("aria-hidden","false"),d.inert=!1,c(".app-shell").inert=!0,c("#drawerBackdrop").classList.add("show"),c("#drawerBody").scrollTop=0,c("#drawerClose").focus({preventScroll:!0})}document.addEventListener("keydown",t=>{const a=c("#actionDrawer.show")||c("#leadDrawer.show");if(!a)return;const n=a.id==="actionDrawer"?R:V;if(t.key==="Escape"){t.preventDefault(),n();return}if(t.key!=="Tab")return;const i=T('button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex="0"]',a).filter(d=>d.getClientRects().length),l=i[0],o=i[i.length-1];if(!l){t.preventDefault();return}t.shiftKey&&(document.activeElement===l||!a.contains(document.activeElement))?(t.preventDefault(),o.focus()):!t.shiftKey&&(document.activeElement===o||!a.contains(document.activeElement))&&(t.preventDefault(),l.focus())});const ge=Object.fromEntries(["beauticians","branches","audit"].map(t=>[t,{q:"",sort:"revenue",page:1}]));let Oe=0,tt=null,ee=null;function qs(){Ca("beauticians")}function Ns(){Ca("branches")}function Hs(){Ca("audit")}function Ca(t){const a=ge[t];B.innerHTML=`<div class="report-shell">
    <div class="page-head"><div><h1 class="page-title">${s(e("nav."+t))}</h1><p class="page-subtitle">${s(e("reporting."+(t==="audit"?"audit_subtitle":"subtitle")))}</p></div>
      <button type="button" class="btn" id="reportRefresh">${s(e("reporting.refresh"))}</button></div>
    <div class="pay-metrics" id="reportMetrics" aria-live="polite"></div>
    <section class="lead-panel card"><div class="lead-panel__head"><div><h2 class="card-title">${s(e("reporting."+(t==="audit"?"recorded_imports":"performance")))}</h2><p class="lead-panel__sub" id="reportPeriod"></p></div><span class="lead-panel__count" id="reportCount">—</span></div>
      <div class="lead-panel__filters report-filters"><label class="lead-field"><span class="lead-field__label">${s(e("reporting.search"))}</span><input class="lead-field__control" type="search" id="reportSearch" maxlength="150" value="${s(a.q)}" placeholder="${s(e("reporting."+(t==="audit"?"search_batch":"search_name")))}"></label>
      ${t!=="audit"?`<label class="lead-field"><span class="lead-field__label">${s(e("reporting.sort"))}</span><select id="reportSort" class="lead-field__control">${["revenue","leads","conversion","orders"].map(i=>`<option value="${i}"${a.sort===i?" selected":""}>${s(e("reporting."+i))}</option>`).join("")}</select></label>`:""}</div>
      <div id="reportTable" class="lead-panel__body pay-table" aria-live="polite"></div>
    </section>
    <p class="report-note">${s(e("reporting."+(t==="audit"?"audit_note":"methodology")))}</p>
  </div>`,c("#reportRefresh").onclick=()=>Te(),c("#reportSearch").oninput=i=>{a.q=i.target.value,a.page=1,clearTimeout(tt),t==="audit"?tt=setTimeout(()=>{r.view===t&&Te()},350):ee&&Qe(t)};const n=c("#reportSort");n&&(n.onchange=i=>{a.sort=i.target.value,ee&&Qe(t)}),Te()}async function Te(){const t=r.view;if(!ge[t])return;const a=++Oe,n=ge[t],i=c("#reportTable");if(!i)return;ee=null,c("#reportMetrics").innerHTML="",i.setAttribute("aria-busy","true"),i.innerHTML=`<div class="pay-empty" role="status">${s(e("reporting.loading"))}</div>`;const l=new URLSearchParams({view:t,page:String(n.page)});r.period&&l.set("period",r.period),r.branch&&r.branch!=="all"&&l.set("branch",r.branch),t==="audit"&&n.q.trim()&&l.set("q",n.q.trim());try{if(!_.reportingUrl)throw new Error("Missing reporting endpoint");const o=await fetch(_.reportingUrl+"?"+l.toString(),{headers:D(!1),credentials:"same-origin"});if(!o.ok)throw new Error("Reporting "+o.status);const d=await o.json();if(a!==Oe||r.view!==t)return;ee=d;const u=d.meta.summary,p=t==="audit"?["batches","imported","duplicates","invalid"]:["leads","converted","orders","revenue"];c("#reportMetrics").innerHTML=p.map(m=>`<div class="pay-metric"><span>${s(e("reporting."+m))}</span><strong>${m==="revenue"?N(u[m]):h(u[m])}</strong></div>`).join(""),c("#reportPeriod").textContent=d.meta.period,Qe(t)}catch{if(a!==Oe||r.view!==t)return;c("#reportCount").textContent="—",i.innerHTML=`<div class="pay-empty" role="alert"><strong>${s(e("reporting.error"))}</strong><button type="button" class="btn" id="reportRetry">${s(e("reporting.refresh"))}</button></div>`,c("#reportRetry").onclick=()=>Te()}finally{a===Oe&&r.view===t&&i.setAttribute("aria-busy","false")}}function Qe(t){const a=c("#reportTable");if(!a||!ee)return;const n=ge[t];let i=[...ee.data||[]];t!=="audit"&&(i=i.filter(p=>String(p.name).toLocaleLowerCase().includes(n.q.trim().toLocaleLowerCase())),i.sort((p,m)=>Number(m[n.sort])-Number(p[n.sort])||String(p.name).localeCompare(String(m.name))));const l=t==="audit"?ee.meta.total:i.length,o=t==="audit"?ee.meta:{current_page:Math.max(1,Math.min(n.page,Math.ceil(l/25)||1)),last_page:Math.ceil(l/25)||1};t!=="audit"&&(n.page=o.current_page,i=i.slice((n.page-1)*25,n.page*25)),c("#reportCount").textContent=e("reporting.results",{count:h(l)});const d=t==="audit"?["code","date","actor","branch","method","status","raw","imported","duplicates","invalid"]:["name","leads","converted","conversion","follow_up","lost","orders","revenue","average_order"],u=["code","date","actor","branch","method","status","name"];a.innerHTML=i.length?`<div class="table-wrap report-table-wrap"><table class="data-table report-table"><thead><tr>${d.map(p=>`<th${u.includes(p)?"":' class="is-num"'}>${s(e("reporting."+p))}</th>`).join("")}</tr></thead><tbody>${i.map(p=>`<tr>${d.map(m=>{let v=u.includes(m)?s(m==="status"?e("reporting.status_"+p[m]):p[m]):m==="conversion"?Number(p.leads)>0?Number(p[m]).toFixed(1)+"%":"—":["revenue","average_order"].includes(m)?N(p[m]):h(p[m]);return`<td${u.includes(m)?"":' class="is-num"'}>${["name","code"].includes(m)?`<strong>${v}</strong>`:v}</td>`}).join("")}</tr>`).join("")}</tbody></table></div>`:`<div class="pay-empty"><strong>${s(e("reporting.empty"))}</strong>${s(e("reporting.empty_hint"))}</div>`,a.insertAdjacentHTML("beforeend",G("report",o)),J("report",p=>{if(n.page=p,t==="audit")return Te();Qe(t)})}function Ds(t,a){B.innerHTML=`${de(s(t),s(a))}<section class="card"><div class="empty">${s(a)}</div></section>`}function Rs(){B.innerHTML=`${de(e("followup.title"),e("followup.subtitle"),`<button type="button" class="btn" data-jump="leads">${s(e("followup.open_workspace"))}</button>`)}
  <div class="grid kpi-grid" id="followKpiMount"></div>
  <section class="lead-panel card" style="margin-top:14px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${s(e("followup.title"))}</h2>
        <p class="lead-panel__sub">${s(e("followup.subtitle"))}</p>
      </div>
      <div class="lead-panel__head-meta">
        <span class="lead-panel__count" id="followResultCount">—</span>
      </div>
    </div>
    <div class="lead-panel__filters">
      <div class="tabs" id="followBuckets" role="tablist"></div>
      <label class="lead-search" for="followSearch">
        <span class="lead-search__icon" aria-hidden="true">⌕</span>
        <input class="lead-search__input" id="followSearch" type="search" autocomplete="off" placeholder="${s(e("followup.search_placeholder"))}" value="${s(r.followSearch)}" />
      </label>
      <div class="lead-filter-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
        <label class="lead-field">
          <span class="lead-field__label">${s(e("workspace.filter_beautician"))}</span>
          <select class="lead-field__control" id="followBeautician"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${s(e("workspace.filter_branch"))}</span>
          <select class="lead-field__control" id="followBranch"></select>
        </label>
      </div>
    </div>
    <div class="lead-panel__body" id="followTableMount"></div>
  </section>`,X(),Pt(),ae()}async function ae(){const t=_.followUpsUrl||"";if(!t){$(e("followup.load_error"));return}const a=new URLSearchParams;r.followSearch&&a.set("q",r.followSearch),r.followBucket&&r.followBucket!=="all"&&a.set("bucket",r.followBucket);const n=r.leadBranch!=="all"?r.leadBranch:r.branch||"all";n&&n!=="all"&&a.set("branch",n),r.leadBeautician&&r.leadBeautician!=="all"&&a.set("beautician",r.leadBeautician),a.set("page",String(r.followPage||1)),Ye=!0,r.view==="followup"&&st();try{const i=await fetch(t+"?"+a.toString(),{headers:D(!1),credentials:"same-origin"});if(!i.ok)throw new Error("followups "+i.status);const l=await i.json();dt=l.meta||{},we=Array.isArray(l.data)?l.data:[],da=l.meta&&l.meta.summary||da,ke=l.filters||ke,l.filters?.statuses&&(E.statuses=l.filters.statuses)}catch(i){console.error(i),$(e("followup.load_error"))}finally{Ye=!1,r.view==="followup"&&(Us(),Pt(),st())}}function Pt(){const t=ke.buckets||[{value:"all",label:e("followup.bucket_all")},{value:"overdue",label:e("followup.bucket_overdue")},{value:"due_today",label:e("followup.bucket_due_today")},{value:"no_response",label:e("followup.bucket_no_response")},{value:"lost",label:e("followup.bucket_lost")}],a=c("#followBuckets");a&&(a.innerHTML=t.map(o=>`<button type="button" class="tab ${String(r.followBucket)===String(o.value)?"active":""}" role="tab" data-follow-bucket="${s(o.value)}">${s(o.label)}</button>`).join(""),T("[data-follow-bucket]",a).forEach(o=>{o.onclick=()=>{r.followBucket=o.dataset.followBucket||"all",r.followPage=1,ae()}}));const n=c("#followBeautician");if(n){const o=[{id:"all",name:e("workspace.all_beauticians")},...ke.beauticians||E.beauticians||[]];n.innerHTML=o.map(d=>`<option value="${s(d.id)}" ${String(r.leadBeautician)===String(d.id)?"selected":""}>${s(d.name)}</option>`).join(""),n.onchange=d=>{r.leadBeautician=d.target.value,r.followPage=1,ae()}}const i=c("#followBranch");if(i){const o=[{id:"all",name:e("workspace.all_branches")},...ke.branches||_.branches||[]];i.innerHTML=o.map(d=>`<option value="${s(d.id)}" ${String(r.leadBranch)===String(d.id)?"selected":""}>${s(d.name)}</option>`).join(""),i.onchange=d=>{r.leadBranch=d.target.value,r.followPage=1,ae()}}const l=c("#followSearch");l&&(l.oninput=o=>{r.followSearch=o.target.value,clearTimeout(Ea),Ea=setTimeout(()=>{r.followPage=1,ae()},350)})}function Us(){const t=c("#followKpiMount");if(!t)return;const a=da||{};t.innerHTML=`
    ${x("↻",e("followup.kpi_queue"),h(a.queue),e("followup.kpi_queue_meta"),"—","blue")}
    ${x("⏱",e("followup.kpi_overdue"),h(a.overdue),e("followup.kpi_overdue_meta"),"—","rose")}
    ${x("✓",e("followup.kpi_due_today"),h(a.due_today),e("followup.kpi_due_meta"),"—","green")}
    ${x("⌀",e("followup.kpi_no_response"),h(a.no_response),e("followup.kpi_no_response_meta"),"—","purple")}
    ${x("✕",e("followup.kpi_lost"),h(a.lost),e("followup.kpi_lost_meta"),"—","rose")}
  `}function st(){const t=c("#followTableMount");if(!t)return;const a=Array.isArray(we)?we.length:0,n=c("#followResultCount");if(n&&(n.textContent=a===1?e("followup.results_count_one"):e("followup.results_count",{count:h(a)})),Ye){t.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${s(e("followup.loading"))}</strong></div>`;return}const i=we;if(!i.length){t.innerHTML=`<div class="lead-empty">
      <div class="lead-empty__icon" aria-hidden="true">↻</div>
      <strong>${s(e("followup.empty"))}</strong>
      <p>${s(e("followup.empty_hint"))}</p>
      <button type="button" class="btn" data-jump="leads">${s(e("followup.open_workspace"))}</button>
    </div>`,X();return}const l=!!_.canEditLead;t.innerHTML=`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${s(e("workspace.col_lead_id"))}</th>
    <th>${s(e("workspace.col_customer"))}</th>
    <th>${s(e("workspace.col_phone"))}</th>
    <th>${s(e("workspace.col_beautician"))}</th>
    <th>${s(e("workspace.col_branch"))}</th>
    <th>${s(e("workspace.col_status"))}</th>
    <th>${s(e("workspace.col_last_fu"))}</th>
    <th>${s(e("followup.col_waiting"))}</th>
    <th class="lead-table__actions"><span class="sr-only">${s(e("workspace.col_action"))}</span></th>
  </tr></thead><tbody>${i.map(o=>{const d=String(o.name||""),u=s((d[0]||"?").toUpperCase()),p=Number(o.days_since_followup||0),m=o.last_followed_up_at?e("followup.days",{count:p}):e("followup.never"),v=String(o.followup_bucket)==="overdue";return`<tr>
      <td><span class="lead-code">${s(o.code||o.id)}</span></td>
      <td><div class="person-cell person-cell--lead"><div class="mini-avatar" aria-hidden="true">${u}</div><div class="person-cell__text"><strong>${s(d||"—")}</strong><small>${s(o.customer||"")}</small></div></div></td>
      <td><span class="lead-mono">${s(o.phone||"—")}</span></td>
      <td>${s(o.beautician||"—")}</td>
      <td><span class="lead-branch">${s(o.branch||"—")}</span></td>
      <td>${P(o.status)}</td>
      <td><span class="lead-date">${s(o.last||"—")}</span></td>
      <td><span class="lead-wait${v?" is-overdue":""}">${s(m)}</span></td>
      <td class="lead-table__actions">
        <div class="lead-menu">
          <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${s(e("workspace.row_actions"))}">
            <span class="lead-menu__dots" aria-hidden="true"></span>
          </button>
          <div class="lead-menu__panel" role="menu" hidden>
            <button type="button" class="lead-menu__item" role="menuitem" data-follow-view="${s(o.id)}">${s(e("followup.view_lead"))}</button>
            ${l?`<button type="button" class="lead-menu__item" role="menuitem" data-follow-mark="${s(o.id)}">${s(e("followup.mark"))}</button>`:""}
          </div>
        </div>
      </td>
    </tr>`}).join("")}</tbody></table></div>`,t.insertAdjacentHTML("beforeend",G("follow",dt)),J("follow",o=>(r.followPage=o,ae())),ft(t),T("[data-follow-view]",t).forEach(o=>o.onclick=()=>{K(),aa(o.dataset.followView)}),T("[data-follow-mark]",t).forEach(o=>o.onclick=()=>{K(),jt(o.dataset.followMark)})}async function jt(t){const a=U(_.leadFollowUpUrlTemplate,t);if(!a){$(e("followup.mark_error"));return}try{const n=await fetch(a,{method:"POST",headers:D(!0),credentials:"same-origin",body:JSON.stringify({})}),i=await n.json().catch(()=>({}));if(!n.ok){$(i.message||e("followup.mark_error"));return}$(i.message||e("followup.marked")),r.view==="followup"?await ae():await q()}catch(n){console.error(n),$(e("followup.mark_error"))}}function Bt(){const t=L||{},a=t.kpis||{},n=a.vs_prev||{},i=t.targets||{},l=t.dual||{},o=Number(l.sales_pct||t.target_board&&t.target_board.sales_pct||0),d=Number(i.sales||0),u=Number(a.sales||0),p=u-d,m=s(t.period&&t.period.label||e("common.this_month")),v=Array.isArray(t.sales_insights)?t.sales_insights:[],b=Array.isArray(t.waterfall)?t.waterfall:[],w=Math.max(1,...b.map(g=>Number(g.value||0)));B.innerHTML=`${de(e("sales.title"),e("sales.subtitle"),`<button type="button" class="btn soft" id="salesRefreshBtn">${s(e("sales.refresh"))}</button>
     <button type="button" class="btn" data-jump="payments">${s(e("sales.open_payments"))}</button>
     <button type="button" class="btn primary" data-jump="leads">${s(e("sales.open_leads"))}</button>`)}
  <section class="sales-insights card">
    <div class="sales-insights__head">
      <div>
        <div class="journey-section__title">${s(e("sales.insights"))}</div>
        <p class="card-subtitle" style="margin:0">${m}</p>
      </div>
    </div>
    <div class="sales-insights__grid">
      ${v.length?v.map(g=>`<article class="sales-insight sales-insight--${s(g.tone||"info")}"><strong>${s(g.title||"")}</strong><p>${s(g.body||"")}</p></article>`).join(""):`<article class="sales-insight sales-insight--info"><strong>${s(e("sales.title"))}</strong><p>${s(e("sales.subtitle"))}</p></article>`}
    </div>
  </section>

  <div class="grid kpi-grid" style="margin-top:12px">
    ${x("◫",e("sales.kpi_sales"),C(a.sales),Y(n.sales,"%"),Q(e("sales.kpi_sales"),C(d)),"rose",Math.min(100,o),"","")}
    ${x("▣",e("sales.kpi_orders"),h(a.orders||0),Y(n.orders||0,"%"),"—","blue")}
    ${x("♙",e("sales.kpi_customers"),h(a.buyers),Y(n.buyers,"%"),Q(e("sales.kpi_customers"),h(i.buyers||0)),"green")}
    ${x("▥",e("sales.kpi_avg"),C(a.avg_sale),Y(n.avg_sale,"%"),Q(e("sales.kpi_avg"),C(i.avg_sale||0)),"purple")}
    ${x("%",e("sales.kpi_target"),W(o),p>=0?"↑ "+C(Math.abs(p)):"↓ "+C(Math.abs(p)),e("sales.of_target"),"green",Math.min(100,o))}
  </div>

  <div class="grid split-60" style="margin-top:12px">
    <section class="card sales-card">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${s(m)}</div>
          <div class="daily-panel__title">${s(e("sales.revenue_trend"))}</div>
          <div class="daily-panel__sub">${s(e("sales.revenue_sub"))}</div>
        </div>
        <span class="sales-card__pill">${C(u)}</span>
      </div>
      <div class="chart-wrap sales-card__chart"><canvas id="salesChart"></canvas></div>
    </section>
    <section class="card target-card ${o>=100?"target-card--over":""}">
      <div class="target-card__head">
        <div>
          <div class="target-card__eyebrow">${s(e("sales.target_card"))}</div>
          <div class="target-card__title">${s(e("overview.target_achievement"))}</div>
        </div>
        <span class="target-card__pill">${o>=100?s(e("overview.above_target")):s(e("overview.below_target"))}</span>
      </div>
      <div class="target-card__body">
        <div class="target-card__ring" style="--p:${Math.min(100,o)}">
          <svg viewBox="0 0 120 120" aria-hidden="true">
            <circle class="target-card__track" cx="60" cy="60" r="52"></circle>
            <circle class="target-card__prog" cx="60" cy="60" r="52"></circle>
          </svg>
          <div class="target-card__ring-value">
            <strong>${W(o)}</strong>
            <span>${s(e("sales.of_target"))}</span>
          </div>
        </div>
        <div class="target-card__side">
          <div class="target-card__stat"><span>${s(e("overview.target"))}</span><strong>${C(d)}</strong></div>
          <div class="target-card__stat"><span>${s(e("overview.actual"))}</span><strong>${C(u)}</strong></div>
          <div class="target-card__delta">
            <strong>${p>=0?"+":""}${C(p)}</strong>
            <span>${s(e("sales.gap"))}</span>
          </div>
        </div>
      </div>
    </section>
  </div>

  <section class="card" style="margin-top:12px">
    <div class="card-title-row">
      <div>
        <div class="card-title">${s(e("sales.pipeline"))}</div>
        <div class="card-subtitle">${s(e("sales.pipeline_sub"))}</div>
      </div>
      <span class="badge blue">${s(e("sales.conv_rate"))}: ${W(a.new_buyer_share_pct||0)}</span>
    </div>
    <div class="sales-funnel">
      ${b.length?b.map((g,f)=>{const S=Number(g.value||0),M=Math.max(8,Math.round(S/w*100));return`<div class="sales-funnel__step">
              <div class="sales-funnel__meta"><span>${s(g.name||"")}</span><strong>${h(S)}</strong></div>
              <div class="sales-funnel__bar"><span style="width:${M}%"></span></div>
            </div>`}).join(""):`<div class="empty"><strong>${s(e("sales.empty_pipeline"))}</strong></div>`}
    </div>
  </section>

  <section class="lead-panel card" style="margin-top:12px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${s(e("sales.attribution"))}</h2>
        <p class="lead-panel__sub">${s(e("sales.attribution_sub"))}</p>
      </div>
    </div>
    <div class="lead-panel__body">${Is()}</div>
  </section>

  <div class="grid three-col" style="margin-top:12px">
    ${(t.branches||[]).slice(0,6).map(g=>vt(g.name,g.new_buyers||0,g.buyers||0,g.conv||0,g.sales||0,g.avg||0,g.buyers||0)).join("")||`<section class="card"><div class="empty"><strong>${s(e("sales.empty_branches"))}</strong></div></section>`}
  </div>`,X(),c("#salesRefreshBtn")&&(c("#salesRefreshBtn").onclick=()=>Ma()),requestAnimationFrame(()=>Ze())}function Is(){const t=L&&L.beauticians&&L.beauticians.length?L.beauticians:[];return t.length?`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${s(e("sales.col_rank"))}</th>
    <th>${s(e("sales.col_beautician"))}</th>
    <th class="is-num">${s(e("sales.col_sales"))}</th>
    <th class="is-num">${s(e("sales.col_customers"))}</th>
    <th class="is-num">${s(e("sales.col_orders"))}</th>
    <th class="is-num">${s(e("sales.col_avg"))}</th>
    <th class="is-num">${s(e("sales.col_leads"))}</th>
    <th class="is-num">${s(e("sales.col_conv"))}</th>
  </tr></thead><tbody>${t.map((a,n)=>{const i=String(a.name||"—"),l=s((i[0]||"?").toUpperCase());return`<tr>
      <td><span class="rank">${n+1}</span></td>
      <td><div class="person-cell person-cell--lead"><div class="mini-avatar" aria-hidden="true">${l}</div><div class="person-cell__text"><strong>${s(i)}</strong></div></div></td>
      <td class="is-num"><span class="lead-money">${C(a.sales||0)}</span></td>
      <td class="is-num">${h(a.buyers||0)}</td>
      <td class="is-num">${h(a.orders||a.order_count||0)}</td>
      <td class="is-num">${C(a.avg||0)}</td>
      <td class="is-num">${h(a.leads||0)}</td>
      <td class="is-num">${W(a.conv||0)}</td>
    </tr>`}).join("")}</tbody></table></div>`:`<div class="lead-empty"><strong>${s(e("sales.empty_beauticians"))}</strong></div>`}function Fs(){const t=(_.userEditUrlTemplate||"").replace(/\/__ID__\/edit$/,"").replace(/\/__ID__$/,"")||"";B.innerHTML=`<div class="cus-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${s(e("customers.title"))}</h1>
        <div class="page-subtitle">${s(e("customers.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="cusRefresh">${s(e("customers.refresh"))}</button>
        ${_.canViewUser&&t?`<a class="btn primary" href="${s(t)}" target="_blank" rel="noopener">${s(e("customers.open_users"))}</a>`:""}
      </div>
    </div>
    <div class="pay-metrics" id="cusMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro">
          <p class="lead-panel__sub" id="cusPulse" style="margin:0">${s(e("customers.subtitle"))}</p>
        </div>
        <div class="lead-panel__head-meta">
          <span class="lead-panel__count" id="cusResultCount">—</span>
        </div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="cusTabs" role="tablist"></div>
        <label class="lead-search" for="cusSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="cusSearch" type="search" autocomplete="off" placeholder="${s(e("customers.search_placeholder"))}" value="${s(r.customerSearch||"")}" />
        </label>
        <div class="lead-filter-grid" style="grid-template-columns:minmax(0,1fr)">
          <label class="lead-field">
            <span class="lead-field__label">${s(e("customers.filter_branch"))}</span>
            <select class="lead-field__control" id="cusBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="cusTableMount"></div>
    </section>
  </div>`;const a=c("#cusRefresh");a&&(a.onclick=()=>ye()),Os(),ye()}function Os(){const t=c("#cusSearch");t&&(t.oninput=()=>{clearTimeout(qa),qa=setTimeout(()=>{r.customerSearch=t.value.trim(),r.customerPage=1,ye()},320)});const a=c("#cusBranch");a&&(a.onchange=()=>{r.customerBranch=a.value,r.customerPage=1,ye()})}async function ye(){const t=_.customersUrl||"",a=c("#cusTableMount");if(!t){a&&(a.innerHTML=`<div class="pay-empty"><strong>${s(e("customers.load_error"))}</strong></div>`);return}ze=!0,nt(),it();const n=new URLSearchParams;r.customerSearch&&n.set("q",r.customerSearch),r.customerSegment&&r.customerSegment!=="all"&&n.set("segment",r.customerSegment);const i=r.customerBranch!=="all"?r.customerBranch:r.branch||"all";i&&i!=="all"&&n.set("branch",i),r.period&&n.set("period",r.period),n.set("page",String(r.customerPage||1)),n.set("per_page","25");try{const l=await fetch(`${t}?${n.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("customers "+l.status);const o=await l.json();he=Array.isArray(o.data)?o.data:[],ga=Object.assign({total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0},o.meta&&o.meta.summary||{}),Se=Object.assign({segments:[],branches:[]},o.filters||{}),xe={current_page:o.meta&&o.meta.current_page||1,last_page:o.meta&&o.meta.last_page||1,total:o.meta&&o.meta.total||0}}catch(l){console.error(l),he=[],$(e("customers.load_error"))}finally{ze=!1,nt(),Ws(),Vs(),it()}}function nt(){const t=ga,a=c("#cusPulse");a&&(a.textContent=xe.total>0?e("customers.pulse_busy",{count:h(xe.total)}):e("customers.pulse_clear"));const n=c("#cusMetrics");n&&(n.innerHTML=`
      <div class="pay-metric"><span>${s(e("customers.stat_total"))}</span><strong>${h(t.total)}</strong></div>
      <div class="pay-metric"><span>${s(e("customers.stat_buyers"))}</span><strong>${h(t.buyers)}</strong></div>
      <div class="pay-metric"><span>${s(e("customers.stat_new"))}</span><strong>${h(t.new_buyers)}</strong></div>
      <div class="pay-metric"><span>${s(e("customers.stat_sales"))}</span><strong>${N(t.period_sales)}</strong></div>
    `)}function Ws(){const t=c("#cusTabs");if(!t)return;const a=ga,n={all:a.total||0,buyers:a.buyers||0,new:a.new_buyers||0,returning:a.returning||0,leads:a.with_leads||0},i=Se.segments&&Se.segments.length?Se.segments:[{value:"all",label:e("customers.tab_all")},{value:"buyers",label:e("customers.tab_buyers")},{value:"new",label:e("customers.tab_new")},{value:"returning",label:e("customers.tab_returning")},{value:"leads",label:e("customers.tab_leads")}];t.innerHTML=i.map(l=>{const o=l.value,d=r.customerSegment===o?"active":"",u=n[o],p=u!==void 0?`<span class="pay-tab-count">${h(u)}</span>`:"";return`<button type="button" class="tab ${d}" role="tab" data-ctab="${s(o)}">${s(l.label)}${p}</button>`}).join(""),T("[data-ctab]",t).forEach(l=>{l.onclick=()=>{r.customerSegment=l.dataset.ctab,r.customerPage=1,ye()}})}function Vs(){const t=c("#cusBranch");if(t){const a=r.customerBranch;t.innerHTML=`<option value="all">${s(e("customers.all_branches"))}</option>`+(Se.branches||[]).map(n=>`<option value="${n.id}" ${String(a)===String(n.id)?"selected":""}>${s(n.code?n.code+" · "+n.name:n.name)}</option>`).join("")}}function it(){const t=c("#cusTableMount"),a=c("#cusResultCount");if(a&&(a.textContent=e("customers.result_count",{count:h(xe.total||he.length)})),!t)return;if(ze){t.innerHTML=`<div class="pay-empty"><strong>${s(e("customers.loading"))}</strong></div>`;return}if(!he.length){t.innerHTML=`<div class="pay-empty"><strong>${s(e("customers.empty"))}</strong><span>${s(e("customers.empty_hint"))}</span></div>`;return}const n=he.map(l=>{const o=[`<span class="pay-chip ${l.segment==="new"||l.segment==="buyer"?"pay-chip--ok":"pay-chip--muted"}">${s(l.segment_label||"")}</span>`,l.has_lead?`<span class="pay-chip pay-chip--warn">${s(e("customers.chip_lead"))}</span>`:"",l.loyalty_tier?`<span class="pay-chip pay-chip--muted">${s(l.loyalty_tier)}</span>`:""].filter(Boolean).join(""),d=l.avatar_url?`<div class="mini-avatar mini-avatar--photo"><img src="${s(l.avatar_url)}" alt=""></div>`:`<div class="mini-avatar">${s(l.initial||"?")}</div>`;return`<tr>
      <td><span class="pay-id">${s(l.code||"CUS-"+l.id)}</span></td>
      <td><div class="person-cell">${d}<div><strong>${s(l.name||"")}</strong><small>${s(l.phone||l.email||"")}</small></div></div></td>
      <td>${s(l.branch||"—")}</td>
      <td><strong>${h(l.paid_orders_count)}</strong><div class="pay-method">${h(l.orders_count)} total</div></td>
      <td><div class="pay-amount">${N(l.paid_sales)}</div><div class="pay-method">${N(l.period_sales)} ${s(e("customers.detail_period").toLowerCase())}</div></td>
      <td>${s(l.last_order_label||"—")}</td>
      <td><div class="pay-chips">${o}</div></td>
      <td><button type="button" class="pay-review-btn" data-customer="${l.id}">${s(e("customers.view"))}</button></td>
    </tr>`}).join(""),i=G("cus",xe);t.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${s(e("customers.col_id"))}</th>
    <th>${s(e("customers.col_customer"))}</th>
    <th>${s(e("customers.col_branch"))}</th>
    <th>${s(e("customers.col_orders"))}</th>
    <th>${s(e("customers.col_sales"))}</th>
    <th>${s(e("customers.col_last"))}</th>
    <th>${s(e("customers.col_segment"))}</th>
    <th>${s(e("customers.col_action"))}</th>
  </tr></thead><tbody>${n}</tbody></table></div>${i}`,T("[data-customer]",t).forEach(l=>l.onclick=()=>Ys(Number(l.dataset.customer))),J("cus",l=>(r.customerPage=l,ye()))}function Ys(t){const a=he.find(u=>Number(u.id)===Number(t));if(!a)return;const n=U(_.userEditUrlTemplate,a.id),i=!!_.canViewUser,o=`<div class="pay-review">
    <div class="pay-review__hero">
      ${a.avatar_url?`<div class="pay-review__avatar" style="padding:0;overflow:hidden"><img src="${s(a.avatar_url)}" alt="" style="width:100%;height:100%;object-fit:cover"></div>`:`<div class="pay-review__avatar">${s(a.initial||"?")}</div>`}
      <div style="min-width:0;flex:1">
        <div class="pay-id">${s(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${s(a.name||"")}</strong>
        <div class="pay-method">${s(a.phone||"—")} · ${s(a.email||"—")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${P(a.segment_label)}${a.loyalty_tier?P(a.loyalty_tier):""}</div>
      </div>
      <div style="text-align:right"><div class="pay-amount">${N(a.paid_sales)}</div><div class="pay-method">${s(e("customers.detail_sales"))}</div></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${s(e("customers.detail_orders"))}</label><strong>${h(a.paid_orders_count)}</strong></div>
      <div class="pay-review__card"><label>${s(e("customers.detail_period"))}</label><strong>${N(a.period_sales)}</strong></div>
      <div class="pay-review__card"><label>${s(e("customers.detail_leads"))}</label><strong>${h(a.leads_count)}</strong></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${s(e("customers.col_branch"))}</label><strong>${s(a.branch_name||a.branch||"—")}</strong></div>
      <div class="pay-review__card"><label>${s(e("customers.col_last"))}</label><strong>${s(a.last_order_label||"—")}</strong></div>
      <div class="pay-review__card"><label>${s(e("customers.col_orders"))}</label><strong>${h(a.orders_count)}</strong></div>
    </div>
  </div>`,d=[i?`<a class="btn primary" href="${s(n)}" target="_blank" rel="noopener">${s(e("customers.open_profile"))}</a>`:"",`<button type="button" class="btn" data-jump="payments" data-customer-id="${s(String(a.id))}" data-customer-name="${s(a.name||"")}" data-customer-phone="${s(a.phone||"")}" data-customer-email="${s(a.email||"")}">${s(e("customers.open_payments"))}</button>`,`<button type="button" class="btn" data-action-drawer-close>${s(e("customers.close"))}</button>`].filter(Boolean).join("");se(e("customers.title"),a.code+" · "+a.name,o,d,"Customer"),setTimeout(()=>{T("#actionDrawerFoot [data-jump]").forEach(u=>u.onclick=()=>{if(R(),u.dataset.jump==="payments"&&u.dataset.customerId){ds({id:u.dataset.customerId,name:u.dataset.customerName,phone:u.dataset.customerPhone,email:u.dataset.customerEmail});return}I(u.dataset.jump)})},0)}function Et(){const t=c("#donutChart");if(!t)return;De(t);const a=t.getContext("2d"),n=t.clientWidth,i=t.clientHeight,l=L&&L.status_mix||[],o=["#2563eb","#0ea5e9","#059669","#d97706","#e11d48","#6366f1"],d=l.map((g,f)=>({label:g.name,value:Number(g.value||0),color:o[f%o.length]})),u=d.reduce((g,f)=>g+f.value,0);if(!d.length||u<=0){a.clearRect(0,0,n,i),a.fillStyle="#94a3b8",a.font="12px Poppins,sans-serif",a.textAlign="center",a.fillText(e("overview.no_branch_data"),n/2,i/2);const g=c("#leadLegend");g&&(g.innerHTML="");return}let p=-Math.PI/2;const m=n/2,v=i/2,b=Math.min(n,i)/2-8;d.forEach(g=>{const f=p+g.value/u*Math.PI*2;a.beginPath(),a.moveTo(m,v),a.arc(m,v,b,p,f),a.closePath(),a.fillStyle=g.color,a.fill(),p=f}),a.beginPath(),a.arc(m,v,b*.58,0,Math.PI*2),a.fillStyle="#fff",a.fill();const w=c("#leadLegend");w&&(w.innerHTML=d.map(g=>`<div class="legend-row"><span class="dot" style="background:${g.color}"></span><span>${s(g.label)}</span><strong>${h(g.value)}</strong><span class="pct">${(g.value/u*100).toFixed(1)}%</span></div>`).join(""))}function Ze(){const t=c("#salesChart");if(!t)return;De(t);const a=t.getContext("2d"),n=t.clientWidth,i=t.clientHeight,l=L&&L.equity||{},o=Array.isArray(l.labels)?l.labels:[];let d;l.actual&&l.actual.length?(d=l.actual.map((y,k)=>k===0?Number(y):Math.max(0,Number(y)-Number(l.actual[k-1]))),d=d.map(y=>Math.round(y/1e3))):d=[];const u=Math.round((L&&L.targets&&L.targets.sales||0)/1e3),p={l:36,r:16,t:28,b:36},m=Math.max(u,...d,1)*1.15,v=y=>p.l+y/Math.max(d.length-1,1)*(n-p.l-p.r),b=y=>i-p.b-y/m*(i-p.t-p.b);a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let y=0;y<4;y++){const k=p.t+(i-p.t-p.b)/3*y;a.beginPath(),a.moveTo(p.l,k),a.lineTo(n-p.r,k),a.stroke()}const w=b(u);a.setLineDash([5,5]),a.strokeStyle="#d97706",a.beginPath(),a.moveTo(p.l,w),a.lineTo(n-p.r,w),a.stroke(),a.setLineDash([]),a.fillStyle="#64748b",a.font="600 10px Poppins",a.textAlign="left",a.fillText("Target",p.l+4,w-6);const g="#1d4ed8",f="#0ea5e9",S=a.createLinearGradient(0,p.t,0,i-p.b);S.addColorStop(0,"rgba(37,99,235,.22)"),S.addColorStop(1,"rgba(14,165,233,.02)"),a.beginPath(),a.moveTo(v(0),i-p.b),d.forEach((y,k)=>a.lineTo(v(k),b(y))),a.lineTo(v(d.length-1),i-p.b),a.closePath(),a.fillStyle=S,a.fill(),a.beginPath(),d.forEach((y,k)=>k?a.lineTo(v(k),b(y)):a.moveTo(v(k),b(y))),a.strokeStyle=g,a.lineWidth=2.75,a.lineJoin="round",a.lineCap="round",a.stroke();const M=Math.max(1,Math.ceil(d.length/8));d.forEach((y,k)=>{if(k%M&&k!==d.length-1)return;const j=v(k),A=b(y);a.beginPath(),a.arc(j,A,4,0,Math.PI*2),a.fillStyle="#fff",a.fill(),a.lineWidth=2,a.strokeStyle=f,a.stroke(),a.fillStyle="#0f172a",a.font="700 10px Poppins",a.textAlign="center",a.fillText("RM"+y+"k",j,A-10),a.fillStyle="#64748b",a.font="600 10px Poppins",a.fillText(o[k]||"",j,i-12)})}function Ks(){if(!c("#branchChart"))return;const t=c("#branchChart"),a=t.getContext("2d"),n=t.clientWidth,i=t.clientHeight,l={l:45,r:20,t:20,b:35};De(t);const o=Array.isArray(L&&L.branches)?L.branches:[],d=o.map(b=>Number(b.new_buyer_share_pct)||Number(b.conv)||0),u=o.map(b=>String(b.name||"").slice(0,2).toUpperCase()||"—");if(!d.length)return;const p=Math.max(...d,50),m=Math.max(8,(n-l.l-l.r-70*d.length)/(d.length+1)),v=Math.min(70,(n-l.l-l.r-m*(d.length+1))/d.length);d.forEach((b,w)=>{const g=l.l+m+(v+m)*w,f=i-l.b-b/p*(i-l.t-l.b);a.fillStyle=[Z("--brand","#38bdf8"),Z("--rose","#0ea5e9"),Z("--navy","#2563eb")][w%3],a.fillRect(g,f,v,i-l.b-f),a.fillStyle=Z("--navy","#1d4ed8"),a.textAlign="center",a.font="700 14px Poppins",a.fillText(b+"%",g+v/2,f-8),a.font="13px Poppins",a.fillText(u[w],g+v/2,i-12)})}function De(t){const a=Math.max(1,window.devicePixelRatio||1),n=t.getBoundingClientRect();t.width=n.width*a,t.height=n.height*a,t.getContext("2d").setTransform(a,0,0,a,0,0)}const xa=["overview","leads","import","imports","followup","sales","payments","customers","wallet","checkin","clearance","beauticians","branches","audit"],Le=String(_.basePath||"").replace(/\/$/,"")||"/admin/leads/central";function lt(t){const a=xa.includes(t)?t:"overview";return a==="overview"?Le:Le+"/"+a}function At(t){const a=String(location.pathname).replace(/\/$/,"");if(a===Le)return"overview";if(a.startsWith(Le+"/")){const n=a.slice(Le.length+1).split("/")[0];return xa.includes(n)?n:"overview"}return"overview"}function rt(){const t=new URLSearchParams(location.search);return t.set("branch",r.branch||"all"),r.period?t.set("period",r.period):t.delete("period"),"?"+t.toString()}function zs(t,{replace:a=!1,silent:n=!1}={}){const i=lt(t)+rt()+location.hash;!n&&(a||i!==location.pathname+location.search+location.hash)&&history[a?"replaceState":"pushState"]({view:t},"",i),T(".nav-item[data-view]").forEach(l=>l.href=lt(l.dataset.view)+rt())}function Gs(){const t=new URLSearchParams(location.search),a=t.get("branch")||"all",n=t.get("period")||c("#periodScope").options[0]?.value||"",i=c("#branchScope"),l=c("#periodScope");i.value=a,r.branch=i.value||"all",i.value=r.branch,/^\d{4}-(0[1-9]|1[0-2])$/.test(n)&&![...l.options].some(o=>o.value===n)&&l.add(new Option(n,n)),l.value=n,r.period=l.value||l.options[0]?.value||"",l.value=r.period}function qt(){r.branch=c("#branchScope").value||"all",r.period=c("#periodScope").value||"",ge[r.view]&&(ge[r.view].page=1),I(r.view)}function I(t,a={}){ja();const n=xa.includes(t)?t:"overview";V(),R(),window.IMMA_TRADE&&n!=="overview"&&IMMA_TRADE.dispose(),r.view=n,zs(n,a),T(".nav-item[data-view]").forEach(i=>i.classList.toggle("active",i.dataset.view===n)),c("#crumbCurrent").textContent={overview:e("common.overview_crumb"),leads:e("nav.leads"),import:e("nav.import"),imports:e("nav.imports"),payments:e("nav.payments"),wallet:e("nav.wallet"),checkin:e("nav.checkin"),clearance:e("nav.clearance"),beauticians:e("nav.beauticians"),branches:e("nav.branches"),audit:e("nav.audit"),followup:e("nav.followup"),sales:e("nav.sales"),customers:e("nav.customers")}[n]||n,["overview","sales"].includes(n)&&ca!==JSON.stringify([r.branch,r.period])?(B.innerHTML=`<section class="card"><div class="pay-empty" role="status">${s(e("overview.loading"))}</div></section>`,Ma()):({overview:mt,leads:Xt,import:ts,imports:os,followup:Rs,sales:Bt,payments:Lt,customers:Fs,wallet:Ps,checkin:bs,clearance:Ms,beauticians:qs,branches:Ns,audit:Hs}[n]||(()=>Ds(e("nav."+n),e("operations.not_ready"))))(),a.silent||window.scrollTo({top:0,behavior:"smooth"}),innerWidth<1e3&&c("#sidebar").classList.remove("open")}function X(){T("[data-jump]").forEach(t=>t.onclick=()=>{t.dataset.importTab&&(r.importTab=t.dataset.importTab),I(t.dataset.jump)})}let We=null,Nt="";function se(t,a,n,i,l=""){V(),R(),We=document.activeElement,Nt=document.body.style.overflow,c("#actionDrawerEyebrow").textContent=l,c("#actionDrawerTitle").textContent=t,c("#actionDrawerBody").innerHTML=`<p class="action-drawer__subtitle">${s(a)}</p>${n}`,c("#actionDrawerFoot").innerHTML=i,c("#actionDrawerClose").setAttribute("aria-label",e("wallet.close")),c("#actionDrawer").classList.add("show"),c("#actionDrawer").setAttribute("aria-hidden","false"),c("#actionDrawer").inert=!1,c("#actionDrawerBackdrop").classList.add("show"),c(".app-shell").inert=!0,document.body.style.overflow="hidden",c("#actionDrawerBody").scrollTop=0,c("#actionDrawerClose").focus({preventScroll:!0});const o=c("#saveLead");o&&(o.onclick=()=>Zt()),T("[data-action-drawer-close]",c("#actionDrawer")).forEach(d=>d.onclick=R)}function R(){const t=c("#actionDrawer");t.classList.contains("show")&&(Xe(),t.classList.remove("show"),t.setAttribute("aria-hidden","true"),t.inert=!0,c("#actionDrawerBackdrop").classList.remove("show"),c(".app-shell").inert=!1,document.body.style.overflow=Nt,We?.isConnected&&We.focus({preventScroll:!0}),We=null)}function V(){const t=c("#leadDrawer");t.classList.contains("show")&&(t.classList.remove("show"),t.inert=!0,c(".app-shell").inert=!1,c("#drawerBackdrop").classList.remove("show"),t.setAttribute("aria-hidden","true"),t.classList.contains("wallet-drawer")&&(t.classList.remove("wallet-drawer"),c("#walletDrawerFoot")?.remove()),document.body.style.overflow=La,Me?.isConnected&&Me.focus({preventScroll:!0}),Me=null)}function Js(){const t=L||{},a=[],n=Number(t.ops?.payments?.queue||0),i=Number(t.ops?.clearance?.queue||0),l=Number(t.kpis?.new_buyers||t.kpis?.unique_leads||0);return n>0&&a.push({icon:"₿",title:"Payments need review",detail:`${h(n)} payment${n===1?"":"s"} in queue`,view:"payments"}),i>0&&a.push({icon:"✓",title:"Clearance queue needs attention",detail:`${h(i)} customer${i===1?"":"s"} waiting`,view:"clearance"}),l>0&&a.push({icon:"♙",title:"New leads captured",detail:`${h(l)} unique lead${l===1?"":"s"} in ${s(t.period?.label||"selected period")}`,view:"leads"}),a}function Pa(){const t=c("#notificationButton"),a=c("#notificationMenu"),n=c("#notificationCount");if(!t||!a||!n)return;const i=Js();n.textContent=String(i.length),n.hidden=i.length===0,a.innerHTML=`<div class="notification-menu__head"><span>Notifications</span><small>${i.length?`${i.length} active`:"All clear"}</small></div>`+(i.length?i.map(l=>`<button type="button" class="notification-menu__item" role="menuitem" data-notification-view="${l.view}"><span class="notification-menu__icon">${l.icon}</span><span><strong>${l.title}</strong><small>${l.detail}</small></span></button>`).join(""):'<div class="notification-menu__empty">No outstanding items right now.</div>')}function Xs(t=null){const a=c("#notificationButton"),n=c("#notificationMenu");if(!a||!n)return;const i=t===null?n.hidden:!t;!n.hidden!==i&&(Pa(),n.hidden=!i,a.setAttribute("aria-expanded",String(i)))}function ja(){const t=c("#notificationButton"),a=c("#notificationMenu");!t||!a||a.hidden||(a.hidden=!0,t.setAttribute("aria-expanded","false"))}function $(t){const a=c("#toast");a.textContent=t,a.classList.add("show"),clearTimeout($._t),$._t=setTimeout(()=>a.classList.remove("show"),2300)}window.showToast=$;window.navigate=I;window.$=c;T(".nav-item[data-view]").forEach(t=>t.addEventListener("click",a=>{a.defaultPrevented||a.metaKey||a.ctrlKey||a.shiftKey||a.altKey||a.button!==0||(a.preventDefault(),t.dataset.view==="payments"&&(r.paymentCustomerId=null,r.paymentCustomerLabel=""),I(t.dataset.view))}));let Ce=null;function Ht(){const t=c("#crumbCurrent").textContent,a=[c("#branchScope"),c("#periodScope"),...T('select,input[type="search"],input[type="date"]',B)].filter(i=>i&&!i.closest("[hidden]")).map(i=>{const l=i.tagName==="SELECT"?i.selectedOptions[0]?.textContent:i.value;if(!l?.trim())return"";const o=i.closest("label")?.querySelector(".lead-field__label")?.textContent||i.parentElement.querySelector("label")?.textContent||"";return o?o.trim()+": "+l.trim():l.trim()}).filter(Boolean);for(const i of[c("#branchScope"),c("#periodScope")]){const l=i.selectedOptions[0]?.textContent?.trim();l&&!a.includes(l)&&a.unshift(l)}T('.tab.active,[role="tab"][aria-selected="true"]',B).forEach(i=>a.push(i.textContent.trim())),c("#centralPrintHeader").innerHTML=`<div class="central-print-brand">${s(e("brand_subtitle"))}</div><h1>${s(t)}</h1><p>${s([...new Set(a)].join(" · "))}</p><p>${s(e("export_pdf.generated"))}: ${s(new Date().toLocaleString(_.locale==="ms"?"ms-MY":"en-MY"))}</p><small>${s(e("export_pdf.scope"))}</small>`;const n=["workspace","payments","customers","wallet","checkin","clearance"].map(i=>e(i+".col_action").toLowerCase());T("table",B).forEach(i=>{T("thead tr:last-child th",i).forEach((o,d)=>{n.includes(o.textContent.trim().toLowerCase())&&(o.classList.add("central-print-action"),T("tbody tr",i).forEach(u=>u.children[d]?.classList.add("central-print-action")))})}),Ce===null&&(Ce=document.title),document.title="Central - "+t+" - "+(r.period||"")}function Qs(){Ce!==null&&(document.title=Ce,Ce=null),T(".central-print-action",B).forEach(t=>t.classList.remove("central-print-action"))}c("#exportCentralPdf").onclick=async()=>{if({leads:Ve,followup:Ye,payments:Ke,customers:ze,wallet:le,checkin:re,clearance:oe}[r.view]||c('[aria-busy="true"],[role="status"]',B)){$(e("export_pdf.loading"));return}const a=c("#exportCentralPdf");a.disabled=!0;try{document.fonts?.ready&&await document.fonts.ready,Ht(),window.print()}finally{a.disabled=!1}};window.addEventListener("beforeprint",Ht);window.addEventListener("afterprint",Qs);c("#menuToggle").onclick=()=>c("#sidebar").classList.toggle("open");c("#notificationButton").onclick=()=>Xs();c("#notificationMenu").onclick=t=>{const a=t.target.closest("[data-notification-view]");a&&(ja(),I(a.dataset.notificationView))};document.addEventListener("pointerdown",t=>{t.target.closest(".notification-wrap")||ja()},!0);Pa();c("#drawerClose").onclick=V;c("#drawerBackdrop").onclick=V;c("#actionDrawerClose").onclick=R;c("#actionDrawerBackdrop").onclick=R;c("#branchScope").onchange=qt;c("#periodScope").onchange=qt;c("#globalSearch").addEventListener("keydown",t=>{t.key==="Enter"&&(I("leads"),r.leadSearch=t.target.value,r.leadPage=1)});window.addEventListener("resize",()=>{r.view==="overview"&&(pt(),ut(),Et(),Ze(),window.IMMA_TRADE&&IMMA_TRADE.resize()),r.view==="sales"&&Ze(),r.view==="branches"&&Ks()});window.addEventListener("popstate",()=>{Gs(),I(At(),{silent:!0})});I(_.initialView||At(),{replace:!0});
