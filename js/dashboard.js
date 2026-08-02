// ---------- State ----------
const state = {
  search: '',
  category: '',
  region: '',
  year: '',
  sort: 'desc',
  page: 1,
};

let trendChart, categoryChart, regionChart;
let debounceTimer;

// ---------- Elements ----------
const els = {
  searchInput: document.getElementById('searchInput'),
  yearFilter: document.getElementById('yearFilter'),
  categoryFilter: document.getElementById('categoryFilter'),
  regionFilter: document.getElementById('regionFilter'),
  clearFilters: document.getElementById('clearFilters'),
  exportBtn: document.getElementById('exportBtn'),
  tableBody: document.getElementById('tableBody'),
  pagination: document.getElementById('pagination'),
  sortAmount: document.getElementById('sortAmount'),
  sortArrow: document.getElementById('sortArrow'),
};

function buildQuery() {
  const p = new URLSearchParams();
  if (state.search) p.set('search', state.search);
  if (state.category) p.set('category', state.category);
  if (state.region) p.set('region', state.region);
  if (state.year) p.set('year', state.year);
  return p;
}

// ---------- Stats + charts ----------
async function loadStats() {
  const q = buildQuery();
  const res = await fetch('api/get_stats.php?' + q.toString());
  if (redirectIfUnauthorized(res)) return;
  const data = await res.json();
  if (!data.success) return;

  document.getElementById('kpiTotalSales').textContent = formatNumber(data.kpi.total_sales);
  document.getElementById('kpiTotalOrders').textContent = formatNumber(data.kpi.total_orders);
  document.getElementById('kpiTotalRevenue').textContent = formatCurrency(data.kpi.total_revenue);
  document.getElementById('kpiAvgOrder').textContent = formatCurrency(data.kpi.avg_order_value);
  document.getElementById('kpiBestCategory').textContent = data.kpi.best_category || '–';
  document.getElementById('kpiBestRegion').textContent = data.kpi.best_region || '–';
  document.getElementById('kpiHighMonth').textContent = monthLabel(data.kpi.highest_month);
  document.getElementById('kpiHighMonthSub').textContent = data.kpi.highest_month_amount != null ? formatCurrency(data.kpi.highest_month_amount) : '–';
  document.getElementById('kpiLowMonth').textContent = monthLabel(data.kpi.lowest_month);
  document.getElementById('kpiLowMonthSub').textContent = data.kpi.lowest_month_amount != null ? formatCurrency(data.kpi.lowest_month_amount) : '–';

  populateFilterOptions(data.filters.years, els.yearFilter, state.year);
  populateFilterOptions(data.filters.categories, els.categoryFilter, state.category);
  populateFilterOptions(data.filters.regions, els.regionFilter, state.region);

  renderTrendChart(data.monthly_trend);
  renderCategoryChart(data.by_category);
  renderRegionChart(data.by_region);
}

function populateFilterOptions(options, selectEl, current) {
  const existing = new Set(Array.from(selectEl.options).map(o => o.value).filter(Boolean));
  const incoming = new Set(options);
  if (existing.size === incoming.size && [...existing].every(v => incoming.has(v))) return;

  const firstOption = selectEl.options[0];
  selectEl.innerHTML = '';
  selectEl.appendChild(firstOption);
  options.forEach(opt => {
    const o = document.createElement('option');
    o.value = opt;
    o.textContent = opt;
    selectEl.appendChild(o);
  });
  selectEl.value = current || '';
}

const chartColors = ['#0f9d8b', '#e8a33d', '#161b2e', '#d1495b', '#5c7ff2', '#7bb6ac', '#c98b3a'];

function renderTrendChart(rows) {
  const ctx = document.getElementById('trendChart');
  const labels = rows.map(r => monthLabel(r.ym));
  const values = rows.map(r => Number(r.revenue));

  if (trendChart) { trendChart.destroy(); }
  trendChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Revenue',
        data: values,
        borderColor: '#0f9d8b',
        backgroundColor: 'rgba(15,157,139,0.1)',
        borderWidth: 2,
        pointRadius: 3,
        pointBackgroundColor: '#0f9d8b',
        tension: 0.3,
        fill: true,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { ticks: { callback: v => '$' + v.toLocaleString() }, grid: { color: '#eef1f6' } },
        x: { grid: { display: false } }
      }
    }
  });
}

