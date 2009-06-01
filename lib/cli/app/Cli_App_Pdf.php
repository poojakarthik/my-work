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
	const SWITCH_SINGLE_PDF = "j";
	const SWITCH_SKIP_XML_OVERVIEW = "i";
	const SWITCH_IGNORE_ACCOUNTS = "n";

	private $logFile = NULL;

	function run()
	{
		$mungeError = '';

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
			
			// Parse Ignore List
			$strIgnoreAccounts	= $arrArgs[self::SWITCH_IGNORE_ACCOUNTS];
			$this->log("Ignore List: '{$strIgnoreAccounts}'");
			$arrIgnoreAccounts	= explode(" ", $strIgnoreAccounts);
			foreach ($arrIgnoreAccounts as $intKey=>$strAccount)
			{
				$intAccount	= (int)trim($strAccount);
				if ($intAccount)
				{
					$this->log("Ignoring Account #{$intAccount}");
					$arrIgnoreAccounts[$intKey]	= $intAccount;
				}
				else
				{
					unset($arrIgnoreAccounts[$intKey]);
				}
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
						
						// Ensure that it isn't in our ignore list
						if (!in_array((int)basename($strPath, '.xml'), $arrIgnoreAccounts))
						{
							// Store the destination file for this source file
							$arrFiles[$strPath] = $strDestination . DIRECTORY_SEPARATOR . $arrSourceContents[$i] . ".$instanceRef.pdf";
						}
						else
						{
							$this->log("Skipping Account #{$intAccount}: Ignored");
						}
					}
				}
			}

			$singlePdfFile = $archiveDir && $arrArgs[self::SWITCH_SINGLE_PDF];
			if ($singlePdfFile)$this->log("Will be munging into one PDF ($archiveDir :: " . $arrArgs[self::SWITCH_SINGLE_PDF] . ")");

			$xmlSummaryFile = NULL;
			$xmlDocumentType = NULL;
			$xmlCustomerGroup = NULL;
			$xmlEffectiveDate = NULL;
			if ($singlePdfFile && !$arrArgs[self::SWITCH_SKIP_XML_OVERVIEW])
			{
				$xmlSummaryFile = fopen($archiveDir.'/' . $instanceRef . '.index.xml', 'w');
				fwrite($xmlSummaryFile, "<PDF>\n\t<Accounts>");
			}


			$this->log("Processing " . count($arrFiles) . " XML source files");

			// Include the pdf library...
			$this->requireOnce("lib/pdf/Flex_Pdf.php");

			$docCount = 0;
			$lastDocNameLen = 0;
			$this->log("Processing document $docCount   ", FALSE, TRUE, TRUE);

			$generatedDocs = array();

			$pageCountOffset = 0;

			foreach ($arrFiles as $strSource => $strDestination)
			{
				$this->log("Processing XML file: $strSource");
				// Make sure we have enough time to generate this PDF (2 minutes should hopefully always be enough!)...
				set_time_limit(1800);

				$fileContents = file_get_contents($strSource);

				$parts = array();
				$requiredTags = array('DocumentType', 'CustomerGroup', 'CreationDate', 'DeliveryMethod');
				preg_match_all("/(?:\<(" . implode('|', $requiredTags) . ")\>([^\<]*)\<)/", $fileContents, $parts);

				if (count($parts) != 3 || count($parts[1]) < 4 || count($parts[2]) < 4)
				{
					throw new Exception("Unable to identify document properties in file: $strSource");
				}

				$docProps = array();
				for($i = 0; $i < 4; $i++)
				{
					if (!array_key_exists($parts[1][$i], $docProps))
					{
						$docProps[$parts[1][$i]] = $parts[2][$i];
					}
				}

				if ($singlePdfFile && !$arrArgs[self::SWITCH_SKIP_XML_OVERVIEW])
				{
					$match = array();
					preg_match("/\<Account +[\s\S]*\<\/Account\>/Ui", $fileContents, $match);
					if (!count($match))
					{
						throw new Exception("Unable to find account details in XML file: $strSource");
					}
					$xmlDetail = $match[0];
				}

				foreach ($requiredTags as $requiredTag)
				{
					if (!array_key_exists($requiredTag, $docProps))
					{
						throw new Exception("Unable to identify document property: $requiredTag");
					}
				}

				$custGroupId = Customer_Group::getForConstantName($docProps["CustomerGroup"])->id;
				if ($arrArgs[self::SWITCH_CUSTOMER_GROUP_ID] !== FALSE)
				{
					if ($custGroupId != $arrArgs[self::SWITCH_CUSTOMER_GROUP_ID])
					{
						$this->log("Skipping XML file '$strSource' as it is for CustomerGroup $custGroupId. We are only processing CustomerGroup " . $arrArgs[self::SWITCH_CUSTOMER_GROUP_ID] . ".");
						continue;
					}
				}

				if ($xmlCustomerGroup === NULL)
				{
					$xmlCustomerGroup = $custGroupId;
				}

				if ($arrArgs[self::SWITCH_EFFECTIVE_DATE] === FALSE)
				{
					$effectiveDate = $docProps["CreationDate"];
				}
				else
				{
					$effectiveDate = $arrArgs[self::SWITCH_EFFECTIVE_DATE];
				}

				if ($xmlEffectiveDate == NULL)
				{
					$xmlEffectiveDate = $effectiveDate;
				}

				$docProps["DocumentType"] = str_replace("DOCUMENT_TYPE_", "DOCUMENT_TEMPLATE_TYPE_", $docProps["DocumentType"]);
				if ($docProps["DocumentType"] == 'DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND_NOTICE')
				{
					$docProps["DocumentType"] = 'DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND';
				}
				$documentTypeId = constant($docProps["DocumentType"]);

				if ($arrArgs[self::SWITCH_DOCUMENT_TYPE_ID] !== FALSE && $arrArgs[self::SWITCH_DOCUMENT_TYPE_ID] !== $documentTypeId)
				{
					$this->log("Skipping XML file '$strSource' as it's document type is $documentTypeId (" . $docProps["DocumentType"] . "). We are only processing type " . $arrArgs[self::SWITCH_DOCUMENT_TYPE_ID] . " documents.");
					continue;
				}

				if ($xmlDocumentType === NULL)
				{
					$xmlDocumentType = $documentTypeId;
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
				$this->log(str_repeat(chr(8), strlen($docCount)+$lastDocNameLen+3) . ++$docCount . " ($strSource)" . str_repeat(" ", $pad) . str_repeat(chr(8), $pad), FALSE, TRUE, TRUE);
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

				// Release memory used by file contents
				$fileContents = "";

				// Create the documents for the template
				$this->log("\nCreating PDF for $strSource");
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

				if ($singlePdfFile && !$arrArgs[self::SWITCH_SKIP_XML_OVERVIEW])
				{
					$nrPages = $pdf->getNrPages();
					fwrite($xmlSummaryFile, "\n\t\t" . substr($xmlDetail, 0, 8) . " Pages=\"$nrPages\" PageOffset=\"$pageCountOffset\" " . substr($xmlDetail, 9));
					$pageCountOffset += $nrPages;
				}

				unset($pdf);
				$this->dieIfErred();
				$this->log("Memory usage after saving PDF to file:       " . memory_get_usage());

				$generatedDocs[] = $strDestination;
			}

			$this->log(str_repeat(chr(8), $lastDocNameLen+3) . "\nProcessing complete", FALSE, TRUE, TRUE);
			$this->log("\n");

			ob_flush();
			flush();

			$rmDir = NULL;
			if ($singlePdfFile)
			{
				// Need to munge all pdfs created into a single pdf file.
				$mungedFile = 'all.pdf';
				$partialFile = 'partial.pdf';

				$cwd = getcwd();
				chdir($archiveDir);

				$this->log("Munging PDFs to $mungedFile");
				ob_flush();
				flush();

				// Munge pdfs together IN ORDER THAT THEY APPEAR IN $generatedDocs
				$blocks = array_chunk($generatedDocs, 10);
				$first = TRUE;
				foreach ($blocks as $block)
				{
					foreach ($block as $i => $v) $block[$i] = basename($v);
					if (!$first)
					{
						rename("$instanceRef.$mungedFile", "$instanceRef.$partialFile");
					}
					$paths = ($first ? '' : $instanceRef.'.partial.pdf ' ) . implode(' ', $block);

					$rd = realpath($archiveDir).DIRECTORY_SEPARATOR;
					$mungeError = shell_exec("pdftk $paths cat output $instanceRef.$mungedFile 2>&1");
					$first = FALSE;
					if (!$first)
					{
						unlink("$instanceRef.$partialFile");
					}
					if ($mungeError)
					{
						if (file_exists("$instanceRef.$mungedFile"))
						{
							unlink("$instanceRef.$mungedFile");
						}
						break;
					}
				}

				chdir($cwd);

				if ($mungeError)
				{
					$this->log("Failed to munge PDFs. Will pack individually.", TRUE);
				}
				else
				{
					// Delete all the existing individual pdf files
					$this->log("Removing individual PDFs");
					foreach ($generatedDocs as $strSource => $strDestination)
					{
						unlink($strDestination);
					}

					// Next, set the $generatedDocs array to contain ONLY the one PDF
					$this->log("Updating the generated docs array PDFs");
					$generatedDocs = array();
					$generatedDocs[] = "$archiveDir/$mungedFile";
					rename("$archiveDir/$instanceRef.$mungedFile", "$archiveDir/$mungedFile");
				}

				$images = "";
				switch($xmlDocumentType)
				{
					case DOCUMENT_TEMPLATE_TYPE_INVOICE:

						$this->log("Loading image resource ('fdbp://Invoice Ad (Print)' for CustomerGroup.Id: $xmlCustomerGroup, Effective date: " . date('Y-m-d H:i:s', $xmlEffectiveDate) . ")");
						$rm = Flex_Pdf_Resource_Manager::getResourceManager($xmlCustomerGroup, date('Y-m-d H:i:s', $xmlEffectiveDate));
						$filePath = $rm->getResourcePath('fdbp://Invoice Ad (Print)');
						$this->log("Resource path: $filePath");

						$fileName = basename($filePath);
						$fileName = 'advert' . substr($fileName, strrpos($fileName, '.'));
						$this->log("File name: $fileName");

						// <img src="fdbp://Invoice Ad (Print)" style="left: 56.6pt; top: 267pt; width: 523pt; height: 295pt; media: print;" />
						$images = "\n\t<Images>\n\t\t<Image page=\"1\" src=\"images/" . htmlspecialchars($fileName) . "\" width=\"523pt\" height=\"295pt\" top=\"267pt\" left=\"56.6pt\" />\n\t</Images>";
						$imgDir = $archiveDir . '/images';
						if (!file_exists($imgDir))
						{
							mkdir($imgDir);
							$rmDir = $imgDir;
						}
						$this->log("Copying image resource to file system");
						copy($filePath, $imgDir . '/' . $fileName);
						$generatedDocs[] = $imgDir . '/' . $fileName;
						break;
				}

				if (!$arrArgs[self::SWITCH_SKIP_XML_OVERVIEW])
				{
					$this->log("Ending XML summary file, including image details");
					// Create an XML record of all the files created containing account details (from xml) and nr pages (from individual pdf)
					fwrite($xmlSummaryFile, "\n\t</Accounts>$images\n</PDF>");
					fclose($xmlSummaryFile);
					$generatedDocs[] = $archiveDir . '/index.xml';
					rename($archiveDir . '/' . $instanceRef . '.index.xml', $archiveDir . '/index.xml');
				}

			}

			// If writing to an archived file...
			if ($bolArchived)
			{
				$this->log("Archiving files to $strArchiveFile");
				$objArchive = new Archive_Tar($strArchiveFile, $strCompression);
				$objArchive->addModify($generatedDocs, '', $archiveDir);

				// Remove the archived folder
				$this->log("Removing unarchived copies of files");
				foreach ($generatedDocs as $strSource => $strDestination)
				{
					unlink($strDestination);
				}

				if ($rmDir)
				{
					rmdir($rmDir);
				}
			}

			if ($mungeError)
			{
				$this->log("\nCompleted with munge error. PDFs have been packed individually, not merged because:\n$mungeError", TRUE, FALSE, TRUE);
				return 1;
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
				self::ARG_DESCRIPTION => "is the name of the 'c'ustomer group to create the PDF file for (from database) [optional, default taken from XML file]",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validConstant("%1$s", "CUSTOMER_GROUP_")'
			),

			self::SWITCH_DOCUMENT_TYPE_ID => array(
				self::ARG_LABEL 		=> "DOCUMENT_TYPE",
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the 'd'ocument type to be generated (e.g. INVOICE, FRIENDLY_REMINDER, OVERDUE_NOTICE, SUSPENSION_NOTICE or FINAL_DEMAND_NOTICE)",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validConstant("%1$s", "DOCUMENT_TEMPLATE_TYPE_")'
			),

			self::SWITCH_XML_DATA_FILE_LOCATION => array(
				self::ARG_LABEL 		=> "XML_DATA_FILE_LOCATION",
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the full path to an 'X'ML data file or directory containing XML files",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validReadableFileOrDirectory("%1$s")'
			),

			self::SWITCH_OUTPUT_FILE_PATH_AND_NAME => array(
				self::ARG_LABEL 		=> "OUTPUT_FILE_PATH_AND_NAME",
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the full path for the output PDF 'f'ile or directory (if PDF files exist, they may be overwritten)",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validWritableFileOrDirectory("%1$s")'
			),
		
			self::SWITCH_EFFECTIVE_DATE => array(
				self::ARG_LABEL 		=> "EFFECTIVE_DATE",
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the 'e'ffective date of the document in 'YYYY-mm-dd hh:ii:ss' or Unix " .
										"timestamp format and detmines the template used [optional, default taken from XML file]",
				self::ARG_DEFAULT 	=> time(),
				self::ARG_VALIDATION 	=> 'Cli::_validDate("%1$s")'
			),
		
			self::SWITCH_SOURCE_MEDIA => array(
				self::ARG_LABEL 		=> "SOURCE_MEDIA", 
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "if specified, only XML documents originally intended for the 'm'edia are processed (EMAIL or PRINT)",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validInArray("%1$s", array("EMAIL","PRINT"))'
			),

			self::SWITCH_OUTPUT_MEDIA => array(
				self::ARG_LABEL 		=> "OUTPUT_MEDIA", 
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the 'o'utput media for the PDF document (EMAIL or PRINT) [optional, default taken from XML file]",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validInArray("%1$s", array("EMAIL","PRINT"))'
			),

			self::SWITCH_SINGLE_PDF => array(
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => " if set, 'j'oins all pdfs to a single pdf file (only effective when archiving files)",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validIsSet()'
			),

			self::SWITCH_SKIP_XML_OVERVIEW => array(
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => " if set, skips generation of an 'i'ndex.xml summary file when outputting to a single PDF file",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_IGNORE_ACCOUNTS => array(
				self::ARG_LABEL 		=> "IGNORE_ACCOUNTS", 
				self::ARG_REQUIRED 		=> FALSE,
				self::ARG_DESCRIPTION 	=> "if set, ignores a space-delimited list of Accounts (encapsulated in quotes for multiple Accounts)",
				self::ARG_DEFAULT 		=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validString("%1$s")'
			),
		);
		return $commandLineArguments;
	}

}

?>
