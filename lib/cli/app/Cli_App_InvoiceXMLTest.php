<?php

class Cli_App_InvoiceXMLTest extends Cli
{
	const SWITCH_XML_DATA_FILE_LOCATION = "x";
	const SWITCH_LOG = "l";
	const SWITCH_VERBOSE = "v";
	const SWITCH_PRECISE = "p";
	const SWITCH_SILENT = "s";

	private $logFile = NULL;
	private $logSilent = FALSE;
	private $logVerbose = FALSE;
	private $rounding = 1;

	private function startLog($logFile, $logSilent=FALSE, $logVerbose=FALSE)
	{
		$this->logSilent = $logSilent;
		$this->logVerbose = $logVerbose;
		if ($logFile && $this->logFile == NULL)
		{
			$this->logFile = fopen($logFile, "w+");
			$this->log("\n::START::");
		}
	}

	private function log($message, $isError=FALSE, $suppressNewLine=FALSE)
	{
		if (!$this->logVerbose && !$isError) return;
		if (!$this->logSilent) 
		{
			echo $message . ($suppressNewLine ? "" : "\n");
			flush();
		}
		if ($this->logFile == NULL) return;
		fwrite($this->logFile, date("Y-m-d H-i-s.u :: ") . trim(str_replace(chr(8), '', $message)) . "\n");
	}

	private function endLog()
	{
		$this->log("::END::");
		if ($this->logFile == NULL) return;
		fclose($this->logFile);
	}

	function run()
	{
		$testingMessageLength = 0;
		try
		{
			// Include the application... 
			//$this->requireOnce("flex.require.php");

			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			// Check to see if we are logging...
			$this->startLog($arrArgs[self::SWITCH_LOG], $arrArgs[self::SWITCH_SILENT], $arrArgs[self::SWITCH_VERBOSE]);

			$this->rounding = $arrArgs[self::SWITCH_PRECISE] ? 0 : 1;

			$this->log("Processing command line parameters");

			$strSource = $arrArgs[self::SWITCH_XML_DATA_FILE_LOCATION];

			$this->log("Mode:"
						. " Logging? " . ($this->logFile ? 'Y' : 'N')
						. " Verbose? " . ($this->logVerbose ? 'Y' : 'N')
						. " Precise? " . ($this->rounding ? 'N' : 'Y')
						);

			$this->log("Source location: $strSource");

			// Get the XML data files to be used
			$arrFiles = array();
			if (is_file($strSource))
			{
				// Store the destination file for the source file
				$arrFiles[] = realpath($strSource);
			}
			else
			{
				// Look for files in the source directory...
				$arrSourceContents = scandir($strSource);
				for ($i = 0, $l = count($arrSourceContents); $i < $l; $i++)
				{
					$strPath = $strSource . DIRECTORY_SEPARATOR . $arrSourceContents[$i];
					// Ignore non-xml files
					if (substr($strPath, -4) != '.xml') continue;
					// Ignore directories (including source directory '.' and parent directory '..')
					if (is_file($strPath))
					{
						// If the file cannot be read, we'd better throw a wobbler as the user may be expecting a PDF for it!
						if (!is_readable($strPath))
						{
							throw new Exception("Directory '" . $strSource . "' contains unreadable file '" . $arrSourceContents[$i] . "'");
						}
						// Store the destination file for this source file
						$arrFiles[] = realpath($strPath);

					}
				}
			}

			$this->log("Testing " . count($arrFiles) . " XML source files");

			// Include the pdf library...
			$this->requireOnce("lib/pdf/Flex_Pdf.php");

			$docCount = 0;

			$generatedDocs = array();

			$errorCount = 0;
			$passCount = 0;

			foreach ($arrFiles as $strSource)
			{
				$undo = str_repeat(chr(8), $testingMessageLength);
				$testingMessage = "Testing XML file (" . ++$docCount . "): $strSource";
				$messageLen = strlen($testingMessage);
				$testingMessageLength = max($testingMessageLength, $messageLen);
				$pad = str_repeat(' ', $testingMessageLength - $messageLen);
				$this->log($undo.$testingMessage.$pad, FALSE, TRUE);

				// Make sure we have enough time to test the XML file (180 should be mega safe!)...
				set_time_limit(180);

				// Create the PDF template
				$this->startErrorCatching();
				$error = $this->test($strSource, $testingMessageLength);
				$this->dieIfErred();
				
				$errorCount += $error;
				$testingMessageLength = $testingMessageLength * ($error ? 0 : 1);
				if (!$error)
				{
					$passCount += 1;
				}
			}

			$message = "Testing complete";
			$undo = str_repeat(chr(8), $testingMessageLength);
			$messageLen = strlen($message);
			$testingMessageLength = max($testingMessageLength, $messageLen);
			$pad = str_repeat(' ', $testingMessageLength - $messageLen);
			$this->log($undo.$message.$pad);

			$message = $errorCount	? "\nCompleted testing after detecting $errorCount defective files and $passCount valid files.\n"
									: "\nCompleted successfully with no errors detected in $passCount files.\n";
			$this->log($message, $errorCount);

			$this->endLog();

			// Must have worked! Exit with 'OK' code 0 for no errors, or positive int of number of errors
			exit($errorCount);
		}
		catch(Exception $exception)
		{
			$this->log($undo."\nERROR: " . $exception->getMessage(), TRUE);
			$this->endLog();
			$this->showUsage($exception->getMessage());
			exit(1);
		}
	}

