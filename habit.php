<?php

require('auth.php');
require('database.php');

$current_page = "habit.php";
$user_id = (int) $_SESSION['user_id'];

$errors = [];

// CSRF token for this session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================================
   DELETE HABIT
   ========================================= */
if (isset($_GET['delete'])) {

    $delete_id = (int) $_GET['delete'];

    if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
        $errors[] = "Invalid request.";
    } else {
        $stmt = mysqli_prepare($con, "DELETE FROM habits WHERE habit_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $delete_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: habit.php?success=deleted");
        exit();
    }
}

/* =========================================
   ADD HABIT
   ========================================= */
if (isset($_POST['add_habit'])) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission. Please try again.";
    } else {

        $habit_name        = trim($_POST['habit_name']);
        $target_frequency  = trim($_POST['target_frequency']);
        $completion_status = $_POST['completion_status'];
        $habit_date        = $_POST['habit_date'];
        $notes             = trim($_POST['notes']);

        if ($habit_name === '') $errors[] = "Habit name is required.";
        if (strlen($habit_name) > 100) $errors[] = "Habit name is too long (max 100 characters).";
        if ($target_frequency === '') $errors[] = "Target frequency is required.";
        if (!in_array($completion_status, ['Pending', 'Completed'], true)) $errors[] = "Invalid status.";
        if ($habit_date === '' || !DateTime::createFromFormat('Y-m-d', $habit_date)) $errors[] = "Please provide a valid date.";

        if (empty($errors)) {
            $stmt = mysqli_prepare(
                $con,
                "INSERT INTO habits (user_id, habit_name, target_frequency, completion_status, habit_date, notes)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $stmt,
                "isssss",
                $user_id,
                $habit_name,
                $target_frequency,
                $completion_status,
                $habit_date,
                $notes
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: habit.php?success=added");
                exit();
            } else {
                $errors[] = "Failed to add habit. Please try again.";
            }
        }
    }
}

/* =========================================
   EDIT HABIT
   ========================================= */
if (isset($_POST['edit_habit'])) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission. Please try again.";
    } else {

        $habit_id           = (int) $_POST['habit_id'];
        $habit_name        = trim($_POST['habit_name']);
        $target_frequency  = trim($_POST['target_frequency']);
        $completion_status = $_POST['completion_status'];
        $habit_date        = $_POST['habit_date'];
        $notes             = trim($_POST['notes']);

        if ($habit_name === '') $errors[] = "Habit name is required.";
        if ($target_frequency === '') $errors[] = "Target frequency is required.";
        if (!in_array($completion_status, ['Pending', 'Completed'], true)) $errors[] = "Invalid status.";
        if ($habit_date === '' || !DateTime::createFromFormat('Y-m-d', $habit_date)) $errors[] = "Please provide a valid date.";

        if (empty($errors)) {
            // user_id in the WHERE clause ensures a user can only edit their own habit
            $stmt = mysqli_prepare(
                $con,
                "UPDATE habits
                 SET habit_name = ?, target_frequency = ?, completion_status = ?, habit_date = ?, notes = ?
                 WHERE habit_id = ? AND user_id = ?"
            );
            mysqli_stmt_bind_param(
                $stmt,
                "sssssii",
                $habit_name,
                $target_frequency,
                $completion_status,
                $habit_date,
                $notes,
                $habit_id,
                $user_id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: habit.php?success=updated");
                exit();
            } else {
                $errors[] = "Failed to update habit. Please try again.";
            }
        }
    }
}

/* =========================================
   FETCH HABITS (with sorting)
   ========================================= */
$allowed_sort  = ['habit_name', 'target_frequency', 'completion_status', 'habit_date'];
$allowed_order = ['ASC', 'DESC'];

$sort  = $_GET['sort'] ?? 'habit_date';
$order = strtoupper($_GET['order'] ?? 'DESC');

if (!in_array($sort, $allowed_sort, true))  $sort = 'habit_date';
if (!in_array($order, $allowed_order, true)) $order = 'DESC';

$next_order = ($order === 'ASC') ? 'DESC' : 'ASC';

$stmt = mysqli_prepare($con, "SELECT * FROM habits WHERE user_id = ? ORDER BY $sort $order");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$habits_result = mysqli_stmt_get_result($stmt);
$habit_count = mysqli_num_rows($habits_result);

ob_start();
?>

