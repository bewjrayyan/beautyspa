
function themeColor(name, fallback){
  const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return v || fallback;
}

function t(path, replace={}){
  const parts=String(path).split('.');
  let cur=(window.IMMA_CENTRAL&&window.IMMA_CENTRAL.i18n)||{};
  for(const p of parts){
    if(cur && typeof cur==='object' && p in cur) cur=cur[p];
    else { cur=null; break; }
  }
  let out=(typeof cur==='string')?cur:path;
  Object.keys(replace||{}).forEach(k=>{
    out=out.replace(new RegExp(':'+k,'g'), String(replace[k]));
  });
  return out;
}

const $ = (s, p=document) => {
  const root = typeof p === 'string' ? document.querySelector(p) : (p || document);
  return root ? root.querySelector(s) : null;
};
const $$ = (s, p=document) => {
  const root = typeof p === 'string' ? document.querySelector(p) : (p || document);
  return root ? [...root.querySelectorAll(s)] : [];
};

const data = {
  leads: []
};

const boot = window.IMMA_CENTRAL || {};
let state = {
  view:'overview',
  branch: String(($('#branchScope')&&$('#branchScope').value)||'all'),
  period: String(($('#periodScope')&&$('#periodScope').value)||(boot.metrics&&boot.metrics.period&&boot.metrics.period.key)||''),
  leadSearch:'', leadStatus:'all', leadBeautician:'all', leadBranch:'all', leadPage:1, leadPerPage:10,
  leadMonth: (function(){
    const d=new Date();
    return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0');
  })(),
  leadCalYear: null,
  followBucket:'all', followSearch:'', followPage:1,
  importTab:'paste', paymentTab:'queue', paymentSearch:'', paymentCustomerId:null, paymentCustomerLabel:'', paymentBeautician:'all', paymentBranch:'all', paymentPage:1,
  customerSegment:'all', customerSearch:'', customerBranch:'all', customerPage:1,
  walletSegment:'all', walletSearch:'', walletTier:'all', walletPage:1, walletCustomerId:null,
  checkinStatus:'live', checkinSearch:'', checkinBranch:'all', checkinBeautician:'all', checkinPage:1,
  checkinDate:boot.today || new Date().toLocaleDateString('en-CA'), checkinScope:'day',
  clearanceState:'waiting', clearanceSearch:'', clearanceBranch:'all', clearanceBeautician:'all', clearancePage:1
};
const root = $('#viewRoot');

let liveMetrics = boot.metrics || null;
let metricsScope = liveMetrics ? JSON.stringify([state.branch,state.period]) : null;
let metricsRequest = 0;
let liveLeads = [];
let liveLeadSummary = {raw:0,unique:0,duplicates:0,existing:0,converted:0,conversion_pct:0,all_time:{raw:0,unique:0,duplicates:0,existing:0,converted:0,conversion_pct:0}};
let liveLeadFilters = {statuses:[],sources:[],beauticians:[],branches:[],months:[]};
let leadsLoading = false;
let leadSearchTimer = null;
let editingLeadId = null;
let selectedLeadIds = new Set();
let leadBulkAction = '';
let leadBulkValue = '';
let liveFollowUps = [];
let liveFollowSummary = {queue:0,overdue:0,due_today:0,no_response:0,lost:0};
let liveFollowFilters = {buckets:[],beauticians:[],branches:[],statuses:[]};
let followLoading = false;
let followSearchTimer = null;
let livePayments = [];
let livePaymentSummary = {pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0};
let livePaymentFilters = {statuses:[],beauticians:[],branches:[]};
let paymentsLoading = false;
let paymentSearchTimer = null;
let paymentMeta = {current_page:1,last_page:1,total:0};
let liveCustomers = [];
let liveCustomerSummary = {total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0};
let liveCustomerFilters = {segments:[],branches:[]};
let customersLoading = false;
let customerSearchTimer = null;
let customerMeta = {current_page:1,last_page:1,total:0};
let liveWallets = [];
let liveWalletSummary = {members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0};
let liveWalletFilters = {segments:[],tiers:[]};
let walletsLoading = false;
let walletRequest = 0, walletLoadError = false;
let walletSearchTimer = null;
let walletMeta = {current_page:1,last_page:1,total:0};
let liveCheckins = [];
let liveCheckinSummary = {live:0,scheduled:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0};
let liveCheckinFilters = {statuses:[],beauticians:[],branches:[]};
let checkinsLoading = false;
let checkinRequest = 0, checkinLoadError = false;
let checkinSearchTimer = null;
let checkinMeta = {current_page:1,last_page:1,total:0};
let checkinScannerStream = null, checkinScannerFrame = 0;
let liveClearances = [];
let liveClearanceSummary = {waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0};
let liveClearanceFilters = {states:[],beauticians:[],branches:[]};
let clearancesLoading = false;
let clearanceRequest = 0, clearanceLoadError = false;
let clearanceSearchTimer = null;
let clearanceMeta = {current_page:1,last_page:1,total:0};

function leadUrl(template, id){
  return String(template||'').replace('__ID__', String(id));
}
function apiHeaders(json=true){
  const h = {
    'Accept':'application/json',
    'X-Requested-With':'XMLHttpRequest',
    'X-CSRF-TOKEN': (boot.csrf || document.querySelector('meta[name="csrf-token"]')?.content || '')
  };
  if(json) h['Content-Type']='application/json';
  return h;
}
async function refreshLeads(){
  const url = boot.leadsUrl || '';
  if(!url){ showToast(t('workspace.load_error')); return; }
  const qs = new URLSearchParams();
  if(state.leadSearch) qs.set('q', state.leadSearch);
  if(state.leadStatus && state.leadStatus!=='all') qs.set('status', state.leadStatus);
  const branch = state.leadBranch!=='all' ? state.leadBranch : (state.branch||'all');
  if(branch && branch!=='all') qs.set('branch', branch);
  if(state.leadBeautician && state.leadBeautician!=='all') qs.set('beautician', state.leadBeautician);
  if(state.leadMonth && state.leadMonth!=='all') qs.set('month', state.leadMonth);
  qs.set('page', String(state.leadPage||1));
  qs.set('per_page', String(state.leadPerPage||10));
  selectedLeadIds.clear();
  leadBulkAction='';
  leadBulkValue='';
  renderLeadBulkBar();
  leadsLoading = true;
  renderLeadTable();
  try{
    const res = await fetch(`${url}?${qs.toString()}`, {
      headers: apiHeaders(false),
      credentials: 'same-origin'
    });
    if(!res.ok) throw new Error('leads '+res.status);
    const json = await res.json();
    leadPageMeta=json.meta||{};
    if([10,50,100,200].includes(Number(leadPageMeta.per_page))){
      state.leadPerPage=Number(leadPageMeta.per_page);
    }
    liveLeads = Array.isArray(json.data) ? json.data : [];
    liveLeadSummary = (json.meta && json.meta.summary) || liveLeadSummary;
    liveLeadFilters = json.filters || liveLeadFilters;
    data.leads = liveLeads;
  }catch(err){
    console.error(err);
    showToast(t('workspace.load_error'));
  }finally{
    leadsLoading = false;
    if(state.view==='leads'){
      renderLeadKpis();
      renderLeadTable();
      bindLeadFilters();
      updateLeadCalendarUi();
    }
  }
}

