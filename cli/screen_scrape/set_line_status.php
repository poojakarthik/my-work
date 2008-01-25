<?php

require_once("../framework/require.php");

// Archived
$arrCols = Array();
$arrCols['Status']	= SERVICE_ARCHIVED;
$updServiceArchived	= new StatementUpdate("Service", "ClosedOn IS NOT NULL", $arrCols);

// Active
$arrCols = Array();
$arrCols['Status']	= SERVICE_ACTIVE;
$updServiceArchived	= new StatementUpdate("Service", "ClosedOn IS NULL", $arrCols);

?>