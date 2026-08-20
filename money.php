<?php
// money.php
require('auth.php');

// 1. SECURE PDO DATABASE CONNECTION
$host = 'localhost';
$db_name = 'student_routine';
$db_user = 'root';
$db_pass = '';

try {
    $con = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];
$current_page = "money.php";

// Handle POST actions (Add, Edit, Delete, Set Goal, Verify & Update Goal)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'create') {
            $stmt = $con->prepare("INSERT INTO transactions (user_id, type, amount, category, description, transaction_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $_POST['type'], $_POST['amount'], $_POST['category'], $_POST['description'] ?? null, $_POST['transaction_date']]);
            $date = $_POST['transaction_date'];
            header("Location: money.php?month=" . date('n', strtotime($date)) . "&year=" . date('Y', strtotime($date)));
            exit;
        } elseif ($_POST['action'] == 'edit' && isset($_POST['id'])) {
            $stmt = $con->prepare("UPDATE transactions SET type=?, amount=?, category=?, description=?, transaction_date=? WHERE transaction_id=? AND user_id=?");
            $stmt->execute([$_POST['type'], $_POST['amount'], $_POST['category'], $_POST['description'] ?? null, $_POST['transaction_date'], $_POST['id'], $user_id]);
            $date = $_POST['transaction_date'];
            header("Location: money.php?month=" . date('n', strtotime($date)) . "&year=" . date('Y', strtotime($date)));
            exit;
        } elseif ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            $stmt = $con->prepare("DELETE FROM transactions WHERE transaction_id=? AND user_id=?");
            $stmt->execute([$_POST['id'], $user_id]);
            $m = isset($_GET['month']) ? intval($_GET['month']) : date('n');
            $y = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
            header("Location: money.php?month=$m&year=$y");
            exit;
        } elseif ($_POST['action'] == 'set_goal') {
            $g_month = intval($_POST['goal_month']);
            $g_year = intval($_POST['goal_year']);
            $g_amount = floatval($_POST['goal_amount']);

            $stmt = $con->prepare("SELECT money_goal_id FROM money_goals WHERE user_id = ? AND goal_month = ? AND goal_year = ?");
            $stmt->execute([$user_id, $g_month, $g_year]);
            $existing = $stmt->fetch();

            if ($existing) {
                $stmt = $con->prepare("UPDATE money_goals SET goal_amount = ? WHERE money_goal_id = ?");
                $stmt->execute([$g_amount, $existing['money_goal_id']]);
            } else {
                $stmt = $con->prepare("INSERT INTO money_goals (user_id, goal_amount, goal_month, goal_year) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $g_amount, $g_month, $g_year]);
            }
            header("Location: money.php?month=$g_month&year=$g_year");
            exit;
        } elseif ($_POST['action'] == 'verify_and_update_goal') {
    $password = $_POST['password'];
    $new_goal = floatval($_POST['new_goal_amount']);
    $g_month = intval($_POST['goal_month']);
    $g_year = intval($_POST['goal_year']);

    // Fetch user's password hash
    $stmt = $con->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Check if password is valid (Supports both secure password_hash and old MD5)
    $valid = false;

    if ($user) {
        $hash = $user['password'];

        if (strlen($hash) == 32) {
            // MD5 fallback
            if (md5($password) == $hash) {
                $valid = true;
            }
        } else {
            // Secure password_hash
            if (password_verify($password, $hash)) {
                $valid = true;
            }
        }
    }

    if ($valid) {
        // Password correct, update the goal
        $stmt = $con->prepare("
            SELECT money_goal_id
            FROM money_goals
            WHERE user_id = ? AND goal_month = ? AND goal_year = ?
        ");
        $stmt->execute([$user_id, $g_month, $g_year]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $con->prepare("
                UPDATE money_goals
                SET goal_amount = ?
                WHERE money_goal_id = ?
            ");
            $stmt->execute([
                $new_goal,
                $existing['money_goal_id']
            ]);
        } else {
            $stmt = $con->prepare("
                INSERT INTO money_goals
                (user_id, goal_amount, goal_month, goal_year)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $new_goal,
                $g_month,
                $g_year
            ]);
        }

        header("Location: money.php?month=$g_month&year=$g_year&status=goal_updated");
        exit;

    } else {
        // Password wrong
        header("Location: money.php?month=$g_month&year=$g_year&status=pass_wrong");
        exit;
    }
}
    }
}

