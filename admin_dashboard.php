
<?php
require('auth.php');
require('database.php');

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: user_dashboard.php");
    exit();
}

$current_page = "admin_dashboard.php";

function tableExists($con, $tableName)
{
    $tableName = mysqli_real_escape_string($con, $tableName);

    $result = mysqli_query(
        $con,
        "SHOW TABLES LIKE '$tableName'"
    );

    return $result && mysqli_num_rows($result) > 0;
}

function getTableCount($con, $tableName)
{
    if (!tableExists($con, $tableName)) {
        return 0;
    }

    $result = mysqli_query(
        $con,
        "SELECT COUNT(*) AS total FROM `$tableName`"
    );

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return (int)$row['total'];
    }

    return 0;
}

$userCount = 0;
$studentCount = 0;
$adminCount = 0;

$result = mysqli_query(
    $con,
    "SELECT COUNT(*) AS total FROM users"
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $userCount = (int)$row['total'];
}

$result = mysqli_query(
    $con,
    "SELECT role, COUNT(*) AS total
     FROM users
     GROUP BY role"
);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (strtolower($row['role']) === 'student') {
            $studentCount = (int)$row['total'];
        }

        if (strtolower($row['role']) === 'admin') {
            $adminCount = (int)$row['total'];
        }
    }
}

$moduleTables = [
    'exercise',
    'diary',
    'money',
    'habit'
];

$totalRecords = 0;
$moduleCounts = [];

foreach ($moduleTables as $table) {
    $count = getTableCount($con, $table);
    $moduleCounts[$table] = $count;
    $totalRecords += $count;
}

$selectedUser = null;

if (isset($_GET['view_user'])) {
    $viewUserId = (int)$_GET['view_user'];

    $stmt = mysqli_prepare(
        $con,
        "SELECT id, role, username, full_name, email, reg_date
         FROM users
         WHERE id = ?
         LIMIT 1"
    );

    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $viewUserId
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $selectedUser = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);
    }
}

$usersResult = mysqli_query(
    $con,
    "SELECT id, role, username, full_name, email, reg_date
     FROM users
     ORDER BY reg_date DESC, id DESC"
);

$recentResult = mysqli_query(
    $con,
    "SELECT id, role, username, full_name, email, reg_date
     FROM users
     ORDER BY reg_date DESC, id DESC
     LIMIT 5"
);

ob_start();
?>

