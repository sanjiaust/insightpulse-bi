<?php
header('Content-Type: application/json');
require_once '../auth.php';
require_once '../config.php';

require_login_api();
$userId = current_user_id();

$conn = get_db_connection();

$search   = $_GET['search']   ?? '';
$category = $_GET['category'] ?? '';
$region   = $_GET['region']   ?? '';
$year     = $_GET['year']     ?? '';
$sort     = ($_GET['sort'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 10;
$offset   = ($page - 1) * $perPage;

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

// Total count for pagination.
$countSql = "SELECT COUNT(*) AS cnt FROM sales $whereSql";
$stmt = $conn->prepare($countSql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// Page of rows, sorted by total_amount.
$dataSql = "SELECT order_id, order_date, product, category, region, quantity, total_amount
            FROM sales $whereSql
            ORDER BY total_amount $sort
            LIMIT ? OFFSET ?";
$stmt = $conn->prepare($dataSql);
$allTypes = $types . 'ii';
$allParams = array_merge($params, [$perPage, $offset]);
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($row = $res->fetch_assoc()) $rows[] = $row;
$stmt->close();

echo json_encode([
    'success' => true,
    'rows' => $rows,
    'total' => $total,
    'page' => $page,
    'per_page' => $perPage,
    'total_pages' => max(1, ceil($total / $perPage)),
]);
