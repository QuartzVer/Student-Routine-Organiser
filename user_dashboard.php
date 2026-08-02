<?php

require('auth.php');

$current_page = "habit.php";

ob_start();

?>

<!-- user/admin dashboard content here -->


<?php

$pageContent = ob_get_clean();

include "layout.php";

?>


