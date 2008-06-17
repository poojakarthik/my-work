<?php

class Cli_App_Pdf extends Cli
{
	const SWITCH_CUSTOMER_GROUP_ID = "c";
	const SWITCH_DOCUMENT_TYPE_ID = "d";
	const SWITCH_EFFECTIVE_DATE = "e";
	const SWITCH_XML_DATA_FILE_LOCATION = "x";
	const SWITCH_OUTPUT_FILE_PATH_AND_NAME = "f";
	const SWITCH_SOURCE_MEDIA = "m";
	const SWITCH_OUTPUT_MEDIA = "o";

	private $logFile = NULL;

	function run()
	{
		try
		{
			// Include the application... 
			$this->requireOnce("flex.require.php");

			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			$this->log("Processing command line parameters");

			$strDestination = $arrArgs[self::SWITCH_OUTPUT_FILE_PATH_AND_NAME];
			$strSource = $arrArgs[self::SWITCH_XML_DATA_FILE_LOCATION];

			// Ensure the directory exists
			$basename = basename($strDestination);
			$dir = $strDestination;
			if (strpos($basename, '.') !== FALSE)
			{
				$dir = dirname($strDestination);
			}
			// If the directory does not exist, create it
			if (!file_exists($dir))
			{
				mkdir($dir, 0777, TRUE);
			}

			// Check to see if target is an archive
			$archiveDir = '';
			$bolArchived = FALSE;
			if ($strDestination !== NULL && !is_dir($strDestination))
			{
				$matches = array();
				if (preg_match("/\.tar(?:\.(bz2|gz)|)$/", $strDestination, $matches))
				{
					$strCompression = count($matches) == 1 ? NULL : strtolower($matches[1]);
					$bolArchived = TRUE;

					$strArchiveFile = $strDestination;
					$strDestination = dirname($strDestination);
					$archiveDir = $strDestination;

					// Check that the PEAR library 'Archive_Tar' is available
					$this->startErrorCatching();
					require_once "Archive/Tar.php";
					$this->dieIfErred();
				}
			}

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

			$this->log("Source location: $strSource");

			$this->log("Destination location: $strSource");

			// Create a unique instance id to allow us to identify the files generated by this execution
			$instanceRef = 'i'.strval(microtime(true)*1000000);
			$this->log("\nUnique ID for this process thread: $instanceRef");

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
						// Ignore non-xml files
						if (substr($strPath, -4) != '.xml') continue;
						// If the file cannot be read, we'd better throw a wobbler as the user may be expecting a PDF for it!
						if (!is_readable($strPath))
						{
							throw new Exception("Directory '" . $strSource . "' contains unreadable file '" . $arrSourceContents[$i] . "'");
						}
						// Store the destination file for this source file
						$arrFiles[$strPath] = $strDestination . DIRECTORY_SEPARATOR . $arrSourceContents[$i] . ".$instanceRef.pdf";

					}
				}
			}

			$this->log("Processing " . count($arrFiles) . " XML source files");

			// Include the pdf library...
			$this->requireOnce("lib/pdf/Flex_Pdf.php");

			$docCount = 0;
			$lastDocNameLen = 0;
			echo "Processing document $docCount   ";

			$generatedDocs = array();

			foreach ($arrFiles as $strSource => $strDestination)
			{
				$this->log("Processing XML file: $strSource");
				// Make sure we have enough time to generate this PDF (2 minutes should hopefully always be enough!)...
				set_time_limit(180);

				$fileContents = file_get_contents($strSource);

				$parts = array();
				preg_match_all("/(?:\<(DocumentType|CustomerGroup|CreationDate|DeliveryMethod)\>([^\<]*)\<)/", $fileContents, $parts);

				if (count($parts) != 3 || count($parts[1]) != 4 || count($parts[2]) != 4)
				{
					throw new Exception("Unable to identify document properties.");
				}

				$docProps = array();
				for($i = 0; $i < 4; $i++)
				{
					$docProps[$parts[1][$i]] = $parts[2][$i]; 
				}

				$custGroupId = constant($docProps["CustomerGroup"]);
				if ($arrArgs[self::SWITCH_CUSTOMER_GROUP_ID] !== FALSE)
				{
					if ($custGroupId != $arrArgs[self::SWITCH_CUSTOMER_GROUP_ID])
					{
						$this->log("Skipping XML file '$strSource' as it is for CustomerGroup $custGroupId. We are only processing CustomerGroup " . $arrArgs[self::SWITCH_CUSTOMER_GROUP_ID] . ".");
						continue;
					}
				}

				if ($arrArgs[self::SWITCH_EFFECTIVE_DATE] === FALSE)
				{
					$effectiveDate = $docProps["CreationDate"];
				}
				else
				{
					$effectiveDate = $arrArgs[self::SWITCH_EFFECTIVE_DATE];
				}

				$docProps["DocumentType"] = str_replace("DOCUMENT_TYPE_", "DOCUMENT_TEMPLATE_TYPE_", $docProps["DocumentType"]);
				$documentTypeId = constant($docProps["DocumentType"]);

				if ($arrArgs[self::SWITCH_DOCUMENT_TYPE_ID] !== FALSE && $arrArgs[self::SWITCH_DOCUMENT_TYPE_ID] !== $documentTypeId)
				{
					$this->log("Skipping XML file '$strSource' as it's document type is $documentTypeId (" . $docProps["DocumentType"] . "). We are only processing type " . $arrArgs[self::SWITCH_DOCUMENT_TYPE_ID] . " documents.");
					continue;
				}

				$targetMedia = constant($docProps["DeliveryMethod"]);
				switch($targetMedia)
				{
					case DELIVERY_METHOD_EMAIL:
					case DELIVERY_METHOD_EMAIL_SENT:
						$targetMedia = "EMAIL";
						break;
					case DELIVERY_METHOD_POST:
						$targetMedia = "PRINT";
						break;
					case DELIVERY_METHOD_DO_NOT_SEND:
						$targetMedia = "DO_NOT_SEND";
						break;
					default:
						$this->log("Skipping XML file '$strSource' as it's target media '$targetMedia' is not supported.");
						continue 2;
				}

				// Check that we are processing xml files of the intended media type 
				if ($arrArgs[self::SWITCH_SOURCE_MEDIA] !== FALSE)
				{
					if ($targetMedia != $arrArgs[self::SWITCH_SOURCE_MEDIA])
					{
						$this->log("Skipping XML file '$strSource' as it is for media '$targetMedia'. We are only processing for media '" . $arrArgs[self::SWITCH_SOURCE_MEDIA] . "'.");
						continue;
					}
				}

				if ($targetMedia == 'DO_NOT_SEND')
				{
					$targetMedia = "PRINT";
				}

				// If an output media is specified, force output to suit...
				if ($arrArgs[self::SWITCH_OUTPUT_MEDIA] !== FALSE)
				{
					$targetMedia = $arrArgs[self::SWITCH_OUTPUT_MEDIA];
				}

				$docNameLen = strlen($strSource);
				$pad = $lastDocNameLen > $docNameLen ? ($lastDocNameLen - $docNameLen) : 0;
				echo str_repeat(chr(8), strlen($docCount)+$lastDocNameLen+3) . ++$docCount . " ($strSource)" . str_repeat(" ", $pad) . str_repeat(chr(8), $pad);
				ob_flush();
				flush();
				$lastDocNameLen = $docNameLen;

				// Create the PDF template
				$this->startErrorCatching();
				$pdfTemplate = new Flex_Pdf_Template(
								$custGroupId, 
								$effectiveDate, 
								$documentTypeId, 
								$fileContents, 
								$targetMedia, 
								TRUE);
				$this->dieIfErred();

				// Create the documents for the template
				$this->log("Creating PDF for $strSource");
				$this->startErrorCatching();
				$pdf = $pdfTemplate->createDocument();
				$this->dieIfErred();

				// We've got what we wanted, so let's free the resources!
				$this->log("Memory usage before forced resource release: " . memory_get_usage());
				$this->startErrorCatching();
				$pdfTemplate->destroy();
				$this->dieIfErred();
				$this->log("Memory usage after forced resource release:  " . memory_get_usage());

				// Save the document to file
				$this->log("Memory usage before saving PDF to file:      " . memory_get_usage());
				$this->startErrorCatching();
				$pdf->save($strDestination);
				unset($pdf);
				$this->dieIfErred();
				$this->log("Memory usage after saving PDF to file:       " . memory_get_usage());

				$generatedDocs[] = $strDestination;
			}

			echo str_repeat(chr(8), $lastDocNameLen+3) . "\nProcessing complete";
			ob_flush();
			flush();

			// If writing to an archived file...
			if ($bolArchived)
			{
				$this->log("Archiving PDFs to $strArchiveFile");
				$objArchive = new Archive_Tar($strArchiveFile, $strCompression);
				$objArchive->addModify($generatedDocs, '', $archiveDir);

				// Remove the archived folder
				$this->log("Removing unarchived copies of PDFs");
				foreach ($generatedDocs as $strSource => $strDestination)
				{
					unlink($strDestination);
				}
			}

			$this->log("\nCompleted successfully.\n", FALSE, FALSE, TRUE);

			// Must have worked! Exit with 'OK' code 0
			return 0;
		}
		catch(Exception $exception)
		{
			$this->showUsage("ERROR: " . $exception->getMessage());
		}
	} 

	function getCommandLineArguments()
	{
		$commandLineArguments = array(

			self::SWITCH_CUSTOMER_GROUP_ID => array(
				self::ARG_LABEL 		=> "CUSTOMER_GROUP",
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the name of the customer group to create the PDF file for (from database) [optional, default taken from XML file]",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validConstant("%1$s", "CUSTOMER_GROUP_")'
			),

			self::SWITCH_DOCUMENT_TYPE_ID => array(
				self::ARG_LABEL 		=> "DOCUMENT_TYPE",
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the document type to be generated (e.g. INVOICE OVERDUE_NOTICE, SUSPENSION_NOTICE or FINAL_DEMAND_NOTICE) [optional, default taken from XML file]",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validConstant("%1$s", "DOCUMENT_TEMPLATE_TYPE_")'
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
										"timestamp format [optional, default taken from XML file]",
				self::ARG_DEFAULT 	=> time(),
				self::ARG_VALIDATION 	=> 'Cli::_validDate("%1$s")'
			),
		
			self::SWITCH_SOURCE_MEDIA => array(
				self::ARG_LABEL 		=> "SOURCE_MEDIA", 
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "if specified, only XML documents originally intended for thie media are processed (EMAIL or PRINT)",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validInArray("%1$s", array("EMAIL","PRINT"))'
			),
		
			self::SWITCH_OUTPUT_MEDIA => array(
				self::ARG_LABEL 		=> "OUTPUT_MEDIA", 
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the output media for the PDF document (EMAIL or PRINT) [optional, default taken from XML file]",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validInArray("%1$s", array("EMAIL","PRINT"))'
			),
		);
		return $commandLineArguments;
	}

}

?>