function fmtInt(n){return Number(n||0).toLocaleString('en-MY')}
function fmtMoney(n){return 'RM'+Number(n||0).toLocaleString('en-MY',{maximumFractionDigits:0})}
function fmtPct(n){return Number(n||0).toLocaleString('en-MY',{maximumFractionDigits:1})+'%'}
function escapeHtml(s){
  return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function deltaTrend(pct, suffix=''){
  const v=Number(pct||0);
  const abs=Math.abs(v).toFixed(1)+suffix;
  if(v>0) return `↑ +${abs} ${t('overview.vs_prev_period')}`;
  if(v<0) return `↓ -${abs} ${t('overview.vs_prev_period')}`;
  return `→ 0${suffix} ${t('overview.vs_prev_period')}`;
}
function targetHint(label, value){return `${t('overview.target')}: ${value}`}

async function refreshMetrics(){
  const url = boot.metricsUrl || '';
  const requestId = ++metricsRequest;
  const branch = state.branch || 'all';
  const period = state.period || '';
  const scope = JSON.stringify([branch,period]);
  const qs = new URLSearchParams({branch,period});
  try{
    if(!url) throw new Error('Missing metrics endpoint');
    const res = await fetch(`${url}?${qs.toString()}`,{headers:apiHeaders(false),credentials:'same-origin'});
    if(!res.ok) throw new Error('metrics '+res.status);
    const json = await res.json();
    if(requestId!==metricsRequest || scope!==JSON.stringify([state.branch,state.period])) return;
    if(!json.metrics) throw new Error('Missing metrics payload');
    liveMetrics = json.metrics;
    metricsScope = scope;
    boot.metrics = liveMetrics;
    renderNotifications();
    if(state.view==='overview') overview();
    if(state.view==='sales') sales();
  }catch(err){
    if(requestId!==metricsRequest || scope!==JSON.stringify([state.branch,state.period])) return;
    if(['overview','sales'].includes(state.view) && metricsScope!==scope){
      root.innerHTML=`<section class="card"><div class="pay-empty" role="alert"><strong>${escapeHtml(t('overview.metrics_error'))}</strong><button type="button" class="btn" id="retryMetrics">${escapeHtml(t('reporting.refresh'))}</button></div></section>`;
      $('#retryMetrics').onclick=()=>refreshMetrics();
    }else showToast(t('overview.metrics_error'));
  }
}


function centralPager(key,meta={}){
  const last=Math.max(1,Number(meta.last_page)||1),current=Math.max(1,Math.min(last,Number(meta.current_page)||1));
  const pages=last<=5?Array.from({length:last},(_,i)=>i+1):[...new Set([1,current-1,current,current+1,last].filter(n=>n>=1&&n<=last))].sort((a,b)=>a-b);
  const button=(page,label,extra='')=>`<button type="button" class="central-pagination__button" data-page="${page}" ${extra}>${label}</button>`;
  let numbers='',previous=0;
  pages.forEach(page=>{
    if(previous && page-previous>1) numbers+='<span class="central-pagination__ellipsis" aria-hidden="true">…</span>';
    numbers+=button(page,String(page),`aria-label="${escapeHtml(t('pagination.page',{page}))}" ${page===current?'aria-current="page" disabled':''}`);
    previous=page;
  });
  return `<nav class="central-pagination" data-pager="${key}" aria-label="${escapeHtml(t('pagination.label'))}">
    ${button(current-1,'‹',`id="${key}Prev" aria-label="${escapeHtml(t('operations.previous'))}" ${current<=1?'disabled':''}`)}
    ${numbers}
    ${button(current+1,'›',`id="${key}Next" aria-label="${escapeHtml(t('operations.next'))}" ${current>=last?'disabled':''}`)}
  </nav>`;
}
function bindCentralPager(key,onChange){
  const nav=$(`[data-pager="${key}"]`);if(!nav)return;
  $$('button[data-page]',nav).forEach(button=>button.onclick=async()=>{
    if(button.disabled)return;
    const enabled=$$('button:not(:disabled)',nav);
    enabled.forEach(el=>el.disabled=true);
    nav.setAttribute('aria-busy','true');
    try{await onChange(Number(button.dataset.page));}
    finally{if(nav.isConnected){enabled.forEach(el=>el.disabled=false);nav.removeAttribute('aria-busy');}}
  });
}
let leadPageMeta={},followPageMeta={},importPage=1;

function money(n){return 'RM'+Number(n).toLocaleString('en-MY')}
function statusBadge(status){
  const raw=String(status??'');
  const s=raw.toUpperCase();
  let c='gray';
  if(s.includes('VERIFIED')||s.includes('PAID')||s==='CONVERTED'||s==='COMPLETED'||s.includes('BANK CHECKED')||s.includes('PROOF')) c='success';
  else if(s.includes('FOLLOW')||s.includes('PENDING')||s.includes('REVIEW')||s==='BOOKING'||s==='CLAIMED'||s.includes('PROCESSING')||s.includes('DECLARED')) c='warning';
  else if(s.includes('HOLD')||s.includes('LOST')||s.includes('NO RESPONSE')||s.includes('CANCEL')||s.includes('REFUND')) c='danger';
  else if(s==='NEW') c='blue';
  return `<span class="badge ${c}"><span class="status-dot"></span>${escapeHtml(raw)}</span>`;
}
function pageHead(title,sub,actions=''){
  return `<div class="page-head"><div><h1 class="page-title">${title}</h1><div class="page-subtitle">${sub}</div></div><div class="page-actions">${actions}</div></div>`
}
function kpi(icon,label,value,trend,target,color='blue',pct=null,full='',sparkId=''){
  const tip=escapeHtml(full||label);
  const safeLabel=escapeHtml(label);
  const safeValue=escapeHtml(value);
  const safeTrend=escapeHtml(trend);
  const safeTarget=escapeHtml(target);
  const safeSpark=escapeHtml(sparkId);
  const up=/^[↑+]/.test(String(trend).trim()) || /above|\+|up/i.test(String(trend));
  const down=/^[↓-]/.test(String(trend).trim()) || /below|down/i.test(String(trend));
  const tone=down?'down':(up?'up':'flat');
  return `<div class="kpi-card kpi-card--${color}">
    <div class="kpi-card__head">
      <div class="kpi-card__icon" aria-hidden="true">${icon}</div>
      <div class="kpi-label" title="${tip}">${safeLabel}</div>
    </div>
    <div class="kpi-value mono">${safeValue}</div>
    <div class="kpi-card__foot">
      <span class="kpi-trend kpi-trend--${tone}">${safeTrend}</span>
      <span class="kpi-target">${safeTarget}</span>
    </div>
    ${sparkId?`<div class="kpi-spark" id="${safeSpark}"></div>`:''}
    ${pct!==null?`<div class="progress kpi-card__bar"><span style="width:${Math.min(100,pct)}%"></span></div>`:''}
  </div>`;
}
function leadKpiIcon(kind){
  const icons={
    database:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v6c0 1.7 3.1 3 7 3s7-1.3 7-3V5"/><path d="M5 11v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"/></svg>',
    new:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="4"/><path d="M3 20c.7-4 2.7-6 6-6 2.1 0 3.7.8 4.8 2.3M18 8v6M15 11h6"/></svg>',
    repeated:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7h-9a5 5 0 0 0-5 5v1"/><path d="m17 4 3 3-3 3M4 17h9a5 5 0 0 0 5-5v-1"/><path d="m7 20-3-3 3-3"/></svg>',
    customers:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c.5-4 2.5-6 6-6s5.5 2 6 6M16 5.5a3 3 0 0 1 0 5.8M17 14c2.4.5 3.7 2.4 4 5"/></svg>',
    conversion:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 18 10 12l4 3 6-8"/><path d="M15 7h5v5"/><circle cx="6" cy="6" r="2"/></svg>',
    queue:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v12H4z"/><path d="M4 13h4l2 3h4l2-3h4"/></svg>',
    overdue:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M12 7v5l3 2M12 4V2M6.4 5.1 5 3.7"/></svg>',
    today:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16M8.5 15l2.2 2.2 4.8-5"/></svg>',
    no_response:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 18h10a4 4 0 0 0 4-4V8a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v9l2-2z"/><path d="M8 10h.01M12 10h.01M16 10h.01"/></svg>',
    lost:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 11a8 8 0 1 1-2.3-5.7"/><path d="M20 4v7h-7"/><path d="m8 12 2.5 2.5L16 9"/></svg>',
    revenue:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/></svg>',
    orders:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18H6z"/><path d="M9 3v4h6V3M9 12h6M9 16h4"/></svg>',
    average:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19 10 5l6 14M6 14h8M19 5v14M17 9h4"/></svg>',
    target:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="m15 9 5-5"/></svg>'
  };
  return icons[kind]||icons.database;
}
function leadKpi(icon,label,value,unit,badge,detail,color='blue'){
  return `<div class="kpi-card kpi-card--${color} lead-kpi-card lead-kpi-card--${color}">
    <div class="lead-kpi-card__glow" aria-hidden="true"></div>
    <div class="kpi-card__head">
      <div class="kpi-card__icon" aria-hidden="true">${icon}</div>
      <div class="kpi-label">${escapeHtml(label)}</div>
    </div>
    <div class="lead-kpi__value">
      <strong>${escapeHtml(value)}</strong>
      <span>${escapeHtml(unit)}</span>
    </div>
    <div class="kpi-card__foot">
      <span class="kpi-trend kpi-trend--flat">${escapeHtml(badge)}</span>
      <span class="kpi-target lead-kpi__detail">${escapeHtml(detail)}</span>
    </div>
  </div>`;
}

function tradePane(titleKey, subKey, chartId, size=''){
  const sizeCls=size?` trade-pane__chart--${size}`:'';
  return `<section class="trade-pane">
    <div class="trade-pane__head">
      <div>
        <div class="trade-pane__eyebrow">${t('trade.section')}</div>
        <div class="trade-pane__title">${t(titleKey)}</div>
        <div class="trade-pane__sub">${t(subKey)}</div>
      </div>
    </div>
    <div class="trade-pane__chart${sizeCls}" id="${chartId}"></div>
  </section>`;
}


function formatDailyDate(d=new Date()){
  const months=['JAN','FEB','MAC','APR','MEI','JUN','JUL','OGOS','SEPT','OKT','NOV','DIS'];
  const dd=String(d.getDate()).padStart(2,'0');
  return `${dd} ${months[d.getMonth()]} ${d.getFullYear()}`;
}
function dailyLeadHero(){
  const m = liveMetrics || {};
  const k = m.kpis || {};
  const targets = m.targets || {};
  const beauticians = Array.isArray(m.beauticians) ? m.beauticians : [];
  const leads = Number(k.new_buyers || 0);
  const target = Number(targets.leads || 0);
  const pct = target > 0 ? Math.round((leads / target) * 1000) / 10 : 0;
  const isPeriod = !!(m.period && m.period.key);
  const dateLabel = m.period && m.period.label ? escapeHtml(m.period.label) : formatDailyDate();
  const slogan = m.slogan || t('daily.slogan');
  const quote = m.quote || t('daily.quote');
  return `<section class="daily-hero">
    <div class="daily-hero__top">
      <div class="daily-hero__copy">
        <div class="daily-hero__eyebrow">${t('daily.eyebrow')}</div>
        <h2 class="daily-hero__title">${t('daily.title')}</h2>
        <div class="daily-hero__meta">
          <span class="daily-hero__date">${dateLabel}</span>
          <span class="daily-hero__pill">${t('daily.live')}</span>
        </div>
        <p class="daily-hero__quote">${escapeHtml(slogan)} -- "${escapeHtml(quote)}"</p>
      </div>
    </div>
    <div class="daily-hero__stats">
      <div class="daily-stat daily-stat--primary">
        <div class="daily-stat__label">${isPeriod ? t('overview.kpi_unique_leads') : t('daily.leads_today')}</div>
        <div class="daily-stat__value">${fmtInt(leads)}</div>
        <div class="daily-stat__sub">${t('daily.beauticians_active',{count:beauticians.length || 0})}</div>
      </div>
      <div class="daily-stat">
        <div class="daily-stat__label">${t('daily.month_progress')}</div>
        <div class="daily-stat__value daily-stat__value--sm">${fmtInt(leads)} / ${fmtInt(target)}</div>
        <div class="daily-stat__sub">${t('daily.of_monthly_target',{pct})}</div>
        <div class="progress daily-stat__bar"><span style="width:${Math.min(100,pct)}%"></span></div>
      </div>
    </div>
  </section>`;
}
const AVATAR_TONES=[
  ['#1d4ed8','#60a5fa'],
  ['#9a3412','#f59e0b'],
  ['#065f46','#34d399'],
  ['#6d28d9','#a78bfa'],
  ['#be123c','#fb7185'],
  ['#0e7490','#22d3ee'],
  ['#a16207','#facc15'],
];
function beauTone(name){
  const s=String(name||'');
  let h=0;
  for(let i=0;i<s.length;i++) h=(h*31+s.charCodeAt(i))>>>0;
  const p=AVATAR_TONES[Math.abs(h)%AVATAR_TONES.length];
  return `linear-gradient(145deg, ${p[0]}, ${p[1]})`;
}
function beauInitials(name){
  const parts=String(name||'').trim().split(/\s+/).filter(Boolean);
  return ((parts[0]?.[0]||'')+(parts[1]?.[0]||'')).toUpperCase();
}
function dailyLeadBoard(){
  const m = liveMetrics || {};
  const periodKey = !!(m.period && m.period.key);
  const rows = Array.isArray(m.beauticians) ? m.beauticians : [];
  const beauTotal = Array.isArray(m.beauticians) ? m.beauticians.length : 0;
  const beauCount = Math.max(1, Number(m.beautician_count) || beauTotal || 1);
  const perTarget = Number((m.targets&&m.targets.beautician_leads)||0) || 112;
  const ranked=[...rows].sort((a,b)=>b.leads-a.leads);
  const dateLabel = m.period && m.period.label ? escapeHtml(m.period.label) : t('common.this_month');
  const totalLeads = ranked.reduce((sum,r)=>sum+(Number(r.leads)||0),0);
  const medallions=['🥇','🥈','🥉'];
  const rowsHtml=ranked.map((r,i)=>{
    const leads=Number(r.leads)||0;
    const target=Number(r.target) || perTarget;
    const pct=target>0?Math.round((leads/target)*100):0;
    const share=totalLeads>0?Math.max(4,Math.round((leads/totalLeads)*100)):0;
    const tone=i===0?'gold':i===1?'silver':i===2?'bronze':'';
    const achStatus=pct>=100?'is-hit':pct>=75?'is-close':'is-low';
    const name=escapeHtml(String(r.name||''));
    const rankInner=i<3
      ? `<span class="rank rank--medal rank--${tone}" title="${t('daily.rank_title',{n:i+1})}">${medallions[i]}</span>`
      : `<span class="rank">${i+1}</span>`;
    return `<tr class="daily-row${tone?` daily-row--${tone}`:''}">
      <td class="daily-rank-cell">${rankInner}</td>
      <td>
        <div class="person-cell">
          <div class="mini-avatar leader-avatar${tone?` mini-avatar--${tone}`:''}" style="background:${beauTone(r.name)}">${beauInitials(r.name)}</div>
          <div class="person-cell__text">
            <strong>${name}${i===0?` <span class="leader-tag">${t('daily.leader')}</span>`:''}</strong>
            <small>${t('daily.target_month',{count:target})}<i class="daily-dot"></i>${dateLabel}</small>
          </div>
        </div>
      </td>
      <td class="daily-num-cell"><strong class="daily-num" title="${leads.toLocaleString()} ${t('daily.leads')}">${leads.toLocaleString()}</strong></td>
      <td>
        <div class="share-cell">
          <div class="share-cell__track"><span style="width:${Math.min(100,share)}%"></span></div>
          <strong>${share}%</strong>
        </div>
      </td>
      <td><span class="daily-target">${target}</span></td>
      <td>
        <div class="achieve">
          <strong class="achieve__pct ${achStatus}">${pct}%</strong>
          <div class="progress achieve__bar"><span class="achieve__fill ${achStatus}" style="width:${Math.min(100,pct)}%"></span></div>
        </div>
      </td>
    </tr>`;
  }).join('') || `<tr><td colspan="6"><div class="empty"><strong>${t('overview.no_beautician_data')}</strong></div></td></tr>`;
  return `<div class="grid daily-split">
    <section class="card daily-board">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${t(periodKey?'daily.rank_eyebrow':'daily.ranking')}</div>
          <div class="daily-panel__title">${t('daily.leaderboard')}</div>
          <div class="daily-panel__sub">${t(periodKey?'daily.rank_sub_month':'daily.rank_sub',{date:dateLabel})}</div>
        </div>
        <button class="btn small soft" data-jump="beauticians">${t('common.full_view')}</button>
      </div>
      <div class="table-wrap daily-board__table">
        <table class="data-table daily-table">
          <thead>
            <tr>
              <th>${t('daily.col_rank')}</th>
              <th>${t('daily.col_beautician')}</th>
              <th>${t(periodKey?'daily.col_leads':'daily.col_today')}</th>
              <th>${t('daily.col_share')}</th>
              <th>${t('daily.col_target')}</th>
              <th>${t('daily.col_ach')}</th>
            </tr>
          </thead>
          <tbody>${rowsHtml}</tbody>
        </table>
      </div>
      <div class="daily-motto">${t('daily.motto')}</div>
    </section>
    <section class="card daily-charts">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${t('daily.distribution')}</div>
          <div class="daily-panel__title">${t('daily.by_beautician')}</div>
          <div class="daily-panel__sub">${t(periodKey?'daily.leads_share_period':'daily.daily_share', periodKey?{period:dateLabel}:{})}</div>
        </div>
      </div>
      <div class="chart-wrap daily-charts__bar"><canvas id="dailyLeadChart"></canvas></div>
      <div class="daily-charts__divider">
        <div class="daily-panel__eyebrow">${t('daily.trend')}</div>
        <div class="daily-panel__title daily-panel__title--sm">${t(periodKey?'daily.trend_period':'daily.trend_7d', periodKey?{period:dateLabel}:{})}</div>
      </div>
      <div class="chart-wrap daily-charts__trend"><canvas id="dailyTrendChart"></canvas></div>
    </section>
  </div>`;
}
function roundRectPath(ctx,x,y,w,h,r){
  const rr=Math.min(r,w/2,h/2);
  ctx.beginPath();
  ctx.moveTo(x+rr,y);
  ctx.arcTo(x+w,y,x+w,y+h,rr);
  ctx.arcTo(x+w,y+h,x,y+h,rr);
  ctx.arcTo(x,y+h,x,y,rr);
  ctx.arcTo(x,y,x+w,y,rr);
  ctx.closePath();
}
function drawDailyLeadChart(){
  const c=$('#dailyLeadChart'); if(!c) return;
  fitCanvas(c);
  const ctx=c.getContext('2d');
  const m = liveMetrics || {};
  const live = Array.isArray(m.beauticians) ? m.beauticians : [];
  const rows = live.length ? live.map(b => ({name: String(b.name || ''), leads: Number(b.leads || 0)})) : [];
  const ranked=[...rows].sort((a,b)=>b.leads-a.leads);
  const labels=ranked.map(r=>r.name);
  const vals=ranked.map(r=>r.leads);
  const w=c.clientWidth,h=c.clientHeight,p={l:28,r:10,t:28,b:42};
  const max=Math.max(...vals,1)*1.2;
  const gap=Math.max(6, Math.min(12, w/60));
  const barW=Math.max(14,(w-p.l-p.r-(gap*(vals.length-1)))/vals.length);
  const navy=themeColor('--navy','#1d4ed8');
  const sky=themeColor('--rose','#0ea5e9');
  // soft panel
  ctx.fillStyle='#f8fafc';
  roundRectPath(ctx,0,0,w,h,12); ctx.fill();
  // grid
  ctx.strokeStyle='#e2e8f0'; ctx.lineWidth=1;
  for(let i=0;i<4;i++){
    const yy=p.t+i*((h-p.t-p.b)/3);
    ctx.beginPath(); ctx.moveTo(p.l,yy); ctx.lineTo(w-p.r,yy); ctx.stroke();
  }
  const tones=[
    ['#1d4ed8','#38bdf8'],
    ['#2563eb','#7dd3fc'],
    ['#0284c7','#67e8f9'],
  ];
  vals.forEach((v,i)=>{
    const x=p.l+i*(barW+gap);
    const bh=Math.max(4,(v/max)*(h-p.t-p.b));
    const y=h-p.b-bh;
    const [c0,c1]=tones[Math.min(i,2)] || [navy,sky];
    const grad=ctx.createLinearGradient(0,y,0,h-p.b);
    grad.addColorStop(0, i<3?c1:sky);
    grad.addColorStop(1, i<3?c0:'#93c5fd');
    ctx.fillStyle=grad;
    roundRectPath(ctx,x,y,barW,bh,8); ctx.fill();
    // value chip
    ctx.fillStyle='#0f172a';
    ctx.font='700 12px Poppins'; ctx.textAlign='center';
    ctx.fillText(String(v), x+barW/2, y-8);
    // label
    const short=labels[i].length>7?labels[i].slice(0,6)+'…':labels[i];
    ctx.fillStyle='#475569';
    ctx.font='600 10px Poppins';
    ctx.fillText(short, x+barW/2, h-14);
  });
}
function drawDailyTrendChart(){
  const c=$('#dailyTrendChart'); if(!c) return;
  fitCanvas(c);
  const ctx=c.getContext('2d');
  const m = liveMetrics || {};
  const trendData = (m.leads_trend && Array.isArray(m.leads_trend.actual)) ? m.leads_trend : null;
  const vals = trendData ? [...trendData.actual] : [];
  const labels = trendData && Array.isArray(trendData.labels) ? trendData.labels : Array.from({length:vals.length},(_,i)=>i===vals.length-1?'Today':'D-'+(vals.length-1-i));
  const w=c.clientWidth,h=c.clientHeight,p={l:28,r:14,t:22,b:28};
  const max=Math.max(...vals,1)*1.15;
  const x=i=>p.l+i*((w-p.l-p.r)/Math.max(vals.length-1,1));
  const y=v=>h-p.b-((v/max)*(h-p.t-p.b));
  const navy=themeColor('--navy','#1d4ed8');
  const sky=themeColor('--rose','#0ea5e9');
  ctx.fillStyle='#f8fafc';
  roundRectPath(ctx,0,0,w,h,12); ctx.fill();
  ctx.strokeStyle='#e2e8f0'; ctx.lineWidth=1;
  for(let i=0;i<3;i++){
    const yy=p.t+i*((h-p.t-p.b)/2);
    ctx.beginPath(); ctx.moveTo(p.l,yy); ctx.lineTo(w-p.r,yy); ctx.stroke();
  }
  const area=ctx.createLinearGradient(0,p.t,0,h-p.b);
  area.addColorStop(0,'rgba(14,165,233,.28)');
  area.addColorStop(1,'rgba(37,99,235,.02)');
  ctx.beginPath(); ctx.moveTo(x(0),h-p.b);
  vals.forEach((v,i)=>ctx.lineTo(x(i),y(v)));
  ctx.lineTo(x(vals.length-1),h-p.b); ctx.closePath();
  ctx.fillStyle=area; ctx.fill();
  // stroke
  ctx.beginPath();
  vals.forEach((v,i)=> i?ctx.lineTo(x(i),y(v)):ctx.moveTo(x(i),y(v)));
  ctx.strokeStyle=navy; ctx.lineWidth=2.75; ctx.lineJoin='round'; ctx.lineCap='round'; ctx.stroke();
  vals.forEach((v,i)=>{
    const px=x(i), py=y(v);
    ctx.beginPath(); ctx.arc(px,py,5,0,Math.PI*2);
    ctx.fillStyle='#fff'; ctx.fill();
    ctx.lineWidth=2.5; ctx.strokeStyle=sky; ctx.stroke();
    ctx.beginPath(); ctx.arc(px,py,2.2,0,Math.PI*2);
    ctx.fillStyle=navy; ctx.fill();
    ctx.fillStyle='#0f172a'; ctx.font='700 10px Poppins'; ctx.textAlign='center';
    ctx.fillText(String(v), px, py-10);
    ctx.fillStyle='#64748b'; ctx.font='600 10px Poppins';
    ctx.fillText(labels[i], px, h-10);
  });
}

function companyTargetBoard(){
  const ct='company_target';
  const m = liveMetrics || {};
  const tg = m.targets || {};
  const beauCount = Math.max(1, Number(m.beautician_count) || 1);
  const leadGoal = Number(tg.leads) || 0;
  const convGoal = Number(tg.conv_pct) || 0;
  const buyerGoal = Number(tg.buyers) || 0;
  const avgGoal = Number(tg.avg_sale) || 0;
  const salesGoal = Number(tg.sales) || 0;
  const perLeads = Number(tg.beautician_leads) || 112;
  const perBuyers = Math.round((perLeads * convGoal) / 100);
  const perSales = Math.round(perBuyers * avgGoal);
  const compactMoney=(n)=>{
    const v=Number(n||0);
    if(Math.abs(v)>=1000000) return 'RM'+(v/1000000).toFixed(1).replace(/\.0$/,'')+'M';
    if(Math.abs(v)>=1000) return 'RM'+(v/1000).toFixed(1).replace(/\.0$/,'')+'k';
    return fmtMoney(v);
  };
  const fmtLeads = fmtInt(leadGoal);
  const fmtConv = fmtPct(convGoal);
  const fmtBuyers = fmtInt(buyerGoal);
  const fmtAvg = fmtMoney(avgGoal);
  const fmtSales = compactMoney(salesGoal);
  return `<section class="company-target">
    <div class="company-target__hero">
      <div class="company-target__hero-copy">
        <div class="company-target__eyebrow">${t(ct+'.eyebrow')}</div>
        <h2 class="company-target__title">${t(ct+'.title',{leadgoal:fmtLeads})}</h2>
        <p class="company-target__desc">${t(ct+'.desc',{leadgoal:fmtLeads,convgoal:fmtConv,buyergoal:fmtBuyers,avggoal:fmtAvg,salesgoal:fmtSales})}</p>
      </div>
      <div class="company-target__hero-goal">
        <div class="company-target__goal-label">${t(ct+'.sales_goal')}</div>
        <div class="company-target__goal-value">${fmtMoney(salesGoal)}</div>
        <div class="company-target__goal-sub">${t(ct+'.goal_sub',{beaucount:beauCount})}</div>
      </div>
    </div>

    <div class="company-target__chain" aria-label="${t(ct+'.chain_aria')}">
      ${[
        [fmtLeads, t(ct+'.step_leads'), t(ct+'.step_leads_meta')],
        [fmtConv, t(ct+'.step_conv'), t(ct+'.step_conv_meta')],
        [fmtBuyers, t(ct+'.step_buyers'), t(ct+'.step_buyers_meta')],
        [fmtAvg, t(ct+'.step_avg'), t(ct+'.step_avg_meta')],
        [fmtSales, t(ct+'.step_sales'), t(ct+'.step_sales_meta')],
      ].map((x,i)=>`
        ${i?`<div class="company-target__arrow" aria-hidden="true">↓</div>`:''}
        <div class="company-target__step ${i===4?'company-target__step--goal':''}">
          <div class="company-target__step-value">${x[0]}</div>
          <div class="company-target__step-label">${x[1]}</div>
          <div class="company-target__step-meta">${x[2]}</div>
        </div>
      `).join('')}
    </div>

    <div class="grid company-target__kpis">
      <article class="ct-card ct-card--kpi1">
        <div class="ct-card__head">
          <span class="ct-card__badge">${t(ct+'.kpi1_badge')}</span>
          <span class="ct-card__icon">🎯</span>
        </div>
        <h3 class="ct-card__title">${t(ct+'.kpi1_title',{convgoal:fmtConv})}</h3>
        <p class="ct-card__text">${t(ct+'.kpi1_text',{leadgoal:fmtLeads,buyergoal:fmtBuyers})}</p>
        <div class="ct-card__math">
          <div><span>${t(ct+'.kpi1_leads')}</span><strong>${fmtLeads}</strong></div>
          <div><span>×</span><strong>${fmtConv}</strong></div>
          <div><span>${t(ct+'.kpi1_buyers')}</span><strong>${fmtBuyers}</strong></div>
        </div>
      </article>

      <article class="ct-card ct-card--kpi2">
        <div class="ct-card__head">
          <span class="ct-card__badge">${t(ct+'.kpi2_badge')}</span>
          <span class="ct-card__icon">💰</span>
        </div>
        <h3 class="ct-card__title">${t(ct+'.kpi2_title',{avggoal:fmtAvg})}</h3>
        <p class="ct-card__text">${t(ct+'.kpi2_text',{buyergoal:fmtBuyers,avggoal:fmtAvg,salesgoal:fmtSales})}</p>
        <div class="ct-card__math">
          <div><span>${t(ct+'.kpi2_buyers')}</span><strong>${fmtBuyers}</strong></div>
          <div><span>×</span><strong>${fmtAvg}</strong></div>
          <div><span>${t(ct+'.kpi2_sales')}</span><strong>${fmtSales}</strong></div>
        </div>
      </article>
    </div>

    <div class="company-target__beauty-head">
      <div>
        <div class="daily-panel__eyebrow">${t(ct+'.per_beautician')}</div>
        <div class="daily-panel__title">${t(ct+'.per_title')}</div>
        <div class="daily-panel__sub">${t(ct+'.per_sub',{beaucount:beauCount})}</div>
      </div>
    </div>
    <div class="grid company-target__beauty">
      <article class="ct-card">
        <div class="ct-card__head"><span class="ct-card__badge">${t(ct+'.badge_leads')}</span></div>
        <div class="ct-card__big">${fmtInt(perLeads)}</div>
        <div class="ct-card__unit">${t(ct+'.unit_leads')}</div>
        <p class="ct-card__text">${t(ct+'.text_leads',{leadgoal:fmtLeads,beaucount:beauCount,leadtarget:fmtInt(perLeads)})}</p>
      </article>
      <article class="ct-card">
        <div class="ct-card__head"><span class="ct-card__badge">${t(ct+'.badge_convert')}</span></div>
        <div class="ct-card__big">±${fmtInt(perBuyers)}</div>
        <div class="ct-card__unit">${t(ct+'.unit_convert')}</div>
        <p class="ct-card__text">${t(ct+'.text_convert',{leadtarget:fmtInt(perLeads),convgoal:fmtConv,buytarget:fmtInt(perBuyers)})}</p>
      </article>
      <article class="ct-card ct-card--accent">
        <div class="ct-card__head"><span class="ct-card__badge">${t(ct+'.badge_sales')}</span></div>
        <div class="ct-card__big">${compactMoney(perSales)}</div>
        <div class="ct-card__unit">${t(ct+'.unit_sales')}</div>
        <p class="ct-card__text">${t(ct+'.text_sales',{buytarget:fmtInt(perBuyers),avggoal:fmtAvg,salarget:fmtMoney(perSales),beaucount:beauCount,compact:compactMoney(perSales),total:fmtSales})}</p>
      </article>
    </div>
  </section>`;
}

function overview(){
  if(window.IMMA_TRADE) IMMA_TRADE.dispose();
  const m = liveMetrics || {};
  const k = m.kpis || {};
  const vs = k.vs_prev || {};
  const targets = m.targets || {};
  const dual = m.dual || {};
  const salesPct = Number(dual.sales_pct || 0);
  const salesTarget = Number(targets.sales || 0);
  const salesActual = Number(k.sales || 0);
  const salesDeltaRm = salesActual - salesTarget;
  const periodLabel = escapeHtml((m.period && m.period.label) || t('common.this_month'));
  const ops = m.ops || {};
  const ckOps = ops.checkin || {};
  const clOps = ops.clearance || {};
  const payOps = ops.payments || {};
  const opCheckinVal = t('ops.value_customers',{count:fmtInt(ckOps.live||0)});
  const opCheckinMeta = t('ops.meta_checkin',{waiting:fmtInt(ckOps.waiting||0),treatment:fmtInt(ckOps.in_treatment||0)});
  const opClearanceVal = t('ops.value_customers',{count:fmtInt(clOps.queue||0)});
  const opClearanceMeta = t('ops.meta_clearance',{waiting:fmtInt(clOps.waiting||0),blocked:fmtInt(clOps.blocked||0)});
  const opPaymentsVal = t('ops.value_pending',{count:fmtInt(payOps.queue||0)});
  const opPaymentsMeta = t('ops.meta_payments',{processing:fmtInt(payOps.processing||0),hold:fmtInt(payOps.hold||0)});

  root.innerHTML = `${pageHead(t('overview.title'),t('overview.subtitle'),`<button class="btn soft">${periodLabel}</button><button class="btn primary" data-jump="leads">${t('common.open_leads')}</button>`)}
  ${companyTargetBoard()}
  <div class="trade-grid trade-grid--2">
    ${tradePane('trade.dual_title','trade.dual_sub','tradeDualRing','sm')}
    ${tradePane('trade.equity_title','trade.equity_sub','tradeEquity','lg')}
  </div>
  <div class="section-label">${t('overview.actual_label')}</div>
  <div class="grid kpi-grid">
    ${kpi('♙',t('overview.kpi_unique_leads'),fmtInt(k.new_buyers),deltaTrend(vs.new_buyers,'%'),targetHint(t('overview.kpi_unique_leads'), fmtInt(targets.leads||0)),'rose',Math.min(100, ((k.new_buyers||0)/Math.max(1,targets.leads||0))*100),'','sparkLeads')}
    ${kpi('▣',t('overview.kpi_buyers'),fmtInt(k.buyers),deltaTrend(vs.buyers,'%'),targetHint(t('overview.kpi_buyers'), fmtInt(targets.buyers||0)),'blue',Math.min(100, ((k.buyers||0)/Math.max(1,targets.buyers||0))*100),'','sparkBuyers')}
    ${kpi('%',t('overview.kpi_conv_rate'),fmtPct(k.new_buyer_share_pct),deltaTrend(vs.new_buyer_share_pct,'pp'),targetHint(t('overview.kpi_conv_rate'), fmtPct(targets.conv_pct||0)),'green',Math.min(100, Number(k.new_buyer_share_pct||0)),'','sparkConv')}
    ${kpi('◫',t('overview.kpi_sales'),fmtMoney(k.sales),deltaTrend(vs.sales,'%'),targetHint(t('overview.kpi_sales'), fmtMoney(salesTarget)),'rose',Math.min(100, salesPct),'','sparkSales')}
    ${kpi('▥',t('overview.kpi_avg_sale'),fmtMoney(k.avg_sale),deltaTrend(vs.avg_sale,'%'),targetHint(t('overview.kpi_avg_sale'), fmtMoney(targets.avg_sale||0)),'purple',Math.min(100, ((k.avg_sale||0)/Math.max(1,targets.avg_sale||0))*100),'','sparkAvg')}
  </div>
  <div class="trade-grid trade-grid--2" style="margin-top:12px">
    ${tradePane('trade.waterfall_title','trade.waterfall_sub','tradeWaterfall')}
    <section class="card">
      <div class="card-title-row"><div class="card-title">▥ ${t('overview.lead_status')}</div><button class="btn small soft">${periodLabel}</button></div>
      <div style="display:grid;grid-template-columns:180px 1fr;gap:14px;align-items:center">
        <div class="chart-wrap" style="height:180px"><canvas id="donutChart"></canvas></div>
        <div class="legend" id="leadLegend"></div>
      </div>
    </section>
  </div>
  <div class="trade-grid trade-grid--2">
    ${tradePane('trade.conversion_title','trade.conversion_sub','tradeConversion')}
    ${tradePane('trade.heatmap_title','trade.heatmap_sub','tradeHeatmap')}
  </div>

  <div class="grid split-60" style="margin-top:12px">
    <section class="card sales-card">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${t('overview.revenue')}</div>
          <div class="daily-panel__title">${t('overview.monthly_sales')}</div>
          <div class="daily-panel__sub">${t('overview.monthly_sales_sub')}</div>
        </div>
        <span class="sales-card__pill">${periodLabel} · ${fmtMoney(salesActual)}</span>
      </div>
      <div class="chart-wrap sales-card__chart"><canvas id="salesChart"></canvas></div>
    </section>
    <section class="card target-card ${salesPct>=100?'target-card--over':''}">
      <div class="target-card__head">
        <div>
          <div class="target-card__eyebrow">${t('overview.monthly_goal')}</div>
          <div class="target-card__title">${t('overview.target_achievement')}</div>
        </div>
        <span class="target-card__pill">${salesPct>=100?t('overview.above_target'):t('overview.below_target')}</span>
      </div>
      <div class="target-card__body">
        <div class="target-card__ring" style="--p:${Math.min(100, salesPct)}">
          <svg viewBox="0 0 120 120" aria-hidden="true">
            <circle class="target-card__track" cx="60" cy="60" r="52"></circle>
            <circle class="target-card__prog" cx="60" cy="60" r="52"></circle>
          </svg>
          <div class="target-card__ring-value">
            <strong>${fmtPct(salesPct)}</strong>
            <span>${t('overview.of_target')}</span>
          </div>
        </div>
        <div class="target-card__side">
          <div class="target-card__stat">
            <span>${t('overview.target')}</span>
            <strong>${fmtMoney(salesTarget)}</strong>
          </div>
          <div class="target-card__stat">
            <span>${t('overview.actual')}</span>
            <strong>${fmtMoney(salesActual)}</strong>
          </div>
          <div class="target-card__delta">
            <strong>${salesDeltaRm>=0?'+':''}${fmtMoney(salesDeltaRm)}</strong>
            <span>${t('overview.vs_target_month')}</span>
          </div>
        </div>
      </div>
    </section>
  </div>

  <section class="card" style="margin-top:12px"><div class="card-title-row"><div class="card-title">♙ ${t('overview.beauticians')}</div><button class="btn small primary" data-jump="beauticians">${t('common.view_all')}</button></div>${beauticianTable()}</section>

  <div class="grid three-col" style="margin-top:12px">
    ${(m.branches||[]).slice(0,6).map(b=>branchCard(b.name, b.new_buyers||0, b.buyers||0, b.conv||0, b.sales||0, b.avg||0, b.buyers||0)).join('') || `<section class="card"><div class="empty"><strong>${t('overview.no_branch_data')}</strong></div></section>`}
  </div>

  <div class="grid op-row" style="margin-top:12px">
    ${opCard('♧',t('ops.checkin'),opCheckinVal,opCheckinMeta,'checkin',t('ops.checkin'),'green')}
    ${opCard('◷',t('ops.clearance'),opClearanceVal,opClearanceMeta,'clearance',t('ops.clearance'),'warning')}
    ${opCard('▣',t('ops.payments'),opPaymentsVal,opPaymentsMeta,'payments',t('ops.payments'),'rose')}
  </div>
  <div style="margin-top:12px">${dailyLeadHero()}</div>
  <div style="margin-top:12px">${dailyLeadBoard()}</div>`;
  requestAnimationFrame(()=>{
    drawDailyLeadChart(); drawDailyTrendChart(); drawDonut(); drawSales(); bindJump();
    if(window.IMMA_TRADE){
      const ticker = Object.assign({}, m.ticker||{});
      const ups = {
        leadsUp: Number(vs.new_buyers||0) >= 0,
        convUp: Number(vs.new_buyer_share_pct||0) >= 0,
        salesUp: Number(vs.sales||0) >= 0,
        targetUp: salesPct >= 100,
        avgUp: Number(vs.avg_sale||0) >= 0,
      };
      IMMA_TRADE.render({
        ticker: Object.assign(ticker, ups),
        leadsPct: Number(dual.buyers_pct || dual.leads_pct || 0),
        salesPct,
        equityActual: (m.equity&&m.equity.actual)||[],
        equityTarget: (m.equity&&m.equity.target_path)||[],
        equityLabels: (m.equity&&m.equity.labels)||[],
        waterfall: (m.waterfall||m.status_mix||[]).map(s=>({name:String(s.name||''), value:s.value})),
        beauticians: (m.beauticians||[]).map(b=>({name:String(b.name||''), leads:b.leads||0, converted:b.converted||0, conv:b.conv||0, sales:b.sales||0})),
        heatmap: m.heatmap||[],
        sparks: m.sparks||[]
      });
    }
  });
}
function beauticianTable(){
  const rows = Array.isArray(liveMetrics?.beauticians) ? liveMetrics.beauticians : [];
  if(!rows.length) return `<div class="empty"><strong>${t('overview.no_beautician_data')}</strong></div>`;
  return `<div class="table-wrap"><table class="data-table"><thead><tr><th>#</th><th>${t('overview.col_beautician')}</th><th title="${t('overview.kpi_unique_leads')}">${t('overview.unique')}</th><th>${t('overview.kpi_buyers')}</th><th title="${t('overview.kpi_conv_rate')}">${t('overview.conv_rate')}</th><th>${t('overview.kpi_sales')}</th><th>${t('overview.kpi_avg_sale')}</th><th>${t('overview.col_orders')}</th></tr></thead><tbody>${rows.map((b,i)=>{
    const name=escapeHtml(b.name);
    return `<tr><td><span class="rank">${i+1}</span></td><td><strong>${name}</strong></td><td>${fmtInt(b.leads)}</td><td>${fmtInt(b.buyers)}</td><td style="color:${b.conv>=40?'var(--success)':b.conv<35?'var(--danger)':'#b16e10'};font-weight:700">${fmtPct(b.conv)}</td><td>${fmtMoney(b.sales)}</td><td>${fmtMoney(b.avg)}</td><td>${fmtInt(b.orders||0)}</td></tr>`;
  }).join('')}</tbody></table></div>`;
}
function branchCard(name,leads,converted,conv,sales,avg,done){
  const tone=conv>=40?'good':conv>=35?'ok':'low';
  const safeName=escapeHtml(name);
  const mark=escapeHtml(String(name||'').slice(0,2).toUpperCase());
  return `<section class="card branch-card branch-card--${tone}">
    <div class="branch-card__head">
      <div class="branch-card__identity">
        <div class="branch-card__mark" aria-hidden="true">${mark}</div>
        <div>
          <div class="branch-card__name">${safeName} ${t('overview.branch')}</div>
          <div class="branch-card__meta">${t('overview.this_month_meta')}</div>
        </div>
      </div>
      <button type="button" class="branch-card__link link-btn" data-branch="${safeName}">${t('common.details')}</button>
    </div>
    <div class="branch-card__hero">
      <div>
        <div class="branch-card__hero-label" title="${t('overview.conv_rate')}">${t('overview.conv_rate')}</div>
        <div class="branch-card__hero-value">${conv}%</div>
      </div>
      <div class="branch-card__sales">
        <div class="branch-card__hero-label">${t('overview.kpi_sales')}</div>
        <div class="branch-card__sales-value">${money(sales)}</div>
      </div>
    </div>
    <div class="branch-card__bar" aria-hidden="true"><span style="width:${Math.min(100,conv)}%"></span></div>
    <div class="branch-card__metrics">
      <div class="branch-metric"><span title="${t('overview.unique')}">${t('overview.unique')}</span><strong>${leads.toLocaleString()}</strong></div>
      <div class="branch-metric"><span>${t('overview.converted')}</span><strong>${converted.toLocaleString()}</strong></div>
      <div class="branch-metric"><span title="${t('overview.kpi_avg_sale')}">${t('overview.kpi_avg_sale')}</span><strong>${money(avg)}</strong></div>
      <div class="branch-metric"><span title="${t('overview.treat_done')}">${t('overview.treat_done')}</span><strong>${done.toLocaleString()}</strong></div>
    </div>
  </section>`;
}
function opCard(icon,title,value,meta,view,label,color){return `<section class="card op-card"><div class="op-icon" style="background:${color==='green'?'var(--success-soft)':color==='warning'?'var(--warning-soft)':'var(--rose-soft)'}">${icon}</div><div class="op-body"><div class="op-title">${title}</div><div class="op-value">${value}</div><div class="op-meta">${meta}</div></div><button class="btn small primary" data-jump="${view}">${label} →</button></section>`}

function shiftLeadMonth(delta){
  if(!state.leadMonth || state.leadMonth==='all'){
    state.leadMonth=currentMonthKey();
  }
  const [y,m]=state.leadMonth.split('-').map(Number);
  const d=new Date(y, m-1+delta, 1);
  state.leadMonth=d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0');
  state.leadPage=1;
  refreshLeads().then(()=>updateLeadCalendarUi());
}
function leadMonthParts(key){
  if(!key || key==='all') return null;
  const [y,m]=String(key).split('-').map(Number);
  if(!y || !m) return null;
  return {y, m};
}
function leadMonthShortNames(){
  const loc=(boot.locale||'en').toLowerCase().startsWith('ms') ? 'ms-MY' : 'en-GB';
  return Array.from({length:12},(_,i)=> new Date(2000,i,1).toLocaleString(loc,{month:'short'}));
}
function leadMonthLabel(key){
  if(!key || key==='all') return t('workspace.all_months');
  const parts=leadMonthParts(key);
  if(!parts) return String(key);
  const months=(liveLeadFilters.months||[]);
  const hit=months.find(m=>String(m.value)===String(key));
  if(hit) return hit.label;
  return leadMonthShortNames()[parts.m-1]+' '+parts.y;
}
function currentMonthKey(){
  const d=new Date();
  return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0');
}
function currentDateKey(){
  const d=new Date();
  return currentMonthKey()+'-'+String(d.getDate()).padStart(2,'0');
}
function leadMonthActions(){
  const parts=leadMonthParts(state.leadMonth) || leadMonthParts(currentMonthKey());
  const year=state.leadCalYear || parts.y;
  const canCreate=!!(boot.canCreateLead);
  return `
    <div class="lead-cal" id="leadCalendar">
      <button type="button" class="lead-cal__nav" id="leadMonthPrev" title="${escapeHtml(t('workspace.month_prev'))}" aria-label="${escapeHtml(t('workspace.month_prev'))}">‹</button>
      <button type="button" class="lead-cal__toggle" id="leadCalToggle" aria-expanded="false" aria-haspopup="dialog">
        <span class="lead-cal__icon" aria-hidden="true">▦</span>
        <span id="leadMonthLabel">${escapeHtml(leadMonthLabel(state.leadMonth))}</span>
      </button>
      <button type="button" class="lead-cal__nav" id="leadMonthNext" title="${escapeHtml(t('workspace.month_next'))}" aria-label="${escapeHtml(t('workspace.month_next'))}">›</button>
      <div class="lead-cal__panel hidden" id="leadCalPanel" role="dialog" aria-label="${escapeHtml(t('workspace.month_label'))}">
        <div class="lead-cal__year-row">
          <button type="button" class="lead-cal__nav" id="leadCalYearPrev" aria-label="${escapeHtml(t('workspace.year_prev'))}">‹</button>
          <strong id="leadCalYearLabel">${year}</strong>
          <button type="button" class="lead-cal__nav" id="leadCalYearNext" aria-label="${escapeHtml(t('workspace.year_next'))}">›</button>
        </div>
        <div class="lead-cal__grid" id="leadCalGrid"></div>
        <div class="lead-cal__footer">
          <button type="button" class="btn soft small" id="leadCalAll">${escapeHtml(t('workspace.all_months'))}</button>
          <button type="button" class="btn soft small" id="leadMonthThis">${escapeHtml(t('workspace.this_month'))}</button>
        </div>
      </div>
    </div>
    ${canCreate?`
      <button type="button" class="btn" data-jump="import" data-import-tab="paste">${escapeHtml(t('workspace.paste_leads'))}</button>
      <button type="button" class="btn" data-jump="import" data-import-tab="excel">${escapeHtml(t('workspace.upload_excel'))}</button>
      <button type="button" class="btn primary" data-jump="import" data-import-tab="paste">${escapeHtml(t('workspace.import_leads'))}</button>
    `:''}
  `;
}
function renderLeadCalGrid(){
  const grid=$('#leadCalGrid');
  const yearLabel=$('#leadCalYearLabel');
  if(!grid) return;
  const parts=leadMonthParts(state.leadMonth);
  const year=state.leadCalYear || (parts?parts.y:new Date().getFullYear());
  state.leadCalYear=year;
  if(yearLabel) yearLabel.textContent=String(year);
  const names=leadMonthShortNames();
  const now=currentMonthKey();
  grid.innerHTML=names.map((name,i)=>{
    const value=`${year}-${String(i+1).padStart(2,'0')}`;
    const selected=String(state.leadMonth)===value;
    const isNow=value===now;
    return `<button type="button" class="lead-cal__month${selected?' is-selected':''}${isNow?' is-now':''}" data-month="${value}">${escapeHtml(name)}</button>`;
  }).join('');
  $$('[data-month]',grid).forEach(btn=>{
    btn.onclick=()=>{
      state.leadMonth=btn.dataset.month;
      state.leadPage=1;
      closeLeadCalendar();
      updateLeadCalendarUi();
      refreshLeads();
    };
  });
}
function openLeadCalendar(){
  const panel=$('#leadCalPanel');
  const toggle=$('#leadCalToggle');
  if(!panel||!toggle) return;
  const parts=leadMonthParts(state.leadMonth);
  state.leadCalYear=parts?parts.y:new Date().getFullYear();
  panel.classList.remove('hidden');
  toggle.setAttribute('aria-expanded','true');
  renderLeadCalGrid();
}
function closeLeadCalendar(){
  const panel=$('#leadCalPanel');
  const toggle=$('#leadCalToggle');
  if(panel) panel.classList.add('hidden');
  if(toggle) toggle.setAttribute('aria-expanded','false');
}
function updateLeadCalendarUi(){
  const label=$('#leadMonthLabel');
  if(label) label.textContent=leadMonthLabel(state.leadMonth);
  if($('#leadCalPanel') && !$('#leadCalPanel').classList.contains('hidden')){
    renderLeadCalGrid();
  }
}
function onLeadCalOutside(e){
  const rootEl=$('#leadCalendar');
  if(!rootEl || rootEl.contains(e.target)) return;
  closeLeadCalendar();
}
function bindLeadMonthActions(){
  if($('#leadMonthPrev')) $('#leadMonthPrev').onclick=()=>{ closeLeadCalendar(); shiftLeadMonth(-1); };
  if($('#leadMonthNext')) $('#leadMonthNext').onclick=()=>{ closeLeadCalendar(); shiftLeadMonth(1); };
  if($('#leadCalToggle')) $('#leadCalToggle').onclick=(e)=>{
    e.stopPropagation();
    const panel=$('#leadCalPanel');
    if(panel && panel.classList.contains('hidden')) openLeadCalendar();
    else closeLeadCalendar();
  };
  if($('#leadCalYearPrev')) $('#leadCalYearPrev').onclick=(e)=>{ e.stopPropagation(); state.leadCalYear=(state.leadCalYear||new Date().getFullYear())-1; renderLeadCalGrid(); };
  if($('#leadCalYearNext')) $('#leadCalYearNext').onclick=(e)=>{ e.stopPropagation(); state.leadCalYear=(state.leadCalYear||new Date().getFullYear())+1; renderLeadCalGrid(); };
  if($('#leadMonthThis')) $('#leadMonthThis').onclick=(e)=>{
    e.stopPropagation();
    state.leadMonth=currentMonthKey();
    state.leadPage=1;
    closeLeadCalendar();
    updateLeadCalendarUi();
    refreshLeads();
  };
  if($('#leadCalAll')) $('#leadCalAll').onclick=(e)=>{
    e.stopPropagation();
    state.leadMonth='all';
    state.leadPage=1;
    closeLeadCalendar();
    updateLeadCalendarUi();
    refreshLeads();
  };
  document.removeEventListener('click', onLeadCalOutside);
  document.addEventListener('click', onLeadCalOutside);
}

function leads(){
  const canCreate = !!(boot.canCreateLead);
  const actions = leadMonthActions();
  root.innerHTML = `${pageHead(t('workspace.title'),t('workspace.subtitle'),actions)}
  <section class="lead-kpi-section" aria-labelledby="leadKpiTitle">
    <div class="lead-kpi-section__head">
      <div><span class="lead-kpi-section__eyebrow">${escapeHtml(t('workspace.kpi_eyebrow'))}</span><h2 id="leadKpiTitle">${escapeHtml(t('workspace.kpi_heading'))}</h2></div>
      <span class="lead-kpi-section__scope">${escapeHtml(t('workspace.kpi_scope',{period:leadMonthLabel(state.leadMonth)}))}</span>
    </div>
    <div class="grid kpi-grid" id="leadKpiMount"></div>
    <p class="card-subtitle">${escapeHtml(t('workspace.kpi_note'))}</p>
  </section>
  <section class="lead-panel card" style="margin-top:14px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${escapeHtml(t('workspace.list_title'))}</h2>
        <p class="lead-panel__sub">${escapeHtml(t('workspace.list_subtitle'))}</p>
      </div>
      <div class="lead-panel__head-meta">
        <span class="lead-panel__count" id="leadResultCount">—</span>
        ${canCreate?`<button class="btn primary" type="button" id="addLeadBtn">${escapeHtml(t('workspace.add_lead'))}</button>`:''}
      </div>
    </div>
    <div class="lead-panel__filters">
      <label class="lead-search" for="leadSearch">
        <span class="lead-search__icon" aria-hidden="true">⌕</span>
        <input class="lead-search__input" id="leadSearch" type="search" autocomplete="off" placeholder="${escapeHtml(t('workspace.search_placeholder'))}" value="${escapeHtml(state.leadSearch)}" />
      </label>
      <div class="lead-filter-grid">
        <label class="lead-field">
          <span class="lead-field__label">${escapeHtml(t('workspace.filter_status'))}</span>
          <select class="lead-field__control" id="leadStatus"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${escapeHtml(t('workspace.filter_beautician'))}</span>
          <select class="lead-field__control" id="leadBeautician"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${escapeHtml(t('workspace.filter_branch'))}</span>
          <select class="lead-field__control" id="leadBranchFilter"></select>
        </label>
      </div>
    </div>
    <div class="lead-panel__chips" id="leadActiveFilters" hidden></div>
    <div class="lead-bulk-bar" id="leadBulkBar" hidden aria-live="polite"></div>
    <div class="lead-panel__body" id="leadTableMount"></div>
  </section>`;
  renderLeadKpis();
  bindLeadFilters();
  bindLeadMonthActions();
  bindJump();
  if($('#addLeadBtn')) $('#addLeadBtn').onclick=openAddLeadDrawer;
  refreshLeads();
}
function renderLeadKpis(){
  const mount=$('#leadKpiMount'); if(!mount) return;
  const s=liveLeadSummary||{};
  const all=s.all_time||s;
  const raw=Number(s.raw||0), uniq=Number(s.unique||0), dup=Number(s.duplicates||0), exist=Number(s.existing||0), conv=Number(s.converted||0), pct=Number(s.conversion_pct||0);
  const allRaw=Number(all.raw||0), allUniq=Number(all.unique||0), allDup=Number(all.duplicates||0), allExist=Number(all.existing||0), allConv=Number(all.converted||0), allPct=Number(all.conversion_pct||0);
  const allClean=allRaw>0?((allUniq/allRaw)*100).toFixed(1):'0.0';
  const allDupPct=allRaw>0?((allDup/allRaw)*100).toFixed(1):'0.0';
  const period=leadMonthLabel(state.leadMonth);
  mount.innerHTML = `
    ${leadKpi(leadKpiIcon('database'),t('workspace.total_leads_database'),fmtInt(allRaw),t('workspace.unit_lead_records'),t('workspace.all_time'),t('workspace.period_added',{period,count:fmtInt(raw)}),'blue')}
    ${leadKpi(leadKpiIcon('new'),t('workspace.new_leads'),fmtInt(uniq),t('workspace.unit_new_leads'),period,t('workspace.all_time_unique',{count:fmtInt(allUniq),pct:allClean}),'green')}
    ${leadKpi(leadKpiIcon('repeated'),t('workspace.repeated_leads'),fmtInt(allDup),t('workspace.unit_repeated_records'),t('workspace.all_time'),t('workspace.period_repeated',{period,count:fmtInt(dup),pct:allDupPct}),'rose')}
    ${leadKpi(leadKpiIcon('customers'),t('workspace.existing_customers'),fmtInt(allExist),t('workspace.unit_registered_customers'),t('workspace.matched_phone'),t('workspace.period_matched',{period,count:fmtInt(exist)}),'purple')}
    ${leadKpi(leadKpiIcon('conversion'),t('workspace.conversion'),fmtPct(allPct),t('workspace.unit_conversion_rate'),t('workspace.all_time'),t('workspace.period_conversion',{period,pct:fmtPct(pct),count:fmtInt(conv),total:fmtInt(allConv)}),'teal')}
  `;
}
function bindLeadFilters(){
  const statusEl=$('#leadStatus');
  const beauEl=$('#leadBeautician');
  const branchEl=$('#leadBranchFilter');
  if(statusEl){
    const opts=[{value:'all',label:t('workspace.all_status')}, ...((liveLeadFilters.statuses)||[])];
    statusEl.innerHTML=opts.map(o=>`<option value="${escapeHtml(o.value)}" ${String(state.leadStatus)===String(o.value)?'selected':''}>${escapeHtml(o.label)}</option>`).join('');
    statusEl.onchange=e=>{state.leadStatus=e.target.value;state.leadPage=1;refreshLeads();};
  }
  if(beauEl){
    const opts=[{id:'all',name:t('workspace.all_beauticians')}, ...((liveLeadFilters.beauticians)||[])];
    beauEl.innerHTML=opts.map(o=>`<option value="${escapeHtml(o.id)}" ${String(state.leadBeautician)===String(o.id)?'selected':''}>${escapeHtml(o.name)}</option>`).join('');
    beauEl.onchange=e=>{state.leadBeautician=e.target.value;state.leadPage=1;refreshLeads();};
  }
  if(branchEl){
    const opts=[{id:'all',name:t('workspace.all_branches')}, ...((liveLeadFilters.branches)||boot.branches||[])];
    branchEl.innerHTML=opts.map(o=>`<option value="${escapeHtml(o.id)}" ${String(state.leadBranch)===String(o.id)?'selected':''}>${escapeHtml(o.name)}</option>`).join('');
    branchEl.onchange=e=>{state.leadBranch=e.target.value;state.leadPage=1;refreshLeads();};
  }
  const search=$('#leadSearch');
  if(search){
    search.oninput=e=>{
      state.leadSearch=e.target.value;
      clearTimeout(leadSearchTimer);
      leadSearchTimer=setTimeout(()=>{state.leadPage=1;refreshLeads();},350);
    };
  }
  updateLeadFilterChrome();
}
function leadFilterChipLabel(kind, value){
  if(kind==='status'){
    const hit=((liveLeadFilters.statuses)||[]).find(s=>String(s.value)===String(value));
    return hit?hit.label:value;
  }
  if(kind==='beautician'){
    const hit=((liveLeadFilters.beauticians)||[]).find(s=>String(s.id)===String(value));
    return hit?hit.name:value;
  }
  if(kind==='branch'){
    const list=(liveLeadFilters.branches)||boot.branches||[];
    const hit=list.find(s=>String(s.id)===String(value));
    return hit?hit.name:value;
  }
  return value;
}
function updateLeadFilterChrome(){
  const countEl=$('#leadResultCount');
  const n=Number(leadPageMeta.total ?? (Array.isArray(liveLeads)?liveLeads.length:0));
  if(countEl){
    countEl.textContent = n===1 ? t('workspace.results_count_one') : t('workspace.results_count',{count:fmtInt(n)});
  }
  const chips=$('#leadActiveFilters');
  if(!chips) return;
  const items=[];
  if(state.leadSearch && String(state.leadSearch).trim()){
    items.push({key:'q', label:`“${String(state.leadSearch).trim()}”`});
  }
  if(state.leadStatus && state.leadStatus!=='all'){
    items.push({key:'status', label:leadFilterChipLabel('status', state.leadStatus)});
  }
  if(state.leadBeautician && state.leadBeautician!=='all'){
    items.push({key:'beautician', label:leadFilterChipLabel('beautician', state.leadBeautician)});
  }
  if(state.leadBranch && state.leadBranch!=='all'){
    items.push({key:'branch', label:leadFilterChipLabel('branch', state.leadBranch)});
  }
  if(!items.length){
    chips.hidden=true;
    chips.innerHTML='';
    return;
  }
  chips.hidden=false;
  chips.innerHTML=`
    <div class="lead-chips">
      ${items.map(i=>`<span class="lead-chip">${escapeHtml(i.label)}<button type="button" class="lead-chip__x" data-clear-filter="${escapeHtml(i.key)}" aria-label="${escapeHtml(t('workspace.clear_filters'))}">×</button></span>`).join('')}
      <button type="button" class="lead-chips__clear" id="leadClearFilters">${escapeHtml(t('workspace.clear_filters'))}</button>
    </div>
  `;
  $$('[data-clear-filter]',chips).forEach(btn=>{
    btn.onclick=()=>{
      const k=btn.dataset.clearFilter;
      if(k==='q') state.leadSearch='';
      if(k==='status') state.leadStatus='all';
      if(k==='beautician') state.leadBeautician='all';
      if(k==='branch') state.leadBranch='all';
      state.leadPage=1;
      const search=$('#leadSearch'); if(search && k==='q') search.value='';
      bindLeadFilters();
      refreshLeads();
    };
  });
  if($('#leadClearFilters')){
    $('#leadClearFilters').onclick=()=>{
      state.leadSearch='';
      state.leadStatus='all';
      state.leadBeautician='all';
      state.leadBranch='all';
      state.leadPage=1;
      const search=$('#leadSearch'); if(search) search.value='';
      bindLeadFilters();
      refreshLeads();
    };
  }
}
function leadBulkValueOptions(action){
  if(action==='status'){
    return (liveLeadFilters.statuses||[]).map(option=>`<option value="${escapeHtml(option.value)}">${escapeHtml(option.label)}</option>`).join('');
  }
  if(action==='source'){
    return (liveLeadFilters.sources||[]).map(option=>`<option value="${escapeHtml(option.value)}">${escapeHtml(option.label)}</option>`).join('');
  }
  if(action==='beautician_id'){
    return `<option value="__none__">${escapeHtml(t('workspace.bulk_unassigned'))}</option>${(liveLeadFilters.beauticians||[]).map(option=>`<option value="${escapeHtml(option.id)}">${escapeHtml(option.name)}</option>`).join('')}`;
  }
  if(action==='spa_branch_id'){
    return `<option value="__none__">${escapeHtml(t('workspace.bulk_unassigned'))}</option>${(liveLeadFilters.branches||boot.branches||[]).map(option=>`<option value="${escapeHtml(option.id)}">${escapeHtml(option.name)}</option>`).join('')}`;
  }
  return '';
}
function renderLeadBulkBar(){
  const bar=$('#leadBulkBar');if(!bar)return;
  const count=selectedLeadIds.size;
  if(!count){bar.hidden=true;bar.innerHTML='';leadBulkAction='';leadBulkValue='';return;}
  const actions=[];
  if(boot.canEditLead){
    actions.push(['status',t('workspace.bulk_update_status')],['source',t('workspace.bulk_update_source')],['created_at',t('workspace.bulk_update_date')],['beautician_id',t('workspace.bulk_assign_beautician')],['spa_branch_id',t('workspace.bulk_assign_branch')]);
  }
  if(boot.canDeleteLead)actions.push(['delete',t('workspace.bulk_delete')]);
  if(!actions.some(([value])=>value===leadBulkAction)){
    leadBulkAction='';
    leadBulkValue='';
  }
  const needsValue=['status','source','created_at','beautician_id','spa_branch_id'].includes(leadBulkAction);
  bar.hidden=false;
  bar.innerHTML=`
    <div class="lead-bulk-bar__summary"><strong>${escapeHtml(t('workspace.bulk_selected',{count:fmtInt(count)}))}</strong></div>
    <div class="lead-bulk-bar__controls">
      <label class="sr-only" for="leadBulkAction">${escapeHtml(t('workspace.bulk_action'))}</label>
      <select class="lead-bulk-control" id="leadBulkAction">
        <option value="">${escapeHtml(t('workspace.bulk_choose_action'))}</option>
        ${actions.map(([value,label])=>`<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`).join('')}
      </select>
      ${leadBulkAction==='created_at'?`<label class="sr-only" for="leadBulkValue">${escapeHtml(t('workspace.bulk_choose_date'))}</label>
      <input class="lead-bulk-control lead-bulk-date" id="leadBulkValue" type="date" max="${currentDateKey()}" value="${escapeHtml(leadBulkValue)}" aria-label="${escapeHtml(t('workspace.bulk_choose_date'))}">`:(needsValue?`<label class="sr-only" for="leadBulkValue">${escapeHtml(t('workspace.bulk_choose_value'))}</label>
      <select class="lead-bulk-control" id="leadBulkValue">
        <option value="">${escapeHtml(t('workspace.bulk_choose_value'))}</option>
        ${leadBulkValueOptions(leadBulkAction)}
      </select>`:'')}
      <button type="button" class="btn ${leadBulkAction==='delete'?'danger':'primary'} lead-bulk-apply" id="leadBulkApply" ${!leadBulkAction||(needsValue&&!leadBulkValue)?'disabled':''}>${escapeHtml(t('workspace.bulk_apply'))}</button>
      <button type="button" class="btn lead-bulk-clear" id="leadBulkClear">${escapeHtml(t('workspace.bulk_clear'))}</button>
    </div>`;
  const actionControl=$('#leadBulkAction');
  actionControl.value=leadBulkAction;
  actionControl.onchange=e=>{leadBulkAction=e.target.value;leadBulkValue='';renderLeadBulkBar();};
  const valueControl=$('#leadBulkValue');
  if(valueControl){
    valueControl.value=leadBulkValue;
    valueControl.onchange=e=>{leadBulkValue=e.target.value;renderLeadBulkBar();};
  }
  $('#leadBulkApply').onclick=applyLeadBulkAction;
  $('#leadBulkClear').onclick=()=>{selectedLeadIds.clear();leadBulkAction='';leadBulkValue='';syncLeadSelectionUi();};
}
function syncLeadSelectionUi(){
  const visibleIds=liveLeads.map(lead=>String(lead.id));
  const selectedVisible=visibleIds.filter(id=>selectedLeadIds.has(id)).length;
  const selectAll=$('#leadSelectAll');
  if(selectAll){
    selectAll.checked=visibleIds.length>0&&selectedVisible===visibleIds.length;
    selectAll.indeterminate=selectedVisible>0&&selectedVisible<visibleIds.length;
  }
  $$('[data-lead-select]').forEach(input=>{
    const selected=selectedLeadIds.has(String(input.value));
    input.checked=selected;
    input.closest('tr')?.classList.toggle('is-selected',selected);
  });
  renderLeadBulkBar();
}
async function submitLeadBulkUpdate(field,value,button){
  const url=boot.leadBulkUpdateUrl||'';
  if(!url){showToast(t('workspace.bulk_update_error'));return;}
  button.disabled=true;
  try{
    const res=await fetch(url,{method:'PATCH',headers:apiHeaders(true),credentials:'same-origin',body:JSON.stringify({ids:[...selectedLeadIds].map(Number),field,value})});
    const body=await res.json().catch(()=>({}));
    if(!res.ok){showToast(body.message||t('workspace.bulk_update_error'));return;}
    showToast(body.message||t('workspace.bulk_updated',{count:selectedLeadIds.size}));
    await refreshLeads();
  }catch(err){console.error(err);showToast(t('workspace.bulk_update_error'));}
  finally{if(button.isConnected)button.disabled=false;}
}
async function submitLeadBulkDelete(button){
  const url=boot.leadBulkDeleteUrl||'';
  if(!url){showToast(t('workspace.bulk_delete_error'));return;}
  button.disabled=true;
  try{
    const res=await fetch(url,{method:'DELETE',headers:apiHeaders(true),credentials:'same-origin',body:JSON.stringify({ids:[...selectedLeadIds].map(Number)})});
    const body=await res.json().catch(()=>({}));
    if(!res.ok){showToast(body.message||t('workspace.bulk_delete_error'));return;}
    if(Number(body.deleted||0)>=liveLeads.length&&state.leadPage>1)state.leadPage--;
    closeActionDrawer();
    showToast(body.message||t('workspace.bulk_deleted',{count:selectedLeadIds.size}));
    await refreshLeads();
  }catch(err){console.error(err);showToast(t('workspace.bulk_delete_error'));}
  finally{if(button.isConnected)button.disabled=false;}
}
function applyLeadBulkAction(){
  const count=selectedLeadIds.size;
  if(!count){showToast(t('workspace.bulk_nothing_selected'));return;}
  const action=leadBulkAction;
  const button=$('#leadBulkApply');
  if(!action||!button)return;
  if(action==='delete'){
    openActionDrawer(
      t('workspace.bulk_delete'),
      t('workspace.bulk_delete_confirm',{count:fmtInt(count)}),
      '',
      `<button type="button" class="btn" data-action-drawer-close>${escapeHtml(t('workspace.cancel'))}</button><button type="button" class="btn danger" id="confirmBulkDeleteLead">${escapeHtml(t('workspace.bulk_delete'))}</button>`,
      t('workspace.bulk_action')
    );
    $('#confirmBulkDeleteLead').onclick=e=>submitLeadBulkDelete(e.currentTarget);
    return;
  }
  if(!leadBulkValue){$('#leadBulkValue')?.focus();return;}
  const value=leadBulkValue==='__none__'?null:(['beautician_id','spa_branch_id'].includes(action)?Number(leadBulkValue):leadBulkValue);
  submitLeadBulkUpdate(action,value,button);
}
function renderLeadTable(){
  const mount=$('#leadTableMount'); if(!mount)return;
  updateLeadFilterChrome();
  if(leadsLoading){
    mount.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${escapeHtml(t('workspace.loading'))}</strong></div>`;
    return;
  }
  const rows=liveLeads;
  if(!rows.length){
    const canCreate=!!boot.canCreateLead;
    mount.innerHTML=`<div class="lead-empty">
      <div class="lead-empty__icon" aria-hidden="true">◎</div>
      <strong>${escapeHtml(t('workspace.no_leads'))}</strong>
      <p>${escapeHtml(t('workspace.no_leads_hint'))}</p>
      ${canCreate?`<button type="button" class="btn primary" id="emptyAddLeadBtn">${escapeHtml(t('workspace.empty_cta'))}</button>`:''}
    </div>`;
    if($('#emptyAddLeadBtn')) $('#emptyAddLeadBtn').onclick=openAddLeadDrawer;
    return;
  }
  const canBulk=!!(boot.canEditLead||boot.canDeleteLead);
  mount.innerHTML = `<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    ${canBulk?`<th class="lead-table__select"><input class="lead-select-box" type="checkbox" id="leadSelectAll" aria-label="${escapeHtml(t('workspace.bulk_select_all'))}"></th>`:''}
    <th class="lead-col--id">${escapeHtml(t('workspace.col_lead_id'))}</th><th class="lead-col--date">${escapeHtml(t('workspace.col_date'))}</th><th class="lead-col--customer">${escapeHtml(t('workspace.col_customer'))}</th>
    <th class="lead-col--phone">${escapeHtml(t('workspace.col_phone'))}</th><th class="lead-col--email">${escapeHtml(t('workspace.col_email'))}</th><th class="lead-col--source">${escapeHtml(t('workspace.col_source'))}</th>
    <th class="lead-col--beautician">${escapeHtml(t('workspace.col_beautician'))}</th><th class="lead-col--branch">${escapeHtml(t('workspace.col_branch'))}</th><th>${escapeHtml(t('workspace.col_status'))}</th>
    <th class="lead-col--payment">${escapeHtml(t('workspace.col_payment'))}</th><th class="is-num lead-col--sales">${escapeHtml(t('workspace.col_sales'))}</th><th class="lead-col--follow-up" title="${escapeHtml(t('workspace.col_last_fu'))}">${escapeHtml(t('workspace.col_last_fu'))}</th>
    <th class="lead-table__actions"><span class="sr-only">${escapeHtml(t('workspace.col_action'))}</span></th>
  </tr></thead><tbody>${rows.map(l=>{
    const name=String(l.name||'');
    const initial=escapeHtml((name[0]||'?').toUpperCase());
    const canEdit=!!boot.canEditLead;
    const canDelete=!!boot.canDeleteLead;
    const email=String(l.email||'').trim();
    const source=String(l.source||'').trim();
    const beautician=String(l.beautician||'').trim();
    const branch=String(l.branch||'').trim();
    const last=String(l.last||'').trim();
    const salesNum=Number(l.sales||0);
    return `<tr data-payment-status="${escapeHtml(l.payment_status||'pending')}">
      ${canBulk?`<td class="lead-table__select"><input class="lead-select-box" type="checkbox" value="${escapeHtml(l.id)}" data-lead-select aria-label="${escapeHtml(t('workspace.bulk_select_lead',{name:name||l.code||l.id}))}"></td>`:''}
      <td class="lead-col--id"><span class="lead-code">${escapeHtml(l.code||l.id)}</span></td>
      <td class="lead-col--date"><span class="lead-date">${escapeHtml(l.date||'—')}</span></td>
      <td>
        <div class="person-cell person-cell--lead">
          <div class="mini-avatar" aria-hidden="true">${initial}</div>
          <div class="person-cell__text">
            <strong>${escapeHtml(name||'—')}</strong>
            ${l.customer?`<small>${escapeHtml(l.customer)}</small>`:''}
          </div>
        </div>
      </td>
      <td><span class="lead-mono">${escapeHtml(l.phone||'—')}</span></td>
      <td class="lead-col--email">${email?`<span class="lead-email" title="${escapeHtml(email)}">${escapeHtml(email)}</span>`:`<span class="lead-muted">—</span>`}</td>
      <td class="lead-col--source">${source?`<span class="lead-tag">${escapeHtml(source)}</span>`:`<span class="lead-muted">—</span>`}</td>
      <td class="lead-col--beautician">${beautician?escapeHtml(beautician):`<span class="lead-muted">—</span>`}</td>
      <td class="lead-col--branch">${branch?`<span class="lead-branch">${escapeHtml(branch)}</span>`:`<span class="lead-muted">—</span>`}</td>
      <td>${statusBadge(l.status)}</td>
      <td class="lead-col--payment">${statusBadge(l.payment)}</td>
      <td class="is-num"><span class="lead-money${salesNum?'':' is-zero'}">${salesNum?money(salesNum):'RM0'}</span></td>
      <td class="lead-col--follow-up"><span class="lead-date">${last?escapeHtml(last):'—'}</span></td>
      <td class="lead-table__actions">
        <div class="lead-menu">
          <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${escapeHtml(t('workspace.row_actions'))}">
            <span class="lead-menu__dots" aria-hidden="true"></span>
          </button>
          <div class="lead-menu__panel" role="menu" hidden>
            <button type="button" class="lead-menu__item" role="menuitem" data-lead-id="${escapeHtml(l.id)}">${escapeHtml(t('workspace.view'))}</button>
            ${canEdit?`<button type="button" class="lead-menu__item" role="menuitem" data-lead-edit="${escapeHtml(l.id)}">${escapeHtml(t('workspace.edit'))}</button>`:''}
            ${canDelete?`<button type="button" class="lead-menu__item lead-menu__item--danger" role="menuitem" data-lead-del="${escapeHtml(l.id)}">${escapeHtml(t('workspace.delete'))}</button>`:''}
          </div>
        </div>
      </td>
    </tr>`;
  }).join('')}</tbody></table></div>`;
  const pageSizes=[10,50,100,200];
  mount.insertAdjacentHTML('beforeend',`<div class="lead-pagination-bar">
    <label class="lead-page-size" for="leadPerPage">
      <span>${escapeHtml(t('workspace.rows_per_page'))}</span>
      <select id="leadPerPage" class="lead-page-size__select">
        ${pageSizes.map(size=>`<option value="${size}" ${Number(state.leadPerPage)===size?'selected':''}>${size}</option>`).join('')}
      </select>
    </label>
    ${centralPager('leads',leadPageMeta)}
  </div>`);
  const pageSize=$('#leadPerPage',mount);
  if(pageSize) pageSize.onchange=()=>{state.leadPerPage=Number(pageSize.value)||10;state.leadPage=1;pageSize.disabled=true;refreshLeads();};
  bindCentralPager('leads',page=>{state.leadPage=page;return refreshLeads();});
  const selectAll=$('#leadSelectAll',mount);
  if(selectAll)selectAll.onchange=()=>{
    liveLeads.forEach(lead=>selectAll.checked?selectedLeadIds.add(String(lead.id)):selectedLeadIds.delete(String(lead.id)));
    syncLeadSelectionUi();
  };
  $$('[data-lead-select]',mount).forEach(input=>input.onchange=()=>{
    input.checked?selectedLeadIds.add(String(input.value)):selectedLeadIds.delete(String(input.value));
    syncLeadSelectionUi();
  });
  syncLeadSelectionUi();
  bindLeadRowMenus(mount);
  $$('[data-lead-id]',mount).forEach(b=>b.onclick=()=>{ closeAllLeadMenus(); openLead(b.dataset.leadId); });
  $$('[data-lead-edit]',mount).forEach(b=>b.onclick=()=>{ closeAllLeadMenus(); openEditLeadDrawer(b.dataset.leadEdit); });
  $$('[data-lead-del]',mount).forEach(b=>b.onclick=()=>{ closeAllLeadMenus(); deleteLead(b.dataset.leadDel); });
}
function resetLeadMenuPanelStyle(panel){
  if(!panel) return;
  panel.classList.remove('is-up');
  panel.style.top='';
  panel.style.left='';
  panel.style.right='';
  panel.style.bottom='';
}
function positionLeadMenuPanel(btn, panel){
  if(!btn||!panel) return;
  const gap=4;
  const btnRect=btn.getBoundingClientRect();
  panel.style.top='0px';
  panel.style.left='0px';
  panel.style.right='auto';
  panel.style.bottom='auto';
  const panelRect=panel.getBoundingClientRect();
  const spaceBelow=window.innerHeight-btnRect.bottom;
  const flipUp=spaceBelow<(panelRect.height+gap+8);
  panel.classList.toggle('is-up', flipUp);
  let top=flipUp ? (btnRect.top-panelRect.height-gap) : (btnRect.bottom+gap);
  let left=btnRect.right-panelRect.width;
  left=Math.max(8, Math.min(left, window.innerWidth-panelRect.width-8));
  top=Math.max(8, Math.min(top, window.innerHeight-panelRect.height-8));
  panel.style.top=`${Math.round(top)}px`;
  panel.style.left=`${Math.round(left)}px`;
}
function closeAllLeadMenus(except=null){
  $$('.lead-menu').forEach(menu=>{
    if(except && menu===except) return;
    const btn=$('.lead-menu__btn',menu);
    const panel=$('.lead-menu__panel',menu);
    if(btn) btn.setAttribute('aria-expanded','false');
    if(panel){
      panel.hidden=true;
      resetLeadMenuPanelStyle(panel);
    }
    menu.classList.remove('is-open');
  });
}
function onLeadMenuOutside(e){
  if(e.target.closest && e.target.closest('.lead-menu')) return;
  closeAllLeadMenus();
}
function onLeadMenuEscape(e){
  if(e.key==='Escape') closeAllLeadMenus();
}
function onLeadMenuViewportChange(){
  closeAllLeadMenus();
}
function bindLeadRowMenus(mount){
  $$('[data-lead-menu]',mount).forEach(btn=>{
    btn.onclick=e=>{
      e.stopPropagation();
      const menu=btn.closest('.lead-menu');
      const panel=$('.lead-menu__panel',menu);
      const open=btn.getAttribute('aria-expanded')==='true';
      closeAllLeadMenus(open?null:menu);
      if(open){
        btn.setAttribute('aria-expanded','false');
        if(panel){
          panel.hidden=true;
          resetLeadMenuPanelStyle(panel);
        }
        menu.classList.remove('is-open');
      } else {
        btn.setAttribute('aria-expanded','true');
        if(panel){
          panel.hidden=false;
          positionLeadMenuPanel(btn, panel);
        }
        menu.classList.add('is-open');
      }
    };
  });
  document.removeEventListener('click', onLeadMenuOutside);
  document.addEventListener('click', onLeadMenuOutside);
  document.removeEventListener('keydown', onLeadMenuEscape);
  document.addEventListener('keydown', onLeadMenuEscape);
  window.removeEventListener('scroll', onLeadMenuViewportChange, true);
  window.addEventListener('scroll', onLeadMenuViewportChange, true);
  window.removeEventListener('resize', onLeadMenuViewportChange);
  window.addEventListener('resize', onLeadMenuViewportChange);
}
function openAddLeadDrawer(){
  editingLeadId=null;
  openActionDrawer(
    t('workspace.manual_entry'),
    t('workspace.manual_sub'),
    leadFormFields({}, false),
    `<button class="btn" type="button" data-action-drawer-close>${escapeHtml(t('workspace.cancel'))}</button><button class="btn primary" type="button" id="saveLead">${escapeHtml(t('workspace.save'))}</button>`,
    t('workspace.title')
  );
}
function openEditLeadDrawer(id){
  const l=liveLeads.find(x=>String(x.id)===String(id));
  if(!l){ openLead(id); return; }
  editingLeadId=l.id;
  openActionDrawer(
    t('workspace.edit_entry'),
    t('workspace.edit_sub'),
    leadFormFields(l, true),
    `<button class="btn" type="button" data-action-drawer-close>${escapeHtml(t('workspace.cancel'))}</button><button class="btn primary" type="button" id="saveLead">${escapeHtml(t('workspace.save_changes'))}</button>`,
    t('workspace.title')
  );
}
function leadFormFields(l={}, isEdit=false){
  const statuses=(liveLeadFilters.statuses||[]).map(s=>`<option value="${escapeHtml(s.value)}" ${String(l.status_key||'')===String(s.value)?'selected':''}>${escapeHtml(s.label)}</option>`).join('');
  const branches=[{id:'',name:'—'}, ...((liveLeadFilters.branches)||[])].map(b=>`<option value="${escapeHtml(b.id)}" ${String(l.branch_id||'')===String(b.id)?'selected':''}>${escapeHtml(b.name)}</option>`).join('');
  const beauticians=[{id:'',name:'—'}, ...((liveLeadFilters.beauticians)||[])].map(b=>`<option value="${escapeHtml(b.id)}" ${String(l.beautician_id||'')===String(b.id)?'selected':''}>${escapeHtml(b.name)}</option>`).join('');
  const source=String(l.source||'manual');
  const initial=escapeHtml((String(l.name||'?')[0]||'?').toUpperCase());
  const leadContext=isEdit?`<section class="lead-editor__profile" aria-label="${escapeHtml(t('workspace.lead_profile'))}">
      <span class="lead-editor__avatar" aria-hidden="true">${initial}</span>
      <div><strong>${escapeHtml(l.name||'—')}</strong><span>${escapeHtml(l.code||l.id||'—')} · ${escapeHtml(t('workspace.lead_created',{date:l.date||'—'}))}</span></div>
      <div class="lead-editor__status">${statusBadge(l.status)}</div>
    </section>`:'';
  return `<form class="lead-editor" id="leadEditorForm" novalidate>
    ${leadContext}
    <div class="lead-editor__error" id="leadFormError" role="alert" hidden></div>
    <section class="lead-editor__section" aria-labelledby="leadContactTitle">
      <div class="lead-editor__section-head"><h4 id="leadContactTitle">${escapeHtml(t('workspace.contact_details'))}</h4><p>${escapeHtml(t('workspace.contact_hint'))}</p></div>
      <div class="lead-editor__grid">
        <div class="lead-editor__field"><label for="mName">${escapeHtml(t('workspace.name'))}</label><input class="search" id="mName" autocomplete="name" required value="${escapeHtml(l.name||'')}"></div>
        <div class="lead-editor__field"><label for="mPhone">${escapeHtml(t('workspace.phone'))}</label><input class="search" id="mPhone" type="tel" inputmode="tel" autocomplete="tel" required value="${escapeHtml(l.phone||'')}"></div>
        <div class="lead-editor__field lead-editor__field--full"><label for="mEmail">${escapeHtml(t('workspace.email'))}</label><input class="search" id="mEmail" type="email" inputmode="email" autocomplete="email" value="${escapeHtml(l.email||'')}"></div>
      </div>
    </section>
    <section class="lead-editor__section" aria-labelledby="leadQualificationTitle">
      <div class="lead-editor__section-head"><h4 id="leadQualificationTitle">${escapeHtml(t('workspace.qualification'))}</h4><p>${escapeHtml(t('workspace.qualification_hint'))}</p></div>
      <div class="lead-editor__grid">
        <div class="lead-editor__field"><label for="mSource">${escapeHtml(t('workspace.source'))}</label><select class="control" id="mSource">${['manual','TikTok','WhatsApp','Facebook','import'].map(s=>`<option value="${s}" ${source===s?'selected':''}>${s}</option>`).join('')}</select></div>
        <div class="lead-editor__field"><label for="mStatus">${escapeHtml(t('workspace.col_status'))}</label><select class="control" id="mStatus">${statuses||`<option value="new">NEW</option>`}</select></div>
      </div>
    </section>
    <section class="lead-editor__section" aria-labelledby="leadOwnershipTitle">
      <div class="lead-editor__section-head"><h4 id="leadOwnershipTitle">${escapeHtml(t('workspace.ownership'))}</h4><p>${escapeHtml(t('workspace.ownership_hint'))}</p></div>
      <div class="lead-editor__grid">
        <div class="lead-editor__field"><label for="mBranch">${escapeHtml(t('workspace.col_branch'))}</label><select class="control" id="mBranch">${branches}</select></div>
        <div class="lead-editor__field"><label for="mBeautician">${escapeHtml(t('workspace.col_beautician'))}</label><select class="control" id="mBeautician">${beauticians}</select></div>
      </div>
    </section>
  </form>`;
}
function setLeadFormError(message=''){
  const error=$('#leadFormError');
  if(!error) return;
  error.hidden=!message;
  error.textContent=message;
}
async function saveLeadFromDrawer(){
  const isEdit=editingLeadId!=null;
  const url=isEdit ? leadUrl(boot.leadUpdateUrlTemplate, editingLeadId) : boot.leadStoreUrl;
  if(!url){ showToast(t('workspace.save_error')); return; }
  const payload={
    name: ($('#mName')?.value||'').trim(),
    phone: ($('#mPhone')?.value||'').trim(),
    email: ($('#mEmail')?.value||'').trim()||null,
    source: $('#mSource')?.value||'manual',
    status: $('#mStatus')?.value||undefined,
    spa_branch_id: $('#mBranch')?.value ? Number($('#mBranch').value) : null,
    beautician_id: $('#mBeautician')?.value ? Number($('#mBeautician').value) : null
  };
  if(!payload.name || !payload.phone){
    const message=t('workspace.required_details');
    setLeadFormError(message);
    showToast(message);
    $('#mName')?.focus({preventScroll:true});
    return;
  }
  setLeadFormError('');
  const button=$('#saveLead');
  const originalLabel=button?.textContent;
  if(button){button.disabled=true;button.setAttribute('aria-busy','true');button.textContent=t('workspace.saving');}
  try{
    const res=await fetch(url,{
      method:isEdit?'PUT':'POST',
      headers:apiHeaders(true),
      credentials:'same-origin',
      body:JSON.stringify(payload)
    });
    const body=await res.json().catch(()=>({}));
    if(!res.ok){
      const msg=(body && (body.message||Object.values(body.errors||{})[0]?.[0]))|| (isEdit?t('workspace.update_error'):t('workspace.save_error'));
      setLeadFormError(msg); showToast(msg); return;
    }
    actionDrawerDirty=false;
    closeActionDrawer();
    editingLeadId=null;
    showToast(body.message|| (isEdit?t('workspace.updated'):t('workspace.saved')));
    if(!isEdit) state.leadPage=1;
    await refreshLeads();
  }catch(err){
    console.error(err);
    const message=isEdit?t('workspace.update_error'):t('workspace.save_error');
    setLeadFormError(message); showToast(message);
  }finally{
    if(button){button.disabled=false;button.removeAttribute('aria-busy');button.textContent=originalLabel||t('workspace.save');}
  }
}
function deleteLead(id){
  if(!boot.canDeleteLead) return;
  openActionDrawer(t('workspace.delete'),t('workspace.delete_confirm'),'',
    `<button type="button" class="btn" data-action-drawer-close>${escapeHtml(t('workspace.cancel'))}</button><button type="button" class="btn danger" id="confirmDeleteLead">${escapeHtml(t('workspace.delete'))}</button>`,t('nav.leads'));
  $('#confirmDeleteLead').onclick=async e=>{
    const button=e.currentTarget;
    button.disabled=true;
    await performDeleteLead(id);
    button.disabled=false;
  };
}
async function performDeleteLead(id){
  if(!boot.canDeleteLead) return;
  const url=leadUrl(boot.leadDestroyUrlTemplate, id);
  if(!url){ showToast(t('workspace.delete_error')); return; }
  try{
    const res=await fetch(url,{method:'DELETE',headers:apiHeaders(false),credentials:'same-origin'});
    const body=await res.json().catch(()=>({}));
    if(!res.ok){ showToast(body.message||t('workspace.delete_error')); return; }
    showToast(body.message||t('workspace.deleted'));
    closeActionDrawer();
    closeDrawer();
    await refreshLeads();
  }catch(err){
    console.error(err);
    showToast(t('workspace.delete_error'));
  }
}
async function openLead(id){
  const local=liveLeads.find(x=>String(x.id)===String(id))
    || liveFollowUps.find(x=>String(x.id)===String(id));
  let l=local;
  const showTpl=boot.leadShowUrlTemplate;
  if(showTpl){
    try{
      const res=await fetch(leadUrl(showTpl,id),{headers:apiHeaders(false),credentials:'same-origin'});
      if(res.ok){
        const json=await res.json();
        l=json.data||l;
        if(json.filters?.statuses) liveLeadFilters.statuses=json.filters.statuses;
      }
    }catch(err){ console.error(err); }
  }
  if(!l) return;
  closeActionDrawer();
  closeDrawer();
  walletDrawerTrigger=document.activeElement;
  walletDrawerScroll=document.body.style.overflow;
  document.body.style.overflow='hidden';
  const eyebrow=$('#leadDrawer .eyebrow');
  if(eyebrow) eyebrow.textContent=t('workspace.drawer_eyebrow');
  $('#drawerName').textContent=l.name||'';
  const analytics=leadJourneyAnalytics(l);
  const statuses=(liveLeadFilters.statuses||[]).map(s=>`<option value="${escapeHtml(s.value)}" ${String(l.status_key)===String(s.value)?'selected':''}>${escapeHtml(s.label)}</option>`).join('');
  const canEdit=!!boot.canEditLead;
  const name=String(l.name||'');
  const initial=escapeHtml((name[0]||'?').toUpperCase());
  $('#drawerBody').innerHTML=`
  <div class="journey">
    <div class="journey-hero">
      <div class="journey-hero__avatar" aria-hidden="true">${initial}</div>
      <div class="journey-hero__meta">
        <div class="journey-hero__code">${escapeHtml(l.code||l.id)}</div>
        <div class="journey-hero__badges">
          ${statusBadge(l.status)}
          <span class="journey-pill journey-pill--${escapeHtml(analytics.healthTone)}">${escapeHtml(analytics.healthLabel)}</span>
          <span class="journey-pill journey-pill--prio-${escapeHtml(analytics.priorityTone)}">${escapeHtml(analytics.priorityLabel)}</span>
        </div>
      </div>
    </div>

    <section class="journey-section">
      <div class="journey-section__title">${escapeHtml(t('workspace.drawer_analytics'))}</div>
      <div class="journey-stats">
        <div class="journey-stat">
          <span class="journey-stat__label">${escapeHtml(t('workspace.drawer_health'))}</span>
          <strong class="journey-stat__value">${analytics.score}</strong>
          <div class="journey-meter"><span style="width:${analytics.score}%"></span></div>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${escapeHtml(t('workspace.drawer_stage'))}</span>
          <strong class="journey-stat__value">${analytics.stagePct}%</strong>
          <div class="journey-meter journey-meter--stage"><span style="width:${analytics.stagePct}%"></span></div>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${escapeHtml(t('workspace.drawer_pipeline_days'))}</span>
          <strong class="journey-stat__value">${escapeHtml(t('workspace.drawer_days',{count:analytics.daysInPipeline}))}</strong>
        </div>
        <div class="journey-stat">
          <span class="journey-stat__label">${escapeHtml(t('workspace.drawer_since_contact'))}</span>
          <strong class="journey-stat__value">${analytics.neverContacted?escapeHtml(t('workspace.drawer_never_contacted')):escapeHtml(t('workspace.drawer_days',{count:analytics.daysSinceContact}))}</strong>
        </div>
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${escapeHtml(t('workspace.drawer_signals'))}</div>
      <div class="journey-signals">
        ${analytics.signals.map(s=>`<span class="journey-signal journey-signal--${escapeHtml(s.tone)}">${escapeHtml(s.label)}</span>`).join('') || `<span class="journey-signal journey-signal--ok">${escapeHtml(t('workspace.drawer_signal_healthy'))}</span>`}
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${escapeHtml(t('workspace.drawer_pipeline'))}</div>
      <div class="journey-pipeline" role="list">${analytics.pipelineHtml}</div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${escapeHtml(t('workspace.drawer_contact'))}</div>
      <div class="journey-kv">
        <div><span>${escapeHtml(t('workspace.col_phone'))}</span><strong>${escapeHtml(l.phone||'—')}</strong></div>
        <div><span>${escapeHtml(t('workspace.col_email'))}</span><strong title="${escapeHtml(l.email||'')}">${escapeHtml(l.email||'—')}</strong></div>
        <div><span>${escapeHtml(t('workspace.drawer_source_label'))}</span><strong>${escapeHtml(l.source||'—')}</strong></div>
        <div><span>${escapeHtml(t('workspace.col_customer'))}</span><strong>${escapeHtml(l.customer||'—')}</strong></div>
      </div>
    </section>

    <section class="journey-section">
      <div class="journey-section__title">${escapeHtml(t('workspace.drawer_assignment'))}</div>
      <div class="journey-kv">
        <div><span>${escapeHtml(t('workspace.col_beautician'))}</span><strong>${escapeHtml(l.beautician||'—')}</strong></div>
        <div><span>${escapeHtml(t('workspace.col_branch'))}</span><strong>${escapeHtml(l.branch||'—')}</strong></div>
        <div><span>${escapeHtml(t('workspace.col_last_fu'))}</span><strong>${escapeHtml(l.last||'—')}</strong></div>
        <div><span>${escapeHtml(t('workspace.col_date'))}</span><strong>${escapeHtml(l.date||'—')}</strong></div>
      </div>
    </section>

    ${canEdit?`<section class="journey-section">
      <div class="journey-section__title">${escapeHtml(t('workspace.update_status'))}</div>
      <div class="journey-status-row">
        <select class="control" id="drawerStatus">${statuses}</select>
        <button class="btn primary" type="button" id="drawerSaveStatus">${escapeHtml(t('workspace.update_status'))}</button>
      </div>
    </section>`:''}

    <div class="journey-actions">
      <div class="journey-section__title">${escapeHtml(t('workspace.drawer_actions'))}</div>
      <div class="journey-actions__row">
        ${canEdit?`<button class="btn primary" type="button" id="drawerFollowBtn">${escapeHtml(t('followup.mark'))}</button>`:''}
        ${canEdit?`<button class="btn" type="button" id="drawerEditBtn">${escapeHtml(t('workspace.edit'))}</button>`:''}
        ${boot.canDeleteLead?`<button class="btn danger" type="button" id="drawerDeleteBtn">${escapeHtml(t('workspace.delete'))}</button>`:''}
      </div>
    </div>
  </div>`;
  $('#leadDrawer').classList.add('show');$('#drawerBackdrop').classList.add('show');$('#leadDrawer').setAttribute('aria-hidden','false');
  $('#leadDrawer').inert=false;
  $('.app-shell').inert=true;
  $('#drawerClose').focus({preventScroll:true});
  bindJump();
  if($('#drawerFollowBtn')) $('#drawerFollowBtn').onclick=()=>markLeadFollowedUp(l.id);
  if($('#drawerEditBtn')) $('#drawerEditBtn').onclick=()=>{ closeDrawer(); openEditLeadDrawer(l.id); };
  if($('#drawerDeleteBtn')) $('#drawerDeleteBtn').onclick=()=>deleteLead(l.id);
  if($('#drawerSaveStatus')){
    $('#drawerSaveStatus').onclick=async()=>{
      const status=$('#drawerStatus')?.value;
      const statusUrl=leadUrl(boot.leadStatusUrlTemplate,l.id);
      if(!status||!statusUrl){ showToast(t('workspace.status_error')); return; }
      try{
        const res=await fetch(statusUrl,{method:'PATCH',headers:apiHeaders(true),credentials:'same-origin',body:JSON.stringify({status})});
        const body=await res.json().catch(()=>({}));
        if(!res.ok){ showToast(body.message||t('workspace.status_error')); return; }
        showToast(body.message||t('workspace.status_updated'));
        if(state.view==='followup') await refreshFollowUps();
        else await refreshLeads();
        openLead(l.id);
      }catch(err){
        console.error(err);
        showToast(t('workspace.status_error'));
      }
    };
  }
}
function leadJourneyAnalytics(l){
  const stages=['new','claimed','follow_up','booking','payment_verified','converted'];
  const key=String(l.status_key||'new');
  const stageIdx=stages.indexOf(key);
  const stagePct=key==='lost'||key==='no_response'
    ? Math.max(10, Math.round(((Math.max(stageIdx,0)+1)/stages.length)*100))
    : Math.round(((Math.max(stageIdx,0)+1)/stages.length)*100);
  const daysInPipeline=Number(l.days_in_pipeline!=null?l.days_in_pipeline:(l.days_since_followup||0));
  const neverContacted=!l.last_followed_up_at;
  const daysSinceContact=Number(l.days_since_followup||0);
  const overdue=String(l.followup_bucket)==='overdue' || (neverContacted?daysInPipeline>=2:daysSinceContact>=2);

  let score=28;
  if(key==='converted') score=96;
  else if(key==='payment_verified') score=82;
  else if(key==='booking') score=68;
  else if(key==='follow_up') score=54;
  else if(key==='claimed') score=42;
  else if(key==='new') score=32;
  else if(key==='no_response') score=22;
  else if(key==='lost') score=12;
  if(l.existing) score+=8;
  if(l.duplicate) score-=6;
  if(overdue) score-=18;
  if(!neverContacted && daysSinceContact===0) score+=6;
  if(l.beautician_id) score+=4;
  if(l.branch_id) score+=3;
  score=Math.max(5, Math.min(99, score));

  let healthTone='warm', healthLabel=t('workspace.drawer_health_warm');
  if(score>=75){ healthTone='hot'; healthLabel=t('workspace.drawer_health_hot'); }
  else if(score<35 || overdue || key==='lost' || key==='no_response'){ healthTone='risk'; healthLabel=t('workspace.drawer_health_risk'); }
  else if(score<50){ healthTone='cold'; healthLabel=t('workspace.drawer_health_cold'); }

  let priorityTone='med', priorityLabel=t('workspace.drawer_priority_medium');
  if(overdue || key==='no_response' || (!l.beautician_id && daysInPipeline>=1)){
    priorityTone='high'; priorityLabel=t('workspace.drawer_priority_high');
  } else if(key==='converted' || key==='payment_verified'){
    priorityTone='low'; priorityLabel=t('workspace.drawer_priority_low');
  }

  const signals=[];
  if(overdue) signals.push({tone:'danger', label:t('workspace.drawer_signal_overdue')});
  if(daysInPipeline<=1 && key==='new') signals.push({tone:'info', label:t('workspace.drawer_signal_fresh')});
  if(l.existing) signals.push({tone:'ok', label:t('workspace.drawer_signal_existing')});
  if(l.duplicate) signals.push({tone:'warn', label:t('workspace.drawer_signal_duplicate')});
  if(!l.beautician_id && key!=='converted') signals.push({tone:'warn', label:t('workspace.drawer_signal_unassigned')});
  if(!l.branch_id && key!=='converted') signals.push({tone:'warn', label:t('workspace.drawer_signal_no_branch')});
  if(!signals.length) signals.push({tone:'ok', label:t('workspace.drawer_signal_healthy')});

  const pipelineHtml=stages.map((s,i)=>{
    const label=(liveLeadFilters.statuses||[]).find(x=>x.value===s)?.label || s.replace(/_/g,' ');
    const done=stageIdx>i || key==='converted';
    const active=stageIdx===i;
    const cls=done?' is-done':(active?' is-active':'');
    return `<div class="journey-step${cls}" role="listitem"><span class="journey-step__dot"></span><span class="journey-step__label">${escapeHtml(label)}</span></div>`;
  }).join('');

  return {score, stagePct, daysInPipeline, daysSinceContact, neverContacted, healthTone, healthLabel, priorityTone, priorityLabel, signals, pipelineHtml};
}

let liveImportPreview = null;
let liveImportHistory = [];
let liveImportSummary = {total_imports:0,raw:0,unique:0,duplicates:0,existing:0,invalid:0,imported:0};
let importBusy = false;

function importView(){
  root.innerHTML=`${pageHead(t('import.title'),t('import.subtitle'),`<button type="button" class="btn" data-jump="imports">${escapeHtml(t('import.history_btn'))}</button><button type="button" class="btn primary" data-jump="leads">${escapeHtml(t('import.view_leads'))}</button>`)}
  <section class="card">
    <div class="tabs" id="importTabs" role="tablist">${[['paste',t('import.tab_paste')],['excel',t('import.tab_excel')],['csv',t('import.tab_csv')],['manual',t('import.tab_manual')]].map(x=>`<button type="button" class="tab ${state.importTab===x[0]?'active':''}" role="tab" aria-selected="${state.importTab===x[0]?'true':'false'}" data-tab="${x[0]}">${escapeHtml(x[1])}</button>`).join('')}</div>
    <div id="importPane" style="margin-top:14px"></div>
  </section>
  <section class="card hidden" id="previewCard" style="margin-top:12px"></section>`;
  renderImportPane();
  bindJump();
  bindImportTabs();
}
function bindImportTabs(){
  const tabs = $$('[data-tab]', '#importTabs');
  tabs.forEach(btn=>{
    btn.onclick=(e)=>{
      e.preventDefault();
      const next = btn.dataset.tab;
      if(!next || next===state.importTab) return;
      state.importTab = next;
      tabs.forEach(x=>{
        const on = x===btn;
        x.classList.toggle('active', on);
        x.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      liveImportPreview = null;
      const pc = $('#previewCard');
      if(pc) pc.classList.add('hidden');
      renderImportPane();
    };
  });
}
function renderImportPane(){
  const pane=$('#importPane'); if(!pane)return;
  const canCreate=!!(boot.canCreateLead);
  if(state.importTab==='paste'){
    pane.innerHTML=`<div><div class="card-title">${escapeHtml(t('import.paste_title'))}</div><div class="card-subtitle">${escapeHtml(t('import.paste_sub'))}</div><textarea class="paste-area" id="pasteArea" placeholder="${escapeHtml(t('import.paste_placeholder'))}"></textarea><div style="display:flex;justify-content:flex-end;margin-top:10px"><button type="button" class="btn primary" id="parseBtn" ${canCreate?'':'disabled'}>${escapeHtml(t('import.parse'))}</button></div></div>`;
    if($('#parseBtn')) $('#parseBtn').onclick=()=>previewPaste();
  } else if(state.importTab==='manual'){
    pane.innerHTML=`<div class="detail-grid"><div><label class="kpi-label" for="manualName">${escapeHtml(t('import.name'))}</label><input class="search" style="width:100%" id="manualName" autocomplete="name"></div><div><label class="kpi-label" for="manualPhone">${escapeHtml(t('import.phone'))}</label><input class="search" style="width:100%" id="manualPhone" inputmode="tel" autocomplete="tel"></div><div><label class="kpi-label" for="manualEmail">${escapeHtml(t('import.email'))}</label><input class="search" style="width:100%" id="manualEmail" type="email" autocomplete="email"></div><div><label class="kpi-label" for="manualSource">${escapeHtml(t('import.source'))}</label><select class="control" style="width:100%" id="manualSource"><option value="TikTok">TikTok</option><option value="WhatsApp">WhatsApp</option><option value="Facebook">Facebook</option><option value="manual">Import</option></select></div><div style="grid-column:1/-1;text-align:right"><button type="button" class="btn primary" id="manualSaveBtn" ${canCreate?'':'disabled'}>${escapeHtml(t('import.save_lead'))}</button></div></div>`;
    if($('#manualSaveBtn')) $('#manualSaveBtn').onclick=saveManualImportLead;
  } else {
    const isExcel=state.importTab==='excel';
    pane.innerHTML=`<div class="import-zone" id="importDropZone" tabindex="0"><div class="import-icon">⇧</div><h3>${escapeHtml(isExcel?t('import.drop_excel'):t('import.drop_csv'))}</h3><p>${escapeHtml(isExcel?t('import.accepted_excel'):t('import.accepted_csv'))}</p><input type="file" id="fileInput" class="hidden" accept="${isExcel?'.xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel':'.csv,text/csv,text/plain'}"><button type="button" class="btn primary" id="browseBtn" ${canCreate?'':'disabled'}>${escapeHtml(t('import.browse'))}</button></div>`;
    const input=$('#fileInput');
    const browse=$('#browseBtn');
    const zone=$('#importDropZone');
    if(browse && input) browse.onclick=(e)=>{ e.preventDefault(); e.stopPropagation(); input.click(); };
    if(input) input.onchange=()=>previewFile(input.files?.[0]);
    if(zone && canCreate){
      zone.addEventListener('click',(e)=>{
        if(e.target===browse || browse?.contains(e.target)) return;
        input?.click();
      });
      ;['dragenter','dragover'].forEach(ev=>zone.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();zone.classList.add('is-dragover')}));
      ;['dragleave','drop'].forEach(ev=>zone.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();zone.classList.remove('is-dragover')}));
      zone.addEventListener('drop',e=>{
        const file=e.dataTransfer?.files?.[0];
        if(file) previewFile(file);
      });
      zone.addEventListener('keydown',e=>{
        if(e.key==='Enter'||e.key===' '){ e.preventDefault(); input?.click(); }
      });
    }
  }
}
async function previewPaste(){
  const paste=$('#pasteArea')?.value||'';
  if(!paste.trim()){ showToast(t('import.paste_required')); return; }
  await runImportPreview({method:'paste', paste});
}
async function previewFile(file){
  if(!file){ showToast(t('import.file_required')); return; }
  const fd=new FormData();
  fd.append('method', state.importTab==='excel'?'excel':'csv');
  fd.append('file', file);
  if(state.branch && state.branch!=='all') fd.append('spa_branch_id', state.branch);
  await runImportPreview(fd, true);
}
async function saveManualImportLead(){
  const name=($('#manualName')?.value||'').trim();
  const phone=($('#manualPhone')?.value||'').trim();
  const email=($('#manualEmail')?.value||'').trim();
  const source=($('#manualSource')?.value||'manual').trim();
  if(!name||!phone){ showToast(t('import.rows_required')); return; }
  await runImportPreview({method:'manual', rows:[{name,phone,email:email||null,source}]});
}
async function runImportPreview(payload, isForm=false){
  const url=boot.importPreviewUrl;
  if(!url){ showToast(t('import.preview_error')); return; }
  if(importBusy) return;
  importBusy=true;
  showToast(t('import.parsing'));
  try{
    const opts={method:'POST', credentials:'same-origin', headers: isForm ? apiHeaders(false) : apiHeaders(true)};
    if(!isForm){
      if(state.branch && state.branch!=='all' && !payload.spa_branch_id) payload.spa_branch_id=Number(state.branch)||null;
      opts.body=JSON.stringify(payload);
    } else {
      opts.body=payload;
    }
    const res=await fetch(url, opts);
    const body=await res.json().catch(()=>({}));
    if(!res.ok){
      const msg=body.message || Object.values(body.errors||{}).flat()[0] || t('import.preview_error');
      showToast(msg);
      return;
    }
    liveImportPreview=body.data||null;
    renderImportPreview();
  }catch(err){
    console.error(err);
    showToast(t('import.preview_error'));
  }finally{
    importBusy=false;
  }
}
function renderImportPreview(){
  const pc=$('#previewCard'); if(!pc||!liveImportPreview) return;
  const rows=liveImportPreview.rows||[];
  const s=liveImportPreview.summary||{};
  const ready=Number(s.ready||0)+Number(s.existing||0);
  pc.classList.remove('hidden');
  pc.innerHTML=`<div class="card-title-row"><div><div class="card-title">${escapeHtml(t('import.preview_title'))}</div><div class="card-subtitle">${escapeHtml(t('import.preview_sub'))}</div></div><button class="btn small" type="button" id="closePreviewBtn">${escapeHtml(t('import.close'))}</button></div>
  <div class="summary-strip">
    <div class="summary-chip"><label>${escapeHtml(t('import.total_rows'))}</label><strong>${fmtInt(s.total||0)}</strong></div>
    <div class="summary-chip"><label>${escapeHtml(t('import.ready'))}</label><strong>${fmtInt(s.ready||0)}</strong></div>
    <div class="summary-chip"><label>${escapeHtml(t('import.duplicate'))}</label><strong>${fmtInt(s.duplicate||0)}</strong></div>
    <div class="summary-chip"><label>${escapeHtml(t('import.existing'))}</label><strong>${fmtInt(s.existing||0)}</strong></div>
    <div class="summary-chip"><label>${escapeHtml(t('import.invalid'))}</label><strong>${fmtInt(s.invalid||0)}</strong></div>
  </div>
  <div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${escapeHtml(t('import.col_row'))}</th><th>${escapeHtml(t('import.col_name'))}</th>
    <th title="${escapeHtml(t('import.col_orig_phone'))}">${escapeHtml(t('import.col_orig_phone'))}</th>
    <th title="${escapeHtml(t('import.col_norm_phone'))}">${escapeHtml(t('import.col_norm_phone'))}</th>
    <th>${escapeHtml(t('import.col_email'))}</th><th>${escapeHtml(t('import.col_detection'))}</th><th>${escapeHtml(t('import.col_action'))}</th>
  </tr></thead><tbody>
  ${rows.map(r=>`<tr><td>${r.row}</td><td><strong>${escapeHtml(r.name||'')}</strong></td><td>${escapeHtml(r.phone_orig||'')}</td><td>${escapeHtml(r.phone_e164||r.phone_norm||'')}</td><td>${escapeHtml(r.email||'')}</td><td>${statusBadge(r.detection)}</td><td>${r.detection==='READY'||r.detection==='EXISTING'?`<span class="badge success">${escapeHtml(t('import.action_import'))}</span>`:`<span class="badge gray">${escapeHtml(t('import.action_skip'))}</span>`}</td></tr>`).join('')||`<tr><td colspan="7"><div class="empty">${escapeHtml(t('import.empty'))}</div></td></tr>`}
  </tbody></table></div>
  <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px"><button class="btn" type="button" id="cancelImportBtn">${escapeHtml(t('import.cancel'))}</button><button class="btn primary" type="button" id="confirmImport" ${ready>0?'':'disabled'}>${escapeHtml(t('import.confirm',{count:ready}))}</button></div>`;
  $('#closePreviewBtn')?.addEventListener('click',()=>pc.classList.add('hidden'));
  $('#cancelImportBtn')?.addEventListener('click',()=>pc.classList.add('hidden'));
  $('#confirmImport')?.addEventListener('click', confirmImportPreview);
  pc.scrollIntoView({behavior:'smooth',block:'start'});
}
async function confirmImportPreview(){
  if(!liveImportPreview||importBusy) return;
  const url=boot.importConfirmUrl;
  if(!url){ showToast(t('import.confirm_error')); return; }
  const rows=(liveImportPreview.rows||[]).map(r=>({
    name:r.name,
    phone:r.phone_norm||r.phone_orig,
    email:r.email||null,
    source:r.source||liveImportPreview.source||'import',
    import: r.detection==='READY'||r.detection==='EXISTING'
  }));
  importBusy=true;
  showToast(t('import.importing'));
  try{
    const res=await fetch(url,{
      method:'POST',
      credentials:'same-origin',
      headers:apiHeaders(true),
      body:JSON.stringify({
        method: liveImportPreview.method||'paste',
        rows,
        source: liveImportPreview.source||'import',
        file_name: liveImportPreview.file_name||null,
        spa_branch_id: liveImportPreview.spa_branch_id||null,
        beautician_id: liveImportPreview.beautician_id||null
      })
    });
    const body=await res.json().catch(()=>({}));
    if(!res.ok){
      showToast(body.message||t('import.confirm_error'));
      return;
    }
    showToast(body.message||t('import.imported',{count:(body.data&&body.data.imported)||0}));
    liveImportPreview=null;
    setTimeout(()=>navigate('leads'),700);
  }catch(err){
    console.error(err);
    showToast(t('import.confirm_error'));
  }finally{
    importBusy=false;
  }
}

async function imports(){
  root.innerHTML=`${pageHead(t('import.history_title'),t('import.history_subtitle'),`<button class="btn primary" data-jump="import">${escapeHtml(t('import.new_import'))}</button>`)}
  <div class="grid kpi-grid" id="importKpiMount"></div>
  <section class="card" style="margin-top:12px"><div id="importHistoryMount"><div class="empty">${escapeHtml(t('workspace.loading'))}</div></div></section>`;
  bindJump();
  await refreshImportHistory();
}
async function refreshImportHistory(){
  const url=boot.importHistoryUrl;
  const mount=$('#importHistoryMount');
  const kpiMount=$('#importKpiMount');
  if(!url){ if(mount) mount.innerHTML=`<div class="empty">${escapeHtml(t('import.load_error'))}</div>`; return; }
  try{
    const res=await fetch(url+'?per_page=50&page='+importPage,{headers:apiHeaders(false),credentials:'same-origin'});
    const body=await res.json().catch(()=>({}));
    if(!res.ok){ showToast(body.message||t('import.load_error')); return; }
    liveImportHistory=body.data||[];
    liveImportSummary=body.meta?.summary||liveImportSummary;
    const s=liveImportSummary;
    if(kpiMount){
      kpiMount.innerHTML=`${kpi('▤',t('import.kpi_total'),fmtInt(s.total_imports),t('common.this_month'),t('import.meta_batches'),'blue')}${kpi('♙',t('import.kpi_raw'),fmtInt(s.raw),t('import.meta_historical'),'','rose')}${kpi('✓',t('import.kpi_unique'),fmtInt(s.unique),t('import.meta_after_clean'),'','green')}${kpi('⧉',t('import.kpi_duplicates'),fmtInt(s.duplicates),t('import.meta_auditable'),'','purple')}${kpi('♧',t('import.kpi_existing'),fmtInt(s.existing),t('import.meta_phone'),'','blue')}`;
    }
    if(!mount) return;
    if(!liveImportHistory.length){
      mount.innerHTML=`<div class="empty"><strong>${escapeHtml(t('import.empty'))}</strong>${escapeHtml(t('import.empty_hint'))}</div>`;
      return;
    }
    mount.innerHTML=`<div class="table-wrap"><table class="data-table"><thead><tr>
      <th>${escapeHtml(t('import.col_batch'))}</th><th>${escapeHtml(t('import.col_date'))}</th>
      <th title="${escapeHtml(t('import.col_by'))}">${escapeHtml(t('import.col_by'))}</th>
      <th>${escapeHtml(t('import.col_method'))}</th><th>${escapeHtml(t('import.col_file'))}</th>
      <th>${escapeHtml(t('import.col_raw'))}</th><th>${escapeHtml(t('import.col_unique'))}</th>
      <th>${escapeHtml(t('import.col_duplicate'))}</th><th>${escapeHtml(t('import.col_existing'))}</th>
      <th>${escapeHtml(t('import.col_invalid'))}</th><th>${escapeHtml(t('import.col_status'))}</th>
    </tr></thead><tbody>
    ${liveImportHistory.map(r=>`<tr>
      <td><strong>${escapeHtml(r.batch_code||'')}</strong></td><td>${escapeHtml(r.date||'')}</td>
      <td>${escapeHtml(r.by||'')}</td><td>${escapeHtml(r.method||'')}</td><td>${escapeHtml(r.file||'')}</td>
      <td>${fmtInt(r.raw)}</td><td>${fmtInt(r.unique)}</td><td>${fmtInt(r.duplicate)}</td>
      <td>${fmtInt(r.existing)}</td><td>${fmtInt(r.invalid)}</td><td>${statusBadge(r.status)}</td>
    </tr>`).join('')}
    </tbody></table></div>${centralPager('imports',body.meta||{})}`;
    bindCentralPager('imports',page=>{importPage=page;return refreshImportHistory();});
  }catch(err){
    console.error(err);
    if(mount) mount.innerHTML=`<div class="empty">${escapeHtml(t('import.load_error'))}</div>`;
  }
}

function payments(){
  const customerChip = state.paymentCustomerId
    ? `<div class="pay-customer-chip" id="payCustomerChip">
        <span>${escapeHtml(t('payments.filtered_customer', {name: state.paymentCustomerLabel || ('#'+state.paymentCustomerId)}))}</span>
        <button type="button" class="btn small soft" id="payClearCustomer">${escapeHtml(t('payments.clear_customer'))}</button>
      </div>`
    : '';
  root.innerHTML = `<div class="pay-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${escapeHtml(t('payments.title'))}</h1>
        <div class="page-subtitle">${escapeHtml(t('payments.subtitle'))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="payRefresh">${escapeHtml(t('payments.refresh'))}</button>
        ${boot.canViewOrder && boot.ordersIndexUrl ? `<a class="btn primary" href="${escapeHtml(boot.ordersIndexUrl)}" target="_blank" rel="noopener">${escapeHtml(t('payments.open_orders'))}</a>` : ''}
      </div>
    </div>
    ${customerChip}
    <section class="clearance-flow payment-flow" aria-labelledby="paymentFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CONTROL</span><h2 id="paymentFlowTitle">${escapeHtml(t('payments.workflow_title'))}</h2><p>${escapeHtml(t('payments.workflow_hint'))}</p></div>
      <ol class="clearance-flow__steps">${[t('payments.step_booked'),t('payments.step_verify'),t('payments.step_paid')].map((label,index)=>`<li><span aria-hidden="true">${index+1}</span><strong>${escapeHtml(label)}</strong></li>`).join('')}</ol>
    </section>
    <div class="pay-metrics" id="payHeroStats"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro">
          <p class="lead-panel__sub" id="payHeroCopy" style="margin:0">${escapeHtml(state.paymentCustomerId ? t('payments.filtered_customer', {name: state.paymentCustomerLabel || ('#'+state.paymentCustomerId)}) : t('payments.subtitle'))}</p>
        </div>
        <div class="lead-panel__head-meta">
          <span class="lead-panel__count" id="payResultCount">—</span>
        </div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="payTabs" role="tablist"></div>
        <label class="lead-search" for="paySearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="paySearch" type="search" autocomplete="off" placeholder="${escapeHtml(t('payments.search_placeholder'))}" value="${escapeHtml(state.paymentSearch||'')}" />
        </label>
        <div class="lead-filter-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
          <label class="lead-field">
            <span class="lead-field__label">${escapeHtml(t('payments.filter_beautician'))}</span>
            <select class="lead-field__control" id="payBeautician"></select>
          </label>
          <label class="lead-field">
            <span class="lead-field__label">${escapeHtml(t('payments.filter_branch'))}</span>
            <select class="lead-field__control" id="payBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="payTableMount"></div>
    </section>
  </div>`;
  const refreshBtn = $('#payRefresh');
  if(refreshBtn) refreshBtn.onclick = () => refreshPayments();
  const clearCustomer = $('#payClearCustomer');
  if(clearCustomer) clearCustomer.onclick = () => clearPaymentCustomerFilter();
  bindPaymentFilters();
  refreshPayments();
}

function clearPaymentCustomerFilter(){
  state.paymentCustomerId = null;
  state.paymentCustomerLabel = '';
  state.paymentSearch = '';
  state.paymentPage = 1;
  if(state.view === 'payments') payments();
  else refreshPayments();
}

function jumpToCustomerPayments(customer){
  if(!customer) return;
  const id = Number(customer.id || 0);
  if(!id) return;
  state.paymentCustomerId = id;
  state.paymentCustomerLabel = String(customer.name || customer.code || ('#'+id));
  state.paymentSearch = String(customer.phone || customer.email || customer.name || '').trim();
  state.paymentTab = 'all';
  state.paymentPage = 1;
  state.paymentBeautician = 'all';
  navigate('payments');
}

function bindPaymentFilters(){
  const search = $('#paySearch');
  if(search){
    search.oninput = () => {
      clearTimeout(paymentSearchTimer);
      paymentSearchTimer = setTimeout(() => {
        state.paymentSearch = search.value.trim();
        state.paymentPage = 1;
        refreshPayments();
      }, 320);
    };
  }
  const beau = $('#payBeautician');
  if(beau) beau.onchange = () => { state.paymentBeautician = beau.value; state.paymentPage = 1; refreshPayments(); };
  const branch = $('#payBranch');
  if(branch) branch.onchange = () => { state.paymentBranch = branch.value; state.paymentPage = 1; refreshPayments(); };
}

async function refreshPayments(){
  const url = boot.paymentsUrl || '';
  const mount = $('#payTableMount');
  if(!url){
    if(mount) mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('payments.load_error'))}</strong></div>`;
    return;
  }
  paymentsLoading = true;
  renderPaymentHero();
  renderPaymentTable();
  const qs = new URLSearchParams();
  if(state.paymentCustomerId) qs.set('customer_id', String(state.paymentCustomerId));
  else if(state.paymentSearch) qs.set('q', state.paymentSearch);
  if(state.paymentTab && state.paymentTab !== 'all') qs.set('status', state.paymentTab);
  const branch = state.paymentBranch !== 'all' ? state.paymentBranch : (state.branch || 'all');
  if(branch && branch !== 'all') qs.set('branch', branch);
  if(state.paymentBeautician && state.paymentBeautician !== 'all') qs.set('beautician', state.paymentBeautician);
  if(state.period) qs.set('period', state.period);
  qs.set('page', String(state.paymentPage || 1));
  qs.set('per_page', '25');
  try{
    const res = await fetch(`${url}?${qs.toString()}`, {
      headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
      credentials: 'same-origin'
    });
    if(!res.ok) throw new Error('payments '+res.status);
    const json = await res.json();
    livePayments = Array.isArray(json.data) ? json.data : [];
    livePaymentSummary = Object.assign({pending:0,processing:0,paid:0,paid_today:0,hold:0,refunded:0,paid_amount:0,pending_amount:0,queue:0}, (json.meta && json.meta.summary) || {});
    livePaymentFilters = Object.assign({statuses:[],beauticians:[],branches:[]}, json.filters || {});
    paymentMeta = {
      current_page: (json.meta && json.meta.current_page) || 1,
      last_page: (json.meta && json.meta.last_page) || 1,
      total: (json.meta && json.meta.total) || 0
    };
  }catch(err){
    console.error(err);
    livePayments = [];
    showToast(t('payments.load_error'));
  }finally{
    paymentsLoading = false;
    renderPaymentHero();
    renderPaymentTabs();
    renderPaymentFilterOptions();
    renderPaymentTable();
  }
}

function paymentPipelineState(status){
  const s = String(status || 'pending');
  if(s === 'paid') return {customer:'done', accountant:'done', hq:'done', active:null};
  if(s === 'processing') return {customer:'done', accountant:'done', hq:'active', active:'hq'};
  if(s === 'canceled' || s === 'refunded') return {customer:'done', accountant:'active', hq:null, active:'accountant'};
  return {customer:'done', accountant:'active', hq:null, active:'accountant'};
}

function payPipeHtml(status){
  const st = paymentPipelineState(status);
  const node = (key) => {
    const v = st[key];
    const cls = v === 'done' ? 'is-done' : (v === 'active' ? 'is-active' : '');
    return `<span class="pay-pipe__node ${cls}" title="${escapeHtml(t('payments.flow_'+ (key === 'hq' ? 'hq' : key)))}"></span>`;
  };
  const line = (from) => `<span class="pay-pipe__line ${st[from]==='done'?'is-done':''}"></span>`;
  return `<div class="pay-pipe" title="${escapeHtml(t('payments.pipeline'))}">${node('customer')}${line('customer')}${node('accountant')}${line('accountant')}${node('hq')}</div>`;
}

function renderPaymentHero(){
  const s = livePaymentSummary;
  const copy = $('#payHeroCopy');
  if(copy){
    copy.textContent = s.queue > 0
      ? t('payments.pulse_busy', {count: fmtInt(s.queue), amount: money(s.pending_amount)})
      : t('payments.pulse_clear');
  }
  const stats = $('#payHeroStats');
  if(stats){
    stats.innerHTML = `
      <div class="pay-metric payment-metric payment-metric--queue"><span>${escapeHtml(t('payments.stat_queue'))}</span><strong>${fmtInt(s.queue)}</strong></div>
      <div class="pay-metric payment-metric payment-metric--paid"><span>${escapeHtml(t('payments.stat_paid'))}</span><strong>${money(s.paid_amount)}</strong></div>
      <div class="pay-metric payment-metric payment-metric--today"><span>${escapeHtml(t('payments.stat_today'))}</span><strong>${fmtInt(s.paid_today)}</strong></div>
      <div class="pay-metric payment-metric payment-metric--hold"><span>${escapeHtml(t('payments.stat_hold'))}</span><strong>${fmtInt(s.hold)}</strong></div>
    `;
  }
}

function renderPaymentTabs(){
  const mount = $('#payTabs');
  if(!mount) return;
  const s = livePaymentSummary;
  const counts = {
    all: (s.pending||0)+(s.processing||0)+(s.paid||0)+(s.hold||0)+(s.refunded||0),
    queue: s.queue||0,
    pending: s.pending||0,
    processing: s.processing||0,
    paid: s.paid||0,
    canceled: s.hold||0,
    refunded: s.refunded||0,
  };
  const tabs = (livePaymentFilters.statuses && livePaymentFilters.statuses.length)
    ? livePaymentFilters.statuses
    : [
      {value:'queue',label:t('payments.tab_queue')},
      {value:'all',label:t('payments.tab_all')},
      {value:'pending',label:t('payments.tab_pending')},
      {value:'processing',label:t('payments.tab_processing')},
      {value:'paid',label:t('payments.tab_paid')},
      {value:'canceled',label:t('payments.tab_hold')},
      {value:'refunded',label:t('payments.tab_refunded')},
    ];
  const ordered = [...tabs].sort((a,b) => (a.value==='queue'?-1:0) - (b.value==='queue'?-1:0));
  mount.innerHTML = ordered.map(tab => {
    const v = tab.value;
    const active = state.paymentTab === v ? 'active' : '';
    const n = counts[v];
    const countHtml = (n !== undefined) ? `<span class="pay-tab-count">${fmtInt(n)}</span>` : '';
    return `<button type="button" class="tab ${active}" role="tab" data-ptab="${escapeHtml(v)}">${escapeHtml(tab.label)}${countHtml}</button>`;
  }).join('');
  $$('[data-ptab]', mount).forEach(btn => {
    btn.onclick = () => {
      state.paymentTab = btn.dataset.ptab;
      state.paymentPage = 1;
      refreshPayments();
    };
  });
}

function renderPaymentFilterOptions(){
  const beau = $('#payBeautician');
  if(beau){
    const cur = state.paymentBeautician;
    beau.innerHTML = `<option value="all">${escapeHtml(t('payments.all_beauticians'))}</option>` +
      (livePaymentFilters.beauticians||[]).map(b => `<option value="${b.id}" ${String(cur)===String(b.id)?'selected':''}>${escapeHtml(b.name)}</option>`).join('');
  }
  const branch = $('#payBranch');
  if(branch){
    const cur = state.paymentBranch;
    branch.innerHTML = `<option value="all">${escapeHtml(t('payments.all_branches'))}</option>` +
      (livePaymentFilters.branches||[]).map(b => `<option value="${b.id}" ${String(cur)===String(b.id)?'selected':''}>${escapeHtml(b.code ? b.code+' · '+b.name : b.name)}</option>`).join('');
  }
}

function renderPaymentTable(){
  const mount = $('#payTableMount');
  const count = $('#payResultCount');
  if(count) count.textContent = t('payments.result_count', {count: fmtInt(paymentMeta.total || livePayments.length)});
  if(!mount) return;
  if(paymentsLoading){
    mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('payments.loading'))}</strong></div>`;
    return;
  }
  if(!livePayments.length){
    mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('payments.empty'))}</strong><span>${escapeHtml(t('payments.empty_hint'))}</span></div>`;
    return;
  }
  const rows = livePayments.map(p => {
    const hasRef = p.ref && p.ref !== '—';
    const chips = [
      p.has_proof ? `<span class="pay-chip pay-chip--ok">${escapeHtml(t('payments.chip_proof'))}</span>` : `<span class="pay-chip pay-chip--muted">${escapeHtml(t('payments.stage_declared'))}</span>`,
      hasRef ? `<span class="pay-chip pay-chip--ok">${escapeHtml(t('payments.chip_ref'))}</span>` : `<span class="pay-chip pay-chip--warn">${escapeHtml(t('payments.chip_no_ref'))}</span>`,
    ].join('');
    return `<tr>
      <td><span class="pay-id">${escapeHtml(p.code || ('ORD-'+p.id))}</span></td>
      <td><div class="person-cell"><div class="mini-avatar">${escapeHtml(p.initial||'?')}</div><div><strong>${escapeHtml(p.customer||'')}</strong><small>${escapeHtml(p.phone||'')}</small></div></div></td>
      <td>${escapeHtml(p.branch||'—')}</td>
      <td>${escapeHtml(p.beautician||'—')}</td>
      <td><div class="pay-amount">${money(p.amount)}</div><div class="pay-method">${escapeHtml(p.payment_method_label||'')}</div></td>
      <td><div class="pay-chips">${chips}</div><div class="pay-method" title="${escapeHtml(t('payments.col_ref'))}">${escapeHtml(p.ref||'—')}</div></td>
      <td>${payPipeHtml(p.payment_status)}</td>
      <td>${statusBadge(p.payment_status_label || p.payment_status)}</td>
      <td><button type="button" class="pay-review-btn" data-payment="${p.id}">${escapeHtml(t('payments.review'))}</button></td>
    </tr>`;
  }).join('');
  const pager = centralPager('pay',paymentMeta);
  mount.innerHTML = `<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${escapeHtml(t('payments.col_id'))}</th>
    <th>${escapeHtml(t('payments.col_customer'))}</th>
    <th>${escapeHtml(t('payments.col_branch'))}</th>
    <th>${escapeHtml(t('payments.col_beautician'))}</th>
    <th>${escapeHtml(t('payments.col_amount'))}</th>
    <th>${escapeHtml(t('payments.col_ref'))}</th>
    <th>${escapeHtml(t('payments.pipeline'))}</th>
    <th>${escapeHtml(t('payments.col_status'))}</th>
    <th>${escapeHtml(t('payments.col_action'))}</th>
  </tr></thead><tbody>${rows}</tbody></table></div>${pager}`;
  $$('[data-payment]', mount).forEach(b => b.onclick = () => reviewPayment(Number(b.dataset.payment)));
  bindCentralPager('pay',page=>{state.paymentPage=page;return refreshPayments();});
}

function paymentProofPreview(payment){
  const proof=payment.proof;
  let content;
  if(!boot.canViewOrder){
    content=`<p class="payment-proof__empty">${escapeHtml(t('payments.proof_access'))}</p>`;
  }else if(!proof?.url){
    content=`<p class="payment-proof__empty">${escapeHtml(t(payment.has_proof?'payments.proof_unavailable':'payments.proof_missing'))}</p>`;
  }else{
    const url=escapeHtml(proof.url),name=escapeHtml(proof.name||t('payments.proof_title'));
    const link=`<a class="btn small" href="${url}" target="_blank" rel="noopener noreferrer">${escapeHtml(t('payments.proof_open'))}</a>`;
    const preview=proof.kind==='image'
      ? `<a class="payment-proof__image" href="${url}" target="_blank" rel="noopener noreferrer"><img id="paymentProofImage" src="${url}" alt="${name}" loading="lazy"></a><p class="payment-proof__empty" id="paymentProofError" hidden>${escapeHtml(t('payments.proof_unavailable'))}</p>`
      : proof.kind==='pdf'
        ? `<object class="payment-proof__pdf" data="${url}" type="application/pdf" aria-label="${name}"><p class="payment-proof__empty">${escapeHtml(t('payments.proof_pdf_hint'))}</p></object>`
        : `<p class="payment-proof__empty">${escapeHtml(t('payments.proof_pdf_hint'))}</p>`;
    content=`${preview}<div class="payment-proof__file"><span>${name}</span>${link}</div>`;
  }
  return `<section class="payment-proof"><h3>${escapeHtml(t('payments.proof_title'))}</h3>${content}</section>`;
}

function reviewPayment(id){
  const p = livePayments.find(x => Number(x.id) === Number(id));
  if(!p) return;
  const orderUrl = leadUrl(boot.orderShowUrlTemplate, p.id);
  const canEdit = !!boot.canEditOrder;
  const canView = !!boot.canViewOrder;
  const checks = ['identity','invoice','method','ref','proof','status'].map(key=>{
    const value=p.checklist?.[key];
    const status=['completed','not_applicable'].includes(value)?value:'pending';
    const icon=status==='completed'?'✓':status==='not_applicable'?'—':'○';
    return `<div class="pay-review__check is-${status}" data-check="${key}"><span class="pay-review__check-icon" aria-hidden="true">${icon}</span><span>${escapeHtml(t('payments.check_'+key))}</span><small>${escapeHtml(t('payments.check_'+status))}</small></div>`;
  }).join('');
  const body = `<div class="pay-review">
    <div class="pay-review__hero">
      <div class="pay-review__avatar">${escapeHtml(p.initial||'?')}</div>
      <div style="min-width:0;flex:1">
        <div class="pay-id">${escapeHtml(p.code||'')}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${escapeHtml(p.customer||'')}</strong>
        <div class="pay-method">${escapeHtml(p.phone||'')} · ${escapeHtml(p.branch_name||p.branch||'')}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px;align-items:center">${statusBadge(p.payment_status_label||p.payment_status)}${payPipeHtml(p.payment_status)}</div>
      </div>
      <div style="text-align:right"><div class="pay-amount">${money(p.amount)}</div><div class="pay-method">${escapeHtml(p.payment_method_label||'')}</div></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${escapeHtml(t('payments.col_customer_stage'))}</label><strong>${escapeHtml(p.customer_stage)}</strong></div>
      <div class="pay-review__card"><label>${escapeHtml(t('payments.col_accountant'))}</label><strong>${escapeHtml(p.accountant_stage)}</strong></div>
      <div class="pay-review__card"><label>${escapeHtml(t('payments.col_hq'))}</label><strong>${escapeHtml(p.hq_stage)}</strong></div>
    </div>
    ${paymentProofPreview(p)}
    ${canEdit ? `<div class="detail-grid">
      <div class="detail-box"><label>${escapeHtml(t('payments.bank_ref'))}</label><input class="control" id="payRefInput" value="${escapeHtml(p.ref==='—'?'':p.ref)}" placeholder="${escapeHtml(t('payments.bank_ref_ph'))}" /></div>
      <div class="detail-box" style="grid-column:span 2"><label>${escapeHtml(t('payments.admin_note'))}</label><input class="control" id="payNoteInput" value="${escapeHtml(p.admin_note||'')}" /></div>
    </div>` : ''}
    <div class="journey-section"><div class="journey-section__title">${escapeHtml(t('payments.checklist'))}</div><div class="pay-review__checks">${checks}</div><p class="pay-review__check-note">${escapeHtml(t('payments.check_note'))}</p></div>
    ${!canView ? `<p class="card-subtitle">${escapeHtml(t('payments.no_order_access'))}</p>` : ''}
  </div>`;
  const foot = [
    canView ? `<a class="btn" href="${escapeHtml(orderUrl)}" target="_blank" rel="noopener">${escapeHtml(t('payments.open_order'))}</a>` : '',
    canEdit ? `<button type="button" class="btn" id="payMarkProcessing">${escapeHtml(t('payments.mark_processing'))}</button>` : '',
    canEdit ? `<button type="button" class="btn danger" id="payMarkHold">${escapeHtml(t('payments.mark_hold'))}</button>` : '',
    canEdit ? `<button type="button" class="btn success" id="payMarkPaid">${escapeHtml(t('payments.mark_paid'))}</button>` : '',
    `<button type="button" class="btn" data-action-drawer-close>${escapeHtml(t('payments.close'))}</button>`,
  ].filter(Boolean).join('');
  openActionDrawer(t('payments.review_title'), t('payments.review_sub', {code: p.code, customer: p.customer}), body, foot, 'Pay Verify');
  const proofImage=$('#paymentProofImage');
  if(proofImage){
    const unavailable=()=>{proofImage.closest('a').hidden=true;$('#paymentProofError').hidden=false;};
    proofImage.onerror=unavailable;
    if(proofImage.complete && !proofImage.naturalWidth) unavailable();
  }
  setTimeout(() => {
    const processing = $('#payMarkProcessing');
    if(processing) processing.onclick = () => updatePaymentStatus(p, 'processing');
    const hold = $('#payMarkHold');
    if(hold) hold.onclick = () => updatePaymentStatus(p, 'canceled');
    const paid = $('#payMarkPaid');
    if(paid) paid.onclick = () => updatePaymentStatus(p, 'paid');
  }, 0);
}

async function updatePaymentStatus(payment, paymentStatus){
  const url = leadUrl(boot.orderPaymentStatusUrlTemplate, payment.id);
  if(!url || !boot.canEditOrder){
    showToast(t('payments.update_error'));
    return;
  }
  const refInput = $('#payRefInput');
  const noteInput = $('#payNoteInput');
  const transactionId = refInput ? refInput.value.trim() : '';
  const adminNote = noteInput ? noteInput.value.trim() : '';
  if(payment.needs_reference && (paymentStatus === 'paid' || paymentStatus === 'processing') && !transactionId && (payment.ref === '—' || !payment.ref)){
    showToast(t('payments.ref_required'));
    return;
  }
  try{
    const res = await fetch(url, {
      method: 'PUT',
      headers: apiHeaders(true),
      credentials: 'same-origin',
      body: JSON.stringify({
        payment_status: paymentStatus,
        transaction_id: transactionId || undefined,
        admin_note: adminNote || undefined,
      })
    });
    const body = await res.json().catch(() => ({}));
    if(!res.ok){
      showToast(body.message || t('payments.update_error'));
      return;
    }
    closeActionDrawer();
    showToast(body.message || t('payments.updated'));
    await refreshPayments();
  }catch(err){
    console.error(err);
    showToast(t('payments.update_error'));
  }
}

function checkin(){
  root.innerHTML = `<div class="cin-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${escapeHtml(t('checkin.title'))}</h1>
        <div class="page-subtitle">${escapeHtml(t('checkin.subtitle'))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="cinRefresh">${escapeHtml(t('checkin.refresh'))}</button>
        ${boot.canConfirmCheckin ? `<button type="button" class="btn primary" id="cinScan">${escapeHtml(t('checkin.scan'))}</button>` : ''}
        ${boot.canViewTreatments && boot.treatmentReservationsUrl ? `<a class="btn" href="${escapeHtml(boot.treatmentReservationsUrl)}" target="_blank" rel="noopener">${escapeHtml(t('checkin.open_crm'))}</a>` : ''}
      </div>
    </div>
    <section class="clearance-flow checkin-flow" aria-labelledby="checkinFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CRM</span><h2 id="checkinFlowTitle">${escapeHtml(t('checkin.workflow_title'))}</h2><p>${escapeHtml(t('checkin.workflow_hint'))}</p></div>
      <ol class="clearance-flow__steps">
        ${[t('checkin.step_booked'),t('checkin.step_checked_in'),t('checkin.step_clearance'),t('checkin.step_treatment')].map((label,index)=>`<li><span aria-hidden="true">${index+1}</span><strong>${escapeHtml(label)}</strong></li>`).join('')}
      </ol>
    </section>
    <div class="pay-metrics" id="cinMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${escapeHtml(t('checkin.subtitle'))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="cinResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="cinTabs" role="group"></div>
        <label class="lead-search" for="cinSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="cinSearch" aria-label="${escapeHtml(t('checkin.search_placeholder'))}" type="search" autocomplete="off" placeholder="${escapeHtml(t('checkin.search_placeholder'))}" value="${escapeHtml(state.checkinSearch||'')}" />
        </label>
        <div class="lead-filter-grid ops-filter-grid">
          <label class="lead-field"><span class="lead-field__label">${escapeHtml(t('checkin.filter_scope'))}</span>
            <select class="lead-field__control" id="cinScope">
              <option value="pipeline"${state.checkinScope==='pipeline'?' selected':''}>${escapeHtml(t('checkin.scope_pipeline'))}</option>
              <option value="day"${state.checkinScope==='day'?' selected':''}>${escapeHtml(t('checkin.scope_day'))}</option>
            </select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${escapeHtml(t('checkin.filter_date'))}</span>
            <input class="lead-field__control" id="cinDate" type="date" value="${escapeHtml(state.checkinDate||'')}"${state.checkinScope==='pipeline'?' disabled':''} />
          </label>
          <label class="lead-field"><span class="lead-field__label">${escapeHtml(t('checkin.filter_beautician'))}</span>
            <select class="lead-field__control" id="cinBeautician"></select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${escapeHtml(t('checkin.filter_branch'))}</span>
            <select class="lead-field__control" id="cinBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="cinTableMount"></div>
    </section>
  </div>`;
  const refreshBtn = $('#cinRefresh'); if(refreshBtn) refreshBtn.onclick = () => refreshCheckins();
  const scanBtn = $('#cinScan'); if(scanBtn) scanBtn.onclick = openCheckinScanner;
  bindCheckinFilters();
  refreshCheckins();
}
function bindCheckinFilters(){
  const search = $('#cinSearch');
  if(search){ search.oninput = () => { clearTimeout(checkinSearchTimer); checkinSearchTimer = setTimeout(() => { state.checkinSearch = search.value.trim(); state.checkinPage = 1; refreshCheckins(); }, 320); }; }
  const scope = $('#cinScope'); if(scope) scope.onchange = () => { state.checkinScope = scope.value; if(scope.value === 'pipeline') state.checkinStatus = 'live'; const date=$('#cinDate'); if(date)date.disabled=scope.value==='pipeline'; state.checkinPage = 1; refreshCheckins(); };
  const date = $('#cinDate'); if(date) date.onchange = () => { state.checkinDate = date.value; state.checkinScope = 'day'; $('#cinScope').value = 'day'; state.checkinPage = 1; refreshCheckins(); };
  const beau = $('#cinBeautician'); if(beau) beau.onchange = () => { state.checkinBeautician = beau.value; state.checkinPage = 1; refreshCheckins(); };
  const branch = $('#cinBranch'); if(branch) branch.onchange = () => { state.checkinBranch = branch.value; state.checkinPage = 1; refreshCheckins(); };
}
async function refreshCheckins(){
  const requestId = ++checkinRequest;
  checkinLoadError = false;
  const url = boot.checkinUrl || '';
  const mount = $('#cinTableMount');
  if(!url){ if(mount) mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('checkin.load_error'))}</strong></div>`; return; }
  checkinsLoading = true; renderCheckinMetrics(); renderCheckinTable();
  const qs = new URLSearchParams();
  if(state.checkinSearch) qs.set('q', state.checkinSearch);
  qs.set('status', state.checkinStatus || 'live');
  qs.set('scope', state.checkinScope || 'day');
  if(state.checkinDate) qs.set('date', state.checkinDate);
  const branch = state.checkinBranch !== 'all' ? state.checkinBranch : (state.branch || 'all');
  if(branch && branch !== 'all') qs.set('branch', branch);
  if(state.checkinBeautician && state.checkinBeautician !== 'all') qs.set('beautician', state.checkinBeautician);
  qs.set('page', String(state.checkinPage || 1)); qs.set('per_page', '25');
  try{
    const res = await fetch(`${url}?${qs.toString()}`, { headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin' });
    if(!res.ok) throw new Error('checkin '+res.status);
    const json = await res.json();
    if(requestId !== checkinRequest) return;
    liveCheckins = Array.isArray(json.data) ? json.data : [];
    liveCheckinSummary = Object.assign({live:0,scheduled:0,waiting:0,in_treatment:0,completed:0,unpaid:0,avg_wait_mins:0}, (json.meta && json.meta.summary) || {});
    liveCheckinFilters = Object.assign({statuses:[],beauticians:[],branches:[]}, json.filters || {});
    checkinMeta = { current_page:(json.meta&&json.meta.current_page)||1, last_page:(json.meta&&json.meta.last_page)||1, total:(json.meta&&json.meta.total)||0 };
  }catch(err){ if(requestId !== checkinRequest) return; checkinLoadError = true; console.error(err); liveCheckins=[]; showToast(t('checkin.load_error')); }
  finally{ if(requestId !== checkinRequest) return; checkinsLoading=false; renderCheckinMetrics(); renderCheckinTabs(); renderCheckinFilterOptions(); renderCheckinTable(); }
}
function renderCheckinMetrics(){
  const el = $('#cinMetrics'); if(!el) return;
  const s = liveCheckinSummary;
  el.setAttribute('aria-busy', String(checkinsLoading));
  const metric = value => checkinsLoading || checkinLoadError ? '—' : fmtInt(value);
  el.innerHTML = `
    <div class="pay-metric checkin-metric checkin-metric--scheduled"><span>${escapeHtml(t('checkin.stat_scheduled'))}</span><strong>${metric(s.scheduled)}</strong></div>
    <div class="pay-metric checkin-metric checkin-metric--waiting"><span>${escapeHtml(t('checkin.stat_waiting'))}</span><strong>${metric(s.waiting)}</strong><small>${escapeHtml(t('checkin.stat_waiting_meta',{minutes:metric(s.avg_wait_mins),unpaid:metric(s.unpaid)}))}</small></div>
    <div class="pay-metric checkin-metric checkin-metric--treatment"><span>${escapeHtml(t('checkin.stat_treatment'))}</span><strong>${metric(s.in_treatment)}</strong></div>
    <div class="pay-metric checkin-metric checkin-metric--done"><span>${escapeHtml(t('checkin.stat_completed'))}</span><strong>${metric(s.completed)}</strong></div>`;
}
function renderCheckinTabs(){
  const mount = $('#cinTabs'); if(!mount) return;
  const s = liveCheckinSummary;
  const tabs = [
    ['live', t('checkin.tab_live'), s.live],
    ['booked', t('checkin.tab_booked'), s.scheduled],
    ['waiting', t('checkin.tab_waiting'), s.waiting],
    ['in_progress', t('checkin.tab_treatment'), s.in_treatment],
    ['completed', t('checkin.tab_completed'), s.completed],
    ['all', t('checkin.tab_all'), null],
  ];
  mount.innerHTML = tabs.filter(([v]) => state.checkinScope !== 'pipeline' || !['completed','all'].includes(v)).map(([v,label,count]) => {
    const active = state.checkinStatus === v ? 'active' : '';
    const badge = count == null ? '' : ` (${fmtInt(count)})`;
    return `<button type="button" class="tab ${active}" aria-pressed="${Boolean(active)}" data-cintab="${v}">${escapeHtml(label)}${badge}</button>`;
  }).join('');
  $$('[data-cintab]', mount).forEach(btn => btn.onclick = () => { state.checkinStatus = btn.dataset.cintab; if(['completed','all'].includes(state.checkinStatus)){ state.checkinScope = 'day'; $('#cinScope').value = 'day'; } state.checkinPage = 1; refreshCheckins(); });
}
function renderCheckinFilterOptions(){
  const beau = $('#cinBeautician');
  if(beau){
    const cur = state.checkinBeautician || 'all';
    beau.innerHTML = `<option value="all">${escapeHtml(t('checkin.all_beauticians'))}</option>` +
      (liveCheckinFilters.beauticians||[]).map(b => `<option value="${b.id}"${String(cur)===String(b.id)?' selected':''}>${escapeHtml(b.name)}</option>`).join('');
  }
  const branch = $('#cinBranch');
  if(branch){
    const cur = state.checkinBranch || 'all';
    branch.innerHTML = `<option value="all">${escapeHtml(t('checkin.all_branches'))}</option>` +
      (liveCheckinFilters.branches||[]).map(b => `<option value="${b.id}"${String(cur)===String(b.id)?' selected':''}>${escapeHtml(b.name)}</option>`).join('');
  }
  const count = $('#cinResultCount');
  if(count) count.textContent = t('checkin.result_count', {count: checkinLoadError ? '—' : fmtInt(checkinMeta.total)});
}
function renderCheckinTable(){
  const mount = $('#cinTableMount'); if(!mount) return;
  mount.setAttribute('aria-busy', String(checkinsLoading));
  if(checkinLoadError){ mount.innerHTML = `<div class="pay-empty" role="alert"><strong>${escapeHtml(t('checkin.load_error'))}</strong><button type="button" class="btn" id="cinRetry">${escapeHtml(t('checkin.refresh'))}</button></div>`; $('#cinRetry').onclick = () => refreshCheckins(); return; }
  if(checkinsLoading){ mount.innerHTML = `<div class="pay-empty" role="status">${escapeHtml(t('checkin.loading'))}</div>`; return; }
  if(!liveCheckins.length){ mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('checkin.empty'))}</strong>${escapeHtml(t('checkin.empty_hint'))}</div>`; return; }
  const rows = liveCheckins.map(c => `<tr data-checkin-status="${escapeHtml(c.status||'pending')}" data-arrival-state="${c.checked_in_at?'arrived':'booked'}">
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${escapeHtml(c.initial||'?')}</div><div class="person-cell__text"><strong>${escapeHtml(c.name||'')}</strong><small>${escapeHtml(c.code||'')} · ${escapeHtml(c.phone||'')}</small></div></div></td>
    <td><strong>${escapeHtml(c.date_label||'')}</strong><small class="checkin-cell-meta">${escapeHtml(c.time||'—')}</small></td>
    <td><strong>${escapeHtml(c.branch_name||c.branch||'—')}</strong><small class="checkin-cell-meta">${escapeHtml(c.beautician||'—')}</small></td>
    <td>${escapeHtml(c.treatment||'—')}</td>
    <td><div class="checkin-status-stack">${statusBadge(c.payment_label)}${statusBadge(c.clearance_label)}</div></td>
    <td><div class="checkin-status-stack">${statusBadge(c.arrival_label)}<small>${escapeHtml(c.waiting_label||'—')}</small></div></td>
    <td><div class="checkin-row-actions">${checkinPrimaryAction(c,true)}<button type="button" class="btn small soft" data-cin-view="${c.id}">${escapeHtml(t('checkin.view'))}</button></div></td>
  </tr>`).join('');
  mount.innerHTML = `<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${escapeHtml(t('checkin.col_customer'))}</th><th>${escapeHtml(t('checkin.col_time'))}</th>
    <th>${escapeHtml(t('checkin.col_branch'))} / ${escapeHtml(t('checkin.col_beautician'))}</th>
    <th>${escapeHtml(t('checkin.col_treatment'))}</th><th>${escapeHtml(t('checkin.col_payment'))} / ${escapeHtml(t('checkin.col_clearance'))}</th>
    <th>${escapeHtml(t('checkin.col_status'))}</th><th>${escapeHtml(t('checkin.col_action'))}</th>
  </tr></thead><tbody>${rows}</tbody></table></div>
  ${centralPager('cin',checkinMeta)}`;
  $$('[data-cin-view]',mount).forEach(b => b.onclick = () => reviewCheckin(b.dataset.cinView));
  bindCheckinActions(mount);
  bindCentralPager('cin',page=>{state.checkinPage=page;return refreshCheckins();});
}

function checkinPrimaryAction(c,compact=false){
  const actions=Array.isArray(c.available_actions)?c.available_actions:[];
  const cls=compact?'btn small':'btn';
  if(actions.includes('confirm_arrival') && boot.canConfirmCheckin){
    return `<button type="button" class="${cls} primary" data-cin-confirm="${c.id}">${escapeHtml(t('checkin.confirm_arrival'))}</button>`;
  }
  if(actions.includes('open_clearance')){
    return `<button type="button" class="${cls} primary" data-cin-clearance="${c.id}">${escapeHtml(t('checkin.go_clearance'))}</button>`;
  }
  if(actions.includes('open_crm') && boot.canViewTreatments && boot.treatmentReservationsUrl){
    return `<a class="${cls} primary" href="${escapeHtml(boot.treatmentReservationsUrl)}" target="_blank" rel="noopener">${escapeHtml(t('checkin.open_crm'))}</a>`;
  }
  return '';
}
function checkinActionHelp(c){
  if(c.status==='completed') return t('checkin.action_help_completed');
  if(c.status==='in_progress') return t('checkin.action_help_treatment');
  return t(c.checked_in_at?'checkin.action_help_waiting':'checkin.action_help_booked');
}
function bindCheckinActions(scope=document){
  $$('[data-cin-confirm]',scope).forEach(button=>button.onclick=()=>confirmCheckinArrival(button.dataset.cinConfirm));
  $$('[data-cin-clearance]',scope).forEach(button=>button.onclick=()=>{
    const c=liveCheckins.find(item=>Number(item.id)===Number(button.dataset.cinClearance));
    if(c) state.clearanceSearch=String(c.phone||c.code||c.name||'').trim();
    closeActionDrawer();
    navigate('clearance');
  });
}
function confirmCheckinArrival(id){
  const c=liveCheckins.find(item=>Number(item.id)===Number(id)); if(!c)return;
  openActionDrawer(
    t('checkin.confirm_title'),
    `${c.code} · ${c.name}`,
    `<div class="checkin-confirm"><div class="clearance-confirm__icon">${clearanceStepIcon(true)}</div><p>${escapeHtml(t('checkin.confirm_body'))}</p></div>`,
    `<button type="button" class="btn" data-action-drawer-close>${escapeHtml(t('checkin.cancel'))}</button><button type="button" class="btn primary" id="confirmCheckinArrival">${escapeHtml(t('checkin.confirm_action'))}</button>`,
    t('checkin.workflow_title')
  );
  $('#confirmCheckinArrival').onclick=event=>submitCheckinArrival(c,event.currentTarget);
}
async function submitCheckinArrival(c,button){
  const url=leadUrl(boot.checkinConfirmUrlTemplate,c.id);
  if(!url||!boot.canConfirmCheckin){showToast(t('checkin.confirm_error'));return;}
  button.disabled=true;
  button.setAttribute('aria-busy','true');
  try{
    const res=await fetch(url,{method:'POST',headers:apiHeaders(true),credentials:'same-origin',body:JSON.stringify({})});
    const body=await res.json().catch(()=>({}));
    if(!res.ok){showToast(body.message||t('checkin.confirm_error'));return;}
    closeActionDrawer();
    showToast(body.message||t('checkin.checkin_confirmed',{code:c.code}));
    await refreshCheckins();
  }catch(err){console.error(err);showToast(t('checkin.confirm_error'));}
  finally{if(button.isConnected){button.disabled=false;button.removeAttribute('aria-busy');}}
}

function isCheckinPassUrl(value){
  try{
    const candidate = new URL(String(value||'').trim(), window.location.origin);
    const base = new URL(boot.checkinPassBaseUrl, window.location.origin);
    return candidate.origin === base.origin &&
      candidate.pathname.startsWith(base.pathname.replace(/\/$/,'') + '/') &&
      candidate.searchParams.has('expires') && candidate.searchParams.has('signature');
  }catch(error){ return false; }
}

function stopCheckinScanner(){
  cancelAnimationFrame(checkinScannerFrame);
  checkinScannerFrame = 0;
  if(checkinScannerStream){
    checkinScannerStream.getTracks().forEach(track=>track.stop());
    checkinScannerStream = null;
  }
  const video = $('#cinScannerVideo');
  if(video) video.srcObject = null;
}

function openCheckinScanner(){
  const body = `<div class="cin-scanner">
    <p class="cin-scanner__hint">${escapeHtml(t('checkin.scanner_hint'))}</p>
    <div class="cin-scanner__viewport"><video id="cinScannerVideo" playsinline muted aria-label="${escapeHtml(t('checkin.scanner_title'))}"></video><div class="cin-scanner__guide" aria-hidden="true"></div></div>
    <p class="cin-scanner__status" id="cinScannerStatus" aria-live="polite"></p>
    <button type="button" class="btn primary" id="cinScannerStart">${escapeHtml(t('checkin.scanner_start'))}</button>
    <label class="lead-field cin-scanner__manual"><span class="lead-field__label">${escapeHtml(t('checkin.scanner_manual_label'))}</span>
      <input class="lead-field__control" id="cinScannerInput" type="url" inputmode="url" autocomplete="off" placeholder="${escapeHtml(t('checkin.scanner_manual_placeholder'))}">
    </label>
  </div>`;
  const foot = `<button type="button" class="btn primary" id="cinScannerOpen">${escapeHtml(t('checkin.scanner_open'))}</button><button type="button" class="btn" data-action-drawer-close>${escapeHtml(t('checkin.close'))}</button>`;
  openActionDrawer(t('checkin.scanner_title'), t('checkin.scanner_hint'), body, foot, t('nav.checkin'));
  $('#cinScannerStart').onclick = startCheckinScanner;
  $('#cinScannerOpen').onclick = () => openScannedCheckinPass($('#cinScannerInput').value);
  $('#cinScannerInput').onkeydown = event => { if(event.key === 'Enter'){ event.preventDefault(); openScannedCheckinPass(event.currentTarget.value); } };
}

function openScannedCheckinPass(value){
  if(!isCheckinPassUrl(value)){
    const status = $('#cinScannerStatus');
    if(status) status.textContent = t('checkin.scanner_invalid');
    return;
  }
  stopCheckinScanner();
  window.location.assign(String(value).trim());
}

async function startCheckinScanner(){
  const status = $('#cinScannerStatus');
  const button = $('#cinScannerStart');
  if(!('BarcodeDetector' in window) || !navigator.mediaDevices?.getUserMedia){
    status.textContent = t('checkin.scanner_unsupported');
    return;
  }
  button.disabled = true;
  status.textContent = t('common.loading');
  try{
    const supported = BarcodeDetector.getSupportedFormats ? await BarcodeDetector.getSupportedFormats() : ['qr_code'];
    if(!supported.includes('qr_code')) throw new Error('QR format is unavailable');
    const detector = new BarcodeDetector({formats:['qr_code']});
    checkinScannerStream = await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'}},audio:false});
    const video = $('#cinScannerVideo');
    video.srcObject = checkinScannerStream;
    await video.play();
    button.textContent = t('checkin.scanner_stop');
    button.disabled = false;
    button.onclick = () => { stopCheckinScanner(); button.textContent=t('checkin.scanner_start'); button.onclick=startCheckinScanner; };
    status.textContent = t('checkin.scanner_hint');
    const scan = async () => {
      if(!checkinScannerStream || !video.isConnected) return;
      try{
        const codes = await detector.detect(video);
        if(codes[0]?.rawValue){ openScannedCheckinPass(codes[0].rawValue); return; }
      }catch(error){ console.error(error); }
      checkinScannerFrame = requestAnimationFrame(scan);
    };
    scan();
  }catch(error){
    console.error(error);
    stopCheckinScanner();
    button.disabled = false;
    status.textContent = t('checkin.scanner_unsupported');
  }
}

function reviewCheckin(id){
  const c = liveCheckins.find(x => Number(x.id) === Number(id)); if(!c) return;
  const orderUrl = c.order_id && boot.orderShowUrlTemplate ? leadUrl(boot.orderShowUrlTemplate, c.order_id) : '';
  const customerUrl = c.customer_id && boot.userEditUrlTemplate ? leadUrl(boot.userEditUrlTemplate, c.customer_id) : '';
  const stage = c.status || 'pending';
  const checkedIn = Boolean(c.checked_in_at);
  const steps = [
    {label:t('checkin.step_booked'), state: checkedIn || stage!=='pending' ? 'done' : 'active', time:''},
    {label:t('checkin.step_checked_in'), state: checkedIn ? 'done' : '', time: checkedIn && c.checked_in_label && c.checked_in_label !== '—' ? c.checked_in_label : ''},
    {label:t('checkin.step_clearance'), state: checkedIn && stage==='pending' ? 'active' : (['in_progress','completed'].includes(stage) ? 'done' : ''), time:''},
    {label:t('checkin.step_treatment'), state: stage==='in_progress' ? 'active' : (stage==='completed' ? 'done' : ''), time:''},
    {label:t('checkin.step_completed'), state: stage==='completed' ? 'active done' : '', time:''},
  ];
  const timeline = `<div class="cin-preview__block cin-timeline"><div class="journey-section__title">${escapeHtml(t('checkin.timeline'))}</div>
    <div class="timeline">${steps.map(s=>`<div class="timeline-step ${s.state}"><div class="timeline-dot">${clearanceStepIcon(s.state.includes('done'))}</div><span>${escapeHtml(s.label)}${s.time?` <em>· ${escapeHtml(s.time)}</em>`:''}</span></div>`).join('')}</div></div>`;
  const contact = `${c.phone||c.email ? `<div class="cin-preview__contact">
    ${c.phone?`<a href="tel:${escapeHtml(c.phone)}"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5.2 2.8 7.5 6 6 7.5c1.2 2.4 2.1 3.3 4.5 4.5l1.5-1.5 3.2 2.3c.5.4.7 1 .5 1.6-.4 1-1.4 1.7-2.5 1.6C7.8 15.6 4.4 12.2 4 6.8c-.1-1.1.6-2.1 1.6-2.5.6-.2 1.2 0 1.6.5Z"/></svg>${escapeHtml(c.phone)}</a>`:''}
    ${c.email?`<a href="mailto:${escapeHtml(c.email)}"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="2.5" y="4" width="15" height="12" rx="2"/><path d="m3.5 6 6.5 5 6.5-5"/></svg>${escapeHtml(c.email)}</a>`:''}
  </div>`:''}`;
  const qrCard = c.checkin_pass_url ? `<div class="cin-preview__block cin-preview__qr">
    <div class="journey-section__title">${escapeHtml(t('checkin.qr_title'))}</div>
    <div class="cin-preview__qr-box"><canvas id="cinQrCanvas" role="img" aria-label="${escapeHtml(t('checkin.qr_title'))}"></canvas></div>
    <div class="cin-preview__qr-code">${escapeHtml(c.code||'')}</div>
    <p class="cin-preview__qr-hint">${escapeHtml(t('checkin.qr_hint'))}</p>
    <div class="cin-preview__qr-actions"><button type="button" class="btn small soft" id="cinCopyCode">${escapeHtml(t('checkin.copy_code'))}</button><a class="btn small" href="${escapeHtml(c.checkin_pass_url)}" target="_blank" rel="noopener">${escapeHtml(t('checkin.view_checkin_pass'))}</a></div>
  </div>` : '';
  const kv = `<div class="cin-preview__block"><div class="journey-section__title">${escapeHtml(t('checkin.details'))}</div>
    <div class="journey-kv">
      <div><span>${escapeHtml(t('checkin.col_treatment'))}</span><strong>${escapeHtml(c.treatment||'—')}</strong></div>
      <div><span>${escapeHtml(t('checkin.col_beautician'))}</span><strong>${escapeHtml(c.beautician||'—')}</strong></div>
      <div><span>${escapeHtml(t('checkin.col_branch'))}</span><strong>${escapeHtml(c.branch_name||c.branch||'—')}</strong></div>
      <div><span>${escapeHtml(t('checkin.col_payment'))}</span><strong>${escapeHtml(c.payment_label||'—')}</strong></div>
      <div><span>${escapeHtml(t('checkin.col_date'))}</span><strong>${escapeHtml((c.date_label||'')+' '+(c.time||''))}</strong></div>
      <div><span>${escapeHtml(t('checkin.col_checked_in'))}</span><strong>${escapeHtml(c.checked_in_label||'—')}</strong></div>
      <div><span>${escapeHtml(t('checkin.col_wait'))}</span><strong>${escapeHtml(c.waiting_label||'—')}</strong></div>
      <div><span>${escapeHtml(t('checkin.col_clearance'))}</span><strong>${escapeHtml(c.clearance_label||'—')}</strong></div>
    </div></div>`;
  const body = `<div class="pay-review cin-preview">
    <div class="pay-review__hero pay-review__hero--cin">
      <div class="pay-review__avatar cin-avatar" aria-hidden="true">${escapeHtml(c.initial||'?')}</div>
      <div style="min-width:0;flex:1">
        <div class="pay-id">${escapeHtml(c.code||'')}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${escapeHtml(c.name||'')}</strong>
        <div class="pay-method">${escapeHtml(c.date_label||'')} ${escapeHtml(c.time||'')} · ${escapeHtml(c.branch_name||c.branch||'—')}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${statusBadge(c.status_label)}${statusBadge(c.arrival_label)}</div>
      </div>
    </div>
    ${contact}<div class="checkin-decision checkin-decision--${escapeHtml(stage==='pending'?(checkedIn?'waiting':'booked'):stage)}"><strong>${escapeHtml(c.arrival_label||c.status_label||'')}</strong><p>${escapeHtml(checkinActionHelp(c))}</p></div>${timeline}${kv}${qrCard}
  </div>`;
  const foot = [
    checkinPrimaryAction(c),
    customerUrl && boot.canViewUser ? `<a class="btn" href="${escapeHtml(customerUrl)}" target="_blank" rel="noopener">${escapeHtml(t('checkin.open_customer'))}</a>` : '',
    orderUrl && boot.canViewOrder ? `<a class="btn" href="${escapeHtml(orderUrl)}" target="_blank" rel="noopener">${escapeHtml(t('checkin.open_order'))}</a>` : '',
    !(Array.isArray(c.available_actions)&&c.available_actions.includes('open_crm')) && boot.canViewTreatments && boot.treatmentReservationsUrl ? `<a class="btn" href="${escapeHtml(boot.treatmentReservationsUrl)}" target="_blank" rel="noopener">${escapeHtml(t('checkin.open_crm'))}</a>` : '',
    `<button type="button" class="btn" data-action-drawer-close>${escapeHtml(t('checkin.close'))}</button>`,
  ].filter(Boolean).join('');
  openActionDrawer(t('checkin.title'), c.code+' · '+c.name, body, foot, t('nav.checkin'));
  bindCheckinActions($('#actionDrawer'));
  const canvas = $('#cinQrCanvas');
  if(canvas && window.QRCentral){
    try{ QRCentral.render(canvas, c.checkin_pass_url || ''); }
    catch(err){ console.error(err); canvas.closest('.cin-preview__qr-box')?.classList.add('hidden'); }
  }
  const copy = $('#cinCopyCode');
  if(copy) copy.onclick = () => copyToClipboard(c.code || '', t('checkin.copied'));
}
function copyToClipboard(text, okMsg){
  const done = () => showToast(okMsg);
  if(!text){ showToast(t('checkin.copy_missing')); return; }
  if(navigator.clipboard && window.isSecureContext){
    navigator.clipboard.writeText(text).then(done).catch(()=>fallbackCopy(text, done));
  }else fallbackCopy(text, done);
}
function fallbackCopy(text, done){
  const ta = document.createElement('textarea');
  ta.value = text;
  ta.setAttribute('readonly','');
  ta.style.position = 'fixed';
  ta.style.opacity = '0';
  document.body.appendChild(ta);
  ta.select();
  try{ document.execCommand('copy'); done(); }
  catch(e){ showToast(t('checkin.copy_missing')); }
  document.body.removeChild(ta);
}

function clearance(){
  root.innerHTML = `<div class="clr-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${escapeHtml(t('clearance.title'))}</h1>
        <div class="page-subtitle">${escapeHtml(t('clearance.subtitle'))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="clrRefresh">${escapeHtml(t('clearance.refresh'))}</button>
        <button type="button" class="btn" data-jump="payments">${escapeHtml(t('clearance.open_payments'))}</button>
        ${boot.canViewTreatments && boot.treatmentReservationsUrl ? `<a class="btn primary" href="${escapeHtml(boot.treatmentReservationsUrl)}" target="_blank" rel="noopener">${escapeHtml(t('clearance.open_crm'))}</a>` : ''}
      </div>
    </div>
    <section class="clearance-flow" aria-labelledby="clearanceFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CRM</span><h2 id="clearanceFlowTitle">${escapeHtml(t('clearance.workflow_title'))}</h2><p>${escapeHtml(t('clearance.workflow_hint'))}</p></div>
      <ol class="clearance-flow__steps">
        ${[t('clearance.step_arrival'),t('clearance.step_payment'),t('clearance.step_treatment'),t('clearance.step_complete')].map((label,index)=>`<li><span aria-hidden="true">${index+1}</span><strong>${escapeHtml(label)}</strong></li>`).join('')}
      </ol>
    </section>
    <div class="pay-metrics" id="clrMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${escapeHtml(t('clearance.subtitle'))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="clrResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="clrTabs" role="group"></div>
        <label class="lead-search" for="clrSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="clrSearch" aria-label="${escapeHtml(t('clearance.search_placeholder'))}" type="search" autocomplete="off" placeholder="${escapeHtml(t('clearance.search_placeholder'))}" value="${escapeHtml(state.clearanceSearch||'')}" />
        </label>
        <div class="lead-filter-grid ops-filter-grid ops-filter-grid--two">
          <label class="lead-field"><span class="lead-field__label">${escapeHtml(t('clearance.filter_beautician'))}</span>
            <select class="lead-field__control" id="clrBeautician"></select>
          </label>
          <label class="lead-field"><span class="lead-field__label">${escapeHtml(t('clearance.filter_branch'))}</span>
            <select class="lead-field__control" id="clrBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="clrTableMount"></div>
    </section>
  </div>`;
  const refreshBtn = $('#clrRefresh'); if(refreshBtn) refreshBtn.onclick = () => refreshClearances();
  bindJump();
  bindClearanceFilters();
  refreshClearances();
}
function bindClearanceFilters(){
  const search = $('#clrSearch');
  if(search){ search.oninput = () => { clearTimeout(clearanceSearchTimer); clearanceSearchTimer = setTimeout(() => { state.clearanceSearch = search.value.trim(); state.clearancePage = 1; refreshClearances(); }, 320); }; }
  const beau = $('#clrBeautician'); if(beau) beau.onchange = () => { state.clearanceBeautician = beau.value; state.clearancePage = 1; refreshClearances(); };
  const branch = $('#clrBranch'); if(branch) branch.onchange = () => { state.clearanceBranch = branch.value; state.clearancePage = 1; refreshClearances(); };
}
async function refreshClearances(){
  const requestId = ++clearanceRequest;
  clearanceLoadError = false;
  const url = boot.clearanceUrl || '';
  const mount = $('#clrTableMount');
  if(!url){ if(mount) mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('clearance.load_error'))}</strong></div>`; return; }
  clearancesLoading = true; renderClearanceMetrics(); renderClearanceTable();
  const qs = new URLSearchParams();
  if(state.clearanceSearch) qs.set('q', state.clearanceSearch);
  if(state.clearanceState) qs.set('state', state.clearanceState);
  const branch = state.clearanceBranch !== 'all' ? state.clearanceBranch : (state.branch || 'all');
  if(branch && branch !== 'all') qs.set('branch', branch);
  if(state.clearanceBeautician && state.clearanceBeautician !== 'all') qs.set('beautician', state.clearanceBeautician);
  qs.set('page', String(state.clearancePage || 1)); qs.set('per_page', '25');
  try{
    const res = await fetch(`${url}?${qs.toString()}`, { headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin' });
    if(!res.ok) throw new Error('clearance '+res.status);
    const json = await res.json();
    if(requestId !== clearanceRequest) return;
    liveClearances = Array.isArray(json.data) ? json.data : [];
    liveClearanceSummary = Object.assign({waiting:0,blocked:0,in_treatment:0,done_today:0,queue:0}, (json.meta && json.meta.summary) || {});
    liveClearanceFilters = Object.assign({states:[],beauticians:[],branches:[]}, json.filters || {});
    clearanceMeta = { current_page:(json.meta&&json.meta.current_page)||1, last_page:(json.meta&&json.meta.last_page)||1, total:(json.meta&&json.meta.total)||0 };
  }catch(err){ if(requestId !== clearanceRequest) return; clearanceLoadError = true; console.error(err); liveClearances=[]; showToast(t('clearance.load_error')); }
  finally{ if(requestId !== clearanceRequest) return; clearancesLoading=false; renderClearanceMetrics(); renderClearanceTabs(); renderClearanceFilterOptions(); renderClearanceTable(); }
}
function renderClearanceMetrics(){
  const el = $('#clrMetrics'); if(!el) return;
  const s = liveClearanceSummary;
  el.setAttribute('aria-busy', String(clearancesLoading));
  const metric = value => clearancesLoading || clearanceLoadError ? '—' : fmtInt(value);
  el.innerHTML = `
    <div class="pay-metric clearance-metric clearance-metric--waiting"><span>${escapeHtml(t('clearance.stat_waiting'))}</span><strong>${metric(s.waiting)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--blocked"><span>${escapeHtml(t('clearance.stat_blocked'))}</span><strong>${metric(s.blocked)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--treatment"><span>${escapeHtml(t('clearance.stat_treatment'))}</span><strong>${metric(s.in_treatment)}</strong></div>
    <div class="pay-metric clearance-metric clearance-metric--done"><span>${escapeHtml(t('clearance.stat_done'))}</span><strong>${metric(s.done_today)}</strong></div>`;
}
function renderClearanceTabs(){
  const mount = $('#clrTabs'); if(!mount) return;
  const s = liveClearanceSummary;
  const tabs = [
    ['waiting', t('clearance.tab_waiting'), s.waiting],
    ['blocked', t('clearance.tab_blocked'), s.blocked],
    ['in_treatment', t('clearance.tab_treatment'), s.in_treatment],
    ['all_queue', t('clearance.tab_queue'), s.queue],
    ['done', t('clearance.tab_done'), s.done_today],
  ];
  mount.innerHTML = tabs.map(([v,label,count]) => {
    const active = state.clearanceState === v ? 'active' : '';
    return `<button type="button" class="tab ${active}" aria-pressed="${Boolean(active)}" data-clrtab="${v}">${escapeHtml(label)} (${fmtInt(count||0)})</button>`;
  }).join('');
  $$('[data-clrtab]', mount).forEach(btn => btn.onclick = () => { state.clearanceState = btn.dataset.clrtab; state.clearancePage = 1; refreshClearances(); });
}
function renderClearanceFilterOptions(){
  const beau = $('#clrBeautician');
  if(beau){
    const cur = state.clearanceBeautician || 'all';
    beau.innerHTML = `<option value="all">${escapeHtml(t('clearance.all_beauticians'))}</option>` +
      (liveClearanceFilters.beauticians||[]).map(b => `<option value="${b.id}"${String(cur)===String(b.id)?' selected':''}>${escapeHtml(b.name)}</option>`).join('');
  }
  const branch = $('#clrBranch');
  if(branch){
    const cur = state.clearanceBranch || 'all';
    branch.innerHTML = `<option value="all">${escapeHtml(t('clearance.all_branches'))}</option>` +
      (liveClearanceFilters.branches||[]).map(b => `<option value="${b.id}"${String(cur)===String(b.id)?' selected':''}>${escapeHtml(b.name)}</option>`).join('');
  }
  const count = $('#clrResultCount');
  if(count) count.textContent = t('clearance.result_count', {count: clearanceLoadError ? '—' : fmtInt(clearanceMeta.total)});
}
function renderClearanceTable(){
  const mount = $('#clrTableMount'); if(!mount) return;
  mount.setAttribute('aria-busy', String(clearancesLoading));
  if(clearanceLoadError){ mount.innerHTML = `<div class="pay-empty" role="alert"><strong>${escapeHtml(t('clearance.load_error'))}</strong><button type="button" class="btn" id="clrRetry">${escapeHtml(t('clearance.refresh'))}</button></div>`; $('#clrRetry').onclick = () => refreshClearances(); return; }
  if(clearancesLoading){ mount.innerHTML = `<div class="pay-empty" role="status">${escapeHtml(t('clearance.loading'))}</div>`; return; }
  if(!liveClearances.length){ mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('clearance.empty'))}</strong>${escapeHtml(t('clearance.empty_hint'))}</div>`; return; }
  const rows = liveClearances.map(c => `<tr data-clearance-state="${escapeHtml(c.clearance||'other')}">
    <td><div class="person-cell person-cell--lead"><div class="mini-avatar">${escapeHtml(c.initial||'?')}</div><div class="person-cell__text"><strong>${escapeHtml(c.name||'')}</strong><small>${escapeHtml(c.code||'')} · ${escapeHtml(c.phone||'')}</small></div></div></td>
    <td><strong>${escapeHtml(c.date_label||'')}</strong><small class="clearance-wait">${escapeHtml(c.checked_in_at?t('clearance.arrival_waiting',{time:c.waiting_label||'—'}):(c.time||'—'))}</small></td>
    <td>${escapeHtml(c.branch_name||c.branch||'—')}</td>
    <td>${escapeHtml(c.treatment||'—')}</td>
    <td>${statusBadge(c.payment_label)}</td>
    <td>${statusBadge(c.clearance_label)}</td>
    <td><div class="clearance-row-actions">${clearancePrimaryAction(c,true)}<button type="button" class="btn small soft" data-clr-view="${c.id}">${escapeHtml(t('clearance.view'))}</button></div></td>
  </tr>`).join('');
  mount.innerHTML = `<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${escapeHtml(t('clearance.col_customer'))}</th><th>${escapeHtml(t('clearance.col_time'))}</th>
    <th>${escapeHtml(t('clearance.col_branch'))}</th><th>${escapeHtml(t('clearance.col_treatment'))}</th>
    <th>${escapeHtml(t('clearance.col_payment'))}</th><th>${escapeHtml(t('clearance.col_clearance'))}</th>
    <th>${escapeHtml(t('clearance.col_action'))}</th>
  </tr></thead><tbody>${rows}</tbody></table></div>
  ${centralPager('clr',clearanceMeta)}`;
  $$('[data-clr-view]',mount).forEach(b => b.onclick = () => reviewClearance(b.dataset.clrView));
  bindClearanceActions(mount);
  bindCentralPager('clr',page=>{state.clearancePage=page;return refreshClearances();});
}
function clearancePrimaryAction(c,compact=false){
  const actions=Array.isArray(c.available_actions)?c.available_actions:[];
  const cls=compact?'btn small':'btn';
  if(actions.includes('start_treatment') && boot.canEditTreatments){
    return `<button type="button" class="${cls} primary" data-clr-status="in_progress" data-clr-id="${c.id}">${escapeHtml(t('clearance.start_treatment'))}</button>`;
  }
  if(actions.includes('complete_treatment') && boot.canEditTreatments){
    return `<button type="button" class="${cls} success" data-clr-status="completed" data-clr-id="${c.id}">${escapeHtml(t('clearance.complete_treatment'))}</button>`;
  }
  if(actions.includes('resolve_payment')){
    if(c.order_id){
      return `<button type="button" class="${cls} danger" data-clr-payment="${c.id}">${escapeHtml(t('clearance.resolve_payment'))}</button>`;
    }
    if(boot.canViewTreatments && boot.treatmentReservationsUrl){
      return `<a class="${cls} danger" href="${escapeHtml(boot.treatmentReservationsUrl)}" target="_blank" rel="noopener">${escapeHtml(t('clearance.resolve_payment'))}</a>`;
    }
  }
  return '';
}
function clearanceActionHelp(c){
  return t(`clearance.action_help_${({waiting:'waiting',blocked:'blocked',in_treatment:'treatment',done:'done'})[c.clearance]||'done'}`);
}
function clearanceStepIcon(done){
  return done
    ? '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 10 3 3 7-7"/></svg>'
    : '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10" cy="10" r="6"/></svg>';
}
function bindClearanceActions(scope=document){
  $$('[data-clr-status]',scope).forEach(button=>button.onclick=()=>confirmClearanceTransition(button.dataset.clrId,button.dataset.clrStatus));
  $$('[data-clr-payment]',scope).forEach(button=>button.onclick=()=>{
    const c=liveClearances.find(item=>Number(item.id)===Number(button.dataset.clrPayment));
    if(c) state.paymentSearch=String(c.phone||c.name||c.code||'').trim();
    closeActionDrawer();
    navigate('payments');
  });
}
function confirmClearanceTransition(id,status){
  const c=liveClearances.find(item=>Number(item.id)===Number(id)); if(!c)return;
  const starting=status==='in_progress';
  openActionDrawer(
    t(starting?'clearance.start_confirm_title':'clearance.complete_confirm_title'),
    `${c.code} · ${c.name}`,
    `<div class="clearance-confirm"><div class="clearance-confirm__icon">${clearanceStepIcon(true)}</div><p>${escapeHtml(t(starting?'clearance.start_confirm_body':'clearance.complete_confirm_body'))}</p></div>`,
    `<button type="button" class="btn" data-action-drawer-close>${escapeHtml(t('clearance.cancel'))}</button><button type="button" class="btn ${starting?'primary':'success'}" id="confirmClearanceStatus">${escapeHtml(t('clearance.confirm_action'))}</button>`,
    t('clearance.workflow_title')
  );
  $('#confirmClearanceStatus').onclick=e=>updateClearanceStatus(c,status,e.currentTarget);
}
async function updateClearanceStatus(c,status,button){
  const url=leadUrl(boot.clearanceStatusUrlTemplate,c.id);
  if(!url||!boot.canEditTreatments){showToast(t('clearance.update_error'));return;}
  button.disabled=true;
  button.setAttribute('aria-busy','true');
  try{
    const res=await fetch(url,{method:'PATCH',headers:apiHeaders(true),credentials:'same-origin',body:JSON.stringify({status})});
    const body=await res.json().catch(()=>({}));
    if(!res.ok){showToast(body.message||t('clearance.update_error'));return;}
    closeActionDrawer();
    showToast(body.message||t('clearance.status_updated'));
    await refreshClearances();
  }catch(err){console.error(err);showToast(t('clearance.update_error'));}
  finally{if(button.isConnected){button.disabled=false;button.removeAttribute('aria-busy');}}
}
function reviewClearance(id){
  const c = liveClearances.find(x => Number(x.id) === Number(id)); if(!c) return;
  const orderUrl = c.order_id && boot.orderShowUrlTemplate ? leadUrl(boot.orderShowUrlTemplate, c.order_id) : '';
  const customerUrl = c.customer_id && boot.userEditUrlTemplate ? leadUrl(boot.userEditUrlTemplate, c.customer_id) : '';
  const body = `<div class="pay-review">
    <div class="pay-review__hero"><div class="pay-review__avatar">${escapeHtml(c.initial||'?')}</div>
      <div style="min-width:0;flex:1"><div class="pay-id">${escapeHtml(c.code||'')}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${escapeHtml(c.name||'')}</strong>
        <div class="pay-method">${escapeHtml(c.phone||'—')} · ${escapeHtml(c.treatment||'')}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${statusBadge(c.clearance_label)}${statusBadge(c.payment_label)}</div>
      </div></div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${escapeHtml(t('clearance.col_time'))}</label><strong>${escapeHtml((c.date_label||'')+' '+(c.time||''))}</strong></div>
      <div class="pay-review__card"><label>${escapeHtml(t('clearance.col_branch'))}</label><strong>${escapeHtml(c.branch_name||c.branch||'—')}</strong></div>
      <div class="pay-review__card"><label>${escapeHtml(t('checkin.col_beautician'))}</label><strong>${escapeHtml(c.beautician||'—')}</strong></div>
    </div>
    <div class="clearance-decision clearance-decision--${escapeHtml(c.clearance||'other')}"><strong>${escapeHtml(c.clearance_label||'')}</strong><p>${escapeHtml(clearanceActionHelp(c))}</p></div>
    <div class="journey-section"><div class="journey-section__title">${escapeHtml(t('clearance.workflow_title'))}</div><ol class="clearance-checklist">
      ${[
        [t('clearance.step_arrival'),Boolean(c.checked_in_at)||['in_treatment','done'].includes(c.clearance)],
        [t('clearance.step_payment'),Boolean(c.payment_ok)||['in_treatment','done'].includes(c.clearance)],
        [t('clearance.step_treatment'),['in_treatment','done'].includes(c.clearance)],
        [t('clearance.step_complete'),c.clearance==='done'],
      ].map(([label,done])=>`<li class="${done?'is-done':''}"><span>${clearanceStepIcon(done)}</span><strong>${escapeHtml(label)}</strong></li>`).join('')}
    </ol></div></div>`;
  const foot = [
    clearancePrimaryAction(c),
    c.checkin_pass_url ? `<a class="btn" href="${escapeHtml(c.checkin_pass_url)}" target="_blank" rel="noopener">${escapeHtml(t('clearance.view_checkin_pass'))}</a>` : '',
    customerUrl && boot.canViewUser ? `<a class="btn" href="${escapeHtml(customerUrl)}" target="_blank" rel="noopener">${escapeHtml(t('clearance.open_customer'))}</a>` : '',
    orderUrl && boot.canViewOrder ? `<a class="btn" href="${escapeHtml(orderUrl)}" target="_blank" rel="noopener">${escapeHtml(t('clearance.open_order'))}</a>` : '',
    boot.canViewTreatments && boot.treatmentReservationsUrl ? `<a class="btn" href="${escapeHtml(boot.treatmentReservationsUrl)}" target="_blank" rel="noopener">${escapeHtml(t('clearance.open_crm'))}</a>` : '',
    `<button type="button" class="btn" data-action-drawer-close>${escapeHtml(t('clearance.close'))}</button>`,
  ].filter(Boolean).join('');
  openActionDrawer(t('clearance.title'), c.code+' · '+c.name, body, foot, t('nav.clearance'));
  setTimeout(() => bindClearanceActions($('#actionDrawerFoot')), 0);
}

