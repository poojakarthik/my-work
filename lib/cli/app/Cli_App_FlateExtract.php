<?php
/*
 * Created on 25/11/2010
 *
 */

class Cli_App_FlateExtract extends Cli
{
	const SWITCH_PDF_FILE_LOCATION = "f";

	private $logFile = NULL;

	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			$this->log("Processing command line parameters.");

			$strSource = $arrArgs[self::SWITCH_PDF_FILE_LOCATION];

			// Get the PDF files to have flate information extracted
			$arrFiles = array();
			if (is_file($strSource))
			{
				// Store the destination file for the source file
				$arrFiles[$strSource] = $strSource . ".flate";
			}
			else
			{
				// Look for files in the source directory...
				foreach (glob("$strSource" . DIRECTORY_SEPARATOR . "*.pdf") as $strFilename)
				{
					$arrFiles[$strFilename] = $strFilename . ".flate";
				}
			}

			$this->log("Processing " . count($arrFiles) . " possible flate compressed files.");

			foreach ($arrFiles as $strSource => $strDestination)
			{
				$this->log("Processing flate file: $strSource");
				// Make sure we have enough time to process this...
				set_time_limit(180);

				try
				{				
					$strContent				= file_get_contents($strSource);
					$intCount					= preg_match_all("|stream(.*)endstream|Us", $strContent, $arrMatches, PREG_PATTERN_ORDER);
					$strFlateContent		= "";

					for ($xi=0; $xi<$intCount; $xi++)
					{
						$strFlateContent .= trim($arrMatches[1][$xi]);
					}
				}
				catch (Exception $e)
				{
					$this->log("Failed to get Flate information from $strSource. Error message: " . $e->getMessage(), TRUE);
					continue;
				}

				$this->log("Writing flate content to $strDestination.");
				$f = @fopen($strDestination, "w+b");
				@fwrite($f, $strFlateContent);
				@fclose($f);

				$this->log("Validating saved, flate content.");
				if (!file_exists($strDestination) || file_get_contents($strDestination) != $strFlateContent)
				{
					$this->log("Writing flate content to $strDestination failed validation. Check the file permissions of the source directory.", TRUE);
					continue;
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

			self::SWITCH_PDF_FILE_LOCATION => array(
				self::ARG_LABEL 		=> "FLATE_PDF_LOCATION",
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the full path to a file containing PDF flate compressed data or directory containing such files",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validReadableFileOrDirectory("%1$s")'
			),

		);
		return $commandLineArguments;
	}

}

?>
