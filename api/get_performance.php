<?php
header('Content-Type: application/json');
require_once '../auth.php';
require_once '../config.php';

require_login_api();
$userId = current_user_id();

$conn = get_db_connection();

$year = $_GET['year'] ?? '';

$where = ['user_id = ?'];
$params = [$userId];
$types = 'i';
if ($year !== '') {
    $where[] = 'YEAR(order_date) = ?';
    $params[] = (int)$year;
    $types .= 'i';
}
$whereSql = 'WHERE ' . implode(' AND ', $where);

// Grand total revenue (for market share %), same filter scope.
$stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) AS grand_total FROM sales $whereSql");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$grandTotal = (float)$stmt->get_result()->fetch_assoc()['grand_total'];
$stmt->close();

function run_query($conn, $sql, $types, $params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    $stmt->close();
    return $rows;
}

// --- Category performance ---
$sql = "SELECT category,
               SUM(total_amount) AS revenue,
               SUM(quantity) AS units,
               COUNT(*) AS orders,
               ROUND(AVG(total_amount), 2) AS avg_order_value
        FROM sales $whereSql
        GROUP BY category
        ORDER BY revenue DESC";
$byCategory = run_query($conn, $sql, $types, $params);
foreach ($byCategory as &$row) {
    $row['market_share'] = $grandTotal > 0 ? round(((float)$row['revenue'] / $grandTotal) * 100, 1) : 0;
}
unset($row);

// --- Region performance ---
$sql = "SELECT region,
               SUM(total_amount) AS revenue,
               SUM(quantity) AS units,
               COUNT(*) AS orders,
               ROUND(AVG(total_amount), 2) AS avg_order_value,
               ROUND(AVG(quantity), 2) AS avg_quantity
        FROM sales $whereSql
        GROUP BY region
        ORDER BY revenue DESC";
$byRegion = run_query($conn, $sql, $types, $params);
foreach ($byRegion as &$row) {
    $row['market_share'] = $grandTotal > 0 ? round(((float)$row['revenue'] / $grandTotal) * 100, 1) : 0;
}
unset($row);

$years = [];
$stmt = $conn->prepare("SELECT DISTINCT YEAR(order_date) AS yr FROM sales WHERE user_id = ? ORDER BY yr DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $years[] = (string)$row['yr'];
$stmt->close();

echo json_encode([
    'success' => true,
    'by_category' => $byCategory,
    'by_region'   => $byRegion,
    'grand_total' => $grandTotal,
    'years' => $years,
]);
