// CUSAT Store Client Script
const API_BASE_URL = 'http://localhost:8000/api';

// Toast Notification Helper
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    

    // SVG icons based on type
    let icon = '';
    if (type === 'success') {
        icon = `<svg width="18" height="18" viewBox="0 0 24 24" fill="#10b981"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>`;
    } else {
        icon = `<svg width="18" height="18" viewBox="0 0 24 24" fill="#ef4444"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>`;
    }

    toast.innerHTML = `${icon} <span>${message}</span>`;
    container.appendChild(toast);

    // Trigger transition
    setTimeout(() => toast.classList.add('show'), 10);

    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Shopping Cart Core Functions
function getCart() {
    try {
        return JSON.parse(localStorage.getItem('cusat_cart')) || [];
    } catch {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem('cusat_cart', JSON.stringify(cart));
    updateCartBadge();
}

function addToCart(productId, name, price, imageUrl) {
    let cart = getCart();
    const existingIndex = cart.findIndex(item => item.product_id === productId);

    if (existingIndex > -1) {
        cart[existingIndex].quantity += 1;
    } else {
        cart.push({
            product_id: productId,
            name: name,
            price: price,
            image_url: imageUrl,
            quantity: 1
        });
    }

    saveCart(cart);
    showToast(`Added ${name} to cart!`, 'success');
}

function removeFromCart(productId) {
    let cart = getCart();
    cart = cart.filter(item => item.product_id !== productId);
    saveCart(cart);
    
    // If we are on the cart page, reload the items
    if (window.location.pathname.includes('cart.php')) {
        renderCartPage();
    }
    showToast("Item removed from cart.", "success");
}

function updateQuantity(productId, delta) {
    let cart = getCart();
    const index = cart.findIndex(item => item.product_id === productId);

    if (index > -1) {
        cart[index].quantity += delta;
        if (cart[index].quantity <= 0) {
            cart = cart.filter(item => item.product_id !== productId);
            showToast("Item removed from cart.", "success");
        }
        saveCart(cart);
        if (window.location.pathname.includes('cart.php')) {
            renderCartPage();
        }
    }
}

function clearCart() {
    localStorage.removeItem('cusat_cart');
    updateCartBadge();
}

function updateCartBadge() {
    const badge = document.getElementById('cart-badge');
    if (!badge) return;

    const cart = getCart();
    const totalCount = cart.reduce((total, item) => total + item.quantity, 0);

    if (totalCount > 0) {
        badge.textContent = totalCount;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

// Render Cart Page Items
function renderCartPage() {
    const cartItemsContainer = document.getElementById('cart-items-list');
    const orderItemsInput = document.getElementById('order-items-input');
    const summarySubtotal = document.getElementById('summary-subtotal');
    const summaryTotal = document.getElementById('summary-total');
    const checkoutBtn = document.getElementById('checkout-btn');
    const checkoutForm = document.getElementById('checkout-form');
    
    if (!cartItemsContainer) return;

    const cart = getCart();
    
    if (cart.length === 0) {
        cartItemsContainer.innerHTML = `
            <div class="empty-state">
                <svg viewBox="0 0 24 24"><path d="M17.21 9l-4.38-6.56c-.19-.28-.51-.42-.83-.42-.32 0-.64.14-.83.43L6.79 9H2c-.55 0-1 .45-1 1 0 .09.01.18.04.27l2.54 9.27c.23.84 1 1.46 1.88 1.46h13.08c.88 0 1.65-.62 1.88-1.46l2.54-9.27L23 10c0-.55-.45-1-1-1h-4.79zM9 9l3-4.5L15 9H9zm9.08 10H5.92L3.99 11h16.02l-1.93 8zM12 13c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                <h3>Your Cart is Empty</h3>
                <p>Add some custom CUSAT merchandise or textbook books to get started!</p>
                <a href="index.php" class="btn btn-primary">Go to Catalog</a>
            </div>
        `;
        if (checkoutBtn) checkoutBtn.disabled = true;
        if (summarySubtotal) summarySubtotal.textContent = '₹0.00';
        if (summaryTotal) summaryTotal.textContent = '₹0.00';
        if (checkoutForm) checkoutForm.style.opacity = '0.5';
        return;
    }

    if (checkoutBtn) checkoutBtn.disabled = false;
    if (checkoutForm) checkoutForm.style.opacity = '1';

    let html = '';
    let total = 0;

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;

        html += `
            <div class="cart-item">
                <div class="cart-item-details">
                    <img src="${item.image_url}" alt="${item.name}" class="cart-item-img">
                    <div>
                        <h4 class="cart-item-name">${item.name}</h4>
                        <div class="cart-item-price">₹${item.price.toFixed(2)}</div>
                    </div>
                </div>
                <div class="cart-item-actions">
                    <div class="qty-control">
                        <button class="qty-btn" onclick="updateQuantity(${item.product_id}, -1)">-</button>
                        <span class="qty-val">${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQuantity(${item.product_id}, 1)">+</button>
                    </div>
                    <button class="remove-btn" onclick="removeFromCart(${item.product_id})" title="Remove item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                    </button>
                </div>
            </div>
        `;
    });

    cartItemsContainer.innerHTML = html;
    if (summarySubtotal) summarySubtotal.textContent = `₹${total.toFixed(2)}`;
    if (summaryTotal) summaryTotal.textContent = `₹${total.toFixed(2)}`;

    // Set value of hidden input for backend tracking
    if (orderItemsInput) {
        // format is simplified list for FastAPI: [{"product_id": X, "quantity": Y}, ...]
        const simplified = cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity
        }));
        orderItemsInput.value = JSON.stringify(simplified);
    }
}