	function getCommandLineArguments()
	{
		$commandLineArguments = array(

			self::SWITCH_XML_DATA_FILE_LOCATION => array(
				self::ARG_LABEL 		=> "XML_DATA_FILE_LOCATION",
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the full path to an XML data file or directory containing XML files",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validReadableFileOrDirectory("%1$s")'
			),

			self::SWITCH_LOG => array(
				self::ARG_LABEL 		=> "LOG_FILE", 
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is a writable file location to write log messages to (EMAIL or PRINT) [optional, default is no logging]",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validFile("%1$s", FALSE)'
			),

			self::SWITCH_VERBOSE => array(
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "for verbose messages [optional, default is to output errors only]",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validIsSet()'
			),

			self::SWITCH_SILENT => array(
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "no not output messages to console [optional, default is to output messages]",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validIsSet()'
			),

			self::SWITCH_PRECISE => array(
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "to treat 'rounding errors' as errors [optional, default is to ignore rounding errors]",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validIsSet()'
			)
		);
		return $commandLineArguments;
	}

	function test($strSource, $cleanUpLength)
	{
		try
		{
			// Clear out the cache for testing the new file
			$cache = array();

			$dom = new DOMDocument();
			$ok = $dom->load($strSource);
			if (!$ok)
			{
				throw new Exception("Unable to load file as XML. Check that the XML is valid.");
			}

			// Services test must be run before anything else 
			$this->testServices($dom, $cache);
			// Summary test must be run before Charges test
			$this->testStatement($dom, $cache);
			// Charges test must be run before Cost Centres test
			$this->testCharges($dom, $cache);
			$this->testCostCentres($dom, $cache);

			return 0;
		}
		catch(Exception $exception)
		{
			$message = "ERROR: Source file: $strSource";
			$undo = str_repeat(chr(8), $cleanUpLength);
			$messageLen = strlen($message);
			$cleanUpLength = max($cleanUpLength, $messageLen);
			$pad = str_repeat(' ', $cleanUpLength - $messageLen);
			$this->log($undo.$message.$pad."\n" . $exception->getMessage(), TRUE);
			return 1;
		}
	}

	function testServices(&$dom, &$cache)
	{
		$services = $dom->getElementsByTagName('Services');
		if ($services->length !== 1)
		{
			throw new Exception('Invoice contains ' . $services->length. ' <services> elements.');
		}
		$services = $services->item(0)->getElementsByTagName('Service');

		$cache['services'] = array();
		$cache['serviceCharges'] = array();
		$cache['costCentreServices'] = array();
		$cache['costCentreServicesTotal'] = array();
		$cache['serviceCostCentre'] = array();

		for ($i = 0; $i < $services->length; $i++)
		{
			$this->testService($services->item($i), &$cache);
		}
	}
	
