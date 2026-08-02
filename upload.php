<?php
header('Content-Type: application/json');
require_once 'auth.php';
require_once 'config.php';

require_login_api();
$userId = current_user_id();

// Expected header columns, in any order (case-insensitive, spaces optional).
$required = ['order id', 'order date', 'customer name', 'product', 'category', 'region', 'quantity', 'unit price', 'total amount'];

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function normalize_header($h) {
    return strtolower(trim(preg_replace('/\s+/', ' ', $h)));
}

// Try a couple of common date formats; fall back gracefully.
function parse_date($value) {
    $value = trim($value);
    if ($value === '') return null;
    $formats = ['Y-m-d', 'm/d/Y', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'm-d-Y'];
    foreach ($formats as $fmt) {
        $d = DateTime::createFromFormat($fmt, $value);
        if ($d && $d->format($fmt) === $value) {
            return $d->format('Y-m-d');
        }
    }
    $ts = strtotime($value);
    return $ts !== false ? date('Y-m-d', $ts) : null;
}

// Returns a float or null if the value is blank/non-numeric.
function parse_numeric($value) {
    $value = trim((string)$value);
    if ($value === '') return null;
    $clean = preg_replace('/[,\s]/', '', $value); // tolerate "1,200.50"
    return is_numeric($clean) ? (float)$clean : null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_file'])) {
    respond(false, 'No file was uploaded.');
}

$file = $_FILES['csv_file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'Upload error (code ' . $file['error'] . '). Please try again.');
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'csv') {
    respond(false, 'Invalid file format. Please upload a .csv file.');
}

$handle = fopen($file['tmp_name'], 'r');
if ($handle === false) {
    respond(false, 'Could not read the uploaded file.');
}

$header = fgetcsv($handle);
if ($header === false) {
    fclose($handle);
    respond(false, 'The CSV file appears to be empty.');
}

$normalizedHeader = array_map('normalize_header', $header);
$colIndex = [];
foreach ($required as $col) {
    $idx = array_search($col, $normalizedHeader);
    if ($idx === false) {
        fclose($handle);
        respond(false, "Invalid CSV format: missing required column \"$col\". " .
            "Expected columns: Order ID, Order Date, Customer Name, Product, Category, Region, Quantity, Unit Price, Total Amount.");
    }
    $colIndex[$col] = $idx;
}

$rows = [];
$skipped = 0;
$defaulted = 0;
$skipReasons = [];

while (($data = fgetcsv($handle)) !== false) {
    if (count($data) < count($header)) {
        $skipped++;
        $skipReasons['malformed row'] = ($skipReasons['malformed row'] ?? 0) + 1;
        continue;
    }

    $orderId  = trim($data[$colIndex['order id']]);
    $dateRaw  = trim($data[$colIndex['order date']]);
    $customer = trim($data[$colIndex['customer name']]);
    $product  = trim($data[$colIndex['product']]);
    $category = trim($data[$colIndex['category']]);
    $region   = trim($data[$colIndex['region']]);
    $qtyRaw    = parse_numeric($data[$colIndex['quantity']]);
    $priceRaw  = parse_numeric($data[$colIndex['unit price']]);
    $totalRaw  = parse_numeric($data[$colIndex['total amount']]);

    // --- Required fields: no fallback possible ---
    if ($orderId === '') {
        $skipped++;
        $skipReasons['missing Order ID'] = ($skipReasons['missing Order ID'] ?? 0) + 1;
        continue;
    }
    $date = parse_date($dateRaw);
    if ($date === null) {
        $skipped++;
        $skipReasons['missing/invalid Order Date'] = ($skipReasons['missing/invalid Order Date'] ?? 0) + 1;
        continue;
    }

    // --- Optional text fields: fall back to a clear placeholder ---
    $rowDefaulted = false;
    if ($customer === '') { $customer = 'Unknown Customer'; $rowDefaulted = true; }
    if ($product  === '') { $product  = 'Unspecified Product'; $rowDefaulted = true; }
    if ($category === '') { $category = 'Uncategorized'; $rowDefaulted = true; }
    if ($region   === '') { $region   = 'Unspecified'; $rowDefaulted = true; }

    // --- Numeric fields: cross-calculate one missing value from the other two ---
    $qty = $qtyRaw;
    $price = $priceRaw;
    $total = $totalRaw;

    if ($qty !== null && $price !== null && $total === null) {
        $total = round($qty * $price, 2);
        $rowDefaulted = true;
    } elseif ($qty !== null && $total !== null && $price === null) {
        if ($qty == 0) { $skipped++; $skipReasons['quantity is zero, cannot derive unit price'] = ($skipReasons['quantity is zero, cannot derive unit price'] ?? 0) + 1; continue; }
        $price = round($total / $qty, 2);
        $rowDefaulted = true;
    } elseif ($price !== null && $total !== null && $qty === null) {
        if ($price == 0) { $skipped++; $skipReasons['unit price is zero, cannot derive quantity'] = ($skipReasons['unit price is zero, cannot derive quantity'] ?? 0) + 1; continue; }
        $qty = (int)round($total / $price);
        $rowDefaulted = true;
    }

    if ($qty === null || $price === null || $total === null) {
        $skipped++;
        $skipReasons['insufficient numeric data (need at least 2 of Quantity / Unit Price / Total Amount)'] =
            ($skipReasons['insufficient numeric data (need at least 2 of Quantity / Unit Price / Total Amount)'] ?? 0) + 1;
        continue;
    }

    if ($rowDefaulted) $defaulted++;

    $rows[] = [$orderId, $date, $customer, $product, $category, $region, (int)$qty, (float)$price, (float)$total];
}
fclose($handle);

