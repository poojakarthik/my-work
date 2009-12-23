<?php

require_once('../../lib/classes/Flex.php');
Flex::load();

//require_once("../../flex.require.php");
LoadApplication();

$appProvisioning = new ApplicationProvisioning();

$appProvisioning->Export();

?>