function wallet(){
  root.innerHTML = `<div class="wal-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${escapeHtml(t('wallet.title'))}</h1>
        <div class="page-subtitle">${escapeHtml(t('wallet.subtitle'))} <span class="loyalty-scope-badge">${escapeHtml(t('wallet.scope_badge'))}</span></div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="walRefresh">${escapeHtml(t('wallet.refresh'))}</button>
        ${boot.canViewLoyalty && boot.loyaltyMembersUrl ? `<a class="btn primary" href="${escapeHtml(boot.loyaltyMembersUrl)}" target="_blank" rel="noopener">${escapeHtml(t('wallet.open_loyalty'))}</a>` : ''}
      </div>
    </div>
    <section class="clearance-flow loyalty-flow" aria-labelledby="loyaltyFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">LOYALTY</span><h2 id="loyaltyFlowTitle">${escapeHtml(t('wallet.workflow_title'))}</h2><p>${escapeHtml(t('wallet.workflow_hint'))}</p></div>
      <ol class="clearance-flow__steps">
        ${[t('wallet.step_enrolled'),t('wallet.step_earn'),t('wallet.step_tier'),t('wallet.step_redeem')].map((label,index)=>`<li><span aria-hidden="true">${index+1}</span><strong>${escapeHtml(label)}</strong></li>`).join('')}
      </ol>
    </section>
    <div class="pay-metrics" id="walMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro"><p class="lead-panel__sub" style="margin:0">${escapeHtml(t('wallet.subtitle'))}</p></div>
        <div class="lead-panel__head-meta"><span class="lead-panel__count" id="walResultCount">—</span></div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="walTabs" role="group"></div>
        <label class="lead-search" for="walSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="walSearch" aria-label="${escapeHtml(t('wallet.search_placeholder'))}" type="search" autocomplete="off" placeholder="${escapeHtml(t('wallet.search_placeholder'))}" value="${escapeHtml(state.walletSearch||'')}" />
        </label>
        <div class="lead-filter-grid loyalty-filter-grid">
          <label class="lead-field"><span class="lead-field__label">${escapeHtml(t('wallet.filter_tier'))}</span>
            <select class="lead-field__control" id="walTier"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="walTableMount"></div>
    </section>
  </div>`;
  const refreshBtn = $('#walRefresh'); if(refreshBtn) refreshBtn.onclick = () => refreshWallets();
  bindWalletFilters();
  refreshWallets();
}
function bindWalletFilters(){
  const search = $('#walSearch');
  if(search){ search.oninput = () => { clearTimeout(walletSearchTimer); walletSearchTimer = setTimeout(() => { state.walletSearch = search.value.trim(); state.walletPage = 1; refreshWallets(); }, 320); }; }
  const tier = $('#walTier'); if(tier) tier.onchange = () => { state.walletTier = tier.value; state.walletPage = 1; refreshWallets(); };
}
async function refreshWallets(){
  const requestId = ++walletRequest;
  walletLoadError = false;
  const url = boot.walletUrl || '';
  const mount = $('#walTableMount');
  if(!url){ if(mount) mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('wallet.load_error'))}</strong></div>`; return; }
  walletsLoading = true; renderWalletMetrics(); renderWalletTable();
  const qs = new URLSearchParams();
  if(state.walletCustomerId) qs.set('customer_id', String(state.walletCustomerId));
  else if(state.walletSearch) qs.set('q', state.walletSearch);
  if(state.walletSegment && state.walletSegment !== 'all') qs.set('segment', state.walletSegment);
  if(state.walletTier && state.walletTier !== 'all') qs.set('tier', state.walletTier);
  qs.set('page', String(state.walletPage || 1)); qs.set('per_page', '25');
  try{
    const res = await fetch(`${url}?${qs.toString()}`, { headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin' });
    if(!res.ok) throw new Error('wallet '+res.status);
    const json = await res.json();
    if(requestId !== walletRequest) return;
    liveWallets = Array.isArray(json.data) ? json.data : [];
    liveWalletSummary = Object.assign({members:0,with_balance:0,zero_balance:0,points_outstanding:0,stamp_ready:0}, (json.meta && json.meta.summary) || {});
    liveWalletFilters = Object.assign({segments:[],tiers:[]}, json.filters || {});
    walletMeta = { current_page:(json.meta&&json.meta.current_page)||1, last_page:(json.meta&&json.meta.last_page)||1, total:(json.meta&&json.meta.total)||0 };
  }catch(err){ if(requestId !== walletRequest) return; walletLoadError = true; console.error(err); liveWallets=[]; showToast(t('wallet.load_error')); }
  finally{ if(requestId !== walletRequest) return; walletsLoading=false; renderWalletMetrics(); renderWalletTabs(); renderWalletFilterOptions(); renderWalletTable(); }
}
function renderWalletMetrics(){
  const el = $('#walMetrics'); if(!el) return;
  const s = liveWalletSummary;
  el.setAttribute('aria-busy', String(walletsLoading));
  const metric = value => walletsLoading || walletLoadError ? '—' : fmtInt(value);
  el.innerHTML = `
    <div class="pay-metric loyalty-metric loyalty-metric--members"><span>${escapeHtml(t('wallet.stat_members'))}</span><strong>${metric(s.members)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--active"><span>${escapeHtml(t('wallet.stat_balance'))}</span><strong>${metric(s.with_balance)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--points"><span>${escapeHtml(t('wallet.stat_points'))}</span><strong>${metric(s.points_outstanding)}</strong></div>
    <div class="pay-metric loyalty-metric loyalty-metric--rewards"><span>${escapeHtml(t('wallet.stat_stamp'))}</span><strong>${metric(s.stamp_ready)}</strong></div>`;
}
function renderWalletTabs(){
  const mount = $('#walTabs'); if(!mount) return;
  const s = liveWalletSummary;
  const tabs = [
    ['all', t('wallet.tab_all'), s.members],
    ['active', t('wallet.tab_active'), s.with_balance],
    ['zero', t('wallet.tab_zero'), s.zero_balance],
    ['stamp_ready', t('wallet.tab_stamp'), s.stamp_ready],
  ];
  mount.innerHTML = tabs.map(([v,label,count]) => {
    const active = state.walletSegment === v ? 'active' : '';
    return `<button type="button" class="tab ${active}" aria-pressed="${Boolean(active)}" data-waltab="${v}">${escapeHtml(label)} (${fmtInt(count||0)})</button>`;
  }).join('');
  $$('[data-waltab]', mount).forEach(btn => btn.onclick = () => { state.walletSegment = btn.dataset.waltab; state.walletPage = 1; refreshWallets(); });
}
function renderWalletFilterOptions(){
  const tier = $('#walTier');
  if(tier){
    const cur = state.walletTier || 'all';
    tier.innerHTML = `<option value="all">${escapeHtml(t('wallet.all_tiers'))}</option>` +
      (liveWalletFilters.tiers||[]).map(x => `<option value="${x.id}"${String(cur)===String(x.id)?' selected':''}>${escapeHtml(x.name)}</option>`).join('');
  }
  const count = $('#walResultCount');
  if(count) count.textContent = t('wallet.result_count', {count: walletLoadError ? '—' : fmtInt(walletMeta.total)});
}
function renderWalletTable(){
  const mount = $('#walTableMount'); if(!mount) return;
  mount.setAttribute('aria-busy', String(walletsLoading));
  if(walletLoadError){ mount.innerHTML = `<div class="pay-empty" role="alert"><strong>${escapeHtml(t('wallet.load_error'))}</strong><button type="button" class="btn" id="walRetry">${escapeHtml(t('wallet.refresh'))}</button></div>`; $('#walRetry').onclick = () => refreshWallets(); return; }
  if(walletsLoading){ mount.innerHTML = `<div class="pay-empty" role="status">${escapeHtml(t('wallet.loading'))}</div>`; return; }
  if(!liveWallets.length){ mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('wallet.empty'))}</strong>${escapeHtml(t('wallet.empty_hint'))}</div>`; return; }
  const rows = liveWallets.map(w => {
    const avatar = w.avatar_url
      ? `<div class="mini-avatar mini-avatar--photo"><img src="${escapeHtml(w.avatar_url)}" alt=""></div>`
      : `<div class="mini-avatar">${escapeHtml(w.initial||'?')}</div>`;
    const stamp = w.stamp_ready ? statusBadge(t('wallet.chip_stamp',{count:w.stamp_ready})) : statusBadge(t('wallet.stamp_summary',{active:fmtInt(w.stamp_active||0),ready:0}));
    const memberUrl = boot.canShowLoyaltyMember && boot.loyaltyMemberShowUrlTemplate ? leadUrl(boot.loyaltyMemberShowUrlTemplate,w.id) : '';
    return `<tr data-membership-segment="${escapeHtml(w.segment||'zero')}">
      <td><div class="person-cell person-cell--lead">${avatar}<div class="person-cell__text"><strong>${escapeHtml(w.name||'')}</strong><small>${escapeHtml(w.code||'')} · ${escapeHtml(w.phone||w.email||'—')}</small></div></div></td>
      <td><div class="loyalty-tier-cell">${w.tier ? statusBadge(w.tier) : '—'}<small>${escapeHtml(t('wallet.tier_since'))}: ${escapeHtml(w.tier_since_label||'—')}</small></div></td>
      <td class="is-num"><strong>${fmtInt(w.balance)}</strong></td>
      <td class="is-num">${money(w.lifetime_spend)}</td>
      <td><div class="loyalty-reward-cell">${stamp}<small>${escapeHtml(t('wallet.stamp_summary',{active:fmtInt(w.stamp_active||0),ready:fmtInt(w.stamp_ready||0)}))}</small></div></td>
      <td><div class="loyalty-activity-cell"><strong>${escapeHtml(t('wallet.activity_summary',{count:fmtInt(w.activity_count||0)}))}</strong><small>${escapeHtml(w.last_activity_label||'—')}</small></div></td>
      <td class="lead-table__actions"><div class="lead-menu loyalty-action-menu">
        <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${escapeHtml(t('wallet.row_actions',{name:w.name||t('wallet.guest')}))}"><span class="lead-menu__dots" aria-hidden="true"></span></button>
        <div class="lead-menu__panel" role="menu" hidden>
          ${memberUrl?`<a class="lead-menu__item" role="menuitem" href="${escapeHtml(memberUrl)}" target="_blank" rel="noopener">${escapeHtml(t('wallet.open_member'))}</a>`:''}
          <button type="button" class="lead-menu__item" role="menuitem" data-wal-view="${w.id}">${escapeHtml(t('wallet.view'))}</button>
        </div>
      </div></td>
    </tr>`;
  }).join('');
  mount.innerHTML = `<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${escapeHtml(t('wallet.col_customer'))}</th><th>${escapeHtml(t('wallet.col_tier'))}</th><th class="is-num">${escapeHtml(t('wallet.col_balance'))}</th>
    <th class="is-num">${escapeHtml(t('wallet.col_spend'))}</th><th>${escapeHtml(t('wallet.col_stamps'))}</th>
    <th>${escapeHtml(t('wallet.col_activity'))}</th><th>${escapeHtml(t('wallet.col_action'))}</th>
  </tr></thead><tbody>${rows}</tbody></table></div>
  ${centralPager('wal',walletMeta)}`;
  bindLeadRowMenus(mount);
  $$('.loyalty-action-menu a[role="menuitem"]',mount).forEach(link => link.onclick = () => closeAllLeadMenus());
  $$('[data-wal-view]',mount).forEach(b => b.onclick = () => { closeAllLeadMenus(); reviewWallet(b.dataset.walView); });
  bindCentralPager('wal',page=>{state.walletPage=page;return refreshWallets();});
}
let walletDrawerTrigger = null;
let walletDrawerScroll = '';
function reviewWallet(id){
  const w = liveWallets.find(x => Number(x.id) === Number(id)); if(!w) return;
  closeActionDrawer();
  closeDrawer();
  walletDrawerTrigger = document.activeElement;
  walletDrawerScroll = document.body.style.overflow;
  document.body.style.overflow = 'hidden';
  const memberUrl = boot.loyaltyMemberShowUrlTemplate ? leadUrl(boot.loyaltyMemberShowUrlTemplate, w.id) : '';
  const profileUrl = w.customer_id && boot.userEditUrlTemplate ? leadUrl(boot.userEditUrlTemplate, w.customer_id) : '';
  const recent = (w.recent||[]).length
    ? `<ol class="wallet-transactions">${w.recent.map(tx => `<li class="wallet-transaction">
        <div class="wallet-transaction__description"><strong>${escapeHtml(tx.description||tx.type||'')}</strong><time>${escapeHtml(tx.created_label||'')}</time></div>
        <div class="wallet-transaction__amount"><strong class="${Number(tx.points)>0?'is-credit':''}">${Number(tx.points)>0?'+':''}${fmtInt(tx.points)} <span>${escapeHtml(t('wallet.col_balance'))}</span></strong><small>${escapeHtml(t('wallet.transaction_balance', {balance:fmtInt(tx.balance_after)}))}</small></div>
      </li>`).join('')}</ol>`
    : `<div class="pay-empty">${escapeHtml(t('wallet.no_recent'))}</div>`;
  const avatar = w.avatar_url
    ? `<img class="wallet-detail__avatar" src="${escapeHtml(w.avatar_url)}" alt="" />`
    : `<div class="wallet-detail__avatar" aria-hidden="true">${escapeHtml(w.initial||'?')}</div>`;
  const drawer = $('#leadDrawer');
  drawer.classList.add('wallet-drawer');
  drawer.setAttribute('role','dialog');
  drawer.setAttribute('aria-modal','true');
  drawer.setAttribute('aria-labelledby','drawerName');
  $('#leadDrawer .eyebrow').textContent=t('nav.wallet');
  $('#drawerName').textContent=t('wallet.title');
  $('#drawerClose').setAttribute('aria-label',t('wallet.close'));
  $('#drawerBody').innerHTML=`<div class="wallet-detail">
    <section class="wallet-detail__customer">${avatar}<div class="wallet-detail__identity">
      <span class="pay-id">${escapeHtml(w.code||'')}</span>
      <h3>${escapeHtml(w.name||'')}</h3>
      <p>${escapeHtml(w.phone||'—')}</p><p>${escapeHtml(w.email||'—')}</p>
      <div class="wallet-detail__badges">${w.tier?statusBadge(w.tier):''}${statusBadge(w.segment_label)}</div>
      <p>${escapeHtml(t('wallet.member_since'))}: ${escapeHtml(w.member_since_label||'—')} · ${escapeHtml(t('wallet.tier_since'))}: ${escapeHtml(w.tier_since_label||'—')}</p>
    </div></section>
    <section class="wallet-detail__balance"><span>${escapeHtml(t('wallet.col_balance'))}</span><strong>${fmtInt(w.balance)}</strong></section>
    <div class="wallet-detail__metrics">
      <section><span>${escapeHtml(t('wallet.col_spend'))}</span><strong>${money(w.lifetime_spend)}</strong></section>
      <section><span>${escapeHtml(t('wallet.col_stamps'))}</span><strong>${escapeHtml(t('wallet.stamp_summary', {active:fmtInt(w.stamp_active),ready:fmtInt(w.stamp_ready)}))}</strong></section>
      <section><span>${escapeHtml(t('wallet.col_activity'))}</span><strong>${escapeHtml(t('wallet.activity_summary',{count:fmtInt(w.activity_count||0)}))}</strong></section>
      <section><span>${escapeHtml(t('wallet.last_activity'))}</span><strong>${escapeHtml(w.last_activity_label||'—')}</strong></section>
    </div>
    <section class="wallet-detail__history"><h3>${escapeHtml(t('wallet.detail_recent'))}</h3>${recent}</section>
  </div>`;
  const foot = document.createElement('div');
  foot.id='walletDrawerFoot';
  foot.className='wallet-drawer__footer';
  foot.innerHTML=[
    memberUrl && boot.canShowLoyaltyMember ? `<a class="btn primary" href="${escapeHtml(memberUrl)}" target="_blank" rel="noopener">${escapeHtml(t('wallet.open_member'))}</a>` : '',
    profileUrl && boot.canViewUser ? `<a class="btn" href="${escapeHtml(profileUrl)}" target="_blank" rel="noopener">${escapeHtml(t('wallet.open_profile'))}</a>` : '',
    `<button type="button" class="btn" data-wallet-close>${escapeHtml(t('wallet.close'))}</button>`,
  ].filter(Boolean).join('');
  drawer.appendChild(foot);
  $('[data-wallet-close]',foot).onclick=closeDrawer;
  drawer.classList.add('show');
  drawer.setAttribute('aria-hidden','false');
  drawer.inert=false;
  $('.app-shell').inert=true;
  $('#drawerBackdrop').classList.add('show');
  $('#drawerBody').scrollTop=0;
  $('#drawerClose').focus({preventScroll:true});
}

