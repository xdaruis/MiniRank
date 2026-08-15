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
        tr.querySelector('.trend').textContent = trend;
      });
    } catch (err) {
      console.error(err);
    } finally {
      btn.disabled = false;
    }
  });
})();
