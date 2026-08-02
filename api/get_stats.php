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

// --- KPI cards ---
$sql = "SELECT COUNT(*) AS total_orders,
               COALESCE(SUM(quantity), 0) AS total_sales,
               COALESCE(SUM(total_amount), 0) AS total_revenue,
               COALESCE(AVG(total_amount), 0) AS avg_order_value
        FROM sales $whereSql";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$kpi = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- Monthly sales trend (line chart) ---
$sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') AS ym, COALESCE(SUM(total_amount),0) AS revenue
        FROM sales $whereSql
        GROUP BY ym ORDER BY ym ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$monthly = [];
while ($row = $res->fetch_assoc()) $monthly[] = $row;
$stmt->close();

// --- Sales by category (bar chart) ---
$sql = "SELECT category, COALESCE(SUM(total_amount),0) AS revenue
        FROM sales $whereSql
        GROUP BY category ORDER BY revenue DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$byCategory = [];
while ($row = $res->fetch_assoc()) $byCategory[] = $row;
$stmt->close();

// --- Sales by region (donut chart) ---
$sql = "SELECT region, COALESCE(SUM(total_amount),0) AS revenue
        FROM sales $whereSql
        GROUP BY region ORDER BY revenue DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$byRegion = [];
while ($row = $res->fetch_assoc()) $byRegion[] = $row;
$stmt->close();

// --- Distinct filter options, scoped to this user's own data, unaffected by
//     the current filters so the dropdowns always show every available option ---
$categories = [];
$stmt = $conn->prepare("SELECT DISTINCT category FROM sales WHERE user_id = ? ORDER BY category ASC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $categories[] = $row['category'];
$stmt->close();

$regions = [];
$stmt = $conn->prepare("SELECT DISTINCT region FROM sales WHERE user_id = ? ORDER BY region ASC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $regions[] = $row['region'];
$stmt->close();

$years = [];
$stmt = $conn->prepare("SELECT DISTINCT YEAR(order_date) AS yr FROM sales WHERE user_id = ? ORDER BY yr DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $years[] = (string)$row['yr'];
$stmt->close();

// --- Derived KPIs (no extra queries needed — reuse the result sets above) ---
$bestCategory = count($byCategory) ? $byCategory[0]['category'] : null;
$bestRegion   = count($byRegion) ? $byRegion[0]['region'] : null;

$highestMonth = null;
$lowestMonth  = null;
if (count($monthly)) {
    $sortedByRevenue = $monthly;
    usort($sortedByRevenue, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
    $highestMonth = $sortedByRevenue[0];
    $lowestMonth  = $sortedByRevenue[count($sortedByRevenue) - 1];
}

echo json_encode([
    'success' => true,
    'kpi' => [
        'total_sales'          => (int)$kpi['total_sales'],
        'total_orders'         => (int)$kpi['total_orders'],
        'total_revenue'        => (float)$kpi['total_revenue'],
        'avg_order_value'      => (float)$kpi['avg_order_value'],
        'best_category'        => $bestCategory,
        'best_region'          => $bestRegion,
        'highest_month'        => $highestMonth ? $highestMonth['ym'] : null,
        'highest_month_amount' => $highestMonth ? (float)$highestMonth['revenue'] : null,
        'lowest_month'         => $lowestMonth ? $lowestMonth['ym'] : null,
        'lowest_month_amount'  => $lowestMonth ? (float)$lowestMonth['revenue'] : null,
    ],
    'monthly_trend' => $monthly,
    'by_category'   => $byCategory,
    'by_region'     => $byRegion,
    'filters' => [
        'categories' => $categories,
        'regions'    => $regions,
        'years'      => $years,
    ]
]);
