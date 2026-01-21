<?php
session_start();
include 'db_connection.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ? AND email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;
                header("Location: profile.php");
                exit();
            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            $errors[] = "User not found.";
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
    margin-top: 4px;
    display: none;
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
    right: 12px;
    top: 58%;
    transform: translateY(-50%);
    color: #666;
    font-size: 17px;
    pointer-events: none;
}

.form-control {
    padding-right: 40px !important;
}
</style>

<main>
    <section class="section">
        <div class="container">
            <div class="row justify-center">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
                        </div>

                        <div class="card-body">

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul>
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form action="" method="POST" id="loginForm">

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

                                <!-- Password -->
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" id="password" name="password" class="form-control"
                                           placeholder="Enter your password">
                                    <i class="fas fa-lock form-icon"></i>
                                    <span id="passwordError" class="error-msg"></span>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" id="remember" name="remember" class="form-check-input">
                                    <label for="remember" class="form-check-label">Remember Me</label>
                                </div>

                                <div class="form-group">
                                    <a href="Forget_Password.php" class="forgot-password">Forgot Password?</a>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">Login</button>

                                <div class="text-center mt-3">
                                    <p>Don't have an account? <a href="registration.php">Register</a></p>
                                </div>

                            </form>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!--login form validatio -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("loginForm");

    const username = document.getElementById("username");
    const email = document.getElementById("email");
    const password = document.getElementById("password");

    const uErr = document.getElementById("usernameError");
    const eErr = document.getElementById("emailError");
    const pErr = document.getElementById("passwordError");

    function resetErrors() {
        [uErr, eErr, pErr].forEach(err => {
            err.textContent = "";
            err.style.display = "none";
        });

        [username, email, password].forEach(inp => {
            inp.classList.remove("input-error", "input-success");
        });
    }

    // ✅ Real-time validation
    username.addEventListener("input", function () {
        if (username.value.trim() !== "") {
            username.classList.add("input-success");
            username.classList.remove("input-error");
            uErr.style.display = "none";
        }
    });

    email.addEventListener("input", function () {
        const pat = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (pat.test(email.value.trim())) {
            email.classList.add("input-success");
            email.classList.remove("input-error");
            eErr.style.display = "none";
        }
    });

    password.addEventListener("input", function () {
        if (password.value.trim() !== "") {
            password.classList.add("input-success");
            password.classList.remove("input-error");
            pErr.style.display = "none";
        }
    });

    // ✅ Final submit validation
    form.addEventListener("submit", function (e) {

        resetErrors();
        let valid = true;

        if (username.value.trim() === "") {
            uErr.textContent = "Username is required.";
            uErr.style.display = "block";
            username.classList.add("input-error");
            valid = false;
        }

        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!pattern.test(email.value.trim())) {
            eErr.textContent = "Enter valid email.";
            eErr.style.display = "block";
            email.classList.add("input-error");
            valid = false;
        }

        if (password.value.trim() === "") {
            pErr.textContent = "Password is required.";
            pErr.style.display = "block";
            password.classList.add("input-error");
            valid = false;
        }

        if (!valid) e.preventDefault();
    });

});
</script>

<?php include 'footer.php'; ?>
</body>
</html>
