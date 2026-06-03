<?php
include 'header.php';

// Redirect if not logged in
if (!$is_logged_in) {
    header("Location: login.php");
    exit();
}

$orders = [];
$error_msg = null;

// Fetch orders for this user from FastAPI
$api_url = "http://localhost:8000/api/orders/user/" . urlencode($user_id);

try {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 3.0
        ]
    ]);
    $response = @file_get_contents($api_url, false, $ctx);
    
    if ($response === FALSE) {
        $error_msg = "Could not fetch your order history. Make sure the FastAPI backend is running.";
    } else {
        $orders = json_decode($response, true);
    }
} catch (Exception $e) {
    $error_msg = "An error occurred while loading order history.";
}
?>

<main class="container">
    <h2 style="font-size: 32px; margin-bottom: 30px;">My Orders</h2>

    <?php if ($error_msg): ?>
        <div style="background-color: #fee2e2; border-left: 4px solid var(--error); color: #991b1b; padding: 16px; border-radius: var(--radius-sm); font-size: 14px; margin-bottom: 30px;">
            <strong>Error: </strong> <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <?php if (!$error_msg): ?>
        <?php if (empty($orders)): ?>
            <div class="admin-card" style="text-align: center; padding: 60px 20px;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="var(--text-muted)" style="opacity: 0.5; margin-bottom: 16px;">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                </svg>
                <h3>No Orders Placed Yet</h3>
                <p style="color: var(--text-muted); margin-bottom: 24px;">You haven't ordered anything from CUSAT Store yet. Explore our products and place your first order!</p>
                <a href="index.php" class="btn btn-teal">Start Shopping</a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <?php foreach ($orders as $order): ?>
                    <div class="admin-card" style="padding: 24px;">
                        <!-- Order Summary Top Bar -->
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 16px; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                            <div>
                                <span style="font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Order Number</span>
                                <h4 style="font-size: 18px; color: var(--cusat-blue);">#CUSAT-<?php echo htmlspecialchars($order['id']); ?></h4>
                            </div>
                            <div>
                                <span style="font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: block; text-align: right;">Date Ordered</span>
                                <span style="font-weight: 600; font-size: 15px; color: var(--primary-light);"><?php echo htmlspecialchars($order['created_at']); ?></span>
                            </div>
                            <div>
                                <span style="font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: block; text-align: right;">Status</span>
                                <span class="status-badge <?php echo ($order['status'] == 'Pending') ? 'status-pending' : 'status-completed'; ?>" style="margin-top: 4px;">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Order Items List -->
                        <div style="margin-bottom: 16px;">
                            <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: block; margin-bottom: 8px;">Items Ordered</span>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div style="display: flex; justify-content: space-between; font-size: 14px;">
                                        <span style="color: var(--primary-light);">
                                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong> 
                                            <span style="color: var(--text-muted);">x <?php echo $item['quantity']; ?></span>
                                        </span>
                                        <span style="font-weight: 600;">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Order Total Footer -->
                        <div style="border-top: 1px solid var(--border); padding-top: 16px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 700; color: var(--primary-light);">Total Paid (COD):</span>
                            <span style="font-size: 20px; font-weight: 800; color: var(--cusat-blue); font-family: 'Outfit';">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<footer>
    <div class="container footer-container">
        <div class="footer-logo">CUSAT <span>Store</span></div>
        <p>&copy; 2026 Cochin University of Science and Technology. All rights reserved.</p>
    </div>
</footer>

<script src="app.js"></script>
</body>
</html>
