<?php

// This is a temporary measure. There is a 'feature' in flex that causes the user to be directed to ui/console.php when their session has timed out.
header('Location: ../management/console.php');

?>
