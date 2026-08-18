(() => {
    const canvas = document.getElementById('signature-pad');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let drawing = false;

    // Set canvas to proper resolution
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
    canvas.style.width = rect.width + 'px';
    canvas.style.height = rect.height + 'px';

    // Draw grid pattern for preview
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 1;
    const step = 20;
    for (let x = 0; x <= rect.width; x += step) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, rect.height);
        ctx.stroke();
    }
    for (let y = 0; y <= rect.height; y += step) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(rect.width, y);
        ctx.stroke();
    }

    // Add preview text
    ctx.fillStyle = '#cbd5e1';
    ctx.font = '14px Inter, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('Signature preview', rect.width / 2, rect.height / 2);
    ctx.font = '12px Inter, sans-serif';
    ctx.fillText('(Not functional in preview mode)', rect.width / 2, rect.height / 2 + 20);

    function getPoint(e) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: (e.touches ? e.touches[0].clientX : e.clientX) - rect.left,
            y: (e.touches ? e.touches[0].clientY : e.clientY) - rect.top
        };
    }

    function startDrawing(e) {
        drawing = true;
        const pt = getPoint(e);
        ctx.beginPath();
        ctx.moveTo(pt.x, pt.y);
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        e.preventDefault();
    }

    function draw(e) {
        if (!drawing) return;
        const pt = getPoint(e);
        ctx.lineTo(pt.x, pt.y);
        ctx.stroke();
        e.preventDefault();
    }

    function stopDrawing() {
        drawing = false;
        ctx.beginPath();
    }

    // Allow drawing in preview for demonstration
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseleave', stopDrawing);
    canvas.addEventListener('touchstart', startDrawing, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stopDrawing);

    // Clear button (if added later)
    const clearBtn = document.getElementById('clear-signature');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            // Redraw grid
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = 1;
            for (let x = 0; x <= rect.width; x += step) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, rect.height);
                ctx.stroke();
            }
            for (let y = 0; y <= rect.height; y += step) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(rect.width, y);
                ctx.stroke();
            }
        });
    }
})();