// Both detail and action drawers use the same keyboard navigation contract.
document.addEventListener('keydown', e=>{
  const drawer=$('#actionDrawer.show') || $('#leadDrawer.show');
  if(!drawer) return;
  const close=drawer.id==='actionDrawer'?closeActionDrawer:closeDrawer;
  if(e.key==='Escape'){e.preventDefault();close();return;}
  if(e.key!=='Tab') return;
  const controls=$$('button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex="0"]',drawer).filter(el=>el.getClientRects().length);
  const first=controls[0],last=controls[controls.length-1];
  if(!first){e.preventDefault();return;}
  if(e.shiftKey && (document.activeElement===first || !drawer.contains(document.activeElement))){e.preventDefault();last.focus();}
  else if(!e.shiftKey && (document.activeElement===last || !drawer.contains(document.activeElement))){e.preventDefault();first.focus();}
});

const reportState = Object.fromEntries(['beauticians','branches','audit'].map(view=>[view,{q:'',sort:'revenue',page:1}]));
let reportRequest = 0;
let reportTimer = null;
let reportPayload = null;
function beauticians(){ reporting('beauticians'); }
function branches(){ reporting('branches'); }
function audit(){ reporting('audit'); }
function reporting(view){
  const filter=reportState[view];
  root.innerHTML=`<div class="report-shell ${view==='beauticians'?'beautician-report-shell':view==='branches'?'branch-report-shell':view==='audit'?'audit-report-shell':''}">
    <div class="page-head"><div><h1 class="page-title">${escapeHtml(t('nav.'+view))}</h1><p class="page-subtitle">${escapeHtml(t('reporting.'+(view==='audit'?'audit_subtitle':'subtitle')))}</p></div>
      <button type="button" class="btn" id="reportRefresh">${escapeHtml(t('reporting.refresh'))}</button></div>
    ${view==='beauticians'||view==='branches'||view==='audit'?`<section class="clearance-flow ${view==='beauticians'?'beautician-flow':view==='branches'?'branch-flow':'audit-flow'}" aria-labelledby="${view}FlowTitle"><div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">${view==='audit'?'CONTROL':'CRM'}</span><h2 id="${view}FlowTitle">${escapeHtml(t('reporting.'+(view==='beauticians'?'beautician':view==='branches'?'branch':'audit')+'_workflow_title'))}</h2><p>${escapeHtml(t('reporting.'+(view==='beauticians'?'beautician':view==='branches'?'branch':'audit')+'_workflow_hint'))}</p></div><ol class="clearance-flow__steps">${[1,2,3].map((step,index)=>`<li><span aria-hidden="true">${index+1}</span><strong>${escapeHtml(t('reporting.'+(view==='beauticians'?'beautician':view==='branches'?'branch':'audit')+'_step_'+step))}</strong></li>`).join('')}</ol></section>`:''}
    <div class="pay-metrics" id="reportMetrics" aria-live="polite"></div>
    <section class="lead-panel card"><div class="lead-panel__head"><div><h2 class="card-title">${escapeHtml(t('reporting.'+(view==='audit'?'recorded_imports':'performance')))}</h2><p class="lead-panel__sub" id="reportPeriod"></p></div><span class="lead-panel__count" id="reportCount">—</span></div>
      <div class="lead-panel__filters report-filters"><label class="lead-field"><span class="lead-field__label">${escapeHtml(t('reporting.search'))}</span><input class="lead-field__control" type="search" id="reportSearch" maxlength="150" value="${escapeHtml(filter.q)}" placeholder="${escapeHtml(t('reporting.'+(view==='audit'?'search_batch':'search_name')))}"></label>
      ${view!=='audit'?`<label class="lead-field"><span class="lead-field__label">${escapeHtml(t('reporting.sort'))}</span><select id="reportSort" class="lead-field__control">${['revenue','leads','conversion','orders'].map(k=>`<option value="${k}"${filter.sort===k?' selected':''}>${escapeHtml(t('reporting.'+k))}</option>`).join('')}</select></label>`:''}</div>
      <div id="reportTable" class="lead-panel__body pay-table" aria-live="polite"></div>
    </section>
    <p class="report-note">${escapeHtml(t('reporting.'+(view==='audit'?'audit_note':view==='beauticians'?'methodology_beauticians':'methodology_branches')))}</p>
  </div>`;
  $('#reportRefresh').onclick=()=>refreshReporting();
  $('#reportSearch').oninput=e=>{
    filter.q=e.target.value;
    filter.page=1;
    clearTimeout(reportTimer);
    if(view==='audit') reportTimer=setTimeout(()=>{if(state.view===view) refreshReporting();},350);
    else if(reportPayload) renderReportRows(view);
  };
  const sort=$('#reportSort'); if(sort) sort.onchange=e=>{filter.sort=e.target.value;if(reportPayload) renderReportRows(view);};
  refreshReporting();
}
async function refreshReporting(){
  const view=state.view; if(!reportState[view]) return;
  const requestId=++reportRequest;
  const filters=reportState[view];
  const mount=$('#reportTable');if(!mount)return;
  reportPayload=null;
  $('#reportMetrics').innerHTML='';
  mount.setAttribute('aria-busy','true');
  mount.innerHTML=`<div class="pay-empty" role="status">${escapeHtml(t('reporting.loading'))}</div>`;
  const qs=new URLSearchParams({view,page:String(filters.page)});
  if(state.period) qs.set('period',state.period);
  if(state.branch && state.branch!=='all') qs.set('branch',state.branch);
  if(view==='audit' && filters.q.trim()) qs.set('q',filters.q.trim());
  try{
    if(!boot.reportingUrl) throw new Error('Missing reporting endpoint');
    const response=await fetch(boot.reportingUrl+'?'+qs.toString(),{headers:apiHeaders(false),credentials:'same-origin'});
    if(!response.ok) throw new Error('Reporting '+response.status);
    const payload=await response.json();
    if(requestId!==reportRequest || state.view!==view)return;
    reportPayload=payload;
    const summary=payload.meta.summary;
    const keys=view==='audit'?['batches','imported','duplicates','invalid']:['leads','converted','orders','revenue'];
    $('#reportMetrics').innerHTML=keys.map((key,index)=>`<div class="pay-metric ${view==='beauticians'?'beautician-metric beautician-metric--'+index:view==='branches'?'branch-metric branch-metric--'+index:view==='audit'?'audit-metric audit-metric--'+index:''}"><span>${escapeHtml(t('reporting.'+key))}</span><strong>${key==='revenue'?money(summary[key]):fmtInt(summary[key])}</strong></div>`).join('');
    $('#reportPeriod').textContent=payload.meta.period;
    renderReportRows(view);
  }catch(err){
    if(requestId!==reportRequest || state.view!==view)return;
    $('#reportCount').textContent='—';
    mount.innerHTML=`<div class="pay-empty" role="alert"><strong>${escapeHtml(t('reporting.error'))}</strong><button type="button" class="btn" id="reportRetry">${escapeHtml(t('reporting.refresh'))}</button></div>`;
    $('#reportRetry').onclick=()=>refreshReporting();
  }finally{if(requestId===reportRequest && state.view===view)mount.setAttribute('aria-busy','false');}
}
function renderReportRows(view){
  const mount=$('#reportTable');if(!mount || !reportPayload)return;
  const filter=reportState[view];
  let rows=[...(reportPayload.data||[])];
  if(view!=='audit'){
    rows=rows.filter(r=>String(r.name).toLocaleLowerCase().includes(filter.q.trim().toLocaleLowerCase()));
    rows.sort((a,b)=>Number(b[filter.sort])-Number(a[filter.sort])||String(a.name).localeCompare(String(b.name)));
  }
  const total=view==='audit'?reportPayload.meta.total:rows.length;
  const pageMeta=view==='audit'?reportPayload.meta:{current_page:Math.max(1,Math.min(filter.page,Math.ceil(total/25)||1)),last_page:Math.ceil(total/25)||1};
  if(view!=='audit'){filter.page=pageMeta.current_page;rows=rows.slice((filter.page-1)*25,filter.page*25);}
  $('#reportCount').textContent=t('reporting.results',{count:fmtInt(total)});
  const keys=view==='audit'?['code','date','actor','branch','method','status','raw','imported','duplicates','invalid']:['name','leads','converted','conversion','follow_up','lost','orders','revenue','average_order'];
  const textKeys=['code','date','actor','branch','method','status','name'];
  const actionHead=view==='beauticians'||view==='branches'?`<th>${escapeHtml(t('reporting.actions'))}</th>`:'';
  mount.innerHTML=rows.length?`<div class="table-wrap report-table-wrap"><table class="data-table report-table"><thead><tr>${keys.map(key=>`<th${textKeys.includes(key)?'':' class="is-num"'}>${escapeHtml(t('reporting.'+key))}</th>`).join('')}${actionHead}</tr></thead><tbody>${rows.map(row=>`<tr>${keys.map(key=>{
    let value=textKeys.includes(key)?escapeHtml(key==='status'?t('reporting.status_'+row[key]):row[key]):key==='conversion'?Number(row.leads)>0?Number(row[key]).toFixed(1)+'%':'—':['revenue','average_order'].includes(key)?money(row[key]):fmtInt(row[key]);
    return `<td${textKeys.includes(key)?'':' class="is-num"'}>${['name','code'].includes(key)?`<strong>${value}</strong>`:value}</td>`;
  }).join('')}${view==='beauticians'||view==='branches'?`<td class="lead-table__actions"><div class="lead-menu ${view==='beauticians'?'beautician':'branch'}-action-menu"><button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${escapeHtml(t('reporting.row_actions',{name:row.name||''}))}"><span class="lead-menu__dots" aria-hidden="true"></span></button><div class="lead-menu__panel" role="menu" hidden><button type="button" class="lead-menu__item" role="menuitem" data-report-leads="${row.id}" data-report-scope="${view}">${escapeHtml(t('reporting.view_leads'))}</button></div></div></td>`:''}</tr>`).join('')}</tbody></table></div>`:`<div class="pay-empty"><strong>${escapeHtml(t('reporting.empty'))}</strong>${escapeHtml(t('reporting.empty_hint'))}</div>`;
  if(view==='beauticians'||view==='branches'){
    bindLeadRowMenus(mount);
    $$('[data-report-leads]',mount).forEach(btn=>btn.onclick=()=>{closeAllLeadMenus();if(btn.dataset.reportScope==='branches')state.leadBranch=btn.dataset.reportLeads;else state.leadBeautician=btn.dataset.reportLeads;closeActionDrawer();navigate('leads');});
    $$('.beautician-action-menu a[role="menuitem"],.branch-action-menu a[role="menuitem"]',mount).forEach(link=>link.onclick=()=>closeAllLeadMenus());
  }
  mount.insertAdjacentHTML('beforeend',centralPager('report',pageMeta));
  bindCentralPager('report',page=>{filter.page=page;if(view==='audit')return refreshReporting();renderReportRows(view);});
}

function generic(title,subtitle){root.innerHTML=`${pageHead(escapeHtml(title),escapeHtml(subtitle))}<section class="card"><div class="empty">${escapeHtml(subtitle)}</div></section>`}

function followup(){
  root.innerHTML = `${pageHead(t('followup.title'),t('followup.subtitle'),`<button type="button" class="btn" data-jump="leads">${escapeHtml(t('followup.open_workspace'))}</button>`)}
  <section class="follow-kpi-section" aria-labelledby="followKpiTitle">
    <div class="follow-kpi-section__head">
      <div><span class="follow-kpi-section__eyebrow">${escapeHtml(t('followup.kpi_eyebrow'))}</span><h2 id="followKpiTitle">${escapeHtml(t('followup.kpi_heading'))}</h2></div>
      <span class="follow-kpi-section__scope">${escapeHtml(t('followup.kpi_scope'))}</span>
    </div>
    <div class="grid kpi-grid" id="followKpiMount"></div>
    <p class="card-subtitle">${escapeHtml(t('followup.kpi_note'))}</p>
  </section>
  <section class="lead-panel card" style="margin-top:14px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${escapeHtml(t('followup.title'))}</h2>
        <p class="lead-panel__sub">${escapeHtml(t('followup.subtitle'))}</p>
      </div>
      <div class="lead-panel__head-meta">
        <span class="lead-panel__count" id="followResultCount">—</span>
      </div>
    </div>
    <div class="lead-panel__filters">
      <div class="tabs" id="followBuckets" role="tablist"></div>
      <label class="lead-search" for="followSearch">
        <span class="lead-search__icon" aria-hidden="true">⌕</span>
        <input class="lead-search__input" id="followSearch" type="search" autocomplete="off" placeholder="${escapeHtml(t('followup.search_placeholder'))}" value="${escapeHtml(state.followSearch)}" />
      </label>
      <div class="lead-filter-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
        <label class="lead-field">
          <span class="lead-field__label">${escapeHtml(t('workspace.filter_beautician'))}</span>
          <select class="lead-field__control" id="followBeautician"></select>
        </label>
        <label class="lead-field">
          <span class="lead-field__label">${escapeHtml(t('workspace.filter_branch'))}</span>
          <select class="lead-field__control" id="followBranch"></select>
        </label>
      </div>
    </div>
    <div class="lead-panel__body" id="followTableMount"></div>
  </section>`;
  bindJump();
  bindFollowFilters();
  refreshFollowUps();
}
async function refreshFollowUps(){
  const url = boot.followUpsUrl || '';
  if(!url){ showToast(t('followup.load_error')); return; }
  const qs = new URLSearchParams();
  if(state.followSearch) qs.set('q', state.followSearch);
  if(state.followBucket && state.followBucket!=='all') qs.set('bucket', state.followBucket);
  const branch = state.leadBranch!=='all' ? state.leadBranch : (state.branch||'all');
  if(branch && branch!=='all') qs.set('branch', branch);
  if(state.leadBeautician && state.leadBeautician!=='all') qs.set('beautician', state.leadBeautician);
  qs.set('page', String(state.followPage||1));
  followLoading = true;
  if(state.view==='followup') renderFollowTable();
  try{
    const res = await fetch(url+'?'+qs.toString(),{headers:apiHeaders(false),credentials:'same-origin'});
    if(!res.ok) throw new Error('followups '+res.status);
    const json = await res.json();
    followPageMeta=json.meta||{};
    liveFollowUps = Array.isArray(json.data) ? json.data : [];
    liveFollowSummary = (json.meta && json.meta.summary) || liveFollowSummary;
    liveFollowFilters = json.filters || liveFollowFilters;
    if(json.filters?.statuses) liveLeadFilters.statuses = json.filters.statuses;
  }catch(err){
    console.error(err);
    showToast(t('followup.load_error'));
  }finally{
    followLoading = false;
    if(state.view==='followup'){
      renderFollowKpis();
      bindFollowFilters();
      renderFollowTable();
    }
  }
}
function bindFollowFilters(){
  const buckets = (liveFollowFilters.buckets||[
    {value:'all',label:t('followup.bucket_all')},
    {value:'overdue',label:t('followup.bucket_overdue')},
    {value:'due_today',label:t('followup.bucket_due_today')},
    {value:'no_response',label:t('followup.bucket_no_response')},
    {value:'lost',label:t('followup.bucket_lost')},
  ]);
  const mount=$('#followBuckets');
  if(mount){
    mount.innerHTML=buckets.map(b=>`<button type="button" class="tab ${String(state.followBucket)===String(b.value)?'active':''}" role="tab" data-follow-bucket="${escapeHtml(b.value)}">${escapeHtml(b.label)}</button>`).join('');
    $$('[data-follow-bucket]',mount).forEach(btn=>{
      btn.onclick=()=>{
        state.followBucket=btn.dataset.followBucket||'all';
        state.followPage=1;
        refreshFollowUps();
      };
    });
  }
  const beauEl=$('#followBeautician');
  if(beauEl){
    const opts=[{id:'all',name:t('workspace.all_beauticians')}, ...((liveFollowFilters.beauticians)||liveLeadFilters.beauticians||[])];
    beauEl.innerHTML=opts.map(o=>`<option value="${escapeHtml(o.id)}" ${String(state.leadBeautician)===String(o.id)?'selected':''}>${escapeHtml(o.name)}</option>`).join('');
    beauEl.onchange=e=>{state.leadBeautician=e.target.value;state.followPage=1;refreshFollowUps();};
  }
  const branchEl=$('#followBranch');
  if(branchEl){
    const opts=[{id:'all',name:t('workspace.all_branches')}, ...((liveFollowFilters.branches)||boot.branches||[])];
    branchEl.innerHTML=opts.map(o=>`<option value="${escapeHtml(o.id)}" ${String(state.leadBranch)===String(o.id)?'selected':''}>${escapeHtml(o.name)}</option>`).join('');
    branchEl.onchange=e=>{state.leadBranch=e.target.value;state.followPage=1;refreshFollowUps();};
  }
  const search=$('#followSearch');
  if(search){
    search.oninput=e=>{
      state.followSearch=e.target.value;
      clearTimeout(followSearchTimer);
      followSearchTimer=setTimeout(()=>{state.followPage=1;refreshFollowUps();},350);
    };
  }
}
function renderFollowKpis(){
  const mount=$('#followKpiMount'); if(!mount) return;
  const s=liveFollowSummary||{};
  mount.innerHTML = `
    ${leadKpi(leadKpiIcon('queue'),t('followup.kpi_queue'),fmtInt(s.queue),t('followup.kpi_unit'),t('followup.kpi_queue_meta'),t('followup.kpi_queue_detail'),'blue')}
    ${leadKpi(leadKpiIcon('overdue'),t('followup.kpi_overdue'),fmtInt(s.overdue),t('followup.kpi_unit'),t('followup.kpi_overdue_meta'),t('followup.kpi_overdue_detail'),'rose')}
    ${leadKpi(leadKpiIcon('today'),t('followup.kpi_due_today'),fmtInt(s.due_today),t('followup.kpi_unit'),t('followup.kpi_due_meta'),t('followup.kpi_due_detail'),'green')}
    ${leadKpi(leadKpiIcon('no_response'),t('followup.kpi_no_response'),fmtInt(s.no_response),t('followup.kpi_unit'),t('followup.kpi_no_response_meta'),t('followup.kpi_no_response_detail'),'purple')}
    ${leadKpi(leadKpiIcon('lost'),t('followup.kpi_lost'),fmtInt(s.lost),t('followup.kpi_unit'),t('followup.kpi_lost_meta'),t('followup.kpi_lost_detail'),'teal')}
  `;
}
function renderFollowTable(){
  const mount=$('#followTableMount'); if(!mount) return;
  const n=Array.isArray(liveFollowUps)?liveFollowUps.length:0;
  const countEl=$('#followResultCount');
  if(countEl) countEl.textContent = n===1 ? t('followup.results_count_one') : t('followup.results_count',{count:fmtInt(n)});
  if(followLoading){
    mount.innerHTML=`<div class="lead-empty lead-empty--loading"><div class="lead-empty__spinner" aria-hidden="true"></div><strong>${escapeHtml(t('followup.loading'))}</strong></div>`;
    return;
  }
  const rows=liveFollowUps;
  if(!rows.length){
    mount.innerHTML=`<div class="lead-empty">
      <div class="lead-empty__icon" aria-hidden="true">↻</div>
      <strong>${escapeHtml(t('followup.empty'))}</strong>
      <p>${escapeHtml(t('followup.empty_hint'))}</p>
      <button type="button" class="btn" data-jump="leads">${escapeHtml(t('followup.open_workspace'))}</button>
    </div>`;
    bindJump();
    return;
  }
  const canEdit=!!boot.canEditLead;
  mount.innerHTML = `<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${escapeHtml(t('workspace.col_lead_id'))}</th>
    <th>${escapeHtml(t('workspace.col_customer'))}</th>
    <th>${escapeHtml(t('workspace.col_phone'))}</th>
    <th>${escapeHtml(t('workspace.col_beautician'))}</th>
    <th>${escapeHtml(t('workspace.col_branch'))}</th>
    <th>${escapeHtml(t('workspace.col_status'))}</th>
    <th>${escapeHtml(t('workspace.col_last_fu'))}</th>
    <th>${escapeHtml(t('followup.col_waiting'))}</th>
    <th class="lead-table__actions"><span class="sr-only">${escapeHtml(t('workspace.col_action'))}</span></th>
  </tr></thead><tbody>${rows.map(l=>{
    const name=String(l.name||'');
    const initial=escapeHtml((name[0]||'?').toUpperCase());
    const days=Number(l.days_since_followup||0);
    const waiting = l.last_followed_up_at
      ? t('followup.days',{count:days})
      : t('followup.never');
    const overdue = String(l.followup_bucket)==='overdue';
    return `<tr>
      <td><span class="lead-code">${escapeHtml(l.code||l.id)}</span></td>
      <td><div class="person-cell person-cell--lead"><div class="mini-avatar" aria-hidden="true">${initial}</div><div class="person-cell__text"><strong>${escapeHtml(name||'—')}</strong><small>${escapeHtml(l.customer||'')}</small></div></div></td>
      <td><span class="lead-mono">${escapeHtml(l.phone||'—')}</span></td>
      <td>${escapeHtml(l.beautician||'—')}</td>
      <td><span class="lead-branch">${escapeHtml(l.branch||'—')}</span></td>
      <td>${statusBadge(l.status)}</td>
      <td><span class="lead-date">${escapeHtml(l.last||'—')}</span></td>
      <td><span class="lead-wait${overdue?' is-overdue':''}">${escapeHtml(waiting)}</span></td>
      <td class="lead-table__actions">
        <div class="lead-menu">
          <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${escapeHtml(t('workspace.row_actions'))}">
            <span class="lead-menu__dots" aria-hidden="true"></span>
          </button>
          <div class="lead-menu__panel" role="menu" hidden>
            <button type="button" class="lead-menu__item" role="menuitem" data-follow-view="${escapeHtml(l.id)}">${escapeHtml(t('followup.view_lead'))}</button>
            ${canEdit?`<button type="button" class="lead-menu__item" role="menuitem" data-follow-mark="${escapeHtml(l.id)}">${escapeHtml(t('followup.mark'))}</button>`:''}
          </div>
        </div>
      </td>
    </tr>`;
  }).join('')}</tbody></table></div>`;
  mount.insertAdjacentHTML('beforeend',centralPager('follow',followPageMeta));
  bindCentralPager('follow',page=>{state.followPage=page;return refreshFollowUps();});
  bindLeadRowMenus(mount);
  $$('[data-follow-view]',mount).forEach(b=>b.onclick=()=>{ closeAllLeadMenus(); openLead(b.dataset.followView); });
  $$('[data-follow-mark]',mount).forEach(b=>b.onclick=()=>{ closeAllLeadMenus(); markLeadFollowedUp(b.dataset.followMark); });
}
async function markLeadFollowedUp(id){
  const url=leadUrl(boot.leadFollowUpUrlTemplate,id);
  if(!url){ showToast(t('followup.mark_error')); return; }
  try{
    const res=await fetch(url,{method:'POST',headers:apiHeaders(true),credentials:'same-origin',body:JSON.stringify({})});
    const body=await res.json().catch(()=>({}));
    if(!res.ok){ showToast(body.message||t('followup.mark_error')); return; }
    showToast(body.message||t('followup.marked'));
    if(state.view==='followup') await refreshFollowUps();
    else await refreshLeads();
  }catch(err){
    console.error(err);
    showToast(t('followup.mark_error'));
  }
}
function sales(){
  const m = liveMetrics || {};
  const k = m.kpis || {};
  const vs = k.vs_prev || {};
  const targets = m.targets || {};
  const dual = m.dual || {};
  const salesPct = Number(dual.sales_pct || (m.target_board && m.target_board.sales_pct) || 0);
  const salesTarget = Number(targets.sales || 0);
  const salesActual = Number(k.sales || 0);
  const salesDeltaRm = salesActual - salesTarget;
  const salesPeriodLabel = String((m.period && m.period.label) || t('common.this_month'));
  const periodLabel = escapeHtml((m.period && m.period.label) || t('common.this_month'));
  const insights = Array.isArray(m.sales_insights) ? m.sales_insights : [];
  const waterfall = Array.isArray(m.waterfall) ? m.waterfall : [];
  const maxPipe = Math.max(1, ...waterfall.map(s=>Number(s.value||0)));

  root.innerHTML = `${pageHead(
    t('sales.title'),
    t('sales.subtitle'),
    `<button type="button" class="btn soft" id="salesRefreshBtn">${escapeHtml(t('sales.refresh'))}</button>
     <button type="button" class="btn" data-jump="payments">${escapeHtml(t('sales.open_payments'))}</button>
     <button type="button" class="btn primary" data-jump="leads">${escapeHtml(t('sales.open_leads'))}</button>`
  )}
  <section class="clearance-flow sales-flow" aria-labelledby="salesFlowTitle">
    <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CRM</span><h2 id="salesFlowTitle">${escapeHtml(t('sales.workflow_title'))}</h2><p>${escapeHtml(t('sales.workflow_hint'))}</p></div>
    <ol class="clearance-flow__steps">${[t('sales.step_leads'),t('sales.step_customer'),t('sales.step_revenue')].map((label,index)=>`<li><span aria-hidden="true">${index+1}</span><strong>${escapeHtml(label)}</strong></li>`).join('')}</ol>
  </section>
  <section class="sales-kpi-section" aria-labelledby="salesKpiTitle">
    <div class="sales-kpi-section__head">
      <div><span class="sales-kpi-section__eyebrow">${escapeHtml(t('sales.kpi_eyebrow'))}</span><h2 id="salesKpiTitle">${escapeHtml(t('sales.kpi_heading'))}</h2></div>
      <span class="sales-kpi-section__scope">${escapeHtml(t('sales.kpi_scope',{period:salesPeriodLabel}))}</span>
    </div>
    <div class="grid kpi-grid" id="salesKpiMount">
      ${leadKpi(leadKpiIcon('revenue'),t('sales.kpi_sales'),fmtMoney(k.sales),t('sales.kpi_unit_revenue'),deltaTrend(vs.sales,'%'),targetHint(t('sales.kpi_sales'), fmtMoney(salesTarget)),'rose')}
      ${leadKpi(leadKpiIcon('orders'),t('sales.kpi_orders'),fmtInt(k.orders||0),t('sales.kpi_unit_orders'),deltaTrend(vs.orders||0,'%'),t('sales.kpi_orders_detail'),'blue')}
      ${leadKpi(leadKpiIcon('customers'),t('sales.kpi_customers'),fmtInt(k.buyers),t('sales.kpi_unit_customers'),deltaTrend(vs.buyers,'%'),targetHint(t('sales.kpi_customers'), fmtInt(targets.buyers||0)),'green')}
      ${leadKpi(leadKpiIcon('average'),t('sales.kpi_avg'),fmtMoney(k.avg_sale),t('sales.kpi_unit_revenue'),deltaTrend(vs.avg_sale,'%'),targetHint(t('sales.kpi_avg'), fmtMoney(targets.avg_sale||0)),'purple')}
      ${leadKpi(leadKpiIcon('target'),t('sales.kpi_target'),fmtPct(salesPct),t('sales.of_target'),salesDeltaRm>=0?'+ '+fmtMoney(Math.abs(salesDeltaRm)):'− '+fmtMoney(Math.abs(salesDeltaRm)),t('sales.kpi_target_detail',{target:fmtMoney(salesTarget),actual:fmtMoney(salesActual)}),'teal')}
    </div>
    <p class="card-subtitle">${escapeHtml(t('sales.kpi_note'))}</p>
  </section>
  <section class="sales-insights card">
    <div class="sales-insights__head">
      <div>
        <div class="journey-section__title">${escapeHtml(t('sales.insights'))}</div>
        <p class="card-subtitle" style="margin:0">${escapeHtml(t('sales.insights_hint'))} · ${periodLabel}</p>
      </div>
      <span class="sales-insights__legend"><i class="is-ok"></i>${escapeHtml(t('sales.insight_positive'))}<i class="is-warn"></i>${escapeHtml(t('sales.insight_attention'))}</span>
    </div>
    <div class="sales-insights__grid">
      ${insights.length
        ? insights.map(i=>`<article class="sales-insight sales-insight--${escapeHtml(i.tone||'info')}"><span class="sales-insight__status">${escapeHtml(i.tone==='ok'?t('sales.insight_positive'):i.tone==='warn'?t('sales.insight_attention'):t('sales.insight_info'))}</span><strong>${escapeHtml(i.title||'')}</strong><p>${escapeHtml(i.body||'')}</p></article>`).join('')
        : `<article class="sales-insight sales-insight--info"><strong>${escapeHtml(t('sales.title'))}</strong><p>${escapeHtml(t('sales.subtitle'))}</p></article>`}
    </div>
  </section>

  <div class="grid split-60" style="margin-top:12px">
    <section class="card sales-card">
      <div class="daily-panel__head">
        <div>
          <div class="daily-panel__eyebrow">${escapeHtml(periodLabel)}</div>
          <div class="daily-panel__title">${escapeHtml(t('sales.revenue_trend'))}</div>
          <div class="daily-panel__sub">${escapeHtml(t('sales.revenue_sub'))}</div>
        </div>
        <span class="sales-card__pill">${fmtMoney(salesActual)}</span>
      </div>
      <div class="chart-wrap sales-card__chart"><canvas id="salesChart"></canvas></div>
    </section>
    <section class="card target-card ${salesPct>=100?'target-card--over':''}">
      <div class="target-card__head">
        <div>
          <div class="target-card__eyebrow">${escapeHtml(t('sales.target_card'))}</div>
          <div class="target-card__title">${escapeHtml(t('overview.target_achievement'))}</div>
        </div>
        <span class="target-card__pill">${salesPct>=100?escapeHtml(t('overview.above_target')):escapeHtml(t('overview.below_target'))}</span>
      </div>
      <div class="target-card__body">
        <div class="target-card__ring" style="--p:${Math.min(100, salesPct)}">
          <svg viewBox="0 0 120 120" aria-hidden="true">
            <circle class="target-card__track" cx="60" cy="60" r="52"></circle>
            <circle class="target-card__prog" cx="60" cy="60" r="52"></circle>
          </svg>
          <div class="target-card__ring-value">
            <strong>${fmtPct(salesPct)}</strong>
            <span>${escapeHtml(t('sales.of_target'))}</span>
          </div>
        </div>
        <div class="target-card__side">
          <div class="target-card__stat"><span>${escapeHtml(t('overview.target'))}</span><strong>${fmtMoney(salesTarget)}</strong></div>
          <div class="target-card__stat"><span>${escapeHtml(t('overview.actual'))}</span><strong>${fmtMoney(salesActual)}</strong></div>
          <div class="target-card__delta">
            <strong>${salesDeltaRm>=0?'+':''}${fmtMoney(salesDeltaRm)}</strong>
            <span>${escapeHtml(t('sales.gap'))}</span>
          </div>
        </div>
      </div>
    </section>
  </div>

  <section class="card" style="margin-top:12px">
    <div class="card-title-row">
      <div>
        <div class="card-title">${escapeHtml(t('sales.pipeline'))}</div>
        <div class="card-subtitle">${escapeHtml(t('sales.pipeline_sub'))}</div>
      </div>
      <span class="badge blue">${escapeHtml(t('sales.conv_rate'))}: ${fmtPct(k.new_buyer_share_pct||0)}</span>
    </div>
    <div class="sales-funnel">
      ${waterfall.length
        ? waterfall.map((s,i)=>{
            const v=Number(s.value||0);
            const pct=Math.max(8, Math.round((v/maxPipe)*100));
            return `<div class="sales-funnel__step">
              <div class="sales-funnel__meta"><span>${escapeHtml(s.name||'')}</span><strong>${fmtInt(v)}</strong></div>
              <div class="sales-funnel__bar"><span style="width:${pct}%"></span></div>
            </div>`;
          }).join('')
        : `<div class="empty"><strong>${escapeHtml(t('sales.empty_pipeline'))}</strong></div>`}
    </div>
  </section>

  <section class="lead-panel card" style="margin-top:12px">
    <div class="lead-panel__head">
      <div class="lead-panel__intro">
        <h2 class="lead-panel__title">${escapeHtml(t('sales.attribution'))}</h2>
        <p class="lead-panel__sub">${escapeHtml(t('sales.attribution_sub'))}</p>
      </div>
    </div>
    <div class="lead-panel__body">${salesBeauticianTable()}</div>
  </section>

  <div class="grid three-col" style="margin-top:12px">
    ${(m.branches||[]).slice(0,6).map(b=>branchCard(b.name, b.new_buyers||0, b.buyers||0, b.conv||0, b.sales||0, b.avg||0, b.buyers||0)).join('')
      || `<section class="card"><div class="empty"><strong>${escapeHtml(t('sales.empty_branches'))}</strong></div></section>`}
  </div>`;

  bindJump();
  if($('#salesRefreshBtn')) $('#salesRefreshBtn').onclick=()=>refreshMetrics();
  requestAnimationFrame(()=>drawSales());
}
function salesBeauticianTable(){
  const rows = (liveMetrics && liveMetrics.beauticians && liveMetrics.beauticians.length)
    ? liveMetrics.beauticians
    : [];
  if(!rows.length) return `<div class="lead-empty"><strong>${escapeHtml(t('sales.empty_beauticians'))}</strong></div>`;
  return `<div class="table-wrap lead-table-wrap"><table class="data-table lead-table"><thead><tr>
    <th>${escapeHtml(t('sales.col_rank'))}</th>
    <th>${escapeHtml(t('sales.col_beautician'))}</th>
    <th class="is-num">${escapeHtml(t('sales.col_sales'))}</th>
    <th class="is-num">${escapeHtml(t('sales.col_customers'))}</th>
    <th class="is-num">${escapeHtml(t('sales.col_orders'))}</th>
    <th class="is-num">${escapeHtml(t('sales.col_avg'))}</th>
    <th class="is-num">${escapeHtml(t('sales.col_leads'))}</th>
    <th class="is-num">${escapeHtml(t('sales.col_conv'))}</th>
  </tr></thead><tbody>${rows.map((b,i)=>{
    const name=String(b.name||'—');
    const initial=escapeHtml((name[0]||'?').toUpperCase());
    return `<tr>
      <td><span class="rank">${i+1}</span></td>
      <td><div class="person-cell person-cell--lead"><div class="mini-avatar" aria-hidden="true">${initial}</div><div class="person-cell__text"><strong>${escapeHtml(name)}</strong></div></div></td>
      <td class="is-num"><span class="lead-money">${fmtMoney(b.sales||0)}</span></td>
      <td class="is-num">${fmtInt(b.buyers||0)}</td>
      <td class="is-num">${fmtInt(b.orders||b.order_count||0)}</td>
      <td class="is-num">${fmtMoney(b.avg||0)}</td>
      <td class="is-num">${fmtInt(b.leads||0)}</td>
      <td class="is-num">${fmtPct(b.conv||0)}</td>
    </tr>`;
  }).join('')}</tbody></table></div>`;
}
function customers(){
  const usersUrl = (boot.userEditUrlTemplate || '').replace(/\/__ID__\/edit$/, '').replace(/\/__ID__$/, '') || '';
  root.innerHTML = `<div class="cus-shell">
    <div class="page-head">
      <div>
        <h1 class="page-title">${escapeHtml(t('customers.title'))}</h1>
        <div class="page-subtitle">${escapeHtml(t('customers.subtitle'))}</div>
      </div>
      <div class="page-actions">
        <button type="button" class="btn" id="cusRefresh">${escapeHtml(t('customers.refresh'))}</button>
        ${boot.canViewUser && usersUrl ? `<a class="btn primary" href="${escapeHtml(usersUrl)}" target="_blank" rel="noopener">${escapeHtml(t('customers.open_users'))}</a>` : ''}
      </div>
    </div>
    <section class="clearance-flow customer-flow" aria-labelledby="customerFlowTitle">
      <div class="clearance-flow__copy"><span class="clearance-flow__eyebrow">CRM</span><h2 id="customerFlowTitle">${escapeHtml(t('customers.workflow_title'))}</h2><p>${escapeHtml(t('customers.workflow_hint'))}</p></div>
      <ol class="clearance-flow__steps">
        ${[t('customers.step_identify'),t('customers.step_engage'),t('customers.step_retain')].map((label,index)=>`<li><span aria-hidden="true">${index+1}</span><strong>${escapeHtml(label)}</strong></li>`).join('')}
      </ol>
    </section>
    <div class="pay-metrics" id="cusMetrics"></div>
    <section class="lead-panel card">
      <div class="lead-panel__head">
        <div class="lead-panel__intro">
          <p class="lead-panel__sub" id="cusPulse" style="margin:0">${escapeHtml(t('customers.subtitle'))}</p>
        </div>
        <div class="lead-panel__head-meta">
          <span class="lead-panel__count" id="cusResultCount">—</span>
        </div>
      </div>
      <div class="lead-panel__filters">
        <div class="tabs pay-tabs" id="cusTabs" role="tablist"></div>
        <label class="lead-search" for="cusSearch">
          <span class="lead-search__icon" aria-hidden="true">⌕</span>
          <input class="lead-search__input" id="cusSearch" type="search" autocomplete="off" placeholder="${escapeHtml(t('customers.search_placeholder'))}" value="${escapeHtml(state.customerSearch||'')}" />
        </label>
        <div class="lead-filter-grid" style="grid-template-columns:minmax(0,1fr)">
          <label class="lead-field">
            <span class="lead-field__label">${escapeHtml(t('customers.filter_branch'))}</span>
            <select class="lead-field__control" id="cusBranch"></select>
          </label>
        </div>
      </div>
      <div class="lead-panel__body pay-table" id="cusTableMount"></div>
    </section>
  </div>`;
  const refreshBtn = $('#cusRefresh');
  if(refreshBtn) refreshBtn.onclick = () => refreshCustomers();
  bindCustomerFilters();
  refreshCustomers();
}

function bindCustomerFilters(){
  const search = $('#cusSearch');
  if(search){
    search.oninput = () => {
      clearTimeout(customerSearchTimer);
      customerSearchTimer = setTimeout(() => {
        state.customerSearch = search.value.trim();
        state.customerPage = 1;
        refreshCustomers();
      }, 320);
    };
  }
  const branch = $('#cusBranch');
  if(branch) branch.onchange = () => { state.customerBranch = branch.value; state.customerPage = 1; refreshCustomers(); };
}

async function refreshCustomers(){
  const url = boot.customersUrl || '';
  const mount = $('#cusTableMount');
  if(!url){
    if(mount) mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('customers.load_error'))}</strong></div>`;
    return;
  }
  customersLoading = true;
  renderCustomerHero();
  renderCustomerTable();
  const qs = new URLSearchParams();
  if(state.customerSearch) qs.set('q', state.customerSearch);
  if(state.customerSegment && state.customerSegment !== 'all') qs.set('segment', state.customerSegment);
  const branch = state.customerBranch !== 'all' ? state.customerBranch : (state.branch || 'all');
  if(branch && branch !== 'all') qs.set('branch', branch);
  if(state.period) qs.set('period', state.period);
  qs.set('page', String(state.customerPage || 1));
  qs.set('per_page', '25');
  try{
    const res = await fetch(`${url}?${qs.toString()}`, {
      headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
      credentials: 'same-origin'
    });
    if(!res.ok) throw new Error('customers '+res.status);
    const json = await res.json();
    liveCustomers = Array.isArray(json.data) ? json.data : [];
    liveCustomerSummary = Object.assign({total:0,buyers:0,new_buyers:0,with_leads:0,period_sales:0,returning:0}, (json.meta && json.meta.summary) || {});
    liveCustomerFilters = Object.assign({segments:[],branches:[]}, json.filters || {});
    customerMeta = {
      current_page: (json.meta && json.meta.current_page) || 1,
      last_page: (json.meta && json.meta.last_page) || 1,
      total: (json.meta && json.meta.total) || 0
    };
  }catch(err){
    console.error(err);
    liveCustomers = [];
    showToast(t('customers.load_error'));
  }finally{
    customersLoading = false;
    renderCustomerHero();
    renderCustomerTabs();
    renderCustomerFilterOptions();
    renderCustomerTable();
  }
}

function renderCustomerHero(){
  const s = liveCustomerSummary;
  const pulse = $('#cusPulse');
  if(pulse){
    pulse.textContent = customerMeta.total > 0
      ? t('customers.pulse_busy', {count: fmtInt(customerMeta.total)})
      : t('customers.pulse_clear');
  }
  const metrics = $('#cusMetrics');
  if(metrics){
    metrics.innerHTML = `
      <div class="pay-metric customer-metric customer-metric--total"><span>${escapeHtml(t('customers.stat_total'))}</span><strong>${fmtInt(s.total)}</strong></div>
      <div class="pay-metric customer-metric customer-metric--buyers"><span>${escapeHtml(t('customers.stat_buyers'))}</span><strong>${fmtInt(s.buyers)}</strong></div>
      <div class="pay-metric customer-metric customer-metric--new"><span>${escapeHtml(t('customers.stat_new'))}</span><strong>${fmtInt(s.new_buyers)}</strong></div>
      <div class="pay-metric customer-metric customer-metric--sales"><span>${escapeHtml(t('customers.stat_sales'))}</span><strong>${money(s.period_sales)}</strong></div>
    `;
  }
}

function renderCustomerTabs(){
  const mount = $('#cusTabs');
  if(!mount) return;
  const s = liveCustomerSummary;
  const counts = {
    all: s.total||0,
    buyers: s.buyers||0,
    new: s.new_buyers||0,
    returning: s.returning||0,
    leads: s.with_leads||0,
  };
  const tabs = (liveCustomerFilters.segments && liveCustomerFilters.segments.length)
    ? liveCustomerFilters.segments
    : [
      {value:'all',label:t('customers.tab_all')},
      {value:'buyers',label:t('customers.tab_buyers')},
      {value:'new',label:t('customers.tab_new')},
      {value:'returning',label:t('customers.tab_returning')},
      {value:'leads',label:t('customers.tab_leads')},
    ];
  mount.innerHTML = tabs.map(tab => {
    const v = tab.value;
    const active = state.customerSegment === v ? 'active' : '';
    const n = counts[v];
    const countHtml = (n !== undefined) ? `<span class="pay-tab-count">${fmtInt(n)}</span>` : '';
    return `<button type="button" class="tab ${active}" role="tab" data-ctab="${escapeHtml(v)}">${escapeHtml(tab.label)}${countHtml}</button>`;
  }).join('');
  $$('[data-ctab]', mount).forEach(btn => {
    btn.onclick = () => {
      state.customerSegment = btn.dataset.ctab;
      state.customerPage = 1;
      refreshCustomers();
    };
  });
}

function renderCustomerFilterOptions(){
  const branch = $('#cusBranch');
  if(branch){
    const cur = state.customerBranch;
    branch.innerHTML = `<option value="all">${escapeHtml(t('customers.all_branches'))}</option>` +
      (liveCustomerFilters.branches||[]).map(b => `<option value="${b.id}" ${String(cur)===String(b.id)?'selected':''}>${escapeHtml(b.code ? b.code+' · '+b.name : b.name)}</option>`).join('');
  }
}

function renderCustomerTable(){
  const mount = $('#cusTableMount');
  const count = $('#cusResultCount');
  if(count) count.textContent = t('customers.result_count', {count: fmtInt(customerMeta.total || liveCustomers.length)});
  if(!mount) return;
  if(customersLoading){
    mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('customers.loading'))}</strong></div>`;
    return;
  }
  if(!liveCustomers.length){
    mount.innerHTML = `<div class="pay-empty"><strong>${escapeHtml(t('customers.empty'))}</strong><span>${escapeHtml(t('customers.empty_hint'))}</span></div>`;
    return;
  }
  const rows = liveCustomers.map(c => {
    const chips = [
      `<span class="pay-chip ${c.segment==='new'?'pay-chip--ok':(c.segment==='buyer'?'pay-chip--ok':'pay-chip--muted')}">${escapeHtml(c.segment_label||'')}</span>`,
      c.has_lead ? `<span class="pay-chip pay-chip--warn">${escapeHtml(t('customers.chip_lead'))}</span>` : '',
      c.loyalty_tier ? `<span class="pay-chip pay-chip--muted">${escapeHtml(c.loyalty_tier)}</span>` : '',
    ].filter(Boolean).join('');
    const avatar = c.avatar_url
      ? `<div class="mini-avatar mini-avatar--photo"><img src="${escapeHtml(c.avatar_url)}" alt=""></div>`
      : `<div class="mini-avatar">${escapeHtml(c.initial||'?')}</div>`;
    const profileUrl = boot.canViewUser && boot.userEditUrlTemplate ? leadUrl(boot.userEditUrlTemplate, c.id) : '';
    return `<tr data-customer-segment="${escapeHtml(c.segment||'registered')}">
      <td><span class="pay-id">${escapeHtml(c.code||('CUS-'+c.id))}</span></td>
      <td><div class="person-cell">${avatar}<div><strong>${escapeHtml(c.name||'')}</strong><small>${escapeHtml(c.phone||c.email||'')}</small></div></div></td>
      <td>${escapeHtml(c.branch||'—')}</td>
      <td><strong>${fmtInt(c.paid_orders_count)}</strong><div class="pay-method">${fmtInt(c.orders_count)} total</div></td>
      <td><div class="pay-amount">${money(c.paid_sales)}</div><div class="pay-method">${money(c.period_sales)} ${escapeHtml(t('customers.detail_period').toLowerCase())}</div></td>
      <td>${escapeHtml(c.last_order_label||'—')}</td>
      <td><div class="pay-chips">${chips}</div></td>
      <td class="lead-table__actions"><div class="lead-menu customer-action-menu">
        <button type="button" class="lead-menu__btn" data-lead-menu aria-haspopup="menu" aria-expanded="false" aria-label="${escapeHtml(t('customers.row_actions',{name:c.name||t('customers.guest')}))}"><span class="lead-menu__dots" aria-hidden="true"></span></button>
        <div class="lead-menu__panel" role="menu" hidden>
          <button type="button" class="lead-menu__item" role="menuitem" data-customer="${c.id}">${escapeHtml(t('customers.view'))}</button>
          <button type="button" class="lead-menu__item" role="menuitem" data-customer-payments="${c.id}" data-customer-name="${escapeHtml(c.name||'')}" data-customer-phone="${escapeHtml(c.phone||'')}" data-customer-email="${escapeHtml(c.email||'')}">${escapeHtml(t('customers.open_payments'))}</button>
          ${profileUrl ? `<a class="lead-menu__item" role="menuitem" href="${escapeHtml(profileUrl)}" target="_blank" rel="noopener">${escapeHtml(t('customers.open_profile'))}</a>` : ''}
        </div>
      </div></td>
    </tr>`;
  }).join('');
  const pager = centralPager('cus',customerMeta);
  mount.innerHTML = `<div class="table-wrap"><table class="data-table"><thead><tr>
    <th>${escapeHtml(t('customers.col_id'))}</th>
    <th>${escapeHtml(t('customers.col_customer'))}</th>
    <th>${escapeHtml(t('customers.col_branch'))}</th>
    <th>${escapeHtml(t('customers.col_orders'))}</th>
    <th>${escapeHtml(t('customers.col_sales'))}</th>
    <th>${escapeHtml(t('customers.col_last'))}</th>
    <th>${escapeHtml(t('customers.col_segment'))}</th>
    <th>${escapeHtml(t('customers.col_action'))}</th>
  </tr></thead><tbody>${rows}</tbody></table></div>${pager}`;
  bindLeadRowMenus(mount);
  $$('[data-customer]', mount).forEach(b => b.onclick = () => { closeAllLeadMenus(); reviewCustomer(Number(b.dataset.customer)); });
  $$('[data-customer-payments]', mount).forEach(b => b.onclick = () => {
    closeAllLeadMenus();
    jumpToCustomerPayments({id:b.dataset.customerPayments,name:b.dataset.customerName,phone:b.dataset.customerPhone,email:b.dataset.customerEmail});
  });
  $$('.customer-action-menu a[role="menuitem"]',mount).forEach(link => link.onclick = () => closeAllLeadMenus());
  bindCentralPager('cus',page=>{state.customerPage=page;return refreshCustomers();});
}

