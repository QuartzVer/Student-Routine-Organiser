<?php
require('database.php');

$message = "";
$reset_link = "";
$show_reset_form = false;
$token = "";

/* =========================================
   RESET PASSWORD USING TOKEN
   ========================================= */

if (isset($_GET['token'])) {

    $token = mysqli_real_escape_string($con, $_GET['token']);

    $query = "SELECT email, created_at
              FROM password_resets
              WHERE token='$token'
              LIMIT 1";

    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) == 1) {

        $reset = mysqli_fetch_assoc($result);

        // Token valid for 30 minutes
        $created_time = strtotime($reset['created_at']);

        if ((time() - $created_time) <= 1800) {

            $show_reset_form = true;
        } else {

            mysqli_query(
                $con,
                "DELETE FROM password_resets WHERE token='$token'"
            );

            $message = "
            <div class='alert alert-danger'>
                This reset link has expired.
            </div>";
        }
    } else {

        $message = "
        <div class='alert alert-danger'>
            Invalid reset link.
        </div>";
    }
}


/* =========================================
   UPDATE PASSWORD
   ========================================= */

if (isset($_POST['reset_password'])) {

    $token = mysqli_real_escape_string(
        $con,
        $_POST['token']
    );

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $password_error = "";

    if (strlen($password) < 8) {
        $password_error = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $password_error = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $password_error = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $password_error = "Password must contain at least one number.";
    } elseif (!preg_match('/[\W_]/', $password)) {
        $password_error = "Password must contain at least one special character (e.g. %, !, @, #).";
    }

    if ($password_error != "") {

        $message = "
    <div class='alert alert-danger'>
        $password_error
    </div>";

        $show_reset_form = true;
    } elseif ($password !== $confirm_password) {

        $message = "
        <div class='alert alert-danger'>
            Passwords do not match.
        </div>";

        $show_reset_form = true;
    } else {

        // Get email associated with token
        $query = "SELECT email, created_at
                  FROM password_resets
                  WHERE token='$token'
                  LIMIT 1";

        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) == 1) {

            $reset = mysqli_fetch_assoc($result);

            // Check token expiry
            $created_time = strtotime($reset['created_at']);

            if ((time() - $created_time) <= 1800) {

                $email = mysqli_real_escape_string(
                    $con,
                    $reset['email']
                );

                // Your existing login system uses MD5
                $hashed_password = md5($password);

                $update = "UPDATE users
                           SET password='$hashed_password'
                           WHERE email='$email'";

                if (mysqli_query($con, $update)) {

                    // Delete token after successful reset
                    mysqli_query(
                        $con,
                        "DELETE FROM password_resets
                         WHERE token='$token'"
                    );

                    $message = "
                    <div class='alert alert-success'>
                        Password reset successfully!
                        <br><br>
                        <a href='login.php'>
                            Click here to login
                        </a>
                    </div>";

                    $show_reset_form = false;
                } else {

                    $message = "
                    <div class='alert alert-danger'>
                        Failed to update password.
                    </div>";

                    $show_reset_form = true;
                }
            } else {

                mysqli_query(
                    $con,
                    "DELETE FROM password_resets
                     WHERE token='$token'"
                );

                $message = "
                <div class='alert alert-danger'>
                    This reset link has expired.
                </div>";
            }
        } else {

            $message = "
            <div class='alert alert-danger'>
                Invalid reset link.
            </div>";
        }
    }
}


/* =========================================
   GENERATE RESET TOKEN
   ========================================= */

