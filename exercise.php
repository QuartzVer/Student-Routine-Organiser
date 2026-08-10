<?php
require('auth.php');
$current_page = "exercise.php";
ob_start();
require('database.php');

//popup message
$message = "";
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'added') {
        $message = "<div class='alert alert-success'>Exercise added successfully.</div>";
    } elseif ($_GET['success'] == 'updated') {
        $message = "<div class='alert alert-success'>Exercise updated successfully.</div>";
    } elseif ($_GET['success'] == 'deleted') {
        $message = "<div class='alert alert-success'>Exercise deleted successfully.</div>";
    } elseif ($_GET['success'] == 'goal_set') {
        $message = "<div class='alert alert-success'>Goal updated successfully.</div>";
    }
}

//delete
if (isset($_GET['delete'])) {

    $exercise_id = (int)$_GET['delete'];

    $query = "DELETE FROM exercise WHERE exercise_id = $exercise_id AND user_id = " . $_SESSION['user_id'];

    if (mysqli_query($con, $query)) {
        header("Location: exercise.php?success=deleted");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Failed to delete exercise.</div>";
    }
}

//insert
if (isset($_POST['add_exercise'])) {

    $activity_type = mysqli_real_escape_string(
        $con,
        $_POST['activity_type']
    );

    $duration = (int)$_POST['duration'];
    $calories_burned = (int)$_POST['calories_burned'];


    $intensity_level = mysqli_real_escape_string(
        $con,
        $_POST['intensity_level']
    );

    $exercise_date = mysqli_real_escape_string(
        $con,
        $_POST['exercise_date']
    );


    if ($duration <= 0 || $calories_burned <= 0) {

        $message = "
    <div class='alert alert-danger'>
        Duration and Calories Burned must be greater than 0.
    </div>";
    } else {
        $user_id = $_SESSION['user_id'];

        $query = "INSERT INTO exercise (user_id, activity_type, duration, calories_burned, intensity_level, exercise_date, activity_status)
        VALUES ('$user_id', '$activity_type', '$duration', '$calories_burned', '$intensity_level', '$exercise_date', 'Scheduled')";


        if (mysqli_query($con, $query)) {
            header("Location: exercise.php?success=added");
            exit();
        } else {

            $message = "<div class='alert alert-danger'>
                        Failed to add exercise.
                    </div>";
        }
    }
}

//update
if (isset($_POST['update_exercise'])) {

    $exercise_id = (int)$_POST['exercise_id'];
    $user_id = $_SESSION['user_id'];

    $activity_type = mysqli_real_escape_string(
        $con,
        $_POST['activity_type']
    );

    $duration = (int)$_POST['duration'];

    $calories_burned = (int)$_POST['calories_burned'];

    $intensity_level = mysqli_real_escape_string(
        $con,
        $_POST['intensity_level']
    );

    $exercise_date = mysqli_real_escape_string(
        $con,
        $_POST['exercise_date']
    );

    $activity_status = mysqli_real_escape_string(
        $con,
        $_POST['activity_status']
    );

    if ($duration <= 0 || $calories_burned <= 0) {

        $message = "
    <div class='alert alert-danger'>
        Duration and Calories Burned must be greater than 0.
    </div>";
    } else {

        $query = "UPDATE exercise SET
            activity_type = '$activity_type',
            duration = '$duration',
            calories_burned = '$calories_burned',
            intensity_level = '$intensity_level',
            exercise_date = '$exercise_date',
            activity_status = '$activity_status'
          WHERE exercise_id = $exercise_id
          AND user_id = " . $user_id;


        if (mysqli_query($con, $query)) {
            header("Location: exercise.php?success=updated");
            exit();
        } else {

            $message = "<div class='alert alert-danger'>
                        Failed to update exercise.
                    </div>";
        }
    }
}
//for editing record
$edit_record = null;
if (isset($_GET['edit'])) {

    $exercise_id = (int)$_GET['edit'];

    $query = "SELECT * FROM exercise
          WHERE exercise_id = $exercise_id
          AND user_id = " . $_SESSION['user_id'];

    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {

        $edit_record = mysqli_fetch_assoc($result);
    }
}

