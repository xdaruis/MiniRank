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
        tr.querySelector('.position').textContent = position;
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