if (isset($_POST['send_reset'])) {

    $email = mysqli_real_escape_string(
        $con,
        trim($_POST['email'])
    );

    // Check email
    $query = "SELECT id
              FROM users
              WHERE email='$email'
              LIMIT 1";

    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) == 1) {

        // Generate secure token
        $token = bin2hex(random_bytes(32));

        // Remove previous token
        mysqli_query(
            $con,
            "DELETE FROM password_resets
             WHERE email='$email'"
        );

        // Insert new token
        $insert = "INSERT INTO password_resets
                   (email, token)
                   VALUES ('$email', '$token')";

        if (mysqli_query($con, $insert)) {

            /*
             * For XAMPP/local development:
             * display the reset link directly.
             *
             * Later, you can replace this with email sending.
             */

            $reset_link = "forgot_pass.php?token=" . $token;

            $message = "
            <div class='alert alert-success'>
                Reset link generated successfully.
            </div>";
        } else {

            $message = "
            <div class='alert alert-danger'>
                Unable to generate reset link.
            </div>";
        }
    } else {

        $message = "
        <div class='alert alert-danger'>
            No account found with this email address.
        </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="shortcut icon" href="icon.png" />

    <meta charset="utf-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Forgot Password</title>

    <link rel="stylesheet"
        href="assets/vendors/mdi/css/materialdesignicons.min.css">

    <link rel="stylesheet"
        href="assets/vendors/ti-icons/css/themify-icons.css">

    <link rel="stylesheet"
        href="assets/vendors/css/vendor.bundle.base.css">

    <link rel="stylesheet"
        href="assets/vendors/font-awesome/css/font-awesome.min.css">

    <link rel="stylesheet"
        href="assets/css/style.css">
    <style>
        html,
        body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background: #000000;
        }

        .container-scroller {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .page-body-wrapper.full-page-wrapper {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .row.w-100 {
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .content-wrapper.full-page-wrapper {
            width: 100% !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0;
        }

        .auth.login-bg {
            min-height: 100vh !important;
            background-size: cover;
            background-position: center;
        }

        .password-requirements {
            margin-top: 8px;
            line-height: 1.8;
        }

        .password-requirements div {
            color: #dc3545;
        }

        .password-requirements div.valid {
            color: #28a745;
        }

        .requirement-icon {
            display: inline-block;
            width: 20px;
            font-weight: bold;
        }
    </style>


</head>

<body>

    <div class="container-scroller">

        <div class="container-fluid page-body-wrapper full-page-wrapper">

            <div class="row w-100">

                <div class="content-wrapper full-page-wrapper
                        d-flex align-items-center auth login-bg">

                    <div class="card col-lg-4 mx-auto">

                        <div class="card-body px-5 py-5">

                            <?php if ($show_reset_form) { ?>

                                <!-- ==========================
                                 RESET PASSWORD FORM
                                 ========================== -->

                                <h3 class="card-title mb-3">
                                    Reset Password
                                </h3>

                                <?php echo $message; ?>

                                <form method="POST" id="resetPasswordForm">

                                    <input
                                        type="hidden"
                                        name="token"
                                        value="<?php echo htmlspecialchars($token); ?>">

                                    <div class="form-group">

                                        <label>New Password</label>

                                        <input
                                            type="password"
                                            name="password"
                                            id="password"
                                            class="form-control p_input"
                                            minlength="8"
                                            required>

                                        <div class="password-requirements">

                                            <div id="lengthRequirement">
                                                <span class="requirement-icon">✗</span>
                                                At least 8 characters
                                            </div>

                                            <div id="uppercaseRequirement">
                                                <span class="requirement-icon">✗</span>
                                                At least one uppercase letter (A-Z)
                                            </div>

                                            <div id="lowercaseRequirement">
                                                <span class="requirement-icon">✗</span>
                                                At least one lowercase letter (a-z)
                                            </div>

                                            <div id="numberRequirement">
                                                <span class="requirement-icon">✗</span>
                                                At least one number (0-9)
                                            </div>

                                            <div id="specialRequirement">
                                                <span class="requirement-icon">✗</span>
                                                At least one special character (e.g. %, !, @, #)
                                            </div>

                                        </div>

                                    </div>

                                    <div class="form-group">

                                        <label>Confirm Password</label>

                                        <input
                                            type="password"
                                            name="confirm_password"
                                            id="confirmPassword"
                                            class="form-control p_input"
                                            required>

                                    </div>

                                    <div class="text-center d-grid">

                                        <button
                                            type="submit"
                                            name="reset_password"
                                            id="updatePasswordButton"
                                            class="btn btn-primary btn-block enter-btn"
                                            disabled>

                                            Update Password

                                        </button>

                                    </div>

                                </form>

                                <p class="text-center mt-4">

                                    <a href="login.php">
                                        Back to Login
                                    </a>

                                </p>

                            <?php } else { ?>

                                <!-- ==========================
                                 FORGOT PASSWORD FORM
                                 ========================== -->

                                <h3 class="card-title mb-3">
                                    Forgot Password
                                </h3>

                                <p class="text-muted">
                                    Enter your registered email address
                                    to reset your password.
                                </p>

                                <?php echo $message; ?>

                                <form method="POST">

                                    <div class="form-group">

                                        <label>Email</label>

                                        <input
                                            type="email"
                                            name="email"
                                            class="form-control p_input"
                                            placeholder="Enter your email"
                                            required>

                                    </div>

                                    <div class="text-center d-grid">

                                        <button
                                            type="submit"
                                            name="send_reset"
                                            class="btn btn-primary btn-block enter-btn">

                                            Show Reset Link

                                        </button>

                                    </div>

                                </form>

                                <?php if ($reset_link != "") { ?>

                                    <div class="alert alert-info mt-4">

                                        <strong>
                                            Reset Link:
                                        </strong>

                                        <br><br>

                                        <a href="<?php echo htmlspecialchars($reset_link); ?>">

                                            <?php echo htmlspecialchars($reset_link); ?>

                                        </a>

                                    </div>

                                <?php } ?>

                                <p class="text-center mt-4">

                                    <a href="login.php">
                                        Back to Login
                                    </a>

                                </p>

                            <?php } ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="assets/vendors/js/vendor.bundle.base.js"></script>

    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>

    <?php if ($show_reset_form) { ?>

    <script>

        const resetForm = document.getElementById("resetPasswordForm");
        const password = document.getElementById("password");
        const updatePasswordButton = document.getElementById("updatePasswordButton");

        const lengthRequirement = document.getElementById("lengthRequirement");
        const uppercaseRequirement = document.getElementById("uppercaseRequirement");
        const lowercaseRequirement = document.getElementById("lowercaseRequirement");
        const numberRequirement = document.getElementById("numberRequirement");
        const specialRequirement = document.getElementById("specialRequirement");

        function updateRequirement(element, valid) {

            const icon = element.querySelector(".requirement-icon");

            if (valid) {
                element.classList.add("valid");
                icon.textContent = "✓";
            } else {
                element.classList.remove("valid");
                icon.textContent = "✗";
            }
        }

        function checkPassword() {

            const value = password.value;

            const hasLength = value.length >= 8;
            const hasUppercase = /[A-Z]/.test(value);
            const hasLowercase = /[a-z]/.test(value);
            const hasNumber = /[0-9]/.test(value);
            const hasSpecial = /[\W_]/.test(value);

            updateRequirement(lengthRequirement, hasLength);
            updateRequirement(uppercaseRequirement, hasUppercase);
            updateRequirement(lowercaseRequirement, hasLowercase);
            updateRequirement(numberRequirement, hasNumber);
            updateRequirement(specialRequirement, hasSpecial);

            if (
                hasLength &&
                hasUppercase &&
                hasLowercase &&
                hasNumber &&
                hasSpecial
            ) {

                updatePasswordButton.disabled = false;

            } else {

                updatePasswordButton.disabled = true;

            }
        }

        password.addEventListener("input", checkPassword);

        resetForm.addEventListener("submit", function(event) {

            const value = password.value;

            const valid =
                value.length >= 8 &&
                /[A-Z]/.test(value) &&
                /[a-z]/.test(value) &&
                /[0-9]/.test(value) &&
                /[\W_]/.test(value);

            if (!valid) {
                event.preventDefault();
            }

        });

        checkPassword();

    </script>

    <?php } ?>

</body>

</html>