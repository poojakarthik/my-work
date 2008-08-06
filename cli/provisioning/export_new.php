<?php

require_once("../../flex.require.php");
LoadApplication();

$appProvisioning = new ApplicationProvisioning();

$appProvisioning->Export();

?>