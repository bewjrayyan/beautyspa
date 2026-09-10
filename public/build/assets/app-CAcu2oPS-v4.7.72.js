function Z(s,a){return getComputedStyle(document.documentElement).getPropertyValue(s).trim()||a}function e(s,a={}){const n=String(s).split(".");let i=window.IMMA_CENTRAL&&window.IMMA_CENTRAL.i18n||{};for(const o of n)if(i&&typeof i=="object"&&o in i)i=i[o];else{i=null;break}let l=typeof i=="string"?i:s;return Object.keys(a||{}).forEach(o=>{l=l.replace(new RegExp(":"+o,"g"),String(a[o]))}),l}const c=(s,a=document)=>{const n=typeof a=="string"?document.querySelector(a):a||document;return n?n.querySelector(s):null},M=(s,a=document)=>{const n=typeof a=="string"?document.querySelector(a):a||document;return n?[...n.querySelectorAll(s)]:[]},Dt={leads:[]},_=window.IMMA_CENTRAL||{};let r={view:"overview",branch:String(c("#branchScope")&&c("#branchScope").value||"all"),period:String(c("#periodScope")&&c("#periodScope").value||_.metrics&&_.metrics.period&&_.metrics.period.key||""),leadSearch:"",leadStatus:"all",leadBeautician:"all",leadBranch:"all",leadPage:1,leadMonth:(function(){const s=new Date;return s.getFullYear()+"-"+String(s.getMonth()+1).padStart(2,"0")})(),leadCalYear:null,followBucket:"all",followSearch:"",followPage:1,importTab:"paste",paymentTab:"queue",paymentSearch:"",paymentCustomerId:null,paymentCustomerLabel:"",paymentBeautician:"all",paymentBranch:"all",paymentPage:1,customerSegment:"all",customerSearch:"",customerBranch:"all",customerPage:1,walletSegment:"all",walletSearch:"",walletTier:"all",walletPage:1,walletCustomerId:null,checkinStatus:"live",checkinSearch:"",checkinBranch:"all",checkinBeautician:"all",checkinPage:1,checkinDate:_.today||new Date().toLocaleDateString("en-CA"),checkinScope:"day",clearanceState:"waiting",clearanceSearch:"",clearanceBranch:"all",clearanceBeautician:"all",clearancePage:1};const B=c("#viewRoot");let C=_.metrics||null,ca=C?JSON.stringify([r.branch,r.period]):null,sa=0,ie=[],ue={raw:0,unique:0,duplicates:0,existing:0,converted:0,conversion_pct:0},A={statuses:[],beauticians:[],branches:[],months:[]},Ve=!1,Pa=null,$e=null,we=[],da={queue:0,overdue:0,due_today:0,no_response:0,lost:0},ke={buckets:[],beauticians:[],branches:[],statuses:[]},Ye=!1,ja=null,me=[],ba={pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0},ve={statuses:[],beauticians:[],branches:[]},Ke=!1,Ba=null,pa={current_page:1,last_page:1,total:0},he=[],ga={total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0},Se={segments:[],branches:[]},ze=!1,Ea=null,xe={current_page:1,last_page:1,total:0},Pe=[],ya={members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0},lt={segments:[],tiers:[]},le=!1,Re=0,je=!1,Aa=null,fa={current_page:1,last_page:1,total:0},Be=[],$a={live:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0},ua={statuses:[],beauticians:[],branches:[]},re=!1,Ue=0,Ee=!1,qa=null,wa={current_page:1,last_page:1,total:0},_e=null,ma=0,Ae=[],ka={waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0},va={states:[],beauticians:[],branches:[]},oe=!1,Ie=0,qe=!1,Ha=null,Sa={current_page:1,last_page:1,total:0};function U(s,a){return String(s||"").replace("__ID__",String(a))}function D(s=!0){const a={Accept:"application/json","X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":_.csrf||document.querySelector('meta[name="csrf-token"]')?.content||""};return s&&(a["Content-Type"]="application/json"),a}async function q(){const s=_.leadsUrl||"";if(!s){w(e("workspace.load_error"));return}const a=new URLSearchParams;r.leadSearch&&a.set("q",r.leadSearch),r.leadStatus&&r.leadStatus!=="all"&&a.set("status",r.leadStatus);const n=r.leadBranch!=="all"?r.leadBranch:r.branch||"all";n&&n!=="all"&&a.set("branch",n),r.leadBeautician&&r.leadBeautician!=="all"&&a.set("beautician",r.leadBeautician),r.leadMonth&&r.leadMonth!=="all"&&a.set("month",r.leadMonth),a.set("page",String(r.leadPage||1)),a.set("per_page","50"),Ve=!0,Ia();try{const i=await fetch(`${s}?${a.toString()}`,{headers:D(!1),credentials:"same-origin"});if(!i.ok)throw new Error("leads "+i.status);const l=await i.json();rt=l.meta||{},ie=Array.isArray(l.data)?l.data:[],ue=l.meta&&l.meta.summary||ue,A=l.filters||A,Dt.leads=ie}catch(i){console.error(i),w(e("workspace.load_error"))}finally{Ve=!1,r.view==="leads"&&(ht(),Ia(),Je(),Ne())}}function v(s){return Number(s||0).toLocaleString("en-MY")}function L(s){return"RM"+Number(s||0).toLocaleString("en-MY",{maximumFractionDigits:0})}function W(s){return Number(s||0).toLocaleString("en-MY",{maximumFractionDigits:1})+"%"}function t(s){return String(s??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;")}function Y(s,a=""){const n=Number(s||0),i=Math.abs(n).toFixed(1)+a;return n>0?`↑ +${i} ${e("overview.vs_prev_period")}`:n<0?`↓ -${i} ${e("overview.vs_prev_period")}`:`→ 0${a} ${e("overview.vs_prev_period")}`}function Q(s,a){return`${e("overview.target")}: ${a}`}async function Ma(){const s=_.metricsUrl||"",a=++sa,n=r.branch||"all",i=r.period||"",l=JSON.stringify([n,i]),o=new URLSearchParams({branch:n,period:i});try{if(!s)throw new Error("Missing metrics endpoint");const d=await fetch(`${s}?${o.toString()}`,{headers:D(!1),credentials:"same-origin"});if(!d.ok)throw new Error("metrics "+d.status);const u=await d.json();if(a!==sa||l!==JSON.stringify([r.branch,r.period]))return;if(!u.metrics)throw new Error("Missing metrics payload");C=u.metrics,ca=l,_.metrics=C,r.view==="overview"&&pt(),r.view==="sales"&&Pt()}catch{if(a!==sa||l!==JSON.stringify([r.branch,r.period]))return;["overview","sales"].includes(r.view)&&ca!==l?(B.innerHTML=`<section class="card"><div class="pay-empty" role="alert"><strong>${t(e("overview.metrics_error"))}</strong><button type="button" class="btn" id="retryMetrics">${t(e("reporting.refresh"))}</button></div></section>`,c("#retryMetrics").onclick=()=>Ma()):w(e("overview.metrics_error"))}}function G(s,a={}){const n=Math.max(1,Number(a.last_page)||1),i=Math.max(1,Math.min(n,Number(a.current_page)||1)),l=n<=5?Array.from({length:n},(p,m)=>m+1):[...new Set([1,i-1,i,i+1,n].filter(p=>p>=1&&p<=n))].sort((p,m)=>p-m),o=(p,m,h="")=>`<button type="button" class="central-pagination__button" data-page="${p}" ${h}>${m}</button>`;let d="",u=0;return l.forEach(p=>{u&&p-u>1&&(d+='<span class="central-pagination__ellipsis" aria-hidden="true">…</span>'),d+=o(p,String(p),`aria-label="${t(e("pagination.page",{page:p}))}" ${p===i?'aria-current="page" disabled':""}`),u=p}),`<nav class="central-pagination" data-pager="${s}" aria-label="${t(e("pagination.label"))}">
    ${o(i-1,"‹",`id="${s}Prev" aria-label="${t(e("operations.previous"))}" ${i<=1?"disabled":""}`)}
    ${d}
    ${o(i+1,"›",`id="${s}Next" aria-label="${t(e("operations.next"))}" ${i>=n?"disabled":""}`)}
  </nav>`}function J(s,a){const n=c(`[data-pager="${s}"]`);n&&M("button[data-page]",n).forEach(i=>i.onclick=async()=>{if(i.disabled)return;const l=M("button:not(:disabled)",n);l.forEach(o=>o.disabled=!0),n.setAttribute("aria-busy","true");try{await a(Number(i.dataset.page))}finally{n.isConnected&&(l.forEach(o=>o.disabled=!1),n.removeAttribute("aria-busy"))}})}let rt={},ot={},Na=1;function H(s){return"RM"+Number(s).toLocaleString("en-MY")}function P(s){const a=String(s??""),n=a.toUpperCase();let i="gray";return n.includes("VERIFIED")||n.includes("PAID")||n==="CONVERTED"||n==="COMPLETED"||n.includes("BANK CHECKED")||n.includes("PROOF")?i="success":n.includes("FOLLOW")||n.includes("PENDING")||n.includes("REVIEW")||n==="BOOKING"||n==="CLAIMED"||n.includes("PROCESSING")||n.includes("DECLARED")?i="warning":n.includes("HOLD")||n.includes("LOST")||n.includes("NO RESPONSE")||n.includes("CANCEL")||n.includes("REFUND")?i="danger":n==="NEW"&&(i="blue"),`<span class="badge ${i}"><span class="status-dot"></span>${t(a)}</span>`}function de(s,a,n=""){return`<div class="page-head"><div><h1 class="page-title">${s}</h1><div class="page-subtitle">${a}</div></div><div class="page-actions">${n}</div></div>`}function x(s,a,n,i,l,o="blue",d=null,u="",p=""){const m=t(u||a),h=t(a),g=t(n),f=t(i),b=t(l),$=t(p),S=/^[↑+]/.test(String(i).trim())||/above|\+|up/i.test(String(i)),y=/^[↓-]/.test(String(i).trim())||/below|down/i.test(String(i))?"down":S?"up":"flat";return`<div class="kpi-card kpi-card--${o}">
    <div class="kpi-card__head">
      <div class="kpi-card__icon" aria-hidden="true">${s}</div>
      <div class="kpi-label" title="${m}">${h}</div>
    </div>
    <div class="kpi-value mono">${g}</div>
    <div class="kpi-card__foot">
      <span class="kpi-trend kpi-trend--${y}">${f}</span>
      <span class="kpi-target">${b}</span>
    </div>
    ${p?`<div class="kpi-spark" id="${$}"></div>`:""}
    ${d!==null?`<div class="progress kpi-card__bar"><span style="width:${Math.min(100,d)}%"></span></div>`:""}
  </div>`}function fe(s,a,n,i=""){const l=i?` trade-pane__chart--${i}`:"";return`<section class="trade-pane">
    <div class="trade-pane__head">
      <div>
        <div class="trade-pane__eyebrow">${e("trade.section")}</div>
        <div class="trade-pane__title">${e(s)}</div>
        <div class="trade-pane__sub">${e(a)}</div>
      </div>
    </div>
    <div class="trade-pane__chart${l}" id="${n}"></div>
  </section>`}function Rt(s=new Date){const a=["JAN","FEB","MAC","APR","MEI","JUN","JUL","OGOS","SEPT","OKT","NOV","DIS"];return`${String(s.getDate()).padStart(2,"0")} ${a[s.getMonth()]} ${s.getFullYear()}`}function Ut(){const s=C||{},a=s.kpis||{},n=s.targets||{},i=Array.isArray(s.beauticians)?s.beauticians:[],l=Number(a.new_buyers||0),o=Number(n.leads||0),d=o>0?Math.round(l/o*1e3)/10:0,u=!!(s.period&&s.period.key),p=s.period&&s.period.label?t(s.period.label):Rt(),m=s.slogan||e("daily.slogan"),h=s.quote||e("daily.quote");return`<section class="daily-hero">
    <div class="daily-hero__top">
      <div class="daily-hero__copy">
        <div class="daily-hero__eyebrow">${e("daily.eyebrow")}</div>
        <h2 class="daily-hero__title">${e("daily.title")}</h2>
        <div class="daily-hero__meta">
          <span class="daily-hero__date">${p}</span>
          <span class="daily-hero__pill">${e("daily.live")}</span>
        </div>
        <p class="daily-hero__quote">${t(m)} -- "${t(h)}"</p>
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
        <div class="daily-stat__value daily-stat__value--sm">${v(l)} / ${v(o)}</div>
        <div class="daily-stat__sub">${e("daily.of_monthly_target",{pct:d})}</div>
        <div class="progress daily-stat__bar"><span style="width:${Math.min(100,d)}%"></span></div>
      </div>
    </div>
  </section>`}const Da=[["#1d4ed8","#60a5fa"],["#9a3412","#f59e0b"],["#065f46","#34d399"],["#6d28d9","#a78bfa"],["#be123c","#fb7185"],["#0e7490","#22d3ee"],["#a16207","#facc15"]];function It(s){const a=String(s||"");let n=0;for(let l=0;l<a.length;l++)n=n*31+a.charCodeAt(l)>>>0;const i=Da[Math.abs(n)%Da.length];return`linear-gradient(145deg, ${i[0]}, ${i[1]})`}function Ft(s){const a=String(s||"").trim().split(/\s+/).filter(Boolean);return((a[0]?.[0]||"")+(a[1]?.[0]||"")).toUpperCase()}function Ot(){const s=C||{},a=!!(s.period&&s.period.key),n=Array.isArray(s.beauticians)?s.beauticians:[],i=Array.isArray(s.beauticians)?s.beauticians.length:0,l=Math.max(1,Number(s.beautician_count)||i||1),o=Math.round((Number(s.targets&&s.targets.leads||0)||0)/l),d=[...n].sort((g,f)=>f.leads-g.leads),u=s.period&&s.period.label?t(s.period.label):e("common.this_month"),p=d.reduce((g,f)=>g+(Number(f.leads)||0),0),m=["🥇","🥈","🥉"],h=d.map((g,f)=>{const b=Number(g.leads)||0,$=Number(g.target)||o,S=$>0?Math.round(b/$*100):0,T=p>0?Math.max(4,Math.round(b/p*100)):0,y=f===0?"gold":f===1?"silver":f===2?"bronze":"",k=S>=100?"is-hit":S>=75?"is-close":"is-low",j=t(String(g.name||"")),E=f<3?`<span class="rank rank--medal rank--${y}" title="${e("daily.rank_title",{n:f+1})}">${m[f]}</span>`:`<span class="rank">${f+1}</span>`;return`<tr class="daily-row${y?` daily-row--${y}`:""}">
      <td class="daily-rank-cell">${E}</td>
      <td>
        <div class="person-cell">
          <div class="mini-avatar leader-avatar${y?` mini-avatar--${y}`:""}" style="background:${It(g.name)}">${Ft(g.name)}</div>
          <div class="person-cell__text">
            <strong>${j}${f===0?` <span class="leader-tag">${e("daily.leader")}</span>`:""}</strong>
            <small>${e("daily.target_month",{count:$})}<i class="daily-dot"></i>${u}</small>
          </div>
        </div>
      </td>
      <td class="daily-num-cell"><strong class="daily-num" title="${b.toLocaleString()} ${e("daily.leads")}">${b.toLocaleString()}</strong></td>
      <td>
        <div class="share-cell">
          <div class="share-cell__track"><span style="width:${Math.min(100,T)}%"></span></div>
          <strong>${T}%</strong>
        </div>
      </td>
      <td><span class="daily-target">${$}</span></td>
      <td>
        <div class="achieve">
          <strong class="achieve__pct ${k}">${S}%</strong>
          <div class="progress achieve__bar"><span class="achieve__fill ${k}" style="width:${Math.min(100,S)}%"></span></div>
        </div>
      </td>
    </tr>`}).join("")||`<tr><td colspan="6"><div class="empty"><strong>${e("overview.no_beautician_data")}</strong></div></td></tr>`;return`<div class="grid daily-split">
    <section class="card daily-board">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${e(a?"daily.rank_eyebrow":"daily.ranking")}</div>
          <div class="daily-panel__title">${e("daily.leaderboard")}</div>
          <div class="daily-panel__sub">${e(a?"daily.rank_sub_month":"daily.rank_sub",{date:u})}</div>
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
          <tbody>${h}</tbody>
        </table>
      </div>
      <div class="daily-motto">${e("daily.motto")}</div>
    </section>
    <section class="card daily-charts">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${e("daily.distribution")}</div>
          <div class="daily-panel__title">${e("daily.by_beautician")}</div>
          <div class="daily-panel__sub">${e(a?"daily.leads_share_period":"daily.daily_share",a?{period:u}:{})}</div>
        </div>
      </div>
      <div class="chart-wrap daily-charts__bar"><canvas id="dailyLeadChart"></canvas></div>
      <div class="daily-charts__divider">
        <div class="daily-panel__eyebrow">${e("daily.trend")}</div>
        <div class="daily-panel__title daily-panel__title--sm">${e(a?"daily.trend_period":"daily.trend_7d",a?{period:u}:{})}</div>
      </div>
      <div class="chart-wrap daily-charts__trend"><canvas id="dailyTrendChart"></canvas></div>
    </section>
  </div>`}function ha(s,a,n,i,l,o){const d=Math.min(o,i/2,l/2);s.beginPath(),s.moveTo(a+d,n),s.arcTo(a+i,n,a+i,n+l,d),s.arcTo(a+i,n+l,a,n+l,d),s.arcTo(a,n+l,a,n,d),s.arcTo(a,n,a+i,n,d),s.closePath()}function ct(){const s=c("#dailyLeadChart");if(!s)return;De(s);const a=s.getContext("2d"),n=C||{},i=Array.isArray(n.beauticians)?n.beauticians:[],o=[...i.length?i.map(y=>({name:String(y.name||""),leads:Number(y.leads||0)})):[]].sort((y,k)=>k.leads-y.leads),d=o.map(y=>y.name),u=o.map(y=>y.leads),p=s.clientWidth,m=s.clientHeight,h={l:28,r:10,t:28,b:42},g=Math.max(...u,1)*1.2,f=Math.max(6,Math.min(12,p/60)),b=Math.max(14,(p-h.l-h.r-f*(u.length-1))/u.length),$=Z("--navy","#1d4ed8"),S=Z("--rose","#0ea5e9");a.fillStyle="#f8fafc",ha(a,0,0,p,m,12),a.fill(),a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let y=0;y<4;y++){const k=h.t+y*((m-h.t-h.b)/3);a.beginPath(),a.moveTo(h.l,k),a.lineTo(p-h.r,k),a.stroke()}const T=[["#1d4ed8","#38bdf8"],["#2563eb","#7dd3fc"],["#0284c7","#67e8f9"]];u.forEach((y,k)=>{const j=h.l+k*(b+f),E=Math.max(4,y/g*(m-h.t-h.b)),pe=m-h.b-E,[I,Ht]=T[Math.min(k,2)]||[$,S],ta=a.createLinearGradient(0,pe,0,m-h.b);ta.addColorStop(0,k<3?Ht:S),ta.addColorStop(1,k<3?I:"#93c5fd"),a.fillStyle=ta,ha(a,j,pe,b,E,8),a.fill(),a.fillStyle="#0f172a",a.font="700 12px Poppins",a.textAlign="center",a.fillText(String(y),j+b/2,pe-8);const Nt=d[k].length>7?d[k].slice(0,6)+"…":d[k];a.fillStyle="#475569",a.font="600 10px Poppins",a.fillText(Nt,j+b/2,m-14)})}function dt(){const s=c("#dailyTrendChart");if(!s)return;De(s);const a=s.getContext("2d"),n=C||{},i=n.leads_trend&&Array.isArray(n.leads_trend.actual)?n.leads_trend:null,l=i?[...i.actual]:[],o=i&&Array.isArray(i.labels)?i.labels:Array.from({length:l.length},(S,T)=>T===l.length-1?"Today":"D-"+(l.length-1-T)),d=s.clientWidth,u=s.clientHeight,p={l:28,r:14,t:22,b:28},m=Math.max(...l,1)*1.15,h=S=>p.l+S*((d-p.l-p.r)/Math.max(l.length-1,1)),g=S=>u-p.b-S/m*(u-p.t-p.b),f=Z("--navy","#1d4ed8"),b=Z("--rose","#0ea5e9");a.fillStyle="#f8fafc",ha(a,0,0,d,u,12),a.fill(),a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let S=0;S<3;S++){const T=p.t+S*((u-p.t-p.b)/2);a.beginPath(),a.moveTo(p.l,T),a.lineTo(d-p.r,T),a.stroke()}const $=a.createLinearGradient(0,p.t,0,u-p.b);$.addColorStop(0,"rgba(14,165,233,.28)"),$.addColorStop(1,"rgba(37,99,235,.02)"),a.beginPath(),a.moveTo(h(0),u-p.b),l.forEach((S,T)=>a.lineTo(h(T),g(S))),a.lineTo(h(l.length-1),u-p.b),a.closePath(),a.fillStyle=$,a.fill(),a.beginPath(),l.forEach((S,T)=>T?a.lineTo(h(T),g(S)):a.moveTo(h(T),g(S))),a.strokeStyle=f,a.lineWidth=2.75,a.lineJoin="round",a.lineCap="round",a.stroke(),l.forEach((S,T)=>{const y=h(T),k=g(S);a.beginPath(),a.arc(y,k,5,0,Math.PI*2),a.fillStyle="#fff",a.fill(),a.lineWidth=2.5,a.strokeStyle=b,a.stroke(),a.beginPath(),a.arc(y,k,2.2,0,Math.PI*2),a.fillStyle=f,a.fill(),a.fillStyle="#0f172a",a.font="700 10px Poppins",a.textAlign="center",a.fillText(String(S),y,k-10),a.fillStyle="#64748b",a.font="600 10px Poppins",a.fillText(o[T],y,u-10)})}function Wt(){const s="company_target",a=C||{},n=a.targets||{},i=Math.max(1,Number(a.beautician_count)||1),l=Number(n.leads)||0,o=Number(n.conv_pct)||0,d=Number(n.buyers)||0,u=Number(n.avg_sale)||0,p=Number(n.sales)||0,m=Math.round(l/i),h=Math.round(l*o/100/i),g=Math.round(h*u),f=k=>{const j=Number(k||0);return Math.abs(j)>=1e6?"RM"+(j/1e6).toFixed(1).replace(/\.0$/,"")+"M":Math.abs(j)>=1e3?"RM"+(j/1e3).toFixed(1).replace(/\.0$/,"")+"k":L(j)},b=v(l),$=W(o),S=v(d),T=L(u),y=f(p);return`<section class="company-target">
    <div class="company-target__hero">
      <div class="company-target__hero-copy">
        <div class="company-target__eyebrow">${e(s+".eyebrow")}</div>
        <h2 class="company-target__title">${e(s+".title",{leadgoal:b})}</h2>
        <p class="company-target__desc">${e(s+".desc",{leadgoal:b,convgoal:$,buyergoal:S,avggoal:T,salesgoal:y})}</p>
      </div>
      <div class="company-target__hero-goal">
        <div class="company-target__goal-label">${e(s+".sales_goal")}</div>
        <div class="company-target__goal-value">${L(p)}</div>
        <div class="company-target__goal-sub">${e(s+".goal_sub",{beaucount:i})}</div>
      </div>
    </div>

    <div class="company-target__chain" aria-label="${e(s+".chain_aria")}">
      ${[[b,e(s+".step_leads"),e(s+".step_leads_meta")],[$,e(s+".step_conv"),e(s+".step_conv_meta")],[S,e(s+".step_buyers"),e(s+".step_buyers_meta")],[T,e(s+".step_avg"),e(s+".step_avg_meta")],[y,e(s+".step_sales"),e(s+".step_sales_meta")]].map((k,j)=>`
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
          <span class="ct-card__badge">${e(s+".kpi1_badge")}</span>
          <span class="ct-card__icon">🎯</span>
        </div>
        <h3 class="ct-card__title">${e(s+".kpi1_title",{convgoal:$})}</h3>
        <p class="ct-card__text">${e(s+".kpi1_text",{leadgoal:b,buyergoal:S})}</p>
        <div class="ct-card__math">
          <div><span>${e(s+".kpi1_leads")}</span><strong>${b}</strong></div>
          <div><span>×</span><strong>${$}</strong></div>
          <div><span>${e(s+".kpi1_buyers")}</span><strong>${S}</strong></div>
        </div>
      </article>

      <article class="ct-card ct-card--kpi2">
        <div class="ct-card__head">
          <span class="ct-card__badge">${e(s+".kpi2_badge")}</span>
          <span class="ct-card__icon">💰</span>
        </div>
        <h3 class="ct-card__title">${e(s+".kpi2_title",{avggoal:T})}</h3>
        <p class="ct-card__text">${e(s+".kpi2_text",{buyergoal:S,avggoal:T,salesgoal:y})}</p>
        <div class="ct-card__math">
          <div><span>${e(s+".kpi2_buyers")}</span><strong>${S}</strong></div>
          <div><span>×</span><strong>${T}</strong></div>
          <div><span>${e(s+".kpi2_sales")}</span><strong>${y}</strong></div>
        </div>
      </article>
    </div>

    <div class="company-target__beauty-head">
      <div>
        <div class="daily-panel__eyebrow">${e(s+".per_beautician")}</div>
        <div class="daily-panel__title">${e(s+".per_title")}</div>
        <div class="daily-panel__sub">${e(s+".per_sub",{beaucount:i})}</div>
      </div>
    </div>
    <div class="grid company-target__beauty">
      <article class="ct-card">
        <div class="ct-card__head"><span class="ct-card__badge">${e(s+".badge_leads")}</span></div>
        <div class="ct-card__big">${v(m)}</div>
        <div class="ct-card__unit">${e(s+".unit_leads")}</div>
        <p class="ct-card__text">${e(s+".text_leads",{leadgoal:b,beaucount:i,leadtarget:v(m)})}</p>
      </article>
      <article class="ct-card">
        <div class="ct-card__head"><span class="ct-card__badge">${e(s+".badge_convert")}</span></div>
        <div class="ct-card__big">±${v(h)}</div>
        <div class="ct-card__unit">${e(s+".unit_convert")}</div>
        <p class="ct-card__text">${e(s+".text_convert",{leadtarget:v(m),convgoal:$,buytarget:v(h)})}</p>
      </article>
      <article class="ct-card ct-card--accent">
        <div class="ct-card__head"><span class="ct-card__badge">${e(s+".badge_sales")}</span></div>
        <div class="ct-card__big">${f(g)}</div>
        <div class="ct-card__unit">${e(s+".unit_sales")}</div>
        <p class="ct-card__text">${e(s+".text_sales",{buytarget:v(h),avggoal:T,salarget:L(g),beaucount:i,compact:f(g),total:y})}</p>
      </article>
    </div>
  </section>`}function pt(){window.IMMA_TRADE&&IMMA_TRADE.dispose();const s=C||{},a=s.kpis||{},n=a.vs_prev||{},i=s.targets||{},l=s.dual||{},o=Number(l.sales_pct||0),d=Number(i.sales||0),u=Number(a.sales||0),p=u-d,m=t(s.period&&s.period.label||e("common.this_month")),h=s.ops||{},g=h.checkin||{},f=h.clearance||{},b=h.payments||{},$=e("ops.value_customers",{count:v(g.live||0)}),S=e("ops.meta_checkin",{waiting:v(g.waiting||0),treatment:v(g.in_treatment||0)}),T=e("ops.value_customers",{count:v(f.queue||0)}),y=e("ops.meta_clearance",{waiting:v(f.waiting||0),blocked:v(f.blocked||0)}),k=e("ops.value_pending",{count:v(b.queue||0)}),j=e("ops.meta_payments",{processing:v(b.processing||0),hold:v(b.hold||0)});B.innerHTML=`${de(e("overview.title"),e("overview.subtitle"),`<button class="btn soft">${m}</button><button class="btn primary" data-jump="leads">${e("common.open_leads")}</button>`)}
  ${Wt()}
  <div class="trade-grid trade-grid--2">
    ${fe("trade.dual_title","trade.dual_sub","tradeDualRing","sm")}
    ${fe("trade.equity_title","trade.equity_sub","tradeEquity","lg")}
  </div>
  <div class="section-label">${e("overview.actual_label")}</div>
  <div class="grid kpi-grid">
    ${x("♙",e("overview.kpi_unique_leads"),v(a.new_buyers),Y(n.new_buyers,"%"),Q(e("overview.kpi_unique_leads"),v(i.leads||0)),"rose",Math.min(100,(a.new_buyers||0)/Math.max(1,i.leads||0)*100),"","sparkLeads")}
    ${x("▣",e("overview.kpi_buyers"),v(a.buyers),Y(n.buyers,"%"),Q(e("overview.kpi_buyers"),v(i.buyers||0)),"blue",Math.min(100,(a.buyers||0)/Math.max(1,i.buyers||0)*100),"","sparkBuyers")}
    ${x("%",e("overview.kpi_conv_rate"),W(a.new_buyer_share_pct),Y(n.new_buyer_share_pct,"pp"),Q(e("overview.kpi_conv_rate"),W(i.conv_pct||0)),"green",Math.min(100,Number(a.new_buyer_share_pct||0)),"","sparkConv")}
    ${x("◫",e("overview.kpi_sales"),L(a.sales),Y(n.sales,"%"),Q(e("overview.kpi_sales"),L(d)),"rose",Math.min(100,o),"","sparkSales")}
    ${x("▥",e("overview.kpi_avg_sale"),L(a.avg_sale),Y(n.avg_sale,"%"),Q(e("overview.kpi_avg_sale"),L(i.avg_sale||0)),"purple",Math.min(100,(a.avg_sale||0)/Math.max(1,i.avg_sale||0)*100),"","sparkAvg")}
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
        <span class="sales-card__pill">${m} · ${L(u)}</span>
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

  <section class="card" style="margin-top:12px"><div class="card-title-row"><div class="card-title">♙ ${e("overview.beauticians")}</div><button class="btn small primary" data-jump="beauticians">${e("common.view_all")}</button></div>${Vt()}</section>

  <div class="grid three-col" style="margin-top:12px">
    ${(s.branches||[]).slice(0,6).map(E=>ut(E.name,E.new_buyers||0,E.buyers||0,E.conv||0,E.sales||0,E.avg||0,E.buyers||0)).join("")||`<section class="card"><div class="empty"><strong>${e("overview.no_branch_data")}</strong></div></section>`}
  </div>

  <div class="grid op-row" style="margin-top:12px">
    ${na("♧",e("ops.checkin"),$,S,"checkin",e("ops.checkin"),"green")}
    ${na("◷",e("ops.clearance"),T,y,"clearance",e("ops.clearance"),"warning")}
    ${na("▣",e("ops.payments"),k,j,"payments",e("ops.payments"),"rose")}
  </div>
  <div style="margin-top:12px">${Ut()}</div>
  <div style="margin-top:12px">${Ot()}</div>`,requestAnimationFrame(()=>{if(ct(),dt(),jt(),Ze(),X(),window.IMMA_TRADE){const E=Object.assign({},s.ticker||{}),pe={leadsUp:Number(n.new_buyers||0)>=0,convUp:Number(n.new_buyer_share_pct||0)>=0,salesUp:Number(n.sales||0)>=0,targetUp:o>=100,avgUp:Number(n.avg_sale||0)>=0};IMMA_TRADE.render({ticker:Object.assign(E,pe),leadsPct:Number(l.buyers_pct||l.leads_pct||0),salesPct:o,equityActual:s.equity&&s.equity.actual||[],equityTarget:s.equity&&s.equity.target_path||[],equityLabels:s.equity&&s.equity.labels||[],waterfall:(s.waterfall||s.status_mix||[]).map(I=>({name:String(I.name||""),value:I.value})),beauticians:(s.beauticians||[]).map(I=>({name:String(I.name||""),leads:I.leads||0,conv:I.conv||0,sales:I.sales||0})),heatmap:s.heatmap||[],sparks:s.sparks||[]})}})}function Vt(){const s=Array.isArray(C?.beauticians)?C.beauticians:[];return s.length?`<div class="table-wrap"><table class="data-table"><thead><tr><th>#</th><th>${e("overview.col_beautician")}</th><th title="${e("overview.kpi_unique_leads")}">${e("overview.unique")}</th><th>${e("overview.kpi_buyers")}</th><th title="${e("overview.kpi_conv_rate")}">${e("overview.conv_rate")}</th><th>${e("overview.kpi_sales")}</th><th>${e("overview.kpi_avg_sale")}</th><th>${e("overview.col_orders")}</th></tr></thead><tbody>${s.map((a,n)=>{const i=t(a.name);return`<tr><td><span class="rank">${n+1}</span></td><td><strong>${i}</strong></td><td>${v(a.leads)}</td><td>${v(a.buyers)}</td><td style="color:${a.conv>=40?"var(--success)":a.conv<35?"var(--danger)":"#b16e10"};font-weight:700">${W(a.conv)}</td><td>${L(a.sales)}</td><td>${L(a.avg)}</td><td>${v(a.orders||0)}</td></tr>`}).join("")}</tbody></table></div>`:`<div class="empty"><strong>${e("overview.no_beautician_data")}</strong></div>`}function ut(s,a,n,i,l,o,d){const u=i>=40?"good":i>=35?"ok":"low",p=t(s),m=t(String(s||"").slice(0,2).toUpperCase());return`<section class="card branch-card branch-card--${u}">
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
        <div class="branch-card__sales-value">${H(l)}</div>
      </div>
    </div>
    <div class="branch-card__bar" aria-hidden="true"><span style="width:${Math.min(100,i)}%"></span></div>
    <div class="branch-card__metrics">
      <div class="branch-metric"><span title="${e("overview.unique")}">${e("overview.unique")}</span><strong>${a.toLocaleString()}</strong></div>
      <div class="branch-metric"><span>${e("overview.converted")}</span><strong>${n.toLocaleString()}</strong></div>
      <div class="branch-metric"><span title="${e("overview.kpi_avg_sale")}">${e("overview.kpi_avg_sale")}</span><strong>${H(o)}</strong></div>
      <div class="branch-metric"><span title="${e("overview.treat_done")}">${e("overview.treat_done")}</span><strong>${d.toLocaleString()}</strong></div>
    </div>
  </section>`}function na(s,a,n,i,l,o,d){return`<section class="card op-card"><div class="op-icon" style="background:${d==="green"?"var(--success-soft)":d==="warning"?"var(--warning-soft)":"var(--rose-soft)"}">${s}</div><div class="op-body"><div class="op-title">${a}</div><div class="op-value">${n}</div><div class="op-meta">${i}</div></div><button class="btn small primary" data-jump="${l}">${o} →</button></section>`}function Ra(s){(!r.leadMonth||r.leadMonth==="all")&&(r.leadMonth=ea());const[a,n]=r.leadMonth.split("-").map(Number),i=new Date(a,n-1+s,1);r.leadMonth=i.getFullYear()+"-"+String(i.getMonth()+1).padStart(2,"0"),r.leadPage=1,q().then(()=>Ne())}function He(s){if(!s||s==="all")return null;const[a,n]=String(s).split("-").map(Number);return!a||!n?null:{y:a,m:n}}function mt(){const s=(_.locale||"en").toLowerCase().startsWith("ms")?"ms-MY":"en-GB";return Array.from({length:12},(a,n)=>new Date(2e3,n,1).toLocaleString(s,{month:"short"}))}function vt(s){if(!s||s==="all")return e("workspace.all_months");const a=He(s);if(!a)return String(s);const i=(A.months||[]).find(l=>String(l.value)===String(s));return i?i.label:mt()[a.m-1]+" "+a.y}function ea(){const s=new Date;return s.getFullYear()+"-"+String(s.getMonth()+1).padStart(2,"0")}function Yt(){const s=He(r.leadMonth)||He(ea()),a=r.leadCalYear||s.y,n=!!_.canCreateLead;return`
    <div class="lead-cal" id="leadCalendar">
      <button type="button" class="lead-cal__nav" id="leadMonthPrev" title="${t(e("workspace.month_prev"))}" aria-label="${t(e("workspace.month_prev"))}">‹</button>
      <button type="button" class="lead-cal__toggle" id="leadCalToggle" aria-expanded="false" aria-haspopup="dialog">
        <span class="lead-cal__icon" aria-hidden="true">▦</span>
        <span id="leadMonthLabel">${t(vt(r.leadMonth))}</span>
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
    ${n?`
      <button type="button" class="btn" data-jump="import" data-import-tab="paste">${t(e("workspace.paste_leads"))}</button>
      <button type="button" class="btn" data-jump="import" data-import-tab="excel">${t(e("workspace.upload_excel"))}</button>
      <button type="button" class="btn primary" data-jump="import" data-import-tab="paste">${t(e("workspace.import_leads"))}</button>
    `:""}
  `}function Ge(){const s=c("#leadCalGrid"),a=c("#leadCalYearLabel");if(!s)return;const n=He(r.leadMonth),i=r.leadCalYear||(n?n.y:new Date().getFullYear());r.leadCalYear=i,a&&(a.textContent=String(i));const l=mt(),o=ea();s.innerHTML=l.map((d,u)=>{const p=`${i}-${String(u+1).padStart(2,"0")}`;return`<button type="button" class="lead-cal__month${String(r.leadMonth)===p?" is-selected":""}${p===o?" is-now":""}" data-month="${p}">${t(d)}</button>`}).join(""),M("[data-month]",s).forEach(d=>{d.onclick=()=>{r.leadMonth=d.dataset.month,r.leadPage=1,ne(),Ne(),q()}})}function Kt(){const s=c("#leadCalPanel"),a=c("#leadCalToggle");if(!s||!a)return;const n=He(r.leadMonth);r.leadCalYear=n?n.y:new Date().getFullYear(),s.classList.remove("hidden"),a.setAttribute("aria-expanded","true"),Ge()}function ne(){const s=c("#leadCalPanel"),a=c("#leadCalToggle");s&&s.classList.add("hidden"),a&&a.setAttribute("aria-expanded","false")}function Ne(){const s=c("#leadMonthLabel");s&&(s.textContent=vt(r.leadMonth)),c("#leadCalPanel")&&!c("#leadCalPanel").classList.contains("hidden")&&Ge()}function Ua(s){const a=c("#leadCalendar");!a||a.contains(s.target)||ne()}function zt(){c("#leadMonthPrev")&&(c("#leadMonthPrev").onclick=()=>{ne(),Ra(-1)}),c("#leadMonthNext")&&(c("#leadMonthNext").onclick=()=>{ne(),Ra(1)}),c("#leadCalToggle")&&(c("#leadCalToggle").onclick=s=>{s.stopPropagation();const a=c("#leadCalPanel");a&&a.classList.contains("hidden")?Kt():ne()}),c("#leadCalYearPrev")&&(c("#leadCalYearPrev").onclick=s=>{s.stopPropagation(),r.leadCalYear=(r.leadCalYear||new Date().getFullYear())-1,Ge()}),c("#leadCalYearNext")&&(c("#leadCalYearNext").onclick=s=>{s.stopPropagation(),r.leadCalYear=(r.leadCalYear||new Date().getFullYear())+1,Ge()}),c("#leadMonthThis")&&(c("#leadMonthThis").onclick=s=>{s.stopPropagation(),r.leadMonth=ea(),r.leadPage=1,ne(),Ne(),q()}),c("#leadCalAll")&&(c("#leadCalAll").onclick=s=>{s.stopPropagation(),r.leadMonth="all",r.leadPage=1,ne(),Ne(),q()}),document.removeEventListener("click",Ua),document.addEventListener("click",Ua)}function Gt(){const s=!!_.canCreateLead,a=Yt();B.innerHTML=`${de(e("workspace.title"),e("workspace.subtitle"),a)}
  <div class="grid kpi-grid" id="leadKpiMount"></div>
  <p class="card-subtitle" style="margin:8px 2px 0">${t(e("workspace.kpi_note"))}</p>
  <section class="lead-panel card" style="margin-top:14px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${t(e("workspace.list_title"))}</h2>
        <p class="lead-panel__sub">${t(e("workspace.list_subtitle"))}</p>
      </div>
      <div class="lead-panel__head-meta">
        <span class="lead-panel__count" id="leadResultCount">—</span>
        ${s?`<button class="btn primary" type="button" id="addLeadBtn">${t(e("workspace.add_lead"))}</button>`:""}
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
    <div class="lead-panel__body" id="leadTableMount"></div>
  </section>`,ht(),Je(),zt(),X(),c("#addLeadBtn")&&(c("#addLeadBtn").onclick=yt),q()}function ht(){const s=c("#leadKpiMount");if(!s)return;const a=ue||{},n=Number(a.raw||0),i=Number(a.unique||0),l=Number(a.duplicates||0),o=Number(a.existing||0),d=Number(a.converted||0),u=Number(a.conversion_pct||0),p=n>0?(i/n*100).toFixed(1):"0.0",m=n>0?(l/n*100).toFixed(1):"0.0";s.innerHTML=`
    ${x("▤",e("workspace.raw_leads"),v(n),"—","—","blue")}
    ${x("♙",e("workspace.unique_leads"),v(i),e("workspace.clean_rate",{pct:p}),"—","green")}
    ${x("⧉",e("workspace.duplicates"),v(l),e("workspace.of_raw",{pct:m}),"—","rose")}
    ${x("♧",e("workspace.existing"),v(o),e("workspace.matched_phone"),"—","purple")}
    ${x("%",e("workspace.conversion"),W(u),e("workspace.converted_customers",{count:v(d)}),e("workspace.target_conv"),"green")}
  `}function Je(){const s=c("#leadStatus"),a=c("#leadBeautician"),n=c("#leadBranchFilter");if(s){const l=[{value:"all",label:e("workspace.all_status")},...A.statuses||[]];s.innerHTML=l.map(o=>`<option value="${t(o.value)}" ${String(r.leadStatus)===String(o.value)?"selected":""}>${t(o.label)}</option>`).join(""),s.onchange=o=>{r.leadStatus=o.target.value,r.leadPage=1,q()}}if(a){const l=[{id:"all",name:e("workspace.all_beauticians")},...A.beauticians||[]];a.innerHTML=l.map(o=>`<option value="${t(o.id)}" ${String(r.leadBeautician)===String(o.id)?"selected":""}>${t(o.name)}</option>`).join(""),a.onchange=o=>{r.leadBeautician=o.target.value,r.leadPage=1,q()}}if(n){const l=[{id:"all",name:e("workspace.all_branches")},...A.branches||_.branches||[]];n.innerHTML=l.map(o=>`<option value="${t(o.id)}" ${String(r.leadBranch)===String(o.id)?"selected":""}>${t(o.name)}</option>`).join(""),n.onchange=o=>{r.leadBranch=o.target.value,r.leadPage=1,q()}}const i=c("#leadSearch");i&&(i.oninput=l=>{r.leadSearch=l.target.value,clearTimeout(Pa),Pa=setTimeout(()=>{r.leadPage=1,q()},350)}),_t()}function ia(s,a){if(s==="status"){const n=(A.statuses||[]).find(i=>String(i.value)===String(a));return n?n.label:a}if(s==="beautician"){const n=(A.beauticians||[]).find(i=>String(i.id)===String(a));return n?n.name:a}if(s==="branch"){const i=(A.branches||_.branches||[]).find(l=>String(l.id)===String(a));return i?i.name:a}return a}function _t(){const s=c("#leadResultCount"),a=Array.isArray(ie)?ie.length:ue&&ue.unique!=null?Number(ue.unique):0;s&&(s.textContent=a===1?e("workspace.results_count_one"):e("workspace.results_count",{count:v(a)}));const n=c("#leadActiveFilters");if(!n)return;const i=[];if(r.leadSearch&&String(r.leadSearch).trim()&&i.push({key:"q",label:`“${String(r.leadSearch).trim()}”`}),r.leadStatus&&r.leadStatus!=="all"&&i.push({key:"status",label:ia("status",r.leadStatus)}),r.leadBeautician&&r.leadBeautician!=="all"&&i.push({key:"beautician",label:ia("beautician",r.leadBeautician)}),r.leadBranch&&r.leadBranch!=="all"&&i.push({key:"branch",label:ia("branch",r.leadBranch)}),!i.length){n.hidden=!0,n.innerHTML="";return}n.hidden=!1,n.innerHTML=`
    <div class="lead-chips">
      ${i.map(l=>`<span class="lead-chip">${t(l.label)}<button type="button" class="lead-chip__x" data-clear-filter="${t(l.key)}" aria-label="${t(e("workspace.clear_filters"))}">×</button></span>`).join("")}
      <button type="button" class="lead-chips__clear" id="leadClearFilters">${t(e("workspace.clear_filters"))}</button>
    </div>
  `,M("[data-clear-filter]",n).forEach(l=>{l.onclick=()=>{const o=l.dataset.clearFilter;o==="q"&&(r.leadSearch=""),o==="status"&&(r.leadStatus="all"),o==="beautician"&&(r.leadBeautician="all"),o==="branch"&&(r.leadBranch="all"),r.leadPage=1;const d=c("#leadSearch");d&&o==="q"&&(d.value=""),Je(),q()}}),c("#leadClearFilters")&&(c("#leadClearFilters").onclick=()=>{r.leadSearch="",r.leadStatus="all",r.leadBeautician="all",r.leadBranch="all",r.leadPage=1;const l=c("#leadSearch");l&&(l.value=""),Je(),q()})}function Ia(){const s=c("#leadTableMount");if(!s)return;if(_t(),Ve){s.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${t(e("workspace.loading"))}</strong></div>`;return}const a=ie;if(!a.length){const n=!!_.canCreateLead;s.innerHTML=`<div class="lead-empty">
      <div class="lead-empty__icon" aria-hidden="true">◎</div>
      <strong>${t(e("workspace.no_leads"))}</strong>
      <p>${t(e("workspace.no_leads_hint"))}</p>
      ${n?`<button type="button" class="btn primary" id="emptyAddLeadBtn">${t(e("workspace.empty_cta"))}</button>`:""}
    </div>`,c("#emptyAddLeadBtn")&&(c("#emptyAddLeadBtn").onclick=yt);return}s.innerHTML=`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${t(e("workspace.col_lead_id"))}</th><th>${t(e("workspace.col_date"))}</th><th>${t(e("workspace.col_customer"))}</th>
    <th>${t(e("workspace.col_phone"))}</th><th>${t(e("workspace.col_email"))}</th><th>${t(e("workspace.col_source"))}</th>
    <th>${t(e("workspace.col_beautician"))}</th><th>${t(e("workspace.col_branch"))}</th><th>${t(e("workspace.col_status"))}</th>
    <th>${t(e("workspace.col_payment"))}</th><th class="is-num">${t(e("workspace.col_sales"))}</th><th title="${t(e("workspace.col_last_fu"))}">${t(e("workspace.col_last_fu"))}</th>
    <th class="lead-table__actions"><span class="sr-only">${t(e("workspace.col_action"))}</span></th>
  </tr></thead><tbody>${a.map(n=>{const i=String(n.name||""),l=t((i[0]||"?").toUpperCase()),o=!!_.canEditLead,d=!!_.canDeleteLead,u=String(n.email||"").trim(),p=String(n.source||"").trim(),m=String(n.beautician||"").trim(),h=String(n.branch||"").trim(),g=String(n.last||"").trim(),f=Number(n.sales||0);return`<tr>
      <td><span class="lead-code">${t(n.code||n.id)}</span></td>
      <td><span class="lead-date">${t(n.date||"—")}</span></td>
      <td>
        <div class="person-cell person-cell--lead">
          <div class="mini-avatar" aria-hidden="true">${l}</div>
          <div class="person-cell__text">
            <strong>${t(i||"—")}</strong>
            ${n.customer?`<small>${t(n.customer)}</small>`:""}
          </div>
        </div>
      </td>
      <td><span class="lead-mono">${t(n.phone||"—")}</span></td>
      <td>${u?`<span class="lead-email" title="${t(u)}">${t(u)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td>${p?`<span class="lead-tag">${t(p)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td>${m?t(m):'<span class="lead-muted">—</span>'}</td>
      <td>${h?`<span class="lead-branch">${t(h)}</span>`:'<span class="lead-muted">—</span>'}</td>
      <td>${P(n.status)}</td>
      <td>${P(n.payment)}</td>
      <td class="is-num"><span class="lead-money${f?"":" is-zero"}">${f?H(f):"RM0"}</span></td>
      <td><span class="lead-date">${g?t(g):"—"}</span></td>
      <td class="lead-table__actions">
        <div class="lead-menu">
          <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${t(e("workspace.row_actions"))}">
            <span class="lead-menu__dots" aria-hidden="true"></span>
          </button>
          <div class="lead-menu__panel" role="menu" hidden>
            <button type="button" class="lead-menu__item" role="menuitem" data-lead-id="${t(n.id)}">${t(e("workspace.view"))}</button>
            ${o?`<button type="button" class="lead-menu__item" role="menuitem" data-lead-edit="${t(n.id)}">${t(e("workspace.edit"))}</button>`:""}
            ${d?`<button type="button" class="lead-menu__item lead-menu__item--danger" role="menuitem" data-lead-del="${t(n.id)}">${t(e("workspace.delete"))}</button>`:""}
          </div>
        </div>
      </td>
    </tr>`}).join("")}</tbody></table></div>`,s.insertAdjacentHTML("beforeend",G("leads",rt)),J("leads",n=>(r.leadPage=n,q())),gt(s),M("[data-lead-id]",s).forEach(n=>n.onclick=()=>{K(),aa(n.dataset.leadId)}),M("[data-lead-edit]",s).forEach(n=>n.onclick=()=>{K(),ft(n.dataset.leadEdit)}),M("[data-lead-del]",s).forEach(n=>n.onclick=()=>{K(),wt(n.dataset.leadDel)})}function bt(s){s&&(s.classList.remove("is-up"),s.style.top="",s.style.left="",s.style.right="",s.style.bottom="")}function Jt(s,a){if(!s||!a)return;const n=4,i=s.getBoundingClientRect();a.style.top="0px",a.style.left="0px",a.style.right="auto",a.style.bottom="auto";const l=a.getBoundingClientRect(),d=window.innerHeight-i.bottom<l.height+n+8;a.classList.toggle("is-up",d);let u=d?i.top-l.height-n:i.bottom+n,p=i.right-l.width;p=Math.max(8,Math.min(p,window.innerWidth-l.width-8)),u=Math.max(8,Math.min(u,window.innerHeight-l.height-8)),a.style.top=`${Math.round(u)}px`,a.style.left=`${Math.round(p)}px`}function K(s=null){M(".lead-menu").forEach(a=>{if(s&&a===s)return;const n=c(".lead-menu__btn",a),i=c(".lead-menu__panel",a);n&&n.setAttribute("aria-expanded","false"),i&&(i.hidden=!0,bt(i)),a.classList.remove("is-open")})}function Fa(s){s.target.closest&&s.target.closest(".lead-menu")||K()}function Oa(s){s.key==="Escape"&&K()}function Fe(){K()}function gt(s){M("[data-lead-menu]",s).forEach(a=>{a.onclick=n=>{n.stopPropagation();const i=a.closest(".lead-menu"),l=c(".lead-menu__panel",i),o=a.getAttribute("aria-expanded")==="true";K(o?null:i),o?(a.setAttribute("aria-expanded","false"),l&&(l.hidden=!0,bt(l)),i.classList.remove("is-open")):(a.setAttribute("aria-expanded","true"),l&&(l.hidden=!1,Jt(a,l)),i.classList.add("is-open"))}}),document.removeEventListener("click",Fa),document.addEventListener("click",Fa),document.removeEventListener("keydown",Oa),document.addEventListener("keydown",Oa),window.removeEventListener("scroll",Fe,!0),window.addEventListener("scroll",Fe,!0),window.removeEventListener("resize",Fe),window.addEventListener("resize",Fe)}function yt(){$e=null,se(e("workspace.manual_entry"),e("workspace.manual_sub"),$t({}),`<button class="btn" type="button" data-action-drawer-close>${t(e("workspace.cancel"))}</button><button class="btn primary" type="button" id="saveLead">${t(e("workspace.save"))}</button>`,e("workspace.title"))}function ft(s){const a=ie.find(n=>String(n.id)===String(s));if(!a){aa(s);return}$e=a.id,se(e("workspace.edit_entry"),e("workspace.edit_sub"),$t(a),`<button class="btn" type="button" data-action-drawer-close>${t(e("workspace.cancel"))}</button><button class="btn primary" type="button" id="saveLead">${t(e("workspace.save"))}</button>`,e("workspace.title"))}function $t(s={}){const a=(A.statuses||[]).map(o=>`<option value="${t(o.value)}" ${String(s.status_key||"")===String(o.value)?"selected":""}>${t(o.label)}</option>`).join(""),n=[{id:"",name:"—"},...A.branches||[]].map(o=>`<option value="${t(o.id)}" ${String(s.branch_id||"")===String(o.id)?"selected":""}>${t(o.name)}</option>`).join(""),i=[{id:"",name:"—"},...A.beauticians||[]].map(o=>`<option value="${t(o.id)}" ${String(s.beautician_id||"")===String(o.id)?"selected":""}>${t(o.name)}</option>`).join(""),l=String(s.source||"manual");return`<div class="detail-grid">
      <div><label class="kpi-label">${t(e("workspace.name"))}</label><input class="search" style="width:100%" id="mName" autocomplete="name" value="${t(s.name||"")}"></div>
      <div><label class="kpi-label">${t(e("workspace.phone"))}</label><input class="search" style="width:100%" id="mPhone" autocomplete="tel" value="${t(s.phone||"")}"></div>
      <div style="grid-column:1/-1"><label class="kpi-label">${t(e("workspace.email"))}</label><input class="search" style="width:100%" id="mEmail" autocomplete="email" value="${t(s.email||"")}"></div>
      <div><label class="kpi-label">${t(e("workspace.source"))}</label>
        <select class="control" style="width:100%" id="mSource">
          ${["manual","TikTok","WhatsApp","Facebook","import"].map(o=>`<option value="${o}" ${l===o?"selected":""}>${o}</option>`).join("")}
        </select>
      </div>
      <div><label class="kpi-label">${t(e("workspace.col_status"))}</label>
        <select class="control" style="width:100%" id="mStatus">${a||'<option value="new">NEW</option>'}</select>
      </div>
      <div><label class="kpi-label">${t(e("workspace.col_branch"))}</label>
        <select class="control" style="width:100%" id="mBranch">${n}</select>
      </div>
      <div><label class="kpi-label">${t(e("workspace.col_beautician"))}</label>
        <select class="control" style="width:100%" id="mBeautician">${i}</select>
      </div>
    </div>`}async function Xt(){const s=$e!=null,a=s?U(_.leadUpdateUrlTemplate,$e):_.leadStoreUrl;if(!a){w(e("workspace.save_error"));return}const n={name:(c("#mName")?.value||"").trim(),phone:(c("#mPhone")?.value||"").trim(),email:(c("#mEmail")?.value||"").trim()||null,source:c("#mSource")?.value||"manual",status:c("#mStatus")?.value||void 0,spa_branch_id:c("#mBranch")?.value?Number(c("#mBranch").value):null,beautician_id:c("#mBeautician")?.value?Number(c("#mBeautician").value):null};if(!n.name||!n.phone){w(e("workspace.save_error"));return}try{const i=await fetch(a,{method:s?"PUT":"POST",headers:D(!0),credentials:"same-origin",body:JSON.stringify(n)}),l=await i.json().catch(()=>({}));if(!i.ok){const o=l&&(l.message||Object.values(l.errors||{})[0]?.[0])||e(s?"workspace.update_error":"workspace.save_error");w(o);return}R(),$e=null,w(l.message||e(s?"workspace.updated":"workspace.saved")),s||(r.leadPage=1),await q()}catch(i){console.error(i),w(e(s?"workspace.update_error":"workspace.save_error"))}}function wt(s){_.canDeleteLead&&(se(e("workspace.delete"),e("workspace.delete_confirm"),"",`<button type="button" class="btn" data-action-drawer-close>${t(e("workspace.cancel"))}</button><button type="button" class="btn danger" id="confirmDeleteLead">${t(e("workspace.delete"))}</button>`,e("nav.leads")),c("#confirmDeleteLead").onclick=async a=>{const n=a.currentTarget;n.disabled=!0,await Qt(s),n.disabled=!1})}async function Qt(s){if(!_.canDeleteLead)return;const a=U(_.leadDestroyUrlTemplate,s);if(!a){w(e("workspace.delete_error"));return}try{const n=await fetch(a,{method:"DELETE",headers:D(!1),credentials:"same-origin"}),i=await n.json().catch(()=>({}));if(!n.ok){w(i.message||e("workspace.delete_error"));return}w(i.message||e("workspace.deleted")),R(),V(),await q()}catch(n){console.error(n),w(e("workspace.delete_error"))}}async function aa(s){let n=ie.find(h=>String(h.id)===String(s))||we.find(h=>String(h.id)===String(s));const i=_.leadShowUrlTemplate;if(i)try{const h=await fetch(U(i,s),{headers:D(!1),credentials:"same-origin"});if(h.ok){const g=await h.json();n=g.data||n,g.filters?.statuses&&(A.statuses=g.filters.statuses)}}catch(h){console.error(h)}if(!n)return;R(),V(),Me=document.activeElement,La=document.body.style.overflow,document.body.style.overflow="hidden";const l=c("#leadDrawer .eyebrow");l&&(l.textContent=e("workspace.drawer_eyebrow")),c("#drawerName").textContent=n.name||"";const o=Zt(n),d=(A.statuses||[]).map(h=>`<option value="${t(h.value)}" ${String(n.status_key)===String(h.value)?"selected":""}>${t(h.label)}</option>`).join(""),u=!!_.canEditLead,p=String(n.name||""),m=t((p[0]||"?").toUpperCase());c("#drawerBody").innerHTML=`
  <div class="journey">
    <div class="journey-hero">
      <div class="journey-hero__avatar" aria-hidden="true">${m}</div>
      <div class="journey-hero__meta">
        <div class="journey-hero__code">${t(n.code||n.id)}</div>
        <div class="journey-hero__badges">
          ${P(n.status)}
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
        ${o.signals.map(h=>`<span class="journey-signal journey-signal--${t(h.tone)}">${t(h.label)}</span>`).join("")||`<span class="journey-signal journey-signal--ok">${t(e("workspace.drawer_signal_healthy"))}</span>`}
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.drawer_pipeline"))}</div>
      <div class="journey-pipeline" role="list">${o.pipelineHtml}</div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.drawer_contact"))}</div>
      <div class="journey-kv">
        <div><span>${t(e("workspace.col_phone"))}</span><strong>${t(n.phone||"—")}</strong></div>
        <div><span>${t(e("workspace.col_email"))}</span><strong title="${t(n.email||"")}">${t(n.email||"—")}</strong></div>
        <div><span>${t(e("workspace.drawer_source_label"))}</span><strong>${t(n.source||"—")}</strong></div>
        <div><span>${t(e("workspace.col_customer"))}</span><strong>${t(n.customer||"—")}</strong></div>
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.drawer_assignment"))}</div>
      <div class="journey-kv">
        <div><span>${t(e("workspace.col_beautician"))}</span><strong>${t(n.beautician||"—")}</strong></div>
        <div><span>${t(e("workspace.col_branch"))}</span><strong>${t(n.branch||"—")}</strong></div>
        <div><span>${t(e("workspace.col_last_fu"))}</span><strong>${t(n.last||"—")}</strong></div>
        <div><span>${t(e("workspace.col_date"))}</span><strong>${t(n.date||"—")}</strong></div>
      </div>
    </section>

    ${u?`<section class="journey-section">
      <div class="journey-section__title">${t(e("workspace.update_status"))}</div>
      <div class="journey-status-row">
        <select class="control" id="drawerStatus">${d}</select>
        <button class="btn primary" type="button" id="drawerSaveStatus">${t(e("workspace.update_status"))}</button>
      </div>
    </section>`:""}

    <div class="journey-actions">
      <div class="journey-section__title">${t(e("workspace.drawer_actions"))}</div>
      <div class="journey-actions__row">
        ${u?`<button class="btn primary" type="button" id="drawerFollowBtn">${t(e("followup.mark"))}</button>`:""}
        ${u?`<button class="btn" type="button" id="drawerEditBtn">${t(e("workspace.edit"))}</button>`:""}
        ${_.canDeleteLead?`<button class="btn danger" type="button" id="drawerDeleteBtn">${t(e("workspace.delete"))}</button>`:""}
      </div>
    </div>
  </div>`,c("#leadDrawer").classList.add("show"),c("#drawerBackdrop").classList.add("show"),c("#leadDrawer").setAttribute("aria-hidden","false"),c("#leadDrawer").inert=!1,c(".app-shell").inert=!0,c("#drawerClose").focus({preventScroll:!0}),X(),c("#drawerFollowBtn")&&(c("#drawerFollowBtn").onclick=()=>xt(n.id)),c("#drawerEditBtn")&&(c("#drawerEditBtn").onclick=()=>{V(),ft(n.id)}),c("#drawerDeleteBtn")&&(c("#drawerDeleteBtn").onclick=()=>wt(n.id)),c("#drawerSaveStatus")&&(c("#drawerSaveStatus").onclick=async()=>{const h=c("#drawerStatus")?.value,g=U(_.leadStatusUrlTemplate,n.id);if(!h||!g){w(e("workspace.status_error"));return}try{const f=await fetch(g,{method:"PATCH",headers:D(!0),credentials:"same-origin",body:JSON.stringify({status:h})}),b=await f.json().catch(()=>({}));if(!f.ok){w(b.message||e("workspace.status_error"));return}w(b.message||e("workspace.status_updated")),r.view==="followup"?await ae():await q(),aa(n.id)}catch(f){console.error(f),w(e("workspace.status_error"))}})}function Zt(s){const a=["new","claimed","follow_up","booking","payment_verified","converted"],n=String(s.status_key||"new"),i=a.indexOf(n),l=n==="lost"||n==="no_response"?Math.max(10,Math.round((Math.max(i,0)+1)/a.length*100)):Math.round((Math.max(i,0)+1)/a.length*100),o=Number(s.days_in_pipeline!=null?s.days_in_pipeline:s.days_since_followup||0),d=!s.last_followed_up_at,u=Number(s.days_since_followup||0),p=String(s.followup_bucket)==="overdue"||(d?o>=2:u>=2);let m=28;n==="converted"?m=96:n==="payment_verified"?m=82:n==="booking"?m=68:n==="follow_up"?m=54:n==="claimed"?m=42:n==="new"?m=32:n==="no_response"?m=22:n==="lost"&&(m=12),s.existing&&(m+=8),s.duplicate&&(m-=6),p&&(m-=18),!d&&u===0&&(m+=6),s.beautician_id&&(m+=4),s.branch_id&&(m+=3),m=Math.max(5,Math.min(99,m));let h="warm",g=e("workspace.drawer_health_warm");m>=75?(h="hot",g=e("workspace.drawer_health_hot")):m<35||p||n==="lost"||n==="no_response"?(h="risk",g=e("workspace.drawer_health_risk")):m<50&&(h="cold",g=e("workspace.drawer_health_cold"));let f="med",b=e("workspace.drawer_priority_medium");p||n==="no_response"||!s.beautician_id&&o>=1?(f="high",b=e("workspace.drawer_priority_high")):(n==="converted"||n==="payment_verified")&&(f="low",b=e("workspace.drawer_priority_low"));const $=[];p&&$.push({tone:"danger",label:e("workspace.drawer_signal_overdue")}),o<=1&&n==="new"&&$.push({tone:"info",label:e("workspace.drawer_signal_fresh")}),s.existing&&$.push({tone:"ok",label:e("workspace.drawer_signal_existing")}),s.duplicate&&$.push({tone:"warn",label:e("workspace.drawer_signal_duplicate")}),!s.beautician_id&&n!=="converted"&&$.push({tone:"warn",label:e("workspace.drawer_signal_unassigned")}),!s.branch_id&&n!=="converted"&&$.push({tone:"warn",label:e("workspace.drawer_signal_no_branch")}),$.length||$.push({tone:"ok",label:e("workspace.drawer_signal_healthy")});const S=a.map((T,y)=>{const k=(A.statuses||[]).find(I=>I.value===T)?.label||T.replace(/_/g," ");return`<div class="journey-step${i>y||n==="converted"?" is-done":i===y?" is-active":""}" role="listitem"><span class="journey-step__dot"></span><span class="journey-step__label">${t(k)}</span></div>`}).join("");return{score:m,stagePct:l,daysInPipeline:o,daysSinceContact:u,neverContacted:d,healthTone:h,healthLabel:g,priorityTone:f,priorityLabel:b,signals:$,pipelineHtml:S}}let N=null,la=[],ra={total_imports:0,raw:0,unique:0,duplicates:0,existing:0,invalid:0,imported:0},be=!1;function es(){B.innerHTML=`${de(e("import.title"),e("import.subtitle"),`<button type="button" class="btn" data-jump="imports">${t(e("import.history_btn"))}</button><button type="button" class="btn primary" data-jump="leads">${t(e("import.view_leads"))}</button>`)}
  <section class="card">
    <div class="tabs" id="importTabs" role="tablist">${[["paste",e("import.tab_paste")],["excel",e("import.tab_excel")],["csv",e("import.tab_csv")],["manual",e("import.tab_manual")]].map(s=>`<button type="button" class="tab ${r.importTab===s[0]?"active":""}" role="tab" aria-selected="${r.importTab===s[0]?"true":"false"}" data-tab="${s[0]}">${t(s[1])}</button>`).join("")}</div>
    <div id="importPane" style="margin-top:14px"></div>
  </section>
  <section class="card hidden" id="previewCard" style="margin-top:12px"></section>`,kt(),X(),as()}function as(){const s=M("[data-tab]","#importTabs");s.forEach(a=>{a.onclick=n=>{n.preventDefault();const i=a.dataset.tab;if(!i||i===r.importTab)return;r.importTab=i,s.forEach(o=>{const d=o===a;o.classList.toggle("active",d),o.setAttribute("aria-selected",d?"true":"false")}),N=null;const l=c("#previewCard");l&&l.classList.add("hidden"),kt()}})}function kt(){const s=c("#importPane");if(!s)return;const a=!!_.canCreateLead;if(r.importTab==="paste")s.innerHTML=`<div><div class="card-title">${t(e("import.paste_title"))}</div><div class="card-subtitle">${t(e("import.paste_sub"))}</div><textarea class="paste-area" id="pasteArea" placeholder="${t(e("import.paste_placeholder"))}"></textarea><div style="display:flex;justify-content:flex-end;margin-top:10px"><button type="button" class="btn primary" id="parseBtn" ${a?"":"disabled"}>${t(e("import.parse"))}</button></div></div>`,c("#parseBtn")&&(c("#parseBtn").onclick=()=>ts());else if(r.importTab==="manual")s.innerHTML=`<div class="detail-grid"><div><label class="kpi-label" for="manualName">${t(e("import.name"))}</label><input class="search" style="width:100%" id="manualName" autocomplete="name"></div><div><label class="kpi-label" for="manualPhone">${t(e("import.phone"))}</label><input class="search" style="width:100%" id="manualPhone" inputmode="tel" autocomplete="tel"></div><div><label class="kpi-label" for="manualEmail">${t(e("import.email"))}</label><input class="search" style="width:100%" id="manualEmail" type="email" autocomplete="email"></div><div><label class="kpi-label" for="manualSource">${t(e("import.source"))}</label><select class="control" style="width:100%" id="manualSource"><option value="TikTok">TikTok</option><option value="WhatsApp">WhatsApp</option><option value="Facebook">Facebook</option><option value="manual">Import</option></select></div><div style="grid-column:1/-1;text-align:right"><button type="button" class="btn primary" id="manualSaveBtn" ${a?"":"disabled"}>${t(e("import.save_lead"))}</button></div></div>`,c("#manualSaveBtn")&&(c("#manualSaveBtn").onclick=ss);else{const n=r.importTab==="excel";s.innerHTML=`<div class="import-zone" id="importDropZone" tabindex="0"><div class="import-icon">⇧</div><h3>${t(e(n?"import.drop_excel":"import.drop_csv"))}</h3><p>${t(e(n?"import.accepted_excel":"import.accepted_csv"))}</p><input type="file" id="fileInput" class="hidden" accept="${n?".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel":".csv,text/csv,text/plain"}"><button type="button" class="btn primary" id="browseBtn" ${a?"":"disabled"}>${t(e("import.browse"))}</button></div>`;const i=c("#fileInput"),l=c("#browseBtn"),o=c("#importDropZone");l&&i&&(l.onclick=d=>{d.preventDefault(),d.stopPropagation(),i.click()}),i&&(i.onchange=()=>Wa(i.files?.[0])),o&&a&&(o.addEventListener("click",d=>{d.target===l||l?.contains(d.target)||i?.click()}),["dragenter","dragover"].forEach(d=>o.addEventListener(d,u=>{u.preventDefault(),u.stopPropagation(),o.classList.add("is-dragover")})),["dragleave","drop"].forEach(d=>o.addEventListener(d,u=>{u.preventDefault(),u.stopPropagation(),o.classList.remove("is-dragover")})),o.addEventListener("drop",d=>{const u=d.dataTransfer?.files?.[0];u&&Wa(u)}),o.addEventListener("keydown",d=>{(d.key==="Enter"||d.key===" ")&&(d.preventDefault(),i?.click())}))}}async function ts(){const s=c("#pasteArea")?.value||"";if(!s.trim()){w(e("import.paste_required"));return}await Ta({method:"paste",paste:s})}async function Wa(s){if(!s){w(e("import.file_required"));return}const a=new FormData;a.append("method",r.importTab==="excel"?"excel":"csv"),a.append("file",s),r.branch&&r.branch!=="all"&&a.append("spa_branch_id",r.branch),await Ta(a,!0)}async function ss(){const s=(c("#manualName")?.value||"").trim(),a=(c("#manualPhone")?.value||"").trim(),n=(c("#manualEmail")?.value||"").trim(),i=(c("#manualSource")?.value||"manual").trim();if(!s||!a){w(e("import.rows_required"));return}await Ta({method:"manual",rows:[{name:s,phone:a,email:n||null,source:i}]})}async function Ta(s,a=!1){const n=_.importPreviewUrl;if(!n){w(e("import.preview_error"));return}if(!be){be=!0,w(e("import.parsing"));try{const i={method:"POST",credentials:"same-origin",headers:D(!a)};a?i.body=s:(r.branch&&r.branch!=="all"&&!s.spa_branch_id&&(s.spa_branch_id=Number(r.branch)||null),i.body=JSON.stringify(s));const l=await fetch(n,i),o=await l.json().catch(()=>({}));if(!l.ok){const d=o.message||Object.values(o.errors||{}).flat()[0]||e("import.preview_error");w(d);return}N=o.data||null,ns()}catch(i){console.error(i),w(e("import.preview_error"))}finally{be=!1}}}function ns(){const s=c("#previewCard");if(!s||!N)return;const a=N.rows||[],n=N.summary||{},i=Number(n.ready||0)+Number(n.existing||0);s.classList.remove("hidden"),s.innerHTML=`<div class="card-title-row"><div><div class="card-title">${t(e("import.preview_title"))}</div><div class="card-subtitle">${t(e("import.preview_sub"))}</div></div><button class="btn small" type="button" id="closePreviewBtn">${t(e("import.close"))}</button></div>
  <div class="summary-strip">
    <div class="summary-chip"><label>${t(e("import.total_rows"))}</label><strong>${v(n.total||0)}</strong></div>
    <div class="summary-chip"><label>${t(e("import.ready"))}</label><strong>${v(n.ready||0)}</strong></div>
    <div class="summary-chip"><label>${t(e("import.duplicate"))}</label><strong>${v(n.duplicate||0)}</strong></div>
    <div class="summary-chip"><label>${t(e("import.existing"))}</label><strong>${v(n.existing||0)}</strong></div>
    <div class="summary-chip"><label>${t(e("import.invalid"))}</label><strong>${v(n.invalid||0)}</strong></div>
  </div>
  <div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("import.col_row"))}</th><th>${t(e("import.col_name"))}</th>
    <th title="${t(e("import.col_orig_phone"))}">${t(e("import.col_orig_phone"))}</th>
    <th title="${t(e("import.col_norm_phone"))}">${t(e("import.col_norm_phone"))}</th>
    <th>${t(e("import.col_email"))}</th><th>${t(e("import.col_detection"))}</th><th>${t(e("import.col_action"))}</th>
  </tr></thead><tbody>
  ${a.map(l=>`<tr><td>${l.row}</td><td><strong>${t(l.name||"")}</strong></td><td>${t(l.phone_orig||"")}</td><td>${t(l.phone_e164||l.phone_norm||"")}</td><td>${t(l.email||"")}</td><td>${P(l.detection)}</td><td>${l.detection==="READY"||l.detection==="EXISTING"?`<span class="badge success">${t(e("import.action_import"))}</span>`:`<span class="badge gray">${t(e("import.action_skip"))}</span>`}</td></tr>`).join("")||`<tr><td colspan="7"><div class="empty">${t(e("import.empty"))}</div></td></tr>`}
  </tbody></table></div>
  <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px"><button class="btn" type="button" id="cancelImportBtn">${t(e("import.cancel"))}</button><button class="btn primary" type="button" id="confirmImport" ${i>0?"":"disabled"}>${t(e("import.confirm",{count:i}))}</button></div>`,c("#closePreviewBtn")?.addEventListener("click",()=>s.classList.add("hidden")),c("#cancelImportBtn")?.addEventListener("click",()=>s.classList.add("hidden")),c("#confirmImport")?.addEventListener("click",is),s.scrollIntoView({behavior:"smooth",block:"start"})}async function is(){if(!N||be)return;const s=_.importConfirmUrl;if(!s){w(e("import.confirm_error"));return}const a=(N.rows||[]).map(n=>({name:n.name,phone:n.phone_norm||n.phone_orig,email:n.email||null,source:n.source||N.source||"import",import:n.detection==="READY"||n.detection==="EXISTING"}));be=!0,w(e("import.importing"));try{const n=await fetch(s,{method:"POST",credentials:"same-origin",headers:D(!0),body:JSON.stringify({method:N.method||"paste",rows:a,source:N.source||"import",file_name:N.file_name||null,spa_branch_id:N.spa_branch_id||null,beautician_id:N.beautician_id||null})}),i=await n.json().catch(()=>({}));if(!n.ok){w(i.message||e("import.confirm_error"));return}w(i.message||e("import.imported",{count:i.data&&i.data.imported||0})),N=null,setTimeout(()=>F("leads"),700)}catch(n){console.error(n),w(e("import.confirm_error"))}finally{be=!1}}async function ls(){B.innerHTML=`${de(e("import.history_title"),e("import.history_subtitle"),`<button class="btn primary" data-jump="import">${t(e("import.new_import"))}</button>`)}
  <div class="grid kpi-grid" id="importKpiMount"></div>
  <section class="card" style="margin-top:12px"><div id="importHistoryMount"><div class="empty">${t(e("workspace.loading"))}</div></div></section>`,X(),await St()}async function St(){const s=_.importHistoryUrl,a=c("#importHistoryMount"),n=c("#importKpiMount");if(!s){a&&(a.innerHTML=`<div class="empty">${t(e("import.load_error"))}</div>`);return}try{const i=await fetch(s+"?per_page=50&page="+Na,{headers:D(!1),credentials:"same-origin"}),l=await i.json().catch(()=>({}));if(!i.ok){w(l.message||e("import.load_error"));return}la=l.data||[],ra=l.meta?.summary||ra;const o=ra;if(n&&(n.innerHTML=`${x("▤",e("import.kpi_total"),v(o.total_imports),e("common.this_month"),e("import.meta_batches"),"blue")}${x("♙",e("import.kpi_raw"),v(o.raw),e("import.meta_historical"),"","rose")}${x("✓",e("import.kpi_unique"),v(o.unique),e("import.meta_after_clean"),"","green")}${x("⧉",e("import.kpi_duplicates"),v(o.duplicates),e("import.meta_auditable"),"","purple")}${x("♧",e("import.kpi_existing"),v(o.existing),e("import.meta_phone"),"","blue")}`),!a)return;if(!la.length){a.innerHTML=`<div class="empty"><strong>${t(e("import.empty"))}</strong>${t(e("import.empty_hint"))}</div>`;return}a.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
      <th>${t(e("import.col_batch"))}</th><th>${t(e("import.col_date"))}</th>
      <th title="${t(e("import.col_by"))}">${t(e("import.col_by"))}</th>
      <th>${t(e("import.col_method"))}</th><th>${t(e("import.col_file"))}</th>
      <th>${t(e("import.col_raw"))}</th><th>${t(e("import.col_unique"))}</th>
      <th>${t(e("import.col_duplicate"))}</th><th>${t(e("import.col_existing"))}</th>
      <th>${t(e("import.col_invalid"))}</th><th>${t(e("import.col_status"))}</th>
    </tr></thead><tbody>
    ${la.map(d=>`<tr>
      <td><strong>${t(d.batch_code||"")}</strong></td><td>${t(d.date||"")}</td>
      <td>${t(d.by||"")}</td><td>${t(d.method||"")}</td><td>${t(d.file||"")}</td>
      <td>${v(d.raw)}</td><td>${v(d.unique)}</td><td>${v(d.duplicate)}</td>
      <td>${v(d.existing)}</td><td>${v(d.invalid)}</td><td>${P(d.status)}</td>
    </tr>`).join("")}
    </tbody></table></div>${G("imports",l.meta||{})}`,J("imports",d=>(Na=d,St()))}catch(i){console.error(i),a&&(a.innerHTML=`<div class="empty">${t(e("import.load_error"))}</div>`)}}function Mt(){const s=r.paymentCustomerId?`<div class="pay-customer-chip" id="payCustomerChip">
        <span>${t(e("payments.filtered_customer",{name:r.paymentCustomerLabel||"#"+r.paymentCustomerId}))}</span>
        <button type="button" class="btn small soft" id="payClearCustomer">${t(e("payments.clear_customer"))}</button>
      </div>`:"";B.innerHTML=`<div class="pay-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${t(e("payments.title"))}</h1>
        <div class="page-subtitle">${t(e("payments.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="payRefresh">${t(e("payments.refresh"))}</button>
        ${_.canViewOrder&&_.ordersIndexUrl?`<a class="btn primary" href="${t(_.ordersIndexUrl)}" target="_blank" rel="noopener">${t(e("payments.open_orders"))}</a>`:""}
      </div>
    </div>
    ${s}
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
  </div>`;const a=c("#payRefresh");a&&(a.onclick=()=>z());const n=c("#payClearCustomer");n&&(n.onclick=()=>rs()),cs(),z()}function rs(){r.paymentCustomerId=null,r.paymentCustomerLabel="",r.paymentSearch="",r.paymentPage=1,r.view==="payments"?Mt():z()}function os(s){if(!s)return;const a=Number(s.id||0);a&&(r.paymentCustomerId=a,r.paymentCustomerLabel=String(s.name||s.code||"#"+a),r.paymentSearch=String(s.phone||s.email||s.name||"").trim(),r.paymentTab="all",r.paymentPage=1,r.paymentBeautician="all",F("payments"))}function cs(){const s=c("#paySearch");s&&(s.oninput=()=>{clearTimeout(Ba),Ba=setTimeout(()=>{r.paymentSearch=s.value.trim(),r.paymentPage=1,z()},320)});const a=c("#payBeautician");a&&(a.onchange=()=>{r.paymentBeautician=a.value,r.paymentPage=1,z()});const n=c("#payBranch");n&&(n.onchange=()=>{r.paymentBranch=n.value,r.paymentPage=1,z()})}async function z(){const s=_.paymentsUrl||"",a=c("#payTableMount");if(!s){a&&(a.innerHTML=`<div class="pay-empty"><strong>${t(e("payments.load_error"))}</strong></div>`);return}Ke=!0,Va(),Ya();const n=new URLSearchParams;r.paymentCustomerId?n.set("customer_id",String(r.paymentCustomerId)):r.paymentSearch&&n.set("q",r.paymentSearch),r.paymentTab&&r.paymentTab!=="all"&&n.set("status",r.paymentTab);const i=r.paymentBranch!=="all"?r.paymentBranch:r.branch||"all";i&&i!=="all"&&n.set("branch",i),r.paymentBeautician&&r.paymentBeautician!=="all"&&n.set("beautician",r.paymentBeautician),r.period&&n.set("period",r.period),n.set("page",String(r.paymentPage||1)),n.set("per_page","25");try{const l=await fetch(`${s}?${n.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("payments "+l.status);const o=await l.json();me=Array.isArray(o.data)?o.data:[],ba=Object.assign({pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0},o.meta&&o.meta.summary||{}),ve=Object.assign({statuses:[],beauticians:[],branches:[]},o.filters||{}),pa={current_page:o.meta&&o.meta.current_page||1,last_page:o.meta&&o.meta.last_page||1,total:o.meta&&o.meta.total||0}}catch(l){console.error(l),me=[],w(e("payments.load_error"))}finally{Ke=!1,Va(),ps(),us(),Ya()}}function ds(s){const a=String(s||"pending");return a==="paid"?{customer:"done",accountant:"done",hq:"done",active:null}:a==="processing"?{customer:"done",accountant:"done",hq:"active",active:"hq"}:a==="canceled"||a==="refunded"?{customer:"done",accountant:"active",hq:null,active:"accountant"}:{customer:"done",accountant:"active",hq:null,active:"accountant"}}function Tt(s){const a=ds(s),n=l=>{const o=a[l];return`<span class="pay-pipe__node ${o==="done"?"is-done":o==="active"?"is-active":""}" title="${t(e("payments.flow_"+(l==="hq"?"hq":l)))}"></span>`},i=l=>`<span class="pay-pipe__line ${a[l]==="done"?"is-done":""}"></span>`;return`<div class="pay-pipe" title="${t(e("payments.pipeline"))}">${n("customer")}${i("customer")}${n("accountant")}${i("accountant")}${n("hq")}</div>`}function Va(){const s=ba,a=c("#payHeroCopy");a&&(a.textContent=s.queue>0?e("payments.pulse_busy",{count:v(s.queue),amount:H(s.pending_amount)}):e("payments.pulse_clear"));const n=c("#payHeroStats");n&&(n.innerHTML=`
      <div class="pay-metric"><span>${t(e("payments.stat_queue"))}</span><strong>${v(s.queue)}</strong></div>
      <div class="pay-metric"><span>${t(e("payments.stat_paid"))}</span><strong>${H(s.paid_amount)}</strong></div>
      <div class="pay-metric"><span>${t(e("payments.stat_today"))}</span><strong>${v(s.paid_today)}</strong></div>
      <div class="pay-metric"><span>${t(e("payments.stat_hold"))}</span><strong>${v(s.hold)}</strong></div>
    `)}function ps(){const s=c("#payTabs");if(!s)return;const a=ba,n={all:(a.pending||0)+(a.processing||0)+(a.paid||0)+(a.hold||0)+(a.refunded||0),queue:a.queue||0,pending:a.pending||0,processing:a.processing||0,paid:a.paid||0,canceled:a.hold||0,refunded:a.refunded||0},l=[...ve.statuses&&ve.statuses.length?ve.statuses:[{value:"queue",label:e("payments.tab_queue")},{value:"all",label:e("payments.tab_all")},{value:"pending",label:e("payments.tab_pending")},{value:"processing",label:e("payments.tab_processing")},{value:"paid",label:e("payments.tab_paid")},{value:"canceled",label:e("payments.tab_hold")},{value:"refunded",label:e("payments.tab_refunded")}]].sort((o,d)=>(o.value==="queue"?-1:0)-(d.value==="queue"?-1:0));s.innerHTML=l.map(o=>{const d=o.value,u=r.paymentTab===d?"active":"",p=n[d],m=p!==void 0?`<span class="pay-tab-count">${v(p)}</span>`:"";return`<button type="button" class="tab ${u}" role="tab" data-ptab="${t(d)}">${t(o.label)}${m}</button>`}).join(""),M("[data-ptab]",s).forEach(o=>{o.onclick=()=>{r.paymentTab=o.dataset.ptab,r.paymentPage=1,z()}})}function us(){const s=c("#payBeautician");if(s){const n=r.paymentBeautician;s.innerHTML=`<option value="all">${t(e("payments.all_beauticians"))}</option>`+(ve.beauticians||[]).map(i=>`<option value="${i.id}" ${String(n)===String(i.id)?"selected":""}>${t(i.name)}</option>`).join("")}const a=c("#payBranch");if(a){const n=r.paymentBranch;a.innerHTML=`<option value="all">${t(e("payments.all_branches"))}</option>`+(ve.branches||[]).map(i=>`<option value="${i.id}" ${String(n)===String(i.id)?"selected":""}>${t(i.code?i.code+" · "+i.name:i.name)}</option>`).join("")}}function Ya(){const s=c("#payTableMount"),a=c("#payResultCount");if(a&&(a.textContent=e("payments.result_count",{count:v(pa.total||me.length)})),!s)return;if(Ke){s.innerHTML=`<div class="pay-empty"><strong>${t(e("payments.loading"))}</strong></div>`;return}if(!me.length){s.innerHTML=`<div class="pay-empty"><strong>${t(e("payments.empty"))}</strong><span>${t(e("payments.empty_hint"))}</span></div>`;return}const n=me.map(l=>{const o=l.ref&&l.ref!=="—",d=[l.has_proof?`<span class="pay-chip pay-chip--ok">${t(e("payments.chip_proof"))}</span>`:`<span class="pay-chip pay-chip--muted">${t(e("payments.stage_declared"))}</span>`,o?`<span class="pay-chip pay-chip--ok">${t(e("payments.chip_ref"))}</span>`:`<span class="pay-chip pay-chip--warn">${t(e("payments.chip_no_ref"))}</span>`].join("");return`<tr>
      <td><span class="pay-id">${t(l.code||"ORD-"+l.id)}</span></td>
      <td><div class="person-cell"><div class="mini-avatar">${t(l.initial||"?")}</div><div><strong>${t(l.customer||"")}</strong><small>${t(l.phone||"")}</small></div></div></td>
      <td>${t(l.branch||"—")}</td>
      <td>${t(l.beautician||"—")}</td>
      <td><div class="pay-amount">${H(l.amount)}</div><div class="pay-method">${t(l.payment_method_label||"")}</div></td>
      <td><div class="pay-chips">${d}</div><div class="pay-method" title="${t(e("payments.col_ref"))}">${t(l.ref||"—")}</div></td>
      <td>${Tt(l.payment_status)}</td>
      <td>${P(l.payment_status_label||l.payment_status)}</td>
      <td><button type="button" class="pay-review-btn" data-payment="${l.id}">${t(e("payments.review"))}</button></td>
    </tr>`}).join(""),i=G("pay",pa);s.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("payments.col_id"))}</th>
    <th>${t(e("payments.col_customer"))}</th>
    <th>${t(e("payments.col_branch"))}</th>
    <th>${t(e("payments.col_beautician"))}</th>
    <th>${t(e("payments.col_amount"))}</th>
    <th>${t(e("payments.col_ref"))}</th>
    <th>${t(e("payments.pipeline"))}</th>
    <th>${t(e("payments.col_status"))}</th>
    <th>${t(e("payments.col_action"))}</th>
  </tr></thead><tbody>${n}</tbody></table></div>${i}`,M("[data-payment]",s).forEach(l=>l.onclick=()=>vs(Number(l.dataset.payment))),J("pay",l=>(r.paymentPage=l,z()))}function ms(s){const a=s.proof;let n;if(!_.canViewOrder)n=`<p class="payment-proof__empty">${t(e("payments.proof_access"))}</p>`;else if(!a?.url)n=`<p class="payment-proof__empty">${t(e(s.has_proof?"payments.proof_unavailable":"payments.proof_missing"))}</p>`;else{const i=t(a.url),l=t(a.name||e("payments.proof_title")),o=`<a class="btn small" href="${i}" target="_blank" rel="noopener noreferrer">${t(e("payments.proof_open"))}</a>`;n=`${a.kind==="image"?`<a class="payment-proof__image" href="${i}" target="_blank" rel="noopener noreferrer"><img id="paymentProofImage" src="${i}" alt="${l}" loading="lazy"></a><p class="payment-proof__empty" id="paymentProofError" hidden>${t(e("payments.proof_unavailable"))}</p>`:a.kind==="pdf"?`<object class="payment-proof__pdf" data="${i}" type="application/pdf" aria-label="${l}"><p class="payment-proof__empty">${t(e("payments.proof_pdf_hint"))}</p></object>`:`<p class="payment-proof__empty">${t(e("payments.proof_pdf_hint"))}</p>`}<div class="payment-proof__file"><span>${l}</span>${o}</div>`}return`<section class="payment-proof"><h3>${t(e("payments.proof_title"))}</h3>${n}</section>`}function vs(s){const a=me.find(m=>Number(m.id)===Number(s));if(!a)return;const n=U(_.orderShowUrlTemplate,a.id),i=!!_.canEditOrder,l=!!_.canViewOrder,o=["identity","invoice","method","ref","proof","status"].map(m=>{const h=a.checklist?.[m],g=["completed","not_applicable"].includes(h)?h:"pending";return`<div class="pay-review__check is-${g}" data-check="${m}"><span class="pay-review__check-icon" aria-hidden="true">${g==="completed"?"✓":g==="not_applicable"?"—":"○"}</span><span>${t(e("payments.check_"+m))}</span><small>${t(e("payments.check_"+g))}</small></div>`}).join(""),d=`<div class="pay-review">
    <div class="pay-review__hero">
      <div class="pay-review__avatar">${t(a.initial||"?")}</div>
      <div style="min-width:0;flex:1">
        <div class="pay-id">${t(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${t(a.customer||"")}</strong>
        <div class="pay-method">${t(a.phone||"")} · ${t(a.branch_name||a.branch||"")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px;align-items:center">${P(a.payment_status_label||a.payment_status)}${Tt(a.payment_status)}</div>
      </div>
      <div style="text-align:right"><div class="pay-amount">${H(a.amount)}</div><div class="pay-method">${t(a.payment_method_label||"")}</div></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${t(e("payments.col_customer_stage"))}</label><strong>${t(a.customer_stage)}</strong></div>
      <div class="pay-review__card"><label>${t(e("payments.col_accountant"))}</label><strong>${t(a.accountant_stage)}</strong></div>
      <div class="pay-review__card"><label>${t(e("payments.col_hq"))}</label><strong>${t(a.hq_stage)}</strong></div>
    </div>
    ${ms(a)}
    ${i?`<div class="detail-grid">
      <div class="detail-box"><label>${t(e("payments.bank_ref"))}</label><input class="control" id="payRefInput" value="${t(a.ref==="—"?"":a.ref)}" placeholder="${t(e("payments.bank_ref_ph"))}" /></div>
      <div class="detail-box" style="grid-column:span 2"><label>${t(e("payments.admin_note"))}</label><input class="control" id="payNoteInput" value="${t(a.admin_note||"")}" /></div>
    </div>`:""}
    <div class="journey-section"><div class="journey-section__title">${t(e("payments.checklist"))}</div><div class="pay-review__checks">${o}</div><p class="pay-review__check-note">${t(e("payments.check_note"))}</p></div>
    ${l?"":`<p class="card-subtitle">${t(e("payments.no_order_access"))}</p>`}
  </div>`,u=[l?`<a class="btn" href="${t(n)}" target="_blank" rel="noopener">${t(e("payments.open_order"))}</a>`:"",i?`<button type="button" class="btn" id="payMarkProcessing">${t(e("payments.mark_processing"))}</button>`:"",i?`<button type="button" class="btn danger" id="payMarkHold">${t(e("payments.mark_hold"))}</button>`:"",i?`<button type="button" class="btn success" id="payMarkPaid">${t(e("payments.mark_paid"))}</button>`:"",`<button type="button" class="btn" data-action-drawer-close>${t(e("payments.close"))}</button>`].filter(Boolean).join("");se(e("payments.review_title"),e("payments.review_sub",{code:a.code,customer:a.customer}),d,u,"Pay Verify");const p=c("#paymentProofImage");if(p){const m=()=>{p.closest("a").hidden=!0,c("#paymentProofError").hidden=!1};p.onerror=m,p.complete&&!p.naturalWidth&&m()}setTimeout(()=>{const m=c("#payMarkProcessing");m&&(m.onclick=()=>oa(a,"processing"));const h=c("#payMarkHold");h&&(h.onclick=()=>oa(a,"canceled"));const g=c("#payMarkPaid");g&&(g.onclick=()=>oa(a,"paid"))},0)}async function oa(s,a){const n=U(_.orderPaymentStatusUrlTemplate,s.id);if(!n||!_.canEditOrder){w(e("payments.update_error"));return}const i=c("#payRefInput"),l=c("#payNoteInput"),o=i?i.value.trim():"",d=l?l.value.trim():"";if(s.needs_reference&&(a==="paid"||a==="processing")&&!o&&(s.ref==="—"||!s.ref)){w(e("payments.ref_required"));return}try{const u=await fetch(n,{method:"PUT",headers:D(!0),credentials:"same-origin",body:JSON.stringify({payment_status:a,transaction_id:o||void 0,admin_note:d||void 0})}),p=await u.json().catch(()=>({}));if(!u.ok){w(p.message||e("payments.update_error"));return}R(),w(p.message||e("payments.updated")),await z()}catch(u){console.error(u),w(e("payments.update_error"))}}function hs(){B.innerHTML=`<div class="cin-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${t(e("checkin.title"))}</h1>
        <div class="page-subtitle">${t(e("checkin.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="cinRefresh">${t(e("checkin.refresh"))}</button>
        ${_.canConfirmCheckin?`<button type="button" class="btn primary" id="cinScan">${t(e("checkin.scan"))}</button>`:""}
        ${_.canViewTreatments&&_.treatmentReservationsUrl?`<a class="btn primary" href="${t(_.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("checkin.open_crm"))}</a>`:""}
      </div>
    </div>
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
            <input class="lead-field__control" id="cinDate" type="date" value="${t(r.checkinDate||"")}" />
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
  </div>`;const s=c("#cinRefresh");s&&(s.onclick=()=>O());const a=c("#cinScan");a&&(a.onclick=fs),_s(),O()}function _s(){const s=c("#cinSearch");s&&(s.oninput=()=>{clearTimeout(qa),qa=setTimeout(()=>{r.checkinSearch=s.value.trim(),r.checkinPage=1,O()},320)});const a=c("#cinScope");a&&(a.onchange=()=>{r.checkinScope=a.value,a.value==="pipeline"&&(r.checkinStatus="live"),r.checkinPage=1,O()});const n=c("#cinDate");n&&(n.onchange=()=>{r.checkinDate=n.value,r.checkinScope="day",c("#cinScope").value="day",r.checkinPage=1,O()});const i=c("#cinBeautician");i&&(i.onchange=()=>{r.checkinBeautician=i.value,r.checkinPage=1,O()});const l=c("#cinBranch");l&&(l.onchange=()=>{r.checkinBranch=l.value,r.checkinPage=1,O()})}async function O(){const s=++Ue;Ee=!1;const a=_.checkinUrl||"",n=c("#cinTableMount");if(!a){n&&(n.innerHTML=`<div class="pay-empty"><strong>${t(e("checkin.load_error"))}</strong></div>`);return}re=!0,Ka(),za();const i=new URLSearchParams;r.checkinSearch&&i.set("q",r.checkinSearch),i.set("status",r.checkinStatus||"live"),i.set("scope",r.checkinScope||"day"),r.checkinDate&&i.set("date",r.checkinDate);const l=r.checkinBranch!=="all"?r.checkinBranch:r.branch||"all";l&&l!=="all"&&i.set("branch",l),r.checkinBeautician&&r.checkinBeautician!=="all"&&i.set("beautician",r.checkinBeautician),i.set("page",String(r.checkinPage||1)),i.set("per_page","25");try{const o=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!o.ok)throw new Error("checkin "+o.status);const d=await o.json();if(s!==Ue)return;Be=Array.isArray(d.data)?d.data:[],$a=Object.assign({live:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0},d.meta&&d.meta.summary||{}),ua=Object.assign({statuses:[],beauticians:[],branches:[]},d.filters||{}),wa={current_page:d.meta&&d.meta.current_page||1,last_page:d.meta&&d.meta.last_page||1,total:d.meta&&d.meta.total||0}}catch(o){if(s!==Ue)return;Ee=!0,console.error(o),Be=[],w(e("checkin.load_error"))}finally{if(s!==Ue)return;re=!1,Ka(),bs(),gs(),za()}}function Ka(){const s=c("#cinMetrics");if(!s)return;const a=$a;s.setAttribute("aria-busy",String(re));const n=i=>re||Ee?"—":v(i);s.innerHTML=`
    <div class="pay-metric"><span>${t(e("checkin.stat_live"))}</span><strong>${n(a.live)}</strong></div>
    <div class="pay-metric"><span>${t(e("checkin.stat_waiting"))}</span><strong>${n(a.waiting)}</strong></div>
    <div class="pay-metric"><span>${t(e("checkin.stat_treatment"))}</span><strong>${n(a.in_treatment)}</strong></div>
    <div class="pay-metric"><span>${t(e("checkin.stat_completed"))}</span><strong>${n(a.completed)}</strong></div>`}function bs(){const s=c("#cinTabs");if(!s)return;const a=$a,n=[["live",e("checkin.tab_live"),a.live],["waiting",e("checkin.tab_waiting"),a.waiting],["in_progress",e("checkin.tab_treatment"),a.in_treatment],["completed",e("checkin.tab_completed"),a.completed],["all",e("checkin.tab_all"),null]];s.innerHTML=n.filter(([i])=>r.checkinScope!=="pipeline"||!["completed","all"].includes(i)).map(([i,l,o])=>{const d=r.checkinStatus===i?"active":"",u=o==null?"":` (${v(o)})`;return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-cintab="${i}">${t(l)}${u}</button>`}).join(""),M("[data-cintab]",s).forEach(i=>i.onclick=()=>{r.checkinStatus=i.dataset.cintab,["completed","all"].includes(r.checkinStatus)&&(r.checkinScope="day",c("#cinScope").value="day"),r.checkinPage=1,O()})}function gs(){const s=c("#cinBeautician");if(s){const i=r.checkinBeautician||"all";s.innerHTML=`<option value="all">${t(e("checkin.all_beauticians"))}</option>`+(ua.beauticians||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const a=c("#cinBranch");if(a){const i=r.checkinBranch||"all";a.innerHTML=`<option value="all">${t(e("checkin.all_branches"))}</option>`+(ua.branches||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const n=c("#cinResultCount");n&&(n.textContent=e("checkin.result_count",{count:Ee?"—":v(wa.total)}))}function za(){const s=c("#cinTableMount");if(!s)return;if(s.setAttribute("aria-busy",String(re)),Ee){s.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("checkin.load_error"))}</strong><button type="button" class="btn" id="cinRetry">${t(e("checkin.refresh"))}</button></div>`,c("#cinRetry").onclick=()=>O();return}if(re){s.innerHTML=`<div class="pay-empty" role="status">${t(e("checkin.loading"))}</div>`;return}if(!Be.length){s.innerHTML=`<div class="pay-empty"><strong>${t(e("checkin.empty"))}</strong>${t(e("checkin.empty_hint"))}</div>`;return}const a=Be.map(n=>`<tr>
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${t(n.initial||"?")}</div><div class="person-cell__text"><strong>${t(n.name||"")}</strong><small>${t(n.code||"")} · ${t(n.phone||"")}</small></div></div></td>
    <td>${t(n.date_label||"")} · ${t(n.time||"")}</td>
    <td>${t(n.branch_name||n.branch||"—")}</td>
    <td>${t(n.beautician||"—")}</td>
    <td>${t(n.treatment||"—")}</td>
    <td>${P(n.payment_label)}</td>
    <td>${P(n.clearance_label)}</td>
    <td>${t(n.waiting_label||"—")}</td>
    <td>${P(n.status_label)}</td>
    <td><button type="button" class="btn small soft" data-cin-view="${n.id}">${t(e("checkin.view"))}</button></td>
  </tr>`).join("");s.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("checkin.col_customer"))}</th><th>${t(e("checkin.col_time"))}</th>
    <th>${t(e("checkin.col_branch"))}</th><th>${t(e("checkin.col_beautician"))}</th>
    <th>${t(e("checkin.col_treatment"))}</th><th>${t(e("checkin.col_payment"))}</th>
    <th>${t(e("checkin.col_clearance"))}</th><th>${t(e("checkin.col_wait"))}</th>
    <th>${t(e("checkin.col_status"))}</th><th>${t(e("checkin.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${G("cin",wa)}`,M("[data-cin-view]").forEach(n=>n.onclick=()=>$s(n.dataset.cinView)),J("cin",n=>(r.checkinPage=n,O()))}function ys(s){try{const a=new URL(String(s||"").trim(),window.location.origin),n=new URL(_.checkinPassBaseUrl,window.location.origin);return a.origin===n.origin&&a.pathname.startsWith(n.pathname.replace(/\/$/,"")+"/")&&a.searchParams.has("expires")&&a.searchParams.has("signature")}catch{return!1}}function Xe(){cancelAnimationFrame(ma),ma=0,_e&&(_e.getTracks().forEach(a=>a.stop()),_e=null);const s=c("#cinScannerVideo");s&&(s.srcObject=null)}function fs(){const s=`<div class="cin-scanner">
    <p class="cin-scanner__hint">${t(e("checkin.scanner_hint"))}</p>
    <div class="cin-scanner__viewport"><video id="cinScannerVideo" playsinline muted aria-label="${t(e("checkin.scanner_title"))}"></video><div class="cin-scanner__guide" aria-hidden="true"></div></div>
    <p class="cin-scanner__status" id="cinScannerStatus" aria-live="polite"></p>
    <button type="button" class="btn primary" id="cinScannerStart">${t(e("checkin.scanner_start"))}</button>
    <label class="lead-field cin-scanner__manual"><span class="lead-field__label">${t(e("checkin.scanner_manual_label"))}</span>
      <input class="lead-field__control" id="cinScannerInput" type="url" inputmode="url" autocomplete="off" placeholder="${t(e("checkin.scanner_manual_placeholder"))}">
    </label>
  </div>`,a=`<button type="button" class="btn primary" id="cinScannerOpen">${t(e("checkin.scanner_open"))}</button><button type="button" class="btn" data-action-drawer-close>${t(e("checkin.close"))}</button>`;se(e("checkin.scanner_title"),e("checkin.scanner_hint"),s,a,e("nav.checkin")),c("#cinScannerStart").onclick=Lt,c("#cinScannerOpen").onclick=()=>_a(c("#cinScannerInput").value),c("#cinScannerInput").onkeydown=n=>{n.key==="Enter"&&(n.preventDefault(),_a(n.currentTarget.value))}}function _a(s){if(!ys(s)){const a=c("#cinScannerStatus");a&&(a.textContent=e("checkin.scanner_invalid"));return}Xe(),window.location.assign(String(s).trim())}async function Lt(){const s=c("#cinScannerStatus"),a=c("#cinScannerStart");if(!("BarcodeDetector"in window)||!navigator.mediaDevices?.getUserMedia){s.textContent=e("checkin.scanner_unsupported");return}a.disabled=!0,s.textContent=e("common.loading");try{if(!(BarcodeDetector.getSupportedFormats?await BarcodeDetector.getSupportedFormats():["qr_code"]).includes("qr_code"))throw new Error("QR format is unavailable");const i=new BarcodeDetector({formats:["qr_code"]});_e=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:"environment"}},audio:!1});const l=c("#cinScannerVideo");l.srcObject=_e,await l.play(),a.textContent=e("checkin.scanner_stop"),a.disabled=!1,a.onclick=()=>{Xe(),a.textContent=e("checkin.scanner_start"),a.onclick=Lt},s.textContent=e("checkin.scanner_hint");const o=async()=>{if(!(!_e||!l.isConnected)){try{const d=await i.detect(l);if(d[0]?.rawValue){_a(d[0].rawValue);return}}catch(d){console.error(d)}ma=requestAnimationFrame(o)}};o()}catch(n){console.error(n),Xe(),a.disabled=!1,s.textContent=e("checkin.scanner_unsupported")}}function $s(s){const a=Be.find($=>Number($.id)===Number(s));if(!a)return;const n=a.order_id&&_.orderShowUrlTemplate?U(_.orderShowUrlTemplate,a.order_id):"",i=a.status||"pending",l=!!a.checked_in_at,o=[{label:e("checkin.step_booked"),state:i==="pending"?"active":"done",time:""},{label:e("checkin.step_checked_in"),state:l?"done":"",time:l&&a.checked_in_label&&a.checked_in_label!=="—"?a.checked_in_label:""},{label:e("checkin.step_treatment"),state:i==="in_progress"?"active":i==="completed"?"done":"",time:""},{label:e("checkin.step_completed"),state:i==="completed"?"active done":"",time:""}],d=`<div class="cin-preview__block cin-timeline"><div class="journey-section__title">${t(e("checkin.timeline"))}</div>
    <div class="timeline">${o.map($=>`<div class="timeline-step ${$.state}"><div class="timeline-dot">${$.state.includes("done")?"✓":$.state.includes("active")?"●":""}</div><span>${t($.label)}${$.time?` <em>· ${t($.time)}</em>`:""}</span></div>`).join("")}</div></div>`,u=`${a.phone||a.email?`<div class="cin-preview__contact">
    ${a.phone?`<a href="tel:${t(a.phone)}"><span aria-hidden="true">📞</span>${t(a.phone)}</a>`:""}
    ${a.email?`<a href="mailto:${t(a.email)}"><span aria-hidden="true">✉️</span>${t(a.email)}</a>`:""}
  </div>`:""}`,p=`<div class="cin-preview__block cin-preview__qr">
    <div class="journey-section__title">${t(e("checkin.qr_title"))}</div>
    <div class="cin-preview__qr-box"><canvas id="cinQrCanvas" role="img" aria-label="${t(e("checkin.qr_title"))}"></canvas></div>
    <div class="cin-preview__qr-code">${t(a.code||"")}</div>
    <p class="cin-preview__qr-hint">${t(e("checkin.qr_hint"))}</p>
    <button type="button" class="btn small soft" id="cinCopyCode">${t(e("checkin.copy_code"))}</button>
  </div>`,m=`<div class="cin-preview__block"><div class="journey-section__title">${t(e("checkin.details"))}</div>
    <div class="journey-kv">
      <div><span>${t(e("checkin.col_treatment"))}</span><strong>${t(a.treatment||"—")}</strong></div>
      <div><span>${t(e("checkin.col_beautician"))}</span><strong>${t(a.beautician||"—")}</strong></div>
      <div><span>${t(e("checkin.col_branch"))}</span><strong>${t(a.branch_name||a.branch||"—")}</strong></div>
      <div><span>${t(e("checkin.col_payment"))}</span><strong>${t(a.payment_label||"—")}</strong></div>
      <div><span>${t(e("checkin.col_date"))}</span><strong>${t((a.date_label||"")+" "+(a.time||""))}</strong></div>
      <div><span>${t(e("checkin.col_checked_in"))}</span><strong>${t(a.checked_in_label||"—")}</strong></div>
      <div><span>${t(e("checkin.col_wait"))}</span><strong>${t(a.waiting_label||"—")}</strong></div>
      <div><span>${t(e("checkin.col_clearance"))}</span><strong>${t(a.clearance_label||"—")}</strong></div>
    </div></div>`,h=`<div class="pay-review cin-preview">
    <div class="pay-review__hero pay-review__hero--cin">
      <div class="pay-review__avatar cin-avatar" aria-hidden="true">${t(a.initial||"?")}</div>
      <div style="min-width:0;flex:1">
        <div class="pay-id">${t(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${t(a.name||"")}</strong>
        <div class="pay-method">${t(a.date_label||"")} ${t(a.time||"")} · ${t(a.branch_name||a.branch||"—")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${P(a.status_label)}${P(a.arrival_label)}</div>
      </div>
    </div>
    ${u}${p}${d}${m}
  </div>`,g=[n&&_.canViewOrder?`<a class="btn primary" href="${t(n)}" target="_blank" rel="noopener">${t(e("checkin.open_order"))}</a>`:"",_.canViewTreatments&&_.treatmentReservationsUrl?`<a class="btn" href="${t(_.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("checkin.open_crm"))}</a>`:"",`<button type="button" class="btn" data-action-drawer-close>${t(e("checkin.close"))}</button>`].filter(Boolean).join("");se(e("checkin.title"),a.code+" · "+a.name,h,g,e("nav.checkin"));const f=c("#cinQrCanvas");if(f&&window.QRCentral)try{QRCentral.render(f,a.checkin_pass_url||"")}catch($){console.error($),f.closest(".cin-preview__qr-box")?.classList.add("hidden")}const b=c("#cinCopyCode");b&&(b.onclick=()=>ws(a.code||"",e("checkin.copied")))}function ws(s,a){const n=()=>w(a);if(!s){w(e("checkin.copy_missing"));return}navigator.clipboard&&window.isSecureContext?navigator.clipboard.writeText(s).then(n).catch(()=>Ga(s,n)):Ga(s,n)}function Ga(s,a){const n=document.createElement("textarea");n.value=s,n.setAttribute("readonly",""),n.style.position="fixed",n.style.opacity="0",document.body.appendChild(n),n.select();try{document.execCommand("copy"),a()}catch{w(e("checkin.copy_missing"))}document.body.removeChild(n)}function ks(){B.innerHTML=`<div class="clr-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${t(e("clearance.title"))}</h1>
        <div class="page-subtitle">${t(e("clearance.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="clrRefresh">${t(e("clearance.refresh"))}</button>
        <button type="button" class="btn" data-jump="payments">${t(e("clearance.open_payments"))}</button>
        ${_.canViewTreatments&&_.treatmentReservationsUrl?`<a class="btn primary" href="${t(_.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("clearance.open_crm"))}</a>`:""}
      </div>
    </div>
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
  </div>`;const s=c("#clrRefresh");s&&(s.onclick=()=>te()),X(),Ss(),te()}function Ss(){const s=c("#clrSearch");s&&(s.oninput=()=>{clearTimeout(Ha),Ha=setTimeout(()=>{r.clearanceSearch=s.value.trim(),r.clearancePage=1,te()},320)});const a=c("#clrBeautician");a&&(a.onchange=()=>{r.clearanceBeautician=a.value,r.clearancePage=1,te()});const n=c("#clrBranch");n&&(n.onchange=()=>{r.clearanceBranch=n.value,r.clearancePage=1,te()})}async function te(){const s=++Ie;qe=!1;const a=_.clearanceUrl||"",n=c("#clrTableMount");if(!a){n&&(n.innerHTML=`<div class="pay-empty"><strong>${t(e("clearance.load_error"))}</strong></div>`);return}oe=!0,Ja(),Xa();const i=new URLSearchParams;r.clearanceSearch&&i.set("q",r.clearanceSearch),r.clearanceState&&i.set("state",r.clearanceState);const l=r.clearanceBranch!=="all"?r.clearanceBranch:r.branch||"all";l&&l!=="all"&&i.set("branch",l),r.clearanceBeautician&&r.clearanceBeautician!=="all"&&i.set("beautician",r.clearanceBeautician),i.set("page",String(r.clearancePage||1)),i.set("per_page","25");try{const o=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!o.ok)throw new Error("clearance "+o.status);const d=await o.json();if(s!==Ie)return;Ae=Array.isArray(d.data)?d.data:[],ka=Object.assign({waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0},d.meta&&d.meta.summary||{}),va=Object.assign({states:[],beauticians:[],branches:[]},d.filters||{}),Sa={current_page:d.meta&&d.meta.current_page||1,last_page:d.meta&&d.meta.last_page||1,total:d.meta&&d.meta.total||0}}catch(o){if(s!==Ie)return;qe=!0,console.error(o),Ae=[],w(e("clearance.load_error"))}finally{if(s!==Ie)return;oe=!1,Ja(),Ms(),Ts(),Xa()}}function Ja(){const s=c("#clrMetrics");if(!s)return;const a=ka;s.setAttribute("aria-busy",String(oe));const n=i=>oe||qe?"—":v(i);s.innerHTML=`
    <div class="pay-metric"><span>${t(e("clearance.stat_waiting"))}</span><strong>${n(a.waiting)}</strong></div>
    <div class="pay-metric"><span>${t(e("clearance.stat_blocked"))}</span><strong>${n(a.blocked)}</strong></div>
    <div class="pay-metric"><span>${t(e("clearance.stat_treatment"))}</span><strong>${n(a.in_treatment)}</strong></div>
    <div class="pay-metric"><span>${t(e("clearance.stat_done"))}</span><strong>${n(a.done_today)}</strong></div>`}function Ms(){const s=c("#clrTabs");if(!s)return;const a=ka,n=[["waiting",e("clearance.tab_waiting"),a.waiting],["blocked",e("clearance.tab_blocked"),a.blocked],["in_treatment",e("clearance.tab_treatment"),a.in_treatment],["all_queue",e("clearance.tab_queue"),a.queue],["done",e("clearance.tab_done"),a.done_today]];s.innerHTML=n.map(([i,l,o])=>{const d=r.clearanceState===i?"active":"";return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-clrtab="${i}">${t(l)} (${v(o||0)})</button>`}).join(""),M("[data-clrtab]",s).forEach(i=>i.onclick=()=>{r.clearanceState=i.dataset.clrtab,r.clearancePage=1,te()})}function Ts(){const s=c("#clrBeautician");if(s){const i=r.clearanceBeautician||"all";s.innerHTML=`<option value="all">${t(e("clearance.all_beauticians"))}</option>`+(va.beauticians||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const a=c("#clrBranch");if(a){const i=r.clearanceBranch||"all";a.innerHTML=`<option value="all">${t(e("clearance.all_branches"))}</option>`+(va.branches||[]).map(l=>`<option value="${l.id}"${String(i)===String(l.id)?" selected":""}>${t(l.name)}</option>`).join("")}const n=c("#clrResultCount");n&&(n.textContent=e("clearance.result_count",{count:qe?"—":v(Sa.total)}))}function Xa(){const s=c("#clrTableMount");if(!s)return;if(s.setAttribute("aria-busy",String(oe)),qe){s.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("clearance.load_error"))}</strong><button type="button" class="btn" id="clrRetry">${t(e("clearance.refresh"))}</button></div>`,c("#clrRetry").onclick=()=>te();return}if(oe){s.innerHTML=`<div class="pay-empty" role="status">${t(e("clearance.loading"))}</div>`;return}if(!Ae.length){s.innerHTML=`<div class="pay-empty"><strong>${t(e("clearance.empty"))}</strong>${t(e("clearance.empty_hint"))}</div>`;return}const a=Ae.map(n=>`<tr>
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${t(n.initial||"?")}</div><div class="person-cell__text"><strong>${t(n.name||"")}</strong><small>${t(n.code||"")} · ${t(n.phone||"")}</small></div></div></td>
    <td>${t(n.date_label||"")} · ${t(n.time||"")}</td>
    <td>${t(n.branch_name||n.branch||"—")}</td>
    <td>${t(n.treatment||"—")}</td>
    <td>${P(n.payment_label)}</td>
    <td>${P(n.clearance_label)}</td>
    <td><button type="button" class="btn small soft" data-clr-view="${n.id}">${t(e("clearance.view"))}</button></td>
  </tr>`).join("");s.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("clearance.col_customer"))}</th><th>${t(e("clearance.col_time"))}</th>
    <th>${t(e("clearance.col_branch"))}</th><th>${t(e("clearance.col_treatment"))}</th>
    <th>${t(e("clearance.col_payment"))}</th><th>${t(e("clearance.col_clearance"))}</th>
    <th>${t(e("clearance.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${G("clr",Sa)}`,M("[data-clr-view]").forEach(n=>n.onclick=()=>Ls(n.dataset.clrView)),J("clr",n=>(r.clearancePage=n,te()))}function Ls(s){const a=Ae.find(o=>Number(o.id)===Number(s));if(!a)return;const n=a.order_id&&_.orderShowUrlTemplate?U(_.orderShowUrlTemplate,a.order_id):"",i=`<div class="pay-review">
    <div class="pay-review__hero"><div class="pay-review__avatar">${t(a.initial||"?")}</div>
      <div style="min-width:0;flex:1"><div class="pay-id">${t(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${t(a.name||"")}</strong>
        <div class="pay-method">${t(a.phone||"—")} · ${t(a.treatment||"")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${P(a.clearance_label)}${P(a.payment_label)}</div>
      </div></div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${t(e("clearance.col_time"))}</label><strong>${t((a.date_label||"")+" "+(a.time||""))}</strong></div>
      <div class="pay-review__card"><label>${t(e("clearance.col_branch"))}</label><strong>${t(a.branch_name||a.branch||"—")}</strong></div>
      <div class="pay-review__card"><label>${t(e("checkin.col_beautician"))}</label><strong>${t(a.beautician||"—")}</strong></div>
    </div></div>`,l=[n&&_.canViewOrder?`<a class="btn primary" href="${t(n)}" target="_blank" rel="noopener">${t(e("clearance.open_order"))}</a>`:"",_.canViewTreatments&&_.treatmentReservationsUrl?`<a class="btn" href="${t(_.treatmentReservationsUrl)}" target="_blank" rel="noopener">${t(e("clearance.open_crm"))}</a>`:"",`<button type="button" class="btn" data-action-drawer-close>${t(e("clearance.close"))}</button>`].filter(Boolean).join("");se(e("clearance.title"),a.code+" · "+a.name,i,l,e("nav.clearance")),setTimeout(()=>{M("#actionDrawerFoot [data-jump]").forEach(o=>o.onclick=()=>{R(),F(o.dataset.jump)})},0)}function Cs(){B.innerHTML=`<div class="wal-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${t(e("wallet.title"))}</h1>
        <div class="page-subtitle">${t(e("wallet.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="walRefresh">${t(e("wallet.refresh"))}</button>
        ${_.canViewLoyalty&&_.loyaltyMembersUrl?`<a class="btn primary" href="${t(_.loyaltyMembersUrl)}" target="_blank" rel="noopener">${t(e("wallet.open_loyalty"))}</a>`:""}
      </div>
    </div>
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
        <div class="lead-filter-grid" style="grid-template-columns:minmax(0,1fr)">
          <label class="lead-field"><span class="lead-field__label">${t(e("wallet.filter_tier"))}</span>
            <select class="lead-field__control" id="walTier"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="walTableMount"></div>
    </section>
  </div>`;const s=c("#walRefresh");s&&(s.onclick=()=>ce()),xs(),ce()}function xs(){const s=c("#walSearch");s&&(s.oninput=()=>{clearTimeout(Aa),Aa=setTimeout(()=>{r.walletSearch=s.value.trim(),r.walletPage=1,ce()},320)});const a=c("#walTier");a&&(a.onchange=()=>{r.walletTier=a.value,r.walletPage=1,ce()})}async function ce(){const s=++Re;je=!1;const a=_.walletUrl||"",n=c("#walTableMount");if(!a){n&&(n.innerHTML=`<div class="pay-empty"><strong>${t(e("wallet.load_error"))}</strong></div>`);return}le=!0,Qa(),Za();const i=new URLSearchParams;r.walletSearch&&i.set("q",r.walletSearch),r.walletSegment&&r.walletSegment!=="all"&&i.set("segment",r.walletSegment),r.walletTier&&r.walletTier!=="all"&&i.set("tier",r.walletTier),i.set("page",String(r.walletPage||1)),i.set("per_page","25");try{const l=await fetch(`${a}?${i.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("wallet "+l.status);const o=await l.json();if(s!==Re)return;Pe=Array.isArray(o.data)?o.data:[],ya=Object.assign({members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0},o.meta&&o.meta.summary||{}),lt=Object.assign({segments:[],tiers:[]},o.filters||{}),fa={current_page:o.meta&&o.meta.current_page||1,last_page:o.meta&&o.meta.last_page||1,total:o.meta&&o.meta.total||0}}catch(l){if(s!==Re)return;je=!0,console.error(l),Pe=[],w(e("wallet.load_error"))}finally{if(s!==Re)return;le=!1,Qa(),Ps(),js(),Za()}}function Qa(){const s=c("#walMetrics");if(!s)return;const a=ya;s.setAttribute("aria-busy",String(le));const n=i=>le||je?"—":v(i);s.innerHTML=`
    <div class="pay-metric"><span>${t(e("wallet.stat_members"))}</span><strong>${n(a.members)}</strong></div>
    <div class="pay-metric"><span>${t(e("wallet.stat_balance"))}</span><strong>${n(a.with_balance)}</strong></div>
    <div class="pay-metric"><span>${t(e("wallet.stat_points"))}</span><strong>${n(a.points_outstanding)}</strong></div>
    <div class="pay-metric"><span>${t(e("wallet.stat_stamp"))}</span><strong>${n(a.stamp_ready)}</strong></div>`}function Ps(){const s=c("#walTabs");if(!s)return;const a=ya,n=[["all",e("wallet.tab_all"),a.members],["active",e("wallet.tab_active"),a.with_balance],["zero",e("wallet.tab_zero"),a.zero_balance],["stamp_ready",e("wallet.tab_stamp"),a.stamp_ready]];s.innerHTML=n.map(([i,l,o])=>{const d=r.walletSegment===i?"active":"";return`<button type="button" class="tab ${d}" aria-pressed="${!!d}" data-waltab="${i}">${t(l)} (${v(o||0)})</button>`}).join(""),M("[data-waltab]",s).forEach(i=>i.onclick=()=>{r.walletSegment=i.dataset.waltab,r.walletPage=1,ce()})}function js(){const s=c("#walTier");if(s){const n=r.walletTier||"all";s.innerHTML=`<option value="all">${t(e("wallet.all_tiers"))}</option>`+(lt.tiers||[]).map(i=>`<option value="${i.id}"${String(n)===String(i.id)?" selected":""}>${t(i.name)}</option>`).join("")}const a=c("#walResultCount");a&&(a.textContent=e("wallet.result_count",{count:je?"—":v(fa.total)}))}function Za(){const s=c("#walTableMount");if(!s)return;if(s.setAttribute("aria-busy",String(le)),je){s.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("wallet.load_error"))}</strong><button type="button" class="btn" id="walRetry">${t(e("wallet.refresh"))}</button></div>`,c("#walRetry").onclick=()=>ce();return}if(le){s.innerHTML=`<div class="pay-empty" role="status">${t(e("wallet.loading"))}</div>`;return}if(!Pe.length){s.innerHTML=`<div class="pay-empty"><strong>${t(e("wallet.empty"))}</strong>${t(e("wallet.empty_hint"))}</div>`;return}const a=Pe.map(n=>{const i=n.avatar_url?`<div class="mini-avatar mini-avatar--photo"><img src="${t(n.avatar_url)}" alt=""></div>`:`<div class="mini-avatar">${t(n.initial||"?")}</div>`,l=n.stamp_ready?P(e("wallet.chip_stamp",{count:n.stamp_ready})):"";return`<tr>
      <td><strong>${t(n.code||"")}</strong></td>
      <td><div class="person-cell person-cell--lead">${i}<div class="person-cell__text"><strong>${t(n.name||"")}</strong><small>${t(n.phone||n.email||"")}</small></div></div></td>
      <td>${n.tier?P(n.tier):"—"}</td>
      <td class="is-num"><strong>${v(n.balance)}</strong></td>
      <td class="is-num">${H(n.lifetime_spend)}</td>
      <td>${l||v(n.stamp_active||0)}</td>
      <td><button type="button" class="btn small soft" data-wal-view="${n.id}">${t(e("wallet.view"))}</button></td>
    </tr>`}).join("");s.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("wallet.col_id"))}</th><th>${t(e("wallet.col_customer"))}</th>
    <th>${t(e("wallet.col_tier"))}</th><th class="is-num">${t(e("wallet.col_balance"))}</th>
    <th class="is-num">${t(e("wallet.col_spend"))}</th><th>${t(e("wallet.col_stamps"))}</th>
    <th>${t(e("wallet.col_action"))}</th>
  </tr></thead><tbody>${a}</tbody></table></div>
  ${G("wal",fa)}`,M("[data-wal-view]").forEach(n=>n.onclick=()=>Bs(n.dataset.walView)),J("wal",n=>(r.walletPage=n,ce()))}let Me=null,La="";function Bs(s){const a=Pe.find(p=>Number(p.id)===Number(s));if(!a)return;R(),V(),Me=document.activeElement,La=document.body.style.overflow,document.body.style.overflow="hidden";const n=_.loyaltyMemberShowUrlTemplate?U(_.loyaltyMemberShowUrlTemplate,a.id):"",i=a.customer_id&&_.userEditUrlTemplate?U(_.userEditUrlTemplate,a.customer_id):"",l=(a.recent||[]).length?`<ol class="wallet-transactions">${a.recent.map(p=>`<li class="wallet-transaction">
        <div class="wallet-transaction__description"><strong>${t(p.description||p.type||"")}</strong><time>${t(p.created_label||"")}</time></div>
        <div class="wallet-transaction__amount"><strong class="${Number(p.points)>0?"is-credit":""}">${Number(p.points)>0?"+":""}${v(p.points)} <span>${t(e("wallet.col_balance"))}</span></strong><small>${t(e("wallet.transaction_balance",{balance:v(p.balance_after)}))}</small></div>
      </li>`).join("")}</ol>`:`<div class="pay-empty">${t(e("wallet.no_recent"))}</div>`,o=a.avatar_url?`<img class="wallet-detail__avatar" src="${t(a.avatar_url)}" alt="" />`:`<div class="wallet-detail__avatar" aria-hidden="true">${t(a.initial||"?")}</div>`,d=c("#leadDrawer");d.classList.add("wallet-drawer"),d.setAttribute("role","dialog"),d.setAttribute("aria-modal","true"),d.setAttribute("aria-labelledby","drawerName"),c("#leadDrawer .eyebrow").textContent=e("nav.wallet"),c("#drawerName").textContent=e("wallet.title"),c("#drawerClose").setAttribute("aria-label",e("wallet.close")),c("#drawerBody").innerHTML=`<div class="wallet-detail">
    <section class="wallet-detail__customer">${o}<div class="wallet-detail__identity">
      <span class="pay-id">${t(a.code||"")}</span>
      <h3>${t(a.name||"")}</h3>
      <p>${t(a.phone||"—")}</p><p>${t(a.email||"—")}</p>
      <div class="wallet-detail__badges">${a.tier?P(a.tier):""}${P(a.segment_label)}</div>
    </div></section>
    <section class="wallet-detail__balance"><span>${t(e("wallet.col_balance"))}</span><strong>${v(a.balance)}</strong></section>
    <div class="wallet-detail__metrics">
      <section><span>${t(e("wallet.col_spend"))}</span><strong>${H(a.lifetime_spend)}</strong></section>
      <section><span>${t(e("wallet.col_stamps"))}</span><strong>${t(e("wallet.stamp_summary",{active:v(a.stamp_active),ready:v(a.stamp_ready)}))}</strong></section>
    </div>
    <section class="wallet-detail__history"><h3>${t(e("wallet.detail_recent"))}</h3>${l}</section>
  </div>`;const u=document.createElement("div");u.id="walletDrawerFoot",u.className="wallet-drawer__footer",u.innerHTML=[n&&_.canShowLoyaltyMember?`<a class="btn primary" href="${t(n)}" target="_blank" rel="noopener">${t(e("wallet.open_member"))}</a>`:"",i&&_.canViewUser?`<a class="btn" href="${t(i)}" target="_blank" rel="noopener">${t(e("wallet.open_profile"))}</a>`:"",`<button type="button" class="btn" data-wallet-close>${t(e("wallet.close"))}</button>`].filter(Boolean).join(""),d.appendChild(u),c("[data-wallet-close]",u).onclick=V,d.classList.add("show"),d.setAttribute("aria-hidden","false"),d.inert=!1,c(".app-shell").inert=!0,c("#drawerBackdrop").classList.add("show"),c("#drawerBody").scrollTop=0,c("#drawerClose").focus({preventScroll:!0})}document.addEventListener("keydown",s=>{const a=c("#actionDrawer.show")||c("#leadDrawer.show");if(!a)return;const n=a.id==="actionDrawer"?R:V;if(s.key==="Escape"){s.preventDefault(),n();return}if(s.key!=="Tab")return;const i=M('button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex="0"]',a).filter(d=>d.getClientRects().length),l=i[0],o=i[i.length-1];if(!l){s.preventDefault();return}s.shiftKey&&(document.activeElement===l||!a.contains(document.activeElement))?(s.preventDefault(),o.focus()):!s.shiftKey&&(document.activeElement===o||!a.contains(document.activeElement))&&(s.preventDefault(),l.focus())});const ge=Object.fromEntries(["beauticians","branches","audit"].map(s=>[s,{q:"",sort:"revenue",page:1}]));let Oe=0,et=null,ee=null;function Es(){Ca("beauticians")}function As(){Ca("branches")}function qs(){Ca("audit")}function Ca(s){const a=ge[s];B.innerHTML=`<div class="report-shell">
    <div class="page-head"><div><h1 class="page-title">${t(e("nav."+s))}</h1><p class="page-subtitle">${t(e("reporting."+(s==="audit"?"audit_subtitle":"subtitle")))}</p></div>
      <button type="button" class="btn" id="reportRefresh">${t(e("reporting.refresh"))}</button></div>
    <div class="pay-metrics" id="reportMetrics" aria-live="polite"></div>
    <section class="lead-panel card"><div class="lead-panel__head"><div><h2 class="card-title">${t(e("reporting."+(s==="audit"?"recorded_imports":"performance")))}</h2><p class="lead-panel__sub" id="reportPeriod"></p></div><span class="lead-panel__count" id="reportCount">—</span></div>
      <div class="lead-panel__filters report-filters"><label class="lead-field"><span class="lead-field__label">${t(e("reporting.search"))}</span><input class="lead-field__control" type="search" id="reportSearch" maxlength="150" value="${t(a.q)}" placeholder="${t(e("reporting."+(s==="audit"?"search_batch":"search_name")))}"></label>
      ${s!=="audit"?`<label class="lead-field"><span class="lead-field__label">${t(e("reporting.sort"))}</span><select id="reportSort" class="lead-field__control">${["revenue","leads","conversion","orders"].map(i=>`<option value="${i}"${a.sort===i?" selected":""}>${t(e("reporting."+i))}</option>`).join("")}</select></label>`:""}</div>
      <div id="reportTable" class="lead-panel__body pay-table" aria-live="polite"></div>
    </section>
    <p class="report-note">${t(e("reporting."+(s==="audit"?"audit_note":"methodology")))}</p>
  </div>`,c("#reportRefresh").onclick=()=>Te(),c("#reportSearch").oninput=i=>{a.q=i.target.value,a.page=1,clearTimeout(et),s==="audit"?et=setTimeout(()=>{r.view===s&&Te()},350):ee&&Qe(s)};const n=c("#reportSort");n&&(n.onchange=i=>{a.sort=i.target.value,ee&&Qe(s)}),Te()}async function Te(){const s=r.view;if(!ge[s])return;const a=++Oe,n=ge[s],i=c("#reportTable");if(!i)return;ee=null,c("#reportMetrics").innerHTML="",i.setAttribute("aria-busy","true"),i.innerHTML=`<div class="pay-empty" role="status">${t(e("reporting.loading"))}</div>`;const l=new URLSearchParams({view:s,page:String(n.page)});r.period&&l.set("period",r.period),r.branch&&r.branch!=="all"&&l.set("branch",r.branch),s==="audit"&&n.q.trim()&&l.set("q",n.q.trim());try{if(!_.reportingUrl)throw new Error("Missing reporting endpoint");const o=await fetch(_.reportingUrl+"?"+l.toString(),{headers:D(!1),credentials:"same-origin"});if(!o.ok)throw new Error("Reporting "+o.status);const d=await o.json();if(a!==Oe||r.view!==s)return;ee=d;const u=d.meta.summary,p=s==="audit"?["batches","imported","duplicates","invalid"]:["leads","converted","orders","revenue"];c("#reportMetrics").innerHTML=p.map(m=>`<div class="pay-metric"><span>${t(e("reporting."+m))}</span><strong>${m==="revenue"?H(u[m]):v(u[m])}</strong></div>`).join(""),c("#reportPeriod").textContent=d.meta.period,Qe(s)}catch{if(a!==Oe||r.view!==s)return;c("#reportCount").textContent="—",i.innerHTML=`<div class="pay-empty" role="alert"><strong>${t(e("reporting.error"))}</strong><button type="button" class="btn" id="reportRetry">${t(e("reporting.refresh"))}</button></div>`,c("#reportRetry").onclick=()=>Te()}finally{a===Oe&&r.view===s&&i.setAttribute("aria-busy","false")}}function Qe(s){const a=c("#reportTable");if(!a||!ee)return;const n=ge[s];let i=[...ee.data||[]];s!=="audit"&&(i=i.filter(p=>String(p.name).toLocaleLowerCase().includes(n.q.trim().toLocaleLowerCase())),i.sort((p,m)=>Number(m[n.sort])-Number(p[n.sort])||String(p.name).localeCompare(String(m.name))));const l=s==="audit"?ee.meta.total:i.length,o=s==="audit"?ee.meta:{current_page:Math.max(1,Math.min(n.page,Math.ceil(l/25)||1)),last_page:Math.ceil(l/25)||1};s!=="audit"&&(n.page=o.current_page,i=i.slice((n.page-1)*25,n.page*25)),c("#reportCount").textContent=e("reporting.results",{count:v(l)});const d=s==="audit"?["code","date","actor","branch","method","status","raw","imported","duplicates","invalid"]:["name","leads","converted","conversion","follow_up","lost","orders","revenue","average_order"],u=["code","date","actor","branch","method","status","name"];a.innerHTML=i.length?`<div class="table-wrap report-table-wrap"><table class="data-table report-table"><thead><tr>${d.map(p=>`<th${u.includes(p)?"":' class="is-num"'}>${t(e("reporting."+p))}</th>`).join("")}</tr></thead><tbody>${i.map(p=>`<tr>${d.map(m=>{let h=u.includes(m)?t(m==="status"?e("reporting.status_"+p[m]):p[m]):m==="conversion"?Number(p.leads)>0?Number(p[m]).toFixed(1)+"%":"—":["revenue","average_order"].includes(m)?H(p[m]):v(p[m]);return`<td${u.includes(m)?"":' class="is-num"'}>${["name","code"].includes(m)?`<strong>${h}</strong>`:h}</td>`}).join("")}</tr>`).join("")}</tbody></table></div>`:`<div class="pay-empty"><strong>${t(e("reporting.empty"))}</strong>${t(e("reporting.empty_hint"))}</div>`,a.insertAdjacentHTML("beforeend",G("report",o)),J("report",p=>{if(n.page=p,s==="audit")return Te();Qe(s)})}function Hs(s,a){B.innerHTML=`${de(t(s),t(a))}<section class="card"><div class="empty">${t(a)}</div></section>`}function Ns(){B.innerHTML=`${de(e("followup.title"),e("followup.subtitle"),`<button type="button" class="btn" data-jump="leads">${t(e("followup.open_workspace"))}</button>`)}
  <div class="grid kpi-grid" id="followKpiMount"></div>
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
  </section>`,X(),Ct(),ae()}async function ae(){const s=_.followUpsUrl||"";if(!s){w(e("followup.load_error"));return}const a=new URLSearchParams;r.followSearch&&a.set("q",r.followSearch),r.followBucket&&r.followBucket!=="all"&&a.set("bucket",r.followBucket);const n=r.leadBranch!=="all"?r.leadBranch:r.branch||"all";n&&n!=="all"&&a.set("branch",n),r.leadBeautician&&r.leadBeautician!=="all"&&a.set("beautician",r.leadBeautician),a.set("page",String(r.followPage||1)),Ye=!0,r.view==="followup"&&at();try{const i=await fetch(s+"?"+a.toString(),{headers:D(!1),credentials:"same-origin"});if(!i.ok)throw new Error("followups "+i.status);const l=await i.json();ot=l.meta||{},we=Array.isArray(l.data)?l.data:[],da=l.meta&&l.meta.summary||da,ke=l.filters||ke,l.filters?.statuses&&(A.statuses=l.filters.statuses)}catch(i){console.error(i),w(e("followup.load_error"))}finally{Ye=!1,r.view==="followup"&&(Ds(),Ct(),at())}}function Ct(){const s=ke.buckets||[{value:"all",label:e("followup.bucket_all")},{value:"overdue",label:e("followup.bucket_overdue")},{value:"due_today",label:e("followup.bucket_due_today")},{value:"no_response",label:e("followup.bucket_no_response")},{value:"lost",label:e("followup.bucket_lost")}],a=c("#followBuckets");a&&(a.innerHTML=s.map(o=>`<button type="button" class="tab ${String(r.followBucket)===String(o.value)?"active":""}" role="tab" data-follow-bucket="${t(o.value)}">${t(o.label)}</button>`).join(""),M("[data-follow-bucket]",a).forEach(o=>{o.onclick=()=>{r.followBucket=o.dataset.followBucket||"all",r.followPage=1,ae()}}));const n=c("#followBeautician");if(n){const o=[{id:"all",name:e("workspace.all_beauticians")},...ke.beauticians||A.beauticians||[]];n.innerHTML=o.map(d=>`<option value="${t(d.id)}" ${String(r.leadBeautician)===String(d.id)?"selected":""}>${t(d.name)}</option>`).join(""),n.onchange=d=>{r.leadBeautician=d.target.value,r.followPage=1,ae()}}const i=c("#followBranch");if(i){const o=[{id:"all",name:e("workspace.all_branches")},...ke.branches||_.branches||[]];i.innerHTML=o.map(d=>`<option value="${t(d.id)}" ${String(r.leadBranch)===String(d.id)?"selected":""}>${t(d.name)}</option>`).join(""),i.onchange=d=>{r.leadBranch=d.target.value,r.followPage=1,ae()}}const l=c("#followSearch");l&&(l.oninput=o=>{r.followSearch=o.target.value,clearTimeout(ja),ja=setTimeout(()=>{r.followPage=1,ae()},350)})}function Ds(){const s=c("#followKpiMount");if(!s)return;const a=da||{};s.innerHTML=`
    ${x("↻",e("followup.kpi_queue"),v(a.queue),e("followup.kpi_queue_meta"),"—","blue")}
    ${x("⏱",e("followup.kpi_overdue"),v(a.overdue),e("followup.kpi_overdue_meta"),"—","rose")}
    ${x("✓",e("followup.kpi_due_today"),v(a.due_today),e("followup.kpi_due_meta"),"—","green")}
    ${x("⌀",e("followup.kpi_no_response"),v(a.no_response),e("followup.kpi_no_response_meta"),"—","purple")}
    ${x("✕",e("followup.kpi_lost"),v(a.lost),e("followup.kpi_lost_meta"),"—","rose")}
  `}function at(){const s=c("#followTableMount");if(!s)return;const a=Array.isArray(we)?we.length:0,n=c("#followResultCount");if(n&&(n.textContent=a===1?e("followup.results_count_one"):e("followup.results_count",{count:v(a)})),Ye){s.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${t(e("followup.loading"))}</strong></div>`;return}const i=we;if(!i.length){s.innerHTML=`<div class="lead-empty">
      <div class="lead-empty__icon" aria-hidden="true">↻</div>
      <strong>${t(e("followup.empty"))}</strong>
      <p>${t(e("followup.empty_hint"))}</p>
      <button type="button" class="btn" data-jump="leads">${t(e("followup.open_workspace"))}</button>
    </div>`,X();return}const l=!!_.canEditLead;s.innerHTML=`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${t(e("workspace.col_lead_id"))}</th>
    <th>${t(e("workspace.col_customer"))}</th>
    <th>${t(e("workspace.col_phone"))}</th>
    <th>${t(e("workspace.col_beautician"))}</th>
    <th>${t(e("workspace.col_branch"))}</th>
    <th>${t(e("workspace.col_status"))}</th>
    <th>${t(e("workspace.col_last_fu"))}</th>
    <th>${t(e("followup.col_waiting"))}</th>
    <th class="lead-table__actions"><span class="sr-only">${t(e("workspace.col_action"))}</span></th>
  </tr></thead><tbody>${i.map(o=>{const d=String(o.name||""),u=t((d[0]||"?").toUpperCase()),p=Number(o.days_since_followup||0),m=o.last_followed_up_at?e("followup.days",{count:p}):e("followup.never"),h=String(o.followup_bucket)==="overdue";return`<tr>
      <td><span class="lead-code">${t(o.code||o.id)}</span></td>
      <td><div class="person-cell person-cell--lead"><div class="mini-avatar" aria-hidden="true">${u}</div><div class="person-cell__text"><strong>${t(d||"—")}</strong><small>${t(o.customer||"")}</small></div></div></td>
      <td><span class="lead-mono">${t(o.phone||"—")}</span></td>
      <td>${t(o.beautician||"—")}</td>
      <td><span class="lead-branch">${t(o.branch||"—")}</span></td>
      <td>${P(o.status)}</td>
      <td><span class="lead-date">${t(o.last||"—")}</span></td>
      <td><span class="lead-wait${h?" is-overdue":""}">${t(m)}</span></td>
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
    </tr>`}).join("")}</tbody></table></div>`,s.insertAdjacentHTML("beforeend",G("follow",ot)),J("follow",o=>(r.followPage=o,ae())),gt(s),M("[data-follow-view]",s).forEach(o=>o.onclick=()=>{K(),aa(o.dataset.followView)}),M("[data-follow-mark]",s).forEach(o=>o.onclick=()=>{K(),xt(o.dataset.followMark)})}async function xt(s){const a=U(_.leadFollowUpUrlTemplate,s);if(!a){w(e("followup.mark_error"));return}try{const n=await fetch(a,{method:"POST",headers:D(!0),credentials:"same-origin",body:JSON.stringify({})}),i=await n.json().catch(()=>({}));if(!n.ok){w(i.message||e("followup.mark_error"));return}w(i.message||e("followup.marked")),r.view==="followup"?await ae():await q()}catch(n){console.error(n),w(e("followup.mark_error"))}}function Pt(){const s=C||{},a=s.kpis||{},n=a.vs_prev||{},i=s.targets||{},l=s.dual||{},o=Number(l.sales_pct||s.target_board&&s.target_board.sales_pct||0),d=Number(i.sales||0),u=Number(a.sales||0),p=u-d,m=t(s.period&&s.period.label||e("common.this_month")),h=Array.isArray(s.sales_insights)?s.sales_insights:[],g=Array.isArray(s.waterfall)?s.waterfall:[],f=Math.max(1,...g.map(b=>Number(b.value||0)));B.innerHTML=`${de(e("sales.title"),e("sales.subtitle"),`<button type="button" class="btn soft" id="salesRefreshBtn">${t(e("sales.refresh"))}</button>
     <button type="button" class="btn" data-jump="payments">${t(e("sales.open_payments"))}</button>
     <button type="button" class="btn primary" data-jump="leads">${t(e("sales.open_leads"))}</button>`)}
  <section class="sales-insights card">
    <div class="sales-insights__head">
      <div>
        <div class="journey-section__title">${t(e("sales.insights"))}</div>
        <p class="card-subtitle" style="margin:0">${m}</p>
      </div>
    </div>
    <div class="sales-insights__grid">
      ${h.length?h.map(b=>`<article class="sales-insight sales-insight--${t(b.tone||"info")}"><strong>${t(b.title||"")}</strong><p>${t(b.body||"")}</p></article>`).join(""):`<article class="sales-insight sales-insight--info"><strong>${t(e("sales.title"))}</strong><p>${t(e("sales.subtitle"))}</p></article>`}
    </div>
  </section>

  <div class="grid kpi-grid" style="margin-top:12px">
    ${x("◫",e("sales.kpi_sales"),L(a.sales),Y(n.sales,"%"),Q(e("sales.kpi_sales"),L(d)),"rose",Math.min(100,o),"","")}
    ${x("▣",e("sales.kpi_orders"),v(a.orders||0),Y(n.orders||0,"%"),"—","blue")}
    ${x("♙",e("sales.kpi_customers"),v(a.buyers),Y(n.buyers,"%"),Q(e("sales.kpi_customers"),v(i.buyers||0)),"green")}
    ${x("▥",e("sales.kpi_avg"),L(a.avg_sale),Y(n.avg_sale,"%"),Q(e("sales.kpi_avg"),L(i.avg_sale||0)),"purple")}
    ${x("%",e("sales.kpi_target"),W(o),p>=0?"↑ "+L(Math.abs(p)):"↓ "+L(Math.abs(p)),e("sales.of_target"),"green",Math.min(100,o))}
  </div>

  <div class="grid split-60" style="margin-top:12px">
    <section class="card sales-card">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${t(m)}</div>
          <div class="daily-panel__title">${t(e("sales.revenue_trend"))}</div>
          <div class="daily-panel__sub">${t(e("sales.revenue_sub"))}</div>
        </div>
        <span class="sales-card__pill">${L(u)}</span>
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
            <strong>${W(o)}</strong>
            <span>${t(e("sales.of_target"))}</span>
          </div>
        </div>
        <div class="target-card__side">
          <div class="target-card__stat"><span>${t(e("overview.target"))}</span><strong>${L(d)}</strong></div>
          <div class="target-card__stat"><span>${t(e("overview.actual"))}</span><strong>${L(u)}</strong></div>
          <div class="target-card__delta">
            <strong>${p>=0?"+":""}${L(p)}</strong>
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
      <span class="badge blue">${t(e("sales.conv_rate"))}: ${W(a.new_buyer_share_pct||0)}</span>
    </div>
    <div class="sales-funnel">
      ${g.length?g.map((b,$)=>{const S=Number(b.value||0),T=Math.max(8,Math.round(S/f*100));return`<div class="sales-funnel__step">
              <div class="sales-funnel__meta"><span>${t(b.name||"")}</span><strong>${v(S)}</strong></div>
              <div class="sales-funnel__bar"><span style="width:${T}%"></span></div>
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
    <div class="lead-panel__body">${Rs()}</div>
  </section>

  <div class="grid three-col" style="margin-top:12px">
    ${(s.branches||[]).slice(0,6).map(b=>ut(b.name,b.new_buyers||0,b.buyers||0,b.conv||0,b.sales||0,b.avg||0,b.buyers||0)).join("")||`<section class="card"><div class="empty"><strong>${t(e("sales.empty_branches"))}</strong></div></section>`}
  </div>`,X(),c("#salesRefreshBtn")&&(c("#salesRefreshBtn").onclick=()=>Ma()),requestAnimationFrame(()=>Ze())}function Rs(){const s=C&&C.beauticians&&C.beauticians.length?C.beauticians:[];return s.length?`<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${t(e("sales.col_rank"))}</th>
    <th>${t(e("sales.col_beautician"))}</th>
    <th class="is-num">${t(e("sales.col_sales"))}</th>
    <th class="is-num">${t(e("sales.col_customers"))}</th>
    <th class="is-num">${t(e("sales.col_orders"))}</th>
    <th class="is-num">${t(e("sales.col_avg"))}</th>
    <th class="is-num">${t(e("sales.col_leads"))}</th>
    <th class="is-num">${t(e("sales.col_conv"))}</th>
  </tr></thead><tbody>${s.map((a,n)=>{const i=String(a.name||"—"),l=t((i[0]||"?").toUpperCase());return`<tr>
      <td><span class="rank">${n+1}</span></td>
      <td><div class="person-cell person-cell--lead"><div class="mini-avatar" aria-hidden="true">${l}</div><div class="person-cell__text"><strong>${t(i)}</strong></div></div></td>
      <td class="is-num"><span class="lead-money">${L(a.sales||0)}</span></td>
      <td class="is-num">${v(a.buyers||0)}</td>
      <td class="is-num">${v(a.orders||a.order_count||0)}</td>
      <td class="is-num">${L(a.avg||0)}</td>
      <td class="is-num">${v(a.leads||0)}</td>
      <td class="is-num">${W(a.conv||0)}</td>
    </tr>`}).join("")}</tbody></table></div>`:`<div class="lead-empty"><strong>${t(e("sales.empty_beauticians"))}</strong></div>`}function Us(){const s=(_.userEditUrlTemplate||"").replace(/\/__ID__\/edit$/,"").replace(/\/__ID__$/,"")||"";B.innerHTML=`<div class="cus-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${t(e("customers.title"))}</h1>
        <div class="page-subtitle">${t(e("customers.subtitle"))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="cusRefresh">${t(e("customers.refresh"))}</button>
        ${_.canViewUser&&s?`<a class="btn primary" href="${t(s)}" target="_blank" rel="noopener">${t(e("customers.open_users"))}</a>`:""}
      </div>
    </div>
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
  </div>`;const a=c("#cusRefresh");a&&(a.onclick=()=>ye()),Is(),ye()}function Is(){const s=c("#cusSearch");s&&(s.oninput=()=>{clearTimeout(Ea),Ea=setTimeout(()=>{r.customerSearch=s.value.trim(),r.customerPage=1,ye()},320)});const a=c("#cusBranch");a&&(a.onchange=()=>{r.customerBranch=a.value,r.customerPage=1,ye()})}async function ye(){const s=_.customersUrl||"",a=c("#cusTableMount");if(!s){a&&(a.innerHTML=`<div class="pay-empty"><strong>${t(e("customers.load_error"))}</strong></div>`);return}ze=!0,tt(),st();const n=new URLSearchParams;r.customerSearch&&n.set("q",r.customerSearch),r.customerSegment&&r.customerSegment!=="all"&&n.set("segment",r.customerSegment);const i=r.customerBranch!=="all"?r.customerBranch:r.branch||"all";i&&i!=="all"&&n.set("branch",i),r.period&&n.set("period",r.period),n.set("page",String(r.customerPage||1)),n.set("per_page","25");try{const l=await fetch(`${s}?${n.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},credentials:"same-origin"});if(!l.ok)throw new Error("customers "+l.status);const o=await l.json();he=Array.isArray(o.data)?o.data:[],ga=Object.assign({total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0},o.meta&&o.meta.summary||{}),Se=Object.assign({segments:[],branches:[]},o.filters||{}),xe={current_page:o.meta&&o.meta.current_page||1,last_page:o.meta&&o.meta.last_page||1,total:o.meta&&o.meta.total||0}}catch(l){console.error(l),he=[],w(e("customers.load_error"))}finally{ze=!1,tt(),Fs(),Os(),st()}}function tt(){const s=ga,a=c("#cusPulse");a&&(a.textContent=xe.total>0?e("customers.pulse_busy",{count:v(xe.total)}):e("customers.pulse_clear"));const n=c("#cusMetrics");n&&(n.innerHTML=`
      <div class="pay-metric"><span>${t(e("customers.stat_total"))}</span><strong>${v(s.total)}</strong></div>
      <div class="pay-metric"><span>${t(e("customers.stat_buyers"))}</span><strong>${v(s.buyers)}</strong></div>
      <div class="pay-metric"><span>${t(e("customers.stat_new"))}</span><strong>${v(s.new_buyers)}</strong></div>
      <div class="pay-metric"><span>${t(e("customers.stat_sales"))}</span><strong>${H(s.period_sales)}</strong></div>
    `)}function Fs(){const s=c("#cusTabs");if(!s)return;const a=ga,n={all:a.total||0,buyers:a.buyers||0,new:a.new_buyers||0,returning:a.returning||0,leads:a.with_leads||0},i=Se.segments&&Se.segments.length?Se.segments:[{value:"all",label:e("customers.tab_all")},{value:"buyers",label:e("customers.tab_buyers")},{value:"new",label:e("customers.tab_new")},{value:"returning",label:e("customers.tab_returning")},{value:"leads",label:e("customers.tab_leads")}];s.innerHTML=i.map(l=>{const o=l.value,d=r.customerSegment===o?"active":"",u=n[o],p=u!==void 0?`<span class="pay-tab-count">${v(u)}</span>`:"";return`<button type="button" class="tab ${d}" role="tab" data-ctab="${t(o)}">${t(l.label)}${p}</button>`}).join(""),M("[data-ctab]",s).forEach(l=>{l.onclick=()=>{r.customerSegment=l.dataset.ctab,r.customerPage=1,ye()}})}function Os(){const s=c("#cusBranch");if(s){const a=r.customerBranch;s.innerHTML=`<option value="all">${t(e("customers.all_branches"))}</option>`+(Se.branches||[]).map(n=>`<option value="${n.id}" ${String(a)===String(n.id)?"selected":""}>${t(n.code?n.code+" · "+n.name:n.name)}</option>`).join("")}}function st(){const s=c("#cusTableMount"),a=c("#cusResultCount");if(a&&(a.textContent=e("customers.result_count",{count:v(xe.total||he.length)})),!s)return;if(ze){s.innerHTML=`<div class="pay-empty"><strong>${t(e("customers.loading"))}</strong></div>`;return}if(!he.length){s.innerHTML=`<div class="pay-empty"><strong>${t(e("customers.empty"))}</strong><span>${t(e("customers.empty_hint"))}</span></div>`;return}const n=he.map(l=>{const o=[`<span class="pay-chip ${l.segment==="new"||l.segment==="buyer"?"pay-chip--ok":"pay-chip--muted"}">${t(l.segment_label||"")}</span>`,l.has_lead?`<span class="pay-chip pay-chip--warn">${t(e("customers.chip_lead"))}</span>`:"",l.loyalty_tier?`<span class="pay-chip pay-chip--muted">${t(l.loyalty_tier)}</span>`:""].filter(Boolean).join(""),d=l.avatar_url?`<div class="mini-avatar mini-avatar--photo"><img src="${t(l.avatar_url)}" alt=""></div>`:`<div class="mini-avatar">${t(l.initial||"?")}</div>`;return`<tr>
      <td><span class="pay-id">${t(l.code||"CUS-"+l.id)}</span></td>
      <td><div class="person-cell">${d}<div><strong>${t(l.name||"")}</strong><small>${t(l.phone||l.email||"")}</small></div></div></td>
      <td>${t(l.branch||"—")}</td>
      <td><strong>${v(l.paid_orders_count)}</strong><div class="pay-method">${v(l.orders_count)} total</div></td>
      <td><div class="pay-amount">${H(l.paid_sales)}</div><div class="pay-method">${H(l.period_sales)} ${t(e("customers.detail_period").toLowerCase())}</div></td>
      <td>${t(l.last_order_label||"—")}</td>
      <td><div class="pay-chips">${o}</div></td>
      <td><button type="button" class="pay-review-btn" data-customer="${l.id}">${t(e("customers.view"))}</button></td>
    </tr>`}).join(""),i=G("cus",xe);s.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${t(e("customers.col_id"))}</th>
    <th>${t(e("customers.col_customer"))}</th>
    <th>${t(e("customers.col_branch"))}</th>
    <th>${t(e("customers.col_orders"))}</th>
    <th>${t(e("customers.col_sales"))}</th>
    <th>${t(e("customers.col_last"))}</th>
    <th>${t(e("customers.col_segment"))}</th>
    <th>${t(e("customers.col_action"))}</th>
  </tr></thead><tbody>${n}</tbody></table></div>${i}`,M("[data-customer]",s).forEach(l=>l.onclick=()=>Ws(Number(l.dataset.customer))),J("cus",l=>(r.customerPage=l,ye()))}function Ws(s){const a=he.find(u=>Number(u.id)===Number(s));if(!a)return;const n=U(_.userEditUrlTemplate,a.id),i=!!_.canViewUser,o=`<div class="pay-review">
    <div class="pay-review__hero">
      ${a.avatar_url?`<div class="pay-review__avatar" style="padding:0;overflow:hidden"><img src="${t(a.avatar_url)}" alt="" style="width:100%;height:100%;object-fit:cover"></div>`:`<div class="pay-review__avatar">${t(a.initial||"?")}</div>`}
      <div style="min-width:0;flex:1">
        <div class="pay-id">${t(a.code||"")}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${t(a.name||"")}</strong>
        <div class="pay-method">${t(a.phone||"—")} · ${t(a.email||"—")}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${P(a.segment_label)}${a.loyalty_tier?P(a.loyalty_tier):""}</div>
      </div>
      <div style="text-align:right"><div class="pay-amount">${H(a.paid_sales)}</div><div class="pay-method">${t(e("customers.detail_sales"))}</div></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${t(e("customers.detail_orders"))}</label><strong>${v(a.paid_orders_count)}</strong></div>
      <div class="pay-review__card"><label>${t(e("customers.detail_period"))}</label><strong>${H(a.period_sales)}</strong></div>
      <div class="pay-review__card"><label>${t(e("customers.detail_leads"))}</label><strong>${v(a.leads_count)}</strong></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${t(e("customers.col_branch"))}</label><strong>${t(a.branch_name||a.branch||"—")}</strong></div>
      <div class="pay-review__card"><label>${t(e("customers.col_last"))}</label><strong>${t(a.last_order_label||"—")}</strong></div>
      <div class="pay-review__card"><label>${t(e("customers.col_orders"))}</label><strong>${v(a.orders_count)}</strong></div>
    </div>
  </div>`,d=[i?`<a class="btn primary" href="${t(n)}" target="_blank" rel="noopener">${t(e("customers.open_profile"))}</a>`:"",`<button type="button" class="btn" data-jump="payments" data-customer-id="${t(String(a.id))}" data-customer-name="${t(a.name||"")}" data-customer-phone="${t(a.phone||"")}" data-customer-email="${t(a.email||"")}">${t(e("customers.open_payments"))}</button>`,`<button type="button" class="btn" data-action-drawer-close>${t(e("customers.close"))}</button>`].filter(Boolean).join("");se(e("customers.title"),a.code+" · "+a.name,o,d,"Customer"),setTimeout(()=>{M("#actionDrawerFoot [data-jump]").forEach(u=>u.onclick=()=>{if(R(),u.dataset.jump==="payments"&&u.dataset.customerId){os({id:u.dataset.customerId,name:u.dataset.customerName,phone:u.dataset.customerPhone,email:u.dataset.customerEmail});return}F(u.dataset.jump)})},0)}function jt(){const s=c("#donutChart");if(!s)return;De(s);const a=s.getContext("2d"),n=s.clientWidth,i=s.clientHeight,l=C&&C.status_mix||[],o=["#2563eb","#0ea5e9","#059669","#d97706","#e11d48","#6366f1"],d=l.map((b,$)=>({label:b.name,value:Number(b.value||0),color:o[$%o.length]})),u=d.reduce((b,$)=>b+$.value,0);if(!d.length||u<=0){a.clearRect(0,0,n,i),a.fillStyle="#94a3b8",a.font="12px Poppins,sans-serif",a.textAlign="center",a.fillText(e("overview.no_branch_data"),n/2,i/2);const b=c("#leadLegend");b&&(b.innerHTML="");return}let p=-Math.PI/2;const m=n/2,h=i/2,g=Math.min(n,i)/2-8;d.forEach(b=>{const $=p+b.value/u*Math.PI*2;a.beginPath(),a.moveTo(m,h),a.arc(m,h,g,p,$),a.closePath(),a.fillStyle=b.color,a.fill(),p=$}),a.beginPath(),a.arc(m,h,g*.58,0,Math.PI*2),a.fillStyle="#fff",a.fill();const f=c("#leadLegend");f&&(f.innerHTML=d.map(b=>`<div class="legend-row"><span class="dot" style="background:${b.color}"></span><span>${t(b.label)}</span><strong>${v(b.value)}</strong><span class="pct">${(b.value/u*100).toFixed(1)}%</span></div>`).join(""))}function Ze(){const s=c("#salesChart");if(!s)return;De(s);const a=s.getContext("2d"),n=s.clientWidth,i=s.clientHeight,l=C&&C.equity||{},o=Array.isArray(l.labels)?l.labels:[];let d;l.actual&&l.actual.length?(d=l.actual.map((y,k)=>k===0?Number(y):Math.max(0,Number(y)-Number(l.actual[k-1]))),d=d.map(y=>Math.round(y/1e3))):d=[];const u=Math.round((C&&C.targets&&C.targets.sales||0)/1e3),p={l:36,r:16,t:28,b:36},m=Math.max(u,...d,1)*1.15,h=y=>p.l+y/Math.max(d.length-1,1)*(n-p.l-p.r),g=y=>i-p.b-y/m*(i-p.t-p.b);a.strokeStyle="#e2e8f0",a.lineWidth=1;for(let y=0;y<4;y++){const k=p.t+(i-p.t-p.b)/3*y;a.beginPath(),a.moveTo(p.l,k),a.lineTo(n-p.r,k),a.stroke()}const f=g(u);a.setLineDash([5,5]),a.strokeStyle="#d97706",a.beginPath(),a.moveTo(p.l,f),a.lineTo(n-p.r,f),a.stroke(),a.setLineDash([]),a.fillStyle="#64748b",a.font="600 10px Poppins",a.textAlign="left",a.fillText("Target",p.l+4,f-6);const b="#1d4ed8",$="#0ea5e9",S=a.createLinearGradient(0,p.t,0,i-p.b);S.addColorStop(0,"rgba(37,99,235,.22)"),S.addColorStop(1,"rgba(14,165,233,.02)"),a.beginPath(),a.moveTo(h(0),i-p.b),d.forEach((y,k)=>a.lineTo(h(k),g(y))),a.lineTo(h(d.length-1),i-p.b),a.closePath(),a.fillStyle=S,a.fill(),a.beginPath(),d.forEach((y,k)=>k?a.lineTo(h(k),g(y)):a.moveTo(h(k),g(y))),a.strokeStyle=b,a.lineWidth=2.75,a.lineJoin="round",a.lineCap="round",a.stroke();const T=Math.max(1,Math.ceil(d.length/8));d.forEach((y,k)=>{if(k%T&&k!==d.length-1)return;const j=h(k),E=g(y);a.beginPath(),a.arc(j,E,4,0,Math.PI*2),a.fillStyle="#fff",a.fill(),a.lineWidth=2,a.strokeStyle=$,a.stroke(),a.fillStyle="#0f172a",a.font="700 10px Poppins",a.textAlign="center",a.fillText("RM"+y+"k",j,E-10),a.fillStyle="#64748b",a.font="600 10px Poppins",a.fillText(o[k]||"",j,i-12)})}function Vs(){if(!c("#branchChart"))return;const s=c("#branchChart"),a=s.getContext("2d"),n=s.clientWidth,i=s.clientHeight,l={l:45,r:20,t:20,b:35};De(s);const o=Array.isArray(C&&C.branches)?C.branches:[],d=o.map(g=>Number(g.new_buyer_share_pct)||Number(g.conv)||0),u=o.map(g=>String(g.name||"").slice(0,2).toUpperCase()||"—");if(!d.length)return;const p=Math.max(...d,50),m=Math.max(8,(n-l.l-l.r-70*d.length)/(d.length+1)),h=Math.min(70,(n-l.l-l.r-m*(d.length+1))/d.length);d.forEach((g,f)=>{const b=l.l+m+(h+m)*f,$=i-l.b-g/p*(i-l.t-l.b);a.fillStyle=[Z("--brand","#38bdf8"),Z("--rose","#0ea5e9"),Z("--navy","#2563eb")][f%3],a.fillRect(b,$,h,i-l.b-$),a.fillStyle=Z("--navy","#1d4ed8"),a.textAlign="center",a.font="700 14px Poppins",a.fillText(g+"%",b+h/2,$-8),a.font="13px Poppins",a.fillText(u[f],b+h/2,i-12)})}function De(s){const a=Math.max(1,window.devicePixelRatio||1),n=s.getBoundingClientRect();s.width=n.width*a,s.height=n.height*a,s.getContext("2d").setTransform(a,0,0,a,0,0)}const xa=["overview","leads","import","imports","followup","sales","payments","customers","wallet","checkin","clearance","beauticians","branches","audit"],Le=String(_.basePath||"").replace(/\/$/,"")||"/admin/leads/central";function nt(s){const a=xa.includes(s)?s:"overview";return a==="overview"?Le:Le+"/"+a}function Bt(s){const a=String(location.pathname).replace(/\/$/,"");if(a===Le)return"overview";if(a.startsWith(Le+"/")){const n=a.slice(Le.length+1).split("/")[0];return xa.includes(n)?n:"overview"}return"overview"}function it(){const s=new URLSearchParams(location.search);return s.set("branch",r.branch||"all"),r.period?s.set("period",r.period):s.delete("period"),"?"+s.toString()}function Ys(s,{replace:a=!1,silent:n=!1}={}){const i=nt(s)+it()+location.hash;!n&&(a||i!==location.pathname+location.search+location.hash)&&history[a?"replaceState":"pushState"]({view:s},"",i),M(".nav-item[data-view]").forEach(l=>l.href=nt(l.dataset.view)+it())}function Ks(){const s=new URLSearchParams(location.search),a=s.get("branch")||"all",n=s.get("period")||c("#periodScope").options[0]?.value||"",i=c("#branchScope"),l=c("#periodScope");i.value=a,r.branch=i.value||"all",i.value=r.branch,/^\d{4}-(0[1-9]|1[0-2])$/.test(n)&&![...l.options].some(o=>o.value===n)&&l.add(new Option(n,n)),l.value=n,r.period=l.value||l.options[0]?.value||"",l.value=r.period}function Et(){r.branch=c("#branchScope").value||"all",r.period=c("#periodScope").value||"",ge[r.view]&&(ge[r.view].page=1),F(r.view)}function F(s,a={}){const n=xa.includes(s)?s:"overview";V(),R(),window.IMMA_TRADE&&n!=="overview"&&IMMA_TRADE.dispose(),r.view=n,Ys(n,a),M(".nav-item[data-view]").forEach(i=>i.classList.toggle("active",i.dataset.view===n)),c("#crumbCurrent").textContent={overview:e("common.overview_crumb"),leads:e("nav.leads"),import:e("nav.import"),imports:e("nav.imports"),payments:e("nav.payments"),wallet:e("nav.wallet"),checkin:e("nav.checkin"),clearance:e("nav.clearance"),beauticians:e("nav.beauticians"),branches:e("nav.branches"),audit:e("nav.audit"),followup:e("nav.followup"),sales:e("nav.sales"),customers:e("nav.customers")}[n]||n,["overview","sales"].includes(n)&&ca!==JSON.stringify([r.branch,r.period])?(B.innerHTML=`<section class="card"><div class="pay-empty" role="status">${t(e("overview.loading"))}</div></section>`,Ma()):({overview:pt,leads:Gt,import:es,imports:ls,followup:Ns,sales:Pt,payments:Mt,customers:Us,wallet:Cs,checkin:hs,clearance:ks,beauticians:Es,branches:As,audit:qs}[n]||(()=>Hs(e("nav."+n),e("operations.not_ready"))))(),a.silent||window.scrollTo({top:0,behavior:"smooth"}),innerWidth<1e3&&c("#sidebar").classList.remove("open")}function X(){M("[data-jump]").forEach(s=>s.onclick=()=>{s.dataset.importTab&&(r.importTab=s.dataset.importTab),F(s.dataset.jump)})}let We=null,At="";function se(s,a,n,i,l=""){V(),R(),We=document.activeElement,At=document.body.style.overflow,c("#actionDrawerEyebrow").textContent=l,c("#actionDrawerTitle").textContent=s,c("#actionDrawerBody").innerHTML=`<p class="action-drawer__subtitle">${t(a)}</p>${n}`,c("#actionDrawerFoot").innerHTML=i,c("#actionDrawerClose").setAttribute("aria-label",e("wallet.close")),c("#actionDrawer").classList.add("show"),c("#actionDrawer").setAttribute("aria-hidden","false"),c("#actionDrawer").inert=!1,c("#actionDrawerBackdrop").classList.add("show"),c(".app-shell").inert=!0,document.body.style.overflow="hidden",c("#actionDrawerBody").scrollTop=0,c("#actionDrawerClose").focus({preventScroll:!0});const o=c("#saveLead");o&&(o.onclick=()=>Xt()),M("[data-action-drawer-close]",c("#actionDrawer")).forEach(d=>d.onclick=R)}function R(){const s=c("#actionDrawer");s.classList.contains("show")&&(Xe(),s.classList.remove("show"),s.setAttribute("aria-hidden","true"),s.inert=!0,c("#actionDrawerBackdrop").classList.remove("show"),c(".app-shell").inert=!1,document.body.style.overflow=At,We?.isConnected&&We.focus({preventScroll:!0}),We=null)}function V(){const s=c("#leadDrawer");s.classList.contains("show")&&(s.classList.remove("show"),s.inert=!0,c(".app-shell").inert=!1,c("#drawerBackdrop").classList.remove("show"),s.setAttribute("aria-hidden","true"),s.classList.contains("wallet-drawer")&&(s.classList.remove("wallet-drawer"),c("#walletDrawerFoot")?.remove()),document.body.style.overflow=La,Me?.isConnected&&Me.focus({preventScroll:!0}),Me=null)}function w(s){const a=c("#toast");a.textContent=s,a.classList.add("show"),clearTimeout(w._t),w._t=setTimeout(()=>a.classList.remove("show"),2300)}window.showToast=w;window.navigate=F;window.$=c;M(".nav-item[data-view]").forEach(s=>s.addEventListener("click",a=>{a.defaultPrevented||a.metaKey||a.ctrlKey||a.shiftKey||a.altKey||a.button!==0||(a.preventDefault(),s.dataset.view==="payments"&&(r.paymentCustomerId=null,r.paymentCustomerLabel=""),F(s.dataset.view))}));let Ce=null;function qt(){const s=c("#crumbCurrent").textContent,a=[c("#branchScope"),c("#periodScope"),...M('select,input[type="search"],input[type="date"]',B)].filter(i=>i&&!i.closest("[hidden]")).map(i=>{const l=i.tagName==="SELECT"?i.selectedOptions[0]?.textContent:i.value;if(!l?.trim())return"";const o=i.closest("label")?.querySelector(".lead-field__label")?.textContent||i.parentElement.querySelector("label")?.textContent||"";return o?o.trim()+": "+l.trim():l.trim()}).filter(Boolean);for(const i of[c("#branchScope"),c("#periodScope")]){const l=i.selectedOptions[0]?.textContent?.trim();l&&!a.includes(l)&&a.unshift(l)}M('.tab.active,[role="tab"][aria-selected="true"]',B).forEach(i=>a.push(i.textContent.trim())),c("#centralPrintHeader").innerHTML=`<div class="central-print-brand">${t(e("brand_subtitle"))}</div><h1>${t(s)}</h1><p>${t([...new Set(a)].join(" · "))}</p><p>${t(e("export_pdf.generated"))}: ${t(new Date().toLocaleString(_.locale==="ms"?"ms-MY":"en-MY"))}</p><small>${t(e("export_pdf.scope"))}</small>`;const n=["workspace","payments","customers","wallet","checkin","clearance"].map(i=>e(i+".col_action").toLowerCase());M("table",B).forEach(i=>{M("thead tr:last-child th",i).forEach((o,d)=>{n.includes(o.textContent.trim().toLowerCase())&&(o.classList.add("central-print-action"),M("tbody tr",i).forEach(u=>u.children[d]?.classList.add("central-print-action")))})}),Ce===null&&(Ce=document.title),document.title="Central - "+s+" - "+(r.period||"")}function zs(){Ce!==null&&(document.title=Ce,Ce=null),M(".central-print-action",B).forEach(s=>s.classList.remove("central-print-action"))}c("#exportCentralPdf").onclick=async()=>{if({leads:Ve,followup:Ye,payments:Ke,customers:ze,wallet:le,checkin:re,clearance:oe}[r.view]||c('[aria-busy="true"],[role="status"]',B)){w(e("export_pdf.loading"));return}const a=c("#exportCentralPdf");a.disabled=!0;try{document.fonts?.ready&&await document.fonts.ready,qt(),window.print()}finally{a.disabled=!1}};window.addEventListener("beforeprint",qt);window.addEventListener("afterprint",zs);c("#menuToggle").onclick=()=>c("#sidebar").classList.toggle("open");c("#drawerClose").onclick=V;c("#drawerBackdrop").onclick=V;c("#actionDrawerClose").onclick=R;c("#actionDrawerBackdrop").onclick=R;c("#branchScope").onchange=Et;c("#periodScope").onchange=Et;c("#globalSearch").addEventListener("keydown",s=>{s.key==="Enter"&&(F("leads"),r.leadSearch=s.target.value,r.leadPage=1)});window.addEventListener("resize",()=>{r.view==="overview"&&(ct(),dt(),jt(),Ze(),window.IMMA_TRADE&&IMMA_TRADE.resize()),r.view==="sales"&&Ze(),r.view==="branches"&&Vs()});window.addEventListener("popstate",()=>{Ks(),F(Bt(),{silent:!0})});F(_.initialView||Bt(),{replace:!0});