//set goal
if (isset($_POST['set_goal'])) {

    $period = mysqli_real_escape_string($con, $_POST['period']);
    $target_value = (int)$_POST['target_value'];
    $user_id = $_SESSION['user_id'];

    if ($target_value <= 0) {
        $message = "<div class='alert alert-danger'>Goal target must be greater than 0.</div>";
    } else {

        $check_query = "SELECT goal_id FROM exercise_goals WHERE user_id = '$user_id'";
        $check_result = mysqli_query($con, $check_query);

        if ($check_result && mysqli_num_rows($check_result) > 0) {

            // Goal already exists - just update target/period.
            $query = "UPDATE exercise_goals SET
                        period = '$period',
                        target_value = '$target_value'
                      WHERE user_id = '$user_id'";
        } else {

            $query = "INSERT INTO exercise_goals (user_id, period, target_value)
                      VALUES ('$user_id', '$period', '$target_value')";
        }

        if (mysqli_query($con, $query)) {
            header("Location: exercise.php?success=goal_set");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Failed to set goal.</div>";
        }
    }
}

//to update exercises missed
$user_id = $_SESSION['user_id'];
mysqli_query($con, "
    UPDATE exercise
    SET activity_status = 'Missed'
    WHERE user_id = '$user_id'
    AND exercise_date < CURDATE()
    AND activity_status = 'Scheduled'
");

//get record based on current user
$query = "SELECT *
          FROM exercise
          WHERE user_id = '$user_id'
          ORDER BY exercise_date DESC, exercise_id DESC";

$result = mysqli_query($con, $query);

//exercise status overview
$status_counts = ['Scheduled' => 0, 'Completed' => 0, 'Missed' => 0];
$status_query = "SELECT activity_status, COUNT(*) as cnt
                  FROM exercise
                  WHERE user_id = '$user_id'
                  GROUP BY activity_status";

$status_result = mysqli_query($con, $status_query);
while ($row = mysqli_fetch_assoc($status_result)) {
    $status_counts[$row['activity_status']] = (int)$row['cnt'];
}
$status_total = array_sum($status_counts);
$status_total = $status_total > 0 ? $status_total : 1; // avoid divide by zero

//calorie goal
$active_goal = null;
$goal_query = "SELECT * FROM exercise_goals WHERE user_id = '$user_id' LIMIT 1";
$goal_result = mysqli_query($con, $goal_query);
if ($goal_result && mysqli_num_rows($goal_result) > 0) {
    $active_goal = mysqli_fetch_assoc($goal_result);

    $today = new DateTime();

    if ($active_goal['period'] == 'weekly') {
        $period_start = (clone $today)->modify('monday this week')->format('Y-m-d');
        $period_end   = (clone $today)->modify('sunday this week')->format('Y-m-d');
    } else {
        $period_start = $today->format('Y-m-01');
        $period_end   = $today->format('Y-m-t');
    }

    $active_goal['period_start'] = $period_start;
    $active_goal['period_end'] = $period_end;

    $progress_query = "SELECT SUM(calories_burned) as total
                        FROM exercise
                        WHERE user_id = '$user_id'
                        AND activity_status = 'Completed'
                        AND exercise_date BETWEEN '$period_start' AND '$period_end'";

    $progress_result = mysqli_query($con, $progress_query);
    $progress_row = mysqli_fetch_assoc($progress_result);

    $active_goal['current_value'] = $progress_row['total'] ? (int)$progress_row['total'] : 0;
    $active_goal['percent'] = min(100, round(($active_goal['current_value'] / $active_goal['target_value']) * 100));
}
?>



<!--content-->
<div class="row">

    <!--exercise form-->
    <div class="col-md-4">

        <div class="card mb-4" id="exercise-form">

            <div class="card-body">
                <?php echo $message; ?>
                <?php if ($edit_record): ?>
                    <h4 class="text-warning mb-4">
                        <i class="mdi mdi-pencil"></i>
                        Edit Exercise
                    </h4>

                    <form action="" method="POST">
                        <input type="hidden"
                            name="exercise_id"
                            value="<?php echo $edit_record['exercise_id']; ?>">

                        <div class="form-group">
                            <label class="text-white">
                                Exercise Date
                            </label>
                            <input type="date"
                                name="exercise_date"
                                class="form-control"
                                value="<?php echo $edit_record['exercise_date']; ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="text-white">
                                Activity Type
                            </label>
                            <input type="text"
                                name="activity_type"
                                class="form-control"
                                value="<?php echo htmlspecialchars($edit_record['activity_type']); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="text-white">
                                Duration (minutes)
                            </label>
                            <input type="number"
                                name="duration"
                                class="form-control"
                                min="1"
                                value="<?php echo $edit_record['duration']; ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="text-white">
                                Calories Burned
                            </label>
                            <input type="number"
                                name="calories_burned"
                                class="form-control"
                                min="1"
                                value="<?php echo $edit_record['calories_burned']; ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="text-white">
                                Intensity Level
                            </label>
                            <select name="intensity_level"
                                class="form-control">

                                <option value="Low"
                                    <?php if ($edit_record['intensity_level'] == 'Low') echo 'selected'; ?>>
                                    Low
                                </option>

                                <option value="Medium"
                                    <?php if ($edit_record['intensity_level'] == 'Medium') echo 'selected'; ?>>
                                    Medium
                                </option>

                                <option value="High"
                                    <?php if ($edit_record['intensity_level'] == 'High') echo 'selected'; ?>>
                                    High
                                </option>

                            </select>
                        </div>

                        <div class="form-group">
                            <label class="text-white">
                                Activity Status
                            </label>
                            <select name="activity_status" class="form-control">

                                <option value="Scheduled"
                                    <?php if ($edit_record['activity_status'] == 'Scheduled') echo 'selected'; ?>>
                                    Scheduled
                                </option>

                                <option value="Completed"
                                    <?php if ($edit_record['activity_status'] == 'Completed') echo 'selected'; ?>>
                                    Completed
                                </option>

                                <option value="Missed"
                                    <?php if ($edit_record['activity_status'] == 'Missed') echo 'selected'; ?>>
                                    Missed
                                </option>

                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button
                                type="submit"
                                name="update_exercise"
                                class="btn btn-warning">
                                <i class="mdi mdi-content-save"></i>
                                Update Exercise
                            </button>

                            <a href="exercise.php"
                                class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                <?php else: ?>

                    <h4 class="text-warning mb-4">
                        <i class="mdi mdi-run-fast"></i>
                        Add Exercise
                    </h4>

                    <form action="" method="POST">

                        <div class="form-group">
                            <label class="text-white">
                                Activity Type
                            </label>
                            <input type="text"
                                name="activity_type"
                                class="form-control"
                                placeholder="Jogging, Cycling, Gym"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="text-white">
                                Duration (minutes)
                            </label>
                            <input type="number"
                                name="duration"
                                class="form-control"
                                min="1"
                                placeholder="30"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="text-white">
                                Calories Burned
                            </label>
                            <input type="number"
                                name="calories_burned"
                                class="form-control"
                                min="1"
                                placeholder="200"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="text-white">
                                Intensity Level
                            </label>
                            <select name="intensity_level"
                                class="form-control">

                                <option value="Low">
                                    Low
                                </option>

                                <option value="Medium">
                                    Medium
                                </option>

                                <option value="High">
                                    High
                                </option>

                            </select>
                        </div>

                        <div class="form-group">
                            <label class="text-white">
                                Exercise Date
                            </label>
                            <input type="date"
                                name="exercise_date"
                                class="form-control"
                                required>
                        </div>

                        <button type="submit"
                            name="add_exercise"
                            class="btn btn-warning btn-block">
                            <i class="mdi mdi-plus"></i>
                            Add Exercise
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>



    <!--overview, goal and records-->
    <div class="col-md-8">

        <!--overview -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="text-white mb-3">Exercise Status Overview</h5>
                <div class="progress" style="height: 25px;">
                    <?php
                    $sched_pct = round(($status_counts['Scheduled'] / $status_total) * 100);
                    $comp_pct = round(($status_counts['Completed'] / $status_total) * 100);
                    $miss_pct = round(($status_counts['Missed'] / $status_total) * 100);
                    ?>
                    <div class="progress-bar bg-info" style="width: <?php echo $sched_pct; ?>%"
                        title="Scheduled: <?php echo $status_counts['Scheduled']; ?>">
                        <?php echo $status_counts['Scheduled'] > 0 ? 'Scheduled ' . $status_counts['Scheduled'] : ''; ?>
                    </div>
                    <div class="progress-bar bg-success" style="width: <?php echo $comp_pct; ?>%"
                        title="Completed: <?php echo $status_counts['Completed']; ?>">
                        <?php echo $status_counts['Completed'] > 0 ? 'Completed ' . $status_counts['Completed'] : ''; ?>
                    </div>
                    <div class="progress-bar bg-danger" style="width: <?php echo $miss_pct; ?>%"
                        title="Missed: <?php echo $status_counts['Missed']; ?>">
                        <?php echo $status_counts['Missed'] > 0 ? 'Missed ' . $status_counts['Missed'] : ''; ?>
                    </div>
                </div>
            </div>
        </div>

        <!--goal-->
        <div class="card mb-3">
            <div class="card-body">

                <div class="d-flex align-items-center mb-3" style="gap: 10px;">
                    <h5 class="text-white mb-0">
                        <?php if ($active_goal): ?>
                            <?php echo ucfirst($active_goal['period']); ?> Calorie Goal
                        <?php else: ?>
                            Calorie Goal
                        <?php endif; ?>
                    </h5>

                    <a href="#"
                        data-toggle="modal"
                        data-target="#goalModal"
                        title="Edit goal"
                        style="display: inline-flex; align-items: center; justify-content: center;
                               width: 28px; height: 28px; border-radius: 50%;
                               background-color: #3a3f4b; color: #ffc107;
                               text-decoration: none; flex-shrink: 0;">
                        <i class="mdi mdi-pencil" style="font-size: 15px; line-height: 1;"></i>
                    </a>
                </div>

                <?php if ($active_goal): ?>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-warning" style="width: <?php echo $active_goal['percent']; ?>%">
                            <?php echo $active_goal['percent']; ?>%
                        </div>
                    </div>
                    <p class="text-muted small mt-2 mb-0">
                        <?php echo $active_goal['current_value']; ?> /
                        <?php echo $active_goal['target_value']; ?> kcal
                        &middot;
                        <?php echo date('M j', strtotime($active_goal['period_start'])); ?>
                        - <?php echo date('M j', strtotime($active_goal['period_end'])); ?>
                    </p>
                <?php else: ?>
                    <p class="text-muted mb-0">
                        No goal set yet. Click the pencil to set one.
                    </p>
                <?php endif; ?>

            </div>
        </div>


        <div class="card">

            <div class="card-body">

                <h4 class="text-white mb-4">
                    Exercise Records
                </h4>

                <div class="table-responsive">

                    <table class="table table-dark table-hover">

                        <thead>
                            <tr>
                                <th>
                                    Exercise Date
                                </th>

                                <th>
                                    Activity
                                </th>

                                <th>
                                    Duration
                                </th>

                                <th>
                                    Calories
                                </th>

                                <th>
                                    Intensity
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if ($result && mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                            ?>

                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($row['exercise_date']); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['activity_type']); ?>
                                        </td>

                                        <td>
                                            <?php echo $row['duration']; ?> min
                                        </td>

                                        <td>
                                            <?php echo $row['calories_burned']; ?> kcal
                                        </td>

                                        <td>
                                            <?php
                                            if ($row['intensity_level'] == 'Low') {
                                                echo '<span class="text-success">Low</span>';
                                            } elseif ($row['intensity_level'] == 'Medium') {
                                                echo '<span class="text-warning">Medium</span>';
                                            } else {
                                                echo '<span class="text-danger">High</span>';
                                            }
                                            ?>
                                        </td>

                                        <td>
                                            <?php if ($row['activity_status'] == 'Scheduled'): ?>
                                                <span class="text-info">
                                                    <i class="mdi mdi-clock-outline"></i>
                                                    Scheduled
                                                </span>
                                            <?php elseif ($row['activity_status'] == 'Completed'): ?>
                                                <span class="text-success">
                                                    <i class="mdi mdi-check-circle"></i>
                                                    Completed
                                                </span>
                                            <?php elseif ($row['activity_status'] == 'Missed'): ?>
                                                <span class="text-danger">
                                                    <i class="mdi mdi-close-circle"></i>
                                                    Missed
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <a href="exercise.php?edit=<?php echo $row['exercise_id']; ?>#exercise-form"
                                                class="btn btn-warning btn-sm">
                                                Edit
                                            </a>

                                            <a href="exercise.php?delete=<?php echo $row['exercise_id']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this exercise?');">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php

                                endwhile;

                            else:

                                ?>

                                <tr>
                                    <td colspan="7"
                                        class="text-center text-muted">
                                        No exercise records found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!--pencil to edit goal-->
<div class="modal fade" id="goalModal" tabindex="-1" role="dialog" aria-labelledby="goalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="goalModalLabel">Set Calorie Goal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="" method="POST">
                <div class="modal-body">

                    <div class="form-group">
                        <label>Period</label>
                        <select name="period" class="form-control">
                            <option value="weekly" <?php if ($active_goal && $active_goal['period'] == 'weekly') echo 'selected'; ?>>
                                Weekly
                            </option>
                            <option value="monthly" <?php if ($active_goal && $active_goal['period'] == 'monthly') echo 'selected'; ?>>
                                Monthly
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Target (kcal)</label>
                        <input type="number"
                            name="target_value"
                            class="form-control"
                            min="1"
                            value="<?php echo $active_goal ? $active_goal['target_value'] : ''; ?>"
                            required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="set_goal" class="btn btn-warning">Save Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
include "layout.php";
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (window.location.hash === "#exercise-form") {
            setTimeout(function() {
                document.getElementById("exercise-form").scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            }, 100);
        }
    });
</script>