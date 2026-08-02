<?php
require_once '../auth.php';
require_once '../config.php';

// This endpoint is a direct browser download link, not a fetch() call, so
// on auth failure we redirect rather than return JSON.
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}
$userId = current_user_id();

$conn = get_db_connection();

$search   = $_GET['search']   ?? '';
$category = $_GET['category'] ?? '';
$region   = $_GET['region']   ?? '';
$year     = $_GET['year']     ?? '';
$sort     = ($_GET['sort'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

$where = ['user_id = ?'];
$params = [$userId];
$types = 'i';

if ($search !== '') {
    $where[] = 'product LIKE ?';
    $params[] = '%' . $search . '%';
    $types .= 's';
}
if ($category !== '') {
    $where[] = 'category = ?';
    $params[] = $category;
    $types .= 's';
}
if ($region !== '') {
    $where[] = 'region = ?';
    $params[] = $region;
    $types .= 's';
}
if ($year !== '') {
    $where[] = 'YEAR(order_date) = ?';
    $params[] = (int)$year;
    $types .= 'i';
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

$sql = "SELECT order_id, order_date, customer_name, product, category, region, quantity, unit_price, total_amount
        FROM sales $whereSql
        ORDER BY total_amount $sort";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$filename = 'sales_export_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Order ID', 'Order Date', 'Customer Name', 'Product', 'Category', 'Region', 'Quantity', 'Unit Price', 'Total Amount']);
while ($row = $res->fetch_assoc()) {
    fputcsv($out, [
        $row['order_id'], $row['order_date'], $row['customer_name'], $row['product'],
        $row['category'], $row['region'], $row['quantity'], $row['unit_price'], $row['total_amount']
    ]);
}
fclose($out);
$stmt->close();
exit;
