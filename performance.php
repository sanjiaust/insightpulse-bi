<?php
require_once 'auth.php';
require_once 'config.php';
require_login_page();
$activeTab = 'performance';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Category &amp; Region — <?php echo htmlspecialchars(APP_NAME); ?></title>
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
        <h2>Category &amp; region performance</h2>
        <p>Revenue, orders, average order value, and market share % for each category and region.</p>
      </div>
      <div class="filter-bar">
        <select id="yearFilter" class="filter-select">
          <option value="">All years</option>
        </select>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head">
        <h2>By category</h2>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>Category</th><th>Revenue</th><th>Units</th><th>Orders</th><th>Avg. Order Value</th><th>Market Share</th></tr>
          </thead>
          <tbody id="categoryBody"><tr><td colspan="6" class="empty-row">No data yet.</td></tr></tbody>
        </table>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head">
        <h2>By region</h2>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>Region</th><th>Revenue</th><th>Units</th><th>Orders</th><th>Avg. Order Value</th><th>Avg. Quantity</th><th>Market Share</th></tr>
          </thead>
          <tbody id="regionBody"><tr><td colspan="7" class="empty-row">No data yet.</td></tr></tbody>
        </table>
      </div>
    </section>

  </main>

    <footer class="app-footer"><?php echo htmlspecialchars(APP_NAME); ?> — runs locally on XAMPP (PHP + MySQL)</footer>
  </div>
</div>

<script src="js/common.js"></script>
<script src="js/performance.js"></script>
</body>
</html>
