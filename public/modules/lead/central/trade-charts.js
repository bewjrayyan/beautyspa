/* IMMA Central — lead→sales chart helpers (ECharts, trade-style cues) */
(function (global) {
  'use strict';

  const charts = new Map();
  const CYAN = '#0284c7';
  const EMERALD = '#059669';
  const ROSE = '#e11d48';
  const AMBER = '#d97706';
  const GRID = 'rgba(148,163,184,.28)';
  const MUTED = '#64748b';
  const TEXT = '#0f172a';

  function t(path, replace) {
    if (typeof global.t === 'function') return global.t(path, replace || {});
    const parts = String(path).split('.');
    let cur = (global.IMMA_CENTRAL && global.IMMA_CENTRAL.i18n) || {};
    for (const p of parts) {
      if (cur && typeof cur === 'object' && p in cur) cur = cur[p];
      else { cur = null; break; }
    }
    let out = typeof cur === 'string' ? cur : path;
    Object.keys(replace || {}).forEach((k) => {
      out = out.replace(new RegExp(':' + k, 'g'), String(replace[k]));
    });
    return out;
  }

  function disposeAll() {
    charts.forEach((c) => { try { c.dispose(); } catch (_) {} });
    charts.clear();
  }

  function mount(el, option, dark) {
    if (!el || typeof echarts === 'undefined') return null;
    const id = el.id || el.getAttribute('data-chart') || Math.random().toString(36).slice(2);
    if (charts.has(id)) {
      try { charts.get(id).dispose(); } catch (_) {}
      charts.delete(id);
    }
    const chart = echarts.init(el, dark ? 'dark' : null, { renderer: 'canvas' });
    chart.setOption(option, false);
    charts.set(id, chart);
    return chart;
  }

  function chartBase() {
    return {
      backgroundColor: 'transparent',
      textStyle: { color: TEXT, fontFamily: 'Poppins, system-ui, sans-serif' },
      grid: { left: 48, right: 18, top: 28, bottom: 36, containLabel: false },
      tooltip: {
        trigger: 'axis',
        backgroundColor: 'rgba(255,255,255,.96)',
        borderColor: '#e2e8f0',
        borderWidth: 1,
        textStyle: { color: TEXT, fontSize: 12 },
        axisPointer: { type: 'cross', crossStyle: { color: MUTED }, lineStyle: { color: CYAN, type: 'dashed' } }
      }
    };
  }

  function axisStyle() {
    return {
      axisLine: { lineStyle: { color: 'rgba(148,163,184,.35)' } },
      axisTick: { show: false },
      axisLabel: { color: MUTED, fontSize: 11 },
      splitLine: { lineStyle: { color: GRID, type: 'dashed' } }
    };
  }

  function escapeHtml(s) {
    return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function renderTicker(el, snap) {
    if (!el) return;
    const items = [
      { k: t('trade.ticker_leads'), v: snap.leads, d: snap.leadsDelta, up: snap.leadsUp !== false },
      { k: t('trade.ticker_conv'), v: snap.conv, d: snap.convDelta, up: snap.convUp !== false },
      { k: t('trade.ticker_sales'), v: snap.sales, d: snap.salesDelta, up: snap.salesUp !== false },
      { k: t('trade.ticker_target'), v: snap.targetPct, d: snap.targetDelta, up: snap.targetUp !== false },
      { k: t('trade.ticker_avg'), v: snap.avg, d: snap.avgDelta, up: snap.avgUp !== false }
    ];
    const row = items.map((it) => {
      const cls = it.up ? 'up' : 'down';
      const arrow = it.up ? '▲' : '▼';
      return `<span class="trade-ticker__item"><span class="trade-ticker__k">${escapeHtml(it.k)}</span><span class="trade-ticker__v mono">${escapeHtml(it.v)}</span><span class="trade-ticker__d ${cls}">${arrow} ${escapeHtml(it.d)}</span></span>`;
    }).join('<span class="trade-ticker__sep">·</span>');
    // Duplicate track for seamless marquee; LIVE badge stays fixed.
    el.innerHTML = `<div class="trade-ticker__inner">
      <span class="trade-ticker__live"><i></i>${escapeHtml(t('trade.ticker_live'))}</span>
      <div class="trade-ticker__viewport" aria-hidden="false">
        <div class="trade-ticker__track">
          <div class="trade-ticker__group">${row}<span class="trade-ticker__sep">·</span></div>
          <div class="trade-ticker__group">${row}<span class="trade-ticker__sep">·</span></div>
        </div>
      </div>
    </div>`;
  }

  function dualRing(el, leadsPct, salesPct) {
    const base = chartBase();
    return mount(el, {
      ...base,
      series: [
        {
          type: 'gauge',
          startAngle: 220, endAngle: -40, center: ['50%', '55%'], radius: '92%',
          min: 0, max: 120, splitNumber: 6,
          axisLine: { lineStyle: { width: 12, color: [[Math.min(1, leadsPct / 120), CYAN], [1, '#e2e8f0']] } },
          pointer: { show: false }, axisTick: { show: false }, splitLine: { show: false }, axisLabel: { show: false },
          detail: { show: false },
          data: [{ value: leadsPct }]
        },
        {
          type: 'gauge',
          startAngle: 220, endAngle: -40, center: ['50%', '55%'], radius: '68%',
          min: 0, max: 120,
          axisLine: { lineStyle: { width: 12, color: [[Math.min(1, salesPct / 120), EMERALD], [1, '#e2e8f0']] } },
          pointer: { show: false }, axisTick: { show: false }, splitLine: { show: false }, axisLabel: { show: false },
          title: { offsetCenter: [0, '18%'], color: MUTED, fontSize: 11 },
          detail: {
            valueAnimation: true, offsetCenter: [0, '-8%'],
            formatter: (v) => `{a|${v.toFixed(1)}%}\n{b|${t('trade.dual_sales_label')}}`,
            rich: {
              a: { fontSize: 26, fontWeight: 700, color: TEXT, fontFamily: 'ui-monospace, Menlo, monospace', lineHeight: 32 },
              b: { fontSize: 11, color: MUTED, lineHeight: 16 }
            }
          },
          data: [{ value: salesPct, name: t('trade.dual_leads_label', {pct: leadsPct.toFixed(0)}) }]
        }
      ]
    }, false);
  }

  function equityCurve(el, actual, targetLine, labels) {
    const ax = axisStyle();
    const base = chartBase();
    return mount(el, {
      ...base,
      legend: { data: [t('trade.legend_actual'), t('trade.legend_target')], textStyle: { color: MUTED, fontSize: 11 }, top: 0, right: 8 },
      xAxis: { type: 'category', data: labels, boundaryGap: false, ...ax, splitLine: { show: false } },
      yAxis: {
        type: 'value', ...ax,
        axisLabel: { color: MUTED, fontSize: 10, formatter: (v) => (v >= 1000 ? (v / 1000) + 'k' : v) }
      },
      series: [
        {
          name: t('trade.legend_actual'), type: 'line', smooth: 0.25, symbol: 'circle', symbolSize: 6,
          data: actual,
          lineStyle: { width: 2.5, color: CYAN },
          itemStyle: { color: CYAN },
          areaStyle: {
            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
              { offset: 0, color: 'rgba(2,132,199,.28)' },
              { offset: 1, color: 'rgba(2,132,199,.02)' }
            ])
          },
          markLine: {
            silent: true,
            symbol: 'none',
            data: [{ yAxis: 1000000, name: 'RM1M' }],
            lineStyle: { color: AMBER, type: 'dashed', width: 1.5 },
            label: { color: AMBER, formatter: 'RM1M', fontSize: 10 }
          }
        },
        {
          name: t('trade.legend_target'), type: 'line', smooth: false, symbol: 'none',
          data: targetLine,
          lineStyle: { width: 1.5, color: ROSE, type: 'dashed' }
        }
      ]
    }, false);
  }

  function waterfall(el, steps) {
    // steps: [{name, value}] absolute stage sizes; compute assist/loss
    const names = steps.map((s) => s.name);
    const values = steps.map((s) => s.value);
    const assist = [];
    const positive = [];
    const negative = [];
    let prev = 0;
    values.forEach((v, i) => {
      if (i === 0) {
        assist.push(0);
        positive.push(v);
        negative.push('-');
        prev = v;
        return;
      }
      const drop = Math.max(0, prev - v);
      // Transparent base at remaining height + red drop from previous stage.
      assist.push(v);
      positive.push('-');
      negative.push(drop);
      prev = v;
    });
    const ax = axisStyle();
    const base = chartBase();
    return mount(el, {
      ...base,
      grid: { left: 48, right: 16, top: 24, bottom: 48 },
      xAxis: { type: 'category', data: names, ...ax, axisLabel: { color: MUTED, fontSize: 10, rotate: 28 }, splitLine: { show: false } },
      yAxis: { type: 'value', ...ax },
      series: [
        { type: 'bar', stack: 'wf', silent: true, itemStyle: { borderColor: 'transparent', color: 'transparent' }, data: assist, emphasis: { itemStyle: { color: 'transparent' } } },
        { type: 'bar', stack: 'wf', name: 'Keep', data: positive, itemStyle: { color: EMERALD, borderRadius: [4, 4, 0, 0] }, label: { show: true, position: 'top', color: TEXT, fontSize: 10 } },
        { type: 'bar', stack: 'wf', name: 'Drop', data: negative, itemStyle: { color: ROSE, borderRadius: [4, 4, 0, 0] }, label: { show: true, position: 'top', color: ROSE, fontSize: 10 } }
      ]
    }, false);
  }

  function scatter(el, points) {
    // points: {name, leads, conv, sales}[]
    const ax = axisStyle();
    const base = chartBase();
    const data = points.map((p) => ({
      value: [p.leads, p.conv, p.sales / 1000, p.name],
      name: p.name
    }));
    return mount(el, {
      ...base,
      grid: { left: 48, right: 20, top: 28, bottom: 40 },
      tooltip: {
        trigger: 'item',
        backgroundColor: 'rgba(255,255,255,.96)',
        borderColor: '#e2e8f0',
        textStyle: { color: TEXT },
        formatter: (p) => {
          const v = p.data.value;
          return `<strong>${v[3]}</strong><br/>${t('trade.ticker_leads')} ${v[0]} · ${t('trade.ticker_conv')} ${v[1]}%<br/>${t('trade.ticker_sales')} RM${(v[2] * 1000).toLocaleString('en-MY')}`;
        }
      },
      xAxis: { type: 'value', name: t('trade.ticker_leads'), nameTextStyle: { color: MUTED }, ...ax, splitLine: { lineStyle: { color: GRID, type: 'dashed' } } },
      yAxis: {
        type: 'value', name: t('trade.ticker_conv'), nameTextStyle: { color: MUTED }, ...ax,
        markLine: undefined
      },
      series: [{
        type: 'scatter',
        data,
        symbolSize: (val) => Math.max(14, Math.min(48, val[2] / 2.2)),
        itemStyle: {
          color: (p) => (p.value[1] >= 40 ? EMERALD : p.value[1] >= 35 ? AMBER : ROSE),
          shadowBlur: 10,
          shadowColor: 'rgba(34,211,238,.25)'
        },
        label: { show: true, formatter: (p) => p.data.name, position: 'top', color: MUTED, fontSize: 10 },
        markLine: {
          silent: true,
          symbol: 'none',
          data: [{ yAxis: 40 }],
          lineStyle: { color: AMBER, type: 'dashed' },
          label: { formatter: '40%', color: AMBER, fontSize: 10 }
        }
      }]
    }, false);
  }

  function heatmap(el, days) {
    // days: [{d:'09-01', v: number}, ...] 30 items
    const ax = axisStyle();
    const base = chartBase();
    const weeks = Math.ceil(days.length / 7);
    const data = days.map((d, i) => [i % 7, Math.floor(i / 7), Number(d.v) || 0, d.d]);
    const max = Math.max(...days.map((d) => d.v), 1);
    return mount(el, {
      ...base,
      grid: { left: 36, right: 16, top: 16, bottom: 28 },
      tooltip: {
        position: 'top',
        backgroundColor: 'rgba(255,255,255,.96)',
        borderColor: '#e2e8f0',
        textStyle: { color: TEXT },
        formatter: (p) => `${p.data[3]}: <b>${p.data[2]}</b> ${t('trade.ticker_leads').toLowerCase()}`
      },
      xAxis: { type: 'category', data: t('trade.weekdays').split('|'), ...ax, splitArea: { show: false }, splitLine: { show: false } },
      yAxis: { type: 'category', data: Array.from({ length: weeks }, (_, i) => 'W' + (i + 1)), ...ax, splitArea: { show: false }, splitLine: { show: false } },
      series: [{
        type: 'heatmap',
        data,
        itemStyle: {
          borderColor: '#ffffff',
          borderWidth: 2,
          color: (params) => {
            const intensity = Math.min(1, (Number(params.data[2]) || 0) / max);
            return intensity > 0 ? `rgba(2, 132, 199, ${0.18 + (intensity * 0.72)})` : '#eef2f7';
          }
        },
        label: { show: true, color: MUTED, fontSize: 9, formatter: (p) => p.data[2] > 0 ? p.data[2] : '' },
        emphasis: { itemStyle: { shadowBlur: 8, shadowColor: CYAN, borderColor: CYAN } }
      }]
    }, false);
  }


  function sparkline(el, values, up) {
    if (!el || typeof echarts === 'undefined') return null;
    const color = up ? EMERALD : ROSE;
    return mount(el, {
      backgroundColor: 'transparent',
      grid: { left: 0, right: 0, top: 2, bottom: 2 },
      xAxis: { type: 'category', show: false, data: values.map((_, i) => i) },
      yAxis: { type: 'value', show: false, min: Math.min(...values) * 0.95, max: Math.max(...values) * 1.05 },
      series: [{
        type: 'line', data: values, symbol: 'none', smooth: 0.35,
        lineStyle: { width: 1.6, color },
        areaStyle: {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: up ? 'rgba(52,211,153,.35)' : 'rgba(251,113,133,.35)' },
            { offset: 1, color: 'rgba(0,0,0,0)' }
          ])
        }
      }]
    }, false);
  }

  function demoDays(n) {
    const out = [];
    const now = new Date(2026, 8, 8);
    for (let i = n - 1; i >= 0; i--) {
      const d = new Date(now);
      d.setDate(d.getDate() - i);
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const dd = String(d.getDate()).padStart(2, '0');
      const base = 22 + ((i * 7) % 17) + (i % 5);
      out.push({ d: mm + '-' + dd, v: base + (i % 3 === 0 ? 12 : 0) });
    }
    return out;
  }

  function renderOverview(payload) {
    disposeAll();
    const p = payload || {};
    renderTicker(document.getElementById('tradeTicker'), p.ticker || {
      leads: '1,000', leadsDelta: '12.5%',
      conv: '41.2%', convDelta: '3.2pp',
      sales: 'RM1.037M', salesDelta: '8.9%',
      targetPct: '103.6%', targetDelta: '3.6pp',
      avg: 'RM2,516', avgDelta: '4.1%'
    });

    dualRing(document.getElementById('tradeDualRing'), p.leadsPct ?? 100, p.salesPct ?? 103.6);

    const equityActual = p.equityActual || [42000, 98000, 165000, 248000, 340000, 455000, 572000, 690000, 810000, 925000, 1036500];
    const equityTarget = p.equityTarget || equityActual.map((_, i, arr) => Math.round((1000000 / (arr.length - 1)) * i));
    const equityLabels = p.equityLabels || ['D1', 'D3', 'D5', 'D7', 'D9', 'D11', 'D13', 'D15', 'D17', 'D19', 'D21'];
    equityCurve(document.getElementById('tradeEquity'), equityActual, equityTarget, equityLabels);

    waterfall(document.getElementById('tradeWaterfall'), p.waterfall || [
      { name: 'Unique', value: 1000 },
      { name: 'Claimed', value: 870 },
      { name: 'F/U', value: 720 },
      { name: 'Book', value: 510 },
      { name: 'Pay', value: 430 },
      { name: 'Verified', value: 412 },
      { name: 'Treat', value: 385 }
    ]);

    scatter(document.getElementById('tradeScatter'), p.scatter || (p.beauticians || []));

    heatmap(document.getElementById('tradeHeatmap'), p.heatmap || demoDays(28));

    (p.sparks || []).forEach((s) => {
      const el = document.getElementById(s.id);
      if (el) sparkline(el, s.values, s.up !== false);
    });
  }

  function resize() {
    charts.forEach((c) => { try { c.resize(); } catch (_) {} });
  }

  global.IMMA_TRADE = {
    render: renderOverview,
    dispose: disposeAll,
    resize,
    sparkline,
    renderTicker
  };

  global.addEventListener('resize', () => resize());
})(window);