if (empty($rows)) {
    respond(false, 'No valid data rows were found in the CSV file.', ['skip_reasons' => $skipReasons]);
}

$conn = get_db_connection();

$conn->begin_transaction();
try {
    // Data is scoped per account and accumulates across uploads, so a user
    // can upload a 2025 file and later a 2026 file and see both. Re-uploading
    // the same order for the same account updates that row instead of
    // duplicating it (order_id is unique per user).
    $stmt = $conn->prepare(
        'INSERT INTO sales (user_id, order_id, order_date, customer_name, product, category, region, quantity, unit_price, total_amount)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           order_date = VALUES(order_date), customer_name = VALUES(customer_name),
           product = VALUES(product), category = VALUES(category), region = VALUES(region),
           quantity = VALUES(quantity), unit_price = VALUES(unit_price), total_amount = VALUES(total_amount)'
    );
    $stmt->bind_param('issssssidd', $userId, $orderId, $date, $customer, $product, $category, $region, $qty, $price, $total);

    $newCount = 0;
    $updatedCount = 0;
    $unchangedCount = 0;
    foreach ($rows as $r) {
        [$orderId, $date, $customer, $product, $category, $region, $qty, $price, $total] = $r;
        $stmt->execute();
        // With ON DUPLICATE KEY UPDATE, MySQL reports affected_rows as:
        // 1 = new row inserted, 2 = existing row updated with a changed value, 0 = duplicate key but no change.
        if ($stmt->affected_rows === 1) {
            $newCount++;
        } elseif ($stmt->affected_rows === 2) {
            $updatedCount++;
        } else {
            $unchangedCount++;
        }
    }
    $inserted = $newCount + $updatedCount + $unchangedCount;
    $stmt->close();
    $conn->commit();

    $msg = "Import complete: $newCount new order(s) added";
    if ($updatedCount > 0) $msg .= ", $updatedCount existing order(s) updated";
    if ($unchangedCount > 0) $msg .= ", $unchangedCount unchanged";
    if ($defaulted > 0) $msg .= ", $defaulted row(s) had a value auto-filled";
    if ($skipped > 0) $msg .= ", $skipped skipped";
    $msg .= '.';

    respond(true, $msg, [
        'new'          => $newCount,
        'updated'      => $updatedCount,
        'unchanged'    => $unchangedCount,
        'inserted'     => $inserted,
        'defaulted'    => $defaulted,
        'skipped'      => $skipped,
        'skip_reasons' => $skipReasons,
    ]);
} catch (Exception $e) {
    $conn->rollback();
    respond(false, 'Import failed: ' . $e->getMessage());
}
