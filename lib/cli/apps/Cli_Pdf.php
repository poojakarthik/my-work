<?php

class Cli_Pdf extends Cli
{
	const SWITCH_CUSTOMER_GROUP_ID = "c";
	const SWITCH_DOCUMENT_TYPE_ID = "d";
	const SWITCH_EFFECTIVE_DATE = "e";
	const SWITCH_XML_DATA_FILE_LOCATION = "x";
	const SWITCH_OUTPUT_FILE_PATH_AND_NAME = "f";
	const SWITCH_TARGET_MEDIA = "m";

	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$args = $this->getValidatedArguments();
			
			// Include the application... 
			$this->requireOnce("flex.require.php");
			
			// Include the pdf library...
			$this->requireOnce("lib/pdf/Flex_Pdf.php");

			// Create the PDF template
			$this->startErrorCatching();
			$pdfTemplate = new Flex_Pdf_Template(
							$args[Cli_Pdf::SWITCH_CUSTOMER_GROUP_ID], 
							$args[Cli_Pdf::SWITCH_EFFECTIVE_DATE], 
							$args[Cli_Pdf::SWITCH_DOCUMENT_TYPE_ID], 
							file_get_contents($args[Cli_Pdf::SWITCH_XML_DATA_FILE_LOCATION]), 
							$args[Cli_Pdf::SWITCH_TARGET_MEDIA], 
							TRUE);
			$this->dieIfErrored();
			
			// Create the documents for the template
			$this->startErrorCatching();
			$pdf = $pdfTemplate->createDocument();
			$this->dieIfErrored();
			
			// Save the document to file
			$this->startErrorCatching();
			$pdf->save($args[Cli_Pdf::SWITCH_OUTPUT_FILE_PATH_AND_NAME]);
			$this->dieIfErrored();
			
			// Must have worked! Exit with 'OK' code 0
			exit(0);
		}
		catch(Exception $exception)
		{
			$this->showUsage($exception->getMessage());
		}
	} 

	function getCommandLineArguments()
	{
		$commandLineArguments = array(
		
			self::SWITCH_CUSTOMER_GROUP_ID => array(
				self::ARG_LABEL 		=> "CUSTOMER_GROUP_ID",
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the integer Id of the customer group to create the PDF file for (from database)",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validInteger("%1$s")'
			),
		
			self::SWITCH_DOCUMENT_TYPE_ID => array(
				self::ARG_LABEL 		=> "DOCUMENT_TYPE_ID",
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the integer Id of the document type to be generated (from database)",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validInteger("%1$s")'
			),
		
			self::SWITCH_XML_DATA_FILE_LOCATION => array(
				self::ARG_LABEL 		=> "XML_DATA_FILE_LOCATION",
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the full path to the XML data file",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validFile("%1$s", TRUE)'
			),
		
			self::SWITCH_OUTPUT_FILE_PATH_AND_NAME => array(
				self::ARG_LABEL 		=> "OUTPUT_FILE_PATH_AND_NAME",
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the full path for the output PDF file (if one already exists, it may be overwritten)",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validFile("%1$s", FALSE)'
			),
		
			self::SWITCH_EFFECTIVE_DATE => array(
				self::ARG_LABEL 		=> "EFFECTIVE_DATE",
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the effective date of the document in 'YYYY-mm-dd hh:ii:ss' or Unix " .
										"timestamp format [optional, default is now]",
				self::ARG_DEFAULT 	=> time(),
				self::ARG_VALIDATION 	=> 'Cli::_validDate("%1$s")'
			),
		
			self::SWITCH_TARGET_MEDIA => array(
				self::ARG_LABEL 		=> "TARGET_MEDIA", 
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the target media for the PDF document (EMAIL or PRINT) [optional, default is EMAIL]",
				self::ARG_DEFAULT 	=> "EMAIL",
				self::ARG_VALIDATION 	=> 'Cli::_validInArray("%1$s", array("EMAIL","PRINT"))'
			)
		);
		return $commandLineArguments;
	}

}

?>
