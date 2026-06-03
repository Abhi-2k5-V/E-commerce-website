<?php
include 'header.php';

// Check if showing success receipt page
$order_placed = isset($_GET['success']) && $_GET['success'] == 1;
?>

<main class="container">

    <?php if ($order_placed): ?>
        <!-- Order Placement Success Page -->
        <section class="order-success-card">
            <div class="order-success-header">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <h2>Order Taken Successfully!</h2>
                <p>Thank you for shopping at CUSAT Store</p>
            </div>
            
            <div class="order-receipt-details">
                <h3 style="margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">Order Details</h3>
                
                <div class="receipt-info-grid">
                    <div>
                        <div class="receipt-label">Order ID</div>
                        <div class="receipt-value">#CUSAT-<?php echo htmlspecialchars($_GET['order_id']); ?></div>
                    </div>
                    <div>
                        <div class="receipt-label">Customer Name</div>
                        <div class="receipt-value"><?php echo htmlspecialchars($_GET['name']); ?></div>
                    </div>
                    <div>
                        <div class="receipt-label">Date & Time</div>
                        <div class="receipt-value"><?php echo htmlspecialchars($_GET['date']); ?></div>
                    </div>
                    <div>
                        <div class="receipt-label">Total Amount</div>
                        <div class="receipt-value" style="color: var(--cusat-blue); font-weight: 800;">₹<?php echo number_format($_GET['total'], 2); ?></div>
                    </div>
                </div>

                <div style="background-color: rgba(13, 148, 136, 0.05); border-left: 4px solid var(--teal); padding: 16px; border-radius: var(--radius-sm); margin-bottom: 24px; font-size: 14px; line-height: 1.6;">
                    <strong>Next Steps:</strong>
                    <ul style="margin-left: 20px; margin-top: 8px;">
                        <li>Your order has been recorded in our store system.</li>
                        <li><strong>No online payment is required.</strong> You can settle the payment offline via cash/UPI upon delivery or pickup.</li>
                        <li>You will be contacted on your phone for verification or delivery instructions.</li>
                    </ul>
                </div>

                <div style="display: flex; gap: 16px;">
                    <a href="index.php" class="btn btn-primary" style="flex: 1;">Continue Shopping</a>
                    <?php if ($is_logged_in): ?>
                        <a href="orders.php" class="btn btn-secondary" style="flex: 1;">View My Orders</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    <?php else: ?>
        <!-- Main Cart View -->
        <h2 style="font-size: 32px; margin-bottom: 30px;">Shopping Cart</h2>
        
        <div class="cart-grid">
            <!-- Left: Cart Items List -->
            <div class="cart-card">
                <h3 class="cart-card-title">Selected Items</h3>
                <div id="cart-items-list" class="cart-items-list">
                    <!-- Rendered dynamically by app.js -->
                </div>
            </div>

            <!-- Right: Order Form & Summary -->
            <div>
                <!-- Checkout Form Box -->
                <div class="summary-box">
                    <h3 style="font-size: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">Order & Delivery Details</h3>
                    
                    <?php if (!$is_logged_in): ?>
                        <!-- Login warning -->
                        <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid var(--accent); color: #b45309; padding: 16px; border-radius: var(--radius-md); font-size: 14px; line-height: 1.5; margin-bottom: 20px;">
                            <strong>Authentication Required</strong><br>
                            You must be logged in to place an order. Please sign in or register to proceed.
                            <div style="margin-top: 12px; display: flex; gap: 10px;">
                                <a href="login.php" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;">Login</a>
                                <a href="register.php" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">Register</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form id="checkout-form" style="<?php echo !$is_logged_in ? 'pointer-events: none; opacity: 0.4;' : ''; ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                        <input type="hidden" id="order-items-input" name="items" value="">

                        <div class="form-group">
                            <label for="customer_name" class="form-label">Full Name</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-input" 
                                   value="<?php echo htmlspecialchars($is_logged_in ? $_SESSION['user']['name'] : ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="customer_email" class="form-label">Email Address</label>
                            <input type="email" id="customer_email" name="customer_email" class="form-input" 
                                   value="<?php echo htmlspecialchars($is_logged_in ? $_SESSION['user']['email'] : ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="customer_phone" class="form-label">Phone Number</label>
                            <input type="tel" id="customer_phone" name="customer_phone" class="form-input" placeholder="e.g. +91 9876543210" required>
                        </div>

                        <div class="form-group">
                            <label for="department" class="form-label">CUSAT Department</label>
                            <input type="text" id="department" name="department" class="form-input" placeholder="e.g. School of Engineering (SOE)" required>
                        </div>

                        <div class="form-group">
                            <label for="roll_number" class="form-label">Roll Number</label>
                            <input type="text" id="roll_number" name="roll_number" class="form-input" placeholder="e.g. 20120045" required>
                        </div>

                        <div class="form-group">
                            <label for="delivery_address" class="form-label">Hostel Room / Department Delivery Address</label>
                            <textarea id="delivery_address" name="delivery_address" class="form-input" rows="3" placeholder="e.g. Room 402, Sahara Hostel / School of Engineering Office" style="resize: none;" required></textarea>
                        </div>

                        <!-- Price summary -->
                        <div style="margin: 24px 0 16px 0; border-top: 1px solid var(--border); padding-top: 16px;">
                            <div class="summary-row">
                                <span>Cart Subtotal</span>
                                <span id="summary-subtotal">₹0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Delivery Fee</span>
                                <span style="color: var(--success); font-weight: 600;">FREE (On Campus)</span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Total Amount</span>
                                <span id="summary-total">₹0.00</span>
                            </div>
                        </div>

                        <button type="submit" id="checkout-btn" class="btn btn-teal" style="width: 100%; padding: 12px; font-size: 15px;" <?php echo !$is_logged_in ? 'disabled' : ''; ?>>
                            Place Order (Cash on Delivery)
                        </button>
                    </form>
                </div>
            </div>
        </div>
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