function reviewCustomer(id){
  const c = liveCustomers.find(x => Number(x.id) === Number(id));
  if(!c) return;
  const profileUrl = leadUrl(boot.userEditUrlTemplate, c.id);
  const canView = !!boot.canViewUser;
  const avatar = c.avatar_url
    ? `<div class="pay-review__avatar" style="padding:0;overflow:hidden"><img src="${escapeHtml(c.avatar_url)}" alt="" style="width:100%;height:100%;object-fit:cover"></div>`
    : `<div class="pay-review__avatar">${escapeHtml(c.initial||'?')}</div>`;
  const body = `<div class="pay-review">
    <div class="pay-review__hero">
      ${avatar}
      <div style="min-width:0;flex:1">
        <div class="pay-id">${escapeHtml(c.code||'')}</div>
        <strong style="display:block;margin-top:6px;font-size:16px">${escapeHtml(c.name||'')}</strong>
        <div class="pay-method">${escapeHtml(c.phone||'—')} · ${escapeHtml(c.email||'—')}</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">${statusBadge(c.segment_label)}${c.loyalty_tier?statusBadge(c.loyalty_tier):''}</div>
      </div>
      <div style="text-align:right"><div class="pay-amount">${money(c.paid_sales)}</div><div class="pay-method">${escapeHtml(t('customers.detail_sales'))}</div></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${escapeHtml(t('customers.detail_orders'))}</label><strong>${fmtInt(c.paid_orders_count)}</strong></div>
      <div class="pay-review__card"><label>${escapeHtml(t('customers.detail_period'))}</label><strong>${money(c.period_sales)}</strong></div>
      <div class="pay-review__card"><label>${escapeHtml(t('customers.detail_leads'))}</label><strong>${fmtInt(c.leads_count)}</strong></div>
    </div>
    <div class="pay-review__grid">
      <div class="pay-review__card"><label>${escapeHtml(t('customers.col_branch'))}</label><strong>${escapeHtml(c.branch_name||c.branch||'—')}</strong></div>
      <div class="pay-review__card"><label>${escapeHtml(t('customers.col_last'))}</label><strong>${escapeHtml(c.last_order_label||'—')}</strong></div>
      <div class="pay-review__card"><label>${escapeHtml(t('customers.col_orders'))}</label><strong>${fmtInt(c.orders_count)}</strong></div>
    </div>
  </div>`;
  const foot = [
    canView ? `<a class="btn primary" href="${escapeHtml(profileUrl)}" target="_blank" rel="noopener">${escapeHtml(t('customers.open_profile'))}</a>` : '',
    `<button type="button" class="btn" data-jump="payments" data-customer-id="${escapeHtml(String(c.id))}" data-customer-name="${escapeHtml(c.name||'')}" data-customer-phone="${escapeHtml(c.phone||'')}" data-customer-email="${escapeHtml(c.email||'')}">${escapeHtml(t('customers.open_payments'))}</button>`,
    `<button type="button" class="btn" data-action-drawer-close>${escapeHtml(t('customers.close'))}</button>`,
  ].filter(Boolean).join('');
  openActionDrawer(t('customers.title'), c.code+' · '+c.name, body, foot, 'Customer');
  setTimeout(() => {
    $$('#actionDrawerFoot [data-jump]').forEach(b => b.onclick = () => {
      closeActionDrawer();
      if(b.dataset.jump === 'payments' && b.dataset.customerId){
        jumpToCustomerPayments({
          id: b.dataset.customerId,
          name: b.dataset.customerName,
          phone: b.dataset.customerPhone,
          email: b.dataset.customerEmail
        });
        return;
      }
      navigate(b.dataset.jump);
    });
  }, 0);
}


