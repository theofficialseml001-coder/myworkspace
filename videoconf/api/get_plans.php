<?php
/**
 * Video Conference SFU Solution - API: Get Subscription Plans
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in (optional for viewing plans)
$is_logged_in = isLoggedIn();
$user = $is_logged_in ? getCurrentUser() : null;
$conn = getDBConnection();

// Get all active plans
$stmt = mysqli_prepare($conn, "SELECT * FROM subscription_plans WHERE status = 'active' ORDER BY price ASC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$plans = [];
while ($plan = mysqli_fetch_assoc($result)) {
    $plans[] = [
        'id' => $plan['id'],
        'name' => $plan['name'],
        'description' => $plan['description'],
        'price' => $plan['price'],
        'currency' => $plan['currency'],
        'billing_cycle' => $plan['billing_cycle'],
        'max_participants' => $plan['max_participants'],
        'max_duration' => $plan['max_duration'],
        'max_recordings' => $plan['max_recordings'],
        'max_storage_gb' => $plan['max_storage_gb'],
        'features' => json_decode($plan['features'], true),
        'is_popular' => $plan['is_popular']
    ];
}

// Get current user's subscription if logged in
$current_subscription = null;
if ($user) {
    $stmt = mysqli_prepare($conn, "SELECT us.*, sp.name as plan_name, sp.price, sp.currency
        FROM user_subscriptions us
        JOIN subscription_plans sp ON us.plan_id = sp.id
        WHERE us.user_id = ? AND us.status = 'active'
        ORDER BY us.created_at DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $user['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $current_subscription = mysqli_fetch_assoc($result);
}

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'plans' => $plans,
    'current_subscription' => $current_subscription
]);
?>
