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
$selectedUserCounts = [];
$selectedUserTotalRecords = 0;

if (isset($_GET['view_user'])) {
    $viewUserId = (int)$_GET['view_user'];

    $stmt = mysqli_prepare(
        $con,
        "SELECT id, role, username, full_name, email, reg_date, last_login
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

    if ($selectedUser) {
        foreach ($moduleTables as $table) {
            if (tableExists($con, $table)) {
                $countStmt = mysqli_prepare(
                    $con,
                    "SELECT COUNT(*) AS total FROM `$table` WHERE user_id = ?"
                );

                if ($countStmt) {
                    mysqli_stmt_bind_param($countStmt, "i", $viewUserId);
                    mysqli_stmt_execute($countStmt);
                    $countResult = mysqli_stmt_get_result($countStmt);
                    $countRow = mysqli_fetch_assoc($countResult);
                    $selectedUserCounts[$table] = (int)$countRow['total'];
                    mysqli_stmt_close($countStmt);
                } else {
                    $selectedUserCounts[$table] = 0;
                }
            } else {
                // Money and Habit tables may not exist yet in the current database.
                $selectedUserCounts[$table] = 0;
            }

            $selectedUserTotalRecords += $selectedUserCounts[$table];
        }
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
    "SELECT id, role, username, full_name, email, last_login
     FROM users
     WHERE last_login IS NOT NULL
     ORDER BY last_login DESC, id DESC
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

            <div class="mb-3">
                <input type="text" id="userTableSearch" class="form-control"
                       placeholder="Search ID, username, full name or email..." autocomplete="off">
            </div>

            <div class="table-responsive">

                <table class="table table-dark table-hover" id="usersTable">

                    <thead>

                        <tr>
                            <th class="sortable" data-column="0" data-type="number">ID <span class="sort-indicator">↕</span></th>
                            <th class="sortable" data-column="1" data-type="text">Username <span class="sort-indicator">↕</span></th>
                            <th class="sortable" data-column="2" data-type="text">Full Name <span class="sort-indicator">↕</span></th>
                            <th class="sortable" data-column="3" data-type="text">Email <span class="sort-indicator">↕</span></th>
                            <th class="sortable" data-column="4" data-type="text">Role <span class="sort-indicator">↕</span></th>
                            <th class="sortable" data-column="5" data-type="date">Registered <span class="sort-indicator">↕</span></th>
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
                                        $regTimestamp = strtotime($user['reg_date']);
                                        echo '<span data-sort-value="' . $regTimestamp . '">' .
                                             date('d M Y, h:i A', $regTimestamp) .
                                             '</span>';
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

                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">

                    <h4 class="text-white mb-0">
                        <i class="mdi mdi-account-card-details text-info"></i>
                        View User Details
                    </h4>

                    <a
                        href="admin_dashboard.php"
                        class="btn btn-secondary btn-sm mt-2 mt-md-0"
                    >
                        Close
                    </a>

                </div>

                <!-- User information is shown once -->
                <div class="row mb-4">

                    <div class="col-md-3 mb-3">
                        <p class="text-muted mb-1">Username</p>
                        <p class="text-white mb-0">
                            <?php echo htmlspecialchars($selectedUser['username']); ?>
                        </p>
                    </div>

                    <div class="col-md-3 mb-3">
                        <p class="text-muted mb-1">Full Name</p>
                        <p class="text-white mb-0">
                            <?php echo htmlspecialchars($selectedUser['full_name']); ?>
                        </p>
                    </div>

                    <div class="col-md-3 mb-3">
                        <p class="text-muted mb-1">Email</p>
                        <p class="text-white mb-0 text-break">
                            <?php echo htmlspecialchars($selectedUser['email']); ?>
                        </p>
                    </div>

                    <div class="col-md-3 mb-3">
                        <p class="text-muted mb-1">Role</p>
                        <p class="text-white mb-0">
                            <?php echo htmlspecialchars(ucfirst($selectedUser['role'])); ?>
                        </p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <p class="text-muted mb-1">Registration Date</p>
                        <p class="text-white mb-0">
                            <?php
                            echo date(
                                'd M Y, h:i A',
                                strtotime($selectedUser['reg_date'])
                            );
                            ?>
                        </p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <p class="text-muted mb-1">Last Login</p>
                        <p class="text-white mb-0">
                            <?php if (!empty($selectedUser['last_login'])): ?>
                                <?php
                                echo date(
                                    'd M Y, h:i A',
                                    strtotime($selectedUser['last_login'])
                                );
                                ?>
                            <?php else: ?>
                                Never logged in
                            <?php endif; ?>
                        </p>
                    </div>

                </div>

                <!-- Overall record count -->
                <div class="card bg-dark mb-4">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Records</p>
                                <h3 class="text-white mb-0">
                                    <?php echo $selectedUserTotalRecords; ?>
                                </h3>
                            </div>
                            <i class="mdi mdi-database-check text-info" style="font-size: 36px;"></i>
                        </div>
                    </div>
                </div>

                <!-- Four module record counts -->
                <h5 class="text-white mb-3">Module Records</h5>

                <div class="row">

                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="mdi mdi-run text-success" style="font-size: 32px;"></i>
                                <p class="text-muted mb-1 mt-2">Exercise</p>
                                <h3 class="text-white mb-0">
                                    <?php echo $selectedUserCounts['exercise'] ?? 0; ?>
                                </h3>
                                <small class="text-muted">records</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="mdi mdi-book-open-page-variant text-danger" style="font-size: 32px;"></i>
                                <p class="text-muted mb-1 mt-2">Diary</p>
                                <h3 class="text-white mb-0">
                                    <?php echo $selectedUserCounts['diary'] ?? 0; ?>
                                </h3>
                                <small class="text-muted">records</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="mdi mdi-cash-multiple text-warning" style="font-size: 32px;"></i>
                                <p class="text-muted mb-1 mt-2">Money</p>
                                <h3 class="text-white mb-0">
                                    <?php echo $selectedUserCounts['money'] ?? 0; ?>
                                </h3>
                                <small class="text-muted">records</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="mdi mdi-check-circle-outline text-info" style="font-size: 32px;"></i>
                                <p class="text-muted mb-1 mt-2">Habit</p>
                                <h3 class="text-white mb-0">
                                    <?php echo $selectedUserCounts['habit'] ?? 0; ?>
                                </h3>
                                <small class="text-muted">records</small>
                            </div>
                        </div>
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

                <i class="mdi mdi-login text-success"></i>
                Recent Logins

            </h4>

            <div class="mb-3">
                <input type="text" id="recentLoginSearch" class="form-control"
                       placeholder="Search username, full name or role..." autocomplete="off">
            </div>

            <div class="table-responsive">

                <table class="table table-dark table-hover" id="recentLoginsTable">

                    <thead>

                        <tr>
                            <th class="sortable" data-column="0" data-type="text">Username <span class="sort-indicator">↕</span></th>
                            <th class="sortable" data-column="1" data-type="text">Full Name <span class="sort-indicator">↕</span></th>
                            <th class="sortable" data-column="2" data-type="text">Email <span class="sort-indicator">↕</span></th>
                            <th class="sortable" data-column="3" data-type="text">Role <span class="sort-indicator">↕</span></th>
                            <th class="sortable" data-column="4" data-type="date">Last Login <span class="sort-indicator">↕</span></th>
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
                                        $loginTimestamp = strtotime($user['last_login']);
                                        echo '<span data-sort-value="' . $loginTimestamp . '">' .
                                             date('d M Y, h:i A', $loginTimestamp) .
                                             '</span>';
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
                                    No recent logins found.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<style>
.sortable{cursor:pointer;user-select:none;white-space:nowrap}
.sortable:hover{color:#fff}
.sort-indicator{margin-left:4px;font-size:15px;opacity:.55}
.sortable.sort-asc .sort-indicator,.sortable.sort-desc .sort-indicator{opacity:1;color:#ffc107}
#userTableSearch,#recentLoginSearch{background:#191d28;border:1px solid #343a46;color:#fff}
#userTableSearch::placeholder,#recentLoginSearch::placeholder{color:#7d8594}
#userTableSearch:focus,#recentLoginSearch:focus{background:#191d28;color:#fff;border-color:#17a2b8;box-shadow:0 0 0 .2rem rgba(23,162,184,.15)}
</style>

<script>
document.addEventListener('DOMContentLoaded',function(){
    function setupTable(tableId,searchId,searchColumns){
        const table=document.getElementById(tableId);
        const search=document.getElementById(searchId);
        if(!table||!search)return;

        const tbody=table.querySelector('tbody');
        const headers=table.querySelectorAll('th.sortable');
        let activeColumn=null;
        let direction='asc';

        function getRows(){
            return Array.from(tbody.querySelectorAll('tr')).filter(function(row){
                return row.querySelectorAll('td').length>1;
            });
        }

        function getValue(row,column){
            const cell=row.cells[column];
            if(!cell)return '';
            const sortValue=cell.querySelector('[data-sort-value]');
            return sortValue ? sortValue.getAttribute('data-sort-value') : cell.textContent.trim();
        }

        search.addEventListener('input',function(){
            const query=this.value.trim().toLowerCase();
            getRows().forEach(function(row){
                const matched=!query||searchColumns.some(function(column){
                    return getValue(row,column).toLowerCase().includes(query);
                });
                row.style.display=matched?'':'none';
            });
        });

        headers.forEach(function(header){
            header.addEventListener('click',function(){
                const column=Number(this.dataset.column);
                const type=this.dataset.type||'text';

                if(activeColumn===column){
                    direction=direction==='asc'?'desc':'asc';
                }else{
                    activeColumn=column;
                    direction='asc';
                }

                getRows().sort(function(a,b){
                    const aValue=getValue(a,column);
                    const bValue=getValue(b,column);
                    let result;

                    if(type==='number'||type==='date'){
                        result=(Number(aValue)||0)-(Number(bValue)||0);
                    }else{
                        result=aValue.localeCompare(bValue,undefined,{numeric:true,sensitivity:'base'});
                    }

                    return direction==='asc'?result:-result;
                }).forEach(function(row){
                    tbody.appendChild(row);
                });

                headers.forEach(function(h){
                    h.classList.remove('sort-asc','sort-desc');
                    const indicator=h.querySelector('.sort-indicator');
                    if(indicator)indicator.textContent='↕';
                });

                this.classList.add(direction==='asc'?'sort-asc':'sort-desc');
                const indicator=this.querySelector('.sort-indicator');
                if(indicator)indicator.textContent=direction==='asc'?'↑':'↓';
            });
        });
    }

    setupTable('usersTable','userTableSearch',[0,1,2,3]);
    setupTable('recentLoginsTable','recentLoginSearch',[0,1,3]);
});
</script>

<?php
$pageContent = ob_get_clean();

include "layout.php";

?>