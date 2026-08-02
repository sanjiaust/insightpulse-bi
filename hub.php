<?php
require_once 'auth.php';
require_once 'config.php';
require_login_page();
$activeTab = 'hub';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hub — <?php echo htmlspecialchars(APP_NAME); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app-shell">
  <?php include 'partials/sidebar.php'; ?>

  <div class="main-content">
  <main class="layout">
    <section class="hub-intro">
      <h2>What do you want to analyze?</h2>
      <p>Pick a view below. Every view reflects only the CSV data your account has imported.</p>
    </section>

    <section class="hub-grid">
      <a href="dashboard.php" class="hub-card">
        <span class="hub-card-num">01</span>
        <h3>Overview Dashboard</h3>
        <p>KPI cards, monthly trend, category and region charts, and the full order table with search &amp; filters.</p>
        <span class="hub-card-cta">Open overview →</span>
      </a>

      <a href="yoy.php" class="hub-card">
        <span class="hub-card-num">02</span>
        <h3>Year-over-Year Comparison</h3>
        <p>Revenue, orders, and average order value compared across years, with growth % calculated using SQL window functions.</p>
        <span class="hub-card-cta">Compare years →</span>
      </a>

      <a href="rankings.php" class="hub-card">
        <span class="hub-card-num">03</span>
        <h3>Rankings</h3>
        <p>Top 10 and bottom 10 products by revenue, plus your top 10 customers ranked by spend.</p>
        <span class="hub-card-cta">View rankings →</span>
      </a>

      <a href="performance.php" class="hub-card">
        <span class="hub-card-num">04</span>
        <h3>Category &amp; Region Performance</h3>
        <p>Revenue, orders, average order value, and market share % broken down by category and by region.</p>
        <span class="hub-card-cta">See breakdown →</span>
      </a>

      <a href="import.php" class="hub-card hub-card-accent">
        <span class="hub-card-num">05</span>
        <h3>Import Data</h3>
        <p>Upload a CSV file for any year. Column guidelines and missing-value handling are explained on this page.</p>
        <span class="hub-card-cta">Import a CSV →</span>
      </a>
    </section>
  </main>

    <footer class="app-footer"><?php echo htmlspecialchars(APP_NAME); ?> — runs locally on XAMPP (PHP + MySQL)</footer>
  </div>
</div>
</body>
</html>