<div class="col-12">

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap">

                <div>
                    <h3 class="text-white mb-1">
                        <i class="mdi mdi-shield-account text-primary"></i>
                        Admin Dashboard
                    </h3>

                    <p class="text-muted mb-0">
                        View registered users and basic system summaries.
                    </p>
                </div>

                <span class="badge badge-primary p-2 mt-2 mt-md-0">
                    Administrator
                </span>

            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-6 mb-3">

            <div class="card h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">
                        Total Users
                    </p>

                    <h2 class="text-white mb-0">
                        <?php echo $userCount; ?>
                    </h2>

                    <small class="text-info">
                        <?php echo $studentCount; ?> students ·
                        <?php echo $adminCount; ?> admins
                    </small>

                </div>

            </div>

        </div>

        <div class="col-md-6 mb-3">

            <div class="card h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">
                        Total Records
                    </p>

                    <h2 class="text-white mb-0">
                        <?php echo $totalRecords; ?>
                    </h2>

                    <small class="text-muted">
                        Across all routine modules
                    </small>

                </div>

            </div>

        </div>

    </div>

    <div class="card mb-4">

        <div class="card-body">

            <h4 class="text-white mb-3">
                <i class="mdi mdi-chart-box-outline text-primary"></i>
                Module Record Summary
            </h4>

            <div class="table-responsive">

                <table class="table table-dark table-hover">

                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Total Records</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>Exercise Tracker</td>
                            <td>
                                <?php echo $moduleCounts['exercise']; ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Diary Journal</td>
                            <td>
                                <?php echo $moduleCounts['diary']; ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Money Tracker</td>
                            <td>
                                <?php echo $moduleCounts['money']; ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Habit Tracker</td>
                            <td>
                                <?php echo $moduleCounts['habit']; ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Total</th>
                            <th>
                                <?php echo $totalRecords; ?>
                            </th>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <div class="card mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h4 class="text-white mb-0">
                    <i class="mdi mdi-account-multiple text-primary"></i>
                    View All Users
                </h4>

                <span class="text-muted small">
                    <?php echo $userCount; ?> registered user(s)
                </span>

            </div>

            <div class="table-responsive">

                <table class="table table-dark table-hover">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if ($usersResult && mysqli_num_rows($usersResult) > 0): ?>

                            <?php while ($user = mysqli_fetch_assoc($usersResult)): ?>

                                <tr>

                                    <td>
                                        <?php echo (int)$user['id']; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($user['full_name']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </td>

                                    <td>

                                        <?php if (strtolower($user['role']) === 'admin'): ?>

                                            <span class="text-primary">
                                                Admin
                                            </span>

                                        <?php else: ?>

                                            <span class="text-info">
                                                Student
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php

                                        echo date(
                                            'd M Y, h:i A',
                                            strtotime($user['reg_date'])
                                        );

                                        ?>

                                    </td>

                                    <td>

                                        <a
                                            href="admin_dashboard.php?view_user=<?php echo (int)$user['id']; ?>#user-details"
                                            class="btn btn-info btn-sm"
                                        >

                                            <i class="mdi mdi-account-details"></i>
                                            View Details

                                        </a>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center text-muted"
                                >
                                    No registered users found.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <?php if ($selectedUser): ?>

        <div
            class="card mb-4"
            id="user-details"
        >

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h4 class="text-white mb-0">

                        <i class="mdi mdi-account-card-details text-info"></i>
                        View User Details

                    </h4>

                    <a
                        href="admin_dashboard.php"
                        class="btn btn-secondary btn-sm"
                    >
                        Close
                    </a>

                </div>

                <div class="row">

                    <div class="col-md-6">

                        <p class="text-muted mb-1">
                            Username
                        </p>

                        <p class="text-white">
                            <?php
                            echo htmlspecialchars(
                                $selectedUser['username']
                            );
                            ?>
                        </p>

                    </div>

                    <div class="col-md-6">

                        <p class="text-muted mb-1">
                            Full Name
                        </p>

                        <p class="text-white">
                            <?php
                            echo htmlspecialchars(
                                $selectedUser['full_name']
                            );
                            ?>
                        </p>

                    </div>

                    <div class="col-md-6">

                        <p class="text-muted mb-1">
                            Email
                        </p>

                        <p class="text-white">
                            <?php
                            echo htmlspecialchars(
                                $selectedUser['email']
                            );
                            ?>
                        </p>

                    </div>

                    <div class="col-md-6">

                        <p class="text-muted mb-1">
                            Role
                        </p>

                        <p class="text-white">
                            <?php
                            echo htmlspecialchars(
                                ucfirst($selectedUser['role'])
                            );
                            ?>
                        </p>

                    </div>

                    <div class="col-md-6">

                        <p class="text-muted mb-1">
                            Registration Date
                        </p>

                        <p class="text-white">

                            <?php

                            echo date(
                                'd M Y, h:i A',
                                strtotime(
                                    $selectedUser['reg_date']
                                )
                            );

                            ?>

                        </p>

                    </div>

                </div>

            </div>

        </div>

    <?php elseif (isset($_GET['view_user'])): ?>

        <div class="alert alert-warning">
            The selected user could not be found.
        </div>

    <?php endif; ?>

    <div class="card mb-4">

        <div class="card-body">

            <h4 class="text-white mb-3">

                <i class="mdi mdi-account-clock text-warning"></i>
                Recent Registrations

            </h4>

            <div class="table-responsive">

                <table class="table table-dark table-hover">

                    <thead>

                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registration Date</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if ($recentResult && mysqli_num_rows($recentResult) > 0): ?>

                            <?php while ($user = mysqli_fetch_assoc($recentResult)): ?>

                                <tr>

                                    <td>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($user['full_name']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            ucfirst($user['role'])
                                        );
                                        ?>
                                    </td>

                                    <td>

                                        <?php

                                        echo date(
                                            'd M Y, h:i A',
                                            strtotime($user['reg_date'])
                                        );

                                        ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center text-muted"
                                >
                                    No registrations found.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php

$pageContent = ob_get_clean();

include "layout.php";

?>