function drawDonut(){
  const c=$('#donutChart'); if(!c) return; fitCanvas(c);
  const ctx=c.getContext('2d'); const w=c.clientWidth, h=c.clientHeight;
  const mix=(liveMetrics&&liveMetrics.status_mix)||[];
  const colors=['#2563eb','#0ea5e9','#059669','#d97706','#e11d48','#6366f1'];
  const parts = mix.map((s,i)=>({label:s.name, value:Number(s.value||0), color:colors[i%colors.length]}));
  const total = parts.reduce((a,b)=>a+b.value,0);
  if(!parts.length || total<=0){
    ctx.clearRect(0,0,w,h);
    ctx.fillStyle='#94a3b8';
    ctx.font='12px Poppins,sans-serif';
    ctx.textAlign='center';
    ctx.fillText(t('overview.no_branch_data'), w/2, h/2);
    const legend=$('#leadLegend');
    if(legend) legend.innerHTML='';
    return;
  }
  let a0=-Math.PI/2;
  const cx=w/2, cy=h/2, r=Math.min(w,h)/2-8;
  parts.forEach(p=>{
    const a1=a0+(p.value/total)*Math.PI*2;
    ctx.beginPath(); ctx.moveTo(cx,cy); ctx.arc(cx,cy,r,a0,a1); ctx.closePath();
    ctx.fillStyle=p.color; ctx.fill();
    a0=a1;
  });
  ctx.beginPath(); ctx.arc(cx,cy,r*0.58,0,Math.PI*2); ctx.fillStyle='#fff'; ctx.fill();
  const legend=$('#leadLegend');
  if(legend){
    legend.innerHTML = parts.map(p=>`<div class="legend-row"><span class="dot" style="background:${p.color}"></span><span>${escapeHtml(p.label)}</span><strong>${fmtInt(p.value)}</strong><span class="pct">${((p.value/total)*100).toFixed(1)}%</span></div>`).join('');
  }
}
function drawSales(){
  const c=$('#salesChart'); if(!c) return; fitCanvas(c);
  const ctx=c.getContext('2d'); const w=c.clientWidth, h=c.clientHeight;
  const eq = (liveMetrics&&liveMetrics.equity)||{};
  const labels = Array.isArray(eq.labels) ? eq.labels : [];
  let vals;
  if(eq.actual && eq.actual.length){
    vals = eq.actual.map((v,i)=> i===0 ? Number(v) : Math.max(0, Number(v)-Number(eq.actual[i-1])));
    // convert to k for labels
    vals = vals.map(v=>Math.round(v/1000));
  } else {
    vals = [];
  }
  const target = Math.round(((liveMetrics&&liveMetrics.targets&&liveMetrics.targets.sales)||0)/1000);
  const p={l:36,r:16,t:28,b:36};
  const max=Math.max(target, ...vals, 1)*1.15;
  const x=i=>p.l+(i/(Math.max(vals.length-1,1)))*(w-p.l-p.r);
  const y=v=>h-p.b-((v/max)*(h-p.t-p.b));
  ctx.strokeStyle='#e2e8f0'; ctx.lineWidth=1;
  for(let i=0;i<4;i++){const yy=p.t+((h-p.t-p.b)/3)*i; ctx.beginPath(); ctx.moveTo(p.l,yy); ctx.lineTo(w-p.r,yy); ctx.stroke();}
  const ty=y(target);
  ctx.setLineDash([5,5]); ctx.strokeStyle='#d97706'; ctx.beginPath(); ctx.moveTo(p.l,ty); ctx.lineTo(w-p.r,ty); ctx.stroke(); ctx.setLineDash([]);
  ctx.fillStyle='#64748b'; ctx.font='600 10px Poppins'; ctx.textAlign='left'; ctx.fillText('Target', p.l+4, ty-6);
  const navy='#1d4ed8', sky='#0ea5e9';
  const area=ctx.createLinearGradient(0,p.t,0,h-p.b);
  area.addColorStop(0,'rgba(37,99,235,.22)'); area.addColorStop(1,'rgba(14,165,233,.02)');
  ctx.beginPath(); ctx.moveTo(x(0),h-p.b);
  vals.forEach((v,i)=>ctx.lineTo(x(i),y(v)));
  ctx.lineTo(x(vals.length-1),h-p.b); ctx.closePath(); ctx.fillStyle=area; ctx.fill();
  ctx.beginPath(); vals.forEach((v,i)=> i?ctx.lineTo(x(i),y(v)):ctx.moveTo(x(i),y(v)));
  ctx.strokeStyle=navy; ctx.lineWidth=2.75; ctx.lineJoin='round'; ctx.lineCap='round'; ctx.stroke();
  const step=Math.max(1, Math.ceil(vals.length/8));
  vals.forEach((v,i)=>{
    if(i%step && i!==vals.length-1) return;
    const px=x(i), py=y(v);
    ctx.beginPath(); ctx.arc(px,py,4,0,Math.PI*2); ctx.fillStyle='#fff'; ctx.fill();
    ctx.lineWidth=2; ctx.strokeStyle=sky; ctx.stroke();
    ctx.fillStyle='#0f172a'; ctx.font='700 10px Poppins'; ctx.textAlign='center';
    ctx.fillText('RM'+v+'k', px, py-10);
    ctx.fillStyle='#64748b'; ctx.font='600 10px Poppins';
    ctx.fillText(labels[i]||'', px, h-12);
  });
}
function drawBranch(){if(!$('#branchChart'))return;const c=$('#branchChart'),ctx=c.getContext('2d'),w=c.clientWidth,h=c.clientHeight,p={l:45,r:20,t:20,b:35};fitCanvas(c);const branches=Array.isArray(liveMetrics&&liveMetrics.branches)?liveMetrics.branches:[];const vals=branches.map(b=>Number(b.new_buyer_share_pct)||Number(b.conv)||0);const labels=branches.map(b=>String(b.name||'').slice(0,2).toUpperCase()||'—');if(!vals.length)return;const max=Math.max(...vals,50);const gap=Math.max(8,(w-p.l-p.r-70*vals.length)/(vals.length+1));const barW=Math.min(70,(w-p.l-p.r-gap*(vals.length+1))/vals.length);vals.forEach((v,i)=>{const x=p.l+gap+(barW+gap)*i,y=h-p.b-(v/max)*(h-p.t-p.b);ctx.fillStyle=[themeColor('--brand','#38bdf8'),themeColor('--rose','#0ea5e9'),themeColor('--navy','#2563eb')][i%3];ctx.fillRect(x,y,barW,h-p.b-y);ctx.fillStyle=themeColor('--navy','#1d4ed8');ctx.textAlign='center';ctx.font='700 14px Poppins';ctx.fillText(v+'%',x+barW/2,y-8);ctx.font='13px Poppins';ctx.fillText(labels[i],x+barW/2,h-12)})}
function fitCanvas(c){const dpr=Math.max(1,window.devicePixelRatio||1);const rect=c.getBoundingClientRect();c.width=rect.width*dpr;c.height=rect.height*dpr;c.getContext('2d').setTransform(dpr,0,0,dpr,0,0)}

