const yearFilter = document.getElementById('yearFilter');
let currentYear = '';

async function loadRankings() {
  const q = new URLSearchParams();
  if (currentYear) q.set('year', currentYear);

  const res = await fetch('api/get_rankings.php?' + q.toString());
  if (redirectIfUnauthorized(res)) return;
  const data = await res.json();
  if (!data.success) return;

  populateYearFilter(data.years);
  renderProductTable('topProductsBody', data.top_products, 6);
  renderProductTable('bottomProductsBody', data.bottom_products, 6);
  renderCustomerTable('topCustomersBody', data.top_customers);
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

function renderProductTable(bodyId, rows, colSpan) {
  const body = document.getElementById(bodyId);
  if (!rows.length) {
    body.innerHTML = `<tr><td colspan="${colSpan}" class="empty-row">No data yet — go to <a href="import.php">Import Data</a> to upload a CSV.</td></tr>`;
    return;
  }
  body.innerHTML = rows.map(r => `
    <tr>
      <td><span class="rank-badge">#${r.rnk}</span></td>
      <td>${escapeHtml(r.product)}</td>
      <td>${formatCurrency(r.revenue)}</td>
      <td>${formatNumber(r.units_sold)}</td>
      <td>${formatNumber(r.orders)}</td>
      <td>${r.avg_selling_price != null ? formatCurrency(r.avg_selling_price) : '—'}</td>
    </tr>
  `).join('');
}

function renderCustomerTable(bodyId, rows) {
  const body = document.getElementById(bodyId);
  if (!rows.length) {
    body.innerHTML = `<tr><td colspan="5" class="empty-row">No data yet — go to <a href="import.php">Import Data</a> to upload a CSV.</td></tr>`;
    return;
  }
  body.innerHTML = rows.map(r => `
    <tr>
      <td><span class="rank-badge">#${r.rnk}</span></td>
      <td>${escapeHtml(r.customer_name)}</td>
      <td>${formatCurrency(r.revenue)}</td>
      <td>${formatNumber(r.orders)}</td>
      <td>${formatCurrency(r.avg_order_value)}</td>
    </tr>
  `).join('');
}

yearFilter.addEventListener('change', () => {
  currentYear = yearFilter.value;
  loadRankings();
});

loadRankings();
