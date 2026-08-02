<?php
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

if (isset($_POST['submit'])) {

    $username = mysqli_real_escape_string($con, stripslashes($_POST['username']));
    $full_name = mysqli_real_escape_string($con, stripslashes($_POST['full_name']));
    $email = mysqli_real_escape_string($con, stripslashes($_POST['email']));
    $password = mysqli_real_escape_string($con, stripslashes($_POST['password']));
    $role = "student";

    // Check if username and email already exists
    $check = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
    $checkEmail = mysqli_query($con, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($check) > 0) {

        $message = "<div class='alert alert-danger'>
                    Username already exists.
                </div>";
    } elseif (mysqli_num_rows($checkEmail) > 0) {

        $message = "<div class='alert alert-danger'>
                    Email already registered.
                </div>";
    } else {

        $query = "INSERT INTO users(role, username, full_name, password, email)
VALUES('$role', '$username', '$full_name', '" . md5($password) . "', '$email')";

        if (mysqli_query($con, $query)) {

            $message = "<div class='alert alert-success'>
                            Registration successful.
                            <br><a href='login.php' class='text-success'>
                            Click here to Login
                            </a>
                        </div>";
        } else {

            $message = "<div class='alert alert-danger'>
                            Registration failed.
                        </div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Student Routine Organizer - Register</title>

    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="shortcut icon" href="assets/images/favicon.png" />

</head>

<body>

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

                            <?php echo $message; ?>

                            <form method="POST" action="">

                                <div class="form-group">

                                    <label>Username</label>

                                    <input
                                        type="text"
                                        name="username"
                                        class="form-control p_input"
                                        required>

                                </div>

                                <div class="form-group">

                                    <label>Full Name</label>

                                    <input
                                        type="text"
                                        name="full_name"
                                        class="form-control p_input"
                                        required>

                                </div>

                                <div class="form-group">

                                    <label>Email</label>

                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control p_input"
                                        required>

                                </div>

                                <div class="form-group">

                                    <label>Password</label>

                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control p_input"
                                        required>

                                </div>

                                <div class="text-center">

                                    <button
                                        type="submit"
                                        name="submit"
                                        class="btn btn-primary btn-block enter-btn">

                                        Register

                                    </button>

                                </div>

                                <div class="text-center mt-4">

                                    Already have an account?

                                    <a href="login.php">
                                        Login
                                    </a>

                                </div>

                            </form>

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

</body>

</html>