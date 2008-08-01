<?php

require_once("../../flex.require.php");
require_once("provisioning.php");

$appProvisioning = new ApplicationProvisioning();

// Statements
$selResponse	= new StatementSelect(	"ProvisioningResponse",
										"",
										"");

?>