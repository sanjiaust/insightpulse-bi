<?php
require_once 'auth.php';
require_once 'config.php';
require_login_page();
$activeTab = 'import';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Import Data — <?php echo htmlspecialchars(APP_NAME); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-shell">
  <?php include 'partials/sidebar.php'; ?>

  <div class="main-content">
  <main class="layout">

    <section class="panel upload-panel">
      <div class="panel-head">
        <h2>Import a CSV file</h2>
        <p>Each upload adds to your account's data — upload one year at a time and switch between them using the Year filter on the other pages.</p>
      </div>

      <form id="uploadForm" class="upload-form">
        <label for="csvFile" class="dropzone" id="dropzone">
          <span class="dropzone-icon">⇪</span>
          <span class="dropzone-text">Choose a CSV file or drag it here</span>
          <span class="dropzone-file" id="fileNameLabel"></span>
          <input type="file" id="csvFile" name="csv_file" accept=".csv" hidden>
        </label>
        <button type="submit" class="btn-primary" id="uploadBtn">Import CSV</button>
      </form>

      <div id="uploadMessage" class="upload-message" hidden></div>
    </section>

    <section class="panel guideline-panel">
      <div class="panel-head">
        <h2>Expected columns</h2>
        <p>Headers are matched case-insensitively and can appear in any order.</p>
      </div>
      <div class="table-wrap">
        <table class="data-table guideline-table">
          <thead>
            <tr><th>Column</th><th>Required?</th><th>Type</th><th>If missing or invalid</th></tr>
          </thead>
          <tbody>
            <tr><td>Order ID</td><td><span class="pill pill-required">Required</span></td><td>Text</td><td>Row is skipped</td></tr>
            <tr><td>Order Date</td><td><span class="pill pill-required">Required</span></td><td>Date</td><td>Row is skipped</td></tr>
            <tr><td>Customer Name</td><td><span class="pill pill-optional">Optional</span></td><td>Text</td><td>Filled in as "Unknown Customer"</td></tr>
            <tr><td>Product</td><td><span class="pill pill-optional">Optional</span></td><td>Text</td><td>Filled in as "Unspecified Product"</td></tr>
            <tr><td>Category</td><td><span class="pill pill-optional">Optional</span></td><td>Text</td><td>Filled in as "Uncategorized"</td></tr>
            <tr><td>Region</td><td><span class="pill pill-optional">Optional</span></td><td>Text</td><td>Filled in as "Unspecified"</td></tr>
            <tr><td>Quantity</td><td><span class="pill pill-conditional">Conditional</span></td><td>Number</td><td>Calculated from Total Amount ÷ Unit Price if both are present; otherwise row is skipped</td></tr>
            <tr><td>Unit Price</td><td><span class="pill pill-conditional">Conditional</span></td><td>Number</td><td>Calculated from Total Amount ÷ Quantity if both are present; otherwise row is skipped</td></tr>
            <tr><td>Total Amount</td><td><span class="pill pill-conditional">Conditional</span></td><td>Number</td><td>Calculated as Quantity × Unit Price if both are present; otherwise row is skipped</td></tr>
          </tbody>
        </table>
      </div>

      <div class="guideline-note">
        <strong>How missing values are handled:</strong> Order ID and Order Date are the only fields a row absolutely
        cannot be imported without. Text fields (Customer Name, Product, Category, Region) fall back to clear
        placeholder values instead of being dropped, so a few blank cells don't cost you the whole row. Numeric
        fields (Quantity, Unit Price, Total Amount) are cross-calculated when possible — if any one of the three
        is missing but the other two are present, it's derived automatically. A row is only skipped when a
        required field is missing or a numeric field can't be resolved. After every import you'll see a summary
        of how many rows were inserted, how many had a value filled in automatically, and how many were skipped —
        with reasons.
      </div>

      <details class="csv-help">
        <summary>Accepted date formats</summary>
        <code>YYYY-MM-DD, MM/DD/YYYY, DD/MM/YYYY, DD-MM-YYYY, YYYY/MM/DD, MM-DD-YYYY</code>
      </details>
    </section>

  </main>

    <footer class="app-footer"><?php echo htmlspecialchars(APP_NAME); ?> — runs locally on XAMPP (PHP + MySQL)</footer>
  </div>
</div>

<script src="js/import.js"></script>
</body>
</html>
