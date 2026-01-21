<?php
include 'db_connection.php';

$message = '';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Server-side validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }

    if (empty($errors)) {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // Since no email setup, show on-screen notification
            $message = "Password reset link sent to your email. (Note: In a real application, an email would be sent here.)";
        } else {
            $errors[] = "Email not found.";
        }
        $stmt->close();
    }
}

include 'header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <div class="row justify-center">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-key"></i> Forgot Password</h2>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul>
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ($message): ?>
                                <div class="alert alert-success">
                                    <?php echo htmlspecialchars($message); ?>
                                </div>
                            <?php endif; ?>
                            <form action="" method="POST" id="forgotPasswordForm">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" required class="form-control" placeholder="Enter your email address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    <i class="fas fa-envelope form-icon"></i>
                                </div>

                                <div class="form-group">
                                    <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>

                                <div class="text-center mt-3">
                                    <p>Remember your password? <a href="login.php" class="auth-link">Login</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value.trim();

    if (email === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        e.preventDefault();
        alert('Valid email is required.');
    }
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>
