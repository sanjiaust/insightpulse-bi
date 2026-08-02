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

function run_ranked_query($conn, $sql, $types, $params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    $stmt->close();
    return $rows;
}

// --- Top 10 products by revenue, ranked with RANK() ---
$sql = "SELECT product,
               SUM(total_amount) AS revenue,
               SUM(quantity) AS units_sold,
               COUNT(*) AS orders,
               ROUND(SUM(total_amount) / NULLIF(SUM(quantity), 0), 2) AS avg_selling_price,
               RANK() OVER (ORDER BY SUM(total_amount) DESC) AS rnk
        FROM sales $whereSql
        GROUP BY product
        ORDER BY revenue DESC
        LIMIT 10";
$topProducts = run_ranked_query($conn, $sql, $types, $params);

// --- Bottom 10 products by revenue, ranked from the bottom up ---
$sql = "SELECT product,
               SUM(total_amount) AS revenue,
               SUM(quantity) AS units_sold,
               COUNT(*) AS orders,
               ROUND(SUM(total_amount) / NULLIF(SUM(quantity), 0), 2) AS avg_selling_price,
               RANK() OVER (ORDER BY SUM(total_amount) ASC) AS rnk
        FROM sales $whereSql
        GROUP BY product
        ORDER BY revenue ASC
        LIMIT 10";
$bottomProducts = run_ranked_query($conn, $sql, $types, $params);

// --- Top 10 customers by revenue ---
$sql = "SELECT customer_name,
               SUM(total_amount) AS revenue,
               COUNT(*) AS orders,
               ROUND(AVG(total_amount), 2) AS avg_order_value,
               RANK() OVER (ORDER BY SUM(total_amount) DESC) AS rnk
        FROM sales $whereSql
        GROUP BY customer_name
        ORDER BY revenue DESC
        LIMIT 10";
$topCustomers = run_ranked_query($conn, $sql, $types, $params);

// Years available, for the page's year filter.
$years = [];
$stmt = $conn->prepare("SELECT DISTINCT YEAR(order_date) AS yr FROM sales WHERE user_id = ? ORDER BY yr DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $years[] = (string)$row['yr'];
$stmt->close();

echo json_encode([
    'success' => true,
    'top_products'    => $topProducts,
    'bottom_products' => $bottomProducts,
    'top_customers'   => $topCustomers,
    'years' => $years,
]);
