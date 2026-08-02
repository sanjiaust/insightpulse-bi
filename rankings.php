<?php
require_once 'auth.php';
require_once 'config.php';
require_login_page();
$activeTab = 'rankings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rankings — <?php echo htmlspecialchars(APP_NAME); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
  <?php include 'partials/sidebar.php'; ?>

  <div class="main-content">
  <main class="layout">

    <section class="panel">
      <div class="panel-head">
        <h2>Rankings</h2>
        <p>Ranked with SQL's <code>RANK()</code> window function. Filter by year if you've imported more than one.</p>
      </div>
      <div class="filter-bar">
        <select id="yearFilter" class="filter-select">
          <option value="">All years</option>
        </select>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head">
        <h2>Top 10 products</h2>
        <p>Ranked by revenue, with units sold, orders, and average selling price.</p>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>Rank</th><th>Product</th><th>Revenue</th><th>Units Sold</th><th>Orders</th><th>Avg. Selling Price</th></tr>
          </thead>
          <tbody id="topProductsBody"><tr><td colspan="6" class="empty-row">No data yet.</td></tr></tbody>
        </table>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head">
        <h2>Bottom 10 products</h2>
        <p>Lowest revenue products — candidates for review or discontinuation.</p>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>Rank</th><th>Product</th><th>Revenue</th><th>Units Sold</th><th>Orders</th><th>Avg. Selling Price</th></tr>
          </thead>
          <tbody id="bottomProductsBody"><tr><td colspan="6" class="empty-row">No data yet.</td></tr></tbody>
        </table>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head">
        <h2>Top 10 customers</h2>
        <p>Ranked by total spend, with order count and average order value.</p>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>Rank</th><th>Customer</th><th>Revenue</th><th>Orders</th><th>Avg. Order Value</th></tr>
          </thead>
          <tbody id="topCustomersBody"><tr><td colspan="5" class="empty-row">No data yet.</td></tr></tbody>
        </table>
      </div>
    </section>

  </main>

    <footer class="app-footer"><?php echo htmlspecialchars(APP_NAME); ?> — runs locally on XAMPP (PHP + MySQL)</footer>
  </div>
</div>

<script src="js/common.js"></script>
<script src="js/rankings.js"></script>
</body>
</html>
