<?php 
session_start();

if (isset($_SESSION['user_id'])) { 
 
    if ($_SESSION['role'] == 'admin') { 
        header("Location: admin_dashboard.php"); 
    } else { 
        header("Location: user_dashboard.php"); 
    } 
 
    exit(); 
} 

require('database.php'); 
 
$message = ""; 
$message_type = ""; 
 
if (isset($_POST['submit'])) { 
 
    $username = mysqli_real_escape_string(
        $con,
        stripslashes($_POST['username'])
    );

    $full_name = mysqli_real_escape_string(
        $con,
        stripslashes($_POST['full_name'])
    );

    $email = mysqli_real_escape_string(
        $con,
        stripslashes($_POST['email'])
    );

    $password = mysqli_real_escape_string(
        $con,
        stripslashes($_POST['password'])
    );

    $role = "student"; 
 
    // Password requirements
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
 
    // Check if username and email already exist
    $check = mysqli_query(
        $con,
        "SELECT * FROM users WHERE username='$username'"
    );

    $checkEmail = mysqli_query(
        $con,
        "SELECT * FROM users WHERE email='$email'"
    );
 
    if ($password_error != "") { 
 
        $message = $password_error;
        $message_type = "danger";

    } elseif (mysqli_num_rows($check) > 0) { 
 
        $message = "Username already exists.";
        $message_type = "danger";

    } elseif (mysqli_num_rows($checkEmail) > 0) { 
 
        $message = "Email already registered.";
        $message_type = "danger";

    } else { 
 
        $query = "INSERT INTO users(role, username, full_name, password, email) 
                  VALUES(
                      '$role',
                      '$username',
                      '$full_name',
                      '" . md5($password) . "',
                      '$email'
                  )"; 
 
        if (mysqli_query($con, $query)) { 
 
            $message = "Registration successful! You can now login.";
            $message_type = "success";

        } else { 
 
            $message = "Registration failed.";
            $message_type = "danger";
        } 
    } 
} 
?> 
 
<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Student Routine Organizer - Register</title>

    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">

    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">

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

        /* Form spacing */
        .registration-form .form-group {
            margin-bottom: 15px;
        }

        /* Password requirements */
        .password-requirements {
            margin-top: 5px;
            line-height: 1.5;
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

        .registration-form .register-button-area {
            margin-top: 5px;
        }

        .registration-login {
            margin-top: 15px !important;
            margin-bottom: 0 !important;
        }

        /* =================================
           MESSAGE OVERLAY
        ================================= */

        .registration-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.65);
            z-index: 9998;
        }

        /* =================================
           MESSAGE BOX
        ================================= */

        .registration-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 380px;
            max-width: calc(100% - 40px);
            z-index: 9999;
        }

        .registration-message .alert {
            margin: 0;
            padding: 18px 45px 18px 20px;
            position: relative;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.4);
        }

        .registration-message .close-message {
            position: absolute;
            top: 7px;
            right: 10px;
            border: none;
            background: transparent;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
            padding: 0;
            opacity: 0.7;
        }

        .registration-message .close-message:hover {
            opacity: 1;
        }

        .registration-message .login-link {
            display: inline-block;
            margin-top: 6px;
        }

    </style>

    <link rel="shortcut icon" href="assets/images/favicon.png" />

</head>

<body>

    <?php if ($message != "") { ?>

        <!-- Dark overlay -->
        <div
            class="registration-overlay"
            id="registrationOverlay">
        </div>

        <!-- Message -->
        <div
            class="registration-message"
            id="registrationMessage">

            <div class="alert alert-<?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

                <?php if ($message_type == "success") { ?>

                    <br>

                    <a
                        href="login.php"
                        class="text-success login-link">

                        Click here to Login

                    </a>

                <?php } ?>

                <button
                    type="button"
                    class="close-message"
                    onclick="closeMessage()"
                    aria-label="Close">

                    &times;

                </button>

            </div>

        </div>

    <?php } ?>

    <div class="container-scroller">

        <div class="container-fluid page-body-wrapper full-page-wrapper">

            <div class="row w-100">

                <div class="content-wrapper full-page-wrapper d-flex align-items-center auth login-bg">

                    <div class="card col-lg-4 mx-auto">

                        <div class="card-body px-5 py-5">

                            <h2 class="text-center mb-2">
                                Student Routine Organizer
                            </h2>

                            <p class="text-muted text-center mb-4">
                                Create your account
                            </p>

                            <form
                                method="POST"
                                action=""
                                id="registerForm"
                                class="registration-form">

                                <div class="form-group">

                                    <label>Username</label>

                                    <input
                                        type="text"
                                        name="username"
                                        class="form-control p_input"
                                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                        required>

                                </div>

                                <div class="form-group">

                                    <label>Full Name</label>

                                    <input
                                        type="text"
                                        name="full_name"
                                        class="form-control p_input"
                                        value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                        required>

                                </div>

                                <div class="form-group">

                                    <label>Email</label>

                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control p_input"
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                        required>

                                </div>

                                <div class="form-group">

                                    <label>Password</label>

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

                                <div class="text-center register-button-area">

                                    <button
                                        type="submit"
                                        name="submit"
                                        id="registerButton"
                                        class="btn btn-primary btn-block enter-btn"
                                        disabled>

                                        Register

                                    </button>

                                </div>

                                <p class="sign-up registration-login">

                                    Already have an account?

                                    <a href="login.php">
                                        Login
                                    </a>

                                </p>

                            </form>

                        </div>

                    </div>

                </div>

                <!-- content-wrapper ends -->

            </div>

            <!-- row ends -->

        </div>

        <!-- page-body-wrapper ends -->

    </div>

    <!-- container-scroller -->

    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>

    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>

    <script>

        const form = document.getElementById("registerForm");
        const password = document.getElementById("password");
        const registerButton = document.getElementById("registerButton");

        const lengthRequirement =
            document.getElementById("lengthRequirement");

        const uppercaseRequirement =
            document.getElementById("uppercaseRequirement");

        const lowercaseRequirement =
            document.getElementById("lowercaseRequirement");

        const numberRequirement =
            document.getElementById("numberRequirement");

        const specialRequirement =
            document.getElementById("specialRequirement");


        function updateRequirement(element, valid) {

            const icon =
                element.querySelector(".requirement-icon");

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

            const hasLength =
                value.length >= 8;

            const hasUppercase =
                /[A-Z]/.test(value);

            const hasLowercase =
                /[a-z]/.test(value);

            const hasNumber =
                /[0-9]/.test(value);

            const hasSpecial =
                /[\W_]/.test(value);


            updateRequirement(
                lengthRequirement,
                hasLength
            );

            updateRequirement(
                uppercaseRequirement,
                hasUppercase
            );

            updateRequirement(
                lowercaseRequirement,
                hasLowercase
            );

            updateRequirement(
                numberRequirement,
                hasNumber
            );

            updateRequirement(
                specialRequirement,
                hasSpecial
            );


            if (
                hasLength &&
                hasUppercase &&
                hasLowercase &&
                hasNumber &&
                hasSpecial
            ) {

                registerButton.disabled = false;

            } else {

                registerButton.disabled = true;

            }

        }


        password.addEventListener(
            "input",
            checkPassword
        );


        form.addEventListener(
            "submit",
            function(event) {

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

            }
        );


        function closeMessage() {

            const message =
                document.getElementById("registrationMessage");

            const overlay =
                document.getElementById("registrationOverlay");

            if (message) {
                message.style.display = "none";
            }

            if (overlay) {
                overlay.style.display = "none";
            }

        }


        checkPassword();

    </script>

</body>

</html>