	function testService(&$service, &$cache)
	{
		$fnn = $service->getAttribute('FNN');
		$costCentre = $service->getAttribute('CostCentre');
		$plan = $service->getAttribute('Plan');
		$grandTotal = round(round(floatval($service->getAttribute('GrandTotal')), 2)*100);

		if (array_key_exists($fnn, $cache['services']))
		{
			throw new Exception("Service with FNN '$fnn' is itemised multiple times.");
		}

		$cache['services'][$fnn] = $grandTotal;
		$cache['costCentreServices'][$costCentre][] = $fnn;
		if (!array_key_exists($costCentre, $cache['costCentreServicesTotal']))
		{
			$cache['costCentreServicesTotal'][$costCentre] = 0;
		}
		$cache['costCentreServicesTotal'][$costCentre] += $grandTotal;
		$cache['serviceCostCentre'][$fnn] = $costCentre;

		$serviceCategories = array();

		$itemisationTotal = 0;

		$categories = $service->getElementsByTagName('Category');

		for ($j = 0; $j < $categories->length; $j++)
		{
			$category = $categories->item($j);

			$categoryName = $category->getAttribute('Name');
			$categoryGrandTotal = round(round(floatval($category->getAttribute('GrandTotal')), 2)*100);
			$itemisationTotal += $categoryGrandTotal;

			if (!array_key_exists($categoryName, $cache['serviceCharges']))
			{
				$cache['serviceCharges'][$categoryName] = 0;
			}
			$cache['serviceCharges'][$categoryName] += $categoryGrandTotal;

			$categoryRecords = intval($category->getAttribute('Records'));

			$items = $category->getElementsByTagName('Item');

			if ($items->length !== $categoryRecords)
			{
				throw new Exception("Itemisation of '$categoryName' for FNN '$fnn' claims there are $categoryRecords items but lists " . $items->length . ".");
			}

			$categoryTotal = 0;
			$categoryItems = array();

			for ($k = 0; $k < $items->length; $k++)
			{
				$charge = $items->item($k)->getElementsByTagName('Charge');
				if ($charge->length !== 1)
				{
					throw new Exception("Itemisation of '$categoryName' for FNN '$fnn' lists " . $charge->length . " Charges for item $k.");
				}
				$charge = round(round(floatval($charge->item(0)->nodeValue), 2)*100);
				$categoryTotal += $charge;

				$props = "";
				for ($l = 0; $l < $items->item($k)->childNodes->length; $l++)
				{
					$props .= ", \t" . $items->item($k)->childNodes->item($l)->nodeValue;
				}

				if (array_search($props, $categoryItems) !== FALSE)
				{
					throw new Exception("Duplicate charge found in itemisation of '$categoryName' for FNN '$fnn'>> $props.");
				}
				$categoryItems[] = $props;
			}

			if (!$this->precisionEquals($categoryTotal, $categoryGrandTotal, $categoryRecords))
			{
				throw new Exception("Category '$categoryName' for FNN '$fnn' claims GrandTotal of $categoryGrandTotal but items total $categoryTotal.");
			}
		}

		if (!$this->precisionEquals($itemisationTotal, $grandTotal, $categories->length))
		{
			throw new Exception("Service itemisation for FNN '$fnn' claims GrandTotal of $grandTotal but categories total $itemisationTotal.");
		}

	}

	function precisionEquals($totalA, $totalB, $items)
	{
		$allowableError = $this->rounding * $items;
		return (abs($totalA - $totalB) - $allowableError) <= 0;
	}

