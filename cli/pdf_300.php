<?php
/*
Usage:
	php /path/to/flex/cli/pdf.php [-c CUSTOMER_GROUP] [-d DOCUMENT_TYPE] -x XML_DATA_FILE_LOCATION [-f OUTPUT_FILE_PATH_AND_NAME] [-e EFFECTIVE_DATE] [-m SOURCE_MEDIA] [-o OUTPUT_MEDIA] [-l LOG_FILE]
where:
	CUSTOMER_GROUP				is the name of the customer group to create the PDF file for (from database) [optional, default taken from XML file]
	DOCUMENT_TYPE				is the document type to be generated (e.g. INVOICE OVERDUE_NOTICE, SUSPENSION_NOTICE or FINAL_DEMAND_NOTICE) [optional, default taken from XML file]
	XML_DATA_FILE_LOCATION		is the full path to an XML data file or directory containing XML files
	OUTPUT_FILE_PATH_AND_NAME	is the full path for the output PDF file or directory (if PDF files exist, they may be overwritten)
	EFFECTIVE_DATE				is the effective date of the document in 'YYYY-mm-dd hh:ii:ss' or Unix timestamp format [optional, default taken from XML file]
	SOURCE_MEDIA				if specified, only XML documents originally intended for thie media are processed (EMAIL or PRINT)
	OUTPUT_MEDIA				is the output media for the PDF document (EMAIL or PRINT) [optional, default taken from XML file]
	LOG_FILE					is a writable file location to write log messages to (EMAIL or PRINT) [optional, default is no logging]
**/

	require_once dirname(__FILE__) . "/../lib/cli/Cli.php";	

	Cli::execute("Cli_App_Pdf_300");

?>
