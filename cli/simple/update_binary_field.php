<?php

// Load Flex
require_once("../../lib/classes/Flex.php");
Flex::load();

$strTable		= "file_type";
$strField		= "icon_16x16";
$strConstraint	= "name = 'PDF'";

$strFilePath	= "../../html/admin/img/template/pdf_small.png";

$strFileContents	= @file_get_contents($strFilePath);
if ($strFileContents === false)
{
	throw new Exception("There was an error reading from '{$strFilePath}'.");
}

$arrColumns	= array($strField=>$strFileContents);
$updUpdateBinary	= new StatementUpdate($strTable, $strConstraint, $arrColumns, 1);
if ($updUpdateBinary->Execute($arrColumns, array()) === false)
{
	throw new Exception($updUpdateBinary->Error());
}

CliEcho("Successfully updated {$strTable}.{$strField} where {$strConstraint} with {$strFilePath}");
?>