	function testStatement(&$dom, &$cache)
	{
		$statements = $dom->getElementsByTagName('Statement');
		if ($statements->length !== 1)
		{
			throw new Exception('Invoice contains ' . $statements->length. ' <Statement> elements.');
		}
		$statement = $statements->item(0);
		$openingBalance = $statement->getElementsByTagName('OpeningBalance');
		$payments = $statement->getElementsByTagName('Payments');
		$overdue = $statement->getElementsByTagName('Overdue');
		$newCharges = $statement->getElementsByTagName('NewCharges');
		$totalOwing = $statement->getElementsByTagName('TotalOwing');

		if ($openingBalance->length !== 1)
		{
			throw new Exception('Invoice contains ' . $openingBalance->length. ' <OpeningBalance> elements.');
		}
		$openingBalance = round(round(floatval($openingBalance->item(0)->nodeValue), 2)*100);

		if ($payments->length !== 1)
		{
			throw new Exception('Invoice contains ' . $payments->length. ' <Payments> elements.');
		}
		$payments = round(round(floatval($payments->item(0)->nodeValue), 2)*100);

		if ($overdue->length !== 1)
		{
			throw new Exception('Invoice contains ' . $overdue->length. ' <Overdue> elements.');
		}
		$overdue = round(round(floatval($overdue->item(0)->nodeValue), 2)*100);

		if ($newCharges->length !== 1)
		{
			throw new Exception('Invoice contains ' . $newCharges->length. ' <NewCharges> elements.');
		}
		$newCharges = round(round(floatval($newCharges->item(0)->nodeValue), 2)*100);

		if ($totalOwing->length !== 1)
		{
			throw new Exception('Invoice contains ' . $totalOwing->length. ' <TotalOwing> elements.');
		}
		$totalOwing = round(round(floatval($totalOwing->item(0)->nodeValue), 2)*100);

		if (!$this->precisionEquals($openingBalance - $payments + $newCharges, $totalOwing, 3))
		{
			throw new Exception("Opening balance of '$openingBalance' less payments of '$payments' plus charges of '$newCharges' does not equal total owing '$totalOwing'.");
		}

		$cache['statementNewCharges'] = $newCharges;
	}

	function testCharges(&$dom, &$cache)
	{
		$charges = $dom->getElementsByTagName('Charges');
		if ($charges->length !== 1)
		{
			throw new Exception('Invoice contains ' . $charges->length. ' <Charges> elements.');
		}
		$categories = $charges->item(0)->getElementsByTagName('Category');

		$cache['charges'] = array();

		$chargesTotal = 0;

		for ($i = 0; $i < $categories->length; $i++)
		{
			$categoryName = $categories->item($i)->getAttribute('Name');
			$categoryTotal = round(round(floatval($categories->item($i)->getAttribute('GrandTotal')), 2)*100);
			$chargesTotal += $categoryTotal;
			$categoryRecords = intval($categories->item($i)->getAttribute('Records'));

			if (array_key_exists($categoryName, $cache['charges']))
			{
				throw new Exception("Multiple entries found for charge type '$categoryName' in Document/Invoice/Charges.");
			}

			$cache['charges'][$categoryName] = $categoryTotal;

			$items = $categories->item($i)->getElementsByTagName('Item');
			$nrRecords = $items->length;
			if ($nrRecords != $categoryRecords)
			{
				throw new Exception("Category '$categoryName' claims to have $categoryRecords items but lists $nrRecords items in Document/Invoice/Charges.");
			}

			// This must be an account charge
			if ($categoryName == 'GST Total')
			{
				// Ignore it!
			}
			else if ($nrRecords)
			{
				$itemDescs = array();
				$total = 0;
				for ($j = 0; $j < $items->length; $j++)
				{
					$desc = $items->item($j)->getElementsByTagName('Description');
					if ($desc->length != 1)
					{
						throw new Exception("Category '$categoryName' item $j has $desc->length Descriptions in Document/Invoice/Charges.");
					}
					$desc = $desc->item(0)->nodeValue;
					if (array_key_exists($desc, $itemDescs))
					{
						throw new Exception("Category '$categoryName' has duplicate items of type '$desc' in Document/Invoice/Charges.");
					}

					$chrg = $items->item($j)->getElementsByTagName('Charge');
					if ($chrg->length != 1)
					{
						throw new Exception("Category '$categoryName' item $j has $chrg->length Charges in Document/Invoice/Charges.");
					}
					$total += round(round(floatval($chrg->item(0)->nodeValue), 2)*100);
				}
				if (!$this->precisionEquals($total, $categoryTotal, $items->length))
				{
					throw new Exception("Category '$categoryName' GrandTotal of $categoryTotal does not match Items total of $total in Document/Invoice/Charges.");
				}
			}
			// This must be a service charge - check it is itemised and that the totals add up
			else
			{
				if ($categoryTotal > 0 && !array_key_exists($categoryName, $cache['serviceCharges']))
				{
					throw new Exception("Document/Invoice/Charges/Category '$categoryName' appears to be a service charge but has no corresponding service itemisation.");
				}
				if (($categoryTotal > 0 || array_key_exists($categoryName, $cache['serviceCharges'])) && $cache['serviceCharges'][$categoryName] != $categoryTotal)
				{
					throw new Exception("Document/Invoice/Charges/Category '$categoryName' claims total of $categoryTotal but service itemisations of the type total " . $cache['serviceCharges'][$categoryName] . ".");
				}
			}
		}

		if (!$this->precisionEquals($cache['statementNewCharges'], $chargesTotal, $charges->length))
		{
			throw new Exception("Statement claims new charges of " . $cache['statementNewCharges'] . " but Charges in summary total " . $chargesTotal . ".");
		}

		$cache['chargesTotal'] = $chargesTotal;
	}