const CENTRAL_VIEWS = ['overview','leads','import','imports','followup','sales','payments','customers','wallet','checkin','clearance','beauticians','branches','audit'];
const centralBasePath = String(boot.basePath || '').replace(/\/$/, '') || '/admin/leads/central';

function pathForView(view){
  const v = CENTRAL_VIEWS.includes(view) ? view : 'overview';
  return v === 'overview' ? centralBasePath : (centralBasePath + '/' + v);
}
function viewFromPath(pathname){
  const path = String(pathname || location.pathname).replace(/\/$/, '');
  if(path === centralBasePath) return 'overview';
  if(path.startsWith(centralBasePath + '/')){
    const seg = path.slice(centralBasePath.length + 1).split('/')[0];
    return CENTRAL_VIEWS.includes(seg) ? seg : 'overview';
  }
  return 'overview';
}
function scopeSearch(){
  const params=new URLSearchParams(location.search);
  params.set('branch',state.branch || 'all');
  if(state.period) params.set('period',state.period); else params.delete('period');
  return '?'+params.toString();
}
function syncUrl(view, {replace=false, silent=false}={}){
  const next=pathForView(view)+scopeSearch()+location.hash;
  if(!silent && (replace || next!==location.pathname+location.search+location.hash)){
    history[replace?'replaceState':'pushState']({view},'',next);
  }
  // Cmd/Ctrl-click must open the same filtered view too.
  $$('.nav-item[data-view]').forEach(link=>link.href=pathForView(link.dataset.view)+scopeSearch());
}
function restoreScopeFromUrl(){
  const params=new URLSearchParams(location.search);
  const branch=params.get('branch') || 'all';
  const period=params.get('period') || $('#periodScope').options[0]?.value || '';
  const branchSelect=$('#branchScope'), periodSelect=$('#periodScope');
  branchSelect.value=branch;
  state.branch=branchSelect.value || 'all';
  branchSelect.value=state.branch;
  if(/^\d{4}-(0[1-9]|1[0-2])$/.test(period) && ![...periodSelect.options].some(o=>o.value===period)){
    periodSelect.add(new Option(period,period));
  }
  periodSelect.value=period;
  state.period=periodSelect.value || periodSelect.options[0]?.value || '';
  periodSelect.value=state.period;
}
function changeCentralScope(){
  state.branch=$('#branchScope').value || 'all';
  state.period=$('#periodScope').value || '';
  if(reportState[state.view]) reportState[state.view].page=1;
  navigate(state.view);
}

