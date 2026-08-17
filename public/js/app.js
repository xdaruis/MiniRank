(function () {
  const btn = document.getElementById('refresh-positions');
  if (!btn) return;

  btn.addEventListener('click', async () => {
    btn.disabled = true;
    try {
      const res = await fetch('refresh.php', { method: 'POST' });
      const rows = await res.json();
      rows.forEach(({ keyword_id, position, trend }) => {
        const tr = document.querySelector(`tr[data-keyword-id="${keyword_id}"]`);
        if (!tr) return;
        const pos = tr.querySelector('.position');
        pos.textContent = position;
        pos.classList.toggle('pos-good', position <= 10);
        // TODO: Double source of truth — trend -> arrow glyph + trend-<label> class is
        // rendered by both list.php (backend) and here (frontend). Duplicated logic can
        // drift and becomes a maintenance hazard. Consolidate to one source of truth.
        const t = tr.querySelector('.trend');
        t.className = 'trend trend-' + trend;
        t.textContent = trend === 'improved' ? '▲' : (trend === 'declined' ? '▼' : '=');
      });
    } catch (err) {
      console.error(err);
    } finally {
      btn.disabled = false;
    }
  });
})();

(function () {
  const chart = document.querySelector('.chart');
  const tip = document.getElementById('chart-tooltip');
  if (!chart || !tip) return;

  const hits = Array.from(chart.querySelectorAll('circle.chart-hit'));
  if (hits.length === 0) return;

  const PAD = 10;

  function show(listItem, rect) {
    tip.textContent = `${listItem.dataset.date} · Position ${listItem.dataset.position}`;
    tip.classList.add('is-visible');

    const wrap = chart.getBoundingClientRect();
    const tipW = tip.offsetWidth;
    const topEdge = rect.top - wrap.top;
    const above = topEdge > wrap.height * 0.35;

    tip.classList.toggle('flip', !above);
    tip.style.left = tipW > wrap.width
      ? `${PAD}px`
      : `${Math.min(Math.max(rect.left - wrap.left + rect.width / 2 - tipW / 2, PAD), wrap.width - tipW - PAD)}px`;
    tip.style.top = above ? `${topEdge}px` : `${topEdge + rect.height}px`;
  }

  chart.addEventListener('click', (e) => {
    const direct = e.target.closest('circle');
    if (!direct) return;

    const px = e.clientX;
    let best = direct.classList.contains('chart-hit') ? direct : hits[0];
    let bestDist = Infinity;
    hits.forEach((hit) => {
      const d = Math.abs(hit.getBoundingClientRect().left + hit.getBoundingClientRect().width / 2 - px);
      if (d < bestDist) { bestDist = d; best = hit; }
    });

    show(best, best.getBoundingClientRect());
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.chart')) tip.classList.remove('is-visible');
  });
})();
