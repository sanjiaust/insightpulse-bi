async function loadYoy() {
  const res = await fetch('api/get_yoy.php');
  if (redirectIfUnauthorized(res)) return;
  const data = await res.json();
  if (!data.success) return;

  renderYoyCards(data.yearly);
  renderMonthlyMatrix(data.monthly_matrix, data.years);
}

function renderYoyCards(yearly) {
  const container = document.getElementById('yoyCards');
  if (!yearly.length) {
    container.innerHTML = '<p class="empty-state">No data yet — go to <a href="import.php">Import Data</a> to upload a CSV.</p>';
    return;
  }

  container.innerHTML = yearly.map(y => {
    const revGrowth = y.growth_revenue_pct;
    const ordGrowth = y.growth_orders_pct;
    const revGrowthHtml = revGrowth === null
      ? '<span class="growth-neutral">baseline year</span>'
      : `<span class="${revGrowth >= 0 ? 'growth-up' : 'growth-down'}">${formatPercent(revGrowth, true)} vs ${y.year - 1}</span>`;
    const ordGrowthHtml = ordGrowth === null
      ? '<span class="growth-neutral">baseline year</span>'
      : `<span class="${ordGrowth >= 0 ? 'growth-up' : 'growth-down'}">${formatPercent(ordGrowth, true)} vs ${y.year - 1}</span>`;

    return `
      <div class="yoy-card">
        <div class="yoy-card-year">${y.year}</div>
        <div class="yoy-card-row">
          <span class="yoy-card-label">Revenue</span>
          <span class="yoy-card-value">${formatCurrency(y.revenue)}</span>
          ${revGrowthHtml}
        </div>
        <div class="yoy-card-row">
          <span class="yoy-card-label">Orders</span>
          <span class="yoy-card-value">${formatNumber(y.orders)}</span>
          ${ordGrowthHtml}
        </div>
        <div class="yoy-card-row">
          <span class="yoy-card-label">Avg. Order Value</span>
          <span class="yoy-card-value">${formatCurrency(y.avg_order_value)}</span>
        </div>
        <div class="yoy-card-row">
          <span class="yoy-card-label">Units Sold</span>
          <span class="yoy-card-value">${formatNumber(y.units)}</span>
        </div>
      </div>
    `;
  }).join('');
}

function renderMonthlyMatrix(matrix, years) {
  const table = document.getElementById('monthlyMatrixTable');
  if (!years.length) {
    table.querySelector('tbody').innerHTML = '<tr><td class="empty-row">No data yet.</td></tr>';
    return;
  }

  const thead = table.querySelector('thead tr');
  thead.innerHTML = '<th>Month</th>' + years.map(y => `<th>${y}</th>`).join('');

  const tbody = table.querySelector('tbody');
  tbody.innerHTML = matrix.map(row => {
    const cells = years.map(y => {
      const v = row.values[y];
      return `<td>${v != null ? formatCurrency(v) : '—'}</td>`;
    }).join('');
    return `<tr><td>${row.month}</td>${cells}</tr>`;
  }).join('');
}

loadYoy();
