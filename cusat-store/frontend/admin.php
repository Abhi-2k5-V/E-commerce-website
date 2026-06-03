<?php
include 'header.php';

// Authorization check: Must be logged in and must be an administrator
if (!$is_admin) {
    header("Location: index.php");
    exit();
}

$orders = [];
$products = [];
$error_orders_msg = null;
$error_products_msg = null;
$admin_token = $_SESSION['admin_token'];

// 1. Fetch all orders from FastAPI (passing admin token header)
$orders_url = "http://localhost:8000/api/orders";
try {
    $options = [
        'http' => [
            'header'  => "x-admin-token: " . $admin_token . "\r\n",
            'method'  => 'GET',
            'timeout' => 3.0,
            'ignore_errors' => true
        ]
    ];
    $context  = stream_context_create($options);
    $response = @file_get_contents($orders_url, false, $context);
    
    if (isset($http_response_header)) {
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        $status_code = intval($match[1]);
    } else {
        $status_code = 500;
    }

    if ($response === FALSE || $status_code != 200) {
        $error_orders_msg = "Could not fetch orders log. Please check if FastAPI backend is running.";
    } else {
        $orders = json_decode($response, true);
    }
} catch (Exception $e) {
    $error_orders_msg = "An error occurred while fetching orders.";
}

// 2. Fetch all products from FastAPI to display with delete actions
$products_url = "http://localhost:8000/api/products";
try {
    $ctx = stream_context_create(['http' => ['timeout' => 3.0]]);
    $response = @file_get_contents($products_url, false, $ctx);
    if ($response === FALSE) {
        $error_products_msg = "Could not fetch catalog products.";
    } else {
        $products = json_decode($response, true);
    }
} catch (Exception $e) {
    $error_products_msg = "An error occurred while fetching products.";
}
?>

<main class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="font-size: 32px;">Merchant & Admin Control Panel</h2>
        <span style="background-color: var(--cusat-gold); color: var(--primary); font-weight: 700; padding: 6px 14px; border-radius: var(--radius-full); font-size: 13px;">
            Authorized Admin Account
        </span>
    </div>

    <div class="admin-grid">
        
        <!-- Left: Product Management -->
        <div style="display: flex; flex-direction: column; gap: 30px;">
            
            <!-- Add Product Form Card -->
            <div class="admin-card">
                <h3 style="font-size: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">Add New Store Product</h3>
                
                <form id="add-product-form" onsubmit="handleAddProduct(event, '<?php echo $admin_token; ?>')">
                    <div class="form-group">
                        <label for="prod_name" class="form-label">Product Title</label>
                        <input type="text" id="prod_name" name="name" class="form-input" placeholder="e.g. CUSAT Polo T-Shirt" required>
                    </div>

                    <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label for="prod_price" class="form-label">Price (INR)</label>
                            <input type="number" id="prod_price" name="price" step="0.01" class="form-input" placeholder="e.g. 399.00" required>
                        </div>
                        <div>
                            <label for="prod_cat" class="form-label">Category</label>
                            <select id="prod_cat" name="category" class="form-input" style="height: 48px;" required>
                                <option value="Apparel">Apparel</option>
                                <option value="Textbooks">Textbooks</option>
                                <option value="Tech">Tech</option>
                                <option value="Stationery">Stationery</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="prod_img" class="form-label">Product Image URL</label>
                        <input type="url" id="prod_img" name="image_url" class="form-input" placeholder="e.g. https://images.unsplash.com/... (optional)">
                    </div>

                    <div class="form-group">
                        <label for="prod_desc" class="form-label">Product Description</label>
                        <textarea id="prod_desc" name="description" class="form-input" rows="4" placeholder="Detail the materials, size guide, syllabus relevance, etc." style="resize: none;" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-teal" style="width: 100%; padding: 12px; margin-top: 10px;">Add Product to Catalog</button>
                </form>
            </div>

            <!-- Existing Products Catalog (with Delete) -->
            <div class="admin-card">
                <h3 style="font-size: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">Store Catalog Management</h3>
                
                <?php if ($error_products_msg): ?>
                    <p style="color: var(--error); font-size: 14px;"><?php echo $error_products_msg; ?></p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 14px; max-height: 450px; overflow-y: auto; padding-right: 6px;">
                        <?php foreach ($products as $prod): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; background-color: #f1f5f9;">
                                    <div>
                                        <div style="font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($prod['name']); ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted);">₹<?php echo number_format($prod['price'], 2); ?> | <?php echo htmlspecialchars($prod['category']); ?></div>
                                    </div>
                                </div>
                                <button class="btn btn-danger" onclick="deleteProduct(<?php echo $prod['id']; ?>, '<?php echo $admin_token; ?>')" style="padding: 6px 10px; font-size: 11px;">
                                    Delete
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Incoming Orders Log -->
        <div class="admin-card">
            <h3 style="font-size: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">Incoming Customer Orders</h3>
            
            <?php if ($error_orders_msg): ?>
                <div style="background-color: #fee2e2; border-left: 4px solid var(--error); color: #991b1b; padding: 12px; border-radius: var(--radius-sm); font-size: 14px;">
                    <strong>Error: </strong> <?php echo htmlspecialchars($error_orders_msg); ?>
                </div>
            <?php else: ?>
                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.3; margin-bottom: 12px;">
                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                        </svg>
                        <p>No orders have been placed in the store yet.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <?php foreach ($orders as $order): ?>
                            <div style="border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; background-color: var(--bg-light);">
                                <!-- Header -->
                                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px; margin-bottom: 10px; flex-wrap: wrap;">
                                    <span style="font-weight: 700; color: var(--cusat-blue); font-size: 15px;">#CUSAT-<?php echo htmlspecialchars($order['id']); ?></span>
                                    <span style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($order['created_at']); ?></span>
                                    <span class="status-badge <?php echo ($order['status'] == 'Pending') ? 'status-pending' : 'status-completed'; ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </div>

                                <!-- Customer Details Grid -->
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px; margin-bottom: 12px; border-bottom: 1px dashed var(--border); padding-bottom: 10px;">
                                    <div>
                                        <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Customer</div>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                        <div><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                        <div>Ph: <?php echo htmlspecialchars($order['customer_phone']); ?></div>
                                    </div>
                                    <div>
                                        <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Campus Address</div>
                                        <div><strong>Dept:</strong> <?php echo htmlspecialchars($order['department']); ?></div>
                                        <div><strong>Roll:</strong> <?php echo htmlspecialchars($order['roll_number']); ?></div>
                                        <div><strong>Dest:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></div>
                                    </div>
                                </div>

                                <!-- Items -->
                                <div style="font-size: 13px; margin-bottom: 10px;">
                                    <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; margin-bottom: 6px;">Ordered Items</div>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <div style="display: flex; justify-content: space-between;">
                                                <span><?php echo htmlspecialchars($item['product_name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                                                <span style="font-weight: 600;">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Total Footer -->
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 10px; font-size: 14px; font-weight: 700;">
                                    <span>Total Amount (COD):</span>
                                    <span style="color: var(--cusat-blue); font-size: 16px;">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>
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