// Fetch Data & Filters
$months = [1 => "January", 2 => "February", 3 => "March", 4 => "April", 5 => "May", 6 => "June", 7 => "July", 8 => "August", 9 => "September", 10 => "October", 11 => "November", 12 => "December"];
$month_req = isset($_GET['month']) ? $_GET['month'] : date('n');

if ($month_req == 'all') {
    $month = null;
    $year = null;
    $filter_label = "All Time";
} else {
    $month = intval($month_req);
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    $filter_label = $months[$month] . " " . $year;
}

$dateFilter = "";
$params = [$user_id];
if ($month && $year) {
    $dateFilter = " AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?";
    $params[] = $month;
    $params[] = $year;
}

$stmt = $con->prepare("SELECT * FROM transactions WHERE user_id = ? $dateFilter ORDER BY transaction_date DESC");
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$stmt = $con->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type='income' $dateFilter");
$stmt->execute($params);
$total_income = $stmt->fetch()['total'] ?? 0;

$stmt = $con->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type='expense' $dateFilter");
$stmt->execute($params);
$total_expense = $stmt->fetch()['total'] ?? 0;

$saved_this_month = $total_income - $total_expense;

// Calculate Overall All-Time Balance
$stmt = $con->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type='income'");
$stmt->execute([$user_id]);
$all_time_income = $stmt->fetch()['total'] ?? 0;

$stmt = $con->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type='expense'");
$stmt->execute([$user_id]);
$all_time_expense = $stmt->fetch()['total'] ?? 0;
$overall_balance = $all_time_income - $all_time_expense;

$stmt = $con->prepare("SELECT category, SUM(amount) as total FROM transactions WHERE user_id = ? AND type='expense' $dateFilter GROUP BY category");
$stmt->execute($params);
$chart_data = $stmt->fetchAll();

// Limit table to 5 for main view
$recent_transactions = array_slice($transactions, 0, 5);

// Fetch Savings Goal for this month
$goal_amount = 0;
if ($filter_label != "All Time") {
    $stmt = $con->prepare("SELECT goal_amount FROM money_goals WHERE user_id = ? AND goal_month = ? AND goal_year = ?");
    $stmt->execute([$user_id, $month, $year]);
    $goal_amount = $stmt->fetch()['goal_amount'] ?? 0;
}