// Intercept checkout form submission to submit via FastAPI
async function handleCheckout(event) {
    event.preventDefault();

    const cart = getCart();
    if (cart.length === 0) {
        showToast("Your cart is empty!", "error");
        return;
    }

    const form = event.target;
    const formData = new FormData(form);

    const items = cart.map(item => ({
        product_id: parseInt(item.product_id),
        quantity: parseInt(item.quantity)
    }));

    //mmm

    const orderData = {
        user_id: formData.get('user_id') ? parseInt(formData.get('user_id')) : null,
        customer_name: formData.get('customer_name'),
        customer_email: formData.get('customer_email'),
        customer_phone: formData.get('customer_phone'),
        department: formData.get('department'),
        roll_number: formData.get('roll_number'),
        delivery_address: formData.get('delivery_address'),
        items: items
    };

    const submitBtn = document.getElementById('checkout-btn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing Order...';
    }

    try {
        const response = await fetch(`${API_BASE_URL}/orders`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });

        if (!response.ok) {
            const errData = await response.json();
            throw new Error(errData.detail || 'Failed to place order');
        }

        const orderResult = await response.json();
        
        // Clear local cart
        clearCart();

        // Redirect to confirmation page with receipt details
        window.location.href = `cart.php?success=1&order_id=${orderResult.id}&name=${encodeURIComponent(orderResult.customer_name)}&total=${orderResult.total_amount}&date=${encodeURIComponent(orderResult.created_at)}`;

    } catch (error) {
        showToast(error.message, 'error');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Place Order';
        }
    }
}

// Delete product handler for Admin Panel
async function deleteProduct(productId, adminToken) {
    if (!confirm("Are you sure you want to delete this product from the catalog?")) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL}/products/${productId}`, {
            method: 'DELETE',
            headers: {
                'x-admin-token': adminToken
            }
        });

        if (!response.ok) {
            const errData = await response.json();
            throw new Error(errData.detail || 'Failed to delete product');
        }

        showToast("Product deleted successfully", "success");
        // Reload admin page after short delay
        setTimeout(() => window.location.reload(), 1000);

    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Add Product handler for Admin Panel
async function handleAddProduct(event, adminToken) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    const productData = {
        name: formData.get('name'),
        price: parseFloat(formData.get('price')),
        category: formData.get('category'),
        description: formData.get('description'),
        image_url: formData.get('image_url') || null
    };

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    try {
        const response = await fetch(`${API_BASE_URL}/products`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-admin-token': adminToken
            },
            body: JSON.stringify(productData)
        });

        if (!response.ok) {
            const errData = await response.json();
            throw new Error(errData.detail || 'Failed to add product');
        }

        showToast("Product added successfully!", "success");
        form.reset();
        
        // Reload admin page after short delay
        setTimeout(() => window.location.reload(), 1000);

    } catch (error) {
        showToast(error.message, 'error');
        if (submitBtn) submitBtn.disabled = false;
    }
}

// On Page Load Initialization
document.addEventListener('DOMContentLoaded', () => {
    updateCartBadge();
    
    if (window.location.pathname.includes('cart.php')) {
        renderCartPage();
        
        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', handleCheckout);
        }
    }
});