function navigate(view, opts={}){
  closeNotifications();
  const target = CENTRAL_VIEWS.includes(view) ? view : 'overview';
  closeDrawer();
  closeActionDrawer();
  if(window.IMMA_TRADE && target!=='overview') IMMA_TRADE.dispose();
  state.view=target;
  const membershipGlobal=target==='wallet';
  for(const scope of [$('#branchScope'),$('#periodScope')]){
    scope.disabled=membershipGlobal;
    scope.title=membershipGlobal?t('wallet.scope_hint'):'';
  }
  syncUrl(target, opts);
  $$('.nav-item[data-view]').forEach(n=>n.classList.toggle('active',n.dataset.view===target));
  $('#crumbCurrent').textContent=({overview:t('common.overview_crumb'),leads:t('nav.leads'),import:t('nav.import'),imports:t('nav.imports'),payments:t('nav.payments'),wallet:t('nav.wallet'),checkin:t('nav.checkin'),clearance:t('nav.clearance'),beauticians:t('nav.beauticians'),branches:t('nav.branches'),audit:t('nav.audit'),followup:t('nav.followup'),sales:t('nav.sales'),customers:t('nav.customers')})[target]||target;
  const metricsStale=metricsScope!==JSON.stringify([state.branch,state.period]);
  if(['overview','sales'].includes(target) && metricsStale){
    root.innerHTML=`<section class="card"><div class="pay-empty" role="status">${escapeHtml(t('overview.loading'))}</div></section>`;
    refreshMetrics();
  }else{
    ({overview,leads,import:importView,imports,followup,sales,payments,customers,wallet,checkin,clearance,beauticians,branches,audit}[target]||(()=>generic(t('nav.'+target),t('operations.not_ready'))))();
    if(metricsStale) refreshMetrics();
  }
  if(!opts.silent) window.scrollTo({top:0,behavior:'smooth'});
  if(innerWidth<1000)$('#sidebar').classList.remove('open');
}
function bindJump(){
  $$('[data-jump]').forEach(b=>b.onclick=()=>{
    if(b.dataset.importTab) state.importTab=b.dataset.importTab;
    navigate(b.dataset.jump);
  });
}
let actionDrawerTrigger=null;
let actionDrawerScroll='';
let actionDrawerDirty=false;
function openActionDrawer(title,sub,body,foot,eyebrow=''){
  closeDrawer();
  closeActionDrawer();
  actionDrawerTrigger=document.activeElement;
  actionDrawerScroll=document.body.style.overflow;
  $('#actionDrawerEyebrow').textContent=eyebrow;
  $('#actionDrawerTitle').textContent=title;
  $('#actionDrawerBody').innerHTML=`<p class="action-drawer__subtitle">${escapeHtml(sub)}</p>${body}`;
  $('#actionDrawerFoot').innerHTML=foot;
  $('#actionDrawerClose').setAttribute('aria-label',t('wallet.close'));
  $('#actionDrawer').classList.add('show');
  $('#actionDrawer').setAttribute('aria-hidden','false');
  $('#actionDrawer').inert=false;
  $('#actionDrawerBackdrop').classList.add('show');
  $('.app-shell').inert=true;
  document.body.style.overflow='hidden';
  $('#actionDrawerBody').scrollTop=0;
  $('#actionDrawerClose').focus({preventScroll:true});
  const save=$('#saveLead');
  if(save) save.onclick=()=>saveLeadFromDrawer();
  const leadForm=$('#leadEditorForm');
  if(leadForm){
    actionDrawerDirty=false;
    leadForm.addEventListener('submit',e=>{e.preventDefault();saveLeadFromDrawer();});
    leadForm.addEventListener('input',()=>{actionDrawerDirty=true;setLeadFormError('');});
    leadForm.addEventListener('change',()=>{actionDrawerDirty=true;setLeadFormError('');});
  }
  $$('[data-action-drawer-close]',$('#actionDrawer')).forEach(x=>x.onclick=closeActionDrawer);
}
function closeActionDrawer(){
  const drawer=$('#actionDrawer');
  if(!drawer.classList.contains('show')) return;
  if(actionDrawerDirty && !window.confirm(t('workspace.discard_changes'))) return false;
  stopCheckinScanner();
  drawer.classList.remove('show');
  drawer.setAttribute('aria-hidden','true');
  drawer.inert=true;
  $('#actionDrawerBackdrop').classList.remove('show');
  $('.app-shell').inert=false;
  document.body.style.overflow=actionDrawerScroll;
  if(actionDrawerTrigger?.isConnected) actionDrawerTrigger.focus({preventScroll:true});
  actionDrawerTrigger=null;
  actionDrawerDirty=false;
  return true;
}
function closeDrawer(){
  const drawer=$('#leadDrawer');
  if(!drawer.classList.contains('show')) return;
  drawer.classList.remove('show');
  drawer.inert=true;
  $('.app-shell').inert=false;
  $('#drawerBackdrop').classList.remove('show');
  drawer.setAttribute('aria-hidden','true');
  if(drawer.classList.contains('wallet-drawer')){
    drawer.classList.remove('wallet-drawer');
    $('#walletDrawerFoot')?.remove();
  }
  document.body.style.overflow=walletDrawerScroll;
  if(walletDrawerTrigger?.isConnected) walletDrawerTrigger.focus({preventScroll:true});
  walletDrawerTrigger=null;
}
let notificationDismissalsMemory={};
function notificationStorageKey(){
  return String(boot.notificationStorageKey||'imma-central.notifications.v1.guest');
}
function notificationScope(){
  return `${state.branch||'all'}|${state.period||''}`;
}
function notificationSignature(item){
  return `${notificationScope()}|${item.id}|${item.count}`;
}
function readNotificationDismissals(){
  try{
    const value=JSON.parse(localStorage.getItem(notificationStorageKey())||'{}');
    return value && typeof value==='object' && !Array.isArray(value) ? value : {};
  }catch(_){
    return {...notificationDismissalsMemory};
  }
}
function writeNotificationDismissals(value){
  const cutoff=Date.now()-(90*24*60*60*1000);
  const entries=Object.entries(value)
    .filter(([,timestamp])=>Number(timestamp)>=cutoff)
    .sort((a,b)=>Number(a[1])-Number(b[1]))
    .slice(-100);
  notificationDismissalsMemory=Object.fromEntries(entries);
  try{localStorage.setItem(notificationStorageKey(),JSON.stringify(notificationDismissalsMemory));}catch(_){}
}
function notificationIcon(kind){
  const icons={
    payments:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/></svg>',
    clearance:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>',
    leads:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M3 20c.7-4 2.7-6 6-6 2.1 0 3.7.8 4.8 2.3M18 8v6M15 11h6"/></svg>'
  };
  return icons[kind]||icons.leads;
}
function notificationCandidates(){
  const m=liveMetrics||{};
  const items=[];
  const payments=Number(m.ops?.payments?.queue||0);
  const clearance=Number(m.ops?.clearance?.queue||0);
  const leads=Number(m.kpis?.new_buyers||m.kpis?.unique_leads||0);
  if(payments>0) items.push({id:'payments',count:payments,icon:notificationIcon('payments'),title:t('notifications.payment_title'),detail:t('notifications.payment_detail',{count:fmtInt(payments)}),view:'payments'});
  if(clearance>0) items.push({id:'clearance',count:clearance,icon:notificationIcon('clearance'),title:t('notifications.clearance_title'),detail:t('notifications.clearance_detail',{count:fmtInt(clearance)}),view:'clearance'});
  if(leads>0) items.push({id:'leads',count:leads,icon:notificationIcon('leads'),title:t('notifications.leads_title'),detail:t('notifications.leads_detail',{count:fmtInt(leads),period:m.period?.label||t('notifications.selected_period')}),view:'leads'});
  return items;
}
function notificationItems(){
  const dismissed=readNotificationDismissals();
  return notificationCandidates().filter(item=>!dismissed[notificationSignature(item)]);
}
function dismissNotificationItems(items){
  const dismissed=readNotificationDismissals();
  const now=Date.now();
  items.forEach(item=>{dismissed[notificationSignature(item)]=now;});
  writeNotificationDismissals(dismissed);
}
function renderNotifications(){
  const button=$('#notificationButton'), menu=$('#notificationMenu'), badge=$('#notificationCount');
  if(!button||!menu||!badge)return;
  const candidates=notificationCandidates();
  const items=notificationItems();
  badge.textContent=String(items.length); badge.hidden=items.length===0;
  button.setAttribute('aria-label',items.length?t('notifications.button_count',{count:fmtInt(items.length)}):t('notifications.button'));
  const status=items.length?t('notifications.active_count',{count:fmtInt(items.length)}):(candidates.length?t('notifications.cleared'):t('notifications.all_clear'));
  const empty=candidates.length?t('notifications.cleared_hint'):t('notifications.empty');
  menu.innerHTML=`<div class="notification-menu__head"><div class="notification-menu__title"><span id="notificationMenuTitle">${escapeHtml(t('notifications.title'))}</span><small>${escapeHtml(status)}</small></div>${items.length?`<button type="button" class="notification-menu__clear" data-clear-notifications>${escapeHtml(t('notifications.clear'))}</button>`:''}</div>`+
    (items.length?items.map(item=>`<button type="button" class="notification-menu__item" data-notification-view="${escapeHtml(item.view)}"><span class="notification-menu__icon">${item.icon}</span><span><strong>${escapeHtml(item.title)}</strong><small>${escapeHtml(item.detail)}</small></span></button>`).join('')+`<p class="notification-menu__note">${escapeHtml(t('notifications.clear_note'))}</p>`:`<div class="notification-menu__empty">${escapeHtml(empty)}</div>`);
}
function requestClearNotifications(){
  const items=notificationItems();
  if(!items.length)return;
  closeNotifications();
  const body=`<div class="notification-clear-summary"><strong>${escapeHtml(t('notifications.clear_summary',{count:fmtInt(items.length)}))}</strong><p>${escapeHtml(t('notifications.clear_note'))}</p></div>`;
  const foot=`<button type="button" class="btn" data-action-drawer-close>${escapeHtml(t('notifications.cancel'))}</button><button type="button" class="btn danger" id="notificationClearConfirm">${escapeHtml(t('notifications.confirm_clear'))}</button>`;
  openActionDrawer(t('notifications.clear_title'),t('notifications.clear_subtitle'),body,foot,t('notifications.title'));
  $('#notificationClearConfirm').onclick=()=>{
    dismissNotificationItems(items);
    closeActionDrawer();
    renderNotifications();
    $('#notificationButton')?.focus({preventScroll:true});
    showToast(t('notifications.clear_success'));
  };
}
function toggleNotifications(force=null){
  const button=$('#notificationButton'), menu=$('#notificationMenu');
  if(!button||!menu)return;
  const open=force===null?menu.hidden:!force;
  if(!menu.hidden===open)return;
  renderNotifications(); menu.hidden=!open; button.setAttribute('aria-expanded',String(open));
}
function closeNotifications(){
  const button=$('#notificationButton'), menu=$('#notificationMenu');
  if(!button||!menu||menu.hidden)return;
  menu.hidden=true;
  button.setAttribute('aria-expanded','false');
}
function showToast(msg){const t=$('#toast');t.textContent=msg;t.classList.add('show');clearTimeout(showToast._t);showToast._t=setTimeout(()=>t.classList.remove('show'),2300)}
window.showToast=showToast;window.navigate=navigate;window.$=$;

$$('.nav-item[data-view]').forEach(n=>n.addEventListener('click',e=>{
  if(e.defaultPrevented) return;
  if(e.metaKey||e.ctrlKey||e.shiftKey||e.altKey||e.button!==0) return;
  e.preventDefault();
  if(n.dataset.view === 'payments'){
    state.paymentCustomerId = null;
    state.paymentCustomerLabel = '';
  }
  navigate(n.dataset.view);
}));
let centralPrintTitle=null;
function prepareCentralPrint(){
  const title=$('#crumbCurrent').textContent;
  const scopes=[$('#branchScope'),$('#periodScope'),...$$('select,input[type="search"],input[type="date"]',root)]
    .filter(el=>el && !el.closest('[hidden]'))
    .map(el=>{
      const value=el.tagName==='SELECT'?el.selectedOptions[0]?.textContent:el.value;
      if(!value?.trim()) return '';
      const label=el.closest('label')?.querySelector('.lead-field__label')?.textContent || el.parentElement.querySelector('label')?.textContent || '';
      return label?label.trim()+': '+value.trim():value.trim();
    }).filter(Boolean);
  // Global scopes stay relevant when their controls are collapsed on mobile.
  for(const select of [$('#branchScope'),$('#periodScope')]){
    const value=select.selectedOptions[0]?.textContent?.trim();
    if(value && !scopes.includes(value)) scopes.unshift(value);
  }
  $$('.tab.active,[role="tab"][aria-selected="true"]',root).forEach(el=>scopes.push(el.textContent.trim()));
  $('#centralPrintHeader').innerHTML=`<div class="central-print-brand">${escapeHtml(t('brand_subtitle'))}</div><h1>${escapeHtml(title)}</h1><p>${escapeHtml([...new Set(scopes)].join(' · '))}</p><p>${escapeHtml(t('export_pdf.generated'))}: ${escapeHtml(new Date().toLocaleString(boot.locale==='ms'?'ms-MY':'en-MY'))}</p><small>${escapeHtml(t('export_pdf.scope'))}</small>`;
  const actionLabels=['workspace','payments','customers','wallet','checkin','clearance'].map(key=>t(key+'.col_action').toLowerCase());
  $$('table',root).forEach(table=>{
    const headers=$$('thead tr:last-child th',table);
    headers.forEach((th,index)=>{
      if(actionLabels.includes(th.textContent.trim().toLowerCase())){
        th.classList.add('central-print-action');
        $$('tbody tr',table).forEach(row=>row.children[index]?.classList.add('central-print-action'));
      }
    });
  });
  if(centralPrintTitle===null) centralPrintTitle=document.title;
  document.title='Central - '+title+' - '+(state.period||'');
}
function finishCentralPrint(){
  if(centralPrintTitle!==null){document.title=centralPrintTitle;centralPrintTitle=null;}
  $$('.central-print-action',root).forEach(el=>el.classList.remove('central-print-action'));
}
$('#exportCentralPdf').onclick=async()=>{
  const loading={leads:leadsLoading,followup:followLoading,payments:paymentsLoading,customers:customersLoading,wallet:walletsLoading,checkin:checkinsLoading,clearance:clearancesLoading};
  if(loading[state.view] || $('[aria-busy="true"],[role="status"]',root)){
    showToast(t('export_pdf.loading'));return;
  }
  const button=$('#exportCentralPdf');button.disabled=true;
  try{
    if(document.fonts?.ready) await document.fonts.ready;
    prepareCentralPrint();
    window.print();
  }finally{button.disabled=false;}
};
window.addEventListener('beforeprint',prepareCentralPrint);
window.addEventListener('afterprint',finishCentralPrint);

$('#menuToggle').onclick=()=>$('#sidebar').classList.toggle('open');
$('#notificationButton').onclick=()=>toggleNotifications();
$('#notificationMenu').onclick=e=>{
  if(e.target.closest('[data-clear-notifications]')){requestClearNotifications();return;}
  const item=e.target.closest('[data-notification-view]');if(!item)return;closeNotifications();navigate(item.dataset.notificationView);
};
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!$('#notificationMenu').hidden){e.preventDefault();closeNotifications();$('#notificationButton').focus({preventScroll:true});}});
document.addEventListener('pointerdown',e=>{if(!e.target.closest('.notification-wrap'))closeNotifications();},true);
renderNotifications();
$('#drawerClose').onclick=closeDrawer;$('#drawerBackdrop').onclick=closeDrawer;$('#actionDrawerClose').onclick=closeActionDrawer;$('#actionDrawerBackdrop').onclick=closeActionDrawer;
$('#branchScope').onchange=changeCentralScope;
$('#periodScope').onchange=changeCentralScope;
$('#globalSearch').addEventListener('keydown',e=>{if(e.key==='Enter'){navigate('leads');state.leadSearch=e.target.value;state.leadPage=1;}});
window.addEventListener('resize',()=>{if(state.view==='overview'){drawDailyLeadChart();drawDailyTrendChart();drawDonut();drawSales();if(window.IMMA_TRADE)IMMA_TRADE.resize()} if(state.view==='sales') drawSales(); if(state.view==='branches')drawBranch()});
window.addEventListener('popstate',()=>{restoreScopeFromUrl();navigate(viewFromPath(),{silent:true});});

navigate(boot.initialView || viewFromPath(), {replace:true});