ob_start();
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* ========================= MONEY TRACKER DARK THEME ========================= */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --bg: #07111f;
        --bg2: #0b1830;
        --card: rgba(10, 20, 38, .78);
        --border: rgba(110, 155, 255, .15);
        --white: #ffffff;
        --text: #d8e4ff;
        --muted: #8ea3c7;
        --purple: #7C5CFF;
        --green: #00D26A;
        --red: #FF4D67;
        --blue: #3B82F6;
        --yellow: #F6B31A;
        --cyan: #22D3EE;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }

    body {
        background: radial-gradient(circle at top left, #1D4ED822, transparent 30%), radial-gradient(circle at bottom right, #2563EB22, transparent 25%), linear-gradient(135deg, #040B16 0%, #071524 40%, #091B33 100%);
        color: var(--text);
        min-height: 100vh;
    }

    body::before {
        content: "";
        position: fixed;
        inset: 0;
        background: repeating-linear-gradient(90deg, transparent 0px, transparent 70px, rgba(255, 255, 255, .015) 71px);
        pointer-events: none;
    }

    .money-container {
        width: 98%;
        max-width: 1450px;
        margin: 15px auto;
        padding: 10px 0;
        display: flex;
        flex-direction: column;
        position: relative;
        z-index: 1;
        min-height: calc(100vh - 160px);
    }

    .glass-card {
        background: var(--card);
        border: 1px solid var(--border);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 18px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, .35), inset 0 1px 0 rgba(255, 255, 255, .05);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 15px;
        flex-shrink: 0;
    }

    .stat-card {
        padding: 15px;
        position: relative;
        overflow: hidden;
    }

    .stat-card::after {
        content: "";
        position: absolute;
        top: -30px;
        right: -30px;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .04);
    }

    .stat-title {
        color: #9CA3AF;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -1px;
        color: #fff;
    }

    .stat-growth {
        margin-top: 8px;
        font-size: 12px;
        color: var(--muted);
    }

    .balance-card {
        border: 1px solid rgba(139, 92, 246, 0.4);
        box-shadow: 0 0 20px rgba(139, 92, 246, 0.25);
    }

    .income-card {
        border: 1px solid rgba(34, 197, 94, 0.4);
        box-shadow: 0 0 20px rgba(34, 197, 94, 0.2);
    }

    .expense-card {
        border: 1px solid rgba(239, 68, 68, 0.4);
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
    }

    .savings-card {
        border: 1px solid rgba(59, 130, 246, 0.4);
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.2);
    }

    .icon-box {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        position: absolute;
        top: 15px;
        right: 15px;
    }

    .icon-purple {
        background: rgba(139, 92, 246, 0.2);
        color: #a78bfa;
        box-shadow: 0 0 20px rgba(139, 92, 246, 0.4);
    }

    .icon-green {
        background: rgba(34, 197, 94, 0.2);
        color: #4ade80;
        box-shadow: 0 0 20px rgba(34, 197, 94, 0.4);
    }

    .icon-red {
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
    }

    .icon-blue {
        background: rgba(59, 130, 246, 0.2);
        color: #60a5fa;
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
    }

    .main-grid {
        display: grid;
        grid-template-columns: 2fr 1.2fr;
        gap: 15px;
        flex-grow: 1;
        min-height: 0;
    }

    .table-card {
        padding: 15px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .section-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        flex-wrap: wrap;
        gap: 10px;
        flex-shrink: 0;
    }

    .section-title h3 {
        color: #fff;
        font-size: 18px;
    }

    .add-btn {
        background: linear-gradient(135deg, #6C4EFF, #8A6DFF);
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: .3s;
        font-size: 13px;
    }

    .add-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(124, 92, 255, .45);
    }

    .table-body {
        flex-grow: 1;
        overflow-y: auto;
        overflow-x: hidden;
        max-height: 350px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead th {
        color: var(--muted);
        font-size: 12px;
        font-weight: 500;
        padding: 10px 5px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
        position: sticky;
        top: 0;
        background: #0b1830;
    }

    tbody td {
        padding: 12px 5px;
        border-bottom: 1px solid rgba(255, 255, 255, .05);
        color: #e5edff;
        font-size: 13px;
    }

    .type {
        padding: 4px 8px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 600;
    }

    .type.exp {
        color: var(--red);
        background: #FF4D6715;
    }

    .type.inc {
        color: var(--green);
        background: #00D26A15;
    }

    .amount-red {
        color: var(--red);
        font-weight: 700;
    }

    .amount-green {
        color: var(--green);
        font-weight: 700;
    }

    .icon-btn {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, .08);
        background: rgba(255, 255, 255, .03);
        color: #c8d7ff;
        cursor: pointer;
        transition: .25s;
        margin-right: 5px;
    }

    .icon-btn:hover {
        background: #3B82F6;
        color: #fff;
        border-color: #3B82F6;
    }

    .icon-btn.delete:hover {
        background: #FF4D67;
        border-color: #FF4D67;
    }

    .side {
        display: flex;
        flex-direction: column;
        gap: 15px;
        min-height: 0;
    }

    .side-card {
        padding: 15px;
    }

    .chart-card {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .legend {
        margin-top: 10px;
        overflow-y: auto;
        max-height: 100px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 13px;
    }

    .legend-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .summary-item {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 15px;
        padding: 12px;
        flex-shrink: 0;
    }

    .progress {
        height: 8px;
        background-color: #2c2c3e;
        border-radius: 20px;
        margin: 10px 0;
    }

    .progress-bar {
        height: 100%;
        border-radius: 20px;
        background-color: var(--green);
    }

    .filter-form {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .form-control-dark {
        background-color: #0b1830;
        border: 1px solid rgba(255, 255, 255, .1);
        color: #fff;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 13px;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.75);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(5px);
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: var(--card);
        border: 1px solid var(--border);
        backdrop-filter: blur(20px);
        color: #fff;
        border-radius: 18px;
        padding: 25px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 600;
    }

    .close-btn {
        background: none;
        border: none;
        color: #fff;
        font-size: 24px;
        cursor: pointer;
        opacity: 0.7;
    }

    .close-btn:hover {
        opacity: 1;
    }

    .form-control {
        background-color: #0b1830;
        border: 1px solid rgba(255, 255, 255, .1);
        color: #fff;
        padding: 10px;
        border-radius: 10px;
        width: 100%;
        box-sizing: border-box;
        margin-bottom: 15px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
    }

    .btn-secondary {
        background: #334155;
        color: #fff;
    }

    .btn-primary {
        background: var(--blue);
        color: #fff;
    }

    .btn-danger {
        background: var(--red);
        color: #fff;
    }

    .btn-warning {
        background: var(--yellow);
        color: #000;
    }

    .modal-footer {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 10px;
    }
</style>

<div class="money-container">
    <!-- TOP SUMMARY CARDS -->
    <div class="summary-grid">
        <div class="glass-card stat-card balance-card">
            <div class="icon-box icon-purple">💰</div>
            <div class="stat-title">Overall Balance (All Time)</div>
            <div class="stat-value" style="color: var(--purple);"><?php echo ($overall_balance < 0 ? '-' : ''); ?>RM <?php echo number_format(abs($overall_balance), 2); ?></div>
            <div class="stat-growth">Total Earned: RM <?php echo number_format($all_time_income, 2); ?></div>
        </div>
        <div class="glass-card stat-card income-card">
            <div class="icon-box icon-green">📈</div>
            <div class="stat-title">Total Income (<?php echo $filter_label; ?>)</div>
            <div class="stat-value" style="color: var(--green);">RM <?php echo number_format($total_income, 2); ?></div>
            <div class="stat-growth">Money received this period</div>
        </div>
        <div class="glass-card stat-card expense-card">
            <div class="icon-box icon-red">📉</div>
            <div class="stat-title">Total Expense (<?php echo $filter_label; ?>)</div>
            <div class="stat-value" style="color: var(--red);">RM <?php echo number_format($total_expense, 2); ?></div>
            <div class="stat-growth">Money spent this period</div>
        </div>
        <div class="glass-card stat-card savings-card">
            <div class="icon-box icon-blue">🏦</div>
            <div class="stat-title">Saved This Month</div>
            <div class="stat-value" style="color: var(--blue);"><?php echo ($saved_this_month < 0 ? '-' : ''); ?>RM <?php echo number_format(abs($saved_this_month), 2); ?></div>
            <div class="stat-growth">Income minus Expenses</div>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="main-grid">
        <!-- LEFT: TABLE -->
        <div class="glass-card table-card">
            <div class="section-title">
                <h3>Recent Transactions</h3>
                <div class="filter-form">
                    <form method="GET" action="" class="d-flex align-items-center" style="gap: 8px;">
                        <select name="month" class="form-control-dark" onchange="this.form.submit()">
                            <option value="all" <?php echo ($filter_label == "All Time") ? 'selected' : ''; ?>>All Time</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($i == $month) ? 'selected' : ''; ?>><?php echo $months[$i]; ?></option>
                            <?php endfor; ?>
                        </select>
                        <?php if ($filter_label != "All Time"): ?>
                            <select name="year" class="form-control-dark" onchange="this.form.submit()">
                                <?php $current_year = date('Y');
                                for ($y = $current_year; $y >= $current_year - 5; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        <?php endif; ?>
                    </form>
                    <a href="money.php" class="btn btn-sm btn-outline-light" style="color:#fff; border-color:rgba(255,255,255,.2); padding: 6px 12px; text-decoration:none;">Reset</a>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="filterTransactions('all')" style="color:#fff; border-color:rgba(255,255,255,.2);">All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="filterTransactions('income')" style="color:#fff; border-color:rgba(255,255,255,.2);">In</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="filterTransactions('expense')" style="color:#fff; border-color:rgba(255,255,255,.2);">Out</button>
                    </div>
                    <button type="button" class="add-btn" onclick="openAddModal()">+ Add</button>
                </div>
            </div>
            <div class="table-body">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="transactionTableBody">
                        <?php if (empty($recent_transactions)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--muted); padding: 20px;">No transactions found for this period.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_transactions as $tx): ?>
                                <tr data-type="<?php echo $tx['type']; ?>">
                                    <td><?php echo date('d M Y', strtotime($tx['transaction_date'])); ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: #fff; font-size: 14px;"><?php echo htmlspecialchars($tx['category']); ?></div>
                                        <?php if (!empty($tx['description'])): ?>
                                            <div style="font-size: 11px; color: var(--muted); margin-top: 2px;"><?php echo htmlspecialchars($tx['description']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="type <?php echo $tx['type'] == 'income' ? 'inc' : 'exp'; ?>"><?php echo ucfirst($tx['type']); ?></span></td>
                                    <td class="amount-<?php echo $tx['type'] == 'income' ? 'green' : 'red'; ?>">RM <?php echo number_format($tx['amount'], 2); ?></td>
                                    <td style="text-align:right;">
                                        <button class="icon-btn" onclick="openEditModal('<?php echo $tx['transaction_id']; ?>', '<?php echo $tx['type']; ?>', '<?php echo $tx['amount']; ?>', '<?php echo htmlspecialchars($tx['category'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($tx['description'], ENT_QUOTES); ?>', '<?php echo $tx['transaction_date']; ?>')">✏️</button>
                                        <button class="icon-btn delete" onclick="openDeleteModal('<?php echo $tx['transaction_id']; ?>')">🗑️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (count($transactions) > 5): ?>
                <div class="text-center mt-2" style="padding-top: 10px;">
                    <button class="btn btn-sm btn-outline-light" onclick="openViewAllModal()" style="color:#fff; border-color:rgba(255,255,255,.2);">View All (<?php echo count($transactions); ?>)</button>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: CHART & GOAL -->
        <div class="side">
            <div class="glass-card side-card chart-card">
                <h3 style="color:#fff; font-size:16px; margin-bottom:10px;">Expenses by Category</h3>
                <canvas id="expenseChart" style="max-height: 180px;"></canvas>
                <div class="legend">
                    <?php foreach ($chart_data as $index => $cat):
                        $color = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'][$index % 6];
                    ?>
                        <div class="legend-item">
                            <div class="legend-left">
                                <div class="dot" style="background:<?php echo $color; ?>"></div>
                                <span><?php echo htmlspecialchars($cat['category']); ?></span>
                            </div>
                            <span class="amount-red">RM <?php echo number_format($cat['total'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($chart_data)): ?>
                        <div class="legend-item">
                            <div class="legend-left">
                                <div class="dot" style="background:#64748b"></div><span>No Expenses</span>
                            </div><span>RM 0.00</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="glass-card side-card summary-item">
                <h3 style="color:#fff; font-size:16px; margin-bottom:10px;">🎯 Savings Goal</h3>
                <?php
                if ($filter_label != "All Time" && $goal_amount > 0) {
                    $current_month = date('n');
                    $current_year = date('Y');
                    $is_past = ($year < $current_year || ($year == $current_year && $month < $current_month));

                    if ($saved_this_month >= $goal_amount) {
                        echo "<p style='color:var(--green); font-size:13px; margin:0 0 10px 0;'>🎉 Goal Achieved! You hit your RM " . number_format($goal_amount, 2) . " target.</p>";
                    } elseif ($is_past) {
                        if ($saved_this_month < 0) {
                            echo "<p style='color:var(--red); font-size:13px; margin:0 0 10px 0;'>💡 Missed goal. Overspent by RM " . number_format(abs($saved_this_month), 2) . ".</p>";
                        } else {
                            $short_by = $goal_amount - $saved_this_month;
                            echo "<p style='color:var(--yellow); font-size:13px; margin:0 0 10px 0;'>💡 Missed goal. Fell short by RM " . number_format($short_by, 2) . ".</p>";
                        }
                    } else {
                        $remaining = $goal_amount - $saved_this_month;
                        if ($saved_this_month < 0) {
                            echo "<p style='color:var(--muted); font-size:13px; margin:0 0 10px 0;'>📊 Overspending. Adjust to hit goal.</p>";
                        } else {
                            echo "<p style='color:var(--blue); font-size:13px; margin:0 0 10px 0;'>📊 On track! Save RM " . number_format($remaining, 2) . " more.</p>";
                        }
                    }
                } else {
                    echo "<p style='color:var(--muted); font-size:13px; margin:0 0 10px 0;'>📊 No goal set for this month yet.</p>";
                }
                ?>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: <?php echo min(100, ($goal_amount > 0 ? ($saved_this_month / $goal_amount) * 100 : 0)); ?>%;"></div>
                </div>

                <!-- PROFESSIONAL LOCKED GOAL UI -->
                <?php if ($filter_label != "All Time"): ?>
                    <?php if ($goal_amount > 0): ?>
                        <!-- Goal is LOCKED. Show Change Button only. -->
                        <div class="text-center mt-2">
                            <button type="button" class="btn btn-warning" style="padding: 8px 15px; font-size: 13px;" onclick="openChangeGoalModal('<?php echo $goal_amount; ?>', '<?php echo $month; ?>', '<?php echo $year; ?>')">
                                🔒 Change Goal
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- No goal set yet. Show Input Form. -->
                        <form method="POST" action="money.php" class="d-flex" style="gap: 8px; margin-top: 5px;">
                            <input type="hidden" name="action" value="set_goal">
                            <input type="hidden" name="goal_month" value="<?php echo $month; ?>">
                            <input type="hidden" name="goal_year" value="<?php echo $year; ?>">
                            <input type="number" step="0.01" name="goal_amount" class="form-control" placeholder="Set Goal (RM)" required style="margin-bottom: 0;">
                            <button type="submit" class="btn btn-primary" style="padding: 8px 15px; font-size: 13px;">Set</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="font-size: 11px; color: var(--muted); margin: 0; text-align: center;">Select a specific month to set a goal.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ================= CUSTOM MODAL: VIEW ALL ================= -->
<div class="modal-overlay" id="viewAllModal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h5 class="modal-title">All Transactions (<?php echo $filter_label; ?>)</h5>
            <button class="close-btn" onclick="closeModal('viewAllModal')">&times;</button>
        </div>
        <div style="max-height: 60vh; overflow-y: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($tx['transaction_date'])); ?></td>
                            <td><?php echo htmlspecialchars($tx['category']); ?></td>
                            <td><span class="type <?php echo $tx['type'] == 'income' ? 'inc' : 'exp'; ?>"><?php echo ucfirst($tx['type']); ?></span></td>
                            <td class="amount-<?php echo $tx['type'] == 'income' ? 'green' : 'red'; ?>">RM <?php echo number_format($tx['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= CUSTOM MODAL: ADD TRANSACTION ================= -->
<div class="modal-overlay" id="addTransactionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Record New Transaction</h5>
            <button class="close-btn" onclick="closeModal('addTransactionModal')">&times;</button>
        </div>
        <form method="POST" action="money.php">
            <input type="hidden" name="action" value="create">
            <label style="font-size:14px; color:#9CA3AF;">Type</label>
            <select name="type" class="form-control" required>
                <option value="expense">Expense</option>
                <option value="income">Income</option>
            </select>
            <label style="font-size:14px; color:#9CA3AF;">Amount (RM)</label>
            <input type="number" step="0.01" name="amount" class="form-control" required>
            <label style="font-size:14px; color:#9CA3AF;">Category</label>
            <select name="category" class="form-control" required>
                <option value="Food">Food</option>
                <option value="Transport">Transport</option>
                <option value="Stationery">Stationery</option>
                <option value="Entertainment">Entertainment</option>
                <option value="Rent">Rent</option>
                <option value="Health">Health</option>
                <option value="Allowance">Allowance</option>
                <option value="Part Time">Part Time</option>
                <option value="Salary">Salary</option>
                <option value="Other">Other</option>
            </select>
            <label style="font-size:14px; color:#9CA3AF;">Description</label>
            <textarea name="description" class="form-control"></textarea>
            <label style="font-size:14px; color:#9CA3AF;">Date</label>
            <input type="date" name="transaction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addTransactionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Transaction</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= CUSTOM MODAL: EDIT TRANSACTION ================= -->
<div class="modal-overlay" id="editTransactionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Update Transaction</h5>
            <button class="close-btn" onclick="closeModal('editTransactionModal')">&times;</button>
        </div>
        <form method="POST" action="money.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <label style="font-size:14px; color:#9CA3AF;">Type</label>
            <select name="type" id="edit_type" class="form-control" required>
                <option value="expense">Expense</option>
                <option value="income">Income</option>
            </select>
            <label style="font-size:14px; color:#9CA3AF;">Amount (RM)</label>
            <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required>
            <label style="font-size:14px; color:#9CA3AF;">Category</label>
            <select name="category" id="edit_category" class="form-control" required>
                <option value="Food">Food</option>
                <option value="Transport">Transport</option>
                <option value="Stationery">Stationery</option>
                <option value="Entertainment">Entertainment</option>
                <option value="Rent">Rent</option>
                <option value="Health">Health</option>
                <option value="Allowance">Allowance</option>
                <option value="Part Time">Part Time</option>
                <option value="Salary">Salary</option>
                <option value="Other">Other</option>
            </select>
            <label style="font-size:14px; color:#9CA3AF;">Description</label>
            <textarea name="description" id="edit_description" class="form-control"></textarea>
            <label style="font-size:14px; color:#9CA3AF;">Date</label>
            <input type="date" name="transaction_date" id="edit_date" class="form-control" required>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editTransactionModal')">Cancel</button>
                <button type="submit" class="btn btn-warning">Update Transaction</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= CUSTOM MODAL: DELETE CONFIRMATION ================= -->
<div class="modal-overlay" id="deleteTransactionModal">
    <div class="modal-content" style="max-width: 450px; text-align: center;">
        <div class="modal-header" style="justify-content: center;">
            <h5 class="modal-title" style="color: var(--red);">Confirm Deletion</h5>
        </div>
        <p style="color: var(--muted); margin-bottom: 20px;">Are you sure you want to delete this transaction? This action cannot be undone.</p>
        <form method="POST" action="money.php">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="delete_id">
            <div class="modal-footer" style="justify-content: center;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteTransactionModal')">Cancel</button>
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= CUSTOM MODAL: CHANGE GOAL (PASSWORD REQUIRED) ================= -->
<div class="modal-overlay" id="changeGoalModal">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h5 class="modal-title" style="color: var(--yellow);">🔒 Authorize Goal Change</h5>
            <button class="close-btn" onclick="closeModal('changeGoalModal')">&times;</button>
        </div>
        <p style="color: var(--muted); margin-bottom: 20px; font-size: 14px;">For security, please enter your password to modify your savings goal.</p>
        <form method="POST" action="money.php">
            <input type="hidden" name="action" value="verify_and_update_goal">
            <input type="hidden" name="goal_month" id="change_goal_month">
            <input type="hidden" name="goal_year" id="change_goal_year">

            <label style="font-size:14px; color:#9CA3AF;">New Goal Amount (RM)</label>
            <input type="number" step="0.01" name="new_goal_amount" id="change_goal_amount" class="form-control" required>

            <label style="font-size:14px; color:#9CA3AF;">Your Password</label>
            <input type="password" name="password" class="form-control" required style="margin-bottom: 20px;">

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('changeGoalModal')">Cancel</button>
                <button type="submit" class="btn btn-warning">Authorize & Update</button>
            </div>
        </form>
    </div>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] == 'pass_wrong'): ?>
    <script>
        window.onload = function() {
            alert("Password incorrect! Goal was not updated.");
            window.history.replaceState({}, document.title, "money.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>");
        }
    </script>
<?php endif; ?>

<script>
    // PURE JS MODAL FUNCTIONS (BULLETPROOF)
    function openAddModal() {
        document.getElementById("addTransactionModal").classList.add("active");
    }

    function openViewAllModal() {
        document.getElementById("viewAllModal").classList.add("active");
    }

    function openEditModal(id, type, amount, category, description, date) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_type').value = type;
        document.getElementById('edit_amount').value = amount;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_date').value = date;
        document.getElementById("editTransactionModal").classList.add("active");
    }

    function openDeleteModal(id) {
        document.getElementById('delete_id').value = id;
        document.getElementById("deleteTransactionModal").classList.add("active");
    }

    // Open Change Goal Modal and pre-fill data
    function openChangeGoalModal(amount, month, year) {
        document.getElementById('change_goal_amount').value = amount;
        document.getElementById('change_goal_month').value = month;
        document.getElementById('change_goal_year').value = year;
        document.getElementById("changeGoalModal").classList.add("active");
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove("active");
    }

    // Close if clicking outside the modal
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove("active");
        }
    };

    function filterTransactions(type) {
        const rows = document.querySelectorAll('#transactionTableBody tr');
        rows.forEach(row => {
            if (type === 'all' || row.getAttribute('data-type') === type) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Chart.js Initialization
    const ctx = document.getElementById('expenseChart').getContext('2d');
    const categories = <?php echo json_encode(array_column($chart_data, 'category')); ?>;
    const totals = <?php echo json_encode(array_map('floatval', array_column($chart_data, 'total'))); ?>;
    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categories.length > 0 ? categories : ['No Expenses Yet'],
            datasets: [{
                data: totals.length > 0 ? totals : [1],
                backgroundColor: colors,
                borderColor: '#0b1830',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += 'RM ' + context.parsed.toFixed(2);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>

<?php
$pageContent = ob_get_clean();
include "layout.php";
?>