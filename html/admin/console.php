<?php

require_once('../../lib/classes/Flex.php');
Flex::load();

try
{
	// Create an assertion
	Flex::assert(
		false, 
		"flex/html/admin/console.php accessed, it does nothing but redirect to '../management/console.php'.", 
		null, 
		"Deprecated Page (Framework 1) Accessed: console.php"
	);
}
catch (Exception_Assertion $e)
{
	// Do nothing, the assert function has sent the email already
}

// This is a temporary measure. There is a 'feature' in flex that causes the user to be directed to ui/console.php when their session has timed out.
header('Location: ../management/console.php');

?>
