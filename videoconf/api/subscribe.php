<?php
/**
 * Video Conference SFU Solution - API: Subscribe to Plan
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

// Get POST data
$plan_id = (int)($_POST['plan_id'] ?? 0);
$payment_method = sanitize($_POST['payment_method'] ?? '');

if ($plan_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Plan ID is required']);
    exit;
}

// Get plan info
$stmt = mysqli_prepare($conn, "SELECT * FROM subscription_plans WHERE id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $plan_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$plan = mysqli_fetch_assoc($result);

if (!$plan) {
    echo json_encode(['success' => false, 'error' => 'Plan not found or inactive']);
    exit;
}

// Check if user already has an active subscription
$stmt = mysqli_prepare($conn, "SELECT id FROM user_subscriptions 
    WHERE user_id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_fetch_assoc($result)) {
    // Cancel existing subscription first
    $stmt = mysqli_prepare($conn, "UPDATE user_subscriptions SET status = 'cancelled', cancelled_at = NOW() 
        WHERE user_id = ? AND status = 'active'");
    mysqli_stmt_bind_param($stmt, "i", $user['id']);
    mysqli_stmt_execute($stmt);
}

// In a real implementation, you would integrate with a payment gateway here
// For demo purposes, we'll create the subscription directly

// Calculate end date based on billing cycle
$billing_cycle = $plan['billing_cycle'];
if ($billing_cycle === 'monthly') {
    $ends_at = date('Y-m-d H:i:s', strtotime('+1 month'));
} elseif ($billing_cycle === 'yearly') {
    $ends_at = date('Y-m-d H:i:s', strtotime('+1 year'));
} else {
    $ends_at = date('Y-m-d H:i:s', strtotime('+1 month'));
}

// Create subscription
$stmt = mysqli_prepare($conn, "INSERT INTO user_subscriptions 
    (user_id, plan_id, status, starts_at, ends_at, payment_method) 
    VALUES (?, ?, 'active', NOW(), ?, ?)");
mysqli_stmt_bind_param($stmt, "iiss", $user['id'], $plan_id, $ends_at, $payment_method);

if (mysqli_stmt_execute($stmt)) {
    $subscription_id = mysqli_insert_id($conn);
    
    // Log activity
    $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address) 
        VALUES (?, 'subscribe', 'Subscribed to plan: {$plan['name']}', ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_stmt_bind_param($stmt, "is", $user['id'], $ip);
    mysqli_stmt_execute($stmt);
    
    echo json_encode([
        'success' => true,
        'message' => 'Successfully subscribed to ' . $plan['name'],
        'subscription' => [
            'id' => $subscription_id,
            'plan_name' => $plan['name'],
            'ends_at' => $ends_at
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to create subscription']);
}

mysqli_close($conn);
?>
