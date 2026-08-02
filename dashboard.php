<?php
require_once 'auth.php';
require_once 'config.php';
require_login_page();
$activeTab = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Overview — <?php echo htmlspecialchars(APP_NAME); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
  <?php include 'partials/sidebar.php'; ?>

  <div class="main-content">
  <main class="layout">

    <!-- KPI cards -->
    <section class="kpi-grid">
      <div class="kpi-card">
        <span class="kpi-label">Total Sales</span>
        <span class="kpi-value" id="kpiTotalSales">–</span>
        <span class="kpi-sub">units sold</span>
      </div>
      <div class="kpi-card">
        <span class="kpi-label">Total Orders</span>
        <span class="kpi-value" id="kpiTotalOrders">–</span>
        <span class="kpi-sub">order records</span>
      </div>
      <div class="kpi-card kpi-accent">
        <span class="kpi-label">Total Revenue</span>
        <span class="kpi-value" id="kpiTotalRevenue">–</span>
        <span class="kpi-sub">gross amount</span>
      </div>
      <div class="kpi-card">
        <span class="kpi-label">Avg. Order Value</span>
        <span class="kpi-value" id="kpiAvgOrder">–</span>
        <span class="kpi-sub">per order</span>
      </div>
      <div class="kpi-card">
        <span class="kpi-label">Best Category</span>
        <span class="kpi-value kpi-value-text" id="kpiBestCategory">–</span>
        <span class="kpi-sub">by revenue</span>
      </div>
      <div class="kpi-card">
        <span class="kpi-label">Best Region</span>
        <span class="kpi-value kpi-value-text" id="kpiBestRegion">–</span>
        <span class="kpi-sub">by revenue</span>
      </div>
      <div class="kpi-card">
        <span class="kpi-label">Highest Revenue Month</span>
        <span class="kpi-value kpi-value-text" id="kpiHighMonth">–</span>
        <span class="kpi-sub" id="kpiHighMonthSub">–</span>
      </div>
      <div class="kpi-card">
        <span class="kpi-label">Lowest Revenue Month</span>
        <span class="kpi-value kpi-value-text" id="kpiLowMonth">–</span>
        <span class="kpi-sub" id="kpiLowMonthSub">–</span>
      </div>
    </section>

    <!-- Charts -->
    <section class="panel chart-panel">
      <div class="panel-head">
        <h2>Trends</h2>
        <p>Monthly revenue trend across the imported period.</p>
      </div>
      <div class="chart-box chart-box-wide">
        <canvas id="trendChart"></canvas>
      </div>
    </section>

    <section class="panel chart-panel-split">
      <div class="chart-half">
        <div class="panel-head">
          <h2>By category</h2>
          <p>Revenue split across product categories.</p>
        </div>
        <div class="chart-box">
          <canvas id="categoryChart"></canvas>
        </div>
      </div>
      <div class="chart-half">
        <div class="panel-head">
          <h2>By region</h2>
          <p>Revenue share by sales region.</p>
        </div>
        <div class="chart-box">
          <canvas id="regionChart"></canvas>
        </div>
      </div>
    </section>

    <!-- Filters + table -->
    <section class="panel table-panel">
      <div class="panel-head">
        <h2>Order records</h2>
        <p>Search, filter, and sort the imported sales rows.</p>
      </div>

      <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="Search by product name…" class="filter-input">
        <select id="yearFilter" class="filter-select">
          <option value="">All years</option>
        </select>
        <select id="categoryFilter" class="filter-select">
          <option value="">All categories</option>
        </select>
        <select id="regionFilter" class="filter-select">
          <option value="">All regions</option>
        </select>
        <button id="clearFilters" class="btn-ghost">Clear</button>
        <a id="exportBtn" href="#" class="btn-ghost export-link">Export CSV</a>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Order Date</th>
              <th>Product</th>
              <th>Category</th>
              <th>Region</th>
              <th>Qty</th>
              <th id="sortAmount" class="sortable">Total Amount <span class="sort-arrow" id="sortArrow">▼</span></th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <tr><td colspan="7" class="empty-row">No data yet — go to <a href="import.php">Import Data</a> to upload a CSV.</td></tr>
          </tbody>
        </table>
      </div>

      <div class="pagination" id="pagination"></div>
    </section>

  </main>

    <footer class="app-footer"><?php echo htmlspecialchars(APP_NAME); ?> — runs locally on XAMPP (PHP + MySQL)</footer>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="js/common.js"></script>
<script src="js/dashboard.js"></script>
</body>
</html>
