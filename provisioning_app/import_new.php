<?php

require_once("../framework/require.php");
require_once("require.php");
require_once("provisioning.php");

$appProvisioning = new ApplicationProvisioning();

$appProvisioning->Import();

?>