const yearFilter = document.getElementById('yearFilter');
let currentYear = '';

async function loadPerformance() {
  const q = new URLSearchParams();
  if (currentYear) q.set('year', currentYear);

  const res = await fetch('api/get_performance.php?' + q.toString());
  if (redirectIfUnauthorized(res)) return;
  const data = await res.json();
  if (!data.success) return;

  populateYearFilter(data.years);
  renderCategoryTable(data.by_category);
  renderRegionTable(data.by_region);
}

function populateYearFilter(years) {
  const existing = new Set(Array.from(yearFilter.options).map(o => o.value).filter(Boolean));
  const incoming = new Set(years);
  if (existing.size === incoming.size && [...existing].every(v => incoming.has(v))) return;

  const first = yearFilter.options[0];
  yearFilter.innerHTML = '';
  yearFilter.appendChild(first);
  years.forEach(y => {
    const o = document.createElement('option');
    o.value = y;
    o.textContent = y;
    yearFilter.appendChild(o);
  });
  yearFilter.value = currentYear;
}

function marketShareBar(pct) {
  return `<div class="share-cell"><div class="share-bar"><div class="share-fill" style="width:${Math.min(pct, 100)}%"></div></div><span>${pct}%</span></div>`;
}

function renderCategoryTable(rows) {
  const body = document.getElementById('categoryBody');
  if (!rows.length) {
    body.innerHTML = `<tr><td colspan="6" class="empty-row">No data yet — go to <a href="import.php">Import Data</a> to upload a CSV.</td></tr>`;
    return;
  }
  body.innerHTML = rows.map(r => `
    <tr>
      <td><span class="pill">${escapeHtml(r.category)}</span></td>
      <td>${formatCurrency(r.revenue)}</td>
      <td>${formatNumber(r.units)}</td>
      <td>${formatNumber(r.orders)}</td>
      <td>${formatCurrency(r.avg_order_value)}</td>
      <td>${marketShareBar(r.market_share)}</td>
    </tr>
  `).join('');
}

function renderRegionTable(rows) {
  const body = document.getElementById('regionBody');
  if (!rows.length) {
    body.innerHTML = `<tr><td colspan="7" class="empty-row">No data yet — go to <a href="import.php">Import Data</a> to upload a CSV.</td></tr>`;
    return;
  }
  body.innerHTML = rows.map(r => `
    <tr>
      <td>${escapeHtml(r.region)}</td>
      <td>${formatCurrency(r.revenue)}</td>
      <td>${formatNumber(r.units)}</td>
      <td>${formatNumber(r.orders)}</td>
      <td>${formatCurrency(r.avg_order_value)}</td>
      <td>${formatNumber(r.avg_quantity)}</td>
      <td>${marketShareBar(r.market_share)}</td>
    </tr>
  `).join('');
}

yearFilter.addEventListener('change', () => {
  currentYear = yearFilter.value;
  loadPerformance();
});

loadPerformance();
