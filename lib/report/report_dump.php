<?php
require_once dirname(__FILE__) . "/../cli/Cli.php";	
require_once dirname(__FILE__).'/report_permissions.php';
Cli::execute("Cli_App_DataReportDump");
?>