<?php

require('auth.php');

$current_page = "money.php";

ob_start();

?>

<!-- Money content will be added here later -->


<?php

$pageContent = ob_get_clean();

include "layout.php";

?>