<div class="col-12">

    <?php if (isset($_GET['success'])):
        $msgs = [
            'added'   => 'Habit added successfully.',
            'updated' => 'Habit updated successfully.',
            'deleted' => 'Habit deleted successfully.',
        ];
    ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($msgs[$_GET['success']] ?? 'Done.'); ?>
        </div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endforeach; ?>

    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-title mb-0">
                    <i class="mdi mdi-checkbox-marked-circle-outline text-success"></i>
                    My Habits
                </h4>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addHabitModal">
                    <i class="mdi mdi-plus"></i> Add Habit
                </button>
            </div>

            <?php if ($habit_count === 0): ?>
                <p class="text-muted text-center py-4">No habits yet. Click "Add Habit" to create your first one.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>
                                    <a href="?sort=habit_name&order=<?php echo $sort === 'habit_name' ? $next_order : 'ASC'; ?>"
                                        style="text-decoration: none; color: inherit;">
                                        Habit Name
                                        <i class="mdi mdi-sort" style="color: white;"></i>
                                    </a>
                                </th>

                                <th>
                                    <a href="?sort=target_frequency&order=<?php echo $sort === 'target_frequency' ? $next_order : 'ASC'; ?>"
                                        style="text-decoration: none; color: inherit;">
                                        Target Frequency
                                        <i class="mdi mdi-sort" style="color: white;"></i>
                                    </a>
                                </th>

                                <th>
                                    <a href="?sort=completion_status&order=<?php echo $sort === 'completion_status' ? $next_order : 'ASC'; ?>"
                                        style="text-decoration: none; color: inherit;">
                                        Status
                                        <i class="mdi mdi-sort" style="color: white;"></i>
                                    </a>
                                </th>

                                <th>
                                    <a href="?sort=habit_date&order=<?php echo $sort === 'habit_date' ? $next_order : 'ASC'; ?>"
                                        style="text-decoration: none; color: inherit;">
                                        Date
                                        <i class="mdi mdi-sort" style="color: white;"></i>
                                    </a>
                                </th>

                                <th>Notes</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($habit = mysqli_fetch_assoc($habits_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($habit['habit_name']); ?></td>
                                    <td><?php echo htmlspecialchars($habit['target_frequency']); ?></td>
                                    <td>
                                        <?php if ($habit['completion_status'] === 'Completed'): ?>
                                            <label class="badge badge-success">Completed</label>
                                        <?php else: ?>
                                            <label class="badge badge-warning">Pending</label>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($habit['habit_date']); ?></td>
                                    <td><?php echo htmlspecialchars($habit['notes'] !== null && $habit['notes'] !== '' ? $habit['notes'] : '-'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-inverse-warning btn-icon" data-toggle="modal" data-target="#editHabitModal<?php echo $habit['habit_id']; ?>">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <a class="btn btn-inverse-danger btn-icon"
                                            style="display: inline-flex; align-items: center; justify-content: center;"
                                            href="habit.php?delete=<?php echo $habit['habit_id']; ?>&token=<?php echo urlencode($_SESSION['csrf_token']); ?>"
                                            onclick="return confirm('Delete this habit record?');">
                                            <i class="mdi mdi-delete"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- Edit modal for habit #<?php echo $habit['habit_id']; ?> -->
                                <div class="modal fade" id="editHabitModal<?php echo $habit['habit_id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="POST" action="habit.php">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Habit</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                    <input type="hidden" name="habit_id" value="<?php echo $habit['habit_id']; ?>">

                                                    <div class="form-group">
                                                        <label>Habit Name</label>
                                                        <input type="text" name="habit_name" class="form-control" maxlength="100" required
                                                            value="<?php echo htmlspecialchars($habit['habit_name']); ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Target Frequency</label>
                                                        <input type="text" name="target_frequency" class="form-control" maxlength="50" required
                                                            value="<?php echo htmlspecialchars($habit['target_frequency']); ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Completion Status</label>
                                                        <select name="completion_status" class="form-control">
                                                            <option value="Pending" <?php echo $habit['completion_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="Completed" <?php echo $habit['completion_status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Date</label>
                                                        <input type="date" name="habit_date" class="form-control" required
                                                            value="<?php echo htmlspecialchars($habit['habit_date']); ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Notes (optional)</label>
                                                        <textarea name="notes" class="form-control" rows="2" maxlength="255"><?php echo htmlspecialchars($habit['notes'] ?? ''); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="edit_habit" class="btn btn-primary">Save Changes</button>
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Add Habit Modal -->
<div class="modal fade" id="addHabitModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="habit.php">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Habit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="form-group">
                        <label>Habit Name</label>
                        <input type="text" name="habit_name" class="form-control" maxlength="100" required placeholder="e.g. Drink 2L water">
                    </div>
                    <div class="form-group">
                        <label>Target Frequency</label>
                        <input type="text" name="target_frequency" class="form-control" maxlength="50" required placeholder="e.g. Daily, 3x/week">
                    </div>
                    <div class="form-group">
                        <label>Completion Status</label>
                        <select name="completion_status" class="form-control">
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="habit_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_habit" class="btn btn-primary">Save Habit</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php

$pageContent = ob_get_clean();

include "layout.php";

?>