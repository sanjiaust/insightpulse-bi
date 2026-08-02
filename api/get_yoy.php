<?php
header('Content-Type: application/json');
require_once '../auth.php';
require_once '../config.php';

require_login_api();
$userId = current_user_id();

$conn = get_db_connection();

// --- Yearly totals, with prior-year values pulled in via LAG() (window function) ---
// Requires MySQL 8+ or MariaDB 10.2+ (standard on current XAMPP releases).
$sql = "SELECT yr, revenue, orders, avg_order_value, units,
               LAG(revenue) OVER (ORDER BY yr) AS prev_revenue,
               LAG(orders) OVER (ORDER BY yr) AS prev_orders
        FROM (
            SELECT YEAR(order_date) AS yr,
                   SUM(total_amount) AS revenue,
                   COUNT(*) AS orders,
                   ROUND(AVG(total_amount), 2) AS avg_order_value,
                   SUM(quantity) AS units
            FROM sales
            WHERE user_id = ?
            GROUP BY yr
        ) t
        ORDER BY yr ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();

$yearly = [];
while ($row = $res->fetch_assoc()) {
    $revenue = (float)$row['revenue'];
    $orders  = (int)$row['orders'];
    $prevRevenue = $row['prev_revenue'] !== null ? (float)$row['prev_revenue'] : null;
    $prevOrders  = $row['prev_orders']  !== null ? (int)$row['prev_orders']  : null;

    $growthRevenuePct = ($prevRevenue !== null && $prevRevenue != 0)
        ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1) : null;
    $growthOrdersPct = ($prevOrders !== null && $prevOrders != 0)
        ? round((($orders - $prevOrders) / $prevOrders) * 100, 1) : null;

    $yearly[] = [
        'year'                => (int)$row['yr'],
        'revenue'             => $revenue,
        'orders'              => $orders,
        'avg_order_value'     => (float)$row['avg_order_value'],
        'units'               => (int)$row['units'],
        'growth_revenue_pct'  => $growthRevenuePct,
        'growth_orders_pct'   => $growthOrdersPct,
    ];
}
$stmt->close();

// --- Monthly revenue matrix (month x year), for side-by-side comparison ---
$sql = "SELECT YEAR(order_date) AS yr, MONTH(order_date) AS mo, SUM(total_amount) AS revenue
        FROM sales WHERE user_id = ?
        GROUP BY yr, mo ORDER BY mo ASC, yr ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();

$monthNames = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
               '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
$matrix = [];
foreach ($monthNames as $num => $name) {
    $matrix[$num] = ['month' => $name, 'values' => []];
}
$yearsSeen = [];
while ($row = $res->fetch_assoc()) {
    $mo = str_pad($row['mo'], 2, '0', STR_PAD_LEFT);
    $yr = (string)$row['yr'];
    $matrix[$mo]['values'][$yr] = (float)$row['revenue'];
    $yearsSeen[$yr] = true;
}
$stmt->close();

$yearsList = array_keys($yearsSeen);
sort($yearsList);

echo json_encode([
    'success' => true,
    'yearly' => $yearly,
    'monthly_matrix' => array_values($matrix),
    'years' => $yearsList,
]);
