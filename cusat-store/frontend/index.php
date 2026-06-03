<?php
include 'header.php';

// Setup API URL
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'All';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$api_url = "http://localhost:8000/api/products";
if ($selected_category != 'All') {
    $api_url .= "?category=" . urlencode($selected_category);
}

$products = [];
$backend_offline = false;

// Attempt to fetch products from FastAPI
try {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 2.0 // 2 seconds timeout
        ]
    ]);
    $response = @file_get_contents($api_url, false, $ctx);
    if ($response === FALSE) {
        $backend_offline = true;
    } else {
        $products = json_decode($response, true);
        
        // Filter by search query if set
        if (!empty($search_query)) {
            $filtered = [];
            foreach ($products as $p) {
                if (stristr($p['name'], $search_query) !== FALSE || 
                    stristr($p['description'], $search_query) !== FALSE) {
                    $filtered[] = $p;
                }
            }
            $products = $filtered;
        }
    }
} catch (Exception $e) {
    $backend_offline = true;
}

$categories = ['All', 'Apparel', 'Textbooks', 'Tech', 'Stationery'];
?>

<main class="container">
    
    <!-- Hero Banner Section -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-tag">Official Store</span>
            <h1 class="hero-title">Wear Your Pride, Learn in Style</h1>
            <p class="hero-subtitle">Get official Cochin University merchandise, textbooks, stationery, and lab essentials. Designed for CUSATians, by CUSATians.</p>
            <a href="#store-section" class="btn btn-teal">Shop Collection</a>
        </div>
    </section>

    <!-- Store Controls Section -->
    <section id="store-section" class="store-controls">
        <!-- Categories tabs -->
        <div class="categories">
            <?php foreach ($categories as $cat): ?>
                <a href="index.php?category=<?php echo urlencode($cat); ?>&search=<?php echo urlencode($search_query); ?>#store-section" 
                   class="category-tab <?php echo ($selected_category == $cat) ? 'active' : ''; ?>">
                    <?php echo $cat; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="index.php#store-section" class="search-bar">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
        </form>
    </section>

    <!-- Error state: Backend Offline -->
    <?php if ($backend_offline): ?>
        <div class="admin-card" style="border-left: 5px solid var(--error); margin: 40px 0; text-align: center; padding: 40px;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="#ef4444" style="margin-bottom:16px;">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
            <h3 style="font-size: 22px; margin-bottom: 10px;">FastAPI Backend is Offline</h3>
            <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto 20px auto;">
                CUSAT Store requires the FastAPI backend to load products and handle checkouts. Please make sure the backend server is running locally on port 8000.
            </p>
            <code style="background-color: var(--bg-light); padding: 8px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); display: inline-block;">
                uvicorn main:app --reload --port 8000
            </code>
        </div>
    <?php endif; ?>

    <!-- Catalog Content -->
    <?php if (!$backend_offline): ?>
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <h3>No Products Found</h3>
                <p>We couldn't find any products matching your selection.</p>
                <a href="index.php" class="btn btn-secondary">Clear Filters</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $prod): ?>
                    <article class="product-card">
                        <div class="product-img-wrapper">
                            <span class="product-badge"><?php echo htmlspecialchars($prod['category']); ?></span>
                            <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
                            <p class="product-desc"><?php echo htmlspecialchars($prod['description']); ?></p>
                            <div class="product-footer">
                                <span class="product-price">₹<?php echo number_format($prod['price'], 2); ?></span>
                                <button class="btn btn-primary" onclick="addToCart(<?php echo $prod['id']; ?>, '<?php echo addslashes($prod['name']); ?>', <?php echo $prod['price']; ?>, '<?php echo addslashes($prod['image_url']); ?>')">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </article>
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
