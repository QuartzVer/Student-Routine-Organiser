<?php

require('auth.php');

$current_page = "habit.php";

ob_start();

?>

<!-- Habit content will be added here later -->


<?php

$pageContent = ob_get_clean();

include "layout.php";

?>


