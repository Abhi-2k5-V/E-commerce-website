<?php
include 'header.php';

// Redirect if already logged in
if ($is_logged_in) {
    header("Location: index.php");
    exit();
}

$error_msg = null;
$email = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        // Submit details to FastAPI backend
        $api_url = "http://localhost:8000/api/login";
        $data = [
            'email' => $email,
            'password' => $password
        ];

        try {
            $options = [
                'http' => [
                    'header'  => "Content-type: application/json\r\n",
                    'method'  => 'POST',
                    'content' => json_encode($data),
                    'timeout' => 3.0,
                    'ignore_errors' => true // allows reading body on error status codes
                ]
            ];
            $context  = stream_context_create($options);
            $response = @file_get_contents($api_url, false, $context);
            
            // Extract HTTP status code
            if (isset($http_response_header)) {
                preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
                $status_code = intval($match[1]);
            } else {
                $status_code = 500;
            }

            if ($response === FALSE) {
                $error_msg = "Cannot connect to the store database backend. Please check that FastAPI is running.";
            } else {
                $result = json_decode($response, true);
                if ($status_code == 200) {
                    // Login successful! Save to session
                    $_SESSION['user'] = [
                        'id' => $result['id'],
                        'name' => $result['name'],
                        'email' => $result['email'],
                        'is_admin' => $result['is_admin']
                    ];
                    // Also save an admin token for API queries if admin
                    if ($result['is_admin']) {
                        $_SESSION['admin_token'] = "admin_secret_token_cusat";
                    }
                    
                    header("Location: index.php");
                    exit();
                } else {
                    $error_msg = isset($result['detail']) ? $result['detail'] : "Invalid email or password.";
                }
            }
        } catch (Exception $e) {
            $error_msg = "An error occurred during authentication.";
        }
    }
}
?>

<main class="container">
    <div class="auth-wrapper">
        <div class="auth-header">
            <h2>Welcome Back</h2>
            <p>Login to your CUSAT Store account</p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background-color: #fee2e2; border-left: 4px solid var(--error); color: #991b1b; padding: 12px; border-radius: var(--radius-sm); font-size: 14px; margin-bottom: 20px;">
                <strong>Error: </strong> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email" class="form-label">CUSAT Email Address</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="e.g. name@cusat.ac.in" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; padding: 12px;">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Register here</a>
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
