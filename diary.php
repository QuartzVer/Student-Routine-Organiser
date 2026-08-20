<?php
require('auth.php');
require('database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_page = "diary.php";

$user_id = (int)$_SESSION['user_id'];

$message = "";
$message_type = "";

$edit_id = 0;
$edit_title = "";
$edit_content = "";
$edit_mood = "Neutral";
$edit_date = date("Y-m-d");

if (isset($_POST['add_diary'])) {

    $title = trim($_POST['title'] ?? "");
    $content = trim($_POST['content'] ?? "");
    $mood = trim($_POST['mood_status'] ?? "Neutral");
    $diary_date = $_POST['diary_date'] ?? date("Y-m-d");

    if ($title === "") {

        $message = "Please enter a title.";
        $message_type = "danger";

    } elseif ($content === "") {

        $message = "Please enter your journal content.";
        $message_type = "danger";

    } elseif ($diary_date === "") {

        $message = "Please select a date.";
        $message_type = "danger";

    } else {

        $stmt = mysqli_prepare(
            $con,
            "INSERT INTO diary
            (user_id, title, content, mood_status, diary_date)
            VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "issss",
                $user_id,
                $title,
                $content,
                $mood,
                $diary_date
            );

            if (mysqli_stmt_execute($stmt)) {

               header("Location: diary.php");
               exit();

            } else {

                $message = "Unable to save the journal entry.";
                $message_type = "danger";
            }

            mysqli_stmt_close($stmt);

        } else {

            $message = "Database error.";
            $message_type = "danger";
        }
    }
}

if (isset($_POST['update_diary'])) {

    $diary_id = (int)($_POST['diary_id'] ?? 0);
    $title = trim($_POST['title'] ?? "");
    $content = trim($_POST['content'] ?? "");
    $mood = trim($_POST['mood_status'] ?? "Neutral");
    $diary_date = $_POST['diary_date'] ?? date("Y-m-d");

    if ($diary_id <= 0) {

        $message = "Invalid journal entry.";
        $message_type = "danger";

    } elseif ($title === "") {

        $message = "Please enter a title.";
        $message_type = "danger";

    } elseif ($content === "") {

        $message = "Please enter your journal content.";
        $message_type = "danger";

    } elseif ($diary_date === "") {

        $message = "Please select a date.";
        $message_type = "danger";

    } else {

        $stmt = mysqli_prepare(
            $con,
            "UPDATE diary
             SET title = ?,
                 content = ?,
                 mood_status = ?,
                 diary_date = ?
             WHERE diary_id = ?
             AND user_id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ssssii",
                $title,
                $content,
                $mood,
                $diary_date,
                $diary_id,
                $user_id
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: diary.php");
                exit();

            } else {

                $message = "Unable to update the journal entry.";
                $message_type = "danger";
            }

            mysqli_stmt_close($stmt);

        } else {

            $message = "Database error.";
            $message_type = "danger";
        }
    }
}

