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
			$arrArgs = $this->getValidatedArguments();
			
			$strDestination = $arrArgs[Cli_Pdf::SWITCH_OUTPUT_FILE_PATH_AND_NAME];
			$strSource = $arrArgs[Cli_Pdf::SWITCH_XML_DATA_FILE_LOCATION];
			
			// Check the destination type is valid (can't have file destination for source directory)
			if ($strDestination !== NULL && !is_dir($strDestination) && is_dir($strSource))
			{
				throw new Exception("Cannot use a file or non-existent directory '$strDestination' as the destination for files from a directory source.");
			}
			
			// If no destination specified, default to source directory
			if ($strDestination === NULL)
			{
				$strDestination = is_file($strSource) ? dirname($strSource) : $strSource;
			}
			
			// Get the XML data files to be used
			$arrFiles = array();
			if (is_file($strSource))
			{
				// Store the destination file for the source file
				$arrFiles[$strSource] = is_dir($strDestination) ? ($strDestination . DIRECTORY_SEPARATOR . basename($strSource) . ".pdf") : $strDestination;
			}
			else
			{
				// Look for files in the source directory...
				$arrSourceContents = scandir($strSource);
				for ($i = 0, $l = count($arrSourceContents); $i < $l; $i++)
				{
					$strPath = $strSource . DIRECTORY_SEPARATOR . $arrSourceContents[$i];
					// Ignore directories (including source directory '.' and parent directory '..')
					if (is_file($strPath))
					{
						// If the file cannot be read, we'd better throw a wobbler as the user may be expecting a PDF for it!
						if (!is_readable($strPath))
						{
							throw new Exception("Directory '" . $strSource . "' contains unreadable file '" . $arrSourceContents[$i] . "'");
						}
						// Store the destination file for this source file
						$arrFiles[$strPath] = $strDestination . DIRECTORY_SEPARATOR . $arrSourceContents[$i] . ".pdf";
					}
				}
			}
			
			// Include the application... 
			$this->requireOnce("flex.require.php");
			
			// Include the pdf library...
			$this->requireOnce("lib/pdf/Flex_Pdf.php");

			foreach ($arrFiles as $strSource => $strDestination)
			{
				// Make sure we have enough time to generate this PDF (2 minutes should hopefully always be enough!)...
				set_time_limit(120);
				
				// Create the PDF template
				$this->startErrorCatching();
				$pdfTemplate = new Flex_Pdf_Template(
								$arrArgs[Cli_Pdf::SWITCH_CUSTOMER_GROUP_ID], 
								$arrArgs[Cli_Pdf::SWITCH_EFFECTIVE_DATE], 
								$arrArgs[Cli_Pdf::SWITCH_DOCUMENT_TYPE_ID], 
								file_get_contents($strSource), 
								$arrArgs[Cli_Pdf::SWITCH_TARGET_MEDIA], 
								TRUE);
				$this->dieIfErred();
				
				// Create the documents for the template
				$this->startErrorCatching();
				$pdf = $pdfTemplate->createDocument();
				$this->dieIfErred();
				
				// We've got what we wanted, so let's free the resources!
				$this->startErrorCatching();
				$pdfTemplate->destroy();
				$this->dieIfErred();
				
				// Save the document to file
				$this->startErrorCatching();
				$pdf->save($strDestination);
				$this->dieIfErred();
			}
			
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
				self::ARG_DESCRIPTION => "is the full path to an XML data file or directory containing XML files",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validReadableFileOrDirectory("%1$s")'
			),

			self::SWITCH_OUTPUT_FILE_PATH_AND_NAME => array(
				self::ARG_LABEL 		=> "OUTPUT_FILE_PATH_AND_NAME",
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the full path for the output PDF file or directory (if PDF files exist, they may be overwritten)",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validWritableFileOrDirectory("%1$s")'
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
