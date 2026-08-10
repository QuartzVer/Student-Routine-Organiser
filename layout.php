<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Student Routine Organizer</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.theme.default.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="icon.png" />

    <style>
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
            overflow-x: hidden;
            background: #191c24;
        }

        .container-scroller,
        .page-body-wrapper,
        .main-panel,
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
        }

        .row {
            margin: 0 !important;
        }

        .header-card {
            margin-top: 0 !important;
            margin-bottom: 15px;
        }





        .nav-link {
            padding: 12px 20px !important;
            border-radius: 5px;
        }

        .nav-link.active {
            background-color: #0090e7;
            color: white !important;
        }





        /* Header separation */
        .header-card {
            margin-bottom: 15px;
            border-radius: 12px;
        }

        /* Navbar as tabs */
        .nav-card {
            border-radius: 12px;
            padding: 5px;
        }

        .nav-card .card-body {
            padding: 8px;
        }

        .nav-tabs-custom {
            gap: 8px;
        }

        .nav-tabs-custom .nav-link {
            padding: 10px 18px;
            border-radius: 8px;
            color: #ffffff;
            transition: 0.3s;
        }

        /* Hover effect */
        .nav-tabs-custom .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Active tab */
        .nav-tabs-custom .active-tab {
            background-color: #2c3e50;
            color: white !important;
        }



        /* Dropdown size match navbar tabs */
        .dropdown-menu {
            font-size: 14px !important;
            border-radius: 8px;
        }

        .dropdown-item {
            padding: 8px 15px !important;
            font-size: 14px !important;
        }

        .dropdown-item i {
            font-size: 14px;
        }




        .form-control {
            color: #ffffff !important;
        }

        select.form-control {
            color: #ffffff !important;
        }

        select.form-control option {
            background-color: #ffffff !important;
            color: #000000 !important;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
        
    </style>

</head>

<body>
    <div class="container-scroller">

        <div class="page-body-wrapper">

            <div class="main-panel">

                <div class="content-wrapper">


                    <!-- Header -->
                    <div class="row">

                        <div class="col-12">

                            <div class="card header-card">
                                <div class="card-body text-center">

                                    <h1 class="text-white mb-2">
                                        Student Routine Organizer
                                    </h1>

                                    <p class="text-muted fs-6 mb-0">
                                        Your life, completely organized.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- Navigation -->
                    <div class="row">

                        <div class="col-12">

                            <div class="card nav-card mb-4">
                                <div class="card-body p-2">

                                    <ul class="nav justify-content-center nav-tabs-custom">
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo ($current_page == 'user_dashboard.php') ? 'active-tab' : ''; ?>"
                                                href="user_dashboard.php"> <i
                                                    class="mdi mdi-view-dashboard text-primary"></i>
                                                Dashboard
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link <?php echo ($current_page == 'exercise.php') ? 'active-tab' : ''; ?>"
                                                href="exercise.php"> <i
                                                    class="mdi mdi-run-fast text-warning"></i>
                                                Exercise Tracker
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link <?php echo ($current_page == 'diary.php') ? 'active-tab' : ''; ?>"
                                                href="diary.php"> <i
                                                    class="mdi mdi-book-open-page-variant text-danger"></i>
                                                Diary Journal
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link <?php echo ($current_page == 'money.php') ? 'active-tab' : ''; ?>"
                                                href="money.php"> <i class="mdi mdi-cash-multiple text-info"></i>
                                                Money Tracker
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link <?php echo ($current_page == 'habit.php') ? 'active-tab' : ''; ?>"
                                                href="habit.php"> <i
                                                    class="mdi mdi-checkbox-marked-circle-outline text-success"></i>
                                                Habit Tracker
                                            </a>
                                        </li>



                                        <li class="nav-item dropdown ml-auto">

                                            <a class="nav-link text-white dropdown-toggle" href="#" id="userDropdown"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                                                <i class="mdi mdi-account-circle text-primary"></i>
                                                <?php echo $_SESSION['username']; ?>

                                            </a>


                                            <div class="dropdown-menu dropdown-menu-right">


                                                <a class="dropdown-item" href="profile.php">
                                                    <i class="mdi mdi-account-outline mr-2"></i>
                                                    Profile
                                                </a>

                                                <div class="dropdown-divider"></div>

                                                <a class="dropdown-item text-danger" href="logout.php">
                                                    <i class="mdi mdi-logout mr-2"></i>
                                                    Logout
                                                </a>

                                            </div>

                                        </li>

                                    </ul>

                                </div>

                            </div>

                        </div>

                    </div>




                    <!-- Content -->
                    <div class="row">

                        <?php echo $pageContent ?? ''; ?>

                    </div>

                </div> <!-- main-panel -->

            </div> <!-- page-body-wrapper -->

        </div> <!-- container-scroller -->


        <!-- container-scroller -->
        <!-- plugins:js -->
        <script src="assets/vendors/js/vendor.bundle.base.js"></script>
        <!-- endinject -->
        <!-- Plugin js for this page -->
        <script src="assets/vendors/chart.js/chart.umd.js"></script>
        <script src="assets/vendors/progressbar.js/progressbar.min.js"></script>
        <script src="assets/vendors/jvectormap/jquery-jvectormap.min.js"></script>
        <script src="assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
        <script src="assets/vendors/owl-carousel-2/owl.carousel.min.js"></script>
        <script src="assets/js/jquery.cookie.js" type="text/javascript"></script>
        <!-- End plugin js for this page -->
        <!-- inject:js -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/off-canvas.js"></script>
        <script src="assets/js/misc.js"></script>
        <script src="assets/js/settings.js"></script>
        <script src="assets/js/todolist.js"></script>
        <!-- endinject -->
        <!-- Custom js for this page -->
        <script src="assets/js/proBanner.js"></script>
        <script src="assets/js/dashboard.js"></script>
        <!-- End custom js for this page -->
</body>

</html>