if (isset($_GET['delete'])) {

    $diary_id = (int)$_GET['delete'];

    if ($diary_id > 0) {

        $stmt = mysqli_prepare(
            $con,
            "DELETE FROM diary
             WHERE diary_id = ?
             AND user_id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $diary_id,
                $user_id
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: diary.php");
                exit();

            } else {

                $message = "Unable to delete the journal entry.";
                $message_type = "danger";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

if (isset($_GET['edit'])) {

    $edit_id = (int)$_GET['edit'];

    if ($edit_id > 0) {

        $stmt = mysqli_prepare(
            $con,
            "SELECT diary_id, title, content, mood_status, diary_date
             FROM diary
             WHERE diary_id = ?
             AND user_id = ?
             LIMIT 1"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $edit_id,
                $user_id
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $edit_data = mysqli_fetch_assoc($result);

            if ($edit_data) {

                $edit_title = $edit_data['title'];
                $edit_content = $edit_data['content'];
                $edit_mood = $edit_data['mood_status'];
                $edit_date = $edit_data['diary_date'];

            } else {

                $edit_id = 0;

                $message = "Journal entry not found.";
                $message_type = "danger";
            }

            mysqli_stmt_close($stmt);
        }
    }
}


/* ─────────────────────────────────────────────
   Search and Sorting
   ───────────────────────────────────────────── */

$allowed_sort = [
    'mood_status',
    'title',
    'diary_date'
];

$allowed_order = [
    'ASC',
    'DESC'
];

$sort = $_GET['sort'] ?? 'diary_date';
$order = strtoupper($_GET['order'] ?? 'DESC');

$keyword = trim($_GET['search'] ?? '');

if (!in_array($sort, $allowed_sort, true)) {

    $sort = 'diary_date';

}

if (!in_array($order, $allowed_order, true)) {

    $order = 'DESC';

}

$next_order = ($order === 'ASC') ? 'DESC' : 'ASC';


/* Search Mood and Title */

if ($keyword !== '') {

    $search = mysqli_real_escape_string(
        $con,
        $keyword
    );

    $diaryQuery = "
        SELECT diary_id, title, content, mood_status, diary_date
        FROM diary
        WHERE user_id = $user_id
        AND (
            mood_status LIKE '%$search%'
            OR title LIKE '%$search%'
        )
        ORDER BY $sort $order, diary_id DESC
    ";

} else {

    $diaryQuery = "
        SELECT diary_id, title, content, mood_status, diary_date
        FROM diary
        WHERE user_id = $user_id
        ORDER BY $sort $order, diary_id DESC
    ";

}

$diaryResult = mysqli_query(
    $con,
    $diaryQuery
);

ob_start();
?>

<style>
.diary-page {
    width: 100%;
    max-width: 100%;
}

.diary-form {
    width: 100%;
}

.diary-form textarea {
    width: 100%;
    min-height: 160px;
    resize: vertical;
    overflow-wrap: anywhere;
    word-break: break-word;
    white-space: pre-wrap;
}

.diary-table-wrapper {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
}

.diary-table {
    width: 100%;
    min-width: 800px;
    table-layout: fixed;
    border-collapse: collapse;
}

.diary-table th,
.diary-table td {
    vertical-align: middle;
    padding: 15px 20px;
}

.diary-table th:nth-child(1),
.diary-table td:nth-child(1) {
    width: 14%;
}

.diary-table th:nth-child(2),
.diary-table td:nth-child(2) {
    width: 23%;
}

.diary-table th:nth-child(3),
.diary-table td:nth-child(3) {
    width: 18%;
}

.diary-table th:nth-child(4),
.diary-table td:nth-child(4) {
    width: 15%;
}

.diary-table th:nth-child(5),
.diary-table td:nth-child(5) {
    width: 30%;
}

.diary-title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.diary-action {
    white-space: nowrap;
}

.diary-action .btn {
    margin-right: 5px;
    margin-bottom: 4px;
}

.diary-view-btn {
    min-width: 85px;
}


/* ─────────────────────────────────────────────
   Diary Search
   ───────────────────────────────────────────── */

.diary-header-search {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 20px;
}

.diary-header-search h3 {
    margin-bottom: 0 !important;
    flex-shrink: 0;
}

.diary-search-area {
    width: 500px;
    max-width: 100%;
}

.diary-search-form {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    width: 100%;
}

.diary-search-input {
    width: 100%;
    height: 42px;
}

.diary-search-button {
    height: 42px;
    min-width: 95px;
    margin-left: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.diary-clear-area {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-top: 8px;
}

.diary-clear-button {
    min-width: 90px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 4px;
}

.diary-modal {
    display: none;
    position: fixed;
    z-index: 99999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.75);
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.diary-modal-box {
    width: 90%;
    max-width: 800px;
    max-height: 85vh;
    background: #202328;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.diary-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 22px;
    border-bottom: 1px solid #3a3d42;
    flex-shrink: 0;
}

.diary-modal-header h4 {
    margin: 0;
    color: #ffffff;
    overflow-wrap: anywhere;
    word-break: break-word;
    padding-right: 20px;
}

.diary-modal-close {
    border: none;
    background: transparent;
    color: #ffffff;
    font-size: 30px;
    cursor: pointer;
    line-height: 1;
    flex-shrink: 0;
}

.diary-modal-body {
    padding: 22px;
    overflow-y: auto;
    max-height: 60vh;
    color: #ffffff;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;
    line-height: 1.7;
}

.diary-modal-footer {
    padding: 15px 22px;
    border-top: 1px solid #3a3d42;
    text-align: right;
    flex-shrink: 0;
}

@media (max-width: 768px) {

    .diary-header-search {
        display: block;
    }

    .diary-header-search h3 {
        margin-bottom: 20px !important;
    }

    .diary-search-area {
        width: 100%;
    }

    .diary-search-form {
        width: 100%;
    }

    .diary-search-input {
        min-width: 0;
    }

    .diary-clear-area {
        justify-content: center;
    }

    .diary-table {
        min-width: 800px;
    }

    .diary-modal {
        padding: 10px;
    }

    .diary-modal-box {
        width: 95%;
        max-height: 90vh;
    }

    .diary-modal-body {
        max-height: 65vh;
    }
}
</style>

<div class="diary-page">

    <?php if ($message !== ""): ?>

        <div class="alert alert-<?php echo $message_type; ?> mb-4">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <div class="card mb-4">

        <div class="card-body diary-form">

            <?php if ($edit_id > 0): ?>

                <h3 class="text-white mb-4">

                    <i class="mdi mdi-pencil text-warning"></i>

                    Edit Journal Entry

                </h3>

                <form method="POST">

                    <input
                        type="hidden"
                        name="diary_id"
                        value="<?php echo $edit_id; ?>"
                    >

                    <div class="form-group">

                        <label class="text-white">
                            Title
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            value="<?php echo htmlspecialchars($edit_title); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label class="text-white">
                            Content
                        </label>

                        <textarea
                            name="content"
                            class="form-control auto-expand"
                            rows="6"
                            required
                        ><?php echo htmlspecialchars($edit_content); ?></textarea>

                    </div>


                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">

                                <label class="text-white">
                                    Mood
                                </label>

                                <select
                                    name="mood_status"
                                    class="form-control"
                                >

                                    <option
                                        value="Happy"
                                        <?php echo $edit_mood === "Happy" ? "selected" : ""; ?>
                                    >
                                        Happy
                                    </option>

                                    <option
                                        value="Good"
                                        <?php echo $edit_mood === "Good" ? "selected" : ""; ?>
                                    >
                                        Good
                                    </option>

                                    <option
                                        value="Neutral"
                                        <?php echo $edit_mood === "Neutral" ? "selected" : ""; ?>
                                    >
                                        Neutral
                                    </option>

                                    <option
                                        value="Sad"
                                        <?php echo $edit_mood === "Sad" ? "selected" : ""; ?>
                                    >
                                        Sad
                                    </option>

                                    <option
                                        value="Angry"
                                        <?php echo $edit_mood === "Angry" ? "selected" : ""; ?>
                                    >
                                        Angry
                                    </option>

                                </select>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="form-group">

                                <label class="text-white">
                                    Date
                                </label>

                                <input
                                    type="date"
                                    name="diary_date"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($edit_date); ?>"
                                    required
                                >

                            </div>

                        </div>

                    </div>


                    <button
                        type="submit"
                        name="update_diary"
                        class="btn btn-warning"
                    >

                        <i class="mdi mdi-content-save"></i>

                        Update Journal

                    </button>


                    <a
                        href="diary.php"
                        class="btn btn-secondary"
                    >

                        Cancel

                    </a>

                </form>

            <?php else: ?>

                <h3 class="text-white mb-4">

                    <i class="mdi mdi-book-plus text-primary"></i>

                    Add Journal Entry

                </h3>

                <form method="POST">

                    <div class="form-group">

                        <label class="text-white">
                            Title
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            placeholder="Enter journal title"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label class="text-white">
                            Content
                        </label>

                        <textarea
                            name="content"
                            class="form-control auto-expand"
                            rows="6"
                            placeholder="Write your thoughts, experiences or anything you want to remember..."
                            required
                        ></textarea>

                    </div>


                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">

                                <label class="text-white">
                                    Mood
                                </label>

                                <select
                                    name="mood_status"
                                    class="form-control"
                                >

                                    <option value="Happy">
                                        Happy
                                    </option>

                                    <option value="Good">
                                        Good
                                    </option>

                                    <option value="Neutral" selected>
                                        Neutral
                                    </option>

                                    <option value="Sad">
                                        Sad
                                    </option>

                                    <option value="Angry">
                                        Angry
                                    </option>

                                </select>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="form-group">

                                <label class="text-white">
                                    Date
                                </label>

                                <input
                                    type="date"
                                    name="diary_date"
                                    class="form-control"
                                    value="<?php echo date('Y-m-d'); ?>"
                                    required
                                >

                            </div>

                        </div>

                    </div>


                    <button
                        type="submit"
                        name="add_diary"
                        class="btn btn-primary"
                    >

                        <i class="mdi mdi-content-save"></i>

                        Save Journal

                    </button>

                </form>

            <?php endif; ?>

        </div>

    </div>


    <div class="card">

        <div class="card-body">

            <div class="diary-header-search">

                <h3 class="text-white">

                    <i class="mdi mdi-book text-danger"></i>

                    My Journal Entries

                </h3>


                <div class="diary-search-area">

                    <form
                        action="diary.php"
                        method="GET"
                        class="diary-search-form"
                    >

                        <input
                            type="text"
                            name="search"
                            class="form-control diary-search-input"
                            placeholder="Search mood or title..."
                            value="<?php echo htmlspecialchars($keyword); ?>"
                        >

                        <button
                            type="submit"
                            class="btn btn-info diary-search-button"
                        >

                            <i class="mdi mdi-magnify"></i>

                            Search

                        </button>

                        <?php if ($keyword !== ''): ?>

                            <a
                                href="diary.php"
                                class="btn btn-outline-secondary diary-clear-button ml-2"
                            >

                                <i class="mdi mdi-close"></i>

                                Clear

                            </a>

                        <?php endif; ?>

                    </form>

                </div>

            </div>


            <div class="diary-table-wrapper">

                <table class="table table-dark table-hover diary-table">

                    <thead>

                        <tr>

                            <th>

                                Mood

                                <a
                                    href="?sort=mood_status&order=<?php echo $next_order; ?>&search=<?php echo urlencode($keyword); ?>"
                                    style="text-decoration: none;"
                                >

                                    <i
                                        class="mdi mdi-sort"
                                        style="color: white;"
                                    ></i>

                                </a>

                            </th>


                            <th>

                                Title

                                <a
                                    href="?sort=title&order=<?php echo $next_order; ?>&search=<?php echo urlencode($keyword); ?>"
                                    style="text-decoration: none;"
                                >

                                    <i
                                        class="mdi mdi-sort"
                                        style="color: white;"
                                    ></i>

                                </a>

                            </th>


                            <th>

                                Date

                                <a
                                    href="?sort=diary_date&order=<?php echo $next_order; ?>&search=<?php echo urlencode($keyword); ?>"
                                    style="text-decoration: none;"
                                >

                                    <i
                                        class="mdi mdi-sort"
                                        style="color: white;"
                                    ></i>

                                </a>

                            </th>


                            <th>
                                Content
                            </th>


                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (
                            $diaryResult &&
                            mysqli_num_rows($diaryResult) > 0
                        ): ?>

                            <?php while (
                                $diary =
                                    mysqli_fetch_assoc($diaryResult)
                            ): ?>

                                <tr>

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $diary['mood_status']
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <div class="diary-title">

                                            <?php
                                            echo htmlspecialchars(
                                                $diary['title']
                                            );
                                            ?>

                                        </div>

                                    </td>


                                    <td>

                                        <?php
                                        echo date(
                                            'd M Y',
                                            strtotime(
                                                $diary['diary_date']
                                            )
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <button
                                            type="button"
                                            class="btn btn-info btn-sm diary-view-btn"
                                            onclick="openDiaryContent(
                                                <?php
                                                echo htmlspecialchars(
                                                    json_encode(
                                                        $diary['title'],
                                                        JSON_UNESCAPED_UNICODE
                                                    ),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                                ?>,
                                                <?php
                                                echo htmlspecialchars(
                                                    json_encode(
                                                        $diary['content'],
                                                        JSON_UNESCAPED_UNICODE
                                                    ),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                                ?>
                                            )"
                                        >

                                            <i class="mdi mdi-eye"></i>

                                            View

                                        </button>

                                    </td>


                                    <td class="diary-action">

                                        <a
                                            href="diary.php?edit=<?php echo (int)$diary['diary_id']; ?>"
                                            class="btn btn-warning btn-sm"
                                        >

                                            <i class="mdi mdi-pencil"></i>

                                            Edit

                                        </a>


                                        <a
                                            href="diary.php?delete=<?php echo (int)$diary['diary_id']; ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this journal entry?');"
                                        >

                                            <i class="mdi mdi-delete"></i>

                                            Delete

                                        </a>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center text-muted"
                                >

                                    <?php if ($keyword !== ''): ?>

                                        No journal entries match
                                        "<?php echo htmlspecialchars($keyword); ?>".

                                    <?php else: ?>

                                        No journal entries yet.

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<div
    id="diaryContentModal"
    class="diary-modal"
    onclick="closeDiaryContent(event)"
>

    <div
        class="diary-modal-box"
        onclick="event.stopPropagation()"
    >

        <div class="diary-modal-header">

            <h4 id="diaryModalTitle">
                Journal Content
            </h4>

            <button
                type="button"
                class="diary-modal-close"
                onclick="closeDiaryModal()"
            >

                &times;

            </button>

        </div>


        <div
            id="diaryModalContent"
            class="diary-modal-body"
        ></div>


        <div class="diary-modal-footer">

            <button
                type="button"
                class="btn btn-secondary"
                onclick="closeDiaryModal()"
            >

                Close

            </button>

        </div>

    </div>

</div>


<script>
function openDiaryContent(title, content) {

    document.getElementById("diaryModalTitle").textContent = title;

    document.getElementById("diaryModalContent").textContent = content;

    document.getElementById("diaryContentModal").style.display = "flex";

    document.body.style.overflow = "hidden";
}

function closeDiaryModal() {

    document.getElementById("diaryContentModal").style.display = "none";

    document.body.style.overflow = "";
}

function closeDiaryContent(event) {

    if (event.target.id === "diaryContentModal") {

        closeDiaryModal();

    }
}

document.addEventListener("keydown", function(event) {

    if (event.key === "Escape") {

        closeDiaryModal();

    }

});


document.addEventListener("DOMContentLoaded", function() {

    const textareas = document.querySelectorAll(".auto-expand");

    textareas.forEach(function(textarea) {

        function resizeTextarea() {

            textarea.style.height = "auto";

            textarea.style.height =
                textarea.scrollHeight + "px";
        }

        textarea.addEventListener(
            "input",
            resizeTextarea
        );

        resizeTextarea();

    });

});
</script>

<?php

$pageContent = ob_get_clean();

include "layout.php";

?>