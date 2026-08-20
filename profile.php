<?php
require_once 'auth.php';
require_once 'database.php';

$user_id = (int)$_SESSION['user_id'];
$message = '';
$msgType = '';

// ── Get flash message after redirect ──
if (isset($_SESSION['profile_message'])) {
    $message = $_SESSION['profile_message'];
    $msgType = $_SESSION['profile_msgType'];

    unset($_SESSION['profile_message']);
    unset($_SESSION['profile_msgType']);
}

// ── Handle POST: Edit Profile ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {

    $pwd      = $_POST['profile_password'] ?? '';
    $newName  = trim($_POST['full_name'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');

    if ($pwd === '' || $newName === '' || $newEmail === '') {

        $_SESSION['profile_message'] = 'All fields are required.';
        $_SESSION['profile_msgType'] = 'error';

    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['profile_message'] = 'Please enter a valid email address.';
        $_SESSION['profile_msgType'] = 'error';

    } else {

        $user = $con->query("SELECT password FROM users WHERE id = $user_id")->fetch_assoc();

        if (!$user || md5($pwd) !== $user['password']) {

            $_SESSION['profile_message'] = 'Password is incorrect.';
            $_SESSION['profile_msgType'] = 'error';

        } else {

            $safeEmail = $con->real_escape_string($newEmail);
            $safeName  = $con->real_escape_string($newName);

            $emailCheck = $con->query("
                SELECT id
                FROM users
                WHERE email = '$safeEmail'
                AND id != $user_id
            ");

            if ($emailCheck->num_rows > 0) {

                $_SESSION['profile_message'] = 'That email is already used by another account.';
                $_SESSION['profile_msgType'] = 'error';

            } else {

                $update = $con->query("
                    UPDATE users
                    SET full_name = '$safeName',
                        email = '$safeEmail'
                    WHERE id = $user_id
                ");

                if ($update) {

                    $_SESSION['full_name'] = $newName;
                    $_SESSION['email'] = $newEmail;

                    $_SESSION['profile_message'] = 'Profile updated successfully.';
                    $_SESSION['profile_msgType'] = 'success';

                } else {

                    $_SESSION['profile_message'] = 'Failed to update profile.';
                    $_SESSION['profile_msgType'] = 'error';
                }
            }
        }
    }

    // ── Redirect after POST to prevent form resubmission ──
    header('Location: profile.php');
    exit();
}


// ── Handle POST: Change Password ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {

    $oldPwd     = $_POST['old_password'] ?? '';
    $newPwd     = $_POST['new_password'] ?? '';
    $confirmPwd = $_POST['confirm_password'] ?? '';

    if ($oldPwd === '' || $newPwd === '' || $confirmPwd === '') {

        $_SESSION['profile_message'] = 'All password fields are required.';
        $_SESSION['profile_msgType'] = 'error';

    } elseif (strlen($newPwd) < 8) {

        $_SESSION['profile_message'] = 'New password must be at least 8 characters long.';
        $_SESSION['profile_msgType'] = 'error';

    } elseif (!preg_match('/[A-Z]/', $newPwd)) {

        $_SESSION['profile_message'] = 'New password must contain at least one uppercase letter.';
        $_SESSION['profile_msgType'] = 'error';

    } elseif (!preg_match('/[a-z]/', $newPwd)) {

        $_SESSION['profile_message'] = 'New password must contain at least one lowercase letter.';
        $_SESSION['profile_msgType'] = 'error';

    } elseif (!preg_match('/[0-9]/', $newPwd)) {

        $_SESSION['profile_message'] = 'New password must contain at least one number.';
        $_SESSION['profile_msgType'] = 'error';

    } elseif (!preg_match('/[^A-Za-z0-9]/', $newPwd)) {

        $_SESSION['profile_message'] = 'New password must contain at least one special character.';
        $_SESSION['profile_msgType'] = 'error';

    } elseif ($newPwd !== $confirmPwd) {

        $_SESSION['profile_message'] = 'New password and confirmation do not match.';
        $_SESSION['profile_msgType'] = 'error';

    } else {

        $user = $con->query("SELECT password FROM users WHERE id = $user_id")->fetch_assoc();

        if (!$user || md5($oldPwd) !== $user['password']) {

            $_SESSION['profile_message'] = 'Current password is incorrect.';
            $_SESSION['profile_msgType'] = 'error';

        } else {

            $hashedPassword = md5($newPwd);

            $update = $con->query("
                UPDATE users
                SET password = '$hashedPassword'
                WHERE id = $user_id
            ");

            if ($update) {

                $_SESSION['profile_message'] = 'Password changed successfully.';
                $_SESSION['profile_msgType'] = 'success';

            } else {

                $_SESSION['profile_message'] = 'Failed to change password.';
                $_SESSION['profile_msgType'] = 'error';
            }
        }
    }

    // ── Redirect after POST to prevent form resubmission ──
    header('Location: profile.php');
    exit();
}


// ── Fetch user info ──
$user = $con->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();


// ── Fetch stats ──
$statExercises = (int)$con->query("
    SELECT COUNT(*) AS c
    FROM exercise
    WHERE user_id = $user_id
")->fetch_assoc()['c'];

$statCalories = (int)$con->query("
    SELECT COALESCE(SUM(calories_burned),0) AS c
    FROM exercise
    WHERE user_id = $user_id
    AND activity_status = 'Completed'
")->fetch_assoc()['c'];

$statDiary = (int)$con->query("
    SELECT COUNT(*) AS c
    FROM diary
    WHERE user_id = $user_id
")->fetch_assoc()['c'];

$statHabitsTotal = (int)$con->query("
    SELECT COUNT(*) AS c
    FROM habits
    WHERE user_id = $user_id
")->fetch_assoc()['c'];

$statHabitsDone = (int)$con->query("
    SELECT COUNT(*) AS c
    FROM habits
    WHERE user_id = $user_id
    AND completion_status = 'Completed'
")->fetch_assoc()['c'];

$statIncome = (float)$con->query("
    SELECT COALESCE(SUM(amount),0) AS t
    FROM transactions
    WHERE user_id = $user_id
    AND type = 'income'
")->fetch_assoc()['t'];

$statExpense = (float)$con->query("
    SELECT COALESCE(SUM(amount),0) AS t
    FROM transactions
    WHERE user_id = $user_id
    AND type = 'expense'
")->fetch_assoc()['t'];


$initial = strtoupper(substr($user['username'], 0, 1));
$roleColor = $user['role'] === 'admin' ? '#e74c3c' : '#0090e7';
$regDate = date('M j, Y', strtotime($user['reg_date']));
$lastLogin = $user['last_login']
    ? date('M j, Y g:i A', strtotime($user['last_login']))
    : 'First login';

ob_start();
?>

<style>
    .profile-header {
        background: #1e2230;
        border-radius: 14px;
        padding: 28px 32px;
        display: flex;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #0090e7, #00bcd4);
        box-shadow: 0 4px 20px rgba(0, 144, 231, 0.3);
    }

    .profile-info {
        flex: 1;
        min-width: 200px;
    }

    .profile-name {
        font-size: 22px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 2px;
    }

    .profile-meta {
        font-size: 13px;
        color: #8f9bb3;
        margin-bottom: 6px;
    }

    .profile-meta a {
        color: #8f9bb3;
        text-decoration: none;
    }

    .profile-meta a:hover {
        color: #0090e7;
    }

    .role-badge {
        display: inline-block;
        font-size: 10px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .profile-dates {
        text-align: right;
        flex-shrink: 0;
    }

    .profile-dates p {
        font-size: 12px;
        color: #6c7a89;
        margin-bottom: 3px;
    }

    .profile-dates span {
        color: #8f9bb3;
    }

    .form-card {
        background: #1e2230;
        border-radius: 14px;
        padding: 24px;
        height: 100%;
    }

    .form-card-title {
        font-size: 15px;
        font-weight: 600;
        color: #fff;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-card-title i {
        font-size: 18px;
        opacity: 0.6;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 12px;
        color: #8f9bb3;
        margin-bottom: 5px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .form-group input {
        width: 100%;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.04);
        color: #fff;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-group input:focus {
        border-color: #0090e7;
        box-shadow: 0 0 0 3px rgba(0, 144, 231, 0.15);
    }

    .form-group input::placeholder {
        color: #555d6e;
    }

    .btn-save {
        width: 100%;
        padding: 11px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-save:hover {
        transform: translateY(-1px);
    }

    .btn-save:active {
        transform: translateY(0);
    }

    .btn-primary-custom {
        background: #0090e7;
        color: #fff;
        box-shadow: 0 4px 15px rgba(0, 144, 231, 0.3);
    }

    .btn-primary-custom:hover {
        box-shadow: 0 6px 20px rgba(0, 144, 231, 0.4);
    }

    .btn-warning-custom {
        background: #f6b93b;
        color: #1e2230;
        box-shadow: 0 4px 15px rgba(246, 185, 59, 0.3);
    }

    .btn-warning-custom:hover {
        box-shadow: 0 6px 20px rgba(246, 185, 59, 0.4);
    }

    .stat-card {
        background: #1e2230;
        border-radius: 12px;
        padding: 18px;
        text-align: center;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        margin: 0 auto 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 11px;
        color: #6c7a89;
        margin-top: 2px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .alert-msg {
        padding: 12px 18px;
        border-radius: 10px;
        margin-bottom: 16px;
        font-size: 13px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        animation: slideDown 0.3s ease;
    }

    .alert-msg.success {
        background: rgba(76, 175, 80, 0.12);
        color: #4caf50;
        border: 1px solid rgba(76, 175, 80, 0.2);
    }

    .alert-msg.error {
        background: rgba(231, 76, 60, 0.12);
        color: #e74c3c;
        border: 1px solid rgba(231, 76, 60, 0.2);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<?php if ($message): ?>
    <div class="col-12">
        <div class="alert-msg <?php echo $msgType; ?>">
            <i class="mdi <?php echo $msgType === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    </div>
<?php endif; ?>

<!-- Profile Header -->
<div class="col-12 mb-4">
    <div class="profile-header">
        <div class="profile-avatar"><?php echo $initial; ?></div>
        <div class="profile-info">
            <div class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
            <div class="profile-meta">
                <a href="#"><i class="mdi mdi-at" style="font-size:12px;"></i><?php echo htmlspecialchars($user['username']); ?></a>
                <span style="margin:0 8px; color:#3a3f4b;">|</span>
                <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>"><i class="mdi mdi-email-outline" style="font-size:12px;"></i> <?php echo htmlspecialchars($user['email']); ?></a>
                <span style="margin:0 8px; color:#3a3f4b;">|</span>
                <span class="role-badge" style="background:<?php echo $roleColor; ?>20; color:<?php echo $roleColor; ?>;">
                    <?php echo htmlspecialchars($user['role']); ?>
                </span>
            </div>
        </div>
        <div class="profile-dates">
            <p><i class="mdi mdi-calendar-plus" style="font-size:13px; margin-right:4px;"></i> Joined: <span><?php echo $regDate; ?></span></p>
            <p><i class="mdi mdi-clock-outline" style="font-size:13px; margin-right:4px;"></i> Last login: <span><?php echo $lastLogin; ?></span></p>
        </div>
    </div>
</div>

<!-- Edit Profile — now requires password -->
<div class="col-lg-6 col-12 mb-4">
    <div class="form-card">
        <div class="form-card-title">
            <i class="mdi mdi-account-edit" style="color:#0090e7;"></i> Edit Profile
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_profile">
            <div class="form-group">
                <label for="profile_password">Password <span style="color:#555d6e;text-transform:none;letter-spacing:0;">(to confirm changes)</span></label>
                <input
                    type="password"
                    id="profile_password"
                    name="profile_password"
                    placeholder="Enter your current password"
                    required>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" placeholder="Enter your full name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Enter your email" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <button type="submit" class="btn-save btn-primary-custom">
                    <i class="mdi mdi-content-save" style="font-size:16px; margin-right:4px;"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Change Password -->
<div class="col-lg-6 col-12 mb-4">
    <div class="form-card">
        <div class="form-card-title">
            <i class="mdi mdi-lock-reset" style="color:#f6b93b;"></i> Change Password
        </div>
        <form method="POST" action="" id="changePasswordForm">
            <input type="hidden" name="action" value="change_password">

            <div class="form-group">
                <label for="old_password">Current Password</label>
                <input
                    type="password"
                    id="old_password"
                    name="old_password"
                    placeholder="Enter current password"
                    required>
            </div>

            <div class="form-group">
                <label for="change_new_password">New Password</label>
                <input
                    type="password"
                    id="change_new_password"
                    name="new_password"
                    placeholder="Min 8 chars, uppercase, lowercase, number & symbol"
                    required
                    minlength="8"
                    pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}"
                    title="Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character.">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Re-enter new password"
                    required
                    minlength="8">
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <button type="submit" class="btn-save btn-warning-custom">
                    <i class="mdi mdi-key" style="font-size:16px; margin-right:4px;"></i> Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Account Stats -->
<div class="col-12 mb-2">
    <h6 class="text-white font-weight-bold mb-3" style="font-size:13px; text-transform:uppercase; letter-spacing:0.6px; opacity:0.6;">
        <i class="mdi mdi-chart-arc" style="font-size:15px;"></i> Account Overview
    </h6>
</div>

<div class="col-xl-2 col-lg-4 col-md-4 col-6 mb-4">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(246,185,59,0.12); color:#f6b93b;"><i class="mdi mdi-run-fast"></i></div>
        <div class="stat-value"><?php echo $statExercises; ?></div>
        <div class="stat-label">Exercises</div>
    </div>
</div>

<div class="col-xl-2 col-lg-4 col-md-4 col-6 mb-4">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(255,152,0,0.12); color:#ff9800;"><i class="mdi mdi-fire"></i></div>
        <div class="stat-value"><?php echo number_format($statCalories); ?></div>
        <div class="stat-label">Calories</div>
    </div>
</div>

<div class="col-xl-2 col-lg-4 col-md-4 col-6 mb-4">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(233,30,99,0.12); color:#e91e63;"><i class="mdi mdi-book-open-page-variant"></i></div>
        <div class="stat-value"><?php echo $statDiary; ?></div>
        <div class="stat-label">Diary Entries</div>
    </div>
</div>

<div class="col-xl-2 col-lg-4 col-md-4 col-6 mb-4">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(76,175,80,0.12); color:#4caf50;"><i class="mdi mdi-checkbox-marked-circle-outline"></i></div>
        <div class="stat-value"><?php echo "$statHabitsDone"; ?><span style="font-size:13px;color:#555d6e;font-weight:400;">/<?php echo $statHabitsTotal; ?></span></div>
        <div class="stat-label">Habits Done</div>
    </div>
</div>

<div class="col-xl-2 col-lg-4 col-md-4 col-6 mb-4">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(0,144,231,0.12); color:#0090e7;"><i class="mdi mdi-arrow-up-bold"></i></div>
        <div class="stat-value" style="color:#4caf50;"><?php echo '$' . number_format($statIncome, 2); ?></div>
        <div class="stat-label">Income</div>
    </div>
</div>

<div class="col-xl-2 col-lg-4 col-md-4 col-6 mb-4">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(231,76,60,0.12); color:#e74c3c;"><i class="mdi mdi-arrow-down-bold"></i></div>
        <div class="stat-value" style="color:#e74c3c;"><?php echo '$' . number_format($statExpense, 2); ?></div>
        <div class="stat-label">Expenses</div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
$current_page = basename(__FILE__);
require_once 'layout.php';
?>