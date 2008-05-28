<?php
/*

Usage:
    php /path/to/flex/lib/pdf/cli.php -cCUSTOMER_GROUP_ID -dDOCUMENT_TYPE_ID [-eEFFECTIVE_DATE] -xXML_DATA_FILE_LOCATION -fOUTPUT_FILE_PATH_AND_NAME [-mTARGET_MEDIA]
where:
    CUSTOMER_GROUP_ID             is the Id of the customer group to create the PDF file for (from database)
    DOCUMENT_TYPE_ID              identifies the type of document to be generated (from database)
    XML_DATA_FILE_LOCATION        is the full path to the XML data file
    OUTPUT_FILE_PATH_AND_NAME     is the full path for the output PDF file (if one already exists, it may be overwritten)
    EFFECTIVE_DATE                is the effective date of the document in 'YYYY-mm-dd hh:ii:ss' or Unix timestamp format [optional, default is now]
    TARGET_MEDIA                  is the target media for the PDF document (EMAIL or PRINT) [optional, default is EMAIL]
*/

	require_once dirname(__FILE__) . "/../../lib/cli/Cli.php";	

	Cli::execute("Cli_Invoice_XML_Test");

?>
