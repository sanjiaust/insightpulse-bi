<?php
require_once 'auth.php';
require_once 'config.php';
require_login_page();
$activeTab = 'yoy';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Year-over-Year — <?php echo htmlspecialchars(APP_NAME); ?></title>
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
        <h2>Year-over-Year comparison</h2>
        <p>Revenue, orders, and average order value for each year you've imported, with growth % calculated using SQL's <code>LAG()</code> window function against the prior year.</p>
      </div>
      <div class="yoy-cards" id="yoyCards">
        <p class="empty-state">Import at least two years of data to see growth comparisons.</p>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head">
        <h2>Monthly revenue by year</h2>
        <p>Each month lined up across years, so you can spot seasonal shifts.</p>
      </div>
      <div class="table-wrap">
        <table class="data-table" id="monthlyMatrixTable">
          <thead><tr><th>Month</th></tr></thead>
          <tbody><tr><td class="empty-row">No data yet.</td></tr></tbody>
        </table>
      </div>
    </section>

  </main>

    <footer class="app-footer"><?php echo htmlspecialchars(APP_NAME); ?> — runs locally on XAMPP (PHP + MySQL)</footer>
  </div>
</div>

<script src="js/common.js"></script>
<script src="js/yoy.js"></script>
</body>
</html>