function renderCategoryChart(rows) {
  const ctx = document.getElementById('categoryChart');
  const labels = rows.map(r => r.category);
  const values = rows.map(r => Number(r.revenue));

  if (categoryChart) { categoryChart.destroy(); }
  categoryChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Revenue',
        data: values,
        backgroundColor: chartColors,
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { ticks: { callback: v => '$' + v.toLocaleString() }, grid: { color: '#eef1f6' } },
        x: { grid: { display: false } }
      }
    }
  });
}

function renderRegionChart(rows) {
  const ctx = document.getElementById('regionChart');
  const labels = rows.map(r => r.region);
  const values = rows.map(r => Number(r.revenue));

  if (regionChart) { regionChart.destroy(); }
  regionChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: chartColors,
        borderColor: '#ffffff',
        borderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } }
    }
  });
}

function updateExportLink() {
  const q = buildQuery();
  q.set('sort', state.sort);
  els.exportBtn.href = 'api/export_csv.php?' + q.toString();
}

// ---------- Table ----------
async function loadTable() {
  const q = buildQuery();
  q.set('sort', state.sort);
  q.set('page', state.page);

  updateExportLink();

  const res = await fetch('api/get_data.php?' + q.toString());
  if (redirectIfUnauthorized(res)) return;
  const data = await res.json();
  if (!data.success) return;

  renderTable(data.rows);
  renderPagination(data.page, data.total_pages, data.total);
}

function renderTable(rows) {
  if (!rows.length) {
    els.tableBody.innerHTML = '<tr><td colspan="7" class="empty-row">No matching order records found.</td></tr>';
    return;
  }
  els.tableBody.innerHTML = rows.map(r => `
    <tr>
      <td>${escapeHtml(r.order_id)}</td>
      <td>${formatDate(r.order_date)}</td>
      <td>${escapeHtml(r.product)}</td>
      <td><span class="pill">${escapeHtml(r.category)}</span></td>
      <td>${escapeHtml(r.region)}</td>
      <td>${formatNumber(r.quantity)}</td>
      <td>${formatCurrency(r.total_amount)}</td>
    </tr>
  `).join('');
}

function renderPagination(page, totalPages, total) {
  if (totalPages <= 1) {
    els.pagination.innerHTML = total ? `<span class="page-info">${total} record(s)</span>` : '';
    return;
  }

  let html = '';
  html += `<button ${page === 1 ? 'disabled' : ''} data-page="${page - 1}">‹</button>`;

  const maxButtons = 5;
  let start = Math.max(1, page - Math.floor(maxButtons / 2));
  let end = Math.min(totalPages, start + maxButtons - 1);
  start = Math.max(1, end - maxButtons + 1);

  for (let i = start; i <= end; i++) {
    html += `<button class="${i === page ? 'active' : ''}" data-page="${i}">${i}</button>`;
  }

  html += `<button ${page === totalPages ? 'disabled' : ''} data-page="${page + 1}">›</button>`;
  html += `<span class="page-info">${total} record(s)</span>`;

  els.pagination.innerHTML = html;
  els.pagination.querySelectorAll('button[data-page]').forEach(btn => {
    btn.addEventListener('click', () => {
      state.page = Number(btn.dataset.page);
      loadTable();
    });
  });
}

// ---------- Filters ----------
els.searchInput.addEventListener('input', () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    state.search = els.searchInput.value.trim();
    state.page = 1;
    loadStats();
    loadTable();
  }, 350);
});

els.yearFilter.addEventListener('change', () => {
  state.year = els.yearFilter.value;
  state.page = 1;
  loadStats();
  loadTable();
});

els.categoryFilter.addEventListener('change', () => {
  state.category = els.categoryFilter.value;
  state.page = 1;
  loadStats();
  loadTable();
});

els.regionFilter.addEventListener('change', () => {
  state.region = els.regionFilter.value;
  state.page = 1;
  loadStats();
  loadTable();
});

els.clearFilters.addEventListener('click', () => {
  state.search = '';
  state.category = '';
  state.region = '';
  state.year = '';
  state.page = 1;
  els.searchInput.value = '';
  els.categoryFilter.value = '';
  els.regionFilter.value = '';
  els.yearFilter.value = '';
  loadStats();
  loadTable();
});

els.sortAmount.addEventListener('click', () => {
  state.sort = state.sort === 'desc' ? 'asc' : 'desc';
  els.sortArrow.textContent = state.sort === 'desc' ? '▼' : '▲';
  loadTable();
});

// ---------- Init ----------
(async function init() {
  await loadStats();
  await loadTable();
})();
