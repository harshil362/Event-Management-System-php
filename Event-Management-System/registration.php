<?php
include 'db_connection.php';

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Server-side validation
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Username or email already exists.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $phone, $hashed_password);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}

include 'header.php';
?>

<style>
.error-msg {
    color: red;
    font-size: 14px;
    display: none;
    margin-top: 5px;
}

.input-error {
    border: 2px solid red !important;
}

.input-success {
    border: 2px solid green !important;
}

.form-group {
    position: relative;
}

.form-icon {
    position: absolute;
    right: 15px;
    top: 58%;
    transform: translateY(-50%);
    color: #666;
    font-size: 17px;
    pointer-events: none;
}
.form-control{
    padding-right: 45px !important;
}
</style>

<main>
    <section class="section">
        <div class="container">
            <div class="row justify-center">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-user-plus"></i> Registration</h2>
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

                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    Registration successful! <a href="login.php">Login here</a>
                                </div>
                            <?php endif; ?>

                            <form action="" method="POST" id="registrationForm">

                                <!-- Username -->
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" id="username" name="username" class="form-control"
                                           placeholder="Enter your username"
                                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                                    <i class="fas fa-user form-icon"></i>
                                    <span id="usernameError" class="error-msg"></span>
                                </div>

                                <!-- Email -->
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                           placeholder="Enter your email"
                                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                    <i class="fas fa-envelope form-icon"></i>
                                    <span id="emailError" class="error-msg"></span>
                                </div>

                                <!-- Phone -->
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="tel" id="phone" name="phone" maxlength="10" class="form-control"
                                           placeholder="Enter your phone number"
                                           value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                                    <i class="fas fa-phone form-icon"></i>
                                    <span id="phoneError" class="error-msg"></span>
                                </div>

                                <!-- Password -->
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" id="password" name="password" class="form-control"
                                           placeholder="Enter your password">
                                    <i class="fas fa-lock form-icon"></i>
                                    <span id="passwordError" class="error-msg"></span>
                                </div>

                                <!-- Confirm Password -->
                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <input type="password" id="confirm-password" name="confirm-password" class="form-control"
                                           placeholder="Confirm your password">
                                    <i class="fas fa-lock form-icon"></i>
                                    <span id="confirmError" class="error-msg"></span>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">Register</button>

                                <div class="text-center mt-3">
                                    <p>Already have an account? <a href="login.php" class="auth-link">Login</a></p>
                                </div>
                            </form>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>


<!--register form validatio -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("registrationForm");

    const username = document.getElementById("username");
    const email = document.getElementById("email");
    const phone = document.getElementById("phone");
    const password = document.getElementById("password");
    const confirm = document.getElementById("confirm-password");

    const uErr = document.getElementById("usernameError");
    const eErr = document.getElementById("emailError");
    const pErr = document.getElementById("phoneError");
    const passErr = document.getElementById("passwordError");
    const cErr = document.getElementById("confirmError");

    function resetErrors() {
        [uErr, eErr, pErr, passErr, cErr].forEach(err => {
            err.style.display = "none";
            err.textContent = "";
        });

        [username, email, phone, password, confirm].forEach(inp => {
            inp.classList.remove("input-error", "input-success");
        });
    }

    /* ✅ Real-time validation */

    username.addEventListener("input", function () {
        if (username.value.trim().length >= 3) {
            username.classList.add("input-success");
            username.classList.remove("input-error");
            uErr.style.display = "none";
        }
    });

    email.addEventListener("input", function () {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (pattern.test(email.value.trim())) {
            email.classList.add("input-success");
            email.classList.remove("input-error");
            eErr.style.display = "none";
        }
    });

    phone.addEventListener("input", function () {
        phone.value = phone.value.replace(/[^0-9]/g, "");
        if (phone.value.length > 10) phone.value = phone.value.slice(0, 10);

        if (/^[0-9]{10}$/.test(phone.value)) {
            phone.classList.add("input-success");
            phone.classList.remove("input-error");
            pErr.style.display = "none";
        }
    });

    password.addEventListener("input", function () {
        if (password.value.length >= 6) {
            password.classList.add("input-success");
            password.classList.remove("input-error");
            passErr.style.display = "none";
        }
    });

    confirm.addEventListener("input", function () {
        if (confirm.value.trim() === password.value.trim()) {
            confirm.classList.add("input-success");
            confirm.classList.remove("input-error");
            cErr.style.display = "none";
        }
    });

    /* ✅ Submit Validation */
    form.addEventListener("submit", function(e){

        resetErrors();
        let valid = true;

        if (username.value.trim().length < 3) {
            uErr.textContent = "Username must be at least 3 characters.";
            uErr.style.display = "block";
            username.classList.add("input-error");
            valid = false;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email.value.trim())) {
            eErr.textContent = "Enter valid email.";
            eErr.style.display = "block";
            email.classList.add("input-error");
            valid = false;
        }

        if (!/^[0-9]{10}$/.test(phone.value.trim())) {
            pErr.textContent = "Phone number must be exactly 10 digits.";
            pErr.style.display = "block";
            phone.classList.add("input-error");
            valid = false;
        }

        if (password.value.length < 6) {
            passErr.textContent = "Password must be at least 6 characters.";
            passErr.style.display = "block";
            password.classList.add("input-error");
            valid = false;
        }

        if (password.value !== confirm.value.trim()) {
            cErr.textContent = "Passwords do not match.";
            cErr.style.display = "block";
            confirm.classList.add("input-error");
            valid = false;
        }

        if (!valid) e.preventDefault();
        
    });

});
</script>

<?php include 'footer.php'; ?>
</body>
</html>
