<?php
    session_start();
    require_once 'db.php';

    // Redirect if not logged in
    if (! isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    // Get user information
    $user = null;
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();
    }

    // Helper function to get count from a table
    function getCount($mysqli, $table, $where = '')
    {
        $query  = "SELECT COUNT(*) as count FROM $table $where";
        $result = $mysqli->query($query);
        $row    = $result->fetch_assoc();
        return $row['count'];
    }

    // Dashboard statistics
    $stats = [
        'menu_items'     => getCount($mysqli, 'menu_items'),
        'categories'     => getCount($mysqli, 'categories'),
        'pending_orders' => getCount($mysqli, 'orders', "WHERE status = 'pending'"),
        'reservations'   => getCount($mysqli, 'reservations'),
        'testimonials'   => getCount($mysqli, 'testimonials'),
        'users'          => getCount($mysqli, 'users'),
    ];

    // Get recent orders
    $recent_orders = [];
    $result        = $mysqli->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 5");
    if ($result) {
        $recent_orders = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get sales data for the past 7 days
    $sales_data = [];
    $sales_sql  = "
    SELECT DATE(order_date) as order_day, SUM(total) as total_sales
    FROM orders
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(order_date)
";
    $result = $mysqli->query($sales_sql);
    if ($result) {
        $sales_data = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Prepare data for Chart.js
    $labels   = [];
    $datasets = [];
    foreach ($sales_data as $row) {
        $labels[]   = date('d/m', strtotime($row['order_day']));
        $datasets[] = $row['total_sales'];
    }

    // Get top-selling menu items
    $top_selling_products = [];
    $result               = $mysqli->query("
    SELECT name, sales_count as total_quantity
    FROM menu_items
    WHERE sales_count > 0
    ORDER BY sales_count DESC
    LIMIT 5
");
    if ($result) {
        $top_selling_products = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get pending reservations
    $pending_reservations = [];
    $result               = $mysqli->query("
    SELECT * FROM reservations
    ORDER BY reservation_date ASC
    LIMIT 5
");
    if ($result) {
        $pending_reservations = $result->fetch_all(MYSQLI_ASSOC);
    }
?>

<?php include_once 'includes/header.php'; ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="btn btn-sm btn-outline-secondary">
            Welcome,                                         <?php echo htmlspecialchars($user['full_name']); ?>
        </span>
    </div>
</div>

<?php include_once 'includes/stats_cards.php'; ?>
<?php include_once 'includes/recent_orders.php'; ?>
<?php include_once 'includes/sales_chart.php'; ?>
<?php include_once 'includes/top_selling.php'; ?>
<?php include_once 'includes/pending_reservations.php'; ?>
<?php include_once 'includes/footer.php'; ?>
