<?php
include 'header.php';

// Redirect if already logged in
if ($is_logged_in) {
    header("Location: index.php");
    exit();
}

$error_msg = null;
$name = '';
$email = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        // Send request to FastAPI backend
        $api_url = "http://localhost:8000/api/register";
        $data = [
            'name' => $name,
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
                    'ignore_errors' => true
                ]
            ];
            $context  = stream_context_create($options);
            $response = @file_get_contents($api_url, false, $context);

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
                    // Registration successful! Log user in automatically
                    $_SESSION['user'] = [
                        'id' => $result['id'],
                        'name' => $result['name'],
                        'email' => $result['email'],
                        'is_admin' => $result['is_admin']
                    ];
                    if ($result['is_admin']) {
                        $_SESSION['admin_token'] = "admin_secret_token_cusat";
                    }
                    
                    header("Location: index.php");
                    exit();
                } else {
                    $error_msg = isset($result['detail']) ? $result['detail'] : "Email registration failed. It may be already in use.";
                }
            }
        } catch (Exception $e) {
            $error_msg = "An error occurred during registration.";
        }
    }
}
?>

<main class="container">
    <div class="auth-wrapper">
        <div class="auth-header">
            <h2>Join CUSAT Store</h2>
            <p>Create an account to order CUSAT items</p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background-color: #fee2e2; border-left: 4px solid var(--error); color: #991b1b; padding: 12px; border-radius: var(--radius-sm); font-size: 14px; margin-bottom: 20px;">
                <strong>Error: </strong> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <div style="background-color: rgba(13, 148, 136, 0.05); border-left: 4px solid var(--teal); padding: 12px; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 20px; color: var(--teal-hover);">
            <strong>Tip:</strong> Register with <strong>admin@cusat.ac.in</strong> to automatically get Merchant/Admin Panel privileges!
        </div>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-input" placeholder="e.g. Athulya Ram" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">CUSAT Email Address</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="e.g. student@cusat.ac.in" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Min. 6 characters" required>
            </div>

            <button type="submit" class="btn btn-teal" style="width: 100%; margin-top: 10px; padding: 12px;">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign In</a>
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
