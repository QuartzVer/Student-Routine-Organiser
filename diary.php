<?php

require('auth.php');

$current_page = "diary.php";

ob_start();

?>

<!-- Diary content will be added here later -->


<?php

$pageContent = ob_get_clean();

include "layout.php";

?>


