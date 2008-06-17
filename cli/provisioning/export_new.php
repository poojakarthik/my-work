<?php

require_once("../../flex.require.php");
require_once("require.php");
require_once("provisioning.php");

$appProvisioning = new ApplicationProvisioning();

$appProvisioning->Export();

?>