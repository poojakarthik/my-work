<?php

	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//

	// call application loader
	require ('../../lib/classes/Flex.php');

	Flex::endSession(Flex::FLEX_ADMIN_SESSION);

	// Forward to Login Page
	header ("Location: login.php"); exit;

?>
