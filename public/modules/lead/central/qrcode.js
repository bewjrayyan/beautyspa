/* Fleetcart Central — self-contained QR Code generator.
 * ISO/IEC 18004, byte mode, ECC level M, versions 1-10.
 * No external dependencies. Exposes QRCentral.render(canvas, text, opts).
 * Used by the Live Check-In drawer to show a scannable booking code.
 */
(function (root, factory) {
  var api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (root) root.QRCentral = api;
})(typeof window !== 'undefined' ? window : (typeof global !== 'undefined' ? global : this), function () {
  'use strict';

  /* ---------- GF(256) arithmetic ---------- */
  var EXP = new Uint8Array(512), LOG = new Uint8Array(256);
  (function () {
    var x = 1, i;
    for (i = 0; i < 255; i++) { EXP[i] = x; LOG[x] = i; x <<= 1; if (x & 0x100) x ^= 0x11D; }
    for (i = 255; i < 512; i++) EXP[i] = EXP[i - 255];
  })();

  function gfMul(a, b) {
    if (a === 0 || b === 0) return 0;
    return EXP[LOG[a] + LOG[b]];
  }

  /* Generator polynomial for `degree` error-correction codewords.
   * g(x) = (x - a^0)(x - a^1)...(x - a^(degree-1)); coefficient array highest-first. */
  function rsGenerator(degree) {
    var g = [1], i, j;
    for (i = 0; i < degree; i++) {
      var a = EXP[i];
      var next = new Array(g.length + 1).fill(0);
      for (j = 0; j < g.length; j++) {
        next[j] ^= g[j];                 // multiply by x
        next[j + 1] ^= gfMul(g[j], a);   // multiply by a^i
      }
      g = next;
    }
    return g;
  }

  function rsRemainder(data, gen) {
    var out = data.slice(), degree = gen.length - 1, i, j;
    for (i = 0; i < data.length - degree; i++) {
      var coef = out[i];
      if (coef !== 0) {
        for (j = 0; j < gen.length; j++) out[i + j] ^= gfMul(gen[j], coef);
      }
    }
    return out.slice(data.length - degree);
  }

  /* ---------- QR tables: ECC level M (00), versions 1-10 ----------
   * Each row: [version, dataCodewords, [[dataPerBlock, ecPerBlock, blockCount]]] */
  var VERSIONS = [
    [1,  16,  [[16, 10, 1]]],
    [2,  28,  [[28, 16, 1]]],
    [3,  44,  [[44, 26, 1]]],
    [4,  64,  [[32, 18, 2]]],
    [5,  86,  [[43, 24, 2]]],
    [6, 108,  [[27, 16, 4]]],
    [7, 124,  [[31, 18, 4]]],
    [8, 154,  [[38, 22, 2], [39, 22, 2]]],
    [9, 182,  [[36, 22, 3], [37, 22, 2]]],
    [10, 216, [[43, 26, 4], [44, 26, 1]]]
  ];
  var ALIGN = {
    1: [], 2: [6, 18], 3: [6, 22], 4: [6, 26], 5: [6, 30], 6: [6, 34],
    7: [6, 22, 38], 8: [6, 24, 42], 9: [6, 26, 46], 10: [6, 28, 50]
  };

  function bchVersion(ver) {
    var rem = ver, i;
    for (i = 0; i < 12; i++) rem = (rem << 1) ^ ((rem >>> 11) * 0x1F25);
    return (ver << 12) | (rem & 0xFFF);
  }

  function bchFormat(eccBits, mask) {
    var data = (eccBits << 3) | mask, rem = data, i;
    for (i = 0; i < 10; i++) rem = (rem << 1) ^ ((rem >>> 9) * 0x537);
    return ((data << 10) | (rem & 0x3FF)) ^ 0x5412;
  }

  function utf8Bytes(text) {
    var out = [], i, cp;
    for (i = 0; i < text.length; i++) {
      cp = text.codePointAt(i);
      if (cp > 0xFFFF) i++;
      if (cp < 0x80) out.push(cp);
      else if (cp < 0x800) out.push(0xC0 | (cp >> 6), 0x80 | (cp & 0x3F));
      else if (cp < 0x10000) out.push(0xE0 | (cp >> 12), 0x80 | ((cp >> 6) & 0x3F), 0x80 | (cp & 0x3F));
      else out.push(0xF0 | (cp >> 18), 0x80 | ((cp >> 12) & 0x3F), 0x80 | ((cp >> 6) & 0x3F), 0x80 | (cp & 0x3F));
    }
    return out;
  }

  /* Build the interleaved data + EC codeword stream for `text`. */
  function makeCodewords(text) {
    var bytes = utf8Bytes(text);
    var vi = -1, i, j;
    for (i = 0; i < VERSIONS.length; i++) {
      var bitsNeeded = 4 + (VERSIONS[i][0] <= 9 ? 8 : 16) + bytes.length * 8;
      if (bitsNeeded <= VERSIONS[i][1] * 8) { vi = i; break; }
    }
    if (vi < 0) throw new Error('Check-in QR payload too long');
    var ver = VERSIONS[vi];
    var capBits = ver[1] * 8;

    /* bit stream (byte mode, MSB-first) */
    var stream = [];
    function pushVal(val, nbits) { for (var k = nbits - 1; k >= 0; k--) stream.push((val >>> k) & 1); }
    pushVal(4, 4);
    pushVal(bytes.length, ver[0] <= 9 ? 8 : 16);
    for (i = 0; i < bytes.length; i++) pushVal(bytes[i], 8);
    var term = Math.min(4, capBits - stream.length);
    for (i = 0; i < term; i++) stream.push(0);
    while (stream.length % 8 !== 0) stream.push(0);

    var dataCodewords = [];
    for (i = 0; i < stream.length; i += 8) {
      var b = 0;
      for (j = 0; j < 8; j++) b = (b << 1) | stream[i + j];
      dataCodewords.push(b);
    }
    var pads = [0xEC, 0x11], p = 0;
    while (dataCodewords.length < ver[1]) dataCodewords.push(pads[p++ & 1]);

    /* split into RS blocks */
    var cfg = [], groups = ver[2], g, n;
    for (g = 0; g < groups.length; g++)
      for (n = 0; n < groups[g][2]; n++) cfg.push({ d: groups[g][0], e: groups[g][1] });

    var blocks = [], offset = 0;
    for (i = 0; i < cfg.length; i++) {
      blocks.push(dataCodewords.slice(offset, offset + cfg[i].d));
      offset += cfg[i].d;
    }
    for (i = 0; i < blocks.length; i++) {
      var gen = rsGenerator(cfg[i].e);
      var padded = blocks[i].concat(new Array(cfg[i].e).fill(0));
      blocks[i].ec = rsRemainder(padded, gen);
    }

    /* interleave data codewords then EC codewords */
    var maxD = 0, maxE = 0;
    for (i = 0; i < blocks.length; i++) {
      if (blocks[i].length > maxD) maxD = blocks[i].length;
      if (blocks[i].ec.length > maxE) maxE = blocks[i].ec.length;
    }
    var merged = [];
    for (i = 0; i < maxD; i++)
      for (j = 0; j < blocks.length; j++)
        if (i < blocks[j].length) merged.push(blocks[j][i]);
    for (i = 0; i < maxE; i++)
      for (j = 0; j < blocks.length; j++)
        if (i < blocks[j].ec.length) merged.push(blocks[j].ec[i]);

    return { order: ver[0], codewords: merged };
  }

  /* Mask condition per mask index (ISO/IEC 18004 8.8.1). */
  function maskBit(mask, r, c) {
    switch (mask) {
      case 0: return (r + c) % 2 === 0;
      case 1: return r % 2 === 0;
      case 2: return c % 3 === 0;
      case 3: return (r + c) % 3 === 0;
      case 4: return (Math.floor(r / 2) + Math.floor(c / 3)) % 2 === 0;
      case 5: return ((r * c) % 2 + (r * c) % 3) === 0;
      case 6: return (((r * c) % 2) + ((r * c) % 3)) % 2 === 0;
      default: return (((r * c) % 3) + ((r + c) % 2)) % 2 === 0;
    }
  }

  /* Format info placement: two 15-bit copies around the finder patterns.
   * ISO/IEC 18004 section 7.9 — correct bit→module mapping for ECC level M. */
  function writeFormat(m, size, mask) {
    var fmt = bchFormat(0, mask), i, bit;   /* 0 = ECC_M */
    for (i = 0; i < 15; i++) {
      bit = (fmt >>> i) & 1;

      /* Vertical copy: top-left then bottom-left. */
      if (i < 6) m[i * size + 8] = bit;
      else if (i < 8) m[(i + 1) * size + 8] = bit;
      else m[(size - 15 + i) * size + 8] = bit;

      /* Horizontal copy: top-right then top-left. */
      if (i < 8) m[8 * size + (size - i - 1)] = bit;
      else if (i === 8) m[8 * size + 7] = bit;
      else m[8 * size + (15 - i - 1)] = bit;
    }

    m[(size - 8) * size + 8] = 1;          /* fixed dark module */
  }
/* Mask penalty score (ISO/IEC 18004 8.8.2). */
  function penalty(m, size) {
    var score = 0, r, c, run, v;

    for (r = 0; r < size; r++) {                       /* rule 1 — rows */
      run = 1;
      for (c = 1; c < size; c++) {
        if (m[r * size + c] === m[r * size + c - 1]) { run++; continue; }
        if (run >= 5) score += 3 + (run - 5);
        run = 1;
      }
      if (run >= 5) score += 3 + (run - 5);
    }
    for (c = 0; c < size; c++) {                       /* rule 1 — columns */
      run = 1;
      for (r = 1; r < size; r++) {
        if (m[r * size + c] === m[(r - 1) * size + c]) { run++; continue; }
        if (run >= 5) score += 3 + (run - 5);
        run = 1;
      }
      if (run >= 5) score += 3 + (run - 5);
    }
    for (r = 0; r < size - 1; r++)                     /* rule 2 — 2x2 blocks */
      for (c = 0; c < size - 1; c++) {
        v = m[r * size + c];
        if (m[r * size + c + 1] === v && m[(r + 1) * size + c] === v && m[(r + 1) * size + c + 1] === v) score += 3;
      }

    for (r = 0; r < size; r++) {                       /* rule 3 — 1011101 finder-like */
      for (c = 0; c < size; c++) {
        if (c + 6 < size &&
            m[r * size + c] === 1 && m[r * size + c + 1] === 0 && m[r * size + c + 2] === 1 &&
            m[r * size + c + 3] === 1 && m[r * size + c + 4] === 1 && m[r * size + c + 5] === 0 &&
            m[r * size + c + 6] === 1 &&
            ((c >= 4 && m[r * size + c - 1] === 0 && m[r * size + c - 2] === 0 && m[r * size + c - 3] === 0 && m[r * size + c - 4] === 0) ||
             (c + 10 < size && m[r * size + c + 7] === 0 && m[r * size + c + 8] === 0 && m[r * size + c + 9] === 0 && m[r * size + c + 10] === 0))) score += 40;
        if (r + 6 < size &&
            m[r * size + c] === 1 && m[(r + 1) * size + c] === 0 && m[(r + 2) * size + c] === 1 &&
            m[(r + 3) * size + c] === 1 && m[(r + 4) * size + c] === 1 && m[(r + 5) * size + c] === 0 &&
            m[(r + 6) * size + c] === 1 &&
            ((r >= 4 && m[(r - 1) * size + c] === 0 && m[(r - 2) * size + c] === 0 && m[(r - 3) * size + c] === 0 && m[(r - 4) * size + c] === 0) ||
             (r + 10 < size && m[(r + 7) * size + c] === 0 && m[(r + 8) * size + c] === 0 && m[(r + 9) * size + c] === 0 && m[(r + 10) * size + c] === 0))) score += 40;
      }
    }

    var total = size * size, dark = 0;                 /* rule 4 — dark proportion */
    for (r = 0; r < total; r++) if (m[r]) dark++;
    score += Math.floor(Math.abs(dark * 100 / total - 50) / 5) * 10;
    return score;
  }
/* Build the finished boolean-symbol matrix (masked) for `text`. */
  function makeMatrix(text) {
    var cw = makeCodewords(text);
    var size = cw.order * 4 + 17;
    var modules = new Uint8Array(size * size);
    var isFn = new Uint8Array(size * size);
    var dataPos = [];
    var i, j, r, c;

    function setFn(r, c, val) {
      if (r < 0 || c < 0 || r >= size || c >= size) return;
      var idx = r * size + c;
      modules[idx] = val ? 1 : 0;
      isFn[idx] = 1;
    }

    function drawFinder(r0, c0) {
      for (var dr = 0; dr < 7; dr++)
        for (var dc = 0; dc < 7; dc++) {
          var on = dr === 0 || dr === 6 || dc === 0 || dc === 6 || (dr >= 2 && dr <= 4 && dc >= 2 && dc <= 4);
          setFn(r0 + dr, c0 + dc, on);
        }
      for (var i = -1; i <= 7; i++) {
        setFn(r0 + i, c0 - 1, 0); setFn(r0 + i, c0 + 7, 0);
        setFn(r0 - 1, c0 + i, 0); setFn(r0 + 7, c0 + i, 0);
      }
    }

    drawFinder(0, 0);
    drawFinder(0, size - 7);
    drawFinder(size - 7, 0);

    for (i = 8; i < size - 8; i++) {                   /* timing patterns */
      setFn(6, i, i % 2 === 0);
      setFn(i, 6, i % 2 === 0);
    }

    var centers = ALIGN[cw.order] || [];
    for (var a = 0; a < centers.length; a++)           /* alignment patterns */
      for (var b = 0; b < centers.length; b++) {
        r = centers[a]; c = centers[b];
        if ((r <= 8 && c <= 8) || (r <= 8 && c >= size - 9) || (r >= size - 9 && c <= 8)) continue;
        for (var dr = -2; dr <= 2; dr++)
          for (var dc = -2; dc <= 2; dc++) {
            var on = Math.abs(dr) === 2 || Math.abs(dc) === 2 || (dr === 0 && dc === 0);
            setFn(r + dr, c + dc, on);
          }
      }

    for (i = 0; i < 6; i++) setFn(i, 8, 0);            /* reserve format areas */
    setFn(7, 8, 0); setFn(8, 8, 0);
    for (i = 0; i < 7; i++) setFn(size - 1 - i, 8, 0);
    for (i = 0; i < 8; i++) setFn(8, size - 1 - i, 0);
    setFn(8, 7, 0);
    for (i = 0; i < 6; i++) setFn(8, 5 - i, 0);
    setFn(size - 8, 8, 1);                             /* dark module */

    if (cw.order >= 7) {                               /* version info */
      var vbits = bchVersion(cw.order);
      for (i = 0; i < 18; i++) {
        var vb = (vbits >>> i) & 1;
        var aa = size - 11 + (i % 3);
        var bb = Math.floor(i / 3);
        setFn(aa, bb, vb);
        setFn(bb, aa, vb);
      }
    }
for (var right = size - 1; right >= 1; right -= 2) { /* collect data positions */
      if (right === 6) right = 5;
      for (var vert = 0; vert < size; vert++) {
        for (var j = 0; j < 2; j++) {
          var x = right - j;
          var upward = ((right + 1) & 2) === 0;
          var y = upward ? size - 1 - vert : vert;
          if (!isFn[y * size + x]) dataPos.push([y, x]);
        }
      }
    }

    if (dataPos.length < cw.codewords.length * 8)
      throw new Error('QR data placement shortage (v' + cw.order + ')');

    for (i = 0; i < cw.codewords.length; i++) {        /* place bits, MSB first */
      for (j = 0; j < 8; j++) {
        var bit = (cw.codewords[i] >>> (7 - j)) & 1;
        var pos = dataPos[i * 8 + j];
        modules[pos[0] * size + pos[1]] = bit;
      }
    }

    var raw = modules.slice(), bestMask = 0, bestScore = Infinity, bestMatrix = null;
    for (var mask = 0; mask < 8; mask++) {             /* choose best mask */
      var candidate = raw.slice();
      for (i = 0; i < dataPos.length; i++) {
        var pr = dataPos[i][0], pc = dataPos[i][1];
        if (maskBit(mask, pr, pc)) candidate[pr * size + pc] ^= 1;
      }
      writeFormat(candidate, size, mask);
      var sc = penalty(candidate, size);
      if (sc < bestScore) { bestScore = sc; bestMask = mask; bestMatrix = candidate; }
    }

    return { version: cw.order, size: size, mask: bestMask, modules: bestMatrix };
  }

  /* Render a matrix onto a canvas (crisp rects, dark-on-white). */
  function drawToCanvas(canvas, matrix, opts) {
    opts = opts || {};
    var size = matrix.size;
    var quiet = opts.quiet === undefined ? 4 : opts.quiet;
    var module = opts.module || Math.max(2, Math.floor((opts.size || 200) / (size + quiet * 2)));
    var dim = (size + quiet * 2) * module;
    canvas.width = dim;
    canvas.height = dim;
    var ctx = canvas.getContext('2d');
    ctx.fillStyle = opts.background || '#ffffff';
    ctx.fillRect(0, 0, dim, dim);
    ctx.fillStyle = opts.color || '#14375f';
    for (var r = 0; r < size; r++)
      for (var c = 0; c < size; c++)
        if (matrix.modules[r * size + c])
          ctx.fillRect((c + quiet) * module, (r + quiet) * module, module, module);
    return canvas;
  }

  return {
    generate: makeMatrix,
    render: function (canvas, text, opts) {
      return drawToCanvas(canvas, makeMatrix(text), opts);
    }
  };
});