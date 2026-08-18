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

$remembered_username = "";

if (isset($_COOKIE['user'])) {
    $remembered_username = $_COOKIE['user'];
}

if (isset($_POST['username'])) {

    $username = mysqli_real_escape_string(
        $con,
        stripslashes($_POST['username'])
    );

    $password = mysqli_real_escape_string(
        $con,
        stripslashes($_POST['password'])
    );

    $query = "SELECT *
              FROM users
              WHERE username='$username'
              AND password='" . md5($password) . "'";

    $result = mysqli_query($con, $query);

    if (!$result) {
        die("Database query failed: " . mysqli_error($con));
    }

    $rows = mysqli_num_rows($result);

    if ($rows == 1) {

        $userData = mysqli_fetch_assoc($result);

        $_SESSION['username'] = $userData['username'];
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['role'] = $userData['role'];
        $_SESSION['last_activity'] = time();

        // Record the user's latest successful login time.
        $login_id = (int)$userData['id'];

        $login_stmt = mysqli_prepare(
            $con,
            "UPDATE users SET last_login = NOW() WHERE id = ?"
        );

        if ($login_stmt) {
            mysqli_stmt_bind_param($login_stmt, "i", $login_id);
            mysqli_stmt_execute($login_stmt);
            mysqli_stmt_close($login_stmt);
        }

        if (isset($_POST['remember_me'])) {

            setcookie(
                "user",
                $username,
                time() + (60 * 60 * 24 * 30),
                "/"
            );
        }

        if ($userData['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }

        exit();
    } else {

        $message = "
        <div class='alert alert-danger'>
            Username or password is incorrect.
        </div>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Student Routine Organizer - Login</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
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
</style>

    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="row w-100">
                <div class="content-wrapper full-page-wrapper d-flex align-items-center auth login-bg">
                    <div class="card col-lg-4 mx-auto">
                        <div class="card-body px-5 py-5">
                            <h3 class="card-title text-start mb-3">Login</h3>
                            <?php echo $message; ?>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text"
                                        name="username"
                                        class="form-control p_input"
                                        value="<?php echo $remembered_username; ?>"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" name="password" class="form-control p_input" required>
                                </div>
                                <div class="form-group d-flex align-items-center justify-content-between">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="remember_me"> Remember me </label>
                                    </div>
                                    <a href="forgot_pass.php" class="forgot-pass">Forgot password</a>
                                </div>
                                <div class="text-center d-grid gap-2">
                                    <button type="submit" name="submit" class="btn btn-primary btn-block enter-btn">Login</button>
                                </div>

                                <p class="sign-up">
                                    Don't have an Account?
                                    <a href="registration.php">Sign Up</a>
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
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
    <!-- endinject -->
</body>

</html>