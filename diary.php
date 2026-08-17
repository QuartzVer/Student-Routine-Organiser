<?php
require('auth.php');
require('database.php');

$current_page = "diary.php";
ob_start();

$message = "";

// Success messages.
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') {
        $message = "<div class='alert alert-success'>Journal entry added successfully.</div>";
    } elseif ($_GET['success'] === 'updated') {
        $message = "<div class='alert alert-success'>Journal entry updated successfully.</div>";
    } elseif ($_GET['success'] === 'deleted') {
        $message = "<div class='alert alert-success'>Journal entry deleted successfully.</div>";
    }
}

$user_id = (int)$_SESSION['user_id'];

// Delete journal entry. The user_id condition prevents deleting another user's record.
if (isset($_GET['delete'])) {
    $diary_id = (int)$_GET['delete'];

    $stmt = mysqli_prepare(
        $con,
        "DELETE FROM diary WHERE diary_id = ? AND user_id = ?"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $diary_id, $user_id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($success) {
            header("Location: diary.php?success=deleted");
            exit();
        }
    }

    $message = "<div class='alert alert-danger'>Failed to delete journal entry.</div>";
}

// Add journal entry.
if (isset($_POST['add_diary'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $mood_status = trim($_POST['mood_status'] ?? '');
    $diary_date = trim($_POST['diary_date'] ?? '');

    $allowedMoods = ['Happy', 'Good', 'Neutral', 'Sad', 'Stressed', 'Angry'];

    if ($title === '' || $content === '' || $diary_date === '') {
        $message = "<div class='alert alert-danger'>Please complete all required fields.</div>";
    } elseif (!in_array($mood_status, $allowedMoods, true)) {
        $message = "<div class='alert alert-danger'>Please select a valid mood.</div>";
    } else {
        $stmt = mysqli_prepare(
            $con,
            "INSERT INTO diary (user_id, title, content, mood_status, diary_date)
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "issss",
                $user_id,
                $title,
                $content,
                $mood_status,
                $diary_date
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: diary.php?success=added");
                exit();
            }

            mysqli_stmt_close($stmt);
        }

        $message = "<div class='alert alert-danger'>Failed to add journal entry.</div>";
    }
}

// Update journal entry.
if (isset($_POST['update_diary'])) {
    $diary_id = (int)($_POST['diary_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $mood_status = trim($_POST['mood_status'] ?? '');
    $diary_date = trim($_POST['diary_date'] ?? '');

    $allowedMoods = ['Happy', 'Good', 'Neutral', 'Sad', 'Stressed', 'Angry'];

    if ($diary_id <= 0 || $title === '' || $content === '' || $diary_date === '') {
        $message = "<div class='alert alert-danger'>Please complete all required fields.</div>";
    } elseif (!in_array($mood_status, $allowedMoods, true)) {
        $message = "<div class='alert alert-danger'>Please select a valid mood.</div>";
    } else {
        $stmt = mysqli_prepare(
            $con,
            "UPDATE diary
             SET title = ?, content = ?, mood_status = ?, diary_date = ?
             WHERE diary_id = ? AND user_id = ?"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssii",
                $title,
                $content,
                $mood_status,
                $diary_date,
                $diary_id,
                $user_id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: diary.php?success=updated");
                exit();
            }

            mysqli_stmt_close($stmt);
        }

        $message = "<div class='alert alert-danger'>Failed to update journal entry.</div>";
    }
}

// Load the record being edited.
$edit_record = null;
if (isset($_GET['edit'])) {
    $diary_id = (int)$_GET['edit'];

    $stmt = mysqli_prepare(
        $con,
        "SELECT * FROM diary WHERE diary_id = ? AND user_id = ? LIMIT 1"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $diary_id, $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $edit_record = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}

// Retrieve the current user's journal entries.
$stmt = mysqli_prepare(
    $con,
    "SELECT diary_id, title, content, mood_status, diary_date
     FROM diary
     WHERE user_id = ?
     ORDER BY diary_date DESC, diary_id DESC"
);

$result = false;
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>

<div class="row">

    <!-- Add/Edit form -->
    <div class="col-md-4">
        <div class="card mb-4" id="diary-form">
            <div class="card-body">
                <?php echo $message; ?>

                <?php if ($edit_record): ?>
                    <h4 class="text-danger mb-4">
                        <i class="mdi mdi-pencil"></i>
                        Edit Journal Entry
                    </h4>

                    <form method="POST" action="">
                        <input type="hidden" name="diary_id" value="<?php echo (int)$edit_record['diary_id']; ?>">

                        <div class="form-group">
                            <label class="text-white">Journal Date</label>
                            <input type="date"
                                name="diary_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($edit_record['diary_date']); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="text-white">Title</label>
                            <input type="text"
                                name="title"
                                class="form-control"
                                maxlength="150"
                                value="<?php echo htmlspecialchars($edit_record['title']); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="text-white">Mood</label>
                            <select name="mood_status" class="form-control" required>
                                <?php
                                $moods = ['Happy', 'Good', 'Neutral', 'Sad', 'Stressed', 'Angry'];
                                foreach ($moods as $mood):
                                ?>
                                    <option value="<?php echo $mood; ?>"
                                        <?php echo ($edit_record['mood_status'] === $mood) ? 'selected' : ''; ?>>
                                        <?php echo $mood; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="text-white">Journal Content</label>
                            <textarea name="content"
                                class="form-control"
                                rows="7"
                                maxlength="5000"
                                required><?php echo htmlspecialchars($edit_record['content']); ?></textarea>
                        </div>

                        <div class="d-flex" style="gap: 8px;">
                            <button type="submit" name="update_diary" class="btn btn-warning">
                                <i class="mdi mdi-content-save"></i>
                                Update Entry
                            </button>
                            <a href="diary.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                <?php else: ?>
                    <h4 class="text-danger mb-4">
                        <i class="mdi mdi-book-open-page-variant"></i>
                        Add Journal Entry
                    </h4>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="text-white">Journal Date</label>
                            <input type="date"
                                name="diary_date"
                                class="form-control"
                                value="<?php echo date('Y-m-d'); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="text-white">Title</label>
                            <input type="text"
                                name="title"
                                class="form-control"
                                maxlength="150"
                                placeholder="How was your day?"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="text-white">Mood</label>
                            <select name="mood_status" class="form-control" required>
                                <option value="Happy">Happy</option>
                                <option value="Good">Good</option>
                                <option value="Neutral" selected>Neutral</option>
                                <option value="Sad">Sad</option>
                                <option value="Stressed">Stressed</option>
                                <option value="Angry">Angry</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="text-white">Journal Content</label>
                            <textarea name="content"
                                class="form-control"
                                rows="7"
                                maxlength="5000"
                                placeholder="Write about your thoughts, experiences, or anything you would like to remember..."
                                required></textarea>
                        </div>

                        <button type="submit" name="add_diary" class="btn btn-danger btn-block">
                            <i class="mdi mdi-plus"></i>
                            Add Journal Entry
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Journal records -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h4 class="text-white mb-4">
                    <i class="mdi mdi-book-multiple text-danger"></i>
                    My Journal Entries
                </h4>

                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Title</th>
                                <th>Mood</th>
                                <th>Content</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['diary_date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td>
                                            <?php
                                            $moodClass = 'text-info';
                                            if ($row['mood_status'] === 'Happy' || $row['mood_status'] === 'Good') {
                                                $moodClass = 'text-success';
                                            } elseif ($row['mood_status'] === 'Sad' || $row['mood_status'] === 'Stressed' || $row['mood_status'] === 'Angry') {
                                                $moodClass = 'text-danger';
                                            }
                                            ?>
                                            <span class="<?php echo $moodClass; ?>">
                                                <?php echo htmlspecialchars($row['mood_status']); ?>
                                            </span>
                                        </td>
                                        <td style="min-width: 220px; max-width: 350px;">
                                            <?php
                                            $preview = $row['content'];
                                            if (strlen($preview) > 120) {
                                                $preview = substr($preview, 0, 120) . '...';
                                            }
                                            echo nl2br(htmlspecialchars($preview));
                                            ?>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <a href="diary.php?edit=<?php echo (int)$row['diary_id']; ?>#diary-form"
                                                class="btn btn-warning btn-sm">
                                                Edit
                                            </a>
                                            <a href="diary.php?delete=<?php echo (int)$row['diary_id']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this journal entry?');">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No journal entries found. Add your first entry using the form.
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

<?php
if ($stmt) {
    mysqli_stmt_close($stmt);
}
$pageContent = ob_get_clean();
include "layout.php";
?>