	function testCostCentres(&$dom, &$cache)
	{
		$costCentres = $dom->getElementsByTagName('CostCentre');

		$listedCostCentres = array();

		for ($i = 0; $i < $costCentres->length; $i++)
		{
			$costCentre = $costCentres->item($i);
			$name = $costCentre->getAttribute('Name');
			if (array_key_exists($name, $listedCostCentres))
			{
				throw new Exception("Cost Centre '$name' is listed multiple times.");
			}
			$listedCostCentres[] = $name;
			$grandTotal = round(round(floatval($costCentre->getAttribute('Total')), 2)*100);

			$services = $costCentre->getElementsByTagName('Service');
			$records = $services->length;
			
			if ($records && !array_key_exists($name, $cache['costCentreServices']))
			{
				throw new Exception("Cost Centre '$name' lists $records services but no service itemisations exist for it.");
			}

			if (count($cache['costCentreServices'][$name]) != $records)
			{
				throw new Exception("Cost Centre '$name' claims to have $records services but " . count($cache['costCentreServices'][$name]) . " services are itemised as belonging to it.");
			}

			if (!$this->precisionEquals($cache['costCentreServicesTotal'][$name], $grandTotal, $records))
			{
				throw new Exception("Cost Centre '$name' claims total charges of $grandTotal, but service itemisations total " . $cache['costCentreServicesTotal'][$name] . ".");
			}

			$costCentreTotal = 0;
			for ($j = 0; $j < $records; $j++)
			{
				$fnn = $services->item($j)->getAttribute('FNN');
				$total = round(round(floatval($services->item($j)->nodeValue), 2)*100);
				$costCentreTotal += $total;

				if (!array_key_exists($fnn, $cache['serviceCostCentre']))
				{
					throw new Exception("Cost Centre '$name' claims to have service '$fnn' but no itemisation exists for it.");
				}

				if ($name != $cache['serviceCostCentre'][$fnn])
				{
					throw new Exception("Cost Centre '$name' claims to have service '$fnn' but itemisation of that service claims it is for Cost Centre " . $cache['serviceCostCentre'][$fnn] . ".");
				}

				if (!$this->precisionEquals($total, $cache['services'][$fnn], 0))
				{
					throw new Exception("Cost Centre '$name' lists service '$fnn' total as $total, but itemisation of that service claims total of " . $cache['services'][$fnn] . ".");
				}
			}
		}
		
		foreach ($cache['serviceCostCentre'] as $fnn => $costCentre)
		{
			if ($costCentre && array_search($costCentre, $listedCostCentres) === FALSE)
			{
				throw new Exception("Service '$fnn' is itemised as belonging to Cost Centre '$costCentre', but no such Cost Centre is listed.");
			}
		}
	}

}

?>
