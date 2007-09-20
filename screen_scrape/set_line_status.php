<?php

require_once("../framework/require.php");

// Archived
$arrCols = Array();
$arrCols['LineStatus']	= SERVICE_ARCHIVED;
$updServiceArchived	= new StatementUpdate("Service", "ClosedOn IS NOT NULL", $arrCols);

// Active
$arrCols = Array();
$arrCols['LineStatus']	= SERVICE_ACTIVE;
$updServiceArchived	= new StatementUpdate("Service", "ClosedOn IS NULL", $arrCols);

?>